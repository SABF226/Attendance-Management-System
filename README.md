# English Club Attendance List

A comprehensive web application for managing English Club members and tracking attendance.

## Features

### Member Management
- **Add Members**: Add new members with name, field of study, phone, and email
- **Edit Members**: Modify existing member information
- **Delete Members**: Remove members from the system
- **Search Members**: Find members by name or email
- **View Member List**: See all members in a sortable table

### Attendance Sessions
- **Create Sessions**: Create new attendance sessions with date and name
- **Take Attendance**: Mark members as present, absent, or excused
- **View Session Details**: See attendance records for each session
- **Delete Sessions**: Remove sessions and all associated records

### Export Functionality
- **PDF Export**: Export attendance list to PDF format
- **Excel Export**: Export attendance list to Excel (.xlsx) format
- **CSV Export**: Fallback CSV export if libraries not installed

## Requirements

- PHP 8.0+
- MySQL 5.7+
- Web server (Apache/Nginx)

## Installation

### 1. Database Setup

#### Option A: Using the Setup Script (Recommended)
1. Upload all files to your web server
2. Navigate to `setup.php` in your browser (e.g., `http://localhost/attendance-list/setup.php`)
3. Follow the on-screen instructions
4. **Important**: Delete `setup.php` after setup for security

### Option B: Manual Setup
1. Import the SQL schema from `config/schema.sql` into your MySQL database
2. Update database credentials in `config/database.php`:
   ```php
   private $host = 'localhost';
   private $dbname = 'english_club';
   private $username = 'Chair';
   private $password = 'xxxx';
   ```

### 2. (Optional) Install Export Libraries

For full PDF and Excel export functionality:

```bash
cd /var/www/html/attendance-list
composer require tecnickcom/tcpdf phpoffice/phpspreadsheet
```

Without these libraries, the app will still work with simplified HTML/PDF and CSV exports.

## Usage

### Access the Application
Open your browser and navigate to the application URL:
- `http://localhost/attendance-list/index.php`

### Dashboard
The dashboard shows:
- Total number of members
- Total number of sessions
- Overall attendance statistics
- Recent sessions

### Managing Members
1. Click "Members" in the navigation
2. To add a new member: Click "+ Add New Member"
3. To edit a member: Click "Edit" next to the member
4. To delete a member: Click "Delete" (confirmation required)
5. Use the search box to find members by name or email

### Managing Sessions
1. Click "Sessions" in the navigation
2. To create a new session: Click "+ Create New Session"
3. To take attendance: Click "Take Attendance" next to a session
4. Mark each member as Present, Absent, or Excused
5. Click "Save Attendance" to record the attendance
6. To view session details: Click "View"
7. To export: Click "PDF" or "Excel" buttons

### Exporting Attendance
From the Sessions page:
- **PDF**: Click "PDF" to download a PDF version
- **Excel**: Click "Excel" to download an Excel file

## File Structure

```
attendance-list/
├── config/
│   ├── database.php      # Database configuration
│   └── schema.sql        # SQL schema (backup)
├── models/
│   ├── Member.php        # Member data model
│   ├── AttendanceSession.php
│   └── AttendanceRecord.php
├── controllers/
│   ├── MemberController.php
│   └── AttendanceController.php
├── views/
│   ├── header.php        # Common header
│   ├── footer.php        # Common footer
│   ├── dashboard.php     # Dashboard view
│   ├── members/
│   │   ├── index.php     # Members list
│   │   └── form.php      # Add/Edit form
│   └── sessions/
│       ├── index.php     # Sessions list
│       ├── create.php    # Create session
│       ├── take_attendance.php
│       └── view.php      # Session details
├── assets/
│   ├── css/
│   │   └── style.css
│   └── js/
│       └── main.js
├── index.php             # Main entry point
├── setup.php             # Database setup script
├── TODO.md               # Implementation progress
└── implementation_plan.md
```

## Database Schema

### members
| Column | Type | Description |
|--------|------|-------------|
| id | INT | Primary key |
| name | VARCHAR(100) | Member's full name |
| field | VARCHAR(100) | Field of study |
| phone | VARCHAR(20) | Phone number |
| email | VARCHAR(100) | Email (unique) |
| created_at | TIMESTAMP | Creation date |
| updated_at | TIMESTAMP | Last update |

### attendance_sessions
| Column | Type | Description |
|--------|------|-------------|
| id | INT | Primary key |
| session_date | DATE | Session date |
| session_name | VARCHAR(100) | Session name |
| created_at | TIMESTAMP | Creation date |

### attendance_records
| Column | Type | Description |
|--------|------|-------------|
| id | INT | Primary key |
| session_id | INT | Foreign key to sessions |
| member_id | INT | Foreign key to members |
| status | ENUM | present/absent/excused |
| notes | TEXT | Optional notes |
| created_at | TIMESTAMP | Creation date |

## Troubleshooting

### Database Connection Error
- Check that MySQL is running
- Verify credentials in `config/database.php`
- Ensure the database exists

### Export Not Working
- Install the required composer packages (see Installation step 2)
- Check file permissions

### Session Errors
- Make sure PHP has write permissions for session files
- Check PHP error logs

## License

This project is for educational purposes.

