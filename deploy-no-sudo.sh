#!/bin/bash

# FaZona EV - Deployment Without Sudo
# For environments where sudo is not available

set -e  # Exit on any error

echo "🚀 Starting FaZona EV deployment (no sudo)..."

# Check if we're running as root
if [ "$EUID" -eq 0 ]; then
    echo "✅ Running as root - proceeding with installation"
    SUDO_CMD=""
else
    echo "⚠️  Not running as root - some features may not work"
    SUDO_CMD=""
fi

# Update system packages (if possible)
echo "📦 Updating system packages..."
apt update 2>/dev/null || echo "⚠️  Cannot update packages - continuing anyway"

# Install Node.js 18+ if not installed
if ! command -v node &> /dev/null || [[ $(node -v | cut -d'v' -f2 | cut -d'.' -f1) -lt 18 ]]; then
    echo "📦 Installing Node.js 18..."
    curl -fsSL https://deb.nodesource.com/setup_18.x | bash - 2>/dev/null || echo "⚠️  Node.js installation failed"
    apt-get install -y nodejs 2>/dev/null || echo "⚠️  Node.js installation failed"
fi

# Install required packages (if possible)
echo "📦 Installing required packages..."
apt install -y nginx mysql-server 2>/dev/null || echo "⚠️  Some packages may not be installed"

# Install PM2 globally if not installed
if ! command -v pm2 &> /dev/null; then
    echo "📦 Installing PM2..."
    npm install -g pm2 2>/dev/null || echo "⚠️  PM2 installation failed - will use node directly"
fi

# Install project dependencies
echo "📦 Installing project dependencies..."
npm install

# Setup environment file if it doesn't exist
if [ ! -f .env ]; then
    echo "⚙️ Creating environment file..."
    cat > .env << EOF
# Database Configuration
DB_HOST=localhost
DB_USER=root
DB_PASSWORD=
DB_NAME=fazona_ev
DB_PORT=3306

# Application Configuration
JWT_SECRET=fazona_ev_secret_key_change_this_in_production
NODE_ENV=production
PORT=5000

# Domain Configuration
DOMAIN=\${DOMAIN:-localhost}
FRONTEND_URL=https://\${DOMAIN:-localhost}
BACKEND_URL=https://\${DOMAIN:-localhost}/api
EOF
    echo "✅ Environment file created - please edit with your settings"
fi

# Setup MySQL database (if MySQL is available)
echo "🗄️ Setting up database..."
if command -v mysql &> /dev/null; then
    mysql -e "CREATE DATABASE IF NOT EXISTS fazona_ev;" 2>/dev/null || echo "⚠️  Database creation failed"
    mysql -e "CREATE USER IF NOT EXISTS 'fazona_user'@'localhost' IDENTIFIED BY 'secure_password';" 2>/dev/null || echo "⚠️  User creation failed"
    mysql -e "GRANT ALL PRIVILEGES ON fazona_ev.* TO 'fazona_user'@'localhost';" 2>/dev/null || echo "⚠️  Permission grant failed"
    mysql -e "FLUSH PRIVILEGES;" 2>/dev/null || echo "⚠️  Privilege flush failed"
else
    echo "⚠️  MySQL not found - please setup database manually"
fi

# Run database setup
echo "🗄️ Initializing database schema..."
npm run db:setup || echo "⚠️  Database setup failed - please check credentials"

# Build frontend
echo "🏗️ Building frontend..."
npm run build

# Create uploads directory
mkdir -p server/uploads
chmod 755 server/uploads 2>/dev/null || echo "⚠️  Cannot set permissions"

# Create a simple nginx config (if nginx is available)
if command -v nginx &> /dev/null; then
    echo "🌐 Creating basic Nginx configuration..."
    mkdir -p /etc/nginx/sites-available /etc/nginx/sites-enabled 2>/dev/null || echo "⚠️  Cannot create nginx directories"
    
    cat > /tmp/fazona.conf << 'EOF'
server {
    listen 80;
    server_name _;
    
    # Serve static files
    location / {
        root /var/www/fazona.org/dist;
        try_files $uri $uri/ /index.html;
    }
    
    # API proxy
    location /api/ {
        proxy_pass http://localhost:5000;
        proxy_set_header Host $host;
        proxy_set_header X-Real-IP $remote_addr;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
        proxy_set_header X-Forwarded-Proto $scheme;
    }
    
    # Uploads
    location /uploads/ {
        proxy_pass http://localhost:5000;
        proxy_set_header Host $host;
    }
}
EOF
    
    cp /tmp/fazona.conf /etc/nginx/sites-available/fazona.org 2>/dev/null || echo "⚠️  Cannot copy nginx config"
    ln -sf /etc/nginx/sites-available/fazona.org /etc/nginx/sites-enabled/ 2>/dev/null || echo "⚠️  Cannot enable nginx site"
    rm -f /etc/nginx/sites-enabled/default 2>/dev/null || echo "⚠️  Cannot remove default site"
    
    # Test and restart nginx
    nginx -t 2>/dev/null && echo "✅ Nginx configuration valid" || echo "⚠️  Nginx configuration error"
    systemctl restart nginx 2>/dev/null || service nginx restart 2>/dev/null || echo "⚠️  Cannot restart nginx"
fi

# Start application
echo "🚀 Starting application..."

# Try PM2 first, fallback to node
if command -v pm2 &> /dev/null; then
    pm2 delete fazona-ev-backend 2>/dev/null || true
    pm2 start server/index.js --name fazona-ev-backend
    pm2 save 2>/dev/null || echo "⚠️  Cannot save PM2 config"
    pm2 startup 2>/dev/null || echo "⚠️  Cannot setup PM2 startup"
else
    echo "🔄 Starting with Node.js (PM2 not available)"
    # Kill any existing process
    pkill -f "node server/index.js" 2>/dev/null || true
    # Start in background
    nohup node server/index.js > app.log 2>&1 &
    echo $! > app.pid
    echo "✅ Application started with PID: $(cat app.pid)"
fi

echo ""
echo "✅ Deployment completed!"
echo ""
echo "🌐 Your application should be running on:"
echo "   Port 5000: http://localhost:5000"
echo "   If nginx is configured: http://your-domain"
echo ""
echo "🔧 Admin panel: /admin"
echo "🔑 Default credentials: admin / admin123"
echo ""
echo "📊 Check application status:"
if command -v pm2 &> /dev/null; then
    echo "   pm2 list"
    echo "   pm2 logs"
else
    echo "   tail -f app.log"
    echo "   ps aux | grep node"
fi
echo ""
echo "⚠️  Important next steps:"
echo "1. Edit .env file with your database credentials"
echo "2. Change admin password after first login"
echo "3. Configure your domain/DNS settings"
echo "4. Setup SSL certificate if needed"