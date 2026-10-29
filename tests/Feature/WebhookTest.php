<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Event;
use Illuminate\Testing\TestResponse;
use WeBRTeu\EuPlatesc\Facades\EuPlatesc;
use WeBRTeu\Payments\Enums\PaymentStatus;
use WeBRTeu\Payments\Events\PaymentStatusChanged;
use WeBRTeu\Payments\Models\Payment;
use WeBRTeu\Payments\Models\PaymentEvent;

function postSilent(array $fields): TestResponse
{
    return test()->call('POST', '/webhooks/payments/euplatesc', [], [], [], ['CONTENT_TYPE' => 'application/x-www-form-urlencoded'], http_build_query($fields));
}

beforeEach(function (): void {
    Event::fake([PaymentStatusChanged::class]);
    Payment::query()->create(['gateway' => 'euplatesc', 'order_id' => '123-1', 'amount' => 14170, 'status' => PaymentStatus::Pending]);
});

it('marks action=0 as Paid', function (): void {
    postSilent(silentFields())->assertOk()->assertContent('OK');

    expect(Payment::query()->sole())->status->toBe(PaymentStatus::Paid)->provider_payment_id->toBe('EP0001');
});

it('marks any other action as Failed and keeps the message', function (): void {
    postSilent(silentFields(['action' => '1', 'message' => 'Card expirat', 'ep_id' => 'EP0002']))->assertOk();

    expect(Payment::query()->sole())->status->toBe(PaymentStatus::Failed)
        ->raw->toMatchArray(['message' => 'Card expirat']);
});

it('processes a duplicate (ep_id + action) only once', function (): void {
    postSilent(silentFields())->assertOk();
    postSilent(silentFields())->assertOk();

    expect(PaymentEvent::query()->count())->toBe(1);
    Event::assertDispatchedTimes(PaymentStatusChanged::class, 1);
});

it('rejects a tampered amount', function (): void {
    postSilent(['amount' => '1.00'] + silentFields())->assertStatus(400)->assertContent('INVALID');

    expect(Payment::query()->sole()->status)->toBe(PaymentStatus::Pending);
});

it('verifies the return page for display only', function (): void {
    expect(EuPlatesc::verifyReturn(silentFields(), 14170))->toBeTrue()
        ->and(EuPlatesc::verifyReturn(silentFields(['action' => '2'])))->toBeFalse();
});
