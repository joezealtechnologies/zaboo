#!/bin/bash

# Basic Deployment - No external dependencies
# For very restricted environments

echo "🚀 Basic FaZona EV Deployment"

# Check for Node.js
if ! command -v node &> /dev/null; then
    echo "❌ Node.js is required but not installed"
    echo "🔧 Please install Node.js first:"
    echo "   - Download from: https://nodejs.org/"
    echo "   - Or ask your VPS provider to install Node.js"
    exit 1
fi

# Check for npm
if ! command -v npm &> /dev/null; then
    echo "❌ npm is required but not installed"
    echo "🔧 npm usually comes with Node.js"
    echo "   - Reinstall Node.js from: https://nodejs.org/"
    echo "   - Or ask your VPS provider for npm access"
    exit 1
fi

echo "✅ Node.js version: $(node --version)"
echo "✅ npm version: $(npm --version)"

# Install dependencies
echo "📦 Installing dependencies..."
npm install --production || {
    echo "❌ npm install failed"
    echo "🔧 Trying alternative installation..."
    npm install --no-optional --no-audit --no-fund || {
        echo "❌ All installation methods failed"
        exit 1
    }
}

# Create environment file
if [ ! -f .env ]; then
    echo "⚙️ Creating environment file..."
    cat > .env << EOF
DB_HOST=localhost
DB_USER=root
DB_PASSWORD=
DB_NAME=fazona_ev
DB_PORT=3306
JWT_SECRET=change_this_secret_key_in_production
NODE_ENV=production
PORT=5000
EOF
    echo "✅ Environment file created - please edit with your settings"
fi

# Build frontend
echo "🏗️ Building frontend..."
npm run build || {
    echo "❌ Build failed"
    echo "🔧 Trying with more memory..."
    NODE_OPTIONS="--max-old-space-size=2048" npm run build || {
        echo "❌ Build still failed - check for errors"
        exit 1
    }
}

# Create uploads directory
mkdir -p server/uploads

# Try database setup (optional)
echo "🗄️ Attempting database setup..."
npm run db:setup || echo "⚠️  Database setup failed - configure manually"

# Start application
echo "🚀 Starting application..."

# Kill any existing process
pkill -f "node server/index.js" 2>/dev/null || true

# Start in background
nohup node server/index.js > app.log 2>&1 &
APP_PID=$!
echo $APP_PID > app.pid

echo "✅ Application started!"
echo "📊 Process ID: $APP_PID"
echo "🌐 Application running on port 5000"
echo "📝 Logs: tail -f app.log"
echo ""
echo "🔧 Admin panel: http://your-server:5000/admin"
echo "🔑 Default credentials: admin / admin123"
echo ""
echo "⚠️  Next steps:"
echo "1. Edit .env file: nano .env"
echo "2. Configure your database"
echo "3. Setup reverse proxy (nginx/apache)"
echo "4. Change admin password"