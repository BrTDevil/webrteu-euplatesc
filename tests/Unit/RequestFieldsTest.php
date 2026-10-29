<?php

declare(strict_types=1);

use Carbon\CarbonImmutable;
use WeBRTeu\EuPlatesc\RequestFields;

it('signs the fields in the fixed order with amounts in lei and a UTC timestamp', function (): void {
    $fields = RequestFields::make(paymentRequest(), EP_MERCHANT, EP_KEY, 'ro', CarbonImmutable::parse('2026-10-28 12:00:00', 'Europe/Bucharest'), 'abcdef0123456789');

    expect(array_slice(array_keys($fields), 0, 8))->toBe(['amount', 'curr', 'invoice_id', 'order_desc', 'merch_id', 'timestamp', 'nonce', 'fp_hash'])
        ->and($fields['amount'])->toBe('141.70')
        ->and($fields['timestamp'])->toBe('20261028100000')
        // ⚠️ vector fix calculat cu LengthPrefixedHmac; de înlocuit cu exemplul oficial EuPlatesc.
        ->and($fields['fp_hash'])->toBe(strtoupper(hash_hmac('md5', '6141.703RON5123-111Comanda 1231144840981287142026102810000016abcdef0123456789', (string) hex2bin(EP_KEY))))
        ->and($fields)->toMatchArray(['fname' => 'Ion', 'lname' => 'Popescu', 'email' => 'ion@example.com', 'country' => 'RO', 'city' => 'Brașov', 'add' => 'Str. Lungă 10', 'lang' => 'ro']);
});

it('generates a random nonce and keeps extra fields outside the hash', function (): void {
    $first = RequestFields::make(paymentRequest(), EP_MERCHANT, EP_KEY, extra: ['ExtraData[silenturl]' => 'https://x']);
    $second = RequestFields::make(paymentRequest(), EP_MERCHANT, EP_KEY);

    expect($first['nonce'])->toHaveLength(32)->not->toBe($second['nonce'])
        ->and($first['ExtraData[silenturl]'])->toBe('https://x');
});
