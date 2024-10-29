<?php
require_once 'config.php';

// Check if user is logged in
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: login.php');
    exit();
}

// Get summary statistics
$stats = [
    'total_bookings' => 0,
    'pending_bookings' => 0,
    'revenue_this_month' => 0,
    'completed_bookings' => 0
];

try {
    // Total bookings
    $query = "SELECT COUNT(*) FROM bookings";
    $stmt = $conn->query($query);
    $stats['total_bookings'] = $stmt->fetchColumn();

    // Pending bookings
    $query = "SELECT COUNT(*) FROM bookings WHERE status = 'pending'";
    $stmt = $conn->query($query);
    $stats['pending_bookings'] = $stmt->fetchColumn();

    // Revenue this month
    $query = "SELECT SUM(total_price) FROM bookings 
              WHERE MONTH(created_at) = MONTH(CURRENT_DATE()) 
              AND YEAR(created_at) = YEAR(CURRENT_DATE())
              AND status != 'cancelled'";
    $stmt = $conn->query($query);
    $stats['revenue_this_month'] = $stmt->fetchColumn() ?: 0;

    // Completed bookings
    $query = "SELECT COUNT(*) FROM bookings WHERE status = 'completed'";
    $stmt = $conn->query($query);
    $stats['completed_bookings'] = $stmt->fetchColumn();

    // Recent bookings
    $query = "SELECT * FROM bookings ORDER BY created_at DESC LIMIT 5";
    $stmt = $conn->query($query);
    $recent_bookings = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch(PDOException $e) {
    // Handle error appropriately
    error_log($e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Mawar Photography</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.32/dist/sweetalert2.min.css">
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

        .sidebar .nav-link {
            color: rgba(255,255,255,0.8);
            border-radius: 8px;
            margin: 8px 0;
            transition: all 0.3s ease;
        }

        .sidebar .nav-link:hover,
        .sidebar .nav-link.active {
            background-color: rgba(255,255,255,0.1);
            color: white;
            transform: translateX(5px);
        }

        .main-content {
            margin-left: var(--sidebar-width);
            padding: 30px;
            transition: all 0.3s ease;
        }

        .stats-card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            transition: transform 0.3s ease;
            background: white;
            height: 100%;
        }

        .stats-card:hover {
            transform: translateY(-5px);
        }

        .stats-icon {
            width: 50px;
            height: 50px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            margin-bottom: 15px;
        }

        .recent-bookings-card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }

        .chart-card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            background: white;
            padding: 20px;
            margin-bottom: 30px;
        }

        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(-100%);
            }

            .sidebar.active {
                transform: translateX(0);
            }

            .main-content {
                margin-left: 0;
            }
        }

        .fade-in {
            animation: fadeIn 0.5s ease-in;
        }

        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        ::-webkit-scrollbar {
            width: 8px;
        }

        ::-webkit-scrollbar-track {
            background: #f1f1f1;
        }

        ::-webkit-scrollbar-thumb {
            background: var(--primary-color);
            border-radius: 4px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: var(--secondary-color);
        }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <nav class="sidebar">
        <div class="p-4">
            <h3 class="text-center mb-4">
                <i class="fas fa-camera-retro me-2"></i>
                Mawar Photography
            </h3>
            <ul class="nav flex-column">
                <li class="nav-item">
                    <a class="nav-link active" href="dashboard.php">
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
                    <a class="nav-link" href="reports.php">
                        <i class="fas fa-chart-bar me-2"></i>Reports
                    </a>
                </li>
            </ul>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="main-content">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="h3 mb-0">Dashboard Overview</h1>
            <div class="user-info d-flex align-items-center">
                <span class="me-3">
                    <i class="fas fa-user-circle me-2"></i>
                    <?php echo htmlspecialchars($_SESSION['full_name']); ?>
                </span>
                <a href="logout.php" class="btn btn-danger btn-sm">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </a>
            </div>
        </div>

        <!-- Statistics Cards -->
        <div class="row g-4 mb-4">
            <div class="col-md-3">
                <div class="stats-card p-4">
                    <div class="stats-icon bg-primary text-white">
                        <i class="fas fa-calendar-check"></i>
                    </div>
                    <h3 class="fs-2"><?php echo number_format($stats['total_bookings']); ?></h3>
                    <p class="text-muted mb-0">Total Bookings</p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stats-card p-4">
                    <div class="stats-icon bg-warning text-white">
                        <i class="fas fa-clock"></i>
                    </div>
                    <h3 class="fs-2"><?php echo number_format($stats['pending_bookings']); ?></h3>
                    <p class="text-muted mb-0">Pending Bookings</p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stats-card p-4">
                    <div class="stats-icon bg-success text-white">
                        <i class="fas fa-money-bill-wave"></i>
                    </div>
                    <h3 class="fs-2">Rp <?php echo number_format($stats['revenue_this_month']); ?></h3>
                    <p class="text-muted mb-0">Revenue This Month</p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stats-card p-4">
                    <div class="stats-icon bg-info text-white">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <h3 class="fs-2"><?php echo number_format($stats['completed_bookings']); ?></h3>
                    <p class="text-muted mb-0">Completed Bookings</p>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Recent Bookings -->
            <div class="col-md-8">
                <div class="card recent-bookings-card mb-4">
                    <div class="card-header bg-white">
                        <h5 class="card-title mb-0">Recent Bookings</h5>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead>
                                <tr>
                                    <th>Customer</th>
                                    <th>Service</th>
                                    <th>Date</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach($recent_bookings as $booking): ?>
                                    <tr>
                                        <td>
                                            <strong><?php echo htmlspecialchars($booking['customer_name']); ?></strong>
                                            <br>
                                            <small class="text-muted"><?php echo htmlspecialchars($booking['phone_number']); ?></small>
                                        </td>
                                        <td><?php echo ucfirst($booking['service_type']); ?></td>
                                        <td><?php echo date('d M Y', strtotime($booking['booking_date'])); ?></td>
                                        <td>
                                            <?php
                                            $status_colors = [
                                                'pending' => 'warning',
                                                'confirmed' => 'primary',
                                                'completed' => 'success',
                                                'cancelled' => 'danger'
                                            ];
                                            $color = $status_colors[$booking['status']] ?? 'secondary';
                                            ?>
                                            <span class="badge bg-<?php echo $color; ?>">
                                                <?php echo ucfirst($booking['status']); ?>
                                            </span>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="col-md-4">
                <div class="card mb-4">
                    <div class="card-header bg-white">
                        <h5 class="card-title mb-0">Quick Actions</h5>
                    </div>
                    <div class="card-body">
                        <div class="d-grid gap-2">
                            <a href="new-booking.php" class="btn btn-primary">
                                <i class="fas fa-plus-circle me-2"></i>New Booking
                            </a>
                            <a href="reports.php" class="btn btn-outline-primary">
                                <i class="fas fa-file-alt me-2"></i>Generate Report
                            </a>
                            <a href="packages.php" class="btn btn-outline-primary">
                                <i class="fas fa-box me-2"></i>Manage Packages
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Calendar Preview -->
                <div class="card">
                    <div class="card-header bg-white">
                        <h5 class="card-title mb-0">Upcoming Events</h5>
                    </div>
                    <div class="card-body">
                        <div id="mini-calendar">
                            <!-- Calendar content would go here -->
                            <!-- You can integrate a calendar library like FullCalendar -->
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.32/dist/sweetalert2.all.min.js"></script>
    <script>
        // Mobile sidebar toggle
        document.addEventListener('DOMContentLoaded', function() {
            const handleResize = () => {
                if (window.innerWidth <= 768) {
                    document.querySelector('.sidebar').classList.remove('active');
                }
            };

            window.addEventListener('resize', handleResize);
            handleResize();
        });
    </script>
</body>
</html>