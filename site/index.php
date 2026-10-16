<?php
require_once __DIR__ . '/config.php';
lux_session_start();   // cookie HttpOnly + SameSite (vedi config.php)

$isAdmin = isAdminLoggedIn();
$lang = currentLang();
$isIt = $lang === 'it';

// Il catalogo non si scarica piu' tutto: gli strumenti sono 100.000 e la
// pagina chiede 24 voci alla volta a /api/catalog.php (ricerca e filtri
// risolti dal server). Qui servono solo i numeri.
$totalTools   = lux_catalog_total();
$generatedN   = lux_catalog_generated_count();
$catCounts    = lux_catalog_counts_all();
$fmtTools     = number_format($totalTools, 0, $isIt ? ',' : '.', $isIt ? '.' : ',');

$latestNotices = getNoticesFromLocal(3);

$pageTitle       = $isIt ? 'L.U.X. Verse — 100.000 strumenti AI gratuiti' : 'L.U.X. Verse — 100,000 free AI tools';
// 39.1.1: il claim sulla privacy dice quello che il sito fa davvero. I cookie
// tecnici (sessione e lingua) esistono e servono a far funzionare le cose;
// quello che non esiste e' il tracciamento. Dirlo per esteso e' piu' onesto
// di un "nessun cookie" che non regge al primo controllo.
$pageDescription = $isIt
    ? 'L.U.X. Verse è un ecosistema di 100.000 strumenti AI gratuiti per creatività, produttività e innovazione. Nessuna registrazione, nessun cookie di tracciamento, nessun costo.'
    : 'L.U.X. Verse is an ecosystem of 100,000 free AI tools for creativity, productivity and innovation. No registration, no tracking cookies, no cost.';
$pageKeywords    = 'AI tools, L.U.X., L.U.X. Verse, artificial intelligence, free AI tools, no registration, no profit, productivity, creativity, educational tools, games, utilities, online AI ecosystem, strumenti AI gratuiti, intelligenza artificiale';
$pageCSS         = 'assets/css/index.css';
$pageOgType      = 'website';

// Le sei voci dei filtri: la stessa lista per server e client, cosi' le
// etichette non si scollano dal contenuto.
$categories = [
    ''             => 'All',
    'creativita'   => 'Creativity',
    'produttivita' => 'Productivity',
    'educativi'    => 'Educational',
    'giochi'       => 'Games',
    'utility'      => 'Utilities',
];

