-- FaZona EV Database Setup for PHP Version
-- Run this in your MySQL database

-- Create database
CREATE DATABASE IF NOT EXISTS fazona_ev;
USE fazona_ev;

-- Create admin_users table
CREATE TABLE IF NOT EXISTS admin_users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Create vehicles table
CREATE TABLE IF NOT EXISTS vehicles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    price VARCHAR(50) NOT NULL,
    range_km VARCHAR(50) NOT NULL,
    description TEXT,
    features JSON,
    badge VARCHAR(50),
    badge_color VARCHAR(50) DEFAULT 'bg-brand-red',
    rating INT DEFAULT 5,
    is_active BOOLEAN DEFAULT true,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Create vehicle_images table
CREATE TABLE IF NOT EXISTS vehicle_images (
    id INT AUTO_INCREMENT PRIMARY KEY,
    vehicle_id INT NOT NULL,
    image_url VARCHAR(255) NOT NULL,
    is_primary BOOLEAN DEFAULT false,
    alt_text VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (vehicle_id) REFERENCES vehicles(id) ON DELETE CASCADE
);

-- Insert default admin user (password: admin123)
INSERT INTO admin_users (username, email, password) VALUES 
('admin', 'admin@fazonaev.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi')
ON DUPLICATE KEY UPDATE username = username;

-- Insert sample vehicles
INSERT INTO vehicles (name, price, range_km, description, features, badge, badge_color, rating, is_active) VALUES 
(
    'Premium Long Range',
    '₦20 million',
    '200km per charge',
    'Our flagship electric vehicle with premium features and extended range for long-distance travel.',
    '["Fast Charging", "Premium Interior", "Advanced Safety", "Government Duty Inclusive"]',
    'Most Popular',
    'bg-brand-red',
    5,
    true
),
(
    'Mid-Range Model',
    '₦12 million',
    '150km per charge',
    'Perfect balance of performance and affordability for urban and suburban driving.',
    '["Smart Dashboard", "Regenerative Braking", "Fast Charging", "Eco Mode"]',
    'Best Value',
    'bg-green-500',
    5,
    true
),
(
    'Standard Range',
    '₦9.5 million',
    '100km per charge',
    'Ideal for city driving with essential features and reliable performance.',
    '["Digital Display", "Energy Recovery", "Compact Design", "City Optimized"]',
    NULL,
    NULL,
    4,
    true
),
(
    'Compact Entry',
    '₦6.5 million',
    '80km per charge',
    'Affordable entry-level electric vehicle perfect for first-time EV buyers.',
    '["Essential Features", "Urban Mobility", "Affordable Entry", "Efficient Design"]',
    'Entry Level',
    'bg-blue-500',
    4,
    true
)
ON DUPLICATE KEY UPDATE name = name;