-- FaZona EV Database Setup
-- Complete SQL script for creating the database and tables

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
    rating INT DEFAULT 5 CHECK (rating >= 1 AND rating <= 5),
    is_active BOOLEAN DEFAULT true,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    -- Indexes for better performance
    INDEX idx_is_active (is_active),
    INDEX idx_created_at (created_at),
    INDEX idx_name (name)
);

-- Create vehicle_images table
CREATE TABLE IF NOT EXISTS vehicle_images (
    id INT AUTO_INCREMENT PRIMARY KEY,
    vehicle_id INT NOT NULL,
    image_url VARCHAR(255) NOT NULL,
    is_primary BOOLEAN DEFAULT false,
    alt_text VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    -- Foreign key constraint
    FOREIGN KEY (vehicle_id) REFERENCES vehicles(id) ON DELETE CASCADE,
    
    -- Indexes
    INDEX idx_vehicle_id (vehicle_id),
    INDEX idx_is_primary (is_primary)
);

-- Insert default admin user
-- Password: admin123 (hashed with bcrypt)
INSERT INTO admin_users (username, email, password) VALUES 
('admin', 'admin@fazonaev.com', '$2a$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi')
ON DUPLICATE KEY UPDATE username = username;

-- Insert sample vehicles (optional)
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

-- Create a view for active vehicles with primary images
CREATE OR REPLACE VIEW active_vehicles_view AS
SELECT 
    v.*,
    vi.image_url as primary_image,
    (SELECT COUNT(*) FROM vehicle_images WHERE vehicle_id = v.id) as image_count
FROM vehicles v
LEFT JOIN vehicle_images vi ON v.id = vi.vehicle_id AND vi.is_primary = true
WHERE v.is_active = true
ORDER BY v.created_at DESC;

-- Create a stored procedure for getting vehicle with all images
DELIMITER //
CREATE PROCEDURE GetVehicleWithImages(IN vehicle_id INT)
BEGIN
    SELECT 
        v.*,
        GROUP_CONCAT(vi.image_url ORDER BY vi.is_primary DESC, vi.created_at ASC) as all_images
    FROM vehicles v
    LEFT JOIN vehicle_images vi ON v.id = vi.vehicle_id
    WHERE v.id = vehicle_id
    GROUP BY v.id;
END //
DELIMITER ;

-- Create triggers for maintaining data integrity

-- Trigger to ensure only one primary image per vehicle
DELIMITER //
CREATE TRIGGER before_vehicle_image_insert
BEFORE INSERT ON vehicle_images
FOR EACH ROW
BEGIN
    IF NEW.is_primary = true THEN
        UPDATE vehicle_images 
        SET is_primary = false 
        WHERE vehicle_id = NEW.vehicle_id AND is_primary = true;
    END IF;
END //
DELIMITER ;

DELIMITER //
CREATE TRIGGER before_vehicle_image_update
BEFORE UPDATE ON vehicle_images
FOR EACH ROW
BEGIN
    IF NEW.is_primary = true AND OLD.is_primary = false THEN
        UPDATE vehicle_images 
        SET is_primary = false 
        WHERE vehicle_id = NEW.vehicle_id AND is_primary = true AND id != NEW.id;
    END IF;
END //
DELIMITER ;

-- Create indexes for better query performance
CREATE INDEX idx_vehicles_active_created ON vehicles(is_active, created_at DESC);
CREATE INDEX idx_vehicle_images_vehicle_primary ON vehicle_images(vehicle_id, is_primary);

-- Show table structure
DESCRIBE admin_users;
DESCRIBE vehicles;
DESCRIBE vehicle_images;

-- Show sample data
SELECT 'Admin Users:' as table_name;
SELECT id, username, email, created_at FROM admin_users;

SELECT 'Vehicles:' as table_name;
SELECT id, name, price, range_km, badge, is_active, created_at FROM vehicles;

SELECT 'Vehicle Images:' as table_name;
SELECT id, vehicle_id, image_url, is_primary, created_at FROM vehicle_images;