<?php

namespace Tests\Feature\Livewire\Member;

use App\Livewire\Member\PaymentWidget;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class PaymentWidgetTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_regenerates_qr_code_on_input_changes(): void
    {
        $user = User::factory()->create([
            'name' => 'Jan Novák',
            'payment_vs' => '20240001',
        ]);

        $this->actingAs($user);

        $component = Livewire::test(PaymentWidget::class);

        $initialQrCode = $component->get('qrCodeDataUri');
        $this->assertNotEmpty($initialQrCode);

        // Změna poznámky
        $component->set('note', 'Nová poznámka')
            ->assertSet('note', 'Nová poznámka');

        $afterNoteQrCode = $component->get('qrCodeDataUri');
        $this->assertNotEmpty($afterNoteQrCode, 'QR kód po změně poznámky nesmí být prázdný');
        $this->assertNotEquals($initialQrCode, $afterNoteQrCode);

        // Změna SS
        $component->set('ss', '1234')
            ->assertSet('ss', '1234');

        $afterSsQrCode = $component->get('qrCodeDataUri');
        $this->assertNotEmpty($afterSsQrCode, 'QR kód po změně SS nesmí být prázdný');
        $this->assertNotEquals($afterNoteQrCode, $afterSsQrCode);

        // Změna částky
        $component->set('amount', '500')
            ->assertSet('amount', '500');

        $afterAmountQrCode = $component->get('qrCodeDataUri');
        $this->assertNotEmpty($afterAmountQrCode, 'QR kód po změně částky nesmí být prázdný');
        $this->assertNotEquals($afterSsQrCode, $afterAmountQrCode);
    }

    public function test_it_handles_different_amount_formats(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $component = Livewire::test(PaymentWidget::class);

        // Čárka jako oddělovač
        $component->set('amount', '100,50');
        $qrWithComma = $component->get('qrCodeDataUri');

        // Tečka jako oddělovač
        $component->set('amount', '100.50');
        $qrWithDot = $component->get('qrCodeDataUri');

        $this->assertEquals($qrWithComma, $qrWithDot);
    }
}
