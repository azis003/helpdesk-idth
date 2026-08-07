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
    const categorySelect = triageForm.querySelector('[data-ticket-triage-category]');
    const outcomeInputs = [...triageForm.querySelectorAll('[data-ticket-triage-outcome]')];
    const panels = [...triageForm.querySelectorAll('[data-ticket-triage-panel]')];
    const assigneeSelect = triageForm.querySelector('[data-ticket-assignee-select]');
    const rejectionReason = triageForm.querySelector('[name="rejection_reason"]');
    const suggestionList = triageForm.querySelector('[data-ticket-suggestion-list]');
    const suggestionEmpty = triageForm.querySelector('[data-ticket-suggestion-empty]');
    const suggestionMapElement = triageForm.parentElement?.querySelector('[data-ticket-suggestions-map]');
    const submitButton = triageForm.querySelector('[data-ticket-triage-submit]');
    const submitLabel = triageForm.querySelector('[data-ticket-triage-submit-label]');
    const submitLoading = triageForm.querySelector('[data-ticket-triage-submit-loading]');
    let suggestionMap = {};

    try {
        suggestionMap = JSON.parse(suggestionMapElement?.textContent || '{}');
    } catch {
        suggestionMap = {};
    }

    const selectedOutcome = () => outcomeInputs.find((input) => input.checked)?.value || 'self';

    const renderSuggestions = () => {
        if (!categorySelect || !suggestionList) {
            return;
        }

        const suggestions = Array.isArray(suggestionMap[categorySelect.value])
            ? suggestionMap[categorySelect.value]
            : [];

        suggestionList.replaceChildren();

        suggestions.forEach((suggestion) => {
            const item = document.createElement('li');
            item.className = 'rounded-lg border border-[#dcebef] bg-white px-3 py-2';

            const name = document.createElement('p');
            name.className = 'text-xs font-extrabold text-[#35505b]';
            name.textContent = suggestion.user_name || 'Teknisi tersedia';

            const score = document.createElement('span');
            score.className = 'ml-1 rounded-full bg-[#e8faf4] px-1.5 py-0.5 text-[0.62rem] text-[#087f5b]';
            score.textContent = `${suggestion.match_count || 0} skill`;
            name.append(score);

            const skills = document.createElement('p');
            skills.className = 'mt-1 text-[0.68rem] text-[#78909a]';
            skills.textContent = Array.isArray(suggestion.matching_skill_names)
                ? suggestion.matching_skill_names.join(', ')
                : 'Skill sesuai kategori';

            item.append(name, skills);
            suggestionList.append(item);
        });

        if (suggestionEmpty) {
            suggestionEmpty.classList.toggle('hidden', suggestions.length > 0);
            suggestionEmpty.textContent = categorySelect.value
                ? 'Belum ada teknisi Tier 2 dengan skill yang dipetakan ke kategori ini.'
                : 'Pilih kategori untuk melihat saran teknisi.';
        }
    };

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

        if (categorySelect) {
            categorySelect.required = outcome !== 'reject';
        }

        if (assigneeSelect) {
            assigneeSelect.required = outcome === 'tier_2';
        }

        if (rejectionReason) {
            rejectionReason.required = outcome === 'reject';
        }

        renderSuggestions();
    };

    outcomeInputs.forEach((input) => input.addEventListener('change', updateTriagePanels));
    categorySelect?.addEventListener('change', renderSuggestions);

    triageForm.addEventListener('submit', (event) => {
        if (selectedOutcome() === 'reject' && !window.confirm('Tolak tiket ini? Alasan penolakan akan terlihat oleh Pemohon dan tiket menjadi final.')) {
            event.preventDefault();
            return;
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
