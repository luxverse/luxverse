<?php
// ============================================================
// PERCORSO DEL RILASCIO — L.U.X. VERSE 39.1.1 (struttura nuova)
// ============================================================
// La roadmap pubblica: cosa è fatto, cosa è in corso, cosa arriva dopo. Sta
// sul sito e non in un messaggio, così chiunque può controllare se quello
// che avevamo promesso è stato mantenuto.
//
// I dati delle fasi NON stanno più scritti qui: stanno in
// includes/roadmap.php, perché le stesse voci compaiono anche nella linea
// del tempo. Numeri veri presi dal registro dei tool eseguibili
// (data/agent_tools.json) e dalle versioni (LUX_SITE_VERSION, LUX_APP_VERSION):
// niente cifre inventate.
// ============================================================

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/page.php';
require_once __DIR__ . '/includes/roadmap.php';
lux_session_start();

$lang    = currentLang();
$isIt    = ($lang === 'it');
$tools   = function_exists('lux_agent_tools') ? lux_agent_tools() : [];
$engines = function_exists('lux_agent_engine_counts') ? lux_agent_engine_counts() : ['local' => 0, 'ai' => 0];
$nTools  = count($tools);
$nLocal  = (int)($engines['local'] ?? 0);
$nAi     = (int)($engines['ai'] ?? 0);
$total   = function_exists('lux_catalog_total') ? lux_catalog_total() : 0;
$fmt     = number_format($total, 0, $isIt ? ',' : '.', $isIt ? '.' : ',');

$phases = lux_roadmap_phases($isIt, ['tools' => $nTools, 'local' => $nLocal, 'ai' => $nAi, 'total' => $total]);

$nOk   = count($phases[0]['items'] ?? []);
$nWork = count($phases[1]['items'] ?? []);
$nNext = count($phases[2]['items'] ?? []);

$siteVer = defined('LUX_SITE_VERSION') ? (string)LUX_SITE_VERSION : '';
$appVer  = defined('LUX_APP_VERSION') ? (string)LUX_APP_VERSION : '';

$pageTitle       = $isIt ? 'Percorso del rilascio — L.U.X. Verse' : 'Release roadmap — L.U.X. Verse';
$pageDescription = $isIt
    ? 'Il percorso del rilascio: cosa è già online, cosa arriva prima, cosa dopo. Ogni voce dice cosa cambia per chi usa il sito, senza promesse gonfiate.'
    : 'The release path: what is already online, what comes first, what comes later. Every item says what changes for the people using the site, without inflated promises.';
$pageKeywords    = 'roadmap, percorso, rilascio, aggiornamento, L.U.X. Verse, cosa arriva';
$pageCSS         = '';

$sezioni = [
    ['id' => 'regola', 'n' => '01', 't' => $isIt ? 'La regola di ogni rilascio' : 'The rule of every release'],
    ['id' => 'fatto',  'n' => '02', 't' => $isIt ? 'Fatto — in questo rilascio' : 'Done — in this release'],
    ['id' => 'corso',  'n' => '03', 't' => $isIt ? 'Subito dopo — in corso' : 'Right after — in progress'],
    ['id' => 'dopo',   'n' => '04', 't' => $isIt ? 'Più avanti — annunciato' : 'Later — announced'],
    ['id' => 'motore', 'n' => '05', 't' => $isIt ? 'Gli strumenti che il motore esegue oggi' : 'The tools the engine can run today'],
    ['id' => 'numeri', 'n' => '06', 't' => $isIt ? 'Versioni e numeri veri' : 'Versions and real numbers'],
];

lux_header();
lux_page_open([
    'crumbs'   => [['t' => 'Home', 'u' => '/index.php'], ['t' => $isIt ? 'Percorso del rilascio' : 'Release roadmap']],
    'kicker'   => $isIt ? 'Cosa arriva, in che ordine' : 'What arrives, in which order',
    'title'    => $isIt ? 'Percorso del rilascio' : 'Release roadmap',
    'lead'     => $isIt
        ? 'Da un sito con centomila strumenti a una piattaforma che capisce, esegue e ricorda. Qui c\'è il percorso in ordine: prima quello che rende l\'ecosistema più utile, poi tutto il resto. Se una cosa non c\'è, è scritta qui sotto, non promessa a metà.'
        : 'From a site with one hundred thousand tools to a platform that understands, runs and remembers. Here is the path in order: first what makes the ecosystem more useful, then everything else. If something is not there, it is written below, not promised halfway.',
    'meta'     => [
        ($isIt ? 'Versione ' : 'Version ') . $siteVer,
        $nOk . ' ' . ($isIt ? 'voci fatte' : 'items done') . ' · ' . $nWork . ' ' . ($isIt ? 'in corso' : 'in progress') . ' · ' . $nNext . ' ' . ($isIt ? 'annunciate' : 'announced'),
        $isIt ? 'Ultimo aggiornamento: 15 ottobre 2026' : 'Last update: 15 October 2026',
    ],
    'actions'  => [
        ['t' => $isIt ? 'Prova il motore' : 'Try the engine', 'u' => '/index.php', 'p' => true],
        ['t' => t('Linea del Tempo'), 'u' => '/linea-del-tempo.php'],
    ],
    'sections' => $sezioni,
]);

