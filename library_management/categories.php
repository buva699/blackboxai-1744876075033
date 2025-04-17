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
                $name = sanitizeInput($_POST['name']);
                $description = sanitizeInput($_POST['description']);
                
                $stmt = $conn->prepare("INSERT INTO categories (name, description) VALUES (?, ?)");
                $stmt->bind_param("ss", $name, $description);
                
                if ($stmt->execute()) {
                    $success_message = "Category added successfully!";
                } else {
                    $error_message = "Error adding category: " . $conn->error;
                }
                break;

            case 'edit':
                $id = (int)$_POST['category_id'];
                $name = sanitizeInput($_POST['name']);
                $description = sanitizeInput($_POST['description']);
                
                $stmt = $conn->prepare("UPDATE categories SET name = ?, description = ? WHERE id = ?");
                $stmt->bind_param("ssi", $name, $description, $id);
                
                if ($stmt->execute()) {
                    $success_message = "Category updated successfully!";
                } else {
                    $error_message = "Error updating category: " . $conn->error;
                }
                break;

            case 'delete':
                $id = (int)$_POST['category_id'];
                
                // Check if category has books
                $check = $conn->prepare("SELECT COUNT(*) as count FROM books WHERE category_id = ?");
                $check->bind_param("i", $id);
                $check->execute();
                $result = $check->get_result()->fetch_assoc();
                
                if ($result['count'] > 0) {
                    $error_message = "Cannot delete category: Category has books assigned to it.";
                } else {
                    $stmt = $conn->prepare("DELETE FROM categories WHERE id = ?");
                    $stmt->bind_param("i", $id);
                    
                    if ($stmt->execute()) {
                        $success_message = "Category deleted successfully!";
                    } else {
                        $error_message = "Error deleting category: " . $conn->error;
                    }
                }
                break;
        }
    }
}

// Fetch categories with book counts
$categories_query = "
    SELECT c.*, COUNT(b.id) as book_count 
    FROM categories c 
    LEFT JOIN books b ON c.id = b.category_id 
    GROUP BY c.id 
    ORDER BY c.name";
$categories = $conn->query($categories_query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Categories Management - SDCKL Library</title>
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
                <a href="borrowings.php" class="flex items-center px-6 py-3 hover:bg-gray-700">
                    <i class="fas fa-exchange-alt mr-3"></i>
                    Borrowings
                </a>
                <a href="categories.php" class="flex items-center px-6 py-3 bg-gray-900">
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
                    <h2 class="text-2xl font-semibold">Categories Management</h2>
                    <button onclick="document.getElementById('addCategoryModal').classList.remove('hidden')" 
                            class="bg-blue-500 text-white px-4 py-2 rounded-lg hover:bg-blue-600">
                        <i class="fas fa-plus mr-2"></i>Add New Category
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

                <!-- Categories Grid -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <?php while($category = $categories->fetch_assoc()): ?>
                    <div class="bg-white rounded-lg shadow p-6">
                        <div class="flex justify-between items-start mb-4">
                            <div>
                                <h3 class="text-xl font-semibold"><?php echo htmlspecialchars($category['name']); ?></h3>
                                <p class="text-sm text-gray-500"><?php echo $category['book_count']; ?> books</p>
                            </div>
                            <div class="flex space-x-2">
                                <button onclick="editCategory(<?php echo $category['id']; ?>, '<?php echo addslashes($category['name']); ?>', '<?php echo addslashes($category['description']); ?>')" 
                                        class="text-blue-500 hover:text-blue-700">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <?php if ($category['book_count'] == 0): ?>
                                <form method="POST" class="inline" onsubmit="return confirm('Are you sure you want to delete this category?');">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="category_id" value="<?php echo $category['id']; ?>">
                                    <button type="submit" class="text-red-500 hover:text-red-700">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                                <?php endif; ?>
                            </div>
                        </div>
                        <p class="text-gray-600"><?php echo htmlspecialchars($category['description']); ?></p>
                    </div>
                    <?php endwhile; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Category Modal -->
    <div id="addCategoryModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden overflow-y-auto h-full w-full">
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-medium">Add New Category</h3>
                <button onclick="document.getElementById('addCategoryModal').classList.add('hidden')" 
                        class="text-gray-600 hover:text-gray-800">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <form method="POST" class="space-y-4">
                <input type="hidden" name="action" value="add">
                
                <div>
                    <label class="block text-sm font-medium text-gray-700">Category Name</label>
                    <input type="text" name="name" required 
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">Description</label>
                    <textarea name="description" rows="3" 
                              class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"></textarea>
                </div>

                <div class="flex justify-end">
                    <button type="button" 
                            onclick="document.getElementById('addCategoryModal').classList.add('hidden')"
                            class="bg-gray-200 text-gray-700 px-4 py-2 rounded-lg mr-2 hover:bg-gray-300">
                        Cancel
                    </button>
                    <button type="submit" 
                            class="bg-blue-500 text-white px-4 py-2 rounded-lg hover:bg-blue-600">
                        Add Category
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Edit Category Modal -->
    <div id="editCategoryModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden overflow-y-auto h-full w-full">
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-medium">Edit Category</h3>
                <button onclick="document.getElementById('editCategoryModal').classList.add('hidden')" 
                        class="text-gray-600 hover:text-gray-800">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <form method="POST" class="space-y-4">
                <input type="hidden" name="action" value="edit">
                <input type="hidden" name="category_id" id="edit_category_id">
                
                <div>
                    <label class="block text-sm font-medium text-gray-700">Category Name</label>
                    <input type="text" name="name" id="edit_category_name" required 
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">Description</label>
                    <textarea name="description" id="edit_category_description" rows="3" 
                              class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"></textarea>
                </div>

                <div class="flex justify-end">
                    <button type="button" 
                            onclick="document.getElementById('editCategoryModal').classList.add('hidden')"
                            class="bg-gray-200 text-gray-700 px-4 py-2 rounded-lg mr-2 hover:bg-gray-300">
                        Cancel
                    </button>
                    <button type="submit" 
                            class="bg-blue-500 text-white px-4 py-2 rounded-lg hover:bg-blue-600">
                        Update Category
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function editCategory(id, name, description) {
            document.getElementById('edit_category_id').value = id;
            document.getElementById('edit_category_name').value = name;
            document.getElementById('edit_category_description').value = description;
            document.getElementById('editCategoryModal').classList.remove('hidden');
        }
    </script>
</body>
</html>
