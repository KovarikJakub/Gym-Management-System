const refetchPageContent = async () => {
    // Refetch the entire sessions list and update UI
    const fetchResponse = await fetch('sessions.php');
    const html = await fetchResponse.text();
    
    const parser = new DOMParser();
    const doc = parser.parseFromString(html, 'text/html');
    const newContainer = doc.getElementById('sessions-container');
    
    if (newContainer)
    {
        document.getElementById('sessions-container').innerHTML = newContainer.innerHTML;
        attachButtonListeners(); // Re-attach listeners to new buttons
    }
}

const buildFormData = (sessionId) =>
{
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    const formData = new URLSearchParams();
    formData.append('session_id', sessionId);
    formData.append('csrf_token', csrfToken);

    return formData;
}

const onEnrollButtonClick = async (event, buttonElement) => {

    const sessionId = buttonElement.getAttribute('data-id');
    const formData = buildFormData(sessionId);
    try {
        const response = await fetch('action_enroll_to_class.php', {
            method: 'POST',
            body: formData
        });
        const result = await response.json();
        if (result.success) {
            // Refetch the entire sessions list and update UI
            refetchPageContent();
            alert(result.message);
        } else {
            alert(result.message);
        }
    } catch (error) {
        console.error('Fetch error:', error);
    }
}

const onCancelButtonClick = async (event, buttonElement) => {
    const sessionId = buttonElement.getAttribute('data-id');
    const formData = buildFormData(sessionId);
    try {
        const response = await fetch('action_cancel_enrollment.php', {
            method: 'POST',
            body: formData
        });
        const result = await response.json();
        if (result.success) {
            // Refetch the entire sessions list and update UI
            refetchPageContent();
            alert(result.message);
        } else {
            alert(result.message);
        }
    } catch (error) {
        console.error('Fetch error:', error);
    }
}

const attachButtonListeners = () => {

    const enrollButtons = document.querySelectorAll(".btn-enroll");
    for(let button of enrollButtons)
    {
        button.addEventListener('click', async (event) => onEnrollButtonClick(event, button))
    }

    const cancelButtons = document.querySelectorAll(".btn-cancel");
    for(let button of cancelButtons)
    {
        button.addEventListener('click', async (event) => onCancelButtonClick(event, button));
    }
}

attachButtonListeners();

const sessionForm = document.getElementById('sessionForm');
if (sessionForm) {
    sessionForm.addEventListener('submit', async (e) => {
        e.preventDefault(); 
        
        const formData = new FormData(sessionForm);
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        formData.append('csrf_token', csrfToken);

        try {
            const response = await fetch('action_add_session.php', {
                method: 'POST',
                body: formData
            });
            const result = await response.json();
            
            alert(result.message); 
            
            if (result.success) {
                sessionForm.reset(); 
                refetchPageContent(); 
            }
        } catch (error) {
            console.error('Fetch error:', error);
            alert('An error occurred while connecting to the server.');
        }
    });
}