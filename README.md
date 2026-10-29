# webrteu/euplatesc

[![PHP](https://img.shields.io/badge/PHP-%5E8.2-777bb4)](https://www.php.net/)
[![Laravel](https://img.shields.io/badge/Laravel-12%E2%80%9313-ff2d20)](https://laravel.com/)
[![Licență MIT](https://img.shields.io/badge/licen%C8%9B%C4%83-MIT-green)](LICENSE.md)
[![Packagist](https://img.shields.io/packagist/v/webrteu/euplatesc)](https://packagist.org/packages/webrteu/euplatesc)

Integrare Laravel pentru EuPlatesc.ro (EuroPayment Services): plată pe pagina găzduită EuPlatesc printr-un formular
POST semnat HMAC-MD5 (`fp_hash`), confirmare prin **Silent URL**. Implementează `Gateway` din `webrteu/payments`.

Nu este un pachet oficial EuPlatesc.

## Cerințe

- PHP `^8.2`, Laravel 12–13
- contract EuPlatesc (Merchant ID + cheie secretă hex)
- `webrteu/payments`

## Instalare

```bash
composer require webrteu/euplatesc
php artisan vendor:publish --tag=euplatesc-config
```

## Configurare

```dotenv
EUPLATESC_MERCHANT_ID=
EUPLATESC_SECRET_KEY=        # cheia hex primită de la EuPlatesc
EUPLATESC_CURRENCY=RON
EUPLATESC_LANG=ro
EUPLATESC_SEND_URLS=false    # true = trimite ExtraData[successurl/failedurl/silenturl] per tranzacție ⚠️
```

Contul nou e în mod test până la activare. ⚠️ Nu există un URL de sandbox separat (de confirmat cu EuPlatesc).

## Utilizare

```php
$redirect = Payments::start('euplatesc', new PaymentRequestData(
    orderId: "{$order->id}-{$attempt}",   // invoice_id, unic per încercare
    amount: 14170,                        // trimis ca "141.70"
    description: "Comanda {$order->id}",  // trimisă fără diacritice
    customer: new CustomerData($order->email, $order->first_name, $order->last_name),
    returnUrl: route('checkout.return', $order),
), payable: $order);

return $redirect->toResponse();   // pagină cu <x-payments::auto-submit-form>, trimisă automat
```

sau în propriul view: `<x-payments::auto-submit-form :redirect="$redirect" />`.

Semnătura folosește `LengthPrefixedHmac` din core: câmpurile `amount, curr, invoice_id, order_desc, merch_id, timestamp,
nonce`, în această ordine; `timestamp` = `gmdate('YmdHis')`. Descrierea se trimite fără diacritice, pentru că `strlen`
numără octeți.

Pagina de return, **doar pentru afișare**: `EuPlatesc::verifyReturn($request->post(), $payment->amount)`.

## Webhook (Silent URL)

- în panoul EuPlatesc, la **Silent URL**: `https://site.ro/webhooks/payments/euplatesc`
- verificare: `fp_hash` pe `amount, curr, invoice_id, ep_id, merch_id, action, message, approval, timestamp, nonce`
  ⚠️ (ordinea se confirmă cu documentația de la contract), apoi `merch_id`, iar suma e verificată de core
- idempotență: `ep_id` + `action`
- răspuns: HTTP 200 `OK` ⚠️

| `action` | `PaymentStatus` |
|---|---|
| `0` | `Paid` |
| altceva | `Failed` (motivul în `raw.message`) |

## Comenzi Artisan

`payments:reconcile` **nu** funcționează pentru EuPlatesc: fără API de operații, `status()` aruncă
`UnsupportedOperationException`. Sursa adevărului rămâne Silent URL-ul.

## Ce nu face pachetul

- nu implementează API-ul de operații (captură, anulare, refund, status) ⚠️ disponibil doar pe anumite conturi
- nu marchează comanda plătită din pagina de return

## Testare

```bash
composer test
EUPLATESC_SANDBOX_MERCHANT_ID=... EUPLATESC_SANDBOX_SECRET_KEY=... vendor/bin/pest --group=sandbox
```

Vectorul `fp_hash` din teste e calculat cu implementarea din core. **De înlocuit cu exemplul oficial EuPlatesc**.

## Changelog

Vezi [CHANGELOG.md](CHANGELOG.md).

## Autor

[WeBRTeu](https://webrt.eu)

## Licență

MIT. Vezi [LICENSE.md](LICENSE.md).
