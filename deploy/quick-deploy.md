# Quick Deployment Options for FaZona EV

## 🚀 Option 1: Fully Automated (Easiest)

```bash
# Just run this - it builds everything automatically
./deploy/deploy.sh
```

**What it does:**
- ✅ Installs all system dependencies
- ✅ Sets up database
- ✅ Builds frontend automatically
- ✅ Configures Nginx
- ✅ Sets up SSL
- ✅ Starts the application

---

## 🔧 Option 2: Manual Control

### Step 1: Build Frontend
```bash
# Build the React app
npm install
npm run build
```

### Step 2: Deploy
```bash
# Run manual deployment
./deploy/manual-deploy.sh
```

---

## 📦 Option 3: Build Only

```bash
# Just build without deploying
./deploy/build-only.sh
```

---

## 🎯 What Gets Built

When you run `npm run build`, it creates:

```
dist/
├── index.html          # Main HTML file
├── assets/
│   ├── index-[hash].js  # Bundled JavaScript
│   ├── index-[hash].css # Bundled CSS
│   └── images/         # Optimized images
└── fazona/             # Your vehicle images
```

---

## 🌐 After Deployment

Your website will be available at:
- **Main site:** https://fazona.org
- **Admin panel:** https://fazona.org/admin
- **API:** https://fazona.org/api

---

## 🔑 Default Admin Login

- **Username:** admin
- **Password:** admin123
- **⚠️ Change this immediately after first login!**

---

## 📊 Monitor Your Deployment

```bash
# Check application status
pm2 list
pm2 monit

# Check logs
pm2 logs

# System health
./deploy/monitoring.sh
```

---

## 🆘 If Something Goes Wrong

```bash
# Restart application
pm2 restart all

# Restart Nginx
sudo systemctl restart nginx

# Check what's running
sudo netstat -tlnp | grep :80
sudo netstat -tlnp | grep :443
sudo netstat -tlnp | grep :5000
```

---

## 💡 Pro Tips

1. **Always backup before updates:**
   ```bash
   ./deploy/backup.sh
   ```

2. **Update your app:**
   ```bash
   ./deploy/update.sh
   ```

3. **Monitor regularly:**
   ```bash
   ./deploy/monitoring.sh
   ```

The deployment scripts handle everything for you - no manual building required! 🎉