<?php

namespace App\controllers\back;

use Endroid\QrCode\Color\Color;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Label\Label;
use Endroid\QrCode\Logo\Logo;
use Endroid\QrCode\RoundBlockSizeMode;
use Endroid\QrCode\Writer\PngWriter;

class QrCodeService
{
    public static function generateQrCode($data)
    {
        $qrData = json_encode($data);
        $qrCode = new QrCode(
            data: $qrData,
            encoding: new Encoding('UTF-8'),
            errorCorrectionLevel: ErrorCorrectionLevel::Low,
            size: 300,
            margin: 10,
            roundBlockSizeMode: RoundBlockSizeMode::Margin,
            foregroundColor: new Color(242, 214, 255),
            backgroundColor: new Color(0, 0, 0)
        );
        // Create generic label
        $label = new Label(
            text: 'Event Hive',
            textColor: new Color(242, 214, 255)
        );

        // Use the PNG writer to generate the QR code
        $writer = new PngWriter();
        $result = $writer->write($qrCode,null,$label);
// Save it to a file
$qrcode_uniqe = 'assets/QR/'.$data['id'] . '.png';
$result->saveToFile(__DIR__.'/../../../public/'.$qrcode_uniqe);
return $qrcode_uniqe;
    }
}
