// 1. confirmation d'inscription
document.getElementById('signup-link').addEventListener('click', function(event) {
    // Afficher la popup de confirmation
    var userConfirmed = confirm('Êtes-vous sûr de vouloir vous inscrire à cette sortie ?');

    // Si l'utilisateur clique sur "Annuler", empêcher le comportement par défaut du lien
    if (!userConfirmed) {
        event.preventDefault();
    }
});
// 2. confirmation de désistement
document.getElementById('cut-link').addEventListener('click', function(event) {
    // Afficher la popup de confirmation
    var userConfirmed = confirm('Êtes-vous sûr de vouloir vous retirer de cette sortie ?');

    // Si l'utilisateur clique sur "Annuler", empêcher le comportement par défaut du lien
    if (!userConfirmed) {
        event.preventDefault();
    }
});
// 3. confirmation la publication
document.getElementById('publish-link').addEventListener('click', function(event) {
    // Afficher la popup de confirmation
    var userConfirmed = confirm('Êtes-vous sûr de vouloir publier cette sortie ?');

    // Si l'utilisateur clique sur "Annuler", empêcher le comportement par défaut du lien
    if (!userConfirmed) {
        event.preventDefault();
    }
});
// 4. confirmation l'annulation
document.getElementById('cancel-link').addEventListener('click', function(event) {
    // Afficher la popup de confirmation
    var userConfirmed = confirm('Êtes-vous sûr de vouloir annuler cette sortie ?');

    // Si l'utilisateur clique sur "Annuler", empêcher le comportement par défaut du lien
    if (!userConfirmed) {
        event.preventDefault();
    }
});
// 5. confirmation la suppression
document.getElementById('delete-link').addEventListener('click', function(event) {
    // Afficher la popup de confirmation
    var userConfirmed = confirm('Êtes-vous sûr de vouloir supprimer cette sortie ? Cette action est définitive');

    // Si l'utilisateur clique sur "Annuler", empêcher le comportement par défaut du lien
    if (!userConfirmed) {
        event.preventDefault();
    }
});