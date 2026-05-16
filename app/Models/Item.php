<?php

namespace App\Models;

use App\Core\Model;

class Item extends Model
{
    protected $table = 'items';

    // ---- Queries ----

    public static function all()
    {
        $instance = new static();
        $result = $instance->db->query(
            "SELECT i.*, c.name as category_name FROM items i
             LEFT JOIN categories c ON i.category_id = c.id
             WHERE i.is_active = TRUE ORDER BY i.name ASC"
        );
        $rows = [];
        while ($row = $result->fetch_assoc()) {
            $rows[] = new static($row);
        }
        return $rows;
    }

    public static function findById($id)
    {
        $instance = new static();
        $result = $instance->db->query(
            "SELECT i.*, c.name as category_name FROM items i
             LEFT JOIN categories c ON i.category_id = c.id
             WHERE i.id = ? LIMIT 1",
            [$id]
        );
        $row = $result->fetch_assoc();
        return $row ? new static($row) : null;
    }

    public static function findBySku($sku)
    {
        return static::where('sku', $sku);
    }

    public static function filter($params = [])
    {
        $instance = new static();
        $where = ['i.is_active = TRUE'];
        $binds = [];

        if (!empty($params['category_id'])) {
            $where[] = 'i.category_id = ?';
            $binds[] = (int) $params['category_id'];
        }
        if (!empty($params['search'])) {
            $where[] = '(i.name LIKE ? OR i.sku LIKE ?)';
            $binds[] = '%' . $params['search'] . '%';
            $binds[] = '%' . $params['search'] . '%';
        }

        $sql = "SELECT i.*, c.name as category_name FROM items i
                LEFT JOIN categories c ON i.category_id = c.id
                WHERE " . implode(' AND ', $where) . " ORDER BY i.name ASC";

        $result = $instance->db->query($sql, $binds);
        $rows = [];
        while ($row = $result->fetch_assoc()) {
            $rows[] = new static($row);
        }
        return $rows;
    }

    // ---- CRUD ----

    public static function create($data)
    {
        $item = new static();
        $item->fill([
            'name'          => $data['name'],
            'sku'           => $data['sku'],
            'category_id'   => (int) $data['category_id'],
            'description'   => $data['description'] ?? null,
            'cost_price'    => (float) $data['cost_price'],
            'selling_price' => (float) $data['selling_price'],
            'reorder_level' => (int) ($data['reorder_level'] ?? 10),
            'is_active'     => 1,
        ]);
        $item->save();
        return $item;
    }

    public function updateData($data)
    {
        $allowed = ['name','sku','category_id','description','cost_price','selling_price','reorder_level','is_active'];
        foreach ($allowed as $field) {
            if (array_key_exists($field, $data)) {
                $this->attributes[$field] = $data[$field];
            }
        }
        return $this->save();
    }

    // ---- Stock ----

    public function getCategory()
    {
        $result = $this->db->query("SELECT * FROM categories WHERE id = ?", [$this->category_id]);
        return $result->fetch_assoc();
    }

    public function getTotalStock()
    {
        $result = $this->db->query("SELECT COALESCE(SUM(quantity),0) as total FROM stock WHERE item_id = ?", [$this->id]);
        return (int) $result->fetch_assoc()['total'];
    }

    public function getStockInBranch($branchId)
    {
        $result = $this->db->query("SELECT * FROM stock WHERE item_id = ? AND branch_id = ?", [$this->id, $branchId]);
        return $result->fetch_assoc();
    }

    public function getAllStock()
    {
        $result = $this->db->query(
            "SELECT s.*, b.name as branch_name FROM stock s
             JOIN branches b ON s.branch_id = b.id WHERE s.item_id = ?",
            [$this->id]
        );
        $rows = [];
        while ($row = $result->fetch_assoc()) {
            $rows[] = $row;
        }
        return $rows;
    }

    public function isLowStock()
    {
        $result = $this->db->query(
            "SELECT COUNT(*) as c FROM stock WHERE item_id = ? AND quantity <= ?",
            [$this->id, $this->reorder_level]
        );
        return (int) $result->fetch_assoc()['c'] > 0;
    }

    // ---- Pricing ----

    public function getProfitMargin()
    {
        if (!$this->cost_price) {
            return 0;
        }
        return round((($this->selling_price - $this->cost_price) / $this->cost_price) * 100, 2);
    }

    public function getProfitPerItem()
    {
        return $this->selling_price - $this->cost_price;
    }

    public function toArray()
    {
        return array_merge($this->attributes, [
            'total_stock'    => $this->getTotalStock(),
            'profit_margin'  => $this->getProfitMargin(),
            'is_low_stock'   => $this->isLowStock(),
        ]);
    }
}
