<?php
class Category
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    public function create($data)
    {
        try {
            $query = "INSERT INTO categories (name) VALUES (:name)";
            $stmt = $this->db->prepare($query);
            return $stmt->execute(['name' => $data['name']]);
        } catch (PDOException $e) {
            error_log("Error creating category: " . $e->getMessage());
            return false;
        }
    }

    public function getAll()
    {
        try {
            $query = "SELECT * FROM categories ORDER BY name ASC";
            $stmt = $this->db->query($query);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error getting categories: " . $e->getMessage());
            return [];
        }
    }

    public function update($id, $data)
    {
        try {
            $query = "UPDATE categories SET name = :name WHERE id = :id";
            $stmt = $this->db->prepare($query);
            return $stmt->execute([
                'name' => $data['name'],
                'id' => $id
            ]);
        } catch (PDOException $e) {
            error_log("Error updating category: " . $e->getMessage());
            return false;
        }
    }

    public function delete($id)
    {
        try {
            $query = "DELETE FROM categories WHERE id = :id";
            $stmt = $this->db->prepare($query);
            return $stmt->execute(['id' => $id]);
        } catch (PDOException $e) {
            error_log("Error deleting category: " . $e->getMessage());
            return false;
        }
    }

    public function getById($id)
    {
        try {
            $query = "SELECT * FROM categories WHERE id = :id";
            $stmt = $this->db->prepare($query);
            $stmt->execute(['id' => $id]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error getting category: " . $e->getMessage());
            return false;
        }
    }

    public function getCategoryStats()
    {
        try {
            $query = "SELECT c.id, c.name, COUNT(co.id) as course_count 
                     FROM categories c 
                     LEFT JOIN courses co ON c.id = co.category_id 
                     GROUP BY c.id, c.name";
            $stmt = $this->db->query($query);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error getting category stats: " . $e->getMessage());
            return [];
        }
    }
}
