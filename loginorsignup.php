
<?php 
include('./dbConnection.php');

// ─── Handle AJAX Login ───
if(isset($_POST['checkLogemail']) && $_POST['checkLogemail'] == "checkLogemail") {
    $email = sanitize($_POST['stuLogEmail']);
    $password = $_POST['stuLogPass'];

    $sql = "SELECT * FROM users WHERE email = ? AND role = 'student' AND status = 'active'";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if($result->num_rows == 1) {
        $row = $result->fetch_assoc();
        if(password_verify($password, $row['password'])) {
            $_SESSION['is_login'] = true;
            $_SESSION['stu_id'] = $row['user_id'];
            $_SESSION['stuLogEmail'] = $row['email'];
            $_SESSION['stu_name'] = $row['name'];

            $avatar = $row['avatar'] ?? '';
            if(empty($avatar)) {
                $avatar = 'https://ui-avatars.com/api/?name=' . urlencode($row['name']) . '&background=6366f1&color=fff&size=150';
            }
            $_SESSION['stu_img'] = $avatar;

            echo "1";
        } else {
            echo "0";
        }
    } else {
        echo "0";
    }
    exit();
}

// ─── Handle AJAX Register ───
if(isset($_POST['checkStuEmail']) && $_POST['checkStuEmail'] == "checkStuEmail") {
    $name = sanitize($_POST['stuname']);
    $email = sanitize($_POST['stuemail']);
    $password = $_POST['stupass'];

    if(empty($name) || empty($email) || empty($password)) {
        echo "0";
        exit();
    }

    if(strlen($password) < 6) {
        echo "2";
        exit();
    }

    $check_sql = "SELECT user_id FROM users WHERE email = ?";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param("s", $email);
    $check_stmt->execute();

    if($check_stmt->get_result()->num_rows > 0) {
        echo "3";
        exit();
    }

    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    $sql = "INSERT INTO users (name, email, password, role, avatar, status, created_at) 
            VALUES (?, ?, ?, 'student', '', 'active', NOW())";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sss", $name, $email, $hashed_password);

    if($stmt->execute()) {
        $new_user_id = $stmt->insert_id;
        $default_avatar = 'https://ui-avatars.com/api/?name=' . urlencode($name) . '&background=6366f1&color=fff&size=150';

        $update_sql = "UPDATE users SET avatar = ? WHERE user_id = ?";
        $update_stmt = $conn->prepare($update_sql);
        $update_stmt->bind_param("si", $default_avatar, $new_user_id);
        $update_stmt->execute();

        $_SESSION['is_login'] = true;
        $_SESSION['stu_id'] = $new_user_id;
        $_SESSION['stuLogEmail'] = $email;
        $_SESSION['stu_name'] = $name;
        $_SESSION['stu_img'] = $default_avatar;

        echo "1";
    } else {
        echo "0";
    }
    exit();
}

if(isLoggedIn()) {
    redirect('index.php');
}

include('./includes/header.php');
?>

