<?php

namespace App\Models;

use Database\Factories\YouthCampFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable(['year', 'theme', 'venue', 'verse_reference', 'tagline', 'pre_registration_fee', 'on_site_fee', 'pre_registration_deadline', 'starts_on', 'ends_on'])]
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
            'pre_registration_deadline' => 'date',
        ];
    }

    /**
     * Pre-registration stays open through the whole deadline day, Philippine time; with no deadline it never closes.
     */
    public function isPreRegistrationOpen(): bool
    {
        return $this->pre_registration_deadline === null
            || now()->lte($this->pre_registration_deadline->copy()->endOfDay());
    }
}
