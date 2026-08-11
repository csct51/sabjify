<?php

namespace App\Services;

use App\Models\Order;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Response;

class InvoiceService
{
    public function download(Order $order): Response
    {
        $order->load(['items.product', 'items.basket.products', 'user']);

        return Pdf::loadView('invoices.order', ['order' => $order])
            ->setPaper('a4')
            ->download('invoice-'.$order->order_number.'.pdf');
    }
}
