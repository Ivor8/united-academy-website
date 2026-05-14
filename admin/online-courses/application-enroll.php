<?php
require_once '../includes/config.php';
require_once '../includes/auth.php';
requireLogin();

if (!hasPermission('manage_course_applications')) {
    echo json_encode(['success' => false, 'message' => 'Access denied']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit();
}

if (!isset($_POST['id']) || !is_numeric($_POST['id'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid application ID']);
    exit();
}

$id = intval($_POST['id']);
$pdo = getDB();

try {
    // Get application details
    $stmt = $pdo->prepare("SELECT * FROM course_applications WHERE id = ? AND status = 'accepted'");
    $stmt->execute([$id]);
    $application = $stmt->fetch();
    
    if (!$application) {
        echo json_encode(['success' => false, 'message' => 'Application not found or not accepted']);
        exit();
    }
    
    // Check if already enrolled
    $checkStmt = $pdo->prepare("SELECT id FROM course_enrollments WHERE course_id = ? AND student_email = ?");
    $checkStmt->execute([$application['course_id'], $application['email']]);
    if ($checkStmt->fetch()) {
        echo json_encode(['success' => false, 'message' => 'Student is already enrolled in this course']);
        exit();
    }
    
    // Insert into enrollments
    $enrollStmt = $pdo->prepare("
        INSERT INTO course_enrollments 
        (course_id, student_name, student_email, student_phone, enrollment_date, status, notes, created_at, updated_at) 
        VALUES (?, ?, ?, ?, CURDATE(), 'active', ?, NOW(), NOW())
    ");
    $enrollStmt->execute([
        $application['course_id'],
        $application['first_name'] . ' ' . $application['last_name'],
        $application['email'],
        $application['phone'],
        'Enrolled from accepted application'
    ]);
    
    // Update application status to enrolled
    $updateStmt = $pdo->prepare("UPDATE course_applications SET status = 'enrolled', updated_at = NOW() WHERE id = ?");
    $updateStmt->execute([$id]);
    
    // Update course current_enrollments count
    $updateCourseStmt = $pdo->prepare("UPDATE online_courses SET current_enrollments = current_enrollments + 1 WHERE id = ?");
    $updateCourseStmt->execute([$application['course_id']]);
    
    // Log the activity
    $userId = $_SESSION['user_id'];
    $logStmt = $pdo->prepare("INSERT INTO activity_logs (user_id, action, details, created_at) VALUES (?, 'enroll_student', ?, NOW())");
    $logStmt->execute([$userId, "Enrolled student {$application['first_name']} {$application['last_name']} in course ID {$application['course_id']}"]);
    
    echo json_encode(['success' => true, 'message' => 'Student enrolled successfully']);
} catch (Exception $e) {
    error_log("Error enrolling student: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Failed to enroll student']);
}
?>