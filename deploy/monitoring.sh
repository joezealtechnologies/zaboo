#!/bin/bash

# FaZona EV Monitoring Script
# Check system health and application status

echo "🔍 FaZona EV System Health Check"
echo "================================"

# Check disk space
echo "💾 Disk Usage:"
df -h | grep -E "/$|/var"

echo ""

# Check memory usage
echo "🧠 Memory Usage:"
free -h

echo ""

# Check PM2 processes
echo "⚡ PM2 Processes:"
pm2 list

echo ""

# Check Nginx status
echo "🌐 Nginx Status:"
sudo systemctl status nginx --no-pager -l

echo ""

# Check MySQL status
echo "🗄️ MySQL Status:"
sudo systemctl status mysql --no-pager -l

echo ""

# Check SSL certificate expiry
echo "🔒 SSL Certificate:"
echo | openssl s_client -servername fazona.org -connect fazona.org:443 2>/dev/null | openssl x509 -noout -dates

echo ""

# Check recent logs for errors
echo "📝 Recent Error Logs:"
echo "PM2 Errors (last 10 lines):"
pm2 logs --err --lines 10

echo ""

# Check website response
echo "🌍 Website Health:"
curl -s -o /dev/null -w "HTTP Status: %{http_code}\nResponse Time: %{time_total}s\n" https://fazona.org

echo ""
echo "✅ Health check completed!"