<?php
require_once __DIR__ . '/../includes/Model.php';

class Admin extends Model {
    protected $table = 'admin';
    protected $fillable = [
        'username',
        'password',
        'full_name',
        'email'
    ];

    /**
     * Authenticate admin
     */
    public function authenticate($username, $password) {
        $sql = "SELECT * FROM {$this->table} WHERE username = ?";
        $admin = $this->db->fetchOne($sql, [$username]);

        if ($admin && password_verify($password, $admin['password'])) {
            unset($admin['password']); // Remove password from session data
            return $admin;
        }

        return false;
    }

    /**
     * Create new admin
     */
    public function createAdmin($data) {
        if (isset($data['password'])) {
            $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
        }
        return $this->create($data);
    }

    /**
     * Update admin
     */
    public function updateAdmin($id, $data) {
        // Hash password if it's being updated
        if (isset($data['password']) && !empty($data['password'])) {
            $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
        } else {
            unset($data['password']); // Don't update password if not provided
        }
        return $this->update($id, $data);
    }

    /**
     * Change password
     */
    public function changePassword($adminId, $currentPassword, $newPassword) {
        // Verify current password
        $admin = $this->find($adminId);
        if (!$admin || !password_verify($currentPassword, $admin['password'])) {
            return false;
        }

        // Update to new password
        return $this->update($adminId, [
            'password' => password_hash($newPassword, PASSWORD_DEFAULT)
        ]);
    }

    /**
     * Reset password
     */
    public function resetPassword($email) {
        $admin = $this->findOneBy('email', $email);
        if (!$admin) {
            return false;
        }

        // Generate temporary password
        $tempPassword = bin2hex(random_bytes(8));
        $hashedPassword = password_hash($tempPassword, PASSWORD_DEFAULT);

        // Update password
        $updated = $this->update($admin['id'], ['password' => $hashedPassword]);

        if ($updated) {
            // Send email with temporary password
            $subject = "Password Reset - SDCKL Library Management System";
            $message = "Your temporary password is: " . $tempPassword . "\n";
            $message .= "Please change your password after logging in.";
            
            return mail($email, $subject, $message);
        }

        return false;
    }

    /**
     * Validate admin data
     */
    public function validate($data, $isNew = true) {
        $errors = [];

        if (empty($data['username'])) {
            $errors[] = "Username is required";
        } elseif ($this->isUsernameExists($data['username'], isset($data['id']) ? $data['id'] : null)) {
            $errors[] = "Username already exists";
        }

        if (empty($data['email'])) {
            $errors[] = "Email is required";
        } elseif (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $errors[] = "Invalid email format";
        } elseif ($this->isEmailExists($data['email'], isset($data['id']) ? $data['id'] : null)) {
            $errors[] = "Email already exists";
        }

        if ($isNew && empty($data['password'])) {
            $errors[] = "Password is required";
        }

        if (!empty($data['password']) && strlen($data['password']) < 8) {
            $errors[] = "Password must be at least 8 characters long";
        }

        if (empty($data['full_name'])) {
            $errors[] = "Full name is required";
        }

        return $errors;
    }

    /**
     * Check if username exists
     */
    private function isUsernameExists($username, $excludeId = null) {
        $sql = "SELECT COUNT(*) as count FROM {$this->table} WHERE username = ?";
        $params = [$username];

        if ($excludeId) {
            $sql .= " AND id != ?";
            $params[] = $excludeId;
        }

        $result = $this->db->fetchOne($sql, $params);
        return $result['count'] > 0;
    }

    /**
     * Check if email exists
     */
    private function isEmailExists($email, $excludeId = null) {
        $sql = "SELECT COUNT(*) as count FROM {$this->table} WHERE email = ?";
        $params = [$email];

        if ($excludeId) {
            $sql .= " AND id != ?";
            $params[] = $excludeId;
        }

        $result = $this->db->fetchOne($sql, $params);
        return $result['count'] > 0;
    }

    /**
     * Get admin activity log
     */
    public function getActivityLog($adminId, $limit = 50) {
        $sql = "SELECT * FROM activity_log 
                WHERE user_id = ? 
                ORDER BY created_at DESC 
                LIMIT " . intval($limit);
        return $this->db->fetchAll($sql, [$adminId]);
    }

    /**
     * Log admin activity
     */
    public function logActivity($adminId, $action, $details = '') {
        $data = [
            'user_id' => $adminId,
            'action' => $action,
            'details' => $details,
            'created_at' => date('Y-m-d H:i:s')
        ];

        return $this->db->insert('activity_log', $data);
    }

    /**
     * Get last login time
     */
    public function getLastLogin($adminId) {
        $sql = "SELECT created_at 
                FROM activity_log 
                WHERE user_id = ? AND action = 'login' 
                ORDER BY created_at DESC 
                LIMIT 1";
        $result = $this->db->fetchOne($sql, [$adminId]);
        return $result ? $result['created_at'] : null;
    }
}
