<?php
/**
 * Admin User Controller
 * Manager-only user management (CRUD, role assignment, password reset)
 */

namespace App\Controllers;

use App\Core\Controller;
use App\Models\User;
use App\Models\Role;
use App\Models\Branch;

class AdminUserController extends Controller
{
    /**
     * Require manager role for all actions
     */
    protected function authorize()
    {
        $this->requireAuth();
        $this->requireRole('manager');
    }

    /**
     * List all users with pagination and filters
     */
    public function index()
    {
        $page = (int)($this->request('page') ?? 1);
        $filters = [
            'search' => $this->request('search'),
            'role_id' => $this->request('role_id'),
            'is_active' => $this->request('is_active'),
        ];

        $users = User::paginateWithFilters($page, PAGINATION_PER_PAGE, $filters);
        $roles = Role::all();

        $this->view('admin/users/index', [
            'pageTitle' => 'User Management',
            'users' => $users,
            'roles' => $roles,
            'filters' => $filters,
        ]);
    }

    /**
     * Show user details
     */
    public function show($id)
    {
        $user = User::findById($id);
        if (!$user) {
            $this->abort(404, 'User not found');
        }

        $role = $user->getRole();
        $branch = $user->getBranch();
        $permissions = $user->getPermissions();

        $this->view('admin/users/form', [
            'pageTitle' => 'View User',
            'user' => $user,
            'role' => $role,
            'branch' => $branch,
            'permissions' => $permissions,
            'mode' => 'view',
            'roles' => Role::all(),
            'branches' => Branch::all(),
        ]);
    }

    /**
     * Show create user form
     */
    public function create()
    {
        $this->view('admin/users/form', [
            'pageTitle' => 'Create User',
            'user' => null,
            'mode' => 'create',
            'roles' => Role::all(),
            'branches' => Branch::all(),
            'old' => $this->session->getFlash('old', []),
            'errors' => $this->session->getFlash('errors', []),
        ]);
    }

    /**
     * Store new user
     */
    public function store()
    {
        $this->validateCsrf();

        $data = [
            'first_name' => trim($this->request('first_name', '')),
            'last_name' => trim($this->request('last_name', '')),
            'username' => trim($this->request('username', '')),
            'email' => trim($this->request('email', '')),
            'phone' => trim($this->request('phone', '')),
            'password' => $this->request('password', ''),
            'password_confirmation' => $this->request('password_confirmation', ''),
            'role_id' => $this->request('role_id'),
            'branch_id' => $this->request('branch_id'),
            'is_active' => $this->request('is_active', 1),
        ];

        // Validation
        $errors = $this->validateUserData($data, 'create');

        if (!empty($errors)) {
            $this->session->setFlash('errors', $errors);
            $this->session->setFlash('old', $data);
            $this->redirect('/manager/users/create');
            return;
        }

        // Create user
        $user = User::createUser($data);

        if ($user) {
            $this->session->setFlash('success', 'User "' . $user->getFullName() . '" created successfully.');
            $this->redirect('/manager/users');
        } else {
            $this->session->setFlash('error', 'Failed to create user. Please try again.');
            $this->session->setFlash('old', $data);
            $this->redirect('/manager/users/create');
        }
    }

    /**
     * Show edit user form
     */
    public function edit($id)
    {
        $user = User::findById($id);
        if (!$user) {
            $this->abort(404, 'User not found');
        }

        $this->view('admin/users/form', [
            'pageTitle' => 'Edit User',
            'user' => $user,
            'mode' => 'edit',
            'roles' => Role::all(),
            'branches' => Branch::all(),
            'old' => $this->session->getFlash('old', []),
            'errors' => $this->session->getFlash('errors', []),
        ]);
    }

