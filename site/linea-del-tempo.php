<?php
// ============================================================
// LINEA DEL TEMPO — L.U.X. VERSE 39.1.1
// ============================================================
// La pagina non racconta una storia inventata: le tappe sono gli avvisi
// della bacheca (data/notices.json), letti in ordine di data. Se un
// avviso c'e', la tappa c'e'; se un avviso si toglie, la tappa sparisce.
// Quando il titolo di un avviso contiene un numero di versione, la tappa
// viene marcata come rilascio.
//
// In piu' (39.1.1) la pagina porta anche quello che stiamo costruendo:
// le voci del percorso del rilascio con il loro stato e il collegamento
// alla pagina dove quella cosa vive gia'. I dati arrivano da
// includes/roadmap.php, gli stessi che usa la pagina del percorso: cosi'
// le due pagine non possono raccontare cose diverse.
// ============================================================

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/page.php';
require_once __DIR__ . '/includes/roadmap.php';
lux_session_start();

$lang = currentLang();
$isIt = ($lang === 'it');

$notices = getNoticesFromLocal(0);

// Tappe: data, titolo, testo breve, tipo, eventuale versione.
$steps = [];
foreach ($notices as $n) {
    $titleIt = (string)($n['title_it'] ?? ($n['title'] ?? ''));
    $titleEn = (string)($n['title_en'] ?? ($n['title'] ?? ''));
    $msgIt   = (string)($n['message_it'] ?? ($n['message'] ?? ''));
    $msgEn   = (string)($n['message_en'] ?? ($n['message'] ?? ''));
    $date    = (string)($n['date'] ?? ($n['start_date'] ?? ''));
    $ts      = $date !== '' ? (int)strtotime($date) : 0;
    $title   = $isIt ? $titleIt : $titleEn;
    $msg     = $isIt ? $msgIt : $msgEn;
    if ($msg === '') $msg = $isIt ? $msgEn : $msgIt;

    // Estratto leggibile: righe d'elenco via, prime frasi tenute.
    $msg = trim(preg_replace('/\s+/', ' ', str_replace('·', ' ', $msg)));
    if (function_exists('mb_substr')) $msg = mb_substr($msg, 0, 260) . (mb_strlen($msg) > 260 ? '…' : '');
    else $msg = substr($msg, 0, 260) . (strlen($msg) > 260 ? '…' : '');

    $meta = noticeTypeMeta($n['type'] ?? 'info');
    $ver  = '';
    if (preg_match('/(\d+\.\d+(?:\.\d+)?)/', $title, $m)) $ver = $m[1];

    $steps[] = [
        'ts'    => $ts,
        'date'  => $ts > 0 ? date('d.m.Y', $ts) : '',
        'title' => $title,
        'msg'   => $msg,
        'type'  => $meta['class'],
        'label' => $meta['label'],
        'ver'   => $ver,
        'sig'   => (string)($n['signature'] ?? ''),
    ];
}
usort($steps, function ($a, $b) { return $b['ts'] <=> $a['ts']; });

// Raggruppa per mese (la tappa resta leggibile anche su elenchi lunghi).
$byMonth = [];
foreach ($steps as $s) {
    $key = $s['ts'] > 0 ? date('Y-m', $s['ts']) : 'nd';
    $byMonth[$key][] = $s;
}

$first = $steps ? end($steps) : null;
$last  = $steps ? $steps[0] : null;

$pageTitle       = $isIt ? 'Linea del tempo — L.U.X. Verse' : 'Timeline — L.U.X. Verse';
$pageDescription = $isIt
    ? 'La storia di L.U.X. Verse in ordine di data: ogni rilascio, ogni correzione e ogni avviso, dagli avvisi della bacheca.'
    : 'The history of L.U.X. Verse in date order: every release, every fix and every notice, straight from the board.';
$pageKeywords    = 'linea del tempo, storia, tappe, rilasci, L.U.X. Verse, timeline, changelog';
$pageCSS         = '';

$sezioni = [
    ['id' => 'come',   'n' => '01', 't' => $isIt ? 'Come si legge questa pagina' : 'How to read this page'],
    ['id' => 'tappe',  'n' => '02', 't' => $isIt ? 'Le tappe' : 'The milestones'],
    ['id' => 'avanti', 'n' => '03', 't' => $isIt ? 'Dove stiamo andando' : 'Where we are going'],
    ['id' => 'regola', 'n' => '04', 't' => $isIt ? 'Come teniamo traccia d\'ora in avanti' : 'How we keep track from now on'],
];

