/**
 * Dashboard Script
 * Handles statistics loading, charts, and recent activity
 */

$(document).ready(function() {

    // Load Dashboard Data
    function loadDashboardData() {
        $.ajax({
            url: '/dashboard/stats',
            type: 'GET',
            success: function(response) {
                if (response.success) {
                    // Update stats
                    $('#total-bookings').text(response.total || 0);
                    $('#active-bookings').text(response.active || 0);
                    $('#pending-bookings').text(response.pending || 0);
                    $('#cancelled-bookings').text(response.cancelled || 0);
                    
                    // Create charts
                    createStatusChart(response.status_data || {});
                    createTrendChart(response.trend_data || []);
                    
                    // Load recent activity
                    loadRecentActivity();
                }
            },
            error: function() {
                console.log('Error loading dashboard data');
                $('#total-bookings').text('0');
                $('#active-bookings').text('0');
                $('#pending-bookings').text('0');
                $('#cancelled-bookings').text('0');
            }
        });
    }


    // Status Chart (Doughnut)
    function createStatusChart(data) {
        var ctx = document.getElementById('statusChart').getContext('2d');
        
        var labels = ['Pending', 'Confirmed', 'Active', 'Returned', 'Completed', 'Cancelled'];
        var colors = ['#f59e0b', '#3b82f6', '#28a745', '#17a2b8', '#2e6da4', '#dc3545'];
        var values = [];
        
        labels.forEach(function(label) {
            values.push(data[label.toLowerCase()] || 0);
        });
        
        // Destroy existing chart if it exists
        if (window.statusChartInstance) {
            window.statusChartInstance.destroy();
        }
        
        window.statusChartInstance = new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: labels,
                datasets: [{
                    data: values,
                    backgroundColor: colors,
                    borderWidth: 2,
                    borderColor: '#fff'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                aspectRatio: 1.25,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            padding: 12,
                            usePointStyle: true,
                            font: { size: 12 }
                        }
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                var total = context.dataset.data.reduce(function(a, b) { return a + b; }, 0);
                                var percentage = total > 0 ? ((context.parsed / total) * 100).toFixed(1) : 0;
                                return context.label + ': ' + context.parsed + ' (' + percentage + '%)';
                            }
                        }
                    }
                },
                cutout: '60%'
            }
        });
    }

  
    // Trend Chart (Bar)
    function createTrendChart(data) {
        var ctx = document.getElementById('trendChart').getContext('2d');
        
        var months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
        var currentMonth = new Date().getMonth();
        var last6Months = [];
        var values = [];
        
        for (var i = 5; i >= 0; i--) {
            var idx = (currentMonth - i + 12) % 12;
            last6Months.push(months[idx]);
            values.push(data[idx] || 0);
        }
        
        // Destroy existing chart if it exists
        if (window.trendChartInstance) {
            window.trendChartInstance.destroy();
        }
        
        window.trendChartInstance = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: last6Months,
                datasets: [{
                    label: 'Bookings',
                    data: values,
                    backgroundColor: 'rgba(59, 130, 246, 0.7)',
                    borderColor: '#3b82f6',
                    borderWidth: 2,
                    borderRadius: 4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                aspectRatio: 1.25,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 1
                        }
                    }
                }
            }
        });
    }

    
    // Recent Activity
    function loadRecentActivity() {
        $.ajax({
            url: '/dashboard/recent-activity',
            type: 'GET',
            success: function(response) {
                var container = $('#recent-activity');
                
                if (response.length === 0) {
                    container.html('<p class="text-center text-muted">No recent activity.</p>');
                    return;
                }
                
                var html = '';
                response.forEach(function(item) {
                    var iconClass = item.status === 'completed' ? 'bg-success' : 
                                   item.status === 'pending' ? 'bg-warning' : 
                                   item.status === 'cancelled' ? 'bg-danger' : 'bg-info';
                    var statusLabel = item.status.charAt(0).toUpperCase() + item.status.slice(1);
                    
                    html += `
                        <div class="activity-item">
                            <div class="activity-icon ${iconClass}">
                                <span class="glyphicon glyphicon-calendar"></span>
                            </div>
                            <div class="activity-content">
                                <p class="activity-text">
                                    <strong>${item.user}</strong> booked 
                                    <strong>${item.car}</strong>
                                    ${item.status ? '<span class="activity-status label-status label-'+item.status+'">'+statusLabel+'</span>' : ''}
                                </p>
                                <p class="activity-time">${item.time}</p>
                            </div>
                        </div>
                    `;
                });
                
                container.html(html);
            },
            error: function() {
                $('#recent-activity').html('<p class="text-center text-muted">Unable to load recent activity.</p>');
            }
        });
    }


    // Auto-refresh every 30 seconds
    loadDashboardData();
    setInterval(loadDashboardData, 30000);
});