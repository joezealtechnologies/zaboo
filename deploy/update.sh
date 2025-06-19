#!/bin/bash

# FaZona EV Update Script
# Use this script to update your deployed application

set -e

APP_DIR="/var/www/fazona.org"
BACKUP_DIR="/var/backups/fazona-ev"

echo "🔄 Starting FaZona EV update..."

# Create backup directory
sudo mkdir -p $BACKUP_DIR

# Backup database
echo "📦 Creating database backup..."
mysqldump -u fazona_user -p fazona_ev > $BACKUP_DIR/fazona_ev_$(date +%Y%m%d_%H%M%S).sql

# Backup uploads
echo "📦 Backing up uploads..."
sudo cp -r $APP_DIR/server/uploads $BACKUP_DIR/uploads_$(date +%Y%m%d_%H%M%S)

# Navigate to app directory
cd $APP_DIR

# Pull latest changes
echo "⬇️ Pulling latest changes..."
git pull origin main

# Install/update dependencies
echo "📦 Installing dependencies..."
npm install

# Build frontend
echo "🏗️ Building frontend..."
npm run build

# Restart PM2 processes
echo "🔄 Restarting application..."
pm2 restart all

# Reload Nginx
echo "🔄 Reloading Nginx..."
sudo nginx -s reload

echo "✅ Update completed successfully!"
echo "🌐 Check your website: https://fazona.org"