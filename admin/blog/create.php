<?php
$pageTitle = 'Create Blog Post';
require_once '../includes/header.php';

// Enable maximum error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

$pdo = getDB();
$error = '';
$success = '';
$debugMessages = []; // Array to store all debug messages
$formData = []; // Store submitted form data

echo "Page loaded at " . date('H:i:s') . "<br>";

// Function to add debug message
function addDebug($message, $type = 'info') {
    global $debugMessages;
    $debugMessages[] = [
        'time' => date('H:i:s'),
        'message' => $message,
        'type' => $type
    ];
}

// Get categories for dropdown
$categories = ['blog', 'news', 'events', 'publications', 'video'];

// Get tags
$tags = $pdo->query("SELECT * FROM tags ORDER BY name")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    echo "Form submitted<br>";
    addDebug("=== FORM SUBMITTED ===", 'info');
    addDebug("POST data received", 'debug');
    
    try {
        // Store form data for repopulation
        $formData = $_POST;
        
        // Validate required fields
        addDebug("Validating required fields...", 'info');
        if (empty($_POST['title'])) {
            throw new Exception("❌ Title is required");
        }
        if (empty($_POST['content'])) {
            throw new Exception("❌ Content is required");
        }
        if (empty($_POST['category'])) {
            throw new Exception("❌ Category is required");
        }
        addDebug("✅ Required fields validation passed", 'success');
        
        // Sanitize inputs
        addDebug("Sanitizing inputs...", 'info');
        $title = sanitize($_POST['title']);
        $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $title)));
        $excerpt = sanitize($_POST['excerpt'] ?? '');
        $content = $_POST['content'];
        $category = sanitize($_POST['category']);
        $status = sanitize($_POST['status']);
        $featured = isset($_POST['featured']) ? 1 : 0;
        $mediaType = sanitize($_POST['media_type'] ?? 'image');
        $mediaUrl = '';
        $videoPoster = '';
        $featuredImage = '';
        
        addDebug("Sanitized data:", 'debug');
        addDebug("- Title: " . $title, 'debug');
        addDebug("- Slug: " . $slug, 'debug');
        addDebug("- Category: " . $category, 'debug');
        addDebug("- Status: " . $status, 'debug');
        addDebug("- Media Type: " . $mediaType, 'debug');
        
        // Handle featured image upload
        addDebug("Processing featured image...", 'info');
        if (isset($_FILES['featured_image']) && $_FILES['featured_image']['error'] === UPLOAD_ERR_OK) {
            addDebug("Featured image detected: " . $_FILES['featured_image']['name'], 'info');
            $uploaded = uploadFile($_FILES['featured_image'], 'blog');
            if ($uploaded) {
                $featuredImage = $uploaded;
                addDebug("✅ Featured image uploaded successfully: " . $featuredImage, 'success');
            } else {
                addDebug("⚠️ Featured image upload failed", 'warning');
            }
        } else {
            addDebug("No featured image to upload", 'info');
        }
        
        // Handle media based on type
        addDebug("Processing media based on type: " . $mediaType, 'info');
        if ($mediaType === 'video' && !empty($_POST['media_url'])) {
            $mediaUrl = sanitize($_POST['media_url']);
            $videoPoster = !empty($_POST['video_poster']) ? sanitize($_POST['video_poster']) : '';
            addDebug("✅ Video URL added: " . $mediaUrl, 'success');
            if ($videoPoster) {
                addDebug("✅ Video poster URL added: " . $videoPoster, 'success');
            }
        } elseif ($mediaType === 'pdf' && isset($_FILES['media_file']) && $_FILES['media_file']['error'] === UPLOAD_ERR_OK) {
            addDebug("PDF file detected: " . $_FILES['media_file']['name'], 'info');
            $uploaded = uploadFile($_FILES['media_file'], 'blog');
            if ($uploaded) {
                $mediaUrl = $uploaded;
                addDebug("✅ PDF uploaded successfully: " . $mediaUrl, 'success');
            } else {
                addDebug("⚠️ PDF upload failed", 'warning');
            }
        } elseif ($mediaType === 'image' && isset($_FILES['media_image']) && $_FILES['media_image']['error'] === UPLOAD_ERR_OK) {
            addDebug("Media image detected: " . $_FILES['media_image']['name'], 'info');
            $uploaded = uploadFile($_FILES['media_image'], 'blog');
            if ($uploaded) {
                $mediaUrl = $uploaded;
                addDebug("✅ Media image uploaded successfully: " . $mediaUrl, 'success');
            } else {
                addDebug("⚠️ Media image upload failed", 'warning');
            }
        }
        
        // Check if slug exists
        addDebug("Checking if slug exists...", 'info');
        $checkStmt = $pdo->prepare("SELECT id FROM blog_posts WHERE slug = ?");
        $checkStmt->execute([$slug]);
        if ($checkStmt->fetch()) {
            $originalSlug = $slug;
            $slug = $slug . '-' . time();
            addDebug("⚠️ Slug already exists, changed from '$originalSlug' to '$slug'", 'warning');
        } else {
            addDebug("✅ Slug is unique", 'success');
        }
        
        // Prepare INSERT statement
        addDebug("Preparing database INSERT...", 'info');
        $stmt = $pdo->prepare("
            INSERT INTO blog_posts (title, slug, excerpt, content, featured_image, media_type, media_url, video_poster, category, status, featured, author_id) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        
        $params = [$title, $slug, $excerpt, $content, $featuredImage, $mediaType, $mediaUrl, $videoPoster, $category, $status, $featured, $_SESSION['user_id']];
        
        addDebug("Executing INSERT with parameters:", 'debug');
        foreach ($params as $i => $param) {
            addDebug("  Param " . ($i+1) . ": " . (is_string($param) ? substr($param, 0, 50) : $param), 'debug');
        }
        
        // Execute INSERT
        echo "About to execute insert<br>";
        if (!$stmt->execute($params)) {
            $errorInfo = $stmt->errorInfo();
            throw new Exception("Database insert failed: " . $errorInfo[2]);
        }
        echo "Insert executed successfully<br>";
        
        $postId = $pdo->lastInsertId();
        addDebug("✅ Database INSERT successful! Post ID: " . $postId, 'success');
        
        // Handle tags
        if (isset($_POST['tags']) && is_array($_POST['tags']) && count($_POST['tags']) > 0) {
            addDebug("Processing tags...", 'info');
            $tagCount = 0;
            $tagStmt = $pdo->prepare("INSERT INTO blog_post_tags (blog_post_id, tag_id) VALUES (?, ?)");
            foreach ($_POST['tags'] as $tagId) {
                if ($tagStmt->execute([$postId, $tagId])) {
                    $tagCount++;
                }
            }
            addDebug("✅ Added $tagCount tags to the post", 'success');
        } else {
            addDebug("No tags selected", 'info');
        }
        
        // Log activity
        addDebug("Logging activity...", 'info');
        $logStmt = $pdo->prepare("INSERT INTO activity_logs (user_id, action, table_name, record_id) VALUES (?, 'CREATE', 'blog_posts', ?)");
        $logStmt->execute([$_SESSION['user_id'], $postId]);
        addDebug("✅ Activity logged", 'success');
        
        addDebug("🎉 SUCCESS! Blog post created successfully!", 'success');
        echo "Blog post created successfully<br>";
        $success = true;
        
    } catch (Exception $e) {
        echo "Error occurred: " . $e->getMessage() . "<br>";
        $error = $e->getMessage();
        addDebug("❌ EXCEPTION CAUGHT: " . $e->getMessage(), 'error');
        addDebug("Stack trace: " . $e->getTraceAsString(), 'error');
    }
}

