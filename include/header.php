<!DOCTYPE html>
<html lang="it">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <!-- Includiamo i fogli di stile di Bootstrap per la gestione rapida dell'interfaccia e del responsive -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <title>Orderly</title>
</head>

<body>
  <script>
    // Funzione globale che cambia il tema (chiaro/scuro)
    // Si trova qui in header per essere immediatamente disponibile su tutte le pagine
    function toggleTheme() {
      const isDark = document.body.getAttribute('data-theme') === 'dark';
      const newTheme = isDark ? 'light' : 'dark';

      // Imposta un attributo data-theme che modifica i colori via CSS variables
      document.body.setAttribute('data-theme', newTheme);

      // Sincronizza l'icona del pulsante (icona del sole in modalità scura, luna in modalità chiara)
      document.querySelectorAll('[id="theme-icon"]').forEach(icon => {
        icon.classList.replace(isDark ? 'fa-sun' : 'fa-moon', isDark ? 'fa-moon' : 'fa-sun');
      });

      // Salva permanentemente la scelta nel browser dell'utente
      localStorage.setItem('theme', newTheme);
    }

    // Auto-eseguente: applica il tema scuro istantaneamente se era stato salvato in precedenza
    // Blocca il caricamento della pagina finché non applica per evitare il fastidioso "flicker" bianco
    (function () {
      if (localStorage.getItem('theme') === 'dark') {
        document.body.setAttribute('data-theme', 'dark');
      }
    })();
  </script>