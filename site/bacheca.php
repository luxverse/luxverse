<?php
// ============================================================
// BACHECA — L.U.X. VERSE 39.1.2 (struttura rifatta)
// ============================================================
// Com'era (39.1.1): "In evidenza" con al massimo due avvisi fissati, messi
// affiancati e TAGLIATI dopo sei righe; sotto, l'elenco con ricerca e filtri.
// Due problemi veri, visti sulla pagina:
//   1. il testo degli avvisi fissati veniva tagliato a metà frase, e non
//      c'era nessun altro posto dove leggerlo per intero: l'avviso del
//      trasloco si fermava a "…sono già al nuovo indirizzo" e buona parte di
//      quello che c'era scritto non si poteva leggere;
//   2. con tutti gli avvisi fissati (è il caso di adesso) "In evidenza"
//      mostrava due avvisi su nove: decideva l'ordine di importanza, e chi
//      leggeva non vedeva né il resto né il testo completo.
//
// Com'è adesso — tre passaggi, tutti raggiungibili scorrendo la pagina:
//   1. IN APERTURA: l'avviso più recente fra i fissati, a tutta larghezza e
//      PER INTERO. Uno solo, così si sa da dove cominciare;
//   2. L'INDICE DELLA BACHECA: l'elenco compatto di tutti gli avvisi (data,
//      tipo, titolo). In un colpo d'occhio si vede cosa c'è e si salta dove
//      serve, senza scorrere tutto;
//   3. TUTTI GLI AVVISI: ricerca, filtri per tipo e conteggi, e sotto ogni
//      avviso per esteso, raggruppato per mese. Nessun avviso esce da qui:
//      quello che sta in apertura resta anche nell'indice, e tutto il resto
//      resta nell'elenco. Niente testo tagliato, in nessun punto.
//
// Il testo di ogni avviso passa da lux_notice_body() (includes/lux.php), che
// rende visibile la struttura che chi scrive usa già: voci numerate, titoli in
// maiuscolo, voci con la "·" e capoversi. Le parole non cambiano.
//
// La colonna a destra spiega come leggere la pagina, elenca i tipi (e i tipi
// si possono cliccare per filtrare l'elenco) e tiene i collegamenti utili.
// ============================================================

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/page.php';
lux_session_start();

$lang = currentLang();
$isIt = ($lang === 'it');

$notices = getNoticesFromLocal(0);      // fissati prima, poi dal più recente

// ------------------------------------------------------------
// 1. IN APERTURA — l'avviso più recente fra i fissati
// ------------------------------------------------------------
// Se nessun avviso è fissato si prende semplicemente il più recente: la
// pagina ha sempre un inizio, anche se chi scrive non fissa niente.
$openIdx = null;
foreach ($notices as $i => $n) {
    if (!empty($n['pinned'])) { $openIdx = $i; break; }
}
if ($openIdx === null && $notices) $openIdx = 0;
$opener = ($openIdx === null) ? null : $notices[$openIdx];

// Ogni avviso ha la sua ancora (#a1, #a2…): l'indice punta lì, e il
// collegamento a un avviso si può copiare e mandare a qualcuno.
$rows = [];
foreach ($notices as $i => $n) {
    $rows[] = ['n' => $n, 'id' => 'a' . ($i + 1), 'open' => ($i === $openIdx)];
}
$list = [];
foreach ($rows as $r) { if (!$r['open']) $list[] = $r; }

// ------------------------------------------------------------
// 2. TIPI, DATE, GRUPPI PER MESE
// ------------------------------------------------------------
$presentTypes = [];
foreach ($notices as $n) {
    $meta = noticeTypeMeta($n['type'] ?? 'info');
    $key  = $meta['class'] !== '' ? $meta['class'] : 'info';
    if (!isset($presentTypes[$key])) {
        $presentTypes[$key] = ['class' => $meta['class'], 'label' => $meta['label'], 'count' => 0];
    }
    $presentTypes[$key]['count']++;
}

$monthIt = [1 => 'Gennaio', 'Febbraio', 'Marzo', 'Aprile', 'Maggio', 'Giugno',
            'Luglio', 'Agosto', 'Settembre', 'Ottobre', 'Novembre', 'Dicembre'];
