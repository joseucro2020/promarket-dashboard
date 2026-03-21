<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Libraries\Centaurosms;
use App\Facades\WasenderApi;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class SmsController extends Controller
{
    public function index()
    {
        $contact = User::select('id', 'name as nom', 'telefono as cel')
            ->where('nivel', '1')
            ->whereRaw('LENGTH(telefono) = 11')
            ->where('telefono', 'like', '%04%')
            ->get()
            ->makeHidden(['full_document', 'status_name', 'es_date']);

        return view('panel.sms.index')->with(['contact' => $contact, 'smsdisponible' => $this->smsDisponible()]);
    }

    public function sms($msg, $destinatarios)
    {
        if (! class_exists('\App\Libraries\Centaurosms')) {
            return ['response' => ['sms_enviado' => 0]];
        }

        $SMS = new Centaurosms(env('CENTAURO_KEY'), env('CENTAURO_SECRET'));
        $result = $SMS->set_sms_send($destinatarios, $msg);
        return $result;
    }

    public function smsDisponible()
    {
        if (! class_exists('\App\Libraries\Centaurosms')) {
            return 0;
        }

        $SMS = new Centaurosms(env('CENTAURO_KEY'), env('CENTAURO_SECRET'));
        $SMS_disponibles = $SMS->get_sms_disponibles();

        return isset($SMS_disponibles['response']['sms_disponibles']) ? $SMS_disponibles['response']['sms_disponibles'] : 0;
    }

    public function enviar(Request $request)
    {
        $message = $request->input('description');
        $numerocell = $request->input('numerocell');
        $tipo = $request->input('tipo');
        $provider = $request->input('provider', 'centauro');

        if ($tipo == 0) { // Todos
            $destinatariosArr = [];
            foreach ($request->input('contact', []) as $contact) {
                $destinatariosArr[] = ['id' => $contact['id'], 'cel' => $contact['cel'], 'nom' => $contact['nom']];
            }
            $destinatarios = json_encode($destinatariosArr);
        } else { // Individual
            $destinatarios = json_encode(['id' => 0, 'cel' => $numerocell, 'nom' => 'Individual']);
        }

        // If provider is wasender, send via Wasender WhatsApp API
        if ($provider === 'wasender') {
            try {
                // use the Wasender facade which returns array response
                $to = $numerocell ?: json_decode($destinatarios, true)[0]['cel'] ?? null;

                Log::channel('whatsapp')->info('wasender_panel_send_requested', [
                    'source' => 'panel_sms',
                    'user_id' => optional($request->user())->id,
                    'ip' => $request->ip(),
                    'to' => $to,
                    'message_length' => mb_strlen((string) $message),
                    'tipo' => $tipo,
                ]);

                $wasenderResult = WasenderApi::sendText($to, $message);

                Log::channel('whatsapp')->info('wasender_panel_send_success', [
                    'source' => 'panel_sms',
                    'user_id' => optional($request->user())->id,
                    'ip' => $request->ip(),
                    'to' => $to,
                    'message_length' => mb_strlen((string) $message),
                    'result' => $wasenderResult,
                ]);

                return response()->json([
                    'provider' => 'wasender',
                    'to' => $to,
                    'result' => $wasenderResult
                ]);
            } catch (\Exception $e) {
                Log::channel('whatsapp')->error('wasender_panel_send_error', [
                    'source' => 'panel_sms',
                    'user_id' => optional($request->user())->id,
                    'ip' => $request->ip(),
                    'to' => $to ?? null,
                    'message_length' => mb_strlen((string) $message),
                    'error' => $e->getMessage(),
                ]);

                return response()->json([
                    'provider' => 'wasender',
                    'error' => $e->getMessage()
                ], 500);
            }
        }

        return response()->json([
            'destinatarios' => $destinatarios,
            'sms' => $this->sms($message, $destinatarios)
        ]);
    }
}
