document.addEventListener("DOMContentLoaded", function() {
    function detectDeviceType() {
        const userAgent = navigator.userAgent.toLowerCase();
        if (userAgent.match(/mobile/i)) {
            document.body.classList.add('is-mobile');
        } else if (userAgent.match(/tablet|ipad|android/i)) {
            document.body.classList.add('is-tablet');
        } else {
            document.body.classList.add('is-desktop');
        }
    }
    detectDeviceType();
});