lux_page_section([
    'n' => '01', 'id' => 'regola',
    't' => $isIt ? 'La regola di ogni rilascio' : 'The rule of every release',
    'body' => $isIt
        ? '<p>Ogni cosa che aggiungiamo deve rendere l\'ecosistema <strong>più utile</strong>, non solo più ricco. Se una funzione non fa risparmiare tempo a chi usa il sito, non entra nel rilascio: resta qui, nella parte «più avanti», finché non ha un senso vero.</p>
           <p>Lo stato di ogni voce è quello reale: <strong>Fatto</strong> solo per quello che è online adesso e si può provare, <strong>In corso</strong> per quello che è cominciato, <strong>Annunciato</strong> per quello che abbiamo in mente ma non promettiamo per oggi. Dove la prima parte è già online, il collegamento è lì accanto.</p>'
        : '<p>Everything we add must make the ecosystem <strong>more useful</strong>, not just bigger. If a feature does not save time for the people using the site, it does not enter the release: it stays here, under "later", until it has a real purpose.</p>
           <p>The state of every item is the real one: <strong>Done</strong> only for what is online now and can be tried, <strong>In progress</strong> for what has started, <strong>Announced</strong> for what we have in mind but do not promise for today. Where the first part is already online, the link sits right next to it.</p>',
    'brief' => [
        't' => $isIt ? 'Come si legge' : 'How to read it',
        'items' => $isIt
            ? ['Pallino verde: fatto e provabile adesso', 'Pallino oro: in corso, la prima parte c\'è', 'Pallino azzurro: annunciato, non promesso per oggi', 'Un collegamento = quella cosa vive già su una pagina']
            : ['Green dot: done and provable now', 'Gold dot: in progress, the first part exists', 'Blue dot: announced, not promised for today', 'A link = that thing already lives on a page'],
    ],
]);

/** Una fase completa: intestazione + voci con stato e collegamento. */
function lux_roadmap_phase(array $ph, bool $isIt): void
{
    ?>
    <div class="rl-phase">
        <span class="agent-badge <?= htmlspecialchars($ph['state']) ?>"><?= htmlspecialchars(lux_roadmap_state_label($ph['state'], $isIt)) ?></span>
        <h3><?= htmlspecialchars($ph['label']) ?></h3>
        <span class="rl-count"><?= count($ph['items']) ?> <?= $isIt ? 'voci' : 'items' ?></span>
    </div>
    <div class="rl-list">
        <?php foreach ($ph['items'] as $it): ?>
        <div class="rl-item state-<?= htmlspecialchars($ph['state']) ?>">
            <div class="rl-head">
                <h4 class="rl-title"><?= htmlspecialchars($it['t']) ?></h4>
            </div>
            <p class="rl-text"><?= htmlspecialchars($it['d']) ?></p>
            <?= lux_roadmap_note_html((string)$it['n']) ?>
        </div>
        <?php endforeach; ?>
    </div>
    <?php
}

lux_page_section([
    'n' => '02', 'id' => 'fatto',
    't' => $isIt ? 'Fatto — in questo rilascio' : 'Done — in this release',
    'body' => ($isIt
        ? '<p>Quello che è online adesso, e che puoi provare subito. Niente di questa lista è «quasi pronto»: se è qui, funziona.</p>'
        : '<p>What is online now, and that you can try right away. Nothing on this list is "almost ready": if it is here, it works.</p>')
        . call_user_func(function () use ($phases, $isIt) {
            ob_start();
            lux_roadmap_phase($phases[0], $isIt);
            return ob_get_clean();
        }),
]);

lux_page_section([
    'n' => '03', 'id' => 'corso',
    't' => $isIt ? 'Subito dopo — in corso' : 'Right after — in progress',
    'body' => ($isIt
        ? '<p>Quello su cui stiamo lavorando adesso. La prima parte di alcune voci è già online: dove c\'è il collegamento, si può già usare.</p>'
        : '<p>What we are working on right now. The first part of some items is already online: where the link is, it can already be used.</p>')
        . call_user_func(function () use ($phases, $isIt) {
            ob_start();
            lux_roadmap_phase($phases[1], $isIt);
            return ob_get_clean();
        }),
]);

