<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mawar Photography - Elegant Moments Captured</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;700&family=Montserrat:wght@300;400;700&display=swap');

        :root {
            --primary-color: #333;
            --secondary-color: #f4f4f4;
            --accent-color: #d4af37;
            --text-color: #333;
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
        }

        header {
            position: fixed;
            width: 100%;
            z-index: 1000;
            padding: 1rem 5%;
            background-color: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(10px);
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }

        nav {
            display: flex;
            justify-content: space-between;
            align-items: center;
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
            transition: color 0.3s ease;
            font-size: 0.9rem;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        nav ul li a:hover {
            color: var(--accent-color);
        }

        /* Slideshow Styles */
        .hero {
            height: 100vh;
            position: relative;
            overflow: hidden;
        }

        .slide {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            opacity: 0;
            transform: scale(1.1);
            transition: all 0.8s ease;
            background-size: cover;
            background-position: center;
        }

        .slide.active {
            opacity: 1;
            transform: scale(1);
        }

        .slide::after {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.3);
        }

        .slide-nav {
            position: absolute;
            bottom: 30px;
            left: 50%;
            transform: translateX(-50%);
            z-index: 10;
            display: flex;
            gap: 10px;
        }

        .slide-nav-btn {
            width: 10px;
            height: 10px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.5);
            cursor: pointer;
            border: none;
            transition: all 0.3s ease;
        }

        .slide-nav-btn.active {
            background: #fff;
            transform: scale(1.2);
        }

        .slide-arrows {
            position: absolute;
            top: 50%;
            width: 100%;
            transform: translateY(-50%);
            z-index: 10;
            display: flex;
            justify-content: space-between;
            padding: 0 30px;
        }

        .slide-arrow {
            font-size: 2rem;
            color: white;
            background: none;
            border: none;
            cursor: pointer;
            opacity: 0.7;
            transition: opacity 0.3s ease;
        }

        .slide-arrow:hover {
            opacity: 1;
        }

        .hero-content {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            text-align: center;
            color: #fff;
            z-index: 2;
            width: 90%;
            max-width: 800px;
        }

        .hero h1 {
            font-family: 'Playfair Display', serif;
            font-size: 3.5rem;
            margin-bottom: 1rem;
            opacity: 0;
            transform: translateY(20px);
            animation: fadeInUp 0.8s forwards;
        }

        .hero p {
            font-size: 1.2rem;
            margin-bottom: 2rem;
            opacity: 0;
            transform: translateY(20px);
            animation: fadeInUp 0.8s forwards 0.3s;
        }

        @keyframes fadeInUp {
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .btn {
            display: inline-block;
            background-color: var(--accent-color);
            color: #fff;
            padding: 1rem 2.5rem;
            text-decoration: none;
            border-radius: 5px;
            transition: all 0.3s ease;
            text-transform: uppercase;
            letter-spacing: 1px;
            font-size: 0.9rem;
            opacity: 0;
            transform: translateY(20px);
            animation: fadeInUp 0.8s forwards 0.6s;
        }

        .btn:hover {
            background-color: #b8960c;
            transform: translateY(-2px);
        }

        /* Rest of your existing styles... */

        @media (max-width: 768px) {
            nav ul {
                display: none;
            }

            .hero h1 {
                font-size: 2.5rem;
            }

            .hero p {
                font-size: 1rem;
            }

            .slide-arrows {
                display: none;
            }
        }
        #portfolio {
            padding: 100px 5%;
            background: #fff;
        }

        .portfolio-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
            margin-top: 50px;
        }

        .portfolio-item {
            position: relative;
            overflow: hidden;
            border-radius: 8px;
            height: 300px;
        }

        .portfolio-item img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.3s ease;
        }

        .portfolio-overlay {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.7);
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            opacity: 0;
            transition: opacity 0.3s ease;
            color: white;
            text-align: center;
            padding: 20px;
        }

        .portfolio-item:hover .portfolio-overlay {
            opacity: 1;
        }

        .portfolio-item:hover img {
            transform: scale(1.1);
        }

        /* About Section */
        #about {
            padding: 100px 5%;
            background: var(--secondary-color);
        }

        .about-content {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 50px;
            align-items: center;
        }

        .about-image {
            border-radius: 8px;
            overflow: hidden;
        }

        .about-image img {
            width: 100%;
            height: auto;
        }

        .about-text h2 {
            font-family: 'Playfair Display', serif;
            font-size: 2.5rem;
            margin-bottom: 20px;
        }

        /* Contact Section */
        #contact {
            padding: 100px 5%;
            background: #fff;
        }

        .contact-container {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 50px;
        }

        .contact-info {
            padding-right: 50px;
        }

        .contact-info h2 {
            font-family: 'Playfair Display', serif;
            font-size: 2.5rem;
            margin-bottom: 20px;
        }

        .contact-form {
            display: grid;
            gap: 20px;
        }

        .form-group {
            display: flex;
            flex-direction: column;
        }

        .form-group label {
            margin-bottom: 5px;
            font-weight: 500;
        }

        .form-group input,
        .form-group textarea {
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-family: inherit;
        }

        .form-group textarea {
            height: 150px;
            resize: vertical;
        }

        /* Login Modal */
        .login-modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            z-index: 2000;
            justify-content: center;
            align-items: center;
        }

        .login-content {
            background: white;
            padding: 40px;
            border-radius: 8px;
            width: 90%;
            max-width: 400px;
            position: relative;
        }

        .close-login {
            position: absolute;
            top: 20px;
            right: 20px;
            font-size: 1.5rem;
            cursor: pointer;
        }

        /* Section Headers */
        .section-header {
            text-align: center;
            margin-bottom: 50px;
        }

        .section-header h2 {
            font-family: 'Playfair Display', serif;
            font-size: 2.5rem;
            color: var(--primary-color);
        }

        .section-header p {
            color: #666;
            margin-top: 10px;
        }
        .portfolio-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    gap: 30px;
    padding: 20px;
}

