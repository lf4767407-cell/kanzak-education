<?php
include('./dbConnection.php');

// ─── Check if user is logged in ───
if(!isLoggedIn()) {
    $_SESSION['redirect_after_login'] = 'profile.php';
    redirect('loginorsignup.php');
}

$stu_id = getUserId();
$user = null;
$errors = [];
$success_msg = '';

// ─── Get user data from database ───
$sql = "SELECT * FROM users WHERE user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $stu_id);
$stmt->execute();
$result = $stmt->get_result();
if($result->num_rows > 0) {
    $user = $result->fetch_assoc();
}

// ─── Handle Avatar Upload ───
if(isset($_POST['upload_avatar']) && isset($_FILES['avatar'])) {
    $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    $max_size = 5 * 1024 * 1024; // 5MB

    $file = $_FILES['avatar'];

    if($file['error'] === 0) {
        if(in_array($file['type'], $allowed_types)) {
            if($file['size'] <= $max_size) {
                $upload_dir = './uploads/avatars/';
                if(!is_dir($upload_dir)) {
                    mkdir($upload_dir, 0755, true);
                }

                $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
                $filename = 'avatar_' . $stu_id . '_' . time() . '.' . $ext;
                $filepath = $upload_dir . $filename;

                if(move_uploaded_file($file['tmp_name'], $filepath)) {
                    $update_sql = "UPDATE users SET avatar = ? WHERE user_id = ?";
                    $update_stmt = $conn->prepare($update_sql);
                    $db_path = $filepath;
                    $update_stmt->bind_param("si", $db_path, $stu_id);

                    if($update_stmt->execute()) {
                        $_SESSION['stu_img'] = $db_path;
                        $success_msg = 'تم رفع الصورة الشخصية بنجاح!';
                        $stmt->execute();
                        $user = $stmt->get_result()->fetch_assoc();
                    } else {
                        $errors[] = 'حدث خطأ في تحديث قاعدة البيانات.';
                    }
                } else {
                    $errors[] = 'فشل في رفع الملف.';
                }
            } else {
                $errors[] = 'حجم الملف كبير جداً. الحد الأقصى 5 ميجابايت.';
            }
        } else {
            $errors[] = 'نوع الملف غير مدعوم. يسمح فقط بـ JPG, PNG, GIF, WEBP.';
        }
    } else {
        $errors[] = 'حدث خطأ أثناء رفع الملف.';
    }
}

// ─── Handle Profile Update ───
if(isset($_POST['update_profile'])) {
    $name = sanitize($_POST['name']);
    $email = sanitize($_POST['email']);
    $bio = sanitize($_POST['bio']);
    $phone = sanitize($_POST['phone'] ?? '');

    if(empty($name)) {
        $errors[] = 'الاسم مطلوب.';
    }
    if(empty($email)) {
        $errors[] = 'البريد الإلكتروني مطلوب.';
    } elseif(!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'البريد الإلكتروني غير صحيح.';
    }

    if(empty($errors)) {
        $check_sql = "SELECT user_id FROM users WHERE email = ? AND user_id != ?";
        $check_stmt = $conn->prepare($check_sql);
        $check_stmt->bind_param("si", $email, $stu_id);
        $check_stmt->execute();

        if($check_stmt->get_result()->num_rows > 0) {
            $errors[] = 'البريد الإلكتروني مستخدم من قبل مستخدم آخر.';
        } else {
            $update_sql = "UPDATE users SET name = ?, email = ?, bio = ?, phone = ? WHERE user_id = ?";
            $update_stmt = $conn->prepare($update_sql);
            $update_stmt->bind_param("ssssi", $name, $email, $bio, $phone, $stu_id);

            if($update_stmt->execute()) {
                $_SESSION['stu_name'] = $name;
                $_SESSION['stuLogEmail'] = $email;
                $success_msg = 'تم تحديث الملف الشخصي بنجاح!';
                $stmt->execute();
                $user = $stmt->get_result()->fetch_assoc();
            } else {
                $errors[] = 'حدث خطأ أثناء التحديث.';
            }
        }
    }
}

