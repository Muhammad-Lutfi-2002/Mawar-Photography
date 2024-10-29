<?php
require_once 'config.php';

// Check if user is logged in
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit();
}

// Check if required parameters are present
if (!isset($_POST['booking_id']) || !isset($_POST['status'])) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Missing required parameters']);
    exit();
}

$booking_id = $_POST['booking_id'];
$status = $_POST['status'];

// Validate status
$allowed_statuses = ['pending', 'confirmed', 'completed', 'cancelled'];
if (!in_array($status, $allowed_statuses)) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Invalid status']);
    exit();
}

try {
    // Prepare the update query
    $query = "UPDATE bookings SET status = :status WHERE booking_id = :booking_id";
    $stmt = $conn->prepare($query);
    
    // Execute the query with parameters
    $result = $stmt->execute([
        ':status' => $status,
        ':booking_id' => $booking_id
    ]);

    if ($result) {
        // Log the status change if needed
        $log_query = "INSERT INTO booking_logs (booking_id, status, changed_by, changed_at) 
                     VALUES (:booking_id, :status, :admin_id, NOW())";
        try {
            $log_stmt = $conn->prepare($log_query);
            $log_stmt->execute([
                ':booking_id' => $booking_id,
                ':status' => $status,
                ':admin_id' => $_SESSION['admin_id'] // Assuming you have admin_id in session
            ]);
        } catch (PDOException $e) {
            // Log error silently - don't affect main operation
            error_log("Failed to log booking status change: " . $e->getMessage());
        }

        header('Content-Type: application/json');
        echo json_encode([
            'success' => true,
            'message' => 'Booking status updated successfully',
            'new_status' => $status
        ]);
    } else {
        throw new Exception('Failed to update booking status');
    }
} catch (Exception $e) {
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'message' => 'Error updating booking status: ' . $e->getMessage()
    ]);
}