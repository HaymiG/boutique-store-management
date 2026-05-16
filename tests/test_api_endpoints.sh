#!/bin/bash
echo "=== Testing Phase 2.3 Endpoints ==="

# 1. Login
echo "Logging in..."
curl -s -X POST -H 'Content-Type: application/json' -d '{"email":"admin@boutique.com","password":"secure_password"}' 'http://localhost:8000/api/login' -c cookie.txt | grep -q '"success":true' || echo "Login failed"

# 2. Get Users
echo "Testing /api/users..."
curl -s -b cookie.txt 'http://localhost:8000/api/users' | head -c 100

# 3. Get Roles
echo -e "\nTesting /api/roles..."
curl -s -b cookie.txt 'http://localhost:8000/api/roles' | head -c 100

# 4. Get Branches
echo -e "\nTesting /api/branches..."
curl -s -b cookie.txt 'http://localhost:8000/api/branches' | head -c 100

