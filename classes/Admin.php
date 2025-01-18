<?php
class Admin extends User
{
    public function getSpecificData()
    {
        return [];  // Admins don't have specific data table
    }

    public function updateSpecificData($data)
    {
        return true;  // Admins don't have specific data to update
    }

    public function getStats()
    {
        try {
            $stats = [];

            // Get total users by role
            $query = "SELECT role, COUNT(*) as count FROM users GROUP BY role";
            $stmt = $this->db->prepare($query);
            $stmt->execute();
            $stats['users'] = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);

            // Get total courses
            $query = "SELECT COUNT(*) FROM courses";
            $stmt = $this->db->prepare($query);
            $stmt->execute();
            $stats['total_courses'] = $stmt->fetchColumn();

            return $stats;
        } catch (PDOException $e) {
            error_log("Error getting admin stats: " . $e->getMessage());
            return [];
        }
    }
}
