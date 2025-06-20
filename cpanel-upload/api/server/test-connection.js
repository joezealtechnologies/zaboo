// Test database connection and login
import mysql from 'mysql2/promise';
import bcrypt from 'bcryptjs';
import dotenv from 'dotenv';

dotenv.config();

async function testConnection() {
  const dbConfig = {
    host: process.env.DB_HOST || 'localhost',
    user: process.env.DB_USER || 'root',
    password: process.env.DB_PASSWORD || '',
    database: process.env.DB_NAME || 'fazona_ev',
    port: process.env.DB_PORT || 3306
  };

  console.log('🔍 Testing database connection...');
  console.log('Config:', {
    host: dbConfig.host,
    user: dbConfig.user,
    database: dbConfig.database,
    port: dbConfig.port,
    password: dbConfig.password ? '***' : 'empty'
  });

  try {
    const connection = await mysql.createConnection(dbConfig);
    console.log('✅ Database connection successful');

    // Test admin user
    const [users] = await connection.execute(
      'SELECT id, username, email, password FROM admin_users WHERE username = ? OR email = ?',
      ['admin', 'admin']
    );

    if (users.length === 0) {
      console.log('❌ No admin user found');
      console.log('💡 Run: npm run db:setup to create admin user');
      return;
    }

    const user = users[0];
    console.log('✅ Admin user found:', {
      id: user.id,
      username: user.username,
      email: user.email
    });

    // Test password
    const testPassword = 'admin123';
    const isValidPassword = await bcrypt.compare(testPassword, user.password);
    
    console.log('🔐 Password test:');
    console.log('   Testing password: admin123');
    console.log('   Stored hash:', user.password.substring(0, 30) + '...');
    console.log('   Password valid:', isValidPassword);

    if (!isValidPassword) {
      console.log('⚠️  Password mismatch! Fixing...');
      
      const newHash = await bcrypt.hash('admin123', 10);
      await connection.execute(
        'UPDATE admin_users SET password = ? WHERE id = ?',
        [newHash, user.id]
      );
      
      console.log('✅ Password updated successfully');
    }

    // Test vehicles
    const [vehicleCount] = await connection.execute('SELECT COUNT(*) as count FROM vehicles');
    console.log('📊 Vehicles in database:', vehicleCount[0].count);

    // Test vehicle images
    const [imageCount] = await connection.execute('SELECT COUNT(*) as count FROM vehicle_images');
    console.log('🖼️  Vehicle images in database:', imageCount[0].count);

    await connection.end();
    console.log('\n🎉 All tests passed! You should be able to login now.');
    console.log('\n🚀 Next steps:');
    console.log('1. Start the server: npm run server');
    console.log('2. Start the frontend: npm run dev');
    console.log('3. Or start both: npm run dev:full');
    console.log('4. Visit admin panel: http://localhost:3000/admin');
    console.log('5. Login with: admin / admin123');

  } catch (error) {
    console.error('❌ Connection test failed:', error.message);
    
    if (error.code === 'ER_BAD_DB_ERROR') {
      console.log('💡 Database "fazona_ev" does not exist. Run: npm run db:setup');
    }
    
    if (error.code === 'ECONNREFUSED') {
      console.log('💡 MySQL server is not running. Please start MySQL first.');
    }
    
    if (error.code === 'ER_ACCESS_DENIED_ERROR') {
      console.log('💡 Access denied. Check your MySQL credentials in .env file.');
    }
  }
}

testConnection();