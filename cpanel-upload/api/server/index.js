import express from 'express';
import cors from 'cors';
import mysql from 'mysql2/promise';
import multer from 'multer';
import bcrypt from 'bcryptjs';
import jwt from 'jsonwebtoken';
import path from 'path';
import fs from 'fs';
import { fileURLToPath } from 'url';
import dotenv from 'dotenv';

dotenv.config();

const __filename = fileURLToPath(import.meta.url);
const __dirname = path.dirname(__filename);

const app = express();
const PORT = process.env.PORT || 5000;

// Middleware
app.use(cors());
app.use(express.json());
app.use('/uploads', express.static(path.join(__dirname, 'uploads')));

// Create uploads directory if it doesn't exist
const uploadsDir = path.join(__dirname, 'uploads');
if (!fs.existsSync(uploadsDir)) {
  fs.mkdirSync(uploadsDir, { recursive: true });
}

// Database configuration
const dbConfig = {
  host: process.env.DB_HOST || 'localhost',
  user: process.env.DB_USER || 'root',
  password: process.env.DB_PASSWORD || '',
  database: process.env.DB_NAME || 'fazona_ev',
  port: process.env.DB_PORT || 3306
};

// Create database connection
let db;
async function initDatabase() {
  try {
    // Connect directly to the database (not using USE command)
    db = await mysql.createConnection(dbConfig);
    console.log('Connected to MySQL database');
    
    // Test the connection
    await db.execute('SELECT 1');
    console.log('Database connection verified');
    
    // Create tables if they don't exist
    await createTables();
    await createDefaultAdmin();
  } catch (error) {
    console.error('Database connection failed:', error);
    
    // If database doesn't exist, try to create it
    if (error.code === 'ER_BAD_DB_ERROR') {
      console.log('Database does not exist. Please run: npm run db:setup');
    }
  }
}

// Create database tables
async function createTables() {
  try {
    // Admin users table
    const adminTable = `
      CREATE TABLE IF NOT EXISTS admin_users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(50) UNIQUE NOT NULL,
        email VARCHAR(100) UNIQUE NOT NULL,
        password VARCHAR(255) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
      )
    `;

    // Vehicles table
    const vehiclesTable = `
      CREATE TABLE IF NOT EXISTS vehicles (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL,
        price VARCHAR(50) NOT NULL,
        range_km VARCHAR(50) NOT NULL,
        description TEXT,
        features JSON,
        badge VARCHAR(50),
        badge_color VARCHAR(50),
        rating INT DEFAULT 5,
        is_active BOOLEAN DEFAULT true,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
      )
    `;

    // Vehicle images table
    const vehicleImagesTable = `
      CREATE TABLE IF NOT EXISTS vehicle_images (
        id INT AUTO_INCREMENT PRIMARY KEY,
        vehicle_id INT NOT NULL,
        image_url VARCHAR(255) NOT NULL,
        is_primary BOOLEAN DEFAULT false,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (vehicle_id) REFERENCES vehicles(id) ON DELETE CASCADE
      )
    `;

    await db.execute(adminTable);
    await db.execute(vehiclesTable);
    await db.execute(vehicleImagesTable);
    
    console.log('Database tables verified/created successfully');
  } catch (error) {
    console.error('Error creating tables:', error);
  }
}

// Create default admin user
async function createDefaultAdmin() {
  try {
    const [existing] = await db.execute('SELECT id FROM admin_users WHERE username = ?', ['admin']);
    
    if (existing.length === 0) {
      const hashedPassword = await bcrypt.hash('admin123', 10);
      await db.execute(
        'INSERT INTO admin_users (username, email, password) VALUES (?, ?, ?)',
        ['admin', 'admin@fazonaev.com', hashedPassword]
      );
      console.log('Default admin user created: admin/admin123');
    }
  } catch (error) {
    console.error('Error creating default admin:', error);
  }
}

// Enhanced Multer configuration for multiple file uploads
const storage = multer.diskStorage({
  destination: (req, file, cb) => {
    cb(null, uploadsDir);
  },
  filename: (req, file, cb) => {
    const uniqueSuffix = Date.now() + '-' + Math.round(Math.random() * 1E9);
    const ext = path.extname(file.originalname);
    cb(null, `vehicle-${uniqueSuffix}${ext}`);
  }
});

