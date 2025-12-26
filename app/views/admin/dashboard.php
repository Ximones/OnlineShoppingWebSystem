<?php $title = 'Admin Dashboard'; ?>

<div style="margin-bottom: 30px;">
    <h1>Admin Dashboard</h1>
    <p>Welcome to the admin panel. Manage your store from here.</p>
</div>

<!-- Statistics Cards -->
<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px; margin-bottom: 40px;">
    <!-- Members Card -->
    <div class="panel" style="padding: 20px; text-align: center;">
        <div style="font-size: 36px; font-weight: bold; color: #007bff; margin-bottom: 10px;">
            <?= number_format($stats['total_members']); ?>
        </div>
        <div style="font-size: 18px; color: #666; margin-bottom: 15px;">Total Members</div>
        <a href="?module=admin&resource=members&action=index" class="btn small">View Members</a>
    </div>

    <!-- Products Card -->
    <div class="panel" style="padding: 20px; text-align: center;">
        <div style="font-size: 36px; font-weight: bold; color: #28a745; margin-bottom: 10px;">
            <?= number_format($stats['total_products']); ?>
        </div>
        <div style="font-size: 18px; color: #666; margin-bottom: 10px;">Total Products</div>
        <div style="font-size: 14px; color: #999; margin-bottom: 15px;">
            <?= number_format($stats['active_products']); ?> active
        </div>
        <a href="?module=admin&resource=products&action=index" class="btn small">View Products</a>
    </div>

    <!-- Orders Card -->
    <div class="panel" style="padding: 20px; text-align: center;">
        <div style="font-size: 36px; font-weight: bold; color: #ffc107; margin-bottom: 10px;">
            <?= number_format($stats['total_orders']); ?>
        </div>
        <div style="font-size: 18px; color: #666; margin-bottom: 10px;">Total Orders</div>
        <div style="font-size: 14px; color: #999; margin-bottom: 15px;">
            <?= number_format($stats['pending_orders']); ?> pending
        </div>
        <a href="?module=admin&resource=orders&action=index" class="btn small">View Orders</a>
    </div>

    <!-- Vouchers Card -->
    <div class="panel" style="padding: 20px; text-align: center;">
        <div style="font-size: 36px; font-weight: bold; color: #dc3545; margin-bottom: 10px;">
            <?= number_format($stats['total_vouchers']); ?>
        </div>
        <div style="font-size: 18px; color: #666; margin-bottom: 10px;">Total Vouchers</div>
        <div style="font-size: 14px; color: #999; margin-bottom: 15px;">
            <?= number_format($stats['active_vouchers']); ?> active
        </div>
        <a href="?module=admin&resource=vouchers&action=index" class="btn small">View Vouchers</a>
    </div>

    <!-- Revenue Card -->
    <div class="panel" style="padding: 20px; text-align: center;">
        <div style="font-size: 36px; font-weight: bold; color: #17a2b8; margin-bottom: 10px;">
            RM <?= number_format($stats['total_revenue'], 2); ?>
        </div>
        <div style="font-size: 18px; color: #666;">Total Revenue</div>
    </div>

    <!-- PayLater Pending Card -->
    <div class="panel" style="padding: 20px; text-align: center;">
        <div style="font-size: 36px; font-weight: bold; color: #ff9800; margin-bottom: 10px;">
            <?= number_format($stats['paylater_pending'] ?? 0); ?>
        </div>
        <div style="font-size: 18px; color: #666; margin-bottom: 10px;">PayLater Pending Bills</div>
        <div style="font-size: 14px; color: #999;">RM <?= number_format($stats['paylater_outstanding'] ?? 0, 2); ?> outstanding</div>
        <a href="?module=admin&resource=paylater&action=index" class="btn small" style="margin-top: 10px;">View PayLater</a>
    </div>

    <!-- PayLater Interest Revenue Card -->
    <div class="panel" style="padding: 20px; text-align: center;">
        <div style="font-size: 36px; font-weight: bold; color: #9c27b0; margin-bottom: 10px;">
            RM <?= number_format($stats['paylater_interest_revenue'] ?? 0, 2); ?>
        </div>
        <div style="font-size: 18px; color: #666;">PayLater Interest Revenue</div>
    </div>
