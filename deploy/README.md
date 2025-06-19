# FaZona EV Deployment Guide

This guide will help you deploy the FaZona EV website to your VPS at fazona.org.

## Prerequisites

- Ubuntu/Debian VPS with root access
- Domain name (fazona.org) pointing to your server's IP
- At least 2GB RAM and 20GB storage

## Quick Deployment

1. **Clone the repository to your VPS:**
```bash
git clone <your-repo-url> /var/www/fazona.org
cd /var/www/fazona.org
```

2. **Run the deployment script:**
```bash
chmod +x deploy/deploy.sh
./deploy/deploy.sh
```

3. **Configure environment variables:**
```bash
nano .env
# Update with your actual database credentials and settings
```

4. **Test the deployment:**
```bash
curl -I https://fazona.org
```

## Manual Deployment Steps

### 1. System Setup

```bash
# Update system
sudo apt update && sudo apt upgrade -y

# Install required packages
sudo apt install -y nginx mysql-server nodejs npm git certbot python3-certbot-nginx ufw

# Install Node.js 18+
curl -fsSL https://deb.nodesource.com/setup_18.x | sudo -E bash -
sudo apt-get install -y nodejs

# Install PM2
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
# Clone repository
git clone <your-repo-url> /var/www/fazona.org
cd /var/www/fazona.org

# Install dependencies
npm install

# Setup environment
cp deploy/production.env .env
nano .env  # Edit with your settings

# Setup database
npm run db:setup

# Build frontend
npm run build
```

### 4. Nginx Configuration

```bash
# Copy Nginx config
sudo cp deploy/nginx.conf /etc/nginx/sites-available/fazona.org
sudo ln -sf /etc/nginx/sites-available/fazona.org /etc/nginx/sites-enabled/
sudo rm -f /etc/nginx/sites-enabled/default

# Test configuration
sudo nginx -t

# Get SSL certificate
sudo certbot --nginx -d fazona.org -d www.fazona.org
```

### 5. Start Services

```bash
# Start application with PM2
pm2 start deploy/ecosystem.config.js --env production
pm2 save
pm2 startup

# Start Nginx
sudo systemctl restart nginx
sudo systemctl enable nginx
```

### 6. Firewall Setup

```bash
sudo ufw allow 22/tcp
sudo ufw allow 80/tcp
sudo ufw allow 443/tcp
sudo ufw --force enable
```

## Post-Deployment

### 1. Change Default Admin Password
- Visit https://fazona.org/admin
- Login with admin/admin123
- Change the password immediately

### 2. Test All Features
- [ ] Website loads correctly
- [ ] Admin panel accessible
- [ ] Vehicle management works
- [ ] Image uploads work
- [ ] Contact forms work

### 3. Setup Monitoring
```bash
# Check application status
pm2 monit

# View logs
pm2 logs

# System health check
./deploy/monitoring.sh
```

### 4. Setup Backups
```bash
# Add to crontab for daily backups
crontab -e
# Add: 0 2 * * * /var/www/fazona.org/deploy/backup.sh
```

## Maintenance

### Update Application
```bash
cd /var/www/fazona.org
./deploy/update.sh
```

### View Logs
```bash
# PM2 logs
pm2 logs

# Nginx logs
sudo tail -f /var/log/nginx/access.log
sudo tail -f /var/log/nginx/error.log

# System logs
journalctl -u nginx -f
```

### Backup Data
```bash
./deploy/backup.sh
```

### Monitor System
```bash
./deploy/monitoring.sh
```

## Troubleshooting

### Common Issues

1. **502 Bad Gateway**
   - Check if PM2 processes are running: `pm2 list`
   - Restart application: `pm2 restart all`

2. **SSL Certificate Issues**
   - Renew certificate: `sudo certbot renew`
   - Check certificate status: `sudo certbot certificates`

3. **Database Connection Issues**
   - Check MySQL status: `sudo systemctl status mysql`
   - Test connection: `npm run db:test`

4. **File Upload Issues**
   - Check uploads directory permissions: `ls -la server/uploads`
   - Fix permissions: `chmod 755 server/uploads`

### Performance Optimization

1. **Enable Gzip Compression** (already in nginx.conf)
2. **Optimize Images** before uploading
3. **Monitor Resource Usage**:
   ```bash
   htop
   df -h
   free -h
   ```

### Security Checklist

- [ ] Change default admin password
- [ ] Update all system packages regularly
- [ ] Monitor access logs for suspicious activity
- [ ] Keep SSL certificates updated
- [ ] Regular database backups
- [ ] Firewall properly configured

## Support

For issues during deployment:
1. Check the logs: `pm2 logs` and `/var/log/nginx/error.log`
2. Verify all services are running: `systemctl status nginx mysql`
3. Test database connection: `npm run db:test`
4. Check firewall settings: `sudo ufw status`

## Environment Variables Reference

```env
# Database
DB_HOST=localhost
DB_USER=fazona_user
DB_PASSWORD=your_secure_password
DB_NAME=fazona_ev
DB_PORT=3306

# Application
NODE_ENV=production
PORT=5000
JWT_SECRET=your_jwt_secret

# Domain
DOMAIN=fazona.org
FRONTEND_URL=https://fazona.org
```

Your FaZona EV website should now be live at https://fazona.org! 🚗⚡