<?php

namespace App\Http\Controllers;

use App\Models\Supplier;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests\SupplierRequest;
use App\Models\Pais;
use App\Models\Estado;
use App\Models\Municipality;

class SupplierController extends Controller
{
    public function index()
    {
        $suppliers = Supplier::where('status_prove', '1')->orderBy('id', 'desc')->get();

        return view('panel.suppliers.index', compact('suppliers'));
    }

    public function create()
    {
        $paises = Pais::orderBy('nombre','asc')->get();

        return view('panel.suppliers.create')->with([
            'paises' => $paises,
        ]);
    }

    public function store(SupplierRequest $request)
    {
        $data = $request->validated();

        Supplier::create($data);

        return redirect()->route('suppliers.index')->with('success', __('Supplier saved successfully.'));
    }

    public function edit($id)
    {
        $supplier = Supplier::find($id);

        $paises = Pais::orderBy('nombre','asc')->get();

        $estados = Estado::where('id', $supplier->estado_prove)->get();

        $municipios = Municipality::where('id', $supplier->muni_prove)->get();

        return view('panel.suppliers.edit')->with([
            'supplier' => $supplier,
            'paises' => $paises,
            'states' => $estados,
            'municipalities' => $municipios
        ]);
    }

    public function update(SupplierRequest $request, $id)
    {
        $supplier = Supplier::find($id);
        $supplier->fill($request->validated());
        $supplier->save();

        return redirect()->route('suppliers.index')->with('success', __('Supplier updated successfully.'));
    }

    public function destroy($id)
    {
        $item = Supplier::find($id);
        $item->status_prove = 2;
        $item->save();

        return redirect()->route('suppliers.index')->with('success', __('Supplier deleted successfully.'));
    }

    public function status($id)
    {
        $supplier = Supplier::find($id);
        $supplier->status_prove = $supplier->status_prove == '1' ? '2' : '1';
        $supplier->save();

        return redirect()->route('suppliers.index')->with('success', __('Supplier status updated.'));
    }

    /**
     * Devuelve los estados para un país (JSON)
     */
    public function getStates($countryId)
    {
        $states = Estado::where('pais_id', $countryId)->orderBy('nombre', 'asc')->get();

        return response()->json($states);
    }

    /**
     * Devuelve los municipios para un estado (JSON)
     */
    public function getMunicipalities($stateId)
    {
        $municipalities = Municipality::where('estado_id', $stateId)->orderBy('name', 'asc')->get();

        return response()->json($municipalities);
    }
}
