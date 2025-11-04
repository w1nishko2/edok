#!/bin/bash

# Deploy script for im-edok.ru on Shared Web Hosting
# Usage: bash deploy.sh

echo "🚀 Starting deployment for im-edok.ru..."

# Colors
GREEN='\033[0;32m'
RED='\033[0;31m'
NC='\033[0m' # No Color

# Step 1: Git pull
echo -e "${GREEN}Step 1: Pulling latest code from GitHub...${NC}"
git pull origin main

if [ $? -ne 0 ]; then
    echo -e "${RED}Error: Git pull failed!${NC}"
    exit 1
fi

# Step 2: Check files
echo -e "${GREEN}Step 2: Checking required files...${NC}"
required_files=(".htaccess" "index.html" "public/.htaccess" "public/index.php")

for file in "${required_files[@]}"; do
    if [ -f "$file" ]; then
        echo "✅ $file exists"
    else
        echo -e "${RED}❌ $file missing!${NC}"
    fi
done

# Step 3: Set permissions
echo -e "${GREEN}Step 3: Setting file permissions...${NC}"
chmod 644 .htaccess 2>/dev/null
chmod 644 index.html 2>/dev/null
chmod 755 public/ 2>/dev/null
chmod 644 public/.htaccess 2>/dev/null
chmod 644 public/index.php 2>/dev/null
chmod -R 775 storage/ 2>/dev/null
chmod -R 775 bootstrap/cache/ 2>/dev/null

echo "✅ Permissions set"

# Step 4: Clear Laravel cache
echo -e "${GREEN}Step 4: Clearing Laravel caches...${NC}"
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan cache:clear

echo "✅ Cache cleared"

# Step 5: Optimize (optional)
# Uncomment if you want to cache config and routes in production
# echo -e "${GREEN}Step 5: Optimizing for production...${NC}"
# php artisan config:cache
# php artisan route:cache
# php artisan view:cache

# Final check
echo -e "${GREEN}Step 6: Final verification...${NC}"
if [ -f "public/index.php" ]; then
    echo "✅ public/index.php is ready"
else
    echo -e "${RED}❌ public/index.php not found!${NC}"
    exit 1
fi

echo ""
echo -e "${GREEN}✨ Deployment completed successfully!${NC}"
echo ""
echo "🔍 Next steps:"
echo "1. Visit https://im-edok.ru/ to check if site works"
echo "2. Visit https://im-edok.ru/test.php to verify PHP"
echo "3. Check https://im-edok.ru/sitemap.xml"
echo ""
echo "If you see 403 error, contact hosting support to:"
echo "- Set DocumentRoot to: $(pwd)/public"
echo "- Enable mod_rewrite"
echo "- Allow AllowOverride All"
