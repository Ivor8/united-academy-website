<?php
$pageTitle = 'Edit Blog Post';
require_once '../includes/header.php';

if (!hasPermission('edit_blog')) {
    header('Location: index.php');
    exit();
}

$pdo = getDB();
$error = '';
$success = '';

// Get blog post
$postId = isset($_GET['id']) ? intval($_GET['id']) : 0;
$stmt = $pdo->prepare("SELECT * FROM blog_posts WHERE id = ?");
$stmt->execute([$postId]);
$post = $stmt->fetch();

if (!$post) {
    header('Location: index.php');
    exit();
}

// Get categories
$categories = ['blog', 'news', 'events', 'publications', 'video'];

// Get tags
$tags = $pdo->query("SELECT * FROM tags ORDER BY name")->fetchAll();

// Get post tags
$postTagsStmt = $pdo->prepare("SELECT tag_id FROM blog_post_tags WHERE blog_post_id = ?");
$postTagsStmt->execute([$postId]);
$postTags = $postTagsStmt->fetchAll(PDO::FETCH_COLUMN);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = sanitize($_POST['title']);
    $slug = sanitize($_POST['slug']);
    $excerpt = sanitize($_POST['excerpt']);
    $content = $_POST['content'];
    $category = sanitize($_POST['category']);
    $status = sanitize($_POST['status']);
    $featured = isset($_POST['featured']) ? 1 : 0;
    $mediaType = sanitize($_POST['media_type']);
    $mediaUrl = $post['media_url']; // Keep existing if not changed
    $videoPoster = $post['video_poster']; // Keep existing if not changed
    $featuredImage = $post['featured_image']; // Keep existing if not changed
    
    // Handle featured image upload
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
    
    // Update post
    $stmt = $pdo->prepare("
        UPDATE blog_posts 
        SET title = ?, slug = ?, excerpt = ?, content = ?, featured_image = ?, media_type = ?, media_url = ?, video_poster = ?, category = ?, status = ?, featured = ? 
        WHERE id = ?
    ");
    
    if ($stmt->execute([$title, $slug, $excerpt, $content, $featuredImage, $mediaType, $mediaUrl, $videoPoster, $category, $status, $featured, $postId])) {
        
        // Update tags
        $pdo->prepare("DELETE FROM blog_post_tags WHERE blog_post_id = ?")->execute([$postId]);
        if (isset($_POST['tags']) && is_array($_POST['tags'])) {
            foreach ($_POST['tags'] as $tagId) {
                $tagStmt = $pdo->prepare("INSERT INTO blog_post_tags (blog_post_id, tag_id) VALUES (?, ?)");
                $tagStmt->execute([$postId, $tagId]);
            }
        }
        
        // Log activity
        $logStmt = $pdo->prepare("INSERT INTO activity_logs (user_id, action, table_name, record_id) VALUES (?, 'UPDATE', 'blog_posts', ?)");
        $logStmt->execute([$_SESSION['user_id'], $postId]);
        
        $success = 'Blog post updated successfully!';
    } else {
        $error = 'Failed to update blog post. Please try again.';
    }
}

$extraCss = '<link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">
<style>
    .form-container {
        max-width: 1000px;
        margin: 0 auto;
    }
    .form-group {
        margin-bottom: 1.5rem;
    }
    .form-group label {
        display: block;
        margin-bottom: 0.5rem;
        font-weight: 600;
    }
    .form-group input, 
    .form-group select, 
    .form-group textarea {
        width: 100%;
        padding: 0.75rem;
        border: 1px solid #E2E8F0;
        border-radius: 12px;
        font-family: inherit;
    }
    .form-row {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 1rem;
    }
    .editor-container {
        height: 400px;
    }
    .tag-group {
        display: flex;
        flex-wrap: wrap;
        gap: 0.75rem;
    }
    .tag-checkbox {
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }
    .tag-checkbox input {
        width: auto;
    }
    .media-preview {
        margin-top: 1rem;
        max-width: 300px;
    }
    .media-preview img,
    .media-preview video {
        width: 100%;
        border-radius: 12px;
    }
    .current-media {
        margin-top: 0.5rem;
        padding: 0.5rem;
        background: var(--light);
        border-radius: 8px;
        font-size: 0.85rem;
    }
