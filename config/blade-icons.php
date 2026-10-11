<?php

return [
    'components' => [
        // Resolve SVGs on demand. Registering every Font Awesome Pro alias scans
        // tens of thousands of files at each boot when the manifest is absent.
        'disabled' => true,
        'default' => 'icon',
    ],
];
