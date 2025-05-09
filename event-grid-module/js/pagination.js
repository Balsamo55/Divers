document.addEventListener('DOMContentLoaded', () => {
    const paginationLinks = document.querySelectorAll('.pagination-link');
    const gridContainer = document.querySelector('.event-grid');

    if (paginationLinks && gridContainer) {
        paginationLinks.forEach(link => {
            link.addEventListener('click', (event) => {
                event.preventDefault();

                const paged = link.dataset.page;
                const ajaxUrl = link.dataset.ajaxUrl;
                const nonce = link.dataset.nonce;

                console.log(`Pagination link clicked. Page: ${paged}`);

                // Désactiver tous les liens pendant le chargement
                paginationLinks.forEach(link => link.classList.add('disabled'));

                // Envoyer la requête AJAX
                fetch(ajaxUrl, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: new URLSearchParams({
                        action: 'pagination_events',
                        nonce: nonce,
                        paged: paged
                    })
                })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            // Mettre à jour la grille
                            console.log('Réponse AJAX reçue avec succès');
                            gridContainer.innerHTML = data.data.html;

                            // Réactiver les liens après le chargement
                            paginationLinks.forEach(link => link.classList.remove('disabled'));
                        } else {
                            console.error('Erreur AJAX :', data.data);
                        }
                    })
                    .catch(error => console.error('Erreur AJAX :', error));
            });
        });
    } else {
        console.error('Pagination links or grid container not found.');
    }
});