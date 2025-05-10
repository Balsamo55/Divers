document.addEventListener('DOMContentLoaded', () => {
  const gridContainer = document.querySelector('.event-grid');

  // Écouteur unique sur le document (ou body)
  document.body.addEventListener('click', event => {
    if (!event.target.classList.contains('pagination-link')) return;
    event.preventDefault();

    const link    = event.target;
    const paged   = link.dataset.page;
    const ajaxUrl = link.dataset.ajaxUrl;
    const nonce   = link.dataset.nonce;

    // Supprime l'ancienne barre de pagination
    const old = document.querySelector('.pagination');
    if (old) old.remove();

    // Envoi AJAX
    fetch(ajaxUrl, {
      method: 'POST',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
      body: new URLSearchParams({
        action: 'pagination_events',
        paged: paged,
        nonce: nonce
      })
    })
    .then(res => res.json())
    .then(data => {
      if (data.success) {
        // Injecte la grille + la pagination
        gridContainer.innerHTML = data.data.html + data.data.pagination;
      } else {
        console.error('Erreur AJAX :', data.data);
      }
    })
    .catch(err => console.error('Erreur AJAX :', err));
  });
});