lux_page_section([
    'n' => '04', 'id' => 'dopo',
    't' => $isIt ? 'Più avanti — annunciato, non promesso per oggi' : 'Later — announced, not promised for today',
    'body' => ($isIt
        ? '<p>Sono cose che vogliamo fare, dette ad alta voce perché chi usa il sito ha il diritto di sapere dove andiamo. Non hanno una data: le mettiamo qui per non farle sembrare già fatte.</p>'
        : '<p>Things we want to do, said out loud because people using the site have the right to know where we are going. They have no date: we put them here so they do not look already done.</p>')
        . call_user_func(function () use ($phases, $isIt) {
            ob_start();
            lux_roadmap_phase($phases[2], $isIt);
            return ob_get_clean();
        }),
]);

$toolsHtml = '<p>' . ($isIt
    ? 'Elenco completo di quello che il motore può eseguire adesso, con il suo ingresso e la sua uscita. Si aggiorna dal registro vero (<code>data/agent_tools.json</code>): se uno strumento non è qui, non è ancora eseguibile e resta una pagina da aprire.'
    : 'The full list of what the engine can run right now, with its input and output. It is generated from the real registry (<code>data/agent_tools.json</code>): if a tool is not here, it is not runnable yet and stays a page to open.')
    . '</p><div class="rl-list">';
foreach (array_slice($tools, 0, 70) as $key => $t2) {
    $toolsHtml .= '<a class="rl-item" style="display:block;text-decoration:none" href="'
        . htmlspecialchars((string)($t2['endpoint'] ?? ('/' . $key . '.php'))) . '">'
        . '<h4 class="rl-title">' . htmlspecialchars((string)($t2['name'] ?? $key)) . '</h4>'
        . '<p class="rl-text">' . htmlspecialchars((string)($isIt ? ($t2['desc_it'] ?? '') : ($t2['desc_en'] ?? ($t2['desc_it'] ?? '')))) . '</p>'
        . '<span class="rl-note">' . htmlspecialchars($isIt ? 'ingressi' : 'inputs') . ': '
        . htmlspecialchars(implode(', ', array_map(function ($f) { return (string)($f['name'] ?? ''); }, (array)($t2['inputs'] ?? []))))
        . ' · ' . (((string)($t2['engine'] ?? 'ai') === 'local') ? t('Instant') : 'AI') . '</span></a>';
}
$toolsHtml .= '</div>';

lux_page_section([
    'n' => '05', 'id' => 'motore',
    't' => $isIt ? 'Gli strumenti che il motore esegue oggi' : 'The tools the engine can run today',
    'body' => $toolsHtml,
]);

lux_page_section([
    'n' => '06', 'id' => 'numeri',
    't' => $isIt ? 'Versioni e numeri veri' : 'Versions and real numbers',
    'body' => $isIt
        ? '<ul>
             <li><strong>Piattaforma:</strong> versione ' . htmlspecialchars($siteVer) . '.</li>
             <li><strong>App Android:</strong> versione ' . htmlspecialchars($appVer) . '. Il numero dell\'app cambia solo quando esce davvero un APK nuovo.</li>
             <li><strong>Catalogo:</strong> ' . $fmt . ' strumenti, di cui <strong>' . $nTools . '</strong> eseguibili dal motore (' . $nLocal . ' istantanei, ' . $nAi . ' con IA).</li>
             <li><strong>Sito:</strong> ' . $nOk . ' voci fatte, ' . $nWork . ' in corso, ' . $nNext . ' annunciate in questo percorso.</li>
           </ul>
           <p>Questi numeri si leggono dai file veri, non sono scritti a mano: se il catalogo cambia, cambiano anche qui.</p>'
        : '<ul>
             <li><strong>Platform:</strong> version ' . htmlspecialchars($siteVer) . '.</li>
             <li><strong>Android app:</strong> version ' . htmlspecialchars($appVer) . '. The app number changes only when a new APK actually ships.</li>
             <li><strong>Catalogue:</strong> ' . $fmt . ' tools, of which <strong>' . $nTools . '</strong> are runnable by the engine (' . $nLocal . ' instant, ' . $nAi . ' AI).</li>
             <li><strong>Site:</strong> ' . $nOk . ' items done, ' . $nWork . ' in progress, ' . $nNext . ' announced on this path.</li>
           </ul>
           <p>These numbers are read from the real files, not written by hand: if the catalogue changes, they change here too.</p>',
]);

lux_page_close([
    'next' => [
        ['t' => t('Linea del Tempo'), 'd' => $isIt ? 'Le tappe in ordine di data, e cosa stiamo costruendo.' : 'Milestones in date order, and what we are building.', 'u' => '/linea-del-tempo.php'],
        ['t' => t('Bacheca'), 'd' => $isIt ? 'Gli avvisi per esteso, con ricerca e filtri.' : 'The notices in full, with search and filters.', 'u' => '/bacheca.php'],
        ['t' => $isIt ? 'Proponi uno strumento' : 'Suggest a tool', 'd' => $isIt ? 'Manca qualcosa? Dillo e vota le idee degli altri.' : 'Something missing? Say it and vote on other ideas.', 'u' => '/proponi.php'],
    ],
]);
lux_footer();
