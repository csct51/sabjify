<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Services\InvoiceService;
use Illuminate\Http\Response;

class InvoiceController extends Controller
{
    public function download(Order $order): Response
    {
        abort_unless(
            $order->user_id === auth('web')->id() || auth('admin')->check(),
            403
        );

        return app(InvoiceService::class)->download($order);
    }
}
