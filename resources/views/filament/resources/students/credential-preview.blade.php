@php
    $studentRecord = $record ?? null;
    $credential = $studentRecord?->credential;

    $firstName = $get('first_name')
        ?: $studentRecord?->first_name
        ?: '';

    $paternalLastName = $get('paternal_last_name')
        ?: $studentRecord?->paternal_last_name
        ?: '';

    $maternalLastName = $get('maternal_last_name')
        ?: $studentRecord?->maternal_last_name
        ?: '';

    $fullName = trim(
        $firstName . ' ' .
        $paternalLastName . ' ' .
        $maternalLastName
    );

    if (blank($fullName)) {
        $fullName = 'Nombre del estudiante';
    }

    $studentNumber = $get('student_number')
        ?: $studentRecord?->student_number
        ?: 'Matrícula pendiente';

    $semester = $get('semester')
        ?: $studentRecord?->semester
        ?: 'Sin asignar';

    $careerId = $get('career_id')
        ?: $studentRecord?->career_id;

    $careerName = $careerId
        ? \App\Models\Career::query()
            ->whereKey($careerId)
            ->value('name')
        : null;

    $careerName = $careerName ?: 'Carrera sin asignar';

    $studentStatus = $get('status')
        ?: $studentRecord?->status
        ?: 'active';

    /*
     * Obtener la fotografía, tanto si acaba de seleccionarse
     * como si ya se encontraba guardada.
     */
    $photoState = $get('photo_path');

    if (is_array($photoState)) {
        $photoState = collect($photoState)
            ->flatten()
            ->first(
                fn ($value): bool =>
                    is_string($value)
                    || $value instanceof
                        \Livewire\Features\SupportFileUploads\TemporaryUploadedFile
            );
    }

    $photoUrl = null;

    if (
        $photoState instanceof
        \Livewire\Features\SupportFileUploads\TemporaryUploadedFile
    ) {
        try {
            $photoUrl = $photoState->temporaryUrl();
        } catch (\Throwable $exception) {
            $photoUrl = null;
        }
    } else {
        $storedPhotoPath =
            is_string($photoState) && filled($photoState)
                ? $photoState
                : $studentRecord?->photo_path;

        if (is_string($storedPhotoPath) && filled($storedPhotoPath)) {
            $storedPhotoPath = trim($storedPhotoPath);

            if (
                str_starts_with($storedPhotoPath, 'http://')
                || str_starts_with($storedPhotoPath, 'https://')
            ) {
                $photoUrl = $storedPhotoPath;
            } elseif (
                str_starts_with($storedPhotoPath, '/storage/')
            ) {
                $photoUrl = url($storedPhotoPath);
            } elseif (
                str_starts_with($storedPhotoPath, 'storage/')
            ) {
                $photoUrl = url('/' . $storedPhotoPath);
            } else {
                $storedPhotoPath = ltrim($storedPhotoPath, '/');

                $configuredDisk = (string) config(
                    'filesystems.default',
                    'public'
                );

                $diskCandidates = array_values(
                    array_unique([
                        $configuredDisk,
                        'public',
                    ])
                );

                foreach ($diskCandidates as $diskName) {
                    try {
                        $disk = \Illuminate\Support\Facades\Storage::disk(
                            $diskName
                        );

                        if (! $disk->exists($storedPhotoPath)) {
                            continue;
                        }

                        if ($diskName === 'public') {
                            $photoUrl = url(
                                '/storage/' . $storedPhotoPath
                            );
                        } else {
                            try {
                                $photoUrl = $disk->temporaryUrl(
                                    $storedPhotoPath,
                                    now()->addMinutes(30)
                                );
                            } catch (\Throwable $exception) {
                                $photoUrl = null;
                            }
                        }

                        if (filled($photoUrl)) {
                            break;
                        }
                    } catch (\Throwable $exception) {
                        continue;
                    }
                }

                if (blank($photoUrl)) {
                    $photoUrl = url(
                        '/storage/' . $storedPhotoPath
                    );
                }
            }
        }
    }

    $initials = strtoupper(
        mb_substr($firstName, 0, 1) .
        mb_substr($paternalLastName, 0, 1)
    );

    if (blank($initials)) {
        $initials = 'IT';
    }

    $credentialExpired =
        $credential?->expires_at
        && $credential->expires_at
            ->copy()
            ->endOfDay()
            ->isPast();

    if (! $credential) {
        $credentialLabel = 'Sin credencial asignada';
        $credentialClass = 'credential-status-pending';
    } elseif (
        $studentStatus !== 'active'
        || $credential->status !== 'active'
    ) {
        $credentialLabel = 'Credencial inactiva';
        $credentialClass = 'credential-status-inactive';
    } elseif ($credentialExpired) {
        $credentialLabel = 'Credencial vencida';
        $credentialClass = 'credential-status-expired';
    } else {
        $credentialLabel = 'Credencial vigente';
        $credentialClass = 'credential-status-valid';
    }

    $credentialNumber =
        $credential?->credential_number
        ?: 'Pendiente de asignación';

    $issuedAt = $credential?->issued_at
        ? $credential->issued_at->format('d/m/Y')
        : 'Sin asignar';

    $expiresAt = $credential?->expires_at
        ? $credential->expires_at->format('d/m/Y')
        : 'Sin asignar';
