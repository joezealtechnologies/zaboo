# 🎯 **Complete cPanel Database Setup**

## 🗄️ **Step 1: Create Database in cPanel**

### **Access MySQL Databases:**
1. Login to your cPanel
2. Find **"MySQL Databases"** (usually in Databases section)
3. Click on it

### **Create New Database:**
1. **Database Name:** Enter `fazonaev`
   - cPanel will make it: `yourusername_fazonaev`
2. Click **"Create Database"**

### **Create Database User:**
1. **Username:** Enter `dbuser`
   - cPanel will make it: `yourusername_dbuser`
2. **Password:** Create a strong password
3. Click **"Create User"**

### **Add User to Database:**
1. Select your user from dropdown
2. Select your database from dropdown
3. Click **"Add"**
4. **Check "ALL PRIVILEGES"**
5. Click **"Make Changes"**

---

## 📝 **Step 2: Note Your Database Details**

Write down these details (with your actual cPanel username):

```
Database Host: localhost
Database Name: yourusername_fazonaev
Database User: yourusername_dbuser
Database Password: [your chosen password]
Database Port: 3306
```

---

## ⚙️ **Step 3: Create .env File**

In your backend folder, create `.env` file:

```env
# Replace with your actual details
DB_HOST=localhost
DB_USER=yourusername_dbuser
DB_PASSWORD=your_actual_password
DB_NAME=yourusername_fazonaev
DB_PORT=3306

# Generate a random string for this
JWT_SECRET=your_super_long_random_jwt_secret_key

# Production settings
NODE_ENV=production
PORT=3000
DOMAIN=fazona.org
```

---

## 🗃️ **Step 4: Import Database Schema**

### **Option A: Using cPanel phpMyAdmin**
1. Go to **"phpMyAdmin"** in cPanel
2. Select your database
3. Click **"Import"** tab
4. Upload `database-setup.sql` file
5. Click **"Go"**

### **Option B: Using SQL tab**
1. Go to **"phpMyAdmin"**
2. Select your database
3. Click **"SQL"** tab
4. Copy and paste the SQL from `cpanel/database-setup.sql`
5. Click **"Go"**

---

## 🧪 **Step 5: Test Connection**

If your hosting supports Node.js:
1. Upload your backend files
2. Install dependencies: `npm install`
3. Test: `node server/test-connection.js`

---

## 📋 **Quick Reference**

### **Your Database Details Template:**
```
Host: localhost
Name: [cpanel_username]_fazonaev
User: [cpanel_username]_dbuser
Pass: [your_password]
Port: 3306
```

### **Default Admin Login:**
- Username: `admin`
- Password: `admin123`
- ⚠️ **Change this immediately!**

---

## 🔍 **Finding Your cPanel Username**

Not sure of your cPanel username?
- Check your hosting welcome email
- Look at the cPanel URL: `cpanel.yourdomain.com/cpanel`
- Contact your hosting provider
- Check existing databases in cPanel (they'll show the prefix)

---

**Once you have these details, put them in your `.env` file and you're ready to go!** 🚀