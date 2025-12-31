<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Libraries\Centaurosms;
use App\Models\User;

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

        if ($tipo == 0) { // Todos
            $destinatariosArr = [];
            foreach ($request->input('contact', []) as $contact) {
                $destinatariosArr[] = ['id' => $contact['id'], 'cel' => $contact['cel'], 'nom' => $contact['nom']];
            }
            $destinatarios = json_encode($destinatariosArr);
        } else { // Individual
            $destinatarios = json_encode(['id' => 0, 'cel' => $numerocell, 'nom' => 'Individual']);
        }

        return response()->json([
            'destinatarios' => $destinatarios,
            'sms' => $this->sms($message, $destinatarios)
        ]);
    }
}
