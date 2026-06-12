<?php
/**
 * Admin Video Manager - إدارة الفيديوهات
 * Add/Edit/Delete videos from database
 */
include('../dbConnection.php');

if(!isAdmin()) {
    redirect('login.php');
}

$page_title = 'إدارة الفيديوهات';
$success_msg = '';
$error_msg = '';

// Extract YouTube ID
function getYouTubeId($url) {
    $pattern = '/(?:youtube\.com\/(?:[^\/]+\/.+\/|(?:v|e(?:mbed)?)\/|.*[?&]v=)|youtu\.be\/)([^"&?\/\s]{11})/';
    if(preg_match($pattern, $url, $matches)) {
        return $matches[1];
    }
    return $url;
}

// Extract Vimeo ID
function getVimeoId($url) {
    if(preg_match('/vimeo\.com\/(\d+)/', $url, $matches)) {
        return $matches[1];
    }
    return $url;
}

// Handle: Add/Edit Video
if(isset($_POST['save_video'])) {
    $video_id = isset($_POST['video_id']) ? intval($_POST['video_id']) : 0;
    $course_id = intval($_POST['course_id']);
    $lesson_id = !empty($_POST['lesson_id']) ? intval($_POST['lesson_id']) : null;
    $title = sanitize($_POST['title']);
    $video_type = sanitize($_POST['video_type']);
    $is_preview = isset($_POST['is_preview']) ? 1 : 0;

    $video_url = '';

    if($video_type == 'youtube') {
        $video_url = getYouTubeId(sanitize($_POST['video_url']));
        if(strlen($video_url) != 11) {
            $error_msg = 'معرف YouTube غير صحيح (يجب 11 حرف)';
        }
    } 
    elseif($video_type == 'vimeo') {
        $video_url = getVimeoId(sanitize($_POST['video_url']));
    } 
    elseif($video_type == 'local') {
        if(isset($_FILES['video_file']) && $_FILES['video_file']['error'] === UPLOAD_ERR_OK) {
            $upload = uploadVideoFile($_FILES['video_file'], '../uploads/videos/');
            if(isset($upload['success'])) {
                $video_url = str_replace('../', './', $upload['path']);
            } else {
                $error_msg = $upload['error'] ?? 'فشل في رفع الفيديو';
            }
        } elseif($video_id == 0) {
            $error_msg = 'الرجاء اختيار ملف الفيديو';
        }
    }

    if(empty($error_msg)) {
        if($video_id > 0) {
            if($video_url) {
                $sql = "UPDATE course_videos SET course_id=?, lesson_id=?, video_url=?, video_type=?, title=?, is_preview=? WHERE video_id=?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("iisssii", $course_id, $lesson_id, $video_url, $video_type, $title, $is_preview, $video_id);
            } else {
                $sql = "UPDATE course_videos SET course_id=?, lesson_id=?, video_type=?, title=?, is_preview=? WHERE video_id=?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("iissii", $course_id, $lesson_id, $video_type, $title, $is_preview, $video_id);
            }
        } else {
            $sql = "INSERT INTO course_videos (course_id, lesson_id, video_url, video_type, title, is_preview) VALUES (?, ?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("iisssi", $course_id, $lesson_id, $video_url, $video_type, $title, $is_preview);
        }

        if($stmt->execute()) {
            $success_msg = $video_id > 0 ? 'تم تحديث الفيديو' : 'تم إضافة الفيديو';
        } else {
            $error_msg = 'خطأ في قاعدة البيانات';
        }
    }
}

// Handle: Delete Video
if(isset($_GET['delete'])) {
    $video_id = intval($_GET['delete']);
    $check = $conn->prepare("SELECT video_url, video_type FROM course_videos WHERE video_id = ?");
    $check->bind_param("i", $video_id);
    $check->execute();
    $result = $check->get_result();

    if($result->num_rows > 0) {
        $video = $result->fetch_assoc();
        if($video['video_type'] == 'local' && file_exists($video['video_url'])) {
            unlink($video['video_url']);
        }
        $conn->query("DELETE FROM course_videos WHERE video_id = $video_id");
        $success_msg = 'تم حذف الفيديو';
    }
}

