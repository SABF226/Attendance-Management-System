# Implementation Plan

## English Club Attendance List - Implementation Plan

[Overview]
Transform the existing basic student directory into a comprehensive English Club Attendance List management system with member CRUD operations, attendance session tracking, and export capabilities (PDF/Excel).

The application will be rebuilt using a modular PHP structure with separate files for database operations, views, and controllers to ensure maintainability and scalability.

---

[Types]

### Database Schema

**Table: members**
| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| id | INT | PRIMARY KEY, AUTO_INCREMENT | Unique member ID |
| name | VARCHAR(100) | NOT NULL | Member's full name |
| field | VARCHAR(100) | NOT NULL | Field of study/interest |
| phone | VARCHAR(20) | NOT NULL | Phone number |
| email | VARCHAR(100) | NOT NULL, UNIQUE | Email address |
| created_at | TIMESTAMP | DEFAULT CURRENT_TIMESTAMP | Record creation date |
| updated_at | TIMESTAMP | DEFAULT CURRENT_TIMESTAMP ON UPDATE | Last update date |

**Table: attendance_sessions**
| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| id | INT | PRIMARY KEY, AUTO_INCREMENT | Session ID |
| session_date | DATE | NOT NULL | Date of attendance session |
| session_name | VARCHAR(100) | NOT NULL | Name/description of session |
| created_at | TIMESTAMP | DEFAULT CURRENT_TIMESTAMP | Record creation date |

**Table: attendance_records**
| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| id | INT | PRIMARY KEY, AUTO_INCREMENT | Record ID |
| session_id | INT | FOREIGN KEY -> attendance_sessions(id) | Reference to session |
| member_id | INT | FOREIGN KEY -> members(id) | Reference to member |
| status | ENUM('present','absent','excused') | DEFAULT 'present' | Attendance status |
| notes | TEXT | NULLABLE | Optional notes |
| created_at | TIMESTAMP | DEFAULT CURRENT_TIMESTAMP | Record creation date |

**Unique Constraint:** (session_id, member_id) - a member can only have one record per session

---

[Files]

### New Files to Create

1. **config/database.php** - Database configuration and connection
2. **models/Member.php** - Member model class with CRUD methods
3. **models/AttendanceSession.php** - Session model class
4. **models/AttendanceRecord.php** - Attendance record model
5. **controllers/MemberController.php** - Member management logic
6. **controllers/AttendanceController.php** - Attendance management logic
7. **views/header.php** - Common header/navigation
8. **views/footer.php** - Common footer
9. **views/members/index.php** - Members list view
10. **views/members/form.php** - Add/Edit member form
11. **views/sessions/index.php** - Sessions list view
12. **views/sessions/create.php** - Create new session
13. **views/sessions/take_attendance.php** - Take attendance for a session
14. **views/export/pdf.php** - PDF export template
15. **views/export/excel.php** - Excel export logic
16. **index.php** - Main dashboard
17. **assets/css/style.css** - Main stylesheet
18. **assets/js/main.js** - Frontend JavaScript

### Files to Modify

1. **etudiant.php** - Archive or repurpose as legacy file

### Files to Delete

1. None (backup original etudiant.php)

---

[Functions]

### New Functions

**Member.php**
- `getAll()` - Fetch all members ordered by name
- `getById($id)` - Fetch single member by ID
- `create($data)` - Create new member
- `update($id, $data)` - Update existing member
- `delete($id)` - Delete member (soft or hard)
- `search($query)` - Search members by name/email

**AttendanceSession.php**
- `getAll()` - Fetch all sessions ordered by date DESC
- `getById($id)` - Fetch single session with attendance records
- `create($data)` - Create new session
- `delete($id)` - Delete session and associated records

**AttendanceRecord.php**
- `getBySession($sessionId)` - Get all attendance records for a session
- `setAttendance($sessionId, $memberId, $status, $notes)` - Set/update attendance
- `getMemberStats($memberId)` - Get attendance statistics for a member

**MemberController.php**
- `index()` - Display all members
- `store($postData)` - Create new member
- `edit($id)` - Display edit form
- `update($id, $postData)` - Update member
- `destroy($id)` - Delete member

**AttendanceController.php**
- `sessionIndex()` - List all sessions
- `storeSession($postData)` - Create new session
- `takeAttendance($sessionId)` - Display attendance form
- `saveAttendance($sessionId, $postData)` - Save attendance records
- `exportSession($sessionId, $format)` - Export session to PDF/Excel

### Modified Functions

None - all new implementation

### Removed Functions

None

---

[Classes]

### New Classes

**Database** (in config/database.php)
- Singleton pattern for PDO connection
- Methods: `getConnection()`, `query()`, `execute()`

**Member** (in models/Member.php)
- Data access layer for members table
- Properties: id, name, field, phone, email, created_at, updated_at

**AttendanceSession** (in models/AttendanceSession.php)
- Data access layer for sessions table
- Properties: id, session_date, session_name, created_at

**AttendanceRecord** (in models/AttendanceRecord.php)
- Data access layer for attendance records
- Properties: id, session_id, member_id, status, notes, created_at

### Modified Classes

None

### Removed Classes

None

---

[Dependencies]

### PHP Dependencies (Composer)
- **tecnickcom/tcpdf** - PDF generation library (^6.6)
- **phpoffice/phpspreadsheet** - Excel/CSV export library (^1.29)
- **phpmailer/phpmailer** - Email sending (optional, for notifications) (^6.8)

### System Requirements
- PHP 8.0+
- MySQL 5.7+
- Apache/Nginx web server

### Installation Commands
```bash
cd /var/www/html/attendance-list
composer require tecnickcom/tcpdf phpoffice/phpspreadsheet
```

---

[Testing]

### Testing Approach

1. **Manual Testing**
   - Test all CRUD operations for members
   - Test session creation and attendance taking
   - Test PDF and Excel export functionality

2. **Validation Points**
   - Form validation (required fields, email format, phone format)
   - Database integrity (foreign keys, unique constraints)
   - Export file generation and download
   - Session attendance marking accuracy

---

[Implementation Order]

1. **Step 1:** Create database schema and config/database.php
2. **Step 2:** Create Member model with basic CRUD
3. **Step 3:** Create views for member management (list, add, edit, delete)
4. **Step 4:** Create MemberController and wire up routes
5. **Step 5:** Implement AttendanceSession model
6. **Step 6:** Implement AttendanceRecord model
7. **Step 7:** Create session management views
8. **Step 8:** Create attendance taking interface
9. **Step 9:** Implement PDF export functionality
10. **Step 10:** Implement Excel export functionality
11. **Step 11:** Create dashboard/index page
12. **Step 12:** Add styling and JavaScript
13. **Step 13:** Test and validate entire flow
14. **Step 14:** Document usage

---

[Notes]

- The original etudiant.php will be archived as it uses a different database/table structure
- New database will be named: `english_club`
- Session-based navigation or simple PHP includes will be used (no framework)
- Export will include both member details and their attendance status per session
- All user inputs will be sanitized to prevent XSS and SQL injection

