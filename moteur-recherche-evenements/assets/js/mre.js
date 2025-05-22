document.addEventListener('DOMContentLoaded', function () {
  console.log("mre.js chargé");

  // Fonction de normalisation (accents + casse)
  const normalize = str =>
    str.normalize("NFD").replace(/\p{Diacritic}/gu, "").toLowerCase();

  // ---------- Autocomplétion pour VILLE ----------
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

  // ---------- Autocomplétion pour CATÉGORIE ----------
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

  // ---------- Fermer les suggestions en cliquant ailleurs ----------
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

  // ---------- Bouton de réinitialisation ----------
  const resetBtn = document.getElementById('mre-reset');
  if (resetBtn && form) {
    resetBtn.addEventListener('click', function (e) {
      e.preventDefault(); // évite le reset natif du navigateur
      form.reset();

      // Vide les suggestions
      if (villeBox) villeBox.innerHTML = '';
      if (catBox) catBox.innerHTML = '';

      const outputTarget = form.dataset.output || '#mre-resultats';

      // Recharge les événements par défaut (date >= aujourd'hui)
      fetch(mre_ajax.url, {
        method: 'POST',
        body: new URLSearchParams({
          action: 'mre_filtrer_evenements'
        })
      })
      .then(response => response.text())
      .then(html => {
        document.querySelector(outputTarget).innerHTML = html;
      })
      .catch(err => console.error("Erreur AJAX réinitialisation :", err));
    });
  }

  if (typeof flatpickr !== 'undefined') {
  flatpickr("#date_debut", {
    dateFormat: "Y-m-d",
    locale: "fr"
  });
  flatpickr("#date_fin", {
    dateFormat: "Y-m-d",
    locale: "fr"
  });
}
document.getElementById('mre-reset')?.addEventListener('click', function (e) {
  setTimeout(() => {
    fetch(mre_ajax.url, {
      method: 'POST',
      body: new URLSearchParams({
        action: 'mre_filtrer_evenements',
        reset: 'true'
      })
    })
    .then(res => res.text())
    .then(html => {
      document.querySelector('#mre-resultats').innerHTML = html;
    });
  }, 100); // Attendre que le formulaire soit vidé
});

});
