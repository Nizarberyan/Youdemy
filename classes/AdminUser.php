<?php

require_once 'User.php';

class AdminUser extends User
{
    // Implement any required abstract methods from User class
    // Add admin-specific functionality

    public function countAll($role = null)
    {
        $sql = "SELECT COUNT(*) as count FROM users";
        if ($role) {
            $sql .= " WHERE role = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$role]);
        } else {
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
        }
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['count'];
    }

    public function getAll($role = null, $status = null)
    {
        $sql = "SELECT * FROM users";
        $params = [];

        if ($role || $status) {
            $sql .= " WHERE";
            if ($role) {
                $sql .= " role = ?";
                $params[] = $role;
            }
            if ($status) {
                if ($role) $sql .= " AND";
                $sql .= " status = ?";
                $params[] = $status;
            }
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getSpecificData()
    {
        // For admin users, we'll implement a method to get all user data
        $sql = "SELECT * FROM users WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$this->id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function updateSpecificData($data)
    {
        // For admin users, we'll implement a method to update specific user data
        $allowedFields = ['first_name', 'last_name', 'email', 'status', 'role'];

        // Validate fields
        foreach ($data as $key => $value) {
            if (!in_array($key, $allowedFields)) {
                throw new Exception("Invalid field: $key");
            }
        }

        // Build the SQL query
        $setFields = [];
        $params = [];
        foreach ($data as $key => $value) {
            $setFields[] = "$key = ?";
            $params[] = $value;
        }
        $params[] = $this->id;

        $sql = "UPDATE users SET " . implode(', ', $setFields) . " WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($params);
    }

    public function delete($userId)
    {
        $sql = "DELETE FROM users WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$userId]);
    }

    public function getAllExcept($userId, $role = null, $status = null, $limit = null, $offset = null)
    {
        $sql = "SELECT * FROM users WHERE id != :userId";
        $params = [':userId' => $userId];

        if ($role) {
            $sql .= " AND role = :role";
            $params[':role'] = $role;
        }
        if ($status) {
            $sql .= " AND status = :status";
            $params[':status'] = $status;
        }

        if ($limit !== null) {
            $sql .= " LIMIT " . intval($limit);

            if ($offset !== null) {
                $sql .= " OFFSET " . intval($offset);
            }
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function countAllExcept($userId, $role = null)
    {
        $sql = "SELECT COUNT(*) as count FROM users WHERE id != ?";
        $params = [$userId];

        if ($role) {
            $sql .= " AND role = ?";
            $params[] = $role;
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['count'];
    }
}
