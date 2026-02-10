<?php

use App\Mail\ContactFormSubmitted;
use App\Models\Setting;
use Illuminate\Support\Facades\Mail;
use Inertia\Testing\AssertableInertia;

test('contact page can be rendered', function () {
    $response = $this->get(route('contact'));

    $response->assertOk();
    $response->assertInertia(fn (AssertableInertia $page) => $page
        ->component('Contact')
    );
});

test('contact form submission with valid data sends email and redirects with success', function () {
    Mail::fake();
    Setting::set('contact_email', 'admin@example.com');

    $response = $this->post(route('contact.store'), [
        'name' => 'Jane Doe',
        'email' => 'jane@example.com',
        'message' => 'Hello, I have a question.',
        'accept_terms' => '1',
    ]);

    $response->assertRedirect();
    $response->assertSessionHas('status');
    Mail::assertSent(ContactFormSubmitted::class, function (ContactFormSubmitted $mail) {
        return $mail->hasTo('admin@example.com')
            && $mail->data['name'] === 'Jane Doe'
            && $mail->data['email'] === 'jane@example.com'
            && $mail->data['message'] === 'Hello, I have a question.';
    });
});

test('contact form submission when contact email is not configured redirects with error and does not send email', function () {
    Mail::fake();
    Setting::where('key', 'contact_email')->delete();

    $response = $this->post(route('contact.store'), [
        'name' => 'Jane Doe',
        'email' => 'jane@example.com',
        'message' => 'Hello, I have a question.',
        'accept_terms' => '1',
    ]);

    $response->assertRedirect();
    $response->assertSessionHas('error');
    Mail::assertNotSent(ContactFormSubmitted::class);
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
