🎓 كنزك التعليمي | Kanzak Educational Platform
    
منصة التعلم الإلكتروني الأولى في ليبيا 🇱🇾 The First E-Learning Platform in Libya
________________________________________
📋 Table of Contents | فهرس المحتويات
•	Overview | نظرة عامة
•	Features | المميزات
•	Technology Stack | التقنيات المستخدمة
•	Database Schema | هيكل قاعدة البيانات
•	Installation | التثبيت
•	Project Structure | هيكل المشروع
•	Screenshots | لقطات الشاشة
•	API Endpoints | نقاط الوصول
•	Security | الأمان
•	Contributing | المساهمة
•	License | الترخيص
•	Contact | التواصل
________________________________________
🌟 Overview | نظرة عامة
كنزك التعليمي هو منصة تعليمية إلكترونية متكاملة مبنية بـ PHP و MySQL، مصممة خصيصاً للطلاب في ليبيا والعالم العربي. المنصة تقدم دورات تعليمية مجانية عالية الجودة في مختلف المجالات التقنية.
Key Highlights | أبرز النقاط:
•	✅ 100% مجاني - جميع الدورات متاحة مجاناً
•	✅ شهادات معتمدة - احصل على شهادة إتمام لكل دورة
•	✅ محتوى عربي - واجهة عربية بالكامل مع دعم RTL
•	✅ مشاركة يوتيوب - دعم تضمين فيديوهات YouTube
•	✅ نظام دفع متكامل - بوابة دفع Paytm
•	✅ تتبع التقدم - متابعة تقدمك في كل دورة
________________________________________
✨ Features | المميزات
🎓 For Students | للطلاب
Feature	Description	الوصف
🔐 User Authentication	Secure login/signup with password hashing	تسجيل دخول/حساب جديد آمن
📚 Course Catalog	Browse courses by category with search & filter	تصفح الدورات حسب التصنيف
❤️ Favorites	Save courses to favorites list	حفظ الدورات في المفضلة
📝 Enrollment	One-click free course enrollment	تسجيل مجاني بنقرة واحدة
📊 Progress Tracking	Track completed lessons per course	متابعة التقدم في الدروس
🎬 Video Player	YouTube/Vimeo/Local video support	دعم فيديوهات متعددة
⭐ Reviews	Rate and review courses	تقييم ومراجعة الدورات
👤 Profile	Edit profile with avatar upload	تعديل الملف الشخصي
🏆 Certificates	Completion certificates	شهادات إتمام
👨🏫 For Instructors | للمحاضرين
Feature	Description	الوصف
📤 Course Management	Add/edit courses with rich details	إدارة الدورات
🎥 Video Upload	Support for YouTube, Vimeo, and local videos	رفع الفيديوهات
📈 Analytics	View enrollment statistics	إحصائيات التسجيل
🔧 Admin Features | مميزات الإدارة
Feature	Description	الوصف
📊 Dashboard	Overview of platform statistics	لوحة تحكم إحصائية
👥 User Management	Manage students and instructors	إدارة المستخدمين
📧 Contact Messages	Handle support inquiries	رسائل التواصل
💰 Order Management	Track payment transactions	إدارة الطلبات
________________________________________
🛠 Technology Stack | التقنيات المستخدمة
Backend | الخلفية
PHP 8.2+ (Procedural + MySQLi OOP)
MySQL 8.0 / MariaDB 10.4+
Apache/Nginx Server
Frontend | الواجهة الأمامية
HTML5 + CSS3
Bootstrap 5.3 (RTL Support)
Font Awesome 6.4
JavaScript (Vanilla - No jQuery)
Google Fonts (Cairo)
Payment Gateway | بوابة الدفع
Paytm Payment Gateway
Database | قاعدة البيانات
MySQL with UTF-8mb4 (Arabic support)
Foreign Key Constraints
InnoDB Engine
________________________________________
🗄 Database Schema | هيكل قاعدة البيانات
Tables | الجداول
┌─────────────────┐     ┌─────────────────┐     ┌─────────────────┐
│    users        │     │   categories    │     │    courses      │
├─────────────────┤     ├─────────────────┤     ├─────────────────┤
│ user_id (PK)    │     │ cat_id (PK)     │     │ course_id (PK)  │
│ name            │     │ cat_name        │     │ cat_id (FK)     │
│ email (UNIQUE)  │     │ cat_icon        │     │ course_name     │
│ password        │     │ cat_desc        │     │ course_desc     │
│ role            │     │ status          │     │ course_detail   │
│ avatar          │     │ created_at      │     │ course_img      │
│ phone           │     └─────────────────┘     │ course_duration │
│ bio             │           │                 │ course_level    │
│ status          │           │                 │ course_price    │
│ created_at      │           └────────────────►│ course_original │
│ updated_at      │                             │ instructor_name │
└─────────────────┘                             │ rating          │
        │                                     │ status          │
        │                                     │ created_at      │
        │                                     └─────────────────┘
        │                                              │
        │         ┌─────────────────┐                  │
        │         │    lessons      │◄─────────────────┘
        │         ├─────────────────┤
        │         │ lesson_id (PK)  │
        │         │ course_id (FK)  │
        │         │ lesson_name     │
        │         │ lesson_desc     │
        │         │ video_url       │
        │         │ video_type      │
        │         │ duration        │
        │         │ is_free         │
        │         │ lesson_order    │
        │         └─────────────────┘
        │
        │         ┌─────────────────┐     ┌─────────────────┐
        │         │    orders       │     │    progress     │
        │         ├─────────────────┤     ├─────────────────┤
        │         │ order_id (PK)   │     │ progress_id(PK)│
        │         │ order_no (UNIQ) │     │ user_id (FK)    │
        │         │ user_id (FK)    │     │ course_id (FK)  │
        │         │ course_id (FK)  │     │ lesson_id (FK)  │
        │         │ amount          │     │ is_completed    │
        │         │ payment_status  │     │ completed_at    │
        │         │ txn_id          │     │ created_at      │
        │         │ created_at      │     └─────────────────┘
        │         └─────────────────┘
        │
        │         ┌─────────────────┐     ┌─────────────────┐
        │         │   favorites     │     │    reviews      │
        │         ├─────────────────┤     ├─────────────────┤
        │         │ fav_id (PK)     │     │ review_id (PK)  │
        │         │ user_id (FK)    │     │ user_id (FK)    │
        │         │ course_id (FK)  │     │ course_id (FK)  │
        │         │ created_at      │     │ rating          │
        │         └─────────────────┘     │ review_text     │
        │                                 │ created_at      │
        │                                 └─────────────────┘
        │
        │         ┌─────────────────┐     ┌─────────────────┐
        │         │  course_videos  │     │    contacts     │
        │         ├─────────────────┤     ├─────────────────┤
        │         │ video_id (PK)   │     │ contact_id (PK) │
        │         │ course_id (FK)  │     │ name            │
        │         │ video_url       │     │ email           │
        │         │ video_type      │     │ subject         │
        │         │ title           │     │ message         │
        │         │ duration        │     │ status          │
        │         │ is_preview      │     │ created_at      │
        │         │ sort_order      │     └─────────────────┘
        │         └─────────────────┘
        │
        └────────►┌─────────────────┐
                  │    contacts     │
                  ├─────────────────┤
                  │ contact_id (PK) │
                  │ name            │
                  │ email           │
                  │ subject         │
                  │ message         │
                  │ status          │
                  │ created_at      │
                  └─────────────────┘
