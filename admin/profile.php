<?php
$pageTitle = 'My Profile';
require_once 'includes/header.php';

$pdo = getDB();
$userId = $_SESSION['user_id'];
$error = '';
$success = '';

// Get user data
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$userId]);
$user = $stmt->fetch();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update_profile'])) {
        $firstName = sanitize($_POST['first_name']);
        $lastName = sanitize($_POST['last_name']);
        $phone = sanitize($_POST['phone']);
        
        // Handle profile image upload
        $profileImage = $user['profile_image'];
        if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] === UPLOAD_ERR_OK) {
            $uploaded = uploadFile($_FILES['profile_image'], 'users');
            if ($uploaded) {
                $profileImage = $uploaded;
            }
        }
        
        $updateStmt = $pdo->prepare("UPDATE users SET first_name = ?, last_name = ?, phone = ?, profile_image = ? WHERE id = ?");
        if ($updateStmt->execute([$firstName, $lastName, $phone, $profileImage, $userId])) {
            $_SESSION['full_name'] = $firstName . ' ' . $lastName;
            $success = 'Profile updated successfully!';
            // Refresh user data
            $stmt->execute([$userId]);
            $user = $stmt->fetch();
        } else {
            $error = 'Failed to update profile.';
        }
    } elseif (isset($_POST['change_password'])) {
        $currentPassword = $_POST['current_password'];
        $newPassword = $_POST['new_password'];
        $confirmPassword = $_POST['confirm_password'];
        
        // Verify current password
        if (!password_verify($currentPassword, $user['password_hash'])) {
            $error = 'Current password is incorrect.';
        } elseif ($newPassword !== $confirmPassword) {
            $error = 'New passwords do not match.';
        } elseif (strlen($newPassword) < 6) {
            $error = 'Password must be at least 6 characters.';
        } else {
            $newHash = password_hash($newPassword, PASSWORD_DEFAULT);
            $passStmt = $pdo->prepare("UPDATE users SET password_hash = ? WHERE id = ?");
            if ($passStmt->execute([$newHash, $userId])) {
                $success = 'Password changed successfully!';
            } else {
                $error = 'Failed to change password.';
            }
        }
    }
}

$extraCss = '<style>
    .profile-container {
        display: grid;
        grid-template-columns: 300px 1fr;
        gap: 2rem;
    }
    .profile-sidebar {
        background: white;
        border-radius: 24px;
        padding: 2rem;
        text-align: center;
        box-shadow: var(--shadow);
    }
    .profile-avatar {
        width: 150px;
        height: 150px;
        border-radius: 50%;
        object-fit: cover;
        margin-bottom: 1rem;
        border: 4px solid var(--blue);
    }
    .profile-sidebar h3 {
        margin-bottom: 0.25rem;
    }
    .profile-sidebar .role {
        color: var(--gray);
        font-size: 0.9rem;
    }
    .profile-sidebar .email {
        margin-top: 1rem;
        padding-top: 1rem;
        border-top: 1px solid #E2E8F0;
        font-size: 0.9rem;
    }
    .profile-form {
        background: white;
        border-radius: 24px;
        padding: 2rem;
        box-shadow: var(--shadow);
    }
    .form-group {
        margin-bottom: 1.5rem;
    }
    .form-group label {
        display: block;
        margin-bottom: 0.5rem;
        font-weight: 600;
    }
    .form-group input {
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
    .submit-btn {
        background: var(--blue);
        color: white;
        border: none;
        padding: 0.75rem 2rem;
        border-radius: 40px;
        cursor: pointer;
        font-weight: 600;
        transition: var(--transition);
    }
    .submit-btn:hover {
        background: var(--red);
    }
    @media (max-width: 768px) {
        .profile-container {
            grid-template-columns: 1fr;
        }
        .form-row {
            grid-template-columns: 1fr;
        }
    }
</style>';
?>

<div class="profile-container">
    <div class="profile-sidebar animate-slide-up">
        <?php if ($user['profile_image']): ?>
            <img src="<?php echo $user['profile_image']; ?>" alt="Avatar" class="profile-avatar">
        <?php else: ?>
            <div class="profile-avatar" style="background: var(--light); display: flex; align-items: center; justify-content: center; margin: 0 auto 1rem;">
                <i class="fas fa-user-circle" style="font-size: 6rem; color: var(--blue);"></i>
            </div>
        <?php endif; ?>
        <h3><?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?></h3>
        <p class="role"><?php echo getUserRole(); ?></p>
        <div class="email">
            <i class="fas fa-envelope"></i> <?php echo htmlspecialchars($user['email']); ?>
        </div>
        <div class="email">
            <i class="fas fa-calendar-alt"></i> Joined <?php echo date('M Y', strtotime($user['created_at'])); ?>
        </div>
    </div>
    
    <div class="profile-form animate-slide-up">
        <?php if ($error): ?>
            <div class="alert alert-error"><?php echo $error; ?></div>
        <?php endif; ?>
        <?php if ($success): ?>
            <div class="alert alert-success"><?php echo $success; ?></div>
        <?php endif; ?>
        
        <h2 style="margin-bottom: 1.5rem;">Edit Profile</h2>
        
        <form method="POST" enctype="multipart/form-data">
            <div class="form-row">
                <div class="form-group">
                    <label>First Name</label>
                    <input type="text" name="first_name" value="<?php echo htmlspecialchars($user['first_name']); ?>" required>
                </div>
                <div class="form-group">
                    <label>Last Name</label>
                    <input type="text" name="last_name" value="<?php echo htmlspecialchars($user['last_name']); ?>" required>
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label>Email</label>
                    <input type="email" value="<?php echo htmlspecialchars($user['email']); ?>" disabled style="background: var(--light);">
                </div>
                <div class="form-group">
                    <label>Phone Number</label>
                    <input type="tel" name="phone" value="<?php echo htmlspecialchars($user['phone']); ?>">
                </div>
            </div>
            
            <div class="form-group">
                <label>Profile Image</label>
                <input type="file" name="profile_image" accept="image/*">
            </div>
            
            <button type="submit" name="update_profile" class="submit-btn">
                <i class="fas fa-save"></i> Update Profile
            </button>
        </form>
        
        <hr style="margin: 2rem 0; border-color: #E2E8F0;">
        
        <h2 style="margin-bottom: 1.5rem;">Change Password</h2>
        
        <form method="POST">
            <div class="form-group">
                <label>Current Password</label>
                <input type="password" name="current_password" required>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label>New Password</label>
                    <input type="password" name="new_password" required>
                </div>
                <div class="form-group">
                    <label>Confirm New Password</label>
                    <input type="password" name="confirm_password" required>
                </div>
            </div>
            
            <button type="submit" name="change_password" class="submit-btn">
                <i class="fas fa-key"></i> Change Password
            </button>
        </form>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>