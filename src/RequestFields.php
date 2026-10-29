<?php

declare(strict_types=1);

namespace WeBRTeu\EuPlatesc;

use Carbon\CarbonImmutable;
use Illuminate\Support\Str;
use WeBRTeu\Payments\Data\PaymentRequestData;
use WeBRTeu\Payments\Support\LengthPrefixedHmac;
use WeBRTeu\Payments\Support\Money;

/**
 * PaymentRequestData → câmpurile formularului EuPlatesc + fp_hash.
 */
final class RequestFields
{
    /**
     * Ordinea fixă a câmpurilor semnate la cerere.
     */
    public const SIGNED = ['amount', 'curr', 'invoice_id', 'order_desc', 'merch_id', 'timestamp', 'nonce'];

    /**
     * @param  array<string, string>  $extra  câmpuri suplimentare nesemnate (ex. ExtraData[silenturl])
     * @return array<string, string>
     */
    public static function make(PaymentRequestData $request, string $merchantId, string $hexKey, string $lang = 'ro', ?CarbonImmutable $now = null, ?string $nonce = null, array $extra = []): array
    {
        $signed = [
            'amount' => Money::toDecimalString($request->amount),
            'curr' => $request->currency,
            'invoice_id' => $request->orderId,
            // strlen numără octeți: descrierea se trimite fără diacritice, ca lungimea să fie neambiguă.
            'order_desc' => self::description($request->description),
            'merch_id' => $merchantId,
            'timestamp' => ($now ?? CarbonImmutable::now())->utc()->format('YmdHis'),
            'nonce' => $nonce ?? md5(random_bytes(16)),
        ];

        $customer = $request->customer;

        return $signed + [
            'fp_hash' => LengthPrefixedHmac::sign(array_values($signed), $hexKey, 'md5'),
            'fname' => $customer->firstName,
            'lname' => $customer->lastName,
            'email' => $customer->email,
            'phone' => (string) $customer->phone,
            'country' => $customer->countryCode,
            'city' => (string) $customer->city,
            'add' => (string) $customer->address,
            'lang' => $lang,
        ] + $extra;
    }

    public static function description(string $description): string
    {
        return Str::limit(Str::ascii($description), 200, '');
    }
}
