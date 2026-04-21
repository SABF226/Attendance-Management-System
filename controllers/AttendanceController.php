<?php
/**
 * Attendance Controller
 * Handles all session and attendance-related operations
 */

require_once __DIR__ . '/../models/AttendanceSession.php';
require_once __DIR__ . '/../models/AttendanceRecord.php';
require_once __DIR__ . '/../models/Member.php';

class AttendanceController {
    private $sessionModel;
    private $recordModel;
    private $memberModel;
    
    public function __construct() {
        $this->sessionModel = new AttendanceSession();
        $this->recordModel = new AttendanceRecord();
        $this->memberModel = new Member();
    }
    
    /**
     * Display all sessions with filtering
     */
    public function sessionIndex() {
        // Build filters from query parameters
        $filters = [];
        if (!empty($_GET['date_from'])) {
            $filters['date_from'] = $_GET['date_from'];
        }
        if (!empty($_GET['date_to'])) {
            $filters['date_to'] = $_GET['date_to'];
        }
        if (!empty($_GET['has_absences'])) {
            $filters['has_absences'] = true;
        }
        
        // Get sort parameter
        $sort = $_GET['sort'] ?? 'date_desc';
        
        // Get filtered sessions
        if (!empty($filters) || $sort !== 'date_desc') {
            $sessions = $this->sessionModel->getFiltered($filters, $sort);
        } else {
            $sessions = $this->sessionModel->getAll();
        }
        
        // Get stats for each session
        foreach ($sessions as &$session) {
            $stats = $this->sessionModel->getWithStats($session['id']);
            $session['present_count'] = $stats['present_count'] ?? 0;
            $session['absent_count'] = $stats['absent_count'] ?? 0;
            $session['excused_count'] = $stats['excused_count'] ?? 0;
            
            // Calculate attendance rate for sorting
            $total = $session['present_count'] + $session['absent_count'] + $session['excused_count'];
            $session['attendance_rate'] = $total > 0 ? ($session['present_count'] / $total) * 100 : 0;
        }
        
        // Sort by attendance rate if requested
        if ($sort === 'rate_asc') {
            usort($sessions, function($a, $b) {
                return $a['attendance_rate'] <=> $b['attendance_rate'];
            });
        } elseif ($sort === 'rate_desc') {
            usort($sessions, function($a, $b) {
                return $b['attendance_rate'] <=> $a['attendance_rate'];
            });
        }
        
        // Get monthly statistics
        $monthlyStats = $this->sessionModel->getMonthlyStats();
        
        return [
            'sessions' => $sessions,
            'monthlyStats' => $monthlyStats,
            'filters' => $filters,
            'sort' => $sort
        ];
    }
    
    /**
     * Show create session form
     */
    public function create() {
        return [
            'view' => 'views/sessions/create.php',
            'data' => [
                'session' => [],
                'breadcrumbs' => [
                    ['label' => 'Sessions', 'url' => '?page=sessions'],
                    ['label' => 'Create New Session']
                ]
            ]
        ];
    }
    
    /**
     * Store new session
     */
    public function storeSession($postData) {
        $errors = $this->validateSession($postData);
        
        if (!empty($errors)) {
            return [
                'view' => 'views/sessions/create.php',
                'data' => ['errors' => $errors, 'session' => $postData]
            ];
        }
        
        try {
            $this->sessionModel->create($postData);
            $_SESSION['message'] = 'Session created successfully!';
            $_SESSION['message_type'] = 'success';
            $this->redirect('?page=sessions');
        } catch (Exception $e) {
            return [
                'view' => 'views/sessions/create.php',
                'data' => ['errors' => ['Failed to create session: ' . $e->getMessage()], 'session' => $postData]
            ];
        }
    }
    
    /**
     * Delete session
     */
    public function deleteSession($id) {
        try {
            $this->sessionModel->delete($id);
            $_SESSION['message'] = 'Session deleted successfully!';
            $_SESSION['message_type'] = 'success';
        } catch (Exception $e) {
            $_SESSION['message'] = 'Failed to delete session!';
            $_SESSION['message_type'] = 'error';
        }
        
        $this->redirect('?page=sessions');
    }
    
