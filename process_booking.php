<?php
require_once 'config.php';

class BookingProcessor {
    private $conn;
    private $response;

    public function __construct($connection) {
        $this->conn = $connection();
        $this->response = [
            'success' => false,
            'message' => '',
            'data' => []
        ];
    }

    public function processBooking($postData) {
        try {
            // Validate request method
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                throw new Exception('Invalid request method');
            }

            // Start transaction
            $this->conn->beginTransaction();

            // Sanitize and validate input
            $cleanData = $this->sanitizeAndValidate($postData);
            if (!$cleanData['valid']) {
                throw new Exception($cleanData['message']);
            }

            // Get or create user
            $userId = $this->getOrCreateUser($cleanData['data']);

            // Get package details and validate
            $packageDetails = $this->getPackageDetails($cleanData['data']['package']);
            if (!$packageDetails) {
                throw new Exception('Invalid package selected');
            }

            // Calculate total price
            $totalPrice = $this->calculateTotalPrice(
                $packageDetails['base_price'],
                $cleanData['data']['extra_hours']
            );

            // Create booking
            $bookingId = $this->createBooking([
                'user_id' => $userId,
                'package_id' => $packageDetails['package_id'],
                'booking_date' => $cleanData['data']['datetime'],
                'location' => $cleanData['data']['lokasi'],
                'extra_hours' => $cleanData['data']['extra_hours'],
                'total_price' => $totalPrice
            ]);

            // Commit transaction
            $this->conn->commit();

            $this->response['success'] = true;
            $this->response['message'] = 'Booking successfully created';
            $this->response['data'] = [
                'booking_id' => $bookingId,
                'total_price' => $totalPrice
            ];

        } catch (Exception $e) {
            $this->conn->rollBack();
            $this->response['message'] = $e->getMessage();
            error_log("Booking Error: " . $e->getMessage());
        }

        return $this->response;
    }

    private function sanitizeAndValidate($data) {
        $required_fields = ['service_type', 'nama', 'package', 'datetime', 'phone', 'lokasi', 'extra_hours'];
        $clean_data = [];
        $errors = [];

        // Check required fields
        foreach ($required_fields as $field) {
            if (!isset($data[$field]) || empty($data[$field])) {
                $errors[] = ucfirst($field) . " is required";
                continue;
            }

            $clean_data[$field] = sanitize_input($data[$field]);
        }

        if (!empty($errors)) {
            return ['valid' => false, 'message' => implode(", ", $errors)];
        }

        // Validate name
        if (strlen($clean_data['nama']) > 100) {
            $errors[] = "Name is too long";
        }

        // Validate phone number
        if (!preg_match('/^(\+?62|0)[1-9][0-9]{7,11}$/', $clean_data['phone'])) {
            $errors[] = "Invalid phone number format";
        }

        // Validate booking date
        if (!validateDate($clean_data['datetime'])) {
            try {
                $bookingDate = new DateTime($clean_data['datetime']);
                $clean_data['datetime'] = $bookingDate->format('Y-m-d H:i:s');
            } catch (Exception $e) {
                $errors[] = "Invalid date format. Please use YYYY-MM-DD HH:MM:SS format";
            }
        }

        if (empty($errors)) {
            $bookingDate = new DateTime($clean_data['datetime']);
            $minDate = new DateTime('+24 hours');
            if ($bookingDate < $minDate) {
                $errors[] = "Booking must be at least 24 hours in advance";
            }
        }

        // Validate extra hours
        if (!is_numeric($clean_data['extra_hours']) || $clean_data['extra_hours'] < 0) {
            $errors[] = "Invalid extra hours value";
        }

        // Validate location
        if (strlen($clean_data['lokasi']) > 255) {
            $errors[] = "Location description is too long";
        }

        if (!empty($errors)) {
            return ['valid' => false, 'message' => implode(", ", $errors)];
        }

        return ['valid' => true, 'data' => $clean_data];
    }

    // ... (rest of the methods remain the same)
}

// Process the request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');
    
    $processor = new BookingProcessor($conn);
    $result = $processor->processBooking($_POST);
    
    http_response_code($result['success'] ? 200 : 400);
    echo json_encode($result);
    exit;
}