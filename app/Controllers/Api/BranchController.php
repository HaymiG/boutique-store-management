<?php

namespace App\Controllers\Api;

use App\Core\Controller;

class BranchController extends Controller
{
    /**
     * List all active branches
     * GET /api/branches
     */
    public function listBranches()
    {
        if (!$this->isAuthenticated()) {
            $this->respondJsonError('Unauthorized', 401);
            return;
        }

        try {
            $branches = $this->db->query(
                "SELECT id, name, address, phone, email, manager_id, is_active, created_at 
                 FROM branches 
                 WHERE is_active = 1 
                 ORDER BY name ASC"
            );

            $this->respondJson([
                'success' => true,
                'branches' => $branches ? $this->resultToArray($branches) : []
            ]);
        } catch (\Exception $e) {
            $this->respondJsonError('Error fetching branches: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Create a new branch
     * POST /api/branches
     */
    public function createBranch()
    {
        if (!$this->isAuthenticated()) {
            $this->respondJsonError('Unauthorized', 401);
            return;
        }

        // Check if user has permission to create branches
        if (!$this->hasPermission('branches.create')) {
            $this->respondJsonError('Forbidden: Permission denied', 403);
            return;
        }

        try {
            $data = json_decode(file_get_contents('php://input'), true);
            if (empty($data['name'])) {
                $this->respondJsonError('Branch name is required', 400);
                return;
            }

            $branchId = $this->db->insert('branches', [
                'name' => $data['name'],
                'manager_id' => !empty($data['manager_id']) ? $data['manager_id'] : null,
                'address' => $data['address'] ?? null,
                'phone' => $data['phone'] ?? null,
                'email' => $data['email'] ?? null,
                'is_active' => 1,
                'created_at' => date('Y-m-d H:i:s')
            ]);

            if (!$branchId) {
                $this->respondJsonError('Failed to create branch', 500);
                return;
            }

            $this->respondJson([
                'success' => true,
                'message' => 'Branch created successfully',
                'branch' => [
                    'id' => $branchId,
                    'name' => $data['name'],
                    'address' => $data['address'] ?? null,
                    'phone' => $data['phone'] ?? null,
                    'email' => $data['email'] ?? null,
                    'manager_id' => !empty($data['manager_id']) ? $data['manager_id'] : null,
                    'is_active' => 1
                ]
            ]);
        } catch (\Exception $e) {
            $this->respondJsonError('Error creating branch: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Delete a branch
     * DELETE /api/branches/{id}
     */
    public function deleteBranch($id)
    {
        if (!$this->isAuthenticated()) {
            $this->respondJsonError('Unauthorized', 401);
            return;
        }

        if (!$this->hasPermission('branches.delete')) {
            $this->respondJsonError('Forbidden: Permission denied', 403);
            return;
        }

        try {
            // Soft delete the branch instead of hard delete to avoid foreign key constraints
            $deleted = $this->db->update('branches', ['is_active' => 0], 'id = ?', [$id]);

            if (!$deleted) {
                $this->respondJsonError('Branch not found or already deleted', 404);
                return;
            }

            $this->respondJson([
                'success' => true,
                'message' => 'Branch deleted successfully'
            ]);
        } catch (\Exception $e) {
            $this->respondJsonError('Error deleting branch: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Helper: Respond with JSON
     */
    protected function respondJson($data, $statusCode = 200)
    {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }

    /**
     * Helper: Respond with JSON error
     */
    protected function respondJsonError($message, $statusCode = 400)
    {
        $this->respondJson([
            'success' => false,
            'message' => $message
        ], $statusCode);
    }
}
