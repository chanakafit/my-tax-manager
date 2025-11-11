<?php
// Auto-generated mail configuration
$smtpHost = getenv('SMTP_HOST') ?: '';
$smtpPort = getenv('SMTP_PORT') ?: '587';
$smtpUser = getenv('SMTP_USER') ?: '';
$smtpPass = getenv('SMTP_PASS') ?: '';

return [
    'smtp' => [
        'dsn' => $smtpHost && $smtpUser
            ? sprintf('smtp://%s:%s@%s:%s', $smtpUser, $smtpPass, $smtpHost, $smtpPort)
            : 'native://default'  // Use native mail if no SMTP configured
    ],
];
