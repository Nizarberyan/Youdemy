<?php
session_start();
require_once '../config/database.php';
require_once '../classes/Auth.php';
require_once '../classes/Course.php';
require_once '../classes/Category.php';

$auth = new Auth();
$auth->requireRole('admin');

$course = new Course();
$category = new Category();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $courseId = $_POST['course_id'] ?? null;
    $newStatus = $_POST['status'] ?? '';

    if ($courseId && $action === 'change_status' && $newStatus) {
        $result = $course->update($courseId, ['status' => $newStatus]);
        if ($result) {
            $_SESSION['success'] = "Course status updated successfully";
        } else {
            $_SESSION['error'] = "Failed to update course status";
        }
        header('Location: courses.php');
        exit;
    }
}

$categoryId = $_GET['category'] ?? '';
$status = $_GET['status'] ?? '';
$search = $_GET['search'] ?? '';
$page = $_GET['page'] ?? 1;
$limit = 10;
$offset = ($page - 1) * $limit;

$filters = [
    'category_id' => $categoryId,
    'status' => $status,
    'search' => $search
];

$courses = $course->getAll($filters, $limit, $offset);
$totalCourses = $course->countAll($filters);
$totalPages = ceil($totalCourses / $limit);
$categories = $category->getAll();

require_once 'adminHeader.php';
?>


<?php if (isset($_SESSION['success'])): ?>
    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
        <?= $_SESSION['success'] ?>
        <?php unset($_SESSION['success']); ?>
    </div>
<?php endif; ?>

<?php if (isset($_SESSION['error'])): ?>
    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
        <?= $_SESSION['error'] ?>
        <?php unset($_SESSION['error']); ?>
    </div>
<?php endif; ?>

<div class="min-h-screen bg-gray-100">
    <div class="py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center">
                <h1 class="text-3xl font-bold text-gray-900">Course Management</h1>
                <div class="flex space-x-4">
                    <select id="statusFilter" onchange="applyFilters()" class="rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        <option value="">All Status</option>
                        <option value="draft" <?= $status === 'draft' ? 'selected' : '' ?>>Draft</option>
                        <option value="published" <?= $status === 'published' ? 'selected' : '' ?>>Published</option>
                        <option value="archived" <?= $status === 'archived' ? 'selected' : '' ?>>Archived</option>
                    </select>
                </div>
            </div>

            <div class="mt-8">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Course</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Teacher</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php foreach ($courses as $course): ?>
                            <tr>
                                <td class="px-6 py-4">
                                    <div class="flex items-center">
                                        <div class="h-10 w-10 flex-shrink-0">
                                            <img class="h-10 w-10 rounded object-cover"
                                                src="../assets/images/uploads/courses/<?= $course['thumbnail'] ?? '../assets/images/uploads/courses/course-default.png' ?>"
                                                alt="">
                                        </div>
                                        <div class="ml-4">
                                            <div class="text-sm font-medium text-gray-900">
                                                <?= htmlspecialchars($course['title']) ?>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <?= htmlspecialchars($course['first_name'] . ' ' . $course['last_name']) ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                        <?php
                                        echo match ($course['status']) {
                                            'published' => 'bg-green-100 text-green-800',
                                            'draft' => 'bg-yellow-100 text-yellow-800',
                                            'archived' => 'bg-gray-100 text-gray-800',
                                            default => 'bg-gray-100 text-gray-800'
                                        }
                                        ?>">
                                        <?= ucfirst($course['status']) ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <div class="flex items-center space-x-4">
                                        <select onchange="changeStatus(this, <?= $course['id'] ?>)"
                                            class="text-sm rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                            <option value="">Change Status</option>
                                            <option value="draft">Set as Draft</option>
                                            <option value="published">Set as Published</option>
                                            <option value="archived">Set as Archived</option>
                                        </select>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <?php if ($totalPages > 1): ?>
                <!-- Pagination code here -->
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
    function changeStatus(selectElement, courseId) {
        const newStatus = selectElement.value;
        if (!newStatus) return;

        if (confirm(`Are you sure you want to change the status to ${newStatus}?`)) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.style.display = 'none';

            const actionInput = document.createElement('input');
            actionInput.type = 'hidden';
            actionInput.name = 'action';
            actionInput.value = 'change_status';

            const courseIdInput = document.createElement('input');
            courseIdInput.type = 'hidden';
            courseIdInput.name = 'course_id';
            courseIdInput.value = courseId;

            const statusInput = document.createElement('input');
            statusInput.type = 'hidden';
            statusInput.name = 'status';
            statusInput.value = newStatus;

            form.appendChild(actionInput);
            form.appendChild(courseIdInput);
            form.appendChild(statusInput);

            document.body.appendChild(form);
            form.submit();
        } else {
            selectElement.value = ''; // Reset the select if user cancels
        }
    }

    function applyFilters() {
        const status = document.getElementById('statusFilter').value;
        window.location.href = `courses.php?status=${status}`;
    }
</script>

<?php require_once '../includes/footer.php'; ?>