<?php
/**
 * Common utility functions for the Library Management System
 */

/**
 * Sanitize user input
 * @param string $input The input to sanitize
 * @return string Sanitized input
 */
function sanitize_input($input) {
    global $conn;
    return mysqli_real_escape_string($conn, trim($input));
}

/**
 * Format date to readable format
 * @param string $date Date string
 * @return string Formatted date
 */
function format_date($date) {
    return date('M d, Y', strtotime($date));
}

/**
 * Calculate fine for overdue books
 * @param string $due_date Due date
 * @return float Fine amount
 */
function calculate_fine($due_date) {
    $today = new DateTime();
    $due = new DateTime($due_date);
    if ($today > $due) {
        $diff = $today->diff($due);
        $days = $diff->days;
        // Get fine rate from settings
        global $conn;
        $result = $conn->query("SELECT fine_per_day, grace_period_days FROM fine_settings LIMIT 1");
        $settings = $result->fetch_assoc();
        $days = max(0, $days - $settings['grace_period_days']);
        return $days * $settings['fine_per_day'];
    }
    return 0;
}

/**
 * Check if a book is available for borrowing
 * @param int $book_id Book ID
 * @return bool True if available, false otherwise
 */
function is_book_available($book_id) {
    global $conn;
    $stmt = $conn->prepare("SELECT available_copies FROM books WHERE id = ?");
    $stmt->bind_param("i", $book_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $book = $result->fetch_assoc();
    return $book['available_copies'] > 0;
}

/**
 * Check if a member has overdue books
 * @param int $member_id Member ID
 * @return bool True if has overdue books, false otherwise
 */
function has_overdue_books($member_id) {
    global $conn;
    $stmt = $conn->prepare("SELECT COUNT(*) as count FROM borrowings WHERE member_id = ? AND status = 'overdue'");
    $stmt->bind_param("i", $member_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $count = $result->fetch_assoc();
    return $count['count'] > 0;
}

/**
 * Get member borrowing statistics
 * @param int $member_id Member ID
 * @return array Statistics array
 */
function get_member_stats($member_id) {
    global $conn;
    $stats = [
        'total_borrowed' => 0,
        'currently_borrowed' => 0,
        'overdue' => 0,
        'total_fines' => 0
    ];
    
    $stmt = $conn->prepare("
        SELECT 
            COUNT(*) as total_borrowed,
            SUM(CASE WHEN status = 'borrowed' THEN 1 ELSE 0 END) as currently_borrowed,
            SUM(CASE WHEN status = 'overdue' THEN 1 ELSE 0 END) as overdue,
            SUM(fine_amount) as total_fines
        FROM borrowings 
        WHERE member_id = ?");
    $stmt->bind_param("i", $member_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $data = $result->fetch_assoc();
    
    $stats['total_borrowed'] = $data['total_borrowed'];
    $stats['currently_borrowed'] = $data['currently_borrowed'];
    $stats['overdue'] = $data['overdue'];
    $stats['total_fines'] = $data['total_fines'];
    
    return $stats;
}

/**
 * Get book borrowing statistics
 * @param int $book_id Book ID
 * @return array Statistics array
 */
function get_book_stats($book_id) {
    global $conn;
    $stats = [
        'total_borrowed' => 0,
        'currently_borrowed' => 0,
        'available_copies' => 0
    ];
    
    $stmt = $conn->prepare("
        SELECT 
            (SELECT COUNT(*) FROM borrowings WHERE book_id = ?) as total_borrowed,
            (SELECT COUNT(*) FROM borrowings WHERE book_id = ? AND status = 'borrowed') as currently_borrowed,
            available_copies
        FROM books 
        WHERE id = ?");
    $stmt->bind_param("iii", $book_id, $book_id, $book_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $data = $result->fetch_assoc();
    
    $stats['total_borrowed'] = $data['total_borrowed'];
    $stats['currently_borrowed'] = $data['currently_borrowed'];
    $stats['available_copies'] = $data['available_copies'];
    
    return $stats;
}

/**
 * Generate a unique member ID
 * @param string $type Member type (student/faculty)
 * @return string Unique member ID
 */
function generate_member_id($type) {
    global $conn;
    $prefix = strtoupper(substr($type, 0, 3));
    $result = $conn->query("SELECT MAX(CAST(SUBSTRING(member_id, 4) AS UNSIGNED)) as max_id FROM members WHERE member_type = '$type'");
    $data = $result->fetch_assoc();
    $next_id = ($data['max_id'] ?? 0) + 1;
    return $prefix . str_pad($next_id, 3, '0', STR_PAD_LEFT);
}

/**
 * Log system activity
 * @param string $action Action performed
 * @param string $details Action details
 * @param int $user_id User ID who performed the action
 */
function log_activity($action, $details, $user_id) {
    global $conn;
    $stmt = $conn->prepare("INSERT INTO activity_log (action, details, user_id) VALUES (?, ?, ?)");
    $stmt->bind_param("ssi", $action, $details, $user_id);
    $stmt->execute();
}

/**
 * Format currency amount
 * @param float $amount Amount to format
 * @return string Formatted amount
 */
function format_currency($amount) {
    return '$' . number_format($amount, 2);
}

/**
 * Check if user has permission for an action
 * @param string $action Action to check
 * @return bool True if permitted, false otherwise
 */
function has_permission($action) {
    // For now, all logged-in users have all permissions
    return isset($_SESSION['user_id']);
}

/**
 * Send email notification
 * @param string $to Recipient email
 * @param string $subject Email subject
 * @param string $message Email message
 * @return bool True if sent successfully, false otherwise
 */
function send_notification($to, $subject, $message) {
    // Email headers
    $headers = "MIME-Version: 1.0" . "\r\n";
    $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
    $headers .= 'From: SDCKL Library <library@sdckl.edu>' . "\r\n";
    
    // Send email
    return mail($to, $subject, $message, $headers);
}
