<?php
/**
 * Leaderboard View
 * Displays the Top Attendees of the BIT English Club
 */
?>
<div class="card">
    <div class="leaderboard-header">
        <div class="leaderboard-title-group">
            <h2 class="card-title">🏆 Top Attendees Leaderboard</h2>
            <p style="color: #666; margin-bottom: 0;">Celebrating our most dedicated and active club members!</p>
        </div>
        <div class="leaderboard-search">
            <input type="text" id="leaderboardSearchInput" placeholder="Search members or fields..." onkeyup="filterLeaderboard()">
            <i class="search-icon">🔍</i>
        </div>
    </div>
</div>

<div class="leaderboard-podium card">
    <h3 style="margin-bottom: 1.5rem; text-align: center; color: #1D1F5A; font-family: 'Outfit', sans-serif;">✨ Club Champions ✨</h3>
    <div class="podium-container">
        <?php 
        $podium = array_slice($topAttendees, 0, 3);
        $ranks = [
            1 => ['class' => 'gold', 'medal' => '🥇', 'title' => 'Attendance Champion'],
            2 => ['class' => 'silver', 'medal' => '🥈', 'title' => 'Master Attendee'],
            3 => ['class' => 'bronze', 'medal' => '🥉', 'title' => 'Dedicated Star']
        ];
        
        // Re-arrange for standard podium visual order: 2nd, 1st, 3rd
        $visualOrder = [];
        if (isset($podium[1])) $visualOrder[] = ['data' => $podium[1], 'rank' => 2];
        if (isset($podium[0])) $visualOrder[] = ['data' => $podium[0], 'rank' => 1];
        if (isset($podium[2])) $visualOrder[] = ['data' => $podium[2], 'rank' => 3];
        
        foreach ($visualOrder as $pod): 
            $member = $pod['data'];
            $rank = $pod['rank'];
            $cfg = $ranks[$rank];
        ?>
            <div class="podium-column podium-<?= $cfg['class'] ?>">
                <div class="podium-medal"><?= $cfg['medal'] ?></div>
                <div class="podium-avatar"><?= mb_substr($member['name'], 0, 2) ?></div>
                <div class="podium-name"><?= htmlspecialchars($member['name']) ?></div>
                <div class="podium-field"><?= htmlspecialchars($member['field']) ?></div>
                <div class="podium-rate"><?= $member['attendance_rate'] ?>%</div>
                <div class="podium-bar">
                    <div class="podium-rank-text"><?= $rank ?></div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<div class="card">
    <h2 class="card-title" style="margin-bottom: 1.5rem;">Leaderboard Standings</h2>
    
    <?php if (empty($topAttendees)): ?>
        <div class="empty-state">
            <div class="empty-state-title">No Statistics Available Yet</div>
            <div class="empty-state-description">
                Once members start attending sessions, their rankings will appear here!
            </div>
        </div>
    <?php else: ?>
        <div class="table-responsive">
            <table id="leaderboardTable">
                <thead>
                    <tr>
                        <th style="width: 80px; text-align: center;">Rank</th>
                        <th>Member Name</th>
                        <th>Field of Study</th>
                        <th style="width: 150px; text-align: center;">Sessions Attended</th>
                        <th style="width: 250px;">Attendance Rate</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    foreach ($topAttendees as $index => $attendee): 
                        $rank = $index + 1;
                        $medal = $rank === 1 ? '🥇' : ($rank === 2 ? '🥈' : ($rank === 3 ? '🥉' : ''));
                        
                        // Set progress bar gradient based on percentage
                        $rate = (float)$attendee['attendance_rate'];
                        $barClass = $rate >= 85 ? 'success' : ($rate >= 60 ? 'warning' : 'danger');
                    ?>
                        <tr class="leaderboard-row">
                            <td style="text-align: center; font-weight: bold; font-size: 1.1rem;">
                                <?= $medal ?: $rank ?>
                            </td>
                            <td class="leaderboard-name-cell" style="font-weight: 600; color: #1D1F5A;">
                                <?= htmlspecialchars($attendee['name']) ?>
                            </td>
                            <td><?= htmlspecialchars($attendee['field']) ?></td>
                            <td style="text-align: center; font-weight: 500;">
                                <span class="badge" style="background-color: rgba(29, 31, 90, 0.1); color: #1D1F5A; padding: 5px 12px; border-radius: 8px;">
                                    <?= $attendee['present_count'] ?> / <?= $attendee['total_sessions'] ?>
                                </span>
                            </td>
                            <td>
                                <div class="leaderboard-rate-container">
                                    <div class="progress-container" style="background-color: #eee; height: 10px; border-radius: 10px; flex-grow: 1; margin-right: 15px; overflow: hidden; position: relative;">
                                        <div class="progress-bar progress-<?= $barClass ?>" style="width: <?= $rate ?>%; height: 100%; border-radius: 10px; transition: width 1s ease;"></div>
                                    </div>
                                    <span style="font-weight: bold; min-width: 45px; display: inline-block; text-align: right; color: #1D1F5A;">
                                        <?= $attendee['attendance_rate'] ?>%
                                    </span>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<script>
    function filterLeaderboard() {
        const input = document.getElementById("leaderboardSearchInput");
        const filter = input.value.toUpperCase();
        const table = document.getElementById("leaderboardTable");
        if (!table) return;
        
        const tr = table.getElementsByTagName("tr");
        
        // Loop through all table rows, skip header
        for (let i = 1; i < tr.length; i++) {
            const nameCell = tr[i].getElementsByTagName("td")[1];
            const fieldCell = tr[i].getElementsByTagName("td")[2];
            
            if (nameCell || fieldCell) {
                const nameText = nameCell.textContent || nameCell.innerText;
                const fieldText = fieldCell.textContent || fieldCell.innerText;
                
                if (nameText.toUpperCase().indexOf(filter) > -1 || fieldText.toUpperCase().indexOf(filter) > -1) {
                    tr[i].style.display = "";
                } else {
                    tr[i].style.display = "none";
                }
            }
        }
    }
</script>
