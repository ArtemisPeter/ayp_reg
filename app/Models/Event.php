<?php

namespace App\Models;

use Database\Factories\EventFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable(['youth_camp_id', 'name'])]
class Event extends Model
{
    /** @use HasFactory<EventFactory> */
    use HasFactory, SoftDeletes;

    public function youthCamp(): BelongsTo
    {
        return $this->belongsTo(YouthCamp::class);
    }

    public function ranks(): HasMany
    {
        return $this->hasMany(EventRank::class);
    }

    public function scores(): HasMany
    {
        return $this->hasMany(EventScore::class);
    }
}
