# 🚀 Déploiement sur InfinityFree (PHP + MySQL gratuit)

Guide pas-à-pas pour mettre l'app **BIT English Club Attendance** en ligne sur
[InfinityFree](https://infinityfree.com) (hébergement PHP + MySQL gratuit, SSL inclus).

> ⏱️ ~30 min la première fois. Tu auras besoin : du code du projet, d'un client FTP
> (**FileZilla**) ou du gestionnaire de fichiers en ligne, et de ton **mot de passe
> d'application Gmail** (pour les emails).

---

## 0. Avant de commencer — préparer `vendor/`

`vendor/` est **ignoré par git** et InfinityFree **n'a pas Composer**. Il faut donc
générer le dossier en local puis l'**uploader tel quel** :

```bash
cd /var/www/html/attendance-list
composer install --no-dev   # crée vendor/ (PHPMailer + mpdf)
```

➡️ On uploadera **tout le projet, y compris `vendor/`**.

---

## 1. Créer le compte et le site

1. Inscris-toi sur **https://infinityfree.com** (gratuit, sans carte bancaire).
2. **Create Account** → choisis un sous-domaine gratuit (ex. `bit-attendance.rf.gd`)
   ou branche un domaine perso plus tard.
3. Attends que le compte soit **activé** (quelques minutes).

Note bien, depuis le **Client Area → "Account Details"** :
- **FTP hostname**, **FTP username**, **FTP password**
- Le dossier web racine : **`htdocs/`**

---

## 2. Créer la base de données MySQL

1. Client Area → **MySQL Databases**.
2. Crée une base (ex. `english_club`). Le panneau te donne des **noms préfixés** :
   - **DB host** : ex. `sqlXXX.infinityfree.com` (⚠️ **pas** `localhost`)
   - **DB name** : ex. `epiz_12345678_english_club`
   - **DB user** : ex. `epiz_12345678`
   - **DB password** : celui que tu as défini
3. Garde ces 4 valeurs sous la main.

---

## 3. Configurer la connexion DB dans le code

Édite **`config/database.php`** avec les valeurs d'InfinityFree :

```php
private $host     = 'sqlXXX.infinityfree.com';      // DB host fourni
private $dbname   = 'epiz_12345678_english_club';   // DB name fourni
private $username = 'epiz_12345678';                // DB user fourni
private $password = 'TON_MOT_DE_PASSE_DB';
```

> 💡 `PDO::ATTR_PERSISTENT` est activé : c'est OK sur InfinityFree.

---

## 4. Configurer les emails

1. Copie le modèle puis renseigne ton **App Password Gmail** :
   ```bash
   cp config/mail.local.php.example config/mail.local.php
   ```
2. Édite **`config/mail.local.php`** :
   ```php
   return [
       'password' => 'ton-app-password-16-caracteres',
       'app_url'  => 'https://bit-attendance.rf.gd',  // ⚠️ URL PUBLIQUE (https), pas localhost
   ];
   ```
   `app_url` sert à construire le lien du scanner dans les emails — mets bien l'URL **publique**.

> 🔒 `config/mail.local.php` est ignoré par git ; tu le **crées directement sur le serveur**
> (ou tu l'uploades), il ne doit jamais finir dans un dépôt public.

---

## 5. Uploader les fichiers (FTP)

1. Ouvre **FileZilla** → connecte-toi avec le FTP host/user/password du Client Area.
2. Va dans le dossier **`htdocs/`** côté serveur.
3. Envoie **tout le contenu du projet** dans `htdocs/` :
   `api/ assets/ config/ controllers/ cron/ fpdf/ helpers/ models/ views/ vendor/ index.php …`
   - ✅ **Inclure `vendor/`** (sinon PHPMailer/mpdf manquent → erreurs).
   - ✅ Inclure `config/database.php` et `config/mail.local.php` modifiés.
   - ⏳ `vendor/` a beaucoup de petits fichiers → l'upload peut être long.

> Astuce : pour aller plus vite, zippe le projet en local, uploade le `.zip` via le
> **gestionnaire de fichiers en ligne** d'InfinityFree, puis **Extract** sur le serveur.

---

## 6. Importer la base (phpMyAdmin)

1. Client Area → **MySQL Databases** → **Admin (phpMyAdmin)** de ta base.
2. Sélectionne ta base (`epiz_..._english_club`) à gauche.
3. Onglet **Import** → **Choisir un fichier** → `deploy/install.sql` → **Go**.
   - Ce fichier **ne contient pas** de `CREATE DATABASE` : il s'importe dans la base déjà choisie.
   - Compatible MariaDB (collation `utf8mb4_unicode_ci`).
4. Vérifie que les 3 tables sont créées : `members`, `attendance_sessions`, `attendance_records`.

---

## 7. Créer le compte admin

1. Dans le navigateur, ouvre **une fois** :
   `https://ton-site/setup_auth.php`
   → ça ajoute/seed les comptes admin (`admin@bit.bf` / `admin123`) et membre test
   (`member@bit.bf` / `member123`).
2. **Connecte-toi**, change le mot de passe admin.
3. 🔐 **Supprime `setup_auth.php` ET `setup.php`** du serveur (sécurité).

---

## 8. Activer le HTTPS (indispensable)

La caméra (scan QR) et le GPS (géofence) **exigent HTTPS**.
- Client Area → **Free SSL Certificates** (ou via Cloudflare) → active le certificat
  pour ton domaine, puis force le `https://`.
- Vérifie : ouvre le site en `https://…`, le cadenas doit être présent.

---

## 9. Test final (checklist)

- [ ] Page d'accueil → redirige vers la connexion
- [ ] Login admin OK
- [ ] Créer une session, prendre les présences
- [ ] **QR** : générer, le code tourne toutes les 20 s
- [ ] **Scan** (téléphone, HTTPS) : caméra OK → présence + XP
- [ ] **Géofence** : épingler depuis un téléphone GPS (≤ 300 m)
- [ ] **Email** : `Send reminder` sur une session → email reçu

---

## ⚠️ Limites du plan gratuit InfinityFree

| Sujet | Détail |
|---|---|
| **Pas de cron** | Le plan gratuit **ne lance pas** `cron/send_reminders.php`. → Les rappels **automatiques** ne tournent pas. Le bouton **« Send reminder »** (manuel) fonctionne. |
| **Pas d'accès SSH/CLI** | D'où l'upload de `vendor/` (pas de `composer install` sur le serveur). |
| **Quotas** | Limites de CPU/jour et ~quotas d'« hits ». OK pour un club ; pas pour du gros trafic. |
| **MySQL distant bloqué** | La base n'est joignable que depuis le serveur (normal). |

### Automatiser les rappels malgré tout (optionnel)
Comme il n'y a pas de cron, on peut ajouter un **petit endpoint web sécurisé** (avec une
clé secrète) que tu déclenches via un service gratuit comme **cron-job.org** chaque matin.
Dis-le-moi et je l'ajoute (`cron/send_reminders.php` est actuellement **CLI-only** par
sécurité).

---

## 🔧 Dépannage rapide

| Problème | Cause probable / solution |
|---|---|
| **"Database connection failed"** | `config/database.php` : host/nom/préfixe `epiz_…` incorrects |
| **Page blanche / 500** | `vendor/` non uploadé, ou version PHP < 8 (régler PHP 8.x dans le panneau) |
| **Emails non envoyés** | `config/mail.local.php` absent/incorrect, ou App Password Gmail invalide |
| **Liens d'email pointent vers localhost** | `app_url` pas mis à jour dans `config/mail.local.php` |
| **Caméra/GPS bloqués** | Site pas en **HTTPS** |
| **QR scan « invalide »** | Horloge serveur décalée (le token tournant dépend de l'heure) |

---

## 🔭 Et Vercel ?

Vercel **ne convient pas** à cette app (PHP + MySQL ne tournent pas nativement dessus).
Vercel + MongoDB Atlas deviendra pertinent **seulement** après la refonte **MERN**
(voir `docs/MERN_REDESIGN.md`). Pour un vrai domaine + meilleure infra côté PHP, vise le
**GitHub Student Developer Pack** (domaine `.me` gratuit + crédits DigitalOcean).
