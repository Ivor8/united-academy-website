<?php
$pageTitle = 'Online Courses Management';
require_once '../includes/config.php';
require_once '../includes/auth.php';
requireLogin();

if (!hasPermission('view_online_courses')) {
    header('Location: index.php');
    exit();
}

$pdo = getDB();
$search = isset($_GET['search']) ? sanitize($_GET['search']) : '';
$category = isset($_GET['category']) ? sanitize($_GET['category']) : 'all';
$status = isset($_GET['status']) ? sanitize($_GET['status']) : 'all';
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$limit = 10;
$offset = ($page - 1) * $limit;

// Build query
$query = "SELECT oc.*, u.first_name, u.last_name,
           (SELECT COUNT(*) FROM course_applications WHERE course_id = oc.id AND status = 'pending') as pending_applications,
           (SELECT COUNT(*) FROM course_enrollments WHERE course_id = oc.id AND status = 'active') as active_enrollments
          FROM online_courses oc 
          LEFT JOIN users u ON oc.created_by = u.id 
          WHERE 1=1";

$params = [];

if ($category !== 'all') {
    $query .= " AND oc.category = ?";
    $params[] = $category;
}

if ($status !== 'all') {
    $query .= " AND oc.status = ?";
    $params[] = $status;
}

if ($search) {
    $query .= " AND (oc.title LIKE ? OR oc.short_description LIKE ? OR oc.long_description LIKE ?)";
    $searchPattern = "%$search%";
    $params[] = $searchPattern;
    $params[] = $searchPattern;
    $params[] = $searchPattern;
}

$query .= " ORDER BY oc.order_position ASC, oc.created_at DESC LIMIT $limit OFFSET $offset";

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$courses = $stmt->fetchAll();

// Get total count for pagination
$countQuery = "SELECT COUNT(*) as total FROM online_courses oc WHERE 1=1";
$countParams = [];

if ($category !== 'all') {
    $countQuery .= " AND oc.category = ?";
    $countParams[] = $category;
}

if ($status !== 'all') {
    $countQuery .= " AND oc.status = ?";
    $countParams[] = $status;
}

if ($search) {
    $countQuery .= " AND (oc.title LIKE ? OR oc.short_description LIKE ? OR oc.long_description LIKE ?)";
    $countParams[] = "%$search%";
    $countParams[] = "%$search%";
    $countParams[] = "%$search%";
}

$countStmt = $pdo->prepare($countQuery);
$countStmt->execute($countParams);
$totalCourses = $countStmt->fetch()['total'];
$totalPages = ceil($totalCourses / $limit);

