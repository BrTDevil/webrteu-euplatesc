<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Config;
use WeBRTeu\Payments\Enums\RedirectType;
use WeBRTeu\Payments\Exceptions\UnsupportedOperationException;
use WeBRTeu\Payments\Facades\Payments;
use WeBRTeu\Payments\Models\Payment;

it('returns a Form redirect with every field', function (): void {
    $redirect = Payments::start('euplatesc', paymentRequest());

    expect($redirect->type)->toBe(RedirectType::Form)
        ->and($redirect->url)->toBe('https://secure.euplatesc.ro/tdsprocess/tranzactd.php')
        ->and($redirect->fields)->toHaveKeys(['amount', 'curr', 'invoice_id', 'order_desc', 'merch_id', 'timestamp', 'nonce', 'fp_hash', 'fname', 'email', 'lang'])
        ->and($redirect->fields)->not->toHaveKey('ExtraData[silenturl]')
        ->and(Payment::query()->sole()->provider_payment_id)->toBeNull();
});

it('sends the return and silent URLs per transaction when enabled', function (): void {
    Config::set('euplatesc.send_urls', true);

    expect(Payments::gateway('euplatesc')->createPayment(paymentRequest())->fields)
        ->toMatchArray(['ExtraData[silenturl]' => 'https://shop.example/webhooks/payments/euplatesc', 'ExtraData[successurl]' => 'https://shop.example/plata/euplatesc/return']);
});

it('has no status API', function (): void {
    Payments::gateway('euplatesc')->status('EP0001');
})->throws(UnsupportedOperationException::class);
