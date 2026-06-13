<?php
  include('./dbConnection.php');
  include('./includes/header.php');

  // ─── Handle Direct Enrollment from Index ───
  if(isset($_POST['enroll_course']) && isset($_POST['course_id']) && isset($_SESSION['stu_id'])) {
      $enroll_course_id = intval($_POST['course_id']);
      $stu_id = $_SESSION['stu_id'];

      // Check if already enrolled
      $check = $conn->prepare("SELECT order_id FROM orders WHERE course_id = ? AND user_id = ?");
      $check->bind_param("ii", $enroll_course_id, $stu_id);
      $check->execute();

      if($check->get_result()->num_rows == 0) {
          // Generate order number
          $order_no = "KZ" . date('Ymd') . rand(10000, 99999);

          // Get course price
          $price_sql = "SELECT course_price FROM courses WHERE course_id = ?";
          $price_stmt = $conn->prepare($price_sql);
          $price_stmt->bind_param("i", $enroll_course_id);
          $price_stmt->execute();
          $price = $price_stmt->get_result()->fetch_assoc()['course_price'] ?? 0;

          // Insert enrollment (free)
          $enroll_sql = "INSERT INTO orders (order_no, user_id, course_id, amount, payment_status, created_at) 
                        VALUES (?, ?, ?, ?, 'completed', NOW())";
          $enroll_stmt = $conn->prepare($enroll_sql);
          $enroll_stmt->bind_param("siid", $order_no, $stu_id, $enroll_course_id, $price);
          $enroll_stmt->execute();
      }

      header("Location: index.php?enrolled=1");
      exit();
  }

  // ─── Handle Favorite Toggle ───
  if(isset($_POST['toggle_fav']) && isset($_POST['fav_course_id']) && isset($_SESSION['stu_id'])) {
      $fav_id = intval($_POST['fav_course_id']);
      $stu_id = $_SESSION['stu_id'];

      $check_fav = $conn->prepare("SELECT fav_id FROM favorites WHERE user_id = ? AND course_id = ?");
      $check_fav->bind_param("ii", $stu_id, $fav_id);
      $check_fav->execute();

      if($check_fav->get_result()->num_rows > 0) {
          $conn->query("DELETE FROM favorites WHERE user_id = $stu_id AND course_id = $fav_id");
      } else {
          $conn->query("INSERT INTO favorites (user_id, course_id, created_at) VALUES ($stu_id, $fav_id, NOW())");
      }

      header("Location: index.php");
      exit();
  }
?>

