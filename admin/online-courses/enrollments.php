<?php
$pageTitle = 'Course Enrollments';
require_once '../includes/config.php';
require_once '../includes/auth.php';
requireLogin();

if (!hasPermission('view_course_enrollments')) {
    header('Location: index.php');
    exit();
}

$pdo = getDB();
$search = isset($_GET['search']) ? sanitize($_GET['search']) : '';
$courseId = isset($_GET['course_id']) ? intval($_GET['course_id']) : 0;
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$limit = 10;
$offset = ($page - 1) * $limit;

// Build query
$query = "SELECT ca.*, oc.title as course_title, oc.category as course_category, oc.level as course_level
          FROM course_applications ca 
          LEFT JOIN online_courses oc ON ca.course_id = oc.id 
          WHERE ca.status = 'accepted'";

$params = [];

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
$enrollments = $stmt->fetchAll();

// Get total count for pagination
$countQuery = "SELECT COUNT(*) as total FROM course_applications ca LEFT JOIN online_courses oc ON ca.course_id = oc.id WHERE ca.status = 'accepted'";
$countParams = [];

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
$totalEnrollments = $countStmt->fetch()['total'];
$totalPages = ceil($totalEnrollments / $limit);

// Get all courses for filter dropdown
$coursesStmt = $pdo->prepare("SELECT id, title FROM online_courses WHERE status = 'published' ORDER BY title");
$coursesStmt->execute();
$courses = $coursesStmt->fetchAll();

