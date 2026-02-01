<?php

use App\Models\SocialAccount;
use App\Models\User;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\User as SocialiteUser;
use Mockery as m;

test('redirects to google oauth provider', function () {
    $provider = 'google';
    $redirectUrl = 'https://accounts.google.com/oauth/authorize';

    Socialite::shouldReceive('driver')
        ->with($provider)
        ->once()
        ->andReturnSelf();

    Socialite::shouldReceive('with')
        ->with(m::type('array'))
        ->once()
        ->andReturnSelf();

    Socialite::shouldReceive('redirect')
        ->once()
        ->andReturn(redirect($redirectUrl));

    $response = $this->get(route('social.redirect', ['provider' => $provider]));

    $response->assertRedirect();
});

test('creates new user from google oauth callback', function () {
    $provider = 'google';
    $socialiteUser = m::mock(SocialiteUser::class);
    $socialiteUser->shouldReceive('getId')->andReturn('123456789');
    $socialiteUser->shouldReceive('getEmail')->andReturn('test@example.com');
    $socialiteUser->shouldReceive('getName')->andReturn('Test User');
    $socialiteUser->token = 'oauth-token';
    $socialiteUser->refreshToken = 'refresh-token';

    Socialite::shouldReceive('driver')
        ->with($provider)
        ->once()
        ->andReturnSelf();

    Socialite::shouldReceive('user')
        ->once()
        ->andReturn($socialiteUser);

    $response = $this->get(route('social.callback', ['provider' => $provider]));

    $response->assertRedirect(route('dashboard', absolute: false));
    $this->assertAuthenticated();

    $user = User::where('email', 'test@example.com')->first();
    expect($user)->not->toBeNull();
    expect($user->name)->toBe('Test User');
    // Email should be verified when created via social auth
    expect($user->email_verified_at)->not->toBeNull();

    $socialAccount = SocialAccount::where('user_id', $user->id)
        ->where('provider', $provider)
        ->first();
    expect($socialAccount)->not->toBeNull();
    expect($socialAccount->provider_id)->toBe('123456789');
});

test('logs in existing user with social account', function () {
    $provider = 'google';
    $user = User::factory()->create([
        'email' => 'existing@example.com',
    ]);

    SocialAccount::create([
        'user_id' => $user->id,
        'provider' => $provider,
        'provider_id' => '123456789',
        'provider_token' => 'existing-token',
    ]);

    $socialiteUser = m::mock(SocialiteUser::class);
    $socialiteUser->shouldReceive('getId')->andReturn('123456789');
    $socialiteUser->shouldReceive('getEmail')->andReturn('existing@example.com');
    $socialiteUser->shouldReceive('getName')->andReturn('Existing User');
    $socialiteUser->token = 'new-oauth-token';
    $socialiteUser->refreshToken = 'new-refresh-token';

    Socialite::shouldReceive('driver')
        ->with($provider)
        ->once()
        ->andReturnSelf();

    Socialite::shouldReceive('user')
        ->once()
        ->andReturn($socialiteUser);

    $response = $this->get(route('social.callback', ['provider' => $provider]));

    $response->assertRedirect(route('dashboard', absolute: false));
    $this->assertAuthenticatedAs($user);
});

test('links social account to existing user by email', function () {
    $provider = 'google';
    $user = User::factory()->create([
        'email' => 'existing@example.com',
    ]);

    $socialiteUser = m::mock(SocialiteUser::class);
    $socialiteUser->shouldReceive('getId')->andReturn('123456789');
    $socialiteUser->shouldReceive('getEmail')->andReturn('existing@example.com');
    $socialiteUser->shouldReceive('getName')->andReturn('Existing User');
    $socialiteUser->token = 'oauth-token';
    $socialiteUser->refreshToken = 'refresh-token';

    Socialite::shouldReceive('driver')
        ->with($provider)
        ->once()
        ->andReturnSelf();

    Socialite::shouldReceive('user')
        ->once()
        ->andReturn($socialiteUser);

    $response = $this->get(route('social.callback', ['provider' => $provider]));

    $response->assertRedirect(route('dashboard', absolute: false));
    $this->assertAuthenticatedAs($user);

    $socialAccount = SocialAccount::where('user_id', $user->id)
        ->where('provider', $provider)
        ->first();
    expect($socialAccount)->not->toBeNull();
});

test('links social account when user is authenticated', function () {
    $provider = 'google';
    $user = User::factory()->create();

    $socialiteUser = m::mock(SocialiteUser::class);
    $socialiteUser->shouldReceive('getId')->andReturn('123456789');
    $socialiteUser->shouldReceive('getEmail')->andReturn('social@example.com');
    $socialiteUser->shouldReceive('getName')->andReturn('Social User');
    $socialiteUser->token = 'oauth-token';
    $socialiteUser->refreshToken = 'refresh-token';

    Socialite::shouldReceive('driver')
        ->with($provider)
        ->once()
        ->andReturnSelf();

    Socialite::shouldReceive('with')
        ->with(m::type('array'))
        ->once()
        ->andReturnSelf();

    Socialite::shouldReceive('redirect')
        ->once()
        ->andReturn(redirect('http://google.com'));

    // First, redirect to link
    $redirectResponse = $this->actingAs($user)
        ->get(route('social.link', ['provider' => $provider]));

    $redirectResponse->assertRedirect();

    // Then simulate callback with intent=link
    $state = base64_encode(json_encode(['intent' => 'link']));

    Socialite::shouldReceive('driver')
        ->with($provider)
        ->once()
        ->andReturnSelf();

    Socialite::shouldReceive('user')
        ->once()
        ->andReturn($socialiteUser);

    $callbackResponse = $this->actingAs($user)
        ->get(route('social.callback', ['provider' => $provider, 'state' => $state]));

    $callbackResponse->assertRedirect(route('profile.edit', absolute: false));
    $callbackResponse->assertSessionHas('status');

    $socialAccount = SocialAccount::where('user_id', $user->id)
        ->where('provider', $provider)
        ->first();
    expect($socialAccount)->not->toBeNull();
});

