<?php

use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Layout('layouts.store')]
    #[Title('Terms & Conditions')]
    class extends Component
{
};
?>

<div>
    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="mb-8">
            <a href="{{ route('profile') }}" wire:navigate class="inline-flex items-center gap-1.5 text-sm font-medium text-stone-500 hover:text-stone-700"><i data-lucide="arrow-left" class="w-4 h-4"></i> Back to Profile</a>
            <div class="flex items-center gap-4 mt-4">
                <span class="flex items-center justify-center w-14 h-14 rounded-2xl bg-brand-600 text-white"><i data-lucide="file-text" class="w-7 h-7"></i></span>
                <div>
                    <h1 class="text-2xl font-bold text-stone-900">Terms & Conditions</h1>
                    <p class="text-sm text-stone-500">The rules and guidelines for using our service.</p>
                </div>
            </div>
        </div>

        <section class="bg-white rounded-2xl border border-stone-200 p-6 sm:p-8 space-y-8 text-sm leading-relaxed text-stone-700">
            <div>
                <p class="text-stone-500">Last updated: {{ now()->format('F j, Y') }}</p>
                <p class="mt-4">These Terms & Conditions ("Terms") govern your use of {{ config('app.name') }} ("we", "us", "our") grocery delivery service and website. By creating an account, browsing our catalogue or placing an order, you agree to be bound by these Terms.</p>
            </div>

            <div>
                <h2 class="text-base font-semibold text-stone-900 mb-2">1. Eligibility</h2>
                <p>You must be at least 18 years old, or have the permission of a parent or guardian, to use our service. By using the service, you confirm that all information you provide, including your phone number and delivery address, is accurate and complete.</p>
            </div>

            <div>
                <h2 class="text-base font-semibold text-stone-900 mb-2">2. Orders & Payment</h2>
                <ul class="list-disc pl-5 space-y-1.5">
                    <li>All prices are displayed in the applicable currency and include applicable taxes unless stated otherwise.</li>
                    <li>An order is confirmed once payment is successfully processed or, for cash on delivery, once our team accepts the order.</li>
                    <li>We reserve the right to cancel any order due to product unavailability, pricing errors or suspected fraud. If your payment was already taken, we will refund it in full.</li>
                    <li>Payment must be made using a valid method available on our platform.</li>
                </ul>
            </div>

            <div>
                <h2 class="text-base font-semibold text-stone-900 mb-2">3. Delivery</h2>
                <ul class="list-disc pl-5 space-y-1.5">
                    <li>We will deliver to the address you provide at checkout. Delivery fees and free-delivery thresholds are shown at checkout.</li>
                    <li>Delivery times are estimates and may vary due to traffic, weather or demand.</li>
                    <li>Please inspect your order upon delivery and report any issues promptly through our support channels.</li>
                </ul>
            </div>

            <div>
                <h2 class="text-base font-semibold text-stone-900 mb-2">4. Refunds & Returns</h2>
                <ul class="list-disc pl-5 space-y-1.5">
                    <li>If any item is damaged, spoiled or incorrect, please contact us within the time period stated at checkout, and we will issue a replacement or refund.</li>
                    <li>Refunds for online payments are processed back to your original payment method and may take a few business days to reflect.</li>
                </ul>
            </div>

            <div>
                <h2 class="text-base font-semibold text-stone-900 mb-2">5. Account Responsibilities</h2>
                <p>You are responsible for maintaining the confidentiality of your account and for all activity under your account. Please notify us immediately of any unauthorised use. We may suspend or terminate accounts that violate these Terms.</p>
            </div>

            <div>
                <h2 class="text-base font-semibold text-stone-900 mb-2">6. Acceptable Use</h2>
                <p>You agree not to misuse the service, including attempting to interfere with its operation, submitting false orders, or using the service for any unlawful purpose.</p>
            </div>

            <div>
                <h2 class="text-base font-semibold text-stone-900 mb-2">7. Limitation of Liability</h2>
                <p>To the maximum extent permitted by law, our liability is limited to the value of the products in your order. We are not liable for indirect, incidental or consequential damages arising from your use of the service.</p>
            </div>

            <div>
                <h2 class="text-base font-semibold text-stone-900 mb-2">8. Changes to These Terms</h2>
                <p>We may update these Terms from time to time. Any changes will be posted on this page, and continued use of the service after such changes constitutes acceptance of the updated Terms.</p>
            </div>

            <div>
                <h2 class="text-base font-semibold text-stone-900 mb-2">9. Contact Us</h2>
                <p>For questions about these Terms, please contact us at <a href="mailto:{{ 'support@' . strtolower(config('app.name')) . '.com' }}" class="text-brand-600 hover:text-brand-700 font-medium">{{ 'support@' . strtolower(config('app.name')) . '.com' }}</a>.</p>
            </div>
        </section>
    </div>
</div>
