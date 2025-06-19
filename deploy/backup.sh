#!/bin/bash

# FaZona EV Backup Script
# Run this regularly to backup your data

BACKUP_DIR="/var/backups/fazona-ev"
DATE=$(date +%Y%m%d_%H%M%S)
APP_DIR="/var/www/fazona.org"

echo "📦 Starting backup process..."

# Create backup directory
mkdir -p $BACKUP_DIR

# Backup database
echo "💾 Backing up database..."
mysqldump -u fazona_user -p fazona_ev | gzip > $BACKUP_DIR/database_$DATE.sql.gz

# Backup uploads
echo "📁 Backing up uploads..."
tar -czf $BACKUP_DIR/uploads_$DATE.tar.gz -C $APP_DIR/server uploads

# Backup configuration
echo "⚙️ Backing up configuration..."
cp $APP_DIR/.env $BACKUP_DIR/env_$DATE.backup

# Clean old backups (keep last 30 days)
echo "🧹 Cleaning old backups..."
find $BACKUP_DIR -name "*.sql.gz" -mtime +30 -delete
find $BACKUP_DIR -name "*.tar.gz" -mtime +30 -delete
find $BACKUP_DIR -name "*.backup" -mtime +30 -delete

echo "✅ Backup completed successfully!"
echo "📍 Backups stored in: $BACKUP_DIR"