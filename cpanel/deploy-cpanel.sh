#!/bin/bash

# FaZona EV - cPanel Deployment Preparation Script
# Run this on your local machine to prepare files for cPanel upload

echo "🚀 Preparing FaZona EV for cPanel deployment..."

# Create deployment directory
mkdir -p cpanel-deploy
cd cpanel-deploy

# Build the frontend
echo "🏗️ Building frontend..."
cd ..
npm install
npm run build

# Copy built files to deployment folder
echo "📁 Preparing files for upload..."
cp -r dist/* cpanel-deploy/
cp -r public/fazona cpanel-deploy/
cp cpanel/.htaccess cpanel-deploy/

# Prepare backend files (if hosting supports Node.js)
mkdir -p cpanel-deploy/api
cp -r server cpanel-deploy/api/
cp cpanel/package-production.json cpanel-deploy/api/package.json
cp cpanel/database-setup.sql cpanel-deploy/api/

# Create environment template
cat > cpanel-deploy/api/.env << EOF
# Update these with your cPanel database details
DB_HOST=localhost
DB_USER=your_cpanel_db_user
DB_PASSWORD=your_db_password
DB_NAME=your_cpanel_db_name
DB_PORT=3306
JWT_SECRET=your_secret_key_here
NODE_ENV=production
PORT=3000
EOF

# Create upload instructions
cat > cpanel-deploy/UPLOAD-INSTRUCTIONS.txt << EOF
FaZona EV - cPanel Upload Instructions
=====================================

1. FRONTEND FILES (Upload to public_html/):
   - All files in this folder EXCEPT the 'api' folder
   - Make sure .htaccess is uploaded

2. BACKEND FILES (Upload to api/ or backend/ folder):
   - Contents of the 'api' folder
   - Edit .env file with your database details
   - Run 'npm install' in cPanel Node.js interface

3. DATABASE SETUP:
   - Create MySQL database in cPanel
   - Run the SQL commands from database-setup.sql

4. VERIFY:
   - Visit https://fazona.org
   - Test admin panel at https://fazona.org/admin
   - Login: admin / admin123

Need help? Check the README files!
EOF

echo "✅ Files prepared for cPanel upload!"
echo ""
echo "📁 Files are ready in: ./cpanel-deploy/"
echo ""
echo "📋 Next steps:"
echo "1. Upload contents to your cPanel File Manager"
echo "2. Follow UPLOAD-INSTRUCTIONS.txt"
echo "3. Setup database in cPanel MySQL"
echo "4. Configure .env file"
echo ""
echo "🌐 Your website will be live at: https://fazona.org"