.portfolio-item {
    position: relative;
    overflow: hidden;
    border-radius: 10px;
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
    height: 300px;
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.portfolio-item:hover {
    transform: translateY(-5px);
    box-shadow: 0 6px 12px rgba(0,0,0,0.2);
}

.portfolio-item img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.5s ease;
}

.portfolio-overlay {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0, 0, 0, 0.7);
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
    opacity: 0;
    transition: opacity 0.3s ease;
    padding: 20px;
    text-align: center;
    color: white;
}

.portfolio-overlay h3 {
    font-size: 1.5rem;
    margin-bottom: 10px;
    transform: translateY(20px);
    transition: transform 0.3s ease;
}

.portfolio-overlay p {
    transform: translateY(20px);
    transition: transform 0.3s ease;
}

.portfolio-item:hover .portfolio-overlay {
    opacity: 1;
}

.portfolio-item:hover .portfolio-overlay h3,
.portfolio-item:hover .portfolio-overlay p {
    transform: translateY(0);
}

.portfolio-item:hover img {
    transform: scale(1.1);
}

.services-section {
    padding: 100px 5%;
    background-color: var(--secondary-color);
}

.services-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
    gap: 30px;
    margin-top: 50px;
}

.service-card {
    background: white;
    padding: 30px;
    border-radius: 10px;
    text-align: center;
    transition: transform 0.3s ease, box-shadow 0.3s ease;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    min-height: 300px;
}

.service-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 15px rgba(0, 0, 0, 0.2);
}

.service-icon {
    width: 80px;
    height: 80px;
    margin: 0 auto 20px;
    background: var(--accent-color);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: transform 0.3s ease;
}

.service-card:hover .service-icon {
    transform: scale(1.1);
}

.service-icon i {
    font-size: 35px;
    color: white;
}

.service-card h3 {
    font-family: 'Playfair Display', serif;
    font-size: 1.5rem;
    margin-bottom: 15px;
    color: var(--primary-color);
}

