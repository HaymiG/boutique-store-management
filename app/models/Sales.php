<?php
/**
 * Sales Model
 */

namespace App\Models;

use App\Core\Model;

class Sales extends Model
{
    protected $table = 'sales';
    protected $primaryKey = 'id';

    /**
     * Get sales items
     */
    public function getItems()
    {
        $result = $this->db->query(
            "SELECT si.*, i.name, i.sku FROM sales_items si
             JOIN items i ON si.item_id = i.id
             WHERE si.sales_id = ?",
            [$this->id]
        );

        $items = [];
        while ($row = $result->fetch_assoc()) {
            $items[] = $row;
        }
        return $items;
    }

    /**
     * Get seller info
     */
    public function getSeller()
    {
        $result = $this->db->query(
            "SELECT * FROM users WHERE id = ?",
            [$this->user_id]
        );
        return $result->fetch_assoc();
    }

    /**
     * Get branch info
     */
    public function getBranch()
    {
        $result = $this->db->query(
            "SELECT * FROM branches WHERE id = ?",
            [$this->branch_id]
        );
        return $result->fetch_assoc();
    }

    /**
     * Get sales by date range
     */
    public static function getByDateRange($from, $to, $branchId = null, $userId = null)
    {
        $instance = new static();
        $query = "SELECT * FROM {$instance->table} 
                  WHERE DATE(created_at) BETWEEN ? AND ?";
        $params = [$from, $to];

        if ($branchId) {
            $query .= " AND branch_id = ?";
            $params[] = $branchId;
        }

        if ($userId) {
            $query .= " AND user_id = ?";
            $params[] = $userId;
        }

        $query .= " ORDER BY created_at DESC";

        $result = $instance->db->query($query, $params);

        $sales = [];
        while ($row = $result->fetch_assoc()) {
            $sales[] = new static($row);
        }
        return $sales;
    }

    /**
     * Generate transaction number
     */
    public static function generateTransactionNumber()
    {
        return 'TXN-' . date('YmdHis') . '-' . mt_rand(1000, 9999);
    }

    /**
     * Calculate daily total for seller
     */
    public static function getDailyTotalForUser($userId, $branchId)
    {
        $instance = new static();
        $result = $instance->db->query(
            "SELECT SUM(final_amount) as total, COUNT(*) as transactions
             FROM {$instance->table}
             WHERE user_id = ? AND branch_id = ? AND DATE(created_at) = CURDATE()",
            [$userId, $branchId]
        );

        return $result->fetch_assoc();
    }
}
?>
