<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Circuit;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CircuitController extends Controller
{
    public function index()
    {
        return view('admin.circuits.index', ['circuits' => Circuit::withCount('localChurches')->orderBy('name')->get()]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('circuits', 'name')->withoutTrashed()],
            'colour' => ['required', 'regex:/^#[0-9A-Fa-f]{6}$/'],
        ]);

        Circuit::create($data);

        return redirect('/admin/circuits');
    }

    public function destroy(Circuit $circuit): RedirectResponse
    {
        if ($circuit->localChurches()->exists()) {
            return redirect('/admin/circuits')->withErrors(['name' => 'This Circuit still has Local Churches. Remove them first.']);
        }

        if (User::where('circuit_id', $circuit->id)->exists()) {
            return redirect('/admin/circuits')->withErrors(['name' => 'This Circuit still has a user account assigned to it. Remove that account first.']);
        }

        $circuit->delete();

        return redirect('/admin/circuits');
    }

    public function update(Request $request, Circuit $circuit): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('circuits', 'name')->withoutTrashed()->ignore($circuit->id)],
            'colour' => ['required', 'regex:/^#[0-9A-Fa-f]{6}$/'],
        ]);

        $circuit->update($data);

        return redirect('/admin/circuits');
    }
}