<section class="auth-section">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8 col-xl-7">
                <div class="auth-card">
                    <div class="row g-0">
                        <!-- Left Side - Branding -->
                        <div class="col-lg-5 d-none d-lg-block">
                            <div class="auth-branding h-100 d-flex flex-column justify-content-center align-items-center text-center p-4"
                                 style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                                <div class="mb-4">
                                    <i class="fas fa-graduation-cap fa-4x text-white mb-3"></i>
                                    <h3 class="fw-bold text-white">كنزك التعليمي</h3>
                                    <p class="text-white-50">منصة التعلم الإلكتروني الأولى في ليبيا 🇱🇾</p>
                                </div>
                                <div class="mt-4">
                                    <div class="d-flex align-items-center mb-3 text-white">
                                        <i class="fas fa-check-circle text-success me-2"></i>
                                        <span>200+ دورة تعليمية</span>
                                    </div>
                                    <div class="d-flex align-items-center mb-3 text-white">
                                        <i class="fas fa-check-circle text-success me-2"></i>
                                        <span>محاضرون خبراء</span>
                                    </div>
                                    <div class="d-flex align-items-center text-white">
                                        <i class="fas fa-check-circle text-success me-2"></i>
                                        <span>شهادات معتمدة</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Right Side - Forms -->
                        <div class="col-lg-7 p-4 p-md-5">
                            <!-- Tabs -->
                            <ul class="nav nav-pills mb-4 justify-content-center gap-2" id="authTabs" role="tablist">
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link active px-4 py-2 rounded-pill fw-bold" id="login-tab" data-bs-toggle="tab" 
                                            data-bs-target="#login" type="button" role="tab">
                                        <i class="fas fa-sign-in-alt me-2"></i> تسجيل الدخول
                                    </button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link px-4 py-2 rounded-pill fw-bold" id="register-tab" data-bs-toggle="tab" 
                                            data-bs-target="#register" type="button" role="tab">
                                        <i class="fas fa-user-plus me-2"></i> إنشاء حساب
                                    </button>
                                </li>
                            </ul>

                            <div class="tab-content" id="authTabContent">
                                <!-- Login Form -->
                                <div class="tab-pane fade show active" id="login" role="tabpanel">
                                    <h4 class="text-center mb-4 fw-bold text-primary">
                                        <i class="fas fa-user-circle me-2"></i> تسجيل الدخول
                                    </h4>
                                    <form id="stuLoginForm" class="needs-validation" novalidate>
                                        <div class="mb-4">
                                            <label class="form-label fw-bold">البريد الإلكتروني</label>
                                            <div class="input-group">
                                                <span class="input-group-text bg-light border-end-0">
                                                    <i class="fas fa-envelope text-muted"></i>
                                                </span>
                                                <input type="email" class="form-control border-start-0 bg-light py-3" 
                                                       name="stuLogEmail" id="stuLogEmail" 
                                                       placeholder="example@email.com" required>
                                            </div>
                                        </div>
                                        <div class="mb-4">
                                            <label class="form-label fw-bold">كلمة المرور</label>
                                            <div class="input-group">
                                                <span class="input-group-text bg-light border-end-0">
                                                    <i class="fas fa-lock text-muted"></i>
                                                </span>
                                                <input type="password" class="form-control border-start-0 bg-light py-3" 
                                                       name="stuLogPass" id="stuLogPass" 
                                                       placeholder="••••••••" required>
                                                <button class="btn btn-outline-secondary border-start-0" type="button" 
                                                        onclick="togglePassword('stuLogPass')">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                            </div>
                                        </div>
                                        <div class="mb-4">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" id="remember">
                                                <label class="form-check-label" for="remember">تذكرني</label>
                                            </div>
                                        </div>
                                        <button type="button" class="btn btn-primary w-100 py-3 rounded-pill fw-bold" 
                                                id="stuLoginBtn" onclick="checkStuLogin()">
                                            <i class="fas fa-sign-in-alt me-2"></i> تسجيل الدخول
                                        </button>
                                    </form>
                                    <div id="statusLogMsg" class="mt-3"></div>

                                    <div class="text-center mt-4">
                                        <p class="text-muted mb-0">ليس لديك حساب؟ 
                                            <a href="#" onclick="switchToRegister(); return false;" class="text-primary fw-bold text-decoration-none">
                                                أنشئ حساباً جديداً
                                            </a>
                                        </p>
                                    </div>
                                </div>

                                <!-- Register Form -->
                                <div class="tab-pane fade" id="register" role="tabpanel">
                                    <h4 class="text-center mb-4 fw-bold text-success">
                                        <i class="fas fa-user-plus me-2"></i> إنشاء حساب جديد
                                    </h4>
                                    <form id="stuRegForm" class="needs-validation" novalidate>
                                        <div class="mb-3">
                                            <label class="form-label fw-bold">الاسم الكامل</label>
                                            <div class="input-group">
                                                <span class="input-group-text bg-light border-end-0">
                                                    <i class="fas fa-user text-muted"></i>
                                                </span>
                                                <input type="text" class="form-control border-start-0 bg-light py-3" 
                                                       name="stuname" id="stuname" 
                                                       placeholder="محمد أحمد" required>
                                            </div>
                                            <small id="statusMsg1" class="text-danger"></small>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label fw-bold">البريد الإلكتروني</label>
                                            <div class="input-group">
                                                <span class="input-group-text bg-light border-end-0">
                                                    <i class="fas fa-envelope text-muted"></i>
                                                </span>
                                                <input type="email" class="form-control border-start-0 bg-light py-3" 
                                                       name="stuemail" id="stuemail" 
                                                       placeholder="example@email.com" required>
                                            </div>
                                            <small id="statusMsg2" class="text-danger"></small>
                                        </div>
                                        <div class="mb-4">
                                            <label class="form-label fw-bold">كلمة المرور</label>
                                            <div class="input-group">
                                                <span class="input-group-text bg-light border-end-0">
                                                    <i class="fas fa-lock text-muted"></i>
                                                </span>
                                                <input type="password" class="form-control border-start-0 bg-light py-3" 
                                                       name="stupass" id="stupass" 
                                                       placeholder="••••••••" required minlength="6">
                                                <button class="btn btn-outline-secondary border-start-0" type="button" 
                                                        onclick="togglePassword('stupass')">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                            </div>
                                            <small id="statusMsg3" class="text-danger"></small>
                                        </div>
                                        <div class="mb-4">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" id="agreeTerms" required>
                                                <label class="form-check-label" for="agreeTerms">
                                                    أوافق على <a href="#" class="text-primary">شروط الاستخدام</a> و
                                                    <a href="#" class="text-primary">سياسة الخصوصية</a>
                                                </label>
                                            </div>
                                        </div>
                                        <button type="button" class="btn btn-success w-100 py-3 rounded-pill fw-bold" 
                                                id="signup" onclick="addStu()">
                                            <i class="fas fa-user-plus me-2"></i> إنشاء حساب
                                        </button>
                                    </form>
                                    <div id="successMsg" class="mt-3"></div>

                                    <div class="text-center mt-4">
                                        <p class="text-muted mb-0">لديك حساب بالفعل؟ 
                                            <a href="#" onclick="switchToLogin(); return false;" class="text-primary fw-bold text-decoration-none">
                                                سجل الدخول
                                            </a>
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<script>
// ─── Pure JavaScript - No jQuery ───

