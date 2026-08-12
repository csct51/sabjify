<?php

use Livewire\Livewire;

test('privacy policy page is accessible', function () {
    $this->get(route('privacy-policy'))
        ->assertOk()
        ->assertSee('Privacy Policy')
        ->assertSee('Information We Collect')
        ->assertSee('Data Security');
});

test('terms and conditions page is accessible', function () {
    $this->get(route('terms-conditions'))
        ->assertOk()
        ->assertSee('Terms & Conditions', false)
        ->assertSee('Orders & Payment', false)
        ->assertSee('Refunds & Returns', false);
});

test('privacy policy page renders as a livewire component', function () {
    Livewire::test('privacy-policy')
        ->assertOk()
        ->assertSee('Privacy Policy');
});

test('terms and conditions page renders as a livewire component', function () {
    Livewire::test('terms-conditions')
        ->assertOk()
        ->assertSee('Terms & Conditions', false);
});