lux_header();
?>
<main>

    <!-- ============ HERO ============ -->
    <section class="hero">
        <div class="container">
            <div class="hero-inner">
                <div class="hero-badge">
                    <span class="dot" aria-hidden="true"></span>
                    <span data-i18n="allOnline"><?= t('All systems online') ?></span>
                </div>

                <h1 class="hero-title">
                    <span class="hero-line"><?= t('{n} AI tools.', ['n' => $fmtTools]) ?></span>
                    <span class="hero-line hero-dim"><?= t('Un unico ecosistema.') ?></span>
                    <span class="hero-line hero-sub"><?= t('Zero compromessi.') ?></span>
                </h1>

                <p class="hero-lead" data-i18n="heroText"><?= t('L.U.X. Verse is an ecosystem of AI tools for your creativity, productivity and innovation. All free, no registration.') ?></p>

                <div class="hero-buttons">
                    <a href="#progetti" class="btn-neon">
                        <?= icon('grid', 17) ?>
                        <span data-i18n="exploreProjects"><?= t('Explore Projects') ?></span>
                    </a>
                    <a href="linea-del-tempo.php" class="btn-secondary">
                        <?= icon('time', 17) ?>
                        <span data-i18n="learnMore"><?= t('Learn more') ?></span>
                    </a>
                </div>
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
                        <strong data-i18n="noProfit"><?= t('100% No Profit') ?></strong>
                        <span data-i18n="statNoProfitLabel"><?= t('Nessun ricavo, nessuna pubblicità') ?></span>
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
            </div>
        </div>
    </section>

    <?php if (!empty($latestNotices)): ?>
    <!-- ============ CAPITOLO 01 · BACHECA ============ -->
    <!-- La home parla la stessa lingua delle altre pagine: capitolo
         numerato, dati veri in testa, azione principale a destra. -->
    <?php
    $homeNoticesTotal = count(getNoticesFromLocal(0));
    $homeLastNotice   = '';
    foreach (getNoticesFromLocal(0) as $hn) {
        $hd = (string)($hn['date'] ?? '');
        if ($hd !== '' && strtotime($hd) > strtotime($homeLastNotice === '' ? '1970-01-01' : $homeLastNotice)) $homeLastNotice = $hd;
    }
    ?>
    <section class="main-section doc-home" id="bacheca">
        <div class="container">
            <div class="doc-head">
                <span class="sec-index"><?= icon('board', 13) ?> <?= $isIt ? 'Capitolo 01 · Bacheca' : 'Chapter 01 · Board' ?></span>
                <h2 class="sec-title doc-title"><?= t('Notizie e avvisi') ?></h2>
                <p class="sec-sub doc-lead"><?= t('All the latest news about the L.U.X. Verse ecosystem.') ?></p>
                <div class="doc-bar">
                    <ul class="doc-meta">
                        <li><?= $homeNoticesTotal ?> <?= $isIt ? 'avvisi pubblicati' : 'published notices' ?></li>
                        <?php if ($homeLastNotice !== ''): ?>
                        <li><?= $isIt ? 'Ultimo: ' : 'Latest: ' ?><?= htmlspecialchars(date('d.m.Y', strtotime($homeLastNotice))) ?></li>
                        <?php endif; ?>
                    </ul>
                    <div class="doc-actions">
                        <a href="bacheca.php" class="btn-small primary">
                            <?= t('Vedi tutte le notizie') ?>
                            <?= icon('arrow-right', 14) ?>
                        </a>
                    </div>
                </div>
            </div>

            <div class="b-strip">
                <?php foreach ($latestNotices as $n):
                    $meta  = noticeTypeMeta($n['type'] ?? 'info');
                    $title = $isIt ? ($n['title_it'] ?? ($n['title'] ?? '')) : ($n['title_en'] ?? ($n['title'] ?? ''));
                    $msg   = $isIt ? ($n['message_it'] ?? ($n['message'] ?? '')) : ($n['message_en'] ?? ($n['message'] ?? ''));
                    $excerpt = trim(preg_replace('/\s+/', ' ', $msg));
                    if (function_exists('mb_strlen') && function_exists('mb_substr')) {
                        $isLong  = mb_strlen($excerpt) > 170;
                        $excerpt = mb_substr($excerpt, 0, 170);
                    } else {
                        $isLong  = strlen($excerpt) > 170;
                        $excerpt = substr($excerpt, 0, 170);
                    }
                    $date  = !empty($n['date']) ? date('d.m.Y', strtotime($n['date'])) : '';
                    // Avviso social: le icone vanno sia sopra sia sotto il testo.
                    $social = function_exists('noticeSocialLinks') ? noticeSocialLinks($n) : [];
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
                    <p class="b-body"><?= htmlspecialchars($excerpt) ?><?= $isLong ? '…' : '' ?></p>
                    <?php /* Una volta sola, come in bacheca: prima la riga
                             compariva sia sopra sia sotto il testo. */ ?>
                    <?php if ($social): ?><?= noticeSocialBar($social, 'bottom', t('Follow us')) ?><?php endif; ?>
                </article>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
    <?php endif; ?>

    <!-- ============ CAPITOLO 02 · ARCHIVIO STRUMENTI ============ -->
    <section class="projects-section doc-home" id="progetti" aria-label="Projects">
        <div class="container">
            <div class="doc-head">
                <span class="sec-index"><?= icon('grid', 13) ?> <?= $isIt ? 'Capitolo 02 · Catalogo' : 'Chapter 02 · Catalogue' ?></span>
                <h2 class="sec-title doc-title" data-i18n="projectsTitle"><?= t('Our Projects') ?></h2>
                <p class="sec-sub doc-lead" data-i18n="projectsSub"><?= t('Explore all the tools of the L.U.X. Verse ecosystem') ?></p>
                <div class="doc-bar">
                    <ul class="doc-meta">
                        <li><?= $fmtTools ?> <?= $isIt ? 'strumenti nel catalogo' : 'tools in the catalogue' ?></li>
                        <li><?= $isIt ? 'Motore: IA (Veloce) e Catalogo (Manuale)' : 'Engine: AI (Fast) and Catalogue (Manual)' ?></li>
                        <li><?= $isIt ? 'Nessun costo, nessuna registrazione' : 'No cost, no registration' ?></li>
                    </ul>
                </div>
            </div>

            <div class="toolbar">
                <!-- 39.1.1: la stessa barra, due modi di lavorare. IA (Veloce)
                     esegue gli strumenti e porta il risultato finito; Catalogo
                     (Manuale) e' la ricerca di sempre, per scegliere a mano. -->
                <div class="agent-modes" id="agent-modes" role="tablist" aria-label="<?= t('How do you want to work?') ?>">
                    <button type="button" class="agent-mode active" data-mode="ai" role="tab" aria-selected="true">
                        <?= icon('sparkles', 14) ?>
                        <span><?= t('AI (Fast)') ?></span>
                    </button>
                    <button type="button" class="agent-mode" data-mode="catalog" role="tab" aria-selected="false">
                        <?= icon('grid', 14) ?>
                        <span><?= t('Catalogue (Manual)') ?></span>
                    </button>
                    <span class="agent-modes-note" id="agent-mode-note"><?= t('Write what you need: L.U.X. runs the tools and brings you the finished result.') ?></span>
                </div>
                <div class="intent-box">
                    <?= icon('search', 18) ?>
                    <input type="text" id="search-projects" class="search-input" placeholder="<?= t('Describe what you need and I will find the right project...') ?>" autocomplete="off" spellcheck="false">
                    <button type="button" class="intent-go" id="intent-go">
                        <?= icon('sparkles', 15) ?>
                        <span id="intent-go-label"><?= t('Run it now') ?></span>
                    </button>
                </div>
                <div class="filters" id="filters">
                    <?php foreach ($categories as $slug => $label): ?>
                        <button type="button" class="filter-btn<?= $slug === '' ? ' active' : '' ?>" data-filter="<?= htmlspecialchars($slug) ?>">
                            <?= htmlspecialchars(t($label)) ?><span class="filter-n"><?= isset($catCounts[$slug]) && $slug !== '' ? number_format($catCounts[$slug], 0, ',', '.') : $fmtTools ?></span>
                        </button>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- 39.1.1: il risultato dell'agente nasce qui, sotto la barra:
                 nessuna pagina da aprire, nessun giro a vuoto. -->
            <div class="agent-result" id="agent-result" hidden aria-live="polite"></div>

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

    <!-- ============ CAPITOLO 03 · DA DOVE CONTINUARE ============ -->
    <!-- Ogni pagina del sito, in un posto solo: prima le cose da usare
         (app, motore, catalogo), poi quelle da leggere, poi quelle legali.
         Chi arriva qui trova tutto senza cercare nel menu. -->
    <section class="main-section doc-home" id="continua">
        <div class="container">
            <div class="doc-head">
                <span class="sec-index"><?= icon('arrow-right', 13) ?> <?= $isIt ? 'Capitolo 03 · Da dove continuare' : 'Chapter 03 · Where to continue' ?></span>
                <h2 class="sec-title doc-title"><?= $isIt ? 'Tutto il resto, in un posto solo' : 'Everything else, in one place' ?></h2>
                <p class="sec-sub doc-lead"><?= $isIt
                    ? 'Le pagine del sito, una per una: quelle da usare, quelle da leggere e quelle legali. Se cerchi una cosa e non sai dove sta, è qui.'
                    : 'The pages of the site, one by one: the ones to use, the ones to read and the legal ones. If you are looking for something and do not know where it is, it is here.' ?></p>
                <div class="doc-bar">
                    <ul class="doc-meta">
                        <li><?= $isIt ? '14 pagine collegate' : '14 pages linked' ?></li>
                        <li><?= $isIt ? 'L\'app si scarica dalla sua pagina' : 'The app is downloaded from its page' ?></li>
                        <li>Ctrl+K</li>
                    </ul>
                    <div class="doc-actions">
                        <a class="btn-small primary" href="app.php"><?= icon('smartphone', 14) ?> <?= $isIt ? 'Scarica l\'app' : 'Download the app' ?></a>
                        <a class="btn-small" href="linea-del-tempo.php"><?= $isIt ? 'Linea del tempo' : 'Timeline' ?></a>
                    </div>
                </div>
            </div>

            <?php
            $homeLinks = [
                ['/app.php', $isIt ? 'L\'app per Android' : 'The Android app', $isIt ? 'Come si scarica l\'APK, quanto pesa, cosa fare se Google chiede una verifica.' : 'How to download the APK, how big it is, what to do if Google asks for a verification.'],
                ['/roadmap.php', $isIt ? 'Percorso del rilascio' : 'Release roadmap', $isIt ? 'Cosa è fatto, cosa è in corso e cosa è annunciato, con lo stato vero di ogni voce.' : 'What is done, in progress and announced, with the real state of every item.'],
                ['/linea-del-tempo.php', $isIt ? 'Linea del tempo' : 'Timeline', $isIt ? 'La storia in ordine di data, più quello che stiamo costruendo adesso.' : 'The history in date order, plus what we are building right now.'],
                ['/bacheca.php', $isIt ? 'Bacheca' : 'Board', $isIt ? 'Gli avvisi per esteso, con ricerca e filtri per tipo.' : 'The notices in full, with search and filters by type.'],
                ['/status.php', $isIt ? 'Stato dei servizi' : 'Service status', $isIt ? 'Come stanno i servizi adesso, misurato davvero.' : 'How the services are doing right now, really measured.'],
                ['/spazio.php', $isIt ? 'Il mio spazio' : 'My space', $isIt ? 'Cronologia, preferiti e risultati, con il tuo codice personale.' : 'History, favourites and results, with your personal code.'],
                ['/proponi.php', $isIt ? 'Proponi uno strumento' : 'Suggest a tool', $isIt ? 'Manca qualcosa? Dillo e vota le idee degli altri.' : 'Something missing? Say it and vote on other ideas.'],
                ['/sviluppatori.php', $isIt ? 'Sviluppatori e API' : 'Developers and API', $isIt ? 'Chiavi anonime, esempi e documentazione per usare gli strumenti altrove.' : 'Anonymous keys, examples and docs to use the tools elsewhere.'],
                ['/chi-siamo.php', $isIt ? 'Chi siamo' : 'About us', $isIt ? 'Il progetto, la storia e chi lo manda avanti.' : 'The project, the story and who keeps it going.'],
                ['/faq.php', $isIt ? 'Domande frequenti' : 'FAQ', $isIt ? 'Le risposte alle domande più comuni, senza giri di parole.' : 'Answers to the most common questions, without talking around them.'],
                ['/assistenza.php', $isIt ? 'Assistenza' : 'Support', $isIt ? 'Qualcosa non funziona? Scrivilo qui, con un codice per seguirlo.' : 'Something broken? Write it here, with a code to follow it.'],
                ['/contattaci.php', $isIt ? 'Contatti' : 'Contact', $isIt ? 'Una domanda, una collaborazione, una segnalazione.' : 'A question, a collaboration, a report.'],
                ['/privacy.php', $isIt ? 'Privacy Policy' : 'Privacy Policy', $isIt ? 'Cosa trattiamo e cosa no, cookie compresi, con i nomi veri.' : 'What we process and what we do not, cookies included, by their real names.'],
                ['/terms.php', $isIt ? 'Termini di Servizio' : 'Terms of Service', $isIt ? 'Le regole d\'uso, i limiti e cosa puoi aspettarti.' : 'The rules of use, the limits and what you can expect.'],
            ];
            ?>
            <nav class="doc-next" aria-label="<?= t('Continue from here') ?>">
                <span class="doc-next-title"><?= icon('arrow-right', 13) ?> <?= t('Continue from here') ?></span>
                <div class="doc-next-grid">
                    <?php foreach ($homeLinks as $hl): ?>
                    <a class="doc-next-card" href="<?= htmlspecialchars($hl[0]) ?>">
                        <b><?= htmlspecialchars($hl[1]) ?></b>
                        <span><?= htmlspecialchars($hl[2]) ?></span>
                        <i aria-hidden="true"><?= icon('arrow-right', 14) ?></i>
                    </a>
                    <?php endforeach; ?>
                </div>
            </nav>
        </div>
    </section>

