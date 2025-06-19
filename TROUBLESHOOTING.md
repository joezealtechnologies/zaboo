# FaZona EV Login Troubleshooting Guide

## Quick Fix Steps

### 1. Check Database Connection
```bash
# Test if MySQL is running
mysql -u root -p

# If MySQL is not running, start it:
# Windows (XAMPP): Start XAMPP and start MySQL
# macOS: brew services start mysql
# Linux: sudo systemctl start mysql
```

### 2. Setup Database
```bash
# Run the database setup script
npm run db:setup
```

### 3. Test Connection
```bash
# Test the database connection and login
npm run db:test
```

### 4. Manual Database Setup (if scripts fail)
```sql
-- Connect to MySQL
mysql -u root -p

-- Create database
CREATE DATABASE fazona_ev;
USE fazona_ev;

-- Create admin table
CREATE TABLE admin_users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Insert admin user (password: admin123)
INSERT INTO admin_users (username, email, password) VALUES 
('admin', 'admin@fazonaev.com', '$2a$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi');
```

## Common Issues

### Issue 1: "Connection refused"
**Cause:** MySQL server is not running
**Solution:**
- Start MySQL server
- Check if port 3306 is available
- Verify MySQL is installed

### Issue 2: "Access denied"
**Cause:** Wrong database credentials
**Solution:**
- Check `.env` file credentials
- Verify MySQL username/password
- Grant proper permissions to user

### Issue 3: "Database does not exist"
**Cause:** Database not created
**Solution:**
- Run `npm run db:setup`
- Or manually create database

### Issue 4: "Invalid credentials" on login
**Cause:** Admin user not created or password mismatch
**Solution:**
- Run `npm run db:test` to check and fix
- Or manually recreate admin user

## Environment Variables

Make sure your `.env` file has correct values:

```env
DB_HOST=localhost
DB_USER=root
DB_PASSWORD=your_mysql_password
DB_NAME=fazona_ev
DB_PORT=3306
JWT_SECRET=your_secret_key
PORT=5000
```

## Default Login Credentials

- **Username:** admin
- **Password:** admin123
- **Email:** admin@fazonaev.com

## Step-by-Step Debugging

1. **Check MySQL Status:**
   ```bash
   # Check if MySQL is running
   sudo systemctl status mysql  # Linux
   brew services list | grep mysql  # macOS
   ```

2. **Test MySQL Connection:**
   ```bash
   mysql -u root -p
   ```

3. **Run Setup Scripts:**
   ```bash
   npm run db:setup
   npm run db:test
   ```

4. **Check Server Logs:**
   ```bash
   npm run server
   # Look for connection errors in the console
   ```

5. **Test API Endpoint:**
   ```bash
   curl -X POST http://localhost:5000/api/admin/login \
   -H "Content-Type: application/json" \
   -d '{"username":"admin","password":"admin123"}'
   ```

## Contact Support

If you're still having issues:
1. Check the server console for error messages
2. Verify all dependencies are installed: `npm install`
3. Make sure ports 3000 and 5000 are available
4. Try restarting both frontend and backend servers

## Success Indicators

When everything is working correctly, you should see:
- ✅ Database connection successful
- ✅ Admin user found
- ✅ Password valid
- ✅ Server running on port 5000
- ✅ Admin panel accessible at http://localhost:3000/admin