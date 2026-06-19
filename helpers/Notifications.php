<?php
/**
 * Notifications — builds and sends member-facing emails.
 * Currently: session reminders. Uses Mailer for transport.
 */

require_once __DIR__ . '/Mailer.php';

class Notifications {

    /** Pretty date/time line for a session, e.g. "Mon 23 Jun 2026 at 18:00". */
    private static function whenLine($session) {
        $date = !empty($session['session_date']) ? date('D j M Y', strtotime($session['session_date'])) : '';
        $time = !empty($session['session_time']) ? date('H:i', strtotime($session['session_time'])) : '';
        return $time !== '' ? "$date at $time" : $date;
    }

    /** HTML body for a session reminder. */
    public static function sessionReminderHtml($session, $memberName) {
        $name    = htmlspecialchars($memberName ?: 'there', ENT_QUOTES);
        $title   = htmlspecialchars($session['session_name'] ?? 'Club session', ENT_QUOTES);
        $when    = htmlspecialchars(self::whenLine($session), ENT_QUOTES);
        $team    = !empty($session['session_team']) ? htmlspecialchars($session['session_team'], ENT_QUOTES) : '';
        $appUrl  = Mailer::appUrl();
        $scanUrl = $appUrl . '/index.php?page=qr&action=scanner';

        $teamRow = $team !== ''
            ? "<tr><td style=\"padding:4px 0;color:#666;\">Team</td><td style=\"padding:4px 0;font-weight:600;\">{$team}</td></tr>"
            : '';

        return <<<HTML
<div style="font-family:Arial,Helvetica,sans-serif;max-width:560px;margin:0 auto;color:#1d1f5a;">
  <div style="background:#1D1F5A;color:#fff;padding:20px 24px;border-radius:12px 12px 0 0;">
    <h2 style="margin:0;font-size:20px;">📣 Session Reminder</h2>
  </div>
  <div style="border:1px solid #e6e6ef;border-top:none;border-radius:0 0 12px 12px;padding:24px;">
    <p style="margin:0 0 12px;">Hi <strong>{$name}</strong>,</p>
    <p style="margin:0 0 16px;color:#444;">This is a reminder for your upcoming BIT English Club session:</p>
    <table style="width:100%;border-collapse:collapse;font-size:15px;margin-bottom:20px;">
      <tr><td style="padding:4px 0;color:#666;width:90px;">Session</td><td style="padding:4px 0;font-weight:600;">{$title}</td></tr>
      <tr><td style="padding:4px 0;color:#666;">When</td><td style="padding:4px 0;font-weight:600;">{$when}</td></tr>
      {$teamRow}
    </table>
    <a href="{$scanUrl}" style="display:inline-block;background:#1D1F5A;color:#fff;text-decoration:none;padding:12px 22px;border-radius:8px;font-weight:600;">Open the check-in scanner</a>
    <p style="margin:18px 0 0;font-size:13px;color:#888;">See you there! Scan the QR code shown in class to mark your attendance and earn XP.</p>
  </div>
  <p style="text-align:center;font-size:12px;color:#aaa;margin:14px 0;">BIT English Club · Attendance System</p>
</div>
HTML;
    }

    /** Plain-text fallback for a session reminder. */
    public static function sessionReminderText($session, $memberName) {
        $when = self::whenLine($session);
        $title = $session['session_name'] ?? 'Club session';
        $lines = [
            "Hi " . ($memberName ?: 'there') . ",",
            "",
            "Reminder for your BIT English Club session:",
            "  Session: $title",
            "  When:    $when",
        ];
        if (!empty($session['session_team'])) {
            $lines[] = "  Team:    " . $session['session_team'];
        }
        $lines[] = "";
        $lines[] = "Scan the QR code shown in class to mark your attendance.";
        $lines[] = "BIT English Club";
        return implode("\n", $lines);
    }

    /**
     * Send a session reminder to a list of members.
     * $members: rows with at least 'email' and 'name'.
     * Returns ['sent'=>int, 'failed'=>int, 'skipped'=>int].
     */
    public static function sendSessionReminders($session, array $members) {
        $sent = $failed = $skipped = 0;
        $subject = 'Reminder: ' . ($session['session_name'] ?? 'Club session') . ' — ' . self::whenLine($session);

        foreach ($members as $m) {
            $email = trim($m['email'] ?? '');
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $skipped++;
                continue;
            }
            $ok = Mailer::send(
                $email,
                $m['name'] ?? '',
                $subject,
                self::sessionReminderHtml($session, $m['name'] ?? ''),
                self::sessionReminderText($session, $m['name'] ?? '')
            );
            $ok ? $sent++ : $failed++;
        }
        return ['sent' => $sent, 'failed' => $failed, 'skipped' => $skipped];
    }
}
