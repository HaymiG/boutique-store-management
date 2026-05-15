# Boutique Store Management System - Complete Project Overview

## Table of Contents
1. [Project Summary](#project-summary)
2. [Core Features](#core-features)
3. [System Architecture](#system-architecture)
4. [Technology Stack](#technology-stack)
5. [Directory Structure & File Descriptions](#directory-structure--file-descriptions)
6. [Database Schema](#database-schema)
7. [Frontend Components](#frontend-components)
8. [Backend Components](#backend-components)
9. [Authentication & Security](#authentication--security)
10. [Role-Based Access Control (RBAC)](#role-based-access-control-rbac)
11. [API Endpoints](#api-endpoints)
12. [How It All Works Together](#how-it-all-works-together)
13. [Setup & Deployment](#setup--deployment)
14. [Development Workflow](#development-workflow)

---

## Project Summary

**Boutique Store Management System** is a comprehensive web-based inventory and sales management platform designed for multi-branch boutique stores. It enables businesses to:

- 🏪 **Manage multiple store branches** from a centralized dashboard
- 📦 **Track inventory** across locations with real-time stock levels
- 💰 **Record sales transactions** with automatic inventory updates
- 👥 **Control user access** through role-based permissions
- 📊 **Generate detailed reports** on sales trends and inventory status
- 🔐 **Secure operations** with authentication, audit logging, and permissions

**Built with**: PHP 8.0+, MySQL, HTML5, CSS3, JavaScript (Vanilla)

---

## Core Features

### 1. **Inventory Management**
- Track products across multiple branches
- Monitor stock levels with reorder level alerts
- Record damaged goods and adjustments
- View complete stock history with audit trail

### 2. **Sales Processing**
- Create and manage sales transactions
- Automatic inventory deductions
- Real-time transaction tracking
- Sales attribution to sellers

### 3. **Multi-Branch Support**
- Manage multiple store locations
- Branch-specific inventory management
- Manager assignment per branch
- Consolidated reporting across branches

### 4. **Role-Based Access Control**
- 4 predefined roles: Admin, Manager, Staff, Viewer
- Granular permission control
- Fine-grained access to features and data

### 5. **Account Security**
- Email/password authentication
- Password hashing with bcrypt
- Login attempt tracking
- Account locking after failed attempts
- Token-based password reset

### 6. **Audit & Compliance**
- Complete activity logging
- Stock movement history
- User action tracking
- Transaction audit trail

---

## System Architecture

### Architecture Pattern: **Custom MVC Framework**

```
┌─────────────────────────────────────────────────────┐
│                   Frontend (Browser)                 │
│  HTML | CSS | JavaScript (Vanilla) | AJAX Calls     │
└──────────────────┬──────────────────────────────────┘
                   │ HTTP Requests
┌──────────────────▼──────────────────────────────────┐
│              Public Web Server Layer                 │
│  public/index.php → Loads bootstrap.php             │
│  public/router.php → Routes HTTP requests           │
└──────────────────┬──────────────────────────────────┘
                   │ Dispatch to
┌──────────────────▼──────────────────────────────────┐
│              Application Core Layer                  │
│  ┌─────────────────────────────────────────────────┐│
│  │ Router → Middleware → Controller → Model        ││
│  │  Routes requests to appropriate handlers         ││
│  └─────────────────────────────────────────────────┘│
└──────────────────┬──────────────────────────────────┘
                   │ Query/Update
┌──────────────────▼──────────────────────────────────┐
│              Database Layer                         │
│  MySQL Database with 11 Tables                      │
│  Schema: Roles, Permissions, Users, Items, Stock... │
└──────────────────────────────────────────────────────┘
```

---

## Technology Stack

### Backend
- **Language**: PHP 8.0+ (Object-Oriented)
- **Database**: MySQL 8.0+
- **Database Extension**: MySQLi (database abstraction layer included)
- **Architecture Pattern**: Custom MVC Framework

### Frontend
- **Markup**: HTML5
- **Styling**: CSS3 (responsive design)
- **Client-side Logic**: JavaScript (Vanilla - no framework)
- **API Communication**: Fetch API with CSRF token support

### Development Tools
- **Package Manager**: Composer (PHP)
- **Code Quality**: PHPStan, PHP CS Fixer
- **Testing**: PHPUnit
- **Code Style**: PSR-12

---

## Directory Structure & File Descriptions

### 📁 `/app` - Application Core Code

#### `/app/core` - Framework Foundation
- **`Router.php`** - URL routing engine
  - Maps HTTP requests to controller methods
  - Supports: GET, POST, PUT, DELETE, PATCH
  - Route parameter extraction and middleware pipeline

- **`Controller.php`** - Base controller class
  - Provides authentication checks
  - Authorization helpers for role checking
  - JSON response methods
  - Error handling

- **`Model.php`** - Base model class
  - Database query builder functionality
  - CRUD operation helpers
  - Query execution and data mapping

- **`Database.php`** - Database abstraction layer
  - MySQLi connection wrapper
  - Query execution
  - Statement preparation (prevents SQL injection)
  - Result fetching and manipulation

- **`Auth.php`** - Authentication service
  - User login/logout logic
  - Session management
  - Password verification
  - Password reset token generation

- **`Session.php`** - Session handler
  - Session initialization
  - Session data storage/retrieval
  - Prevents session fixation attacks

- **`Logger.php`** - Logging utility
  - Application event logging
  - Error tracking
  - Activity audit trail

- **`Middleware/`** - Request processing pipeline
  - Middleware stack for request handling
  - Cross-cutting concerns (auth, CORS, etc.)

#### `/app/Controllers` - HTTP Request Handlers

- **`AuthController.php`**
  - Public pages: home, about, contact, login, register
  - Authentication endpoints
  - Session management

- **`DashboardController.php`**
  - Dashboard view rendering
  - User-specific data for authenticated users

- **`PasswordResetController.php`**
  - Password reset request handling
  - Token validation
  - Password change processing

- **`UserController.php`**
  - User management API endpoints
  - User list retrieval
  - User creation/update/deletion

- **`RoleController.php`**
  - Role management endpoints
  - Permission assignment
  - Role queries

#### `/app/Models` - Data Objects & Database Queries

- **`User.php`**
  - User data structure
  - Authentication methods
  - Account security (login tracking, locking)
  - Password reset logic

- **`Item.php`**
  - Product/inventory item entity
  - Stock level queries
  - Category associations

- **`Sales.php`**
  - Sales transaction records
  - Line items within sales
  - Transaction details and history

- **`Branch.php`**
  - Store location data
  - Manager assignments
  - Branch details

- **`Role.php`**
  - Role definitions
  - Role-permission relationships

- **`Permission.php`**
  - Permission entity
  - Role assignment

#### `/app/helpers` - Utility Functions

- **`logging.php`**
  - Logging configuration
  - Log message formatting
  - Error/info/warning logging helpers

- **`rbac.php`**
  - Role-based access control helpers
  - Permission checking utilities
  - Authorization helpers for views/controllers

#### `/app/Exceptions` - Custom Exception Classes

- **`AppException.php`** - General application errors
- **`HttpException.php`** - HTTP status exceptions
- **`NotFoundException.php`** - 404 Not Found errors

### 📁 `/config` - Configuration Files

- **`config.php`** - Main configuration
  ```php
  - Database connection details
  - Application timezone
  - Debug mode settings
  - Security settings
  ```

- **`db.php`** - Database credentials
  ```php
  - Host, port, username, password
  - Database name
  - Connection charset
  ```

- **`rbac.php`** - Role and Permission definitions
  ```php
  - Role-permission matrix
  - Feature access control
  - Default role assignments
  ```

### 📁 `/database` - Database Schema & Migrations

- **`store.sql`** - Initial database schema
  - Creates all 11 tables
  - Defines relationships
  - Sets up indexes

- **`ERD.md`** - Entity-Relationship Diagram
  - Visual representation of database structure
  - Table relationships

- **`migrations/`** - Database schema updates
  - `001_add_password_reset.sql` - Password reset functionality
  - `002_add_rbac.sql` - RBAC system tables

### 📁 `/routes` - URL Routing Definition

- **`web.php`** - All application routes
  ```php
  Public Routes:
    / (home)
    /about
    /contact
    /login
    /register
  
  Protected Routes:
    /dashboard
  
  API Routes:
    /api/login (POST)
    /api/logout (POST)
    /api/user (GET)
    /api/password/forgot (POST)
    /api/password/reset (POST)
    /api/password/verify-token (GET)
  ```

### 📁 `/frontend` - Frontend Assets

#### `/frontend/pages` - HTML Templates

- **`index.html`** - Homepage/landing page
- **`login.html`** - User login form
- **`dashboard.html`** - Main application dashboard
- **`about.html`** - About page
- **`contact.html`** - Contact information

#### `/frontend/css` - Stylesheets

- **`base.css`** - Global styles, reset, utilities
- **`auth.css`** - Authentication form styling
- **`dashboard.css`** - Dashboard interface styling
- **`landing.css`** - Homepage styling

#### `/frontend/js` - JavaScript Files

- **`api.js`**
  - API client wrapper
  - Fetch API abstractions
  - CSRF token handling
  - Error handling

- **`app.js`**
  - Main application initialization
  - Event listeners setup
  - Application state management

- **`auth.js`**
  - Login form handling
  - Logout functionality
  - Session management on frontend

- **`dashboard.js`**
  - Dashboard functionality
  - Data display and updates
  - User interface interactions

- **`mockData.js`**
  - Mock data for development/testing
  - Dummy users, items, sales data

#### `/frontend/images` - Media Assets
- Logos, icons, and images

### 📁 `/public` - Web Server Entry Points

- **`index.php`** - Main entry point
  ```php
  1. Loads bootstrap.php
  2. Initializes database connection
  3. Sets up error handling
  4. Routes request to application
  ```

- **`router.php`** - Router configuration
  - Route definitions
  - Request dispatch logic

### 📁 `/storage` - Runtime Data

- **`cache/`** - Cached data
- **`logs/`** - Application logs
- **`uploads/`** - User-uploaded files

### 📁 `/tests` - Test Suite

- **`rbac_test.php`** - Tests for RBAC functionality
  - Role-permission verification
  - Access control testing

### 📁 `/vendor` - Composer Dependencies

- Composer autoloader
- Development tools (PHP CS Fixer, PHPStan, PHPUnit)

### Root Files

- **`bootstrap.php`** - Application bootstrapper
  - Environment setup
  - Class autoloading (PSR-4)
  - Service initialization
  - Error handling configuration

- **`composer.json`** - PHP dependency management
  - Required packages
  - Development tools
  - Autoload configuration

- **`.env.example`** - Environment variable template

- **`serve.sh`** - Development server startup script

- **`srs.md`** - Software Requirements Specification

---

## Database Schema

### 11 Core Tables

#### 1. **roles** - User role definitions
```sql
id, name, description, created_at, updated_at
```
- Predefined roles: admin, manager, staff, viewer

#### 2. **permissions** - Feature access permissions
```sql
id, name, description, resource, action, created_at
```
- Actions: create, read, update, delete
- Resources: users, items, sales, stock, reports, branches, roles

#### 3. **role_permissions** - Maps roles to permissions
```sql
id, role_id (FK), permission_id (FK)
```

#### 4. **users** - System users
```sql
id, name, email, password, phone, role_id (FK), branch_id (FK),
is_active, last_login, login_attempts, locked_at, created_at, updated_at
```
- Login tracking for security
- Account locking after failed attempts

#### 5. **password_resets** - Password reset tokens
```sql
email, token, created_at
```
- Token-based password recovery
- Expiration handling

#### 6. **branches** - Physical store locations
```sql
id, name, location, manager_id (FK), created_at, updated_at
```
- Multi-location support
- Manager assignment

#### 7. **categories** - Product categories
```sql
id, name, description, created_at, updated_at
```
- Product classification (Clothing, Accessories, etc.)

#### 8. **items** - Product master data
```sql
id, name, category_id (FK), price, reorder_level, 
created_by (FK), created_at, updated_at
```
- Product catalog
- Reorder level alerts

#### 9. **stock** - Inventory levels per branch
```sql
id, item_id (FK), branch_id (FK), quantity, reserved, 
damaged, last_counted, created_at, updated_at
```
- Real-time stock tracking
- Reserved quantity for pending orders
- Damaged goods tracking

#### 10. **stock_history** - Audit trail for inventory
```sql
id, item_id (FK), branch_id (FK), type (in/out/damage/transfer/adjustment),
quantity, reason, user_id (FK), created_at
```
- Complete movement history
- Audit trail for compliance

#### 11. **sales** - Sales transactions
```sql
id, branch_id (FK), user_id (FK), total_amount, 
payment_method, created_at, updated_at
```

#### 12. **sales_items** - Line items in sales
```sql
id, sales_id (FK), item_id (FK), quantity, unit_price, 
subtotal, created_at
```

### Database Relationships

```
roles ──┬─→ role_permissions ←─ permissions
        │
        └─→ users (role_id)

users ──┬─→ sales (user_id - seller)
        ├─→ branches (manager_id)
        └─→ stock_history (user_id - who made change)

branches ──┬─→ stock (branch_id)
           ├─→ stock_history (branch_id)
           └─→ sales (branch_id)

items ──┬─→ stock (item_id)
        ├─→ stock_history (item_id)
        └─→ sales_items (item_id)

sales ──→ sales_items
```

---

## Frontend Components

### HTML Pages

#### `index.html` - Homepage
- Landing page introduction
- Links to about, contact, login
- Brand/company information

#### `login.html` - Authentication
- Email/password input fields
- Login form submission to `/api/login`
- Links to registration and password reset
- Error message display

#### `dashboard.html` - Main Application
- Navigation bar with user menu
- Sidebar with feature navigation
- Main content area for different views
- Real-time data displays
- Interactive data tables
- Charts/visualizations (area for extension)

#### `about.html` - Company Information
- Company mission
- Team details
- Brand information

#### `contact.html` - Contact Information
- Contact form
- Business hours
- Location information

### CSS Styling

#### `base.css`
- CSS reset/normalize
- Typography (fonts, sizes, colors)
- Layout utilities (flexbox, grid helpers)
- Button styles
- Form styles
- Responsive breakpoints

#### `auth.css`
- Login form styling
- Form field styling
- Error message display
- Button styling specific to auth

#### `dashboard.css`
- Dashboard layout (sidebar + main)
- Navigation bar styling
- Data table styling
- Card/widget styling
- Responsive dashboard layout

#### `landing.css`
- Hero section styling
- Feature cards
- Footer styling
- Call-to-action buttons

### JavaScript Functionality

#### `api.js` - API Client Helper
```javascript
Key Functions:
- fetch(method, endpoint, body) - Make API calls
- getCSRFToken() - Get token from DOM
- handleResponse(response) - Parse JSON responses
- handleError(error) - Error handling
```

#### `auth.js` - Authentication Logic
```javascript
Key Functions:
- login(email, password) - Submit login form
- logout() - Clear session and redirect
- checkSession() - Verify if user is logged in
- redirectIfNotLoggedIn() - Protect pages
```

#### `app.js` - Main Application
```javascript
Key Functions:
- initialize() - Setup app on page load
- setupEventListeners() - Attach handlers
- loadInitialData() - Fetch user/dashboard data
- removeActiveClass/addActiveClass() - UI state management
```

#### `dashboard.js` - Dashboard Features
```javascript
Key Functions:
- loadDashboardData() - Fetch dashboard info
- displayInventory() - Show stock levels
- displaySales() - Show recent sales
- handleUserActions() - Button clicks, forms
```

#### `mockData.js` - Development Data
- Mock user objects
- Mock inventory items
- Mock sales transactions
- Mock branch data
- Used for testing without database

---

## Backend Components

### Core Framework Files

#### `Router.php` - HTTP Routing
```php
Responsibilities:
1. Parse incoming request (method, path, query params)
2. Match request to defined routes
3. Extract route parameters
4. Instantiate appropriate controller
5. Call controller method
6. Handle middleware pipeline
7. Return response

Key Methods:
- route($method, $path, $callback)
- dispatch($method, $path, $params)
- getRoutes()
- match($pattern, $path) - Regex route matching
```

#### `Controller.php` - Base Controller
```php
Responsibilities:
1. Handle HTTP requests
2. Call model methods
3. Check user permissions
4. Return JSON/HTML responses

Key Methods:
- jsonResponse($data, $status)
- requireAuth() - Verify login
- authorize($permission) - Check permissions
- redirect($path)
```

#### `Model.php` - Data Access Layer
```php
Responsibilities:
1. Define database table structure
2. Build and execute queries
3. Map results to objects
4. Implement CRUD operations

Key Methods:
- find($id) - Retrieve by ID
- findAll() - Retrieve all records
- create($data) - Insert new
- update($id, $data) - Modify existing
- delete($id) - Remove record
- where($column, $operator, $value) - Query builder
```

#### `Database.php` - Database Connection
```php
Responsibilities:
1. Establish MySQLi connection
2. Execute prepared statements (prevent SQL injection)
3. Fetch and format results
4. Handle database errors

Key Methods:
- query($sql, $types, $params) - Execute statement
- fetchOne() - Get single row
- fetchAll() - Get multiple rows
- lastInsertId() - Get last inserted ID
```

#### `Auth.php` - Authentication Service
```php
Responsibilities:
1. Authenticate users (email + password)
2. Verify password hashing
3. Manage login sessions
4. Generate password reset tokens
5. Track login attempts
6. Lock accounts on failed attempts

Key Methods:
- login($email, $password) - Authenticate user
- logout() - Destroy session
- isLoggedIn() - Check session
- getCurrentUser() - Get logged-in user
- generateResetToken($email) - Create reset link
- resetPassword($token, $newPassword)
```

### Controller Implementations

#### `AuthController.php`
```php
Public Routes (No Auth Required):
- GET /  →  index.php
- GET /about  →  about.html
- GET /contact  →  contact.html
- GET /login  →  login.html
- GET /register  →  register.html

API Endpoints:
- POST /api/login  →  Authenticate user
- POST /api/logout  →  End session
```

#### `DashboardController.php`
```php
Protected Routes (Auth Required):
- GET /dashboard  →  Dashboard view

Returns:
- User information
- Branch assignments
- Accessible features based on role
```

#### `UserController.php`
```php
API Endpoints (Protected):
- GET /api/users  →  List users (filtered by role/branch)
- POST /api/users  →  Create new user
- PUT /api/users/:id  →  Update user
- DELETE /api/users/:id  →  Deactivate user
```

#### `PasswordResetController.php`
```php
API Endpoints (Public):
- POST /api/password/forgot  →  Request reset
- POST /api/password/verify-token  →  Validate token
- POST /api/password/reset  →  Complete reset
```

### Model Implementations

All models extend `Model.php` base class:

#### `User.php`
```php
Table: users
Methods:
- findByEmail($email) - Lookup by email
- hasPermission($permission) - Check role permission
- lock() / unlock() - Account security
- recordLoginAttempt()
- isAccountLocked()
- getRole() - Get user's role
```

#### `Item.php`
```php
Table: items
Methods:
- findByCategory($categoryId)
- getStockLevels($branchId)
- isLowStock($branchId) - Check reorder level
- getCategoryName()
- getAllWithStock()
```

#### `Sales.php`
```php
Table: sales, sales_items
Methods:
- createWithItems($data, $items)
- getTransactionDetails($salesId)
- getSalesByDateRange($start, $end)
- getTotalBySellerId($userId)
- getLineItems($salesId)
```

#### `Branch.php`
```php
Table: branches
Methods:
- findWithManager()
- getManagerId()
- getStaffMembers()
- getInventoryByBranch()
```

#### `Role.php` & `Permission.php`
```php
Table: roles, permissions, role_permissions
Methods:
- getPermissions() - Get role's permissions
- hasPermission($permissionName)
- getPerm issionsMatrix() - All role-permission mappings
```

### Helper Functions

#### `logging.php`
```php
Functions:
- logActivity($userId, $action, $resource) - Record action
- logError($message, $context) - Log errors
- getActivityLog($filters) - Retrieve logs
```

#### `rbac.php`
```php
Functions:
- canUser($userId, $action, $resource) - Permission check
- requirePermission($action, $resource) - Enforce in controller
- getUserPermissions($userId) - Get all permissions
```

---

## Authentication & Security

### Authentication Flow

```
User: Enters email/password → login.html

Frontend: form submit
→ POST /api/login with credentials
→ api.js sends CSRF token

Backend: AuthController::login()
→ Find user by email
→ Verify password hash (bcrypt)
→ Create session if valid
→ Return user data

Frontend: Store session
→ Display dashboard
→ Include auth token in future requests
```

### Security Measures

1. **Password Security**
   - Bcrypt hashing (not plaintext)
   - Password verification on login
   - Secure password reset tokens

2. **Session Security**
   - Session-based authentication
   - CSRF token protection on all POST/PUT/DELETE requests
   - Session expiration handling
   - Secure session initialization

3. **Account Security**
   - Login attempt tracking
   - Account locking after repeated failed attempts
   - Failed attempt counter reset on successful login
   - Manual unlock capability

4. **SQL Injection Prevention**
   - Prepared statements throughout
   - Parameter binding in Database.php
   - Query builder pattern

5. **Data Protection**
   - Role-based access control (RBAC)
   - Permission-based feature access
   - Data isolation per role
   - Audit logging of all changes

---

## Role-Based Access Control (RBAC)

### Role Definitions

#### **Admin** - Complete System Control
```
✓ User Management (Create, Read, Update, Delete)
✓ Role & Permission Management
✓ Item/Product Management
✓ Stock Management & Adjustments
✓ Sales Transaction Management
✓ Report Access (All)
✓ Branch Management
✓ System Settings & Configuration
✓ View Audit Logs
```

#### **Manager** - Branch & Inventory Control
```
✓ Read Users in branch
✓ Read/Update Items
✓ Read/Create Stock Adjustments
✓ Read/Create Sales
✓ Read Reports (branch-specific)
✓ Read Branch Details
✗ Cannot: Delete data, manage users, change roles
```

#### **Staff** - Limited Operational Access
```
✓ Read Items
✓ Read Stock Levels
✓ Create Sales Transactions
✓ Read Own Sales (permission check)
✗ Cannot: Modify inventory, create users, access reports
```

#### **Viewer** - Read-Only Access
```
✓ Read Items
✓ Read Stock (viewing only)
✓ Read Sales (viewing only)
✓ Read Reports (viewing only)
✗ Cannot: Create or modify anything
```

### Permission Matrix

```
Permission        Admin  Manager  Staff  Viewer
─────────────────────────────────────────────
user.create       ✓
user.read         ✓      ✓
user.update       ✓
user.delete       ✓

item.create       ✓      ✓
item.read         ✓      ✓        ✓      ✓
item.update       ✓      ✓
item.delete       ✓

stock.create      ✓      ✓
stock.read        ✓      ✓        ✓      ✓
stock.update      ✓      ✓
stock.delete      ✓

sales.create      ✓      ✓        ✓
sales.read        ✓      ✓        ✓      ✓
sales.update      ✓      ✓
sales.delete      ✓

report.view       ✓      ✓                ✓

role.manage       ✓
permission.manage ✓
```

### RBAC Implementation

1. **In Controllers**: 
   ```php
   $this->authorize('item.read');  // Check permission
   ```

2. **In Views**:
   ```php
   <?php if(canUser($userId, 'create', 'sales')): ?>
       <!-- Show create button -->
   <?php endif; ?>
   ```

3. **Database**:
   - role_permissions table links roles to permissions
   - Each user has one assigned role
   - Role permissions determine feature access

---

## API Endpoints

### Public Endpoints (No Authentication)

#### `POST /api/login`
**Request**:
```json
{
  "email": "user@example.com",
  "password": "securePassword123"
}
```

**Response (Success)**: 
```json
{
  "success": true,
  "user": {
    "id": 1,
    "name": "John Doe",
    "email": "john@example.com",
    "role": "admin",
    "branch_id": 1
  }
}
```

**Response (Failure)**:
```json
{
  "success": false,
  "message": "Invalid credentials"
}
```

---

#### `POST /api/password/forgot`
**Request**:
```json
{
  "email": "user@example.com"
}
```

**Response**:
```json
{
  "success": true,
  "message": "Reset link sent to email"
}
```

---

#### `POST /api/password/verify-token`
**Request**:
```json
{
  "token": "reset_token_string"
}
```

**Response**:
```json
{
  "success": true,
  "valid": true
}
```

---

#### `POST /api/password/reset`
**Request**:
```json
{
  "token": "reset_token_string",
  "password": "newPassword123"
}
```

**Response**:
```json
{
  "success": true,
  "message": "Password reset successfully"
}
```

---

### Protected Endpoints (Authentication Required)

#### `POST /api/logout`
**Headers**: CSRF Token required

**Response**:
```json
{
  "success": true,
  "message": "Logged out successfully"
}
```

---

#### `GET /api/user`
**Headers**: User must be authenticated

**Response**:
```json
{
  "id": 1,
  "name": "John Doe",
  "email": "john@example.com",
  "role": "admin",
  "branch_id": 1,
  "permissions": ["user.read", "user.create", ...]
}
```

---

#### `GET /api/users` (Admin/Manager only)
**Query Parameters**:
- `?role=admin` - Filter by role
- `?branch_id=1` - Filter by branch

**Response**:
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "name": "John Doe",
      "email": "john@example.com",
      "role": "admin"
    },
    ...
  ]
}
```

---

#### `POST /api/users` (Admin only)
**Request**:
```json
{
  "name": "Jane Smith",
  "email": "jane@example.com",
  "password": "securePass123",
  "role": "manager",
  "branch_id": 2
}
```

**Response**:
```json
{
  "success": true,
  "user": {
    "id": 5,
    "name": "Jane Smith",
    ...
  }
}
```

---

#### `PUT /api/users/:id` (Admin)
**Request**:
```json
{
  "name": "Jane Doe",
  "role": "staff",
  "is_active": true
}
```

**Response**: Updated user object

---

#### `DELETE /api/users/:id` (Admin)
**Response**:
```json
{
  "success": true,
  "message": "User deactivated"
}
```

---

## How It All Works Together

### Request/Response Cycle

```
1. USER INTERACTION
   ├─ User clicks button/submits form in browser

2. FRONTEND PROCESSING (JavaScript)
   ├─ api.js reads form data
   ├─ Adds CSRF token to request
   ├─ Sends HTTP request (GET/POST/PUT/DELETE)
   └─ Awaits response

3. SERVER ROUTING (public/index.php)
   ├─ bootstrap.php loads application
   ├─ Router matches URL to route
   ├─ Extracts parameters
   └─ Routes to appropriate controller

4. MIDDLEWARE PROCESSING (Middleware/)
   ├─ Parse request body
   ├─ Validate CSRF token
   ├─ Check authentication
   └─ Prepare session

5. CONTROLLER EXECUTION (Controllers/)
   ├─ Verify user authentication
   ├─ Check user permissions (RBAC)
   └─ Call appropriate model method

6. MODEL EXECUTION (Models/)
   ├─ Query/Update database
   ├─ Fetch related data
   ├─ Apply business logic
   └─ Return results

7. DATABASE OPERATION (Database.php)
   ├─ Prepare SQL statement
   ├─ Bind parameters (SQL injection prevention)
   ├─ Execute query
   ├─ Fetch results
   └─ Return formatted data

8. RESPONSE GENERATION (Controller)
   ├─ Format result data (JSON)
   ├─ Add status code
   └─ Send HTTP response

9. FRONTEND HANDLING (JavaScript)
   ├─ api.js receives response
   ├─ Parse JSON
   ├─ Handle errors if any
   ├─ Update DOM
   └─ Display to user

10. USER SEES RESULT
    └─ Data displayed on screen
```

### Example: User Login Flow

```
User Types Email/Password
    ↓
login.html form submit
    ↓
auth.js captures data
    ↓
POST /api/login (via api.js)
    ↓
public/index.php routes to AuthController
    ↓
AuthController::apiLogin()
    ├─ Get email/password from request
    ├─ Call Auth::login()
    │   ├─ User::findByEmail()  [Model queries database]
    │   ├─ password_verify() check
    │   ├─ Session creation
    │   └─ Login timestamp update
    ├─ Return JSON response
    └─ HTTP 200 + user data
    ↓
api.js receives response
    ↓
auth.js stores session token
    ↓
Redirect to /dashboard
    ↓
dashboard.html loads
    ↓
dashboard.js calls fetchCurrentUser()
    ↓
GET /api/user (with auth token)
    ↓
DashboardController gets user data
    ↓
Display user dashboard
```

### Example: Inventory Management Flow

```
Manager wants to view stock levels
    ↓
dashboard.html shows inventory section
    ↓
dashboard.js calls loadInventory()
    ↓
GET /api/items?branch_id=1
    ↓
ItemController::getInventory()
    ├─ Check auth
    ├─ Check permission: item.read
    ├─ Call Item::getStockLevels()
    │   ├─ Query stock table
    │   ├─ Get item details
    │   ├─ Check reorder levels
    │   └─ Return formatted data
    └─ Return JSON
    ↓
api.js receives items with stock
    ↓
dashboard.js builds HTML table
    ↓
User sees inventory display
```

---

## Setup & Deployment

### Prerequisites
- PHP 8.0 or higher
- MySQL 8.0 or higher
- Apache with mod_rewrite enabled
- Composer (for PHP dependencies)

### Installation Steps

1. **Clone/Extract Project**
   ```bash
   cd /var/www/html/
   git clone <repository> boutique-store
   cd boutique-store
   ```

2. **Install Dependencies**
   ```bash
   composer install
   ```

3. **Database Setup**
   ```bash
   # Create database
   mysql -u root -p < database/store.sql
   
   # Run migrations (if updating)
   mysql -u root -p < database/migrations/001_add_password_reset.sql
   mysql -u root -p < database/migrations/002_add_rbac.sql
   ```

4. **Configure Environment**
   ```bash
   cp .env.example .env
   # Edit .env with your database credentials
   ```

5. **Update Configuration Files**
   - `config/db.php` - Database connection details
   - `config/config.php` - Application settings
   - `config/rbac.php` - Role definitions

6. **Set Permissions**
   ```bash
   chmod -R 755 storage/
   chmod -R 755 frontend/
   chmod 644 config/*.php
   ```

7. **Start Development Server**
   ```bash
   ./serve.sh
   # or
   php -S localhost:8000 -t public/
   ```

8. **Access Application**
   - Homepage: `http://localhost:8000/`
   - Login: `http://localhost:8000/login`

### Default Credentials
Check your database seeding or create initial admin user:
- Email: `admin@example.com`
- Password: (set during installation)

### Production Deployment

1. **Apache Virtual Host Configuration**
   ```apache
   <VirtualHost *:80>
       ServerName boutique-store.com
       DocumentRoot /var/www/boutique-store/public
       
       <Directory /var/www/boutique-store/public>
           AllowOverride All
           Require all granted
           
           RewriteEngine On
           RewriteCond %{REQUEST_FILENAME} !-d
           RewriteCond %{REQUEST_FILENAME} !-f
           RewriteRule ^ index.php [QSA,L]
       </Directory>
   </VirtualHost>
   ```

2. **SSL Certificate Setup**
   ```bash
   certbot certonly --webroot -w /var/www/boutique-store -d boutique-store.com
   ```

3. **Environment Configuration**
   - Set `DEBUG = false` in `config.php`
   - Secure database credentials
   - Configure email for password resets

---

## Development Workflow

### Key Development Commands

```bash
# Run tests
npm test
npm test:coverage

# Code quality checks
npm lint          # Check code style
npm lint:fix      # Auto-fix issues
npm analyze       # Static analysis

# Development server
./serve.sh

# Database management
mysql -u root -p boutique_store < database/store.sql
```

### Project Files for Presentation

1. **THIS FILE** - `PROJECT_OVERVIEW.md`
   - Complete project documentation
   - Every file explained
   - Architecture overview
   - Use for presentation reference

2. **Architecture Diagram** - Create visual showing layers
   - Frontend → Router → Controllers → Models → Database

3. **Database E-R Diagram** - Available in `database/ERD.md`
   - Shows all table relationships

4. **API Documentation** - See `docs/API_DOCUMENTATION.md`
   - Detailed endpoint specifications

5. **Code Examples** - Refer to actual files:
   - Controller example: `app/Controllers/AuthController.php`
   - Model example: `app/Models/User.php`
   - Route example: `routes/web.php`

### Key Files to Review

**For Presentation**:
1. `routes/web.php` - See all application routes
2. `config/rbac.php` - See role definitions
3. `app/core/Auth.php` - See authentication logic
4. `database/store.sql` - See database schema
5. `public/index.php` - See application bootstrap
6. `frontend/js/api.js` - See API communication
7. `frontend/js/dashboard.js` - See frontend logic

**For Understanding Specific Features**:
- **User Management**: `UserController.php` + `User.php` + `users` table
- **Sales**: `SalesController.php` + `Sales.php` + `sales` tables
- **Inventory**: `Item.php` + `stock` tables + `stock_history`
- **Reports**: Check for reporting controller (planned feature)
- **RBAC**: `config/rbac.php` + `role_permissions` table + `rbac.php` helper

---

## Next Steps & Future Enhancements

### Currently Implemented ✅
- Core MVC framework
- User authentication
- Password reset system
- RBAC system
- Dashboard foundation
- Frontend pages and styling
- API structure

### Planned Features ⏳
- Full API v1 endpoints
- Sales operations module
- Inventory management module
- Advanced reporting and analytics
- Stock transfer workflows
- User role management interface
- System administration panel
- Email notifications

### Suggested Enhancements
- Add charts/graphs to dashboard
- Implement advanced search filters
- Add batch import/export functionality
- Real-time inventory notifications
- Mobile-responsive improvements
- API rate limiting
- Two-factor authentication
- Audit log viewer
- User preference settings

---

## Summary

The Boutique Store Management System is a **professional-grade inventory and sales management application** built with:

- **Clean Architecture**: MVC pattern with separation of concerns
- **Security First**: Bcrypt passwords, CSRF protection, SQL injection prevention, RBAC
- **Scalability**: Supports multiple branches and users
- **Maintainability**: Well-organized code, clear naming, PSR standards
- **Extensibility**: Base classes for controllers and models, middleware pipeline

Every component is designed to work together seamlessly, from user login through sales processing to inventory management and reporting. The RBAC system ensures that users can only access the features and data appropriate to their role.

Use this document as your complete reference for the presentation. Each section explains what code does and why it matters.

**Good luck with your presentation!** 🚀
