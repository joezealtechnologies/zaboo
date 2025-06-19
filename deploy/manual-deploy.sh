#!/bin/bash

# Manual Build and Deploy Script for FaZona EV
# Use this if you want more control over the build process

set -e

echo "🏗️ Manual Build and Deploy Process"
echo "=================================="

APP_DIR="/var/www/fazona.org"
DOMAIN="fazona.org"

# Step 1: Build locally or on server
echo "📦 Step 1: Installing dependencies..."
npm install

echo "🏗️ Step 2: Building frontend..."
npm run build

echo "✅ Build completed! Files are in ./dist/"
echo ""

# Step 3: Setup server (if not done already)
echo "🔧 Step 3: Server setup..."
read -p "Have you run the initial server setup? (y/n): " setup_done

if [ "$setup_done" != "y" ]; then
    echo "Running server setup..."
    
    # Install system packages
    sudo apt update && sudo apt upgrade -y
    sudo apt install -y nginx mysql-server nodejs npm git certbot python3-certbot-nginx ufw
    
    # Install Node.js 18+
    curl -fsSL https://deb.nodesource.com/setup_18.x | sudo -E bash -
    sudo apt-get install -y nodejs
    
    # Install PM2
    sudo npm install -g pm2
    
    echo "✅ Server setup completed!"
fi

# Step 4: Database setup
echo "🗄️ Step 4: Database setup..."
read -p "Setup database? (y/n): " db_setup

if [ "$db_setup" = "y" ]; then
    # Setup database
    sudo mysql -e "CREATE DATABASE IF NOT EXISTS fazona_ev;"
    sudo mysql -e "CREATE USER IF NOT EXISTS 'fazona_user'@'localhost' IDENTIFIED BY 'secure_password_here';"
    sudo mysql -e "GRANT ALL PRIVILEGES ON fazona_ev.* TO 'fazona_user'@'localhost';"
    sudo mysql -e "FLUSH PRIVILEGES;"
    
    # Run database migrations
    npm run db:setup
    
    echo "✅ Database setup completed!"
fi

# Step 5: Environment configuration
echo "⚙️ Step 5: Environment setup..."
if [ ! -f .env ]; then
    cp deploy/production.env .env
    echo "📝 Please edit .env file with your configuration:"
    echo "nano .env"
    read -p "Press Enter after editing .env file..."
fi

# Step 6: Nginx configuration
echo "🌐 Step 6: Nginx setup..."
sudo cp deploy/nginx.conf /etc/nginx/sites-available/$DOMAIN
sudo ln -sf /etc/nginx/sites-available/$DOMAIN /etc/nginx/sites-enabled/
sudo rm -f /etc/nginx/sites-enabled/default

# Test Nginx config
sudo nginx -t

# Step 7: SSL Certificate
echo "🔒 Step 7: SSL Certificate..."
read -p "Setup SSL certificate? (y/n): " ssl_setup

if [ "$ssl_setup" = "y" ]; then
    sudo certbot --nginx -d $DOMAIN -d www.$DOMAIN
fi

# Step 8: Start application
echo "🚀 Step 8: Starting application..."

# Create uploads directory
mkdir -p server/uploads
chmod 755 server/uploads

# Start with PM2
pm2 start deploy/ecosystem.config.js --env production
pm2 save
pm2 startup

# Restart Nginx
sudo systemctl restart nginx

# Setup firewall
sudo ufw allow 22/tcp
sudo ufw allow 80/tcp
sudo ufw allow 443/tcp
sudo ufw --force enable

echo ""
echo "✅ Manual deployment completed!"
echo "🌐 Website: https://$DOMAIN"
echo "🔧 Admin: https://$DOMAIN/admin"
echo "📊 Monitor: pm2 monit"
echo ""
echo "🔑 Default admin login:"
echo "Username: admin"
echo "Password: admin123"
echo ""
echo "⚠️ Remember to change the admin password!"