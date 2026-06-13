<?php
  include('./dbConnection.php');
  include('./includes/header.php');
  
  if(!isset($_GET['course_id'])) {
      redirect('courses.php');
  }
  
  $course_id = intval($_GET['course_id']);
  $_SESSION['course_id'] = $course_id;
  
  // Get course details with prepared statement
  $sql = "SELECT c.*, cat.cat_name, cat.cat_icon, 
          COUNT(DISTINCT co.order_id) as enrolled_count,
          COUNT(DISTINCT l.lesson_id) as lessons_count
          FROM courses c 
          LEFT JOIN categories cat ON c.cat_id = cat.cat_id
          LEFT JOIN orders co ON c.course_id = co.course_id AND co.payment_status = 'completed'
          LEFT JOIN lessons l ON c.course_id = l.course_id
          WHERE c.course_id = ? AND c.status = 'active'
          GROUP BY c.course_id";
  $stmt = $conn->prepare($sql);
  $stmt->bind_param("i", $course_id);
  $stmt->execute();
  $result = $stmt->get_result();
  
  if($result->num_rows == 0) {
      redirect('pageNotFound.php');
  }
  
  $course = $result->fetch_assoc();
  $discount = $course['course_original_price'] > 0 ? 
      round((($course['course_original_price'] - $course['course_price']) / $course['course_original_price']) * 100) : 0;
  
  // Check if user purchased this course
  $purchased = false;
  $progress = 0;
  if(isset($_SESSION['is_login']) && isset($_SESSION['stu_id'])) {
      $check_sql = "SELECT * FROM orders WHERE course_id = ? AND user_id = ? AND payment_status = 'completed'";
      $check_stmt = $conn->prepare($check_sql);
      $check_stmt->bind_param("ii", $course_id, $_SESSION['stu_id']);
      $check_stmt->execute();
      $purchased = $check_stmt->get_result()->num_rows > 0;
      
      // Calculate progress
      if($purchased) {
          $prog_sql = "SELECT COUNT(*) as completed FROM progress WHERE user_id = ? AND course_id = ? AND is_completed = 'yes'";
          $prog_stmt = $conn->prepare($prog_sql);
          $prog_stmt->bind_param("ii", $_SESSION['stu_id'], $course_id);
          $prog_stmt->execute();
          $completed = $prog_stmt->get_result()->fetch_assoc()['completed'] ?? 0;
          $total_lessons = $course['lessons_count'] ?? 1;
          $progress = $total_lessons > 0 ? round(($completed / $total_lessons) * 100) : 0;
      }
  }
  
  // Get lessons with video info
  $lesson_sql = "SELECT l.*, 
                 CASE 
                   WHEN l.video_type = 'youtube' THEN CONCAT('https://www.youtube.com/embed/', l.video_url)
                   WHEN l.video_type = 'vimeo' THEN CONCAT('https://player.vimeo.com/video/', l.video_url)
                   ELSE l.video_url
                 END as embed_url
                 FROM lessons l WHERE l.course_id = ? ORDER BY l.lesson_order ASC";
  $lesson_stmt = $conn->prepare($lesson_sql);
  $lesson_stmt->bind_param("i", $course_id);
  $lesson_stmt->execute();
  $lessons = $lesson_stmt->get_result();
  
  // Get course videos (preview videos)
  $video_sql = "SELECT * FROM course_videos WHERE course_id = ? ORDER BY video_id DESC";
  $video_stmt = $conn->prepare($video_sql);
  $video_stmt->bind_param("i", $course_id);
  $video_stmt->execute();
  $course_videos = $video_stmt->get_result();
  
  // Get reviews
  $review_sql = "SELECT r.*, u.name, u.avatar FROM reviews r 
                JOIN users u ON r.user_id = u.user_id 
                WHERE r.course_id = ? ORDER BY r.created_at DESC LIMIT 5";
  $review_stmt = $conn->prepare($review_sql);
  $review_stmt->bind_param("i", $course_id);
  $review_stmt->execute();
  $reviews = $review_stmt->get_result();
