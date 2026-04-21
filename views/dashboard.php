<div class="card">
    <div class="dashboard-logo">
        <img src="assets/logo_bit_en.jpg" alt="English Club Logo">
    </div>
    <h2 class="card-title">Dashboard</h2>
    <p style="color: #1D1F5A; margin-bottom: 2rem;">Welcome to English Club Attendance List Management System</p>
    
    <div class="dashboard-stats">
        <!-- Total Members -->
        <div class="stat-item">
            <div class="stat-number"><?= $totalMembers ?? 0 ?></div>
            <div class="stat-label">Total Members</div>
            <a href="?page=members" class="stat-link" style="color: #80BCCB;">View Members →</a>
        </div>
        
        <!-- Total Sessions -->
        <div class="stat-item alt">
            <div class="stat-number"><?= $totalSessions ?? 0 ?></div>
            <div class="stat-label">Total Sessions</div>
            <a href="?page=sessions" class="stat-link" style="color: #1D1F5A;">View Sessions →</a>
        </div>
        
        <!-- Present Today -->
        <div class="stat-item alt">
            <div class="stat-number"><?= $overallStats['present'] ?? 0 ?></div>
            <div class="stat-label">Total Present</div>
        </div>
        
        <!-- Absent Count -->
        <div class="stat-item danger">
            <div class="stat-number"><?= $overallStats['absent'] ?? 0 ?></div>
            <div class="stat-label">Total Absent</div>
        </div>
    </div>
</div>

<!-- Quick Actions -->
<div class="card">
    <h2 class="card-title">Quick Actions</h2>
    <div class="quick-actions">
        <a href="?page=members&action=create" class="btn btn-primary">+ Add New Member</a>
        <a href="?page=sessions&action=create" class="btn btn-success">+ Create New Session</a>
        <a href="?page=members" class="btn btn-secondary">View All Members</a>
        <a href="?page=sessions" class="btn btn-secondary">View All Sessions</a>
    </div>
</div>

<!-- Charts Section -->
<div class="dashboard-charts">
    <!-- Attendance Trend Chart -->
    <div class="card chart-card">
        <h3 class="chart-title">Attendance Trend (Last 5 Sessions)</h3>
        <div class="chart-container">
            <canvas id="attendanceTrendChart"></canvas>
        </div>
    </div>
    
    <!-- Status Distribution Chart -->
    <div class="card chart-card">
        <h3 class="chart-title">Status Distribution</h3>
        <div class="chart-container pie-chart-container">
            <canvas id="statusPieChart"></canvas>
        </div>
    </div>
    
    <!-- Top Attendees -->
    <div class="card chart-card">
        <h3 class="chart-title">Top Attendees</h3>
        <div id="topAttendeesList" class="top-attendees-container">
            <p class="loading-text">Loading...</p>
        </div>
    </div>
</div>

<!-- Recent Sessions -->
<div class="card">
    <h2 class="card-title">Recent Sessions</h2>
    <?php if (empty($recentSessions)): ?>
        <div class="empty-state">
            <div class="empty-state-title">No Sessions Yet</div>
            <div class="empty-state-description">
                Get started by creating your first attendance session.
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
                        <th>Date</th>
                        <th>Session Name</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($recentSessions as $session): ?>
                        <tr>
                            <td><?= htmlspecialchars(date('M d, Y', strtotime($session['session_date']))) ?></td>
                            <td><?= htmlspecialchars($session['session_name']) ?></td>
                            <td class="actions">
                                <a href="?page=sessions&action=take&id=<?= $session['id'] ?>" class="btn btn-success btn-sm">Take Attendance</a>
                                <a href="?page=sessions&action=view&id=<?= $session['id'] ?>" class="btn btn-secondary btn-sm">View</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<script src="assets/js/dashboard.js"></script>
