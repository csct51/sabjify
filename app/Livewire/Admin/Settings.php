<?php

namespace App\Livewire\Admin;

use App\Models\Setting;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.admin')]
#[Title('Settings')]
class Settings extends Component
{
    public string $storeName = '';

    public string $deliveryFee = '';

    public string $freeDeliveryThreshold = '';

    public string $placeholderImage = '';

    /** @var array<int, string> */
    public array $enabledPaymentMethods = [];

    public function mount(): void
    {
        $this->storeName = (string) Setting::get('store_name', config('app.name'));
        $this->deliveryFee = (string) Setting::get('delivery_fee', config('mart.delivery_fee'));
        $this->freeDeliveryThreshold = (string) Setting::get('free_delivery_threshold', config('mart.free_delivery_threshold'));
        $this->placeholderImage = (string) Setting::get('placeholder_image', config('mart.placeholder_image'));
        $this->enabledPaymentMethods = Setting::getArray('enabled_payment_methods', config('mart.enabled_payment_methods'));
    }

    public function save(): void
    {
        $this->validate([
            'storeName' => ['required', 'string', 'max:100'],
            'deliveryFee' => ['required', 'integer', 'min:0'],
            'freeDeliveryThreshold' => ['required', 'integer', 'min:0'],
            'placeholderImage' => ['nullable', 'url', 'max:500'],
            'enabledPaymentMethods' => ['required', 'array', 'min:1'],
            'enabledPaymentMethods.*' => ['in:cod,online'],
        ]);

        Setting::updateOrCreate(['key' => 'store_name'], ['value' => $this->storeName]);
        Setting::updateOrCreate(['key' => 'delivery_fee'], ['value' => $this->deliveryFee]);
        Setting::updateOrCreate(['key' => 'free_delivery_threshold'], ['value' => $this->freeDeliveryThreshold]);
        Setting::updateOrCreate(['key' => 'placeholder_image'], ['value' => $this->placeholderImage]);
        Setting::updateOrCreate(['key' => 'enabled_payment_methods'], ['value' => implode(',', $this->enabledPaymentMethods)]);

        session()->flash('success', 'Settings saved.');
    }

    /**
     * @return array<string, string>
     */
    protected function messages(): array
    {
        return [
            'enabledPaymentMethods.required' => 'At least one payment method is required.',
        ];
    }

    public function render(): View
    {
        return view('livewire.admin.settings');
    }
}
