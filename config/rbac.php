<?php

/**
 * RBAC Configuration
 * Role-Based Access Control settings and constants
 */

return [
    /**
     * Default roles that should be created on migration
     */
    'default_roles' => [
        'admin' => [
            'description' => 'Administrator with full system access',
            'permissions' => [
                // Users
                'users.create', 'users.read', 'users.update', 'users.delete',
                // Roles & Permissions
                'roles.create', 'roles.read', 'roles.update', 'roles.delete',
                'permissions.create', 'permissions.read', 'permissions.update', 'permissions.delete',
                // Items
                'items.create', 'items.read', 'items.update', 'items.delete',
                // Sales
                'sales.create', 'sales.read', 'sales.update', 'sales.delete',
                // Reports
                'reports.view', 'reports.export',
                // Branches
                'branches.create', 'branches.read', 'branches.update', 'branches.delete',
                // Stock
                'stock.view', 'stock.manage',
                // System
                'system.settings', 'system.logs',
            ],
        ],
        'manager' => [
            'description' => 'Branch manager with management capabilities',
            'permissions' => [
                // Users
                'users.read', 'users.update',
                // Items
                'items.read', 'items.update',
                // Sales
                'sales.create', 'sales.read', 'sales.update',
                // Reports
                'reports.view',
                // Branches
                'branches.read', 'branches.update',
                // Stock
                'stock.view', 'stock.manage',
            ],
        ],
        'staff' => [
            'description' => 'Staff member with limited access',
            'permissions' => [
                // Items
                'items.read',
                // Sales
                'sales.create', 'sales.read',
                // Stock
                'stock.view',
            ],
        ],
        'viewer' => [
            'description' => 'Read-only access to view data',
            'permissions' => [
                // Items
                'items.read',
                // Sales
                'sales.read',
                // Reports
                'reports.view',
                // Stock
                'stock.view',
            ],
        ],
    ],

    /**
     * Available resources for resource-based access control
     * Format: 'resource' => ['action1', 'action2', ...]
     */
    'resources' => [
        'users' => ['create', 'read', 'update', 'delete'],
        'roles' => ['create', 'read', 'update', 'delete'],
        'permissions' => ['create', 'read', 'update', 'delete'],
        'items' => ['create', 'read', 'update', 'delete'],
        'sales' => ['create', 'read', 'update', 'delete'],
        'reports' => ['view', 'export'],
        'branches' => ['create', 'read', 'update', 'delete'],
        'stock' => ['view', 'manage'],
        'system' => ['settings', 'logs'],
    ],

    /**
     * Super admin roles (always have all permissions)
     */
    'super_admin_roles' => [
        'admin',
    ],

    /**
     * Public actions (no authentication required)
     * Format: 'resource' => ['action1', 'action2', ...]
     */
    'public_actions' => [
        'auth' => ['login', 'logout', 'register'],
        'pages' => ['index', 'about', 'contact'],
    ],

    /**
     * Permission caching options
     */
    'cache' => [
        'enabled' => true,
        'ttl' => 3600, // 1 hour in seconds
        'driver' => 'session', // 'session', 'redis', 'file'
    ],

    /**
     * Authorization error pages
     */
    'error_pages' => [
        401 => 'errors/401',
        403 => 'errors/403',
        404 => 'errors/404',
    ],

    /**
     * Permission checking behavior
     */
    'strict_mode' => true, // Require explicit permission (true) or allow if not explicitly denied (false)

    /**
     * Enable permission audit logging
     */
    'audit_logging' => [
        'enabled' => true,
        'log_denied_access' => true,
        'log_permission_changes' => true,
    ],

    /**
     * Default permission naming conventions
     */
    'naming_conventions' => [
        'create' => 'create',
        'read' => 'read',
        'view' => 'view',
        'update' => 'update',
        'edit' => 'update',
        'delete' => 'delete',
        'destroy' => 'delete',
        'manage' => 'manage',
        'admin' => 'admin',
    ],

    /**
     * Role hierarchy (optional)
     * Higher number = higher priority
     */
    'hierarchy' => [
        'admin' => 100,
        'manager' => 50,
        'staff' => 25,
        'viewer' => 10,
    ],
];
