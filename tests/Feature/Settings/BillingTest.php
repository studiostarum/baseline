<?php

use App\Models\User;

test('billing page is displayed', function () {
    $user = User::factory()->create();

    $response = $this
        ->actingAs($user)
        ->get(route('billing.show'));

    $response->assertOk();
});

test('billing page requires authentication', function () {
    $response = $this->get(route('billing.show'));

    $response->assertRedirect(route('login'));
});

test('billing page is accessible by unverified users when MustVerifyEmail is not implemented', function () {
    $user = User::factory()->unverified()->create();

    $response = $this
        ->actingAs($user)
        ->get(route('billing.show'));

    $response->assertOk();
});

test('billing page shows no subscription for new users', function () {
    $user = User::factory()->create();

    $response = $this
        ->actingAs($user)
        ->get(route('billing.show'));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('settings/Billing')
        ->where('subscription', null)
        ->where('hasStripeCustomer', false)
    );
});

test('billing portal requires authentication', function () {
    $response = $this->post(route('billing.portal'));

    $response->assertRedirect(route('login'));
});
