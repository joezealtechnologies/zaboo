-- Useful SQL queries for managing the FaZona EV database

USE fazona_ev;

-- 1. Get all active vehicles with their primary images
SELECT 
    v.id,
    v.name,
    v.price,
    v.range_km,
    v.description,
    v.features,
    v.badge,
    v.badge_color,
    v.rating,
    vi.image_url as primary_image,
    v.created_at
FROM vehicles v
LEFT JOIN vehicle_images vi ON v.id = vi.vehicle_id AND vi.is_primary = true
WHERE v.is_active = true
ORDER BY v.created_at DESC;

-- 2. Get a specific vehicle with all its images
SELECT 
    v.*,
    GROUP_CONCAT(vi.image_url ORDER BY vi.is_primary DESC, vi.created_at ASC) as all_images
FROM vehicles v
LEFT JOIN vehicle_images vi ON v.id = vi.vehicle_id
WHERE v.id = 1  -- Replace with actual vehicle ID
GROUP BY v.id;

-- 3. Get vehicles by price range
SELECT * FROM vehicles 
WHERE CAST(REPLACE(REPLACE(price, '₦', ''), ' million', '') AS DECIMAL) BETWEEN 10 AND 20
AND is_active = true;

-- 4. Get vehicles with specific features
SELECT * FROM vehicles 
WHERE JSON_CONTAINS(features, '"Fast Charging"')
AND is_active = true;

-- 5. Get vehicle statistics
SELECT 
    COUNT(*) as total_vehicles,
    COUNT(CASE WHEN is_active = true THEN 1 END) as active_vehicles,
    COUNT(CASE WHEN is_active = false THEN 1 END) as inactive_vehicles,
    AVG(rating) as average_rating
FROM vehicles;

-- 6. Get vehicles with image counts
SELECT 
    v.id,
    v.name,
    v.price,
    COUNT(vi.id) as image_count,
    v.is_active
FROM vehicles v
LEFT JOIN vehicle_images vi ON v.id = vi.vehicle_id
GROUP BY v.id, v.name, v.price, v.is_active
ORDER BY image_count DESC;

-- 7. Find vehicles without images
SELECT v.* 
FROM vehicles v
LEFT JOIN vehicle_images vi ON v.id = vi.vehicle_id
WHERE vi.id IS NULL;

-- 8. Get most popular vehicles (by rating)
SELECT * FROM vehicles 
WHERE is_active = true 
ORDER BY rating DESC, created_at DESC;

-- 9. Search vehicles by name or description
SELECT * FROM vehicles 
WHERE (name LIKE '%Premium%' OR description LIKE '%Premium%')
AND is_active = true;

-- 10. Get vehicles created in the last 30 days
SELECT * FROM vehicles 
WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
ORDER BY created_at DESC;

-- 11. Update vehicle status (activate/deactivate)
-- UPDATE vehicles SET is_active = false WHERE id = 1;

-- 12. Set a new primary image for a vehicle
-- UPDATE vehicle_images SET is_primary = false WHERE vehicle_id = 1;
-- UPDATE vehicle_images SET is_primary = true WHERE id = 5 AND vehicle_id = 1;

-- 13. Delete a vehicle and all its images (CASCADE will handle images)
-- DELETE FROM vehicles WHERE id = 1;

-- 14. Get admin user information
SELECT id, username, email, created_at FROM admin_users;

-- 15. Create a new admin user (password should be hashed)
-- INSERT INTO admin_users (username, email, password) 
-- VALUES ('newadmin', 'newadmin@fazonaev.com', '$2a$10$hashedpassword');

-- 16. Backup queries - Export vehicle data
SELECT 
    v.id,
    v.name,
    v.price,
    v.range_km,
    v.description,
    v.features,
    v.badge,
    v.badge_color,
    v.rating,
    v.is_active,
    GROUP_CONCAT(vi.image_url) as images
FROM vehicles v
LEFT JOIN vehicle_images vi ON v.id = vi.vehicle_id
GROUP BY v.id
ORDER BY v.id;