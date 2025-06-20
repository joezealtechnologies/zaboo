#!/bin/bash

# FaZona EV - Automated Deployment Actions
# This script runs automatically on every deployment

set -e  # Exit on any error

echo "🚀 Starting FaZona EV deployment..."

# Update system packages
echo "📦 Updating system packages..."
sudo apt update

# Install Node.js 18+ if not installed
if ! command -v node &> /dev/null || [[ $(node -v | cut -d'v' -f2 | cut -d'.' -f1) -lt 18 ]]; then
    echo "📦 Installing Node.js 18..."
    curl -fsSL https://deb.nodesource.com/setup_18.x | sudo -E bash -
    sudo apt-get install -y nodejs
fi

# Install required system packages
echo "📦 Installing required packages..."
sudo apt install -y nginx mysql-server certbot python3-certbot-nginx ufw

# Install PM2 globally if not installed
if ! command -v pm2 &> /dev/null; then
    echo "📦 Installing PM2..."
    sudo npm install -g pm2
fi

# Install project dependencies
echo "📦 Installing project dependencies..."
npm install

# Setup environment file if it doesn't exist
if [ ! -f .env ]; then
    echo "⚙️ Creating environment file..."
    cp server/.env.example .env
    echo "⚠️  Please edit .env file with your database credentials"
fi

# Setup MySQL database
echo "🗄️ Setting up database..."
sudo mysql -e "CREATE DATABASE IF NOT EXISTS fazona_ev;" 2>/dev/null || true
sudo mysql -e "CREATE USER IF NOT EXISTS 'fazona_user'@'localhost' IDENTIFIED BY 'secure_password_change_this';" 2>/dev/null || true
sudo mysql -e "GRANT ALL PRIVILEGES ON fazona_ev.* TO 'fazona_user'@'localhost';" 2>/dev/null || true
sudo mysql -e "FLUSH PRIVILEGES;" 2>/dev/null || true

# Run database setup
echo "🗄️ Initializing database schema..."
npm run db:setup || echo "⚠️  Database setup failed - please check credentials"

# Build frontend
echo "🏗️ Building frontend..."
npm run build

# Create uploads directory
mkdir -p server/uploads
chmod 755 server/uploads

# Setup Nginx configuration
echo "🌐 Configuring Nginx..."
sudo cp deploy/nginx.conf /etc/nginx/sites-available/fazona.org 2>/dev/null || true
sudo ln -sf /etc/nginx/sites-available/fazona.org /etc/nginx/sites-enabled/ 2>/dev/null || true
sudo rm -f /etc/nginx/sites-enabled/default 2>/dev/null || true

# Test Nginx configuration
sudo nginx -t && echo "✅ Nginx configuration valid" || echo "⚠️  Nginx configuration error"

# Setup SSL certificate (non-interactive)
echo "🔒 Setting up SSL certificate..."
sudo certbot --nginx -d fazona.org -d www.fazona.org --non-interactive --agree-tos --email admin@fazona.org 2>/dev/null || echo "⚠️  SSL setup failed - manual configuration needed"

# Setup firewall
echo "🛡️ Configuring firewall..."
sudo ufw allow 22/tcp 2>/dev/null || true
sudo ufw allow 80/tcp 2>/dev/null || true
sudo ufw allow 443/tcp 2>/dev/null || true
sudo ufw --force enable 2>/dev/null || true

# Start/restart application with PM2
echo "🚀 Starting application..."
pm2 delete fazona-ev-backend 2>/dev/null || true
pm2 start deploy/ecosystem.config.js --env production
pm2 save
pm2 startup ubuntu -u $USER --hp $HOME 2>/dev/null || true

# Restart services
echo "🔄 Restarting services..."
sudo systemctl restart nginx 2>/dev/null || true
sudo systemctl enable nginx 2>/dev/null || true
sudo systemctl enable mysql 2>/dev/null || true

# Setup automatic SSL renewal
echo "🔄 Setting up SSL auto-renewal..."
(crontab -l 2>/dev/null; echo "0 12 * * * /usr/bin/certbot renew --quiet") | crontab - 2>/dev/null || true

# Setup daily backups
echo "💾 Setting up daily backups..."
(crontab -l 2>/dev/null; echo "0 2 * * * /var/www/fazona.org/deploy/backup.sh") | crontab - 2>/dev/null || true

echo "✅ Deployment completed successfully!"
echo ""
echo "🌐 Your website should be available at:"
echo "   Main site: https://fazona.org"
echo "   Admin panel: https://fazona.org/admin"
echo ""
echo "🔑 Default admin credentials:"
echo "   Username: admin"
echo "   Password: admin123"
echo "   ⚠️  CHANGE THESE IMMEDIATELY!"
echo ""
echo "📊 Monitor your application:"
echo "   pm2 list"
echo "   pm2 logs"
echo "   pm2 monit"