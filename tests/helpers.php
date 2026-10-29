<?php

declare(strict_types=1);

use WeBRTeu\EuPlatesc\ResponseVerifier;
use WeBRTeu\Payments\Data\CustomerData;
use WeBRTeu\Payments\Data\PaymentRequestData;
use WeBRTeu\Payments\Support\LengthPrefixedHmac;

const EP_KEY = '00112233445566778899AABBCCDDEEFF';
const EP_MERCHANT = '44840981287';

function paymentRequest(string $orderId = '123-1', int $amount = 14170, string $description = 'Comanda 123'): PaymentRequestData
{
    return new PaymentRequestData(
        orderId: $orderId,
        amount: $amount,
        description: $description,
        customer: new CustomerData('ion@example.com', 'Ion', 'Popescu', '0721000000', 'Brașov', address: 'Str. Lungă 10'),
        returnUrl: 'https://shop.example/plata/euplatesc/return',
    );
}

/**
 * Corpul unui Silent URL semnat corect.
 *
 * @param  array<string, string>  $overrides
 * @return array<string, string>
 */
function silentFields(array $overrides = [], bool $sign = true): array
{
    $fields = $overrides + [
        'amount' => '141.70',
        'curr' => 'RON',
        'invoice_id' => '123-1',
        'ep_id' => 'EP0001',
        'merch_id' => EP_MERCHANT,
        'action' => '0',
        'message' => 'Approved',
        'approval' => '123456',
        'timestamp' => '20261028100000',
        'nonce' => 'abcdef0123456789',
    ];

    if ($sign) {
        $fields['fp_hash'] = LengthPrefixedHmac::sign(array_map(fn (string $key): string => $fields[$key] ?? '', ResponseVerifier::SIGNED), EP_KEY, 'md5');
    }

    return $fields;
}
