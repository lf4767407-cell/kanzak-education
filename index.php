<?php
include('../dbConnection.php');

if(!isset($_SESSION['is_login']) || !isset($_SESSION['stuLogEmail']) || $_SESSION['stuLogEmail'] != 'admin@kanzak.com') {
    redirect('../index.php');
}

// Stats
$students_count = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM users WHERE role = 'student'"))['count'];
$courses_count = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM courses WHERE status = 'active'"))['count'];
$orders_count = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM orders WHERE payment_status = 'completed'"))['count'];
$revenue_total = mysqli_fetch_assoc(mysqli_query($conn, "SELECT SUM(amount) as total FROM orders WHERE payment_status = 'completed'"))['total'] ?? 0;
$lessons_count = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM lessons"))['count'];
$contacts_count = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM contacts"))['count'];

$stats = ['students' => $students_count, 'courses' => $courses_count, 'orders' => $orders_count, 'revenue' => $revenue_total, 'lessons' => $lessons_count, 'contacts' => $contacts_count];

$recent_orders = mysqli_query($conn, "SELECT o.*, u.name, c.course_name FROM orders o JOIN users u ON o.user_id = u.user_id JOIN courses c ON o.course_id = c.course_id ORDER BY o.created_at DESC LIMIT 5");
$recent_contacts = mysqli_query($conn, "SELECT * FROM contacts ORDER BY created_at DESC LIMIT 5");
$top_courses = mysqli_query($conn, "SELECT c.*, COUNT(o.order_id) as sales FROM courses c LEFT JOIN orders o ON c.course_id = o.course_id AND o.payment_status = 'completed' GROUP BY c.course_id ORDER BY sales DESC LIMIT 5");
$students = mysqli_query($conn, "SELECT u.*, COUNT(o.order_id) as courses_count FROM users u LEFT JOIN orders o ON u.user_id = o.user_id AND o.payment_status = 'completed' WHERE u.role = 'student' GROUP BY u.user_id ORDER BY u.created_at DESC LIMIT 10");

include('../includes/header.php');
?>