lux_header();
lux_page_open([
    'crumbs'   => [['t' => 'Home', 'u' => '/index.php'], ['t' => t('Linea del Tempo')]],
    'kicker'   => $isIt ? 'Da dove veniamo' : 'Where we come from',
    'title'    => $isIt ? 'Linea del tempo' : 'Timeline',
    'lead'     => $isIt
        ? 'Qui c\'è la storia del progetto in ordine di data: la nascita, i rilasci, le correzioni, i passi indietro. Sono le stesse voci che leggi in bacheca, messe in fila.'
        : 'Here is the history of the project in date order: the beginning, the releases, the fixes, the steps back. They are the same entries you read on the board, put in a line.',
    'meta'     => array_values(array_filter([
        count($steps) . ' ' . ($isIt ? 'tappe' : 'milestones'),
        $first && $first['date'] !== '' ? ($isIt ? 'Dalla prima: ' . $first['date'] : 'From the first: ' . $first['date']) : '',
        $last && $last['date'] !== '' ? ($isIt ? 'All\'ultima: ' . $last['date'] : 'To the latest: ' . $last['date']) : '',
    ])),
    'actions'  => [
        ['t' => t('Bacheca'), 'u' => '/bacheca.php', 'p' => true],
        ['t' => $isIt ? 'Percorso del rilascio' : 'Release roadmap', 'u' => '/roadmap.php'],
    ],
    'sections' => $sezioni,
]);

lux_page_section([
    'n' => '01', 'id' => 'come',
    't' => $isIt ? 'Come si legge questa pagina' : 'How to read this page',
    'body' => $isIt
        ? '<p>Ogni tappa è un avviso pubblicato in bacheca, con la sua data. Dove compare un numero di versione (per esempio <code>39.1.1</code>) vuol dire che quella tappa è un rilascio della piattaforma; le altre sono correzioni, avvisi o comunicazioni.</p>
           <p>Le tappe sono in ordine dalla più recente alla più vecchia, raggruppate per mese. Non c\'è nulla scritto a mano: se un avviso viene tolto dalla bacheca, sparisce anche da qui.</p>'
        : '<p>Every milestone is a notice published on the board, with its date. Where a version number appears (for example <code>39.1.1</code>) it means that milestone is a platform release; the others are fixes, notices or announcements.</p>
           <p>Milestones run from the most recent to the oldest, grouped by month. Nothing is written by hand: if a notice is removed from the board, it disappears from here too.</p>',
    'brief' => [
        't' => $isIt ? 'In breve' : 'In short',
        'items' => $isIt
            ? [count($steps) . ' tappe pubblicate', 'Dati presi dalla bacheca, non riscritti', 'I rilasci sono marcati con il numero di versione']
            : [count($steps) . ' published milestones', 'Data taken from the board, not rewritten', 'Releases are marked with the version number'],
    ],
]);

ob_start();
if (!$byMonth) {
    echo '<p>' . ($isIt ? 'Nessuna tappa pubblicata al momento.' : 'No milestone published yet.') . '</p>';
} else {
    $monthNames = [1 => 'gennaio', 2 => 'febbraio', 3 => 'marzo', 4 => 'aprile', 5 => 'maggio', 6 => 'giugno',
                   7 => 'luglio', 8 => 'agosto', 9 => 'settembre', 10 => 'ottobre', 11 => 'novembre', 12 => 'dicembre'];
    $monthNamesEn = [1 => 'January', 2 => 'February', 3 => 'March', 4 => 'April', 5 => 'May', 6 => 'June',
                     7 => 'July', 8 => 'August', 9 => 'September', 10 => 'October', 11 => 'November', 12 => 'December'];
    foreach ($byMonth as $key => $rows) {
        if ($key === 'nd') {
            $label = $isIt ? 'Senza data' : 'Without a date';
        } else {
            [$y, $m] = array_map('intval', explode('-', $key));
            $label = ($isIt ? $monthNames[$m] : $monthNamesEn[$m]) . ' ' . $y;
        }
        echo '<h3>' . htmlspecialchars(ucfirst($label)) . '</h3><ul class="tl">';
        foreach ($rows as $s) {
            $isRelease = $s['ver'] !== '';
            echo '<li class="tl-item' . ($isRelease ? '' : ' dim') . '">'
               . '<span class="tl-date">' . htmlspecialchars($s['date']) . '</span>'
               . '<h4 class="tl-title">' . htmlspecialchars($s['title'])
               . ($isRelease ? '<span class="tl-tag">' . ($isIt ? 'rilascio' : 'release') . ' ' . htmlspecialchars($s['ver']) . '</span>' : '')
               . '</h4>'
               . ($s['msg'] !== '' ? '<p class="tl-text">' . htmlspecialchars($s['msg']) . '</p>' : '')
               . '</li>';
        }
        echo '</ul>';
    }
}
$tlHtml = ob_get_clean();

