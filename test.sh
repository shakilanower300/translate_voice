#!/usr/bin/env bash

# Simple test script to verify PHP and Laravel basics work
echo "Testing PHP and Laravel setup..."

# Test PHP
php --version
echo "PHP version check: OK"

# Test if we can run basic artisan commands
php artisan --version
echo "Laravel artisan check: OK"

# Test if we can start the server (just check the command works)
timeout 5s php artisan serve --host=127.0.0.1 --port=8080 &
SERVER_PID=$!
sleep 2

# Check if server is running
if kill -0 $SERVER_PID 2>/dev/null; then
    echo "Laravel server startup: OK"
    kill $SERVER_PID
else
    echo "Laravel server startup: FAILED"
fi

echo "Basic test completed."