    /**
     * Show take attendance form
     */
    public function takeAttendance($sessionId) {
        $session = $this->sessionModel->getById($sessionId);
        
        if (!$session) {
            $_SESSION['message'] = 'Session not found!';
            $_SESSION['message_type'] = 'error';
            $this->redirect('?page=sessions');
        }
        
        $members = $this->memberModel->getAll();
        $records = $this->recordModel->getBySession($sessionId);
        
        // Index records by member_id for easy lookup
        $attendanceRecords = [];
        foreach ($records as $record) {
            $attendanceRecords[$record['member_id']] = $record;
        }
        
        return [
            'view' => 'views/sessions/take_attendance.php',
            'data' => [
                'session' => $session,
                'members' => $members,
                'attendanceRecords' => $attendanceRecords,
                'breadcrumbs' => [
                    ['label' => 'Sessions', 'url' => '?page=sessions'],
                    ['label' => $session['session_name'] ?? 'Take Attendance']
                ]
            ]
        ];
    }
    
    /**
     * Save attendance records
     */
    public function saveAttendance($sessionId, $postData) {
        $attendance = $postData['attendance'] ?? [];
        
        if (empty($attendance)) {
            $_SESSION['message'] = 'No attendance data provided!';
            $_SESSION['message_type'] = 'error';
            $this->redirect('?page=sessions&action=take&id=' . $sessionId);
        }
        
        try {
            $this->recordModel->saveBatch($sessionId, $attendance);
            $_SESSION['message'] = 'Attendance saved successfully!';
            $_SESSION['message_type'] = 'success';
        } catch (Exception $e) {
            $_SESSION['message'] = 'Failed to save attendance: ' . $e->getMessage();
            $_SESSION['message_type'] = 'error';
        }
        
        $this->redirect('?page=sessions&action=take&id=' . $sessionId);
    }
    
    /**
     * View session details
     */
    public function viewSession($sessionId) {
        $session = $this->sessionModel->getWithStats($sessionId);
        
        if (!$session) {
            $_SESSION['message'] = 'Session not found!';
            $_SESSION['message_type'] = 'error';
            $this->redirect('?page=sessions');
        }
        
        $records = $this->recordModel->getBySession($sessionId);
        
        return [
            'view' => 'views/sessions/view.php',
            'data' => [
                'session' => $session,
                'records' => $records,
                'breadcrumbs' => [
                    ['label' => 'Sessions', 'url' => '?page=sessions'],
                    ['label' => $session['session_name'] ?? 'Session Details']
                ]
            ]
        ];
    }
    
    /**
     * Export session to PDF or Excel
     */
    public function exportSession($sessionId, $format) {
        $session = $this->sessionModel->getById($sessionId);
        
        if (!$session) {
            $_SESSION['message'] = 'Session not found!';
            $_SESSION['message_type'] = 'error';
            $this->redirect('?page=sessions');
        }
        
        $records = $this->recordModel->getBySession($sessionId);
        
        if ($format === 'pdf') {
            $this->exportToPdf($session, $records);
        } elseif ($format === 'excel') {
            $this->exportToExcel($session, $records);
        } else {
            $_SESSION['message'] = 'Invalid export format!';
            $_SESSION['message_type'] = 'error';
            $this->redirect('?page=sessions');
        }
    }
    
    /**
     * Helper method to redirect
     */
    private function redirect($url) {
        if (headers_sent($file, $line)) {
            echo "<script>window.location.href='$url';</script>";
            exit;
        }
        header('Location: ' . $url);
        exit;
    }
    
