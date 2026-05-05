<?php

/**
 * Base Model Class
 * All models extend from this base class
 */

namespace App\Core;

class Model
{
    /**
     * Database instance
     */
    protected $db;

    /**
     * Table name
     */
    protected $table;

    /**
     * Primary key
     */
    protected $primaryKey = 'id';

    /**
     * Model properties
     */
    protected $attributes = [];

    /**
     * Original attributes (for change detection)
     */
    protected $original = [];

    /**
     * Constructor
     */
    public function __construct($data = [])
    {
        $this->db = Database::getInstance();
        $this->fill($data);
    }

    /**
     * Fill model with data
     */
    public function fill($data = [])
    {
        foreach ($data as $key => $value) {
            $this->attributes[$key] = $value;
            $this->original[$key] = $value;
        }
        return $this;
    }

    /**
     * Get attribute
     */
    public function __get($name)
    {
        return $this->attributes[$name] ?? null;
    }

    /**
     * Set attribute
     */
    public function __set($name, $value)
    {
        $this->attributes[$name] = $value;
    }

    /**
     * Check if attribute exists
     */
    public function __isset($name)
    {
        return isset($this->attributes[$name]);
    }

    /**
     * Save model (insert or update)
     */
    public function save()
    {
        if (isset($this->attributes[$this->primaryKey])) {
            return $this->update();
        } else {
            return $this->insert();
        }
    }

    /**
     * Insert model
     */
    protected function insert()
    {
        $columns = implode(', ', array_keys($this->attributes));
        $placeholders = implode(', ', array_fill(0, count($this->attributes), '?'));

        $sql = "INSERT INTO {$this->table} ({$columns}) VALUES ({$placeholders})";

        $id = $this->db->insert($this->table, $this->attributes);
        $this->attributes[$this->primaryKey] = $id;

        return $id;
    }

    /**
     * Update model
     */
    protected function update()
    {
        $changes = $this->getChanges();

        if (empty($changes)) {
            return true;
        }

        $this->db->update(
            $this->table,
            $changes,
            "{$this->primaryKey} = ?",
            [$this->attributes[$this->primaryKey]]
        );

        // Update original data
        $this->original = $this->attributes;

        return true;
    }

    /**
     * Delete model
     */
    public function delete()
    {
        if (!isset($this->attributes[$this->primaryKey])) {
            throw new \Exception('Cannot delete model without primary key');
        }

        return $this->db->delete(
            $this->table,
            "{$this->primaryKey} = ?",
            [$this->attributes[$this->primaryKey]]
        );
    }

    /**
     * Get changes since fill
     */
    protected function getChanges()
    {
        $changes = [];

        foreach ($this->attributes as $key => $value) {
            if (!isset($this->original[$key]) || $this->original[$key] !== $value) {
                $changes[$key] = $value;
            }
        }

        return $changes;
    }

    /**
     * Find by primary key
     */
    public static function find($id)
    {
        $instance = new static();
        $result = $instance->db->query(
            "SELECT * FROM {$instance->table} WHERE {$instance->primaryKey} = ? LIMIT 1",
            [$id]
        );

        $data = $result->fetch_assoc();

        if (!$data) {
            return null;
        }

        return new static($data);
    }

    /**
     * Find all records
     */
    public static function all()
    {
        $instance = new static();
        $result = $instance->db->query("SELECT * FROM {$instance->table}");

        $records = [];
        while ($row = $result->fetch_assoc()) {
            $records[] = new static($row);
        }

        return $records;
    }

    /**
     * Find by column
     */
    public static function where($column, $value)
    {
        $instance = new static();
        $result = $instance->db->query(
            "SELECT * FROM {$instance->table} WHERE {$column} = ? LIMIT 1",
            [$value]
        );

        $data = $result->fetch_assoc();

        if (!$data) {
            return null;
        }

        return new static($data);
    }

    /**
     * Find multiple records by column
     */
    public static function whereMany($column, $value)
    {
        $instance = new static();
        $result = $instance->db->query(
            "SELECT * FROM {$instance->table} WHERE {$column} = ?",
            [$value]
        );

        $records = [];
        while ($row = $result->fetch_assoc()) {
            $records[] = new static($row);
        }

        return $records;
    }

    /**
     * Get all records with pagination
     */
    public static function paginate($page = 1, $perPage = PAGINATION_PER_PAGE)
    {
        $instance = new static();
        $offset = ($page - 1) * $perPage;

        $countResult = $instance->db->query("SELECT COUNT(*) as count FROM {$instance->table}");
        $countRow = $countResult->fetch_assoc();
        $total = $countRow['count'];

        $result = $instance->db->query(
            "SELECT * FROM {$instance->table} LIMIT ? OFFSET ?",
            [$perPage, $offset]
        );

        $records = [];
        while ($row = $result->fetch_assoc()) {
            $records[] = new static($row);
        }

        return [
            'data' => $records,
            'total' => $total,
            'pages' => ceil($total / $perPage),
            'current_page' => $page,
            'per_page' => $perPage,
        ];
    }

    /**
     * Get model as array
     */
    public function toArray()
    {
        return $this->attributes;
    }

    /**
     * Get model as JSON
     */
    public function toJson()
    {
        return json_encode($this->attributes);
    }
}
