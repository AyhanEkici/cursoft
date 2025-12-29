# Development Guide

## Local Development Setup

### Using XAMPP (Current Setup)

1. **Start Services**
   - Start Apache and MySQL in XAMPP Control Panel

2. **Database Setup**
   - Open phpMyAdmin: `http://localhost/phpmyadmin`
   - Run schema files in order:
     - `database/schema.sql`
     - `database/schema_phase2.sql`
     - `database/schema_phase3.sql`
     - `database/schema_phase4.sql`

3. **Access Application**
   - Main app: `http://localhost/cursoft/`
   - Test interfaces: `http://localhost/cursoft/phase1/test_planner.php`

### Using Docker

1. **Start Services**
   ```bash
   cd docker
   docker-compose -f docker-compose.prod.yml up -d
   ```

2. **Access Application**
   - Main app: `http://localhost:8080`
   - Grafana: `http://localhost:3000`
   - Prometheus: `http://localhost:9090`

## Project Structure

```
cursoft/
├── api/              # API endpoints
├── config/           # Configuration files
├── database/         # Database schemas
├── docker/           # Docker configurations
├── docs/             # Documentation
├── includes/         # PHP classes
├── pages/            # Frontend pages
├── phase1-5/         # Phase-specific files
├── public/           # Public assets (CSS, JS)
├── scripts/          # Deployment scripts
└── tests/            # Test files
```

## Development Workflow

1. **Create Feature Branch**
   ```bash
   git checkout -b feature/new-feature
   ```

2. **Make Changes**
   - Write code
   - Test locally
   - Update documentation

3. **Commit Changes**
   ```bash
   git add .
   git commit -m "Add new feature"
   ```

4. **Push and Create PR**
   ```bash
   git push origin feature/new-feature
   ```

## Testing

### Run Test Suite
```bash
# Via browser
http://localhost/cursoft/tests/test_phase2_phase3.php

# Or run individual phase tests
http://localhost/cursoft/phase1/test_planner.php
http://localhost/cursoft/phase2/test_containers.php
```

### Manual Testing Checklist
- [ ] Create project
- [ ] View project details
- [ ] Start pipeline
- [ ] Configure LLM keys
- [ ] Test API endpoints

## Code Style

- Use PSR-12 coding standards
- Comment complex logic
- Use meaningful variable names
- Follow existing code structure

## Database Changes

1. Create migration SQL file
2. Test on development database
3. Document changes
4. Include rollback script

## API Changes

1. Update API documentation
2. Maintain backward compatibility
3. Version API if breaking changes
4. Update frontend API client

## Deployment

See `DEPLOYMENT.md` for production deployment procedures.

