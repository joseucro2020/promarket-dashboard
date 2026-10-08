<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AppConfiguration;
use Illuminate\Support\Facades\Storage;

class AppConfigurationController extends Controller
{
    public function index()
    {
        // Load existing configurations from DB
        $configs = \App\Models\AppConfiguration::all()->pluck('value', 'key')->toArray();
        $categories = \App\Models\SpecialCategory::where('status', '1')->orderBy('name')->get(['id', 'name']);
        
        return view('app_configurations.index', compact('configs', 'categories'));
    }

    public function store(Request $request)
    {
        $data = $request->except('_token');

        foreach ($data as $key => $value) {
            // Si el valor viene como un string JSON (ej. home_layout_json), lo convertimos a array
            // para que el cast 'array' del modelo no lo encripte doblemente en la BD.
            if (is_string($value) && (str_starts_with(trim($value), '{') || str_starts_with(trim($value), '['))) {
                $decoded = json_decode($value, true);
                if (json_last_error() === JSON_ERROR_NONE) {
                    $value = $decoded;
                }
            }

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

    public function uploadImage(Request $request)
    {
        $request->validate([
            'image' => 'required|image|max:5120'
        ]);

        if ($request->hasFile('image')) {
            $file = $request->file('image');
            
            // Usar la misma lógica exacta de CategoryController para el nombre
            $filename = time() . '_' . \Illuminate\Support\Str::random(8) . '.' . $file->getClientOriginalExtension();
            
            $targetDir = public_path('img/categories');
            
            $file->move($targetDir, $filename);
            
            // Devolver solo la ruta relativa
            $url = 'img/categories/' . $filename;
            
            // URL absoluta
            $absoluteUrl = url('/' . $url);
            
            return response()->json(['success' => true, 'url' => $absoluteUrl]);
        }

        return response()->json(['success' => false], 400);
    }
}
