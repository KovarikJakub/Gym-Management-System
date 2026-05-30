
const updateContent = async (url, pushState = true) => {
    if (pushState) {
        window.history.pushState({}, '', url);
    }
    
    try {
        const response = await fetch(url);
        const html = await response.text();
        const parser = new DOMParser();
        const doc = parser.parseFromString(html, 'text/html');
        
        // Try updating the broad dynamic area (used on sessions.php)
        const newDynamicArea = doc.getElementById('sessions-dynamic-area');
        const currentDynamicArea = document.getElementById('sessions-dynamic-area');
        
        if (newDynamicArea && currentDynamicArea) {
            currentDynamicArea.innerHTML = newDynamicArea.innerHTML;
        } else {
            // Fallback to updating just the sessions container (used on dashboard)
            const newContainer = doc.getElementById('sessions-container');
            const currentContainer = document.getElementById('sessions-container');
            if (newContainer && currentContainer) {
                currentContainer.innerHTML = newContainer.innerHTML;
            }
        }
        
        // Always re-attach listeners because the DOM changed
        attachEventListeners();
    } catch (error) {
        console.error('Error updating content:', error);
    }
};

const buildFormData = (sessionId) => {
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    const formData = new URLSearchParams();
    formData.append('session_id', sessionId);
    formData.append('csrf_token', csrfToken);
    return formData;
};

const handleEnrollmentAction = async (actionUrl, sessionId) => {
    const formData = buildFormData(sessionId);
    try {
        const response = await fetch(actionUrl, {
            method: 'POST',
            body: formData
        });
        const result = await response.json();
        if (result.success) {
            updateContent(window.location.href, false);
        } else {
            alert(result.message);
        }
    } catch (error) {
        console.error('Action error:', error);
    }
};

const attachEventListeners = () => {
    // Enrollment / Cancellation buttons
    document.querySelectorAll(".btn-enroll").forEach(btn => {
        btn.addEventListener('click', () => handleEnrollmentAction('action_enroll_to_class.php', btn.getAttribute('data-id')));
    });

    document.querySelectorAll(".btn-cancel").forEach(btn => {
        btn.addEventListener('click', () => handleEnrollmentAction('action_cancel_enrollment.php', btn.getAttribute('data-id')));
    });

    // Week Selector Links
    document.querySelectorAll(".week-selector a").forEach(link => {
        link.addEventListener('click', (e) => {
            e.preventDefault();
            updateContent(link.href);
        });
    });

    // Filter Form Changes
    const filterForm = document.querySelector('.additional-filters');
    if (filterForm) {
        filterForm.querySelectorAll('select').forEach(select => {
            select.onchange = null; // Remove inline onchange if present
            select.addEventListener('change', () => {
                const formData = new FormData(filterForm);
                const params = new URLSearchParams(formData);
                updateContent(`sessions.php?${params.toString()}`);
            });
        });

        const clearLink = filterForm.querySelector('.clear-filters');
        if (clearLink) {
            clearLink.addEventListener('click', (e) => {
                e.preventDefault();
                updateContent(clearLink.href);
            });
        }
    }
};

// Handle browser back/forward navigation
window.onpopstate = () => updateContent(window.location.href, false);

// Initial attachment
attachEventListeners();