<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../classes/Auth.php';

// Check if user is logged in and is a student
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'student') {
    header('Location: ../login.php');
    exit;
}

$auth = new Auth();
$user = $auth->getUserById($_SESSION['user_id']);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Dashboard - YouDemy</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>

<body class="bg-gray-100">
    <!-- Navigation -->
    <nav class="bg-white shadow-lg">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <!-- Left side -->
                <div class="flex">
                    <!-- Logo -->
                    <div class="flex-shrink-0 flex items-center">
                        <a href="../index.php" class="text-2xl font-bold text-indigo-600">YouDemy</a>
                    </div>
                    <!-- Navigation Links -->
                    <div class="hidden sm:ml-6 sm:flex sm:space-x-8">
                        <a href="../courses/index.php"
                            class="border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700 inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium">
                            Browse Courses
                        </a>
                        <a href="courses.php"
                            class="border-indigo-500 text-gray-900 inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium">
                            My Courses
                        </a>
                        <a href="certificates.php"
                            class="border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700 inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium">
                            Certificates
                        </a>
                    </div>
                </div>

                <!-- Right side -->
                <div class="flex items-center">
                    <!-- Search -->
                    <div class="flex-1 flex items-center justify-center px-2 lg:ml-6 lg:justify-end">
                        <div class="max-w-lg w-full lg:max-w-xs">
                            <form action="../courses/index.php" method="get">
                                <label for="search" class="sr-only">Search courses</label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <i class="fas fa-search text-gray-400"></i>
                                    </div>
                                    <input id="search" name="search"
                                        class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-md leading-5 bg-white placeholder-gray-500 focus:outline-none focus:placeholder-gray-400 focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                                        placeholder="Search courses"
                                        type="search">
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Profile dropdown -->
                    <div class="ml-3 relative">
                        <div class="flex items-center space-x-4">
                            <div class="flex items-center">
                                <img class="h-8 w-8 rounded-full object-cover"
                                    src="<?= $user['profile_image'] ?? '../assets/images/default-avatar.png' ?>"
                                    alt="Profile">
                                <span class="ml-2 text-gray-700"><?= htmlspecialchars($user['first_name']) ?></span>
                            </div>
                            <div class="relative">
                                <a href="../profile.php" class="text-gray-500 hover:text-gray-700">
                                    <i class="fas fa-cog"></i>
                                </a>
                            </div>
                            <div>
                                <a href="../logout.php" class="text-gray-500 hover:text-gray-700">
                                    <i class="fas fa-sign-out-alt"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Mobile menu -->
        <div class="sm:hidden">
            <div class="pt-2 pb-3 space-y-1">
                <a href="../courses/index.php"
                    class="text-gray-500 hover:text-gray-700 block pl-3 pr-4 py-2 text-base font-medium">
                    Browse Courses
                </a>
                <a href="courses.php"
                    class="text-indigo-600 block pl-3 pr-4 py-2 text-base font-medium">
                    My Courses
                </a>
                <a href="certificates.php"
                    class="text-gray-500 hover:text-gray-700 block pl-3 pr-4 py-2 text-base font-medium">
                    Certificates
                </a>
            </div>
        </div>
    </nav>

    <!-- Flash Messages -->
    <?php if (isset($_SESSION['message'])): ?>
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-4">
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
                <?= htmlspecialchars($_SESSION['message']) ?>
            </div>
        </div>
        <?php unset($_SESSION['message']); ?>
    <?php endif; ?>

    <?php if (isset($_SESSION['error'])): ?>
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-4">
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
                <?= htmlspecialchars($_SESSION['error']) ?>
            </div>
        </div>
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>
</body>

</html>