<?php
class Course
{
    private $db;
    private $table = 'courses';

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    // Create new course
    public function create($data)
    {
        $query = "INSERT INTO {$this->table} 
                 (teacher_id, category_id, title, description, price, level, status, thumbnail) 
                 VALUES 
                 (:teacher_id, :category_id, :title, :description, :price, :level, :status, :thumbnail)";

        try {
            $stmt = $this->db->prepare($query);

            $stmt->bindParam(':teacher_id', $data['teacher_id']);
            $stmt->bindParam(':category_id', $data['category_id']);
            $stmt->bindParam(':title', $data['title']);
            $stmt->bindParam(':description', $data['description']);
            $stmt->bindParam(':price', $data['price']);
            $stmt->bindParam(':level', $data['level']);
            $stmt->bindParam(':status', $data['status']);
            $stmt->bindParam(':thumbnail', $data['thumbnail']);

            if ($stmt->execute()) {
                return $this->db->lastInsertId();
            }
            return false;
        } catch (PDOException $e) {
            error_log("Error creating course: " . $e->getMessage());
            return false;
        }
    }

    // Get course by ID with additional details
    public function getById($id)
    {
        $query = "SELECT c.*, u.first_name, u.last_name, cat.name as category_name 
                 FROM {$this->table} c
                 JOIN users u ON c.teacher_id = u.id
                 JOIN categories cat ON c.category_id = cat.id
                 WHERE c.id = :id";
        try {
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':id', $id);
            $stmt->execute();
            return $stmt->fetch();
        } catch (PDOException $e) {
            error_log("Error getting course: " . $e->getMessage());
            return false;
        }
    }

    // Update course
    public function update($id, $data)
    {
        $query = "UPDATE {$this->table} 
                 SET title = :title,
                     description = :description,
                     price = :price,
                     level = :level,
                     status = :status,
                     category_id = :category_id,
                     thumbnail = :thumbnail
                 WHERE id = :id AND teacher_id = :teacher_id";

        try {
            $stmt = $this->db->prepare($query);

            $stmt->bindParam(':title', $data['title']);
            $stmt->bindParam(':description', $data['description']);
            $stmt->bindParam(':price', $data['price']);
            $stmt->bindParam(':level', $data['level']);
            $stmt->bindParam(':status', $data['status']);
            $stmt->bindParam(':category_id', $data['category_id']);
            $stmt->bindParam(':thumbnail', $data['thumbnail']);
            $stmt->bindParam(':id', $id);
            $stmt->bindParam(':teacher_id', $data['teacher_id']);

            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error updating course: " . $e->getMessage());
            return false;
        }
    }

    // Delete course
    public function delete($id, $teacher_id)
    {
        $query = "DELETE FROM {$this->table} WHERE id = :id AND teacher_id = :teacher_id";
        try {
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':id', $id);
            $stmt->bindParam(':teacher_id', $teacher_id);
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error deleting course: " . $e->getMessage());
            return false;
        }
    }

    // Get all courses with filters
    public function getAll($filters = [], $limit = null, $offset = 0)
    {
        $query = "SELECT c.*, u.first_name, u.last_name, cat.name as category_name 
                 FROM {$this->table} c
                 JOIN users u ON c.teacher_id = u.id
                 JOIN categories cat ON c.category_id = cat.id
                 WHERE 1=1";

        $params = [];

        // Add filters
        if (!empty($filters['category_id'])) {
            $query .= " AND c.category_id = :category_id";
            $params[':category_id'] = $filters['category_id'];
        }

        if (!empty($filters['teacher_id'])) {
            $query .= " AND c.teacher_id = :teacher_id";
            $params[':teacher_id'] = $filters['teacher_id'];
        }

        if (!empty($filters['status'])) {
            $query .= " AND c.status = :status";
            $params[':status'] = $filters['status'];
        }

        if (!empty($filters['level'])) {
            $query .= " AND c.level = :level";
            $params[':level'] = $filters['level'];
        }

        if (!empty($filters['search'])) {
            $query .= " AND (c.title LIKE :search OR c.description LIKE :search)";
            $params[':search'] = "%{$filters['search']}%";
        }

        // Add sorting
        $query .= " ORDER BY c.created_at DESC";

        // Add pagination
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
            error_log("Error getting courses: " . $e->getMessage());
            return false;
        }
    }

    // Get courses by teacher
    public function getByTeacher($teacher_id)
    {
        $query = "SELECT * FROM {$this->table} WHERE teacher_id = :teacher_id ORDER BY created_at DESC";
        try {
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':teacher_id', $teacher_id);
            $stmt->execute();
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Error getting teacher courses: " . $e->getMessage());
            return false;
        }
    }

    // Get enrolled students for a course
    public function getEnrolledStudents($course_id)
    {
        $query = "SELECT u.* FROM users u
                 JOIN enrollments e ON u.id = e.student_id
                 WHERE e.course_id = :course_id";
        try {
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':course_id', $course_id);
            $stmt->execute();
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Error getting enrolled students: " . $e->getMessage());
            return false;
        }
    }

    // Count total courses
    public function countAll($filters = [])
    {
        $query = "SELECT COUNT(*) as total FROM {$this->table} WHERE 1=1";
        $params = [];

        if (!empty($filters['category_id'])) {
            $query .= " AND category_id = :category_id";
            $params[':category_id'] = $filters['category_id'];
        }

        if (!empty($filters['teacher_id'])) {
            $query .= " AND teacher_id = :teacher_id";
            $params[':teacher_id'] = $filters['teacher_id'];
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
            error_log("Error counting courses: " . $e->getMessage());
            return false;
        }
    }

    // Get popular courses
    public function getPopularCourses($limit = 5)
    {
        $query = "SELECT c.*, COUNT(e.id) as enrollment_count 
                 FROM {$this->table} c
                 LEFT JOIN enrollments e ON c.id = e.course_id
                 WHERE c.status = 'published'
                 GROUP BY c.id
                 ORDER BY enrollment_count DESC
                 LIMIT :limit";

        try {
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Error getting popular courses: " . $e->getMessage());
            return false;
        }
    }
}