$monthEn = [1 => 'January', 'February', 'March', 'April', 'May', 'June',
            'July', 'August', 'September', 'October', 'November', 'December'];

/** "15.10.2026 — 10:49" (l'ora solo se c'è davvero). */
function lux_notice_when(array $n): string
{
    if (empty($n['date'])) return '';
    $ts = strtotime((string)$n['date']);
    if (!$ts) return '';
    $time = date('H:i', $ts);
    return date('d.m.Y', $ts) . ($time !== '00:00' ? ' — ' . $time : '');
}

/** L'etichetta del mese di un avviso: "Ottobre 2026". */
function lux_notice_month(array $n, bool $isIt, array $it, array $en): string
{
    $ts = !empty($n['date']) ? strtotime((string)$n['date']) : 0;
    if (!$ts) return $isIt ? 'Senza data' : 'No date';
    $names = $isIt ? $it : $en;
    return $names[(int)date('n', $ts)] . ' ' . date('Y', $ts);
}

// I gruppi si costruiscono sull'elenco, che è già dal più recente al più
// vecchio: i mesi escono in ordine, senza riordinarli.
$groups = [];
foreach ($list as $r) {
    $ts  = !empty($r['n']['date']) ? strtotime((string)$r['n']['date']) : 0;
    $key = $ts ? date('Y-m', $ts) : 'senza-data';
    if (!isset($groups[$key])) {
        $groups[$key] = ['label' => lux_notice_month($r['n'], $isIt, $monthIt, $monthEn), 'items' => []];
    }
    $groups[$key]['items'][] = $r;
}

$lastDate = '';
foreach ($notices as $n) {
    if (empty($n['date'])) continue;
    $ts = strtotime((string)$n['date']);
    if ($ts && strtotime($lastDate === '' ? '1970-01-01' : $lastDate) < $ts) $lastDate = (string)$n['date'];
}

$pageTitle       = $isIt ? 'Bacheca — Notizie e Avvisi | L.U.X. Verse' : 'Board — News & Notices | L.U.X. Verse';
$pageDescription = $isIt
    ? 'Tutte le notizie, gli avvisi e gli aggiornamenti dell\'ecosistema L.U.X. Verse: cosa è cambiato, cosa arriva e cosa non va, in italiano e inglese.'
    : 'All the news, notices and updates from the L.U.X. Verse ecosystem: what changed, what is coming and what is broken, in Italian and English.';
$pageKeywords    = 'bacheca, notizie, avvisi, aggiornamenti, L.U.X. Verse, news, notices, updates';
$pageOgType      = 'website';
$pageCSS         = '';

/**
 * Una scheda-avviso: tipo, data, titolo, testo per INTERO, canali e firma.
 * Niente estratti e niente tagli: la scheda mostra tutto quello che c'è.
 */
function lux_board_card(array $n, bool $isIt, string $anchor): void
{
    $meta    = noticeTypeMeta($n['type'] ?? 'info');
    $typeKey = $meta['class'] !== '' ? $meta['class'] : 'info';
    $title   = $isIt ? ($n['title_it'] ?? ($n['title'] ?? '')) : ($n['title_en'] ?? ($n['title'] ?? ''));
    $msg     = $isIt ? ($n['message_it'] ?? ($n['message'] ?? '')) : ($n['message_en'] ?? ($n['message'] ?? ''));
    $when    = lux_notice_when($n);
    $sig     = (string)($n['signature'] ?? '');
    $social  = function_exists('noticeSocialLinks') ? noticeSocialLinks($n) : [];
    $search  = $title . ' ' . preg_replace('/\s+/', ' ', (string)$msg);
    if (function_exists('mb_strtolower')) $search = mb_strtolower($search, 'UTF-8');
    else $search = strtolower($search);
    ?>
    <article class="b-card<?= !empty($n['pinned']) ? ' pinned' : '' ?>" id="<?= htmlspecialchars($anchor) ?>"
             data-ntype="<?= htmlspecialchars($typeKey) ?>" data-txt="<?= htmlspecialchars($search) ?>">
        <div class="b-top">
            <span class="b-tag <?= htmlspecialchars($meta['class']) ?>">
                <?php if (!empty($n['pinned'])): ?><?= icon('star', 10) ?><?php endif; ?>
                <?= htmlspecialchars(t($meta['label'])) ?>
            </span>
            <?php if ($when !== ''): ?>
            <a class="b-date b-when" href="#<?= htmlspecialchars($anchor) ?>"
               title="<?= $isIt ? 'Collegamento a questo avviso' : 'Link to this notice' ?>"><?= htmlspecialchars($when) ?></a>
            <?php endif; ?>
        </div>
        <h4 class="b-title"><?= htmlspecialchars($title) ?></h4>
        <div class="b-body"><?= lux_notice_body((string)$msg) ?></div>
        <?php /* La riga "segui ci" compare una volta sola, in fondo. */ ?>
        <?php if ($social): ?><?= noticeSocialBar($social, 'bottom', t('Follow us')) ?><?php endif; ?>
        <?php if ($sig !== ''): ?>
        <p class="b-sign">— <?= htmlspecialchars($sig) ?></p>
        <?php endif; ?>
    </article>
    <?php
}

