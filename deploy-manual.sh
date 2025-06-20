#!/bin/bash

# Manual Step-by-Step Deployment
# Guides you through each step

echo "🚀 Manual FaZona EV Deployment Guide"
echo "===================================="

# Function to wait for user confirmation
wait_for_user() {
    echo ""
    read -p "Press Enter to continue or Ctrl+C to exit..."
    echo ""
}

echo "This script will guide you through deploying FaZona EV step by step."
wait_for_user

# Step 1: Check Node.js
echo "Step 1: Checking Node.js..."
if command -v node &> /dev/null; then
    echo "✅ Node.js found: $(node --version)"
else
    echo "❌ Node.js not found"
    echo "🔧 Please install Node.js:"
    echo "   wget https://nodejs.org/dist/v18.19.0/node-v18.19.0-linux-x64.tar.xz"
    echo "   tar -xf node-v18.19.0-linux-x64.tar.xz"
    echo "   sudo cp -r node-v18.19.0-linux-x64/* /usr/local/"
    wait_for_user
fi

# Step 2: Check npm
echo "Step 2: Checking npm..."
if command -v npm &> /dev/null; then
    echo "✅ npm found: $(npm --version)"
else
    echo "❌ npm not found"
    echo "🔧 npm should come with Node.js. Try reinstalling Node.js."
    wait_for_user
fi

# Step 3: Install dependencies
echo "Step 3: Installing project dependencies..."
echo "Running: npm install"
wait_for_user
npm install || {
    echo "❌ npm install failed"
    echo "🔧 Try: npm install --no-optional"
    wait_for_user
    npm install --no-optional || exit 1
}
echo "✅ Dependencies installed"

# Step 4: Environment setup
echo "Step 4: Setting up environment..."
if [ ! -f .env ]; then
    cat > .env << EOF
DB_HOST=localhost
DB_USER=root
DB_PASSWORD=
DB_NAME=fazona_ev
DB_PORT=3306
JWT_SECRET=your_secret_key_here
NODE_ENV=production
PORT=5000
EOF
    echo "✅ Environment file created"
    echo "🔧 Please edit .env with your database details:"
    echo "   nano .env"
    wait_for_user
else
    echo "✅ Environment file exists"
fi

# Step 5: Database setup
echo "Step 5: Database setup..."
echo "🔧 Make sure MySQL is installed and running"
echo "🔧 Create database and user manually if needed:"
echo "   mysql -e \"CREATE DATABASE fazona_ev;\""
echo "   mysql -e \"CREATE USER 'fazona_user'@'localhost' IDENTIFIED BY 'password';\""
echo "   mysql -e \"GRANT ALL ON fazona_ev.* TO 'fazona_user'@'localhost';\""
wait_for_user

echo "Running database setup..."
npm run db:setup || echo "⚠️  Database setup failed - check your credentials"

# Step 6: Build frontend
echo "Step 6: Building frontend..."
echo "Running: npm run build"
wait_for_user
npm run build || {
    echo "❌ Build failed"
    echo "🔧 Try with more memory: NODE_OPTIONS=\"--max-old-space-size=2048\" npm run build"
    wait_for_user
    NODE_OPTIONS="--max-old-space-size=2048" npm run build || exit 1
}
echo "✅ Frontend built"

# Step 7: Create uploads directory
echo "Step 7: Creating uploads directory..."
mkdir -p server/uploads
chmod 755 server/uploads 2>/dev/null || true
echo "✅ Uploads directory created"

# Step 8: Start application
echo "Step 8: Starting application..."
echo "🔧 Choose how to start:"
echo "   1. With PM2 (recommended for production)"
echo "   2. With Node.js directly"
echo "   3. Manual start"
read -p "Enter choice (1-3): " choice

case $choice in
    1)
        if command -v pm2 &> /dev/null; then
            pm2 start server/index.js --name fazona-ev
            pm2 save
            echo "✅ Started with PM2"
        else
            echo "❌ PM2 not found. Installing..."
            npm install -g pm2 || {
                echo "❌ PM2 installation failed"
                echo "🔧 Starting with Node.js instead..."
                nohup node server/index.js > app.log 2>&1 &
                echo $! > app.pid
            }
        fi
        ;;
    2)
        nohup node server/index.js > app.log 2>&1 &
        echo $! > app.pid
        echo "✅ Started with Node.js (PID: $(cat app.pid))"
        ;;
    3)
        echo "🔧 To start manually, run:"
        echo "   node server/index.js"
        echo "   or"
        echo "   nohup node server/index.js > app.log 2>&1 &"
        ;;
esac

echo ""
echo "✅ Deployment completed!"
echo ""
echo "🌐 Your application should be running on:"
echo "   http://your-server-ip:5000"
echo ""
echo "🔧 Admin panel: /admin"
echo "🔑 Default credentials: admin / admin123"
echo ""
echo "📊 Check status:"
echo "   ps aux | grep node"
echo "   tail -f app.log"
if command -v pm2 &> /dev/null; then
    echo "   pm2 list"
    echo "   pm2 logs"
fi