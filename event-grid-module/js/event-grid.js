document.addEventListener('DOMContentLoaded', () => {
    const loadMoreButton = document.querySelector('.load-more-button');

    if (loadMoreButton) {
        loadMoreButton.addEventListener('click', () => {
            const offset = parseInt(loadMoreButton.dataset.offset, 10);
            const increment = parseInt(loadMoreButton.dataset.increment, 10);
            const ajaxUrl = loadMoreButton.dataset.ajaxUrl;

            // Désactiver le bouton pendant la requête
            loadMoreButton.disabled = true;
            loadMoreButton.textContent = 'Chargement...';

            // Envoyer la requête AJAX
            fetch(ajaxUrl, {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: new URLSearchParams({
                    action: 'load_more_events',
                    nonce: loadMoreButton.dataset.nonce,
                    offset: offset,
                    limit: increment
                })
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Ajouter les nouveaux événements
                        const grid = document.querySelector('.event-grid');
                        grid.insertAdjacentHTML('beforeend', data.data);

                        // Mettre à jour l'offset
                        loadMoreButton.dataset.offset = offset + increment;

                        // Réactiver le bouton
                        loadMoreButton.disabled = false;
                        loadMoreButton.textContent = 'Charger plus';
                    } else {
                        loadMoreButton.textContent = 'Plus rien à charger';
                    }
                })
                .catch(error => {
                    console.error('Erreur AJAX :', error);
                    loadMoreButton.textContent = 'Erreur, réessayez';
                });
        });
    }
});