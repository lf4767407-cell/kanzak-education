
<?php
// Ensure session is started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Get current page for active state
$current_page = basename($_SERVER['PHP_SELF']);

// Check login status
$is_logged_in = isset($_SESSION['is_login']) && $_SESSION['is_login'] === true;
$user_name = $_SESSION['stu_name'] ?? '';
$user_avatar = '';

if($is_logged_in && isset($_SESSION['stu_img'])) {
    $avatar = $_SESSION['stu_img'];
    if(strpos($avatar, 'http') === 0) {
        $user_avatar = $avatar;
    } elseif(file_exists($avatar)) {
        $user_avatar = $avatar;
    } else {
        $user_avatar = 'https://ui-avatars.com/api/?name=' . urlencode($user_name) . '&background=6366f1&color=fff&size=64';
    }
} else {
    $user_avatar = 'https://ui-avatars.com/api/?name=' . urlencode($user_name ?: 'User') . '&background=6366f1&color=fff&size=64';
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="كنزك التعليمي - منصة التعلم الإلكتروني الأولى في ليبيا">
    <title>كنزك التعليمي - منصة التعلم الإلكتروني 🇱🇾</title>
    
    <!-- Bootstrap CSS RTL -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.rtl.min.css">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Google Fonts - Arabic -->
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --primary: #6366f1;
            --primary-dark: #4f46e5;
            --secondary: #10b981;
            --accent: #f59e0b;
            --dark: #1e293b;
            --light: #f8fafc;
        }
        
        * {
            font-family: 'Cairo', sans-serif;
        }
        
        body {
            background-color: var(--light);
            overflow-x: hidden;
        }
        
        /* Navbar */
        .navbar-custom {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            box-shadow: 0 2px 20px rgba(0,0,0,0.08);
            padding: 0.8rem 0;
        }
        
        .navbar-brand {
            font-weight: 800;
            font-size: 1.5rem;
            color: var(--primary) !important;
        }
        
        .navbar-brand i {
            color: var(--accent);
        }
        
        .nav-link {
            font-weight: 600;
            color: var(--dark) !important;
            padding: 0.5rem 1rem !important;
            border-radius: 50px;
            transition: all 0.3s ease;
            margin: 0 0.2rem;
        }
        
        .nav-link:hover {
            background: var(--primary);
            color: white !important;
        }
        
        .nav-link.active {
            background: var(--primary);
            color: white !important;
        }
        
        /* User Avatar in Navbar */
        .nav-avatar {
            width: 38px;
            height: 38px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid var(--primary);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        
        .nav-avatar:hover {
            transform: scale(1.1);
            box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.3);
        }
        
        .dropdown-menu {
            border: none;
            box-shadow: 0 10px 40px rgba(0,0,0,0.15);
        }
        
        .dropdown-item {
            padding: 0.75rem 1.5rem;
            font-weight: 600;
            transition: all 0.2s ease;
        }
        
        .dropdown-item:hover {
            background: var(--primary);
            color: white !important;
        }
        
        .dropdown-item i {
            width: 20px;
            text-align: center;
        }
        
        /* Hero Section */
        .hero-section {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            position: relative;
            overflow: hidden;
        }
        
        .hero-section::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><circle cx="20" cy="20" r="2" fill="rgba(255,255,255,0.1)"/><circle cx="80" cy="40" r="3" fill="rgba(255,255,255,0.1)"/><circle cx="40" cy="80" r="2" fill="rgba(255,255,255,0.1)"/></svg>');
            background-size: 100px 100px;
        }
        
        .hero-title {
            font-size: 3.5rem;
            font-weight: 800;
            color: white;
            line-height: 1.2;
        }
        
        .hero-title span {
            color: var(--accent);
        }
        
        .hero-subtitle {
            font-size: 1.2rem;
            color: rgba(255,255,255,0.9);
            max-width: 500px;
        }
        
        .hero-btn {
            padding: 1rem 2rem;
            border-radius: 50px;
            font-weight: 700;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            transition: all 0.3s ease;
        }
        
        .hero-btn-primary {
            background: white;
            color: var(--primary);
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
        }
        
        .hero-btn-primary:hover {
            transform: translateY(-3px);
            box-shadow: 0 15px 40px rgba(0,0,0,0.3);
        }
        
        .hero-btn-outline {
            border: 2px solid white;
            color: white;
        }
        
        .hero-btn-outline:hover {
            background: white;
            color: var(--primary);
        }
        
        .hero-image {
            border-radius: 20px;
            box-shadow: 0 30px 60px rgba(0,0,0,0.3);
        }
        
        .floating-card {
            position: absolute;
            bottom: 20px;
            left: -20px;
            background: white;
            padding: 1rem 1.5rem;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
            animation: float 3s ease-in-out infinite;
        }
        
        @keyframes float {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-10px); }
        }
        
        /* Stats Banner */
        .stats-banner {
            background: white;
            border-radius: 20px;
            padding: 2rem;
            box-shadow: 0 10px 40px rgba(0,0,0,0.1);
        }
        
        .stat-item i {
            font-size: 2rem;
            margin-bottom: 0.5rem;
        }
        
        .stat-item h3 {
            font-weight: 800;
            color: var(--dark);
        }
        
        /* Course Cards */
        .course-card {
            background: white;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 5px 20px rgba(0,0,0,0.08);
            transition: all 0.3s ease;
            border: none;
        }
        
        .course-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 20px 40px rgba(0,0,0,0.15);
        }
        
        .course-image-wrapper {
            position: relative;
            overflow: hidden;
            height: 200px;
        }
        
        .course-image {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.5s ease;
        }
        
        .course-card:hover .course-image {
            transform: scale(1.1);
        }
        
        .course-overlay {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(99, 102, 241, 0.8);
            display: flex;
            align-items: center;
            justify-content: center;
            opacity: 0;
            transition: all 0.3s ease;
        }
        
        .course-card:hover .course-overlay {
            opacity: 1;
        }
        
        .course-badge {
            position: absolute;
            top: 15px;
            right: 15px;
            background: var(--secondary);
            color: white;
            padding: 0.3rem 0.8rem;
            border-radius: 50px;
            font-size: 0.8rem;
            font-weight: 700;
        }
        
        .course-content {
            padding: 1.5rem;
        }
        
        .course-title {
            font-weight: 700;
            color: var(--dark);
            margin-bottom: 0.5rem;
        }
        
        .course-price {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .free-badge {
            background: var(--secondary);
            color: white;
            padding: 0.3rem 0.8rem;
            border-radius: 50px;
            font-size: 0.85rem;
            font-weight: 700;
        }
        
        /* Category Cards */
        .category-card {
            background: white;
            border-radius: 20px;
            padding: 2rem 1rem;
            text-align: center;
            box-shadow: 0 5px 20px rgba(0,0,0,0.08);
            transition: all 0.3s ease;
            border: none;
        }
        
        .category-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 30px rgba(0,0,0,0.15);
        }
        
        .icon-wrapper {
            width: 70px;
            height: 70px;
            border-radius: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1rem;
            font-size: 1.8rem;
        }
        
        /* Testimonials */
        .testimonial-card {
            background: white;
            border-radius: 20px;
            padding: 2rem;
            box-shadow: 0 5px 20px rgba(0,0,0,0.08);
        }
        
        .testimonial-text {
            font-size: 1rem;
            color: #64748b;
            line-height: 1.8;
            margin-bottom: 1.5rem;
        }
        
        .testimonial-author {
            display: flex;
            align-items: center;
            gap: 1rem;
        }
        
        .testimonial-author img {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            object-fit: cover;
        }
        
        /* CTA Section */
        .cta-section {
            background: linear-gradient(135deg, #f0f9ff 0%, #e0f2fe 100%);
            padding: 4rem 0;
            position: relative;
            overflow: hidden;
        }
        
        .cta-section::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -10%;
            width: 400px;
            height: 400px;
            background: rgba(99, 102, 241, 0.1);
            border-radius: 50%;
        }
        
        /* Auth Section */
        .auth-section {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            padding: 2rem 0;
        }
        
        .auth-card {
            background: white;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 30px 60px rgba(0,0,0,0.3);
        }
        
        .auth-branding {
            position: relative;
            overflow: hidden;
        }
        
        .auth-branding::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><circle cx="20" cy="20" r="2" fill="rgba(255,255,255,0.1)"/><circle cx="80" cy="40" r="3" fill="rgba(255,255,255,0.1)"/><circle cx="40" cy="80" r="2" fill="rgba(255,255,255,0.1)"/></svg>');
            background-size: 100px 100px;
        }
        
        /* Page Banner */
        .page-banner {
            background: linear-gradient(135deg, #f0f9ff 0%, #e0f2fe 100%);
            padding: 2rem 0;
        }
        
        /* Course Header */
        .course-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
        
        .course-pricing-card {
            background: white;
            border-radius: 20px;
            padding: 2rem;
            box-shadow: 0 20px 40px rgba(0,0,0,0.2);
        }
        
        .lesson-item {
            background: white;
            border: 1px solid #e2e8f0;
            transition: all 0.3s ease;
        }
        
        .lesson-item:hover {
            background: #f8fafc;
            border-color: var(--primary);
        }
        
        .lesson-number {
            width: 40px;
            height: 40px;
            background: var(--primary);
            color: white;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
        }
        
        /* Footer */
        .footer {
            background: var(--dark);
            color: white;
            padding: 3rem 0 1rem;
        }
        
        .footer-link {
            color: rgba(255,255,255,0.7);
            text-decoration: none;
            transition: color 0.3s ease;
        }
        
        .footer-link:hover {
            color: var(--primary);
        }
        
        /* Animations */
        .animate-on-scroll {
            opacity: 0;
            transform: translateY(30px);
            transition: all 0.6s ease;
        }
        
        .animate-on-scroll.visible {
            opacity: 1;
            transform: translateY(0);
        }
        
        /* Custom Scrollbar */
        ::-webkit-scrollbar {
            width: 8px;
        }
        
        ::-webkit-scrollbar-track {
            background: #f1f5f9;
        }
        
        ::-webkit-scrollbar-thumb {
            background: var(--primary);
            border-radius: 4px;
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            .hero-title {
                font-size: 2.5rem;
            }
            
            .stats-banner {
                margin-top: -2rem;
            }
            
            .nav-avatar {
                width: 32px;
                height: 32px;
            }
        }
        
        /* Video Styles */
        .video-preview-wrapper {
            position: relative;
            width: 100%;
            padding-bottom: 56.25%;
            background: #000;
            border-radius: 16px 16px 0 0;
            overflow: hidden;
        }
        
        .course-video-iframe {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            border: none;
        }
        
        .course-video-player {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        
        /* Profile Styles */
        .profile-header {
            position: relative;
            overflow: hidden;
        }
        
        .profile-header::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><circle cx="50" cy="50" r="40" fill="none" stroke="rgba(255,255,255,0.1)" stroke-width="0.5"/></svg>');
            background-size: 50px 50px;
            opacity: 0.3;
        }
        
        .avatar-wrapper {
            position: relative;
        }
        
        .profile-avatar {
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        
        .profile-avatar:hover {
            transform: scale(1.05);
        }
        
        .avatar-preview img {
            transition: all 0.3s ease;
        }
        
        /* Admin Styles */
        .admin-sidebar {
            background: #1e293b;
            min-height: 100vh;
            color: white;
            padding: 20px 0;
            position: fixed;
            right: 0;
            top: 0;
            width: 260px;
            z-index: 1000;
            transition: transform 0.3s ease;
        }
        
        .admin-main {
            margin-right: 260px;
            padding: 30px;
            min-height: 100vh;
            background: #f8fafc;
            transition: margin-right 0.3s ease;
        }
        
        .admin-link {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 12px 20px;
            color: rgba(255,255,255,0.7);
            text-decoration: none;
            transition: all 0.3s;
            border-right: 3px solid transparent;
            cursor: pointer;
        }
        
        .admin-link:hover, .admin-link.active {
            background: rgba(255,255,255,0.1);
            color: white;
            border-right-color: #6366f1;
        }
        
        @media (max-width: 991px) {
            .admin-sidebar { transform: translateX(100%); }
            .admin-sidebar.active { transform: translateX(0); }
            .admin-main { margin-right: 0; }
        }
        
        .fade-in {
            animation: fadeIn 0.5s ease-in;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
    </style>
</head>
<body>
    <!-- ═══════════════════════════════════════════════════════════════ -->
    <!-- Navbar -->
    <!-- ═══════════════════════════════════════════════════════════════ -->
    <nav class="navbar navbar-expand-lg navbar-custom fixed-top">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <i class="fas fa-graduation-cap me-2"></i>كنزك التعليمي
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <!-- Main Navigation -->
                <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                    <li class="nav-item">
                        <a class="nav-link <?php echo ($current_page == 'index.php') ? 'active' : ''; ?>" href="index.php">
                            <i class="fas fa-home me-1"></i> الرئيسية
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo ($current_page == 'courses.php') ? 'active' : ''; ?>" href="courses.php">
                            <i class="fas fa-book me-1"></i> الدورات
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo ($current_page == 'contact.php') ? 'active' : ''; ?>" href="contact.php">
                            <i class="fas fa-envelope me-1"></i> تواصل معنا
                        </a>
                    </li>
                    <?php if($is_logged_in): ?>
                    <!-- Profile Link - Only visible when logged in -->
                    <li class="nav-item">
                        <a class="nav-link <?php echo ($current_page == 'profile.php') ? 'active' : ''; ?>" href="profile.php">
                            <i class="fas fa-user me-1"></i> الملف الشخصي
                        </a>
                    </li>
                    <?php endif; ?>
                </ul>
                
                <!-- User Actions -->
                <ul class="navbar-nav ms-auto mb-2 mb-lg-0 align-items-center">
                    <?php if($is_logged_in): ?>
                        <!-- Logged In User Dropdown -->
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle d-flex align-items-center gap-2 py-1" 
                               href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <img src="<?php echo $user_avatar; ?>" class="nav-avatar" alt="<?php echo htmlspecialchars($user_name); ?>">
                                <span class="fw-bold d-none d-sm-inline"><?php echo htmlspecialchars($user_name); ?></span>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end border-0 shadow-lg rounded-4 py-2">
                                <li class="px-3 py-2 border-bottom">
                                    <div class="d-flex align-items-center gap-2">
                                        <img src="<?php echo $user_avatar; ?>" class="rounded-circle" width="40" height="40" style="object-fit: cover;">
                                        <div>
                                            <div class="fw-bold"><?php echo htmlspecialchars($user_name); ?></div>
                                            <small class="text-muted">طالب</small>
                                        </div>
                                    </div>
                                </li>
                                <li><a class="dropdown-item py-2" href="profile.php">
                                    <i class="fas fa-user text-primary me-2"></i> الملف الشخصي
                                </a></li>
                                <li><a class="dropdown-item py-2" href="student/mycourses.php">
                                    <i class="fas fa-book text-success me-2"></i> دوراتي
                                </a></li>
                                <li><a class="dropdown-item py-2" href="student/watchCourse.php">
                                    <i class="fas fa-play-circle text-info me-2"></i> استمر في التعلم
                                </a></li>
                                <li><hr class="dropdown-divider mx-3"></li>
                                <li><a class="dropdown-item py-2 text-danger" href="logout.php">
                                    <i class="fas fa-sign-out-alt me-2"></i> تسجيل الخروج
                                </a></li>
                            </ul>
                        </li>
                    <?php else: ?>
                        <!-- Guest User -->
                        <li class="nav-item">
                            <a class="nav-link" href="loginorsignup.php">
                                <i class="fas fa-sign-in-alt me-1"></i> تسجيل الدخول
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="btn btn-primary rounded-pill px-4 ms-2 fw-bold" href="loginorsignup.php">
                                <i class="fas fa-user-plus me-1"></i> إنشاء حساب
                            </a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>
    
    <!-- Spacer for fixed navbar -->
    <div style="height: 76px;"></div>
