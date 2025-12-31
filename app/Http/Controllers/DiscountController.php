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
        $discount = Discount::create($request->validated());

        if ($discount->type === 'quantity_product') {
            $discount->products()->attach($request->products_id ?? []);
        } else {
            $existsOtherActive = Discount::where('type', $discount->type)
                ->where('status', Discount::ACTIVE)
                ->where('id', '!=', $discount->id)
                ->exists();

            if ($existsOtherActive) {
                $discount->status = Discount::INACTIVE;
                $discount->save();
            }
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
        $discount->fill($request->validated());
        $discount->save();
        $discount->products()->sync($request->products_id ?? []);

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
}
