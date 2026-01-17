<?php

namespace App\Http\Controllers;

use App\Http\Requests\PaymentGatewayRequest;
use App\Models\PaymentGateway;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class PaymentGatewayController extends Controller
{
    public function index()
    {
        $gateways = PaymentGateway::query()->orderBy('id', 'desc')->get();

        return view('panel.payment-gateway.index', compact('gateways'));
    }

    public function create()
    {
        return view('panel.payment-gateway.form', [
            'gateway' => null,
        ]);
    }

    public function store(PaymentGatewayRequest $request): RedirectResponse
    {
        $data = $this->validatedData($request);

        PaymentGateway::create($data);

        return redirect()->route('payment-gateway.index')->with('success', __('Information saved successfully.'));
    }

    public function edit($id)
    {
        $gateway = PaymentGateway::findOrFail($id);

        return view('panel.payment-gateway.form', compact('gateway'));
    }

    public function update(PaymentGatewayRequest $request, $id): RedirectResponse
    {
        $gateway = PaymentGateway::findOrFail($id);
        $data = $this->validatedData($request, $gateway->icon);

        $gateway->update($data);

        return redirect()->route('payment-gateway.index')->with('success', __('Information updated successfully.'));
    }

    public function destroy($id): RedirectResponse
    {
        $gateway = PaymentGateway::findOrFail($id);

        $this->deleteIconIfNeeded($gateway->icon);
        $gateway->delete();

        return redirect()->route('payment-gateway.index')->with('success', __('Deleted successfully.'));
    }

    public function status($id): RedirectResponse
    {
        $gateway = PaymentGateway::findOrFail($id);
        $gateway->status = !$gateway->status;
        $gateway->save();

        return redirect()->route('payment-gateway.index')->with('success', __('Information updated successfully.'));
    }

    private function validatedData(PaymentGatewayRequest $request, ?string $previousIcon = null): array
    {
        $validated = $request->validated();

        // config llega como string JSON, persistimos como array para cast automático
        if (array_key_exists('config', $validated) && $validated['config'] !== null && $validated['config'] !== '') {
            $decoded = json_decode($validated['config'], true);
            if (json_last_error() === JSON_ERROR_NONE) {
                $validated['config'] = $decoded;
            }
        } else {
            $validated['config'] = null;
        }

        // Si no seleccionan available_types, lo dejamos como null
        if (empty($validated['available_types'])) {
            $validated['available_types'] = null;
        }

        // Icon upload
        if ($request->hasFile('icon_file')) {
            $file = $request->file('icon_file');

            $dir = public_path('img/payment_icons');
            if (!is_dir($dir)) {
                @mkdir($dir, 0755, true);
            }

            $filename = 'gateway_' . time() . '_' . mt_rand(1000, 9999) . '.' . strtolower($file->getClientOriginalExtension());
            $file->move($dir, $filename);

            $validated['icon'] = 'img/payment_icons/' . $filename;

            $this->deleteIconIfNeeded($previousIcon);
        } else {
            // Mantener icon existente si estamos en update
            if ($previousIcon !== null) {
                $validated['icon'] = $previousIcon;
            }
        }

        return $validated;
    }

    private function deleteIconIfNeeded(?string $iconPath): void
    {
        if (!$iconPath) {
            return;
        }

        $fullPath = public_path($iconPath);
        if (is_file($fullPath)) {
            @unlink($fullPath);
        }
    }
}
