document.addEventListener("DOMContentLoaded", function() {
    console.log("navbarEffects.js loaded");
    document.querySelectorAll('.navbar-nav .nav-link').forEach(anchor => {
        anchor.addEventListener('click', function(e) {
            console.log("Link clicked:", this.getAttribute('href'));
        });
    });
});