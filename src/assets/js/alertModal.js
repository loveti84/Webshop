// Inject modal HTML
(function initializeAlertModal() {
    if ($('#alertModal').length) {
        return; // Modal already exists
    }

    const modalHTML = `
        <div class="modal fade" id="alertModal" tabindex="-1" aria-labelledby="alertModalTitle" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header" id="alertModalHeader">
                        <h5 class="modal-title" id="alertModalTitle"></h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body" id="alertModalBody"></div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Sluiten</button>
                    </div>
                </div>
            </div>
        </div>
    `;

    $('body').append(modalHTML);
})();

function showAlert(message, type = 'success', callback = null) {
    const $modalHeader = $('#alertModalHeader');
    const $modalTitle = $('#alertModalTitle');
    const $modalBody = $('#alertModalBody');
    const $alertModal = $('#alertModal');

    // Remove previous classes and add new ones
    $modalHeader.attr('class', 'modal-header');

    const configs = {
        success: {
            classes: 'bg-success text-white',
            icon: 'bi-check-circle-fill',
            title: 'Succes'
        },
        error: {
            classes: 'bg-danger text-white',
            icon: 'bi-exclamation-triangle-fill',
            title: 'Fout'
        },
        warning: {
            classes: 'bg-warning text-dark',
            icon: 'bi-exclamation-circle-fill',
            title: 'Waarschuwing'
        },
        info: {
            classes: 'bg-info text-white',
            icon: 'bi-info-circle-fill',
            title: 'Informatie'
        }
    };

    const config = configs[type] || configs.info;

    $modalHeader.addClass(config.classes);
    $modalTitle.html(`<i class="bi ${config.icon} me-2"></i>${config.title}`);
    $modalBody.text(message);

    if (callback) {
        $alertModal.one('hidden.bs.modal', callback);
    }

    // Show modal
    const modal = new bootstrap.Modal($alertModal[0]);
    modal.show();
}
//"Export" showAlert
window.showAlert = showAlert;