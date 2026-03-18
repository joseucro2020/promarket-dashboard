<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Support\Facades\Http;
use App\Facades\WasenderApi;

class SendToNumberTest extends TestCase
{
    public function test_send_message_to_specific_number_is_successful()
    {
        $message = "Hola Edoardo Andres Lunchi Villegas, soy Alex Gonzalez * la persona encargada en entregar su pedido *128127416 de Farmatodo.\nSi tiene algún requerimiento puede comunicarse conmigo por este medio.\nAl finalizar le invito a calificar mi servicio en la página Web o App. Su opinión en muy importante para nosotros.";

        Http::fake([
            'https://www.wasenderapi.com/api/send-message' => Http::response(['success' => true, 'message' => 'Message sent'], 200),
        ]);

        $result = WasenderApi::sendText('584244470584', $message);

        $this->assertIsArray($result);
        $this->assertTrue($result['success']);
        $this->assertSame('Message sent', $result['message']);
    }
}
