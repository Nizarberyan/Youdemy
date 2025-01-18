<?php
class Auth
{
    private $db;
    private $table = 'users';

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    public function login($email, $password)
    {
        try {
            $query = "SELECT * FROM {$this->table} WHERE email = :email";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':email', $email);
            $stmt->execute();

            $user = $stmt->fetch();

            if ($user && password_verify($password, $user['password'])) {
                if ($user['status'] !== 'active') {
                    return ['error' => 'Account is not active'];
                }

                $this->setSession($user);
                return ['success' => true, 'user' => $user];
            }

            return ['error' => 'Invalid credentials'];
        } catch (PDOException $e) {
            error_log("Login error: " . $e->getMessage());
            return ['error' => 'Login failed'];
        }
    }

    public function register($data)
    {
        try {
            $query = "SELECT id FROM {$this->table} WHERE email = :email";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':email', $data['email']);
            $stmt->execute();

            if ($stmt->fetch()) {
                return ['error' => 'Email already exists'];
            }

            $query = "INSERT INTO {$this->table} (email, password, first_name, last_name, role, status) 
                     VALUES (:email, :password, :first_name, :last_name, :role, :status)";

            $stmt = $this->db->prepare($query);

            $hashedPassword = password_hash($data['password'], PASSWORD_DEFAULT);
            $status = ($data['role'] === 'teacher') ? 'pending' : 'active';

            $stmt->bindParam(':email', $data['email']);
            $stmt->bindParam(':password', $hashedPassword);
            $stmt->bindParam(':first_name', $data['first_name']);
            $stmt->bindParam(':last_name', $data['last_name']);
            $stmt->bindParam(':role', $data['role']);
            $stmt->bindParam(':status', $status);

            $stmt->execute();
            return ['success' => true, 'user_id' => $this->db->lastInsertId()];
        } catch (PDOException $e) {
            error_log("Registration error: " . $e->getMessage());
            return ['error' => 'Registration failed'];
        }
    }

    public function logout()
    {
        session_start();
        session_unset();
        session_destroy();
        return true;
    }

    public function isLoggedIn()
    {
        return isset($_SESSION['user_id']);
    }

    public function getCurrentUser()
    {
        if (!$this->isLoggedIn()) {
            return null;
        }

        try {
            $query = "SELECT * FROM {$this->table} WHERE id = :id";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':id', $_SESSION['user_id']);
            $stmt->execute();
            return $stmt->fetch();
        } catch (PDOException $e) {
            error_log("Get current user error: " . $e->getMessage());
            return null;
        }
    }

    public function hasRole($role)
    {
        return isset($_SESSION['user_role']) && $_SESSION['user_role'] === $role;
    }

    public function resetPassword($email)
    {
        try {
            $token = bin2hex(random_bytes(32));
            $expiry = date('Y-m-d H:i:s', strtotime('+1 hour'));

            $query = "UPDATE {$this->table} 
                     SET reset_token = :token, 
                         reset_expiry = :expiry 
                     WHERE email = :email";

            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':token', $token);
            $stmt->bindParam(':expiry', $expiry);
            $stmt->bindParam(':email', $email);

            if ($stmt->execute()) {
                return ['success' => true, 'token' => $token];
            }
            return ['error' => 'Password reset failed'];
        } catch (PDOException $e) {
            error_log("Password reset error: " . $e->getMessage());
            return ['error' => 'Password reset failed'];
        }
    }

    public function verifyResetToken($token)
    {
        try {
            $query = "SELECT id FROM {$this->table} 
                     WHERE reset_token = :token 
                     AND reset_expiry > NOW() 
                     AND status = 'active'";

            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':token', $token);
            $stmt->execute();

            return $stmt->fetch() !== false;
        } catch (PDOException $e) {
            error_log("Token verification error: " . $e->getMessage());
            return false;
        }
    }

    public function updatePassword($token, $newPassword)
    {
        try {
            $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);

            $query = "UPDATE {$this->table} 
                     SET password = :password,
                         reset_token = NULL,
                         reset_expiry = NULL
                     WHERE reset_token = :token 
                     AND reset_expiry > NOW()";

            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':password', $hashedPassword);
            $stmt->bindParam(':token', $token);

            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Password update error: " . $e->getMessage());
            return false;
        }
    }

    private function setSession($user)
    {
        session_start();
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_email'] = $user['email'];
        $_SESSION['user_role'] = $user['role'];
        $_SESSION['user_name'] = $user['first_name'] . ' ' . $user['last_name'];
        $_SESSION['last_activity'] = time();
    }

    public function requireAuth()
    {
        if (!$this->isLoggedIn()) {
            header('Location: /auth/login.php');
            exit();
        }
    }

    public function requireRole($role)
    {
        // if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== $role) {
        //     if (!isset($_SESSION['user_id'])) {
        //         $_SESSION['redirect_url'] = $_SERVER['REQUEST_URI'];
        //         header('Location: /auth/login.php');
        //         exit;
        //     }

        //     $roleRedirects = [
        //         'student' => '/student/index.php',
        //         'teacher' => '/teacher/index.php',
        //         'admin' => '/admin/index.php'
        //     ];
        //     header('Location: ' . ($roleRedirects[$_SESSION['user_role']] ?? '/'));
        //     exit;
        // }
    }
}
