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
            case 'add':
                $title = sanitizeInput($_POST['title']);
                $author = sanitizeInput($_POST['author']);
                $isbn = sanitizeInput($_POST['isbn']);
                $category_id = (int)$_POST['category_id'];
                $publisher = sanitizeInput($_POST['publisher']);
                $publication_year = (int)$_POST['publication_year'];
                $copies = (int)$_POST['copies'];
                $shelf_location = sanitizeInput($_POST['shelf_location']);
                $description = sanitizeInput($_POST['description']);

                $stmt = $conn->prepare("INSERT INTO books (title, author, isbn, category_id, publisher, publication_year, copies, available_copies, shelf_location, description) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt->bind_param("sssissiiss", $title, $author, $isbn, $category_id, $publisher, $publication_year, $copies, $copies, $shelf_location, $description);
                
                if ($stmt->execute()) {
                    $success_message = "Book added successfully!";
                } else {
                    $error_message = "Error adding book: " . $conn->error;
                }
                break;

            case 'delete':
                $book_id = (int)$_POST['book_id'];
                $stmt = $conn->prepare("DELETE FROM books WHERE id = ? AND id NOT IN (SELECT book_id FROM borrowings WHERE status = 'borrowed')");
                $stmt->bind_param("i", $book_id);
                
                if ($stmt->execute()) {
                    $success_message = "Book deleted successfully!";
                } else {
                    $error_message = "Error deleting book: " . $conn->error;
                }
                break;
        }
    }
}

// Fetch categories for dropdown
$categories = $conn->query("SELECT id, name FROM categories ORDER BY name");

// Fetch books with category names
$books_query = "
    SELECT b.*, c.name as category_name 
    FROM books b 
    LEFT JOIN categories c ON b.category_id = c.id 
    ORDER BY b.title";
$books = $conn->query($books_query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Books Management - SDCKL Library</title>
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
                <a href="books.php" class="flex items-center px-6 py-3 bg-gray-900">
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
            <div class="container mx-auto px-6 py-8">
                <div class="flex justify-between items-center mb-6">
                    <h2 class="text-2xl font-semibold">Books Management</h2>
                    <button onclick="document.getElementById('addBookModal').classList.remove('hidden')" 
                            class="bg-blue-500 text-white px-4 py-2 rounded-lg hover:bg-blue-600">
                        <i class="fas fa-plus mr-2"></i>Add New Book
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

                <!-- Books Table -->
                <div class="bg-white rounded-lg shadow overflow-hidden">
                    <table class="min-w-full">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Title</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Author</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ISBN</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Category</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Copies</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Available</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php while($book = $books->fetch_assoc()): ?>
                            <tr>
                                <td class="px-6 py-4"><?php echo htmlspecialchars($book['title']); ?></td>
                                <td class="px-6 py-4"><?php echo htmlspecialchars($book['author']); ?></td>
                                <td class="px-6 py-4"><?php echo htmlspecialchars($book['isbn']); ?></td>
                                <td class="px-6 py-4"><?php echo htmlspecialchars($book['category_name']); ?></td>
                                <td class="px-6 py-4"><?php echo $book['copies']; ?></td>
                                <td class="px-6 py-4"><?php echo $book['available_copies']; ?></td>
                                <td class="px-6 py-4">
                                    <button onclick="editBook(<?php echo $book['id']; ?>)" 
                                            class="text-blue-500 hover:text-blue-700 mr-3">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <form method="POST" class="inline" onsubmit="return confirm('Are you sure you want to delete this book?');">
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="book_id" value="<?php echo $book['id']; ?>">
                                        <button type="submit" class="text-red-500 hover:text-red-700">
                                            <i class="fas fa-trash"></i>
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

    <!-- Add Book Modal -->
    <div id="addBookModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden overflow-y-auto h-full w-full">
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-medium">Add New Book</h3>
                <button onclick="document.getElementById('addBookModal').classList.add('hidden')" 
                        class="text-gray-600 hover:text-gray-800">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <form method="POST" class="space-y-4">
                <input type="hidden" name="action" value="add">
                
                <div>
                    <label class="block text-sm font-medium text-gray-700">Title</label>
                    <input type="text" name="title" required 
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">Author</label>
                    <input type="text" name="author" required 
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">ISBN</label>
                    <input type="text" name="isbn" required 
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">Category</label>
                    <select name="category_id" required 
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        <?php while($category = $categories->fetch_assoc()): ?>
                            <option value="<?php echo $category['id']; ?>">
                                <?php echo htmlspecialchars($category['name']); ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">Publisher</label>
                    <input type="text" name="publisher" required 
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">Publication Year</label>
                    <input type="number" name="publication_year" required min="1900" max="<?php echo date('Y'); ?>" 
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">Number of Copies</label>
                    <input type="number" name="copies" required min="1" 
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">Shelf Location</label>
                    <input type="text" name="shelf_location" required 
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">Description</label>
                    <textarea name="description" rows="3" 
                              class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"></textarea>
                </div>

                <div class="flex justify-end">
                    <button type="button" 
                            onclick="document.getElementById('addBookModal').classList.add('hidden')"
                            class="bg-gray-200 text-gray-700 px-4 py-2 rounded-lg mr-2 hover:bg-gray-300">
                        Cancel
                    </button>
                    <button type="submit" 
                            class="bg-blue-500 text-white px-4 py-2 rounded-lg hover:bg-blue-600">
                        Add Book
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function editBook(bookId) {
            // Implement edit functionality
            alert('Edit book ' + bookId);
        }
    </script>
</body>
</html>
