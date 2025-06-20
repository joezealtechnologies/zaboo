#!/bin/bash

# FaZona EV - VPS Deployment Fix
# Handles missing curl certificates and npm

set -e  # Exit on any error

echo "🚀 Starting FaZona EV VPS deployment (with fixes)..."

# Fix curl certificate issues
echo "🔧 Fixing curl certificate issues..."
if [ -f /etc/redhat-release ]; then
    # CentOS/RHEL/Rocky Linux
    yum update -y ca-certificates 2>/dev/null || true
    yum install -y curl wget ca-certificates 2>/dev/null || true
elif [ -f /etc/debian_version ]; then
    # Debian/Ubuntu
    apt-get update 2>/dev/null || true
    apt-get install -y curl wget ca-certificates 2>/dev/null || true
else
    echo "⚠️  Unknown OS - trying generic fixes..."
fi

# Alternative curl with no certificate verification (temporary fix)
CURL_CMD="curl -k"  # -k flag ignores SSL certificate errors

# Check if we're running as root
if [ "$EUID" -eq 0 ]; then
    echo "✅ Running as root"
    INSTALL_CMD="yum install -y"
    if command -v apt-get &> /dev/null; then
        INSTALL_CMD="apt-get install -y"
    fi
else
    echo "⚠️  Not running as root - limited functionality"
    INSTALL_CMD="echo 'Cannot install:'"
fi

