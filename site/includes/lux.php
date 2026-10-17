<?php
// ============================================================
// L.U.X. VERSE - INTERFACCIA CONDIVISA (UN UNICO FILE)
// ============================================================
// Prima erano quattro file: icons.php, header.php, footer.php e
// app-promo.php. Il contenuto e' lo stesso, ora in un file solo:
//
//   icon(nome, px, classe)   set di icone SVG (zero emoji)
//   lux_header()             <head>, navbar, menu mobile, loader
//   lux_footer()             footer + script + promo app
//
// La pagina imposta le proprie variabili e poi chiama lux_header():
//   $pageTitle, $pageDescription, $pageKeywords, $pageOgImage,
//   $pageOgType, $pageCSS, $extraCSS, $isAppView
// Le variabili vengono lette da $GLOBALS (prima arrivavano per include).
// ============================================================

// ---------- VERSIONE DEGLI ASSET (cache-busting) ----------
// Un solo posto per la versione di CSS e JS: la usano l'header (fogli di
// stile, icone) e il footer (site.js, cookie-consent.js).
//
// Il valore NON si scrive a mano: e' l'impronta del contenuto dei fogli di
// stile e degli script. Si scriveva a mano, e nel rilascio 39.1.1 il numero
// e' rimasto quello di prima mentre CSS e HTML cambiavano: chi aveva gia'
// visitato il sito teneva in cache il foglio di stile VECCHIO e vedeva la
// pagina nuova senza stile (velo del caricamento che non si toglieva,
// riquadro di comando sciolto in fondo alla pagina, schede sovrapposte).
// Da sola, ogni volta che un CSS o un JS cambia, questa impronta cambia e
// il browser e' costretto a riscaricarli: non c'e' piu' niente da ricordare
// a ogni rilascio.
if (!defined('LUX_ASSET_V')) {
    $luxSeed = defined('LUX_SITE_VERSION') ? (string)LUX_SITE_VERSION : '';
    // TUTTO quello che il browser tiene da parte, non solo una parte: prima
    // l'elenco era fatto a mano (site.css, index.css, assets/js/*.js) e
    // lasciava fuori cookie-consent.js e assets/css/style.css. Il 16/10/2026
    // si e' visto il risultato: pubblicato l'avviso cookie nuovo, il sito
    // continuava a servire quello vecchio dalla cache, perche' l'impronta non
    // era cambiata. Ora si prendono tutti i fogli di stile e tutti gli script
    // delle cartelle css/ e js/ (anche annidate), piu' i file JS della radice
    // (cookie-consent.js) e il service worker, che decide cosa tenere in
    // cache senza rete.
    $luxAssets = array_merge(
        (array)glob(__DIR__ . '/../assets/css/*.css'),
        (array)glob(__DIR__ . '/../assets/css/*/*.css'),
        (array)glob(__DIR__ . '/../assets/js/*.js'),
        (array)glob(__DIR__ . '/../assets/js/*/*.js'),
        (array)glob(__DIR__ . '/../*.js'),
        [__DIR__ . '/../assets/css/style.css', __DIR__ . '/../cookie-consent.js', __DIR__ . '/../sw.js']
    );
    $luxAssets = array_values(array_unique($luxAssets));
    sort($luxAssets);   // ordine stabile: l'impronta non cambia da sola
    foreach ($luxAssets as $luxAsset) {
        if (is_file($luxAsset)) $luxSeed .= '|' . md5_file($luxAsset);
    }
    define('LUX_ASSET_V', substr(md5($luxSeed), 0, 10));
}
unset($luxSeed, $luxAssets, $luxAsset);

// ============================================================
// URL DELLE PAGINE STATICHE — il sito diviso in due (17/10/2026)
// ============================================================
// Vedi la nota lunga in config.php (LUX_STATIC_BASE, LUX_STATIC_SELF). In
// breve: alcune pagine sono file .html gia' pronti, allo stesso indirizzo del
// sito, e tutti i link devono puntare al posto giusto senza che ogni pagina
// lo scriva a mano.
//
// Due funzioni e basta:
//   lux_url('/privacy.php')     → l'indirizzo giusto di quella pagina
//   lux_fix_links($html)        → riscrive in un colpo tutti gli href e gli
//                                 src di una pagina intera
//
// lux_fix_links viene agganciata a ogni pagina pubblica con ob_start (vedi
// lux_header): ogni href o src che comincia per / viene mandato al server o
// alla rete statica, secondo la tabella di config.php. In questo modo non
// bisogna ricordarsi di aggiornare i link uno a uno quando una pagina cambia
// posto, e una pagina dimenticata non finisce mai con un link morto.
if (!function_exists('lux_url')) {
    /** L'indirizzo completo di una pagina o di un file, al posto giusto. */
    function lux_url(string $url): string
    {
        $static = defined('LUX_STATIC_PAGES') ? (array)LUX_STATIC_PAGES : [];
        $base   = defined('LUX_STATIC_BASE') ? rtrim((string)LUX_STATIC_BASE, '/') : '';
        $back   = defined('LUX_SITE_URL') ? rtrim((string)LUX_SITE_URL, '/') : '';
        $self   = defined('LUX_STATIC_SELF') ? (bool)LUX_STATIC_SELF : false;
        $export = defined('LUX_EXPORT') && LUX_EXPORT;
        $en     = function_exists('currentLang') && currentLang() === 'en';

        // Solo i percorsi relativi alla radice si riscrivono. Un indirizzo
        // completo resta com'e': e' quello dei canonical e degli hreflang,
        // che devono indicare il sito vero (luxverse.c22.eu) anche nelle
        // copie statiche — cosi' il contenuto ufficiale resta uno solo e la
        // copia veloce non gli fa concorrenza negli indici di ricerca.
        if ($url === '' || $url[0] !== '/' || strpos($url, '//') === 0) return $url;

        $parts = parse_url($url);
        $path  = $parts['path'] ?? '/';
        $file  = basename($path);

        $lang = null;
        if (!empty($parts['query'])) {
            $q = [];
            parse_str((string)$parts['query'], $q);
            if (isset($q['lang'])) $lang = ($q['lang'] === 'en') ? 'en' : 'it';
        }

        // Pagina statica: e' un file .html gia' pronto. L'indirizzo e' lo
        // stesso del sito (LUX_STATIC_SELF): la barra del browser non deve
        // mai cambiare dominio. Le copie in inglese stanno in /en/ (le
        // genera tools/build_static.php), quindi il parametro ?lang non
        // serve piu': senza toglierlo, la stessa pagina due volte.
        if ($base !== '' && in_array($file, $static, true)) {
            $l    = $lang ?? ($en ? 'en' : 'it');
            // File .html e non .php: un .html lo consegna il server cosi'
            // com'e', senza eseguire niente.
            $host = ($self && $back !== '') ? $back : $base;
            $out  = $host . ($l === 'en' ? '/en' : '') . str_replace('.php', '.html', $path);
            if ($lang !== null) {
                $q = [];
                parse_str((string)($parts['query'] ?? ''), $q);
                unset($q['lang']);
                $qs = http_build_query($q);
                if ($qs !== '') $out .= '?' . $qs;
            }
            return $out;
        }

        // I file di lavoro (fogli di stile, script, immagini) stanno nella
        // parte statica: sono loro il peso, e farli arrivare dalla rete e' il
        // guadagno di velocita' piu' grande di tutti.
        //
        // Restano qui, invece, i file che devono per forza venire dallo
        // stesso indirizzo del sito: il service worker (un browser non lo
        // registra da un altro dominio, quindi da li' l'offline non
        // funzionerebbe), il manifest (il suo start_url si risolve rispetto
        // al manifest: da un altro dominio porterebbe fuori strada) e
        // l'avviso sui cookie. L'APK resta qui per lo stesso motivo di
        // sempre: e' un file singolo e pesante, non un asset di pagina.
        if ($base !== '' && strpos($path, '/assets/') === 0) {
            return $base . $path;
        }

        // Pagina dinamica: in esportazione serve l'indirizzo assoluto (la
        // copia statica non sa dove vive il server); nelle pagine vere
        // resta il relativo, che funziona dovunque sia il sito.
        return ($export && $back !== '') ? $back . $url : $url;
    }

    /** L'indirizzo della stessa pagina nell'altra lingua. */
    function lux_lang_url(string $lang): string
    {
        $lang = ($lang === 'en') ? 'en' : 'it';
        $cur  = defined('LUX_EXPORT_PAGE')
            ? (string)LUX_EXPORT_PAGE
            : basename((string)($_SERVER['SCRIPT_NAME'] ?? ''));

        $static = defined('LUX_STATIC_PAGES') ? (array)LUX_STATIC_PAGES : [];
        $base   = defined('LUX_STATIC_BASE') ? rtrim((string)LUX_STATIC_BASE, '/') : '';
        $back   = defined('LUX_SITE_URL') ? rtrim((string)LUX_SITE_URL, '/') : '';
        $self   = defined('LUX_STATIC_SELF') ? (bool)LUX_STATIC_SELF : false;
        $export = defined('LUX_EXPORT') && LUX_EXPORT;

        // Pagina statica: le due lingue sono due file (privacy.html e
        // en/privacy.html), quindi il cambio lingua e' un link, non un
        // parametro da far leggere a un programma che li' non c'e'.
        if ($base !== '' && $cur !== '' && in_array($cur, $static, true)) {
            $host = ($self && $back !== '') ? $back : $base;
            return $host . ($lang === 'en' ? '/en' : '') . '/' . str_replace('.php', '.html', $cur);
        }
        return ($export && $back !== '' ? $back : '') . '/' . $cur . '?lang=' . $lang;
    }

    /**
     * Riscrive tutti gli href/src/action di una pagina.
     * Usata come callback di ob_start: vedi lux_header.
     */
    function lux_fix_links(string $html): string
    {
        if ($html === '') return $html;

        // 1. Il cambio lingua: nel sito e' <a href="?lang=en">, un indirizzo
        // relativo che non comincia per "/": sulla copia statica porterebbe
        // a una pagina che non esiste. Qui diventa il link alla copia giusta.
        if (strpos($html, '?lang=') !== false) {
            $html = (string)preg_replace_callback(
                '/\b(href)\s*=\s*(["\'])\?lang=(en|it)\2/',
                function ($m) { return 'href=' . $m[2] . lux_lang_url($m[3]) . $m[2]; },
                $html
            );
        }

        // 2. Tutti gli indirizzi che partono dalla radice.
        if (strpos($html, '="/') === false) return $html;
        return (string)preg_replace_callback(
            '/\b(href|src|action)\s*=\s*(["\'])(\/[^"\'\s]*)\2/',
            function ($m) { return $m[1] . '=' . $m[2] . lux_url($m[3]) . $m[2]; },
            $html
        );
    }
}

// ---------- ICONE SVG ----------

// ============================================================
// L.U.X. VERSE - ICON SET (SVG inline, zero emoji, zero font-icon)
// GENERATO DA tools/build_icons.py - non modificare a mano.
// Uso PHP:  icon('mail', 18)  |  projectIcon('palette')
// Tutte le icone sono 24x24, stroke, currentColor: si adattano
// a qualsiasi colore/tema e non richiedono file esterni.
// ============================================================

