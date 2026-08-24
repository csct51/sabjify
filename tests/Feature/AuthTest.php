<?php

use App\Livewire\Auth\PhoneLogin;
use App\Livewire\Profile;
use App\Models\OtpCode;
use App\Models\User;
use Livewire\Livewire;

test('login page renders', function () {
    $this->get('/login')->assertOk()->assertSee('Mobile Number');
});

test('send otp creates a valid code and moves to otp step', function () {
    $user = User::factory()->create(['phone' => '9876543210']);

    Livewire::test(PhoneLogin::class)
        ->set('phone', '9876543210')
        ->call('sendOtp')
        ->assertSet('step', 'otp')
        ->assertSet('isNewUser', false)
        ->assertHasNoErrors();

    $otp = OtpCode::where('phone', '9876543210')->latest()->first();

    expect($otp)->not->toBeNull()
        ->and($otp->code)->toHaveLength(6)
        ->and($otp->expires_at->isFuture())->toBeTrue()
        ->and($user->fresh()->exists())->toBeTrue();
});

test('invalid phone is rejected', function () {
    Livewire::test(PhoneLogin::class)
        ->set('phone', '12345')
        ->call('sendOtp')
        ->assertHasErrors('phone');
});

test('valid otp logs in an existing user', function () {
    $user = User::factory()->create(['phone' => '9876543210']);

    Livewire::test(PhoneLogin::class)
        ->set('phone', '9876543210')
        ->call('sendOtp');

    $code = OtpCode::where('phone', '9876543210')->latest()->first()->code;

    Livewire::test(PhoneLogin::class)
        ->set('phone', '9876543210')
        ->set('otp', $code)
        ->call('verifyOtp')
        ->assertRedirect(route('home'));

    expect(auth()->id())->toBe($user->id);
});

test('valid otp registers a new user', function () {
    Livewire::test(PhoneLogin::class)
        ->set('phone', '9876501234')
        ->call('sendOtp')
        ->assertSet('isNewUser', true);

    $code = OtpCode::where('phone', '9876501234')->latest()->first()->code;

    Livewire::test(PhoneLogin::class)
        ->set('phone', '9876501234')
        ->set('name', 'Rahul Sharma')
        ->set('otp', $code)
        ->call('verifyOtp')
        ->assertRedirect(route('home'));

    $user = User::where('phone', '9876501234')->first();

    expect($user)->not->toBeNull()
        ->and($user->name)->toBe('Rahul Sharma');
});

test('name is required for new users', function () {
    Livewire::test(PhoneLogin::class)
        ->set('phone', '9876501234')
        ->call('sendOtp');

    $code = OtpCode::where('phone', '9876501234')->latest()->first()->code;

    Livewire::test(PhoneLogin::class)
        ->set('phone', '9876501234')
        ->call('sendOtp')
        ->set('otp', $code)
        ->call('verifyOtp')
        ->assertHasErrors('name');
});

test('invalid otp is rejected', function () {
    User::factory()->create(['phone' => '9876543210']);

    Livewire::test(PhoneLogin::class)
        ->set('phone', '9876543210')
        ->call('sendOtp');

    Livewire::test(PhoneLogin::class)
        ->set('phone', '9876543210')
        ->set('otp', '000000')
        ->call('verifyOtp')
        ->assertHasErrors('otp');

    expect(auth()->check())->toBeFalse();
});

test('used otp cannot be reused', function () {
    User::factory()->create(['phone' => '9876543210']);

    Livewire::test(PhoneLogin::class)
        ->set('phone', '9876543210')
        ->call('sendOtp');

    $code = OtpCode::where('phone', '9876543210')->latest()->first()->code;

    Livewire::test(PhoneLogin::class)
        ->set('phone', '9876543210')
        ->set('otp', $code)
        ->call('verifyOtp');

    Livewire::test(PhoneLogin::class)
        ->set('phone', '9876543210')
        ->set('otp', $code)
        ->call('verifyOtp')
        ->assertHasErrors('otp');
});

test('deactivated account cannot log in', function () {
    User::factory()->create(['phone' => '9876543210', 'is_active' => false]);

    Livewire::test(PhoneLogin::class)
        ->set('phone', '9876543210')
        ->call('sendOtp')
        ->assertSet('isInactive', true)
        ->assertHasErrors('phone');
});

test('user can log out from profile', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get('/profile')
        ->assertOk();

    Livewire::test(Profile::class)
        ->call('logout')
        ->assertRedirect(route('login'));

    expect(auth()->check())->toBeFalse()
        ->and(auth('web')->check())->toBeFalse();
});

test('otp requests are rate limited', function () {
    $phone = '9811111111';

    $last = null;
    for ($i = 0; $i < 6; $i++) {
        $last = Livewire::test(PhoneLogin::class)
            ->set('phone', $phone)
            ->call('sendOtp');
    }

    $last->assertHasErrors('phone');
    expect(OtpCode::where('phone', $phone)->count())->toBeLessThan(6);
});

test('otp verification attempts are rate limited', function () {
    $phone = '9822222222';

    Livewire::test(PhoneLogin::class)->set('phone', $phone)->call('sendOtp');

    $last = null;
    for ($i = 0; $i < 6; $i++) {
        $last = Livewire::test(PhoneLogin::class)
            ->set('phone', $phone)
            ->set('otp', '000000')
            ->call('verifyOtp');
    }

    $last->assertHasErrors('otp');
});
