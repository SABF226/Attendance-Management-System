<?php
// Breadcrumb for session view
$breadcrumbs = [
    ['label' => 'Sessions', 'url' => '?page=sessions'],
    ['label' => $session['session_name'] ?? 'Session Details']
];
?>
<div class="card">
    <div class="card-header">
        <h2 class="card-title">Session Details</h2>
        <div class="actions">
            <a href="?page=sessions&action=export&id=<?= $session['id'] ?? 0 ?>&format=pdf" class="btn btn-secondary btn-sm">Export PDF</a>
            <a href="?page=sessions&action=export&id=<?= $session['id'] ?? 0 ?>&format=excel" class="btn btn-secondary btn-sm">Export Excel</a>
            <a href="?page=sessions" class="btn btn-secondary btn-sm">Back to Sessions</a>
        </div>
    </div>
    
    <div class="session-info">
        <h3><?= htmlspecialchars($session['session_name'] ?? '') ?></h3>
        <p><strong>Date:</strong> <?= htmlspecialchars(date('F d, Y', strtotime($session['session_date'] ?? 'now'))) ?></p>
        
        <div class="session-stats">
            <div>
                <strong>Present:</strong> 
                <span class="badge badge-present"><?= $session['present_count'] ?? 0 ?></span>
            </div>
            <div>
                <strong>Absent:</strong> 
                <span class="badge badge-absent"><?= $session['absent_count'] ?? 0 ?></span>
            </div>
            <div>
                <strong>Excused:</strong> 
                <span class="badge badge-excused"><?= $session['excused_count'] ?? 0 ?></span>
            </div>
        </div>
    </div>
    
    <?php if (empty($records)): ?>
        <div class="empty-state">
            <div class="empty-state-title">No Attendance Records</div>
            <div class="empty-state-description">
                Attendance has not been recorded for this session yet.
            </div>
            <div class="empty-state-action">
                <a href="?page=sessions&action=take&id=<?= $session['id'] ?>" class="btn btn-primary">Take Attendance</a>
            </div>
        </div>
    <?php else: ?>
        <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th>Member</th>
                        <th>Field</th>
                        <th>Email</th>
                        <th>Status</th>
                        <th>Notes</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($records as $record): ?>
                        <tr>
                            <td><?= htmlspecialchars($record['name'] ?? '') ?></td>
                            <td><?= htmlspecialchars($record['field'] ?? '') ?></td>
                            <td><?= htmlspecialchars($record['email'] ?? '') ?></td>
                            <td>
                                <span class="badge badge-<?= $record['status'] ?? 'present' ?>">
                                    <?= ucfirst(htmlspecialchars($record['status'] ?? 'present')) ?>
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