// ─── Handle Add/Remove Favorite ───
if(isset($_POST['toggle_favorite']) && isset($_POST['course_id'])) {
    $fav_course_id = intval($_POST['course_id']);
    $check_fav = $conn->prepare("SELECT fav_id FROM favorites WHERE user_id = ? AND course_id = ?");
    $check_fav->bind_param("ii", $stu_id, $fav_course_id);
    $check_fav->execute();

    if($check_fav->get_result()->num_rows > 0) {
        // Remove from favorites
        $conn->prepare("DELETE FROM favorites WHERE user_id = ? AND course_id = ?")->bind_param("ii", $stu_id, $fav_course_id);
        $conn->query("DELETE FROM favorites WHERE user_id = $stu_id AND course_id = $fav_course_id");
    } else {
        // Add to favorites
        $conn->query("INSERT INTO favorites (user_id, course_id, created_at) VALUES ($stu_id, $fav_course_id, NOW())");
    }

    // Refresh page to show updated favorites
    header("Location: profile.php");
    exit();
}

// ─── Get Statistics ───
$courses_sql = "SELECT COUNT(*) as total FROM orders WHERE user_id = ? AND payment_status = 'completed'";
$courses_stmt = $conn->prepare($courses_sql);
$courses_stmt->bind_param("i", $stu_id);
$courses_stmt->execute();
$courses_count = $courses_stmt->get_result()->fetch_assoc()['total'] ?? 0;

$lessons_sql = "SELECT COUNT(*) as total FROM progress WHERE user_id = ? AND is_completed = 'yes'";
$lessons_stmt = $conn->prepare($lessons_sql);
$lessons_stmt->bind_param("i", $stu_id);
$lessons_stmt->execute();
$lessons_count = $lessons_stmt->get_result()->fetch_assoc()['total'] ?? 0;

$total_courses_sql = "SELECT COUNT(*) as total FROM courses WHERE status = 'active'";
$total_courses = $conn->query($total_courses_sql)->fetch_assoc()['total'] ?? 0;

// ─── Get ALL enrolled courses with progress ───
$all_courses_sql = "SELECT c.*, o.created_at as enrolled_date, o.order_no,
                    (SELECT COUNT(*) FROM lessons WHERE course_id = c.course_id) as total_lessons,
                    (SELECT COUNT(*) FROM progress p WHERE p.course_id = c.course_id AND p.user_id = ? AND p.is_completed = 'yes') as completed_lessons
                    FROM courses c
                    JOIN orders o ON c.course_id = o.course_id
                    WHERE o.user_id = ? AND o.payment_status = 'completed'
                    ORDER BY o.created_at DESC";
$all_courses_stmt = $conn->prepare($all_courses_sql);
$all_courses_stmt->bind_param("ii", $stu_id, $stu_id);
$all_courses_stmt->execute();
$all_courses = $all_courses_stmt->get_result();

// ─── Get Favorite Courses ───
$fav_sql = "SELECT c.*, cat.cat_name, cat.cat_icon,
            (SELECT COUNT(*) FROM orders WHERE course_id = c.course_id AND payment_status = 'completed') as enrolled_count
            FROM favorites f
            JOIN courses c ON f.course_id = c.course_id
            LEFT JOIN categories cat ON c.cat_id = cat.cat_id
            WHERE f.user_id = ? AND c.status = 'active'
            ORDER BY f.created_at DESC";
$fav_stmt = $conn->prepare($fav_sql);
$fav_stmt->bind_param("i", $stu_id);
$fav_stmt->execute();
$favorite_courses = $fav_stmt->get_result();

// ─── Get recent courses (last 3) ───
$recent_courses_sql = "SELECT c.course_id, c.course_name, c.course_img, c.course_duration, o.created_at 
                       FROM orders o 
                       JOIN courses c ON o.course_id = c.course_id 
                       WHERE o.user_id = ? AND o.payment_status = 'completed' 
                       ORDER BY o.created_at DESC LIMIT 3";
