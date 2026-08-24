<?php

use App\Models\User;

test('guests can browse the home page without logging in', function () {
    $this->get('/')->assertOk();
});

test('authenticated users can access the home page', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->get('/')->assertOk();
});

test('guests are redirected from account areas', function () {
    $this->get('/cart')->assertRedirect();
    $this->get('/profile')->assertRedirect();
});
