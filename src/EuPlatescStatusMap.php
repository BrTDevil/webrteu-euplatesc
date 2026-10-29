<?php

declare(strict_types=1);

namespace WeBRTeu\EuPlatesc;

use WeBRTeu\Payments\Enums\PaymentStatus;

final class EuPlatescStatusMap
{
    /**
     * `action = 0` = aprobată; orice altceva = eșuată (motivul e în `message`).
     */
    public static function resolve(string $action): PaymentStatus
    {
        return $action === '0' ? PaymentStatus::Paid : PaymentStatus::Failed;
    }
}
