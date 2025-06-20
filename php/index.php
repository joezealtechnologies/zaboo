<?php
// FaZona EV - Main Website (PHP Version)
// This replaces the React frontend with a PHP version

session_start();

// Database configuration
$config = [
    'host' => 'localhost',
    'dbname' => 'fazona_ev',
    'username' => 'root',
    'password' => '',
    'charset' => 'utf8mb4'
];

// Try to connect to database
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
    
    // Fetch vehicles from database
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
        $vehicle['image'] = $vehicle['primary_image'] ?: ($vehicle['images'][0] ?? null);
    }
    
} catch (PDOException $e) {
    // If database connection fails, use static data
    $vehicles = [
        [
            'id' => 1,
            'name' => 'Premium Long Range',
            'price' => '₦20 million',
            'range_km' => '200km per charge',
            'description' => 'Our flagship electric vehicle with premium features and extended range.',
            'features' => ['Fast Charging', 'Premium Interior', 'Advanced Safety', 'Government Duty Inclusive'],
            'badge' => 'Most Popular',
            'badge_color' => 'bg-brand-red',
            'rating' => 5,
            'image' => '/fazona/20millionnaira.jpg',
            'images' => ['/fazona/20millionnaira.jpg', '/fazona/20millionnairacar.jpg']
        ],
        [
            'id' => 2,
            'name' => 'Mid-Range Model',
            'price' => '₦12 million',
            'range_km' => '150km per charge',
            'description' => 'Perfect balance of performance and affordability.',
            'features' => ['Smart Dashboard', 'Regenerative Braking', 'Fast Charging', 'Eco Mode'],
            'badge' => 'Best Value',
            'badge_color' => 'bg-green-500',
            'rating' => 5,
            'image' => '/fazona/9.5millionnaira.jpg',
            'images' => ['/fazona/9.5millionnaira.jpg']
        ],
        [
            'id' => 3,
            'name' => 'Standard Range',
            'price' => '₦9.5 million',
            'range_km' => '100km per charge',
            'description' => 'Ideal for city driving with essential features.',
            'features' => ['Digital Display', 'Energy Recovery', 'Compact Design', 'City Optimized'],
            'badge' => null,
            'badge_color' => null,
            'rating' => 4,
            'image' => '/fazona/4.5millionnaira.jpg',
            'images' => ['/fazona/4.5millionnaira.jpg']
        ]
    ];
}

