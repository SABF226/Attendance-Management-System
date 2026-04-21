<div class="card">
    <div class="card-header">
        <h2 class="card-title">Attendance Sessions</h2>
        <a href="?page=sessions&action=create" class="btn btn-primary">+ Create New Session</a>
    </div>
    
    <?php if (isset($_SESSION['message'])): ?>
        <div id="sessionMessage" data-message="<?= htmlspecialchars($_SESSION['message']) ?>" data-type="<?= $_SESSION['message_type'] ?? 'success' ?>" style="display: none;"></div>
        <?php unset($_SESSION['message'], $_SESSION['message_type']); ?>
    <?php endif; ?>
    
    <!-- Session Statistics Bar -->
    <?php if (!empty($monthlyStats)): ?>
    <div class="stats-bar">
        <div class="stats-bar-item">
            <span class="stats-bar-value"><?= $monthlyStats['total_this_month'] ?></span>
            <span class="stats-bar-label">Sessions This Month</span>
        </div>
        <div class="stats-bar-item">
            <span class="stats-bar-value"><?= $monthlyStats['avg_attendance_rate'] ?>%</span>
            <span class="stats-bar-label">Avg Attendance Rate</span>
        </div>
        <div class="stats-bar-item">
            <span class="stats-bar-value"><?= htmlspecialchars($monthlyStats['most_active_team']) ?></span>
            <span class="stats-bar-label">Most Active Team</span>
        </div>
        <div class="stats-bar-item">
            <span class="stats-bar-value"><?= $monthlyStats['total_sessions'] ?></span>
            <span class="stats-bar-label">Total Sessions</span>
        </div>
    </div>
    <?php endif; ?>
    
    <!-- Filter Bar -->
    <div class="filter-bar">
        <form method="GET" class="filter-form">
            <input type="hidden" name="page" value="sessions">
            
            <div class="filter-group">
                <label class="filter-label">From Date</label>
                <input type="date" name="date_from" class="filter-input" 
                       value="<?= htmlspecialchars($filters['date_from'] ?? '') ?>">
            </div>
            
            <div class="filter-group">
                <label class="filter-label">To Date</label>
                <input type="date" name="date_to" class="filter-input" 
                       value="<?= htmlspecialchars($filters['date_to'] ?? '') ?>">
            </div>
            
            <div class="filter-group">
                <label class="filter-label">Sort By</label>
                <select name="sort" class="filter-input">
                    <option value="date_desc" <?= $sort === 'date_desc' ? 'selected' : '' ?>>Date (Newest First)</option>
                    <option value="date_asc" <?= $sort === 'date_asc' ? 'selected' : '' ?>>Date (Oldest First)</option>
                    <option value="name_asc" <?= $sort === 'name_asc' ? 'selected' : '' ?>>Name (A-Z)</option>
                    <option value="name_desc" <?= $sort === 'name_desc' ? 'selected' : '' ?>>Name (Z-A)</option>
                    <option value="rate_desc" <?= $sort === 'rate_desc' ? 'selected' : '' ?>>Attendance Rate (High-Low)</option>
                    <option value="rate_asc" <?= $sort === 'rate_asc' ? 'selected' : '' ?>>Attendance Rate (Low-High)</option>
                </select>
            </div>
            
            <div class="filter-group filter-checkbox">
                <label class="filter-checkbox-label">
                    <input type="checkbox" name="has_absences" value="1" 
                           <?= !empty($filters['has_absences']) ? 'checked' : '' ?>>
                    <span>Show sessions with absences only</span>
                </label>
            </div>
            
            <div class="filter-actions">
                <button type="submit" class="btn btn-primary btn-sm">Apply Filters</button>
                <a href="?page=sessions" class="btn btn-secondary btn-sm">Reset</a>
            </div>
        </form>
    </div>
    
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
                                <div class="action-dropdown">
                                    <button type="button" class="action-dropdown-toggle" onclick="toggleDropdown(this)">
                                        Actions
                                    </button>
                                    <div class="action-dropdown-menu">
                                        <a href="?page=sessions&action=view&id=<?= $session['id'] ?? 0 ?>" class="action-dropdown-item">
                                            View Details
                                        </a>
                                        <div class="action-dropdown-divider"></div>
                                        <a href="?page=sessions&action=export&id=<?= $session['id'] ?? 0 ?>&format=pdf" class="action-dropdown-item">
                                            Export PDF
                                        </a>
                                        <a href="?page=sessions&action=export&id=<?= $session['id'] ?? 0 ?>&format=excel" class="action-dropdown-item">
                                            Export Excel
                                        </a>
                                        <div class="action-dropdown-divider"></div>
                                        <a href="?page=sessions&action=delete&id=<?= $session['id'] ?? 0 ?>"
                                           class="action-dropdown-item danger"
                                           onclick="return confirmDelete(this, '<?= htmlspecialchars(addslashes($session['session_name'] ?? 'this session')) ?>')">
                                            Delete
                                        </a>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

