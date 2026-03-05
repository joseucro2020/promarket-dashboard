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
        $diskPath = $this->getBannerImageDiskPath();
        $savedPath = null;

        if (!$diskPath) {
            return response()->json([
                'result' => false,
                'error' => __('BANNERS_IMAGE_PATH is not configured.'),
            ], 500);
        }

        if (!File::exists($diskPath)) {
            File::makeDirectory($diskPath, 0755, true);
        }

        if (!File::isWritable($diskPath)) {
            return response()->json([
                'result' => false,
                'error' => __('No write permissions on banner path.'),
                'path' => $diskPath,
            ], 500);
        }

        if ($request->id == 0) {
            $file = $request->file('file');
            $file_name = SetNameImage::set($file->getClientOriginalName(), $file->getClientOriginalExtension());

            // Save file to disk path
            $targetPath = rtrim($diskPath, '\\/') . DIRECTORY_SEPARATOR . $file_name;
            $file->move($diskPath, $file_name);
            $savedPath = $targetPath;

            if (!File::exists($targetPath)) {
                Log::error('Banner upload failed after move', ['targetPath' => $targetPath]);
                return response()->json([
                    'result' => false,
                    'error' => __('Banner image was not saved on destination path.'),
                    'path' => $targetPath,
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

            $targetPath = rtrim($diskPath, '\\/') . DIRECTORY_SEPARATOR . $file_name;
            $file->move($diskPath, $file_name);
            $savedPath = $targetPath;

            if (!File::exists($targetPath)) {
                Log::error('Banner update failed after move', ['targetPath' => $targetPath]);
                return response()->json([
                    'result' => false,
                    'error' => __('Banner image was not saved on destination path.'),
                    'path' => $targetPath,
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
        ]);
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

    private function getBannerImageDiskPath(): string
    {
        $path = env('BANNERS_IMAGE_PATH') ?: config('custom.banner_image_path');

        if (!$path) {
            return '';
        }

        return rtrim($path, '\\/');
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