Relationships | العلاقات
•	users → orders (1:N) - One user can have many orders
•	users → favorites (1:N) - One user can favorite many courses
•	users → progress (1:N) - One user can have progress in many lessons
•	users → reviews (1:N) - One user can review many courses
•	categories → courses (1:N) - One category can have many courses
•	courses → lessons (1:N) - One course can have many lessons
•	courses → course_videos (1:N) - One course can have many videos
•	courses → orders (1:N) - One course can have many orders
•	courses → favorites (1:N) - One course can be favorited by many users
•	courses → progress (1:N) - One course can have progress from many users
•	courses → reviews (1:N) - One course can have many reviews
•	lessons → progress (1:N) - One lesson can have progress from many users
________________________________________
📥 Installation | التثبيت
Prerequisites | المتطلبات
•	PHP 8.2 or higher
•	MySQL 8.0+ or MariaDB 10.4+
•	Apache/Nginx web server
•	mod_rewrite enabled (for clean URLs)
Step-by-Step Guide | خطوات التثبيت
1. Clone the Repository | استنساخ المستودع
git clone https://github.com/yourusername/kanzak-educational.git
cd kanzak-educational
2. Database Setup | إعداد قاعدة البيانات
# Create database
mysql -u root -p -e "CREATE DATABASE kanzak_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"

