<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Event;
use App\Events\MessagesUpserted;

class WasenderWebhookController extends Controller
{
    public function handle(Request $request)
    {
        $payload = $request->getContent();
        $secret = config('wasenderapi.webhook_secret');

        $signatureHeader = $request->header('X-Wasender-Signature') ?: $request->header('Wasender-Signature');

        if ($secret) {
            $computed = hash_hmac('sha256', $payload, $secret);
            if (!hash_equals($computed, (string) $signatureHeader)) {
                return response()->json(['message' => 'Invalid signature'], 403);
            }
        }

        $data = json_decode($payload, true);

        // Dispatch events based on payload contents. Example for messages upserted:
        if (isset($data['messages']) || (isset($data['type']) && $data['type'] === 'messages_upserted')) {
            Event::dispatch(new MessagesUpserted($data));
        }

        return response()->json(['ok' => true]);
    }
}
