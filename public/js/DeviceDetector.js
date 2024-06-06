document.addEventListener("DOMContentLoaded", function() {
    var isMobile = /iPhone|iPad|iPod|Android/i.test(navigator.userAgent);
    if (isMobile) {
        var btnDevice = document.getElementById('btn-device');
        if (btnDevice) {
            btnDevice.disabled = true;
            btnDevice.classList.add('disabled-button'); // Ajoute la classe CSS
            btnDevice.title = "Ce bouton est désactivé sur les smartphones.";
        }
    }
});
