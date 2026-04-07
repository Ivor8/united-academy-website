<?php
$pageTitle = 'Add Testimonial';
require_once '../includes/header.php';

// Temporarily bypass permission check for debugging
// if (!hasPermission('create_testimonials')) {
//     header('Location: index.php');
//     exit();
// }

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
    
    .form-container {
        max-width: 800px !important;
        margin: 0 auto !important;
        padding: 2rem !important;
        background: var(--white) !important;
        border-radius: 12px !important;
        box-shadow: 0 4px 20px rgba(0,0,0,0.05) !important;
    }
    
    .form-group {
        margin-bottom: 1.5rem !important;
    }
    
    .form-group label {
        display: block !important;
        margin-bottom: 0.5rem !important;
        font-weight: 600 !important;
        color: var(--dark) !important;
        font-size: 0.9rem !important;
    }
    
    .form-group input, 
    .form-group select, 
    .form-group textarea {
        width: 100% !important;
        padding: 0.75rem 1rem !important;
        border: 2px solid #E2E8F0 !important;
        border-radius: 12px !important;
        font-family: inherit !important;
        font-size: 0.9rem !important;
        transition: var(--transition) !important;
        background: var(--white) !important;
        box-sizing: border-box !important;
    }
    
    .form-group input:focus, 
    .form-group select:focus, 
    .form-group textarea:focus {
        outline: none !important;
        border-color: var(--blue) !important;
        box-shadow: 0 0 0 3px rgba(30, 100, 200, 0.1) !important;
    }
    
    .form-row {
        display: grid !important;
        grid-template-columns: 1fr 1fr !important;
        gap: 1rem !important;
    }
    
    .rating-select {
        display: flex !important;
        gap: 0.5rem !important;
        flex-direction: row-reverse !important;
        justify-content: flex-end !important;
        padding: 0.75rem !important;
        background: var(--light) !important;
        border: 2px solid #E2E8F0 !important;
        border-radius: 12px !important;
    }
    
    .rating-select input {
        display: none !important;
    }
    
    .rating-select label {
        font-size: 1.5rem !important;
        color: #CBD5E1 !important;
        cursor: pointer !important;
        transition: var(--transition) !important;
    }
    
    .rating-select label:hover,
    .rating-select label:hover ~ label,
    .rating-select input:checked ~ label {
        color: #FBBF24 !important;
        transform: scale(1.1) !important;
    }
    
    .media-preview {
        margin-top: 1rem !important;
        max-width: 300px !important;
        border-radius: 12px !important;
        overflow: hidden !important;
    }
    
    .media-preview img,
    .media-preview video {
        width: 100% !important;
        border-radius: 12px !important;
        box-shadow: 0 4px 12px rgba(0,0,0,0.1) !important;
    }
    
    .btn {
        padding: 0.75rem 2rem !important;
        border: none !important;
        border-radius: 12px !important;
        font-weight: 600 !important;
        cursor: pointer !important;
        transition: var(--transition) !important;
        font-size: 0.9rem !important;
        display: inline-flex !important;
        align-items: center !important;
        gap: 0.5rem !important;
    }
    
    .btn-primary {
        background: var(--blue) !important;
        color: white !important;
    }
    
    .btn-primary:hover {
        background: #1565c0 !important;
        transform: translateY(-2px) !important;
        box-shadow: 0 4px 12px rgba(30, 100, 200, 0.2) !important;
    }
    
    .btn-secondary {
        background: var(--gray) !important;
        color: white !important;
    }
    
    .btn-secondary:hover {
        background: #475569 !important;
        transform: translateY(-2px) !important;
    }
    
    .alert {
        padding: 1rem 1.5rem !important;
        border-radius: 12px !important;
        margin-bottom: 1.5rem !important;
        font-weight: 500 !important;
    }
    
    .alert-success {
        background: rgba(46, 125, 50, 0.1) !important;
        color: var(--green) !important;
        border: 2px solid rgba(46, 125, 50, 0.2) !important;
    }
    
    .alert-error {
        background: rgba(211, 47, 47, 0.1) !important;
        color: var(--red) !important;
        border: 2px solid rgba(211, 47, 47, 0.2) !important;
    }
    
    .checkbox-group {
        display: flex !important;
        align-items: center !important;
        gap: 0.5rem !important;
        padding: 0.75rem !important;
        background: var(--light) !important;
        border: 2px solid #E2E8F0 !important;
        border-radius: 12px !important;
        cursor: pointer !important;
        transition: var(--transition) !important;
    }
    
    .checkbox-group:hover {
        border-color: var(--blue) !important;
        background: rgba(30, 100, 200, 0.05) !important;
    }
    
    .checkbox-group input[type="checkbox"] {
        width: auto !important;
        margin: 0 !important;
        transform: scale(1.2) !important;
    }
    
    input[type="file"] {
        padding: 0.5rem !important;
        border: 2px dashed #E2E8F0 !important;
        background: var(--light) !important;
        cursor: pointer !important;
    }
    
    input[type="file"]:hover {
        border-color: var(--blue) !important;
        background: rgba(30, 100, 200, 0.05) !important;
    }
    
    /* Responsive */
    @media (max-width: 768px) {
        .form-container {
            padding: 1rem !important;
        }
        
        .form-row {
            grid-template-columns: 1fr !important;
        }
        
        .rating-select {
            justify-content: center !important;
        }
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