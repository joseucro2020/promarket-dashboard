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
            'image' => 'required|file|mimes:jpeg,png,jpg,gif,webp,mp4,webm|max:30720'
        ]);

        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $ext = strtolower($file->getClientOriginalExtension());
            $filename = 'banner_' . time() . '_' . \Illuminate\Support\Str::random(8) . '.' . $ext;
            
            // 1. Determinar el path de disco (donde se guarda físicamente)
            $preferredPath = env('ECOMMERCE_IMAGE_PATH') ? rtrim(env('ECOMMERCE_IMAGE_PATH'), '\\/') : null;
            $pathsToTry = array_filter([$preferredPath, public_path('img/products')]);
            
            $diskPath = null;
            foreach ($pathsToTry as $path) {
                try {
                    if (!\Illuminate\Support\Facades\File::exists($path)) {
                        \Illuminate\Support\Facades\File::makeDirectory($path, 0755, true);
                    }
                    if (is_dir($path) && is_writable($path)) {
                        $diskPath = $path;
                        break;
                    }
                } catch (\Throwable $e) {}
            }
            
            if (!$diskPath) {
                return response()->json(['success' => false, 'message' => 'No writable image directory found.'], 500);
            }
            
            // 2. Si es GIF o Video, mover directamente para conservar la animación o formato original
            if (in_array($ext, ['gif', 'mp4', 'webm'])) {
                $file->move($diskPath, $filename);
            } else {
                // Comprimir y guardar la imagen para optimizar la carga
                try {
                    $manager = new \Intervention\Image\ImageManager(new \Intervention\Image\Drivers\Gd\Driver());
                    $image = $manager->read($file->getRealPath());
                    
                    if ($image->width() > 1000) {
                        $image->scaleDown(width: 1000);
                    }
                    
                    $image->save($diskPath . '/' . $filename, quality: 80);
                } catch (\Throwable $e) {
                    $file->move($diskPath, $filename);
                }
            }
            
            // 3. Determinar el path público (URL)
            $publicPathTrim = trim(env('ECOMMERCE_IMAGE_PUBLIC_PATH', 'img/products'), '/');
            
            // Replicar la misma lógica de URL de ProductController
            $requestBase = rtrim($request->getSchemeAndHttpHost() . $request->getBasePath(), '/');
            $envBase = config('app.asset_url') ?: config('app.url') ?: env('APP_URL') ?: env('ASSET_URL');
            $baseUrlTrim = rtrim(($envBase ?: $requestBase), '/');
            
            $endsWithPublic = $publicPathTrim !== '' && substr($baseUrlTrim, -strlen($publicPathTrim)) === $publicPathTrim;
            
            if ($endsWithPublic) {
                $absoluteUrl = $baseUrlTrim . '/' . ltrim($filename, '/');
            } else {
                $absoluteUrl = $baseUrlTrim . '/' . $publicPathTrim . '/' . ltrim($filename, '/');
            }
            
            return response()->json(['success' => true, 'url' => $absoluteUrl]);
        }

        return response()->json(['success' => false], 400);
    }
}