$extraCss = '<style>
    .form-container { max-width: 1000px; margin: 0 auto; padding: 2rem; background: white; border-radius: 24px; box-shadow: var(--shadow); }
    .form-group { margin-bottom: 1.5rem; }
    .form-group label { display: block; margin-bottom: 0.5rem; font-weight: 600; color: var(--dark); }
    .form-group input, .form-group select, .form-group textarea { width: 100%; padding: 0.75rem; border: 2px solid #E2E8F0; border-radius: 12px; font-family: inherit; transition: var(--transition); }
    .form-group input:focus, .form-group select:focus, .form-group textarea:focus { border-color: var(--blue); outline: none; box-shadow: 0 0 0 3px rgba(30,100,200,0.1); }
    .form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; }
    .tag-group { display: flex; flex-wrap: wrap; gap: 0.75rem; margin-top: 0.5rem; }
    .tag-checkbox { display: flex; align-items: center; gap: 0.5rem; padding: 0.5rem 1rem; background: var(--light); border: 2px solid #E2E8F0; border-radius: 20px; cursor: pointer; transition: var(--transition); }
    .tag-checkbox:hover { border-color: var(--blue); background: rgba(30,100,200,0.05); }
    .media-preview { margin-top: 1rem; max-width: 300px; }
    .media-preview img, .media-preview video { width: 100%; border-radius: 12px; box-shadow: var(--shadow); }
    .hidden { display: none !important; }
    
    /* Debug Console Styles */
    .debug-console { 
        margin-top: 2rem; 
        background: #1E1E1E; 
        border-radius: 12px; 
        overflow: hidden;
        font-family: "Courier New", monospace;
        font-size: 12px;
    }
    .debug-header { 
        background: #2D2D2D; 
        padding: 0.75rem 1rem; 
        border-bottom: 1px solid #3D3D3D;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    .debug-header h3 { 
        color: #4EC9B0; 
        margin: 0; 
        font-size: 14px;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }
    .debug-clear { 
        background: #D32F2F; 
        color: white; 
        border: none; 
        padding: 4px 12px; 
        border-radius: 6px; 
        cursor: pointer;
        font-size: 11px;
    }
    .debug-messages { 
        max-height: 400px; 
        overflow-y: auto; 
        padding: 0.5rem;
    }
    .debug-message { 
        padding: 0.5rem; 
        margin: 0.25rem 0; 
        border-radius: 6px; 
        font-family: monospace;
        font-size: 12px;
        border-left: 3px solid;
    }
    .debug-message.info { border-left-color: #2196F3; background: rgba(33,150,243,0.1); color: #B3D4FC; }
    .debug-message.success { border-left-color: #4CAF50; background: rgba(76,175,80,0.1); color: #A5D6A7; }
    .debug-message.warning { border-left-color: #FF9800; background: rgba(255,152,0,0.1); color: #FFCC80; }
    .debug-message.error { border-left-color: #F44336; background: rgba(244,67,54,0.1); color: #EF9A9A; }
    .debug-message.debug { border-left-color: #9C27B0; background: rgba(156,39,176,0.1); color: #CE93D8; }
    .debug-time { color: #888; font-size: 10px; margin-right: 10px; }
    
    .alert-box { 
        padding: 1rem; 
        border-radius: 12px; 
        margin-bottom: 1.5rem; 
        display: flex;
        align-items: center;
        gap: 0.75rem;
    }
    .alert-success { background: #E8F5E9; border-left: 4px solid #4CAF50; color: #2E7D32; }
    .alert-error { background: #FFEBEE; border-left: 4px solid #F44336; color: #C62828; }
    
    @media (max-width: 768px) { .form-row { grid-template-columns: 1fr; } }
</style>';

$extraJs = '<script>
document.addEventListener("DOMContentLoaded", function() {
    const mediaTypeSelect = document.getElementById("mediaType");
    const mediaImageRow = document.getElementById("mediaImageRow");
    const mediaVideoRow = document.getElementById("mediaVideoRow");
    const mediaPdfRow = document.getElementById("mediaPdfRow");
    const mediaUrlInput = document.getElementById("mediaUrlInput");
    const mediaPreview = document.getElementById("mediaPreview");
    
    if (mediaTypeSelect) {
        function toggleMediaFields() {
            const selectedType = mediaTypeSelect.value;
            mediaImageRow.style.display = selectedType === "image" ? "block" : "none";
            mediaVideoRow.style.display = selectedType === "video" ? "block" : "none";
            mediaPdfRow.style.display = selectedType === "pdf" ? "block" : "none";
            previewMedia();
        }
        
        function previewMedia() {
            if (!mediaPreview) return;
            const mediaUrl = mediaUrlInput ? mediaUrlInput.value : "";
            
            if (mediaTypeSelect.value === "video" && mediaUrl) {
                mediaPreview.innerHTML = `<video controls src="${mediaUrl}" style="width:100%; border-radius:12px;"></video>`;
            } else {
                mediaPreview.innerHTML = "";
            }
        }
        
        mediaTypeSelect.addEventListener("change", toggleMediaFields);
        toggleMediaFields();
        
        if (mediaUrlInput) {
            mediaUrlInput.addEventListener("input", previewMedia);
        }
    }
    
    // Auto-scroll to debug console
    if (document.querySelector(".debug-messages") && document.querySelector(".debug-messages").children.length > 0) {
        document.querySelector(".debug-messages").scrollTop = document.querySelector(".debug-messages").scrollHeight;
    }
});
</script>';
?>

<div class="form-container">
    <div class="card-header">
        <h1><i class="fas fa-plus"></i> Create New Blog Post</h1>
        <a href="index.php" class="view-all">Back to Posts</a>
    </div>
    
    <?php if ($success === true): ?>
        <div class="alert-box alert-success">
            <i class="fas fa-check-circle" style="font-size: 1.5rem;"></i>
            <div>
                <strong>Success!</strong> Blog post created successfully!<br>
                <small>Redirecting to blog list...</small>
            </div>
        </div>
        <script>
            setTimeout(function() {
                window.location.href = 'index.php';
            }, 3000);
        </script>
    <?php endif; ?>
    
    <?php if ($error): ?>
        <div class="alert-box alert-error">
            <i class="fas fa-exclamation-circle" style="font-size: 1.5rem;"></i>
            <div>
                <strong>Error!</strong> <?php echo htmlspecialchars($error); ?>
            </div>
        </div>
    <?php endif; ?>
    
    <form method="POST" enctype="multipart/form-data">
        <div class="form-row">
            <div class="form-group">
                <label>Title *</label>
                <input type="text" name="title" required value="<?php echo isset($formData['title']) ? htmlspecialchars($formData['title']) : ''; ?>">
            </div>
            
            <div class="form-group">
                <label>Category *</label>
                <select name="category" required>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?php echo $cat; ?>" <?php echo (isset($formData['category']) && $formData['category'] === $cat) ? 'selected' : ''; ?>>
                            <?php echo ucfirst($cat); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
        
        <div class="form-row">
            <div class="form-group">
                <label>Status *</label>
                <select name="status" required>
                    <option value="draft" <?php echo (isset($formData['status']) && $formData['status'] === 'draft') ? 'selected' : ''; ?>>Draft</option>
                    <option value="published" <?php echo (isset($formData['status']) && $formData['status'] === 'published') ? 'selected' : ''; ?>>Published</option>
                </select>
            </div>
            
            <div class="form-group">
                <label>
                    <input type="checkbox" name="featured" value="1" <?php echo isset($formData['featured']) ? 'checked' : ''; ?>>
                    Feature this post
                </label>
            </div>
        </div>
        
        <div class="form-group">
            <label>Excerpt / Short Description</label>
            <textarea name="excerpt" rows="3"><?php echo isset($formData['excerpt']) ? htmlspecialchars($formData['excerpt']) : ''; ?></textarea>
        </div>
        
        <div class="form-group">
            <label>Content *</label>
            <textarea name="content" rows="10" required><?php echo isset($formData['content']) ? htmlspecialchars($formData['content']) : ''; ?></textarea>
        </div>
        
        <div class="form-row">
            <div class="form-group">
                <label>Featured Image</label>
                <input type="file" name="featured_image" accept="image/*">
                <small style="color: var(--gray);">JPG, PNG, GIF up to 10MB</small>
            </div>
            
            <div class="form-group">
                <label>Media Type</label>
                <select name="media_type" id="mediaType">
                    <option value="image" <?php echo (isset($formData['media_type']) && $formData['media_type'] === 'image') ? 'selected' : ''; ?>>Image</option>
                    <option value="video" <?php echo (isset($formData['media_type']) && $formData['media_type'] === 'video') ? 'selected' : ''; ?>>Video</option>
                    <option value="pdf" <?php echo (isset($formData['media_type']) && $formData['media_type'] === 'pdf') ? 'selected' : ''; ?>>PDF</option>
                </select>
            </div>
        </div>
        
        <div id="mediaImageRow">
            <div class="form-group">
                <label>Upload Media Image</label>
                <input type="file" name="media_image" accept="image/*">
                <small style="color: var(--gray);">JPG, PNG, GIF up to 10MB</small>
            </div>
        </div>

        <div id="mediaPdfRow" style="display: none;">
            <div class="form-group">
                <label>Upload PDF Document</label>
                <input type="file" name="media_file" accept="application/pdf">
                <small style="color: var(--gray);">PDF file up to 20MB</small>
            </div>
        </div>
        
        <div id="mediaVideoRow" style="display: none;">
            <div class="form-group">
                <label>Media URL (Video)</label>
                <input type="text" id="mediaUrlInput" name="media_url" placeholder="https://example.com/video.mp4" value="<?php echo isset($formData['media_url']) ? htmlspecialchars($formData['media_url']) : ''; ?>">
                <small style="color: var(--gray);">MP4, WebM, or YouTube embed URL</small>
            </div>
            <div class="form-group">
                <label>Video Poster Image URL</label>
                <input type="text" name="video_poster" placeholder="https://example.com/poster.jpg" value="<?php echo isset($formData['video_poster']) ? htmlspecialchars($formData['video_poster']) : ''; ?>">
                <small style="color: var(--gray);">Thumbnail image for video</small>
            </div>
        </div>
        
        <div id="mediaPreview" class="media-preview"></div>
        
        <div class="form-group">
            <label>Tags</label>
            <div class="tag-group">
                <?php foreach ($tags as $tag): ?>
                    <label class="tag-checkbox">
                        <input type="checkbox" name="tags[]" value="<?php echo $tag['id']; ?>" <?php echo (isset($formData['tags']) && in_array($tag['id'], $formData['tags'])) ? 'checked' : ''; ?>>
                        <?php echo htmlspecialchars($tag['name']); ?>
                    </label>
                <?php endforeach; ?>
            </div>
        </div>
        
        <div class="form-group">
            <button type="submit" class="action-btn edit-btn no-loading" style="padding: 0.75rem 2rem;">
                <i class="fas fa-save"></i> Create Post
            </button>
        </div>
    </form>
    
    <script>
        const mediaTypeSelect = document.getElementById('mediaType');
        const mediaImageRow = document.getElementById('mediaImageRow');
        const mediaVideoRow = document.getElementById('mediaVideoRow');
        const mediaPdfRow = document.getElementById('mediaPdfRow');

        function updateMediaInputs() {
            const type = mediaTypeSelect.value;
            mediaImageRow.style.display = type === 'image' ? 'block' : 'none';
            mediaVideoRow.style.display = type === 'video' ? 'block' : 'none';
            mediaPdfRow.style.display = type === 'pdf' ? 'block' : 'none';
        }

        mediaTypeSelect.addEventListener('change', updateMediaInputs);
        updateMediaInputs();
    </script>

    <!-- Debug Console -->
    <?php if (!empty($debugMessages)): ?>
    <div class="debug-console">
        <div class="debug-header">
            <h3>
                <i class="fas fa-bug"></i>
                Debug Console
                <span style="font-size: 10px; color: #888;">(<?php echo count($debugMessages); ?> messages)</span>
            </h3>
            <button class="debug-clear" onclick="this.closest('.debug-console').remove()">Clear</button>
        </div>
        <div class="debug-messages">
            <?php foreach ($debugMessages as $msg): ?>
                <div class="debug-message <?php echo $msg['type']; ?>">
                    <span class="debug-time">[<?php echo $msg['time']; ?>]</span>
                    <?php echo htmlspecialchars($msg['message']); ?>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>
</div>

<?php require_once '../includes/footer.php'; ?>