.service-card p {
    color: #666;
    margin-bottom: 20px;
    line-height: 1.6;
}

.service-btn {
    display: inline-block;
    padding: 12px 30px;
    background-color: var(--accent-color);
    color: white;
    text-decoration: none;
    border-radius: 5px;
    transition: all 0.3s ease;
    text-transform: uppercase;
    font-size: 0.9rem;
    letter-spacing: 1px;
}

.service-btn:hover {
    background-color: #b8960c;
    transform: translateY(-2px);
}

@media (max-width: 768px) {
    .services-grid {
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 20px;
    }
    
    .service-card {
        min-height: 280px;
    }
}
/* Responsive adjustments */
@media (max-width: 768px) {
    .portfolio-grid {
        grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
        gap: 20px;
        padding: 15px;
    }
}

        @media (max-width: 768px) {
            .about-content,
            .contact-container {
                grid-template-columns: 1fr;
            }

            .contact-info {
                padding-right: 0;
            }
        }
        
        .social-links {
            margin-top: 30px;
        }

        .social-links a {
            display: flex;
            align-items: center;
            color: var(--text-color);
            text-decoration: none;
            margin-bottom: 15px;
            transition: color 0.3s ease;
        }

        .social-links a:hover {
            color: var(--accent-color);
        }

        .social-links i {
            width: 30px;
            font-size: 1.5rem;
        }

        .social-platform {
            margin-left: 10px;
        }

        .social-username {
            color: var(--accent-color);
            font-weight: 500;
        }

        /* Login Button Style */
        .login-btn-nav {
            background-color: var(--accent-color);
            color: white;
            padding: 8px 20px;
            border-radius: 5px;
            text-decoration: none;
            transition: background-color 0.3s ease;
        }

        .login-btn-nav:hover {
            background-color: #b8960c;
        }

        /* Contact Form Enhancement */
        .contact-info {
            background: #f8f9fa;
            padding: 30px;
            border-radius: 8px;
        }

        .contact-details i {
            color: var(--accent-color);
            margin-right: 10px;
            width: 20px;
            text-align: center;
        }

        .contact-details p {
            margin-bottom: 15px;
            display: flex;
            align-items: center;
        }

        /* WhatsApp Button */
        .whatsapp-btn {
            display: inline-flex;
            align-items: center;
            background-color: #25D366;
            color: white;
            padding: 10px 20px;
            border-radius: 5px;
            text-decoration: none;
            margin-top: 20px;
            transition: background-color 0.3s ease;
        }

        .whatsapp-btn i {
            margin-right: 10px;
        }

        .whatsapp-btn:hover {
            background-color: #128C7E;
        }
        .footer-section {
    background-color: #b8960c;
    color: #fff;
    padding: 60px 0 20px;
    margin-top: 60px;
}

.footer-content {
    max-width: 1200px;
    margin: 0 auto;
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 40px;
    padding: 0 20px;
}

.footer-column {
    display: flex;
    flex-direction: column;
}

.footer-logo {
    max-width: 150px;
    margin-bottom: 20px;
}

.footer-column h3 {
    color: #fff;
    font-family: 'Montserrat', sans-serif;
    font-size: 1.2rem;
    margin-bottom: 20px;
    font-weight: 600;
}

.footer-column ul {
    list-style: none;
    padding: 0;
}

.footer-column ul li {
    margin-bottom: 12px;
}

.footer-column ul li a {
    color: #fff;
    text-decoration: none;
    transition: color 0.3s ease;
}

.footer-column ul li a:hover {
    color: var(--accent-color);
}

.footer-column p {
    margin-bottom: 10px;
    font-size: 0.9rem;
    line-height: 1.6;
}

.footer-social {
    display: flex;
    gap: 15px;
    margin-top: 20px;
}

.footer-social a {
    color: #fff;
    font-size: 1.5rem;
    transition: color 0.3s ease;
}

.footer-social a:hover {
    color: var(--accent-color);
}

