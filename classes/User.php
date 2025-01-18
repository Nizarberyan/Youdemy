<?php
class User
{
    private $db;
    private $table = 'users';

    // User properties
    private $id;
    private $email;
    private $firstName;
    private $lastName;
    private $role;
    private $status;
    private $profileImage;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    // Create new user
    public function create($data)
    {
        $query = "INSERT INTO {$this->table} 
                 (email, password, first_name, last_name, role, status) 
                 VALUES (:email, :password, :firstName, :lastName, :role, :status)";

        try {
            $stmt = $this->db->prepare($query);

            // Hash password
            $hashedPassword = password_hash($data['password'], PASSWORD_DEFAULT);

            $stmt->bindParam(':email', $data['email']);
            $stmt->bindParam(':password', $hashedPassword);
            $stmt->bindParam(':firstName', $data['firstName']);
            $stmt->bindParam(':lastName', $data['lastName']);
            $stmt->bindParam(':role', $data['role']);
            $stmt->bindParam(':status', $data['status']);

            if ($stmt->execute()) {
                return $this->db->lastInsertId();
            }
            return false;
        } catch (PDOException $e) {
            error_log("Error creating user: " . $e->getMessage());
            return false;
        }
    }

    // Get user by ID
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

    // Get user by email
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

    // Update user
    public function update($id, $data)
    {
        $query = "UPDATE {$this->table} 
                 SET first_name = :firstName, 
                     last_name = :lastName, 
                     email = :email,
                     status = :status
                 WHERE id = :id";

        try {
            $stmt = $this->db->prepare($query);

            $stmt->bindParam(':firstName', $data['firstName']);
            $stmt->bindParam(':lastName', $data['lastName']);
            $stmt->bindParam(':email', $data['email']);
            $stmt->bindParam(':status', $data['status']);
            $stmt->bindParam(':id', $id);

            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error updating user: " . $e->getMessage());
            return false;
        }
    }

    // Update password
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

    // Delete user
    public function delete($id)
    {
        $query = "DELETE FROM {$this->table} WHERE id = :id";
        try {
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':id', $id);
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error deleting user: " . $e->getMessage());
            return false;
        }
    }

    // Get all users with optional filtering
    public function getAll($role = null, $status = null, $limit = null, $offset = 0)
    {
        $query = "SELECT * FROM {$this->table} WHERE 1=1";
        $params = [];

        if ($role) {
            $query .= " AND role = :role";
            $params[':role'] = $role;
        }

        if ($status) {
            $query .= " AND status = :status";
            $params[':status'] = $status;
        }

        if ($limit) {
            $query .= " LIMIT :limit OFFSET :offset";
            $params[':limit'] = $limit;
            $params[':offset'] = $offset;
        }

        try {
            $stmt = $this->db->prepare($query);
            foreach ($params as $key => &$value) {
                $stmt->bindParam($key, $value);
            }
            $stmt->execute();
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Error getting users: " . $e->getMessage());
            return false;
        }
    }

    // Update profile image
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

    // Count total users
    public function countAll($role = null)
    {
        $query = "SELECT COUNT(*) as total FROM {$this->table}";
        $params = [];

        if ($role) {
            $query .= " WHERE role = :role";
            $params[':role'] = $role;
        }

        try {
            $stmt = $this->db->prepare($query);
            foreach ($params as $key => &$value) {
                $stmt->bindParam($key, $value);
            }
            $stmt->execute();
            $result = $stmt->fetch();
            return $result['total'];
        } catch (PDOException $e) {
            error_log("Error counting users: " . $e->getMessage());
            return false;
        }
    }
}