    /**
     * Export to PDF
     */
    /**
     * Export to PDF
     */
    private function exportToPdf($session, $records) {
        if (!file_exists(__DIR__ . '/../fpdf/fpdf.php')) {
            die("Error: FPDF not found. Please ensure fpdf.php has been downloaded.");
        }
        
        require_once __DIR__ . '/../fpdf/fpdf.php';
        
        $pdf = new \FPDF('P', 'mm', 'A4');
        $pdf->SetCreator('SaBf GraphiTech');
        $pdf->SetAuthor('English Club');
        $pdf->SetTitle('Attendance List - ' . htmlspecialchars($session['session_name'] ?? 'Session'));
        
        $pdf->AddPage();
        
        // --- Header Section ---
        $logoPath = __DIR__ . '/../assets/logo_bit_en.jpg';
        if (file_exists($logoPath)) {
            $pdf->Image($logoPath, 15, 10, 30);
        }
        
        $pdf->SetFont('Arial', 'B', 16);
        $pdf->SetTextColor(29, 31, 90); // #1D1F5A
        $pdf->Cell(0, 10, 'Attendance List', 0, 1, 'C');
        
        $pdf->SetFont('Arial', 'B', 14);
        $pdf->SetTextColor(45, 47, 122); // #2D2F7A
        $pdf->Cell(0, 8, iconv('UTF-8', 'ISO-8859-1//TRANSLIT', $session['session_name'] ?? ''), 0, 1, 'C');
        $pdf->Ln(5);
        
        // Session Metadata
        $pdf->SetFont('Arial', '', 11);
        $pdf->SetTextColor(0, 0, 0);
        
        $dateFormatted = date('F d, Y', strtotime($session['session_date'] ?? 'now'));
        $timeFormatted = !empty($session['session_time']) ? date('H:i', strtotime($session['session_time'])) : 'N/A';
        $team = !empty($session['session_team']) ? $session['session_team'] : 'N/A';
        
        $pdf->Cell(70, 6, 'Date: ' . $dateFormatted, 0, 0, 'L');
        $pdf->Cell(60, 6, 'Time: ' . $timeFormatted, 0, 0, 'L');
        $pdf->Cell(60, 6, "Session's Team: " . iconv('UTF-8', 'ISO-8859-1//TRANSLIT', $team), 0, 1, 'L');
        $pdf->Ln(5);
        
        // --- Body Section (Table) ---
        $pdf->SetFillColor(29, 31, 90);
        $pdf->SetTextColor(252, 251, 255);
        $pdf->SetFont('Arial', 'B', 10);
        
        $w = [45, 45, 55, 20, 25];
        $pdf->Cell($w[0], 7, 'Name', 1, 0, 'C', true);
        $pdf->Cell($w[1], 7, 'Field', 1, 0, 'C', true);
        $pdf->Cell($w[2], 7, 'Email', 1, 0, 'C', true);
        $pdf->Cell($w[3], 7, 'Status', 1, 0, 'C', true);
        $pdf->Cell($w[4], 7, 'Notes', 1, 1, 'C', true);
        
        $pdf->SetFont('Arial', '', 9);
        
        foreach ($records as $record) {
            $status = $record['status'] ?? 'present';
            $name = iconv('UTF-8', 'ISO-8859-1//TRANSLIT', substr($record['name'] ?? '', 0, 25));
            $field = iconv('UTF-8', 'ISO-8859-1//TRANSLIT', substr($record['field'] ?? '', 0, 25));
            $email = iconv('UTF-8', 'ISO-8859-1//TRANSLIT', substr($record['email'] ?? '', 0, 30));
            $notes = iconv('UTF-8', 'ISO-8859-1//TRANSLIT', substr($record['notes'] ?? '-', 0, 15));
            
            $pdf->SetTextColor(0, 0, 0); 
            $pdf->Cell($w[0], 6, $name, 1, 0, 'L');
            $pdf->Cell($w[1], 6, $field, 1, 0, 'L');
            $pdf->Cell($w[2], 6, $email, 1, 0, 'L');
            
            if ($status === 'present') {
                 $pdf->SetFillColor(128, 188, 203);
                 $pdf->SetTextColor(29, 31, 90);
                 $pdf->Cell($w[3], 6, ucfirst($status), 1, 0, 'C', true);
            } elseif ($status === 'absent') {
                 $pdf->SetFillColor(182, 31, 36);
                 $pdf->SetTextColor(252, 251, 255);
                 $pdf->Cell($w[3], 6, ucfirst($status), 1, 0, 'C', true);
            } else {
                 $pdf->SetFillColor(252, 251, 255);
                 $pdf->SetTextColor(29, 31, 90);
                 $pdf->Cell($w[3], 6, ucfirst($status), 1, 0, 'C', true);
            }
            
            $pdf->SetTextColor(0, 0, 0);
            $pdf->Cell($w[4], 6, $notes, 1, 1, 'L');
        }
        
        $pdf->Ln(15);
        
        if ($pdf->GetY() > 240) {
            $pdf->AddPage();
        }
        
        $pdf->SetTextColor(0, 0, 0);
        $pdf->SetFont('Arial', 'B', 10);
        $pdf->Cell(60, 6, 'Chairman', 0, 0, 'C');
        $pdf->Cell(70, 6, 'General Secretary', 0, 0, 'C');
        $pdf->Cell(60, 6, "Session's Team Leader", 0, 1, 'C');
        
        $pdf->Ln(20);
        $y = $pdf->GetY();
        $pdf->Line(25, $y, 65, $y);
        $pdf->Line(90, $y, 140, $y);
        $pdf->Line(160, $y, 200, $y);
        
        $pdf->SetY(-15);
        $pdf->SetFont('Arial', 'I', 8);
        $pdf->Cell(0, 10, iconv('UTF-8', 'ISO-8859-1//TRANSLIT', '© 2026 SaBf GraphiTech. All rights reserved.'), 0, 0, 'R');
        
        if (ob_get_length()) ob_end_clean();
        
        $filename = 'BIT_English_Club_attendance_' . ($session['session_date'] ?? date('Y-m-d')) . '.pdf';
        $pdf->Output('D', $filename);
        exit;
    }

