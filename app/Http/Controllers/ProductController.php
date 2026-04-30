<?php

namespace App\Http\Controllers;

use App\Libraries\SetNameImage;
use App\Exports\ProductsExport;
use Illuminate\Http\Request;
use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;
use App\Models\Category;
use App\Models\Subsubcategories;
use App\Models\Collection;
use App\Models\Design;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\ProductColor;
use App\Models\ProductAmount;
use App\Models\Subcategory;
use App\Models\Taxe;
use App\Models\ProductCategory;
use App\Models\ProductSubcategory;
use App\Models\Tag;
use App\Models\ProductTag;
use App\Models\Supplier;
use App\Models\ProductProveedor;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\File;
use Illuminate\Validation\ValidationException;
use Carbon\Carbon;
use Maatwebsite\Excel\Facades\Excel;

class ProductController extends Controller
{
    private $width_file = 550;
    private $height_file = 800;

    public function index(Request $request)
    {
        if ($request->ajax() || $request->has('draw')) {
            $baseQuery = $this->buildIndexQuery($request, false);
            $query = $this->buildIndexQuery($request, true);

            $totalRecords = $baseQuery->count();
            $filteredRecords = $query->count();

            // Order
            $this->applySorting($query, $request);

            // Pagination
            $start = $request->input('start', 0);
            $length = $request->input('length', 10);
            $products = $query->skip($start)->take($length)->get();
            
            $data = $this->transformProductsForDataTable($products);
            // dd($data);

            return response()->json([
                'draw' => intval($request->input('draw')),
                'recordsTotal' => $totalRecords,
                'recordsFiltered' => $filteredRecords,
                'data' => $data
            ]);
        }

        return $this->loadIndexView();
    }

    public function create()
    {
        $categories = Category::all();
        $taxes = Taxe::where('status', Taxe::STATUS_ACTIVE)->get();
        $tags = Tag::orderBy('name', 'asc')->get();
        return view('panel.products.create', compact('categories', 'taxes', 'tags'));
    }

    public function exportExcel(Request $request)
    {
        try {
            $request->merge([
                'search' => ['value' => $request->input('search', '')],
            ]);

            $products = $this->buildIndexQuery($request, true)
                ->with([
                    'categories:id,name,name_english',
                    'subcategories:id,name,name_english,category_id',
                    'colors.amounts' => function ($query) {
                        $query->whereNull('deleted_at');
                    },
                    'tags:id,name',
                    'supplier:id,nombre_prove',
                ])
                ->orderBy('products.status', 'desc')
                ->orderBy('products.id', 'desc')
                ->get();

            $today = now()->format('d-m-Y h:i A');
            $timestamp = now()->format('d-m-Y(His)');
            $fileName = 'Reporte-Productos-' . $timestamp . '.xlsx';

            try {
                if (class_exists(Excel::class) && class_exists(ProductsExport::class)) {
                    return Excel::download(new ProductsExport($products, $today), $fileName);
                }
            } catch (\Throwable $exception) {
                Log::error('Products XLSX export failed, fallback CSV', [
                    'message' => $exception->getMessage(),
                    'trace' => $exception->getTraceAsString(),
                ]);
            }

            return $this->exportProductsCsv($products, $timestamp);
        } catch (\Throwable $exception) {
            Log::error('Products export failed with full dataset, using emergency fallback', [
                'message' => $exception->getMessage(),
                'trace' => $exception->getTraceAsString(),
            ]);

            $timestamp = now()->format('d-m-Y(His)');
            $fallbackProducts = Product::query()
                ->select('id', 'name', 'variable', 'price_1', 'price_2', 'company_id', 'created_at', 'updated_at')
                ->orderByDesc('id')
                ->limit(5000)
                ->get();

            return response()->streamDownload(function () use ($fallbackProducts) {
                $output = fopen('php://output', 'w');
                fwrite($output, "\xEF\xBB\xBF");

                fputcsv($output, [
                    'ID',
                    'Nombre',
                    'Tipo',
                    'Costo',
                    'Precio',
                    'Compañía',
                    'Fecha ingreso',
                    'Fecha modificación',
                ]);

                foreach ($fallbackProducts as $item) {
                    fputcsv($output, [
                        $item->id,
                        $item->name,
                        $this->getProductTypeLabel((int) $item->variable),
                        number_format((float) $item->price_2, 2, '.', ','),
                        number_format((float) $item->price_1, 2, '.', ','),
                        $item->company_id ?? '-',
                        optional($item->created_at)->format('d-m-Y H:i'),
                        optional($item->updated_at)->format('d-m-Y H:i'),
                    ]);
                }

                fclose($output);
            }, 'Reporte-Productos-' . $timestamp . '-fallback.csv', [
                'Content-Type' => 'text/csv; charset=UTF-8',
            ]);
        }
    }

