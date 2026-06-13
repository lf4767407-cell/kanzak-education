<?php
// 1. استدعاء ملف الخزنة والأدوات لتوحيد الاتصال واستخدام نفس إعدادات النظام المتبعة
include('./dbConnection.php');

// 2. لقطة الوداع: نسحب اسم الطالب ورقم المعرف الخاص به من الجلسة (التي بدأت بالفعل داخل dbConnection) قبل إتلافها لوداعه باسمه
$user_name = $_SESSION['stu_name'] ?? 'زائر';
$user_id = $_SESSION['stu_id'] ?? null;

// 3. تصفير كشكول الجلسة: نقوم بإفراغ مصفوفة الـ SESSION تماماً ومحو أي أثر لبيانات الدخول الحالية
$_SESSION = array();

// 4. تدمير ملفات تعريف الارتباط (Cookies): إذا كان المتصفح يحتفظ بمعرف الجلسة الآلي..
if (isset($_COOKIE[session_name()])) {
    // نأمر المتصفح بحذف ملف الكوكي فوراً عبر إرجاع تاريخ صلاحيته إلى الماضي (time()-3600)
    setcookie(session_name(), '', time()-3600, '/');
}

// 5. الإعدام النهائي: نغلق وندمر ملف الجلسة على السيرفر بالكامل، وبذلك يخرج الطالب بأمان ونظام
session_destroy();

// ═══════════════════════════════════════════════════════════════
//  الجزء الثاني: واجهة العرض والتصميم (HTML5 / Bootstrap 5)
// ═══════════════════════════════════════════════════════════════
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تسجيل الخروج - كنزك التعليمي</title>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.rtl.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        /* أنماط وتنسيقات مخصصة لصفحة تسجيل الخروج لتبدو بمظهر شاشات الخروج الاحترافية */
        * { font-family: 'Cairo', sans-serif; }
        body {
            /* خلفية متدرجة سينمائية مريحة للعين ومعبرة عن الخروج */
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        /* كرت الوداع الأبيض المنبثق بتأثيرات الظلال العميقة والزوايا الناعمة */
        .logout-card {
            background: white;
            padding: 2.5rem;
            border-radius: 24px;
            box-shadow: 0 15px 35px rgba(0,0,0,0.2);
            max-width: 500px;
            width: 100%;
            text-align: center;
            animation: fadeInUp 0.6s ease-out; /* تأثير حركي ناعم يرتفع للأعلى عند فتح الصفحة لزيادة الجاذبية */
        }
        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        /* تصميم اللوحة البنفسجية المحيطة بأيقونة الباب التعبيرية */
        .logout-icon {
            width: 90px;
            height: 90px;
            background: linear-gradient(135deg, #6366f1, #4f46e5);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1.5rem;
            font-size: 3rem;
            color: white;
        }
        .user-name {
            color: #667eea;
            font-weight: 800;
        }
    </style>
</head>
<body>

    <div class="logout-card">
        <div class="logout-icon">
            <i class="fas fa-sign-out-alt"></i>
        </div>
        
        <h2 class="fw-bold mb-3">تم تسجيل الخروج بنجاح</h2>
        
        <p class="text-muted mb-4">
            إلى اللقاء، <span class="user-name"><?php echo htmlspecialchars($user_name); ?></span>! 👋<br>
            نتمنى أن تكون قد استفدت من وقتك في <strong>كنزك التعليمي</strong>.
        </p>
        
        <div class="d-flex gap-3 justify-content-center">
            <a href="loginorsignup.php" class="btn btn-primary rounded-pill px-4 fw-bold shadow-sm">
                <i class="fas fa-sign-in-alt me-2"></i> تسجيل الدخول مجدداً
            </a>
            <a href="index.php" class="btn btn-outline-secondary rounded-pill px-4 fw-bold">
                <i class="fas fa-home me-2"></i> الصفحة الرئيسية
            </a>
        </div>
        
        <div class="mt-4 text-muted small">
            <i class="fas fa-heart text-danger me-1"></i> طورت بحب في منصة كنزك
        </div>
    </div>

</body>
</html>