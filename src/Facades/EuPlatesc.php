<?php

declare(strict_types=1);

namespace WeBRTeu\EuPlatesc\Facades;

use Illuminate\Support\Facades\Facade;
use WeBRTeu\EuPlatesc\EuPlatescGateway;

/**
 * @method static \WeBRTeu\Payments\Data\PaymentRedirectData createPayment(\WeBRTeu\Payments\Data\PaymentRequestData $request)
 * @method static bool verifyReturn(array<string, mixed> $fields, ?int $expectedAmount = null)
 *
 * @see EuPlatescGateway
 */
class EuPlatesc extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return EuPlatescGateway::class;
    }
}
