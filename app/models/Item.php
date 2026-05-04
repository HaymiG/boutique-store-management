<?php
/**
 * Item Model
 */

namespace App\Models;

use App\Core\Model;

class Item extends Model
{
    protected $table = 'items';
    protected $primaryKey = 'id';

    /**
     * Get item category
     */
    public function getCategory()
    {
        $result = $this->db->query(
            "SELECT * FROM categories WHERE id = ?",
            [$this->category_id]
        );
        return $result->fetch_assoc();
    }

    /**
     * Get stock in a branch
     */
    public function getStockInBranch($branchId)
    {
        $result = $this->db->query(
            "SELECT * FROM stock WHERE item_id = ? AND branch_id = ?",
            [$this->id, $branchId]
        );
        return $result->fetch_assoc();
    }

    /**
     * Get all stock across branches
     */
    public function getAllStock()
    {
        $result = $this->db->query(
            "SELECT s.*, b.name as branch_name FROM stock s 
             JOIN branches b ON s.branch_id = b.id 
             WHERE s.item_id = ?",
            [$this->id]
        );

        $stock = [];
        while ($row = $result->fetch_assoc()) {
            $stock[] = $row;
        }
        return $stock;
    }

    /**
     * Get total stock across all branches
     */
    public function getTotalStock()
    {
        $result = $this->db->query(
            "SELECT SUM(quantity) as total FROM stock WHERE item_id = ?",
            [$this->id]
        );

        $row = $result->fetch_assoc();
        return $row['total'] ?? 0;
    }

    /**
     * Check if item is low stock in any branch
     */
    public function isLowStock()
    {
        $result = $this->db->query(
            "SELECT COUNT(*) as count FROM stock WHERE item_id = ? AND quantity <= ?",
            [$this->id, $this->reorder_level]
        );

        $row = $result->fetch_assoc();
        return $row['count'] > 0;
    }

    /**
     * Get item by SKU
     */
    public static function findBySku($sku)
    {
        return static::where('sku', $sku);
    }

    /**
     * Get items by category
     */
    public static function getByCategory($categoryId)
    {
        $instance = new static();
        $result = $instance->db->query(
            "SELECT * FROM {$instance->table} WHERE category_id = ? AND is_active = TRUE",
            [$categoryId]
        );

        $items = [];
        while ($row = $result->fetch_assoc()) {
            $items[] = new static($row);
        }
        return $items;
    }

    /**
     * Calculate profit margin
     */
    public function getProfitMargin()
    {
        if ($this->cost_price == 0) {
            return 0;
        }
        return (($this->selling_price - $this->cost_price) / $this->cost_price) * 100;
    }

    /**
     * Calculate profit per item
     */
    public function getProfitPerItem()
    {
        return $this->selling_price - $this->cost_price;
    }
}
?>
