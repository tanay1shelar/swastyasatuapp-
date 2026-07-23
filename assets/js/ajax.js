/**
 * AJAX Helper Service
 */
async function sendApiRequest(url, method = 'GET', payload = null) {
    const options = {
        method,
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json'
        }
    };
    if (payload && method !== 'GET') {
        options.body = JSON.stringify(payload);
    }
    const response = await fetch(url, options);
    return await response.json();
}
