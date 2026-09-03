<?php

namespace Tests\Feature;

use App\Mail\ContactFormMail;
use App\Mail\ErrorMail;
use App\Mail\TestMail;
use Tests\TestCase;

class EmailDesignTest extends TestCase
{
    /**
     * Testuje, zda e-maily obsahují základní prvky layoutu a dodržují technická pravidla.
     */
    public function test_emails_contain_base_layout_elements()
    {
        config(['email_branding.brand_name' => 'Kbelští sokoli']);

        $mailables = [
            new TestMail('Testovací zpráva'),
            new ContactFormMail('Jan', 'jan@test.cz', 'admin@test.cz', 'Zpráva', 'Předmět'),
            new ErrorMail(['exception' => ['class' => 'Error', 'message' => 'Fail']]),
        ];

        foreach ($mailables as $mailable) {
            $html = $mailable->render();

            // Branding a fallbacky
            $this->assertStringContainsString('Kbelští sokoli', $html, 'E-mail '.get_class($mailable).' neobsahuje název klubu.');
            $this->assertStringContainsString('email-logo.png', $html, 'E-mail '.get_class($mailable).' neobsahuje logo.');
            $this->assertStringContainsString('alt="Sokoli"', $html, 'E-mail '.get_class($mailable).' neobsahuje správný ALT u loga.');

            // Kompatibilita
            $this->assertStringContainsString('Arial, Helvetica, sans-serif', $html, 'E-mail '.get_class($mailable).' nepoužívá bezpečné fonty.');
            $this->assertStringContainsString('width="600"', $html, 'E-mail '.get_class($mailable).' nepoužívá fixní šířku 600px.');

            // Zákaz moderního CSS (flex, grid)
            $this->assertStringNotContainsString('display: flex', $html, 'E-mail '.get_class($mailable)." obsahuje 'display: flex', což není doporučeno.");
            $this->assertStringNotContainsString('display: grid', $html, 'E-mail '.get_class($mailable)." obsahuje 'display: grid', což není doporučeno.");
        }
    }
}
