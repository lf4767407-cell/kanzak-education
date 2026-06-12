<?php
include('../dbConnection.php');

if(!isAdmin()) {
    redirect('../index.php');
}

// Handle delete
if(isset($_GET['delete'])) {
    $lesson_id = intval($_GET['delete']);

    // Get lesson info to delete local video if exists
    $lesson_info = mysqli_query($conn, "SELECT video_url FROM lessons WHERE lesson_id = '$lesson_id'");
    $lesson_data = mysqli_fetch_assoc($lesson_info);

    if($lesson_data && !empty($lesson_data['video_url']) && strpos($lesson_data['video_url'], 'http') !== 0 && file_exists($lesson_data['video_url'])) {
        unlink($lesson_data['video_url']);
    }

    mysqli_query($conn, "DELETE FROM lessons WHERE lesson_id = '$lesson_id'");
    redirect('lessons.php');
}

// Handle add/edit
if(isset($_POST['save_lesson'])) {
    $lesson_id = isset($_POST['lesson_id']) ? intval($_POST['lesson_id']) : 0;
    $course_id = intval($_POST['course_id']);
    $name = sanitize($_POST['lesson_name']);
    $desc = sanitize($_POST['lesson_desc']);
    $order = intval($_POST['lesson_order']);
    $duration = sanitize($_POST['duration']);
    $is_free = isset($_POST['is_free']) ? 'yes' : 'no';
    $video_type = sanitize($_POST['video_type'] ?? 'youtube');
    $video_url = sanitize($_POST['video_url'] ?? '');

    // Extract YouTube/Vimeo ID if full URL provided
    if(!empty($video_url)) {
        if($video_type == 'youtube') {
            $video_url = extractYouTubeId($video_url);
        } elseif($video_type == 'vimeo') {
            $vimeo_match = null;
            if(preg_match('/vimeo\.com\/(\d+)/', $video_url, $vimeo_match)) {
                $video_url = $vimeo_match[1];
            }
        }
        // For local, keep path as is
    }

    // Handle local video upload (overrides URL if provided)
    if(isset($_FILES['video_file']) && $_FILES['video_file']['error'] === UPLOAD_ERR_OK) {
        $upload_result = uploadVideoFile($_FILES['video_file'], '../uploads/lessons/');
        if(isset($upload_result['success'])) {
            $video_url = str_replace('../', './', $upload_result['path']);
            $video_type = 'local';
        }
    }

    if($lesson_id > 0) {
        // Update existing lesson
        if($file_path) {
            $sql = "UPDATE lessons SET course_id='$course_id', lesson_name='$name', lesson_desc='$desc', 
                    lesson_order='$order', duration='$duration', is_free='$is_free', video_url='$video_url',
                    video_type='$video_type', WHERE lesson_id='$lesson_id'";
        } else {
            $sql = "UPDATE lessons SET course_id='$course_id', lesson_name='$name', lesson_desc='$desc', 
                    lesson_order='$order', duration='$duration', is_free='$is_free', video_url='$video_url',
                    video_type='$video_type' WHERE lesson_id='$lesson_id'";
        }
    } else {
        // Insert new lesson
        $sql = "INSERT INTO lessons (course_id, lesson_name, lesson_desc, video_url, video_type,
                lesson_order, duration, is_free) 
                VALUES ('$course_id', '$name', '$desc', '$video_url', '$video_type', 
                '$order', '$duration', '$is_free')";
    }

    $result = mysqli_query($conn, $sql);
    if(!$result) {
        die("Error: " . mysqli_error($conn));
    }
    redirect('lessons.php');
}

// Get lessons with course info
$lessons = mysqli_query($conn, "SELECT l.*, c.course_name FROM lessons l JOIN courses c ON l.course_id = c.course_id ORDER BY l.course_id, l.lesson_order");

// Get courses for dropdown
$courses = mysqli_query($conn, "SELECT course_id, course_name FROM courses ORDER BY course_name");

