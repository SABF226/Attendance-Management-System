<?php
// Breadcrumb for member profile
$breadcrumbs = [
    ['label' => 'Members', 'url' => '?page=members'],
    ['label' => $member['name'] ?? 'Member Profile']
];
?>

<div class="card">
    <div class="card-header">
        <div>
            <h2 class="card-title"><?= htmlspecialchars($member['name'] ?? 'Unknown') ?></h2>
            <span class="text-muted"><?= htmlspecialchars($member['field'] ?? 'No field specified') ?></span>
        </div>
        <div class="header-actions">
            <a href="?page=members&action=edit&id=<?= $member['id'] ?? 0 ?>" class="btn btn-secondary">Edit Member</a>
            <a href="?page=members" class="btn btn-primary">Back to List</a>
        </div>
    </div>
    
    <!-- Member Stats Cards -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-value"><?= $memberStats['attendance_rate'] ?? 0 ?>%</div>
            <div class="stat-label">Attendance Rate</div>
        </div>
        <div class="stat-card">
            <div class="stat-value"><?= $memberStats['total_sessions'] ?? 0 ?></div>
            <div class="stat-label">Total Sessions</div>
        </div>
        <div class="stat-card present">
            <div class="stat-value"><?= $memberStats['present'] ?? 0 ?></div>
            <div class="stat-label">Present</div>
        </div>
        <div class="stat-card absent">
            <div class="stat-value"><?= $memberStats['absent'] ?? 0 ?></div>
            <div class="stat-label">Absent</div>
        </div>
        <div class="stat-card excused">
            <div class="stat-value"><?= $memberStats['excused'] ?? 0 ?></div>
            <div class="stat-label">Excused</div>
        </div>
    </div>
    
    <!-- Member Details -->
    <div class="member-details">
        <h3>Contact Information</h3>
        <div class="details-grid">
            <div class="detail-item">
                <span class="detail-label">Phone:</span>
                <span class="detail-value"><?= htmlspecialchars($member['phone'] ?? 'Not provided') ?></span>
            </div>
            <div class="detail-item">
                <span class="detail-label">Email:</span>
                <span class="detail-value"><?= htmlspecialchars($member['email'] ?? 'Not provided') ?></span>
            </div>
            <div class="detail-item">
                <span class="detail-label">Joined:</span>
                <span class="detail-value"><?= htmlspecialchars(date('F d, Y', strtotime($member['created_at'] ?? 'now'))) ?></span>
            </div>
        </div>
    </div>
    
    <!-- Attendance History -->
    <div class="attendance-history">
        <h3>Attendance History</h3>
        
        <?php if (empty($attendanceHistory)): ?>
            <div class="empty-state">
                <div class="empty-state-title">No Attendance Records</div>
                <div class="empty-state-description">
                    This member has not attended any sessions yet.
                </div>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="history-table">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Session</th>
                            <th>Status</th>
                            <th>Notes</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($attendanceHistory as $record): ?>
                            <tr>
                                <td><?= htmlspecialchars(date('M d, Y', strtotime($record['session_date'] ?? 'now'))) ?></td>
                                <td><?= htmlspecialchars($record['session_name'] ?? 'Unknown Session') ?></td>
                                <td>
                                    <span class="status-badge status-<?= $record['status'] ?? 'unknown' ?>">
                                        <?= ucfirst($record['status'] ?? 'Unknown') ?>
                                    </span>
                                </td>
                                <td><?= htmlspecialchars($record['notes'] ?? '-') ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<style>
.header-actions {
    display: flex;
    gap: 0.5rem;
}

.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(140px, 1fr));
    gap: 1rem;
    margin: 1.5rem 0;
}

.stat-card {
    background: linear-gradient(135deg, #1D1F5A 0%, #2D2F7A 100%);
    color: white;
    padding: 1.25rem;
    border-radius: 12px;
    text-align: center;
}

.stat-card.present {
    background: linear-gradient(135deg, #80BCCB 0%, #60a0b0 100%);
    color: #1D1F5A;
}

.stat-card.absent {
    background: linear-gradient(135deg, #B61F24 0%, #9a1a1f 100%);
}

.stat-card.excused {
    background: linear-gradient(135deg, #f0ad4e 0%, #ec971f 100%);
    color: #1D1F5A;
}

.stat-value {
    font-size: 1.75rem;
    font-weight: 700;
    margin-bottom: 0.25rem;
}

.stat-label {
    font-size: 0.85rem;
    opacity: 0.9;
}

.member-details {
    margin: 2rem 0;
    padding: 1.5rem;
    background: #f8f9fa;
    border-radius: 12px;
}

.member-details h3 {
    margin-top: 0;
    margin-bottom: 1rem;
    color: #1D1F5A;
}

.details-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 1rem;
}

.detail-item {
    display: flex;
    flex-direction: column;
    gap: 0.25rem;
}

.detail-label {
    font-size: 0.8rem;
    font-weight: 600;
    color: #666;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.detail-value {
    font-size: 1rem;
    color: #333;
}

.attendance-history {
    margin-top: 2rem;
}

.attendance-history h3 {
    margin-bottom: 1rem;
    color: #1D1F5A;
}

.history-table {
    width: 100%;
}

.status-badge {
    display: inline-block;
    padding: 0.375rem 0.75rem;
    border-radius: 4px;
    font-size: 0.85rem;
    font-weight: 500;
}

.status-present {
    background: rgba(128, 188, 203, 0.2);
    color: #1D1F5A;
}

.status-absent {
    background: rgba(182, 31, 36, 0.1);
    color: #B61F24;
}

.status-excused {
    background: rgba(240, 173, 78, 0.15);
    color: #c77c0f;
}

@media (max-width: 768px) {
    .stats-grid {
        grid-template-columns: repeat(2, 1fr);
    }
    
    .header-actions {
        flex-direction: column;
        width: 100%;
    }
    
    .card-header {
        flex-direction: column;
        align-items: flex-start;
    }
}
</style>
