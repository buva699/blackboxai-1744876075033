<?php
// Authentication functions
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function requireLogin() {
    if (!isLoggedIn()) {
        header("Location: index.php");
        exit();
    }
}

function logout() {
    session_start();
    session_destroy();
    header("Location: index.php");
    exit();
}

// Date and time functions
function formatDate($date) {
    return date('Y-m-d', strtotime($date));
}

function formatDateTime($datetime) {
    return date('Y-m-d H:i:s', strtotime($datetime));
}

function calculateDaysDifference($date1, $date2) {
    $diff = strtotime($date2) - strtotime($date1);
    return floor($diff / (60 * 60 * 24));
}

// Validation functions
function validateEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

function validateISBN($isbn) {
    return preg_match('/^(?=(?:\D*\d){10}(?:(?:\D*\d){3})?$)[\d-]+$/', $isbn);
}

function validatePhone($phone) {
    return preg_match('/^[0-9]{10,15}$/', $phone);
}

// Formatting functions
function formatMoney($amount) {
    return number_format($amount, 2);
}

function formatStatus($status) {
    $statusClasses = [
        'active' => 'bg-green-100 text-green-800',
        'inactive' => 'bg-red-100 text-red-800',
        'borrowed' => 'bg-blue-100 text-blue-800',
        'returned' => 'bg-gray-100 text-gray-800',
        'overdue' => 'bg-yellow-100 text-yellow-800'
    ];
    
    $class = $statusClasses[$status] ?? 'bg-gray-100 text-gray-800';
    return sprintf('<span class="px-2 py-1 rounded-full text-sm %s">%s</span>', 
        $class, 
        ucfirst($status)
    );
}

// Flash messages
function setFlashMessage($type, $message) {
    $_SESSION['flash'] = [
        'type' => $type,
        'message' => $message
    ];
}

function getFlashMessage() {
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}

// Pagination helper
function getPaginationLinks($currentPage, $totalPages, $urlPattern) {
    $links = [];
    
    if ($currentPage > 1) {
        $links[] = [
            'page' => $currentPage - 1,
            'url' => sprintf($urlPattern, $currentPage - 1),
            'label' => 'Previous'
        ];
    }
    
    for ($i = max(1, $currentPage - 2); $i <= min($totalPages, $currentPage + 2); $i++) {
        $links[] = [
            'page' => $i,
            'url' => sprintf($urlPattern, $i),
            'label' => $i,
            'current' => $i === $currentPage
        ];
    }
    
    if ($currentPage < $totalPages) {
        $links[] = [
            'page' => $currentPage + 1,
            'url' => sprintf($urlPattern, $currentPage + 1),
            'label' => 'Next'
        ];
    }
    
    return $links;
}

// File upload helper
function handleFileUpload($file, $allowedTypes = ['image/jpeg', 'image/png'], $maxSize = 5242880) {
    $errors = [];
    
    if ($file['error'] !== UPLOAD_ERR_OK) {
        $errors[] = 'File upload failed';
        return ['success' => false, 'errors' => $errors];
    }
    
    if (!in_array($file['type'], $allowedTypes)) {
        $errors[] = 'Invalid file type';
    }
    
    if ($file['size'] > $maxSize) {
        $errors[] = 'File size too large';
    }
    
    if (!empty($errors)) {
        return ['success' => false, 'errors' => $errors];
    }
    
    $fileName = uniqid() . '_' . basename($file['name']);
    $uploadPath = __DIR__ . '/../uploads/' . $fileName;
    
    if (!move_uploaded_file($file['tmp_name'], $uploadPath)) {
        $errors[] = 'Failed to move uploaded file';
        return ['success' => false, 'errors' => $errors];
    }
    
    return [
        'success' => true,
        'fileName' => $fileName,
        'path' => $uploadPath
    ];
}

// Search helper
function buildSearchQuery($table, $fields, $keyword) {
    $conditions = [];
    $params = [];
    
    foreach ($fields as $field) {
        $conditions[] = "$field LIKE ?";
        $params[] = "%$keyword%";
    }
    
    $sql = "SELECT * FROM $table WHERE " . implode(' OR ', $conditions);
    return ['sql' => $sql, 'params' => $params];
}

// Generate member ID
function generateMemberId($prefix, $currentCount) {
    return sprintf('%s%04d', $prefix, $currentCount + 1);
}

// Calculate fine amount
function calculateFine($dueDate, $returnDate = null, $finePerDay = 1.00, $gracePeriod = 0) {
    if (!$returnDate) {
        $returnDate = date('Y-m-d');
    }
    
    $daysLate = calculateDaysDifference($dueDate, $returnDate) - $gracePeriod;
    return $daysLate > 0 ? $daysLate * $finePerDay : 0;
}

// Security functions
function generateCSRFToken() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function validateCSRFToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

// API Response helper
function jsonResponse($data, $status = 200) {
    header('Content-Type: application/json');
    http_response_code($status);
    echo json_encode($data);
    exit();
}
