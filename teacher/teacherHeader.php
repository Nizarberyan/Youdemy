<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once '../classes/Auth.php';
$auth = new Auth();
$auth->requireRole('teacher');

$currentPage = basename($_SERVER['PHP_SELF']);
?>

<!DOCTYPE html>
<html lang="en" class="h-full bg-gray-100">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Teacher Dashboard - LearnHub</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>

<body class="h-full">
    <nav class="bg-purple-800">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex">
                    <div class="flex-shrink-0 flex items-center">
                        <a href="index.php" class="text-2xl font-bold text-white">Teacher Panel</a>
                    </div>
                    <div class="hidden sm:ml-6 sm:flex sm:space-x-8">
                        <a href="index.php"
                            class="<?= $currentPage === 'index.php' ? 'bg-purple-900 text-white' : 'text-gray-300 hover:bg-purple-700 hover:text-white' ?> px-3 py-2 rounded-md text-sm font-medium">
                            Dashboard
                        </a>
                        <a href="courses/index.php"
                            class="<?= strpos($currentPage, 'courses') !== false ? 'bg-purple-900 text-white' : 'text-gray-300 hover:bg-purple-700 hover:text-white' ?> px-3 py-2 rounded-md text-sm font-medium">
                            My Courses
                        </a>
                        <a href="students.php"
                            class="<?= $currentPage === 'students.php' ? 'bg-purple-900 text-white' : 'text-gray-300 hover:bg-purple-700 hover:text-white' ?> px-3 py-2 rounded-md text-sm font-medium">
                            My Students
                        </a>
                    </div>
                </div>
                <div class="hidden sm:ml-6 sm:flex sm:items-center">
                    <div class="flex items-center space-x-4">
                        <a href="../index.php" class="text-gray-300 hover:bg-purple-700 hover:text-white px-3 py-2 rounded-md text-sm font-medium">
                            <i class="fas fa-home mr-2"></i>Main Site
                        </a>
                        <a href="../auth/logout.php"
                            class="text-gray-300 hover:bg-purple-700 hover:text-white px-3 py-2 rounded-md text-sm font-medium">
                            <i class="fas fa-sign-out-alt mr-2"></i>Logout
                        </a>
                        <div class="relative">
                            <button type="button"
                                onclick="toggleDropdown()"
                                class="flex text-sm rounded-full focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-offset-gray-800 focus:ring-white"
                                id="user-menu-button"
                                aria-expanded="false"
                                aria-haspopup="true">
                                <img class="h-8 w-8 rounded-full object-cover"
                                    src="../<?= $_SESSION['user_profile_image'] ?? 'assets/images/default-avatar.png' ?>"
                                    alt="Admin profile">
                            </button>
                            <div id="user-dropdown"
                                class="hidden origin-top-right absolute right-0 mt-2 w-48 rounded-md shadow-lg py-1 bg-white ring-1 ring-black ring-opacity-5 focus:outline-none"
                                role="menu"
                                aria-orientation="vertical"
                                aria-labelledby="user-menu-button"
                                tabindex="-1">
                                <a href="profile.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100" role="menuitem">Profile</a>
                                <a href="../auth/logout.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100" role="menuitem">Sign out</a>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="-mr-2 flex items-center sm:hidden">
                    <button type="button"
                        onclick="toggleMobileMenu()"
                        class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 hover:text-white hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-inset focus:ring-white"
                        aria-controls="mobile-menu"
                        aria-expanded="false">
                        <span class="sr-only">Open main menu</span>
                        <i class="fas fa-bars"></i>
                    </button>
                </div>
            </div>
        </div>

        <!-- Mobile menu -->
        <div class="sm:hidden hidden" id="mobile-menu">
            <div class="px-2 pt-2 pb-3 space-y-1">
                <a href="index.php"
                    class="<?= $currentPage === 'index.php' ? 'bg-purple-900 text-white' : 'text-gray-300 hover:bg-purple-700 hover:text-white' ?> block px-3 py-2 rounded-md text-base font-medium">
                    Dashboard
                </a>
                <a href="courses/index.php"
                    class="<?= strpos($currentPage, 'courses') !== false ? 'bg-purple-900 text-white' : 'text-gray-300 hover:bg-purple-700 hover:text-white' ?> block px-3 py-2 rounded-md text-base font-medium">
                    My Courses
                </a>
                <a href="students.php"
                    class="<?= $currentPage === 'students.php' ? 'bg-purple-900 text-white' : 'text-gray-300 hover:bg-purple-700 hover:text-white' ?> block px-3 py-2 rounded-md text-base font-medium">
                    My Students
                </a>
            </div>
            <div class="pt-4 pb-3 border-t border-gray-700">
                <div class="flex items-center px-5">
                    <div class="flex-shrink-0">
                        <img class="h-10 w-10 rounded-full object-cover"
                            src="../<?= $_SESSION['user_profile_image'] ?? 'assets/images/default-avatar.png' ?>"
                            alt="Admin profile">
                    </div>
                    <div class="ml-3">
                        <div class="text-base font-medium text-white"><?= htmlspecialchars($_SESSION['user_name']) ?></div>
                        <div class="text-sm font-medium text-gray-400"><?= htmlspecialchars($_SESSION['user_email']) ?></div>
                    </div>
                </div>
                <div class="mt-3 px-2 space-y-1">
                    <a href="../profile.php" class="block px-3 py-2 rounded-md text-base font-medium text-gray-400 hover:text-white hover:bg-gray-700">Profile</a>
                    <a href="../auth/logout.php" class="block px-3 py-2 rounded-md text-base font-medium text-gray-400 hover:text-white hover:bg-gray-700">Sign out</a>
                </div>
            </div>
        </div>
    </nav>

    <script>
        function toggleDropdown() {
            const dropdown = document.getElementById('user-dropdown');
            dropdown.classList.toggle('hidden');
        }

        function toggleMobileMenu() {
            const mobileMenu = document.getElementById('mobile-menu');
            mobileMenu.classList.toggle('hidden');
        }

        // Close dropdown when clicking outside
        document.addEventListener('click', function(event) {
            const dropdown = document.getElementById('user-dropdown');
            const button = document.getElementById('user-menu-button');

            if (!dropdown.contains(event.target) && !button.contains(event.target)) {
                dropdown.classList.add('hidden');
            }
        });
    </script>