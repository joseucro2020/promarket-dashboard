<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\ProductAmount;
use App\Models\Product;
use App\Models\ProductColor;
use Illuminate\Support\Str;

class WebServicesController extends Controller
{
    public function index()
    {
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

        return view('panel.kromi-market.index')->with([
            'categories' => $categories
        ]);
    }

    public function import(Request $request)
    {
        // legacy Excel import placeholder - keep interface compatible
        return response()->json(['result' => false, 'message' => 'Import via Excel not implemented']);
    }

    public function kromimarket()
    {
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

        return view('panel.kromi-market.kromimarket')->with([
            'categories' => $categories
        ]);
    }

    public function import_csv(Request $request)
    {
        $file = $request->file('file');
        if (! $file) {
            return response()->json(['error' => 'No file uploaded'], 400);
        }

        $fileContents = file($file->getPathname());

        $products = [];
        foreach ($fileContents as $line) {
            $data = str_getcsv($line);

            $product = array(
                'sku' => $data[0] ?? null,
                'name' => isset($data[1]) ? mb_convert_encoding($data[1], 'UTF-8', 'UTF-8') : "",
                'father' => isset($data[2]) ? mb_convert_encoding($data[2], 'UTF-8', 'UTF-8') : "",
                'son' => isset($data[3]) ? mb_convert_encoding($data[3], 'UTF-8', 'UTF-8') : "",
                'grandson' => isset($data[4]) ? mb_convert_encoding($data[4], 'UTF-8', 'UTF-8') : "",
                'price' => isset($data[5]) ? $data[5] : 0,
                'amount' => isset($data[8]) ? $data[8] : 0,
            );
            array_push($products, $product);
        }

        return response()->json(['products' => $products]);
    }

    /**
     * Devuelve un listado simple de productos para la tabla Kromi (JSON).
     * Campos devueltos: sku, name, father, son, grandson, price, amount
     */
    public function products(Request $request)
    {
        $perPage = intval($request->get('perPage', 500));

        $query = Product::select('products.*')
            ->with([
                'categories' => function ($category) {
                    $category->select('id', 'name', 'name_english');
                },
                'subcategories' => function ($subcategory) {
                    $subcategory->select('id', 'name', 'name_english');
                },
                'subsubcategories' => function ($subsubcategories) {
                    $subsubcategories->select('id', 'name', 'name_english');
                },
                'colors' => function ($colors) {
                    $colors->select('id', 'name', 'name_english', 'product_id')
                        ->where('status', '1')
                        ->with(['amounts' => function ($q) {
                            $q->select('id', 'amount', 'min', 'max', 'cost', 'umbral', 'price', 'unit', 'presentation', 'product_color_id', 'category_size_id', 'sku', 'utilidad');
                        }]);
                }
            ])
            ->whereIn('products.status', ['1', '0', '2'])
            ->when(!is_null($request->company), function ($q) use ($request) {
                $q->where('products.company_id', $request->company);
            })
            ->when(!is_null($request->status), function ($q) use ($request) {
                $q->where('products.status', $request->status);
            })
            ->when(!is_null($request->category), function ($q) use ($request) {
                $q->where('products.category_id', $request->category);
            })
            ->when(!is_null($request->subcategory), function ($q) use ($request) {
                $q->where('products.subcategory_id', $request->subcategory);
            })
            ->when(!is_null($request->subsubcategory), function ($q) use ($request) {
                $q->where('products.subsubcategory_id', $request->subsubcategory);
            })
            ->when(!is_null($request->typeProduct), function ($q) use ($request) {
                $q->where('products.variable', $request->typeProduct);
            })
            ->when(isset($request->search), function ($q) use ($request) {
                $q->where(function ($qq) use ($request) {
                    $qq->where('products.name', 'like', '%' . $request->search . '%')
                        ->orWhere('products.name_english', 'like', '%' . $request->search . '%');
                });
            })
            ->when(!is_null($request->inventory), function ($q) use ($request) {
                $inventory = intval($request->inventory);
                $q->whereHas('colors.amounts', function ($qa) use ($inventory) {
                    if ($inventory == 2) { // low stock
                        $qa->where('product_amount.amount', '>', 0)
                           ->whereColumn('product_amount.amount', '<=', 'product_amount.umbral')
                           ->whereNull('product_amount.deleted_at');
                    } else if ($inventory == 1) { // in stock
                        $qa->where('product_amount.amount', '>', 0)
                           ->whereColumn('product_amount.amount', '>', 'product_amount.umbral')
                           ->whereNull('product_amount.deleted_at');
                    } else { // out of stock
                        $qa->where('product_amount.amount', 0)
                           ->whereNull('product_amount.deleted_at');
                    }
                });
            })
            ->orderBy('products.id', 'DESC');

        $data = $query->paginate($perPage);

        $products = collect($data->items())->map(function ($p) {
            // Prefer amounts from loaded relations if available
            $sku = null; $price = null; $amount = 0;
            if (!empty($p->colors)) {
                foreach ($p->colors as $color) {
                    if (!empty($color->amounts)) {
                        $a = $color->amounts[0];
                        if ($a) {
                            $sku = $a->sku ?? $sku;
                            $price = $a->price ?? $price;
                            $amount = $a->amount ?? $amount;
                            break;
                        }
                    }
                }
            }

            // Fallback to ProductAmount query if nothing loaded
            if (!$sku) {
                $pa = ProductAmount::whereHas('product_color', function ($q) use ($p) {
                    $q->where('product_id', $p->id);
                })->orderBy('id')->first();
                if ($pa) {
                    $sku = $pa->sku ?? $sku;
                    $price = $pa->price ?? $price;
                    $amount = $pa->amount ?? $amount;
                }
            }

            return [
                'id' => $p->id,
                'sku' => $sku ?? ($p->slug ?? $p->id),
                'name' => $p->name,
                'father_id' => $p->category_id ?? null,
                'father' => optional($p->categories)->name ?? '',
                'son_id' => $p->subcategory_id ?? null,
                'son' => optional($p->subcategories)->name ?? '',
                'grandson_id' => $p->subsubcategory_id ?? null,
                'grandson' => optional($p->subsubcategories)->name ?? '',
                'price' => $price ?? ($p->price_1 ?? 0),
                'amount' => $amount ?? 0,
            ];
        })->toArray();

        return response()->json(['products' => $products, 'total' => $data->total()]);
    }

