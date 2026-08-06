import './bootstrap';

const sidebar = document.querySelector('[data-sidebar]');
const sidebarToggle = document.querySelector('[data-sidebar-toggle]');

if (sidebar && sidebarToggle) {
    const storageKey = 'sihati.sidebar.collapsed';
    const toggleLabel = sidebarToggle.querySelector('[data-sidebar-toggle-label]');
    let isCollapsed = false;

    try {
        isCollapsed = window.localStorage.getItem(storageKey) === 'true';
    } catch {
        isCollapsed = false;
    }

    const renderSidebar = () => {
        sidebar.classList.toggle('is-collapsed', isCollapsed);
        sidebarToggle.setAttribute('aria-expanded', String(!isCollapsed));
        sidebarToggle.setAttribute('aria-label', isCollapsed ? 'Tampilkan menu' : 'Sembunyikan menu');
        sidebarToggle.setAttribute('title', isCollapsed ? 'Tampilkan menu' : 'Sembunyikan menu');

        if (toggleLabel) {
            toggleLabel.textContent = isCollapsed ? 'Tampilkan menu' : 'Sembunyikan menu';
        }
    };

    sidebarToggle.addEventListener('click', () => {
        isCollapsed = !isCollapsed;
        renderSidebar();

        try {
            window.localStorage.setItem(storageKey, String(isCollapsed));
        } catch {
            // Prefer the visual interaction even when browser storage is unavailable.
        }
    });

    renderSidebar();
}

const mandatoryPasswordModal = document.querySelector('[data-mandatory-password-modal]');

if (mandatoryPasswordModal) {
    const focusableSelector = [
        'a[href]',
        'button:not([disabled])',
        'input:not([disabled])',
        'select:not([disabled])',
        'textarea:not([disabled])',
        '[tabindex]:not([tabindex="-1"])',
    ].join(', ');

    const getFocusableElements = () => [...mandatoryPasswordModal.querySelectorAll(focusableSelector)];
    const firstFocusableElement = getFocusableElements()[0];

    document.body.classList.add('overflow-hidden');
    firstFocusableElement?.focus();

    mandatoryPasswordModal.addEventListener('keydown', (event) => {
        if (event.key === 'Escape') {
            event.preventDefault();

            return;
        }

        if (event.key !== 'Tab') {
            return;
        }

        const focusableElements = getFocusableElements();
        const firstElement = focusableElements[0];
        const lastElement = focusableElements[focusableElements.length - 1];

        if (!firstElement || !lastElement) {
            return;
        }

        if (event.shiftKey && document.activeElement === firstElement) {
            event.preventDefault();
            lastElement.focus();
        } else if (!event.shiftKey && document.activeElement === lastElement) {
            event.preventDefault();
            firstElement.focus();
        }
    });
}

const ticketForms = document.querySelectorAll('[data-ticket-form]');

ticketForms.forEach((ticketForm) => {
    const serviceSelect = ticketForm.querySelector('[data-ticket-service-select]');
    const servicePanels = [...ticketForm.querySelectorAll('[data-ticket-service-panel]')];
    const serviceEmptyState = ticketForm.querySelector('[data-ticket-service-empty]');
    const attachmentGroups = [...ticketForm.querySelectorAll('[data-ticket-attachment]')];
    const attachmentEmptyState = ticketForm.querySelector('[data-ticket-attachment-empty]');
    const locationSelect = ticketForm.querySelector('[data-ticket-location-select]');
    const locationRequiredLabels = [...ticketForm.querySelectorAll('[data-ticket-location-required]')];
    const locationHelp = ticketForm.querySelector('[data-ticket-location-help]');
    const submitButton = ticketForm.querySelector('[data-ticket-submit]');
    const submitLabel = ticketForm.querySelector('[data-ticket-submit-label]');
    const submitLoading = ticketForm.querySelector('[data-ticket-submit-loading]');

    if (!serviceSelect) {
        return;
    }

    const updateTicketForm = () => {
        const selectedOption = serviceSelect.options[serviceSelect.selectedIndex];
        const serviceId = serviceSelect.value;
        const serviceCode = selectedOption?.dataset.serviceCode || '';
        const hasService = serviceId !== '';

        servicePanels.forEach((panel) => {
            const active = panel.dataset.serviceId === serviceId;
            panel.classList.toggle('hidden', !active);
            panel.disabled = !active;
        });

        serviceEmptyState?.classList.toggle('hidden', hasService);

        attachmentGroups.forEach((group) => {
            const active = hasService && (group.dataset.serviceId === '' || group.dataset.serviceId === serviceId);
            group.classList.toggle('hidden', !active);
            group.querySelectorAll('[data-ticket-attachment-input]').forEach((input) => {
                input.disabled = !active;
            });
        });

        attachmentEmptyState?.classList.toggle('hidden', hasService || attachmentGroups.length === 0);

        const locationRequired = serviceCode === 'SVC-01' || serviceCode === 'SVC-05';

        if (locationSelect) {
            locationSelect.required = locationRequired;
        }

        locationRequiredLabels.forEach((label) => {
            label.classList.toggle('hidden', !locationRequired);
        });

        if (locationHelp) {
            locationHelp.textContent = locationRequired
                ? `Lokasi wajib diisi untuk ${serviceCode}.`
                : 'Layanan yang dipilih dapat menyimpan lokasi kosong.';
        }
    };

    serviceSelect.addEventListener('change', updateTicketForm);

    ticketForm.addEventListener('submit', () => {
        if (!submitButton) {
            return;
        }

        submitButton.disabled = true;
        submitButton.setAttribute('aria-busy', 'true');
        submitLabel?.classList.add('hidden');
        submitLoading?.classList.remove('hidden');
    });

    updateTicketForm();
});
