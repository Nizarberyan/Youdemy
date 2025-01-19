<?php
require_once '../config/database.php';
require_once '../classes/Course.php';

session_start();

if (!isset($_SESSION['user_id']) || !isset($_POST['course_id'])) {
    header('Location: index.php');
    exit;
}

$course = new Course();
$courseId = $_POST['course_id'];
$userId = $_SESSION['user_id'];

// Update the enrollment status to completed
try {
    $db = Database::getInstance()->getConnection();
    $stmt = $db->prepare("
        UPDATE enrollments 
        SET status = 'completed', 
            completed_at = NOW() 
        WHERE student_id = :student_id 
        AND course_id = :course_id
    ");

    $stmt->execute([
        ':student_id' => $userId,
        ':course_id' => $courseId
    ]);

    $_SESSION['message'] = 'Congratulations! You have completed the course.';
} catch (Exception $e) {
    $_SESSION['error'] = 'Error updating completion status.';
}

header('Location: view.php?id=' . $courseId);
exit;
