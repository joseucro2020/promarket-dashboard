<?php

namespace Tests\Unit;

use App\Http\Controllers\PurchaseController;
use PHPUnit\Framework\TestCase;
use ReflectionMethod;

class PurchaseControllerTipTest extends TestCase
{
    public function test_resolve_tip_amount_uses_tip_money_with_currency_conversion()
    {
        $controller = new PurchaseController();
        $method = new ReflectionMethod(PurchaseController::class, 'resolveTipAmount');
        $method->setAccessible(true);

        // Same currency (coin=2 USD, currency=2 USD)
        $purchaseUsd = (object) [
            'tip_money' => 5.5,
            'currency' => '2',
            'details' => [(object) ['coin' => '2']],
            'exchange' => (object) ['change' => 50],
        ];
        $this->assertEqualsWithDelta(5.5, $method->invoke($controller, $purchaseUsd), 0.0001);

        // Bs to USD (coin=1 Bs, currency=2 USD, rate=50)
        $purchaseBsToUsd = (object) [
            'tip_money' => 250,
            'currency' => '2',
            'details' => [(object) ['coin' => '1']],
            'exchange' => (object) ['change' => 50],
        ];
        $this->assertEqualsWithDelta(5.0, $method->invoke($controller, $purchaseBsToUsd), 0.0001);

        // Fallback when tip_money is null
        $purchaseFallback = (object) [
            'tip_money' => null,
            'propina' => 3.25,
        ];
        $this->assertEqualsWithDelta(3.25, $method->invoke($controller, $purchaseFallback), 0.0001);
    }
}

