<?php
require_once 'session.php';
require_once 'functions.php';

error_reporting(E_ALL);
ini_set('display_errors', 1);

define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'mawar_photography');

try {
    $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4";
    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ];
    
    $conn = new PDO($dsn, DB_USER, DB_PASS, $options);
} catch(PDOException $e) {
    error_log("Connection failed: " . $e->getMessage());
    die("Sorry, there was a problem connecting to our database. Please try again later.");
}

// Create necessary tables
try {
    // Bookings table
    $conn->exec("
        CREATE TABLE IF NOT EXISTS bookings (
            id INT AUTO_INCREMENT PRIMARY KEY,
            service_type VARCHAR(50) NOT NULL,
            customer_name VARCHAR(100) NOT NULL,
            package_name VARCHAR(100) NOT NULL,
            booking_date DATETIME NOT NULL,
            phone_number VARCHAR(20) NOT NULL,
            location TEXT NOT NULL,
            extra_hours INT NOT NULL DEFAULT 0,
            total_price DECIMAL(10,2) NOT NULL,
            status ENUM('pending', 'confirmed', 'cancelled') DEFAULT 'pending',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )
    ");

    // Packages table
    $conn->exec("
        CREATE TABLE IF NOT EXISTS packages (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(100) NOT NULL,
            price DECIMAL(10,2) NOT NULL,
            description TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )
    ");

    // Create admins table if not exists
    $conn->exec("
        CREATE TABLE IF NOT EXISTS admins (
            admin_id INT AUTO_INCREMENT PRIMARY KEY,
            username VARCHAR(50) NOT NULL UNIQUE,
            password VARCHAR(255) NOT NULL,
            full_name VARCHAR(100) NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )
    ");

    // Insert default packages if not exists
    $stmt = $conn->query("SELECT COUNT(*) FROM packages");
    if ($stmt->fetchColumn() == 0) {
        $conn->exec("
            INSERT INTO packages (name, price, description) VALUES 
            ('Special Package', 1700000, 'Basic photography package'),
            ('Premium Package', 2500000, 'Premium photography package'),
            ('Ultimate Package', 3500000, 'Ultimate photography package')
        ");
    }

// Add default admin if none exists
$stmt = $conn->query("SELECT COUNT(*) FROM admins");
if ($stmt->fetchColumn() == 0) {
    $password_hash = password_hash('admin123', PASSWORD_DEFAULT);
    $conn->exec("
        INSERT INTO admins (username, password, full_name) 
        VALUES ('admin', '$password_hash', 'Administrator')
    ");
}

} catch(PDOException $e) {
    error_log("Table creation failed: " . $e->getMessage());
}