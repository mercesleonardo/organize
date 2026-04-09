<?php

use App\Enums\UserRole;
use App\Models\{Transaction, User};

test('admin tem passe livre via gate before', function () {
    $admin = User::factory()->create(['role' => UserRole::ADMIN]);
    $owner = User::factory()->create();

    $transaction = Transaction::factory()->create(['user_id' => $owner->id]);

    expect($admin->can('delete', $transaction))->toBeTrue();
});

test('support não tem passe livre fora do módulo de atendimento', function () {
    $support = User::factory()->create(['role' => UserRole::SUPPORT]);
    $owner   = User::factory()->create();

    $transaction = Transaction::factory()->create(['user_id' => $owner->id]);

    expect($support->can('delete', $transaction))->toBeFalse();
});
