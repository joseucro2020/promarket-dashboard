<?php

namespace App\Services;

class PhoneNormalizationService
{
    protected $supportedCountryCodes = [
        '58', '1', '52', '57', '54', '56', '51', '593', '595', '598', '591',
        '34', '507', '506', '503', '502', '504', '505', '53'
    ];

    /**
     * Normaliza un número telefónico al formato internacional para WhatsApp.
     * Ejemplo válido: 584244470584
     */
    public function normalizeWhatsappVe($phone, $preferredCountryCode = '58')
    {
        if ($phone === null) {
            return null;
        }

        $preferredCountryCode = preg_replace('/\D+/', '', (string) $preferredCountryCode);
        if (!in_array($preferredCountryCode, $this->supportedCountryCodes, true)) {
            $preferredCountryCode = '58';
        }

        $digits = preg_replace('/\D+/', '', (string) $phone);

        if ($digits === '') {
            return null;
        }

        while (substr($digits, 0, 2) === '00') {
            $digits = substr($digits, 2);
        }

        $detectedCode = $this->detectCountryCode($digits);
        if ($detectedCode !== null && strlen($digits) >= (strlen($detectedCode) + 6) && strlen($digits) <= 15) {
            return $digits;
        }

        if (preg_match('/^0\d{7,12}$/', $digits)) {
            return $preferredCountryCode . ltrim($digits, '0');
        }

        if (preg_match('/^\d{7,12}$/', $digits)) {
            return $preferredCountryCode . $digits;
        }

        return null;
    }

    protected function detectCountryCode($digits)
    {
        $codes = $this->supportedCountryCodes;
        usort($codes, function ($a, $b) {
            return strlen($b) <=> strlen($a);
        });

        foreach ($codes as $code) {
            if (strpos($digits, $code) === 0) {
                return $code;
            }
        }

        return null;
    }
}
