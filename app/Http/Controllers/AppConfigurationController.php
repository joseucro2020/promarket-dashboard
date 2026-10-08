<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AppConfiguration;

class AppConfigurationController extends Controller
{
    public function index()
    {
        // Load existing configurations from DB
        $configs = \App\Models\AppConfiguration::all()->pluck('value', 'key')->toArray();
        return view('app_configurations.index', compact('configs'));
    }

    public function store(Request $request)
    {
        $data = $request->except('_token');

        foreach ($data as $key => $value) {
            \App\Models\AppConfiguration::updateOrCreate(
                ['key' => $key],
                ['value' => $value]
            );
        }

        return back()->with('success', 'Configuración guardada exitosamente.');
    }

    public function apiConfig()
    {
        $configs = \App\Models\AppConfiguration::all()->pluck('value', 'key')->toArray();
        
        // Return JSON structure
        return response()->json([
            'status' => 'success',
            'data' => $configs
        ]);
    }
}
