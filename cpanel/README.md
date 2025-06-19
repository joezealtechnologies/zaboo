# FaZona EV - cPanel Deployment Guide

## 🎯 **cPanel Hosting Setup for fazona.org**

This guide will help you deploy FaZona EV to your cPanel hosting account.

### 📋 **Prerequisites**

- cPanel hosting account with Node.js support
- MySQL database access
- File Manager or FTP access
- Domain: fazona.org

---

## 🚀 **Quick Deployment Steps**

### **Step 1: Prepare Files for Upload**

```bash
# On your local machine, build the project
npm install
npm run build
```

### **Step 2: Upload to cPanel**

1. **Upload built files** to `public_html/`
2. **Upload backend** to a separate folder (e.g., `api/`)
3. **Setup database** through cPanel

### **Step 3: Configure**

1. Update database settings
2. Set up Node.js app (if supported)
3. Configure domain settings

---

## 📁 **File Structure for cPanel**

```
public_html/                 # Your domain root
├── index.html              # Built React app
├── assets/                 # CSS, JS, images
├── fazona/                 # Vehicle images
└── .htaccess              # URL rewriting

api/                        # Backend folder
├── server/
├── package.json
├── .env
└── uploads/
```

---

## ⚙️ **Configuration Files Included**

- `.htaccess` - URL rewriting for React Router
- `cpanel.yml` - Node.js app configuration
- `package.json` - Production dependencies only
- Database setup scripts
- Environment configuration

---

## 🔧 **Setup Instructions**

1. **Build locally:** `npm run build`
2. **Upload files** using File Manager or FTP
3. **Create database** in cPanel
4. **Configure Node.js app** (if available)
5. **Update DNS** if needed

---

## 📞 **Support**

If your hosting doesn't support Node.js:
- Frontend will work as static files
- Backend features will need alternative hosting
- Contact your hosting provider for Node.js support

---

**Your website will be live at: https://fazona.org** 🚗⚡