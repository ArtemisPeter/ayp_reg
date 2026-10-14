<?php

namespace App\Http\Controllers\Admin;

use App\Enums\Role;
use App\Http\Controllers\Controller;
use App\Models\Circuit;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class UserController extends Controller
{
    public function index()
    {
        return view('admin.users.index', [
            'users' => User::with('circuit')->orderBy('name')->get(),
            'circuits' => Circuit::orderBy('name')->get(),
            'roles' => Role::cases(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'max:255', 'unique:users,username'],
            'role' => ['required', Rule::enum(Role::class)],
            'circuit_id' => [
                Rule::requiredIf($request->input('role') === Role::CircuitPresident->value),
                'nullable',
                Rule::exists('circuits', 'id')->whereNull('deleted_at'),
            ],
            'password' => ['required', 'string', Password::defaults()],
        ]);

        User::create($data + ['must_change_password' => true]);

        return redirect('/admin/users');
    }

    public function destroy(Request $request, User $user): RedirectResponse
    {
        if ($user->is($request->user())) {
            return redirect('/admin/users')->withErrors(['username' => 'You cannot remove your own account.']);
        }

        $user->delete();

        return redirect('/admin/users');
    }

    public function resetPassword(Request $request, User $user): RedirectResponse
    {
        $data = $request->validate(['password' => ['required', 'string', Password::defaults()]]);

        $user->update(['password' => $data['password'], 'must_change_password' => true]);

        return redirect('/admin/users');
    }
}