const upload = multer({ 
  storage: storage,
  fileFilter: (req, file, cb) => {
    // Check file type
    if (file.mimetype.startsWith('image/')) {
      cb(null, true);
    } else {
      cb(new Error('Only image files are allowed!'), false);
    }
  },
  limits: {
    fileSize: 5 * 1024 * 1024, // 5MB limit per file
    files: 10 // Maximum 10 files per upload
  }
});

// JWT middleware
const authenticateToken = (req, res, next) => {
  const authHeader = req.headers['authorization'];
  const token = authHeader && authHeader.split(' ')[1];

  if (!token) {
    return res.status(401).json({ error: 'Access token required' });
  }

  jwt.verify(token, process.env.JWT_SECRET || 'fazona_secret_key', (err, user) => {
    if (err) {
      return res.status(403).json({ error: 'Invalid token' });
    }
    req.user = user;
    next();
  });
};

// Helper function to manage primary images
async function managePrimaryImages(vehicleId, newImageIsPrimary = false) {
  try {
    if (newImageIsPrimary) {
      // Set all existing images for this vehicle to non-primary
      await db.execute(
        'UPDATE vehicle_images SET is_primary = false WHERE vehicle_id = ?',
        [vehicleId]
      );
    } else {
      // Check if vehicle has any primary image
      const [primaryImages] = await db.execute(
        'SELECT id FROM vehicle_images WHERE vehicle_id = ? AND is_primary = true',
        [vehicleId]
      );
      
      // If no primary image exists, make the first image primary
      if (primaryImages.length === 0) {
        const [firstImage] = await db.execute(
          'SELECT id FROM vehicle_images WHERE vehicle_id = ? ORDER BY created_at ASC LIMIT 1',
          [vehicleId]
        );
        
        if (firstImage.length > 0) {
          await db.execute(
            'UPDATE vehicle_images SET is_primary = true WHERE id = ?',
            [firstImage[0].id]
          );
        }
      }
    }
  } catch (error) {
    console.error('Error managing primary images:', error);
  }
}

// Routes

// Admin login
app.post('/api/admin/login', async (req, res) => {
  try {
    const { username, password } = req.body;

    const [users] = await db.execute(
      'SELECT * FROM admin_users WHERE username = ? OR email = ?',
      [username, username]
    );

    if (users.length === 0) {
      return res.status(401).json({ error: 'Invalid credentials' });
    }

    const user = users[0];
    const isValidPassword = await bcrypt.compare(password, user.password);

    if (!isValidPassword) {
      return res.status(401).json({ error: 'Invalid credentials' });
    }

    const token = jwt.sign(
      { id: user.id, username: user.username },
      process.env.JWT_SECRET || 'fazona_secret_key',
      { expiresIn: '24h' }
    );

    res.json({
      token,
      user: {
        id: user.id,
        username: user.username,
        email: user.email
      }
    });
  } catch (error) {
    console.error('Login error:', error);
    res.status(500).json({ error: 'Internal server error' });
  }
});

// Get all vehicles (public)
app.get('/api/vehicles', async (req, res) => {
  try {
    const [vehicles] = await db.execute(`
      SELECT v.*, 
             GROUP_CONCAT(vi.image_url ORDER BY vi.is_primary DESC, vi.created_at ASC) as images,
             (SELECT vi2.image_url FROM vehicle_images vi2 WHERE vi2.vehicle_id = v.id AND vi2.is_primary = true LIMIT 1) as primary_image
      FROM vehicles v
      LEFT JOIN vehicle_images vi ON v.id = vi.vehicle_id
      WHERE v.is_active = true
      GROUP BY v.id
      ORDER BY v.created_at DESC
    `);

    const formattedVehicles = vehicles.map(vehicle => ({
      ...vehicle,
      features: vehicle.features ? JSON.parse(vehicle.features) : [],
      images: vehicle.images ? vehicle.images.split(',') : [],
      image: vehicle.primary_image || (vehicle.images ? vehicle.images.split(',')[0] : null)
    }));

    res.json(formattedVehicles);
  } catch (error) {
    console.error('Error fetching vehicles:', error);
    res.status(500).json({ error: 'Internal server error' });
  }
});

