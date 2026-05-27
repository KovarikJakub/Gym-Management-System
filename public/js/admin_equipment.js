const getCsrfToken = () =>
    document.querySelector('meta[name="csrf-token"]').getAttribute('content');

let toastTimer;

const showToast = (text, success) => {
    const toastEl = document.getElementById('interfaceAlertBox');
    clearTimeout(toastTimer);

    toastEl.textContent = text;
    toastEl.className = `admin-notification admin-notification--${
        success ? 'success' : 'error'
    } toast-show`;

    toastTimer = setTimeout(() => {
        toastEl.className = '';
    }, 3000);
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
    const fetchResponse = await fetch('equipment.php');
    const html = await fetchResponse.text();

    const tempContainer = document.createElement('div');
    tempContainer.innerHTML = html;

    const newContainer = tempContainer.querySelector('#equipmentMasterTable');

    if (newContainer) {
        document.getElementById('equipmentMasterTable').innerHTML = newContainer.innerHTML;
        attachButtonListeners();
    }

    const counters = [
        'count-available',
        'count-in-use',
        'count-maintenance'
    ];

    for (let id of counters) {
        const newCounter = tempContainer.querySelector(`#${id}`);
        const currentCounter = document.getElementById(id);

        if (newCounter && currentCounter) {
            currentCounter.textContent = newCounter.textContent;
        }
    }
};

const sendPostRequest = async (data) => {
    const body = new URLSearchParams({
        csrf_token: getCsrfToken(),
        ...data
    });

    const response = await fetch('action_manage_equipment.php', {
        method: 'POST',
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: body
    });

    return response.json();
};

const resetForm = () => {
    const form = document.getElementById('equipmentLogisticsForm');
    if (!form) return;

    form.reset();
    document.getElementById('formSubmissionAction').value = 'create';
    document.getElementById('formRecordId').value = '';
    
    document.querySelector('#equipmentFormModule h2').textContent = 'Add New Equipment';
    document.getElementById('formSubmitTrigger').textContent = 'Add Equipment';
};

const onTableClick = async (event) => {
    const editBtn = event.target.closest('.btn-action-edit');

    if (editBtn) {
        document.getElementById('formSubmissionAction').value = 'update';
        document.getElementById('formRecordId').value = editBtn.getAttribute('data-id');
        document.getElementById('inputFieldName').value = editBtn.getAttribute('data-name');
        document.getElementById('inputFieldStatus').value = editBtn.getAttribute('data-status');

        document.querySelector('#equipmentFormModule h2').textContent = `Edit: ${editBtn.getAttribute('data-name')}`;
        document.getElementById('formSubmitTrigger').textContent = 'Save Changes';
        document.getElementById('equipmentFormModule').scrollIntoView({ behavior: 'smooth' });
        return;
    }

    const deleteBtn = event.target.closest('.btn-action-delete');
    if (deleteBtn) {
        if (!confirm(`Delete "${deleteBtn.getAttribute('data-name')}"?`)) return;

        try {
            const result = await sendPostRequest({
                action: 'delete',
                equipment_id: deleteBtn.getAttribute('data-id')
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

    const form = document.getElementById('equipmentLogisticsForm');
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

    const form = document.getElementById('equipmentLogisticsForm');
    if (form) {
        form.addEventListener('submit', onFormSubmit);
    }
});