@endphp

<style>
    .credential-preview-container {
        width: 100%;
        padding: 6px 0;
    }

    .credential-preview-title {
        margin: 0 0 10px;
        color: #667085;
        font-size: 12px;
        text-align: center;
    }

    .credential-preview-card {
        width: 100%;
        max-width: 620px;
        margin: 0 auto;
        overflow: hidden;
        border: 1px solid #d8dee8;
        border-radius: 16px;
        background: #ffffff;
        color: #172033;
        box-shadow: 0 10px 24px rgba(23, 54, 93, 0.13);
    }

    .credential-preview-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        padding: 12px 16px;
        background: linear-gradient(135deg, #17365d, #22558d);
        color: #ffffff;
    }

    .credential-preview-institution {
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .credential-preview-logo {
        width: 44px;
        height: 44px;
        display: flex;
        flex-shrink: 0;
        align-items: center;
        justify-content: center;
        border: 2px solid #e8ad29;
        border-radius: 50%;
        color: #ffffff;
        font-size: 13px;
        font-weight: 800;
    }

    .credential-preview-institution h3 {
        margin: 0;
        font-size: 14px;
        line-height: 1.2;
    }

    .credential-preview-institution p {
        margin: 3px 0 0;
        color: #dce9f7;
        font-size: 10px;
    }

    .credential-preview-type {
        color: #e8ad29;
        font-size: 11px;
        font-weight: 700;
        text-align: right;
    }

    .credential-preview-body {
        display: grid;
        grid-template-columns: 96px minmax(0, 1fr) 88px;
        gap: 14px;
        align-items: center;
        padding: 16px;
    }

    .credential-preview-photo,
    .credential-preview-initials {
        width: 96px;
        height: 115px;
        border: 2px solid #e8ad29;
        border-radius: 11px;
        background: #edf2f7;
    }

    .credential-preview-photo {
        display: block;
        object-fit: cover;
    }

    .credential-preview-initials {
        align-items: center;
        justify-content: center;
        color: #17365d;
        font-size: 30px;
        font-weight: 800;
    }

    .credential-preview-name {
        margin: 0 0 4px;
        color: #172033;
        font-size: 18px;
        line-height: 1.2;
    }

    .credential-preview-number {
        margin: 0 0 11px;
        color: #5f6b7a;
        font-size: 12px;
    }

    .credential-preview-information {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 8px 14px;
    }

    .credential-preview-field {
        min-width: 0;
    }

    .credential-preview-field-full {
        grid-column: 1 / -1;
    }

    .credential-preview-label {
        display: block;
        margin-bottom: 2px;
        color: #7a8495;
        font-size: 9px;
        letter-spacing: 0.04em;
        text-transform: uppercase;
    }

    .credential-preview-value {
        display: block;
        color: #172033;
        font-size: 11px;
        font-weight: 700;
        overflow-wrap: anywhere;
    }

    .credential-preview-qr {
        text-align: center;
    }

    .credential-preview-qr img {
        width: 82px;
        height: 82px;
        padding: 4px;
        border: 1px solid #d8dee8;
        border-radius: 8px;
        background: #ffffff;
    }

    .credential-preview-qr-placeholder {
        width: 82px;
        height: 82px;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 8px;
        border: 2px dashed #c5ccd6;
        border-radius: 8px;
        background: #f8fafc;
        color: #7a8495;
        font-size: 9px;
        line-height: 1.2;
    }

    .credential-preview-status {
        display: inline-block;
        margin-top: 7px;
        padding: 4px 7px;
        border-radius: 999px;
        font-size: 9px;
        font-weight: 700;
    }

    .credential-status-valid {
        border: 1px solid #86d5a6;
        background: #ecfdf3;
        color: #08783f;
    }

    .credential-status-pending {
        border: 1px solid #c5ccd6;
        background: #f3f4f6;
        color: #596273;
    }

    .credential-status-expired {
        border: 1px solid #f1c46d;
        background: #fff8e8;
        color: #995f00;
    }

    .credential-status-inactive {
        border: 1px solid #efa3a3;
        background: #fff1f1;
        color: #a11a1a;
    }

    .credential-preview-footer {
        padding: 8px 16px;
        border-top: 1px solid #e5e9f0;
        background: #f8fafc;
        color: #667085;
        font-size: 9px;
        text-align: center;
    }

    @media (max-width: 660px) {
        .credential-preview-card {
            max-width: 400px;
        }

        .credential-preview-body {
            grid-template-columns: 1fr;
            justify-items: center;
            text-align: center;
        }

        .credential-preview-information {
            width: 100%;
        }

        .credential-preview-header {
            flex-direction: column;
            text-align: center;
        }

        .credential-preview-type {
            text-align: center;
        }
    }
</style>

<div class="credential-preview-container">
    <p class="credential-preview-title">
        Vista previa de cómo aparecerá la credencial del alumno
    </p>

    <article class="credential-preview-card">
        <header class="credential-preview-header">
            <div class="credential-preview-institution">
                <div class="credential-preview-logo">
                    ITSS
                </div>

                <div>
                    <h3>
                        Instituto Tecnológico Superior
                        de la Región Sierra
                    </h3>

                    <p>Identificación institucional</p>
                </div>
            </div>

            <div class="credential-preview-type">
                CREDENCIAL DIGITAL
            </div>
        </header>

        <div class="credential-preview-body">
            <div>
                @if ($photoUrl)
                    <img
                        src="{{ $photoUrl }}"
                        alt="Fotografía del alumno"
                        class="credential-preview-photo"
                        onerror="
                            this.style.display = 'none';
                            this.nextElementSibling.style.display = 'flex';
                        "
                    >

                    <div
                        class="credential-preview-initials"
                        style="display: none;"
                    >
                        {{ $initials }}
                    </div>
                @else
                    <div
                        class="credential-preview-initials"
                        style="display: flex;"
                    >
                        {{ $initials }}
                    </div>
                @endif
            </div>

            <div>
                <h2 class="credential-preview-name">
                    {{ $fullName }}
                </h2>

                <p class="credential-preview-number">
                    Matrícula:
                    <strong>{{ $studentNumber }}</strong>
                </p>

                <div class="credential-preview-information">
                    <div class="credential-preview-field credential-preview-field-full">
                        <span class="credential-preview-label">
                            Carrera
                        </span>

                        <span class="credential-preview-value">
                            {{ $careerName }}
                        </span>
                    </div>

                    <div class="credential-preview-field">
                        <span class="credential-preview-label">
                            Semestre
                        </span>

                        <span class="credential-preview-value">
                            {{ $semester }}
                        </span>
                    </div>

                    <div class="credential-preview-field">
                        <span class="credential-preview-label">
                            No. de credencial
                        </span>

                        <span class="credential-preview-value">
                            {{ $credentialNumber }}
                        </span>
                    </div>

                    <div class="credential-preview-field">
                        <span class="credential-preview-label">
                            Fecha de emisión
                        </span>

                        <span class="credential-preview-value">
                            {{ $issuedAt }}
                        </span>
                    </div>

                    <div class="credential-preview-field">
                        <span class="credential-preview-label">
                            Vigencia
                        </span>

                        <span class="credential-preview-value">
                            {{ $expiresAt }}
                        </span>
                    </div>
                </div>
            </div>

            <div class="credential-preview-qr">
                @if ($credential?->qr_token)
                    <img
                        src="{{ route(
                            'credential.qr',
                            [
                                'credential' =>
                                    $credential->qr_token,
                            ]
                        ) }}"
                        alt="Código QR de la credencial"
                    >
                @else
                    <div class="credential-preview-qr-placeholder">
                        El código QR aparecerá cuando se asigne la credencial
                    </div>
                @endif

                <span class="credential-preview-status {{ $credentialClass }}">
                    {{ $credentialLabel }}
                </span>
            </div>
        </div>

        <footer class="credential-preview-footer">
            Vista previa para uso interno del personal autorizado
        </footer>
    </article>
</div>