// Get all vehicles (admin)
app.get('/api/admin/vehicles', authenticateToken, async (req, res) => {
  try {
    const [vehicles] = await db.execute(`
      SELECT v.*, 
             GROUP_CONCAT(vi.image_url ORDER BY vi.is_primary DESC, vi.created_at ASC) as images,
             (SELECT vi2.image_url FROM vehicle_images vi2 WHERE vi2.vehicle_id = v.id AND vi2.is_primary = true LIMIT 1) as primary_image
      FROM vehicles v
      LEFT JOIN vehicle_images vi ON v.id = vi.vehicle_id
      GROUP BY v.id
      ORDER BY v.created_at DESC
    `);

    const formattedVehicles = vehicles.map(vehicle => ({
      ...vehicle,
      features: vehicle.features ? JSON.parse(vehicle.features) : [],
      images: vehicle.images ? vehicle.images.split(',') : []
    }));

    res.json(formattedVehicles);
  } catch (error) {
    console.error('Error fetching vehicles:', error);
    res.status(500).json({ error: 'Internal server error' });
  }
});

// Create new vehicle with multiple images
app.post('/api/admin/vehicles', authenticateToken, upload.array('images', 10), async (req, res) => {
  try {
    const { name, price, range_km, description, features, badge, badge_color, rating } = req.body;
    
    // Insert vehicle first
    const [result] = await db.execute(
      'INSERT INTO vehicles (name, price, range_km, description, features, badge, badge_color, rating) VALUES (?, ?, ?, ?, ?, ?, ?, ?)',
      [name, price, range_km, description, JSON.stringify(features ? features.split(',').map(f => f.trim()) : []), badge, badge_color, rating || 5]
    );

    const vehicleId = result.insertId;

    // Insert images one by one to avoid trigger conflicts
    if (req.files && req.files.length > 0) {
      for (let i = 0; i < req.files.length; i++) {
        const file = req.files[i];
        const imageUrl = `/uploads/${file.filename}`;
        const isPrimary = i === 0; // First image is primary

        // Insert image without relying on triggers
        await db.execute(
          'INSERT INTO vehicle_images (vehicle_id, image_url, is_primary) VALUES (?, ?, ?)',
          [vehicleId, imageUrl, isPrimary]
        );
      }
    }

    res.json({ 
      message: 'Vehicle created successfully', 
      id: vehicleId,
      imagesUploaded: req.files ? req.files.length : 0
    });
  } catch (error) {
    console.error('Error creating vehicle:', error);
    res.status(500).json({ error: 'Internal server error: ' + error.message });
  }
});

// Update vehicle with additional images
app.put('/api/admin/vehicles/:id', authenticateToken, upload.array('images', 10), async (req, res) => {
  try {
    const vehicleId = req.params.id;
    const { name, price, range_km, description, features, badge, badge_color, rating, is_active } = req.body;
    
    // Update vehicle
    await db.execute(
      'UPDATE vehicles SET name = ?, price = ?, range_km = ?, description = ?, features = ?, badge = ?, badge_color = ?, rating = ?, is_active = ? WHERE id = ?',
      [name, price, range_km, description, JSON.stringify(features ? features.split(',').map(f => f.trim()) : []), badge, badge_color, rating || 5, is_active !== undefined ? is_active : true, vehicleId]
    );

    // Add new images if provided
    if (req.files && req.files.length > 0) {
      // Check if this vehicle has any existing images
      const [existingImages] = await db.execute('SELECT COUNT(*) as count FROM vehicle_images WHERE vehicle_id = ?', [vehicleId]);
      const hasExistingImages = existingImages[0].count > 0;

      for (let i = 0; i < req.files.length; i++) {
        const file = req.files[i];
        const imageUrl = `/uploads/${file.filename}`;
        
        // If no existing images, make first new image primary
        const isPrimary = !hasExistingImages && i === 0;

        await db.execute(
          'INSERT INTO vehicle_images (vehicle_id, image_url, is_primary) VALUES (?, ?, ?)',
          [vehicleId, imageUrl, isPrimary]
        );
      }
    }

    res.json({ 
      message: 'Vehicle updated successfully',
      newImagesAdded: req.files ? req.files.length : 0
    });
  } catch (error) {
    console.error('Error updating vehicle:', error);
    res.status(500).json({ error: 'Internal server error: ' + error.message });
  }
});

