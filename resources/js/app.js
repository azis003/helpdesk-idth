import './bootstrap';

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