<!-- Hero Section -->
<section class="hero-section">
    <div class="container">
        <div class="row align-items-center min-vh-100 pt-5">
            <div class="col-lg-6 hero-content">
                <span class="badge bg-white text-primary px-4 py-2 mb-3 fs-6 shadow-sm">
                    <i class="fas fa-star me-1 text-warning"></i> منصة التعلم الإلكتروني الأولى
                </span>
                <h1 class="hero-title">
                    تعلم بذكاء<br>
                    <span>حقق أحلامك</span>
                </h1>
                <p class="hero-subtitle">
                    منصة كنزك التعليمي توفر لك أفضل الدورات التعليمية <strong class="text-primary">مجاناً</strong> مع نخبة من المحاضرين المتخصصين. 
                    تعلم في أي وقت ومن أي مكان.
                </p>
                <div class="d-flex gap-3 flex-wrap">
                    <?php if(!isset($_SESSION['is_login'])){ ?>
                        <a href="courses.php" class="hero-btn hero-btn-primary">
                            <i class="fas fa-play-circle me-2"></i> ابدأ التعلم
                        </a>
                        <a href="loginorsignup.php" class="hero-btn hero-btn-outline">
                            <i class="fas fa-user-plus me-2"></i> سجل الآن مجاناً
                        </a>
                    <?php } else { ?>
                        <a href="profile.php" class="hero-btn hero-btn-primary">
                            <i class="fas fa-user me-2"></i> ملفي الشخصي
                        </a>
                        <a href="courses.php" class="hero-btn hero-btn-outline">
                            <i class="fas fa-book me-2"></i> استكشف الدورات
                        </a>
                    <?php } ?>
                </div>
                <div class="mt-5 d-flex gap-4 text-muted">
                    <div class="bg-white rounded-pill px-4 py-2 shadow-sm">
                        <i class="fas fa-users me-2 text-primary"></i> <strong>10,000+</strong> طالب
                    </div>
                    <div class="bg-white rounded-pill px-4 py-2 shadow-sm">
                        <i class="fas fa-book me-2 text-success"></i> <strong>200+</strong> دورة
                    </div>
                    <div class="bg-white rounded-pill px-4 py-2 shadow-sm">
                        <i class="fas fa-star me-2 text-warning"></i> <strong>4.9</strong> تقييم
                    </div>
                </div>
            </div>
            <div class="col-lg-6 d-none d-lg-block">
                <div class="hero-image-wrapper">
                    <img src="https://images.unsplash.com/photo-1522202176988-66273c2fd55f?w=600&h=500&fit=crop" 
                         alt="طلاب يتعلمون" class="hero-image img-fluid">
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Stats Banner -->
<section class="container mt-n5 position-relative" style="z-index: 10;">
    <div class="stats-banner">
        <div class="row text-center g-4">
            <div class="col-6 col-md-3">
                <div class="stat-item">
                    <i class="fas fa-book-open text-primary"></i>
                    <h3>200+</h3>
                    <p class="text-muted mb-0">دورة تعليمية</p>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="stat-item">
                    <i class="fas fa-chalkboard-teacher text-success"></i>
                    <h3>50+</h3>
                    <p class="text-muted mb-0">محاضر خبير</p>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="stat-item">
                    <i class="fas fa-user-graduate text-warning"></i>
                    <h3>10K+</h3>
                    <p class="text-muted mb-0">طالب مسجل</p>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="stat-item">
                    <i class="fas fa-award text-danger"></i>
                    <h3>95%</h3>
                    <p class="text-muted mb-0">نسبة الرضا</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ═══════════════════════════════════════════════════════════════ -->
<!-- My Enrolled Courses Section (Only for Logged In Users) -->
<!-- ═══════════════════════════════════════════════════════════════ -->
<?php if(isset($_SESSION['stu_id'])) { 
    $user_id = $_SESSION['stu_id'];

    // Get enrolled courses with progress
    $my_courses_sql = "SELECT c.*, o.created_at as enrolled_date,
                      (SELECT COUNT(*) FROM lessons WHERE course_id = c.course_id) as total_lessons,
                      (SELECT COUNT(*) FROM progress p WHERE p.course_id = c.course_id AND p.user_id = '$user_id' AND p.is_completed = 'yes') as completed_lessons
                      FROM courses c
                      JOIN orders o ON c.course_id = o.course_id
                      WHERE o.user_id = '$user_id' AND o.payment_status = 'completed'
                      ORDER BY o.created_at DESC
                      LIMIT 4";
    $my_courses = $conn->query($my_courses_sql);

    if($my_courses && $my_courses->num_rows > 0) {
?>
<section class="py-5 bg-white">
    <div class="container">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <span class="badge bg-success bg-opacity-10 text-success px-3 py-2 mb-2">
                    <i class="fas fa-graduation-cap me-1"></i> دوراتي
                </span>
                <h2 class="fw-bold mb-1">استمر في التعلم</h2>
                <p class="text-muted mb-0">الدورات التي سجلت فيها - أكمل تقدمك!</p>
            </div>
            <a href="student/mycourses.php" class="btn btn-outline-primary rounded-pill">
                عرض الكل <i class="fas fa-arrow-left me-1"></i>
            </a>
        </div>

        <!-- Success Alert after enrollment -->
        <?php if(isset($_GET['enrolled']) && $_GET['enrolled'] == '1') { ?>
        <div class="alert alert-success alert-dismissible fade show rounded-4 shadow-sm mb-4" role="alert">
            <i class="fas fa-check-circle me-2"></i>
            <strong>تم التسجيل في الدورة بنجاح!</strong> ابدأ التعلم الآن.
            <button type="button" class="btn-close" onclick="this.parentElement.remove()"></button>
        </div>
        <?php } ?>

        <div class="row g-4">
            <?php while($course = $my_courses->fetch_assoc()) {
                $total = $course['total_lessons'] ?? 1;
                $completed = $course['completed_lessons'] ?? 0;
                $progress = $total > 0 ? round(($completed / $total) * 100) : 0;
            ?>
            <div class="col-md-6 col-lg-3">
                <div class="card border-0 shadow-sm rounded-4 h-100 enrolled-course-card">
                    <div class="position-relative">
                        <img src="<?php echo $course['course_img']; ?>" class="card-img-top" style="height: 160px; object-fit: cover;" alt="">
                        <div class="position-absolute top-0 start-0 m-2">
                            <span class="badge bg-success">
                                <i class="fas fa-check-circle me-1"></i> مسجل
                            </span>
                        </div>
                        <!-- Progress Badge -->
                        <div class="position-absolute bottom-0 end-0 m-2">
                            <span class="badge bg-primary">
                                <?php echo $progress; ?>%
                            </span>
                        </div>
                    </div>
                    <div class="card-body p-3">
                        <h6 class="fw-bold mb-2"><?php echo $course['course_name']; ?></h6>

                        <!-- Progress Bar -->
                        <div class="mb-3">
                            <div class="progress" style="height: 6px;">
                                <div class="progress-bar bg-success" role="progressbar" style="width: <?php echo $progress; ?>%"></div>
                            </div>
                            <small class="text-muted mt-1 d-block">
                                <?php echo $completed; ?>/<?php echo $total; ?> درس
                            </small>
                        </div>

                        <a href="student/watchCourse.php?course_id=<?php echo $course['course_id']; ?>" class="btn btn-primary w-100 rounded-pill btn-sm">
                            <i class="fas fa-play me-1"></i> <?php echo $progress > 0 ? 'استمر' : 'ابدأ الآن'; ?>
                        </a>
                    </div>
                </div>
            </div>
            <?php } ?>
        </div>
    </div>
</section>
<?php } } ?>

