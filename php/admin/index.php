<?php
// FaZona EV - Admin Panel (PHP Version)
session_start();

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
    die("Database connection failed: " . $e->getMessage());
}

// Handle login
if ($_POST['action'] ?? '' === 'login') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    
    $stmt = $pdo->prepare("SELECT * FROM admin_users WHERE username = ? OR email = ?");
    $stmt->execute([$username, $username]);
    $user = $stmt->fetch();
    
    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['admin_logged_in'] = true;
        $_SESSION['admin_user'] = $user;
        header('Location: ' . $_SERVER['PHP_SELF']);
        exit;
    } else {
        $login_error = 'Invalid credentials';
    }
}

// Handle logout
if ($_GET['action'] ?? '' === 'logout') {
    session_destroy();
    header('Location: ' . $_SERVER['PHP_SELF']);
    exit;
}

// Check if logged in
$is_logged_in = $_SESSION['admin_logged_in'] ?? false;

// Handle vehicle operations (only if logged in)
if ($is_logged_in) {
    // Handle vehicle creation
    if ($_POST['action'] ?? '' === 'create_vehicle') {
        $name = $_POST['name'] ?? '';
        $price = $_POST['price'] ?? '';
        $range_km = $_POST['range_km'] ?? '';
        $description = $_POST['description'] ?? '';
        $features = $_POST['features'] ?? '';
        $badge = $_POST['badge'] ?? '';
        $badge_color = $_POST['badge_color'] ?? '';
        $rating = (int)($_POST['rating'] ?? 5);
        $is_active = isset($_POST['is_active']) ? 1 : 0;
        
        // Convert features to JSON
        $features_array = array_map('trim', explode(',', $features));
        $features_json = json_encode($features_array);
        
        $stmt = $pdo->prepare("
            INSERT INTO vehicles (name, price, range_km, description, features, badge, badge_color, rating, is_active) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([$name, $price, $range_km, $description, $features_json, $badge, $badge_color, $rating, $is_active]);
        
        $vehicle_id = $pdo->lastInsertId();
        
        // Handle image uploads
        if (isset($_FILES['images'])) {
            $upload_dir = '../uploads/';
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0755, true);
            }
            
            foreach ($_FILES['images']['tmp_name'] as $key => $tmp_name) {
                if ($_FILES['images']['error'][$key] === UPLOAD_ERR_OK) {
                    $file_name = 'vehicle-' . time() . '-' . rand(100000, 999999) . '.' . pathinfo($_FILES['images']['name'][$key], PATHINFO_EXTENSION);
                    $file_path = $upload_dir . $file_name;
                    
                    if (move_uploaded_file($tmp_name, $file_path)) {
                        $image_url = '/uploads/' . $file_name;
                        $is_primary = $key === 0 ? 1 : 0; // First image is primary
                        
                        $stmt = $pdo->prepare("INSERT INTO vehicle_images (vehicle_id, image_url, is_primary) VALUES (?, ?, ?)");
                        $stmt->execute([$vehicle_id, $image_url, $is_primary]);
                    }
                }
            }
        }
        
        $success_message = 'Vehicle created successfully!';
    }
    
    // Handle vehicle update
    if ($_POST['action'] ?? '' === 'update_vehicle') {
        $vehicle_id = (int)($_POST['vehicle_id'] ?? 0);
        $name = $_POST['name'] ?? '';
        $price = $_POST['price'] ?? '';
        $range_km = $_POST['range_km'] ?? '';
        $description = $_POST['description'] ?? '';
        $features = $_POST['features'] ?? '';
        $badge = $_POST['badge'] ?? '';
        $badge_color = $_POST['badge_color'] ?? '';
        $rating = (int)($_POST['rating'] ?? 5);
        $is_active = isset($_POST['is_active']) ? 1 : 0;
        
        // Convert features to JSON
        $features_array = array_map('trim', explode(',', $features));
        $features_json = json_encode($features_array);
        
        $stmt = $pdo->prepare("
            UPDATE vehicles 
            SET name = ?, price = ?, range_km = ?, description = ?, features = ?, badge = ?, badge_color = ?, rating = ?, is_active = ?
            WHERE id = ?
        ");
        $stmt->execute([$name, $price, $range_km, $description, $features_json, $badge, $badge_color, $rating, $is_active, $vehicle_id]);
        
        // Handle new image uploads
        if (isset($_FILES['images'])) {
            $upload_dir = '../uploads/';
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0755, true);
            }
            
            foreach ($_FILES['images']['tmp_name'] as $key => $tmp_name) {
                if ($_FILES['images']['error'][$key] === UPLOAD_ERR_OK) {
                    $file_name = 'vehicle-' . time() . '-' . rand(100000, 999999) . '.' . pathinfo($_FILES['images']['name'][$key], PATHINFO_EXTENSION);
                    $file_path = $upload_dir . $file_name;
                    
                    if (move_uploaded_file($tmp_name, $file_path)) {
                        $image_url = '/uploads/' . $file_name;
                        
                        // Check if this vehicle has any existing images
                        $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM vehicle_images WHERE vehicle_id = ?");
                        $stmt->execute([$vehicle_id]);
                        $existing_count = $stmt->fetch()['count'];
                        
                        $is_primary = ($existing_count == 0 && $key === 0) ? 1 : 0; // First image is primary if no existing images
                        
                        $stmt = $pdo->prepare("INSERT INTO vehicle_images (vehicle_id, image_url, is_primary) VALUES (?, ?, ?)");
                        $stmt->execute([$vehicle_id, $image_url, $is_primary]);
                    }
                }
            }
        }
        
        $success_message = 'Vehicle updated successfully!';
    }
    
    // Handle vehicle deletion
    if ($_POST['action'] ?? '' === 'delete_vehicle') {
        $vehicle_id = (int)($_POST['vehicle_id'] ?? 0);
        
        // Get images to delete files
        $stmt = $pdo->prepare("SELECT image_url FROM vehicle_images WHERE vehicle_id = ?");
        $stmt->execute([$vehicle_id]);
        $images = $stmt->fetchAll();
        
        // Delete image files
        foreach ($images as $image) {
            $file_path = '..' . $image['image_url'];
            if (file_exists($file_path)) {
                unlink($file_path);
            }
        }
        
        // Delete vehicle (images will be deleted by CASCADE)
        $stmt = $pdo->prepare("DELETE FROM vehicles WHERE id = ?");
        $stmt->execute([$vehicle_id]);
        
        $success_message = 'Vehicle deleted successfully!';
    }
    
    // Handle vehicle status toggle
    if ($_POST['action'] ?? '' === 'toggle_status') {
        $vehicle_id = (int)($_POST['vehicle_id'] ?? 0);
        $stmt = $pdo->prepare("UPDATE vehicles SET is_active = NOT is_active WHERE id = ?");
        $stmt->execute([$vehicle_id]);
        
        $success_message = 'Vehicle status updated!';
    }
    
    // Handle image deletion
    if ($_POST['action'] ?? '' === 'delete_image') {
        $image_id = (int)($_POST['image_id'] ?? 0);
        $vehicle_id = (int)($_POST['vehicle_id'] ?? 0);
        
        // Get image info
        $stmt = $pdo->prepare("SELECT image_url, is_primary FROM vehicle_images WHERE id = ?");
        $stmt->execute([$image_id]);
        $image = $stmt->fetch();
        
        if ($image) {
            // Delete file
            $file_path = '..' . $image['image_url'];
            if (file_exists($file_path)) {
                unlink($file_path);
            }
            
            // Delete from database
            $stmt = $pdo->prepare("DELETE FROM vehicle_images WHERE id = ?");
            $stmt->execute([$image_id]);
            
            // If this was the primary image, set another image as primary
            if ($image['is_primary']) {
                $stmt = $pdo->prepare("SELECT id FROM vehicle_images WHERE vehicle_id = ? ORDER BY created_at ASC LIMIT 1");
                $stmt->execute([$vehicle_id]);
                $next_image = $stmt->fetch();
                
                if ($next_image) {
                    $stmt = $pdo->prepare("UPDATE vehicle_images SET is_primary = 1 WHERE id = ?");
                    $stmt->execute([$next_image['id']]);
                }
            }
        }
        
        $success_message = 'Image deleted successfully!';
    }
    
    // Fetch all vehicles for admin
    $stmt = $pdo->query("
        SELECT v.*, 
               GROUP_CONCAT(vi.image_url ORDER BY vi.is_primary DESC, vi.created_at ASC) as images,
               (SELECT vi2.image_url FROM vehicle_images vi2 WHERE vi2.vehicle_id = v.id AND vi2.is_primary = true LIMIT 1) as primary_image
        FROM vehicles v
        LEFT JOIN vehicle_images vi ON v.id = vi.vehicle_id
        GROUP BY v.id
        ORDER BY v.created_at DESC
    ");
    $vehicles = $stmt->fetchAll();
    
    // Format vehicles data
    foreach ($vehicles as &$vehicle) {
        $vehicle['features'] = json_decode($vehicle['features'], true) ?: [];
        $vehicle['images'] = $vehicle['images'] ? explode(',', $vehicle['images']) : [];
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FaZona EV - Admin Panel</title>
    
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
            padding: 0.75rem 1.5rem;
            border-radius: 0.75rem;
            font-family: 'Montserrat', sans-serif;
            font-weight: 600;
            transition: all 0.3s;
            border: none;
            cursor: pointer;
        }
        
        .btn-primary:hover {
            background: linear-gradient(to right, #b8001a, #9a0016);
            transform: scale(1.05);
        }
        
        .btn-secondary {
            border: 2px solid #D6001C;
            color: #D6001C;
            padding: 0.75rem 1.5rem;
            border-radius: 0.75rem;
            font-family: 'Montserrat', sans-serif;
            font-weight: 600;
            transition: all 0.3s;
            background: transparent;
            cursor: pointer;
        }
        
        .btn-secondary:hover {
            background: #D6001C;
            color: white;
        }
        
        .card-hover {
            transition: all 0.5s;
        }
        
        .card-hover:hover {
            transform: translateY(-8px);
            box-shadow: 0 25px 50px rgba(0, 0, 0, 0.15);
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
    </style>
</head>
<body>
    <?php if (!$is_logged_in): ?>
    <!-- Login Page -->
    <div class="min-h-screen bg-gradient-to-br from-brand-red to-red-800 flex items-center justify-center p-4">
        <div class="bg-white rounded-3xl shadow-2xl p-8 w-full max-w-md">
            <!-- Header -->
            <div class="text-center mb-8">
                <div class="w-20 h-20 bg-gradient-to-r from-brand-red to-red-600 rounded-full flex items-center justify-center mx-auto mb-4">
                    <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                    </svg>
                </div>
                <h1 class="text-3xl font-montserrat font-bold text-brand-black mb-2">Admin Login</h1>
                <p class="text-gray-600">FaZona EV Management Panel</p>
            </div>

            <!-- Error Message -->
            <?php if (isset($login_error)): ?>
            <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-xl mb-6">
                <?= htmlspecialchars($login_error) ?>
            </div>
            <?php endif; ?>

            <!-- Login Form -->
            <form method="POST" class="space-y-6">
                <input type="hidden" name="action" value="login">
                
                <div class="relative">
                    <svg class="absolute left-3 top-1/2 transform -translate-y-1/2 w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                    </svg>
                    <input type="text" name="username" required placeholder="Username or Email" class="w-full pl-12 pr-4 py-4 border border-gray-200 rounded-xl focus:ring-2 focus:ring-brand-red focus:border-transparent transition-all duration-300">
                </div>

                <div class="relative">
                    <svg class="absolute left-3 top-1/2 transform -translate-y-1/2 w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                    </svg>
                    <input type="password" name="password" required placeholder="Password" class="w-full pl-12 pr-4 py-4 border border-gray-200 rounded-xl focus:ring-2 focus:ring-brand-red focus:border-transparent transition-all duration-300">
                </div>

                <button type="submit" class="w-full btn-primary flex items-center justify-center space-x-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"></path>
                    </svg>
                    <span>Login</span>
                </button>
            </form>

            <!-- Default Credentials Info -->
            <div class="mt-8 p-4 bg-gray-50 rounded-xl">
                <p class="text-sm text-gray-600 text-center">
                    <strong>Default Credentials:</strong><br>
                    Username: admin<br>
                    Password: admin123
                </p>
            </div>
        </div>
    </div>

    <?php else: ?>
    <!-- Admin Dashboard -->
    <div class="min-h-screen bg-gray-50">
        <!-- Header -->
        <header class="bg-white shadow-sm border-b">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between items-center h-16">
                    <div class="flex items-center space-x-4">
                        <svg class="w-8 h-8 text-brand-red" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                        </svg>
                        <h1 class="text-2xl font-montserrat font-bold text-brand-black">FaZona EV Admin</h1>
                    </div>
                    <div class="flex items-center space-x-4">
                        <span class="text-gray-600">Welcome, <?= htmlspecialchars($_SESSION['admin_user']['username']) ?></span>
                        <a href="?action=logout" class="flex items-center space-x-2 text-gray-600 hover:text-brand-red transition-colors">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                            </svg>
                            <span>Logout</span>
                        </a>
                    </div>
                </div>
            </div>
        </header>

        <!-- Main Content -->
        <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <!-- Success Message -->
            <?php if (isset($success_message)): ?>
            <div class="mb-8 bg-green-50 border border-green-200 text-green-800 px-6 py-4 rounded-xl">
                <div class="flex items-center space-x-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <span class="font-semibold"><?= htmlspecialchars($success_message) ?></span>
                </div>
            </div>
            <?php endif; ?>

            <!-- Actions Bar -->
            <div class="flex justify-between items-center mb-8">
                <h2 class="text-3xl font-montserrat font-bold text-brand-black">Vehicle Management</h2>
                <button onclick="openAddModal()" class="btn-primary flex items-center space-x-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                    </svg>
                    <span>Add Vehicle</span>
                </button>
            </div>

            <!-- Vehicles Grid -->
            <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-6">
                <?php foreach ($vehicles as $vehicle): ?>
                <div class="bg-white rounded-2xl shadow-lg overflow-hidden card-hover">
                    <!-- Vehicle Image -->
                    <div class="relative h-48">
                        <?php if ($vehicle['primary_image']): ?>
                        <img src="<?= htmlspecialchars($vehicle['primary_image']) ?>" alt="<?= htmlspecialchars($vehicle['name']) ?>" class="w-full h-full object-cover">
                        <?php else: ?>
                        <div class="w-full h-full bg-gray-200 flex items-center justify-center">
                            <svg class="w-12 h-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                            </svg>
                        </div>
                        <?php endif; ?>
                        
                        <!-- Status Badge -->
                        <div class="absolute top-3 left-3">
                            <span class="px-3 py-1 rounded-full text-xs font-semibold <?= $vehicle['is_active'] ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' ?>">
                                <?= $vehicle['is_active'] ? 'Active' : 'Inactive' ?>
                            </span>
                        </div>

                        <!-- Badge -->
                        <?php if ($vehicle['badge']): ?>
                        <div class="absolute top-3 right-3">
                            <span class="px-3 py-1 rounded-full text-xs font-semibold text-white <?= htmlspecialchars($vehicle['badge_color'] ?: 'bg-brand-red') ?>">
                                <?= htmlspecialchars($vehicle['badge']) ?>
                            </span>
                        </div>
                        <?php endif; ?>

                        <!-- Image Count -->
                        <?php if (count($vehicle['images']) > 0): ?>
                        <div class="absolute bottom-3 right-3 bg-black/50 text-white px-2 py-1 rounded-full text-xs flex items-center space-x-1">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"></path>
                            </svg>
                            <span><?= count($vehicle['images']) ?></span>
                        </div>
                        <?php endif; ?>
                    </div>

                    <!-- Vehicle Info -->
                    <div class="p-6">
                        <div class="flex justify-between items-start mb-2">
                            <h3 class="text-xl font-montserrat font-bold text-brand-black">
                                <?= htmlspecialchars($vehicle['name']) ?>
                            </h3>
                            <div class="flex items-center space-x-1">
                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                <svg class="w-4 h-4 <?= $i <= $vehicle['rating'] ? 'text-yellow-400 fill-current' : 'text-gray-300' ?>" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path>
                                </svg>
                                <?php endfor; ?>
                            </div>
                        </div>

                        <p class="text-2xl font-bold text-brand-red mb-2"><?= htmlspecialchars($vehicle['price']) ?></p>
                        <p class="text-gray-600 mb-4"><?= htmlspecialchars($vehicle['range_km']) ?></p>

                        <!-- Features -->
                        <div class="flex flex-wrap gap-2 mb-4">
                            <?php foreach (array_slice($vehicle['features'], 0, 2) as $feature): ?>
                            <span class="px-2 py-1 bg-gray-100 text-gray-700 text-xs rounded-full">
                                <?= htmlspecialchars($feature) ?>
                            </span>
                            <?php endforeach; ?>
                            <?php if (count($vehicle['features']) > 2): ?>
                            <span class="px-2 py-1 bg-gray-100 text-gray-700 text-xs rounded-full">
                                +<?= count($vehicle['features']) - 2 ?> more
                            </span>
                            <?php endif; ?>
                        </div>

                        <!-- Actions -->
                        <div class="flex space-x-2 mb-3">
                            <button onclick="openEditModal(<?= htmlspecialchars(json_encode($vehicle)) ?>)" class="flex-1 bg-blue-500 text-white px-4 py-2 rounded-lg hover:bg-blue-600 transition-colors flex items-center justify-center space-x-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                </svg>
                                <span>Edit</span>
                            </button>
                            <form method="POST" class="flex-1">
                                <input type="hidden" name="action" value="toggle_status">
                                <input type="hidden" name="vehicle_id" value="<?= $vehicle['id'] ?>">
                                <button type="submit" class="w-full <?= $vehicle['is_active'] ? 'bg-yellow-500 hover:bg-yellow-600' : 'bg-green-500 hover:bg-green-600' ?> text-white px-4 py-2 rounded-lg transition-colors flex items-center justify-center space-x-2">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="<?= $vehicle['is_active'] ? 'M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.878 9.878L3 3m6.878 6.878L21 21' : 'M15 12a3 3 0 11-6 0 3 3 0 016 0z M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z' ?>"></path>
                                    </svg>
                                    <span><?= $vehicle['is_active'] ? 'Hide' : 'Show' ?></span>
                                </button>
                            </form>
                        </div>
                        
                        <div class="flex space-x-2">
                            <button onclick="openImagesModal(<?= $vehicle['id'] ?>, '<?= htmlspecialchars($vehicle['name']) ?>', <?= htmlspecialchars(json_encode($vehicle['images'])) ?>)" class="flex-1 bg-purple-500 text-white px-4 py-2 rounded-lg hover:bg-purple-600 transition-colors flex items-center justify-center space-x-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                </svg>
                                <span>Images</span>
                            </button>
                            <form method="POST" class="flex-1" onsubmit="return confirm('Are you sure you want to delete this vehicle?')">
                                <input type="hidden" name="action" value="delete_vehicle">
                                <input type="hidden" name="vehicle_id" value="<?= $vehicle['id'] ?>">
                                <button type="submit" class="w-full bg-red-500 text-white px-4 py-2 rounded-lg hover:bg-red-600 transition-colors flex items-center justify-center space-x-2">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                    </svg>
                                    <span>Delete</span>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </main>
    </div>

    <!-- Add Vehicle Modal -->
    <div id="addModal" class="modal">
        <div class="modal-content max-w-4xl">
            <div class="bg-gradient-to-r from-brand-red to-red-600 text-white p-6 rounded-t-3xl -m-8 mb-8">
                <div class="flex justify-between items-center">
                    <h2 class="text-2xl font-montserrat font-bold">Add New Vehicle</h2>
                    <button onclick="closeAddModal()" class="w-10 h-10 bg-white/20 rounded-full flex items-center justify-center hover:bg-white/30 transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
            </div>

            <form method="POST" enctype="multipart/form-data" class="space-y-6">
                <input type="hidden" name="action" value="create_vehicle">
                
                <div class="grid md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-semibold text-brand-black mb-2">Vehicle Name *</label>
                        <input type="text" name="name" required class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-brand-red focus:border-transparent" placeholder="e.g., Premium Long Range">
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-brand-black mb-2">Price *</label>
                        <input type="text" name="price" required class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-brand-red focus:border-transparent" placeholder="e.g., ₦20 million">
                    </div>
                </div>

                <div class="grid md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-semibold text-brand-black mb-2">Range *</label>
                        <input type="text" name="range_km" required class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-brand-red focus:border-transparent" placeholder="e.g., 200km per charge">
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-brand-black mb-2">Rating</label>
                        <select name="rating" class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-brand-red focus:border-transparent">
                            <option value="5">5 Stars</option>
                            <option value="4">4 Stars</option>
                            <option value="3">3 Stars</option>
                            <option value="2">2 Stars</option>
                            <option value="1">1 Star</option>
                        </select>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-brand-black mb-2">Description</label>
                    <textarea name="description" rows="3" class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-brand-red focus:border-transparent resize-none" placeholder="Vehicle description..."></textarea>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-brand-black mb-2">Features (comma separated)</label>
                    <input type="text" name="features" class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-brand-red focus:border-transparent" placeholder="e.g., Fast Charging, Premium Interior, Advanced Safety">
                </div>

                <div class="grid md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-semibold text-brand-black mb-2">Badge Text</label>
                        <input type="text" name="badge" class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-brand-red focus:border-transparent" placeholder="e.g., Most Popular, Best Value">
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-brand-black mb-2">Badge Color</label>
                        <select name="badge_color" class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-brand-red focus:border-transparent">
                            <option value="">Select Color</option>
                            <option value="bg-brand-red">Brand Red</option>
                            <option value="bg-green-500">Green</option>
                            <option value="bg-blue-500">Blue</option>
                            <option value="bg-yellow-500">Yellow</option>
                            <option value="bg-purple-500">Purple</option>
                        </select>
                    </div>
                </div>

                <div class="flex items-center space-x-3">
                    <input type="checkbox" id="is_active" name="is_active" checked class="w-5 h-5 text-brand-red border-gray-300 rounded focus:ring-brand-red">
                    <label for="is_active" class="text-sm font-semibold text-brand-black">Active (visible on website)</label>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-brand-black mb-2">Vehicle Images</label>
                    <div class="border-2 border-dashed border-gray-300 rounded-xl p-6 text-center">
                        <input type="file" name="images[]" multiple accept="image/*" class="hidden" id="image-upload">
                        <label for="image-upload" class="cursor-pointer flex flex-col items-center space-y-2">
                            <svg class="w-12 h-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
                            </svg>
                            <span class="text-gray-600">Click to upload images</span>
                            <span class="text-sm text-gray-500">PNG, JPG up to 5MB each • Multiple files supported</span>
                        </label>
                    </div>
                </div>

                <button type="submit" class="w-full btn-primary flex items-center justify-center space-x-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <span>Create Vehicle</span>
                </button>
            </form>
        </div>
    </div>

    <!-- Edit Vehicle Modal -->
    <div id="editModal" class="modal">
        <div class="modal-content max-w-4xl">
            <div class="bg-gradient-to-r from-blue-500 to-blue-600 text-white p-6 rounded-t-3xl -m-8 mb-8">
                <div class="flex justify-between items-center">
                    <h2 class="text-2xl font-montserrat font-bold">Edit Vehicle</h2>
                    <button onclick="closeEditModal()" class="w-10 h-10 bg-white/20 rounded-full flex items-center justify-center hover:bg-white/30 transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
            </div>

            <form method="POST" enctype="multipart/form-data" class="space-y-6">
                <input type="hidden" name="action" value="update_vehicle">
                <input type="hidden" name="vehicle_id" id="edit_vehicle_id">
                
                <div class="grid md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-semibold text-brand-black mb-2">Vehicle Name *</label>
                        <input type="text" name="name" id="edit_name" required class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-brand-red focus:border-transparent">
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-brand-black mb-2">Price *</label>
                        <input type="text" name="price" id="edit_price" required class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-brand-red focus:border-transparent">
                    </div>
                </div>

                <div class="grid md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-semibold text-brand-black mb-2">Range *</label>
                        <input type="text" name="range_km" id="edit_range_km" required class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-brand-red focus:border-transparent">
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-brand-black mb-2">Rating</label>
                        <select name="rating" id="edit_rating" class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-brand-red focus:border-transparent">
                            <option value="5">5 Stars</option>
                            <option value="4">4 Stars</option>
                            <option value="3">3 Stars</option>
                            <option value="2">2 Stars</option>
                            <option value="1">1 Star</option>
                        </select>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-brand-black mb-2">Description</label>
                    <textarea name="description" id="edit_description" rows="3" class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-brand-red focus:border-transparent resize-none"></textarea>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-brand-black mb-2">Features (comma separated)</label>
                    <input type="text" name="features" id="edit_features" class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-brand-red focus:border-transparent">
                </div>

                <div class="grid md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-semibold text-brand-black mb-2">Badge Text</label>
                        <input type="text" name="badge" id="edit_badge" class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-brand-red focus:border-transparent">
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-brand-black mb-2">Badge Color</label>
                        <select name="badge_color" id="edit_badge_color" class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-brand-red focus:border-transparent">
                            <option value="">Select Color</option>
                            <option value="bg-brand-red">Brand Red</option>
                            <option value="bg-green-500">Green</option>
                            <option value="bg-blue-500">Blue</option>
                            <option value="bg-yellow-500">Yellow</option>
                            <option value="bg-purple-500">Purple</option>
                        </select>
                    </div>
                </div>

                <div class="flex items-center space-x-3">
                    <input type="checkbox" id="edit_is_active" name="is_active" class="w-5 h-5 text-brand-red border-gray-300 rounded focus:ring-brand-red">
                    <label for="edit_is_active" class="text-sm font-semibold text-brand-black">Active (visible on website)</label>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-brand-black mb-2">Add New Images</label>
                    <div class="border-2 border-dashed border-gray-300 rounded-xl p-6 text-center">
                        <input type="file" name="images[]" multiple accept="image/*" class="hidden" id="edit-image-upload">
                        <label for="edit-image-upload" class="cursor-pointer flex flex-col items-center space-y-2">
                            <svg class="w-12 h-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
                            </svg>
                            <span class="text-gray-600">Click to upload additional images</span>
                            <span class="text-sm text-gray-500">PNG, JPG up to 5MB each • Multiple files supported</span>
                        </label>
                    </div>
                </div>

                <button type="submit" class="w-full bg-blue-500 hover:bg-blue-600 text-white px-6 py-4 rounded-xl font-montserrat font-semibold transition-all duration-300 flex items-center justify-center space-x-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <span>Update Vehicle</span>
                </button>
            </form>
        </div>
    </div>

    <!-- Images Modal -->
    <div id="imagesModal" class="modal">
        <div class="modal-content max-w-4xl">
            <div class="bg-gradient-to-r from-purple-500 to-purple-600 text-white p-6 rounded-t-3xl -m-8 mb-8">
                <div class="flex justify-between items-center">
                    <h2 class="text-2xl font-montserrat font-bold">Manage Images</h2>
                    <button onclick="closeImagesModal()" class="w-10 h-10 bg-white/20 rounded-full flex items-center justify-center hover:bg-white/30 transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
            </div>

            <div id="imagesContent">
                <!-- Images will be loaded here -->
            </div>
        </div>
    </div>

    <script>
        function openAddModal() {
            document.getElementById('addModal').classList.add('show');
            document.body.style.overflow = 'hidden';
        }

        function closeAddModal() {
            document.getElementById('addModal').classList.remove('show');
            document.body.style.overflow = 'auto';
        }

        function openEditModal(vehicle) {
            document.getElementById('edit_vehicle_id').value = vehicle.id;
            document.getElementById('edit_name').value = vehicle.name;
            document.getElementById('edit_price').value = vehicle.price;
            document.getElementById('edit_range_km').value = vehicle.range_km;
            document.getElementById('edit_description').value = vehicle.description || '';
            document.getElementById('edit_features').value = vehicle.features.join(', ');
            document.getElementById('edit_badge').value = vehicle.badge || '';
            document.getElementById('edit_badge_color').value = vehicle.badge_color || '';
            document.getElementById('edit_rating').value = vehicle.rating;
            document.getElementById('edit_is_active').checked = vehicle.is_active == 1;
            
            document.getElementById('editModal').classList.add('show');
            document.body.style.overflow = 'hidden';
        }

        function closeEditModal() {
            document.getElementById('editModal').classList.remove('show');
            document.body.style.overflow = 'auto';
        }

        function openImagesModal(vehicleId, vehicleName, images) {
            const content = document.getElementById('imagesContent');
            
            let html = `<h3 class="text-xl font-bold mb-4">Images for: ${vehicleName}</h3>`;
            
            if (images.length > 0) {
                html += '<div class="grid grid-cols-2 md:grid-cols-3 gap-4">';
                images.forEach((image, index) => {
                    html += `
                        <div class="relative group">
                            <img src="${image}" alt="Vehicle Image" class="w-full h-32 object-cover rounded-lg">
                            <form method="POST" class="absolute top-2 right-2 opacity-0 group-hover:opacity-100 transition-opacity">
                                <input type="hidden" name="action" value="delete_image">
                                <input type="hidden" name="vehicle_id" value="${vehicleId}">
                                <input type="hidden" name="image_id" value="${index + 1}">
                                <button type="submit" onclick="return confirm('Delete this image?')" class="bg-red-500 text-white p-1 rounded-full hover:bg-red-600">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                    </svg>
                                </button>
                            </form>
                        </div>
                    `;
                });
                html += '</div>';
            } else {
                html += '<p class="text-gray-500 text-center py-8">No images uploaded for this vehicle.</p>';
            }
            
            content.innerHTML = html;
            document.getElementById('imagesModal').classList.add('show');
            document.body.style.overflow = 'hidden';
        }

        function closeImagesModal() {
            document.getElementById('imagesModal').classList.remove('show');
            document.body.style.overflow = 'auto';
        }

        // Close modals when clicking outside
        document.addEventListener('click', function(event) {
            if (event.target.classList.contains('modal')) {
                closeAddModal();
                closeEditModal();
                closeImagesModal();
            }
        });

        // File upload preview
        document.getElementById('image-upload').addEventListener('change', function(e) {
            const files = e.target.files;
            if (files.length > 0) {
                const label = e.target.nextElementSibling;
                label.querySelector('span').textContent = `${files.length} file(s) selected`;
            }
        });

        document.getElementById('edit-image-upload').addEventListener('change', function(e) {
            const files = e.target.files;
            if (files.length > 0) {
                const label = e.target.nextElementSibling;
                label.querySelector('span').textContent = `${files.length} new file(s) selected`;
            }
        });
    </script>
    <?php endif; ?>
</body>
</html>