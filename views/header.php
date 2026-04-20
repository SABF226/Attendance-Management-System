<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?? 'English Club Attendance' ?></title>
    <link rel="stylesheet" href="assets/css/style.css">
    <script src="assets/js/main.js" defer></script>
</head>
<body>
    <nav class="navbar">
        <div class="nav-container">
            <div class="nav-brand">
                <img src="assets/logo_bit_en.jpg" alt="English Club Logo" class="nav-logo-img">
                <span class="nav-title">English Club</span>
            </div>
            <button class="menu-toggle" aria-label="Toggle menu" onclick="toggleMenu()">
                <span></span>
                <span></span>
                <span></span>
            </button>
            <ul class="nav-menu" id="navMenu">
                <li><a href="index.php" class="nav-link <?= ($currentPage ?? '') === 'dashboard' ? 'active' : '' ?>">Dashboard</a></li>
                <li><a href="?page=members" class="nav-link <?= ($currentPage ?? '') === 'members' ? 'active' : '' ?>">Members</a></li>
                <li><a href="?page=sessions" class="nav-link <?= ($currentPage ?? '') === 'sessions' ? 'active' : '' ?>">Sessions</a></li>
            </ul>
        </div>
    </nav>
    <div class="container">
        <?php if (!empty($breadcrumbs)): ?>
        <nav class="breadcrumb" aria-label="Breadcrumb">
            <a href="index.php">Dashboard</a>
            <?php foreach ($breadcrumbs as $index => $crumb): ?>
                <span class="breadcrumb-separator">/</span>
                <?php if ($index === count($breadcrumbs) - 1): ?>
                    <span class="breadcrumb-current"><?= htmlspecialchars($crumb['label']) ?></span>
                <?php else: ?>
                    <a href="<?= $crumb['url'] ?? '#' ?>"><?= htmlspecialchars($crumb['label']) ?></a>
                <?php endif; ?>
            <?php endforeach; ?>
        </nav>
        <?php endif; ?>
