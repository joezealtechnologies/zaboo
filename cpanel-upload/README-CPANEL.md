# 📋 **FaZona EV - cPanel Deployment Checklist**

## ✅ **Pre-Upload Checklist**

- [ ] Built website files ready
- [ ] Backend files prepared
- [ ] Database script ready
- [ ] cPanel login details available

---

## 🚀 **Upload Process**

### **1. Website Files (public_html/)**
Upload these to your domain root:
- [ ] `index.html`
- [ ] `assets/` folder
- [ ] `fazona/` folder
- [ ] `.htaccess` file

### **2. Backend Files (public_html/api/)**
If Node.js supported:
- [ ] Create `api/` folder
- [ ] Upload `api/` contents
- [ ] Edit `.env` file

### **3. Database Setup**
- [ ] Create MySQL database in cPanel
- [ ] Create database user
- [ ] Import `database-setup.sql`
- [ ] Update `.env` with database details

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

## ✅ **Testing**

After upload, test:
- [ ] Website loads: https://fazona.org
- [ ] Admin panel: https://fazona.org/admin
- [ ] Login works: admin/admin123
- [ ] Images display correctly
- [ ] Contact forms work

---

## 🆘 **Troubleshooting**

### **Website not loading:**
- Check if all files uploaded correctly
- Verify `.htaccess` is in place
- Check file permissions

### **Admin panel not working:**
- Verify Node.js support with hosting provider
- Check database connection
- Ensure `.env` file is configured correctly

### **Images not showing:**
- Check `fazona/` folder uploaded
- Verify image file permissions
- Check file paths in browser console

---

## 📞 **Support**

**Hosting Provider Questions:**
- "Do you support Node.js applications?"
- "Can I run Express.js apps?"
- "How do I enable Node.js for my domain?"

**File Permissions:**
- Folders: 755
- Files: 644
- .htaccess: 644

---

**Your website will be live at https://fazona.org!** 🌐✨