<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EventSemester extends Model
{
    protected $fillable = [
        'event_id',
        'semester',
    ];

    protected function casts(): array
    {
        return [
            'semester' => 'integer',
        ];
    }

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }
}