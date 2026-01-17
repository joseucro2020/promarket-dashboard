<?php

namespace App\Http\Controllers;

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
use Carbon\Carbon;
use File;

class ProductController extends Controller
{
    private $width_file = 550;
    private $height_file = 800;

    public function index(Request $request)
    {
        if ($request->ajax()) {
            $query = $this->buildIndexQuery($request);

            $totalRecords = Product::count();
            $filteredRecords = $query->count();

            // Order
            $this->applySorting($query, $request);

            // Pagination
            $start = $request->input('start', 0);
            $length = $request->input('length', 10);
            $products = $query->skip($start)->take($length)->get();

            $data = $this->transformProductsForDataTable($products);

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
        return view('panel.products.create', compact('categories', 'taxes'));
    }

    public function store(StoreProductRequest $request)
    {
        try {
            Log::info('Product store request data: ' . json_encode($request->all()));
            DB::transaction(function () use ($request) {
                $product = new Product;
                $this->fillProductData($product, $request);
                $product->status = '1';
                $product->save();

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
                    'price' => $request->price_1,
                    'cost' => $request->price_2,
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
        $product = Product::with(['images', 'colors.amounts'])->findOrFail($id);
        $categories = Category::all();
        $taxes = Taxe::where('status', Taxe::STATUS_ACTIVE)->get();
        return view('panel.products.edit', compact('product', 'categories', 'taxes'));
    }

    public function update(UpdateProductRequest $request, $id)
    {
        try {
            Log::info('Product update request id: ' . $id . ' data: ' . json_encode($request->all()));
            DB::transaction(function () use ($request, $id) {
                $product = Product::findOrFail($id);
                Log::info('Product before update: ' . json_encode($product->toArray()));
                $this->fillProductData($product, $request);
                $product->save();
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

        $submittedIds = [];

        if ($request->has('presentations')) {
            foreach ($request->presentations as $data) {
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
                    $amount = ProductAmount::find($data['id']);
                    if ($amount) {
                        $amount->update($amountData);
                        $submittedIds[] = $amount->id;
                    }
                } else {
                    $amount = ProductAmount::create($amountData);
                    $submittedIds[] = $amount->id;
                }
            }
        }

        // Delete removed presentations (soft delete if model uses SoftDeletes)
        $color->amounts()->whereNotIn('id', $submittedIds)->delete();
    }

    public function destroy($id)
    {
        $product = Product::findOrFail($id);
        $product->status = '2'; // Soft delete status
        $product->save();
        return redirect()->route('products.index')->with('success', __('Product deleted successfully.'));
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

    // --- Private Helper Methods ---

    private function buildIndexQuery(Request $request)
    {
        $query = Product::select('products.*')
            ->with([
                'categories:id,name,name_english',
                'subcategories:id,name,name_english',
                'subsubcategories:id,name,name_english',
                'images',
                'taxe',
                'colors' => function ($colors) {
                    $colors->select('id', 'name', 'name_english', 'product_id')
                        ->where('status', '1')
                        ->with([
                            'amounts' => function ($q) {
                                $q->select('id as amount_id', 'amount', 'min', 'max', 'cost', 'umbral', 'price', 'unit', 'presentation', 'product_color_id', 'category_size_id', 'sku', 'utilidad')
                                    ->with([
                                        'category_size' => function ($c) {
                                            $c->select('id', 'category_id', 'size_id')
                                                ->with(['size:id,name']);
                                        }
                                    ]);
                            }
                        ]);
                }
            ]);

        // Apply Filters
        if ($request->filled('company')) {
            $query->where('products.company_id', $request->company);
        }
        
        if ($request->filled('status')) {
            $query->where('products.status', $request->status);
        } else {
            $query->whereIn('products.status', ['0', '1']);
        }

        if ($request->filled('category')) {
            $query->where('products.category_id', $request->category);
        }
        if ($request->filled('subcategory')) {
            $query->where('products.subcategory_id', $request->subcategory);
        }
        if ($request->filled('subsubcategory')) {
            $query->where('products.subsubcategory_id', $request->subsubcategory);
        }
        if ($request->filled('typeProduct')) {
            $query->where('products.variable', $request->typeProduct);
        }

        // Search
        if ($request->filled('search.value')) {
            $searchValue = $request->input('search.value');
            $query->where(function($q) use ($searchValue) {
                $q->where('products.name', 'like', "%{$searchValue}%")
                  ->orWhere('products.name_english', 'like', "%{$searchValue}%")
                  ->orWhere('products.id', 'like', "%{$searchValue}%");
            });
        }

        // Inventory Filter
        if ($request->filled('inventory')) {
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

        return $query;
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
            }
        } else {
            $query->orderBy('products.id', 'desc');
        }
    }

    private function transformProductsForDataTable($products)
    {
        $data = [];
        foreach ($products as $product) {
            // Calculate profit/percentage
            $profit = $product->price_1 - $product->price_2;
            $percentage = $product->price_2 > 0 ? ($profit / $product->price_2) * 100 : 0;

            // Calculate Stock
            $stock = 0;
            if ($product->colors && $product->colors->count() > 0) {
                foreach ($product->colors as $color) {
                    foreach ($color->amounts as $amount) {
                        $stock += $amount->amount;
                    }
                }
            }

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
            
            $actionsHtml = '
                <div class="d-flex align-items-center col-actions">
                    <a class="mr-1" href="'.$editUrl.'" data-toggle="tooltip" data-placement="top" title="'.__('Edit').'">
                        <i data-feather="edit-2"></i>
                    </a>
                    <form action="'.$deleteUrl.'" method="POST" onsubmit="return confirm(\''.$confirm.'\');" style="display:inline;">
                        '.$csrf.'
                        '.$method.'
                        <button type="submit" class="btn btn-icon btn-flat-danger" data-toggle="tooltip" data-placement="top" title="'.__('Delete').'">
                            <i data-feather="trash"></i>
                        </button>
                    </form>
                </div>';

            $data[] = [
                'actions' => $actionsHtml,
                'id' => $product->id,
                'image' => $imageHtml,
                'category' => optional($product->categories)->name ?? '-',
                'name' => $product->name,
                'stock' => $stock,
                'threshold' => $product->threshold ?? '-',
                'tax' => optional($product->taxe)->name ?? '-',
                'cost' => '$' . number_format($product->price_2, 2),
                'price' => '$' . number_format($product->price_1, 2),
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
        $categories = Category::select('id', 'name', 'name_english')
            ->where('status', '1')
            ->orderBy('name', 'DESC')
            ->get();

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
            'categories' => $categories,
            'collections' => $collections,
            'designs' => $designs,
            'taxes' => $taxes,
            'tags' => $tags,
            'supplier' => $supplier,
        ]);
    }

    private function fillProductData(Product $product, Request $request)
    {
        $product->name = $request->name;
        $product->name_english = $request->name_english;
        $product->description = $request->description;
        $product->description_english = $request->description_english;
        $product->slug = Str::slug($request->name);
        $product->price_1 = $request->price_1;
        $product->price_2 = $request->price_2;
        $product->category_id = $request->category_id;
        $product->subcategory_id = $request->subcategory_id;
        $product->subsubcategory_id = $request->subsubcategory_id;
        $product->taxe_id = $request->taxe_id;
        $product->retail = $request->retail;
        $product->wholesale = $request->wholesale;
        $product->variable = $request->variable ?? 0;
        // Map form fields to DB columns
        if ($request->has('min_stock_deactivate')) {
            $product->minexi = $request->input('min_stock_deactivate');
        }
        if ($request->has('max_stock_activate')) {
            $product->maxexi = $request->input('max_stock_activate');
        }
    }

    private function handleImageUpload(Request $request, Product $product, $isUpdate = false)
    {
        $url = 'img/products/';
        Log::info('handleImageUpload start for product: ' . $product->id . ' hasFile(image): ' . ($request->hasFile('image') ? 'yes' : 'no') . ' secondary_count: ' . (count($request->file('secondary_images') ?? [])));

        // Main image
        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $file_name = time() . '_main.' . $file->getClientOriginalExtension();
            try {
                $file->move(public_path($url), $file_name);
                Log::info('Main image moved: ' . $file_name);
            } catch (\Exception $e) {
                Log::error('Error moving main image: ' . $e->getMessage());
            }

            // Try to resize if helper exists
            if (function_exists('ResizeImage') || class_exists('ResizeImage')) {
                try {
                    ResizeImage::dimenssion($file_name, $file->getClientOriginalExtension(), $url, $this->width_file, $this->height_file);
                } catch (\Exception $e) {
                    Log::error('Resize error: ' . $e->getMessage());
                }
            }

            if ($isUpdate) {
                $productImage = $product->images()->where('main', '1')->first();
                if ($productImage) {
                    // delete old file
                    $old = $productImage->file;
                    if ($old && File::exists(public_path($url . $old))) {
                        try { File::delete(public_path($url . $old)); } catch (\Exception $e) { Log::error('Error deleting old main image: '.$e->getMessage()); }
                        Log::info('Deleted old main image: ' . $old);
                    }
                    $productImage->update(['file' => $file_name]);
                } else {
                    ProductImage::create(['product_id' => $product->id, 'file' => $file_name, 'main' => '1']);
                }
            } else {
                ProductImage::create(['product_id' => $product->id, 'file' => $file_name, 'main' => '1']);
            }
        }

        // Secondary images (multiple)
        if ($request->hasFile('secondary_images')) {
            foreach ($request->file('secondary_images') as $file) {
                try {
                    $imageName = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
                    try {
                        $file->move(public_path($url), $imageName);
                        Log::info('Secondary image moved: ' . $imageName);
                    } catch (\Exception $e) {
                        Log::error('Error moving secondary image: ' . $e->getMessage());
                    }

                    if (function_exists('ResizeImage') || class_exists('ResizeImage')) {
                        try {
                            ResizeImage::dimenssion($imageName, $file->getClientOriginalExtension(), $url, $this->width_file, $this->height_file);
                        } catch (\Exception $e) {
                            Log::error('Resize error secondary: ' . $e->getMessage());
                        }
                    }

                    $img = ProductImage::create(['product_id' => $product->id, 'file' => $imageName, 'main' => '0']);
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
                    if ($item->file && File::exists(public_path($url . $item->file))) {
                        try { File::delete(public_path($url . $item->file)); } catch (\Exception $e) { Log::error('Delete file error: '.$e->getMessage()); }
                    }
                }
                ProductImage::whereIn('id', $ids)->delete();
            }
        }
    }

    public function updateImage(Request $request)
    {
        $url = "img/products/";
        $hasMain = ProductImage::where('product_id', $request->product_id)->where('main', '1')->exists();
        $fileId = null;
        $file_name = null;

        if (!$hasMain) {
            $file = $request->file('file');
            $file_name = function_exists('SetNameImage') || class_exists('SetNameImage')
                ? SetNameImage::set($file->getClientOriginalName(), $file->getClientOriginalExtension())
                : time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();

            $file->move(public_path($url), $file_name);

            if (function_exists('ResizeImage') || class_exists('ResizeImage')) {
                try { ResizeImage::dimenssion($file_name, $file->getClientOriginalExtension(), $url, $this->width_file, $this->height_file); } catch (\Exception $e) { Log::error($e->getMessage()); }
            }

            $detail = new ProductImage;
            $detail->file = $file_name;
            $detail->main = '1';
            $detail->product_id = $request->product_id;
            $detail->save();
            $fileId = $detail->id;
            return response()->json(['result' => true, 'id' => $fileId, 'file' => $file_name]);
        }

        if ($request->id == NULL || $request->id == 'null') {
            $item = ProductImage::where('product_id', $request->product_id)->where('main', '1')->first();
            $odlFile = $item->file;
            $file = $request->file('file');
            $file_name = function_exists('SetNameImage') || class_exists('SetNameImage')
                ? SetNameImage::set($file->getClientOriginalName(), $file->getClientOriginalExtension())
                : time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();

            $file->move(public_path($url), $file_name);

            if (function_exists('ResizeImage') || class_exists('ResizeImage')) {
                try { ResizeImage::dimenssion($file_name, $file->getClientOriginalExtension(), $url, $this->width_file, $this->height_file); } catch (\Exception $e) { Log::error($e->getMessage()); }
            }

            if ($odlFile && File::exists(public_path($url . $odlFile))) {
                File::delete(public_path($url . $odlFile));
            }

            $item->file = $file_name;
            $item->save();
            $fileId = $item->id;
        } else if ($request->id == 0) {
            $file = $request->file('file');
            $file_name = function_exists('SetNameImage') || class_exists('SetNameImage')
                ? SetNameImage::set($file->getClientOriginalName(), $file->getClientOriginalExtension())
                : time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $file->move(public_path($url), $file_name);

            if (function_exists('ResizeImage') || class_exists('ResizeImage')) {
                try { ResizeImage::dimenssion($file_name, $file->getClientOriginalExtension(), $url, $this->width_file, $this->height_file); } catch (\Exception $e) { Log::error($e->getMessage()); }
            }

            $detail = new ProductImage;
            $detail->file = $file_name;
            $detail->product_id = $request->product_id;
            $detail->save();
            $fileId = $detail->id;
        } else {
            $item = ProductImage::find($request->id);
            $odlFile = $item->file;
            $file = $request->file('file');
            $file_name = function_exists('SetNameImage') || class_exists('SetNameImage')
                ? SetNameImage::set($file->getClientOriginalName(), $file->getClientOriginalExtension())
                : time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $file->move(public_path($url), $file_name);

            if (function_exists('ResizeImage') || class_exists('ResizeImage')) {
                try { ResizeImage::dimenssion($file_name, $file->getClientOriginalExtension(), $url, $this->width_file, $this->height_file); } catch (\Exception $e) { Log::error($e->getMessage()); }
            }

            if ($odlFile && File::exists(public_path($url . $odlFile))) {
                File::delete(public_path($url . $odlFile));
            }
            $item->file = $file_name;
            $item->save();
            $fileId = $request->id;
        }

        return response()->json(['result' => true, 'id' => $fileId, 'file' => $file_name]);
    }
}