$recent_stmt = $conn->prepare($recent_courses_sql);
$recent_stmt->bind_param("i", $stu_id);
$recent_stmt->execute();
$recent_courses = $recent_stmt->get_result();

include('./includes/header.php');
?>

<!-- ═══════════════════════════════════════════════════════════════ -->
<!-- Profile Page - Modern Design -->
<!-- ═══════════════════════════════════════════════════════════════ -->
<section class="profile-section py-5">
    <div class="container">

        <!-- Alerts -->
        <?php if(!empty($success_msg)): ?>
            <div class="alert alert-success alert-dismissible fade show rounded-4 shadow-sm mb-4" role="alert">
                <i class="fas fa-check-circle me-2"></i>
                <strong><?php echo $success_msg; ?></strong>
                <button type="button" class="btn-close" onclick="this.parentElement.remove()"></button>
            </div>
        <?php endif; ?>

        <?php if(!empty($errors)): ?>
            <?php foreach($errors as $error): ?>
                <div class="alert alert-danger alert-dismissible fade show rounded-4 shadow-sm mb-3" role="alert">
                    <i class="fas fa-times-circle me-2"></i>
                    <strong><?php echo $error; ?></strong>
                    <button type="button" class="btn-close" onclick="this.parentElement.remove()"></button>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>

        <div class="row g-4">
            <!-- Left Sidebar - User Card -->
            <div class="col-lg-4">
                <!-- Profile Card -->
                <div class="card profile-card border-0 shadow-lg rounded-4 mb-4">
                    <div class="card-body p-4 text-center">
                        <!-- Avatar -->
                        <div class="avatar-container mb-3 position-relative d-inline-block">
                            <?php 
                            $has_custom_avatar = ($user && !empty($user['avatar']) && file_exists($user['avatar']) && strpos($user['avatar'], 'http') !== 0);
                            $avatar_url = getUserAvatar();
                            ?>

                            <img src="<?php echo $avatar_url; ?>" 
                                 alt="<?php echo htmlspecialchars($user['name'] ?? 'المستخدم'); ?>" 
                                 class="profile-avatar-img rounded-circle"
                                 style="width: 150px; height: 150px; object-fit: cover; border: 4px solid #667eea;"
                                 id="profileAvatar">

                            <!-- Camera Button -->
                            <button class="btn btn-light btn-sm rounded-circle position-absolute bottom-0 end-0 shadow-sm" 
                                    style="width: 40px; height: 40px; border: 3px solid white;"
                                    onclick="document.getElementById('avatarInput').click()"
                                    title="تغيير الصورة الشخصية">
                                <i class="fas fa-camera text-primary"></i>
                            </button>
                        </div>

                        <h4 class="fw-bold mb-1"><?php echo htmlspecialchars($user['name'] ?? 'المستخدم'); ?></h4>
                        <p class="text-muted mb-2"><?php echo htmlspecialchars($user['email'] ?? ''); ?></p>

                        <span class="badge bg-primary bg-opacity-10 text-primary px-3 py-2 rounded-pill">
                            <i class="fas fa-user-graduate me-1"></i> طالب
                        </span>

                        <?php if(!empty($user['bio'])): ?>
                        <p class="text-muted mt-3 small fst-italic">
                            <i class="fas fa-quote-right me-1 text-primary"></i>
                            <?php echo htmlspecialchars($user['bio']); ?>
                        </p>
                        <?php endif; ?>

                        <div class="mt-3">
                            <small class="text-muted">
                                <i class="fas fa-calendar me-1"></i>
                                انضم منذ: <?php echo formatDate($user['created_at'] ?? null, 'Y-m-d'); ?>
                            </small>
                        </div>
                    </div>
                </div>

                <!-- Quick Stats -->
                <div class="card border-0 shadow-sm rounded-4 mb-4">
                    <div class="card-body p-4">
                        <h6 class="fw-bold mb-3">
                            <i class="fas fa-chart-pie text-primary me-2"></i> إحصائياتي
                        </h6>
                        <div class="row g-2">
                            <div class="col-6">
                                <div class="stat-box p-3 rounded-3 bg-primary bg-opacity-10 text-center">
                                    <h5 class="fw-bold text-primary mb-1"><?php echo $courses_count; ?></h5>
                                    <small class="text-muted">دوراتي</small>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="stat-box p-3 rounded-3 bg-success bg-opacity-10 text-center">
                                    <h5 class="fw-bold text-success mb-1"><?php echo $lessons_count; ?></h5>
                                    <small class="text-muted">دروس مكتملة</small>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="stat-box p-3 rounded-3 bg-warning bg-opacity-10 text-center">
                                    <h5 class="fw-bold text-warning mb-1"><?php echo $favorite_courses->num_rows; ?></h5>
                                    <small class="text-muted">مفضلات</small>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="stat-box p-3 rounded-3 bg-info bg-opacity-10 text-center">
                                    <h5 class="fw-bold text-info mb-1"><?php echo $total_courses - $courses_count; ?></h5>
                                    <small class="text-muted">دورات متبقية</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Navigation Menu -->
                <div class="card border-0 shadow-sm rounded-4">
                    <div class="card-body p-0">
                        <div class="list-group list-group-flush rounded-4">
                            <a href="profile.php" class="list-group-item list-group-item-action py-3 active">
                                <i class="fas fa-user text-primary me-2"></i> الملف الشخصي
                            </a>
                            <a href="student/mycourses.php" class="list-group-item list-group-item-action py-3">
                                <i class="fas fa-book text-success me-2"></i> دوراتي
                            </a>
                            <a href="student/watchCourse.php" class="list-group-item list-group-item-action py-3">
                                <i class="fas fa-play-circle text-info me-2"></i> استمر في التعلم
                            </a>
                            <a href="courses.php" class="list-group-item list-group-item-action py-3">
                                <i class="fas fa-search text-warning me-2"></i> استكشف دورات جديدة
                            </a>
                            <a href="contact.php" class="list-group-item list-group-item-action py-3">
                                <i class="fas fa-headset text-danger me-2"></i> الدعم الفني
                            </a>
                            <a href="logout.php" class="list-group-item list-group-item-action py-3 text-danger">
                                <i class="fas fa-sign-out-alt me-2"></i> تسجيل الخروج
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Main Content -->
            <div class="col-lg-8">

                <!-- Edit Profile Form -->
                <div class="card border-0 shadow-sm rounded-4 mb-4">
                    <div class="card-header bg-white border-0 p-4 pb-0">
                        <h5 class="fw-bold mb-0">
                            <i class="fas fa-user-edit text-primary me-2"></i> تعديل الملف الشخصي
                        </h5>
                    </div>
                    <div class="card-body p-4">
                        <form method="POST" action="profile.php" id="profileForm">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">الاسم الكامل <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light"><i class="fas fa-user text-muted"></i></span>
                                        <input type="text" name="name" class="form-control bg-light" 
                                               value="<?php echo htmlspecialchars($user['name'] ?? ''); ?>" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">البريد الإلكتروني <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light"><i class="fas fa-envelope text-muted"></i></span>
                                        <input type="email" name="email" class="form-control bg-light" 
                                               value="<?php echo htmlspecialchars($user['email'] ?? ''); ?>" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">رقم الهاتف</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light"><i class="fas fa-phone text-muted"></i></span>
                                        <input type="tel" name="phone" class="form-control bg-light" 
                                               value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>"
                                               placeholder="+218 91 XXX XXXX">
                                    </div>
                                </div>
                                <div class="col-12">
                                    <label class="form-label fw-bold">نبذة عني</label>
                                    <textarea name="bio" class="form-control bg-light" rows="4" 
                                              placeholder="اكتب نبذة قصيرة عنك..."><?php echo htmlspecialchars($user['bio'] ?? ''); ?></textarea>
                                </div>
                                <div class="col-12">
                                    <button type="submit" name="update_profile" class="btn btn-primary rounded-pill px-5 py-2 fw-bold">
                                        <i class="fas fa-save me-2"></i> حفظ التغييرات
                                    </button>
                                    <a href="index.php" class="btn btn-outline-secondary rounded-pill px-4 py-2 ms-2">
                                        <i class="fas fa-home me-2"></i> الرئيسية
                                    </a>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Avatar Upload Section (Hidden Form) -->
                <form method="POST" action="profile.php" enctype="multipart/form-data" id="avatarForm" style="display:none;">
                    <input type="file" name="avatar" id="avatarInput" accept="image/jpeg,image/png,image/gif,image/webp" 
                           onchange="previewAndSubmitAvatar(this)">
                    <button type="submit" name="upload_avatar" id="avatarSubmitBtn"></button>
                </form>

                <!-- Favorite Courses Section -->
                <div class="card border-0 shadow-sm rounded-4 mb-4">
                    <div class="card-header bg-white border-0 p-4 pb-0 d-flex justify-content-between align-items-center">
                        <h5 class="fw-bold mb-0">
                            <i class="fas fa-heart text-danger me-2"></i> دوراتي المفضلة
                        </h5>
                        <a href="courses.php" class="btn btn-sm btn-outline-primary rounded-pill">
                            اكتشف المزيد <i class="fas fa-arrow-left me-1"></i>
                        </a>
                    </div>
                    <div class="card-body p-4">
                        <?php if($favorite_courses && $favorite_courses->num_rows > 0): ?>
                            <div class="row g-3">
                                <?php while($course = $favorite_courses->fetch_assoc()): ?>
                                <div class="col-md-6">
                                    <div class="course-card-small d-flex flex-column h-100 p-3 rounded-4 bg-white border shadow-sm position-relative">
                                        <!-- Favorite Button -->
                                        <form method="POST" action="profile.php" class="position-absolute top-0 end-0 m-2">
                                            <input type="hidden" name="course_id" value="<?php echo $course['course_id']; ?>">
                                            <button type="submit" name="toggle_favorite" class="btn btn-light btn-sm rounded-circle shadow-sm" style="width: 36px; height: 36px;">
                                                <i class="fas fa-heart text-danger"></i>
                                            </button>
                                        </form>

                                        <div class="d-flex align-items-center mb-3">
                                            <img src="<?php echo $course['course_img'] ?? 'https://via.placeholder.com/80'; ?>" 
                                                 class="rounded-3 me-3 flex-shrink-0" 
                                                 style="width: 80px; height: 60px; object-fit: cover;" 
                                                 alt="<?php echo htmlspecialchars($course['course_name']); ?>">
                                            <div class="flex-grow-1 min-width-0">
                                                <h6 class="fw-bold mb-1 text-truncate"><?php echo htmlspecialchars($course['course_name']); ?></h6>
                                                <small class="text-muted">
                                                    <i class="fas fa-clock me-1"></i> <?php echo $course['course_duration'] ?? 'غير محدد'; ?>
                                                </small>
                                            </div>
                                        </div>

                                        <div class="mt-auto">
                                            <a href="coursedetails.php?course_id=<?php echo $course['course_id']; ?>" 
                                               class="btn btn-sm btn-outline-primary rounded-pill w-100">
                                                <i class="fas fa-info-circle me-1"></i> عرض التفاصيل
                                            </a>
                                        </div>
                                    </div>
                                </div>
                                <?php endwhile; ?>
                            </div>
                        <?php else: ?>
                            <div class="text-center py-4">
                                <i class="fas fa-heart fa-3x text-muted mb-3"></i>
                                <p class="text-muted">لا توجد دورات مفضلة بعد.</p>
                                <a href="courses.php" class="btn btn-primary rounded-pill">
                                    <i class="fas fa-search me-2"></i> استكشف الدورات
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- My Courses Section -->
                <div class="card border-0 shadow-sm rounded-4 mb-4">
                    <div class="card-header bg-white border-0 p-4 pb-0 d-flex justify-content-between align-items-center">
                        <h5 class="fw-bold mb-0">
                            <i class="fas fa-book text-primary me-2"></i> دوراتي المسجل بها
                        </h5>
                        <a href="student/mycourses.php" class="btn btn-sm btn-outline-primary rounded-pill">
                            عرض الكل <i class="fas fa-arrow-left me-1"></i>
                        </a>
                    </div>
                    <div class="card-body p-4">
                        <?php if($all_courses && $all_courses->num_rows > 0): ?>
                            <div class="row g-3">
                                <?php while($course = $all_courses->fetch_assoc()): 
                                    $total = $course['total_lessons'] ?? 1;
                                    $completed = $course['completed_lessons'] ?? 0;
                                    $progress = $total > 0 ? round(($completed / $total) * 100) : 0;
                                ?>
                                <div class="col-md-6">
                                    <div class="course-card-small d-flex flex-column h-100 p-3 rounded-4 bg-white border shadow-sm">
                                        <div class="d-flex align-items-center mb-3">
                                            <img src="<?php echo $course['course_img'] ?? 'https://via.placeholder.com/80'; ?>" 
                                                 class="rounded-3 me-3 flex-shrink-0" 
                                                 style="width: 80px; height: 60px; object-fit: cover;" 
                                                 alt="<?php echo htmlspecialchars($course['course_name']); ?>">
                                            <div class="flex-grow-1 min-width-0">
                                                <h6 class="fw-bold mb-1 text-truncate"><?php echo htmlspecialchars($course['course_name']); ?></h6>
                                                <small class="text-muted">
                                                    <i class="fas fa-clock me-1"></i> <?php echo $course['course_duration'] ?? 'غير محدد'; ?>
                                                </small>
                                            </div>
                                        </div>

                                        <!-- Progress Bar -->
                                        <div class="mb-3">
                                            <div class="d-flex justify-content-between mb-1">
                                                <small class="text-muted">التقدم</small>
                                                <small class="fw-bold text-primary"><?php echo $progress; ?>%</small>
                                            </div>
                                            <div class="progress" style="height: 8px;">
                                                <div class="progress-bar bg-success" role="progressbar" style="width: <?php echo $progress; ?>%"></div>
                                            </div>
                                            <small class="text-muted mt-1 d-block">
                                                <?php echo $completed; ?> من <?php echo $total; ?> درس مكتمل
                                            </small>
                                        </div>

                                        <div class="mt-auto">
                                            <a href="student/watchCourse.php?course_id=<?php echo $course['course_id']; ?>" 
                                               class="btn btn-sm btn-primary rounded-pill w-100">
                                                <i class="fas fa-play me-1"></i> 
                                                <?php echo $progress > 0 ? 'استمر في التعلم' : 'ابدأ الآن'; ?>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                                <?php endwhile; ?>
                            </div>
                        <?php else: ?>
                            <div class="text-center py-4">
                                <i class="fas fa-book-open fa-3x text-muted mb-3"></i>
                                <p class="text-muted">لم تسجل في أي دورة بعد.</p>
                                <a href="courses.php" class="btn btn-primary rounded-pill">
                                    <i class="fas fa-search me-2"></i> استكشف الدورات
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Recent Activity -->
                <div class="card border-0 shadow-sm rounded-4">
                    <div class="card-header bg-white border-0 p-4 pb-0 d-flex justify-content-between align-items-center">
                        <h5 class="fw-bold mb-0">
                            <i class="fas fa-history text-primary me-2"></i> آخر النشاطات
                        </h5>
                    </div>
                    <div class="card-body p-4">
                        <?php if($recent_courses && $recent_courses->num_rows > 0): ?>
                            <div class="timeline">
                                <?php while($course = $recent_courses->fetch_assoc()): ?>
                                <div class="timeline-item d-flex mb-3 pb-3 border-bottom">
                                    <div class="timeline-icon bg-primary bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center me-3 flex-shrink-0" style="width: 40px; height: 40px;">
                                        <i class="fas fa-book text-primary small"></i>
                                    </div>
                                    <div class="flex-grow-1">
                                        <h6 class="fw-bold mb-1">سجلت في دورة <?php echo htmlspecialchars($course['course_name']); ?></h6>
                                        <small class="text-muted">
                                            <i class="fas fa-calendar me-1"></i> <?php echo formatDate($course['created_at']); ?>
                                        </small>
                                    </div>
                                </div>
                                <?php endwhile; ?>
                            </div>
                        <?php else: ?>
                            <div class="text-center py-3 text-muted">
                                <i class="fas fa-inbox fa-2x mb-2"></i>
                                <p class="mb-0">لا توجد نشاطات حديثة</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<style>
