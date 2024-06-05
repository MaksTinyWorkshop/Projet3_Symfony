document.addEventListener('DOMContentLoaded', function () {

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