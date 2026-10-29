<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Http;
use WeBRTeu\Payments\Facades\Payments;

it('posts a test transaction form to EuPlatesc', function (): void {
    $redirect = Payments::gateway('euplatesc')->createPayment(paymentRequest('SMOKE-'.time()));

    expect(Http::asForm()->post($redirect->url, $redirect->fields)->successful())->toBeTrue();
})->group('sandbox')->skip(! env('EUPLATESC_SANDBOX_MERCHANT_ID'), 'Setează EUPLATESC_SANDBOX_MERCHANT_ID / _SECRET_KEY (cont în mod test).');
