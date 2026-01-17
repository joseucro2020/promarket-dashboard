<?php

namespace App\Traits;
use App\Models\Device;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

trait FCMTrait{

    public function sendPushMessage($registrationIds, $message)
    {

        try{
            $curl = curl_init();
            $server_key = env('FIREBASE_SERVER_KEY','');
            $url = 'https://fcm.googleapis.com/fcm/send';
            $data = isset($message->data) ? $message->data : [];
            $data['type'] = $message->type;
            curl_setopt_array($curl, array(
                CURLOPT_URL => $url,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => "",
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => "POST",
                CURLOPT_POSTFIELDS => json_encode([
                    'registration_ids' => $registrationIds,
                    'notification' => [
                        "title" => $message->title,
                        "body" => $message->text,
                    ],
                    'data' => $data
                ]),
                CURLOPT_HTTPHEADER => array("Content-Type: application/json", "Authorization: key=".$server_key),
            ));

            $response = curl_exec($curl);
            if ($response === FALSE) {
                die('Curl failed: ' . curl_error($curl));
            }
            curl_close($curl);

            $response = json_decode($response, true);
            return $response;
        } catch (\Throwable $th) {
            report($th);
        }
    }

    public function sendPushMessageMassive($message)
    {
        try {
            Device::chunk(500, function ($devices) use($message) {
                $registrationIds = $devices->pluck('device_key');
                $response = $this->sendPushMessage($registrationIds, $message);
                Log::info("Massive Res FCM", [$response]);
                if($response && isset($response['results'])){
                    foreach ($response['results'] as $key => $value) {
                        if(isset($value['error']) && in_array($value['error'], ['NotRegistered','InvalidRegistration'])) {
                            Device::where('device_key', $registrationIds[$key])->delete();
                            unset($registrationIds[$key]);
                        }
                    }
                }
            });
            unset($message['data']);
            $message->update(['sended_at'=> Carbon::now()]);
        } catch (\Throwable $th) {
            report($th);
        }
    }
}