lux_header();
lux_page_open([
    'crumbs'   => [['t' => 'Home', 'u' => '/index.php'], ['t' => t('Bacheca')]],
    'kicker'   => t('Bacheca L.U.X. Verse'),
    'title'    => $isIt ? 'Notizie e avvisi dell\'ecosistema' : 'News and notices from the ecosystem',
    'lead'     => $isIt
        ? 'In apertura l\'avviso più recente, per intero. Poi l\'indice di tutto quello che c\'è, e sotto ogni avviso per esteso, con la ricerca e i filtri per tipo: qui non si taglia niente, quello che è scritto si legge tutto.'
        : 'Opening with the most recent notice, in full. Then the index of everything on the board, and below every notice in full, with search and filters by type: nothing is cut here, what is written can be read in full.',
    'meta'     => array_values(array_filter([
        count($notices) . ' ' . ($isIt ? 'avvisi pubblicati' : 'published notices'),
        $lastDate !== '' ? ($isIt ? 'Ultimo aggiornamento: ' . date('d.m.Y', strtotime($lastDate)) : 'Last update: ' . date('d.m.Y', strtotime($lastDate))) : '',
        $isIt ? 'In italiano e inglese' : 'In Italian and English',
    ])),
    'actions'  => [
        ['t' => $isIt ? 'Percorso del rilascio' : 'Release roadmap', 'u' => '/roadmap.php', 'p' => true],
        ['t' => $isIt ? 'Linea del tempo' : 'Timeline', 'u' => '/linea-del-tempo.php'],
    ],
]);
?>
<div class="doc-grid flip">

    <div class="doc-main">

        <?php if ($opener): ?>
        <?php
        $om    = noticeTypeMeta($opener['type'] ?? 'info');
        $ot    = $isIt ? ($opener['title_it'] ?? ($opener['title'] ?? '')) : ($opener['title_en'] ?? ($opener['title'] ?? ''));
        $omg   = $isIt ? ($opener['message_it'] ?? ($opener['message'] ?? '')) : ($opener['message_en'] ?? ($opener['message'] ?? ''));
        $osig  = (string)($opener['signature'] ?? '');
        $osoc  = function_exists('noticeSocialLinks') ? noticeSocialLinks($opener) : [];
        ?>
        <section class="b-open" aria-labelledby="b-open-title">
            <div class="b-top">
                <span class="b-eyebrow"><?= icon('star', 11) ?> <?= $isIt ? 'In apertura' : 'Opening' ?></span>
                <span class="b-top-right">
                    <span class="b-tag <?= htmlspecialchars($om['class']) ?>"><?= htmlspecialchars(t($om['label'])) ?></span>
                    <?php if (lux_notice_when($opener) !== ''): ?>
                    <span class="b-date"><?= htmlspecialchars(lux_notice_when($opener)) ?></span>
                    <?php endif; ?>
                </span>
            </div>
            <h3 class="b-open-title" id="b-open-title"><?= htmlspecialchars($ot) ?></h3>
            <div class="b-body"><?= lux_notice_body((string)$omg) ?></div>
            <?php if ($osoc): ?><?= noticeSocialBar($osoc, 'bottom', t('Follow us')) ?><?php endif; ?>
            <?php if ($osig !== ''): ?>
            <p class="b-sign">— <?= htmlspecialchars($osig) ?></p>
            <?php endif; ?>
            <p class="b-open-note"><?= $isIt
                ? 'Questo è l\'avviso più recente fra quelli fissati. Gli altri, con questo, sono qui sotto.'
                : 'This is the most recent pinned notice. The others, including this one, are listed below.' ?></p>
        </section>
        <?php endif; ?>

        <?php if ($rows): ?>
        <h2 class="doc-sec-t"><span class="doc-num"><?= icon('board', 12) ?></span><span><?= $isIt ? 'L\'indice della bacheca' : 'Board index' ?></span></h2>
        <p class="b-hint"><?= $isIt
            ? 'Tutti gli avvisi in una riga ciascuno, dal più recente. Clicca un titolo per andare a leggerlo.'
            : 'Every notice in one line, newest first. Click a title to jump to it.' ?></p>
        <ol class="b-index">
            <?php foreach ($rows as $r):
                $n     = $r['n'];
                $m     = noticeTypeMeta($n['type'] ?? 'info');
                $t     = $isIt ? ($n['title_it'] ?? ($n['title'] ?? '')) : ($n['title_en'] ?? ($n['title'] ?? ''));
                $ts    = !empty($n['date']) ? strtotime((string)$n['date']) : 0;
                $short = $ts ? date('d.m', $ts) : '';
            ?>
            <li>
                <a class="b-index-row" href="#<?= htmlspecialchars($r['id']) ?>">
                    <span class="b-index-date"><?= htmlspecialchars($short) ?></span>
                    <span class="b-tag <?= htmlspecialchars($m['class']) ?>"><?= htmlspecialchars(t($m['label'])) ?></span>
                    <span class="b-index-title"><?= htmlspecialchars($t) ?></span>
                    <?php if ($r['open']): ?>
                    <span class="b-flag"><?= $isIt ? 'in apertura' : 'opening' ?></span>
                    <?php elseif (!empty($n['pinned'])): ?>
                    <span class="b-flag" title="<?= $isIt ? 'Fissato' : 'Pinned' ?>"><?= icon('star', 10) ?></span>
                    <?php endif; ?>
                    <i class="b-index-go" aria-hidden="true"><?= icon('arrow-right', 13) ?></i>
                </a>
            </li>
            <?php endforeach; ?>
        </ol>
        <?php endif; ?>

        <h2 class="doc-sec-t"><span class="doc-num"><?= count($list) ?></span><span><?= $isIt ? 'Tutti gli avvisi' : 'All notices' ?></span></h2>

        <?php if (!$notices): ?>
        <div class="b-empty">
            <?= icon('board', 34) ?>
            <p><?= t('Nessun avviso al momento.') ?></p>
            <a href="index.php#progetti" class="btn-small"><?= t('Explore Projects') ?> <?= icon('arrow-right', 14) ?></a>
        </div>
        <?php else: ?>

        <div class="b-tools">
            <label class="b-search">
                <?= icon('search', 16) ?>
                <input type="search" id="nt-search" placeholder="<?= $isIt ? 'Cerca in titoli e testi degli avvisi…' : 'Search titles and notice texts…' ?>" autocomplete="off">
            </label>
            <div class="filters" id="notice-filters">
                <button type="button" class="filter-btn active" data-ntype="all"><?= t('All') ?> &middot; <?= count($list) ?></button>
                <?php foreach ($presentTypes as $key => $info): ?>
                <button type="button" class="filter-btn" data-ntype="<?= htmlspecialchars($key) ?>">
                    <?= htmlspecialchars(t($info['label'])) ?> &middot; <?= (int)$info['count'] ?>
                </button>
                <?php endforeach; ?>
            </div>
            <button type="button" class="b-reset" id="nt-reset" hidden><?= $isIt ? 'Azzera' : 'Reset' ?></button>
        </div>
        <p class="b-count" id="nt-count" role="status" aria-live="polite"><?= count($list) . ' ' . ($isIt ? 'avvisi nell\'elenco' : 'notices listed') ?></p>

        <div class="b-list" id="notice-list">
            <?php foreach ($groups as $key => $g): ?>
            <section class="b-group" data-b-month="<?= htmlspecialchars($key) ?>">
                <h3 class="b-month"><?= htmlspecialchars($g['label']) ?></h3>
                <div class="b-cards">
                    <?php foreach ($g['items'] as $r) lux_board_card($r['n'], $isIt, $r['id']); ?>
                </div>
            </section>
            <?php endforeach; ?>
        </div>
        <p class="b-empty-inline" id="nt-none" hidden><?= $isIt ? 'Nessun avviso corrisponde a questa ricerca.' : 'No notice matches this search.' ?></p>

        <?php endif; ?>
    </div>

    <aside class="b-side">

        <div class="space-card">
            <h3><?= icon('book-open', 14) ?> <?= $isIt ? 'Come leggere la pagina' : 'How to read this page' ?></h3>
            <div class="space-list">
                <div class="space-row">
                    <span class="space-row-main">
                        <b><?= $isIt ? '1. In apertura' : '1. Opening' ?></b>
                        <span><?= $isIt ? 'L\'avviso più recente, tutto, senza tagli' : 'The most recent notice, in full' ?></span>
                    </span>
                </div>
                <div class="space-row">
                    <span class="space-row-main">
                        <b><?= $isIt ? '2. L\'indice' : '2. The index' ?></b>
                        <span><?= $isIt ? 'Tutti i titoli in fila: si salta dove serve' : 'All titles in a row: jump where you need' ?></span>
                    </span>
                </div>
                <div class="space-row">
                    <span class="space-row-main">
                        <b><?= $isIt ? '3. Tutti gli avvisi' : '3. All notices' ?></b>
                        <span><?= $isIt ? 'Per esteso, mese per mese, con ricerca e filtri' : 'In full, month by month, with search and filters' ?></span>
                    </span>
                </div>
            </div>
        </div>

        <?php if ($presentTypes): /* senza avvisi non c'e' nessun tipo: la scheda non si mostra vuota */ ?>
        <div class="space-card">
            <h3><?= icon('board', 14) ?> <?= $isIt ? 'Tipi di avviso' : 'Notice types' ?></h3>
            <div class="space-list">
                <?php foreach ($presentTypes as $key => $info): ?>
                <button type="button" class="space-row bt" data-ntype="<?= htmlspecialchars($key) ?>" aria-pressed="false">
                    <span class="space-row-main">
                        <b><?= htmlspecialchars(t($info['label'])) ?></b>
                        <span><?= (int)$info['count'] ?> <?= $isIt ? 'avvisi' : 'notices' ?> &middot; <?= $isIt ? 'mostrali' : 'show them' ?></span>
                    </span>
                    <i class="b-side-go" aria-hidden="true"><?= icon('arrow-right', 13) ?></i>
                </button>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>

        <div class="space-card">
            <h3><?= icon('arrow-right', 14) ?> <?= $isIt ? 'Collegamenti utili' : 'Useful links' ?></h3>
            <div class="space-list">
                <a class="space-row" href="/linea-del-tempo.php"><span class="space-row-main"><b><?= t('Linea del Tempo') ?></b><span><?= $isIt ? 'Le stesse tappe, una per una, in ordine di data' : 'The same milestones, one by one, in date order' ?></span></span></a>
                <a class="space-row" href="/roadmap.php"><span class="space-row-main"><b><?= $isIt ? 'Percorso del rilascio' : 'Release roadmap' ?></b><span><?= $isIt ? 'Cosa è fatto, cosa è in corso, cosa arriva' : 'What is done, in progress, coming' ?></span></span></a>
                <a class="space-row" href="/status.php"><span class="space-row-main"><b><?= t('Stato') ?></b><span><?= $isIt ? 'Come stanno i servizi, adesso' : 'How the services are doing, right now' ?></span></span></a>
                <a class="space-row" href="/assistenza.php"><span class="space-row-main"><b><?= t('Assistenza') ?></b><span><?= $isIt ? 'Qualcosa non funziona? Scrivici' : 'Something broken? Write to us' ?></span></span></a>
            </div>
        </div>

        <div class="space-card">
            <h3><?= icon('bell', 14) ?> <?= $isIt ? 'Restare aggiornati' : 'Stay updated' ?></h3>
            <p class="space-empty"><?= $isIt
                ? 'Il campanello in alto raccoglie le notifiche nuove: si accende un puntino quando c\'è qualcosa che non hai ancora letto. Non serve nessun account e non ti scriviamo altrove se non vuoi.'
                : 'The bell at the top collects new notifications: a dot lights up when there is something you have not read yet. No account needed, and we do not contact you elsewhere unless you want it.' ?></p>
            <div class="space-list">
                <a class="space-row" href="/app.php"><span class="space-row-main"><b><?= t('App') ?></b><span><?= $isIt ? 'Gli avvisi sul telefono, con l\'app ufficiale' : 'Notices on your phone, with the official app' ?></span></span></a>
            </div>
        </div>
    </aside>