<!-- ═══════════════════════════════════════════════════════════════ -->
<!-- All Courses - Available for Registration -->
<!-- ═══════════════════════════════════════════════════════════════ -->
<section class="py-5 bg-light">
    <div class="container">
        <div class="text-center mb-5">
            <span class="badge bg-primary bg-opacity-10 text-primary px-3 py-2 mb-3">
                <i class="fas fa-th-large me-1"></i> جميع الدورات
            </span>
            <h2 class="fw-bold display-6">اكتشف دوراتنا المميزة</h2>
            <p class="text-muted">اختر من مجموعة واسعة من الدورات في مختلف المجالات - <strong class="text-success">كلها مجانية!</strong></p>
        </div>

        <div class="row g-4">
            <?php
            // Get all courses (excluding already enrolled ones for logged in users)
            $exclude_sql = "";
            if(isset($_SESSION['stu_id'])) {
                $exclude_sql = " AND c.course_id NOT IN (SELECT course_id FROM orders WHERE user_id = " . $_SESSION['stu_id'] . " AND payment_status = 'completed')";
            }

            $sql = "SELECT c.*, COUNT(co.order_id) as enrolled_count, cat.cat_name, cat.cat_icon
                    FROM courses c 
                    LEFT JOIN orders co ON c.course_id = co.course_id AND co.payment_status = 'completed'
                    LEFT JOIN categories cat ON c.cat_id = cat.cat_id
                    WHERE c.status = 'active' $exclude_sql
                    GROUP BY c.course_id 
                    ORDER BY enrolled_count DESC 
                    LIMIT 8";
            $result = $conn->query($sql);
            if($result && $result->num_rows > 0){ 
                while($row = $result->fetch_assoc()){
                    $course_id = $row['course_id'];

                    $is_logged_in = isset($_SESSION['stu_id']);

                    // Check if favorite
                    $is_fav = false;
                    if($is_logged_in) {
                        $fav_sql = "SELECT fav_id FROM favorites WHERE user_id = ? AND course_id = ?";
                        $fav_stmt = $conn->prepare($fav_sql);
                        $fav_stmt->bind_param("ii", $_SESSION['stu_id'], $course_id);
                        $fav_stmt->execute();
                        $is_fav = $fav_stmt->get_result()->num_rows > 0;
                    }

                    // Get lesson count
                    $lesson_sql = "SELECT COUNT(*) as lesson_count FROM lessons WHERE course_id = ?";
                    $lesson_stmt = $conn->prepare($lesson_sql);
                    $lesson_stmt->bind_param("i", $course_id);
                    $lesson_stmt->execute();
                    $lesson_count = $lesson_stmt->get_result()->fetch_assoc()['lesson_count'] ?? 0;
            ?>
            <!-- Course Card -->
            <div class="col-md-6 col-lg-3 animate-on-scroll">
                <div class="course-card-image h-100">
                    <!-- Image Section -->
                    <div class="course-image-section position-relative">
                        <img src="<?php echo htmlspecialchars($row['course_img']); ?>" 
                             class="w-100" 
                             alt="<?php echo htmlspecialchars($row['course_name']); ?>"
                             style="height: 160px; object-fit: cover;">

                        <!-- Free Badge -->
                        <div class="position-absolute top-0 start-0 m-2">
                            <span class="badge bg-success">
                                <i class="fas fa-gift me-1"></i> مجاني
                            </span>
                        </div>

                        <!-- Favorite Button (Only for logged in) -->
                        <?php if($is_logged_in) { ?>
                        <form method="POST" action="index.php" class="position-absolute top-0 end-0 m-2">
                            <input type="hidden" name="fav_course_id" value="<?php echo $course_id; ?>">
                            <button type="submit" name="toggle_fav" class="btn btn-light btn-sm rounded-circle shadow-sm" style="width: 36px; height: 36px; padding: 0;">
                                <i class="fas fa-heart <?php echo $is_fav ? 'text-danger' : 'text-muted'; ?>"></i>
                            </button>
                        </form>
                        <?php } ?>

                        <!-- Duration Badge -->
                        <div class="position-absolute bottom-0 start-0 m-2">
                            <span class="badge bg-dark bg-opacity-75">
                                <i class="fas fa-clock me-1"></i> <?php echo htmlspecialchars($row['course_duration'] ?? 'غير محدد'); ?>
                            </span>
                        </div>

                        <!-- LOCKED OVERLAY for non-logged users -->
                        <?php if(!$is_logged_in) { ?>
                        <div class="position-absolute top-0 start-0 w-100 h-100 d-flex flex-column align-items-center justify-content-center" 
                             style="background: rgba(0,0,0,0.6); backdrop-filter: blur(2px);">
                            <i class="fas fa-lock fa-3x text-white mb-3"></i>
                            <p class="text-white fw-bold mb-2">المحتوى مقفل</p>
                            <a href="loginorsignup.php" class="btn btn-primary btn-sm rounded-pill">
                                <i class="fas fa-user-lock me-1"></i> سجل الدخول للوصول
                            </a>
                        </div>
                        <?php } ?>
                    </div>

                    <!-- Content Section -->
                    <div class="course-content-section bg-white p-3">
                        <!-- Category -->
                        <div class="mb-2">
                            <span class="badge bg-light text-dark border">
                                <i class="fas <?php echo $row['cat_icon'] ?? 'fa-book'; ?> me-1"></i>
                                <?php echo htmlspecialchars($row['cat_name'] ?? 'عام'); ?>
                            </span>
                        </div>

                        <!-- Course Name -->
                        <h6 class="fw-bold mb-2 course-title-line"><?php echo htmlspecialchars($row['course_name']); ?></h6>

                        <!-- Meta Info -->
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <div class="text-warning small">
                                <i class="fas fa-star"></i>
                                <span class="text-dark"><?php echo htmlspecialchars($row['rating'] ?? '0.0'); ?></span>
                            </div>
                            <small class="text-muted">
                                <i class="fas fa-user-friends me-1"></i> <?php echo intval($row['enrolled_count']); ?>
                            </small>
                        </div>

                        <!-- Lessons Count -->
                        <div class="mb-2">
                            <small class="text-muted">
                                <i class="fas fa-list-ol me-1"></i> <?php echo $lesson_count; ?> درس
                            </small>
                        </div>

                        <!-- Instructor -->
                        <div class="d-flex align-items-center pt-2 border-top">
                            <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($row['instructor_name'] ?? 'معلم'); ?>&background=random&size=64" 
                                 class="rounded-circle me-2" width="28" height="28" alt="">
                            <small class="text-muted"><?php echo htmlspecialchars($row['instructor_name'] ?? 'معلم'); ?></small>
                        </div>

                        <!-- Action Button -->
                        <div class="mt-3">
                            <?php if($is_logged_in) { ?>
                                <!-- DIRECT ENROLLMENT BUTTON -->
                                <form method="POST" action="index.php" class="d-grid">
                                    <input type="hidden" name="course_id" value="<?php echo $course_id; ?>">
                                    <button type="submit" name="enroll_course" class="btn btn-success w-100 rounded-pill btn-sm">
                                        <i class="fas fa-user-plus me-1"></i> سجل في الدورة
                                    </button>
                                </form>
                            <?php } else { ?>
                            <a href="loginorsignup.php" class="btn btn-outline-primary w-100 rounded-pill btn-sm">
                                <i class="fas fa-lock me-1"></i> سجل الدخول للوصول
                            </a>
                            <?php } ?>
                        </div>
                    </div>
                </div>
            </div>
            <?php } } else { ?>
            <div class="col-12 text-center py-5">
                <i class="fas fa-check-circle fa-3x text-success mb-3"></i>
                <h4>لقد سجلت في جميع الدورات المتاحة!</h4>
                <p class="text-muted">استمر في التعلم من صفحة "دوراتي"</p>
                <a href="student/mycourses.php" class="btn btn-primary rounded-pill mt-3">
                    <i class="fas fa-book me-2"></i> دوراتي
                </a>
            </div>
            <?php } ?>
        </div>

        <div class="text-center mt-5">
            <a href="courses.php" class="btn btn-primary px-5 rounded-pill fw-bold">
                <i class="fas fa-th-large me-2"></i> عرض جميع الدورات
            </a>
        </div>
    </div>
