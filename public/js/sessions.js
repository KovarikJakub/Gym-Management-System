

const onEnrollButtonClick = async (event, buttonElement) => {
    const sessionId = buttonElement.getAttribute('data-id');
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    const formData = new URLSearchParams();
    formData.append('session_id', sessionId);
    formData.append('csrf_token', csrfToken);
    console.log(`button data-id-${sessionId} clicked`);

    try{
        const response = await fetch('action_enroll_to_class.php', {
            method: 'POST',
            body: formData
        });

        const result = await response.json();

        if (result.success) {
            // Update UI without page refresh
            buttonElement.innerText = 'Enrolled';
            buttonElement.disabled = true;
            buttonElement.classList.add('success');
            alert(result.message);
        } else {
            alert(result.message);
        }
    } catch (error) {
        console.error('Fetch error:', error);
    }
}

const attachEnrollListener = () => {

    const enrollButtons = document.querySelectorAll(".btn-enroll");
    for(let button of enrollButtons)
    {
        console.log(`attach for: brn-${button.getAttribute('data-id')}`);
        button.addEventListener('click', async (event) => onEnrollButtonClick(event, button))
    }
}

attachEnrollListener();