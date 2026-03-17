<?php

return [
    'title' => 'reCAPTCHA Settings',
    'navigation' => 'reCAPTCHA',
    'sections' => [
        'general' => 'General reCAPTCHA v3 Settings',
        'general_desc' => 'Set up keys and parameters for form spam protection.',
    ],
    'fields' => [
        'site_key' => 'Site Key',
        'site_key_help' => 'Public key (Site Key) for the frontend.',
        'secret_key' => 'Secret Key',
        'secret_key_help' => 'Secret key (Secret Key) for communication with Google servers.',
        'threshold' => 'Threshold',
        'threshold_help' => 'Threshold (0.0 to 1.0). The higher the number, the stricter the check (0.5 is standard).',
        'enabled' => 'Enable Protection',
        'enabled_help' => 'If disabled, forms will not require verification.',
    ],
    'help' => [
        'title' => 'reCAPTCHA v3 Help',
        'description' => 'How to obtain keys and set up protection',
        'content' => '
            <div class="prose dark:prose-invert max-w-none text-sm">
                <p>This website uses <strong>Google reCAPTCHA v3</strong>. This version does not require users to click on images, but returns a score based on their behavior.</p>
                <ol>
                    <li>Go to the <a href="https://www.google.com/recaptcha/admin" target="_blank" class="text-primary underline">Google reCAPTCHA Admin Console</a>.</li>
                    <li>Create a new site of type <strong>v3</strong>.</li>
                    <li>Add the domain (e.g., <code>kbelstisokoli.cz</code>).</li>
                    <li>Copy the keys and paste them above.</li>
                </ol>
                <div class="bg-primary/5 p-4 rounded-xl border border-primary/10 mt-4">
                    <p class="font-bold text-primary mb-1 italic">Keys for this project:</p>
                    <code class="block bg-white p-2 rounded border mb-2">Site key: 6LfRn3csAAAAAKPzWb8wMPDrP8k9qRNbh6ZA6E_I</code>
                    <code class="block bg-white p-2 rounded border">Secret key: 6LfRn3csAAAAAH7X7gs09H8TJ8VCTX7lCDJLvldN</code>
                </div>
            </div>
        ',
    ],
    'notifications' => [
        'saved' => 'reCAPTCHA settings saved successfully.',
        'error' => 'An error occurred while saving settings.',
    ],
    'actions' => [
        'save' => 'Save Settings',
    ],
];
