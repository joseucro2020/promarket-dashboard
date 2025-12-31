<?php

namespace App\Http\Controllers;

use App\Models\Offer;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests\OfferRequest;
use App\Models\Category;
use App\Models\Product;
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
        ]);
    }

    public function store(OfferRequest $request)
    {
        $offer = Offer::create($request->validated());

        if ($request->has('products_id')) {
            $offer->products()->attach($request->products_id);
        }

        return redirect()->route('offers.index')->with('success', __('Offer saved successfully.'));
    }

    public function edit(Offer $offer)
    {
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
        ]);
    }

    public function update(OfferRequest $request, Offer $offer)
    {
        $offer->fill($request->validated());
        $offer->save();
        $offer->products()->sync($request->products_id ?? []);

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
}