// Toggle Password Visibility
function togglePassword(inputId) {
    var input = document.getElementById(inputId);
    if(!input) return;

    var type = input.getAttribute('type') === 'password' ? 'text' : 'password';
    input.setAttribute('type', type);

    var btn = input.parentElement.querySelector('button i');
    if(btn) {
        btn.classList.toggle('fa-eye');
        btn.classList.toggle('fa-eye-slash');
    }
}

// Switch Between Login and Register
function switchToRegister() {
    var registerTab = document.getElementById('register-tab');
    if(registerTab) {
        var tab = new bootstrap.Tab(registerTab);
        tab.show();
    }
}

function switchToLogin() {
    var loginTab = document.getElementById('login-tab');
    if(loginTab) {
        var tab = new bootstrap.Tab(loginTab);
        tab.show();
    }
}

// AJAX Login using Fetch API (No jQuery)
function checkStuLogin() {
    var stuLogEmail = document.getElementById('stuLogEmail').value.trim();
    var stuLogPass = document.getElementById('stuLogPass').value;
    var statusDiv = document.getElementById('statusLogMsg');
    var btn = document.getElementById('stuLoginBtn');

    if(stuLogEmail === "") {
        statusDiv.innerHTML = '<div class="alert alert-danger rounded-4"><i class="fas fa-exclamation-circle me-2"></i>الرجاء إدخال البريد الإلكتروني</div>';
        return;
    }
    if(stuLogPass === "") {
        statusDiv.innerHTML = '<div class="alert alert-danger rounded-4"><i class="fas fa-exclamation-circle me-2"></i>الرجاء إدخال كلمة المرور</div>';
        return;
    }

    btn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i> جاري تسجيل الدخول...';
    btn.disabled = true;

    var formData = new FormData();
    formData.append('checkLogemail', 'checkLogemail');
    formData.append('stuLogEmail', stuLogEmail);
    formData.append('stuLogPass', stuLogPass);

    fetch('loginorsignup.php', {
        method: 'POST',
        body: formData
    })
    .then(function(response) { return response.text(); })
    .then(function(data) {
        if(data == "0") {
            statusDiv.innerHTML = '<div class="alert alert-danger rounded-4"><i class="fas fa-times-circle me-2"></i>البريد الإلكتروني أو كلمة المرور غير صحيحة!</div>';
            btn.innerHTML = '<i class="fas fa-sign-in-alt me-2"></i> تسجيل الدخول';
            btn.disabled = false;
        } else if(data == "1") {
            statusDiv.innerHTML = '<div class="alert alert-success rounded-4"><i class="fas fa-check-circle me-2"></i>تم تسجيل الدخول بنجاح! جاري التوجيه...</div>';
            setTimeout(function() {
                window.location.href = 'index.php';
            }, 1500);
        }
    })
    .catch(function() {
        statusDiv.innerHTML = '<div class="alert alert-danger rounded-4"><i class="fas fa-times-circle me-2"></i>حدث خطأ في الاتصال. حاول مرة أخرى.</div>';
        btn.innerHTML = '<i class="fas fa-sign-in-alt me-2"></i> تسجيل الدخول';
        btn.disabled = false;
    });
}

