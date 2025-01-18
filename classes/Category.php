<?php
class Category
{
    private $db;
    private $table = 'categories';

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    public function create($data)
    {
        $query = "INSERT INTO {$this->table} (name, description, icon) 
                 VALUES (:name, :description, :icon)";

        try {
            $stmt = $this->db->prepare($query);

            $stmt->bindParam(':name', $data['name']);
            $stmt->bindParam(':description', $data['description']);
            $stmt->bindParam(':icon', $data['icon']);

            if ($stmt->execute()) {
                return $this->db->lastInsertId();
            }
            return false;
        } catch (PDOException $e) {
            error_log("Error creating category: " . $e->getMessage());
            return false;
        }
    }

    public function getById($id)
    {
        $query = "SELECT * FROM {$this->table} WHERE id = :id";
        try {
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':id', $id);
            $stmt->execute();
            return $stmt->fetch();
        } catch (PDOException $e) {
            error_log("Error getting category: " . $e->getMessage());
            return false;
        }
    }

    public function update($id, $data)
    {
        $query = "UPDATE {$this->table} 
                 SET name = :name, 
                     description = :description, 
                     icon = :icon 
                 WHERE id = :id";

        try {
            $stmt = $this->db->prepare($query);

            $stmt->bindParam(':name', $data['name']);
            $stmt->bindParam(':description', $data['description']);
            $stmt->bindParam(':icon', $data['icon']);
            $stmt->bindParam(':id', $id);

            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error updating category: " . $e->getMessage());
            return false;
        }
    }

    public function delete($id)
    {
        if ($this->hasCoursesAssigned($id)) {
            return false;
        }

        $query = "DELETE FROM {$this->table} WHERE id = :id";
        try {
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':id', $id);
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error deleting category: " . $e->getMessage());
            return false;
        }
    }

    public function getAll()
    {
        $query = "SELECT c.*, COUNT(co.id) as course_count 
                 FROM {$this->table} c
                 LEFT JOIN courses co ON c.id = co.category_id
                 GROUP BY c.id
                 ORDER BY c.name ASC";

        try {
            $stmt = $this->db->prepare($query);
            $stmt->execute();
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Error getting categories: " . $e->getMessage());
            return false;
        }
    }

    public function getPopularCategories($limit = 5)
    {
        $query = "SELECT c.*, COUNT(co.id) as course_count 
                 FROM {$this->table} c
                 LEFT JOIN courses co ON c.id = co.category_id
                 GROUP BY c.id
                 ORDER BY course_count DESC
                 LIMIT :limit";

        try {
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Error getting popular categories: " . $e->getMessage());
            return false;
        }
    }

    private function hasCoursesAssigned($id)
    {
        $query = "SELECT COUNT(*) as count FROM courses WHERE category_id = :id";
        try {
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':id', $id);
            $stmt->execute();
            $result = $stmt->fetch();
            return $result['count'] > 0;
        } catch (PDOException $e) {
            error_log("Error checking category courses: " . $e->getMessage());
            return true;
        }
    }

    public function getCategoryStats()
    {
        $query = "SELECT 
                    c.name,
                    COUNT(DISTINCT co.id) as total_courses,
                    COUNT(DISTINCT e.student_id) as total_students
                 FROM {$this->table} c
                 LEFT JOIN courses co ON c.id = co.category_id
                 LEFT JOIN enrollments e ON co.id = e.course_id
                 GROUP BY c.id
                 ORDER BY total_courses DESC";

        try {
            $stmt = $this->db->prepare($query);
            $stmt->execute();
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Error getting category statistics: " . $e->getMessage());
            return false;
        }
    }

    public function search($keyword)
    {
        $query = "SELECT * FROM {$this->table} 
                 WHERE name LIKE :keyword 
                 OR description LIKE :keyword";

        try {
            $keyword = "%{$keyword}%";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':keyword', $keyword);
            $stmt->execute();
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Error searching categories: " . $e->getMessage());
            return false;
        }
    }

    public function getCourses($category_id, $limit = null, $offset = 0)
    {
        $query = "SELECT c.* FROM courses c
                 WHERE c.category_id = :category_id
                 AND c.status = 'published'
                 ORDER BY c.created_at DESC";

        if ($limit) {
            $query .= " LIMIT :limit OFFSET :offset";
        }

        try {
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':category_id', $category_id);

            if ($limit) {
                $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
                $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
            }

            $stmt->execute();
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Error getting category courses: " . $e->getMessage());
            return false;
        }
    }
}
