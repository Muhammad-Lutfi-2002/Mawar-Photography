<?php
require_once 'config.php';

if (isset($_GET['booking_id'])) {
    $booking_id = $_GET['booking_id'];
    
    try {
        $stmt = $conn->prepare("SELECT * FROM bookings WHERE booking_id = ?");
        $stmt->execute([$booking_id]);
        $booking = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($booking) {
            // Format tanggal ke format Indonesia
            $booking['booking_date'] = date('d/m/Y H:i', strtotime($booking['booking_date']));
            
            header('Content-Type: application/json');
            echo json_encode($booking);
        } else {
            http_response_code(404);
            echo json_encode(['error' => 'Data booking tidak ditemukan']);
        }
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Terjadi kesalahan pada server']);
    }
} else {
    http_response_code(400);
    echo json_encode(['error' => 'ID Booking tidak diberikan']);
}