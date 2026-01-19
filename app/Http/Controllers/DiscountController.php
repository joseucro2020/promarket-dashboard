<?php

namespace App\Http\Controllers;

use App\Models\Discount;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests\DiscountRequest;
use App\Models\Category;
use App\Models\Product;
use App\Models\Promotion;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DiscountController extends Controller
{
    public function index()
    {
        $discounts = Discount::latest()->take(1000)->get();

        return view('panel.discounts.index', [
            'categories' => [],
            'products' => [],
            'discounts' => $discounts,
        ]);
    }

    public function create()
    {
        $promotions = Promotion::where('status', Promotion::STATUS_ACTIVE)
            ->whereDate('start_date', '<=', date('Y-m-d'))
            ->whereDate('end_date', '>=', date('Y-m-d'))
            ->get();

        $promotions = $promotions->map(function ($promotion) {
            return $promotion->products->map(function ($product) {
                return $product->product_amount['product']['id'] ?? 0;
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
                            $r->select('id', 'name', 'subcategory_id')
                                ->where('status', '1');
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

        return view('panel.discounts.form', [
            'categories' => $categories,
            'products' => $products,
        ]);
    }

    public function store(DiscountRequest $request)
    {
        $data = $request->validated();

        // Map incoming discount_mode to internal type names
        $mode = $request->input('discount_mode');
        if ($mode) {
            switch ($mode) {
                case 'quantity':
                    $data['type'] = 'quantity_product';
                    $data['quantity_product'] = $request->input('quantity_products');
                    // clear other type-related fields
                    $data['minimum_purchase'] = null;
                    $data['quantity_purchase'] = null;
                    break;
                case 'amount':
                    $data['type'] = 'minimum_purchase';
                    $data['minimum_purchase'] = $request->input('min_amount');
                    $data['quantity_product'] = null;
                    $data['quantity_purchase'] = null;
                    break;
                case 'count':
                    $data['type'] = 'quantity_purchase';
                    $data['quantity_purchase'] = $request->input('quantity_products');
                    $data['quantity_product'] = null;
                    $data['minimum_purchase'] = null;
                    break;
            }
        }

        $discount = Discount::create($data);

        // Attach products only for quantity_product type if provided
        if (($data['type'] ?? null) === 'quantity_product') {
            if ($request->has('products_id')) {
                $discount->products()->sync($request->input('products_id', []));
            }
        }

        // If this type should be unique and there is an existing active of same type, deactivate new one
        $existsOtherActive = Discount::where('type', $discount->type)
            ->where('status', Discount::ACTIVE)
            ->where('id', '!=', $discount->id)
            ->exists();

        if ($existsOtherActive) {
            $discount->status = Discount::INACTIVE;
            $discount->save();
        }

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json(['message' => __('Discount saved successfully.'), 'redirect' => route('discounts.index')]);
        }

        return redirect()->route('discounts.index')->with('success', __('Discount saved successfully.'));
    }

    public function edit(Discount $discount)
    {
        $promotions = Promotion::where('status', Promotion::STATUS_ACTIVE)
            ->whereDate('start_date', '<=', date('Y-m-d'))
            ->whereDate('end_date', '>=', date('Y-m-d'))
            ->get();

        $promotions = $promotions->map(function ($promotion) {
            return $promotion->products->map(function ($product) {
                return $product->product_amount['product']['id'] ?? 0;
            });
        });

        if (count($promotions) > 0) {
            $promotions = call_user_func_array('array_merge', $promotions->toArray());
            $promotions = array_filter($promotions);
            $promotions = array_diff($promotions, $discount->products()->pluck('id')->toArray());
        }

        $categories = Category::select('id', 'name', 'name_english')
            ->where('status', '1')
            ->with([
                'subcategories' => function ($sql) {
                    $sql->select('id', 'name', 'name_english', 'category_id')
                        ->with(['sub_subcategories' => function ($r) {
                            $r->select('id', 'name', 'subcategory_id')
                                ->where('status', '1');
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

        return view('panel.discounts.form', [
            'categories' => $categories,
            'products' => $products,
            'discount' => $discount->load('products'),
        ]);
    }

    public function update(DiscountRequest $request, Discount $discount)
    {
        $data = $request->validated();

        $mode = $request->input('discount_mode');
        if ($mode) {
            switch ($mode) {
                case 'quantity':
                    $data['type'] = 'quantity_product';
                    $data['quantity_product'] = $request->input('quantity_products');
                    $data['minimum_purchase'] = null;
                    $data['quantity_purchase'] = null;
                    break;
                case 'amount':
                    $data['type'] = 'minimum_purchase';
                    $data['minimum_purchase'] = $request->input('min_amount');
                    $data['quantity_product'] = null;
                    $data['quantity_purchase'] = null;
                    break;
                case 'count':
                    $data['type'] = 'quantity_purchase';
                    $data['quantity_purchase'] = $request->input('quantity_products');
                    $data['quantity_product'] = null;
                    $data['minimum_purchase'] = null;
                    break;
            }
        }

        $discount->fill($data);
        $discount->save();

        if (($data['type'] ?? null) === 'quantity_product') {
            if ($request->has('products_id')) {
                $discount->products()->sync($request->input('products_id', []));
            }
        } else {
            // ensure no leftover product relations for non-quantity types
            $discount->products()->detach();
        }

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json(['message' => __('Discount updated successfully.'), 'redirect' => route('discounts.index')]);
        }

        return redirect()->route('discounts.index')->with('success', __('Discount updated successfully.'));
    }

    public function destroy(Discount $discount)
    {
        $discount->delete();
        return redirect()->route('discounts.index')->with('success', __('Discount deleted successfully.'));
    }

    public function status(Discount $discount)
    {
        if ($discount->status == Discount::INACTIVE) {
            $now = Carbon::now();
            $end = new Carbon($discount->end);
            if ($end->isBefore($now)) {
                return response()->json(['message' => 'No se puede activar el descuento debido a que la fecha de finalización ya ha pasado'], 422);
            }

            $productsWithOffersActive = Product::whereIn('id', $discount->products->pluck('id'))
                ->whereHas('offersActive')
                ->exists();

            if ($productsWithOffersActive) {
                return response()->json(['message' => 'No se puede activar el descuento debido tiene productos agregados que ya tienen un oferta activa'], 422);
            }

            $productsWithDiscountsActive = Product::whereIn('id', $discount->products->pluck('id'))
                ->whereHas('discountsActive')
                ->exists();

            if ($productsWithDiscountsActive) {
                return response()->json(['message' => 'No se puede activar el descuento debido tiene productos agregados que ya tienen un descuento activo'], 422);
            }

            $promotions = Promotion::where('status', Promotion::STATUS_ACTIVE)
                ->whereDate('start_date', '<=', date('Y-m-d'))
                ->whereDate('end_date', '>=', date('Y-m-d'))
                ->get();

            $promotions = $promotions->map(function ($promotion) {
                return $promotion->products->map(function ($product) {
                    return $product->product_amount['product']['id'] ?? 0;
                });
            });

            if (count($promotions) > 0) {
                $promotions = call_user_func_array('array_merge', $promotions->toArray());

                if (array_intersect($promotions, $discount->products->pluck('id')->toArray()) > 0) {
                    return response()->json(['message' => 'No se puede activar el descuento debido tiene productos con una promoción activa'], 422);
                }
            }
        }

        $discount->status = $discount->status == Discount::ACTIVE ? Discount::INACTIVE : Discount::ACTIVE;
        $discount->save();

        return response()->json($discount);
    }

    /**
     * Server-side endpoint for DataTables to fetch products with filters for discounts
     */
    public function productsData(Request $request)
    {
        $start = (int)($request->input('start', 0));
        $length = (int)($request->input('length', 10));
        $search = $request->input('search.value', '');

        $query = Product::where('status', Product::STATUS_ACTIVE)->with('categories');

        if ($request->filled('category')) {
            $query->where('category_id', $request->input('category'));
        }
        if ($request->filled('subcategory')) {
            $query->where('subcategory_id', $request->input('subcategory'));
        }
        if ($request->filled('subsubcategory')) {
            $query->where('subsubcategory_id', $request->input('subsubcategory'));
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
        $columnsMap = [0 => 'id', 1 => 'name'];
        $orderColumn = $columnsMap[$orderColIndex] ?? 'name';

        $rows = $query->orderBy($orderColumn, $orderDir)
            ->skip($start)
            ->take($length)
            ->get(['id', 'name', 'category_id', 'price_1']);

        $data = [];
        foreach ($rows as $row) {
            $categoryName = $row->categories->name ?? '';
            $price = $row->price_1 ?? '';
            $action = '<button type="button" class="btn btn-sm btn-outline-primary btn-add-product" data-id="'.$row->id.'" data-name="'.htmlspecialchars($row->name, ENT_QUOTES).'">'.__('Add').'</button>';
            $data[] = ['select' => $action, 'name' => $row->name, 'category' => $categoryName, 'price' => $price];
        }

        return response()->json([
            'draw' => (int)$request->input('draw', 0),
            'recordsTotal' => $recordsTotal,
            'recordsFiltered' => $recordsFiltered,
            'data' => $data,
        ]);
    }
}
