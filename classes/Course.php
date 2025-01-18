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
    public function delete($courseId, $teacherId = null)
    {
        try {
            $query = "DELETE FROM courses WHERE id = :id";
            if ($teacherId !== null) {
                $query .= " AND teacher_id = :teacher_id";
            }

            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':id', $courseId);
            if ($teacherId !== null) {
                $stmt->bindParam(':teacher_id', $teacherId);
            }
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error deleting course: " . $e->getMessage());
            return false;
        }
    }

    // Get all courses with filters
    public function getAll($filters = [], $limit = null, $offset = null, $sort = 'newest')
    {
        $query = "SELECT c.*, u.first_name, u.last_name, 
                  CONCAT(u.first_name, ' ', u.last_name) as teacher_name,
                  u.profile_image as teacher_image,
                  cat.name as category_name,
                  (SELECT COUNT(*) FROM enrollments WHERE course_id = c.id) as student_count
                  FROM {$this->table} c
                  JOIN users u ON c.teacher_id = u.id
                  LEFT JOIN categories cat ON c.category_id = cat.id
                  WHERE 1=1";

        $params = [];

        if (!empty($filters['category_id'])) {
            $query .= " AND c.category_id = :category_id";
            $params[':category_id'] = $filters['category_id'];
        }

        if (!empty($filters['search'])) {
            $query .= " AND (c.title LIKE :search OR c.description LIKE :search)";
            $params[':search'] = "%{$filters['search']}%";
        }

        if (!empty($filters['level'])) {
            $query .= " AND c.level = :level";
            $params[':level'] = $filters['level'];
        }

        if (!empty($filters['status'])) {
            $query .= " AND c.status = :status";
            $params[':status'] = $filters['status'];
        }

        if (!empty($filters['teacher_id'])) {
            $query .= " AND c.teacher_id = :teacher_id";
            $params[':teacher_id'] = $filters['teacher_id'];
        }

        switch ($sort) {
            case 'popular':
                $query .= " ORDER BY student_count DESC";
                break;
            case 'price_low':
                $query .= " ORDER BY c.price ASC";
                break;
            case 'price_high':
                $query .= " ORDER BY c.price DESC";
                break;
            default:
                $query .= " ORDER BY c.created_at DESC";
        }

        if ($limit !== null) {
            $query .= " LIMIT :limit";
            $params[':limit'] = $limit;

            if ($offset !== null) {
                $query .= " OFFSET :offset";
                $params[':offset'] = $offset;
            }
        }

        try {
            $stmt = $this->db->prepare($query);
            foreach ($params as $key => $value) {
                if ($key == ':limit' || $key == ':offset') {
                    $stmt->bindValue($key, $value, PDO::PARAM_INT);
                } else {
                    $stmt->bindValue($key, $value);
                }
            }
            $stmt->execute();
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Error getting courses: " . $e->getMessage());
            return []; // Return empty array instead of false
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

    // Get featured courses
    public function getFeatured($limit = 6)
    {
        $query = "SELECT c.*, u.first_name, u.last_name, 
                  CONCAT(u.first_name, ' ', u.last_name) as teacher_name,
                  u.profile_image as teacher_image,
                  (SELECT COUNT(*) FROM enrollments WHERE course_id = c.id) as student_count
                  FROM {$this->table} c
                  JOIN users u ON c.teacher_id = u.id
                  WHERE c.status = 'published' AND c.featured = 1
                  ORDER BY c.created_at DESC
                  LIMIT :limit";

        try {
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Error getting featured courses: " . $e->getMessage());
            return [];
        }
    }

    public function isStudentEnrolled($studentId, $courseId)
    {
        $query = "SELECT COUNT(*) as count FROM enrollments 
                  WHERE student_id = :student_id AND course_id = :course_id";

        try {
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':student_id', $studentId);
            $stmt->bindParam(':course_id', $courseId);
            $stmt->execute();
            $result = $stmt->fetch();
            return $result['count'] > 0;
        } catch (PDOException $e) {
            error_log("Error checking enrollment: " . $e->getMessage());
            return false;
        }
    }

    public function getStudentProgress($studentId, $courseId)
    {
        $query = "SELECT 
            (COUNT(DISTINCT cl.id) * 100 / (SELECT COUNT(*) FROM course_lessons WHERE course_id = :course_id)) as progress
            FROM course_lessons cl
            LEFT JOIN lesson_progress lp ON cl.id = lp.lesson_id AND lp.student_id = :student_id
            WHERE cl.course_id = :course_id AND lp.completed = 1";

        try {
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':student_id', $studentId);
            $stmt->bindParam(':course_id', $courseId);
            $stmt->execute();
            $result = $stmt->fetch();
            return round($result['progress'] ?? 0);
        } catch (PDOException $e) {
            error_log("Error getting progress: " . $e->getMessage());
            return 0;
        }
    }

    public function getCurriculum($courseId)
    {
        $query = "SELECT cs.*, cl.id as lesson_id, cl.title as lesson_title, 
                  cl.duration, cl.sort_order as lesson_order
                  FROM course_sections cs
                  LEFT JOIN course_lessons cl ON cs.id = cl.section_id
                  WHERE cs.course_id = :course_id
                  ORDER BY cs.sort_order, cl.sort_order";

        try {
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':course_id', $courseId);
            $stmt->execute();
            $results = $stmt->fetchAll();

            $curriculum = [];
            foreach ($results as $row) {
                if (!isset($curriculum[$row['id']])) {
                    $curriculum[$row['id']] = [
                        'title' => $row['title'],
                        'lessons' => []
                    ];
                }
                if ($row['lesson_id']) {
                    $curriculum[$row['id']]['lessons'][] = [
                        'id' => $row['lesson_id'],
                        'title' => $row['lesson_title'],
                        'duration' => $row['duration']
                    ];
                }
            }
            return array_values($curriculum);
        } catch (PDOException $e) {
            error_log("Error getting curriculum: " . $e->getMessage());
            return [];
        }
    }

    public function getReviews($courseId)
    {
        $query = "SELECT r.*, 
                  CONCAT(u.first_name, ' ', u.last_name) as user_name,
                  u.profile_image as user_image
                  FROM reviews r
                  JOIN users u ON r.student_id = u.id
                  WHERE r.course_id = :course_id
                  ORDER BY r.created_at DESC";

        try {
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':course_id', $courseId);
            $stmt->execute();
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Error getting reviews: " . $e->getMessage());
            return [];
        }
    }

    public function getAverageRating($courseId)
    {
        $query = "SELECT AVG(rating) as avg_rating 
                  FROM reviews 
                  WHERE course_id = :course_id";

        try {
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':course_id', $courseId);
            $stmt->execute();
            $result = $stmt->fetch();
            return round($result['avg_rating'] ?? 0, 1);
        } catch (PDOException $e) {
            error_log("Error getting average rating: " . $e->getMessage());
            return 0;
        }
    }

    public function countEnrolledCourses($studentId, $filters = [])
    {
        $query = "SELECT COUNT(DISTINCT c.id) as total
                  FROM courses c
                  JOIN enrollments e ON c.id = e.course_id
                  WHERE e.student_id = :student_id";

        $params = [':student_id' => $studentId];

        if (!empty($filters['search'])) {
            $query .= " AND (c.title LIKE :search OR c.description LIKE :search)";
            $params[':search'] = "%{$filters['search']}%";
        }

        if (!empty($filters['status'])) {
            if ($filters['status'] === 'completed') {
                $query .= " AND (SELECT COUNT(*) FROM lesson_progress lp 
                           JOIN course_lessons cl ON lp.lesson_id = cl.id 
                           WHERE cl.course_id = c.id AND lp.student_id = :student_id2 
                           AND lp.completed = 1) = 
                           (SELECT COUNT(*) FROM course_lessons WHERE course_id = c.id)";
                $params[':student_id2'] = $studentId;
            }
        }

        try {
            $stmt = $this->db->prepare($query);
            foreach ($params as $key => $value) {
                $stmt->bindValue($key, $value);
            }
            $stmt->execute();
            return $stmt->fetch()['total'];
        } catch (PDOException $e) {
            error_log("Error counting enrolled courses: " . $e->getMessage());
            return 0;
        }
    }

    public function getEnrolledCourses($studentId, $filters = [], $limit = null, $offset = null)
    {
        $query = "SELECT DISTINCT c.*, u.first_name, u.last_name,
                  CONCAT(u.first_name, ' ', u.last_name) as teacher_name
                  FROM courses c
                  JOIN enrollments e ON c.id = e.course_id
                  JOIN users u ON c.teacher_id = u.id
                  WHERE e.student_id = :student_id";

        $params = [':student_id' => $studentId];

        if (!empty($filters['search'])) {
            $query .= " AND (c.title LIKE :search OR c.description LIKE :search)";
            $params[':search'] = "%{$filters['search']}%";
        }

        if (!empty($filters['status'])) {
            if ($filters['status'] === 'completed') {
                $query .= " AND (SELECT COUNT(*) FROM lesson_progress lp 
                           JOIN course_lessons cl ON lp.lesson_id = cl.id 
                           WHERE cl.course_id = c.id AND lp.student_id = :student_id2 
                           AND lp.completed = 1) = 
                           (SELECT COUNT(*) FROM course_lessons WHERE course_id = c.id)";
                $params[':student_id2'] = $studentId;
            }
        }

        $query .= " ORDER BY e.enrolled_at DESC";

        if ($limit !== null) {
            $query .= " LIMIT :limit";
            if ($offset !== null) {
                $query .= " OFFSET :offset";
            }
        }

        try {
            $stmt = $this->db->prepare($query);
            foreach ($params as $key => $value) {
                $stmt->bindValue($key, $value);
            }
            if ($limit !== null) {
                $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
                if ($offset !== null) {
                    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
                }
            }
            $stmt->execute();
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Error getting enrolled courses: " . $e->getMessage());
            return [];
        }
    }
}
