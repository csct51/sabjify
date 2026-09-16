<?php

use App\Livewire\Auth\PhoneLogin;
use App\Models\OtpCode;
use App\Models\User;
use Livewire\Livewire;

test('debug phone skips otp send and stores no code', function () {
    User::factory()->create(['phone' => '7869815580', 'is_active' => true]);

    Livewire::test(PhoneLogin::class)
        ->set('phone', '7869815580')
        ->call('sendOtp')
        ->assertSet('step', 'otp')
        ->assertHasNoErrors();

    expect(OtpCode::where('phone', '7869815580')->exists())->toBeFalse();
});

test('debug phone logs in with the bypass otp without a stored code', function () {
    $user = User::factory()->create(['phone' => '7869815580', 'is_active' => true]);

    Livewire::test(PhoneLogin::class)
        ->set('phone', '7869815580')
        ->call('sendOtp');

    Livewire::test(PhoneLogin::class)
        ->set('phone', '7869815580')
        ->set('otp', '147258')
        ->call('verifyOtp')
        ->assertRedirect(route('home'));

    expect(auth()->id())->toBe($user->id);
});

test('debug phone still rejects a wrong otp', function () {
    User::factory()->create(['phone' => '7869815580', 'is_active' => true]);

    Livewire::test(PhoneLogin::class)
        ->set('phone', '7869815580')
        ->call('sendOtp');

    Livewire::test(PhoneLogin::class)
        ->set('phone', '7869815580')
        ->set('otp', '147259')
        ->call('verifyOtp')
        ->assertHasErrors('otp');

    expect(auth()->check())->toBeFalse();
});

test('bypass otp does not work for other numbers', function () {
    User::factory()->create(['phone' => '9876543210', 'is_active' => true]);

    Livewire::test(PhoneLogin::class)
        ->set('phone', '9876543210')
        ->call('sendOtp');

    Livewire::test(PhoneLogin::class)
        ->set('phone', '9876543210')
        ->set('otp', '147258')
        ->call('verifyOtp')
        ->assertHasErrors('otp');

    expect(auth()->check())->toBeFalse();
});
