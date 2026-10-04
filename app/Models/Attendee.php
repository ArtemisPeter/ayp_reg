<?php

namespace App\Models;

use App\Enums\AttendeeType;
use App\Enums\RegistrationType;
use Database\Factories\AttendeeFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

#[Fillable([
    'youth_camp_id', 'local_church_id', 'first_name', 'middle_name', 'last_name',
    'birthday', 'is_baptized', 'type', 'registration_type',
])]
class Attendee extends Model
{
    /** @use HasFactory<AttendeeFactory> */
    use HasFactory, SoftDeletes;

    protected static function booted(): void
    {
        static::creating(function (Attendee $attendee): void {
            $attendee->qr_code ??= (string) Str::ulid();
        });
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'birthday' => 'date',
            'is_baptized' => 'boolean',
            'needs_reprint' => 'boolean',
            'type' => AttendeeType::class,
            'registration_type' => RegistrationType::class,
        ];
    }

    public function localChurch(): BelongsTo
    {
        return $this->belongsTo(LocalChurch::class);
    }

    public function youthCamp(): BelongsTo
    {
        return $this->belongsTo(YouthCamp::class);
    }
}
