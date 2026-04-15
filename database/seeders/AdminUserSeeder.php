<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $email    = config('admin.user.email');
        $name     = config('admin.user.name', 'Admin');
        $password = config('admin.user.password');

        if (!is_string($email) || $email === '' || !is_string($password) || $password === '') {
            return;
        }

        $user = User::query()->where('email', $email)->first();

        if ($user === null) {
            User::query()->create([
                'name'     => is_string($name) && $name !== '' ? $name : 'Admin',
                'email'    => $email,
                'password' => Hash::make($password),
                'role'     => UserRole::ADMIN->value,
            ]);

            return;
        }

        if ($user->role !== UserRole::ADMIN) {
            $user->forceFill(['role' => UserRole::ADMIN->value])->save();
        }

        if (is_string($name) && $name !== '' && $user->name !== $name) {
            $user->forceFill(['name' => $name])->save();
        }
    }
}
