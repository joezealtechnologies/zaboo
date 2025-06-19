# FaZona EV Database Documentation

## Database Setup

### Quick Setup
1. Run `setup.sql` to create the database structure
2. Optionally run `sample_data.sql` for test data
3. Use `queries.sql` for common operations
4. Use `maintenance.sql` for database optimization

### Manual Setup Steps

1. **Create Database:**
```sql
CREATE DATABASE fazona_ev;
USE fazona_ev;
```

2. **Run Setup Script:**
```bash
mysql -u root -p fazona_ev < database/setup.sql
```

3. **Add Sample Data (Optional):**
```bash
mysql -u root -p fazona_ev < database/sample_data.sql
```

## Database Schema

### Tables Overview

#### `admin_users`
- Stores admin login credentials
- Default admin: username=`admin`, password=`admin123`
- Passwords are hashed with bcrypt

#### `vehicles`
- Main vehicle information
- JSON field for features array
- Boolean flag for active/inactive status
- Rating system (1-5 stars)

#### `vehicle_images`
- Multiple images per vehicle
- Primary image designation
- Cascade delete when vehicle is removed

### Key Features

1. **Foreign Key Constraints:** Ensures data integrity
2. **Triggers:** Automatically manage primary images
3. **Indexes:** Optimized for common queries
4. **JSON Support:** Flexible feature storage
5. **Cascade Delete:** Clean up related data

## Common Operations

### Adding a Vehicle
```sql
INSERT INTO vehicles (name, price, range_km, description, features, badge, badge_color, rating) 
VALUES ('New Model', '₦15 million', '150km', 'Description', '["Feature1", "Feature2"]', 'New', 'bg-blue-500', 5);
```

### Adding Vehicle Images
```sql
INSERT INTO vehicle_images (vehicle_id, image_url, is_primary) 
VALUES (1, '/uploads/image1.jpg', true);
```

### Getting Active Vehicles
```sql
SELECT v.*, vi.image_url as primary_image 
FROM vehicles v 
LEFT JOIN vehicle_images vi ON v.id = vi.vehicle_id AND vi.is_primary = true 
WHERE v.is_active = true;
```

## Security Considerations

1. **Password Hashing:** All passwords use bcrypt
2. **SQL Injection:** Use parameterized queries
3. **File Uploads:** Validate image types and sizes
4. **Access Control:** JWT tokens for admin access

## Backup Strategy

1. **Regular Backups:**
```bash
mysqldump -u root -p fazona_ev > backup_$(date +%Y%m%d).sql
```

2. **Table-Specific Backups:**
```bash
mysqldump -u root -p fazona_ev vehicles > vehicles_backup.sql
```

## Performance Optimization

1. **Indexes:** Created on frequently queried columns
2. **Query Optimization:** Use EXPLAIN to analyze queries
3. **Regular Maintenance:** Run OPTIMIZE TABLE periodically
4. **Monitor:** Check slow query log

## Troubleshooting

### Common Issues

1. **Foreign Key Errors:**
   - Ensure parent records exist before inserting child records
   - Check CASCADE settings

2. **Image Upload Issues:**
   - Verify file permissions on uploads directory
   - Check file size limits

3. **Performance Issues:**
   - Run ANALYZE TABLE on large tables
   - Check for missing indexes

### Useful Commands

```sql
-- Check table structure
DESCRIBE vehicles;

-- Show indexes
SHOW INDEX FROM vehicles;

-- Check foreign keys
SELECT * FROM information_schema.KEY_COLUMN_USAGE 
WHERE TABLE_SCHEMA = 'fazona_ev';

-- Monitor table sizes
SELECT table_name, 
       ROUND(((data_length + index_length) / 1024 / 1024), 2) AS 'Size (MB)'
FROM information_schema.TABLES 
WHERE table_schema = 'fazona_ev';
```

## Migration Notes

When updating the database schema:

1. Always backup before making changes
2. Test migrations on development first
3. Use ALTER TABLE for schema changes
4. Update application code accordingly
5. Document all changes

## API Integration

The database is designed to work with the Express.js API:

- **Authentication:** JWT tokens stored in headers
- **File Uploads:** Multer handles image storage
- **Validation:** Server-side validation before database operations
- **Error Handling:** Proper error responses for database issues

For more information, see the main README.md file.