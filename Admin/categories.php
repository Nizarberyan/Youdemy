<?php
require_once '../config/database.php';
require_once '../classes/Auth.php';
require_once '../classes/Category.php';

$auth = new Auth();
$auth->requireRole('admin');

$category = new Category();

$action = $_GET['action'] ?? '';
$id = $_GET['id'] ?? null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');

    if ($action === 'create' || $action === 'update') {
        $data = json_decode(file_get_contents('php://input'), true);

        if ($action === 'create') {
            $result = $category->create($data);
            echo json_encode(['success' => $result !== false, 'id' => $result]);
        } else {
            $result = $category->update($id, $data);
            echo json_encode(['success' => $result]);
        }
        exit;
    }

    if ($action === 'delete') {
        $result = $category->delete($id);
        echo json_encode(['success' => $result]);
        exit;
    }
}

$categories = $category->getAll();
$categoryStats = $category->getCategoryStats();

require_once '../includes/header.php';
?>

<div class="min-h-screen bg-gray-100">
    <div class="py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center">
                <h1 class="text-3xl font-bold text-gray-900">Category Management</h1>
                <button onclick="openModal()" class="bg-indigo-600 text-white px-4 py-2 rounded-md hover:bg-indigo-700">
                    Add Category
                </button>
            </div>

            <div class="mt-8 flex flex-col">
                <div class="-my-2 -mx-4 overflow-x-auto sm:-mx-6 lg:-mx-8">
                    <div class="inline-block min-w-full py-2 align-middle md:px-6 lg:px-8">
                        <div class="overflow-hidden shadow ring-1 ring-black ring-opacity-5 md:rounded-lg">
                            <table class="min-w-full divide-y divide-gray-300">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Category</th>
                                        <th class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Description</th>
                                        <th class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Courses</th>
                                        <th class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Students</th>
                                        <th class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200 bg-white">
                                    <?php foreach ($categories as $cat): ?>
                                        <tr>
                                            <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-900">
                                                <div class="flex items-center">
                                                    <div class="h-8 w-8 flex-shrink-0 text-gray-500">
                                                        <i class="fas <?= htmlspecialchars($cat['icon']) ?>"></i>
                                                    </div>
                                                    <div class="ml-4">
                                                        <?= htmlspecialchars($cat['name']) ?>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="px-3 py-4 text-sm text-gray-500">
                                                <?= htmlspecialchars($cat['description']) ?>
                                            </td>
                                            <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">
                                                <?= $cat['course_count'] ?>
                                            </td>
                                            <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">
                                                <?= $categoryStats[$cat['id']]['total_students'] ?? 0 ?>
                                            </td>
                                            <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">
                                                <div class="flex space-x-2">
                                                    <button onclick="editCategory(<?= htmlspecialchars(json_encode($cat)) ?>)"
                                                        class="text-indigo-600 hover:text-indigo-900">Edit</button>
                                                    <button onclick="deleteCategory(<?= $cat['id'] ?>)"
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
        </div>
    </div>
</div>

<div id="categoryModal" class="fixed inset-0 bg-gray-500 bg-opacity-75 hidden">
    <div class="flex min-h-full items-center justify-center">
        <div class="bg-white rounded-lg px-4 pt-5 pb-4 text-left shadow-xl sm:my-8 sm:w-full sm:max-w-lg sm:p-6">
            <div class="mt-3 text-center sm:mt-0 sm:text-left">
                <h3 class="text-lg font-medium leading-6 text-gray-900" id="modalTitle">Add Category</h3>
                <div class="mt-4">
                    <form id="categoryForm" onsubmit="handleSubmit(event)">
                        <input type="hidden" id="categoryId">
                        <div class="space-y-4">
                            <div>
                                <label for="name" class="block text-sm font-medium text-gray-700">Name</label>
                                <input type="text" id="name" required
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            </div>
                            <div>
                                <label for="description" class="block text-sm font-medium text-gray-700">Description</label>
                                <textarea id="description" rows="3" required
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"></textarea>
                            </div>
                            <div>
                                <label for="icon" class="block text-sm font-medium text-gray-700">Icon (FontAwesome class)</label>
                                <input type="text" id="icon" required placeholder="fa-book"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            </div>
                        </div>
                        <div class="mt-5 sm:mt-6 sm:grid sm:grid-flow-row-dense sm:grid-cols-2 sm:gap-3">
                            <button type="submit"
                                class="inline-flex w-full justify-center rounded-md border border-transparent bg-indigo-600 px-4 py-2 text-base font-medium text-white shadow-sm hover:bg-indigo-700 sm:col-start-2">
                                Save
                            </button>
                            <button type="button" onclick="closeModal()"
                                class="mt-3 inline-flex w-full justify-center rounded-md border border-gray-300 bg-white px-4 py-2 text-base font-medium text-gray-700 shadow-sm hover:bg-gray-50 sm:col-start-1 sm:mt-0">
                                Cancel
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    function openModal() {
        document.getElementById('modalTitle').textContent = 'Add Category';
        document.getElementById('categoryId').value = '';
        document.getElementById('categoryForm').reset();
        document.getElementById('categoryModal').classList.remove('hidden');
    }

    function closeModal() {
        document.getElementById('categoryModal').classList.add('hidden');
    }

    function editCategory(category) {
        document.getElementById('modalTitle').textContent = 'Edit Category';
        document.getElementById('categoryId').value = category.id;
        document.getElementById('name').value = category.name;
        document.getElementById('description').value = category.description;
        document.getElementById('icon').value = category.icon;
        document.getElementById('categoryModal').classList.remove('hidden');
    }

    function deleteCategory(categoryId) {
        if (confirm('Are you sure you want to delete this category? This action cannot be undone.')) {
            fetch(`categories.php?action=delete&id=${categoryId}`, {
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
                        alert('Error deleting category. It may have associated courses.');
                    }
                });
        }
    }

    async function handleSubmit(event) {
        event.preventDefault();

        const categoryId = document.getElementById('categoryId').value;
        const data = {
            name: document.getElementById('name').value,
            description: document.getElementById('description').value,
            icon: document.getElementById('icon').value
        };

        const action = categoryId ? 'update' : 'create';
        const url = `categories.php?action=${action}${categoryId ? '&id=' + categoryId : ''}`;

        try {
            const response = await fetch(url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(data)
            });

            const result = await response.json();
            if (result.success) {
                location.reload();
            } else {
                alert('Error saving category');
            }
        } catch (error) {
            alert('Error saving category');
        }
    }

    document.addEventListener('keydown', function(event) {
        if (event.key === 'Escape') {
            closeModal();
        }
    });
</script>

<?php require_once '../includes/footer.php'; ?>