.footer-bottom {
    max-width: 1200px;
    margin: 40px auto 0;
    padding: 20px;
    border-top: 1px solid rgba(255, 255, 255, 0.1);
    text-align: center;
    font-size: 0.9rem;
}

@media (max-width: 968px) {
    .footer-content {
        grid-template-columns: repeat(2, 1fr);
        gap: 30px;
    }
}

@media (max-width: 576px) {
    .footer-content {
        grid-template-columns: 1fr;
        text-align: center;
    }
    
    .footer-social {
        justify-content: center;
    }
}
    </style>
</head>
<body>
<header>
    <nav>
        <div class="logo">Mawar Photography</div>
        <ul>
            <li><a href="#home">Home</a></li>
            <li><a href="#gallery">Gallery</a></li>
            <li><a href="#about">About</a></li>
            <li><a href="#Services">Services</a></li>  <!-- Changed from portfolio to gallery -->
            <li><a href="#contact">Contact</a></li>
            <li><a href="Login_Page.php" class="login-btn-nav">Login</a></li>
        </ul>
    </nav>
</header>

    <section id="home" class="hero">
        <!-- HD Slideshow Images -->
        <div class="slide active" style="background-image: url('Gallery/ultah')"></div>
        <div class="slide" style="background-image: url('Gallery/Preweed.jpg')"></div>
        <div class="slide" style="background-image: url('Gallery/Wedding.jpg"></div>
        <div class="slide" style="background-image: url('Gallery/Engag.jpg')"></div>
        
        <div class="slide-arrows">
            <button class="slide-arrow" onclick="changeSlide(-1)">❮</button>
            <button class="slide-arrow" onclick="changeSlide(1)">❯</button>
        </div>
        
        <div class="slide-nav"></div>

        <div class="hero-content">
            <h1>Capture Your Precious Moments</h1>
            <p>Professional photography services for your special occasions</p>
            <a href="#contact" class="btn">Get in Touch</a>
        </div>
    </section>
    <section id="gallery">
    <div class="section-header">
        <h2>Our Gallery</h2>
        <p>Explore our collection of precious moments</p>
    </div>
    <div class="portfolio-grid">
        <div class="portfolio-item">
            <img src="Gallery/Wedding.jpg" alt="Wedding Photo">
            <div class="portfolio-overlay">
                <h3>Wedding Photography</h3>
                <p>Capturing beautiful wedding ceremonies</p>
            </div>
        </div>
        
        <div class="portfolio-item">
            <img src="Gallery/Engag.jpg" alt="Engagement Photo">
            <div class="portfolio-overlay">
                <h3>Engagement Photography</h3>
                <p>Celebrating love stories</p>
            </div>
        </div>

        <div class="portfolio-item">
            <img src="Gallery/Preweed.jpg" alt="Pre-wedding Photo">
            <div class="portfolio-overlay">
                <h3>Pre-wedding Photography</h3>
                <p>Romantic pre-wedding moments</p>
            </div>
        </div>

        <div class="portfolio-item">
            <img src="Gallery/ultah.jpg" alt="Birthday Photo">
            <div class="portfolio-overlay">
                <h3>Birthday Photography</h3>
                <p>Making birthday memories special</p>
            </div>
        </div>

        <div class="portfolio-item">
            <img src="Gallery/Reuni.jpg" alt="Reunion Photo">
            <div class="portfolio-overlay">
                <h3>Reunion Photography</h3>
                <p>Capturing reunited moments</p>
            </div>
        </div>

        <div class="portfolio-item">
            <img src="Gallery/KELAS.jpg" alt="Class Photo">
            <div class="portfolio-overlay">
                <h3>Class Photography</h3>
                <p>School memories that last forever</p>
            </div>
        </div>

        <div class="portfolio-item">
            <img src="Gallery/Wisuda.jpg" alt="Graduation Photo">
            <div class="portfolio-overlay">
                <h3>Graduation Photography</h3>
                <p>Celebrating academic achievements</p>
            </div>
        </div>

        <div class="portfolio-item">
            <img src="https://images.pexels.com/photos/2774556/pexels-photo-2774556.jpeg" alt="Family Photo">
            <div class="portfolio-overlay">
                <h3>Family Photography</h3>
                <p>Treasured family moments</p>
            </div>
        </div>