</div>

<!-- Charts Section -->
<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(500px, 1fr)); gap: 20px; margin-bottom: 40px;">
    <!-- Revenue Over Time Chart -->
    <section class="panel">
        <h2 style="margin-bottom: 20px;">Revenue Over Time (Last 12 Months)</h2>
        <?php if (empty($revenueByMonth)): ?>
            <p style="color: #999; text-align: center; padding: 40px;">No revenue data available for the last 12 months.</p>
        <?php else: ?>
            <canvas id="revenueChart" style="max-height: 300px;"></canvas>
        <?php endif; ?>
    </section>

    <!-- Orders by Status Chart -->
    <section class="panel">
        <h2 style="margin-bottom: 20px;">Orders by Status</h2>
        <?php if (empty($ordersByStatus)): ?>
            <p style="color: #999; text-align: center; padding: 40px;">No orders data available.</p>
        <?php else: ?>
            <canvas id="ordersStatusChart" style="max-height: 300px;"></canvas>
        <?php endif; ?>
    </section>
</div>

<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(500px, 1fr)); gap: 20px; margin-bottom: 40px;">
    <!-- Orders Over Time Chart -->
    <section class="panel">
        <h2 style="margin-bottom: 20px;">Orders Over Time (Last 30 Days)</h2>
        <?php if (empty($ordersOverTime)): ?>
            <p style="color: #999; text-align: center; padding: 40px;">No orders data available for the last 30 days.</p>
        <?php else: ?>
            <canvas id="ordersTimeChart" style="max-height: 300px;"></canvas>
        <?php endif; ?>
    </section>

    <!-- New Members Over Time Chart -->
    <section class="panel">
        <h2 style="margin-bottom: 20px;">New Members (Last 12 Months)</h2>
        <?php if (empty($membersByMonth)): ?>
            <p style="color: #999; text-align: center; padding: 40px;">No member registration data available for the last 12 months.</p>
        <?php else: ?>
            <canvas id="membersChart" style="max-height: 300px;"></canvas>
        <?php endif; ?>
    </section>
</div>

<!-- Top Selling Products Chart -->
<section class="panel" style="margin-bottom: 40px;">
    <h2 style="margin-bottom: 20px;">Top Selling Products (Last 30 Days)</h2>
    <?php if (empty($topProducts)): ?>
        <p style="color: #999; text-align: center; padding: 40px;">No product sales data available for the last 30 days.</p>
    <?php else: ?>
        <canvas id="topProductsChart" style="max-height: 400px;"></canvas>
    <?php endif; ?>
</section>

<!-- Recent Orders -->
<section class="panel">
    <h2 style="margin-bottom: 20px;">Recent Orders</h2>
    <?php if (empty($recentOrders)): ?>
        <p style="color: #999; text-align: center; padding: 40px;">No orders yet.</p>
    <?php else: ?>
        <table class="table">
            <thead>
                <tr>
                    <th>Order ID</th>
                    <th>Member</th>
                    <th>Status</th>
                    <th>Total</th>
                    <th>Date</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($recentOrders as $order): ?>
                    <tr>
                        <td>#<?= $order['id']; ?></td>
                        <td><?= encode($order['member_name'] ?? 'N/A'); ?></td>
                        <td>
                            <span class="badge <?= $order['status']; ?>">
                                <?= encode(ucfirst($order['status'])); ?>
                            </span>
                        </td>
                        <td>RM <?= number_format($order['total_amount'], 2); ?></td>
                        <td><?= encode($order['created_at']); ?></td>
                        <td>
                            <a class="btn small" href="?module=admin&resource=orders&action=detail&id=<?= $order['id']; ?>">
                                View
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <div style="text-align: right; margin-top: 15px;">
            <a href="?module=admin&resource=orders&action=index" class="btn">View All Orders</a>
        </div>
    <?php endif; ?>
