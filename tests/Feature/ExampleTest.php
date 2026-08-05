<?php

use App\Models\User;

test('guests are redirected to login when visiting the application', function () {
    $this->get('/')->assertRedirect(route('login'));
});

test('authenticated users can access the home page', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->get('/')->assertOk();
});