</section>

    <!-- About Section -->
    <section id="about">
        <div class="about-content">
            <div class="about-image">
                <img src="https://images.pexels.com/photos/2959192/pexels-photo-2959192.jpeg" alt="About Us">
            </div>
            <div class="about-text">
                <h2>About Us</h2>
                <p>Mawar Photography has been capturing life's precious moments for over a decade. Our passion for photography drives us to create stunning visual memories that last a lifetime.</p>
                <p>We specialize in wedding photography, portrait sessions, and special events, bringing our artistic vision and technical expertise to every shoot.</p>
            </div>
        </div>
    </section>
    <section id="Services" class="services-section">
    <div class="section-header">
        <h2>Our Photography Services</h2>
        <p>Choose from our professional photography packages</p>
    </div>
    
    <div class="services-grid">
        <!-- Wedding Photography -->
        <div class="service-card">
            <div class="service-icon">
                <i class="fas fa-ring"></i>
            </div>
            <h3>Booking Now Photography !</h3>
            <p>Capture your special day with our professional wedding & Other  photography services</p>
            <a href="services.php?service=wedding" class="service-btn">Book Now →</a>
        </div>

        <!-- Other Services -->
        <div class="service-card">
            <div class="service-icon">
                <i class="fas fa-plus"></i>
            </div>
            <h3>Custom Photography</h3>
            <p>Other events and custom photography services available</p>
            <a href="services.php?service=custom" class="service-btn">Contact Us →</a>
        </div>
    </div>
</section>

    <!-- Contact Section -->
    <!-- Login Modal -->
    <section id="contact">
        <div class="section-header">
            <h2>Contact Us</h2>
            <p>Let's discuss your photography needs</p>
        </div>
        <div class="contact-container">
            <div class="contact-info">
                <h3>Get in Touch</h3>
                <p>We'd love to hear from you. Contact us through any of these platforms:</p>
                
                <div class="social-links">
                    <!-- WhatsApp Contacts -->
                    <a href="https://wa.me/6285860772251" target="_blank">
                        <i class="fab fa-whatsapp"></i>
                        <div class="social-platform">
                            <span>WhatsApp</span><br>
                            <span class="social-username">085860772251</span>
                        </div>
                    </a>
                    <a href="https://wa.me/6285890533840" target="_blank">
                        <i class="fab fa-whatsapp"></i>
                        <div class="social-platform">
                            <span>WhatsApp Alternative</span><br>
                            <span class="social-username">+62 858-9053-3840</span>
                        </div>
                    </a>

                    <!-- Instagram Accounts -->
                    <a href="https://instagram.com/mawarphotography_" target="_blank">
                        <i class="fab fa-instagram"></i>
                        <div class="social-platform">
                            <span>Instagram</span><br>
                            <span class="social-username">@mawarphotography_</span>
                        </div>
                    </a>
                    <a href="https://instagram.com/yearbooksukabumi" target="_blank">
                        <i class="fab fa-instagram"></i>
                        <div class="social-platform">
                            <span>Instagram Yearbook</span><br>
                            <span class="social-username">@yearbooksukabumi</span>
                        </div>
                    </a>

                    <!-- TikTok -->
                    <a href="https://tiktok.com/@foto_wisudasukabumi" target="_blank">
                        <i class="fab fa-tiktok"></i>
                        <div class="social-platform">
                            <span>TikTok</span><br>
                            <span class="social-username">@foto_wisudasukabumi</span>
                        </div>
                    </a>
                </div>

                <!-- Quick Contact Button -->
                <a href="https://wa.me/6285860772251" class="whatsapp-btn" target="_blank">
                    <i class="fab fa-whatsapp"></i>
                    Chat with Us on WhatsApp
                </a>
            </div>
    </section>
    <footer class="footer-section">
    <div class="footer-content">
        <div class="footer-column">
            <img src="/path-to-your-logo.png" alt="Mawar Photography Logo" class="footer-logo">
            <p>The most trusted and complete photography services in Indonesia</p>
            <div class="footer-social">
                <a href="#"><i class="fab fa-facebook"></i></a>
                <a href="#"><i class="fab fa-instagram"></i></a>
                <a href="#"><i class="fab fa-tiktok"></i></a>
            </div>
        </div>
        
        <div class="footer-column">
            <h3>LEARN MORE</h3>
            <ul>
                <li><a href="#about">About Us</a></li>
                <li><a href="#faqs">FAQs</a></li>
                <li><a href="#terms">Terms and Conditions</a></li>
                <li><a href="#privacy">Privacy Policy</a></li>
            </ul>
        </div>
        
        <div class="footer-column">
            <h3>SOCIAL</h3>
            <ul>
                <li><a href="#">Facebook</a></li>
                <li><a href="#">Instagram</a></li>
            </ul>
        </div>
        
        <div class="footer-column">
            <h3>CONTACT</h3>
            <p>Sagaran, sukaraja 43192</p>
            <p>Jl. lorem ipsum</p>
            <p>Kav 52-53, Sagaran,</p>
            <p>Sagaran 12190</p>
            <p>hello@mawarphotography.com</p>
            <p>+62 819 680 680</p>
        </div>
    </div>
    
    <div class="footer-bottom">
        <p>&copy; 2024 Mawar Photography. All rights reserved.</p>
    </div>
