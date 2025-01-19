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
        'duration' => $_POST['duration'] ?? null,
        'sort_order' => $_POST['sort_order'] ?? 0
    ];

    // Handle different content types
    switch ($_POST['content_type']) {
        case 'video':
            $lessonData['content'] = $_POST['video_url'];
            break;
        case 'text':
            $lessonData['content'] = $_POST['text_content'];
            break;
        case 'pdf':
            if (isset($_FILES['pdf_file']) && $_FILES['pdf_file']['error'] === UPLOAD_ERR_OK) {
                $uploadDir = '../../assets/uploads/lessons/';
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0777, true);
                }

                $fileExtension = strtolower(pathinfo($_FILES['pdf_file']['name'], PATHINFO_EXTENSION));
                if ($fileExtension !== 'pdf') {
                    $error = "Only PDF files are allowed";
                    break;
                }

                $fileName = uniqid('lesson_') . '.pdf';
                $filePath = 'assets/uploads/lessons/' . $fileName;

                if (move_uploaded_file($_FILES['pdf_file']['tmp_name'], $uploadDir . $fileName)) {
                    $lessonData['content'] = $filePath;
                } else {
                    $error = "Error uploading PDF file";
                }
            } else {
                $error = "Please select a PDF file";
            }
            break;
    }

    if (!isset($error)) {
        if ($course->addLesson($sectionId, $lessonData)) {
            $_SESSION['success'] = "Lesson added successfully";
            header('Location: edit.php?id=' . $_GET['course_id']);
            exit;
        } else {
            $error = "Error adding lesson";
        }
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
                <form action="add_lesson.php?section_id=<?= $sectionId ?>&course_id=<?= $_GET['course_id'] ?>" method="POST" enctype="multipart/form-data">
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
                                <option value="pdf">PDF Document</option>
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
                            <textarea name="text_content" id="text_content_input" rows="10"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"></textarea>
                        </div>

                        <div id="pdf_content" style="display: none;">
                            <label for="pdf_file" class="block text-sm font-medium text-gray-700">PDF Document</label>
                            <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-md">
                                <div class="space-y-1 text-center">
                                    <div class="flex text-sm text-gray-600">
                                        <label for="pdf_file" class="relative cursor-pointer bg-white rounded-md font-medium text-indigo-600 hover:text-indigo-500 focus-within:outline-none focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-indigo-500">
                                            <span>Upload a file</span>
                                            <input id="pdf_file" name="pdf_file" type="file" class="sr-only" accept=".pdf">
                                        </label>
                                        <p class="pl-1">or drag and drop</p>
                                    </div>
                                    <p class="text-xs text-gray-500">PDF up to 10MB</p>
                                </div>
                            </div>
                        </div>

                        <div>
                            <label for="duration" class="block text-sm font-medium text-gray-700">Duration (optional)</label>
                            <input type="text" name="duration" id="duration"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                placeholder="e.g., 5:30">
                            <p class="mt-1 text-sm text-gray-500">Format: MM:SS (for videos) or estimated reading time</p>
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
        const pdfContent = document.getElementById('pdf_content');
        const videoUrl = document.getElementById('video_url');
        const textInput = document.getElementById('text_content_input');
        const pdfFile = document.getElementById('pdf_file');

        // Hide all content divs first
        videoContent.style.display = 'none';
        textContent.style.display = 'none';
        pdfContent.style.display = 'none';

        // Reset required attributes
        videoUrl.required = false;
        textInput.required = false;
        pdfFile.required = false;

        // Show and set required for selected content type
        switch (contentType) {
            case 'video':
                videoContent.style.display = 'block';
                videoUrl.required = true;
                break;
            case 'text':
                textContent.style.display = 'block';
                textInput.required = true;
                break;
            case 'pdf':
                pdfContent.style.display = 'block';
                pdfFile.required = true;
                break;
        }
    }

    // Initialize the form
    document.addEventListener('DOMContentLoaded', toggleContentFields);

    // Add drag and drop functionality for PDF
    const pdfDropZone = document.getElementById('pdf_content');
    const pdfInput = document.getElementById('pdf_file');

    ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
        pdfDropZone.addEventListener(eventName, preventDefaults, false);
    });

    function preventDefaults(e) {
        e.preventDefault();
        e.stopPropagation();
    }

    ['dragenter', 'dragover'].forEach(eventName => {
        pdfDropZone.addEventListener(eventName, highlight, false);
    });

    ['dragleave', 'drop'].forEach(eventName => {
        pdfDropZone.addEventListener(eventName, unhighlight, false);
    });

    function highlight(e) {
        pdfDropZone.classList.add('border-indigo-500');
    }

    function unhighlight(e) {
        pdfDropZone.classList.remove('border-indigo-500');
    }

    pdfDropZone.addEventListener('drop', handleDrop, false);

    function handleDrop(e) {
        const dt = e.dataTransfer;
        const files = dt.files;
        pdfInput.files = files;
    }
</script>

<?php require_once '../../includes/footer.php'; ?>