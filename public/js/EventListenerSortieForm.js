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
});