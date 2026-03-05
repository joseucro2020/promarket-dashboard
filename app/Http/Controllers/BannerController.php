<?php

namespace App\Http\Controllers;

use App\Libraries\SetNameImage;
use App\Models\Banner;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

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

            // Save file locally inside the configured disk path (no SFTP)
            $targetPath = rtrim($diskPath, '\\/') . DIRECTORY_SEPARATOR . $file_name;
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

            // Build public URL and save it in DB (avoid duplicating public path)
            $publicPath = $this->getBannerImagePublicPath();
            $publicPathTrim = trim($publicPath, '/');
            $requestBase = $request->getSchemeAndHttpHost();
            $baseUrlCandidate = $requestBase ?: config('custom.banner_image_url') ?: rtrim(config('app.url', ''), '/');
            $baseUrlTrim = rtrim($baseUrlCandidate, '/');
            $endsWithPublic = $publicPathTrim !== '' && substr($baseUrlTrim, -strlen($publicPathTrim)) === $publicPathTrim;
            if ($endsWithPublic) {
                $imageUrl = $baseUrlTrim . '/' . ltrim($file_name, '/');
            } else {
                $imageUrl = $baseUrlTrim . '/' . $publicPathTrim . '/' . ltrim($file_name, '/');
            }

            $banner = new Banner;
            $banner->foto = $imageUrl;
            $banner->save();
            $fileId = $banner->id;
        } else {
            $item = Banner::findOrFail($request->id);
            $odlFile = $item->foto;
            $file = $request->file('file');
            $file_name = SetNameImage::set($file->getClientOriginalName(), $file->getClientOriginalExtension());
            // Save file locally for update (no SFTP)
            $targetPath = rtrim($diskPath, '\\/') . DIRECTORY_SEPARATOR . $file_name;
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

            // delete old local file if exists (odlFile may be full URL)
            if ($odlFile) {
                $oldName = basename($odlFile);
                $oldFull = rtrim($diskPath, '\\/') . DIRECTORY_SEPARATOR . $oldName;
                File::delete($oldFull);
            }

            // Build public URL and save it in DB (avoid duplicating public path)
            $publicPath = $this->getBannerImagePublicPath();
            $publicPathTrim = trim($publicPath, '/');
            $requestBase = $request->getSchemeAndHttpHost();
            $baseUrlCandidate = $requestBase ?: config('custom.banner_image_url') ?: rtrim(config('app.url', ''), '/');
            $baseUrlTrim = rtrim($baseUrlCandidate, '/');
            $endsWithPublic = $publicPathTrim !== '' && substr($baseUrlTrim, -strlen($publicPathTrim)) === $publicPathTrim;
            if ($endsWithPublic) {
                $imageUrl = $baseUrlTrim . '/' . ltrim($file_name, '/');
            } else {
                $imageUrl = $baseUrlTrim . '/' . $publicPathTrim . '/' . ltrim($file_name, '/');
            }

            $item->foto = $imageUrl;
            $item->save();
            $fileId = $request->id;
        }

        // Build full public URL for the saved banner image
        // Ensure $imageUrl is defined (it may have been set above)
        if (!isset($imageUrl)) {
            $publicPath = $this->getBannerImagePublicPath(); // e.g. 'img/slider/'
            $publicPathTrim = trim($publicPath, '/');
            $requestBase = $request->getSchemeAndHttpHost();
            $baseUrlCandidate = $requestBase ?: config('custom.banner_image_url') ?: rtrim(config('app.url', ''), '/');
            $baseUrlTrim = rtrim($baseUrlCandidate, '/');
            $endsWithPublic = $publicPathTrim !== '' && substr($baseUrlTrim, -strlen($publicPathTrim)) === $publicPathTrim;
            if ($endsWithPublic) {
                $imageUrl = $baseUrlTrim . '/' . ltrim($file_name, '/');
            } else {
                $imageUrl = $baseUrlTrim . '/' . $publicPathTrim . '/' . ltrim($file_name, '/');
            }
        }

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
            // Build debug info
            $info = [
                'step' => 'init',
                'php_user' => function_exists('posix_getpwuid') && function_exists('posix_geteuid')
                    ? (posix_getpwuid(posix_geteuid())['name'] ?? 'unknown')
                    : get_current_user(),
                'php_version' => PHP_VERSION,
                'cwd' => getcwd(),
            ];

            // SFTP configuration
            $sftpHost = config('filesystems.disks.ecommerce_sftp.host') ?? env('SFTP_HOST');
            $sftpRoot = config('filesystems.disks.ecommerce_sftp.root') ?? env('SFTP_ROOT', '');
            $info['sftp_host'] = $sftpHost;
            $info['sftp_root'] = $sftpRoot;

            if (!$sftpHost) {
                return response()->json([
                    'result' => false,
                    'error' => 'SFTP not configured (SFTP_HOST missing).',
                    'debug' => $info,
                ], 500);
            }

            // Prepare probe content and remote path
            $providedName = (string) $request->input('name', '');
            $baseName = $providedName !== '' ? pathinfo($providedName, PATHINFO_FILENAME) : 'probe_' . date('Ymd_His');
            $safeBaseName = preg_replace('/[^a-zA-Z0-9_\-]/', '_', $baseName);
            $probeFile = trim($safeBaseName, '_') ?: 'probe_' . date('Ymd_His');
            $probeFile .= '.txt';

            $content = (string) $request->input('content', 'SFTP probe OK') . "\n" . 'time=' . date('c') . "\n";

            $remotePath = rtrim($sftpRoot, '/') . ($sftpRoot !== '' ? '/' : '') . $probeFile;
            $info['remote_probe'] = $remotePath;
            $info['step'] = 'sftp_put';

            try {
                $put = Storage::disk('ecommerce_sftp')->put($remotePath, $content);
                $info['sftp_put_result'] = $put;
                try {
                    $info['sftp_exists'] = Storage::disk('ecommerce_sftp')->exists($remotePath);
                } catch (\Throwable $e2) {
                    $info['sftp_exists'] = 'unknown';
                }
                // attempt cleanup
                try { Storage::disk('ecommerce_sftp')->delete($remotePath); } catch (\Throwable $__) {}

                return response()->json([
                    'result' => true,
                    'message' => 'SFTP probe successful',
                    'debug' => $info,
                ]);
            } catch (\Throwable $ex) {
                $info['sftp_error'] = $ex->getMessage();
                return response()->json([
                    'result' => false,
                    'error' => 'SFTP probe failed: ' . $ex->getMessage(),
                    'debug' => $info,
                ], 500);
            }
        } catch (\Throwable $e) {
            return response()->json([
                'result' => false,
                'error' => 'Fatal probe error: ' . $e->getMessage(),
                'trace' => $e->getTraceAsString(),
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
            $p = rtrim(trim($runtimeEnvPath, " \t\n\r\0\x0B\"'"), '\\/');
            // If path is absolute (starts with drive letter, \ or /), return as-is.
            if ($this->isAbsolutePath($p)) {
                return $p;
            }
            // If path starts with "public/", strip it to avoid duplicating public
            $p = preg_replace('#^public[\\/]#i', '', $p);
            return rtrim(public_path($p), '\\/');
        }

        $path = config('custom.banner_image_path');

        if (!$path) {
            $source = 'none';
            return '';
        }

        $source = 'config';
        $p = rtrim(trim((string) $path, " \t\n\r\0\x0B\"'"), '\\/');
        if ($this->isAbsolutePath($p)) {
            return $p;
        }
        $p = preg_replace('#^public[\\/]#i', '', $p);
        return rtrim(public_path($p), '\\/');
    }

    private function getBannerImagePublicPath(): string
    {
        $path = config('custom.banner_image_public_path', 'img/slider');

        return rtrim($path, '/\\') . '/';
    }

    /**
     * Determine whether a path is absolute (Windows drive or Unix root).
     */
    private function isAbsolutePath(string $p): bool
    {
        if ($p === '') {
            return false;
        }
        // Unix absolute
        if ($p[0] === '/' || $p[0] === '\\') {
            return true;
        }
        // Windows drive letter, e.g. C:\ or C:/
        if (strlen($p) >= 3 && ctype_alpha($p[0]) && $p[1] === ':' && ($p[2] === '\\' || $p[2] === '/')) {
            return true;
        }
        return false;
    }

    public function destroy(Request $request, $id)
    {
        $banner = Banner::findOrFail($id);

        if ($banner->foto) {
            // `foto` may contain a full URL; extract basename to delete local file
            $fileName = basename($banner->foto);
            $fullPath = rtrim($this->getBannerImageDiskPath(), '\\/') . DIRECTORY_SEPARATOR . $fileName;
            File::delete($fullPath);
        }

        $banner->delete();

        if ($request->expectsJson()) {
            return response()->json(['result' => true]);
        }

        return redirect()->route('banners.index')->with('success', __('Banner deleted successfully.'));
    }
}
