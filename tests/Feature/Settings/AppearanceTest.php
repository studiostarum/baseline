<?php

use App\Models\User;

test('appearance page is displayed for authenticated users', function () {
    $user = User::factory()->create();

    $response = $this
        ->actingAs($user)
        ->get(route('appearance.edit'));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('settings/Appearance')
        ->has('sidebarOpen')
    );
});
