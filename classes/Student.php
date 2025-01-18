<?php
class Student extends User
{
    private $studentTable = 'students';

    public function __construct($userId = null)
    {
        parent::__construct();
        if ($userId) {
            $this->id = $userId;
            $this->loadStudentData();
        }
    }

    private function loadStudentData()
    {
        $query = "SELECT s.*, u.* FROM {$this->studentTable} s
                  JOIN {$this->table} u ON s.user_id = u.id
                  WHERE s.user_id = :id";
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
            error_log("Error loading student data: " . $e->getMessage());
        }
    }

    public function getSpecificData()
    {
        $query = "SELECT * FROM {$this->studentTable} WHERE user_id = :user_id";
        try {
            $stmt = $this->db->prepare($query);
            $stmt->execute(['user_id' => $this->id]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error getting student data: " . $e->getMessage());
            return false;
        }
    }

    public function updateSpecificData($data)
    {
        try {
            $checkQuery = "SELECT user_id FROM {$this->studentTable} WHERE user_id = :user_id";
            $stmt = $this->db->prepare($checkQuery);
            $stmt->execute(['user_id' => $this->id]);

            if ($stmt->fetch()) {
                $query = "UPDATE {$this->studentTable} 
                         SET education_level = :education_level 
                         WHERE user_id = :user_id";
            } else {
                $query = "INSERT INTO {$this->studentTable} 
                         (user_id, education_level) 
                         VALUES (:user_id, :education_level)";
            }

            $stmt = $this->db->prepare($query);
            return $stmt->execute([
                'user_id' => $this->id,
                'education_level' => $data['education_level'] ?? null
            ]);
        } catch (PDOException $e) {
            error_log("Error updating student data: " . $e->getMessage());
            return false;
        }
    }
}
