<?php

use App\Enums\SmsPurpose;
use App\Models\Customer;
use App\Models\Debt;
use App\Models\Shop;
use App\Models\SmsMessage;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;

beforeEach(fn () => fakeUsms());

it('reminds customers whose debt is due today, once', function () {
    $shop = Shop::factory()->create(['name' => 'Chilonzor Market']);
    $customer = Customer::factory()->for($shop)->create(['name' => 'Aziza', 'phone' => '998935554433']);
    $debt = Debt::factory()->for($customer)->dueToday()->create(['shop_id' => $shop->id, 'amount' => 150_000, 'paid_amount' => 50_000]);

    $this->artisan('debts:remind')->assertSuccessful();
    $this->artisan('debts:remind')->assertSuccessful();

    Http::assertSentCount(1);
    Http::assertSent(fn (Request $request): bool => $request['phone'] === '998935554433'
        && $request['template_id'] === 44
        && $request['params'] === ['name' => 'Aziza', 'shop' => 'Chilonzor Market', 'amount' => '100 000']);
    expect(SmsMessage::query()->sole()->purpose)->toBe(SmsPurpose::DebtReminder)
        ->and($debt->fresh()->reminded_at)->not->toBeNull();
});

it('skips paid debts, other days and shops without the debt book', function () {
    $shop = Shop::factory()->create();
    Debt::factory()->for(Customer::factory()->for($shop))->dueToday()->paid()->create(['shop_id' => $shop->id]);
    Debt::factory()->for(Customer::factory()->for($shop))->create(['shop_id' => $shop->id]);

    $basic = Shop::factory()->subscribed('business')->create();
    Debt::factory()->for(Customer::factory()->for($basic))->dueToday()->create(['shop_id' => $basic->id]);

    $expired = Shop::factory()->subscriptionExpired()->create();
    Debt::factory()->for(Customer::factory()->for($expired))->dueToday()->create(['shop_id' => $expired->id]);

    $this->artisan('debts:remind')->assertSuccessful();

    Http::assertNothingSent();
});
