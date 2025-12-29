# 🐳 Run Docker Compose - Production Setup

## ⚠️ Before Running

The `docker-compose.prod.yml` needs environment variables. Create a `.env` file:

### Step 1: Create .env File

In `E:\xampp\htdocs\cursoft\docker\` create `.env`:

```env
DB_PASSWORD=cursoft123
MYSQL_ROOT_PASSWORD=root123
APP_PORT=8080
DB_PORT=3307
NGINX_PORT=80
NGINX_SSL_PORT=443
PROMETHEUS_PORT=9090
GRAFANA_PORT=3000
GRAFANA_PASSWORD=admin
```

### Step 2: Run Docker Compose

```powershell
cd E:\xampp\htdocs\cursoft\docker
docker-compose -f docker-compose.prod.yml up -d
```

## 📋 What Will Start

- ✅ **cursoft-app** - PHP/Apache application (port 8080)
- ✅ **cursoft-db** - MySQL database (port 3307)
- ✅ **cursoft-nginx** - Nginx reverse proxy (port 80)
- ✅ **cursoft-prometheus** - Monitoring (port 9090)
- ✅ **cursoft-grafana** - Dashboards (port 3000)

## 🔍 Check Status

```powershell
docker-compose -f docker-compose.prod.yml ps
```

## 📊 Access Services

- **Application:** http://localhost:8080
- **Nginx:** http://localhost
- **Grafana:** http://localhost:3000 (admin/admin)
- **Prometheus:** http://localhost:9090
- **Database:** localhost:3307

## 🛑 Stop Services

```powershell
docker-compose -f docker-compose.prod.yml down
```

## 📝 View Logs

```powershell
docker-compose -f docker-compose.prod.yml logs
docker-compose -f docker-compose.prod.yml logs app
docker-compose -f docker-compose.prod.yml logs db
```

---

**Create the .env file first, then run docker-compose!**


