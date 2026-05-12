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
    .applications-management {
        padding: 1rem;
    }
    
    .applications-grid {
        display: grid;
        gap: 1.5rem;
        margin-top: 2rem;
    }
    
    .application-card {
        background: white;
        border-radius: 16px;
        box-shadow: 0 4px 20px rgba(0,0,0,0.08);
        overflow: hidden;
        transition: all 0.3s ease;
        border-left: 4px solid #1E64C8;
    }
    
    .application-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 12px 30px rgba(0,0,0,0.12);
    }
    
    .application-card.status-accepted {
        border-left-color: #2E7D32;
    }
    
    .application-card.status-rejected {
        border-left-color: #D32F2F;
    }
    
    .application-card.status-reviewed {
        border-left-color: #F59E0B;
    }
    
    .application-header {
        padding: 1.5rem;
        background: var(--light);
        border-bottom: 1px solid #e5e7eb;
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
    }
    
    .applicant-info {
        flex: 1;
    }
    
    .applicant-name {
        font-size: 1.2rem;
        font-weight: 700;
        color: var(--dark);
        margin-bottom: 0.5rem;
    }
    
    .applicant-email {
        color: #64748b;
        font-size: 0.9rem;
        margin-bottom: 0.25rem;
    }
    
    .applicant-phone {
        color: #64748b;
        font-size: 0.9rem;
    }
    
    .application-meta {
        text-align: right;
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
        padding: 0.4rem 0.8rem;
        border-radius: 20px;
        font-size: 0.75rem;
        font-weight: 600;
    }
    
    .status-pending {
        background: rgba(245,158,11,0.1);
        color: #F59E0B;
    }
    
    .status-reviewed {
        background: rgba(59,130,246,0.1);
        color: #3B82F6;
    }
    
    .status-accepted {
        background: rgba(46,125,50,0.1);
        color: #2E7D32;
    }
    
    .status-rejected {
        background: rgba(211,47,47,0.1);
        color: #D32F2F;
    }
    
    .status-enrolled {
        background: rgba(124,58,237,0.1);
        color: #7C3AED;
    }
    
    .application-content {
        padding: 1.5rem;
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
    }
    
    .detail-item i {
        color: #1E64C8;
        width: 16px;
    }
    
    .motivation-text {
        color: #475569;
        line-height: 1.6;
        margin-bottom: 1.5rem;
        padding: 1rem;
        background: #f8fafc;
        border-radius: 8px;
        border-left: 3px solid #1E64C8;
    }
    
    .application-actions {
        display: flex;
        gap: 0.5rem;
        flex-wrap: wrap;
    }
    
    .btn-small {
        padding: 0.4rem 0.8rem;
        font-size: 0.8rem;
        text-decoration: none;
        border-radius: 8px;
        transition: all 0.3s ease;
        border: none;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 0.3rem;
    }
    
    .btn-small:hover {
        transform: translateY(-2px);
    }
    
    .btn-accept {
        background: #2E7D32;
        color: white;
    }
    
    .btn-accept:hover {
        background: #1B5E20;
    }
    
    .btn-reject {
        background: #D32F2F;
        color: white;
    }
    
    .btn-reject:hover {
        background: #B71C1C;
    }
    
    .btn-review {
        background: #F59E0B;
        color: white;
    }
    
    .btn-review:hover {
        background: #D97706;
    }
    
    .btn-enroll {
        background: #7C3AED;
        color: white;
    }
    
    .btn-enroll:hover {
        background: #6D28D9;
    }
    
    .btn-view {
        background: #1E64C8;
        color: white;
    }
    
    .btn-view:hover {
        background: #1565C0;
    }
    
    .filters-section {
        background: white;
        padding: 1.5rem;
        border-radius: 12px;
        margin-bottom: 2rem;
        box-shadow: 0 2px 10px rgba(0,0,0,0.05);
    }
    
    .filters-row {
        display: flex;
        gap: 1rem;
        align-items: center;
        flex-wrap: wrap;
    }
    
    .filter-group {
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }
    
    .filter-group label {
        font-weight: 600;
        color: var(--dark);
    }
    
    .filter-group select,
    .filter-group input {
        padding: 0.5rem;
        border: 1px solid #e5e7eb;
        border-radius: 8px;
    }
    
    @media (max-width: 768px) {
        .applications-grid {
            gap: 1rem;
        }
        
        .application-card {
            border-radius: 12px;
        }
        
        .application-header {
            flex-direction: column;
            gap: 1rem;
            text-align: left;
        }
        
        .application-meta {
            text-align: left;
        }
        
        .application-details {
            grid-template-columns: 1fr;
            gap: 0.5rem;
        }
        
        .application-actions {
            flex-direction: column;
        }
        
        .filters-row {
            flex-direction: column;
            align-items: stretch;
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
        <div style="display: flex; gap: 1rem; align-items: center;">
            <span style="color: #64748b; font-size: 0.9rem;">
                <?php echo $totalApplications; ?> Total Applications
            </span>
            <a href="index.php" class="view-all">Back to Courses</a>
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