?>

<!-- Course Header -->
<section class="course-header py-5">
    <div class="container">
        <nav aria-label="breadcrumb" class="mb-4">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="index.php" class="text-white">الرئيسية</a></li>
                <li class="breadcrumb-item"><a href="courses.php" class="text-white">الدورات</a></li>
                <li class="breadcrumb-item active text-white-50"><?php echo $course['course_name']; ?></li>
            </ol>
        </nav>
        <div class="row align-items-center">
            <div class="col-lg-8">
                <span class="badge bg-primary mb-3">
                    <i class="fas <?php echo $course['cat_icon'] ?? 'fa-book'; ?> me-1"></i> 
                    <?php echo $course['cat_name'] ?? 'عام'; ?>
                </span>
                <h1 class="display-5 fw-bold text-white mb-3"><?php echo $course['course_name']; ?></h1>
                <p class="lead text-white-50 mb-4"><?php echo $course['course_desc']; ?></p>
                <div class="d-flex flex-wrap gap-4 text-white-50">
                    <span><i class="fas fa-user-friends me-2"></i> <?php echo $course['enrolled_count']; ?> طالب مسجل</span>
                    <span><i class="fas fa-clock me-2"></i> <?php echo $course['course_duration']; ?></span>
                    <span><i class="fas fa-star text-warning me-2"></i> <?php echo $course['rating']; ?> (<?php echo $reviews->num_rows; ?> تقييم)</span>
                    <span><i class="fas fa-layer-group me-2"></i> <?php echo $course['course_level']; ?></span>
                    <span><i class="fas fa-certificate me-2"></i> شهادة إتمام</span>
                </div>
                
                <?php if($purchased) { ?>
                <div class="mt-4">
                    <div class="d-flex align-items-center gap-3">
                        <span class="text-white">تقدمك: <?php echo $progress; ?>%</span>
                        <div class="progress flex-grow-1" style="height: 8px; max-width: 300px;">
                            <div class="progress-bar bg-success" role="progressbar" style="width: <?php echo $progress; ?>%"></div>
                        </div>
                    </div>
                </div>
                <?php } ?>
            </div>
            <div class="col-lg-4 mt-4 mt-lg-0">
                <div class="course-pricing-card">
                    <div class="text-center mb-4">
                        <img src="<?php echo $course['course_img']; ?>" class="img-fluid rounded-3 mb-3" style="max-height: 180px; object-fit: cover; width: 100%;" alt="">
                        <div class="pricing">
                            <?php if($discount > 0) { ?>
                            <span class="text-decoration-line-through text-muted h4">₹<?php echo $course['course_original_price']; ?></span>
                            <span class="badge bg-danger ms-2">خصم <?php echo $discount; ?>%</span>
                            <?php } ?>
                            <div class="h2 text-primary fw-bold mt-2">₹<?php echo $course['course_price']; ?></div>
                        </div>
                    </div>
                    
                    <?php if(isset($_SESSION['is_login'])) { ?>
                        <?php if($purchased) { ?>
                            <a href="student/watchCourse.php?course_id=<?php echo $course_id; ?>" 
                               class="btn btn-success w-100 py-3 rounded-pill fw-bold mb-3">
                                <i class="fas fa-play-circle me-2"></i> استمر في التعلم
                            </a>
                            <div class="text-center text-success">
                                <i class="fas fa-check-circle me-1"></i> تم شراء هذه الدورة
                            </div>
                        <?php } else { ?>
                            <form action="checkout.php" method="post">
                                <input type="hidden" name="id" value="<?php echo $course['course_price']; ?>">
                                <input type="hidden" name="course_id" value="<?php echo $course_id; ?>">
                                <button type="submit" class="btn btn-primary w-100 py-3 rounded-pill fw-bold mb-3">
                                    <i class="fas fa-shopping-cart me-2"></i> اشترِ الآن
                                </button>
                            </form>
                        <?php } ?>
                    <?php } else { ?>
                        <a href="loginorsignup.php" class="btn btn-primary w-100 py-3 rounded-pill fw-bold mb-3">
                            <i class="fas fa-user-lock me-2"></i> سجل الدخول للشراء
                        </a>
                    <?php } ?>
                    
                    <ul class="list-unstyled text-muted small mt-3">
                        <li class="mb-2"><i class="fas fa-check text-success me-2"></i> مدى الحياة الوصول</li>
                        <li class="mb-2"><i class="fas fa-check text-success me-2"></i> <?php echo $course['lessons_count']; ?> درس</li>
                        <li class="mb-2"><i class="fas fa-check text-success me-2"></i> شهادة إتمام</li>
                        <li class="mb-2"><i class="fas fa-check text-success me-2"></i> وصول على الجوال</li>
                        <li class="mb-2"><i class="fas fa-check text-success me-2"></i> ضمان استرداد 30 يوم</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Course Content -->
