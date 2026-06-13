<?php
// ═══════════════════════════════════════════════════════════════
//  كنزك التعليمي - الاتصال بقاعدة البيانات (MySQLi Procedural)
// ═══════════════════════════════════════════════════════════════

$db_host = "localhost";
$db_user = "root";
$db_password = "";
$db_name = "kanzak_db";

// ─── إنشاء الاتصال ───
$conn = mysqli_connect($db_host, $db_user, $db_password, $db_name);

// ─── التحقق من الاتصال ───
if(!$conn) {
    die("<div style='direction:rtl;text-align:right;padding:20px;' class='alert alert-danger'>
        <i class='fas fa-exclamation-circle me-2'></i>
        فشل الاتصال بقاعدة البيانات: " . mysqli_connect_error() . "
    </div>");
}

// ─── تعيين الترميز ───
mysqli_set_charset($conn, "utf8mb4");

// ─── بدء الجلسة ───
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ═══════════════════════════════════════════════════════════════
//  دوال الأمان والمساعدة
// ═══════════════════════════════════════════════════════════════

/**
 * تنظيف البيانات المدخلة
 */
function sanitize($data) {
    global $conn;
    if ($data === null) return '';
    $data = trim($data);
    $data = strip_tags($data);
    $data = mysqli_real_escape_string($conn, $data);
    $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    return $data;
}

/**
 * إعادة التوجيه
 */
function redirect($url) {
    echo "<script>window.location.href='" . $url . "';</script>";
    exit();
}

/**
 * عرض رسالة تنبيه
 */
function showAlert($message, $type = 'success') {
    $classes = [
        'success' => 'alert-success',
        'error'   => 'alert-danger',
        'warning' => 'alert-warning',
        'info'    => 'alert-info'
    ];
    $class = $classes[$type] ?? $classes['info'];
    $icons = [
        'success' => 'fa-check-circle',
        'error'   => 'fa-times-circle',
        'warning' => 'fa-exclamation-triangle',
        'info'    => 'fa-info-circle'
    ];
    $icon = $icons[$type] ?? $icons['info'];

    return "
    <div class='alert $class alert-dismissible fade show rounded-4 border-0 shadow-sm' style='direction:rtl;' role='alert'>
        <i class='fas $icon me-2'></i>
        <strong>$message</strong>
        <button type='button' class='btn-close' onclick='this.parentElement.remove()'></button>
    </div>";
}

/**
 * التحقق من تسجيل الدخول
 */
function isLoggedIn() {
    return isset($_SESSION['is_login']) && $_SESSION['is_login'] === true 
           && isset($_SESSION['stu_id']) && !empty($_SESSION['stu_id']);
}

/**
 * الحصول على معرف المستخدم
 */
function getUserId() {
    return $_SESSION['stu_id'] ?? null;
}

/**
 * الحصول على اسم المستخدم
 */
function getUserName() {
    return $_SESSION['stu_name'] ?? 'زائر';
}

/**
 * الحصول على بريد المستخدم
 */
function getUserEmail() {
    return $_SESSION['stuLogEmail'] ?? '';
}

/**
 * الحصول على صورة المستخدم
 */
function getUserAvatar() {
    if(isset($_SESSION['stu_img']) && !empty($_SESSION['stu_img'])) {
        $avatar_path = $_SESSION['stu_img'];
        if(strpos($avatar_path, 'http') === 0) {
            return $avatar_path;
        }
        if(file_exists($avatar_path)) {
            return $avatar_path;
        }
    }
    $name = urlencode(getUserName());
    return "https://ui-avatars.com/api/?name=$name&background=6366f1&color=fff&size=150";
}

/**
 * التحقق من صلاحية المستخدم
 */
function requireLogin() {
    if(!isLoggedIn()) {
        $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'];
        redirect('loginorsignup.php');
    }
}

/**
 * تسجيل الخروج الآمن
 */
function logout() {
    $_SESSION = array();
    if (isset($_COOKIE[session_name()])) {
        setcookie(session_name(), '', time()-3600, '/');
    }
    session_destroy();
}

/**
 * تنسيق التاريخ
 */
function formatDate($date, $format = 'Y-m-d') {
    if(empty($date)) return 'غير محدد';
    return date($format, strtotime($date));
}

/**
 * اختصار النص
 */
function truncateText($text, $length = 100) {
    if(strlen($text) <= $length) return $text;
    return substr($text, 0, $length) . '...';
}

/**
 * الحصول على عدد الدروس المكتملة
 */
function getCompletedLessons($user_id, $course_id) {
    global $conn;
    $sql = "SELECT COUNT(*) as total FROM progress WHERE user_id = '$user_id' AND course_id = '$course_id' AND is_completed = 'yes'";
    $result = mysqli_query($conn, $sql);
    return mysqli_fetch_assoc($result)['total'] ?? 0;
}

/**
 * الحصول على إجمالي الدروس
 */
function getTotalLessons($course_id) {
    global $conn;
    $sql = "SELECT COUNT(*) as total FROM lessons WHERE course_id = '$course_id'";
    $result = mysqli_query($conn, $sql);
    return mysqli_fetch_assoc($result)['total'] ?? 0;
}

/**
 * حساب نسبة التقدم
 */
function getProgress($user_id, $course_id) {
    $total = getTotalLessons($course_id);
    if($total == 0) return 0;
    $completed = getCompletedLessons($user_id, $course_id);
    return round(($completed / $total) * 100);
}

/**
 * التحقق من شراء الدورة
 */
function isCoursePurchased($user_id, $course_id) {
    global $conn;
    $sql = "SELECT * FROM orders WHERE user_id = '$user_id' AND course_id = '$course_id' AND payment_status = 'completed'";
    $result = mysqli_query($conn, $sql);
    return mysqli_num_rows($result) > 0;
}

/**
 * الحصول على الدورات المسجل فيها الطالب
 */
function getStudentCourses($user_id) {
    global $conn;
    $sql = "SELECT c.*, o.created_at as enrolled_date, o.order_no
            FROM courses c
            JOIN orders o ON c.course_id = o.course_id
            WHERE o.user_id = '$user_id' AND o.payment_status = 'completed'
            ORDER BY o.created_at DESC";
    return mysqli_query($conn, $sql);
}

/**
 * الحصول على معلومات الطالب مع دوراته
 */
function getStudentWithCourses($user_id) {
    global $conn;
    $sql = "SELECT u.*, COUNT(o.order_id) as courses_count 
            FROM users u 
            LEFT JOIN orders o ON u.user_id = o.user_id AND o.payment_status = 'completed'
            WHERE u.user_id = '$user_id' AND u.role = 'student'
            GROUP BY u.user_id";
    $result = mysqli_query($conn, $sql);
    return mysqli_fetch_assoc($result);
}

/**
 * استخراج معرف YouTube من الرابط
 */
function extractYouTubeId($url) {
    $pattern = '/(?:youtube\.com\/(?:[^\/]+\/.+\/|(?:v|e(?:mbed)?)\/|.*[?&]v=)|youtu\.be\/)([^"&?\/\s]{11})/';
    if(preg_match($pattern, $url, $matches)) {
        return $matches[1];
    }
    return $url;
}

/**
 * التحقق من صلاحية الأدمن
 */
function isAdmin() {
    return isset($_SESSION['stuLogEmail']) && $_SESSION['stuLogEmail'] == 'admin@kanzak.com';
}

/**
 * الحصول على معرف Vimeo من الرابط
 */
function extractVimeoId($url) {
    if(preg_match('/vimeo\.com\/(\d+)/', $url, $matches)) {
        return $matches[1];
    }
    return $url;
}

/**
 * رفع ملف الفيديو بشكل آمن
 */
function uploadVideoFile($file, $upload_dir = './uploads/videos/') {
    $allowed_types = ['video/mp4', 'video/webm', 'video/ogg', 'video/quicktime', 'video/x-msvideo', 'video/x-matroska'];
    $allowed_exts = ['mp4', 'webm', 'ogg', 'mov', 'avi', 'mkv'];
    $max_size = 500 * 1024 * 1024; // 500MB
    
    if(!is_dir($upload_dir)) {
        mkdir($upload_dir, 0755, true);
    }
    
    // التحقق من وجود ملف
    if(!isset($file) || $file['error'] !== UPLOAD_ERR_OK) {
        return ['error' => 'لم يتم رفع الملف أو حدث خطأ في الرفع'];
    }
    
    // التحقق من الحجم
    if($file['size'] > $max_size) {
        return ['error' => 'حجم الملف كبير جداً. الحد الأقصى 500 ميجابايت'];
    }
    
    // التحقق من نوع الملف باستخدام MIME
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime_type = $finfo->file($file['tmp_name']);
    
    if(!in_array($mime_type, $allowed_types)) {
        return ['error' => 'نوع الملف غير مدعوم. الأنواع المدعومة: MP4, WebM, OGG, MOV, AVI'];
    }
    
    // التحقق من الامتداد
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if(!in_array($ext, $allowed_exts)) {
        return ['error' => 'امتداد الملف غير مدعوم'];
    }
    
    // إنشاء اسم فريد
    $filename = 'video_' . time() . '_' . uniqid() . '.' . $ext;
    $filepath = $upload_dir . $filename;
    
    if(move_uploaded_file($file['tmp_name'], $filepath)) {
        return ['success' => true, 'path' => $filepath, 'filename' => $filename];
    }
    
    return ['error' => 'فشل في نقل الملف إلى المجلد المستهدف'];
}

/**
 * رفع صورة بشكل آمن
 */
function uploadImageFile($file, $upload_dir = './uploads/images/') {
    $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    $allowed_exts = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    $max_size = 5 * 1024 * 1024; // 5MB
    
    if(!is_dir($upload_dir)) {
        mkdir($upload_dir, 0755, true);
    }
    
    if(!isset($file) || $file['error'] !== UPLOAD_ERR_OK) {
        return ['error' => 'لم يتم رفع الملف'];
    }
    
    if($file['size'] > $max_size) {
        return ['error' => 'حجم الملف كبير جداً. الحد الأقصى 5 ميجابايت'];
    }
    
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime_type = $finfo->file($file['tmp_name']);
    
    if(!in_array($mime_type, $allowed_types)) {
        return ['error' => 'نوع الملف غير مدعوم. الأنواع المدعومة: JPG, PNG, GIF, WEBP'];
    }
    
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $filename = 'img_' . time() . '_' . uniqid() . '.' . $ext;
    $filepath = $upload_dir . $filename;
    
    if(move_uploaded_file($file['tmp_name'], $filepath)) {
        return ['success' => true, 'path' => $filepath, 'filename' => $filename];
    }
    
    return ['error' => 'فشل في رفع الصورة'];
}