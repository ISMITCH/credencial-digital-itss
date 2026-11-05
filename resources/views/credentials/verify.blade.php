@php
    $fullName = trim(
        $student->first_name . ' ' .
        $student->paternal_last_name . ' ' .
        ($student->maternal_last_name ?? '')
    );

    $initials = strtoupper(
        mb_substr($student->first_name ?? '', 0, 1) .
        mb_substr($student->paternal_last_name ?? '', 0, 1)
    );

    $statusInformation = match ($verificationStatus) {
        'valid' => [
            'label' => 'Credencial vigente',
            'message' => 'La identidad y vigencia de esta credencial fueron verificadas correctamente.',
            'class' => 'status-valid',
        ],
        'expired' => [
            'label' => 'Credencial vencida',
            'message' => 'La fecha de vigencia de esta credencial ya terminó.',
            'class' => 'status-expired',
        ],
        'inactive' => [
            'label' => 'Credencial inactiva',
            'message' => 'Esta credencial o el registro del estudiante se encuentra inactivo.',
            'class' => 'status-inactive',
        ],
        default => [
            'label' => 'No verificada',
            'message' => 'No fue posible comprobar el estado de la credencial.',
            'class' => 'status-inactive',
        ],
    };
@endphp

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <meta
        name="robots"
        content="noindex, nofollow"
    >

    <title>
        Verificación de credencial - ITSS
    </title>

    <style>
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            min-height: 100vh;
            padding: 24px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: Arial, Helvetica, sans-serif;
            background:
                linear-gradient(
                    135deg,
                    #eef2f7 0%,
                    #f8fafc 50%,
                    #e9eef5 100%
                );
            color: #172033;
        }

        .page {
            width: 100%;
            max-width: 520px;
        }

        .institution {
            margin-bottom: 18px;
            text-align: center;
        }

        .institution-logo {
            width: 72px;
            height: 72px;
            margin: 0 auto 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            background: #17365d;
            border: 4px solid #e8ad29;
            color: #ffffff;
            font-size: 22px;
            font-weight: 800;
            letter-spacing: 1px;
        }

        .institution h1 {
            margin: 0;
            font-size: 20px;
            line-height: 1.3;
            color: #17365d;
        }

        .institution p {
            margin: 5px 0 0;
            font-size: 14px;
            color: #667085;
        }

        .credential {
            overflow: hidden;
            border-radius: 22px;
            border: 1px solid #d8dee8;
            background: #ffffff;
            box-shadow:
                0 20px 45px rgba(23, 54, 93, 0.15);
        }

        .credential-header {
            padding: 18px 24px;
            background:
                linear-gradient(
                    135deg,
                    #17365d,
                    #22558d
                );
            color: #ffffff;
            text-align: center;
        }

        .credential-header h2 {
            margin: 0;
            font-size: 20px;
        }

        .credential-header p {
            margin: 5px 0 0;
            color: #dce9f7;
            font-size: 13px;
        }

        .credential-body {
            padding: 24px;
        }

        .student-main {
            display: flex;
            align-items: center;
            gap: 18px;
            margin-bottom: 22px;
        }

        .student-photo,
        .student-initials {
            width: 105px;
            height: 125px;
            flex-shrink: 0;
            border-radius: 14px;
            border: 3px solid #e8ad29;
            object-fit: cover;
            background: #edf2f7;
        }

        .student-initials {
            display: flex;
            align-items: center;
            justify-content: center;
            color: #17365d;
            font-size: 34px;
            font-weight: 800;
        }

        .student-name {
            margin: 0 0 8px;
            font-size: 22px;
            line-height: 1.25;
            color: #172033;
        }

        .student-number {
            margin: 0;
            color: #667085;
            font-size: 14px;
        }

        .status-box {
            margin-bottom: 22px;
            padding: 14px;
            border-radius: 12px;
            text-align: center;
        }

        .status-box strong {
            display: block;
            margin-bottom: 5px;
            font-size: 17px;
        }

        .status-box span {
            font-size: 13px;
            line-height: 1.4;
        }

        .status-valid {
            border: 1px solid #86d5a6;
            background: #ecfdf3;
            color: #08783f;
        }

        .status-expired {
            border: 1px solid #f1c46d;
            background: #fff8e8;
            color: #995f00;
        }

        .status-inactive {
            border: 1px solid #efa3a3;
            background: #fff1f1;
            color: #a11a1a;
        }

        .information {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 16px;
        }

        .information-item {
            min-width: 0;
        }

        .information-item.full {
            grid-column: 1 / -1;
        }

        .information-label {
            display: block;
            margin-bottom: 4px;
            color: #7a8495;
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 0.04em;
        }

        .information-value {
            display: block;
            color: #172033;
            font-size: 15px;
            font-weight: 700;
            overflow-wrap: anywhere;
        }

        .credential-footer {
            padding: 16px 24px;
            border-top: 1px solid #e5e9f0;
            background: #f8fafc;
            text-align: center;
            color: #667085;
            font-size: 12px;
            line-height: 1.5;
        }

        .verification-time {
            margin-top: 16px;
            text-align: center;
            color: #7a8495;
            font-size: 12px;
        }

        @media (max-width: 520px) {
            body {
                padding: 14px;
                align-items: flex-start;
            }

            .student-main {
                flex-direction: column;
                text-align: center;
            }

            .information {
                grid-template-columns: 1fr;
            }

            .information-item.full {
                grid-column: auto;
            }
        }
    </style>
