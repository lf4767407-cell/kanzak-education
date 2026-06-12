<?php
/**
 * Admin Sidebar - كنزك التعليمي
 * Shared sidebar for all admin pages
 */

// Get unread contacts count for badge
$contacts_badge = 0;
$contacts_result = mysqli_query($conn, "SELECT COUNT(*) as count FROM contacts WHERE is_read = 'no'");
if($contacts_result) {
    $contacts_badge = mysqli_fetch_assoc($contacts_result)['count'] ?? 0;
}

// Get current page name
$current_page = basename($_SERVER['PHP_SELF']);
?>

<!-- Admin Sidebar -->
<div class="admin-sidebar" id="adminSidebar">
    <div class="text-center p-4 border-bottom border-secondary">
        <i class="fas fa-shield-alt fa-2x text-primary mb-2"></i>
        <h5 class="fw-bold mb-0">لوحة التحكم</h5>
        <small class="text-white-50">كنزك التعليمي</small>
    </div>

    <nav class="mt-3">
        <a href="dashboard.php" class="admin-link <?php echo $current_page == 'dashboard.php' ? 'active' : ''; ?>">
            <i class="fas fa-tachometer-alt"></i> الرئيسية
        </a>
        <a href="students.php" class="admin-link <?php echo $current_page == 'students.php' ? 'active' : ''; ?>">
            <i class="fas fa-users"></i> الطلاب
        </a>
        <a href="courses.php" class="admin-link <?php echo $current_page == 'courses.php' ? 'active' : ''; ?>">
            <i class="fas fa-book"></i> الدورات
        </a>
        <a href="lessons.php" class="admin-link <?php echo $current_page == 'lessons.php' ? 'active' : ''; ?>">
            <i class="fas fa-video"></i> الدروس
        </a>
        <a href="orders.php" class="admin-link <?php echo $current_page == 'orders.php' ? 'active' : ''; ?>">
            <i class="fas fa-shopping-cart"></i> الطلبات
        </a>
        <a href="contacts.php" class="admin-link <?php echo $current_page == 'contacts.php' ? 'active' : ''; ?>">
            <i class="fas fa-envelope"></i> الرسائل
            <?php if($contacts_badge > 0) { ?>
            <span class="badge bg-danger ms-auto"><?php echo $contacts_badge; ?></span>
            <?php } ?>
        </a>
        <a href="videos.php" class="admin-link <?php echo $current_page == 'videos.php' ? 'active' : ''; ?>">
            <i class="fas fa-video"></i> الفيديوهات
        </a>
        <a href="categories.php" class="admin-link <?php echo $current_page == 'categories.php' ? 'active' : ''; ?>">
            <i class="fas fa-tags"></i> التصنيفات
        </a>
        <a href="../index.php" target="_blank" class="admin-link text-warning">
            <i class="fas fa-globe"></i> الموقع
        </a>
        <a href="../logout.php" class="admin-link text-danger">
            <i class="fas fa-sign-out-alt"></i> تسجيل الخروج
        </a>
    </nav>
</div>