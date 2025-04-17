<?php
require_once 'database/config.php';
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'borrow':
                $book_id = (int)$_POST['book_id'];
                $member_id = (int)$_POST['member_id'];
                $borrow_date = date('Y-m-d');
                $due_date = date('Y-m-d', strtotime('+14 days')); // 2 weeks borrowing period

                // Start transaction
                $conn->begin_transaction();

                try {
                    // Check if book is available
                    $book_check = $conn->prepare("SELECT available_copies FROM books WHERE id = ? AND available_copies > 0");
                    $book_check->bind_param("i", $book_id);
                    $book_check->execute();
                    $result = $book_check->get_result();

                    if ($result->num_rows === 0) {
                        throw new Exception("Book is not available for borrowing.");
                    }

                    // Check if member has any overdue books
                    $overdue_check = $conn->prepare("SELECT COUNT(*) as overdue FROM borrowings WHERE member_id = ? AND status = 'overdue'");
                    $overdue_check->bind_param("i", $member_id);
                    $overdue_check->execute();
                    $overdue_result = $overdue_check->get_result()->fetch_assoc();

                    if ($overdue_result['overdue'] > 0) {
                        throw new Exception("Member has overdue books. Cannot borrow more books.");
                    }

                    // Create borrowing record
                    $stmt = $conn->prepare("INSERT INTO borrowings (book_id, member_id, borrow_date, due_date) VALUES (?, ?, ?, ?)");
                    $stmt->bind_param("iiss", $book_id, $member_id, $borrow_date, $due_date);
                    $stmt->execute();

                    // Update book available copies
                    $update = $conn->prepare("UPDATE books SET available_copies = available_copies - 1 WHERE id = ?");
                    $update->bind_param("i", $book_id);
                    $update->execute();

                    $conn->commit();
                    $success_message = "Book borrowed successfully!";
                } catch (Exception $e) {
                    $conn->rollback();
                    $error_message = $e->getMessage();
                }
                break;

            case 'return':
                $borrowing_id = (int)$_POST['borrowing_id'];
                $return_date = date('Y-m-d');

                // Start transaction
                $conn->begin_transaction();

                try {
                    // Get borrowing details
                    $borrowing = $conn->prepare("SELECT book_id, due_date FROM borrowings WHERE id = ?");
                    $borrowing->bind_param("i", $borrowing_id);
                    $borrowing->execute();
                    $result = $borrowing->get_result()->fetch_assoc();

                    // Calculate fine if overdue
                    $fine = 0;
                    if (strtotime($return_date) > strtotime($result['due_date'])) {
                        $days_overdue = floor((strtotime($return_date) - strtotime($result['due_date'])) / (60 * 60 * 24));
                        $fine = $days_overdue * 1.00; // $1 per day fine
                    }

                    // Update borrowing record
                    $stmt = $conn->prepare("UPDATE borrowings SET return_date = ?, fine_amount = ?, status = 'returned' WHERE id = ?");
                    $stmt->bind_param("sdi", $return_date, $fine, $borrowing_id);
                    $stmt->execute();

                    // Update book available copies
                    $update = $conn->prepare("UPDATE books SET available_copies = available_copies + 1 WHERE id = ?");
                    $update->bind_param("i", $result['book_id']);
                    $update->execute();

                    $conn->commit();
                    $success_message = "Book returned successfully!" . ($fine > 0 ? " Fine amount: $" . number_format($fine, 2) : "");
                } catch (Exception $e) {
                    $conn->rollback();
                    $error_message = $e->getMessage();
                }
                break;
        }
    }
}

// Fetch active books and members for the borrow form
$books = $conn->query("SELECT id, title, author FROM books WHERE available_copies > 0 ORDER BY title");
$members = $conn->query("SELECT id, member_id, full_name FROM members WHERE status = 'active' ORDER BY full_name");

// Fetch current borrowings
$borrowings_query = "
    SELECT b.*, 
           bk.title as book_title, 
           m.full_name as member_name,
           m.member_id as member_number
    FROM borrowings b
    JOIN books bk ON b.book_id = bk.id
    JOIN members m ON b.member_id = m.id
    WHERE b.status != 'returned'
    ORDER BY b.due_date ASC";
