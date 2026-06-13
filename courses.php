<?php
  include('./dbConnection.php');
  include('./includes/header.php');
?>

<!-- Page Banner -->
<section class="page-banner">
    <div class="container">
        <div class="row align-items-center py-5">
            <div class="col-lg-8">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="index.php" class="text-primary-dark">الرئيسية</a></li>
                        <li class="breadcrumb-item active text-muted">الدورات</li>
                    </ol>
                </nav>
                <h1 class="display-4 fw-bold text-primary-dark">جميع الدورات</h1>
                <p class="text-muted">اكتشف دوراتنا المتنوعة في مختلف المجالات التقنية - <strong class="text-success">كلها مجانية!</strong></p>
            </div>
        </div>
    </div>
</section>

<!-- Course Filter -->
<section class="py-4 bg-white shadow-sm">
    <div class="container">
        <div class="row g-3 align-items-center">
            <div class="col-md-4">
                <div class="input-group">
                    <span class="input-group-text bg-light border-0"><i class="fas fa-search text-muted"></i></span>
                    <input type="text" class="form-control border-0 bg-light" id="courseSearch" placeholder="ابحث عن دورة...">
                </div>
            </div>
            <div class="col-md-3">
                <select class="form-select border-0 bg-light" id="categoryFilter">
                    <option value="">جميع التصنيفات</option>
                    <?php
                    $cat_sql = "SELECT DISTINCT cat_id, cat_name FROM categories ORDER BY cat_name";
                    $cat_result = $conn->query($cat_sql);
                    if($cat_result) {
                        while($cat = $cat_result->fetch_assoc()) {
                            echo '<option value="' . $cat['cat_id'] . '">' . htmlspecialchars($cat['cat_name']) . '</option>';
                        }
                    }
                    ?>
                </select>
            </div>
            <div class="col-md-3">
                <select class="form-select border-0 bg-light" id="sortFilter">
                    <option value="popular">الأكثر شعبية</option>
                    <option value="newest">الأحدث</option>
                    <option value="name">الاسم (أ-ي)</option>
                </select>
            </div>
            <div class="col-md-2 text-end">
                <span class="text-muted">
                    <?php 
                    $count_sql = "SELECT COUNT(*) as total FROM courses WHERE status = 'active'";
                    $count_result = $conn->query($count_sql);
                    echo ($count_result ? $count_result->fetch_assoc()['total'] : 0) . ' دورة';
                    ?>
                </span>
            </div>
        </div>
    </div>
</section>

