<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable(['name'])]
class LocalChurch extends Model
{
    use HasFactory, SoftDeletes;

    public function circuit(): BelongsTo
    {
        return $this->belongsTo(Circuit::class);
    }
}
