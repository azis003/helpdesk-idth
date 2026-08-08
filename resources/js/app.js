import './bootstrap';
import Swal from 'sweetalert2';
import 'sweetalert2/dist/sweetalert2.min.css';

const swalToast = Swal.mixin({
    toast: true,
    position: 'top-end',
    showConfirmButton: false,
    showCloseButton: true,
    timer: 5500,
    timerProgressBar: true,
    customClass: {
        popup: 'sihati-swal-popup',
    },
});

const escapeHtml = (value) => String(value).replace(/[&<>"']/g, (character) => ({
    '&': '&amp;',
    '<': '&lt;',
    '>': '&gt;',
    '"': '&quot;',
    "'": '&#039;',
}[character]));

const showSwalConfirmation = (message, options = {}) => Swal.fire({
    icon: 'warning',
    title: options.title || 'Konfirmasi tindakan',
    text: message,
    showCancelButton: true,
    confirmButtonText: options.confirmButtonText || 'Ya, lanjutkan',
    cancelButtonText: 'Batal',
    reverseButtons: true,
    focusCancel: true,
    allowOutsideClick: false,
    confirmButtonColor: '#0b98e5',
    cancelButtonColor: '#94a3b8',
    customClass: {
        popup: 'sihati-swal-popup',
    },
});

const requestFormSubmit = (form) => {
    if (typeof form.requestSubmit === 'function') {
        form.requestSubmit();

        return;
    }

    HTMLFormElement.prototype.submit.call(form);
};

const flashElement = document.querySelector('[data-swal-flash]');

if (flashElement) {
    try {
        const flash = JSON.parse(flashElement.textContent || '{}');

        if (flash.success) {
            swalToast.fire({
                icon: 'success',
                title: flash.success,
            });
        } else if (flash.warning) {
            swalToast.fire({
                icon: 'warning',
                title: flash.warning,
                timer: 6500,
            });
        } else if (Array.isArray(flash.errors) && flash.errors.length > 0) {
            const errorItems = flash.errors
                .map((error) => `<li>${escapeHtml(error)}</li>`)
                .join('');

            Swal.fire({
                icon: 'error',
                title: 'Periksa kembali data yang dimasukkan.',
                html: `<ul class="sihati-swal-errors">${errorItems}</ul>`,
                confirmButtonText: 'Tutup',
                confirmButtonColor: '#0b98e5',
                customClass: {
                    popup: 'sihati-swal-popup',
                },
            });
        }
    } catch {
        // Abaikan flash yang tidak dapat dibaca agar halaman tetap dapat digunakan.
    }
}

const confirmableForms = [...document.querySelectorAll('form[data-swal-confirm]')];

confirmableForms.forEach((form) => {
    form.addEventListener('submit', async (event) => {
        if (form.dataset.swalConfirmed === 'true') {
            delete form.dataset.swalConfirmed;

            return;
        }

        if (form.dataset.swalPending === 'true') {
            event.preventDefault();

            return;
        }

        event.preventDefault();
        form.dataset.swalPending = 'true';

        try {
            const result = await showSwalConfirmation(form.dataset.swalConfirm);

            if (result.isConfirmed) {
                form.dataset.swalConfirmed = 'true';
                requestFormSubmit(form);
            }
        } finally {
            delete form.dataset.swalPending;
        }
    });
});

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

const passwordResetModal = document.querySelector('[data-password-reset-modal]');

