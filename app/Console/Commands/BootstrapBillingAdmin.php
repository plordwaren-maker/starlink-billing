<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class BootstrapBillingAdmin extends Command
{
    protected $signature = 'billing:bootstrap-admin';
    protected $description = 'Create the initial billing administrator once from environment configuration';

    public function handle(): int
    {
        $email = strtolower(trim((string) config('admin.email')));
        $name = trim((string) config('admin.name', 'Administrator'));

        if ($email === '' || ! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->error('Set a valid ADMIN_EMAIL environment variable before bootstrapping the administrator.');
            return self::FAILURE;
        }

        // Idempotent: never reset an existing administrator password on deploy/restart.
        $existing = User::where('email', $email)->first();
        if ($existing) {
            $this->info('Administrator account already exists for '.$email.'. No credentials were changed.');
            return self::SUCCESS;
        }

        $configuredPassword = config('admin.password');
        $generated = ! is_string($configuredPassword) || $configuredPassword === '';
        $password = $generated ? Str::random(48) : (string) $configuredPassword;

        if (strlen($password) < 12) {
            $this->error('ADMIN_PASSWORD must be at least 12 characters, or leave it unset to generate a secure password.');
            return self::FAILURE;
        }

        $user = User::create([
            'name' => $name !== '' ? $name : 'Administrator',
            'email' => $email,
            'password' => Hash::make($password),
        ]);

        $this->info('Initial administrator account created for '.$user->email.'.');
        if ($generated) {
            $this->newLine();
            $this->warn('ONE-TIME ADMIN PASSWORD (copy it now; it will not be shown again):');
            $this->line($password); // Artisan stdout is visible in Render service logs.
            $this->newLine();
        } else {
            $this->info('Password was read from ADMIN_PASSWORD and was not printed.');
        }

        return self::SUCCESS;
    }
}
