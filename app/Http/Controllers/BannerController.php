<?php

namespace App\Http\Controllers;

use App\Libraries\SetNameImage;
use App\Models\Banner;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;

class BannerController extends Controller
{
    public function index()
    {
        $banners = Banner::orderBy('created_at', 'desc')->get();

        return view('panel.banners.index', compact('banners'));
    }

    public function upload(Request $request): JsonResponse
    {
        $data = $request->validate(
            [
                'file' => 'required|file|mimes:jpg,jpeg,png|max:5120',
                'id' => 'nullable|integer|min:0',
            ],
            [
                'file.required' => __('Please select an image.'),
                'file.mimes' => __('Invalid file format. Must be jpg, jpeg or png.'),
            ]
        );

        $directory = public_path('img/slider');
        if (!File::exists($directory)) {
            File::makeDirectory($directory, 0755, true);
        }

        $file = $request->file('file');
        $fileName = SetNameImage::set($file->getClientOriginalName(), $file->getClientOriginalExtension());
        $file->move($directory, $fileName);

        $id = (int)($data['id'] ?? 0);

        if ($id === 0) {
            $banner = Banner::create(['foto' => $fileName]);
        } else {
            $banner = Banner::findOrFail($id);
            $oldFile = $banner->foto;

            $banner->foto = $fileName;
            $banner->save();

            if ($oldFile) {
                File::delete(public_path('img/slider/' . $oldFile));
            }
        }

        return response()->json([
            'result' => true,
            'id' => $banner->id,
            'file' => $banner->foto,
            'url' => asset('img/slider/' . $banner->foto),
        ]);
    }

    public function destroy(Request $request, $id)
    {
        $banner = Banner::findOrFail($id);

        if ($banner->foto) {
            File::delete(public_path('img/slider/' . $banner->foto));
        }

        $banner->delete();

        if ($request->expectsJson()) {
            return response()->json(['result' => true]);
        }

        return redirect()->route('banners.index')->with('success', __('Banner deleted successfully.'));
    }
}
