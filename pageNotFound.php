<?php
include('./includes/header.php');
?>

<section class="py-5 min-vh-100 d-flex align-items-center">
    <div class="container text-center">
        <div class="mb-4">
            <i class="fas fa-exclamation-triangle fa-5x text-warning"></i>
        </div>
        <h1 class="display-1 fw-bold text-primary mb-3">404</h1>
        <h2 class="fw-bold mb-3">الصفحة غير موجودة</h2>
        <p class="text-muted mb-4 fs-5">عذراً، الصفحة التي تبحث عنها غير موجودة أو تم نقلها.</p>
        <div class="d-flex justify-content-center gap-3">
            <a href="index.php" class="btn btn-primary btn-lg px-5 rounded-pill">
                <i class="fas fa-home me-2"></i> العودة للرئيسية
            </a>
            <a href="courses.php" class="btn btn-outline-primary btn-lg px-5 rounded-pill">
                <i class="fas fa-book me-2"></i> تصفح الدورات
            </a>
        </div>
    </div>
</section>

<?php include('./includes/footer.php'); ?>