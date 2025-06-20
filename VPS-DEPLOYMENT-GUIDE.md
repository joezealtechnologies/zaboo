# 🚀 FaZona EV VPS Deployment Guide

## Prerequisites

- Ubuntu/Debian VPS with root access
- Domain name pointing to your server's IP
- At least 2GB RAM and 20GB storage

## Quick Deployment (Automated)

### 1. Clone Repository to VPS
```bash
# SSH into your VPS
ssh root@your-server-ip

# Clone the repository
git clone <your-repo-url> /var/www/fazona.org
cd /var/www/fazona.org

# Make deployment script executable
chmod +x deploy/deploy.sh
```

### 2. Run Automated Deployment
```bash
# This will install everything automatically
./deploy/deploy.sh
```

The automated script will:
- ✅ Install Node.js, MySQL, Nginx
- ✅ Setup database and tables
- ✅ Build the frontend
- ✅ Configure SSL certificates
- ✅ Start the application with PM2

## Manual Deployment (Step by Step)

### 1. System Setup
```bash
# Update system
sudo apt update && sudo apt upgrade -y

# Install required packages
sudo apt install -y nginx mysql-server nodejs npm git certbot python3-certbot-nginx ufw

# Install Node.js 18+
curl -fsSL https://deb.nodesource.com/setup_18.x | sudo -E bash -
sudo apt-get install -y nodejs

# Install PM2 globally
sudo npm install -g pm2
```

### 2. Database Setup
```bash
# Secure MySQL installation
sudo mysql_secure_installation

# Create database and user
sudo mysql -e "CREATE DATABASE fazona_ev;"
sudo mysql -e "CREATE USER 'fazona_user'@'localhost' IDENTIFIED BY 'your_secure_password';"
sudo mysql -e "GRANT ALL PRIVILEGES ON fazona_ev.* TO 'fazona_user'@'localhost';"
sudo mysql -e "FLUSH PRIVILEGES;"
```

### 3. Application Setup
```bash
# Navigate to project directory
cd /var/www/fazona.org

# Install dependencies
npm install

# Create environment file
cp server/.env.example .env

# Edit environment variables
nano .env
```

Update `.env` with your settings:
```env
DB_HOST=localhost
DB_USER=fazona_user
DB_PASSWORD=your_secure_password
DB_NAME=fazona_ev
DB_PORT=3306
JWT_SECRET=your_very_long_random_jwt_secret_key
NODE_ENV=production
PORT=5000
DOMAIN=fazona.org
FRONTEND_URL=https://fazona.org
```

### 4. Database Initialization
```bash
# Setup database tables and admin user
npm run db:setup

# Test database connection
npm run db:test
```

### 5. Build Frontend
```bash
# Build the React application
npm run build
```

### 6. Nginx Configuration
```bash
# Copy Nginx configuration
sudo cp deploy/nginx.conf /etc/nginx/sites-available/fazona.org
sudo ln -sf /etc/nginx/sites-available/fazona.org /etc/nginx/sites-enabled/
sudo rm -f /etc/nginx/sites-enabled/default

# Test Nginx configuration
sudo nginx -t
```

### 7. SSL Certificate
```bash
# Get SSL certificate from Let's Encrypt
sudo certbot --nginx -d fazona.org -d www.fazona.org
```

### 8. Start Application
```bash
# Start with PM2
pm2 start deploy/ecosystem.config.js --env production
pm2 save
pm2 startup

# Restart Nginx
sudo systemctl restart nginx
sudo systemctl enable nginx
```

### 9. Firewall Setup
```bash
sudo ufw allow 22/tcp
sudo ufw allow 80/tcp
sudo ufw allow 443/tcp
sudo ufw --force enable
```

## Post-Deployment

### 1. Verify Deployment
- Visit https://fazona.org (main website)
- Visit https://fazona.org/admin (admin panel)
- Login with: admin / admin123
- **⚠️ Change the admin password immediately!**

### 2. Monitor Application
```bash
# Check PM2 status
pm2 list
pm2 monit

# View logs
pm2 logs

# Check system health
./deploy/monitoring.sh
```

### 3. Setup Backups
```bash
# Add to crontab for daily backups
crontab -e
# Add: 0 2 * * * /var/www/fazona.org/deploy/backup.sh
```

## Updating Your Application

```bash
cd /var/www/fazona.org
./deploy/update.sh
```

## Troubleshooting

### Common Issues

1. **502 Bad Gateway**
   ```bash
   pm2 restart all
   sudo systemctl restart nginx
   ```

2. **Database Connection Issues**
   ```bash
   npm run db:test
   sudo systemctl status mysql
   ```

3. **SSL Certificate Issues**
   ```bash
   sudo certbot renew
   sudo certbot certificates
   ```

### Check Logs
```bash
# Application logs
pm2 logs

# Nginx logs
sudo tail -f /var/log/nginx/error.log

# System logs
journalctl -u nginx -f
```

## Environment Variables Reference

```env
# Database Configuration
DB_HOST=localhost
DB_USER=fazona_user
DB_PASSWORD=your_secure_password
DB_NAME=fazona_ev
DB_PORT=3306

# Application Configuration
JWT_SECRET=your_very_long_random_jwt_secret_key
NODE_ENV=production
PORT=5000

# Domain Configuration
DOMAIN=fazona.org
FRONTEND_URL=https://fazona.org
BACKEND_URL=https://fazona.org/api

# Security
CORS_ORIGIN=https://fazona.org
```

## Default Admin Credentials

- **Username:** admin
- **Password:** admin123
- **Email:** admin@fazonaev.com

**🔒 IMPORTANT: Change these credentials immediately after first login!**

## File Structure on VPS

```
/var/www/fazona.org/
├── dist/                 # Built frontend files (served by Nginx)
├── server/              # Backend Node.js application
├── deploy/              # Deployment scripts
├── .env                 # Environment variables
├── package.json         # Dependencies
└── ecosystem.config.js  # PM2 configuration
```

## Success Indicators

When everything is working:
- ✅ Website loads at https://fazona.org
- ✅ Admin panel accessible at https://fazona.org/admin
- ✅ SSL certificate is valid
- ✅ PM2 shows running processes
- ✅ Database connection successful
- ✅ All vehicle data displays correctly

Your FaZona EV website will be live with full backend functionality! 🚗⚡