<?php
class Database {
    private static $instance = null;
    private $conn;

    private function __construct() {
        require_once __DIR__ . '/../database/config.php';
        
        try {
            $this->conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
            
            if ($this->conn->connect_error) {
                throw new Exception("Connection failed: " . $this->conn->connect_error);
            }
            
            $this->conn->set_charset("utf8mb4");
        } catch (Exception $e) {
            error_log("Database Connection Error: " . $e->getMessage());
            die("Database connection failed. Please try again later.");
        }
    }

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function getConnection() {
        return $this->conn;
    }

    public function query($sql) {
        try {
            $result = $this->conn->query($sql);
            if ($result === false) {
                throw new Exception("Query failed: " . $this->conn->error);
            }
            return $result;
        } catch (Exception $e) {
            error_log("Database Query Error: " . $e->getMessage());
            throw $e;
        }
    }

    public function prepare($sql) {
        try {
            $stmt = $this->conn->prepare($sql);
            if ($stmt === false) {
                throw new Exception("Prepare failed: " . $this->conn->error);
            }
            return $stmt;
        } catch (Exception $e) {
            error_log("Database Prepare Error: " . $e->getMessage());
            throw $e;
        }
    }

    public function beginTransaction() {
        try {
            $this->conn->begin_transaction();
        } catch (Exception $e) {
            error_log("Transaction Begin Error: " . $e->getMessage());
            throw $e;
        }
    }

    public function commit() {
        try {
            $this->conn->commit();
        } catch (Exception $e) {
            error_log("Transaction Commit Error: " . $e->getMessage());
            throw $e;
        }
    }

    public function rollback() {
        try {
            $this->conn->rollback();
        } catch (Exception $e) {
            error_log("Transaction Rollback Error: " . $e->getMessage());
            throw $e;
        }
    }

    public function escape($value) {
        return $this->conn->real_escape_string($value);
    }

    public function lastInsertId() {
        return $this->conn->insert_id;
    }

    public function affectedRows() {
        return $this->conn->affected_rows;
    }

    public function close() {
        if ($this->conn) {
            $this->conn->close();
        }
    }

    // Prevent cloning of the instance
    private function __clone() {}

    // Prevent unserializing of the instance
    public function __wakeup() {
        throw new Exception("Cannot unserialize singleton");
    }

    // Helper methods for common operations

    public function fetchAll($sql, $params = []) {
        try {
            $stmt = $this->prepare($sql);
            if (!empty($params)) {
                $types = str_repeat('s', count($params));
                $stmt->bind_param($types, ...$params);
            }
            $stmt->execute();
            $result = $stmt->get_result();
            return $result->fetch_all(MYSQLI_ASSOC);
        } catch (Exception $e) {
            error_log("FetchAll Error: " . $e->getMessage());
            throw $e;
        }
    }

    public function fetchOne($sql, $params = []) {
        try {
            $stmt = $this->prepare($sql);
            if (!empty($params)) {
                $types = str_repeat('s', count($params));
                $stmt->bind_param($types, ...$params);
            }
            $stmt->execute();
            $result = $stmt->get_result();
            return $result->fetch_assoc();
        } catch (Exception $e) {
            error_log("FetchOne Error: " . $e->getMessage());
            throw $e;
        }
    }

    public function insert($table, $data) {
        try {
            $columns = implode(', ', array_keys($data));
            $values = implode(', ', array_fill(0, count($data), '?'));
            $sql = "INSERT INTO $table ($columns) VALUES ($values)";
            
            $stmt = $this->prepare($sql);
            $types = str_repeat('s', count($data));
            $stmt->bind_param($types, ...array_values($data));
            
            $stmt->execute();
            return $this->lastInsertId();
        } catch (Exception $e) {
            error_log("Insert Error: " . $e->getMessage());
            throw $e;
        }
    }

    public function update($table, $data, $where, $whereParams = []) {
        try {
            $set = implode(' = ?, ', array_keys($data)) . ' = ?';
            $sql = "UPDATE $table SET $set WHERE $where";
            
            $stmt = $this->prepare($sql);
            $params = array_merge(array_values($data), $whereParams);
            $types = str_repeat('s', count($params));
            $stmt->bind_param($types, ...$params);
            
            $stmt->execute();
            return $this->affectedRows();
        } catch (Exception $e) {
            error_log("Update Error: " . $e->getMessage());
            throw $e;
        }
    }

    public function delete($table, $where, $params = []) {
        try {
            $sql = "DELETE FROM $table WHERE $where";
            
            $stmt = $this->prepare($sql);
            if (!empty($params)) {
                $types = str_repeat('s', count($params));
                $stmt->bind_param($types, ...$params);
            }
            
            $stmt->execute();
            return $this->affectedRows();
        } catch (Exception $e) {
            error_log("Delete Error: " . $e->getMessage());
            throw $e;
        }
    }

    public function count($table, $where = '', $params = []) {
        try {
            $sql = "SELECT COUNT(*) as count FROM $table";
            if ($where) {
                $sql .= " WHERE $where";
            }
            
            $stmt = $this->prepare($sql);
            if (!empty($params)) {
                $types = str_repeat('s', count($params));
                $stmt->bind_param($types, ...$params);
            }
            
            $stmt->execute();
            $result = $stmt->get_result();
            return $result->fetch_assoc()['count'];
        } catch (Exception $e) {
            error_log("Count Error: " . $e->getMessage());
            throw $e;
        }
    }
}
