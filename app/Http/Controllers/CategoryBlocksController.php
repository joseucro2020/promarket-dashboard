<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Category;
use App\Models\Product;

class CategoryBlocksController extends Controller
{
    public function index()
    {
        $categories = Category::select('id', 'name', 'name_english')
            ->where('status', '1')
            ->with([
                'subcategories' => function ($sql) {
                    $sql->select('subcategories.id', 'subcategories.name', 'subcategories.name_english', 'subcategories.category_id')
                        ->with([
                            'sub_subcategories' => function ($sql) {
                                $sql->select('id', 'name', 'name_english', 'subcategory_id')
                                    ->where('status', '1');
                            }
                        ])
                        ->where('subcategories.status', '1');
                },
            ])
            ->get();

        return view('panel.categoryblocks.index', [
            'categories' => $categories,
        ]);
    }

    public function search(Request $request)
    {
        $products = Product::select('id', 'name')
            ->when(!is_null($request->category), function ($query) use ($request) {
                $query->where('category_id', $request->category);
            })
            ->when(!is_null($request->subcategory), function ($query) use ($request) {
                $query->where('subcategory_id', $request->subcategory);
            })
            ->when(!is_null($request->subsubcategory), function ($query) use ($request) {
                $query->where('subsubcategory_id', $request->subsubcategory);
            })
            ->whereIn('products.status', ['0', '1'])
            ->orderBy('id', 'desc')
            ->get();

        return response()->json($products);
    }

    public function update(Request $request)
    {
        $data = [
            'category_id' => $request->ccategory,
        ];

        if (!empty($request->csubcategory)) {
            $data['subcategory_id'] = $request->csubcategory;
        }

        if (!empty($request->csubsubcategory)) {
            $data['subsubcategory_id'] = $request->csubsubcategory;
        }

        Product::when(!is_null($request->category), function ($query) use ($request) {
            $query->where('category_id', $request->category);
        })
            ->when(!is_null($request->subcategory), function ($query) use ($request) {
                $query->where('subcategory_id', $request->subcategory);
            })
            ->when(!is_null($request->subsubcategory), function ($query) use ($request) {
                $query->where('subsubcategory_id', $request->subsubcategory);
            })
            ->whereIn('status', ['0', '1'])
            ->update($data);

        return response()->json(['ok' => true]);
    }
}
