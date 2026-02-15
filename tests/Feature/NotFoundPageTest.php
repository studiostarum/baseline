<?php

use Inertia\Testing\AssertableInertia;

test('nonexistent route returns 404 and renders NotFound Inertia page', function () {
    $response = $this->get('/nonexistent-page');

    $response->assertNotFound();
    $response->assertInertia(fn (AssertableInertia $page) => $page
        ->component('errors/NotFound')
    );
});
