<?php
$pageTitle = 'Testimonials Management';
require_once '../includes/header.php';

if (!hasPermission('view_testimonials')) {
    header('Location: ../index.php');
    exit();
}

$pdo = getDB();

// Handle status filter
$statusFilter = isset($_GET['status']) ? $_GET['status'] : 'all';
$searchTerm = isset($_GET['search']) ? sanitize($_GET['search']) : '';

$query = "SELECT t.*, u.first_name, u.last_name 
          FROM testimonials t 
          LEFT JOIN users u ON t.author_id = u.id 
          WHERE 1=1";
$params = [];

if ($statusFilter !== 'all') {
    $query .= " AND t.status = ?";
    $params[] = $statusFilter;
}

if ($searchTerm) {
    $query .= " AND (t.student_name LIKE ? OR t.testimonial_text LIKE ?)";
    $searchPattern = "%$searchTerm%";
    $params[] = $searchPattern;
    $params[] = $searchPattern;
}

$query .= " ORDER BY t.created_at DESC";
$stmt = $pdo->prepare($query);
$stmt->execute($params);
$testimonials = $stmt->fetchAll();

// Handle bulk approval if permission exists
if (hasPermission('approve_testimonials') && isset($_POST['bulk_action']) && isset($_POST['selected_ids'])) {
    $ids = $_POST['selected_ids'];
    $action = $_POST['bulk_action'];
    
    if ($action === 'approve') {
        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $updateStmt = $pdo->prepare("UPDATE testimonials SET status = 'approved' WHERE id IN ($placeholders)");
        $updateStmt->execute($ids);
        $success = count($ids) . ' testimonials approved.';
    } elseif ($action === 'delete' && hasPermission('delete_testimonials')) {
        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $deleteStmt = $pdo->prepare("DELETE FROM testimonials WHERE id IN ($placeholders)");
        $deleteStmt->execute($ids);
        $success = count($ids) . ' testimonials deleted.';
    }
}

$extraCss = '<style>
    .testimonials-filters {
        display: flex;
        gap: 1rem;
        flex-wrap: wrap;
        margin-bottom: 2rem;
        align-items: center;
    }
    .filter-badge {
        padding: 0.5rem 1rem;
        background: var(--light);
        border-radius: 40px;
        text-decoration: none;
        color: var(--dark);
        transition: var(--transition);
    }
    .filter-badge.active,
    .filter-badge:hover {
        background: var(--blue);
        color: white;
    }
    .bulk-actions {
        display: flex;
        gap: 0.5rem;
        margin-left: auto;
    }
    .bulk-btn {
        padding: 0.5rem 1rem;
        border: none;
        border-radius: 8px;
        cursor: pointer;
        font-weight: 500;
    }
    .approve-btn {
        background: var(--green);
        color: white;
    }
    .delete-bulk-btn {
        background: var(--red);
        color: white;
    }
    .testimonials-table {
        width: 100%;
        overflow-x: auto;
    }
    .testimonials-table table {
        width: 100%;
        border-collapse: collapse;
    }
    .testimonials-table th,
    .testimonials-table td {
        padding: 1rem;
        text-align: left;
        border-bottom: 1px solid #E2E8F0;
    }
    .testimonials-table th {
        background: var(--light);
        font-weight: 600;
    }
    .testimonials-table tr:hover {
        background: var(--light);
    }
    .student-avatar {
        width: 50px;
        height: 50px;
        border-radius: 50%;
        object-fit: cover;
    }
    .rating-stars {
        color: #FBBF24;
        font-size: 0.85rem;
    }
    .status-badge {
        padding: 0.25rem 0.75rem;
        border-radius: 20px;
        font-size: 0.75rem;
        font-weight: 600;
    }
    .status-badge.approved {
        background: rgba(46, 125, 50, 0.1);
        color: var(--green);
    }
    .status-badge.pending {
        background: rgba(243, 156, 18, 0.1);
        color: var(--orange);
    }
    .status-badge.rejected {
        background: rgba(100, 116, 139, 0.1);
        color: var(--gray);
    }
    .action-buttons {
        display: flex;
        gap: 0.5rem;
    }
    .action-btn {
        padding: 0.5rem;
        border-radius: 8px;
        color: white;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 0.3rem;
        font-size: 0.85rem;
    }
    .edit-btn {
        background: var(--blue);
    }
    .delete-btn {
        background: var(--red);
    }
    .approve-single-btn {
        background: var(--green);
    }
    .featured-star {
        color: #FBBF24;
        font-size: 1.2rem;
    }
    .select-all {
        margin: 0;
    }
