<?php

namespace App\Models;

use Database\Factories\YouthCampFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable(['year', 'theme', 'venue', 'verse_reference', 'tagline', 'pre_registration_fee', 'on_site_fee', 'starts_on', 'ends_on'])]
class YouthCamp extends Model
{
    /** @use HasFactory<YouthCampFactory> */
    use HasFactory, SoftDeletes;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'starts_on' => 'date',
            'ends_on' => 'date',
            'is_current' => 'boolean',
            'pre_registration_fee' => 'decimal:2',
            'on_site_fee' => 'decimal:2',
        ];
    }
}
