<?php
require_once '../config/database.php';
require_once '../classes/Auth.php';
require_once '../classes/Course.php';
require_once '../classes/User.php';

$auth = new Auth();
$auth->requireRole('student');

$user = new User();
$course = new Course();

$studentId = $_SESSION['user_id'];
$studentData = $user->getById($studentId);
$enrolledCourses = $course->getEnrolledCourses($studentId);

// Get progress data
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
                <h1 class="text-3xl font-bold text-gray-900">My Learning Dashboard</h1>
                <a href="../courses/index.php" class="bg-indigo-600 text-white px-4 py-2 rounded-md hover:bg-indigo-700">
                    Browse Courses
                </a>
            </div>

            <div class="mt-6 grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-3">
                <div class="bg-white overflow-hidden shadow rounded-lg">
                    <div class="p-5">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <i class="fas fa-book-open text-2xl text-indigo-600"></i>
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt class="text-sm font-medium text-gray-500 truncate">Enrolled Courses</dt>
                                    <dd class="text-2xl font-semibold text-gray-900"><?= count($enrolledCourses) ?></dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow rounded-lg">
                    <div class="p-5">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <i class="fas fa-graduation-cap text-2xl text-green-600"></i>
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt class="text-sm font-medium text-gray-500 truncate">Completed Courses</dt>
                                    <dd class="text-2xl font-semibold text-gray-900">
                                        <?= count(array_filter($progress, fn($p) => $p === 100)) ?>
                                    </dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow rounded-lg">
                    <div class="p-5">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <i class="fas fa-certificate text-2xl text-yellow-600"></i>
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt class="text-sm font-medium text-gray-500 truncate">Certificates Earned</dt>
                                    <dd class="text-2xl font-semibold text-gray-900">
                                        <?= count(array_filter($progress, fn($p) => $p === 100)) ?>
                                    </dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="mt-8">
                <h2 class="text-lg font-medium text-gray-900">My Courses</h2>
                <div class="mt-4 grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-3">
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
                                    <?= htmlspecialchars(truncateText($course['description'], 100)) ?>
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
                                        Continue Learning
                                    </a>
                                    <?php if ($progress[$course['id']] === 100): ?>
                                        <a href="certificates.php?course_id=<?= $course['id'] ?>"
                                            class="text-indigo-600 hover:text-indigo-900">
                                            View Certificate
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
            </div>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>