if (!function_exists('lux_icon_paths')) {
    function lux_icon_paths(): array
    {
        return [
            'alert' => '<path d="M10.3 3.9 1.9 18a2 2 0 0 0 1.7 3h16.8a2 2 0 0 0 1.7-3L13.7 3.9a2 2 0 0 0-3.4 0z"/><line x1="12" y1="9" x2="12" y2="13.5"/><line x1="12" y1="17" x2="12.01" y2="17"/>',
            'arrow-down' => '<line x1="12" y1="4" x2="12" y2="19.6"/><polyline points="5.4 13 12 19.6 18.6 13"/>',
            'arrow-left' => '<line x1="20" y1="12" x2="4.4" y2="12"/><polyline points="11 5.4 4.4 12 11 18.6"/>',
            'arrow-right' => '<line x1="4" y1="12" x2="19" y2="12"/><polyline points="13 6 19 12 13 18"/>',
            'arrow-up' => '<line x1="12" y1="20" x2="12" y2="4.4"/><polyline points="5.4 11 12 4.4 18.6 11"/>',
            'arrow-up-right' => '<line x1="7" y1="17" x2="17" y2="7"/><polyline points="8 7 17 7 17 16"/>',
            'ball' => '<circle cx="12" cy="12" r="9.4"/><path d="M12 6.6l3.5 2.6-1.3 4.1h-4.4L8.5 9.2z"/><path d="M12 2.6v4M4.7 9.2h3.8m7 0h3.8M7.2 20.6l3.1-4.5m6.5 4.5-3.1-4.5"/>',
            'bear' => '<circle cx="5.6" cy="6.4" r="2.9"/><circle cx="18.4" cy="6.4" r="2.9"/><path d="M4.6 13.4a7.4 7.4 0 0 1 14.8 0c0 4.2-3.3 7.6-7.4 7.6s-7.4-3.4-7.4-7.6z"/><circle cx="12" cy="16.4" r="2.4"/>',
            'bell' => '<path d="M18 8.6a6 6 0 0 0-12 0c0 5-2.2 6.4-2.2 6.4h16.4S18 13.6 18 8.6z"/><path d="M13.7 18.6a2 2 0 0 1-3.4 0"/>',
            'bird' => '<path d="M4.4 15.4c1.6-3.1 4.7-5.2 8.2-5.2l3-3.6 1 3.1h3.4c-1 5.1-5 8.2-9.6 8.2-3 0-5-1-6-2.5z"/><circle cx="15.6" cy="10.6" r=".9"/><path d="M4.4 15.4 2.4 20"/>',
            'board' => '<rect x="3" y="3" width="18" height="18" rx="2"/><line x1="3" y1="9" x2="21" y2="9"/><line x1="9" y1="21" x2="9" y2="9"/>',
            'bolt' => '<polygon points="13 2 3.5 13.5 11 13.5 10 22 20.5 10 13 10 13 2"/>',
            'book' => '<path d="M2.5 3.5h6a4 4 0 0 1 4 4v13a3 3 0 0 0-3-3h-7z"/><path d="M21.5 3.5h-6a4 4 0 0 0-4 4v13a3 3 0 0 1 3-3h7z"/>',
            'book-open' => '<path d="M12 6.6C10.4 5 8.4 4.4 6 4.4H2.6v14.1H6c2.4 0 4.4.6 6 2.1 1.6-1.5 3.6-2.1 6-2.1h3.4V4.4H18c-2.4 0-4.4.6-6 2.2z"/><line x1="12" y1="6.6" x2="12" y2="20.6"/>',
            'bookmark' => '<path d="M6 3.4h12a1 1 0 0 1 1 1v17L12 16.8 5 21.4v-17a1 1 0 0 1 1-1z"/>',
            'books' => '<path d="M2.5 3.5h6a4 4 0 0 1 4 4v13a3 3 0 0 0-3-3h-7z"/><path d="M21.5 3.5h-6a4 4 0 0 0-4 4v13a3 3 0 0 1 3-3h7z"/>',
            'bot' => '<rect x="4" y="8" width="16" height="12" rx="3"/><line x1="12" y1="4.5" x2="12" y2="8"/><circle cx="12" cy="3" r="1.5"/><line x1="9" y1="13" x2="9.01" y2="13"/><line x1="15" y1="13" x2="15.01" y2="13"/><path d="M9.5 16.5h5"/><line x1="1.5" y1="13" x2="4" y2="13"/><line x1="20" y1="13" x2="22.5" y2="13"/>',
            'bow' => '<circle cx="12" cy="5.6" r="2.6"/><path d="M5 19c2-2.6 4.3-3.8 7-3.8s5 1.2 7 3.8"/><path d="M4 22h16"/>',
            'brain' => '<path d="M12 5.6A3 3 0 0 1 6.6 7 2.6 2.6 0 0 0 5.2 11a2.6 2.6 0 0 0 1 4.4A3 3 0 0 0 12 18.4"/><path d="M12 5.6A3 3 0 0 1 17.4 7a2.6 2.6 0 0 1 1.4 4 2.6 2.6 0 0 1-1 4.4A3 3 0 0 1 12 18.4"/><line x1="12" y1="5.6" x2="12" y2="18.4"/>',
            'brief' => '<rect x="2.5" y="7.5" width="19" height="13" rx="2"/><path d="M8.5 7.5V5.8A1.8 1.8 0 0 1 10.3 4h3.4a1.8 1.8 0 0 1 1.8 1.8V7.5"/><line x1="2.5" y1="12.5" x2="21.5" y2="12.5"/>',
            'bulb' => '<path d="M9.5 18.5h5"/><path d="M10.5 22h3"/><path d="M15.2 14.2c.2-1 .7-1.8 1.5-2.6a4.9 4.9 0 0 0 1.6-3.5 6.2 6.2 0 0 0-12.4 0c0 1 .3 2.3 1.5 3.6a4.8 4.8 0 0 1 1.4 2.5"/>',
            'burst' => '<path d="M12 2.6l1.9 5.1 5.2-2-3.2 4.6 4.6 2.5-5.5.5 1 5.5-4-3.4-4 3.4 1-5.5-5.5-.5 4.6-2.5-3.2-4.6 5.2 2z"/>',
            'calc' => '<rect x="4.5" y="2.5" width="15" height="19" rx="2.5"/><line x1="8.5" y1="6.5" x2="15.5" y2="6.5"/><line x1="8.5" y1="11" x2="8.51" y2="11"/><line x1="12" y1="11" x2="12.01" y2="11"/><line x1="15.5" y1="11" x2="15.51" y2="11"/><line x1="8.5" y1="15" x2="8.51" y2="15"/><line x1="12" y1="15" x2="12.01" y2="15"/><line x1="15.5" y1="15" x2="15.51" y2="15"/><line x1="8.5" y1="19" x2="15.5" y2="19"/>',
            'calendar' => '<rect x="3" y="4.5" width="18" height="17" rx="2"/><line x1="16" y1="2.5" x2="16" y2="6.5"/><line x1="8" y1="2.5" x2="8" y2="6.5"/><line x1="3" y1="10" x2="21" y2="10"/>',
            'camera' => '<path d="M3 8.6a2 2 0 0 1 2-2h2.4l1.5-2.6h6l1.5 2.6H19a2 2 0 0 1 2 2v9.4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><circle cx="12" cy="13" r="3.9"/>',
            'cards' => '<rect x="3" y="4.2" width="11.4" height="15.8" rx="2" transform="rotate(-9 8.7 12.1)"/><rect x="9.6" y="4.2" width="11.4" height="15.8" rx="2" transform="rotate(9 15.3 12.1)"/>',
            'cat' => '<path d="M5.2 4.4 6 11"/><path d="M18.8 4.4 18 11"/><path d="M4.8 9.6a7.2 7.2 0 0 1 14.4 0v3.8a4.2 4.2 0 0 1-4.2 4.2H9a4.2 4.2 0 0 1-4.2-4.2z"/><line x1="9.6" y1="12.2" x2="9.61" y2="12.2"/><line x1="14.4" y1="12.2" x2="14.41" y2="12.2"/>',
            'chart' => '<line x1="3.5" y1="20.6" x2="20.5" y2="20.6"/><rect x="4.6" y="12" width="3.6" height="8.6"/><rect x="10.2" y="7" width="3.6" height="13.6"/><rect x="15.8" y="10" width="3.6" height="10.6"/>',
            'chat' => '<path d="M21 12a8 8 0 0 1-8 8H8l-5 3 1.4-4.2A8 8 0 1 1 21 12z"/>',
            'check' => '<polyline points="20 6.5 9.5 17 4 11.5"/>',
            'check-circle' => '<circle cx="12" cy="12" r="9.5"/><polyline points="7.8 12.4 10.8 15.3 16.2 9.2"/>',
            'chess' => '<circle cx="12" cy="5.8" r="2.9"/><path d="M8.6 16.6c0-3 1.5-5.1 3.4-5.1s3.4 2.1 3.4 5.1z"/><path d="M6.6 20.4h10.8l-2-3.8H8.6z"/>',
            'chevron-down' => '<polyline points="6 9 12 15 18 9"/>',
            'chevron-right' => '<polyline points="9 6 15 12 9 18"/>',
            'clipboard' => '<rect x="5" y="4.2" width="14" height="17" rx="2"/><rect x="9" y="2.4" width="6" height="4" rx="1"/><line x1="9" y1="11.4" x2="15" y2="11.4"/><line x1="9" y1="15.4" x2="13.4" y2="15.4"/>',
            'close' => '<line x1="6" y1="6" x2="18" y2="18"/><line x1="18" y1="6" x2="6" y2="18"/>',
            'close-circle' => '<circle cx="12" cy="12" r="9.5"/><line x1="9" y1="9" x2="15" y2="15"/><line x1="15" y1="9" x2="9" y2="15"/>',
            'cloud' => '<path d="M7 18.6h10a4.4 4.4 0 0 0 .4-8.8 6 6 0 0 0-11.6 1.6A4 4 0 0 0 7 18.6z"/>',
            'code' => '<polyline points="16 18 22 12 16 6"/><polyline points="8 6 2 12 8 18"/>',
            'coffee' => '<path d="M3.6 8.4h13v5a5 5 0 0 1-5 5h-3a5 5 0 0 1-5-5z"/><path d="M16.6 9.9h2.4a2.5 2.5 0 0 1 0 5h-2.4"/><line x1="4" y1="21.4" x2="16" y2="21.4"/>',
            'confetti' => '<path d="M4.4 19.6 8.6 8.4l7.2 7.2z"/><circle cx="16" cy="5" r="1"/><circle cx="20" cy="9" r="1"/><circle cx="12.4" cy="3.4" r="1"/><line x1="19.4" y1="15.4" x2="21.4" y2="17.4"/>',
            'cookie' => '<circle cx="12" cy="12" r="9.4"/><circle cx="9" cy="9.6" r="1.2"/><circle cx="14.6" cy="8.6" r="1.2"/><circle cx="11.4" cy="15" r="1.2"/><circle cx="16" cy="14.6" r="1.2"/>',
            'copy' => '<rect x="9" y="9" width="12" height="12" rx="2"/><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"/>',
            'cpu' => '<rect x="6.4" y="6.4" width="11.2" height="11.2" rx="2"/><rect x="9.6" y="9.6" width="4.8" height="4.8" rx="1"/><line x1="9.6" y1="2.6" x2="9.6" y2="6.4"/><line x1="14.4" y1="2.6" x2="14.4" y2="6.4"/><line x1="9.6" y1="17.6" x2="9.6" y2="21.4"/><line x1="14.4" y1="17.6" x2="14.4" y2="21.4"/><line x1="2.6" y1="9.6" x2="6.4" y2="9.6"/><line x1="17.6" y1="9.6" x2="21.4" y2="9.6"/><line x1="2.6" y1="14.4" x2="6.4" y2="14.4"/><line x1="17.6" y1="14.4" x2="21.4" y2="14.4"/>',
            'crown' => '<path d="M2.5 6.6 6.6 12 12 4.4 17.4 12l4.1-5.4V19a1 1 0 0 1-1 1H3.5a1 1 0 0 1-1-1z"/>',
            'debate' => '<line x1="12" y1="3" x2="12" y2="21"/><line x1="4.5" y1="6.5" x2="19.5" y2="6.5"/><path d="M4.5 6.5 2 13h5z"/><path d="M19.5 6.5 17 13h5z"/><line x1="8" y1="21" x2="16" y2="21"/>',
            'dice' => '<rect x="4" y="4" width="16" height="16" rx="3"/><circle cx="9" cy="9" r="1.3"/><circle cx="15" cy="9" r="1.3"/><circle cx="9" cy="15" r="1.3"/><circle cx="15" cy="15" r="1.3"/>',
            'dish' => '<path d="M6.5 3v7a2 2 0 0 0 4 0V3"/><line x1="8.5" y1="10" x2="8.5" y2="21"/><path d="M17.5 3c-1.2 2-1.8 4-1.8 6 0 1.7.6 2.9 1.8 3.5V21"/><path d="M19.6 3c1.2 2 1.8 4 1.8 6 0 1.7-.6 2.9-1.8 3.5V21"/>',
            'divide' => '<circle cx="12" cy="6.6" r="1.4"/><line x1="5" y1="12" x2="19" y2="12"/><circle cx="12" cy="17.4" r="1.4"/>',
            'dog' => '<path d="M5 5.4c-1.6.6-2.6 2.2-2.6 4.2 0 1.4.6 2.6 1.6 3.4"/><path d="M19 5.4c1.6.6 2.6 2.2 2.6 4.2 0 1.4-.6 2.6-1.6 3.4"/><path d="M12 20.6c-4 0-7.2-3.1-7.2-7s3.2-7 7.2-7 7.2 3.1 7.2 7-3.2 7-7.2 7z"/><line x1="9.6" y1="12.4" x2="9.61" y2="12.4"/><line x1="14.4" y1="12.4" x2="14.41" y2="12.4"/><circle cx="12" cy="16.4" r="2"/>',
            'dot' => '<circle cx="12" cy="12" r="9.5"/><circle cx="12" cy="12" r="5.5"/><circle cx="12" cy="12" r="1.6"/>',
            'dot-blue' => '<circle cx="12" cy="12" r="8.4" fill="#4C8FE2" stroke="none"/>',
            'dot-green' => '<circle cx="12" cy="12" r="8.4" fill="#46B36B" stroke="none"/>',
            'dot-red' => '<circle cx="12" cy="12" r="8.4" fill="#E2574C" stroke="none"/>',
            'dot-yellow' => '<circle cx="12" cy="12" r="8.4" fill="#E2B33C" stroke="none"/>',
            'download' => '<path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/>',
            'droplet' => '<path d="M12 3.6s6 6.2 6 10.2a6 6 0 0 1-12 0c0-4 6-10.2 6-10.2z"/>',
            'egg' => '<path d="M12 3c3.4 0 6 5 6 9a6 6 0 0 1-12 0c0-4 2.6-9 6-9z"/>',
            'external' => '<path d="M18 13.5V19a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h5.5"/><polyline points="14 3 21 3 21 10"/><line x1="10" y1="14" x2="21" y2="3"/>',
            'eye' => '<path d="M2.4 12S6 6.4 12 6.4 21.6 12 21.6 12 18 17.6 12 17.6 2.4 12 2.4 12z"/><circle cx="12" cy="12" r="3"/>',
            'family' => '<circle cx="7" cy="5.4" r="2.5"/><path d="M2.5 20.2v-4a4.5 4.5 0 0 1 9 0v4"/><circle cx="17.2" cy="8.4" r="2"/><path d="M13.6 20.2v-2.9a3.6 3.6 0 0 1 7.2 0v2.9"/>',
            'file' => '<path d="M14 2H6.5A1.5 1.5 0 0 0 5 3.5v17A1.5 1.5 0 0 0 6.5 22h11a1.5 1.5 0 0 0 1.5-1.5V7z"/><polyline points="14 2 14 7 19 7"/><line x1="9" y1="13" x2="15" y2="13"/><line x1="9" y1="17" x2="15" y2="17"/>',
            'film' => '<rect x="2.6" y="8.6" width="18.8" height="12" rx="2"/><path d="M2.6 8.6 4 4.4l17 2-1.4 2.2z"/><line x1="8" y1="6.7" x2="9.4" y2="8.6"/><line x1="13" y1="7.2" x2="14.4" y2="9.1"/>',
            'filter' => '<path d="M3.4 5h17.2l-6.6 8v6.4L10 21v-8z"/>',
            'fist' => '<path d="M5.8 12.6a3 3 0 0 1 3-3h6.4a3 3 0 0 1 3 3v2.6a4.4 4.4 0 0 1-4.4 4.4H10.2a4.4 4.4 0 0 1-4.4-4.4z"/><path d="M8.6 9.6V7.4a1.6 1.6 0 0 1 3.2 0v2.2"/><path d="M11.8 9.6V6.6a1.6 1.6 0 0 1 3.2 0v3"/>',
            'flag' => '<path d="M5.4 21.4V3.6"/><path d="M5.4 4.6h10.4l-1.6 3.8 1.6 3.8H5.4"/>',
            'flame' => '<path d="M12 2.8c2.6 3.2 5.2 5.6 5.2 9.2a5.2 5.2 0 0 1-10.4 0c0-1.8.7-3.2 1.7-4.5.4 1 1 1.8 1.8 2.3-.2-2.7.9-5.2 1.7-7z"/><path d="M12 21.8a2.6 2.6 0 0 0 2.6-2.6c0-1.5-1.2-2.3-2.6-4-1.4 1.7-2.6 2.5-2.6 4A2.6 2.6 0 0 0 12 21.8z"/>',
            'flask' => '<path d="M9 3h6"/><path d="M10 3v6.6L5.6 18a2 2 0 0 0 1.8 3h9.2a2 2 0 0 0 1.8-3L14 9.6V3"/><line x1="7.6" y1="15" x2="16.4" y2="15"/>',
            'folder' => '<path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5.5A1.5 1.5 0 0 1 3.5 4H9l2 2.5h9.5A1.5 1.5 0 0 1 22 8z"/>',
            'fox' => '<path d="M4.4 3.4 7 9.6"/><path d="M19.6 3.4 17 9.6"/><path d="M12 20.6c-4.2 0-7.4-3.4-7.4-7.6S7.8 5.4 12 5.4s7.4 3.4 7.4 7.6-3.2 7.6-7.4 7.6z"/><line x1="9.4" y1="12" x2="9.41" y2="12"/><line x1="14.6" y1="12" x2="14.61" y2="12"/><path d="M10.4 17h3.2"/>',
            'frown' => '<circle cx="12" cy="12" r="9.5"/><path d="M15.6 16.4a4.4 4.4 0 0 0-7.2 0"/><line x1="8.6" y1="9.6" x2="10.6" y2="10.6"/><line x1="15.4" y1="9.6" x2="13.4" y2="10.6"/>',
            'gamepad' => '<line x1="6" y1="11" x2="10" y2="11"/><line x1="8" y1="9" x2="8" y2="13"/><line x1="15" y1="11.5" x2="15.01" y2="11.5"/><line x1="17.5" y1="9.5" x2="17.51" y2="9.5"/><path d="M17.3 5H6.7a4 4 0 0 0-4 3.6C2.6 9.4 2 14.5 2 16a3 3 0 0 0 5 2l1.4-1.4a2 2 0 0 1 1.4-.6h4.4a2 2 0 0 1 1.4.6L17 18a3 3 0 0 0 5-2c0-1.5-.6-6.6-.7-7.4A4 4 0 0 0 17.3 5z"/>',
            'gem' => '<path d="M7 3.2h10l5 5.9-10 11.7L2 9.1z"/><path d="M2 9.1h20"/><path d="M12 20.8 8.5 9.1 12 3.2l3.5 5.9z"/>',
            'gift' => '<polyline points="20 12 20 22 4 22 4 12"/><rect x="2" y="7" width="20" height="5"/><line x1="12" y1="22" x2="12" y2="7"/><path d="M12 7H7.5a2.5 2.5 0 0 1 0-5C11 2 12 7 12 7z"/><path d="M12 7h4.5a2.5 2.5 0 0 0 0-5C13 2 12 7 12 7z"/>',
            'github' => '<path d="M9 19c-5 1.5-5-2.5-7-3m14 6v-3.9a3.4 3.4 0 0 0-.9-2.6c3.1-.4 6.4-1.5 6.4-7A5.4 5.4 0 0 0 20 4.8 5.1 5.1 0 0 0 19.9 1S18.7.7 16 2.5a13.4 13.4 0 0 0-7 0C6.3.7 5.1 1 5.1 1A5.1 5.1 0 0 0 5 4.8a5.4 5.4 0 0 0-1.5 3.7c0 5.4 3.3 6.6 6.4 7A3.4 3.4 0 0 0 9 18.1V22"/>',
            'globe' => '<circle cx="12" cy="12" r="9.5"/><line x1="2.5" y1="12" x2="21.5" y2="12"/><path d="M12 2.5a15 15 0 0 1 4 9.5 15 15 0 0 1-4 9.5 15 15 0 0 1-4-9.5 15 15 0 0 1 4-9.5z"/>',
            'gradcap' => '<path d="M22 9.8 12 4.5 2 9.8l10 5.3z"/><path d="M6 12.4V17c0 1.7 2.7 3 6 3s6-1.3 6-3v-4.6"/><line x1="21.6" y1="10.4" x2="21.6" y2="16"/>',
            'grid' => '<rect x="3" y="3" width="7.5" height="7.5" rx="1.5"/><rect x="13.5" y="3" width="7.5" height="7.5" rx="1.5"/><rect x="3" y="13.5" width="7.5" height="7.5" rx="1.5"/><rect x="13.5" y="13.5" width="7.5" height="7.5" rx="1.5"/>',
            'guitar-note' => '<path d="M9 18V5.4l12-2.5v12.4"/><circle cx="6" cy="18" r="3"/><circle cx="18" cy="15.4" r="3"/>',
            'hands' => '<path d="M8 21c-1.9 0-3-1.4-3-3.4V9"/><path d="M16 21c1.9 0 3-1.4 3-3.4V9"/><path d="M8 9.4V4.6a1.6 1.6 0 0 1 3.2 0v8.6"/><path d="M12.8 13.2V4.6a1.6 1.6 0 0 1 3.2 0v4.8"/>',
            'hash' => '<line x1="4" y1="9" x2="20" y2="9"/><line x1="4" y1="15" x2="20" y2="15"/><line x1="10" y1="3" x2="8" y2="21"/><line x1="16" y1="3" x2="14" y2="21"/>',
            'headset' => '<path d="M4.4 15.4v-3a7.6 7.6 0 0 1 15.2 0v3"/><rect x="2.6" y="14" width="4" height="6" rx="1.6"/><rect x="17.4" y="14" width="4" height="6" rx="1.6"/><path d="M19.4 20v.6a2.4 2.4 0 0 1-2.4 2.4h-2.6"/>',
            'heart' => '<path d="M20.8 5.6a5 5 0 0 0-7.1 0L12 7.3l-1.7-1.7a5 5 0 0 0-7.1 7.1l1.7 1.7L12 21.5l7.1-7.1 1.7-1.7a5 5 0 0 0 0-7.1z"/>',
            'help' => '<circle cx="12" cy="12" r="9.5"/><path d="M9.4 9.3a2.7 2.7 0 0 1 5.3.8c0 2-2.7 2.5-2.7 2.5"/><line x1="12" y1="17" x2="12.01" y2="17"/>',
            'home' => '<path d="M3 9.5 12 2l9 7.5V20a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><path d="M9 22v-8h6v8"/>',
            'infinity' => '<path d="M7.6 8.4c2.6 0 4.4 3.6 4.4 3.6s1.8 3.6 4.4 3.6a3.6 3.6 0 0 0 0-7.2c-2.6 0-4.4 3.6-4.4 3.6S10.2 15.6 7.6 15.6a3.6 3.6 0 0 1 0-7.2z"/>',
            'info' => '<circle cx="12" cy="12" r="9.5"/><line x1="12" y1="16" x2="12" y2="11.5"/><line x1="12" y1="8" x2="12.01" y2="8"/>',
            'invader' => '<path d="M5 10.2a3 3 0 0 1 3-3h8a3 3 0 0 1 3 3v9.4h-3.4v-2.2h-2.2v2.2H8.6v-2.2H6.4v2.2H5z"/><line x1="9.4" y1="11.6" x2="9.41" y2="11.6"/><line x1="14.6" y1="11.6" x2="14.61" y2="11.6"/>',
            'journal' => '<path d="M4 4.5A2.5 2.5 0 0 1 6.5 2h12A1.5 1.5 0 0 1 20 3.5v17a1.5 1.5 0 0 1-1.5 1.5h-12A2.5 2.5 0 0 0 4 19.5z"/><line x1="8" y1="2" x2="8" y2="22"/><line x1="12" y1="7" x2="17" y2="7"/><line x1="12" y1="11" x2="17" y2="11"/>',
            'key' => '<circle cx="7.6" cy="15.6" r="3.6"/><path d="M10.4 13.2 20.4 3.2"/><path d="M17.4 6.2 19.6 8.4"/><path d="M15.4 8.2 17.6 10.4"/>',
            'laptop' => '<rect x="3.6" y="4.6" width="16.8" height="11.2" rx="1.6"/><line x1="2" y1="19" x2="22" y2="19"/>',
            'laugh' => '<circle cx="12" cy="12" r="9.5"/><path d="M7.4 13.9a5.6 5.6 0 0 0 9.2 0z" fill="currentColor" stroke="none"/><line x1="9" y1="9.2" x2="9.01" y2="9.2"/><line x1="15" y1="9.2" x2="15.01" y2="9.2"/>',
            'leaf' => '<path d="M4.4 19.6c0-8 5.6-13.2 15.2-13.2 0 9.6-5.2 15.2-13.2 15.2"/><path d="M4.4 19.6 15 9"/>',
            'link' => '<circle cx="8.5" cy="12" r="5.4"/><circle cx="15.5" cy="12" r="5.4"/>',
            'lock' => '<rect x="3.5" y="10.5" width="17" height="11" rx="2"/><path d="M7.5 10.5V7a4.5 4.5 0 0 1 9 0v3.5"/>',
            'mail' => '<rect x="2" y="4.5" width="20" height="15" rx="2"/><polyline points="2.5 6 12 13 21.5 6"/>',
            'meditate' => '<circle cx="12" cy="5" r="2.3"/><path d="M12 8v5.2"/><path d="M12 13.2c-2.6 0-4.6 2-4.6 4.6h9.2c0-2.6-2-4.6-4.6-4.6z"/><path d="M4.5 20.6h15"/>',
            'megaphone' => '<path d="M3 11v3l13 5V5.5z"/><path d="M16 9.4a4 4 0 0 1 0 6.2"/><path d="M5.6 14.6V19a2 2 0 0 0 4 0v-3.1"/>',
            'menu' => '<line x1="3" y1="7" x2="21" y2="7"/><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="17" x2="21" y2="17"/>',
            'mic' => '<rect x="9" y="2" width="6" height="11" rx="3"/><path d="M19 11v1.5a7 7 0 0 1-14 0V11"/><line x1="12" y1="19.5" x2="12" y2="22.5"/><line x1="8.5" y1="22.5" x2="15.5" y2="22.5"/>',
            'mind' => '<circle cx="18" cy="5.5" r="2.8"/><circle cx="6" cy="12" r="2.8"/><circle cx="18" cy="18.5" r="2.8"/><line x1="8.5" y1="13.3" x2="15.5" y2="17.2"/><line x1="15.5" y1="6.8" x2="8.5" y2="10.7"/>',
            'minus' => '<line x1="5" y1="12" x2="19" y2="12"/>',
            'moon' => '<path d="M20 15.6A8.6 8.6 0 0 1 8.9 4 9.2 9.2 0 1 0 20 15.6z"/>',
            'mouse' => '<circle cx="5.6" cy="8" r="3.2"/><circle cx="18.4" cy="8" r="3.2"/><circle cx="12" cy="14" r="6.4"/><path d="M8.6 20.4c2 1.4 4.8 1.4 6.8 0"/><line x1="9.8" y1="13.4" x2="9.81" y2="13.4"/><line x1="14.2" y1="13.4" x2="14.21" y2="13.4"/>',
            'multiply' => '<line x1="6" y1="6" x2="18" y2="18"/><line x1="18" y1="6" x2="6" y2="18"/>',
            'music' => '<path d="M9 18V5.5l12-2.5v12.5"/><circle cx="6" cy="18" r="3"/><circle cx="18" cy="15.5" r="3"/>',
            'note' => '<path d="M14 2.6H5.6A1.6 1.6 0 0 0 4 4.2v15.6a1.6 1.6 0 0 0 1.6 1.6h12.8a1.6 1.6 0 0 0 1.6-1.6V8.6z"/><polyline points="14 2.6 14 8.6 20 8.6"/><line x1="8" y1="13" x2="16" y2="13"/><line x1="8" y1="17" x2="13" y2="17"/>',
            'orb' => '<path d="M12 2.5 14 9l6.5 2L14 13l-2 6.5L10 13 3.5 11 10 9z"/><path d="M18.5 16.5l.8 2.4 2.4.8-2.4.8-.8 2.4-.8-2.4-2.4-.8 2.4-.8z"/>',
            'orbit' => '<circle cx="12" cy="12" r="2.6"/><ellipse cx="12" cy="12" rx="9.6" ry="4" transform="rotate(-32 12 12)"/><path d="M19.4 4.4l.9 2.1 2.1.9-2.1.9-.9 2.1-.9-2.1-2.1-.9 2.1-.9z"/>',
            'palette' => '<circle cx="13.6" cy="6.6" r="1"/><circle cx="17.4" cy="10.4" r="1"/><circle cx="8.4" cy="7.6" r="1"/><circle cx="6.6" cy="12.6" r="1"/><path d="M12 2.5C6.8 2.5 2.5 6.7 2.5 12s4.3 9.5 9.5 9.5c.9 0 1.6-.7 1.6-1.6 0-.4-.2-.8-.4-1.1-.3-.3-.4-.6-.4-1.1 0-.9.7-1.6 1.6-1.6h1.9c2.9 0 5.3-2.4 5.3-5.3C22 6.3 17.5 2.5 12 2.5z"/>',
            'panda' => '<circle cx="5.4" cy="6.2" r="2.9"/><circle cx="18.6" cy="6.2" r="2.9"/><circle cx="12" cy="13.4" r="7.6"/><ellipse cx="9" cy="11.6" rx="2.1" ry="2.6"/><ellipse cx="15" cy="11.6" rx="2.1" ry="2.6"/><path d="M9.8 17.4a3 3 0 0 0 4.4 0"/>',
            'party' => '<path d="M4 20 9.4 6.6 17.4 14.6z"/><path d="M14.4 4.4l.8 1.8M18.4 6.6l1.8-.8M20.4 10.6l-1.8.6M16.4 2.6l-.6 1.8M21.4 7.4l-1.8.8"/>',
            'pause' => '<line x1="8.4" y1="4.6" x2="8.4" y2="19.4"/><line x1="15.6" y1="4.6" x2="15.6" y2="19.4"/>',
            'paw' => '<circle cx="7.4" cy="8" r="2.1"/><circle cx="12" cy="5.8" r="2.1"/><circle cx="16.6" cy="8" r="2.1"/><path d="M12 11c3 0 5.4 2.1 5.4 4.6S15 20 12 20s-5.4-1.9-5.4-4.4S9 11 12 11z"/>',
            'pen' => '<path d="M12 19.5 19.5 12l2.5 2.5L14.5 22z"/><path d="M18 13 17 5.5 2.5 2.5 5.5 17 13 18z"/><line x1="2.5" y1="2.5" x2="10.5" y2="10.5"/>',
            'phone' => '<path d="M5 3.4h3.6l1.5 4-2.2 1.6a12.4 12.4 0 0 0 6.7 6.7l1.6-2.2 4 1.5v3.6a2 2 0 0 1-2 2C10.6 20.6 3 13 3 5.4a2 2 0 0 1 2-2z"/>',
            'pin' => '<path d="M12 21.4s7-6.2 7-11.2a7 7 0 1 0-14 0c0 5 7 11.2 7 11.2z"/><circle cx="12" cy="10" r="2.6"/>',
            'pin-note' => '<path d="M8.4 3.6h7.2l-3.6 6.4 4 8.4H4.4l4-8.4z"/><line x1="12" y1="18.4" x2="12" y2="21.4"/>',
            'pizza' => '<path d="M12 3 20.2 20.6H3.8z"/><circle cx="10.6" cy="14" r="1.2"/><circle cx="13.4" cy="17" r="1.2"/><path d="M6.6 8.4A9.6 9.6 0 0 1 12 3"/>',
            'play' => '<path d="M7.4 4.6 19 12 7.4 19.4z"/>',
            'plus' => '<line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/>',
            'pointer' => '<path d="M10 3.6v9.8"/><path d="M10 13.4 7.2 11a1.7 1.7 0 0 0-2.4 2.4l5.4 5.4c.8.8 1.9 1.2 3 1.2h3.2c1.4 0 2.5-.9 2.9-2.2l1.3-4.6a1.6 1.6 0 0 0-2-1.9l-3.9.9"/>',
            'pong' => '<line x1="12" y1="2.4" x2="12" y2="21.6"/><rect x="3.6" y="7" width="2.6" height="6" rx="1.2"/><rect x="17.8" y="11" width="2.6" height="6" rx="1.2"/><circle cx="15.4" cy="6.2" r="1.8"/>',
            'pulse' => '<path d="M2.5 12h4l2.2-6 3.4 12 2.4-7 1.8 1h5.2"/>',
            'puzzle' => '<path d="M10 3.2h4a1 1 0 0 1 1 1v2.4h1.1a2.6 2.6 0 0 1 0 5.2H15v6.2a1 1 0 0 1-1 1h-2.6v-1a2.6 2.6 0 0 0-5.2 0v1H3.6a1 1 0 0 1-1-1v-6.6h2.6a2.6 2.6 0 0 0 0-5.2H2.6V6.2a3 3 0 0 1 3-3z"/>',
            'puzzle-piece' => '<path d="M10 3.2h4a1 1 0 0 1 1 1v2.4h1.1a2.6 2.6 0 0 1 0 5.2H15v6.2a1 1 0 0 1-1 1h-2.6v-1a2.6 2.6 0 0 0-5.2 0v1H3.6a1 1 0 0 1-1-1v-6.6h2.6a2.6 2.6 0 0 0 0-5.2H2.6V6.2a3 3 0 0 1 3-3z"/>',
            'quiz' => '<rect x="3" y="3" width="18" height="18" rx="3"/><path d="M9.6 9.6a2.5 2.5 0 0 1 5 .6c0 1.8-2.5 2.3-2.5 2.3"/><line x1="12" y1="16.4" x2="12.01" y2="16.4"/>',
            'rabbit' => '<path d="M8.8 9.4V4.8a1.7 1.7 0 0 1 3.4 0v4.6"/><path d="M13.8 9.4V4.6a1.7 1.7 0 0 1 3.4 0v4.8"/><path d="M12 21c-3.6 0-6.2-2.3-6.2-5.4S8.4 10 12 10s6.2 2.5 6.2 5.6S15.6 21 12 21z"/><line x1="9.8" y1="14.4" x2="9.81" y2="14.4"/><line x1="14.2" y1="14.4" x2="14.21" y2="14.4"/>',
            'rainbow' => '<path d="M2.4 19.4a9.6 9.6 0 0 1 19.2 0"/><path d="M6.4 19.4a5.6 5.6 0 0 1 11.2 0"/><path d="M10.4 19.4a1.6 1.6 0 0 1 3.2 0"/><line x1="12" y1="19.4" x2="12" y2="21.4"/>',
            'refresh' => '<polyline points="22 4 22 10 16 10"/><path d="M20.5 15a9 9 0 1 1-2.6-9.3L22 10"/>',
            'resume' => '<rect x="2.5" y="4" width="19" height="16" rx="2"/><circle cx="9" cy="10.5" r="2.6"/><path d="M4.5 17.5a5.2 5.2 0 0 1 9 0"/><line x1="15" y1="9.5" x2="19" y2="9.5"/><line x1="15" y1="13.5" x2="19" y2="13.5"/>',
            'retry' => '<path d="M3.4 12a8.6 8.6 0 1 1 2.6 6.2"/><polyline points="3 17.6 3.4 12 9 12"/>',
            'rocket' => '<path d="M4.5 16.5c-1.5 1.3-2 5-2 5s3.7-.5 5-2c.7-.8.7-2.1-.1-2.9a2.2 2.2 0 0 0-2.9 0z"/><path d="M12 15l-3-3a22 22 0 0 1 2-4A12.9 12.9 0 0 1 22 2c0 2.7-.8 7.5-6 11a22 22 0 0 1-4 2z"/><path d="M9 12H4s.6-3 2-4c1.6-1.1 5 0 5 0"/><path d="M12 15v5s3-.6 4-2c1.1-1.6 0-5 0-5"/>',
            'ruler' => '<path d="M3.4 16.6 16.6 3.4l4 4-13.2 13.2z"/><line x1="8" y1="9.6" x2="10" y2="11.6"/><line x1="11" y1="6.6" x2="13" y2="8.6"/>',
            'scale' => '<line x1="12" y1="3" x2="12" y2="21.4"/><line x1="5" y1="6.6" x2="19" y2="6.6"/><path d="M5 6.6 2 13h6z"/><path d="M19 6.6 16 13h6z"/><line x1="8.4" y1="21.4" x2="15.6" y2="21.4"/>',
            'scissors' => '<circle cx="6.4" cy="18.4" r="2.6"/><circle cx="17.6" cy="18.4" r="2.6"/><line x1="8.2" y1="16.5" x2="19" y2="3"/><line x1="15.8" y1="16.5" x2="5" y2="3"/>',
            'scroll' => '<path d="M5 3.4h11a2 2 0 0 1 2 2v13.4a2 2 0 0 0 2 2H7a2 2 0 0 1-2-2z"/><path d="M5 3.4a2 2 0 0 0-2 2v1.8h4"/><line x1="9" y1="8.4" x2="15" y2="8.4"/><line x1="9" y1="12.4" x2="15" y2="12.4"/>',
            'search' => '<circle cx="11" cy="11" r="7.5"/><line x1="21" y1="21" x2="16.5" y2="16.5"/>',
            'seedling' => '<path d="M12 21v-8.2"/><path d="M12 12.8c0-3.6-2.6-6.2-6.2-6.2 0 3.6 2.6 6.2 6.2 6.2z"/><path d="M12 12.8c0-3.6 2.6-6.2 6.2-6.2 0 3.6-2.6 6.2-6.2 6.2z"/>',
            'send' => '<line x1="21" y1="3" x2="10" y2="14"/><polygon points="21 3 14.5 21 10 14 3 9.5 21 3"/>',
            'settings' => '<circle cx="12" cy="12" r="3.2"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 1 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 1 1-4 0v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 1 1-2.83-2.83l.06-.06A1.65 1.65 0 0 0 4.6 15a1.65 1.65 0 0 0-1.51-1H3a2 2 0 1 1 0-4h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 1 1 2.83-2.83l.06.06A1.65 1.65 0 0 0 9 4.6 1.65 1.65 0 0 0 10 3.09V3a2 2 0 1 1 4 0v.09A1.65 1.65 0 0 0 15 4.6a1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 1 1 2.83 2.83l-.06.06A1.65 1.65 0 0 0 19.4 9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 1 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1z"/>',
            'shield' => '<path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>',
            'shuffle' => '<polyline points="16 3.4 21 3.4 21 8.4"/><line x1="4" y1="20" x2="21" y2="3.4"/><polyline points="21 15.6 21 20.6 16 20.6"/><line x1="15" y1="15" x2="21" y2="20.6"/><line x1="4" y1="3.8" x2="9" y2="8.8"/>',
            'slots' => '<rect x="3" y="4.5" width="18" height="15" rx="2.5"/><line x1="9" y1="4.5" x2="9" y2="19.5"/><line x1="15" y1="4.5" x2="15" y2="19.5"/><line x1="6" y1="12" x2="6.01" y2="12"/><line x1="12" y1="12" x2="12.01" y2="12"/><line x1="18" y1="12" x2="18.01" y2="12"/>',
            'smartphone' => '<rect x="5.5" y="2" width="13" height="20" rx="2.5"/><line x1="12" y1="18" x2="12.01" y2="18"/>',
            'smile' => '<circle cx="12" cy="12" r="9.5"/><path d="M8 14.2s1.6 2.1 4 2.1 4-2.1 4-2.1"/><line x1="9" y1="9.4" x2="9.01" y2="9.4"/><line x1="15" y1="9.4" x2="15.01" y2="9.4"/>',
            'snake' => '<path d="M4 19h5a3 3 0 0 0 0-6H9a3 3 0 0 1 0-6h4a2.5 2.5 0 0 0 0-5"/><circle cx="6.2" cy="19" r="1.6"/><line x1="15.4" y1="3.4" x2="18" y2="2"/>',
            'sofa' => '<path d="M4.4 11V8.4a2.4 2.4 0 0 1 2.4-2.4h10.4a2.4 2.4 0 0 1 2.4 2.4V11"/><path d="M2.6 11.4h18.8v5.8a1.4 1.4 0 0 1-1.4 1.4H4a1.4 1.4 0 0 1-1.4-1.4z"/><line x1="6.6" y1="18.6" x2="6.6" y2="21.4"/><line x1="17.4" y1="18.6" x2="17.4" y2="21.4"/>',
            'sparkles' => '<path d="M12 3l1.9 5.6L19.5 10l-5.6 1.9L12 17.5l-1.9-5.6L4.5 10l5.6-1.4z"/>',
            'speaker' => '<polygon points="11 5 6 9 2 9 2 15 6 15 11 19 11 5"/><path d="M15.5 8.5a5 5 0 0 1 0 7"/><path d="M18.5 5.5a9 9 0 0 1 0 13"/>',
            'spider' => '<circle cx="12" cy="13.4" r="3.4"/><circle cx="12" cy="8.6" r="2"/><line x1="8.6" y1="11.6" x2="4.2" y2="8.6"/><line x1="8.6" y1="13.6" x2="3.4" y2="13.4"/><line x1="8.6" y1="15.6" x2="4.2" y2="18.6"/><line x1="15.4" y1="11.6" x2="19.8" y2="8.6"/><line x1="15.4" y1="13.6" x2="20.6" y2="13.4"/><line x1="15.4" y1="15.6" x2="19.8" y2="18.6"/>',
            'spiral' => '<path d="M12 21a9 9 0 1 1 9-9 6 6 0 0 1-6 6 3 3 0 0 1-3-3 1.5 1.5 0 0 1 3 0"/>',
            'star' => '<path d="M12 2.5l3 6.1 6.7 1-4.9 4.7 1.2 6.7L12 17.8l-6 3.2 1.2-6.7L2.3 9.6l6.7-1z"/>',
            'star-solid' => '<path d="M12 2.6l3 6.1 6.6 1-4.8 4.7 1.2 6.7-6-3.2-6 3.2 1.2-6.7L2.4 9.7l6.6-1z" fill="currentColor" stroke="none"/>',
            'stop' => '<rect x="5.6" y="5.6" width="12.8" height="12.8" rx="2"/>',
            'strength' => '<path d="M6.5 8.5h11"/><path d="M6.5 15.5h11"/><line x1="3" y1="10.4" x2="3" y2="13.6"/><line x1="21" y1="10.4" x2="21" y2="13.6"/><line x1="6.5" y1="7.5" x2="6.5" y2="16.5"/><line x1="17.5" y1="7.5" x2="17.5" y2="16.5"/>',
            'suit-club' => '<circle cx="12" cy="8.4" r="3.4"/><circle cx="7.6" cy="15.4" r="3.4"/><circle cx="16.4" cy="15.4" r="3.4"/><path d="M12 11.8v9.6"/><path d="M9.4 21.4h5.2"/>',
            'suit-diamond' => '<path d="M12 3.2 17.6 12 12 20.8 6.4 12z" fill="#E2574C" stroke="#E2574C"/>',
            'suit-heart' => '<path d="M12 20.6 4.8 13.4a4.6 4.6 0 0 1 7.2-5.6 4.6 4.6 0 0 1 7.2 5.6z" fill="#E2574C" stroke="#E2574C"/>',
            'suit-spade' => '<path d="M12 3.4c4.2 4.2 7.6 6.6 7.6 10.6a4.6 4.6 0 0 1-7.6 3.6 4.6 4.6 0 0 1-7.6-3.6C4.4 10 7.8 7.6 12 3.4z"/><path d="M12 17.6v3.8"/><path d="M9.4 21.4h5.2"/>',
            'sun' => '<circle cx="12" cy="12" r="4.4"/><line x1="12" y1="2" x2="12" y2="4.4"/><line x1="12" y1="19.6" x2="12" y2="22"/><line x1="2" y1="12" x2="4.4" y2="12"/><line x1="19.6" y1="12" x2="22" y2="12"/><line x1="5.1" y1="5.1" x2="6.8" y2="6.8"/><line x1="17.2" y1="17.2" x2="18.9" y2="18.9"/><line x1="5.1" y1="18.9" x2="6.8" y2="17.2"/><line x1="17.2" y1="6.8" x2="18.9" y2="5.1"/>',
            'tag' => '<path d="M3 12.4V3.6a.6.6 0 0 1 .6-.6h8.8L21 11.6 12.4 20.2z"/><circle cx="7.8" cy="7.8" r="1.5"/>',
            'target' => '<circle cx="12" cy="12" r="9.4"/><circle cx="12" cy="12" r="5.4"/><circle cx="12" cy="12" r="1.5"/>',
            'ticket' => '<path d="M3 8.6a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2v1.9a2 2 0 0 0 0 3.8v1.9a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-1.9a2 2 0 0 0 0-3.8z"/><line x1="12" y1="7" x2="12" y2="17.6" stroke-dasharray="2 2"/>',
            'tile' => '<rect x="4" y="4" width="16" height="16" rx="2"/><line x1="4" y1="4" x2="20" y2="20"/><line x1="20" y1="4" x2="4" y2="20"/>',
            'tile-empty' => '<rect x="3.5" y="3.5" width="17" height="17" rx="2.5"/>',
            'tile-filled' => '<rect x="3.5" y="3.5" width="17" height="17" rx="2.5" fill="currentColor" stroke="none"/>',
            'tiktok' => '<path d="M14.2 15.4V3.6"/><path d="M14.2 4.2c.4 3.4 3 5.7 6.2 5.9"/><circle cx="9.9" cy="15.4" r="4.3"/>',
            'time' => '<circle cx="12" cy="12" r="9.5"/><polyline points="12 6.5 12 12 16 14"/>',
            'timer' => '<circle cx="12" cy="13" r="8.5"/><line x1="12" y1="13" x2="15.5" y2="9.5"/><line x1="9.5" y1="1.5" x2="14.5" y2="1.5"/><line x1="12" y1="1.5" x2="12" y2="4.5"/>',
            'tools' => '<path d="M14.6 6.6a4 4 0 0 1 5.2 5.2l-8.8 8.8a2.5 2.5 0 0 1-3.6-3.6z"/><path d="M6.6 3.4 10 6.8l-2 2-3.4-3.4z"/>',
            'translate' => '<path d="M4 5h9"/><path d="M8.5 3v2"/><path d="M11 5c0 4.5-3 8-7 8"/><path d="M5.5 9c1 2.5 3 4 5.5 4.5"/><path d="M13 21l4-9 4 9"/><line x1="14.6" y1="18" x2="18.4" y2="18"/>',
            'trash' => '<polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/><path d="M10 11v6M14 11v6"/><path d="M9 6V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"/>',
            'tree' => '<path d="M12 3 6 11.4h3.4L5 17.4h14l-4.4-6H18z"/><line x1="12" y1="17.4" x2="12" y2="21.4"/>',
            'trident' => '<line x1="12" y1="3" x2="12" y2="21.4"/><path d="M6.2 6.6v3.2a3.2 3.2 0 0 0 3.2 3.2"/><path d="M17.8 6.6v3.2a3.2 3.2 0 0 1-3.2 3.2"/><line x1="6.2" y1="4.2" x2="6.2" y2="6.6"/><line x1="17.8" y1="4.2" x2="17.8" y2="6.6"/><line x1="8.4" y1="21.4" x2="15.6" y2="21.4"/>',
            'trophy' => '<path d="M8 3.6h8v5.9a4 4 0 0 1-8 0z"/><path d="M8 5.4H5.6a2.6 2.6 0 0 0 2.9 5.1"/><path d="M16 5.4h2.4a2.6 2.6 0 0 1-2.9 5.1"/><path d="M12 13.5v3.4"/><path d="M8.4 20.4h7.2l-1-3.5H9.4z"/>',
            'twitter' => '<path d="M22.5 4.3a9 9 0 0 1-2.6 1.3 4.6 4.6 0 0 0-7.9 3.1v1A10.9 10.9 0 0 1 3 5.1s-3.8 9.4 5 13.2a11.8 11.8 0 0 1-6.5 2c9 5.1 20.4 0 20.4-11.7a4.6 4.6 0 0 0-.1-.9 7.9 7.9 0 0 0 1.7-3.4z"/>',
            'unlock' => '<rect x="3.6" y="10.4" width="16.8" height="11" rx="2"/><path d="M7.6 10.4V7a4.4 4.4 0 0 1 8.4-2"/>',
            'upload' => '<path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 8 12 3 17 8"/><line x1="12" y1="3" x2="12" y2="15"/>',
            'user' => '<circle cx="12" cy="8" r="4"/><path d="M4.5 21a7.5 7.5 0 0 1 15 0"/>',
            'users' => '<circle cx="9" cy="8" r="3.5"/><path d="M2.5 20a6.5 6.5 0 0 1 13 0"/><path d="M16 5.2a3.5 3.5 0 0 1 0 6.6"/><path d="M18 14.4a6.5 6.5 0 0 1 3.5 5.6"/>',
            'versus' => '<circle cx="18" cy="18" r="3"/><circle cx="6" cy="6" r="3"/><path d="M13.5 6H16a2 2 0 0 1 2 2v7"/><path d="M10.5 18H8a2 2 0 0 1-2-2V9"/>',
            'wave' => '<path d="M8.2 12.2V5.6a1.6 1.6 0 0 1 3.2 0v5.6"/><path d="M11.4 11.2V4.4a1.6 1.6 0 0 1 3.2 0v6.8"/><path d="M14.6 11V6.4a1.6 1.6 0 0 1 3.2 0v8c0 4.4-2.6 7.4-6.6 7.4-3 0-5-1.6-6.4-4.6L3.2 13.8c-.6-1.1-.1-2.2.9-2.5.9-.3 1.7.1 2.2.9l1.9 2.8"/>',
            'wifi' => '<path d="M3.4 9.4a13 13 0 0 1 17.2 0"/><path d="M6.6 13a8.6 8.6 0 0 1 10.8 0"/><path d="M9.6 16.4a4.4 4.4 0 0 1 4.8 0"/><line x1="12" y1="20" x2="12.01" y2="20"/>',
            'write' => '<path d="M12 20.5h9"/><path d="M16.6 3.5a2.1 2.1 0 0 1 3 3L7 19.1l-4 1 1-4z"/>',
            'youtube' => '<path d="M22.5 6.4a2.8 2.8 0 0 0-2-2C18.9 4 12 4 12 4s-6.9 0-8.6.5a2.8 2.8 0 0 0-1.9 2A29 29 0 0 0 1 11.8a29 29 0 0 0 .5 5.3 2.8 2.8 0 0 0 1.9 2c1.7.5 8.6.5 8.6.5s6.9 0 8.6-.5a2.8 2.8 0 0 0 1.9-2 29 29 0 0 0 .5-5.3 29 29 0 0 0-.5-5.4z"/><polygon points="9.8 15 15.5 11.8 9.8 8.5 9.8 15"/>',
            'zod-aquarius' => '<path d="M2.6 8.8 5.4 6l2.8 2.8L11 6l2.8 2.8L16.6 6l2.8 2.8L22 6"/><path d="M2.6 16l2.8-2.8L8.2 16 11 13.2 13.8 16l2.8-2.8L19.4 16 22 13.2"/>',
            'zod-aries' => '<path d="M4.4 6.4a3.4 3.4 0 0 1 6.8 0v13"/><path d="M12.8 6.4a3.4 3.4 0 0 1 6.8 0v13"/>',
            'zod-cancer' => '<path d="M4.4 10.2c2.6-4.2 7.2-4.2 9.2-1"/><path d="M19.6 13.8c-2.6 4.2-7.2 4.2-9.2 1"/><circle cx="5.4" cy="6.8" r="1.9"/><circle cx="18.6" cy="17.2" r="1.9"/>',
            'zod-capricorn' => '<path d="M4.4 9.4a4.4 4.4 0 0 1 8.8 0c0 4-2.8 6.2-2.8 8.8 0 1.9 1.4 2.9 2.8 2.5"/><path d="M13.2 15.4c2.4 0 4-1.5 6-1.5 1.5 0 2.4.9 2.4 2.1"/>',
            'zod-gemini' => '<line x1="5" y1="3.4" x2="19" y2="3.4"/><line x1="5" y1="20.6" x2="19" y2="20.6"/><line x1="7.6" y1="3.4" x2="7.6" y2="20.6"/><line x1="16.4" y1="3.4" x2="16.4" y2="20.6"/>',
            'zod-leo' => '<circle cx="6.2" cy="12.4" r="2.9"/><path d="M9.1 10.4c2.4-3 5.6-3.4 7.6-.6 2 2.8 1.2 6.8-.8 8.8-1.3 1.3-3.2 1.7-4.7.9"/>',
            'zod-libra' => '<line x1="3.4" y1="20.4" x2="20.6" y2="20.4"/><line x1="6.6" y1="9.4" x2="17.4" y2="9.4"/><path d="M6.6 9.4 3 16h7.2z"/><path d="M17.4 9.4 14 16h7.2z"/>',
            'zod-pisces' => '<path d="M7 3.6c3 3 3 13.8 0 16.8"/><path d="M17 3.6c-3 3-3 13.8 0 16.8"/><line x1="3.4" y1="12" x2="20.6" y2="12"/>',
            'zod-sagittarius' => '<line x1="3.6" y1="20.4" x2="19.6" y2="4.4"/><polyline points="13 4.4 19.6 4.4 19.6 11"/><line x1="10.4" y1="9.6" x2="14.4" y2="13.6"/>',
            'zod-scorpio' => '<path d="M4.4 19.4V9a3.4 3.4 0 0 1 6.8 0v10.4"/><path d="M11.2 9a3.4 3.4 0 0 1 6.8 0v4.6"/><path d="M18 13.6h3.6v-3.2"/>',
            'zod-taurus' => '<circle cx="12" cy="15.4" r="5.4"/><path d="M3.6 3.4c0 4 3.8 6.2 8.4 6.2s8.4-2.2 8.4-6.2"/>',
            'zod-virgo' => '<path d="M4.4 19.4V8.4a3.4 3.4 0 0 1 6.8 0v11"/><path d="M11.2 8.4a3.4 3.4 0 0 1 6.8 0v5.8"/><path d="M18 14.2c1 2 2.4 3 3.6 3"/><line x1="3.4" y1="19.4" x2="20.6" y2="19.4"/>',
        ];
    }
}

