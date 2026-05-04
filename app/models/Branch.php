<?php
/**
 * Branch Model
 */

namespace App\Models;

use App\Core\Model;

class Branch extends Model
{
    protected $table = 'branches';
    protected $primaryKey = 'id';

    /**
     * Get branch manager
     */
    public function getManager()
    {
        $result = $this->db->query(
            "SELECT * FROM users WHERE id = ?",
            [$this->manager_id]
        );
        return $result->fetch_assoc();
    }

    /**
     * Get all users in branch
     */
    public function getUsers()
    {
        $result = $this->db->query(
            "SELECT * FROM users WHERE branch_id = ? AND is_active = TRUE",
            [$this->id]
        );

        $users = [];
        while ($row = $result->fetch_assoc()) {
            $users[] = $row;
        }
        return $users;
    }

    /**
     * Get total stock value in branch
     */
    public function getTotalStockValue()
    {
        $result = $this->db->query(
            "SELECT SUM(s.quantity * i.selling_price) as total_value 
             FROM stock s
             JOIN items i ON s.item_id = i.id
             WHERE s.branch_id = ?",
            [$this->id]
        );

        $row = $result->fetch_assoc();
        return $row['total_value'] ?? 0;
    }

    /**
     * Get total sales for branch
     */
    public function getTotalSales($from = null, $to = null)
    {
        $query = "SELECT SUM(final_amount) as total FROM sales WHERE branch_id = ?";
        $params = [$this->id];

        if ($from && $to) {
            $query .= " AND created_at BETWEEN ? AND ?";
            $params[] = $from . ' 00:00:00';
            $params[] = $to . ' 23:59:59';
        }

        $result = $this->db->query($query, $params);
        $row = $result->fetch_assoc();
        return $row['total'] ?? 0;
    }

    /**
     * Get number of transactions
     */
    public function getTransactionCount($from = null, $to = null)
    {
        $query = "SELECT COUNT(*) as count FROM sales WHERE branch_id = ?";
        $params = [$this->id];

        if ($from && $to) {
            $query .= " AND created_at BETWEEN ? AND ?";
            $params[] = $from . ' 00:00:00';
            $params[] = $to . ' 23:59:59';
        }

        $result = $this->db->query($query, $params);
        $row = $result->fetch_assoc();
        return $row['count'] ?? 0;
    }
}
?>
