# FaZona EV Website with Backend Management

A complete electric vehicle website with backend management system for FaZona EV.

## Features

### Frontend
- Modern React website with Framer Motion animations
- Responsive design with Tailwind CSS
- Dynamic vehicle listings from database
- Image carousels for multiple vehicle photos
- Contact forms and quote requests

### Backend
- Express.js REST API
- MySQL database with proper relationships
- JWT authentication for admin access
- File upload handling for vehicle images
- CRUD operations for vehicle management

### Admin Panel
- Secure login system (default: admin/admin123)
- Vehicle management dashboard
- Multiple image upload per vehicle
- Image carousel management
- Vehicle status control (active/inactive)
- Real-time preview of changes

## Setup Instructions

### 1. Database Setup
Install MySQL and create a database:
```sql
CREATE DATABASE fazona_ev;
```

### 2. Environment Configuration
Update the `.env` file with your database credentials:
```env
DB_HOST=localhost
DB_USER=root
DB_PASSWORD=your_password
DB_NAME=fazona_ev
DB_PORT=3306
JWT_SECRET=your_secret_key
PORT=5000
```

### 3. Install Dependencies
```bash
npm install
```

### 4. Start the Application
```bash
# Start both frontend and backend
npm run dev:full

# Or start separately:
# Backend only
npm run server

# Frontend only
npm run dev
```

### 5. Access the Application
- **Website**: http://localhost:3000
- **Admin Panel**: http://localhost:3000/admin
- **API**: http://localhost:5000/api

## Admin Panel Usage

### Default Login Credentials
- **Username**: admin
- **Email**: admin@fazonaev.com
- **Password**: admin123

### Managing Vehicles
1. Login to admin panel at `/admin`
2. Click "Add Vehicle" to create new listings
3. Upload multiple images per vehicle
4. Set vehicle details, pricing, and features
5. Control visibility with active/inactive status
6. Edit or delete existing vehicles

### Image Management
- Upload multiple images per vehicle
- First uploaded image becomes primary
- Navigate through images with arrow controls
- Delete individual images
- Set any image as primary

## Database Schema

### Tables
- `admin_users`: Admin authentication
- `vehicles`: Vehicle information
- `vehicle_images`: Multiple images per vehicle

### Key Features
- Foreign key relationships
- Cascade delete for images
- JSON storage for features array
- Automatic timestamps

## API Endpoints

### Public Endpoints
- `GET /api/vehicles` - Get all active vehicles

### Admin Endpoints (Requires Authentication)
- `POST /api/admin/login` - Admin login
- `GET /api/admin/vehicles` - Get all vehicles
- `POST /api/admin/vehicles` - Create vehicle
- `PUT /api/admin/vehicles/:id` - Update vehicle
- `DELETE /api/admin/vehicles/:id` - Delete vehicle
- `DELETE /api/admin/vehicles/:vehicleId/images/:imageId` - Delete image
- `PUT /api/admin/vehicles/:vehicleId/images/:imageId/primary` - Set primary image

## File Structure
```
├── server/
│   ├── index.js          # Express server
│   └── uploads/          # Uploaded images
├── src/
│   ├── components/
│   │   ├── admin/        # Admin panel components
│   │   └── ...           # Website components
│   ├── services/
│   │   └── api.ts        # API service layer
│   └── ...
├── .env                  # Environment variables
└── package.json
```

## Production Deployment

### Database
1. Set up MySQL database on your server
2. Update `.env` with production database credentials
3. Ensure proper database permissions

### Backend
1. Deploy Express server to your hosting platform
2. Set environment variables
3. Ensure file upload directory permissions

### Frontend
1. Update API base URL in production
2. Build and deploy React application
3. Configure routing for admin panel

## Security Notes

- Change default admin credentials in production
- Use strong JWT secret
- Implement rate limiting
- Add input validation
- Use HTTPS in production
- Secure file upload directory

## Support

For technical support or questions about the backend system, contact the development team.