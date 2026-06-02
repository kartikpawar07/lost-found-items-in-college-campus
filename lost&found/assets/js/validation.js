// Client-side Form Validation logic for Lost & Found Management System

document.addEventListener('DOMContentLoaded', function () {
    // 1. Password Matching and Strength Validation for Register Form
    const registerForm = document.querySelector('form.needs-validation-register');
    if (registerForm) {
        registerForm.addEventListener('submit', function (event) {
            const password = document.getElementById('password');
            const confirmPassword = document.getElementById('confirm_password');
            const phone = document.getElementById('phone');
            let isValid = true;

            // Password mismatch validation
            if (password && confirmPassword) {
                if (password.value !== confirmPassword.value) {
                    confirmPassword.setCustomValidity('Passwords do not match.');
                    confirmPassword.classList.add('is-invalid');
                    const errorDiv = confirmPassword.nextElementSibling;
                    if (errorDiv && errorDiv.classList.contains('invalid-feedback')) {
                        errorDiv.textContent = 'Passwords do not match.';
                    }
                    isValid = false;
                } else {
                    confirmPassword.setCustomValidity('');
                    confirmPassword.classList.remove('is-invalid');
                    confirmPassword.classList.add('is-valid');
                }
            }

            // Phone number length validation
            if (phone) {
                const phoneVal = phone.value.trim();
                const phoneRegex = /^[0-9]{10,15}$/;
                if (!phoneRegex.test(phoneVal)) {
                    phone.setCustomValidity('Phone number must be between 10 to 15 digits.');
                    phone.classList.add('is-invalid');
                    isValid = false;
                } else {
                    phone.setCustomValidity('');
                    phone.classList.remove('is-invalid');
                    phone.classList.add('is-valid');
                }
            }

            if (!registerForm.checkValidity() || !isValid) {
                event.preventDefault();
                event.stopPropagation();
            }

            registerForm.classList.add('was-validated');
        }, false);
    }

    // 2. Generic Bootstrap forms validation activator
    const forms = document.querySelectorAll('.needs-validation');
    Array.prototype.slice.call(forms).forEach(function (form) {
        form.addEventListener('submit', function (event) {
            // File input validation (if any)
            const fileInput = form.querySelector('input[type="file"]');
            if (fileInput && fileInput.files.length > 0) {
                const file = fileInput.files[0];
                const allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
                const maxSize = 3 * 1024 * 1024; // 3MB

                if (!allowedTypes.includes(file.type)) {
                    fileInput.setCustomValidity('Only JPG, PNG, GIF, and WEBP images are allowed.');
                    fileInput.classList.add('is-invalid');
                    event.preventDefault();
                    event.stopPropagation();
                } else if (file.size > maxSize) {
                    fileInput.setCustomValidity('Image size should be less than 3MB.');
                    fileInput.classList.add('is-invalid');
                    event.preventDefault();
                    event.stopPropagation();
                } else {
                    fileInput.setCustomValidity('');
                    fileInput.classList.remove('is-invalid');
                    fileInput.classList.add('is-valid');
                }
            }

            if (!form.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
            }

            form.classList.add('was-validated');
        }, false);
    });

    // 3. Clear invalid status on key up
    const inputs = document.querySelectorAll('.form-control');
    inputs.forEach(input => {
        input.addEventListener('input', function() {
            if (this.checkValidity()) {
                this.classList.remove('is-invalid');
                this.setCustomValidity('');
            }
        });
    });
});
