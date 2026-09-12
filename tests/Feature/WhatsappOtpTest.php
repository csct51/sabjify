<?php

use App\Livewire\Auth\PhoneLogin;
use App\Models\OtpCode;
use App\Services\WhatsappService;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Livewire\Livewire;

beforeEach(function () {
    config(['services.aoc.whatsapp.key' => 'test-key']);
});

test('otp is sent over whatsapp with the template payload', function () {
    Http::fake(['*' => Http::response(['status' => 'sent'], 200)]);

    Livewire::test(PhoneLogin::class)
        ->set('phone', '9876543210')
        ->call('sendOtp')
        ->assertSet('step', 'otp')
        ->assertHasNoErrors();

    $code = OtpCode::where('phone', '9876543210')->latest()->first()->code;

    Http::assertSent(function (Request $request) use ($code) {
        return $request->url() === 'https://api.aoc-portal.com/v1/whatsapp'
            && $request->header('apikey') === ['test-key']
            && $request['to'] === '+919876543210'
            && $request['templateName'] === 'otp'
            && $request['otp'] === $code
            && $request['type'] === 'template';
    });
});

test('gateway rejection shows a retry error without advancing', function () {
    Http::fake(['*' => Http::response(['error' => 'nope'], 500)]);

    Livewire::test(PhoneLogin::class)
        ->set('phone', '9876543210')
        ->call('sendOtp')
        ->assertSet('step', 'phone')
        ->assertHasErrors(['phone' => 'Could not send the OTP on WhatsApp. Please try again.']);
});

test('connection failure shows a retry error without advancing', function () {
    Http::fake(fn () => Http::failedConnection('timeout'));

    Livewire::test(PhoneLogin::class)
        ->set('phone', '9876543210')
        ->call('sendOtp')
        ->assertSet('step', 'phone')
        ->assertHasErrors('phone');
});

test('no api key means no http attempt and normal flow', function () {
    config(['services.aoc.whatsapp.key' => null]);
    Http::preventStrayRequests();

    Livewire::test(PhoneLogin::class)
        ->set('phone', '9876543210')
        ->call('sendOtp')
        ->assertSet('step', 'otp')
        ->assertHasNoErrors();
});

test('dev otp hint is hidden when whatsapp is configured', function () {
    Http::fake(['*' => Http::response(['status' => 'sent'], 200)]);

    Livewire::test(PhoneLogin::class)
        ->set('phone', '9876543210')
        ->call('sendOtp')
        ->assertSet('devOtp', null);
});

test('dev otp hint still shows without a key in local', function () {
    app()->instance('env', 'local');
    config(['services.aoc.whatsapp.key' => null]);

    expect((new PhoneLogin)->showsDevOtp())->toBeTrue();
});

test('dev otp hint hides outside local even without a key', function () {
    config(['services.aoc.whatsapp.key' => null]);

    expect((new PhoneLogin)->showsDevOtp())->toBeFalse();
});

test('phone numbers normalize to international format', function () {
    $service = app(WhatsappService::class);

    expect($service->normalizePhone('9876543210'))->toBe('+919876543210')
        ->and($service->normalizePhone('+919876543210'))->toBe('+919876543210')
        ->and($service->normalizePhone('919876543210'))->toBe('+919876543210');
});
