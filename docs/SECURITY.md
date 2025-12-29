# Security Guidelines

## Production Security Checklist

### ✅ Application Security

- [ ] Disable `display_errors` in production
- [ ] Enable HTTPS/SSL
- [ ] Set secure session cookies
- [ ] Implement CSRF protection
- [ ] Validate all user inputs
- [ ] Use prepared statements (already implemented)
- [ ] Sanitize output (XSS prevention)

### ✅ Database Security

- [ ] Use strong database passwords
- [ ] Limit database user permissions
- [ ] Enable database encryption at rest
- [ ] Regular security updates
- [ ] Backup encryption

### ✅ Server Security

- [ ] Keep PHP and MySQL updated
- [ ] Configure firewall rules
- [ ] Disable unnecessary services
- [ ] Regular security patches
- [ ] Monitor access logs

### ✅ API Security

- [ ] Implement rate limiting
- [ ] Use API authentication tokens
- [ ] Validate API requests
- [ ] Log API access
- [ ] Monitor for suspicious activity

### ✅ LLM API Keys

- [ ] Store keys encrypted
- [ ] Rotate keys regularly
- [ ] Monitor key usage
- [ ] Set spending limits
- [ ] Never commit keys to Git

## Security Headers

Already configured in `.htaccess`:
- X-Content-Type-Options: nosniff
- X-Frame-Options: SAMEORIGIN
- X-XSS-Protection: 1; mode=block
- Referrer-Policy: strict-origin-when-cross-origin

## Password Policy

- Minimum 8 characters
- Use password hashing (bcrypt)
- Implement password reset flow
- Consider 2FA for admin users

## File Permissions

```bash
# Directories
find . -type d -exec chmod 755 {} \;

# Files
find . -type f -exec chmod 644 {} \;

# Executable scripts
chmod 755 scripts/*.sh

# Sensitive files
chmod 600 .env.production
```

## Regular Security Tasks

1. **Weekly**: Review error logs for suspicious activity
2. **Monthly**: Update dependencies
3. **Quarterly**: Security audit
4. **Annually**: Penetration testing

## Incident Response

1. Identify the issue
2. Contain the threat
3. Assess damage
4. Restore from backup if needed
5. Document and learn

