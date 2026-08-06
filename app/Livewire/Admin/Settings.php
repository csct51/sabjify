<?php

namespace App\Livewire\Admin;

use App\Models\Setting;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\WithFileUploads;

#[Layout('layouts.admin')]
#[Title('Settings')]
class Settings extends Component
{
    use WithFileUploads;

    public string $storeName = '';

    public string $deliveryFee = '';

    public string $freeDeliveryThreshold = '';

    public string $minimumOrderAmount = '';

    public string $logoType = 'icon';

    public string $logoUrl = '';

    public ?TemporaryUploadedFile $logoImage = null;

    /** @var array<int, string> */
    public array $enabledPaymentMethods = [];

    public function mount(): void
    {
        $this->storeName = (string) Setting::get('store_name', config('app.name'));
        $this->deliveryFee = (string) Setting::get('delivery_fee', config('mart.delivery_fee'));
        $this->freeDeliveryThreshold = (string) Setting::get('free_delivery_threshold', config('mart.free_delivery_threshold'));
        $this->minimumOrderAmount = (string) Setting::get('minimum_order_amount', config('mart.minimum_order_amount'));
        $this->logoType = (string) Setting::get('logo_type', 'icon');
        $this->logoUrl = $this->logoType === 'url' ? (string) Setting::get('logo_value', '') : '';
        $this->enabledPaymentMethods = Setting::getArray('enabled_payment_methods', config('mart.enabled_payment_methods'));
    }

    public function save(): void
    {
        $this->validate([
            'storeName' => ['required', 'string', 'max:100'],
            'deliveryFee' => ['required', 'integer', 'min:0'],
            'freeDeliveryThreshold' => ['required', 'integer', 'min:0'],
            'minimumOrderAmount' => ['required', 'integer', 'min:0'],
            'logoType' => ['required', 'in:icon,image,url'],
            'logoUrl' => ['required_if:logoType,url', 'nullable', 'url', 'max:500'],
            'logoImage' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'enabledPaymentMethods' => ['required', 'array', 'min:1'],
            'enabledPaymentMethods.*' => ['in:cod,online'],
        ]);

        Setting::updateOrCreate(['key' => 'store_name'], ['value' => $this->storeName]);
        Setting::updateOrCreate(['key' => 'delivery_fee'], ['value' => $this->deliveryFee]);
        Setting::updateOrCreate(['key' => 'free_delivery_threshold'], ['value' => $this->freeDeliveryThreshold]);
        Setting::updateOrCreate(['key' => 'minimum_order_amount'], ['value' => $this->minimumOrderAmount]);
        Setting::updateOrCreate(['key' => 'enabled_payment_methods'], ['value' => implode(',', $this->enabledPaymentMethods)]);

        $this->saveLogo();

        session()->flash('success', 'Settings saved.');
    }

    protected function saveLogo(): void
    {
        if ($this->logoType === 'icon') {
            Setting::where('key', 'logo_type')->delete();
            Setting::where('key', 'logo_value')->delete();

            return;
        }

        if ($this->logoType === 'url') {
            Setting::updateOrCreate(['key' => 'logo_type'], ['value' => 'url']);
            Setting::updateOrCreate(['key' => 'logo_value'], ['value' => $this->logoUrl]);

            return;
        }

        if ($this->logoImage === null) {
            return;
        }

        $oldPath = Setting::get('logo_value');

        if (is_string($oldPath) && $oldPath !== '' && Storage::disk('public')->exists($oldPath)) {
            Storage::disk('public')->delete($oldPath);
        }

        $path = $this->logoImage->store('logos', 'public');

        Setting::updateOrCreate(['key' => 'logo_type'], ['value' => 'image']);
        Setting::updateOrCreate(['key' => 'logo_value'], ['value' => $path]);
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
