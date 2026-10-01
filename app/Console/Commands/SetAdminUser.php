<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class SetAdminUser extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:set-admin-user';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Set rentify.id@gmail.com as admin user';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $email = 'rentify.id@gmail.com';
        $password = 'Rentify21';

        $user = \App\Models\User::where('email', $email)->first();

        if ($user) {
            $user->role = 'admin';
            $user->password = \Illuminate\Support\Facades\Hash::make($password);
            $user->email_verified_at = $user->email_verified_at ?? now();
            $user->save();
            $this->info("User with email {$email} updated successfully as admin!");
        } else {
            $user = \App\Models\User::create([
                'name' => 'Admin Rentify',
                'email' => $email,
                'password' => \Illuminate\Support\Facades\Hash::make($password),
                'role' => 'admin',
                'email_verified_at' => now(),
            ]);
            $this->info("Admin user created successfully with email {$email}!");
        }

        $this->table(
            ['ID', 'Name', 'Email', 'Role', 'Email Verified At'],
            [[$user->id, $user->name, $user->email, $user->role, $user->email_verified_at]]
        );

        return 0;
    }
}
