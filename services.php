<?php
require_once 'config.php';

function getPackages() {
    global $conn;
    try {
        $stmt = $conn->query("SELECT * FROM packages ORDER BY price ASC");
        return $stmt->fetchAll();
    } catch(PDOException $e) {
        error_log("Error fetching packages: " . $e->getMessage());
        return [];
    }
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Validate required fields
        $required_fields = ['service_type', 'nama', 'package', 'datetime', 'phone', 'lokasi', 'extra_hours'];
        $data = [];
        
        foreach ($required_fields as $field) {
            if (!isset($_POST[$field]) || empty($_POST[$field])) {
                throw new Exception("Field $field is required");
            }
            $data[$field] = sanitize_input($_POST[$field]);
        }

        // Validate phone number
        if (!preg_match('/^(\+?62|0)[1-9][0-9]{7,11}$/', $data['phone'])) {
            throw new Exception("Invalid phone number format");
        }

        // Validate and format datetime
        try {
            $datetime = new DateTime($data['datetime']);
            $now = new DateTime();
            $minBookingTime = (new DateTime())->modify('+24 hours');

            if ($datetime < $minBookingTime) {
                throw new Exception("Booking must be at least 24 hours in advance");
            }
            $data['datetime'] = $datetime->format('Y-m-d H:i:s');
        } catch (Exception $e) {
            throw new Exception("Invalid date format or date is too soon");
        }

        // Validate extra hours
        $data['extra_hours'] = intval($data['extra_hours']);
        if ($data['extra_hours'] < 0) {
            throw new Exception("Extra hours cannot be negative");
        }

        // Calculate total price
        $basePrice = 1700000; // Base price for Special Package
        $extraHourRate = 50000;
        $totalPrice = $basePrice + ($data['extra_hours'] * $extraHourRate);

        // Insert booking
        $stmt = $conn->prepare("
            INSERT INTO bookings (
                service_type,
                customer_name,
                package_name,
                booking_date,
                phone_number,
                location,
                extra_hours,
                total_price
            ) VALUES (
                :service_type,
                :nama,
                :package,
                :datetime,
                :phone,
                :lokasi,
                :extra_hours,
                :total_price
            )
        ");

        $result = $stmt->execute([
            'service_type' => $data['service_type'],
            'nama' => $data['nama'],
            'package' => $data['package'],
            'datetime' => $data['datetime'],
            'phone' => $data['phone'],
            'lokasi' => $data['lokasi'],
            'extra_hours' => $data['extra_hours'],
            'total_price' => $totalPrice
        ]);

        if ($result) {
            $response = [
                'success' => true,
                'message' => 'Booking berhasil dibuat!',
                'data' => [
                    'booking_id' => $conn->lastInsertId(),
                    'total_price' => $totalPrice
                ]
            ];
        } else {
            throw new Exception("Failed to create booking");
        }

    } catch (Exception $e) {
        $response = [
            'success' => false,
            'message' => $e->getMessage()
        ];
        http_response_code(400);
    }

    header('Content-Type: application/json');
    echo json_encode($response);
    exit;
}