test('unlinks social account after password confirmation', function () {
    $provider = 'google';
    $user = User::factory()->create([
        'password' => bcrypt('password'),
    ]);

    SocialAccount::create([
        'user_id' => $user->id,
        'provider' => $provider,
        'provider_id' => '123456789',
        'provider_token' => 'token',
    ]);

    $response = $this->actingAs($user)
        ->withSession(['auth.password_confirmed_at' => time()])
        ->delete(route('social.unlink', ['provider' => $provider]));

    $response->assertRedirect(route('profile.edit', absolute: false));
    $response->assertSessionHas('status');

    expect(SocialAccount::where('user_id', $user->id)->where('provider', $provider)->first())->toBeNull();
});

test('prevents unlinking only authentication method', function () {
    $provider = 'google';
    $user = User::factory()->create([
        'password' => null,
    ]);

    $socialAccount = SocialAccount::create([
        'user_id' => $user->id,
        'provider' => $provider,
        'provider_id' => '123456789',
        'provider_token' => 'token',
    ]);

    $response = $this->actingAs($user)
        ->get(route('social.unlink.confirm', ['provider' => $provider]));

    $response->assertRedirect(route('profile.edit', absolute: false));
    $response->assertSessionHas('error');
    expect(SocialAccount::find($socialAccount->id))->not->toBeNull();
});

test('show unlink confirm redirects to profile when user has no password', function () {
    $provider = 'google';
    $user = User::factory()->create([
        'password' => null,
    ]);

    SocialAccount::create([
        'user_id' => $user->id,
        'provider' => $provider,
        'provider_id' => '123456789',
        'provider_token' => 'token',
    ]);

    $response = $this->actingAs($user)
        ->get(route('social.unlink.confirm', ['provider' => $provider]));

    $response->assertRedirect(route('profile.edit', absolute: false));
    $response->assertSessionHas('error');
});

test('show unlink confirm redirects to password confirm when not recently confirmed', function () {
    $provider = 'google';
    $user = User::factory()->create([
        'password' => bcrypt('password'),
    ]);

    SocialAccount::create([
        'user_id' => $user->id,
        'provider' => $provider,
        'provider_id' => '123456789',
        'provider_token' => 'token',
    ]);

    $response = $this->actingAs($user)
        ->get(route('social.unlink.confirm', ['provider' => $provider]));

    $response->assertRedirect(route('password.confirm'));
});

test('unlink delete redirects to password confirm when not recently confirmed', function () {
    $provider = 'google';
    $user = User::factory()->create([
        'password' => bcrypt('password'),
    ]);

    SocialAccount::create([
        'user_id' => $user->id,
        'provider' => $provider,
        'provider_id' => '123456789',
        'provider_token' => 'token',
    ]);

    $response = $this->actingAs($user)
        ->delete(route('social.unlink', ['provider' => $provider]));

    $response->assertRedirect(route('password.confirm'));
});

test('prevents linking already linked provider', function () {
    $provider = 'google';
    $user = User::factory()->create();

    SocialAccount::create([
        'user_id' => $user->id,
        'provider' => $provider,
        'provider_id' => '123456789',
        'provider_token' => 'token',
    ]);

    $socialiteUser = m::mock(SocialiteUser::class);
    $socialiteUser->shouldReceive('getId')->andReturn('987654321');
    $socialiteUser->shouldReceive('getEmail')->andReturn('different@example.com');
    $socialiteUser->shouldReceive('getName')->andReturn('Different User');
    $socialiteUser->token = 'oauth-token';
    $socialiteUser->refreshToken = 'refresh-token';

    Socialite::shouldReceive('driver')
        ->with($provider)
        ->once()
        ->andReturnSelf();

    Socialite::shouldReceive('with')
        ->with(m::type('array'))
        ->once()
        ->andReturnSelf();

    Socialite::shouldReceive('redirect')
        ->once()
        ->andReturn(redirect('http://google.com'));

    $redirectResponse = $this->actingAs($user)
        ->get(route('social.link', ['provider' => $provider]));

    $redirectResponse->assertRedirect();

    // Simulate callback with intent=link
    $state = base64_encode(json_encode(['intent' => 'link']));

    Socialite::shouldReceive('driver')
        ->with($provider)
        ->once()
        ->andReturnSelf();

    Socialite::shouldReceive('user')
        ->once()
        ->andReturn($socialiteUser);

    $callbackResponse = $this->actingAs($user)
        ->get(route('social.callback', ['provider' => $provider, 'state' => $state]));

    $callbackResponse->assertRedirect(route('profile.edit', absolute: false));
    $callbackResponse->assertSessionHas('error');
});

test('rejects invalid provider', function () {
    $response = $this->get(route('social.redirect', ['provider' => 'invalid']));

    $response->assertNotFound();
});

test('handles oauth errors gracefully', function () {
    $provider = 'google';

    Socialite::shouldReceive('driver')
        ->with($provider)
        ->once()
        ->andThrow(new \Exception('OAuth error'));

    $response = $this->get(route('social.callback', ['provider' => $provider]));

    $response->assertRedirect(route('login', absolute: false));
    $response->assertSessionHas('error');
});
