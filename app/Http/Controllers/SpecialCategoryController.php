<?php

namespace App\Http\Controllers;

use App\Http\Requests\SpecialCategoryRequest;
use App\Models\Category;
use App\Models\SpecialCategory;
use App\Models\SpecialCategoryDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class SpecialCategoryController extends Controller
{
    public function index()
    {
        $categories = DB::table('special_categories')
            ->select(
                'special_categories.id',
                'special_categories.name',
                'special_categories.order',
                'special_categories.status',
                'special_categories.slider_quantity',
                'special_categories.tipo_order',
                'special_categories.tipo_special',
                'special_categories.slug'
            )
            ->orderBy('status', 'desc')
            ->orderBy('order', 'asc')
            ->whereNull('special_categories.deleted_at')
            ->get();

        $normalCategories = Category::select('id', 'name', 'name_english')
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
                },
            ])
            ->orderBy('name', 'DESC')
            ->get();

        $products = DB::table('products')
            ->select(
                'products.id',
                'products.name',
                'products.category_id',
                'products.subcategory_id',
                'products.subsubcategory_id'
            )
            ->where('status', '1')
            ->get();

        return view('panel.special-categories.index')->with([
            'categories' => $categories,
            'products' => $products,
            'normalCategories' => $normalCategories,
        ]);
    }

    public function store(SpecialCategoryRequest $request)
    {
        $category = SpecialCategory::create($request->all());
        $category->slug = Str::slug($request->name);
        $category->save();

        $this->syncProducts($category->id, $request->products);
        $this->reorderExcept($category->id, (int) $request->order);

        return response()->json(['ok' => true]);
    }

    public function update(SpecialCategoryRequest $request, $id)
    {
        $category = SpecialCategory::findOrFail($id);
        $category->fill($request->all());
        $category->slug = Str::slug($request->name);
        $category->save();

        SpecialCategoryDetail::where('special_category_id', $category->id)->delete();
        $this->syncProducts($category->id, $request->products);
        $this->reorderExcept($category->id, (int) $request->order);

        return response()->json(['ok' => true]);
    }

    public function destroy($id)
    {
        SpecialCategory::where('id', $id)->delete();

        $categories = SpecialCategory::orderBy('order', 'asc')->get();
        $num = 0;
        foreach ($categories as $category) {
            $num++;
            $category->order = $num;
            $category->save();
        }

        return redirect()->back();
    }

    public function status($id)
    {
        $category = SpecialCategory::findOrFail($id);
        $category->status = $category->status == 1 ? 0 : 1;
        $category->save();

        return response()->json([
            'status' => $category->status,
        ]);
    }

    public function detail($id)
    {
        $categories = SpecialCategory::with(['products' => function ($q) {
            $q->select('products.id');
        }])
            ->orderBy('order', 'asc')
            ->where('id', $id)
            ->get();

        return response()->json([
            'categories' => $categories,
        ]);
    }

    private function syncProducts(int $specialCategoryId, ?string $productsJson): void
    {
        if (!$productsJson) {
            return;
        }

        $decoded = json_decode($productsJson, true);
        if (!is_array($decoded)) {
            return;
        }

        foreach ($decoded as $productId) {
            $detail = new SpecialCategoryDetail();
            $detail->product_id = $productId;
            $detail->special_category_id = $specialCategoryId;
            $detail->save();
        }
    }

    private function reorderExcept(int $specialCategoryId, int $desiredOrder): void
    {
        $categories = SpecialCategory::where('id', '!=', $specialCategoryId)
            ->orderBy('order', 'asc')
            ->get();

        $num = 0;
        foreach ($categories as $category) {
            $num++;
            if ($desiredOrder > 0 && $num == $desiredOrder) {
                $num++;
            }
            $category->order = $num;
            $category->save();
        }
    }
}
