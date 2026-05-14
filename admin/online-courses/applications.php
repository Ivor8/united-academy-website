<?php
$pageTitle = 'Course Applications';
require_once '../includes/header.php';

if (!hasPermission('view_course_applications')) {
    header('Location: index.php');
    exit();
}

$pdo = getDB();
$search = isset($_GET['search']) ? sanitize($_GET['search']) : '';
$status = isset($_GET['status']) ? sanitize($_GET['status']) : 'all';
$courseId = isset($_GET['course_id']) ? intval($_GET['course_id']) : 0;
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$limit = 10;
$offset = ($page - 1) * $limit;

// Build query
$query = "SELECT ca.*, oc.title as course_title, oc.category as course_category, oc.level as course_level
          FROM course_applications ca 
          LEFT JOIN online_courses oc ON ca.course_id = oc.id 
          WHERE 1=1";

$params = [];

if ($status !== 'all') {
    $query .= " AND ca.status = ?";
    $params[] = $status;
}

if ($courseId > 0) {
    $query .= " AND ca.course_id = ?";
    $params[] = $courseId;
}

if ($search) {
    $query .= " AND (ca.first_name LIKE ? OR ca.last_name LIKE ? OR ca.email LIKE ? OR oc.title LIKE ?)";
    $searchPattern = "%$search%";
    $params[] = $searchPattern;
    $params[] = $searchPattern;
    $params[] = $searchPattern;
    $params[] = $searchPattern;
}

$query .= " ORDER BY ca.submitted_at DESC LIMIT $limit OFFSET $offset";

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$applications = $stmt->fetchAll();

// Get total count for pagination
$countQuery = "SELECT COUNT(*) as total FROM course_applications ca LEFT JOIN online_courses oc ON ca.course_id = oc.id WHERE 1=1";
$countParams = [];

if ($status !== 'all') {
    $countQuery .= " AND ca.status = ?";
    $countParams[] = $status;
}

if ($courseId > 0) {
    $countQuery .= " AND ca.course_id = ?";
    $countParams[] = $courseId;
}

if ($search) {
    $countQuery .= " AND (ca.first_name LIKE ? OR ca.last_name LIKE ? OR ca.email LIKE ? OR oc.title LIKE ?)";
    $countParams[] = "%$search%";
    $countParams[] = "%$search%";
    $countParams[] = "%$search%";
    $countParams[] = "%$search%";
}

$countStmt = $pdo->prepare($countQuery);
$countStmt->execute($countParams);
$totalApplications = $countStmt->fetch()['total'];
$totalPages = ceil($totalApplications / $limit);

// Get all courses for filter dropdown
$coursesStmt = $pdo->prepare("SELECT id, title FROM online_courses WHERE status = 'published' ORDER BY title");
$coursesStmt->execute();
$courses = $coursesStmt->fetchAll();

