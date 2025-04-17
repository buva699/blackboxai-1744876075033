<?php
require_once __DIR__ . '/functions.php';
session_start();
requireLogin();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SDCKL Library Management System</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        body { font-family: 'Poppins', sans-serif; }
    </style>
</head>
<body class="bg-gray-100">
    <!-- Navigation -->
    <nav class="bg-white shadow-lg">
        <div class="max-w-7xl mx-auto px-4">
            <div class="flex justify-between h-16">
                <!-- Logo and Navigation Links -->
                <div class="flex">
                    <div class="flex-shrink-0 flex items-center">
                        <a href="dashboard.php" class="text-xl font-bold text-gray-800">SDCKL Library</a>
                    </div>
                    
                    <div class="hidden md:ml-6 md:flex md:space-x-8">
                        <a href="dashboard.php" class="<?php echo basename($_SERVER['PHP_SELF']) === 'dashboard.php' ? 'border-b-2 border-blue-500' : ''; ?> inline-flex items-center px-1 pt-1 text-sm font-medium text-gray-900">
                            <i class="fas fa-home mr-2"></i> Dashboard
                        </a>
                        <a href="books.php" class="<?php echo basename($_SERVER['PHP_SELF']) === 'books.php' ? 'border-b-2 border-blue-500' : ''; ?> inline-flex items-center px-1 pt-1 text-sm font-medium text-gray-500 hover:text-gray-700">
                            <i class="fas fa-book mr-2"></i> Books
                        </a>
                        <a href="members.php" class="<?php echo basename($_SERVER['PHP_SELF']) === 'members.php' ? 'border-b-2 border-blue-500' : ''; ?> inline-flex items-center px-1 pt-1 text-sm font-medium text-gray-500 hover:text-gray-700">
                            <i class="fas fa-users mr-2"></i> Members
                        </a>
                        <a href="borrowings.php" class="<?php echo basename($_SERVER['PHP_SELF']) === 'borrowings.php' ? 'border-b-2 border-blue-500' : ''; ?> inline-flex items-center px-1 pt-1 text-sm font-medium text-gray-500 hover:text-gray-700">
                            <i class="fas fa-exchange-alt mr-2"></i> Borrowings
                        </a>
                        <a href="categories.php" class="<?php echo basename($_SERVER['PHP_SELF']) === 'categories.php' ? 'border-b-2 border-blue-500' : ''; ?> inline-flex items-center px-1 pt-1 text-sm font-medium text-gray-500 hover:text-gray-700">
                            <i class="fas fa-tags mr-2"></i> Categories
                        </a>
                        <a href="reports.php" class="<?php echo basename($_SERVER['PHP_SELF']) === 'reports.php' ? 'border-b-2 border-blue-500' : ''; ?> inline-flex items-center px-1 pt-1 text-sm font-medium text-gray-500 hover:text-gray-700">
                            <i class="fas fa-chart-bar mr-2"></i> Reports
                        </a>
                    </div>
                </div>

                <!-- User Menu -->
                <div class="hidden md:ml-6 md:flex md:items-center">
                    <div class="ml-3 relative">
                        <div class="relative">
                            <button type="button" class="flex items-center max-w-xs text-sm rounded-full focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500" id="user-menu-button">
                                <span class="mr-2"><?php echo htmlspecialchars($_SESSION['full_name']); ?></span>
                                <i class="fas fa-user-circle text-2xl text-gray-400"></i>
                            </button>
                        </div>

                        <!-- Dropdown menu -->
                        <div class="hidden origin-top-right absolute right-0 mt-2 w-48 rounded-md shadow-lg py-1 bg-white ring-1 ring-black ring-opacity-5 focus:outline-none" id="user-menu-dropdown">
                            <a href="profile.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                <i class="fas fa-user mr-2"></i> Profile
                            </a>
                            <a href="settings.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                <i class="fas fa-cog mr-2"></i> Settings
                            </a>
                            <a href="logout.php" class="block px-4 py-2 text-sm text-red-700 hover:bg-gray-100">
                                <i class="fas fa-sign-out-alt mr-2"></i> Logout
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Mobile menu button -->
                <div class="flex items-center md:hidden">
                    <button type="button" class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 hover:text-gray-500 hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-inset focus:ring-blue-500" id="mobile-menu-button">
                        <span class="sr-only">Open main menu</span>
                        <i class="fas fa-bars"></i>
                    </button>
                </div>
            </div>
        </div>

        <!-- Mobile menu -->
        <div class="hidden md:hidden" id="mobile-menu">
            <div class="pt-2 pb-3 space-y-1">
                <a href="dashboard.php" class="block pl-3 pr-4 py-2 border-l-4 text-base font-medium hover:bg-gray-50 hover:border-gray-300 hover:text-gray-700">
                    <i class="fas fa-home mr-2"></i> Dashboard
                </a>
                <a href="books.php" class="block pl-3 pr-4 py-2 border-l-4 text-base font-medium hover:bg-gray-50 hover:border-gray-300 hover:text-gray-700">
                    <i class="fas fa-book mr-2"></i> Books
                </a>
                <a href="members.php" class="block pl-3 pr-4 py-2 border-l-4 text-base font-medium hover:bg-gray-50 hover:border-gray-300 hover:text-gray-700">
                    <i class="fas fa-users mr-2"></i> Members
                </a>
                <a href="borrowings.php" class="block pl-3 pr-4 py-2 border-l-4 text-base font-medium hover:bg-gray-50 hover:border-gray-300 hover:text-gray-700">
                    <i class="fas fa-exchange-alt mr-2"></i> Borrowings
                </a>
                <a href="categories.php" class="block pl-3 pr-4 py-2 border-l-4 text-base font-medium hover:bg-gray-50 hover:border-gray-300 hover:text-gray-700">
                    <i class="fas fa-tags mr-2"></i> Categories
                </a>
                <a href="reports.php" class="block pl-3 pr-4 py-2 border-l-4 text-base font-medium hover:bg-gray-50 hover:border-gray-300 hover:text-gray-700">
                    <i class="fas fa-chart-bar mr-2"></i> Reports
                </a>
            </div>
        </div>
    </nav>

    <!-- Flash Messages -->
    <?php if ($flash = getFlashMessage()): ?>
        <div class="max-w-7xl mx-auto px-4 mt-4">
            <div class="rounded-md p-4 <?php echo $flash['type'] === 'success' ? 'bg-green-50 text-green-800' : 'bg-red-50 text-red-800'; ?>">
                <p class="flex items-center">
                    <i class="fas <?php echo $flash['type'] === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle'; ?> mr-2"></i>
                    <?php echo htmlspecialchars($flash['message']); ?>
                </p>
            </div>
        </div>
    <?php endif; ?>

    <!-- Main Content Container -->
    <main class="max-w-7xl mx-auto px-4 py-6">
