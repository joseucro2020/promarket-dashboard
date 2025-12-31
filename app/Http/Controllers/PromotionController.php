<?php

namespace App\Http\Controllers;

use App\Libraries\SetNameImage;
use App\Http\Requests\PromotionRequest;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductAmount;
use App\Models\Promotion;
use App\Models\PromotionProduct;
use App\Models\Subcategory;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;

class PromotionController extends Controller
{
    private $width_file = 550;
    private $height_file = 800;

    public function index()
    {
        $promotions = Promotion::with(['products.product_amount.product'])
            ->orderBy('order', 'desc')
            ->orderBy('id', 'desc')
            ->get();

        foreach ($promotions as $promotion) {
            foreach ($promotion->products as $product) {
                if (isset($product->product_amount)) {
                    if ($promotion->status != Promotion::STATUS_SOLD_OUT) {
                        $promotion->status = $product->product_amount->amount >= $product->amount
                            ? $promotion->status
                            : Promotion::STATUS_SOLD_OUT;
                    }
                }
            }
        }

        return view('panel.promotions.index', compact('promotions'));
    }

    public function create()
    {
        $categories = $this->loadCategories();

        return view('panel.promotions.form', [
            'categories' => $categories,
            'selectedProducts' => [],
        ]);
    }

    public function store(PromotionRequest $request)
    {
        $promotion = new Promotion($request->validated());
        $promotion->image = null;
        $promotion->order = $request->input('order', 0);
        $promotion->status = Promotion::STATUS_INACTIVE;

        if ($request->hasFile('image')) {
            $promotion->image = $this->storeImage($request->file('image'));
        }

        $promotion->save();

        $this->syncProducts($promotion, $request->products);

        return redirect()->route('promotions.index')->with('success', __('Promotion saved successfully.'));
    }

    public function edit(Promotion $promotion)
    {
        $categories = $this->loadCategories();

        $promotion->load('products.product_amount.product');

        $selectedProducts = $promotion->products->map(function ($product) {
            return [
                'id' => $product->product_id,
                'total' => $product->amount,
                'name' => optional($product->product_amount->product)->name ?? __('Product'),
                'presentation' => optional($product->product_amount)->presentation,
                'available' => optional($product->product_amount)->amount,
            ];
        })->toArray();

        return view('panel.promotions.form', [
            'categories' => $categories,
            'promotion' => $promotion,
            'selectedProducts' => $selectedProducts,
        ]);
    }

    public function update(PromotionRequest $request, Promotion $promotion)
    {
        $promotion->fill($request->validated());
        $promotion->order = $request->input('order', $promotion->order);

        if ($request->hasFile('image')) {
            $promotion->image = $this->storeImage($request->file('image'));
        } else {
            $promotion->image = $request->input('current_image', $promotion->image);
        }

        $promotion->save();

        $this->syncProducts($promotion, $request->products);

        return redirect()->route('promotions.index')->with('success', __('Promotion updated successfully.'));
    }

    public function destroy(Promotion $promotion)
    {
        $promotion->delete();

        return redirect()->route('promotions.index')->with('success', __('Promotion deleted successfully.'));
    }

    public function status(Promotion $promotion)
    {
        $data = $promotion->load('products.product_amount.product');

        foreach ($data->products as $product) {
            if ($product->amount > optional($product->product_amount)->amount) {
                return response()->json([
                    'message' => 'El producto: ' . optional($product->product_amount->product)->en_name . ' ya no cuenta con el stock suficiente'
                ], 422);
            }
        }

        if ($promotion->status == Promotion::STATUS_INACTIVE) {
            $now = Carbon::now();
            $end = new Carbon($promotion->end_date);

            if ($end->isBefore($now)) {
                return response()->json([
                    'message' => 'No se puede activar la promoción debido a que la fecha de finalización ya ha pasado'
                ], 422);
            }

            $promotion_products = $promotion->products->map(function ($q) {
                return optional($q->product_amount->product)->id;
            })->filter()->values();

            $productsWithOffersActive = Product::whereIn('id', $promotion_products)
                ->whereHas('offersActive')
                ->exists();

            if ($productsWithOffersActive) {
                return response()->json([
                    'message' => 'No se puede activar la promoción debido tiene productos agregados que ya tienen un oferta activa'
                ], 422);
            }

            $productsWithDiscountsActive = Product::whereIn('id', $promotion_products)
                ->whereHas('discountsActive')
                ->exists();

            if ($productsWithDiscountsActive) {
                return response()->json([
                    'message' => 'No se puede activar la promoción debido tiene productos agregados que ya tienen un descuento activo'
                ], 422);
            }
        }

        $promotion->status = $promotion->status == Promotion::STATUS_ACTIVE
            ? Promotion::STATUS_INACTIVE
            : Promotion::STATUS_ACTIVE;

        $promotion->save();

        return response()->json($promotion);
    }

