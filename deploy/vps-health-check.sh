#!/bin/bash

# FaZona EV VPS Health Check Script
# Run this to verify your deployment is working correctly

echo "🔍 FaZona EV VPS Health Check"
echo "============================="

DOMAIN="fazona.org"
APP_DIR="/var/www/fazona.org"

# Colors
GREEN='\033[0;32m'
RED='\033[0;31m'
YELLOW='\033[1;33m'
NC='\033[0m'

check_pass() {
    echo -e "${GREEN}✅ $1${NC}"
}

check_fail() {
    echo -e "${RED}❌ $1${NC}"
}

check_warn() {
    echo -e "${YELLOW}⚠️  $1${NC}"
}

echo ""
echo "🔧 System Services:"

# Check Nginx
if systemctl is-active --quiet nginx; then
    check_pass "Nginx is running"
else
    check_fail "Nginx is not running"
fi

# Check MySQL
if systemctl is-active --quiet mysql; then
    check_pass "MySQL is running"
else
    check_fail "MySQL is not running"
fi

echo ""
echo "📱 Application Status:"

# Check PM2 processes
if pm2 list | grep -q "fazona-ev-backend"; then
    check_pass "PM2 application is running"
    pm2 list | grep fazona-ev-backend
else
    check_fail "PM2 application is not running"
fi

echo ""
echo "🌐 Website Accessibility:"

# Check main website
if curl -s -o /dev/null -w "%{http_code}" https://$DOMAIN | grep -q "200"; then
    check_pass "Main website (https://$DOMAIN) is accessible"
else
    check_fail "Main website is not accessible"
fi

# Check admin panel
if curl -s -o /dev/null -w "%{http_code}" https://$DOMAIN/admin | grep -q "200"; then
    check_pass "Admin panel (https://$DOMAIN/admin) is accessible"
else
    check_fail "Admin panel is not accessible"
fi

# Check API endpoint
if curl -s -o /dev/null -w "%{http_code}" https://$DOMAIN/api/vehicles | grep -q "200"; then
    check_pass "API endpoint (https://$DOMAIN/api/vehicles) is working"
else
    check_fail "API endpoint is not working"
fi

echo ""
echo "🔒 SSL Certificate:"

# Check SSL certificate
ssl_info=$(echo | openssl s_client -servername $DOMAIN -connect $DOMAIN:443 2>/dev/null | openssl x509 -noout -dates 2>/dev/null)
if [ $? -eq 0 ]; then
    check_pass "SSL certificate is valid"
    echo "$ssl_info"
else
    check_fail "SSL certificate issue"
fi

echo ""
echo "💾 Database Connection:"

# Check database connection (if we're in the app directory)
if [ -f "$APP_DIR/package.json" ]; then
    cd $APP_DIR
    if npm run db:test > /dev/null 2>&1; then
        check_pass "Database connection is working"
    else
        check_fail "Database connection failed"
    fi
else
    check_warn "Cannot test database (not in app directory)"
fi

echo ""
echo "📊 Resource Usage:"

# Check disk space
disk_usage=$(df -h / | awk 'NR==2{print $5}' | sed 's/%//')
if [ $disk_usage -lt 80 ]; then
    check_pass "Disk usage: ${disk_usage}% (healthy)"
else
    check_warn "Disk usage: ${disk_usage}% (consider cleanup)"
fi

# Check memory usage
mem_usage=$(free | grep Mem | awk '{printf("%.1f", $3/$2 * 100.0)}')
if (( $(echo "$mem_usage < 80" | bc -l) )); then
    check_pass "Memory usage: ${mem_usage}% (healthy)"
else
    check_warn "Memory usage: ${mem_usage}% (high)"
fi

echo ""
echo "🔍 Recent Logs:"

# Show recent PM2 logs
echo "Last 5 PM2 log entries:"
pm2 logs --lines 5 2>/dev/null || echo "No PM2 logs available"

echo ""
echo "📋 Quick Commands:"
echo "• View live logs: pm2 logs"
echo "• Monitor processes: pm2 monit"
echo "• Restart app: pm2 restart all"
echo "• Check Nginx: sudo systemctl status nginx"
echo "• Check MySQL: sudo systemctl status mysql"
echo "• Renew SSL: sudo certbot renew"

echo ""
if curl -s -o /dev/null -w "%{http_code}" https://$DOMAIN | grep -q "200"; then
    echo "🎉 Your FaZona EV website is running successfully!"
    echo "🌐 Visit: https://$DOMAIN"
    echo "🔧 Admin: https://$DOMAIN/admin"
else
    echo "⚠️  Some issues detected. Check the failed items above."
fi