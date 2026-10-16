<?php
require_once __DIR__ . '/config.php';
lux_session_start();   // cookie HttpOnly + SameSite (vedi config.php)

$isAppView = true;
$isAdmin = isAdminLoggedIn();
$lang = currentLang();
$isIt = $lang === 'it';

// Numeri del catalogo. L'app mostra gli STESSI numeri del sito: prima
// contava solo le voci curate di data/projects.json (mille) e sembrava che
// l'ecosistema avesse centomila strumenti in meno di quelli che ha. Il
// totale, la divisione per categoria e l'elenco arrivano da qui.
$totalTools   = lux_catalog_total();
$catCounts    = lux_catalog_counts_all();
$fmtTools     = number_format($totalTools, 0, $isIt ? ',' : '.', $isIt ? '.' : ',');
$latestNotices = getNoticesFromLocal(2);

// Le sei voci dei filtri: la stessa lista per il server e per il browser.
$categories = [
    ''             => 'All',
    'creativita'   => 'Creativity',
    'produttivita' => 'Productivity',
    'educativi'    => 'Educational',
    'giochi'       => 'Games',
    'utility'      => 'Utilities',
];

$pageTitle       = $isIt ? 'L.U.X. Verse App — Strumenti AI' : 'L.U.X. Verse App — AI Tools';
$pageDescription = $isIt
    ? 'Tutti gli strumenti AI di L.U.X. Verse nella web app: creatività, produttività, educativi, giochi e utility. Gratis e senza registrazione.'
    : 'All the L.U.X. Verse AI tools in the web app: creativity, productivity, educational, games and utilities. Free, no registration.';
$pageKeywords    = 'L.U.X. Verse app, strumenti AI, workme, AI tools, free AI, no registration, intelligenza artificiale';
$pageCSS         = 'assets/css/index.css';
$pageOgType      = 'website';

