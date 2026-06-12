-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jun 11, 2026 at 10:46 AM
-- Server version: 10.4.28-MariaDB
-- PHP Version: 8.2.4

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `cat_id` int(11) NOT NULL,
  `cat_name` varchar(100) NOT NULL,
  `cat_icon` varchar(50) DEFAULT 'fa-book',
  `cat_desc` text DEFAULT NULL,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`cat_id`, `cat_name`, `cat_icon`, `cat_desc`, `status`, `created_at`) VALUES
(1, 'برمجة الويب', 'fa-globe', 'تعلم تطوير المواقع والتطبيقات', 'active', '2026-06-08 17:04:51'),
(2, 'تصميم UI/UX', 'fa-paint-brush', 'تعلم تصميم الواجهات وتجربة المستخدم', 'active', '2026-06-08 17:04:51'),
(3, 'تسويق رقمي', 'fa-chart-line', 'تعلم التسويق الإلكتروني', 'active', '2026-06-08 17:04:51'),
(4, 'قواعد البيانات', 'fa-database', 'تعلم SQL وإدارة البيانات', 'active', '2026-06-08 17:04:51'),
(5, 'لغات البرمجة', 'fa-code', 'Python, JavaScript, PHP وغيرها', 'active', '2026-06-08 17:04:51'),
(6, 'أمن المعلومات', 'fa-shield-alt', 'تعلم الأمن السيبراني', 'active', '2026-06-08 17:04:51');

-- --------------------------------------------------------

--
-- Table structure for table `contacts`
--

