<?php
require_once 'config.php';

// Check if user is logged in
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: login.php');
    exit();
}

// Get package statistics
$package_stats = [];
try {
    $stmt = $conn->query("
        SELECT 
            p.package_name,
            p.package_type,
            p.base_price,
            COUNT(b.booking_id) as booking_count,
            SUM(CASE WHEN b.status = 'completed' THEN 1 ELSE 0 END) as completed_count,
            SUM(CASE WHEN b.status = 'pending' THEN 1 ELSE 0 END) as pending_count,
            SUM(b.total_price) as total_revenue
        FROM packages p
        LEFT JOIN bookings b ON p.package_name = b.package_name
        GROUP BY p.package_id, p.package_name, p.package_type, p.base_price
        ORDER BY p.package_type, p.base_price DESC
    ");
    $package_stats = $stmt->fetchAll();
} catch(PDOException $e) {
    error_log("Error fetching package statistics: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Packages - Mawar Photography</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css">
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
            transition: transform 0.2s;
        }

        .card:hover {
            transform: translateY(-5px);
        }

        .stats-card {
            background: linear-gradient(45deg, var(--primary-color) 0%, #224abe 100%);
            color: white;
            border-radius: 15px;
            padding: 20px;
            margin-bottom: 25px;
        }

        .package-table {
            background: white;
            border-radius: 15px;
            padding: 20px;
            margin-top: 25px;
        }

        .btn-primary {
            background: var(--primary-color);
            border: none;
        }

        .btn-primary:hover {
            background: #8e7209;
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

        .dataTables_wrapper .dataTables_paginate .paginate_button.current {
            background: var(--primary-color) !important;
            border-color: var(--primary-color) !important;
            color: white !important;
        }

        .status-badge {
            padding: 0.5rem 1rem;
            border-radius: 30px;
            font-weight: 600;
        }

        .status-active {
            background: #e8f5e9;
            color: #2e7d32;
        }

        .status-inactive {
            background: #ffebee;
            color: #c62828;
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

        /* Custom Datatable Styling */
        .dataTables_wrapper {
            padding: 20px 0;
        }

        table.dataTable thead th {
            background-color: #f8f9fc;
            border-bottom: 2px solid var(--primary-color);
        }

        .dataTables_length select,
        .dataTables_filter input {
            border-radius: 20px;
            padding: 5px 10px;
            border: 1px solid #ddd;
        }

        .package-actions {
            display: flex;
            gap: 10px;
            justify-content: flex-end;
        }

        .package-actions button {
            padding: 5px 15px;
            border-radius: 20px;
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
                    <a class="nav-link active" href="packages.php">
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
            <h1 class="h3 mb-0 text-gray-800">Package Management</h1>
            <div class="user-info">
                <span class="me-3"><?php echo htmlspecialchars($_SESSION['full_name']); ?></span>
                <a href="logout.php" class="btn btn-danger btn-sm">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </a>
            </div>
        </div>

        <!-- Statistics Cards -->
        <div class="row">
            <div class="col-xl-3 col-md-6">
                <div class="stats-card">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h6 class="text-white-50">Total Packages</h6>
                            <h2 class="mb-0"><?php echo count($package_stats); ?></h2>
                        </div>
                        <div>
                            <i class="fas fa-box fa-2x text-white-50"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6">
                <div class="stats-card">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h6 class="text-white-50">Total Bookings</h6>
                            <h2 class="mb-0"><?php echo array_sum(array_column($package_stats, 'booking_count')); ?></h2>
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
                            <h6 class="text-white-50">Active Packages</h6>
                            <h2 class="mb-0"><?php echo count(array_filter($package_stats, function($p) { return $p['booking_count'] > 0; })); ?></h2>
                        </div>
                        <div>
                            <i class="fas fa-check-circle fa-2x text-white-50"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6">
                <div class="stats-card">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h6 class="text-white-50">Total Revenue</h6>
                            <h2 class="mb-0">Rp <?php echo number_format(array_sum(array_column($package_stats, 'total_revenue'))); ?></h2>
                        </div>
                        <div>
                            <i class="fas fa-dollar-sign fa-2x text-white-50"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Packages Table -->
        <div class="card package-table">
            <div class="card-header py-3 d-flex justify-content-between align-items-center">
                <h6 class="m-0 font-weight-bold text-primary">Package List</h6>
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addPackageModal">
                    <i class="fas fa-plus me-1"></i> Add New Package
                </button>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover" id="packagesTable">
                        <thead>
                            <tr>
                                <th>Package Name</th>
                                <th>Type</th>
                                <th>Base Price</th>
                                <th>Total Bookings</th>
                                <th>Status</th>
                                <th>Revenue</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($package_stats as $package): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($package['package_name']); ?></td>
                                <td><?php echo ucfirst($package['package_type']); ?></td>
                                <td>Rp <?php echo number_format($package['base_price']); ?></td>
                                <td><?php echo $package['booking_count']; ?></td>
                                <td>
                                    <span class="status-badge <?php echo $package['booking_count'] > 0 ? 'status-active' : 'status-inactive'; ?>">
                                        <?php echo $package['booking_count'] > 0 ? 'Active' : 'Inactive'; ?>
                                    </span>
                                </td>
                                <td>Rp <?php echo number_format($package['total_revenue'] ?? 0); ?></td>
                                <td class="package-actions">
                                    <button class="btn btn-info btn-sm" onclick="viewPackageDetails('<?php echo htmlspecialchars($package['package_name']); ?>')">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <button class="btn btn-warning btn-sm" onclick="editPackage('<?php echo htmlspecialchars($package['package_name']); ?>')">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button class="btn btn-danger btn-sm" onclick="deletePackage('<?php echo htmlspecialchars($package['package_name']); ?>')">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </main>

    <!-- Package Details Modal -->
    <div class="modal fade" id="packageDetailsModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Package Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div id="packageDetailsContent"></div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>
    <script>
        $(document).ready(function() {
            $('#packagesTable').DataTable({
                "pageLength": 10,
                "order": [[1, "asc"]],
                "language": {
                    "search": "Search packages:",
                    "lengthMenu": "Show _MENU_ packages per page",
                }
            });
        });

        function viewPackageDetails(packageName) {
            const modal = new bootstrap.Modal(document.getElementById('packageDetailsModal'));
            
            $.ajax({
                url: 'get-package-details.php',
                type: 'POST',
                data: { package_name: packageName },
                success: function(response) {
                    $('#packageDetailsContent').html(response);
                    modal.show();
                },
                error: function() {
                    alert('Failed to load package details');
                }
            });
        }

        function editPackage(packageName) {
            // Implement edit functionality
            alert('Edit package: ' + packageName);
        }

        function deletePackage(packageName) {
            if (confirm('Are you sure you want to delete this package?')) {
                // Implement delete functionality
                alert('Delete package: ' + packageName);
            }
        }
    </script>
</body>
</html>