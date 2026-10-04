<?php

namespace App\CampId;

use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;

class BaconQrCodeSvg implements QrCodeSvg
{
    public function render(string $payload): string
    {
        $writer = new Writer(new ImageRenderer(new RendererStyle(400, 0), new SvgImageBackEnd));

        return $writer->writeString($payload);
    }
}
