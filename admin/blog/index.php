<?php
$pageTitle = 'Blog Management';
require_once '../includes/header.php';

$pdo = getDB();

// Handle status filter
$statusFilter = isset($_GET['status']) ? $_GET['status'] : 'all';
$searchTerm = isset($_GET['search']) ? sanitize($_GET['search']) : '';

// Build query
$query = "SELECT bp.*, u.first_name, u.last_name 
          FROM blog_posts bp 
          LEFT JOIN users u ON bp.author_id = u.id 
          WHERE 1=1";
$params = [];

if ($statusFilter !== 'all') {
    $query .= " AND bp.status = ?";
    $params[] = $statusFilter;
}

if ($searchTerm) {
    $query .= " AND (bp.title LIKE ? OR bp.excerpt LIKE ? OR bp.content LIKE ?)";
    $searchPattern = "%$searchTerm%";
    $params[] = $searchPattern;
    $params[] = $searchPattern;
    $params[] = $searchPattern;
}

$query .= " ORDER BY bp.created_at DESC";
$stmt = $pdo->prepare($query);
$stmt->execute($params);
$posts = $stmt->fetchAll();

$extraCss = '<style>
    :root {
        --blue: #1E64C8;
        --green: #2E7D32;
        --red: #D32F2F;
        --orange: #F39C12;
        --dark: #1E293B;
        --gray: #64748B;
        --light: #F8FAFC;
        --white: #FFFFFF;
        --transition: all 0.3s ease;
    }
    
    /* Card Header Styling */
    .card-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 2rem;
        padding: 1.5rem;
        background: white;
        border-radius: 12px;
        box-shadow: 0 4px 20px rgba(0,0,0,0.05);
    }
    
    .card-header h1 {
        color: var(--dark);
        font-size: 1.5rem;
        font-weight: 600;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }
    
    .card-header h1 i {
        color: var(--blue);
    }
    
    .view-all {
        background: var(--blue);
        color: white;
        padding: 0.75rem 1.5rem;
        border-radius: 40px;
        text-decoration: none;
        font-weight: 500;
        transition: var(--transition);
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
    }
    
    .view-all:hover {
        background: #1565c0;
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(30, 100, 200, 0.2);
        color: white;
    }
    
    .blog-management {
        padding: 2rem;
        background: var(--light);
        min-height: 100vh;
    }
    
    .blog-filters {
        display: flex;
        gap: 1rem;
        flex-wrap: wrap;
        margin-bottom: 2rem;
        align-items: center;
        padding: 1.5rem;
        background: white;
        border-radius: 12px;
        box-shadow: 0 4px 20px rgba(0,0,0,0.05);
    }
    
    .filter-badge {
        padding: 0.5rem 1rem;
        background: var(--light);
        border-radius: 40px;
        text-decoration: none;
        color: var(--dark);
        transition: var(--transition);
        border: 1px solid #E2E8F0;
        font-weight: 500;
    }
    
    .filter-badge.active,
    .filter-badge:hover {
        background: var(--blue);
        color: white;
        border-color: var(--blue);
        transform: translateY(-2px);
    }
    
    .search-form {
        margin-left: auto;
        display: flex;
        gap: 0.5rem;
    }
    
    .search-form input {
        padding: 0.5rem 1rem;
        border: 1px solid #E2E8F0;
        border-radius: 40px;
        outline: none;
        min-width: 250px;
        transition: var(--transition);
    }
    
    .search-form input:focus {
        border-color: var(--blue);
        box-shadow: 0 0 0 3px rgba(30, 100, 200, 0.1);
    }
    
    .search-form button {
        padding: 0.5rem 1.5rem;
        background: var(--blue);
        color: white;
        border: none;
        border-radius: 40px;
        cursor: pointer;
        transition: var(--transition);
    }
    
    .search-form button:hover {
        background: #1565c0;
        transform: translateY(-1px);
    }
    
    .blog-table {
        width: 100%;
        overflow-x: auto;
        background: white;
        border-radius: 12px;
        box-shadow: 0 4px 20px rgba(0,0,0,0.05);
    }
    
    .blog-table table {
        width: 100%;
        border-collapse: collapse;
    }
    
    .blog-table th,
    .blog-table td {
        padding: 1rem;
        text-align: left;
        border-bottom: 1px solid #E2E8F0;
    }
    
    .blog-table th {
        background: var(--light);
        font-weight: 600;
        color: var(--dark);
        font-size: 0.875rem;
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }
    
    .blog-table tr:hover {
        background: rgba(30, 100, 200, 0.02);
    }
    
    .blog-image {
        width: 60px;
        height: 60px;
        object-fit: cover;
        border-radius: 8px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
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
        transition: var(--transition);
        border: none;
        cursor: pointer;
    }
    
    .action-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    }
    
    .edit-btn {
        background: var(--blue);
    }
    
    .edit-btn:hover {
        background: #1565c0;
    }
    
    .delete-btn {
        background: var(--red);
    }
    
    .delete-btn:hover {
        background: #c62828;
    }
    
    .view-btn {
        background: var(--green);
    }
    
    .view-btn:hover {
        background: #1b5e20;
    }
    
    .publish-badge {
        padding: 0.25rem 0.75rem;
        border-radius: 20px;
        font-size: 0.75rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }
    
    .publish-badge.published {
        background: rgba(46, 125, 50, 0.1);
        color: var(--green);
        border: 1px solid rgba(46, 125, 50, 0.2);
    }
    
    .publish-badge.draft {
        background: rgba(243, 156, 18, 0.1);
        color: var(--orange);
        border: 1px solid rgba(243, 156, 18, 0.2);
    }
    
    .publish-badge.archived {
        background: rgba(100, 116, 139, 0.1);
        color: var(--gray);
        border: 1px solid rgba(100, 116, 139, 0.2);
    }
    
    /* Responsive */
    @media (max-width: 768px) {
        .blog-management {
            padding: 1rem;
        }
        
        .card-header {
            flex-direction: column;
            gap: 1rem;
            text-align: center;
        }
        
        .blog-filters {
            flex-direction: column;
            align-items: stretch;
        }
        
        .search-form {
            margin-left: 0;
            margin-top: 1rem;
        }
        
        .search-form input {
            min-width: auto;
            width: 100%;
        }
        
        .action-buttons {
            flex-direction: column;
        }
    }
