<?php
require_once '../includes/config.php';
require_once '../includes/auth.php';
requireLogin();

if (!hasPermission('manage_course_enrollments')) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Access denied']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit();
}

if (!isset($_POST['id']) || !is_numeric($_POST['id'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid enrollment ID']);
    exit();
}

$id = intval($_POST['id']);
$status = isset($_POST['status']) ? sanitize($_POST['status']) : '';

$validStatuses = ['active', 'completed', 'dropped', 'suspended'];
if (!in_array($status, $validStatuses)) {
    echo json_encode(['success' => false, 'message' => 'Invalid status']);
    exit();
}

$pdo = getDB();

try {
    // Update enrollment status
    $stmt = $pdo->prepare("UPDATE course_enrollments SET status = ?, updated_at = NOW() WHERE id = ?");
    $stmt->execute([$status, $id]);
    
    // Log the activity
    $userId = $_SESSION['user_id'];
    $logStmt = $pdo->prepare("INSERT INTO activity_logs (user_id, action, details, created_at) VALUES (?, 'update_enrollment_status', ?, NOW())");
    $logStmt->execute([$userId, "Updated enrollment ID $id to status: $status"]);
    
    echo json_encode(['success' => true, 'message' => 'Enrollment status updated successfully']);
} catch (Exception $e) {
    error_log("Error updating enrollment status: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Failed to update enrollment status']);
}
?>