// Handle contact form submission
$message = '';
if ($_POST['action'] ?? '' === 'contact') {
    $name = htmlspecialchars($_POST['name'] ?? '');
    $email = htmlspecialchars($_POST['email'] ?? '');
    $phone = htmlspecialchars($_POST['phone'] ?? '');
    $vehicle = htmlspecialchars($_POST['vehicle'] ?? '');
    $msg = htmlspecialchars($_POST['message'] ?? '');
    
    if ($name && $email && $msg) {
        $subject = "Contact Form Submission from $name";
        $body = "New contact form submission:\n\n";
        $body .= "Name: $name\n";
        $body .= "Email: $email\n";
        $body .= "Phone: $phone\n";
        $body .= "Interested Vehicle: $vehicle\n\n";
        $body .= "Message:\n$msg\n\n";
        $body .= "---\nThis message was sent from the FaZona EV website.";
        
        $headers = "From: $email\r\n";
        $headers .= "Reply-To: $email\r\n";
        
        if (mail('evfazona@gmail.com', $subject, $body, $headers)) {
            $message = 'success';
        } else {
            $message = 'error';
        }
    } else {
        $message = 'missing';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FaZona EV - Drive the Future Today</title>
    <meta name="description" content="Nigeria's Premier Electric Vehicle Brand. Experience premium electric mobility with FaZona EV. Clean, affordable, and smart transportation solutions designed for Nigeria's future.">
    
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
    
    <!-- Custom CSS -->
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
            font-size: 1.125rem;
            transition: all 0.3s;
            border: none;
            cursor: pointer;
        }
        
        .btn-primary:hover {
            background: linear-gradient(to right, #b8001a, #9a0016);
            transform: scale(1.05);
            box-shadow: 0 20px 40px rgba(214, 0, 28, 0.3);
        }
        
        .btn-secondary {
            border: 2px solid #D6001C;
            color: #D6001C;
            padding: 1rem 2rem;
            border-radius: 9999px;
            font-family: 'Montserrat', sans-serif;
            font-weight: 600;
            font-size: 1.125rem;
            transition: all 0.3s;
            background: transparent;
            cursor: pointer;
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
        
        .animate-float {
            animation: float 3s ease-in-out infinite;
        }
        
        @keyframes float {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-10px); }
        }
        
        .animate-pulse-glow {
            animation: pulse-glow 2s ease-in-out infinite;
        }
        
        @keyframes pulse-glow {
            0%, 100% { box-shadow: 0 0 20px rgba(214, 0, 28, 0.3); }
            50% { box-shadow: 0 0 40px rgba(214, 0, 28, 0.6); }
        }
        
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.6);
            backdrop-filter: blur(8px);
        }
        
        .modal.show {
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .modal-content {
            background: white;
            border-radius: 1.5rem;
            padding: 2rem;
            max-width: 90%;
            max-height: 90%;
            overflow-y: auto;
        }
        
        .image-carousel {
            position: relative;
            overflow: hidden;
            border-radius: 1rem;
        }
        
        .carousel-container {
            display: flex;
            transition: transform 0.3s ease;
        }
        
        .carousel-slide {
            min-width: 100%;
            height: 300px;
            object-fit: cover;
        }
        
        .carousel-nav {
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            background: rgba(0,0,0,0.5);
            color: white;
            border: none;
            border-radius: 50%;
            width: 40px;
            height: 40px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s;
        }
        
        .carousel-nav:hover {
            background: rgba(0,0,0,0.8);
        }
        
        .carousel-prev {
            left: 10px;
        }
        
        .carousel-next {
            right: 10px;
        }
        
        .carousel-indicators {
            position: absolute;
            bottom: 10px;
            left: 50%;
            transform: translateX(-50%);
            display: flex;
            gap: 8px;
        }
        
        .carousel-indicator {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: rgba(255,255,255,0.5);
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .carousel-indicator.active {
            background: white;
            transform: scale(1.2);
        }
    </style>
</head>
<body>
    <!-- Header -->
    <header class="fixed top-0 left-0 right-0 z-50 bg-white/95 backdrop-blur-md shadow-lg">
        <div class="max-w-7xl mx-auto px-6 lg:px-20">
            <div class="flex items-center justify-between h-20">
                <!-- Logo -->
                <div class="flex items-center">
                    <img src="/fazona/FaZona.png" alt="FaZona EV Logo" class="h-20 w-auto">
                </div>

                <!-- Desktop Navigation -->
                <nav class="hidden lg:flex items-center space-x-8">
                    <a href="#home" class="text-brand-black hover:text-brand-red transition-colors font-medium">Home</a>
                    <a href="#features" class="text-brand-black hover:text-brand-red transition-colors font-medium">Features</a>
                    <a href="#vehicles" class="text-brand-black hover:text-brand-red transition-colors font-medium">Vehicles</a>
                    <a href="#about" class="text-brand-black hover:text-brand-red transition-colors font-medium">About</a>
                    <a href="#contact" class="text-brand-black hover:text-brand-red transition-colors font-medium">Contact</a>
                </nav>

                <!-- Report Issue Button -->
                <button onclick="openReportModal()" class="hidden lg:flex items-center space-x-2 btn-primary">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                    </svg>
                    <span>Report Issue</span>
                </button>

                <!-- Mobile Menu Button -->
                <button onclick="toggleMobileMenu()" class="lg:hidden relative w-12 h-12 rounded-full bg-gradient-to-r from-brand-red to-red-600 flex items-center justify-center shadow-lg">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                    </svg>
                </button>
            </div>

            <!-- Mobile Menu -->
            <div id="mobileMenu" class="lg:hidden hidden bg-gradient-to-br from-white to-gray-50 rounded-3xl mt-4 shadow-2xl border border-gray-100 p-8">
                <div class="space-y-6">
                    <a href="#home" onclick="toggleMobileMenu()" class="block text-brand-black hover:text-brand-red transition-colors font-medium py-3 text-lg border-b border-gray-100">Home</a>
                    <a href="#features" onclick="toggleMobileMenu()" class="block text-brand-black hover:text-brand-red transition-colors font-medium py-3 text-lg border-b border-gray-100">Features</a>
                    <a href="#vehicles" onclick="toggleMobileMenu()" class="block text-brand-black hover:text-brand-red transition-colors font-medium py-3 text-lg border-b border-gray-100">Vehicles</a>
                    <a href="#about" onclick="toggleMobileMenu()" class="block text-brand-black hover:text-brand-red transition-colors font-medium py-3 text-lg border-b border-gray-100">About</a>
                    <a href="#contact" onclick="toggleMobileMenu()" class="block text-brand-black hover:text-brand-red transition-colors font-medium py-3 text-lg">Contact</a>
                    <button onclick="openReportModal(); toggleMobileMenu();" class="w-full btn-primary flex items-center justify-center space-x-2 mt-6">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                        </svg>
                        <span>Report Issue</span>
                    </button>
                </div>
            </div>
        </div>
    </header>

    <!-- Hero Section -->
    <section id="home" class="relative min-h-screen flex items-center justify-center overflow-hidden pt-20">
        <div class="absolute inset-0 bg-gradient-to-br from-brand-white via-gray-50 to-red-50"></div>
        
        <!-- Animated Background Elements -->
        <div class="absolute top-20 right-10 w-20 h-20 border-4 border-brand-red/30 rounded-full animate-pulse"></div>
        <div class="absolute bottom-20 left-10 w-16 h-16 bg-gradient-to-r from-brand-red/20 to-red-600/30 rounded-full animate-float"></div>

        <div class="max-w-7xl mx-auto px-6 lg:px-20 relative z-10 w-full">
            <div class="grid lg:grid-cols-2 gap-8 items-center w-full">
                <!-- Left Content -->
                <div class="space-y-6 w-full">
                    <h1 class="text-4xl lg:text-6xl font-montserrat font-bold text-brand-black leading-tight">
                        <span class="animate-pulse">Drive the</span>
                        <span class="gradient-text block">Future</span>
                        <span>Today</span>
                    </h1>

                    <div class="text-lg font-semibold text-brand-red">
                        Nigeria's Premier EV Brand
                    </div>

                    <p class="text-lg text-gray-600 leading-relaxed max-w-lg">
                        Experience premium electric mobility with FaZona EV. Clean, affordable, and smart transportation solutions designed for Nigeria's future.
                    </p>

                    <!-- Feature Pills -->
                    <div class="flex flex-wrap gap-3">
                        <div class="flex items-center space-x-2 bg-white px-3 py-2 rounded-full shadow-md border border-gray-100">
                            <div class="w-5 h-5 rounded-full bg-gradient-to-r from-green-400 to-emerald-500 flex items-center justify-center">
                                <svg class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                            </div>
                            <span class="text-brand-black font-medium text-sm">Zero Emissions</span>
                        </div>
                        <div class="flex items-center space-x-2 bg-white px-3 py-2 rounded-full shadow-md border border-gray-100">
                            <div class="w-5 h-5 rounded-full bg-gradient-to-r from-yellow-400 to-orange-500 flex items-center justify-center">
                                <svg class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                                </svg>
                            </div>
                            <span class="text-brand-black font-medium text-sm">Fast Charging</span>
                        </div>
                        <div class="flex items-center space-x-2 bg-white px-3 py-2 rounded-full shadow-md border border-gray-100">
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
                        <button onclick="document.getElementById('vehicles').scrollIntoView({behavior: 'smooth'})" class="btn-primary animate-pulse-glow">
                            Explore Vehicles
                        </button>
                    </div>
                </div>

                <!-- Right Content - Car Image -->
                <div class="relative w-full">
                    <div class="relative z-10 w-full animate-float">
                        <div class="relative overflow-hidden rounded-3xl shadow-2xl w-full">
                            <img src="/fazona/20millionnairacar.jpg" alt="FaZona EV Car" class="w-full h-auto max-w-full hover:scale-105 transition-transform duration-500">
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Scroll Indicator -->
        <div class="absolute bottom-8 left-1/2 transform -translate-x-1/2 animate-bounce">
            <div class="flex flex-col items-center space-y-2">
                <svg class="w-8 h-8 text-brand-red" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3"></path>
                </svg>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section id="features" class="py-20 bg-white">
        <div class="max-w-7xl mx-auto px-6 lg:px-20">
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

            <!-- Stats Section -->
            <div class="mt-20 bg-gradient-to-r from-brand-red to-red-600 rounded-3xl p-12 text-white">
                <div class="grid md:grid-cols-3 gap-8 text-center">
                    <div>
                        <h3 class="text-4xl lg:text-5xl font-montserrat font-bold mb-2">200km</h3>
                        <p class="text-red-100 text-lg">Maximum Range</p>
                    </div>
                    <div>
                        <h3 class="text-4xl lg:text-5xl font-montserrat font-bold mb-2">100%</h3>
                        <p class="text-red-100 text-lg">Electric Powered</p>
                    </div>
                    <div>
                        <h3 class="text-4xl lg:text-5xl font-montserrat font-bold mb-2">24/7</h3>
                        <p class="text-red-100 text-lg">Support Available</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Vehicles Section -->
    <section id="vehicles" class="py-20 bg-gradient-to-br from-gray-50 to-white">
        <div class="max-w-7xl mx-auto px-6 lg:px-20">
            <div class="text-center mb-16">
                <h2 class="text-4xl lg:text-5xl font-montserrat font-bold text-brand-black mb-6">
                    Our <span class="gradient-text">Vehicle Lineup</span>
                </h2>
                <p class="text-xl text-gray-600 max-w-3xl mx-auto leading-relaxed">
                    Choose from our range of premium electric vehicles designed to meet every need and budget.
                </p>
            </div>

            <!-- Electric Cars Grid -->
            <div class="grid md:grid-cols-2 lg:grid-cols-2 gap-8 mb-16">
                <?php foreach ($vehicles as $index => $vehicle): ?>
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

                    <!-- Image -->
                    <div class="relative h-64 overflow-hidden group">
                        <?php if ($vehicle['image']): ?>
                        <img src="<?= htmlspecialchars($vehicle['image']) ?>" alt="<?= htmlspecialchars($vehicle['name']) ?>" class="w-full h-full object-cover cursor-pointer transition-transform duration-500 group-hover:scale-110" onclick="openImageModal('<?= htmlspecialchars($vehicle['name']) ?>', <?= htmlspecialchars(json_encode($vehicle['images'])) ?>)">
                        
                        <!-- View Gallery Overlay -->
                        <div class="absolute inset-0 bg-black/40 flex items-center justify-center cursor-pointer z-10 opacity-0 group-hover:opacity-100 transition-opacity" onclick="openImageModal('<?= htmlspecialchars($vehicle['name']) ?>', <?= htmlspecialchars(json_encode($vehicle['images'])) ?>)">
                            <div class="bg-white/90 backdrop-blur-sm rounded-full p-6 flex flex-col items-center space-y-2">
                                <svg class="w-8 h-8 text-brand-red" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                </svg>
                                <span class="text-brand-red font-semibold text-sm">View Gallery</span>
                                <?php if (count($vehicle['images']) > 1): ?>
                                <span class="text-gray-600 text-xs"><?= count($vehicle['images']) ?> photos</span>
                                <?php endif; ?>
                            </div>
                        </div>
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
                        <p class="text-gray-600 mb-4 line-clamp-2"><?= htmlspecialchars($vehicle['description']) ?></p>
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
                        <button onclick="openQuoteModal('<?= htmlspecialchars($vehicle['name']) ?>', '<?= htmlspecialchars($vehicle['price']) ?>', '<?= htmlspecialchars($vehicle['image']) ?>')" class="w-full btn-primary flex items-center justify-center space-x-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 4.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                            </svg>
                            <span>Get Quote</span>
                        </button>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>

            <!-- Electric Tricycle Section -->
            <div class="bg-gradient-to-r from-white to-gray-50 rounded-3xl overflow-hidden shadow-xl">
                <div class="grid lg:grid-cols-2 gap-0">
                    <!-- Image -->
                    <div class="relative h-80 lg:h-auto overflow-hidden group">
                        <img src="/fazona/tricicle.jpg" alt="Electric Tricycle (EV Keke)" class="w-full h-full object-cover cursor-pointer transition-transform duration-500 group-hover:scale-105" onclick="openImageModal('Electric Tricycle (EV Keke)', ['/fazona/tricicle.jpg'])">
                        
                        <!-- View Image Overlay -->
                        <div class="absolute inset-0 bg-black/40 flex items-center justify-center cursor-pointer z-10 opacity-0 group-hover:opacity-100 transition-opacity" onclick="openImageModal('Electric Tricycle (EV Keke)', ['/fazona/tricicle.jpg'])">
                            <div class="bg-white/90 backdrop-blur-sm rounded-full p-6 flex flex-col items-center space-y-2">
                                <svg class="w-8 h-8 text-brand-red" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                </svg>
                                <span class="text-brand-red font-semibold text-sm">View Image</span>
                            </div>
                        </div>
                        
                        <div class="absolute inset-0 bg-gradient-to-r from-brand-red/20 to-transparent"></div>
                        
                        <!-- Floating Badge -->
                        <div class="absolute top-6 left-6 bg-yellow-400 text-black px-4 py-2 rounded-full text-sm font-bold z-20 animate-float">
                            Commercial Grade
                        </div>
                    </div>

                    <!-- Content -->
                    <div class="p-12 flex flex-col justify-center">
                        <div class="flex items-center space-x-3 mb-6">
                            <div class="w-12 h-12 bg-brand-red rounded-full flex items-center justify-center">
                                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17a2 2 0 11-4 0 2 2 0 014 0zM21 17a2 2 0 11-4 0 2 2 0 014 0z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 17h4v-6H7v6zM17 17h4v-6h-4v6z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 6h14l-1 7H6L5 6z"></path>
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
                            <button onclick="openQuoteModal('Electric Tricycle (EV Keke)', 'Contact for Pricing', '/fazona/tricicle.jpg')" class="btn-primary flex-1 flex items-center justify-center space-x-2">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 4.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                                </svg>
                                <span>Request Pricing</span>
                            </button>
                            <button class="btn-secondary flex-1">
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
        <div class="max-w-7xl mx-auto px-6 lg:px-20">
            <!-- Hero Stats Section -->
            <div class="text-center mb-20">
                <div class="inline-flex items-center space-x-3 bg-gradient-to-r from-brand-red/10 to-red-600/10 px-8 py-4 rounded-full border border-brand-red/20 mb-8">
                    <div class="w-8 h-8 bg-brand-red rounded-full flex items-center justify-center">
                        <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                        </svg>
                    </div>
                    <span class="text-brand-red font-bold text-xl">Revolutionizing Nigerian Transportation</span>
                </div>

                <h2 class="text-3xl lg:text-4xl font-montserrat font-bold text-brand-black mb-6">
                    Leading the <span class="gradient-text">Electric Revolution</span> in West Africa
                </h2>

                <p class="text-xl text-gray-600 max-w-4xl mx-auto leading-relaxed mb-12">
                    From Lagos to Abuja, from Kano to Port Harcourt - FaZona EV is transforming how Nigeria moves. 
                    We're not just selling cars; we're building the future of sustainable transportation across Africa.
                </p>

                <!-- Impressive Stats Grid -->
                <div class="grid md:grid-cols-2 lg:grid-cols-4 gap-8">
                    <div class="bg-gradient-to-br from-white to-gray-50 rounded-3xl p-8 shadow-lg border border-gray-100 card-hover">
                        <div class="w-16 h-16 rounded-2xl bg-gradient-to-r from-yellow-400 to-orange-500 flex items-center justify-center mx-auto mb-6">
                            <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                            </svg>
                        </div>
                        <h3 class="text-4xl lg:text-5xl font-montserrat font-bold text-brand-red mb-2">200km</h3>
                        <p class="text-gray-600 font-semibold">Maximum Range</p>
                    </div>

                    <div class="bg-gradient-to-br from-white to-gray-50 rounded-3xl p-8 shadow-lg border border-gray-100 card-hover">
                        <div class="w-16 h-16 rounded-2xl bg-gradient-to-r from-green-400 to-emerald-500 flex items-center justify-center mx-auto mb-6">
                            <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                        </div>
                        <h3 class="text-4xl lg:text-5xl font-montserrat font-bold text-brand-red mb-2">100%</h3>
                        <p class="text-gray-600 font-semibold">Zero Emissions</p>
                    </div>

                    <div class="bg-gradient-to-br from-white to-gray-50 rounded-3xl p-8 shadow-lg border border-gray-100 card-hover">
                        <div class="w-16 h-16 rounded-2xl bg-gradient-to-r from-blue-400 to-cyan-500 flex items-center justify-center mx-auto mb-6">
                            <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                        <h3 class="text-4xl lg:text-5xl font-montserrat font-bold text-brand-red mb-2">24/7</h3>
                        <p class="text-gray-600 font-semibold">Support Available</p>
                    </div>

                    <div class="bg-gradient-to-br from-white to-gray-50 rounded-3xl p-8 shadow-lg border border-gray-100 card-hover">
                        <div class="w-16 h-16 rounded-2xl bg-gradient-to-r from-purple-400 to-pink-500 flex items-center justify-center mx-auto mb-6">
                            <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                            </svg>
                        </div>
                        <h3 class="text-4xl lg:text-5xl font-montserrat font-bold text-brand-red mb-2">5+</h3>
                        <p class="text-gray-600 font-semibold">Vehicle Models</p>
                    </div>
                </div>
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
                        <div class="absolute -top-6 -right-6 w-12 h-12 border-4 border-brand-red/20 rounded-full animate-pulse"></div>
                        <div class="absolute -bottom-6 -left-6 w-8 h-8 bg-brand-red/20 rounded-full animate-float"></div>
                    </div>
                </div>
            </div>

            <!-- Values Grid -->
            <div class="mt-20">
                <div class="grid md:grid-cols-2 lg:grid-cols-4 gap-8">
                    <div class="text-center group">
                        <div class="w-16 h-16 bg-gradient-to-r from-brand-red to-red-600 rounded-2xl flex items-center justify-center mx-auto mb-6 group-hover:scale-110 transition-transform duration-300">
                            <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"></path>
                            </svg>
                        </div>
                        <h3 class="text-xl font-montserrat font-bold text-brand-black mb-4">Our Mission</h3>
                        <p class="text-gray-600 leading-relaxed">To redefine transportation across Africa by delivering eco-friendly, cost-effective, and future-forward electric mobility options.</p>
                    </div>

                    <div class="text-center group">
                        <div class="w-16 h-16 bg-gradient-to-r from-brand-red to-red-600 rounded-2xl flex items-center justify-center mx-auto mb-6 group-hover:scale-110 transition-transform duration-300">
                            <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                        <h3 class="text-xl font-montserrat font-bold text-brand-black mb-4">Global Impact</h3>
                        <p class="text-gray-600 leading-relaxed">Starting from Nigeria, we aim to transform the African automotive landscape with sustainable transportation solutions.</p>
                    </div>

                    <div class="text-center group">
                        <div class="w-16 h-16 bg-gradient-to-r from-brand-red to-red-600 rounded-2xl flex items-center justify-center mx-auto mb-6 group-hover:scale-110 transition-transform duration-300">
                            <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"></path>
                            </svg>
                        </div>
                        <h3 class="text-xl font-montserrat font-bold text-brand-black mb-4">Customer First</h3>
                        <p class="text-gray-600 leading-relaxed">Every vehicle is designed with our customers in mind, ensuring reliability, affordability, and exceptional performance.</p>
                    </div>

                    <div class="text-center group">
                        <div class="w-16 h-16 bg-gradient-to-r from-brand-red to-red-600 rounded-2xl flex items-center justify-center mx-auto mb-6 group-hover:scale-110 transition-transform duration-300">
                            <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"></path>
                            </svg>
                        </div>
                        <h3 class="text-xl font-montserrat font-bold text-brand-black mb-4">Quality Promise</h3>
                        <p class="text-gray-600 leading-relaxed">We maintain the highest standards in manufacturing and service, delivering premium electric vehicles you can trust.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Contact Section -->
    <section id="contact" class="py-20 bg-gray-50">
        <div class="max-w-7xl mx-auto px-6 lg:px-20">
            <div class="text-center mb-16">
                <h2 class="text-4xl lg:text-5xl font-montserrat font-bold text-brand-black mb-6">
                    Get in <span class="gradient-text">Touch</span>
                </h2>
                <p class="text-xl text-gray-600 max-w-3xl mx-auto leading-relaxed">
                    Ready to join the electric revolution? Contact us today to learn more about 
                    our vehicles or schedule a test drive.
                </p>
            </div>

            <?php if ($message): ?>
            <div class="mb-8 max-w-2xl mx-auto">
                <?php if ($message === 'success'): ?>
                <div class="bg-green-50 border border-green-200 text-green-800 px-6 py-4 rounded-xl">
                    <div class="flex items-center space-x-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <span class="font-semibold">Message sent successfully!</span>
                    </div>
                    <p class="mt-2">Thank you for contacting us. We'll get back to you soon.</p>
                </div>
                <?php elseif ($message === 'error'): ?>
                <div class="bg-red-50 border border-red-200 text-red-800 px-6 py-4 rounded-xl">
                    <div class="flex items-center space-x-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <span class="font-semibold">Error sending message</span>
                    </div>
                    <p class="mt-2">Please try again or contact us directly at evfazona@gmail.com</p>
                </div>
                <?php elseif ($message === 'missing'): ?>
                <div class="bg-yellow-50 border border-yellow-200 text-yellow-800 px-6 py-4 rounded-xl">
                    <div class="flex items-center space-x-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                        </svg>
                        <span class="font-semibold">Please fill in all required fields</span>
                    </div>
                </div>
                <?php endif; ?>
            </div>
            <?php endif; ?>

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

                    <form method="POST" class="space-y-6">
                        <input type="hidden" name="action" value="contact">
                        
                        <div class="grid md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-semibold text-brand-black mb-2">
                                    Full Name *
                                </label>
                                <input type="text" name="name" required class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-brand-red focus:border-transparent transition-all duration-300" placeholder="Your full name">
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-brand-black mb-2">
                                    Email Address *
                                </label>
                                <input type="email" name="email" required class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-brand-red focus:border-transparent transition-all duration-300" placeholder="your@email.com">
                            </div>
                        </div>

                        <div class="grid md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-semibold text-brand-black mb-2">
                                    Phone Number
                                </label>
                                <input type="tel" name="phone" class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-brand-red focus:border-transparent transition-all duration-300" placeholder="+234 (0) 123 456 7890">
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-brand-black mb-2">
                                    Interested Vehicle
                                </label>
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
                            <label class="block text-sm font-semibold text-brand-black mb-2">
                                Message *
                            </label>
                            <textarea name="message" required rows="5" class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-brand-red focus:border-transparent transition-all duration-300 resize-none" placeholder="Tell us about your requirements or questions..."></textarea>
                        </div>

                        <button type="submit" class="w-full btn-primary flex items-center justify-center space-x-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path>
                            </svg>
                            <span>Send Message</span>
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
                        <p class="text-red-100 mb-6 leading-relaxed">Schedule a test drive today and experience the future of transportation firsthand.</p>
                        <button onclick="openQuoteModal('General Quote', 'Contact for Pricing', '')" class="bg-white text-brand-red px-6 py-3 rounded-full font-semibold hover:shadow-lg transition-all duration-300 flex items-center space-x-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 4.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                            </svg>
                            <span>Get Quote Now</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-gradient-to-br from-brand-black to-gray-900 text-white relative overflow-hidden">
        <!-- Background Pattern -->
        <div class="absolute inset-0 opacity-5">
            <div class="absolute inset-0" style="background-image: url('data:image/svg+xml,%3Csvg width=\'60\' height=\'60\' viewBox=\'0 0 60 60\' xmlns=\'http://www.w3.org/2000/svg\'%3E%3Cg fill=\'none\' fill-rule=\'evenodd\'%3E%3Cg fill=\'%23ffffff\' fill-opacity=\'0.1\'%3E%3Ccircle cx=\'30\' cy=\'30\' r=\'2\'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E');"></div>
        </div>

        <!-- Main Footer -->
        <div class="max-w-7xl mx-auto px-6 lg:px-20 py-16 relative z-10">
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
                                <path d="M12.017 0C5.396 0 .029 5.367.029 11.987c0 6.62 5.367 11.987 11.988 11.987s11.987-5.367 11.987-11.987C24.004 5.367 18.637.001 12.017.001zM8.449 16.988c-1.297 0-2.448-.611-3.132-1.551-.684-.94-.684-2.126 0-3.066.684-.94 1.835-1.551 3.132-1.551s2.448.611 3.132 1.551c.684.94.684 2.126 0 3.066-.684.94-1.835 1.551-3.132 1.551zm7.718 0c-1.297 0-2.448-.611-3.132-1.551-.684-.94-.684-2.126 0-3.066.684-.94 1.835-1.551 3.132-1.551s2.448.611 3.132 1.551c.684.94.684 2.126 0 3.066-.684.94-1.835 1.551-3.132 1.551z"/>
                            </svg>
                        </a>
                        <a href="#" class="w-12 h-12 bg-gradient-to-r from-gray-800 to-gray-700 rounded-full flex items-center justify-center hover:from-brand-red hover:to-red-600 transition-all duration-300 shadow-lg">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433c-1.144 0-2.063-.926-2.063-2.065 0-1.138.92-2.063 2.063-2.063 1.14 0 2.064.925 2.064 2.063 0 1.139-.925 2.065-2.064 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z"/>
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
                        <form onsubmit="subscribeNewsletter(event)" class="flex">
                            <input type="email" id="newsletterEmail" placeholder="Your email" required class="flex-1 px-4 py-3 bg-gray-800 border border-gray-700 rounded-l-xl focus:outline-none focus:border-brand-red text-white placeholder-gray-400">
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
        <div class="border-t border-gray-800 relative z-10">
            <div class="max-w-7xl mx-auto px-6 lg:px-20 py-6">
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

    <!-- Modals -->
    
    <!-- Quote Modal -->
    <div id="quoteModal" class="modal">
        <div class="modal-content max-w-2xl">
            <div class="bg-gradient-to-r from-brand-red to-red-600 text-white p-6 rounded-t-3xl -m-8 mb-8">
                <div class="flex justify-between items-center">
                    <div class="flex items-center space-x-4">
                        <div class="w-16 h-16 bg-white/20 rounded-full flex items-center justify-center">
                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                            </svg>
                        </div>
                        <div>
                            <h2 class="text-3xl font-montserrat font-bold">Get Quote</h2>
                            <p class="text-red-100" id="quoteSubtitle">Request pricing for vehicle</p>
                        </div>
                    </div>
                    <button onclick="closeQuoteModal()" class="w-10 h-10 bg-white/20 rounded-full flex items-center justify-center hover:bg-white/30 transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
            </div>

            <!-- Vehicle Info -->
            <div class="p-8 border-b border-gray-100 -mx-8 mb-8">
                <div class="flex items-center space-x-6">
                    <img id="quoteVehicleImage" src="" alt="" class="w-24 h-16 object-cover rounded-xl" style="display: none;">
                    <div>
                        <h3 id="quoteVehicleName" class="text-2xl font-montserrat font-bold text-brand-black"></h3>
                        <p id="quoteVehiclePrice" class="text-xl text-brand-red font-semibold"></p>
                    </div>
                </div>
            </div>

            <!-- Form -->
            <form onsubmit="submitQuote(event)" class="space-y-6">
                <div class="grid md:grid-cols-2 gap-6">
                    <div class="relative">
                        <svg class="absolute left-3 top-1/2 transform -translate-y-1/2 w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                        </svg>
                        <input type="text" id="quoteName" required placeholder="Full Name" class="w-full pl-12 pr-4 py-4 border border-gray-200 rounded-xl focus:ring-2 focus:ring-brand-red focus:border-transparent transition-all duration-300">
                    </div>
                    
                    <div class="relative">
                        <svg class="absolute left-3 top-1/2 transform -translate-y-1/2 w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 4.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                        </svg>
                        <input type="email" id="quoteEmail" required placeholder="Email Address" class="w-full pl-12 pr-4 py-4 border border-gray-200 rounded-xl focus:ring-2 focus:ring-brand-red focus:border-transparent transition-all duration-300">
                    </div>
                </div>

                <div class="grid md:grid-cols-2 gap-6">
                    <div class="relative">
                        <svg class="absolute left-3 top-1/2 transform -translate-y-1/2 w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path>
                        </svg>
                        <input type="tel" id="quotePhone" required placeholder="Phone Number" class="w-full pl-12 pr-4 py-4 border border-gray-200 rounded-xl focus:ring-2 focus:ring-brand-red focus:border-transparent transition-all duration-300">
                    </div>
                    
                    <div class="relative">
                        <svg class="absolute left-3 top-1/2 transform -translate-y-1/2 w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                        </svg>
                        <input type="text" id="quoteLocation" required placeholder="Location/City" class="w-full pl-12 pr-4 py-4 border border-gray-200 rounded-xl focus:ring-2 focus:ring-brand-red focus:border-transparent transition-all duration-300">
                    </div>
                </div>

                <div>
                    <textarea id="quoteMessage" rows="4" placeholder="Additional requirements or questions..." class="w-full px-4 py-4 border border-gray-200 rounded-xl focus:ring-2 focus:ring-brand-red focus:border-transparent transition-all duration-300 resize-none"></textarea>
                </div>

                <button type="submit" class="w-full btn-primary flex items-center justify-center space-x-3 text-lg py-4">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path>
                    </svg>
                    <span>Send Quote Request</span>
                </button>
            </form>
        </div>
    </div>

    <!-- Image Modal -->
    <div id="imageModal" class="modal">
        <div class="modal-content max-w-5xl max-h-[90vh] p-0 bg-black rounded-3xl overflow-hidden">
            <!-- Close Button -->
            <button onclick="closeImageModal()" class="absolute top-6 right-6 w-12 h-12 bg-white/20 rounded-full flex items-center justify-center text-white hover:bg-white/30 transition-colors z-20">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>

            <!-- Image Container -->
            <div class="relative">
                <div id="imageCarousel" class="image-carousel">
                    <div id="carouselContainer" class="carousel-container">
                        <!-- Images will be inserted here -->
                    </div>
                    
                    <!-- Navigation Arrows -->
                    <button id="carouselPrev" class="carousel-nav carousel-prev" onclick="previousImage()">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                        </svg>
                    </button>
                    <button id="carouselNext" class="carousel-nav carousel-next" onclick="nextImage()">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                        </svg>
                    </button>

                    <!-- Image Counter -->
                    <div id="imageCounter" class="absolute bottom-4 left-1/2 transform -translate-x-1/2 bg-black/70 text-white px-4 py-2 rounded-full text-sm font-medium">
                        1 / 1
                    </div>

                    <!-- Vehicle Name -->
                    <div id="imageVehicleName" class="absolute top-4 left-4 bg-black/70 text-white px-4 py-2 rounded-full text-sm font-semibold">
                        Vehicle Name
                    </div>

                    <!-- Indicators -->
                    <div id="carouselIndicators" class="carousel-indicators">
                        <!-- Indicators will be inserted here -->
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Report Issue Modal -->
    <div id="reportModal" class="modal">
        <div class="modal-content max-w-4xl max-h-[90vh] overflow-y-auto">
            <div class="bg-gradient-to-r from-brand-red to-red-600 text-white p-6 rounded-t-3xl -m-8 mb-8">
                <div class="flex justify-between items-center">
                    <div class="flex items-center space-x-4">
                        <div class="w-16 h-16 bg-white/20 rounded-full flex items-center justify-center">
                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                            </svg>
                        </div>
                        <div>
                            <h2 class="text-3xl font-montserrat font-bold">Report Vehicle Issue</h2>
                            <p class="text-red-100">Get professional assistance for your FaZona EV</p>
                        </div>
                    </div>
                    <button onclick="closeReportModal()" class="w-10 h-10 bg-white/20 rounded-full flex items-center justify-center hover:bg-white/30 transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
            </div>

            <form onsubmit="submitReport(event)" class="space-y-8">
                <!-- Customer Information -->
                <div>
                    <h3 class="text-xl font-montserrat font-bold text-brand-black mb-6 flex items-center space-x-2">
                        <svg class="w-6 h-6 text-brand-red" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                        </svg>
                        <span>Customer Information</span>
                    </h3>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                        <div class="relative">
                            <svg class="absolute left-3 top-1/2 transform -translate-y-1/2 w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                            </svg>
                            <input type="text" required placeholder="Full Name" class="w-full pl-12 pr-4 py-4 border border-gray-200 rounded-xl focus:ring-2 focus:ring-brand-red focus:border-transparent transition-all duration-300">
                        </div>
                        
                        <div class="relative">
                            <svg class="absolute left-3 top-1/2 transform -translate-y-1/2 w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 4.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                            </svg>
                            <input type="email" required placeholder="Email Address" class="w-full pl-12 pr-4 py-4 border border-gray-200 rounded-xl focus:ring-2 focus:ring-brand-red focus:border-transparent transition-all duration-300">
                        </div>

                        <div class="relative">
                            <svg class="absolute left-3 top-1/2 transform -translate-y-1/2 w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path>
                            </svg>
                            <input type="tel" required placeholder="Phone Number" class="w-full pl-12 pr-4 py-4 border border-gray-200 rounded-xl focus:ring-2 focus:ring-brand-red focus:border-transparent transition-all duration-300">
                        </div>

                        <div class="relative">
                            <svg class="absolute left-3 top-1/2 transform -translate-y-1/2 w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3a2 2 0 012-2h4a2 2 0 012 2v4m-6 4v10a2 2 0 002 2h4a2 2 0 002-2V11m-6 0h8m-8 0V7a2 2 0 012-2h4a2 2 0 012 2v4"></path>
                            </svg>
                            <select required class="w-full pl-12 pr-4 py-4 border border-gray-200 rounded-xl focus:ring-2 focus:ring-brand-red focus:border-transparent transition-all duration-300 appearance-none bg-white">
                                <option value="">Preferred Contact Time</option>
                                <option value="Morning (8AM - 12PM)">Morning (8AM - 12PM)</option>
                                <option value="Afternoon (12PM - 5PM)">Afternoon (12PM - 5PM)</option>
                                <option value="Evening (5PM - 8PM)">Evening (5PM - 8PM)</option>
                                <option value="Anytime">Anytime</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Vehicle Information -->
                <div>
                    <h3 class="text-xl font-montserrat font-bold text-brand-black mb-6 flex items-center space-x-2">
                        <svg class="w-6 h-6 text-brand-red" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                        </svg>
                        <span>Vehicle Information</span>
                    </h3>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                        <div class="relative">
                            <svg class="absolute left-3 top-1/2 transform -translate-y-1/2 w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                            </svg>
                            <input type="text" required placeholder="Vehicle Model (e.g., FaZona Premium)" class="w-full pl-12 pr-4 py-4 border border-gray-200 rounded-xl focus:ring-2 focus:ring-brand-red focus:border-transparent transition-all duration-300">
                        </div>
                        
                        <div class="relative">
                            <svg class="absolute left-3 top-1/2 transform -translate-y-1/2 w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3a2 2 0 012-2h4a2 2 0 012 2v4m-6 4v10a2 2 0 002 2h4a2 2 0 002-2V11m-6 0h8m-8 0V7a2 2 0 012-2h4a2 2 0 012 2v4"></path>
                            </svg>
                            <input type="text" required placeholder="Year (e.g., 2024)" class="w-full pl-12 pr-4 py-4 border border-gray-200 rounded-xl focus:ring-2 focus:ring-brand-red focus:border-transparent transition-all duration-300">
                        </div>

                        <div class="relative">
                            <input type="text" placeholder="License Plate Number (Optional)" class="w-full px-4 py-4 border border-gray-200 rounded-xl focus:ring-2 focus:ring-brand-red focus:border-transparent transition-all duration-300">
                        </div>

                        <div class="relative">
                            <svg class="absolute left-3 top-1/2 transform -translate-y-1/2 w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                            </svg>
                            <input type="text" required placeholder="Current Location/City" class="w-full pl-12 pr-4 py-4 border border-gray-200 rounded-xl focus:ring-2 focus:ring-brand-red focus:border-transparent transition-all duration-300">
                        </div>
                    </div>
                </div>

                <!-- Issue Details -->
                <div>
                    <h3 class="text-xl font-montserrat font-bold text-brand-black mb-6 flex items-center space-x-2">
                        <svg class="w-6 h-6 text-brand-red" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                        </svg>
                        <span>Issue Details</span>
                    </h3>
                    
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 mb-6">
                        <div>
                            <label class="block text-sm font-semibold text-brand-black mb-2">Issue Type *</label>
                            <select required class="w-full px-4 py-4 border border-gray-200 rounded-xl focus:ring-2 focus:ring-brand-red focus:border-transparent transition-all duration-300 appearance-none bg-white">
                                <option value="">Select Issue Type</option>
                                <option value="Engine/Motor Issues">Engine/Motor Issues</option>
                                <option value="Battery Problems">Battery Problems</option>
                                <option value="Charging Issues">Charging Issues</option>
                                <option value="Brake System">Brake System</option>
                                <option value="Electrical Problems">Electrical Problems</option>
                                <option value="Air Conditioning/Heating">Air Conditioning/Heating</option>
                                <option value="Transmission">Transmission</option>
                                <option value="Suspension">Suspension</option>
                                <option value="Lights/Indicators">Lights/Indicators</option>
                                <option value="Dashboard/Electronics">Dashboard/Electronics</option>
                                <option value="Tire Issues">Tire Issues</option>
                                <option value="Other">Other</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-brand-black mb-2">Urgency Level *</label>
                            <select required class="w-full px-4 py-4 border border-gray-200 rounded-xl focus:ring-2 focus:ring-brand-red focus:border-transparent transition-all duration-300 appearance-none bg-white">
                                <option value="">Select Urgency Level</option>
                                <option value="low">Low - Can wait a few days</option>
                                <option value="medium">Medium - Within 24-48 hours</option>
                                <option value="high">High - Same day service needed</option>
                                <option value="critical">Critical - Vehicle unsafe to drive</option>
                            </select>
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-brand-black mb-2">Detailed Description *</label>
                        <textarea required rows="4" placeholder="Please describe the issue in detail. Include when it started, what symptoms you're experiencing, and any error messages..." class="w-full px-4 py-4 border border-gray-200 rounded-xl focus:ring-2 focus:ring-brand-red focus:border-transparent transition-all duration-300 resize-none"></textarea>
                    </div>
                </div>

                <!-- Submit Button -->
                <button type="submit" class="w-full bg-gradient-to-r from-brand-red to-red-600 hover:from-red-700 hover:to-red-800 text-white flex items-center justify-center space-x-3 text-lg py-4 rounded-xl font-montserrat font-semibold transition-all duration-300">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path>
                    </svg>
                    <span>Send Issue Report</span>
                </button>

                <!-- Help Text -->
                <div class="bg-blue-50 border border-blue-200 rounded-xl p-4 text-sm text-blue-800">
                    <p class="font-semibold mb-2">📞 Emergency Contact:</p>
                    <p class="leading-relaxed">If this is a critical safety issue or emergency, please call us immediately at <strong>+234 913 585 9888</strong> or contact emergency services if needed.</p>
                </div>
            </form>
        </div>
    </div>

    <!-- JavaScript -->
    <script>
        // Global variables
        let currentImageIndex = 0;
        let currentImages = [];
        let currentVehicleName = '';

        // Mobile menu toggle
        function toggleMobileMenu() {
            const menu = document.getElementById('mobileMenu');
            menu.classList.toggle('hidden');
        }

        // Quote modal functions
        function openQuoteModal(vehicleName, price, image) {
            document.getElementById('quoteVehicleName').textContent = vehicleName;
            document.getElementById('quoteVehiclePrice').textContent = price;
            document.getElementById('quoteSubtitle').textContent = `Request pricing for ${vehicleName}`;
            
            const imageEl = document.getElementById('quoteVehicleImage');
            if (image && image !== '') {
                imageEl.src = image;
                imageEl.style.display = 'block';
            } else {
                imageEl.style.display = 'none';
            }
            
            document.getElementById('quoteModal').classList.add('show');
            document.body.style.overflow = 'hidden';
        }

        function closeQuoteModal() {
            document.getElementById('quoteModal').classList.remove('show');
            document.body.style.overflow = 'auto';
        }

        function submitQuote(event) {
            event.preventDefault();
            
            const name = document.getElementById('quoteName').value;
            const email = document.getElementById('quoteEmail').value;
            const phone = document.getElementById('quotePhone').value;
            const location = document.getElementById('quoteLocation').value;
            const message = document.getElementById('quoteMessage').value;
            const vehicleName = document.getElementById('quoteVehicleName').textContent;
            const vehiclePrice = document.getElementById('quoteVehiclePrice').textContent;
            
            const subject = `Quote Request for ${vehicleName}`;
            const body = `Hello FaZona EV Team,

I am interested in getting a quote for the ${vehicleName} (${vehiclePrice}).

Customer Details:
Name: ${name}
Email: ${email}
Phone: ${phone}
Location: ${location}

Message:
${message}

Please provide me with:
- Final pricing details
- Availability and delivery timeline
- Financing options
- Test drive scheduling
- Technical specifications

Thank you for your time.

Best regards,
${name}`;

            const mailtoLink = `mailto:evfazona@gmail.com?subject=${encodeURIComponent(subject)}&body=${encodeURIComponent(body)}`;
            window.location.href = mailtoLink;
            
            closeQuoteModal();
        }

        // Image modal functions
        function openImageModal(vehicleName, images) {
            currentImages = Array.isArray(images) ? images : [images];
            currentVehicleName = vehicleName;
            currentImageIndex = 0;
            
            updateImageModal();
            document.getElementById('imageModal').classList.add('show');
            document.body.style.overflow = 'hidden';
        }

        function closeImageModal() {
            document.getElementById('imageModal').classList.remove('show');
            document.body.style.overflow = 'auto';
        }

        function updateImageModal() {
            const container = document.getElementById('carouselContainer');
            const counter = document.getElementById('imageCounter');
            const vehicleName = document.getElementById('imageVehicleName');
            const indicators = document.getElementById('carouselIndicators');
            
            // Update images
            container.innerHTML = '';
            currentImages.forEach((image, index) => {
                const img = document.createElement('img');
                img.src = image;
                img.alt = `${currentVehicleName} - Image ${index + 1}`;
                img.className = 'carousel-slide';
                container.appendChild(img);
            });
            
            // Update counter
            counter.textContent = `${currentImageIndex + 1} / ${currentImages.length}`;
            
            // Update vehicle name
            vehicleName.textContent = currentVehicleName;
            
            // Update indicators
            indicators.innerHTML = '';
            if (currentImages.length > 1) {
                currentImages.forEach((_, index) => {
                    const indicator = document.createElement('div');
                    indicator.className = `carousel-indicator ${index === currentImageIndex ? 'active' : ''}`;
                    indicator.onclick = () => goToImage(index);
                    indicators.appendChild(indicator);
                });
            }
            
            // Update carousel position
            container.style.transform = `translateX(-${currentImageIndex * 100}%)`;
            
            // Show/hide navigation
            const prevBtn = document.getElementById('carouselPrev');
            const nextBtn = document.getElementById('carouselNext');
            if (currentImages.length > 1) {
                prevBtn.style.display = 'flex';
                nextBtn.style.display = 'flex';
            } else {
                prevBtn.style.display = 'none';
                nextBtn.style.display = 'none';
            }
        }

        function previousImage() {
            currentImageIndex = currentImageIndex > 0 ? currentImageIndex - 1 : currentImages.length - 1;
            updateImageModal();
        }

        function nextImage() {
            currentImageIndex = currentImageIndex < currentImages.length - 1 ? currentImageIndex + 1 : 0;
            updateImageModal();
        }

        function goToImage(index) {
            currentImageIndex = index;
            updateImageModal();
        }

        // Report modal functions
        function openReportModal() {
            document.getElementById('reportModal').classList.add('show');
            document.body.style.overflow = 'hidden';
        }

        function closeReportModal() {
            document.getElementById('reportModal').classList.remove('show');
            document.body.style.overflow = 'auto';
        }

        function submitReport(event) {
            event.preventDefault();
            
            const formData = new FormData(event.target);
            const reportId = Date.now().toString();
            const currentDate = new Date().toLocaleString();
            
            const subject = `🚨 Vehicle Issue Report #${reportId} - ${formData.get('issueType')} (${formData.get('urgencyLevel').toUpperCase()} Priority)`;
            const body = `NEW VEHICLE ISSUE REPORT
========================
Report ID: #${reportId}
Submitted: ${currentDate}

CUSTOMER INFORMATION:
• Name: ${formData.get('customerName')}
• Email: ${formData.get('email')}
• Phone: ${formData.get('phone')}
• Preferred Contact Time: ${formData.get('preferredContactTime')}

VEHICLE DETAILS:
• Model: ${formData.get('vehicleModel')}
• Year: ${formData.get('vehicleYear')}
• License Plate: ${formData.get('licensePlate') || 'Not provided'}
• Current Location: ${formData.get('location')}

ISSUE DETAILS:
• Type: ${formData.get('issueType')}
• Urgency Level: ${formData.get('urgencyLevel').toUpperCase()}
• Description: ${formData.get('description')}

STATUS: PENDING REVIEW

---
This report was submitted through the FaZona EV website.`;

            const mailtoLink = `mailto:evfazona@gmail.com?subject=${encodeURIComponent(subject)}&body=${encodeURIComponent(body)}`;
            window.location.href = mailtoLink;
            
            closeReportModal();
        }

        // Newsletter subscription
        function subscribeNewsletter(event) {
            event.preventDefault();
            
            const email = document.getElementById('newsletterEmail').value;
            const subject = 'Newsletter Subscription Request';
            const body = `Hello FaZona EV Team,

I would like to subscribe to your newsletter to stay updated on:
- New vehicle launches
- Special offers and promotions
- Company news and updates
- Electric vehicle industry insights

Email: ${email}

Thank you!`;

            const mailtoLink = `mailto:evfazona@gmail.com?subject=${encodeURIComponent(subject)}&body=${encodeURIComponent(body)}`;
            window.location.href = mailtoLink;
            
            document.getElementById('newsletterEmail').value = '';
        }

        // Close modals when clicking outside
        document.addEventListener('click', function(event) {
            if (event.target.classList.contains('modal')) {
                if (event.target.id === 'quoteModal') closeQuoteModal();
                if (event.target.id === 'imageModal') closeImageModal();
                if (event.target.id === 'reportModal') closeReportModal();
            }
        });

        // Keyboard navigation for image modal
        document.addEventListener('keydown', function(event) {
            if (document.getElementById('imageModal').classList.contains('show')) {
                if (event.key === 'Escape') closeImageModal();
                if (event.key === 'ArrowLeft') previousImage();
                if (event.key === 'ArrowRight') nextImage();
            }
        });

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
            });
        });

        // Add scroll effect to header
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