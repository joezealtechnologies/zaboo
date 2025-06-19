# 🗄️ **Database Configuration Guide for cPanel**

## 📍 **Where to Add Database Info**

### **Location:** `api/.env` file (or root `.env` if backend in root)

---

## 🔧 **Step-by-Step Setup**

### **Step 1: Create Database in cPanel**

1. **Login to cPanel**
2. **Go to "MySQL Databases"**
3. **Create Database:**
   - Database Name: `fazonaev` (will become `yourusername_fazonaev`)
4. **Create Database User:**
   - Username: `dbuser` (will become `yourusername_dbuser`)
   - Password: Create a strong password
5. **Add User to Database:**
   - Select user and database
   - Grant **ALL PRIVILEGES**

### **Step 2: Get Database Details**

cPanel automatically adds your username as prefix:
- **Database Name:** `yourusername_fazonaev`
- **Database User:** `yourusername_dbuser`
- **Host:** Usually `localhost`
- **Port:** Usually `3306`

### **Step 3: Create .env File**

In your backend folder (`api/` or wherever you put server files):

```env
DB_HOST=localhost
DB_USER=yourusername_dbuser
DB_PASSWORD=your_actual_password
DB_NAME=yourusername_fazonaev
DB_PORT=3306
JWT_SECRET=make_this_a_very_long_random_string
NODE_ENV=production
```

### **Step 4: Upload and Test**

1. **Upload .env file** to your backend folder
2. **Run database setup** (if Node.js supported)
3. **Test connection**

---

## 📋 **Real Example**

If your cPanel username is `fazuser`:

```env
DB_HOST=localhost
DB_USER=fazuser_dbuser
DB_PASSWORD=MySecurePass123!
DB_NAME=fazuser_fazonaev
DB_PORT=3306
JWT_SECRET=super_long_random_string_for_jwt_security_12345
NODE_ENV=production
```

---

## 🔍 **How to Find Your Database Details**

### **In cPanel:**
1. Go to **"MySQL Databases"**
2. Scroll down to **"Current Databases"**
3. You'll see your database name with prefix
4. Scroll to **"Current Users"**
5. You'll see your username with prefix

### **Common cPanel Database Patterns:**
- **Database:** `cpanelusername_databasename`
- **User:** `cpanelusername_username`
- **Host:** `localhost` (99% of the time)
- **Port:** `3306` (default MySQL port)

---

## ⚠️ **Important Notes**

1. **Prefixes are automatic** - cPanel adds your username
2. **Use the FULL names** including prefixes in .env
3. **Keep .env file secure** - don't share passwords
4. **Test connection** after setup

---

## 🆘 **If You Can't Find Database Info**

1. **Check cPanel MySQL section**
2. **Contact your hosting provider**
3. **Look for "Database Connection Details" in hosting panel**
4. **Check hosting welcome email** (sometimes includes DB info)

---

## 🧪 **Test Your Database Connection**

If Node.js is supported, you can test with:
```bash
node server/test-connection.js
```

Or check the application logs for connection errors.

---

**Your database info goes in the `.env` file in your backend folder!** 🗄️✅