</main>

<script>
const LANG = <?= json_encode($lang, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP) ?>;
const IS_IT = LANG === 'it';
const ICON_PATHS = <?= json_encode(lux_icon_paths(), JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP) ?>;
const TOTAL_TOOLS = <?= (int)$totalTools ?>;
const PER_PAGE = 24;

// === ICONE SVG (nessuna emoji) ===
function svgIcon(key, size, cls, sw) {
    const d = ICON_PATHS[key] || ICON_PATHS.rocket;
    return '<svg class="' + (cls || 'ico') + '" width="' + (size || 20) + '" height="' + (size || 20) + '" viewBox="0 0 24 24" '
         + 'fill="none" stroke="currentColor" stroke-width="' + (sw || 1.5) + '" stroke-linecap="round" stroke-linejoin="round" '
         + 'aria-hidden="true" focusable="false">' + d + '</svg>';
}

// Le etichette dei filtri arrivano dal server: qui stanno solo le traduzioni
// usate dal resto della pagina.
const T = {
    projectsFound  : <?= json_encode(t('projects found')) ?>,
    loadMore       : <?= json_encode(t('Load more')) ?>,
    noResults      : <?= json_encode(t('No projects found')) ?>,
    loadError      : <?= json_encode(t('Error loading projects. Try again later.')) ?>,
    open           : <?= json_encode(t('Open')) ?>,
    endOfCatalog   : <?= json_encode(t('End of the catalog.')) ?>,
    cats: <?= json_encode([
        'creativita'   => t('Creativity'),
        'produttivita' => t('Productivity'),
        'educativi'    => t('Educational'),
        'giochi'       => t('Games'),
        'utility'      => t('Utilities'),
    ]) ?>,
    status: <?= json_encode([
        'attivo'       => t('Active'),
        'manutenzione' => t('Maintenance'),
        'pausa'        => t('On Pause'),
        'costruzione'  => t('Under Construction'),
        'verifica'     => t('Under Review'),
        'bloccato'     => t('Blocked'),
        'archiviato'   => t('Archived'),
        'disattivato'  => t('Off'),
    ]) ?>
};

