<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Support\Facades\Http;
use App\Facades\WasenderApi;

class WasenderApiTest extends TestCase
{
    public function test_send_text_via_facade_returns_success_and_message()
    {
        Http::fake([
            'https://www.wasenderapi.com/api/send-message' => Http::response(['success' => true, 'message' => 'Message sent'], 200),
        ]);

        $result = WasenderApi::sendText('123', 'hello');

        $this->assertIsArray($result);
        $this->assertTrue($result['success']);
        $this->assertSame('Message sent', $result['message']);
    }
}
