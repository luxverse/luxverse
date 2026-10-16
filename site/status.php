<?php
// ============================================================
// STATO DEI SERVIZI — L.U.X. VERSE 39.1.1
// ============================================================
// I numeri non si scrivono a mano: arrivano da lux_status_report(), che
// misura davvero file, dati, configurazione ed estensioni del server, e
// tiene il risultato in cache 60 secondi (data/status.json).
// Se un dato non e' verificabile lo stato e' "na" e c'e' scritto il
// motivo, invece di una spunta verde inventata.
// ============================================================

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/page.php';
lux_session_start();

$lang = currentLang();
$isIt = ($lang === 'it');

$fresh  = isset($_GET['fresh']) && $_GET['fresh'] !== '';
$report = function_exists('lux_status_report') ? lux_status_report($fresh) : [];
$checks = (array)($report['checks'] ?? []);
$counts = (array)($report['counters'] ?? []);
$state  = (string)($report['state'] ?? 'na');
$health = (int)($report['health'] ?? 0);
$genAt  = (string)($report['generated'] ?? '');
$cache  = !empty($report['from_cache']);
$ms     = (float)($report['duration_ms'] ?? 0);

$stateLabel = [
    'ok'   => $isIt ? 'Tutto regolare' : 'Everything normal',
    'warn' => $isIt ? 'Qualche avviso' : 'Some warnings',
    'down' => $isIt ? 'Qualcosa non risponde' : 'Something is not responding',
    'na'   => $isIt ? 'Stato non disponibile' : 'Status unavailable',
][$state] ?? $state;

// Raggruppa i controlli per area.
$groups = [];
foreach ($checks as $c) {
    $g = (string)($c['group'] ?? ($isIt ? 'Altro' : 'Other'));
    $groups[$g][] = $c;
}

$pageTitle       = $isIt ? 'Stato dei servizi — L.U.X. Verse' : 'Service status — L.U.X. Verse';
$pageDescription = $isIt
    ? 'Lo stato reale dei servizi di L.U.X. Verse: controlli misurati sul server, con numeri veri e nessun servizio di monitoraggio esterno.'
    : 'The real status of L.U.X. Verse services: checks measured on the server, with real numbers and no external monitoring service.';
$pageKeywords    = 'stato, servizi, uptime, monitoraggio, L.U.X. Verse, status, health';
$pageCSS         = '';

$sezioni = [
    ['id' => 'adesso',   'n' => '01', 't' => $isIt ? 'Come stanno i servizi adesso' : 'How the services are doing now'],
    ['id' => 'controlli','n' => '02', 't' => $isIt ? 'I controlli, area per area' : 'The checks, area by area'],
    ['id' => 'misuriamo','n' => '03', 't' => $isIt ? 'Cosa misuriamo e cosa no' : 'What we measure and what we do not'],
    ['id' => 'problemi', 'n' => '04', 't' => $isIt ? 'Se qualcosa non risponde' : 'If something is not responding'],
];

lux_header();
lux_page_open([
    'crumbs'   => [['t' => 'Home', 'u' => '/index.php'], ['t' => t('Stato')]],
    'kicker'   => $isIt ? 'Trasparenza' : 'Transparency',
    'title'    => $isIt ? 'Stato dei servizi' : 'Service status',
    'lead'     => $isIt
        ? 'Quello che vedi qui è misurato sul server, non dichiarato: file pubblici, catalogo, API, dati e configurazione. Se un controllo non è verificabile lo trovi scritto, con il motivo.'
        : 'What you see here is measured on the server, not declared: public files, catalogue, APIs, data and configuration. If a check cannot be verified you find it written down, with the reason.',
    'meta'     => array_values(array_filter([
        $stateLabel,
        'Salute: ' . $health . '%',
        $genAt !== '' ? ($isIt ? 'Rilevato il ' . date('d.m.Y H:i', strtotime($genAt)) : 'Measured on ' . date('d.m.Y H:i', strtotime($genAt))) . ($cache ? ($isIt ? ' (dalla cache)' : ' (from cache)') : '') : '',
        $ms > 0 ? number_format($ms, 0, ',', '.') . ' ms' : '',
    ])),
    'actions'  => [
        ['t' => $isIt ? 'Rimisura adesso' : 'Measure again', 'u' => '/status.php?fresh=1', 'p' => true],
        ['t' => t('Assistenza'), 'u' => '/assistenza.php'],
    ],
    'sections' => $sezioni,
]);

