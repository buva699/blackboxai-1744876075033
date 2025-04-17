<?php
require_once __DIR__ . '/../includes/Model.php';

class Category extends Model {
    protected $table = 'categories';
    protected $fillable = [
        'name',
        'description'
    ];

    /**
     * Get categories with book counts
     */
    public function getCategoriesWithBookCount() {
        $sql = "SELECT c.*, COUNT(b.id) as book_count 
                FROM {$this->table} c 
                LEFT JOIN books b ON c.id = b.category_id 
                GROUP BY c.id 
                ORDER BY c.name";
        return $this->db->fetchAll($sql);
    }

    /**
     * Get category with its books
     */
    public function getCategoryWithBooks($categoryId) {
        $sql = "SELECT c.*, b.* 
                FROM {$this->table} c 
                LEFT JOIN books b ON c.id = b.category_id 
                WHERE c.id = ?
                ORDER BY b.title";
        return $this->db->fetchAll($sql, [$categoryId]);
    }

    /**
     * Get popular categories
     */
    public function getPopularCategories($limit = 5) {
        $limit = intval($limit);
        $sql = "SELECT c.*, 
                    COUNT(DISTINCT b.id) as book_count,
                    COUNT(DISTINCT br.id) as borrow_count
                FROM {$this->table} c
                LEFT JOIN books b ON c.id = b.category_id
                LEFT JOIN borrowings br ON b.id = br.book_id
                GROUP BY c.id
                ORDER BY borrow_count DESC
                LIMIT $limit";
        return $this->db->fetchAll($sql);
    }

    /**
     * Search categories
     */
    public function searchCategories($keyword) {
        return $this->search(['name', 'description'], $keyword);
    }

    /**
     * Check if category can be deleted
     */
    public function canDelete($categoryId) {
        $sql = "SELECT COUNT(*) as count FROM books WHERE category_id = ?";
        $result = $this->db->fetchOne($sql, [$categoryId]);
        return $result['count'] == 0;
    }

    /**
     * Get category statistics
     */
    public function getStatistics() {
        $sql = "SELECT 
                    COUNT(DISTINCT c.id) as total_categories,
                    COUNT(DISTINCT b.id) as total_books,
                    AVG(books_per_category.book_count) as avg_books_per_category
                FROM {$this->table} c
                LEFT JOIN books b ON c.id = b.category_id
                LEFT JOIN (
                    SELECT category_id, COUNT(*) as book_count
                    FROM books
                    GROUP BY category_id
                ) books_per_category ON c.id = books_per_category.category_id";
        return $this->db->fetchOne($sql);
    }

    /**
     * Get categories for dropdown
     */
    public function getCategoriesForDropdown() {
        $sql = "SELECT id, name FROM {$this->table} ORDER BY name";
        return $this->db->fetchAll($sql);
    }

    /**
     * Validate category data
     */
    public function validate($data) {
        $errors = [];

        if (empty($data['name'])) {
            $errors[] = "Category name is required";
        } elseif ($this->isCategoryNameExists($data['name'], isset($data['id']) ? $data['id'] : null)) {
            $errors[] = "Category name already exists";
        }

        return $errors;
    }

    /**
     * Check if category name exists
     */
    private function isCategoryNameExists($name, $excludeId = null) {
        $sql = "SELECT COUNT(*) as count FROM {$this->table} WHERE name = ?";
        $params = [$name];

        if ($excludeId) {
            $sql .= " AND id != ?";
            $params[] = $excludeId;
        }

        $result = $this->db->fetchOne($sql, $params);
        return $result['count'] > 0;
    }

    /**
     * Get category borrowing statistics
     */
    public function getCategoryBorrowingStats($categoryId) {
        $sql = "SELECT 
                    c.name as category_name,
                    COUNT(DISTINCT b.id) as total_books,
                    COUNT(DISTINCT br.id) as total_borrowings,
                    COUNT(DISTINCT br.member_id) as unique_borrowers
                FROM {$this->table} c
                LEFT JOIN books b ON c.id = b.category_id
                LEFT JOIN borrowings br ON b.id = br.book_id
                WHERE c.id = ?
                GROUP BY c.id";
        return $this->db->fetchOne($sql, [$categoryId]);
    }

    /**
     * Get monthly category statistics
     */
    public function getMonthlyStats($categoryId, $year = null) {
        if (!$year) {
            $year = date('Y');
        }
        $year = intval($year);
        
        $sql = "SELECT 
                    MONTH(br.borrow_date) as month,
                    COUNT(*) as borrow_count
                FROM {$this->table} c
                JOIN books b ON c.id = b.category_id
                JOIN borrowings br ON b.id = br.book_id
                WHERE c.id = ? AND YEAR(br.borrow_date) = $year
                GROUP BY MONTH(br.borrow_date)
                ORDER BY month";
        return $this->db->fetchAll($sql, [$categoryId]);
    }

    /**
     * Move books to another category
     */
    public function moveBooks($fromCategoryId, $toCategoryId) {
        $this->beginTransaction();
        
        try {
            $sql = "UPDATE books SET category_id = ? WHERE category_id = ?";
            $this->db->query($sql, [$toCategoryId, $fromCategoryId]);
            
            $this->delete($fromCategoryId);
            
            $this->commit();
            return true;
        } catch (Exception $e) {
            $this->rollback();
            throw $e;
        }
    }
}