// AJAX Register using Fetch API
function addStu() {
    var stuname = document.getElementById('stuname').value.trim();
    var stuemail = document.getElementById('stuemail').value.trim();
    var stupass = document.getElementById('stupass').value;
    var successDiv = document.getElementById('successMsg');
    var btn = document.getElementById('signup');

    document.getElementById('statusMsg1').textContent = "";
    document.getElementById('statusMsg2').textContent = "";
    document.getElementById('statusMsg3').textContent = "";

    var isValid = true;

    if(stuname === "") {
        document.getElementById('statusMsg1').textContent = "الرجاء إدخال الاسم الكامل";
        isValid = false;
    }

    if(stuemail === "") {
        document.getElementById('statusMsg2').textContent = "الرجاء إدخال البريد الإلكتروني";
        isValid = false;
    } else if(!stuemail.includes('@')) {
        document.getElementById('statusMsg2').textContent = "الرجاء إدخال بريد إلكتروني صحيح";
        isValid = false;
    }

    if(stupass === "") {
        document.getElementById('statusMsg3').textContent = "الرجاء إدخال كلمة المرور";
        isValid = false;
    } else if(stupass.length < 6) {
        document.getElementById('statusMsg3').textContent = "كلمة المرور يجب أن تكون 6 أحرف على الأقل";
        isValid = false;
    }

    if(!isValid) return;

    btn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i> جاري إنشاء الحساب...';
    btn.disabled = true;

    var formData = new FormData();
    formData.append('checkStuEmail', 'checkStuEmail');
    formData.append('stuname', stuname);
    formData.append('stuemail', stuemail);
    formData.append('stupass', stupass);

    fetch('loginorsignup.php', {
        method: 'POST',
        body: formData
    })
    .then(function(response) { return response.text(); })
    .then(function(data) {
        if(data == "0") {
            successDiv.innerHTML = '<div class="alert alert-danger rounded-4"><i class="fas fa-times-circle me-2"></i>حدث خطأ. حاول مرة أخرى.</div>';
            btn.innerHTML = '<i class="fas fa-user-plus me-2"></i> إنشاء حساب';
            btn.disabled = false;
        } else if(data == "2") {
            document.getElementById('statusMsg3').textContent = "كلمة المرور يجب أن تكون 6 أحرف على الأقل";
            btn.innerHTML = '<i class="fas fa-user-plus me-2"></i> إنشاء حساب';
            btn.disabled = false;
        } else if(data == "3") {
            successDiv.innerHTML = '<div class="alert alert-warning rounded-4"><i class="fas fa-exclamation-triangle me-2"></i>البريد الإلكتروني مستخدم بالفعل!</div>';
            btn.innerHTML = '<i class="fas fa-user-plus me-2"></i> إنشاء حساب';
            btn.disabled = false;
        } else if(data == "1") {
            successDiv.innerHTML = '<div class="alert alert-success rounded-4"><i class="fas fa-check-circle me-2"></i>تم إنشاء الحساب بنجاح! جاري التوجيه...</div>';
            setTimeout(function() {
                window.location.href = 'index.php';
            }, 1500);
        }
    })
    .catch(function() {
        successDiv.innerHTML = '<div class="alert alert-danger rounded-4"><i class="fas fa-times-circle me-2"></i>حدث خطأ في الاتصال. حاول مرة أخرى.</div>';
        btn.innerHTML = '<i class="fas fa-user-plus me-2"></i> إنشاء حساب';
        btn.disabled = false;
    });
}

// Handle Enter Key
document.addEventListener('DOMContentLoaded', function() {
    var logPass = document.getElementById('stuLogPass');
    var regPass = document.getElementById('stupass');

    if(logPass) {
        logPass.addEventListener('keypress', function(e) {
            if(e.key === 'Enter') checkStuLogin();
        });
    }
    if(regPass) {
        regPass.addEventListener('keypress', function(e) {
            if(e.key === 'Enter') addStu();
        });
    }
});
</script>

<?php 
    include('./includes/footer.php');
?>