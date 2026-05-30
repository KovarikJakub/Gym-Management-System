const getCsrfToken = () =>
    document.querySelector('meta[name="csrf-token"]').getAttribute('content');

let toastTimer;

const showToast = (msg, isSuccess) => {
    const alertBox = document.getElementById('interfaceAlertBox');
    clearTimeout(toastTimer);

    alertBox.textContent = msg;

    alertBox.className = `admin-notification admin-notification--${
        isSuccess ? 'success' : 'error'
    } toast-show`;

    toastTimer = setTimeout(() => {
        alertBox.classList.remove('toast-show');
    }, 3500);
};

const attachButtonListeners = () => {
    const editButtons = document.querySelectorAll('.btn-action-edit');

    for (let button of editButtons) {
        button.addEventListener('click', async (event) => onTableClick(event));
    }

    const deleteButtons = document.querySelectorAll('.btn-action-delete');

    for (let button of deleteButtons) {
        button.addEventListener('click', async (event) => onTableClick(event));
    }
};

const refetchPageContent = async () => {
    const response = await fetch('admin_classes.php');
    const html = await response.text();

    const temp = document.createElement('div');
    temp.innerHTML = html;

    const newContainer = temp.querySelector('#classesTable');

    if (newContainer) {
        document.getElementById('classesTable').innerHTML = newContainer.innerHTML;
        attachButtonListeners();
    }
};

const sendPostRequest = async (data) => {
    const body = new URLSearchParams({
        csrf_token: getCsrfToken(),
        ...data
    });

    const response = await fetch('action_manage_classes.php', {
        method: 'POST',
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: body
    });

    return response.json();
};

const resetForm = () => {
    const form = document.getElementById('classForm');
    if (!form) return;

    form.reset();

    document.getElementById('formSubmissionAction').value = 'create';
    document.getElementById('formRecordId').value = '';

    document.querySelector('#classFormBox h2').textContent = 'Add New Course';
    document.getElementById('formSubmitTrigger').textContent = 'Save Course';

    document.getElementById('formCancelTrigger').style.display = 'none';
};

const onTableClick = async (event) => {
    const editBtn = event.target.closest('.btn-action-edit');

    if (editBtn) {
        document.getElementById('formSubmissionAction').value = 'update';
        document.getElementById('formRecordId').value = editBtn.getAttribute('data-id');
        document.getElementById('inputFieldName').value = editBtn.getAttribute('data-name');
        document.getElementById('inputFieldType').value = editBtn.getAttribute('data-type');
        document.getElementById('inputFieldDifficulty').value = editBtn.getAttribute('data-difficulty');
        document.getElementById('inputFieldCapacity').value = editBtn.getAttribute('data-capacity');
        document.getElementById('inputFieldDescription').value = editBtn.getAttribute('data-description');

        document.querySelector('#classFormBox h2').textContent = `Modify Curriculum: ${editBtn.getAttribute('data-name')}`;
        document.getElementById('formSubmitTrigger').textContent = 'Save Changes';
        document.getElementById('formCancelTrigger').style.display = 'inline-block';

        document.getElementById('classFormBox').scrollIntoView({
            behavior: 'smooth'
        });

        return;
    }

    const deleteBtn = event.target.closest('.btn-action-delete');

    if (deleteBtn) {
        if (!confirm(`Permanently drop "${deleteBtn.getAttribute('data-name')}" from catalog?`)) {
            return;
        }

        try {
            const result = await sendPostRequest({
                action: 'delete',
                class_id: deleteBtn.getAttribute('data-id')
            });

            showToast(result.message, result.success);

            if (result.success) {
                await refetchPageContent();
                resetForm();
            }
        } catch (error) {
            console.error('Fetch error:', error);
        }
    }
};

const onFormSubmit = async (event) => {
    event.preventDefault();

    const form = document.getElementById('classForm');
    const data = Object.fromEntries(new FormData(form));

    try {
        const result = await sendPostRequest(data);
        showToast(result.message, result.success);

        if (!result.success) return;

        await refetchPageContent();
        resetForm();
    } catch (error) {
        console.error('Fetch error:', error);
    }
};

document.addEventListener('DOMContentLoaded', () => {
    attachButtonListeners();

    const cancelBtn = document.getElementById('formCancelTrigger');
    if (cancelBtn) {
        cancelBtn.addEventListener('click', resetForm);
    }

    const classForm = document.getElementById('classForm');
    if (classForm) {
        classForm.addEventListener('submit', onFormSubmit);
    }
});