#!/bin/bash

# Minimal Deployment Script
# For very basic environments

echo "🚀 Minimal FaZona EV Deployment"

# Install dependencies
echo "📦 Installing dependencies..."
npm install || { echo "❌ npm install failed"; exit 1; }

# Create basic environment
echo "⚙️ Setting up environment..."
if [ ! -f .env ]; then
    cat > .env << EOF
DB_HOST=localhost
DB_USER=root
DB_PASSWORD=
DB_NAME=fazona_ev
DB_PORT=3306
JWT_SECRET=change_this_secret_key
NODE_ENV=production
PORT=5000
EOF
fi

# Build frontend
echo "🏗️ Building frontend..."
npm run build || { echo "❌ Build failed"; exit 1; }

# Create uploads directory
mkdir -p server/uploads

# Try database setup
echo "🗄️ Setting up database..."
npm run db:setup || echo "⚠️  Database setup failed - configure manually"

# Start application
echo "🚀 Starting application..."
if command -v pm2 &> /dev/null; then
    pm2 start server/index.js --name fazona-ev
else
    echo "Starting with node..."
    node server/index.js &
    echo $! > app.pid
fi

echo "✅ Deployment complete!"
echo "🌐 Application running on port 5000"
echo "🔧 Admin: /admin (admin/admin123)"