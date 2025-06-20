# 🚀 **FaZona EV - Complete cPanel Upload Guide**

## 📁 **What's in This Package**

Your complete deployment package contains:
- ✅ Built React website (ready for upload)
- ✅ Backend API server (Node.js)
- ✅ Database setup script
- ✅ Configuration files
- ✅ All vehicle images
- ✅ Assets folder with CSS/JS

---

## 🎯 **Quick Upload Steps**

### **Step 1: Upload Website Files**
Upload to your `public_html/` folder:
- `index.html`
- `assets/` folder (CSS, JS, etc.) ← **This is now included!**
- `fazona/` folder (vehicle images)
- `.htaccess` file

### **Step 2: Upload Backend (if Node.js supported)**
Create folder `public_html/api/` and upload:
- All contents of `api/` folder
- Edit `api/.env` with your database details

### **Step 3: Setup Database**
1. Go to cPanel **MySQL Databases**
2. Create database: `fazonaev`
3. Create user: `dbuser` 
4. Add user to database with ALL PRIVILEGES
5. Go to **phpMyAdmin**
6. Import `database-setup.sql`

---

## 🗄️ **Database Configuration**

### **Your Database Details Will Be:**
```
Database Name: yourusername_fazonaev
Database User: yourusername_dbuser
Database Host: localhost
Database Port: 3306
```

### **Update .env File:**
Edit `api/.env` with your actual details:
```env
DB_HOST=localhost
DB_USER=yourusername_dbuser
DB_PASSWORD=your_chosen_password
DB_NAME=yourusername_fazonaev
```

---

## ✅ **After Upload - Test Your Site**

1. **Visit:** https://fazona.org
   - Should show your beautiful website ✅

2. **Visit:** https://fazona.org/admin
   - Should show admin login ✅

3. **Login with:**
   - Username: `admin`
   - Password: `admin123`
   - **⚠️ Change this password immediately!**

---

## 🌐 **If Node.js Not Supported**

Don't worry! Your website will still work perfectly:

**✅ What Works:**
- Beautiful website display
- Vehicle showcase with images
- Contact forms (opens email)
- All animations and interactions

**❌ What Needs Node.js:**
- Admin panel for managing vehicles
- Dynamic content updates
- Image uploads

**💡 Solution:** Contact your hosting provider about Node.js support or upgrade your plan.

---

## 📞 **Need Help?**

### **Check Node.js Support:**
Ask your hosting provider:
- "Do you support Node.js applications?"
- "Can I run Express.js apps?"
- "What Node.js versions are available?"

### **Common Issues:**
- **React Router not working:** Make sure `.htaccess` is uploaded
- **Images not loading:** Check file permissions (644 for files, 755 for folders)
- **Admin panel not working:** Backend needs Node.js support

---

## 🎉 **You're Ready!**

Your FaZona EV website is ready to go live at **https://fazona.org**

**Default Admin Login:**
- Username: `admin`
- Password: `admin123`
- **🔒 Change this immediately after first login!**

---

**Everything is built and ready - just upload and configure!** 🚗⚡