// Delete vehicle
app.delete('/api/admin/vehicles/:id', authenticateToken, async (req, res) => {
  try {
    const vehicleId = req.params.id;
    
    // Get images to delete files
    const [images] = await db.execute('SELECT image_url FROM vehicle_images WHERE vehicle_id = ?', [vehicleId]);
    
    // Delete image files
    images.forEach(img => {
      const filePath = path.join(__dirname, img.image_url);
      if (fs.existsSync(filePath)) {
        fs.unlinkSync(filePath);
      }
    });

    // Delete vehicle (images will be deleted by CASCADE)
    await db.execute('DELETE FROM vehicles WHERE id = ?', [vehicleId]);

    res.json({ message: 'Vehicle deleted successfully' });
  } catch (error) {
    console.error('Error deleting vehicle:', error);
    res.status(500).json({ error: 'Internal server error' });
  }
});

// Delete specific vehicle image
app.delete('/api/admin/vehicles/:vehicleId/images/:imageId', authenticateToken, async (req, res) => {
  try {
    const { vehicleId, imageId } = req.params;
    
    // Get image info
    const [images] = await db.execute('SELECT id, image_url, is_primary FROM vehicle_images WHERE id = ? AND vehicle_id = ?', [imageId, vehicleId]);
    
    if (images.length === 0) {
      return res.status(404).json({ error: 'Image not found' });
    }

    const imageToDelete = images[0];

    // Delete file
    const filePath = path.join(__dirname, imageToDelete.image_url);
    if (fs.existsSync(filePath)) {
      fs.unlinkSync(filePath);
    }

    // Delete from database
    await db.execute('DELETE FROM vehicle_images WHERE id = ?', [imageId]);

    // If this was the primary image, set another image as primary
    if (imageToDelete.is_primary) {
      await managePrimaryImages(vehicleId, false);
    }

    res.json({ message: 'Image deleted successfully' });
  } catch (error) {
    console.error('Error deleting image:', error);
    res.status(500).json({ error: 'Internal server error' });
  }
});

// Set primary image
app.put('/api/admin/vehicles/:vehicleId/images/:imageId/primary', authenticateToken, async (req, res) => {
  try {
    const { vehicleId, imageId } = req.params;
    
    // Remove primary flag from all images of this vehicle
    await db.execute('UPDATE vehicle_images SET is_primary = false WHERE vehicle_id = ?', [vehicleId]);
    
    // Set new primary image
    await db.execute('UPDATE vehicle_images SET is_primary = true WHERE id = ? AND vehicle_id = ?', [imageId, vehicleId]);

    res.json({ message: 'Primary image updated successfully' });
  } catch (error) {
    console.error('Error updating primary image:', error);
    res.status(500).json({ error: 'Internal server error' });
  }
});

// Get vehicle images
app.get('/api/admin/vehicles/:vehicleId/images', authenticateToken, async (req, res) => {
  try {
    const { vehicleId } = req.params;
    
    const [images] = await db.execute(
      'SELECT * FROM vehicle_images WHERE vehicle_id = ? ORDER BY is_primary DESC, created_at ASC',
      [vehicleId]
    );

    res.json(images);
  } catch (error) {
    console.error('Error fetching vehicle images:', error);
    res.status(500).json({ error: 'Internal server error' });
  }
});

// Initialize database and start server
initDatabase().then(() => {
  app.listen(PORT, () => {
    console.log(`Server running on port ${PORT}`);
    console.log(`Admin panel will be available at: http://localhost:3000/admin`);
  });
});