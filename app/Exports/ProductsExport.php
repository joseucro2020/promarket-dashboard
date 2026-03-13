<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;

class ProductsExport implements FromView
{
    private $products;
    private $today;

    public function __construct($products, string $today)
    {
        $this->products = $products;
        $this->today = $today;
    }

    public function view(): View
    {
        return view('panel.products.export_excel', [
            'products' => $this->products,
            'today' => $this->today,
        ]);
    }
}
