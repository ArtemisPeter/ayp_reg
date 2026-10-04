<?php

namespace App\Gate;

use App\Enums\GateDirection;
use App\Models\Attendee;
use App\Models\GateScan;
use App\Models\User;
use Carbon\CarbonImmutable;

class GateScanRecorder
{
    private const DUPLICATE_WINDOW_SECONDS = 5;

    /**
     * @return array{status: string, mismatch: bool, message: ?string, attendee: ?array<string, string>}
     */
    public function record(
        User $security,
        string $clientScanId,
        GateDirection $direction,
        CarbonImmutable $scannedAt,
        ?string $qrCode,
        ?string $manualName,
    ): array {
        if ($existing = GateScan::where('client_scan_id', $clientScanId)->first()) {
            return $this->toResponse($existing);
        }

        if ($qrCode !== null) {
            $attendee = Attendee::where('qr_code', $qrCode)->first();

            if ($attendee === null) {
                return ['status' => 'invalid', 'mismatch' => false, 'message' => 'Card not recognized.', 'attendee' => null];
            }

            return $this->recordForAttendee($security, $clientScanId, $direction, $scannedAt, $attendee);
        }

        $scan = GateScan::create([
            'client_scan_id' => $clientScanId,
            'attendee_id' => null,
            'manual_name' => $manualName,
            'direction' => $direction,
            'scanned_by' => $security->id,
            'scanned_at' => $scannedAt,
            'is_mismatch' => true,
            'message' => 'Entered by name; not on the downloaded roster. Needs matching.',
        ]);

        return $this->toResponse($scan);
    }

    private function recordForAttendee(
        User $security,
        string $clientScanId,
        GateDirection $direction,
        CarbonImmutable $scannedAt,
        Attendee $attendee,
    ): array {
        $last = GateScan::where('attendee_id', $attendee->id)->orderByDesc('scanned_at')->first();

        if ($last && $last->direction === $direction && $scannedAt->diffInSeconds($last->scanned_at, true) <= self::DUPLICATE_WINDOW_SECONDS) {
            return $this->toResponse($last, attendee: $attendee);
        }

        [$isMismatch, $message] = $this->checkAgainst($last, $direction);

        $scan = GateScan::create([
            'client_scan_id' => $clientScanId,
            'attendee_id' => $attendee->id,
            'direction' => $direction,
            'scanned_by' => $security->id,
            'scanned_at' => $scannedAt,
            'is_mismatch' => $isMismatch,
            'message' => $message,
        ]);

        return $this->toResponse($scan, attendee: $attendee);
    }

    /**
     * @return array{0: bool, 1: ?string}
     */
    private function checkAgainst(?GateScan $last, GateDirection $direction): array
    {
        if ($last === null) {
            return $direction === GateDirection::Out
                ? [true, 'Not recorded as IN yet.']
                : [false, null];
        }

        if ($last->direction === $direction) {
            $word = strtoupper($direction->value);

            return [true, "Already {$word} since {$last->scanned_at->format('g:i A')}."];
        }

        return [false, null];
    }

    /**
     * @return array{status: string, mismatch: bool, message: ?string, attendee: ?array<string, string>}
     */
    private function toResponse(GateScan $scan, ?Attendee $attendee = null): array
    {
        $attendee ??= $scan->attendee;

        return [
            'status' => $scan->direction->value,
            'mismatch' => $scan->is_mismatch,
            'message' => $scan->message,
            'attendee' => $attendee === null ? null : [
                'first_name' => $attendee->first_name,
                'local_church' => $attendee->localChurch->name,
                'circuit' => $attendee->localChurch->circuit->name,
            ],
        ];
    }
}
