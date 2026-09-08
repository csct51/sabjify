<?php

use App\Livewire\Admin\InfoCards\Edit as InfoCardsEdit;
use App\Livewire\Admin\InfoCards\Index as InfoCardsIndex;
use App\Livewire\BasketShow;
use App\Livewire\Home;
use App\Models\Admin;
use App\Models\Basket;
use App\Models\InfoCard;
use App\Support\InfoCards;
use Livewire\Livewire;

test('info cards fall back to defaults when the table is empty', function () {
    expect(InfoCard::count())->toBe(0)
        ->and(InfoCards::all())->toBe(InfoCards::DEFAULTS);
});

test('index lists all six cards with edit links', function () {
    $admin = Admin::factory()->create();

    Livewire::actingAs($admin, 'admin')
        ->test(InfoCardsIndex::class)
        ->assertSee('On-time delivery')
        ->assertSee('Health basket')
        ->assertSee(route('admin.info-cards.edit', 1));
});

test('admin can edit a card and it renders on the storefront', function () {
    $admin = Admin::factory()->create();

    Livewire::actingAs($admin, 'admin')
        ->test(InfoCardsEdit::class, ['position' => 1])
        ->set('title', 'Lightning delivery')
        ->set('subtitle', 'Same day')
        ->set('icon', 'gift')
        ->call('save')
        ->assertRedirect(route('admin.info-cards.index'));

    expect(InfoCard::count())->toBe(1)
        ->and(InfoCard::where('position', 1)->first()->title)->toBe('Lightning delivery');

    $basket = Basket::factory()->create(['is_active' => true]);

    Livewire::test(Home::class)
        ->assertSee('Lightning delivery')
        ->assertSee('Same day')
        ->assertDontSee('On-time delivery');

    Livewire::test(BasketShow::class, ['basket' => $basket])
        ->assertSee('Lightning delivery')
        ->assertDontSee('On-time delivery');
});

test('info card icon outside the whitelist is rejected', function () {
    $admin = Admin::factory()->create();

    Livewire::actingAs($admin, 'admin')
        ->test(InfoCardsEdit::class, ['position' => 1])
        ->set('title', 'Lightning delivery')
        ->set('icon', 'not-an-icon')
        ->call('save')
        ->assertHasErrors('icon');

    expect(InfoCard::count())->toBe(0);
});
