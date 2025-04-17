<?php
require_once 'includes/header.php';
require_once 'models/Book.php';
require_once 'models/Member.php';
require_once 'models/Borrowing.php';

$book = new Book();
$member = new Member();
$borrowing = new Borrowing();

// Get statistics
$bookStats = $book->getStatistics();
$totalMembers = $member->count();
$activeMembers = $member->count('status = "active"');
$totalBorrowings = $borrowing->count();
$overdueBorrowings = $borrowing->count('status = "overdue"');

// Get recent activities
$recentBorrowings = $borrowing->getRecentBorrowings(5);
$popularBooks = $book->getPopularBooks(5);

<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
    <!-- Total Books -->
    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center">
            <div class="p-3 rounded-full bg-blue-100 text-blue-500">
                <i class="fas fa-book text-2xl"></i>
            </div>
            <div class="ml-4">
                <p class="text-sm text-gray-500">Total Books</p>
                <p class="text-xl font-semibold"><?php echo $bookStats['total_books']; ?></p>
            </div>
        </div>
        <div class="mt-4">
            <p class="text-sm text-gray-500">Available Copies: <?php echo $bookStats['available_copies']; ?></p>
        </div>
    </div>

    <!-- Total Members -->
    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center">
            <div class="p-3 rounded-full bg-green-100 text-green-500">
                <i class="fas fa-users text-2xl"></i>
            </div>
            <div class="ml-4">
                <p class="text-sm text-gray-500">Total Members</p>
                <p class="text-xl font-semibold"><?php echo $totalMembers; ?></p>
            </div>
        </div>
        <div class="mt-4">
            <p class="text-sm text-gray-500">Active Members: <?php echo $activeMembers; ?></p>
        </div>
    </div>

    <!-- Total Borrowings -->
    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center">
            <div class="p-3 rounded-full bg-purple-100 text-purple-500">
                <i class="fas fa-exchange-alt text-2xl"></i>
            </div>
            <div class="ml-4">
                <p class="text-sm text-gray-500">Total Borrowings</p>
                <p class="text-xl font-semibold"><?php echo $totalBorrowings; ?></p>
            </div>
        </div>
        <div class="mt-4">
            <p class="text-sm text-gray-500">Overdue: <?php echo $overdueBorrowings; ?></p>
        </div>
    </div>

    <!-- Book Categories -->
    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center">
            <div class="p-3 rounded-full bg-yellow-100 text-yellow-500">
                <i class="fas fa-tags text-2xl"></i>
            </div>
            <div class="ml-4">
                <p class="text-sm text-gray-500">Categories</p>
                <p class="text-xl font-semibold"><?php echo $bookStats['total_categories']; ?></p>
            </div>
        </div>
        <div class="mt-4">
            <p class="text-sm text-gray-500">Authors: <?php echo $bookStats['total_authors']; ?></p>
        </div>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
    <!-- Recent Borrowings -->
    <div class="bg-white rounded-lg shadow">
        <div class="p-6 border-b">
            <h2 class="text-lg font-semibold">Recent Borrowings</h2>
        </div>
        <div class="p-6">
            <?php if ($recentBorrowings): ?>
                <div class="space-y-4">
                    <?php foreach ($recentBorrowings as $borrowing): ?>
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="font-medium"><?php echo htmlspecialchars($borrowing['book_title']); ?></p>
                                <p class="text-sm text-gray-500">
                                    Borrowed by: <?php echo htmlspecialchars($borrowing['member_name']); ?>
                                </p>
                            </div>
                            <div class="text-right">
                                <p class="text-sm"><?php echo formatDate($borrowing['borrow_date']); ?></p>
                                <p class="text-sm <?php echo $borrowing['status'] === 'overdue' ? 'text-red-500' : 'text-gray-500'; ?>">
                                    <?php echo ucfirst($borrowing['status']); ?>
                                </p>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                <div class="mt-6 text-center">
                    <a href="borrowings.php" class="text-blue-500 hover:text-blue-600">View All Borrowings</a>
                </div>
            <?php else: ?>
                <p class="text-gray-500 text-center">No recent borrowings</p>
            <?php endif; ?>
        </div>
    </div>

    <!-- Popular Books -->
    <div class="bg-white rounded-lg shadow">
        <div class="p-6 border-b">
            <h2 class="text-lg font-semibold">Popular Books</h2>
        </div>
        <div class="p-6">
            <?php if ($popularBooks): ?>
                <div class="space-y-4">
                    <?php foreach ($popularBooks as $book): ?>
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="font-medium"><?php echo htmlspecialchars($book['title']); ?></p>
                                <p class="text-sm text-gray-500">
                                    by <?php echo htmlspecialchars($book['author']); ?>
                                </p>
                            </div>
                            <div class="text-right">
                                <p class="text-sm">Borrowed <?php echo $book['borrow_count']; ?> times</p>
                                <p class="text-sm text-gray-500">
                                    Available: <?php echo $book['available_copies']; ?>/<?php echo $book['copies']; ?>
                                </p>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                <div class="mt-6 text-center">
                    <a href="books.php" class="text-blue-500 hover:text-blue-600">View All Books</a>
                </div>
            <?php else: ?>
                <p class="text-gray-500 text-center">No books available</p>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
