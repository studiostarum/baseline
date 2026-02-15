<?php

use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

test('confirm password screen can be rendered', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get(route('password.confirm'));

    $response->assertOk();

    $response->assertInertia(fn (Assert $page) => $page
        ->component('auth/ConfirmPassword')
    );
});

test('password confirmation requires authentication', function () {
    $response = $this->get(route('password.confirm'));

    $response->assertRedirect(route('login'));
});

test('successful password confirmation redirects to dashboard when intended url is home', function () {
    $user = User::factory()->create([
        'password' => bcrypt('password'),
    ]);

    $response = $this->actingAs($user)
        ->withSession(['url.intended' => route('home')])
        ->post(route('password.confirm.store'), [
            'password' => 'password',
        ]);

    $response->assertRedirect(route('dashboard'));
});

test('successful password confirmation redirects to intended url when not home', function () {
    $user = User::factory()->create([
        'password' => bcrypt('password'),
    ]);
    $intendedUrl = route('two-factor.show');

    $response = $this->actingAs($user)
        ->withSession(['url.intended' => $intendedUrl])
        ->post(route('password.confirm.store'), [
            'password' => 'password',
        ]);

    $response->assertRedirect($intendedUrl);
});