</style>';
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
    
    // Set existing content
    quill.root.innerHTML = `<?php echo addslashes($post["content"]); ?>`;
    
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
        <h1><i class="fas fa-edit"></i> Edit Blog Post</h1>
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
                <input type="text" name="title" required value="<?php echo htmlspecialchars($post['title']); ?>">
            </div>
            
            <div class="form-group">
                <label>Slug *</label>
                <input type="text" name="slug" required value="<?php echo htmlspecialchars($post['slug']); ?>">
            </div>
        </div>
        
        <div class="form-row">
            <div class="form-group">
                <label>Category *</label>
                <select name="category" required>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?php echo $cat; ?>" <?php echo $post['category'] === $cat ? 'selected' : ''; ?>>
                            <?php echo ucfirst($cat); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="form-group">
                <label>Status *</label>
                <select name="status" required>
                    <option value="draft" <?php echo $post['status'] === 'draft' ? 'selected' : ''; ?>>Draft</option>
                    <option value="published" <?php echo $post['status'] === 'published' ? 'selected' : ''; ?>>Published</option>
                    <option value="archived" <?php echo $post['status'] === 'archived' ? 'selected' : ''; ?>>Archived</option>
                </select>
            </div>
        </div>
        
        <div class="form-row">
            <div class="form-group">
                <label>
                    <input type="checkbox" name="featured" value="1" <?php echo $post['featured'] ? 'checked' : ''; ?>>
                    Feature this post
                </label>
            </div>
            
            <div class="form-group">
                <label>Current Stats</label>
                <div class="current-media">
                    Views: <?php echo number_format($post['views']); ?> | 
                    Created: <?php echo formatDate($post['created_at']); ?>
                    <?php if ($post['published_at']): ?>
                        | Published: <?php echo formatDate($post['published_at']); ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <div class="form-group">
            <label>Excerpt / Short Description</label>
            <textarea name="excerpt" rows="3"><?php echo htmlspecialchars($post['excerpt']); ?></textarea>
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
                <?php if ($post['featured_image']): ?>
                    <div class="current-media">
                        Current: <img src="<?php echo $post['featured_image']; ?>" alt="" style="width: 100px; height: 60px; object-fit: cover; border-radius: 4px; vertical-align: middle;">
                    </div>
                <?php endif; ?>
            </div>
            
            <div class="form-group">
                <label>Media Type</label>
                <select name="media_type" onchange="previewMedia()">
                    <option value="image" <?php echo $post['media_type'] === 'image' ? 'selected' : ''; ?>>Image</option>
                    <option value="video" <?php echo $post['media_type'] === 'video' ? 'selected' : ''; ?>>Video</option>
                </select>
            </div>
        </div>
        
        <div class="form-row" id="mediaImageRow">
            <div class="form-group">
                <label>Upload Media Image</label>
                <input type="file" name="media_image" accept="image/*">
                <?php if ($post['media_type'] === 'image' && $post['media_url']): ?>
                    <div class="current-media">
                        Current: <img src="<?php echo $post['media_url']; ?>" alt="" style="width: 100px; height: 60px; object-fit: cover; border-radius: 4px; vertical-align: middle;">
                    </div>
                <?php endif; ?>
            </div>
        </div>
        
        <div class="form-row" id="mediaVideoRow" style="display: <?php echo $post['media_type'] === 'video' ? 'block' : 'none'; ?>;">
            <div class="form-group">
                <label>Media URL (Video)</label>
                <input type="text" name="media_url" value="<?php echo htmlspecialchars($post['media_url']); ?>" placeholder="https://example.com/video.mp4" onchange="previewMedia()">
            </div>
            <div class="form-group">
                <label>Video Poster Image URL</label>
                <input type="text" name="video_poster" value="<?php echo htmlspecialchars($post['video_poster']); ?>" placeholder="https://example.com/poster.jpg">
            </div>
        </div>
        
        <div id="mediaPreview" class="media-preview"></div>
        
        <div class="form-group">
            <label>Tags</label>
            <div class="tag-group">
                <?php foreach ($tags as $tag): ?>
                    <label class="tag-checkbox">
                        <input type="checkbox" name="tags[]" value="<?php echo $tag['id']; ?>" <?php echo in_array($tag['id'], $postTags) ? 'checked' : ''; ?>>
                        <?php echo htmlspecialchars($tag['name']); ?>
                    </label>
                <?php endforeach; ?>
            </div>
        </div>
        
        <div class="form-group">
            <button type="submit" class="action-btn edit-btn no-loading" style="padding: 0.75rem 2rem; font-size: 1rem;">
                <i class="fas fa-save"></i> Update Post
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