<section class="py-5">
    <div class="container">
        <div class="row">
            <div class="col-lg-8">
                <!-- Course Preview Video -->
                <?php if($course_videos && $course_videos->num_rows > 0) { 
                    $preview_video = $course_videos->fetch_assoc();
                ?>
                <div class="card border-0 shadow-sm mb-4 rounded-4">
                    <div class="card-body p-0">
                        <div class="video-preview-wrapper" style="border-radius: 16px;">
                            <?php if($preview_video['video_type'] == 'youtube') { ?>
                                <iframe class="course-video-iframe" 
                                        src="https://www.youtube.com/embed/<?php echo $preview_video['video_url']; ?>" 
                                        allowfullscreen></iframe>
                            <?php } elseif($preview_video['video_type'] == 'vimeo') { ?>
                                <iframe class="course-video-iframe" 
                                        src="https://player.vimeo.com/video/<?php echo $preview_video['video_url']; ?>" 
                                        allowfullscreen></iframe>
                            <?php } else { ?>
                                <video class="course-video-player" controls>
                                    <source src="<?php echo $preview_video['video_url']; ?>" type="video/mp4">
                                    متصفحك لا يدعم تشغيل الفيديو.
                                </video>
                            <?php } ?>
                        </div>
                        <div class="p-3">
                            <h6 class="fw-bold mb-1"><?php echo $preview_video['title']; ?></h6>
                            <span class="badge bg-<?php echo $preview_video['video_type'] == 'youtube' ? 'danger' : ($preview_video['video_type'] == 'vimeo' ? 'info' : 'success'); ?> rounded-pill">
                                <i class="fab fa-<?php echo $preview_video['video_type']; ?> me-1"></i> 
                                <?php echo $preview_video['video_type']; ?>
                            </span>
                        </div>
                    </div>
                </div>
                <?php } ?>
                
                <!-- What You'll Learn -->
                <div class="card border-0 shadow-sm mb-4 rounded-4">
                    <div class="card-body p-4">
                        <h4 class="fw-bold mb-4"><i class="fas fa-bullseye text-primary me-2"></i> ما ستتعلمه</h4>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <div class="d-flex align-items-start">
                                    <i class="fas fa-check-circle text-success mt-1 me-2"></i>
                                    <span>فهم المفاهيم الأساسية والمتقدمة في المجال</span>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="d-flex align-items-start">
                                    <i class="fas fa-check-circle text-success mt-1 me-2"></i>
                                    <span>بناء مشاريع عملية من الواقع</span>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="d-flex align-items-start">
                                    <i class="fas fa-check-circle text-success mt-1 me-2"></i>
                                    <span>الحصول على شهادة معتمدة</span>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="d-flex align-items-start">
                                    <i class="fas fa-check-circle text-success mt-1 me-2"></i>
                                    <span>التحضير لسوق العمل</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Course Description -->
                <div class="card border-0 shadow-sm mb-4 rounded-4">
                    <div class="card-body p-4">
                        <h4 class="fw-bold mb-3"><i class="fas fa-align-left text-primary me-2"></i> وصف الدورة</h4>
                        <p class="text-muted"><?php echo nl2br($course['course_detail'] ?? $course['course_desc']); ?></p>
                    </div>
                </div>
                
                <!-- Course Lessons -->
                <div class="card border-0 shadow-sm mb-4 rounded-4">
                    <div class="card-body p-4">
                        <h4 class="fw-bold mb-4">
                            <i class="fas fa-list-ol text-primary me-2"></i> محتوى الدورة
                            <span class="badge bg-light text-dark ms-2"><?php echo $lessons->num_rows; ?> درس</span>
                        </h4>
                        <?php if($lessons->num_rows > 0) { 
                            $lesson_num = 0;
                            while($lesson = $lessons->fetch_assoc()) {
                                $lesson_num++;
                                $is_free = $lesson['is_free'] == 'yes';
                                $can_watch = $purchased || $is_free;
                        ?>
                        <div class="lesson-item d-flex align-items-center p-3 rounded-3 mb-2" 
                             style="background: <?php echo $can_watch ? '#f8fafc' : '#fff'; ?>;">
                            <div class="lesson-number me-3 flex-shrink-0">
                                <?php echo $lesson_num; ?>
                            </div>
                            <div class="flex-grow-1">
                                <h6 class="mb-1 fw-bold"><?php echo $lesson['lesson_name']; ?></h6>
                                <small class="text-muted">
                                    <i class="fas fa-clock me-1"></i> <?php echo $lesson['duration'] ?? '15:00'; ?>
                                    <?php if($is_free) { ?>
                                    <span class="badge bg-success bg-opacity-10 text-success ms-2">مجاني</span>
                                    <?php } ?>
                                    <?php if($lesson['video_type'] == 'youtube') { ?>
                                    <span class="badge bg-danger bg-opacity-10 text-danger ms-1"><i class="fab fa-youtube me-1"></i>YouTube</span>
                                    <?php } elseif($lesson['video_type'] == 'vimeo') { ?>
                                    <span class="badge bg-info bg-opacity-10 text-info ms-1"><i class="fab fa-vimeo-v me-1"></i>Vimeo</span>
                                    <?php } elseif($lesson['video_type'] == 'local') { ?>
                                    <span class="badge bg-success bg-opacity-10 text-success ms-1"><i class="fas fa-video me-1"></i>محلي</span>
                                    <?php } ?>
                                </small>
                            </div>
                            <?php if($can_watch) { ?>
                                <a href="<?php echo $purchased ? 'student/watchCourse.php?course_id='.$course_id.'&lesson_id='.$lesson['lesson_id'] : '#'; ?>" 
                                   class="btn btn-sm btn-outline-primary rounded-pill"
                                   <?php if(!$purchased) { ?>onclick="openPreviewModal('<?php echo $lesson['video_url']; ?>', '<?php echo $lesson['video_type']; ?>', '<?php echo htmlspecialchars($lesson['lesson_name'], ENT_QUOTES); ?>')"<?php } ?>>
                                    <i class="fas fa-play me-1"></i> <?php echo $purchased ? 'شاهد' : 'معاينة'; ?>
                                </a>
                            <?php } else { ?>
                                <span class="text-muted"><i class="fas fa-lock"></i></span>
                            <?php } ?>
                        </div>
                        <?php } } else { ?>
                        <div class="text-center py-4 text-muted">
                            <i class="fas fa-folder-open fa-2x mb-2"></i>
                            <p>سيتم إضافة الدروس قريباً</p>
                        </div>
                        <?php } ?>
                    </div>
                </div>
                
                <!-- Reviews -->
                <div class="card border-0 shadow-sm rounded-4">
                    <div class="card-body p-4">
                        <h4 class="fw-bold mb-4">
                            <i class="fas fa-comments text-primary me-2"></i> التقييمات
                            <span class="badge bg-light text-dark ms-2"><?php echo $reviews->num_rows; ?></span>
                        </h4>
                        <?php if($reviews->num_rows > 0) { 
                            while($review = $reviews->fetch_assoc()) {
                        ?>
                        <div class="d-flex mb-4 pb-4 border-bottom">
                            <img src="<?php echo $review['avatar'] ?? 'https://ui-avatars.com/api/?name='.urlencode($review['name']).'&background=random'; ?>" 
                                 class="rounded-circle me-3" width="50" height="50" alt="">
                            <div class="flex-grow-1">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <h6 class="fw-bold mb-0"><?php echo $review['name']; ?></h6>
                                    <small class="text-muted"><?php echo date('Y-m-d', strtotime($review['created_at'])); ?></small>
                                </div>
                                <div class="text-warning mb-2">
                                    <?php for($i=0; $i<$review['rating']; $i++) { ?>
                                        <i class="fas fa-star"></i>
                                    <?php } ?>
                                </div>
                                <p class="text-muted mb-0"><?php echo $review['review_text']; ?></p>
                            </div>
                        </div>
                        <?php } } else { ?>
                        <div class="text-center py-4 text-muted">
                            <i class="fas fa-comment-slash fa-2x mb-2"></i>
                            <p>لا توجد تقييمات بعد. كن أول من يقيم!</p>
                        </div>
                        <?php } ?>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-4">
                <!-- Instructor Info -->
                <div class="card border-0 shadow-sm mb-4 rounded-4">
                    <div class="card-body p-4">
                        <h5 class="fw-bold mb-3"><i class="fas fa-chalkboard-teacher text-primary me-2"></i> المحاضر</h5>
                        <div class="d-flex align-items-center mb-3">
                            <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($course['instructor_name']); ?>&background=random&size=100" 
                                 class="rounded-circle me-3" width="60" height="60" alt="">
                            <div>
                                <h6 class="fw-bold mb-1"><?php echo $course['instructor_name']; ?></h6>
                                <small class="text-muted"><?php echo $course['instructor_bio'] ?? 'خبير في المجال'; ?></small>
                                <div class="text-warning small">
                                    <i class="fas fa-star"></i> 4.9
                                    <span class="text-muted">• <?php echo rand(5, 15); ?> دورة</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Related Courses -->
                <div class="card border-0 shadow-sm rounded-4">
                    <div class="card-body p-4">
                        <h5 class="fw-bold mb-3"><i class="fas fa-th-large text-primary me-2"></i> دورات مشابهة</h5>
                        <?php
                        $related_sql = "SELECT * FROM courses WHERE course_id != ? AND cat_id = ? AND status = 'active' LIMIT 3";
                        $related_stmt = $conn->prepare($related_sql);
                        $related_stmt->bind_param("ii", $course_id, $course['cat_id']);
                        $related_stmt->execute();
                        $related = $related_stmt->get_result();
                        
                        if($related->num_rows > 0) {
                            while($rel = $related->fetch_assoc()) {
                        ?>
                        <a href="coursedetails.php?course_id=<?php echo $rel['course_id']; ?>" class="text-decoration-none">
                            <div class="d-flex align-items-center mb-3 p-2 rounded-3 hover-bg-light">
                                <img src="<?php echo $rel['course_img']; ?>" class="rounded-3 me-3" width="80" height="60" style="object-fit: cover;" alt="">
                                <div>
                                    <h6 class="fw-bold mb-1 text-dark small"><?php echo $rel['course_name']; ?></h6>
                                    <span class="text-primary fw-bold">₹<?php echo $rel['course_price']; ?></span>
                                </div>
                            </div>
                        </a>
                        <?php } } else { ?>
                        <p class="text-muted text-center py-3">لا توجد دورات مشابهة</p>
                        <?php } ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<?php 
    include('./includes/footer.php');