/* Profile Section Styles */
.profile-section {
    background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
    min-height: 100vh;
}

.profile-card {
    background: white;
    transition: all 0.3s ease;
}

.profile-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 20px 40px rgba(0,0,0,0.1) !important;
}

.avatar-container {
    position: relative;
    display: inline-block;
}

.profile-avatar-img {
    transition: all 0.3s ease;
}

.profile-avatar-img:hover {
    transform: scale(1.05);
    box-shadow: 0 10px 30px rgba(102, 126, 234, 0.3);
}

.stat-box {
    transition: all 0.3s ease;
}

.stat-box:hover {
    transform: translateY(-3px);
}

.course-card-small {
    transition: all 0.3s ease;
}

.course-card-small:hover {
    transform: translateY(-3px);
    box-shadow: 0 10px 25px rgba(0,0,0,0.1) !important;
}

/* List Group Active State */
.list-group-item.active {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border-color: transparent;
}

.list-group-item.active i {
    color: white !important;
}

/* Form Styles */
.form-control:focus {
    border-color: #667eea;
    box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
}

/* Button Styles */
.btn-primary {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border: none;
    transition: all 0.3s ease;
}

.btn-primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 10px 20px rgba(102, 126, 234, 0.3);
}

/* Timeline */
.timeline-item:last-child {
    border-bottom: none !important;
    margin-bottom: 0 !important;
    padding-bottom: 0 !important;
}

