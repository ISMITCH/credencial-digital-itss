<x-filament-panels::page>
    <script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>

    <script>
        window.credentialScanner = (wire) => ({
            wire: wire,
            scanner: null,
            scanning: false,
            processing: false,
            message: '',

            async startScanner() {
                const selectedEventId = document
                    .getElementById('eventId')
                    ?.value;

                if (!selectedEventId) {
                    this.message =
                        'Primero selecciona el evento.';

                    return;
                }

                if (typeof Html5Qrcode === 'undefined') {
                    this.message =
                        'No se pudo cargar el lector QR. Revisa tu conexión a internet.';

                    return;
                }

                this.scanning = true;
                this.message =
                    'Permite el acceso a la cámara y apunta al código QR.';

                await new Promise((resolve) => {
                    requestAnimationFrame(resolve);
                });

                this.scanner = new Html5Qrcode(
                    'credential-qr-reader'
                );

                try {
                    await this.scanner.start(
                        {
                            facingMode: 'environment',
                        },
                        {
                            fps: 10,
                            qrbox: (
                                viewfinderWidth,
                                viewfinderHeight
                            ) => {
                                const edge = Math.floor(
                                    Math.min(
                                        viewfinderWidth,
                                        viewfinderHeight
                                    ) * 0.7
                                );

                                return {
                                    width: edge,
                                    height: edge,
                                };
                            },
                        },
                        async (decodedText) => {
                            if (this.processing) {
                                return;
                            }

                            this.processing = true;

                            const qrValue =
                                decodedText.trim();

                            const eventId = document
                                .getElementById('eventId')
                                ?.value;

                            await this.stopScanner(true);

                            this.message =
                                'Código QR leído. Verificando información...';

                            try {
                                await this.wire.$call(
                                    'verifyScannedCredential',
                                    qrValue,
                                    eventId
                                );

                                this.message =
                                    'Verificación terminada.';
                            } catch (error) {
                                console.error(error);

                                this.message =
                                    'No se pudo completar la verificación.';
                            }

                            this.processing = false;
                        },
                        () => {
                            // Continúa buscando un código QR.
                        }
                    );
                } catch (error) {
                    console.error(error);

                    await this.stopScanner(true);

                    const errorText =
                        String(error).toLowerCase();

                    if (
                        errorText.includes('permission') ||
                        errorText.includes('notallowed')
                    ) {
                        this.message =
                            'No se otorgó permiso para utilizar la cámara.';
                    } else if (
                        errorText.includes('notfound') ||
                        errorText.includes('device')
                    ) {
                        this.message =
                            'No se encontró una cámara disponible.';
                    } else {
                        this.message =
                            'No se pudo abrir la cámara. Revisa los permisos del navegador.';
                    }
                }
            },

            async stopScanner(keepMessage = false) {
                if (this.scanner) {
                    try {
                        if (this.scanner.isScanning) {
                            await this.scanner.stop();
                        }

                        this.scanner.clear();
                    } catch (error) {
                        console.error(error);
                    }

                    this.scanner = null;
                }

                this.scanning = false;

                if (!keepMessage) {
                    this.message = '';
                }
            },

            closeCamera() {
                if (
                    this.scanner &&
                    this.scanner.isScanning
                ) {
                    this.scanner.stop().catch(() => {});
                }
            },
        });
    </script>

    <div
        x-data="credentialScanner($wire)"
        x-on:beforeunload.window="closeCamera()"
        class="mx-auto w-full max-w-3xl space-y-6"
    >
        <x-filament::section>
            <x-slot name="heading">
                Verificar acceso al evento
            </x-slot>

            <x-slot name="description">
                Selecciona el evento e ingresa la matrícula,
                el número de credencial o escanea el código QR.
            </x-slot>

            <div class="space-y-5">
                <div>
                    <label
                        for="eventId"
                        class="mb-2 block text-sm font-medium text-gray-950 dark:text-white"
                    >
                        Evento
                    </label>

                    <x-filament::input.wrapper>
                        <x-filament::input.select
                            id="eventId"
                            wire:model="eventId"
                        >
                            <option value="">
                                Selecciona un evento
                            </option>

                            @foreach ($this->getEventOptions() as $id => $name)
                                <option value="{{ $id }}">
                                    {{ $name }}
                                </option>
                            @endforeach
                        </x-filament::input.select>
                    </x-filament::input.wrapper>

                    @error('eventId')
                        <p class="mt-2 text-sm text-danger-600">
                            {{ $message }}
                        </p>
                    @enderror
                </div>

                <div>
                    <label
                        for="search"
                        class="mb-2 block text-sm font-medium text-gray-950 dark:text-white"
                    >
                        Matrícula, número de credencial o código QR
                    </label>

                    <x-filament::input.wrapper>
                        <x-filament::input
                            id="search"
                            type="text"
                            wire:model="search"
                            placeholder="Ejemplo: 22E30480 o CRED-TEST-001"
                            autocomplete="off"
                        />
                    </x-filament::input.wrapper>

                    @error('search')
                        <p class="mt-2 text-sm text-danger-600">
                            {{ $message }}
                        </p>
                    @enderror
                </div>

                <div class="flex flex-wrap gap-3">
                    <x-filament::button
                        type="button"
                        color="gray"
                        icon="heroicon-o-qr-code"
                        x-on:click="startScanner()"
                        x-bind:disabled="scanning || processing"
                    >
                        Escanear código QR
                    </x-filament::button>

                    <x-filament::button
                        type="button"
                        color="danger"
                        icon="heroicon-o-x-mark"
                        x-show="scanning"
                        x-cloak
                        x-on:click="stopScanner()"
                    >
                        Detener cámara
                    </x-filament::button>

                    <x-filament::button
                        type="button"
                        icon="heroicon-o-magnifying-glass"
                        wire:click="verifyCredential"
                        wire:loading.attr="disabled"
                        wire:target="verifyCredential"
                    >
                        Verificar credencial
                    </x-filament::button>
                </div>

                <div
                    x-show="scanning || message"
                    x-cloak
                    class="rounded-xl border border-gray-200 bg-gray-50 p-4 dark:border-white/10 dark:bg-white/5"
                >
                    <div
                        wire:ignore
                        x-show="scanning"
                    >
                        <div
                            id="credential-qr-reader"
                            class="mx-auto w-full max-w-md overflow-hidden rounded-lg"
                        ></div>
                    </div>

                    <p
                        x-text="message"
                        class="mt-3 text-sm text-gray-600 dark:text-gray-300"
                    ></p>
                </div>

                <p
                    wire:loading
                    wire:target="verifyCredential"
                    class="text-sm text-gray-600"
                >
                    Verificando información...
                </p>
            </div>
        </x-filament::section>

        @php
            $resultInformation = match ($resultStatus) {
                'valid' => [
                    'title' => 'Alumno autorizado',
                    'message' => 'La credencial es válida y el alumno puede asistir al evento.',
                    'color' => 'success',
                ],
                'registered' => [
                    'title' => 'Asistencia registrada',
                    'message' => 'La asistencia del alumno se guardó correctamente.',
                    'color' => 'success',
                ],
                'duplicate' => [
                    'title' => 'Asistencia ya registrada',
                    'message' => 'Este alumno ya asistió y tiene una asistencia registrada en el evento.',
                    'color' => 'warning',
                ],
                'inactive' => [
                    'title' => 'Credencial inactiva',
                    'message' => 'El alumno o su credencial se encuentra inactivo.',
                    'color' => 'danger',
                ],
                'expired' => [
                    'title' => 'Credencial vencida',
                    'message' => 'La fecha de vigencia de esta credencial ya terminó.',
                    'color' => 'warning',
                ],
                'career_not_allowed' => [
                    'title' => 'Carrera no permitida',
                    'message' => 'La carrera del alumno no está incluida en este evento.',
                    'color' => 'danger',
                ],
                'semester_not_allowed' => [
                    'title' => 'Semestre no permitido',
                    'message' => 'El semestre del alumno no está incluido en este evento.',
                    'color' => 'danger',
                ],
                'event_unavailable' => [
                    'title' => 'Evento no disponible',
                    'message' => 'El evento no existe o ya no se encuentra programado.',
                    'color' => 'danger',
                ],
                'not_found' => [
                    'title' => 'Credencial no encontrada',
                    'message' => 'No existe una credencial relacionada con el dato ingresado.',
                    'color' => 'danger',
                ],
                default => null,
            };
        @endphp

        @if ($resultInformation)
            <x-filament::section>
                <x-slot name="heading">
                    Resultado de la verificación
                </x-slot>

                <div class="space-y-3">
                    <x-filament::badge
                        :color="$resultInformation['color']"
                        size="lg"
                    >
                        {{ $resultInformation['title'] }}
                    </x-filament::badge>

                    <p class="text-sm text-gray-700 dark:text-gray-300">
                        {{ $resultInformation['message'] }}
                    </p>
                </div>

                @if ($credentialData)
                    <div class="mt-6 grid gap-4 sm:grid-cols-2">
                        <div>
                            <p class="text-sm text-gray-500">Evento</p>
                            <p class="font-semibold">
                                {{ $credentialData['event_name'] }}
                            </p>
                        </div>

                        <div>
                            <p class="text-sm text-gray-500">Alumno</p>
                            <p class="font-semibold">
                                {{ $credentialData['student_name'] }}
                            </p>
                        </div>

                        <div>
                            <p class="text-sm text-gray-500">Matrícula</p>
                            <p class="font-semibold">
                                {{ $credentialData['student_number'] }}
                            </p>
                        </div>

                        <div>
                            <p class="text-sm text-gray-500">
                                Número de credencial
                            </p>
                            <p class="font-semibold">
                                {{ $credentialData['credential_number'] }}
                            </p>
                        </div>

                        <div>
                            <p class="text-sm text-gray-500">Carrera</p>
                            <p class="font-semibold">
                                {{ $credentialData['career'] }}
                            </p>
                        </div>

                        <div>
                            <p class="text-sm text-gray-500">Semestre</p>
                            <p class="font-semibold">
                                {{ $credentialData['semester'] }}
                            </p>
                        </div>

                        <div>
                            <p class="text-sm text-gray-500">
                                Fecha de emisión
                            </p>
                            <p class="font-semibold">
                                {{ $credentialData['issued_at'] }}
                            </p>
                        </div>

                        <div>
                            <p class="text-sm text-gray-500">
                                Fecha de vencimiento
                            </p>
                            <p class="font-semibold">
                                {{ $credentialData['expires_at'] }}
                            </p>
                        </div>
                    </div>

                    @if ($canRegister)
                        <div class="mt-6">
                            <x-filament::button
                                type="button"
                                color="success"
                                icon="heroicon-o-check-circle"
                                wire:click="registerAttendance"
                                wire:loading.attr="disabled"
                                wire:target="registerAttendance"
                            >
                                Registrar asistencia
                            </x-filament::button>
                        </div>
                    @endif
                @endif
            </x-filament::section>
        @endif
    </div>
</x-filament-panels::page>