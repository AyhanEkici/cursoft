# 🐳 Docker Setup & Connection Guide

## ✅ Check Docker Installation

### Step 1: Verify Docker is Running

```powershell
docker --version
docker ps
```

**If Docker is not installed:**
- Download: https://www.docker.com/products/docker-desktop
- Install Docker Desktop for Windows
- Start Docker Desktop

## 🚀 Build Docker Image

### Build the Development Image

```powershell
cd E:\xampp\htdocs\cursoft\docker
docker build -f Dockerfile.cursoft-dev -t cursoft-dev:latest .
```

This creates the base development image.

## 🔌 Connect to Docker Container

### Option 1: Run Interactive Container

```powershell
docker run -it --rm cursoft-dev:latest
```

This gives you a bash shell inside the container.

### Option 2: Run with Volume Mount

```powershell
docker run -it --rm -v E:\xampp\htdocs\cursoft\workspaces:/workspace cursoft-dev:latest
```

This mounts your workspaces folder.

### Option 3: Use Docker Compose

```powershell
cd E:\xampp\htdocs\cursoft\docker
docker-compose -f docker-compose.template.yml up -d
```

## 📋 Docker Commands

### List Containers
```powershell
docker ps          # Running containers
docker ps -a       # All containers
```

### List Images
```powershell
docker images
```

### Stop Container
```powershell
docker stop <container_id>
```

### Remove Container
```powershell
docker rm <container_id>
```

### Execute Command in Container
```powershell
docker exec -it <container_id> bash
```

## 🔧 For Cursoft Project

### Test Container Creation

The project uses `ContainerManager.php` to create containers. You can test it:

1. **Via Web Interface:**
   - Visit: http://localhost/cursoft/phase2/test_containers.php

2. **Via API:**
   - POST to: http://localhost/cursoft/api/containers.php

## ⚠️ Important Notes

- **Docker must be running** before using container features
- **Ports:** Containers use ports 8000-8099 (check for conflicts)
- **Volumes:** Workspaces are mounted at `/workspace` in containers

## 🎯 Quick Start

1. **Start Docker Desktop**
2. **Build image:**
   ```powershell
   cd E:\xampp\htdocs\cursoft\docker
   docker build -f Dockerfile.cursoft-dev -t cursoft-dev:latest .
   ```
3. **Test connection:**
   ```powershell
   docker run -it --rm cursoft-dev:latest
   ```

---

**Is Docker Desktop running on your system?**


