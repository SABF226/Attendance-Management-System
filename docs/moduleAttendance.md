Voici le fichier `moduleAttendance.md` prêt à être transmis à votre agent de codage.

```markdown
# 📱 Module d’Attendance par QR Code – Spécification pour Agent de Codage

> Intégration d’un système de validation de présence par QR code dans l’application existante **BIT English Club Attendance System**.

---

## 🎯 Objectif

Permettre aux **membres** de valider leur présence à une session en scannant un QR code dynamique affiché par un **coach**.  
La validation enregistre automatiquement la présence, évite les doublons et crédite des points d’expérience (XP).

---

## 🏗️ 1. Modifications de la base de données

Exécute le script SQL suivant (fichier : `config/schema_update_qr.sql`) :

```sql
-- Ajout des colonnes pour le QR code dans la table attendance_sessions
ALTER TABLE attendance_sessions
ADD COLUMN qr_code_token VARCHAR(64) UNIQUE NULL,
ADD COLUMN qr_code_expires_at DATETIME NULL,
ADD COLUMN is_qr_active BOOLEAN DEFAULT FALSE;

-- Ajout d’une colonne points dans members (si inexistante)
ALTER TABLE members
ADD COLUMN points INT DEFAULT 0;
```

---

## 🧩 2. Backend – Modèle et Contrôleur

### 2.1 Mise à jour du modèle `AttendanceSession`

Fichier : `models/AttendanceSession.php`

Ajoute les méthodes suivantes :

```php
/**
 * Génère un QR code pour une session
 * @param int $sessionId
 * @param int $durationMinutes Durée de validité (défaut 30)
 * @return string|false Le token généré ou false en cas d'échec
 */
public function generateQRCode($sessionId, $durationMinutes = 30) {
    $token = bin2hex(random_bytes(32));
    $expiresAt = date('Y-m-d H:i:s', strtotime("+{$durationMinutes} minutes"));
    $sql = "UPDATE attendance_sessions 
            SET qr_code_token = :token, 
                qr_code_expires_at = :expires,
                is_qr_active = 1 
            WHERE id = :id";
    $stmt = $this->db->prepare($sql);
    $result = $stmt->execute([
        ':token' => $token,
        ':expires' => $expiresAt,
        ':id' => $sessionId
    ]);
    return $result ? $token : false;
}

/**
 * Vérifie la validité d’un token QR
 * @param int $sessionId
 * @param string $token
 * @return bool
 */
public function isQRCodeValid($sessionId, $token) {
    $sql = "SELECT id FROM attendance_sessions 
            WHERE id = :id 
              AND qr_code_token = :token 
              AND is_qr_active = 1 
              AND qr_code_expires_at > NOW()";
    $stmt = $this->db->prepare($sql);
    $stmt->execute([':id' => $sessionId, ':token' => $token]);
    return $stmt->fetch() !== false;
}

/**
 * Désactive le QR code d’une session
 * @param int $sessionId
 * @return bool
 */
public function deactivateQRCode($sessionId) {
    $sql = "UPDATE attendance_sessions SET is_qr_active = 0 WHERE id = :id";
    $stmt = $this->db->prepare($sql);
    return $stmt->execute([':id' => $sessionId]);
}
```

### 2.2 Nouveau contrôleur `QRController`

Fichier : `controllers/QRController.php`

```php
<?php
require_once __DIR__ . '/../models/AttendanceSession.php';
require_once __DIR__ . '/../models/AttendanceRecord.php';
require_once __DIR__ . '/../helpers/Security.php';

class QRController {
    private $db;
    private $sessionModel;
    private $attendanceModel;

    public function __construct($db) {
        $this->db = $db;
        $this->sessionModel = new AttendanceSession($db);
        $this->attendanceModel = new AttendanceRecord($db);
    }

