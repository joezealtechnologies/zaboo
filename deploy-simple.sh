#!/bin/bash

# Simple Deployment Script for Basic VPS
# Use this if the full deployment script is too complex

echo "🚀 Simple FaZona EV Deployment"

# Install dependencies
npm install

# Build frontend
npm run build

# Setup environment if needed
if [ ! -f .env ]; then
    cp server/.env.example .env
    echo "⚠️  Edit .env file with your database details"
fi

# Try to setup database
npm run db:setup || echo "⚠️  Database setup failed - configure manually"

# Start with PM2 if available, otherwise with node
if command -v pm2 &> /dev/null; then
    pm2 delete fazona-ev-backend 2>/dev/null || true
    pm2 start server/index.js --name fazona-ev-backend
    pm2 save
else
    echo "🔄 Starting with Node.js (install PM2 for production)"
    nohup node server/index.js > app.log 2>&1 &
fi

echo "✅ Basic deployment completed!"
echo "🌐 Check your application at your domain"
echo "🔧 Admin panel: /admin (admin/admin123)"