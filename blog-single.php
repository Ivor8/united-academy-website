<?php
require_once 'admin/includes/config.php';

// Get database connection
$pdo = getDB();

// Get slug from URL
$slug = isset($_GET['slug']) ? sanitize($_GET['slug']) : '';

if (empty($slug)) {
    header('Location: blog.php');
    exit();
}

// Get blog post
$stmt = $pdo->prepare("
    SELECT bp.*, u.first_name, u.last_name, u.username as author_username,
           GROUP_CONCAT(t.name ORDER BY t.name) as tags
    FROM blog_posts bp 
    LEFT JOIN users u ON bp.author_id = u.id 
    LEFT JOIN blog_post_tags bpt ON bp.id = bpt.blog_post_id
    LEFT JOIN tags t ON bpt.tag_id = t.id
    WHERE bp.slug = ? AND bp.status = 'published'
    GROUP BY bp.id
");
$stmt->execute([$slug]);
$post = $stmt->fetch();

if (!$post) {
    // Post not found or not published
    header('HTTP/1.0 404 Not Found');
    include '404.html'; // You might want to create a 404 page
    exit();
}

// Increment view count
$viewStmt = $pdo->prepare("UPDATE blog_posts SET views = views + 1 WHERE id = ?");
$viewStmt->execute([$post['id']]);

// Get related posts
$relatedStmt = $pdo->prepare("
    SELECT bp.*, u.first_name, u.last_name 
    FROM blog_posts bp 
    LEFT JOIN users u ON bp.author_id = u.id 
    WHERE bp.category = ? AND bp.status = 'published' AND bp.id != ?
    ORDER BY bp.published_at DESC 
    LIMIT 3
");
$relatedStmt->execute([$post['category'], $post['id']]);
$relatedPosts = $relatedStmt->fetchAll();

// Get tags array
$tags = !empty($post['tags']) ? explode(',', $post['tags']) : [];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($post['title']); ?> | <?php echo SITE_NAME; ?></title>
    <meta name="description" content="<?php echo htmlspecialchars(strip_tags($post['excerpt'])); ?>">
    <meta name="keywords" content="<?php echo htmlspecialchars(implode(', ', $tags)); ?>">
    <meta name="author" content="<?php echo htmlspecialchars($post['first_name'] . ' ' . $post['last_name']); ?>">
    
    <!-- Open Graph -->
    <meta property="og:title" content="<?php echo htmlspecialchars($post['title']); ?>">
    <meta property="og:description" content="<?php echo htmlspecialchars(strip_tags($post['excerpt'])); ?>">
    <meta property="og:image" content="<?php echo SITE_URL . (empty($post['featured_image']) ? 'assets/images/logo.jpg' : UPLOAD_URL . $post['featured_image']); ?>">
    <meta property="og:url" content="<?php echo SITE_URL; ?>blog-single.php?slug=<?php echo $post['slug']; ?>">
    <meta property="og:type" content="article">
    <meta property="og:site_name" content="<?php echo SITE_NAME; ?>">
    
    <!-- Twitter Card -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="<?php echo htmlspecialchars($post['title']); ?>">
    <meta name="twitter:description" content="<?php echo htmlspecialchars(strip_tags($post['excerpt'])); ?>">
    <meta name="twitter:image" content="<?php echo SITE_URL . (empty($post['featured_image']) ? 'assets/images/logo.jpg' : UPLOAD_URL . $post['featured_image']); ?>">
    
    <!-- Canonical URL -->
    <link rel="canonical" href="<?php echo SITE_URL; ?>blog-single.php?slug=<?php echo $post['slug']; ?>">
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Playfair+Display:wght@400;700;900&display=swap" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    
    <!-- Main CSS -->
    <link rel="stylesheet" href="assets/css/style.css">
    
    <style>
        .blog-hero {
            background: linear-gradient(135deg, rgba(30, 100, 200, 0.9), rgba(30, 100, 200, 0.7)), 
                        url('<?php echo empty($post['featured_image']) ? 'assets/images/hero1.jpg' : UPLOAD_URL . $post['featured_image']; ?>');
            background-size: cover;
            background-position: center;
            background-blend-mode: overlay;
            color: white;
            text-align: center;
            padding: 8rem 2rem;
            margin-bottom: 3rem;
            border-radius: 16px;
            position: relative;
            overflow: hidden;
        }
        
        .blog-hero::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.4);
            z-index: 1;
        }
        
        .blog-hero-content {
            position: relative;
            z-index: 2;
            max-width: 800px;
            margin: 0 auto;
        }
        
        .blog-category {
            display: inline-block;
            background: rgba(255, 255, 255, 0.2);
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 1rem;
        }
        
        .blog-title {
            font-size: 3rem;
            font-weight: 900;
            margin-bottom: 1rem;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.3);
            line-height: 1.1;
        }
        
        .blog-meta {
            display: flex;
            align-items: center;
            gap: 2rem;
            font-size: 0.9rem;
            opacity: 0.9;
        }
        
        .blog-meta i {
            margin-right: 0.5rem;
        }
        
        .blog-content {
            max-width: 800px;
            margin: 0 auto 3rem;
            background: white;
            padding: 3rem;
            border-radius: 16px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            line-height: 1.8;
        }
        
        .blog-content h1,
        .blog-content h2,
        .blog-content h3 {
            margin-top: 2rem;
            margin-bottom: 1rem;
            color: #1E293B;
        }
        
        .blog-content h1 {
            font-size: 2.5rem;
            font-weight: 700;
        }
        
        .blog-content h2 {
            font-size: 2rem;
            font-weight: 600;
        }
        
        .blog-content h3 {
            font-size: 1.5rem;
            font-weight: 600;
        }
        
        .blog-content p {
            margin-bottom: 1.5rem;
            color: #4B5563;
        }
        
        .blog-content img {
            max-width: 100%;
            height: auto;
            border-radius: 12px;
            margin: 2rem 0;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }
        
        .blog-tags {
            display: flex;
            flex-wrap: wrap;
            gap: 0.5rem;
            margin: 2rem 0;
        }
        
        .tag {
            background: #F1F5F9;
            color: #1E64C8;
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 500;
            text-decoration: none;
            transition: all 0.3s ease;
        }
        
        .tag:hover {
            background: #1E64C8;
            color: white;
            transform: translateY(-2px);
        }
        
        .blog-footer {
            max-width: 800px;
            margin: 0 auto;
            padding: 2rem;
            background: #F8FAFC;
            border-radius: 16px;
            text-align: center;
        }
        
        .author-info {
            display: flex;
            align-items: center;
            gap: 1rem;
            margin-bottom: 2rem;
        }
        
        .author-avatar {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            background: #E2E8F0;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #64748B;
            font-size: 1.5rem;
        }
        
        .author-details h4 {
            font-size: 1.1rem;
            font-weight: 600;
            color: #1E293B;
            margin-bottom: 0.25rem;
        }
        
        .author-details p {
            color: #64748B;
            font-size: 0.9rem;
        }
        
        .blog-stats {
            display: flex;
            justify-content: center;
            gap: 3rem;
            margin-bottom: 2rem;
            flex-wrap: wrap;
        }
        
        .stat-item {
            text-align: center;
        }
        
        .stat-number {
            font-size: 1.5rem;
            font-weight: 700;
            color: #1E64C8;
            display: block;
        }
        
        .stat-label {
            color: #64748B;
            font-size: 0.85rem;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        
        .related-posts {
            margin-top: 3rem;
        }
        
        .related-posts h3 {
            font-size: 1.5rem;
            font-weight: 700;
            color: #1E293B;
            margin-bottom: 2rem;
            text-align: center;
        }
        
        .related-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 2rem;
            max-width: 1200px;
            margin: 0 auto;
        }
        
        .related-card {
            background: white;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            text-decoration: none;
            color: inherit;
        }
        
        .related-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
        }
        
        .related-image {
            width: 100%;
            height: 200px;
            object-fit: cover;
            background: #E2E8F0;
        }
        
        .related-content {
            padding: 1.5rem;
        }
        
        .related-title {
            font-size: 1.1rem;
            font-weight: 600;
            color: #1E293B;
            margin-bottom: 0.5rem;
            line-height: 1.4;
        }
        
        .related-meta {
            font-size: 0.85rem;
            color: #64748B;
            display: flex;
            align-items: center;
            gap: 1rem;
        }
        
        @media (max-width: 768px) {
            .blog-hero {
                padding: 4rem 1rem;
            }
            
            .blog-title {
                font-size: 2rem;
            }
            
            .blog-content {
                padding: 2rem 1.5rem;
                margin: 0 auto 2rem;
            }
            
            .blog-meta {
                flex-direction: column;
                gap: 0.5rem;
                align-items: flex-start;
            }
            
            .related-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <?php include 'includes/header.html'; ?>
    
    <main class="main-content">
        <!-- Blog Hero Section -->
        <section class="blog-hero">
            <div class="blog-hero-content">
                <div class="blog-category"><?php echo ucfirst($post['category']); ?></div>
                <h1 class="blog-title"><?php echo htmlspecialchars($post['title']); ?></h1>
                <div class="blog-meta">
                    <span><i class="fas fa-user"></i> <?php echo htmlspecialchars($post['first_name'] . ' ' . $post['last_name']); ?></span>
                    <span><i class="fas fa-calendar"></i> <?php echo date('F j, Y', strtotime($post['published_at'])); ?></span>
                    <span><i class="fas fa-eye"></i> <?php echo number_format($post['views']); ?> views</span>
                </div>
            </div>
        </section>
        
        <!-- Blog Content -->
        <section class="blog-content">
            <?php echo $post['content']; ?>
            
            <!-- Tags -->
            <?php if (!empty($tags)): ?>
                <div class="blog-tags">
                    <?php foreach ($tags as $tag): ?>
                        <a href="blog.php?tag=<?php echo urlencode(trim($tag)); ?>" class="tag">
                            <?php echo htmlspecialchars(trim($tag)); ?>
                        </a>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </section>
        
        <!-- Blog Footer -->
        <section class="blog-footer">
            <div class="author-info">
                <div class="author-avatar">
                    <?php if (!empty($post['featured_image'])): ?>
                        <img src="<?php echo UPLOAD_URL . $post['featured_image']; ?>" alt="<?php echo htmlspecialchars($post['first_name']); ?>">
                    <?php else: ?>
                        <i class="fas fa-user"></i>
                    <?php endif; ?>
                </div>
                <div class="author-details">
                    <h4><?php echo htmlspecialchars($post['first_name'] . ' ' . $post['last_name']); ?></h4>
                    <p><?php echo date('F j, Y', strtotime($post['published_at'])); ?></p>
                </div>
            </div>
            
            <div class="blog-stats">
                <div class="stat-item">
                    <span class="stat-number"><?php echo number_format($post['views']); ?></span>
                    <span class="stat-label">Views</span>
                </div>
                <div class="stat-item">
                    <span class="stat-number"><?php echo count($tags); ?></span>
                    <span class="stat-label">Tags</span>
                </div>
                <div class="stat-item">
                    <span class="stat-number"><?php echo ucfirst($post['category']); ?></span>
                    <span class="stat-label">Category</span>
                </div>
            </div>
        </section>
        
        <!-- Related Posts -->
        <?php if (!empty($relatedPosts)): ?>
            <section class="related-posts">
                <h3>Related Posts</h3>
                <div class="related-grid">
                    <?php foreach ($relatedPosts as $related): ?>
                        <a href="blog-single.php?slug=<?php echo $related['slug']; ?>" class="related-card">
                            <?php if (!empty($related['featured_image'])): ?>
                                <img src="<?php echo UPLOAD_URL . $related['featured_image']; ?>" alt="<?php echo htmlspecialchars($related['title']); ?>" class="related-image">
                            <?php else: ?>
                                <div class="related-image" style="background: linear-gradient(135deg, #667eea, #764ba2); display: flex; align-items: center; justify-content: center; color: white; font-size: 2rem;">
                                    <i class="fas fa-blog"></i>
                                </div>
                            <?php endif; ?>
                            <div class="related-content">
                                <h4 class="related-title"><?php echo htmlspecialchars($related['title']); ?></h4>
                                <div class="related-meta">
                                    <span><i class="fas fa-calendar"></i> <?php echo date('M j, Y', strtotime($related['published_at'])); ?></span>
                                    <span><i class="fas fa-user"></i> <?php echo htmlspecialchars($related['first_name']); ?></span>
                                </div>
                            </div>
                        </a>
                    <?php endforeach; ?>
                </div>
            </section>
        <?php endif; ?>
    </main>
    
    <?php include 'includes/footer.html'; ?>
    
    <script>
        // Smooth scroll for anchor links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            });
        });
        
        // Copy link functionality
        function copyLink() {
            const url = window.location.href;
            navigator.clipboard.writeText(url).then(() => {
                // Show success message
                const toast = document.createElement('div');
                toast.textContent = 'Link copied to clipboard!';
                toast.style.cssText = `
                    position: fixed;
                    bottom: 20px;
                    right: 20px;
                    background: #1E64C8;
                    color: white;
                    padding: 1rem 1.5rem;
                    border-radius: 8px;
                    z-index: 1000;
                    animation: slideInRight 0.3s ease;
                `;
                document.body.appendChild(toast);
                
                setTimeout(() => {
                    toast.remove();
                }, 3000);
            });
        }
    </script>
</body>
</html>
