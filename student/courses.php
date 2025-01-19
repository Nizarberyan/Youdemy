<?php
session_start();
require_once '../config/database.php';
require_once '../classes/Auth.php';
require_once '../classes/Course.php';
require_once '../classes/User.php';

$auth = new Auth();
$auth->requireRole('student');

$user = new User();
$course = new Course();

$studentId = $_SESSION['user_id'];
$status = $_GET['status'] ?? '';
$search = $_GET['search'] ?? '';
$page = $_GET['page'] ?? 1;
$limit = 10;
$offset = ($page - 1) * $limit;

$filters = [
    'student_id' => $studentId,
    'status' => $status,
    'search' => $search
];

$enrolledCourses = $course->getEnrolledCourses($studentId, $filters, $limit, $offset);
$totalCourses = $course->countEnrolledCourses($studentId, $filters);
$totalPages = ceil($totalCourses / $limit);

// Get progress for each course
$progress = [];
foreach ($enrolledCourses as $enrolledCourse) {
    $progress[$enrolledCourse['id']] = $course->getStudentProgress($studentId, $enrolledCourse['id']);
}

require_once '../includes/header.php';
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
                    <div class="bg-white overflow-hidden shadow rounded-lg">
                        <div class="relative pb-2/3">
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
                                            <span class="text-xs font-semibold inline-block py-1 px-2 uppercase rounded-full text-indigo-600 bg-indigo-200">
                                                Progress
                                            </span>
                                        </div>
                                        <div class="text-right">
                                            <span class="text-xs font-semibold inline-block text-indigo-600">
                                                <?= $progress[$course['id']] ?>%
                                            </span>
                                        </div>
                                    </div>
                                    <div class="overflow-hidden h-2 mb-4 text-xs flex rounded bg-indigo-200">
                                        <div style="width:<?= $progress[$course['id']] ?>%"
                                            class="shadow-none flex flex-col text-center whitespace-nowrap text-white justify-center bg-indigo-500">
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="mt-4 flex justify-between items-center">
                                <a href="../courses/learn.php?id=<?= $course['id'] ?>"
                                    class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700">
                                    <?= $progress[$course['id']] === 100 ? 'Review Course' : 'Continue Learning' ?>
                                </a>
                                <?php if ($progress[$course['id']] === 100): ?>
                                    <a href="certificates.php?course_id=<?= $course['id'] ?>"
                                        class="text-indigo-600 hover:text-indigo-900">
                                        <i class="fas fa-certificate"></i> Certificate
                                    </a>
                                <?php endif; ?>
                            </div>
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