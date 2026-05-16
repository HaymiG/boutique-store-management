<?php

namespace App\Models;

use App\Core\Model;

class Category extends Model
{
    protected $table = 'categories';

    // ---- Queries ----

    public static function all()
    {
        $instance = new static();
        $result = $instance->db->query(
            "SELECT c.*, COUNT(i.id) as item_count
             FROM categories c LEFT JOIN items i ON c.id = i.category_id AND i.is_active = TRUE
             GROUP BY c.id ORDER BY c.name ASC"
        );
        $rows = [];
        while ($row = $result->fetch_assoc()) {
            $rows[] = new static($row);
        }
        return $rows;
    }

    public static function findById($id)
    {
        return static::find($id);
    }

    public static function findByName($name)
    {
        return static::where('name', $name);
    }

    // ---- CRUD ----

    public static function create($data)
    {
        if (empty($data['name'])) {
            throw new \Exception('Category name is required');
        }
        if (static::findByName($data['name'])) {
            throw new \Exception('Category already exists');
        }

        $cat = new static(['name' => $data['name'], 'description' => $data['description'] ?? null]);
        $cat->save();
        return $cat;
    }

    public function updateData($data)
    {
        if (isset($data['name']) && $data['name'] !== $this->name) {
            if (static::findByName($data['name'])) {
                throw new \Exception('Category name already exists');
            }
            $this->attributes['name'] = $data['name'];
        }
        if (array_key_exists('description', $data)) {
            $this->attributes['description'] = $data['description'];
        }
        return $this->save();
    }

    public function delete()
    {
        $result = $this->db->query("SELECT COUNT(*) as c FROM items WHERE category_id = ?", [$this->id]);
        if ((int) $result->fetch_assoc()['c'] > 0) {
            throw new \Exception('Cannot delete category with items');
        }
        parent::delete();
    }

    // ---- Relationship ----

    public function getItems()
    {
        $result = $this->db->query(
            "SELECT * FROM items WHERE category_id = ? AND is_active = TRUE ORDER BY name ASC",
            [$this->id]
        );
        $rows = [];
        while ($row = $result->fetch_assoc()) {
            $rows[] = new Item($row);
        }
        return $rows;
    }

    public function toArray()
    {
        return [
            'id'          => $this->id,
            'name'        => $this->name,
            'description' => $this->description ?? null,
            'item_count'  => $this->item_count ?? 0,
            'created_at'  => $this->created_at,
        ];
    }
}
