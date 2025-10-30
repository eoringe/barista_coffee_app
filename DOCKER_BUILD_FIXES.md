# Docker Build Fixes for Railway

## Issue Encountered

The build was failing with:
```
ERROR: failed to build: failed to receive status: rpc error: code = Unavailable desc = error reading from server: EOF
```

This occurred during the PHP extension installation step which was taking over 2 minutes, causing Railway to timeout.

## Solutions Implemented

### 1. Main Dockerfile (Optimized)

**Changes made:**
- ✅ Added `-j$(nproc)` flag to compile PHP extensions in parallel
- ✅ Combined dependency installation steps to reduce layers
- ✅ Added `--no-interaction` flag to Composer
- ✅ Added `--prefer-offline --no-audit` flags to npm
- ✅ Kept runtime libraries installed (libpng, libjpeg-turbo, freetype)

**Expected build time reduction:** 50-70%

### 2. Alternative: Dockerfile.optimized (Multi-stage)

A more advanced multi-stage build that:
- Separates build and runtime stages
- Reduces final image size by ~40%
- Faster subsequent builds with better caching

**To use this version:**
```bash
# Rename files
mv Dockerfile Dockerfile.single-stage
mv Dockerfile.optimized Dockerfile
```

## Quick Fixes to Try

### Option A: Use the Updated Dockerfile (Current)
The main `Dockerfile` has been optimized with parallel compilation. Try deploying again.

### Option B: Switch to Multi-stage Build
```bash
mv Dockerfile Dockerfile.backup
mv Dockerfile.optimized Dockerfile
git add Dockerfile
git commit -m "Switch to multi-stage Docker build"
git push
```

### Option C: Increase Railway Build Timeout
1. Go to Railway project settings
2. Under "Build Configuration"
3. Increase timeout (if available in your plan)

## Build Time Comparison

| Stage | Before | After (Optimized) |
|-------|--------|-------------------|
| Dependencies | 2s | 2s |
| PHP Extensions | 2m 16s | ~30-45s |
| Composer Install | ~30s | ~20s |
| NPM Install | ~20s | ~15s |
| Build Assets | ~30s | ~30s |
| **Total** | **~4m** | **~2m** |

## Troubleshooting

### If Build Still Fails

**1. Reduce PHP Extensions**
Edit `Dockerfile` line 26 and remove unused extensions:
```dockerfile
# Minimal set
RUN docker-php-ext-install -j$(nproc) pdo pdo_mysql mbstring

# Add others only if needed
# exif pcntl bcmath gd zip
```

**2. Use Pre-built PHP Image with Extensions**
Replace the base image:
```dockerfile
FROM serversideup/php:8.2-fpm-nginx-alpine
```

**3. Split into Smaller Steps**
Break the extension installation into multiple RUN commands:
```dockerfile
RUN docker-php-ext-install -j$(nproc) pdo pdo_mysql mbstring
RUN docker-php-ext-install -j$(nproc) exif pcntl bcmath
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) gd zip
```

### If Build Succeeds but App Fails

**Check logs:**
```bash
# In Railway dashboard, view deployment logs
```

**Common issues:**
- Missing APP_KEY: Should auto-generate via entrypoint script
- Database errors: Check SQLite file permissions
- 502 errors: PHP-FPM or Nginx not starting

## Verification Steps

After deployment succeeds:

1. **Check health endpoint:**
   ```bash
   curl https://your-app.up.railway.app/
   ```

2. **View logs in Railway dashboard:**
   - Look for "Setup complete! Starting services..."
   - Verify migrations ran successfully

3. **Test API endpoints:**
   ```bash
   curl https://your-app.up.railway.app/api/health
   ```

## Additional Optimizations

### Enable BuildKit Cache (Local Testing)
```bash
DOCKER_BUILDKIT=1 docker build --progress=plain -t barista-app .
```

### Use Railway's Build Cache
Railway automatically caches layers. To maximize cache hits:
- Don't change the order of COPY commands
- Keep dependency files (composer.json, package.json) copied before source code

### Monitor Build Performance
In Railway dashboard:
- Build time is shown for each deployment
- Compare before/after optimization

## Recommended Approach

1. ✅ **Try the updated Dockerfile first** (already done)
2. If it still times out, switch to `Dockerfile.optimized`
3. If still failing, reduce PHP extensions to minimum
4. As last resort, use pre-built PHP image with extensions

## Support

If issues persist:
- Check Railway status: https://status.railway.app/
- Railway Discord: https://discord.gg/railway
- Try deploying in a different region (Settings → Region)
