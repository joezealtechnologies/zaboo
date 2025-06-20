#!/bin/bash

# Docker-based Deployment
# For containerized environments

echo "🐳 Docker-based FaZona EV Deployment"

# Install dependencies
npm install

# Build frontend
npm run build

# Create environment file
if [ ! -f .env ]; then
    cat > .env << EOF
DB_HOST=localhost
DB_USER=root
DB_PASSWORD=
DB_NAME=fazona_ev
DB_PORT=3306
JWT_SECRET=docker_secret_key
NODE_ENV=production
PORT=5000
EOF
fi

# Create uploads directory
mkdir -p server/uploads

# Start application directly with node
echo "🚀 Starting application with Node.js..."
node server/index.js &
echo $! > app.pid

echo "✅ Application started!"
echo "📊 Process ID: $(cat app.pid)"
echo "🌐 Running on port 5000"