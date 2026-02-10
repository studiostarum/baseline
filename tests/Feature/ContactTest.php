<?php

use Inertia\Testing\AssertableInertia;

test('contact page can be rendered', function () {
    $response = $this->get(route('contact'));

    $response->assertOk();
    $response->assertInertia(fn (AssertableInertia $page) => $page
        ->component('Contact')
    );
});

test('contact form submission with valid data redirects back with success', function () {
    $response = $this->post(route('contact.store'), [
        'name' => 'Jane Doe',
        'email' => 'jane@example.com',
        'message' => 'Hello, I have a question.',
        'accept_terms' => '1',
    ]);

    $response->assertRedirect();
    $response->assertSessionHas('status');
});

test('contact form submission with invalid data returns validation errors', function () {
    $response = $this->post(route('contact.store'), [
        'name' => '',
        'email' => 'not-an-email',
        'message' => '',
        'accept_terms' => '0',
    ]);

    $response->assertSessionHasErrors(['name', 'email', 'message', 'accept_terms']);
});
