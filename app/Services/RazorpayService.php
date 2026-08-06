<?php

namespace App\Services;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;

class RazorpayService
{
    private const API_BASE_URL = 'https://api.razorpay.com/v1';

    /**
     * Create a Razorpay order and return its id.
     *
     * @param  array<string, string>  $notes
     *
     * @throws RequestException
     * @throws ConnectionException
     */
    public function createOrder(int $amountInPaise, string $receipt, array $notes = []): string
    {
        $response = $this->client()->post(self::API_BASE_URL.'/orders', [
            'amount' => $amountInPaise,
            'currency' => 'INR',
            'receipt' => $receipt,
            'notes' => $notes,
        ])->throw();

        return (string) $response->json('id');
    }

    public function verifySignature(string $razorpayOrderId, string $razorpayPaymentId, string $razorpaySignature): bool
    {
        $expected = hash_hmac('sha256', $razorpayOrderId.'|'.$razorpayPaymentId, config('razorpay.key_secret'));

        return hash_equals($expected, $razorpaySignature);
    }

    /**
     * Fetch a payment by its Razorpay payment id.
     *
     * @return array<string, mixed>
     *
     * @throws RequestException
     * @throws ConnectionException
     */
    public function fetchPayment(string $paymentId): array
    {
        return $this->client()
            ->get(self::API_BASE_URL.'/payments/'.$paymentId)
            ->throw()
            ->json();
    }

    private function client(): PendingRequest
    {
        return Http::withBasicAuth(config('razorpay.key_id'), config('razorpay.key_secret'))
            ->acceptJson()
            ->asJson();
    }
}
