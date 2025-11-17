<?php

return [
    // Mailjet Configuration (kept in params.php for security)
    'mailjet' => [
        'apiKey' => '4acb3a75131a9848eae60784b3e68f72', // Replace with your Mailjet API key
        'secretKey' => '374844a3bf2db6d23e73b6235cab9b1d', // Replace with your Mailjet Secret key
    ],

    // Tax Configuration (kept in params.php for complex nested structure)
    'taxConfigs' => [
        2024 => [
            'yearlyTaxRelief' => 0,
            'taxRate' => 0
        ],
        2025 => [
            'yearlyTaxRelief' => 1800000,
            'taxRate' => 15
        ],
    ],
    'defaultTaxRate' => 15, // Default tax rate percentage

    // Bootstrap Version
    'bsVersion' => '5.x',

    // Signature Details (kept in params.php as it contains file path)
    'signature' => [
        'image' => '@app/assets/sign45678.png',
        'name' => 'Chanaka Karunarathne',
        'title' => 'Authorized Signature'
    ],

    // Note: Other configurations have been moved to the database table 'system_config'
    // Use SystemConfig::get('config_key') to retrieve values
    // Or SystemConfig::getBusinessAddress() and SystemConfig::getBankingDetails() for grouped data
];
