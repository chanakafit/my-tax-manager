<?php

return [
    'adminEmail' => 'admin@example.com',
    'senderEmail' => 'hello@chanakalk.com',
    'senderName' => 'Chanaka IT Services',

    // Business Details
    'businessName' => 'CHANAKA IT SERVICES',
    'businessAddress' => [
        'line1' => 'Kandegedara, Weerasinghe Rd',
        'line2' => 'Puwakdandawa',
        'city' => 'Beliatta',
        'postalCode' => '82400',
        'province' => 'Southern Province',
    ],

    // System configuration
    'payrollExpenseCategoryId' => 4,

    //mailjet confgs
    'mailjet' => [
        'apiKey' => '4acb3a75131a9848eae60784b3e68f72', // Replace with your Mailjet API key
        'secretKey' => '374844a3bf2db6d23e73b6235cab9b1d', // Replace with your Mailjet Secret key
    ],

    // Banking Details
    'bankingDetails' => [
        'swiftCode' => 'NTBCLKLX',  // Replace with actual swift code
        'bankName' => 'NATIONS TRUST BANK PLC',
        'branchName' => 'PILIYANDALA',
        'bankCode' => '7162',
        'branchCode' => '040',
        'bankAddress' => 'NATIONS TRUST BANK, NO. 30 MORATUWA - PILIYANDALA RD, PILIYANDALA 10300, SRI LANKA',
        'accountName' => 'CHANAKA IT SERVICES',
        'accountNumber' => '270400001394',
    ],

    // Financial system parameters
    'yearlyTaxRelief' => 1800000, // Annual tax relief amount
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
    'defaultBankAccountId' => 1, // Default bank account for transactions
    'invoicePrefix' => 'INV', // Prefix for invoice numbers
    'dateFormat' => 'php:Y-m-d',
    'datetimeFormat' => 'php:Y-m-d H:i:s',
    'currencyCode' => 'USD',
    'decimalPlaces' => 2,

    // Payment methods
    'paymentMethods' => [
        'cash' => 'Cash',
        'bank_transfer' => 'Bank Transfer',
        'check' => 'Check',
        'credit_card' => 'Credit Card',
    ],

    // Invoice statuses
    'invoiceStatuses' => [
        'pending' => 'Pending',
        'paid' => 'Paid',
        'overdue' => 'Overdue',
        'cancelled' => 'Cancelled',
    ],

    // Employee departments
    'departments' => [
        'management' => 'Management',
        'finance' => 'Finance',
        'sales' => 'Sales',
        'operations' => 'Operations',
        'hr' => 'Human Resources',
        'it' => 'Information Technology',
        'marketing' => 'Marketing',
        'customer_service' => 'Customer Service',
        'production' => 'Production',
        'logistics' => 'Logistics',
        'legal' => 'Legal',
        'administration' => 'Administration',
    ],
    'bsVersion'                     => '5.x',
    'currencySymbol'                 => 'Rs. ',
    'invoiceNumberStart' => 49, // Starting number for invoice sequence
    'invoiceNumberFormat' => 'INV-%06d', // Format with 6 digits
    'currencies' => [
        'LKR' => 'Sri Lankan Rupee',
        'USD' => 'US Dollar',
        'EUR' => 'Euro',
        'GBP' => 'British Pound',
        'AUD' => 'Australian Dollar',
    ],

    // Bank Names List
    'bankNames' => [
        'Nations Trust Bank',
        'Seylan Bank',
        'National Development Bank',
        'Bank of Ceylon',
        'Commercial Bank',
        'HSBC Bank',
        'Union Bank',
    ],

    // Signature Details
    'signature' => [
        'image' => '@app/assets/sign45678.png',
        'name' => 'Chanaka Karunarathne',
        'title' => 'Authorized Signature'
    ],
];