    /**
     * Update user
     */
    public function update($id)
    {
        $this->validateCsrf();

        $user = User::findById($id);
        if (!$user) {
            $this->abort(404, 'User not found');
        }

        $data = [
            'first_name' => trim($this->request('first_name', '')),
            'last_name' => trim($this->request('last_name', '')),
            'username' => trim($this->request('username', '')),
            'email' => trim($this->request('email', '')),
            'phone' => trim($this->request('phone', '')),
            'password' => $this->request('password', ''),
            'password_confirmation' => $this->request('password_confirmation', ''),
            'role_id' => $this->request('role_id'),
            'branch_id' => $this->request('branch_id'),
            'is_active' => $this->request('is_active', 1),
        ];

        // Validation
        $errors = $this->validateUserData($data, 'edit', $id);

        if (!empty($errors)) {
            $this->session->setFlash('errors', $errors);
            $this->session->setFlash('old', $data);
            $this->redirect("/manager/users/{$id}/edit");
            return;
        }

        $user->updateUser($data);

        $this->session->setFlash('success', 'User "' . $user->getFullName() . '" updated successfully.');
        $this->redirect('/manager/users');
    }

    /**
     * Soft delete user
     */
    public function destroy($id)
    {
        $this->validateCsrf();

        $user = User::findById($id);
        if (!$user) {
            $this->abort(404, 'User not found');
        }

        // Prevent self-deletion
        if ($user->id == $this->user['id']) {
            $this->session->setFlash('error', 'You cannot delete your own account.');
            $this->redirect('/manager/users');
            return;
        }

        $user->softDelete();
        $this->session->setFlash('success', 'User "' . $user->getFullName() . '" has been deactivated.');
        $this->redirect('/manager/users');
    }

    /**
     * Reset user password (admin action)
     */
    public function resetPassword($id)
    {
        $this->validateCsrf();

        $user = User::findById($id);
        if (!$user) {
            $this->abort(404, 'User not found');
        }

        $newPassword = $this->request('new_password', '');
        
        if (empty($newPassword) || !User::isValidPassword($newPassword)) {
            $this->session->setFlash('error', 'Password must be 8+ characters with uppercase, lowercase, and number.');
            $this->redirect("/manager/users/{$id}/edit");
            return;
        }

        $user->setPassword($newPassword);
        $user->unlock(); // Also unlock the account
        $user->save();

        $this->session->setFlash('success', 'Password reset for "' . $user->getFullName() . '" completed.');
        $this->redirect('/manager/users');
    }

    /**
     * Validate user data
     */
    private function validateUserData($data, $mode = 'create', $userId = null)
    {
        $errors = [];

        if (empty($data['first_name'])) {
            $errors['first_name'] = 'First name is required';
        }
        if (empty($data['last_name'])) {
            $errors['last_name'] = 'Last name is required';
        }
        if (empty($data['username'])) {
            $errors['username'] = 'Username is required';
        } elseif (strlen($data['username']) < 3) {
            $errors['username'] = 'Username must be at least 3 characters';
        } elseif (!User::isUsernameUnique($data['username'], $userId)) {
            $errors['username'] = 'Username already taken';
        }
        if (empty($data['email'])) {
            $errors['email'] = 'Email is required';
        } elseif (!User::isValidEmail($data['email'])) {
            $errors['email'] = 'Invalid email format';
        } elseif (!User::isEmailUnique($data['email'], $userId)) {
            $errors['email'] = 'Email already in use';
        }
        if (empty($data['role_id'])) {
            $errors['role_id'] = 'Role is required';
        }

        // Password validation (required on create, optional on edit)
        if ($mode === 'create') {
            if (empty($data['password'])) {
                $errors['password'] = 'Password is required';
            } elseif (!User::isValidPassword($data['password'])) {
                $errors['password'] = 'Password must be 8+ characters with uppercase, lowercase, and number';
            } elseif ($data['password'] !== $data['password_confirmation']) {
                $errors['password_confirmation'] = 'Passwords do not match';
            }
        } elseif (!empty($data['password'])) {
            if (!User::isValidPassword($data['password'])) {
                $errors['password'] = 'Password must be 8+ characters with uppercase, lowercase, and number';
            } elseif ($data['password'] !== $data['password_confirmation']) {
                $errors['password_confirmation'] = 'Passwords do not match';
            }
        }

        return $errors;
    }
}
?>
