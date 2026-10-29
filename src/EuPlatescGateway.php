<?php

declare(strict_types=1);

namespace WeBRTeu\EuPlatesc;

use Illuminate\Http\Request;
use Illuminate\Http\Response as HttpResponse;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\URL;
use Symfony\Component\HttpFoundation\Response;
use WeBRTeu\Payments\Contracts\Gateway;
use WeBRTeu\Payments\Data\PaymentRedirectData;
use WeBRTeu\Payments\Data\PaymentRequestData;
use WeBRTeu\Payments\Data\PaymentStatusData;
use WeBRTeu\Payments\Data\WebhookData;
use WeBRTeu\Payments\Enums\RedirectType;
use WeBRTeu\Payments\Exceptions\UnsupportedOperationException;
use WeBRTeu\Payments\Support\Money;

/**
 * Plată pe pagina găzduită EuPlatesc (formular POST), confirmare prin Silent URL.
 * Fără API de operații: status(), deci și reconcilierea, nu sunt disponibile.
 */
class EuPlatescGateway implements Gateway
{
    public function name(): string
    {
        return 'euplatesc';
    }

    public function createPayment(PaymentRequestData $request): PaymentRedirectData
    {
        $extra = Config::boolean('euplatesc.send_urls', false) ? [
            'ExtraData[successurl]' => $request->returnUrl,
            'ExtraData[failedurl]' => $request->returnUrl,
            'ExtraData[silenturl]' => URL::route('payments.webhook', ['gateway' => 'euplatesc']),
        ] : [];

        return new PaymentRedirectData(
            type: RedirectType::Form,
            url: Config::string('euplatesc.url', 'https://secure.euplatesc.ro/tdsprocess/tranzactd.php'),
            fields: RequestFields::make($request, $this->merchantId(), $this->key(), Config::string('euplatesc.lang', 'ro'), extra: $extra),
        );
    }

    /**
     * Silent URL: POST form-urlencoded, citit din corpul brut.
     */
    public function parseWebhook(Request $request): WebhookData
    {
        $fields = $this->fields($request->getContent());

        ResponseVerifier::verify($fields, $this->merchantId(), $this->key());

        $action = (string) ($fields['action'] ?? '');
        $epId = (string) ($fields['ep_id'] ?? '');

        return new WebhookData(
            eventId: $epId.'-'.$action,
            orderId: (string) ($fields['invoice_id'] ?? ''),
            status: EuPlatescStatusMap::resolve($action),
            providerPaymentId: $epId !== '' ? $epId : null,
            amount: Money::fromDecimal((string) ($fields['amount'] ?? '0')),
            raw: $fields,
        );
    }

    /**
     * ⚠️ Dacă EuPlatesc cere un corp anume pe Silent URL se confirmă din docs; până atunci HTTP 200 „OK”.
     */
    public function webhookResponse(?WebhookData $webhook, bool $accepted): Response
    {
        return new HttpResponse($accepted ? 'OK' : 'INVALID', $accepted ? 200 : 400);
    }

    public function status(string $providerPaymentId): PaymentStatusData
    {
        throw UnsupportedOperationException::for($this->name(), 'status (fără API de operații EuPlatesc)');
    }

    /**
     * Verifică datele primite pe pagina de return. Doar pentru afișare: comanda se marchează plătită din Silent URL.
     *
     * @param  array<string, mixed>  $fields
     */
    public function verifyReturn(array $fields, ?int $expectedAmount = null): bool
    {
        ResponseVerifier::verify($fields, $this->merchantId(), $this->key(), $expectedAmount);

        return ($fields['action'] ?? null) === '0';
    }

    /**
     * @return array<string, string>
     */
    private function fields(string $raw): array
    {
        parse_str($raw, $fields);

        return array_map(fn (mixed $value): string => is_scalar($value) ? (string) $value : '', array_filter($fields, is_string(...), ARRAY_FILTER_USE_KEY));
    }

    private function merchantId(): string
    {
        return (string) Config::get('euplatesc.merchant_id', '');
    }

    private function key(): string
    {
        return Config::string('euplatesc.secret_key', '');
    }
}
