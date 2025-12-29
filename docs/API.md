# API Documentation

## Base URL
```
http://localhost/cursoft/api
```

## Authentication

Most endpoints require authentication via session. Login first to establish session.

## Endpoints

### Authentication

#### Login
```
POST /auth.php
Body: {
  "action": "login",
  "email": "user@example.com",
  "password": "password",
  "remember_me": false
}
```

#### Signup
```
POST /auth.php
Body: {
  "action": "signup",
  "email": "user@example.com",
  "password": "password",
  "name": "User Name"
}
```

#### Logout
```
POST /auth.php
Body: {
  "action": "logout"
}
```

#### Check Auth
```
GET /auth.php?check=1
```

### Projects

#### Create Project
```
POST /projects.php
Body: {
  "user_id": 1,
  "name": "My Project",
  "prompt": "Build a todo app"
}
```

#### Get Project
```
GET /projects.php?id=1
```

#### Get User Projects
```
GET /projects.php?user_id=1
```

### Containers

#### Create Container
```
POST /containers.php
Body: {
  "project_id": 1,
  "auto_start": true
}
```

#### Get Containers
```
GET /containers.php
GET /containers.php?project_id=1
```

#### Container Actions
```
PUT /containers.php
Body: {
  "id": 1,
  "action": "start" | "stop" | "restart"
}
```

### LLM

#### Make LLM Request
```
POST /llm.php
Body: {
  "user_id": 1,
  "provider": "openai",
  "prompt": "Write a function...",
  "model": "gpt-3.5-turbo",
  "max_tokens": 500
}
```

#### Get Providers
```
GET /llm.php?providers
```

#### Get Models
```
GET /llm.php?models&provider=openai
```

### Pipeline

#### Start Pipeline
```
POST /pipeline.php
Body: {
  "project_id": 1,
  "action": "start",
  "user_id": 1,
  "llm_provider": "openai"
}
```

#### Get Pipeline Status
```
GET /pipeline.php?project_id=1
```

#### Execute Stage
```
POST /pipeline.php
Body: {
  "project_id": 1,
  "action": "execute_stage",
  "stage": "development"
}
```

### Monitoring

#### Health Check
```
GET /health.php
```

#### Metrics (Prometheus)
```
GET /metrics.php
```

## Response Format

All endpoints return JSON:

**Success:**
```json
{
  "success": true,
  "data": {...}
}
```

**Error:**
```json
{
  "error": "Error message"
}
```

## Status Codes

- `200` - Success
- `400` - Bad Request
- `401` - Unauthorized
- `404` - Not Found
- `500` - Server Error
- `503` - Service Unavailable (health check)

## Rate Limiting

Currently no rate limiting implemented. Consider adding for production.

