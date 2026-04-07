<?php
$pageTitle = 'Create Blog Post';
require_once '../includes/header.php';

// Temporarily bypass permission check for debugging
// if (!hasPermission('create_blog')) {
//     header('Location: index.php');
//     exit();
// }

$pdo = getDB();
$error = '';
$success = '';

// Get categories for dropdown
$categories = ['blog', 'news', 'events', 'publications', 'video'];

// Get tags
$tags = $pdo->query("SELECT * FROM tags ORDER BY name")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = sanitize($_POST['title']);
    $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $title)));
    $excerpt = sanitize($_POST['excerpt']);
    $content = $_POST['content'];
    $category = sanitize($_POST['category']);
    $status = sanitize($_POST['status']);
    $featured = isset($_POST['featured']) ? 1 : 0;
    $mediaType = sanitize($_POST['media_type']);
    $mediaUrl = '';
    $videoPoster = '';
    
    // Handle featured image upload
    $featuredImage = '';
    if (isset($_FILES['featured_image']) && $_FILES['featured_image']['error'] === UPLOAD_ERR_OK) {
        $uploaded = uploadFile($_FILES['featured_image'], 'blog');
        if ($uploaded) {
            $featuredImage = $uploaded;
        }
    }
    
    // Handle media URL for video
    if ($mediaType === 'video' && !empty($_POST['media_url'])) {
        $mediaUrl = sanitize($_POST['media_url']);
        if (!empty($_POST['video_poster'])) {
            $videoPoster = sanitize($_POST['video_poster']);
        }
    } elseif ($mediaType === 'image' && isset($_FILES['media_image']) && $_FILES['media_image']['error'] === UPLOAD_ERR_OK) {
        $uploaded = uploadFile($_FILES['media_image'], 'blog');
        if ($uploaded) {
            $mediaUrl = $uploaded;
        }
    }
    
    // Check if slug exists
    $checkStmt = $pdo->prepare("SELECT id FROM blog_posts WHERE slug = ?");
    $checkStmt->execute([$slug]);
    if ($checkStmt->fetch()) {
        $slug = $slug . '-' . time();
    }
    
    // Insert post
    $stmt = $pdo->prepare("
        INSERT INTO blog_posts (title, slug, excerpt, content, featured_image, media_type, media_url, video_poster, category, status, featured, author_id) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");
    
    if ($stmt->execute([$title, $slug, $excerpt, $content, $featuredImage, $mediaType, $mediaUrl, $videoPoster, $category, $status, $featured, $_SESSION['user_id']])) {
        $postId = $pdo->lastInsertId();
        
        // Handle tags
        if (isset($_POST['tags']) && is_array($_POST['tags'])) {
            foreach ($_POST['tags'] as $tagId) {
                $tagStmt = $pdo->prepare("INSERT INTO blog_post_tags (blog_post_id, tag_id) VALUES (?, ?)");
                $tagStmt->execute([$postId, $tagId]);
            }
        }
        
        // Log activity
        $logStmt = $pdo->prepare("INSERT INTO activity_logs (user_id, action, table_name, record_id) VALUES (?, 'CREATE', 'blog_posts', ?)");
        $logStmt->execute([$_SESSION['user_id'], $postId]);
        
        $success = 'Blog post created successfully!';
        header('refresh:2;url=index.php');
    } else {
        $error = 'Failed to create blog post. Please try again.';
    }
}

