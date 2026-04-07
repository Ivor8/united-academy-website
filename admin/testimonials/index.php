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

$extraCss = '';

?>

<div class="testimonials-management">
    <style>
        :root {
            --blue: #1E64C8;
            --blue-light: #E3F2FD;
            --green: #2E7D32;
            --green-light: #E8F5E9;
            --red: #D32F2F;
            --red-light: #FFEBEE;
            --orange: #F39C12;
            --orange-light: #FFF3E0;
            --dark: #1E293B;
            --gray: #64748B;
            --light: #F8FAFC;
            --white: #FFFFFF;
            --transition: all 0.3s ease;
        }

        /* Testimonials Management Container */
        .testimonials-management {
            padding: 2rem;
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            min-height: 100vh;
        }

        /* Card Header Styling */
        .card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2.5rem;
            padding: 2rem;
            background: white;
            border-radius: 16px;
            box-shadow: 0 8px 32px rgba(0,0,0,0.08);
            border-left: 6px solid var(--blue);
            animation: slideDown 0.4s ease-out;
        }

        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .card-header h1 {
            color: var(--dark);
            font-size: 1.75rem;
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            letter-spacing: -0.01em;
        }

        .card-header h1 i {
            color: var(--blue);
            font-size: 2rem;
        }

        .view-all {
            background: linear-gradient(135deg, var(--blue) 0%, #1565c0 100%);
            color: white;
            padding: 0.85rem 1.75rem;
            border-radius: 50px;
            text-decoration: none;
            font-weight: 600;
            transition: var(--transition);
            display: inline-flex;
            align-items: center;
            gap: 0.6rem;
            box-shadow: 0 4px 16px rgba(30, 100, 200, 0.25);
            font-size: 0.95rem;
        }

        .view-all:hover {
            background: linear-gradient(135deg, #1565c0 0%, #0d47a1 100%);
            transform: translateY(-3px);
            box-shadow: 0 6px 24px rgba(30, 100, 200, 0.35);
            color: white;
        }

        .view-all i {
            font-size: 1.1rem;
        }

        /* Filters Section */
        .testimonials-filters {
            display: flex;
            gap: 1.25rem;
            flex-wrap: wrap;
            margin-bottom: 2.5rem;
            align-items: center;
            padding: 1.75rem;
            background: white;
            border-radius: 16px;
            box-shadow: 0 8px 32px rgba(0,0,0,0.08);
            animation: slideDown 0.5s ease-out 0.1s backwards;
        }

        .filter-badge {
            padding: 0.65rem 1.3rem;
            background: var(--light);
            border-radius: 50px;
            text-decoration: none;
            color: var(--dark);
            transition: var(--transition);
            border: 2px solid #E2E8F0;
            font-weight: 600;
            font-size: 0.9rem;
            cursor: pointer;
            position: relative;
            overflow: hidden;
        }

        .filter-badge::before {
            content: "";
            position: absolute;
            top: 50%;
            left: 50%;
            width: 0;
            height: 0;
            background: rgba(30, 100, 200, 0.2);
            border-radius: 50%;
            transform: translate(-50%, -50%);
            transition: width 0.5s, height 0.5s;
        }

        .filter-badge:hover::before,
        .filter-badge.active::before {
            width: 300px;
            height: 300px;
        }

        .filter-badge.active {
            background: linear-gradient(135deg, var(--blue) 0%, #1565c0 100%);
            color: white;
            border-color: var(--blue);
            transform: translateY(-2px);
            box-shadow: 0 4px 16px rgba(30, 100, 200, 0.25);
            z-index: 1;
        }

        .filter-badge:hover {
            border-color: var(--blue);
            transform: translateY(-2px);
        }

        /* Search Form */
        .search-form {
            margin-left: auto;
            display: flex;
            gap: 0.75rem;
        }

        .search-form input {
            padding: 0.7rem 1.25rem;
            border: 2px solid #E2E8F0;
            border-radius: 50px;
            outline: none;
            min-width: 280px;
            transition: var(--transition);
            font-size: 0.9rem;
            background: white;
            font-family: inherit;
        }

        .search-form input:focus {
            border-color: var(--blue);
            background: var(--blue-light);
            box-shadow: 0 0 0 4px rgba(30, 100, 200, 0.1);
        }

        .search-form button {
            padding: 0.7rem 1.5rem;
            background: linear-gradient(135deg, var(--blue) 0%, #1565c0 100%);
            color: white;
            border: none;
            border-radius: 50px;
            cursor: pointer;
            transition: var(--transition);
            font-weight: 600;
            box-shadow: 0 4px 16px rgba(30, 100, 200, 0.25);
        }

        .search-form button:hover {
            background: linear-gradient(135deg, #1565c0 0%, #0d47a1 100%);
            transform: translateY(-2px);
            box-shadow: 0 6px 24px rgba(30, 100, 200, 0.35);
        }

        /* Bulk Actions */
        .bulk-actions {
            display: flex;
            gap: 0.75rem;
        }

        .bulk-btn {
            padding: 0.7rem 1.25rem;
            border: none;
            border-radius: 50px;
            cursor: pointer;
            font-weight: 600;
            transition: var(--transition);
            font-size: 0.9rem;
            box-shadow: 0 4px 16px rgba(0,0,0,0.15);
        }

        .approve-btn {
            background: linear-gradient(135deg, var(--green) 0%, #1b5e20 100%);
            color: white;
        }

        .approve-btn:hover {
            background: linear-gradient(135deg, #1b5e20 0%, #004d40 100%);
            transform: translateY(-2px);
            box-shadow: 0 6px 24px rgba(46, 125, 50, 0.35);
        }

        .delete-bulk-btn {
            background: linear-gradient(135deg, var(--red) 0%, #c62828 100%);
            color: white;
        }

        .delete-bulk-btn:hover {
            background: linear-gradient(135deg, #c62828 0%, #ad1457 100%);
            transform: translateY(-2px);
            box-shadow: 0 6px 24px rgba(211, 47, 47, 0.35);
        }

        /* Testimonials Table */
        .testimonials-table {
            width: 100%;
            background: white;
            border-radius: 16px;
            box-shadow: 0 12px 40px rgba(0,0,0,0.1);
            overflow: hidden;
            animation: slideUp 0.5s ease-out 0.2s backwards;
        }

        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .testimonials-table table {
            width: 100%;
            border-collapse: collapse;
        }

        .testimonials-table th {
            background: linear-gradient(135deg, #f5f7fa 0%, #e8eef5 100%);
            padding: 1.25rem;
            text-align: left;
            font-weight: 700;
            color: var(--dark);
            font-size: 0.8rem;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            border-bottom: 2px solid #E2E8F0;
        }

        .testimonials-table th:first-child {
            border-radius: 16px 0 0 0;
        }

        .testimonials-table th:last-child {
            border-radius: 0 16px 0 0;
        }

        .testimonials-table td {
            padding: 1.25rem;
            text-align: left;
            border-bottom: 1px solid #F1F5F9;
            color: var(--dark);
            font-size: 0.95rem;
        }

        .testimonials-table tbody tr {
            transition: var(--transition);
            animation: slideInRow 0.4s ease-out forwards;
        }

        @keyframes slideInRow {
            from {
                opacity: 0;
            }
            to {
                opacity: 1;
            }
        }

        .testimonials-table tbody tr:hover {
            background: var(--blue-light);
            transform: scale(1.005);
            box-shadow: inset 0 2px 8px rgba(30, 100, 200, 0.08);
        }

        .testimonials-table tbody tr:last-child td {
            border-bottom: none;
        }

        /* Student Avatar */
        .student-avatar {
            width: 60px;
            height: 60px;
            object-fit: cover;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            transition: var(--transition);
            border: 2px solid #E2E8F0;
        }

        .testimonials-table tbody tr:hover .student-avatar {
            transform: scale(1.08);
            box-shadow: 0 6px 18px rgba(0,0,0,0.2);
        }

        /* Rating Stars */
        .rating-stars {
            color: #FBBF24;
            font-size: 1rem;
        }

        /* Status Badges */
        .status-badge {
            padding: 0.35rem 0.9rem;
            border-radius: 25px;
            font-size: 0.75rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.06em;
            display: inline-block;
            transition: var(--transition);
        }

        .status-badge.approved {
            background: var(--green-light);
            color: var(--green);
            border: 1.5px solid var(--green);
            box-shadow: inset 0 2px 4px rgba(46, 125, 50, 0.1);
        }

        .status-badge.approved:hover {
            box-shadow: 0 4px 12px rgba(46, 125, 50, 0.2);
        }

        .status-badge.pending {
            background: var(--orange-light);
            color: var(--orange);
            border: 1.5px solid var(--orange);
            box-shadow: inset 0 2px 4px rgba(243, 156, 18, 0.1);
        }

        .status-badge.pending:hover {
            box-shadow: 0 4px 12px rgba(243, 156, 18, 0.2);
        }

        .status-badge.rejected {
            background: #F0F4F8;
            color: var(--gray);
            border: 1.5px solid #CBD5E0;
            box-shadow: inset 0 2px 4px rgba(100, 116, 139, 0.1);
        }

        .status-badge.rejected:hover {
            box-shadow: 0 4px 12px rgba(100, 116, 139, 0.15);
        }

        /* Featured Star */
        .featured-star {
            color: #FBBF24;
            font-size: 1.4rem;
            transition: var(--transition);
        }

        .featured-star:hover {
            transform: scale(1.2);
        }

        /* Action Buttons */
        .action-buttons {
            display: flex;
            gap: 0.6rem;
        }

        .action-btn {
            width: 36px;
            height: 36px;
            border-radius: 10px;
            color: white;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            text-decoration: none;
            transition: var(--transition);
            border: none;
            cursor: pointer;
            font-weight: 600;
            box-shadow: 0 2px 8px rgba(0,0,0,0.12);
        }

        .action-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 6px 16px rgba(0,0,0,0.2);
        }

        .edit-btn {
            background: linear-gradient(135deg, var(--blue) 0%, #1565c0 100%);
        }

        .edit-btn:hover {
            background: linear-gradient(135deg, #1565c0 0%, #0d47a1 100%);
        }

        .delete-btn {
            background: linear-gradient(135deg, var(--red) 0%, #c62828 100%);
        }

        .delete-btn:hover {
            background: linear-gradient(135deg, #c62828 0%, #ad1457 100%);
        }

        .approve-single-btn {
            background: linear-gradient(135deg, var(--green) 0%, #1b5e20 100%);
        }

        .approve-single-btn:hover {
            background: linear-gradient(135deg, #1b5e20 0%, #004d40 100%);
        }

        /* Select All Checkbox */
        .select-all {
            width: 18px;
            height: 18px;
            cursor: pointer;
        }

        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 4rem 2rem;
            color: var(--gray);
        }

        .empty-state i {
            font-size: 4rem;
            color: var(--light);
            margin-bottom: 1rem;
            opacity: 0.6;
        }

        .empty-state p {
            font-size: 1.1rem;
            margin-bottom: 1.5rem;
            font-weight: 500;
        }

        /* Responsive */
        @media (max-width: 1024px) {
            .search-form input {
                min-width: 200px;
            }
        }

        @media (max-width: 768px) {
            .testimonials-management {
                padding: 1rem;
            }

            .card-header {
                flex-direction: column;
                gap: 1rem;
                text-align: center;
                padding: 1.5rem;
            }

            .card-header h1 {
                font-size: 1.4rem;
            }

            .testimonials-filters {
                flex-direction: column;
                align-items: stretch;
                padding: 1.25rem;
            }

            .filter-badge {
                padding: 0.6rem 1rem;
                font-size: 0.85rem;
            }

            .search-form {
                margin-left: 0;
                flex-direction: column;
                width: 100%;
                order: 1;
            }

            .search-form input {
                min-width: auto;
                width: 100%;
            }

            .search-form button {
                order: 2;
            }

            .bulk-actions {
                margin-left: 0;
                margin-top: 1rem;
                flex-direction: column;
                width: 100%;
            }

            .bulk-btn {
                width: 100%;
                justify-content: center;
            }

            .action-buttons {
                flex-direction: row;
                gap: 0.5rem;
            }

            .action-btn {
                width: 32px;
                height: 32px;
                font-size: 0.85rem;
            }

            .testimonials-table {
                border-radius: 12px;
                font-size: 0.85rem;
            }

            .testimonials-table th,
            .testimonials-table td {
                padding: 0.85rem;
            }

            .student-avatar {
                width: 50px;
                height: 50px;
            }

            .status-badge {
                padding: 0.3rem 0.7rem;
                font-size: 0.7rem;
            }
        }

        @media (max-width: 480px) {
            .testimonials-management {
                padding: 0.5rem;
            }

            .card-header {
                padding: 1rem;
                border-left: 4px solid var(--blue);
            }

            .card-header h1 {
                font-size: 1.2rem;
            }

            .view-all {
                padding: 0.7rem 1.25rem;
                font-size: 0.85rem;
            }

            .testimonials-filters {
                padding: 1rem;
                gap: 0.75rem;
            }

            .testimonials-table {
                border-radius: 8px;
                font-size: 0.75rem;
            }

            .testimonials-table th,
            .testimonials-table td {
                padding: 0.6rem;
            }

            .student-avatar {
                width: 40px;
                height: 40px;
            }

            .action-btn {
                width: 28px;
                height: 28px;
                font-size: 0.75rem;
            }
        }
    </style>
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
    
    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success"><?php echo $_SESSION['success']; unset($_SESSION['success']); ?></div>
    <?php endif; ?>
    
    <?php if (isset($_SESSION['info'])): ?>
        <div class="alert alert-info"><?php echo $_SESSION['info']; unset($_SESSION['info']); ?></div>
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