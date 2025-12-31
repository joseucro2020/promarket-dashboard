<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\ProductAmount;
use App\Models\Product;

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
}
