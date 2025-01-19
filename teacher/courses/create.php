<?php
session_start();
require_once '../../config/database.php';
require_once '../../classes/Auth.php';
require_once '../../classes/Course.php';
require_once '../../classes/Category.php';
require_once '../../classes/Tags.php';

$auth = new Auth();
$auth->requireRole('teacher');

$category = new Category();
$categories = $category->getAll();

$tags = new Tags();
$allTags = $tags->getAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $course = new Course();

    $uploadDir = '../../assets/images/uploads/courses/';
    $thumbnailPath = null;

    if (isset($_FILES['thumbnail']) && $_FILES['thumbnail']['error'] === UPLOAD_ERR_OK) {
        $fileExtension = pathinfo($_FILES['thumbnail']['name'], PATHINFO_EXTENSION);
        $fileName = uniqid('course_') . '.' . $fileExtension;
        $thumbnailPath = 'assets/images/uploads/courses/' . $fileName;

        if (!move_uploaded_file($_FILES['thumbnail']['tmp_name'], $uploadDir . $fileName)) {
            $error = "Error uploading thumbnail";
        }
    }

    $contentPaths = [];

    if (isset($_POST['content_urls']) && is_array($_POST['content_urls'])) {
        foreach ($_POST['content_urls'] as $url) {
            if (!empty(trim($url))) {
                $contentPaths[] = trim($url);
            }
        }
    }

    $preview_url = $_POST['preview_url'] ?? null;

    $courseData = [
        'teacher_id' => $_SESSION['user_id'],
        'category_id' => $_POST['category_id'],
        'title' => $_POST['title'],
        'description' => $_POST['description'],
        'price' => $_POST['price'],
        'level' => $_POST['level'],
        'status' => 'draft',
        'thumbnail' => $thumbnailPath,
        'content_paths' => json_encode($contentPaths),
        'preview_url' => $preview_url
    ];

    $courseId = $course->create($courseData);

    if ($courseId) {
        if (isset($_POST['tags']) && is_array($_POST['tags'])) {
            foreach ($_POST['tags'] as $tagId) {
                $course->addTag($courseId, $tagId);
            }
        }

        header('Location: edit.php?id=' . $courseId);
        exit;
    } else {
        $error = "Error creating course";
    }
}

require_once 'teachercourseHeader.php';
?>

