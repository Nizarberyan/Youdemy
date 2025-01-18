<?php
require_once '../config/database.php';
require_once '../classes/Course.php';
require_once '../classes/User.php';
require_once '../classes/Auth.php';

session_start();

$course = new Course();
$auth = new Auth();

$courseId = $_GET['id'] ?? null;
if (!$courseId) {
    header('Location: index.php');
    exit;
}

$courseData = $course->getById($courseId);
if (!$courseData || $courseData['status'] !== 'published') {
    header('Location: index.php');
    exit;
}

$isEnrolled = false;
$progress = 0;
if (isset($_SESSION['user_id'])) {
    $isEnrolled = $course->isStudentEnrolled($_SESSION['user_id'], $courseId);
    if ($isEnrolled) {
        $progress = $course->getStudentProgress($_SESSION['user_id'], $courseId);
    }
}

// Get teacher data using the Course class's getById method which already includes teacher info
$teacher = $courseData;
$curriculum = $course->getCurriculum($courseId);
$reviews = $course->getReviews($courseId);
$avgRating = $course->getAverageRating($courseId);

require_once '../includes/header.php';
?>

<div class="min-h-screen bg-gray-100">
    <!-- Course Header -->
    <div class="bg-indigo-600 text-white py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="lg:grid lg:grid-cols-2 lg:gap-8 items-center">
                <div>
                    <h1 class="text-4xl font-bold mb-4"><?= htmlspecialchars($courseData['title']) ?></h1>
                    <p class="text-xl text-indigo-100 mb-6"><?= htmlspecialchars($courseData['subtitle']) ?></p>
                    <div class="flex items-center space-x-4 mb-6">
                        <div class="flex items-center">
                            <i class="fas fa-star text-yellow-400 mr-1"></i>
                            <span><?= number_format($avgRating, 1) ?> (<?= count($reviews) ?> reviews)</span>
                        </div>
                        <div class="flex items-center">
                            <i class="fas fa-users text-indigo-200 mr-1"></i>
                            <span><?= $courseData['student_count'] ?> students</span>
                        </div>
                        <div class="flex items-center">
                            <i class="fas fa-signal text-indigo-200 mr-1"></i>
                            <span><?= ucfirst($courseData['level']) ?></span>
                        </div>
                    </div>
                    <div class="flex items-center space-x-4">
                        <img class="h-12 w-12 rounded-full object-cover"
                            src="../<?= $teacher['profile_image'] ?? 'assets/images/default-avatar.png' ?>"
                            alt="<?= htmlspecialchars($teacher['first_name'] . ' ' . $teacher['last_name']) ?>">
                        <div>
                            <p class="font-medium">Created by</p>
                            <p class="text-indigo-100"><?= htmlspecialchars($teacher['first_name'] . ' ' . $teacher['last_name']) ?></p>
                        </div>
                    </div>
                </div>
                <div class="mt-8 lg:mt-0">
                    <div class="bg-white rounded-lg shadow-lg p-6">
                        <?php if ($courseData['thumbnail']): ?>
                            <img class="w-full rounded-lg mb-6" src="../<?= $courseData['thumbnail'] ?>" alt="Course thumbnail">
                        <?php endif; ?>
                        <div class="text-gray-900 text-3xl font-bold mb-6">
                            <?= formatCurrency($courseData['price']) ?>
                        </div>
                        <?php if ($isEnrolled): ?>
                            <a href="learn.php?id=<?= $courseId ?>"
                                class="block w-full text-center bg-indigo-600 text-white px-4 py-2 rounded-md hover:bg-indigo-700 mb-4">
                                Continue Learning (<?= $progress ?>% complete)
                            </a>
                        <?php else: ?>
                            <form action="enroll.php" method="POST">
                                <input type="hidden" name="course_id" value="<?= $courseId ?>">
                                <button type="submit" class="w-full bg-indigo-600 text-white px-4 py-2 rounded-md hover:bg-indigo-700 mb-4">
                                    Enroll Now
                                </button>
                            </form>
                        <?php endif; ?>
                        <div class="space-y-4 text-sm">
                            <div class="flex items-center">
                                <i class="fas fa-infinity w-6 text-gray-400"></i>
                                <span>Full lifetime access</span>
                            </div>
                            <div class="flex items-center">
                                <i class="fas fa-mobile-alt w-6 text-gray-400"></i>
                                <span>Access on mobile and TV</span>
                            </div>
                            <div class="flex items-center">
                                <i class="fas fa-certificate w-6 text-gray-400"></i>
                                <span>Certificate of completion</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Course Content -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <div class="lg:grid lg:grid-cols-3 lg:gap-8">
            <div class="lg:col-span-2">
                <!-- What you'll learn -->
                <div class="bg-white rounded-lg shadow-sm p-6 mb-8">
                    <h2 class="text-2xl font-bold mb-4">What you'll learn</h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <?php foreach (json_decode($courseData['learning_objectives'], true) as $objective): ?>
                            <div class="flex items-start">
                                <i class="fas fa-check text-green-500 mt-1 mr-2"></i>
                                <span><?= htmlspecialchars($objective) ?></span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- Course Description -->
                <div class="bg-white rounded-lg shadow-sm p-6 mb-8">
                    <h2 class="text-2xl font-bold mb-4">Course Description</h2>
                    <div class="prose max-w-none">
                        <?= nl2br(htmlspecialchars($courseData['description'])) ?>
                    </div>
                </div>

                <!-- Curriculum -->
                <div class="bg-white rounded-lg shadow-sm p-6 mb-8">
                    <h2 class="text-2xl font-bold mb-4">Course Content</h2>
                    <div class="space-y-4">
                        <?php foreach ($curriculum as $section): ?>
                            <div class="border rounded-lg">
                                <div class="p-4 bg-gray-50 rounded-t-lg font-medium">
                                    <?= htmlspecialchars($section['title']) ?>
                                </div>
                                <div class="divide-y">
                                    <?php foreach ($section['lessons'] as $lesson): ?>
                                        <div class="p-4 flex items-center justify-between">
                                            <div class="flex items-center">
                                                <i class="fas fa-play-circle text-gray-400 mr-2"></i>
                                                <span><?= htmlspecialchars($lesson['title']) ?></span>
                                            </div>
                                            <span class="text-sm text-gray-500"><?= $lesson['duration'] ?></span>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- Reviews -->
                <div class="bg-white rounded-lg shadow-sm p-6">
                    <h2 class="text-2xl font-bold mb-4">Student Reviews</h2>
                    <?php if (empty($reviews)): ?>
                        <p class="text-gray-500">No reviews yet.</p>
                    <?php else: ?>
                        <div class="space-y-6">
                            <?php foreach ($reviews as $review): ?>
                                <div class="border-b pb-6 last:border-b-0 last:pb-0">
                                    <div class="flex items-center mb-2">
                                        <img class="h-10 w-10 rounded-full object-cover mr-4"
                                            src="../<?= $review['user_image'] ?? 'assets/images/default-avatar.png' ?>"
                                            alt="<?= htmlspecialchars($review['user_name']) ?>">
                                        <div>
                                            <div class="font-medium"><?= htmlspecialchars($review['user_name']) ?></div>
                                            <div class="flex items-center">
                                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                                    <i class="fas fa-star <?= $i <= $review['rating'] ? 'text-yellow-400' : 'text-gray-300' ?>"></i>
                                                <?php endfor; ?>
                                            </div>
                                        </div>
                                        <div class="ml-auto text-sm text-gray-500">
                                            <?= formatDate($review['created_at']) ?>
                                        </div>
                                    </div>
                                    <p class="text-gray-600"><?= nl2br(htmlspecialchars($review['comment'])) ?></p>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Requirements and Target Audience -->
            <div>
                <div class="bg-white rounded-lg shadow-sm p-6 mb-8">
                    <h2 class="text-xl font-bold mb-4">Requirements</h2>
                    <ul class="list-disc list-inside space-y-2 text-gray-600">
                        <?php foreach (json_decode($courseData['requirements'], true) as $requirement): ?>
                            <li><?= htmlspecialchars($requirement) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>

                <div class="bg-white rounded-lg shadow-sm p-6">
                    <h2 class="text-xl font-bold mb-4">Who this course is for:</h2>
                    <ul class="list-disc list-inside space-y-2 text-gray-600">
                        <?php foreach (json_decode($courseData['target_audience'], true) as $audience): ?>
                            <li><?= htmlspecialchars($audience) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>