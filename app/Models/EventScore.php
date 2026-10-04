<?php

namespace App\Models;

use Database\Factories\EventScoreFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['event_id', 'circuit_id', 'event_rank_id'])]
class EventScore extends Model
{
    /** @use HasFactory<EventScoreFactory> */
    use HasFactory;

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function circuit(): BelongsTo
    {
        return $this->belongsTo(Circuit::class);
    }

    public function rank(): BelongsTo
    {
        return $this->belongsTo(EventRank::class, 'event_rank_id');
    }
}
