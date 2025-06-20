#!/bin/bash

# FaZona EV - Quick VPS Setup Script
# Run this on your VPS to deploy everything automatically

set -e

echo "🚀 FaZona EV VPS Deployment Starting..."
echo "======================================"

# Configuration
DOMAIN="fazona.org"
DB_NAME="fazona_ev"
DB_USER="fazona_user"
DB_PASSWORD=$(openssl rand -base64 32)
JWT_SECRET=$(openssl rand -base64 64)
APP_DIR="/var/www/fazona.org"

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

print_status() {
    echo -e "${GREEN}[INFO]${NC} $1"
}

print_warning() {
    echo -e "${YELLOW}[WARNING]${NC} $1"
}

print_error() {
    echo -e "${RED}[ERROR]${NC} $1"
}

# Check if running as root
if [[ $EUID -eq 0 ]]; then
   print_error "This script should not be run as root for security reasons"
   exit 1
fi

# Update system
print_status "Updating system packages..."
sudo apt update && sudo apt upgrade -y

# Install required packages
print_status "Installing required packages..."
sudo apt install -y curl wget git nginx mysql-server certbot python3-certbot-nginx ufw

# Install Node.js 18+
print_status "Installing Node.js 18..."
curl -fsSL https://deb.nodesource.com/setup_18.x | sudo -E bash -
sudo apt-get install -y nodejs

# Install PM2
print_status "Installing PM2..."
sudo npm install -g pm2

# Create application directory
print_status "Setting up application directory..."
sudo mkdir -p $APP_DIR
sudo chown $USER:$USER $APP_DIR

# Check if we're already in the project directory
if [ ! -f "package.json" ]; then
    print_error "Please run this script from the FaZona EV project root directory"
    print_error "Or clone the repository first: git clone <repo-url> $APP_DIR"
    exit 1
fi

# Copy files to app directory if not already there
if [ "$PWD" != "$APP_DIR" ]; then
    print_status "Copying project files..."
    sudo cp -r . $APP_DIR/
    sudo chown -R $USER:$USER $APP_DIR
    cd $APP_DIR
fi

# Install dependencies
print_status "Installing Node.js dependencies..."
npm install

# Setup MySQL database
print_status "Setting up MySQL database..."
sudo mysql -e "CREATE DATABASE IF NOT EXISTS $DB_NAME;"
sudo mysql -e "CREATE USER IF NOT EXISTS '$DB_USER'@'localhost' IDENTIFIED BY '$DB_PASSWORD';"
sudo mysql -e "GRANT ALL PRIVILEGES ON $DB_NAME.* TO '$DB_USER'@'localhost';"
sudo mysql -e "FLUSH PRIVILEGES;"

# Create environment file
print_status "Creating environment configuration..."
cat > .env << EOF
# Database Configuration
DB_HOST=localhost
DB_USER=$DB_USER
DB_PASSWORD=$DB_PASSWORD
DB_NAME=$DB_NAME
DB_PORT=3306

# Application Configuration
JWT_SECRET=$JWT_SECRET
NODE_ENV=production
PORT=5000

# Domain Configuration
DOMAIN=$DOMAIN
FRONTEND_URL=https://$DOMAIN
BACKEND_URL=https://$DOMAIN/api

# Security
CORS_ORIGIN=https://$DOMAIN
EOF

# Setup database schema
print_status "Setting up database schema..."
npm run db:setup

# Build frontend
print_status "Building frontend..."
npm run build

# Setup Nginx
print_status "Configuring Nginx..."
sudo cp deploy/nginx.conf /etc/nginx/sites-available/$DOMAIN
sudo ln -sf /etc/nginx/sites-available/$DOMAIN /etc/nginx/sites-enabled/
sudo rm -f /etc/nginx/sites-enabled/default

# Test Nginx configuration
sudo nginx -t

# Setup SSL certificate
print_status "Setting up SSL certificate..."
sudo certbot --nginx -d $DOMAIN -d www.$DOMAIN --non-interactive --agree-tos --email admin@$DOMAIN

# Setup firewall
print_status "Configuring firewall..."
sudo ufw allow 22/tcp
sudo ufw allow 80/tcp
sudo ufw allow 443/tcp
sudo ufw --force enable

# Create uploads directory
mkdir -p server/uploads
chmod 755 server/uploads

# Start application with PM2
print_status "Starting application..."
pm2 start deploy/ecosystem.config.js --env production
pm2 save
pm2 startup

# Restart services
print_status "Restarting services..."
sudo systemctl restart nginx
sudo systemctl enable nginx
sudo systemctl enable mysql

# Setup automatic SSL renewal
print_status "Setting up automatic SSL renewal..."
echo "0 12 * * * /usr/bin/certbot renew --quiet" | sudo crontab -

# Setup daily backups
print_status "Setting up daily backups..."
echo "0 2 * * * $APP_DIR/deploy/backup.sh" | crontab -

print_status "✅ Deployment completed successfully!"
echo ""
echo "🌐 Your website is now available at: https://$DOMAIN"
echo "🔧 Admin panel: https://$DOMAIN/admin"
echo "📊 Monitor with: pm2 monit"
echo "📝 View logs with: pm2 logs"
echo ""
print_warning "IMPORTANT SECURITY STEPS:"
echo "1. Login to admin panel: https://$DOMAIN/admin"
echo "2. Default credentials: admin / admin123"
echo "3. CHANGE THE ADMIN PASSWORD IMMEDIATELY!"
echo "4. Database password: $DB_PASSWORD"
echo "5. JWT Secret: $JWT_SECRET"
echo ""
echo "📋 Saved configuration in: $APP_DIR/.env"
echo ""
print_status "🎉 FaZona EV is now live and ready to use!"