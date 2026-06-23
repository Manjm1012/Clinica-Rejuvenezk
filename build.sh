#!/bin/bash
# Build script for Dokploy - executed before starting the app
echo "▶ Configuring nginx for large file uploads..."
sed -i 's/tcp_nopush   on;/tcp_nopush   on;\n    client_max_body_size 200M;/' /nginx.conf 2>/dev/null || true
echo "✓ Nginx configured with 200M upload limit"
