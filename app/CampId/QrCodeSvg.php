<?php

namespace App\CampId;

interface QrCodeSvg
{
    /**
     * Renders the payload as an SVG QR code.
     */
    public function render(string $payload): string;
}
