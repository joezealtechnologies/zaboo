-- Database maintenance and optimization queries

USE fazona_ev;

-- 1. Optimize tables
OPTIMIZE TABLE admin_users;
OPTIMIZE TABLE vehicles;
OPTIMIZE TABLE vehicle_images;

-- 2. Analyze tables for better query performance
ANALYZE TABLE admin_users;
ANALYZE TABLE vehicles;
ANALYZE TABLE vehicle_images;

-- 3. Check table status
SHOW TABLE STATUS LIKE 'vehicles';
SHOW TABLE STATUS LIKE 'vehicle_images';
SHOW TABLE STATUS LIKE 'admin_users';

-- 4. Show index usage
SHOW INDEX FROM vehicles;
SHOW INDEX FROM vehicle_images;
SHOW INDEX FROM admin_users;

-- 5. Database size information
SELECT 
    table_name AS 'Table',
    ROUND(((data_length + index_length) / 1024 / 1024), 2) AS 'Size (MB)'
FROM information_schema.TABLES 
WHERE table_schema = 'fazona_ev'
ORDER BY (data_length + index_length) DESC;

-- 6. Clean up orphaned records (if any)
-- This should not return any results if foreign keys are working properly
SELECT vi.* FROM vehicle_images vi
LEFT JOIN vehicles v ON vi.vehicle_id = v.id
WHERE v.id IS NULL;

-- 7. Ensure data integrity
-- Check for vehicles without primary images
SELECT v.id, v.name 
FROM vehicles v
LEFT JOIN vehicle_images vi ON v.id = vi.vehicle_id AND vi.is_primary = true
WHERE vi.id IS NULL AND v.is_active = true;

-- 8. Fix vehicles without primary images (set first image as primary)
UPDATE vehicle_images vi1
JOIN (
    SELECT vehicle_id, MIN(id) as first_image_id
    FROM vehicle_images vi2
    WHERE vehicle_id IN (
        SELECT v.id 
        FROM vehicles v
        LEFT JOIN vehicle_images vi ON v.id = vi.vehicle_id AND vi.is_primary = true
        WHERE vi.id IS NULL AND v.is_active = true
    )
    GROUP BY vehicle_id
) first_images ON vi1.vehicle_id = first_images.vehicle_id AND vi1.id = first_images.first_image_id
SET vi1.is_primary = true;

-- 9. Create backup of important data
CREATE TABLE vehicles_backup AS SELECT * FROM vehicles;
CREATE TABLE vehicle_images_backup AS SELECT * FROM vehicle_images;
CREATE TABLE admin_users_backup AS SELECT * FROM admin_users;

-- 10. Performance monitoring queries
-- Show slow queries (if slow query log is enabled)
-- SHOW VARIABLES LIKE 'slow_query_log';
-- SHOW VARIABLES LIKE 'long_query_time';

-- 11. Show current connections and processes
SHOW PROCESSLIST;

-- 12. Show database variables
SHOW VARIABLES LIKE 'max_connections';
SHOW VARIABLES LIKE 'innodb_buffer_pool_size';

-- 13. Reset auto increment if needed (be careful with this)
-- ALTER TABLE vehicles AUTO_INCREMENT = 1;
-- ALTER TABLE vehicle_images AUTO_INCREMENT = 1;
-- ALTER TABLE admin_users AUTO_INCREMENT = 1;