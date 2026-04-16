document.addEventListener('DOMContentLoaded', function () {
    var activeNav = document.querySelector('.navbar .nav-link.active');
    if (!activeNav) {
        var firstFrontLink = document.querySelector('.navbar .nav-link');
        if (firstFrontLink) {
            firstFrontLink.classList.add('active');
        }
    }
});
