<?php

namespace App\Http\Controllers;

use App\Analytics\RegistrationOverview;
use App\Enums\Role;

class DashboardController extends Controller
{
    public function __invoke(RegistrationOverview $overview)
    {
        $user = auth()->user();
        $data = [];

        if (in_array($user->role, [Role::Admin, Role::Secretary], true)) {
            $data['overview'] = $overview->summary();
            $data['perCircuit'] = $overview->perCircuit();
        } elseif ($user->role === Role::CircuitPresident) {
            $data['overview'] = $overview->summary($user->circuit);
        }

        return view('dashboard', $data);
    }
}
