<?php
/**
 * Admin Shared Styles - كنزك التعليمي
 */
?>
<style>
    /* Admin Sidebar */
    .admin-sidebar {
        background: #1e293b;
        min-height: 100vh;
        color: white;
        padding: 20px 0;
        position: fixed;
        right: 0;
        top: 0;
        width: 260px;
        z-index: 1000;
        transition: transform 0.3s ease;
        overflow-y: auto;
        max-height: 100vh;
    }
    
    .admin-main {
        margin-right: 260px;
        padding: 30px;
        min-height: 100vh;
        background: #f8fafc;
        transition: margin-right 0.3s ease;
    }
    
    .admin-link {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 12px 20px;
        color: rgba(255,255,255,0.7);
        text-decoration: none;
        transition: all 0.3s;
        border-right: 3px solid transparent;
        cursor: pointer;
    }
    
    .admin-link:hover, .admin-link.active {
        background: rgba(255,255,255,0.1);
        color: white;
        border-right-color: #6366f1;
    }
    
    .admin-link i {
        width: 20px;
        text-align: center;
    }
    
    /* Stats Cards */
    .stat-card-admin {
        background: white;
        border-radius: 12px;
        padding: 25px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        transition: all 0.3s;
    }
    
    .stat-card-admin:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 30px rgba(0,0,0,0.1);
    }
    
    .stat-icon-admin {
        width: 50px;
        height: 50px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 20px;
        margin-bottom: 15px;
    }
    
    .stat-card-sm {
        background: white;
        border-radius: 12px;
        padding: 15px 20px;
        text-align: center;
        box-shadow: 0 2px 10px rgba(0,0,0,0.05);
    }
    
    /* Animations */
    .fade-in {
        animation: fadeIn 0.5s ease-in;
    }
    
    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(20px); }
        to { opacity: 1; transform: translateY(0); }
    }
    
    /* Student Card */
    .student-card {
        transition: all 0.3s ease;
    }
    
    .student-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 10px 30px rgba(0,0,0,0.1) !important;
    }
    
    /* Video Preview */
    .video-preview {
        position: relative;
        padding-bottom: 56.25%;
        height: 0;
        overflow: hidden;
        border-radius: 8px;
        background: #000;
    }
    
    .video-preview iframe, .video-preview video {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        border: none;
    }
    
    .video-preview-small {
        position: relative;
        padding-bottom: 56.25%;
        height: 0;
        overflow: hidden;
        border-radius: 8px;
        background: #000;
    }
    
    .video-preview-small iframe, .video-preview-small video {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        border: none;
    }
    
    /* Tables */
    .student-row:hover {
        background: #f8fafc;
    }
    
    /* Responsive */
    @media (max-width: 991.98px) {
        .admin-sidebar {
            transform: translateX(100%);
        }
        .admin-sidebar.active {
            transform: translateX(0);
        }
        .admin-main {
            margin-right: 0;
        }
    }
    
    /* Scrollbar */
    .courses-list::-webkit-scrollbar {
        width: 4px;
    }
    .courses-list::-webkit-scrollbar-track {
        background: #f1f1f1;
    }
    .courses-list::-webkit-scrollbar-thumb {
        background: #6366f1;
        border-radius: 4px;
    }
</style>

<!-- Sidebar Toggle Script -->
<script>
function toggleSidebar() {
    var sidebar = document.getElementById('adminSidebar');
    sidebar.classList.toggle('active');
}

// Close sidebar when clicking outside on mobile
document.addEventListener('click', function(e) {
    var sidebar = document.getElementById('adminSidebar');
    var toggleBtn = document.getElementById('sidebarToggle') || document.querySelector('.btn-light');
    if (window.innerWidth < 992) {
        if (!sidebar.contains(e.target) && (!toggleBtn || (e.target !== toggleBtn && !toggleBtn.contains(e.target)))) {
            sidebar.classList.remove('active');
        }
    }
});

// Animate cards on load
document.addEventListener('DOMContentLoaded', function() {
    var cards = document.querySelectorAll('.fade-in');
    cards.forEach(function(card, index) {
        card.style.animationDelay = (index * 0.1) + 's';
    });
});
</script>
