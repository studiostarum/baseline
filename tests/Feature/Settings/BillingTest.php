<?php

use App\Models\User;
use Illuminate\Support\Facades\Config;

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

test('billing page passes defaultPriceIdAnnual when configured', function () {
    Config::set('services.stripe.price_id', 'price_monthly');
    Config::set('services.stripe.price_id_annual', 'price_annual');

    $user = User::factory()->create();

    $response = $this
        ->actingAs($user)
        ->get(route('billing.show'));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('settings/Billing')
        ->has('defaultPriceId')
        ->has('defaultPriceIdAnnual')
        ->where('defaultPriceId', 'price_monthly')
        ->where('defaultPriceIdAnnual', 'price_annual')
    );
});

test('checkout requires authentication', function () {
    $response = $this->post(route('billing.checkout'), [
        'price_id' => 'price_xxx',
    ]);

    $response->assertRedirect(route('login'));
});

test('checkout without price_id returns back with error when no default configured', function () {
    Config::set('services.stripe.price_id', null);
    Config::set('services.stripe.price_id_annual', null);
    Config::set('cashier.key', 'pk_test_xxx');
    Config::set('cashier.secret', 'sk_test_xxx');

    $user = User::factory()->create();

    $response = $this
        ->actingAs($user)
        ->from(route('billing.show'))
        ->post(route('billing.checkout'), []);

    $response->assertRedirect(route('billing.show'));
    $response->assertSessionHas('error');
});

test('checkout with invalid price_id returns back with error when both prices configured', function () {
    Config::set('services.stripe.price_id', 'price_monthly');
    Config::set('services.stripe.price_id_annual', 'price_annual');
    Config::set('cashier.key', 'pk_test_xxx');
    Config::set('cashier.secret', 'sk_test_xxx');

    $user = User::factory()->create();

    $response = $this
        ->actingAs($user)
        ->from(route('billing.show'))
        ->post(route('billing.checkout'), [
            'price_id' => 'price_invalid',
        ]);

    $response->assertRedirect(route('billing.show'));
    $response->assertSessionHas('error');
});
