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
