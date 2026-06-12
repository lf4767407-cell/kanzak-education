
<?php
session_start();

// Get user name before destroying session
$user_name = $_SESSION['stu_name'] ?? 'زائر';
$user_id = $_SESSION['stu_id'] ?? null;

// Clear all session data
$_SESSION = array();

// Destroy session cookie
if (isset($_COOKIE[session_name()])) {
    setcookie(session_name(), '', time()-3600, '/');
}

// Destroy session
session_destroy();

// Show logout confirmation page
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
        * { font-family: 'Cairo', sans-serif; }
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .logout-card {
            background: white;
            border-radius: 24px;
            padding: 3rem;
            text-align: center;
            box-shadow: 0 30px 60px rgba(0,0,0,0.3);
            max-width: 500px;
            width: 90%;
        }
        .logout-icon {
            width: 100px;
            height: 100px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
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
            <a href="loginorsignup.php" class="btn btn-primary rounded-pill px-4 fw-bold">
                <i class="fas fa-sign-in-alt me-2"></i> تسجيل الدخول مجدداً
            </a>
            <a href="index.php" class="btn btn-outline-secondary rounded-pill px-4 fw-bold">
                <i class="fas fa-home me-2"></i> الصفحة الرئيسية
            </a>
        </div>
        <div class="mt-4 text-muted small">
            <i class="fas fa-heart text-danger me-1"></i>
            كنزك التعليمي - ليبيا 🇱🇾
        </div>
    </div>

    <!-- Auto redirect after 5 seconds -->
    <script>
        setTimeout(function() {
            window.location.href = 'index.php';
        }, 5000);
    </script>
</body>
</html>

