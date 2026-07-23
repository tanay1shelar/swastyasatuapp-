/**
 * Healthcare & Medical Camp Management System (HMCMS)
 * Form Validation Helper Script (assets/js/validation.js)
 */
function validateRequiredFields(formElement) {
    return formElement ? formElement.checkValidity() : true;
}
