# 🚀 FaZona EV Deployment Guide

## Quick Start

### VPS Deployment (Recommended)
```bash
git clone https://github.com/yourusername/fazona-ev.git /var/www/fazona.org
cd /var/www/fazona.org
chmod +x deploy/quick-vps-setup.sh
./deploy/quick-vps-setup.sh
```

### cPanel Deployment
1. Upload `cpanel-upload/` contents to your hosting
2. Create MySQL database
3. Import database schema
4. Configure `.env` file

## Environment Setup

Create `.env` file:
```env
DB_HOST=localhost
DB_USER=your_db_user
DB_PASSWORD=your_db_password
DB_NAME=fazona_ev
JWT_SECRET=your_jwt_secret
NODE_ENV=production
```

## Default Credentials

- Username: admin
- Password: admin123
- ⚠️ Change after first login!

## Access Points

- Website: https://fazona.org
- Admin: https://fazona.org/admin
- API: https://fazona.org/api

For detailed deployment instructions, see `VPS-DEPLOYMENT-GUIDE.md`.