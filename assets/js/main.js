// ==========================
// MediBuddy Main JS
// ==========================

// Modal Functions
function openModal(modalId) {
    document.getElementById(modalId).style.display = "block";
}

function closeModal(modalId) {
    document.getElementById(modalId).style.display = "none";
}

// Close modal when clicking outside
window.onclick = (event) => {
    if (event.target.classList.contains("modal")) {
        event.target.style.display = "none";
    }
}

// Form Validation
function validateForm(formId) {
    var form = document.getElementById(formId);
    var inputs = form.querySelectorAll("[required]");
    var isValid = true;

    inputs.forEach(input => {
        if (!input.value.trim()) {
            input.style.borderColor = "#f56565";
            isValid = false;
        } else {
            input.style.borderColor = "#ddd";
        }
    });

    return isValid;
}

// Email Validation
function validateEmail(email) {
    var re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return re.test(email);
}

// Password Strength Checker
function checkPasswordStrength(password) {
    var strength = 0;
    if (password.length >= 8) strength++;
    if (/[a-z]/.test(password)) strength++;
    if (/[A-Z]/.test(password)) strength++;
    if (/[0-9]/.test(password)) strength++;
    if (/[$@#&!]/.test(password)) strength++;
    return strength;
}

// Confirm Delete
function confirmDelete(message) {
    return confirm(message || "Are you sure you want to delete this item?");
}

// Auto-hide alerts after 5 seconds
document.addEventListener("DOMContentLoaded", () => {
    var alerts = document.querySelectorAll(".alert");
    alerts.forEach(alert => {
        setTimeout(() => {
            alert.style.transition = "opacity 0.5s";
            alert.style.opacity = "0";
            setTimeout(() => {
                alert.remove();
            }, 500);
        }, 5000);
    });
});