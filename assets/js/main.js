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
