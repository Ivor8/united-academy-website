<?php
require_once '../includes/header.php';

if (!hasPermission('approve_testimonials')) {
    header('Location: index.php');
    exit();
}

$pdo = getDB();
$testimonialId = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Get testimonial info
$stmt = $pdo->prepare("SELECT student_name, status FROM testimonials WHERE id = ?");
$stmt->execute([$testimonialId]);
$testimonial = $stmt->fetch();

if (!$testimonial) {
    header('Location: index.php');
    exit();
}

if ($testimonial['status'] === 'approved') {
    $_SESSION['info'] = 'Testimonial is already approved.';
    header('Location: index.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirm'])) {
    $approveStmt = $pdo->prepare("UPDATE testimonials SET status = 'approved' WHERE id = ?");
    if ($approveStmt->execute([$testimonialId])) {
        // Log activity
        $logStmt = $pdo->prepare("INSERT INTO activity_logs (user_id, action, table_name, record_id, new_data) VALUES (?, 'APPROVE', 'testimonials', ?, ?)");
        $logStmt->execute([$_SESSION['user_id'], $testimonialId, json_encode(['student_name' => $testimonial['student_name']])]);
        
        $_SESSION['success'] = 'Testimonial approved successfully!';
        header('Location: index.php');
        exit();
    } else {
        $error = 'Failed to approve testimonial. Please try again.';
    }
}
?>

<div class="form-container">
    <div class="card-header">
        <h1><i class="fas fa-check"></i> Approve Testimonial</h1>
        <a href="index.php" class="view-all">Back to Testimonials</a>
    </div>
    
    <?php if (isset($error)): ?>
        <div class="alert alert-error"><?php echo $error; ?></div>
    <?php endif; ?>
    
    <div class="approve-confirmation animate-slide-up">
        <div class="approve-warning">
            <i class="fas fa-check-circle" style="color: var(--green); font-size: 3rem; margin-bottom: 1rem;"></i>
            <h2>Approve this testimonial?</h2>
            <p><strong><?php echo htmlspecialchars($testimonial['student_name']); ?></strong></p>
            <p>Once approved, this testimonial will be visible on the public website.</p>
        </div>
        
        <form method="POST" class="approve-form">
            <input type="hidden" name="confirm" value="1">
            <div class="form-actions">
                <a href="index.php" class="btn btn-secondary">Cancel</a>
                <button type="submit" class="btn btn-success">
                    <i class="fas fa-check"></i> Approve Testimonial
                </button>
            </div>
        </form>
    </div>
</div>

<style>
.approve-confirmation {
    max-width: 600px;
    margin: 2rem auto;
}
.approve-warning {
    text-align: center;
    padding: 2rem;
    background: var(--light);
    border-radius: 12px;
    margin-bottom: 2rem;
}
.approve-warning h2 {
    color: var(--green);
    margin-bottom: 1rem;
}
.approve-warning p {
    margin-bottom: 0.5rem;
    color: var(--dark);
}
.approve-form {
    text-align: center;
}
.form-actions {
    display: flex;
    gap: 1rem;
    justify-content: center;
}
.btn {
    padding: 0.75rem 2rem;
    border: none;
    border-radius: 8px;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    font-weight: 500;
    cursor: pointer;
    transition: var(--transition);
}
.btn-secondary {
    background: var(--gray);
    color: white;
}
.btn-success {
    background: var(--green);
    color: white;
}
.btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
}
</style>

<?php require_once '../includes/footer.php'; ?>
