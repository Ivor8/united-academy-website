<?php
$pageTitle = 'Online Courses Management';
require_once '../includes/header.php';

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
    .courses-management {
        padding: 1rem;
    }
    
    .courses-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
        gap: 1.5rem;
        margin-top: 2rem;
    }
    
    .course-card {
        background: white;
        border-radius: 16px;
        box-shadow: 0 4px 20px rgba(0,0,0,0.08);
        overflow: hidden;
        transition: transform 0.3s ease, box-shadow 0.3s ease;
        border: 1px solid rgba(30,100,200,0.1);
    }
    
    .course-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 12px 30px rgba(0,0,0,0.12);
    }
    
    .course-image {
        width: 100%;
        height: 200px;
        object-fit: cover;
        background: var(--light);
    }
    
    .course-content {
        padding: 1.5rem;
    }
    
    .course-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        margin-bottom: 1rem;
    }
    
    .course-title {
        font-size: 1.1rem;
        font-weight: 700;
        color: var(--dark);
        margin: 0 0 0.5rem 0;
        line-height: 1.3;
    }
    
    .course-meta {
        display: flex;
        flex-wrap: wrap;
        gap: 0.5rem;
        margin-bottom: 1rem;
    }
    
    .meta-badge {
        padding: 0.3rem 0.8rem;
        border-radius: 20px;
        font-size: 0.75rem;
        font-weight: 600;
    }
    
    .meta-badge.category {
        background: rgba(30,100,200,0.1);
        color: var(--blue);
    }
    
    .meta-badge.level {
        background: rgba(46,125,50,0.1);
        color: var(--green);
    }
    
    .meta-badge.status {
        background: rgba(211,47,47,0.1);
        color: var(--red);
    }
    
    .course-description {
        color: #64748b;
        font-size: 0.9rem;
        line-height: 1.6;
        margin-bottom: 1rem;
        display: -webkit-box;
        -webkit-line-clamp: 3;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }
    
    .course-stats {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding-top: 1rem;
        border-top: 1px solid #e5e7eb;
        font-size: 0.85rem;
        color: #64748b;
    }
    
    .stat-item {
        display: flex;
        align-items: center;
        gap: 0.3rem;
    }
    
    .featured-badge {
        position: absolute;
        top: 1rem;
        right: 1rem;
        background: linear-gradient(135deg, #1E64C8, #2E7D32);
        color: white;
        padding: 0.4rem 0.8rem;
        border-radius: 20px;
        font-size: 0.75rem;
        font-weight: 600;
    }
    
    .action-buttons {
        display: flex;
        gap: 0.5rem;
        margin-top: 1rem;
    }
    
    .btn-small {
        padding: 0.4rem 0.8rem;
        font-size: 0.8rem;
        text-decoration: none;
        border-radius: 8px;
        transition: all 0.3s ease;
    }
    
    .btn-small:hover {
        transform: translateY(-2px);
    }
    
    @media (max-width: 768px) {
        .courses-grid {
            grid-template-columns: 1fr;
            gap: 1rem;
        }
        
        .course-card {
            border-radius: 12px;
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
                <div class="course-card" style="position: relative;">
                    <?php if ($course['featured']): ?>
                        <div class="featured-badge">
                            <i class="fas fa-star"></i> Featured
                        </div>
                    <?php endif; ?>
                    
                    <?php if ($course['cover_image']): ?>
                        <img src="../<?php echo $course['cover_image']; ?>" alt="<?php echo htmlspecialchars($course['title']); ?>" class="course-image">
                    <?php else: ?>
                        <div class="course-image" style="display: flex; align-items: center; justify-content: center; color: #94a3b8;">
                            <i class="fas fa-image" style="font-size: 2rem;"></i>
                        </div>
                    <?php endif; ?>
                    
                    <div class="course-content">
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
                                <span><?php echo $course['active_enrollments']; ?> Enrolled
                            </div>
                            <div class="stat-item">
                                <i class="fas fa-user-graduate"></i>
                                <span><?php echo $course['pending_applications']; ?> Pending
                            </div>
                            <div class="stat-item">
                                <i class="fas fa-clock"></i>
                                <span><?php echo htmlspecialchars($course['duration']); ?></span>
                            </div>
                        </div>
                        
                        <div class="action-buttons">
                            <a href="edit.php?id=<?php echo $course['id']; ?>" class="btn-small" style="background: var(--blue); color: white;">
                                <i class="fas fa-edit"></i> Edit
                            </a>
                            <a href="applications.php?course_id=<?php echo $course['id']; ?>" class="btn-small" style="background: var(--green); color: white;">
                                <i class="fas fa-users"></i> Applications
                            </a>
                            <a href="enrollments.php?course_id=<?php echo $course['id']; ?>" class="btn-small" style="background: var(--orange); color: white;">
                                <i class="fas fa-graduation-cap"></i> Enrollments
                            </a>
                            <?php if (hasPermission('delete_online_courses')): ?>
                            <a href="delete.php?id=<?php echo $course['id']; ?>" class="btn-small" style="background: var(--red); color: white;" onclick="return confirm('Are you sure you want to delete this course?')">
                                <i class="fas fa-trash"></i> Delete
                            </a>
                            <?php endif; ?>
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
