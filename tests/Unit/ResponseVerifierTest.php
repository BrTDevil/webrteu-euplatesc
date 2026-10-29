<?php

declare(strict_types=1);

use WeBRTeu\EuPlatesc\ResponseVerifier;
use WeBRTeu\Payments\Exceptions\InvalidSignatureException;
use WeBRTeu\Payments\Support\LengthPrefixedHmac;

it('accepts a valid response', function (): void {
    ResponseVerifier::verify(silentFields(), EP_MERCHANT, EP_KEY, 14170);

    expect(true)->toBeTrue();
});

it('uses a dash for empty fields in the signed string', function (): void {
    $fields = silentFields(['approval' => '']);

    expect(LengthPrefixedHmac::data(array_map(fn (string $key): string => $fields[$key], ResponseVerifier::SIGNED)))->toContain('8Approved-14');

    ResponseVerifier::verify($fields, EP_MERCHANT, EP_KEY);
});

it('rejects tampered or foreign responses', function (array $fields, ?int $amount, string $message): void {
    expect(fn () => ResponseVerifier::verify($fields, EP_MERCHANT, EP_KEY, $amount))->toThrow(InvalidSignatureException::class, $message);
})->with([
    'hash modificat' => [fn (): array => ['fp_hash' => 'ABC'] + silentFields(), null, 'fp_hash'],
    'fără hash' => [fn (): array => silentFields(sign: false), null, 'fp_hash'],
    'sumă modificată' => [fn (): array => ['amount' => '1.00'] + silentFields(), null, 'fp_hash'],
    'merch_id străin' => [fn (): array => silentFields(['merch_id' => '999']), null, 'merch_id'],
    'sumă diferită de comandă' => [fn (): array => silentFields(), 100, 'Suma'],
]);
