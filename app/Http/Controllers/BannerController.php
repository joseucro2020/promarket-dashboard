<?php

namespace App\Http\Controllers;

use App\Libraries\SetNameImage;
use App\Models\Banner;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;

class BannerController extends Controller
{
    public function index()
    {
        // $banners = Banner::orderBy('created_at', 'desc')->get();
        $banners = Banner::all();

        return view('panel.banners.index', compact('banners'));
    }

    public function upload(Request $request)
    {
        $rules = [
            'file' => 'required|mimes:jpg,jpeg,png',
        ];

        $messages = [
            'mimes' => 'Formato de archivo incorrecto. Debe ser jpg, jpeg o png'
        ];

        $attributes = [
            'file' => 'banner'
        ];

        $validation = Validator::make($request->all(), $rules, $messages, $attributes);

        if ($validation->fails()) {
            return response()->json([
                'result' => false,
                'error' => $validation->errors()->first()
            ], 422);
        }

        // Determine disk path for banners (must be explicitly configured)
        $pathSource = null;
        $diskPath = $this->getBannerImageDiskPath($pathSource);
        $savedPath = null;

        if (!$diskPath) {
            return response()->json([
                'result' => false,
                'error' => __('BANNERS_IMAGE_PATH is not configured.'),
                'configured_path_source' => $pathSource,
            ], 500);
        }

        if (!File::exists($diskPath)) {
            File::makeDirectory($diskPath, 0755, true);
        }

        if (!File::exists($diskPath)) {
            return response()->json([
                'result' => false,
                'error' => __('Banner path does not exist and could not be created.'),
                'path' => $diskPath,
                'configured_path_source' => $pathSource,
            ], 500);
        }

        if (!File::isWritable($diskPath)) {
            return response()->json([
                'result' => false,
                'error' => __('No write permissions on banner path.'),
                'path' => $diskPath,
                'configured_path_source' => $pathSource,
            ], 500);
        }

        if ($request->id == 0) {
            $file = $request->file('file');
            $file_name = SetNameImage::set($file->getClientOriginalName(), $file->getClientOriginalExtension());

            // Save file to disk path
            $targetPath = rtrim($diskPath, '\/') . DIRECTORY_SEPARATOR . $file_name;
            try {
                $file->move($diskPath, $file_name);
            } catch (\Throwable $e) {
                Log::error('Banner upload move exception', [
                    'diskPath' => $diskPath,
                    'targetPath' => $targetPath,
                    'message' => $e->getMessage(),
                ]);

                return response()->json([
                    'result' => false,
                    'error' => __('Error saving banner image.'),
                    'path' => $targetPath,
                    'detail' => $e->getMessage(),
                    'configured_path_source' => $pathSource,
                ], 500);
            }
            $savedPath = $targetPath;

            if (!File::exists($targetPath)) {
                Log::error('Banner upload failed after move', ['targetPath' => $targetPath]);
                return response()->json([
                    'result' => false,
                    'error' => __('Banner image was not saved on destination path.'),
                    'path' => $targetPath,
                    'configured_path_source' => $pathSource,
                ], 500);
            }

            $banner = new Banner;
            $banner->foto = $file_name;
            $banner->save();
            $fileId = $banner->id;
        } else {
            $item = Banner::findOrFail($request->id);
            $odlFile = $item->foto;
            $file = $request->file('file');
            $file_name = SetNameImage::set($file->getClientOriginalName(), $file->getClientOriginalExtension());

            $targetPath = rtrim($diskPath, '\/') . DIRECTORY_SEPARATOR . $file_name;
            try {
                $file->move($diskPath, $file_name);
            } catch (\Throwable $e) {
                Log::error('Banner update move exception', [
                    'diskPath' => $diskPath,
                    'targetPath' => $targetPath,
                    'message' => $e->getMessage(),
                ]);

                return response()->json([
                    'result' => false,
                    'error' => __('Error saving banner image.'),
                    'path' => $targetPath,
                    'detail' => $e->getMessage(),
                    'configured_path_source' => $pathSource,
                ], 500);
            }
            $savedPath = $targetPath;

            if (!File::exists($targetPath)) {
                Log::error('Banner update failed after move', ['targetPath' => $targetPath]);
                return response()->json([
                    'result' => false,
                    'error' => __('Banner image was not saved on destination path.'),
                    'path' => $targetPath,
                    'configured_path_source' => $pathSource,
                ], 500);
            }

            // delete old file if exists
            if ($odlFile) {
                $oldFull = rtrim($diskPath, '\\/') . DIRECTORY_SEPARATOR . $odlFile;
                File::delete($oldFull);
            }

            $item->foto = $file_name;
            $item->save();
            $fileId = $request->id;
        }

        $baseUrl = config('custom.banner_image_url');
        $imageUrl = $baseUrl
            ? rtrim($baseUrl, '/') . '/' . ltrim($file_name, '/')
            : route('banners.image', ['file' => $file_name]);

        return response()->json([
            'result' => true,
            'id' => $fileId,
            'file' => $file_name,
            'url' => $imageUrl,
            'saved_path' => $savedPath,
            'configured_path' => $diskPath,
            'configured_path_source' => $pathSource,
        ]);
    }