$extraCss = '<style>
    * {
        --transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }
    
    .applications-management {
        padding: 2rem 1rem;
        background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
        min-height: 100vh;
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
    }
    
    .stat-icon {
        font-size: 1.5rem;
        color: #1E64C8;
        width: 40px;
        height: 40px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: rgba(30,100,200,0.1);
        border-radius: 10px;
    }
    
    .stat-content h4 {
        margin: 0;
        font-size: 0.9rem;
        color: #64748b;
        font-weight: 500;
    }
    
    .stat-content p {
        margin: 0.25rem 0 0 0;
        font-size: 1.5rem;
        font-weight: 700;
        color: #1E64C8;
    }
    
    /* Filters Section */
    .filters-section {
        background: white;
        padding: 1.5rem;
        border-radius: 16px;
        margin-bottom: 2rem;
        box-shadow: 0 2px 8px rgba(0,0,0,0.05);
        position: sticky;
        top: 2rem;
        z-index: 10;
    }
    
    .filters-row {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 1rem;
    }
    
    .filter-group {
        display: flex;
        flex-direction: column;
        gap: 0.5rem;
    }
    
    .filter-group label {
        font-size: 0.85rem;
        font-weight: 600;
        color: #1E64C8;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    
    .filter-group input,
    .filter-group select {
        padding: 0.75rem 1rem;
        border: 2px solid #e5e7eb;
        border-radius: 10px;
        font-size: 0.9rem;
        transition: var(--transition);
        background: white;
        color: #1a202c;
    }
    
    .filter-group input:focus,
    .filter-group select:focus {
        outline: none;
        border-color: #1E64C8;
        box-shadow: 0 0 0 3px rgba(30,100,200,0.1);
    }
    
    /* Applications Grid */
    .applications-grid {
        display: grid;
        gap: 1.5rem;
        margin-top: 2rem;
    }
    
    .application-card {
        background: white;
        border-radius: 16px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.05);
        overflow: hidden;
        transition: var(--transition);
        border-left: 5px solid #1E64C8;
        display: grid;
        grid-template-columns: auto 1fr auto auto;
        align-items: stretch;
    }
    
    .application-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 12px 24px rgba(0,0,0,0.12);
    }
    
    .application-card.status-accepted {
        border-left-color: #2E7D32;
        background: linear-gradient(to right, rgba(46,125,50,0.02), white);
    }
    
    .application-card.status-rejected {
        border-left-color: #D32F2F;
        background: linear-gradient(to right, rgba(211,47,47,0.02), white);
    }
    
    .application-card.status-reviewed {
        border-left-color: #F59E0B;
        background: linear-gradient(to right, rgba(245,158,11,0.02), white);
    }
    
    .application-card.status-pending {
        border-left-color: #3B82F6;
        background: linear-gradient(to right, rgba(59,130,246,0.02), white);
    }
    
    .application-header {
        padding: 1.5rem;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        grid-column: 1 / 2;
        background: rgba(248,250,252,0.5);
        border-right: 1px solid #e5e7eb;
        min-width: 200px;
    }
    
    .applicant-info {
        flex: 1;
    }
    
    .applicant-name {
        font-size: 1rem;
        font-weight: 700;
        color: var(--dark);
        margin-bottom: 0.5rem;
        line-height: 1.3;
    }
    
    .applicant-email {
        color: #64748b;
        font-size: 0.85rem;
        margin-bottom: 0.25rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }
    
    .applicant-phone {
        color: #64748b;
        font-size: 0.85rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }
    
    .application-meta {
        text-align: left;
        margin-top: 1rem;
        padding-top: 1rem;
        border-top: 1px solid #e5e7eb;
    }
    
    .course-badge {
        display: inline-block;
        padding: 0.4rem 0.8rem;
        background: rgba(30,100,200,0.1);
        color: #1E64C8;
        border-radius: 20px;
        font-size: 0.75rem;
        font-weight: 600;
        margin-bottom: 0.5rem;
    }
    
    .status-badge {
        display: inline-block;
        padding: 0.5rem 1rem;
        border-radius: 20px;
        font-size: 0.75rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    
    .status-pending {
        background: rgba(245,158,11,0.15);
        color: #D97706;
    }
    
    .status-reviewed {
        background: rgba(59,130,246,0.15);
        color: #1E40AF;
    }
    
    .status-accepted {
        background: rgba(46,125,50,0.15);
        color: #15803D;
    }
    
    .status-rejected {
        background: rgba(211,47,47,0.15);
        color: #7F1D1D;
    }
    
    .status-enrolled {
        background: rgba(124,58,237,0.15);
        color: #5B21B6;
    }
    
    .application-content {
        padding: 1.5rem;
        grid-column: 2 / 3;
        border-right: 1px solid #e5e7eb;
    }
    
    .application-details {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 1rem;
        margin-bottom: 1.5rem;
    }
    
    .detail-item {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        color: #64748b;
        font-size: 0.85rem;
        padding: 0.5rem;
        background: rgba(248,250,252,0.5);
        border-radius: 8px;
        transition: var(--transition);
    }
    
    .detail-item:hover {
        background: rgba(248,250,252,1);
    }
    
    .detail-item i {
        color: #1E64C8;
        width: 18px;
        font-size: 0.95rem;
    }
    
    .motivation-text {
        color: #475569;
        line-height: 1.6;
        margin-bottom: 1.5rem;
        padding: 1rem;
        background: linear-gradient(135deg, rgba(30,100,200,0.05) 0%, rgba(30,100,200,0.02) 100%);
        border-radius: 8px;
        border-left: 4px solid #1E64C8;
        font-size: 0.9rem;
    }
    
    .application-actions {
        display: flex;
        gap: 0.5rem;
        flex-wrap: wrap;
        justify-content: flex-start;
    }
    
    .btn-small {
        padding: 0.5rem 1rem;
        font-size: 0.75rem;
        font-weight: 600;
        text-decoration: none;
        border-radius: 8px;
        transition: var(--transition);
        border: none;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 0.4rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        white-space: nowrap;
    }
    
    .btn-small:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    }
    
    .btn-small:active {
        transform: translateY(0);
    }
    
    .btn-accept {
        background: linear-gradient(135deg, #2E7D32 0%, #1B5E20 100%);
        color: white;
    }
    
    .btn-reject {
        background: linear-gradient(135deg, #D32F2F 0%, #B71C1C 100%);
        color: white;
    }
    
    .btn-review {
        background: linear-gradient(135deg, #F59E0B 0%, #D97706 100%);
        color: white;
    }
    
    .btn-enroll {
        background: linear-gradient(135deg, #7C3AED 0%, #6D28D9 100%);
        color: white;
    }
    
    .btn-view {
        background: linear-gradient(135deg, #1E64C8 0%, #1565C0 100%);
        color: white;
    }
    
    .application-meta-right {
        padding: 1.5rem;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        align-items: center;
        text-align: center;
        grid-column: 3 / 4;
        border-right: 1px solid #e5e7eb;
        background: rgba(248,250,252,0.5);
    }
    
    .application-actions-right {
        padding: 1.5rem;
        display: flex;
        flex-direction: column;
        justify-content: center;
        gap: 0.5rem;
        grid-column: 4 / 5;
    }
    
    .no-results {
        text-align: center;
        padding: 3rem 2rem;
        color: #64748b;
        background: white;
        border-radius: 16px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.05);
    }
    
    .no-results i {
        font-size: 3rem;
        margin-bottom: 1rem;
        opacity: 0.3;
    }
    
    .no-results h3 {
        font-size: 1.2rem;
        color: #1a202c;
        margin: 0.5rem 0;
        font-weight: 600;
    }
    
    .no-results p {
        color: #64748b;
        margin: 0;
        font-size: 0.95rem;
    }
    
    /* Pagination */
    .pagination-container {
        display: flex;
        justify-content: center;
        margin-top: 2rem;
    }
    
    .pagination {
        display: flex;
        gap: 0.5rem;
        align-items: center;
        background: white;
        padding: 1rem;
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
    
    @media (max-width: 1024px) {
        .application-card {
            grid-template-columns: auto 1fr auto;
        }
        
        .application-actions-right {
            display: none;
        }
        
        .application-content {
            border-right: none;
        }
        
        .application-meta-right {
            border-right: none;
        }
    }
    
    @media (max-width: 768px) {
        .stats-bar {
            grid-template-columns: 1fr;
            gap: 1rem;
        }
        
        .filters-row {
            grid-template-columns: 1fr;
        }
        
        .applications-grid {
            gap: 1rem;
        }
        
        .application-card {
            grid-template-columns: 1fr;
            border-left: 5px solid;
        }
        
        .application-header {
            grid-column: 1 / -1;
            border-right: none;
            border-bottom: 1px solid #e5e7eb;
        }
        
        .application-content {
            grid-column: 1 / -1;
            border-right: none;
            border-bottom: 1px solid #e5e7eb;
            padding: 1rem;
        }
        
        .application-meta-right {
            grid-column: 1 / -1;
            border-right: none;
            border-bottom: 1px solid #e5e7eb;
            flex-direction: row;
            padding: 1rem;
            gap: 1rem;
        }
        
        .application-actions-right {
            grid-column: 1 / -1;
            display: flex;
            flex-direction: row;
            padding: 1rem;
            gap: 0.5rem;
        }
        
        .application-actions {
            flex-direction: column;
        }
        
        .btn-small {
            width: 100%;
            justify-content: center;
        }
        
        .applications-management {
            padding: 1rem;
        }
        
        .filters-section {
            padding: 1rem;
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
        
        // Filter change handlers
        const statusFilter = document.getElementById("statusFilter");
        if (statusFilter) {
            statusFilter.addEventListener("change", function() {
                updateFilters();
            });
        }
        
        const courseFilter = document.getElementById("courseFilter");
        if (courseFilter) {
            courseFilter.addEventListener("change", function() {
                updateFilters();
            });
        }
        
        function updateFilters() {
            const search = searchInput ? searchInput.value : "";
            const status = statusFilter ? statusFilter.value : "all";
            const courseId = courseFilter ? courseFilter.value : "0";
            
            let url = "?";
            if (search) url += "search=" + encodeURIComponent(search) + "&";
            if (status !== "all") url += "status=" + status + "&";
            if (courseId !== "0") url += "course_id=" + courseId + "&";
            
            window.location.href = url;
        }
    });
</script>';
?>

<div class="applications-management">
    <div class="card-header">
        <h1><i class="fas fa-user-graduate"></i> Course Applications</h1>
        <a href="index.php" class="view-all" style="background: var(--blue); color: white; padding: 0.75rem 1.5rem; border-radius: 40px;">
            <i class="fas fa-arrow-left"></i> Back to Courses
        </a>
    </div>
    
    <!-- Statistics Bar -->
    <div class="stats-bar">
        <div class="stat-card">
            <div class="stat-icon"><i class="fas fa-inbox"></i></div>
            <div class="stat-content">
                <h4>Total Applications</h4>
                <p><?php echo $totalApplications; ?></p>
            </div>
        </div>
        
        <?php
        $pendingStmt = $pdo->prepare("SELECT COUNT(*) as count FROM course_applications WHERE status = 'pending'");
        $pendingStmt->execute();
        $pendingCount = $pendingStmt->fetch()['count'];
        
        $acceptedStmt = $pdo->prepare("SELECT COUNT(*) as count FROM course_applications WHERE status = 'accepted'");
        $acceptedStmt->execute();
        $acceptedCount = $acceptedStmt->fetch()['count'];
        
        $rejectedStmt = $pdo->prepare("SELECT COUNT(*) as count FROM course_applications WHERE status = 'rejected'");
        $rejectedStmt->execute();
        $rejectedCount = $rejectedStmt->fetch()['count'];
        ?>
        
        <div class="stat-card">
            <div class="stat-icon"><i class="fas fa-clock"></i></div>
            <div class="stat-content">
                <h4>Pending Review</h4>
                <p><?php echo $pendingCount; ?></p>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon"><i class="fas fa-check-circle"></i></div>
            <div class="stat-content">
                <h4>Accepted</h4>
                <p><?php echo $acceptedCount; ?></p>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon"><i class="fas fa-times-circle"></i></div>
            <div class="stat-content">
                <h4>Rejected</h4>
                <p><?php echo $rejectedCount; ?></p>
            </div>
        </div>
    </div>
    
    <!-- Filters Section -->
    <div class="filters-section">
        <div class="filters-row">
            <div class="filter-group">
                <label for="searchInput">Search:</label>
                <input type="text" id="searchInput" placeholder="Search applications..." value="<?php echo htmlspecialchars($search); ?>">
            </div>
            
            <div class="filter-group">
                <label for="statusFilter">Status:</label>
                <select id="statusFilter">
                    <option value="all" <?php echo $status === 'all' ? 'selected' : ''; ?>>All Status</option>
                    <option value="pending" <?php echo $status === 'pending' ? 'selected' : ''; ?>>Pending</option>
                    <option value="reviewed" <?php echo $status === 'reviewed' ? 'selected' : ''; ?>>Reviewed</option>
                    <option value="accepted" <?php echo $status === 'accepted' ? 'selected' : ''; ?>>Accepted</option>
                    <option value="rejected" <?php echo $status === 'rejected' ? 'selected' : ''; ?>>Rejected</option>
                    <option value="enrolled" <?php echo $status === 'enrolled' ? 'selected' : ''; ?>>Enrolled</option>
                </select>
            </div>
            
            <div class="filter-group">
                <label for="courseFilter">Course:</label>
                <select id="courseFilter">
                    <option value="0" <?php echo $courseId === 0 ? 'selected' : ''; ?>>All Courses</option>
                    <?php foreach ($courses as $course): ?>
                        <option value="<?php echo $course['id']; ?>" <?php echo $courseId === $course['id'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($course['title']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
    </div>
    
    <div class="applications-grid">
        <?php if (count($applications) > 0): ?>
            <?php foreach ($applications as $application): ?>
                <div class="application-card status-<?php echo $application['status']; ?>">
                    <div class="application-header">
                        <div class="applicant-info">
                            <div class="applicant-name">
                                <?php echo htmlspecialchars($application['first_name'] . ' ' . $application['last_name']); ?>
                            </div>
                            <div class="applicant-email">
                                <i class="fas fa-envelope"></i> <?php echo htmlspecialchars($application['email']); ?>
                            </div>
                            <?php if ($application['phone']): ?>
                                <div class="applicant-phone">
                                    <i class="fas fa-phone"></i> <?php echo htmlspecialchars($application['phone']); ?>
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <div class="application-meta">
                            <div class="course-badge">
                                <?php echo htmlspecialchars($application['course_title']); ?>
                            </div>
                            <div class="status-badge status-<?php echo $application['status']; ?>">
                                <?php echo ucfirst($application['status']); ?>
                            </div>
                            <div style="color: #64748b; font-size: 0.75rem; margin-top: 0.5rem;">
                                <?php echo date('M j, Y H:i', strtotime($application['submitted_at'])); ?>
                            </div>
                        </div>
                    </div>
                    
                    <div class="application-content">
                        <div class="application-details">
                            <?php if ($application['nationality']): ?>
                                <div class="detail-item">
                                    <i class="fas fa-globe"></i>
                                    <span><?php echo htmlspecialchars($application['nationality']); ?></span>
                                </div>
                            <?php endif; ?>
                            
                            <?php if ($application['current_education']): ?>
                                <div class="detail-item">
                                    <i class="fas fa-graduation-cap"></i>
                                    <span><?php echo htmlspecialchars($application['current_education']); ?></span>
                                </div>
                            <?php endif; ?>
                            
                            <?php if ($application['how_hear_about']): ?>
                                <div class="detail-item">
                                    <i class="fas fa-info-circle"></i>
                                    <span><?php echo htmlspecialchars($application['how_hear_about']); ?></span>
                                </div>
                            <?php endif; ?>
                            
                            <?php if ($application['has_computer']): ?>
                                <div class="detail-item">
                                    <i class="fas fa-laptop"></i>
                                    <span>Has computer</span>
                                </div>
                            <?php endif; ?>
                            
                            <?php if ($application['needs_financial_aid']): ?>
                                <div class="detail-item">
                                    <i class="fas fa-dollar-sign"></i>
                                    <span>Needs aid</span>
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <?php if ($application['motivation']): ?>
                            <div class="motivation-text">
                                <strong>Motivation:</strong><br>
                                <?php echo nl2br(htmlspecialchars(substr($application['motivation'], 0, 200))); ?>
                                <?php if (strlen($application['motivation']) > 200): ?>
                                    <a href="#" onclick="showFullMotivation(this, '<?php echo htmlspecialchars($application['motivation']); ?>'); return false;">...more</a>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                        
                        <div class="application-actions">
                            <button class="btn-small btn-view" onclick="viewApplication(<?php echo $application['id']; ?>)">
                                <i class="fas fa-eye"></i> View Details
                            </button>
                            
                            <?php if (hasPermission('manage_course_applications')): ?>
                                <?php if ($application['status'] === 'pending'): ?>
                                    <button class="btn-small btn-review" onclick="updateApplicationStatus(<?php echo $application['id']; ?>, 'reviewed')">
                                        <i class="fas fa-check"></i> Mark Reviewed
                                    </button>
                                    <button class="btn-small btn-accept" onclick="updateApplicationStatus(<?php echo $application['id']; ?>, 'accepted')">
                                        <i class="fas fa-check-circle"></i> Accept
                                    </button>
                                    <button class="btn-small btn-reject" onclick="updateApplicationStatus(<?php echo $application['id']; ?>, 'rejected')">
                                        <i class="fas fa-times-circle"></i> Reject
                                    </button>
                                <?php elseif ($application['status'] === 'accepted'): ?>
                                    <button class="btn-small btn-enroll" onclick="enrollStudent(<?php echo $application['id']; ?>)">
                                        <i class="fas fa-user-plus"></i> Enroll Student
                                    </button>
                                <?php endif; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="no-results" style="text-align: center; padding: 3rem; color: #64748b;">
                <i class="fas fa-user-graduate" style="font-size: 3rem; margin-bottom: 1rem; opacity: 0.5;"></i>
                <h3>No applications found</h3>
                <p>There are no course applications matching your criteria.</p>
            </div>
        <?php endif; ?>
    </div>
    
    <!-- Pagination -->
    <?php if ($totalPages > 1): ?>
    <div class="pagination-container" style="margin-top: 2rem;">
        <div class="pagination">
            <?php if ($page > 1): ?>
                <a href="?page=<?php echo $page - 1; ?>&status=<?php echo urlencode($status); ?>&course_id=<?php echo $courseId; ?>&search=<?php echo urlencode($search); ?>" class="pagination-btn prev">
                    <i class="fas fa-chevron-left"></i> Previous
                </a>
            <?php endif; ?>
            
            <?php
            $start = max(1, $page - 2);
            $end = min($totalPages, $page + 2);
            
            if ($start > 1) {
                echo '<a href="?page=1&status=' . urlencode($status) . '&course_id=' . $courseId . '&search=' . urlencode($search) . '" class="pagination-btn">1</a>';
                if ($start > 2) echo '<span class="pagination-ellipsis">...</span>';
            }
            
            for ($i = $start; $i <= $end; $i++) {
                $active = $i == $page ? 'active' : '';
                echo '<a href="?page=' . $i . '&status=' . urlencode($status) . '&course_id=' . $courseId . '&search=' . urlencode($search) . '" class="pagination-btn ' . $active . '">' . $i . '</a>';
            }
            
            if ($end < $totalPages) {
                if ($end < $totalPages - 1) echo '<span class="pagination-ellipsis">...</span>';
                echo '<a href="?page=' . $totalPages . '&status=' . urlencode($status) . '&course_id=' . $courseId . '&search=' . urlencode($search) . '" class="pagination-btn">' . $totalPages . '</a>';
            }
            ?>
            
            <?php if ($page < $totalPages): ?>
                <a href="?page=<?php echo $page + 1; ?>&status=<?php echo urlencode($status); ?>&course_id=<?php echo $courseId; ?>&search=<?php echo urlencode($search); ?>" class="pagination-btn next">
                    Next <i class="fas fa-chevron-right"></i>
                </a>
            <?php endif; ?>
        </div>
    </div>
    <?php endif; ?>
</div>

<!-- Modal for viewing full application details -->
<div id="applicationModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1000; overflow-y: auto;">
    <div style="background: white; margin: 2rem auto; max-width: 800px; border-radius: 20px; padding: 2rem;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
            <h2>Application Details</h2>
            <button onclick="closeModal()" style="background: none; border: none; font-size: 1.5rem; cursor: pointer;">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div id="modalContent"></div>
    </div>
</div>

<script>
    function viewApplication(id) {
        fetch('application-details.php?id=' + id)
            .then(response => response.text())
            .then(html => {
                document.getElementById('modalContent').innerHTML = html;
                document.getElementById('applicationModal').style.display = 'block';
            });
    }
    
    function closeModal() {
        document.getElementById('applicationModal').style.display = 'none';
    }
    
    function showFullMotivation(element, fullText) {
        const container = element.parentElement;
        container.innerHTML = '<strong>Motivation:</strong><br>' + fullText.replace(/\n/g, '<br>');
    }
    
    function updateApplicationStatus(id, status) {
        if (confirm('Are you sure you want to update this application status to ' + status + '?')) {
            fetch('application-update.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'id=' + id + '&status=' + status
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    alert('Error: ' + data.message);
                }
            });
        }
    }
    
    function enrollStudent(id) {
        if (confirm('Are you sure you want to enroll this student?')) {
            fetch('application-enroll.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'id=' + id
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    alert('Error: ' + data.message);
                }
            });
        }
    }
</script>

<?php require_once '../includes/footer.php'; ?>
