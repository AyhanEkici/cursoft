# Troubleshooting Guide

## Common Issues

### Database Connection Errors

**Symptoms**: "Database connection failed"

**Solutions**:
1. Check MySQL is running: `docker ps` or XAMPP Control Panel
2. Verify credentials in `includes/Database.php`
3. Check database exists: `SHOW DATABASES;`
4. Test connection: `mysql -u root -p`

### 404 Not Found

**Symptoms**: Pages not loading

**Solutions**:
1. Check Apache is running
2. Verify file paths are correct
3. Check `.htaccess` is being read (mod_rewrite enabled)
4. Verify DocumentRoot in Apache config

### Session Issues

**Symptoms**: Logged out unexpectedly

**Solutions**:
1. Check session directory permissions
2. Verify session lifetime settings
3. Check if cookies are enabled
4. Review `config/production.php` session settings

### Docker Issues

**Symptoms**: Containers not starting

**Solutions**:
1. Check Docker is running: `docker ps`
2. View logs: `docker-compose logs`
3. Check ports aren't in use
4. Verify `.env.production` is configured

### LLM API Errors

**Symptoms**: "Invalid API key" or "Model parameter required"

**Solutions**:
1. Verify API key in `llm_configs` table
2. Check key is not a test/placeholder key
3. Ensure provider is enabled
4. Test API key directly with provider

### Pipeline Not Starting

**Symptoms**: Pipeline stays in "pending" status

**Solutions**:
1. Check Docker is running (for containers)
2. Verify LLM API keys are configured
3. Check project has tasks in `project_plans`
4. Review `pipeline_stages` table

## Debug Mode

Enable debug mode temporarily:

```php
// In config/production.php
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);
```

**Remember to disable in production!**

## Log Files

Check these log files:
- `logs/php-errors.log` - PHP errors
- `logs/deployment.log` - Deployment logs
- Apache error log (XAMPP)
- MySQL error log

## Performance Issues

### Slow Page Loads
1. Enable OPcache
2. Check database query performance
3. Review slow query log
4. Optimize images/assets

### High Memory Usage
1. Increase `memory_limit` in PHP
2. Review large file operations
3. Check for memory leaks
4. Monitor container resources

## Getting Help

1. Check logs first
2. Review this troubleshooting guide
3. Check GitHub issues
4. Review documentation

## Emergency Procedures

### Site Down
1. Check health endpoint
2. Review error logs
3. Check database connection
4. Restart services if needed

### Data Loss
1. Stop all operations
2. Restore from latest backup
3. Verify data integrity
4. Document incident

