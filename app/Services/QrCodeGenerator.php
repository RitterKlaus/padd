<?php

namespace App\Services;

use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;

class QrCodeGenerator
{
    /**
     * Create a black and white QR code as SVG data URI for an image element.
     */
    public function dataUri(string $content): string
    {
        $writer = new Writer(new ImageRenderer(new RendererStyle(256, 4), new SvgImageBackEnd));

        return 'data:image/svg+xml;base64,'.base64_encode($writer->writeString($content));
    }
}
