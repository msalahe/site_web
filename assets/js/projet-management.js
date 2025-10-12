/**
 * Gestion des projets
 * Gère les opérations CRUD et upload d'images
 */

class ProjetManagement {
    constructor() {
        this.selectedProjetId = null;
        this.selectedImageId = null;
        this.init();
    }

    init() {
        this.initDataTable();
        this.bindEvents();
        this.initFormValidation();
    }

    initDataTable() {
        if ($.fn.DataTable && $('#example').length) {
            if ($.fn.DataTable.isDataTable('#example')) {
                $('#example').DataTable().destroy();
            }
            $('#example').DataTable({
                language: {
                    url: '//cdn.datatables.net/plug-ins/1.13.4/i18n/fr-FR.json'
                }
            });
        }
    }

    bindEvents() {
        // Gestion suppression projet
        $(document).on('click', '.delete-btn', (e) => {
            this.selectedProjetId = $(e.currentTarget).data('projet-id');
        });

        $('#confirmDeleteBtn').on('click', () => this.deleteProjet());

        // Gestion suppression image
        $(document).on('click', '.delete-icon', (e) => {
            this.selectedImageId = $(e.currentTarget).data('image-id');
            console.log('Image ID sélectionnée:', this.selectedImageId);
        });

        // Bouton de confirmation pour supprimer l'image
        $('#supprimerType').on('click', () => this.deleteImage());
    }

    /**
     * Initialise la validation du formulaire
     */
    initFormValidation() {
        const form = document.querySelector('form[name="projet"]');
        if (!form) return;

        form.addEventListener('submit', (event) => {
            if (!this.validateForm(form)) {
                event.preventDefault();
                event.stopPropagation();
            }
        });

        // Validation en temps réel
        form.querySelectorAll('input, textarea, select').forEach(field => {
            field.addEventListener('blur', () => this.validateField(field));
            field.addEventListener('input', () => {
                if (field.classList.contains('is-invalid')) {
                    this.validateField(field);
                }
            });
        });
    }

    /**
     * Valide un champ
     */
    validateField(field) {
        const isValid = this.isFieldValid(field);

        if (isValid) {
            field.classList.remove('is-invalid');
            field.classList.add('is-valid');
        } else {
            field.classList.remove('is-valid');
            field.classList.add('is-invalid');
        }

        return isValid;
    }

    /**
     * Vérifie si un champ est valide
     */
    isFieldValid(field) {
        if (field.hasAttribute('required') || field.value.trim() !== '') {
            if (field.tagName === 'SELECT') {
                return field.value !== '';
            }
            return field.value.trim() !== '';
        }
        return true;
    }

    /**
     * Valide tout le formulaire
     */
    validateForm(form) {
        let isValid = true;
        let firstErrorField = null;

        // Valider tous les champs requis
        form.querySelectorAll('input[required], textarea[required], select[required]').forEach(field => {
            if (!this.validateField(field)) {
                isValid = false;
                if (!firstErrorField) {
                    firstErrorField = field;
                }
            }
        });

        if (!isValid) {
            if (firstErrorField) {
                firstErrorField.focus();
            }
            this.showError('Veuillez remplir tous les champs obligatoires');
        }

        return isValid;
    }

    /**
     * Supprime un projet
     */
    deleteProjet() {
        if (!this.selectedProjetId) {
            console.error('Aucun projet sélectionné');
            return;
        }

        this.toggleDeleteButton(false);

        fetch(`/delete/projet/${this.selectedProjetId}`, {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Content-Type': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                $(`#projet-${this.selectedProjetId}`).fadeOut(400, function() {
                    $(this).remove();
                });
                this.showSuccess(data.message);
            } else {
                this.showError(data.message);
            }
        })
        .catch(error => {
            console.error('Erreur:', error);
            this.showError('Une erreur est survenue lors de la suppression');
        })
        .finally(() => {
            this.toggleDeleteButton(true);
            this.closeModal('ModalSuppression');
        });
    }

    /**
     * Supprime une image
     */
    deleteImage() {
        if (!this.selectedImageId) {
            console.error('Aucune image sélectionnée');
            return;
        }

        $('#supprimerType').hide();
        $('#footer-affretement-supprimer').show();

        $.ajax({
            url: `/delete/image/${this.selectedImageId}`,
            method: 'POST',
            dataType: 'json'
        })
        .done((response) => {
            if (response.success) {
                $(`.project-image-item[data-image-id="${this.selectedImageId}"]`).fadeOut(400, function() {
                    $(this).remove();
                });
                this.showSuccess(response.message);
            } else {
                this.showError(response.message);
            }
        })
        .fail(() => {
            this.showError('Une erreur est survenue lors de la suppression de l\'image');
        })
        .always(() => {
            $('#supprimerType').show();
            $('#footer-affretement-supprimer').hide();
            $('#ModalSuppression').modal('hide');
        });
    }

    /**
     * Active/désactive le bouton de suppression
     */
    toggleDeleteButton(enabled) {
        const btn = document.getElementById('confirmDeleteBtn');
        const spinner = document.getElementById('loading-spinner');

        if (btn && spinner) {
            btn.style.display = enabled ? 'block' : 'none';
            spinner.style.display = enabled ? 'none' : 'block';
        }
    }

    /**
     * Ferme un modal
     */
    closeModal(modalId) {
        $(`#${modalId}`).modal('hide');
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
}

// Initialisation globale
let projetManagement;

// Initialisation
document.addEventListener('DOMContentLoaded', function() {
    projetManagement = new ProjetManagement();
    console.log('ProjetManagement initialisé');
});
