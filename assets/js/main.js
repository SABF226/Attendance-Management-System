/**
 * English Club Attendance List - Main JavaScript
 * Color Palette: Deep Navy #1D1F5A, Rich Red #B61F24, Warm Cream #FCFBFF, Soft Blue-Grey #80BCCB
 */

document.addEventListener('DOMContentLoaded', function() {
    
    // Auto-hide alerts after 5 seconds
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(function(alert) {
        setTimeout(function() {
            alert.style.transition = 'opacity 0.5s';
            alert.style.opacity = '0';
            setTimeout(function() {
                alert.remove();
            }, 500);
        }, 5000);
    });
    
    // Confirm delete actions
    const deleteLinks = document.querySelectorAll('a[href*="action=delete"]');
    deleteLinks.forEach(function(link) {
        if (!link.hasAttribute('onclick')) {
            link.addEventListener('click', function(e) {
                if (!confirm('Are you sure you want to delete this item? This action cannot be undone.')) {
                    e.preventDefault();
                }
            });
        }
    });
    
    // Form validation
    const forms = document.querySelectorAll('form');
    forms.forEach(function(form) {
        form.addEventListener('submit', function(e) {
            const requiredFields = form.querySelectorAll('[required]');
            let isValid = true;
            
            requiredFields.forEach(function(field) {
                if (!field.value.trim()) {
                    isValid = false;
                    field.style.borderColor = '#B61F24';
                } else {
                    field.style.borderColor = '#80BCCB';
                }
            });
            
            if (!isValid) {
                e.preventDefault();
                alert('Please fill in all required fields.');
            }
        });
    });
    
    // Email validation
    const emailInputs = document.querySelectorAll('input[type="email"]');
    emailInputs.forEach(function(input) {
        input.addEventListener('blur', function() {
            if (this.value && !isValidEmail(this.value)) {
                this.style.borderColor = '#B61F24';
                showValidationMessage(this, 'Please enter a valid email address');
            } else {
                this.style.borderColor = '#80BCCB';
                removeValidationMessage(this);
            }
        });
    });
    
    // Select all / deselect all for attendance
    const selectAllCheckbox = document.getElementById('select-all');
    if (selectAllCheckbox) {
        selectAllCheckbox.addEventListener('change', function() {
            const checkboxes = document.querySelectorAll('.attendance-checkbox');
            checkboxes.forEach(function(checkbox) {
                checkbox.checked = selectAllCheckbox.checked;
            });
        });
    }
    
    // Search functionality enhancement
    const searchInput = document.querySelector('input[name="search"]');
    if (searchInput) {
        let searchTimeout;
        searchInput.addEventListener('input', function() {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(function() {
                // Optional: Auto-submit after typing stops
                // form.submit();
            }, 500);
        });
    }
    
    // Table row highlighting
    const tableRows = document.querySelectorAll('table tbody tr');
    tableRows.forEach(function(row) {
        row.addEventListener('click', function(e) {
            if (e.target.tagName !== 'A' && e.target.tagName !== 'BUTTON') {
                this.classList.toggle('selected');
            }
        });
    });
    
    // Date picker enhancement
    const dateInputs = document.querySelectorAll('input[type="date"]');
    dateInputs.forEach(function(input) {
        // Set min date to today
        const today = new Date().toISOString().split('T')[0];
        if (!input.min) {
            input.min = today;
        }
    });
    
    // Export button loading state
    const exportButtons = document.querySelectorAll('.btn[href*="export"]');
    exportButtons.forEach(function(btn) {
        btn.addEventListener('click', function() {
            const originalText = btn.textContent;
            btn.textContent = 'Exporting...';
            btn.disabled = true;
            
            setTimeout(function() {
                btn.textContent = originalText;
                btn.disabled = false;
            }, 3000);
        });
    });
});

// Helper functions
function isValidEmail(email) {
    const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return re.test(email);
}

function showValidationMessage(element, message) {
    removeValidationMessage(element);
    const errorDiv = document.createElement('div');
    errorDiv.className = 'validation-error';
    errorDiv.style.color = '#B61F24';
    errorDiv.style.fontSize = '0.875rem';
    errorDiv.style.marginTop = '0.25rem';
    errorDiv.textContent = message;
    element.parentNode.appendChild(errorDiv);
}

function removeValidationMessage(element) {
    const existing = element.parentNode.querySelector('.validation-error');
    if (existing) {
        existing.remove();
    }
}

// Export to CSV function (for browser-side export)
function exportToCSV(data, filename) {
    const csvContent = "data:text/csv;charset=utf-8," 
        + data.map(e => e.join(",")).join("\n");
    const encodedUri = encodeURI(csvContent);
    const link = document.createElement("a");
    link.setAttribute("href", encodedUri);
    link.setAttribute("download", filename);
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
}

