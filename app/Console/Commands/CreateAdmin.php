<?php

namespace App\Console\Commands;

use App\Enums\Role;
use App\Models\User;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

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
        $password = password('Password (at least 8 characters)', required: true, validate: fn (string $value) => strlen($value) < 8 ? 'Use at least 8 characters.' : null);

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
