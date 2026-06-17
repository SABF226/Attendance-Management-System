<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?? 'English Club Attendance' ?></title>
    <link rel="stylesheet" href="<?= Security::baseUrl('assets/css/style.css') ?>">
    <script src="<?= Security::baseUrl('assets/js/chart.umd.min.js') ?>"></script>
    <script src="<?= Security::baseUrl('assets/js/main.js') ?>"></script>
</head>
<body>
    <!-- Sidebar Navigation -->
    <nav class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <div class="sidebar-brand">
                <img src="<?= Security::baseUrl('assets/logo_bit_en.jpg') ?>" alt="Logo" class="sidebar-logo">
                <!--<span class="sidebar-title">EngClub</span> -->
            </div>
            <button class="sidebar-toggle" aria-label="Toggle sidebar" onclick="toggleSidebar()">
                <span class="toggle-icon">◀</span>
            </button>
        </div>
        
        <ul class="sidebar-menu">
            <li class="sidebar-item">
                <a href="index.php" class="sidebar-link <?= ($currentPage ?? '') === 'dashboard' ? 'active' : '' ?>">
                    <svg class="sidebar-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path>
                        <polyline points="9 22 9 12 15 12 15 22"></polyline>
                    </svg>
                    <span class="sidebar-text">Dashboard</span>
                </a>
            </li>
            
            <?php if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin'): ?>
            <li class="sidebar-item">
                <a href="?page=members" class="sidebar-link <?= ($currentPage ?? '') === 'members' ? 'active' : '' ?>">
                    <svg class="sidebar-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                        <circle cx="9" cy="7" r="4"></circle>
                        <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                        <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                    </svg>
                    <span class="sidebar-text">Members</span>
                </a>
            </li>
            <li class="sidebar-item">
                <a href="?page=sessions" class="sidebar-link <?= ($currentPage ?? '') === 'sessions' ? 'active' : '' ?>">
                    <svg class="sidebar-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                        <line x1="16" y1="2" x2="16" y2="6"></line>
                        <line x1="8" y1="2" x2="8" y2="6"></line>
                        <line x1="3" y1="10" x2="21" y2="10"></line>
                    </svg>
                    <span class="sidebar-text">Sessions</span>
                </a>
            </li>
            <?php endif; ?>
            
            <?php if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'member'): ?>
            <li class="sidebar-item">
                <a href="?page=qr&action=scanner" class="sidebar-link <?= (($currentPage ?? '') === 'qr') ? 'active' : '' ?>">
                    <svg class="sidebar-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/>
                        <rect x="3" y="14" width="7" height="7" rx="1"/>
                        <path d="M14 14h2v2h-2zM18 14h3v3M14 18h3v3M18 18h3v3"/>
                    </svg>
                    <span class="sidebar-text">Scan QR</span>
                </a>
            </li>
            <?php endif; ?>

            <li class="sidebar-item">
                <a href="?page=leaderboard" class="sidebar-link <?= ($currentPage ?? '') === 'leaderboard' ? 'active' : '' ?>">
                    <svg class="sidebar-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="8" r="7"></circle>
                        <polyline points="8.21 13.89 7 23 12 20 17 23 15.79 13.88"></polyline>
                    </svg>
                    <span class="sidebar-text">Leaderboard</span>
                </a>
            </li>
        </ul>
        
        <div class="sidebar-footer" style="padding: 15px; border-top: 1px solid rgba(252,251,255,0.1); display: flex; flex-direction: column; gap: 8px;">
            <div class="user-profile-badge" style="display: flex; align-items: center; gap: 10px;">
                <div class="user-avatar" style="width: 35px; height: 35px; border-radius: 50%; background-color: #80BCCB; color: #1D1F5A; display: flex; align-items: center; justify-content: center; font-weight: bold; font-size: 14px; flex-shrink: 0; text-transform: uppercase;">
                    <?= mb_substr($_SESSION['user_name'] ?? 'U', 0, 2) ?>
                </div>
                <div class="user-info" style="display: flex; flex-direction: column; min-width: 0;">
                    <span class="user-name" style="font-size: 13px; font-weight: bold; color: #FCFBFF; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;"><?= htmlspecialchars($_SESSION['user_name'] ?? '') ?></span>
                    <span class="user-role" style="font-size: 10px; color: #80BCCB; font-weight: 600; text-transform: uppercase;"><?= htmlspecialchars($_SESSION['user_role'] ?? '') ?></span>
                </div>
            </div>
            <a href="?page=auth&action=logout" class="sidebar-link logout-link" style="padding: 8px 10px; border-radius: 6px; font-size: 12px; color: #B61F24; display: flex; align-items: center; gap: 8px; text-decoration: none; font-weight: bold; transition: all 0.2s; background: rgba(182, 31, 36, 0.08);">
                <svg class="sidebar-icon" style="width: 16px; height: 16px; stroke: #B61F24;" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path>
                    <polyline points="16 17 21 12 16 7"></polyline>
                    <line x1="21" y1="12" x2="9" y2="12"></line>
                </svg>
                <span class="sidebar-text" style="color: #B61F24;">Logout</span>
            </a>
        </div>
    </nav>
    
    <!-- Mobile Header -->
    <header class="mobile-header">
        <button class="mobile-menu-toggle" aria-label="Toggle menu" onclick="toggleMobileMenu()">
            <span></span>
            <span></span>
            <span></span>
        </button>
        <span class="mobile-title">English Club</span>
    </header>
    
    <!-- Toast Notification Container -->
    <div class="toast-container" id="toastContainer"></div>
    
    <!-- Main Content Area -->
    <div class="main-content" id="mainContent">
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
