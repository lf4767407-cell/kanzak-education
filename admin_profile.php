<?php
include('../dbConnection.php');

if(!isAdmin()) {
    redirect('../index.php');
}

$admin_email = $_SESSION['stuLogEmail'] ?? 'admin@kanzak.com';
$admin_name = $_SESSION['stu_name'] ?? 'Admin';

// Get admin data
$admin_sql = "SELECT * FROM users WHERE email = ? AND role = 'admin'";
$admin_stmt = $conn->prepare($admin_sql);
$admin_stmt->bind_param("s", $admin_email);
$admin_stmt->execute();
$admin = $admin_stmt->get_result()->fetch_assoc();

$success_msg = '';
$errors = [];

// Handle profile update
if(isset($_POST['update_profile'])) {
    $name = sanitize($_POST['name']);
    $email = sanitize($_POST['email']);
    $current_pass = $_POST['current_password'] ?? '';
    $new_pass = $_POST['new_password'] ?? '';
    
    if(empty($name)) $errors[] = 'الاسم مطلوب';
    if(empty($email)) $errors[] = 'البريد مطلوب';
    
    if(empty($errors)) {
        // Update basic info
        $update_sql = "UPDATE users SET name = ?, email = ? WHERE user_id = ?";
        $update_stmt = $conn->prepare($update_sql);
        $update_stmt->bind_param("ssi", $name, $email, $admin['user_id']);
        
        if($update_stmt->execute()) {
            $_SESSION['stu_name'] = $name;
            $_SESSION['stuLogEmail'] = $email;
            $success_msg = 'تم تحديث الملف الشخصي بنجاح!';
        }
        
        // Update password if provided
        if(!empty($current_pass) && !empty($new_pass)) {
            if(password_verify($current_pass, $admin['password'])) {
                $hashed = password_hash($new_pass, PASSWORD_DEFAULT);
                $conn->query("UPDATE users SET password = '$hashed' WHERE user_id = " . $admin['user_id']);
                $success_msg .= ' وتم تحديث كلمة المرور!';
            } else {
                $errors[] = 'كلمة المرور الحالية غير صحيحة';
            }
        }
    }
}

// Get admin stats
$total_students = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM users WHERE role = 'student'"))['count'];
$total_courses = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM courses"))['count'];
$total_orders = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM orders"))['count'];

include('../includes/header.php');
?>