?>

<!-- Preview Video Modal -->
<div class="modal fade" id="previewModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 20px; overflow: hidden;">
            <div class="modal-header border-0 bg-primary text-white">
                <h5 class="modal-title fw-bold" id="previewModalTitle">
                    <i class="fas fa-play-circle me-2"></i> معاينة الدرس
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-0 bg-dark">
                <div class="ratio ratio-16x9" id="previewVideoContainer">
                    <!-- Video will be inserted here -->
                </div>
            </div>
            <div class="modal-footer border-0 justify-content-center">
                <p class="text-muted mb-0">
                    <i class="fas fa-info-circle text-primary me-1"></i>
                    هذا درس مجاني للمعاينة. <a href="loginorsignup.php" class="text-primary fw-bold">سجل الآن</a> للوصول الكامل!
                </p>
            </div>
        </div>
    </div>
</div>

<style>
/* Modal Animation */
.modal.fade .modal-dialog {
    transition: transform 0.3s ease-out;
    transform: scale(0.95);
}
.modal.show .modal-dialog {
    transform: scale(1);
}
#previewModal .modal-content {
    animation: modalSlideIn 0.3s ease;
}
@keyframes modalSlideIn {
    from { opacity: 0; transform: translateY(-30px); }
    to { opacity: 1; transform: translateY(0); }
}
</style>