if (!function_exists('icon')) {
    /**
     * Icona SVG inline, scalabile e adattiva (currentColor).
     *
     * @param string $name  chiave icona
     * @param int    $size  dimensione in px (0 = 1em, segue il testo)
     * @param string $class classe extra
     * @param float  $sw    spessore tratto (default 1.6)
     */
    function icon(string $name, int $size = 20, string $class = '', float $sw = 1.6): string
    {
        $paths = lux_icon_paths();
        $d   = $paths[$name] ?? $paths['sparkles'];
        $cls = 'ico' . ($class !== '' ? ' ' . $class : '');
        $dim = $size > 0 ? ' width="' . $size . '" height="' . $size . '"'
                         : ' width="1em" height="1em"';

        return '<svg class="' . $cls . '"' . $dim . ' viewBox="0 0 24 24"'
            . ' fill="none" stroke="currentColor" stroke-width="' . $sw . '"'
            . ' stroke-linecap="round" stroke-linejoin="round"'
            . ' aria-hidden="true" focusable="false">' . $d . '</svg>';
    }
}

if (!function_exists('lux_logo')) {
    /**
     * Marchio L.U.X. Verse: il logo ufficiale.
     *
     * Fino alla 39.1.2 qui dentro c'era un disegno fatto in casa (un quadrato
     * scuro con la L e la V) messo li' per non dover scaricare niente. Adesso
     * il marchio ufficiale c'e': e' un PNG trasparente, ritagliato sul segno
     * (assets/images/luxverse-official-mark.png, 192 px, 18 KB) e con le
     * misure scritte nell'immagine, cosi' il riquadro che lo contiene non
     * salta quando il file arriva. Lo sfondo non e' nella figura: lo mette il
     * riquadro che lo ospita (la placca della testata, il fondo del pannello),
     * quindi il marchio sta bene sia su fondo scuro sia su fondo chiaro.
     *
     * @param int    $size  lato in px (0 = segue il contenitore)
     * @param string $class classe extra
     */
    function lux_logo(int $size = 0, string $class = ''): string
    {
        $cls = 'lux-mark' . ($class !== '' ? ' ' . $class : '');
        $dim = $size > 0 ? ' width="' . $size . '" height="' . $size . '"' : '';

        return '<img class="' . $cls . '"' . $dim
            . ' src="/assets/images/luxverse-official-mark.png"'
            . ' alt="" aria-hidden="true" decoding="async" fetchpriority="high">';
    }
}

if (!function_exists('projectIcon')) {
    /** Icona di un progetto a partire dal campo `icon` di projects.json */
    function projectIcon(string $key, int $size = 22): string
    {
        $paths = lux_icon_paths();
        if (!isset($paths[$key])) $key = 'rocket';
        return icon($key, $size, 'p-ico', 1.5);
    }
}

if (!function_exists('noticeSocialLinks')) {
    /**
     * I canali social di un avviso (campo "social"), ripuliti: si tengono
     * solo gli indirizzi http(s) veri, con nome della rete ed etichetta
     * pronti da usare. Una voce senza rete riconosciuta diventa un
     * collegamento semplice, cosi' non si perde per strada.
     */
    function noticeSocialLinks($notice): array
    {
        $out = [];
        if (!is_array($notice) || !is_array($notice['social'] ?? null)) return $out;
        foreach ($notice['social'] as $s) {
            if (is_array($s)) {
                $net   = strtolower(trim((string)($s['network'] ?? '')));
                $url   = trim((string)($s['url'] ?? ''));
                $label = trim((string)($s['label'] ?? ''));
            } else {
                $net = ''; $url = trim((string)$s); $label = '';
            }
            if ($url === '' || !preg_match('#^https?://#i', $url)) continue;
            if ($net === '')   $net = 'link';
            if ($label === '') $label = ucfirst($net);
            $out[] = ['network' => $net, 'url' => $url, 'label' => $label];
        }
        return $out;
    }
}

if (!function_exists('noticeSocialBar')) {
    /**
     * La riga di icone social di un avviso. Si mette sia sopra sia sotto il
     * testo (position: "top" | "bottom"): cambia il posto, non l'ordine dei
     * canali, cosi' le due righe si leggono allo stesso modo.
     */
    function noticeSocialBar(array $links, string $position = 'bottom', string $label = 'Follow us'): string
    {
        if (!$links) return '';
        $pos   = $position === 'top' ? 'top' : 'bottom';
        $paths = lux_icon_paths();

        $h = '<div class="b-social b-social-' . $pos . '">';
        $h .= '<span class="b-social-label">' . htmlspecialchars($label) . '</span>';
        foreach ($links as $l) {
            $ico = isset($paths[$l['network']]) ? $l['network'] : 'link';
            $h .= '<a class="b-social-link net-' . htmlspecialchars($l['network']) . '"'
                . ' href="' . htmlspecialchars($l['url'], ENT_QUOTES) . '"'
                . ' target="_blank" rel="noopener"'
                . ' aria-label="' . htmlspecialchars($l['label']) . '">'
                . icon($ico, 16, 'b-social-ico')
                . '<span>' . htmlspecialchars($l['label']) . '</span></a>';
        }
        return $h . '</div>';
    }
}

if (!function_exists('lux_notice_lines')) {
    /**
     * Le righe dentro un capoverso di un avviso.
     *
     * Chi scrive va a capo dove vuole: quei ritorni a capo restano, cosi' il
     * testo si legge come e' stato scritto. Il testo e' sempre scappato:
     * dentro un avviso non passa HTML.
     */
    function lux_notice_lines(string $t): string
    {
        // Ogni a capo diventa un'interruzione, e nessun carattere di a capo
        // resta nel testo: il contenitore tiene i ritorni a capo come li ha
        // scritti l'autore, senza raddoppiarli.
        $t = htmlspecialchars($t);

        // Gli indirizzi internet, in un avviso, sono link veri e cliccabili.
        // Si lavora DOPO htmlspecialchars, quindi quello che esce e' sempre
        // testo gia' reso innocuo: l'unica cosa che si aggiunge sono i due tag
        // del collegamento. La punteggiatura in fondo a una frase resta fuori
        // dal link (altrimenti l'indirizzo si porterebbe dietro il punto).
        $t = preg_replace_callback(
            '~https?://[^\s<>]+~u',
            static function (array $m): string {
                $url  = rtrim($m[0], '.,;:!?)]');
                $tail = substr($m[0], strlen($url));
                return '<a href="' . $url . '" target="_blank" rel="noopener">' . $url . '</a>' . $tail;
            },
            $t
        ) ?? $t;

        return str_replace("\n", '<br>', $t);
    }
}

