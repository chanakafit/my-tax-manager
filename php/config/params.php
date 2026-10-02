<?php

return [
    // Mail is configured with the MAIL_DSN environment variable (.env.prod /
    // .env.local), read in config/web.php and config/console.php. Never keep
    // provider credentials in this file - it is committed.

    // Tax Configuration (kept in params.php for complex nested structure)
    // Keyed by Sri Lankan year of assessment (2025 = 2025/2026, 1 Apr 2025 - 31 Mar 2026).
    // A year that is not listed inherits the most recent listed year, so no new entry
    // is needed until the IRD changes the rates - see TaxConfig::getYearConfig().
    //
    // 'taxRate' is the MAXIMUM rate, not a flat rate: First Schedule paragraph 1(6)
    // of the Inland Revenue Act (added by Amendment Act No. 2 of 2025) taxes an
    // individual's service-export / foreign-source income received in foreign
    // currency and remitted through a bank at "the maximum rate of 15%". Where the
    // normal progressive rates in 'taxBrackets' give less, the lower amount applies.
    'taxConfigs' => [
        2024 => [
            // Service exports / foreign source income were exempt (Third Schedule (u))
            'yearlyTaxRelief' => 0,
            'taxRate' => 0,
            'taxBrackets' => [],
        ],
        2025 => [
            // Personal relief, Fifth Schedule para 2(a)(v) - IRD notice PN/IT/2025-01
            'yearlyTaxRelief' => 1800000,
            'taxRate' => 15,
            // Normal progressive rates for individuals from 01.04.2025.
            // 'width' is the width of the band; null means "balance".
            'taxBrackets' => [
                ['width' => 1000000, 'rate' => 6],
                ['width' => 500000, 'rate' => 18],
                ['width' => 500000, 'rate' => 24],
                ['width' => 500000, 'rate' => 30],
                ['width' => null, 'rate' => 36],
            ],
        ],
    ],
    'defaultTaxRate' => 15, // Default tax rate percentage

    // Bootstrap Version
    'bsVersion' => '5.x',

    // Note: Other configurations have been moved to the database table 'system_config'
    // Use SystemConfig::get('config_key') to retrieve values
    // Or SystemConfig::getBusinessAddress() and SystemConfig::getBankingDetails() for grouped data
];
