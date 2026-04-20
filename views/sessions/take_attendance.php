<?php
// Breadcrumb for take attendance
$breadcrumbs = [
    ['label' => 'Sessions', 'url' => '?page=sessions'],
    ['label' => $session['session_name'] ?? 'Take Attendance']
];
?>
<div class="card">
    <div class="take-attendance-logo">
        <img src="assets/logo_bit_en.jpg" alt="English Club Logo">
    </div>
    <div class="card-header">
        <h2 class="card-title">Take Attendance: <?= htmlspecialchars($session['session_name'] ?? '') ?></h2>
        <a href="?page=sessions" class="btn btn-secondary">Back to Sessions</a>
    </div>
    
    <div class="session-info">
        <strong>Date:</strong> <?= htmlspecialchars(date('F d, Y', strtotime($session['session_date'] ?? 'now'))) ?>
    </div>
    
    <?php if (isset($_SESSION['message'])): ?>
        <div class="alert alert-<?= $_SESSION['message_type'] ?? 'success' ?>">
            <?= $_SESSION['message'] ?>
        </div>
        <?php unset($_SESSION['message'], $_SESSION['message_type']); ?>
    <?php endif; ?>
    
    <?php if (empty($members)): ?>
        <div class="empty-state">
            <div class="empty-state-title">No Members Found</div>
            <div class="empty-state-description">
                You need to add members before you can take attendance. Create your first member to get started.
            </div>
            <div class="empty-state-action">
                <a href="?page=members&action=create" class="btn btn-primary">Add Members</a>
            </div>
        </div>
    <?php else: ?>
        <form method="POST" action="?page=sessions&action=save&id=<?= $session['id'] ?? 0 ?>" class="attendance-form">
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
                        <?php foreach ($members as $member): 
                            $existingRecord = $attendanceRecords[$member['id']] ?? null;
                        ?>
                            <tr>
                                <td><?= htmlspecialchars($member['name'] ?? '') ?></td>
                                <td><?= htmlspecialchars($member['field'] ?? '') ?></td>
                                <td><?= htmlspecialchars($member['email'] ?? '') ?></td>
                                <td>
                                    <div class="status-checkboxes">
                                        <label class="status-checkbox status-present">
                                            <input type="radio" 
                                                   name="attendance[<?= $member['id'] ?>][status]" 
                                                   value="present" 
                                                   <?= ($existingRecord['status'] ?? 'present') === 'present' ? 'checked' : '' ?>
                                                   required>
                                            <span>Present</span>
                                        </label>
                                        <label class="status-checkbox status-absent">
                                            <input type="radio" 
                                                   name="attendance[<?= $member['id'] ?>][status]" 
                                                   value="absent" 
                                                   <?= ($existingRecord['status'] ?? '') === 'absent' ? 'checked' : '' ?>>
                                            <span>Absent</span>
                                        </label>
                                        <label class="status-checkbox status-excused">
                                            <input type="radio" 
                                                   name="attendance[<?= $member['id'] ?>][status]" 
                                                   value="excused" 
                                                   <?= ($existingRecord['status'] ?? '') === 'excused' ? 'checked' : '' ?>>
                                            <span>Excused</span>
                                        </label>
                                    </div>
                                </td>
                                <td>
                                    <input type="text" 
                                           name="attendance[<?= $member['id'] ?>][notes]" 
                                           class="form-control" 
                                           placeholder="Optional notes..."
                                           value="<?= htmlspecialchars($existingRecord['notes'] ?? '') ?>">
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            
            <div class="actions">
                <a href="?page=sessions" class="btn btn-secondary">Cancel</a>
                <button type="submit" class="btn btn-success">Save Attendance</button>
            </div>
        </form>
    <?php endif; ?>
</div>