</style>';

?>

<div class="blog-management">
    <div class="card-header">
        <h1><i class="fas fa-blog"></i> Blog Management</h1>
        <?php if (hasPermission('create_blog')): ?>
        <a href="create.php" class="view-all" style="background: var(--blue); color: white; padding: 0.75rem 1.5rem; border-radius: 40px;">
            <i class="fas fa-plus"></i> Create New Post
        </a>
        <?php endif; ?>
    </div>
    
    <div class="blog-filters">
        <a href="?status=all" class="filter-badge <?php echo $statusFilter === 'all' ? 'active' : ''; ?>">
            All Posts
        </a>
        <a href="?status=published" class="filter-badge <?php echo $statusFilter === 'published' ? 'active' : ''; ?>">
            Published
        </a>
        <a href="?status=draft" class="filter-badge <?php echo $statusFilter === 'draft' ? 'active' : ''; ?>">
            Drafts
        </a>
        <a href="?status=archived" class="filter-badge <?php echo $statusFilter === 'archived' ? 'active' : ''; ?>">
            Archived
        </a>
        
        <form method="GET" class="search-form">
            <input type="hidden" name="status" value="<?php echo $statusFilter; ?>">
            <input type="text" name="search" placeholder="Search posts..." value="<?php echo htmlspecialchars($searchTerm); ?>">
            <button type="submit"><i class="fas fa-search"></i></button>
        </form>
    </div>
    
    <div class="blog-table">
        <table>
            <thead>
                <tr>
                    <th>Image</th>
                    <th>Title</th>
                    <th>Author</th>
                    <th>Category</th>
                    <th>Status</th>
                    <th>Views</th>
                    <th>Date</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($posts) > 0): ?>
                    <?php foreach ($posts as $post): ?>
                    <tr>
                        <td>
                            <?php if ($post['featured_image']): ?>
                                <img src="<?php echo $post['featured_image']; ?>" alt="" class="blog-image">
                            <?php else: ?>
                                <div class="blog-image" style="background: var(--light); display: flex; align-items: center; justify-content: center;">
                                    <i class="fas fa-image"></i>
                                </div>
                            <?php endif; ?>
                        </td>
                        <td>
                            <strong><?php echo htmlspecialchars(truncate($post['title'], 50)); ?></strong>
                        </td>
                        <td><?php echo htmlspecialchars($post['first_name'] . ' ' . $post['last_name']); ?></td>
                        <td><?php echo ucfirst($post['category']); ?></td>
                        <td>
                            <span class="publish-badge <?php echo $post['status']; ?>">
                                <?php echo ucfirst($post['status']); ?>
                            </span>
                        </td>
                        <td><?php echo number_format($post['views']); ?></td>
                        <td><?php echo formatDate($post['created_at']); ?></td>
                        <td class="action-buttons">
                            <a href="edit.php?id=<?php echo $post['id']; ?>" class="action-btn edit-btn" title="Edit">
                                <i class="fas fa-edit"></i>
                            </a>
                            <?php if (hasPermission('delete_blog')): ?>
                            <a href="delete.php?id=<?php echo $post['id']; ?>" class="action-btn delete-btn" title="Delete" onclick="return confirm('Are you sure you want to delete this post?')">
                                <i class="fas fa-trash"></i>
                            </a>
                            <?php endif; ?>
                            <a href="<?php echo SITE_URL . 'blog.php?id=' . $post['id']; ?>" target="_blank" class="action-btn view-btn" title="View">
                                <i class="fas fa-eye"></i>
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="8" style="text-align: center; padding: 3rem;">
                            <i class="fas fa-edit" style="font-size: 3rem; opacity: 0.5;"></i>
                            <p>No blog posts found.</p>
                            <a href="create.php" class="action-btn edit-btn" style="margin-top: 1rem;">Create your first post</a>
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>