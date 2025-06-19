#!/bin/bash

# FaZona EV Deployment Script
# Run this script on your VPS to deploy the application

set -e  # Exit on any error

echo "🚀 Starting FaZona EV deployment..."

# Configuration
APP_NAME="fazona-ev"
APP_DIR="/var/www/fazona.org"
REPO_URL="https://github.com/yourusername/fazona-ev.git"  # Update with your repo
DOMAIN="fazona.org"
DB_NAME="fazona_ev"
DB_USER="fazona_user"

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Function to print colored output
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

# Update system packages
print_status "Updating system packages..."
sudo apt update && sudo apt upgrade -y

# Install required packages
print_status "Installing required packages..."
sudo apt install -y curl wget git nginx mysql-server nodejs npm certbot python3-certbot-nginx ufw

# Install Node.js 18+ (if not already installed)
print_status "Installing Node.js 18..."
curl -fsSL https://deb.nodesource.com/setup_18.x | sudo -E bash -
sudo apt-get install -y nodejs

# Install PM2 globally
print_status "Installing PM2..."
sudo npm install -g pm2

# Create application directory
print_status "Creating application directory..."
sudo mkdir -p $APP_DIR
sudo chown $USER:$USER $APP_DIR

# Clone or update repository
if [ -d "$APP_DIR/.git" ]; then
    print_status "Updating existing repository..."
    cd $APP_DIR
    git pull origin main
else
    print_status "Cloning repository..."
    git clone $REPO_URL $APP_DIR
    cd $APP_DIR
fi

# Install dependencies
print_status "Installing Node.js dependencies..."
npm install

# Build frontend
print_status "Building frontend..."
npm run build

# Setup MySQL database
print_status "Setting up MySQL database..."
sudo mysql -e "CREATE DATABASE IF NOT EXISTS $DB_NAME;"
sudo mysql -e "CREATE USER IF NOT EXISTS '$DB_USER'@'localhost' IDENTIFIED BY 'secure_password_here';"
sudo mysql -e "GRANT ALL PRIVILEGES ON $DB_NAME.* TO '$DB_USER'@'localhost';"
sudo mysql -e "FLUSH PRIVILEGES;"

# Run database setup
print_status "Setting up database schema..."
npm run db:setup

# Setup environment file
print_status "Setting up environment variables..."
if [ ! -f .env ]; then
    cp deploy/production.env .env
    print_warning "Please edit .env file with your actual configuration"
fi

# Setup uploads directory
print_status "Setting up uploads directory..."
mkdir -p server/uploads
chmod 755 server/uploads

# Setup logging directory
print_status "Setting up logging..."
sudo mkdir -p /var/log/pm2
sudo mkdir -p /var/log/fazona-ev
sudo chown $USER:$USER /var/log/pm2
sudo chown $USER:$USER /var/log/fazona-ev

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

# Setup log rotation
print_status "Setting up log rotation..."
sudo tee /etc/logrotate.d/fazona-ev > /dev/null <<EOF
/var/log/fazona-ev/*.log {
    daily
    missingok
    rotate 52
    compress
    delaycompress
    notifempty
    create 644 $USER $USER
}
EOF

print_status "✅ Deployment completed successfully!"
echo ""
echo "🌐 Your website should now be available at: https://$DOMAIN"
echo "🔧 Admin panel: https://$DOMAIN/admin"
echo "📊 Monitor with: pm2 monit"
echo "📝 View logs with: pm2 logs"
echo ""
print_warning "Don't forget to:"
echo "1. Update .env file with your actual database credentials"
echo "2. Change default admin password (admin/admin123)"
echo "3. Configure your domain's DNS to point to this server"
echo "4. Test all functionality"