// Get lesson for edit
$edit_lesson = null;
if(isset($_GET['edit'])) {
    $edit_id = intval($_GET['edit']);
    $edit_result = mysqli_query($conn, "SELECT * FROM lessons WHERE lesson_id = '$edit_id'");
    $edit_lesson = mysqli_fetch_assoc($edit_result);
}

include('../includes/header.php');
?>

<style>
    .admin-sidebar { background: #1e293b; min-height: 100vh; color: white; padding: 20px 0; position: fixed; right: 0; top: 0; width: 260px; z-index: 1000; transition: transform 0.3s ease; }
    .admin-main { margin-right: 260px; padding: 30px; min-height: 100vh; background: #f8fafc; transition: margin-right 0.3s ease; }
    .admin-link { display: flex; align-items: center; gap: 10px; padding: 12px 20px; color: rgba(255,255,255,0.7); text-decoration: none; transition: all 0.3s; border-right: 3px solid transparent; }
    .admin-link:hover, .admin-link.active { background: rgba(255,255,255,0.1); color: white; border-right-color: #6366f1; }
    @media (max-width: 991px) { .admin-sidebar { transform: translateX(100%); } .admin-sidebar.active { transform: translateX(0); } .admin-main { margin-right: 0; } }
    .video-preview-small { position: relative; padding-bottom: 56.25%; height: 0; overflow: hidden; border-radius: 8px; background: #000; }
    .video-preview-small iframe, .video-preview-small video { position: absolute; top: 0; left: 0; width: 100%; height: 100%; border: none; }
</style>

<div class="admin-sidebar" id="adminSidebar">
    <div class="text-center p-4 border-bottom border-secondary">
        <i class="fas fa-shield-alt fa-2x text-primary mb-2"></i>
        <h5 class="fw-bold mb-0">لوحة التحكم</h5>
    </div>
    <nav class="mt-3">
        <a href="index.php" class="admin-link"><i class="fas fa-tachometer-alt"></i> الرئيسية</a>
        <a href="students.php" class="admin-link"><i class="fas fa-users"></i> الطلاب</a>
        <a href="courses.php" class="admin-link"><i class="fas fa-book"></i> الدورات</a>
        <a href="lessons.php" class="admin-link active"><i class="fas fa-video"></i> الدروس</a>
        <a href="orders.php" class="admin-link"><i class="fas fa-shopping-cart"></i> الطلبات</a>
        <a href="contacts.php" class="admin-link"><i class="fas fa-envelope"></i> الرسائل</a>
        <a href="../logout.php" class="admin-link text-danger"><i class="fas fa-sign-out-alt"></i> تسجيل الخروج</a>
    </nav>
</div>

<div class="admin-main">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold mb-0">إدارة الدروس</h4>
            <small class="text-muted">إضافة، تعديل وحذف الدروس مع الفيديوهات</small>
        </div>
        <button class="btn btn-primary rounded-pill" data-bs-toggle="modal" data-bs-target="#lessonModal" onclick="resetForm()">
            <i class="fas fa-plus me-2"></i> درس جديد
        </button>
    </div>

    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead class="bg-light">
                        <tr><th>#</th><th>الدورة</th><th>الدرس</th><th>الترتيب</th><th>المدة</th><th>الفيديو</th><th>مجاني</th><th>الإجراءات</th></tr>
                    </thead>
                    <tbody>
                        <?php if(mysqli_num_rows($lessons) > 0) { $num = 0; while($lesson = mysqli_fetch_assoc($lessons)) { $num++; ?>
                        <tr>
                            <td><?php echo $num; ?></td>
                            <td class="fw-bold"><?php echo $lesson['course_name']; ?></td>
                            <td><?php echo $lesson['lesson_name']; ?></td>
                            <td><?php echo $lesson['lesson_order']; ?></td>
                            <td><?php echo $lesson['duration']; ?></td>
                            <td>
                                <?php if(!empty($lesson['video_url'])) { 
                                    $vtype = $lesson['video_type'] ?? 'local';
                                    if($vtype == 'youtube' || strpos($lesson['video_url'], 'youtube') !== false || strpos($lesson['video_url'], 'youtu.be') !== false) { ?>
                                    <span class="badge bg-danger"><i class="fab fa-youtube me-1"></i> YouTube</span>
                                    <?php } elseif($vtype == 'vimeo' || strpos($lesson['video_url'], 'vimeo') !== false) { ?>
                                    <span class="badge bg-info"><i class="fab fa-vimeo-v me-1"></i> Vimeo</span>
                                    <?php } else { ?>
                                    <span class="badge bg-success"><i class="fas fa-video me-1"></i> محلي</span>
                                    <?php } ?>
                                <?php } else { ?>
                                    <span class="badge bg-secondary">لا يوجد</span>
                                <?php } ?>
                            </td>
                            <td><?php echo $lesson['is_free'] == 'yes' ? '<span class="badge bg-success">نعم</span>' : '<span class="badge bg-secondary">لا</span>'; ?></td>
                            <td>
                                <a href="?edit=<?php echo $lesson['lesson_id']; ?>" class="btn btn-sm btn-primary rounded-pill me-1" data-bs-toggle="modal" data-bs-target="#lessonModal"><i class="fas fa-edit"></i></a>
                                <a href="?delete=<?php echo $lesson['lesson_id']; ?>" class="btn btn-sm btn-danger rounded-pill" onclick="return confirm('هل أنت متأكد؟')"><i class="fas fa-trash"></i></a>
                            </td>
                        </tr>
                        <?php } } else { ?><tr><td colspan="8" class="text-center py-4 text-muted">لا توجد دروس</td></tr><?php } ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Lesson Modal -->
<div class="modal fade" id="lessonModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-bold"><?php echo $edit_lesson ? 'تعديل درس' : 'درس جديد'; ?></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="" enctype="multipart/form-data">
                <div class="modal-body">
                    <input type="hidden" name="lesson_id" value="<?php echo $edit_lesson['lesson_id'] ?? ''; ?>">
                    <div class="mb-3">
                        <label class="form-label fw-bold">الدورة</label>
                        <select name="course_id" class="form-select" required>
                            <option value="">اختر الدورة</option>
                            <?php mysqli_data_seek($courses, 0); while($course = mysqli_fetch_assoc($courses)) { ?>
                            <option value="<?php echo $course['course_id']; ?>" <?php echo ($edit_lesson && $edit_lesson['course_id'] == $course['course_id']) ? 'selected' : ''; ?>><?php echo $course['course_name']; ?></option>
                            <?php } ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">اسم الدرس</label>
                        <input type="text" name="lesson_name" class="form-control" required value="<?php echo $edit_lesson['lesson_name'] ?? ''; ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">الوصف</label>
                        <textarea name="lesson_desc" class="form-control" rows="2"><?php echo $edit_lesson['lesson_desc'] ?? ''; ?></textarea>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">الترتيب</label>
                            <input type="number" name="lesson_order" class="form-control" required value="<?php echo $edit_lesson['lesson_order'] ?? '1'; ?>">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">المدة</label>
                            <input type="text" name="duration" class="form-control" placeholder="15:00" value="<?php echo $edit_lesson['duration'] ?? ''; ?>">
                        </div>
                    </div>

                    <hr class="my-3">
                    <h6 class="fw-bold mb-3 text-primary"><i class="fas fa-video me-2"></i> الفيديو</h6>

                    <div class="mb-3">
                        <label class="form-label fw-bold">نوع الفيديو</label>
                        <select name="video_type" class="form-select" id="videoTypeSelect" onchange="toggleVideoInput()">
                            <option value="youtube" <?php echo (!$edit_lesson || ($edit_lesson['video_type'] ?? 'youtube') == 'youtube') ? 'selected' : ''; ?>>YouTube</option>
                            <option value="vimeo" <?php echo ($edit_lesson && ($edit_lesson['video_type'] ?? '') == 'vimeo') ? 'selected' : ''; ?>>Vimeo</option>
                            <option value="local" <?php echo ($edit_lesson && ($edit_lesson['video_type'] ?? '') == 'local') ? 'selected' : ''; ?>>ملف محلي</option>
                        </select>
                    </div>

                    <div class="mb-3" id="urlInputDiv">
                        <label class="form-label fw-bold">رابط أو معرف الفيديو</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light" id="urlIcon"><i class="fab fa-youtube text-danger"></i></span>
                            <input type="text" name="video_url" class="form-control" id="videoUrlInput" placeholder="https://www.youtube.com/watch?v=... أو dQw4w9WgXcQ" value="<?php echo $edit_lesson['video_url'] ?? ''; ?>">
                        </div>
                        <small class="text-muted" id="urlHint">YouTube: يمكنك لصق الرابط كاملاً أو المعرف فقط (11 حرف)</small>
                    </div>
                    <div class="mb-3" id="fileInputDiv" style="display:none;">
                        <label class="form-label fw-bold">ملف الفيديو</label>
                        <input type="file" name="video_file" class="form-control" accept="video/*">
                        <small class="text-muted">الصيغ المدعومة: MP4, WebM, MOV, AVI</small>
                        <?php if($edit_lesson && !empty($edit_lesson['video_url']) && ($edit_lesson['video_type'] ?? '') == 'local') { ?>
                        <small class="text-muted d-block">الملف الحالي: <a href="<?php echo $edit_lesson['video_url']; ?>" target="_blank">عرض</a></small>
                        <?php } ?>
                    </div>


                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="is_free" id="isFree" <?php echo ($edit_lesson && $edit_lesson['is_free'] == 'yes') ? 'checked' : ''; ?>>
                        <label class="form-check-label" for="isFree">درس مجاني (معاينة)</label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary rounded-pill" data-bs-dismiss="modal">إلغاء</button>
                    <button type="submit" name="save_lesson" class="btn btn-primary rounded-pill"><i class="fas fa-save me-2"></i> حفظ</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function resetForm() {
    document.querySelector('#lessonModal form').reset();
    document.querySelector('input[name="lesson_id"]').value = '';
    document.querySelector('#lessonModal .modal-title').textContent = 'درس جديد';
    toggleVideoInput();
}

function toggleVideoInput() {
    var type = document.getElementById('videoTypeSelect').value;
    var urlDiv = document.getElementById('urlInputDiv');
    var fileDiv = document.getElementById('fileInputDiv');
    var icon = document.getElementById('urlIcon');
    var hint = document.getElementById('urlHint');

    if(type === 'local') {
        urlDiv.style.display = 'none';
        fileDiv.style.display = 'block';
    } else {
        urlDiv.style.display = 'block';
        fileDiv.style.display = 'none';

        if(type === 'youtube') {
            icon.innerHTML = '<i class="fab fa-youtube text-danger"></i>';
            hint.textContent = 'YouTube: يمكنك لصق الرابط كاملاً أو المعرف فقط (11 حرف)';
            document.getElementById('videoUrlInput').placeholder = 'https://www.youtube.com/watch?v=... أو dQw4w9WgXcQ';
        } else if(type === 'vimeo') {
            icon.innerHTML = '<i class="fab fa-vimeo-v text-info"></i>';
            hint.textContent = 'Vimeo: أدخل رابط الفيديو أو المعرف الرقمي';
            document.getElementById('videoUrlInput').placeholder = 'https://vimeo.com/123456789 أو 123456789';
        }
    }
}

<?php if($edit_lesson) { ?>
document.addEventListener('DOMContentLoaded', function() {
    toggleVideoInput();
    var modal = new bootstrap.Modal(document.getElementById('lessonModal'));
    modal.show();
});
<?php } else { ?>
document.addEventListener('DOMContentLoaded', function() {
    toggleVideoInput();
});
<?php } ?>
</script>

<?php include('../includes/footer.php'); ?>