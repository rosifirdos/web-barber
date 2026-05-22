/**
 * IF Barber - Admin Dashboard Scripts
 * Mobile sidebar, Modals, Image previews, and Confirmation handlers
 */

document.addEventListener('DOMContentLoaded', function () {

    // ============================================
    // Mobile Sidebar Toggle
    // ============================================
    var sidebarToggle = document.getElementById('sidebarToggle');
    var adminSidebar = document.querySelector('.admin-sidebar');

    if (sidebarToggle && adminSidebar) {
        sidebarToggle.addEventListener('click', function (e) {
            e.stopPropagation();
            adminSidebar.classList.toggle('active');
            
            // Toggle icon
            var icon = sidebarToggle.querySelector('i');
            if (icon) {
                if (adminSidebar.classList.contains('active')) {
                    icon.setAttribute('data-lucide', 'x');
                } else {
                    icon.setAttribute('data-lucide', 'menu');
                }
                lucide.createIcons();
            }
        });

        // Close sidebar when clicking outside on mobile
        document.addEventListener('click', function (e) {
            if (window.innerWidth <= 992 && adminSidebar.classList.contains('active')) {
                if (!adminSidebar.contains(e.target) && e.target !== sidebarToggle) {
                    adminSidebar.classList.remove('active');
                    var icon = sidebarToggle.querySelector('i');
                    if (icon) {
                        icon.setAttribute('data-lucide', 'menu');
                        lucide.createIcons();
                    }
                }
            }
        });
    }

    // ============================================
    // Modal Handlers
    // ============================================
    window.openModal = function (modalId) {
        var modal = document.getElementById(modalId);
        if (modal) {
            modal.classList.add('active');
            document.body.style.overflow = 'hidden';
        }
    };

    window.closeModal = function (modalId) {
        var modal = document.getElementById(modalId);
        if (modal) {
            modal.classList.remove('active');
            document.body.style.overflow = '';
        }
    };

    // Close modal when clicking overlay
    var modals = document.querySelectorAll('.admin-modal');
    modals.forEach(function (modal) {
        var overlay = modal.querySelector('.admin-modal__overlay');
        var closeBtn = modal.querySelector('.admin-modal__close');
        
        if (overlay) {
            overlay.addEventListener('click', function () {
                closeModal(modal.id);
            });
        }
        
        if (closeBtn) {
            closeBtn.addEventListener('click', function () {
                closeModal(modal.id);
            });
        }
    });

    // ============================================
    // Image Upload Preview Helper
    // ============================================
    window.initImagePreview = function (fileInputId, previewImgId, placeholderId) {
        var fileInput = document.getElementById(fileInputId);
        var previewImg = document.getElementById(previewImgId);
        var placeholder = document.getElementById(placeholderId);

        if (fileInput && previewImg && placeholder) {
            fileInput.addEventListener('change', function () {
                var file = this.files[0];
                if (file) {
                    var reader = new FileReader();
                    reader.onload = function (e) {
                        previewImg.src = e.target.result;
                        previewImg.style.display = 'block';
                        placeholder.style.display = 'none';
                    };
                    reader.readAsDataURL(file);
                }
            });
        }
    };

    // ============================================
    // Action Confirmation Wrapper
    // ============================================
    window.confirmAction = function (message, confirmCallback) {
        if (confirm(message)) {
            if (typeof confirmCallback === 'function') {
                confirmCallback();
            } else if (typeof confirmCallback === 'string') {
                window.location.href = confirmCallback;
            }
        }
    };

    // Auto-dismiss Flash Messages
    var flash = document.getElementById('flashMessage');
    if (flash) {
        setTimeout(function () {
            flash.style.opacity = '0';
            flash.style.transform = 'translateY(-20px)';
            setTimeout(function () {
                flash.remove();
            }, 300);
        }, 4000);
    }
});
