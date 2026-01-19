<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use GuzzleHttp\Client;
use App\Models\ExchangeRate;
use Illuminate\Support\Str;

class FetchBcvRates extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'exchange_rates:fetch-bcv {--to=VES : Target currency code (default VES)} {--insecure : Disable SSL verification (use only for testing)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fetch daily exchange rates from https://www.bcv.org.ve/ and store them in exchange_rates table';

    /**
     * Known currency codes to look for on the BCV page.
     * Extend this list if you need more currencies.
     *
     * @var array
     */
    protected $currencies = [
        'USD','EUR','CNY','TRY','RUB','GBP','BRL','ARS','CLP','PEN'
    ];

    public function handle()
    {
        $to = strtoupper($this->option('to') ?: 'VES');
        $this->info("Fetching BCV page to populate rates (to={$to})...");

        // allow temporary insecure mode for environments missing CA bundle
        $insecure = (bool) $this->option('insecure');

        if ($insecure) {
            // create a client that disables SSL verification (test/dev only)
            $client = new Client(['timeout' => 15, 'verify' => false]);
        } else {
            // resolve Guzzle client from the container so tests can swap a mock instance
            $client = app()->make(Client::class, ['timeout' => 15]);
        }
        try {
            $res = $client->get('https://www.bcv.org.ve/');
            $html = (string) $res->getBody();
        } catch (\Exception $e) {
            $this->error('HTTP request failed: ' . $e->getMessage());
            return 1;
        }

        $found = 0;
        foreach ($this->currencies as $code) {
            // try to find the currency code in the HTML and extract the nearest numeric value
            $pos = stripos($html, $code);
            if ($pos === false) {
                // try searching by symbol fallback (e.g. € or $) - skip for now
                continue;
            }

            // take a small window after the currency code to look for a numeric value
            $snippet = substr($html, $pos, 400);
            // regex: find the first number-like token (digits, dots, commas)
            if (preg_match('/([0-9\.,]{3,})/', $snippet, $m)) {
                $raw = $m[1];
                $num = $this->normalizeNumber($raw);
                if ($num !== null) {
                    // persist to DB
                    // avoid duplicates: if a rate with same from/to and similar value already exists today, skip
                    // use an epsilon (tolerance) to tolerate rounding differences (0.01 = one cent)
                    $epsilon = 0.01;

                    $exists = ExchangeRate::where('currency_from', $code)
                        ->where('currency_to', $to)
                        ->whereDate('created_at', now()->toDateString())
                        ->whereRaw('ABS(`change` - ?) <= ?', [$num, $epsilon])
                        ->exists();

                    if ($exists) {
                        $this->info("Rate already exists today for {$code} -> {$to} ≈ {$num}, skipping");
                        continue;
                    }

                    try {
                        ExchangeRate::create([
                            'currency_from' => $code,
                            'currency_to' => $to,
                            'change' => $num,
                            'notes' => 'BCV import',
                            'created_at' => now(),
                        ]);
                        $this->info("Saved rate: {$code} -> {$to} = {$num}");
                        $found++;
                    } catch (\Exception $e) {
                        $this->error('DB insert failed for ' . $code . ': ' . $e->getMessage());
                    }
                } else {
                    $this->warn('Could not normalize number for ' . $code . ' raw: ' . $raw);
                }
            } else {
                $this->warn('No numeric match found near ' . $code);
            }
        }

        if ($found === 0) {
            $this->warn('No rates were found. The BCV page structure may have changed.');
            return 2;
        }

        $this->info('Done. Rates saved: ' . $found);
        return 0;
    }

    /**
     * Normalize number string to float-like decimal with dot.
     * Returns null if normalization fails.
     *
     * @param string $s
     * @return float|null
     */
    protected function normalizeNumber($s)
    {
        if (!$s) return null;
        // remove whitespace
        $s = str_replace([' ', "\n", "\r", "\t"], '', $s);
        // replace comma with dot (BCV often uses comma as decimal separator)
        $s = str_replace(',', '.', $s);
        // keep digits and dots only
        $s = preg_replace('/[^0-9\.]/', '', $s);
        if ($s === '') return null;
        // if multiple dots, keep first as decimal separator and strip others (thousands separators)
        if (substr_count($s, '.') > 1) {
            $parts = explode('.', $s);
            $decimal = array_pop($parts);
            $int = implode('', $parts);
            $s = $int . '.' . $decimal;
        }
        if (!is_numeric($s)) return null;
        return (float) $s;
    }
}
