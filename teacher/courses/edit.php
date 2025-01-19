<?php
session_start();
require_once '../../config/database.php';
require_once '../../classes/Auth.php';
require_once '../../classes/Course.php';
require_once '../../classes/Category.php';

$auth = new Auth();
$auth->requireRole('teacher');

$course = new Course();
$category = new Category();

$courseId = $_GET['id'] ?? null;
if (!$courseId) {
    header('Location: view.php');
    exit;
}

$courseData = $course->getById($courseId);
if (!$courseData || $courseData['teacher_id'] != $_SESSION['user_id']) {
    header('Location: view.php');
    exit;
}

$categories = $category->getAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $uploadDir = '../../assets/images/uploads/courses/';
    $thumbnailPath = $courseData['thumbnail'];

    if (isset($_FILES['thumbnail']) && $_FILES['thumbnail']['error'] === UPLOAD_ERR_OK) {
        if ($thumbnailPath && file_exists('../../' . $thumbnailPath)) {
            unlink('../../' . $thumbnailPath);
        }

        $fileExtension = pathinfo($_FILES['thumbnail']['name'], PATHINFO_EXTENSION);
        $fileName = uniqid('course_') . '.' . $fileExtension;
        $thumbnailPath = 'assets/images/uploads/courses/' . $fileName;

        if (!move_uploaded_file($_FILES['thumbnail']['tmp_name'], $uploadDir . $fileName)) {
            $error = "Error uploading thumbnail";
        }
    }

    $updateData = [
        'category_id' => $_POST['category_id'],
        'title' => $_POST['title'],
        'description' => $_POST['description'],
        'price' => $_POST['price'],
        'level' => $_POST['level'],
        'status' => $_POST['status'],
        'thumbnail' => $thumbnailPath
    ];

    if ($course->update($courseId, $updateData)) {
        $success = "Course updated successfully";
        $courseData = $course->getById($courseId);
    } else {
        $error = "Error updating course";
    }
}

require_once 'teachercourseHeader.php';
?>

<div class="min-h-screen bg-gray-100">
    <div class="py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between">
                <h1 class="text-3xl font-bold text-gray-900">Edit Course</h1>
                <div class="flex space-x-3">
                    <a href="view.php" class="text-indigo-600 hover:text-indigo-900">
                        <i class="fas fa-arrow-left"></i> Back to Courses
                    </a>
                    <a href="../../courses/view.php?id=<?= $courseId ?>" target="_blank" class="text-green-600 hover:text-green-900">
                        <i class="fas fa-eye"></i> Preview Course
                    </a>
                </div>
            </div>

            <?php if (isset($success)): ?>
                <div class="mt-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
                    <span class="block sm:inline"><?= htmlspecialchars($success) ?></span>
                </div>
            <?php endif; ?>

            <?php if (isset($error)): ?>
                <div class="mt-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
                    <span class="block sm:inline"><?= htmlspecialchars($error) ?></span>
                </div>
            <?php endif; ?>

            <div class="mt-6 bg-white shadow px-4 py-5 sm:rounded-lg sm:p-6">
                <form action="edit.php?id=<?= $courseId ?>" method="POST" enctype="multipart/form-data">
                    <div class="space-y-6">
                        <div>
                            <label for="title" class="block text-sm font-medium text-gray-700">Course Title</label>
                            <input type="text" name="title" id="title" required value="<?= htmlspecialchars($courseData['title']) ?>"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        </div>

                        <div>
                            <label for="category_id" class="block text-sm font-medium text-gray-700">Category</label>
                            <select name="category_id" id="category_id" required
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <?php foreach ($categories as $cat): ?>
                                    <option value="<?= $cat['id'] ?>" <?= $cat['id'] == $courseData['category_id'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($cat['name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div>
                            <label for="description" class="block text-sm font-medium text-gray-700">Description</label>
                            <textarea name="description" id="description" rows="4" required
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"><?= htmlspecialchars($courseData['description']) ?></textarea>
                        </div>

                        <div class="grid grid-cols-1 gap-6 sm:grid-cols-3">
                            <div>
                                <label for="price" class="block text-sm font-medium text-gray-700">Price ($)</label>
                                <input type="number" name="price" id="price" min="0" step="0.01" required value="<?= $courseData['price'] ?>"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            </div>

                            <div>
                                <label for="level" class="block text-sm font-medium text-gray-700">Level</label>
                                <select name="level" id="level" required
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    <option value="beginner" <?= $courseData['level'] === 'beginner' ? 'selected' : '' ?>>Beginner</option>
                                    <option value="intermediate" <?= $courseData['level'] === 'intermediate' ? 'selected' : '' ?>>Intermediate</option>
                                    <option value="advanced" <?= $courseData['level'] === 'advanced' ? 'selected' : '' ?>>Advanced</option>
                                </select>
                            </div>

                            <div>
                                <label for="status" class="block text-sm font-medium text-gray-700">Status</label>
                                <select name="status" id="status" required
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    <option value="draft" <?= $courseData['status'] === 'draft' ? 'selected' : '' ?>>Draft</option>
                                    <option value="published" <?= $courseData['status'] === 'published' ? 'selected' : '' ?>>Published</option>
                                </select>
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Course Thumbnail</label>
                            <?php if ($courseData['thumbnail']): ?>
                                <div class="mt-2">
                                    <img src="../../assets/images/uploads/courses/<?= htmlspecialchars($courseData['thumbnail']) ?>"
                                        alt="Current thumbnail"
                                        class="h-32 w-32 object-cover rounded-md">
                                </div>
                            <?php endif; ?>
                            <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-md">
                                <div class="space-y-1 text-center">
                                    <div class="flex text-sm text-gray-600">
                                        <label for="thumbnail" class="relative cursor-pointer bg-white rounded-md font-medium text-indigo-600 hover:text-indigo-500 focus-within:outline-none focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-indigo-500">
                                            <span>Upload a new thumbnail</span>
                                            <input id="thumbnail" name="thumbnail" type="file" class="sr-only" accept="image/*">
                                        </label>
                                        <p class="pl-1">or drag and drop</p>
                                    </div>
                                    <p class="text-xs text-gray-500">PNG, JPG, GIF up to 10MB</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mt-6 flex justify-end space-x-3">
                        <a href="view.php" class="bg-white py-2 px-4 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            Cancel
                        </a>
                        <button type="submit" class="bg-indigo-600 py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            Update Course
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
    }
</script>

<?php require_once '../../includes/footer.php'; ?>