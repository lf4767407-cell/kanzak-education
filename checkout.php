<?php 
include('./dbConnection.php');

if(!isset($_SESSION['stuLogEmail'])) {
    redirect('loginorsignup.php');
}

header("Pragma: no-cache");
header("Cache-Control: no-cache");
header("Expires: 0");

$stuEmail = $_SESSION['stuLogEmail'];
$course_id = isset($_POST['course_id']) ? intval($_POST['course_id']) : 0;
$amount = isset($_POST['id']) ? floatval($_POST['id']) : 0;

// Generate order number
$order_no = "KZ" . date('Ymd') . rand(10000, 99999);

// Save order to database
$order_sql = "INSERT INTO orders (order_no, user_id, course_id, amount, payment_status) VALUES (?, ?, ?, ?, 'pending')";
$order_stmt = $conn->prepare($order_sql);
$order_stmt->bind_param("siid", $order_no, $_SESSION['stu_id'], $course_id, $amount);
$order_stmt->execute();

include('./includes/header.php');
?>

<section class="py-5 bg-light min-vh-100">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8 col-lg-6">
                <div class="card border-0 shadow-lg rounded-4">
                    <div class="card-body p-5">
                        <div class="text-center mb-4">
                            <div class="bg-primary bg-opacity-10 rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 80px; height: 80px;">
                                <i class="fas fa-credit-card fa-2x text-primary"></i>
                            </div>
                            <h3 class="fw-bold">إتمام عملية الدفع</h3>
                            <p class="text-muted">كنزك التعليمي - بوابة الدفع الآمنة</p>
                        </div>
                        
                        <form method="post" action="./PaytmKit/pgRedirect.php" class="needs-validation" novalidate>
                            <div class="mb-3">
                                <label class="form-label fw-bold">رقم الطلب</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light"><i class="fas fa-hashtag text-muted"></i></span>
                                    <input type="text" class="form-control bg-light" name="ORDER_ID" 
                                           value="<?php echo $order_no; ?>" readonly>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label fw-bold">البريد الإلكتروني</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light"><i class="fas fa-envelope text-muted"></i></span>
                                    <input type="email" class="form-control bg-light" name="CUST_ID" 
                                           value="<?php echo $stuEmail; ?>" readonly>
                                </div>
                            </div>
                            
                            <div class="mb-4">
                                <label class="form-label fw-bold">المبلغ</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light"><i class="fas fa-money-bill text-muted"></i></span>
                                    <input type="text" class="form-control bg-light fw-bold text-primary" name="TXN_AMOUNT" 
                                           value="<?php echo $amount; ?>" readonly>
                                    <span class="input-group-text">₹</span>
                                </div>
                            </div>
                            
                            <input type="hidden" name="INDUSTRY_TYPE_ID" value="Retail">
                            <input type="hidden" name="CHANNEL_ID" value="WEB">
                            
                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-primary py-3 rounded-pill fw-bold">
                                    <i class="fas fa-lock me-2"></i> الدفع الآن ₹<?php echo $amount; ?>
                                </button>
                                <a href="courses.php" class="btn btn-outline-secondary py-3 rounded-pill">
                                    <i class="fas fa-times me-2"></i> إلغاء
                                </a>
                            </div>
                        </form>
                        
                        <div class="mt-4 text-center">
                            <small class="text-muted">
                                <i class="fas fa-shield-alt me-1 text-success"></i>
                                الدفع آمن ومشفر. معلوماتك محمية 100%.
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<?php include('./includes/footer.php'); ?>