if (!function_exists('lux_notice_body')) {
    /**
     * Il testo di un avviso, diviso in blocchi leggibili.
     *
     * Perche': un avviso lungo era un muro di testo unico, e la struttura
     * c'era gia' ma non si vedeva — chi scrive separa gli argomenti con la
     * "·", oppure li numera, oppure mette in maiuscolo il titolo della voce.
     * Qui quella struttura diventa visibile:
     *   · voce numerata   "1. ..."        → numero in evidenza, testo accanto;
     *   · voce con titolo "LE CAUSE, ... " → titolo in maiuscolo, sotto il resto;
     *   · voce elenco     "· ..."         → pallino dorato e testo;
     *   · capoverso                        → testo semplice.
     *
     * Il testo NON cambia e non viene tagliato: la "·" dell'autore diventa il
     * pallino dell'elenco (era gia' un punto elenco, non una parola), il resto
     * dei caratteri resta identico. Nessuna riga viene tolta.
     */
    function lux_notice_body(string $msg): string
    {
        $msg = str_replace(["\r\n", "\r"], "\n", $msg);

        // La "·" con cui l'autore separa gli argomenti e' un punto elenco:
        // diventa un marcatore di voce (\x1F, un carattere di controllo che
        // non compare mai in un testo scritto a mano) e da li' in poi si
        // ragiona per blocchi.
        $msg = preg_replace('/[ \t]*·[ \t]*/u', "\n\n\x1F", $msg);
        if ($msg === null) return '<p class="b-p">' . lux_notice_lines($msg) . '</p>';

        $out = [];
        // Si va a capo dove c'e' una riga vuota, oppure davanti a una voce
        // segnata con la "·" anche se il testo e' tutto di seguito.
        foreach (preg_split('/(?:\n{2,}|\n+(?=\x1F))/u', $msg) as $block) {
            $block = trim((string)$block);
            if ($block === '') continue;

            $bullet = false;
            if (strncmp($block, "\x1F", 1) === 0) {
                $bullet = true;
                $block  = trim(substr($block, 1));
                if ($block === '') continue;
            }

            // 1. voce numerata: "1. ..." oppure "1) ..."
            if (preg_match('/^(\d{1,2})[.)]\s*(.+)$/su', $block, $m)) {
                $out[] = '<div class="b-item b-item-num">'
                       . '<span class="b-mark" aria-hidden="true">' . $m[1] . '.</span> '
                       . '<span class="b-item-body">' . lux_notice_lines($m[2]) . '</span></div>';
                continue;
            }

            // 2. titolo della voce: la parte in maiuscolo fino all'ultimo punto
            //    (es. "LE CAUSE, UNA PER UNA."): il resto e' la spiegazione.
            if (preg_match('/^([^\p{Ll}]{6,}\.)\s*(.*)$/su', $block, $m)) {
                $out[] = '<div class="b-item b-item-head' . ($bullet ? ' b-bullet' : '') . '">'
                       . '<b class="b-head">' . htmlspecialchars(trim($m[1])) . '</b>'
                       . ($m[2] !== '' ? ' <span class="b-item-body">' . lux_notice_lines($m[2]) . '</span>' : '')
                       . '</div>';
                continue;
            }

            // 3. voce di elenco (senza titolo) e 4. capoverso normale.
            $out[] = '<p class="b-p' . ($bullet ? ' b-bullet' : '') . '">' . lux_notice_lines($block) . '</p>';
        }

        // Fra un blocco e l'altro resta uno spazio: a schermo non si vede (i
        // blocchi sono separati dalla loro forma), ma chi legge con un lettore
        // di schermo o copia il testo non si trova le parole attaccate.
        return implode(' ', $out);
    }
}

if (!function_exists('lux_emoji_to_icon')) {
    /** Nome icona associato a un emoji ('' se sconosciuto). */
    function lux_emoji_to_icon(string $emoji): string
    {
        static $map = null;
        if ($map === null) {
            $map = lux_emoji_map();
        }
        $key = str_replace("\u{FE0F}", '', $emoji);
        return $map[$key] ?? ($map[$emoji] ?? '');
    }
}

if (!function_exists('lux_emoji_map')) {
    /** Mappa emoji -> chiave icona (fonte unica, usata anche dal runtime JS). */
    function lux_emoji_map(): array
    {
        return [
            "\u{1F468}\u{200D}\u{1F467}\u{200D}\u{1F467}" => 'family',
            "\u{1F468}\u{200D}\u{1F469}\u{200D}\u{1F466}" => 'family',
            "\u{1F468}\u{200D}\u{1F469}\u{200D}\u{1F467}" => 'family',
            "\u{1F469}\u{200D}\u{1F466}\u{200D}\u{1F466}" => 'family',
            "\u{1F469}\u{200D}\u{1F469}\u{200D}\u{1F467}" => 'family',
            "\u{1F468}\u{200D}\u{1F466}" => 'family',
            "\u{1F468}\u{200D}\u{1F467}" => 'family',
            "\u{1F469}\u{200D}\u{1F466}" => 'family',
            "\u{1F469}\u{200D}\u{1F467}" => 'family',
            "\u{2049}" => 'help',
            "\u{21A9}" => 'retry',
            "\u{2600}" => 'sun',
            "\u{2605}" => 'star-solid',
            "\u{260E}" => 'phone',
            "\u{2615}" => 'coffee',
            "\u{261D}" => 'pointer',
            "\u{2648}" => 'zod-aries',
            "\u{2649}" => 'zod-taurus',
            "\u{264A}" => 'zod-gemini',
            "\u{264B}" => 'zod-cancer',
            "\u{264C}" => 'zod-leo',
            "\u{264D}" => 'zod-virgo',
            "\u{264E}" => 'zod-libra',
            "\u{264F}" => 'zod-scorpio',
            "\u{2650}" => 'zod-sagittarius',
            "\u{2651}" => 'zod-capricorn',
            "\u{2652}" => 'zod-aquarius',
            "\u{2653}" => 'zod-pisces',
            "\u{265F}" => 'chess',
            "\u{2660}" => 'suit-spade',
            "\u{2663}" => 'suit-club',
            "\u{2665}" => 'suit-heart',
            "\u{2666}" => 'suit-diamond',
            "\u{267B}" => 'refresh',
            "\u{267E}" => 'infinity',
            "\u{2696}" => 'scale',
            "\u{2697}" => 'scale',
            "\u{26A0}" => 'alert',
            "\u{26A1}" => 'bolt',
            "\u{26BD}" => 'ball',
            "\u{2705}" => 'check-circle',
            "\u{2709}" => 'mail',
            "\u{270D}" => 'pen',
            "\u{2713}" => 'check',
            "\u{2714}" => 'check',
            "\u{2715}" => 'close',
            "\u{2716}" => 'multiply',
            "\u{2728}" => 'sparkles',
            "\u{274C}" => 'close-circle',
            "\u{274E}" => 'close-circle',
            "\u{2753}" => 'help',
            "\u{2755}" => 'alert',
            "\u{2757}" => 'alert',
            "\u{2764}" => 'heart',
            "\u{2797}" => 'divide',
            "\u{2B05}" => 'arrow-left',
            "\u{2B06}" => 'arrow-up',
            "\u{2B07}" => 'arrow-down',
            "\u{2B1B}" => 'tile-filled',
            "\u{2B1C}" => 'tile-empty',
            "\u{2B50}" => 'star-solid',
            "\u{1F004}" => 'tile',
            "\u{1F0CF}" => 'cards',
            "\u{1F193}" => 'tag',
            "\u{1F300}" => 'spiral',
            "\u{1F308}" => 'rainbow',
            "\u{1F309}" => 'infinity',
            "\u{1F30B}" => 'burst',
            "\u{1F30C}" => 'orbit',
            "\u{1F30D}" => 'globe',
            "\u{1F30E}" => 'globe',
            "\u{1F30F}" => 'globe',
            "\u{1F310}" => 'globe',
            "\u{1F319}" => 'moon',
            "\u{1F31B}" => 'moon',
            "\u{1F31E}" => 'sun',
            "\u{1F31F}" => 'star-solid',
            "\u{1F331}" => 'seedling',
            "\u{1F333}" => 'tree',
            "\u{1F334}" => 'tree',
            "\u{1F33F}" => 'leaf',
            "\u{1F343}" => 'leaf',
            "\u{1F345}" => 'timer',
            "\u{1F354}" => 'burger',
            "\u{1F355}" => 'pizza',
            "\u{1F35A}" => 'timer',
            "\u{1F35F}" => 'burger',
            "\u{1F36A}" => 'cookie',
            "\u{1F389}" => 'party',
            "\u{1F38A}" => 'confetti',
            "\u{1F393}" => 'gradcap',
            "\u{1F3A5}" => 'film',
            "\u{1F3A8}" => 'palette',
            "\u{1F3AB}" => 'ticket',
            "\u{1F3AC}" => 'film',
            "\u{1F3AE}" => 'gamepad',
            "\u{1F3AF}" => 'target',
            "\u{1F3B0}" => 'slots',
            "\u{1F3B2}" => 'dice',
            "\u{1F3B5}" => 'music',
            "\u{1F3B6}" => 'music',
            "\u{1F3BE}" => 'ball',
            "\u{1F3C0}" => 'ball',
            "\u{1F3C6}" => 'trophy',
            "\u{1F3D3}" => 'pong',
            "\u{1F3EB}" => 'gradcap',
            "\u{1F3F7}" => 'tag',
            "\u{1F407}" => 'rabbit',
            "\u{1F408}" => 'cat',
            "\u{1F409}" => 'panda',
            "\u{1F40D}" => 'snake',
            "\u{1F415}" => 'dog',
            "\u{1F41D}" => 'orbit',
            "\u{1F41E}" => 'orbit',
            "\u{1F423}" => 'egg',
            "\u{1F425}" => 'bird',
            "\u{1F426}" => 'bird',
            "\u{1F427}" => 'bird',
            "\u{1F42D}" => 'mouse',
            "\u{1F430}" => 'rabbit',
            "\u{1F431}" => 'cat',
            "\u{1F436}" => 'dog',
            "\u{1F439}" => 'mouse',
            "\u{1F43A}" => 'dog',
            "\u{1F43B}" => 'bear',
            "\u{1F43C}" => 'panda',
            "\u{1F43E}" => 'paw',
            "\u{1F440}" => 'eye',
            "\u{1F441}" => 'eye',
            "\u{1F446}" => 'pointer',
            "\u{1F448}" => 'pointer',
            "\u{1F449}" => 'pointer',
            "\u{1F44B}" => 'wave',
            "\u{1F451}" => 'crown',
            "\u{1F45C}" => 'brief',
            "\u{1F464}" => 'user',
            "\u{1F465}" => 'users',
            "\u{1F466}" => 'user',
            "\u{1F467}" => 'user',
            "\u{1F468}" => 'user',
            "\u{1F469}" => 'user',
            "\u{1F46A}" => 'family',
            "\u{1F476}" => 'user',
            "\u{1F47E}" => 'invader',
            "\u{1F48C}" => 'mail',
            "\u{1F48E}" => 'gem',
            "\u{1F495}" => 'heart',
            "\u{1F496}" => 'heart',
            "\u{1F49A}" => 'heart',
            "\u{1F4A0}" => 'tile-empty',
            "\u{1F4A1}" => 'bulb',
            "\u{1F4A2}" => 'burst',
            "\u{1F4A3}" => 'alert',
            "\u{1F4A4}" => 'moon',
            "\u{1F4A5}" => 'burst',
            "\u{1F4A6}" => 'droplet',
            "\u{1F4A7}" => 'droplet',
            "\u{1F4AA}" => 'strength',
            "\u{1F4AB}" => 'orbit',
            "\u{1F4AC}" => 'chat',
            "\u{1F4AD}" => 'chat',
            "\u{1F4AF}" => 'check-circle',
            "\u{1F4B0}" => 'tag',
            "\u{1F4B3}" => 'ticket',
            "\u{1F4B5}" => 'tag',
            "\u{1F4B8}" => 'tag',
            "\u{1F4BB}" => 'laptop',
            "\u{1F4BC}" => 'brief',
            "\u{1F4BE}" => 'file',
            "\u{1F4C0}" => 'film',
            "\u{1F4C1}" => 'folder',
            "\u{1F4C2}" => 'folder',
            "\u{1F4C4}" => 'file',
            "\u{1F4C5}" => 'calendar',
            "\u{1F4C6}" => 'calendar',
            "\u{1F4C8}" => 'chart',
            "\u{1F4C9}" => 'chart',
            "\u{1F4CA}" => 'chart',
            "\u{1F4CB}" => 'clipboard',
            "\u{1F4CC}" => 'pin',
            "\u{1F4CD}" => 'pin',
            "\u{1F4D0}" => 'ruler',
            "\u{1F4D5}" => 'book',
            "\u{1F4D6}" => 'book-open',
            "\u{1F4DA}" => 'books',
            "\u{1F4DC}" => 'scroll',
            "\u{1F4DD}" => 'note',
            "\u{1F4DE}" => 'phone',
            "\u{1F4E1}" => 'wifi',
            "\u{1F4E2}" => 'megaphone',
            "\u{1F4E3}" => 'megaphone',
            "\u{1F4E4}" => 'upload',
            "\u{1F4E5}" => 'download',
            "\u{1F4E7}" => 'mail',
            "\u{1F4E8}" => 'mail',
            "\u{1F4E9}" => 'mail',
            "\u{1F4F0}" => 'note',
            "\u{1F4F1}" => 'smartphone',
            "\u{1F4F8}" => 'camera',
            "\u{1F500}" => 'shuffle',
            "\u{1F50B}" => 'bolt',
            "\u{1F50D}" => 'search',
            "\u{1F50E}" => 'search',
            "\u{1F511}" => 'key',
            "\u{1F512}" => 'lock',
            "\u{1F513}" => 'unlock',
            "\u{1F517}" => 'link',
            "\u{1F51D}" => 'pin-note',
            "\u{1F522}" => 'hash',
            "\u{1F525}" => 'flame',
            "\u{1F527}" => 'tools',
            "\u{1F528}" => 'tools',
            "\u{1F52C}" => 'flask',
            "\u{1F52D}" => 'orbit',
            "\u{1F52E}" => 'orb',
            "\u{1F531}" => 'trident',
            "\u{1F534}" => 'dot-red',
            "\u{1F535}" => 'dot-blue',
            "\u{1F577}" => 'spider',
            "\u{1F58A}" => 'pen',
            "\u{1F5A5}" => 'laptop',
            "\u{1F5B1}" => 'pointer',
            "\u{1F5B2}" => 'target',
            "\u{1F5C2}" => 'folder',
            "\u{1F5D3}" => 'calendar',
            "\u{1F602}" => 'laugh',
            "\u{1F60A}" => 'smile',
            "\u{1F620}" => 'frown',
            "\u{1F642}" => 'smile',
            "\u{1F647}" => 'bow',
            "\u{1F64F}" => 'hands',
            "\u{1F680}" => 'rocket',
            "\u{1F6A8}" => 'alert',
            "\u{1F6AA}" => 'external',
            "\u{1F6AB}" => 'close-circle',
            "\u{1F6CB}" => 'sofa',
            "\u{1F6D1}" => 'stop',
            "\u{1F6E0}" => 'tools',
            "\u{1F6F8}" => 'rocket',
            "\u{1F7E0}" => 'dot-red',
            "\u{1F7E1}" => 'dot-yellow',
            "\u{1F7E2}" => 'dot-green',
            "\u{1F7E3}" => 'dot-blue',
            "\u{1F916}" => 'bot',
            "\u{1F923}" => 'laugh',
            "\u{1F95A}" => 'egg',
            "\u{1F984}" => 'fox',
            "\u{1F98A}" => 'fox',
            "\u{1F9D1}" => 'user',
            "\u{1F9D8}" => 'meditate',
            "\u{1F9E0}" => 'brain',
            "\u{1F9E2}" => 'chess',
            "\u{1F9E9}" => 'puzzle',
            "\u{1F9EA}" => 'flask',
            "\u{1F9EC}" => 'brain',
            "\u{1F9F0}" => 'link',
            "\u{1F9F1}" => 'tile',
            "\u{1F9F2}" => 'tools',
            "\u{1FAA2}" => 'link',
        ];
    }
}

if (!function_exists('lux_emoji_svg')) {
    /** Converte un emoji in SVG inline; stringa vuota se non riconosciuto. */
    function lux_emoji_svg(string $emoji, int $size = 0, float $sw = 1.8): string
    {
        $name = lux_emoji_to_icon($emoji);
        if ($name === '') return '';
        return icon($name, $size, 'ico-em', $sw);
    }
}

if (!function_exists('lux_strip_emoji')) {
    /** Rimuove ogni emoji da un testo (usato sul contenuto JSON dell'AI). */
    function lux_strip_emoji(string $text): string
    {
        if ($text === '' || $text === null) return (string)$text;
        $clean = preg_replace('/[\x{1F000}-\x{1FAFF}\x{2600}-\x{27BF}\x{2B00}-\x{2BFF}\x{FE0F}\x{200D}\x{20E3}\x{2190}-\x{21FF}]/u', '', $text);
        return $clean === null ? $text : $clean;
    }
}

if (!function_exists('lux_deemoji')) {
    /**
     * Sostituisce gli emoji in un testo con icone (placeholder) e rimuove
     * quelli non mappati. Usato su tutto il testo generato dall'AI, cosi'
     * il contenuto dinamico resta coerente con il design del sito.
     */
    function lux_deemoji(string $text): string
    {
        if ($text === '' || $text === null) return (string)$text;

        static $keys = null, $html = null;
        if ($keys === null) {
            $map  = lux_emoji_map();
            $keys = array_keys($map);
            usort($keys, function ($a, $b) { return strlen($b) <=> strlen($a); });
            $html = [];
            foreach ($keys as $k) {
                $html[] = '<i data-i=' . $map[$k] . ' class=ic></i>';
            }
        }

        $text = str_replace($keys, $html, $text);
        $clean = preg_replace('/[\x{1F000}-\x{1FAFF}\x{2600}-\x{27BF}\x{2B00}-\x{2BFF}\x{FE0F}\x{200D}\x{20E3}]/u', '', $text);

        return $clean === null ? $text : $clean;
    }
}


