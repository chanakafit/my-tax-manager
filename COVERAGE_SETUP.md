# Code Coverage Setup for Unit Tests

## Issue
Running `docker compose -p mb exec php php vendor/bin/codecept run unit --coverage --coverage-html` fails because PHP doesn't have a code coverage driver installed (Xdebug or PCOV).

## Solution Implemented

### 1. Updated Dockerfile
Modified `/local/php/php-fpm/Dockerfile` to install Xdebug:

```dockerfile
# Install Xdebug for code coverage (for Codeception tests)
RUN apk add --no-cache ${PHPIZE_DEPS} linux-headers && \
    pecl install xdebug && \
    docker-php-ext-enable xdebug && \
    apk del ${PHPIZE_DEPS} linux-headers

# Configure Xdebug for coverage only (not debugging)
RUN echo "xdebug.mode=coverage" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini
```

### 2. Updated docker-compose.yml
Added build context to PHP service:

```yaml
  php:
    env_file:
      - .env
    build:
      context: .
      dockerfile: local/php/php-fpm/Dockerfile
    image: php:latest
    container_name: mb-php
    restart: always
```

### 3. Rebuild and Restart

```bash
# Stop containers
docker compose -p mb down

# Rebuild PHP container with no cache
docker compose -p mb build php --no-cache

# Start all containers
docker compose -p mb up -d

# Wait for containers to be healthy (about 10-15 seconds)
sleep 15
```

### 4. Verify Xdebug Installation

```bash
# Check if Xdebug is loaded
docker compose -p mb exec php php -m | grep xdebug

# Should output: xdebug

# Check Xdebug configuration
docker compose -p mb exec php php -i | grep xdebug.mode

# Should output: xdebug.mode => coverage => coverage
```

### 5. Run Tests with Coverage

```bash
# Generate HTML coverage report
docker compose -p mb exec php php vendor/bin/codecept run unit --coverage --coverage-html

# Generate XML coverage report (for CI/CD)
docker compose -p mb exec php php vendor/bin/codecept run unit --coverage --coverage-xml

# View HTML report
open php/tests/_output/coverage/index.html
```

## Alternative: Use PCOV Instead

If you prefer PCOV (faster than Xdebug for coverage only):

### Dockerfile Changes:
```dockerfile
# Install PCOV for code coverage (faster alternative to Xdebug)
RUN apk add --no-cache ${PHPIZE_DEPS} && \
    pecl install pcov && \
    docker-php-ext-enable pcov && \
    apk del ${PHPIZE_DEPS}
```

### Rebuild:
```bash
docker compose -p mb down
docker compose -p mb build php --no-cache
docker compose -p mb up -d
```

## Troubleshooting

### Issue: Coverage driver not found
**Symptom:** Tests run but no coverage report generated

**Solution:**
1. Verify extension is loaded: `docker compose -p mb exec php php -m | grep -E "xdebug|pcov"`
2. Rebuild container: `docker compose -p mb build php --no-cache`
3. Restart containers: `docker compose -p mb restart php`

### Issue: Xdebug slows down tests
**Solution:** Use PCOV instead (see alternative above), or configure Xdebug mode:
```ini
xdebug.mode=coverage
xdebug.start_with_request=no
```

### Issue: Coverage report is empty
**Solution:** Check codeception.yml configuration:
```yaml
coverage:
    enabled: true
    include:
        - models/*
        - components/*
        - commands/*
    exclude:
        - models/*Search.php
        - models/forms/*
```

## Performance Notes

- **Xdebug:** Full-featured debugger, slower for coverage (~2-3x normal time)
- **PCOV:** Coverage only, faster (~1.5x normal time)
- **No Coverage:** Fastest (baseline)

For development, use Xdebug. For CI/CD, consider PCOV for faster builds.

## Coverage Report Location

HTML reports are generated in:
```
php/tests/_output/coverage/index.html
```

XML reports (for CI/CD tools like Codecov, Coveralls):
```
php/tests/_output/coverage.xml
```

## Next Steps

1. ✅ Install coverage driver (Xdebug or PCOV)
2. ✅ Update docker-compose.yml with build context
3. ✅ Rebuild containers
4. Run tests with coverage
5. View coverage report
6. (Optional) Integrate with CI/CD for automated coverage tracking

## Verification Checklist

- [ ] Xdebug or PCOV is installed: `php -m | grep -E "xdebug|pcov"`
- [ ] Coverage enabled in codeception.yml
- [ ] Tests run successfully: `codecept run unit`
- [ ] Coverage report generated: `codecept run unit --coverage --coverage-html`
- [ ] HTML report exists: `tests/_output/coverage/index.html`

## Status

✅ Dockerfile updated with Xdebug installation  
✅ docker-compose.yml updated with build context  
⏳ Waiting for container rebuild and verification

