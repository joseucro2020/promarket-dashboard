<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\RedirectResponse;
use App\Http\Controllers\Controller;

/**
 * Deprecated Admin controller.
 * The Suppliers controller now lives in App\Http\Controllers\SupplierController.
 * This stub redirects to the panel routes to avoid breaking existing links.
 */
class SupplierController extends Controller
{
    public function __call($method, $args)
    {
        // Redirect any call to the suppliers index route
        return redirect()->route('suppliers.index');
    }
}
