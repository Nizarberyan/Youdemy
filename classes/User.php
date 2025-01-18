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
        try {
            $setFields = [];
            $params = [];

            foreach ($data as $field => $value) {
                $setFields[] = "`$field` = :$field";
                $params[$field] = $value;
            }

            $params['id'] = $id;

            $query = "UPDATE users SET " . implode(', ', $setFields) . " WHERE id = :id";
            error_log("Update query: " . $query . " with params: " . print_r($params, true));

            $stmt = $this->db->prepare($query);
            $result = $stmt->execute($params);

            if (!$result) {
                error_log("Update failed. Error info: " . print_r($stmt->errorInfo(), true));
            }

            return $result;
        } catch (PDOException $e) {
            error_log("Database error in update: " . $e->getMessage());
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
    public function getAll($role = '', $status = '', $limit = null, $offset = 0)
    {
        try {
            $params = [];
            $conditions = [];

            if (!empty($role)) {
                $conditions[] = "role = :role";
                $params[':role'] = $role;
            }

            if (!empty($status)) {
                $conditions[] = "status = :status";
                $params[':status'] = $status;
            }

            $whereClause = !empty($conditions) ? "WHERE " . implode(' AND ', $conditions) : "";
            $limitClause = $limit ? "LIMIT :limit OFFSET :offset" : "";

            $query = "SELECT * FROM users $whereClause ORDER BY created_at DESC $limitClause";

            $stmt = $this->db->prepare($query);

            if ($limit) {
                $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
                $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
            }

            foreach ($params as $key => $value) {
                $stmt->bindValue($key, $value);
            }

            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error in getAll: " . $e->getMessage());
            return [];  // Return empty array instead of false
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
