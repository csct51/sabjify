<?php

use App\Models\Admin;
use App\Models\Purchase;

test('inventory group stays open on purchase child routes', function () {
    $admin = Admin::factory()->create();
    $purchase = Purchase::create([
        'purchase_number' => 'PUR-950',
        'supplier_name' => 'Cash',
        'purchase_date' => now()->format('Y-m-d'),
        'total_amount' => 0,
    ]);

    $routes = [
        route('admin.purchases.index'),
        route('admin.purchases.create'),
        route('admin.purchases.edit', $purchase),
        route('admin.purchases.show', $purchase),
    ];

    foreach ($routes as $url) {
        $html = $this->actingAs($admin, 'admin')->get($url)->assertOk()->getContent();

        // Inventory group renders open in both desktop and mobile sidebars.
        expect(substr_count($html, 'x-data="{ open: true }"'))->toBe(2);
    }
});

test('only the current sidebar link is highlighted', function () {
    $admin = Admin::factory()->create();

    // Report pages: shared group pattern must not light up neighbour links.
    // Highlighted nav links render in both desktop and mobile sidebars.
    // (Scoped to the nav-link class chain; the x-logo uses the same colors.)
    foreach ([route('admin.reports.selling'), route('admin.reports.stock')] as $url) {
        $html = $this->actingAs($admin, 'admin')->get($url)->assertOk()->getContent();

        expect(substr_count($html, 'transition bg-brand-600 text-white'))->toBe(2);
    }

    // Child routes keep their parent link highlighted, and only it.
    $html = $this->actingAs($admin, 'admin')->get(route('admin.purchases.create'))->assertOk()->getContent();

    expect(substr_count($html, 'transition bg-brand-600 text-white'))->toBe(2);
});

test('unrelated groups stay closed on child routes', function () {
    $admin = Admin::factory()->create();

    $html = $this->actingAs($admin, 'admin')->get(route('admin.dashboard'))->assertOk()->getContent();

    expect(substr_count($html, 'x-data="{ open: true }"'))->toBe(0);
});
