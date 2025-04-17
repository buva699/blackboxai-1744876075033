<?php
require_once __DIR__ . '/../includes/Model.php';

class Member extends Model {
    protected $table = 'members';
    protected $fillable = [
        'member_id',
        'full_name',
        'email',
        'phone',
        'member_type',
        'department',
        'status'
    ];

    /**
     * Get active members
     */
    public function getActiveMembers() {
        return $this->findBy('status', 'active');
    }

    /**
     * Get member with their borrowing statistics
     */
    public function getMembersWithStats() {
        $sql = "SELECT m.*, 
                    COUNT(DISTINCT b.id) as total_borrowed,
                    SUM(CASE WHEN b.status = 'borrowed' THEN 1 ELSE 0 END) as currently_borrowed,
                    SUM(CASE WHEN b.status = 'overdue' THEN 1 ELSE 0 END) as overdue_books
                FROM {$this->table} m
                LEFT JOIN borrowings b ON m.id = b.member_id
                GROUP BY m.id
                ORDER BY m.full_name";
        return $this->db->fetchAll($sql);
    }

    /**
     * Get member's current borrowings
     */
    public function getCurrentBorrowings($memberId) {
        $sql = "SELECT b.*, bk.title as book_title, bk.author 
                FROM borrowings b 
                JOIN books bk ON b.book_id = bk.id 
                WHERE b.member_id = ? AND b.status IN ('borrowed', 'overdue')
                ORDER BY b.due_date ASC";
        return $this->db->fetchAll($sql, [$memberId]);
    }

    /**
     * Get member's borrowing history
     */
    public function getBorrowingHistory($memberId) {
        $sql = "SELECT b.*, bk.title as book_title, bk.author 
                FROM borrowings b 
                JOIN books bk ON b.book_id = bk.id 
                WHERE b.member_id = ?
                ORDER BY b.borrow_date DESC";
        return $this->db->fetchAll($sql, [$memberId]);
    }

    /**
     * Check if member has overdue books
     */
    public function hasOverdueBooks($memberId) {
        $sql = "SELECT COUNT(*) as count 
                FROM borrowings 
                WHERE member_id = ? AND status = 'overdue'";
        $result = $this->db->fetchOne($sql, [$memberId]);
        return $result['count'] > 0;
    }

    /**
     * Get member's total fines
     */
    public function getTotalFines($memberId) {
        $sql = "SELECT SUM(fine_amount) as total_fines 
                FROM borrowings 
                WHERE member_id = ?";
        $result = $this->db->fetchOne($sql, [$memberId]);
        return $result['total_fines'] ?? 0;
    }

    /**
     * Get most active members
     */
    public function getMostActiveMembers($limit = 5) {
        $limit = intval($limit);
        $sql = "SELECT m.*, COUNT(b.id) as borrow_count 
                FROM {$this->table} m 
                LEFT JOIN borrowings b ON m.id = b.member_id 
                GROUP BY m.id 
                ORDER BY borrow_count DESC 
                LIMIT $limit";
        return $this->db->fetchAll($sql);
    }

    /**
     * Search members
     */
    public function searchMembers($keyword) {
        return $this->search(
            ['member_id', 'full_name', 'email', 'phone', 'department'],
            $keyword
        );
    }

    /**
     * Generate unique member ID
     */
    public function generateMemberId($memberType) {
        $prefix = strtoupper(substr($memberType, 0, 3));
        $sql = "SELECT MAX(CAST(SUBSTRING(member_id, 4) AS UNSIGNED)) as max_id 
                FROM {$this->table} 
                WHERE member_type = ?";
        $result = $this->db->fetchOne($sql, [$memberType]);
        $nextId = ($result['max_id'] ?? 0) + 1;
        return $prefix . str_pad($nextId, 3, '0', STR_PAD_LEFT);
    }

    /**
     * Update member status
     */
    public function updateStatus($memberId, $status) {
        return $this->update($memberId, ['status' => $status]);
    }

    /**
     * Get member statistics
     */
    public function getStatistics() {
        $sql = "SELECT 
                    COUNT(*) as total_members,
                    SUM(CASE WHEN status = 'active' THEN 1 ELSE 0 END) as active_members,
                    SUM(CASE WHEN member_type = 'student' THEN 1 ELSE 0 END) as total_students,
                    SUM(CASE WHEN member_type = 'faculty' THEN 1 ELSE 0 END) as total_faculty,
                    COUNT(DISTINCT department) as total_departments
                FROM {$this->table}";
        return $this->db->fetchOne($sql);
    }

    /**
     * Validate member data
     */
    public function validate($data) {
        $errors = [];

        if (empty($data['full_name'])) {
            $errors[] = "Full name is required";
        }

        if (empty($data['email'])) {
            $errors[] = "Email is required";
        } elseif (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $errors[] = "Invalid email format";
        } elseif ($this->isEmailExists($data['email'], isset($data['id']) ? $data['id'] : null)) {
            $errors[] = "Email already exists";
        }

        if (empty($data['phone'])) {
            $errors[] = "Phone number is required";
        }

        if (empty($data['member_type']) || !in_array($data['member_type'], ['student', 'faculty'])) {
            $errors[] = "Invalid member type";
        }

        if (empty($data['department'])) {
            $errors[] = "Department is required";
        }

        return $errors;
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
     * Get members by department
     */
    public function getMembersByDepartment() {
        $sql = "SELECT department, 
                    COUNT(*) as total_members,
                    SUM(CASE WHEN status = 'active' THEN 1 ELSE 0 END) as active_members
                FROM {$this->table}
                GROUP BY department
                ORDER BY department";
        return $this->db->fetchAll($sql);
    }
}