lux_header();
?>
<main>

    <!-- ============ HERO APP ============ -->
    <section class="hero">
        <div class="container">
            <div class="hero-inner">
                <div class="hero-badge">
                    <span class="dot" aria-hidden="true"></span>
                    <span data-i18n="allOnline"><?= t('All systems online') ?></span>
                </div>
                <h1 class="hero-title">
                    <span class="hero-line">L.U.X.</span>
                    <span class="hero-line hero-sub">Verse</span>
                </h1>
                <p class="hero-lead" data-i18n="heroText"><?= t('L.U.X. Verse is an ecosystem of AI tools for your creativity, productivity and innovation. All free, no registration.') ?></p>
            </div>

            <div class="trust-badges">
                <div class="trust-badge">
                    <span class="trust-text">
                        <strong data-i18n="statTools"><?= $fmtTools ?></strong>
                        <span data-i18n="statToolsLabel"><?= t('Strumenti AI') ?></span>
                    </span>
                </div>
                <div class="trust-badge">
                    <span class="trust-text">
                        <strong data-i18n="noReg"><?= t('No Registration') ?></strong>
                        <span data-i18n="statNoRegLabel"><?= t('Nessun account da creare') ?></span>
                    </span>
                </div>
                <div class="trust-badge">
                    <span class="trust-text">
                        <strong data-i18n="noTracking"><?= t('No tracking') ?></strong>
                        <span data-i18n="statNoTrackingLabel"><?= t('Nessun cookie di tracciamento') ?></span>
                    </span>
                </div>
                <div class="trust-badge">
                    <span class="trust-text">
                        <strong data-i18n="noFee"><?= t('100% Free') ?></strong>
                        <span data-i18n="statNoProfitLabel"><?= t('Nessun ricavo, nessuna pubblicità') ?></span>
                    </span>
                </div>
            </div>
        </div>
    </section>

    <?php if (!empty($latestNotices)): ?>
    <!-- ============ BACHECA ============ -->
    <section class="main-section" id="bacheca" style="padding-top:52px;">
        <div class="container">
            <div class="sec-head">
                <div class="sec-head-main">
                    <span class="sec-index"><?= t('Bacheca') ?></span>
                    <h2 class="sec-title"><?= t('Notizie e avvisi') ?></h2>
                </div>
                <span class="sec-rule" aria-hidden="true"></span>
                <a href="bacheca.php" class="btn-small"><?= t('Vedi tutte le notizie') ?> <?= icon('arrow-right', 14) ?></a>
            </div>
            <div class="b-strip">
                <?php foreach ($latestNotices as $n):
                    $meta  = noticeTypeMeta($n['type'] ?? 'info');
                    $title = $isIt ? ($n['title_it'] ?? ($n['title'] ?? '')) : ($n['title_en'] ?? ($n['title'] ?? ''));
                    $msg   = $isIt ? ($n['message_it'] ?? ($n['message'] ?? '')) : ($n['message_en'] ?? ($n['message'] ?? ''));
                    $excerpt = trim(preg_replace('/\s+/', ' ', $msg));
                    $excerpt = function_exists('mb_substr') ? mb_substr($excerpt, 0, 140) : substr($excerpt, 0, 140);
                    $date  = !empty($n['date']) ? date('d.m.Y', strtotime($n['date'])) : '';
                ?>
                <article class="b-card<?= !empty($n['pinned']) ? ' pinned' : '' ?>">
                    <div class="b-top">
                        <span class="b-tag <?= $meta['class'] ?>">
                            <?php if (!empty($n['pinned'])): ?><?= icon('star', 10) ?><?php endif; ?>
                            <?= htmlspecialchars(t($meta['label'])) ?>
                        </span>
                        <span class="b-date"><?= htmlspecialchars($date) ?></span>
                    </div>
                    <h3 class="b-title"><?= htmlspecialchars($title) ?></h3>
                    <p class="b-body"><?= htmlspecialchars($excerpt) ?></p>
                </article>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
    <?php endif; ?>

    <!-- ============ ARCHIVIO STRUMENTI ============ -->
    <section class="projects-section" id="progetti" aria-label="Projects">
        <div class="container">
            <div class="sec-head">
                <div class="sec-head-main">
                    <span class="sec-index"><?= t('Archivio') ?></span>
                    <h2 class="sec-title" data-i18n="projectsTitle"><?= t('Our Projects') ?></h2>
                    <p class="sec-sub" data-i18n="projectsSub"><?= t('Explore all the tools of the L.U.X. Verse ecosystem') ?></p>
                </div>
                <span class="sec-rule" aria-hidden="true"></span>
            </div>

            <div class="toolbar">
                <div class="search-wrapper">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="11" cy="11" r="7.5"></circle><line x1="21" y1="21" x2="16.5" y2="16.5"></line></svg>
                    <input type="text" id="search-projects" class="search-input" data-i18n-ph="search" placeholder="<?= t('Search a project...') ?>" autocomplete="off" spellcheck="false" enterkeyhint="search">
                </div>
                <div class="filters" id="filters">
                    <?php foreach ($categories as $slug => $label): ?>
                        <button type="button" class="filter-btn<?= $slug === '' ? ' active' : '' ?>" data-filter="<?= htmlspecialchars($slug) ?>">
                            <?= htmlspecialchars(t($label)) ?><span class="filter-n"><?= isset($catCounts[$slug]) && $slug !== '' ? number_format($catCounts[$slug], 0, ',', '.') : $fmtTools ?></span>
                        </button>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="projects-count">
                <strong id="count-visible">0</strong> <span data-i18n="projectsFound"><?= t('projects found') ?></span>
                <span class="catalog-note" id="catalog-note"></span>
            </div>

            <div class="projects-grid" id="projects-grid"></div>

            <div class="catalog-more" id="catalog-more">
                <button type="button" class="btn-small" id="load-more">
                    <?= icon('plus', 14) ?>
                    <span data-i18n="loadMore"><?= t('Load more') ?></span>
                </button>
                <span class="catalog-progress" id="catalog-progress"></span>
                <div id="catalog-sentinel" aria-hidden="true"></div>
            </div>
        </div>
    </section>

</main>

<script>
const LANG = <?= json_encode($lang, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP) ?>;
const IS_IT = LANG === 'it';
const ICON_PATHS = <?= json_encode(lux_icon_paths(), JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP) ?>;

// === ICONE SVG (nessuna emoji, su qualsiasi dispositivo) ===
function svgIcon(key, size, cls, sw) {
    const d = ICON_PATHS[key] || ICON_PATHS.rocket;
    return '<svg class="' + (cls || 'ico') + '" width="' + (size || 20) + '" height="' + (size || 20) + '" viewBox="0 0 24 24" '
         + 'fill="none" stroke="currentColor" stroke-width="' + (sw || 1.5) + '" stroke-linecap="round" stroke-linejoin="round" '
         + 'aria-hidden="true" focusable="false">' + d + '</svg>';
}

