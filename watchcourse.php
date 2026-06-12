<?php
include('../dbConnection.php');

if(!isLoggedIn()) {
    redirect('../loginorsignup.php');
}

$user_id = $_SESSION['stu_id'];

// Get enrolled courses with progress
$courses_sql = "SELECT c.*, o.created_at as enrolled_date, o.order_no,
                (SELECT COUNT(*) FROM lessons WHERE course_id = c.course_id) as total_lessons,
                (SELECT COUNT(*) FROM progress p WHERE p.course_id = c.course_id AND p.user_id = '$user_id' AND p.is_completed = 'yes') as completed_lessons
                FROM courses c
                JOIN orders o ON c.course_id = o.course_id
                WHERE o.user_id = '$user_id' AND o.payment_status = 'completed'
                ORDER BY o.created_at DESC";
$enrolled_courses = mysqli_query($conn, $courses_sql);

include('../includes/header.php');
?>

<section class="py-5 bg-light min-vh-100">
    <div class="container">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 class="fw-bold mb-1">دوراتي</h2>
                <p class="text-muted mb-0">إدارة دوراتك وتقدمك</p>
            </div>
            <a href="../courses.php" class="btn btn-primary rounded-pill">
                <i class="fas fa-plus me-2"></i> دورة جديدة
            </a>
        </div>

        <?php if(mysqli_num_rows($enrolled_courses) > 0) { ?>
        <div class="row g-4">
            <?php while($course = mysqli_fetch_assoc($enrolled_courses)) {
                $total = $course['total_lessons'] ?? 1;
                $completed = $course['completed_lessons'] ?? 0;
                $progress = $total > 0 ? round(($completed / $total) * 100) : 0;
            ?>
            <div class="col-md-6 col-lg-4">
                <div class="card border-0 shadow-sm rounded-4 h-100">
                    <div class="position-relative">
                        <img src="<?php echo $course['course_img']; ?>" class="card-img-top" style="height: 180px; object-fit: cover;" alt="">
                        <div class="position-absolute top-0 start-0 m-2">
                            <span class="badge bg-dark bg-opacity-75">
                                <i class="fas fa-clock me-1"></i> <?php echo $course['course_duration']; ?>
                            </span>
                        </div>
                        <!-- Progress Badge -->
                        <div class="position-absolute bottom-0 end-0 m-2">
                            <span class="badge bg-primary">
                                <?php echo $progress; ?>% مكتمل
                            </span>
                        </div>
                    </div>
                    <div class="card-body p-4">
                        <h5 class="fw-bold mb-2"><?php echo $course['course_name']; ?></h5>
                        <p class="text-muted small mb-3"><?php echo substr($course['course_desc'], 0, 80); ?>...</p>

                        <div class="mb-3">
                            <div class="progress" style="height: 8px;">
                                <div class="progress-bar bg-success" role="progressbar" style="width: <?php echo $progress; ?>%"></div>
                            </div>
                        </div>

                        <div class="d-flex justify-content-between align-items-center">
                            <small class="text-muted">
                                <i class="fas fa-check-circle text-success me-1"></i> <?php echo $completed; ?>/<?php echo $total; ?> درس
                            </small>
                            <a href="watchCourse.php?course_id=<?php echo $course['course_id']; ?>" class="btn btn-primary btn-sm rounded-pill">
                                <i class="fas fa-play me-1"></i> <?php echo $progress > 0 ? 'استمر' : 'ابدأ'; ?>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            <?php } ?>
        </div>
        <?php } else { ?>
        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-body text-center py-5">
                <i class="fas fa-book-open fa-4x text-muted mb-4"></i>
                <h4 class="fw-bold mb-2">لا توجد دورات</h4>
                <p class="text-muted mb-4">لم تشترك في أي دورة بعد. ابدأ رحلة التعلم الآن!</p>
                <a href="../courses.php" class="btn btn-primary btn-lg rounded-pill px-5">
                    <i class="fas fa-search me-2"></i> استكشف الدورات
                </a>
            </div>
        </div>
        <?php } ?>
    </div>
</section>

<?php include('../includes/footer.php'); ?>