</footer>
<script src="https://cdnjs.cloudflare.com/ajax/libs/aos/2.3.4/aos.js"></script>
    <script>
        // Previous script content remains...

        // Handle form submissions
        document.querySelector('.contact-form').addEventListener('submit', function(e) {
            e.preventDefault();
            // Add your form submission logic here
            alert('Message sent successfully!');
        });

        document.querySelector('.login-form').addEventListener('submit', function(e) {
            e.preventDefault();
            // Add your login logic here
            alert('Login successful!');
            closeLogin();
        });
    </script>
</body>

    <!-- Rest of your HTML content... -->

    <script>
        let currentSlide = 0;
        const slides = document.querySelectorAll('.slide');
        const slideNav = document.querySelector('.slide-nav');

        // Create navigation dots
        slides.forEach((_, index) => {
            const btn = document.createElement('button');
            btn.classList.add('slide-nav-btn');
            if (index === 0) btn.classList.add('active');
            btn.addEventListener('click', () => goToSlide(index));
            slideNav.appendChild(btn);
        });

        function showSlide(n) {
            slides[currentSlide].classList.remove('active');
            document.querySelectorAll('.slide-nav-btn')[currentSlide].classList.remove('active');
            
            currentSlide = (n + slides.length) % slides.length;
            
            slides[currentSlide].classList.add('active');
            document.querySelectorAll('.slide-nav-btn')[currentSlide].classList.add('active');
        }

        function changeSlide(direction) {
            showSlide(currentSlide + direction);
        }

        function goToSlide(n) {
            showSlide(n);
        }

        // Auto advance slides
        setInterval(() => changeSlide(1), 5000);

        // Smooth scrolling
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                document.querySelector(this.getAttribute('href')).scrollIntoView({
                    behavior: 'smooth'
                });
            });
        });

        // Navbar scroll effect
        window.addEventListener('scroll', function() {
            const header = document.querySelector('header');
            header.style.backgroundColor = window.scrollY > 50 ? 'rgba(255, 255, 255, 0.9)' : 'transparent';
        });
        document.querySelector('.contact-form').addEventListener('submit', function(e) {
            e.preventDefault();
            // Add your form submission logic here
            alert('Message sent successfully!');
        });
    </script>
</body>
</html>