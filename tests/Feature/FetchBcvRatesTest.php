<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use GuzzleHttp\Client;
use Psr\Http\Message\ResponseInterface;
use App\Models\ExchangeRate;
use Illuminate\Support\Facades\Artisan;

class FetchBcvRatesTest extends TestCase
{
    use RefreshDatabase;

    public function test_command_creates_rates_from_bcv_html()
    {
        // sample HTMLs: first with 250,00 then a very close value 250.0001
        $html1 = '<html><body><div>USD</div><div>250,00</div></body></html>';
        $html2 = '<html><body><div>USD</div><div>250.0001</div></body></html>';

        $mockResponse = Mockery::mock(ResponseInterface::class);
        // return different bodies on consecutive calls
        $mockResponse->shouldReceive('getBody')->andReturn($html1, $html2);

        $mockClient = Mockery::mock(Client::class);
        $mockClient->shouldReceive('get')->andReturn($mockResponse);

        // bind the mock client into the container so the command uses it
        $this->app->instance(Client::class, $mockClient);

        // first run: inserts
        Artisan::call('exchange_rates:fetch-bcv', ['--to' => 'VES']);

        $this->assertDatabaseHas('exchange_rates', [
            'currency_from' => 'USD',
            'currency_to' => 'VES',
        ]);

        // second run: value is very close; command should treat it as duplicate and not insert
        Artisan::call('exchange_rates:fetch-bcv', ['--to' => 'VES']);

        $this->assertDatabaseCount('exchange_rates', 1);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}
