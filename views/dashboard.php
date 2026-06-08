<?php
/**
 * Dashboard View
 * Conditionally displays Admin Dashboard or Member Dashboard
 */

$userRole = $_SESSION['user_role'] ?? 'member';

if ($userRole === 'member'):
    // ============================================
    // MEMBER PERSONAL DASHBOARD VIEW
    // ============================================
    $present = (int)($myStats['present'] ?? 0);
    $absent = (int)($myStats['absent'] ?? 0);
    $excused = (int)($myStats['excused'] ?? 0);
    $total = (int)($myStats['total_sessions'] ?? 0);
    
    // Calculate rate
    $rate = $total > 0 ? round(($present / $total) * 100, 1) : 0;
    
    // Custom motivational badges based on attendance rate
    if ($rate >= 85) {
        $badgeClass = 'badge-champion';
        $badgeLabel = 'Attendance Champion';
        $motivationalText = 'Exceptional commitment! You are one of the shining stars of the BIT English Club. Keep rocking! 🌟';
    } elseif ($rate >= 70) {
        $badgeClass = 'badge-gold';
        $badgeLabel = 'Highly Dedicated';
        $motivationalText = 'Great job! You are highly active in our club sessions. Keep attending to hit that Champion mark! 💪';
    } elseif ($rate >= 50) {
        $badgeClass = 'badge-silver';
        $badgeLabel = 'Regular Attendee';
        $motivationalText = 'You are on the right track! Regular attendance is the best way to master English speaking skills.';
    } else {
        $badgeClass = 'badge-bronze';
        $badgeLabel = 'Growing Speaker';
        $motivationalText = 'We miss you in our sessions! Join more upcoming club meetings to practice and speak with confidence! ❤️';
    }
?>
    <!-- Personal Member Dashboard -->
    <div class="card personal-dashboard-card" style="position: relative; overflow: hidden; background: linear-gradient(135deg, #1D1F5A 0%, #15173c 100%); color: #FCFBFF;">
        <div class="personal-glow" style="position: absolute; right: -50px; top: -50px; width: 180px; height: 180px; border-radius: 50%; background-color: rgba(128, 188, 203, 0.2); filter: blur(30px);"></div>
        <div style="z-index: 2; position: relative;">
            <span class="badge-role <?= $badgeClass ?>" style="display: inline-block; font-size: 11px; padding: 6px 14px; border-radius: 20px; font-weight: 700; text-transform: uppercase; margin-bottom: 12px; letter-spacing: 0.5px;">
                <?= $badgeLabel ?>
            </span>
            <h2 class="card-title" style="color: #FCFBFF; font-family: 'Outfit', sans-serif; font-size: 28px; margin-bottom: 5px;">
                Hello, <?= htmlspecialchars($_SESSION['user_name']) ?>!
            </h2>
            <p style="color: #a3a6ce; font-size: 14px; margin-bottom: 0; max-width: 650px;">
                <?= $motivationalText ?>
            </p>
        </div>
    </div>

    <!-- Personal Statistics KPI Cards -->
    <div class="dashboard-stats" style="grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); margin-bottom: 2rem;">
        <!-- Attendance Rate -->
        <div class="stat-item" style="border-top: 5px solid #80BCCB;">
            <div class="stat-number" style="color: #1D1F5A;"><?= $rate ?>%</div>
            <div class="stat-label">My Attendance Rate</div>
            <div class="progress-container" style="background-color: #eee; height: 6px; border-radius: 5px; overflow: hidden; margin-top: 10px;">
                <div class="progress-bar" style="width: <?= $rate ?>%; height: 100%; border-radius: 5px; background: linear-gradient(90deg, #1D1F5A, #80BCCB);"></div>
            </div>
        </div>

        <!-- Total Sessions -->
        <div class="stat-item alt" style="border-top: 5px solid #1D1F5A;">
            <div class="stat-number" style="color: #1D1F5A;"><?= $total ?></div>
            <div class="stat-label">Sessions Tracked</div>
            <span style="font-size: 11px; color: #888; display: block; margin-top: 5px;">Out of <?= $overallStats['total_sessions'] ?? 0 ?> total</span>
        </div>

        <!-- Sessions Present -->
        <div class="stat-item" style="border-top: 5px solid #2ecc71;">
            <div class="stat-number" style="color: #2ecc71;"><?= $present ?></div>
            <div class="stat-label">Sessions Present</div>
            <span style="font-size: 11px; color: #888; display: block; margin-top: 5px;">Excellent attendance!</span>
        </div>

        <!-- Excused Sessions -->
        <div class="stat-item alt" style="border-top: 5px solid #f39c12;">
            <div class="stat-number" style="color: #f39c12;"><?= $excused ?></div>
            <div class="stat-label">Sessions Excused</div>
            <span style="font-size: 11px; color: #888; display: block; margin-top: 5px;">Notified in advance</span>
        </div>
    </div>

    <!-- Personal Attendance History -->
    <div class="card">
        <h2 class="card-title" style="margin-bottom: 1.5rem; color: #1D1F5A; font-family: 'Outfit', sans-serif;">📅 My Attendance History</h2>
        
        <?php if (empty($myHistory)): ?>
            <div class="empty-state">
                <div class="empty-state-title">No Attendance Records Yet</div>
                <div class="empty-state-description">
                    You haven't been marked in any club session yet. Once an admin records your attendance, it will display here!
                </div>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table>
                    <thead>
                        <tr>
                            <th style="width: 140px;">Date</th>
                            <th>Session Name</th>
                            <th style="width: 120px; text-align: center;">Status</th>
                            <th>Admin Notes / Role</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        foreach ($myHistory as $row): 
                            $status = $row['status'];
                            $badgeColor = $status === 'present' ? '#2ecc71' : ($status === 'excused' ? '#f39c12' : '#e74c3c');
                            $badgeBg = $status === 'present' ? 'rgba(46, 204, 113, 0.15)' : ($status === 'excused' ? 'rgba(243, 156, 18, 0.15)' : 'rgba(231, 76, 60, 0.15)');
                        ?>
                            <tr>
                                <td style="font-weight: 500;"><?= htmlspecialchars(date('M d, Y', strtotime($row['session_date']))) ?></td>
                                <td style="font-weight: bold; color: #1D1F5A;"><?= htmlspecialchars($row['session_name']) ?></td>
                                <td style="text-align: center;">
                                    <span class="badge" style="background-color: <?= $badgeBg ?>; color: <?= $badgeColor ?>; border: 1px solid <?= $badgeColor ?>; padding: 4px 12px; border-radius: 8px; font-weight: bold; text-transform: uppercase; font-size: 11px; display: inline-block;">
                                        <?= htmlspecialchars($status) ?>
                                    </span>
                                </td>
                                <td style="color: #666; font-style: italic;"><?= htmlspecialchars($row['notes'] ?: 'No notes') ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>

<?php else: ?>
    <!-- ============================================
    // ADMIN DASHBOARD VIEW
    // ============================================ -->
    <div class="card">
        <h2 class="card-title">Admin Dashboard</h2>
        <p style="color: #1D1F5A; margin-bottom: 2rem;">Welcome to English Club Attendance List Management System (Administrator Panel)</p>
        
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

    <script src="<?= Security::baseUrl('assets/js/dashboard.js') ?>"></script>
<?php endif; ?>