$extraCss = '<style>
    * {
        --transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }
    
    .enrollments-management {
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
    
    /* Enrollments Grid */
    .enrollments-grid {
        display: grid;
        gap: 1.5rem;
        margin-top: 2rem;
    }
    
    .enrollment-card {
        background: white;
        border-radius: 18px;
        box-shadow: 0 10px 28px rgba(15,23,42,0.08);
        overflow: hidden;
        transition: var(--transition);
        border-left: 5px solid #1E64C8;
        display: flex;
        flex-direction: column;
    }
    
    .enrollment-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 18px 34px rgba(15,23,42,0.12);
    }
    
    .enrollment-card.status-active {
        border-left-color: #2E7D32;
        background: linear-gradient(to right, rgba(46,125,50,0.03), white);
    }
    
    .enrollment-card.status-completed {
        border-left-color: #1565C0;
        background: linear-gradient(to right, rgba(21,101,192,0.03), white);
    }
    
    .enrollment-card.status-dropped {
        border-left-color: #D32F2F;
        background: linear-gradient(to right, rgba(211,47,47,0.03), white);
    }
    
    .enrollment-card.status-suspended {
        border-left-color: #F57C00;
        background: linear-gradient(to right, rgba(245,124,0,0.03), white);
    }
    
    .enrollment-card-top {
        display: flex;
        justify-content: space-between;
        gap: 1.5rem;
        align-items: flex-start;
        padding: 1.5rem;
        background: rgba(248,250,252,0.9);
        border-bottom: 1px solid #e5e7eb;
    }
    
    .enrollment-header {
        display: flex;
        flex-direction: column;
        gap: 0.75rem;
        flex: 1;
    }
    
    .enrollment-meta-right {
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        align-items: flex-end;
        gap: 0.75rem;
        min-width: 170px;
        color: #475569;
        font-size: 0.95rem;
    }
    
    .enrollment-card-body {
        padding: 1.5rem;
        display: flex;
        flex-direction: column;
        gap: 1.25rem;
    }
    
    .enrollment-card-footer {
        padding: 1rem 1.5rem 1.5rem;
        background: rgba(248,250,252,0.7);
        border-top: 1px solid #e5e7eb;
    }
    
    .meta-line {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        color: #64748b;
        font-size: 0.9rem;
    }
    
    .student-info {
        flex: 1;
    }
    
    .student-name {
        font-size: 1rem;
        font-weight: 700;
        color: var(--dark);
        margin-bottom: 0.5rem;
        line-height: 1.3;
    }
    
    .student-email {
        color: #64748b;
        font-size: 0.85rem;
        margin-bottom: 0.25rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }
    
    .student-phone {
        color: #64748b;
        font-size: 0.85rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }
    
    .enrollment-meta {
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
    
    .status-active {
        background: rgba(46,125,50,0.15);
        color: #15803D;
    }
    
    .status-completed {
        background: rgba(21,101,192,0.15);
        color: #1D4ED8;
    }
    
    .status-dropped {
        background: rgba(211,47,47,0.15);
        color: #DC2626;
    }
    
    .status-suspended {
        background: rgba(245,124,0,0.15);
        color: #EA580C;
    }
    
    .enrollment-details {
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
    
    .enrollment-actions {
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
    
    .btn-complete {
        background: linear-gradient(135deg, #2E7D32 0%, #1B5E20 100%);
        color: white;
    }
    
    .btn-suspend {
        background: linear-gradient(135deg, #F57C00 0%, #EF6C00 100%);
        color: white;
    }
    
    .btn-drop {
        background: linear-gradient(135deg, #D32F2F 0%, #B71C1C 100%);
        color: white;
    }
    
    .btn-view {
        background: linear-gradient(135deg, #1E64C8 0%, #1565C0 100%);
        color: white;
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
        .enrollment-card {
            grid-template-columns: auto 1fr auto;
        }
        
        .enrollment-actions-right {
            display: none;
        }
        
        .enrollment-content {
            border-right: none;
        }
        
        .enrollment-meta-right {
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
        
        .enrollments-grid {
            gap: 1rem;
        }
        
        .enrollment-card {
            grid-template-columns: 1fr;
            border-left: 5px solid;
        }
        
        .enrollment-header {
            grid-column: 1 / -1;
            border-right: none;
            border-bottom: 1px solid #e5e7eb;
        }
        
        .enrollment-content {
            grid-column: 1 / -1;
            border-right: none;
            border-bottom: 1px solid #e5e7eb;
            padding: 1rem;
        }
        
        .enrollment-meta-right {
            grid-column: 1 / -1;
            border-right: none;
            border-bottom: 1px solid #e5e7eb;
            flex-direction: row;
            padding: 1rem;
            gap: 1rem;
        }
        
        .enrollment-actions-right {
            grid-column: 1 / -1;
            display: flex;
            flex-direction: row;
            padding: 1rem;
            gap: 0.5rem;
        }
        
        .enrollment-actions {
            flex-direction: column;
        }
        
        .btn-small {
            width: 100%;
            justify-content: center;
        }
        
        .enrollments-management {
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
require_once '../includes/header.php';
?>

<div class="enrollments-management">
    <div class="card-header">
        <h1><i class="fas fa-users"></i> Course Enrollments</h1>
        <a href="index.php" class="view-all" style="background: var(--blue); color: white; padding: 0.75rem 1.5rem; border-radius: 40px;">
            <i class="fas fa-arrow-left"></i> Back to Courses
        </a>
    </div>
    
    <!-- Statistics Bar -->
    <div class="stats-bar">
        <div class="stat-card">
            <div class="stat-icon"><i class="fas fa-users"></i></div>
            <div class="stat-content">
                <h4>Accepted Applications</h4>
                <p><?php echo $totalEnrollments; ?></p>
            </div>
        </div>
    </div>
    
    <!-- Filters Section -->
    <div class="filters-section">
        <div class="filters-row">
            <div class="filter-group">
                <label for="searchInput">Search:</label>
                <input type="text" id="searchInput" placeholder="Search accepted applications..." value="<?php echo htmlspecialchars($search); ?>">
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
    
    <div class="enrollments-grid">
        <?php if (count($enrollments) > 0): ?>
            <?php foreach ($enrollments as $enrollment): ?>
                <div class="enrollment-card status-accepted">
                    <div class="enrollment-card-top">
                        <div class="enrollment-header">
                            <div class="student-info">
                                <div class="student-name">
                                    <?php echo htmlspecialchars($enrollment['first_name'] . ' ' . $enrollment['last_name']); ?>
                                </div>
                                <div class="student-email">
                                    <i class="fas fa-envelope"></i> <?php echo htmlspecialchars($enrollment['email']); ?>
                                </div>
                                <?php if ($enrollment['phone']): ?>
                                    <div class="student-phone">
                                        <i class="fas fa-phone"></i> <?php echo htmlspecialchars($enrollment['phone']); ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                            
                            <div class="enrollment-meta">
                                <div class="course-badge">
                                    <?php echo htmlspecialchars($enrollment['course_title']); ?>
                                </div>
                                <div class="status-badge status-accepted">
                                    Accepted
                                </div>
                            </div>
                        </div>
                        <div class="enrollment-meta-right">
                            <div class="meta-line">
                                <i class="fas fa-calendar-alt"></i>
                                Applied: <?php echo date('M j, Y H:i', strtotime($enrollment['submitted_at'])); ?>
                            </div>
                        </div>
                    </div>
                    
                    <div class="enrollment-card-body">
                        <div class="enrollment-details">
                            <?php if ($enrollment['nationality']): ?>
                                <div class="detail-item">
                                    <i class="fas fa-globe"></i>
                                    <span><?php echo htmlspecialchars($enrollment['nationality']); ?></span>
                                </div>
                            <?php endif; ?>
                            
                            <?php if ($enrollment['current_education']): ?>
                                <div class="detail-item">
                                    <i class="fas fa-graduation-cap"></i>
                                    <span><?php echo htmlspecialchars($enrollment['current_education']); ?></span>
                                </div>
                            <?php endif; ?>
                            
                            <?php if ($enrollment['how_hear_about']): ?>
                                <div class="detail-item">
                                    <i class="fas fa-info-circle"></i>
                                    <span><?php echo htmlspecialchars($enrollment['how_hear_about']); ?></span>
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <?php if ($enrollment['motivation']): ?>
                            <div class="motivation-text">
                                <strong>Motivation:</strong><br>
                                <?php echo nl2br(htmlspecialchars(substr($enrollment['motivation'], 0, 200))); ?>
                                <?php if (strlen($enrollment['motivation']) > 200): ?>
                                    <a href="#" onclick="showFullMotivation(this, '<?php echo htmlspecialchars($enrollment['motivation']); ?>'); return false;">...more</a>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="enrollment-card-footer">
                        <div class="enrollment-actions">
                            <button class="btn-small btn-view" onclick="viewApplication(<?php echo $enrollment['id']; ?>)">
                                <i class="fas fa-eye"></i> View Details
                            </button>
                            
                            <?php if (hasPermission('manage_course_applications')): ?>
                                <button class="btn-small btn-enroll" onclick="enrollStudent(<?php echo $enrollment['id']; ?>)">
                                    <i class="fas fa-user-plus"></i> Enroll Student
                                </button>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="no-results" style="text-align: center; padding: 3rem; color: #64748b;">
                <i class="fas fa-users" style="font-size: 3rem; margin-bottom: 1rem; opacity: 0.5;"></i>
                <h3>No accepted applications found</h3>
                <p>There are no accepted course applications matching your criteria.</p>
            </div>
        <?php endif; ?>
    </div>
    
    <!-- Pagination -->
    <?php if ($totalPages > 1): ?>
    <div class="pagination-container" style="margin-top: 2rem;">
        <div class="pagination">
            <?php if ($page > 1): ?>
                <a href="?page=<?php echo $page - 1; ?>&course_id=<?php echo $courseId; ?>&search=<?php echo urlencode($search); ?>" class="pagination-btn prev">
                    <i class="fas fa-chevron-left"></i> Previous
                </a>
            <?php endif; ?>
            
            <?php
            $start = max(1, $page - 2);
            $end = min($totalPages, $page + 2);
            
            if ($start > 1) {
                echo '<a href="?page=1&course_id=' . $courseId . '&search=' . urlencode($search) . '" class="pagination-btn">1</a>';
                if ($start > 2) echo '<span class="pagination-ellipsis">...</span>';
            }
            
            for ($i = $start; $i <= $end; $i++) {
                $active = $i == $page ? 'active' : '';
                echo '<a href="?page=' . $i . '&course_id=' . $courseId . '&search=' . urlencode($search) . '" class="pagination-btn ' . $active . '">' . $i . '</a>';
            }
            
            if ($end < $totalPages) {
                if ($end < $totalPages - 1) echo '<span class="pagination-ellipsis">...</span>';
                echo '<a href="?page=' . $totalPages . '&course_id=' . $courseId . '&search=' . urlencode($search) . '" class="pagination-btn">' . $totalPages . '</a>';
            }
            ?>
            
            <?php if ($page < $totalPages): ?>
                <a href="?page=<?php echo $page + 1; ?>&course_id=<?php echo $courseId; ?>&search=<?php echo urlencode($search); ?>" class="pagination-btn next">
                    Next <i class="fas fa-chevron-right"></i>
                </a>
            <?php endif; ?>
        </div>
    </div>
    <?php endif; ?>
</div>

<!-- Modal for viewing full enrollment details -->
<div id="enrollmentModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1000; overflow-y: auto;">
    <div style="background: white; margin: 2rem auto; max-width: 800px; border-radius: 20px; padding: 2rem;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
            <h2>Enrollment Details</h2>
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
                document.getElementById('enrollmentModal').style.display = 'block';
            });
    }
    
    function closeModal() {
        document.getElementById('enrollmentModal').style.display = 'none';
    }
    
    function showFullMotivation(element, fullText) {
        const container = element.parentElement;
        container.innerHTML = '<strong>Motivation:</strong><br>' + fullText.replace(/\n/g, '<br>');
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
    
    // Search functionality
    document.addEventListener("DOMContentLoaded", function() {
        const searchInput = document.getElementById("searchInput");
        if (searchInput) {
            searchInput.addEventListener("keyup", function(e) {
                if (e.key === "Enter") {
                    window.location.href = "?search=" + encodeURIComponent(this.value);
                }
            });
        }
        
        // Filter change handlers
        const courseFilter = document.getElementById("courseFilter");
        if (courseFilter) {
            courseFilter.addEventListener("change", function() {
                updateFilters();
            });
        }
        
        function updateFilters() {
            const search = searchInput ? searchInput.value : "";
            const courseId = courseFilter ? courseFilter.value : "0";
            
            let url = "?";
            if (search) url += "search=" + encodeURIComponent(search) + "&";
            if (courseId !== "0") url += "course_id=" + courseId + "&";
            
            window.location.href = url;
        }
    });
</script>

<?php require_once '../includes/footer.php'; ?>