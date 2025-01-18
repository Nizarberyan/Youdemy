<?php
class Auth
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    public function register($data)
    {
        try {
            $this->db->beginTransaction();

            // First, create the user record
            $query = "INSERT INTO users (email, password, first_name, last_name, role, status) 
                     VALUES (:email, :password, :first_name, :last_name, :role, :status)";

            $stmt = $this->db->prepare($query);
            $hashedPassword = password_hash($data['password'], PASSWORD_DEFAULT);

            // Set status based on role - students are automatically active
            $status = ($data['role'] === 'teacher') ? 'pending' : 'active';

            $stmt->execute([
                'email' => $data['email'],
                'password' => $hashedPassword,
                'first_name' => $data['first_name'],
                'last_name' => $data['last_name'],
                'role' => $data['role'],
                'status' => $status
            ]);

            $userId = $this->db->lastInsertId();

            // Handle role-specific data
            if ($data['role'] === 'teacher') {
                $teacherQuery = "INSERT INTO teachers (user_id, bio, specialization) 
                                VALUES (:user_id, :bio, :specialization)";
                $stmt = $this->db->prepare($teacherQuery);
                $stmt->execute([
                    'user_id' => $userId,
                    'bio' => $data['bio'] ?? null,
                    'specialization' => $data['specialization'] ?? null
                ]);
            } elseif ($data['role'] === 'student') {
                $studentQuery = "INSERT INTO students (user_id, education_level) 
                               VALUES (:user_id, :education_level)";
                $stmt = $this->db->prepare($studentQuery);
                $stmt->execute([
                    'user_id' => $userId,
                    'education_level' => $data['education_level'] ?? null
                ]);
            }

            $this->db->commit();
            return ['success' => true, 'status' => $status, 'role' => $data['role']];
        } catch (PDOException $e) {
            $this->db->rollBack();
            error_log("Registration error: " . $e->getMessage());
            return false;
        }
    }

    public function login($email, $password)
    {
        try {
            $query = "SELECT * FROM users WHERE email = :email";
            $stmt = $this->db->prepare($query);
            $stmt->execute(['email' => $email]);
            $user = $stmt->fetch();

            if ($user && password_verify($password, $user['password'])) {
                if ($user['status'] !== 'active') {
                    return ['error' => 'Account is not active'];
                }

                $_SESSION['user_id'] = $user['id'];
                $_SESSION['role'] = $user['role'];
                $_SESSION['name'] = $user['first_name'] . ' ' . $user['last_name'];

                return ['success' => true, 'role' => $user['role']];
            }

            return ['error' => 'Invalid credentials'];
        } catch (PDOException $e) {
            error_log("Login error: " . $e->getMessage());
            return ['error' => 'Login failed'];
        }
    }

    public function logout()
    {
        // Clear all session data
        $_SESSION = array();

        // Destroy the session cookie
        if (isset($_COOKIE[session_name()])) {
            setcookie(session_name(), '', time() - 3600, '/');
        }

        // Destroy the session
        session_destroy();

        // Redirect to login page
        header('Location: /auth/login.php');
        exit();
    }

    public function isLoggedIn()
    {
        return isset($_SESSION['user_id']);
    }

    public function requireLogin()
    {
        if (!$this->isLoggedIn()) {
            header('Location: /auth/login.php');
            exit();
        }
    }

    public function requireRole($role)
    {
        $this->requireLogin();
        if ($_SESSION['role'] !== $role && $_SESSION['role'] !== 'admin') {
            header('Location: /403.php');
            exit();
        }
    }

    public function getCurrentUser()
    {
        if (!$this->isLoggedIn()) {
            return null;
        }

        return User::createUser($_SESSION['role'], $_SESSION['user_id']);
    }
}
