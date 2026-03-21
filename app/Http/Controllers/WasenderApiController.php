<?php

namespace App\Http\Controllers;

use App\Facades\WasenderApi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class WasenderApiController extends Controller
{
    public function sendText(Request $request)
    {
        $expectedToken = config('wasenderapi.personal_access_token') ?: env('WASENDERAPI_PERSONAL_ACCESS_TOKEN');
        $incomingToken = $request->bearerToken() ?: $request->header('X-API-KEY');

        Log::channel('whatsapp')->info('wasender_api_request_received', [
            'source' => 'external_api',
            'ip' => $request->ip(),
            'to' => $request->input('to'),
            'message_length' => mb_strlen((string) $request->input('message', '')),
        ]);

        if (empty($expectedToken) || $incomingToken !== $expectedToken) {
            Log::channel('whatsapp')->warning('wasender_api_request_unauthorized', [
                'source' => 'external_api',
                'ip' => $request->ip(),
                'to' => $request->input('to'),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 401);
        }

        $validated = $request->validate([
            'to' => ['required', 'string'],
            'message' => ['required', 'string', 'max:2000'],
        ]);

        try {
            $result = WasenderApi::sendText($validated['to'], $validated['message']);

            Log::channel('whatsapp')->info('wasender_api_send_success', [
                'source' => 'external_api',
                'ip' => $request->ip(),
                'to' => $validated['to'],
                'message_length' => mb_strlen((string) $validated['message']),
                'result' => $result,
            ]);

            return response()->json([
                'success' => true,
                'provider' => 'wasender',
                'to' => $validated['to'],
                'result' => $result,
            ]);
        } catch (\Throwable $e) {
            Log::channel('whatsapp')->error('wasender_api_send_error', [
                'source' => 'external_api',
                'ip' => $request->ip(),
                'to' => $validated['to'] ?? null,
                'message_length' => isset($validated['message']) ? mb_strlen((string) $validated['message']) : null,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'provider' => 'wasender',
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}
