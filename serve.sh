#!/bin/bash
# Development server startup script

echo "Starting Boutique Store Management System..."
echo "Server will be available at: http://localhost:8000"
echo "Press Ctrl+C to stop the server"
echo ""

php -S localhost:8000 -t public public/router.php
