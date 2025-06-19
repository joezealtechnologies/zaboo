#!/bin/bash

# Build Only Script - Just builds the frontend
# Use this if you only want to build without deploying

echo "🏗️ Building FaZona EV Frontend..."

# Check if we're in the right directory
if [ ! -f "package.json" ]; then
    echo "❌ Error: package.json not found. Run this from the project root."
    exit 1
fi

# Install dependencies if node_modules doesn't exist
if [ ! -d "node_modules" ]; then
    echo "📦 Installing dependencies..."
    npm install
fi

# Build the frontend
echo "🔨 Building frontend..."
npm run build

# Check if build was successful
if [ -d "dist" ]; then
    echo "✅ Build successful!"
    echo "📁 Built files are in: ./dist/"
    echo "📊 Build size:"
    du -sh dist/
    echo ""
    echo "🚀 Ready to deploy!"
    echo "You can now:"
    echo "1. Upload the 'dist' folder to your server"
    echo "2. Or run the full deployment script"
else
    echo "❌ Build failed!"
    exit 1
fi