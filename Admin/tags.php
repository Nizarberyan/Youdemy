<?php
session_start();
require_once '../config/database.php';
require_once '../classes/Auth.php';
require_once '../classes/Tags.php';

$auth = new Auth();
$auth->requireRole('admin');

$tags = new Tags();

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $id = $_POST['id'] ?? null;

    switch ($action) {
        case 'create':
            if (!empty($_POST['name'])) {
                $tagNames = array_map('trim', explode(',', $_POST['name']));
                $success = true;
                $createdCount = 0;

                foreach ($tagNames as $tagName) {
                    if (!empty($tagName)) {
                        if ($tags->create(['name' => $tagName])) {
                            $createdCount++;
                        } else {
                            $success = false;
                        }
                    }
                }

                if ($success && $createdCount > 0) {
                    $_SESSION['success'] = $createdCount . ' tag(s) created successfully';
                } else {
                    $_SESSION['error'] = 'Failed to create some tags';
                }
            }
            break;

        case 'delete':
            if ($id) {
                $result = $tags->delete($id);
                if ($result) {
                    $_SESSION['success'] = 'Tag deleted successfully';
                } else {
                    $_SESSION['error'] = 'Failed to delete tag';
                }
            }
            break;
    }

    header('Location: tags.php');
    exit;
}

// Get all tags
$allTags = $tags->getAll();

require_once 'adminHeader.php';
?>

<div class="min-h-screen bg-gray-100">
    <div class="py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center">
                <h1 class="text-3xl font-bold text-gray-900">Manage Tags</h1>
            </div>

            <?php if (isset($_SESSION['success'])): ?>
                <div class="mt-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
                    <span class="block sm:inline"><?= htmlspecialchars($_SESSION['success']) ?></span>
                </div>
                <?php unset($_SESSION['success']); ?>
            <?php endif; ?>

            <?php if (isset($_SESSION['error'])): ?>
                <div class="mt-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
                    <span class="block sm:inline"><?= htmlspecialchars($_SESSION['error']) ?></span>
                </div>
                <?php unset($_SESSION['error']); ?>
            <?php endif; ?>

            <!-- Add Tag Form -->
            <div class="mt-6 bg-white shadow px-4 py-5 sm:rounded-lg sm:p-6">
                <form method="POST" class="space-y-4">
                    <input type="hidden" name="action" value="create">
                    <div>
                        <label for="tag-name" class="block text-sm font-medium text-gray-700">New Tag Names</label>
                        <p class="text-sm text-gray-500 mb-2">Enter multiple tags separated by commas (e.g., "PHP, JavaScript, Web Design")</p>
                        <div class="mt-1 flex rounded-md shadow-sm">
                            <input type="text" name="name" id="tag-name" required placeholder="Tag1, Tag2, Tag3"
                                class="flex-1 min-w-0 block w-full px-3 py-2 rounded-md border border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                            <button type="submit" class="ml-3 inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                Add Tags
                            </button>
                        </div>
                    </div>
                </form>
            </div>

            <!-- Tags List -->
            <div class="mt-8 flex flex-col">
                <div class="-my-2 -mx-4 overflow-x-auto sm:-mx-6 lg:-mx-8">
                    <div class="inline-block min-w-full py-2 align-middle md:px-6 lg:px-8">
                        <div class="overflow-hidden shadow ring-1 ring-black ring-opacity-5 md:rounded-lg">
                            <table class="min-w-full divide-y divide-gray-300">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th scope="col" class="py-3.5 pl-4 pr-3 text-left text-sm font-semibold text-gray-900">Name</th>
                                        <th scope="col" class="relative py-3.5 pl-3 pr-4 sm:pr-6">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200 bg-white">
                                    <?php foreach ($allTags as $tag): ?>
                                        <tr>
                                            <td class="whitespace-nowrap py-4 pl-4 pr-3 text-sm text-gray-900">
                                                <?= htmlspecialchars($tag['name']) ?>
                                            </td>
                                            <td class="relative whitespace-nowrap py-4 pl-3 pr-4 text-right text-sm font-medium sm:pr-6">
                                                <div class="flex space-x-2 justify-end">
                                                    <form method="POST" class="inline">
                                                        <input type="hidden" name="action" value="delete">
                                                        <input type="hidden" name="id" value="<?= $tag['id'] ?>">
                                                        <button type="submit"
                                                            onclick="return confirm('Are you sure you want to delete this tag?')"
                                                            class="text-red-600 hover:text-red-900">Delete</button>
                                                    </form>
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

<?php require_once '../includes/footer.php'; ?>