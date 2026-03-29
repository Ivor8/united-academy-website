<?php
$pageTitle = 'Dashboard';
require_once 'includes/header.php';

$pdo = getDB();

// Get counts
$blogCount = $pdo->query("SELECT COUNT(*) FROM blog_posts WHERE status != 'archived'")->fetchColumn();
$testimonialsCount = $pdo->query("SELECT COUNT(*) FROM testimonials")->fetchColumn();
$pendingTestimonials = $pdo->query("SELECT COUNT(*) FROM testimonials WHERE status = 'pending'")->fetchColumn();
$usersCount = $pdo->query("SELECT COUNT(*) FROM users WHERE is_active = 1")->fetchColumn();

// Get recent blog posts
$recentBlogs = $pdo->query("
    SELECT id, title, status, created_at, views 
    FROM blog_posts 
    ORDER BY created_at DESC 
    LIMIT 5
")->fetchAll();

// Get recent testimonials
$recentTestimonials = $pdo->query("
    SELECT id, student_name, status, created_at 
    FROM testimonials 
    ORDER BY created_at DESC 
    LIMIT 5
")->fetchAll();

// Get recent activity
$recentActivity = $pdo->prepare("
    SELECT al.*, u.first_name, u.last_name 
    FROM activity_logs al
    LEFT JOIN users u ON al.user_id = u.id
    ORDER BY al.created_at DESC 
    LIMIT 10
");
$recentActivity->execute();
$activities = $recentActivity->fetchAll();
?>

<div class="dashboard-container">
    <!-- Welcome Banner -->
    <div class="welcome-banner animate-fade-in">
        <div class="welcome-text">
            <h1>Welcome back, <?php echo htmlspecialchars($_SESSION['full_name']); ?>!</h1>
            <p>Here's what's happening with your website today.</p>
        </div>
        <div class="welcome-date">
            <i class="fas fa-calendar-alt"></i>
            <span><?php echo date('l, F j, Y'); ?></span>
        </div>
    </div>
    
    <!-- Stats Grid -->
    <div class="stats-grid">
        <div class="stat-card animate-slide-up">
            <div class="stat-icon blue">
                <i class="fas fa-blog"></i>
            </div>
            <div class="stat-info">
                <h3><?php echo number_format($blogCount); ?></h3>
                <p>Total Posts</p>
            </div>
            <div class="stat-trend positive">
                <i class="fas fa-arrow-up"></i> 12%
            </div>
        </div>
        
        <div class="stat-card animate-slide-up">
            <div class="stat-icon green">
                <i class="fas fa-star"></i>
            </div>
            <div class="stat-info">
                <h3><?php echo number_format($testimonialsCount); ?></h3>
                <p>Testimonials</p>
            </div>
            <div class="stat-trend">
                <i class="fas fa-chart-line"></i> Total
            </div>
        </div>
        
        <div class="stat-card animate-slide-up">
            <div class="stat-icon orange">
                <i class="fas fa-clock"></i>
            </div>
            <div class="stat-info">
                <h3><?php echo number_format($pendingTestimonials); ?></h3>
                <p>Pending Approval</p>
            </div>
            <div class="stat-trend warning">
                <i class="fas fa-exclamation"></i> Needs review
            </div>
        </div>
        
        <div class="stat-card animate-slide-up">
            <div class="stat-icon purple">
                <i class="fas fa-users"></i>
            </div>
            <div class="stat-info">
                <h3><?php echo number_format($usersCount); ?></h3>
                <p>Active Users</p>
            </div>
            <div class="stat-trend positive">
                <i class="fas fa-user-plus"></i> Active
            </div>
        </div>
    </div>
    
    <!-- Quick Actions -->
    <div class="quick-actions animate-fade-in">
        <h3>Quick Actions</h3>
        <div class="actions-grid">
            <?php if (hasPermission('create_blog')): ?>
            <a href="blog/create.php" class="action-btn">
                <i class="fas fa-plus-circle"></i>
                <span>New Blog Post</span>
            </a>
            <?php endif; ?>
            <?php if (hasPermission('create_testimonials')): ?>
            <a href="testimonials/create.php" class="action-btn">
                <i class="fas fa-user-plus"></i>
                <span>Add Testimonial</span>
            </a>
            <?php endif; ?>
            <a href="#" class="action-btn" onclick="refreshData()">
                <i class="fas fa-sync-alt"></i>
                <span>Refresh Data</span>
            </a>
        </div>
    </div>
    
    <!-- Recent Content Grid -->
    <div class="recent-grid">
        <!-- Recent Blog Posts -->
        <div class="recent-card animate-slide-up">
            <div class="card-header">
                <h3><i class="fas fa-newspaper"></i> Recent Blog Posts</h3>
                <a href="blog/index.php" class="view-all">View All <i class="fas fa-arrow-right"></i></a>
            </div>
            <div class="card-content">
                <?php if (count($recentBlogs) > 0): ?>
                    <div class="recent-list">
                        <?php foreach ($recentBlogs as $blog): ?>
                        <div class="list-item">
                            <div class="item-info">
                                <h4><?php echo htmlspecialchars(truncate($blog['title'], 40)); ?></h4>
                                <div class="item-meta">
                                    <span class="status <?php echo $blog['status']; ?>">
                                        <?php echo ucfirst($blog['status']); ?>
                                    </span>
                                    <span><i class="fas fa-eye"></i> <?php echo number_format($blog['views']); ?></span>
                                    <span><i class="fas fa-calendar"></i> <?php echo formatDate($blog['created_at']); ?></span>
                                </div>
                            </div>
                            <div class="item-actions">
                                <a href="blog/edit.php?id=<?php echo $blog['id']; ?>" class="edit-btn" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </a>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="empty-state">
                        <i class="fas fa-edit"></i>
                        <p>No blog posts yet. <a href="blog/create.php">Create your first post</a></p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Recent Testimonials -->
        <div class="recent-card animate-slide-up">
            <div class="card-header">
                <h3><i class="fas fa-star"></i> Recent Testimonials</h3>
                <a href="testimonials/index.php" class="view-all">View All <i class="fas fa-arrow-right"></i></a>
            </div>
            <div class="card-content">
                <?php if (count($recentTestimonials) > 0): ?>
                    <div class="recent-list">
                        <?php foreach ($recentTestimonials as $testimonial): ?>
                        <div class="list-item">
                            <div class="item-info">
                                <h4><?php echo htmlspecialchars($testimonial['student_name']); ?></h4>
                                <div class="item-meta">
                                    <span class="status <?php echo $testimonial['status']; ?>">
                                        <?php echo ucfirst($testimonial['status']); ?>
                                    </span>
                                    <span><i class="fas fa-calendar"></i> <?php echo formatDate($testimonial['created_at']); ?></span>
                                </div>
                            </div>
                            <div class="item-actions">
                                <a href="testimonials/edit.php?id=<?php echo $testimonial['id']; ?>" class="edit-btn" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </a>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="empty-state">
                        <i class="fas fa-star"></i>
                        <p>No testimonials yet. <a href="testimonials/create.php">Add your first testimonial</a></p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <!-- Recent Activity Log -->
    <div class="activity-card animate-slide-up">
        <div class="card-header">
            <h3><i class="fas fa-history"></i> Recent Activity</h3>
            <button class="refresh-btn" onclick="loadActivity()"><i class="fas fa-sync-alt"></i></button>
        </div>
        <div class="card-content">
            <div class="activity-timeline" id="activityTimeline">
                <?php if (count($activities) > 0): ?>
                    <?php foreach ($activities as $activity): ?>
                    <div class="activity-item">
                        <div class="activity-icon">
                            <?php
                            $icon = 'fa-edit';
                            if (strpos($activity['action'], 'CREATE') !== false) $icon = 'fa-plus-circle';
                            if (strpos($activity['action'], 'UPDATE') !== false) $icon = 'fa-sync-alt';
                            if (strpos($activity['action'], 'DELETE') !== false) $icon = 'fa-trash-alt';
                            if (strpos($activity['action'], 'LOGIN') !== false) $icon = 'fa-sign-in-alt';
                            ?>
                            <i class="fas <?php echo $icon; ?>"></i>
                        </div>
                        <div class="activity-content">
                            <p>
                                <strong><?php echo htmlspecialchars($activity['first_name'] . ' ' . $activity['last_name']); ?></strong>
                                <?php echo strtolower($activity['action']); ?>
                                <span class="activity-table"><?php echo $activity['table_name']; ?></span>
                            </p>
                            <span class="activity-time">
                                <i class="far fa-clock"></i> 
                                <?php echo date('M d, Y H:i', strtotime($activity['created_at'])); ?>
                            </span>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="empty-state">
                        <i class="fas fa-history"></i>
                        <p>No recent activity found.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script>
function refreshData() {
    location.reload();
}

function loadActivity() {
    fetch('ajax/get_activity.php')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const timeline = document.getElementById('activityTimeline');
                // Update timeline with new data
            }
        });
}

// Auto refresh every 60 seconds
setInterval(() => {
    fetch('ajax/get_stats.php')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Update stats without reload
            }
        });
}, 60000);
</script>

<?php require_once 'includes/footer.php'; ?>