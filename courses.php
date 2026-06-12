<?php
include('../dbConnection.php');

if(!isAdmin()) {
    redirect('../index.php');
}

// Helper function to extract YouTube ID
function getYouTubeId($url) {
    $pattern = '/(?:youtube\.com\/(?:[^\/]+\/.+\/|(?:v|e(?:mbed)?)\/|.*[?&]v=)|youtu\.be\/)([^"&?\/\s]{11})/';
    if(preg_match($pattern, $url, $matches)) {
        return $matches[1];
    }
    return $url;
}

function getVimeoId($url) {
    if(preg_match('/vimeo\.com\/(\d+)/', $url, $matches)) {
        return $matches[1];
    }
    return $url;
}

// Handle delete course
if(isset($_GET['delete'])) {
    $course_id = intval($_GET['delete']);

    // Delete related videos first
    mysqli_query($conn, "DELETE FROM course_videos WHERE course_id = '$course_id'");
    // Delete related lessons
    mysqli_query($conn, "DELETE FROM lessons WHERE course_id = '$course_id'");
    // Delete related orders
    mysqli_query($conn, "DELETE FROM orders WHERE course_id = '$course_id'");
    // Delete course
    mysqli_query($conn, "DELETE FROM courses WHERE course_id = '$course_id'");
    redirect('courses.php');
}

