<?php
/**
 * Mailer — thin wrapper around PHPMailer with SMTP settings from config/mail.php.
 */

require_once __DIR__ . '/../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class Mailer {

    /** Load (and cache) the mail config array. */
    private static function config() {
        static $cfg = null;
        if ($cfg === null) {
            $cfg = require __DIR__ . '/../config/mail.php';
        }
        return $cfg;
    }

    /** Is mail configured and enabled (password present)? */
    public static function isConfigured() {
        $cfg = self::config();
        return !empty($cfg['enabled']) && !empty($cfg['password']);
    }

    public static function appUrl() {
        $cfg = self::config();
        return rtrim($cfg['app_url'] ?? '', '/');
    }

    /**
     * Send one HTML email. Returns true on success, false on failure
     * (failures are logged, never thrown, so bulk sends keep going).
     */
    public static function send($toEmail, $toName, $subject, $htmlBody, $textBody = '') {
        if (!self::isConfigured()) {
            error_log('Mailer: not configured (missing SMTP password) — skipping send to ' . $toEmail);
            return false;
        }
        if (!filter_var($toEmail, FILTER_VALIDATE_EMAIL)) {
            error_log('Mailer: invalid recipient skipped: ' . $toEmail);
            return false;
        }

        $cfg = self::config();
        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host       = $cfg['host'];
            $mail->SMTPAuth   = true;
            $mail->Username   = $cfg['username'];
            $mail->Password   = $cfg['password'];
            $mail->SMTPSecure = ($cfg['encryption'] === 'ssl')
                ? PHPMailer::ENCRYPTION_SMTPS
                : PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = (int)$cfg['port'];
            $mail->CharSet    = 'UTF-8';

            $mail->setFrom($cfg['from_email'], $cfg['from_name']);
            if (!empty($cfg['reply_to'])) {
                $mail->addReplyTo($cfg['reply_to'], $cfg['from_name']);
            }
            $mail->addAddress($toEmail, $toName ?: $toEmail);

            $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->Body    = $htmlBody;
            $mail->AltBody = $textBody !== '' ? $textBody : trim(strip_tags($htmlBody));

            $mail->send();
            return true;
        } catch (Exception $e) {
            error_log('Mailer error to ' . $toEmail . ': ' . $mail->ErrorInfo);
            return false;
        }
    }
}
