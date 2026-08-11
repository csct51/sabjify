<?php

use App\Livewire\Admin\Password;
use App\Models\Admin;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;

test('guest is redirected to admin login when accessing change password', function () {
    $this->get('/admin/password')->assertRedirect(route('admin.login'));
});

test('admin can view the change password page', function () {
    $admin = Admin::factory()->create();

    $this->actingAs($admin, 'admin')->get('/admin/password')->assertOk()->assertSee('Change Password');
});

test('admin can update their password', function () {
    $admin = Admin::factory()->create(['password' => 'current-password']);

    Livewire::actingAs($admin, 'admin')
        ->test(Password::class)
        ->set('currentPassword', 'current-password')
        ->set('newPassword', 'new-password-123')
        ->set('newPasswordConfirmation', 'new-password-123')
        ->call('updatePassword')
        ->assertHasNoErrors()
        ->assertDispatched('toast', message: 'Password updated successfully.');

    expect(Hash::check('new-password-123', $admin->fresh()->password))->toBeTrue();
});

test('admin cannot change password with an incorrect current password', function () {
    $admin = Admin::factory()->create(['password' => 'current-password']);

    Livewire::actingAs($admin, 'admin')
        ->test(Password::class)
        ->set('currentPassword', 'wrong-password')
        ->set('newPassword', 'new-password-123')
        ->set('newPasswordConfirmation', 'new-password-123')
        ->call('updatePassword')
        ->assertHasErrors(['currentPassword']);

    expect(Hash::check('current-password', $admin->fresh()->password))->toBeTrue();
});

test('new password must be at least 8 characters', function () {
    $admin = Admin::factory()->create(['password' => 'current-password']);

    Livewire::actingAs($admin, 'admin')
        ->test(Password::class)
        ->set('currentPassword', 'current-password')
        ->set('newPassword', 'short')
        ->set('newPasswordConfirmation', 'short')
        ->call('updatePassword')
        ->assertHasErrors(['newPassword' => 'min']);
});

test('new password confirmation must match', function () {
    $admin = Admin::factory()->create(['password' => 'current-password']);

    Livewire::actingAs($admin, 'admin')
        ->test(Password::class)
        ->set('currentPassword', 'current-password')
        ->set('newPassword', 'new-password-123')
        ->set('newPasswordConfirmation', 'different-password')
        ->call('updatePassword')
        ->assertHasErrors(['newPassword' => 'same']);
});
