<?php
require_once '../config/database.php';
require_once '../classes/Course.php';
require_once '../classes/Category.php';

session_start();

$course = new Course();
$category = new Category();

// Get filters
$categoryId = $_GET['category'] ?? '';
$search = $_GET['search'] ?? '';
$level = $_GET['level'] ?? '';
$sort = $_GET['sort'] ?? 'newest';
$page = $_GET['page'] ?? 1;
$limit = 12;
$offset = ($page - 1) * $limit;

$filters = [
    'category_id' => $categoryId,
    'search' => $search,
    'level' => $level,
    'status' => 'published' // Only show published courses
];

$courses = $course->getAll($filters, $limit, $offset, $sort);
$totalCourses = $course->countAll($filters);
$totalPages = ceil($totalCourses / $limit);
$categories = $category->getAll();

require_once '../includes/header.php';
?>

<div class="min-h-screen bg-gray-100">
    <div class="py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center space-y-4 md:space-y-0">
                <h1 class="text-3xl font-bold text-gray-900">Explore Courses</h1>
                <div class="flex space-x-4">
                    <select id="sortFilter" onchange="applyFilters()" class="rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        <option value="newest" <?= $sort === 'newest' ? 'selected' : '' ?>>Newest</option>
                        <option value="popular" <?= $sort === 'popular' ? 'selected' : '' ?>>Most Popular</option>
                        <option value="price_low" <?= $sort === 'price_low' ? 'selected' : '' ?>>Price: Low to High</option>
                        <option value="price_high" <?= $sort === 'price_high' ? 'selected' : '' ?>>Price: High to Low</option>
                    </select>
                </div>
            </div>

            <div class="mt-6 grid grid-cols-1 md:grid-cols-4 gap-6">
                <!-- Filters Sidebar -->
                <div class="bg-white rounded-lg shadow p-6">
                    <h2 class="text-lg font-medium text-gray-900 mb-4">Filters</h2>

                    <div class="space-y-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Category</label>
                            <select id="categoryFilter" onchange="applyFilters()" class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <option value="">All Categories</option>
                                <?php foreach ($categories as $cat): ?>
                                    <option value="<?= $cat['id'] ?>" <?= $categoryId == $cat['id'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($cat['name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Level</label>
                            <select id="levelFilter" onchange="applyFilters()" class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <option value="">All Levels</option>
                                <option value="beginner" <?= $level === 'beginner' ? 'selected' : '' ?>>Beginner</option>
                                <option value="intermediate" <?= $level === 'intermediate' ? 'selected' : '' ?>>Intermediate</option>
                                <option value="advanced" <?= $level === 'advanced' ? 'selected' : '' ?>>Advanced</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Search</label>
                            <input type="text" id="searchInput" placeholder="Search courses..." value="<?= htmlspecialchars($search) ?>"
                                class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        </div>
                    </div>
                </div>

                <!-- Course Grid -->
                <div class="md:col-span-3">
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                        <?php foreach ($courses as $course): ?>
                            <div class="bg-white rounded-lg shadow overflow-hidden">
                                <div class="relative pb-2/3">
                                    <img class="absolute h-full w-full object-cover"
                                        src="../<?= $course['thumbnail'] ?? 'assets/images/course-default.png' ?>"
                                        alt="<?= htmlspecialchars($course['title']) ?>">
                                </div>
                                <div class="p-4">
                                    <h3 class="text-lg font-medium text-gray-900">
                                        <a href="view.php?id=<?= $course['id'] ?>" class="hover:text-indigo-600">
                                            <?= htmlspecialchars($course['title']) ?>
                                        </a>
                                    </h3>
                                    <p class="mt-1 text-sm text-gray-500">
                                        <?= htmlspecialchars(substr($course['description'], 0, 100)) . (strlen($course['description']) > 100 ? '...' : '') ?>
                                    </p>
                                    <div class="mt-4 flex items-center justify-between">
                                        <div class="flex items-center">
                                            <img class="h-8 w-8 rounded-full object-cover"
                                                src="../assets/images/default-avatar.png"
                                                alt="Teacher">
                                            <span class="ml-2 text-sm text-gray-600">
                                                <?= htmlspecialchars($course['first_name'] . ' ' . $course['last_name']) ?>
                                            </span>
                                        </div>
                                        <span class="text-lg font-medium text-indigo-600">
                                            $<?= number_format($course['price'], 2) ?>
                                        </span>
                                    </div>
                                    <div class="mt-4 flex items-center text-sm text-gray-500">
                                        <span class="bg-indigo-100 text-indigo-800 px-2 py-1 rounded-full text-xs">
                                            <?= ucfirst($course['level'] ?? 'All Levels') ?>
                                        </span>
                                        <span class="ml-2 flex items-center">
                                            <i class="fas fa-users mr-1"></i>
                                            <?= $course['enrollment_count'] ?? 0 ?> students
                                        </span>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <?php if (empty($courses)): ?>
                        <div class="text-center py-12">
                            <i class="fas fa-search text-4xl text-gray-400"></i>
                            <p class="mt-2 text-gray-500">No courses found matching your criteria.</p>
                        </div>
                    <?php endif; ?>

                    <?php if ($totalPages > 1): ?>
                        <div class="mt-6 flex justify-center">
                            <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px" aria-label="Pagination">
                                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                                    <a href="?page=<?= $i ?>&category=<?= $categoryId ?>&level=<?= $level ?>&search=<?= urlencode($search) ?>&sort=<?= $sort ?>"
                                        class="relative inline-flex items-center px-4 py-2 border border-gray-300 bg-white text-sm font-medium text-gray-700 hover:bg-gray-50
                                      <?= $i === $page ? 'z-10 bg-indigo-50 border-indigo-500 text-indigo-600' : '' ?>">
                                        <?= $i ?>
                                    </a>
                                <?php endfor; ?>
                            </nav>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    let searchTimeout;

    document.getElementById('searchInput').addEventListener('input', function(e) {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(() => applyFilters(), 500);
    });

    function applyFilters() {
        const category = document.getElementById('categoryFilter').value;
        const level = document.getElementById('levelFilter').value;
        const search = document.getElementById('searchInput').value;
        const sort = document.getElementById('sortFilter').value;

        window.location.href = `index.php?category=${category}&level=${level}&search=${encodeURIComponent(search)}&sort=${sort}`;
    }
</script>

<?php require_once '../includes/footer.php'; ?>