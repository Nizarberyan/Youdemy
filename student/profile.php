<?php
require_once '../config/database.php';
require_once '../classes/Auth.php';
require_once '../classes/User.php';

$auth = new Auth();
$auth->requireRole('student');

$user = new User();
$userId = $_SESSION['user_id'];
$userData = $user->getById($userId);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $uploadDir = '../assets/images/uploads/profiles/';
    $profileImage = $userData['profile_image'];

    if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] === UPLOAD_ERR_OK) {
        if ($profileImage && file_exists('../' . $profileImage)) {
            unlink('../' . $profileImage);
        }

        $fileExtension = pathinfo($_FILES['profile_image']['name'], PATHINFO_EXTENSION);
        $fileName = uniqid('profile_') . '.' . $fileExtension;
        $profileImage = 'assets/images/uploads/profiles/' . $fileName;

        if (!move_uploaded_file($_FILES['profile_image']['tmp_name'], $uploadDir . $fileName)) {
            $error = "Error uploading profile image";
        }
    }

    $updateData = [
        'first_name' => $_POST['first_name'],
        'last_name' => $_POST['last_name'],
        'email' => $_POST['email'],
        'bio' => $_POST['bio'],
        'interests' => $_POST['interests'],
        'profile_image' => $profileImage
    ];

    if (isset($_POST['password']) && !empty($_POST['password'])) {
        if ($_POST['password'] === $_POST['password_confirm']) {
            $updateData['password'] = password_hash($_POST['password'], PASSWORD_DEFAULT);
        } else {
            $error = "Passwords do not match";
        }
    }

    if (!isset($error)) {
        if ($user->update($userId, $updateData)) {
            $success = "Profile updated successfully";
            $userData = $user->getById($userId);
        } else {
            $error = "Error updating profile";
        }
    }
}

require_once '../includes/header.php';
?>

<div class="min-h-screen bg-gray-100">
    <div class="py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <h1 class="text-3xl font-bold text-gray-900">Profile Settings</h1>

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
                <form action="profile.php" method="POST" enctype="multipart/form-data">
                    <div class="space-y-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Profile Photo</label>
                            <div class="mt-2 flex items-center space-x-5">
                                <div class="flex-shrink-0">
                                    <img class="h-24 w-24 rounded-full object-cover"
                                        src="../<?= $userData['profile_image'] ?? 'assets/images/default-avatar.png' ?>"
                                        alt="Profile photo">
                                </div>
                                <div class="flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-md">
                                    <div class="space-y-1 text-center">
                                        <div class="flex text-sm text-gray-600">
                                            <label for="profile_image" class="relative cursor-pointer bg-white rounded-md font-medium text-indigo-600 hover:text-indigo-500 focus-within:outline-none focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-indigo-500">
                                                <span>Upload a new photo</span>
                                                <input id="profile_image" name="profile_image" type="file" class="sr-only" accept="image/*">
                                            </label>
                                        </div>
                                        <p class="text-xs text-gray-500">PNG, JPG, GIF up to 10MB</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                            <div>
                                <label for="first_name" class="block text-sm font-medium text-gray-700">First Name</label>
                                <input type="text" name="first_name" id="first_name" required
                                    value="<?= htmlspecialchars($userData['first_name']) ?>"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            </div>

                            <div>
                                <label for="last_name" class="block text-sm font-medium text-gray-700">Last Name</label>
                                <input type="text" name="last_name" id="last_name" required
                                    value="<?= htmlspecialchars($userData['last_name']) ?>"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            </div>
                        </div>

                        <div>
                            <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
                            <input type="email" name="email" id="email" required
                                value="<?= htmlspecialchars($userData['email']) ?>"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        </div>

                        <div>
                            <label for="bio" class="block text-sm font-medium text-gray-700">Bio</label>
                            <textarea name="bio" id="bio" rows="4"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"><?= htmlspecialchars($userData['bio'] ?? '') ?></textarea>
                            <p class="mt-2 text-sm text-gray-500">Brief description for your profile.</p>
                        </div>

                        <div>
                            <label for="interests" class="block text-sm font-medium text-gray-700">Learning Interests</label>
                            <input type="text" name="interests" id="interests"
                                value="<?= htmlspecialchars($userData['interests'] ?? '') ?>"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <p class="mt-2 text-sm text-gray-500">Separate multiple interests with commas.</p>
                        </div>

                        <div class="space-y-6">
                            <div class="text-sm font-medium text-gray-700">Change Password (optional)</div>

                            <div>
                                <label for="password" class="block text-sm font-medium text-gray-700">New Password</label>
                                <input type="password" name="password" id="password"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            </div>

                            <div>
                                <label for="password_confirm" class="block text-sm font-medium text-gray-700">Confirm New Password</label>
                                <input type="password" name="password_confirm" id="password_confirm"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            </div>
                        </div>
                    </div>

                    <div class="mt-6 flex justify-end">
                        <button type="submit" class="bg-indigo-600 py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            Save Changes
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    const profileInput = document.getElementById('profile_image');
    const dropZone = profileInput.closest('div.border-dashed');

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
        profileInput.files = files;
    }
</script>

<?php require_once '../includes/footer.php'; ?>