$extraCss = '<link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">';
$extraJs = '<script src="https://cdn.quilljs.com/1.3.6/quill.js"></script>
<script>
    var quill = new Quill(\'#editor\', {
        theme: \'snow\',
        placeholder: \'Write your blog post content here...\',
        modules: {
            toolbar: [
                [{ \'header\': [1, 2, 3, 4, 5, 6, false] }],
                [\'bold\', \'italic\', \'underline\', \'strike\'],
                [\'blockquote\', \'code-block\'],
                [{ \'list\': \'ordered\'}, { \'list\': \'bullet\' }],
                [\'link\', \'image\', \'video\'],
                [\'clean\']
            ]
        }
    });
    
    document.querySelector(\'form\').addEventListener(\'submit\', function() {
        document.querySelector(\'[name="content"]\').value = quill.root.innerHTML;
    });
    
    function previewMedia() {
        const mediaType = document.querySelector(\'[name="media_type"]\').value;
        const mediaUrl = document.querySelector(\'[name="media_url"]\').value;
        const preview = document.getElementById(\'mediaPreview\');
        
        if (mediaType === \'video\' && mediaUrl) {
            preview.innerHTML = `<video controls src="${mediaUrl}" style="width:100%; border-radius:12px;"></video>`;
        } else if (mediaType === \'image\' && mediaUrl) {
            preview.innerHTML = `<img src="${mediaUrl}" alt="Preview">`;
        } else {
            preview.innerHTML = \'\';
        }
    }
</script>';
?>

<div class="form-container">
    <div class="card-header">
        <h1><i class="fas fa-plus"></i> Create New Blog Post</h1>
        <a href="index.php" class="view-all">Back to Posts</a>
    </div>
    
    <?php if ($error): ?>
        <div class="alert alert-error"><?php echo $error; ?></div>
    <?php endif; ?>
    
    <?php if ($success): ?>
        <div class="alert alert-success"><?php echo $success; ?></div>
    <?php endif; ?>
    
    <form method="POST" enctype="multipart/form-data" class="animate-slide-up">
        <div class="form-row">
            <div class="form-group">
                <label>Title *</label>
                <input type="text" name="title" required placeholder="Enter post title">
            </div>
            
            <div class="form-group">
                <label>Category *</label>
                <select name="category" required>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?php echo $cat; ?>"><?php echo ucfirst($cat); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
        
        <div class="form-row">
            <div class="form-group">
                <label>Status *</label>
                <select name="status" required>
                    <option value="draft">Draft</option>
                    <option value="published">Published</option>
                </select>
            </div>
            
            <div class="form-group">
                <label>
                    <input type="checkbox" name="featured" value="1">
                    Feature this post
                </label>
            </div>
        </div>
        
        <div class="form-group">
            <label>Excerpt / Short Description</label>
            <textarea name="excerpt" rows="3" placeholder="Brief summary of the post..."></textarea>
        </div>
        
        <div class="form-group">
            <label>Content *</label>
            <div id="editor" class="editor-container"></div>
            <input type="hidden" name="content">
        </div>
        
        <div class="form-row">
            <div class="form-group">
                <label>Featured Image</label>
                <input type="file" name="featured_image" accept="image/*">
            </div>
            
            <div class="form-group">
                <label>Media Type</label>
                <select name="media_type" onchange="previewMedia()">
                    <option value="image">Image</option>
                    <option value="video">Video</option>
                </select>
            </div>
        </div>
        
        <div class="form-row" id="mediaImageRow">
            <div class="form-group">
                <label>Upload Media Image</label>
                <input type="file" name="media_image" accept="image/*">
            </div>
        </div>
        
        <div class="form-row" id="mediaVideoRow" style="display: none;">
            <div class="form-group">
                <label>Media URL (Video)</label>
                <input type="text" name="media_url" placeholder="https://example.com/video.mp4" onchange="previewMedia()">
            </div>
            <div class="form-group">
                <label>Video Poster Image URL</label>
                <input type="text" name="video_poster" placeholder="https://example.com/poster.jpg">
            </div>
        </div>
        
        <div id="mediaPreview" class="media-preview"></div>
        
        <div class="form-group">
            <label>Tags</label>
            <div class="tag-group">
                <?php foreach ($tags as $tag): ?>
                    <label class="tag-checkbox">
                        <input type="checkbox" name="tags[]" value="<?php echo $tag['id']; ?>">
                        <?php echo htmlspecialchars($tag['name']); ?>
                    </label>
                <?php endforeach; ?>
            </div>
        </div>
        
        <div class="form-group">
            <button type="submit" class="action-btn edit-btn" style="padding: 0.75rem 2rem; font-size: 1rem;">
                <i class="fas fa-save"></i> Create Post
            </button>
        </div>
    </form>
</div>

<script>
    // Toggle media fields based on media type
    const mediaTypeSelect = document.querySelector('[name="media_type"]');
    const mediaImageRow = document.getElementById('mediaImageRow');
    const mediaVideoRow = document.getElementById('mediaVideoRow');
    
    function toggleMediaFields() {
        if (mediaTypeSelect.value === 'video') {
            mediaImageRow.style.display = 'none';
            mediaVideoRow.style.display = 'grid';
        } else {
            mediaImageRow.style.display = 'grid';
            mediaVideoRow.style.display = 'none';
        }
    }
    
    mediaTypeSelect.addEventListener('change', toggleMediaFields);
    toggleMediaFields();
</script>

<?php require_once '../includes/footer.php'; ?>