<style>
    .admin-sidebar { background: linear-gradient(180deg, #1e293b 0%, #0f172a 100%); min-height: 100vh; color: white; padding: 20px 0; position: fixed; right: 0; top: 0; width: 260px; z-index: 1000; transition: transform 0.3s ease; box-shadow: -5px 0 20px rgba(0,0,0,0.1); }
    .admin-main { margin-right: 260px; padding: 30px; min-height: 100vh; background: #f1f5f9; transition: margin-right 0.3s ease; }
    .admin-link { display: flex; align-items: center; gap: 12px; padding: 14px 24px; color: rgba(255,255,255,0.7); text-decoration: none; transition: all 0.3s; border-right: 3px solid transparent; margin: 4px 12px; border-radius: 12px; }
    .admin-link:hover, .admin-link.active { background: rgba(99, 102, 241, 0.2); color: white; border-right-color: #6366f1; }
    .admin-link i { width: 24px; text-align: center; font-size: 16px; }
    .stat-card-admin { background: white; border-radius: 16px; padding: 24px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); transition: all 0.3s; border: 1px solid #e2e8f0; }
    .stat-card-admin:hover { transform: translateY(-5px); box-shadow: 0 15px 35px rgba(0,0,0,0.1); }
    .stat-icon-admin { width: 56px; height: 56px; border-radius: 14px; display: flex; align-items: center; justify-content: center; font-size: 24px; margin-bottom: 16px; }
    .student-row:hover { background: #f8fafc; }
    @media (max-width: 991px) { .admin-sidebar { transform: translateX(100%); } .admin-sidebar.active { transform: translateX(0); } .admin-main { margin-right: 0; } }
    .fade-in { animation: fadeIn 0.5s ease-in; }
    @keyframes fadeIn { from { opacity: 0; transform: translateY(20px); } to { opacity: 1; transform: translateY(0); } }
    .table-card { background: white; border-radius: 16px; box-shadow: 0 2px 10px rgba(0,0,0,0.05); overflow: hidden; }
    .table-card .card-header { background: white; border-bottom: 1px solid #f1f5f9; padding: 20px 24px; }
    .admin-table { width: 100%; border-collapse: separate; border-spacing: 0; }
    .admin-table thead th { background: #f8fafc; padding: 14px 20px; font-weight: 700; color: #475569; border: none; font-size: 0.875rem; }
    .admin-table tbody td { padding: 14px 20px; border-bottom: 1px solid #f1f5f9; vertical-align: middle; }
    .admin-table tbody tr:hover { background: #f8fafc; }
    .badge-admin { padding: 6px 14px; border-radius: 50px; font-size: 0.75rem; font-weight: 600; }
</style>

<!-- Admin Sidebar -->
<div class="admin-sidebar" id="adminSidebar">
    <div class="text-center p-4 border-bottom border-secondary border-opacity-25">
        <div class="bg-white bg-opacity-10 rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 60px; height: 60px;">
            <i class="fas fa-shield-alt fa-2x text-primary"></i>
        </div>
        <h5 class="fw-bold mb-1">لوحة التحكم</h5>
        <small class="text-white-50">كنزك التعليمي</small>
    </div>

    <nav class="mt-3">
        <a href="index.php" class="admin-link active">
            <i class="fas fa-tachometer-alt"></i> الرئيسية
        </a>
        <a href="students.php" class="admin-link">
            <i class="fas fa-users"></i> الطلاب
        </a>
        <a href="courses.php" class="admin-link">
            <i class="fas fa-book"></i> الدورات
        </a>
        <a href="lessons.php" class="admin-link">
            <i class="fas fa-video"></i> الدروس
        </a>
        <a href="orders.php" class="admin-link">
            <i class="fas fa-shopping-cart"></i> الطلبات
        </a>
        <a href="contacts.php" class="admin-link">
            <i class="fas fa-envelope"></i> الرسائل
            <?php if($stats['contacts'] > 0) { ?>
            <span class="badge bg-danger ms-auto rounded-pill"><?php echo $stats['contacts']; ?></span>
            <?php } ?>
        </a>
        <div class="mt-4 pt-4 border-top border-secondary border-opacity-25">
            <a href="../index.php" target="_blank" class="admin-link text-warning">
                <i class="fas fa-globe"></i> الموقع
            </a>
            <a href="../logout.php" class="admin-link text-danger">
                <i class="fas fa-sign-out-alt"></i> تسجيل الخروج
            </a>
        </div>
    </nav>
</div>

<!-- Admin Main -->
<div class="admin-main">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold mb-0">لوحة التحكم</h4>
            <small class="text-muted">نظرة عامة على المنصة</small>
        </div>
        <button class="btn btn-light d-lg-none" id="sidebarToggle" onclick="toggleSidebar()">
            <i class="fas fa-bars"></i>
        </button>
    </div>

    <!-- Stats Cards -->
    <div class="row g-4 mb-4 fade-in">
        <div class="col-md-6 col-lg-4 col-xl-2">
            <div class="stat-card-admin">
                <div class="stat-icon-admin bg-primary bg-opacity-10 text-primary">
                    <i class="fas fa-users"></i>
                </div>
                <h3 class="fw-bold mb-1"><?php echo $stats['students']; ?></h3>
                <small class="text-muted">طالب</small>
            </div>
        </div>
        <div class="col-md-6 col-lg-4 col-xl-2">
            <div class="stat-card-admin">
                <div class="stat-icon-admin bg-success bg-opacity-10 text-success">
                    <i class="fas fa-book"></i>
                </div>
                <h3 class="fw-bold mb-1"><?php echo $stats['courses']; ?></h3>
                <small class="text-muted">دورة</small>
            </div>
        </div>
        <div class="col-md-6 col-lg-4 col-xl-2">
            <div class="stat-card-admin">
                <div class="stat-icon-admin bg-warning bg-opacity-10 text-warning">
                    <i class="fas fa-shopping-cart"></i>
                </div>
                <h3 class="fw-bold mb-1"><?php echo $stats['orders']; ?></h3>
                <small class="text-muted">طلب</small>
            </div>
        </div>
        <div class="col-md-6 col-lg-4 col-xl-2">
            <div class="stat-card-admin">
                <div class="stat-icon-admin bg-danger bg-opacity-10 text-danger">
                    <i class="fas fa-money-bill"></i>
                </div>
                <h3 class="fw-bold mb-1">₹<?php echo number_format($stats['revenue'], 2); ?></h3>
                <small class="text-muted">إيرادات</small>
            </div>
        </div>
        <div class="col-md-6 col-lg-4 col-xl-2">
            <div class="stat-card-admin">
                <div class="stat-icon-admin bg-info bg-opacity-10 text-info">
                    <i class="fas fa-video"></i>
                </div>
                <h3 class="fw-bold mb-1"><?php echo $stats['lessons']; ?></h3>
                <small class="text-muted">درس</small>
            </div>
        </div>
        <div class="col-md-6 col-lg-4 col-xl-2">
            <div class="stat-card-admin">
                <div class="stat-icon-admin bg-secondary bg-opacity-10 text-secondary">
                    <i class="fas fa-envelope"></i>
                </div>
                <h3 class="fw-bold mb-1"><?php echo $stats['contacts']; ?></h3>
                <small class="text-muted">رسائل</small>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <!-- Recent Orders -->
        <div class="col-lg-8 fade-in">
            <div class="table-card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="fw-bold mb-0"><i class="fas fa-shopping-cart text-primary me-2"></i> آخر الطلبات</h5>
                    <a href="orders.php" class="btn btn-sm btn-primary rounded-pill">عرض الكل</a>
                </div>
                <div class="table-responsive">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>رقم الطلب</th>
                                <th>الطالب</th>
                                <th>الدورة</th>
                                <th>المبلغ</th>
                                <th>الحالة</th>
                                <th>التاريخ</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if(mysqli_num_rows($recent_orders) > 0) { 
                                while($order = mysqli_fetch_assoc($recent_orders)) { 
                            ?>
                            <tr>
                                <td><code class="bg-light px-2 py-1 rounded"><?php echo $order['order_no']; ?></code></td>
                                <td class="fw-bold"><?php echo $order['name']; ?></td>
                                <td><?php echo $order['course_name']; ?></td>
                                <td class="fw-bold text-primary">₹<?php echo number_format($order['amount'], 2); ?></td>
                                <td>
                                    <?php if($order['payment_status'] == 'completed') { ?>
                                        <span class="badge-admin bg-success bg-opacity-10 text-success">مكتمل</span>
                                    <?php } elseif($order['payment_status'] == 'pending') { ?>
                                        <span class="badge-admin bg-warning bg-opacity-10 text-warning">معلق</span>
                                    <?php } else { ?>
                                        <span class="badge-admin bg-danger bg-opacity-10 text-danger">فاشل</span>
                                    <?php } ?>
                                </td>
                                <td><?php echo date('Y-m-d', strtotime($order['created_at'])); ?></td>
                            </tr>
                            <?php } } else { ?>
                            <tr><td colspan="6" class="text-center py-4 text-muted">لا توجد طلبات</td></tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Top Courses -->
        <div class="col-lg-4 fade-in">
            <div class="table-card h-100">
                <div class="card-header">
                    <h5 class="fw-bold mb-0"><i class="fas fa-chart-line text-primary me-2"></i> أفضل الدورات</h5>
                </div>
                <div class="p-3">
                    <?php if(mysqli_num_rows($top_courses) > 0) { 
                        while($course = mysqli_fetch_assoc($top_courses)) { 
                    ?>
                    <div class="d-flex align-items-center mb-3 pb-3 border-bottom">
                        <img src="<?php echo $course['course_img']; ?>" class="rounded-3 me-3" width="60" height="45" style="object-fit: cover;" alt="" onerror="this.src='https://via.placeholder.com/60x45/6366f1/ffffff?text=C'">
                        <div class="flex-grow-1">
                            <h6 class="mb-0 small fw-bold"><?php echo $course['course_name']; ?></h6>
                            <small class="text-muted"><?php echo $course['sales']; ?> مبيعة</small>
                        </div>
                        <span class="fw-bold text-primary">₹<?php echo number_format($course['course_price'], 2); ?></span>
                    </div>
                    <?php } } else { ?>
                    <p class="text-muted text-center py-3">لا توجد بيانات</p>
                    <?php } ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Students Table with Courses -->
    <div class="table-card mt-4 fade-in">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="fw-bold mb-0"><i class="fas fa-users text-primary me-2"></i> الطلاب والدورات المسجل بها</h5>
            <a href="students.php" class="btn btn-sm btn-primary rounded-pill">عرض الكل</a>
        </div>
        <div class="table-responsive">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>الطالب</th>
                        <th>البريد</th>
                        <th>الدورات المسجل بها</th>
                        <th>تاريخ التسجيل</th>
                        <th>الحالة</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(mysqli_num_rows($students) > 0) { 
                        $num = 0;
                        while($student = mysqli_fetch_assoc($students)) { 
                            $num++;
                            $stu_id = $student['user_id'];
                            $stu_courses_sql = "SELECT c.course_name FROM courses c JOIN orders o ON c.course_id = o.course_id WHERE o.user_id = '$stu_id' AND o.payment_status = 'completed' ORDER BY o.created_at DESC LIMIT 3";
                            $stu_courses = mysqli_query($conn, $stu_courses_sql);
                            $stu_courses_count = mysqli_num_rows($stu_courses);
                    ?>
                    <tr class="student-row">
                        <td><?php echo $num; ?></td>
                        <td>
                            <div class="d-flex align-items-center">
                                <img src="<?php echo $student['avatar'] ?? 'https://ui-avatars.com/api/?name='.urlencode($student['name']).'&background=random'; ?>" 
                                     class="rounded-circle me-2" width="35" height="35" alt="">
                                <span class="fw-bold"><?php echo $student['name']; ?></span>
                            </div>
                        </td>
                        <td><?php echo $student['email']; ?></td>
                        <td>
                            <?php if($stu_courses_count > 0) { ?>
                                <div class="d-flex flex-wrap gap-1">
                                    <?php while($sc = mysqli_fetch_assoc($stu_courses)) { ?>
                                        <span class="badge bg-primary bg-opacity-10 text-primary"><?php echo $sc['course_name']; ?></span>
                                    <?php } ?>
                                    <?php if($student['courses_count'] > 3) { ?>
                                        <span class="badge bg-secondary">+<?php echo $student['courses_count'] - 3; ?></span>
                                    <?php } ?>
                                </div>
                            <?php } else { ?>
                                <span class="text-muted">-</span>
                            <?php } ?>
                        </td>
                        <td><?php echo date('Y-m-d', strtotime($student['created_at'])); ?></td>
                        <td>
                            <?php if($student['status'] == 'active') { ?>
                                <span class="badge-admin bg-success bg-opacity-10 text-success">نشط</span>
                            <?php } else { ?>
                                <span class="badge-admin bg-secondary bg-opacity-10 text-secondary">غير نشط</span>
                            <?php } ?>
                        </td>
                    </tr>
                    <?php } } else { ?>
                    <tr><td colspan="6" class="text-center py-4 text-muted">لا يوجد طلاب</td></tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Recent Messages -->
    <div class="table-card mt-4 fade-in">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="fw-bold mb-0"><i class="fas fa-envelope text-primary me-2"></i> آخر الرسائل</h5>
            <a href="contacts.php" class="btn btn-sm btn-primary rounded-pill">عرض الكل</a>
        </div>
        <div class="table-responsive">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>الاسم</th>
                        <th>البريد</th>
                        <th>الموضوع</th>
                        <th>الرسالة</th>
                        <th>التاريخ</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(mysqli_num_rows($recent_contacts) > 0) { 
                        while($contact = mysqli_fetch_assoc($recent_contacts)) { 
                    ?>
                    <tr>
                        <td class="fw-bold"><?php echo $contact['name']; ?></td>
                        <td><?php echo $contact['email']; ?></td>
                        <td><?php echo $contact['subject'] ?? '-'; ?></td>
                        <td><?php echo substr($contact['message'], 0, 50); ?>...</td>
                        <td><?php echo date('Y-m-d', strtotime($contact['created_at'])); ?></td>
                    </tr>
                    <?php } } else { ?>
                    <tr><td colspan="5" class="text-center py-4 text-muted">لا توجد رسائل</td></tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
function toggleSidebar() {
    var sidebar = document.getElementById('adminSidebar');
    sidebar.classList.toggle('active');
}

document.addEventListener('click', function(e) {
    var sidebar = document.getElementById('adminSidebar');
    var toggle = document.getElementById('sidebarToggle');
    if (window.innerWidth < 992) {
        if (!sidebar.contains(e.target) && e.target !== toggle && !toggle.contains(e.target)) {
            sidebar.classList.remove('active');
        }
    }
});

document.addEventListener('DOMContentLoaded', function() {
    var cards = document.querySelectorAll('.fade-in');
    cards.forEach(function(card, index) {
        card.style.animationDelay = (index * 0.1) + 's';
    });
});
</script>

<?php include('../includes/footer.php'); ?>