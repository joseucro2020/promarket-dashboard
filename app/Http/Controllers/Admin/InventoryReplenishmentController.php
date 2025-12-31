<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Product;

class InventoryReplenishmentController extends Controller
{
    public function index()
    {
        return view('panel.inventory.index');
    }

    public function create()
    {
        $products = Product::whereIn('status', ['1','0'])->orderBy('name')->pluck('name','id');
        return view('panel.inventory.create', ['products' => $products]);
    }

    public function store(Request $request)
    {
        // Minimal store: validate and pretend to save. Real logic should update product amounts.
        $data = $request->validate([
            'product_id' => 'required|integer',
            'quantity' => 'required|numeric',
            'type' => 'required|string',
            'reason' => 'nullable|string'
        ]);

        // TODO: implement stock update logic using ProductAmount and transactions

        return redirect()->route('inventory.index')->with('success','Reposición registrada correctamente.');
    }
}
