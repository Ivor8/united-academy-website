<?php
$pageTitle = 'Edit Online Course';
require_once '../includes/header.php';

if (!hasPermission('edit_online_courses')) {
    header('Location: index.php');
    exit();
}

$pdo = getDB();
$error = '';
$success = '';

// Get course data
$courseId = isset($_GET['id']) ? intval($_GET['id']) : 0;
$stmt = $pdo->prepare("SELECT * FROM online_courses WHERE id = ?");
$stmt->execute([$courseId]);
$course = $stmt->fetch();

if (!$course) {
    header('Location: index.php');
    exit();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = sanitize($_POST['title']);
    $slug = createSlug($title);
    $shortDescription = sanitize($_POST['short_description']);
    $longDescription = sanitize($_POST['long_description']);
    $category = sanitize($_POST['category']);
    $level = sanitize($_POST['level']);
    $duration = sanitize($_POST['duration']);
    $price = floatval($_POST['price']);
    $currency = sanitize($_POST['currency']);
    $language = sanitize($_POST['language']);
    $requirements = sanitize($_POST['requirements']);
    $objectives = sanitize($_POST['objectives']);
    $curriculum = sanitize($_POST['curriculum']);
    $instructorName = sanitize($_POST['instructor_name']);
    $instructorBio = sanitize($_POST['instructor_bio']);
    $videoIntroUrl = sanitize($_POST['video_intro_url']);
    $startDate = sanitize($_POST['start_date']);
    $endDate = sanitize($_POST['end_date']);
    $maxStudents = intval($_POST['max_students']);
    $metaTitle = sanitize($_POST['meta_title']);
    $metaDescription = sanitize($_POST['meta_description']);
    $metaKeywords = sanitize($_POST['meta_keywords']);
    $featured = isset($_POST['featured']) ? 1 : 0;
    
    // Handle cover image upload
    $coverImage = $course['cover_image'];
    if (isset($_FILES['cover_image']) && $_FILES['cover_image']['error'] === UPLOAD_ERR_OK) {
        $uploaded = uploadFile($_FILES['cover_image'], 'courses');
        if ($uploaded) {
            $coverImage = $uploaded;
        }
    }
    
    // Handle instructor image upload
    $instructorImage = $course['instructor_image'];
    if (isset($_FILES['instructor_image']) && $_FILES['instructor_image']['error'] === UPLOAD_ERR_OK) {
        $uploaded = uploadFile($_FILES['instructor_image'], 'instructors');
        if ($uploaded) {
            $instructorImage = $uploaded;
        }
    }
    
    // Validate required fields
    if (empty($title) || empty($shortDescription) || empty($category)) {
        $error = 'Title, short description, and category are required fields.';
    } else {
        // Check if slug already exists (excluding current course)
        $slugCheck = $pdo->prepare("SELECT id FROM online_courses WHERE slug = ? AND id != ?");
        $slugCheck->execute([$slug, $courseId]);
        if ($slugCheck->fetch()) {
            $error = 'A course with this title already exists. Please choose a different title.';
        } else {
            // Update course
            $stmt = $pdo->prepare("
                UPDATE online_courses SET 
                    title = ?, slug = ?, short_description = ?, long_description = ?, 
                    cover_image = ?, category = ?, level = ?, duration = ?, price = ?, 
                    currency = ?, language = ?, requirements = ?, objectives = ?, 
                    curriculum = ?, instructor_name = ?, instructor_bio = ?, 
                    instructor_image = ?, video_intro_url = ?, start_date = ?, end_date = ?, 
                    max_students = ?, meta_title = ?, meta_description = ?, meta_keywords = ?, 
                    featured = ?, updated_at = NOW()
                    WHERE id = ?
            ");
            $params = [
                $title, $slug, $shortDescription, $longDescription,
                $coverImage, $category, $level, $duration, $price,
                $currency, $language, $requirements, $objectives,
                $curriculum, $instructorName, $instructorBio, $instructorImage,
                $videoIntroUrl, $startDate, $endDate, $maxStudents,
                $metaTitle, $metaDescription, $metaKeywords, $featured, $courseId
            ];
            
            if ($stmt->execute($params)) {
                // Log activity
                $logStmt = $pdo->prepare("INSERT INTO activity_logs (user_id, action, table_name, record_id, new_data) VALUES (?, ?, ?, ?, ?)");
                $logStmt->execute([$_SESSION['user_id'], 'UPDATE', 'online_courses', $courseId, json_encode($_POST)]);
                
                $success = 'Course has been successfully updated!';
            } else {
                $error = 'Failed to update course. Please try again.';
            }
        }
    }
}

$extraCss = '<style>
    .edit-form {
        max-width: 1000px;
        margin: 0 auto;
    }
    
    .form-section {
        background: white;
        border-radius: 16px;
        padding: 2rem;
        margin-bottom: 2rem;
        box-shadow: 0 4px 20px rgba(0,0,0,0.08);
    }
    
    .form-section h3 {
        margin: 0 0 1.5rem 0;
        padding-bottom: 1rem;
        border-bottom: 2px solid #e5e7eb;
        color: var(--dark);
    }
    
    .current-images {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 1rem;
        margin-bottom: 2rem;
    }
    
    .image-preview {
        border: 2px solid #e5e7eb;
        border-radius: 8px;
        overflow: hidden;
        text-align: center;
        background: var(--light);
    }
    
    .image-preview img {
        width: 100%;
        height: 150px;
        object-fit: cover;
        display: block;
    }
    
    .course-stats {
        background: var(--light);
        padding: 1rem;
        border-radius: 8px;
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 1rem;
        margin-bottom: 2rem;
    }
    
    .stat-box {
        text-align: center;
        padding: 1rem;
        border-radius: 8px;
        background: white;
    }
    
    .stat-number {
        font-size: 1.5rem;
        font-weight: 700;
        color: var(--blue);
        display: block;
        margin-bottom: 0.5rem;
    }
    
    .stat-label {
        font-size: 0.85rem;
        color: #64748b;
    }
</style>';

$extraJs = '<script>
    document.addEventListener("DOMContentLoaded", function() {
        // Character counter for title
        const titleInput = document.getElementById("title");
        const slugInput = document.getElementById("slug");
        
        if (titleInput && slugInput) {
            titleInput.addEventListener("input", function() {
                const slug = this.value.toLowerCase()
                    .replace(/[^a-z0-9\s-]/g, "")
                    .replace(/\s+/g, "-")
                    .substring(0, 50);
                slugInput.value = slug;
            });
        }
        
        // File upload preview
        const coverImageInput = document.getElementById("cover_image");
        const instructorImageInput = document.getElementById("instructor_image");
        
        function handleFilePreview(input, previewId) {
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const preview = document.getElementById(previewId);
                    if (preview) {
                        preview.innerHTML = `<img src="${e.target.result}" style="max-width: 100%; height: auto; border-radius: 8px;">`;
                    }
                };
                reader.readAsDataURL(input.files[0]);
            }
        }
        
        if (coverImageInput) {
            coverImageInput.addEventListener("change", function() {
                handleFilePreview(this, "coverImagePreview");
            });
        }
        
        if (instructorImageInput) {
            instructorImageInput.addEventListener("change", function() {
                handleFilePreview(this, "instructorImagePreview");
            });
        }
    });
