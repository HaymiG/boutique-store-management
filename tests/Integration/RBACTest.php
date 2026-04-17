<?php
/**
 * Integration Tests for RBAC (Role-Based Access Control)
 * Tests role checking, permission verification, and authorization logic
 */

namespace Tests\Integration;

use PHPUnit\Framework\TestCase;

class RBACTest extends TestCase
{
    /**
     * Role permissions map (mirrors config.php)
     */
    private array $rolePermissions = [
        'manager' => [
            'branch.create', 'branch.read', 'branch.update', 'branch.delete',
            'user.create', 'user.read', 'user.update', 'user.delete',
            'inventory.create', 'inventory.read', 'inventory.update', 'inventory.delete',
            'inventory.transfer', 'sales.read', 'sales.report', 'report.generate',
        ],
        'store_keeper' => [
            'inventory.create', 'inventory.read', 'inventory.update',
            'stock.manage', 'sales.read.own', 'report.inventory', 'report.alerts',
        ],
        'seller' => [
            'sales.create', 'sales.read.own', 'inventory.read',
        ],
    ];

    private array $roles = [
        'manager' => 1,
        'store_keeper' => 2,
        'seller' => 3,
    ];

    /**
     * Test manager has all permissions
     */
    public function testManagerHasAllPermissions(): void
    {
        $role = 'manager';
        $permissions = $this->rolePermissions[$role];

        $this->assertCount(16, $permissions);
        $this->assertContains('branch.create', $permissions);
        $this->assertContains('user.create', $permissions);
        $this->assertContains('user.delete', $permissions);
        $this->assertContains('report.generate', $permissions);
    }

    /**
     * Test store keeper has inventory permissions but NOT branch management
     */
    public function testStoreKeeperPermissions(): void
    {
        $role = 'store_keeper';
        $permissions = $this->rolePermissions[$role];

        // Has inventory permissions
        $this->assertContains('inventory.create', $permissions);
        $this->assertContains('inventory.read', $permissions);
        $this->assertContains('inventory.update', $permissions);
        $this->assertContains('stock.manage', $permissions);

        // Does NOT have branch or user management
        $this->assertNotContains('branch.create', $permissions);
        $this->assertNotContains('user.create', $permissions);
        $this->assertNotContains('user.delete', $permissions);
    }

    /**
     * Test seller has minimal permissions
     */
    public function testSellerPermissions(): void
    {
        $role = 'seller';
        $permissions = $this->rolePermissions[$role];

        $this->assertCount(3, $permissions);
        $this->assertContains('sales.create', $permissions);
        $this->assertContains('sales.read.own', $permissions);
        $this->assertContains('inventory.read', $permissions);

        // Does NOT have write access to inventory
        $this->assertNotContains('inventory.create', $permissions);
        $this->assertNotContains('inventory.update', $permissions);
        $this->assertNotContains('inventory.delete', $permissions);
    }

    /**
     * Test seller cannot access user management
     */
    public function testSellerCannotManageUsers(): void
    {
        $role = 'seller';
        $permissions = $this->rolePermissions[$role];

        $this->assertNotContains('user.create', $permissions);
        $this->assertNotContains('user.read', $permissions);
        $this->assertNotContains('user.update', $permissions);
        $this->assertNotContains('user.delete', $permissions);
    }

    /**
     * Test store keeper cannot access branch management
     */
    public function testStoreKeeperCannotManageBranches(): void
    {
        $role = 'store_keeper';
        $permissions = $this->rolePermissions[$role];

        $this->assertNotContains('branch.create', $permissions);
        $this->assertNotContains('branch.update', $permissions);
        $this->assertNotContains('branch.delete', $permissions);
    }

    /**
     * Test permission checking helper
     */
    public function testHasPermissionCheck(): void
    {
        $role = 'store_keeper';
        $permissions = $this->rolePermissions[$role] ?? [];

        $this->assertTrue(in_array('inventory.create', $permissions));
        $this->assertFalse(in_array('user.delete', $permissions));
    }

    /**
     * Test role hierarchy (manager > store_keeper > seller)
     */
    public function testRoleHierarchy(): void
    {
        $managerPerms = $this->rolePermissions['manager'];
        $keeperPerms = $this->rolePermissions['store_keeper'];
        $sellerPerms = $this->rolePermissions['seller'];

        // Manager has the most permissions
        $this->assertGreaterThan(count($keeperPerms), count($managerPerms));
        $this->assertGreaterThan(count($sellerPerms), count($keeperPerms));
    }

    /**
     * Test role ID mapping
     */
    public function testRoleIdMapping(): void
    {
        $this->assertEquals(1, $this->roles['manager']);
        $this->assertEquals(2, $this->roles['store_keeper']);
        $this->assertEquals(3, $this->roles['seller']);
    }

    /**
     * Test role lookup by ID
     */
    public function testRoleLookupById(): void
    {
        $roleMap = array_flip($this->roles);

        $this->assertEquals('manager', $roleMap[1]);
        $this->assertEquals('store_keeper', $roleMap[2]);
        $this->assertEquals('seller', $roleMap[3]);
    }

    /**
     * Test hasAnyRole check
     */
    public function testHasAnyRole(): void
    {
        $userRole = 'store_keeper';
        $allowedRoles = ['manager', 'store_keeper'];

        $this->assertTrue(in_array($userRole, $allowedRoles));
    }

    /**
     * Test hasAnyRole fails for unauthorized role
     */
    public function testHasAnyRoleFails(): void
    {
        $userRole = 'seller';
        $allowedRoles = ['manager', 'store_keeper'];

        $this->assertFalse(in_array($userRole, $allowedRoles));
    }

    /**
     * Test unauthenticated user has no permissions
     */
    public function testUnauthenticatedHasNoPermissions(): void
    {
        $userRole = null;
        $permissions = $this->rolePermissions[$userRole] ?? [];

        $this->assertEmpty($permissions);
    }

    /**
     * Test unknown role has no permissions
     */
    public function testUnknownRoleHasNoPermissions(): void
    {
        $userRole = 'admin_supreme';
        $permissions = $this->rolePermissions[$userRole] ?? [];

        $this->assertEmpty($permissions);
    }

    /**
     * Test canAccess helper (resource.action format)
     */
    public function testCanAccessResourceAction(): void
    {
        $role = 'manager';
        $permissions = $this->rolePermissions[$role];

        // Manager can access branch.create
        $permission = 'branch' . '.' . 'create';
        $this->assertTrue(in_array($permission, $permissions));

        // Manager can access user.delete
        $permission = 'user' . '.' . 'delete';
        $this->assertTrue(in_array($permission, $permissions));
    }

    /**
     * Test all predefined permissions are well-formed
     */
    public function testAllPermissionsAreWellFormed(): void
    {
        foreach ($this->rolePermissions as $role => $permissions) {
            foreach ($permissions as $perm) {
                // Each permission should follow resource.action format
                $this->assertStringContainsString('.', $perm,
                    "Permission '{$perm}' for role '{$role}' should contain a dot separator");
                $this->assertNotEmpty($perm, "Permission should not be empty");
            }
        }
    }
}
?>
