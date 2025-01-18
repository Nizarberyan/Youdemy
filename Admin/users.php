<?php
session_start();
require_once '../config/database.php';
require_once '../classes/Auth.php';
require_once '../classes/User.php';

$auth = new Auth();
$auth->requireRole('admin');

$user = new User();

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $userId = $_POST['id'] ?? '';

    if (empty($userId) || !is_numeric($userId)) {
        $_SESSION['error'] = 'Invalid user ID';
    } else {
        try {
            switch ($action) {
                case 'approve':
                    if ($user->update($userId, ['status' => 'active'])) {
                        $_SESSION['success'] = 'User approved successfully';
                    } else {
                        $_SESSION['error'] = 'Failed to approve user';
                    }
                    break;

                case 'reject':
                case 'delete':
                    if ($user->delete($userId)) {
                        $_SESSION['success'] = 'User deleted successfully';
                    } else {
                        $_SESSION['error'] = 'Failed to delete user';
                    }
                    break;

                case 'suspend':
                    if ($user->update($userId, ['status' => 'suspended'])) {
                        $_SESSION['success'] = 'User suspended successfully';
                    } else {
                        $_SESSION['error'] = 'Failed to suspend user';
                    }
                    break;
                    
                default:
                    $_SESSION['error'] = 'Invalid action';
            }
        } catch (Exception $e) {
            $_SESSION['error'] = $e->getMessage();
        }
    }
    
    header('Location: users.php');
    exit;
}

// Get filters and pagination parameters
$role = $_GET['role'] ?? '';
$status = $_GET['status'] ?? '';
$page = max(1, intval($_GET['page'] ?? 1));
$limit = 10;
$offset = ($page - 1) * $limit;

// Get users list
$users = $user->getAll($role, $status, $limit, $offset) ?? [];
$totalUsers = $user->countAll($role) ?? 0;
$totalPages = ceil($totalUsers / $limit);

require_once '../includes/header.php';
?>

