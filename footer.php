<?php
// Close database connection if exists
if(isset($conn) && $conn instanceof mysqli) {
    $conn->close();
}
?>

<!-- ═══════════════════════════════════════════════════════════════ -->
<!-- Footer -->
<!-- ═══════════════════════════════════════════════════════════════ -->
<footer class="footer">
    <div class="container">
        <div class="row g-4">
            <!-- Brand Column -->
            <div class="col-lg-4">
                <h4 class="fw-bold mb-3">
                    <i class="fas fa-graduation-cap me-2 text-primary"></i>كنزك التعليمي
                </h4>
                <p class="text-white-50 mb-3">
                    منصة تعليمية إلكترونية رائدة في <strong>ليبيا</strong>، نقدم دورات مجانية عالية الجودة في مختلف المجالات التقنية.
                </p>
                <div class="d-flex gap-3">
                    <a href="#" class="btn btn-outline-light btn-sm rounded-circle" style="width: 40px; height: 40px;" title="Facebook">
                        <i class="fab fa-facebook-f"></i>
                    </a>
                    <a href="#" class="btn btn-outline-light btn-sm rounded-circle" style="width: 40px; height: 40px;" title="Twitter">
                        <i class="fab fa-twitter"></i>
                    </a>
                    <a href="#" class="btn btn-outline-light btn-sm rounded-circle" style="width: 40px; height: 40px;" title="Instagram">
                        <i class="fab fa-instagram"></i>
                    </a>
                    <a href="#" class="btn btn-outline-light btn-sm rounded-circle" style="width: 40px; height: 40px;" title="YouTube">
                        <i class="fab fa-youtube"></i>
                    </a>
                    <a href="#" class="btn btn-outline-light btn-sm rounded-circle" style="width: 40px; height: 40px;" title="Telegram">
                        <i class="fab fa-telegram-plane"></i>
                    </a>
                </div>
            </div>

            <!-- Quick Links -->
            <div class="col-lg-2 col-md-4">
                <h6 class="fw-bold mb-3">روابط سريعة</h6>
                <ul class="list-unstyled">
                    <li class="mb-2"><a href="index.php" class="footer-link"><i class="fas fa-chevron-left me-1 small"></i> الرئيسية</a></li>
                    <li class="mb-2"><a href="courses.php" class="footer-link"><i class="fas fa-chevron-left me-1 small"></i> الدورات</a></li>
                    <li class="mb-2"><a href="contact.php" class="footer-link"><i class="fas fa-chevron-left me-1 small"></i> تواصل معنا</a></li>
                    <li class="mb-2"><a href="loginorsignup.php" class="footer-link"><i class="fas fa-chevron-left me-1 small"></i> تسجيل الدخول</a></li>
                    <?php if(isset($_SESSION['is_login']) && $_SESSION['is_login'] === true): ?>
                    <li class="mb-2"><a href="profile.php" class="footer-link"><i class="fas fa-chevron-left me-1 small"></i> الملف الشخصي</a></li>
                    <?php endif; ?>
                </ul>
            </div>

            <!-- Categories -->
            <div class="col-lg-2 col-md-4">
                <h6 class="fw-bold mb-3">التصنيفات</h6>
                <ul class="list-unstyled">
                    <li class="mb-2"><a href="courses.php" class="footer-link"><i class="fas fa-code me-1"></i> برمجة</a></li>
                    <li class="mb-2"><a href="courses.php" class="footer-link"><i class="fas fa-palette me-1"></i> تصميم</a></li>
                    <li class="mb-2"><a href="courses.php" class="footer-link"><i class="fas fa-bullhorn me-1"></i> تسويق</a></li>
                    <li class="mb-2"><a href="courses.php" class="footer-link"><i class="fas fa-language me-1"></i> لغات</a></li>
                    <li class="mb-2"><a href="courses.php" class="footer-link"><i class="fas fa-database me-1"></i> قواعد بيانات</a></li>
                </ul>
            </div>

            <!-- Contact Info -->
            <div class="col-lg-4 col-md-4">
                <h6 class="fw-bold mb-3">معلومات التواصل</h6>
                <ul class="list-unstyled text-white-50">
                    <li class="mb-3">
                        <i class="fas fa-map-marker-alt me-2 text-primary"></i>
                        <strong>مصراتة - ليبيا</strong>
                    </li>
                    <li class="mb-2">
                        <i class="fas fa-phone me-2 text-primary"></i>
                        <span dir="ltr">+218 91 000 0000</span>
                    </li>
                    <li class="mb-2">
                        <i class="fas fa-phone me-2 text-primary"></i>
                        <span dir="ltr">+218 51 000 000</span>
                    </li>
                    <li class="mb-2">
                        <i class="fas fa-envelope me-2 text-primary"></i>
                        info@kanzak.ly
                    </li>
                    <li class="mb-2">
                        <i class="fas fa-globe me-2 text-primary"></i>
                        www.kanzak.ly
                    </li>
                </ul>
            </div>
        </div>

        <hr class="my-4 border-secondary">

        <!-- Elegant Copyright -->
        <div class="row align-items-center">
            <div class="col-md-6 text-center text-md-start">
                <p class="mb-0 text-white-50">
                    © 2026 كنزك التعليمي - ليبيا - جميع الحقوق محفوظة
                </p>
            </div>
            <div class="col-md-6 text-center text-md-end">
                <p class="mb-0 text-white-50">
                    <strong>مصراتة - ليبيا</strong>
                </p>
            </div>
        </div>
    </div>