<!-- All Courses - Card Style Like Image -->
<section class="py-5 bg-light">
    <div class="container">
        <div class="row g-4" id="coursesGrid">
            <?php
            $sql = "SELECT c.*, COUNT(co.order_id) as enrolled_count, cat.cat_name, cat.cat_id as category_id, cat.cat_icon
                    FROM courses c 
                    LEFT JOIN orders co ON c.course_id = co.course_id AND co.payment_status = 'completed'
                    LEFT JOIN categories cat ON c.cat_id = cat.cat_id
                    WHERE c.status = 'active'";

            if(isset($_GET['cat']) && !empty($_GET['cat'])) {
                $sql .= " AND c.cat_id = " . intval($_GET['cat']);
            }

            $sql .= " GROUP BY c.course_id ORDER BY c.course_id DESC";

            $result = $conn->query($sql);
            if($result && $result->num_rows > 0){ 
                while($row = $result->fetch_assoc()){
                    $course_id = intval($row['course_id']);

                    // Get lesson count
                    $lesson_sql = "SELECT COUNT(*) as lesson_count FROM lessons WHERE course_id = ?";
                    $lesson_stmt = $conn->prepare($lesson_sql);
                    $lesson_stmt->bind_param("i", $course_id);
                    $lesson_stmt->execute();
                    $lesson_count = $lesson_stmt->get_result()->fetch_assoc()['lesson_count'] ?? 0;
                    $lesson_stmt->close();

                    // Check if favorite
                    $is_fav = false;
                    if(isset($_SESSION['stu_id'])) {
                        $fav_sql = "SELECT fav_id FROM favorites WHERE user_id = ? AND course_id = ?";
                        $fav_stmt = $conn->prepare($fav_sql);
                        $fav_stmt->bind_param("ii", $_SESSION['stu_id'], $course_id);
                        $fav_stmt->execute();
                        $is_fav = $fav_stmt->get_result()->num_rows > 0;
                    }
            ?>
            <!-- Course Card - Like Image Style -->
            <div class="col-md-6 col-lg-3 course-item" data-category="<?php echo $row['cat_id']; ?>">
                <div class="course-card-image h-100">
                    <!-- Image Section with Overlay -->
                    <div class="course-image-section position-relative">
                        <img src="<?php echo htmlspecialchars($row['course_img']); ?>" 
                             class="w-100" 
                             alt="<?php echo htmlspecialchars($row['course_name']); ?>"
                             style="height: 160px; object-fit: cover;">

                        <!-- Free Badge -->
                        <div class="position-absolute top-0 start-0 m-2">
                            <span class="badge bg-success">
                                <i class="fas fa-gift me-1"></i> مجاني
                            </span>
                        </div>

                        <!-- Favorite Button -->
                        <?php if(isset($_SESSION['stu_id'])) { ?>
                        <form method="POST" action="courses.php" class="position-absolute top-0 end-0 m-2">
                            <input type="hidden" name="fav_course_id" value="<?php echo $course_id; ?>">
                            <button type="submit" name="toggle_fav" class="btn btn-light btn-sm rounded-circle shadow-sm" style="width: 36px; height: 36px; padding: 0;">
                                <i class="fas fa-heart <?php echo $is_fav ? 'text-danger' : 'text-muted'; ?>"></i>
                            </button>
                        </form>
                        <?php } else { ?>
                        <div class="position-absolute top-0 end-0 m-2">
                            <a href="loginorsignup.php" class="btn btn-light btn-sm rounded-circle shadow-sm" style="width: 36px; height: 36px; padding: 0; display: flex; align-items: center; justify-content: center;">
                                <i class="fas fa-heart text-muted"></i>
                            </a>
                        </div>
                        <?php } ?>

                        <!-- Duration Badge -->
                        <div class="position-absolute bottom-0 start-0 m-2">
                            <span class="badge bg-dark bg-opacity-75">
                                <i class="fas fa-clock me-1"></i> <?php echo htmlspecialchars($row['course_duration'] ?? 'غير محدد'); ?>
                            </span>
                        </div>
                    </div>

                    <!-- Content Section -->
                    <div class="course-content-section bg-white p-3">
                        <!-- Category -->
                        <div class="mb-2">
                            <span class="badge bg-light text-dark border">
                                <i class="fas <?php echo $row['cat_icon'] ?? 'fa-book'; ?> me-1"></i>
                                <?php echo htmlspecialchars($row['cat_name'] ?? 'عام'); ?>
                            </span>
                        </div>

                        <!-- Course Name -->
                        <h6 class="fw-bold mb-2 course-title-line"><?php echo htmlspecialchars($row['course_name']); ?></h6>

                        <!-- Meta Info -->
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <div class="text-warning small">
                                <i class="fas fa-star"></i>
                                <span class="text-dark"><?php echo htmlspecialchars($row['rating'] ?? '0.0'); ?></span>
                            </div>
                            <small class="text-muted">
                                <i class="fas fa-user-friends me-1"></i> <?php echo intval($row['enrolled_count']); ?>
                            </small>
                        </div>

                        <!-- Lessons Count -->
                        <div class="mb-2">
                            <small class="text-muted">
                                <i class="fas fa-list-ol me-1"></i> <?php echo $lesson_count; ?> درس
                            </small>
                        </div>

                        <!-- Instructor & Price -->
                        <div class="d-flex justify-content-between align-items-center pt-2 border-top">
                            <div class="d-flex align-items-center">
                                <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($row['instructor_name'] ?? 'معلم'); ?>&background=random&size=64" 
                                     class="rounded-circle me-2" width="28" height="28" alt="">
                                <small class="text-muted"><?php echo htmlspecialchars($row['instructor_name'] ?? 'معلم'); ?></small>
                            </div>
                            <div class="d-flex align-items-center gap-2">
                                <?php if($row['course_original_price'] > 0) { ?>
                                <small class="text-decoration-line-through text-muted">₹<?php echo $row['course_original_price']; ?></small>
                                <?php } ?>
                                <span class="text-primary fw-bold">₹<?php echo $row['course_price']; ?></span>
                            </div>
                        </div>

                        <!-- Action Button -->
                        <div class="mt-3">
                            <a href="coursedetails.php?course_id=<?php echo $course_id; ?>" class="btn btn-primary w-100 rounded-pill btn-sm">
                                <i class="fas fa-info-circle me-1"></i> التفاصيل
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            <?php } } else { ?>
            <div class="col-12 text-center py-5">
                <i class="fas fa-search fa-3x text-muted mb-3"></i>
                <h4>لا توجد دورات متاحة حالياً</h4>
                <p class="text-muted">يرجى العودة لاحقاً أو التواصل مع الإدارة</p>
                <a href="contact.php" class="btn btn-primary rounded-pill mt-3">
                    <i class="fas fa-envelope me-2"></i> تواصل معنا
                </a>
            </div>
            <?php } ?>
        </div>
    </div>
