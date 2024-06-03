document.addEventListener('DOMContentLoaded', function () {
    let lieuField = document.getElementById('crea_sortie_form_lieu'); // Remplacez 'sortieForm_lieu' par l'ID réel de votre champ Lieu

    lieuField.addEventListener('change', function () {
        let lieuId = this.value;
        let selectedLieu = lieuxData.find(function (lieu) {
            return lieu.id == lieuId;
        });
        document.getElementById('lieuVille').textContent = 'Ville : ' + selectedLieu.ville;
        document.getElementById('lieuRue').textContent = 'Rue : ' + selectedLieu.rue;
        document.getElementById('lieuCodePostal').textContent = 'Code Postal : ' + selectedLieu.codePostal;
    });

    // Script pour transférer le contenu du motif d'annulation dans le champ caché
    document.querySelectorAll('.modal').forEach(modal => {
        modal.addEventListener('shown.bs.modal', function (event) {
            const button = event.relatedTarget;
            const textarea = modal.querySelector('textarea');
            const hiddenInput = modal.querySelector('input[type="hidden"]');
            const submitButton = modal.querySelector('form button[type="submit"]');

            if (submitButton && textarea && hiddenInput) {
                submitButton.addEventListener('click', function () {
                    hiddenInput.value = textarea.value;
                });
            }
        });
    });


});