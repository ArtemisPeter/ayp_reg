<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable(['name', 'colour'])]
class Circuit extends Model
{
    use HasFactory, SoftDeletes;

    public function localChurches(): HasMany
    {
        return $this->hasMany(LocalChurch::class);
    }

    public function attendees(): HasManyThrough
    {
        return $this->hasManyThrough(Attendee::class, LocalChurch::class);
    }
}
