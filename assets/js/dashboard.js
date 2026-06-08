/**
 * Dashboard Charts Initialization
 * Handles Chart.js charts for attendance visualization
 */

// Wait for DOM to be ready
document.addEventListener('DOMContentLoaded', function() {
    // Initialize charts if on dashboard page
    if (document.getElementById('attendanceTrendChart')) {
        // Check if Chart.js is loaded
        if (typeof Chart === 'undefined') {
            console.error('Chart.js is not loaded! Waiting...');
            // Wait for Chart.js to load
            const checkChart = setInterval(function() {
                if (typeof Chart !== 'undefined') {
                    clearInterval(checkChart);
                    console.log('Chart.js is now loaded');
                    loadDashboardData();
                }
            }, 100);
            // Timeout after 5 seconds
            setTimeout(function() {
                clearInterval(checkChart);
                if (typeof Chart === 'undefined') {
                    showDashboardError('Chart library failed to load. Check internet connection.');
                }
            }, 5000);
        } else {
            console.log('Chart.js is ready');
            loadDashboardData();
        }
    }
});

/**
 * Fetch dashboard data from API and render charts
 */
function loadDashboardData() {
    // Use absolute path for API endpoint
    const basePath = window.location.pathname.includes('/attendance-list/') ? '/attendance-list/' : '/';
    const apiUrl = window.location.origin + basePath + 'api/dashboard_stats.php';
    
    console.log('Fetching dashboard data from:', apiUrl);
    
    fetch(apiUrl)
        .then(response => {
            console.log('API Response status:', response.status);
            if (!response.ok) {
                throw new Error('HTTP error! status: ' + response.status);
            }
            return response.json();
        })
        .then(result => {
            console.log('API Result:', result);
            if (result.success && result.data) {
                renderAttendanceTrendChart(result.data.trend);
                renderStatusPieChart(result.data.statusDistribution);
                renderTopAttendees(result.data.topAttendees);
            } else {
                console.error('API returned error:', result.error);
                showDashboardError('Error: ' + (result.error || 'Unknown error'));
            }
        })
        .catch(error => {
            console.error('Network error:', error);
            showDashboardError('Network error. Check console (F12) for details.');
        });
}

/**
 * Show error message in dashboard stats areas
 */
function showDashboardError(message) {
    const containers = [
        { id: 'attendanceTrendChart', parent: true },
        { id: 'statusPieChart', parent: true },
        { id: 'topAttendeesList', parent: false }
    ];
    
    containers.forEach(item => {
        const el = document.getElementById(item.id);
        if (el) {
            const target = item.parent ? el.parentElement : el;
            target.innerHTML = '<p class="no-data">' + message + '</p>';
        }
    });
}

/**
 * Render Attendance Trend Line Chart
 */
function renderAttendanceTrendChart(trendData) {
    const canvas = document.getElementById('attendanceTrendChart');
    if (!canvas) return;
    
    // Check if no data available
    if (!trendData || trendData.length === 0) {
        canvas.parentElement.innerHTML = '<p class="no-data">No session data available</p>';
        return;
    }
    
    const ctx = canvas.getContext('2d');
    
    const labels = trendData.map(session => {
        const date = new Date(session.session_date);
        return date.toLocaleDateString('en-US', { month: 'short', day: 'numeric' });
    });
    
    const attendanceRates = trendData.map(session => session.attendance_rate);
    const presentCounts = trendData.map(session => session.present_count);
    const totalMembers = trendData.map(session => session.total_members);
    
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: labels,
            datasets: [{
                label: 'Attendance Rate (%)',
                data: attendanceRates,
                borderColor: '#1D1F5A',
                backgroundColor: 'rgba(29, 31, 90, 0.1)',
                borderWidth: 2,
                fill: true,
                tension: 0.4,
                pointBackgroundColor: '#1D1F5A',
                pointBorderColor: '#FCFBFF',
                pointBorderWidth: 2,
                pointRadius: 5,
                pointHoverRadius: 7
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    callbacks: {
                        afterLabel: function(context) {
                            const index = context.dataIndex;
                            return `Present: ${presentCounts[index]}/${totalMembers[index]}`;
                        }
                    },
                    backgroundColor: '#1D1F5A',
                    titleColor: '#FCFBFF',
                    bodyColor: '#FCFBFF',
                    borderColor: '#80BCCB',
                    borderWidth: 1
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    max: 100,
                    ticks: {
                        callback: function(value) {
                            return value + '%';
                        },
                        color: '#666'
                    },
                    grid: {
                        color: 'rgba(128, 188, 203, 0.2)'
                    }
                },
                x: {
                    ticks: {
                        color: '#666'
                    },
                    grid: {
                        display: false
                    }
                }
            }
        }
    });
}