const statusClasses = {
    'attivo': 'active',
    'manutenzione': 'manutenzione',
    'pausa': 'pausa',
    'costruzione': 'costruzione',
    'verifica': 'verifica',
    'bloccato': 'bloccato',
    'archiviato': 'archiviato',
    'disattivato': 'disattivato'
};

let state = { page: 0, cat: '', q: '', total: 0, shown: 0, loading: false, done: false, intent: false };
let searchTimeout = null;

function projectDesc(p) {
    return IS_IT ? (p.description_it || p.description || '') : (p.description_en || p.description || '');
}

function cardHTML(project) {
    const status = project.status || 'attivo';
    const statusClass = statusClasses[status] || 'active';
    const statusText = (T.status[status] || T.status['attivo']);
    const color = project.color || '#D8A93C';
    // Nessuna etichetta "curato" o "generato": il catalogo e' uno solo, tutti
    // gli strumenti sono sullo stesso piano.
    const tag = '<span class="p-tag">' + esc(project.category ? (T.cats && T.cats[project.category] ? T.cats[project.category] : project.category) : '') + '</span>';
    return '<a class="project-card" href="' + project.url + '" style="--pc:' + color + '">'
        + '<span class="p-icon" aria-hidden="true">' + svgIcon(project.icon, 20, 'p-ico', 1.5) + '</span>'
        + '<h4>' + esc(project.name) + '</h4>'
        + '<p>' + esc(projectDesc(project)) + '</p>'
        + '<span class="p-footer">'
        +   '<span class="p-status ' + statusClass + '">' + esc(statusText) + '</span>'
        +   tag
        +   '<span class="p-link">' + esc(T.open) + svgIcon('arrow-right', 13, 'ico', 2) + '</span>'
        + '</span>'
        + '</a>';
}