if (passwordResetModal) {
    const passwordResetButtons = [...document.querySelectorAll('[data-password-reset-open]')];
    const passwordResetForm = passwordResetModal.querySelector('[data-password-reset-form]');
    const passwordResetInput = passwordResetModal.querySelector('[data-password-reset-input]');
    const passwordResetConfirmation = passwordResetModal.querySelector('[data-password-reset-confirmation]');
    const passwordResetUserId = passwordResetModal.querySelector('[data-password-reset-user-id]');
    const passwordResetTitle = passwordResetModal.querySelector('[data-password-reset-title]');
    const passwordResetSubmit = passwordResetModal.querySelector('[data-password-reset-submit]');
    const passwordResetFocusableSelector = [
        'button:not([disabled])',
        'input:not([disabled])',
        '[tabindex]:not([tabindex="-1"])',
    ].join(', ');
    let passwordResetLastTrigger = null;

    const getPasswordResetFocusableElements = () => [...passwordResetModal.querySelectorAll(passwordResetFocusableSelector)];

    const closePasswordResetModal = () => {
        passwordResetModal.classList.add('hidden');
        passwordResetModal.setAttribute('aria-hidden', 'true');
        document.body.classList.remove('overflow-hidden');
        passwordResetForm?.reset();
        passwordResetLastTrigger?.focus();
        passwordResetLastTrigger = null;
    };

    const openPasswordResetModal = (trigger) => {
        if (!passwordResetForm || !passwordResetInput) {
            return;
        }

        passwordResetLastTrigger = trigger;
        passwordResetForm.action = trigger.dataset.action || '';
        passwordResetUserId && (passwordResetUserId.value = trigger.dataset.userId || '');
        passwordResetTitle && (passwordResetTitle.textContent = `Ganti password ${trigger.dataset.userName || ''}`.trim());
        passwordResetInput.value = '';
        passwordResetModal.classList.remove('hidden');
        passwordResetModal.setAttribute('aria-hidden', 'false');
        document.body.classList.add('overflow-hidden');
        window.requestAnimationFrame(() => passwordResetInput.focus());
    };

    passwordResetButtons.forEach((button) => {
        button.setAttribute('aria-haspopup', 'dialog');
        button.setAttribute('aria-controls', 'password-reset-modal');
        button.addEventListener('click', () => openPasswordResetModal(button));
    });

    passwordResetModal.querySelectorAll('[data-password-reset-close]').forEach((closeButton) => {
        closeButton.addEventListener('click', closePasswordResetModal);
    });

    passwordResetModal.addEventListener('keydown', (event) => {
        if (event.key === 'Escape') {
            event.preventDefault();
            closePasswordResetModal();

            return;
        }

        if (event.key !== 'Tab') {
            return;
        }

        const focusableElements = getPasswordResetFocusableElements();
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

    passwordResetForm?.addEventListener('submit', () => {
        if (passwordResetConfirmation && passwordResetInput) {
            passwordResetConfirmation.value = passwordResetInput.value;
        }

        if (passwordResetSubmit) {
            passwordResetSubmit.disabled = true;
            passwordResetSubmit.setAttribute('aria-busy', 'true');
        }
    });

    const autoOpenUserId = passwordResetModal.dataset.autoUser;
    const autoOpenTrigger = passwordResetButtons.find((button) => button.dataset.userId === autoOpenUserId);

    if (autoOpenTrigger) {
        openPasswordResetModal(autoOpenTrigger);
    }
}

const teamCreateModal = document.querySelector('[data-team-create-modal]');

if (teamCreateModal) {
    const teamCreateButtons = [...document.querySelectorAll('[data-team-create-open]')];
    const teamCreateForm = teamCreateModal.querySelector('[data-team-create-form]');
    const teamCreateInput = teamCreateModal.querySelector('#team-create-name');
    const teamCreateSubmit = teamCreateModal.querySelector('[data-team-create-submit]');
    const teamCreateLabel = teamCreateModal.querySelector('[data-team-create-label]');
    const teamCreateLoading = teamCreateModal.querySelector('[data-team-create-loading]');
    const teamCreateFocusableSelector = [
        'button:not([disabled])',
        'input:not([disabled])',
        'select:not([disabled])',
        'textarea:not([disabled])',
        '[href]',
        '[tabindex]:not([tabindex="-1"])',
    ].join(', ');
    let teamCreateLastTrigger = null;

    const getTeamCreateFocusableElements = () => [...teamCreateModal.querySelectorAll(teamCreateFocusableSelector)];

    const closeTeamCreateModal = () => {
        teamCreateModal.classList.add('hidden');
        teamCreateModal.setAttribute('aria-hidden', 'true');
        document.body.classList.remove('overflow-hidden');
        teamCreateForm?.reset();

        if (teamCreateSubmit) {
            teamCreateSubmit.disabled = false;
            teamCreateSubmit.setAttribute('aria-busy', 'false');
        }

        teamCreateLabel?.classList.remove('hidden');
        teamCreateLoading?.classList.add('hidden');
        teamCreateLastTrigger?.focus();
        teamCreateLastTrigger = null;
    };

    const openTeamCreateModal = (trigger) => {
        teamCreateLastTrigger = trigger;
        teamCreateModal.classList.remove('hidden');
        teamCreateModal.setAttribute('aria-hidden', 'false');
        document.body.classList.add('overflow-hidden');
        window.requestAnimationFrame(() => teamCreateInput?.focus());
    };

    teamCreateButtons.forEach((button) => {
        button.setAttribute('aria-haspopup', 'dialog');
        button.setAttribute('aria-controls', 'team-create-modal');
        button.addEventListener('click', () => openTeamCreateModal(button));
    });

    teamCreateModal.querySelectorAll('[data-team-create-close]').forEach((closeButton) => {
        closeButton.addEventListener('click', closeTeamCreateModal);
    });

    teamCreateModal.addEventListener('keydown', (event) => {
        if (event.key === 'Escape') {
            event.preventDefault();
            closeTeamCreateModal();

            return;
        }

        if (event.key !== 'Tab') {
            return;
        }

        const focusableElements = getTeamCreateFocusableElements();
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

    teamCreateForm?.addEventListener('submit', () => {
        if (!teamCreateSubmit) {
            return;
        }

        teamCreateSubmit.disabled = true;
        teamCreateSubmit.setAttribute('aria-busy', 'true');
        teamCreateLabel?.classList.add('hidden');
        teamCreateLoading?.classList.remove('hidden');
    });

    if (teamCreateModal.dataset.autoOpen === 'true' && teamCreateButtons[0]) {
        openTeamCreateModal(teamCreateButtons[0]);
    }
}

const uiModals = [...document.querySelectorAll('[data-ui-modal]')];

uiModals.forEach((modal) => {
    const modalId = modal.id;
    const modalTriggers = [...document.querySelectorAll('[data-ui-modal-open]')]
        .filter((trigger) => trigger.dataset.uiModalOpen === modalId);
    const modalForms = [...modal.querySelectorAll('[data-ui-modal-form]')];
    const focusableSelector = [
        'button:not([disabled]):not([tabindex="-1"])',
        'input:not([disabled])',
        'select:not([disabled])',
        'textarea:not([disabled])',
        '[href]',
        '[tabindex]:not([tabindex="-1"])',
    ].join(', ');
    let lastTrigger = null;

    const getFocusableElements = () => [...modal.querySelectorAll(focusableSelector)];

    const resetModalForm = (form) => {
        if (!form) {
            return;
        }

        form.reset();

        if (modal.dataset.clearOnClose === 'true') {
            const validationErrorIds = new Set(
                [...form.querySelectorAll('[data-ui-validation-error]')]
                    .map((error) => error.id)
                    .filter(Boolean),
            );

            form.querySelectorAll('input, select, textarea').forEach((control) => {
                if (control.type === 'hidden') {
                    return;
                }

                if (control.type === 'checkbox' || control.type === 'radio') {
                    control.checked = false;
                } else if (control.tagName === 'SELECT') {
                    control.selectedIndex = 0;
                } else {
                    control.value = '';
                }
            });

            form.querySelectorAll('[aria-invalid="true"]').forEach((field) => {
                field.removeAttribute('aria-invalid');
                const describedBy = field.getAttribute('aria-describedby');

                if (describedBy) {
                    const remainingIds = describedBy
                        .split(/\s+/)
                        .filter((id) => id && !validationErrorIds.has(id));

                    if (remainingIds.length > 0) {
                        field.setAttribute('aria-describedby', remainingIds.join(' '));
                    } else {
                        field.removeAttribute('aria-describedby');
                    }
                }
            });

            form.querySelectorAll('[data-ui-validation-error]').forEach((error) => error.remove());
        }

        const submit = form.querySelector('[data-ui-modal-submit]');
        const label = form.querySelector('[data-ui-modal-label]');
        const loading = form.querySelector('[data-ui-modal-loading]');

        if (submit) {
            submit.disabled = false;
            submit.setAttribute('aria-busy', 'false');
        }

        label?.classList.remove('hidden');
        loading?.classList.add('hidden');
    };

    const closeModal = () => {
        modal.classList.add('hidden');
        modal.setAttribute('aria-hidden', 'true');
        document.body.classList.remove('overflow-hidden');

        if (modal.dataset.resetOnClose === 'true') {
            modalForms.forEach(resetModalForm);
        }

        lastTrigger?.focus();
        lastTrigger = null;
    };

    const openModal = (trigger = null) => {
        lastTrigger = trigger;
        modal.classList.remove('hidden');
        modal.setAttribute('aria-hidden', 'false');
        document.body.classList.add('overflow-hidden');

        window.requestAnimationFrame(() => {
            const initialFocus = modal.querySelector('[data-ui-modal-focus]') || getFocusableElements()[0];
            initialFocus?.focus();
        });
    };

    modalTriggers.forEach((trigger) => {
        trigger.setAttribute('aria-haspopup', 'dialog');
        trigger.setAttribute('aria-controls', modalId);
        trigger.addEventListener('click', () => openModal(trigger));
    });

    modal.querySelectorAll('[data-ui-modal-close]').forEach((closeButton) => {
        closeButton.addEventListener('click', closeModal);
    });

    modal.addEventListener('keydown', (event) => {
        if (event.key === 'Escape') {
            event.preventDefault();
            closeModal();

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

    modalForms.forEach((form) => {
        form.addEventListener('submit', () => {
            const submit = form.querySelector('[data-ui-modal-submit]');
            const label = form.querySelector('[data-ui-modal-label]');
            const loading = form.querySelector('[data-ui-modal-loading]');

            if (!submit) {
                return;
            }

            submit.disabled = true;
            submit.setAttribute('aria-busy', 'true');
            label?.classList.add('hidden');
            loading?.classList.remove('hidden');
        });
    });

    if (modal.dataset.autoOpen === 'true') {
        openModal(modalTriggers[0] || null);
    }
});

const submitFeedbackForms = [...document.querySelectorAll('[data-submit-feedback]')];

submitFeedbackForms.forEach((form) => {
    form.addEventListener('submit', () => {
        const submit = form.querySelector('[data-submit-button], [data-ui-modal-submit]');
        const label = form.querySelector('[data-submit-label], [data-ui-modal-label]');
        const loading = form.querySelector('[data-submit-loading], [data-ui-modal-loading]');

        if (!submit) {
            return;
        }

        submit.disabled = true;
        submit.setAttribute('aria-busy', 'true');
        label?.classList.add('hidden');
        loading?.classList.remove('hidden');
    });
});

const makeFieldKey = (value) => {
    let key = String(value || '')
        .normalize('NFKD')
        .replace(/[\u0300-\u036f]/g, '')
        .toLowerCase()
        .trim()
        .replace(/[\s-]+/g, '_')
        .replace(/[^a-z0-9_]/g, '')
        .replace(/_+/g, '_')
        .replace(/^_+|_+$/g, '');

    if (key === '') {
        return '';
    }

    if (!/^[a-z]/.test(key)) {
        key = `field_${key}`;
    }

    return key;
};

const formBuilders = [...document.querySelectorAll('[data-field-builder]')];

formBuilders.forEach((form) => {
    const labelInput = form.querySelector('[data-field-label-input]');
    const keyInput = form.querySelector('[data-field-key-input]');
    const typeInput = form.querySelector('[data-field-type-select]');
    const optionsPanel = form.querySelector('[data-field-options-panel]');
    const optionsInput = form.querySelector('[data-field-options-input]');

    const syncFieldKey = () => {
        if (labelInput && keyInput) {
            keyInput.value = makeFieldKey(labelInput.value);
        }
    };

    const syncOptionsPanel = () => {
        if (!typeInput || !optionsPanel || !optionsInput) {
            return;
        }

        const isChoiceField = ['select', 'multiselect'].includes(typeInput.value);
        optionsPanel.classList.toggle('hidden', !isChoiceField);
        optionsPanel.setAttribute('aria-hidden', String(!isChoiceField));
        optionsInput.disabled = !isChoiceField;
    };

    labelInput?.addEventListener('input', syncFieldKey);
    typeInput?.addEventListener('change', syncOptionsPanel);
    syncFieldKey();
    syncOptionsPanel();
});

const serviceFieldBuilders = [...document.querySelectorAll('[data-service-field-builder]')];

serviceFieldBuilders.forEach((form) => {
    const fieldList = form.querySelector('[data-service-field-list]');
    const fieldTemplate = form.querySelector('[data-service-field-template]');
    const addButton = form.querySelector('[data-service-field-add]');

    if (!fieldList || !fieldTemplate || !addButton) {
        return;
    }

    let nextIndex = fieldList.querySelectorAll('[data-service-field-row]').length;

    const updateFieldNumbers = () => {
        fieldList.querySelectorAll('[data-service-field-number]').forEach((number, index) => {
            number.textContent = String(index + 1);
        });
    };

    const bindFieldRow = (row) => {
        const labelInput = row.querySelector('[data-service-field-label]');
        const keyInput = row.querySelector('[data-service-field-key]');
        const typeInput = row.querySelector('[data-service-field-type]');
        const optionsPanel = row.querySelector('[data-service-field-options-panel]');
        const optionsInput = row.querySelector('[data-service-field-options]');
        const removeButton = row.querySelector('[data-service-field-remove]');

        const syncFieldKey = () => {
            if (labelInput && keyInput) {
                keyInput.value = makeFieldKey(labelInput.value);
            }
        };

        const syncOptionsPanel = () => {
            if (!typeInput || !optionsPanel || !optionsInput) {
                return;
            }

            const isChoiceField = ['select', 'multiselect'].includes(typeInput.value);
            optionsPanel.classList.toggle('hidden', !isChoiceField);
            optionsPanel.setAttribute('aria-hidden', String(!isChoiceField));
            optionsInput.disabled = !isChoiceField;
        };

        labelInput?.addEventListener('input', syncFieldKey);
        typeInput?.addEventListener('change', syncOptionsPanel);
        removeButton?.addEventListener('click', () => {
            row.remove();
            updateFieldNumbers();
        });
        syncFieldKey();
        syncOptionsPanel();
    };

    fieldList.querySelectorAll('[data-service-field-row]').forEach(bindFieldRow);
    updateFieldNumbers();

    addButton.addEventListener('click', () => {
        const index = nextIndex;
        nextIndex += 1;
        const markup = fieldTemplate.innerHTML.replaceAll('__INDEX__', String(index));
        fieldList.insertAdjacentHTML('beforeend', markup);
        const newRow = fieldList.lastElementChild;

        if (newRow) {
            bindFieldRow(newRow);
            updateFieldNumbers();
            newRow.querySelector('[data-service-field-label]')?.focus();
        }
    });
});

const skillPickers = [...document.querySelectorAll('[data-skill-picker]')];

skillPickers.forEach((picker) => {
    const summary = picker.querySelector('[data-skill-picker-summary]');
    const inputs = [...picker.querySelectorAll('input[type="checkbox"]')];

    if (!summary || inputs.length === 0) {
        return;
    }

    const syncSummary = () => {
        const selected = inputs.filter((input) => input.checked);
        summary.textContent = selected.length === 0
            ? 'Belum ada keahlian dipilih'
            : `${selected.length} keahlian dipilih`;
    };

    inputs.forEach((input) => input.addEventListener('change', syncSummary));
    syncSummary();
});

const userForms = [...document.querySelectorAll('[data-user-form]')];

userForms.forEach((form) => {
    const roleInputs = [...form.querySelectorAll('[data-user-role]')];
    const skillsPanel = form.querySelector('[data-user-skills]');
    const skillInputs = [...form.querySelectorAll('[data-user-skill]')];
    const teamInput = form.querySelector('[data-user-team-input]');
    const teamPositionPanel = form.querySelector('[data-user-team-position]');
    const teamPositionInput = form.querySelector('[data-user-team-position-input]');

    if (roleInputs.length === 0 || !skillsPanel) {
        return;
    }

    const updateUserRoleFields = () => {
        const hasSelectedTeam = Boolean(teamInput?.value);
        const hasSelectedRole = roleInputs.some((input) => input.checked);
        const hasTechnicianRole = roleInputs
            .some((input) => input.checked && input.dataset.roleSlug === 'agen_tier_2');

        roleInputs.forEach((input, index) => {
            input.required = index === 0 && !hasSelectedRole;
        });

        skillsPanel.classList.toggle('hidden', !hasTechnicianRole);
        const hasSelectedSkill = skillInputs.some((input) => input.checked);

        skillInputs.forEach((input, index) => {
            input.disabled = !hasTechnicianRole;
            input.required = hasTechnicianRole && index === 0 && !hasSelectedSkill;
        });

        teamPositionPanel?.classList.toggle('hidden', !hasSelectedTeam);
        if (teamPositionInput) {
            teamPositionInput.disabled = !hasSelectedTeam;
            teamPositionInput.required = hasSelectedTeam;

            if (!hasSelectedTeam) {
                teamPositionInput.value = '';
            }
        }

        if (!hasTechnicianRole) {
            skillInputs.forEach((input) => {
                input.checked = false;
            });
        }
    };

    roleInputs.forEach((input) => input.addEventListener('change', updateUserRoleFields));
    skillInputs.forEach((input) => input.addEventListener('change', updateUserRoleFields));
    teamInput?.addEventListener('change', updateUserRoleFields);
    form.addEventListener('reset', () => window.setTimeout(updateUserRoleFields, 0));
    form.addEventListener('submit', () => {
        const submit = form.querySelector('[data-user-form-submit]');
        const label = form.querySelector('[data-ui-modal-label]');
        const loading = form.querySelector('[data-ui-modal-loading]');

        if (!submit) {
            return;
        }

        submit.disabled = true;
        submit.setAttribute('aria-busy', 'true');
        label?.classList.add('hidden');
        loading?.classList.remove('hidden');
    });
    updateUserRoleFields();
});

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

const queueClaimForms = document.querySelectorAll('[data-queue-claim]');

queueClaimForms.forEach((claimForm) => {
    claimForm.addEventListener('submit', () => {
        const button = claimForm.querySelector('[data-queue-claim-button]');
        const label = claimForm.querySelector('[data-queue-claim-label]');
        const loading = claimForm.querySelector('[data-queue-claim-loading]');

        if (!button) {
            return;
        }

        button.disabled = true;
        button.setAttribute('aria-busy', 'true');
        label?.classList.add('hidden');
        loading?.classList.remove('hidden');
    });
});

const triageForms = document.querySelectorAll('[data-ticket-triage-form]');

triageForms.forEach((triageForm) => {
    const outcomeInputs = [...triageForm.querySelectorAll('[data-ticket-triage-outcome]')];
    const panels = [...triageForm.querySelectorAll('[data-ticket-triage-panel]')];
    const assigneeSelect = triageForm.querySelector('[data-ticket-assignee-select]');
    const rejectionReason = triageForm.querySelector('[name="rejection_reason"]');
    const submitButton = triageForm.querySelector('[data-ticket-triage-submit]');
    const submitLabel = triageForm.querySelector('[data-ticket-triage-submit-label]');
    const submitLoading = triageForm.querySelector('[data-ticket-triage-submit-loading]');

    const selectedOutcome = () => outcomeInputs.find((input) => input.checked)?.value || 'self';

    const updateTriagePanels = () => {
        const outcome = selectedOutcome();

        panels.forEach((panel) => {
            const active = panel.dataset.ticketTriagePanel === outcome;
            panel.hidden = !active;
            panel.setAttribute('aria-hidden', String(!active));
            panel.querySelectorAll('input, select, textarea').forEach((input) => {
                input.disabled = !active;
            });
        });

        if (assigneeSelect) {
            assigneeSelect.required = outcome === 'tier_2';
        }

        if (rejectionReason) {
            rejectionReason.required = outcome === 'reject';
        }

    };

    outcomeInputs.forEach((input) => input.addEventListener('change', updateTriagePanels));

    triageForm.addEventListener('submit', async (event) => {
        if (selectedOutcome() === 'reject' && triageForm.dataset.swalConfirmed !== 'true') {
            if (triageForm.dataset.swalPending === 'true') {
                event.preventDefault();

                return;
            }

            event.preventDefault();
            triageForm.dataset.swalPending = 'true';

            try {
                const result = await showSwalConfirmation('Tolak tiket ini? Alasan penolakan akan terlihat oleh Pemohon dan tiket menjadi final.');

                if (result.isConfirmed) {
                    triageForm.dataset.swalConfirmed = 'true';
                    requestFormSubmit(triageForm);
                }
            } finally {
                delete triageForm.dataset.swalPending;
            }

            return;
        }

        if (triageForm.dataset.swalConfirmed === 'true') {
            delete triageForm.dataset.swalConfirmed;
        }

        if (submitButton) {
            submitButton.disabled = true;
            submitButton.setAttribute('aria-busy', 'true');
            submitLabel?.classList.add('hidden');
            submitLoading?.classList.remove('hidden');
        }
    });

    updateTriagePanels();
});

const assignmentForms = document.querySelectorAll('[data-ticket-assignment-form]');

assignmentForms.forEach((assignmentForm) => {
    assignmentForm.addEventListener('submit', () => {
        const button = assignmentForm.querySelector('[data-ticket-assignment-submit]');
        const label = assignmentForm.querySelector('[data-ticket-assignment-submit-label]');
        const loading = assignmentForm.querySelector('[data-ticket-assignment-submit-loading]');

        if (!button) {
            return;
        }

        button.disabled = true;
        button.setAttribute('aria-busy', 'true');
        label?.classList.add('hidden');
        loading?.classList.remove('hidden');
    });
});

const returnForms = document.querySelectorAll('[data-ticket-return-form]');

returnForms.forEach((returnForm) => {
    returnForm.addEventListener('submit', () => {
        const button = returnForm.querySelector('[data-ticket-return-submit]');

        if (!button) {
            return;
        }

        button.disabled = true;
        button.setAttribute('aria-busy', 'true');
    });
});

const communicationForms = document.querySelectorAll('[data-ticket-communication-form]');

communicationForms.forEach((communicationForm) => {
    communicationForm.addEventListener('submit', () => {
        const button = communicationForm.querySelector('[data-ticket-communication-submit]');

        if (!button) {
            return;
        }

        button.disabled = true;
        button.setAttribute('aria-busy', 'true');
        button.dataset.originalLabel = button.textContent;
        button.textContent = 'Menyimpan…';
    });
});

const internalFieldForms = document.querySelectorAll('[data-ticket-internal-fields-form]');

internalFieldForms.forEach((form) => {
    form.addEventListener('submit', () => {
        const button = form.querySelector('[data-ticket-internal-fields-submit]');
        const label = form.querySelector('[data-ticket-internal-fields-label]');
        const loading = form.querySelector('[data-ticket-internal-fields-loading]');

        if (!button) {
            return;
        }

        button.disabled = true;
        button.setAttribute('aria-busy', 'true');
        label?.classList.add('hidden');
        loading?.classList.remove('hidden');
    });
});

const approvalForms = document.querySelectorAll('[data-approval-request-form], [data-approval-decision-form]');

approvalForms.forEach((approvalForm) => {
    approvalForm.addEventListener('submit', () => {
        const button = approvalForm.querySelector('[data-approval-request-submit], [data-approval-submit]');

        if (!button) {
            return;
        }

        button.disabled = true;
        button.setAttribute('aria-busy', 'true');
        button.dataset.originalLabel = button.textContent;
        button.textContent = 'Memproses...';
    });
});

const ticketAttachmentForms = document.querySelectorAll('[data-ticket-attachment-form], [data-ticket-database-change-form]');

ticketAttachmentForms.forEach((form) => {
    form.addEventListener('submit', (event) => {
        if (form.dataset.ticketDatabaseChangeForm !== undefined && event.defaultPrevented) {
            return;
        }

        const button = form.querySelector('button[type="submit"]');

        if (!button) {
            return;
        }

        button.disabled = true;
        button.setAttribute('aria-busy', 'true');
        button.dataset.originalLabel = button.textContent;
        button.textContent = 'Menyimpan...';
    });
});

const reportFilterForms = document.querySelectorAll('[data-report-filter-form]');

reportFilterForms.forEach((form) => {
    form.addEventListener('submit', () => {
        const button = form.querySelector('[data-report-filter-submit]');
        const label = form.querySelector('[data-report-filter-label]');
        const loading = form.querySelector('[data-report-filter-loading]');

        if (!button) {
            return;
        }

        button.disabled = true;
        button.setAttribute('aria-busy', 'true');
        label?.classList.add('hidden');
        loading?.classList.remove('hidden');
    });
});

const reportExportForms = document.querySelectorAll('[data-report-export-form]');

const downloadReportFile = async (form, button, label, loading, errorMessage) => {
    const url = new URL(form.action, window.location.href);

    for (const [key, value] of new FormData(form).entries()) {
        url.searchParams.append(key, value);
    }

    try {
        const response = await fetch(url, {
            credentials: 'same-origin',
            headers: {
                Accept: 'application/octet-stream, application/pdf',
            },
        });

        if (!response.ok) {
            throw new Error(`Export failed with status ${response.status}`);
        }

        const content = await response.blob();
        const disposition = response.headers.get('Content-Disposition') || '';
        const fileNameMatch = disposition.match(/filename\*=UTF-8''([^;]+)|filename="([^"]+)"|filename=([^;]+)/i);
        const fallbackFileName = form.action.includes('/pdf')
            ? 'laporan-tiket-bulanan.pdf'
            : 'laporan-tiket-bulanan.xlsx';
        const fileName = fileNameMatch
            ? decodeURIComponent((fileNameMatch[1] || fileNameMatch[2] || fileNameMatch[3]).trim())
            : fallbackFileName;
        const downloadUrl = URL.createObjectURL(content);
        const link = document.createElement('a');

        link.href = downloadUrl;
        link.download = fileName;
        link.style.display = 'none';
        document.body.appendChild(link);
        link.click();
        link.remove();
        window.setTimeout(() => URL.revokeObjectURL(downloadUrl), 1000);
    } catch (error) {
        errorMessage?.classList.remove('hidden');
    } finally {
        button.disabled = false;
        button.setAttribute('aria-busy', 'false');
        label?.classList.remove('hidden');
        loading?.classList.add('hidden');
    }
};

reportExportForms.forEach((form) => {
    form.addEventListener('submit', (event) => {
        event.preventDefault();

        const button = form.querySelector('[data-report-export-submit]');
        const label = form.querySelector('[data-report-export-label]');
        const loading = form.querySelector('[data-report-export-loading]');
        const errorMessage = form.querySelector('[data-report-export-error]');

        if (!button || button.disabled) {
            return;
        }

        button.disabled = true;
        button.setAttribute('aria-busy', 'true');
        label?.classList.add('hidden');
        loading?.classList.remove('hidden');
        errorMessage?.classList.add('hidden');

        void downloadReportFile(form, button, label, loading, errorMessage);
    });
});

const brandingForm = document.querySelector('[data-branding-form]');

if (brandingForm) {
    const logoInput = brandingForm.querySelector('[data-branding-logo-input]') || brandingForm.querySelector('#branding-logo');
    const previewImage = brandingForm.querySelector('[data-branding-preview-image]');
    const previewFallback = brandingForm.querySelector('[data-branding-preview-fallback]');
    const fileName = brandingForm.querySelector('[data-branding-file-name]');
    const submitButton = brandingForm.querySelector('[data-branding-submit]');
    const submitLabel = brandingForm.querySelector('[data-branding-submit-label]');
    const submitLoading = brandingForm.querySelector('[data-branding-submit-loading]');

    logoInput?.addEventListener('change', () => {
        const file = logoInput.files?.[0];

        if (!file) {
            if (fileName) {
                fileName.textContent = 'Belum ada file baru yang dipilih.';
            }

            return;
        }

        if (fileName) {
            fileName.textContent = `${file.name} · ${(file.size / 1024 / 1024).toFixed(2)} MB`;
        }

        if (previewImage) {
            previewImage.src = URL.createObjectURL(file);
            previewImage.alt = `Pratinjau ${file.name}`;
            previewImage.classList.remove('hidden');
            previewFallback?.classList.add('hidden');
        }
    });

    brandingForm.addEventListener('submit', () => {
        if (!submitButton) {
            return;
        }

        submitButton.disabled = true;
        submitButton.setAttribute('aria-busy', 'true');
        submitLabel?.classList.add('hidden');
        submitLoading?.classList.remove('hidden');
    });
}
