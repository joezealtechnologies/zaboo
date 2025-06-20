# 🔧 VPS Troubleshooting Guide

## Quick Fixes

### 1. Website Not Loading
```bash
# Check if services are running
sudo systemctl status nginx
sudo systemctl status mysql
pm2 list

# Restart services
sudo systemctl restart nginx
pm2 restart all
```

### 2. 502 Bad Gateway
```bash
# Check if backend is running
pm2 list
pm2 logs

# Restart backend
pm2 restart all

# Check Nginx configuration
sudo nginx -t
sudo systemctl reload nginx
```

### 3. Database Connection Issues
```bash
# Test database connection
cd /var/www/fazona.org
npm run db:test

# Check MySQL status
sudo systemctl status mysql

# Restart MySQL
sudo systemctl restart mysql
```

### 4. SSL Certificate Issues
```bash
# Check certificate status
sudo certbot certificates

# Renew certificate
sudo certbot renew

# Test SSL
openssl s_client -connect fazona.org:443
```

## Common Issues

### Issue: "Cannot connect to database"
**Symptoms:** API returns 500 errors, admin login fails
**Solution:**
```bash
# Check MySQL is running
sudo systemctl start mysql

# Verify database exists
sudo mysql -e "SHOW DATABASES;" | grep fazona_ev

# Check user permissions
sudo mysql -e "SHOW GRANTS FOR 'fazona_user'@'localhost';"

# Recreate if needed
sudo mysql -e "GRANT ALL PRIVILEGES ON fazona_ev.* TO 'fazona_user'@'localhost';"
```

### Issue: "PM2 process not running"
**Symptoms:** Backend API not responding
**Solution:**
```bash
# Check PM2 status
pm2 list

# Start if stopped
cd /var/www/fazona.org
pm2 start deploy/ecosystem.config.js --env production

# Save PM2 configuration
pm2 save
```

### Issue: "Nginx 404 errors"
**Symptoms:** Website shows Nginx default page
**Solution:**
```bash
# Check Nginx configuration
sudo nginx -t

# Verify site is enabled
ls -la /etc/nginx/sites-enabled/

# Restart Nginx
sudo systemctl restart nginx
```

### Issue: "SSL certificate expired"
**Symptoms:** Browser shows security warnings
**Solution:**
```bash
# Renew certificate
sudo certbot renew

# Check auto-renewal
sudo systemctl status certbot.timer

# Manual renewal if needed
sudo certbot --nginx -d fazona.org -d www.fazona.org
```

## Monitoring Commands

### Check Application Health
```bash
# Run health check script
./deploy/vps-health-check.sh

# Monitor in real-time
pm2 monit

# View logs
pm2 logs
tail -f /var/log/nginx/error.log
```

### Check Resource Usage
```bash
# Disk space
df -h

# Memory usage
free -h

# CPU usage
top

# Network connections
netstat -tlnp
```

### Check Processes
```bash
# All running processes
ps aux | grep node
ps aux | grep nginx
ps aux | grep mysql

# Ports in use
sudo netstat -tlnp | grep :80
sudo netstat -tlnp | grep :443
sudo netstat -tlnp | grep :5000
```

## Log Locations

- **PM2 Logs:** `~/.pm2/logs/`
- **Nginx Access:** `/var/log/nginx/access.log`
- **Nginx Error:** `/var/log/nginx/error.log`
- **MySQL Error:** `/var/log/mysql/error.log`
- **System:** `journalctl -u nginx` or `journalctl -u mysql`

## Recovery Procedures

### Complete Application Restart
```bash
# Stop everything
pm2 stop all
sudo systemctl stop nginx

# Start everything
sudo systemctl start nginx
pm2 start all

# Verify
./deploy/vps-health-check.sh
```

### Database Recovery
```bash
# Backup current database
mysqldump -u fazona_user -p fazona_ev > backup.sql

# Restore from backup
mysql -u fazona_user -p fazona_ev < backup.sql

# Or recreate from scratch
npm run db:setup
```

### Nginx Configuration Reset
```bash
# Backup current config
sudo cp /etc/nginx/sites-available/fazona.org /etc/nginx/sites-available/fazona.org.backup

# Restore from project
sudo cp deploy/nginx.conf /etc/nginx/sites-available/fazona.org

# Test and reload
sudo nginx -t
sudo systemctl reload nginx
```

## Performance Optimization

### Enable Gzip Compression
Already configured in nginx.conf

### Optimize MySQL
```bash
sudo mysql_secure_installation
sudo systemctl restart mysql
```

### Monitor Performance
```bash
# Install htop for better monitoring
sudo apt install htop

# Monitor in real-time
htop
```

## Security Checklist

- [ ] Changed default admin password
- [ ] Firewall is enabled and configured
- [ ] SSL certificate is valid and auto-renewing
- [ ] Database user has minimal required permissions
- [ ] Regular backups are configured
- [ ] System packages are up to date

## Getting Help

If you're still experiencing issues:

1. **Check the logs first:**
   ```bash
   pm2 logs
   sudo tail -f /var/log/nginx/error.log
   ```

2. **Run the health check:**
   ```bash
   ./deploy/vps-health-check.sh
   ```

3. **Verify environment variables:**
   ```bash
   cat /var/www/fazona.org/.env
   ```

4. **Test individual components:**
   ```bash
   # Test database
   npm run db:test
   
   # Test API directly
   curl https://fazona.org/api/vehicles
   
   # Test admin login
   curl -X POST https://fazona.org/api/admin/login \
        -H "Content-Type: application/json" \
        -d '{"username":"admin","password":"admin123"}'
   ```

Your FaZona EV website should be running smoothly on your VPS! 🚗⚡