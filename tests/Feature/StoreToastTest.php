<?php

use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('store front uses the centered store toast component', function () {
    $this->get('/')
        ->assertOk()
        ->assertSee('data-store-toast')
        ->assertDontSee('top-4 left-1/2 -translate-x-1/2');
});
