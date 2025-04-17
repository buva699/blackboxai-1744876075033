<?php
require_once __DIR__ . '/Database.php';

abstract class Model {
    protected $db;
    protected $table;
    protected $primaryKey = 'id';
    protected $fillable = [];
    protected $timestamps = true;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    /**
     * Find a record by ID
     */
    public function find($id) {
        $sql = "SELECT * FROM {$this->table} WHERE {$this->primaryKey} = ?";
        return $this->db->fetchOne($sql, [$id]);
    }

    /**
     * Get all records
     */
    public function all($orderBy = null) {
        $sql = "SELECT * FROM {$this->table}";
        if ($orderBy) {
            $sql .= " ORDER BY $orderBy";
        }
        return $this->db->fetchAll($sql);
    }

    /**
     * Create a new record
     */
    public function create($data) {
        $filteredData = $this->filterData($data);
        
        if ($this->timestamps) {
            $filteredData['created_at'] = date('Y-m-d H:i:s');
        }

        return $this->db->insert($this->table, $filteredData);
    }

    /**
     * Update a record
     */
    public function update($id, $data) {
        $filteredData = $this->filterData($data);
        
        if ($this->timestamps) {
            $filteredData['updated_at'] = date('Y-m-d H:i:s');
        }

        return $this->db->update(
            $this->table, 
            $filteredData, 
            "{$this->primaryKey} = ?", 
            [$id]
        );
    }

    /**
     * Delete a record
     */
    public function delete($id) {
        return $this->db->delete(
            $this->table, 
            "{$this->primaryKey} = ?", 
            [$id]
        );
    }

    /**
     * Find records by a specific field
     */
    public function findBy($field, $value) {
        $sql = "SELECT * FROM {$this->table} WHERE $field = ?";
        return $this->db->fetchAll($sql, [$value]);
    }

    /**
     * Find one record by a specific field
     */
    public function findOneBy($field, $value) {
        $sql = "SELECT * FROM {$this->table} WHERE $field = ? LIMIT 1";
        return $this->db->fetchOne($sql, [$value]);
    }

    /**
     * Count total records
     */
    public function count($where = '', $params = []) {
        return $this->db->count($this->table, $where, $params);
    }

    /**
     * Filter data based on fillable fields
     */
    protected function filterData($data) {
        if (empty($this->fillable)) {
            return $data;
        }

        return array_intersect_key($data, array_flip($this->fillable));
    }

    /**
     * Begin a database transaction
     */
    public function beginTransaction() {
        $this->db->beginTransaction();
    }

    /**
     * Commit a database transaction
     */
    public function commit() {
        $this->db->commit();
    }

    /**
     * Rollback a database transaction
     */
    public function rollback() {
        $this->db->rollback();
    }

    /**
     * Execute a custom query
     */
    protected function query($sql, $params = []) {
        return $this->db->fetchAll($sql, $params);
    }

    /**
     * Execute a custom query and get one result
     */
    protected function queryOne($sql, $params = []) {
        return $this->db->fetchOne($sql, $params);
    }

    /**
     * Get paginated results
     */
    public function paginate($page = 1, $perPage = 10, $orderBy = null) {
        $offset = ($page - 1) * $perPage;
        
        $sql = "SELECT * FROM {$this->table}";
        if ($orderBy) {
            $sql .= " ORDER BY $orderBy";
        }
        $sql .= " LIMIT $perPage OFFSET $offset";

        $items = $this->db->fetchAll($sql);
        $total = $this->count();

        return [
            'items' => $items,
            'total' => $total,
            'per_page' => $perPage,
            'current_page' => $page,
            'last_page' => ceil($total / $perPage)
        ];
    }

    /**
     * Search records
     */
    public function search($fields, $keyword) {
        $conditions = [];
        $params = [];
        
        foreach ($fields as $field) {
            $conditions[] = "$field LIKE ?";
            $params[] = "%$keyword%";
        }
        
        $where = implode(' OR ', $conditions);
        $sql = "SELECT * FROM {$this->table} WHERE $where";
        
        return $this->db->fetchAll($sql, $params);
    }

    /**
     * Get records with relationships
     */
    protected function with($relation, $foreignKey, $localKey = null) {
        if (!$localKey) {
            $localKey = $this->primaryKey;
        }

        $sql = "SELECT t1.*, t2.* 
                FROM {$this->table} t1 
                JOIN $relation t2 ON t1.$foreignKey = t2.$localKey";
        
        return $this->db->fetchAll($sql);
    }
}
