<?php

use App\Models\User;
use Laravel\Socialite\Contracts\Provider;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\{InvalidStateException, User as SocialiteUser};

test('redirect do google retorna um redirect', function () {
    $provider = \Mockery::mock(Provider::class);
    $provider->shouldReceive('scopes')->with(['openid', 'profile', 'email'])->andReturnSelf();
    $provider->shouldReceive('redirect')->andReturn(redirect('https://accounts.google.com/o/oauth2/auth'));

    Socialite::shouldReceive('driver')->with('google')->andReturn($provider);

    $this->get(route('auth.google.redirect'))
        ->assertRedirect('https://accounts.google.com/o/oauth2/auth');
});

test('callback cria utilizador e autentica', function () {
    $provider = \Mockery::mock(Provider::class);

    $socialUser        = new SocialiteUser();
    $socialUser->id    = 'google-123';
    $socialUser->name  = 'John Doe';
    $socialUser->email = 'john@example.com';

    $provider->shouldReceive('scopes')->with(['openid', 'profile', 'email'])->andReturnSelf();
    $provider->shouldReceive('user')->andReturn($socialUser);
    Socialite::shouldReceive('driver')->with('google')->andReturn($provider);

    $this->get(route('auth.google.callback'))
        ->assertRedirect(route('dashboard', absolute: false));

    $user = User::query()->where('email', 'john@example.com')->first();

    expect($user)->not->toBeNull()
        ->and($user->google_id)->toBe('google-123')
        ->and($user->email_verified_at)->not->toBeNull()
        ->and($user->categories()->count())->toBe(0);

    $this->assertAuthenticatedAs($user);
});

test('callback vincula google_id ao utilizador existente pelo email', function () {
    $existing = User::factory()->create([
        'email'     => 'jane@example.com',
        'google_id' => null,
    ]);

    $provider = \Mockery::mock(Provider::class);

    $socialUser        = new SocialiteUser();
    $socialUser->id    = 'google-999';
    $socialUser->name  = 'Jane';
    $socialUser->email = 'jane@example.com';

    $provider->shouldReceive('scopes')->with(['openid', 'profile', 'email'])->andReturnSelf();
    $provider->shouldReceive('user')->andReturn($socialUser);
    Socialite::shouldReceive('driver')->with('google')->andReturn($provider);

    $this->get(route('auth.google.callback'))
        ->assertRedirect(route('dashboard', absolute: false));

    expect($existing->refresh()->google_id)->toBe('google-999');
    $this->assertAuthenticatedAs($existing);
});

test('callback com estado oauth inválido redireciona para login com mensagem', function () {
    $provider = \Mockery::mock(Provider::class);
    $provider->shouldReceive('scopes')->with(['openid', 'profile', 'email'])->andReturnSelf();
    $provider->shouldReceive('user')->andThrow(new InvalidStateException());
    Socialite::shouldReceive('driver')->with('google')->andReturn($provider);

    $this->get(route('auth.google.callback'))
        ->assertRedirect(route('login', absolute: false))
        ->assertSessionHas('status');
});

test('redirect do google com oauth stateless chama stateless no driver', function () {
    config(['services.google.stateless' => true]);

    $provider = \Mockery::mock(Provider::class);
    $provider->shouldReceive('scopes')->with(['openid', 'profile', 'email'])->andReturnSelf();
    $provider->shouldReceive('stateless')->andReturnSelf();
    $provider->shouldReceive('redirect')->andReturn(redirect('https://accounts.google.com/o/oauth2/auth'));

    Socialite::shouldReceive('driver')->with('google')->andReturn($provider);

    $this->get(route('auth.google.redirect'))
        ->assertRedirect('https://accounts.google.com/o/oauth2/auth');
});
