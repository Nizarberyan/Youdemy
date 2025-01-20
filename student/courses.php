<?php
require_once '../config/database.php';
require_once '../classes/Auth.php';
require_once '../classes/Course.php';

session_start();

// Check if user is logged in and is a student
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'student') {
    header('Location: ../login.php');
    exit;
}

$auth = new Auth();
$course = new Course();
$userData = $auth->getCurrentUser(); // Use this instead of User class

$status = $_GET['status'] ?? '';
$search = $_GET['search'] ?? '';
$page = $_GET['page'] ?? 1;
$limit = 10;
$offset = ($page - 1) * $limit;

$filters = [
    'student_id' => $_SESSION['user_id'],
    'status' => $status,
    'search' => $search
];

$enrolledCourses = $course->getEnrolledCourses($_SESSION['user_id'], $filters, $limit, $offset);
$totalCourses = $course->countEnrolledCourses($_SESSION['user_id'], $filters);
$totalPages = ceil($totalCourses / $limit);

// Get progress for each course
$progress = [];
foreach ($enrolledCourses as $enrolledCourse) {
    $progress[$enrolledCourse['id']] = $course->getStudentProgress($_SESSION['user_id'], $enrolledCourse['id']);
}

require_once 'studentHeader.php';
?>

<div class="min-h-screen bg-gray-100">
    <div class="py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center">
                <h1 class="text-3xl font-bold text-gray-900">My Courses</h1>
                <a href="../courses/index.php" class="bg-indigo-600 text-white px-4 py-2 rounded-md hover:bg-indigo-700">
                    Browse More Courses
                </a>
            </div>

            <div class="mt-4 flex justify-between items-center">
                <div class="flex space-x-4">
                    <select id="statusFilter" onchange="applyFilters()" class="rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        <option value="">All Progress</option>
                        <option value="in_progress" <?= $status === 'in_progress' ? 'selected' : '' ?>>In Progress</option>
                        <option value="completed" <?= $status === 'completed' ? 'selected' : '' ?>>Completed</option>
                    </select>
                </div>
                <div class="flex-1 max-w-md ml-4">
                    <input type="text" id="searchInput" placeholder="Search your courses..." value="<?= htmlspecialchars($search) ?>"
                        class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                </div>
            </div>

            <div class="mt-8 grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
                <?php foreach ($enrolledCourses as $course): ?>
                    <div class="bg-white overflow-hidden shadow rounded-lg relative">
                        <?php if ($course['is_completed']): ?>
                            <div class="absolute top-4 right-4 bg-green-500 text-white px-3 py-1 rounded-full text-sm font-semibold">
                                <i class="fas fa-check-circle mr-1"></i> Completed
                            </div>
                        <?php endif; ?>

                        <div class="relative pb-48">
                            <img class="absolute h-full w-full object-cover"
                                src="../<?= $course['thumbnail'] ?? 'assets/images/course-default.png' ?>"
                                alt="<?= htmlspecialchars($course['title']) ?>">
                        </div>
                        <div class="p-4">
                            <h3 class="text-lg font-medium text-gray-900">
                                <?= htmlspecialchars($course['title']) ?>
                            </h3>
                            <p class="mt-1 text-sm text-gray-500">
                                By <?= htmlspecialchars($course['teacher_name']) ?>
                            </p>
                            <div class="mt-4">
                                <div class="relative pt-1">
                                    <div class="flex mb-2 items-center justify-between">
                                        <div>
                                            <span class="text-xs font-semibold inline-block py-1 px-2 uppercase rounded-full <?= $course['is_completed'] ? 'text-green-600 bg-green-200' : 'text-indigo-600 bg-indigo-200' ?>">
                                                <?= $course['is_completed'] ? 'Completed' : 'Progress' ?>
                                            </span>
                                        </div>
                                        <div class="text-right">
                                            <span class="text-xs font-semibold inline-block <?= $course['is_completed'] ? 'text-green-600' : 'text-indigo-600' ?>">
                                                <?= $course['enrollment_progress'] ?>%
                                            </span>
                                        </div>
                                    </div>
                                    <div class="overflow-hidden h-2 mb-4 text-xs flex rounded bg-gray-200">
                                        <div style="width:<?= $course['enrollment_progress'] ?>%"
                                            class="shadow-none flex flex-col text-center whitespace-nowrap text-white justify-center <?= $course['is_completed'] ? 'bg-green-500' : 'bg-indigo-500' ?>">
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="mt-4 flex justify-between items-center">
                                <a href="../courses/learn.php?id=<?= $course['id'] ?>"
                                    class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white <?= $course['is_completed'] ? 'bg-green-600 hover:bg-green-700' : 'bg-indigo-600 hover:bg-indigo-700' ?>">
                                    <?= $course['is_completed'] ? 'Review Course' : 'Continue Learning' ?>
                                </a>
                                <?php if ($course['is_completed']): ?>
                                    <a href="certificates.php?course_id=<?= $course['id'] ?>"
                                        class="text-green-600 hover:text-green-900">
                                        <i class="fas fa-certificate mr-1"></i>View Certificate
                                    </a>
                                <?php endif; ?>
                            </div>
                            <?php if ($course['completed_at']): ?>
                                <p class="mt-2 text-xs text-gray-500">
                                    Completed on <?= date('M j, Y', strtotime($course['completed_at'])) ?>
                                </p>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <?php if (empty($enrolledCourses)): ?>
                <div class="text-center mt-8">
                    <i class="fas fa-book-open text-4xl text-gray-400"></i>
                    <p class="mt-2 text-sm text-gray-500">You haven't enrolled in any courses yet.</p>
                    <p class="mt-1">
                        <a href="../courses/browse.php" class="text-indigo-600 hover:text-indigo-900">
                            Browse our course catalog
                        </a>
                    </p>
                </div>
            <?php endif; ?>

            <?php if ($totalPages > 1): ?>
                <div class="mt-6 flex justify-center">
                    <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px" aria-label="Pagination">
                        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                            <a href="?page=<?= $i ?>&status=<?= $status ?>&search=<?= urlencode($search) ?>"
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

<script>
    let searchTimeout;

    document.getElementById('searchInput').addEventListener('input', function(e) {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(() => applyFilters(), 500);
    });

    function applyFilters() {
        const status = document.getElementById('statusFilter').value;
        const search = document.getElementById('searchInput').value;
        window.location.href = `courses.php?status=${status}&search=${encodeURIComponent(search)}`;
    }
</script>

<?php require_once '../includes/footer.php'; ?>