// ---------- HEADER ----------
function lux_header(array $vars = []): void
{
    // I link della pagina vengono riscritti alla fine, quando tutto il testo
    // e' pronto: si fa con un buffer di output agganciato qui (vedi
    // lux_fix_links). Il pannello di amministrazione e le API non ne hanno
    // bisogno e non li prendono.
    if (!defined('LUX_NO_LINK_REWRITE')) {
        $luxScript = basename((string)($_SERVER['SCRIPT_NAME'] ?? ''));
        if ($luxScript !== 'admin.php' && $luxScript !== 'api.php') ob_start('lux_fix_links');
        unset($luxScript);
    }

    // Variabili della pagina: chi chiama puo' passarle (chi le ha in una
    // funzione, come il motore degli strumenti) altrimenti si leggono
    // dallo scope globale (le pagine normali).
    foreach (['pageTitle', 'pageDescription', 'pageKeywords', 'pageOgImage', 'pageOgType', 'pageCSS', 'extraCSS', 'isAppView'] as $__k) {
        $$__k = $vars[$__k] ?? ($GLOBALS[$__k] ?? null);
    }
    unset($__k);


// ============================================================
// HEADER CONDIVISO L.U.X. VERSE — v7 "ONYX"
// Grafite + osso + oro · icone SVG (nessuna emoji) · zero blur
// Prima dell'include si può impostare:
//   $pageTitle (richiesta) · $pageDescription · $pageKeywords
//   $pageOgImage · $extraCSS · $pageCSS · $pageOgType · $isAppView
// ============================================================

require_once dirname(__DIR__) . '/config.php';
lux_session_start();
trackVisit();

$pageTitle       = $pageTitle ?? t('Home');
$pageDescription = $pageDescription ?? '';
$pageKeywords    = $pageKeywords ?? '';
$pageOgImage     = $pageOgImage ?? 'https://luxverse.c22.eu/assets/images/luxverse-official-fondo.png';
$pageCSS         = $pageCSS ?? '';
$currentFile     = basename($_SERVER['SCRIPT_NAME']);
if (isset($_GET['app']) && $_GET['app'] === '1') { $_SESSION['app_view'] = true; }
$isAppView       = !empty($isAppView) || !empty($_SESSION['app_view']);
$homeLink        = $isAppView ? 'webapp.php' : 'index.php';
$currentLang     = currentLang();
$htmlLang        = $currentLang === 'it' ? 'it' : 'en';
$SITE_URL        = 'https://luxverse.c22.eu';
$canonical       = $SITE_URL . '/' . $currentFile;
// Le pagine costruite come file .html hanno il loro indirizzo definitivo: il
// canonical deve indicare quello, non il .php che nel frattempo reindirizza.
$canonicalFile   = (defined('LUX_EXPORT_PAGE') && LUX_EXPORT_PAGE !== '') ? (string)LUX_EXPORT_PAGE : $currentFile;
$canonicalStatic = in_array($canonicalFile, (array)LUX_STATIC_PAGES, true);
if ($canonicalStatic) {
    $canonicalHost = (defined('LUX_STATIC_SELF') && LUX_STATIC_SELF)
        ? $SITE_URL
        : rtrim((string)LUX_STATIC_BASE, '/');
    $canonicalHtml = str_replace('.php', '.html', $canonicalFile);
    $canonical     = $canonicalHost . '/' . $canonicalHtml;
    $canonicalIt   = $canonical;
    $canonicalEn   = $canonicalHost . '/en/' . $canonicalHtml;
} else {
    $canonicalIt   = $canonical . '?lang=it';
    $canonicalEn   = $canonical . '?lang=en';
}
$assetV          = LUX_ASSET_V;
?>
<!DOCTYPE html>
<html lang="<?= $htmlLang ?>">
<head>
    <meta charset="UTF-8">
    <script>
    // Dove vivono le API e se questa e' una copia statica. Stanno QUI, in
    // cima, e non in fondo: il campanello delle notifiche e il resto del
    // codice dell'intestazione partono prima, e leggono queste due righe.
    // Non si decide in fase di costruzione ma qui, guardando dove la pagina
    // e' aperta: sullo stesso dominio le API sono a portata di mano (stesse
    // origine, sessione compresa); su una copia pubblicata altrove la pagina
    // resta in sola lettura, cosi' non chiede niente a un indirizzo che non
    // ha.
    window.LUX_SITE_ORIGIN = <?= json_encode($SITE_URL) ?>;
    window.LUX_STATIC_PAGE = (location.origin !== window.LUX_SITE_ORIGIN);
    window.LUX_API_BASE = window.LUX_STATIC_PAGE ? window.LUX_SITE_ORIGIN : '';
    </script>
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <title><?= htmlspecialchars($pageTitle) ?></title>

    <?php if ($pageDescription !== ''): ?>
    <meta name="description" content="<?= htmlspecialchars($pageDescription) ?>">
    <?php endif; ?>
    <?php if ($pageKeywords !== ''): ?>
    <meta name="keywords" content="<?= htmlspecialchars($pageKeywords) ?>">
    <?php endif; ?>
    <meta name="author" content="L.U.X. Verse Ecosystem">
    <?php
    // Le pagine dei contenuti creati dalle persone (per esempio un risultato
    // condiviso) non finiscono nei motori di ricerca: si raggiungono per
    // link, ma non sono pagine del sito. Basta alzare $luxNoIndex prima di
    // chiamare lux_header().
    ?>
    <meta name="robots" content="<?= !empty($GLOBALS['luxNoIndex']) ? 'noindex, follow' : 'index, follow, max-image-preview:large' ?>">
    <meta name="google-site-verification" content="mcK_eYgFZlrMHdJgdzAJaxV17T91fZsf9sUg5u5vm6E" />
    <meta name="color-scheme" content="dark">

    <link rel="canonical" href="<?= $canonical ?>">
    <link rel="alternate" hreflang="it" href="<?= $canonicalIt ?>">
    <link rel="alternate" hreflang="en" href="<?= $canonicalEn ?>">
    <link rel="alternate" hreflang="x-default" href="<?= $canonicalIt ?>">

    <meta property="og:title" content="<?= htmlspecialchars($pageTitle) ?>">
    <?php if ($pageDescription !== ''): ?>
    <meta property="og:description" content="<?= htmlspecialchars($pageDescription) ?>">
    <?php endif; ?>
    <meta property="og:image" content="<?= htmlspecialchars($pageOgImage) ?>">
    <meta property="og:url" content="<?= $canonical ?>">
    <meta property="og:type" content="<?= $pageOgType ?? 'website' ?>">
    <meta property="og:site_name" content="L.U.X. Verse">
    <meta property="og:locale" content="<?= $htmlLang === 'it' ? 'it_IT' : 'en_US' ?>">
    <meta property="og:locale:alternate" content="<?= $htmlLang === 'it' ? 'en_US' : 'it_IT' ?>">

    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="<?= htmlspecialchars($pageTitle) ?>">
    <?php if ($pageDescription !== ''): ?>
    <meta name="twitter:description" content="<?= htmlspecialchars($pageDescription) ?>">
    <?php endif; ?>
    <meta name="twitter:image" content="<?= htmlspecialchars($pageOgImage) ?>">

    <meta name="theme-color" content="#0A0B0D">
    <link rel="manifest" href="/manifest.json">
    <meta name="mobile-web-app-capable" content="yes">
    <link rel="apple-touch-icon" href="/assets/images/apple-touch-icon.png">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="L.U.X. Verse">
    <link rel="icon" type="image/png" href="/assets/images/favicon-32.png">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Archivo:wght@500;600;700&family=Inter+Tight:wght@400;500;600&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="/assets/css/site.css?v=<?= $assetV ?>">
    <?php if ($pageCSS !== ''):
        // Il foglio della pagina va chiesto dalla radice: le pagine lo
        // scrivono come 'assets/css/index.css', e da una pagina in
        // sottocartella (per esempio /progetti/...) quel percorso relativo
        // punterebbe a /progetti/assets/css/index.css, cioe' a un 404: la
        // pagina si vedrebbe senza stile. Meglio non dipendere da dove sta
        // il file che chiama l'header.
        $pageCssHref = $pageCSS;
        if (strpos($pageCssHref, '//') === false && substr($pageCssHref, 0, 1) !== '/') {
            $pageCssHref = '/' . ltrim($pageCssHref, './');
        }
        $pageCssHref .= strpos($pageCssHref, '?') === false ? '?v=' . $assetV : '';
    ?>
    <link rel="stylesheet" href="<?= htmlspecialchars($pageCssHref) ?>">
    <?php endif; ?>
    <?php if (!empty($extraCSS)): ?>
    <style><?= $extraCSS ?></style>
    <?php endif; ?>

    <!-- ICON RUNTIME: converte i placeholder <i data-i=nome> in SVG inline.
         Caricato presto (defer) cosi' nessuna icona resta vuota per piu' di un frame. -->
    <script src="/assets/js/lux-icons.js?v=<?= $assetV ?>" defer></script>

    <script type="application/ld+json">
    {
        "@context": "https://schema.org",
        "@graph": [
            {
                "@type": "Organization",
                "@id": "<?= $SITE_URL ?>/#organization",
                "name": "L.U.X. Verse",
                "url": "<?= $SITE_URL ?>",
                "logo": { "@type": "ImageObject", "url": "<?= $SITE_URL ?>/assets/images/luxverse-official-fondo.png" },
                "slogan": "<?= t('A free AI ecosystem for creativity, productivity and innovation.') ?>",
                "founder": { "@type": "Person", "name": "M. M. Yeamin" }
            },
            {
                "@type": "WebSite",
                "@id": "<?= $SITE_URL ?>/#website",
                "url": "<?= $SITE_URL ?>",
                "name": "L.U.X. Verse",
                "description": "<?= t('L.U.X. Verse is a free AI ecosystem for creativity, productivity and innovation. 100% no registration, no tracking cookies.') ?>",
                "inLanguage": ["it", "en"],
                "publisher": { "@id": "<?= $SITE_URL ?>/#organization" }
            },
            {
                "@type": "BreadcrumbList",
                "itemListElement": [
                    { "@type": "ListItem", "position": 1, "name": "Home", "item": "<?= $SITE_URL ?>/index.php" }
                ]
            }
        ]
    }
    </script>
</head>
<body class="<?= isAdminLoggedIn() ? 'is-admin-logged' : '' ?>" data-page="<?= htmlspecialchars($currentFile) ?>">

    <!-- LOADER MINIMALE (come prima: solo il marchio testuale, nessun logo immagine) -->
    <div id="workme-loader-overlay" aria-hidden="true">
        <div class="loader-core">
            <div class="loader-aurora"></div>
            <div class="loader-ring r1"></div>
            <div class="loader-ring r2"></div>
            <div class="loader-ring r3"></div>
            <div class="loader-logo" id="loaderLogo">
                <span class="l-letter">L</span><span class="l-dot">&middot;</span><span class="l-letter">U</span><span class="l-dot">&middot;</span><span class="l-letter">X</span><span style="display:inline-block;width:0.55em"></span><span class="l-letter">V</span><span class="l-letter">E</span><span class="l-letter">R</span><span class="l-letter">S</span><span class="l-letter">E</span>
            </div>
            <div class="loader-sub"><?= t('Loading') ?> <span id="loaderPercent">0</span>%</div>
            <div class="loader-bar"><div class="loader-bar-fill" id="loaderBarFill"></div></div>
        </div>
    </div>

    <!-- SFONDO -->
    <div class="bg-aurora" aria-hidden="true"></div>

    <header class="header" id="header">
        <div class="container header-inner">
            <a href="<?= $homeLink ?>" class="logo" aria-label="L.U.X. Verse — Home">
                <span class="logo-badge" aria-hidden="true">
                    <?= lux_logo(30) ?>
                </span>
                <span class="logo-text">L.U.X.<span>Verse</span></span>
            </a>

            <nav class="nav" aria-label="Navigazione principale">
                <a href="<?= $homeLink ?>" class="<?= $currentFile === basename($homeLink) ? 'active' : '' ?>" <?= $currentFile === basename($homeLink) ? 'aria-current="page"' : '' ?>><?= t('Home') ?></a>
                <a href="/bacheca.php" class="<?= $currentFile === 'bacheca.php' ? 'active' : '' ?>" <?= $currentFile === 'bacheca.php' ? 'aria-current="page"' : '' ?>><?= t('Bacheca') ?></a>
                <a href="/linea-del-tempo.php" class="<?= $currentFile === 'linea-del-tempo.php' ? 'active' : '' ?>" <?= $currentFile === 'linea-del-tempo.php' ? 'aria-current="page"' : '' ?>><?= t('Linea del Tempo') ?></a>
                <a href="/app.php" class="<?= $currentFile === 'app.php' ? 'active' : '' ?>" <?= $currentFile === 'app.php' ? 'aria-current="page"' : '' ?>><?= t('App') ?></a>
                <a href="/codice-aperto.php" class="<?= $currentFile === 'codice-aperto.php' ? 'active' : '' ?>" <?= $currentFile === 'codice-aperto.php' ? 'aria-current="page"' : '' ?>><?= t('Open source') ?></a>
                <a href="/chi-siamo.php" class="<?= $currentFile === 'chi-siamo.php' ? 'active' : '' ?>"><?= t('Chi Siamo') ?></a>
                <a href="/faq.php" class="<?= $currentFile === 'faq.php' ? 'active' : '' ?>"><?= t('FAQ') ?></a>
                <a href="/assistenza.php" class="<?= $currentFile === 'assistenza.php' ? 'active' : '' ?>"><?= t('Assistenza') ?></a>
                <a href="/status.php" class="<?= $currentFile === 'status.php' ? 'active' : '' ?>" <?= $currentFile === 'status.php' ? 'aria-current="page"' : '' ?>><?= t('Stato') ?></a>
            </nav>

            <div class="header-actions">
                <div class="notif-wrap" id="luxNotifWrap">
                    <button type="button" class="notif-btn" id="luxNotifBtn" aria-label="<?= t('Notifications') ?>" aria-haspopup="true" aria-expanded="false">
                        <?= icon('bell', 17) ?>
                        <span class="notif-dot" id="luxNotifDot" hidden></span>
                    </button>
                    <div class="notif-panel" id="luxNotifPanel" hidden>
                        <div class="notif-head">
                            <span><?= t('Notifications') ?></span>
                            <button type="button" class="notif-clear" id="luxNotifClear"><?= t('Mark all as read') ?></button>
                        </div>
                        <ul class="notif-list" id="luxNotifList"></ul>
                        <p class="notif-empty" id="luxNotifEmpty"><?= t('No notifications right now.') ?></p>
                    </div>
                </div>
                <div class="lang-group" role="group" aria-label="<?= t('Lingua') ?>">
                    <a href="?lang=it" class="lang-btn <?= $currentLang === 'it' ? 'active' : '' ?>" title="Italiano" hreflang="it" rel="alternate">IT</a>
                    <a href="?lang=en" class="lang-btn <?= $currentLang === 'en' ? 'active' : '' ?>" title="English" hreflang="en" rel="alternate">EN</a>
                </div>
                <button type="button" class="navbar-profile" id="luxpHeaderProfile" title="<?= t('Area personale') ?>" aria-label="<?= t('Area personale') ?>">
                    <span class="navbar-profile-avatar" id="luxpHeaderAvatar"><?= icon('user', 18) ?></span>
                </button>
                <a href="/admin.php" class="navbar-admin" title="<?= t('Admin') ?>" aria-label="<?= t('Admin') ?>">
                    <?= icon('settings', 15) ?>
                    <span><?= t('Admin') ?></span>
                </a>
                <button class="hamburger" id="hamburger" aria-label="Menu" aria-expanded="false" aria-controls="mobileMenu" type="button">
                    <span></span><span></span><span></span>
                </button>
            </div>
        </div>
    </header>

    <script>
    (function () {
        var wrap = document.getElementById('luxNotifWrap');
        if (!wrap) return;
        var btn     = document.getElementById('luxNotifBtn');
        var panel   = document.getElementById('luxNotifPanel');
        var list    = document.getElementById('luxNotifList');
        var dot     = document.getElementById('luxNotifDot');
        var emptyEl = document.getElementById('luxNotifEmpty');
        var clearBt = document.getElementById('luxNotifClear');
        var KEY = 'lux_notif_read';
        var LANG = <?= json_encode($currentLang) ?>;
        var LABEL = <?= json_encode(t('Notifications')) ?>;
        var items = [];

        function readIds() {
            try { var v = JSON.parse(localStorage.getItem(KEY) || '[]'); return Array.isArray(v) ? v : []; }
            catch (e) { return []; }
        }
        function saveIds(ids) {
            try { localStorage.setItem(KEY, JSON.stringify(ids.slice(-120))); } catch (e) {}
        }
        function esc(s) {
            return String(s == null ? '' : s).replace(/[&<>"']/g, function (c) {
                return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c];
            });
        }
        function paint() {
            var ids = readIds();
            var unread = items.filter(function (n) { return ids.indexOf(n.id) === -1; }).length;
            // Nessun numero accanto alla campana: c'e' solo il puntino rosso,
            // e compare soltanto se c'e' davvero qualcosa di nuovo. Il conteggio
            // rimane nel testo, per chi usa un lettore di schermo.
            dot.hidden = unread === 0;
            dot.textContent = '';
            btn.setAttribute('aria-label', unread > 0 ? LABEL + ' · ' + unread : LABEL);
            if (!items.length) {
                list.innerHTML = '';
                emptyEl.hidden = false;
                return;
            }
            emptyEl.hidden = true;
            list.innerHTML = items.map(function (n) {
                var tag = '<span class="notif-cat ' + esc(n.category || 'info') + '">' + esc(n.category || 'info') + '</span>';
                var body = '<b>' + esc(n.title) + '</b><span>' + esc(n.message) + '</span>';
                var time = '<time>' + esc(n.date) + '</time>';
                if (n.redirect) {
                    return '<li class="notif-item"><a href="' + esc(n.redirect) + '">' + tag + body + time + '</a></li>';
                }
                return '<li class="notif-item">' + tag + '<div class="notif-txt">' + body + '</div>' + time + '</li>';
            }).join('');
        }
        // ------------------------------------------------------------
        // AVVISO A COMPARSA — 5 secondi, poi rientra nel campanello
        // ------------------------------------------------------------
        // Quando esce una notifica nuova (anche inviata adesso dall'admin) la
        // vedi comparire in alto a destra per 5 secondi, con la barra del tempo
        // che scorre. Poi sparisce da sola e resta nel campanello: e' la stessa
        // notifica, non una copia. Se ci passi sopra il mouse il tempo si ferma,
        // cosi' si puo' leggere con calma o cliccare per aprire la pagina.
        var POPUP_KEY   = 'lux_notif_popup';
        var POPUP_MS    = 5000;
        var POPUPS_MAX  = 3;
        var queue       = [];
        var showing     = false;

        function popupSeen() {
            try { var v = JSON.parse(localStorage.getItem(POPUP_KEY) || '[]'); return Array.isArray(v) ? v : []; }
            catch (e) { return []; }
        }
        function popupMark(id) {
            var ids = popupSeen();
            if (ids.indexOf(id) === -1) ids.push(id);
            try { localStorage.setItem(POPUP_KEY, JSON.stringify(ids.slice(-160))); } catch (e) {}
        }

        function announce(list) {
            var seen = popupSeen();
            var fresh = list.filter(function (n) {
                return n && n.id && seen.indexOf(n.id) === -1;
            }).slice(0, POPUPS_MAX);
            if (!fresh.length) return;
            fresh.reverse().forEach(function (n) { queue.push(n); });
            if (!showing) next();
        }

        function next() {
            var n = queue.shift();
            if (!n) { showing = false; return; }
            showing = true;
            popupMark(n.id);

            var box = document.getElementById('luxPopBox');
            if (!box) {
                box = document.createElement('div');
                box.id = 'luxPopBox';
                box.className = 'luxpop';
                box.setAttribute('role', 'status');
                box.setAttribute('aria-live', 'polite');
                document.body.appendChild(box);
            }

            var card = document.createElement('div');
            card.className = 'luxpop-card cat-' + esc(n.category || 'info');
            var tag  = '<span class="luxpop-cat">' + esc(n.category || 'info') + '</span>';
            var body = '<span class="luxpop-txt"><b>' + esc(n.title) + '</b><span>' + esc(n.message) + '</span></span>';
            card.innerHTML =
                  '<span class="luxpop-icon" aria-hidden="true">'
                + '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round">'
                + '<path d="M18 8.6a6 6 0 0 0-12 0c0 5-2.2 6.4-2.2 6.4h16.4S18 13.6 18 8.6z"></path>'
                + '<path d="M13.7 18.6a2 2 0 0 1-3.4 0"></path></svg></span>'
                + '<div class="luxpop-body">' + tag + body + '</div>'
                + '<button type="button" class="luxpop-close" aria-label="' + esc(LABEL) + '">'
                + '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><line x1="6" y1="6" x2="18" y2="18"></line><line x1="18" y1="6" x2="6" y2="18"></line></svg>'
                + '</button>'
                + '<span class="luxpop-bar"></span>';
            box.appendChild(card);

            var bar   = card.querySelector('.luxpop-bar');
            var left  = POPUP_MS;
            var timer = null;

            function start() {
                left = POPUP_MS;
                if (bar) { bar.style.transition = 'none'; bar.style.width = '100%'; }
                window.requestAnimationFrame(function () {
                    if (bar) { bar.style.transition = 'width ' + POPUP_MS + 'ms linear'; bar.style.width = '0%'; }
                });
                timer = window.setTimeout(close, POPUP_MS);
            }
            function pause() {
                if (timer) window.clearTimeout(timer);
                timer = null;
                if (bar) {
                    left = Math.max(0, left - ((window.performance && performance.now ? performance.now() : Date.now()) - startedAt));
                    bar.style.transition = 'none';
                    bar.style.width = Math.round((left / POPUP_MS) * 100) + '%';
                }
            }
            function close() {
                if (timer) window.clearTimeout(timer);
                card.classList.add('out');
                window.setTimeout(function () {
                    if (card.parentNode) card.parentNode.removeChild(card);
                    next();
                }, 260);
            }
            var startedAt = (window.performance && performance.now) ? performance.now() : Date.now();

            var closeBtn = card.querySelector('.luxpop-close');
            closeBtn.addEventListener('click', function (e) { e.stopPropagation(); e.preventDefault(); close(); });
            card.addEventListener('mouseenter', function () { pause(); });
            card.addEventListener('mouseleave', function () { start(); });
            card.addEventListener('click', function (e) {
                if (closeBtn.contains(e.target)) return;
                if (n.redirect) window.location.href = n.redirect;
                else close();
            });

            if (n.redirect) {
                card.setAttribute('role', 'link');
                card.setAttribute('tabindex', '0');
                card.addEventListener('keydown', function (e) { if (e.key === 'Enter') window.location.href = n.redirect; });
            }
            start();
        }

        function load() {
            // La copia statica non ha il campanello del server: le notifiche
            // si leggono sul sito, dove c'e' la sessione e c'e' il programma
            // che le sa leggere. Qui non si chiede niente a nessuno, cosi'
            // non si vedono richieste che finiscono nel vuoto.
            if (window.LUX_STATIC_PAGE) return;
            fetch((window.LUX_API_BASE || '') + '/api/notifications.php?limit=20&lang=' + encodeURIComponent(LANG), { cache: 'no-store' })
                .then(function (r) { return r.json(); })
                .then(function (d) {
                    if (d && d.success && Array.isArray(d.notifications)) {
                        items = d.notifications;
                        paint();
                        announce(items);
                    }
                })
                .catch(function () {});
        }
        function openPanel() {
            // Su schermo stretto il pannello e' fissato ai lati (vedi site.css):
            // il top va preso dal campanello, cosi' resta attaccato anche se
            // l'intestazione e' sticky o la pagina e' scrollata.
            if (window.innerWidth <= 720) {
                panel.style.top = Math.round(btn.getBoundingClientRect().bottom + 10) + 'px';
            } else {
                panel.style.top = '';
            }
            panel.hidden = false;
            btn.setAttribute('aria-expanded', 'true');
            var ids = readIds();
            items.forEach(function (n) { if (n.id && ids.indexOf(n.id) === -1) ids.push(n.id); });
            saveIds(ids);
            paint();
        }
        function closePanel() {
            panel.hidden = true;
            btn.setAttribute('aria-expanded', 'false');
        }
        btn.addEventListener('click', function (e) {
            e.stopPropagation();
            panel.hidden ? openPanel() : closePanel();
        });
        clearBt.addEventListener('click', function (e) {
            e.stopPropagation();
            saveIds(items.map(function (n) { return n.id; }));
            paint();
        });
        document.addEventListener('click', function (e) {
            if (!wrap.contains(e.target)) closePanel();
        });
        document.addEventListener('keydown', function (e) { if (e.key === 'Escape') closePanel(); });

        load();

        // Tempo reale: la revisione condivisa (assets/js/site.js) avvisa
        // appena cambia qualcosa — una notifica inviata dal pannello compare
        // subito, senza aspettare il prossimo giro. La risposta contiene gia'
        // la lista aggiornata, quindi non serve un'altra chiamata per
        // leggerla: prima ce n'erano due, una dietro l'altra.
        function attachRealtime() {
            if (!window.LUX_REALTIME || !window.LUX_REALTIME.on) {
                window.setTimeout(attachRealtime, 200);
                return;
            }
            window.LUX_REALTIME.on(function (d) {
                if (!d || !Array.isArray(d.notifications)) return;
                items = d.notifications;
                paint();
                announce(items);
            });
        }
        attachRealtime();
    })();
    </script>

    <div class="mobile-menu-overlay" id="mobileMenuOverlay"></div>

    <nav class="mobile-menu" id="mobileMenu" aria-label="Navigazione mobile">
        <div class="lang-group mobile" role="group" aria-label="<?= t('Lingua') ?>">
            <a href="?lang=it" class="lang-btn <?= $currentLang === 'it' ? 'active' : '' ?>">IT</a>
            <a href="?lang=en" class="lang-btn <?= $currentLang === 'en' ? 'active' : '' ?>">EN</a>
        </div>

        <a href="<?= $homeLink ?>" class="m-link <?= $currentFile === basename($homeLink) ? 'active' : '' ?>">
            <?= icon('home', 17, 'm-icon') ?>
            <?= t('Home') ?>
        </a>
        <a href="/bacheca.php" class="m-link <?= $currentFile === 'bacheca.php' ? 'active' : '' ?>">
            <?= icon('board', 17, 'm-icon') ?>
            <?= t('Bacheca') ?>
        </a>
        <a href="/linea-del-tempo.php" class="m-link <?= $currentFile === 'linea-del-tempo.php' ? 'active' : '' ?>">
            <?= icon('time', 17, 'm-icon') ?>
            <?= t('Linea del Tempo') ?>
        </a>

        <a href="/app.php" class="m-link <?= $currentFile === 'app.php' ? 'active' : '' ?>">
            <?= icon('smartphone', 17, 'm-icon') ?>
            <?= t('App') ?>
        </a>

        <a href="/codice-aperto.php" class="m-link <?= $currentFile === 'codice-aperto.php' ? 'active' : '' ?>">
            <?= icon('code', 17, 'm-icon') ?>
            <?= t('Open source') ?>
        </a>

        <div class="m-head"><?= t('Info') ?></div>
        <a href="/chi-siamo.php" class="m-link <?= $currentFile === 'chi-siamo.php' ? 'active' : '' ?>">
            <?= icon('info', 17, 'm-icon') ?>
            <?= t('Chi Siamo') ?>
        </a>
        <a href="/faq.php" class="m-link <?= $currentFile === 'faq.php' ? 'active' : '' ?>">
            <?= icon('help', 17, 'm-icon') ?>
            <?= t('FAQ') ?>
        </a>
        <a href="/assistenza.php" class="m-link <?= $currentFile === 'assistenza.php' ? 'active' : '' ?>">
            <?= icon('chat', 17, 'm-icon') ?>
            <?= t('Assistenza') ?>
        </a>
        <a href="/contattaci.php" class="m-link <?= $currentFile === 'contattaci.php' ? 'active' : '' ?>">
            <?= icon('mail', 17, 'm-icon') ?>
            <?= t('Contatti') ?>
        </a>
        <a href="/status.php" class="m-link <?= $currentFile === 'status.php' ? 'active' : '' ?>">
            <?= icon('pulse', 17, 'm-icon') ?>
            <?= t('Stato') ?>
        </a>
        <a href="/admin.php" class="m-link admin-link-mobile">
            <?= icon('settings', 17, 'm-icon') ?>
            <?= t('Admin') ?>
        </a>
    </nav>

<?php
}


