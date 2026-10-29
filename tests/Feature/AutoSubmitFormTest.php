<?php

declare(strict_types=1);

use WeBRTeu\Payments\Facades\Payments;

it('renders the auto-submit form with every field, escaped', function (): void {
    $redirect = Payments::gateway('euplatesc')->createPayment(paymentRequest(description: 'Comanda "123" <b>'));
    $html = (string) $redirect->toResponse()->getContent();

    expect($html)->toContain('action="https://secure.euplatesc.ro/tdsprocess/tranzactd.php"')
        ->toContain('name="fp_hash" value="'.$redirect->fields['fp_hash'].'"')
        ->toContain('name="order_desc" value="Comanda &quot;123&quot; &lt;b&gt;"')
        ->toContain('Continuă spre plată')
        ->not->toContain('<b>"');
});
