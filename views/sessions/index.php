<div class="card">
    <div class="card-header">
        <h2 class="card-title">Attendance Sessions</h2>
        <a href="?page=sessions&action=create" class="btn btn-primary">+ Create New Session</a>
    </div>
    
    <?php if (isset($_SESSION['message'])): ?>
        <div class="alert alert-<?= $_SESSION['message_type'] ?? 'success' ?>">
            <?= $_SESSION['message'] ?>
        </div>
        <?php unset($_SESSION['message'], $_SESSION['message_type']); ?>
    <?php endif; ?>
    
    <?php if (empty($sessions)): ?>
        <div class="empty-state">
            <div class="empty-state-title">No Sessions Found</div>
            <div class="empty-state-description">
                Start tracking attendance by creating your first session.
            </div>
            <div class="empty-state-action">
                <a href="?page=sessions&action=create" class="btn btn-primary">Create First Session</a>
            </div>
        </div>
    <?php else: ?>
        <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Date</th>
                        <th>Session Name</th>
                        <th>Present</th>
                        <th>Absent</th>
                        <th>Excused</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($sessions as $session): ?>
                        <tr>
                            <td><?= htmlspecialchars($session['id'] ?? '') ?></td>
                            <td><?= htmlspecialchars(date('M d, Y', strtotime($session['session_date'] ?? 'now'))) ?></td>
                            <td><?= htmlspecialchars($session['session_name'] ?? '') ?></td>
                            <td>
                                <span class="badge badge-present">
                                    <?= $session['present_count'] ?? 0 ?>
                                </span>
                            </td>
                            <td>
                                <span class="badge badge-absent">
                                    <?= $session['absent_count'] ?? 0 ?>
                                </span>
                            </td>
                            <td>
                                <span class="badge badge-excused">
                                    <?= $session['excused_count'] ?? 0 ?>
                                </span>
                            </td>
                            <td class="actions">
                                <a href="?page=sessions&action=take&id=<?= $session['id'] ?? 0 ?>" class="btn btn-success btn-sm">
                                    Take
                                </a>
                                <a href="?page=sessions&action=view&id=<?= $session['id'] ?? 0 ?>" class="btn btn-secondary btn-sm">
                                    View
                                </a>
                                <a href="?page=sessions&action=export&id=<?= $session['id'] ?? 0 ?>&format=pdf" class="btn btn-secondary btn-sm">
                                    PDF
                                </a>
                                <a href="?page=sessions&action=export&id=<?= $session['id'] ?? 0 ?>&format=excel" class="btn btn-secondary btn-sm">
                                    Excel
                                </a>
                                <a href="?page=sessions&action=delete&id=<?= $session['id'] ?? 0 ?>"
                                   class="btn btn-danger btn-sm"
                                   onclick="return confirm('Are you sure you want to delete this session and all its records?')">
                                    Delete
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

