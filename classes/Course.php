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
        try {
            // First verify that the teacher exists in the teachers table
            $checkTeacherQuery = "SELECT user_id FROM teachers WHERE user_id = :teacher_id";
            $stmt = $this->db->prepare($checkTeacherQuery);
            $stmt->execute(['teacher_id' => $data['teacher_id']]);

            if (!$stmt->fetch()) {
                // If teacher doesn't exist in teachers table, create the record
                $createTeacherQuery = "INSERT INTO teachers (user_id) VALUES (:teacher_id)";
                $stmt = $this->db->prepare($createTeacherQuery);
                $stmt->execute(['teacher_id' => $data['teacher_id']]);
            }

            // Handle thumbnail path - store only filename in database
            $thumbnail = null;
            if (!empty($data['thumbnail'])) {
                // Remove any directory path prefixes
                $thumbnail = basename($data['thumbnail']);

                // Move the uploaded file to the correct location if it exists
                $sourcePath = $_FILES['thumbnail']['tmp_name'];
                if (file_exists($sourcePath)) {
                    $targetDir = dirname(__DIR__) . '/Admin/assets/images/uploads/courses/';
                    if (!is_dir($targetDir)) {
                        mkdir($targetDir, 0777, true);
                    }
                    move_uploaded_file($sourcePath, $targetDir . $thumbnail);
                }
            }

            $query = "INSERT INTO courses (teacher_id, category_id, title, description, price, level, status, thumbnail) 
                      VALUES (:teacher_id, :category_id, :title, :description, :price, :level, :status, :thumbnail)";

            $stmt = $this->db->prepare($query);
            $stmt->execute([
                'teacher_id' => $data['teacher_id'],
                'category_id' => $data['category_id'],
                'title' => $data['title'],
                'description' => $data['description'],
                'price' => $data['price'],
                'level' => $data['level'],
                'status' => $data['status'] ?? 'draft',
                'thumbnail' => $thumbnail
            ]);

            if ($stmt->rowCount() > 0) {
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
    public function update($id, $data, $teacherId = null)
    {
        try {
            // If only status is being updated
            if (count($data) === 1 && isset($data['status'])) {
                $query = "UPDATE {$this->table} SET status = :status WHERE id = :id";
                if ($teacherId !== null) {
                    $query .= " AND teacher_id = :teacher_id";
                }

                $stmt = $this->db->prepare($query);
                $stmt->bindParam(':status', $data['status']);
                $stmt->bindParam(':id', $id);
                if ($teacherId !== null) {
                    $stmt->bindParam(':teacher_id', $teacherId);
                }
                return $stmt->execute();
            }

            // Handle thumbnail path - store only filename in database
            if (!empty($data['thumbnail'])) {
                // Remove any directory path prefixes
                $data['thumbnail'] = basename($data['thumbnail']);

                // Move the uploaded file to the correct location if it exists
                $sourcePath = $_FILES['thumbnail']['tmp_name'];
                if (file_exists($sourcePath)) {
                    $targetDir = dirname(__DIR__) . '/admin/assets/images/uploads/courses/';
                    if (!is_dir($targetDir)) {
                        mkdir($targetDir, 0777, true);
                    }
                    move_uploaded_file($sourcePath, $targetDir . $data['thumbnail']);
                }
            }

            // Full course update (teacher action)
            $query = "UPDATE {$this->table} 
                     SET title = :title,
                         description = :description,
                         price = :price,
                         level = :level,
                         status = :status,
                         category_id = :category_id,
                         thumbnail = :thumbnail
                     WHERE id = :id AND teacher_id = :teacher_id";

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

    public function getAll($filters = [], $limit = null, $offset = 0)
    {
        try {
            $query = "SELECT c.*, u.first_name, u.last_name, cat.name as category_name,
                     (SELECT COUNT(*) FROM enrollments WHERE course_id = c.id) as enrollment_count 
                     FROM courses c 
                     LEFT JOIN users u ON c.teacher_id = u.id 
                     LEFT JOIN categories cat ON c.category_id = cat.id";

            $conditions = [];
            $params = [];

            if (!empty($filters['category_id'])) {
                $conditions[] = "c.category_id = :category_id";
                $params[':category_id'] = $filters['category_id'];
            }

            if (!empty($filters['status'])) {
                $conditions[] = "c.status = :status";
                $params[':status'] = $filters['status'];
            }

            if (!empty($filters['teacher_id'])) {
                $conditions[] = "c.teacher_id = :teacher_id";
                $params[':teacher_id'] = $filters['teacher_id'];
            }

            if (!empty($filters['search'])) {
                $conditions[] = "(c.title LIKE :search OR c.description LIKE :search)";
                $params[':search'] = "%" . $filters['search'] . "%";
            }

            if (!empty($conditions)) {
                $query .= " WHERE " . implode(' AND ', $conditions);
            }

            $query .= " ORDER BY c.created_at DESC";

            if ($limit !== null) {
                $query .= " LIMIT :limit OFFSET :offset";
                $params[':limit'] = (int)$limit;
                $params[':offset'] = (int)$offset;
            }

            $stmt = $this->db->prepare($query);

            foreach ($params as $key => &$value) {
                if ($key === ':limit' || $key === ':offset') {
                    $stmt->bindValue($key, $value, PDO::PARAM_INT);
                } else {
                    $stmt->bindValue($key, $value);
                }
            }

            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error getting courses: " . $e->getMessage());
            return [];
        }
    }

    // Get courses by teacher
    public function getByTeacher($teacher_id)
    {
        try {
            $query = "SELECT c.*, cat.name as category_name,
                     (SELECT COUNT(*) FROM enrollments WHERE course_id = c.id) as enrollment_count,
                     u.first_name, u.last_name,
                     CONCAT(u.first_name, ' ', u.last_name) as teacher_name
                     FROM {$this->table} c
                     LEFT JOIN categories cat ON c.category_id = cat.id
                     LEFT JOIN teachers t ON c.teacher_id = t.user_id
                     LEFT JOIN users u ON t.user_id = u.id
                     WHERE c.teacher_id = :teacher_id 
                     ORDER BY c.created_at DESC";

            $stmt = $this->db->prepare($query);
            $stmt->bindValue(':teacher_id', $teacher_id, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error getting teacher courses: " . $e->getMessage());
            return [];
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
        try {
            $query = "SELECT COUNT(*) as total FROM {$this->table} c WHERE 1=1";
            $params = [];

            if (!empty($filters['category_id'])) {
                $query .= " AND c.category_id = :category_id";
                $params[':category_id'] = $filters['category_id'];
            }

            if (!empty($filters['status'])) {
                $query .= " AND c.status = :status";
                $params[':status'] = $filters['status'];
            }

            if (!empty($filters['teacher_id'])) {
                $query .= " AND c.teacher_id = :teacher_id";
                $params[':teacher_id'] = $filters['teacher_id'];
            }

            if (!empty($filters['search'])) {
                $query .= " AND (c.title LIKE :search OR c.description LIKE :search)";
                $params[':search'] = "%" . $filters['search'] . "%";
            }

            $stmt = $this->db->prepare($query);
            foreach ($params as $key => &$value) {
                $stmt->bindValue($key, $value);
            }
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return (int)$result['total'];
        } catch (PDOException $e) {
            error_log("Error counting courses: " . $e->getMessage());
            return 0;
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

    public function addLesson($sectionId, $data)
    {
        try {
            $query = "INSERT INTO course_lessons (section_id, title, content_type, content, duration, sort_order) 
                      VALUES (:section_id, :title, :content_type, :content, :duration, :sort_order)";

            $stmt = $this->db->prepare($query);
            $stmt->execute([
                'section_id' => $sectionId,
                'title' => $data['title'],
                'content_type' => $data['content_type'],
                'content' => $data['content'],
                'duration' => $data['duration'] ?? null,
                'sort_order' => $data['sort_order'] ?? 0
            ]);

            return $this->db->lastInsertId();
        } catch (PDOException $e) {
            error_log("Error adding lesson: " . $e->getMessage());
            return false;
        }
    }

    public function updateLesson($lessonId, $data)
    {
        try {
            $query = "UPDATE course_lessons 
                      SET title = :title,
                          content_type = :content_type,
                          content = :content,
                          duration = :duration,
                          sort_order = :sort_order
                      WHERE id = :id";

            $stmt = $this->db->prepare($query);
            return $stmt->execute([
                'id' => $lessonId,
                'title' => $data['title'],
                'content_type' => $data['content_type'],
                'content' => $data['content'],
                'duration' => $data['duration'] ?? null,
                'sort_order' => $data['sort_order'] ?? 0
            ]);
        } catch (PDOException $e) {
            error_log("Error updating lesson: " . $e->getMessage());
            return false;
        }
    }

    public function addTag($courseId, $tagId)
    {
        try {
            $query = "INSERT INTO course_tags (course_id, tag_id) VALUES (:course_id, :tag_id)";
            $stmt = $this->db->prepare($query);
            return $stmt->execute([
                'course_id' => $courseId,
                'tag_id' => $tagId
            ]);
        } catch (PDOException $e) {
            error_log("Error adding course tag: " . $e->getMessage());
            return false;
        }
    }

    public function removeTag($courseId, $tagId)
    {
        try {
            $query = "DELETE FROM course_tags WHERE course_id = :course_id AND tag_id = :tag_id";
            $stmt = $this->db->prepare($query);
            return $stmt->execute([
                'course_id' => $courseId,
                'tag_id' => $tagId
            ]);
        } catch (PDOException $e) {
            error_log("Error removing course tag: " . $e->getMessage());
            return false;
        }
    }

    public function getCourseTags($courseId)
    {
        try {
            $query = "SELECT t.* FROM tags t 
                     INNER JOIN course_tags ct ON t.id = ct.tag_id 
                     WHERE ct.course_id = :course_id";
            $stmt = $this->db->prepare($query);
            $stmt->execute(['course_id' => $courseId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error getting course tags: " . $e->getMessage());
            return [];
        }
    }

    public function addBulkTags($courseId, array $tagIds)
    {
        try {
            // Begin transaction
            $this->db->beginTransaction();

            $query = "INSERT INTO course_tags (course_id, tag_id) VALUES (:course_id, :tag_id)";
            $stmt = $this->db->prepare($query);

            foreach ($tagIds as $tagId) {
                $stmt->execute([
                    'course_id' => $courseId,
                    'tag_id' => $tagId
                ]);
            }

            // Commit transaction
            $this->db->commit();
            return true;
        } catch (PDOException $e) {
            // Rollback on error
            $this->db->rollBack();
            error_log("Error adding bulk course tags: " . $e->getMessage());
            return false;
        }
    }
}
