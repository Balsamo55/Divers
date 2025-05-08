document.addEventListener('DOMContentLoaded', function () {
    const loadMoreButton = document.querySelector('.load-more-button');
    
    if (!loadMoreButton) return; // Si le bouton n'existe pas, ne rien faire.

    loadMoreButton.addEventListener('click', function () {
        const increment = parseInt(loadMoreButton.getAttribute('data-increment')) || 3; // Nombre d'éléments à charger
        const offset = parseInt(loadMoreButton.getAttribute('data-offset')) || 0; // Décalage actuel
        const grid = document.querySelector('.event-grid');
        const ajaxUrl = loadMoreButton.getAttribute('data-ajax-url'); // URL AJAX WordPress

        // Ajouter un indicateur de chargement
        loadMoreButton.disabled = true;
        loadMoreButton.textContent = 'Chargement...';

        fetch(`${ajaxUrl}?action=load_more_events&increment=${increment}&offset=${offset}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Ajouter les nouveaux événements à la grille
                    grid.insertAdjacentHTML('beforeend', data.events_html);

                    // Mettre à jour l'offset
                    loadMoreButton.setAttribute('data-offset', offset + increment);

                    // Si plus d'événements à charger, masquer le bouton
                    if (!data.has_more_events) {
                        loadMoreButton.style.display = 'none';
                    }

                    // Réactiver le bouton
                    loadMoreButton.disabled = false;
                    loadMoreButton.textContent = 'Charger plus';
                } else {
                    console.error('Erreur lors du chargement des événements :', data.error);
                    loadMoreButton.textContent = 'Erreur. Réessayez.';
                }
            })
            .catch(error => {
                console.error('Erreur réseau ou serveur :', error);
                loadMoreButton.textContent = 'Erreur. Réessayez.';
            });
    });
});