// Get all videos
$videos = $conn->query("SELECT v.*, c.course_name, l.lesson_name 
                      FROM course_videos v 
                      LEFT JOIN courses c ON v.course_id = c.course_id 
                      LEFT JOIN lessons l ON v.lesson_id = l.lesson_id 
                      ORDER BY v.video_id DESC");

// Get courses for dropdown
$courses = $conn->query("SELECT course_id, course_name FROM courses ORDER BY course_name");

// Get for edit
$edit_video = null;
if(isset($_GET['edit'])) {
    $edit_id = intval($_GET['edit']);
    $edit_result = $conn->query("SELECT * FROM course_videos WHERE video_id = $edit_id");
    if($edit_result->num_rows > 0) {
        $edit_video = $edit_result->fetch_assoc();
    }
}

include('./includes/header.php');
include('./includes/sidebar.php');
?>

<div class="admin-main">
    <div class="container-fluid">
        <!-- Page Header -->
        <div class="page-header">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="dashboard.php">الرئيسية</a></li>
                    <li class="breadcrumb-item active">الفيديوهات</li>
                </ol>
            </nav>
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h2><i class="fas fa-video me-2"></i> إدارة الفيديوهات</h2>
                    <p class="mb-0">إضافة فيديوهات YouTube أو رفع ملفات محلية</p>
                </div>
                <button class="btn btn-light rounded-pill" data-bs-toggle="modal" data-bs-target="#videoModal" onclick="resetVideoForm()">
                    <i class="fas fa-plus me-2"></i> فيديو جديد
                </button>
            </div>
        </div>

        <!-- Alerts -->
        <?php if($success_msg): ?>
        <div class="alert alert-success admin-alert"><?php echo $success_msg; ?></div>
        <?php endif; ?>
        <?php if($error_msg): ?>
        <div class="alert alert-danger admin-alert"><?php echo $error_msg; ?></div>
        <?php endif; ?>

        <!-- Videos Grid -->
        <div class="row g-4">
            <?php if($videos && $videos->num_rows > 0) { 
                while($video = $videos->fetch_assoc()) { 
            ?>
            <div class="col-md-6 col-lg-4">
                <div class="admin-card p-0 overflow-hidden">
                    <!-- Video Preview -->
                    <div class="position-relative" style="padding-bottom: 56.25%; background: #000;">
                        <?php if($video['video_type'] == 'youtube') { ?>
                            <iframe src="https://www.youtube.com/embed/<?php echo $video['video_url']; ?>" 
                                    style="position:absolute;top:0;left:0;width:100%;height:100%;border:none;"
                                    allowfullscreen></iframe>
                        <?php } elseif($video['video_type'] == 'vimeo') { ?>
                            <iframe src="https://player.vimeo.com/video/<?php echo $video['video_url']; ?>" 
                                    style="position:absolute;top:0;left:0;width:100%;height:100%;border:none;"
                                    allowfullscreen></iframe>
                        <?php } else { ?>
                            <video controls style="position:absolute;top:0;left:0;width:100%;height:100%;">
                                <source src="<?php echo $video['video_url']; ?>" type="video/mp4">
                            </video>
                        <?php } ?>
                    </div>
                    <div class="p-4">
                        <div class="d-flex justify-content-between mb-2">
                            <h6 class="fw-bold mb-0"><?php echo htmlspecialchars($video['title']); ?></h6>
                            <?php if($video['is_preview']) { ?>
                                <span class="badge bg-success">معاينة</span>
                            <?php } ?>
                        </div>
                        <p class="text-muted small mb-2">
                            <i class="fas fa-book me-1 text-primary"></i> <?php echo htmlspecialchars($video['course_name'] ?? 'غير محدد'); ?>
                        </p>
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="badge bg-<?php echo $video['video_type'] == 'youtube' ? 'danger' : ($video['video_type'] == 'vimeo' ? 'info' : 'success'); ?>">
                                <i class="fab fa-<?php echo $video['video_type']; ?> me-1"></i> <?php echo $video['video_type']; ?>
                            </span>
                            <div class="d-flex gap-1">
                                <a href="?edit=<?php echo $video['video_id']; ?>" class="btn btn-sm btn-warning text-white" data-bs-toggle="modal" data-bs-target="#videoModal">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <a href="?delete=<?php echo $video['video_id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('حذف هذا الفيديو؟')">
                                    <i class="fas fa-trash"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php } } else { ?>
            <div class="col-12 text-center py-5">
                <i class="fas fa-video fa-3x text-muted mb-3"></i>
                <p>لا توجد فيديوهات</p>
            </div>
            <?php } ?>
        </div>
    </div>
</div>

<!-- Video Modal -->
<div class="modal fade admin-modal" id="videoModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-bold">
                    <i class="fas fa-video text-primary me-2"></i>
                    <?php echo $edit_video ? 'تعديل فيديو' : 'فيديو جديد'; ?>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="" enctype="multipart/form-data" class="admin-form">
                <div class="modal-body">
                    <input type="hidden" name="video_id" value="<?php echo $edit_video['video_id'] ?? ''; ?>">

                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label">العنوان <span class="text-danger">*</span></label>
                            <input type="text" name="title" class="form-control" required 
                                   value="<?php echo htmlspecialchars($edit_video['title'] ?? ''); ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">الدورة <span class="text-danger">*</span></label>
                            <select name="course_id" class="form-select" required>
                                <option value="">اختر الدورة</option>
                                <?php while($course = $courses->fetch_assoc()) { ?>
                                <option value="<?php echo $course['course_id']; ?>" <?php echo ($edit_video && $edit_video['course_id'] == $course['course_id']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($course['course_name']); ?>
                                </option>
                                <?php } ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">نوع الفيديو <span class="text-danger">*</span></label>
                            <select name="video_type" class="form-select" id="videoType" onchange="toggleVideoInput()">
                                <option value="youtube" <?php echo (!$edit_video || $edit_video['video_type'] == 'youtube') ? 'selected' : ''; ?>>YouTube</option>
                                <option value="vimeo" <?php echo ($edit_video && $edit_video['video_type'] == 'vimeo') ? 'selected' : ''; ?>>Vimeo</option>
                                <option value="local" <?php echo ($edit_video && $edit_video['video_type'] == 'local') ? 'selected' : ''; ?>>ملف محلي</option>
                            </select>
                        </div>
                        <div class="col-12" id="urlInput">
                            <label class="form-label">رابط/معرف الفيديو <span class="text-danger">*</span></label>
                            <input type="text" name="video_url" class="form-control" 
                                   value="<?php echo htmlspecialchars($edit_video['video_url'] ?? ''); ?>"
                                   placeholder="معرف YouTube (11 حرف) أو رابط Vimeo">
                            <small class="text-muted">YouTube: dQw4w9WgXcQ أو https://youtube.com/watch?v=dQw4w9WgXcQ</small>
                        </div>
                        <div class="col-12" id="fileInput" style="display:none;">
                            <label class="form-label">ملف الفيديو</label>
                            <input type="file" name="video_file" class="form-control" accept="video/*">
                            <?php if($edit_video && $edit_video['video_type'] == 'local') { ?>
                            <small class="text-muted">الملف الحالي: <?php echo $edit_video['video_url']; ?></small>
                            <?php } ?>
                        </div>
                        <div class="col-12">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="is_preview" id="isPreview" 
                                    <?php echo ($edit_video && $edit_video['is_preview']) ? 'checked' : ''; ?>>
                                <label class="form-check-label fw-bold" for="isPreview">
                                    <i class="fas fa-eye text-primary me-1"></i> فيديو معاينة (متاح للجميع)
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary rounded-pill" data-bs-dismiss="modal">إلغاء</button>
                    <button type="submit" name="save_video" class="btn btn-admin-primary rounded-pill">
                        <i class="fas fa-save me-2"></i> حفظ
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function toggleVideoInput() {
    var type = document.getElementById('videoType').value;
    document.getElementById('urlInput').style.display = type === 'local' ? 'none' : 'block';
    document.getElementById('fileInput').style.display = type === 'local' ? 'block' : 'none';
}
function resetVideoForm() {
    document.querySelector('#videoModal form').reset();
    document.querySelector('input[name="video_id"]').value = '';
    toggleVideoInput();
}
<?php if($edit_video): ?>
document.addEventListener('DOMContentLoaded', function() {
    toggleVideoInput();
    var modal = new bootstrap.Modal(document.getElementById('videoModal'));
    modal.show();
});
<?php endif; ?>
</script>

<?php include('./includes/footer.php'); ?>