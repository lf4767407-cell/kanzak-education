<?php
include('../dbConnection.php');

if(!isAdmin()) {
    redirect('../index.php');
}

// Handle delete
if(isset($_GET['delete'])) {
    $user_id = intval($_GET['delete']);
    $sql = "DELETE FROM users WHERE user_id = '$user_id' AND role = 'student'";
    mysqli_query($conn, $sql);
    redirect('students.php');
}

// Handle status toggle
if(isset($_GET['toggle'])) {
    $user_id = intval($_GET['toggle']);
    $sql = "UPDATE users SET status = CASE WHEN status = 'active' THEN 'inactive' ELSE 'active' END WHERE user_id = '$user_id'";
    mysqli_query($conn, $sql);
    redirect('students.php');
}

// Get students with their enrolled courses
$students_sql = "SELECT u.*, COUNT(o.order_id) as courses_count FROM users u 
                LEFT JOIN orders o ON u.user_id = o.user_id AND o.payment_status = 'completed'
                WHERE u.role = 'student'
                GROUP BY u.user_id ORDER BY u.created_at DESC";
$students = mysqli_query($conn, $students_sql);

// Get total students count
$total_students = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM users WHERE role = 'student'"))['count'];
$active_students = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM users WHERE role = 'student' AND status = 'active'"))['count'];

include('../includes/header.php');
?>