// 01 — sintesi
$sumHtml = '<div class="st-sum">'
    . '<span class="st-pill ok"><span class="st-dot ok"></span>' . ($isIt ? 'Regolari' : 'Normal') . ' <b>' . (int)($counts['ok'] ?? 0) . '</b></span>'
    . '<span class="st-pill warn"><span class="st-dot warn"></span>' . ($isIt ? 'Avvisi' : 'Warnings') . ' <b>' . (int)($counts['warn'] ?? 0) . '</b></span>'
    . '<span class="st-pill down"><span class="st-dot down"></span>' . ($isIt ? 'Problemi' : 'Problems') . ' <b>' . (int)($counts['down'] ?? 0) . '</b></span>'
    . '<span class="st-pill"><span class="st-dot"></span>' . ($isIt ? 'Controlli totali' : 'Total checks') . ' <b>' . (int)($counts['total'] ?? count($checks)) . '</b></span>'
    . '<span class="st-pill ' . ($state === 'ok' ? 'ok' : ($state === 'down' ? 'down' : 'warn')) . '"><b>' . htmlspecialchars($stateLabel) . '</b></span>'
    . '</div>';

lux_page_section([
    'n' => '01', 'id' => 'adesso',
    't' => $isIt ? 'Come stanno i servizi adesso' : 'How the services are doing now',
    'body' => $sumHtml . '<p>' . ($isIt
        ? 'Il riquadro qui sopra è il riassunto di tutti i controlli: <strong>regolari</strong> quando la misura è quella attesa, <strong>avviso</strong> quando è accettabile ma non perfetta, <strong>problema</strong> quando qualcosa non risponde o non è verificabile.'
        : 'The box above is the summary of every check: <strong>normal</strong> when the measurement is as expected, <strong>warning</strong> when it is acceptable but not perfect, <strong>problem</strong> when something is not responding or cannot be verified.') . '</p>',
    'brief' => [
        't' => $isIt ? 'Da dove arrivano questi numeri' : 'Where these numbers come from',
        'items' => $isIt
            ? [
                'Da un controllo che gira su questo server e guarda file, dati, configurazione ed estensioni di PHP.',
                'Il risultato resta in memoria 60 secondi: la pagina non rifà tutto il lavoro a ogni visita.',
                ($cache ? 'Questa lettura viene dalla memoria (meno di un minuto fa).' : 'Questa lettura è stata fatta adesso.'),
              ]
            : [
                'From a check that runs on this server and looks at files, data, configuration and PHP extensions.',
                'The result stays in memory for 60 seconds: the page does not redo all the work on every visit.',
                ($cache ? 'This reading comes from memory (less than a minute ago).' : 'This reading was taken just now.'),
              ],
    ],
]);

// 02 — i controlli
ob_start();
if (!$groups) {
    echo '<p>' . ($isIt ? 'Nessun controllo disponibile in questo momento.' : 'No check available right now.') . '</p>';
} else {
    foreach ($groups as $g => $rows) {
        echo '<h3>' . htmlspecialchars((string)$g) . '</h3><div class="st-list">';
        foreach ($rows as $c) {
            $st = (string)($c['status'] ?? 'na');
            echo '<div class="st-row"><span class="st-dot ' . htmlspecialchars($st) . '"></span>'
               . '<b>' . htmlspecialchars((string)($c['label'] ?? $c['key'] ?? '')) . '</b>'
               . '<span>' . htmlspecialchars((string)($c['detail'] ?? '')) . '</span>'
               . '<span class="st-ms">' . (isset($c['ms']) ? number_format((float)$c['ms'], 1, ',', '.') . ' ms' : '') . '</span></div>';
        }
        echo '</div>';
    }
}
$checksHtml = ob_get_clean();