</style>';
?>

<div class="testimonials-management">
    <div class="card-header">
        <h1><i class="fas fa-star"></i> Testimonials Management</h1>
        <?php if (hasPermission('create_testimonials')): ?>
        <a href="create.php" class="view-all" style="background: var(--blue); color: white; padding: 0.75rem 1.5rem; border-radius: 40px;">
            <i class="fas fa-plus"></i> Add Testimonial
        </a>
        <?php endif; ?>
    </div>
    
    <?php if (isset($success)): ?>
        <div class="alert alert-success"><?php echo $success; ?></div>
    <?php endif; ?>
    
    <form method="POST" id="bulkForm">
        <div class="testimonials-filters">
            <a href="?status=all" class="filter-badge <?php echo $statusFilter === 'all' ? 'active' : ''; ?>">
                All
            </a>
            <a href="?status=approved" class="filter-badge <?php echo $statusFilter === 'approved' ? 'active' : ''; ?>">
                Approved
            </a>
            <a href="?status=pending" class="filter-badge <?php echo $statusFilter === 'pending' ? 'active' : ''; ?>">
                Pending
            </a>
            <a href="?status=rejected" class="filter-badge <?php echo $statusFilter === 'rejected' ? 'active' : ''; ?>">
                Rejected
            </a>
            
            <div class="search-form">
                <input type="text" name="search" placeholder="Search testimonials..." value="<?php echo htmlspecialchars($searchTerm); ?>">
                <button type="submit"><i class="fas fa-search"></i></button>
            </div>
            
            <?php if (hasPermission('approve_testimonials')): ?>
            <div class="bulk-actions">
                <button type="button" class="bulk-btn approve-btn" onclick="submitBulkAction('approve')">
                    <i class="fas fa-check"></i> Approve Selected
                </button>
                <?php if (hasPermission('delete_testimonials')): ?>
                <button type="button" class="bulk-btn delete-bulk-btn" onclick="submitBulkAction('delete')">
                    <i class="fas fa-trash"></i> Delete Selected
                </button>
                <?php endif; ?>
            </div>
            <?php endif; ?>
        </div>
        
        <div class="testimonials-table">
            <table>
                <thead>
                    <tr>
                        <?php if (hasPermission('approve_testimonials')): ?>
                        <th width="40">
                            <input type="checkbox" id="selectAll" class="select-all">
                        </th>
                        <?php endif; ?>
                        <th>Avatar</th>
                        <th>Student Name</th>
                        <th>Testimonial</th>
                        <th>Rating</th>
                        <th>Status</th>
                        <th>Featured</th>
                        <th>Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($testimonials) > 0): ?>
                        <?php foreach ($testimonials as $testimonial): ?>
                        <tr>
                            <?php if (hasPermission('approve_testimonials')): ?>
                            <td>
                                <input type="checkbox" name="selected_ids[]" value="<?php echo $testimonial['id']; ?>" class="testimonial-checkbox">
                            </td>
                            <?php endif; ?>
                            <td>
                                <?php if ($testimonial['student_avatar']): ?>
                                    <img src="<?php echo $testimonial['student_avatar']; ?>" alt="" class="student-avatar">
                                <?php else: ?>
                                    <div class="student-avatar" style="background: var(--light); display: flex; align-items: center; justify-content: center;">
                                        <i class="fas fa-user"></i>
                                    </div>
                                <?php endif; ?>
                            </td>
                            <td>
                                <strong><?php echo htmlspecialchars($testimonial['student_name']); ?></strong>
                                <?php if ($testimonial['student_program']): ?>
                                    <br><small><?php echo htmlspecialchars($testimonial['student_program']); ?></small>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div style="max-width: 300px;">
                                    <?php echo htmlspecialchars(truncate($testimonial['testimonial_text'], 80)); ?>
                                    <?php if ($testimonial['media_type'] === 'video' && $testimonial['media_url']): ?>
                                        <br><small><i class="fas fa-video"></i> Video included</small>
                                    <?php endif; ?>
                                </div>
                            </td>
                            <td>
                                <div class="rating-stars">
                                    <?php for ($i = 1; $i <= 5; $i++): ?>
                                        <?php if ($i <= $testimonial['rating']): ?>
                                            <i class="fas fa-star"></i>
                                        <?php else: ?>
                                            <i class="far fa-star"></i>
                                        <?php endif; ?>
                                    <?php endfor; ?>
                                </div>
                            </td>
                            <td>
                                <span class="status-badge <?php echo $testimonial['status']; ?>">
                                    <?php echo ucfirst($testimonial['status']); ?>
                                </span>
                            </td>
                            <td>
                                <?php if ($testimonial['featured']): ?>
                                    <i class="fas fa-star featured-star"></i>
                                <?php else: ?>
                                    <i class="far fa-star" style="color: #CBD5E1;"></i>
                                <?php endif; ?>
                            </td>
                            <td><?php echo formatDate($testimonial['created_at']); ?></td>
                            <td class="action-buttons">
                                <a href="edit.php?id=<?php echo $testimonial['id']; ?>" class="action-btn edit-btn" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <?php if (hasPermission('approve_testimonials') && $testimonial['status'] === 'pending'): ?>
                                <a href="approve.php?id=<?php echo $testimonial['id']; ?>" class="action-btn approve-single-btn" title="Approve">
                                    <i class="fas fa-check"></i>
                                </a>
                                <?php endif; ?>
                                <?php if (hasPermission('delete_testimonials')): ?>
                                <a href="delete.php?id=<?php echo $testimonial['id']; ?>" class="action-btn delete-btn" title="Delete" onclick="return confirm('Are you sure?')">
                                    <i class="fas fa-trash"></i>
                                </a>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="<?php echo hasPermission('approve_testimonials') ? '9' : '8'; ?>" style="text-align: center; padding: 3rem;">
                                <i class="fas fa-star" style="font-size: 3rem; opacity: 0.5;"></i>
                                <p>No testimonials found.</p>
                                <?php if (hasPermission('create_testimonials')): ?>
                                <a href="create.php" class="action-btn edit-btn" style="margin-top: 1rem;">Add your first testimonial</a>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <input type="hidden" name="bulk_action" id="bulkAction">
    </form>
</div>

<script>
    // Select all functionality
    const selectAll = document.getElementById('selectAll');
    const checkboxes = document.querySelectorAll('.testimonial-checkbox');
    
    if (selectAll) {
        selectAll.addEventListener('change', function() {
            checkboxes.forEach(cb => cb.checked = this.checked);
        });
    }
    
    function submitBulkAction(action) {
        const checked = document.querySelectorAll('.testimonial-checkbox:checked');
        if (checked.length === 0) {
            alert('Please select at least one testimonial.');
            return;
        }
        
        if (confirm(`Are you sure you want to ${action} the selected testimonials?`)) {
            document.getElementById('bulkAction').value = action;
            document.getElementById('bulkForm').submit();
        }
    }
</script>

<?php require_once '../includes/footer.php'; ?>