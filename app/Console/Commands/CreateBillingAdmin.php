<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

class CreateBillingAdmin extends Command
{
    protected $signature = 'billing:create-admin';
    protected $description = 'Create or update the login account for Starlink Billing';

    public function handle(): int
    {
        $name = trim((string) $this->ask('Administrator name'));
        $email = strtolower(trim((string) $this->ask('Administrator email')));
        $password = (string) $this->secret('Password (minimum 12 characters)');

        if ($name === '' || ! filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($password) < 12) {
            $this->error('A name, valid email, and password of at least 12 characters are required.');
            return self::FAILURE;
        }

        $user = User::updateOrCreate(
            ['email' => $email],
            ['name' => $name, 'password' => Hash::make($password)]
        );

        $this->info('Administrator account is ready for '.$user->email.'.');
        return self::SUCCESS;
    }
}
