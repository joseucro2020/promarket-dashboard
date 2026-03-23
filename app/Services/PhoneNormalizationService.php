<?php

namespace App\Services;

class PhoneNormalizationService
{
    /**
     * Normaliza un número telefónico al formato WhatsApp de Venezuela: 58 + 10 dígitos.
     * Ejemplo válido: 584244470584
     */
    public function normalizeWhatsappVe($phone)
    {
        if ($phone === null) {
            return null;
        }

        $digits = preg_replace('/\D+/', '', (string) $phone);

        if ($digits === '') {
            return null;
        }

        while (substr($digits, 0, 2) === '00') {
            $digits = substr($digits, 2);
        }

        if (preg_match('/^58\d{10}$/', $digits)) {
            return $digits;
        }

        if (preg_match('/^0\d{10}$/', $digits)) {
            return '58' . substr($digits, 1);
        }

        if (preg_match('/^\d{10}$/', $digits)) {
            return '58' . $digits;
        }

        return null;
    }
}