const T = {
    allOnline     : <?= json_encode(t('All systems online')) ?>,
    heroText      : <?= json_encode(t('L.U.X. Verse is an ecosystem of AI tools for your creativity, productivity and innovation. All free, no registration.')) ?>,
    noReg         : <?= json_encode(t('No Registration')) ?>,
    noTracking    : <?= json_encode(t('No tracking')) ?>,
    noFee         : <?= json_encode(t('100% Free')) ?>,
    statToolsLabel: <?= json_encode(t('Strumenti AI')) ?>,
    statNoRegLabel: <?= json_encode(t('Nessun account da creare')) ?>,
    statNoTrackingLabel: <?= json_encode(t('Nessun cookie di tracciamento')) ?>,
    statNoProfitLabel: <?= json_encode(t('Nessun ricavo, nessuna pubblicità')) ?>,
    projectsTitle : <?= json_encode(t('Our Projects')) ?>,
    projectsSub   : <?= json_encode(t('Explore all the tools of the L.U.X. Verse ecosystem')) ?>,
    search        : <?= json_encode(t('Search a project...')) ?>,
    all           : <?= json_encode(t('All')) ?>,
    creativity    : <?= json_encode(t('Creativity')) ?>,
    productivity  : <?= json_encode(t('Productivity')) ?>,
    educational   : <?= json_encode(t('Educational')) ?>,
    games         : <?= json_encode(t('Games')) ?>,
    utilities     : <?= json_encode(t('Utilities')) ?>,
    projectsFound : <?= json_encode(t('projects found')) ?>,
    // Chiavi = stato reale di data/states.json (non l'etichetta tradotta).
    status: <?= json_encode([
        'attivo'       => t('Active'),
        'manutenzione' => t('Maintenance'),
        'pausa'        => t('On Pause'),
        'costruzione'  => t('Under Construction'),
        'verifica'     => t('Under Review'),
        'bloccato'     => t('Blocked'),
        'archiviato'   => t('Archived'),
        'disattivato'  => t('Off'),
    ]) ?>,
    noResults : <?= json_encode(t('No projects found')) ?>,
    loadError : <?= json_encode(t('Error loading projects. Try again later.')) ?>,
    loadMore  : <?= json_encode(t('Load more')) ?>,
    endOfCatalog: <?= json_encode(t('End of the catalog.')) ?>,
    cats: <?= json_encode([
        'creativita'   => t('Creativity'),
        'produttivita' => t('Productivity'),
        'educativi'    => t('Educational'),
        'giochi'       => t('Games'),
        'utility'      => t('Utilities'),
    ]) ?>,
    open      : <?= json_encode(t('Open')) ?>
};

// Totale del catalogo e quanti strumenti si chiedono per volta: gli stessi
// numeri che usa il sito, così l'app e il sito non possono divergere.
const TOTAL_TOOLS = <?= (int)$totalTools ?>;
const PER_PAGE = 24;

function applyT() {
    document.querySelectorAll('[data-i18n]').forEach(el => {
        const key = el.dataset.i18n;
        if (T[key] !== undefined) el.textContent = T[key];
    });
    document.querySelectorAll('[data-i18n-ph]').forEach(el => {
        const key = el.dataset.i18nPh;
        if (T[key] !== undefined) el.placeholder = T[key];
    });
    document.querySelectorAll('[data-i18n-txt]').forEach(el => {
        const key = el.dataset.i18nTxt;
        if (T[key] !== undefined) el.textContent = T[key];
    });
}

const statusClasses = {
    'attivo': 'active',
    'manutenzione': 'manutenzione',
    'pausa': 'pausa',
    'costruzione': 'costruzione',
    'verifica': 'verifica',
    'bloccato': 'bloccato',
    'archiviato': 'archiviato'
};

// ============================================================
// CATALOGO — IL SITO INTERO, NON SOLO LE VOCI CURATE
// ============================================================
// Prima l'app scaricava data/projects.json (mille voci) e i centomila
// strumenti generati non comparivano da nessuna parte: chi apriva l'app
// leggeva "1.000" mentre il sito diceva "100.000". Adesso si interroga lo
// stesso endpoint del sito (/api/catalog.php), una pagina per volta: le
// voci sono tante, ma la pagina resta leggera anche sul telefono.
let state = { page: 0, cat: '', q: '', total: 0, shown: 0, loading: false, done: false };
let searchTimeout = null;

function projectDesc(p) {
    return IS_IT
        ? (p.description_it || p.description || '')
        : (p.description_en || p.description || '');
}

function cardHTML(project) {
    const status = project.status || 'attivo';
    const statusClass = statusClasses[status] || 'active';
    const statusText = T.status[status] || T.status['attivo'];
    const color = project.color || '#D8A93C';
    const catLabel = project.category && T.cats[project.category] ? T.cats[project.category] : '';
    return '<a class="project-card" href="' + project.url + '" style="--pc:' + color + '">'
        + '<span class="p-icon" aria-hidden="true">' + svgIcon(project.icon, 20, 'p-ico', 1.5) + '</span>'
        + '<h4>' + escapeHtml(project.name) + '</h4>'
        + '<p>' + escapeHtml(projectDesc(project)) + '</p>'
        + '<span class="p-footer">'
        +   '<span class="p-status ' + statusClass + '">' + escapeHtml(statusText) + '</span>'
        +   (catLabel ? '<span class="p-tag">' + escapeHtml(catLabel) + '</span>' : '')
        +   '<span class="p-link">' + escapeHtml(T.open) + svgIcon('arrow-right', 13, 'ico', 2) + '</span>'
        + '</span>'
        + '</a>';
}

