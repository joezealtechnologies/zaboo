# 🚀 **FaZona EV - cPanel Upload Instructions**

## 📁 **What to Upload Where**

### **1. Website Files (Upload to public_html/)**
Upload these files to your domain root folder:
- `index.html`
- `assets/` folder
- `fazona/` folder (vehicle images)
- `.htaccess` file

### **2. Backend Files (Upload to api/ subfolder)**
If your hosting supports Node.js:
- Create folder: `public_html/api/`
- Upload `api/` folder contents there
- Edit `.env` file with your database details

---

## 🗄️ **Database Setup**

### **Step 1: Create Database in cPanel**
1. Go to **MySQL Databases**
2. Create database: `fazonaev` (becomes `username_fazonaev`)
3. Create user: `dbuser` (becomes `username_dbuser`)
4. Add user to database with ALL PRIVILEGES

### **Step 2: Import Database**
1. Go to **phpMyAdmin**
2. Select your database
3. Click **Import** tab
4. Upload `database-setup.sql`
5. Click **Go**

### **Step 3: Update .env File**
Edit `api/.env` with your actual database details:
```env
DB_HOST=localhost
DB_USER=yourusername_dbuser
DB_PASSWORD=your_password
DB_NAME=yourusername_fazonaev
```

---

## ✅ **Verification**

After upload:
1. Visit `https://fazona.org` - should show website
2. Visit `https://fazona.org/admin` - should show admin login
3. Login: `admin` / `admin123`
4. **Change admin password immediately!**

---

## 🆘 **If Node.js Not Supported**

Your website will still work perfectly as a static site!
- Main website: ✅ Works
- Vehicle showcase: ✅ Works  
- Contact forms: ✅ Works (opens email)
- Admin panel: ❌ Needs Node.js

---

## 📞 **Need Help?**

Contact your hosting provider and ask:
- "Do you support Node.js applications?"
- "Can I run Express.js apps?"

**Your website is ready to upload!** 🚗⚡