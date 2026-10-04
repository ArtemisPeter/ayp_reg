<?php

namespace App\Http\Controllers;

use App\Enums\RegistrationType;
use App\Enums\Role;
use App\Models\YouthCamp;
use App\Registration\RosterImport;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class RegistrationUploadController extends Controller
{
    public function store(Request $request, RosterImport $rosterImport): RedirectResponse
    {
        $request->validate(['file' => ['required', 'file', 'mimes:csv,txt', 'max:2048']]);

        $camp = YouthCamp::where('is_current', true)->first();

        if ($camp === null) {
            return redirect('/attendees')->withErrors(['file' => 'No Youth Camp is open for registration yet. Ask the Admin to set the current camp.']);
        }

        $isCircuitPresident = $request->user()->role === Role::CircuitPresident;

        abort_if($isCircuitPresident && $request->user()->circuit === null, 403);

        $errors = $rosterImport->import(
            $request->file('file'),
            $camp,
            $isCircuitPresident ? $request->user()->circuit : null,
            $isCircuitPresident ? RegistrationType::PreRegistration : RegistrationType::OnSite,
        );

        return redirect('/attendees')->with('roster_errors', $errors);
    }
}
