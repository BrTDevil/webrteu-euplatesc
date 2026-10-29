<?php

declare(strict_types=1);

namespace WeBRTeu\EuPlatesc;

use WeBRTeu\Payments\Exceptions\InvalidSignatureException;
use WeBRTeu\Payments\Support\LengthPrefixedHmac;
use WeBRTeu\Payments\Support\Money;

/**
 * Verifică răspunsul EuPlatesc (Silent URL și pagina de return).
 * ⚠️ Ordinea câmpurilor semnate la răspuns se confirmă cu documentația primită la contract.
 */
final class ResponseVerifier
{
    public const SIGNED = ['amount', 'curr', 'invoice_id', 'ep_id', 'merch_id', 'action', 'message', 'approval', 'timestamp', 'nonce'];

    /**
     * @param  array<string, mixed>  $fields
     * @param  int|null  $expectedAmount  suma comenzii, în bani (null = nu se verifică aici; jobul din core o verifică oricum)
     */
    public static function verify(array $fields, string $merchantId, string $hexKey, ?int $expectedAmount = null): void
    {
        $values = array_map(fn (string $key): string => is_scalar($fields[$key] ?? null) ? (string) $fields[$key] : '', self::SIGNED);
        $received = is_scalar($fields['fp_hash'] ?? null) ? (string) $fields['fp_hash'] : '';

        if ($received === '' || ! LengthPrefixedHmac::verify($values, $hexKey, 'md5', $received)) {
            throw new InvalidSignatureException('fp_hash EuPlatesc nu este valid.');
        }

        if (($fields['merch_id'] ?? null) !== $merchantId) {
            throw new InvalidSignatureException('merch_id nu aparține acestui cont EuPlatesc.');
        }

        if ($expectedAmount !== null && Money::fromDecimal((string) ($fields['amount'] ?? '0')) !== $expectedAmount) {
            throw new InvalidSignatureException('Suma din răspunsul EuPlatesc nu corespunde comenzii.');
        }
    }
}