function updateProgress() {
    const counter = document.getElementById('count-visible');
    const progress = document.getElementById('catalog-progress');
    const note = document.getElementById('catalog-note');
    const fmt = n => Number(n || 0).toLocaleString(IS_IT ? 'it-IT' : 'en-US');
    if (counter) counter.textContent = fmt(state.total);
    if (progress) {
        progress.textContent = state.done
            ? T.endOfCatalog
            : (fmt(state.shown) + ' / ' + fmt(state.total));
    }
    if (note) note.textContent = state.q !== '' ? ('· "' + state.q + '"') : '';
    const more = document.getElementById('catalog-more');
    if (more) more.hidden = state.done;
}

function showSkeletons(n) {
    const grid = document.getElementById('projects-grid');
    let html = '';
    for (let i = 0; i < n; i++) html += '<div class="project-card skeleton" aria-hidden="true"><span class="sk-line w40"></span><span class="sk-line w80"></span><span class="sk-line w60"></span></div>';
    grid.innerHTML = html;
}

async function loadPage(reset) {
    if (state.loading) return;
    state.loading = true;

    const grid = document.getElementById('projects-grid');
    const btn = document.getElementById('load-more');
    if (reset) {
        state.page = 0; state.shown = 0; state.done = false;
        showSkeletons(6);
    } else if (btn) {
        btn.classList.add('loading');
    }

    const next = state.page + 1;
    const url = '/api/catalog.php?page=' + next + '&per=' + PER_PAGE
              + '&cat=' + encodeURIComponent(state.cat) + '&q=' + encodeURIComponent(state.q);

    try {
        const res  = await fetch(url, { cache: 'no-store' });
        const data = await res.json();
        if (!data.success) throw new Error('api');

        state.page  = next;
        state.total = data.total;
        state.done  = (data.items || []).length === 0 || state.page >= data.pages;

        if (reset) grid.innerHTML = '';
        grid.insertAdjacentHTML('beforeend', (data.items || []).map(cardHTML).join(''));
        state.shown += (data.items || []).length;

        if (state.total === 0) {
            grid.innerHTML = '<div class="empty-projects"><p>' + escapeHtml(T.noResults) + '</p></div>';
            state.done = true;
        }
        updateProgress();
    } catch (e) {
        if (reset) grid.innerHTML = '<div class="empty-projects"><p>' + escapeHtml(T.loadError) + '</p></div>';
    } finally {
        state.loading = false;
        if (btn) btn.classList.remove('loading');
    }
}

document.querySelectorAll('.filter-btn').forEach(btn => {
    btn.addEventListener('click', function () {
        document.querySelectorAll('.filter-btn').forEach(b => b.classList.remove('active'));
        this.classList.add('active');
        state.cat = this.dataset.filter || '';
        loadPage(true);
    });
});

// Ricerca per parole chiave, risolta dal server sul catalogo intero (nome,
// descrizione, categoria): scrivendo si cercato fra tutti i 100.000, non
// soltanto fra quelli già caricati a schermo.
const searchInput = document.getElementById('search-projects');
if (searchInput) {
    searchInput.addEventListener('input', function (e) {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(() => {
            state.q = (e.target.value || '').trim();
            loadPage(true);
            // La tastiera del telefono si chiude da sola: resta in vista
            // l'elenco dei risultati, non il campo in scrittura.
            if (document.activeElement === e.target && e.target.blur) e.target.blur();
        }, 220);
    });
    searchInput.addEventListener('keydown', function (e) {
        if (e.key === 'Enter') { e.preventDefault(); e.target.blur(); }
    });
}

const loadMoreBtn = document.getElementById('load-more');
if (loadMoreBtn) loadMoreBtn.addEventListener('click', () => loadPage(false));

// Scorrimento infinito: quando la sentinella entra nello schermo si carica la
// pagina successiva. Il pulsante resta come ripiego.
if ('IntersectionObserver' in window) {
    const sentinel = document.getElementById('catalog-sentinel');
    if (sentinel) {
        new IntersectionObserver(entries => {
            entries.forEach(en => {
                if (en.isIntersecting && !state.done && !state.loading && state.page > 0) loadPage(false);
            });
        }, { rootMargin: '600px 0px' }).observe(sentinel);
    }
}

document.addEventListener('DOMContentLoaded', () => {
    applyT();
    // I numeri del catalogo arrivano dal server con la pagina: se il sito ne
    // pubblica altri, l'app li mostra senza aspettare un aggiornamento dell'APK.
    loadPage(true);
});
</script>

<?php lux_footer(); ?>
