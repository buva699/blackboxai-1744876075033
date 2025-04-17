<?php
require_once 'database/config.php';
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

// Fetch dashboard statistics
$stats = [
    'total_books' => 0,
    'total_members' => 0,
    'books_borrowed' => 0,
    'books_overdue' => 0
];

// Get total books
$result = $conn->query("SELECT COUNT(*) as count FROM books");
$stats['total_books'] = $result->fetch_assoc()['count'];

// Get total members
$result = $conn->query("SELECT COUNT(*) as count FROM members WHERE status = 'active'");
$stats['total_members'] = $result->fetch_assoc()['count'];

// Get borrowed books
$result = $conn->query("SELECT COUNT(*) as count FROM borrowings WHERE status = 'borrowed'");
$stats['books_borrowed'] = $result->fetch_assoc()['count'];

// Get overdue books
$result = $conn->query("SELECT COUNT(*) as count FROM borrowings WHERE status = 'overdue'");
$stats['books_overdue'] = $result->fetch_assoc()['count'];

// Get recent borrowings
$recent_borrowings = $conn->query("
    SELECT b.*, m.full_name as member_name, bk.title as book_title 
    FROM borrowings b 
    JOIN members m ON b.member_id = m.id 
    JOIN books bk ON b.book_id = bk.id 
    ORDER BY b.created_at DESC 
    LIMIT 5
");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - SDCKL Library Management System</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
        }
    </style>
</head>
<body class="bg-gray-100">
    <div class="min-h-screen flex">
        <!-- Sidebar -->
        <div class="bg-gray-800 text-white w-64 py-6 flex flex-col">
            <div class="px-6 mb-8">
                <h1 class="text-2xl font-bold">SDCKL Library</h1>
            </div>
            
            <nav class="flex-1">
                <a href="dashboard.php" class="flex items-center px-6 py-3 bg-gray-900">
                    <i class="fas fa-tachometer-alt mr-3"></i>
                    Dashboard
                </a>
                <a href="books.php" class="flex items-center px-6 py-3 hover:bg-gray-700">
                    <i class="fas fa-book mr-3"></i>
                    Books
                </a>
                <a href="members.php" class="flex items-center px-6 py-3 hover:bg-gray-700">
                    <i class="fas fa-users mr-3"></i>
                    Members
                </a>
                <a href="borrowings.php" class="flex items-center px-6 py-3 hover:bg-gray-700">
                    <i class="fas fa-exchange-alt mr-3"></i>
                    Borrowings
                </a>
                <a href="categories.php" class="flex items-center px-6 py-3 hover:bg-gray-700">
                    <i class="fas fa-tags mr-3"></i>
                    Categories
                </a>
                <a href="reports.php" class="flex items-center px-6 py-3 hover:bg-gray-700">
                    <i class="fas fa-chart-bar mr-3"></i>
                    Reports
                </a>
            </nav>
            
            <div class="px-6 py-4 border-t border-gray-700">
                <div class="flex items-center">
                    <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($_SESSION['full_name']); ?>" 
                         class="w-8 h-8 rounded-full mr-3">
                    <div>
                        <p class="text-sm"><?php echo htmlspecialchars($_SESSION['full_name']); ?></p>
                        <a href="logout.php" class="text-xs text-gray-400 hover:text-white">Logout</a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Content -->
        <div class="flex-1 overflow-x-hidden overflow-y-auto">
            <!-- Top bar -->
            <div class="bg-white shadow px-6 py-4">
                <h2 class="text-xl font-semibold">Dashboard</h2>
            </div>

            <!-- Statistics Cards -->
            <div class="p-6 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                <div class="bg-white rounded-lg shadow p-6">
                    <div class="flex items-center">
                        <div class="p-3 rounded-full bg-blue-100 text-blue-500">
                            <i class="fas fa-book text-2xl"></i>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm text-gray-500">Total Books</p>
                            <p class="text-2xl font-semibold"><?php echo $stats['total_books']; ?></p>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow p-6">
                    <div class="flex items-center">
                        <div class="p-3 rounded-full bg-green-100 text-green-500">
                            <i class="fas fa-users text-2xl"></i>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm text-gray-500">Active Members</p>
                            <p class="text-2xl font-semibold"><?php echo $stats['total_members']; ?></p>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow p-6">
                    <div class="flex items-center">
                        <div class="p-3 rounded-full bg-yellow-100 text-yellow-500">
                            <i class="fas fa-exchange-alt text-2xl"></i>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm text-gray-500">Books Borrowed</p>
                            <p class="text-2xl font-semibold"><?php echo $stats['books_borrowed']; ?></p>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow p-6">
                    <div class="flex items-center">
                        <div class="p-3 rounded-full bg-red-100 text-red-500">
                            <i class="fas fa-exclamation-circle text-2xl"></i>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm text-gray-500">Books Overdue</p>
                            <p class="text-2xl font-semibold"><?php echo $stats['books_overdue']; ?></p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Borrowings -->
            <div class="p-6">
                <div class="bg-white rounded-lg shadow">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h3 class="text-lg font-semibold">Recent Borrowings</h3>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Member</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Book</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Borrow Date</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Due Date</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <?php while($row = $recent_borrowings->fetch_assoc()): ?>
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap"><?php echo htmlspecialchars($row['member_name']); ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap"><?php echo htmlspecialchars($row['book_title']); ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap"><?php echo date('M d, Y', strtotime($row['borrow_date'])); ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap"><?php echo date('M d, Y', strtotime($row['due_date'])); ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                            <?php 
                                            echo match($row['status']) {
                                                'borrowed' => 'bg-green-100 text-green-800',
                                                'returned' => 'bg-blue-100 text-blue-800',
                                                'overdue' => 'bg-red-100 text-red-800',
                                                default => 'bg-gray-100 text-gray-800'
                                            };
                                            ?>">
                                            <?php echo ucfirst($row['status']); ?>
                                        </span>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
