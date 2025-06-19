// Standalone database setup script to ensure everything is created properly
import mysql from 'mysql2/promise';
import bcrypt from 'bcryptjs';
import dotenv from 'dotenv';

dotenv.config();

const dbConfig = {
  host: process.env.DB_HOST || 'localhost',
  user: process.env.DB_USER || 'root',
  password: process.env.DB_PASSWORD || '',
  port: process.env.DB_PORT || 3306
};

async function setupDatabase() {
  let connection;
  
  try {
    console.log('🔄 Connecting to MySQL...');
    console.log('Connection config:', {
      host: dbConfig.host,
      user: dbConfig.user,
      port: dbConfig.port,
      password: dbConfig.password ? '***' : 'empty'
    });

    // Connect to MySQL server (without database)
    connection = await mysql.createConnection(dbConfig);
    console.log('✅ Connected to MySQL server');

    // Create database using query() instead of execute()
    await connection.query('CREATE DATABASE IF NOT EXISTS fazona_ev');
    console.log('✅ Database "fazona_ev" created/verified');

    // Close connection and reconnect with database specified
    await connection.end();
    
    // Reconnect with database
    const dbConfigWithDB = {
      ...dbConfig,
      database: 'fazona_ev'
    };
    
    connection = await mysql.createConnection(dbConfigWithDB);
    console.log('✅ Connected to fazona_ev database');

    // Drop existing triggers if they exist to avoid conflicts
    try {
      await connection.query('DROP TRIGGER IF EXISTS before_vehicle_image_insert');
      await connection.query('DROP TRIGGER IF EXISTS before_vehicle_image_update');
      console.log('🧹 Cleaned up existing triggers');
    } catch (error) {
      // Ignore errors if triggers don't exist
    }

    // Create admin_users table
    const adminTable = `
      CREATE TABLE IF NOT EXISTS admin_users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(50) UNIQUE NOT NULL,
        email VARCHAR(100) UNIQUE NOT NULL,
        password VARCHAR(255) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
      )
    `;
    await connection.query(adminTable);
    console.log('✅ admin_users table created');

    // Create vehicles table
    const vehiclesTable = `
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
      )
    `;
    await connection.query(vehiclesTable);
    console.log('✅ vehicles table created');

    // Create vehicle_images table WITHOUT triggers initially
    const vehicleImagesTable = `
      CREATE TABLE IF NOT EXISTS vehicle_images (
        id INT AUTO_INCREMENT PRIMARY KEY,
        vehicle_id INT NOT NULL,
        image_url VARCHAR(255) NOT NULL,
        is_primary BOOLEAN DEFAULT false,
        alt_text VARCHAR(255),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (vehicle_id) REFERENCES vehicles(id) ON DELETE CASCADE
      )
    `;
    await connection.query(vehicleImagesTable);
    console.log('✅ vehicle_images table created');

    // Check if admin user exists
    const [existingAdmin] = await connection.execute(
      'SELECT id, username FROM admin_users WHERE username = ?',
      ['admin']
    );

    if (existingAdmin.length === 0) {
      // Create default admin user
      const hashedPassword = await bcrypt.hash('admin123', 10);
      await connection.execute(
        'INSERT INTO admin_users (username, email, password) VALUES (?, ?, ?)',
        ['admin', 'admin@fazonaev.com', hashedPassword]
      );
      console.log('✅ Default admin user created');
      console.log('   Username: admin');
      console.log('   Password: admin123');
      console.log('   Email: admin@fazonaev.com');
    } else {
      console.log('✅ Admin user already exists');
      console.log('   Username:', existingAdmin[0].username);
    }

    // Test login credentials
    const [users] = await connection.execute(
      'SELECT id, username, email, password FROM admin_users WHERE username = ?',
      ['admin']
    );

    if (users.length > 0) {
      const user = users[0];
      const testPassword = 'admin123';
      const isValid = await bcrypt.compare(testPassword, user.password);
      
      console.log('🔍 Testing login credentials:');
      console.log('   User found:', !!user);
      console.log('   Password hash:', user.password.substring(0, 20) + '...');
      console.log('   Password test result:', isValid);
      
      if (!isValid) {
        console.log('⚠️  Password verification failed! Recreating admin user...');
        
        // Delete existing admin and recreate
        await connection.execute('DELETE FROM admin_users WHERE username = ?', ['admin']);
        
        const newHashedPassword = await bcrypt.hash('admin123', 10);
        await connection.execute(
          'INSERT INTO admin_users (username, email, password) VALUES (?, ?, ?)',
          ['admin', 'admin@fazonaev.com', newHashedPassword]
        );
        
        console.log('✅ Admin user recreated with correct password');
      }
    }

    // Insert sample vehicles if none exist
    const [vehicleCount] = await connection.execute('SELECT COUNT(*) as count FROM vehicles');
    
    if (vehicleCount[0].count === 0) {
      console.log('🔄 Adding sample vehicles...');
      
      const sampleVehicles = [
        {
          name: 'Premium Long Range',
          price: '₦20 million',
          range_km: '200km per charge',
          description: 'Our flagship electric vehicle with premium features and extended range.',
          features: '["Fast Charging", "Premium Interior", "Advanced Safety", "Government Duty Inclusive"]',
          badge: 'Most Popular',
          badge_color: 'bg-brand-red',
          rating: 5
        },
        {
          name: 'Mid-Range Model',
          price: '₦12 million',
          range_km: '150km per charge',
          description: 'Perfect balance of performance and affordability.',
          features: '["Smart Dashboard", "Regenerative Braking", "Fast Charging", "Eco Mode"]',
          badge: 'Best Value',
          badge_color: 'bg-green-500',
          rating: 5
        },
        {
          name: 'Standard Range',
          price: '₦9.5 million',
          range_km: '100km per charge',
          description: 'Ideal for city driving with essential features.',
          features: '["Digital Display", "Energy Recovery", "Compact Design", "City Optimized"]',
          badge: null,
          badge_color: null,
          rating: 4
        },
        {
          name: 'Compact Entry',
          price: '₦6.5 million',
          range_km: '80km per charge',
          description: 'Affordable entry-level electric vehicle.',
          features: '["Essential Features", "Urban Mobility", "Affordable Entry", "Efficient Design"]',
          badge: 'Entry Level',
          badge_color: 'bg-blue-500',
          rating: 4
        }
      ];

      for (const vehicle of sampleVehicles) {
        await connection.execute(
          'INSERT INTO vehicles (name, price, range_km, description, features, badge, badge_color, rating) VALUES (?, ?, ?, ?, ?, ?, ?, ?)',
          [vehicle.name, vehicle.price, vehicle.range_km, vehicle.description, vehicle.features, vehicle.badge, vehicle.badge_color, vehicle.rating]
        );
      }
      
      console.log('✅ Sample vehicles added');
    }

    console.log('\n🎉 Database setup completed successfully!');
    console.log('\n📋 Summary:');
    console.log('   Database: fazona_ev');
    console.log('   Tables: admin_users, vehicles, vehicle_images');
    console.log('   Admin Login: admin / admin123');
    console.log('   Admin Panel: http://localhost:3000/admin');
    console.log('\n⚠️  Note: Triggers removed to prevent conflicts');
    console.log('   Primary image management handled by application logic');

  } catch (error) {
    console.error('❌ Database setup failed:', error);
    
    if (error.code === 'ER_ACCESS_DENIED_ERROR') {
      console.log('\n🔧 Troubleshooting Tips:');
      console.log('1. Check your MySQL credentials in .env file');
      console.log('2. Make sure MySQL server is running');
      console.log('3. Verify the user has proper permissions');
      console.log('4. Try connecting with: mysql -u root -p');
    }
    
    if (error.code === 'ECONNREFUSED') {
      console.log('\n🔧 Connection refused - MySQL server might not be running');
      console.log('1. Start MySQL: sudo systemctl start mysql (Linux)');
      console.log('2. Or: brew services start mysql (macOS)');
      console.log('3. Or start XAMPP/WAMP if using those');
    }
    
    if (error.code === 'ER_UNSUPPORTED_PS') {
      console.log('\n🔧 MySQL prepared statement issue - this should be fixed now');
      console.log('1. The script has been updated to avoid this error');
      console.log('2. Try running the setup again');
    }
    
    process.exit(1);
  } finally {
    if (connection) {
      await connection.end();
    }
  }
}

// Run setup
setupDatabase();