    public function updateOrder(Request $request, Promotion $promotion)
    {
        $promotion->order = (int) $request->input('order', 0);
        $promotion->save();

        return response()->json([
            'result' => true,
            'order' => $promotion->order,
        ]);
    }

    public function getsubcategory(Request $request, $id)
    {
        $subCategorys = Subcategory::where('category_id', $id)
            ->where('status', '1')
            ->with(['sub_subcategories' => function ($query) {
                $query->select('id', 'name', 'subcategory_id')->where('status', '1');
            }])
            ->get();

        $subCategoryArray = $subCategorys->map(function ($subCategory) {
            return [
                'id' => $subCategory->id,
                'name' => $subCategory->name,
                'sub_subcategories' => $subCategory->sub_subcategories->map(function ($subSub) {
                    return [
                        'id' => $subSub->id,
                        'name' => $subSub->name,
                    ];
                })->toArray(),
            ];
        })->toArray();

        $productRequest = new Request();
        $productRequest->merge(['category_id' => $id]);
        $products = $this->fetchProductAmounts($productRequest);

        return response()->json([
            'subcategory' => $subCategoryArray,
            'products' => $products,
        ]);
    }

    public function getproducts(Request $request)
    {
        return response()->json([
            'data' => $this->fetchProductAmounts($request),
        ]);
    }

    protected function loadCategories()
    {
        return Category::select('id', 'name', 'name_english')
            ->where('status', '1')
            ->with([
                'subcategories' => function ($sql) {
                    $sql->select('id', 'name', 'name_english', 'category_id')
                        ->where('status', '1')
                        ->with(['sub_subcategories' => function ($r) {
                            $r->select('id', 'name', 'subcategory_id')
                                ->where('status', '1');
                        }]);
                },
                'sizes' => function ($sizes) {
                    $sizes->select('category_sizes.id', 'name');
                },
            ])
            ->orderBy('name', 'DESC')
            ->get();
    }

    protected function storeImage($image)
    {
        $directory = public_path('img/promotions');

        if (!File::exists($directory)) {
            File::makeDirectory($directory, 0755, true);
        }

        $name = SetNameImage::set($image->getClientOriginalName(), $image->getClientOriginalExtension());
        $image->move($directory, $name);

        return 'img/promotions/' . $name;
    }

    protected function syncProducts(Promotion $promotion, ?string $payload)
    {
        $decoded = json_decode($payload, true);

        if (!is_array($decoded)) {
            return;
        }

        PromotionProduct::where('promotion_id', $promotion->id)->delete();

        foreach ($decoded as $product) {
            if (empty($product['id']) || empty($product['total'])) {
                continue;
            }

            PromotionProduct::create([
                'promotion_id' => $promotion->id,
                'product_id' => $product['id'],
                'amount' => (int) $product['total'],
            ]);
        }
    }

    protected function fetchProductAmounts(Request $request)
    {
        $categoryId = $request->get('category_id');
        $subcategoryId = $request->get('subcategory_id');
        $subsubcategoryId = $request->get('subsubcategory_id');

        $blockedProductIds = Product::whereHas('offersActive')->pluck('id')->toArray();
        $blockedProductIds = array_unique(array_merge($blockedProductIds, Product::whereHas('discountsActive')->pluck('id')->toArray()));

        $query = ProductAmount::select(
            'product_amount.id',
            'product_amount.presentation',
            'product_amount.price',
            'product_amount.cost',
            'product_amount.unit',
            'product_amount.amount',
            'product_colors.product_id',
            'products.name'
        )
            ->join('product_colors', 'product_amount.product_color_id', '=', 'product_colors.id')
            ->join('products', 'product_colors.product_id', '=', 'products.id')
            ->when($categoryId, function ($q) use ($categoryId) {
                $q->where('products.category_id', $categoryId);
            })
            ->when($subcategoryId, function ($q) use ($subcategoryId) {
                $q->where('products.subcategory_id', $subcategoryId);
            })
            ->when($subsubcategoryId, function ($q) use ($subsubcategoryId) {
                $q->where('products.subsubcategory_id', $subsubcategoryId);
            })
            ->where('products.status', '1')
            ->whereNull('product_amount.deleted_at')
            ->where(function ($q) use ($blockedProductIds) {
                if (!empty($blockedProductIds)) {
                    $q->whereNotIn('product_colors.product_id', $blockedProductIds);
                }
            })
            ->orderBy('products.id', 'DESC')
            ->get()
            ->map(function ($item) {
                return [
                    'id' => $item->id,
                    'name' => $item->name,
                    'presentation' => $item->presentation,
                    'price' => $item->price,
                    'cost' => $item->cost,
                    'unit' => $item->unit,
                    'amount' => $item->amount,
                ];
            });

        return $query;
    }
}
