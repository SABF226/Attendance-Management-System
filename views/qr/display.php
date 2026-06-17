<?php
// $session, $pageTitle, $breadcrumbs are set by QRController::display()
$csrfToken  = Security::generateCsrfToken();
$sessionId  = (int)$session['id'];
$isActive   = (bool)$session['is_qr_active'];
$expiresAt  = $session['qr_code_expires_at'] ?? '';
$token      = htmlspecialchars($session['qr_code_token'] ?? '', ENT_QUOTES);
?>

<script src="<?= Security::baseUrl('assets/js/qrcode.min.js') ?>"></script>

<div class="card">
    <div class="card-header">
        <h2 class="card-title">QR Code — <?= htmlspecialchars($session['session_name']) ?></h2>
        <div style="display:flex;gap:8px;align-items:center;flex-wrap:wrap;">
            <form method="GET" action="index.php" style="display:flex;gap:8px;align-items:center;">
                <input type="hidden" name="page" value="qr">
                <input type="hidden" name="action" value="display">
                <input type="hidden" name="id" value="<?= $sessionId ?>">
                <input type="hidden" name="generate" value="1">
                <label for="duration" style="font-size:13px;color:#666;">Valid for</label>
                <select name="duration" id="duration" class="form-control" style="width:auto;">
                    <option value="5">5 min</option>
                    <option value="15">15 min</option>
                    <option value="30" selected>30 min</option>
                    <option value="60">60 min</option>
                </select>
                <button type="submit" class="btn btn-primary"><?= $isActive ? 'Regenerate' : 'Generate' ?></button>
            </form>
            <a href="index.php?page=sessions" class="btn btn-secondary">Back to Sessions</a>
        </div>
    </div>

    <div style="padding:24px;text-align:center;">

        <?php if ($isActive && $token): ?>

            <div id="qrcode" style="display:inline-block;padding:16px;background:#fff;border-radius:12px;box-shadow:0 2px 12px rgba(0,0,0,.12);margin-bottom:16px;"></div>

            <p style="font-size:13px;color:#666;margin-bottom:4px;">
                Expires at <strong><?= date('H:i:s', strtotime($expiresAt)) ?></strong>
            </p>

            <div id="qr-timer" style="font-size:22px;font-weight:700;color:#1D1F5A;margin-bottom:20px;"></div>

            <form method="POST" action="index.php?page=qr&action=deactivate&id=<?= $sessionId ?>" style="display:inline;">
                <?= Security::csrfField() ?>
                <button type="submit" class="btn btn-danger"
                        onclick="return confirm('Deactivate this QR code? Members will not be able to scan it anymore.');">
                    Deactivate QR
                </button>
            </form>

        <?php else: ?>

            <div style="padding:40px;color:#888;">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"
                     style="width:64px;height:64px;margin-bottom:12px;opacity:.4;">
                    <rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/>
                    <rect x="3" y="14" width="7" height="7" rx="1"/>
                    <path d="M14 14h2v2h-2zM18 14h3v3h-3zM14 18h3v3h-3zM18 18h3v3"/>
                </svg>
                <p style="font-size:16px;font-weight:600;">QR code is deactivated</p>
                <p style="font-size:13px;">Click <strong>Regenerate</strong> to create a new one.</p>
            </div>

        <?php endif; ?>

    </div>
</div>

<?php if ($isActive && $token): ?>
<script>
(function () {
    // Render QR code from token
    new QRCode(document.getElementById('qrcode'), {
        text: <?= json_encode($session['qr_code_token']) ?>,
        width: 260,
        height: 260,
        colorDark: '#1D1F5A',
        colorLight: '#ffffff',
        correctLevel: QRCode.CorrectLevel.H
    });

    // Countdown timer
    const expiresAt = new Date(<?= json_encode($expiresAt) ?>).getTime();
    const timerEl   = document.getElementById('qr-timer');

    function tick() {
        const diff = expiresAt - Date.now();
        if (diff <= 0) {
            timerEl.textContent = 'Expired';
            timerEl.style.color = '#B61F24';
            setTimeout(() => location.reload(), 2000);
            return;
        }
        const m = Math.floor(diff / 60000);
        const s = Math.floor((diff % 60000) / 1000);
        timerEl.textContent = m + 'm ' + String(s).padStart(2, '0') + 's remaining';
    }

    tick();
    setInterval(tick, 1000);
})();
</script>
<?php endif; ?>
