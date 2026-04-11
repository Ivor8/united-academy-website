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

?>

<div class="blog-management">
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
        
        /* Blog Management Container */
        .blog-management {
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
        .blog-filters {
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
        
        /* Blog Table */
        .blog-table {
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
        
        .blog-table table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .blog-table th {
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
        
        .blog-table th:first-child {
            border-radius: 16px 0 0 0;
        }
        
        .blog-table th:last-child {
            border-radius: 0 16px 0 0;
        }
        
        .blog-table td {
            padding: 1.25rem;
            text-align: left;
            border-bottom: 1px solid #F1F5F9;
            color: var(--dark);
            font-size: 0.95rem;
        }
        
        .blog-table tbody tr {
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
        
        .blog-table tbody tr:hover {
            background: var(--blue-light);
            transform: scale(1.005);
            box-shadow: inset 0 2px 8px rgba(30, 100, 200, 0.08);
        }
        
        .blog-table tbody tr:last-child td {
            border-bottom: none;
        }
        
        /* Blog Image */
        .blog-image {
            width: 70px;
            height: 70px;
            object-fit: cover;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            transition: var(--transition);
            border: 2px solid #E2E8F0;
        }
        
        .blog-table tbody tr:hover .blog-image {
            transform: scale(1.08);
            box-shadow: 0 6px 18px rgba(0,0,0,0.2);
        }
        
        .blog-table td strong {
            color: var(--dark);
            font-weight: 700;
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
        
        .view-btn {
            background: linear-gradient(135deg, var(--green) 0%, #1b5e20 100%);
        }
        
        .view-btn:hover {
            background: linear-gradient(135deg, #1b5e20 0%, #004d40 100%);
        }
        
        /* Status Badges */
        .publish-badge {
            padding: 0.35rem 0.9rem;
            border-radius: 25px;
            font-size: 0.75rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.06em;
            display: inline-block;
            transition: var(--transition);
        }
        
        .publish-badge.published {
            background: var(--green-light);
            color: var(--green);
            border: 1.5px solid var(--green);
            box-shadow: inset 0 2px 4px rgba(46, 125, 50, 0.1);
        }
        
        .publish-badge.published:hover {
            box-shadow: 0 4px 12px rgba(46, 125, 50, 0.2);
        }
        
        .publish-badge.draft {
            background: var(--orange-light);
            color: var(--orange);
            border: 1.5px solid var(--orange);
            box-shadow: inset 0 2px 4px rgba(243, 156, 18, 0.1);
        }
        
        .publish-badge.draft:hover {
            box-shadow: 0 4px 12px rgba(243, 156, 18, 0.2);
        }
        
        .publish-badge.archived {
            background: #F0F4F8;
            color: var(--gray);
            border: 1.5px solid #CBD5E0;
            box-shadow: inset 0 2px 4px rgba(100, 116, 139, 0.1);
        }
        
        .publish-badge.archived:hover {
            box-shadow: 0 4px 12px rgba(100, 116, 139, 0.15);
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
            .blog-management {
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
            
            .blog-filters {
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
            }
            
            .search-form input {
                min-width: auto;
                width: 100%;
                order: 1;
            }
            
            .search-form button {
                order: 2;
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
            
            .blog-table {
                border-radius: 12px;
                font-size: 0.85rem;
            }
            
            .blog-table th,
            .blog-table td {
                padding: 0.85rem;
            }
            
            .blog-image {
                width: 50px;
                height: 50px;
            }
            
            .publish-badge {
                padding: 0.3rem 0.7rem;
                font-size: 0.7rem;
            }
        }
        
        @media (max-width: 480px) {
            .blog-management {
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
            
            .blog-filters {
                padding: 1rem;
                gap: 0.75rem;
            }
            
            .blog-table {
                border-radius: 8px;
                font-size: 0.75rem;
            }
            
            .blog-table th,
            .blog-table td {
                padding: 0.6rem;
            }
            
            .blog-image {
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
                                <img src="<?php echo getUploadUrl($post['featured_image']); ?>" alt="" class="blog-image">
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
                            <a href="edit.php?id=<?php echo $post['id']; ?>" class="action-btn edit-btn no-loading" title="Edit">
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
                        <td colspan="8" class="empty-state">
                            <i class="fas fa-pen-fancy"></i>
                            <p style="font-size: 1.15rem; font-weight: 600; margin: 1rem 0 0.5rem 0;">No blog posts found</p>
                            <p style="font-size: 0.9rem; color: #94a3b8; margin-bottom: 1.5rem;">Start creating engaging content for your audience</p>
                            <a href="create.php" class="view-all" style="display: inline-flex;">
                                <i class="fas fa-pen-nib"></i> Create Your First Post
                            </a>
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>