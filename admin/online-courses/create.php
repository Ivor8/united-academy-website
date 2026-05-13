<?php
$pageTitle = 'Create Online Course';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

if (!hasPermission('create_online_courses')) {
    header('Location: index.php');
    exit();
}

$pdo = getDB();
$error = '';
$success = '';

// Function to create online_courses table if it doesn't exist
function createOnlineCoursesTable($pdo) {
    $createTableSQL = "
        CREATE TABLE IF NOT EXISTS `online_courses` (
          `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
          `title` varchar(255) NOT NULL,
          `slug` varchar(255) NOT NULL,
          `short_description` text DEFAULT NULL,
          `long_description` longtext DEFAULT NULL,
          `cover_image` varchar(500) DEFAULT NULL,
          `category` enum('health','it','business','languages','professional') NOT NULL DEFAULT 'health',
          `level` enum('beginner','intermediate','advanced') DEFAULT 'beginner',
          `duration` varchar(50) DEFAULT NULL,
          `price` decimal(10,2) DEFAULT 0.00,
          `currency` varchar(3) DEFAULT 'XAF',
          `language` varchar(50) DEFAULT NULL,
          `requirements` longtext DEFAULT NULL,
          `objectives` longtext DEFAULT NULL,
          `curriculum` longtext DEFAULT NULL,
          `instructor_name` varchar(100) DEFAULT NULL,
          `instructor_bio` text DEFAULT NULL,
          `instructor_image` varchar(500) DEFAULT NULL,
          `video_intro_url` varchar(500) DEFAULT NULL,
          `status` enum('draft','published','archived') DEFAULT 'draft',
          `featured` tinyint(1) DEFAULT 0,
          `start_date` date DEFAULT NULL,
          `end_date` date DEFAULT NULL,
          `max_students` int(10) UNSIGNED DEFAULT NULL,
          `current_enrollments` int(10) UNSIGNED DEFAULT 0,
          `order_position` int(11) DEFAULT 0,
          `meta_title` varchar(255) DEFAULT NULL,
          `meta_description` text DEFAULT NULL,
          `meta_keywords` varchar(500) DEFAULT NULL,
          `created_by` int(10) UNSIGNED DEFAULT NULL,
          `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
          `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
          PRIMARY KEY (`id`),
          UNIQUE KEY `slug` (`slug`),
          KEY `idx_category` (`category`),
          KEY `idx_status` (`status`),
          KEY `idx_featured` (`featured`),
          KEY `idx_created_by` (`created_by`),
          KEY `idx_order_position` (`order_position`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ";
    $pdo->exec($createTableSQL);
}

// =========================
// HANDLE FORM SUBMISSION
// =========================

$debugMessages = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $debugMessages[] = "Form submitted successfully.";
    $debugMessages[] = "POST request detected.";

    try {

        // Create table if not exists
        createOnlineCoursesTable($pdo);
        $debugMessages[] = "Database table checked.";

        // =========================
        // GET FORM DATA
        // =========================

        $courseId = !empty($_POST['course_id']) ? intval($_POST['course_id']) : 0;

        $title = trim($_POST['title'] ?? '');
        $slug = createSlug($title);

        $shortDescription = trim($_POST['short_description'] ?? '');
        $longDescription = trim($_POST['long_description'] ?? '');

        $category = trim($_POST['category'] ?? '');
        $level = trim($_POST['level'] ?? 'beginner');

        $duration = trim($_POST['duration'] ?? '');

        $price = !empty($_POST['price']) ? floatval($_POST['price']) : 0;

        $currency = trim($_POST['currency'] ?? 'XAF');

        $language = trim($_POST['language'] ?? '');

        $requirements = trim($_POST['requirements'] ?? '');
        $objectives = trim($_POST['objectives'] ?? '');
        $curriculum = trim($_POST['curriculum'] ?? '');

        $instructorName = trim($_POST['instructor_name'] ?? '');
        $instructorBio = trim($_POST['instructor_bio'] ?? '');

        $videoIntroUrl = trim($_POST['video_intro_url'] ?? '');

        $startDate = !empty($_POST['start_date']) ? $_POST['start_date'] : null;
        $endDate = !empty($_POST['end_date']) ? $_POST['end_date'] : null;

        $maxStudents = !empty($_POST['max_students']) ? intval($_POST['max_students']) : null;

        $metaTitle = trim($_POST['meta_title'] ?? '');
        $metaDescription = trim($_POST['meta_description'] ?? '');
        $metaKeywords = trim($_POST['meta_keywords'] ?? '');

        $featured = isset($_POST['featured']) ? 1 : 0;

        $debugMessages[] = "Form fields processed.";

        // =========================
        // VALIDATION
        // =========================

        if (empty($title)) {
            throw new Exception("Course title is required.");
        }

        if (empty($shortDescription)) {
            throw new Exception("Short description is required.");
        }

        if (empty($category)) {
            throw new Exception("Category is required.");
        }

        $debugMessages[] = "Validation passed.";

        // =========================
        // HANDLE COVER IMAGE
        // =========================

        $coverImage = '';

        if (
            isset($_FILES['cover_image']) &&
            $_FILES['cover_image']['error'] === 0
        ) {

            $debugMessages[] = "Uploading cover image...";

            $uploaded = uploadFile($_FILES['cover_image'], 'courses');

            if ($uploaded) {
                $coverImage = $uploaded;
                $debugMessages[] = "Cover image uploaded.";
            } else {
                $debugMessages[] = "Cover image upload failed.";
            }
        }

        // =========================
        // HANDLE INSTRUCTOR IMAGE
        // =========================

        $instructorImage = '';

        if (
            isset($_FILES['instructor_image']) &&
            $_FILES['instructor_image']['error'] === 0
        ) {

            $debugMessages[] = "Uploading instructor image...";

            $uploaded = uploadFile($_FILES['instructor_image'], 'instructors');

            if ($uploaded) {
                $instructorImage = $uploaded;
                $debugMessages[] = "Instructor image uploaded.";
            } else {
                $debugMessages[] = "Instructor image upload failed.";
            }
        }

        // =========================
        // CHECK DUPLICATE SLUG
        // =========================

        $check = $pdo->prepare("
            SELECT id FROM online_courses
            WHERE slug = ?
            AND id != ?
        ");

        $check->execute([$slug, $courseId]);

        if ($check->fetch()) {
            throw new Exception("A course with this title already exists.");
        }

        $debugMessages[] = "Slug validation passed.";

        // =========================
        // UPDATE COURSE
        // =========================

        if ($courseId > 0) {

            $debugMessages[] = "Updating existing course ID: " . $courseId;

            $stmt = $pdo->prepare("
                UPDATE online_courses SET
                    title = ?,
                    slug = ?,
                    short_description = ?,
                    long_description = ?,
                    cover_image = ?,
                    category = ?,
                    level = ?,
                    duration = ?,
                    price = ?,
                    currency = ?,
                    language = ?,
                    requirements = ?,
                    objectives = ?,
                    curriculum = ?,
                    instructor_name = ?,
                    instructor_bio = ?,
                    instructor_image = ?,
                    video_intro_url = ?,
                    start_date = ?,
                    end_date = ?,
                    max_students = ?,
                    meta_title = ?,
                    meta_description = ?,
                    meta_keywords = ?,
                    featured = ?,
                    updated_at = NOW()
                WHERE id = ?
            ");

            $result = $stmt->execute([
                $title,
                $slug,
                $shortDescription,
                $longDescription,
                $coverImage,
                $category,
                $level,
                $duration,
                $price,
                $currency,
                $language,
                $requirements,
                $objectives,
                $curriculum,
                $instructorName,
                $instructorBio,
                $instructorImage,
                $videoIntroUrl,
                $startDate,
                $endDate,
                $maxStudents,
                $metaTitle,
                $metaDescription,
                $metaKeywords,
                $featured,
                $courseId
            ]);

            if (!$result) {
                throw new Exception("Update query failed.");
            }

            $success = "Course updated successfully.";

            $debugMessages[] = "UPDATE successful.";

        } else {

            // =========================
            // CREATE COURSE
            // =========================

            $debugMessages[] = "Creating new course.";

            $stmt = $pdo->prepare("
                INSERT INTO online_courses (
                    title,
                    slug,
                    short_description,
                    long_description,
                    cover_image,
                    category,
                    level,
                    duration,
                    price,
                    currency,
                    language,
                    requirements,
                    objectives,
                    curriculum,
                    instructor_name,
                    instructor_bio,
                    instructor_image,
                    video_intro_url,
                    start_date,
                    end_date,
                    max_students,
                    meta_title,
                    meta_description,
                    meta_keywords,
                    featured,
                    created_by,
                    created_at,
                    updated_at
                ) VALUES (
                    ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW()
                )
            ");

            $result = $stmt->execute([
                $title,
                $slug,
                $shortDescription,
                $longDescription,
                $coverImage,
                $category,
                $level,
                $duration,
                $price,
                $currency,
                $language,
                $requirements,
                $objectives,
                $curriculum,
                $instructorName,
                $instructorBio,
                $instructorImage,
                $videoIntroUrl,
                $startDate,
                $endDate,
                $maxStudents,
                $metaTitle,
                $metaDescription,
                $metaKeywords,
                $featured,
                $_SESSION['user_id']
            ]);

            if (!$result) {

                $errorInfo = $stmt->errorInfo();

                throw new Exception(
                    "Insert failed: " . $errorInfo[2]
                );
            }

            $newId = $pdo->lastInsertId();

            $success = "Course created successfully. ID: " . $newId;

            $debugMessages[] = "INSERT successful.";
            $debugMessages[] = "Inserted course ID: " . $newId;
        }

    } catch (Exception $e) {

        $error = $e->getMessage();

        $debugMessages[] = "ERROR: " . $e->getMessage();
    }
}
// Include header after form processing
require_once '../includes/header.php';

// Get course data for editing
$course = null;
if (isset($_GET['id'])) {
    $courseId = intval($_GET['id']);
    $stmt = $pdo->prepare("SELECT * FROM online_courses WHERE id = ?");
    $stmt->execute([$courseId]);
    $course = $stmt->fetch();
}

$extraCss = '<style>
    .course-form {
        max-width: 1000px;
        margin: 0 auto;
        padding: 2rem;
    }
    
    .form-row {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 1.5rem;
        margin-bottom: 1.5rem;
    }
    
    .form-group {
        margin-bottom: 1.5rem;
    }
    
    .form-group label {
        display: block;
        font-weight: 600;
        margin-bottom: 0.5rem;
        color: var(--dark);
    }
    
    .form-group input,
    .form-group select,
    .form-group textarea {
        width: 100%;
        padding: 0.75rem;
        border: 1px solid #e5e7eb;
        border-radius: 8px;
        font-family: inherit;
        transition: border-color 0.3s ease;
    }
    
    .form-group input:focus,
    .form-group select:focus,
    .form-group textarea:focus {
        outline: none;
        border-color: var(--blue);
        box-shadow: 0 0 0 3px rgba(30,100,200,0.1);
    }
    
    .form-group textarea {
        resize: vertical;
        min-height: 100px;
    }
    
    .image-upload {
        border: 2px dashed #e5e7eb;
        border-radius: 8px;
        padding: 2rem;
        text-align: center;
        background: var(--light);
        margin-bottom: 1rem;
    }
    
    .image-upload input[type="file"] {
        display: none;
    }
    
    .image-upload-label {
        display: inline-block;
        padding: 0.75rem 1.5rem;
        background: var(--blue);
        color: white;
        border-radius: 8px;
        cursor: pointer;
        transition: background 0.3s ease;
    }
    
    .image-upload-label:hover {
        background: var(--dark);
    }
    
    .current-image {
        max-width: 200px;
        margin-top: 1rem;
        border-radius: 8px;
        overflow: hidden;
    }
    
    .current-image img {
        width: 100%;
        height: auto;
        display: block;
    }
    
    .form-actions {
        display: flex;
        gap: 1rem;
        margin-top: 2rem;
    }
    
    .btn {
        padding: 0.75rem 2rem;
        border: none;
        border-radius: 8px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s ease;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
    }
    
    .btn-primary {
        background: var(--blue);
        color: white;
    }
    
    .btn-primary:hover {
        background: var(--dark);
        transform: translateY(-2px);
    }
    
    .btn-secondary {
        background: var(--gray);
        color: white;
    }
    
    .btn-secondary:hover {
        background: var(--dark);
    }
    
    .required {
        color: var(--red);
    }
    
    .char-counter {
        font-size: 0.8rem;
        color: #64748b;
        margin-left: 0.5rem;
    }
    
    @media (max-width: 768px) {
        .form-row {
            grid-template-columns: 1fr;
            gap: 1rem;
        }
        
        .form-actions {
            flex-direction: column;
        }
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
                    .replace(/-+/g, "-")
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
        <!-- remove this after -->
         <?php if (!empty($debugMessages)): ?>

    <div style="
        background:#0f172a;
        color:#e2e8f0;
        padding:15px;
        border-radius:8px;
        margin-bottom:20px;
        font-family:monospace;
        font-size:14px;
    ">

        <h3 style="margin-top:0;color:#38bdf8;">
            Debug Console
        </h3>

        <?php foreach ($debugMessages as $msg): ?>

            <div style="padding:5px 0;border-bottom:1px solid #334155;">
                → <?php echo htmlspecialchars($msg); ?>
            </div>

        <?php endforeach; ?>

    </div>

<?php endif; ?>
         <!-- remove this after -->
        <h1><i class="fas fa-plus"></i> <?php echo $course ? 'Edit' : 'Create'; ?> Online Course</h1>
        <a href="index.php" class="view-all">Back to Courses</a>
    </div>
    
    <?php if ($error): ?>
        <div class="alert alert-error"><?php echo $error; ?></div>
    <?php endif; ?>
    
    <?php if ($success): ?>
        <div class="alert alert-success"><?php echo $success; ?></div>
    <?php endif; ?>
    
    <form method="POST" enctype="multipart/form-data" class="course-form animate-slide-up">
    <?php if ($course): ?>
        <input type="hidden" name="course_id" value="<?php echo $course['id']; ?>">
    <?php endif; ?>
        
        <div class="form-row">
            <div class="form-group">
                <label for="title">Course Title <span class="required">*</span></label>
                <input type="text" id="title" name="title" value="<?php echo htmlspecialchars($course['title'] ?? ''); ?>" required>
                <span class="char-counter"><span id="titleCounter">50</span> / 50</span>
            </div>
            
            <div class="form-group">
                <label for="slug">URL Slug</label>
                <input type="text" id="slug" name="slug" value="<?php echo htmlspecialchars($course['slug'] ?? ''); ?>" readonly>
            </div>
        </div>
        
        <div class="form-row">
            <div class="form-group">
                <label for="category">Category <span class="required">*</span></label>
                <select id="category" name="category" required>
                    <option value="">Select Category</option>
                    <option value="health" <?php echo ($course['category'] ?? '') === 'health' ? 'selected' : ''; ?>>Health</option>
                    <option value="it" <?php echo ($course['category'] ?? '') === 'it' ? 'selected' : ''; ?>>IT</option>
                    <option value="business" <?php echo ($course['category'] ?? '') === 'business' ? 'selected' : ''; ?>>Business</option>
                    <option value="languages" <?php echo ($course['category'] ?? '') === 'languages' ? 'selected' : ''; ?>>Languages</option>
                    <option value="professional" <?php echo ($course['category'] ?? '') === 'professional' ? 'selected' : ''; ?>>Professional</option>
                </select>
            </div>
            
            <div class="form-group">
                <label for="level">Level <span class="required">*</span></label>
                <select id="level" name="level" required>
                    <option value="">Select Level</option>
                    <option value="beginner" <?php echo ($course['level'] ?? '') === 'beginner' ? 'selected' : ''; ?>>Beginner</option>
                    <option value="intermediate" <?php echo ($course['level'] ?? '') === 'intermediate' ? 'selected' : ''; ?>>Intermediate</option>
                    <option value="advanced" <?php echo ($course['level'] ?? '') === 'advanced' ? 'selected' : ''; ?>>Advanced</option>
                </select>
            </div>
        </div>
        
        <div class="form-group">
            <label for="short_description">Short Description <span class="required">*</span></label>
            <textarea id="short_description" name="short_description" rows="3" required><?php echo htmlspecialchars($course['short_description'] ?? ''); ?></textarea>
            <small style="color: #64748b; font-size: 0.8rem;">Brief description for course listings and previews</small>
        </div>
        
        <div class="form-group">
            <label for="long_description">Long Description</label>
            <textarea id="long_description" name="long_description" rows="8"><?php echo htmlspecialchars($course['long_description'] ?? ''); ?></textarea>
            <small style="color: #64748b; font-size: 0.8rem;">Detailed course information for students</small>
        </div>
        
        <div class="form-row">
            <div class="form-group">
                <label for="duration">Duration</label>
                <input type="text" id="duration" name="duration" value="<?php echo htmlspecialchars($course['duration'] ?? ''); ?>" placeholder="e.g., 6 weeks, 3 months">
                <small style="color: #64748b; font-size: 0.8rem;">Course length (e.g., "6 weeks", "3 months")</small>
            </div>
            
            <div class="form-group">
                <label for="price">Price</label>
                <input type="number" id="price" name="price" value="<?php echo htmlspecialchars($course['price'] ?? ''); ?>" step="0.01" min="0">
                <small style="color: #64748b; font-size: 0.8rem;">Course fee (0 for free courses)</small>
            </div>
        </div>
        
        <div class="form-row">
            <div class="form-group">
                <label for="language">Language</label>
                <input type="text" id="language" name="language" value="<?php echo htmlspecialchars($course['language'] ?? ''); ?>" placeholder="e.g., English, French">
            </div>
            
            <div class="form-group">
                <label for="max_students">Max Students</label>
                <input type="number" id="max_students" name="max_students" value="<?php echo htmlspecialchars($course['max_students'] ?? ''); ?>" min="1">
                <small style="color: #64748b; font-size: 0.8rem;">Maximum enrollment capacity</small>
            </div>
        </div>
        
        <div class="form-group">
            <label for="requirements">Requirements</label>
            <textarea id="requirements" name="requirements" rows="4"><?php echo htmlspecialchars($course['requirements'] ?? ''); ?></textarea>
            <small style="color: #64748b; font-size: 0.8rem;">Prerequisites and requirements</small>
        </div>
        
        <div class="form-group">
            <label for="objectives">Learning Objectives</label>
            <textarea id="objectives" name="objectives" rows="4"><?php echo htmlspecialchars($course['objectives'] ?? ''); ?></textarea>
            <small style="color: #64748b; font-size: 0.8rem;">What students will learn</small>
        </div>
        
        <div class="form-group">
            <label for="curriculum">Curriculum</label>
            <textarea id="curriculum" name="curriculum" rows="6"><?php echo htmlspecialchars($course['curriculum'] ?? ''); ?></textarea>
            <small style="color: #64748b; font-size: 0.8rem;">Course syllabus and modules</small>
        </div>
        
        <div class="form-group">
            <label for="video_intro_url">Intro Video URL</label>
            <input type="url" id="video_intro_url" name="video_intro_url" value="<?php echo htmlspecialchars($course['video_intro_url'] ?? ''); ?>" placeholder="https://example.com/intro-video.mp4">
            <small style="color: #64748b; font-size: 0.8rem;">YouTube or hosted video URL</small>
        </div>
        
        <div class="form-row">
            <div class="form-group">
                <label for="instructor_name">Instructor Name</label>
                <input type="text" id="instructor_name" name="instructor_name" value="<?php echo htmlspecialchars($course['instructor_name'] ?? ''); ?>" placeholder="Full instructor name">
            </div>
            
            <div class="form-group">
                <label for="instructor_bio">Instructor Bio</label>
                <textarea id="instructor_bio" name="instructor_bio" rows="3"><?php echo htmlspecialchars($course['instructor_bio'] ?? ''); ?></textarea>
                <small style="color: #64748b; font-size: 0.8rem;">Instructor background and expertise</small>
            </div>
        </div>
        
        <div class="form-row">
            <div class="form-group">
                <label for="cover_image">Course Cover Image</label>
                <div class="image-upload">
                    <input type="file" id="cover_image" name="cover_image" accept="image/*">
                    <label for="cover_image" class="image-upload-label">
                        <i class="fas fa-upload"></i> Choose Cover Image
                    </label>
                    <div id="coverImagePreview" class="current-image">
                        <div style="color: #94a3b8; padding: 2rem;">
                            <i class="fas fa-image" style="font-size: 3rem; opacity: 0.3;"></i>
                            <p>No cover image uploaded</p>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="form-group">
                <label for="instructor_image">Instructor Photo</label>
                <div class="image-upload">
                    <input type="file" id="instructor_image" name="instructor_image" accept="image/*">
                    <label for="instructor_image" class="image-upload-label">
                        <i class="fas fa-camera"></i> Choose Instructor Photo
                    </label>
                    <div id="instructorImagePreview" class="current-image">
                        <div style="color: #94a3b8; padding: 2rem;">
                            <i class="fas fa-user-tie" style="font-size: 2rem;"></i>
                            <p>No instructor photo uploaded</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="form-row">
            <div class="form-group">
                <label for="start_date">Start Date</label>
                <input type="date" id="start_date" name="start_date" value="<?php echo htmlspecialchars($course['start_date'] ?? ''); ?>">
            </div>
            
            <div class="form-group">
                <label for="end_date">End Date</label>
                <input type="date" id="end_date" name="end_date" value="<?php echo htmlspecialchars($course['end_date'] ?? ''); ?>">
            </div>
        </div>
        
        <div class="form-group">
            <label>
                <input type="checkbox" name="featured" value="1" <?php echo ($course['featured'] ?? 0) ? 'checked' : ''; ?>>
                Feature this course on homepage
            </label>
            <small style="color: #64748b; font-size: 0.8rem;">Featured courses appear prominently on the courses page</small>
        </div>
        
        <!-- SEO Fields -->
        <h3 style="margin: 2rem 0 1rem 0; padding-bottom: 0.5rem; border-bottom: 1px solid #e5e7eb;">SEO Information</h3>
        
        <div class="form-group">
            <label for="meta_title">Meta Title</label>
            <input type="text" id="meta_title" name="meta_title" value="<?php echo htmlspecialchars($course['meta_title'] ?? ''); ?>" placeholder="SEO title (60 chars max)">
            <small style="color: #64748b; font-size: 0.8rem;">Title for search engines and browser tabs</small>
        </div>
        
        <div class="form-group">
            <label for="meta_description">Meta Description</label>
            <textarea id="meta_description" name="meta_description" rows="3"><?php echo htmlspecialchars($course['meta_description'] ?? ''); ?></textarea>
            <small style="color: #64748b; font-size: 0.8rem;">Description for search engines (160 chars max)</small>
        </div>
        
        <div class="form-group">
            <label for="meta_keywords">Meta Keywords</label>
            <input type="text" id="meta_keywords" name="meta_keywords" value="<?php echo htmlspecialchars($course['meta_keywords'] ?? ''); ?>" placeholder="keyword1, keyword2, keyword3">
            <small style="color: #64748b; font-size: 0.8rem;">Comma-separated keywords for SEO</small>
        </div>
        
        <div class="form-actions">
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save"></i>
                <?php echo $course ? 'Update' : 'Create'; ?> Course
            </button>
            
            <a href="index.php" class="btn btn-secondary">
                <i class="fas fa-times"></i>
                Cancel
            </a>
        </div>
    </form>
</div>

<?php require_once '../includes/footer.php'; ?>