</section>

<!-- Categories -->
<section class="py-5">
    <div class="container">
        <div class="text-center mb-5">
            <span class="badge bg-primary bg-opacity-10 text-primary px-3 py-2 mb-3">التصنيفات</span>
            <h2 class="fw-bold display-6">تصفح حسب المجال</h2>
        </div>
        <div class="row g-4">
            <?php
            $cat_sql = "SELECT * FROM categories ORDER BY cat_id";
            $cat_result = $conn->query($cat_sql);
            if($cat_result && $cat_result->num_rows > 0) {
                while($cat = $cat_result->fetch_assoc()) {
                    $colors = ['primary', 'success', 'warning', 'danger', 'info', 'dark'];
                    $color = $colors[$cat['cat_id'] % count($colors)];
            ?>
            <div class="col-6 col-md-4 col-lg-2 animate-on-scroll">
                <a href="courses.php?cat=<?php echo $cat['cat_id']; ?>" class="text-decoration-none">
                    <div class="category-card">
                        <div class="icon-wrapper bg-<?php echo $color; ?> bg-opacity-10">
                            <i class="fas <?php echo $cat['cat_icon']; ?> text-<?php echo $color; ?>"></i>
                        </div>
                        <h6 class="fw-bold mb-1"><?php echo $cat['cat_name']; ?></h6>
                        <small class="text-muted">
                            <?php 
                            $count_sql = "SELECT COUNT(*) FROM courses WHERE cat_id = " . $cat['cat_id'];
                            $count_result = $conn->query($count_sql);
                            echo $count_result->fetch_row()[0];
                            ?> دورة
                        </small>
                    </div>
                </a>
            </div>
            <?php } } ?>
        </div>
    </div>
