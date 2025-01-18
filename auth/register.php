<?php
require_once '../config/database.php';
require_once '../classes/Auth.php';

session_start();

// Redirect if already logged in
if (isset($_SESSION['user_id'])) {
    header('Location: /');
    exit;
}

$auth = new Auth();
$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        'first_name' => $_POST['first_name'],
        'last_name' => $_POST['last_name'],
        'email' => $_POST['email'],
        'password' => $_POST['password'],
        'role' => $_POST['role']
    ];

    // Add role-specific data
    if ($_POST['role'] === 'teacher') {
        $data['bio'] = $_POST['bio'];
        $data['specialization'] = $_POST['specialization'];
    } elseif ($_POST['role'] === 'student') {
        $data['education_level'] = $_POST['education_level'];
    }

    $result = $auth->register($data);
    if ($result && $result['success']) {
        if ($result['role'] === 'teacher') {
            $_SESSION['success'] = "Registration successful! Please wait for admin approval.";
            header('Location: login.php');
        } else {
            // For students, log them in automatically
            $loginResult = $auth->login($data['email'], $data['password']);
            if ($loginResult['success']) {
                header('Location: /student/index.php');
            } else {
                header('Location: login.php');
            }
        }
        exit();
    } else {
        $error = "Registration failed. Please try again.";
    }
}

require_once '../includes/header.php';
?>

<div class="min-h-screen bg-gray-50 flex flex-col justify-center py-12 sm:px-6 lg:px-8">
    <div class="sm:mx-auto sm:w-full sm:max-w-md">
        <h2 class="mt-6 text-center text-3xl font-extrabold text-gray-900">
            Create your account
        </h2>
    </div>

    <div class="mt-8 sm:mx-auto sm:w-full sm:max-w-md">
        <div class="bg-white py-8 px-4 shadow sm:rounded-lg sm:px-10">
            <?php if (isset($error)): ?>
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                    <span class="block sm:inline"><?= htmlspecialchars($error) ?></span>
                </div>
            <?php endif; ?>

            <form class="space-y-6" action="register.php" method="POST">
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label for="first_name" class="block text-sm font-medium text-gray-700">First Name</label>
                        <input type="text" name="first_name" id="first_name" required
                            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                    </div>

                    <div>
                        <label for="last_name" class="block text-sm font-medium text-gray-700">Last Name</label>
                        <input type="text" name="last_name" id="last_name" required
                            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                    </div>
                </div>

                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700">Email address</label>
                    <input type="email" name="email" id="email" required
                        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                </div>

                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700">Password</label>
                    <input type="password" name="password" id="password" required
                        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                </div>

                <div>
                    <label for="role" class="block text-sm font-medium text-gray-700">I want to</label>
                    <select name="role" id="role" required onchange="toggleRoleFields(this.value)"
                        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                        <option value="">Select role</option>
                        <option value="student">Learn on Youdemy</option>
                        <option value="teacher">Teach on Youdemy</option>
                    </select>
                </div>

                <!-- Teacher-specific fields -->
                <div id="teacher-fields" style="display: none;">
                    <div class="space-y-4">
                        <div>
                            <label for="bio" class="block text-sm font-medium text-gray-700">Bio</label>
                            <textarea name="bio" id="bio" rows="3"
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500"></textarea>
                        </div>

                        <div>
                            <label for="specialization" class="block text-sm font-medium text-gray-700">Areas of Expertise</label>
                            <input type="text" name="specialization" id="specialization"
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                            <p class="mt-1 text-sm text-gray-500">Separate multiple areas with commas</p>
                        </div>
                    </div>
                </div>

                <!-- Student-specific fields -->
                <div id="student-fields" style="display: none;">
                    <div>
                        <label for="education_level" class="block text-sm font-medium text-gray-700">Education Level</label>
                        <select name="education_level" id="education_level"
                            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                            <option value="">Select level</option>
                            <option value="high_school">High School</option>
                            <option value="bachelors">Bachelor's Degree</option>
                            <option value="masters">Master's Degree</option>
                            <option value="phd">PhD</option>
                            <option value="other">Other</option>
                        </select>
                    </div>
                </div>

                <div>
                    <button type="submit"
                        class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        Register
                    </button>
                </div>
            </form>

            <div class="mt-6">
                <div class="relative">
                    <div class="absolute inset-0 flex items-center">
                        <div class="w-full border-t border-gray-300"></div>
                    </div>
                    <div class="relative flex justify-center text-sm">
                        <span class="px-2 bg-white text-gray-500">
                            Already have an account?
                        </span>
                    </div>
                </div>

                <div class="mt-6">
                    <a href="login.php"
                        class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-indigo-600 bg-white hover:bg-gray-50">
                        Sign in
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    function toggleRoleFields(role) {
        const teacherFields = document.getElementById('teacher-fields');
        const studentFields = document.getElementById('student-fields');

        teacherFields.style.display = role === 'teacher' ? 'block' : 'none';
        studentFields.style.display = role === 'student' ? 'block' : 'none';

        // Update required attributes
        const bioInput = document.getElementById('bio');
        const specializationInput = document.getElementById('specialization');
        const educationLevelInput = document.getElementById('education_level');

        if (role === 'teacher') {
            bioInput.required = true;
            specializationInput.required = true;
            educationLevelInput.required = false;
        } else if (role === 'student') {
            bioInput.required = false;
            specializationInput.required = false;
            educationLevelInput.required = true;
        } else {
            bioInput.required = false;
            specializationInput.required = false;
            educationLevelInput.required = false;
        }
    }
</script>

<?php require_once '../includes/footer.php'; ?>