function esc(str) {
    return String(str == null ? '' : str).replace(/[&<>"']/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));
}

function updateProgress() {
    const progress = document.getElementById('catalog-progress');
    const note = document.getElementById('catalog-note');
    const counter = document.getElementById('count-visible');
    if (counter) counter.textContent = state.total.toLocaleString(IS_IT ? 'it-IT' : 'en-US');
    if (progress) {
        progress.textContent = state.done
            ? T.endOfCatalog
            : (state.shown.toLocaleString(IS_IT ? 'it-IT' : 'en-US') + ' / ' + state.total.toLocaleString(IS_IT ? 'it-IT' : 'en-US'));
    }
    if (note) note.textContent = state.q !== '' ? ('· "' + state.q + '"') : '';
    document.getElementById('catalog-more').hidden = state.done;
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
        state.page = 0; state.shown = 0; state.done = false; state.intent = false;
        showSkeletons(6);
    } else {
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
            grid.innerHTML = '<div class="empty-projects"><p>' + esc(T.noResults) + '</p></div>';
            state.done = true;
        }
        updateProgress();
    } catch (e) {
        if (reset) grid.innerHTML = '<div class="empty-projects"><p>' + esc(T.loadError) + '</p></div>';
    } finally {
        state.loading = false;
        btn.classList.remove('loading');
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

// La barra non e' piu' una ricerca per nome: l'utente scrive la sua
// esigenza e il sistema (API 4) propone i progetti piu' adatti. La ricerca
// per parole chiave resta come ripiego dentro l'endpoint.
async function intentSearch() {
    const input = document.getElementById('search-projects');
    const q = (input && input.value || '').trim();
    const grid = document.getElementById('projects-grid');

    if (q === '') { state.intent = false; state.q = ''; loadPage(true); return; }

    state.intent = true;
    state.done = true;
    showSkeletons(4);
    const btn = document.getElementById('intent-go');
    if (btn) btn.classList.add('loading');

    try {
        const res = await fetch('/api/catalog_intent.php', {
            method: 'POST', headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ q: q })
        });
        const data = await res.json();
        if (!data.success) throw new Error('api');
        materializeIntent(data);
    } catch (e) {
        grid.innerHTML = '<div class="empty-projects"><p>' + esc(T.loadError) + '</p></div>';
    } finally {
        if (btn) btn.classList.remove('loading');
    }
}

function materializeIntent(data) {
    const grid = document.getElementById('projects-grid');
    const note = document.getElementById('catalog-note');
    const counter = document.getElementById('count-visible');
    const items = data.items || [];
    const more = document.getElementById('catalog-more');
    if (more) more.hidden = true;
    if (counter) counter.textContent = items.length.toLocaleString(IS_IT ? 'it-IT' : 'en-US');
    if (note) note.textContent = data.why ? ('· ' + data.why) : ('· "' + (data.query || '') + '"');
    if (!items.length) {
        grid.innerHTML = '<div class="empty-projects"><p>' + esc(T.noResults) + '</p></div>';
        return;
    }
    grid.innerHTML = items.map(cardHTML).join('');
}

const searchInput = document.getElementById('search-projects');
const intentBtn   = document.getElementById('intent-go');
// Il clic e l'Invio non sono piu' legati qui: li prende in carico agentSubmit
// (in fondo), che sceglie fra i due modi della barra. Restano qui solo i
// comportamenti del campo di scrittura.
if (searchInput) {
    searchInput.addEventListener('input', function (e) {
        if ((e.target.value || '').trim() === '' && state.intent) { state.intent = false; loadPage(true); }
    });
}

document.getElementById('load-more').addEventListener('click', () => loadPage(false));

// Scorrimento infinito: quando la sentinella entra nello schermo si carica la
// pagina dopo. Il pulsante resta come ripiego (e per chi usa la tastiera).
if ('IntersectionObserver' in window) {
    const sentinel = document.getElementById('catalog-sentinel');
    new IntersectionObserver(entries => {
        entries.forEach(en => {
            if (en.isIntersecting && !state.done && !state.loading && state.page > 0) loadPage(false);
        });
    }, { rootMargin: '600px 0px' }).observe(sentinel);
}

document.addEventListener('DOMContentLoaded', () => loadPage(true));

