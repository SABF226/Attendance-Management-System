<?php
/**
 * AttendancePdf
 *
 * FPDF subclass for the generated attendance document. Centralises the
 * page geometry (margins), the repeating page header/footer and the
 * repeating table column header so long attendance lists paginate cleanly
 * across multiple pages.
 */

if (!class_exists('FPDF')) {
    require_once __DIR__ . '/../fpdf/fpdf.php';
}

class AttendancePdf extends \FPDF
{
    /** Brand colours (RGB). */
    public const BRAND_DARK   = [29, 31, 90];    // #1D1F5A
    public const BRAND_ACCENT = [45, 47, 122];   // #2D2F7A
    public const BRAND_LIGHT  = [252, 251, 255];  // #FCFBFF
    public const PRESENT_BG   = [128, 188, 203];
    public const ABSENT_BG    = [182, 31, 36];

    /** Table column widths (mm) — must sum to the printable width. */
    public const COL_WIDTHS  = [42, 38, 52, 22, 26]; // total 180
    public const COL_HEADERS = ['Name', 'Field', 'Email', 'Status', 'Notes'];

    public const ROW_HEIGHT    = 6;
    public const HEADER_HEIGHT = 7;

    private string $reportTitle = 'Attendance List';
    private string $sessionName = '';
    private string $logoPath    = '';

    /** Configure document-level metadata shown in the repeating header. */
    public function configure(string $reportTitle, string $sessionName, string $logoPath): void
    {
        $this->reportTitle = $reportTitle;
        $this->sessionName = $this->latin($sessionName);
        $this->logoPath    = $logoPath;
    }

    /** Convert UTF-8 to the Latin-1 subset FPDF core fonts can render. */
    public function latin(string $text): string
    {
        $converted = @iconv('UTF-8', 'ISO-8859-1//TRANSLIT', $text);
        return $converted === false ? $text : $converted;
    }

    /** Repeating page header: logo, title, session name, divider. */
    public function Header(): void
    {
        if ($this->logoPath !== '' && file_exists($this->logoPath)) {
            $this->Image($this->logoPath, $this->lMargin, $this->tMargin, 28);
        }

        $this->SetY($this->tMargin);
        $this->SetFont('Arial', 'B', 16);
        $this->SetTextColor(...self::BRAND_DARK);
        $this->Cell(0, 10, $this->reportTitle, 0, 1, 'C');

        if ($this->sessionName !== '') {
            $this->SetFont('Arial', 'B', 13);
            $this->SetTextColor(...self::BRAND_ACCENT);
            $this->Cell(0, 8, $this->sessionName, 0, 1, 'C');
        }

        $this->Ln(2);
        $y = $this->GetY();
        $this->SetDrawColor(...self::BRAND_ACCENT);
        $this->SetLineWidth(0.3);
        $this->Line($this->lMargin, $y, $this->w - $this->rMargin, $y);
        $this->SetLineWidth(0.2);
        $this->Ln(4);
    }

    /** Repeating page footer: page numbering and copyright. */
    public function Footer(): void
    {
        $this->SetY(-15);
        $this->SetFont('Arial', 'I', 8);
        $this->SetTextColor(120, 120, 120);
        $this->Cell(0, 10, 'Page ' . $this->PageNo() . '/{nb}', 0, 0, 'L');
        $this->Cell(0, 10, $this->latin('© 2026 SaBf GraphiTech. All rights reserved.'), 0, 0, 'R');
    }

    /** Draw the table column header row. Re-called after each page break. */
    public function tableHeader(): void
    {
        $this->SetFillColor(...self::BRAND_DARK);
        $this->SetTextColor(...self::BRAND_LIGHT);
        $this->SetFont('Arial', 'B', 10);
        foreach (self::COL_HEADERS as $i => $label) {
            $last = $i === count(self::COL_HEADERS) - 1;
            $this->Cell(self::COL_WIDTHS[$i], self::HEADER_HEIGHT, $label, 1, $last ? 1 : 0, 'C', true);
        }
    }

    /**
     * Ensure $needed mm of vertical space remains before the bottom margin;
     * otherwise start a new page. Returns true when a page break occurred.
     */
    public function ensureSpace(float $needed): bool
    {
        if ($this->GetY() + $needed > $this->PageBreakTrigger) {
            $this->AddPage();
            return true;
        }
        return false;
    }
}
