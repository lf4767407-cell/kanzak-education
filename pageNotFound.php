<?php
// 1. مفتاح الربط الرئيسي: استدعاء ملف التأسيس لفتح قفل الاتصال بقاعدة البيانات $conn
// هذا يضمن أن الصفحة تسير على نفس نهج ونظام باقي ملفات الموقع وتستخدم نفس الإعدادات الموحدة
include('./dbConnection.php');

// 2. استدعاء رأس الصفحة المشترك (الشعار، قائمة التنقل العلوية، والتحقق من الجلسات النشطة)
// بفضل استدعاء ملف dbConnection بالسطر الأول، سيتمكن الهيدر الآن من التعرف على بيانات الطالب
// المسجل دخوله (اسمه وصورته) وعرضها بشكل صحيح حتى داخل صفحة الخطأ بدلاً من إظهاره كزائر غريب
include('./includes/header.php');
?>

<section class="py-5 min-vh-100 d-flex align-items-center" style="direction: rtl;">
    <div class="container text-center">
        <div class="mb-4">
            <i class="fas fa-exclamation-triangle fa-5x text-warning"></i>
        </div>
        
        <h1 class="display-1 fw-bold text-primary mb-3">404</h1>
        <h2 class="fw-bold mb-3">الصفحة غير موجودة</h2>
        <p class="text-muted mb-4 fs-5">عذراً، الصفحة التي تبحث عنها غير موجودة أو تم نقلها برابط آخر.</p>
        
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

<?php 
// 3. استدعاء ملف الفوتر (تذييل الموقع) لإغلاق أوسمة الصفحات المفتوحة وإدراج ملفات الجافا سكريبت والتنسيقات
include('./includes/footer.php'); 
?>