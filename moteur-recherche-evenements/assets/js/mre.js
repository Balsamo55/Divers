document.addEventListener('DOMContentLoaded', function () {
  console.log("mre.js chargé");

  const normalize = str =>
    str.normalize("NFD").replace(/\p{Diacritic}/gu, "").toLowerCase();

  // ---------- Autocomplétion VILLE ----------
  const villeInput = document.getElementById('ville');
  const villeBox = document.getElementById('ville-suggestions');

  if (villeInput && villeBox && window.mreVilles) {
    villeInput.addEventListener('input', function () {
      const value = this.value;
      villeBox.innerHTML = '';
      if (value.length < 1) return;

      const matches = window.mreVilles.filter(ville =>
        normalize(ville).includes(normalize(value))
      ).slice(0, 10);

      matches.forEach(ville => {
        const li = document.createElement('li');
        li.textContent = ville;
        li.classList.add('mre-suggestion-item');
        li.addEventListener('click', function () {
          villeInput.value = ville;
          villeBox.innerHTML = '';
        });
        villeBox.appendChild(li);
      });
    });
  }

  // ---------- Autocomplétion CATÉGORIE ----------
  const catInput = document.getElementById('categorie');
  const catBox = document.getElementById('categorie-suggestions');

  if (catInput && catBox && window.mreCategories) {
    catInput.addEventListener('input', function () {
      const value = this.value;
      catBox.innerHTML = '';
      if (value.length < 1) return;

      const matches = window.mreCategories.filter(cat =>
        normalize(cat).includes(normalize(value))
      ).slice(0, 10);

      matches.forEach(cat => {
        const li = document.createElement('li');
        li.textContent = cat;
        li.classList.add('mre-suggestion-item');
        li.addEventListener('click', function () {
          catInput.value = cat;
          catBox.innerHTML = '';
        });
        catBox.appendChild(li);
      });
    });
  }

  // ---------- Fermer les suggestions ----------
  document.addEventListener('click', function (e) {
    if (!villeBox.contains(e.target) && e.target !== villeInput) {
      villeBox.innerHTML = '';
    }
    if (!catBox.contains(e.target) && e.target !== catInput) {
      catBox.innerHTML = '';
    }
  });

  // ---------- Formulaire AJAX ----------
  const form = document.getElementById('mre-form');
  if (form) {
    form.addEventListener('submit', function (e) {
      e.preventDefault();
      const formData = new FormData(form);
      const outputTarget = form.dataset.output || '#mre-resultats';

      fetch(mre_ajax.url, {
        method: 'POST',
        body: new URLSearchParams({
          action: 'mre_filtrer_evenements',
          ...Object.fromEntries(formData)
        })
      })
      .then(response => response.text())
      .then(html => {
        document.querySelector(outputTarget).innerHTML = html;
      })
      .catch(err => console.error("Erreur AJAX filtrage :", err));
    });
  }

  // ---------- Bouton reset ----------
  const resetBtn = document.getElementById('mre-reset');
  if (resetBtn && form) {
    resetBtn.addEventListener('click', function (e) {
      e.preventDefault();
      form.reset();
      if (villeBox) villeBox.innerHTML = '';
      if (catBox) catBox.innerHTML = '';
      const outputTarget = form.dataset.output || '#mre-resultats';

      fetch(mre_ajax.url, {
        method: 'POST',
        body: new URLSearchParams({
          action: 'mre_filtrer_evenements',
          reset: 'true'
        })
      })
      .then(res => res.text())
      .then(html => {
        document.querySelector(outputTarget).innerHTML = html;
      })
      .catch(err => console.error("Erreur AJAX reset :", err));
    });
  }

  // ---------- Flatpickr ----------
  if (typeof flatpickr !== 'undefined') {
    flatpickr("#date_debut", { dateFormat: "Y-m-d", locale: "fr" });
    flatpickr("#date_fin", { dateFormat: "Y-m-d", locale: "fr" });
  }

  // ---------- Bouton "Charger plus" ----------
  document.addEventListener('click', function (e) {
    if (e.target && e.target.id === 'mre-load-more') {
      e.preventDefault();
      const button = e.target;
      const nextPage = button.getAttribute('data-page');
      const container = button.closest('#mre-resultats');
      const form = document.querySelector('#mre-form');
      const formData = new FormData(form);
      formData.append('action', 'mre_filtrer_evenements');
      formData.append('paged', nextPage);

      fetch(mre_ajax.url, {
        method: 'POST',
        body: formData
      })
      .then(response => response.text())
      .then(html => {
        const tempDiv = document.createElement('div');
        tempDiv.innerHTML = html;

        const newCards = tempDiv.querySelectorAll('.mre-carte');
        const grid = container.querySelector('.mre-grille');
        newCards.forEach(card => grid.appendChild(card));

        button.parentElement.remove();

        const newButtonWrapper = tempDiv.querySelector('.mre-load-more-wrapper');
        if (newButtonWrapper && grid) {
          container.appendChild(newButtonWrapper); // ✅ Correction ici
        }
      })
      .catch(err => console.error("Erreur AJAX chargement + :", err));
    }
  });

});