</section>

<?php 
    // Handle favorite toggle
    if(isset($_POST['toggle_fav']) && isset($_POST['fav_course_id']) && isset($_SESSION['stu_id'])) {
        $fav_id = intval($_POST['fav_course_id']);
        $stu_id = $_SESSION['stu_id'];

        $check_fav = $conn->prepare("SELECT fav_id FROM favorites WHERE user_id = ? AND course_id = ?");
        $check_fav->bind_param("ii", $stu_id, $fav_id);
        $check_fav->execute();

        if($check_fav->get_result()->num_rows > 0) {
            $conn->query("DELETE FROM favorites WHERE user_id = $stu_id AND course_id = $fav_id");
        } else {
            $conn->query("INSERT INTO favorites (user_id, course_id, created_at) VALUES ($stu_id, $fav_id, NOW())");
        }

        header("Location: courses.php" . (isset($_GET['cat']) ? '?cat='.$_GET['cat'] : ''));
        exit();
    }

    include('./includes/footer.php');
?>

<style>
/* ═══════════════════════════════════════════════════════════════ */
/* Course Card Style - Like the Image */
/* ═══════════════════════════════════════════════════════════════ */

.course-card-image {
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 2px 8px rgba(0,0,0,0.08);
    transition: all 0.3s ease;
    background: white;
}

.course-card-image:hover {
    transform: translateY(-5px);
    box-shadow: 0 15px 35px rgba(0,0,0,0.12);
}

.course-image-section {
    position: relative;
    overflow: hidden;
}

.course-image-section img {
    transition: transform 0.3s ease;
}

.course-card-image:hover .course-image-section img {
    transform: scale(1.05);
}

.course-content-section {
    border-bottom-left-radius: 12px;
    border-bottom-right-radius: 12px;
}

.course-title-line {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
    line-height: 1.5;
    min-height: 3em;
}

/* Page Banner */
.page-banner {
    background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
}

/* Favorite button hover */
.btn-light:hover .fa-heart {
    color: #dc3545 !important;
}

/* Responsive */
@media (max-width: 768px) {
    .course-card-image {
        margin-bottom: 1rem;
    }
}
</style>

<script>
// ─── Pure JavaScript - No jQuery ───

// Search
document.getElementById('courseSearch').addEventListener('keyup', function() {
    var value = this.value.toLowerCase();
    var items = document.querySelectorAll('#coursesGrid .course-item');
    items.forEach(function(item) {
        var text = item.textContent.toLowerCase();
        item.style.display = text.indexOf(value) > -1 ? '' : 'none';
    });
});

// Category Filter
document.getElementById('categoryFilter').addEventListener('change', function() {
    var value = this.value;
    var items = document.querySelectorAll('#coursesGrid .course-item');
    items.forEach(function(item) {
        if(value === '') {
            item.style.display = '';
        } else {
            item.style.display = item.getAttribute('data-category') === value ? '' : 'none';
        }
    });
});

// Sort Filter
document.getElementById('sortFilter').addEventListener('change', function() {
    var sortType = this.value;
    var grid = document.getElementById('coursesGrid');
    var items = Array.from(grid.querySelectorAll('.course-item'));

    items.sort(function(a, b) {
        if(sortType === 'name') {
            var nameA = a.querySelector('.course-title-line').textContent.toLowerCase();
            var nameB = b.querySelector('.course-title-line').textContent.toLowerCase();
            return nameA.localeCompare(nameB);
        } else if(sortType === 'newest') {
            var idA = parseInt(a.getAttribute('data-category')) || 0;
            var idB = parseInt(b.getAttribute('data-category')) || 0;
            return idB - idA;
        } else if(sortType === 'popular') {
            var countA = parseInt(a.querySelector('.fa-user-friends').parentElement.textContent) || 0;
            var countB = parseInt(b.querySelector('.fa-user-friends').parentElement.textContent) || 0;
            return countB - countA;
        }
        return 0;
    });

    items.forEach(function(item) {
        grid.appendChild(item);
    });
});
</script>