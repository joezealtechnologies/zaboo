# 🚗⚡ FaZona EV - Complete Electric Vehicle Website

Nigeria's Premier Electric Vehicle Brand with Full Backend Management System.

## 🌟 Features

### 🎨 **Frontend**
- Modern React website with Framer Motion animations
- Responsive design with Tailwind CSS
- Dynamic vehicle listings from database
- Image galleries with lightbox functionality
- Contact forms and quote requests
- Professional admin panel

### 🔧 **Backend**
- Express.js REST API
- MySQL database with proper relationships
- JWT authentication for admin access
- File upload handling for vehicle images
- CRUD operations for vehicle management
- Image carousel management

### 🛡️ **Admin Panel**
- Secure login system (default: admin/admin123)
- Vehicle management dashboard
- Multiple image upload per vehicle
- Real-time preview of changes
- Vehicle status control (active/inactive)

## 🚀 **Quick Deployment Options**

### **Option 1: VPS Deployment (Recommended)**
```bash
# Clone repository to your VPS
git clone https://github.com/yourusername/fazona-ev.git /var/www/fazona.org
cd /var/www/fazona.org

# Run automated deployment
chmod +x deploy/quick-vps-setup.sh
./deploy/quick-vps-setup.sh
```

### **Option 2: cPanel Hosting**
1. Upload files from `cpanel-upload/` folder to your cPanel
2. Create MySQL database and import SQL file
3. Configure database credentials
4. Access admin panel at `yourdomain.com/admin`

### **Option 3: Local Development**
```bash
# Install dependencies
npm install

# Setup database
npm run db:setup

# Start development servers
npm run dev:full
```

## 🌐 **Live Access Points**

- **Main Website:** https://fazona.org
- **Admin Panel:** https://fazona.org/admin
- **API Endpoints:** https://fazona.org/api

## 🔐 **Default Admin Credentials**

- **Username:** admin
- **Password:** admin123
- **⚠️ Change immediately after first login!**

## 📁 **Project Structure**

```
fazona-ev/
├── src/                     # React frontend source
├── server/                  # Node.js backend
├── deploy/                  # VPS deployment scripts
├── cpanel-upload/          # cPanel hosting files
├── supabase/migrations/    # Database setup scripts
├── public/                 # Static assets
└── dist/                   # Built frontend (generated)
```

## 🛠️ **Technology Stack**

- **Frontend:** React, TypeScript, Tailwind CSS, Framer Motion
- **Backend:** Node.js, Express.js, MySQL
- **Authentication:** JWT
- **File Upload:** Multer
- **Deployment:** PM2, Nginx, Let's Encrypt SSL

## 📊 **Database Schema**

- `admin_users` - Admin authentication
- `vehicles` - Vehicle information
- `vehicle_images` - Multiple images per vehicle with primary image support

## 🔧 **Environment Variables**

```env
DB_HOST=localhost
DB_USER=your_db_user
DB_PASSWORD=your_db_password
DB_NAME=fazona_ev
DB_PORT=3306
JWT_SECRET=your_jwt_secret
NODE_ENV=production
PORT=5000
```

## 📞 **Support & Contact**

- **Email:** evfazona@gmail.com
- **WhatsApp:** +234 913 585 9888
- **Instagram:** @fazona_ev

## 🎯 **Deployment Features**

✅ **Complete Full-Stack Application**
✅ **Automated SSL Certificate Setup**
✅ **Database Backup System**
✅ **Health Monitoring Scripts**
✅ **Production Optimization**
✅ **Security Configuration**

## 📈 **Performance**

- Optimized build with code splitting
- Image optimization and lazy loading
- Gzip compression
- CDN-ready static assets
- Database query optimization

## 🔒 **Security**

- JWT token authentication
- SQL injection protection
- File upload validation
- CORS configuration
- Rate limiting
- SSL/HTTPS enforcement

---

**Drive the Future Today with FaZona EV!** 🚗⚡

Built with ❤️ for sustainable transportation in Nigeria.