<?php
// FaZona EV - Database Configuration (PHP Version)

// Database configuration
$config = [
    'host' => 'localhost',
    'dbname' => 'fazona_ev',
    'username' => 'root',
    'password' => '',
    'charset' => 'utf8mb4'
];

// Create PDO connection
function getDatabase() {
    global $config;
    
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
        return $pdo;
    } catch (PDOException $e) {
        // In production, log this error instead of displaying it
        die("Database connection failed. Please check your configuration.");
    }
}

// Site configuration
define('SITE_NAME', 'FaZona EV');
define('SITE_URL', 'https://fazona.org');
define('ADMIN_EMAIL', 'evfazona@gmail.com');
define('UPLOAD_DIR', 'uploads/');
define('MAX_FILE_SIZE', 5 * 1024 * 1024); // 5MB

// Create uploads directory if it doesn't exist
if (!is_dir(UPLOAD_DIR)) {
    mkdir(UPLOAD_DIR, 0755, true);
}
?>