    public function probeWriteTxt(Request $request): JsonResponse
    {
        try {
        $pathSource = null;
        $diskPath = $this->getBannerImageDiskPath($pathSource);

        if (!$diskPath) {
            return response()->json([
                'result' => false,
                'error' => __('BANNERS_IMAGE_PATH is not configured.'),
                'configured_path_source' => $pathSource,
            ], 500);
        }

        if (!File::exists($diskPath)) {
            File::makeDirectory($diskPath, 0755, true);
        }

        if (!File::exists($diskPath)) {
            return response()->json([
                'result' => false,
                'error' => __('Banner path does not exist and could not be created.'),
                'path' => $diskPath,
                'configured_path_source' => $pathSource,
            ], 500);
        }

        if (!File::isWritable($diskPath)) {
            return response()->json([
                'result' => false,
                'error' => __('No write permissions on banner path.'),
                'path' => $diskPath,
                'configured_path_source' => $pathSource,
            ], 500);
        }

        $providedName = (string) $request->input('name', '');
        $baseName = $providedName !== '' ? pathinfo($providedName, PATHINFO_FILENAME) : 'probe_' . date('Ymd_His');
        $safeBaseName = preg_replace('/[^a-zA-Z0-9_\-]/', '_', $baseName);
        $fileName = trim($safeBaseName, '_') ?: 'probe_' . date('Ymd_His');
        $fileName .= '.txt';

        $content = (string) $request->input('content', 'Banner path write probe OK');
        $content .= PHP_EOL . 'time=' . date('c') . PHP_EOL;

        $targetPath = rtrim($diskPath, '\\/') . DIRECTORY_SEPARATOR . $fileName;

        try {
            File::put($targetPath, $content);
        } catch (\Throwable $e) {
            Log::error('Banner TXT probe failed', [
                'path' => $targetPath,
                'message' => $e->getMessage(),
            ]);

            return response()->json([
                'result' => false,
                'error' => __('Error writing probe file.'),
                'path' => $targetPath,
                'detail' => $e->getMessage(),
                'configured_path_source' => $pathSource,
            ], 500);
        }

        if (!File::exists($targetPath)) {
            return response()->json([
                'result' => false,
                'error' => __('Probe file was not saved on destination path.'),
                'path' => $targetPath,
                'configured_path_source' => $pathSource,
            ], 500);
        }

        $size = null;
        try {
            $size = File::size($targetPath);
        } catch (\Throwable $e) {
            Log::warning('Banner TXT probe size check failed', [
                'path' => $targetPath,
                'message' => $e->getMessage(),
            ]);
        }

        return response()->json([
            'result' => true,
            'file' => $fileName,
            'saved_path' => $targetPath,
            'configured_path' => $diskPath,
            'configured_path_source' => $pathSource,
            'size' => $size,
        ]);
        } catch (\Throwable $e) {
            Log::error('Banner TXT probe fatal error', [
                'message' => $e->getMessage(),
            ]);

            return response()->json([
                'result' => false,
                'error' => __('Unexpected error while probing banner write path.'),
                'detail' => $e->getMessage(),
            ], 500);
        }
    }

    public function image(string $file)
    {
        $safeFile = basename($file);
        $fullPath = rtrim($this->getBannerImageDiskPath(), '\\/') . DIRECTORY_SEPARATOR . $safeFile;

        if (!File::exists($fullPath)) {
            abort(404);
        }

        return response()->file($fullPath);
    }

    private function getBannerImageDiskPath(?string &$source = null): string
    {
        $runtimeEnvPath = getenv('BANNERS_IMAGE_PATH');
        if (!$runtimeEnvPath && isset($_ENV['BANNERS_IMAGE_PATH'])) {
            $runtimeEnvPath = $_ENV['BANNERS_IMAGE_PATH'];
        }

        if ($runtimeEnvPath) {
            $source = 'runtime-env';
            return rtrim(trim($runtimeEnvPath, " \t\n\r\0\x0B\"'"), '\\/');
        }

        $path = config('custom.banner_image_path');

        if (!$path) {
            $source = 'none';
            return '';
        }

        $source = 'config';

        return rtrim(trim((string) $path, " \t\n\r\0\x0B\"'"), '\\/');
    }

    private function getBannerImagePublicPath(): string
    {
        $path = config('custom.banner_image_public_path', 'img/slider');

        return rtrim($path, '/\\') . '/';
    }

    public function destroy(Request $request, $id)
    {
        $banner = Banner::findOrFail($id);

        if ($banner->foto) {
            $fullPath = rtrim($this->getBannerImageDiskPath(), '\\/') . DIRECTORY_SEPARATOR . $banner->foto;
            File::delete($fullPath);
        }

        $banner->delete();

        if ($request->expectsJson()) {
            return response()->json(['result' => true]);
        }

        return redirect()->route('banners.index')->with('success', __('Banner deleted successfully.'));
    }
}
