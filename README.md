# Cursoft - AI-Powered Development Platform

A full-stack SaaS platform that automatically builds software projects from user prompts using AI/LLM technology.

## 🚀 Quick Start

### Prerequisites
- XAMPP (Apache, MySQL, PHP 8.0+)
- Docker Desktop (optional, for containerized deployment)
- Git (optional)

### Installation

1. **Clone or Download**
   ```bash
   cd E:\xampp\htdocs
   git clone <repository-url> cursoft
   ```

2. **Database Setup**
   - Open phpMyAdmin: `http://localhost/phpmyadmin`
   - Run SQL files in order:
     - `database/schema.sql`
     - `database/schema_phase2.sql`
     - `database/schema_phase3.sql`
     - `database/schema_phase4.sql`
   - Or run all at once: `database/schema_all_phases.sql`

3. **Access Application**
   - Main app: `http://localhost/cursoft/`
   - Will redirect to login page

4. **Create Account**
   - Sign up at: `http://localhost/cursoft/pages/signup.php`
   - Or use test user: `test@example.com` (password in schema.sql)

## 📁 Project Structure

```
cursoft/
├── api/              # REST API endpoints
├── config/           # Configuration files
├── database/         # Database schemas
├── docker/           # Docker configurations
├── docs/             # Documentation
├── includes/         # PHP classes
├── pages/            # Frontend pages
├── phase1-5/         # Phase-specific files
├── public/           # CSS, JavaScript
├── scripts/          # Deployment scripts
└── tests/            # Test suites
```

## 🎯 Features

### Phase 1: Project Planner ✅
- Prompt decomposition
- Task generation
- Project planning

### Phase 2: Container Manager ✅
- Docker container management
- Isolated development environments
- Container orchestration

### Phase 3: Safe Agent Toolkit ✅
- Multi-provider LLM integration
- Safe code execution
- Autonomous debugging
- Development pipeline

### Phase 4: Frontend Website ✅
- User authentication
- Dashboard
- Project management
- Real-time updates

### Phase 5: Deployment & Production ✅
- Docker Compose setup
- Production configuration
- Monitoring (Prometheus/Grafana)
- CI/CD pipelines
- Documentation

## 🔧 Configuration

### Database
Edit `includes/Database.php`:
```php
private $host = 'localhost';
private $dbname = 'cursoft';
private $username = 'root';
private $password = '';
```

### Production
Copy `docker/.env.production.example` to `.env.production` and configure.

## 📡 API Documentation

See `docs/API.md` for complete API documentation.

## 🐳 Docker Deployment

```bash
cd docker
docker-compose -f docker-compose.prod.yml up -d
```

## 📚 Documentation

- [Deployment Guide](docs/DEPLOYMENT.md)
- [Operations Manual](docs/OPERATIONS.md)
- [Security Guidelines](docs/SECURITY.md)
- [API Documentation](docs/API.md)
- [Troubleshooting](docs/TROUBLESHOOTING.md)
- [Development Guide](docs/DEVELOPMENT.md)

## 🧪 Testing

- Phase 1-3 Tests: `http://localhost/cursoft/tests/test_phase2_phase3.php`
- All Tests: `http://localhost/cursoft/tests/run_all_tests.php`

## 🔐 Security

- Password hashing (bcrypt)
- Prepared statements (SQL injection prevention)
- Session management
- Security headers
- Input validation

## 📊 Monitoring

- Health Check: `/api/health.php`
- Metrics: `/api/metrics.php` (Prometheus format)
- Grafana: `http://localhost:3000` (if enabled)

## 🛠️ Maintenance

### Daily
- Monitor health endpoint
- Check error logs

### Weekly
- Database backups
- Log rotation

### Monthly
- Security updates
- Performance review

## 📝 License

[Your License Here]

## 🤝 Contributing

[Contributing Guidelines]

## 📞 Support

[Support Information]

---

**Built with:** PHP 8.0, MySQL, Apache (XAMPP), Docker, Prometheus, Grafana
