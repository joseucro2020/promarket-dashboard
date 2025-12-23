<?php

namespace App\Http\Controllers;

use App\Http\Requests\CouponRequest;
use App\Models\Coupon;
use App\Models\User;

class CouponController extends Controller
{
    private function listSellers()
    {
        return User::select('id', 'name', 'identificacion', 'persona')->orderBy('name')->get();
    }

    public function index()
    {
        $coupons = Coupon::with('user')->orderBy('id', 'desc')->get();

        return view('panel.coupons.index', compact('coupons'));
    }

    public function create()
    {
        $sellers = $this->listSellers();

        return view('panel.coupons.form', compact('sellers'));
    }

    public function store(CouponRequest $request)
    {
        $coupon = Coupon::create($request->validated());
        $coupon->recurrent_purchase = $request->filled('recurrent_purchase') ? $request->recurrent_purchase : null;
        $coupon->status = Coupon::STATUS_INACTIVE;
        $coupon->save();

        return redirect()->route('coupons.index')->with('success', __('Coupon saved successfully.'));
    }

    public function edit(Coupon $coupon)
    {
        $sellers = $this->listSellers();

        return view('panel.coupons.form', compact('coupon', 'sellers'));
    }

    public function update(CouponRequest $request, Coupon $coupon)
    {
        $coupon->fill($request->validated());
        $coupon->recurrent_purchase = $request->filled('recurrent_purchase') ? $request->recurrent_purchase : null;
        $coupon->save();

        return redirect()->route('coupons.index')->with('success', __('Coupon updated successfully.'));
    }

    public function destroy(Coupon $coupon)
    {
        $coupon->delete();

        return redirect()->route('coupons.index')->with('success', __('Coupon deleted successfully.'));
    }

    public function status(Coupon $coupon)
    {
        $coupon->status = $coupon->status === Coupon::STATUS_INACTIVE ? Coupon::STATUS_ACTIVE : Coupon::STATUS_INACTIVE;
        $coupon->save();

        return redirect()->route('coupons.index')->with('success', __('Coupon status updated.'));
    }
}