// Handle add/edit course
if(isset($_POST['save_course'])) {
    $course_id = isset($_POST['course_id']) ? intval($_POST['course_id']) : 0;
    $name = mysqli_real_escape_string($conn, $_POST['course_name']);
    $desc = mysqli_real_escape_string($conn, $_POST['course_desc']);
    $detail = mysqli_real_escape_string($conn, $_POST['course_detail']);
    $price = floatval($_POST['course_price']);
    $original_price = floatval($_POST['course_original_price']);
    $duration = mysqli_real_escape_string($conn, $_POST['course_duration']);
    $level = mysqli_real_escape_string($conn, $_POST['course_level']);
    $cat_id = intval($_POST['cat_id']);
    $instructor = mysqli_real_escape_string($conn, $_POST['instructor_name']);
    $status = mysqli_real_escape_string($conn, $_POST['status']);

    // Handle image upload
    $img = '';
    if(isset($_FILES['course_img']) && $_FILES['course_img']['tmp_name']) {
        $upload_result = uploadImageFile($_FILES['course_img'], '../uploads/courses/');
        if(isset($upload_result['success'])) {
            $img = $upload_result['path'];
        }
    }

    if($course_id > 0) {
        // Update existing course
        if($img) {
            $sql = "UPDATE courses SET course_name='$name', course_desc='$desc', course_detail='$detail', 
                    course_img='$img', course_price='$price', course_original_price='$original_price', 
                    course_duration='$duration', course_level='$level', cat_id='$cat_id', 
                    instructor_name='$instructor', status='$status' WHERE course_id='$course_id'";
        } else {
            $sql = "UPDATE courses SET course_name='$name', course_desc='$desc', course_detail='$detail', 
                    course_price='$price', course_original_price='$original_price', 
                    course_duration='$duration', course_level='$level', cat_id='$cat_id', 
                    instructor_name='$instructor', status='$status' WHERE course_id='$course_id'";
        }
    } else {
        // Insert new course
        $img = $img ?: 'https://images.unsplash.com/photo-1516321318423-f06f85e504b3?w=600&h=400&fit=crop';
        $sql = "INSERT INTO courses (course_name, course_desc, course_detail, course_img, course_price, 
                course_original_price, course_duration, course_level, cat_id, instructor_name, status) 
                VALUES ('$name', '$desc', '$detail', '$img', '$price', '$original_price', '$duration', 
                '$level', '$cat_id', '$instructor', '$status')";
    }

    mysqli_query($conn, $sql);

    if($course_id == 0) {
        $course_id = mysqli_insert_id($conn);
    }

    // Handle video addition (YouTube/Vimeo URL)
    if(isset($_POST['video_url']) && !empty($_POST['video_url'])) {
        $video_url = mysqli_real_escape_string($conn, $_POST['video_url']);
        $video_type = mysqli_real_escape_string($conn, $_POST['video_type']);
        $video_title = mysqli_real_escape_string($conn, $_POST['video_title'] ?? 'فيديو تقديمي');

        if($video_type == 'youtube') {
            $video_url = getYouTubeId($video_url);
        } elseif($video_type == 'vimeo') {
            $video_url = getVimeoId($video_url);
        }

        mysqli_query($conn, "INSERT INTO course_videos (course_id, video_url, video_type, title, is_preview) 
                VALUES ('$course_id', '$video_url', '$video_type', '$video_title', 1)");
    }

    // Handle local video upload
    if(isset($_FILES['video_file']) && $_FILES['video_file']['tmp_name']) {
        $upload_result = uploadVideoFile($_FILES['video_file'], '../uploads/videos/');
        if(isset($upload_result['success'])) {
            $video_path = $upload_result['path'];
            $video_title = mysqli_real_escape_string($conn, $_POST['video_title'] ?? 'فيديو تقديمي');

            mysqli_query($conn, "INSERT INTO course_videos (course_id, video_url, video_type, title, is_preview) 
                    VALUES ('$course_id', '$video_path', 'local', '$video_title', 1)");
        }
    }

    redirect('courses.php');
}

// Handle delete video
if(isset($_GET['delete_video'])) {
    $video_id = intval($_GET['delete_video']);
    $course_id = intval($_GET['course_id']);

    // Get video path to delete file if local
    $video_result = mysqli_query($conn, "SELECT video_url, video_type FROM course_videos WHERE video_id = '$video_id'");
    $video_data = mysqli_fetch_assoc($video_result);

    if($video_data && $video_data['video_type'] == 'local' && file_exists($video_data['video_url'])) {
        unlink($video_data['video_url']);
    }

    mysqli_query($conn, "DELETE FROM course_videos WHERE video_id = '$video_id'");
    redirect('courses.php?edit=' . $course_id);
}

// Get courses with their videos
$courses = mysqli_query($conn, "SELECT c.*, cat.cat_name FROM courses c LEFT JOIN categories cat ON c.cat_id = cat.cat_id ORDER BY c.course_id DESC");

// Get categories for dropdown
$categories = mysqli_query($conn, "SELECT * FROM categories");

// Get course for edit
$edit_course = null;
$edit_videos = null;
if(isset($_GET['edit'])) {
    $edit_id = intval($_GET['edit']);
    $edit_result = mysqli_query($conn, "SELECT * FROM courses WHERE course_id = '$edit_id'");
    $edit_course = mysqli_fetch_assoc($edit_result);

    $edit_videos = mysqli_query($conn, "SELECT * FROM course_videos WHERE course_id = '$edit_id' ORDER BY video_id DESC");
}

include('../includes/header.php');
?>

<style>
    .admin-sidebar { background: #1e293b; min-height: 100vh; color: white; padding: 20px 0; position: fixed; right: 0; top: 0; width: 260px; z-index: 1000; transition: transform 0.3s ease; }
    .admin-main { margin-right: 260px; padding: 30px; min-height: 100vh; background: #f8fafc; transition: margin-right 0.3s ease; }
    .admin-link { display: flex; align-items: center; gap: 10px; padding: 12px 20px; color: rgba(255,255,255,0.7); text-decoration: none; transition: all 0.3s; border-right: 3px solid transparent; }
    .admin-link:hover, .admin-link.active { background: rgba(255,255,255,0.1); color: white; border-right-color: #6366f1; }
    @media (max-width: 991px) { .admin-sidebar { transform: translateX(100%); } .admin-sidebar.active { transform: translateX(0); } .admin-main { margin-right: 0; } }
    .video-preview { position: relative; padding-bottom: 56.25%; height: 0; overflow: hidden; border-radius: 8px; background: #000; }
    .video-preview iframe, .video-preview video { position: absolute; top: 0; left: 0; width: 100%; height: 100%; border: none; }
    .video-card { transition: all 0.3s ease; }
    .video-card:hover { transform: translateY(-3px); box-shadow: 0 10px 25px rgba(0,0,0,0.1) !important; }
    .fade-in { animation: fadeIn 0.5s ease-in; }
    @keyframes fadeIn { from { opacity: 0; transform: translateY(20px); } to { opacity: 1; transform: translateY(0); } }
</style>

<div class="admin-sidebar" id="adminSidebar">
    <div class="text-center p-4 border-bottom border-secondary">
        <i class="fas fa-shield-alt fa-2x text-primary mb-2"></i>
        <h5 class="fw-bold mb-0">لوحة التحكم</h5>
        <small class="text-white-50">كنزك التعليمي</small>
    </div>
    <nav class="mt-3">
        <a href="index.php" class="admin-link"><i class="fas fa-tachometer-alt"></i> الرئيسية</a>
        <a href="students.php" class="admin-link"><i class="fas fa-users"></i> الطلاب</a>
        <a href="courses.php" class="admin-link active"><i class="fas fa-book"></i> الدورات</a>
        <a href="lessons.php" class="admin-link"><i class="fas fa-video"></i> الدروس</a>
        <a href="orders.php" class="admin-link"><i class="fas fa-shopping-cart"></i> الطلبات</a>
        <a href="contacts.php" class="admin-link"><i class="fas fa-envelope"></i> الرسائل</a>
        <a href="../index.php" class="admin-link text-warning"><i class="fas fa-globe"></i> الموقع</a>
        <a href="../logout.php" class="admin-link text-danger"><i class="fas fa-sign-out-alt"></i> تسجيل الخروج</a>
    </nav>
</div>

<div class="admin-main">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold mb-0">إدارة الدورات</h4>
            <small class="text-muted">إضافة، تعديل وحذف الدورات مع الفيديوهات</small>
        </div>
        <button class="btn btn-primary rounded-pill" data-bs-toggle="modal" data-bs-target="#courseModal" onclick="resetForm()">
            <i class="fas fa-plus me-2"></i> دورة جديدة
        </button>
    </div>

    <!-- Courses Table -->
    <div class="card border-0 shadow-sm rounded-4 mb-4 fade-in">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th>#</th>
                            <th>الصورة</th>
                            <th>الاسم</th>
                            <th>التصنيف</th>
                            <th>السعر</th>
                            <th>الفيديوهات</th>
                            <th>الحالة</th>
                            <th>الإجراءات</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(mysqli_num_rows($courses) > 0) { $num = 0; while($course = mysqli_fetch_assoc($courses)) { $num++;
                            $video_count_result = mysqli_query($conn, "SELECT COUNT(*) as count FROM course_videos WHERE course_id = '{$course['course_id']}'");
                            $video_count = mysqli_fetch_assoc($video_count_result)['count'];
                        ?>
                        <tr>
                            <td><?php echo $num; ?></td>
                            <td><img src="<?php echo $course['course_img']; ?>" width="50" height="35" style="object-fit: cover; border-radius: 6px;" alt="" onerror="this.src='https://via.placeholder.com/50x35/6366f1/ffffff?text=Course'"></td>
                            <td class="fw-bold"><?php echo $course['course_name']; ?></td>
                            <td><span class="badge bg-light text-dark border"><?php echo $course['cat_name'] ?? 'عام'; ?></span></td>
                            <td class="fw-bold text-primary">₹<?php echo number_format($course['course_price'], 2); ?></td>
                            <td><span class="badge bg-info"><i class="fas fa-video me-1"></i> <?php echo $video_count; ?></span></td>
                            <td>
                                <?php if($course['status'] == 'active') { ?><span class="badge bg-success rounded-pill">نشط</span>
                                <?php } elseif($course['status'] == 'draft') { ?><span class="badge bg-warning rounded-pill">مسودة</span>
                                <?php } else { ?><span class="badge bg-secondary rounded-pill">غير نشط</span><?php } ?>
                            </td>
                            <td>
                                <a href="?edit=<?php echo $course['course_id']; ?>" class="btn btn-sm btn-primary rounded-pill me-1" data-bs-toggle="modal" data-bs-target="#courseModal"><i class="fas fa-edit"></i></a>
                                <a href="?delete=<?php echo $course['course_id']; ?>" class="btn btn-sm btn-danger rounded-pill" onclick="return confirm('هل أنت متأكد؟ سيتم حذف جميع الفيديوهات أيضاً!')"><i class="fas fa-trash"></i></a>
                            </td>
                        </tr>
                        <?php } } else { ?><tr><td colspan="8" class="text-center py-4 text-muted">لا توجد دورات</td></tr><?php } ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <?php if($edit_course && $edit_videos && mysqli_num_rows($edit_videos) > 0) { ?>
    <!-- Current Videos for Edit -->
    <div class="card border-0 shadow-sm rounded-4 mb-4 fade-in">
        <div class="card-header bg-white border-0 p-4">
            <h5 class="fw-bold mb-0"><i class="fas fa-video text-primary me-2"></i> الفيديوهات الحالية للدورة: <?php echo $edit_course['course_name']; ?></h5>
        </div>
        <div class="card-body p-4">
            <div class="row g-3">
                <?php while($video = mysqli_fetch_assoc($edit_videos)) { ?>
                <div class="col-md-6 col-lg-4">
                    <div class="card video-card border-0 shadow-sm rounded-4">
                        <div class="video-preview">
                            <?php if($video['video_type'] == 'youtube') { ?>
                                <iframe src="https://www.youtube.com/embed/<?php echo $video['video_url']; ?>" allowfullscreen></iframe>
                            <?php } elseif($video['video_type'] == 'vimeo') { ?>
                                <iframe src="https://player.vimeo.com/video/<?php echo $video['video_url']; ?>" allowfullscreen></iframe>
                            <?php } else { ?>
                                <video controls><source src="<?php echo $video['video_url']; ?>" type="video/mp4"></video>
                            <?php } ?>
                        </div>
                        <div class="card-body p-3">
                            <h6 class="fw-bold mb-1"><?php echo $video['title']; ?></h6>
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="badge bg-<?php echo $video['video_type'] == 'youtube' ? 'danger' : ($video['video_type'] == 'vimeo' ? 'info' : 'success'); ?> rounded-pill">
                                    <i class="fab fa-<?php echo $video['video_type']; ?> me-1"></i> <?php echo $video['video_type']; ?>
                                </span>
                                <a href="?delete_video=<?php echo $video['video_id']; ?>&course_id=<?php echo $edit_course['course_id']; ?>" 
                                   class="btn btn-sm btn-danger rounded-pill" onclick="return confirm('حذف الفيديو؟')">
                                    <i class="fas fa-trash"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                <?php } ?>
            </div>
        </div>
    </div>
    <?php } ?>
</div>

<!-- Course Modal -->
<div class="modal fade" id="courseModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-bold"><i class="fas fa-book me-2 text-primary"></i><?php echo $edit_course ? 'تعديل دورة' : 'دورة جديدة'; ?></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="" enctype="multipart/form-data">
                <div class="modal-body">
                    <input type="hidden" name="course_id" value="<?php echo $edit_course['course_id'] ?? ''; ?>">

                    <!-- Course Info -->
                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">اسم الدورة <span class="text-danger">*</span></label>
                            <input type="text" name="course_name" class="form-control" required value="<?php echo $edit_course['course_name'] ?? ''; ?>" placeholder="اسم الدورة">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">التصنيف <span class="text-danger">*</span></label>
                            <select name="cat_id" class="form-select" required>
                                <option value="">اختر التصنيف</option>
                                <?php mysqli_data_seek($categories, 0); while($cat = mysqli_fetch_assoc($categories)) { ?>
                                <option value="<?php echo $cat['cat_id']; ?>" <?php echo ($edit_course && $edit_course['cat_id'] == $cat['cat_id']) ? 'selected' : ''; ?>><?php echo $cat['cat_name']; ?></option>
                                <?php } ?>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-bold">وصف مختصر <span class="text-danger">*</span></label>
                            <textarea name="course_desc" class="form-control" rows="2" required placeholder="وصف مختصر للدورة"><?php echo $edit_course['course_desc'] ?? ''; ?></textarea>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-bold">وصف تفصيلي</label>
                            <textarea name="course_detail" class="form-control" rows="4" placeholder="وصف تفصيلي للدورة"><?php echo $edit_course['course_detail'] ?? ''; ?></textarea>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold">السعر <span class="text-danger">*</span></label>
                            <input type="number" name="course_price" class="form-control" required step="0.01" value="<?php echo $edit_course['course_price'] ?? ''; ?>" placeholder="99.99">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold">السعر الأصلي</label>
                            <input type="number" name="course_original_price" class="form-control" step="0.01" value="<?php echo $edit_course['course_original_price'] ?? ''; ?>" placeholder="199.99">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold">المدة</label>
                            <input type="text" name="course_duration" class="form-control" placeholder="مثال: 12 ساعة" value="<?php echo $edit_course['course_duration'] ?? ''; ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">المستوى</label>
                            <select name="course_level" class="form-select">
                                <option value="مبتدئ" <?php echo ($edit_course && $edit_course['course_level'] == 'مبتدئ') ? 'selected' : ''; ?>>مبتدئ</option>
                                <option value="متوسط" <?php echo ($edit_course && $edit_course['course_level'] == 'متوسط') ? 'selected' : ''; ?>>متوسط</option>
                                <option value="متقدم" <?php echo ($edit_course && $edit_course['course_level'] == 'متقدم') ? 'selected' : ''; ?>>متقدم</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">المحاضر</label>
                            <input type="text" name="instructor_name" class="form-control" value="<?php echo $edit_course['instructor_name'] ?? ''; ?>" placeholder="اسم المحاضر">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">الحالة</label>
                            <select name="status" class="form-select">
                                <option value="active" <?php echo ($edit_course && $edit_course['status'] == 'active') ? 'selected' : ''; ?>>نشط</option>
                                <option value="draft" <?php echo ($edit_course && $edit_course['status'] == 'draft') ? 'selected' : ''; ?>>مسودة</option>
                                <option value="inactive" <?php echo ($edit_course && $edit_course['status'] == 'inactive') ? 'selected' : ''; ?>>غير نشط</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">صورة الدورة</label>
                            <input type="file" name="course_img" class="form-control" accept="image/*">
                            <?php if($edit_course && $edit_course['course_img']) { ?>
                            <small class="text-muted">الصورة الحالية: <a href="<?php echo $edit_course['course_img']; ?>" target="_blank">عرض</a></small>
                            <?php } ?>
                        </div>
                    </div>

                    <hr class="my-4">

                    <!-- Video Section -->
                    <h6 class="fw-bold mb-3 text-primary"><i class="fas fa-video me-2"></i> إضافة فيديو تقديمي للدورة</h6>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">نوع الفيديو</label>
                            <select name="video_type" class="form-select" id="videoType" onchange="toggleVideoInput()">
                                <option value="youtube">YouTube (موصى به)</option>
                                <option value="vimeo">Vimeo</option>
                                <option value="local">رفع من الجهاز</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">عنوان الفيديو</label>
                            <input type="text" name="video_title" class="form-control" placeholder="مثال: مقدمة في الدورة">
                        </div>
                        <div class="col-12" id="urlInput">
                            <label class="form-label fw-bold">رابط الفيديو</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light" id="urlPrefix"><i class="fab fa-youtube text-danger"></i></span>
                                <input type="text" name="video_url" class="form-control" id="videoUrl" placeholder="https://www.youtube.com/watch?v=... أو معرف الفيديو">
                            </div>
                            <small class="text-muted">يمكنك لصق رابط YouTube كامل أو معرف الفيديو فقط (11 حرف)</small>

                            <!-- Live Preview -->
                            <div id="videoPreview" class="mt-3" style="display: none;">
                                <label class="form-label fw-bold small">معاينة:</label>
                                <div class="video-preview">
                                    <iframe id="previewFrame" src="" allowfullscreen></iframe>
                                </div>
                            </div>
                        </div>
                        <div class="col-12" id="fileInput" style="display:none;">
                            <label class="form-label fw-bold">ملف الفيديو</label>
                            <input type="file" name="video_file" class="form-control" accept="video/*">
                            <small class="text-muted">الصيغ المدعومة: MP4, WebM, MOV</small>
                        </div>
                    </div>

                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary rounded-pill" data-bs-dismiss="modal">إلغاء</button>
                    <button type="submit" name="save_course" class="btn btn-primary rounded-pill"><i class="fas fa-save me-2"></i> حفظ</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function resetForm() {
    document.querySelector('form').reset();
    document.querySelector('input[name="course_id"]').value = '';
    document.querySelector('.modal-title').innerHTML = '<i class="fas fa-book me-2 text-primary"></i>دورة جديدة';
    document.getElementById('videoPreview').style.display = 'none';
}

function toggleVideoInput() {
    var type = document.getElementById('videoType').value;
    var urlInput = document.getElementById('urlInput');
    var fileInput = document.getElementById('fileInput');
    var urlPrefix = document.getElementById('urlPrefix');
    var videoUrl = document.getElementById('videoUrl');

    if(type === 'local') {
        urlInput.style.display = 'none';
        fileInput.style.display = 'block';
    } else {
        urlInput.style.display = 'block';
        fileInput.style.display = 'none';

        if(type === 'youtube') {
            urlPrefix.innerHTML = '<i class="fab fa-youtube text-danger"></i>';
            videoUrl.placeholder = 'https://www.youtube.com/watch?v=... أو معرف الفيديو';
        } else if(type === 'vimeo') {
            urlPrefix.innerHTML = '<i class="fab fa-vimeo-v text-info"></i>';
            videoUrl.placeholder = 'https://vimeo.com/... أو معرف الفيديو';
        }
    }
}

// Live preview for YouTube
var videoUrlInput = document.getElementById('videoUrl');
if(videoUrlInput) {
    videoUrlInput.addEventListener('input', function() {
        var url = this.value.trim();
        var type = document.getElementById('videoType').value;
        var preview = document.getElementById('videoPreview');
        var frame = document.getElementById('previewFrame');

        if(type === 'youtube' && url.length >= 11) {
            var videoId = url;
            var match = url.match(/(?:youtube\.com\/(?:[^\/]+\/.+\/|(?:v|e(?:mbed)?)\/|.*[?&]v=)|youtu\.be\/)([^"&?\/\s]{11})/);
            if(match) videoId = match[1];

            if(videoId.length === 11) {
                frame.src = 'https://www.youtube.com/embed/' + videoId;
                preview.style.display = 'block';
            }
        } else {
            preview.style.display = 'none';
        }
    });
}

<?php if($edit_course) { ?>
document.addEventListener('DOMContentLoaded', function() {
    var modal = new bootstrap.Modal(document.getElementById('courseModal'));
    modal.show();
});
<?php } ?>
</script>

<?php include('../includes/footer.php'); ?>