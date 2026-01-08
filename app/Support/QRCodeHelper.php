<?php

namespace App\Support;

use PragmaRX\Google2FAQRCode\Google2FA;

class QRCodeHelper
{
    /**
     * Generate QR code HTML that works across different environments.
     * 
     * The Google2FA library returns different formats:
     * - SVG XML when Imagick is not available (Windows typically)
     * - data:image/png;base64,... when Imagick is available (macOS typically)
     * 
     * This helper detects the format and returns appropriate HTML.
     *
     * @param string $company
     * @param string $holder
     * @param string $secret
     * @param int $size
     * @return string HTML for displaying the QR code
     */
    public static function generate(string $company, string $holder, string $secret, int $size = 200): string
    {
        $google2fa = new Google2FA();
        $qrCode = $google2fa->getQRCodeInline($company, $holder, $secret, $size);

        // Check if it's a data URI (starts with "data:")
        if (str_starts_with($qrCode, 'data:')) {
            // It's a base64-encoded image, use img tag
            return '<img src="' . $qrCode . '" alt="QR Code" class="qr-code-img" style="width: ' . $size . 'px; height: ' . $size . 'px;">';
        }

        // It's SVG content, output directly with wrapper for styling
        return '<div class="qr-code-svg" style="width: ' . $size . 'px; height: ' . $size . 'px;">' . $qrCode . '</div>';
    }
}
