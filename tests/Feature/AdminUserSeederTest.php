<?php

use App\Enums\UserRole;
use App\Models\User;
use Database\Seeders\AdminUserSeeder;
use Illuminate\Support\Facades\Hash;

test('admin seeder não faz nada quando não configurado', function () {
    config()->set('admin.user.email', null);
    config()->set('admin.user.password', null);

    $this->seed(AdminUserSeeder::class);

    expect(User::query()->count())->toBe(0);
});

test('admin seeder cria um admin quando configurado', function () {
    config()->set('admin.user.name', 'Admin');
    config()->set('admin.user.email', 'admin@example.com');
    config()->set('admin.user.password', 'super-secret');

    $this->seed(AdminUserSeeder::class);

    $user = User::query()->where('email', 'admin@example.com')->first();

    expect($user)->not->toBeNull()
        ->and($user->role)->toBe(UserRole::ADMIN)
        ->and($user->name)->toBe('Admin')
        ->and(Hash::check('super-secret', (string) $user->password))->toBeTrue();
});

test('admin seeder promove usuário existente para admin', function () {
    $user = User::factory()->create([
        'email' => 'admin@example.com',
        'role'  => UserRole::USER,
    ]);

    config()->set('admin.user.name', 'Admin Renomeado');
    config()->set('admin.user.email', 'admin@example.com');
    config()->set('admin.user.password', 'irrelevante');

    $this->seed(AdminUserSeeder::class);

    expect($user->fresh()->role)->toBe(UserRole::ADMIN)
        ->and($user->fresh()->name)->toBe('Admin Renomeado');
});
