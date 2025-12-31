<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\ShippingFee;
use App\Models\Setting;

class ShippingFeeController extends Controller
{
    public function index()
    {
        $shippingFees = ShippingFee::orderBy('type', 'desc')->get();
        $setting = Setting::getMinimunPurchase();
        $minimumPurchase = $setting && isset($setting->value) ? $setting->value : 0;

        return view('panel.shipping-fees.index')->with([
            'shippingFees' => $shippingFees,
            'minimumPurchase' => $minimumPurchase
        ]);
    }

    public function updateMinimum(Request $request)
    {
        $value = $request->input('minimum_purchase', 0);

        $setting = Setting::where('name', Setting::MINIMUN_PURCHASE)->first();
        if (! $setting) {
            $setting = new Setting();
            $setting->name = Setting::MINIMUN_PURCHASE;
        }
        $setting->value = $value;
        $setting->save();

        return redirect()->back()->with('success', __('Minimum purchase updated'));
    }

    public function update(Request $request, $id)
    {
        $shippingFee = ShippingFee::find($id);
        if (! $shippingFee) {
            return response()->json(['result' => false, 'message' => 'Not found'], 404);
        }

        $shippingFee->amount = $request->input('amount');
        $shippingFee->save();

        return response()->json(['result' => true, 'message' => 'Tasa de envio actualizada', 'shippingFee' => $shippingFee]);
    }

    public function getAll()
    {
        return ShippingFee::orderBy('type', 'desc')->get();
    }
}