</div>
<?php
lux_page_close([
    'next' => [
        ['t' => t('Linea del Tempo'), 'd' => $isIt ? 'La storia del progetto in ordine di data.' : 'The story of the project in date order.', 'u' => '/linea-del-tempo.php'],
        ['t' => $isIt ? 'Percorso del rilascio' : 'Release roadmap', 'd' => $isIt ? 'Cosa è già online e cosa arriva dopo, senza promesse gonfiate.' : 'What is already online and what comes next, without inflated promises.', 'u' => '/roadmap.php'],
        ['t' => $isIt ? 'Proponi uno strumento' : 'Suggest a tool', 'd' => $isIt ? 'Manca qualcosa? Dillo e vota le idee degli altri.' : 'Something missing? Say it and vote on other ideas.', 'u' => '/proponi.php'],
    ],
]);

if ($notices): ?>
<script>
(function () {
    var list = document.getElementById('notice-list');
    if (!list) return;

    var cards  = [].slice.call(list.querySelectorAll('.b-card'));
    var groups = [].slice.call(list.querySelectorAll('.b-group'));
    var none   = document.getElementById('nt-none');
    var count  = document.getElementById('nt-count');
    var reset  = document.getElementById('nt-reset');
    var search = document.getElementById('nt-search');
    var chips  = [].slice.call(document.querySelectorAll('[data-ntype]'));
    var type   = 'all';

    function apply() {
        var q = ((search || {}).value || '').trim().toLowerCase();
        var shown = 0;

        cards.forEach(function (c) {
            var okType = (type === 'all' || c.dataset.ntype === type);
            var okText = (q === '' || (c.dataset.txt || '').indexOf(q) !== -1);
            var ok = okType && okText;
            c.style.display = ok ? '' : 'none';
            if (ok) shown++;
        });

        // Un mese senza avvisi visibili non si mostra: resterebbe un titolo
        // solo, sopra il vuoto.
        groups.forEach(function (g) {
            var vis = g.querySelectorAll('.b-card:not([style*="display: none"])').length;
            g.style.display = vis ? '' : 'none';
        });

        if (none)  none.hidden = shown !== 0;
        if (count) count.textContent = shown + (shown === 1 ? ' avviso nell\'elenco' : ' avvisi nell\'elenco');
        if (reset) reset.hidden = (type === 'all' && q === '');
    }

    // Lo stesso tipo si accende sia fra i filtri in alto sia nell'elenco dei
    // tipi nella colonna a destra: sono la stessa scelta, mostrata in due posti.
    function sync() {
        chips.forEach(function (x) {
            var on = (x.dataset.ntype === type);
            x.classList.toggle('active', on);
            if (x.hasAttribute('aria-pressed')) x.setAttribute('aria-pressed', on ? 'true' : 'false');
        });
    }

    chips.forEach(function (b) {
        b.addEventListener('click', function () {
            type = b.dataset.ntype;
            sync();
            apply();
        });
    });

    if (search) search.addEventListener('input', apply);
    if (reset) reset.addEventListener('click', function () {
        if (search) search.value = '';
        type = 'all';
        sync();
        apply();
        if (search) search.focus();
    });

    // Il browser può riaprire la pagina con la ricerca ancora scritta: lo
    // stato dei filtri deve corrispondere a quello che si vede.
    if (search && search.value !== '') apply();
})();
</script>
<?php endif; ?>
<?php lux_footer(); ?>
