<?php
require_once '../../config/database.php';
require_once '../../classes/Auth.php';
require_once '../../classes/Course.php';

$auth = new Auth();
$auth->requireRole('teacher');

$course = new Course();

$sectionId = $_GET['section_id'] ?? null;
if (!$sectionId) {
    header('Location: view.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $lessonData = [
        'title' => $_POST['title'],
        'content_type' => $_POST['content_type'],
        'content' => $_POST['content_type'] === 'video' ? $_POST['video_url'] : $_POST['text_content'],
        'duration' => $_POST['duration'] ?? null,
        'sort_order' => $_POST['sort_order'] ?? 0
    ];

    if ($course->addLesson($sectionId, $lessonData)) {
        $_SESSION['success'] = "Lesson added successfully";
        header('Location: edit.php?id=' . $_GET['course_id']);
        exit;
    } else {
        $error = "Error adding lesson";
    }
}

require_once '../../includes/header.php';
?>

<div class="min-h-screen bg-gray-100">
    <div class="py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between">
                <h1 class="text-3xl font-bold text-gray-900">Add New Lesson</h1>
                <a href="edit.php?id=<?= $_GET['course_id'] ?>" class="text-indigo-600 hover:text-indigo-900">
                    <i class="fas fa-arrow-left"></i> Back to Course
                </a>
            </div>

            <?php if (isset($error)): ?>
                <div class="mt-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
                    <span class="block sm:inline"><?= htmlspecialchars($error) ?></span>
                </div>
            <?php endif; ?>

            <div class="mt-6 bg-white shadow px-4 py-5 sm:rounded-lg sm:p-6">
                <form action="add_lesson.php?section_id=<?= $sectionId ?>&course_id=<?= $_GET['course_id'] ?>" method="POST">
                    <div class="space-y-6">
                        <div>
                            <label for="title" class="block text-sm font-medium text-gray-700">Lesson Title</label>
                            <input type="text" name="title" id="title" required
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        </div>

                        <div>
                            <label for="content_type" class="block text-sm font-medium text-gray-700">Content Type</label>
                            <select name="content_type" id="content_type" required
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                onchange="toggleContentFields()">
                                <option value="video">Video</option>
                                <option value="text">Text</option>
                            </select>
                        </div>

                        <div id="video_content">
                            <label for="video_url" class="block text-sm font-medium text-gray-700">Video URL</label>
                            <input type="url" name="video_url" id="video_url"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <p class="mt-1 text-sm text-gray-500">Enter the URL of your video (YouTube, Vimeo, etc.)</p>
                        </div>

                        <div id="text_content" style="display: none;">
                            <label for="text_content" class="block text-sm font-medium text-gray-700">Text Content</label>
                            <textarea name="text_content" id="text_content" rows="10"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"></textarea>
                        </div>

                        <div>
                            <label for="duration" class="block text-sm font-medium text-gray-700">Duration (optional)</label>
                            <input type="text" name="duration" id="duration"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                placeholder="e.g., 5:30">
                            <p class="mt-1 text-sm text-gray-500">Format: MM:SS (for videos) or estimated reading time (for text)</p>
                        </div>

                        <div>
                            <label for="sort_order" class="block text-sm font-medium text-gray-700">Sort Order</label>
                            <input type="number" name="sort_order" id="sort_order"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                value="0">
                        </div>
                    </div>

                    <div class="mt-6 flex justify-end space-x-3">
                        <a href="edit.php?id=<?= $_GET['course_id'] ?>"
                            class="bg-white py-2 px-4 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            Cancel
                        </a>
                        <button type="submit"
                            class="bg-indigo-600 py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            Add Lesson
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    function toggleContentFields() {
        const contentType = document.getElementById('content_type').value;
        const videoContent = document.getElementById('video_content');
        const textContent = document.getElementById('text_content');

        if (contentType === 'video') {
            videoContent.style.display = 'block';
            textContent.style.display = 'none';
            document.getElementById('video_url').required = true;
            document.getElementById('text_content').required = false;
        } else {
            videoContent.style.display = 'none';
            textContent.style.display = 'block';
            document.getElementById('video_url').required = false;
            document.getElementById('text_content').required = true;
        }
    }

    // Initialize the form
    document.addEventListener('DOMContentLoaded', toggleContentFields);
</script>

<?php require_once '../../includes/footer.php'; ?>