<?php
$pageTitle = 'Add Testimonial';
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

// Function to add debug message
function addDebug($message, $type = 'info') {
    global $debugMessages;
    $debugMessages[] = [
        'time' => date('H:i:s'),
        'message' => $message,
        'type' => $type
    ];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    addDebug("=== TESTIMONIAL FORM SUBMITTED ===", 'info');
    addDebug("POST data received", 'debug');
    
    try {
        // Store form data for repopulation
        $formData = $_POST;
        
        // Validate required fields
        addDebug("Validating required fields...", 'info');
        if (empty($_POST['student_name'])) {
            throw new Exception("❌ Student name is required");
        }
        if (empty($_POST['testimonial_text'])) {
            throw new Exception("❌ Testimonial text is required");
        }
        if (empty($_POST['rating'])) {
            throw new Exception("❌ Rating is required");
        }
        addDebug("✅ Required fields validation passed", 'success');
        
        // Sanitize inputs
        addDebug("Sanitizing inputs...", 'info');
        $studentName = sanitize($_POST['student_name']);
        $studentProgram = sanitize($_POST['student_program'] ?? '');
        $testimonialText = sanitize($_POST['testimonial_text']);
        $rating = intval($_POST['rating']);
        $featured = isset($_POST['featured']) ? 1 : 0;
        $status = sanitize($_POST['status'] ?? 'pending');
        $graduationYear = !empty($_POST['graduation_year']) ? intval($_POST['graduation_year']) : null;
        $currentPosition = sanitize($_POST['current_position'] ?? '');
        $companyName = sanitize($_POST['company_name'] ?? '');
        $mediaType = sanitize($_POST['media_type'] ?? 'image');
        $mediaUrl = '';
        $videoPoster = '';
        $studentAvatar = '';
        
        addDebug("Sanitized data:", 'debug');
        addDebug("- Student Name: " . $studentName, 'debug');
        addDebug("- Program: " . ($studentProgram ?: 'Not provided'), 'debug');
        addDebug("- Rating: " . $rating . "/5", 'debug');
        addDebug("- Status: " . $status, 'debug');
        addDebug("- Media Type: " . $mediaType, 'debug');
        
        // Handle avatar upload
        addDebug("Processing student avatar...", 'info');
        if (isset($_FILES['student_avatar']) && $_FILES['student_avatar']['error'] === UPLOAD_ERR_OK) {
            addDebug("Avatar detected: " . $_FILES['student_avatar']['name'], 'info');
            $uploaded = uploadFile($_FILES['student_avatar'], 'testimonials');
            if ($uploaded) {
                $studentAvatar = $uploaded;
                addDebug("✅ Avatar uploaded successfully: " . $studentAvatar, 'success');
            } else {
                addDebug("⚠️ Avatar upload failed", 'warning');
            }
        } else {
            addDebug("No avatar to upload", 'info');
        }
        
        // Handle media
        addDebug("Processing media based on type: " . $mediaType, 'info');
        if ($mediaType === 'video' && !empty($_POST['media_url'])) {
            $mediaUrl = sanitize($_POST['media_url']);
            $videoPoster = !empty($_POST['video_poster']) ? sanitize($_POST['video_poster']) : '';
            addDebug("✅ Video URL added: " . $mediaUrl, 'success');
            if ($videoPoster) {
                addDebug("✅ Video poster URL added: " . $videoPoster, 'success');
            }
        } elseif ($mediaType === 'image' && isset($_FILES['media_image']) && $_FILES['media_image']['error'] === UPLOAD_ERR_OK) {
            addDebug("Media image detected: " . $_FILES['media_image']['name'], 'info');
            $uploaded = uploadFile($_FILES['media_image'], 'testimonials');
            if ($uploaded) {
                $mediaUrl = $uploaded;
                addDebug("✅ Media image uploaded successfully: " . $mediaUrl, 'success');
            } else {
                addDebug("⚠️ Media image upload failed", 'warning');
            }
        }
        
        // Prepare INSERT statement
        addDebug("Preparing database INSERT...", 'info');
        $stmt = $pdo->prepare("
            INSERT INTO testimonials (student_name, student_program, student_avatar, testimonial_text, media_type, media_url, video_poster, rating, featured, status, graduation_year, current_position, company_name, author_id) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        
        $params = [$studentName, $studentProgram, $studentAvatar, $testimonialText, $mediaType, $mediaUrl, $videoPoster, $rating, $featured, $status, $graduationYear, $currentPosition, $companyName, $_SESSION['user_id']];
        
        addDebug("Executing INSERT with parameters:", 'debug');
        foreach ($params as $i => $param) {
            $displayValue = is_string($param) ? (strlen($param) > 50 ? substr($param, 0, 50) . '...' : $param) : $param;
            addDebug("  Param " . ($i+1) . ": " . ($displayValue ?: 'NULL'), 'debug');
        }
        
        // Execute INSERT
        if (!$stmt->execute($params)) {
            $errorInfo = $stmt->errorInfo();
            throw new Exception("Database insert failed: " . $errorInfo[2]);
        }
        
        $testimonialId = $pdo->lastInsertId();
        addDebug("✅ Database INSERT successful! Testimonial ID: " . $testimonialId, 'success');
        
        // Log activity
        addDebug("Logging activity...", 'info');
        $logStmt = $pdo->prepare("INSERT INTO activity_logs (user_id, action, table_name, record_id) VALUES (?, 'CREATE', 'testimonials', ?)");
        $logStmt->execute([$_SESSION['user_id'], $testimonialId]);
        addDebug("✅ Activity logged", 'success');
        
        addDebug("🎉 SUCCESS! Testimonial created successfully!", 'success');
        $success = true;
        
    } catch (Exception $e) {
        $error = $e->getMessage();
        addDebug("❌ EXCEPTION CAUGHT: " . $e->getMessage(), 'error');
        addDebug("Stack trace: " . $e->getTraceAsString(), 'error');
    }
}

$extraCss = '<style>
    .form-container { max-width: 800px; margin: 0 auto; padding: 2rem; background: white; border-radius: 24px; box-shadow: var(--shadow); }
    .form-group { margin-bottom: 1.5rem; }
    .form-group label { display: block; margin-bottom: 0.5rem; font-weight: 600; color: var(--dark); }
    .form-group input, .form-group select, .form-group textarea { width: 100%; padding: 0.75rem; border: 2px solid #E2E8F0; border-radius: 12px; font-family: inherit; transition: var(--transition); }
    .form-group input:focus, .form-group select:focus, .form-group textarea:focus { border-color: var(--blue); outline: none; box-shadow: 0 0 0 3px rgba(30,100,200,0.1); }
    .form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; }
    .rating-select { display: flex; flex-direction: row-reverse; justify-content: flex-end; gap: 0.5rem; padding: 0.75rem; background: var(--light); border-radius: 12px; }
    .rating-select input { display: none; }
    .rating-select label { font-size: 1.5rem; color: #CBD5E1; cursor: pointer; transition: all 0.2s; }
    .rating-select label:hover, .rating-select label:hover ~ label, .rating-select input:checked ~ label { color: #FBBF24; transform: scale(1.1); }
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
    const mediaType = document.getElementById("mediaType");
    const mediaImageRow = document.getElementById("mediaImageRow");
    const mediaVideoRow = document.getElementById("mediaVideoRow");
    const mediaUrlInput = document.getElementById("mediaUrlInput");
    const mediaPreview = document.getElementById("mediaPreview");
    
    if (mediaType) {
        function toggleMediaFields() {
            if (mediaType.value === "video") {
                mediaImageRow.classList.add("hidden");
                mediaVideoRow.classList.remove("hidden");
            } else {
                mediaImageRow.classList.remove("hidden");
                mediaVideoRow.classList.add("hidden");
            }
            previewMedia();
        }
        
        function previewMedia() {
            if (!mediaPreview) return;
            const mediaUrl = mediaUrlInput ? mediaUrlInput.value : "";
            
            if (mediaType.value === "video" && mediaUrl) {
                mediaPreview.innerHTML = `<video controls src="${mediaUrl}" style="width:100%; border-radius:12px;"></video>`;
            } else {
                mediaPreview.innerHTML = "";
            }
        }
        
        mediaType.addEventListener("change", toggleMediaFields);
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
        <h1><i class="fas fa-plus"></i> Add New Testimonial</h1>
        <a href="index.php" class="view-all">Back to Testimonials</a>
    </div>
    
    <?php if ($success === true): ?>
        <div class="alert-box alert-success">
            <i class="fas fa-check-circle" style="font-size: 1.5rem;"></i>
            <div>
                <strong>Success!</strong> Testimonial created successfully!<br>
                <small>Redirecting to testimonials list...</small>
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
                <label>Student Name *</label>
                <input type="text" name="student_name" required value="<?php echo isset($formData['student_name']) ? htmlspecialchars($formData['student_name']) : ''; ?>">
            </div>
            
            <div class="form-group">
                <label>Program</label>
                <input type="text" name="student_program" value="<?php echo isset($formData['student_program']) ? htmlspecialchars($formData['student_program']) : ''; ?>">
                <small style="color: var(--gray);">e.g., Pharmacy Salesperson</small>
            </div>
        </div>
        
        <div class="form-row">
            <div class="form-group">
                <label>Current Position</label>
                <input type="text" name="current_position" value="<?php echo isset($formData['current_position']) ? htmlspecialchars($formData['current_position']) : ''; ?>">
                <small style="color: var(--gray);">e.g., Pharmacy Manager</small>
            </div>
            
            <div class="form-group">
                <label>Company Name</label>
                <input type="text" name="company_name" value="<?php echo isset($formData['company_name']) ? htmlspecialchars($formData['company_name']) : ''; ?>">
                <small style="color: var(--gray);">e.g., Zenith Pro Clinic</small>
            </div>
        </div>
        
        <div class="form-row">
            <div class="form-group">
                <label>Graduation Year</label>
                <input type="number" name="graduation_year" value="<?php echo isset($formData['graduation_year']) ? htmlspecialchars($formData['graduation_year']) : ''; ?>" min="2000" max="2030">
                <small style="color: var(--gray);">Year of graduation</small>
            </div>
            
            <div class="form-group">
                <label>Status *</label>
                <select name="status" required>
                    <option value="pending" <?php echo (isset($formData['status']) && $formData['status'] === 'pending') ? 'selected' : ''; ?>>Pending Review</option>
                    <option value="approved" <?php echo (isset($formData['status']) && $formData['status'] === 'approved') ? 'selected' : ''; ?>>Approved</option>
                </select>
            </div>
        </div>
        
        <div class="form-group">
            <label>Rating *</label>
            <div class="rating-select">
                <input type="radio" name="rating" value="5" id="star5" <?php echo (isset($formData['rating']) && $formData['rating'] == 5) ? 'checked' : ''; ?>><label for="star5">★</label>
                <input type="radio" name="rating" value="4" id="star4" <?php echo (isset($formData['rating']) && $formData['rating'] == 4) ? 'checked' : ''; ?>><label for="star4">★</label>
                <input type="radio" name="rating" value="3" id="star3" <?php echo (isset($formData['rating']) && $formData['rating'] == 3) ? 'checked' : ''; ?>><label for="star3">★</label>
                <input type="radio" name="rating" value="2" id="star2" <?php echo (isset($formData['rating']) && $formData['rating'] == 2) ? 'checked' : ''; ?>><label for="star2">★</label>
                <input type="radio" name="rating" value="1" id="star1" <?php echo (!isset($formData['rating']) || $formData['rating'] == 1) ? 'checked' : ''; ?>><label for="star1">★</label>
            </div>
            <small style="color: var(--gray);">Click on stars to rate (5 = best)</small>
        </div>
        
        <div class="form-group">
            <label>Testimonial Text *</label>
            <textarea name="testimonial_text" rows="5" required><?php echo isset($formData['testimonial_text']) ? htmlspecialchars($formData['testimonial_text']) : ''; ?></textarea>
            <small style="color: var(--gray);">What the student says about their experience</small>
        </div>
        
        <div class="form-row">
            <div class="form-group">
                <label>Student Avatar</label>
                <input type="file" name="student_avatar" accept="image/*">
                <small style="color: var(--gray);">JPG, PNG, GIF up to 10MB</small>
            </div>
            
            <div class="form-group">
                <label>
                    <input type="checkbox" name="featured" value="1" <?php echo isset($formData['featured']) ? 'checked' : ''; ?>>
                    Feature this testimonial
                </label>
                <small style="color: var(--gray; display: block; margin-top: 0.5rem;">Featured testimonials appear on homepage</small>
            </div>
        </div>
        
        <div class="form-group">
            <label>Media Type</label>
            <select name="media_type" id="mediaType">
                <option value="image" <?php echo (isset($formData['media_type']) && $formData['media_type'] === 'image') ? 'selected' : ''; ?>>Image</option>
                <option value="video" <?php echo (isset($formData['media_type']) && $formData['media_type'] === 'video') ? 'selected' : ''; ?>>Video</option>
            </select>
            <small style="color: var(--gray);">Choose whether to add an image or video</small>
        </div>
        
        <div id="mediaImageRow">
            <div class="form-group">
                <label>Upload Media Image</label>
                <input type="file" name="media_image" accept="image/*">
                <small style="color: var(--gray);">JPG, PNG, GIF up to 10MB</small>
            </div>
        </div>
        
        <div id="mediaVideoRow" class="hidden">
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
            <button type="submit" class="action-btn edit-btn" style="padding: 0.75rem 2rem;">
                <i class="fas fa-save"></i> Add Testimonial
            </button>
        </div>
    </form>
    
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