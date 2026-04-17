<?php
/**
 * Role Controller
 * Manager-only role and permission management
 */

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Role;
use App\Models\Permission;

class RoleController extends Controller
{
    /**
     * Require manager role
     */
    protected function authorize()
    {
        $this->requireAuth();
        $this->requireRole('manager');
    }

    /**
     * Show roles and permissions overview
     */
    public function index()
    {
        $roles = Role::all();

        $this->view('admin/roles/index', [
            'pageTitle' => 'Roles & Permissions',
            'roles' => $roles,
        ]);
    }
}
?>