// Action Dropdown toggle function
function toggleDropdown(button) {
    const dropdown = button.parentElement;
    const menu = dropdown.querySelector('.action-dropdown-menu');
    
    // Close all other dropdowns first
    document.querySelectorAll('.action-dropdown-menu.active').forEach(function(openMenu) {
        if (openMenu !== menu) {
            openMenu.classList.remove('active');
        }
    });
    
    // Toggle current dropdown
    menu.classList.toggle('active');
}

// Close dropdowns when clicking outside
document.addEventListener('click', function(e) {
    if (!e.target.closest('.action-dropdown')) {
        document.querySelectorAll('.action-dropdown-menu.active').forEach(function(menu) {
            menu.classList.remove('active');
        });
    }
});

/**
 * Sidebar toggle function
 * Expands or collapses the left sidebar
 */
function toggleSidebar() {
    const sidebar = document.getElementById('sidebar');
    if (sidebar) {
        sidebar.classList.toggle('collapsed');
        // Store preference in localStorage
        const isCollapsed = sidebar.classList.contains('collapsed');
        localStorage.setItem('sidebarCollapsed', isCollapsed);
    }
}

/**
 * Restore sidebar state on page load
 */
document.addEventListener('DOMContentLoaded', function() {
    const sidebar = document.getElementById('sidebar');
    if (sidebar) {
        const isCollapsed = localStorage.getItem('sidebarCollapsed') === 'true';
        if (isCollapsed) {
            sidebar.classList.add('collapsed');
        }
    }
});

/**
 * Mobile menu toggle function
 * Shows/hides sidebar on mobile devices
 */
function toggleMobileMenu() {
    const sidebar = document.getElementById('sidebar');
    if (sidebar) {
        sidebar.classList.toggle('active');
    }
}

// Mobile menu toggle function
function toggleMenu() {
    const menuToggle = document.querySelector('.menu-toggle');
    const navMenu = document.getElementById('navMenu');
    if (menuToggle && navMenu) {
        menuToggle.classList.toggle('active');
        navMenu.classList.toggle('active');
    }
}

// Close mobile menu when clicking outside
document.addEventListener('click', function(e) {
    const navMenu = document.getElementById('navMenu');
    const menuToggle = document.querySelector('.menu-toggle');
    if (navMenu && navMenu.classList.contains('active')) {
        if (!navMenu.contains(e.target) && !menuToggle.contains(e.target)) {
            navMenu.classList.remove('active');
            menuToggle.classList.remove('active');
        }
    }
});

// Close mobile menu when window is resized to desktop
window.addEventListener('resize', function() {
    if (window.innerWidth > 768) {
        const navMenu = document.getElementById('navMenu');
        const menuToggle = document.querySelector('.menu-toggle');
        if (navMenu) navMenu.classList.remove('active');
        if (menuToggle) menuToggle.classList.remove('active');
    }
});

/**
 * Attendance Bulk Actions
 */
function markAllPresent() {
    document.querySelectorAll('input[type="radio"][value="present"]').forEach(function(radio) {
        radio.checked = true;
    });
}

function resetAllAttendance() {
    document.querySelectorAll('input[type="radio"]').forEach(function(radio) {
        radio.checked = false;
    });
    document.querySelectorAll('input[type="text"], textarea').forEach(function(input) {
        if (input.name.includes('notes')) {
            input.value = '';
        }
    });
}

/**
 * Modal Confirmation System
 * Replaces native browser confirm() with styled modal
 */
let modalCallback = null;

function showModal(title, message, confirmText, isDanger, callback) {
    const modal = document.getElementById('confirmModal');
    const modalTitle = document.getElementById('modalTitle');
    const modalMessage = document.getElementById('modalMessage');
    const confirmBtn = document.getElementById('modalConfirmBtn');
    
    modalTitle.textContent = title;
    modalMessage.textContent = message;
    confirmBtn.textContent = confirmText || 'Confirm';
    
    // Set button style
    confirmBtn.className = 'modal-btn ' + (isDanger ? 'modal-btn-danger' : 'modal-btn-secondary');
    
    modalCallback = callback;
    modal.classList.add('active');
}

function closeModal() {
    const modal = document.getElementById('confirmModal');
    modal.classList.remove('active');
    modalCallback = null;
}

function confirmModalAction() {
    if (modalCallback) {
        modalCallback();
    }
    closeModal();
}

// Close modal on overlay click
document.addEventListener('DOMContentLoaded', function() {
    const modal = document.getElementById('confirmModal');
    if (modal) {
        modal.addEventListener('click', function(e) {
            if (e.target === modal) {
                closeModal();
            }
        });
    }
    
    // Close modal on Escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && modal && modal.classList.contains('active')) {
            closeModal();
        }
    });
    
    // Check for session toast messages
    checkSessionMessages();
});