    private function exportProductsCsv($products, string $timestamp)
    {
        $fileName = 'Reporte-Productos-' . $timestamp . '.csv';

        return response()->streamDownload(function () use ($products) {
            $output = fopen('php://output', 'w');
            fwrite($output, "\xEF\xBB\xBF");

            fputcsv($output, [
                'Código del Producto', 'Nombre', 'Presentación', 'Tipo', 'Existencia', 'Costo Unitario',
                'Umbral', 'Mín. Venta', 'Máx. Venta', 'Precio ($)', 'Ganancia ($)', '% Utilidad',
                'ID Categoría', 'Categoría', 'ID Subcategoría', 'Subcategoría', 'Referencia', 'SKU',
                'Tags', 'Proveedor', 'Fecha ingreso', 'Fecha modificación', 'Proveedor', 'Padre', 'Compañía',
            ]);

            foreach ($products as $item) {
                $presentations = $item->colors->flatMap(function ($color) {
                    return $color->amounts;
                });

                $tags = $item->tags->pluck('name')->implode(', ');
                $supplier = $item->supplier->pluck('nombre_prove')->filter()->implode(', ');
                $supplier = $supplier !== '' ? $supplier : '-';

                foreach ($presentations as $product) {
                    $price = (int) $item->variable === Product::TYPE_VARIABLE ? (float) $product->price : (float) $item->price_1;
                    $cost = (float) $product->cost;
                    $ganancia = $price - $cost;
                    $porcentaje = $cost > 0 ? number_format(($ganancia / $cost) * 100, 2, '.', ',') : 0;

                    fputcsv($output, [
                        $item->id,
                        $item->name,
                        (int) $item->variable === Product::TYPE_VARIABLE ? ($product->presentation ?? '') : '',
                        $item->type_variable,
                        $product->amount,
                        number_format($cost, 2, '.', ','),
                        $product->umbral,
                        $product->min,
                        $product->max,
                        number_format($price, 2, '.', ','),
                        number_format($ganancia, 2, '.', ','),
                        $porcentaje,
                        optional($item->categories)->id ?? '',
                        optional($item->categories)->name ?? '',
                        optional($item->subcategories)->id ?? '',
                        optional($item->subcategories)->name ?? '',
                        $product->id,
                        $product->sku,
                        $tags,
                        $supplier,
                        $item->es_date,
                        $item->es_update,
                        $supplier,
                        '-',
                        $item->company_id ?? '-',
                    ]);
                }
            }

            fclose($output);
        }, $fileName, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    public function store(StoreProductRequest $request)
    {
        try {
            Log::info('Product store request data: ' . json_encode($request->all()));
            $this->ensureValidImageUploads($request);
            DB::transaction(function () use ($request) {
                $product = new Product;
                $this->fillProductData($product, $request);
                $product->status = '1';
                $product->save();

                $this->syncExtraRelations($product, $request);

                // Create default color
                $color = ProductColor::create([
                    'name' => 'por defecto',
                    'name_english' => 'default',
                    'product_id' => $product->id,
                ]);

                // Create default amount (stock)
                ProductAmount::create([
                    'amount' => 0,
                    'product_color_id' => $color->id,
                    'category_size_id' => 1, // Default size ID
                    'unit' => 1,
                    'presentation' => 0,
                    'price' => $request->price_1,
                    'cost' => $request->price_2,
                    'min' => $request->input('retail', 1),
                    'max' => $request->input('wholesale', 1),
                    'umbral' => $request->input('umbral', 0),
                    'sku' => $request->input('sku', ''),
                ]);

                $this->handleImageUpload($request, $product);
            });

            return redirect()->route('products.index')->with('success', __('Product created successfully.'));
        } catch (\Exception $e) {
            Log::error('Error creating product: ' . $e->getMessage() . '\n' . $e->getTraceAsString());
            return redirect()->back()->with('error', __('Error creating product: ') . $e->getMessage())->withInput();
        }
    }

    public function edit($id)
    {
        $product = Product::with(['images', 'colors.amounts', 'secondary_categories', 'secondary_subcategories'])->findOrFail($id);
        $categories = Category::all();
        $taxes = Taxe::where('status', Taxe::STATUS_ACTIVE)->get();
        $tags = Tag::orderBy('name', 'asc')->get();
        return view('panel.products.edit', compact('product', 'categories', 'taxes', 'tags'));
    }

    public function update(UpdateProductRequest $request, $id)
    {
        try {
            Log::info('Product update request id: ' . $id . ' data: ' . json_encode($request->all()));
            $this->ensureValidImageUploads($request);
            DB::transaction(function () use ($request, $id) {
                $product = Product::findOrFail($id);
                Log::info('Product before update: ' . json_encode($product->toArray()));
                $this->fillProductData($product, $request);
                $product->save();
                $this->syncExtraRelations($product, $request);
                Log::info('Product after update: ' . json_encode($product->toArray()));

                $this->handlePresentations($request, $product);
                $this->handleImageUpload($request, $product, true);
            });

            return redirect()->route('products.index')->with('success', __('Product updated successfully.'));
        } catch (\Exception $e) {
            Log::error('Error updating product: ' . $e->getMessage() . '\n' . $e->getTraceAsString());
            return redirect()->back()->with('error', __('Error updating product: ') . $e->getMessage())->withInput();
        }
    }

    private function handlePresentations(Request $request, Product $product)
    {
        $color = $product->colors()->first();
        if (!$color) {
            $color = ProductColor::create([
                'name' => 'por defecto',
                'name_english' => 'default',
                'product_id' => $product->id,
            ]);
        }

        // Simple/Bulk products do not submit the `presentations` array from the form.
        // Persist their editable stock fields on the default amount row instead.
        if ((int) $product->variable !== Product::TYPE_VARIABLE) {
            $amount = $color->amounts()->first();

            if (!$amount) {
                $amount = new ProductAmount();
                $amount->product_color_id = $color->id;
                $amount->category_size_id = 1;
                $amount->unit = 1;
                $amount->amount = 0;
                $amount->presentation = 0;
            }

            $amount->price = $request->input('price_1', $amount->price ?? 0);
            $amount->cost = $request->input('price_2', $amount->cost ?? 0);
            $amount->min = $request->input('retail', $amount->min);
            $amount->max = $request->input('wholesale', $amount->max);
            $amount->umbral = $request->input('umbral', $amount->umbral);
            $amount->sku = $request->input('sku', $amount->sku);
            $amount->save();

            return;
        }

        $submittedIds = [];

        $presentations = $request->input('presentations', []);

        // Avoid deleting existing rows when the edit form does not submit presentations.
        if (!is_array($presentations) || empty($presentations)) {
            Log::warning('Skipping product presentations sync because request payload is empty.', [
                'product_id' => $product->id,
                'variable' => $product->variable,
            ]);

            return;
        }

        foreach ($presentations as $data) {
            if (!is_array($data)) {
                continue;
            }

            if (
                !array_key_exists('presentation', $data)
                || !array_key_exists('amount', $data)
                || !array_key_exists('price', $data)
                || !array_key_exists('cost', $data)
            ) {
                continue;
            }

                $amountData = [
                    'product_color_id' => $color->id,
                    'category_size_id' => $data['unit'] ?? 1,
                    'unit' => $data['unit'] ?? 1,
                    'presentation' => $data['presentation'],
                    'amount' => $data['amount'],
                    'price' => $data['price'],
                    'cost' => $data['cost'],
                    'min' => $data['min'],
                    'max' => $data['max'],
                    'umbral' => $data['umbral'],
                    'sku' => $data['sku'],
                ];

                if (isset($data['id'])) {
                    $amount = ProductAmount::where('product_color_id', $color->id)
                        ->find($data['id']);
                    if ($amount) {
                        $amount->update($amountData);
                        $submittedIds[] = $amount->id;
                    }
                } else {
                    $amount = ProductAmount::create($amountData);
                    $submittedIds[] = $amount->id;
                }
        }

        if (empty($submittedIds)) {
            Log::warning('Skipping product presentations delete because no valid presentation rows were submitted.', [
                'product_id' => $product->id,
                'variable' => $product->variable,
            ]);

            return;
        }

        // Delete removed presentations (soft delete if model uses SoftDeletes)
        $color->amounts()->whereNotIn('id', $submittedIds)->delete();
    }

    private function syncExtraRelations(Product $product, Request $request)
    {
        $secondaryCategoryIds = array_values(array_unique(array_filter((array) $request->input('secondary_categories', []))));
        $secondarySubcategoryIds = array_values(array_unique(array_filter((array) $request->input('secondary_subcategories', []))));
        $tagIds = array_values(array_unique(array_filter((array) $request->input('tags', []))));

        $product->secondary_categories()->sync($secondaryCategoryIds);
        $product->secondary_subcategories()->sync($secondarySubcategoryIds);
        $product->tags()->sync($tagIds);
    }

    public function destroy($id)
    {
        $product = Product::findOrFail($id);
        $product->status = '2'; // Soft delete status
        $product->save();
        return redirect()->route('products.index')->with('success', __('Product deleted successfully.'));
    }

    public function status($id)
    {
        $product = Product::findOrFail($id);
        if ($product->status === '2') {
            return response()->json(['message' => __('An error occurred')], 422);
        }

        $product->status = $product->status === '1' ? '0' : '1';
        $product->save();

        return response()->json($product);
    }

    public function getSubcategories($id)
    {
        $subcategories = Subcategory::where('category_id', $id)
            ->where('status', '1')
            ->with(['sub_subcategories' => function ($query) {
                $query->select('id', 'name', 'subcategory_id')->where('status', '1');
            }])
            ->get(['id', 'name', 'category_id']);

        $subcategoriesArray = $subcategories->map(function ($subCategory) {
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

        return response()->json([
            'subcategory' => $subcategoriesArray,
        ]);
    }

    /**
     * Actualiza la utilidad y precio de una presentación (ProductAmount) vía AJAX.
     * Request esperado: amount[price] (numeric), amount[utilidad] (int 0-100)
     */
    public function updateUtilidad(Request $request, $id)
    {
        $validator = \Validator::make($request->all(), [
            'amount.price' => 'required|numeric',
            'amount.utilidad' => 'required|integer|min:0|max:100',
        ]);

        if ($validator->fails()) {
            return response()->json(['result' => false, 'errors' => $validator->errors()], 422);
        }

        try {
            return DB::transaction(function() use ($request, $id) {
                $ProductAmount = ProductAmount::with(['product_color', 'product'])->findOrFail($id);

                $price = $request->input('amount.price');
                $utilidad = intval($request->input('amount.utilidad'));

                $ProductAmount->price = $price;
                $ProductAmount->utilidad = $utilidad;
                $ProductAmount->save();

                if ($ProductAmount->product) {
                    $Product = Product::find($ProductAmount->product->id);
                    if ($Product) {
                        $Product->price_1 = $price;
                        $Product->price_2 = $price;
                        $Product->save();
                    }
                }

                return response()->json(['result' => true, 'ProductAmount' => $ProductAmount], 200);
            });
        } catch (\Exception $e) {
            Log::error('Error updating utilidad: ' . $e->getMessage());
            return response()->json(['result' => false, 'message' => 'Error actualizando utilidad'], 500);
        }
    }

    public function getProducts(Request $request)
    {
        if ($request->has('draw')) {
            return $this->index($request);
        }

        $searchValue = $request->input('search');
        if (is_array($searchValue)) {
            $searchValue = $searchValue['value'] ?? null;
        }

        if (!empty($searchValue) && !$request->filled('search.value')) {
            $request->merge([
                'search' => ['value' => $searchValue],
            ]);
        }

        $productsQuery = $this->buildIndexQuery($request, true)
            ->with([
                'colors' => function ($colors) use ($request) {
                    $colors->select('id', 'name', 'name_english', 'product_id')
                        ->where('status', '1')
                        ->with([
                            'amounts' => function ($q) use ($request) {
                                $q->select('id as amount_id', 'amount', 'min', 'max', 'cost', 'umbral', 'price', 'unit', 'presentation', 'product_color_id', 'category_size_id', 'sku')
                                    ->with([
                                        'category_size' => function ($c) {
                                            $c->select('id', 'category_id', 'size_id')
                                                ->with([
                                                    'size' => function ($s) {
                                                        $s->select('id', 'name')
                                                            ->where('status', '1');
                                                    }
                                                ]);
                                        }
                                    ])
                                    ->when($request->filled('inventory'), function ($query) use ($request) {
                                        $inventory = intval($request->inventory);
                                        if ($inventory == 2) {
                                            $query->where('product_amount.amount', '>', 0)
                                                ->whereColumn('product_amount.amount', '<=', 'product_amount.umbral')
                                                ->whereNull('product_amount.deleted_at');
                                        } else if ($inventory == 1) {
                                            $query->where('product_amount.amount', '>', 0)
                                                ->whereColumn('product_amount.amount', '>', 'product_amount.umbral')
                                                ->whereNull('product_amount.deleted_at');
                                        } else {
                                            $query->where('product_amount.amount', 0)
                                                ->whereNull('product_amount.deleted_at');
                                        }
                                    });
                            }
                        ]);
                }
            ])
            ->orderBy('products.id', 'DESC');

        $perPage = intval($request->input('length', 10));
        if ($perPage <= 0) {
            $perPage = 10;
        }

        $products = $productsQuery->paginate($perPage);

        return response()->json([
            'products' => $products
        ]);
    }

    public function indicators(Request $request)
    {
        return response()->json($this->buildProductIndicators($request));
    }

    // --- Private Helper Methods ---

    private function buildIndexQuery(Request $request, bool $applySearch = true)
    {
        $stockSubquery = DB::table('product_colors')
            ->join('product_amount', 'product_amount.product_color_id', '=', 'product_colors.id')
            ->whereColumn('product_colors.product_id', 'products.id')
            ->whereNull('product_amount.deleted_at')
            ->selectRaw('COALESCE(SUM(product_amount.amount), 0)');

        $costSubquery = DB::table('product_colors')
            ->join('product_amount', 'product_amount.product_color_id', '=', 'product_colors.id')
            ->whereColumn('product_colors.product_id', 'products.id')
            ->whereNull('product_amount.deleted_at')
            ->orderByRaw('CASE WHEN product_amount.category_size_id = 1 THEN 0 ELSE 1 END')
            ->orderBy('product_amount.id')
            ->limit(1)
            ->select('product_amount.cost');

        $priceSubquery = DB::table('product_colors')
            ->join('product_amount', 'product_amount.product_color_id', '=', 'product_colors.id')
            ->whereColumn('product_colors.product_id', 'products.id')
            ->whereNull('product_amount.deleted_at')
            ->orderByRaw('CASE WHEN product_amount.category_size_id = 1 THEN 0 ELSE 1 END')
            ->orderBy('product_amount.id')
            ->limit(1)
            ->select('product_amount.price');

        $thresholdSubquery = DB::table('product_colors')
            ->join('product_amount', 'product_amount.product_color_id', '=', 'product_colors.id')
            ->whereColumn('product_colors.product_id', 'products.id')
            ->whereNull('product_amount.deleted_at')
            ->orderByRaw('CASE WHEN product_amount.category_size_id = 1 THEN 0 ELSE 1 END')
            ->orderBy('product_amount.id')
            ->limit(1)
            ->select('product_amount.umbral');

        $query = Product::select('products.*')
            ->selectSub($stockSubquery, 'stock_total')
            ->selectSub($costSubquery, 'cost_from_amount')
            ->selectSub($priceSubquery, 'price_from_amount')
            ->selectSub($thresholdSubquery, 'threshold_from_amount')
            ->with([
                'categories:id,name,name_english',
                'subcategories:id,name,name_english',
                'subsubcategories:id,name,name_english',
                'images',
                'taxe'
            ]);

        $this->applyProductFilters($query, $request, $applySearch);

        return $query;
    }

    private function normalizeSearchRequest(Request $request): void
    {
        $searchValue = $request->input('search');
        if (is_array($searchValue)) {
            $searchValue = $searchValue['value'] ?? null;
        }

        if (!empty($searchValue) && !$request->filled('search.value')) {
            $request->merge([
                'search' => ['value' => $searchValue],
            ]);
        }
    }

    private function hasRequestValue(Request $request, string $key): bool
    {
        if (!$request->exists($key)) {
            return false;
        }

        $value = $request->input($key);

        return !is_null($value) && $value !== '';
    }

    private function applyProductFilters($query, Request $request, bool $applySearch = true, bool $applyDefaultStatusFilter = true)
    {
        $this->normalizeSearchRequest($request);

        // Apply Filters
        if ($this->hasRequestValue($request, 'company')) {
            if ((string) $request->company === '__NULL__') {
                $query->whereNull('products.company_id');
            } else {
                $query->where('products.company_id', $request->company);
            }
        }
        
        if ($this->hasRequestValue($request, 'status')) {
            $query->where('products.status', $request->status);
        } elseif ($applyDefaultStatusFilter) {
            $query->whereIn('products.status', ['0', '1']);
        }

        if ($this->hasRequestValue($request, 'category')) {
            $query->where('products.category_id', $request->category);
        }
        if ($this->hasRequestValue($request, 'subcategory')) {
            $query->where('products.subcategory_id', $request->subcategory);
        }
        if ($this->hasRequestValue($request, 'subsubcategory')) {
            $query->where('products.subsubcategory_id', $request->subsubcategory);
        }
        if ($this->hasRequestValue($request, 'typeProduct')) {
            $query->where('products.variable', $request->typeProduct);
        }

        // Search
        if ($applySearch && $request->filled('search.value')) {
            $searchValue = $request->input('search.value');
            $searchLower = Str::lower(trim($searchValue));
            $searchValueForName = $searchValue;
            $typeFilter = null;

            if (in_array($searchLower, ['simple', 'simple product', 'producto simple'], true)) {
                $typeFilter = Product::TYPE_SIMPLE;
                $searchValueForName = '';
            } elseif (in_array($searchLower, ['variable', 'variable product', 'producto variable'], true)) {
                $typeFilter = Product::TYPE_VARIABLE;
                $searchValueForName = '';
            } elseif (in_array($searchLower, ['bulk', 'bulk product', 'producto a granel'], true)) {
                $typeFilter = Product::TYPE_BULK;
                $searchValueForName = '';
            } else {
                if (Str::contains($searchLower, ['simple product', 'producto simple'])) {
                    $typeFilter = Product::TYPE_SIMPLE;
                    $searchValueForName = trim(str_ireplace(['simple product', 'producto simple'], '', $searchValueForName));
                } elseif (Str::contains($searchLower, ['variable product', 'producto variable'])) {
                    $typeFilter = Product::TYPE_VARIABLE;
                    $searchValueForName = trim(str_ireplace(['variable product', 'producto variable'], '', $searchValueForName));
                } elseif (Str::contains($searchLower, ['bulk product', 'producto a granel'])) {
                    $typeFilter = Product::TYPE_BULK;
                    $searchValueForName = trim(str_ireplace(['bulk product', 'producto a granel'], '', $searchValueForName));
                }
            }

            if ($typeFilter !== null) {
                $query->where('products.variable', $typeFilter);
            }

            if ($searchValueForName !== '') {
                $searchTerms = preg_split('/\s+/', trim($searchValueForName)) ?: [];
                $searchTerms = array_values(array_filter($searchTerms, function ($term) {
                    return $term !== '';
                }));

                if (empty($searchTerms)) {
                    $searchTerms = [$searchValueForName];
                }

                $query->where(function ($outerQuery) use ($searchTerms) {
                    foreach ($searchTerms as $term) {
                        $like = '%' . $term . '%';

                        $outerQuery->where(function ($innerQuery) use ($term, $like) {
                            $innerQuery->where('products.name', 'like', $like)
                                ->orWhere('products.name_english', 'like', $like)
                                ->orWhere('products.description', 'like', $like)
                                ->orWhere('products.description_english', 'like', $like)
                                ->orWhere('products.slug', 'like', $like)
                                ->orWhere('products.id', 'like', $like)
                                ->orWhereHas('categories', function ($categoryQuery) use ($like) {
                                    $categoryQuery->where('name', 'like', $like)
                                        ->orWhere('name_english', 'like', $like);
                                })
                                ->orWhereHas('subcategories', function ($subcategoryQuery) use ($like) {
                                    $subcategoryQuery->where('name', 'like', $like)
                                        ->orWhere('name_english', 'like', $like);
                                })
                                ->orWhereHas('subsubcategories', function ($subsubcategoryQuery) use ($like) {
                                    $subsubcategoryQuery->where('name', 'like', $like)
                                        ->orWhere('name_english', 'like', $like);
                                })
                                ->orWhereHas('colors.amounts', function ($amountQuery) use ($like) {
                                    $amountQuery->where('sku', 'like', $like);
                                });

                            if (is_numeric($term)) {
                                $innerQuery->orWhere('products.id', (int) $term);
                            }
                        });
                    }
                });
            }
        }

        // Inventory Filter
        if ($this->hasRequestValue($request, 'inventory')) {
            $inventory = intval($request->inventory);
            $query->whereHas('colors.amounts', function ($qa) use ($inventory) {
                if ($inventory == 2) { // Con Poca Existencia
                    $qa->where('product_amount.amount', '>', 0)
                       ->whereColumn('product_amount.amount', '<=', 'product_amount.umbral')
                       ->whereNull('product_amount.deleted_at');
                } else if ($inventory == 1) { // Con Existencia
                    $qa->where('product_amount.amount', '>', 0)
                       ->whereColumn('product_amount.amount', '>', 'product_amount.umbral')
                       ->whereNull('product_amount.deleted_at');
                } else { // Agotado
                    $qa->where('product_amount.amount', 0)
                       ->whereNull('product_amount.deleted_at');
                }
            });
        }

        if ((string) $request->input('withoutImages') === '1') {
            $query->whereDoesntHave('images');
        }

        return $query;
    }

    private function buildProductIndicators(Request $request): array
    {
        $baseIndicatorsQuery = Product::query();
        $this->applyProductFilters($baseIndicatorsQuery, $request, true);

        $statusIndicatorsQuery = Product::query();
        $this->applyProductFilters($statusIndicatorsQuery, $request, true, false);

        $lowStockQuery = Product::query();
        $this->applyProductFilters($lowStockQuery, $request, true);
        $lowStockProductsCount = $lowStockQuery
            ->whereHas('colors.amounts', function ($query) {
                $query->where('product_amount.amount', '>', 0)
                    ->whereColumn('product_amount.amount', '<=', 'product_amount.umbral')
                    ->whereNull('product_amount.deleted_at');
            })
            ->count();

        $productsByCompanyCounts = (clone $baseIndicatorsQuery)
            ->selectRaw('COALESCE(products.company_id, 0) as company_id, COUNT(*) as total')
            ->groupBy('products.company_id')
            ->orderByDesc('total')
            ->get()
            ->map(function ($row) {
                return [
                    'company_id' => (string) $row->company_id,
                    'total' => (int) $row->total,
                ];
            })
            ->values();

        $productsByStatusCounts = (clone $statusIndicatorsQuery)
            ->selectRaw('products.status, COUNT(*) as total')
            ->groupBy('products.status')
            ->pluck('total', 'status');

        $productsWithoutImagesCount = (clone $baseIndicatorsQuery)
            ->whereDoesntHave('images')
            ->count();

        return [
            'lowStockProductsCount' => (int) $lowStockProductsCount,
            'productsByCompanyCounts' => $productsByCompanyCounts,
            'productsByStatusCounts' => [
                '0' => (int) ($productsByStatusCounts['0'] ?? 0),
                '1' => (int) ($productsByStatusCounts['1'] ?? 0),
                '2' => (int) ($productsByStatusCounts['2'] ?? 0),
            ],
            'productsWithoutImagesCount' => (int) $productsWithoutImagesCount,
        ];
    }

    private function applySorting($query, Request $request)
    {
        if ($request->has('order')) {
            $orderColumnIndex = $request->input('order.0.column');
            $orderDirection = $request->input('order.0.dir');
            $columns = $request->input('columns');
            $columnName = $columns[$orderColumnIndex]['name'] ?? 'id';
            
            $sortableColumns = ['id', 'name', 'price_1', 'price_2', 'created_at', 'updated_at'];
            if (in_array($columnName, $sortableColumns)) {
                 $query->orderBy('products.'.$columnName, $orderDirection);
            } else {
                 $query->orderBy('products.status', 'desc')
                       ->orderBy('products.id', 'desc');
            }
        } else {
            $query->orderBy('products.status', 'desc')
                  ->orderBy('products.id', 'desc');
        }
    }

    private function transformProductsForDataTable($products)
    {
        $data = [];
        foreach ($products as $product) {
            // dd($product);
            // Calculate profit/percentage
            $costValue = isset($product->cost_from_amount) && !is_null($product->cost_from_amount)
                ? (float) $product->cost_from_amount
                : (float) $product->price_2;
            $priceValue = isset($product->price_from_amount) && !is_null($product->price_from_amount)
                ? (float) $product->price_from_amount
                : (float) $product->price_1;
            $profit = $priceValue - $costValue;
            $percentage = $costValue > 0 ? ($profit / $costValue) * 100 : 0;

            // Calculate Stock
            // Calculate Stock
            $stock = isset($product->stock_total) ? (int) $product->stock_total : 0;

            // Image HTML
            $imageHtml = '<span class="avatar-content">P</span>';
            if ($product->image_url) {
                $imageHtml = '<img src="'.$product->image_url.'" alt="Img" height="32" width="32">';
            }

            // Actions HTML
            $editUrl = route('products.edit', $product->id);
            $deleteUrl = route('products.destroy', $product->id);
            $csrf = csrf_field();
            $method = method_field('DELETE');
            $confirm = __('Are you sure?');
            
            $statusUrl = route('products.status', $product->id, false);
            $checked = $product->status === '1' ? 'checked' : '';
            $disabled = $product->status === '2' ? 'disabled' : '';
            $deletedBadge = $product->status === '2'
                ? '<span class="badge badge-light-secondary mr-1">'.__('Deleted').'</span>'
                : '';
            $actionsHtml = '
                <div class="d-flex align-items-center">
                    '.$deletedBadge.'
                    <div class="custom-control custom-switch custom-switch-success mr-1">
                        <input type="checkbox" class="custom-control-input product-status-toggle" id="product_status_'.$product->id.'" data-url="'.$statusUrl.'" '.$checked.' '.$disabled.' />
                        <label class="custom-control-label" for="product_status_'.$product->id.'"></label>
                    </div>
                    <a href="'.$editUrl.'" class="btn btn-icon btn-flat-success mr-1" data-toggle="tooltip" data-placement="top" title="'.__('Edit').'">
                        <i data-feather="edit"></i>
                    </a>
                    <form action="'.$deleteUrl.'" method="POST" onsubmit="return confirm(\''.$confirm.'\');" class="m-0">
                        '.$csrf.'
                        '.$method.'
                        <button type="submit" class="btn btn-icon btn-flat-danger" data-toggle="tooltip" data-placement="top" title="'.__('Delete').'">
                            <i data-feather="trash"></i>
                        </button>
                    </form>
                </div>';

            $typeValue = (int) $product->variable;
            if ($typeValue === Product::TYPE_VARIABLE) {
                $typeBadgeClass = 'badge-light-primary';
                $typeIcon = 'layers';
                $typeLabel = __('Variable Product');
            } elseif ($typeValue === Product::TYPE_BULK) {
                $typeBadgeClass = 'badge-light-warning';
                $typeIcon = 'archive';
                $typeLabel = __('Bulk Product');
            } else {
                $typeBadgeClass = 'badge-light-secondary';
                $typeIcon = 'box';
                $typeLabel = __('Simple Product');
            }
            $typeHtml = '<span class="badge '.$typeBadgeClass.' d-inline-flex align-items-center">'
                .'<i data-feather="'.$typeIcon.'" class="mr-25"></i>'
                .'<span>'.$typeLabel.'</span>'
                .'</span>';
            $nameHtml = '<div class="d-flex align-items-center">'
                .'<span class="mr-1">'.e($product->name).'</span>'
                .$typeHtml
                .'</div>';

            $data[] = [
                'actions' => $actionsHtml,
                'id' => $product->id,
                'image' => $imageHtml,
                'category' => optional($product->categories)->name ?? '-',
                'name' => $nameHtml,
                'stock' => $stock,
                'threshold' => isset($product->threshold_from_amount) && !is_null($product->threshold_from_amount)
                    ? $product->threshold_from_amount
                    : ($product->threshold ?? '-'),
                'tax' => optional($product->taxe)->name ?? '-',
                'cost' => '$' . number_format($costValue, 2),
                'price' => '$' . number_format($priceValue, 2),
                'profit' => '$' . number_format($profit, 2),
                'percentage' => number_format($percentage, 1) . '%',
                'created_at' => $product->created_at->format('d-m-y h:i A'),
                'updated_at' => $product->updated_at->format('d-m-y h:i A'),
                'pro' => $product->pro ? 'Yes' : 'No'
            ];
        }
        return $data;
    }

    private function loadIndexView()
    {
        $companies = Product::query()
            ->select('company_id')
            ->distinct()
            ->orderByRaw('CASE WHEN company_id IS NULL THEN 0 ELSE 1 END')
            ->orderBy('company_id')
            ->pluck('company_id');
            
        $indicators = $this->buildProductIndicators(request());

        $categories = Category::select('id', 'name', 'name_english')
            ->where('status', '1')
            ->with([
                'subcategories' => function ($query) {
                    $query->select('id', 'name', 'name_english', 'category_id')
                        ->where('status', '1')
                        ->with([
                            'sub_subcategories' => function ($subQuery) {
                                $subQuery->select('id', 'name', 'name_english', 'subcategory_id')
                                    ->where('status', '1');
                            }
                        ]);
                }
            ])
            ->orderBy('name', 'DESC')
            ->get();

        $categoryFilterTree = $categories->map(function ($category) {
            return [
                'id' => $category->id,
                'name' => $category->name,
                'subcategories' => $category->subcategories->map(function ($subcategory) {
                    return [
                        'id' => $subcategory->id,
                        'name' => $subcategory->name,
                        'sub_subcategories' => $subcategory->sub_subcategories->map(function ($subSubcategory) {
                            return [
                                'id' => $subSubcategory->id,
                                'name' => $subSubcategory->name,
                            ];
                        })->values()->all(),
                    ];
                })->values()->all(),
            ];
        })->values();

        $allSubcategories = $categoryFilterTree
            ->flatMap(function ($category) {
                return collect($category['subcategories']);
            })
            ->unique('id')
            ->values();

        $allSubsubcategories = $allSubcategories
            ->flatMap(function ($subcategory) {
                return collect($subcategory['sub_subcategories']);
            })
            ->unique('id')
            ->values();

        $collections = Collection::select('id', 'name', 'name_english')
            ->where('status', '1')
            ->get();

        $designs = Design::select('id', 'name', 'name_english', 'collection_id')
            ->where('status', '1')
            ->get();

        $taxes = Taxe::where('status', Taxe::STATUS_ACTIVE)->get();
        $tags = Tag::orderBy('name', 'asc')->get();
        $supplier = Supplier::orderBy('nombre_prove', 'asc')->get();

        return view('panel.products.index')->with([
            'companies' => $companies,
            'lowStockProductsCount' => $indicators['lowStockProductsCount'],
            'productsByCompanyCounts' => $indicators['productsByCompanyCounts'],
            'productsByStatusCounts' => $indicators['productsByStatusCounts'],
            'productsWithoutImagesCount' => $indicators['productsWithoutImagesCount'],
            'categories' => $categories,
            'collections' => $collections,
            'designs' => $designs,
            'taxes' => $taxes,
            'tags' => $tags,
            'supplier' => $supplier,
            'categoryFilterTree' => $categoryFilterTree,
            'allSubcategories' => $allSubcategories,
            'allSubsubcategories' => $allSubsubcategories,
        ]);
    }

    private function fillProductData(Product $product, Request $request)
    {
        $product->name = $request->name;
        $product->name_english = $request->name_english;
        $product->description = $request->description;
        $product->description_english = $request->description_english;
        $product->slug = Str::slug($request->name);
        $product->category_id = $request->category_id;
        $product->subcategory_id = $request->subcategory_id;
        $product->subsubcategory_id = $request->subsubcategory_id;
        $collectionId = $request->input('collection_id', $product->collection_id);
        if (empty($collectionId)) {
            $collectionId = Collection::query()->where('status', '1')->value('id')
                ?? Collection::query()->value('id');
        }

        if (empty($collectionId)) {
            throw ValidationException::withMessages([
                'collection_id' => __('Please create at least one active collection before creating products.'),
            ]);
        }

        $product->collection_id = $collectionId;
        $product->taxe_id = $request->taxe_id;
        $product->autom = $request->boolean('auto_update') ? 1 : 0;
        $product->retail = $request->filled('retail') ? $request->retail : $product->retail;
        $product->wholesale = $request->filled('wholesale')
            ? $this->normalizeWholesaleFlag($request->input('wholesale'))
            : $product->wholesale;

        $type = $request->input('type');
        if (is_string($type) && $type !== '') {
            switch (Str::lower($type)) {
                case 'variable':
                    $variable = Product::TYPE_VARIABLE;
                    break;
                case 'bulk':
                    $variable = Product::TYPE_BULK;
                    break;
                case 'simple':
                default:
                    $variable = Product::TYPE_SIMPLE;
                    break;
            }
        } else {
            $variable = (int) $request->input('variable', $product->variable ?? Product::TYPE_SIMPLE);
        }
        $product->variable = $variable;

        if ((int) $variable === Product::TYPE_VARIABLE) {
            if ($request->filled('price_1')) {
                $product->price_1 = $request->price_1;
            }
            if ($request->filled('price_2')) {
                $product->price_2 = $request->price_2;
            }
        } else {
            $product->price_1 = $request->price_1;
            $product->price_2 = $request->price_2;
        }

        if ((int) $variable === Product::TYPE_BULK) {
            $product->bulk_unit = $request->input('bulk_unit');
            $product->bulk_min_sale = $request->filled('bulk_min_sale') ? $request->input('bulk_min_sale') : ($product->bulk_min_sale ?? 1.000);
            $product->bulk_step = $request->filled('bulk_step') ? $request->input('bulk_step') : ($product->bulk_step ?? 0.100);
        }

        // Map form fields to DB columns
        if ($request->has('min_stock_deactivate')) {
            $product->minexi = $request->input('min_stock_deactivate');
        }
        if ($request->has('max_stock_activate')) {
            $product->maxexi = $request->input('max_stock_activate');
        }
    }

    private function getProductImageDiskPath(): string
    {
        $path = env('ECOMMERCE_IMAGE_PATH');

        if ($path) {
            return rtrim($path, '\\/');
        }

        return public_path('img/products');
    }

    private function normalizeWholesaleFlag($value): int
    {
        if ($value === null || $value === '') {
            return 0;
        }

        return (float) $value > 0 ? 1 : 0;
    }

    private function ensureValidImageUploads(Request $request): void
    {
        $imageError = $this->getUploadErrorCode('image');
        if ($imageError !== null && $imageError !== UPLOAD_ERR_OK && $imageError !== UPLOAD_ERR_NO_FILE) {
            Log::warning('Invalid main image upload detected.', [
                'product_id' => $request->route('id'),
                'error_code' => $imageError,
            ]);

            throw ValidationException::withMessages([
                'image' => [$this->getUploadErrorMessage($imageError)],
            ]);
        }

        $secondaryErrors = $this->getUploadErrorCodes('secondary_images');
        foreach ($secondaryErrors as $index => $errorCode) {
            if ($errorCode !== UPLOAD_ERR_OK && $errorCode !== UPLOAD_ERR_NO_FILE) {
                Log::warning('Invalid secondary image upload detected.', [
                    'product_id' => $request->route('id'),
                    'index' => $index,
                    'error_code' => $errorCode,
                ]);

                throw ValidationException::withMessages([
                    'secondary_images.' . $index => [$this->getUploadErrorMessage($errorCode)],
                ]);
            }
        }
    }

    private function getUploadErrorCode(string $field): ?int
    {
        if (!isset($_FILES[$field])) {
            return null;
        }

        $error = $_FILES[$field]['error'] ?? null;

        if (is_array($error)) {
            return null;
        }

        return is_numeric($error) ? (int) $error : null;
    }

    private function getUploadErrorCodes(string $field): array
    {
        if (!isset($_FILES[$field])) {
            return [];
        }

        $error = $_FILES[$field]['error'] ?? [];

        if (!is_array($error)) {
            return is_numeric($error) ? [(int) $error] : [];
        }

        return array_map(function ($value) {
            return is_numeric($value) ? (int) $value : UPLOAD_ERR_NO_FILE;
        }, $error);
    }

    private function getUploadErrorMessage(int $errorCode): string
    {
        switch ($errorCode) {
            case UPLOAD_ERR_INI_SIZE:
            case UPLOAD_ERR_FORM_SIZE:
                return __('The selected image exceeds the maximum allowed upload size.');
            case UPLOAD_ERR_PARTIAL:
                return __('The image upload was interrupted. Please try again.');
            case UPLOAD_ERR_NO_TMP_DIR:
                return __('The server is missing a temporary folder for image uploads.');
            case UPLOAD_ERR_CANT_WRITE:
                return __('The server could not write the uploaded image to disk.');
            case UPLOAD_ERR_EXTENSION:
                return __('A server extension stopped the image upload.');
            default:
                return __('The selected image could not be uploaded.');
        }
    }

    private function getProductTypeLabel(int $variable): string
    {
        switch ($variable) {
            case Product::TYPE_VARIABLE:
                return 'Variable';
            case Product::TYPE_BULK:
                return 'Bulk';
            default:
                return 'Simple';
        }
    }

    private function ensureProductImageDiskPath(?string $preferredPath = null): string
    {
        $paths = array_filter([
            $preferredPath,
            public_path('img/products'),
        ]);

        foreach ($paths as $path) {
            try {
                if (!File::exists($path)) {
                    File::makeDirectory($path, 0755, true);
                }

                if (is_dir($path) && is_writable($path)) {
                    return $path;
                }
            } catch (\Throwable $exception) {
                Log::warning('Unable to prepare product image directory', [
                    'path' => $path,
                    'error' => $exception->getMessage(),
                ]);
            }
        }

        throw new \RuntimeException('Unable to prepare writable image directory.');
    }

    private function getProductImagePublicPath(): string
    {
        $path = env('ECOMMERCE_IMAGE_PUBLIC_PATH', 'img/products');

        return rtrim($path, '/\\') . '/';
    }

    private function buildProductImageUrl(Request $request, string $fileName): string
    {
        $publicPathTrim = trim($this->getProductImagePublicPath(), '/');
        $requestBase = rtrim($request->getSchemeAndHttpHost() . $request->getBasePath(), '/');
        $envBase = config('app.asset_url') ?: config('app.url') ?: env('APP_URL') ?: env('ASSET_URL');
        $baseUrlTrim = rtrim(($envBase ?: $requestBase), '/');

        $endsWithPublic = $publicPathTrim !== '' && substr($baseUrlTrim, -strlen($publicPathTrim)) === $publicPathTrim;

        if ($endsWithPublic) {
            return $baseUrlTrim . '/' . ltrim($fileName, '/');
        }

        return $baseUrlTrim . '/' . $publicPathTrim . '/' . ltrim($fileName, '/');
    }

    private function extractImageFileName(?string $value): string
    {
        $value = (string) $value;
        if ($value === '') {
            return '';
        }

        $pathPart = parse_url($value, PHP_URL_PATH);
        return basename($pathPart ?: $value);
    }

    private function handleImageUpload(Request $request, Product $product, $isUpdate = false)
    {
        $diskPath = $this->ensureProductImageDiskPath($this->getProductImageDiskPath());
        $publicPath = $this->getProductImagePublicPath();
        Log::info('handleImageUpload start for product: ' . $product->id . ' hasFile(image): ' . ($request->hasFile('image') ? 'yes' : 'no') . ' secondary_count: ' . (count($request->file('secondary_images') ?? [])));

        // Main image
        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $file_name = SetNameImage::set($file->getClientOriginalName(), $file->getClientOriginalExtension());
            $imageUrl = $this->buildProductImageUrl($request, $file_name);
            try {
                $file->move($diskPath, $file_name);
                Log::info('Main image moved: ' . $file_name);
            } catch (\Exception $e) {
                Log::error('Error moving main image: ' . $e->getMessage());
            }

            // Try to resize if helper exists
            if (function_exists('ResizeImage') || class_exists('ResizeImage')) {
                try {
                    ResizeImage::dimenssion($file_name, $file->getClientOriginalExtension(), $publicPath, $this->width_file, $this->height_file);
                } catch (\Exception $e) {
                    Log::error('Resize error: ' . $e->getMessage());
                }
            }

            if ($isUpdate) {
                $productImage = $product->images()->where('main', '1')->first();
                if ($productImage) {
                    // delete old file
                    $old = $this->extractImageFileName($productImage->file);
                    $oldPath = $diskPath . DIRECTORY_SEPARATOR . $old;
                    if ($old && File::exists($oldPath)) {
                        try { File::delete($oldPath); } catch (\Exception $e) { Log::error('Error deleting old main image: '.$e->getMessage()); }
                        Log::info('Deleted old main image: ' . $old);
                    }
                    $productImage->update(['file' => $imageUrl]);
                } else {
                    ProductImage::create(['product_id' => $product->id, 'file' => $imageUrl, 'main' => '1']);
                }
            } else {
                ProductImage::create(['product_id' => $product->id, 'file' => $imageUrl, 'main' => '1']);
            }
        }

        // Secondary images (multiple)
        if ($request->hasFile('secondary_images')) {
            foreach ($request->file('secondary_images') as $file) {
                try {
                    $imageName = SetNameImage::set($file->getClientOriginalName(), $file->getClientOriginalExtension());
                    $imageUrl = $this->buildProductImageUrl($request, $imageName);
                    try {
                        $file->move($diskPath, $imageName);
                        Log::info('Secondary image moved: ' . $imageName);
                    } catch (\Exception $e) {
                        Log::error('Error moving secondary image: ' . $e->getMessage());
                    }

                    if (function_exists('ResizeImage') || class_exists('ResizeImage')) {
                        try {
                            ResizeImage::dimenssion($imageName, $file->getClientOriginalExtension(), $publicPath, $this->width_file, $this->height_file);
                        } catch (\Exception $e) {
                            Log::error('Resize error secondary: ' . $e->getMessage());
                        }
                    }

                    $img = ProductImage::create(['product_id' => $product->id, 'file' => $imageUrl, 'main' => '0']);
                    Log::info('Created secondary image record id: ' . $img->id . ' file: ' . $imageName);
                } catch (\Exception $e) {
                    Log::error('Error creating secondary image: ' . $e->getMessage());
                }
            }
        }

        // Delete images by id (also delete physical files)
        if ($request->has('delete_images')) {
            $ids = $request->delete_images;
            if (is_array($ids) && count($ids) > 0) {
                $items = ProductImage::whereIn('id', $ids)->get();
                foreach ($items as $item) {
                        $itemFileName = $this->extractImageFileName($item->file);
                        $itemPath = $diskPath . DIRECTORY_SEPARATOR . $itemFileName;
                        if ($itemFileName && File::exists($itemPath)) {
                            try { File::delete($itemPath); } catch (\Exception $e) { Log::error('Delete file error: '.$e->getMessage()); }
                        }
                }
                ProductImage::whereIn('id', $ids)->delete();
            }
        }
    }

    public function updateImage(Request $request)
    {
        $diskPath = $this->ensureProductImageDiskPath($this->getProductImageDiskPath());
        $publicPath = $this->getProductImagePublicPath();
        $hasMain = ProductImage::where('product_id', $request->product_id)->where('main', '1')->exists();
        $fileId = null;
        $file_name = null;
        $imageUrl = null;

        if (!$hasMain) {
            $file = $request->file('file');
            $file_name = SetNameImage::set($file->getClientOriginalName(), $file->getClientOriginalExtension());
            $imageUrl = $this->buildProductImageUrl($request, $file_name);

            $file->move($diskPath, $file_name);

            if (function_exists('ResizeImage') || class_exists('ResizeImage')) {
                try { ResizeImage::dimenssion($file_name, $file->getClientOriginalExtension(), $publicPath, $this->width_file, $this->height_file); } catch (\Exception $e) { Log::error($e->getMessage()); }
            }

            $detail = new ProductImage;
            $detail->file = $imageUrl;
            $detail->main = '1';
            $detail->product_id = $request->product_id;
            $detail->save();
            $fileId = $detail->id;
            return response()->json(['result' => true, 'id' => $fileId, 'file' => $file_name, 'url' => $imageUrl]);
        }

        if ($request->id == NULL || $request->id == 'null') {
            $item = ProductImage::where('product_id', $request->product_id)->where('main', '1')->first();
            $odlFile = $this->extractImageFileName($item->file);
            $file = $request->file('file');
            $file_name = SetNameImage::set($file->getClientOriginalName(), $file->getClientOriginalExtension());
            $imageUrl = $this->buildProductImageUrl($request, $file_name);

            $file->move($diskPath, $file_name);

            if (function_exists('ResizeImage') || class_exists('ResizeImage')) {
                try { ResizeImage::dimenssion($file_name, $file->getClientOriginalExtension(), $publicPath, $this->width_file, $this->height_file); } catch (\Exception $e) { Log::error($e->getMessage()); }
            }

            $oldPath = $diskPath . DIRECTORY_SEPARATOR . $odlFile;
            if ($odlFile && File::exists($oldPath)) {
                File::delete($oldPath);
            }

            $item->file = $imageUrl;
            $item->save();
            $fileId = $item->id;
        } else if ($request->id == 0) {
            $file = $request->file('file');
            $file_name = SetNameImage::set($file->getClientOriginalName(), $file->getClientOriginalExtension());
            $imageUrl = $this->buildProductImageUrl($request, $file_name);
            $file->move($diskPath, $file_name);

            if (function_exists('ResizeImage') || class_exists('ResizeImage')) {
                try { ResizeImage::dimenssion($file_name, $file->getClientOriginalExtension(), $publicPath, $this->width_file, $this->height_file); } catch (\Exception $e) { Log::error($e->getMessage()); }
            }

            $detail = new ProductImage;
            $detail->file = $imageUrl;
            $detail->product_id = $request->product_id;
            $detail->save();
            $fileId = $detail->id;
        } else {
            $item = ProductImage::find($request->id);
            $odlFile = $this->extractImageFileName($item->file);
            $file = $request->file('file');
            $file_name = SetNameImage::set($file->getClientOriginalName(), $file->getClientOriginalExtension());
            $imageUrl = $this->buildProductImageUrl($request, $file_name);
            $file->move($diskPath, $file_name);

            if (function_exists('ResizeImage') || class_exists('ResizeImage')) {
                try { ResizeImage::dimenssion($file_name, $file->getClientOriginalExtension(), $publicPath, $this->width_file, $this->height_file); } catch (\Exception $e) { Log::error($e->getMessage()); }
            }

            $oldPath = $diskPath . DIRECTORY_SEPARATOR . $odlFile;
            if ($odlFile && File::exists($oldPath)) {
                File::delete($oldPath);
            }
            $item->file = $imageUrl;
            $item->save();
            $fileId = $request->id;
        }

        return response()->json(['result' => true, 'id' => $fileId, 'file' => $file_name, 'url' => $imageUrl]);
    }
}