$packages = getPackages();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mawar Photography - Services</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;700&family=Montserrat:wght@300;400;700&display=swap');

        :root {
            --primary-color: #333;
            --secondary-color: #f4f4f4;
            --accent-color: #d4af37;
            --text-color: #333;
            --hover-color: #b8960c;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Montserrat', sans-serif;
            line-height: 1.6;
            color: var(--text-color);
            background-color: var(--secondary-color);
            padding-top: 80px;
        }

        header {
            position: fixed;
            top: 0;
            width: 100%;
            background-color: white;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            z-index: 1000;
        }

        nav {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1rem 5%;
        }

        .logo {
            font-family: 'Playfair Display', serif;
            font-size: 1.5rem;
            color: var(--primary-color);
        }

        nav ul {
            display: flex;
            list-style: none;
        }

        nav ul li {
            margin-left: 2rem;
        }

        nav ul li a {
            color: var(--primary-color);
            text-decoration: none;
            font-weight: 300;
            text-transform: uppercase;
            letter-spacing: 1px;
            transition: color 0.3s;
        }

        nav ul li a:hover {
            color: var(--accent-color);
        }

        .services-container {
            max-width: 1200px;
            margin: 2rem auto;
            padding: 0 20px;
        }

        .services-title {
            text-align: center;
            margin-bottom: 3rem;
            padding: 2rem 0;
        }

        .services-title h1 {
            font-family: 'Playfair Display', serif;
            font-size: 2.5rem;
            color: var(--primary-color);
            margin-bottom: 1rem;
        }

        .category-section {
            margin-bottom: 4rem;
        }

        .category-title {
            text-align: center;
            margin-bottom: 2rem;
            padding: 1rem;
            background: var(--accent-color);
            color: white;
            border-radius: 8px;
            font-family: 'Playfair Display', serif;
        }

        .packages-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 2rem;
            margin-bottom: 2rem;
        }

        .package-card {
            background: white;
            padding: 2rem;
            border-radius: 12px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            transition: transform 0.3s, box-shadow 0.3s;
        }

        .package-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 6px 12px rgba(0,0,0,0.15);
        }

        .package-card h3 {
            color: var(--accent-color);
            margin-bottom: 1.5rem;
            font-family: 'Playfair Display', serif;
            font-size: 1.5rem;
            border-bottom: 2px solid var(--accent-color);
            padding-bottom: 0.5rem;
        }

        .package-card ul {
            list-style: none;
            margin-bottom: 1.5rem;
        }

        .package-card ul li {
            margin-bottom: 0.8rem;
            padding-left: 1.5rem;
            position: relative;
        }

        .package-card ul li::before {
            content: "✓";
            color: var(--accent-color);
            position: absolute;
            left: 0;
            font-weight: bold;
        }

        .package-price {
            font-size: 1.5rem;
            font-weight: bold;
            color: var(--accent-color);
            margin-top: 1.5rem;
            text-align: center;
            padding: 1rem;
            border-top: 1px solid #eee;
        }

        .best-deal {
            position: relative;
            overflow: hidden;
        }

        .best-deal::after {
            content: "Best Deal!";
            position: absolute;
            top: 20px;
            right: -35px;
            background: var(--accent-color);
            color: white;
            padding: 5px 40px;
            transform: rotate(45deg);
            font-size: 0.8rem;
        }

        /* Form Styles */
        .booking-form {
            background: white;
            padding: 3rem;
            border-radius: 12px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            margin-top: 3rem;
        }

        .form-title {
            text-align: center;
            margin-bottom: 2rem;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        label {
            display: block;
            margin-bottom: 0.8rem;
            font-weight: 500;
            color: var(--primary-color);
        }

        input, select, textarea {
            width: 100%;
            padding: 1rem;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-family: inherit;
            font-size: 1rem;
            transition: border-color 0.3s;
        }

        input:focus, select:focus, textarea:focus {
            outline: none;
            border-color: var(--accent-color);
        }

        .submit-btn {
            background-color: var(--accent-color);
            color: white;
            padding: 1rem 2rem;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 1.1rem;
            text-transform: uppercase;
            letter-spacing: 1px;
            transition: background-color 0.3s;
            width: 100%;
            margin-top: 1rem;
        }

        .submit-btn:hover {
            background-color: var(--hover-color);
        }

        .total-price {
            background: #f8f8f8;
            padding: 1rem;
            border-radius: 6px;
            margin-top: 1rem;
            text-align: right;
            font-weight: bold;
        }

        .note {
            font-style: italic;
            color: #666;
            margin-top: 1rem;
            font-size: 0.9rem;
            text-align: center;
        }

        @media (max-width: 768px) {
            nav ul {
                display: none;
            }
            
            .booking-form {
                padding: 1.5rem;
            }
        }
    </style>
</head>
<body>
    <header>
        <nav>
            <div class="logo">Mawar Photography</div>
            <ul>
                <li><a href="Home_Page.php">Home</a></li>
                <li><a href="Home_Page.php#gallery">Gallery</a></li>
                <li><a href="#Services">Services</a></li>
                <li><a href="Home_Page.php#about">About</a></li>
                <li><a href="Home_Page.php#contact">Contact</a></li>
                <li><a href="Login_Page.php" class="login-btn-nav">Login</a></li>
            </ul>
        </nav>
    </header>

    <div class="services-container">
        <!-- Wedding Packages -->
        <div class="category-section">
            <div class="category-title">
                <h2>Wedding Package Pricelist 2024</h2>
            </div>
            <div class="packages-grid">
                <div class="package-card">
                    <h3>Magnetic I</h3>
                    <ul>
                        <li>1 Album Magnetik (10 sheet)</li>
                        <li>120 Softfile photo (G-drive)</li>
                        <li>7 hours time work</li>
                    </ul>
                    <div class="package-price">IDR 1.4jt</div>
                </div>

                <div class="package-card">
                    <h3>File Only</h3>
                    <ul>
                        <li>All Softfile photo (G-drive)</li>
                        <li>7 hours time work</li>
                    </ul>
                    <div class="package-price">IDR 1jt</div>
                </div>

                <div class="package-card">
                    <h3>Magnetic II</h3>
                    <ul>
                        <li>2 Album Magnetik (10 sheet)</li>
                        <li>All Softfile photo (G-drive)</li>
                        <li>7 hours time work</li>
                    </ul>
                    <div class="package-price">IDR 1.8jt</div>
                </div>

                <div class="package-card">
                    <h3>Magazine I</h3>
                    <ul>
                        <li>1 Album Magazine (10 sheet)</li>
                        <li>All Softfile photo (Flashdisk)</li>
                        <li>7 hours time work</li>
                    </ul>
                    <div class="package-price">IDR 2jt</div>
                </div>

                <div class="package-card">
                    <h3>Cinematic Video</h3>
                    <ul>
                        <li>1 & 5 menit Video</li>
                        <li>Delivered via Flashdisk</li>
                    </ul>
                    <div class="package-price">IDR 1.5jt</div>
                </div>

                <div class="package-card">
                    <h3>Magazine II</h3>
                    <ul>
                        <li>1 Album Magazine (15 sheet)</li>
                        <li>Cetak 40 foto 4R</li>
                        <li>All Softfile photo (Flashdisk)</li>
                        <li>1 Cetak 12R + Frame</li>
                        <li>7 hours time work</li>
                        <li>Include Siraman</li>
                    </ul>
                    <div class="package-price">IDR 3.3jt</div>
                </div>

                <div class="package-card best-deal">
                    <h3>Cinematic & Magazine</h3>
                    <ul>
                        <li>1 Album Magazine (15 sheet)</li>
                        <li>Cetak 40 foto 4R</li>
                        <li>All Softfile photo (Flashdisk)</li>
                        <li>1 Cetak 12R + Frame</li>
                        <li>1 & 5 menit Video</li>
                        <li>7 hours time work</li>
                        <li>Include Siraman</li>
                    </ul>
                    <div class="package-price">
                        <s>IDR 3.5jt</s><br>
                        IDR 3.3jt
                    </div>
                </div>
            </div>
        </div>

        <!-- Akad Only Packages -->
        <div class="category-section">
            <div class="category-title">
                <h2>Akad Only Package Pricelist 2024</h2>
            </div>
            <div class="packages-grid">
                <div class="package-card">
                    <h3>Package I</h3>
                    <ul>
                        <li>1 album magnetik (10 sheet)</li>
                        <li>80 Softfile photo (G-drive)</li>
                        <li>4 hours time work</li>
                    </ul>
                    <div class="package-price">IDR 850K</div>
                </div>

                <div class="package-card">
                    <h3>Package II</h3>
                    <ul>
                        <li>Soft File ONLY</li>
                        <li>100 Softfile photo (G-drive)</li>
                        <li>4 hours time work</li>
                    </ul>
                    <div class="package-price">IDR 600K</div>
                </div>

                <div class="package-card">
                    <h3>Special Package</h3>
                    <ul>
                        <li>1 minute Cinematic</li>
                        <li>All Softfile Photo (G-drive)</li>
                        <li>5 hours time work</li>
                    </ul>
                    <div class="package-price">IDR 1.7jt</div>
                </div>
            </div>
        </div>

        <!-- Wisuda Packages -->
        <div class="category-section">
            <div class="category-title">
                <h2>Wisuda Photoshoot 2024</h2>
            </div>
            <div class="packages-grid">
                <div class="package-card">
                    <h3>Personal Package</h3>
                    <ul>
                        <li>SoftFile Only</li>
                        <li>30 Foto edit</li>
                        <li>1 hour work</li>
                    </ul>
                    <div class="package-price">IDR 200K</div>
                </div>

                <div class="package-card">
                    <h3>Family Package</h3>
                    <ul>
                        <li>Family, couple, personal</li>
                        <li>SoftFile Only</li>
                        <li>35 Foto edit</li>
                        <li>1.5 hour work</li>
                    </ul>
                    <div class="package-price">IDR 350K</div>
                </div>

                <div class="package-card">
                    <h3>Group Package</h3>
                    <ul>
                        <li>Group max 3 & personal</li>
                        <li>SoftFile Only</li>
                        <li>35 Foto edit </li>
                        <li>2 hour work</li>
                    </ul>
                    <div class="package-price">IDR 400K</div>
                </div>

                <div class="package-card">
                    <h3>Special Package</h3>
                    <ul>
                        <li>Photo & Cinematic</li>
                        <li>SoftFile 35 foto</li>
                        <li>1 menit Cinematic</li>
                        <li>3 hour work</li>
                    </ul>
                    <div class="package-price">IDR 700K</div>
                </div>
            </div>
        </div>

        <!-- Engagement Packages -->
        <div class="category-section">
            <div class="category-title">
                <h2>Engagement Package Pricelist 2024</h2>
            </div>
            <div class="packages-grid">
                <div class="package-card">
                    <h3>Package I</h3>
                    <ul>
                        <li>1 Album Magnetik (Isi 40 Foto)</li>
                        <li>80 Softfile photo (G-drive)</li>
                        <li>4 hours time work</li>
                    </ul>
                    <div class="package-price">IDR 800K</div>
                </div>

                <div class="package-card">
                    <h3>Package II</h3>
                    <ul>
                        <li>Soft File ONLY</li>
                        <li>85 Softfile photo (G-drive)</li>
                        <li>4 hours time work</li>
                    </ul>
                    <div class="package-price">IDR 600K</div>
                </div>

                <div class="package-card">
                    <h3>Special Package</h3>
                    <ul>
                        <li>Foto & Cinematic</li>
                        <li>1 minute Cinematic</li>
                        <li>All Softfile photo (G-drive)</li>
                        <li>5 hours time work</li>
                    </ul>
                    <div class="package-price">IDR 1.3jt</div>
                </div>
            </div>
        </div>

        <!-- Booking Form -->
        <form class="booking-form" method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" id="bookingForm">
            <div class="form-title">
                <h2>Book Your Session</h2>
            </div>

            <div class="form-group">
                <label for="service_type">Type of Service</label>
                <select name="service_type" id="service_type" required>
                    <option value="">Select a service</option>
                    <option value="wedding">Wedding Photography</option>
                    <option value="akad">Akad Only Photography</option>
                    <option value="engagement">Engagement Photography</option>
                    <option value="wisuda">Graduation Photography</option>
                </select>
            </div>

            <div class="form-group">
                <label for="nama">Full Name</label>
                <input type="text" name="nama" id="nama" required>
            </div>

            <div class="form-group">
                <label for="package">Package</label>
                <select name="package" id="package" required>
                    <option value="">Select a package</option>
                    <!-- Package options will be populated by JavaScript -->
                </select>
            </div>

            <div class="form-group">
                <label for="datetime">Date and Time</label>
                <input type="datetime-local" name="datetime" id="datetime" required>
            </div>

            <div class="form-group">
                <label for="phone">Phone Number</label>
                <input type="tel" name="phone" id="phone" required pattern="[0-9]+" title="Please enter numbers only">
            </div>

            <div class="form-group">
                <label for="lokasi">Location</label>
                <textarea name="lokasi" id="lokasi" rows="3" required></textarea>
            </div>

            <div class="form-group">
                <label for="extra_hours">Additional Hours</label>
                <input type="number" name="extra_hours" id="extra_hours" min="0" value="0">
            </div>

            <div class="total-price" id="totalPrice">
                Total Price: IDR 0
            </div>

            <p class="note">* Additional hours are charged at IDR 50K per hour</p>

            <button type="submit" class="submit-btn">Book Now</button>
        </form>
    </div>

    <script>
       // Toast notification system
const Toast = {
    init() {
        this.hideTimeout = null;
        this.container = document.createElement('div');
        this.container.className = 'toast-container';
        document.body.appendChild(this.container);

        // Add styles for toast
        const style = document.createElement('style');
        style.textContent = `
            .toast-container {
                position: fixed;
                top: 20px;
                right: 20px;
                z-index: 9999;
            }
            .toast {
                min-width: 300px;
                margin-bottom: 10px;
                padding: 15px;
                border-radius: 8px;
                color: white;
                font-size: 14px;
                font-weight: 500;
                display: flex;
                align-items: center;
                gap: 10px;
                animation: slideIn 0.3s ease-in-out;
                box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
                cursor: pointer;
            }
            .toast-success {
                background-color: #4caf50;
            }
            .toast-error {
                background-color: #f44336;
            }
            .toast-warning {
                background-color: #ff9800;
            }
            .toast-info {
                background-color: #2196f3;
            }
            .toast-progress {
                position: absolute;
                bottom: 0;
                left: 0;
                height: 3px;
                width: 100%;
                background-color: rgba(255, 255, 255, 0.3);
            }
            .toast-progress-bar {
                height: 100%;
                background-color: rgba(255, 255, 255, 0.6);
                transition: width linear;
            }
            @keyframes slideIn {
                from {
                    transform: translateX(100%);
                    opacity: 0;
                }
                to {
                    transform: translateX(0);
                    opacity: 1;
                }
            }
            @keyframes slideOut {
                from {
                    transform: translateX(0);
                    opacity: 1;
                }
                to {
                    transform: translateX(100%);
                    opacity: 0;
                }
            }
            .toast-icon {
                font-size: 20px;
            }
        `;
        document.head.appendChild(style);
    },

    show(message, type = 'success', duration = 5000) {
        const toast = document.createElement('div');
        toast.className = `toast toast-${type}`;
        
        // Create icon based on type
        const icon = document.createElement('span');
        icon.className = 'toast-icon';
        switch (type) {
            case 'success':
                icon.innerHTML = '✓';
                break;
            case 'error':
                icon.innerHTML = '✕';
                break;
            case 'warning':
                icon.innerHTML = '⚠';
                break;
            case 'info':
                icon.innerHTML = 'ℹ';
                break;
        }
        
        // Create message element
        const text = document.createElement('span');
        text.textContent = message;
        
        // Create progress bar
        const progress = document.createElement('div');
        progress.className = 'toast-progress';
        const progressBar = document.createElement('div');
        progressBar.className = 'toast-progress-bar';
        progress.appendChild(progressBar);
        
        // Append elements
        toast.appendChild(icon);
        toast.appendChild(text);
        toast.appendChild(progress);
        this.container.appendChild(toast);
        
        // Animate progress bar
        progressBar.style.width = '100%';
        progressBar.style.transition = `width ${duration}ms linear`;
        setTimeout(() => progressBar.style.width = '0%', 50);
        
        // Remove toast after duration
        setTimeout(() => {
            toast.style.animation = 'slideOut 0.3s ease-in-out forwards';
            setTimeout(() => toast.remove(), 300);
        }, duration);

        // Click to dismiss
        toast.onclick = () => {
            toast.style.animation = 'slideOut 0.3s ease-in-out forwards';
            setTimeout(() => toast.remove(), 300);
        };
    }
};

// Package data structure
const packageData = {
    wedding: {
        magnetic_1: { name: "Magnetic I", price: 1400000, hours: 7 },
        file_only: { name: "File Only", price: 1000000, hours: 7 },
        magnetic_2: { name: "Magnetic II", price: 1800000, hours: 7 },
        magazine_1: { name: "Magazine I", price: 2000000, hours: 7 },
        cinematic: { name: "Cinematic Video", price: 1500000, hours: 7 },
        magazine_2: { name: "Magazine II", price: 3300000, hours: 7 },
        cinematic_magazine: { name: "Cinematic & Magazine", price: 3300000, hours: 7 }
    },
    akad: {
        package_1: { name: "Package I", price: 850000, hours: 4 },
        package_2: { name: "Package II", price: 600000, hours: 4 },
        special: { name: "Special Package", price: 1700000, hours: 5 }
    },
    wisuda: {
        personal: { name: "Personal Package", price: 200000, hours: 1 },
        family: { name: "Family Package", price: 350000, hours: 1.5 },
        group: { name: "Group Package", price: 400000, hours: 2 },
        special: { name: "Special Package", price: 700000, hours: 3 }
    },
    engagement: {
        package_1: { name: "Package I", price: 800000, hours: 4 },
        package_2: { name: "Package II", price: 600000, hours: 4 },
        special: { name: "Special Package", price: 1300000, hours: 5 }
    }
};

// Format price to Indonesian Rupiah format
function formatPrice(price) {
    if (price >= 1000000) {
        return `IDR ${(price/1000000).toFixed(1)}jt`;
    }
    return `IDR ${price.toLocaleString('id-ID')}`;
}

// Populate package options based on service type
function populatePackages(serviceType) {
    const packageSelect = document.getElementById('package');
    packageSelect.innerHTML = '<option value="">Select a package</option>';
    
    if (serviceType && packageData[serviceType]) {
        for (const [key, package] of Object.entries(packageData[serviceType])) {
            const option = document.createElement('option');
            option.value = key;
            option.dataset.price = package.price;
            option.dataset.hours = package.hours;
            option.textContent = `${package.name} - ${formatPrice(package.price)}`;
            packageSelect.appendChild(option);
        }
    }
}

// Calculate total price including extra hours
function calculateTotalPrice() {
    const packageSelect = document.getElementById('package');
    const extraHours = parseInt(document.getElementById('extra_hours').value) || 0;
    const selectedOption = packageSelect.options[packageSelect.selectedIndex];
    
    if (!selectedOption.value) {
        document.getElementById('totalPrice').textContent = 'Total Price: IDR 0';
        return;
    }

    const basePrice = parseInt(selectedOption.dataset.price);
    const extraPrice = extraHours * 50000; // 50K per hour
    const totalPrice = basePrice + extraPrice;

    document.getElementById('totalPrice').innerHTML = `
        Base Price: ${formatPrice(basePrice)}<br>
        Extra Hours: ${extraHours} × IDR 50K = ${formatPrice(extraPrice)}<br>
        <strong>Total Price: ${formatPrice(totalPrice)}</strong>
    `;
}

// Validate datetime
function validateDateTime() {
    const dateTimeInput = document.getElementById('datetime');
    const selectedDate = new Date(dateTimeInput.value);
    const now = new Date();
    const minDate = new Date(now.getTime() + (24 * 60 * 60 * 1000));
    
    if (selectedDate < minDate) {
        Toast.show('Please select a date and time at least 24 hours in advance', 'warning');
        dateTimeInput.value = '';
        return false;
    }
    
    return true;
}

// Validate phone number
function validatePhone() {
    const phoneInput = document.getElementById('phone');
    const phoneNumber = phoneInput.value.trim();
    const cleanNumber = phoneNumber.replace(/\D/g, '');
    
    if (!/^(\+?62|0)[1-9][0-9]{7,11}$/.test(cleanNumber)) {
        Toast.show('Please enter a valid Indonesian phone number', 'error');
        return false;
    }
    
    phoneInput.value = cleanNumber.replace(/^0/, '62');
    return true;
}

// Initialize when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    // Initialize Toast notification system
    Toast.init();
    
    // Add event listeners
    document.getElementById('service_type').addEventListener('change', function() {
        populatePackages(this.value);
        calculateTotalPrice();
    });

    document.getElementById('package').addEventListener('change', calculateTotalPrice);
    document.getElementById('extra_hours').addEventListener('input', calculateTotalPrice);
    document.getElementById('datetime').addEventListener('change', validateDateTime);

    // Form submission handler
    document.getElementById('bookingForm').addEventListener('submit', function(e) {
        e.preventDefault();
        
        if (!validateDateTime() || !validatePhone()) {
            return;
        }
        
        // Validate required fields
        const requiredFields = ['service_type', 'nama', 'package', 'datetime', 'phone', 'lokasi'];
        let isValid = true;
        
        requiredFields.forEach(field => {
            const element = document.getElementById(field);
            if (!element.value.trim()) {
                Toast.show(`Please fill in the ${field.replace('_', ' ')} field`, 'error');
                isValid = false;
            }
        });
        
        if (!isValid) return;
        
        // Show loading state
        Toast.show('Processing your booking...', 'info');
        
        // Submit form using fetch
        const formData = new FormData(this);
        
        fetch('services.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                Toast.show('Booking successful! We will contact you soon.', 'success');
                this.reset();
                calculateTotalPrice();
                window.scrollTo({ top: 0, behavior: 'smooth' });
            } else {
                Toast.show(data.message || 'An error occurred during booking', 'error');
            }
        })
        .catch(error => {
            Toast.show('An error occurred. Please try again later.', 'error');
            console.error('Error:', error);
        });
    });
});

    </script>
</body>
</html>