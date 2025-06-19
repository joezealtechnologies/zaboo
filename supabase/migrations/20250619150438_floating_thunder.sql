-- Additional sample data for testing
-- Run this after the main setup if you want more test data

USE fazona_ev;

-- Insert more sample vehicles
INSERT INTO vehicles (name, price, range_km, description, features, badge, badge_color, rating, is_active) VALUES 
(
    'Executive Sedan',
    '₦25 million',
    '250km per charge',
    'Luxury electric sedan designed for executives and premium customers.',
    '["Leather Interior", "Autopilot", "Premium Sound", "Climate Control", "Wireless Charging"]',
    'Luxury',
    'bg-purple-500',
    5,
    true
),
(
    'Urban Commuter',
    '₦8 million',
    '120km per charge',
    'Compact and efficient vehicle perfect for daily urban commuting.',
    '["Compact Design", "Easy Parking", "Low Maintenance", "City Mode"]',
    'Commuter',
    'bg-yellow-500',
    4,
    true
),
(
    'Family SUV',
    '₦18 million',
    '180km per charge',
    'Spacious electric SUV perfect for families with advanced safety features.',
    '["7 Seater", "Family Safety", "Large Storage", "Child Locks", "Entertainment System"]',
    'Family Choice',
    'bg-green-500',
    5,
    true
);

-- Sample vehicle images (you would need to upload actual images)
-- These are placeholder entries showing the structure
INSERT INTO vehicle_images (vehicle_id, image_url, is_primary, alt_text) VALUES 
(1, '/uploads/premium-long-range-1.jpg', true, 'Premium Long Range - Front View'),
(1, '/uploads/premium-long-range-2.jpg', false, 'Premium Long Range - Interior'),
(1, '/uploads/premium-long-range-3.jpg', false, 'Premium Long Range - Side View'),
(2, '/uploads/mid-range-1.jpg', true, 'Mid-Range Model - Front View'),
(2, '/uploads/mid-range-2.jpg', false, 'Mid-Range Model - Dashboard'),
(3, '/uploads/standard-range-1.jpg', true, 'Standard Range - Front View'),
(4, '/uploads/compact-entry-1.jpg', true, 'Compact Entry - Front View');