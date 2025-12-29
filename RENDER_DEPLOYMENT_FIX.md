# 🔧 Render.com Deployment Fix

## Issues Fixed

### 1. ✅ PORT Environment Variable
- **Issue:** Render uses `$PORT` (defaults to 10000)
- **Fix:** Updated `startCommand` to use `${PORT}` syntax
- **File:** `render.yaml`

### 2. ✅ Health Check Path
- **Issue:** Health check path was `/health.php` but file is in `public/health.php`
- **Fix:** After build, `health.php` is copied to `public/` root
- **File:** `render-build.sh`

### 3. ✅ Build Script Structure
- **Issue:** Build script was creating nested `public/public/` directory
- **Fix:** Updated to properly copy assets to `public/` root
- **File:** `render-build.sh`

### 4. ✅ PHP Built-in Server Router
- **Issue:** PHP `-S` server needs router for clean URLs
- **Fix:** Created `router.php` for proper routing
- **File:** `render-build.sh`

## Updated Files

### `render.yaml`
```yaml
startCommand: "php -S 0.0.0.0:${PORT} -t public"
healthCheckPath: /health.php
```

### `render-build.sh`
- Properly copies files to `public/` directory
- Creates `router.php` for PHP built-in server
- Maintains correct directory structure

## Render-Specific Requirements

### ✅ PORT Variable
- Render automatically sets `$PORT` environment variable
- Default: `10000`
- Use `${PORT}` in startCommand

### ✅ Build Process
1. Render runs `./render-build.sh`
2. Script prepares `public/` directory
3. All files copied to `public/`
4. Router created for PHP server

### ✅ Start Command
```bash
php -S 0.0.0.0:${PORT} -t public
```
- `-S` = PHP built-in server
- `0.0.0.0` = Listen on all interfaces
- `${PORT}` = Use Render's PORT
- `-t public` = Document root is `public/`

### ✅ Health Check
- Path: `/health.php`
- File location: `public/health.php` (after build)
- Must return HTTP 200 for healthy status

## Testing Locally

### Simulate Render Environment:
```powershell
# Set PORT variable
$env:PORT = "10000"

# Run build script
bash render-build.sh

# Start PHP server (like Render)
php -S localhost:10000 -t public

# Test health check
curl http://localhost:10000/health.php
```

## Deployment Checklist

- [x] `render.yaml` updated with `${PORT}`
- [x] `render-build.sh` fixed structure
- [x] `health.php` accessible at `/health.php`
- [x] Router created for PHP server
- [x] All files copied to `public/` correctly

## Next Steps

1. **Commit changes:**
   ```powershell
   git add render.yaml render-build.sh
   git commit -m "Fix Render deployment configuration"
   git push origin main
   ```

2. **Deploy on Render:**
   - Render will auto-detect `render.yaml`
   - Build will run `./render-build.sh`
   - Service will start with correct PORT

3. **Monitor Build:**
   - Check Render dashboard logs
   - Verify health check passes
   - Test application URLs

## Common Issues

### Build Fails
- Check `render-build.sh` has execute permissions
- Verify all paths exist
- Check Render build logs

### Health Check Fails
- Verify `health.php` exists in `public/` after build
- Check file permissions
- Test locally first

### Port Issues
- Ensure `${PORT}` is used (not hardcoded)
- Render sets PORT automatically
- Default is 10000

## Files Structure After Build

```
public/
├── index.php          # Main entry point
├── health.php         # Health check endpoint
├── router.php        # PHP server router
├── api/              # API endpoints
├── pages/            # Page files
├── includes/         # PHP classes
├── config/           # Configuration
├── css/              # Stylesheets
└── js/               # JavaScript
```

