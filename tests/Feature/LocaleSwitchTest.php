<?php

it('redirects back when switching locale', function () {
    $response = $this->get('/locale/nl');

    $response->assertRedirect();
});

it('stores locale in session when switching', function () {
    $response = $this->get('/locale/nl');

    $response->assertSessionHas('locale', 'nl');
});

it('applies locale on subsequent request', function () {
    $this->get('/locale/nl');
    $response = $this->get('/');

    $response->assertInertia(fn ($page) => $page->component('Welcome')->where('locale', 'nl'));
});

it('persists locale in cookie when switching', function () {
    $response = $this->get('/locale/nl');

    $response->assertCookie('locale', 'nl');
});

it('applies locale from cookie when session has no locale', function () {
    $response = $this->withCookie('locale', 'nl')->get('/');

    $response->assertInertia(fn ($page) => $page->component('Welcome')->where('locale', 'nl'));
});

it('rejects invalid locale', function () {
    $response = $this->get('/locale/de');

    $response->assertNotFound();
});
