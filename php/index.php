<?php
// FaZona EV - Main Website (PHP Version)
// Database configuration
$config = [
    'host' => 'localhost',
    'dbname' => 'fazona_ev',
    'username' => 'root',
    'password' => '',
    'charset' => 'utf8mb4'
];

// Connect to database
try {
    $pdo = new PDO(
        "mysql:host={$config['host']};dbname={$config['dbname']};charset={$config['charset']}", 
        $config['username'], 
        $config['password'],
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]
    );
} catch (PDOException $e) {
    // In production, log this error instead of displaying it
    $pdo = null;
}

// Fetch vehicles from database
$vehicles = [];
if ($pdo) {
    try {
        $stmt = $pdo->query("
            SELECT v.*, 
                   GROUP_CONCAT(vi.image_url ORDER BY vi.is_primary DESC, vi.created_at ASC) as images,
                   (SELECT vi2.image_url FROM vehicle_images vi2 WHERE vi2.vehicle_id = v.id AND vi2.is_primary = true LIMIT 1) as primary_image
            FROM vehicles v
            LEFT JOIN vehicle_images vi ON v.id = vi.vehicle_id
            WHERE v.is_active = true
            GROUP BY v.id
            ORDER BY v.created_at DESC
        ");
        $vehicles = $stmt->fetchAll();
        
        // Format vehicles data
        foreach ($vehicles as &$vehicle) {
            $vehicle['features'] = json_decode($vehicle['features'], true) ?: [];
            $vehicle['images'] = $vehicle['images'] ? explode(',', $vehicle['images']) : [];
        }
    } catch (PDOException $e) {
        // Handle error silently
        $vehicles = [];
    }
}

// Hero images for sliding
$heroImages = [
    '/fazona/20millionnairacar.jpg',
    '/fazona/20millionnaira.jpg',
    '/fazona/9.5millionnaira.jpg',
    '/fazona/4.5millionnaira.jpg'
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FaZona EV - Drive the Future Today</title>
    <meta name="description" content="Nigeria's Premier Electric Vehicle Brand. Experience premium electric mobility with FaZona EV. Clean, affordable, and smart transportation solutions designed for Nigeria's future." />
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700;800&family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        'brand-red': '#D6001C',
                        'brand-white': '#F8F8F8',
                        'brand-black': '#0A0A0A',
                    },
                    fontFamily: {
                        'poppins': ['Poppins', 'sans-serif'],
                        'montserrat': ['Montserrat', 'sans-serif'],
                    }
                }
            }
        }
    </script>
    
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #F8F8F8;
        }
        
        h1, h2, h3, h4, h5, h6 {
            font-family: 'Montserrat', sans-serif;
            font-weight: 700;
        }
        
        .btn-primary {
            background: linear-gradient(to right, #D6001C, #b8001a);
            color: white;
            padding: 1rem 2rem;
            border-radius: 9999px;
            font-family: 'Montserrat', sans-serif;
            font-weight: 600;
            transition: all 0.3s;
            border: none;
            cursor: pointer;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .btn-primary:hover {
            background: linear-gradient(to right, #b8001a, #9a0016);
            transform: scale(1.05);
            box-shadow: 0 10px 25px rgba(214, 0, 28, 0.3);
        }
        
        .btn-secondary {
            border: 2px solid #D6001C;
            color: #D6001C;
            padding: 1rem 2rem;
            border-radius: 9999px;
            font-family: 'Montserrat', sans-serif;
            font-weight: 600;
            transition: all 0.3s;
            background: transparent;
            cursor: pointer;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .btn-secondary:hover {
            background: #D6001C;
            color: white;
            transform: scale(1.05);
        }
        
        .gradient-text {
            background: linear-gradient(to right, #D6001C, #b8001a);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        
        .card-hover {
            transition: all 0.5s;
        }
        
        .card-hover:hover {
            transform: translateY(-8px);
            box-shadow: 0 25px 50px rgba(0, 0, 0, 0.15);
        }

        /* Hero Image Slider Styles */
        .hero-slider {
            position: relative;
            overflow: hidden;
            border-radius: 1.5rem;
            box-shadow: 0 25px 50px rgba(0, 0, 0, 0.15);
        }

        .hero-slide {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            opacity: 0;
            transition: opacity 0.8s ease-in-out;
        }

        .hero-slide.active {
            opacity: 1;
        }

        .hero-slide img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .hero-indicators {
            position: absolute;
            bottom: 1rem;
            left: 50%;
            transform: translateX(-50%);
            display: flex;
            gap: 0.5rem;
            z-index: 10;
        }

        .hero-indicator {
            width: 12px;
            height: 12px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.5);
            cursor: pointer;
            transition: all 0.3s;
        }

        .hero-indicator.active {
            background: white;
            transform: scale(1.2);
        }

        .hero-nav {
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            background: rgba(255, 255, 255, 0.8);
            border: none;
            border-radius: 50%;
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.3s;
            z-index: 10;
            opacity: 0;
        }

        .hero-slider:hover .hero-nav {
            opacity: 1;
        }

        .hero-nav:hover {
            background: white;
            transform: translateY(-50%) scale(1.1);
        }

        .hero-nav.prev {
            left: 1rem;
        }

        .hero-nav.next {
            right: 1rem;
        }

        /* Floating animations */
        @keyframes float {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-10px); }
        }

        .animate-float {
            animation: float 3s ease-in-out infinite;
        }

        @keyframes pulse-glow {
            0%, 100% { box-shadow: 0 0 20px rgba(214, 0, 28, 0.3); }
            50% { box-shadow: 0 0 40px rgba(214, 0, 28, 0.6); }
        }

        .animate-pulse-glow {
            animation: pulse-glow 2s ease-in-out infinite;
        }

        /* Feature pills animation */
        .feature-pill {
            animation: float 2s ease-in-out infinite;
        }

        .feature-pill:nth-child(2) {
            animation-delay: 0.2s;
        }

        .feature-pill:nth-child(3) {
            animation-delay: 0.4s;
        }
    </style>
</head>
<body>
    <!-- Header -->
    <header class="fixed top-0 left-0 right-0 z-50 bg-white/95 backdrop-blur-md shadow-lg">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-20">
                <!-- Logo -->
                <div class="flex items-center">
                    <img src="/fazona/FaZona.png" alt="FaZona EV Logo" class="h-20 w-auto">
                </div>

                <!-- Desktop Navigation -->
                <nav class="hidden lg:flex items-center space-x-8">
                    <a href="#home" class="text-brand-black hover:text-brand-red transition-colors duration-300 font-medium">Home</a>
                    <a href="#features" class="text-brand-black hover:text-brand-red transition-colors duration-300 font-medium">Features</a>
                    <a href="#vehicles" class="text-brand-black hover:text-brand-red transition-colors duration-300 font-medium">Vehicles</a>
                    <a href="#about" class="text-brand-black hover:text-brand-red transition-colors duration-300 font-medium">About</a>
                    <a href="#contact" class="text-brand-black hover:text-brand-red transition-colors duration-300 font-medium">Contact</a>
                </nav>

                <!-- Admin Button -->
                <a href="/admin" class="hidden lg:flex btn-primary">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                    </svg>
                    Admin Panel
                </a>

                <!-- Mobile Menu Button -->
                <button class="lg:hidden relative w-12 h-12 rounded-full bg-gradient-to-r from-brand-red to-red-600 flex items-center justify-center shadow-lg" onclick="toggleMobileMenu()">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                    </svg>
                </button>
            </div>

            <!-- Mobile Menu -->
            <div id="mobileMenu" class="lg:hidden hidden bg-white rounded-3xl mt-4 shadow-2xl border border-gray-100 p-8">
                <div class="space-y-6">
                    <a href="#home" class="block text-brand-black hover:text-brand-red transition-colors duration-300 font-medium py-3 text-lg border-b border-gray-100">Home</a>
                    <a href="#features" class="block text-brand-black hover:text-brand-red transition-colors duration-300 font-medium py-3 text-lg border-b border-gray-100">Features</a>
                    <a href="#vehicles" class="block text-brand-black hover:text-brand-red transition-colors duration-300 font-medium py-3 text-lg border-b border-gray-100">Vehicles</a>
                    <a href="#about" class="block text-brand-black hover:text-brand-red transition-colors duration-300 font-medium py-3 text-lg border-b border-gray-100">About</a>
                    <a href="#contact" class="block text-brand-black hover:text-brand-red transition-colors duration-300 font-medium py-3 text-lg">Contact</a>
                    <a href="/admin" class="btn-primary w-full justify-center mt-6">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                        </svg>
                        Admin Panel
                    </a>
                </div>
            </div>
        </div>
    </header>

    <!-- Hero Section with Sliding Images -->
    <section id="home" class="relative min-h-screen flex items-center justify-center overflow-hidden pt-20">
        <!-- Background -->
        <div class="absolute inset-0 bg-gradient-to-br from-brand-white via-gray-50 to-red-50"></div>
        
        <!-- Animated Background Elements -->
        <div class="absolute top-20 right-10 w-20 h-20 border-4 border-brand-red/30 rounded-full animate-spin" style="animation-duration: 20s;"></div>
        <div class="absolute bottom-20 left-10 w-16 h-16 bg-gradient-to-r from-brand-red/20 to-red-600/30 rounded-full animate-float"></div>

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10 w-full">
            <div class="grid lg:grid-cols-2 gap-8 items-center w-full">
                <!-- Left Content -->
                <div class="space-y-6 w-full">
                    <!-- Main Heading -->
                    <h1 class="text-4xl lg:text-6xl font-montserrat font-bold text-brand-black leading-tight">
                        <span class="animate-pulse-glow">Drive the</span>
                        <span class="gradient-text block animate-float">Future</span>
                        <span>Today</span>
                    </h1>

                    <!-- Premier EV Brand Text -->
                    <div class="text-lg font-semibold text-brand-red">
                        Nigeria's Premier EV Brand
                    </div>

                    <!-- Subheading -->
                    <p class="text-lg text-gray-600 leading-relaxed max-w-lg">
                        Experience premium electric mobility with FaZona EV. Clean, affordable, and smart transportation solutions designed for Nigeria's future.
                    </p>

                    <!-- Feature Pills -->
                    <div class="flex flex-wrap gap-3">
                        <div class="feature-pill flex items-center space-x-2 bg-white px-3 py-2 rounded-full shadow-md border border-gray-100">
                            <div class="w-5 h-5 rounded-full bg-gradient-to-r from-green-400 to-emerald-500 flex items-center justify-center">
                                <svg class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                            </div>
                            <span class="text-brand-black font-medium text-sm">Zero Emissions</span>
                        </div>
                        <div class="feature-pill flex items-center space-x-2 bg-white px-3 py-2 rounded-full shadow-md border border-gray-100">
                            <div class="w-5 h-5 rounded-full bg-gradient-to-r from-yellow-400 to-orange-500 flex items-center justify-center">
                                <svg class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                                </svg>
                            </div>
                            <span class="text-brand-black font-medium text-sm">Fast Charging</span>
                        </div>
                        <div class="feature-pill flex items-center space-x-2 bg-white px-3 py-2 rounded-full shadow-md border border-gray-100">
                            <div class="w-5 h-5 rounded-full bg-gradient-to-r from-blue-400 to-cyan-500 flex items-center justify-center">
                                <svg class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                            <span class="text-brand-black font-medium text-sm">Minimal Maintenance</span>
                        </div>
                    </div>

                    <!-- CTA Button -->
                    <div class="flex justify-start">
                        <a href="#vehicles" class="btn-primary animate-pulse-glow">
                            Explore Vehicles
                        </a>
                    </div>
                </div>

                <!-- Right Content - Sliding Car Images -->
                <div class="relative w-full animate-float">
                    <div class="hero-slider relative w-full h-96">
                        <?php foreach ($heroImages as $index => $image): ?>
                        <div class="hero-slide <?= $index === 0 ? 'active' : '' ?>" data-slide="<?= $index ?>">
                            <img src="<?= htmlspecialchars($image) ?>" alt="FaZona EV Car <?= $index + 1 ?>" class="w-full h-full object-cover">
                        </div>
                        <?php endforeach; ?>
                        
                        <!-- Navigation Arrows -->
                        <button class="hero-nav prev" onclick="changeSlide(-1)">
                            <svg class="w-5 h-5 text-brand-black" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                            </svg>
                        </button>
                        <button class="hero-nav next" onclick="changeSlide(1)">
                            <svg class="w-5 h-5 text-brand-black" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                            </svg>
                        </button>
                        
                        <!-- Indicators -->
                        <div class="hero-indicators">
                            <?php foreach ($heroImages as $index => $image): ?>
                            <button class="hero-indicator <?= $index === 0 ? 'active' : '' ?>" onclick="goToSlide(<?= $index ?>)" data-indicator="<?= $index ?>"></button>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    
                    <!-- Decorative Elements -->
                    <div class="absolute -top-8 -right-8 w-16 h-16 border-4 border-brand-red/40 rounded-full animate-spin" style="animation-duration: 8s;"></div>
                    <div class="absolute -bottom-8 -left-8 w-12 h-12 bg-gradient-to-r from-brand-red/30 to-red-600/30 rounded-full animate-float"></div>
                </div>
            </div>
        </div>

        <!-- Scroll Indicator -->
        <div class="absolute bottom-8 left-1/2 transform -translate-x-1/2 animate-bounce">
            <div class="flex flex-col items-center space-y-2">
                <svg class="w-8 h-8 text-brand-red" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3"></path>
                </svg>
                <div class="w-8 h-0.5 bg-brand-red"></div>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section id="features" class="py-20 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <h2 class="text-4xl lg:text-5xl font-montserrat font-bold text-brand-black mb-6">
                    Why Choose <span class="gradient-text">FaZona EV</span>
                </h2>
                <p class="text-xl text-gray-600 max-w-3xl mx-auto leading-relaxed">
                    Experience the future of transportation with cutting-edge technology, 
                    environmental consciousness, and unmatched reliability.
                </p>
            </div>

            <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-8">
                <!-- Feature 1 -->
                <div class="group relative bg-white rounded-3xl p-8 shadow-lg card-hover border border-gray-100">
                    <div class="w-16 h-16 rounded-2xl bg-gradient-to-r from-yellow-400 to-orange-500 p-4 mb-6 group-hover:scale-110 transition-transform duration-300">
                        <svg class="w-full h-full text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                        </svg>
                    </div>
                    <h3 class="text-xl font-montserrat font-bold text-brand-black mb-4">Fast Charging Support</h3>
                    <p class="text-gray-600 leading-relaxed">Advanced charging technology that gets you back on the road quickly with minimal downtime.</p>
                </div>

                <!-- Feature 2 -->
                <div class="group relative bg-white rounded-3xl p-8 shadow-lg card-hover border border-gray-100">
                    <div class="w-16 h-16 rounded-2xl bg-gradient-to-r from-green-400 to-emerald-500 p-4 mb-6 group-hover:scale-110 transition-transform duration-300">
                        <svg class="w-full h-full text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                    </div>
                    <h3 class="text-xl font-montserrat font-bold text-brand-black mb-4">Zero Emissions</h3>
                    <p class="text-gray-600 leading-relaxed">Completely clean energy transportation contributing to a healthier environment for Nigeria.</p>
                </div>

                <!-- Feature 3 -->
                <div class="group relative bg-white rounded-3xl p-8 shadow-lg card-hover border border-gray-100">
                    <div class="w-16 h-16 rounded-2xl bg-gradient-to-r from-blue-400 to-cyan-500 p-4 mb-6 group-hover:scale-110 transition-transform duration-300">
                        <svg class="w-full h-full text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <h3 class="text-xl font-montserrat font-bold text-brand-black mb-4">Regenerative Braking</h3>
                    <p class="text-gray-600 leading-relaxed">Intelligent braking system that recovers energy while driving, extending your vehicle range.</p>
                </div>

                <!-- Feature 4 -->
                <div class="group relative bg-white rounded-3xl p-8 shadow-lg card-hover border border-gray-100">
                    <div class="w-16 h-16 rounded-2xl bg-gradient-to-r from-purple-400 to-pink-500 p-4 mb-6 group-hover:scale-110 transition-transform duration-300">
                        <svg class="w-full h-full text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                        </svg>
                    </div>
                    <h3 class="text-xl font-montserrat font-bold text-brand-black mb-4">Smart Digital Dashboard</h3>
                    <p class="text-gray-600 leading-relaxed">Intuitive digital interface providing real-time vehicle data and connectivity features.</p>
                </div>

                <!-- Feature 5 -->
                <div class="group relative bg-white rounded-3xl p-8 shadow-lg card-hover border border-gray-100">
                    <div class="w-16 h-16 rounded-2xl bg-gradient-to-r from-red-400 to-rose-500 p-4 mb-6 group-hover:scale-110 transition-transform duration-300">
                        <svg class="w-full h-full text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                        </svg>
                    </div>
                    <h3 class="text-xl font-montserrat font-bold text-brand-black mb-4">Minimal Maintenance</h3>
                    <p class="text-gray-600 leading-relaxed">Electric motors require significantly less maintenance compared to traditional combustion engines.</p>
                </div>

                <!-- Feature 6 -->
                <div class="group relative bg-white rounded-3xl p-8 shadow-lg card-hover border border-gray-100">
                    <div class="w-16 h-16 rounded-2xl bg-gradient-to-r from-indigo-400 to-blue-500 p-4 mb-6 group-hover:scale-110 transition-transform duration-300">
                        <svg class="w-full h-full text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                        </svg>
                    </div>
                    <h3 class="text-xl font-montserrat font-bold text-brand-black mb-4">Government Duty Inclusive</h3>
                    <p class="text-gray-600 leading-relaxed">Select models include all government duties and taxes, providing transparent pricing.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Vehicles Section -->
    <section id="vehicles" class="py-20 bg-gradient-to-br from-gray-50 to-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <h2 class="text-4xl lg:text-5xl font-montserrat font-bold text-brand-black mb-6">
                    Our <span class="gradient-text">Vehicle Lineup</span>
                </h2>
                <p class="text-xl text-gray-600 max-w-3xl mx-auto leading-relaxed">
                    Choose from our range of premium electric vehicles designed to meet every need and budget.
                </p>
            </div>

            <!-- Dynamic Vehicles from Database -->
            <?php if (!empty($vehicles)): ?>
            <div class="grid md:grid-cols-2 lg:grid-cols-2 gap-8 mb-16">
                <?php foreach ($vehicles as $vehicle): ?>
                <div class="group relative bg-white rounded-3xl overflow-hidden shadow-lg hover:shadow-2xl transition-all duration-500 card-hover">
                    <!-- Badge -->
                    <?php if ($vehicle['badge']): ?>
                    <div class="absolute top-6 left-6 <?= htmlspecialchars($vehicle['badge_color'] ?: 'bg-brand-red') ?> text-white px-4 py-2 rounded-full text-sm font-semibold z-20">
                        <?= htmlspecialchars($vehicle['badge']) ?>
                    </div>
                    <?php endif; ?>

                    <!-- Rating -->
                    <div class="absolute top-6 right-6 flex items-center space-x-1 bg-white/90 backdrop-blur-sm px-3 py-1 rounded-full z-20">
                        <?php for ($i = 1; $i <= 5; $i++): ?>
                        <svg class="w-4 h-4 <?= $i <= $vehicle['rating'] ? 'text-yellow-400 fill-current' : 'text-gray-300' ?>" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path>
                        </svg>
                        <?php endfor; ?>
                    </div>

                    <!-- Vehicle Image -->
                    <div class="relative h-64 overflow-hidden">
                        <?php if ($vehicle['primary_image']): ?>
                        <img src="<?= htmlspecialchars($vehicle['primary_image']) ?>" alt="<?= htmlspecialchars($vehicle['name']) ?>" class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-110">
                        <?php else: ?>
                        <div class="w-full h-full bg-gray-200 flex items-center justify-center">
                            <svg class="w-12 h-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                            </svg>
                        </div>
                        <?php endif; ?>
                        <div class="absolute inset-0 bg-gradient-to-t from-black/30 to-transparent"></div>
                    </div>

                    <!-- Content -->
                    <div class="p-8">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-2xl font-montserrat font-bold text-brand-black">
                                <?= htmlspecialchars($vehicle['name']) ?>
                            </h3>
                            <div class="flex items-center space-x-2 text-brand-red">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                                </svg>
                                <span class="font-semibold"><?= htmlspecialchars($vehicle['range_km']) ?></span>
                            </div>
                        </div>

                        <div class="text-3xl font-montserrat font-bold text-brand-red mb-6">
                            <?= htmlspecialchars($vehicle['price']) ?>
                        </div>

                        <!-- Description -->
                        <?php if ($vehicle['description']): ?>
                        <p class="text-gray-600 mb-4"><?= htmlspecialchars($vehicle['description']) ?></p>
                        <?php endif; ?>

                        <!-- Features -->
                        <div class="grid grid-cols-2 gap-3 mb-6">
                            <?php foreach (array_slice($vehicle['features'], 0, 4) as $feature): ?>
                            <div class="flex items-center space-x-2 text-sm text-gray-600">
                                <div class="w-2 h-2 bg-brand-red rounded-full"></div>
                                <span><?= htmlspecialchars($feature) ?></span>
                            </div>
                            <?php endforeach; ?>
                        </div>

                        <!-- CTA -->
                        <a href="mailto:evfazona@gmail.com?subject=Quote Request for <?= urlencode($vehicle['name']) ?>&body=Hello FaZona EV Team,%0A%0AI am interested in getting a quote for the <?= urlencode($vehicle['name']) ?> (<?= urlencode($vehicle['price']) ?>).%0A%0APlease provide me with more information.%0A%0AThank you!" class="btn-primary w-full justify-center">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 4.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                            </svg>
                            Get Quote
                        </a>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>

            <!-- Electric Tricycle Section -->
            <div class="bg-gradient-to-r from-white to-gray-50 rounded-3xl overflow-hidden shadow-xl">
                <div class="grid lg:grid-cols-2 gap-0">
                    <!-- Image -->
                    <div class="relative h-80 lg:h-auto overflow-hidden">
                        <img src="/fazona/tricicle.jpg" alt="Electric Tricycle" class="w-full h-full object-cover transition-transform duration-500 hover:scale-105">
                        <div class="absolute inset-0 bg-gradient-to-r from-brand-red/20 to-transparent"></div>
                        <div class="absolute top-6 left-6 bg-yellow-400 text-black px-4 py-2 rounded-full text-sm font-bold">
                            Commercial Grade
                        </div>
                    </div>

                    <!-- Content -->
                    <div class="p-12 flex flex-col justify-center">
                        <div class="flex items-center space-x-3 mb-6">
                            <div class="w-12 h-12 bg-brand-red rounded-full flex items-center justify-center">
                                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7v8a2 2 0 002 2h6M8 7V5a2 2 0 012-2h4.586a1 1 0 01.707.293l4.414 4.414a1 1 0 01.293.707V15a2 2 0 01-2 2v0a2 2 0 01-2-2v-5a2 2 0 00-2-2H8z"></path>
                                </svg>
                            </div>
                            <h3 class="text-3xl font-montserrat font-bold text-brand-black">
                                Electric Tricycle (EV Keke)
                            </h3>
                        </div>

                        <p class="text-xl text-gray-600 mb-8 leading-relaxed">
                            Built for short-distance commutes and intra-city delivery
                        </p>

                        <!-- Features -->
                        <div class="grid grid-cols-2 gap-4 mb-8">
                            <?php 
                            $tricycleFeatures = ['Commercial Use', 'Cargo Capacity', 'Low Operating Cost', 'Durable Build'];
                            foreach ($tricycleFeatures as $feature): 
                            ?>
                            <div class="flex items-center space-x-3">
                                <div class="w-3 h-3 bg-brand-red rounded-full"></div>
                                <span class="text-gray-700 font-medium"><?= htmlspecialchars($feature) ?></span>
                            </div>
                            <?php endforeach; ?>
                        </div>

                        <div class="flex flex-col sm:flex-row gap-4">
                            <a href="mailto:evfazona@gmail.com?subject=Quote Request for Electric Tricycle&body=Hello FaZona EV Team,%0A%0AI am interested in getting pricing information for the Electric Tricycle (EV Keke).%0A%0APlease provide me with more details.%0A%0AThank you!" class="btn-primary flex-1 justify-center">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 4.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                                </svg>
                                Request Pricing
                            </a>
                            <button class="btn-secondary flex-1 justify-center">
                                Learn More
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- About Section -->
    <section id="about" class="py-20 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <h2 class="text-4xl lg:text-5xl font-montserrat font-bold text-brand-black mb-6">
                    Leading the <span class="gradient-text">Electric Revolution</span> in West Africa
                </h2>
                <p class="text-xl text-gray-600 max-w-4xl mx-auto leading-relaxed">
                    From Lagos to Abuja, from Kano to Port Harcourt - FaZona EV is transforming how Nigeria moves. 
                    We're not just selling cars; we're building the future of sustainable transportation across Africa.
                </p>
            </div>

            <div class="grid lg:grid-cols-2 gap-16 items-center">
                <!-- Left Content -->
                <div>
                    <h2 class="text-4xl lg:text-5xl font-montserrat font-bold text-brand-black mb-8">
                        Driving Africa's <span class="gradient-text">Electric Future</span>
                    </h2>
                    
                    <p class="text-xl text-gray-600 leading-relaxed mb-8">
                        FaZona EV is a forward-thinking electric vehicle brand focused on clean, 
                        affordable, and smart mobility in Nigeria. Our product lineup is tailored 
                        to meet the needs of both individuals and businesses seeking energy-efficient 
                        transportation solutions.
                    </p>

                    <div class="space-y-6">
                        <div class="flex items-start space-x-4">
                            <div class="w-6 h-6 bg-brand-red rounded-full flex items-center justify-center mt-1">
                                <div class="w-2 h-2 bg-white rounded-full"></div>
                            </div>
                            <div>
                                <h4 class="font-montserrat font-semibold text-brand-black mb-2">
                                    Sustainable Innovation
                                </h4>
                                <p class="text-gray-600">
                                    Leading the charge in electric vehicle technology with cutting-edge solutions 
                                    designed for African roads and conditions.
                                </p>
                            </div>
                        </div>

                        <div class="flex items-start space-x-4">
                            <div class="w-6 h-6 bg-brand-red rounded-full flex items-center justify-center mt-1">
                                <div class="w-2 h-2 bg-white rounded-full"></div>
                            </div>
                            <div>
                                <h4 class="font-montserrat font-semibold text-brand-black mb-2">
                                    Local Understanding
                                </h4>
                                <p class="text-gray-600">
                                    Built with deep understanding of Nigerian transportation needs, 
                                    infrastructure, and economic considerations.
                                </p>
                            </div>
                        </div>

                        <div class="flex items-start space-x-4">
                            <div class="w-6 h-6 bg-brand-red rounded-full flex items-center justify-center mt-1">
                                <div class="w-2 h-2 bg-white rounded-full"></div>
                            </div>
                            <div>
                                <h4 class="font-montserrat font-semibold text-brand-black mb-2">
                                    Future Ready
                                </h4>
                                <p class="text-gray-600">
                                    Preparing Nigeria for the global shift to electric mobility with 
                                    reliable, efficient, and affordable electric vehicles.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right Content - Logo -->
                <div class="relative">
                    <div class="relative bg-gradient-to-br from-gray-50 to-white rounded-3xl p-12 shadow-lg">
                        <img src="/fazona/LogoFaZona.png" alt="FaZona EV Logo" class="w-full h-auto max-w-md mx-auto">
                        
                        <!-- Decorative Elements -->
                        <div class="absolute -top-6 -right-6 w-12 h-12 border-4 border-brand-red/20 rounded-full animate-spin" style="animation-duration: 20s;"></div>
                        <div class="absolute -bottom-6 -left-6 w-8 h-8 bg-brand-red/20 rounded-full animate-float"></div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Contact Section -->
    <section id="contact" class="py-20 bg-gray-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <h2 class="text-4xl lg:text-5xl font-montserrat font-bold text-brand-black mb-6">
                    Get in <span class="gradient-text">Touch</span>
                </h2>
                <p class="text-xl text-gray-600 max-w-3xl mx-auto leading-relaxed">
                    Ready to join the electric revolution? Contact us today to learn more about 
                    our vehicles or schedule a test drive.
                </p>
            </div>

            <div class="grid lg:grid-cols-2 gap-16">
                <!-- Contact Form -->
                <div class="bg-white rounded-3xl p-8 shadow-lg">
                    <div class="flex items-center space-x-3 mb-8">
                        <div class="w-12 h-12 bg-brand-red rounded-full flex items-center justify-center">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
                            </svg>
                        </div>
                        <h3 class="text-2xl font-montserrat font-bold text-brand-black">
                            Send us a Message
                        </h3>
                    </div>

                    <form action="mailto:evfazona@gmail.com" method="post" enctype="text/plain" class="space-y-6">
                        <div class="grid md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-semibold text-brand-black mb-2">Full Name *</label>
                                <input type="text" name="name" required class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-brand-red focus:border-transparent transition-all duration-300" placeholder="Your full name">
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-brand-black mb-2">Email Address *</label>
                                <input type="email" name="email" required class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-brand-red focus:border-transparent transition-all duration-300" placeholder="your@email.com">
                            </div>
                        </div>

                        <div class="grid md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-semibold text-brand-black mb-2">Phone Number</label>
                                <input type="tel" name="phone" class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-brand-red focus:border-transparent transition-all duration-300" placeholder="+234 (0) 123 456 7890">
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-brand-black mb-2">Interested Vehicle</label>
                                <select name="vehicle" class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-brand-red focus:border-transparent transition-all duration-300">
                                    <option value="">Select a vehicle</option>
                                    <option value="premium-long-range">Premium Long Range (₦20M)</option>
                                    <option value="mid-range">Mid-Range Model (₦12M)</option>
                                    <option value="standard-range">Standard Range (₦9.5M)</option>
                                    <option value="compact-entry">Compact Entry (₦6.5M)</option>
                                    <option value="tricycle">Electric Tricycle</option>
                                </select>
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-brand-black mb-2">Message *</label>
                            <textarea name="message" required rows="5" class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-brand-red focus:border-transparent transition-all duration-300 resize-none" placeholder="Tell us about your requirements or questions..."></textarea>
                        </div>

                        <button type="submit" class="btn-primary w-full justify-center">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path>
                            </svg>
                            Send Message
                        </button>
                    </form>
                </div>

                <!-- Contact Information -->
                <div class="space-y-8">
                    <div>
                        <h3 class="text-3xl font-montserrat font-bold text-brand-black mb-6">
                            Let's Start a Conversation
                        </h3>
                        <p class="text-lg text-gray-600 leading-relaxed mb-8">
                            Whether you're interested in purchasing a vehicle, need technical support, 
                            or want to learn more about our electric mobility solutions, we're here to help.
                        </p>
                    </div>

                    <!-- Contact Cards -->
                    <div class="space-y-6">
                        <!-- Email -->
                        <div class="bg-white rounded-2xl p-6 shadow-lg card-hover cursor-pointer" onclick="window.location.href='mailto:evfazona@gmail.com'">
                            <div class="flex items-center space-x-4">
                                <div class="w-12 h-12 bg-gradient-to-r from-brand-red to-red-600 rounded-xl flex items-center justify-center">
                                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 4.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                                    </svg>
                                </div>
                                <div class="flex-1">
                                    <h4 class="font-montserrat font-semibold text-brand-black mb-1">Email Us</h4>
                                    <p class="text-gray-600 mb-2">evfazona@gmail.com</p>
                                    <button class="text-brand-red font-semibold hover:underline">Send Email</button>
                                </div>
                            </div>
                        </div>

                        <!-- WhatsApp -->
                        <div class="bg-white rounded-2xl p-6 shadow-lg card-hover cursor-pointer" onclick="window.open('https://wa.me/2349135859888', '_blank')">
                            <div class="flex items-center space-x-4">
                                <div class="w-12 h-12 bg-gradient-to-r from-brand-red to-red-600 rounded-xl flex items-center justify-center">
                                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path>
                                    </svg>
                                </div>
                                <div class="flex-1">
                                    <h4 class="font-montserrat font-semibold text-brand-black mb-1">WhatsApp Us</h4>
                                    <p class="text-gray-600 mb-2">+234 913 585 9888</p>
                                    <button class="text-brand-red font-semibold hover:underline">Chat on WhatsApp</button>
                                </div>
                            </div>
                        </div>

                        <!-- Instagram -->
                        <div class="bg-white rounded-2xl p-6 shadow-lg card-hover cursor-pointer" onclick="window.open('https://www.instagram.com/fazona_ev?igsh=MTdqeTZvMno4d294eQ%3D%3D&utm_source=qr', '_blank')">
                            <div class="flex items-center space-x-4">
                                <div class="w-12 h-12 bg-gradient-to-r from-brand-red to-red-600 rounded-xl flex items-center justify-center">
                                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 4V2a1 1 0 011-1h8a1 1 0 011 1v2m-9 4v10a2 2 0 002 2h6a2 2 0 002-2V8M7 8h10M7 8l-2-2m12 2l2-2"></path>
                                    </svg>
                                </div>
                                <div class="flex-1">
                                    <h4 class="font-montserrat font-semibold text-brand-black mb-1">Follow Us</h4>
                                    <p class="text-gray-600 mb-2">@fazona_ev</p>
                                    <button class="text-brand-red font-semibold hover:underline">View Instagram</button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- CTA Section -->
                    <div class="bg-gradient-to-r from-brand-red to-red-600 rounded-2xl p-8 text-white">
                        <h4 class="text-2xl font-montserrat font-bold mb-4">Ready to Go Electric?</h4>
                        <p class="text-red-100 mb-6 leading-relaxed">
                            Schedule a test drive today and experience the future of transportation firsthand.
                        </p>
                        <a href="mailto:evfazona@gmail.com?subject=Test Drive Request&body=Hello FaZona EV Team,%0A%0AI would like to schedule a test drive.%0A%0APlease contact me to arrange a convenient time.%0A%0AThank you!" class="bg-white text-brand-red px-6 py-3 rounded-full font-semibold hover:shadow-lg transition-all duration-300 inline-flex items-center space-x-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 4.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                            </svg>
                            <span>Get Quote Now</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-gradient-to-br from-brand-black to-gray-900 text-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
            <div class="grid lg:grid-cols-4 gap-12">
                <!-- Brand Section -->
                <div class="lg:col-span-1">
                    <div class="flex items-center space-x-3 mb-6">
                        <img src="/fazona/FaZona.png" alt="FaZona EV Logo" class="h-16 w-auto">
                    </div>
                    
                    <p class="text-gray-300 leading-relaxed mb-6">
                        Redefining transportation across Africa with eco-friendly, cost-effective, 
                        and future-forward electric mobility solutions.
                    </p>

                    <!-- Social Links -->
                    <div class="flex space-x-4">
                        <a href="#" class="w-12 h-12 bg-gradient-to-r from-gray-800 to-gray-700 rounded-full flex items-center justify-center hover:from-brand-red hover:to-red-600 transition-all duration-300 shadow-lg">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M24 4.557c-.883.392-1.832.656-2.828.775 1.017-.609 1.798-1.574 2.165-2.724-.951.564-2.005.974-3.127 1.195-.897-.957-2.178-1.555-3.594-1.555-3.179 0-5.515 2.966-4.797 6.045-4.091-.205-7.719-2.165-10.148-5.144-1.29 2.213-.669 5.108 1.523 6.574-.806-.026-1.566-.247-2.229-.616-.054 2.281 1.581 4.415 3.949 4.89-.693.188-1.452.232-2.224.084.626 1.956 2.444 3.379 4.6 3.419-2.07 1.623-4.678 2.348-7.29 2.04 2.179 1.397 4.768 2.212 7.548 2.212 9.142 0 14.307-7.721 13.995-14.646.962-.695 1.797-1.562 2.457-2.549z"/>
                            </svg>
                        </a>
                        <a href="https://www.instagram.com/fazona_ev?igsh=MTdqeTZvMno4d294eQ%3D%3D&utm_source=qr" class="w-12 h-12 bg-gradient-to-r from-gray-800 to-gray-700 rounded-full flex items-center justify-center hover:from-brand-red hover:to-red-600 transition-all duration-300 shadow-lg">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M12.017 0C5.396 0 .029 5.367.029 11.987c0 6.62 5.367 11.987 11.988 11.987s11.987-5.367 11.987-11.987C24.004 5.367 18.637.001 12.017.001zM8.449 16.988c-1.297 0-2.448-.611-3.132-1.551-.684-.94-.684-2.126 0-3.066.684-.94 1.835-1.551 3.132-1.551s2.448.611 3.132 1.551c.684.94.684 2.126 0 3.066-.684.94-1.835 1.551-3.132 1.551z"/>
                            </svg>
                        </a>
                    </div>
                </div>

                <!-- Quick Links -->
                <div>
                    <h4 class="text-xl font-montserrat font-bold mb-6">Quick Links</h4>
                    <ul class="space-y-3">
                        <li><a href="#home" class="text-gray-300 hover:text-brand-red transition-colors duration-300">Home</a></li>
                        <li><a href="#features" class="text-gray-300 hover:text-brand-red transition-colors duration-300">Features</a></li>
                        <li><a href="#vehicles" class="text-gray-300 hover:text-brand-red transition-colors duration-300">Vehicles</a></li>
                        <li><a href="#about" class="text-gray-300 hover:text-brand-red transition-colors duration-300">About</a></li>
                        <li><a href="#contact" class="text-gray-300 hover:text-brand-red transition-colors duration-300">Contact</a></li>
                    </ul>
                </div>

                <!-- Vehicles -->
                <div>
                    <h4 class="text-xl font-montserrat font-bold mb-6">Our Vehicles</h4>
                    <ul class="space-y-3">
                        <li><a href="#vehicles" class="text-gray-300 hover:text-brand-red transition-colors duration-300">Premium Long Range</a></li>
                        <li><a href="#vehicles" class="text-gray-300 hover:text-brand-red transition-colors duration-300">Mid-Range Model</a></li>
                        <li><a href="#vehicles" class="text-gray-300 hover:text-brand-red transition-colors duration-300">Standard Range</a></li>
                        <li><a href="#vehicles" class="text-gray-300 hover:text-brand-red transition-colors duration-300">Compact Entry</a></li>
                        <li><a href="#vehicles" class="text-gray-300 hover:text-brand-red transition-colors duration-300">Electric Tricycle</a></li>
                    </ul>
                </div>

                <!-- Contact Info -->
                <div>
                    <h4 class="text-xl font-montserrat font-bold mb-6">Contact Info</h4>
                    <div class="space-y-4">
                        <div class="flex items-start space-x-3 cursor-pointer group" onclick="window.location.href='mailto:evfazona@gmail.com'">
                            <svg class="w-5 h-5 text-brand-red mt-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 4.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                            </svg>
                            <div>
                                <p class="text-gray-300 group-hover:text-brand-red transition-colors">evfazona@gmail.com</p>
                            </div>
                        </div>
                        <div class="flex items-start space-x-3 cursor-pointer group" onclick="window.open('https://wa.me/2349135859888', '_blank')">
                            <svg class="w-5 h-5 text-brand-red mt-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path>
                            </svg>
                            <div>
                                <p class="text-gray-300 group-hover:text-brand-red transition-colors">+234 913 585 9888</p>
                            </div>
                        </div>
                        <div class="flex items-start space-x-3 cursor-pointer group" onclick="window.open('https://www.instagram.com/fazona_ev?igsh=MTdqeTZvMno4d294eQ%3D%3D&utm_source=qr', '_blank')">
                            <svg class="w-5 h-5 text-brand-red mt-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 4V2a1 1 0 011-1h8a1 1 0 011 1v2m-9 4v10a2 2 0 002 2h6a2 2 0 002-2V8M7 8h10M7 8l-2-2m12 2l2-2"></path>
                            </svg>
                            <div>
                                <p class="text-gray-300 group-hover:text-brand-red transition-colors">@fazona_ev</p>
                            </div>
                        </div>
                    </div>

                    <!-- Newsletter -->
                    <div class="mt-8">
                        <h5 class="font-montserrat font-semibold mb-3">Stay Updated</h5>
                        <form action="mailto:evfazona@gmail.com" method="post" enctype="text/plain" class="flex">
                            <input type="email" name="email" placeholder="Your email" required class="flex-1 px-4 py-3 bg-gray-800 border border-gray-700 rounded-l-xl focus:outline-none focus:border-brand-red text-white placeholder-gray-400">
                            <button type="submit" class="bg-brand-red px-6 py-3 rounded-r-xl hover:bg-red-700 transition-colors duration-300">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 4.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                                </svg>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Bottom Footer -->
        <div class="border-t border-gray-800">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
                <div class="flex flex-col md:flex-row justify-between items-center space-y-4 md:space-y-0">
                    <p class="text-gray-400 text-center md:text-left">
                        © 2025 FaZona EV. All rights reserved.
                    </p>
                    <div class="flex space-x-6 text-sm">
                        <a href="#" class="text-gray-400 hover:text-brand-red transition-colors duration-300">Privacy Policy</a>
                        <a href="#" class="text-gray-400 hover:text-brand-red transition-colors duration-300">Terms of Service</a>
                        <a href="#" class="text-gray-400 hover:text-brand-red transition-colors duration-300">Cookie Policy</a>
                    </div>
                </div>
            </div>
        </div>
    </footer>

    <script>
        // Mobile menu toggle
        function toggleMobileMenu() {
            const menu = document.getElementById('mobileMenu');
            menu.classList.toggle('hidden');
        }

        // Hero Image Slider
        let currentSlide = 0;
        const slides = document.querySelectorAll('.hero-slide');
        const indicators = document.querySelectorAll('.hero-indicator');
        const totalSlides = slides.length;

        function showSlide(index) {
            // Remove active class from all slides and indicators
            slides.forEach(slide => slide.classList.remove('active'));
            indicators.forEach(indicator => indicator.classList.remove('active'));
            
            // Add active class to current slide and indicator
            slides[index].classList.add('active');
            indicators[index].classList.add('active');
            
            currentSlide = index;
        }

        function nextSlide() {
            const next = (currentSlide + 1) % totalSlides;
            showSlide(next);
        }

        function prevSlide() {
            const prev = (currentSlide - 1 + totalSlides) % totalSlides;
            showSlide(prev);
        }

        function changeSlide(direction) {
            if (direction === 1) {
                nextSlide();
            } else {
                prevSlide();
            }
        }

        function goToSlide(index) {
            showSlide(index);
        }

        // Auto-advance slides every 4 seconds
        setInterval(nextSlide, 4000);

        // Smooth scrolling for navigation links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
                
                // Close mobile menu if open
                const mobileMenu = document.getElementById('mobileMenu');
                if (!mobileMenu.classList.contains('hidden')) {
                    mobileMenu.classList.add('hidden');
                }
            });
        });

        // Header scroll effect
        window.addEventListener('scroll', function() {
            const header = document.querySelector('header');
            if (window.scrollY > 50) {
                header.classList.add('bg-white/95', 'backdrop-blur-md', 'shadow-lg');
            } else {
                header.classList.remove('bg-white/95', 'backdrop-blur-md', 'shadow-lg');
            }
        });
    </script>
</body>
</html>