// ============================================================
// 39.1.1 — L'AGENTE: DAL BISOGNO AL RISULTATO
// ------------------------------------------------------------
// Due modi nella stessa barra:
//   IA (Veloce)      → il pianificatore sceglie gli strumenti, il browser
//                      li esegue (sono gli stessi endpoint delle pagine) e
//                      l'aggregatore unisce tutto in una risposta sola;
//   Catalogo (Manuale) → la ricerca di sempre, per scegliere a mano.
// Se l'IA non e' disponibile si passa al catalogo e lo si dice: nessun
// vicolo cieco e nessun errore secco.
// ============================================================
const AT = {
    run       : <?= json_encode(t('Run it now')) ?>,
    search    : <?= json_encode(t('Search in the catalogue')) ?>,
    planning  : <?= json_encode(t('Understanding what you need...')) ?>,
    running   : <?= json_encode(t('Running the tools...')) ?>,
    step      : <?= json_encode(t('Step {n} of {m}')) ?>,
    result    : <?= json_encode(t('Result')) ?>,
    agent     : <?= json_encode(t('L.U.X. agent')) ?>,
    why       : <?= json_encode(t('Why these tools')) ?>,
    chain     : <?= json_encode(t('The chain it ran')) ?>,
    stepsTitle: <?= json_encode(t('What the tools produced')) ?>,
    copy      : <?= json_encode(t('Copy')) ?>,
    copied    : <?= json_encode(t('Copied')) ?>,
    save      : <?= json_encode(t('Save in my space')) ?>,
    saved     : <?= json_encode(t('Saved in my space')) ?>,
    link      : <?= json_encode(t('Create a shareable link')) ?>,
    linkCopied: <?= json_encode(t('Link copied')) ?>,
    linkReady : <?= json_encode(t('Public link ready')) ?>,
    openTool  : <?= json_encode(t('Open the tool')) ?>,
    again     : <?= json_encode(t('Start again')) ?>,
    missing   : <?= json_encode(t('I need a few more details to run it')) ?>,
    execute   : <?= json_encode(t('Run')) ?>,
    fallback  : <?= json_encode(t('The AI is not available right now: here are the proposals from the catalogue.')) ?>,
    toolError : <?= json_encode(t('One of the tools did not answer. You can open it and run it by hand.')) ?>,
    err       : <?= json_encode(t('Something went wrong. Try again.')) ?>,
    noItems   : <?= json_encode(t('Nothing found. Try describing the need with other words.')) ?>,
    aiHint    : <?= json_encode(t('Write what you need in your own words: L.U.X. chooses the tools and shows you the finished result.')) ?>,
    catHint   : <?= json_encode(t('Write what you need: L.U.X. proposes the most suitable tools and you choose which to open.')) ?>
};

let agentMode = 'ai';
let agentLast = null;

const agentBox   = document.getElementById('agent-result');
const agentInput = document.getElementById('search-projects');
const modeNote   = document.getElementById('agent-mode-note');
const goLabel    = document.getElementById('intent-go-label');

function agentEsc(s) { return esc(s); }
function agentIcon(name, cls) { return svgIcon(name, 15, cls || 'ico', 1.6); }

function setMode(mode) {
    agentMode = mode === 'catalog' ? 'catalog' : 'ai';
    document.querySelectorAll('#agent-modes .agent-mode').forEach(function (b) {
        const on = b.dataset.mode === agentMode;
        b.classList.toggle('active', on);
        b.setAttribute('aria-selected', on ? 'true' : 'false');
    });
    if (goLabel) goLabel.textContent = agentMode === 'ai' ? AT.run : AT.search;
    if (modeNote) modeNote.textContent = agentMode === 'ai' ? AT.aiHint : AT.catHint;
    if (agentBox) { agentBox.hidden = true; agentBox.innerHTML = ''; }
}

document.querySelectorAll('#agent-modes .agent-mode').forEach(function (b) {
    b.addEventListener('click', function () { setMode(b.dataset.mode); });
});

function agentLoading(text) {
    agentBox.hidden = false;
    agentBox.innerHTML = '<div class="agent-card"><div class="agent-loading">' +
        '<span class="dot"></span><span class="dot"></span><span class="dot"></span>' +
        '<span>' + agentEsc(text) + '</span></div></div>';
}

function agentChips(steps) {
    return steps.map(function (s) {
        const cls = s.state === 'done' ? ' done' : (s.state === 'error' ? ' err' : '');
        return '<span class="agent-chip' + cls + '">' + agentIcon(s.icon || 'sparkles') + agentEsc(s.name || s.key) + '</span>';
    }).join('');
}

// ---------- l'esecuzione di un passo ----------
// Ogni passo chiama la pagina dello strumento in POST: e' lo stesso motore
// che usa la pagina quando premi il pulsante, quindi il risultato e' identico.
async function runStep(step, previous) {
    const body = new URLSearchParams();
    body.set('lux_tool', step.key);
    (step.fields || []).forEach(function (f) {
        let v = (step.inputs && step.inputs[f.name] !== undefined) ? step.inputs[f.name] : (f.default || '');
        v = String(v);
        // I segnaposto {passo1} diventano quello che ha prodotto il passo prima.
        v = v.replace(/\{passo(\d)\}/gi, function (m, n) {
            const prev = previous[Number(n) - 1];
            return prev ? String(prev.content || '').substring(0, 3000) : '';
        });
        body.set(f.name, v);
        step.inputs[f.name] = v;
    });

    const res = await fetch(step.endpoint, { method: 'POST', body: body });
    const data = await res.json();
    if (!data || !data.success) throw new Error((data && data.error) || AT.toolError);
    return data;
}