<div class="min-h-screen bg-gray-100">
    <div class="py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between">
                <h1 class="text-3xl font-bold text-gray-900">Create New Course</h1>
                <a href="view.php" class="text-indigo-600 hover:text-indigo-900">
                    <i class="fas fa-arrow-left"></i> Back to Courses
                </a>
            </div>

            <?php if (isset($error)): ?>
                <div class="mt-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
                    <span class="block sm:inline"><?= htmlspecialchars($error) ?></span>
                </div>
            <?php endif; ?>

            <div class="mt-6 bg-white shadow px-4 py-5 sm:rounded-lg sm:p-6">
                <form action="create.php" method="POST" enctype="multipart/form-data">
                    <div class="space-y-6">
                        <div>
                            <label for="title" class="block text-sm font-medium text-gray-700">Course Title</label>
                            <input type="text" name="title" id="title" required
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        </div>

                        <div>
                            <label for="category_id" class="block text-sm font-medium text-gray-700">Category</label>
                            <select name="category_id" id="category_id" required
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <option value="">Select a category</option>
                                <?php foreach ($categories as $cat): ?>
                                    <option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div>
                            <label for="description" class="block text-sm font-medium text-gray-700">Description</label>
                            <textarea name="description" id="description" rows="4" required
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"></textarea>
                        </div>

                        <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                            <div>
                                <label for="price" class="block text-sm font-medium text-gray-700">Price ($)</label>
                                <input type="number" name="price" id="price" min="0" step="0.01" required
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            </div>

                            <div>
                                <label for="level" class="block text-sm font-medium text-gray-700">Level</label>
                                <select name="level" id="level" required
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    <option value="beginner">Beginner</option>
                                    <option value="intermediate">Intermediate</option>
                                    <option value="advanced">Advanced</option>
                                </select>
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Course Thumbnail</label>
                            <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-md">
                                <div class="space-y-1 text-center">
                                    <div class="flex text-sm text-gray-600">
                                        <label for="thumbnail" class="relative cursor-pointer bg-white rounded-md font-medium text-indigo-600 hover:text-indigo-500 focus-within:outline-none focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-indigo-500">
                                            <span>Upload a file</span>
                                            <input id="thumbnail" name="thumbnail" type="file" class="sr-only" accept="image/*" required>
                                        </label>
                                        <p class="pl-1">or drag and drop</p>
                                    </div>
                                    <p class="text-xs text-gray-500">PNG, JPG, GIF up to 10MB</p>
                                </div>
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Course Content URL</label>
                            <p class="text-sm text-gray-500 mb-2">Add the URL for your course content (YouTube video, PDF document, etc.)</p>
                            <div id="content-urls-container">
                                <div class="mb-2">
                                    <input type="url" name="content_urls[]"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                        placeholder="https://example.com/content">
                                </div>
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Course Tags</label>
                            <p class="text-sm text-gray-500 mb-2">Select tags that best describe your course content</p>
                            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
                                <?php foreach ($allTags as $tag): ?>
                                    <div class="flex items-center">
                                        <input type="checkbox" name="tags[]" value="<?= $tag['id'] ?>"
                                            class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded">
                                        <label class="ml-2 text-sm text-gray-700">
                                            <?= htmlspecialchars($tag['name']) ?>
                                        </label>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label for="preview_url" class="block text-gray-700 text-sm font-bold mb-2">
                                Course Preview URL (Optional)
                            </label>
                            <input
                                type="url"
                                id="preview_url"
                                name="preview_url"
                                placeholder="Enter YouTube or Vimeo URL"
                                class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                                value="<?= htmlspecialchars($courseData['preview_url'] ?? '') ?>">
                            <p class="text-gray-600 text-xs mt-1">
                                Add a YouTube or Vimeo URL for a course preview video
                            </p>
                        </div>
                    </div>

                    <div class="mt-6 flex justify-end space-x-3">
                        <a href="index.php" class="bg-white py-2 px-4 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            Cancel
                        </a>
                        <button type="submit" class="bg-indigo-600 py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            Create Course
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    const thumbnailInput = document.getElementById('thumbnail');
    const dropZone = thumbnailInput.closest('div.border-dashed');
    const fileNameDisplay = document.createElement('p');
    fileNameDisplay.className = 'mt-2 text-sm text-gray-600';
    dropZone.appendChild(fileNameDisplay);

    thumbnailInput.addEventListener('change', function(e) {
        const fileName = e.target.files[0]?.name;
        fileNameDisplay.textContent = fileName ? `Selected file: ${fileName}` : '';
    });

    ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
        dropZone.addEventListener(eventName, preventDefaults, false);
    });

    function preventDefaults(e) {
        e.preventDefault();
        e.stopPropagation();
    }

    ['dragenter', 'dragover'].forEach(eventName => {
        dropZone.addEventListener(eventName, highlight, false);
    });

    ['dragleave', 'drop'].forEach(eventName => {
        dropZone.addEventListener(eventName, unhighlight, false);
    });

    function highlight(e) {
        dropZone.classList.add('border-indigo-500');
    }

    function unhighlight(e) {
        dropZone.classList.remove('border-indigo-500');
    }

    dropZone.addEventListener('drop', handleDrop, false);

    function handleDrop(e) {
        const dt = e.dataTransfer;
        const files = dt.files;
        thumbnailInput.files = files;
        const fileName = files[0]?.name;
        fileNameDisplay.textContent = fileName ? `Selected file: ${fileName}` : '';
    }

    // Add URL field functionality
    const addUrlBtn = document.getElementById('add-url-btn');
    const contentUrlsContainer = document.getElementById('content-urls-container');

    // Remove URL field functionality since we only want one URL
</script>

<?php require_once '../../includes/footer.php'; ?>