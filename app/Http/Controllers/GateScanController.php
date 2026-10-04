<?php

namespace App\Http\Controllers;

use App\Enums\GateDirection;
use App\Gate\GateScanRecorder;
use App\Models\Attendee;
use App\Models\GateScan;
use Carbon\CarbonImmutable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class GateScanController extends Controller
{
    public function roster(): JsonResponse
    {
        $roster = Attendee::with('localChurch.circuit')
            ->whereHas('youthCamp', fn ($camp) => $camp->where('is_current', true))
            ->get()
            ->map(fn (Attendee $attendee) => [
                'qr_code' => $attendee->qr_code,
                'first_name' => $attendee->first_name,
                'local_church' => $attendee->localChurch->name,
                'circuit' => $attendee->localChurch->circuit->name,
            ])
            ->values();

        return response()->json($roster);
    }

    public function review()
    {
        return view('gate.review', [
            'scans' => GateScan::with(['attendee', 'scannedBy'])
                ->where(fn ($query) => $query->where('is_mismatch', true)->orWhereNull('attendee_id'))
                ->orderByDesc('scanned_at')
                ->get(),
        ]);
    }

    public function store(Request $request, GateScanRecorder $recorder): JsonResponse
    {
        $data = $request->validate([
            'client_scan_id' => ['required', 'string', 'max:64'],
            'direction' => ['required', Rule::enum(GateDirection::class)],
            'scanned_at' => ['required', 'date'],
            'qr_code' => ['nullable', 'string', 'required_without:manual_name'],
            'manual_name' => ['nullable', 'string', 'max:255', 'required_without:qr_code'],
        ]);

        $result = $recorder->record(
            $request->user(),
            $data['client_scan_id'],
            GateDirection::from($data['direction']),
            CarbonImmutable::parse($data['scanned_at'])->setTimezone(config('app.timezone')),
            $data['qr_code'] ?? null,
            $data['manual_name'] ?? null,
        );

        return response()->json($result);
    }
}
