<?php
require_once 'config.php';

// Check if user is logged in
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: login.php');
    exit();
}

// Initialize filters
$status_filter = isset($_GET['status']) ? $_GET['status'] : '';
$service_filter = isset($_GET['service_type']) ? $_GET['service_type'] : '';
$search = isset($_GET['search']) ? $_GET['search'] : '';
$date_from = isset($_GET['date_from']) ? $_GET['date_from'] : '';
$date_to = isset($_GET['date_to']) ? $_GET['date_to'] : '';

// Build query with prepared statements
$query = "SELECT * FROM bookings WHERE 1=1";
$params = array();

if ($status_filter) {
    $query .= " AND status = :status";
    $params[':status'] = $status_filter;
}
if ($service_filter) {
    $query .= " AND service_type = :service_type";
    $params[':service_type'] = $service_filter;
}
if ($search) {
    $query .= " AND (customer_name LIKE :search OR phone_number LIKE :search_phone OR location LIKE :search_location)";
    $search_param = "%$search%";
    $params[':search'] = $search_param;
    $params[':search_phone'] = $search_param;
    $params[':search_location'] = $search_param;
}
if ($date_from) {
    $query .= " AND booking_date >= :date_from";
    $params[':date_from'] = $date_from;
}
if ($date_to) {
    $query .= " AND booking_date <= :date_to";
    $params[':date_to'] = $date_to . ' 23:59:59';
}

$query .= " ORDER BY created_at DESC";

// Prepare and execute query
$stmt = $conn->prepare($query);
$stmt->execute($params);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bookings - Mawar Photography</title>
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

        .card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            transition: transform 0.3s ease;
        }

        .card:hover {
            transform: translateY(-5px);
        }

        .filter-section {
            background: white;
            padding: 25px;
            border-radius: 15px;
            margin-bottom: 30px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }

        .form-control, .form-select {
            border-radius: 10px;
            border: 1px solid #dee2e6;
            padding: 10px 15px;
        }

        .btn {
            border-radius: 10px;
            padding: 10px 20px;
            font-weight: 500;
        }

        .btn-primary {
            background: var(--primary-color);
            border: none;
        }

        .btn-primary:hover {
            background: var(--secondary-color);
        }

        .table {
            background: white;
            border-radius: 15px;
            overflow: hidden;
        }

        .table th {
            background-color: var(--accent-color);
            border-bottom: none;
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.85rem;
        }

        .status-badge {
            padding: 8px 15px;
            border-radius: 20px;
            font-weight: 500;
            font-size: 0.85rem;
        }

        .action-buttons .btn {
            width: 35px;
            height: 35px;
            padding: 0;
            line-height: 35px;
            text-align: center;
            margin: 0 2px;
            border-radius: 8px;
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

            .filter-section .row {
                flex-direction: column;
            }

            .filter-section .col-md-2,
            .filter-section .col-md-3 {
                width: 100%;
                margin-bottom: 10px;
            }
        }

        /* Animation Classes */
        .fade-in {
            animation: fadeIn 0.5s ease-in;
        }

        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        /* Custom Scrollbar */
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

        /* Loading Spinner */
        .loading-spinner {
            display: none;
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            z-index: 9999;
        }

        .overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0,0,0,0.5);
            z-index: 9998;
        }
    </style>
