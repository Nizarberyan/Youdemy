<?php
require_once '../config/database.php';
require_once '../classes/Course.php';
require_once '../classes/Auth.php';

session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    $_SESSION['error'] = 'You must be logged in to access this course.';
    header('Location: ../login.php');
    exit;
}

$course = new Course();
$auth = new Auth();

// Get course ID from URL
$courseId = $_GET['id'] ?? null;
if (!$courseId) {
    header('Location: index.php');
    exit;
}

// Verify enrollment
if (!$course->isStudentEnrolled($_SESSION['user_id'], $courseId)) {
    $_SESSION['error'] = 'You are not enrolled in this course.';
    header('Location: view.php?id=' . $courseId);
    exit;
}

// Get course details
$courseData = $course->getById($courseId);
if (!$courseData) {
    header('Location: index.php');
    exit;
}

// Get curriculum
$curriculum = $course->getCurriculum($courseId);

// Get student progress
$progress = $course->getStudentProgress($_SESSION['user_id'], $courseId);

require_once '../includes/header.php';
?>

<div class="min-h-screen bg-gray-100">
    <!-- Course Header -->
    <div class="bg-indigo-600 text-white py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <h1 class="text-3xl font-bold"><?= htmlspecialchars($courseData['title']) ?></h1>
            <div class="mt-2">
                <div class="flex items-center">
                    <div class="flex items-center">
                        <i class="fas fa-graduation-cap text-indigo-200 mr-2"></i>
                        <span>Progress: <?= $progress ?>%</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Course Content -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="bg-white rounded-lg shadow-lg p-6 max-w-[80%] mx-auto">
            <!-- Content Display -->
            <div class="aspect-w-16 aspect-h-9 w-full mb-6">
                <iframe
                    src="<?= htmlspecialchars($courseData['preview_url'] ?? '') ?>"
                    frameborder="0"
                    allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                    allowfullscreen
                    class="w-full rounded-lg shadow-lg"
                    style="min-height: 80vh;"></iframe>
            </div>

            <!-- Complete Button -->
            <div class="mt-6 text-center">
                <form action="complete_lesson.php" method="POST">
                    <input type="hidden" name="course_id" value="<?= $courseId ?>">
                    <button type="submit"
                        class="bg-green-600 text-white px-8 py-3 rounded-lg font-semibold hover:bg-green-700 transition duration-200">
                        Mark as Complete
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>