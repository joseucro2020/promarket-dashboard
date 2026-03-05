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

            // Try uploading to remote ecommerce via SFTP first (if configured)
            $sftpUsed = false;
            if (config('filesystems.disks.ecommerce_sftp.host') || env('SFTP_HOST')) {
                try {
                    $stream = fopen($file->getRealPath(), 'r');
                    $sftpRoot = rtrim(env('SFTP_ROOT', ''), '/');
                    $remotePath = ($sftpRoot !== '' ? $sftpRoot . '/' : '') . $file_name;
                    Storage::disk('ecommerce_sftp')->put($remotePath, $stream);
                    if (is_resource($stream)) {
                        fclose($stream);
                    }
                    $savedPath = 'sftp://' . $remotePath;
                    $sftpUsed = true;
                } catch (\Throwable $e) {
                    Log::warning('SFTP upload failed', [
                        'host' => config('filesystems.disks.ecommerce_sftp.host'),
                        'target' => $remotePath ?? null,
                        'message' => $e->getMessage(),
                    ]);
                    if (isset($stream) && is_resource($stream)) {
                        fclose($stream);
                    }
                }
            }

            // Fallback to local filesystem if remote failed or not configured
            if (!$sftpUsed) {
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
            // Try SFTP upload for update
            $sftpUsed = false;
            if (config('filesystems.disks.ecommerce_sftp.host') || env('SFTP_HOST')) {
                try {
                    $stream = fopen($file->getRealPath(), 'r');
                    $sftpRoot = rtrim(env('SFTP_ROOT', ''), '/');
                    $remotePath = ($sftpRoot !== '' ? $sftpRoot . '/' : '') . $file_name;
                    Storage::disk('ecommerce_sftp')->put($remotePath, $stream);
                    if (is_resource($stream)) {
                        fclose($stream);
                    }
                    $savedPath = 'sftp://' . $remotePath;
                    $sftpUsed = true;
                    // delete old remote file if exists
                    if ($odlFile) {
                        $oldRemote = ($sftpRoot !== '' ? $sftpRoot . '/' : '') . $odlFile;
                        try { Storage::disk('ecommerce_sftp')->delete($oldRemote); } catch (\Throwable $__) {}
                    }
                } catch (\Throwable $e) {
                    Log::warning('SFTP update failed', [
                        'host' => config('filesystems.disks.ecommerce_sftp.host'),
                        'target' => $remotePath ?? null,
                        'message' => $e->getMessage(),
                    ]);
                    if (isset($stream) && is_resource($stream)) {
                        fclose($stream);
                    }
                }
            }

            if (!$sftpUsed) {
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

                // delete old local file if exists
                if ($odlFile) {
                    $oldFull = rtrim($diskPath, '\\/') . DIRECTORY_SEPARATOR . $odlFile;
                    File::delete($oldFull);
                }
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
