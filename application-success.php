<?php
require_once 'admin/includes/config.php';

$courseName = isset($_GET['course']) ? sanitize($_GET['course']) : 'Course';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=yes">
    <title>Application Successful | <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .success-container {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #f8fafc, #e5e7eb);
            padding: 2rem;
        }
        
        .success-content {
            background: white;
            padding: 4rem 3rem;
            border-radius: 20px;
            text-align: center;
            max-width: 600px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
            position: relative;
            overflow: hidden;
        }
        
        .success-content::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 5px;
            background: linear-gradient(90deg, #1E64C8, #2E7D32);
        }
        
        .success-icon {
            font-size: 4rem;
            color: #2E7D32;
            margin-bottom: 2rem;
            animation: bounce 2s infinite;
        }
        
        @keyframes bounce {
            0%, 20%, 50%, 80%, 100% {
                transform: translateY(0);
            }
            40% {
                transform: translateY(-20px);
            }
            60% {
                transform: translateY(-10px);
            }
        }
        
        .success-title {
            font-size: 2rem;
            font-weight: 700;
            color: var(--dark);
            margin-bottom: 1rem;
        }
        
        .success-message {
            color: #64748b;
            line-height: 1.6;
            margin-bottom: 2rem;
            font-size: 1.1rem;
        }
        
        .course-info {
            background: var(--light);
            padding: 1.5rem;
            border-radius: 12px;
            margin-bottom: 2rem;
            border-left: 4px solid #1E64C8;
        }
        
        .course-info h3 {
            color: #1E64C8;
            margin-bottom: 0.5rem;
            font-size: 1.1rem;
        }
        
        .next-steps {
            background: #f0f9ff;
            padding: 1.5rem;
            border-radius: 12px;
            margin-bottom: 2rem;
            border-left: 4px solid #0ea5e9;
        }
        
        .next-steps h3 {
            color: #0ea5e9;
            margin-bottom: 1rem;
            font-size: 1.1rem;
        }
        
        .next-steps ul {
            text-align: left;
            color: #64748b;
            line-height: 1.6;
        }
        
        .next-steps li {
            margin-bottom: 0.5rem;
        }
        
        .action-buttons {
            display: flex;
            gap: 1rem;
            justify-content: center;
            flex-wrap: wrap;
        }
        
        .btn {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.75rem 1.5rem;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
            border: none;
            cursor: pointer;
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
            background: var(--green);
            color: white;
        }
        
        .btn-secondary:hover {
            background: #2E7D32;
            transform: translateY(-2px);
        }
        
        .whatsapp-btn {
            background: #25D366;
            color: white;
        }
        
        .whatsapp-btn:hover {
            background: #128C7E;
            transform: translateY(-2px);
        }
        
        @media (max-width: 768px) {
            .success-content {
                padding: 3rem 2rem;
                margin: 1rem;
            }
            
            .success-title {
                font-size: 1.5rem;
            }
            
            .action-buttons {
                flex-direction: column;
                align-items: stretch;
            }
        }
    </style>
</head>
<body>
    <div class="success-container">
        <div class="success-content">
            <div class="success-icon">
                <i class="fas fa-check-circle"></i>
            </div>
            
            <h1 class="success-title">Application Submitted Successfully!</h1>
            
            <div class="success-message">
                Thank you for your interest in our online courses. Your application has been received and our team will review it within 2-3 business days.
            </div>
            
            <div class="course-info">
                <h3>Course Applied For</h3>
                <p style="color: var(--dark); font-weight: 600; font-size: 1.1rem;">
                    <?php echo htmlspecialchars($courseName); ?>
                </p>
            </div>
            
            <div class="next-steps">
                <h3>What Happens Next?</h3>
                <ul>
                    <li><i class="fas fa-check" style="color: #2E7D32; margin-right: 0.5rem;"></i> Our admissions team will review your application</li>
                    <li><i class="fas fa-check" style="color: #2E7D32; margin-right: 0.5rem;"></i> You'll receive an email with the application decision</li>
                    <li><i class="fas fa-check" style="color: #2E7D32; margin-right: 0.5rem;"></i> If approved, you'll receive enrollment instructions</li>
                    <li><i class="fas fa-check" style="color: #2E7D32; margin-right: 0.5rem;"></i> Feel free to contact us if you have any questions</li>
                </ul>
            </div>
            
            <div class="action-buttons">
                <a href="online-courses.php" class="btn btn-primary">
                    <i class="fas fa-arrow-left"></i>
                    Browse More Courses
                </a>
                
                <a href="index.php" class="btn btn-secondary">
                    <i class="fas fa-home"></i>
                    Back to Home
                </a>
                
                <a href="https://wa.me/<?php echo str_replace([' ', ''], '+', getSetting('whatsapp_number')); ?>?text=Hello! I just submitted an application for the <?php echo urlencode($courseName); ?> course and have a question." 
                   class="btn whatsapp-btn" target="_blank">
                    <i class="fab fa-whatsapp"></i>
                    Chat on WhatsApp
                </a>
            </div>
        </div>
    </div>
</body>
</html>
