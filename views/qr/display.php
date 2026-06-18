<?php
// $session, $pageTitle, $breadcrumbs are set by QRController::display()
$csrfToken = Security::generateCsrfToken();
$sessionId = (int)$session['id'];
$isActive  = (bool)$session['is_qr_active'];
$tokenUrl  = Security::baseUrl('index.php') . '?page=qr&action=token&id=' . $sessionId;
$geoSet    = !empty($session['geo_lat']) && !empty($session['geo_lng']);
$geoRadius = $geoSet ? (int)$session['geo_radius'] : 100;
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

        <div id="qrcode" style="display:none;padding:16px;background:#fff;border-radius:12px;box-shadow:0 2px 12px rgba(0,0,0,.12);margin:0 auto 16px;width:max-content;"></div>

        <p id="qr-rotate-note" style="font-size:12px;color:#0a7d33;font-weight:600;margin-bottom:8px;display:none;">
            🔄 This code refreshes every <span id="qr-step">20</span>s — a screenshot won't work once it rotates.
        </p>

        <p id="qr-expiry" style="font-size:13px;color:#666;margin-bottom:4px;display:none;">
            Check-in closes at <strong id="qr-expiry-time"></strong>
        </p>

        <div id="qr-timer" style="font-size:22px;font-weight:700;color:#1D1F5A;margin-bottom:20px;"></div>

        <div id="qr-inactive" style="padding:40px;color:#888;display:none;">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"
                 style="width:64px;height:64px;margin-bottom:12px;opacity:.4;">
                <rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/>
                <rect x="3" y="14" width="7" height="7" rx="1"/>
                <path d="M14 14h2v2h-2zM18 14h3v3h-3zM14 18h3v3h-3zM18 18h3v3"/>
            </svg>
            <p style="font-size:16px;font-weight:600;">QR check-in is not active</p>
            <p style="font-size:13px;">Click <strong>Generate</strong> above to open check-in.</p>
        </div>

        <form id="qr-deactivate-form" method="POST" action="index.php?page=qr&action=deactivate&id=<?= $sessionId ?>" style="display:none;">
            <?= Security::csrfField() ?>
            <button type="submit" class="btn btn-danger"
                    onclick="return confirm('Deactivate this QR code? Members will not be able to scan it anymore.');">
                Deactivate QR
            </button>
        </form>

    </div>
</div>

<div class="card">
    <div class="card-header">
        <h2 class="card-title">📍 Class location (geofence)</h2>
    </div>
    <div style="padding:24px;">
        <p id="geo-status" style="font-size:14px;margin-bottom:14px;">
            <?php if ($geoSet): ?>
                ✅ Geofence <strong>ON</strong> — members must be within <strong><?= $geoRadius ?> m</strong> of the pinned location.
            <?php else: ?>
                ⚪ Geofence <strong>OFF</strong> — members can check in from anywhere (the rotating QR still applies).
            <?php endif; ?>
        </p>
        <p style="font-size:12px;color:#888;margin-bottom:14px;">
            Stand in the classroom and tap the button to pin this room as the check-in area.
        </p>
        <div style="display:flex;gap:10px;align-items:center;flex-wrap:wrap;">
            <label for="geo-radius" style="font-size:13px;color:#666;">Radius</label>
            <select id="geo-radius" class="form-control" style="width:auto;">
                <?php foreach ([50, 100, 200, 500] as $r): ?>
                    <option value="<?= $r ?>" <?= $r === $geoRadius ? 'selected' : '' ?>><?= $r ?> m</option>
                <?php endforeach; ?>
            </select>
            <button id="geo-set" class="btn btn-primary" type="button">Set to my current location</button>
            <button id="geo-clear" class="btn btn-secondary" type="button" <?= $geoSet ? '' : 'style="display:none;"' ?>>Remove geofence</button>
        </div>
        <p id="geo-msg" style="font-size:13px;margin-top:12px;color:#666;"></p>
    </div>
</div>

