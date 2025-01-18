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

$teacherId = $_SESSION['user_id'];
$status = $_GET['status'] ?? '';
$search = $_GET['search'] ?? '';
$page = $_GET['page'] ?? 1;
$limit = 10;
$offset = ($page - 1) * $limit;

$filters = [
    'teacher_id' => $teacherId,
    'status' => $status,
    'search' => $search
];

$courses = $course->getAll($filters, $limit, $offset);
$totalCourses = $course->countAll($filters);
$totalPages = ceil($totalCourses / $limit);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');

    $action = $_POST['action'] ?? '';
    $courseId = $_POST['course_id'] ?? null;

    if ($courseId && $action) {
        $courseData = $course->getById($courseId);
        if ($courseData['teacher_id'] != $teacherId) {
            echo json_encode(['success' => false, 'message' => 'Unauthorized']);
            exit;
        }

        switch ($action) {
            case 'publish':
                $result = $course->update($courseId, ['status' => 'published']);
                break;
            case 'unpublish':
                $result = $course->update($courseId, ['status' => 'draft']);
                break;
            case 'delete':
                $result = $course->delete($courseId, $teacherId);
                break;
            default:
                $result = false;
        }

        echo json_encode(['success' => $result]);
        exit;
    }
}

require_once '../../includes/header.php';
?>

<div class="min-h-screen bg-gray-100">
    <div class="py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center">
                <h1 class="text-3xl font-bold text-gray-900">My Courses</h1>
                <a href="create.php" class="bg-indigo-600 text-white px-4 py-2 rounded-md hover:bg-indigo-700">
                    Create New Course
                </a>
            </div>

            <div class="mt-4 flex justify-between items-center">
                <div class="flex space-x-4">
                    <select id="statusFilter" onchange="applyFilters()" class="rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        <option value="">All Status</option>
                        <option value="draft" <?= $status === 'draft' ? 'selected' : '' ?>>Draft</option>
                        <option value="published" <?= $status === 'published' ? 'selected' : '' ?>>Published</option>
                    </select>
                </div>
                <div class="flex-1 max-w-md ml-4">
                    <input type="text" id="searchInput" placeholder="Search courses..." value="<?= htmlspecialchars($search) ?>"
                        class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                </div>
            </div>

            <div class="mt-8 flex flex-col">
                <div class="-my-2 -mx-4 overflow-x-auto sm:-mx-6 lg:-mx-8">
                    <div class="inline-block min-w-full py-2 align-middle md:px-6 lg:px-8">
                        <div class="overflow-hidden shadow ring-1 ring-black ring-opacity-5 md:rounded-lg">
                            <table class="min-w-full divide-y divide-gray-300">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Course</th>
                                        <th class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Category</th>
                                        <th class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Price</th>
                                        <th class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Status</th>
                                        <th class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Students</th>
                                        <th class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200 bg-white">
                                    <?php foreach ($courses as $courseData): ?>
                                        <tr>
                                            <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-900">
                                                <div class="flex items-center">
                                                    <div class="h-10 w-10 flex-shrink-0">
                                                        <img class="h-10 w-10 rounded object-cover"
                                                            src="../../<?= $courseData['thumbnail'] ?? 'assets/images/course-default.png' ?>"
                                                            alt="">
                                                    </div>
                                                    <div class="ml-4">
                                                        <div class="font-medium"><?= htmlspecialchars($courseData['title']) ?></div>
                                                        <div class="text-gray-500"><?= htmlspecialchars(substr($courseData['description'], 0, 50)) ?>...</div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">
                                                <?= htmlspecialchars($courseData['category_name']) ?>
                                            </td>
                                            <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">
                                                $<?= number_format($courseData['price'], 2) ?>
                                            </td>
                                            <td class="whitespace-nowrap px-3 py-4 text-sm">
                                                <span class="inline-flex rounded-full px-2 text-xs font-semibold leading-5 
                                                <?= $courseData['status'] === 'published' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' ?>">
                                                    <?= ucfirst($courseData['status']) ?>
                                                </span>
                                            </td>
                                            <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">
                                                <?= $courseData['enrollment_count'] ?? 0 ?>
                                            </td>
                                            <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">
                                                <div class="flex space-x-3">
                                                    <a href="edit.php?id=<?= $courseData['id'] ?>" class="text-indigo-600 hover:text-indigo-900">
                                                        Edit
                                                    </a>
                                                    <?php if ($courseData['status'] === 'draft'): ?>
                                                        <button onclick="handleAction('publish', <?= $courseData['id'] ?>)"
                                                            class="text-green-600 hover:text-green-900">Publish</button>
                                                    <?php else: ?>
                                                        <button onclick="handleAction('unpublish', <?= $courseData['id'] ?>)"
                                                            class="text-yellow-600 hover:text-yellow-900">Unpublish</button>
                                                    <?php endif; ?>
                                                    <button onclick="handleAction('delete', <?= $courseData['id'] ?>)"
                                                        class="text-red-600 hover:text-red-900">Delete</button>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <?php if ($totalPages > 1): ?>
                <div class="mt-4 flex items-center justify-between">
                    <div class="flex justify-between sm:hidden">
                        <?php if ($page > 1): ?>
                            <a href="?page=<?= $page - 1 ?>&status=<?= $status ?>&search=<?= urlencode($search) ?>"
                                class="relative inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">Previous</a>
                        <?php endif; ?>
                        <?php if ($page < $totalPages): ?>
                            <a href="?page=<?= $page + 1 ?>&status=<?= $status ?>&search=<?= urlencode($search) ?>"
                                class="relative ml-3 inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">Next</a>
                        <?php endif; ?>
                    </div>
                    <div class="hidden sm:flex sm:flex-1 sm:items-center sm:justify-between">
                        <div>
                            <p class="text-sm text-gray-700">
                                Showing page <span class="font-medium"><?= $page ?></span> of <span class="font-medium"><?= $totalPages ?></span>
                            </p>
                        </div>
                        <div>
                            <nav class="isolate inline-flex -space-x-px rounded-md shadow-sm" aria-label="Pagination">
                                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                                    <a href="?page=<?= $i ?>&status=<?= $status ?>&search=<?= urlencode($search) ?>"
                                        class="relative inline-flex items-center px-4 py-2 text-sm font-semibold 
                                      <?= $i === $page ? 'bg-indigo-600 text-white focus-visible:outline-indigo-600' : 'text-gray-900 ring-1 ring-inset ring-gray-300 hover:bg-gray-50 focus:outline-offset-0' ?>">
                                        <?= $i ?>
                                    </a>
                                <?php endfor; ?>
                            </nav>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
    let searchTimeout;

    document.getElementById('searchInput').addEventListener('input', function(e) {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(() => applyFilters(), 500);
    });

    function handleAction(action, courseId) {
        const confirmMessages = {
            publish: 'Are you sure you want to publish this course?',
            unpublish: 'Are you sure you want to unpublish this course?',
            delete: 'Are you sure you want to delete this course? This action cannot be undone.'
        };

        if (confirm(confirmMessages[action])) {
            fetch('list.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        action: action,
                        course_id: courseId
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        location.reload();
                    } else {
                        alert('Error performing action');
                    }
                });
        }
    }

    function applyFilters() {
        const status = document.getElementById('statusFilter').value;
        const search = document.getElementById('searchInput').value;
        window.location.href = `list.php?status=${status}&search=${encodeURIComponent(search)}`;
    }
</script>

<?php require_once '../../includes/footer.php'; ?>