    /**
     * Registrar los items seleccionados desde la importación CSV/Kromi.
     * Espera `checkActive` como JSON (array) o array en el request con objetos: sku, price, amount, name
     */
    public function registerKromi(Request $request)
    {
        $raw = $request->input('checkActive');
        $items = [];
        if (is_string($raw)) {
            $items = json_decode($raw, true) ?: [];
        } elseif (is_array($raw)) {
            $items = $raw;
        }

        if (!is_array($items) || count($items) === 0) {
            return redirect()->back()->with('error', __('No items selected to register'));
        }

        $categoryId = $request->input('promarket-categories') ?: null;
        $subcategoryId = $request->input('promarket-subcategories') ?: null;
        $subsubcategoryId = $request->input('promarket-sub-subcategories') ?: null;
        $companyId = $request->input('company') ?: 1;

        foreach ($items as $product) {
            $sku = $product['sku'] ?? null;
            if (! $sku) continue;

            $priceRaw = $product['price'] ?? $product['cost'] ?? 0;
            // normalize price to numeric
            $price = 0;
            if (is_string($priceRaw)) {
                $price = floatval(str_replace([',', '$', ' '], ['.', '', ''], $priceRaw));
            } else {
                $price = floatval($priceRaw);
            }
            $amount = isset($product['amount']) ? intval($product['amount']) : 0;
            $name = $product['name'] ?? $sku;

            $productAmount = ProductAmount::with(['product_color', 'product'])->where('sku', $sku)->get();
            if (count($productAmount) > 0) {
                $pa = $productAmount[0];
                $pa->amount = $amount;
                $pa->cost = number_format($price, 2, '.', ',');

                $utilidadPct = floatval($pa->utilidad ?? 0);
                $utilidad = $price * ($utilidadPct / 100);
                $finalPrice = $price + $utilidad;

                $pa->price = number_format($finalPrice, 2, '.', ',');
                $pa->save();

                if ($pa->product) {
                    $Product = Product::find($pa->product->id);
                    if ($Product) {
                        $Product->price_1 = number_format($finalPrice, 2, '.', ',');
                        $Product->price_2 = number_format($finalPrice, 2, '.', ',');
                        $Product->company_id = $companyId;
                        $Product->save();
                    }
                }
            } else {
                // crear nuevo producto y sus relaciones mínimas
                $productbio = new Product;
                $productbio->name = $name;
                $productbio->name_english = $name;
                $productbio->description = $name;
                $productbio->description_english = $name;
                $productbio->coin = 2;
                $productbio->price_1 = number_format($price, 2, '.', ',');
                $productbio->price_2 = number_format($price, 2, '.', ',');
                $productbio->status = 1;
                $productbio->company_id = $companyId;
                $productbio->slug = Str::slug(substr($name, 0, 64));
                if ($categoryId) $productbio->category_id = $categoryId;
                if ($subcategoryId) $productbio->subcategory_id = $subcategoryId;
                if ($subsubcategoryId) $productbio->subsubcategory_id = $subsubcategoryId;
                $productbio->collection_id = 1;
                $productbio->minexi = 5;
                $productbio->maxexi = 10;
                $productbio->autom = 0;
                $productbio->save();

                // crear color por defecto
                $color = new ProductColor;
                $color->name = 'por defecto';
                $color->name_english = 'default';
                $color->product_id = $productbio->id;
                $color->save();

                $size = new ProductAmount;
                $size->amount = $amount;
                $size->product_color_id = $color->id;
                $size->category_size_id = 1;
                $size->min = 1;
                $size->max = 6;
                $size->cost = $price;
                $size->umbral = 1;
                $size->price = $price;
                $size->sku = $sku;
                $size->utilidad = 1;
                $size->save();
            }
        }

        return redirect()->back()->with('success', __('Items registrados correctamente'));
    }
}
