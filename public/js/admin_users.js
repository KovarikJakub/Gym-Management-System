const getCsrfToken = () => document.querySelector('meta[name="csrf-token"]').getAttribute('content');

let toastTimer;
const showToast = (msg, isSuccess) => {
    const toast = document.getElementById('toast');
    clearTimeout(toastTimer);
    toast.textContent = msg;
    toast.className = `admin-toast admin-toast--${isSuccess ? 'success' : 'error'} show`;
    toastTimer = setTimeout(() => toast.classList.remove('show'), 3500);
};

const refetchPageContent = async () => {
    const fetchResponse = await fetch('admin_users.php');
    const html = await fetchResponse.text();
    const doc = new DOMParser().parseFromString(html, 'text/html');
    const newContainer = doc.getElementById('usersTable');
    if (newContainer) {
        document.getElementById('usersTable').innerHTML = newContainer.innerHTML;
    }
};

const sendPostRequest = async (data) => {
    const body = new URLSearchParams({ csrf_token: getCsrfToken(), ...data });
    const response = await fetch('action_manage_users.php', {
        method: 'POST',
        headers: { 'X-Requested-With': 'XMLHttpRequest' },
        body: body,
    });
    return response.json();
};

const resetForm = () => {
    const form = document.getElementById('userForm');
    if (!form) return;
    form.reset();
    document.querySelector('#formBox h2').textContent = 'Create New User';
    document.getElementById('formAction').value = 'create';
    document.getElementById('formUserId').value = '';
    document.getElementById('formSubmitBtn').textContent = 'Create User';
    document.getElementById('formCancelBtn').style.display = 'none';
    document.getElementById('emailRow').style.display = '';
    document.getElementById('roleRow').style.display = '';
    document.getElementById('fieldEmail').setAttribute('required', 'required');
    document.getElementById('fieldPassword').placeholder = 'Leave blank to keep current';
};

const onTableClick = async (event) => {
    const editBtn = event.target.closest('.btn-edit');
    if (editBtn) {
        document.querySelector('#formBox h2').textContent = 'Edit User: ' + editBtn.getAttribute('data-username');
        document.getElementById('formAction').value = 'update';
        document.getElementById('formUserId').value = editBtn.getAttribute('data-id');
        document.getElementById('formSubmitBtn').textContent = 'Save Changes';
        document.getElementById('formCancelBtn').style.display = '';

        document.getElementById('emailRow').style.display = 'none';
        document.getElementById('roleRow').style.display = '';
        document.getElementById('fieldEmail').removeAttribute('required');

        document.getElementById('fieldRealName').value = editBtn.getAttribute('data-name');
        document.getElementById('fieldUsername').value = editBtn.getAttribute('data-username');
        document.getElementById('fieldPassword').value = '';
        document.getElementById('fieldPassword').placeholder = 'Leave blank to keep current';
        document.getElementById('fieldRole').value = editBtn.getAttribute('data-role');

        document.getElementById('formBox').scrollIntoView({ behavior: 'smooth' });
        return;
    }

    const toggleBtn = event.target.closest('.btn-toggle');
    if (toggleBtn) {
        const id = toggleBtn.getAttribute('data-id');
        try {
            const res = await sendPostRequest({ action: 'toggle_deactivate', user_id: id });
            showToast(res.message, res.success);
            if (res.success) {
                refetchPageContent();
                resetForm();
            }
        } catch (error) {
            console.error('Fetch error:', error);
        }
    }
};

const onFormSubmit = async (event) => {
    event.preventDefault();
    const form = document.getElementById('userForm');
    const data = Object.fromEntries(new FormData(form));
    try {
        const res = await sendPostRequest(data);
        showToast(res.message, res.success);
        if (!res.success) return;
        refetchPageContent();
        resetForm();
    } catch (error) {
        console.error('Fetch error:', error);
    }
};

document.addEventListener('DOMContentLoaded', () => {
    const usersTable = document.getElementById('usersTable');
    if (usersTable) usersTable.addEventListener('click', onTableClick);

    const cancelBtn = document.getElementById('formCancelBtn');
    if (cancelBtn) cancelBtn.addEventListener('click', resetForm);

    const userForm = document.getElementById('userForm');
    if (userForm) userForm.addEventListener('submit', onFormSubmit);
});