# Import schema
mysql -u root -p kanzak_db < kanzak_db.sql
3. Configuration | الإعدادات
Edit dbConnection.php:
$db_host = "localhost";
$db_user = "root";
$db_password = "your_password";  // Change this
$db_name = "kanzak_db";
4. Directory Permissions | صلاحيات المجلدات
chmod 755 uploads/
chmod 755 uploads/avatars/
chmod 755 uploads/courses/
chmod 755 uploads/videos/
5. Payment Configuration | إعداد الدفع
Configure Paytm credentials in ./PaytmKit/lib/config_paytm.php:
define('PAYTM_MERCHANT_MID', 'your_merchant_id');
define('PAYTM_MERCHANT_KEY', 'your_merchant_key');
define('PAYTM_ENVIRONMENT', 'TEST'); // Change to 'PROD' for production
6. Access the Application | الوصول للتطبيق
http://localhost/kanzak-educational/
Default Admin Account | حساب الإدارة الافتراضي
Email: admin@kanzak.com
Password: admin123
⚠️ Important: Change the default password immediately after installation!
________________________________________
📁 Project Structure | هيكل المشروع
kanzak-educational/
│
├── 📄 index.php                 # Homepage with hero section, courses, testimonials
├── 📄 courses.php               # Course catalog with filtering and search
├── 📄 coursedetails.php         # Course details with lessons and enrollment
├── 📄 coursedetails_fixed.php   # Fixed version of course details
├── 📄 loginorsignup.php         # Login & registration with AJAX
├── 📄 profile.php               # User profile with avatar upload and favorites
├── 📄 checkout.php              # Payment checkout page
├── 📄 paymentstatus.php         # Payment status verification
├── 📄 contact.php               # Contact form with validation
├── 📄 logout.php                # Secure logout with confirmation
├── 📄 pageNotFound.php          # 404 error page
│
├── 🔧 dbConnection.php          # Database connection & helper functions
├── 🗄️ kanzak_db.sql            # Database schema with sample data
│
├── 📁 includes/
│   ├── header.php               # Common header with navigation
│   └── footer.php               # Common footer with scripts
│
├── 📁 PaytmKit/                 # Paytm payment integration
│   ├── lib/
│   │   ├── config_paytm.php     # Paytm configuration
│   │   └── encdec_paytm.php     # Encryption/Decryption utilities
│   └── pgRedirect.php           # Payment redirect handler
│
├── 📁 uploads/                  # Upload directories
│   ├── avatars/                 # User profile pictures
│   ├── courses/                   # Course thumbnail images
│   └── videos/                    # Local video files
│
├── 📁 student/                    # Student area
│   ├── mycourses.php            # My enrolled courses
│   └── watchCourse.php          # Video player and lesson viewer
│
└── 📁 admin/                      # Admin panel (if applicable)
    └── ...
________________________________________
📸 Screenshots | لقطات الشاشة
🏠 Home Page | الصفحة الرئيسية
•	Hero section with animated background
•	Statistics banner (courses, students, instructors)
•	Featured courses grid with hover effects
•	Category cards with icons
•	Student testimonials
•	Contact form
📚 Courses Page | صفحة الدورات
•	Filter by category dropdown
•	Search functionality
•	Sort options (popular, newest, name)
•	Course cards with image, rating, duration
•	Free badge and enrollment button
📖 Course Details | تفاصيل الدورة
•	Course header with gradient background
•	Video preview player
•	Lesson list with lock/unlock status
•	Progress bar for enrolled students
•	Related courses sidebar
•	Review and rating section
👤 Profile Page | الملف الشخصي
•	Avatar upload with preview
•	Personal information editing
•	Statistics dashboard
•	Favorite courses grid
•	Enrolled courses with progress bars
•	Recent activity timeline
________________________________________
🔌 API Endpoints | نقاط الوصول
AJAX Endpoints | نقاط AJAX
Endpoint	Method	Description	الوصف
loginorsignup.php	POST	checkLogemail - Login verification	التحقق من تسجيل الدخول
loginorsignup.php	POST	checkStuEmail - Registration	إنشاء حساب جديد
index.php	POST	enroll_course - Free enrollment	التسجيل المجاني
index.php	POST	toggle_fav - Toggle favorite	تبديل المفضلة
coursedetails.php	POST	enroll_course - Course enrollment	تسجيل في الدورة
coursedetails.php	POST	toggle_favorite - Toggle favorite	تبديل المفضلة
profile.php	POST	upload_avatar - Avatar upload	رفع الصورة
profile.php	POST	update_profile - Update profile	تحديث الملف
profile.php	POST	toggle_favorite - Remove favorite	إزالة من المفضلة
contact.php	POST	submit - Contact form	نموذج التواصل
checkout.php	POST	id, course_id - Initiate payment	بدء الدفع
paymentstatus.php	POST	ORDER_ID - Check payment status	حالة الدفع
Response Codes | رموز الاستجابة
Login/Register:
  "1" = Success | نجاح
  "0" = Error | خطأ
  "2" = Password too short | كلمة المرور قصيرة
  "3" = Email exists | البريد مستخدم
