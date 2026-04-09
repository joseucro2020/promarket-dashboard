<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;
use App\Models\ExchangeRate;
use App\Http\Requests\ExchangeRateRequest;

class ExchangeRateController extends Controller
{
    public function diagnose(Request $request)
    {
        $diagnostic = [
            'ok' => true,
            'module' => 'exchange-rates',
            'timestamp' => now()->toDateTimeString(),
            'request' => [
                'url' => $request->fullUrl(),
                'method' => $request->method(),
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'referer' => $request->headers->get('referer'),
                'accept' => $request->headers->get('accept'),
                'host' => $request->getHost(),
                'is_secure' => $request->isSecure(),
            ],
            'view' => [
                'name' => 'panel.exchange_rates.index',
                'exists' => view()->exists('panel.exchange_rates.index'),
            ],
        ];

        try {
            $ratesQuery = ExchangeRate::where('currency_from', 'USD')->orderBy('created_at', 'desc');
            $ratesCount = (clone $ratesQuery)->count();
            $latestRate = $ratesQuery->first();

            $diagnostic['database'] = [
                'connection' => config('database.default'),
                'rates_count' => $ratesCount,
                'latest_rate' => $latestRate ? [
                    'id' => $latestRate->id,
                    'currency_from' => $latestRate->currency_from,
                    'currency_to' => $latestRate->currency_to,
                    'change' => $latestRate->change,
                    'created_at' => optional($latestRate->created_at)->toDateTimeString(),
                ] : null,
            ];
        } catch (\Throwable $exception) {
            $diagnostic['ok'] = false;
            $diagnostic['database_error'] = [
                'message' => $exception->getMessage(),
                'class' => get_class($exception),
            ];
        }

        return response()->json($diagnostic);
    }

    public function index(Request $request)
    {
        try {
            $rates = ExchangeRate::where('currency_from', 'USD')
                ->orderBy('created_at', 'desc')
                ->get();

            return response()->view('panel.exchange_rates.index', compact('rates'));
        } catch (\Throwable $exception) {
            Log::error('Exchange rates module failed to render', [
                'message' => $exception->getMessage(),
                'url' => $request->fullUrl(),
                'method' => $request->method(),
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'referer' => $request->headers->get('referer'),
                'accept' => $request->headers->get('accept'),
                'trace' => $exception->getTraceAsString(),
            ]);

            throw $exception;
        }
    }

    public function create()
    {
        return view('panel.exchange_rates.form');
    }

    public function store(ExchangeRateRequest $request)
    {
        ExchangeRate::create($request->validated());
        return redirect()->route('exchange-rates.index')->with('success', 'Tasa creada correctamente.');
    }

    public function edit($id)
    {
        $rate = ExchangeRate::findOrFail($id);
        return view('panel.exchange_rates.form', compact('rate'));
    }

    public function update(ExchangeRateRequest $request, $id)
    {
        $rate = ExchangeRate::findOrFail($id);
        $rate->update($request->validated());
        return redirect()->route('exchange-rates.index')->with('success', 'Tasa actualizada correctamente.');
    }

    public function destroy($id)
    {
        $rate = ExchangeRate::findOrFail($id);
        $rate->delete();
        return redirect()->route('exchange-rates.index')->with('success', 'Tasa eliminada.');
    }

    /**
     * Execute BCV fetch command on demand from UI
     */
    public function fetchBcvNow(Request $request)
    {
        try {
            $code = Artisan::call('exchange_rates:fetch-bcv --insecure');
            $output = trim(Artisan::output());
            if ($code === 0) {
                $message = $output ?: __('Rates fetched successfully.');
            } else {
                $message = $output ?: __('Fetch command returned code :code', ['code' => $code]);
            }
            return redirect()->route('exchange-rates.index')->with('success', $message);
        } catch (\Exception $e) {
            return redirect()->route('exchange-rates.index')->with('success', 'Error: ' . $e->getMessage());
        }
    }
}
