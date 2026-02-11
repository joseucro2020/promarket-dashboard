<?php

namespace App\Http\Controllers;

use App\Models\Estado;
use App\Models\Municipality;
use App\Models\Pais;
use App\Models\Parish;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class StatesMunicipalitiesController extends Controller
{
    public function index()
    {
        $states = Estado::where('pais_id', Pais::VENEZUELA_ID)
            ->orderBy('nombre', 'asc')
            ->get();

        return view('panel.states-municipalities.index', [
            'states' => $states,
        ]);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $request->validate([
            'nombre' => ['required', 'string', 'max:255'],
        ]);

        $state = Estado::findOrFail($id);
        $state->nombre = $request->input('nombre');
        $state->save();

        return response()->json([
            'result' => true,
            'state' => $state,
        ]);
    }

    public function status(int $id): JsonResponse
    {
        $state = Estado::findOrFail($id);
        $state->status = (int) (!((int) $state->status));
        $state->save();

        return response()->json([
            'result' => true,
            'status' => (int) $state->status,
        ]);
    }

    public function show(int $estadoId)
    {
        $state = Estado::findOrFail($estadoId);

        $municipalities = Municipality::with('parishes')
            ->where('estado_id', $estadoId)
            ->orderBy('name', 'asc')
            ->get();

        return view('panel.states-municipalities.municipalities', [
            'state' => $state,
            'municipalities' => $municipalities,
        ]);
    }

    public function parishes(int $municipalityId)
    {
        $municipality = Municipality::findOrFail($municipalityId);

        $parishes = Parish::where('municipality_id', $municipalityId)
            ->orderBy('name', 'asc')
            ->get();

        return view('panel.states-municipalities.parishes', [
            'municipality' => $municipality,
            'parishes' => $parishes,
        ]);
    }

    public function storeParish(Request $request, int $municipalityId)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
        ]);

        $municipality = Municipality::findOrFail($municipalityId);

        Parish::create([
            'name' => $request->input('name'),
            'municipality_id' => $municipality->id,
        ]);

        return redirect()
            ->route('states-municipalities.municipalities.parishes', $municipality->id)
            ->with('success', __('Information saved successfully.'));
    }

    public function destroyParish(int $id)
    {
        $parish = Parish::findOrFail($id);
        $municipalityId = $parish->municipality_id;
        $parish->delete();

        return redirect()
            ->route('states-municipalities.municipalities.parishes', $municipalityId)
            ->with('success', __('Deleted successfully.'));
    }

    public function updateMunicipality(Request $request, int $id): JsonResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
        ]);

        $municipality = Municipality::findOrFail($id);
        $municipality->name = $request->input('name');
        $municipality->save();

        return response()->json([
            'result' => true,
            'municipality' => $municipality->loadCount('parishes'),
        ]);
    }

    public function municipalityStatus(int $id): JsonResponse
    {
        $municipality = Municipality::findOrFail($id);
        $municipality->status = (int) (!((int) $municipality->status));
        $municipality->save();

        return response()->json([
            'result' => true,
            'status' => (int) $municipality->status,
        ]);
    }
}
