<?php

namespace App\Http\Controllers;

use App\Http\Requests\SpecialCategoryRequest;
use App\Models\Category;
use App\Models\SpecialCategory;
use App\Models\SpecialCategoryDetail;
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

        return view('panel.special-categories.index')->with([
            'categories' => $categories,
        ]);
    }

    public function create()
    {
        return view('panel.special-categories.form', $this->getFormViewData());
    }

    public function edit($id)
    {
        $category = SpecialCategory::with(['products' => function ($query) {
            $query->select('products.id');
        }])->findOrFail($id);

        return view('panel.special-categories.form', $this->getFormViewData($category));
    }

    public function store(SpecialCategoryRequest $request)
    {
        $payload = $request->all();
        $payload['tipo_special'] = $this->normalizeSpecialTypeValue($request->input('tipo_special'));
        $payload['tipo_order'] = $this->normalizeOrderTypeValue($request->input('tipo_order'));

        $category = SpecialCategory::create($payload);
        $category->slug = Str::slug($request->name);
        $category->save();

        $this->syncProducts($category->id, $request->products);
        $this->reorderExcept($category->id, (int) $request->order);

        return redirect()
            ->route('special-categories.index')
            ->with('success', __('locale.Special category saved successfully.'));
    }

    public function update(SpecialCategoryRequest $request, $id)
    {
        $category = SpecialCategory::findOrFail($id);
        $payload = $request->all();
        $payload['tipo_special'] = $this->normalizeSpecialTypeValue($request->input('tipo_special'));
        $payload['tipo_order'] = $this->normalizeOrderTypeValue($request->input('tipo_order'));

        $category->fill($payload);
        $category->slug = Str::slug($request->name);
        $category->save();

        SpecialCategoryDetail::where('special_category_id', $category->id)->delete();
        $this->syncProducts($category->id, $request->products);
        $this->reorderExcept($category->id, (int) $request->order);

        return redirect()
            ->route('special-categories.index')
            ->with('success', __('locale.Special category updated successfully.'));
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

    private function getFormViewData(?SpecialCategory $specialCategory = null): array
    {
        $normalCategories = Category::select('id', 'name', 'name_english')
            ->where('status', '1')
            ->with([
                'subcategories' => function ($sql) {
                    $sql->select('id', 'name', 'name_english', 'category_id')
                        ->with(['sub_subcategories' => function ($query) {
                            $query->select('id', 'name', 'subcategory_id')
                                ->where('status', '1');
                        }])
                        ->where('status', '1');
                },
            ])
            ->orderBy('name', 'ASC')
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
            ->orderBy('name', 'ASC')
            ->get();

        return [
            'specialCategory' => $specialCategory,
            'nextSpecialCategoryOrder' => SpecialCategory::query()->count() + 1,
            'normalCategories' => $normalCategories,
            'products' => $products,
            'selectedSpecialType' => $this->normalizeSpecialTypeValue(optional($specialCategory)->tipo_special),
            'selectedOrderType' => $this->normalizeOrderTypeValue(optional($specialCategory)->tipo_order),
            'specialTypeOptions' => $this->buildOptionMap(
                [
                    '1' => __('locale.Special Category Default'),
                    '2' => __('locale.Special Category Offers'),
                    '3' => __('locale.Special Category New'),
                    '4' => __('locale.Special Category Best Sellers'),
                ],
                [],
                $this->normalizeSpecialTypeValue(optional($specialCategory)->tipo_special)
            ),
            'orderTypeOptions' => $this->buildOptionMap(
                [
                    '1' => __('locale.Fixed'),
                    '2' => __('locale.Random'),
                ],
                [],
                $this->normalizeOrderTypeValue(optional($specialCategory)->tipo_order)
            ),
        ];
    }

    private function buildOptionMap(array $defaults, array $dbValues, ?string $currentValue = null): array
    {
        $options = $defaults;

        foreach ($dbValues as $value) {
            if (!array_key_exists($value, $options)) {
                $options[$value] = $this->humanizeOptionValue($value);
            }
        }

        if ($currentValue && !array_key_exists($currentValue, $options)) {
            $options[$currentValue] = $this->humanizeOptionValue($currentValue);
        }

        return $options;
    }

    private function humanizeOptionValue(string $value): string
    {
        return Str::headline(str_replace(['_', '-'], ' ', $value));
    }

    private function normalizeSpecialTypeValue($value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        $normalized = trim((string) $value);

        $map = [
            '1' => '1',
            '2' => '2',
            '3' => '3',
            '4' => '4',
            'Categorias Especial' => '1',
            'Categorias Ofertas' => '2',
            'Categorias Nuevos' => '3',
            'Categorias Mas Vendidos' => '4',
        ];

        return $map[$normalized] ?? null;
    }

    private function normalizeOrderTypeValue($value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        $normalized = trim((string) $value);

        $map = [
            '1' => '1',
            '2' => '2',
            'Fijo' => '1',
            'Aleatorio' => '2',
            'Manual' => '1',
            'Secuencial' => '1',
        ];

        return $map[$normalized] ?? null;
    }
}
