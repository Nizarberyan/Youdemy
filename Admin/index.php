<?php
session_start();
require_once '../config/database.php';
require_once '../classes/Auth.php';
require_once '../classes/User.php';
require_once '../classes/Course.php';
require_once '../classes/Category.php';
// die(var_dump($_SESSION['user_role']));
// $auth = new Auth();
// $auth->requireRole('admin');

$user = new User();
$course = new Course();
$category = new Category();

$totalUsers = $user->countAll();
$totalTeachers = $user->countAll('teacher');
$totalStudents = $user->countAll('student');
$totalCourses = $course->countAll();
$popularCourses = $course->getPopularCourses(5);
$categoryStats = $category->getCategoryStats();

$pendingTeachers = $user->getAll('teacher', 'pending');

require_once '../includes/header.php';
?>

<div class="min-h-screen bg-gray-100">
    <div class="py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <h1 class="text-3xl font-bold text-gray-900">Admin Dashboard</h1>

            <div class="mt-6 grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4">
                <div class="bg-white overflow-hidden shadow rounded-lg">
                    <div class="p-5">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <i class="fas fa-users text-2xl text-indigo-600"></i>
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt class="text-sm font-medium text-gray-500 truncate">Total Users</dt>
                                    <dd class="text-2xl font-semibold text-gray-900"><?= $totalUsers ?></dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow rounded-lg">
                    <div class="p-5">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <i class="fas fa-chalkboard-teacher text-2xl text-purple-600"></i>
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt class="text-sm font-medium text-gray-500 truncate">Total Teachers</dt>
                                    <dd class="text-2xl font-semibold text-gray-900"><?= $totalTeachers ?></dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow rounded-lg">
                    <div class="p-5">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <i class="fas fa-user-graduate text-2xl text-green-600"></i>
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
                                <i class="fas fa-book text-2xl text-blue-600"></i>
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt class="text-sm font-medium text-gray-500 truncate">Total Courses</dt>
                                    <dd class="text-2xl font-semibold text-gray-900"><?= $totalCourses ?></dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="mt-8 grid grid-cols-1 gap-6 lg:grid-cols-2">
                <div class="bg-white shadow rounded-lg p-6">
                    <h2 class="text-xl font-semibold text-gray-900 mb-4">Popular Courses</h2>
                    <div class="flow-root">
                        <ul class="-my-5 divide-y divide-gray-200">
                            <?php foreach ($popularCourses as $course): ?>
                                <li class="py-4">
                                    <div class="flex items-center space-x-4">
                                        <div class="flex-shrink-0">
                                            <img class="h-8 w-8 rounded-full" src="<?= $course['thumbnail'] ?? 'assets\images\default-avatar.png' ?>" alt="">
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <p class="text-sm font-medium text-gray-900 truncate"><?= htmlspecialchars($course['title']) ?></p>
                                            <p class="text-sm text-gray-500">Enrollments: <?= $course['enrollment_count'] ?></p>
                                        </div>
                                    </div>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                </div>

                <div class="bg-white shadow rounded-lg p-6">
                    <h2 class="text-xl font-semibold text-gray-900 mb-4">Pending Teacher Approvals</h2>
                    <div class="flow-root">
                        <ul class="-my-5 divide-y divide-gray-200">
                            <?php foreach ($pendingTeachers as $teacher): ?>
                                <li class="py-4">
                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center space-x-4">
                                            <div class="flex-shrink-0">
                                                <img class="h-8 w-8 rounded-full" src="<?= $teacher['profile_image'] ?? '../assets/images/default-avatar.png' ?>" alt="">
                                            </div>
                                            <div class="flex-1 min-w-0">
                                                <p class="text-sm font-medium text-gray-900 truncate">
                                                    <?= htmlspecialchars($teacher['first_name'] . ' ' . $teacher['last_name']) ?>
                                                </p>
                                                <p class="text-sm text-gray-500"><?= htmlspecialchars($teacher['email']) ?></p>
                                            </div>
                                        </div>
                                        <?php if ($teacher['status'] === 'pending'): ?>
                                            <div class="flex space-x-2">
                                                <form method="POST" action="users.php" class="inline">
                                                    <input type="hidden" name="action" value="approve">
                                                    <input type="hidden" name="id" value="<?= $teacher['id'] ?>">
                                                    <button type="submit" 
                                                        onclick="return confirm('Are you sure you want to approve this teacher?')"
                                                        class="text-green-600 hover:text-green-900">
                                                        Approve
                                                    </button>
                                                </form>
                                                
                                                <form method="POST" action="users.php" class="inline">
                                                    <input type="hidden" name="action" value="reject">
                                                    <input type="hidden" name="id" value="<?= $teacher['id'] ?>">
                                                    <button type="submit" 
                                                        onclick="return confirm('Are you sure you want to reject this teacher?')"
                                                        class="text-red-600 hover:text-red-900">
                                                        Reject
                                                    </button>
                                                </form>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                </div>
            </div>

            <div class="flex space-x-4">
                <a href="users.php" class="text-gray-600 hover:text-gray-900">Users</a>
                <a href="courses.php" class="text-gray-600 hover:text-gray-900">Courses</a>
                <a href="categories.php" class="text-gray-600 hover:text-gray-900">Categories</a>
            </div>
        </div>
    </div>
</div>

<script>
    function approveTeacher(teacherId) {
        if (confirm('Are you sure you want to approve this teacher?')) {
            fetch(`users.php?action=approve&id=${teacherId}`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        location.reload();
                    } else {
                        alert('Error approving teacher');
                    }
                });
        }
    }

    function rejectTeacher(teacherId) {
        if (confirm('Are you sure you want to reject this teacher?')) {
            fetch(`users.php?action=reject&id=${teacherId}`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        location.reload();
                    } else {
                        alert('Error rejecting teacher');
                    }
                });
        }
    }
</script>

<?php require_once '../includes/footer.php'; ?>