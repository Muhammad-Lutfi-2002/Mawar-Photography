<?php
require_once 'config.php';

// Check if user is logged in
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: login.php');
    exit();
}

// Initialize arrays
$monthly_revenue = [];
$package_performance = [];
$status_distribution = [];

// Get reports data
try {
    // Monthly Revenue
    $monthly_revenue = $conn->query("
        SELECT 
            DATE_FORMAT(booking_date, '%Y-%m') as month,
            COALESCE(SUM(total_price), 0) as revenue,
            COUNT(*) as booking_count
        FROM bookings
        WHERE status = 'completed'
        GROUP BY DATE_FORMAT(booking_date, '%Y-%m')
        ORDER BY month DESC
        LIMIT 12
    ")->fetchAll(PDO::FETCH_ASSOC);

    // Package Performance
    $package_performance = $conn->query("
        SELECT 
            p.package_name,
            p.package_type,
            COALESCE(COUNT(b.booking_id), 0) as total_bookings,
            COALESCE(SUM(b.total_price), 0) as total_revenue,
            COALESCE(AVG(b.total_price), 0) as avg_revenue
        FROM packages p
        LEFT JOIN bookings b ON p.package_name = b.package_name
        GROUP BY p.package_name, p.package_type
        ORDER BY total_revenue DESC
    ")->fetchAll(PDO::FETCH_ASSOC);

    // Status Distribution
    $status_distribution = $conn->query("
        SELECT 
            status,
            COUNT(*) as count
        FROM bookings
        GROUP BY status
    ")->fetchAll(PDO::FETCH_ASSOC);

} catch(PDOException $e) {
    error_log("Error fetching report data: " . $e->getMessage());
}

// Calculate totals
$total_revenue = array_sum(array_map(function($item) {
    return $item['revenue'] ?? 0;
}, $monthly_revenue));

$total_bookings = array_sum(array_map(function($item) {
    return $item['booking_count'] ?? 0;
}, $monthly_revenue));

$avg_booking_value = $total_bookings > 0 ? ($total_revenue / $total_bookings) : 0;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reports - Mawar Photography</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        :root {
            --primary-color: #b8960c;
            --secondary-color: #858796;
            --sidebar-width: 250px;
        }

        body {
            background-color: #f8f9fc;
            font-family: 'Nunito', sans-serif;
        }

        .sidebar {
            position: fixed;
            left: 0;
            top: 0;
            width: var(--sidebar-width);
            height: 100vh;
            background: linear-gradient(180deg, var(--primary-color) 0%, #224abe 100%);
            color: white;
            z-index: 1000;
            transition: transform 0.3s ease;
        }

        .main-content {
            margin-left: var(--sidebar-width);
            padding: 25px;
            transition: margin 0.3s ease;
        }

        .card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
            margin-bottom: 25px;
        }

        .stats-card {
            background: linear-gradient(45deg, var(--primary-color) 0%, #224abe 100%);
            color: white;
            padding: 20px;
            border-radius: 15px;
        }

        .chart-container {
            position: relative;
            margin: auto;
            height: 300px;
        }

        .nav-link {
            color: rgba(255, 255, 255, 0.8);
            padding: 1rem;
            margin: 0.2rem 0;
            border-radius: 10px;
            transition: all 0.3s;
        }

        .nav-link:hover, .nav-link.active {
            color: white;
            background: rgba(255, 255, 255, 0.1);
        }

        .table-responsive {
            border-radius: 15px;
            background: white;
            padding: 20px;
        }

        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(-100%);
            }

            .main-content {
                margin-left: 0;
            }

            .sidebar.active {
                transform: translateX(0);
            }
        }

        .dashboard-card {
            background: white;
            border-radius: 15px;
            padding: 20px;
            height: 100%;
        }

        .card-header {
            background: none;
            border-bottom: 2px solid var(--primary-color);
            padding-bottom: 15px;
        }

        .card-title {
            color: var(--primary-color);
            font-weight: bold;
            margin: 0;
        }

        .status-badge {
            padding: 0.5rem 1rem;
            border-radius: 30px;
            font-weight: 600;
        }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <nav class="sidebar">
        <div class="p-4">
            <h3 class="text-center mb-4">Mawar Photography</h3>
            <ul class="nav flex-column">
                <li class="nav-item">
                    <a class="nav-link" href="dashboard.php">
                        <i class="fas fa-tachometer-alt me-2"></i>Dashboard
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="bookings.php">
                        <i class="fas fa-calendar-alt me-2"></i>Bookings
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="packages.php">
                        <i class="fas fa-box me-2"></i>Packages
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link active" href="reports.php">
                        <i class="fas fa-chart-bar me-2"></i>Reports
                    </a>
                </li>
            </ul>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="main-content">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="h3 mb-0 text-gray-800">Reports & Analytics</h1>
            <div class="user-info">
                <span class="me-3"><?php echo htmlspecialchars($_SESSION['full_name'] ?? ''); ?></span>
                <a href="logout.php" class="btn btn-danger btn-sm">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </a>
            </div>
        </div>

        <!-- Summary Cards -->
        <div class="row mb-4">
            <div class="col-xl-3 col-md-6">
                <div class="stats-card">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h6 class="text-white-50">Total Revenue</h6>
                            <h2 class="mb-0">Rp <?php echo number_format($total_revenue, 0, ',', '.'); ?></h2>
                        </div>
                        <div>
                            <i class="fas fa-dollar-sign fa-2x text-white-50"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6">
                <div class="stats-card">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h6 class="text-white-50">Total Bookings</h6>
                            <h2 class="mb-0"><?php echo number_format($total_bookings, 0, ',', '.'); ?></h2>
                        </div>
                        <div>
                            <i class="fas fa-calendar-check fa-2x text-white-50"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6">
                <div class="stats-card">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h6 class="text-white-50">Average Booking Value</h6>
                            <h2 class="mb-0">Rp <?php echo number_format($avg_booking_value, 0, ',', '.'); ?></h2>
                        </div>
                        <div>
                            <i class="fas fa-chart-line fa-2x text-white-50"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6">
                <div class="stats-card">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h6 class="text-white-50">Active Packages</h6>
                            <h2 class="mb-0"><?php echo count($package_performance); ?></h2>
                        </div>
                        <div>
                            <i class="fas fa-box fa-2x text-white-50"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Charts Row -->
        <div class="row mb-4">
            <!-- Revenue Trend Chart -->
            <div class="col-xl-8">
                <div class="card">
                    <div class="card-header">
                        <h6 class="card-title">Monthly Revenue Trend</h6>
                    </div>
                    <div class="card-body">
                        <canvas id="revenueChart"></canvas>
                    </div>
                </div>
            </div>
            <!-- Status Distribution Chart -->
            <div class="col-xl-4">
                <div class="card">
                    <div class="card-header">
                        <h6 class="card-title">Booking Status Distribution</h6>
                    </div>
                    <div class="card-body">
                        <canvas id="statusChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Package Performance Table -->
        <div class="card">
            <div class="card-header">
                <h6 class="card-title">Package Performance</h6>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover" id="packagePerformanceTable">
                        <thead>
                            <tr>
                                <th>Package Name</th>
                                <th>Type</th>
                                <th>Total Bookings</th>
                                <th>Total Revenue</th>
                                <th>Average Revenue</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($package_performance as $package): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($package['package_name']); ?></td>
                                <td><?php echo ucfirst($package['package_type']); ?></td>
                                <td><?php echo number_format($package['total_bookings'], 0, ',', '.'); ?></td>
                                <td>Rp <?php echo number_format($package['total_revenue'], 0, ',', '.'); ?></td>
                                <td>Rp <?php echo number_format($package['avg_revenue'], 0, ',', '.'); ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </main>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>
    <script>
        $(document).ready(function() {
            // Initialize DataTable
            $('#packagePerformanceTable').DataTable({
                "pageLength": 10,
                "order": [[3, "desc"]]
            });

            // Revenue Chart
            const revenueChartData = <?php echo json_encode(array_reverse($monthly_revenue)); ?>;
            const revenueChart = new Chart(document.getElementById('revenueChart'), {
                type: 'line',
                data: {
                    labels: revenueChartData.map(item => item.month),
                    datasets: [{
                        label: 'Monthly Revenue',
                        data: revenueChartData.map(item => item.revenue),
                        borderColor: '#b8960c',
                        tension: 0.1,
                        fill: true,
                        backgroundColor: 'rgba(184, 150, 12, 0.1)'
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: {
                            position: 'top',
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                callback: function(value) {
                                    return 'Rp ' + value.toLocaleString('id-ID');
                                }
                            }
                        }
                    }
                }
            });

            // Status Distribution Chart
            const statusChartData = <?php echo json_encode($status_distribution); ?>;
            const statusChart = new Chart(document.getElementById('statusChart'), {
                type: 'doughnut',
                data: {
                    labels: statusChartData.map(item => item.status),
                    datasets: [{
                        data: statusChartData.map(item => item.count),
                        backgroundColor: [
                            '#28a745',
                            '#ffc107',
                            '#dc3545',
                            '#17a2b8'
                        ]
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: {
                            position: 'bottom'
                        }
                    }
                }
            });
        });
    </script>
</body>
</html>