$extraCss = '<style>
    * {
        --transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }
    
    .courses-management {
        padding: 2rem 1rem;
        background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
        min-height: 100vh;
    }
    
    /* Header with Stats */
    .card-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 2rem;
        background: white;
        padding: 1.5rem;
        border-radius: 16px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.05);
        flex-wrap: wrap;
        gap: 1rem;
    }
    
    .card-header h1 {
        margin: 0;
        font-size: 1.8rem;
        color: var(--dark);
        display: flex;
        align-items: center;
        gap: 1rem;
    }
    
    .card-header h1 i {
        color: #1E64C8;
        font-size: 2rem;
    }
    
    /* Statistics Bar */
    .stats-bar {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 1.5rem;
        margin-bottom: 2rem;
        background: white;
        padding: 1.5rem;
        border-radius: 16px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.05);
    }
    
    .stat-card {
        display: flex;
        align-items: center;
        gap: 1rem;
        padding: 1rem;
        background: linear-gradient(135deg, rgba(30,100,200,0.05) 0%, rgba(30,100,200,0.02) 100%);
        border-radius: 12px;
        border: 1px solid rgba(30,100,200,0.1);
        transition: var(--transition);
    }
    
    .stat-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(30,100,200,0.1);
    }
    
    .stat-icon {
        font-size: 1.5rem;
        color: #1E64C8;
        width: 50px;
        height: 50px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: white;
        border-radius: 12px;
        border: 2px solid rgba(30,100,200,0.2);
    }
    
    .stat-content h4 {
        margin: 0;
        font-size: 0.85rem;
        color: #64748b;
        font-weight: 500;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    
    .stat-content p {
        margin: 0.5rem 0 0 0;
        font-size: 1.8rem;
        font-weight: 700;
        color: #1E64C8;
    }
    
    /* Filters Section */
    .course-filters {
        background: white;
        padding: 1.5rem;
        border-radius: 16px;
        margin-bottom: 2rem;
        box-shadow: 0 2px 8px rgba(0,0,0,0.05);
        display: flex;
        gap: 1rem;
        flex-wrap: wrap;
        align-items: center;
        position: sticky;
        top: 1rem;
        z-index: 10;
    }
    
    .course-filters > div {
        flex: 1;
        min-width: 250px;
        display: flex;
        gap: 0.5rem;
        align-items: center;
    }
    
    .course-filters input,
    .course-filters select {
        padding: 0.75rem 1rem;
        border: 2px solid #e5e7eb;
        border-radius: 10px;
        font-size: 0.9rem;
        transition: var(--transition);
        background: white;
        color: #1a202c;
        flex: 1;
    }
    
    .course-filters input:focus,
    .course-filters select:focus {
        outline: none;
        border-color: #1E64C8;
        box-shadow: 0 0 0 3px rgba(30,100,200,0.1);
    }
    
    .course-filters > div:last-child {
        display: flex;
        gap: 0.5rem;
        flex-wrap: wrap;
    }
    
    /* Courses Grid */
    .courses-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
        gap: 1.5rem;
        margin-top: 2rem;
    }
    
    .course-card {
        background: white;
        border-radius: 16px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.05);
        overflow: hidden;
        transition: var(--transition);
        border: 1px solid rgba(30,100,200,0.1);
        position: relative;
        display: flex;
        flex-direction: column;
    }
    
    .course-card:hover {
        transform: translateY(-8px);
        box-shadow: 0 16px 32px rgba(0,0,0,0.12);
        border-color: #1E64C8;
    }
    
    .course-image {
        width: 100%;
        height: 220px;
        object-fit: cover;
        background: linear-gradient(135deg, #e0e7ff 0%, #f0f4ff 100%);
        transition: var(--transition);
    }
    
    .course-card:hover .course-image {
        transform: scale(1.05);
    }
    
    .featured-badge {
        position: absolute;
        top: 1rem;
        right: 1rem;
        background: linear-gradient(135deg, #FFD700 0%, #FFA500 100%);
        color: #333;
        padding: 0.5rem 1rem;
        border-radius: 20px;
        font-size: 0.75rem;
        font-weight: 700;
        display: flex;
        align-items: center;
        gap: 0.5rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.15);
    }
    
    .course-card-body,
    .course-content {
        padding: 1.5rem;
        flex: 1;
        display: flex;
        flex-direction: column;
    }
    
    .course-card-image {
        position: relative;
    }
    
    .course-placeholder {
        display: flex;
        align-items: center;
        justify-content: center;
        height: 220px;
        color: #94a3b8;
        background: linear-gradient(135deg, #e0e7ff 0%, #f0f4ff 100%);
    }
    
    .course-card-footer {
        margin-top: auto;
    }
    
    .course-header {
        margin-bottom: 1rem;
    }
    
    .course-title {
        font-size: 1.1rem;
        font-weight: 700;
        color: var(--dark);
        margin: 0 0 0.75rem 0;
        line-height: 1.4;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
        transition: var(--transition);
    }
    
    .course-card:hover .course-title {
        color: #1E64C8;
    }
    
    .course-meta {
        display: flex;
        flex-wrap: wrap;
        gap: 0.5rem;
        margin-bottom: 0;
    }
    
    .meta-badge {
        padding: 0.4rem 0.8rem;
        border-radius: 20px;
        font-size: 0.7rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.3px;
        white-space: nowrap;
    }
    
    .meta-badge.category {
        background: linear-gradient(135deg, rgba(30,100,200,0.15) 0%, rgba(30,100,200,0.05) 100%);
        color: #0F4C94;
    }
    
    .meta-badge.level {
        background: linear-gradient(135deg, rgba(46,125,50,0.15) 0%, rgba(46,125,50,0.05) 100%);
        color: #0B5B1D;
    }
    
    .meta-badge.status {
        background: linear-gradient(135deg, rgba(211,47,47,0.15) 0%, rgba(211,47,47,0.05) 100%);
        color: #7F1D1D;
    }
    
    .course-description {
        color: #64748b;
        font-size: 0.85rem;
        line-height: 1.5;
        margin-bottom: 1rem;
        flex: 1;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }
    
    .course-stats {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 1rem 0;
        border-top: 1px solid #e5e7eb;
        border-bottom: 1px solid #e5e7eb;
        font-size: 0.85rem;
        color: #64748b;
        margin: 1rem 0;
    }
    
    .stat-item {
        display: flex;
        align-items: center;
        gap: 0.4rem;
        font-weight: 500;
    }
    
    .stat-item i {
        color: #1E64C8;
        font-size: 0.95rem;
    }
    
    .action-buttons {
        display: flex;
        gap: 0.5rem;
        flex-wrap: wrap;
    }
    
    .btn-small {
        padding: 0.5rem 1rem;
        font-size: 0.75rem;
        text-decoration: none;
        border-radius: 8px;
        transition: var(--transition);
        border: none;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 0.4rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.3px;
        white-space: nowrap;
        flex: 1;
        min-width: fit-content;
        color: white;
    }
    
    .btn-small.btn-edit {
        background: linear-gradient(135deg, #1E64C8 0%, #1565C0 100%);
    }
    
    .btn-small.btn-applications {
        background: linear-gradient(135deg, #10B981 0%, #047857 100%);
    }
    
    .btn-small.btn-enrollments {
        background: linear-gradient(135deg, #F59E0B 0%, #D97706 100%);
    }
    
    .btn-small.btn-delete {
        background: linear-gradient(135deg, #EF4444 0%, #B91C1C 100%);
    }
    
    .btn-small:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    }
    
    .btn-small:active {
        transform: translateY(0);
    }
    
    /* No Results */
    .no-results {
        text-align: center;
        padding: 4rem 2rem;
        color: #64748b;
        background: white;
        border-radius: 16px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.05);
        grid-column: 1 / -1;
    }
    
    .no-results i {
        font-size: 4rem;
        margin-bottom: 1rem;
        opacity: 0.2;
        color: #1E64C8;
    }
    
    .no-results h3 {
        font-size: 1.3rem;
        color: #1a202c;
        margin: 1rem 0;
        font-weight: 600;
    }
    
    .no-results p {
        color: #64748b;
        margin: 0.5rem 0 0 0;
        font-size: 0.95rem;
    }
    
    /* Pagination */
    .pagination-container {
        display: flex;
        justify-content: center;
        margin-top: 3rem;
        grid-column: 1 / -1;
    }
    
    .pagination {
        display: flex;
        gap: 0.5rem;
        align-items: center;
        background: white;
        padding: 1rem 1.5rem;
        border-radius: 12px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.05);
        flex-wrap: wrap;
    }
    
    .pagination-btn {
        padding: 0.5rem 0.8rem;
        border: 1px solid #e5e7eb;
        border-radius: 8px;
        text-decoration: none;
        color: #1E64C8;
        cursor: pointer;
        background: white;
        transition: var(--transition);
        font-size: 0.85rem;
        font-weight: 500;
        display: inline-flex;
        align-items: center;
        gap: 0.4rem;
    }
    
    .pagination-btn:hover {
        background: #1E64C8;
        color: white;
        border-color: #1E64C8;
    }
    
    .pagination-btn.active {
        background: #1E64C8;
        color: white;
        border-color: #1E64C8;
        font-weight: 600;
    }
    
    .pagination-ellipsis {
        color: #64748b;
    }
    
    @media (max-width: 1200px) {
        .courses-grid {
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
        }
    }
    
    @media (max-width: 768px) {
        .courses-management {
            padding: 1rem;
        }
        
        .card-header {
            flex-direction: column;
            align-items: flex-start;
        }
        
        .card-header h1 {
            font-size: 1.4rem;
            width: 100%;
        }
        
        .stats-bar {
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
            padding: 1rem;
        }
        
        .course-filters {
            flex-direction: column;
            padding: 1rem;
        }
        
        .course-filters > div {
            width: 100%;
            flex-direction: column;
        }
        
        .course-filters input,
        .course-filters select {
            width: 100%;
        }
        
        .courses-grid {
            grid-template-columns: 1fr;
            gap: 1rem;
        }
        
        .action-buttons {
            flex-direction: column;
        }
        
        .btn-small {
            width: 100%;
        }
        
        .course-image {
            height: 180px;
        }
        
        .course-content {
            padding: 1rem;
        }
        
        .course-title {
            font-size: 1rem;
        }
    }
</style>';

$extraJs = '<script>
    document.addEventListener("DOMContentLoaded", function() {
        // Search functionality
        const searchInput = document.getElementById("searchInput");
        if (searchInput) {
            searchInput.addEventListener("keyup", function(e) {
                if (e.key === "Enter") {
                    window.location.href = "?search=" + encodeURIComponent(this.value);
                }
            });
        }
        
        // Category filter
        const categoryFilter = document.getElementById("categoryFilter");
        if (categoryFilter) {
            categoryFilter.addEventListener("change", function() {
                window.location.href = "?category=" + this.value;
            });
        }
    });
</script>';
require_once '../includes/header.php';
?>

<div class="courses-management">
    <div class="card-header">
        <h1><i class="fas fa-graduation-cap"></i> Online Courses Management</h1>
        <?php if (hasPermission('create_online_courses')): ?>
        <a href="create.php" class="view-all" style="background: var(--blue); color: white; padding: 0.75rem 1.5rem; border-radius: 40px;">
            <i class="fas fa-plus"></i> Create New Course
        </a>
        <?php endif; ?>
    </div>
    
    <!-- Statistics Bar -->
    <div class="stats-bar">
        <div class="stat-card">
            <div class="stat-icon"><i class="fas fa-book-open"></i></div>
            <div class="stat-content">
                <h4>Total Courses</h4>
                <p><?php echo $totalCourses; ?></p>
            </div>
        </div>
        
        <?php
        $publishedStmt = $pdo->prepare("SELECT COUNT(*) as count FROM online_courses WHERE status = 'published'");
        $publishedStmt->execute();
        $publishedCount = $publishedStmt->fetch()['count'];
        
        $draftStmt = $pdo->prepare("SELECT COUNT(*) as count FROM online_courses WHERE status = 'draft'");
        $draftStmt->execute();
        $draftCount = $draftStmt->fetch()['count'];
        
        $enrollmentsStmt = $pdo->prepare("SELECT COUNT(*) as count FROM course_enrollments WHERE status = 'active'");
        $enrollmentsStmt->execute();
        $enrollmentsCount = $enrollmentsStmt->fetch()['count'];
        ?>
        
        <div class="stat-card">
            <div class="stat-icon"><i class="fas fa-check-circle"></i></div>
            <div class="stat-content">
                <h4>Published</h4>
                <p><?php echo $publishedCount; ?></p>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon"><i class="fas fa-file-alt"></i></div>
            <div class="stat-content">
                <h4>Drafts</h4>
                <p><?php echo $draftCount; ?></p>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon"><i class="fas fa-users"></i></div>
            <div class="stat-content">
                <h4>Active Enrollments</h4>
                <p><?php echo $enrollmentsCount; ?></p>
            </div>
        </div>
    </div>
    
    <div class="course-filters" style="background: white; padding: 1.5rem; border-radius: 12px; margin-bottom: 2rem; box-shadow: 0 2px 10px rgba(0,0,0,0.05);">
        <div style="display: flex; gap: 1rem; align-items: center; flex-wrap: wrap;">
            <div style="flex: 1;">
                <input type="text" id="searchInput" placeholder="Search courses..." value="<?php echo htmlspecialchars($search); ?>" style="width: 100%; padding: 0.75rem; border: 1px solid #e5e7eb; border-radius: 8px;">
            </div>
            
            <div style="display: flex; gap: 0.5rem; align-items: center;">
                <select id="categoryFilter" style="padding: 0.75rem; border: 1px solid #e5e7eb; border-radius: 8px;">
                    <option value="all" <?php echo $category === 'all' ? 'selected' : ''; ?>>All Categories</option>
                    <option value="health" <?php echo $category === 'health' ? 'selected' : ''; ?>>Health</option>
                    <option value="it" <?php echo $category === 'it' ? 'selected' : ''; ?>>IT</option>
                    <option value="business" <?php echo $category === 'business' ? 'selected' : ''; ?>>Business</option>
                    <option value="languages" <?php echo $category === 'languages' ? 'selected' : ''; ?>>Languages</option>
                    <option value="professional" <?php echo $category === 'professional' ? 'selected' : ''; ?>>Professional</option>
                </select>
                
                <select id="statusFilter" style="padding: 0.75rem; border: 1px solid #e5e7eb; border-radius: 8px;">
                    <option value="all" <?php echo $status === 'all' ? 'selected' : ''; ?>>All Status</option>
                    <option value="draft" <?php echo $status === 'draft' ? 'selected' : ''; ?>>Draft</option>
                    <option value="published" <?php echo $status === 'published' ? 'selected' : ''; ?>>Published</option>
                    <option value="archived" <?php echo $status === 'archived' ? 'selected' : ''; ?>>Archived</option>
                </select>
            </div>
        </div>
    </div>
    
    <div class="courses-grid">
        <?php if (count($courses) > 0): ?>
            <?php foreach ($courses as $course): ?>
                <div class="course-card">
                    <?php if ($course['featured']): ?>
                        <div class="featured-badge">
                            <i class="fas fa-star"></i> Featured
                        </div>
                    <?php endif; ?>
                    
                    <div class="course-card-image">
                        <?php if ($course['cover_image']): ?>
                            <img src="<?php echo htmlspecialchars(getUploadUrl($course['cover_image'])); ?>" alt="<?php echo htmlspecialchars($course['title']); ?>" class="course-image">
                        <?php else: ?>
                            <div class="course-image course-placeholder">
                                <i class="fas fa-image"></i>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="course-card-body">
                        <div class="course-header">
                            <h3 class="course-title"><?php echo htmlspecialchars($course['title']); ?></h3>
                            <div class="course-meta">
                                <span class="meta-badge category"><?php echo ucfirst($course['category']); ?></span>
                                <span class="meta-badge level"><?php echo ucfirst($course['level']); ?></span>
                                <span class="meta-badge status"><?php echo ucfirst($course['status']); ?></span>
                            </div>
                        </div>
                        
                        <div class="course-description">
                            <?php echo htmlspecialchars($course['short_description']); ?>
                        </div>
                        
                        <div class="course-stats">
                            <div class="stat-item">
                                <i class="fas fa-users"></i>
                                <span><?php echo $course['active_enrollments']; ?> Enrolled</span>
                            </div>
                            <div class="stat-item">
                                <i class="fas fa-user-graduate"></i>
                                <span><?php echo $course['pending_applications']; ?> Pending</span>
                            </div>
                            <div class="stat-item">
                                <i class="fas fa-clock"></i>
                                <span><?php echo htmlspecialchars($course['duration']); ?></span>
                            </div>
                        </div>
                        
                        <div class="course-card-footer">
                            <div class="action-buttons">
                                <a href="edit.php?id=<?php echo $course['id']; ?>" class="btn-small btn-edit">
                                    <i class="fas fa-edit"></i> Edit
                                </a>
                                <a href="applications.php?course_id=<?php echo $course['id']; ?>" class="btn-small btn-applications">
                                    <i class="fas fa-users"></i> Applications
                                </a>
                                <a href="enrollments.php?course_id=<?php echo $course['id']; ?>" class="btn-small btn-enrollments">
                                    <i class="fas fa-graduation-cap"></i> Enrollments
                                </a>
                                <?php if (hasPermission('delete_online_courses')): ?>
                                <a href="delete.php?id=<?php echo $course['id']; ?>" class="btn-small btn-delete" onclick="return confirm('Are you sure you want to delete this course?')">
                                    <i class="fas fa-trash"></i> Delete
                                </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="no-results" style="text-align: center; padding: 3rem; color: #64748b;">
                <i class="fas fa-graduation-cap" style="font-size: 3rem; margin-bottom: 1rem; opacity: 0.5;"></i>
                <h3>No courses found</h3>
                <p>Start by creating your first online course.</p>
            </div>
        <?php endif; ?>
    </div>
    
    <!-- Pagination -->
    <?php if ($totalPages > 1): ?>
    <div class="pagination-container" style="margin-top: 2rem;">
        <div class="pagination">
            <?php if ($page > 1): ?>
                <a href="?page=<?php echo $page - 1; ?>&category=<?php echo urlencode($category); ?>&status=<?php echo urlencode($status); ?>&search=<?php echo urlencode($search); ?>" class="pagination-btn prev">
                    <i class="fas fa-chevron-left"></i> Previous
                </a>
            <?php endif; ?>
            
            <?php
            $start = max(1, $page - 2);
            $end = min($totalPages, $page + 2);
            
            if ($start > 1) {
                echo '<a href="?page=1&category=' . urlencode($category) . '&status=' . urlencode($status) . '&search=' . urlencode($search) . '" class="pagination-btn">1</a>';
                if ($start > 2) echo '<span class="pagination-ellipsis">...</span>';
            }
            
            for ($i = $start; $i <= $end; $i++) {
                $active = $i == $page ? 'active' : '';
                echo '<a href="?page=' . $i . '&category=' . urlencode($category) . '&status=' . urlencode($status) . '&search=' . urlencode($search) . '" class="pagination-btn ' . $active . '">' . $i . '</a>';
            }
            
            if ($end < $totalPages) {
                if ($end < $totalPages - 1) echo '<span class="pagination-ellipsis">...</span>';
                echo '<a href="?page=' . $totalPages . '&category=' . urlencode($category) . '&status=' . urlencode($status) . '&search=' . urlencode($search) . '" class="pagination-btn">' . $totalPages . '</a>';
            }
            ?>
            
            <?php if ($page < $totalPages): ?>
                <a href="?page=<?php echo $page + 1; ?>&category=<?php echo urlencode($category); ?>&status=<?php echo urlencode($status); ?>&search=<?php echo urlencode($search); ?>" class="pagination-btn next">
                    Next <i class="fas fa-chevron-right"></i>
                </a>
            <?php endif; ?>
        </div>
    </div>
    <?php endif; ?>
</div>

<?php require_once '../includes/footer.php'; ?>
