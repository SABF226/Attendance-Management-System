<?php
/**
 * Mail configuration.
 *
 * Secrets (the SMTP password) must NOT live here. Put them in
 * config/mail.local.php (gitignored) or the SMTP_PASS environment variable.
 * See config/mail.local.php.example.
 */

$local = [];
if (file_exists(__DIR__ . '/mail.local.php')) {
    $local = require __DIR__ . '/mail.local.php';
}

return array_merge([
    'enabled'    => true,                       // master switch
    'host'       => 'smtp.gmail.com',
    'port'       => 587,
    'encryption' => 'tls',                      // 'tls' (587) or 'ssl' (465)
    'username'   => 'sabfsanon@gmail.com',
    'password'   => getenv('SMTP_PASS') ?: '',  // Gmail App Password (16 chars)
    'from_email' => 'sabfsanon@gmail.com',
    'from_name'  => 'BIT English Club',
    'reply_to'   => 'sabfsanon@gmail.com',
    // Base URL used to build links inside emails (no trailing slash).
    'app_url'    => 'http://localhost/attendance-list',
], is_array($local) ? $local : []);
