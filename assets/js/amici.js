/*! amici.js — modulo Amici Noticity (robusto a noConflict e load ritardato) */
(function bootstrapAmici() {
  // Funzione per gestire lo stato dei filtri
  function markFiltersActive(active) {
    $('#btn-clear-filters')
      .toggleClass('d-none', !active)
      .data('active', !!active);
  }

  // Attendi jQuery se non è ancora pronto (max ~5s)
  var tries = 0, maxTries = 100, interval = 50;
  var timer = setInterval(function () {
    if (window.jQuery) {
      clearInterval(timer);
      initAmici(window.jQuery);
    } else if (++tries >= maxTries) {
      console.error('[amici.js] jQuery non trovato. Controlla l’ordine degli script.');
      clearInterval(timer);
    }
  }, interval);

  function initAmici($) {
    // Endpoint assoluto corretto (ignora <base> e cartelle)
    const AMICI_API = '/ajax/gestione_amici.php';

    // Usa l’alias $ locale anche se il tema ha usato jQuery.noConflict()
    $(function () {

      function isAmiciPage() {
        return /(^|\/)amici\.php(\?|$)/.test(window.location.pathname + window.location.search);
      }
      if (!isAmiciPage()) return;

      // --- Utility
      function aggiornaContatori() {
        $.post(AMICI_API, { azione: 'contatori' }, function (j) {
          if (!j || !j.success) return;
          $('#badge-amici').text(j.amici);
          $('#badge-suggeriti').text(j.suggeriti);
          $('#badge-richieste').text(j.richieste);
        }, 'json');
      }

      function loadTab(tab, extra) {
        extra = extra || {};
        const data = $.extend({ azione: 'carica_tab', tab: tab, ritorno: 'html' }, extra);
        const target = (tab === 'tab-amici') ? '#amici-list'
                     : (tab === 'tab-suggeriti') ? '#suggeriti-list'
                     : '#richieste-list';
        $(target).html('<div class="text-center py-3 text-muted">Caricamento...</div>');
        $.post(AMICI_API, data, function (html) {
          $(target).html(html);
        }).fail(function () {
          $(target).html('<div class="text-danger">Errore nel caricamento.</div>');
        });
      }

      // Carica la tab Amici all’avvio
      loadTab('tab-amici');

      // Cambio tab
      $('button[data-bs-toggle="tab"]').on('shown.bs.tab', function (e) {
        const tab = $(e.target).data('bs-target').substring(1);
        loadTab(tab);
        $('#search-results').addClass('d-none');
        $('#friends-tabs, #friends-tabs-content').removeClass('d-none');
      });

      // Carica altri (delegato)
      $('body').on('click', '.btn-load-more', function () {
        const next = parseInt($(this).data('next-offset'), 10) || 0;
        const $btn = $(this).prop('disabled', true).text('Carico...');
        $.post(AMICI_API, { azione:'carica_tab', tab:'tab-amici', offset: next, ritorno:'html' }, function (html) {
          $btn.closest('.text-center').remove();
          $('#amici-list').append(html);
        }).fail(function () {
          $btn.prop('disabled', false).text('Carica altri');
        });
      });

      // Invia richiesta
      $('body').on('click', '.btn-invia-richiesta', function() {
        const btn = $(this);
        $.ajax({
          url: AMICI_API,
          type: 'POST',
          data: { azione:'invia_richiesta', destinatario_id: btn.data('destinatario-id') },
          dataType: 'json',
          beforeSend: function(){ btn.prop('disabled', true).text('Invio...'); },
          success: function(r){
            if (r && r.success) {
              btn.removeClass('btn-primary').addClass('btn-secondary').text('Richiesta inviata');
              aggiornaContatori();
            } else {
              alert((r && r.error) ? r.error : 'Errore.');
              btn.prop('disabled', false).text('Aggiungi');
            }
          },
          error: function(){ alert('Errore di rete.'); btn.prop('disabled', false).text('Aggiungi'); }
        });
      });

      // Accetta / Rifiuta richiesta
      $('body').on('click', '.btn-conferma-amicizia', function(){
        const rid = $(this).data('richiesta-id');
        $.post(AMICI_API, { azione:'accetta_richiesta', richiesta_id: rid }, function(r){
          if (r && r.success) {
            loadTab('tab-richieste'); loadTab('tab-amici'); aggiornaContatori();
          } else alert(r.error || 'Errore.');
        }, 'json');
      });
      $('body').on('click', '.btn-rifiuta-amicizia', function(){
        const rid = $(this).data('richiesta-id');
        $.post(AMICI_API, { azione:'rifiuta_richiesta', richiesta_id: rid }, function(r){
          if (r && r.success) { loadTab('tab-richieste'); aggiornaContatori(); }
          else alert(r.error || 'Errore.');
        }, 'json');
      });

      // Rimuovi amico
      $('body').on('click', '.btn-rimuovi-amico', function(){
        const id = $(this).data('amico-id');
        if (!confirm('Vuoi rimuovere questo amico?')) return;
        $.post(AMICI_API, { azione:'rimuovi_amico', amico_id: id }, function(r){
          if (r && r.success) { loadTab('tab-amici'); aggiornaContatori(); }
          else alert(r.error || 'Errore.');
        }, 'json');
      });

      // Suggeriti → refresh
      $('#btn-refresh-suggested').on('click', function(){ loadTab('tab-suggeriti'); });

      // --- Ricerca & Filtri ---
      function showResults() {
        $('#friends-tabs, #friends-tabs-content').addClass('d-none');
        $('#search-results').removeClass('d-none');
      }
      function markFiltersActive(active) {
  $('#btn-clear-filters')
    .toggleClass('d-none', !active)
    .data('active', !!active);
}

      // Apri/chiudi tendina filtro (stile menu post)
      $('#btn-open-filter').on('click', function(e){
        e.stopPropagation();
        $('#friends-filter .menu-dropdown').toggle();
      });
      $(document).on('click', function(){ $('#friends-filter .menu-dropdown').hide(); });
      $('#friends-filter .menu-dropdown').on('click', function(e){ e.stopPropagation(); });

      // Applica / Annulla filtri
      $('#btn-filter-apply').on('click', function(){
        const ambito = $('input[name="ambito_r"]:checked').val() || 'tutti';
        const comune = $('#sel-comune').val();
        const prof   = $('#inp-prof').val().trim();
        $('#f-ambito').val(ambito);
        $('#f-comune').val(comune);
        $('#f-prof').val(prof);
        $('#friends-filter .menu-dropdown').hide();
        markFiltersActive( ambito==='amici' || prof!=='' || (parseInt(comune,10)!==parseInt($('#f-comune').attr('value'),10)) );
        $('#form-ricerca-amici').trigger('submit');
      });
      $('#btn-filter-cancel').on('click', function(){ $('#friends-filter .menu-dropdown').hide(); });

      $('#btn-clear-filters').on('click', function(){
  $('input[name="ambito_r"][value="tutti"]').prop('checked', true);
  $('#sel-comune').val($('#f-comune').attr('value'));
  $('#inp-prof').val('');
  $('#f-ambito').val('tutti');
  $('#f-comune').val($('#f-comune').attr('value'));
  $('#f-prof').val('');
  markFiltersActive(false); // <— stato OFF

   if ($('#ricerca-q').val().trim()==='') {
     $('#search-results').addClass('d-none');
     $('#friends-tabs, #friends-tabs-content').removeClass('d-none');
     return;
   }
   $('#form-ricerca-amici').trigger('submit');
 });

      // Submit ricerca
      $('#form-ricerca-amici').on('submit', function(e){
  e.preventDefault();
  const q   = $('#ricerca-q').val().trim();
  const amb = $('#f-ambito').val();
  const pro = $('#f-prof').val();
  const prof = $('#inp-prof').val().trim();  // Ottieni il valore del campo 'profession' (aggiustato secondo il tuo HTML)
  const ambito = $('input[name="ambito_r"]:checked').val() || 'tutti'; // Ottieni il valore del radio button selezionato (default a 'tutti' se nulla è selezionato)
  
// se i filtri non sono attivi, cerca su tutti i comuni (cid=0)
  const filtersActive = $('#btn-clear-filters').data('active') === true;
  const cid = filtersActive ? $('#f-comune').val() : 0;

  $.post(AMICI_API,
  { azione: 'ricerca', q: q, ambito: ambito, comune_id: cid, professione: prof, ritorno: 'html' },
  function (html) {
    $('.results-list').html(html); // Inserisce i risultati nella lista
    showResults();  // Mostra i risultati
  }
).fail(function () {
  $('.results-list').html('<div class="text-danger">Errore di ricerca.</div>');
  showResults();  // Mostra messaggio di errore nei risultati
});
});

    });
  }
})();