lux_page_section([
    'n' => '02', 'id' => 'controlli',
    't' => $isIt ? 'I controlli, area per area' : 'The checks, area by area',
    'body' => $checksHtml,
]);

lux_page_section([
    'n' => '03', 'id' => 'misuriamo',
    't' => $isIt ? 'Cosa misuriamo e cosa no' : 'What we measure and what we do not',
    'body' => $isIt
        ? '<ul>
             <li><strong>Misuriamo:</strong> presenza e peso dei file pubblici, il catalogo (quanti strumenti e in quanto spazio), gli endpoint delle API, i dati che le pagine leggono davvero, la configurazione del server e le estensioni di PHP che servono.</li>
             <li><strong>Non misuriamo:</strong> chi sei, da dove ci segui, quale pagina hai aperto prima. Nessuno di questi controlli guarda il visitatore.</li>
             <li><strong>Non usiamo</strong> servizi di monitoraggio esterni: i controlli girano qui e i risultati restano qui.</li>
           </ul>'
        : '<ul>
             <li><strong>We measure:</strong> presence and size of public files, the catalogue (how many tools and how much space), the API endpoints, the data the pages actually read, the server configuration and the PHP extensions we need.</li>
             <li><strong>We do not measure:</strong> who you are, where you follow us from, which page you opened before. None of these checks looks at the visitor.</li>
             <li><strong>We do not use</strong> external monitoring services: the checks run here and the results stay here.</li>
           </ul>',
    'note' => $isIt
        ? 'Le misure possono cambiare da un minuto all\'altro: se un controllo ti sembra strano, premi «Rimisura adesso».'
        : 'Measurements can change from one minute to the next: if a check looks odd, press "Measure again".',
]);

lux_page_section([
    'n' => '04', 'id' => 'problemi',
    't' => $isIt ? 'Se qualcosa non risponde' : 'If something is not responding',
    'body' => $isIt
        ? '<p>Un controllo rosso o giallo non significa che il sito sia inutilizzabile: quasi sempre è una parte sola che stiamo sistemando. Se noti un problema che qui non compare, o che resta per più di qualche minuto, <a href="/assistenza.php">scrivici in Assistenza</a>: ci arriva tutto, anche il dettaglio tecnico della pagina da cui scrivi.</p>
           <p>Per sapere <em>cosa</em> abbiamo sistemato, e quando, la pagina giusta è la <a href="/linea-del-tempo.php">Linea del tempo</a>.</p>'
        : '<p>A red or yellow check does not mean the site is unusable: almost always it is a single part we are fixing. If you notice a problem that does not appear here, or that lasts more than a few minutes, <a href="/assistenza.php">write to us in Support</a>: everything reaches us, including the technical detail of the page you are writing from.</p>
           <p>To know <em>what</em> we fixed, and when, the right page is the <a href="/linea-del-tempo.php">Timeline</a>.</p>',
]);

lux_page_close([
    'next' => [
        ['t' => t('Linea del Tempo'), 'd' => $isIt ? 'Ogni tappa del progetto, in ordine di data.' : 'Every milestone of the project, in date order.', 'u' => '/linea-del-tempo.php'],
        ['t' => t('Assistenza'), 'd' => $isIt ? 'Un problema che non vedi qui? Scrivicelo.' : 'A problem you cannot see here? Tell us.', 'u' => '/assistenza.php'],
        ['t' => $isIt ? 'Percorso del rilascio' : 'Release roadmap', 'd' => $isIt ? 'Cosa è fatto, cosa è in corso, cosa arriva.' : 'What is done, in progress, coming.', 'u' => '/roadmap.php'],
    ],
]);
lux_footer();
