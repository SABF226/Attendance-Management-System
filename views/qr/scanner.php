<?php
// Member-only QR scanner page
$csrfToken = Security::generateCsrfToken();
$scanUrl   = Security::baseUrl('index.php') . '?page=qr&action=scan';
?>

<script src="<?= Security::baseUrl('assets/js/html5-qrcode.min.js') ?>"></script>

<div class="card">
    <div class="card-header">
        <h2 class="card-title">Scan Attendance QR Code</h2>
    </div>

    <div style="padding:24px;">

        <p style="text-align:center;color:#666;margin-bottom:16px;font-size:14px;">
            Point your camera at the QR code displayed by your coach.
        </p>

        <div id="qr-reader" style="max-width:480px;margin:0 auto;border-radius:12px;overflow:hidden;"></div>

        <div id="result" style="margin-top:20px;text-align:center;"></div>

        <div style="text-align:center;margin-top:16px;">
            <a href="index.php?page=dashboard" class="btn btn-secondary">Back to Dashboard</a>
        </div>

    </div>
</div>

<script>
(function () {
    const csrfToken = <?= json_encode($csrfToken) ?>;
    const scanUrl   = <?= json_encode($scanUrl) ?>;
    let scanning    = true;
    let scanner     = null;

    function showResult(html) {
        document.getElementById('result').innerHTML = html;
    }

    function showError(msg) {
        document.getElementById('qr-reader').style.display = 'none';
        showResult(
            '<div style="background:#fff3cd;border:1px solid #ffeeba;border-radius:10px;padding:24px;">' +
            '<p style="color:#856404;margin:0 0 8px;font-weight:600;">Camera unavailable</p>' +
            '<p style="color:#856404;margin:0 0 16px;font-size:13px;">' + msg + '</p>' +
            '<button onclick="location.reload()" class="btn btn-primary">Try Again</button>' +
            '</div>'
        );
    }

    function onScanSuccess(decodedText) {
        if (!scanning) return;
        scanning = false;

        if (scanner) scanner.stop().catch(() => {});
        document.getElementById('qr-reader').style.display = 'none';

        showResult(
            '<div style="padding:20px;">' +
            '<div class="spinner" style="margin:0 auto 12px;width:32px;height:32px;border:3px solid #ccc;border-top-color:#1D1F5A;border-radius:50%;animation:spin .8s linear infinite;"></div>' +
            '<p>Validating attendance…</p></div>'
        );

        fetch(scanUrl, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            body: JSON.stringify({ token: decodedText, csrf_token: csrfToken })
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                showResult(
                    '<div style="background:#d4edda;border:1px solid #c3e6cb;border-radius:10px;padding:24px;">' +
                    '<div style="font-size:48px;margin-bottom:8px;">✅</div>' +
                    '<h3 style="color:#155724;margin:0 0 4px;">' + data.message + '</h3>' +
                    '<p style="color:#155724;margin:0;">Total XP: <strong>' + data.newPoints + '</strong></p>' +
                    '</div>'
                );
            } else {
                showResult(
                    '<div style="background:#f8d7da;border:1px solid #f5c6cb;border-radius:10px;padding:24px;">' +
                    '<div style="font-size:48px;margin-bottom:8px;">❌</div>' +
                    '<p style="color:#721c24;margin:0 0 16px;">' + data.error + '</p>' +
                    '<button onclick="location.reload()" class="btn btn-primary">Try Again</button>' +
                    '</div>'
                );
            }
        })
        .catch(() => {
            showResult(
                '<div style="background:#f8d7da;border:1px solid #f5c6cb;border-radius:10px;padding:24px;">' +
                '<p style="color:#721c24;margin:0 0 16px;">Network error. Please check your connection.</p>' +
                '<button onclick="location.reload()" class="btn btn-primary">Try Again</button>' +
                '</div>'
            );
        });
    }

    function startWithCamera(cameraId) {
        scanner = new Html5Qrcode('qr-reader');
        scanner.start(
            cameraId,
            { fps: 10, qrbox: { width: 250, height: 250 } },
            onScanSuccess,
            function () { /* ignore per-frame decode errors */ }
        ).catch(err => showError('Could not start camera: ' + err));
    }

    // Enumerate cameras first, then pick the best one
    Html5Qrcode.getCameras()
        .then(cameras => {
            if (!cameras || cameras.length === 0) {
                showError('No camera detected on this device.');
                return;
            }
            // Prefer rear-facing camera by label, otherwise take the last one
            const rear = cameras.find(c =>
                /back|rear|environment/i.test(c.label)
            );
            startWithCamera((rear || cameras[cameras.length - 1]).id);
        })
        .catch(err => {
            // getCameras() rejects when permission denied
            showError('Camera permission denied. Please allow access in your browser settings and reload. (' + err + ')');
        });
})();
</script>

<style>
@keyframes spin { to { transform: rotate(360deg); } }
</style>
