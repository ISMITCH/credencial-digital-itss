<?php

namespace App\Filament\Pages;

use App\Models\Attendance;
use App\Models\Credential;
use App\Models\Event;
use BackedEnum;
use Carbon\Carbon;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use UnitEnum;

class VerifyCredential extends Page
{
    protected static string|BackedEnum|null $navigationIcon =
        Heroicon::OutlinedIdentification;

    protected static ?string $navigationLabel =
        'Verificar credencial';

    protected static string|UnitEnum|null $navigationGroup =
        'Verificación de la credencial';

    protected static ?string $title =
        'Verificación de la credencial';

    protected static ?int $navigationSort = 1;

    protected string $view =
        'filament.pages.verify-credential';

    public ?int $eventId = null;

    public string $search = '';

    public ?array $credentialData = null;

    public string $resultStatus = '';

    public bool $canRegister = false;

    public string $verificationMethod = 'manual';

    public function getEventOptions(): array
    {
        return Event::query()
            ->where('status', 'scheduled')
            ->orderBy('event_date')
            ->orderBy('start_time')
            ->get()
            ->mapWithKeys(function (Event $event): array {
                $date = $event->event_date
                    ? Carbon::parse($event->event_date)
                        ->format('d/m/Y')
                    : 'Sin fecha';

                return [
                    $event->id =>
                        $event->name . ' - ' . $date,
                ];
            })
            ->all();
    }

    public function verifyScannedCredential(
        string $qrValue,
        mixed $selectedEventId = null
    ): void {
        if (filled($selectedEventId)) {
            $this->eventId = (int) $selectedEventId;
        }

        $this->search = trim($qrValue);

        $this->verifyCredential();
    }

    public function verifyCredential(): void
    {
        $this->validate([
            'eventId' => [
                'required',
                'exists:events,id',
            ],
            'search' => [
                'required',
                'string',
            ],
        ], [
            'eventId.required' =>
                'Selecciona el evento.',
            'eventId.exists' =>
                'El evento seleccionado no existe.',
            'search.required' =>
                'Ingresa la matrícula, el número de credencial o lee el código QR.',
        ]);

        $this->credentialData = null;
        $this->resultStatus = '';
        $this->canRegister = false;
        $this->verificationMethod = 'manual';

        $event = Event::query()
            ->with([
                'careers',
                'eventSemesters',
            ])
            ->find($this->eventId);

        if (
            ! $event ||
            $event->status !== 'scheduled'
        ) {
            $this->resultStatus =
                'event_unavailable';

            return;
        }

        $search = trim($this->search);

        $credential = Credential::query()
            ->with(['student.career'])
            ->where(function ($query) use ($search) {
                $query
                    ->where(
                        'credential_number',
                        $search
                    )
                    ->orWhere(
                        'qr_token',
                        $search
                    )
                    ->orWhereHas(
                        'student',
                        function ($studentQuery) use ($search) {
                            $studentQuery->where(
                                'student_number',
                                $search
                            );
                        }
                    );
            })
            ->first();

        if (
            ! $credential ||
            ! $credential->student
        ) {
            $this->resultStatus = 'not_found';

            return;
        }

        if (
            filled($credential->qr_token) &&
            hash_equals(
                (string) $credential->qr_token,
                $search
            )
        ) {
            $this->verificationMethod = 'qr';
        }

        $student = $credential->student;

        $this->credentialData = [
            'event_id' =>
                $event->id,

            'event_name' =>
                $event->name,

            'credential_id' =>
                $credential->id,

            'credential_number' =>
                $credential->credential_number,

            'student_id' =>
                $student->id,

            'student_number' =>
                $student->student_number,

            'student_name' => trim(
                $student->first_name . ' ' .
                $student->paternal_last_name . ' ' .
                ($student->maternal_last_name ?? '')
            ),

            'career' =>
                $student->career?->name ??
                'Sin carrera',

            'semester' =>
                $student->semester ??
                'Sin semestre',

            'issued_at' =>
                $credential->issued_at
                    ? Carbon::parse(
                        $credential->issued_at
                    )->format('d/m/Y')
                    : 'Sin fecha',

            'expires_at' =>
                $credential->expires_at
                    ? Carbon::parse(
                        $credential->expires_at
                    )->format('d/m/Y')
                    : 'Sin vencimiento',
        ];

        $credentialIsActive =
            $credential->status === 'active';

        $studentIsActive =
            $student->status === 'active';

        $isExpired = $credential->expires_at
            ? Carbon::parse(
                $credential->expires_at
            )
                ->startOfDay()
                ->lt(today())
            : false;

        if (
            ! $credentialIsActive ||
            ! $studentIsActive
        ) {
            $this->resultStatus = 'inactive';

            return;
        }

        if ($isExpired) {
            $this->resultStatus = 'expired';

            return;
        }

        $careerIsAllowed =
            $event->all_careers ||
            $event->careers->contains(
                'id',
                $student->career_id
            );

        if (! $careerIsAllowed) {
            $this->resultStatus =
                'career_not_allowed';

            return;
        }

        $semesterIsAllowed =
            $event->all_semesters ||
            $event->eventSemesters->contains(
                'semester',
                (int) $student->semester
            );

        if (! $semesterIsAllowed) {
            $this->resultStatus =
                'semester_not_allowed';

            return;
        }

        $attendanceAlreadyExists =
            Attendance::query()
                ->where(
                    'event_id',
                    $event->id
                )
                ->where(
                    'student_id',
                    $student->id
                )
                ->exists();

        if ($attendanceAlreadyExists) {
            $this->resultStatus = 'duplicate';

            return;
        }

        $this->resultStatus = 'valid';
        $this->canRegister = true;
    }

    public function registerAttendance(): void
    {
        $this->verifyCredential();

        if (
            ! $this->canRegister ||
            ! $this->credentialData
        ) {
            return;
        }

        Attendance::create([
            'event_id' =>
                $this->credentialData['event_id'],

            'student_id' =>
                $this->credentialData['student_id'],

            'credential_id' =>
                $this->credentialData['credential_id'],

            'registration_method' =>
                $this->verificationMethod,

            'status' => 'present',
        ]);

        $this->resultStatus = 'registered';
        $this->canRegister = false;
    }
}