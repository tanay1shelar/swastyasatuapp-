/**
 * Form Validation Utility
 */
function validateForm(formElement) {
    let isValid = true;
    const inputs = formElement.querySelectorAll('[required]');
    inputs.forEach(input => {
        if (!input.value.trim()) {
            input.classList.add('is-invalid');
            isValid = false;
        } else {
            input.classList.remove('is-invalid');
        }
    });
    return isValid;
}