    // POST /qr/generate – Génération (coach uniquement)
    public function generate() {
        Security::verifyCSRF();
        if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'coach') {
            http_response_code(403);
            echo json_encode(['success' => false, 'error' => 'Accès refusé']);
            return;
        }
        $sessionId = (int)$_POST['session_id'];
        $token = $this->sessionModel->generateQRCode($sessionId);
        if ($token) {
            echo json_encode(['success' => true, 'token' => $token]);
        } else {
            echo json_encode(['success' => false, 'error' => 'Génération échouée']);
        }
    }

    // GET /qr/session/{id} – Affiche le QR (coach uniquement)
    public function display($sessionId) {
        if ($_SESSION['user_role'] !== 'coach') {
            header('Location: index.php?page=login');
            return;
        }
        // Récupérer les infos de la session
        $sql = "SELECT * FROM attendance_sessions WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $sessionId]);
        $session = $stmt->fetch(PDO::FETCH_ASSOC);
        require_once __DIR__ . '/../views/qr/display.php';
    }

    // POST /api/scan – Traitement du scan par un membre
    public function scan() {
        if ($_SESSION['user_role'] !== 'member') {
            http_response_code(403);
            echo json_encode(['success' => false, 'error' => 'Accès membre requis']);
            return;
        }
        $input = json_decode(file_get_contents('php://input'), true);
        $token = $input['token'] ?? '';
        // Trouver la session associée à ce token
        $sql = "SELECT id FROM attendance_sessions 
                WHERE qr_code_token = :token 
                  AND is_qr_active = 1 
                  AND qr_code_expires_at > NOW()";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':token' => $token]);
        $session = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$session) {
            echo json_encode(['success' => false, 'error' => 'QR code invalide ou expiré']);
            return;
        }
        $sessionId = $session['id'];
        $memberId = $_SESSION['user_id'];
        // Vérifier si déjà présent
        $checkSql = "SELECT id FROM attendance_records 
                     WHERE session_id = :sid AND member_id = :mid";
        $checkStmt = $this->db->prepare($checkSql);
        $checkStmt->execute([':sid' => $sessionId, ':mid' => $memberId]);
        if ($checkStmt->fetch()) {
            echo json_encode(['success' => false, 'error' => 'Présence déjà enregistrée pour cette session']);
            return;
        }
        // Insérer la présence
        $insertSql = "INSERT INTO attendance_records (session_id, member_id, status, check_in_time) 
                      VALUES (:sid, :mid, 'present', NOW())";
        $insertStmt = $this->db->prepare($insertSql);
        $insertStmt->execute([':sid' => $sessionId, ':mid' => $memberId]);
        // Ajouter des points (10 XP)
        $updatePoints = "UPDATE members SET points = points + 10 WHERE id = :mid";
        $pointsStmt = $this->db->prepare($updatePoints);
        $pointsStmt->execute([':mid' => $memberId]);
        // Récupérer le nouveau total
        $totalStmt = $this->db->prepare("SELECT points FROM members WHERE id = :mid");
        $totalStmt->execute([':mid' => $memberId]);
        $newPoints = $totalStmt->fetchColumn();
        echo json_encode(['success' => true, 'newPoints' => $newPoints, 'message' => 'Présence validée ! +10 XP']);
    }

    // POST /qr/deactivate/{id} – Désactivation manuelle (coach)
    public function deactivate($sessionId) {
        Security::verifyCSRF();
        if ($_SESSION['user_role'] !== 'coach') {
            http_response_code(403);
            echo json_encode(['success' => false]);
            return;
        }
        $result = $this->sessionModel->deactivateQRCode($sessionId);
        echo json_encode(['success' => $result]);
    }

    // GET /member/scanner – Page de scan pour membre
    public function scannerPage() {
        if ($_SESSION['user_role'] !== 'member') {
            header('Location: index.php?page=login');
            return;
        }
        require_once __DIR__ . '/../views/qr/scanner.php';
    }
}
```

---

## 🖥️ 3. Frontend – Vues

### 3.1 Vue d’affichage du QR (`views/qr/display.php`)

```php
<?php
// $session doit être passé depuis le contrôleur
?>
<div class="container mt-4">
    <div class="card shadow rounded-3">
        <div class="card-header bg-primary text-white">
            <h4>QR Code – <?= htmlspecialchars($session['name']) ?></h4>
        </div>
        <div class="card-body text-center">
            <?php
            // Génération du QR avec la bibliothèque endroid/qr-code
            use Endroid\QrCode\Builder\Builder;
            use Endroid\QrCode\Writer\PngWriter;
            $qrCode = Builder::create()
                ->writer(new PngWriter())
                ->data($session['qr_code_token'])
                ->size(300)
                ->build();
            echo '<img src="data:image/png;base64,' . base64_encode($qrCode->getString()) . '">';
            ?>
            <p class="mt-3">Ce QR code expire le : <?= date('H:i:s', strtotime($session['qr_code_expires_at'])) ?></p>
            <div id="timer" class="mb-3"></div>
            <button id="deactivateBtn" class="btn btn-danger" data-session="<?= $session['id'] ?>">Désactiver le QR</button>
        </div>
    </div>
