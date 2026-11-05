<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Credential extends Model
{
    protected $fillable = [
        'student_id',
        'credential_number',
        'qr_token',
        'issued_at',
        'expires_at',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'issued_at' => 'date',
            'expires_at' => 'date',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Credential $credential): void {
            if (blank($credential->qr_token)) {
                $credential->qr_token = (string) Str::uuid();
            }
        });
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function attendances(): HasMany
    {
        return $this->hasMany(Attendance::class);
    }
}