<style>
    .admin-sidebar { background: linear-gradient(180deg, #1e293b 0%, #0f172a 100%); min-height: 100vh; color: white; padding: 20px 0; position: fixed; right: 0; top: 0; width: 260px; z-index: 1000; transition: transform 0.3s ease; box-shadow: -5px 0 20px rgba(0,0,0,0.1); }
    .admin-main { margin-right: 260px; padding: 30px; min-height: 100vh; background: #f1f5f9; transition: margin-right 0.3s ease; }
    .admin-link { display: flex; align-items: center; gap: 12px; padding: 14px 24px; color: rgba(255,255,255,0.7); text-decoration: none; transition: all 0.3s; border-right: 3px solid transparent; margin: 4px 12px; border-radius: 12px; }
    .admin-link:hover, .admin-link.active { background: rgba(99, 102, 241, 0.2); color: white; border-right-color: #6366f1; }
    @media (max-width: 991px) { .admin-sidebar { transform: translateX(100%); } .admin-sidebar.active { transform: translateX(0); } .admin-main { margin-right: 0; } }
    .profile-card { background: white; border-radius: 20px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); border: 1px solid #e2e8f0; }
    .avatar-upload { position: relative; display: inline-block; }
    .avatar-upload .camera-btn { position: absolute; bottom: 5px; right: 5px; width: 36px; height: 36px; border-radius: 50%; background: white; border: 2px solid #e2e8f0; display: flex; align-items: center; justify-content: center; cursor: pointer; transition: all 0.3s; }
    .avatar-upload .camera-btn:hover { background: #6366f1; color: white; border-color: #6366f1; }
    .stat-box { background: white; border-radius: 16px; padding: 20px; text-align: center; box-shadow: 0 2px 10px rgba(0,0,0,0.05); border: 1px solid #e2e8f0; transition: all 0.3s; }
    .stat-box:hover { transform: translateY(-3px); box-shadow: 0 10px 25px rgba(0,0,0,0.1); }
</style>

<div class="admin-sidebar" id="adminSidebar">
    <div class="text-center p-4 border-bottom border-secondary border-opacity-25">
        <div class="bg-white bg-opacity-10 rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 60px; height: 60px;">
            <i class="fas fa-shield-alt fa-2x text-primary"></i>
        </div>
        <h5 class="fw-bold mb-1">لوحة التحكم</h5>
        <small class="text-white-50">كنزك التعليمي</small>
    </div>
    <nav class="mt-3">
        <a href="index.php" class="admin-link"><i class="fas fa-tachometer-alt"></i> الرئيسية</a>
        <a href="students.php" class="admin-link"><i class="fas fa-users"></i> الطلاب</a>
        <a href="courses.php" class="admin-link"><i class="fas fa-book"></i> الدورات</a>
        <a href="lessons.php" class="admin-link"><i class="fas fa-video"></i> الدروس</a>
        <a href="orders.php" class="admin-link"><i class="fas fa-shopping-cart"></i> الطلبات</a>
        <a href="contacts.php" class="admin-link"><i class="fas fa-envelope"></i> الرسائل</a>
        <div class="mt-4 pt-4 border-top border-secondary border-opacity-25">
            <a href="admin_profile.php" class="admin-link active"><i class="fas fa-user-shield"></i> الملف الشخصي</a>
            <a href="../index.php" target="_blank" class="admin-link text-warning"><i class="fas fa-globe"></i> الموقع</a>
            <a href="../logout.php" class="admin-link text-danger"><i class="fas fa-sign-out-alt"></i> تسجيل الخروج</a>
        </div>
    </nav>
</div>

<div class="admin-main">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold mb-0"><i class="fas fa-user-shield text-primary me-2"></i> الملف الشخصي</h4>
            <small class="text-muted">إدارة حساب المدير</small>
        </div>
        <button class="btn btn-light d-lg-none" onclick="toggleSidebar()"><i class="fas fa-bars"></i></button>
    </div>

    <?php if(!empty($success_msg)): ?>
    <div class="alert alert-success rounded-4 mb-4"><i class="fas fa-check-circle me-2"></i><?php echo $success_msg; ?></div>
    <?php endif; ?>
    
    <?php if(!empty($errors)): ?>
    <div class="alert alert-danger rounded-4 mb-4">
        <?php foreach($errors as $error): ?><div><i class="fas fa-times-circle me-2"></i><?php echo $error; ?></div><?php endforeach; ?>
    </div>
    <?php endif; ?>

    <div class="row g-4">
        <!-- Profile Card -->
        <div class="col-lg-4">
            <div class="profile-card p-4 text-center">
                <div class="avatar-upload mb-3">
                    <img src="https://ui-avatars.com/api/?name=Admin&background=6366f1&color=fff&size=150" 
                         class="rounded-circle" style="width: 120px; height: 120px; object-fit: cover; border: 4px solid #e2e8f0;" alt="">
                    <div class="camera-btn"><i class="fas fa-camera text-muted small"></i></div>
                </div>
                <h4 class="fw-bold mb-1"><?php echo htmlspecialchars($admin['name'] ?? $admin_name); ?></h4>
                <p class="text-muted mb-2"><?php echo htmlspecialchars($admin['email'] ?? $admin_email); ?></p>
                <span class="badge bg-primary bg-opacity-10 text-primary px-3 py-2 rounded-pill">
                    <i class="fas fa-shield-alt me-1"></i> مدير النظام
                </span>
                
                <div class="row g-2 mt-4">
                    <div class="col-4">
                        <div class="stat-box">
                            <h5 class="fw-bold text-primary mb-1"><?php echo $total_students; ?></h5>
                            <small class="text-muted">طلاب</small>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="stat-box">
                            <h5 class="fw-bold text-success mb-1"><?php echo $total_courses; ?></h5>
                            <small class="text-muted">دورات</small>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="stat-box">
                            <h5 class="fw-bold text-warning mb-1"><?php echo $total_orders; ?></h5>
                            <small class="text-muted">طلبات</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Edit Profile Form -->
        <div class="col-lg-8">
            <div class="profile-card p-4">
                <h5 class="fw-bold mb-4"><i class="fas fa-user-edit text-primary me-2"></i> تعديل الملف الشخصي</h5>
                <form method="POST" action="">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">الاسم</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light"><i class="fas fa-user text-muted"></i></span>
                                <input type="text" name="name" class="form-control" value="<?php echo htmlspecialchars($admin['name'] ?? ''); ?>" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">البريد الإلكتروني</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light"><i class="fas fa-envelope text-muted"></i></span>
                                <input type="email" name="email" class="form-control" value="<?php echo htmlspecialchars($admin['email'] ?? ''); ?>" required>
                            </div>
                        </div>
                        <div class="col-12"><hr class="my-2"></div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">كلمة المرور الحالية</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light"><i class="fas fa-lock text-muted"></i></span>
                                <input type="password" name="current_password" class="form-control" placeholder="اترك فارغاً إذا لا تريد التغيير">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">كلمة المرور الجديدة</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light"><i class="fas fa-key text-muted"></i></span>
                                <input type="password" name="new_password" class="form-control" placeholder="اترك فارغاً إذا لا تريد التغيير">
                            </div>
                        </div>
                        <div class="col-12 mt-3">
                            <button type="submit" name="update_profile" class="btn btn-primary rounded-pill px-5">
                                <i class="fas fa-save me-2"></i> حفظ التغييرات
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
function toggleSidebar() {
    document.getElementById('adminSidebar').classList.toggle('active');
}
</script>

<?php include('../includes/footer.php'); ?>