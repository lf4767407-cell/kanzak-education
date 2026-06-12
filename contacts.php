<?php
include('../dbConnection.php');

if(!isAdmin()) {
    redirect('../index.php');
}

// Mark as read
if(isset($_GET['read'])) {
    $contact_id = intval($_GET['read']);
    // FIX: Remove is_read since column doesn't exist
    // Just redirect back
    redirect('contacts.php');
}

// Delete
if(isset($_GET['delete'])) {
    $contact_id = intval($_GET['delete']);
    mysqli_query($conn, "DELETE FROM contacts WHERE contact_id = '$contact_id'");
    redirect('contacts.php');
}

// Get contacts
$contacts = mysqli_query($conn, "SELECT * FROM contacts ORDER BY created_at DESC");

include('../includes/header.php');
?>

<style>
    .admin-sidebar { background: #1e293b; min-height: 100vh; color: white; padding: 20px 0; position: fixed; right: 0; top: 0; width: 260px; z-index: 1000; transition: transform 0.3s ease; }
    .admin-main { margin-right: 260px; padding: 30px; min-height: 100vh; background: #f8fafc; transition: margin-right 0.3s ease; }
    .admin-link { display: flex; align-items: center; gap: 10px; padding: 12px 20px; color: rgba(255,255,255,0.7); text-decoration: none; transition: all 0.3s; border-right: 3px solid transparent; }
    .admin-link:hover, .admin-link.active { background: rgba(255,255,255,0.1); color: white; border-right-color: #6366f1; }
    @media (max-width: 991px) { .admin-sidebar { transform: translateX(100%); } .admin-sidebar.active { transform: translateX(0); } .admin-main { margin-right: 0; } }
    .contact-card { transition: all 0.3s ease; }
    .contact-card:hover { transform: translateY(-3px); box-shadow: 0 10px 30px rgba(0,0,0,0.1) !important; }
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
        <a href="lessons.php" class="admin-link"><i class="fas fa-video"></i> الدروس</a>
        <a href="orders.php" class="admin-link"><i class="fas fa-shopping-cart"></i> الطلبات</a>
        <a href="contacts.php" class="admin-link active"><i class="fas fa-envelope"></i> الرسائل</a>
        <a href="../logout.php" class="admin-link text-danger"><i class="fas fa-sign-out-alt"></i> تسجيل الخروج</a>
    </nav>
</div>

<div class="admin-main">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold mb-0"><i class="fas fa-envelope text-primary me-2"></i> إدارة الرسائل</h4>
            <small class="text-muted">رسائل التواصل من الزوار</small>
        </div>
    </div>

    <div class="row g-4">
        <?php if(mysqli_num_rows($contacts) > 0) { while($contact = mysqli_fetch_assoc($contacts)) { ?>
        <div class="col-md-6 col-lg-4">
            <div class="card contact-card border-0 shadow-sm rounded-4 h-100">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div class="d-flex align-items-center">
                            <div class="bg-primary bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 45px; height: 45px;">
                                <i class="fas fa-user text-primary"></i>
                            </div>
                            <div>
                                <h6 class="fw-bold mb-0"><?php echo htmlspecialchars($contact['name']); ?></h6>
                                <small class="text-muted"><?php echo htmlspecialchars($contact['email']); ?></small>
                            </div>
                        </div>
                    </div>

                    <?php if($contact['subject']) { ?><h6 class="fw-bold mb-2"><?php echo htmlspecialchars($contact['subject']); ?></h6><?php } ?>
                    <p class="text-muted mb-3"><?php echo nl2br(htmlspecialchars($contact['message'])); ?></p>

                    <div class="d-flex justify-content-between align-items-center">
                        <small class="text-muted"><i class="fas fa-clock me-1"></i> <?php echo date('Y-m-d H:i', strtotime($contact['created_at'])); ?></small>
                        <div>
                            <a href="?delete=<?php echo $contact['contact_id']; ?>" class="btn btn-sm btn-danger rounded-pill" onclick="return confirm('هل أنت متأكد؟')">
                                <i class="fas fa-trash"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php } } else { ?>
        <div class="col-12">
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-body text-center py-5">
                    <i class="fas fa-inbox fa-4x text-muted mb-3"></i>
                    <h4 class="fw-bold">لا توجد رسائل</h4>
                    <p class="text-muted">سيتم عرض رسائل التواصل هنا</p>
                </div>
            </div>
        </div>
        <?php } ?>
    </div>
</div>

<script>
function toggleSidebar() {
    var sidebar = document.getElementById('adminSidebar');
    sidebar.classList.toggle('active');
}
</script>

<?php include('../includes/footer.php'); ?>