// ---------- FOOTER ----------
function lux_footer(): void
{
    $isAppView = $GLOBALS['isAppView'] ?? null;


?>
<footer class="footer">
        <div class="container">
            <div class="footer-grid">
                <div class="footer-brand">
                    <h3><span style="display:inline-block;width:28px;height:28px;vertical-align:middle;border-radius:7px;margin-right:9px"><?= lux_logo(28) ?></span><span class="logo-text">L.U.X.<span>Verse</span></span></h3>
                    <p><?= t('A free AI ecosystem for creativity, productivity and innovation.') ?></p>
                </div>
                <div class="footer-col">
                    <h4><?= t('Info') ?></h4>
                    <a href="/bacheca.php"><?= t('Bacheca') ?></a>
                    <a href="/linea-del-tempo.php"><?= t('Linea del Tempo') ?></a>
                    <a href="/chi-siamo.php"><?= t('Chi Siamo') ?></a>
                    <a href="/faq.php"><?= t('FAQ') ?></a>
                    <a href="/assistenza.php"><?= t('Assistenza') ?></a>
                    <a href="/contattaci.php"><?= t('Contatti') ?></a>
                    <a href="/status.php"><?= t('Stato') ?></a>
                </div>
                <div class="footer-col">
                    <h4><?= t('Legali') ?></h4>
                    <a href="/privacy.php"><?= t('Privacy Policy') ?></a>
                    <a href="/terms.php"><?= t('Termini di Servizio') ?></a>
                    <a href="/codice-aperto.php"><?= t('Open source') ?></a>
                    <a href="/app.php"><?= t('App') ?></a>
                </div>
            </div>
            <div class="footer-bottom">
                <p><?= t('© 2026 L.U.X. Verse - Tutti i diritti riservati') ?></p>
                <div class="footer-social">
                    <a href="https://www.tiktok.com/@luxverse.c22.eu" target="_blank" rel="noopener" aria-label="TikTok"><?= icon('tiktok', 16) ?></a>
                    <a href="https://www.youtube.com/@luxverse.22web" target="_blank" rel="noopener" aria-label="YouTube"><?= icon('youtube', 16) ?></a>
                </div>
            </div>
        </div>
    </footer>

    <script src="/cookie-consent.js?v=<?= LUX_ASSET_V ?>" defer></script>
    <script src="/assets/js/site.js?v=<?= LUX_ASSET_V ?>" defer></script>

    <?php
    // ---- 39.1.1: le impostazioni che servono ai due script nuovi ----
    // Le icone servono al comando universale (Ctrl+K), le frasi ai riquadri
    // dello spazio personale. Passano da qui una volta per pagina, cosi' i
    // due file JS restano uguali in italiano e in inglese.
    $luxIconPaths = function_exists('lux_icon_paths') ? lux_icon_paths() : [];
    $luxCmdkT = [
        'Search or command'      => t('Search or command'),
        'Search tools, pages, notices or run a command...' => t('Search tools, pages, notices or run a command...'),
        'Searching...'           => t('Searching...'),
        'Nothing found. Try other words.' => t('Nothing found. Try other words.'),
        'to move'                => t('to move'),
        'to open'                => t('to open'),
        'to close'               => t('to close'),
        'Settings'               => t('Settings'),
        'Tools'                  => t('Tools'),
        'Tool'                   => t('Tool'),
        'Page'                   => t('Page'),
        'Notice'                 => t('Notice'),
        'Setting'                => t('Setting'),
        'My space'               => t('My space'),
        'Release roadmap'        => t('Release roadmap'),
        'Suggest a tool'         => t('Suggest a tool'),
        'Developer API'          => t('Developer API'),
        'History, favourites, saved results' => t('History, favourites, saved results'),
        'What is coming, in which order' => t('What is coming, in which order'),
        'Ideas and votes of the community' => t('Ideas and votes of the community'),
        'Keys, docs, examples'   => t('Keys, docs, examples'),
        'Recently used'          => t('Recently used'),
        'Easier reading'         => t('Easier reading'),
        'Rounder letters, wider lines' => t('Rounder letters, wider lines'),
        'Bigger text'            => t('Bigger text'),
        'Everything a little larger' => t('Everything a little larger'),
        'Read this page aloud'   => t('Read this page aloud'),
        'The browser reads the text' => t('The browser reads the text'),
        'Stop reading'           => t('Stop reading'),
        'Dictate'                => t('Dictate'),
    ];
    $luxSpaceT = [
        'Saved result'   => t('Saved result'),
        'badCode'        => t('That code is not valid. Check it and try again.'),
        'noCode'         => t('No code yet'),
        'linked'         => t('Linked'),
        'notLinked'      => t('Not linked'),
    ];

    // L'avviso su cookie e privacy (cookie-consent.js) e' un file a parte: le
    // frasi passano da qui, una volta per pagina, cosi' il file resta uno solo
    // in italiano e in inglese. Prima erano scritte in italiano dentro il file
    // JS, che apriva un riquadro a tutto schermo bloccando l'accesso: un muro
    // in italiano anche per chi leggeva il sito in inglese, e per dei cookie
    // tecnici (lo dice la pagina Privacy) un blocco non serve.
    $luxConsentT = [
        'title'  => t('Cookies and privacy'),
        'text'   => t('This site uses only technical cookies: no tracking, no advertising, no third-party cookies. You can read the {privacy} and the {terms}.', [
            'privacy' => '<a href="' . lux_url('/privacy.php') . '" style="color:#D8A93C;text-decoration:underline">' . t('Privacy Policy') . '</a>',
            'terms'   => '<a href="' . lux_url('/terms.php') . '" style="color:#D8A93C;text-decoration:underline">' . t('Terms of Service') . '</a>',
        ]),
        'accept' => t('Accept'),
        'close'  => t('Close'),
    ];
    ?>
    <script>
    window.LUX_ICON_PATHS = <?= json_encode($luxIconPaths, JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP) ?>;
    window.LUX_CMDK_T  = <?= json_encode($luxCmdkT, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP) ?>;
    window.LUX_SPACE_T = <?= json_encode($luxSpaceT, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP) ?>;
    window.LUX_CONSENT_T = <?= json_encode($luxConsentT, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP) ?>;
    </script>
<?php
    // Nelle copie statiche (vedi tools/build_static.php) non si caricano le
    // cose che parlano col server: lo Spazio personale, la chat della guida,
    // i voti e il service worker. Non e' un taglio di comodo: il server
    // riconosce le sue richieste dal cookie di sessione, e un cookie non
    // passa da un indirizzo all'altro (lo vuole la regola SameSite, ed e'
    // una cosa giusta). Una pagina statica che provasse a chiamare le API
    // riceverebbe la pagina di verifica dell'hosting invece dei dati.
    // Le pagine statiche servono a leggere: tutto il resto resta sul server,
    // dove i conti, le chiavi e le sessioni vivono davvero.
    $luxExport = defined('LUX_EXPORT') && LUX_EXPORT;
    if (!$luxExport) { ?>    <script src="/assets/js/lux-space.js?v=<?= LUX_ASSET_V ?>" defer></script>
<?php } ?>    <script src="/assets/js/lux-command.js?v=<?= LUX_ASSET_V ?>" defer></script>
<?php if (!$luxExport) { ?>    <script>
        if ('serviceWorker' in navigator && location.protocol === 'https:') {
            window.addEventListener('load', function () {
                // Il service worker riceve l'impronta degli asset: da quella
                // ricava il nome della sua cache, quindi quando un CSS o un JS
                // cambia, la cache vecchia si svuota da sola. Prima il nome
                // era scritto a mano dentro sw.js (v24) e andava ricordato a
                // ogni rilascio: se ci si dimenticava, chi aveva gia' aperto
                // il sito restava con i file di prima.
                navigator.serviceWorker.register('sw.js?v=' + <?= json_encode(LUX_ASSET_V) ?>).catch(function () {});
            });
        }
    </script>
<?php } ?>

    <?php
    // Interfaccia di piattaforma su OGNI pagina pubblica: L.U.X. GUIDE, profilo
    // locale, preferiti, voto e feedback (vedi includes/platform-ui.php).
    //
    // Anche sulle pagine di contenuto (le .html gia' pronte): sono pagine del
    // sito come le altre, allo stesso indirizzo, e senza questa pila su un
    // telefono non ci sarebbe NESSUN modo di aprire il comando di ricerca —
    // li' non esistono i tasti del computer. Le chiamate alle API usano
    // window.LUX_API_BASE, che vale l'indirizzo del sito quando la pagina e'
    // aperta sul sito (e resta inerte su una copia pubblicata altrove).
    // Sul pannello di amministrazione NON si mostra: li' c'e' gia' la chat
    // dentro il portale, e il pulsante coprirebbe i comandi.
    if (basename((string)($_SERVER['SCRIPT_NAME'] ?? '')) !== 'admin.php') {
        if (!function_exists('lux_platform_ui')) require_once __DIR__ . '/platform-ui.php';
        lux_platform_ui();
    }
    ?>
</body>
</html>

<?php
}



// ============================================================
// L.U.X. CORE — STATO DEI SISTEMI (telemetria reale)
// ============================================================
// Ogni controllo misura davvero qualcosa che esiste sul server (file, dati,
// configurazione, estensioni di PHP) e ne registra la latenza. Niente numeri
// inventati: se un dato non e' verificabile lo stato e' "na" e viene scritto
// il motivo. Il risultato e' in cache 60 secondi in data/status.json, cosi'
// la pagina non rifa' tutto il lavoro a ogni visita; ?fresh=1 la forza.
// ============================================================

/** Descrizione di un file per i controlli: esiste, quanti byte, da quando. */
function lux_core_file(string $path): array
{
    if (!is_file($path)) return ['exists' => false, 'size' => 0, 'mtime' => 0];
    return ['exists' => true, 'size' => (int)filesize($path), 'mtime' => (int)filemtime($path)];
}

/** Byte leggibili: 1,2 MB · 812 KB · 340 B */
function lux_core_bytes(int $bytes): string
{
    if ($bytes >= 1048576) return number_format($bytes / 1048576, 1, ',', '.') . ' MB';
    if ($bytes >= 1024)    return number_format($bytes / 1024, 0, ',', '.') . ' KB';
    return $bytes . ' B';
}

/** Quanti endpoint contiene il file unico delle API. */
function lux_core_endpoint_count(string $file): int
{
    if (!is_file($file)) return 0;
    $src = (string)file_get_contents($file);
    return (int)preg_match_all("/case \\\$__ep === '/", $src);
}

/** Quante voci di traduzione italiane contiene lang/lang.php (senza caricarlo). */
function lux_core_lang_entries(string $file): int
{
    if (!is_file($file)) return 0;
    $src = (string)file_get_contents($file);
    // Le voci italiane stanno fra "'it' =>" e l'inizio del blocco inglese.
    $start = strpos($src, "'it' =>");
    $end   = strpos($src, "'en' => array (");
    if ($start === false) return 0;
    $block = ($end === false || $end < $start) ? substr($src, $start) : substr($src, $start, $end - $start);
    return (int)preg_match_all("/=>/", $block);
}

/** Quante voci contiene il blocco inglese di lang/lang.php. */
function lux_core_lang_entries_en(string $file): int
{
    if (!is_file($file)) return 0;
    $src   = (string)file_get_contents($file);
    $start = strpos($src, "'en' => array (");
    if ($start === false) return 0;
    return (int)preg_match_all("/=>/", substr($src, $start));
}

/** Quante volte un'espressione regolare compare in un file (0 se assente). */
function lux_core_count(string $file, string $pattern): int
{
    if (!is_file($file)) return 0;
    return (int)preg_match_all($pattern, (string)file_get_contents($file));
}

/**
 * Scansiona i file pubblicati del sito: quanti sono e quanto pesano.
 * Esclude .git e gli script di sviluppo in tools/, che non vanno online.
 */
function lux_core_scan(string $root, array $skip = ['.git', 'tools', 'node_modules']): array
{
    if (!is_dir($root)) return ['count' => 0, 'bytes' => 0];
    $count = 0;
    $bytes = 0;
    $dir   = new RecursiveDirectoryIterator($root, FilesystemIterator::SKIP_DOTS);
    $only  = new RecursiveCallbackFilterIterator($dir, function ($f) use ($skip) {
        return !in_array($f->getFilename(), $skip, true);
    });
    foreach (new RecursiveIteratorIterator($only) as $f) {
        if ($f->isFile()) { $count++; $bytes += (int)$f->getSize(); }
    }
    return ['count' => $count, 'bytes' => $bytes];
}

/**
 * Report completo: sonde, indice di salute, telemetria e diario delle attivita'.
 * $fresh = true  ignora la cache.
 */
