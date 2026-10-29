<?php

declare(strict_types=1);

use WeBRTeu\EuPlatesc\RequestFields;

it('sends order_desc without diacritics, so byte length equals character length', function (): void {
    $fields = RequestFields::make(paymentRequest(description: 'Comandă țesături Brașov'), EP_MERCHANT, EP_KEY);

    expect($fields['order_desc'])->toBe('Comanda tesaturi Brasov')
        ->and(strlen($fields['order_desc']))->toBe(mb_strlen($fields['order_desc']))
        ->and(strlen(RequestFields::description(str_repeat('a', 300))))->toBe(200);
});
