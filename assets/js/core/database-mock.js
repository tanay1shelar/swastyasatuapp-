/**
 * Healthcare & Medical Camp Management System (HMCMS)
 * Backend Proxy Adapter
 * 
 * Intercepts read/write actions from page controllers and commits changes 
 * directly to api.php endpoints.
 */

(function () {
    // Helper to perform synchronous GET requests
    function syncGet(action) {
        try {
            const xhr = new XMLHttpRequest();
            xhr.open('GET', 'api.php?action=' + action, false); // SYNCHRONOUS
            xhr.send();
            if (xhr.status === 200) {
                const res = JSON.parse(xhr.responseText);
                return res.success ? res.data : [];
            }
        } catch (e) {
            console.error("HMCMS_DB Proxy connection error: ", e);
        }
        return [];
    }

    function sendGeneralUpdate(pat) {
        try {
            const xhr = new XMLHttpRequest();
            xhr.open('POST', 'api.php', false); // SYNCHRONOUS
            xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
            xhr.send(new URLSearchParams({
                action: 'update_patient',
                patient_id: pat.id,
                name: pat.name,
                gender: pat.gender,
                blood: pat.blood,
                dob: pat.dob,
                phone: pat.phone,
                email: pat.email || '',
                address: pat.address,
                emergencyName: pat.emergencyName,
                emergencyRelation: pat.emergencyRelation,
                emergencyPhone: pat.emergencyPhone,
                allergies: pat.allergies,
                chronic: pat.chronic,
                medications: pat.medications,
                camp: pat.camp,
                status: pat.status
            }).toString());
        } catch (e) {
            console.error(e);
        }
    }

    window.HMCMS_DB = {
        getCamps: function () {
            return syncGet('get_camps');
        },
        saveCamps: function (camps) {
            // Camp updates are read-only or managed by UI forms directly
        },
        getPatients: function () {
            return syncGet('search_patients&query=');
        },
        savePatients: function (patients) {
            const current = syncGet('search_patients&query=');
            
            // 1. Patient added
            if (patients.length > current.length) {
                const newPat = patients[patients.length - 1];
                try {
                    const xhr = new XMLHttpRequest();
                    xhr.open('POST', 'api.php', false);
                    
                    const body = new FormData();
                    body.append('action', 'register_patient');
                    body.append('name', newPat.name);
                    body.append('gender', newPat.gender);
                    body.append('blood', newPat.blood);
                    body.append('dob', newPat.dob);
                    body.append('phone', newPat.phone);
                    body.append('aadhaar', newPat.aadhaar);
                    body.append('address', newPat.address);
                    body.append('chronic', newPat.chronic);
                    body.append('allergies', newPat.allergies);
                    body.append('medications', newPat.medications);
                    body.append('emergencyName', newPat.emergencyName);
                    body.append('emergencyPhone', newPat.emergencyPhone);
                    body.append('camp', newPat.camp);

                    const photoInput = document.getElementById('photo-upload-input');
                    const docInput = document.getElementById('doc-upload-input');
                    if (photoInput && photoInput.files[0]) {
                        body.append('photo', photoInput.files[0]);
                    }
                    if (docInput && docInput.files[0]) {
                        body.append('document', docInput.files[0]);
                    }

                    xhr.send(body);
                } catch (e) {
                    console.error(e);
                }
            } 
            // 2. Patient deleted
            else if (patients.length < current.length) {
                current.forEach(c => {
                    if (!patients.some(p => p.id === c.id)) {
                        try {
                            const xhr = new XMLHttpRequest();
                            xhr.open('POST', 'api.php', false);
                            xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
                            xhr.send(new URLSearchParams({
                                action: 'delete_patient',
                                id: c.id
                            }).toString());
                        } catch (e) {
                            console.error(e);
                        }
                    }
                });
            } 
            // 3. Patient updated
            else {
                patients.forEach(pat => {
                    const match = current.find(c => c.id === pat.id);
                    if (match) {
                        // Compare status modifications
                        if (match.status !== pat.status) {
                            try {
                                const xhr = new XMLHttpRequest();
                                xhr.open('POST', 'api.php', false);
                                xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
                                
                                if (pat.status === 'Verified' || pat.status === 'Rejected') {
                                    xhr.send(new URLSearchParams({
                                        action: 'verify_patient',
                                        id: pat.id,
                                        status: pat.status,
                                        remarks: pat.status === 'Verified' ? 'Biometrics matched successfully' : 'Biometric mismatch'
                                    }).toString());
                                } else if (pat.status === 'In Triage') {
                                    const atts = JSON.parse(sessionStorage.getItem('hmcms_attendance') || '[]');
                                    const record = atts.find(a => a.patientId === pat.id);
                                    const prio = pat.triagePriority || (record ? record.triagePriority : 'Low');
                                    xhr.send(new URLSearchParams({
                                        action: 'checkin_patient',
                                        id: pat.id,
                                        status: 'Present',
                                        token: pat.token,
                                        triage_priority: prio
                                    }).toString());
                                } else if (pat.status === 'Completed') {
                                    xhr.send(new URLSearchParams({
                                        action: 'checkout_patient',
                                        id: pat.id
                                    }).toString());
                                } else if (pat.status === 'In Consultation') {
                                    xhr.send(new URLSearchParams({
                                        action: 'call_patient',
                                        id: pat.id
                                    }).toString());
                                } else {
                                    sendGeneralUpdate(pat);
                                }
                            } catch (e) {
                                console.error(e);
                            }
                        } else {
                            // Check if details modifications
                            if (match.name !== pat.name || match.phone !== pat.phone || match.address !== pat.address || match.allergies !== pat.allergies || match.chronic !== pat.chronic || match.medications !== pat.medications) {
                                sendGeneralUpdate(pat);
                            }
                        }
                    }
                });
            }
        },
        getVerifications: function () {
            return syncGet('get_verifications');
        },
        getAttendance: function () {
            return syncGet('get_attendance');
        },
        updateAttendance: function (patientId, status, priority, checkinTime, checkoutTime) {
            try {
                const xhr = new XMLHttpRequest();
                xhr.open('POST', 'api.php', false); // SYNCHRONOUS
                xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
                xhr.send(new URLSearchParams({
                    action: 'update_attendance',
                    id: patientId,
                    status: status,
                    triage_priority: priority,
                    check_in: checkinTime,
                    check_out: checkoutTime || '--'
                }).toString());
                if (xhr.status === 200) {
                    const res = JSON.parse(xhr.responseText);
                    return res.success;
                }
            } catch (e) {
                console.error(e);
            }
            return false;
        }
    };
})();
