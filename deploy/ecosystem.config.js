// PM2 configuration for production deployment
module.exports = {
  apps: [
    {
      name: 'fazona-ev-backend',
      script: './server/index.js',
      instances: 2, // Run 2 instances for load balancing
      exec_mode: 'cluster',
      env: {
        NODE_ENV: 'development',
        PORT: 5000
      },
      env_production: {
        NODE_ENV: 'production',
        PORT: 5000
      },
      // Restart settings
      max_restarts: 10,
      min_uptime: '10s',
      max_memory_restart: '500M',
      
      // Logging
      log_file: '/var/log/pm2/fazona-ev-backend.log',
      error_file: '/var/log/pm2/fazona-ev-backend-error.log',
      out_file: '/var/log/pm2/fazona-ev-backend-out.log',
      log_date_format: 'YYYY-MM-DD HH:mm:ss Z',
      
      // Monitoring
      monitoring: false,
      
      // Auto restart on file changes (disable in production)
      watch: false,
      ignore_watch: ['node_modules', 'uploads', 'logs'],
      
      // Environment variables
      env_file: '.env'
    }
  ]
};