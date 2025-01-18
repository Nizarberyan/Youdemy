<?php
class Teacher extends User
{
    private $teacherTable = 'teachers';

    public function __construct($userId = null)
    {
        parent::__construct();
        if ($userId) {
            $this->id = $userId;
            $this->loadTeacherData();
        }
    }

    private function loadTeacherData()
    {
        $query = "SELECT t.*, u.* FROM {$this->teacherTable} t
                  JOIN {$this->table} u ON t.user_id = u.id
                  WHERE t.user_id = :id";
        try {
            $stmt = $this->db->prepare($query);
            $stmt->execute(['id' => $this->id]);
            $data = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($data) {
                foreach ($data as $key => $value) {
                    if (property_exists($this, $key)) {
                        $this->$key = $value;
                    }
                }
            }
        } catch (PDOException $e) {
            error_log("Error loading teacher data: " . $e->getMessage());
        }
    }

    public function getSpecificData()
    {
        $query = "SELECT * FROM {$this->teacherTable} WHERE user_id = :user_id";
        try {
            $stmt = $this->db->prepare($query);
            $stmt->execute(['user_id' => $this->id]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error getting teacher data: " . $e->getMessage());
            return false;
        }
    }

    public function updateSpecificData($data)
    {
        try {
            // First check if teacher record exists
            $checkQuery = "SELECT user_id FROM {$this->teacherTable} WHERE user_id = :user_id";
            $stmt = $this->db->prepare($checkQuery);
            $stmt->execute(['user_id' => $this->id]);

            if ($stmt->fetch()) {
                // Update existing record
                $query = "UPDATE {$this->teacherTable} 
                         SET bio = :bio, specialization = :specialization 
                         WHERE user_id = :user_id";
            } else {
                // Insert new record
                $query = "INSERT INTO {$this->teacherTable} 
                         (user_id, bio, specialization) 
                         VALUES (:user_id, :bio, :specialization)";
            }

            $stmt = $this->db->prepare($query);
            return $stmt->execute([
                'user_id' => $this->id,
                'bio' => $data['bio'] ?? null,
                'specialization' => $data['specialization'] ?? null
            ]);
        } catch (PDOException $e) {
            error_log("Error updating teacher data: " . $e->getMessage());
            return false;
        }
    }

    public function getCourses()
    {
        $query = "SELECT c.*, cat.name as category_name,
                 (SELECT COUNT(*) FROM enrollments WHERE course_id = c.id) as enrollment_count
                 FROM courses c
                 LEFT JOIN categories cat ON c.category_id = cat.id
                 WHERE c.teacher_id = :teacher_id
                 ORDER BY c.created_at DESC";

        try {
            $stmt = $this->db->prepare($query);
            $stmt->execute(['teacher_id' => $this->id]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error getting teacher courses: " . $e->getMessage());
            return [];
        }
    }

    public function getStudents()
    {
        $query = "SELECT DISTINCT u.* 
                 FROM users u
                 JOIN enrollments e ON u.id = e.student_id
                 JOIN courses c ON e.course_id = c.id
                 WHERE c.teacher_id = :teacher_id";

        try {
            $stmt = $this->db->prepare($query);
            $stmt->execute(['teacher_id' => $this->id]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error getting teacher's students: " . $e->getMessage());
            return [];
        }
    }
}