<style>
    .admin-sidebar { background: #1e293b; min-height: 100vh; color: white; padding: 20px 0; position: fixed; right: 0; top: 0; width: 260px; z-index: 1000; transition: transform 0.3s ease; }
    .admin-main { margin-right: 260px; padding: 30px; min-height: 100vh; background: #f8fafc; transition: margin-right 0.3s ease; }
    .admin-link { display: flex; align-items: center; gap: 10px; padding: 12px 20px; color: rgba(255,255,255,0.7); text-decoration: none; transition: all 0.3s; border-right: 3px solid transparent; }
    .admin-link:hover, .admin-link.active { background: rgba(255,255,255,0.1); color: white; border-right-color: #6366f1; }
    .student-card { transition: all 0.3s ease; }
    .student-card:hover { transform: translateY(-3px); box-shadow: 0 10px 30px rgba(0,0,0,0.1) !important; }
    @media (max-width: 991px) { .admin-sidebar { transform: translateX(100%); } .admin-sidebar.active { transform: translateX(0); } .admin-main { margin-right: 0; } }
    .fade-in { animation: fadeIn 0.5s ease-in; }
    @keyframes fadeIn { from { opacity: 0; transform: translateY(20px); } to { opacity: 1; transform: translateY(0); } }
    .stat-card-sm { background: white; border-radius: 12px; padding: 20px; text-align: center; box-shadow: 0 2px 10px rgba(0,0,0,0.05); }
</style>

<div class="admin-sidebar" id="adminSidebar">
    <div class="text-center p-4 border-bottom border-secondary">
        <i class="fas fa-shield-alt fa-2x text-primary mb-2"></i>
        <h5 class="fw-bold mb-0">لوحة التحكم</h5>
        <small class="text-white-50">كنزك التعليمي</small>
    </div>
    <nav class="mt-3">
        <a href="index.php" class="admin-link"><i class="fas fa-tachometer-alt"></i> الرئيسية</a>
        <a href="students.php" class="admin-link active"><i class="fas fa-users"></i> الطلاب</a>
        <a href="courses.php" class="admin-link"><i class="fas fa-book"></i> الدورات</a>
        <a href="lessons.php" class="admin-link"><i class="fas fa-video"></i> الدروس</a>
        <a href="orders.php" class="admin-link"><i class="fas fa-shopping-cart"></i> الطلبات</a>
        <a href="contacts.php" class="admin-link"><i class="fas fa-envelope"></i> الرسائل</a>
        <a href="../index.php" class="admin-link text-warning"><i class="fas fa-globe"></i> الموقع</a>
        <a href="../logout.php" class="admin-link text-danger"><i class="fas fa-sign-out-alt"></i> تسجيل الخروج</a>
    </nav>
</div>

<div class="admin-main">
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <div>
            <h4 class="fw-bold mb-0">إدارة الطلاب</h4>
            <small class="text-muted">عرض وإدارة حسابات الطلاب ودوراتهم</small>
        </div>
        <div class="d-flex gap-2">
            <div class="stat-card-sm">
                <small class="text-muted">الطلاب</small>
                <h5 class="fw-bold mb-0"><?php echo $total_students; ?></h5>
            </div>
            <div class="stat-card-sm">
                <small class="text-muted">نشط</small>
                <h5 class="fw-bold mb-0 text-success"><?php echo $active_students; ?></h5>
            </div>
            <button class="btn btn-light d-lg-none" onclick="toggleSidebar()"><i class="fas fa-bars"></i></button>
        </div>
    </div>

    <!-- Students Grid with Courses -->
    <div class="row g-4">
        <?php if(mysqli_num_rows($students) > 0) { 
            $num = 0;
            while($student = mysqli_fetch_assoc($students)) { 
                $num++;

                // Get student's enrolled courses with progress
                $stu_id = $student['user_id'];
                $courses_sql = "SELECT c.course_id, c.course_name, c.course_img, c.course_duration, o.created_at as enrolled_date 
                               FROM courses c 
                               JOIN orders o ON c.course_id = o.course_id 
                               WHERE o.user_id = '$stu_id' AND o.payment_status = 'completed'
                               ORDER BY o.created_at DESC";
                $enrolled_courses = mysqli_query($conn, $courses_sql);

                // Calculate progress for each course
                $progress_data = [];
                while($course = mysqli_fetch_assoc($enrolled_courses)) {
                    $total = getTotalLessons($course['course_id']);
                    $completed = getCompletedLessons($stu_id, $course['course_id']);
                    $progress = $total > 0 ? round(($completed / $total) * 100) : 0;
                    $course['progress'] = $progress;
                    $course['total_lessons'] = $total;
                    $course['completed_lessons'] = $completed;
                    $progress_data[] = $course;
                }
        ?>
        <div class="col-lg-6 col-xl-4 fade-in">
            <div class="card student-card border-0 shadow-sm rounded-4 h-100">
                <div class="card-body p-4">
                    <!-- Student Header -->
                    <div class="d-flex align-items-center mb-3">
                        <img src="<?php echo $student['avatar'] ?? 'https://ui-avatars.com/api/?name='.urlencode($student['name']).'&background=random'; ?>" 
                             class="rounded-circle me-3" width="60" height="60" style="object-fit: cover; border: 3px solid #6366f1;" alt="">
                        <div class="flex-grow-1">
                            <h5 class="fw-bold mb-1"><?php echo $student['name']; ?></h5>
                            <p class="text-muted mb-0 small"><?php echo $student['email']; ?></p>
                            <div class="d-flex align-items-center gap-2 mt-1">
                                <span class="badge bg-<?php echo $student['status'] == 'active' ? 'success' : 'secondary'; ?> rounded-pill">
                                    <?php echo $student['status'] == 'active' ? 'نشط' : 'غير نشط'; ?>
                                </span>
                                <span class="badge bg-primary bg-opacity-10 text-primary rounded-pill">
                                    <i class="fas fa-book me-1"></i> <?php echo $student['courses_count']; ?> دورة
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Phone & Date -->
                    <div class="d-flex justify-content-between text-muted small mb-3">
                        <span><i class="fas fa-phone me-1"></i> <?php echo $student['phone'] ?? 'غير محدد'; ?></span>
                        <span><i class="fas fa-calendar me-1"></i> <?php echo date('Y-m-d', strtotime($student['created_at'])); ?></span>
                    </div>

                    <!-- Enrolled Courses with Progress -->
                    <?php if(count($progress_data) > 0) { ?>
                    <div class="mb-3">
                        <h6 class="fw-bold mb-2 small"><i class="fas fa-graduation-cap text-primary me-1"></i> الدورات المسجل بها:</h6>
                        <div class="courses-list" style="max-height: 200px; overflow-y: auto;">
                            <?php foreach($progress_data as $course) { ?>
                            <div class="d-flex align-items-center p-2 rounded-3 bg-light mb-2">
                                <img src="<?php echo $course['course_img']; ?>" class="rounded-2 me-2" width="40" height="30" style="object-fit: cover;" alt="" onerror="this.src='https://via.placeholder.com/40x30/6366f1/ffffff?text=C'">
                                <div class="flex-grow-1 min-width-0">
                                    <h6 class="mb-0 small fw-bold text-truncate"><?php echo $course['course_name']; ?></h6>
                                    <div class="d-flex align-items-center gap-2 mt-1">
                                        <div class="progress flex-grow-1" style="height: 4px;">
                                            <div class="progress-bar bg-success" style="width: <?php echo $course['progress']; ?>%"></div>
                                        </div>
                                        <small class="text-muted" style="font-size: 0.7rem;"><?php echo $course['progress']; ?>%</small>
                                    </div>
                                    <small class="text-muted" style="font-size: 0.7rem;"><?php echo $course['completed_lessons']; ?>/<?php echo $course['total_lessons']; ?> درس</small>
                                </div>
                            </div>
                            <?php } ?>
                        </div>
                    </div>
                    <?php } else { ?>
                    <div class="text-center py-3 bg-light rounded-3 mb-3">
                        <i class="fas fa-book-open text-muted mb-2"></i>
                        <p class="text-muted small mb-0">لم يسجل في أي دورة بعد</p>
                    </div>
                    <?php } ?>

                    <!-- Actions -->
                    <div class="d-flex gap-2">
                        <a href="?toggle=<?php echo $student['user_id']; ?>" class="btn btn-sm btn-warning rounded-pill flex-grow-1" title="تبديل الحالة" onclick="return confirm('تبديل حالة الطالب؟')">
                            <i class="fas fa-toggle-on me-1"></i> تبديل الحالة
                        </a>
                        <a href="?delete=<?php echo $student['user_id']; ?>" class="btn btn-sm btn-danger rounded-pill" onclick="return confirm('هل أنت متأكد من حذف هذا الطالب؟')" title="حذف">
                            <i class="fas fa-trash"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>
        <?php } } else { ?>
        <div class="col-12">
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-body text-center py-5">
                    <i class="fas fa-users fa-4x text-muted mb-3"></i>
                    <h4 class="fw-bold">لا يوجد طلاب</h4>
                    <p class="text-muted">سيتم عرض الطلاب المسجلين هنا</p>
                </div>
            </div>
        </div>
        <?php } ?>
    </div>
</div>

<script>
function toggleSidebar() {
    var sidebar = document.getElementById('adminSidebar');
    sidebar.classList.toggle('active');
}
document.addEventListener('click', function(e) {
    var sidebar = document.getElementById('adminSidebar');
    var toggleBtn = document.querySelector('.btn-light');
    if (window.innerWidth < 992) {
        if (!sidebar.contains(e.target) && e.target !== toggleBtn && !toggleBtn.contains(e.target)) {
            sidebar.classList.remove('active');
        }
    }
});
document.addEventListener('DOMContentLoaded', function() {
    var cards = document.querySelectorAll('.fade-in');
    cards.forEach(function(card, index) { card.style.animationDelay = (index * 0.1) + 's'; });
});
</script>

<?php include('../includes/footer.php'); ?>
