<?php
// ============================================================
// UNA PAGINA STATICA, RESA DAL PHP VERO — L.U.X. VERSE
// ============================================================
// Chiamato da tools/build_static.php, una volta per pagina e per lingua.
//
// Perche' un processo per pagina: ogni pagina ha le sue variabili, la sua
// sessione e i suoi require. Renderizzandole tutte nello stesso processo, la
// seconda erediterebbe i pezzi della prima (titolo, descrizione, lingua) e
// nessuno se ne accorgerebbe finche' un cliente non vede il titolo sbagliato.
//
// Come funziona: si finge di essere il server (indirizzo, percorso, lingua) e
// si include la pagina vera. La pagina stampa il suo HTML, e la riscrittura
// dei link (lux_fix_links, agganciata a lux_header) manda ogni href al posto
// giusto: parte statica o parte dinamica.
//
// Uso (lo fa build_static.php, non serve a mano):
//   php tools/_static_one.php <cartella-del-sito> privacy.php it > privacy.html
//
// La cartella del sito arriva dall'esterno perche' questo file vive in due
// disposizioni diverse: nel repository pubblico sta in <repo>/tools e le
// pagine in <repo>/site, mentre nella cartella di lavoro sta dentro il sito.
// Indovinarla qui dentro funzionava in un caso solo.
// ============================================================

if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit("Solo da riga di comando.\n");
}

$root = rtrim((string)($argv[1] ?? ''), '/\\');
$page = (string)($argv[2] ?? '');
$lang = (($argv[3] ?? 'it') === 'en') ? 'en' : 'it';

if ($root === '' || $page === '' || !is_file($root . '/' . $page)) {
    fwrite(STDERR, "Pagina non trovata: " . $page . " (cartella: " . $root . ")\n");
    exit(2);
}

// Da qui in poi il codice del sito sa di essere in esportazione: cambia gli
// indirizzi (assoluti invece che relativi) e non carica le cose che hanno
// bisogno del server (Spazio personale, guida, service worker).
define('LUX_EXPORT', true);
// Serve al cambio lingua: senza il nome della pagina, un link "?lang=en" non
// si puo' trasformare nella copia giusta (en/privacy.php e non en/faq.php).
define('LUX_EXPORT_PAGE', $page);

$host = getenv('LUX_EXPORT_HOST') ?: 'luxverse.c22.eu';
$_SERVER['HTTP_HOST']      = $host;
$_SERVER['SERVER_NAME']    = $host;
$_SERVER['SERVER_PORT']    = '443';
$_SERVER['HTTPS']          = 'on';
$_SERVER['REQUEST_METHOD'] = 'GET';
$_SERVER['REMOTE_ADDR']    = '127.0.0.1';
$_SERVER['SCRIPT_NAME']    = '/' . $page;
$_SERVER['PHP_SELF']       = '/' . $page;
$_SERVER['REQUEST_URI']    = '/' . $page . ($lang === 'en' ? '?lang=en' : '');
$_GET     = ['lang' => $lang];
$_REQUEST = $_GET;
$_POST    = [];
$_COOKIE  = [];

// La sessione non serve qui: si evita almeno che mandi intestazioni di cache.
// (Non si spegne il cookie: il codice del sito lo configura all'avvio, e
// spegnerlo faceva scrivere un avviso in piu' a ogni pagina.)
ini_set('session.cache_limiter', '');
ini_set('display_errors', 'stderr');   // gli errori vanno a parte, non nella pagina

chdir($root);
require $root . '/' . $page;
