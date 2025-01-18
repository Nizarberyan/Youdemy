<?php
session_start();
require_once '../config/database.php';
require_once '../classes/Auth.php';
require_once '../classes/User.php';
require_once '../classes/Teacher.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: ../auth/login.php');
    exit();
}

$auth = new Auth();
$auth->requireRole('teacher');

// Create Teacher instance
$teacher = new Teacher($_SESSION['user_id']);
$userData = $teacher->getById($_SESSION['user_id']);
$teacherData = $teacher->getSpecificData();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $uploadDir = '../assets/images/uploads/profiles/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        $profileImage = $userData['profile_image'];

        if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] === UPLOAD_ERR_OK) {
            // Delete old profile image if it exists
            if ($profileImage && file_exists('../' . $profileImage)) {
                unlink('../' . $profileImage);
            }

            $fileExtension = strtolower(pathinfo($_FILES['profile_image']['name'], PATHINFO_EXTENSION));
            $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif'];

            if (!in_array($fileExtension, $allowedExtensions)) {
                throw new Exception("Invalid file type. Only JPG, PNG and GIF are allowed.");
            }

            $fileName = uniqid('profile_') . '.' . $fileExtension;
            $profileImage = 'assets/images/uploads/profiles/' . $fileName;

            if (!move_uploaded_file($_FILES['profile_image']['tmp_name'], $uploadDir . $fileName)) {
                throw new Exception("Error uploading profile image");
            }
        }

        // Update user data
        $updateData = [
            'first_name' => $_POST['first_name'],
            'last_name' => $_POST['last_name'],
            'email' => $_POST['email'],
            'profile_image' => $profileImage
        ];

        // Handle password update if provided
        if (!empty($_POST['password'])) {
            if ($_POST['password'] !== $_POST['password_confirm']) {
                throw new Exception("Passwords do not match");
            }
            $updateData['password'] = password_hash($_POST['password'], PASSWORD_DEFAULT);
        }

        // Update base user data
        if (!$teacher->update($_SESSION['user_id'], $updateData)) {
            throw new Exception("Error updating user profile");
        }

        // Update teacher-specific data
        $teacherUpdateData = [
            'bio' => $_POST['bio'],
            'specialization' => $_POST['expertise']
        ];

        if (!$teacher->updateSpecificData($teacherUpdateData)) {
            throw new Exception("Error updating teacher profile");
        }

        $success = "Profile updated successfully";
        $userData = $teacher->getById($_SESSION['user_id']);
        $teacherData = $teacher->getSpecificData();
    } catch (Exception $e) {
        $error = $e->getMessage();
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
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"><?= htmlspecialchars($teacherData['bio'] ?? '') ?></textarea>
                            <p class="mt-2 text-sm text-gray-500">Brief description for your profile.</p>
                        </div>

                        <div>
                            <label for="expertise" class="block text-sm font-medium text-gray-700">Areas of Expertise</label>
                            <input type="text" name="expertise" id="expertise"
                                value="<?= htmlspecialchars($teacherData['specialization'] ?? '') ?>"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <p class="mt-2 text-sm text-gray-500">Separate multiple areas with commas.</p>
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