<?php

return [
    'electricity_jan' => [
        'expense_category_id' => 1, // utilities
        'vendor_id' => 2, // electricity_board
        'expense_date' => '2025-01-15',
        'title' => 'Monthly Electricity Bill - January',
        'description' => 'Office electricity consumption',
        'amount' => 25000.00,
        'currency_code' => 'LKR',
        'exchange_rate' => 1.00,
        'payment_method' => 'bank_transfer',
        'status' => 'approved',
        'is_recurring' => 1,
        'created_at' => 1700000000,
        'updated_at' => 1700000000,
        'created_by' => 1,
        'updated_by' => 1,
    ],
    'rent_jan' => [
        'expense_category_id' => 2, // rent
        'vendor_id' => 4, // rent_landlord
        'expense_date' => '2025-01-01',
        'title' => 'Office Rent - January',
        'description' => 'Monthly office rent payment',
        'amount' => 150000.00,
        'currency_code' => 'LKR',
        'exchange_rate' => 1.00,
        'payment_method' => 'bank_transfer',
        'status' => 'approved',
        'is_recurring' => 1,
        'created_at' => 1700000000,
        'updated_at' => 1700000000,
        'created_by' => 1,
        'updated_by' => 1,
    ],
    'office_supplies_oct' => [
        'expense_category_id' => 3, // office_supplies
        'vendor_id' => 1, // office_supplies_co
        'expense_date' => '2025-10-15',
        'title' => 'Printer Paper and Toner',
        'description' => 'Office supplies purchase',
        'amount' => 12500.00,
        'currency_code' => 'LKR',
        'exchange_rate' => 1.00,
        'payment_method' => 'cash',
        'status' => 'approved',
        'is_recurring' => 0,
        'created_at' => 1700000000,
        'updated_at' => 1700000000,
        'created_by' => 1,
        'updated_by' => 1,
    ],
    'telecom_nov' => [
        'expense_category_id' => 4, // telecommunications
        'vendor_id' => 3, // telecom_provider
        'expense_date' => '2025-11-10',
        'title' => 'Internet and Phone Bill - November',
        'description' => 'Monthly telecom charges',
        'amount' => 18000.00,
        'currency_code' => 'LKR',
        'exchange_rate' => 1.00,
        'payment_method' => 'bank_transfer',
        'status' => 'pending',
        'is_recurring' => 1,
        'created_at' => 1700000000,
        'updated_at' => 1700000000,
        'created_by' => 1,
        'updated_by' => 1,
    ],
    'foreign_expense' => [
        'expense_category_id' => 3, // office_supplies
        'vendor_id' => 1, // office_supplies_co
        'expense_date' => '2025-09-20',
        'title' => 'Software License - USD',
        'description' => 'Annual software subscription',
        'amount' => 500.00,
        'currency_code' => 'USD',
        'exchange_rate' => 330.00,
        'payment_method' => 'credit_card',
        'status' => 'approved',
        'is_recurring' => 0,
        'created_at' => 1700000000,
        'updated_at' => 1700000000,
        'created_by' => 1,
        'updated_by' => 1,
    ],
];

