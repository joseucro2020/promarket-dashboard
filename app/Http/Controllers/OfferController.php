<?php

namespace App\Http\Controllers;

use App\Models\Offer;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests\OfferRequest;
use App\Models\Category;
use App\Models\Product;
use App\Models\Subcategory;
use App\Models\Subsubcategories;
use Carbon\Carbon;
use App\Models\Promotion;
use Illuminate\Support\Facades\DB;

class OfferController extends Controller
{
    public function index()
    {
        return view('panel.offers.index', [
            'offers' => Offer::latest()->take(1000)->get(),
            'categories' => [],
            'products' => [],
        ]);
    }

    public function create()
    {
        // Safely attempt to gather active promotions' product ids.
        // Some environments may not have the promotion pivot table/schema; guard against SQL errors.
        try {
            $promotions = Promotion::where('status', Promotion::STATUS_ACTIVE)
                ->whereDate('start_date', '<=', date('Y-m-d'))
                ->whereDate('end_date', '>=', date('Y-m-d'))
                ->get();

            $promotions = $promotions->map(function ($q) {
                return $q->products->map(function ($q) {
                    return $q->product_amount['product']['id'] ?? null;
                });
            });

            if (count($promotions) > 0) {
                $promotions = call_user_func_array('array_merge', $promotions->toArray());
                $promotions = array_filter($promotions);
            }
        } catch (\Exception $e) {
            // log and fallback to empty promotions so the page still loads
            logger()->warning('Could not load promotions for offers form: '.$e->getMessage());
            $promotions = [];
        }

        $categories = Category::select('id', 'name', 'name_english')
            ->where('status', '1')
            ->with([
                'subcategories' => function ($sql) {
                    $sql->select('id', 'name', 'name_english', 'category_id')
                        ->with(['sub_subcategories' => function ($r) {
                            $r->select('id', 'name', 'subcategory_id')->where('status', '1');
                        }])
                        ->where('status', '1');
                },
                'sizes' => function ($sizes) {
                    $sizes->select('category_sizes.id', 'name');
                }
            ])
            ->orderBy('name', 'DESC')
            ->get();

        $products = DB::table('products')
            ->select('products.id', 'products.name', 'products.category_id', 'products.subcategory_id', 'products.subsubcategory_id')
            ->when(!empty($promotions), function ($q) use ($promotions) {
                $q->whereNotIn('id', $promotions);
            })
            ->where('status', '1')
            ->get();

        return view('panel.offers.form', [
            'categories' => $categories,
            'products' => $products,
            'subcategories' => Subcategory::where('status','1')->get(),
            'subsub' => Subsubcategories::where('status','1')->get(),
        ]);
    }

    public function store(OfferRequest $request)
    {
        $offer = Offer::create($request->validated());

        // attach selected products (view submits `products[]`)
        if ($request->filled('products')) {
            $offer->products()->attach($request->input('products'));
        }

        return redirect()->route('offers.index')->with('success', __('Offer saved successfully.'));
    }

    public function edit(Offer $offer)
    {
        try {
            $promotions = Promotion::where('status', Promotion::STATUS_ACTIVE)
                ->whereDate('start_date', '<=', date('Y-m-d'))
                ->whereDate('end_date', '>=', date('Y-m-d'))
                ->get();

            $promotions = $promotions->map(function ($q) {
                return $q->products->map(function ($q) {
                    return $q->product_amount['product']['id'] ?? null;
                });
            });

            if (count($promotions) > 0) {
                $promotions = call_user_func_array('array_merge', $promotions->toArray());
                $promotions = array_filter($promotions);
                $promotions = array_diff($promotions, $offer->products()->pluck('id')->toArray());
            }
        } catch (\Exception $e) {
            logger()->warning('Could not load promotions for offers edit form: '.$e->getMessage());
            $promotions = [];
        }

        $categories = Category::select('id', 'name', 'name_english')
            ->where('status', '1')
            ->with([
                'subcategories' => function ($sql) {
                    $sql->select('id', 'name', 'name_english', 'category_id')
                        ->with(['sub_subcategories' => function ($r) {
                            $r->select('id', 'name', 'subcategory_id')->where('status', '1');
                        }])
                        ->where('status', '1');
                },
                'sizes' => function ($sizes) {
                    $sizes->select('category_sizes.id', 'name');
                }
            ])
            ->orderBy('name', 'DESC')
            ->get();

        $products = DB::table('products')
            ->select('products.id', 'products.name', 'products.category_id', 'products.subcategory_id', 'products.subsubcategory_id')
            ->when(!empty($promotions), function ($q) use ($promotions) {
                $q->whereNotIn('id', $promotions);
            })
            ->where('status', '1')
            ->get();

        return view('panel.offers.form', [
            'offer' => $offer->load('products'),
            'categories' => $categories,
            'products' => $products,
            'subcategories' => Subcategory::where('status','1')->get(),
            'subsub' => Subsubcategories::where('status','1')->get(),
        ]);
    }