________________________________________
🔒 Security | الأمان
Implemented Security Measures | إجراءات الأمان المطبقة
Feature	Implementation	التنفيذ
🔐 Password Hashing	password_hash() with bcrypt	تشفير كلمات المرور
🛡️ SQL Injection Prevention	Prepared Statements (MySQLi)	جمل محضرة
🧹 XSS Prevention	htmlspecialchars() + strip_tags()	منع XSS
📝 CSRF Protection	Session-based tokens	حماية CSRF
🚫 File Upload Validation	MIME type + extension check	التحقق من الملفات
🔒 Session Security	Secure session handling	جلسات آمنة
📏 Input Sanitization	sanitize() function	تنظيف المدخلات
Security Functions | دوال الأمان
// Input sanitization
function sanitize($data) {
    $data = trim($data);
    $data = strip_tags($data);
    $data = mysqli_real_escape_string($conn, $data);
    $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    return $data;
}

// Authentication check
function isLoggedIn() {
    return isset($_SESSION['is_login']) && $_SESSION['is_login'] === true;
}

// File upload security
function uploadImageFile($file, $upload_dir = './uploads/images/') {
    $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    $max_size = 5 * 1024 * 1024; // 5MB
    // ... validation logic
}
________________________________________
🤝 Contributing | المساهمة
We welcome contributions from the community! | نرحب بالمساهمات من المجتمع!
How to Contribute | كيفية المساهمة
1.	Fork the repository
2.	Create a feature branch (git checkout -b feature/amazing-feature)
3.	Commit your changes (git commit -m 'Add amazing feature')
4.	Push to the branch (git push origin feature/amazing-feature)
5.	Open a Pull Request
Contribution Guidelines | إرشادات المساهمة
•	Follow PSR-12 coding standards
•	Write comments in Arabic and English
•	Test changes thoroughly before submitting
•	Update documentation for any new features
________________________________________
📄 License | الترخيص
This project is licensed under the MIT License - see the LICENSE file for details.
MIT License

Copyright (c) 2026 Kanzak Educational

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all
copies or substantial portions of the Software.
________________________________________
📞 Contact | التواصل
Support | الدعم الفني
•	📧 Email: info@kanzak.ly
•	📱 Phone: +218 91 000 0000
•	📍 Address: Misrata, Libya 🇱🇾
•	💬 WhatsApp: +218 91 000 0000
Social Media | وسائل التواصل
•	🌐 Website: www.kanzak.ly
•	📘 Facebook: Kanzak Educational
•	📸 Instagram: @kanzak_edu
•	🐦 Twitter: @kanzak_edu
________________________________________
🙏 Acknowledgments | الشكر والتقدير
•	Bootstrap Team for the amazing CSS framework
•	Font Awesome for the beautiful icons
•	Google Fonts for the Cairo Arabic font
•	Paytm for the payment gateway integration
•	Unsplash for free stock images
•	All contributors who helped build this platform
________________________________________
Made with ❤️ in Libya 🇱🇾 كنزك التعليمي - ليبيا © 2026 Kanzak Educational. All rights reserved.
⬆️ Back to Top | العودة للأعلى
