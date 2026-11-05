<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Event extends Model
{
    protected $fillable = [
        'name',
        'description',
        'location',
        'event_date',
        'start_time',
        'end_time',
        'status',
        'all_careers',
        'all_semesters',
    ];

    protected function casts(): array
    {
        return [
            'event_date' => 'date',
            'all_careers' => 'boolean',
            'all_semesters' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        /*
         * Cada vez que se consulta un evento, se comprueba
         * si ya pasó su fecha y hora de finalización.
         */
        static::retrieved(function (Event $event): void {
            $event->completeIfEnded();
        });

        /*
         * Evita guardar como programado un evento
         * cuya fecha ya terminó.
         */
        static::saving(function (Event $event): void {
            if (
                $event->status === 'scheduled'
                && $event->hasEnded()
            ) {
                $event->status = 'completed';
            }
        });
    }

    public function hasEnded(): bool
    {
        if (! $this->event_date) {
            return false;
        }

        $timezone = config(
            'app.timezone',
            'America/Mexico_City'
        );

        $eventDate = $this->event_date->format('Y-m-d');

        /*
         * Si no se indicó una hora de finalización,
         * se considera terminado al finalizar ese día.
         */
        $endTime = $this->end_time ?: '23:59:59';

        $eventEnd = Carbon::parse(
            $eventDate . ' ' . $endTime,
            $timezone
        );

        return now($timezone)->greaterThan($eventEnd);
    }

    public function completeIfEnded(): void
    {
        if (
            $this->status !== 'scheduled'
            || ! $this->hasEnded()
        ) {
            return;
        }

        $this->status = 'completed';
        $this->saveQuietly();
    }

    public function careers(): BelongsToMany
    {
        return $this->belongsToMany(Career::class)
            ->withTimestamps();
    }

    public function eventSemesters(): HasMany
    {
        return $this->hasMany(EventSemester::class);
    }

    public function attendances(): HasMany
    {
        return $this->hasMany(Attendance::class);
    }
}