<?php
require_once '../includes/config.php';
require_once '../includes/auth.php';
requireLogin();

if (!hasPermission('view_course_enrollments')) {
    http_response_code(403);
    echo 'Access denied';
    exit();
}

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    http_response_code(400);
    echo 'Invalid enrollment ID';
    exit();
}

$id = intval($_GET['id']);
$pdo = getDB();

$stmt = $pdo->prepare("
    SELECT ce.*, oc.title as course_title, oc.category, oc.level, oc.duration, oc.price, oc.currency
    FROM course_enrollments ce
    LEFT JOIN online_courses oc ON ce.course_id = oc.id
    WHERE ce.id = ?
");
$stmt->execute([$id]);
$enrollment = $stmt->fetch();

if (!$enrollment) {
    http_response_code(404);
    echo 'Enrollment not found';
    exit();
}
?>

<div class="enrollment-details">
    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 2rem; margin-bottom: 2rem;">
        <div>
            <h3 style="color: #1E64C8; margin-bottom: 1rem;"><i class="fas fa-user"></i> Student Information</h3>
            <div style="background: rgba(248,250,252,0.5); padding: 1rem; border-radius: 8px;">
                <p><strong>Name:</strong> <?php echo htmlspecialchars($enrollment['student_name']); ?></p>
                <p><strong>Email:</strong> <?php echo htmlspecialchars($enrollment['student_email']); ?></p>
                <?php if ($enrollment['student_phone']): ?>
                    <p><strong>Phone:</strong> <?php echo htmlspecialchars($enrollment['student_phone']); ?></p>
                <?php endif; ?>
            </div>
        </div>
        
        <div>
            <h3 style="color: #1E64C8; margin-bottom: 1rem;"><i class="fas fa-graduation-cap"></i> Course Information</h3>
            <div style="background: rgba(248,250,252,0.5); padding: 1rem; border-radius: 8px;">
                <p><strong>Course:</strong> <?php echo htmlspecialchars($enrollment['course_title']); ?></p>
                <p><strong>Category:</strong> <?php echo ucfirst($enrollment['category']); ?></p>
                <p><strong>Level:</strong> <?php echo ucfirst($enrollment['level']); ?></p>
                <p><strong>Duration:</strong> <?php echo $enrollment['duration']; ?></p>
                <p><strong>Price:</strong> <?php echo number_format($enrollment['price'], 0); ?> <?php echo $enrollment['currency']; ?></p>
            </div>
        </div>
    </div>
    
    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 2rem; margin-bottom: 2rem;">
        <div>
            <h3 style="color: #1E64C8; margin-bottom: 1rem;"><i class="fas fa-chart-line"></i> Enrollment Progress</h3>
            <div style="background: rgba(248,250,252,0.5); padding: 1rem; border-radius: 8px;">
                <p><strong>Status:</strong> 
                    <span class="status-badge status-<?php echo $enrollment['status']; ?>" style="display: inline-block; padding: 0.25rem 0.5rem; border-radius: 12px; font-size: 0.75rem; font-weight: 600;">
                        <?php echo ucfirst($enrollment['status']); ?>
                    </span>
                </p>
                <p><strong>Enrollment Date:</strong> <?php echo date('F j, Y', strtotime($enrollment['enrollment_date'])); ?></p>
                <p><strong>Completion:</strong> <?php echo $enrollment['completion_percentage']; ?>%</p>
                <?php if ($enrollment['certificate_issued']): ?>
                    <p><strong>Certificate:</strong> <i class="fas fa-check-circle" style="color: #2E7D32;"></i> Issued</p>
                    <?php if ($enrollment['certificate_url']): ?>
                        <p><strong>Certificate URL:</strong> <a href="<?php echo htmlspecialchars($enrollment['certificate_url']); ?>" target="_blank">View Certificate</a></p>
                    <?php endif; ?>
                <?php else: ?>
                    <p><strong>Certificate:</strong> <i class="fas fa-times-circle" style="color: #D32F2F;"></i> Not Issued</p>
                <?php endif; ?>
            </div>
        </div>
        
        <div>
            <h3 style="color: #1E64C8; margin-bottom: 1rem;"><i class="fas fa-sticky-note"></i> Additional Information</h3>
            <div style="background: rgba(248,250,252,0.5); padding: 1rem; border-radius: 8px;">
                <?php if ($enrollment['notes']): ?>
                    <p><strong>Notes:</strong></p>
                    <p style="background: white; padding: 0.5rem; border-radius: 4px; border-left: 3px solid #1E64C8;">
                        <?php echo nl2br(htmlspecialchars($enrollment['notes'])); ?>
                    </p>
                <?php else: ?>
                    <p><em>No additional notes</em></p>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <div style="background: rgba(248,250,252,0.5); padding: 1rem; border-radius: 8px;">
        <p style="margin: 0; font-size: 0.85rem; color: #64748b;">
            <strong>Last Updated:</strong> <?php echo date('F j, Y H:i', strtotime($enrollment['updated_at'])); ?>
        </p>
    </div>
</div>

<style>
.status-active { background: rgba(46,125,50,0.15); color: #15803D; }
.status-completed { background: rgba(21,101,192,0.15); color: #1D4ED8; }
.status-dropped { background: rgba(211,47,47,0.15); color: #DC2626; }
.status-suspended { background: rgba(245,124,0,0.15); color: #EA580C; }
</style>