</footer>

<!-- ═══════════════════════════════════════════════════════════════ -->
<!-- Scripts -->
<!-- ═══════════════════════════════════════════════════════════════ -->

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<!-- Pure JavaScript Utilities (No jQuery) -->
<script>
// ─── Scroll Animation ───
function initScrollAnimation() {
    var elements = document.querySelectorAll('.animate-on-scroll');
    var observer = new IntersectionObserver(function(entries) {
        entries.forEach(function(entry) {
            if (entry.isIntersecting) {
                entry.target.classList.add('visible');
            }
        });
    }, { threshold: 0.1 });

    elements.forEach(function(el) {
        observer.observe(el);
    });
}

// ─── Initialize on Load ───
document.addEventListener('DOMContentLoaded', function() {
    initScrollAnimation();

    // Navbar scroll effect
    var navbar = document.querySelector('.navbar-custom');
    if(navbar) {
        window.addEventListener('scroll', function() {
            if(window.scrollY > 50) {
                navbar.style.boxShadow = '0 4px 30px rgba(0,0,0,0.15)';
            } else {
                navbar.style.boxShadow = '0 2px 20px rgba(0,0,0,0.08)';
            }
        });
    }
});

// ─── Toggle Password Visibility ───
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

// ─── Course Search and Filter (Pure JS) ───
document.addEventListener('DOMContentLoaded', function() {
    var searchInput = document.getElementById('courseSearch');
    if(searchInput) {
        searchInput.addEventListener('keyup', function() {
            var value = this.value.toLowerCase();
            var items = document.querySelectorAll('#coursesGrid .course-item');
            items.forEach(function(item) {
                var text = item.textContent.toLowerCase();
                item.style.display = text.indexOf(value) > -1 ? '' : 'none';
            });
        });
    }

    var categoryFilter = document.getElementById('categoryFilter');
    if(categoryFilter) {
        categoryFilter.addEventListener('change', function() {
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
    }
});

// ─── Preview Avatar Before Upload ───
function previewAvatar(input) {
    if (input.files && input.files[0]) {
        var reader = new FileReader();
        reader.onload = function(e) {
            var tempPreview = document.getElementById('tempPreview');
            var container = document.getElementById('avatarPreviewContainer');
            if(tempPreview) tempPreview.src = e.target.result;
            if(container) container.style.display = 'block';
        };
        reader.readAsDataURL(input.files[0]);
    }
}

function previewModalAvatar(input) {
    if (input.files && input.files[0]) {
        var reader = new FileReader();
        reader.onload = function(e) {
            var tempPreview = document.getElementById('modalTempPreview');
            var container = document.getElementById('modalPreviewContainer');
            if(tempPreview) tempPreview.src = e.target.result;
            if(container) container.style.display = 'block';
        };
        reader.readAsDataURL(input.files[0]);
    }
}

// ─── Auto-hide alerts ───
document.addEventListener('DOMContentLoaded', function() {
    var alerts = document.querySelectorAll('.alert');
    alerts.forEach(function(alert) {
        setTimeout(function() {
            if(alert && alert.parentElement) {
                alert.style.opacity = '0';
                alert.style.transition = 'opacity 0.5s';
                setTimeout(function() {
                    alert.remove();
                }, 500);
            }
        }, 5000);
    });
});

// ─── Show Toast Notification ───
function showToast(message, type = 'success') {
    var toastContainer = document.getElementById('toastContainer');
    if(!toastContainer) {
        toastContainer = document.createElement('div');
        toastContainer.id = 'toastContainer';
        toastContainer.style.cssText = 'position:fixed;top:20px;left:20px;z-index:9999;direction:rtl;';
        document.body.appendChild(toastContainer);
    }
    
    var icons = {
        success: 'fa-check-circle',
        error: 'fa-times-circle',
        warning: 'fa-exclamation-triangle',
        info: 'fa-info-circle'
    };
    
    var colors = {
        success: '#10b981',
        error: '#ef4444',
        warning: '#f59e0b',
        info: '#3b82f6'
    };
    
    var toast = document.createElement('div');
    toast.style.cssText = 'background:white;padding:15px 20px;border-radius:12px;box-shadow:0 10px 30px rgba(0,0,0,0.15);margin-bottom:10px;display:flex;align-items:center;gap:10px;animation:slideIn 0.3s ease;';
    toast.innerHTML = '<i class="fas ' + icons[type] + '" style="color:' + colors[type] + ';font-size:20px;"></i><span style="font-weight:600;">' + message + '</span>';
    
    toastContainer.appendChild(toast);
    
    setTimeout(function() {
        toast.style.animation = 'slideOut 0.3s ease';
        setTimeout(function() {
            toast.remove();
        }, 300);
    }, 3000);
}

// Add CSS animations for toast
var style = document.createElement('style');
style.textContent = `
    @keyframes slideIn {
        from { transform: translateX(-100%); opacity: 0; }
        to { transform: translateX(0); opacity: 1; }
    }
    @keyframes slideOut {
        from { transform: translateX(0); opacity: 1; }
        to { transform: translateX(-100%); opacity: 0; }
    }
`;
document.head.appendChild(style);
</script>

</body>
</html>
