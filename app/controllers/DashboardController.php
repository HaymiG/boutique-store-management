<?php
/**
 * Dashboard Controller
 * Renders the main dashboard view with role-appropriate data
 */

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Database;

class DashboardController extends Controller
{
    /**
     * Require authentication for all dashboard actions
     */
    protected function authorize()
    {
        $this->requireAuth();
    }

    /**
     * Show the dashboard
     */
    public function index()
    {
        $db = Database::getInstance();
        $role = $this->user['role'] ?? 'seller';

        $data = [
            'pageTitle' => 'Dashboard',
            'role' => $role,
        ];

        // Fetch stats based on role
        $data['stats'] = $this->getStats($db, $role);
        $data['recentSales'] = $this->getRecentSales($db, $role);

        $this->view('dashboard', $data);
    }

    /**
     * Get dashboard statistics
     */
    private function getStats($db, $role)
    {
        $stats = [];

        // Today's revenue
        $result = $db->query(
            "SELECT COALESCE(SUM(final_amount), 0) as total 
             FROM sales WHERE DATE(created_at) = CURDATE()"
        );
        $row = $result->fetch_assoc();
        $stats['today_revenue'] = $row['total'];

        // Total active items
        $result = $db->query("SELECT COUNT(*) as count FROM items WHERE is_active = 1");
        $row = $result->fetch_assoc();
        $stats['active_items'] = $row['count'];

        // Active branches
        $result = $db->query("SELECT COUNT(*) as count FROM branches WHERE is_active = 1");
        $row = $result->fetch_assoc();
        $stats['active_branches'] = $row['count'];

        // Total users (manager only)
        if ($role === 'manager') {
            $result = $db->query("SELECT COUNT(*) as count FROM users WHERE is_active = 1");
            $row = $result->fetch_assoc();
            $stats['active_users'] = $row['count'];

            // Low stock alerts
            $result = $db->query(
                "SELECT COUNT(*) as count FROM stock s 
                 JOIN items i ON s.item_id = i.id 
                 WHERE s.quantity <= i.reorder_level AND i.is_active = 1"
            );
            $row = $result->fetch_assoc();
            $stats['low_stock_count'] = $row['count'];
        }

        return $stats;
    }

    /**
     * Get recent sales
     */
    private function getRecentSales($db, $role)
    {
        $query = "SELECT s.*, u.first_name, u.last_name, b.name as branch_name 
                  FROM sales s
                  JOIN users u ON s.user_id = u.id
                  JOIN branches b ON s.branch_id = b.id";

        $params = [];

        // Sellers only see their own sales
        if ($role === 'seller') {
            $query .= " WHERE s.user_id = ?";
            $params[] = $this->user['id'];
        }

        $query .= " ORDER BY s.created_at DESC LIMIT 10";

        $result = $db->query($query, $params);
        $sales = [];
        while ($row = $result->fetch_assoc()) {
            $sales[] = $row;
        }
        return $sales;
    }
}
?>
