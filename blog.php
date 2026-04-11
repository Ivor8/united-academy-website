<?php
require_once 'admin/includes/config.php';

// Get database connection
$pdo = getDB();

// Handle filters
$category = isset($_GET['category']) ? sanitize($_GET['category']) : 'all';
$search = isset($_GET['search']) ? sanitize($_GET['search']) : '';
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$limit = 6;
$offset = ($page - 1) * $limit;

// Build query
$query = "SELECT bp.*, u.first_name, u.last_name, GROUP_CONCAT(t.name) as tags
          FROM blog_posts bp 
          LEFT JOIN users u ON bp.author_id = u.id 
          LEFT JOIN blog_post_tags bpt ON bp.id = bpt.blog_post_id
          LEFT JOIN tags t ON bpt.tag_id = t.id
          WHERE bp.status = 'published'";

$params = [];

if ($category !== 'all') {
    $query .= " AND bp.category = ?";
    $params[] = $category;
}

if ($search) {
    $query .= " AND (bp.title LIKE ? OR bp.excerpt LIKE ? OR bp.content LIKE ?)";
    $searchPattern = "%$search%";
    $params[] = $searchPattern;
    $params[] = $searchPattern;
    $params[] = $searchPattern;
}

$query .= " GROUP BY bp.id ORDER BY bp.published_at DESC LIMIT $limit OFFSET $offset";

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$posts = $stmt->fetchAll();

// Get total count for pagination
$countQuery = "SELECT COUNT(DISTINCT bp.id) as total
               FROM blog_posts bp 
               WHERE bp.status = 'published'";
$countParams = [];

if ($category !== 'all') {
    $countQuery .= " AND bp.category = ?";
    $countParams[] = $category;
}

if ($search) {
    $countQuery .= " AND (bp.title LIKE ? OR bp.excerpt LIKE ? OR bp.content LIKE ?)";
    $countParams[] = "%$search%";
    $countParams[] = "%$search%";
    $countParams[] = "%$search%";
}

$countStmt = $pdo->prepare($countQuery);
$countStmt->execute($countParams);
$totalPosts = $countStmt->fetch()['total'];
$totalPages = ceil($totalPosts / $limit);

