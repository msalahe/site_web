/**
 * Gestion des types de projet
 * Gère les opérations CRUD via AJAX
 */

class TypeManagement {
    constructor() {
        this.selectedTypeId = null;
        this.init();
    }

    init() {
        this.initDataTable();
        this.bindEvents();
    }

    initDataTable() {
        if ($.fn.DataTable.isDataTable('#example')) {
            $('#example').DataTable().destroy();
        }
        $('#example').DataTable({
            language: {
                url: '//cdn.datatables.net/plug-ins/1.13.4/i18n/fr-FR.json'
            }
        });
    }

    bindEvents() {
        // Ouvrir modal de création
        $('.add-service').on('click', () => this.openCreateModal());

        // Gestion de la suppression
        $(document).on('click', '.delete-btn', (e) => {
            const button = $(e.currentTarget);
            this.selectedTypeId = button.data('type-id');
            console.log('Type ID sélectionné pour suppression:', this.selectedTypeId);
        });

        $('#confirmDeleteBtn').on('click', () => this.deleteType());
    }

    /**
     * Ouvre le modal de création
     */
    openCreateModal() {
        if ($('#add-service').length > 0) {
            $('#add-service').modal('show');
            return;
        }

        const url = $('#add-service-url').val();
        this.loadModal(url, 'add-service');
    }

    /**
     * Ouvre le modal d'édition
     */
    openEditModal(typeId) {
        const url = $('#edit-service-url').val().replace('__ID__', typeId);
        this.loadModal(url, `edit-service-${typeId}`);
    }

    /**
     * Charge un modal via AJAX
     */
    loadModal(url, modalId) {
        $.get(url)
            .done((response) => {
                const data = typeof response === 'string' ? JSON.parse(response) : response;
                $('body').append(data.message);
                $(`#${modalId}`).modal('show');
            })
            .fail(() => {
                this.showError('Erreur lors du chargement du formulaire');
            });
    }

    /**
     * Soumet le formulaire de création
     */
    submitCreate(event) {
        event.preventDefault();

        this.clearErrors();
        const formData = this.getFormData();

        if (!formData.type_projet) {
            this.showFormError('Veuillez remplir tous les champs du formulaire');
            return;
        }

        this.toggleLoading(true);

        const createUrl = $('#create-url').val();
        console.log('Creating type with URL:', createUrl, 'Data:', formData);

        $.post(createUrl, { service: formData })
            .done((response) => this.handleCreateResponse(response))
            .fail((xhr) => {
                console.error('Error creating type:', xhr);
                this.toggleLoading(false);
                this.handleError();
            });
    }

    /**
     * Soumet le formulaire d'édition
     */
    submitEdit(event, typeId) {
        event.preventDefault();

        this.clearErrors(typeId);
        const formData = { ...this.getFormData(), idType: typeId };

        if (!formData.type_projet) {
            this.showFormError('Veuillez remplir tous les champs du formulaire', typeId);
            return;
        }

        this.toggleLoading(true, typeId);

        const updateUrl = $('#update-url').val();
        console.log('Editing type with URL:', updateUrl, 'Data:', formData);

        $.post(updateUrl, { service: formData })
            .done((response) => this.handleEditResponse(response, typeId))
            .fail((xhr) => {
                console.error('Error editing type:', xhr);
                this.toggleLoading(false, typeId);
                this.handleError();
            });
    }

    /**
     * Supprime un type
     */
    deleteType() {
        if (!this.selectedTypeId) {
            console.error('Aucun type sélectionné');
            return;
        }

        $('#confirmDeleteBtn').hide();
        $('#loading-spinner').show();

        $.ajax({
            url: `/type/delete/${this.selectedTypeId}`,
            method: 'POST',
            dataType: 'json'
        })
        .done((response) => {
            if (response.success) {
                $(`#type-row-${this.selectedTypeId}`).fadeOut(400, function() {
                    $(this).remove();
                });
                this.showSuccess(response.message);
            } else {
                this.showError(response.message);
            }
        })
        .fail(() => {
            this.showError('Une erreur est survenue lors de la suppression');
        })
        .always(() => {
            $('#confirmDeleteBtn').show();
            $('#loading-spinner').hide();
            $('#ModalSuppression').modal('hide');
        });
    }

    /**
     * Gère la réponse de création
     */
    handleCreateResponse(response) {
        const data = typeof response === 'string' ? JSON.parse(response) : response;

        if (data.success || data.code === 200) {
            this.toggleLoading(false);
            $('#add-service').modal('hide').remove();
            this.showSuccess('Le type du projet a été créé avec succès');
            setTimeout(() => location.reload(), 1000);
        } else {
            this.toggleLoading(false);
            this.showFormError(data.message);
        }
    }

    /**
     * Gère la réponse d'édition
     */
    handleEditResponse(response, typeId) {
        const data = typeof response === 'string' ? JSON.parse(response) : response;

        if (data.success || data.code === 200) {
            this.toggleLoading(false, typeId);
            $(`#edit-service-${typeId}`).modal('hide').remove();
            this.showSuccess('Le type du projet a été modifié avec succès');
            setTimeout(() => location.reload(), 1000);
        } else {
            this.toggleLoading(false, typeId);
            this.showFormError(data.message, typeId);
        }
    }

    /**
     * Récupère les données du formulaire
     */
    getFormData() {
        return {
            type_projet: $('#type_projet').val()
        };
    }

    /**
     * Affiche/masque le loading
     */
    toggleLoading(show, typeId = null) {
        const suffix = typeId ? `-${typeId}` : '';
        const buttonId = typeId ? `#modal_button_edit${typeId}` : '#modal_button';
        const loaderId = typeId ? `#footer-load-${typeId}` : '#footer-load';

        if (show) {
            $(buttonId).hide();
            $(loaderId).show();
        } else {
            $(buttonId).show();
            $(loaderId).hide();
        }
    }

    /**
     * Efface les messages d'erreur
     */
    clearErrors(typeId = null) {
        const errorId = typeId ? `#service-error-${typeId}` : '#service-error';
        $(errorId).remove();
    }

    /**
     * Affiche une erreur dans le formulaire
     */
    showFormError(message, typeId = null) {
        const bodyId = typeId ? `#modal-body-${typeId}` : '#modal-body';
        const errorId = typeId ? `service-error-${typeId}` : 'service-error';

        $(bodyId).append(`
            <div class="alert alert-danger mt-2" role="alert" id="${errorId}">
                ${message}
            </div>
        `);
    }

    /**
     * Affiche un message de succès
     */
    showSuccess(message) {
        if (typeof success_noti === 'function') {
            success_noti(message);
        } else {
            alert(message);
        }
    }

    /**
     * Affiche un message d'erreur
     */
    showError(message) {
        if (typeof error_noti === 'function') {
            error_noti(message);
        } else {
            alert(message);
        }
    }

    /**
     * Gère les erreurs AJAX
     */
    handleError() {
        this.showError('Une erreur est survenue. Veuillez réessayer.');
    }
}

// Initialisation globale
let typeManagement;

// Fonctions globales pour compatibilité avec les templates existants
window.submitService = function(event) {
    event.preventDefault();
    if (typeManagement) {
        typeManagement.submitCreate(event);
    }
    return false;
};

window.editType = function(event, idType) {
    event.preventDefault();
    if (typeManagement) {
        typeManagement.submitEdit(event, idType);
    }
    return false;
};

window.edit = function(idType) {
    if (typeManagement) {
        typeManagement.openEditModal(idType);
    }
};

// Initialisation au chargement du DOM
$(document).ready(function() {
    typeManagement = new TypeManagement();
});
