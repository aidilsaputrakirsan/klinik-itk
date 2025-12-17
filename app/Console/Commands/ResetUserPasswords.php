<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

class ResetUserPasswords extends Command
{
    protected $signature = 'app:reset-passwords';
    protected $description = 'Reset all user passwords to "password"';

    public function handle()
    {
        $users = User::all();

        foreach ($users as $user) {
            $user->password = Hash::make('password');
            $user->save();
            $this->info("Reset password for: {$user->email}");
        }

        $this->info('All passwords reset to "password"');
        return 0;
    }
}
