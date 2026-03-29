<?php
$pageTitle = 'Add Testimonial';
require_once '../includes/header.php';

if (!hasPermission('create_testimonials')) {
    header('Location: index.php');
    exit();
}

$pdo = getDB();
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $studentName = sanitize($_POST['student_name']);
    $studentProgram = sanitize($_POST['student_program']);
    $testimonialText = sanitize($_POST['testimonial_text']);
    $rating = intval($_POST['rating']);
    $featured = isset($_POST['featured']) ? 1 : 0;
    $status = sanitize($_POST['status']);
    $graduationYear = !empty($_POST['graduation_year']) ? intval($_POST['graduation_year']) : null;
    $currentPosition = sanitize($_POST['current_position']);
    $companyName = sanitize($_POST['company_name']);
    $mediaType = sanitize($_POST['media_type']);
    $mediaUrl = '';
    $videoPoster = '';
    
    // Handle avatar upload
    $studentAvatar = '';
    if (isset($_FILES['student_avatar']) && $_FILES['student_avatar']['error'] === UPLOAD_ERR_OK) {
        $uploaded = uploadFile($_FILES['student_avatar'], 'testimonials');
        if ($uploaded) {
            $studentAvatar = $uploaded;
        }
    }
    
    // Handle media
    if ($mediaType === 'video' && !empty($_POST['media_url'])) {
        $mediaUrl = sanitize($_POST['media_url']);
        if (!empty($_POST['video_poster'])) {
            $videoPoster = sanitize($_POST['video_poster']);
        }
    } elseif ($mediaType === 'image' && isset($_FILES['media_image']) && $_FILES['media_image']['error'] === UPLOAD_ERR_OK) {
        $uploaded = uploadFile($_FILES['media_image'], 'testimonials');
        if ($uploaded) {
            $mediaUrl = $uploaded;
        }
    }
    
    $stmt = $pdo->prepare("
        INSERT INTO testimonials (student_name, student_program, student_avatar, testimonial_text, media_type, media_url, video_poster, rating, featured, status, graduation_year, current_position, company_name, author_id) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");
    
    if ($stmt->execute([$studentName, $studentProgram, $studentAvatar, $testimonialText, $mediaType, $mediaUrl, $videoPoster, $rating, $featured, $status, $graduationYear, $currentPosition, $companyName, $_SESSION['user_id']])) {
        $logStmt = $pdo->prepare("INSERT INTO activity_logs (user_id, action, table_name, record_id) VALUES (?, 'CREATE', 'testimonials', ?)");
        $logStmt->execute([$_SESSION['user_id'], $pdo->lastInsertId()]);
        
        $success = 'Testimonial added successfully!';
        header('refresh:2;url=index.php');
    } else {
        $error = 'Failed to add testimonial. Please try again.';
    }
}

$extraCss = '<style>
    .form-container {
        max-width: 800px;
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
    .rating-select {
        display: flex;
        gap: 0.5rem;
        flex-direction: row-reverse;
        justify-content: flex-end;
    }
    .rating-select input {
        display: none;
    }
    .rating-select label {
        font-size: 1.5rem;
        color: #CBD5E1;
        cursor: pointer;
        transition: color 0.2s;
    }
    .rating-select label:hover,
    .rating-select label:hover ~ label,
    .rating-select input:checked ~ label {
        color: #FBBF24;
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
</style>';
?>

<div class="form-container">
    <div class="card-header">
        <h1><i class="fas fa-plus"></i> Add New Testimonial</h1>
        <a href="index.php" class="view-all">Back to Testimonials</a>
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
                <label>Student Name *</label>
                <input type="text" name="student_name" required placeholder="e.g., John Doe">
            </div>
            
            <div class="form-group">
                <label>Program</label>
                <input type="text" name="student_program" placeholder="e.g., Pharmacy Salesperson">
            </div>
        </div>
        
        <div class="form-row">
            <div class="form-group">
                <label>Current Position</label>
                <input type="text" name="current_position" placeholder="e.g., Pharmacy Manager">
            </div>
            
            <div class="form-group">
                <label>Company Name</label>
                <input type="text" name="company_name" placeholder="e.g., Zenith Pro Clinic">
            </div>
        </div>
        
        <div class="form-row">
            <div class="form-group">
                <label>Graduation Year</label>
                <input type="number" name="graduation_year" placeholder="e.g., 2025" min="2000" max="2030">
            </div>
            
            <div class="form-group">
                <label>Status *</label>
                <select name="status" required>
                    <option value="pending">Pending Review</option>
                    <option value="approved">Approved</option>
                </select>
            </div>
        </div>
        
        <div class="form-group">
            <label>Rating *</label>
            <div class="rating-select">
                <input type="radio" name="rating" value="5" id="star5"><label for="star5">★</label>
                <input type="radio" name="rating" value="4" id="star4"><label for="star4">★</label>
                <input type="radio" name="rating" value="3" id="star3"><label for="star3">★</label>
                <input type="radio" name="rating" value="2" id="star2"><label for="star2">★</label>
                <input type="radio" name="rating" value="1" id="star1" checked><label for="star1">★</label>
            </div>
        </div>
        
        <div class="form-group">
            <label>Testimonial Text *</label>
            <textarea name="testimonial_text" rows="5" required placeholder="What the student says about their experience..."></textarea>
        </div>
        
        <div class="form-row">
            <div class="form-group">
                <label>Student Avatar</label>
                <input type="file" name="student_avatar" accept="image/*">
            </div>
            
            <div class="form-group">
                <label>
                    <input type="checkbox" name="featured" value="1">
                    Feature this testimonial
                </label>
            </div>
        </div>
        
        <div class="form-row">
            <div class="form-group">
                <label>Media Type</label>
                <select name="media_type" id="mediaType">
                    <option value="image">Image</option>
                    <option value="video">Video</option>
                </select>
            </div>
        </div>
        
        <div id="mediaImageRow">
            <div class="form-group">
                <label>Upload Media Image</label>
                <input type="file" name="media_image" accept="image/*">
            </div>
        </div>
        
        <div id="mediaVideoRow" style="display: none;">
            <div class="form-group">
                <label>Media URL (Video)</label>
                <input type="text" name="media_url" placeholder="https://example.com/video.mp4">
            </div>
            <div class="form-group">
                <label>Video Poster Image URL</label>
                <input type="text" name="video_poster" placeholder="https://example.com/poster.jpg">
            </div>
        </div>
        
        <div id="mediaPreview" class="media-preview"></div>
        
        <div class="form-group">
            <button type="submit" class="action-btn edit-btn" style="padding: 0.75rem 2rem; font-size: 1rem;">
                <i class="fas fa-save"></i> Add Testimonial
            </button>
        </div>
    </form>
</div>

<script>
    const mediaType = document.getElementById('mediaType');
    const mediaImageRow = document.getElementById('mediaImageRow');
    const mediaVideoRow = document.getElementById('mediaVideoRow');
    
    function toggleMediaFields() {
        if (mediaType.value === 'video') {
            mediaImageRow.style.display = 'none';
            mediaVideoRow.style.display = 'block';
        } else {
            mediaImageRow.style.display = 'block';
            mediaVideoRow.style.display = 'none';
        }
    }
    
    mediaType.addEventListener('change', toggleMediaFields);
    toggleMediaFields();
    
    // Rating stars preview
    const ratingInputs = document.querySelectorAll('.rating-select input');
    ratingInputs.forEach(input => {
        input.addEventListener('change', function() {
            // Stars are handled by CSS
        });
    });
</script>

<?php require_once '../includes/footer.php'; ?>