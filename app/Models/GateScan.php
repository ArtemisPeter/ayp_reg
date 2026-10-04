<?php

namespace App\Models;

use App\Enums\GateDirection;
use Database\Factories\GateScanFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['client_scan_id', 'attendee_id', 'manual_name', 'direction', 'scanned_by', 'scanned_at', 'is_mismatch', 'message'])]
class GateScan extends Model
{
    /** @use HasFactory<GateScanFactory> */
    use HasFactory;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'direction' => GateDirection::class,
            'scanned_at' => 'datetime',
            'is_mismatch' => 'boolean',
        ];
    }

    public function attendee(): BelongsTo
    {
        return $this->belongsTo(Attendee::class);
    }

    public function scannedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'scanned_by');
    }
}
