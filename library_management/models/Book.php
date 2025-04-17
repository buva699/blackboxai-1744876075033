<?php
require_once __DIR__ . '/../includes/Model.php';

class Book extends Model {
    protected $table = 'books';
    protected $fillable = [
        'title',
        'author',
        'isbn',
        'category_id',
        'publisher',
        'publication_year',
        'copies',
        'available_copies',
        'shelf_location',
        'description'
    ];

    /**
     * Get available books
     */
    public function getAvailableBooks() {
        $sql = "SELECT * FROM {$this->table} WHERE available_copies > 0";
        return $this->db->fetchAll($sql);
    }

    /**
     * Get books with their categories
     */
    public function getBooksWithCategories() {
        $sql = "SELECT b.*, c.name as category_name 
                FROM {$this->table} b 
                LEFT JOIN categories c ON b.category_id = c.id 
                ORDER BY b.title";
        return $this->db->fetchAll($sql);
    }

    /**
     * Search books
     */
    public function searchBooks($keyword) {
        return $this->search(['title', 'author', 'isbn', 'publisher'], $keyword);
    }

    /**
     * Get popular books
     */
    public function getPopularBooks($limit = 5) {
        $limit = intval($limit);
        $sql = "SELECT b.*, COUNT(br.id) as borrow_count 
                FROM {$this->table} b 
                LEFT JOIN borrowings br ON b.id = br.book_id 
                GROUP BY b.id 
                ORDER BY borrow_count DESC 
                LIMIT $limit";
        return $this->db->fetchAll($sql);
    }

    /**
     * Get books by category
     */
    public function getBooksByCategory($categoryId) {
        return $this->findBy('category_id', $categoryId);
    }

    /**
     * Check if book is available
     */
    public function isAvailable($bookId) {
        $book = $this->find($bookId);
        return $book && $book['available_copies'] > 0;
    }

    /**
     * Update book availability
     */
    public function updateAvailability($bookId, $increment = true) {
        $operator = $increment ? '+' : '-';
        $condition = $increment ? '>= 0' : '> 0';
        $sql = "UPDATE {$this->table} 
                SET available_copies = available_copies {$operator} 1 
                WHERE id = ? AND available_copies $condition";
        return $this->db->query($sql, [$bookId]);
    }

    /**
     * Get book borrowing history
     */
    public function getBorrowingHistory($bookId) {
        $sql = "SELECT br.*, m.full_name as member_name 
                FROM borrowings br 
                JOIN members m ON br.member_id = m.id 
                WHERE br.book_id = ? 
                ORDER BY br.borrow_date DESC";
        return $this->db->fetchAll($sql, [$bookId]);
    }

    /**
     * Get books needing restock (low on copies)
     */
    public function getBooksNeedingRestock($threshold = 2) {
        $threshold = intval($threshold);
        $sql = "SELECT * FROM {$this->table} 
                WHERE available_copies <= $threshold 
                ORDER BY available_copies ASC";
        return $this->db->fetchAll($sql);
    }

    /**
     * Get total books count
     */
    public function getTotalBooks() {
        return $this->count();
    }

    /**
     * Get total available books count
     */
    public function getTotalAvailableBooks() {
        return $this->count('available_copies > 0');
    }

    /**
     * Get books statistics
     */
    public function getStatistics() {
        $sql = "SELECT 
                    COUNT(*) as total_books,
                    SUM(copies) as total_copies,
                    SUM(available_copies) as available_copies,
                    COUNT(DISTINCT category_id) as total_categories,
                    COUNT(DISTINCT author) as total_authors
                FROM {$this->table}";
        return $this->db->fetchOne($sql);
    }

    /**
     * Validate book data
     */
    public function validate($data) {
        $errors = [];

        if (empty($data['title'])) {
            $errors[] = "Title is required";
        }

        if (empty($data['author'])) {
            $errors[] = "Author is required";
        }

        if (empty($data['isbn'])) {
            $errors[] = "ISBN is required";
        } elseif ($this->isIsbnExists($data['isbn'], isset($data['id']) ? $data['id'] : null)) {
            $errors[] = "ISBN already exists";
        }

        if (!isset($data['copies']) || $data['copies'] < 1) {
            $errors[] = "Number of copies must be at least 1";
        }

        return $errors;
    }

    /**
     * Check if ISBN exists
     */
    private function isIsbnExists($isbn, $excludeId = null) {
        $sql = "SELECT COUNT(*) as count FROM {$this->table} WHERE isbn = ?";
        $params = [$isbn];

        if ($excludeId) {
            $sql .= " AND id != ?";
            $params[] = $excludeId;
        }

        $result = $this->db->fetchOne($sql, $params);
        return $result['count'] > 0;
    }
}