</section>

<!-- Testimonials -->
<section class="py-5 bg-light">
    <div class="container">
        <div class="text-center mb-5">
            <span class="badge bg-warning bg-opacity-10 text-warning px-3 py-2 mb-3">آراء الطلاب</span>
            <h2 class="fw-bold display-6">ماذا يقول طلابنا؟</h2>
        </div>
        <div class="row g-4">
            <?php 
            $review_sql = "SELECT r.*, u.name, u.avatar FROM reviews r 
                          JOIN users u ON r.user_id = u.user_id 
                          ORDER BY r.review_id DESC LIMIT 3";
            $review_result = $conn->query($review_sql);
            if($review_result && $review_result->num_rows > 0) {
                while($review = $review_result->fetch_assoc()){
            ?>
            <div class="col-md-4 animate-on-scroll">
                <div class="testimonial-card h-100">
                    <div class="d-flex text-warning mb-3">
                        <?php for($i=0; $i<($review['rating'] ?? 5); $i++) { ?>
                            <i class="fas fa-star"></i>
                        <?php } ?>
                    </div>
                    <p class="testimonial-text">"<?php echo $review['review_text']; ?>"</p>
                    <div class="testimonial-author">
                        <img src="<?php echo $review['avatar']; ?>" alt="<?php echo $review['name']; ?>">
                        <div>
                            <h6 class="fw-bold mb-0"><?php echo $review['name']; ?></h6>
                            <small class="text-muted">طالب في المنصة</small>
                        </div>
                    </div>
                </div>
            </div>
            <?php } } else { ?>
            <!-- Default testimonials -->
            <div class="col-md-4 animate-on-scroll">
                <div class="testimonial-card h-100">
                    <div class="d-flex text-warning mb-3">
                        <i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i>
                    </div>
                    <p class="testimonial-text">"دورة رائعة جداً! الشرح واضح والمعلم ممتاز. استفدت كثيراً وكل هذا <strong class="text-success">مجاناً</strong>!"</p>
                    <div class="testimonial-author">
                        <img src="https://ui-avatars.com/api/?name=Ahmed+Mohamed&background=6366f1&color=fff" alt="">
                        <div>
                            <h6 class="fw-bold mb-0">أحمد محمد</h6>
                            <small class="text-muted">مطور ويب</small>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4 animate-on-scroll">
                <div class="testimonial-card h-100">
                    <div class="d-flex text-warning mb-3">
                        <i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i>
                    </div>
                    <p class="testimonial-text">"أفضل منصة تعليمية جربتها. المحتوى عالي الجودة والدعم الفني ممتاز. أنصح الجميع بالتسجيل."</p>
                    <div class="testimonial-author">
                        <img src="https://ui-avatars.com/api/?name=Sara+Ahmad&background=ec4899&color=fff" alt="">
                        <div>
                            <h6 class="fw-bold mb-0">سارة أحمد</h6>
                            <small class="text-muted">مصممة UI/UX</small>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4 animate-on-scroll">
                <div class="testimonial-card h-100">
                    <div class="d-flex text-warning mb-3">
                        <i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star-half-alt"></i>
                    </div>
                    <p class="testimonial-text">"الدورات منظمة بشكل احترافي و<strong class="text-success">مجانية بالكامل</strong>. سجلت في 3 دورات وكلها كانت ممتازة."</p>
                    <div class="testimonial-author">
                        <img src="https://ui-avatars.com/api/?name=Khaled+Ali&background=10b981&color=fff" alt="">
                        <div>
                            <h6 class="fw-bold mb-0">خالد علي</h6>
                            <small class="text-muted">طالب جامعي</small>
                        </div>
                    </div>
                </div>
            </div>
            <?php } ?>
        </div>
    </div>
