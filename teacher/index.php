<?php
require_once '../config/database.php';
require_once '../classes/Auth.php';
require_once '../classes/Course.php';
require_once '../classes/User.php';

$auth = new Auth();
$auth->requireRole('teacher');

$user = new User();
$course = new Course();

$teacherId = $_SESSION['user_id'];
$teacherData = $user->getById($teacherId);
$teacherCourses = $course->getByTeacher($teacherId);

$totalStudents = 0;
$totalRevenue = 0;
$courseStats = [];

foreach ($teacherCourses as $courseData) {
    $enrolledStudents = $course->getEnrolledStudents($courseData['id']);
    $totalStudents += count($enrolledStudents);
    $totalRevenue += $courseData['price'] * count($enrolledStudents);

    $courseStats[] = [
        'course' => $courseData,
        'students' => count($enrolledStudents)
    ];
}

require_once '../includes/header.php';
?>

<div class="min-h-screen bg-gray-100">
    <div class="py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center">
                <h1 class="text-3xl font-bold text-gray-900">Teacher Dashboard</h1>
                <a href="courses/create.php" class="bg-indigo-600 text-white px-4 py-2 rounded-md hover:bg-indigo-700">
                    Create New Course
                </a>
            </div>

            <div class="mt-6 grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-3">
                <div class="bg-white overflow-hidden shadow rounded-lg">
                    <div class="p-5">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <i class="fas fa-book text-2xl text-indigo-600"></i>
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt class="text-sm font-medium text-gray-500 truncate">Total Courses</dt>
                                    <dd class="text-2xl font-semibold text-gray-900"><?= count($teacherCourses) ?></dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow rounded-lg">
                    <div class="p-5">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <i class="fas fa-users text-2xl text-green-600"></i>
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt class="text-sm font-medium text-gray-500 truncate">Total Students</dt>
                                    <dd class="text-2xl font-semibold text-gray-900"><?= $totalStudents ?></dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow rounded-lg">
                    <div class="p-5">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <i class="fas fa-dollar-sign text-2xl text-yellow-600"></i>
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt class="text-sm font-medium text-gray-500 truncate">Total Revenue</dt>
                                    <dd class="text-2xl font-semibold text-gray-900">$<?= number_format($totalRevenue, 2) ?></dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="mt-8 bg-white shadow rounded-lg">
                <div class="px-4 py-5 sm:p-6">
                    <h2 class="text-lg font-medium text-gray-900">Your Courses</h2>
                    <div class="mt-4 grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
                        <?php foreach ($courseStats as $stat): ?>
                            <div class="relative rounded-lg border border-gray-300 bg-white px-6 py-5 shadow-sm flex items-center space-x-3 hover:border-gray-400">
                                <div class="flex-shrink-0">
                                    <img class="h-10 w-10 rounded-full object-cover"
                                        src="<?= $stat['course']['thumbnail'] ?? '../assets/images/course-default.png' ?>"
                                        alt="">
                                </div>
                                <div class="flex-1 min-w-0">
                                    <a href="courses/edit.php?id=<?= $stat['course']['id'] ?>" class="focus:outline-none">
                                        <p class="text-sm font-medium text-gray-900"><?= htmlspecialchars($stat['course']['title']) ?></p>
                                        <p class="text-sm text-gray-500 truncate">
                                            <?= $stat['students'] ?> students enrolled
                                        </p>
                                        <p class="text-sm text-gray-500">
                                            Status: <span class="capitalize"><?= $stat['course']['status'] ?></span>
                                        </p>
                                    </a>
                                </div>
                                <div class="flex-shrink-0">
                                    <a href="courses/edit.php?id=<?= $stat['course']['id'] ?>"
                                        class="text-indigo-600 hover:text-indigo-900">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <?php if (empty($courseStats)): ?>
                <div class="text-center mt-8">
                    <i class="fas fa-book-open text-4xl text-gray-400"></i>
                    <p class="mt-2 text-sm text-gray-500">You haven't created any courses yet.</p>
                    <p class="mt-1">
                        <a href="courses/create.php" class="text-indigo-600 hover:text-indigo-900">
                            Create your first course
                        </a>
                    </p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>