function lux_status_report(bool $fresh = false): array
{
    $root      = dirname(__DIR__);
    $cacheFile = $root . '/data/status.json';
    $ttl       = 60;

    if (!$fresh && is_file($cacheFile) && (time() - (int)filemtime($cacheFile)) < $ttl) {
        $cached = json_decode((string)file_get_contents($cacheFile), true);
        // La cache vale solo per la lingua con cui e' stata prodotta: i testi
        // delle sonde sono gia' tradotti, non si possono riusare in un'altra.
        if (is_array($cached) && isset($cached['checks'])
            && ($cached['lang'] ?? '') === currentLang()) {
            $cached['from_cache'] = true;
            $cached['cache_age']  = time() - (int)filemtime($cacheFile);
            return $cached;
        }
    }

    $t0     = microtime(true);
    $checks = [];

    /** Esegue una sonda e registra latenza + esito. */
    $probe = function (string $key, string $group, string $label, string $icon, callable $fn) use (&$checks) {
        $start = microtime(true);
        try {
            $res = $fn();
        } catch (Throwable $e) {
            $res = ['status' => 'down', 'detail' => 'Errore: ' . $e->getMessage()];
        }
        $checks[] = [
            'key'    => $key,
            'group'  => $group,
            'label'  => $label,
            'icon'   => $icon,
            'status' => (string)($res['status'] ?? 'ok'),
            'detail' => (string)($res['detail'] ?? ''),
            'ms'     => round((microtime(true) - $start) * 1000, 2),
        ];
    };

    // ---------- PIATTAFORMA ----------
    $probe('web', t('Piattaforma'), t('Sito web'), 'globe', function () use ($root) {
        $files = ['index.php', 'manifest.json', 'sitemap.xml', 'robots.txt'];
        $ok = 0;
        foreach ($files as $f) if (is_file($root . '/' . $f)) $ok++;
        return [
            'status' => $ok === count($files) ? 'ok' : ($ok ? 'warn' : 'down'),
            'detail' => t('{a}/{b} file pubblici presenti', ['a' => $ok, 'b' => count($files)]),
        ];
    });

    $probe('engine', t('Piattaforma'), t('Motore degli strumenti'), 'cpu', function () use ($root) {
        $f = lux_core_file($root . '/tool.php');
        // Si contano TUTTI gli strumenti raggiungibili: i 1.000 scritti a mano
        // piu' i 99.000 generati dal catalogo. Contare solo projects.json
        // farebbe dire alla pagina Stato 1.000 mentre il sito ne serve 100.000.
        $count = function_exists('lux_catalog_total') ? lux_catalog_total() : 0;
        return [
            'status' => $f['exists'] && $count > 0 ? 'ok' : 'down',
            'detail' => t('{n} strumenti in {size} (un unico file)',
                ['n' => number_format($count, 0, '.', '.'), 'size' => lux_core_bytes($f['size'])]),
        ];
    });

    $probe('api', t('Piattaforma'), t('API'), 'bolt', function () use ($root) {
        $f = lux_core_file($root . '/api/api.php');
        $n = lux_core_endpoint_count($root . '/api/api.php');
        return [
            'status' => $f['exists'] && $n > 0 ? 'ok' : 'down',
            'detail' => t('{n} endpoint in un unico file ({size})',
                ['n' => $n, 'size' => lux_core_bytes($f['size'])]),
        ];
    });

    $probe('assets', t('Piattaforma'), t('Interfaccia e asset'), 'grid', function () use ($root) {
        $files = ['assets/css/site.css', 'assets/js/site.js', 'cookie-consent.js',
                  'includes/lux.php', 'lang/lang.php'];
        $ok = 0;
        $bytes = 0;
        foreach ($files as $f) {
            $info = lux_core_file($root . '/' . $f);
            if ($info['exists']) { $ok++; $bytes += $info['size']; }
        }
        return [
            'status' => $ok === count($files) ? 'ok' : ($ok ? 'warn' : 'down'),
            'detail' => t('{a}/{b} file · {size}',
                ['a' => $ok, 'b' => count($files), 'size' => lux_core_bytes($bytes)]),
        ];
    });

    // ---------- DATI ----------
    $probe('data', t('Dati'), t('Archivio dati'), 'folder', function () use ($root) {
        $dir = $root . '/data';
        if (!is_dir($dir)) return ['status' => 'down', 'detail' => t('cartella data/ assente')];
        $probeFile = $dir . '/.write-test';
        $writable = @file_put_contents($probeFile, (string)time()) !== false;
        if ($writable) @unlink($probeFile);
        $json = glob($dir . '/*.json');
        return [
            'status' => $writable ? 'ok' : 'warn',
            'detail' => t('{n} archivi JSON · scrittura {state}', [
                'n' => count((array)$json),
                'state' => $writable ? t('consentita') : t('non consentita'),
            ]),
        ];
    });

    $probe('catalog', t('Dati'), t('Catalogo strumenti'), 'tile', function () use ($root) {
        $file = $root . '/data/projects.json';
        $data = json_decode((string)@file_get_contents($file), true);
        if (!is_array($data)) return ['status' => 'down', 'detail' => t('catalogo non leggibile')];
        // Generati + scritti a mano: e' il numero che vede chi naviga il sito.
        $keys = function_exists('lux_catalog_total') ? lux_catalog_total() : count($data);
        return [
            'status' => 'ok',
            'detail' => t('{n} strumenti · aggiornato {date}', [
                'n' => number_format($keys, 0, '.', '.'), 'date' => date('d/m/Y H:i', (int)@filemtime($file)),
            ]),
        ];
    });

    $probe('states', t('Dati'), t('Stato degli strumenti'), 'filter', function () use ($root) {
        $states = lux_states();
        $off = 0;
        $last = '';
        foreach ($states as $s) {
            if (($s['status'] ?? 'attivo') !== 'attivo') $off++;
            if (!empty($s['timestamp']) && $s['timestamp'] > $last) $last = $s['timestamp'];
        }
        return [
            'status' => 'ok',
            'detail' => t('{n} voci · {off} non attivi', ['n' => count($states), 'off' => $off])
                . ($last ? t(' · ultima modifica {when}', ['when' => $last]) : ''),
        ];
    });

    $probe('notices', t('Dati'), t('Bacheca'), 'board', function () use ($root) {
        $data = json_decode((string)@file_get_contents($root . '/data/notices.json'), true);
        if (!is_array($data)) return ['status' => 'warn', 'detail' => t('nessun avviso leggibile')];
        $last = '';
        foreach ($data as $n) if (!empty($n['date']) && $n['date'] > $last) $last = $n['date'];
        return ['status' => 'ok', 'detail' => t('{n} avvisi · ultimo {when}',
            ['n' => count($data), 'when' => ($last ?: 'n/d')])];
    });

    $probe('tickets', t('Dati'), t('Richieste di assistenza'), 'ticket', function () use ($root) {
        $open = 0;
        $oldest = '';
        $total = 0;
        foreach (['messages.json', 'assistance.json'] as $f) {
            $data = json_decode((string)@file_get_contents($root . '/data/' . $f), true);
            if (!is_array($data)) continue;
            foreach ($data as $m) {
                $total++;
                $st = (string)($m['status'] ?? 'In Attesa');
                if (stripos($st, 'attesa') !== false || stripos($st, 'lavorazione') !== false) {
                    $open++;
                    $d = (string)($m['date'] ?? '');
                    if ($d !== '' && ($oldest === '' || $d < $oldest)) $oldest = $d;
                }
            }
        }
        $h = 0;
        if ($oldest !== '') $h = max(0, (int)round((time() - strtotime($oldest)) / 3600));
        return [
            'status' => 'ok',
            'detail' => t('{open} in coda su {total} totali', ['open' => $open, 'total' => $total])
                . ($open ? t(' · la più vecchia da {h}h', ['h' => $h]) : ''),
        ];
    });

    $probe('visits', t('Dati'), t('Contatore visite'), 'pulse', function () use ($root) {
        $v = json_decode((string)@file_get_contents($root . '/data/visits.json'), true);
        if (!is_array($v)) return ['status' => 'warn', 'detail' => t('nessun dato di visita')];
        return [
            'status' => 'ok',
            'detail' => t('{n} visite · oggi {today}', [
                'n' => number_format((int)($v['total'] ?? 0), 0, ',', '.'),
                'today' => number_format((int)($v['daily'][date('Y-m-d')] ?? 0), 0, ',', '.'),
            ]),
        ];
    });

    // ---------- SERVIZI ----------
    $probe('ai', t('Servizi'), t('Motore AI (Groq)'), 'sparkles', function () {
        if (GROQ_API_KEY === '') {
            return ['status' => 'warn', 'detail' => t('chiave non configurata: funzionano gli strumenti istantanei')];
        }
        return ['status' => 'ok', 'detail' => t('configurato · modello {model}', ['model' => GROQ_MODEL])];
    });

    $probe('app', t('Servizi'), t('App Android'), 'smartphone', function () use ($root) {
        $apk = lux_core_file($root . '/LUXVerse.apk');
        if (!$apk['exists']) return ['status' => 'warn', 'detail' => t('APK non presente in questa copia')];
        return [
            'status' => 'ok',
            'detail' => t('APK {size} · aggiornato {date}', [
                'size' => lux_core_bytes($apk['size']), 'date' => date('d/m/Y', $apk['mtime']),
            ]),
        ];
    });

    $probe('lang', t('Servizi'), t('Traduzioni'), 'translate', function () use ($root) {
        $n = lux_core_lang_entries($root . '/lang/lang.php');
        return [
            'status' => $n > 0 ? 'ok' : 'down',
            'detail' => t('{n} voci italiane in un unico file', ['n' => $n]),
        ];
    });

    // ---------- SICUREZZA ----------
    $probe('secrets', t('Sicurezza'), t('Segreti separati'), 'lock', function () {
        // Pagina pubblica: si dice SE la cassaforte e' attiva, mai dove sta
        // di preciso ne' tanto meno cosa contiene.
        $s = lux_secrets_status();
        if (!$s['loaded']) {
            return ['status' => 'down', 'detail' => t('Vault missing: the admin login is closed')];
        }
        if ($s['place'] !== 'vault') {
            return ['status' => 'warn', 'detail' => t('Secrets still in the public root: they must move into the vault')];
        }
        return [
            'status' => 'ok',
            'detail' => t('Vault active: password, token and AI key are kept apart from the code'),
        ];
    });

    $probe('guards', t('Sicurezza'), t('Protezioni server'), 'shield', function () use ($root) {
        $files = ['.htaccess', 'data/.htaccess', 'api/.htaccess'];
        $ok = 0;
        foreach ($files as $f) if (is_file($root . '/' . $f)) $ok++;
        return [
            'status' => $ok === count($files) ? 'ok' : 'warn',
            'detail' => t('{a}/{b} regole: dati e cartelle interne non raggiungibili',
                ['a' => $ok, 'b' => count($files)]),
        ];
    });

    $probe('https', t('Sicurezza'), t('Connessione cifrata'), 'key', function () {
        $https = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
            || (($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https');
        return [
            'status' => $https ? 'ok' : 'na',
            'detail' => $https
                ? t('HTTPS attivo su questa richiesta')
                : t('richiesta in HTTP locale: da verificare in produzione'),
        ];
    });

    // ---------- PRESTAZIONI ----------
    $probe('php', t('Prestazioni'), t('Runtime PHP'), 'code', function () {
        return ['status' => 'ok', 'detail' => t('PHP {v} · {sapi} · memoria {mem}', [
            'v' => PHP_VERSION, 'sapi' => PHP_SAPI, 'mem' => ini_get('memory_limit'),
        ])];
    });

    $probe('cache', t('Prestazioni'), t('Cache opcode'), 'bolt', function () {
        $on = function_exists('opcache_get_status') && ini_get('opcache.enable');
        return [
            'status' => $on ? 'ok' : 'na',
            'detail' => $on
                ? t('OPcache attivo: il file unico viene compilato una volta sola')
                : t('OPcache non rilevabile su questo server'),
        ];
    });

    // ---------- FLUSSI COMPLETI DEL SITO ----------
    // Ogni sonda qui sotto corrisponde a un percorso che una persona puo'
    // davvero fare sul sito. Leggono solo file pubblici e dicono un fatto
    // misurabile: nessun dato privato, nessun contenuto dei messaggi.
    $read = function (string $rel) use ($root): string {
        $p = $root . '/' . $rel;
        return is_file($p) ? (string)file_get_contents($p) : '';
    };
    $jsonCount = function (string $file) use ($root) {
        $d = json_decode((string)@file_get_contents($root . '/data/' . $file), true);
        return is_array($d) ? count($d) : 0;
    };

    $probe('info_pages', t('Piattaforma'), t('Pagine informative'), 'file-text', function () use ($read) {
        $src = $read('info.php');
        $sections = ['chi-siamo', 'faq', 'assistenza', 'contattaci', 'status', 'privacy', 'terms'];
        $ok = 0;
        foreach ($sections as $s) {
            if (strpos($src, "if (\$__info === '" . $s . "')") !== false) $ok++;
        }
        return [
            'status' => $ok === count($sections) ? 'ok' : ($ok ? 'warn' : 'down'),
            'detail' => t('{a}/{b} sezioni attive: chi siamo, FAQ, assistenza, contatti, stato, privacy, termini',
                ['a' => $ok, 'b' => count($sections)]),
        ];
    });

    $probe('indexing', t('Piattaforma'), t('Indicizzazione e ricerca'), 'search', function () use ($root, $read) {
        $loc    = (int)preg_match_all('/<loc>/', $read('sitemap.xml'));
        $robots = strpos($read('robots.txt'), 'Sitemap') !== false;
        $google = is_file($root . '/google702cac99a3936431.html');
        $ok = ($loc > 0 ? 1 : 0) + ($robots ? 1 : 0) + ($google ? 1 : 0);
        return [
            'status' => $ok === 3 ? 'ok' : ($ok ? 'warn' : 'down'),
            'detail' => t('sitemap con {n} indirizzi · robots.txt {r} · verifica Google {g}', [
                'n' => $loc,
                'r' => $robots ? t('presente') : t('assente'),
                'g' => $google ? t('presente') : t('assente'),
            ]),
        ];
    });

    $probe('pwa', t('Piattaforma'), t('App installabile (PWA)'), 'smartphone', function () use ($root, $read) {
        $manifest = json_decode($read('manifest.json'), true);
        $ok = 0;
        if (is_array($manifest) && !empty($manifest['name'])) $ok++;
        if (is_file($root . '/sw.js')) $ok++;
        if (is_file($root . '/offline.html')) $ok++;
        return [
            'status' => $ok === 3 ? 'ok' : ($ok ? 'warn' : 'down'),
            'detail' => t('manifest, service worker e pagina offline: {a}/{b} presenti', ['a' => $ok, 'b' => 3]),
        ];
    });

    $probe('routing', t('Piattaforma'), t('Instradamento degli indirizzi'), 'link', function () use ($root) {
        $n = lux_core_count($root . '/.htaccess', '/RewriteRule/');
        return [
            'status' => $n >= 3 ? 'ok' : ($n ? 'warn' : 'down'),
            'detail' => t('{n} regole di riscrittura: pagine informative, strumenti e vecchie URL', ['n' => $n]),
        ];
    });

    $probe('json_integrity', t('Dati'), t('Integrità degli archivi'), 'check-circle', function () use ($root) {
        $files = (array)glob($root . '/data/*.json');
        $ok = 0;
        $bytes = 0;
        foreach ($files as $f) {
            $bytes += (int)@filesize($f);
            if (json_decode((string)@file_get_contents($f), true) !== null) $ok++;
        }
        return [
            'status' => ($files && $ok === count($files)) ? 'ok' : ($ok ? 'warn' : 'down'),
            'detail' => t('{a}/{b} file JSON letti senza errori · {size} di dati', [
                'a' => $ok, 'b' => count($files), 'size' => lux_core_bytes($bytes),
            ]),
        ];
    });

    $probe('categories', t('Dati'), t('Categorie del catalogo'), 'filter', function () use ($root) {
        // Conteggio per categoria sull'intero catalogo (generati + scritti a mano),
        // non solo sui 1.000 di projects.json.
        $c = function_exists('lux_catalog_counts_all') ? lux_catalog_counts_all() : [];
        if (!$c) {
            $d = json_decode((string)@file_get_contents($root . '/data/projects.json'), true);
            if (!is_array($d) || !$d) return ['status' => 'warn', 'detail' => t('catalogo non leggibile')];
            foreach ($d as $p) { $k = (string)($p['category'] ?? 'altro'); $c[$k] = ($c[$k] ?? 0) + 1; }
        }
        return [
            'status' => count($c) >= 3 ? 'ok' : 'warn',
            'detail' => t('{n} categorie · da {min} a {max} strumenti ciascuna', [
                'n' => count($c),
                'min' => number_format(min($c), 0, '.', '.'),
                'max' => number_format(max($c), 0, '.', '.'),
            ]),
        ];
    });

    $probe('activity_log', t('Dati'), t('Registro attività'), 'journal', function () use ($root) {
        $d = json_decode((string)@file_get_contents($root . '/data/logs.json'), true);
        if (!is_array($d) || !$d) return ['status' => 'warn', 'detail' => t('nessun evento registrato')];
        $last = '';
        foreach ($d as $l) { $x = (string)($l['timestamp'] ?? ''); if ($x > $last) $last = $x; }
        return ['status' => 'ok', 'detail' => t('{n} eventi registrati · ultimo {when}',
            ['n' => count($d), 'when' => ($last ?: 'n/d')])];
    });

    $probe('community', t('Dati'), t('Archivi della community'), 'chat', function () use ($jsonCount) {
        $chat = $jsonCount('chat.json');
        $posts = $jsonCount('posts.json');
        return [
            'status' => ($chat + $posts) > 0 ? 'ok' : 'warn',
            'detail' => t('{a} messaggi in chat · {b} voci in classifica · {c} contenuti pubblicati', [
                'a' => $chat, 'b' => $jsonCount('leaderboard.json'), 'c' => $posts,
            ]),
        ];
    });

    $probe('coverage', t('Servizi'), t('Copertura delle lingue'), 'globe', function () use ($root) {
        $it = lux_core_lang_entries($root . '/lang/lang.php');
        $en = lux_core_lang_entries_en($root . '/lang/lang.php');
        return [
            'status' => ($it > 0 && count(SUPPORTED_LANGS) >= 2) ? 'ok' : 'warn',
            'detail' => t('{it} voci italiane · {en} voci inglesi · lingue attive: {langs}', [
                'it' => $it, 'en' => $en, 'langs' => implode(', ', SUPPORTED_LANGS),
            ]),
        ];
    });

    $probe('instant_tools', t('Servizi'), t('Strumenti istantanei'), 'bolt', function () use ($root) {
        $n = lux_core_count($root . '/tool.php', "~'run' =>~");
        return [
            'status' => $n > 0 ? 'ok' : 'warn',
            'detail' => t("{n} strumenti calcolano sul server, senza attendere l'AI", ['n' => $n]),
        ];
    });

    $probe('contact_channels', t('Servizi'), t('Canali di contatto'), 'mail', function () use ($root, $read) {
        $ok = 0;
        if (strpos($read('info.php'), 'mailto:') !== false) $ok++;
        if (is_file($root . '/data/messages.json')) $ok++;
        if (is_file($root . '/data/assistance.json')) $ok++;
        return [
            'status' => $ok === 3 ? 'ok' : ($ok ? 'warn' : 'down'),
            'detail' => t('email, assistenza e contatti attivi · risposta prevista entro 24-48 ore'),
        ];
    });

    $probe('admin_area', t('Sicurezza'), t('Area amministrativa'), 'user', function () use ($root) {
        $f  = lux_core_file($root . '/admin.php');
        $pw = defined('ADMIN_PASSWORD_HASH') && ADMIN_PASSWORD_HASH !== '';
        return [
            'status' => $f['exists'] && $pw ? 'ok' : 'warn',
            'detail' => t('accesso riservato con password: {state}',
                ['state' => $pw ? t('protetto') : t('assente')]),
        ];
    });

    $probe('reserved_paths', t('Sicurezza'), t('Percorsi riservati'), 'eye', function () use ($read) {
        $ht = $read('.htaccess');
        $blocked = 0;
        foreach (['/data/', '/tools/', '.git/', 'config'] as $needle) {
            if (strpos($ht, $needle) !== false) $blocked++;
        }
        return [
            'status' => $blocked >= 3 ? 'ok' : ($blocked ? 'warn' : 'down'),
            'detail' => t('data/, tools/, configurazione e .git esclusi dalla pubblicazione'),
        ];
    });

    $probe('status_cache', t('Prestazioni'), t('Cache dello stato'), 'time', function () use ($root) {
        $f = $root . '/data/status.json';
        if (!is_file($f)) return ['status' => 'na', 'detail' => t('nessun controllo salvato: verrà creato al primo accesso')];
        return [
            'status' => 'ok',
            'detail' => t('ultimo controllo salvato {age}s fa · valido per {ttl}s', [
                'age' => max(0, time() - (int)filemtime($f)), 'ttl' => 60,
            ]),
        ];
    });

    $probe('weight', t('Prestazioni'), t('Peso del sito'), 'download', function () use ($root) {
        $scan = lux_core_scan($root);
        $gen  = function_exists('lux_catalog_generated_count') ? lux_catalog_generated_count() : 0;
        return [
            'status' => 'ok',
            'detail' => $gen > 0
                ? t('{n} file pubblicati · {size} in totale · {tools} strumenti senza un solo file in più', [
                    'n' => $scan['count'], 'size' => lux_core_bytes($scan['bytes']),
                    'tools' => number_format($gen, 0, '.', '.'),
                  ])
                : t('{n} file pubblicati · {size} in totale, strumenti compresi', [
                    'n' => $scan['count'], 'size' => lux_core_bytes($scan['bytes']),
                  ]),
        ];
    });

    // ---------- FLUSSI PUBBLICI (una sonda per ogni percorso dell'utente) ----------
    $probe('flow_home', t('Flussi pubblici'), t('Apertura della home'), 'home', function () use ($root) {
        $n = function_exists('lux_catalog_total') ? lux_catalog_total() : 0;
        return [
            'status' => $n > 0 ? 'ok' : 'down',
            'detail' => t('catalogo caricato: {n} strumenti pronti in vetrina',
                ['n' => number_format($n, 0, '.', '.')]),
        ];
    });

    $probe('flow_catalog', t('Flussi pubblici'), t('Esplorazione del catalogo'), 'grid', function () use ($root) {
        $n = function_exists('lux_catalog_total') ? lux_catalog_total() : 0;
        $c = function_exists('lux_catalog_counts_all') ? count(lux_catalog_counts_all()) : 0;
        if ($n <= 0 || $c <= 0) return ['status' => 'down', 'detail' => t('catalogo non leggibile')];
        return ['status' => 'ok', 'detail' => t('{n} schede navigabili in {c} categorie',
            ['n' => number_format($n, 0, '.', '.'), 'c' => $c])];
    });

    $probe('flow_search', t('Flussi pubblici'), t('Ricerca e filtri'), 'search', function () use ($root) {
        $d = json_decode((string)@file_get_contents($root . '/data/projects.json'), true);
        if (!is_array($d) || !$d) return ['status' => 'down', 'detail' => t('catalogo non leggibile')];
        // Gli strumenti scritti a mano si controllano uno per uno; quelli
        // generati hanno nome e descrizione per costruzione (catalogo.php).
        $ok = 0;
        foreach ($d as $p) {
            if (!empty($p['name']) && (!empty($p['description_it']) || !empty($p['description_en']))) $ok++;
        }
        $gen = function_exists('lux_catalog_generated_count') ? lux_catalog_generated_count() : 0;
        $tot = $ok + $gen;
        return [
            'status' => $ok === count($d) ? 'ok' : 'warn',
            'detail' => t('{a}/{b} strumenti hanno nome e descrizione per la ricerca', [
                'a' => number_format($tot, 0, '.', '.'), 'b' => number_format($tot, 0, '.', '.'),
            ]),
        ];
    });

    $probe('flow_tool', t('Flussi pubblici'), t('Apertura di uno strumento'), 'play', function () use ($root) {
        $ded = lux_core_count($root . '/tool.php', "~'desc_it' =>~");
        $all = function_exists('lux_catalog_total') ? lux_catalog_total() : 0;
        return [
            'status' => $all > 0 ? 'ok' : 'down',
            'detail' => t('{n} schede dedicate + {a} create automaticamente dal catalogo',
                ['n' => $ded, 'a' => number_format(max(0, $all - $ded), 0, '.', '.')]),
        ];
    });

    $probe('flow_instant', t('Flussi pubblici'), t('Calcolo istantaneo'), 'bolt', function () use ($root) {
        $n = lux_core_count($root . '/tool.php', "~'run' =>~");
        return [
            'status' => $n > 0 ? 'ok' : 'warn',
            'detail' => t('{n} strumenti rispondono senza AI, in un istante', ['n' => $n]),
        ];
    });

    $probe('flow_notices', t('Flussi pubblici'), t('Lettura della bacheca'), 'board', function () use ($root) {
        $d = json_decode((string)@file_get_contents($root . '/data/notices.json'), true);
        if (!is_array($d)) return ['status' => 'warn', 'detail' => t('nessun avviso leggibile')];
        $last = '';
        foreach ($d as $nn) { $x = (string)($nn['date'] ?? ''); if ($x > $last) $last = $x; }
        return [
            'status' => $d ? 'ok' : 'warn',
            'detail' => t('{n} avvisi pubblicati · ultimo {when}', ['n' => count($d), 'when' => ($last ?: 'n/d')]),
        ];
    });

    $probe('flow_faq', t('Flussi pubblici'), t('Consultazione delle FAQ'), 'help', function () use ($root) {
        $n = lux_core_count($root . '/info.php', '~class="faq-item~');
        return [
            'status' => $n > 0 ? 'ok' : 'warn',
            'detail' => t('{n} risposte disponibili, con ricerca per parola', ['n' => $n]),
        ];
    });

    $probe('flow_support', t('Flussi pubblici'), t('Invio di una richiesta'), 'send', function () use ($root, $jsonCount) {
        $w = is_dir($root . '/data') && is_writable($root . '/data');
        $n = $jsonCount('messages.json') + $jsonCount('assistance.json') + $jsonCount('tickets.json');
        return [
            'status' => $w ? 'ok' : 'warn',
            'detail' => t('archivio richieste scrivibile · {n} richieste ricevute', ['n' => $n]),
        ];
    });

    $probe('flow_track', t('Flussi pubblici'), t('Tracciamento di un ticket'), 'pin', function () use ($jsonCount) {
        $n = $jsonCount('tickets.json') + $jsonCount('assistance.json');
        return [
            'status' => 'ok',
            'detail' => t('ricerca per codice attiva su {n} richieste', ['n' => $n]),
        ];
    });

    $probe('flow_contact', t('Flussi pubblici'), t('Invio di un messaggio'), 'mail', function () use ($root, $jsonCount) {
        $w = is_writable($root . '/data');
        return [
            'status' => $w ? 'ok' : 'warn',
            'detail' => t('archivio messaggi scrivibile · {n} messaggi ricevuti', ['n' => $jsonCount('messages.json')]),
        ];
    });

    $probe('flow_lang', t('Flussi pubblici'), t('Cambio lingua'), 'translate', function () {
        return [
            'status' => count(SUPPORTED_LANGS) >= 2 ? 'ok' : 'warn',
            'detail' => t('italiano e inglese completi su tutte le pagine'),
        ];
    });

    $probe('flow_cookie', t('Flussi pubblici'), t('Consenso cookie'), 'cookie', function () use ($root) {
        $f = lux_core_file($root . '/cookie-consent.js');
        return [
            'status' => $f['exists'] ? 'ok' : 'warn',
            'detail' => t('avviso gestito da un file dedicato, senza cookie di tracciamento'),
        ];
    });

    $probe('flow_offline', t('Flussi pubblici'), t('Uso senza connessione'), 'cloud', function () use ($root) {
        $ok = 0;
        if (is_file($root . '/offline.html')) $ok++;
        if (is_file($root . '/sw.js')) $ok++;
        return [
            'status' => $ok === 2 ? 'ok' : ($ok ? 'warn' : 'down'),
            'detail' => t('pagina offline e cache del servizio: {a}/{b} presenti', ['a' => $ok, 'b' => 2]),
        ];
    });

    $probe('flow_redirect', t('Flussi pubblici'), t('Vecchi indirizzi'), 'link', function () use ($read) {
        $ok = strpos($read('.htaccess'), 'progetti/') !== false;
        return [
            'status' => $ok ? 'ok' : 'warn',
            'detail' => t('i link /progetti/... continuano a funzionare'),
        ];
    });

    // ---------- ESITO ----------
    $weights  = ['ok' => 100, 'warn' => 62, 'down' => 0];
    $sum = 0;
    $n = 0;
    foreach ($checks as $c) {
        if (!isset($weights[$c['status']])) continue;
        $sum += $weights[$c['status']];
        $n++;
    }
    $health = $n ? (int)round($sum / $n) : 0;
    $down   = 0;
    $warn   = 0;
    foreach ($checks as $c) {
        if ($c['status'] === 'down') $down++;
        elseif ($c['status'] === 'warn') $warn++;
    }
    $state = $down > 0 ? 'down' : ($warn > 0 ? 'warn' : 'ok');

    // Presenza del servizio misurata sulle visite reali (non e' un pinger).
    $visits = json_decode((string)@file_get_contents($root . '/data/visits.json'), true);
    $presence = ['first' => '', 'last' => '', 'days_active' => 0, 'days_span' => 0, 'percent' => 0];
    if (is_array($visits) && !empty($visits['daily']) && is_array($visits['daily'])) {
        $days = array_keys($visits['daily']);
        sort($days);
        $presence['first'] = (string)reset($days);
        $presence['last']  = (string)end($days);
        $presence['days_active'] = count($days);
        $span = max(1, (int)floor((strtotime($presence['last']) - strtotime($presence['first'])) / 86400) + 1);
        $presence['days_span'] = $span;
        $presence['percent'] = (int)round(min(100, $presence['days_active'] / $span * 100));
    }

    // Diario: ultime attivita' reali (log, avvisi, stati).
    $timeline = [];
    $logs = json_decode((string)@file_get_contents($root . '/data/logs.json'), true);
    if (is_array($logs)) {
        foreach (array_slice($logs, -60) as $l) {
            $timeline[] = [
                'when'   => (string)($l['timestamp'] ?? ''),
                'label'  => (string)($l['action'] ?? 'attività'),
                'detail' => (string)($l['details'] ?? ''),
            ];
        }
    }
    $notices = json_decode((string)@file_get_contents($root . '/data/notices.json'), true);
    if (is_array($notices)) {
        $isEn = (currentLang() === 'en');
        foreach ($notices as $nn) {
            // Il titolo segue la lingua della pagina: in inglese si usa
            // title_en (con ripiego sul campo generico), in italiano title_it.
            $title = $isEn
                ? (string)($nn['title_en'] ?? ($nn['title'] ?? ''))
                : (string)($nn['title_it'] ?? ($nn['title'] ?? ''));
            $timeline[] = [
                'when'   => (string)($nn['date'] ?? ''),
                'label'  => t('Avviso in bacheca'),
                'detail' => $title,
            ];
        }
    }
    foreach (lux_states() as $k => $s) {
        if (empty($s['timestamp'])) continue;
        $timeline[] = [
            'when'   => (string)$s['timestamp'],
            'label'  => t('Stato aggiornato'),
            'detail' => $k . ' → ' . ($s['status'] ?? 'attivo'),
        ];
    }
    usort($timeline, function ($a, $b) { return strcmp($b['when'], $a['when']); });
    $timeline = array_slice($timeline, 0, 12);

    $report = [
        'generated'   => date('Y-m-d H:i:s'),
        'lang'        => currentLang(),
        'cache_age'   => 0,
        'from_cache'  => false,
        'ttl'         => $ttl,
        'duration_ms' => round((microtime(true) - $t0) * 1000, 2),
        'health'      => $health,
        'state'       => $state,
        'counters'    => ['ok' => $n - $down - $warn, 'warn' => $warn, 'down' => $down, 'total' => count($checks)],
        'checks'      => $checks,
        'timeline'    => $timeline,
        'presence'    => $presence,
        'telemetry'   => [
            'php'        => PHP_VERSION,
            'server'     => (string)($_SERVER['SERVER_SOFTWARE'] ?? 'server locale'),
            'sapi'       => PHP_SAPI,
            'memory'     => ini_get('memory_limit'),
            'peak'       => lux_core_bytes((int)memory_get_peak_usage(true)),
            'opcache'    => (function_exists('opcache_get_status') && ini_get('opcache.enable')) ? t('attiva') : t('non rilevabile'),
            'timezone'   => date_default_timezone_get(),
            'now'        => date('d/m/Y H:i:s'),
        ],
    ];

    if (is_dir($root . '/data') || @mkdir($root . '/data', 0755, true)) {
        @file_put_contents($cacheFile, json_encode($report, JSON_UNESCAPED_UNICODE), LOCK_EX);
    }
    return $report;
}

/**
 * Riepilogo per il badge in footer: legge SOLO la cache, senza rifare le
 * sonde (il footer sta su ogni pagina: non deve costare nulla).
 */
function lux_status_badge(): array
{
    $file = dirname(__DIR__) . '/data/status.json';
    $empty = ['health' => 0, 'state' => 'unknown', 'issues' => 0, 'age' => 0, 'known' => false];
    if (!is_file($file)) return $empty;
    $data = json_decode((string)@file_get_contents($file), true);
    if (!is_array($data) || !isset($data['health'])) return $empty;
    $counters = $data['counters'] ?? [];
    return [
        'health' => (int)$data['health'],
        'state'  => (string)($data['state'] ?? 'ok'),
        'issues' => (int)($counters['warn'] ?? 0) + (int)($counters['down'] ?? 0),
        'age'    => time() - (int)@filemtime($file),
        'known'  => true,
    ];
}
// ============================================================
// L.U.X. SUPPORT — CODA, TRIAGE E DIAGNOSTICA
// ============================================================
// Strumenti condivisi da Assistenza e Contatti:
//   lux_support_queue()      quante richieste sono aperte, da quanto, posizione
//   lux_support_triage()     priorita', tempi di risposta e punteggio reale
//   lux_support_diagnostics() quello che il server sa della richiesta
//   lux_support_timeline()   cronologia leggibile di una richiesta
// Nessun numero inventato: se un dato non esiste, la funzione lo dice.
// ============================================================

/** Tutte le richieste (assistenza + contatti) con la loro origine. */
function lux_support_all(): array
{
    $root = dirname(__DIR__);
    $out  = [];
    foreach (['assistance.json' => 'assistenza', 'messages.json' => 'contatti'] as $file => $origin) {
        $data = json_decode((string)@file_get_contents($root . '/data/' . $file), true);
        if (!is_array($data)) continue;
        foreach ($data as $row) {
            if (!is_array($row)) continue;
            $row['_origin'] = $origin;
            $out[] = $row;
        }
    }
    return $out;
}

/** Una richiesta e' ancora aperta se non e' risolta/archiviata. */
function lux_support_is_open(array $row): bool
{
    $status = (string)($row['status'] ?? 'In Attesa');
    return stripos($status, 'risolto') === false && stripos($status, 'archiviato') === false;
}

/**
 * Stato reale della coda: richieste aperte, la piu' vecchia, i tempi medi
 * (calcolati solo su richieste concluse che hanno una data di chiusura) e la
 * posizione che prenderebbe una nuova richiesta.
 */
function lux_support_queue(): array
{
    $rows   = lux_support_all();
    $open   = 0;
    $oldest = null;
    $durations = [];

    foreach ($rows as $row) {
        if (lux_support_is_open($row)) {
            $open++;
            $when = strtotime((string)($row['date'] ?? ''));
            if ($when && ($oldest === null || $when < $oldest)) $oldest = $when;
        } else {
            $start = strtotime((string)($row['date'] ?? ''));
            $end   = strtotime((string)($row['resolved_at'] ?? ($row['updated'] ?? '')));
            if ($start && $end && $end > $start) $durations[] = ($end - $start) / 3600;
        }
    }

    $avg = null;
    if ($durations) $avg = round(array_sum($durations) / count($durations), 1);

    return [
        'open'          => $open,
        'total'         => count($rows),
        'resolved'      => count($durations),
        'oldest_hours'  => $oldest ? round((time() - $oldest) / 3600, 1) : 0,
        'avg_hours'     => $avg,
        'position'      => $open + 1,
        'checked'       => date('Y-m-d H:i:s'),
    ];
}

/**
 * Triage automatico: trasforma categoria, urgenza e testo in una priorita'
 * motivata, in un tempo di risposta e in un punteggio di completezza.
 * Le regole sono le stesse del pannello dal vivo (assets/js/site.js), quindi
 * quello che l'utente vede mentre scrive e' quello che riceve il server.
 */
function lux_support_triage(array $in): array
{
    $category = (string)($in['category'] ?? ($in['type'] ?? ''));
    $urgency  = (string)($in['urgency'] ?? '');
    $subject  = (string)($in['subject'] ?? '');
    $message  = (string)($in['message'] ?? '');
    $email    = (string)($in['email'] ?? '');
    $page     = (string)($in['page'] ?? '');

    $score = 0;
    $why   = [];

    // Completezza della segnalazione (max 45 punti).
    $len = strlen(trim($message));   // byte: la soglia conta la sostanza, non gli accenti
    if ($len >= 40)  { $score += 15; } else { $why[] = t('il messaggio è molto breve'); }
    if ($len >= 160) { $score += 15; } else { $why[] = t('mancano dettagli: aggiungi cosa è successo passo per passo'); }
    if ($len >= 420) { $score += 15; }
    if ($subject !== '')  $score += 5;
    if ($email !== '' && filter_var($email, FILTER_VALIDATE_EMAIL)) { $score += 10; } else { $why[] = t('email non valida: non potremmo risponderti'); }
    if (preg_match('/https?:\/\/|\.php|passo|step|risultato|schermata|telefono|browser/i', $message)) {
        $score += 10;
    } else {
        $why[] = t('indica la pagina e il dispositivo usato');
    }
    if ($score > 100) $score = 100;

    // Priorita' (P1 massima, P4 minima).
    $base = 4;
    $catMap = [
        'Reclamo'                => 2,
        'Problemi Account'       => 2,
        'Privacy'                => 1,
        'Segnalazione Bug'       => 2,
        'Assistenza Tecnica'     => 3,
        'Richiesta Informazioni' => 4,
        'Suggerimento'           => 4,
        'Altro'                  => 4,
    ];
    foreach ($catMap as $key => $level) {
        if (stripos($category, $key) !== false || stripos($category, strtolower($key)) !== false) {
            $base = $level;
            break;
        }
    }
    if (stripos($urgency, 'bloccante') !== false || stripos($urgency, 'urgente') !== false) $base = min($base, 1);
    elseif (stripos($urgency, 'alta') !== false) $base = min($base, 2);

    $text = $subject . ' ' . $message;
    $redFlags = [
        '/non funziona|non parte|non si apre|non carica|errore|crash|bloccato/i' => t('segnalato un blocco o un errore'),
        '/perso|perdita|cancellat|spariti/i'                                     => t('possibile perdita di dati'),
        '/sicurezza|password|accesso|violaz|truffa|frode/i'                      => t('possibile problema di sicurezza'),
        '/pagamento|addebit|rinnov|abbonamento/i'                                => t('possibile problema di pagamento'),
    ];
    foreach ($redFlags as $pattern => $reason) {
        if (preg_match($pattern, $text)) {
            $base = min($base, 2);
            $why[] = $reason;
        }
    }
    if (preg_match('/sicurezza|violaz|truffa|perdita di dati/i', $text)) $base = 1;

    if ($page !== '' && $base > 2) $base = min($base, 3);

    // Tempi di risposta dichiarati per priorita'.
    $sla = [1 => 12, 2 => 24, 3 => 48, 4 => 72][$base];

    $labels = [
        1 => t('P1 · Critica'),
        2 => t('P2 · Alta'),
        3 => t('P3 · Normale'),
        4 => t('P4 · Bassa'),
    ];

    // Punteggio basso = serve un chiarimento: allunghiamo il tempo stimato.
    if ($score < 40) $sla += 24;
    if ($score >= 85) $sla = max(6, (int)round($sla * 0.85));

    return [
        'priority'   => 'P' . $base,
        'priority_label' => $labels[$base],
        'level'      => $base,
        'sla_hours'  => $sla,
        'score'      => $score,
        'reasons'    => array_values(array_unique($why)),
        'category'   => $category,
        'urgency'    => $urgency,
    ];
}

/** Quello che il server sa di questa richiesta (nessun dato sensibile in chiaro). */
function lux_support_diagnostics(array $client = []): array
{
    $ua = (string)($_SERVER['HTTP_USER_AGENT'] ?? '');
    return array_filter([
        'when'        => date('Y-m-d H:i:s'),
        'page'        => (string)($_SERVER['HTTP_REFERER'] ?? ''),
        'agent'       => substr($ua, 0, 220),
        'device'      => preg_match('/mobile|android|iphone|ipad/i', $ua) ? 'mobile' : 'desktop',
        'language'    => (string)($_SERVER['HTTP_ACCEPT_LANGUAGE'] ?? ''),
        'ip_hash'     => substr(sha1((string)($_SERVER['REMOTE_ADDR'] ?? '') . API_TOKEN), 0, 12),
        'server'      => PHP_VERSION,
        // Dati che arrivano dal browser (raccolti dal pannello dal vivo).
        'viewport'    => (string)($client['viewport'] ?? ''),
        'screen'      => (string)($client['screen'] ?? ''),
        'timezone'    => (string)($client['timezone'] ?? ''),
        'cores'       => (string)($client['cores'] ?? ''),
        'memory'      => (string)($client['memory'] ?? ''),
        'connection'  => (string)($client['connection'] ?? ''),
        'js'          => (string)($client['js'] ?? ''),
        'theme'       => (string)($client['theme'] ?? ''),
        'session'     => (string)($client['session'] ?? ''),
    ], function ($v) { return $v !== '' && $v !== null; });
}

/** Cronologia leggibile di una richiesta (dalla piu' vecchia alla piu' recente). */
function lux_support_timeline(array $row): array
{
    $out = [];
    $created = (string)($row['date'] ?? '');
    if ($created !== '') {
        $out[] = ['when' => $created, 'label' => t('Richiesta ricevuta'), 'note' => t('il ticket è entrato in coda')];
        if (!empty($row['priority_label']) || !empty($row['sla_hours'])) {
            $out[] = [
                'when'  => $created,
                'label' => t('Triage automatico'),
                'note'  => trim((string)($row['priority_label'] ?? '') . ' · '
                    . t('risposta prevista entro {h} ore', ['h' => (int)($row['sla_hours'] ?? 0)])),
            ];
        }
        if (!empty($row['queue_position'])) {
            $out[] = [
                'when'  => $created,
                'label' => t('Posizione in coda'),
                'note'  => t('la richiesta era la numero {n}', ['n' => (int)$row['queue_position']]),
            ];
        }
        if (!empty($row['diagnostics']) && is_array($row['diagnostics'])) {
            $out[] = [
                'when'  => $created,
                'label' => t('Diagnostica acquisita'),
                'note'  => t('{n} dati tecnici allegati al ticket', ['n' => count($row['diagnostics'])]),
            ];
        }
    }
    foreach ((array)($row['history'] ?? []) as $step) {
        if (!is_array($step)) continue;
        // Le etichette sono salvate come chiavi: si traducono adesso, cosi' un
        // ticket scritto in italiano resta leggibile anche in inglese.
        $out[] = [
            'when'  => (string)($step['when'] ?? ''),
            'label' => t((string)($step['label'] ?? 'Aggiornamento')),
            'note'  => t((string)($step['note'] ?? '')),
        ];
    }
    $status = (string)($row['status'] ?? 'In Attesa');
    if (stripos($status, 'risolto') !== false || stripos($status, 'archiviato') !== false) {
        $out[] = [
            'when'  => (string)($row['resolved_at'] ?? ($row['updated'] ?? '')),
            'label' => t('Richiesta conclusa'),
            'note'  => $status,
        ];
    } else {
        $out[] = ['when' => date('Y-m-d H:i:s'), 'label' => t('Stato attuale'), 'note' => $status];
    }
    return $out;
}
// ============================================================
// L.U.X. SUPPORT CONSOLE — pannello dal vivo di Assistenza e Contatti
// ============================================================
// lux_support_console()      markup del pannello (coda, triage, AI, suggerimenti)
// lux_support_console_js()   il JavaScript che lo anima (stesse regole del server)
//
// Le regole del triage sono identiche a lux_support_triage() in questo file:
// quello che il visitatore vede mentre scrive e' quello che il server salva.
// ============================================================

function lux_support_console(array $cfg = []): void
{
    $form  = (string)($cfg['form'] ?? 'assistenza');   // per data-form
    $queue = lux_support_queue();
    ?>
    <section class="sup-console" data-sup-form="<?= htmlspecialchars($form) ?>">
        <div class="sup-head">
            <?= icon('pulse', 15) ?>
            <span><?= t('Console di supporto') ?></span>
            <span class="sup-live"><i></i><?= t('dal vivo') ?></span>
        </div>

        <div class="sup-queue" id="supQueue">
            <?php if ((int)$queue['total'] === 0): ?>
                <span><?= t('Nessuna richiesta in coda: la tua sarebbe la prima.') ?></span>
            <?php else: ?>
                <span>
                    <?= t('In coda:') ?> <b id="supQueueOpen"><?= (int)$queue['open'] ?></b>
                    <?= t('su') ?> <?= (int)$queue['total'] ?> <?= t('richieste totali') ?>
                    <?php if ($queue['avg_hours'] !== null): ?>
                        · <?= t('tempo medio di chiusura:') ?> <b id="supQueueAvg"><?= htmlspecialchars((string)$queue['avg_hours']) ?>h</b>
                    <?php else: ?>
                        · <?= t('nessuna richiesta ancora chiusa: nessun tempo medio da mostrare') ?>
                    <?php endif; ?>
                </span>
            <?php endif; ?>
        </div>

        <div class="sup-triage">
            <div class="sup-badge" id="supPriorityBox">
                <b id="supPriority">P4</b>
                <span id="supPriorityLabel"><?= t('P4 · Bassa') ?></span>
            </div>
            <div class="sup-meter" role="img" aria-label="<?= t('Completezza della segnalazione') ?>">
                <span class="sup-meter-label"><?= t('Completezza') ?></span>
                <div class="sup-bar"><i id="supScoreBar" style="width:0%"></i></div>
                <span class="sup-meter-value" id="supScore">0%</span>
            </div>
            <div class="sup-sla">
                <?= icon('timer', 14) ?>
                <span id="supSla"><?= t('risposta prevista entro {h} ore', ['h' => 72]) ?></span>
            </div>
        </div>

        <ul class="sup-reasons" id="supReasons"></ul>

        <div class="sup-actions">
            <button type="button" class="sup-btn" id="supAiBtn">
                <?= icon('sparkles', 14) ?>
                <span><?= t('Analizza con l\'AI') ?></span>
            </button>
            <span class="sup-hint"><?= t('facoltativo: l\'AI propone categoria e priorità, decidi tu') ?></span>
        </div>
        <div class="sup-ai-out" id="supAiOut" hidden></div>

        <div class="sup-suggest" id="supSuggest" hidden>
            <span class="sup-suggest-title"><?= icon('search', 14) ?> <?= t('Forse la risposta è già nella FAQ:') ?></span>
            <ul id="supSuggestList"></ul>
        </div>
    </section>
    <?php
}

function lux_support_console_js(array $cfg = []): void
{
    $form = (string)($cfg['form'] ?? 'assistenza');
    ?>
    <script>
    // Si avvia a pagina pronta: cosi' trova anche gli elementi che stanno piu'
    // in basso (il riquadro del ticket con il pulsante di scaricamento).
    document.addEventListener('DOMContentLoaded', function () {
        var console_ = document.querySelector('.sup-console[data-sup-form="<?= tjs($form) ?>"]');
        if (!console_) return;
        var form = document.querySelector('form.support-form');
        if (!form) return;

        var field = function (selector) { return form.querySelector(selector); };
        var category = field('[name=category]') || field('[name=type]');
        var urgency  = field('[name=urgency]');
        var subject  = field('[name=subject]') || field('[name=project]');
        var message  = field('[name=message]');
        var email    = field('[name=email]');

        function set(id, text) { var el = document.getElementById(id); if (el) el.textContent = text; }

        // --- dati tecnici: riempiti negli input nascosti prima dell'invio ---
        function telemetry() {
            var nav = window.performance && performance.getEntriesByType
                ? (performance.getEntriesByType('navigation')[0] || null) : null;
            var conn = navigator.connection || navigator.mozConnection || navigator.webkitConnection || {};
            return {
                diag_viewport:   window.innerWidth + 'x' + window.innerHeight,
                diag_screen:     (screen.width || 0) + 'x' + (screen.height || 0) + ' dpr ' + (window.devicePixelRatio || 1),
                diag_timezone:   (function () { try { return Intl.DateTimeFormat().resolvedOptions().timeZone || ''; } catch (e) { return ''; } })(),
                diag_cores:      String(navigator.hardwareConcurrency || ''),
                diag_memory:     String(navigator.deviceMemory || ''),
                diag_connection: [conn.effectiveType || '', conn.downlink ? conn.downlink + ' Mbps' : '', conn.rtt ? conn.rtt + ' ms' : ''].filter(Boolean).join(' · '),
                diag_js:         'ok' + (nav && nav.loadEventEnd ? ' · ' + Math.round(nav.loadEventEnd) + ' ms' : ''),
                diag_theme:      window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light',
                diag_session:    (function () {
                    var key = 'lux_support_sid', id = '';
                    try { id = sessionStorage.getItem(key) || ''; } catch (e) {}
                    if (!id) { id = 's' + Math.random().toString(36).slice(2, 10); try { sessionStorage.setItem(key, id); } catch (e) {} }
                    return id;
                })()
            };
        }

        function ensureHidden(name, value) {
            var el = form.querySelector('input[name="' + name + '"]');
            if (!el) {
                el = document.createElement('input');
                el.type = 'hidden';
                el.name = name;
                form.appendChild(el);
            }
            el.value = value;
        }

        function collect() {
            var data = telemetry();
            Object.keys(data).forEach(function (k) { ensureHidden(k, data[k]); });
            ensureHidden('page', window.location.pathname + window.location.search);
        }

        // --- triage identico a quello del server (lux_support_triage) ---
        var CATS = {
            'Reclamo': 2, 'Problemi Account': 2, 'Privacy': 1, 'Segnalazione Bug': 2,
            'Assistenza Tecnica': 3, 'Richiesta Informazioni': 4, 'Suggerimento': 4, 'Altro': 4
        };

        function triage() {
            var cat = category ? category.value : '';
            var urg = urgency ? urgency.value : '';
            var sub = subject ? subject.value.trim() : '';
            var msg = message ? message.value.trim() : '';
            var mail = email ? email.value.trim() : '';
            var len = msg.length, score = 0, why = [];

            if (len >= 40) score += 15; else why.push('<?= tjs('il messaggio è molto breve') ?>');
            if (len >= 160) score += 15; else why.push('<?= tjs('mancano dettagli: aggiungi cosa è successo passo per passo') ?>');
            if (len >= 420) score += 15;
            if (sub !== '') score += 5;
            if (/^[^@\s]+@[^@\s]+\.[^@\s]+$/.test(mail)) score += 10;
            else why.push('<?= tjs('email non valida: non potremmo risponderti') ?>');
            if (/https?:\/\/|\.php|passo|step|risultato|schermata|telefono|browser/i.test(msg)) score += 10;
            else why.push('<?= tjs('indica la pagina e il dispositivo usato') ?>');
            if (score > 100) score = 100;

            var base = 4;
            Object.keys(CATS).forEach(function (k) {
                if (cat.toLowerCase().indexOf(k.toLowerCase()) !== -1) base = Math.min(base, CATS[k]);
            });
            if (/bloccante|urgente/i.test(urg)) base = Math.min(base, 1);
            else if (/alta/i.test(urg)) base = Math.min(base, 2);

            var text = sub + ' ' + msg;
            if (/non funziona|non parte|non si apre|non carica|errore|crash|bloccato/i.test(text)) { base = Math.min(base, 2); why.push('<?= tjs('segnalato un blocco o un errore') ?>'); }
            if (/perso|perdita|cancellat|spariti/i.test(text)) why.push('<?= tjs('possibile perdita di dati') ?>');
            if (/sicurezza|password|accesso|violaz|truffa|frode/i.test(text)) { base = Math.min(base, 1); why.push('<?= tjs('possibile problema di sicurezza') ?>'); }
            if (/pagamento|addebit|rinnov|abbonamento/i.test(text)) why.push('<?= tjs('possibile problema di pagamento') ?>');

            var labels = { 1: '<?= tjs('P1 · Critica') ?>', 2: '<?= tjs('P2 · Alta') ?>', 3: '<?= tjs('P3 · Normale') ?>', 4: '<?= tjs('P4 · Bassa') ?>' };
            var sla = { 1: 12, 2: 24, 3: 48, 4: 72 }[base];
            if (score < 40) sla += 24;
            if (score >= 85) sla = Math.max(6, Math.round(sla * 0.85));

            return { base: base, label: labels[base], sla: sla, score: score, reasons: why.slice(0, 4) };
        }

        function paint() {
            var t = triage();
            set('supPriority', 'P' + t.base);
            set('supPriorityLabel', t.label);
            set('supScore', t.score + '%');
            var bar = document.getElementById('supScoreBar');
            if (bar) bar.style.width = t.score + '%';
            set('supSla', '<?= tjs('risposta prevista entro {h} ore') ?>'.replace('{h}', t.sla));
            var box = document.getElementById('supPriorityBox');
            if (box) box.className = 'sup-badge p' + t.base;
            var list = document.getElementById('supReasons');
            if (list) {
                list.innerHTML = t.reasons.map(function (r) { return '<li>' + String(r).replace(/[<>&]/g, '') + '</li>'; }).join('');
            }
        }

        [category, urgency, subject, message, email].forEach(function (el) {
            if (el) { el.addEventListener('input', paint); el.addEventListener('change', paint); }
        });
        paint();

        // --- coda reale ---
        fetch('?queue=1', { headers: { 'Accept': 'application/json' } })
            .then(function (r) { return r.json(); })
            .then(function (q) {
                if (!q || !q.success) return;
                set('supQueueOpen', q.open);
                var box = document.getElementById('supQueue');
                var extra = q.avg_hours !== null && q.avg_hours !== undefined
                    ? '<?= tjs(' · tempo medio di chiusura:') ?> ' + q.avg_hours + 'h'
                    : '<?= tjs(' · nessuna richiesta ancora chiusa: nessun tempo medio da mostrare') ?>';
                var pos = '<?= tjs('La tua richiesta entrerebbe come numero {n} in coda.') ?>'.replace('{n}', q.position);
                if (box) box.innerHTML = '<span>' + pos + extra + '</span>';
            })
            .catch(function () {});

        // --- analisi AI (facoltativa, su richiesta) ---
        var aiBtn = document.getElementById('supAiBtn');
        var aiOut = document.getElementById('supAiOut');
        if (aiBtn && aiOut) {
            aiBtn.addEventListener('click', function () {
                if (!subject || !message) return;
                aiBtn.classList.add('is-busy');
                aiOut.hidden = false;
                aiOut.innerHTML = '<span class="sup-ai-wait"><?= tjs('Analisi in corso...') ?></span>';
                var body = new FormData();
                body.append('subject', subject.value);
                body.append('message', message.value);
                fetch('/api/triage.php', { method: 'POST', body: body })
                    .then(function (r) { return r.json(); })
                    .then(function (d) {
                        if (!d.success) {
                            aiOut.innerHTML = '<span class="sup-ai-warn"><?= tjs('Analisi AI non disponibile:') ?> ' +
                                String(d.error || '').replace(/[<>&]/g, '') + '</span>';
                            return;
                        }
                        var html = '<div class="sup-ai-head"><?= tjs('Proposta dell\'AI') ?></div>';
                        html += '<div class="sup-ai-row"><b>' + String(d.priority || '-').replace(/[<>&]/g, '') + '</b><span>' +
                            String(d.category || '-').replace(/[<>&]/g, '') + '</span></div>';
                        if (d.summary) html += '<p>' + String(d.summary).replace(/[<>&]/g, '') + '</p>';
                        if (d.questions && d.questions.length) {
                            html += '<ul>' + d.questions.map(function (q) {
                                return '<li>' + String(q).replace(/[<>&]/g, '') + '</li>';
                            }).join('') + '</ul>';
                        }
                        html += '<button type="button" class="sup-btn small" id="supAiApply"><?= tjs('Usa la categoria proposta') ?></button>';
                        aiOut.innerHTML = html;
                        var apply = document.getElementById('supAiApply');
                        if (apply && category && d.category) {
                            apply.addEventListener('click', function () {
                                for (var i = 0; i < category.options.length; i++) {
                                    if (category.options[i].textContent.trim().toLowerCase() === String(d.category).trim().toLowerCase()) {
                                        category.selectedIndex = i;
                                        category.dispatchEvent(new Event('change'));
                                        apply.textContent = '<?= tjs('Categoria aggiornata') ?>';
                                        break;
                                    }
                                }
                            });
                        }
                    })
                    .catch(function () {
                        aiOut.innerHTML = '<span class="sup-ai-warn"><?= tjs('Analisi AI non disponibile: nessuna risposta dal server.') ?></span>';
                    })
                    .then(function () { aiBtn.classList.remove('is-busy'); });
            });
        }

        // --- suggerimenti dalla FAQ (indice costruito al primo utilizzo) ---
        var suggestBox = document.getElementById('supSuggest');
        var suggestList = document.getElementById('supSuggestList');
        var faqIndex = null;
        var faqTimer = null;

        function loadFaq() {
            return fetch('/faq.php').then(function (r) { return r.text(); }).then(function (html) {
                var doc = new DOMParser().parseFromString(html, 'text/html');
                var items = []
                doc.querySelectorAll('.faq-item').forEach(function (item) {
                    var q = item.querySelector('.faq-question');
                    var a = item.querySelector('.faq-answer');
                    if (!q) return;
                    items.push({
                        q: q.textContent.trim().replace(/\s+/g, ' '),
                        a: a ? a.textContent.trim().replace(/\s+/g, ' ').slice(0, 180) : ''
                    });
                });
                faqIndex = items;
                return items;
            }).catch(function () { faqIndex = []; return []; });
        }

        function suggest() {
            if (!subject || !suggestBox) return;
            var needle = subject.value.trim().toLowerCase();
            if (needle.length < 3) { suggestBox.hidden = true; return; }
            var run = function (items) {
                var words = needle.split(/\s+/).filter(function (w) { return w.length > 3; });
                var hits = items.filter(function (it) {
                    var hay = (it.q + ' ' + it.a).toLowerCase();
                    return hay.indexOf(needle) !== -1 || words.some(function (w) { return hay.indexOf(w) !== -1; });
                }).slice(0, 4);
                if (!hits.length) { suggestBox.hidden = true; return; }
                suggestList.innerHTML = hits.map(function (h) {
                    return '<li><a href="/faq.php">' + h.q.replace(/[<>&]/g, '') + '</a></li>';
                }).join('');
                suggestBox.hidden = false;
            };
            if (faqIndex === null) { loadFaq().then(run); } else { run(faqIndex); }
        }

        if (subject) {
            subject.addEventListener('input', function () {
                clearTimeout(faqTimer);
                faqTimer = setTimeout(suggest, 260);
            });
        }

        form.addEventListener('submit', function () { collect(); });

        // --- scarica il ticket come file di testo ---
        var dl = document.getElementById('supDownload');
        if (dl) {
            dl.addEventListener('click', function () {
                var box = document.getElementById('supTicketText');
                if (!box) return;
                var text = box.textContent.replace(/\n{3,}/g, '\n\n').trim();
                var blob = new Blob([text], { type: 'text/plain;charset=utf-8' });
                var url = URL.createObjectURL(blob);
                var a = document.createElement('a');
                a.href = url;
                a.download = 'lux-ticket-' + (dl.getAttribute('data-code') || 'ticket') + '.txt';
                document.body.appendChild(a);
                a.click();
                document.body.removeChild(a);
                setTimeout(function () { URL.revokeObjectURL(url); }, 4000);
            });
        }
    });
    </script>
    <?php
}
