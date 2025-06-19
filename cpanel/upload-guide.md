# 📁 **File Upload Guide for cPanel**

## 🎯 **What to Upload Where**

### **1. Frontend Files (public_html/)**
After running `npm run build`, upload these to your domain root:

```
public_html/
├── index.html              ← From dist/index.html
├── assets/                 ← From dist/assets/
│   ├── index-[hash].js
│   ├── index-[hash].css
│   └── [other assets]
├── fazona/                 ← Copy from public/fazona/
│   ├── FaZona.png
│   ├── LogoFaZona.png
│   ├── 20millionnaira.jpg
│   └── [other images]
└── .htaccess              ← From cpanel/.htaccess
```

### **2. Backend Files (if Node.js supported)**
Upload to a subfolder like `api/` or `backend/`:

```
api/
├── server/
│   ├── index.js
│   ├── database-setup.js
│   └── uploads/
├── package.json           ← Use cpanel/package-production.json
├── .env                   ← Configure for your hosting
└── node_modules/          ← Will be created by npm install
```

---

## 🔧 **Step-by-Step Upload Process**

### **Step 1: Build the Project**
```bash
# On your computer
npm install
npm run build
```

### **Step 2: Prepare Files**
1. Copy `dist/` contents
2. Copy `public/fazona/` folder
3. Copy `cpanel/.htaccess`
4. Copy backend files (if needed)

### **Step 3: Upload via cPanel File Manager**
1. Login to cPanel
2. Open **File Manager**
3. Navigate to `public_html/`
4. Upload and extract files
5. Set proper permissions (755 for folders, 644 for files)

### **Step 4: Database Setup**
1. Go to **MySQL Databases** in cPanel
2. Create new database: `fazona_ev`
3. Create database user with full permissions
4. Run the SQL from `cpanel/database-setup.sql`

### **Step 5: Configure Environment**
Create `.env` file in your backend folder:
```env
DB_HOST=localhost
DB_USER=your_cpanel_db_user
DB_PASSWORD=your_db_password
DB_NAME=your_cpanel_db_name
DB_PORT=3306
JWT_SECRET=your_secret_key
NODE_ENV=production
```

---

## 🌐 **If Node.js is NOT Supported**

Your hosting might not support Node.js. In this case:

### **Frontend Only Setup**
1. Upload just the `dist/` contents to `public_html/`
2. Upload `.htaccess` for React Router
3. Website will work as static site
4. Admin panel won't work (needs backend)

### **Alternative Backend Options**
1. **Use a different hosting service** for the backend (Heroku, Railway, etc.)
2. **Upgrade hosting plan** to include Node.js
3. **Use serverless functions** (Vercel, Netlify)

---

## 📞 **Check Node.js Support**

Contact your hosting provider and ask:
- "Do you support Node.js applications?"
- "Can I run Express.js apps?"
- "What Node.js versions are available?"

---

## ✅ **Verification Steps**

After upload:
1. Visit `https://fazona.org` - should show the website
2. Check `https://fazona.org/admin` - should show admin login
3. Test image loading
4. Test contact forms

---

## 🆘 **Common Issues**

### **React Router not working**
- Make sure `.htaccess` is uploaded
- Check if mod_rewrite is enabled

### **Images not loading**
- Check file paths in uploaded files
- Verify image permissions (644)

### **Admin panel not working**
- Backend might not be running
- Check database connection
- Verify Node.js support

---

**Need help?** Check with your hosting provider about Node.js support! 🚀