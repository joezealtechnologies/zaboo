# ✅ **FaZona EV Upload Checklist**

## 📋 **Pre-Upload Checklist**

- [ ] cPanel login details ready
- [ ] Domain pointing to hosting account
- [ ] File Manager or FTP access available

---

## 🚀 **Upload Process**

### **1. Website Files (public_html/)**
Upload these to your domain root:
- [ ] `index.html` ← Main website file
- [ ] `assets/` folder ← **CSS, JS, and all styling files**
- [ ] `fazona/` folder ← Vehicle images
- [ ] `.htaccess` file ← React Router support

### **2. Backend Files (public_html/api/) - If Node.js Supported**
- [ ] Create `api/` folder in public_html
- [ ] Upload all contents of `api/` folder
- [ ] Edit `api/.env` with database details

### **3. Database Setup - If Node.js Supported**
- [ ] cPanel → MySQL Databases
- [ ] Create database: `fazonaev`
- [ ] Create user: `dbuser`
- [ ] Add user to database (ALL PRIVILEGES)
- [ ] phpMyAdmin → Import `database-setup.sql`

---

## 🔧 **Configuration**

### **Database Details Template:**
```
Host: localhost
Name: [your_username]_fazonaev
User: [your_username]_dbuser
Pass: [your_password]
Port: 3306
```

### **Update .env File:**
```env
DB_HOST=localhost
DB_USER=[your_username]_dbuser
DB_PASSWORD=[your_password]
DB_NAME=[your_username]_fazonaev
```

---

## ✅ **Testing After Upload**

### **Basic Tests:**
- [ ] Website loads: https://fazona.org
- [ ] CSS styling works (check if assets folder uploaded)
- [ ] Images display correctly
- [ ] Navigation works
- [ ] Contact forms work

### **If Node.js Supported:**
- [ ] Admin panel: https://fazona.org/admin
- [ ] Login works: admin/admin123
- [ ] Vehicle management works
- [ ] Image uploads work

---

## 🆘 **Troubleshooting**

### **Website not loading:**
- [ ] Check if all files uploaded correctly
- [ ] Verify `.htaccess` is in place
- [ ] Check file permissions (755 for folders, 644 for files)

### **No styling (looks broken):**
- [ ] Verify `assets/` folder uploaded completely
- [ ] Check if CSS files are in assets folder
- [ ] Check browser console for 404 errors

### **Admin panel not working:**
- [ ] Verify Node.js support with hosting provider
- [ ] Check database connection
- [ ] Ensure `.env` file configured correctly

### **Images not showing:**
- [ ] Check `fazona/` folder uploaded
- [ ] Verify image file permissions
- [ ] Check file paths in browser console

---

## 📞 **Support Questions**

**Ask your hosting provider:**
- "Do you support Node.js applications?"
- "Can I run Express.js apps?"
- "How do I enable Node.js for my domain?"

---

## 🎉 **Success!**

When everything works:
- ✅ Beautiful website at https://fazona.org
- ✅ Full styling and animations
- ✅ All images loading
- ✅ Contact forms working
- ✅ Admin panel (if Node.js supported)

**🔒 Don't forget to change the admin password from admin123!**

---

**Your FaZona EV website is ready to impress visitors!** 🚗⚡✨