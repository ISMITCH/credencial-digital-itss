<?php

use App\Models\Credential;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\SvgWriter;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

/*
|--------------------------------------------------------------------------
| Verificación pública de la credencial
|--------------------------------------------------------------------------
|
| Esta página puede abrirse sin iniciar sesión al escanear el código QR.
|
*/

Route::get(
    '/credencial/{credential:qr_token}',
    function (Credential $credential) {
        $credential->load([
            'student.career',
        ]);

        $student = $credential->student;

        abort_if(
            ! $student,
            404,
            'No se encontró al estudiante relacionado con esta credencial.'
        );

        $isExpired = $credential->expires_at
            ? $credential->expires_at
                ->copy()
                ->endOfDay()
                ->isPast()
            : false;

        $credentialIsActive =
            $credential->status === 'active';

        $studentIsActive =
            $student->status === 'active';

        if (! $credentialIsActive || ! $studentIsActive) {
            $verificationStatus = 'inactive';
        } elseif ($isExpired) {
            $verificationStatus = 'expired';
        } else {
            $verificationStatus = 'valid';
        }

        return view(
            'credentials.verify',
            [
                'credential' => $credential,
                'student' => $student,
                'verificationStatus' => $verificationStatus,
            ]
        );
    }
)->name('credential.verify');

/*
|--------------------------------------------------------------------------
| Imagen del código QR
|--------------------------------------------------------------------------
|
| Esta ruta genera la imagen del QR. El código ahora contiene la dirección
| pública donde se puede consultar la vigencia de la credencial.
|
*/

Route::get(
    '/credential-qr/{credential:qr_token}',
    function (Credential $credential) {
        $writer = new SvgWriter();

        $verificationUrl = route(
            'credential.verify',
            [
                'credential' => $credential->qr_token,
            ]
        );

        $qrCode = new QrCode(
            data: $verificationUrl,
            size: 300,
            margin: 10,
        );

        $result = $writer->write($qrCode);

        return response(
            $result->getString(),
            200,
            [
                'Content-Type' => $result->getMimeType(),
                'Content-Disposition' => 'inline',
            ]
        );
    }
)
    ->middleware('auth')
    ->name('credential.qr');