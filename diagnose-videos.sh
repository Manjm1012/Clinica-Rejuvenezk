#!/bin/bash
# Quick diagnostic for video upload issues on production server

echo "🔍 Video Upload Diagnostic for Rejuvenezk"
echo ""
echo "Step 1: Check if files exist in storage..."
ls -lah storage/app/public/gallery/videos/ 2>/dev/null || echo "❌ Directory not found or empty"

echo ""
echo "Step 2: Check if storage symlink exists..."
if [ -L "public/storage" ]; then
    echo "✅ Symlink exists"
    ls -l public/storage | head -1
else
    echo "❌ Symlink MISSING - Run: php artisan storage:link"
fi

echo ""
echo "Step 3: Check file permissions..."
stat storage/app/public/gallery/videos/ 2>/dev/null | grep "Access:" || echo "Could not check permissions"

echo ""
echo "Step 4: Test with artisan command..."
php artisan diagnose:video-upload

echo ""
echo "Step 5: To test in browser, open:"
echo "https://rejuvenezk.turnow.cloud/admin"
echo "Then upload a small MP4 test video and check the preview"
echo ""
echo "If video still doesn't show, check browser console (F12) for:"
echo "1. Network tab - check if /media/gallery/videos/... returns 200"
echo "2. Check Content-Type header shows 'video/mp4' or 'video/webm'"
