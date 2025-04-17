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
                $member_id = sanitizeInput($_POST['member_id']);
                $full_name = sanitizeInput($_POST['full_name']);
                $email = sanitizeInput($_POST['email']);
                $phone = sanitizeInput($_POST['phone']);
                $member_type = sanitizeInput($_POST['member_type']);
                $department = sanitizeInput($_POST['department']);
                
                $stmt = $conn->prepare("INSERT INTO members (member_id, full_name, email, phone, member_type, department) VALUES (?, ?, ?, ?, ?, ?)");
                $stmt->bind_param("ssssss", $member_id, $full_name, $email, $phone, $member_type, $department);
                
                if ($stmt->execute()) {
                    $success_message = "Member added successfully!";
                } else {
                    $error_message = "Error adding member: " . $conn->error;
                }
                break;

            case 'update_status':
                $member_id = (int)$_POST['member_id'];
                $status = sanitizeInput($_POST['status']);
                
                $stmt = $conn->prepare("UPDATE members SET status = ? WHERE id = ?");
                $stmt->bind_param("si", $status, $member_id);
                
                if ($stmt->execute()) {
                    $success_message = "Member status updated successfully!";
                } else {
                    $error_message = "Error updating member status: " . $conn->error;
                }
                break;
        }
    }
}

// Fetch members with their borrowing statistics
$members_query = "
    SELECT m.*,
           COUNT(DISTINCT b.id) as total_borrowed,
           SUM(CASE WHEN b.status = 'borrowed' THEN 1 ELSE 0 END) as currently_borrowed,
           SUM(CASE WHEN b.status = 'overdue' THEN 1 ELSE 0 END) as overdue_books
    FROM members m
    LEFT JOIN borrowings b ON m.id = b.member_id
    GROUP BY m.id
    ORDER BY m.full_name";
$members = $conn->query($members_query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Members Management - SDCKL Library</title>
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
                <a href="members.php" class="flex items-center px-6 py-3 bg-gray-900">
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
                    <h2 class="text-2xl font-semibold">Members Management</h2>
                    <button onclick="document.getElementById('addMemberModal').classList.remove('hidden')" 
                            class="bg-blue-500 text-white px-4 py-2 rounded-lg hover:bg-blue-600">
                        <i class="fas fa-plus mr-2"></i>Add New Member
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

                <!-- Members Table -->
                <div class="bg-white rounded-lg shadow overflow-hidden">
                    <table class="min-w-full">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Member ID</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Department</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Books Borrowed</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Overdue</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php while($member = $members->fetch_assoc()): ?>
                            <tr>
                                <td class="px-6 py-4"><?php echo htmlspecialchars($member['member_id']); ?></td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0 h-10 w-10">
                                            <img class="h-10 w-10 rounded-full" 
                                                 src="https://ui-avatars.com/api/?name=<?php echo urlencode($member['full_name']); ?>" 
                                                 alt="">
                                        </div>
                                        <div class="ml-4">
                                            <div class="text-sm font-medium text-gray-900">
                                                <?php echo htmlspecialchars($member['full_name']); ?>
                                            </div>
                                            <div class="text-sm text-gray-500">
                                                <?php echo htmlspecialchars($member['email']); ?>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                        <?php echo $member['member_type'] === 'student' ? 'bg-green-100 text-green-800' : 'bg-purple-100 text-purple-800'; ?>">
                                        <?php echo ucfirst($member['member_type']); ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4"><?php echo htmlspecialchars($member['department']); ?></td>
                                <td class="px-6 py-4"><?php echo $member['currently_borrowed']; ?>/<?php echo $member['total_borrowed']; ?></td>
                                <td class="px-6 py-4">
                                    <?php if ($member['overdue_books'] > 0): ?>
                                        <span class="text-red-600 font-medium"><?php echo $member['overdue_books']; ?></span>
                                    <?php else: ?>
                                        0
                                    <?php endif; ?>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                        <?php echo $member['status'] === 'active' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'; ?>">
                                        <?php echo ucfirst($member['status']); ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    <button onclick="editMember(<?php echo $member['id']; ?>)" 
                                            class="text-blue-500 hover:text-blue-700 mr-3">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <form method="POST" class="inline">
                                        <input type="hidden" name="action" value="update_status">
                                        <input type="hidden" name="member_id" value="<?php echo $member['id']; ?>">
                                        <input type="hidden" name="status" 
                                               value="<?php echo $member['status'] === 'active' ? 'inactive' : 'active'; ?>">
                                        <button type="submit" class="text-<?php echo $member['status'] === 'active' ? 'red' : 'green'; ?>-500 
                                                hover:text-<?php echo $member['status'] === 'active' ? 'red' : 'green'; ?>-700">
                                            <i class="fas fa-<?php echo $member['status'] === 'active' ? 'ban' : 'check'; ?>"></i>
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

    <!-- Add Member Modal -->
    <div id="addMemberModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden overflow-y-auto h-full w-full">
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-medium">Add New Member</h3>
                <button onclick="document.getElementById('addMemberModal').classList.add('hidden')" 
                        class="text-gray-600 hover:text-gray-800">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <form method="POST" class="space-y-4">
                <input type="hidden" name="action" value="add">
                
                <div>
                    <label class="block text-sm font-medium text-gray-700">Member ID</label>
                    <input type="text" name="member_id" required 
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">Full Name</label>
                    <input type="text" name="full_name" required 
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">Email</label>
                    <input type="email" name="email" required 
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">Phone</label>
                    <input type="tel" name="phone" required 
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">Member Type</label>
                    <select name="member_type" required 
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        <option value="student">Student</option>
                        <option value="faculty">Faculty</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">Department</label>
                    <input type="text" name="department" required 
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                </div>

                <div class="flex justify-end">
                    <button type="button" 
                            onclick="document.getElementById('addMemberModal').classList.add('hidden')"
                            class="bg-gray-200 text-gray-700 px-4 py-2 rounded-lg mr-2 hover:bg-gray-300">
                        Cancel
                    </button>
                    <button type="submit" 
                            class="bg-blue-500 text-white px-4 py-2 rounded-lg hover:bg-blue-600">
                        Add Member
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function editMember(memberId) {
            // Implement edit functionality
            alert('Edit member ' + memberId);
        }
    </script>
</body>
</html>