/* Text truncate */
.text-truncate {
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.min-width-0 {
    min-width: 0;
}
</style>

<script>
// Preview avatar before upload and auto-submit
function previewAndSubmitAvatar(input) {
    if (input.files && input.files[0]) {
        var reader = new FileReader();
        reader.onload = function(e) {
            var avatar = document.getElementById('profileAvatar');
            if(avatar) {
                avatar.src = e.target.result;
            }
            setTimeout(function() {
                document.getElementById('avatarSubmitBtn').click();
            }, 500);
        };
        reader.readAsDataURL(input.files[0]);
    }
}

// Form Validation
document.addEventListener('DOMContentLoaded', function() {
    var profileForm = document.getElementById('profileForm');
    if(profileForm) {
        profileForm.addEventListener('submit', function(e) {
            var name = this.querySelector('input[name="name"]').value.trim();
            var email = this.querySelector('input[name="email"]').value.trim();

            if(!name) {
                e.preventDefault();
                alert('الرجاء إدخال الاسم الكامل');
                return false;
            }
            if(!email || !email.includes('@')) {
                e.preventDefault();
                alert('الرجاء إدخال بريد إلكتروني صحيح');
                return false;
            }
        });
    }

    // Auto-hide alerts after 5 seconds
    var alerts = document.querySelectorAll('.alert');
    alerts.forEach(function(alert) {
        setTimeout(function() {
            if(alert && alert.parentElement) {
                alert.style.opacity = '0';
                alert.style.transition = 'opacity 0.5s';
                setTimeout(function() {
                    alert.remove();
                }, 500);
            }
        }, 5000);
    });
});
</script>

<?php include('./includes/footer.php'); ?>