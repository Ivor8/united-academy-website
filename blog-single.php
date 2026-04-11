<?php
require_once 'admin/includes/config.php';

// Get database connection
$pdo = getDB();

// Get slug or id from URL
$slug = isset($_GET['slug']) ? sanitize($_GET['slug']) : '';
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if (empty($slug) && empty($id)) {
    header('Location: blog.php');
    exit();
}

// Build query based on parameter
$query = "
    SELECT bp.*, u.first_name, u.last_name, u.username as author_username,
           GROUP_CONCAT(t.name ORDER BY t.name) as tags
    FROM blog_posts bp 
    LEFT JOIN users u ON bp.author_id = u.id 
    LEFT JOIN blog_post_tags bpt ON bp.id = bpt.blog_post_id
    LEFT JOIN tags t ON bpt.tag_id = t.id
    WHERE bp.status = 'published'";

$params = [];

if (!empty($slug)) {
    $query .= " AND bp.slug = ?";
    $params[] = $slug;
} elseif (!empty($id)) {
    $query .= " AND bp.id = ?";
    $params[] = $id;
}

$query .= " GROUP BY bp.id";

// Get blog post
$stmt = $pdo->prepare($query);
$stmt->execute($params);
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
    <meta property="og:image" content="<?php echo empty($post['featured_image']) ? SITE_URL . 'assets/images/logo.jpg' : getUploadUrl($post['featured_image']); ?>">
    <meta property="og:url" content="<?php echo SITE_URL; ?>blog-single.php?slug=<?php echo $post['slug']; ?>">
    <meta property="og:type" content="article">
    <meta property="og:site_name" content="<?php echo SITE_NAME; ?>">
    
    <!-- Twitter Card -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="<?php echo htmlspecialchars($post['title']); ?>">
    <meta name="twitter:description" content="<?php echo htmlspecialchars(strip_tags($post['excerpt'])); ?>">
    <meta name="twitter:image" content="<?php echo empty($post['featured_image']) ? SITE_URL . 'assets/images/logo.jpg' : getUploadUrl($post['featured_image']); ?>">
    
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
        body {
            background: #f7f9fc;
            color: #102a43;
            font-family: 'Inter', sans-serif;
            margin: 0;
            padding: 0;
        }

        .main-content {
            max-width: 1100px;
            margin: 0 auto;
            padding: 2rem 1.5rem 3rem;
        }

        .blog-hero {
            position: relative;
            overflow: hidden;
            border-radius: 26px;
            min-height: 420px;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 4rem 2rem 3rem;
            background: linear-gradient(135deg, rgba(14, 64, 139, 0.85), rgba(26, 115, 232, 0.72)),
                        url('<?php echo empty($post['featured_image']) ? 'assets/images/hero1.jpg' : getUploadUrl($post['featured_image']); ?>');
            background-size: cover;
            background-position: center center;
            box-shadow: 0 24px 60px rgba(15, 23, 42, 0.12);
        }

        .blog-hero::before {
            content: '';
            position: absolute;
            inset: 0;
            background: rgba(10, 25, 47, 0.35);
        }

        .blog-hero-content {
            position: relative;
            z-index: 1;
            width: 100%;
            max-width: 800px;
            text-align: center;
            color: #ffffff;
        }

        .blog-category {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: rgba(255, 255, 255, 0.15);
            color: #f8fafc;
            padding: 0.75rem 1.2rem;
            border-radius: 999px;
            font-size: 0.82rem;
            font-weight: 700;
            letter-spacing: 1px;
            text-transform: uppercase;
            margin-bottom: 1.25rem;
            border: 1px solid rgba(255, 255, 255, 0.18);
        }

        .blog-title {
            font-size: clamp(2.2rem, 3vw, 3.6rem);
            font-weight: 800;
            line-height: 1.05;
            margin: 0 0 1.2rem;
            letter-spacing: -0.02em;
        }

        .blog-meta {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 1rem 1.75rem;
            font-size: 0.95rem;
            color: rgba(241, 245, 249, 0.95);
            opacity: 0.95;
        }

        .blog-meta span {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }

        .blog-meta i {
            color: rgba(241, 245, 249, 0.9);
        }

        .blog-content {
            max-width: 920px;
            margin: -4rem auto 2.5rem;
            background: #ffffff;
            padding: 3rem;
            border-radius: 28px;
            box-shadow: 0 24px 60px rgba(15, 23, 42, 0.08);
            line-height: 1.85;
            color: #334155;
            border: 1px solid rgba(148, 163, 184, 0.18);
        }

        .blog-content h1,
        .blog-content h2,
        .blog-content h3 {
            margin-top: 2.5rem;
            margin-bottom: 1rem;
            color: #102a43;
            font-weight: 700;
        }

        .blog-content h1 {
            font-size: 2.25rem;
        }

        .blog-content h2 {
            font-size: 1.8rem;
        }

        .blog-content h3 {
            font-size: 1.4rem;
        }

        .blog-content p,
        .blog-content ul,
        .blog-content ol,
        .blog-content blockquote {
            color: #475569;
            font-size: 1rem;
            margin-bottom: 1.5rem;
        }

        .blog-content ul,
        .blog-content ol {
            padding-left: 1.35rem;
        }

        .blog-content li {
            margin-bottom: 0.8rem;
        }

        .blog-content img {
            width: 100%;
            max-height: 620px;
            height: auto;
            border-radius: 20px;
            margin: 2.2rem 0;
            box-shadow: 0 18px 50px rgba(15, 23, 42, 0.08);
            border: 1px solid rgba(148, 163, 184, 0.18);
            object-fit: cover;
        }

        .blog-excerpt {
            margin-bottom: 2rem;
            padding: 1.75rem 1.8rem;
            background: #eef2ff;
            border-left: 4px solid #2563eb;
            color: #102a43;
            border-radius: 18px;
            font-size: 1rem;
            line-height: 1.8;
        }

        .blog-content blockquote {
            padding: 1.8rem 1.6rem;
            margin: 2rem 0;
            background: #eff6ff;
            border-left: 4px solid #2563eb;
            border-radius: 18px;
        }

        .blog-tags {
            display: flex;
            flex-wrap: wrap;
            gap: 0.75rem;
            margin: 2rem 0 0;
        }

        .tag {
            background: #eef2ff;
            color: #1e3a8a;
            padding: 0.7rem 1rem;
            border-radius: 999px;
            font-size: 0.92rem;
            font-weight: 600;
            text-decoration: none;
            transition: transform 0.25s ease, background 0.25s ease, color 0.25s ease;
        }

        .blog-download-panel {
            margin: 1.75rem 0 2rem;
        }

        .download-btn {
            display: inline-flex;
            align-items: center;
            gap: 0.65rem;
            padding: 0.95rem 1.4rem;
            background: linear-gradient(135deg, #E12D39, #D02A31);
            color: white;
            border-radius: 999px;
            font-weight: 700;
            text-decoration: none;
            box-shadow: 0 18px 40px rgba(225, 45, 57, 0.2);
            transition: transform 0.25s ease, box-shadow 0.25s ease;
        }

        .download-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 22px 50px rgba(225, 45, 57, 0.25);
        }

        .tag:hover {
            background: #1d4ed8;
            color: #ffffff;
            transform: translateY(-2px);
        }

        .blog-footer {
            max-width: 920px;
            margin: 0 auto 3rem;
            padding: 2.2rem 2.4rem;
            border-radius: 26px;
            background: #ffffff;
            border: 1px solid rgba(148, 163, 184, 0.14);
            box-shadow: 0 22px 50px rgba(15, 23, 42, 0.05);
        }

        .author-info {
            display: grid;
            grid-template-columns: auto 1fr;
            gap: 1rem;
            align-items: center;
            margin-bottom: 2rem;
        }

        .author-avatar {
            width: 72px;
            height: 72px;
            border-radius: 50%;
            background: #f8fafc;
            display: grid;
            place-items: center;
            color: #1e3a8a;
            font-size: 1.75rem;
            overflow: hidden;
        }

        .author-avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .author-details h4 {
            margin: 0 0 0.35rem;
            font-size: 1.05rem;
            font-weight: 700;
            color: #0f172a;
        }

        .author-details p {
            margin: 0;
            color: #64748b;
            font-size: 0.95rem;
        }

        .blog-stats {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 1rem;
            text-align: center;
        }

        .stat-item {
            padding: 1rem 1rem;
            border-radius: 18px;
            background: #f8fafc;
            border: 1px solid rgba(148, 163, 184, 0.12);
        }

        .stat-number {
            font-size: 1.35rem;
            color: #1d4ed8;
            font-weight: 700;
        }

        .stat-label {
            color: #64748b;
            font-size: 0.82rem;
            letter-spacing: 0.08em;
            text-transform: uppercase;
        }

        .related-posts {
            max-width: 920px;
            margin: 0 auto;
        }

        .related-posts h3 {
            font-size: 1.6rem;
            margin-bottom: 1.75rem;
            text-align: center;
            color: #102a43;
        }

        .related-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 1.5rem;
        }

        .related-card {
            display: flex;
            flex-direction: column;
            height: 100%;
            background: #ffffff;
            border-radius: 22px;
            overflow: hidden;
            box-shadow: 0 18px 40px rgba(15, 23, 42, 0.07);
            transition: transform 0.25s ease, box-shadow 0.25s ease;
        }

        .related-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 24px 60px rgba(15, 23, 42, 0.12);
        }

        .related-image {
            width: 100%;
            aspect-ratio: 16 / 10;
            object-fit: cover;
            background: #cbd5e1;
        }

        .related-content {
            padding: 1.4rem 1.5rem 1.6rem;
            display: flex;
            flex-direction: column;
            gap: 0.9rem;
            flex: 1;
        }

        .related-title {
            margin: 0;
            font-size: 1.05rem;
            font-weight: 700;
            color: #102a43;
            line-height: 1.4;
        }

        .related-meta {
            font-size: 0.9rem;
            color: #64748b;
            display: flex;
            flex-wrap: wrap;
            gap: 0.75rem;
            align-items: center;
        }

        .related-meta i {
            color: #2563eb;
        }

        @media (max-width: 900px) {
            .main-content {
                padding: 1.5rem 1.25rem 2.5rem;
            }

            .blog-content {
                margin-top: -3.25rem;
                padding: 2.4rem 2rem;
            }

            .blog-hero {
                min-height: 360px;
                padding: 3rem 1.5rem 2.5rem;
            }
        }

        @media (max-width: 680px) {
            .blog-hero {
                padding: 2.5rem 1rem 2rem;
            }

            .blog-meta {
                flex-direction: column;
                align-items: center;
                gap: 0.75rem;
            }
            .main-header .logo-area .logo-text-wrapper {
                display: none;
            }
            .blog-content,
            .blog-footer,
            .related-posts {
                padding-left: 1.25rem;
                padding-right: 1.25rem;
            }

            .blog-footer {
                padding: 1.75rem 1.25rem;
            }

            .blog-title {
                font-size: 2rem;
            }

            .blog-stats {
                grid-template-columns: 1fr;
            }

            .author-info {
                grid-template-columns: 1fr;
                justify-items: center;
                text-align: center;
            }

            .author-details {
                align-items: center;
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
                    <span><i class="fas fa-calendar"></i> <?php echo !empty($post['published_at']) ? date('F j, Y', strtotime($post['published_at'])) : 'Date not set'; ?></span>
                    <span><i class="fas fa-eye"></i> <?php echo number_format($post['views']); ?> views</span>
                </div>
            </div>
        </section>
        
        <!-- Blog Content -->
        <section class="blog-content">
            <?php if (!empty($post['excerpt'])): ?>
                <div class="blog-excerpt"><?php echo nl2br(htmlspecialchars($post['excerpt'])); ?></div>
            <?php endif; ?>

            <?php if (!empty($post['content'])): ?>
                <div class="blog-body"><?php echo $post['content']; ?></div>
            <?php else: ?>
                <div class="blog-body">
                    <p>This article does not contain a body yet. Please add the blog content in the admin panel.</p>
                </div>
            <?php endif; ?>

            <?php if ($post['media_type'] === 'pdf' && !empty($post['media_url'])): ?>
                <div class="blog-download-panel">
                    <a href="<?php echo htmlspecialchars(getUploadUrl($post['media_url'])); ?>" class="download-btn" target="_blank" rel="noopener noreferrer">
                        <i class="fas fa-file-pdf"></i>
                        Download attached PDF
                    </a>
                </div>
            <?php endif; ?>
            
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
                        <img src="<?php echo getUploadUrl($post['featured_image']); ?>" alt="<?php echo htmlspecialchars($post['first_name']); ?>">
                    <?php else: ?>
                        <i class="fas fa-user"></i>
                    <?php endif; ?>
                </div>
                <div class="author-details">
                    <h4><?php echo htmlspecialchars($post['first_name'] . ' ' . $post['last_name']); ?></h4>
                    <p><?php echo !empty($post['published_at']) ? date('F j, Y', strtotime($post['published_at'])) : 'Date not set'; ?></p>
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
                                <img src="<?php echo getUploadUrl($related['featured_image']); ?>" alt="<?php echo htmlspecialchars($related['title']); ?>" class="related-image">
                            <?php else: ?>
                                <div class="related-image" style="background: linear-gradient(135deg, #667eea, #764ba2); display: flex; align-items: center; justify-content: center; color: white; font-size: 2rem;">
                                    <i class="fas fa-blog"></i>
                                </div>
                            <?php endif; ?>
                            <div class="related-content">
                                <h4 class="related-title"><?php echo htmlspecialchars($related['title']); ?></h4>
                                <div class="related-meta">
                                    <span><i class="fas fa-calendar"></i> <?php echo !empty($related['published_at']) ? date('M j, Y', strtotime($related['published_at'])) : 'Date not set'; ?></span>
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
