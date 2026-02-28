<?php

namespace Tests\Unit\Livewire\Member;

use Tests\TestCase;
use App\Livewire\Member\PaymentWidget;
use ReflectionMethod;

class PaymentWidgetTest extends TestCase
{
    /**
     * Test převodu českého čísla účtu na IBAN.
     */
    public function test_it_converts_cz_account_to_iban(): void
    {
        $widget = new PaymentWidget();
        $method = new ReflectionMethod(PaymentWidget::class, 'convertToIban');
        $method->setAccessible(true);

        // Standardní účet bez předčíslí
        // 6022854477/6363
        // Air Bank 6363
        // Výsledek by měl být validní IBAN CZ61 6363 0000 0060 2285 4477
        $iban = $method->invoke($widget, '6022854477/6363');

        $this->assertNotNull($iban);
        $this->assertStringStartsWith('CZ', $iban);
        $this->assertEquals(24, strlen($iban));
        $this->assertEquals('CZ6163630000006022854477', $iban);

        // Účet s předčíslím
        // 123-456789/0100 -> CZ94 0100 0001 2300 0045 6789
        $ibanWithPrefix = $method->invoke($widget, '123-456789/0100');
        $this->assertNotNull($ibanWithPrefix);
        $this->assertEquals('CZ9401000001230000456789', $ibanWithPrefix);
    }

    /**
     * Test sanityzace zprávy.
     */
    public function test_it_sanitizes_spayd_message(): void
    {
        $widget = new PaymentWidget();
        $method = new ReflectionMethod(PaymentWidget::class, 'sanitizeMessage');
        $method->setAccessible(true);

        $dirty = 'Příliš žluťoučký kůň úpěl ďábelské ódy! @#$%^&*()';
        $clean = $method->invoke($widget, $dirty);

        $this->assertEquals('Prilis zlutoucky kun upel dabelske ody', $clean);
    }
}
