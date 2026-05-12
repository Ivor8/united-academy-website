<?php
require_once 'admin/includes/config.php';

// Get database connection
$pdo = getDB();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $courseId = intval($_POST['course_id']);
    $firstName = sanitize($_POST['first_name']);
    $lastName = sanitize($_POST['last_name']);
    $email = sanitize($_POST['email']);
    $phone = sanitize($_POST['phone']);
    $dateOfBirth = sanitize($_POST['date_of_birth']);
    $gender = sanitize($_POST['gender']);
    $nationality = sanitize($_POST['nationality']);
    $currentEducation = sanitize($_POST['current_education']);
    $workExperience = sanitize($_POST['work_experience']);
    $motivation = sanitize($_POST['motivation']);
    $howHearAbout = sanitize($_POST['how_hear_about']);
    $hasComputer = isset($_POST['has_computer']) ? 1 : 0;
    $needsFinancialAid = isset($_POST['needs_financial_aid']) ? 1 : 0;
    $additionalInfo = sanitize($_POST['additional_info']);
    
    // Validate required fields
    $errors = [];
    
    if (empty($firstName)) $errors[] = 'First name is required';
    if (empty($lastName)) $errors[] = 'Last name is required';
    if (empty($email)) $errors[] = 'Email is required';
    if (empty($motivation)) $errors[] = 'Motivation is required';
    
    // Validate email format
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Please enter a valid email address';
    }
    
    // Check if course exists
    $courseStmt = $pdo->prepare("SELECT title FROM online_courses WHERE id = ? AND status = 'published'");
    $courseStmt->execute([$courseId]);
    $course = $courseStmt->fetch();
    
    if (!$course) {
        $errors[] = 'Invalid course selected';
    }
    
    // Check for duplicate application
    $duplicateStmt = $pdo->prepare("SELECT id FROM course_applications WHERE course_id = ? AND email = ? AND status != 'rejected'");
    $duplicateStmt->execute([$courseId, $email]);
    if ($duplicateStmt->fetch()) {
        $errors[] = 'You have already applied for this course';
    }
    
    if (empty($errors)) {
        try {
            // Insert application
            $stmt = $pdo->prepare("
                INSERT INTO course_applications (
                    course_id, first_name, last_name, email, phone, date_of_birth, 
                    gender, nationality, current_education, work_experience, motivation, 
                    how_hear_about, has_computer, needs_financial_aid, additional_info, 
                    status, submitted_at
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending', NOW())
            ");
            
            $params = [
                $courseId, $firstName, $lastName, $email, $phone, $dateOfBirth,
                $gender, $nationality, $currentEducation, $workExperience, $motivation,
                $howHearAbout, $hasComputer, $needsFinancialAid, $additionalInfo
            ];
            
            if ($stmt->execute($params)) {
                // Get WhatsApp number for notification
                $whatsappStmt = $pdo->prepare("SELECT setting_value FROM settings WHERE setting_key = 'whatsapp_number'");
                $whatsappStmt->execute();
                $whatsappNumber = $whatsappStmt->fetch()['setting_value'];
                
                // Send notification email to admin
                $to = 'unitedacademyuard@gmail.com';
                $subject = 'New Course Application: ' . $course['title'];
                $message = "
                <h2>New Course Application</h2>
                <p><strong>Course:</strong> {$course['title']}</p>
                <p><strong>Applicant:</strong> {$firstName} {$lastName}</p>
                <p><strong>Email:</strong> {$email}</p>
                <p><strong>Phone:</strong> {$phone}</p>
                <p><strong>Motivation:</strong> {$motivation}</p>
                <p><strong>Submitted:</strong> " . date('Y-m-d H:i:s') . "</p>
                <hr>
                <p>Please review this application in the admin dashboard.</p>
                ";
                
                $headers = "MIME-Version: 1.0" . "\r\n";
                $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
                $headers .= "From: {$email}" . "\r\n";
                
                mail($to, $subject, $message, $headers);
                
                // Send confirmation email to applicant
                $confirmSubject = 'Application Received - ' . $course['title'];
                $confirmMessage = "
                <h2>Thank You for Your Application!</h2>
                <p>Dear {$firstName} {$lastName},</p>
                <p>We have received your application for the course <strong>{$course['title']}</strong>.</p>
                <p>Our team will review your application and contact you within 2-3 business days.</p>
                <p>If you have any questions, feel free to contact us via WhatsApp at <a href='https://wa.me/{$whatsappNumber}'>{$whatsappNumber}</a>.</p>
                <p>Best regards,<br>UNITED ACADEMY-UARD Team</p>
                ";
                
                $confirmHeaders = "MIME-Version: 1.0" . "\r\n";
                $confirmHeaders .= "Content-type:text/html;charset=UTF-8" . "\r\n";
                $confirmHeaders .= "From: unitedacademyuard@gmail.com" . "\r\n";
                
                mail($email, $confirmSubject, $confirmMessage, $confirmHeaders);
                
                // Redirect to success page
                header('Location: application-success.php?course=' . urlencode($course['title']));
                exit();
            } else {
                $error = 'Failed to submit application. Please try again.';
            }
        } catch (Exception $e) {
            $error = 'An error occurred. Please try again later.';
            error_log('Application error: ' . $e->getMessage());
        }
    } else {
        $error = implode('<br>', $errors);
    }
} else {
    // Redirect if not POST request
    header('Location: online-courses.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=yes">
    <title>Application Error | <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .error-container {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #f8fafc;
            padding: 2rem;
        }
        
        .error-content {
            background: white;
            padding: 3rem;
            border-radius: 20px;
            text-align: center;
            max-width: 500px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        }
        
        .error-icon {
            font-size: 4rem;
            color: #dc2626;
            margin-bottom: 1.5rem;
        }
        
        .error-title {
            font-size: 1.8rem;
            font-weight: 700;
            color: var(--dark);
            margin-bottom: 1rem;
        }
        
        .error-message {
            color: #64748b;
            line-height: 1.6;
            margin-bottom: 2rem;
        }
        
        .btn-back {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            background: var(--blue);
            color: white;
            padding: 0.75rem 1.5rem;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        
        .btn-back:hover {
            background: var(--dark);
            transform: translateY(-2px);
        }
    </style>
</head>
<body>
    <div class="error-container">
        <div class="error-content">
            <div class="error-icon">
                <i class="fas fa-exclamation-triangle"></i>
            </div>
            
            <h1 class="error-title">Application Error</h1>
            
            <?php if (isset($error)): ?>
                <div class="error-message">
                    <?php echo $error; ?>
                </div>
            <?php else: ?>
                <div class="error-message">
                    An error occurred while processing your application. Please try again later.
                </div>
            <?php endif; ?>
            
            <a href="javascript:history.back()" class="btn-back">
                <i class="fas fa-arrow-left"></i>
                Go Back
            </a>
        </div>
    </div>
</body>
</html>