</script>';
?>

<div class="form-container">
    <div class="card-header">
        <h1><i class="fas fa-edit"></i> Edit Online Course</h1>
        <a href="index.php" class="view-all">Back to Courses</a>
    </div>
    
    <?php if ($error): ?>
        <div class="alert alert-error"><?php echo $error; ?></div>
    <?php endif; ?>
    
    <?php if ($success): ?>
        <div class="alert alert-success"><?php echo $success; ?></div>
    <?php endif; ?>
    
    <form method="POST" enctype="multipart/form-data" class="edit-form animate-slide-up">
        <input type="hidden" name="course_id" value="<?php echo $course['id']; ?>">
        
        <!-- Course Information -->
        <div class="form-section">
            <h3>Course Information</h3>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="title">Course Title <span class="required">*</span></label>
                    <input type="text" id="title" name="title" value="<?php echo htmlspecialchars($course['title']); ?>" required>
                    <span class="char-counter"><span id="titleCounter">50</span> / 50</span>
                </div>
                
                <div class="form-group">
                    <label for="slug">URL Slug</label>
                    <input type="text" id="slug" name="slug" value="<?php echo htmlspecialchars($course['slug']); ?>" readonly>
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="category">Category <span class="required">*</span></label>
                    <select id="category" name="category" required>
                        <option value="">Select Category</option>
                        <option value="health" <?php echo $course['category'] === 'health' ? 'selected' : ''; ?>>Health</option>
                        <option value="it" <?php echo $course['category'] === 'it' ? 'selected' : ''; ?>>IT</option>
                        <option value="business" <?php echo $course['category'] === 'business' ? 'selected' : ''; ?>>Business</option>
                        <option value="languages" <?php echo $course['category'] === 'languages' ? 'selected' : ''; ?>>Languages</option>
                        <option value="professional" <?php echo $course['category'] === 'professional' ? 'selected' : ''; ?>>Professional</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="level">Level <span class="required">*</span></label>
                    <select id="level" name="level" required>
                        <option value="">Select Level</option>
                        <option value="beginner" <?php echo $course['level'] === 'beginner' ? 'selected' : ''; ?>>Beginner</option>
                        <option value="intermediate" <?php echo $course['level'] === 'intermediate' ? 'selected' : ''; ?>>Intermediate</option>
                        <option value="advanced" <?php echo $course['level'] === 'advanced' ? 'selected' : ''; ?>>Advanced</option>
                    </select>
                </div>
            </div>
            
            <div class="form-group">
                <label for="short_description">Short Description <span class="required">*</span></label>
                <textarea id="short_description" name="short_description" rows="3" required><?php echo htmlspecialchars($course['short_description']); ?></textarea>
                <small>Brief description for course listings and previews</small>
            </div>
            
            <div class="form-group">
                <label for="long_description">Long Description</label>
                <textarea id="long_description" name="long_description" rows="8"><?php echo htmlspecialchars($course['long_description']); ?></textarea>
                <small>Detailed course information for students</small>
            </div>
        </div>
        
        <!-- Course Details -->
        <div class="form-section">
            <h3>Course Details</h3>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="duration">Duration</label>
                    <input type="text" id="duration" name="duration" value="<?php echo htmlspecialchars($course['duration']); ?>" placeholder="e.g., 6 weeks, 3 months">
                    <small>Course length (e.g., "6 weeks", "3 months")</small>
                </div>
                
                <div class="form-group">
                    <label for="price">Price</label>
                    <input type="number" id="price" name="price" value="<?php echo htmlspecialchars($course['price']); ?>" step="0.01" min="0">
                    <small>Course fee (0 for free courses)</small>
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="language">Language</label>
                    <input type="text" id="language" name="language" value="<?php echo htmlspecialchars($course['language']); ?>" placeholder="e.g., English, French">
                </div>
                
                <div class="form-group">
                    <label for="max_students">Max Students</label>
                    <input type="number" id="max_students" name="max_students" value="<?php echo htmlspecialchars($course['max_students']); ?>" min="1">
                    <small>Maximum enrollment capacity</small>
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="start_date">Start Date</label>
                    <input type="date" id="start_date" name="start_date" value="<?php echo htmlspecialchars($course['start_date']); ?>">
                </div>
                
                <div class="form-group">
                    <label for="end_date">End Date</label>
                    <input type="date" id="end_date" name="end_date" value="<?php echo htmlspecialchars($course['end_date']); ?>">
                </div>
            </div>
            
            <div class="form-group">
                <label for="requirements">Requirements</label>
                <textarea id="requirements" name="requirements" rows="4"><?php echo htmlspecialchars($course['requirements']); ?></textarea>
                <small>Prerequisites and requirements</small>
            </div>
            
            <div class="form-group">
                <label for="objectives">Learning Objectives</label>
                <textarea id="objectives" name="objectives" rows="4"><?php echo htmlspecialchars($course['objectives']); ?></textarea>
                <small>What students will learn</small>
            </div>
            
            <div class="form-group">
                <label for="curriculum">Curriculum</label>
                <textarea id="curriculum" name="curriculum" rows="6"><?php echo htmlspecialchars($course['curriculum']); ?></textarea>
                <small>Course syllabus and modules</small>
            </div>
        </div>
        
        <!-- Instructor Information -->
        <div class="form-section">
            <h3>Instructor Information</h3>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="instructor_name">Instructor Name</label>
                    <input type="text" id="instructor_name" name="instructor_name" value="<?php echo htmlspecialchars($course['instructor_name']); ?>" placeholder="Full instructor name">
                </div>
                
                <div class="form-group">
                    <label for="instructor_bio">Instructor Bio</label>
                    <textarea id="instructor_bio" name="instructor_bio" rows="3"><?php echo htmlspecialchars($course['instructor_bio']); ?></textarea>
                    <small>Instructor background and expertise</small>
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="video_intro_url">Intro Video URL</label>
                    <input type="url" id="video_intro_url" name="video_intro_url" value="<?php echo htmlspecialchars($course['video_intro_url']); ?>" placeholder="https://example.com/intro-video.mp4">
                    <small>YouTube or hosted video URL</small>
                </div>
            </div>
        </div>
        
        <!-- Media -->
        <div class="form-section">
            <h3>Course Media</h3>
            
            <div class="current-images">
                <div class="image-preview">
                    <h4>Current Cover Image</h4>
                    <div id="coverImagePreview">
                        <?php if ($course['cover_image']): ?>
                            <img src="../<?php echo $course['cover_image']; ?>" alt="Current cover image">
                        <?php else: ?>
                            <div style="color: #94a3b8; padding: 2rem;">
                                <i class="fas fa-image" style="font-size: 2rem;"></i>
                                <p>No cover image uploaded</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                
                <div class="image-preview">
                    <h4>Current Instructor Photo</h4>
                    <div id="instructorImagePreview">
                        <?php if ($course['instructor_image']): ?>
                            <img src="../<?php echo $course['instructor_image']; ?>" alt="Current instructor photo">
                        <?php else: ?>
                            <div style="color: #94a3b8; padding: 2rem;">
                                <i class="fas fa-user-tie" style="font-size: 2rem;"></i>
                                <p>No instructor photo uploaded</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="cover_image">Course Cover Image</label>
                    <div class="image-upload">
                        <input type="file" id="cover_image" name="cover_image" accept="image/*">
                        <label for="cover_image" class="image-upload-label">
                            <i class="fas fa-upload"></i> Choose New Cover Image
                        </label>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="instructor_image">Instructor Photo</label>
                    <div class="image-upload">
                        <input type="file" id="instructor_image" name="instructor_image" accept="image/*">
                        <label for="instructor_image" class="image-upload-label">
                            <i class="fas fa-camera"></i> Choose New Instructor Photo
                        </label>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Course Settings -->
        <div class="form-section">
            <h3>Course Settings</h3>
            
            <div class="form-group">
                <label>
                    <input type="checkbox" name="featured" value="1" <?php echo $course['featured'] ? 'checked' : ''; ?>>
                    Feature this course on homepage
                </label>
                <small>Featured courses appear prominently on the courses page</small>
            </div>
        </div>
        
        <!-- SEO Fields -->
        <div class="form-section">
            <h3>SEO Information</h3>
            
            <div class="form-group">
                <label for="meta_title">Meta Title</label>
                <input type="text" id="meta_title" name="meta_title" value="<?php echo htmlspecialchars($course['meta_title']); ?>" placeholder="SEO title (60 chars max)">
                <small>Title for search engines and browser tabs</small>
            </div>
            
            <div class="form-group">
                <label for="meta_description">Meta Description</label>
                <textarea id="meta_description" name="meta_description" rows="3"><?php echo htmlspecialchars($course['meta_description']); ?></textarea>
                <small>Description for search engines (160 chars max)</small>
            </div>
            
            <div class="form-group">
                <label for="meta_keywords">Meta Keywords</label>
                <input type="text" id="meta_keywords" name="meta_keywords" value="<?php echo htmlspecialchars($course['meta_keywords']); ?>" placeholder="keyword1, keyword2, keyword3">
                <small>Comma-separated keywords for SEO</small>
            </div>
        </div>
        
        <!-- Course Statistics -->
        <div class="course-stats">
            <div class="stat-box">
                <span class="stat-number"><?php echo $course['current_enrollments']; ?></span>
                <span class="stat-label">Current Enrollments</span>
            </div>
            <div class="stat-box">
                <span class="stat-number"><?php echo $course['pending_applications']; ?></span>
                <span class="stat-label">Pending Applications</span>
            </div>
            <div class="stat-box">
                <span class="stat-number"><?php echo $course['start_date'] ? date('M j, Y', strtotime($course['start_date'])) : 'Not set'; ?></span>
                <span class="stat-label">Start Date</span>
            </div>
            <div class="stat-box">
                <span class="stat-number"><?php echo $course['end_date'] ? date('M j, Y', strtotime($course['end_date'])) : 'Not set'; ?></span>
                <span class="stat-label">End Date</span>
            </div>
        </div>
        
        <div class="form-actions">
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save"></i>
                Update Course
            </button>
            
            <a href="index.php" class="btn btn-secondary">
                <i class="fas fa-times"></i>
                Cancel
            </a>
        </div>
    </form>
</div>

<?php require_once '../includes/footer.php'; ?>
