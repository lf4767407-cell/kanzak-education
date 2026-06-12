

// Navbar scroll effect
document.addEventListener('DOMContentLoaded', function () {
    const navbar = document.querySelector('.navbar-custom');

    window.addEventListener('scroll', function () {
        if (window.scrollY > 50) {
            navbar.classList.add('scrolled');
        } else {
            navbar.classList.remove('scrolled');
        }
    });

    // Smooth scroll for anchor links
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                target.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        });
    });

    // Course search functionality
    const searchInput = document.getElementById('courseSearch');
    if (searchInput) {
        searchInput.addEventListener('input', function () {
            const searchTerm = this.value.toLowerCase();
            const courses = document.querySelectorAll('.course-item');

            courses.forEach(course => {
                const title = course.querySelector('.course-title')?.textContent.toLowerCase() || '';
                const desc = course.querySelector('p')?.textContent.toLowerCase() || '';

                if (title.includes(searchTerm) || desc.includes(searchTerm)) {
                    course.style.display = '';
                } else {
                    course.style.display = 'none';
                }
            });
        });
    }

    // Category filter
    const categoryFilter = document.getElementById('categoryFilter');
    if (categoryFilter) {
        categoryFilter.addEventListener('change', function () {
            const category = this.value.toLowerCase();
            const courses = document.querySelectorAll('.course-item');

            courses.forEach(course => {
                if (!category || course.dataset.category?.includes(category)) {
                    course.style.display = '';
                } else {
                    course.style.display = 'none';
                }
            });
        });
    }

    // Password toggle
    window.togglePassword = function (inputId) {
        const input = document.getElementById(inputId);
        if (input) {
            const type = input.type === 'password' ? 'text' : 'password';
            input.type = type;

            const icon = input.parentElement.querySelector('.fa-eye, .fa-eye-slash');
            if (icon) {
                icon.classList.toggle('fa-eye');
                icon.classList.toggle('fa-eye-slash');
            }
        }
    };

    // Form validation
    const forms = document.querySelectorAll('.needs-validation');
    forms.forEach(form => {
        form.addEventListener('submit', function (e) {
            if (!form.checkValidity()) {
                e.preventDefault();
                e.stopPropagation();
            }
            form.classList.add('was-validated');
        });
    });

    // Animate elements on scroll
    const animateElements = document.querySelectorAll('.animate-on-scroll');

    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('animate-fadeInUp');
                observer.unobserve(entry.target);
            }
        });
    }, { threshold: 0.1 });

    animateElements.forEach(el => observer.observe(el));

    // Dashboard sidebar toggle
    const sidebarToggle = document.getElementById('sidebarToggle');
    if (sidebarToggle) {
        sidebarToggle.addEventListener('click', function () {
            document.querySelector('.dashboard-sidebar').classList.toggle('active');
        });
    }

    // Toast notifications
    window.showToast = function (message, type = 'success') {
        const toastContainer = document.getElementById('toastContainer') || createToastContainer();

        const toast = document.createElement('div');
        toast.className = `alert alert-${type} alert-dismissible fade show`;
        toast.style.cssText = 'position: fixed; top: 20px; left: 20px; z-index: 9999; min-width: 300px; border-radius: 16px; font-weight: 600;';
        toast.innerHTML = `
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;

        toastContainer.appendChild(toast);

        setTimeout(() => {
            toast.remove();
        }, 5000);
    };

    function createToastContainer() {
        const container = document.createElement('div');
        container.id = 'toastContainer';
        document.body.appendChild(container);
        return container;
    }
});

// AJAX Functions for Login/Register
function checkStuLogin() {
    const email = document.getElementById('stuLogEmail')?.value;
    const password = document.getElementById('stuLogPass')?.value;
    const statusMsg = document.getElementById('statusLogMsg');

    if (!email || !password) {
        if (statusMsg) statusMsg.innerHTML = '<div class="alert alert-warning" style="border-radius:16px;">الرجاء إدخال البريد الإلكتروني وكلمة المرور</div>';
        return;
    }

    const formData = new FormData();
    formData.append('checkLogemail', 'true');
    formData.append('stuLogEmail', email);
    formData.append('stuLogPass', password);

    fetch('loginorsignup.php', {
        method: 'POST',
        body: formData
    })
        .then(response => response.text())
        .then(data => {
            if (data.trim() === '0') {
                if (statusMsg) statusMsg.innerHTML = '<div class="alert alert-danger" style="border-radius:16px;">البريد الإلكتروني أو كلمة المرور غير صحيحة</div>';
            } else {
                window.location.href = 'index.php';
            }
        })
        .catch(error => {
            console.error('Error:', error);
            if (statusMsg) statusMsg.innerHTML = '<div class="alert alert-danger" style="border-radius:16px;">حدث خطأ، يرجى المحاولة مرة أخرى</div>';
        });
}

function addStu() {
    const name = document.getElementById('stuname')?.value;
    const email = document.getElementById('stuemail')?.value;
    const password = document.getElementById('stupass')?.value;
    const successMsg = document.getElementById('successMsg');

    // Validation
    if (!name || !email || !password) {
        if (successMsg) successMsg.innerHTML = '<div class="alert alert-warning" style="border-radius:16px;">الرجاء ملء جميع الحقول</div>';
        return;
    }

    if (password.length < 6) {
        if (successMsg) successMsg.innerHTML = '<div class="alert alert-warning" style="border-radius:16px;">كلمة المرور يجب أن تكون 6 أحرف على الأقل</div>';
        return;
    }

    const formData = new FormData();
    formData.append('checkStuEmail', 'true');
    formData.append('stuname', name);
    formData.append('stuemail', email);
    formData.append('stupass', password);

    fetch('loginorsignup.php', {
        method: 'POST',
        body: formData
    })
        .then(response => response.text())
        .then(data => {
            if (data.trim() === '0') {
                if (successMsg) successMsg.innerHTML = '<div class="alert alert-danger" style="border-radius:16px;">البريد الإلكتروني مستخدم بالفعل</div>';
            } else {
                if (successMsg) {
                    successMsg.innerHTML = '<div class="alert alert-success" style="border-radius:16px;">تم إنشاء الحساب بنجاح! جاري تسجيل الدخول...</div>';
                }
                setTimeout(() => {
                    window.location.href = 'index.php';
                }, 1500);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            if (successMsg) successMsg.innerHTML = '<div class="alert alert-danger" style="border-radius:16px;">حدث خطأ، يرجى المحاولة مرة أخرى</div>';
        });
}

// Video player functions
function playLesson(videoUrl, lessonId) {
    const player = document.getElementById('videoPlayer');
    if (player) {
        player.src = videoUrl;
        player.play();

        // Mark as watched
        markLessonWatched(lessonId);
    }
}

function markLessonWatched(lessonId) {
    const formData = new FormData();
    formData.append('markWatched', 'true');
    formData.append('lesson_id', lessonId);

    fetch('student/watchCourse.php', {
        method: 'POST',
        body: formData
    })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const lessonElement = document.querySelector(`[data-lesson="${lessonId}"]`);
                if (lessonElement) {
                    lessonElement.classList.add('completed');
                    lessonElement.querySelector('.lesson-status').innerHTML = '<i class="fas fa-check-circle text-success"></i>';
                }
            }
        })
        .catch(error => console.error('Error:', error));
}

// Admin functions
function deleteCourse(courseId) {
    if (confirm('هل أنت متأكد من حذف هذه الدورة؟')) {
        window.location.href = `admin/courses.php?delete=${courseId}`;
    }
}

function deleteLesson(lessonId) {
    if (confirm('هل أنت متأكد من حذف هذا الدرس؟')) {
        window.location.href = `admin/lessons.php?delete=${lessonId}`;
    }
}
