document.addEventListener('DOMContentLoaded', () => {
  console.log('▶ load-more.js chargé');

  const gridContainer = document.querySelector('.event-grid');
  const btn = document.querySelector('.load-more-button');
  if (!btn) {
    console.warn('—> Aucun bouton `.load-more-button` détecté');
    return;
  }

  // Fallback des variables AJAX passées via wp_localize_script
  const globalAjaxUrl = window.EventGridData && EventGridData.ajaxUrl;
  const globalNonce   = window.EventGridData && EventGridData.nonce;

  btn.addEventListener('click', async (event) => {
    event.preventDefault();

    const page     = parseInt(btn.dataset.page, 10)     || 1;
    const maxPages = parseInt(btn.dataset.maxPages, 10) || 1;
    const ajaxUrl  = btn.dataset.ajaxUrl || globalAjaxUrl;
    const nonce    = btn.dataset.nonce   || globalNonce;

    console.log('👆 Clic Load More', { page, maxPages, ajaxUrl, nonce });

    // Indicateur de chargement
    btn.textContent = 'Chargement…';
    btn.disabled    = true;

    try {
      // Construction du payload
      const params = new URLSearchParams();
      params.append('action', 'load_more_events');
      params.append('page', page);
      if (nonce) params.append('nonce', nonce);

      // Requête AJAX
      const response = await fetch(ajaxUrl, {
        method: 'POST',
        credentials: 'same-origin',
        headers: {
          'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8'
        },
        body: params.toString()
      });

      console.log('🚀 Réponse reçue', response);
      const data = await response.json();
      console.log('📦 Contenu AJAX', data);

      if (!data.success) {
        throw new Error(data.data || 'Erreur AJAX côté serveur');
      }

      // Injection du HTML renvoyé
      gridContainer.insertAdjacentHTML('beforeend', data.data.html);

      // Mise à jour ou suppression du bouton
      const nextPage = data.data.next_page;
      if (nextPage <= data.data.max_pages) {
        btn.dataset.page = nextPage;
        btn.textContent  = 'Charger plus';
        btn.disabled     = false;
      } else {
        console.log('✅ Plus de pages, suppression du bouton');
        btn.remove();
      }
    } catch (err) {
      console.error('❌ Load More Error:', err);
      btn.textContent = 'Erreur, réessayer';
      btn.disabled    = false;
    }
  });
});
