function openReviewModal(sessionId) {
    const modal = document.getElementById('reviewModal');
    const sessionIdInput = modal.querySelector('input[name="session_id"]');
    
    if (sessionIdInput) {
        sessionIdInput.value = sessionId;
    }
    
    modal.style.display = 'block';
    document.body.style.overflow = 'hidden'; // Prevent scrolling
}

function closeReviewModal() {
    const modal = document.getElementById('reviewModal');
    modal.style.display = 'none';
    document.body.style.overflow = 'auto'; // Restore scrolling
}

// Close modal when clicking outside of it
window.onclick = function(event) {
    const modal = document.getElementById('reviewModal');
    if (event.target == modal) {
        closeReviewModal();
    }
}

// Close modal on Escape key
document.addEventListener('keydown', function(event) {
    if (event.key === "Escape") {
        closeReviewModal();
    }
});
