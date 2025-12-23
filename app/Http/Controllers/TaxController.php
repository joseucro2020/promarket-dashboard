<?php

namespace App\Http\Controllers;

use App\Models\Taxe;
use Illuminate\Http\Request;

class TaxController extends Controller
{
    private function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:255',
            'percentage' => 'required|numeric|min:0',
        ];
    }

    public function index()
    {
        $taxes = Taxe::orderBy('created_at', 'desc')->get();

        return view('panel.taxes.index', compact('taxes'));
    }

    public function create()
    {
        return view('panel.taxes.form');
    }

    public function store(Request $request)
    {
        $data = $request->validate($this->rules());

        Taxe::create(array_merge($data, ['status' => Taxe::STATUS_ACTIVE]));

        return redirect()->route('taxes.index')->with('success', __('Tax saved successfully.'));
    }

    public function edit($id)
    {
        $taxe = Taxe::findOrFail($id);

        return view('panel.taxes.form', compact('taxe'));
    }

    public function update(Request $request, $id)
    {
        $taxe = Taxe::findOrFail($id);
        $data = $request->validate($this->rules());

        $taxe->update($data);

        return redirect()->route('taxes.index')->with('success', __('Tax updated successfully.'));
    }

    public function destroy($id)
    {
        $taxe = Taxe::findOrFail($id);
        $taxe->delete();

        return redirect()->route('taxes.index')->with('success', __('Tax deleted successfully.'));
    }

    public function status($id)
    {
        $taxe = Taxe::findOrFail($id);
        $taxe->status = $taxe->status === Taxe::STATUS_ACTIVE ? Taxe::STATUS_INACTIVE : Taxe::STATUS_ACTIVE;
        $taxe->save();

        return redirect()->route('taxes.index')->with('success', __('Tax status updated.'));
    }
}
