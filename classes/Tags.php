<?php
class Tags
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    public function create($data)
    {
        try {
            $query = "INSERT INTO tags (name) VALUES (:name)";
            $stmt = $this->db->prepare($query);
            return $stmt->execute(['name' => $data['name']]);
        } catch (PDOException $e) {
            error_log("Error creating tag: " . $e->getMessage());
            return false;
        }
    }

    public function getAll()
    {
        try {
            $query = "SELECT * FROM tags ORDER BY name ASC";
            $stmt = $this->db->query($query);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error getting tags: " . $e->getMessage());
            return [];
        }
    }

    public function delete($id)
    {
        try {
            $query = "DELETE FROM tags WHERE id = :id";
            $stmt = $this->db->prepare($query);
            return $stmt->execute(['id' => $id]);
        } catch (PDOException $e) {
            error_log("Error deleting tag: " . $e->getMessage());
            return false;
        }
    }

    public function getTeacherTags($teacherId)
    {
        try {
            $query = "SELECT t.* FROM tags t 
                     INNER JOIN teacher_tags tt ON t.id = tt.tag_id 
                     WHERE tt.teacher_id = :teacher_id";
            $stmt = $this->db->prepare($query);
            $stmt->execute(['teacher_id' => $teacherId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error getting teacher tags: " . $e->getMessage());
            return [];
        }
    }

    public function addTeacherTag($teacherId, $tagId)
    {
        try {
            $query = "INSERT INTO teacher_tags (teacher_id, tag_id) VALUES (:teacher_id, :tag_id)";
            $stmt = $this->db->prepare($query);
            return $stmt->execute([
                'teacher_id' => $teacherId,
                'tag_id' => $tagId
            ]);
        } catch (PDOException $e) {
            error_log("Error adding teacher tag: " . $e->getMessage());
            return false;
        }
    }

    public function removeTeacherTag($teacherId, $tagId)
    {
        try {
            $query = "DELETE FROM teacher_tags WHERE teacher_id = :teacher_id AND tag_id = :tag_id";
            $stmt = $this->db->prepare($query);
            return $stmt->execute([
                'teacher_id' => $teacherId,
                'tag_id' => $tagId
            ]);
        } catch (PDOException $e) {
            error_log("Error removing teacher tag: " . $e->getMessage());
            return false;
        }
    }
}
