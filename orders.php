<?php
include('../dbConnection.php');

if(!isAdmin()) {
    redirect('../index.php');
}

// Handle status update
if(isset($_GET['status']) && isset($_GET['id'])) {
    $order_id = intval($_GET['id']);
    $status = sanitize($_GET['status']);
    mysqli_query($conn, "UPDATE orders SET payment_status = '$status' WHERE order_id = '$order_id'");
    redirect('orders.php');
}

// Get orders
$orders = mysqli_query($conn, "SELECT o.*, u.name, u.email, c.course_name FROM orders o JOIN users u ON o.user_id = u.user_id JOIN courses c ON o.course_id = c.course_id ORDER BY o.created_at DESC");

include('../includes/header.php');
?>

<style>
    .admin-sidebar { background: #1e293b; min-height: 100vh; color: white; padding: 20px 0; position: fixed; right: 0; top: 0; width: 260px; z-index: 1000; transition: transform 0.3s ease; }
    .admin-main { margin-right: 260px; padding: 30px; min-height: 100vh; background: #f8fafc; transition: margin-right 0.3s ease; }
    .admin-link { display: flex; align-items: center; gap: 10px; padding: 12px 20px; color: rgba(255,255,255,0.7); text-decoration: none; transition: all 0.3s; border-right: 3px solid transparent; }
    .admin-link:hover, .admin-link.active { background: rgba(255,255,255,0.1); color: white; border-right-color: #6366f1; }
    @media (max-width: 991px) { .admin-sidebar { transform: translateX(100%); } .admin-sidebar.active { transform: translateX(0); } .admin-main { margin-right: 0; } }
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
        <a href="orders.php" class="admin-link active"><i class="fas fa-shopping-cart"></i> الطلبات</a>
        <a href="contacts.php" class="admin-link"><i class="fas fa-envelope"></i> الرسائل</a>
        <a href="../logout.php" class="admin-link text-danger"><i class="fas fa-sign-out-alt"></i> تسجيل الخروج</a>
    </nav>
</div>

<div class="admin-main">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold mb-0">إدارة الطلبات</h4>
            <small class="text-muted">متابعة وإدارة طلبات الشراء</small>
        </div>
    </div>

    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead class="bg-light">
                        <tr>
                            <th>رقم الطلب</th>
                            <th>الطالب</th>
                            <th>البريد</th>
                            <th>الدورة</th>
                            <th>المبلغ</th>
                            <th>الحالة</th>
                            <th>التاريخ</th>
                            <th>الإجراءات</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(mysqli_num_rows($orders) > 0) { while($order = mysqli_fetch_assoc($orders)) { ?>
                        <tr>
                            <td><code><?php echo $order['order_no']; ?></code></td>
                            <td class="fw-bold"><?php echo $order['name']; ?></td>
                            <td><?php echo $order['email']; ?></td>
                            <td><?php echo $order['course_name']; ?></td>
                            <td class="fw-bold text-primary">₹<?php echo $order['amount']; ?></td>
                            <td>
                                <?php if($order['payment_status'] == 'completed') { ?><span class="badge bg-success">مكتمل</span>
                                <?php } elseif($order['payment_status'] == 'pending') { ?><span class="badge bg-warning">معلق</span>
                                <?php } elseif($order['payment_status'] == 'refunded') { ?><span class="badge bg-info">مسترد</span>
                                <?php } else { ?><span class="badge bg-danger">فاشل</span><?php } ?>
                            </td>
                            <td><?php echo date('Y-m-d H:i', strtotime($order['created_at'])); ?></td>
                            <td>
                                <div class="dropdown">
                                    <button class="btn btn-sm btn-light rounded-pill dropdown-toggle" data-bs-toggle="dropdown"><i class="fas fa-cog"></i></button>
                                    <ul class="dropdown-menu">
                                        <li><a class="dropdown-item" href="?status=completed&id=<?php echo $order['order_id']; ?>"><i class="fas fa-check text-success me-2"></i> مكتمل</a></li>
                                        <li><a class="dropdown-item" href="?status=pending&id=<?php echo $order['order_id']; ?>"><i class="fas fa-clock text-warning me-2"></i> معلق</a></li>
                                        <li><a class="dropdown-item" href="?status=refunded&id=<?php echo $order['order_id']; ?>"><i class="fas fa-undo text-info me-2"></i> مسترد</a></li>
                                        <li><a class="dropdown-item" href="?status=failed&id=<?php echo $order['order_id']; ?>"><i class="fas fa-times text-danger me-2"></i> فاشل</a></li>
                                    </ul>
                                </div>
                            </td>
                        </tr>
                        <?php } } else { ?><tr><td colspan="8" class="text-center py-4 text-muted">لا توجد طلبات</td></tr><?php } ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
function toggleSidebar() {
    var sidebar = document.getElementById('adminSidebar');
    sidebar.classList.toggle('active');
}
</script>

<?php include('../includes/footer.php'); ?>
