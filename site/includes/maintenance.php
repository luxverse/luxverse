<?php
// ============================================================
// BLOCCO DEL SITO (manutenzione) — un solo interruttore
// ------------------------------------------------------------
// Lo stato NON sta nel codice: sta in data/maintenance.json, che si accende
// e si spegne dal pannello. Cosi' si blocca e si sblocca il sito senza
// ripubblicare niente, e nessuno resta chiuso fuori per sbaglio.
//
//   {
//     "on": true,
//     "since": "2026-10-12 21:40",     // quando e' stato acceso
//     "hours": 24,                     // entro quante ore si riapre
//     "key": "…",                      // chiave di anteprima (vedi sotto)
//     "api": true,                     // blocca anche le API (app Android)
//     "message_it": "…",               // testo mostrato ai visitatori
//     "message_en": "…"
//   }
//
// Regole:
//   - con il blocco acceso il sito risponde 503 (temporaneo: i motori di
//     ricerca non cancellano le pagine, sanno che si torna);
//   - il PANNELLO resta sempre raggiungibile (admin.php e /api/admin.php),
//     altrimenti chi gestisce il sito non potrebbe piu' riaccendere niente;
//   - le risorse (CSS, immagini, icone) continuano a servire, cosi' il
//     pannello si vede per intero;
//   - con ?lux_anteprima=CHIAVE si vede il sito bloccato senza spegnere il
//     blocco: la chiave si legge nel pannello, e vale due ore.
// ============================================================

/** Lo stato del blocco, letto una volta sola per richiesta. */
function lux_maintenance_state(): array
{
    static $state = null;
    if ($state !== null) return $state;

    $file  = __DIR__ . '/../data/maintenance.json';
    $state = [];
    if (is_file($file)) {
        $raw = json_decode((string)@file_get_contents($file), true);
        if (is_array($raw)) $state = $raw;
    }
    $state += [
        'on'         => false,
        'since'      => '',
        'hours'      => 24,
        'key'        => '',
        'api'        => true,
        'message_it' => 'Stiamo lavorando al sito. Questa interruzione è voluta e dipende da noi: '
                      . 'stiamo mettendo a posto delle cose importanti. Torniamo online entro 24 ore.',
        'message_en' => 'We are working on the site. This downtime is deliberate and caused by us: '
                      . 'we are fixing important things. We will be back online within 24 hours.',
        // Contatto per le urgenze mentre il sito e' chiuso. Si puo' cambiare
        // dal file dello stato senza toccare il codice.
        'contact'    => 'administration.luxverse@gmail.com',
        // Momento assoluto di inizio (secondi dall'1/1/1970). Non dipende dal
        // fuso del server: e' il modo esatto di dire "quando", e le ore si
        // mostrano poi in ora italiana.
        'since_ts'   => 0,

        // --- Testi personalizzati ---------------------------------------
        // Titolo, etichetta e motivi della pausa. Stanno nel file dello stato,
        // non nel codice: per spiegare un motivo nuovo (un intervento, un
        // trasloco di dominio) basta riscrivere il file, senza ripubblicare
        // niente. Se restano vuoti la pagina mostra i testi di sempre.
        //   *fra asterischi*  ->  evidenziato in oro
        //   a capo nel testo   ->  va a capo
        'badge_it'   => '',
        'badge_en'   => '',
        'title_it'   => '',
        'title_en'   => '',
        'points_it'  => [],
        'points_en'  => [],
        // Indirizzo nuovo del sito: si mostra solo se e' stato scritto qui.
        'new_domain' => '',
    ];
    $state['on'] = !empty($state['on']);
    return $state;
}

/** Il blocco e' acceso? */
function lux_maintenance_active(): bool
{
    return lux_maintenance_state()['on'];
}

/**
 * Scrive lo stato del blocco nel file (si usa dal pannello). Si passa solo
 * quello che cambia: il resto resta com'e' (compresi i testi scritti a mano).
 */
