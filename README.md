# 📊 BIT English Club Attendance System

> A modern, secure, and responsive web application for managing club members and tracking attendance sessions with real-time analytics and export capabilities.

<p align="center">
  <img src="assets/logo_bit_en.jpg" alt="BIT English Club" width="200">
</p>

[![PHP Version](https://img.shields.io/badge/PHP-8.0+-777BB4.svg?logo=php)](https://php.net)
[![MySQL Version](https://img.shields.io/badge/MySQL-5.7+-4479A1.svg?logo=mysql)](https://mysql.com)
[![License](https://img.shields.io/badge/License-Educational-green.svg)]()
[![Security](https://img.shields.io/badge/Security-Hardened-success.svg)]()

---

## ✨ Features

### 👥 Member Management
- **Member Profiles** - Store complete member information (name, field of study, contact details)
- **Quick Search** - Find members instantly by name or email
- **Member Attendance History** - View individual attendance records
- **Responsive Member Directory** - Card view on mobile, table view on desktop

### 📅 Attendance Sessions
- **Session Creation** - Create sessions with custom names and dates
- **Bulk Attendance** - Mark attendance for all members efficiently
- **Status Tracking** - Present, Absent, or Excused status options
- **Session Statistics** - View attendance rates per session
- **Advanced Filtering** - Filter sessions by date range and status

### 📊 Dashboard & Analytics
- **Attendance Trend Chart** - Visualize attendance over last 5 sessions
- **Status Distribution** - Pie chart showing present/absent/excused breakdown
- **Top Attendees** - List of most active members
- **Monthly Statistics** - Session counts and average attendance rates

### 📄 Export & Reports
- **PDF Export** - Professional attendance reports (via TCPDF)
- **Excel Export** - Data analysis ready spreadsheets (via PhpSpreadsheet)
- **CSV Export** - Universal compatibility fallback

### 🛡️ Security Features
- **CSRF Protection** - All forms protected against cross-site request forgery
- **Security Headers** - X-Frame-Options, CSP, XSS Protection, etc.
- **Rate Limiting** - Prevent abuse on critical actions (10-30 requests per 5 min)
- **Session Hardening** - HttpOnly, Secure, SameSite=Strict cookies
- **Input Sanitization** - XSS and SQL injection prevention
- **No Sensitive Data Leakage** - Error messages don't expose system details

### 📱 Responsive Design
- **Mobile-First** - Optimized for all screen sizes
- **Sidebar Navigation** - Collapsible on mobile, expanded on desktop
- **Touch-Friendly** - Large buttons and intuitive mobile interactions
- **Toast Notifications** - Non-intrusive success/error messages

---

## 🚀 Quick Start

### Prerequisites
- PHP 8.0 or higher
- MySQL 5.7 or higher
- Apache/Nginx web server
- Composer (optional, for export features)

### Installation

#### Option 1: Automated Setup (Recommended)

```bash
# 1. Clone or download to your web server
cd /var/www/html
git clone <repository-url> attendance-list

# 2. Set permissions
chmod 755 attendance-list
chmod -R 777 attendance-list/logs  # For error logging

# 3. Run web setup
# Open http://localhost/attendance-list/setup.php in browser
# Follow the guided setup wizard

# 4. Delete setup file for security
rm attendance-list/setup.php
```

#### Option 2: Manual Setup

```bash
# 1. Import database schema
mysql -u root -p < config/schema.sql

# 2. Configure database credentials
# Edit config/database.php with your credentials

# 3. (Optional) Install export libraries
composer require tecnickcom/tcpdf phpoffice/phpspreadsheet
```

---

## 📖 Usage Guide

### Dashboard Overview
The dashboard provides at-a-glance insights:
- **Key Metrics** - Total members, sessions, overall attendance rate
- **Visual Charts** - Trend lines, status distribution, top attendees
- **Recent Activity** - Quick access to latest sessions

### Managing Members
1. Navigate to **Members** in the sidebar
2. Click **+ Add New Member** to register a member
3. Use the **Search** box to find existing members
4. Click member names to view detailed attendance history
5. Edit or delete members via action dropdowns

### Taking Attendance
1. Go to **Sessions** → Click **Take Attendance** on a session
2. Use bulk actions (**Mark All Present**, **Reset All**) for efficiency
3. Individual member status can be toggled via status buttons
4. **Keyboard Shortcuts**: P (Present), A (Absent), E (Excused)
5. Click **Save Attendance** to record

### Exporting Data
From the Sessions list:
- Click **PDF** or **Excel** buttons next to any session
- Or use **Filter** options to export specific date ranges

---

## 🏗️ Architecture

### Technology Stack
- **Backend**: PHP 8.0+ (MVC Pattern)
- **Database**: MySQL 5.7+
- **Frontend**: Vanilla JavaScript, Chart.js for analytics
- **Styling**: Custom CSS with CSS Variables
- **Security**: CSRF tokens, Security headers, Rate limiting

### Project Structure
```
attendance-list/
├── 📁 api/                    # REST API endpoints
│   └── dashboard_stats.php    # Chart data API
├── 📁 assets/
│   ├── 📁 css/style.css      # Main stylesheet
│   ├── 📁 js/                # JavaScript files
│   │   ├── main.js           # Global utilities
│   │   ├── dashboard.js      # Chart initialization
│   │   └── chart.umd.min.js  # Local Chart.js
│   └── logo_bit_en.jpg       # Club logo
├── 📁 config/
│   ├── database.php          # Database configuration
│   ├── security.php          # Security headers & session config
│   └── schema.sql            # Database schema
├── 📁 controllers/
│   ├── MemberController.php  # Member CRUD operations
│   └── AttendanceController.php  # Session & attendance management
├── 📁 helpers/
│   └── Security.php          # Security utilities (CSRF, rate limiting)
├── 📁 models/
│   ├── Member.php            # Member data model
│   ├── AttendanceSession.php # Session model with filtering
│   └── AttendanceRecord.php  # Attendance records & analytics
├── 📁 views/
│   ├── header.php            # Common header with sidebar
│   ├── footer.php            # Common footer with modals
│   ├── dashboard.php         # Dashboard with charts
│   ├── 📁 members/           # Member views
│   └── 📁 sessions/          # Session views
├── 📄 index.php              # Application entry point & router
├── 📄 SECURITY.md            # Security documentation
└── 📄 README.md              # This file
```

---

## 🔐 Security Implementation

| Feature | Implementation | Status |
|---------|---------------|--------|
| **CSRF Protection** | Token-based validation on all POST requests | ✅ |
| **SQL Injection** | PDO prepared statements throughout | ✅ |
| **XSS Prevention** | `htmlspecialchars()` on all output | ✅ |
| **Security Headers** | CSP, X-Frame-Options, X-XSS-Protection | ✅ |
| **Rate Limiting** | Session-based limiting per action type | ✅ |
| **Session Security** | HttpOnly, Secure, SameSite=Strict, Auto-regeneration | ✅ |
| **Input Validation** | Type casting and length limits | ✅ |
| **Error Handling** | No sensitive data in error messages | ✅ |

See [SECURITY.md](SECURITY.md) for detailed security documentation and test commands.

---

## 🛠️ Troubleshooting

### Common Issues

| Problem | Solution |
|---------|----------|
| **Database connection failed** | Check `config/database.php` credentials and ensure MySQL is running |
| **Charts not loading** | Ensure `assets/js/chart.umd.min.js` exists (downloaded locally) |
| **PDF/Excel export not working** | Run `composer install` to install TCPDF and PhpSpreadsheet |
| **Session errors** | Check PHP has write permissions to session directory |
| **Permission denied on setup** | Ensure web server user has read/write access to the directory |

### Debug Mode
For development debugging, check the browser console (F12) and PHP error logs:
```bash
tail -f /var/log/apache2/error.log
# or
tail -f /var/www/html/attendance-list/logs/api_errors.log
```

---

## 📝 Roadmap

**Delivered**
- [x] **User Authentication** - Login system with role-based access (admin / member)
- [x] **QR Code Check-In** - Self-service attendance via expiring per-session QR codes
- [x] **Gamification** - XP points and a top-attendees leaderboard

**Planned**
- [ ] **Email Notifications** - Attendance reminders and reports
- [ ] **Advanced Analytics** - Per-member stats done; trends **by field of study** still pending
- [ ] **Multi-language Support** - English and other languages
- [ ] **Mobile App** - PWA or native mobile application
- [ ] **Backup & Restore** - Database backup functionality

> 🔭 A full rebuild of this app on the MERN stack is planned — see [MERN_REDESIGN.md](MERN_REDESIGN.md) for the design and phased implementation plan.

---

## 🤝 Contributing

Contributions are welcome! Please follow these guidelines:

1. **Fork** the repository
2. Create a **feature branch** (`git checkout -b feature/amazing-feature`)
3. **Commit** your changes (`git commit -m 'Add amazing feature'`)
4. **Push** to the branch (`git push origin feature/amazing-feature`)
5. Open a **Pull Request**

### Code Standards
- Follow PSR-12 coding standards for PHP
- Use meaningful variable and function names
- Add comments for complex logic
- Test security implications of changes

---

## 📄 License

This project is developed for **educational purposes** by the BIT English Club.

---

## 🙏 Acknowledgments

- Built for **Beijing Institute of Technology English Club**
- Icons by [Phosphor Icons](https://phosphoricons.com/)
- Charts powered by [Chart.js](https://www.chartjs.org/)
- Export functionality via [TCPDF](https://tcpdf.org/) and [PhpSpreadsheet](https://phpspreadsheet.readthedocs.io/)

---

<p align="center">
  <strong>Made with ❤️ for BIT English Club</strong>
</p>