    /**
     * Export to Excel
     */
    private function exportToExcel($session, $records) {
        // Check if PhpSpreadsheet is available
        if (!file_exists(__DIR__ . '/../vendor/autoload.php')) {
            $this->exportToCsv($session, $records);
            return;
        }
        
        require_once __DIR__ . '/../vendor/autoload.php';
        
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        
        // Add logo
        $logoPath = __DIR__ . '/../assets/logo_bit_en.jpg';
        if (file_exists($logoPath)) {
            $drawing = new \PhpOffice\PhpSpreadsheet\Worksheet\Drawing();
            $drawing->setPath($logoPath);
            $drawing->setWidth(60);
            $drawing->setHeight(60);
            $drawing->setCoordinates('A1');
            $drawing->setOffsetX(10);
            $drawing->setOffsetY(10);
            $drawing->setWorksheet($sheet);
        }
        
        // Headers
        $sheet->setCellValue('A1', 'Attendance List');
        $sheet->setCellValue('A2', 'Session: ' . $session['session_name']);
        $sheet->setCellValue('A3', 'Date: ' . date('F d, Y', strtotime($session['session_date'])));
        
        // Column headers (shifted down if logo exists)
        $headerRow = file_exists($logoPath) ? 6 : 5;
        $sheet->setCellValue('A' . $headerRow, 'Name');
        $sheet->setCellValue('B' . $headerRow, 'Field');
        $sheet->setCellValue('C' . $headerRow, 'Email');
        $sheet->setCellValue('D' . $headerRow, 'Phone');
        $sheet->setCellValue('E' . $headerRow, 'Status');
        $sheet->setCellValue('F' . $headerRow, 'Notes');
        
        // Style header row with new color palette
        $sheet->getStyle('A' . $headerRow . ':F' . $headerRow)->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FCFBFF']],
            'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'color' => ['rgb' => '1D1F5A']],
            'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT]
        ]);
        
        // Data
        $row = $headerRow + 1;
        foreach ($records as $record) {
            $sheet->setCellValue('A' . $row, $record['name']);
            $sheet->setCellValue('B' . $row, $record['field']);
            $sheet->setCellValue('C' . $row, $record['email']);
            $sheet->setCellValue('D' . $row, $record['phone']);
            $sheet->setCellValue('E' . $row, ucfirst($record['status']));
            $sheet->setCellValue('F' . $row, $record['notes'] ?? '');
            
            // Apply status colors
            $statusBgColor = '80BCCB';
            $statusTextColor = '1D1F5A';
            if ($record['status'] === 'absent') {
                $statusBgColor = 'B61F24';
                $statusTextColor = 'FCFBFF';
            } elseif ($record['status'] === 'excused') {
                $statusBgColor = 'FCFBFF';
                $statusTextColor = '1D1F5A';
            }
            $sheet->getStyle('E' . $row)->applyFromArray([
                'font' => ['color' => ['rgb' => $statusTextColor]],
                'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'color' => ['rgb' => $statusBgColor]]
            ]);
            
            $row++;
        }
        
        // Auto-size columns
        foreach (range('A', 'F') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }
        
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="attendance_' . $session['session_date'] . '.xlsx"');
        
        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $writer->save('php://output');
        exit;
    }
    
    /**
     * Export to CSV (fallback)
     */
    private function exportToCsv($session, $records) {
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="attendance_' . $session['session_date'] . '.csv"');
        
        $output = fopen('php://output', 'w');
        
        // BOM for UTF-8
        fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
        
        // Title info
        fputcsv($output, ['English Club Attendance List']);
        fputcsv($output, ['Session: ' . $session['session_name']]);
        fputcsv($output, ['Date: ' . date('F d, Y', strtotime($session['session_date']))]);
        fputcsv($output, []); // Empty row
        
        // Headers
        fputcsv($output, ['Name', 'Field', 'Email', 'Phone', 'Status', 'Notes']);
        
        foreach ($records as $record) {
            fputcsv($output, [
                $record['name'],
                $record['field'],
                $record['email'],
                $record['phone'],
                ucfirst($record['status']),
                $record['notes'] ?? ''
            ]);
        }
        
        fclose($output);
        exit;
    }
    
    /**
     * Validate session data
     */
    private function validateSession($data) {
        $errors = [];
        
        if (empty(trim($data['session_name'] ?? ''))) {
            $errors[] = 'Session name is required';
        }
        
        if (empty(trim($data['session_date'] ?? ''))) {
            $errors[] = 'Session date is required';
        } elseif (!strtotime($data['session_date'])) {
            $errors[] = 'Invalid date format';
        }
        
        return $errors;
    }
}

