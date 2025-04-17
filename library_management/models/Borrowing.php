<?php
require_once __DIR__ . '/../includes/Model.php';

class Borrowing extends Model {
    protected $table = 'borrowings';
    protected $fillable = [
        'book_id',
        'member_id',
        'borrow_date',
        'due_date',
        'return_date',
        'fine_amount',
        'status'
    ];

    /**
     * Create a new borrowing
     */
    public function createBorrowing($bookId, $memberId) {
        $this->beginTransaction();

        try {
            // Check book availability
            $book = $this->db->fetchOne("SELECT available_copies FROM books WHERE id = ?", [$bookId]);
            if (!$book || $book['available_copies'] <= 0) {
                throw new Exception("Book is not available for borrowing");
            }

            // Check if member has overdue books
            $overdue = $this->db->fetchOne(
                "SELECT COUNT(*) as count FROM borrowings WHERE member_id = ? AND status = 'overdue'",
                [$memberId]
            );
            if ($overdue['count'] > 0) {
                throw new Exception("Cannot borrow books. Member has overdue books.");
            }

            // Create borrowing record
            $borrowData = [
                'book_id' => $bookId,
                'member_id' => $memberId,
                'borrow_date' => date('Y-m-d'),
                'due_date' => date('Y-m-d', strtotime('+14 days')),
                'status' => 'borrowed'
            ];
            
            $borrowingId = $this->create($borrowData);

            // Update book availability
            $this->db->query(
                "UPDATE books SET available_copies = available_copies - 1 WHERE id = ?",
                [$bookId]
            );

            $this->commit();
            return $borrowingId;
        } catch (Exception $e) {
            $this->rollback();
            throw $e;
        }
    }

    /**
     * Return a book
     */
    public function returnBook($borrowingId) {
        $this->beginTransaction();

        try {
            // Get borrowing details
            $borrowing = $this->find($borrowingId);
            if (!$borrowing) {
                throw new Exception("Borrowing record not found");
            }

            // Calculate fine if overdue
            $fine = 0;
            if (strtotime($borrowing['due_date']) < time()) {
                $daysOverdue = floor((time() - strtotime($borrowing['due_date'])) / (60 * 60 * 24));
                $fineRate = $this->getFineRate();
                $fine = $daysOverdue * $fineRate;
            }

            // Update borrowing record
            $this->update($borrowingId, [
                'return_date' => date('Y-m-d'),
                'fine_amount' => $fine,
                'status' => 'returned'
            ]);

            // Update book availability
            $this->db->query(
                "UPDATE books SET available_copies = available_copies + 1 WHERE id = ?",
                [$borrowing['book_id']]
            );

            $this->commit();
            return $fine;
        } catch (Exception $e) {
            $this->rollback();
            throw $e;
        }
    }

    /**
     * Get current borrowings with details
     */
    public function getCurrentBorrowings() {
        $sql = "SELECT b.*, 
                    bk.title as book_title, 
                    m.full_name as member_name,
                    m.member_id as member_number
                FROM {$this->table} b
                JOIN books bk ON b.book_id = bk.id
                JOIN members m ON b.member_id = m.id
                WHERE b.status != 'returned'
                ORDER BY b.due_date ASC";
        return $this->db->fetchAll($sql);
    }

    /**
     * Get overdue borrowings
     */
    public function getOverdueBorrowings() {
        $sql = "SELECT b.*, 
                    bk.title as book_title, 
                    m.full_name as member_name,
                    m.member_id as member_number,
                    DATEDIFF(CURRENT_DATE, b.due_date) as days_overdue
                FROM {$this->table} b
                JOIN books bk ON b.book_id = bk.id
                JOIN members m ON b.member_id = m.id
                WHERE b.status = 'overdue'
                ORDER BY b.due_date ASC";
        return $this->db->fetchAll($sql);
    }

    /**
     * Update overdue status
     */
    public function updateOverdueStatus() {
        $sql = "UPDATE {$this->table} 
                SET status = 'overdue' 
                WHERE status = 'borrowed' 
                AND due_date < CURRENT_DATE";
        return $this->db->query($sql);
    }

    /**
     * Get borrowing statistics
     */
    public function getStatistics() {
        $sql = "SELECT 
                    COUNT(*) as total_borrowings,
                    SUM(CASE WHEN status = 'borrowed' THEN 1 ELSE 0 END) as current_borrowings,
                    SUM(CASE WHEN status = 'overdue' THEN 1 ELSE 0 END) as overdue_borrowings,
                    SUM(fine_amount) as total_fines
                FROM {$this->table}";
        return $this->db->fetchOne($sql);
    }

    /**
     * Get monthly borrowing statistics
     */
    public function getMonthlyStats($year = null) {
        if (!$year) {
            $year = date('Y');
        }
        $year = intval($year);
        
        $sql = "SELECT 
                    MONTH(borrow_date) as month,
                    COUNT(*) as total_borrowings,
                    COUNT(DISTINCT member_id) as unique_members
                FROM {$this->table}
                WHERE YEAR(borrow_date) = $year
                GROUP BY MONTH(borrow_date)
                ORDER BY month";
        return $this->db->fetchAll($sql);
    }

    /**
     * Get fine rate from settings
     */
    private function getFineRate() {
        $result = $this->db->fetchOne("SELECT fine_per_day FROM fine_settings LIMIT 1");
        return $result ? $result['fine_per_day'] : 1.00;
    }

    /**
     * Validate borrowing
     */
    public function validate($data) {
        $errors = [];

        if (empty($data['book_id'])) {
            $errors[] = "Book is required";
        }

        if (empty($data['member_id'])) {
            $errors[] = "Member is required";
        }

        // Additional validation can be added here

        return $errors;
    }

    /**
     * Get borrowing details with related information
     */
    public function getBorrowingDetails($borrowingId) {
        $sql = "SELECT b.*, 
                    bk.title as book_title, 
                    bk.author as book_author,
                    m.full_name as member_name,
                    m.member_id as member_number,
                    m.email as member_email
                FROM {$this->table} b
                JOIN books bk ON b.book_id = bk.id
                JOIN members m ON b.member_id = m.id
                WHERE b.id = ?";
        return $this->db->fetchOne($sql, [$borrowingId]);
    }

    /**
     * Get recent borrowings with book and member details
     */
    public function getRecentBorrowings($limit = 5) {
        $sql = "SELECT b.*, 
                    m.full_name as member_name, 
                    bk.title as book_title 
                FROM {$this->table} b 
                JOIN members m ON b.member_id = m.id 
                JOIN books bk ON b.book_id = bk.id 
                ORDER BY b.created_at DESC 
                LIMIT ?";
        return $this->db->fetchAll($sql, [$limit]);
    }
}