</section>

<!-- Quick Actions -->
<section class="panel">
    <h2 style="margin-bottom: 20px;">Quick Actions</h2>
    <div style="display: flex; gap: 15px; flex-wrap: wrap;">
        <a href="?module=admin&resource=products&action=create" class="btn primary">+ New Product</a>
        <a href="?module=admin&resource=categories&action=create" class="btn primary">+ New Category</a>
        <a href="?module=admin&resource=members&action=create" class="btn primary">+ New Member</a>
        <a href="?module=admin&resource=vouchers&action=create" class="btn primary">+ New Voucher</a>
        <a href="?module=shop&action=home" class="btn secondary">View Store</a>
    </div>
</section>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Check if Chart.js is loaded
    if (typeof Chart === 'undefined') {
        console.error('Chart.js is not loaded');
        return;
    }

// Revenue Over Time Chart (Last 12 Months)
<?php if (!empty($revenueByMonth)): ?>
const revenueData = <?= json_encode($revenueByMonth); ?>;
if (document.getElementById('revenueChart')) {
    const revenueLabels = revenueData.map(item => {
        const date = new Date(item.month + '-01');
        return date.toLocaleDateString('en-US', { year: 'numeric', month: 'short' });
    });
    const revenueValues = revenueData.map(item => parseFloat(item.revenue));

    new Chart(document.getElementById('revenueChart'), {
    type: 'line',
    data: {
        labels: revenueLabels,
        datasets: [{
            label: 'Revenue (RM)',
            data: revenueValues,
            borderColor: 'rgb(75, 192, 192)',
            backgroundColor: 'rgba(75, 192, 192, 0.2)',
            tension: 0.4,
            fill: true
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: true,
        plugins: {
            legend: {
                display: true,
                position: 'top'
            },
            tooltip: {
                callbacks: {
                    label: function(context) {
                        return 'RM ' + context.parsed.y.toFixed(2);
                    }
                }
            }
        },
        scales: {
            y: {
                beginAtZero: true,
                ticks: {
                    callback: function(value) {
                        return 'RM ' + value.toFixed(0);
                    }
                }
            }
        }
    }
});
}
<?php endif; ?>

// Orders by Status Chart
<?php if (!empty($ordersByStatus)): ?>
const ordersStatusData = <?= json_encode($ordersByStatus); ?>;
if (document.getElementById('ordersStatusChart')) {
    const statusLabels = ordersStatusData.map(item => item.status.charAt(0).toUpperCase() + item.status.slice(1));
    const statusCounts = ordersStatusData.map(item => parseInt(item.count));
    const statusColors = {
        'pending': 'rgba(255, 206, 86, 0.8)',
        'processing': 'rgba(54, 162, 235, 0.8)',
        'shipped': 'rgba(75, 192, 192, 0.8)',
        'completed': 'rgba(75, 192, 192, 0.8)',
        'cancelled': 'rgba(255, 99, 132, 0.8)'
    };
    const backgroundColors = ordersStatusData.map(item => {
        const status = item.status.toLowerCase();
        return statusColors[status] || 'rgba(153, 102, 255, 0.8)';
    });

    new Chart(document.getElementById('ordersStatusChart'), {
    type: 'doughnut',
    data: {
        labels: statusLabels,
        datasets: [{
            data: statusCounts,
            backgroundColor: backgroundColors,
            borderWidth: 2,
            borderColor: '#fff'
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: true,
        plugins: {
            legend: {
                position: 'bottom'
            },
            tooltip: {
                callbacks: {
                    label: function(context) {
                        const label = context.label || '';
                        const value = context.parsed || 0;
                        const total = context.dataset.data.reduce((a, b) => a + b, 0);
                        const percentage = ((value / total) * 100).toFixed(1);
                        return label + ': ' + value + ' (' + percentage + '%)';
                    }
                }
            }
        }
    }
});
}
<?php endif; ?>

// Orders Over Time Chart (Last 30 Days)
<?php if (!empty($ordersOverTime)): ?>
const ordersTimeData = <?= json_encode($ordersOverTime); ?>;
if (document.getElementById('ordersTimeChart')) {
    const ordersTimeLabels = ordersTimeData.map(item => {
        const date = new Date(item.date);
        return date.toLocaleDateString('en-US', { month: 'short', day: 'numeric' });
    });
    const ordersTimeValues = ordersTimeData.map(item => parseInt(item.count));

    new Chart(document.getElementById('ordersTimeChart'), {
    type: 'line',
    data: {
        labels: ordersTimeLabels,
        datasets: [{
            label: 'Number of Orders',
            data: ordersTimeValues,
            borderColor: 'rgb(255, 99, 132)',
            backgroundColor: 'rgba(255, 99, 132, 0.2)',
            tension: 0.4,
            fill: true
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: true,
        plugins: {
            legend: {
                display: true,
                position: 'top'
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
<?php endif; ?>

// New Members Over Time Chart (Last 12 Months)
<?php if (!empty($membersByMonth)): ?>
const membersData = <?= json_encode($membersByMonth); ?>;
if (document.getElementById('membersChart')) {
    const membersLabels = membersData.map(item => {
        const date = new Date(item.month + '-01');
        return date.toLocaleDateString('en-US', { year: 'numeric', month: 'short' });
    });
    const membersValues = membersData.map(item => parseInt(item.count));

    new Chart(document.getElementById('membersChart'), {
    type: 'bar',
    data: {
        labels: membersLabels,
        datasets: [{
            label: 'New Members',
            data: membersValues,
            backgroundColor: 'rgba(54, 162, 235, 0.8)',
            borderColor: 'rgb(54, 162, 235)',
            borderWidth: 1
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: true,
        plugins: {
            legend: {
                display: true,
                position: 'top'
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
<?php endif; ?>

// Top Selling Products Chart
<?php if (!empty($topProducts)): ?>
const topProductsData = <?= json_encode($topProducts); ?>;
if (document.getElementById('topProductsChart')) {
    const productLabels = topProductsData.map(item => item.name.length > 30 ? item.name.substring(0, 30) + '...' : item.name);
    const productSales = topProductsData.map(item => parseInt(item.total_sold));

    new Chart(document.getElementById('topProductsChart'), {
    type: 'bar',
    data: {
        labels: productLabels,
        datasets: [{
            label: 'Units Sold',
            data: productSales,
            backgroundColor: 'rgba(75, 192, 192, 0.8)',
            borderColor: 'rgb(75, 192, 192)',
            borderWidth: 1
        }]
    },
    options: {
        indexAxis: 'y',
        responsive: true,
        maintainAspectRatio: true,
        plugins: {
            legend: {
                display: true,
                position: 'top'
            }
        },
        scales: {
            x: {
                beginAtZero: true,
                ticks: {
                    stepSize: 1
                }
            }
        }
    }
});
}
<?php endif; ?>

// PayLater by Status Chart
<?php if (!empty($paylaterByStatus ?? [])): ?>
const paylaterStatusData = <?= json_encode($paylaterByStatus); ?>;
if (document.getElementById('paylaterStatusChart')) {
    const statusLabels = paylaterStatusData.map(item => item.status.charAt(0).toUpperCase() + item.status.slice(1));
    const statusCounts = paylaterStatusData.map(item => parseInt(item.count));
    const statusAmounts = paylaterStatusData.map(item => parseFloat(item.total_amount));
    new Chart(document.getElementById('paylaterStatusChart'), {
        type: 'doughnut',
        data: {
            labels: statusLabels,
            datasets: [{
                label: 'Number of Bills',
                data: statusCounts,
                backgroundColor: [
                    'rgba(255, 99, 132, 0.8)',
                    'rgba(75, 192, 192, 0.8)',
                    'rgba(153, 102, 255, 0.8)',
                    'rgba(255, 206, 86, 0.8)'
                ],
                borderWidth: 2,
                borderColor: '#fff'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                legend: { position: 'bottom' },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            const label = context.label || '';
                            const value = context.parsed || 0;
                            const total = context.dataset.data.reduce((a, b) => a + b, 0);
                            const percentage = ((value / total) * 100).toFixed(1);
                            const index = context.dataIndex;
                            const amount = statusAmounts[index] || 0;
                            return label + ': ' + value + ' bills (' + percentage + '%) - RM ' + amount.toFixed(2);
                        }
                    }
                }
            }
        }
    });
}
<?php endif; ?>

// PayLater by Tenure Chart
<?php if (!empty($paylaterByTenure ?? [])): ?>
const paylaterTenureData = <?= json_encode($paylaterByTenure); ?>;
if (document.getElementById('paylaterTenureChart')) {
    const tenureLabels = paylaterTenureData.map(item => item.tenure + ' months');
    const tenureAmounts = paylaterTenureData.map(item => parseFloat(item.total_amount));
    new Chart(document.getElementById('paylaterTenureChart'), {
        type: 'bar',
        data: {
            labels: tenureLabels,
            datasets: [{
                label: 'Total Amount (RM)',
                data: tenureAmounts,
                backgroundColor: 'rgba(54, 162, 235, 0.7)',
                borderColor: 'rgba(54, 162, 235, 1)',
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                legend: { display: true, position: 'top' },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return 'RM ' + context.parsed.y.toFixed(2);
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return 'RM ' + value.toFixed(0);
                        }
                    }
                }
            }
        }
    });
}
<?php endif; ?>

// PayLater Collected vs Outstanding Over Time
<?php if (!empty($paylaterOutstandingByMonth ?? []) || !empty($paylaterCollectedByMonth ?? [])): ?>
const paylaterOutstandingData = <?= json_encode($paylaterOutstandingByMonth ?? []); ?>;
const paylaterCollectedData = <?= json_encode($paylaterCollectedByMonth ?? []); ?>;
if (document.getElementById('paylaterCollectedVsOutstandingChart')) {
    // Merge months from both datasets
    const allMonths = new Set();
    paylaterOutstandingData.forEach(item => allMonths.add(item.month));
    paylaterCollectedData.forEach(item => allMonths.add(item.month));
    const sortedMonths = Array.from(allMonths).sort();

    const outstandingMap = {};
    paylaterOutstandingData.forEach(item => {
        outstandingMap[item.month] = parseFloat(item.outstanding);
    });

    const collectedMap = {};
    paylaterCollectedData.forEach(item => {
        collectedMap[item.month] = parseFloat(item.collected);
    });

    const monthLabels = sortedMonths.map(month => {
        const date = new Date(month + '-01');
        return date.toLocaleDateString('en-US', { year: 'numeric', month: 'short' });
    });

    const outstandingValues = sortedMonths.map(month => outstandingMap[month] || 0);
    const collectedValues = sortedMonths.map(month => collectedMap[month] || 0);

    new Chart(document.getElementById('paylaterCollectedVsOutstandingChart'), {
        type: 'line',
        data: {
            labels: monthLabels,
            datasets: [{
                label: 'Outstanding (RM)',
                data: outstandingValues,
                borderColor: 'rgb(255, 99, 132)',
                backgroundColor: 'rgba(255, 99, 132, 0.2)',
                tension: 0.4,
                fill: true
            }, {
                label: 'Collected (RM)',
                data: collectedValues,
                borderColor: 'rgb(75, 192, 192)',
                backgroundColor: 'rgba(75, 192, 192, 0.2)',
                tension: 0.4,
                fill: true
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                legend: { display: true, position: 'top' },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return context.dataset.label + ': RM ' + context.parsed.y.toFixed(2);
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return 'RM ' + value.toFixed(0);
                        }
                    }
                }
            }
        }
    });
}
<?php endif; ?>
}); // End DOMContentLoaded
</script>