</div>
<script>
// Timer de compte à rebours
const expiresAt = new Date("<?= $session['qr_code_expires_at'] ?>").getTime();
const timer = setInterval(() => {
    const now = new Date().getTime();
    const diff = expiresAt - now;
    if (diff <= 0) {
        clearInterval(timer);
        document.getElementById('timer').innerHTML = "QR expiré";
        location.reload();
    } else {
        const minutes = Math.floor((diff % (1000*60*60)) / (1000*60));
        const seconds = Math.floor((diff % (1000*60)) / 1000);
        document.getElementById('timer').innerHTML = `Validité : ${minutes}m ${seconds}s`;
    }
}, 1000);
document.getElementById('deactivateBtn')?.addEventListener('click', async () => {
    const res = await fetch('/qr/deactivate/' + this.dataset.session, { method: 'POST', headers: { 'X-CSRF-Token': '<?= Security::getCSRFToken() ?>' } });
    if (res.ok) location.reload();
});
</script>
```

### 3.2 Vue de scan (`views/qr/scanner.php`)

```html
<div class="container py-4">
    <div class="card shadow rounded-3">
        <div class="card-header bg-primary text-white">
            <h4>Scanner votre QR code</h4>
        </div>
        <div class="card-body">
            <div id="qr-reader" style="width: 100%; max-width: 500px; margin: auto;"></div>
            <div id="result" class="mt-3 text-center"></div>
        </div>
    </div>
</div>
<script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>
<script>
const html5QrCode = new Html5Qrcode("qr-reader");
const qrCodeSuccessCallback = (decodedText, decodedResult) => {
    html5QrCode.stop().then(() => {
        document.getElementById('result').innerHTML = '<div class="spinner-border text-primary"></div> Validation...';
        fetch('/api/scan', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            body: JSON.stringify({ token: decodedText })
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                document.getElementById('result').innerHTML = `<div class="alert alert-success">✅ ${data.message} (Nouveau total : ${data.newPoints} XP)</div>`;
            } else {
                document.getElementById('result').innerHTML = `<div class="alert alert-danger">❌ ${data.error}</div><button onclick="location.reload()" class="btn btn-primary mt-2">Réessayer</button>`;
            }
        })
        .catch(err => {
            document.getElementById('result').innerHTML = `<div class="alert alert-danger">Erreur réseau. Réessayez.</div><button onclick="location.reload()" class="btn btn-primary mt-2">Réessayer</button>`;
        });
    }).catch(err => console.error(err));
};
html5QrCode.start({ facingMode: "environment" }, { fps: 10, qrbox: 250 }, qrCodeSuccessCallback);
</script>
```

---

## 🔌 4. Routes à ajouter dans `index.php`

```php
// QR Code routes
$router->post('/qr/generate', 'QRController@generate', ['auth', 'role:coach']);
$router->get('/qr/session/(\d+)', 'QRController@display', ['auth', 'role:coach']);
$router->post('/qr/deactivate/(\d+)', 'QRController@deactivate', ['auth', 'role:coach']);
$router->post('/api/scan', 'QRController@scan', ['auth', 'role:member']);
$router->get('/member/scanner', 'QRController@scannerPage', ['auth', 'role:member']);
```

---

## 🛡️ 5. Sécurité & Contraintes

| Exigence | Implémentation |
|----------|----------------|
| Token aléatoire fort | `random_bytes(32)` + `bin2hex` |
| Expiration automatique | Colonne `qr_code_expires_at` + vérification `NOW()` |
| Anti-doublon | Requête `SELECT ... WHERE member_id` + contrainte UNIQUE (à ajouter dans `attendance_records`) |
| Contrôle accès | Middleware `role:coach` ou `role:member` |
| CSRF | `Security::verifyCSRF()` sur les endpoints POST |
| Rate limiting (optionnel) | Ajouter une vérification dans `scan()` : limiter à 5 scans/minute par membre |

---

## 🧪 6. Tests à valider

1. **Coach** : Génère un QR → la session est mise à jour en BDD avec token + expiration.  
2. **Membre** : Scanne le QR → présence enregistrée, points incrémentés.  
3. **Double scan** : Message d’erreur, pas de double insertion.  
4. **QR expiré** : Scan refusé.  
5. **Désactivation manuelle** : Scan ultérieur refusé.  

---

## 📦 7. Dépendances

Ajouter via Composer (si vous utilisez la génération PHP du QR) :

```bash
composer require endroid/qr-code
```

Sinon, la génération peut rester côté client (aucune dépendance PHP supplémentaire).  
Pour le scanner frontal, la bibliothèque `html5-qrcode` est incluse via CDN.

---

## ✅ Checklist finale pour l’agent

- [ ] Exécuter le script SQL d’ajout des colonnes.
- [ ] Modifier `models/AttendanceSession.php` avec les trois nouvelles méthodes.
- [ ] Créer `controllers/QRController.php` avec toutes les actions.
- [ ] Ajouter les routes correspondantes.
- [ ] Créer les dossiers/vues : `views/qr/display.php` et `views/qr/scanner.php`.
- [ ] Tester manuellement chaque fonctionnalité.
- [ ] Mettre à jour `README.md` pour documenter la nouvelle feature.

---

**Le module est prêt à être codé. Respecte scrupuleusement les noms de fichiers, les classes et les chemins indiqués.**  
```