    public function update(OfferRequest $request, Offer $offer)
    {
        $offer->fill($request->validated());
        $offer->save();
        // sync selected products (view uses `products[]`)
        $offer->products()->sync($request->input('products', []));

        return redirect()->route('offers.index')->with('success', __('Offer updated successfully.'));
    }

    public function destroy(Offer $offer)
    {
        $offer->delete();
        return redirect()->route('offers.index')->with('success', __('Offer deleted successfully.'));
    }

    public function status(Offer $offer)
    {
        if ($offer->status == Offer::INACTIVE) {
            $now = Carbon::now();
            $end = new Carbon($offer->end);
            if ($end->isBefore($now)) {
                return response()->json(['message' => 'No se puede activar la oferta debido a que la fecha de finalización ya ha pasado'], 422);
            }

            $productsWithOffersActive = Product::whereIn('id', $offer->products->pluck('id'))
                ->whereHas('offersActive')
                ->exists();

            if ($productsWithOffersActive) {
                return response()->json(['message' => 'No se puede activar la oferta debido tiene productos agregados que ya tienen un oferta activa'], 422);
            }

            $productsWithDiscountsActive = Product::whereIn('id', $offer->products->pluck('id'))
                ->whereHas('discountsActive')
                ->exists();

            if ($productsWithDiscountsActive) {
                return response()->json(['message' => 'No se puede activar la oferta debido tiene productos agregados que ya tienen un descuento activo'], 422);
            }
        }

        $offer->status = $offer->status == Offer::ACTIVE ? Offer::INACTIVE : Offer::ACTIVE;
        $offer->save();
        return response()->json($offer);
    }

    /**
     * Server-side endpoint for DataTables to fetch products with filters.
     */
    public function productsData(Request $request)
    {
        $start = (int)($request->input('start', 0));
        $length = (int)($request->input('length', 10));
        $search = $request->input('search.value', '');

        $query = Product::where('status', Product::STATUS_ACTIVE);

        if ($request->filled('category')) {
            $query->where('category_id', $request->input('category'));
        }
        if ($request->filled('subcategory')) {
            $query->where('subcategory_id', $request->input('subcategory'));
        }
        if ($request->filled('subsub')) {
            $query->where('subsubcategory_id', $request->input('subsub'));
        }

        $recordsTotal = Product::where('status', Product::STATUS_ACTIVE)->count();

        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%");
            });
        }

        $recordsFiltered = $query->count();

        // ordering
        $orderColIndex = $request->input('order.0.column', 1);
        $orderDir = $request->input('order.0.dir', 'asc');
        // map DataTables column index to DB column
        $columnsMap = [0 => 'id', 1 => 'name'];
        $orderColumn = $columnsMap[$orderColIndex] ?? 'name';

        $rows = $query->orderBy($orderColumn, $orderDir)
            ->skip($start)
            ->take($length)
            ->get(['id', 'name']);

        $data = [];
        foreach ($rows as $row) {
            $img = $row->image_url ? $row->image_url : url('images/placeholder.png');
            $imgHtml = '<div class="text-center"><img src="'.$img.'" style="max-height:48px" class="img-fluid"/></div>';
            $action = '<button type="button" class="btn btn-icon btn-flat-success add-to-offer" data-id="'.$row->id.'" data-name="'.htmlspecialchars($row->name, ENT_QUOTES).'" title="'.__('Add').'"><i data-feather="plus"></i></button>';
            $data[] = [$imgHtml, $row->name, $action];
        }

        return response()->json([
            'draw' => (int)$request->input('draw', 0),
            'recordsTotal' => $recordsTotal,
            'recordsFiltered' => $recordsFiltered,
            'data' => $data,
        ]);
    }
}
