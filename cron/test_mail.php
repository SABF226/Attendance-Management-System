<?php
/**
 * One-off SMTP test. Sends a single reminder-style email so you can confirm
 * your Gmail App Password / config works before emailing real members.
 *
 * Usage:  php cron/test_mail.php you@example.com
 */

if (PHP_SAPI !== 'cli') { exit("CLI only.\n"); }

require_once __DIR__ . '/../helpers/Notifications.php';

$to = $argv[1] ?? '';
if (!filter_var($to, FILTER_VALIDATE_EMAIL)) {
    exit("Usage: php cron/test_mail.php you@example.com\n");
}

if (!Mailer::isConfigured()) {
    exit("Email not configured. Put your SMTP password in config/mail.local.php first.\n");
}

$sample = [
    'id'           => 0,
    'session_name' => 'Test Session — please ignore',
    'session_date' => date('Y-m-d'),
    'session_time' => date('H:i:s'),
    'session_team' => 'Test',
];

echo "Sending test email to {$to} ...\n";
$ok = Mailer::send(
    $to,
    'Test Recipient',
    'BIT English Club — SMTP test',
    Notifications::sessionReminderHtml($sample, 'Test Recipient'),
    Notifications::sessionReminderText($sample, 'Test Recipient')
);
echo $ok ? "OK ✓ — check the inbox (and spam).\n" : "FAILED ✗ — see the PHP error log for details.\n";
exit($ok ? 0 : 1);
