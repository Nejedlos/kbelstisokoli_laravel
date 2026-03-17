<?php

return [
    'title' => 'Nastavení reCAPTCHA',
    'navigation' => 'reCAPTCHA',
    'sections' => [
        'general' => 'Obecné nastavení reCAPTCHA v3',
        'general_desc' => 'Zde nastavte klíče a parametry pro ochranu formulářů proti spamu.',
    ],
    'fields' => [
        'site_key' => 'Site Key',
        'site_key_help' => 'Veřejný klíč (Site Key) pro frontend.',
        'secret_key' => 'Secret Key',
        'secret_key_help' => 'Tajný klíč (Secret Key) pro komunikaci se serverem Google.',
        'threshold' => 'Práh citlivosti (Threshold)',
        'threshold_help' => 'Práh citlivosti (0.0 až 1.0). Čím vyšší číslo, tím přísnější kontrola (0.5 je standard).',
        'enabled' => 'Aktivovat ochranu',
        'enabled_help' => 'Pokud je vypnuto, formuláře nebudou vyžadovat ověření.',
    ],
    'help' => [
        'title' => 'Nápověda k reCAPTCHA v3',
        'description' => 'Jak získat klíče a nastavit ochranu',
        'content' => '
            <div class="prose dark:prose-invert max-w-none text-sm">
                <p>Tento web používá <strong>Google reCAPTCHA v3</strong>. Tato verze nevyžaduje od uživatelů klikání na obrázky, ale vrací skóre na základě jejich chování.</p>
                <ol>
                    <li>Přejděte na <a href="https://www.google.com/recaptcha/admin" target="_blank" class="text-primary underline">Google reCAPTCHA Admin Console</a>.</li>
                    <li>Vytvořte nový web typu <strong>v3</strong>.</li>
                    <li>Přidejte doménu (např. <code>kbelstisokoli.cz</code>).</li>
                    <li>Zkopírujte klíče a vložte je výše.</li>
                </ol>
                <div class="bg-primary/5 p-4 rounded-xl border border-primary/10 mt-4">
                    <p class="font-bold text-primary mb-1 italic">Klíče pro tento projekt:</p>
                    <code class="block bg-white p-2 rounded border mb-2">Site key: 6LfRn3csAAAAAKPzWb8wMPDrP8k9qRNbh6ZA6E_I</code>
                    <code class="block bg-white p-2 rounded border">Secret key: 6LfRn3csAAAAAH7X7gs09H8TJ8VCTX7lCDJLvldN</code>
                </div>
            </div>
        ',
    ],
    'notifications' => [
        'saved' => 'Nastavení reCAPTCHA bylo úspěšně uloženo.',
        'error' => 'Při ukládání nastavení došlo k chybě.',
    ],
    'actions' => [
        'save' => 'Uložit nastavení',
    ],
];