lux_page_section([
    'n' => '02', 'id' => 'tappe',
    't' => $isIt ? 'Le tappe' : 'The milestones',
    'body' => $tlHtml,
]);

// ------------------------------------------------------------
// DOVE STIAMO ANDANDO — le voci del percorso con il loro stato
// ------------------------------------------------------------
// Stessa fonte della pagina del percorso: nessun elenco scritto a mano
// che invecchia da solo.
$rmStats = [
    'tools' => count(function_exists('lux_agent_tools') ? lux_agent_tools() : []),
    'local' => (int)(lux_agent_engine_counts()['local'] ?? 0),
    'ai'    => (int)(lux_agent_engine_counts()['ai'] ?? 0),
    'total' => function_exists('lux_catalog_total') ? lux_catalog_total() : 0,
];
$rmPhases = lux_roadmap_phases($isIt, $rmStats);

ob_start();
echo '<p>' . ($isIt
    ? 'Le tappe raccontano quello che è già successo. Qui invece c\'è quello che stiamo costruendo adesso e quello che abbiamo annunciato: lo stato di ogni voce è quello vero, e dove la prima parte è già online trovi il collegamento.'
    : 'The milestones tell what has already happened. Here instead is what we are building now and what we have announced: the state of every item is the real one, and where the first part is already online you find the link.') . '</p>';
foreach ($rmPhases as $i => $ph) {
    echo '<div class="rl-phase"><span class="agent-badge ' . htmlspecialchars($ph['state']) . '">'
       . htmlspecialchars(lux_roadmap_state_label($ph['state'], $isIt)) . '</span>'
       . '<h3>' . htmlspecialchars($ph['label']) . '</h3>'
       . '<span class="rl-count">' . count($ph['items']) . ' ' . ($isIt ? 'voci' : 'items') . '</span></div>';
    echo '<div class="rl-list">';
    foreach ($ph['items'] as $it) {
        echo '<div class="rl-item state-' . htmlspecialchars($ph['state']) . '">'
           . '<h4 class="rl-title">' . htmlspecialchars($it['t']) . '</h4>'
           . '<p class="rl-text">' . htmlspecialchars($it['d']) . '</p>'
           . lux_roadmap_note_html((string)$it['n'])
           . '</div>';
    }
    echo '</div>';
}
$rmHtml = ob_get_clean();

lux_page_section([
    'n' => '03', 'id' => 'avanti',
    't' => $isIt ? 'Dove stiamo andando' : 'Where we are going',
    'body' => $rmHtml,
]);

lux_page_section([
    'n' => '04', 'id' => 'regola',
    't' => $isIt ? 'Come teniamo traccia d\'ora in avanti' : 'How we keep track from now on',
    'body' => $isIt
        ? '<p>Da questo rilascio valgono due regole. <strong>Ogni rilascio ha il suo avviso</strong> in bacheca, con il numero di versione: così la storia si costruisce da sola, senza che nessuno la riscriva a memoria.</p>
           <p><strong>Il percorso è pubblico.</strong> Nella <a href="/roadmap.php">pagina del rilascio</a> c\'è scritto cosa è fatto, cosa è in corso e cosa arriva dopo: se qualcosa che avevamo promesso non c\'è, lo troviamo scritto lì.</p>'
        : '<p>From this release two rules apply. <strong>Every release has its notice</strong> on the board, with the version number: that way the history writes itself, without anyone relying on memory.</p>
           <p><strong>The path is public.</strong> The <a href="/roadmap.php">release page</a> says what is done, what is in progress and what comes next: if something we promised is missing, it is written there.</p>',
]);

lux_page_close([
    'next' => [
        ['t' => t('Bacheca'), 'd' => $isIt ? 'Gli avvisi per esteso, con ricerca e filtri.' : 'The notices in full, with search and filters.', 'u' => '/bacheca.php'],
        ['t' => $isIt ? 'Percorso del rilascio' : 'Release roadmap', 'd' => $isIt ? 'Tutte le voci con stato, collegamenti e numeri veri.' : 'Every item with its state, links and real numbers.', 'u' => '/roadmap.php'],
        ['t' => t('Stato'), 'd' => $isIt ? 'Come stanno i servizi, misurato adesso.' : 'How the services are doing, measured now.', 'u' => '/status.php'],
    ],
]);
lux_footer();