# Install Node.js and npm (multiple methods)
if ! command -v node &> /dev/null; then
    echo "📦 Installing Node.js..."
    
    # Method 1: Try package manager
    $INSTALL_CMD nodejs npm 2>/dev/null || echo "Package manager install failed"
    
    # Method 2: Try NodeSource repository (with fixed curl)
    if ! command -v node &> /dev/null; then
        echo "📦 Trying NodeSource repository..."
        $CURL_CMD -fsSL https://deb.nodesource.com/setup_18.x | bash - 2>/dev/null || echo "NodeSource failed"
        $INSTALL_CMD nodejs 2>/dev/null || echo "NodeSource install failed"
    fi
    
    # Method 3: Try downloading binary directly
    if ! command -v node &> /dev/null; then
        echo "📦 Downloading Node.js binary..."
        cd /tmp
        $CURL_CMD -O https://nodejs.org/dist/v18.19.0/node-v18.19.0-linux-x64.tar.xz 2>/dev/null || echo "Binary download failed"
        if [ -f node-v18.19.0-linux-x64.tar.xz ]; then
            tar -xf node-v18.19.0-linux-x64.tar.xz
            cp -r node-v18.19.0-linux-x64/* /usr/local/ 2>/dev/null || echo "Binary install failed"
        fi
        cd - > /dev/null
    fi
    
    # Method 4: Try alternative package names
    if ! command -v node &> /dev/null; then
        $INSTALL_CMD node npm 2>/dev/null || echo "Alternative package names failed"
    fi
fi

# Check if npm is available
if ! command -v npm &> /dev/null; then
    echo "❌ npm is still not available. Trying alternatives..."
    
    # Try installing npm separately
    $INSTALL_CMD npm 2>/dev/null || echo "npm install failed"
    
    # If still no npm, try yarn as alternative
    if ! command -v npm &> /dev/null && command -v yarn &> /dev/null; then
        echo "📦 Using yarn instead of npm..."
        alias npm=yarn
    fi
fi

# Verify Node.js installation
if command -v node &> /dev/null; then
    echo "✅ Node.js version: $(node --version)"
else
    echo "❌ Node.js installation failed"
    echo "🔧 Manual installation required:"
    echo "   1. Download Node.js from https://nodejs.org/"
    echo "   2. Extract and install manually"
    echo "   3. Or contact your VPS provider for Node.js support"
    exit 1
fi

if command -v npm &> /dev/null; then
    echo "✅ npm version: $(npm --version)"
else
    echo "❌ npm not available - trying to continue anyway..."
fi

# Install project dependencies
echo "📦 Installing project dependencies..."
if command -v npm &> /dev/null; then
    npm install || {
        echo "❌ npm install failed. Trying with different flags..."
        npm install --no-optional --no-audit --no-fund || {
            echo "❌ npm install still failing. Trying yarn..."
            if command -v yarn &> /dev/null; then
                yarn install
            else
                echo "❌ All package installation methods failed"
                exit 1
            fi
        }
    }
else
    echo "❌ Cannot install dependencies - npm not available"
    exit 1
fi

# Setup environment file
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
    echo "✅ Environment file created"
fi

# Install MySQL if not available
if ! command -v mysql &> /dev/null; then
    echo "📦 Installing MySQL..."
    $INSTALL_CMD mysql-server mysql-client 2>/dev/null || echo "⚠️  MySQL installation failed"
fi

# Setup database
echo "🗄️ Setting up database..."
if command -v mysql &> /dev/null; then
    mysql -e "CREATE DATABASE IF NOT EXISTS fazona_ev;" 2>/dev/null || echo "⚠️  Database creation failed"
    mysql -e "CREATE USER IF NOT EXISTS 'fazona_user'@'localhost' IDENTIFIED BY 'secure_password';" 2>/dev/null || echo "⚠️  User creation failed"
    mysql -e "GRANT ALL PRIVILEGES ON fazona_ev.* TO 'fazona_user'@'localhost';" 2>/dev/null || echo "⚠️  Permission grant failed"
    mysql -e "FLUSH PRIVILEGES;" 2>/dev/null || echo "⚠️  Privilege flush failed"
    
    # Run database setup
    npm run db:setup || echo "⚠️  Database schema setup failed"
else
    echo "⚠️  MySQL not available - database setup skipped"
fi

# Build frontend
echo "🏗️ Building frontend..."
npm run build || {
    echo "❌ Build failed. Trying with increased memory..."
    NODE_OPTIONS="--max-old-space-size=4096" npm run build || {
        echo "❌ Build still failing - check for errors above"
        exit 1
    }
}

# Create uploads directory
mkdir -p server/uploads
chmod 755 server/uploads 2>/dev/null || echo "⚠️  Cannot set permissions"

# Install PM2 if possible
if ! command -v pm2 &> /dev/null; then
    echo "📦 Installing PM2..."
    npm install -g pm2 2>/dev/null || echo "⚠️  PM2 installation failed"
fi

# Start application
echo "🚀 Starting application..."
if command -v pm2 &> /dev/null; then
    pm2 delete fazona-ev-backend 2>/dev/null || true
    pm2 start server/index.js --name fazona-ev-backend
    pm2 save 2>/dev/null || echo "⚠️  Cannot save PM2 config"
else
    echo "🔄 Starting with Node.js..."
    pkill -f "node server/index.js" 2>/dev/null || true
    nohup node server/index.js > app.log 2>&1 &
    echo $! > app.pid
    echo "✅ Application started with PID: $(cat app.pid)"
fi

# Install and configure nginx if possible
if ! command -v nginx &> /dev/null; then
    echo "📦 Installing nginx..."
    $INSTALL_CMD nginx 2>/dev/null || echo "⚠️  nginx installation failed"
fi

if command -v nginx &> /dev/null; then
    echo "🌐 Configuring nginx..."
    mkdir -p /etc/nginx/sites-available /etc/nginx/sites-enabled 2>/dev/null || true
    
    cat > /tmp/fazona.conf << 'EOF'
server {
    listen 80 default_server;
    server_name _;
    
    # Serve static files
    location / {
        root /var/www/fazona.org/dist;
        try_files $uri $uri/ /index.html;
        index index.html;
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
    
    cp /tmp/fazona.conf /etc/nginx/sites-available/default 2>/dev/null || echo "⚠️  Cannot copy nginx config"
    
    # Test and restart nginx
    nginx -t 2>/dev/null && echo "✅ nginx configuration valid" || echo "⚠️  nginx configuration error"
    systemctl restart nginx 2>/dev/null || service nginx restart 2>/dev/null || nginx -s reload 2>/dev/null || echo "⚠️  Cannot restart nginx"
fi

echo ""
echo "✅ Deployment completed!"
echo ""
echo "🌐 Your application should be accessible at:"
echo "   Direct: http://your-server-ip:5000"
if command -v nginx &> /dev/null; then
    echo "   Via nginx: http://your-server-ip"
fi
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
echo "3. Point your domain to this server"
echo "4. Setup SSL certificate for production"