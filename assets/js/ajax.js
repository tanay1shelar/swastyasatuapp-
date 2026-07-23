<<<<<<< HEAD
/**
 * Healthcare & Medical Camp Management System (HMCMS)
 * Central AJAX Helper Script (assets/js/ajax.js)
 */
window.HMCMS_AJAX = {
    request: function(endpoint, data, callback) {
        if (window.HMCMS && window.HMCMS.apiCall) {
            return window.HMCMS.apiCall(data.action || endpoint, data).then(callback);
        }
    }
};
=======
// AJAX Helper Scripts
>>>>>>> origin/main
