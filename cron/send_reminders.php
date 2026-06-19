<?php
/**
 * Reminder cron — emails members about sessions happening on a target date.
 *
 * Usage:
 *   php cron/send_reminders.php          # sessions today
 *   php cron/send_reminders.php 1        # sessions tomorrow (1 day ahead)
 *
 * Only sessions whose reminder hasn't been sent yet are processed, so it is
 * safe to run repeatedly. Suggested crontab (every day at 07:00):
 *   0 7 * * * php /var/www/html/attendance-list/cron/send_reminders.php >> /var/www/html/attendance-list/logs/reminders.log 2>&1
 */

if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit("This script can only be run from the command line.\n");
}

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/AttendanceSession.php';
require_once __DIR__ . '/../models/Member.php';
require_once __DIR__ . '/../helpers/Notifications.php';

$daysAhead = isset($argv[1]) ? max(0, (int)$argv[1]) : 0;
$targetDate = date('Y-m-d', strtotime("+{$daysAhead} day"));
$stamp = '[' . date('Y-m-d H:i:s') . ']';

echo "$stamp Reminder run for sessions on {$targetDate}\n";

if (!Mailer::isConfigured()) {
    echo "$stamp ABORT: email not configured (set SMTP password in config/mail.local.php).\n";
    exit(1);
}

$sessionModel = new AttendanceSession();
$memberModel  = new Member();

$sessions = $sessionModel->getByDate($targetDate);
if (!$sessions) {
    echo "$stamp No sessions on {$targetDate}. Nothing to do.\n";
    exit(0);
}

$members = $memberModel->getAll();
$totalSent = 0;

foreach ($sessions as $session) {
    if (!empty($session['reminder_sent_at'])) {
        echo "$stamp - \"{$session['session_name']}\" already reminded at {$session['reminder_sent_at']}, skipping.\n";
        continue;
    }
    $r = Notifications::sendSessionReminders($session, $members);
    $sessionModel->markReminderSent($session['id']);
    $totalSent += $r['sent'];
    echo "$stamp - \"{$session['session_name']}\": sent={$r['sent']} failed={$r['failed']} skipped={$r['skipped']}\n";
}

echo "$stamp Done. Total emails sent: {$totalSent}.\n";
exit(0);
