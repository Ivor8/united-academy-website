<?php
require_once '../includes/header.php';

if (!hasPermission('delete_blog')) {
    header('Location: index.php');
    exit();
}

$pdo = getDB();
$postId = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Get post info for logging
$stmt = $pdo->prepare("SELECT title FROM blog_posts WHERE id = ?");
$stmt->execute([$postId]);
$post = $stmt->fetch();

if (!$post) {
    header('Location: index.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirm'])) {
    // Delete post tags first
    $pdo->prepare("DELETE FROM blog_post_tags WHERE blog_post_id = ?")->execute([$postId]);
    
    // Delete post
    $deleteStmt = $pdo->prepare("DELETE FROM blog_posts WHERE id = ?");
    if ($deleteStmt->execute([$postId])) {
        // Log activity
        $logStmt = $pdo->prepare("INSERT INTO activity_logs (user_id, action, table_name, record_id, new_data) VALUES (?, 'DELETE', 'blog_posts', ?, ?)");
        $logStmt->execute([$_SESSION['user_id'], $postId, json_encode(['title' => $post['title']])]);
        
        $_SESSION['success'] = 'Blog post deleted successfully!';
        header('Location: index.php');
        exit();
    } else {
        $error = 'Failed to delete blog post. Please try again.';
    }
}
?>

<div class="form-container">
    <div class="card-header">
        <h1><i class="fas fa-trash"></i> Delete Blog Post</h1>
        <a href="index.php" class="view-all">Back to Posts</a>
    </div>
    
    <?php if (isset($error)): ?>
        <div class="alert alert-error"><?php echo $error; ?></div>
    <?php endif; ?>
    
    <div class="delete-confirmation animate-slide-up">
        <div class="delete-warning">
            <i class="fas fa-exclamation-triangle"></i>
            <h2>Are you sure you want to delete this blog post?</h2>
            <p><strong><?php echo htmlspecialchars($post['title']); ?></strong></p>
            <p>This action cannot be undone. All associated data including tags will be permanently removed.</p>
        </div>
        
        <form method="POST" class="delete-form">
            <input type="hidden" name="confirm" value="1">
            <div class="form-actions">
                <a href="index.php" class="btn btn-secondary">Cancel</a>
                <button type="submit" class="btn btn-danger">
                    <i class="fas fa-trash"></i> Delete Post
                </button>
            </div>
        </form>
    </div>
</div>

<style>
.delete-confirmation {
    max-width: 600px;
    margin: 2rem auto;
}
.delete-warning {
    text-align: center;
    padding: 2rem;
    background: var(--light);
    border-radius: 12px;
    margin-bottom: 2rem;
}
.delete-warning i {
    font-size: 3rem;
    color: var(--orange);
    margin-bottom: 1rem;
}
.delete-warning h2 {
    color: var(--red);
    margin-bottom: 1rem;
}
.delete-warning p {
    margin-bottom: 0.5rem;
    color: var(--dark);
}
.delete-form {
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
.btn-danger {
    background: var(--red);
    color: white;
}
.btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
}
</style>

<?php require_once '../includes/footer.php'; ?>
