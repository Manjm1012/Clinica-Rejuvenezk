#!/bin/sh

# Fix nginx client_max_body_size for video uploads
sed -i 's/tcp_nopush   on;/tcp_nopush   on;\n    client_max_body_size 200M;/' /nginx.conf
echo "✓ Configured nginx client_max_body_size 200M"

# Create storage symlink for public file serving
cd /app
if [ ! -L public/storage ]; then
    php artisan storage:link
    echo "✓ Created storage symlink"
else
    echo "✓ Storage symlink already exists"
fi