/**
 * Render Status Distribution Pie Chart
 */
function renderStatusPieChart(distribution) {
    const canvas = document.getElementById('statusPieChart');
    if (!canvas) return;
    
    // Check if no data available
    const hasData = distribution && (distribution.present > 0 || distribution.absent > 0 || distribution.excused > 0);
    if (!hasData) {
        canvas.parentElement.innerHTML = '<p class="no-data">No attendance data available</p>';
        return;
    }
    
    const ctx = canvas.getContext('2d');
    
    const data = {
        labels: ['Present', 'Absent', 'Excused'],
        datasets: [{
            data: [
                distribution.present || 0,
                distribution.absent || 0,
                distribution.excused || 0
            ],
            backgroundColor: [
                '#80BCCB',  // Soft Blue-Grey for Present
                '#B61F24',  // Rich Red for Absent
                '#FCFBFF'   // Warm Cream for Excused
            ],
            borderColor: '#1D1F5A',
            borderWidth: 1
        }]
    };
    
    new Chart(ctx, {
        type: 'doughnut',
        data: data,
        options: {
            responsive: true,
            maintainAspectRatio: false,
            cutout: '60%',
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        color: '#1D1F5A',
                        padding: 15,
                        usePointStyle: true,
                        pointStyle: 'circle'
                    }
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            const label = context.label || '';
                            const value = context.parsed;
                            const total = context.dataset.data.reduce((a, b) => a + b, 0);
                            const percentage = total > 0 ? ((value / total) * 100).toFixed(1) : 0;
                            return `${label}: ${value} (${percentage}%)`;
                        }
                    },
                    backgroundColor: '#1D1F5A',
                    titleColor: '#FCFBFF',
                    bodyColor: '#FCFBFF',
                    borderColor: '#80BCCB',
                    borderWidth: 1
                }
            }
        }
    });
}

/**
 * Render Top Attendees List
 */
function renderTopAttendees(attendees) {
    const container = document.getElementById('topAttendeesList');
    
    if (!container) return;
    
    if (attendees.length === 0) {
        container.innerHTML = '<p class="no-data">No attendance data available yet.</p>';
        return;
    }
    
    const html = attendees.map((attendee, index) => {
        const rank = index + 1;
        const medal = rank === 1 ? '🥇' : rank === 2 ? '🥈' : rank === 3 ? '🥉' : `${rank}.`;
        return `
            <div class="top-attendee-item">
                <span class="attendee-rank">${medal}</span>
                <div class="attendee-info">
                    <span class="attendee-name">${escapeHtml(attendee.name)}</span>
                    <span class="attendee-field">${escapeHtml(attendee.field)}</span>
                </div>
                <div class="attendee-stats">
                    <span class="attendance-rate">${attendee.attendance_rate}%</span>
                    <span class="attendance-count">${attendee.present_count}/${attendee.total_sessions}</span>
                </div>
            </div>
        `;
    }).join('');
    
    container.innerHTML = html;
}

/**
 * Escape HTML to prevent XSS
 */
function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}
