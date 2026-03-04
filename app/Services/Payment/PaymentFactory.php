<?php

namespace App\Services\Payment;

use Throwable;
use Razorpay\Api\Api;
use RuntimeException;
use App\Services\HelperService;
use Illuminate\Support\Facades\Log;

class PaymentFactory
{
    public function for(string $method): PaymentGatewayContract
    {
        return match ($method) {
            'stripe'      => app(StripeCheckoutService::class),
            'razorpay'    => app(RazorpayCheckoutService::class),
            'flutterwave' => app(FlutterwaveCheckoutService::class),
            //'cash'     => app(CashOnDeliveryService::class),
            default    => throw new \InvalidArgumentException('Unsupported payment method'),
        };
    }
}