<script>
(function () {
    const sessionId = <?= $sessionId ?>;
    const csrf      = <?= json_encode($csrfToken) ?>;
    const url       = 'index.php?page=qr&action=geofence&id=' + sessionId;
    const statusEl  = document.getElementById('geo-status');
    const msgEl     = document.getElementById('geo-msg');
    const setBtn    = document.getElementById('geo-set');
    const clearBtn  = document.getElementById('geo-clear');

    function post(body) {
        return fetch(url, {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded', 'X-Requested-With': 'XMLHttpRequest' },
            body: new URLSearchParams(body).toString()
        }).then(r => r.json());
    }

    setBtn.addEventListener('click', function () {
        if (!navigator.geolocation) { msgEl.style.color = '#B61F24'; msgEl.textContent = 'This device has no geolocation.'; return; }
        msgEl.style.color = '#666';
        msgEl.textContent = 'Acquiring GPS… stay still, accuracy improves over a few seconds.';
        setBtn.disabled = true;

        // Watch the GPS for a few seconds and keep the most accurate fix, instead
        // of trusting a single (often coarse) first reading.
        let best = null, finished = false, watchId = null;

        function submitPin() {
            const pos = best;
            const radius = document.getElementById('geo-radius').value;
            post({ mode: 'set', csrf_token: csrf, lat: pos.coords.latitude, lng: pos.coords.longitude, radius: radius, accuracy: Math.round(pos.coords.accuracy) })
                .then(function (d) {
                    setBtn.disabled = false;
                    if (d.success) {
                        statusEl.innerHTML = '✅ Geofence <strong>ON</strong> — members must be within <strong>' + d.radius + ' m</strong> of the pinned location.';
                        clearBtn.style.display = 'inline-block';
                        msgEl.style.color = '#0a7d33';
                        msgEl.textContent = 'Location pinned (your GPS accuracy ±' + Math.round(pos.coords.accuracy) + ' m).';
                    } else {
                        msgEl.style.color = '#B61F24';
                        msgEl.textContent = d.error || 'Could not set location.';
                    }
                })
                .catch(function () { setBtn.disabled = false; msgEl.style.color = '#B61F24'; msgEl.textContent = 'Network error.'; });
        }

        function finish() {
            if (finished) return;
            finished = true;
            if (watchId !== null) navigator.geolocation.clearWatch(watchId);
            clearTimeout(timer);
            if (!best) {
                setBtn.disabled = false;
                msgEl.style.color = '#B61F24';
                msgEl.textContent = 'Could not get a GPS fix. Move near a window or outside, keep GPS on, and try again.';
                return;
            }
            submitPin();
        }

        const timer = setTimeout(finish, 20000); // collect up to 20 s, then use the best fix

        watchId = navigator.geolocation.watchPosition(function (pos) {
            if (!best || pos.coords.accuracy < best.coords.accuracy) { best = pos; }
            msgEl.style.color = '#666';
            msgEl.textContent = 'GPS accuracy ±' + Math.round(best.coords.accuracy) + ' m (improving…)';
            if (best.coords.accuracy <= 30) { finish(); } // good enough — stop early
        }, function (err) {
            if (err.code === err.PERMISSION_DENIED) {
                finished = true;
                if (watchId !== null) navigator.geolocation.clearWatch(watchId);
                clearTimeout(timer);
                setBtn.disabled = false;
                msgEl.style.color = '#B61F24';
                msgEl.textContent = 'Location access denied. Allow location for this site and retry.';
            }
            // other transient errors are ignored; the timer finalizes with the best fix (or none)
        }, { enableHighAccuracy: true, maximumAge: 0, timeout: 25000 });
    });

    clearBtn.addEventListener('click', function () {
        post({ mode: 'clear', csrf_token: csrf }).then(function (d) {
            if (d.success) {
                statusEl.innerHTML = '⚪ Geofence <strong>OFF</strong> — members can check in from anywhere (the rotating QR still applies).';
                clearBtn.style.display = 'none';
                msgEl.style.color = '#666';
                msgEl.textContent = 'Geofence removed.';
            }
        });
    });
})();
</script>

<script>
(function () {
    const tokenUrl = <?= json_encode($tokenUrl) ?>;
    const qrEl     = document.getElementById('qrcode');
    const timerEl  = document.getElementById('qr-timer');
    let lastToken = null, expiresAtMs = 0;

    function el(id) { return document.getElementById(id); }

    function renderQR(payload) {
        qrEl.innerHTML = '';
        new QRCode(qrEl, {
            text: payload,
            width: 260,
            height: 260,
            colorDark: '#1D1F5A',
            colorLight: '#ffffff',
            correctLevel: QRCode.CorrectLevel.H
        });
    }

    function setActive(active) {
        qrEl.style.display                     = active ? 'inline-block' : 'none';
        el('qr-rotate-note').style.display     = active ? 'block' : 'none';
        el('qr-expiry').style.display          = active ? 'block' : 'none';
        el('qr-deactivate-form').style.display = active ? 'inline-block' : 'none';
        el('qr-inactive').style.display        = active ? 'none' : 'block';
        if (!active) { timerEl.textContent = ''; lastToken = null; expiresAtMs = 0; }
    }

    function poll() {
        fetch(tokenUrl, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
            .then(r => r.json())
            .then(data => {
                if (!data.active) { setActive(false); return; }
                setActive(true);
                el('qr-step').textContent = data.step;
                expiresAtMs = new Date(data.expiresAt).getTime();
                el('qr-expiry-time').textContent = new Date(data.expiresAt).toLocaleTimeString();
                if (data.token !== lastToken) {
                    lastToken = data.token;
                    // The QR encodes which session + the current rotating token.
                    renderQR(JSON.stringify({ s: data.sessionId, t: data.token }));
                }
            })
            .catch(() => { /* transient network error: keep last QR, retry next tick */ });
    }

    function tick() {
        if (!expiresAtMs) return;
        const diff = expiresAtMs - Date.now();
        if (diff <= 0) {
            timerEl.textContent = 'Check-in closed';
            timerEl.style.color = '#B61F24';
            return;
        }
        const m = Math.floor(diff / 60000);
        const s = Math.floor((diff % 60000) / 1000);
        timerEl.textContent = m + 'm ' + String(s).padStart(2, '0') + 's remaining';
    }

    poll();
    setInterval(poll, 5000);   // refresh the token well within its rotation window
    setInterval(tick, 1000);
})();
</script>