// Get featured post for hero section
$featuredStmt = $pdo->prepare("
    SELECT bp.*, u.first_name, u.last_name, GROUP_CONCAT(t.name) as tags
    FROM blog_posts bp 
    LEFT JOIN users u ON bp.author_id = u.id 
    LEFT JOIN blog_post_tags bpt ON bp.id = bpt.blog_post_id
    LEFT JOIN tags t ON bpt.tag_id = t.id
    WHERE bp.status = 'published' AND bp.featured = 1
    GROUP BY bp.id 
    ORDER BY bp.published_at DESC 
    LIMIT 1
");
$featuredStmt->execute();
$featuredPost = $featuredStmt->fetch();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=yes">
    <!-- META TAGS (SEO) -->
    <title>Blog & News | UNITED ACADEMY-UARD – Latest Updates, Articles & Publications</title>
    <meta name="description" content="Read the latest news, blog posts, and updates from UNITED ACADEMY-UARD. Student stories, industry insights, event announcements, and publications from our vocational training institute.">
    <meta name="keywords" content="UNITED ACADEMY-UARD blog, vocational training news Cameroon, education updates, student stories, health training news, IT courses updates, MINEFOP news">
    <meta name="author" content="UNITED ACADEMY-UARD">
    <meta name="robots" content="index, follow">
    <meta property="og:title" content="Blog & News – UNITED ACADEMY-UARD">
    <meta property="og:description" content="Stay updated with the latest from UNITED ACADEMY-UARD: blog articles, news, events, and publications.">
    <meta property="og:image" content="assets/images/logo.jpg">
    <meta property="og:url" content="https://www.unitedacademy-uard.cm/blog">
    <link rel="canonical" href="https://www.unitedacademy-uard.cm/blog">
    <!-- Font Awesome 6 -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:opsz,wght@14..32,300..700&family=Playfair+Display:wght@400;700;900&display=swap" rel="stylesheet">
    <!-- Main CSS -->
    <link rel="stylesheet" href="assets/css/style.css">
    <!-- Blog page CSS -->
    <link rel="stylesheet" href="assets/css/blog.css">
    <style>
        /* Enhanced blog card hover effects */
        .blog-card:hover {
            transform: translateY(-10px) scale(1.02);
            box-shadow: 0 20px 40px rgba(30,100,200,0.15);
        }
        
        .blog-card:hover .blog-image {
            transform: scale(1.08);
        }
        
        .blog-card:hover .video-overlay {
            opacity: 1;
        }
        
        .read-more-btn:hover {
            background: linear-gradient(135deg, #2E7D32, #1E64C8) !important;
            transform: translateX(5px);
        }
        
        .read-more-btn:hover i {
            transform: translateX(3px);
        }
        
        /* Enhanced video overlay animation */
        .video-overlay:hover {
            opacity: 1 !important;
        }
        
        .play-video-btn:hover {
            transform: scale(1.1);
        }
        
        /* Smooth animations */
        .blog-card {
            animation: cardSlideIn 0.6s ease-out forwards;
        }
        
        @keyframes cardSlideIn {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        /* Responsive enhancements */
        @media (max-width: 768px) {
            .blog-card:hover {
                transform: translateY(-5px) scale(1.01);
            }
            
            .read-more-btn {
                padding: 0.7rem 1.2rem !important;
                font-size: 0.9rem;
            }
        }
    </style>
</head>
<body>
    <div class="page-wrapper">

        <!-- ========== HEADER ========== -->
        <header class="main-header">
            <div class="container header-flex">
                <div class="logo-area">
                    <img src="assets/images/logo.jpg" alt="UNITED ACADEMY-UARD Logo" class="logo-img" width="50" height="50">
                </div>
                <nav class="main-nav">
                    <ul class="nav-links" id="navLinks">
                        <li><a href="index.php" class="nav-link" data-i18n="nav_home">Home</a></li>
                        <li><a href="about.html" class="nav-link" data-i18n="nav_about">About</a></li>
                        <li><a href="programs.html" class="nav-link" data-i18n="nav_programs">Programs</a></li>
                        <li><a href="testimonials.php" class="nav-link" data-i18n="nav_testimonials">Testimonials</a></li>
                        <li><a href="blog.php" class="nav-link active" data-i18n="nav_news">Updates</a></li>
                        <li><a href="contact.html" class="nav-link" data-i18n="nav_contact">Contact</a></li>
                    </ul>
                    <div class="lang-switcher">
                        <button id="lang-en" class="lang-btn">EN</button>
                        <span class="lang-divider">|</span>
                        <button id="lang-fr" class="lang-btn active-lang">FR</button>
                    </div>
                    <button class="mobile-toggle" id="mobileToggle" aria-label="Toggle menu"><i class="fas fa-bars"></i></button>
                </nav>
            </div>
        </header>

        <!-- ========== PAGE HERO ========== -->
        <section class="page-hero blog-hero">
            <div class="page-hero-overlay"></div>
            <div class="container page-hero-content">
                <h1 data-i18n="blog_page_title">Blog & <span class="accent">News</span></h1>
                <div class="divider"></div>
                <p data-i18n="blog_page_sub">Stories, updates, and insights from UNITED ACADEMY-UARD</p>
            </div>
        </section>

        <!-- ========== SEARCH & FILTER SECTION ========== -->
        <section class="section blog-filter-section">
            <div class="container">
                <form method="GET" class="blog-filter-wrapper fade-up">
                    <div class="blog-search">
                        <i class="fas fa-search search-icon"></i>
                        <input type="text" name="search" id="blogSearchInput" placeholder="Search articles, news, events..." value="<?php echo htmlspecialchars($search); ?>" data-i18n-placeholder="blog_search_placeholder">
                    </div>
                    
                    <div class="blog-categories">
                        <button type="submit" name="category" value="all" class="category-btn <?php echo $category === 'all' ? 'active' : ''; ?>" data-i18n="all_items">All</button>
                        <button type="submit" name="category" value="blog" class="category-btn <?php echo $category === 'blog' ? 'active' : ''; ?>" data-i18n="blog_posts">Blog Posts</button>
                        <button type="submit" name="category" value="news" class="category-btn <?php echo $category === 'news' ? 'active' : ''; ?>" data-i18n="news_articles">News</button>
                        <button type="submit" name="category" value="events" class="category-btn <?php echo $category === 'events' ? 'active' : ''; ?>" data-i18n="events">Events</button>
                        <button type="submit" name="category" value="publications" class="category-btn <?php echo $category === 'publications' ? 'active' : ''; ?>" data-i18n="publications">Publications</button>
                        <button type="submit" name="category" value="video" class="category-btn <?php echo $category === 'video' ? 'active' : ''; ?>" data-i18n="videos">Videos</button>
                    </div>
                </form>
            </div>
        </section>

        <!-- ========== FEATURED POST ========== -->
        <?php if ($featuredPost): ?>
        <section class="section featured-section">
            <div class="container">
                <div class="featured-post fade-up" id="featuredPost">
                    <div class="featured-content">
                        <div class="featured-image">
                            <?php if ($featuredPost['media_type'] === 'video' && $featuredPost['media_url']): ?>
                                <video poster="<?php echo htmlspecialchars(getUploadUrl($featuredPost['video_poster'] ?: $featuredPost['featured_image'])); ?>" preload="metadata" class="featured-video">
                                    <source src="<?php echo htmlspecialchars(getUploadUrl($featuredPost['media_url'])); ?>" type="video/mp4">
                                    Your browser does not support the video tag.
                                </video>
                                <div class="video-overlay">
                                    <button class="play-featured-btn" onclick="window.open('blog-single.php?id=<?php echo $featuredPost['id']; ?>', '_blank')">
                                        <i class="fas fa-play-circle"></i>
                                    </button>
                                </div>
                            <?php else: ?>
                                <img src="<?php echo htmlspecialchars(!empty($featuredPost['featured_image']) ? getUploadUrl($featuredPost['featured_image']) : 'assets/images/default-blog.jpg'); ?>" alt="<?php echo htmlspecialchars($featuredPost['title']); ?>" class="featured-image-img">
                            <?php endif; ?>
                            <?php if ($featuredPost['media_type'] === 'pdf' && $featuredPost['media_url']): ?>
                                <div class="pdf-badge" style="position:absolute; top: 15px; left: 15px; background: #E12D39; color: #fff; padding: 0.45rem 0.9rem; border-radius: 999px; font-size: 0.75rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.06em;">PDF</div>
                            <?php endif; ?>
                        </div>
                        <div class="featured-text">
                            <div class="post-category"><?php echo ucfirst($featuredPost['category']); ?></div>
                            <h2><?php echo htmlspecialchars($featuredPost['title']); ?></h2>
                            <p><?php echo htmlspecialchars(truncate($featuredPost['excerpt'] ?: strip_tags($featuredPost['content']), 200)); ?></p>
                            <div class="post-meta">
                                <span class="post-author">
                                    <i class="fas fa-user"></i> 
                                    <?php echo htmlspecialchars($featuredPost['first_name'] . ' ' . $featuredPost['last_name']); ?>
                                </span>
                                <span class="post-date">
                                    <i class="fas fa-calendar"></i> 
                                    <?php echo formatDate($featuredPost['published_at']); ?>
                                </span>
                                <span class="post-views">
                                    <i class="fas fa-eye"></i> 
                                    <?php echo number_format($featuredPost['views']); ?>
                                </span>
                            </div>
                            <a href="blog-single.php?id=<?php echo $featuredPost['id']; ?>" class="btn btn-primary">Read More</a>
                        </div>
                    </div>
                </div>
            </div>
        </section>
        <?php endif; ?>

        <!-- ========== BLOG GRID ========== -->
        <section class="section blog-grid-section">
            <div class="container">
                <div class="section-header fade-up">
                    <h2 data-i18n="latest_updates">Latest updates</h2>
                    <div class="divider"></div>
                </div>

                <div class="blog-posts-grid" id="blogPostsGrid">
                    <?php if (count($posts) > 0): ?>
                        <?php foreach ($posts as $post): ?>
                            <article class="blog-card fade-up" style="background: linear-gradient(135deg, #ffffff 0%, #f8fafc 100%); border-radius: 20px; overflow: hidden; box-shadow: 0 10px 30px rgba(0,0,0,0.1); transition: all 0.3s ease; border: 1px solid rgba(30,100,200,0.1);">
                                <div class="blog-card-image" style="position: relative; height: 220px; overflow: hidden;">
                                    <?php if ($post['media_type'] === 'video' && $post['media_url']): ?>
                                        <video poster="<?php echo htmlspecialchars(getUploadUrl($post['video_poster'] ?: $post['featured_image'])); ?>" preload="metadata" class="blog-video" style="width: 100%; height: 100%; object-fit: cover; transition: transform 0.5s;">
                                            <source src="<?php echo htmlspecialchars(getUploadUrl($post['media_url'])); ?>" type="video/mp4">
                                            Your browser does not support the video tag.
                                        </video>
                                        <div class="video-overlay" style="position: absolute; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.3); display: flex; align-items: center; justify-content: center; opacity: 0; transition: opacity 0.3s;">
                                            <button class="play-video-btn" onclick="window.open('blog-single.php?id=<?php echo $post['id']; ?>', '_blank')" style="background: rgba(255,255,255,0.9); border: none; border-radius: 50%; width: 60px; height: 60px; display: flex; align-items: center; justify-content: center; cursor: pointer; transition: all 0.3s;">
                                                <i class="fas fa-play-circle" style="font-size: 24px; color: var(--blue);"></i>
                                            </button>
                                        </div>
                                    <?php else: ?>
                                        <img src="<?php echo htmlspecialchars(!empty($post['featured_image']) ? getUploadUrl($post['featured_image']) : 'assets/images/default-blog.jpg'); ?>" alt="<?php echo htmlspecialchars($post['title']); ?>" class="blog-image" style="width: 100%; height: 100%; object-fit: cover; transition: transform 0.5s;">
                                    <?php endif; ?>
                                    <?php if ($post['media_type'] === 'pdf' && $post['media_url']): ?>
                                        <div class="pdf-badge" style="position:absolute; top: 15px; left: 15px; background: #E12D39; color: #fff; padding: 0.45rem 0.9rem; border-radius: 999px; font-size: 0.75rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.06em;">PDF</div>
                                    <?php endif; ?>
                                    <div class="post-category" style="position: absolute; top: 15px; right: 15px; background: linear-gradient(135deg, #1E64C8, #2E7D32); color: white; padding: 0.5rem 1.2rem; border-radius: 20px; font-size: 0.8rem; font-weight: 700;"><?php echo ucfirst($post['category']); ?></div>
                                </div>
                                <div class="blog-card-content" style="padding: 1.8rem;">
                                    <h3 style="font-size: 1.4rem; color: #1E64C8; margin-bottom: 1rem; line-height: 1.4;"><a href="blog-single.php?id=<?php echo $post['id']; ?>" style="text-decoration: none; color: inherit;"><?php echo htmlspecialchars($post['title']); ?></a></h3>
                                    <p style="color: #4b5563; margin-bottom: 1.5rem; line-height: 1.6;"><?php echo htmlspecialchars(truncate($post['excerpt'] ?: strip_tags($post['content']), 120)); ?></p>
                                    <div class="post-meta" style="display: flex; gap: 1rem; margin-bottom: 1rem; font-size: 0.85rem; color: #6b7280;">
                                        <span class="post-author">
                                            <i class="fas fa-user" style="color: #2E7D32; margin-right: 0.3rem;"></i> 
                                            <?php echo htmlspecialchars($post['first_name'] . ' ' . $post['last_name']); ?>
                                        </span>
                                        <span class="post-date">
                                            <i class="fas fa-calendar" style="color: #2E7D32; margin-right: 0.3rem;"></i> 
                                            <?php echo formatDate($post['published_at']); ?>
                                        </span>
                                        <span class="post-views">
                                            <i class="fas fa-eye" style="color: #2E7D32; margin-right: 0.3rem;"></i> 
                                            <?php echo number_format($post['views']); ?>
                                        </span>
                                    </div>
                                    <?php if ($post['tags']): ?>
                                        <div class="post-tags" style="margin-bottom: 1.5rem;">
                                            <?php $tags = explode(',', $post['tags']); ?>
                                            <?php foreach (array_slice($tags, 0, 3) as $tag): ?>
                                                <span class="tag" style="display: inline-block; background: #f3f4f6; color: #4b5563; padding: 0.3rem 0.8rem; border-radius: 15px; font-size: 0.8rem; margin-right: 0.5rem; margin-bottom: 0.5rem;"><?php echo htmlspecialchars(trim($tag)); ?></span>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php endif; ?>
                                    <div style="border-top: 1px solid #e5e7eb; padding-top: 1.2rem; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 0.75rem;">
                                        <?php if ($post['media_type'] === 'pdf' && $post['media_url']): ?>
                                            <a href="<?php echo htmlspecialchars(getUploadUrl($post['media_url'])); ?>" class="btn btn-whatsapp" style="background: #E12D39; border-color: #E12D39; margin-right: 0.75rem;" target="_blank" rel="noopener noreferrer"><i class="fas fa-file-download"></i> Download PDF</a>
                                        <?php endif; ?>
                                        <a href="blog-single.php?id=<?php echo $post['id']; ?>" class="read-more-btn" style="background: linear-gradient(135deg, #1E64C8, #2E7D32); color: white; padding: 0.8rem 1.5rem; border-radius: 25px; text-decoration: none; font-weight: 600; transition: all 0.3s; display: inline-flex; align-items: center; gap: 0.5rem;">Read More <i class="fas fa-arrow-right"></i></a>
                                    </div>
                                </div>
                            </article>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="no-results" id="blogNoResults">
                            <i class="fas fa-search"></i>
                            <h3 data-i18n="no_posts_found">No posts found</h3>
                            <p data-i18n="try_adjusting">Try adjusting your search or filter</p>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- ========== PAGINATION ========== -->
                <?php if ($totalPages > 1): ?>
                <div class="pagination-container fade-up">
                    <div class="pagination">
                        <?php if ($page > 1): ?>
                            <a href="?category=<?php echo urlencode($category); ?>&search=<?php echo urlencode($search); ?>&page=<?php echo $page - 1; ?>" class="pagination-btn prev">
                                <i class="fas fa-chevron-left"></i> Previous
                            </a>
                        <?php endif; ?>
                        
                        <?php
                        $start = max(1, $page - 2);
                        $end = min($totalPages, $page + 2);
                        
                        if ($start > 1) {
                            echo '<a href="?category=' . urlencode($category) . '&search=' . urlencode($search) . '&page=1" class="pagination-btn">1</a>';
                            if ($start > 2) echo '<span class="pagination-ellipsis">...</span>';
                        }
                        
                        for ($i = $start; $i <= $end; $i++) {
                            $active = $i == $page ? 'active' : '';
                            echo '<a href="?category=' . urlencode($category) . '&search=' . urlencode($search) . '&page=' . $i . '" class="pagination-btn ' . $active . '">' . $i . '</a>';
                        }
                        
                        if ($end < $totalPages) {
                            if ($end < $totalPages - 1) echo '<span class="pagination-ellipsis">...</span>';
                            echo '<a href="?category=' . urlencode($category) . '&search=' . urlencode($search) . '&page=' . $totalPages . '" class="pagination-btn">' . $totalPages . '</a>';
                        }
                        ?>
                        
                        <?php if ($page < $totalPages): ?>
                            <a href="?category=<?php echo urlencode($category); ?>&search=<?php echo urlencode($search); ?>&page=<?php echo $page + 1; ?>" class="pagination-btn next">
                                Next <i class="fas fa-chevron-right"></i>
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </section>

        <!-- ========== VIDEO MODAL FOR BLOG VIDEOS ========== -->
        <div id="blogVideoModal" class="video-modal">
            <div class="video-modal-content">
                <span class="close-modal">&times;</span>
                <div class="video-container">
                    <video id="blogModalVideo" width="100%" controls>
                        <source id="blogModalVideoSource" src="" type="video/mp4">
                        Your browser does not support video tag.
                    </video>
                </div>
                <div class="video-modal-info" id="blogVideoModalInfo">
                    <h3 id="blogModalVideoTitle"></h3>
                    <p id="blogModalVideoDesc"></p>
                </div>
            </div>
        </div>

        <!-- ========== NEWSLETTER SECTION ========== -->
        <section class="section newsletter-section bg-light">
            <div class="container">
                <div class="newsletter-wrapper fade-up">
                    <div class="newsletter-content">
                        <h3 data-i18n="subscribe_blog">Subscribe to our newsletter</h3>
                        <p data-i18n="subscribe_blog_desc">Get the latest news and updates delivered to your inbox</p>
                    </div>
                    <form class="newsletter-form-large">
                        <input type="email" placeholder="Your email address" data-i18n-placeholder="email_placeholder" required>
                        <button type="submit" class="btn btn-primary" data-i18n="subscribe">Subscribe</button>
                    </form>
                </div>
            </div>
        </section>

        <!-- ========== POPULAR TAGS ========== -->
        <section class="section tags-section">
            <div class="container">
                <div class="tags-wrapper fade-up">
                    <h4 data-i18n="popular_tags">Popular tags:</h4>
                    <div class="tags-cloud">
                        <a href="?tag=health" class="tag">#health</a>
                        <a href="?tag=it" class="tag">#IT</a>
                        <a href="?tag=student-success" class="tag">#student success</a>
                        <a href="?tag=events" class="tag">#events</a>
                        <a href="?tag=internship" class="tag">#internship</a>
                        <a href="?tag=announcements" class="tag">#announcements</a>
                        <a href="?tag=publications" class="tag">#publications</a>
                        <a href="?tag=video" class="tag">#video</a>
                    </div>
                </div>
            </div>
        </section>

        <!-- ========== FOOTER ========== -->
        <footer class="main-footer">
            <div class="container">
                <div class="footer-grid">
                    <div class="footer-col">
                        <img src="assets/images/logo.jpg" alt="UNITED ACADEMY-UARD" class="footer-logo" width="70">
                        <p data-i18n="footer_about">Vocational Training Institute UNITED ACADEMY-UARD – MINEFOP accredited. Excellence, employability, innovation.</p>
                    </div>
                    <div class="footer-col">
                        <h4 data-i18n="quick_links">Quick links</h4>
                        <ul>
                            <li><a href="index.php" data-i18n="nav_home">Home</a></li>
                            <li><a href="about.html" data-i18n="nav_about">About</a></li>
                            <li><a href="programs.html" data-i18n="nav_programs">Programs</a></li>
                            <li><a href="blog.php" data-i18n="nav_blog">Blog</a></li>
                            <li><a href="contact.html" data-i18n="nav_contact">Contact</a></li>
                        </ul>
                    </div>
                    <div class="footer-col">
                        <h4 data-i18n="footer_contact">Contact</h4>
                        <p><i class="fas fa-map-pin"></i> Yaoundé-Simbock, Montée Mechcam, Roseville Complex (Immeuble Bleu)</p>
                        <p><i class="fas fa-phone"></i> +237 683 05 93 55 / +237 658 72 62 37</p>
                        <p><i class="fas fa-envelope"></i> unitedacademyuard@gmail.com</p>
                    </div>
                    <div class="footer-col">
                        <h4 data-i18n="newsletter">Newsletter</h4>
                        <p data-i18n="newsletter_desc">Subscribe for updates and news.</p>
                        <form class="newsletter-form">
                            <input type="email" placeholder="Your email" required>
                            <button type="submit"><i class="fas fa-paper-plane"></i></button>
                        </form>
                        <div class="footer-social">
                            <a href="#"><i class="fab fa-facebook-f"></i></a>
                            <a href="#"><i class="fab fa-linkedin-in"></i></a>
                            <a href="#"><i class="fab fa-x-twitter"></i></a>
                        </div>
                    </div>
                </div>
                <div class="footer-bottom">
                    <p>&copy; 2025 UNITED ACADEMY-UARD. <span data-i18n="footer_rights">All rights reserved.</span> | <span data-i18n="footer_approval">MINEFOP Agreement N° 00300/MINEFOP/SG/DFOP/SDGSF/CSACD/CBAC</span></p>
                    <p class="developer">Developed by <a href="https://www.miraedge.tech" target="_blank" rel="noopener">Mira Edge Technologies</a></p>
                </div>
            </div>
        </footer>
    </div>

    <!-- Main JS -->
    <script src="assets/js/main.js"></script>
</body>
</html>