/**
 * Toast Notification System
 * Displays floating notifications that auto-dismiss
 */
function showToast(message, type = 'info', duration = 5000) {
    const container = document.getElementById('toastContainer');
    if (!container) return;
    
    // Create toast element
    const toast = document.createElement('div');
    toast.className = 'toast toast-' + type;
    
    // Create message
    const msgSpan = document.createElement('span');
    msgSpan.className = 'toast-message';
    msgSpan.textContent = message;
    
    // Create close button
    const closeBtn = document.createElement('button');
    closeBtn.className = 'toast-close';
    closeBtn.innerHTML = '&times;';
    closeBtn.onclick = function() {
        removeToast(toast);
    };
    
    // Assemble toast
    toast.appendChild(msgSpan);
    toast.appendChild(closeBtn);
    
    // Add to container
    container.appendChild(toast);
    
    // Auto-dismiss after duration
    if (duration > 0) {
        setTimeout(function() {
            removeToast(toast);
        }, duration);
    }
    
    return toast;
}

function removeToast(toast) {
    if (!toast || toast.classList.contains('hiding')) return;
    
    toast.classList.add('hiding');
    
    // Remove from DOM after animation
    setTimeout(function() {
        if (toast.parentNode) {
            toast.parentNode.removeChild(toast);
        }
    }, 300);
}

/**
 * Check for session messages and convert to toasts
 * This should be called on page load
 */
function checkSessionMessages() {
    // Check if there's a session message container (set by PHP)
    const sessionMessage = document.getElementById('sessionMessage');
    if (sessionMessage) {
        const message = sessionMessage.dataset.message;
        const type = sessionMessage.dataset.type || 'info';
        
        if (message) {
            showToast(message, type);
            // Remove the hidden container
            sessionMessage.remove();
        }
    }
}

/**
 * Confirm delete action using modal
 * Usage: onclick="return confirmDelete(this, 'Item name')"
 */
function confirmDelete(element, itemName) {
    const message = itemName 
        ? `Are you sure you want to delete "${itemName}"? This action cannot be undone.`
        : 'Are you sure you want to delete this item? This action cannot be undone.';
    
    showModal('Confirm Delete', message, 'Delete', true, function() {
        window.location.href = element.href;
    });
    
    return false;
}

/**
 * Keyboard shortcuts for attendance
 * P = Present, A = Absent, E = Excused
 * Focus moves to notes field after selection
 */
document.addEventListener('DOMContentLoaded', function() {
    const attendanceTable = document.querySelector('.attendance-table');
    if (attendanceTable) {
        attendanceTable.addEventListener('keydown', function(e) {
            // Only process if focused on a radio button
            if (e.target.type === 'radio' && e.target.name.includes('attendance')) {
                const memberId = e.target.name.match(/\[(\d+)\]/)?.[1];
                if (!memberId) return;
                
                let targetValue = null;
                switch(e.key.toLowerCase()) {
                    case 'p':
                        targetValue = 'present';
                        break;
                    case 'a':
                        targetValue = 'absent';
                        break;
                    case 'e':
                        targetValue = 'excused';
                        break;
                }
                
                if (targetValue) {
                    e.preventDefault();
                    const radio = document.querySelector(`input[name="attendance[${memberId}][status]"][value="${targetValue}"]`);
                    if (radio) {
                        radio.checked = true;
                        // Move focus to notes field
                        const notesField = document.querySelector(`input[name="attendance[${memberId}][notes]"], textarea[name="attendance[${memberId}][notes]"]`);
                        if (notesField) {
                            notesField.focus();
                        }
                    }
                }
            }
        });
    }
});
// Mobile menu toggle function
function toggleMenu() {
    const menuToggle = document.querySelector('.menu-toggle');
    const navMenu = document.getElementById('navMenu');
    if (menuToggle && navMenu) {
        menuToggle.classList.toggle('active');
        navMenu.classList.toggle('active');
    }
}

// Close mobile menu when clicking outside
document.addEventListener('click', function(e) {
    const navMenu = document.getElementById('navMenu');
    const menuToggle = document.querySelector('.menu-toggle');
    if (navMenu && navMenu.classList.contains('active')) {
        if (!navMenu.contains(e.target) && !menuToggle.contains(e.target)) {
            navMenu.classList.remove('active');
            menuToggle.classList.remove('active');
        }
    }
});

// Close mobile menu when window is resized to desktop
window.addEventListener('resize', function() {
    if (window.innerWidth > 768) {
        const navMenu = document.getElementById('navMenu');
        const menuToggle = document.querySelector('.menu-toggle');
        if (navMenu) navMenu.classList.remove('active');
        if (menuToggle) menuToggle.classList.remove('active');
    }
});