async function runPlan(plan) {
    const steps = plan.steps || [];
    const done = [];
    agentLoading(AT.running);

    try {
        for (let i = 0; i < steps.length; i++) {
            steps[i].state = 'running';
            agentBox.innerHTML = '<div class="agent-card"><div class="agent-head"><span class="agent-head-ico">' + agentIcon('sparkles', 'ico', 1.6) + '</span>' +
                '<div class="agent-head-main"><span class="agent-kicker">' + agentEsc(AT.agent) + '</span>' +
                '<h3 class="agent-title">' + agentEsc(AT.step.replace('{n}', String(i + 1)).replace('{m}', String(steps.length))) + '</h3>' +
                '<div class="agent-chain">' + agentChips(steps) + '</div></div></div></div>';

            const out = await runStep(steps[i], done);
            steps[i].state = 'done';
            done.push({ name: steps[i].name, tool: steps[i].key, content: String(out.content || '') });
            if (window.LuxSpace) LuxSpace.recordTool(steps[i].key, steps[i].name, steps[i].endpoint);
        }
    } catch (e) {
        // Un passo non ha risposto: si dice quale e si lascia la porta aperta
        // (il link allo strumento), invece di mostrare un errore generico.
        const bad = steps.find(function (s) { return s.state === 'running'; });
        if (bad) bad.state = 'error';
        agentResult({
            title: AT.toolError,
            why: String(e && e.message ? e.message : ''),
            steps: steps,
            content: done.length ? done.map(function (d) { return '— ' + d.name + "\n" + d.content; }).join("\n\n") : '',
            plain: true
        });
        return;
    }

    // Un solo passo: il risultato e' quello. Piu' passi: si uniscono in uno solo.
    let content = done.length === 1 ? done[0].content : '';
    if (done.length > 1) {
        try {
            const r = await fetch('/api/agent.php', {
                method: 'POST', headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ op: 'aggregate', need: plan.need || '', steps: done })
            });
            const d = await r.json();
            if (d && d.success) content = String(d.content || '');
        } catch (e) {}
        if (!content.trim()) {
            content = done.map(function (x) { return '— ' + x.name + "\n" + x.content; }).join("\n\n");
        }
    }

    agentResult({ title: AT.result, why: plan.why || '', steps: steps, content: content, plain: false });
}

function agentResult(o) {
    agentLast = o;
    agentBox.hidden = false;
    const single = (o.steps || []).length === 1;
    const tool = single ? o.steps[0] : null;

    let html = '<div class="agent-card">';
    html += '<div class="agent-head"><span class="agent-head-ico">' + agentIcon('sparkles', 'ico', 1.6) + '</span>' +
            '<div class="agent-head-main">' +
              '<span class="agent-kicker">' + agentEsc(AT.agent) + '</span>' +
              '<h3 class="agent-title">' + agentEsc(o.title || AT.result) + '</h3>' +
              (o.why ? '<p class="agent-sub">' + agentEsc(o.why) + '</p>' : '') +
              '<div class="agent-chain">' + agentChips(o.steps || []) + '</div>' +
            '</div></div>';

    if (o.content && o.content.trim()) {
        const mono = single && tool && tool.output === 'mono';
        html += '<div class="agent-body' + (mono ? ' mono' : '') + '">' + agentEsc(o.content) + '</div>';
    }

    if ((o.steps || []).length > 1) {
        html += '<details class="agent-steps"><summary>' + agentEsc(AT.stepsTitle) + '</summary>';
        o.steps.forEach(function (s, i) {
            html += '<div class="agent-step"><h4>' + agentEsc(AT.step.replace('{n}', String(i + 1)).replace('{m}', String(o.steps.length))) + ' · ' + agentEsc(s.name) + '</h4>' +
                    '<pre>' + agentEsc(s.content || '') + '</pre></div>';
        });
        html += '</details>';
    }

    html += '<div class="agent-actions">';
    html += '<button type="button" class="agent-act primary" data-agent="copy">' + agentIcon('copy') + '<span>' + agentEsc(AT.copy) + '</span></button>';
    if (window.LuxSpace) {
        html += '<button type="button" class="agent-act" data-agent="save">' + agentIcon('star') + '<span>' + agentEsc(AT.save) + '</span></button>';
        html += '<button type="button" class="agent-act" data-agent="link">' + agentIcon('link') + '<span>' + agentEsc(AT.link) + '</span></button>';
    }
    if (single && tool) {
        html += '<a class="agent-act" href="' + agentEsc(tool.endpoint) + '">' + agentIcon('external') + '<span>' + agentEsc(AT.openTool) + '</span></a>';
    }
    html += '<button type="button" class="agent-act" data-agent="again">' + agentIcon('refresh') + '<span>' + agentEsc(AT.again) + '</span></button>';
    html += '</div>';

    if ((o.steps || []).some(function (s) { return s.state === 'error'; })) {
        html += '<p class="agent-note">' + agentEsc(AT.toolError) + '</p>';
    }
    html += '</div>';
    agentBox.innerHTML = html;

    agentBox.querySelectorAll('[data-agent]').forEach(function (btn) {
        btn.addEventListener('click', function () {
            const act = btn.dataset.agent;
            const text = (o.content || '');
            if (act === 'copy') {
                copyText(text);
                const lab = btn.querySelector('span');
                if (lab) { const old = lab.textContent; lab.textContent = AT.copied; setTimeout(function () { lab.textContent = old; }, 1600); }
            }
            if (act === 'save' && window.LuxSpace) {
                LuxSpace.saveResult({ tool: single ? tool.key : 'agent', title: (o.why || AT.result).substring(0, 120), content: text });
                const lab = btn.querySelector('span');
                if (lab) { const old = lab.textContent; lab.textContent = AT.saved; setTimeout(function () { lab.textContent = old; }, 1600); }
            }
            if (act === 'link' && window.LuxSpace) {
                LuxSpace.share({ tool: single ? tool.key : 'agent', title: (o.why || AT.result).substring(0, 120), content: text }).then(function (url) {
                    copyText(url);
                    const lab = btn.querySelector('span');
                    if (lab) { const old = lab.textContent; lab.textContent = AT.linkCopied; setTimeout(function () { lab.textContent = old; }, 2000); }
                }).catch(function () {
                    const lab = btn.querySelector('span');
                    if (lab) { const old = lab.textContent; lab.textContent = AT.err; setTimeout(function () { lab.textContent = old; }, 2000); }
                });
            }
            if (act === 'again') {
                agentBox.hidden = true;
                agentBox.innerHTML = '';
                if (agentInput) { agentInput.value = ''; agentInput.focus(); }
            }
        });
    });
}

