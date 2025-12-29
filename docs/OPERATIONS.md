# Operations Manual

## Daily Operations

### Health Monitoring
- Check health endpoint: `http://localhost/cursoft/api/health.php`
- Monitor Grafana dashboards (if enabled)
- Review error logs: `logs/php-errors.log`

### Database Backups
```bash
# Manual backup
./scripts/backup_database.sh

# Restore from backup
./scripts/restore_database.sh backups/cursoft_backup_YYYYMMDD_HHMMSS.sql.gz
```

### Log Management
```bash
# Clean old logs (keeps last 30 days)
./scripts/cleanup_logs.sh 30
```

## Weekly Tasks

1. Review and rotate logs
2. Check disk space usage
3. Review security logs
4. Update dependencies (if needed)
5. Test backup restoration

## Monthly Tasks

1. Review performance metrics
2. Analyze LLM usage and costs
3. Update documentation
4. Security audit
5. Capacity planning

## Monitoring Endpoints

- **Health Check**: `/api/health.php`
- **Metrics**: `/api/metrics.php` (Prometheus format)
- **Application**: Main dashboard

## Alerting

Set up alerts for:
- Health check failures
- High error rates
- Disk space > 90%
- Database connection failures
- High LLM costs

## Performance Tuning

### PHP Settings
- Adjust `memory_limit` in `config/production.php`
- Tune `max_execution_time` for long-running tasks
- Enable OPcache for production

### Database
- Regular `OPTIMIZE TABLE` operations
- Monitor slow queries
- Index optimization

### Caching
- Enable browser caching (via `.htaccess`)
- Consider Redis for session storage (future enhancement)

## Backup Strategy

1. **Database**: Daily automated backups (via cron)
2. **Files**: Weekly full backup
3. **Retention**: Keep 7 daily, 4 weekly, 12 monthly backups

## Disaster Recovery

1. Restore database from latest backup
2. Restore application files
3. Verify health check
4. Test critical functionality