function lux_maintenance_write(array $patch): bool
{
    $file = __DIR__ . '/../data/maintenance.json';
    $next = array_merge(lux_maintenance_state(), $patch);
    if (!is_dir(dirname($file))) @mkdir(dirname($file), 0755, true);
    return @file_put_contents(
        $file,
        json_encode($next, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
        LOCK_EX
    ) !== false;
}

/**
 * Fuso orario del sito: le ore si mostrano in ora italiana, non in quella
 * del server (che sta altrove e senza questa conversione la pagina diceva
 * "iniziata alle 14:41" mentre in Italia erano le 20:41).
 */
function lux_maintenance_tz(): ?DateTimeZone
{
    static $tz = false;
    if ($tz === false) {
        try { $tz = new DateTimeZone('Europe/Rome'); }
        catch (Exception $e) { $tz = null; }
    }
    return $tz;
}

/** Da quando e' in corso il blocco, come momento assoluto. */
function lux_maintenance_epoch(): int
{
    $st = lux_maintenance_state();
    if (!empty($st['since_ts'])) return (int)$st['since_ts'];
    // Compatibilita': senza since_ts si usa la stringa scritta dal server.
    return $st['since'] !== '' ? (int)strtotime((string)$st['since']) : 0;
}

/**
 * Un'ora letta dal momento assoluto e mostrata in ORA ITALIANA, con
 * l'aggiunta di un eventuale spostamento (le 24 ore del blocco).
 * L'hosting tiene l'orologio in un altro fuso: senza questa conversione la
 * pagina diceva "iniziata alle 14:41" mentre in Italia erano le 20:41.
 */
function lux_maintenance_stamp(int $addSeconds = 0): string
{
    $epoch = lux_maintenance_epoch();
    if ($epoch <= 0) return '';
    $tz = lux_maintenance_tz();
    if ($tz === null) return date('d/m/Y H:i', $epoch + $addSeconds);
    return (new DateTimeImmutable('@' . ($epoch + $addSeconds)))->setTimezone($tz)->format('d/m/Y H:i');
}

/** Quando e' iniziata la manutenzione (ora italiana), o stringa vuota. */
function lux_maintenance_since(): string
{
    return lux_maintenance_stamp(0);
}

/** Quando e' prevista la riapertura (ora italiana), o stringa vuota. */
function lux_maintenance_until(): string
{
    if (lux_maintenance_epoch() <= 0) return '';
    return lux_maintenance_stamp(max(1, (int)lux_maintenance_state()['hours']) * 3600);
}

/** Nome del file che si sta eseguendo (senza percorso). */
function lux_maintenance_script(): string
{
    return basename((string)($_SERVER['SCRIPT_FILENAME'] ?? ''));
}

/**
 * Questa richiesta deve restare aperta anche col blocco acceso?
 * Pannello, API del pannello, risorse statiche e chiave di anteprima.
 */
function lux_maintenance_open_request(): bool
{
    $script = lux_maintenance_script();
    if ($script === 'admin.php') return true;

    // L'app Android apre webapp.php?app=1: se la manutenzione la chiudesse
    // fuori, l'app resterebbe scollegata e mostrerebbe solo l'avviso.
    // La vista app resta raggiungibile, come il pannello.
    if ($script === 'webapp.php' && (string)($_GET['app'] ?? '') === '1') return true;

    $path = (string)parse_url((string)($_SERVER['REQUEST_URI'] ?? ''), PHP_URL_PATH);

    // API del pannello: e' il pannello stesso a interrogarla.
    if (strpos($path, '/api/admin.php') === 0) return true;
    if ((string)($_GET['__ep'] ?? '') === 'admin') return true;

    // CSS, immagini, icone, service worker, manifest: servono al pannello.
    if (strpos($path, '/assets/') === 0) return true;
    if (in_array($path, ['/sw.js', '/manifest.json', '/offline.html', '/robots.txt'], true)) return true;

    // Anteprima per il titolare: ?lux_anteprima=CHIAVE -> cookie di due ore.
    $st     = lux_maintenance_state();
    $key    = (string)$st['key'];
    $cookie = 'lux_anteprima';

    // Uscita dall'anteprima: ?lux_anteprima=esci. Serve perche' il cookie e'
    // HttpOnly (JavaScript non lo puo' cancellare), quindi senza questa via
    // il titolare resterebbe a vedere il sito aperto senza capire perche'.
    if (isset($_GET['lux_anteprima']) && $_GET['lux_anteprima'] === 'esci') {
        if (!headers_sent()) {
            setcookie($cookie, '', ['expires' => time() - 3600, 'path' => '/', 'httponly' => true, 'samesite' => 'Lax']);
        }
        return false;
    }

    if ($key !== '') {
        if (isset($_GET[$cookie], $_GET['lux_anteprima']) && hash_equals($key, (string)$_GET[$cookie])) {
            if (!headers_sent()) {
                setcookie($cookie, hash_hmac('sha256', 'anteprima', $key), [
                    'expires'  => time() + 7200,
                    'path'     => '/',
                    'httponly' => true,
                    'samesite' => 'Lax',
                ]);
            }
            return true;
        }
        if (isset($_COOKIE[$cookie]) && hash_equals(hash_hmac('sha256', 'anteprima', $key), (string)$_COOKIE[$cookie])) {
            return true;
        }
    }

    // Se non si blocca l'API, l'app Android continua a funzionare.
    if (empty($st['api']) && strpos($path, '/api/') === 0) return true;

    return false;
}

/**
 * Un testo scritto nello stato e mostrato nella pagina.
 *
 * Si protegge una volta sola e poi si aggiunge soltanto il NOSTRO markup:
 * ' *cosi* ' diventa evidenziato, e i ritorni a capo diventano <br>. In questo
 * modo i testi si cambiano dal file dello stato senza poter iniettare HTML.
 */
function lux_maintenance_text(string $text, bool $multi = true): string
{
    $out = htmlspecialchars(trim($text), ENT_QUOTES);
    $out = preg_replace('/\*(.+?)\*/s', '<em>$1</em>', $out);
    if ($out === null) $out = htmlspecialchars(trim($text), ENT_QUOTES);
    return $multi ? nl2br($out) : $out;
}

/** I motivi della pausa: una lista di testi gia' pronti da mostrare. */
function lux_maintenance_points($list): array
{
    if (!is_array($list)) return [];
    $out = [];
    foreach ($list as $p) {
        $one = lux_maintenance_text((string)$p);
        if (trim(strip_tags($one)) !== '') $out[] = $one;
    }
    return $out;
}

/**
 * Pagina di manutenzione: autonoma (nessun file esterno da caricare), cosi'
 * si vede anche se qualcosa nel sito e' proprio quello che si sta sistemando.
 */
function lux_maintenance_render(): void
{
    $st      = lux_maintenance_state();
    $isApi   = strpos((string)parse_url((string)($_SERVER['REQUEST_URI'] ?? ''), PHP_URL_PATH), '/api/') === 0;
    $hours   = max(1, (int)$st['hours']);
    $until   = lux_maintenance_until();

    http_response_code(503);
    header('Retry-After: ' . ($hours * 3600));
    header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
    header('X-Robots-Tag: noindex, nofollow');

    if ($isApi) {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode([
            'success' => false,
            'maintenance' => true,
            'error'   => 'L.U.X. Verse è in manutenzione: torniamo online entro ' . $hours . ' ore.',
            'error_en'=> 'L.U.X. Verse is under maintenance: we will be back online within ' . $hours . ' hours.',
            'since'   => lux_maintenance_since(),
            'since_utc' => $st['since'],
            'until'   => $until,
            'contact' => (string)($st['contact'] ?? ''),
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    header('Content-Type: text/html; charset=utf-8');

    // Testi della pagina: si leggono dallo stato. Se non ce ne sono si usano
    // quelli di sempre, quindi il blocco continua a funzionare anche con un
    // file scritto prima che esistessero questi campi.
    $it      = lux_maintenance_text((string)$st['message_it']);
    $en      = lux_maintenance_text((string)$st['message_en']);
    $badgeIt = lux_maintenance_text((string)$st['badge_it'] !== '' ? (string)$st['badge_it'] : 'Manutenzione in corso', false);
    $badgeEn = lux_maintenance_text((string)$st['badge_en'] !== '' ? (string)$st['badge_en'] : 'Maintenance in progress', false);
    $titleIt = lux_maintenance_text((string)$st['title_it'] !== '' ? (string)$st['title_it'] : "Siamo al lavoro.\nQuesta pausa è *voluta*.");
    $titleEn = (string)$st['title_en'] !== '' ? lux_maintenance_text((string)$st['title_en']) : '';
    $pointsIt = lux_maintenance_points($st['points_it'] ?? []);
    $pointsEn = lux_maintenance_points($st['points_en'] ?? []);
    $newDom  = trim((string)($st['new_domain'] ?? ''));
    $sinceIt = lux_maintenance_since();
    $mail    = trim((string)($st['contact'] ?? ''));
    $mail    = $mail !== '' && filter_var($mail, FILTER_VALIDATE_EMAIL) ? $mail : '';
    ?><!DOCTYPE html>
<html lang="it">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="robots" content="noindex, nofollow">
<title>L.U.X. Verse — manutenzione in corso</title>
<style>
    :root { --gold: #D8A93C; --bone: #EDE9E0; --dim: #A5A197; --line: rgba(255,255,255,.09); }
    * { margin: 0; padding: 0; box-sizing: border-box; }
    body { min-height: 100vh; display: flex; align-items: center; justify-content: center; padding: 28px;
        font-family: 'Inter', -apple-system, 'Segoe UI', Roboto, sans-serif; color: var(--bone);
        background: radial-gradient(120% 120% at 50% 0%, #12141A 0%, #0A0B0D 62%); }
    .box { width: 100%; max-width: 660px; }
    .brand { display: flex; align-items: center; gap: 11px; margin-bottom: 30px; }
    .brand span { font-size: 13px; letter-spacing: .34em; text-transform: uppercase; color: var(--dim); }
    .brand .brand-name { white-space: nowrap; }
    .brand .badge-en { margin-left: auto; font-size: 10px; letter-spacing: .12em; color: rgba(165,161,151,.7);
        border: 1px solid var(--line); border-radius: 999px; padding: 5px 9px; white-space: nowrap; }
    @media (max-width: 560px) { .brand .badge-en { display: none; } }
    .badge { display: inline-flex; align-items: center; gap: 9px; padding: 7px 13px; margin-bottom: 22px;
        border: 1px solid var(--line); border-radius: 999px; font-size: 11px; letter-spacing: .18em;
        text-transform: uppercase; color: var(--gold); }
    .dot { width: 7px; height: 7px; border-radius: 50%; background: var(--gold); animation: pulse 2.4s ease-in-out infinite; }
    @keyframes pulse { 0%,100% { opacity: 1; } 50% { opacity: .25; } }
    h1 { font-size: clamp(28px, 5vw, 44px); line-height: 1.12; font-weight: 700; letter-spacing: -.02em; margin-bottom: 20px; }
    h1 em { font-style: normal; color: var(--gold); }
    p.msg { font-size: 17px; line-height: 1.75; color: var(--bone); max-width: 60ch; }
    ul.why { list-style: none; margin: 22px 0 0; padding: 0; max-width: 66ch; }
    ul.why li { position: relative; padding-left: 22px; margin-bottom: 12px;
        font-size: 15.5px; line-height: 1.7; color: var(--bone); }
    ul.why li::before { content: ''; position: absolute; left: 3px; top: 10px;
        width: 6px; height: 6px; border-radius: 50%; background: var(--gold); opacity: .85; }
    p.moved { margin-top: 22px; font-size: 15.5px; line-height: 1.7; color: var(--bone);
        padding: 14px 16px; border: 1px solid var(--line); border-left: 2px solid var(--gold); border-radius: 10px; }
    p.moved b { color: var(--gold); font-weight: 600; }
    .en { margin-top: 30px; padding-top: 24px; border-top: 1px solid var(--line); }
    .en-title { font-size: 14px; letter-spacing: .22em; text-transform: uppercase; color: var(--dim); margin-bottom: 12px; }
    p.en-msg { font-size: 14.5px; line-height: 1.7; color: var(--dim); max-width: 62ch; }
    ul.en-why li { font-size: 14px; color: var(--dim); }
    ul.en-why { margin-top: 16px; }
    .foot { margin-top: 34px; padding-top: 18px; border-top: 1px solid var(--line);
        display: flex; flex-wrap: wrap; gap: 10px 22px; align-items: center;
        font-size: 12.5px; color: var(--dim); }
    .foot b { color: var(--bone); font-weight: 600; }
    .foot .mail { color: var(--gold); text-decoration: none; border-bottom: 1px solid rgba(216,169,60,.35); }
    .foot .mail:hover { border-bottom-color: var(--gold); }
    .foot .en2 { width: 100%; color: rgba(165,161,151,.75); font-size: 11.5px; }
    .bar { height: 3px; width: 100%; margin-top: 26px; border-radius: 3px; overflow: hidden; background: rgba(255,255,255,.07); }
    .bar i { display: block; height: 100%; width: 38%; background: linear-gradient(90deg, transparent, var(--gold), transparent); animation: slide 2.6s ease-in-out infinite; }
    @keyframes slide { 0% { transform: translateX(-100%); } 100% { transform: translateX(320%); } }
</style>
</head>
<body>
    <div class="box">
        <div class="brand">
            <svg aria-hidden="true" width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="#D8A93C" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="12" cy="12" r="9.2"></circle><path d="M12 7.4V12l3.4 2.2"></path>
            </svg>
            <span class="brand-name">L.U.X. Verse</span>
            <span class="badge-en"><?= $badgeEn ?></span>
        </div>

        <div class="badge"><span class="dot"></span> <?= $badgeIt ?></div>

        <h1><?= $titleIt ?></h1>

        <p class="msg"><?= $it ?></p>

        <?php if ($pointsIt): ?>
        <ul class="why">
            <?php foreach ($pointsIt as $p): ?><li><?= $p ?></li><?php endforeach; ?>
        </ul>
        <?php endif; ?>

        <?php if ($newDom !== ''): ?>
        <p class="moved">Il nuovo indirizzo è <b><?= htmlspecialchars($newDom, ENT_QUOTES) ?></b> — i vecchi collegamenti non spariscono.</p>
        <?php endif; ?>

        <div class="en">
            <?php if ($titleEn !== ''): ?><p class="en-title"><?= $titleEn ?></p><?php endif; ?>
            <p class="en-msg"><?= $en ?></p>
            <?php if ($pointsEn): ?>
            <ul class="why en-why">
                <?php foreach ($pointsEn as $p): ?><li><?= $p ?></li><?php endforeach; ?>
            </ul>
            <?php endif; ?>
        </div>

        <div class="bar"><i></i></div>

        <div class="foot">
            <span>Riapertura entro <b><?= (int)$hours ?> ore</b><?= $until !== '' ? ' · entro il <b>' . htmlspecialchars($until, ENT_QUOTES) . '</b>' : '' ?></span>
            <?php if ($sinceIt !== ''): ?><span>Iniziata il <b><?= htmlspecialchars($sinceIt, ENT_QUOTES) ?></b> (ora italiana)</span><?php endif; ?>
            <?php if ($mail !== ''): ?>
                <span>Per urgenze scrivi a <a class="mail" href="mailto:<?= htmlspecialchars($mail, ENT_QUOTES) ?>"><?= htmlspecialchars($mail, ENT_QUOTES) ?></a> fino alla riapertura.</span>
                <span class="en2">Urgent matters: email <a class="mail" href="mailto:<?= htmlspecialchars($mail, ENT_QUOTES) ?>"><?= htmlspecialchars($mail, ENT_QUOTES) ?></a>.</span>
            <?php endif; ?>
        </div>
    </div>
</body>
</html><?php
    exit;
}

// ============================================================
// TRASLOCO DI DOMINIO — la vecchia casa mostra solo una schermata
// ------------------------------------------------------------
// Quando il sito si sposta su un dominio nuovo, sul vecchio indirizzo non
// deve restare in piedi niente: né gli strumenti, né l'API, né il catalogo.
// Tutto quello che arriva lì viene servito con QUESTA pagina sola, che
// spiega in due righe cosa è successo, manda al nuovo indirizzo e dopo
// qualche secondo ci porta da sola.
//
// Lo stato sta in data/moved.json, che sul nuovo sito NON esiste: così lo
// stesso identico codice gira su entrambi, e solo il vecchio si comporta
// da cartello. Campi:
//   {
//     "on": true,
//     "url": "https://luxverse.c22.eu/",   // dove si va
//     "seconds": 20,                       // attesa prima del salto
//     "badge_it": "…", "badge_en": "…",  // testi (facoltativi)
//     "title_it": "…", "title_en": "…",
//     "message_it": "…", "message_en": "…",
//     "button_it": "…", "button_en": "…"
//   }
//
// Restano raggiungibili soltanto ciò che serve a gestire il vecchio spazio
// (il pannello e le sue risorse) e i file .apk: chi ha l'app vecchia deve
// poter scaricare quella nuova senza passare da un altro sito.
// ============================================================

/** Lo stato del trasloco, letto una volta sola per richiesta. */
function lux_moved_state(): array
{
    static $state = null;
    if ($state !== null) return $state;

    $file  = __DIR__ . '/../data/moved.json';
    $state = [];
    if (is_file($file)) {
        $raw = json_decode((string)@file_get_contents($file), true);
        if (is_array($raw)) $state = $raw;
    }
    $state += [
        'on'         => false,
        'url'        => 'https://luxverse.c22.eu/',
        'seconds'    => 20,
        'badge_it'   => 'Trasloco completato',
        'badge_en'   => 'Move completed',
        'title_it'   => "Ci siamo trasferiti.\nL.U.X. Verse ha un *indirizzo nuovo*.",
        'title_en'   => "We have moved.\nL.U.X. Verse has a *new address*.",
        'message_it' => "Questo indirizzo non si aggiorna più: qui resta soltanto questo avviso, e fra pochi secondi ti portiamo noi al sito nuovo, dove tutto continua come prima. Le cause e i dettagli completi del trasloco li trovi sul nuovo dominio, in bacheca.",
        'message_en' => "This address is no longer updated: only this notice stays here, and in a few seconds we will take you to the new site, where everything continues as before. The full causes and details of the move are on the new domain, on the notice board.",
        'why_it'     => 'In una riga: il vecchio indirizzo era un sottodominio gratuito, condiviso con migliaia di altri siti, e la sua reputazione non la sceglievamo noi. Sul dominio nuovo il nome e le regole sono nostri.',
        'why_en'     => 'In one line: the old address was a free subdomain, shared with thousands of other sites, and its reputation was not ours to choose. On the new domain the name and the rules are ours.',
        'button_it'  => 'Vai al nuovo sito',
        'button_en'  => 'Go to the new site',
    ];
    $state['on'] = !empty($state['on']);
    return $state;
}

/** Il trasloco è dichiarato? (cioè: stiamo sul dominio vecchio) */
function lux_moved_active(): bool
{
    return lux_moved_state()['on'];
}

/**
 * Questa richiesta deve restare aperta anche dopo il trasloco?
 * Solo il pannello (per gestire), le sue risorse e il file dell'app.
 */
function lux_moved_open_request(): bool
{
    $path = (string)parse_url((string)($_SERVER['REQUEST_URI'] ?? ''), PHP_URL_PATH);

    if (lux_maintenance_script() === 'admin.php') return true;
    if (strpos($path, '/api/admin.php') === 0) return true;
    if ((string)($_GET['__ep'] ?? '') === 'admin') return true;
    if (strpos($path, '/assets/') === 0) return true;

    // Il file dell'app: con il vecchio indirizzo la pagina App non esiste
    // più, quindi il download deve poter arrivare almeno da qui.
    if (strtolower(substr($path, -4)) === '.apk') return true;

    return false;
}

/**
 * robots.txt e mappe del sito sul dominio vecchio.
 *
 * Dopo il trasloco qui non c'è più niente da indicizzare: ai motori di
 * ricerca si dice di non passare (e la pagina del cartello lo ripete con
 * l'intestazione noindex), mentre la mappa valida è quella del dominio
 * nuovo. Serve a non lasciare in giro due siti identici che si fanno
 * concorrenza da soli.
 *
 * Restituisce true se ha risposto lui (e in quel caso non si va avanti).
 */
function lux_moved_robots_and_maps(string $path): bool
{
    $st  = lux_moved_state();
    $url = (string)$st['url'];

    if ($path === '/robots.txt') {
        header('Content-Type: text/plain; charset=utf-8');
        header('X-Robots-Tag: noindex, nofollow');
        echo "# Il sito è su un altro indirizzo: questo non si indicizza più.\n";
        echo "User-agent: *\nDisallow: /\n\n";
        echo 'Sitemap: ' . $url . "sitemap.xml\n";
        exit;
    }

    if (preg_match('#^/sitemap[^/]*\.xml$#i', $path)) {
        header('Content-Type: application/xml; charset=utf-8');
        header('X-Robots-Tag: noindex, nofollow');
        // Una mappa valida ma vuota per il dominio vecchio: l'unica cosa
        // utile che può dire è dov'è finito il sito.
        echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n",
             '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n",
             "<!-- Il sito è su un altro indirizzo: " . htmlspecialchars($url, ENT_QUOTES) . " -->\n",
             "</urlset>\n";
        exit;
    }

    return false;
}

/**
 * La schermata del trasloco: una pagina sola, autonoma (niente CSS o file
 * esterni da caricare) e con il conto alla rovescia. Chi ha JavaScript
 * vede il numero che scende, chi non ce l'ha viene portato dal meta
 * refresh, chi vuole andare prima preme il pulsante.
 */
function lux_moved_render(): void
{
    $st      = lux_moved_state();
    $url     = (string)$st['url'];
    $sec     = max(3, (int)$st['seconds']);
    $isApi   = strpos((string)parse_url((string)($_SERVER['REQUEST_URI'] ?? ''), PHP_URL_PATH), '/api/') === 0;

    header('X-Robots-Tag: noindex, nofollow');
    header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');

    if ($isApi) {
        // L'app vecchia chiede dati: meglio una risposta chiara (410 Gone)
        // che una pagina HTML dentro un parser JSON.
        http_response_code(410);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode([
            'success' => false,
            'moved'   => true,
            'error'   => 'L.U.X. Verse si è trasferito su ' . $url . '. Aggiorna l\'app: la versione vecchia non riceve più i dati.',
            'error_en'=> 'L.U.X. Verse has moved to ' . $url . '. Please update the app: the old version no longer receives data.',
            'url'     => $url,
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }

    http_response_code(200);
    header('Content-Type: text/html; charset=utf-8');

    $host    = htmlspecialchars((string)parse_url($url, PHP_URL_HOST), ENT_QUOTES);
    $urlAttr = htmlspecialchars($url, ENT_QUOTES);
    $badgeIt = lux_maintenance_text((string)$st['badge_it'], false);
    $badgeEn = lux_maintenance_text((string)$st['badge_en'], false);
    $titleIt = lux_maintenance_text((string)$st['title_it']);
    $titleEn = lux_maintenance_text((string)$st['title_en']);
    $msgIt   = lux_maintenance_text((string)$st['message_it']);
    $msgEn   = lux_maintenance_text((string)$st['message_en']);
    $whyIt   = lux_maintenance_text((string)$st['why_it']);
    $whyEn   = lux_maintenance_text((string)$st['why_en']);
    $btnIt   = lux_maintenance_text((string)$st['button_it'], false);
    $btnEn   = lux_maintenance_text((string)$st['button_en'], false);
    ?><!DOCTYPE html>
<html lang="it">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="robots" content="noindex, nofollow">
<meta http-equiv="refresh" content="<?= $sec ?>; url=<?= $urlAttr ?>">
<link rel="canonical" href="<?= $urlAttr ?>">
<title>L.U.X. Verse si è trasferito · <?= $host ?></title>
<style>
    :root { --gold: #D8A93C; --bone: #EDE9E0; --dim: #A5A197; --line: rgba(255,255,255,.09); }
    * { margin: 0; padding: 0; box-sizing: border-box; }
    body { min-height: 100vh; display: flex; align-items: center; justify-content: center; padding: 28px;
        font-family: 'Inter', -apple-system, 'Segoe UI', Roboto, sans-serif; color: var(--bone);
        background: radial-gradient(120% 120% at 50% 0%, #12141A 0%, #0A0B0D 62%); }
    .box { width: 100%; max-width: 660px; }
    .brand { display: flex; align-items: center; gap: 11px; margin-bottom: 30px; }
    .brand span { font-size: 13px; letter-spacing: .34em; text-transform: uppercase; color: var(--dim); white-space: nowrap; }
    .brand .badge-en { margin-left: auto; font-size: 10px; letter-spacing: .12em; color: rgba(165,161,151,.7);
        border: 1px solid var(--line); border-radius: 999px; padding: 5px 9px; white-space: nowrap; }
    @media (max-width: 560px) { .brand .badge-en { display: none; } }
    .badge { display: inline-flex; align-items: center; gap: 9px; padding: 7px 13px; margin-bottom: 22px;
        border: 1px solid var(--line); border-radius: 999px; font-size: 11px; letter-spacing: .18em;
        text-transform: uppercase; color: var(--gold); }
    .dot { width: 7px; height: 7px; border-radius: 50%; background: var(--gold); animation: pulse 2.4s ease-in-out infinite; }
    @keyframes pulse { 0%,100% { opacity: 1; } 50% { opacity: .25; } }
    h1 { font-size: clamp(28px, 5vw, 44px); line-height: 1.12; font-weight: 700; letter-spacing: -.02em; margin-bottom: 20px; }
    h1 em { font-style: normal; color: var(--gold); }
    p.msg { font-size: 17px; line-height: 1.75; max-width: 60ch; }
    p.why { margin-top: 16px; font-size: 14.5px; line-height: 1.7; color: var(--dim); max-width: 62ch; }
    .new { margin-top: 26px; font-size: 22px; font-weight: 700; letter-spacing: -.01em; color: var(--gold); word-break: break-all; }
    .go { display: inline-flex; align-items: center; gap: 10px; margin-top: 18px; padding: 15px 26px;
        border-radius: 12px; background: var(--gold); color: #12141A; font-size: 16px; font-weight: 700;
        text-decoration: none; }
    .go:hover { background: #E7BC55; }
    .count { margin-top: 20px; font-size: 14.5px; color: var(--dim); }
    .count b { color: var(--bone); font-variant-numeric: tabular-nums; }
    .bar { height: 3px; width: 100%; margin-top: 22px; border-radius: 3px; overflow: hidden; background: rgba(255,255,255,.07); }
    .bar i { display: block; height: 100%; width: 0; background: linear-gradient(90deg, rgba(216,169,60,.35), var(--gold));
        animation: fill <?= $sec ?>s linear forwards; }
    @keyframes fill { to { width: 100%; } }
    .foot { margin-top: 30px; padding-top: 18px; border-top: 1px solid var(--line); font-size: 12.5px; color: var(--dim); }
    .en { margin-top: 32px; padding-top: 24px; border-top: 1px solid var(--line); }
    .en-title { font-size: 14px; letter-spacing: .22em; text-transform: uppercase; color: var(--dim); margin-bottom: 12px; }
    .en p { font-size: 14px; line-height: 1.7; color: var(--dim); max-width: 62ch; }
    .en .go2 { display: inline-block; margin-top: 14px; font-size: 13.5px; color: var(--gold); text-decoration: none;
        border-bottom: 1px solid rgba(216,169,60,.35); }
</style>
</head>
<body>
    <div class="box">
        <div class="brand">
            <svg aria-hidden="true" width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="#D8A93C" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="12" cy="12" r="9.2"></circle><path d="M12 7.4V12l3.4 2.2"></path>
            </svg>
            <span>L.U.X. Verse</span>
            <span class="badge-en"><?= $badgeEn ?></span>
        </div>

        <div class="badge"><span class="dot"></span> <?= $badgeIt ?></div>

        <h1><?= $titleIt ?></h1>

        <p class="msg"><?= $msgIt ?></p>

        <p class="why"><?= $whyIt ?></p>

        <div class="new"><?= $host ?></div>

        <a class="go" href="<?= $urlAttr ?>"><?= $btnIt ?> <span aria-hidden="true">&rarr;</span></a>

        <p class="count">Fra <b id="lux-sec"><?= $sec ?></b> secondi ti portiamo noi. Nessuna attesa se preferisci premere il pulsante.</p>

        <div class="bar"><i></i></div>

        <p class="foot">Il vecchio indirizzo non si aggiorna più: i link che avevi salvato continuano a funzionare, perché portano qui e da qui al sito nuovo. I file <code>.apk</code> dell'app restano scaricabili anche da qui.</p>

        <div class="en">
            <p class="en-title"><?= $badgeEn ?></p>
            <p><?= $titleEn ?></p>
            <p style="margin-top:12px"><?= $msgEn ?></p>
            <p style="margin-top:10px"><?= $whyEn ?></p>
            <a class="go2" href="<?= $urlAttr ?>"><?= $btnEn ?>: <?= $host ?> &rarr;</a>
        </div>
    </div>
    <script>
    (function () {
        var target = <?= json_encode($url) ?>;
        var left   = <?= $sec ?>;
        var el     = document.getElementById('lux-sec');
        var t = setInterval(function () {
            left--;
            if (left <= 0) { clearInterval(t); window.location.replace(target); return; }
            if (el) el.textContent = left;
        }, 1000);
    })();
    </script>
</body>
</html><?php
    exit;
}

/**
 * Punto d'ingresso: chiamato da config.php a ogni richiesta, prima di
 * caricare qualunque altra cosa (catalogo compreso: 100.000 strumenti
 * sarebbero lavoro inutile per una pagina di manutenzione).
 */
function lux_maintenance_guard(): void
{
    // Prima di tutto il trasloco: se siamo sul dominio vecchio si mostra
    // solo il cartello, e niente altro del sito viene caricato.
    if (lux_moved_active() && !lux_moved_open_request()) {
        $path = (string)parse_url((string)($_SERVER['REQUEST_URI'] ?? ''), PHP_URL_PATH);
        lux_moved_robots_and_maps($path);
        lux_moved_render();
    }

    if (!lux_maintenance_active()) return;
    if (lux_maintenance_open_request()) return;
    lux_maintenance_render();
}