<div class="min-h-screen bg-gray-100">
    <div class="py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Display Messages -->
            <?php if (isset($_SESSION['error'])): ?>
                <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
                    <span class="block sm:inline"><?= htmlspecialchars($_SESSION['error']) ?></span>
                </div>
                <?php unset($_SESSION['error']); ?>
            <?php endif; ?>

            <?php if (isset($_SESSION['success'])): ?>
                <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
                    <span class="block sm:inline"><?= htmlspecialchars($_SESSION['success']) ?></span>
                </div>
                <?php unset($_SESSION['success']); ?>
            <?php endif; ?>

            <div class="flex justify-between items-center">
                <h1 class="text-3xl font-bold text-gray-900">User Management</h1>
                <div class="flex space-x-4">
                    <select id="roleFilter" onchange="applyFilters()" class="rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        <option value="">All Roles</option>
                        <option value="student" <?= $role === 'student' ? 'selected' : '' ?>>Students</option>
                        <option value="teacher" <?= $role === 'teacher' ? 'selected' : '' ?>>Teachers</option>
                    </select>
                    <select id="statusFilter" onchange="applyFilters()" class="rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        <option value="">All Status</option>
                        <option value="active" <?= $status === 'active' ? 'selected' : '' ?>>Active</option>
                        <option value="pending" <?= $status === 'pending' ? 'selected' : '' ?>>Pending</option>
                        <option value="suspended" <?= $status === 'suspended' ? 'selected' : '' ?>>Suspended</option>
                    </select>
                </div>
            </div>

            <div class="mt-8 flex flex-col">
                <div class="-my-2 -mx-4 overflow-x-auto sm:-mx-6 lg:-mx-8">
                    <div class="inline-block min-w-full py-2 align-middle md:px-6 lg:px-8">
                        <div class="overflow-hidden shadow ring-1 ring-black ring-opacity-5 md:rounded-lg">
                            <?php if (empty($users)): ?>
                                <div class="p-4 text-center text-gray-500">
                                    No users found.
                                </div>
                            <?php else: ?>
                                <table class="min-w-full divide-y divide-gray-300">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Name</th>
                                            <th class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Email</th>
                                            <th class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Role</th>
                                            <th class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Status</th>
                                            <th class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-200 bg-white">
                                        <?php foreach ($users as $userData): ?>
                                            <tr>
                                                <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-900">
                                                    <div class="flex items-center">
                                                        <div class="h-10 w-10 flex-shrink-0">
                                                            <img class="h-10 w-10 rounded-full" src="<?= $userData['profile_image'] ?? '../assets/images/default-avatar.png' ?>" alt="">
                                                        </div>
                                                        <div class="ml-4">
                                                            <?= htmlspecialchars($userData['first_name'] . ' ' . $userData['last_name']) ?>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500"><?= htmlspecialchars($userData['email']) ?></td>
                                                <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500"><?= ucfirst($userData['role']) ?></td>
                                                <td class="whitespace-nowrap px-3 py-4 text-sm">
                                                    <span class="inline-flex rounded-full px-2 text-xs font-semibold leading-5 
                                                    <?php
                                                    switch ($userData['status']) {
                                                        case 'active':
                                                            echo 'bg-green-100 text-green-800';
                                                            break;
                                                        case 'pending':
                                                            echo 'bg-yellow-100 text-yellow-800';
                                                            break;
                                                        case 'suspended':
                                                            echo 'bg-red-100 text-red-800';
                                                            break;
                                                    }
                                                    ?>">
                                                        <?= ucfirst($userData['status']) ?>
                                                    </span>
                                                </td>
                                                <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">
                                                    <div class="flex space-x-2">
                                                        <?php if ($userData['status'] === 'pending'): ?>
                                                            <form method="POST" class="inline">
                                                                <input type="hidden" name="action" value="approve">
                                                                <input type="hidden" name="id" value="<?= $userData['id'] ?>">
                                                                <button type="submit" 
                                                                    onclick="return confirm('Are you sure you want to approve this user?')"
                                                                    class="text-green-600 hover:text-green-900">
                                                                    Approve
                                                                </button>
                                                            </form>
                                                        <?php endif; ?>

                                                        <?php if ($userData['status'] === 'active'): ?>
                                                            <form method="POST" class="inline">
                                                                <input type="hidden" name="action" value="suspend">
                                                                <input type="hidden" name="id" value="<?= $userData['id'] ?>">
                                                                <button type="submit" 
                                                                    onclick="return confirm('Are you sure you want to suspend this user?')"
                                                                    class="text-yellow-600 hover:text-yellow-900">
                                                                    Suspend
                                                                </button>
                                                            </form>
                                                        <?php endif; ?>

                                                        <?php if ($userData['status'] === 'suspended'): ?>
                                                            <form method="POST" class="inline">
                                                                <input type="hidden" name="action" value="approve">
                                                                <input type="hidden" name="id" value="<?= $userData['id'] ?>">
                                                                <button type="submit" 
                                                                    onclick="return confirm('Are you sure you want to reactivate this user?')"
                                                                    class="text-green-600 hover:text-green-900">
                                                                    Reactivate
                                                                </button>
                                                            </form>
                                                        <?php endif; ?>

                                                        <form method="POST" class="inline">
                                                            <input type="hidden" name="action" value="delete">
                                                            <input type="hidden" name="id" value="<?= $userData['id'] ?>">
                                                            <button type="submit" 
                                                                onclick="return confirm('Are you sure you want to delete this user? This action cannot be undone.')"
                                                                class="text-red-600 hover:text-red-900">
                                                                Delete
                                                            </button>
                                                        </form>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <?php if ($totalPages > 1): ?>
                <div class="mt-4 flex items-center justify-between">
                    <div class="flex justify-between sm:hidden">
                        <?php if ($page > 1): ?>
                            <a href="?page=<?= $page - 1 ?>&role=<?= $role ?>&status=<?= $status ?>" class="relative inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">Previous</a>
                        <?php endif; ?>
                        <?php if ($page < $totalPages): ?>
                            <a href="?page=<?= $page + 1 ?>&role=<?= $role ?>&status=<?= $status ?>" class="relative ml-3 inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">Next</a>
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
                                    <a href="?page=<?= $i ?>&role=<?= $role ?>&status=<?= $status ?>"
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
    function handleAction(action, userId) {
        const confirmMessages = {
            approve: 'Are you sure you want to approve this user?',
            suspend: 'Are you sure you want to suspend this user?',
            delete: 'Are you sure you want to delete this user? This action cannot be undone.'
        };

        if (confirm(confirmMessages[action])) {
            const formData = new FormData();
            formData.append('action', action);
            formData.append('id', userId);

            fetch('users.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    alert(data.error || 'Error performing action');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Network error occurred while performing action');
            });
        }
    }

    function applyFilters() {
        const role = document.getElementById('roleFilter').value;
        const status = document.getElementById('statusFilter').value;
        window.location.href = `users.php?role=${role}&status=${status}`;
    }
</script>

<?php require_once '../includes/footer.php'; ?>