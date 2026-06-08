<?php
/**
 * Auth View - Standalone Page
 * Implements sliding Sign In & Sign Up container with custom BIT English Club aesthetic
 */

// Retrieve any flash messages
$errorMessage = $_SESSION['error_message'] ?? null;
$successMessage = $_SESSION['success_message'] ?? null;

// Retrieve any old input for registration
$oldInput = $_SESSION['old_input'] ?? [];

// Clear flash sessions
unset($_SESSION['error_message'], $_SESSION['success_message'], $_SESSION['old_input']);

// Determine which form should be active on load
$activeForm = $_GET['active'] ?? '';
$isRegisterActive = ($activeForm === 'register' || isset($oldInput['name'])) ? 'active' : '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle ?? 'Auth - BIT English Club') ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700;800&family=Outfit:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet">
    <link rel="icon" href="assets/logo_bit_en.jpg" type="image/jpeg">
    <style>
        :root {
            --primary: #1D1F5A;
            --primary-light: #3d419b;
            --secondary: #80BCCB;
            --danger: #B61F24;
            --dark-bg: #0d0e26;
            --panel-bg: #15173c;
            --white: #FCFBFF;
            --text-muted: #a3a6ce;
            --glass-bg: rgba(21, 23, 60, 0.7);
            --glass-border: rgba(128, 188, 203, 0.15);
            --transition: all 0.6s cubic-bezier(0.68, -0.55, 0.265, 1.55);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Montserrat', sans-serif;
        }

        body {
            background-color: var(--dark-bg);
            background-image: 
                radial-gradient(at 10% 20%, rgba(29, 31, 90, 0.4) 0px, transparent 50%),
                radial-gradient(at 90% 80%, rgba(182, 31, 36, 0.25) 0px, transparent 50%);
            display: flex;
            align-items: center;
            justify-content: center;
            flex-direction: column;
            min-height: 100vh;
            overflow-x: hidden;
            padding: 20px;
        }

        /* Branding Header */
        .brand-header {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 25px;
            z-index: 1001;
            animation: fadeInDown 0.8s ease-out;
        }

        .brand-logo {
            width: 50px;
            height: 50px;
            border-radius: 12px;
            object-fit: cover;
            box-shadow: 0 4px 15px rgba(128, 188, 203, 0.3);
            border: 2px solid var(--secondary);
        }

        .brand-title {
            font-family: 'Outfit', sans-serif;
            font-size: 24px;
            font-weight: 700;
            color: var(--white);
            letter-spacing: 0.5px;
            text-transform: uppercase;
        }

        .brand-title span {
            color: var(--secondary);
        }

        /* Toast Notifications */
        .alert-toast {
            position: fixed;
            top: 25px;
            right: 25px;
            z-index: 9999;
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 16px 24px;
            border-radius: 12px;
            color: var(--white);
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.4);
            font-size: 14px;
            font-weight: 500;
            transform: translateX(120%);
            transition: transform 0.5s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            max-width: 400px;
        }

        .alert-toast.show {
            transform: translateX(0);
        }

        .alert-toast.error {
            background-color: var(--danger);
            border-left: 5px solid #ff4d4d;
        }

        .alert-toast.success {
            background-color: #1a7a4a;
            border-left: 5px solid #2ecc71;
        }

        .alert-toast i {
            font-size: 20px;
        }

        .alert-toast-close {
            background: none;
            border: none;
            color: var(--white);
            cursor: pointer;
            font-size: 16px;
            margin-left: auto;
            opacity: 0.7;
            transition: opacity 0.2s;
        }

        .alert-toast-close:hover {
            opacity: 1;
        }

        /* Main Container */
        .container {
            background-color: var(--glass-bg);
            backdrop-filter: blur(16px);
            border: 1px solid var(--glass-border);
            border-radius: 30px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.3);
            position: relative;
            overflow: hidden;
            width: 850px;
            max-width: 100%;
            min-height: 520px;
            transition: var(--transition);
        }

        .container p {
            font-size: 14px;
            line-height: 22px;
            letter-spacing: 0.3px;
            margin: 15px 0 25px;
            color: var(--text-muted);
        }

        .container span {
            font-size: 12px;
            color: var(--text-muted);
            margin-bottom: 15px;
        }

        .container a {
            color: var(--secondary);
            font-size: 13px;
            text-decoration: none;
            margin: 15px 0 10px;
            transition: color 0.3s;
        }

        .container a:hover {
            color: var(--white);
            text-decoration: underline;
        }

        .container button {
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-light) 100%);
            color: var(--white);
            font-size: 12px;
            padding: 12px 45px;
            border: 1px solid transparent;
            border-radius: 10px;
            font-weight: 700;
            letter-spacing: 1px;
            text-transform: uppercase;
            margin-top: 15px;
            cursor: pointer;
            box-shadow: 0 4px 15px rgba(29, 31, 90, 0.4);
            transition: all 0.3s ease;
        }

        .container button:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(128, 188, 203, 0.3);
        }

        .container button:active {
            transform: translateY(0);
        }

        .container button.hidden {
            background-color: transparent;
            background-image: none;
            border: 2px solid var(--white);
            box-shadow: none;
        }

        .container button.hidden:hover {
            background-color: var(--white);
            color: var(--primary);
            box-shadow: 0 6px 20px rgba(255, 255, 255, 0.2);
        }

        .container form {
            background-color: transparent;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-direction: column;
            padding: 0 45px;
            height: 100%;
            text-align: center;
        }

        .container h1 {
            color: var(--white);
            font-family: 'Outfit', sans-serif;
            font-weight: 700;
            margin-bottom: 10px;
        }

        .input-group {
            position: relative;
            width: 100%;
            margin: 8px 0;
        }

        .input-group i {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--secondary);
            font-size: 16px;
        }

        .container input, .container select {
            background-color: rgba(29, 31, 90, 0.3);
            border: 1px solid var(--glass-border);
            color: var(--white);
            padding: 12px 15px 12px 45px;
            font-size: 14px;
            border-radius: 10px;
            width: 100%;
            outline: none;
            transition: all 0.3s ease;
        }

        .container input::placeholder {
            color: #71759f;
        }

        .container input:focus, .container select:focus {
            border-color: var(--secondary);
            background-color: rgba(29, 31, 90, 0.5);
            box-shadow: 0 0 10px rgba(128, 188, 203, 0.2);
        }

        .container select option {
            background-color: var(--panel-bg);
            color: var(--white);
        }

        .form-container {
            position: absolute;
            top: 0;
            height: 100%;
            transition: var(--transition);
        }

        .sign-in {
            left: 0;
            width: 50%;
            z-index: 2;
        }

        .container.active .sign-in {
            transform: translateX(100%);
            opacity: 0;
            z-index: 1;
        }

        .sign-up {
            left: 0;
            width: 50%;
            opacity: 0;
            z-index: 1;
        }

        .container.active .sign-up {
            transform: translateX(100%);
            opacity: 1;
            z-index: 5;
            animation: move 0.6s;
        }

        @keyframes move {
            0%, 49.99% {
                opacity: 0;
                z-index: 1;
            }
            50%, 100% {
                opacity: 1;
                z-index: 5;
            }
        }

        /* Toggle Panel Overlay */
        .toggle-container {
            position: absolute;
            top: 0;
            left: 50%;
            width: 50%;
            height: 100%;
            overflow: hidden;
            transition: var(--transition);
            border-radius: 150px 0 0 100px;
            z-index: 1000;
        }

        .container.active .toggle-container {
            transform: translateX(-100%);
            border-radius: 0 150px 100px 0;
        }

        .toggle {
            background: linear-gradient(135deg, var(--primary) 0%, var(--danger) 100%);
            height: 100%;
            color: var(--white);
            position: relative;
            left: -100%;
            width: 200%;
            transform: translateX(0);
            transition: var(--transition);
        }

        .container.active .toggle {
            transform: translateX(50%);
        }

        .toggle-panel {
            position: absolute;
            width: 50%;
            height: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-direction: column;
            padding: 0 40px;
            text-align: center;
            top: 0;
            transform: translateX(0);
            transition: var(--transition);
        }

        .toggle-left {
            transform: translateX(-200%);
        }

        .container.active .toggle-left {
            transform: translateX(0);
        }

        .toggle-right {
            right: 0;
            transform: translateX(0);
        }

        .container.active .toggle-right {
            transform: translateX(200%);
        }

        /* Footer Credits */
        .auth-footer {
            margin-top: 25px;
            color: var(--text-muted);
            font-size: 12px;
            letter-spacing: 0.5px;
            animation: fadeInUp 0.8s ease-out;
        }

        /* Animations */
        @keyframes fadeInDown {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Responsive Breakpoints */
        @media (max-width: 768px) {
            .container {
                min-height: 600px;
                display: flex;
                flex-direction: column;
            }

            .form-container {
                position: relative;
                width: 100% !important;
                height: 100% !important;
                transform: none !important;
                opacity: 1 !important;
                z-index: 2 !important;
            }

            .sign-in {
                display: block;
            }

            .sign-up {
                display: none;
            }

            .container.active .sign-in {
                display: none;
            }

            .container.active .sign-up {
                display: block;
                animation: none;
            }

            .toggle-container {
                display: none;
            }

            /* Responsive tab toggle directly inside form for mobile */
            .mobile-toggle-helper {
                display: block !important;
                margin-top: 20px;
                font-size: 13px;
                color: var(--text-muted);
            }

            .mobile-toggle-helper a {
                color: var(--secondary);
                font-weight: 600;
            }
        }

        .mobile-toggle-helper {
            display: none;
        }
    </style>
</head>
<body>

    <!-- Alerts Container -->
    <?php if ($errorMessage): ?>
        <div class="alert-toast error show" id="alertToast">
            <i class='bx bx-error-circle'></i>
            <span><?= htmlspecialchars($errorMessage) ?></span>
            <button class="alert-toast-close" onclick="closeToast()">&times;</button>
        </div>
    <?php endif; ?>

    <?php if ($successMessage): ?>
        <div class="alert-toast success show" id="alertToast">
            <i class='bx bx-check-circle'></i>
            <span><?= htmlspecialchars($successMessage) ?></span>
            <button class="alert-toast-close" onclick="closeToast()">&times;</button>
        </div>
    <?php endif; ?>

    <!-- Branding Logo Header -->
    <div class="brand-header">
        <img src="assets/logo_bit_en.jpg" alt="BIT English Club Logo" class="brand-logo">
        <h1 class="brand-title">BIT <span>English Club</span></h1>
    </div>

    <!-- Auth Slide Container -->
    <div class="container <?= $isRegisterActive ?>" id="container">
        
        <!-- Sign Up Form (Left / Slide transition) -->
        <div class="form-container sign-up">
            <form action="index.php?page=auth&action=register" method="POST">
                <?= Security::csrfField() ?>
                <h1>Create Account</h1>
                <span>Register to join the club & track attendance</span>
                
                <div class="input-group">
                    <i class='bx bx-user'></i>
                    <input type="text" name="name" placeholder="Full Name" value="<?= htmlspecialchars($oldInput['name'] ?? '') ?>" required>
                </div>
                
                <div class="input-group">
                    <i class='bx bx-envelope'></i>
                    <input type="email" name="email" placeholder="Email Address" value="<?= htmlspecialchars($oldInput['email'] ?? '') ?>" required>
                </div>
                
                <div class="input-group">
                    <i class='bx bx-phone'></i>
                    <input type="tel" name="phone" placeholder="Phone (e.g. +226 XX XX XX XX)" value="<?= htmlspecialchars($oldInput['phone'] ?? '') ?>" required>
                </div>
                
                <div class="input-group">
                    <i class='bx bx-book-open'></i>
                    <select name="field" required>
                        <option value="" disabled <?= empty($oldInput['field']) ? 'selected' : '' ?>>Select Field of Study</option>
                        <option value="Computer Science" <?= ($oldInput['field'] ?? '') === 'Computer Science' ? 'selected' : '' ?>>Computer Science (CS)</option>
                        <option value="Mechanical Engineering" <?= ($oldInput['field'] ?? '') === 'Mechanical Engineering' ? 'selected' : '' ?>>Mechanical Engineering (ME)</option>
                        <option value="Electrical Engineering" <?= ($oldInput['field'] ?? '') === 'Electrical Engineering' ? 'selected' : '' ?>>Electrical Engineering (EE)</option>
                        <option value="Business Administration" <?= ($oldInput['field'] ?? '') === 'Business Administration' ? 'selected' : '' ?>>Business Administration (BA)</option>
                        <option value="Other" <?= ($oldInput['field'] ?? '') === 'Other' ? 'selected' : '' ?>>Other</option>
                    </select>
                </div>
                
                <div class="input-group">
                    <i class='bx bx-lock-alt'></i>
                    <input type="password" name="password" placeholder="Create Password" required>
                </div>
                
                <div class="input-group">
                    <i class='bx bx-lock-check'></i>
                    <input type="password" name="confirm_password" placeholder="Confirm Password" required>
                </div>
                
                <button type="submit">Sign Up</button>
                
                <div class="mobile-toggle-helper">
                    Already a member? <a href="#" id="mobileLoginLink">Sign In</a>
                </div>
            </form>
        </div>

        <!-- Sign In Form (Right / Base layer) -->
        <div class="form-container sign-in">
            <form action="index.php?page=auth&action=login" method="POST">
                <?= Security::csrfField() ?>
                <h1>Sign In</h1>
                <span>Log in using your registered club email</span>
                
                <div class="input-group" style="margin-top: 20px;">
                    <i class='bx bx-envelope'></i>
                    <input type="email" name="email" placeholder="Email Address" required>
                </div>
                
                <div class="input-group">
                    <i class='bx bx-lock-alt'></i>
                    <input type="password" name="password" placeholder="Password" required>
                </div>
                
                <a href="#">Forgot Password?</a>
                
                <button type="submit">Sign In</button>
                
                <div class="mobile-toggle-helper">
                    New to the club? <a href="#" id="mobileRegisterLink">Register Now</a>
                </div>
            </form>
        </div>

        <!-- Toggle Panel Container (Slick Overlay) -->
        <div class="toggle-container">
            <div class="toggle">
                <!-- Left Slide: Switch to Sign In -->
                <div class="toggle-panel toggle-left">
                    <h1>Welcome Back!</h1>
                    <p>To keep connected with us, please sign in with your email and password.</p>
                    <button class="hidden" id="login">Sign In</button>
                </div>
                <!-- Right Slide: Switch to Sign Up -->
                <div class="toggle-panel toggle-right">
                    <h1>Hello, Friend!</h1>
                    <p>Register as an English Club member, build your attendance history, and see the top attendees!</p>
                    <button class="hidden" id="register">Sign Up</button>
                </div>
            </div>
        </div>
        
    </div>

    <!-- Footer Credits -->
    <div class="auth-footer">
        &copy; <?= date('Y') ?> BIT English Club Attendance Management. Made with ❤️
    </div>

    <script>
        const container = document.getElementById('container');
        const registerBtn = document.getElementById('register');
        const loginBtn = document.getElementById('login');
        
        // Mobile layout toggles
        const mobileRegisterLink = document.getElementById('mobileRegisterLink');
        const mobileLoginLink = document.getElementById('mobileLoginLink');

        // Sliding animations for desktop
        registerBtn.addEventListener('click', () => {
            container.classList.add("active");
            // Update URL query parameter
            const newurl = window.location.protocol + "//" + window.location.host + window.location.pathname + '?page=auth&active=register';
            window.history.pushState({path:newurl},'',newurl);
        });

        loginBtn.addEventListener('click', () => {
            container.classList.remove("active");
            // Update URL query parameter
            const newurl = window.location.protocol + "//" + window.location.host + window.location.pathname + '?page=auth';
            window.history.pushState({path:newurl},'',newurl);
        });

        // Toggles for mobile devices
        mobileRegisterLink.addEventListener('click', (e) => {
            e.preventDefault();
            container.classList.add("active");
        });

        mobileLoginLink.addEventListener('click', (e) => {
            e.preventDefault();
            container.classList.remove("active");
        });

        // Auto-dismiss alert toasts
        const alertToast = document.getElementById('alertToast');
        if (alertToast) {
            setTimeout(() => {
                closeToast();
            }, 6000);
        }

        function closeToast() {
            const toast = document.getElementById('alertToast');
            if (toast) {
                toast.classList.remove('show');
                setTimeout(() => {
                    toast.remove();
                }, 500);
            }
        }
    </script>
</body>
</html>