</head>
<body>
    <!-- Loading Spinner -->
    <div class="loading-spinner">
        <div class="spinner-border text-primary" role="status">
            <span class="visually-hidden">Loading...</span>
        </div>
    </div>
    <div class="overlay"></div>

    <!-- Sidebar -->
    <nav class="sidebar">
        <div class="p-4">
            <h3 class="text-center mb-4">
                <i class="fas fa-camera-retro me-2"></i>
                Mawar Photography
            </h3>
            <ul class="nav flex-column">
                <li class="nav-item">
                    <a class="nav-link" href="dashboard.php">
                        <i class="fas fa-tachometer-alt me-2"></i>Dashboard
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link active" href="bookings.php">
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
            <h1 class="h3 mb-0">Booking Management</h1>
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

        <!-- Filters -->
        <div class="filter-section fade-in">
            <form method="GET" class="row g-3">
                <div class="col-md-3">
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-search"></i></span>
                        <input type="text" name="search" class="form-control" placeholder="Search customer, phone, location..." value="<?php echo htmlspecialchars($search); ?>">
                    </div>
                </div>
                <div class="col-md-2">
                    <select name="status" class="form-select">
                        <option value="">All Status</option>
                        <option value="pending" <?php echo $status_filter === 'pending' ? 'selected' : ''; ?>>Pending</option>
                        <option value="confirmed" <?php echo $status_filter === 'confirmed' ? 'selected' : ''; ?>>Confirmed</option>
                        <option value="completed" <?php echo $status_filter === 'completed' ? 'selected' : ''; ?>>Completed</option>
                        <option value="cancelled" <?php echo $status_filter === 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <select name="service_type" class="form-select">
                        <option value="">All Services</option>
                        <option value="wedding" <?php echo $service_filter === 'wedding' ? 'selected' : ''; ?>>Wedding</option>
                        <option value="engagement" <?php echo $service_filter === 'engagement' ? 'selected' : ''; ?>>Engagement</option>
                        <option value="akad" <?php echo $service_filter === 'akad' ? 'selected' : ''; ?>>Akad</option>
                        <option value="wisuda" <?php echo $service_filter === 'wisuda' ? 'selected' : ''; ?>>Wisuda</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <input type="date" name="date_from" class="form-control" value="<?php echo $date_from; ?>">
                </div>
                <div class="col-md-2">
                    <input type="date" name="date_to" class="form-control" value="<?php echo $date_to; ?>">
                </div>
                <div class="col-md-1">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-filter me-1"></i> Filter
                    </button>
                </div>
            </form>
        </div>

        <!-- Bookings Table -->
        <div class="card fade-in">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Customer</th>
                            <th>Service</th>
                            <th>Package</th>
                            <th>Date</th>
                            <th>Price</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($booking = $stmt->fetch(PDO::FETCH_ASSOC)): ?>
                            <tr>
                                <td>#<?php echo $booking['booking_id']; ?></td>
                                <td>
                                    <strong><?php echo htmlspecialchars($booking['customer_name']); ?></strong>
                                    <br>
                                    <small class="text-muted">
                                        <i class="fas fa-phone me-1"></i>
                                        <?php echo htmlspecialchars($booking['phone_number']); ?>
                                    </small>
                                </td>
                                <td>
                                    <i class="fas fa-camera me-1"></i>
                                    <?php echo ucfirst($booking['service_type']); ?>
                                </td>
                                <td>
                                    <i class="fas fa-box me-1"></i>
                                    <?php echo htmlspecialchars($booking['package_name']); ?>
                                </td>
                                <td>
                                    <i class="far fa-calendar me-1"></i>
                                    <?php echo date('d M Y H:i', strtotime($booking['booking_date'])); ?>
                                </td>
                                <td>
                                    <i class="fas fa-money-bill-wave me-1"></i>
                                    Rp <?php echo number_format($booking['total_price']); ?>
                                </td>
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
                                    <span class="badge bg-<?php echo $color; ?> status-badge">
                                        <i class="fas fa-circle me-1"></i>
                                        <?php echo ucfirst($booking['status']); ?>
                                    </span>
                                </td>
                                <td class="action-buttons">
                                    <div class="btn-group">
                                        <a href="booking-details.php?id=<?php echo $booking['booking_id']; ?>" 
                                           class="btn btn-info btn-sm" 
                                           data-bs-toggle="tooltip" 
                                           title="View Details">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <button class="btn btn-success btn-sm" 
                                                onclick="updateStatus(<?php echo $booking['booking_id']; ?>, 'confirmed')"
                                                data-bs-toggle="tooltip" 
                                                title="Confirm Booking">
                                            <i class="fas fa-check"></i>
                                        </button>
                                        <button class="btn btn-danger btn-sm" 
                                                onclick="updateStatus(<?php echo $booking['booking_id']; ?>, 'cancelled')"
                                                data-bs-toggle="tooltip" 
                                                title="Cancel Booking">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.32/dist/sweetalert2.all.min.js"></script>
    <script>
        // Initialize tooltips
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl)
        })

        // Update booking status with enhanced UI
        function updateStatus(bookingId, status) {
            Swal.fire({
                title: 'Update Booking Status',
                text: `Are you sure you want to mark this booking as ${status}?`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, update it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Show loading spinner
                    $('.loading-spinner, .overlay').show();

                    $.ajax({
                        url: 'update-booking-status.php',
                        type: 'POST',
                        data: {
                            booking_id: bookingId,
                            status: status
                        },
                        dataType: 'json',
                        success: function(response) {
                            $('.loading-spinner, .overlay').hide();
                            
                            if (response.success) {
                                Swal.fire({
                                    title: 'Success!',
                                    text: 'Booking status has been updated.',
                                    icon: 'success',
                                    timer: 1500,
                                    showConfirmButton: false
                                }).then(() => {
                                    location.reload();
                                });
                            } else {
                                Swal.fire({
                                    title: 'Error!',
                                    text: response.message || 'Failed to update booking status',
                                    icon: 'error'
                                });
                            }
                        },
                        error: function(xhr, status, error) {
                            $('.loading-spinner, .overlay').hide();
                            Swal.fire({
                                title: 'Error!',
                                text: 'An error occurred while processing your request.',
                                icon: 'error'
                            });
                        }
                    });
                }
            });
        }

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
        function updateStatus(bookingId, status) {
    if (status === 'confirmed') {
        // Ambil detail booking melalui AJAX
        $.ajax({
            url: 'get-booking-details.php',
            type: 'GET',
            data: { booking_id: bookingId },
            dataType: 'json',
            success: function(booking) {
                // Format nomor WhatsApp
                const phoneNumber = booking.phone_number.replace(/[^0-9]/g, '');
                const whatsappNumber = phoneNumber.startsWith('0') ? '62' + phoneNumber.slice(1) : phoneNumber;
                
                // Format pesan WhatsApp
                const message = encodeURIComponent(
                    `*Konfirmasi Booking Mawar Photography*\n\n` +
                    `Assalamu'alaikum Wr. Wb.\n\n` +
                    `Yth. Bapak/Ibu ${booking.customer_name},\n` +
                    `Terima kasih telah melakukan booking di Mawar Photography.\n\n` +
                    `Berikut detail pemesanan Anda:\n` +
                    `📋 ID Booking: #${booking.booking_id}\n` +
                    `📸 Jenis Layanan: ${booking.service_type}\n` +
                    `📦 Paket: ${booking.package_name}\n` +
                    `📅 Tanggal: ${booking.booking_date}\n` +
                    `💰 Total Biaya: Rp ${new Intl.NumberFormat('id-ID').format(booking.total_price)}\n\n` +
                    `Untuk mengamankan jadwal booking Anda, mohon melakukan pembayaran DP (minimal 50%) ke rekening berikut:\n\n` +
                    `🏦 Bank BCA\n` +
                    `💳 No. Rekening: 1234567890\n` +
                    `👤 Atas Nama: Mawar Photography\n\n` +
                    `Nominal DP: Rp ${new Intl.NumberFormat('id-ID').format(booking.total_price * 0.5)}\n\n` +
                    `Setelah melakukan pembayaran, mohon kirimkan bukti transfer melalui chat ini dengan format:\n` +
                    `- Bukti Transfer (Foto/Screenshot)\n` +
                    `- Nama Pemesan\n` +
                    `- ID Booking\n\n` +
                    `Jika ada pertanyaan lebih lanjut, silakan hubungi kami melalui chat ini.\n\n` +
                    `Terima kasih atas kepercayaan Anda memilih Mawar Photography! 🙏\n\n` +
                    `Wassalamu'alaikum Wr. Wb.`
                );

                // Konfirmasi pengiriman WhatsApp
                Swal.fire({
                    title: 'Kirim Konfirmasi Booking',
                    text: 'Anda akan diarahkan ke WhatsApp untuk mengirim pesan konfirmasi ke pelanggan. Lanjutkan?',
                    icon: 'info',
                    showCancelButton: true,
                    confirmButtonColor: '#25D366',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Ya, Kirim WA',
                    cancelButtonText: 'Batal'
                }).then((result) => {
                    if (result.isConfirmed) {
                        // Update status booking
                        $.ajax({
                            url: 'update-booking-status.php',
                            type: 'POST',
                            data: {
                                booking_id: bookingId,
                                status: status
                            },
                            dataType: 'json',
                            success: function(response) {
                                if (response.success) {
                                    // Buka WhatsApp dengan pesan
                                    window.open(`https://wa.me/${whatsappNumber}?text=${message}`, '_blank');
                                    
                                    // Tampilkan pesan sukses
                                    Swal.fire({
                                        title: 'Berhasil!',
                                        text: 'Status booking telah diperbarui dan pesan WhatsApp telah disiapkan.',
                                        icon: 'success',
                                        timer: 1500,
                                        showConfirmButton: false
                                    }).then(() => {
                                        location.reload();
                                    });
                                } else {
                                    Swal.fire({
                                        title: 'Gagal!',
                                        text: response.message || 'Gagal memperbarui status booking',
                                        icon: 'error'
                                    });
                                }
                            },
                            error: function() {
                                Swal.fire({
                                    title: 'Error!',
                                    text: 'Terjadi kesalahan saat memproses permintaan Anda.',
                                    icon: 'error'
                                });
                            }
                        });
                    }
                });
            },
            error: function() {
                Swal.fire({
                    title: 'Error!',
                    text: 'Gagal mengambil detail booking',
                    icon: 'error'
                });
            }
        });
    } else {
        // Kode untuk update status lainnya
        Swal.fire({
            title: 'Update Status Booking',
            text: `Anda yakin ingin mengubah status booking menjadi ${status}?`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Ya, Update!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                $('.loading-spinner, .overlay').show();

                $.ajax({
                    url: 'update-booking-status.php',
                    type: 'POST',
                    data: {
                        booking_id: bookingId,
                        status: status
                    },
                    dataType: 'json',
                    success: function(response) {
                        $('.loading-spinner, .overlay').hide();
                        
                        if (response.success) {
                            Swal.fire({
                                title: 'Berhasil!',
                                text: 'Status booking telah diperbarui.',
                                icon: 'success',
                                timer: 1500,
                                showConfirmButton: false
                            }).then(() => {
                                location.reload();
                            });
                        } else {
                            Swal.fire({
                                title: 'Gagal!',
                                text: response.message || 'Gagal memperbarui status booking',
                                icon: 'error'
                            });
                        }
                    },
                    error: function() {
                        $('.loading-spinner, .overlay').hide();
                        Swal.fire({
                            title: 'Error!',
                            text: 'Terjadi kesalahan saat memproses permintaan Anda.',
                            icon: 'error'
                        });
                    }
                });
            }
        });
    }
}
    </script>
</body>
</html>