<script>
// Preview Modal Function
function openPreviewModal(videoUrl, videoType, lessonName) {
    if(!videoUrl || videoUrl === '') {
        alert('عذراً، لا يوجد فيديو متاح للمعاينة');
        return;
    }

    var container = document.getElementById('previewVideoContainer');
    var title = document.getElementById('previewModalTitle');

    // Update title
    title.innerHTML = '<i class="fas fa-play-circle me-2"></i> ' + (lessonName || 'معاينة الدرس');

    // Clear previous content
    container.innerHTML = '';

    var videoHtml = '';

    if(videoType === 'youtube') {
        // Extract YouTube ID if full URL
        var videoId = videoUrl;
        var match = videoUrl.match(/(?:youtube\.com\/(?:[^\/]+\/.+\/|(?:v|e(?:mbed)?)\/|.*[?&]v=)|youtu\.be\/)([^"&?\/\s]{11})/);
        if(match) videoId = match[1];

        videoHtml = '<iframe src="https://www.youtube.com/embed/' + videoId + '?autoplay=1&rel=0" ' +
                   'allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" ' +
                   'allowfullscreen style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; border: none;"></iframe>';
    } else if(videoType === 'vimeo') {
        var vimeoId = videoUrl;
        var vimeoMatch = videoUrl.match(/vimeo\.com\/(\d+)/);
        if(vimeoMatch) vimeoId = vimeoMatch[1];

        videoHtml = '<iframe src="https://player.vimeo.com/video/' + vimeoId + '?autoplay=1" ' +
                   'allow="autoplay; fullscreen; picture-in-picture" ' +
                   'allowfullscreen style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; border: none;"></iframe>';
    } else {
        // Local video
        videoHtml = '<video controls autoplay style="width: 100%; height: 100%; background: #000;">' +
                   '<source src="' + videoUrl + '" type="video/mp4">' +
                   'متصفحك لا يدعم تشغيل الفيديو.' +
                   '</video>';
    }

    container.innerHTML = videoHtml;

    // Show modal
    var modal = new bootstrap.Modal(document.getElementById('previewModal'));
    modal.show();
}

// Stop video when modal closes
document.getElementById('previewModal').addEventListener('hidden.bs.modal', function() {
    document.getElementById('previewVideoContainer').innerHTML = '';
});
</script>