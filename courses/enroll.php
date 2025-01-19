<?php
require_once '../config/database.php';
require_once '../classes/Auth.php';
require_once '../classes/Course.php';

session_start();

$auth = new Auth();
$course = new Course();

// Extensive logging
error_log("Enrollment attempt started");

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    error_log("Enrollment failed: User not logged in");
    $_SESSION['error'] = 'You must be logged in to enroll in a course.';
    header('Location: ../login.php');
    exit;
}

// Check if course ID is provided
if (!isset($_POST['course_id'])) {
    error_log("Enrollment failed: No course ID provided");
    $_SESSION['error'] = 'Invalid course selection.';
    header('Location: index.php');
    exit;
}

$courseId = $_POST['course_id'];
$userId = $_SESSION['user_id'];

error_log("Enrollment attempt - User ID: $userId, Course ID: $courseId");

try {
    // More comprehensive debugging with exception handling
    try {
        $conditionsCheck = $course->checkEnrollmentConditions($userId, $courseId);
    } catch (Exception $e) {
        // Log the specific condition check failure
        error_log("Enrollment Conditions Check Failed: " . $e->getMessage());

        // Set a more user-friendly error message
        $_SESSION['error'] = $e->getMessage();
        header('Location: view.php?id=' . $courseId);
        exit;
    }

    // Log detailed conditions
    error_log("Enrollment Conditions Check: " . json_encode($conditionsCheck));

    // Additional validation based on conditions
    if (!$conditionsCheck) {
        throw new Exception("Unable to verify enrollment conditions");
    }

    // Detailed debugging
    $debugInfo = $course->debugEnrollment($userId, $courseId);

    // Check if course exists and is published
    $courseData = $course->getById($courseId);
    if (!$courseData || $courseData['status'] !== 'published') {
        error_log("Enrollment failed: Course not available");
        throw new Exception('Course is not available.');
    }

    // Check if user is already enrolled
    if ($course->isStudentEnrolled($userId, $courseId)) {
        $_SESSION['message'] = 'You are already enrolled in this course.';
        header('Location: view.php?id=' . $courseId);
        exit;
    }

    // Attempt to enroll the student
    $enrollmentResult = $course->enrollStudent($userId, $courseId);

    if ($enrollmentResult) {
        error_log("Enrollment successful");
        $_SESSION['message'] = 'Successfully enrolled in the course!';
        header('Location: learn.php?id=' . $courseId);
        exit;
    } else {
        // Log additional details for debugging
        error_log("Enrollment failed for User ID: $userId, Course ID: $courseId");
        error_log("Debug Info: " . json_encode($debugInfo));
        throw new Exception('Enrollment failed. Please contact support with the following details: ' .
            "User ID: $userId, Course ID: $courseId");
    }
} catch (Exception $e) {
    error_log("Enrollment exception: " . $e->getMessage());
    $_SESSION['error'] = $e->getMessage();
    header('Location: view.php?id=' . $courseId);
    exit;
}
