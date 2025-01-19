<?php
require_once 'config/database.php';
require_once 'classes/Course.php';
require_once 'classes/Category.php';
require_once 'classes/Auth.php';

session_start();


// Check if user is already logged in and trying to access login/register
// if (isset($_SESSION['user_id'])) {
//     $currentPage = basename($_SERVER['PHP_SELF']);
//     if (in_array($currentPage, ['login.php', 'register.php'])) {
//         header('Location: ../index.php');
//         exit;
//     }
    // }

$course = new Course();
$category = new Category();

// Get featured courses
$featuredCourses = $course->getFeatured(6);
$categories = $category->getAll();

// Get popular courses
$popularCourses = $course->getPopularCourses(6);

require_once 'includes/header.php';
?>

<div class="bg-white">
    <!-- Hero Section -->
    <div class="relative bg-gradient-to-r from-purple-600 to-indigo-600">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-24">
            <div class="text-center">
                <h1 class="text-4xl tracking-tight font-extrabold text-white sm:text-5xl md:text-6xl">
                    Learn Without Limits
                </h1>
                <p class="mt-3 max-w-md mx-auto text-base text-indigo-200 sm:text-lg md:mt-5 md:text-xl md:max-w-3xl">
                    Start, switch, or advance your career with thousands of courses from expert instructors.
                </p>
                <div class="mt-5 max-w-md mx-auto sm:flex sm:justify-center md:mt-8">
                    <div class="rounded-md shadow">
                        <a href="courses/index.php" class="w-full flex items-center justify-center px-8 py-3 border border-transparent text-base font-medium rounded-md text-indigo-600 bg-white hover:bg-gray-50 md:py-4 md:text-lg md:px-10">
                            Browse Courses
                        </a>
                    </div>
                    <?php if (!isset($_SESSION['user_id'])): ?>
                        <div class="mt-3 rounded-md shadow sm:mt-0 sm:ml-3">
                            <a href="auth/register.php" class="w-full flex items-center justify-center px-8 py-3 border border-transparent text-base font-medium rounded-md text-white bg-indigo-500 hover:bg-indigo-600 md:py-4 md:text-lg md:px-10">
                                Join for Free
                            </a>
                        </div>
                        <div class="mt-3 sm:mt-0 sm:ml-3">
                            <a href="auth/login.php" class="w-full flex items-center justify-center px-8 py-3 border border-gray-300 text-base font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 md:py-4 md:text-lg md:px-10">
                                Sign In
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Categories Section -->
    <div class="py-12 bg-gray-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center">
                <h2 class="text-3xl font-extrabold text-gray-900">Popular Categories</h2>
                <p class="mt-4 text-lg text-gray-600">Choose from over 100,000 online video courses</p>
            </div>
            <div class="mt-10 grid grid-cols-2 gap-6 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6">
                <?php foreach ($categories as $category): ?>
                    <a href="courses/browse.php?category=<?= $category['id'] ?>"
                        class="group bg-white p-4 rounded-lg shadow-sm hover:shadow-md transition-shadow">
                        <div class="text-center">
                            <i class="<?= $category['icon'] ?? 'fas fa-book' ?> text-3xl text-indigo-600 mb-2"></i>
                            <h3 class="text-sm font-medium text-gray-900 group-hover:text-indigo-600">
                                <?= htmlspecialchars($category['name']) ?>
                            </h3>
                            <p class="mt-1 text-xs text-gray-500">
                                <?= $category['course_count'] ?? 0 ?> courses
                            </p>
                        </div>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <!-- Featured Courses Section -->
    <div class="py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center">
                <h2 class="text-3xl font-extrabold text-gray-900">Featured Courses</h2>
                <p class="mt-4 text-lg text-gray-600">Hand-picked courses to get you started</p>
            </div>
            <div class="mt-10 grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
                <?php foreach ($featuredCourses as $course): ?>
                    <div class="bg-white rounded-lg shadow-sm overflow-hidden hover:shadow-md transition-shadow">
                        <div class="relative pb-2/3">
                            <img class="absolute h-full w-full object-cover"
                                src="<?= $course['thumbnail'] ?? 'assets/images/course-default.png' ?>"
                                alt="<?= htmlspecialchars($course['title']) ?>">
                        </div>
                        <div class="p-4">
                            <h3 class="text-lg font-medium text-gray-900">
                                <a href="courses/view.php?id=<?= $course['id'] ?>" class="hover:text-indigo-600">
                                    <?= htmlspecialchars($course['title']) ?>
                                </a>
                            </h3>
                            <p class="mt-1 text-sm text-gray-500">
                                <?= htmlspecialchars(truncateText($course['description'], 100)) ?>
                            </p>
                            <div class="mt-4 flex items-center justify-between">
                                <div class="flex items-center">
                                    <img class="h-8 w-8 rounded-full object-cover"
                                        src="<?= $course['teacher_image'] ?? 'assets/images/default-avatar.png' ?>"
                                        alt="<?= htmlspecialchars($course['teacher_name']) ?>">
                                    <span class="ml-2 text-sm text-gray-600">
                                        <?= htmlspecialchars($course['teacher_name']) ?>
                                    </span>
                                </div>
                                <span class="text-lg font-medium text-indigo-600">
                                    <?= formatCurrency($course['price']) ?>
                                </span>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            <div class="mt-10 text-center">
                <a href="courses/browse.php" class="inline-flex items-center px-6 py-3 border border-transparent text-base font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700">
                    View All Courses
                </a>
            </div>
        </div>
    </div>

    <!-- Stats Section -->
    <div class="bg-gradient-to-r from-purple-600 to-indigo-600 py-16 mt-16 stats-section">
        <div class="max-w-7xl mx-auto px-4">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-8 text-white text-center">
                <div class="p-6">
                    <div class="text-4xl font-bold mb-2 stats-number">100K+</div>
                    <div class="text-purple-200">Happy Students 😊</div>
                </div>
                <div class="p-6">
                    <div class="text-4xl font-bold mb-2">1000+</div>
                    <div class="text-purple-200">Amazing Courses 📚</div>
                </div>
                <div class="p-6">
                    <div class="text-4xl font-bold mb-2">500+</div>
                    <div class="text-purple-200">Expert Teachers 🎓</div>
                </div>
                <div class="p-6">
                    <div class="text-4xl font-bold mb-2">4.9</div>
                    <div class="text-purple-200">Star Rating ⭐</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Popular Courses Section -->
    <div class="py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center">
                <h2 class="text-3xl font-extrabold text-gray-900">Most Popular</h2>
                <p class="mt-4 text-lg text-gray-600">Learner favorites in the past month</p>
            </div>
            <div class="mt-10 grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
                <?php foreach ($popularCourses as $course): ?>
                    <div class="bg-white rounded-lg shadow-sm overflow-hidden hover:shadow-md transition-shadow">
                        <div class="relative pb-2/3">
                            <img class="absolute h-full w-full object-cover"
                                src="<?= $course['thumbnail'] ?? 'assets/images/course-default.png' ?>"
                                alt="<?= htmlspecialchars($course['title']) ?>">
                        </div>
                        <div class="p-4">
                            <h3 class="text-lg font-medium text-gray-900">
                                <a href="courses/view.php?id=<?= $course['id'] ?>" class="hover:text-indigo-600">
                                    <?= htmlspecialchars($course['title']) ?>
                                </a>
                            </h3>
                            <p class="mt-1 text-sm text-gray-500">
                                <?= htmlspecialchars(truncateText($course['description'], 100)) ?>
                            </p>
                            <div class="mt-4 flex items-center justify-between">
                                <div class="flex items-center">
                                    <img class="h-8 w-8 rounded-full object-cover"
                                        src="<?= $course['teacher_image'] ?? 'assets/images/default-avatar.png' ?>"
                                        alt="<?= htmlspecialchars($course['teacher_name']) ?>">
                                    <span class="ml-2 text-sm text-gray-600">
                                        <?= htmlspecialchars($course['teacher_name']) ?>
                                    </span>
                                </div>
                                <span class="text-lg font-medium text-indigo-600">
                                    <?= formatCurrency($course['price']) ?>
                                </span>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            <div class="mt-10 text-center">
                <a href="courses/index.php?sort=popular" class="inline-flex items-center px-6 py-3 border border-transparent text-base font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700">
                    View All Popular Courses
                </a>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>