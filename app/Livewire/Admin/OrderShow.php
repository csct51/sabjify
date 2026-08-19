<?php

namespace App\Livewire\Admin;

use App\Models\DeliveryLocation;
use App\Models\Order;
use App\Services\OrderService;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\Component;

#[Layout('layouts.admin')]
class OrderShow extends Component
{
    #[Locked]
    public Order $order;

    public string $status = '';

    public string $paymentStatus = '';

    public string $cancelReason = '';

    public function mount(): void
    {
        abort_unless(auth('admin')->check(), 403);

        $this->order->load(['items.product', 'items.basket.products.units', 'user']);

        $this->status = $this->order->status;
        $this->paymentStatus = $this->order->payment_status;
    }

    public function updateStatus(): void
    {
        $this->validate(['status' => ['required', 'in:'.implode(',', array_keys(Order::STATUSES))]]);

        $data = ['status' => $this->status];

        if ($this->status === Order::STATUS_DELIVERED) {
            $data['delivered_at'] = now();
        }

        $this->order->update($data);
        $this->order->refresh();

        $this->dispatch('toast', message: 'Order status updated.');
    }

    public function updatePaymentStatus(): void
    {
        abort_unless($this->order->payment_method === 'cod', 403);

        $this->validate(['paymentStatus' => ['required', 'in:pending,paid,refunded']]);

        $this->order->update(['payment_status' => $this->paymentStatus]);
        $this->order->refresh();

        $this->dispatch('toast', message: 'Payment status updated.');
    }

    public function cancelOrder(): void
    {
        $this->validate(['cancelReason' => ['required', 'string', 'max:200']]);

        app(OrderService::class)->cancel($this->order, $this->cancelReason, 'platform');

        $this->status = $this->order->status;
        $this->order->refresh();

        $this->dispatch('toast', message: 'Order cancelled.');
    }

    /**
     * @return array<string, string>
     */
    protected function messages(): array
    {
        return [
            'cancelReason.required' => 'Please provide a reason for cancelling this order.',
        ];
    }

    /**
     * Build one map route per active delivery location whose radius covers the
     * order point. Falls back to a route from the store when no zone covers it.
     *
     * @return array<int, array{from: array{0: float, 1: float}, to: array{0: float, 1: float}, name: string, radiusKm: float}>
     */
    public function coveringRoutes(): array
    {
        $orderLat = $this->order->latitude !== null ? (float) $this->order->latitude : null;
        $orderLng = $this->order->longitude !== null ? (float) $this->order->longitude : null;

        if ($orderLat === null || $orderLng === null) {
            return [];
        }

        $covering = DeliveryLocation::active()
            ->get()
            ->filter(fn (DeliveryLocation $zone): bool => $this->distanceKm($orderLat, $orderLng, (float) $zone->latitude, (float) $zone->longitude) <= (float) $zone->radius_km);

        /** @var Collection<int, object{name: string, latitude: float, longitude: float, radius_km: float}> $zones */
        $zones = $covering->isNotEmpty()
            ? $covering
            : collect([(object) [
                'name' => 'Store',
                'latitude' => (float) config('mart.map_default_lat'),
                'longitude' => (float) config('mart.map_default_lng'),
                'radius_km' => 0,
            ]]);

        return $zones
            ->map(fn (object $zone): array => [
                'from' => [(float) $zone->latitude, (float) $zone->longitude],
                'to' => [$orderLat, $orderLng],
                'name' => (string) $zone->name,
                'radiusKm' => (float) $zone->radius_km,
            ])
            ->values()
            ->all();
    }

    private function distanceKm(float $aLat, float $aLng, float $bLat, float $bLng): float
    {
        $earthRadiusKm = 6371;
        $toRadians = fn (float $degrees): float => $degrees * M_PI / 180;
        $dLat = $toRadians($bLat - $aLat);
        $dLng = $toRadians($bLng - $aLng);
        $a = sin($dLat / 2) ** 2 + cos($toRadians($aLat)) * cos($toRadians($bLat)) * sin($dLng / 2) ** 2;

        return $earthRadiusKm * 2 * atan2(sqrt($a), sqrt(1 - $a));
    }

    public function render(): View
    {
        return view('livewire.admin.order-show', ['coveringRoutes' => $this->coveringRoutes()]);
    }
}
