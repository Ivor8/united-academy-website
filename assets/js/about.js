// assets/js/about.js – optional small interactions for about page
(function() {
    // any about-specific interactivity can go here
    // currently we rely on main.js for language and fades, but we can add more

    // example: team card micro-interaction
    const teamCards = document.querySelectorAll('.team-card');
    teamCards.forEach(card => {
        card.addEventListener('mouseenter', () => {
            // subtle extra shadow handled by CSS
        });
    });

    // partner logos smooth hover
    console.log('About page enhanced');
})();