</head>

<body>
    <main class="page">
        <header class="institution">
            <div class="institution-logo">
                ITSS
            </div>

            <h1>
                Instituto Tecnológico Superior de la Región Sierra
            </h1>

            <p>
                Sistema de verificación de identidad institucional
            </p>
        </header>

        <section class="credential">
            <div class="credential-header">
                <h2>
                    Credencial digital de estudiante
                </h2>

                <p>
                    Información institucional verificada
                </p>
            </div>

            <div class="credential-body">
                <div class="student-main">
                    @if (filled($student->photo_path))
                        <img
                            src="{{ asset(
                                'storage/' .
                                ltrim($student->photo_path, '/')
                            ) }}"
                            alt="Fotografía de {{ $fullName }}"
                            class="student-photo"
                        >
                    @else
                        <div class="student-initials">
                            {{ $initials ?: 'IT' }}
                        </div>
                    @endif

                    <div>
                        <h2 class="student-name">
                            {{ $fullName }}
                        </h2>

                        <p class="student-number">
                            Matrícula:
                            <strong>
                                {{ $student->student_number }}
                            </strong>
                        </p>
                    </div>
                </div>

                <div class="status-box {{ $statusInformation['class'] }}">
                    <strong>
                        {{ $statusInformation['label'] }}
                    </strong>

                    <span>
                        {{ $statusInformation['message'] }}
                    </span>
                </div>

                <div class="information">
                    <div class="information-item full">
                        <span class="information-label">
                            Carrera
                        </span>

                        <span class="information-value">
                            {{ $student->career?->name ?? 'Sin carrera registrada' }}
                        </span>
                    </div>

                    <div class="information-item">
                        <span class="information-label">
                            Semestre
                        </span>

                        <span class="information-value">
                            {{ $student->semester ?? 'Sin registrar' }}
                        </span>
                    </div>

                    <div class="information-item">
                        <span class="information-label">
                            Número de credencial
                        </span>

                        <span class="information-value">
                            {{ $credential->credential_number }}
                        </span>
                    </div>

                    <div class="information-item">
                        <span class="information-label">
                            Fecha de emisión
                        </span>

                        <span class="information-value">
                            {{ $credential->issued_at
                                ? $credential->issued_at->format('d/m/Y')
                                : 'Sin registrar' }}
                        </span>
                    </div>

                    <div class="information-item">
                        <span class="information-label">
                            Vigencia
                        </span>

                        <span class="information-value">
                            {{ $credential->expires_at
                                ? $credential->expires_at->format('d/m/Y')
                                : 'Sin vencimiento' }}
                        </span>
                    </div>
                </div>
            </div>

            <footer class="credential-footer">
                Esta consulta permite verificar el estado actual
                de la credencial. No muestra información privada
                de acceso del estudiante.
            </footer>
        </section>

        <p class="verification-time">
            Consulta realizada el
            {{ now()->format('d/m/Y H:i') }}
        </p>
    </main>
</body>
</html>