$borrowings = $conn->query($borrowings_query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Borrowings Management - SDCKL Library</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        body { font-family: 'Poppins', sans-serif; }
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
                <a href="dashboard.php" class="flex items-center px-6 py-3 hover:bg-gray-700">
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
                <a href="borrowings.php" class="flex items-center px-6 py-3 bg-gray-900">
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
            <div class="container mx-auto px-6 py-8">
                <div class="flex justify-between items-center mb-6">
                    <h2 class="text-2xl font-semibold">Borrowings Management</h2>
                    <button onclick="document.getElementById('borrowBookModal').classList.remove('hidden')" 
                            class="bg-blue-500 text-white px-4 py-2 rounded-lg hover:bg-blue-600">
                        <i class="fas fa-plus mr-2"></i>New Borrowing
                    </button>
                </div>

                <?php if (isset($success_message)): ?>
                    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4">
                        <?php echo $success_message; ?>
                    </div>
                <?php endif; ?>

                <?php if (isset($error_message)): ?>
                    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4">
                        <?php echo $error_message; ?>
                    </div>
                <?php endif; ?>

                <!-- Current Borrowings Table -->
                <div class="bg-white rounded-lg shadow overflow-hidden">
                    <table class="min-w-full">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Book</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Member</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Borrow Date</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Due Date</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php while($borrowing = $borrowings->fetch_assoc()): ?>
                            <tr>
                                <td class="px-6 py-4"><?php echo htmlspecialchars($borrowing['book_title']); ?></td>
                                <td class="px-6 py-4">
                                    <div>
                                        <div class="text-sm font-medium text-gray-900">
                                            <?php echo htmlspecialchars($borrowing['member_name']); ?>
                                        </div>
                                        <div class="text-sm text-gray-500">
                                            ID: <?php echo htmlspecialchars($borrowing['member_number']); ?>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4"><?php echo date('M d, Y', strtotime($borrowing['borrow_date'])); ?></td>
                                <td class="px-6 py-4">
                                    <span class="<?php echo strtotime($borrowing['due_date']) < time() ? 'text-red-600 font-medium' : ''; ?>">
                                        <?php echo date('M d, Y', strtotime($borrowing['due_date'])); ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                        <?php 
                                        echo match($borrowing['status']) {
                                            'borrowed' => 'bg-blue-100 text-blue-800',
                                            'overdue' => 'bg-red-100 text-red-800',
                                            default => 'bg-gray-100 text-gray-800'
                                        };
                                        ?>">
                                        <?php echo ucfirst($borrowing['status']); ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    <form method="POST" class="inline" onsubmit="return confirm('Are you sure you want to return this book?');">
                                        <input type="hidden" name="action" value="return">
                                        <input type="hidden" name="borrowing_id" value="<?php echo $borrowing['id']; ?>">
                                        <button type="submit" class="text-green-500 hover:text-green-700">
                                            <i class="fas fa-undo"></i> Return
                                        </button>
                                    </form>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Borrow Book Modal -->
    <div id="borrowBookModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden overflow-y-auto h-full w-full">
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-medium">Borrow Book</h3>
                <button onclick="document.getElementById('borrowBookModal').classList.add('hidden')" 
                        class="text-gray-600 hover:text-gray-800">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <form method="POST" class="space-y-4">
                <input type="hidden" name="action" value="borrow">
                
                <div>
                    <label class="block text-sm font-medium text-gray-700">Book</label>
                    <select name="book_id" required 
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        <?php while($book = $books->fetch_assoc()): ?>
                            <option value="<?php echo $book['id']; ?>">
                                <?php echo htmlspecialchars($book['title'] . ' - ' . $book['author']); ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">Member</label>
                    <select name="member_id" required 
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        <?php while($member = $members->fetch_assoc()): ?>
                            <option value="<?php echo $member['id']; ?>">
                                <?php echo htmlspecialchars($member['member_id'] . ' - ' . $member['full_name']); ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <div class="flex justify-end">
                    <button type="button" 
                            onclick="document.getElementById('borrowBookModal').classList.add('hidden')"
                            class="bg-gray-200 text-gray-700 px-4 py-2 rounded-lg mr-2 hover:bg-gray-300">
                        Cancel
                    </button>
                    <button type="submit" 
                            class="bg-blue-500 text-white px-4 py-2 rounded-lg hover:bg-blue-600">
                        Borrow Book
                    </button>
                </div>
            </form>
        </div>
    </div>
</body>
</html>