</section>

<!-- Contact Section -->
<section id="contact" class="py-5">
    <div class="container">
        <div class="row g-5 align-items-center">
            <div class="col-lg-6">
                <span class="badge bg-primary bg-opacity-10 text-primary px-3 py-2 mb-3">تواصل معنا</span>
                <h2 class="fw-bold display-6 mb-4">هل لديك سؤال؟</h2>
                <p class="text-muted mb-4">فريق <strong>كنزك التعليمي</strong> جاهز لمساعدتك في أي وقت. لا تتردد في التواصل معنا.</p>

                <div class="d-flex align-items-center mb-4 p-3 rounded-4 bg-white shadow-sm">
                    <div class="bg-primary bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 55px; height: 55px;">
                        <i class="fas fa-map-marker-alt text-primary fs-5"></i>
                    </div>
                    <div>
                        <h6 class="fw-bold mb-0">العنوان</h6>
                        <small class="text-muted">مصراته - ليبيا</small>
                    </div>
                </div>

                <div class="d-flex align-items-center mb-4 p-3 rounded-4 bg-white shadow-sm">
                    <div class="bg-success bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 55px; height: 55px;">
                        <i class="fas fa-phone text-success fs-5"></i>
                    </div>
                    <div>
                        <h6 class="fw-bold mb-0">الهاتف</h6>
                        <small class="text-muted">+218 91 000 0000</small>
                    </div>
                </div>

                <div class="d-flex align-items-center p-3 rounded-4 bg-white shadow-sm">
                    <div class="bg-warning bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 55px; height: 55px;">
                        <i class="fas fa-envelope text-warning fs-5"></i>
                    </div>
                    <div>
                        <h6 class="fw-bold mb-0">البريد الإلكتروني</h6>
                        <small class="text-muted">info@kanzak.ly</small>
                    </div>
                </div>
            </div>

            <div class="col-lg-6">
                <div class="card border-0 shadow-lg rounded-4">
                    <div class="card-body p-4">
                        <form action="contact.php" method="POST" class="needs-validation" novalidate>
                            <div class="mb-3">
                                <label class="form-label fw-bold">الاسم</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-end-0"><i class="fas fa-user text-muted"></i></span>
                                    <input type="text" name="name" class="form-control border-start-0 bg-light" required placeholder="اسمك الكامل">
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-bold">البريد الإلكتروني</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-end-0"><i class="fas fa-envelope text-muted"></i></span>
                                    <input type="email" name="email" class="form-control border-start-0 bg-light" required placeholder="your@email.com">
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-bold">الموضوع</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-end-0"><i class="fas fa-tag text-muted"></i></span>
                                    <input type="text" name="subject" class="form-control border-start-0 bg-light" placeholder="موضوع الرسالة">
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-bold">الرسالة</label>
                                <textarea name="message" class="form-control bg-light" rows="5" required placeholder="اكتب رسالتك هنا..."></textarea>
                            </div>
                            <button type="submit" name="submit" class="btn btn-primary w-100 py-3 rounded-pill fw-bold">
                                <i class="fas fa-paper-plane me-2"></i> إرسال الرسالة
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- CTA Section -->
<section class="cta-section">
    <div class="container position-relative" style="z-index: 2;">
        <div class="row align-items-center">
            <div class="col-lg-8 text-center text-lg-start">
                <h2 class="fw-bold text-primary-dark mb-3">هل أنت مستعد لبدء رحلة التعلم؟</h2>
                <p class="text-muted mb-0 fs-5">انضم إلى آلاف الطلاب وابدأ بتطوير مهاراتك اليوم - <strong class="text-success">كل شيء مجاني!</strong></p>
            </div>
            <div class="col-lg-4 text-center text-lg-end mt-4 mt-lg-0">
                <a href="loginorsignup.php" class="btn btn-primary btn-lg px-5 rounded-pill fw-bold shadow-lg">
                    <i class="fas fa-rocket me-2"></i> ابدأ مجاناً
                </a>
            </div>
        </div>
    </div>