CREATE TABLE `contacts` (
  `contact_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `subject` varchar(200) DEFAULT '',
  `message` text NOT NULL,
  `status` enum('new','read','replied','closed') DEFAULT 'new',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `contacts`
--

INSERT INTO `contacts` (`contact_id`, `name`, `email`, `subject`, `message`, `status`, `created_at`) VALUES
(2, 'لبنى فوزي أحمد شحاته', 'lolo@gmail.com', 'استفسار', 'الغعتت', 'new', '2026-06-11 00:02:26');

-- --------------------------------------------------------

--
-- Table structure for table `courses`
--

CREATE TABLE `courses` (
  `course_id` int(11) NOT NULL,
  `cat_id` int(11) DEFAULT NULL,
  `course_name` varchar(200) NOT NULL,
  `course_desc` text DEFAULT NULL,
  `course_detail` text DEFAULT NULL,
  `course_img` varchar(500) DEFAULT 'https://via.placeholder.com/400x300',
  `course_duration` varchar(50) DEFAULT '00:00',
  `course_level` enum('beginner','intermediate','advanced') DEFAULT 'beginner',
  `course_price` decimal(10,2) DEFAULT 0.00,
  `course_original_price` decimal(10,2) DEFAULT 0.00,
  `instructor_name` varchar(100) DEFAULT 'معلم',
  `instructor_bio` text DEFAULT NULL,
  `rating` decimal(2,1) DEFAULT 5.0,
  `status` enum('active','inactive','draft') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `courses`
--

INSERT INTO `courses` (`course_id`, `cat_id`, `course_name`, `course_desc`, `course_detail`, `course_img`, `course_duration`, `course_level`, `course_price`, `course_original_price`, `instructor_name`, `instructor_bio`, `rating`, `status`, `created_at`) VALUES
(1, 1, 'دورة HTML و CSS كاملة', 'تعلم أساسيات بناء المواقع مع HTML و CSS', 'في هذه الدورة ستتعلم كل ما تحتاجه لبناء مواقع احترافية باستخدام HTML5 و CSS3.', 'https://images.unsplash.com/photo-1461749280684-dccba630e2f6?w=400&h=300&fit=crop', '12:30:00', 'beginner', 0.00, 0.00, 'أحمد محمد', NULL, 4.8, 'active', '2026-06-08 17:04:51'),
(2, 1, 'دورة JavaScript متقدمة', 'تعلم البرمجة بلغة JavaScript من الصفر', 'تعلم JavaScript ES6+ مع المشاريع العملية والتطبيقات.', 'https://images.unsplash.com/photo-1579468118864-1b9ea3c0db4a?w=400&h=300&fit=crop', '18:45:00', 'intermediate', 0.00, 0.00, 'خالد علي', NULL, 4.9, 'active', '2026-06-08 17:04:51'),
(3, 2, 'تصميم واجهات المستخدم', 'تعلم Figma و Adobe XD لتصميم UI/UX', 'تعلم تصميم واجهات المستخدم باستخدام أحدث الأدوات.', 'https://images.unsplash.com/photo-1561070791-2526d30994b5?w=400&h=300&fit=crop', '10:00:00', 'beginner', 0.00, 0.00, 'سارة أحمد', NULL, 4.7, 'active', '2026-06-08 17:04:51'),
(4, 3, 'التسويق الرقمي', 'تعلم SEO و Social Media Marketing', 'استراتيجيات التسويق الرقمي الفعالة لزيادة المبيعات.', 'https://images.unsplash.com/photo-1460925895917-afdab827c52f?w=400&h=300&fit=crop', '8:30:00', 'beginner', 0.00, 0.00, 'فاطمة حسن', NULL, 4.6, 'active', '2026-06-08 17:04:51'),
(5, 4, 'MySQL للمبتدئين', 'تعلم قواعد البيانات العلائقية', 'تعلم كيفية إنشاء وإدارة قواعد البيانات باستخدام MySQL.', 'https://images.unsplash.com/photo-1544383835-bda2bc66a55d?w=400&h=300&fit=crop', '14:00:00', 'beginner', 0.00, 0.00, 'عمر سالم', NULL, 4.9, 'active', '2026-06-08 17:04:51'),
(6, 4, 'مقدمة في htmlو css', 'قواعد البيانات', 'قواعد البيانات هي مجموعة عناصر البيانات المنطقيّة المرتبطة مع بعضها البعض بعلاقة رياضيّة، وتتكوّن قاعدة البيانات من جدول واحد أو أكثر، مثل سجل الخاص بالموظف الذي يتكوّن من عدّة حقول، مثل: رقم الموظف، واسم الجهاز، ودرجة الموظف، وتاريخ التعيين، والراتب، وبيانات الموظف التي تخزن في جهاز الحاسوب تكون على نحو منظّم،', 'https://images.unsplash.com/photo-1516321318423-f06f85e504b3?w=600&h=400&fit=crop', '', '', 0.00, 0.00, '1', NULL, 5.0, 'active', '2026-06-08 21:44:09'),
(7, 4, 'قواعد بيانات MY SQL', 'قواعد البيانات ', 'قواعد البيانات هي مجموعة عناصر البيانات المنطقيّة المرتبطة مع بعضها البعض بعلاقة رياضيّة، وتتكوّن قاعدة البيانات من جدول واحد أو أكثر، مثل سجل الخاص بالموظف الذي يتكوّن من عدّة حقول، مثل: رقم الموظف، واسم الجهاز، ودرجة الموظف، وتاريخ التعيين، والراتب، وبيانات الموظف التي تخزن في جهاز الحاسوب تكون على نحو منظّم،', '../uploads/courses/img_1781123114_6a29c82ac905f.jpg', '5:11', '', 0.00, 0.00, 'ماهي قواعد البيانات ؟ What is Data Base', NULL, 5.0, 'active', '2026-06-10 20:25:14'),
(9, 4, 'قواعد بيانات MY SQL', 'قواعد', 'قواعد بيانات', '../uploads/courses/img_1781129222_6a29e00684ead.jpg', '5:11', '', 0.00, 0.00, 'مفاهيم قواعد البيانات', NULL, 5.0, 'active', '2026-06-10 22:07:02'),
(10, 1, 'مقدمة في htmlو css', 'دورة أساسيات تطوير مواقع الويب تقدم تدريبًا شاملاً للمبتدئين حول كيفية بناء وتصميم مواقع ويب احترافية. يبدأ كورس أساسيات تطوير مواقع الويب بتغطية أساسيات HTML، وهي اللغة الأساسية المستخدمة لإنشاء هيكل صفحات الويب. ستتعلم كيفية إنشاء صفحات ويب بسيطة، استخدام العناصر الهيكلية مثل العناوين والفقرات، وإدراج الروابط والصور. ثم تنتقل الدورة إلى CSS، التي تساعد في تنسيق مظهر صفحات الويب، حيث ستتعلم كيفية تطبيق الألوان والخطوط، وتحديد تخطيطات الصفحات باستخدام CSS Grid وFlexbox. ستغطي الدورة أيضًا أساسيات JavaScript، التي تضيف التفاعل والديناميكية للمواقع، مثل التعامل مع الأحداث وإنشاء تأثيرات متحركة. ستتعرف على كيفية استخدام JavaScript لجعل صفحات الويب تفاعلية وتطبيق وظائف مثل النماذج التفاعلية والقوائم المنسدلة. تشمل الدورة تدريبات عملية على بناء موقع ويب من البداية إلى النهاية، مما يساعدك على تطبيق ما تعلمته بشكل عملي. بنهاية الدورة، ستكون قادرًا على تطوير مواقع ويب متجاوبة وجذابة، وستمتلك المهارات اللازمة للانتقال إلى مستويات متقدمة في تطوير الويب,الدورة مجانية وبشهادة معتمدة. Website development basics', 'دورة أساسيات تطوير مواقع الويب تقدم تدريبًا شاملاً للمبتدئين حول كيفية بناء وتصميم مواقع ويب احترافية. يبدأ كورس أساسيات تطوير مواقع الويب بتغطية أساسيات HTML، وهي اللغة الأساسية المستخدمة لإنشاء هيكل صفحات الويب. ستتعلم كيفية إنشاء صفحات ويب بسيطة، استخدام العناصر الهيكلية مثل العناوين والفقرات، وإدراج الروابط والصور. ثم تنتقل الدورة إلى CSS، التي تساعد في تنسيق مظهر صفحات الويب، حيث ستتعلم كيفية تطبيق الألوان والخطوط، وتحديد تخطيطات الصفحات باستخدام CSS Grid وFlexbox. ستغطي الدورة أيضًا أساسيات JavaScript، التي تضيف التفاعل والديناميكية للمواقع، مثل التعامل مع الأحداث وإنشاء تأثيرات متحركة. ستتعرف على كيفية استخدام JavaScript لجعل صفحات الويب تفاعلية وتطبيق وظائف مثل النماذج التفاعلية والقوائم المنسدلة. تشمل الدورة تدريبات عملية على بناء موقع ويب من البداية إلى النهاية، مما يساعدك على تطبيق ما تعلمته بشكل عملي. بنهاية الدورة، ستكون قادرًا على تطوير مواقع ويب متجاوبة وجذابة، وستمتلك المهارات اللازمة للانتقال إلى مستويات متقدمة في تطوير الويب,الدورة مجانية وبشهادة معتمدة. Website development basics', '../uploads/courses/img_1781129345_6a29e0810e2da.jpg', '2:45:49', '', 0.00, 0.00, 'العطاء الرقمي | مقدمة في تطوير مواقع الويب اليوم الثاني', NULL, 5.0, 'active', '2026-06-10 22:09:05'),
(11, 3, 'التسويق الرقمي', 'دورة التسويق عبر الانترنت دورة اونلاين معتمدة احصل على خبرة التسويق عبر الإنترنت مع دورتنا المميزة كورس التسويق عبر الانترنت و التسويق الاكتروني المجاني الاحترافي مقدم من منصة معارف بهدف  تعليم اساسيات  E-marketing للمبتدئين ومن يرغبون التطور في مجال التسويق بشكل اكاديمي، الدورة معتمدة بشهادة مجانية E-marketing تسويق\r\n', 'دورة التسويق عبر الانترنت دورة اونلاين معتمدة احصل على خبرة التسويق عبر الإنترنت مع دورتنا المميزة كورس التسويق عبر الانترنت و التسويق الاكتروني المجاني الاحترافي مقدم من منصة معارف بهدف  تعليم اساسيات  E-marketing للمبتدئين ومن يرغبون التطور في مجال التسويق بشكل اكاديمي، الدورة معتمدة بشهادة مجانية E-marketing تسويق\r\n', 'https://images.unsplash.com/photo-1516321318423-f06f85e504b3?w=600&h=400&fit=crop', '2:40:49', '', 0.00, 0.00, 'العطاء الرقمي | مقدمة في تطوير مواقع الويب اليوم الثاني', NULL, 5.0, 'active', '2026-06-10 22:17:50'),
(12, 4, 'قواعد بيانات MY SQL', 'قواعد البيانات هي مجموعة عناصر البيانات المنطقيّة المرتبطة مع بعضها البعض بعلاقة رياضيّة، وتتكوّن قاعدة البيانات من جدول واحد أو أكثر، مثل سجل الخاص بالموظف الذي يتكوّن من عدّة حقول، مثل: رقم الموظف، واسم الجهاز، ودرجة الموظف، وتاريخ التعيين، والراتب، وبيانات الموظف التي تخزن في جهاز الحاسوب تكون على نحو منظّم،', 'قواعد البيانات هي مجموعة عناصر البيانات المنطقيّة المرتبطة مع بعضها البعض بعلاقة رياضيّة، وتتكوّن قاعدة البيانات من جدول واحد أو أكثر، مثل سجل الخاص بالموظف الذي يتكوّن من عدّة حقول، مثل: رقم الموظف، واسم الجهاز، ودرجة الموظف، وتاريخ التعيين، والراتب، وبيانات الموظف التي تخزن في جهاز الحاسوب تكون على نحو منظّم،', '../uploads/courses/img_1781130556_6a29e53c9f51e.jpg', '5:11', '', 0.00, 0.00, 'ماهي قواعد البيانات ؟ What is Data Base', NULL, 5.0, 'active', '2026-06-10 22:29:16');

-- --------------------------------------------------------

--
-- Table structure for table `course_videos`
--

CREATE TABLE `course_videos` (
  `video_id` int(11) NOT NULL,
  `course_id` int(11) NOT NULL,
  `video_url` varchar(500) NOT NULL,
  `video_type` enum('youtube','vimeo','local') DEFAULT 'youtube',
  `title` varchar(255) DEFAULT '',
  `duration` varchar(20) DEFAULT '00:00',
  `is_preview` tinyint(1) DEFAULT 0,
  `sort_order` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `course_videos`
--

INSERT INTO `course_videos` (`video_id`, `course_id`, `video_url`, `video_type`, `title`, `duration`, `is_preview`, `sort_order`, `created_at`) VALUES
(1, 1, 'dQw4w9WgXcQ', 'youtube', 'مقدمة في HTML', '00:00', 1, 0, '2026-06-08 17:04:51'),
(2, 2, 'PkZNo7MFNFg', 'youtube', 'مقدمة في JavaScript', '00:00', 1, 0, '2026-06-08 17:04:51'),
(3, 3, 'https://example.com/videos/ui-design-intro.mp4', 'local', 'مقدمة في UI Design', '00:00', 1, 0, '2026-06-08 17:04:51'),
(4, 6, '4oDUwo39rx0', 'youtube', 'ماهي قواعد البيانات ؟ What is Data Base', '00:00', 1, 0, '2026-06-08 21:44:09'),
(5, 7, '4oDUwo39rx0', 'youtube', 'ماهي قواعد البيانات ؟ What is Data Base', '00:00', 1, 0, '2026-06-10 20:25:14'),
(7, 9, '4oDUwo39rx0', 'youtube', 'مفاهيم قواعد البيانات', '00:00', 1, 0, '2026-06-10 22:07:02'),
(8, 10, 'exYEnPG2LDM', 'youtube', 'العطاء الرقمي | مقدمة في تطوير مواقع الويب اليوم الثاني', '00:00', 1, 0, '2026-06-10 22:09:05'),
(9, 11, 'exYEnPG2LDM', 'youtube', 'العطاء الرقمي | مقدمة في تطوير مواقع الويب اليوم الثاني', '00:00', 1, 0, '2026-06-10 22:17:50'),
(10, 12, '4oDUwo39rx0', 'youtube', 'ماهي قواعد البيانات ؟ What is Data Base', '00:00', 1, 0, '2026-06-10 22:29:16');

-- --------------------------------------------------------

--
-- Table structure for table `favorites`
--

CREATE TABLE `favorites` (
  `fav_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `course_id` int(11) NOT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `favorites`
--

INSERT INTO `favorites` (`fav_id`, `user_id`, `course_id`, `created_at`) VALUES
(1, 1, 11, '2026-06-11 02:29:20'),
(2, 1, 5, '2026-06-11 03:01:42'),
(3, 1, 1, '2026-06-11 03:15:22');

-- --------------------------------------------------------

--
-- Table structure for table `lessons`
--

CREATE TABLE `lessons` (
  `lesson_id` int(11) NOT NULL,
  `course_id` int(11) NOT NULL,
  `lesson_name` varchar(200) NOT NULL,
  `lesson_desc` text DEFAULT NULL,
  `video_url` varchar(500) DEFAULT NULL,
  `video_type` varchar(20) DEFAULT 'youtube',
  `duration` varchar(20) DEFAULT '15:00',
  `is_free` enum('yes','no') DEFAULT 'no',
  `lesson_order` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `lessons`
--

INSERT INTO `lessons` (`lesson_id`, `course_id`, `lesson_name`, `lesson_desc`, `video_url`, `video_type`, `duration`, `is_free`, `lesson_order`, `created_at`) VALUES
(1, 5, 'ماهي قواعد البيانات ؟ What is Data Base', '', 'https://www.youtube.com/watch?v=4oDUwo39rx0&amp;t=1s', '', '5:11', 'yes', 1, '2026-06-10 19:47:54'),
(2, 11, 'العطاء الرقمي | مقدمة في تطوير مواقع الويب اليوم الثاني', 'دورة التسويق عبر الانترنت دورة اونلاين معتمدة احصل على خبرة التسويق عبر الإنترنت مع دورتنا المميزة كورس التسويق عبر الانترنت و التسويق الاكتروني المجاني الاحترافي مقدم من منصة معارف بهدف  تعليم اساسيات  E-marketing للمبتدئين ومن يرغبون التطور في مجال التسويق بشكل اكاديمي، الدورة معتمدة بشهادة مجانية E-marketing تسويق', 'LUDymrdLnV8', 'youtube', '2:40:49', 'no', 1, '2026-06-10 22:47:35'),
(3, 3, 'العطاء الرقمي | واجهة المستخدم (UI) في الألعاب اليوم الثاني', 'دورة واجهة المستخدم (UI) لتحسين الألعاب تهدف إلى تمكينك من تصميم واجهات مستخدم مبتكرة تعزز من تجربة اللاعب وتزيد من جودة الألعاب. يبدأ كورس واجهة المستخدم (UI) لتحسين الألعاب بتغطية أساسيات تصميم واجهة المستخدم، بما في ذلك فهم مبادئ تجربة المستخدم (UX) وكيفية تطبيقها في سياق الألعاب. ستتعلم كيفية إنشاء واجهات مستخدم سلسة وفعالة تساعد في تحسين تدفق اللعبة وتفاعل اللاعبين. بالإضافة إلى ذلك، ستتناول الدورة استخدام الألوان، والتصميمات الرسومية، والتخطيطات التي تجعل الواجهة ليست فقط جميلة ولكن أيضًا وظيفية ومتجاوبة مع احتياجات اللاعبين. ستتعرف أيضًا على كيفية اختبار وتحليل واجهة المستخدم لجعل الألعاب أكثر جاذبية وسهولة في الاستخدام. بنهاية الدورة، ستكون قادرًا على تصميم واجهات مستخدم ترفع من مستوى الألعاب وتحقق تجربة ممتعة ومتوازنة للاعبين على مختلف المنصات,الدورة مجانية وبشهادة معتمدة. User interface (UI) to improve gaming', 'bVPB1rQ91jI', 'youtube', '2:35:16', 'yes', 1, '2026-06-10 22:50:27');

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `order_id` int(11) NOT NULL,
  `order_no` varchar(50) NOT NULL,
  `user_id` int(11) NOT NULL,
  `course_id` int(11) NOT NULL,
  `amount` decimal(10,2) DEFAULT 0.00,
  `payment_status` enum('pending','completed','failed','refunded') DEFAULT 'pending',
  `txn_id` varchar(100) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`order_id`, `order_no`, `user_id`, `course_id`, `amount`, `payment_status`, `txn_id`, `created_at`) VALUES
(1, 'KZ2026061112246', 1, 5, 0.00, 'completed', NULL, '2026-06-11 00:12:51'),
(2, 'KZ2026061133504', 1, 1, 0.00, 'completed', NULL, '2026-06-11 00:26:15');

-- --------------------------------------------------------

--
-- Table structure for table `progress`
--

CREATE TABLE `progress` (
  `progress_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `course_id` int(11) NOT NULL,
  `lesson_id` int(11) NOT NULL,
  `is_completed` enum('yes','no') DEFAULT 'no',
  `completed_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `reviews`
--

CREATE TABLE `reviews` (
  `review_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `course_id` int(11) NOT NULL,
  `rating` int(11) DEFAULT 5,
  `review_text` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('student','instructor','admin') DEFAULT 'student',
  `avatar` varchar(500) DEFAULT '',
  `phone` varchar(20) DEFAULT '',
  `bio` text DEFAULT '',
  `status` enum('active','inactive','banned') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `name`, `email`, `password`, `role`, `avatar`, `phone`, `bio`, `status`, `created_at`, `updated_at`) VALUES
(1, 'لبنى فوزي أحمد شحاته', 'lolo@gmail.com', '$2y$10$Aec8etF6KS5Jkgt3i4omkuJd24k1wwWEjjjWWlZ4mN0k3T2C.VTWG', 'student', './uploads/avatars/avatar_1_1780951024.jpg', '', 'مطورة مواقع', 'active', '2026-06-08 17:08:43', '2026-06-08 20:37:51'),
(2, 'admin', 'admin@kanzak.com', '$2y$10$UGe1sqKHqFj35iVLPn44L.BIAjhcJBROFxxwPBV9Vp9bfrCF6ovqu', 'student', './uploads/avatars/avatar_2_1781025183.jpg', '', 'مسؤول الموقع', 'active', '2026-06-08 21:14:05', '2026-06-09 17:13:03'),
(4, 'Admin', 'admin@kanzake.com', '$2y$10$YourHashedPasswordHere', 'admin', '', '', '', 'active', '2026-06-10 19:43:11', '2026-06-10 19:43:11');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`cat_id`);

--
-- Indexes for table `contacts`
--
ALTER TABLE `contacts`
  ADD PRIMARY KEY (`contact_id`);

--
-- Indexes for table `courses`
--
ALTER TABLE `courses`
  ADD PRIMARY KEY (`course_id`),
  ADD KEY `cat_id` (`cat_id`);

--
-- Indexes for table `course_videos`
--
ALTER TABLE `course_videos`
  ADD PRIMARY KEY (`video_id`),
  ADD KEY `course_id` (`course_id`);

--
-- Indexes for table `favorites`
--
ALTER TABLE `favorites`
  ADD PRIMARY KEY (`fav_id`),
  ADD UNIQUE KEY `unique_favorite` (`user_id`,`course_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `course_id` (`course_id`);

--
-- Indexes for table `lessons`
--
ALTER TABLE `lessons`
  ADD PRIMARY KEY (`lesson_id`),
  ADD KEY `course_id` (`course_id`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`order_id`),
  ADD UNIQUE KEY `order_no` (`order_no`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `course_id` (`course_id`);

--
-- Indexes for table `progress`
--
ALTER TABLE `progress`
  ADD PRIMARY KEY (`progress_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `course_id` (`course_id`),
  ADD KEY `lesson_id` (`lesson_id`);

--
-- Indexes for table `reviews`
--
ALTER TABLE `reviews`
  ADD PRIMARY KEY (`review_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `course_id` (`course_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `cat_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `contacts`
--
ALTER TABLE `contacts`
  MODIFY `contact_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `courses`
--
ALTER TABLE `courses`
  MODIFY `course_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `course_videos`
--
ALTER TABLE `course_videos`
  MODIFY `video_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `favorites`
--
ALTER TABLE `favorites`
  MODIFY `fav_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `lessons`
--
ALTER TABLE `lessons`
  MODIFY `lesson_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `order_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `progress`
--
ALTER TABLE `progress`
  MODIFY `progress_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `reviews`
--
ALTER TABLE `reviews`
  MODIFY `review_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `courses`
--
ALTER TABLE `courses`
  ADD CONSTRAINT `courses_ibfk_1` FOREIGN KEY (`cat_id`) REFERENCES `categories` (`cat_id`) ON DELETE SET NULL;

--
-- Constraints for table `course_videos`
--
ALTER TABLE `course_videos`
  ADD CONSTRAINT `course_videos_ibfk_1` FOREIGN KEY (`course_id`) REFERENCES `courses` (`course_id`) ON DELETE CASCADE;

--
-- Constraints for table `favorites`
--
ALTER TABLE `favorites`
  ADD CONSTRAINT `fk_fav_course` FOREIGN KEY (`course_id`) REFERENCES `courses` (`course_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_fav_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `lessons`
--
ALTER TABLE `lessons`
  ADD CONSTRAINT `lessons_ibfk_1` FOREIGN KEY (`course_id`) REFERENCES `courses` (`course_id`) ON DELETE CASCADE;

--
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `orders_ibfk_2` FOREIGN KEY (`course_id`) REFERENCES `courses` (`course_id`) ON DELETE CASCADE;

--
-- Constraints for table `progress`
--
ALTER TABLE `progress`
  ADD CONSTRAINT `progress_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `progress_ibfk_2` FOREIGN KEY (`course_id`) REFERENCES `courses` (`course_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `progress_ibfk_3` FOREIGN KEY (`lesson_id`) REFERENCES `lessons` (`lesson_id`) ON DELETE CASCADE;

--
-- Constraints for table `reviews`
--
ALTER TABLE `reviews`
  ADD CONSTRAINT `reviews_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `reviews_ibfk_2` FOREIGN KEY (`course_id`) REFERENCES `courses` (`course_id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
