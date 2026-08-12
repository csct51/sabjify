<?php

use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Layout('layouts.store')]
    #[Title('Privacy Policy')]
    class extends Component
{
};
?>

<div>
    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="mb-8">
            <a href="{{ route('profile') }}" wire:navigate class="inline-flex items-center gap-1.5 text-sm font-medium text-stone-500 hover:text-stone-700"><i data-lucide="arrow-left" class="w-4 h-4"></i> Back to Profile</a>
            <div class="flex items-center gap-4 mt-4">
                <span class="flex items-center justify-center w-14 h-14 rounded-2xl bg-brand-600 text-white"><i data-lucide="shield" class="w-7 h-7"></i></span>
                <div>
                    <h1 class="text-2xl font-bold text-stone-900">Privacy Policy</h1>
                    <p class="text-sm text-stone-500">How we collect, use and protect your information.</p>
                </div>
            </div>
        </div>

        <section class="bg-white rounded-2xl border border-stone-200 p-6 sm:p-8 space-y-8 text-sm leading-relaxed text-stone-700">
            <div>
                <p class="text-stone-500">Last updated: {{ now()->format('F j, Y') }}</p>
                <p class="mt-4">This Privacy Policy explains how {{ config('app.name') }} ("we", "us", "our") collects, uses, shares and protects your personal information when you use our grocery delivery service and website. By creating an account or placing an order, you agree to the practices described in this policy.</p>
            </div>

            <div>
                <h2 class="text-base font-semibold text-stone-900 mb-2">1. Information We Collect</h2>
                <ul class="list-disc pl-5 space-y-1.5">
                    <li><strong>Account information:</strong> your name, phone number and email address.</li>
                    <li><strong>Delivery information:</strong> your delivery address and delivery preferences.</li>
                    <li><strong>Order information:</strong> your order history, cart contents and payment method.</li>
                    <li><strong>Device and usage data:</strong> how you interact with our app and website, such as pages visited and products viewed.</li>
                </ul>
            </div>

            <div>
                <h2 class="text-base font-semibold text-stone-900 mb-2">2. How We Use Your Information</h2>
                <ul class="list-disc pl-5 space-y-1.5">
                    <li>To process and deliver your orders.</li>
                    <li>To send order updates, delivery notifications and confirmations.</li>
                    <li>To provide customer support and resolve issues.</li>
                    <li>To improve our products, service and overall experience.</li>
                    <li>To detect and prevent fraud, misuse or unauthorised activity.</li>
                </ul>
            </div>

            <div>
                <h2 class="text-base font-semibold text-stone-900 mb-2">3. How We Share Your Information</h2>
                <p class="mb-2">We do not sell your personal information. We only share it with service providers that help us run our service, such as:</p>
                <ul class="list-disc pl-5 space-y-1.5">
                    <li>Payment processors, to securely complete your payments.</li>
                    <li>Delivery partners, to fulfil and deliver your orders.</li>
                    <li>IT and analytics providers that support our operations.</li>
                </ul>
                <p class="mt-2">These providers are bound by confidentiality and may only use your data to perform services on our behalf.</p>
            </div>

            <div>
                <h2 class="text-base font-semibold text-stone-900 mb-2">4. Data Security</h2>
                <p>We take reasonable technical and organisational measures to protect your information against unauthorised access, alteration, disclosure or destruction. Payments are processed through secure, PCI-compliant payment gateways.</p>
            </div>

            <div>
                <h2 class="text-base font-semibold text-stone-900 mb-2">5. Data Retention</h2>
                <p>We retain your personal information for as long as your account is active or as needed to provide our services, comply with legal obligations, resolve disputes and enforce our agreements.</p>
            </div>

            <div>
                <h2 class="text-base font-semibold text-stone-900 mb-2">6. Your Rights</h2>
                <p class="mb-2">Depending on where you live, you may have the right to:</p>
                <ul class="list-disc pl-5 space-y-1.5">
                    <li>Access and obtain a copy of your personal information.</li>
                    <li>Request correction of inaccurate data.</li>
                    <li>Request deletion of your personal information.</li>
                    <li>Object to or restrict certain processing of your data.</li>
                </ul>
                <p class="mt-2">You can update your account details at any time from the Account Details section of your profile. For any other request, contact us using the details below.</p>
            </div>

            <div>
                <h2 class="text-base font-semibold text-stone-900 mb-2">7. Changes to This Policy</h2>
                <p>We may update this Privacy Policy from time to time. We will notify you of any material changes by posting the updated policy on this page and updating the "Last updated" date.</p>
            </div>

            <div>
                <h2 class="text-base font-semibold text-stone-900 mb-2">8. Contact Us</h2>
                <p>If you have any questions or concerns about this Privacy Policy or how we handle your data, please reach out to us at <a href="mailto:{{ 'support@' . strtolower(config('app.name')) . '.com' }}" class="text-brand-600 hover:text-brand-700 font-medium">{{ 'support@' . strtolower(config('app.name')) . '.com' }}</a>.</p>
            </div>
        </section>
    </div>
</div>
