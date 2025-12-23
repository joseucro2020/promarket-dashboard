<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ExchangeRate;
use App\Http\Requests\ExchangeRateRequest;

class ExchangeRateController extends Controller
{
    public function index()
    {
        $rates = ExchangeRate::orderBy('created_at','desc')->get();
        return view('panel.exchange_rates.index', compact('rates'));
    }

    public function create()
    {
        return view('panel.exchange_rates.form');
    }

    public function store(ExchangeRateRequest $request)
    {
        ExchangeRate::create($request->validated());
        return redirect()->route('exchange-rates.index')->with('success', 'Tasa creada correctamente.');
    }

    public function edit($id)
    {
        $rate = ExchangeRate::findOrFail($id);
        return view('panel.exchange_rates.form', compact('rate'));
    }

    public function update(ExchangeRateRequest $request, $id)
    {
        $rate = ExchangeRate::findOrFail($id);
        $rate->update($request->validated());
        return redirect()->route('exchange-rates.index')->with('success', 'Tasa actualizada correctamente.');
    }

    public function destroy($id)
    {
        $rate = ExchangeRate::findOrFail($id);
        $rate->delete();
        return redirect()->route('exchange-rates.index')->with('success', 'Tasa eliminada.');
    }
}
