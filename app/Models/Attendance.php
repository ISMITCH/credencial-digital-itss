<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Auth;

class Attendance extends Model
{
    protected $fillable = [
        'event_id',
        'student_id',
        'credential_id',
        'registered_by',
        'registered_at',
        'registration_method',
        'status',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'registered_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Attendance $attendance): void {
            if (blank($attendance->registered_at)) {
                $attendance->registered_at = now();
            }

            if (blank($attendance->registered_by) && Auth::check()) {
                $attendance->registered_by = Auth::id();
            }
        });
    }

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function credential(): BelongsTo
    {
        return $this->belongsTo(Credential::class);
    }

    public function registeredBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'registered_by');
    }
}