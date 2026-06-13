<?php
include('./dbConnection.php');

// ─── Handle Form Submission ───
$success_msg = '';
$error_msg = '';

if(isset($_POST['submit']) && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = sanitize($_POST['name']);
    $email = sanitize($_POST['email']);
    $subject = sanitize($_POST['subject']);
    $message = sanitize($_POST['message']);

    if(empty($name) || empty($email) || empty($message)) {
        $error_msg = 'الرجاء ملء جميع الحقول المطلوبة.';
    } elseif(!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error_msg = 'الرجاء إدخال بريد إلكتروني صحيح.';
    } else {
        // FIX: Removed 'is_read' column - use simple INSERT without it
        $sql = "INSERT INTO contacts (name, email, subject, message, created_at) 
                VALUES (?, ?, ?, ?, NOW())";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssss", $name, $email, $subject, $message);

        if($stmt->execute()) {
            $success_msg = 'تم إرسال رسالتك بنجاح! سنتواصل معك قريباً.';
        } else {
            $error_msg = 'حدث خطأ أثناء إرسال الرسالة. يرجى المحاولة مرة أخرى.';
        }
        $stmt->close();
    }
}

include('./includes/header.php');
?>

<!-- Contact Section -->
<section class="py-5">
    <div class="container">
        <div class="row g-5 align-items-center">
            <!-- Contact Info - Libya/Misrata -->
            <div class="col-lg-6">
                <span class="badge bg-primary bg-opacity-10 text-primary px-3 py-2 mb-3">
                    <i class="fas fa-headset me-1"></i> تواصل معنا
                </span>
                <h2 class="fw-bold display-6 mb-4">هل لديك سؤال أو استفسار؟</h2>
                <p class="text-muted mb-4">
                    فريق <strong>كنزك التعليمي</strong> جاهز لمساعدتك في أي وقت. 
                    لا تتردد في التواصل معنا من أي مكان في <strong>ليبيا 🇱🇾</strong>.
                </p>

                <!-- Address - Misrata, Libya -->
                <div class="d-flex align-items-center mb-4 p-3 rounded-4 bg-light border-end border-4 border-primary">
                    <div class="bg-primary bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center me-3 flex-shrink-0" 
                         style="width: 55px; height: 55px;">
                        <i class="fas fa-map-marker-alt text-primary fs-4"></i>
                    </div>
                    <div>
                        <h6 class="fw-bold mb-1">العنوان</h6>
                        <p class="text-muted mb-1">مصراتة - ليبيا 🇱🇾</p>
                        <div class="mt-1">
                            <span class="badge bg-success bg-opacity-10 text-success">ليبيا</span>
                            <span class="badge bg-info bg-opacity-10 text-info">مصراتة</span>
                            <span class="badge bg-warning bg-opacity-10 text-warning">شمال أفريقيا</span>
                        </div>
                    </div>
                </div>

                <!-- Phone Numbers -->
                <div class="d-flex align-items-center mb-4 p-3 rounded-4 bg-light">
                    <div class="bg-success bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center me-3 flex-shrink-0" 
                         style="width: 55px; height: 55px;">
                        <i class="fas fa-phone text-success fs-4"></i>
                    </div>
                    <div>
                        <h6 class="fw-bold mb-1">الهاتف</h6>
                        <p class="text-muted mb-1" dir="ltr">+218 91 000 0000</p>
                        <p class="text-muted mb-0" dir="ltr">+218 51 000 000</p>
                        <small class="text-muted">متاح من السبت إلى الخميس، 9 ص - 5 م</small>
                    </div>
                </div>

                <!-- Email -->
                <div class="d-flex align-items-center mb-4 p-3 rounded-4 bg-light">
                    <div class="bg-warning bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center me-3 flex-shrink-0" 
                         style="width: 55px; height: 55px;">
                        <i class="fas fa-envelope text-warning fs-4"></i>
                    </div>
                    <div>
                        <h6 class="fw-bold mb-1">البريد الإلكتروني</h6>
                        <p class="text-muted mb-0">info@kanzak.ly</p>
                        <small class="text-muted">نرد على جميع الرسائل خلال 24 ساعة</small>
                    </div>
                </div>

                <!-- WhatsApp -->
                <div class="d-flex align-items-center mb-4 p-3 rounded-4 bg-light">
                    <div class="bg-success bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center me-3 flex-shrink-0" 
                         style="width: 55px; height: 55px;">
                        <i class="fab fa-whatsapp text-success fs-4"></i>
                    </div>
                    <div>
                        <h6 class="fw-bold mb-1">واتساب</h6>
                        <p class="text-muted mb-0" dir="ltr">+218 91 000 0000</p>
                        <small class="text-muted">تواصل معنا مباشرة على واتساب</small>
                    </div>
                </div>
            </div>

            <!-- Contact Form -->
            <div class="col-lg-6">
                <div class="card border-0 shadow-lg rounded-4">
                    <div class="card-header bg-white border-0 p-4 pb-0">
                        <h4 class="fw-bold mb-0">
                            <i class="fas fa-paper-plane text-primary me-2"></i> أرسل رسالتك
                        </h4>
                        <p class="text-muted small mt-2">املأ النموذج أدناه وسنرد عليك في أقرب وقت</p>
                    </div>
                    <div class="card-body p-4">
                        <!-- Success/Error Messages -->
                        <?php if(!empty($success_msg)): ?>
                            <div class="alert alert-success rounded-4 border-0 shadow-sm mb-3">
                                <i class="fas fa-check-circle me-2"></i> <?php echo $success_msg; ?>
                            </div>
                        <?php endif; ?>

                        <?php if(!empty($error_msg)): ?>
                            <div class="alert alert-danger rounded-4 border-0 shadow-sm mb-3">
                                <i class="fas fa-times-circle me-2"></i> <?php echo $error_msg; ?>
                            </div>
                        <?php endif; ?>

                        <form action="contact.php" method="POST" class="needs-validation" novalidate>
                            <div class="mb-3">
                                <label class="form-label fw-bold">الاسم الكامل <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-end-0">
                                        <i class="fas fa-user text-muted"></i>
                                    </span>
                                    <input type="text" name="name" class="form-control border-start-0 bg-light py-3" 
                                           required placeholder="الاسم بالكامل"
                                           value="<?php echo isset($_POST['name']) ? htmlspecialchars($_POST['name']) : ''; ?>">
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-bold">البريد الإلكتروني <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-end-0">
                                        <i class="fas fa-envelope text-muted"></i>
                                    </span>
                                    <input type="email" name="email" class="form-control border-start-0 bg-light py-3" 
                                           required placeholder="example@email.com"
                                           value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-bold">الموضوع</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-end-0">
                                        <i class="fas fa-tag text-muted"></i>
                                    </span>
                                    <input type="text" name="subject" class="form-control border-start-0 bg-light py-3" 
                                           placeholder="موضوع الرسالة"
                                           value="<?php echo isset($_POST['subject']) ? htmlspecialchars($_POST['subject']) : ''; ?>">
                                </div>
                            </div>
                            <div class="mb-4">
                                <label class="form-label fw-bold">الرسالة <span class="text-danger">*</span></label>
                                <textarea name="message" class="form-control bg-light" rows="5" required 
                                          placeholder="اكتب رسالتك هنا..."><?php echo isset($_POST['message']) ? htmlspecialchars($_POST['message']) : ''; ?></textarea>
                            </div>
                            <button type="submit" name="submit" class="btn btn-primary w-100 py-3 rounded-pill fw-bold">
                                <i class="fas fa-paper-plane me-2"></i> إرسال الرسالة
                            </button>
                        </form>

                        <div class="text-center mt-4">
                            <p class="text-muted small">
                                <i class="fas fa-shield-alt text-success me-1"></i>
                                معلوماتك محمية وآمنة. نحترم خصوصيتك.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<?php include('./includes/footer.php'); ?>