function copyText(text) {
    const s = String(text || '');
    if (navigator.clipboard && navigator.clipboard.writeText) { navigator.clipboard.writeText(s).catch(function () {}); return; }
    const ta = document.createElement('textarea');
    ta.value = s; ta.style.position = 'fixed'; ta.style.opacity = '0';
    document.body.appendChild(ta); ta.select();
    try { document.execCommand('copy'); } catch (e) {}
    document.body.removeChild(ta);
}

// ---------- il bisogno scritto dall'utente ----------
async function agentAsk(q) {
    agentLoading(AT.planning);
    let plan = null;
    try {
        const res = await fetch('/api/agent.php?op=plan', {
            method: 'POST', headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ q: q, memory: (window.LuxSpace ? LuxSpace.memoryHint() : {}) })
        });
        plan = await res.json();
    } catch (e) {
        agentBox.innerHTML = '<div class="agent-card"><div class="agent-body">' + agentEsc(AT.err) + '</div></div>';
        return;
    }
    if (!plan || !plan.success) {
        agentBox.innerHTML = '<div class="agent-card"><div class="agent-body">' + agentEsc(AT.err) + '</div></div>';
        return;
    }

    // L'IA non c'e': il catalogo risponde comunque (e si dice perche').
    if (plan.mode !== 'ai') {
        const items = plan.items || [];
        agentBox.hidden = false;
        agentBox.innerHTML = '<div class="agent-card"><div class="agent-head"><span class="agent-head-ico">' + agentIcon('grid') + '</span>' +
            '<div class="agent-head-main"><span class="agent-kicker">' + agentEsc(AT.agent) + '</span>' +
            '<h3 class="agent-title">' + agentEsc(AT.fallback) + '</h3></div></div></div>';
        materializeIntent({ items: items, query: q, why: '' });
        return;
    }

    // Se manca qualche dato, lo si chiede qui sotto invece di aprire una pagina.
    const first = (plan.steps || [])[0];
    if (first && first.missing && first.missing.length) {
        let html = '<div class="agent-card"><div class="agent-head"><span class="agent-head-ico">' + agentIcon('sparkles') + '</span>' +
            '<div class="agent-head-main"><span class="agent-kicker">' + agentEsc(AT.agent) + '</span>' +
            '<h3 class="agent-title">' + agentEsc(AT.missing) + '</h3>' +
            '<div class="agent-chain">' + agentChips(plan.steps) + '</div></div></div>';
        html += '<div class="agent-body">';
        first.missing.forEach(function (name) {
            const f = (first.fields || []).find(function (x) { return x.name === name; }) || { name: name, label: name, type: 'text' };
            html += '<div style="margin-bottom:10px"><label for="ag_' + agentEsc(f.name) + '" style="display:block;font-size:12.5px;color:var(--text-light);margin-bottom:5px">' + agentEsc(f.label || f.name) + '</label>';
            html += '<input id="ag_' + agentEsc(f.name) + '" data-field="' + agentEsc(f.name) + '" type="text" style="width:100%;background:var(--ink-2);border:1px solid var(--line-2);border-radius:6px;color:var(--bone);padding:10px 12px;outline:none" value="' + agentEsc((first.inputs && first.inputs[f.name]) || '') + '"></div>';
        });
        html += '<button type="button" class="agent-act primary" id="agent-fill">' + agentIcon('sparkles') + '<span>' + agentEsc(AT.execute) + '</span></button></div></div>';
        agentBox.hidden = false;
        agentBox.innerHTML = html;
        const fill = document.getElementById('agent-fill');
        if (fill) fill.addEventListener('click', function () {
            agentBox.querySelectorAll('[data-field]').forEach(function (inp) {
                first.inputs[inp.dataset.field] = inp.value;
            });
            first.missing = [];
            runPlan(plan);
        });
        return;
    }

    runPlan(plan);
}

// La barra ha un solo punto di ingresso: il modo scelto decide cosa fare.
async function agentSubmit() {
    const q = (agentInput && agentInput.value || '').trim();
    if (q === '') { setMode(agentMode); loadPage(true); return; }
    if (agentMode === 'catalog') { agentBox.hidden = true; intentSearch(); return; }
    await agentAsk(q);
}

if (intentBtn) intentBtn.addEventListener('click', agentSubmit);
if (searchInput) {
    searchInput.addEventListener('keydown', function (e) { if (e.key === 'Enter') { e.preventDefault(); agentSubmit(); } });
}
setMode('ai');
</script>

<?php lux_footer(); ?>