</section>

<?php include('./includes/footer.php'); ?>

<style>
/* Course Card Style - Like Image */
.course-card-image {
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 2px 8px rgba(0,0,0,0.08);
    transition: all 0.3s ease;
    background: white;
}

.course-card-image:hover {
    transform: translateY(-5px);
    box-shadow: 0 15px 35px rgba(0,0,0,0.12);
}

.course-image-section {
    position: relative;
    overflow: hidden;
}

.course-image-section img {
    transition: transform 0.3s ease;
}

.course-card-image:hover .course-image-section img {
    transform: scale(1.05);
}

.course-content-section {
    border-bottom-left-radius: 12px;
    border-bottom-right-radius: 12px;
}

.course-title-line {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
    line-height: 1.5;
    min-height: 3em;
}

/* Enrolled Course Card */
.enrolled-course-card {
    transition: all 0.3s ease;
    border: 2px solid transparent;
}

.enrolled-course-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 15px 35px rgba(0,0,0,0.12) !important;
    border-color: #667eea;
}

/* Favorite button hover */
.btn-light:hover .fa-heart {
    color: #dc3545 !important;
}

/* Locked overlay animation */
.course-image-section .position-absolute.d-flex {
    transition: opacity 0.3s ease;
}

.course-card-image:hover .course-image-section .position-absolute.d-flex {
    background: rgba(0,0,0,0.7) !important;
}

/* Success enrollment animation */
@keyframes enrollmentSuccess {
    0% { transform: scale(1); }
    50% { transform: scale(1.02); }
    100% { transform: scale(1); }
}

.enrollment-success {
    animation: enrollmentSuccess 0.5s ease;
}
</style>