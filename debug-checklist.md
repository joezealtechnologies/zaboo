# 🔍 Database Connection Debug Checklist

## Issues to Check:

### 1. **Backend Server Status**
- [ ] Is your backend server running?
- [ ] Check if Node.js is supported on your hosting
- [ ] Verify the API endpoint is accessible

### 2. **Database Connection**
- [ ] Database created in cPanel
- [ ] Database user created with proper permissions
- [ ] `.env` file configured with correct credentials
- [ ] Database tables imported from SQL file

### 3. **API Endpoints**
- [ ] Test: `https://fazona.org/api/vehicles`
- [ ] Should return JSON data
- [ ] Check browser console for errors

### 4. **Common Issues**

#### **If using cPanel without Node.js:**
- Website will work as static site
- Database features won't work
- Admin panel won't function

#### **If backend is running but not connecting:**
- Check `.env` file in `api/` folder
- Verify database credentials
- Check MySQL service status

#### **If getting 404 errors:**
- Backend not deployed correctly
- API routes not accessible
- Check server logs

### 5. **Quick Tests**

#### **Test 1: Check if backend is running**
Visit: `https://fazona.org/api/vehicles`
- ✅ Should return JSON with vehicle data
- ❌ If 404: Backend not running/deployed

#### **Test 2: Check database connection**
If you have SSH access:
```bash
cd /path/to/your/api
node server/test-connection.js
```

#### **Test 3: Check browser console**
- Open browser dev tools (F12)
- Look for network errors
- Check API request status

### 6. **Solutions**

#### **For cPanel without Node.js:**
1. Contact hosting provider about Node.js support
2. Or use static mode (website still works beautifully)

#### **For database issues:**
1. Verify database exists in cPanel
2. Check user permissions
3. Import SQL file again

#### **For API issues:**
1. Check if backend files uploaded correctly
2. Verify `.env` configuration
3. Check server error logs

---

## 🚨 **Most Common Issue:**
**cPanel hosting often doesn't support Node.js by default.**

**Quick Check:** Ask your hosting provider:
- "Do you support Node.js applications?"
- "Can I run Express.js apps?"

If NO: Your website will work as static site (still beautiful!)
If YES: Follow database setup instructions.