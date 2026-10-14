<?php

namespace App\Console\Commands;

use App\Enums\Role;
use App\Models\User;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;

use function Laravel\Prompts\password;
use function Laravel\Prompts\text;

#[Signature('app:create-admin')]
#[Description('Create the first Admin user, asking for the username and password')]
class CreateAdmin extends Command
{
    public function handle(): int
    {
        $name = text('Full name', required: true);
        $username = text('Username', required: true, validate: fn (string $value) => User::where('username', $value)->exists() ? 'That username is taken.' : null);
        $password = password(
            'Password (at least 8 characters, with letters and numbers)',
            required: true,
            validate: fn (string $value) => Validator::make(['password' => $value], ['password' => Password::defaults()])->errors()->first('password') ?: null,
        );

        User::create([
            'name' => $name,
            'username' => $username,
            'password' => $password,
            'role' => Role::Admin,
        ]);

        $this->info("Admin '{$username}' created.");

        return self::SUCCESS;
    }
}
