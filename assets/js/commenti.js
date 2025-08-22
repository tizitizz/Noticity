document.addEventListener('DOMContentLoaded', () => {
  const isMobile = window.innerWidth <= 768;

  // Gestione dei commenti
  document.querySelectorAll('.btn-toggle-commenti').forEach(button => {
    if (button) { // Verifica che il pulsante esista
      button.addEventListener('click', () => {
        const postId = button.getAttribute('data-post-id');
        const commentSection = document.querySelector(`.comment-section[data-post-id="${postId}"]`);

        if (isMobile) {
          apriCommentiMobile(postId, commentSection);
        } else {
          apriCommentiDesktop(postId, commentSection);
        }
      });
    }
  });

  // Gestione dei commenti Desktop
  function apriCommentiDesktop(postId, container) {
    if (container.style.display === 'none' || container.style.display === '') {
      caricaCommenti(postId);
      container.style.display = 'block';
    } else {
      container.style.display = 'none';
    }
  }

  // Gestione dei commenti Mobile
  function apriCommentiMobile(postId, section) {
    caricaCommenti(postId);
    let overlay = document.getElementById('commenti-overlay');

    if (!overlay) {
      overlay = document.createElement('div');
      overlay.id = 'commenti-overlay';
      overlay.innerHTML = `
        <div class="commenti-mobile-inner">
          <span class="chiudi-commenti">✕</span>
          <ul class="we-comet commenti-container" id="commenti-${postId}"></ul>
          <li class="post-comment">
            <div class="comet-avatar">
              <img src="assets/img/utenti/default.jpg" alt="avatar">
            </div>
            <div class="post-comt-box">
              <form class="form-commento" data-post-id="${postId}">
                <textarea placeholder="Scrivi un commento..." name="commento" required></textarea>
                <button type="submit"><i class="icofont-long-arrow-up"></i></button> <!-- Freccia verso l'alto -->
              </form>
            </div>
          </li>
        </div>
      `;
      document.body.appendChild(overlay);

      document.querySelector('.chiudi-commenti').addEventListener('click', () => {
        overlay.remove();
      });
    }
  }

  // Funzione per caricare i commenti
  function caricaCommenti(postId) {
    fetch(`get_commenti.php?post_id=${postId}`)
      .then(response => response.text())
      .then(html => {
        const container = document.getElementById(`commenti-${postId}`);
        if (container) container.innerHTML = html;
      });
  }

  // Gestione invio commento
  document.body.addEventListener('submit', e => {
    if (e.target.matches('.form-commento')) {
      e.preventDefault();
      const form = e.target;
      const postId = form.getAttribute('data-post-id');
      const textarea = form.querySelector('textarea');
      const testo = textarea.value.trim();

      if (testo.length === 0) return;

      const formData = new FormData();
      formData.append('post_id', postId);
      formData.append('commento', testo);

      fetch('aggiungi_commento.php', {
        method: 'POST',
        body: formData
      })
      .then(r => r.text())
      .then(() => {
        textarea.value = ''; // Resetta il campo di input
        caricaCommenti(postId); // Ricarica i commenti
      });
    }
  });
});
