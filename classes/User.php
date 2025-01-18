<?php
require_once 'Student.php';
require_once 'Teacher.php';
require_once 'Admin.php';

abstract class User
{
    protected $db;
    protected $table = 'users';

    // Common properties
    protected $id;
    protected $email;
    protected $firstName;
    protected $lastName;
    protected $role;
    protected $status;
    protected $profileImage;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    // Factory method to create specific user types
    public static function createUser($role, $userId = null)
    {
        switch ($role) {
            case 'teacher':
                return new Teacher($userId);
            case 'student':
                return new Student($userId);
            case 'admin':
                return new Admin($userId);
            default:
                throw new Exception("Invalid user role");
        }
    }

    // Common methods that all user types share
    public function getById($id)
    {
        $query = "SELECT * FROM {$this->table} WHERE id = :id";
        try {
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':id', $id);
            $stmt->execute();
            return $stmt->fetch();
        } catch (PDOException $e) {
            error_log("Error getting user: " . $e->getMessage());
            return false;
        }
    }

    public function getByEmail($email)
    {
        $query = "SELECT * FROM {$this->table} WHERE email = :email";
        try {
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':email', $email);
            $stmt->execute();
            return $stmt->fetch();
        } catch (PDOException $e) {
            error_log("Error getting user by email: " . $e->getMessage());
            return false;
        }
    }

    // Base update method
    public function update($id, $data)
    {
        try {
            $setFields = [];
            $params = [];

            foreach ($data as $field => $value) {
                $setFields[] = "`$field` = :$field";
                $params[$field] = $value;
            }

            $params['id'] = $id;

            $query = "UPDATE users SET " . implode(', ', $setFields) . " WHERE id = :id";
            $stmt = $this->db->prepare($query);
            return $stmt->execute($params);
        } catch (PDOException $e) {
            error_log("Database error in update: " . $e->getMessage());
            return false;
        }
    }

    // Abstract method that must be implemented by child classes
    abstract public function getSpecificData();
    abstract public function updateSpecificData($data);

    // Common methods continue...
    public function updatePassword($id, $newPassword)
    {
        $query = "UPDATE {$this->table} SET password = :password WHERE id = :id";
        try {
            $stmt = $this->db->prepare($query);
            $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
            $stmt->bindParam(':password', $hashedPassword);
            $stmt->bindParam(':id', $id);
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error updating password: " . $e->getMessage());
            return false;
        }
    }

    public function updateProfileImage($id, $imagePath)
    {
        $query = "UPDATE {$this->table} SET profile_image = :imagePath WHERE id = :id";
        try {
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':imagePath', $imagePath);
            $stmt->bindParam(':id', $id);
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error updating profile image: " . $e->getMessage());
            return false;
        }
    }

    // Other common methods...
}
