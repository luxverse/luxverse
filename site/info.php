<?php
// ============================================================
// L.U.X. VERSE - TUTTE LE PAGINE INFORMATIVE IN UN UNICO FILE
// ============================================================
// Prima erano sei file separati: chi-siamo.php, privacy.php, terms.php,
// faq.php, assistenza.php, contattaci.php. Il contenuto e' lo stesso, ora
// qui dentro, con la barra delle sezioni in comune.
//
// Le URL pubbliche NON cambiano: /chi-siamo.php, /privacy.php, /terms.php,
// /faq.php, /assistenza.php, /contattaci.php e /status.php. Il rewrite di
// .htaccess le manda a info.php con ?__info=<sezione>; senza rewrite funziona
// /info.php?__info=<sezione>.
//
// Ogni sezione carica solo il proprio CSS, piu' quello condiviso qui sotto.
// ============================================================

require_once __DIR__ . '/config.php';

// ---------- QUALE SEZIONE? ----------
$__info = (string)($_GET['__info'] ?? '');
if ($__info === '') {
    $__info = basename((string)parse_url((string)($_SERVER['REQUEST_URI'] ?? ''), PHP_URL_PATH), '.php');
}
$__info = preg_replace('/[^a-z0-9_-]/', '', strtolower($__info));
// Alias corto: /timeline.php porta alla stessa pagina di /linea-del-tempo.php.
if ($__info === 'timeline') $__info = 'linea-del-tempo';

// L'URL pubblico resta /<sezione>.php: header (canonical, data-page), nav e
// config.php leggono SCRIPT_NAME, che con il rewrite punterebbe a info.php.
if ($__info !== '') {
    $_SERVER['SCRIPT_NAME'] = '/' . $__info . '.php';
}

// Le sezioni informative, nell'ordine in cui appaiono nella barra.
$__info_sections = [
    'chi-siamo'       => ['Chi Siamo', 'info'],
    'linea-del-tempo' => ['Linea del Tempo', 'time'],
    'faq'             => ['FAQ', 'help'],
    'assistenza'      => ['Assistenza', 'chat'],
    'contattaci'      => ['Contatti', 'mail'],
    'status'          => ['Stato dei sistemi', 'pulse'],
    'privacy'    => ['Privacy Policy', 'lock'],
    'terms'      => ['Termini di Servizio', 'file-text'],
];
if (!isset($__info_sections[$__info])) {
    $__info = '';
}

// ---------- CSS CONDIVISO DELLE PAGINE INFORMATIVE ----------
$__info_css = <<<'LUXINFOCSS'
/* ============================================================
   PAGINE INFORMATIVE — barra delle sezioni (comune a tutte)
   ============================================================ */
.info-hub {
    padding-top: 20px;
}
.info-hub-inner {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
    align-items: center;
    padding: 14px 0;
    border-top: 1px solid var(--border);
    border-bottom: 1px solid var(--border);
}
.info-hub-title {
    font-family: var(--font-mono);
    font-size: 11px;
    letter-spacing: .16em;
    text-transform: uppercase;
    color: var(--text-muted);
    margin-right: 6px;
}
.info-chip {
    display: inline-flex;
    align-items: center;
    gap: 7px;
    padding: 7px 13px;
    border: 1px solid var(--border);
    border-radius: 999px;
    background: rgba(255,255,255,.02);
    color: var(--text);
    font-size: 12.5px;
    font-weight: 500;
    text-decoration: none;
    transition: border-color .18s ease, color .18s ease, background .18s ease;
}
.info-chip svg { width: 14px; height: 14px; }
.info-chip:hover { border-color: var(--gold); color: var(--gold); }
.info-chip.is-current {
    border-color: var(--gold);
    color: var(--gold);
    background: rgba(216,169,60,.08);
}
.info-chip.is-current::after {
    content: '';
    width: 6px;
    height: 6px;
    border-radius: 50%;
    background: var(--gold);
}
@media (max-width: 640px) {
    .info-chip { padding: 6px 11px; font-size: 12px; }
    .info-hub-title { width: 100%; margin-bottom: 2px; }
}

/* ============================================================
   CONSOLE DI SUPPORTO — Assistenza e Contatti (comune)
   ============================================================ */
@keyframes supSpin { to { transform: rotate(360deg); } }
@keyframes supPulse { 0%,100% { opacity: 1; } 50% { opacity: .35; } }

.sup-console {
    border: 1px solid var(--border);
    border-radius: 14px;
    padding: 16px 18px;
    background: rgba(255,255,255,.02);
    margin-bottom: 22px;
    display: flex;
    flex-direction: column;
    gap: 12px;
}
.sup-head {
    display: flex;
    align-items: center;
    gap: 8px;
    font-family: var(--font-mono);
    font-size: 11px;
    letter-spacing: .14em;
    text-transform: uppercase;
    color: var(--text-muted);
}
.sup-head svg { width: 15px; height: 15px; }
.sup-live { margin-left: auto; display: inline-flex; align-items: center; gap: 6px; color: var(--green); }
.sup-live i { width: 7px; height: 7px; border-radius: 50%; background: var(--green); animation: supPulse 2s ease-in-out infinite; }
.sup-queue { font-size: 12.5px; color: var(--text-light); }
.sup-queue b { font-family: var(--font-mono); color: var(--text); }
.sup-triage { display: flex; flex-wrap: wrap; align-items: center; gap: 14px; }
.sup-badge {
    display: flex;
    flex-direction: column;
    gap: 2px;
    padding: 9px 13px;
    border-radius: 10px;
    border: 1px solid var(--border);
    min-width: 100px;
}
.sup-badge b { font-family: var(--font-title); font-size: 18px; }
.sup-badge span { font-size: 10.5px; color: var(--text-muted); }
.sup-badge.p1 { border-color: rgba(217,108,91,.5); } .sup-badge.p1 b { color: var(--red); }
.sup-badge.p2 { border-color: rgba(216,169,60,.5); } .sup-badge.p2 b { color: var(--gold); }
.sup-badge.p3 b { color: var(--text); }
.sup-badge.p4 b { color: var(--text-muted); }
.sup-meter { flex: 1; min-width: 190px; display: flex; align-items: center; gap: 10px; }
.sup-meter-label, .sup-meter-value {
    font-family: var(--font-mono);
    font-size: 10.5px;
    letter-spacing: .1em;
    text-transform: uppercase;
    color: var(--text-muted);
}
.sup-bar { flex: 1; height: 7px; border-radius: 999px; background: rgba(237,233,224,.08); overflow: hidden; }
.sup-bar i { display: block; height: 100%; background: var(--gold); transition: width .25s ease; }
.sup-sla { display: inline-flex; align-items: center; gap: 7px; font-size: 12px; color: var(--text-light); }
.sup-sla svg { width: 14px; height: 14px; color: var(--gold); }
.sup-reasons { list-style: none; margin: 0; padding: 0; display: flex; flex-wrap: wrap; gap: 6px; }
.sup-reasons li {
    font-size: 11.5px;
    color: var(--text-muted);
    border: 1px solid var(--border);
    border-radius: 999px;
    padding: 4px 9px;
}
.sup-actions { display: flex; align-items: center; gap: 10px; flex-wrap: wrap; }
.sup-btn {
    display: inline-flex;
    align-items: center;
    gap: 7px;
    padding: 8px 13px;
    border-radius: 9px;
    border: 1px solid var(--border);
    background: rgba(255,255,255,.02);
    color: var(--text);
    font: inherit;
    font-size: 12.5px;
    font-weight: 600;
    cursor: pointer;
}
.sup-btn:hover { border-color: var(--gold); color: var(--gold); }
.sup-btn svg { width: 14px; height: 14px; }
.sup-btn.is-busy svg { animation: supSpin 1s linear infinite; }
.sup-btn.small { padding: 6px 10px; font-size: 11.5px; margin-top: 10px; }
.sup-hint { font-size: 11.5px; color: var(--text-muted); }
.sup-ai-out { font-size: 13px; color: var(--text-light); border-top: 1px solid var(--border); padding-top: 12px; }
.sup-ai-head {
    font-family: var(--font-mono);
    font-size: 10.5px;
    letter-spacing: .12em;
    text-transform: uppercase;
    color: var(--text-muted);
    margin-bottom: 8px;
}
.sup-ai-row { display: flex; gap: 10px; align-items: baseline; }
.sup-ai-row b { font-family: var(--font-title); font-size: 16px; color: var(--gold); }
.sup-ai-out ul { margin: 8px 0 0 18px; }
.sup-ai-out li { margin-bottom: 4px; }
.sup-ai-wait { color: var(--text-muted); }
.sup-ai-warn { color: var(--gold); }
.sup-suggest { border-top: 1px solid var(--border); padding-top: 12px; font-size: 12.5px; }
.sup-suggest-title {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    color: var(--text-muted);
    font-family: var(--font-mono);
    font-size: 10.5px;
    letter-spacing: .12em;
    text-transform: uppercase;
}
.sup-suggest svg { width: 13px; height: 13px; }
.sup-suggest ul { list-style: none; margin: 8px 0 0; padding: 0; display: flex; flex-direction: column; gap: 6px; }
.sup-suggest li a { color: var(--cyan); text-decoration: none; }
.sup-suggest li a:hover { text-decoration: underline; }

/* scheda tecnica del ticket tracciato */
.sup-ticket { border-top: 1px solid var(--border); margin-top: 14px; padding-top: 14px; font-size: 12.5px; color: var(--text-light); }
.sup-ticket p { margin-bottom: 8px; }
.sup-timeline { list-style: none; margin: 10px 0 0; padding: 0; display: flex; flex-direction: column; gap: 9px; }
.sup-timeline li { display: flex; gap: 10px; }
.sup-timeline .when { font-family: var(--font-mono); font-size: 10.5px; color: var(--text-muted); min-width: 112px; }
.sup-timeline .what b { color: var(--text); font-weight: 600; }
.sup-timeline .what span { display: block; color: var(--text-light); }

/* ============================================================
   RICERCA NELLA FAQ
   ============================================================ */
.faq-search {
    display: flex;
    align-items: center;
    gap: 10px;
    max-width: 900px;
    margin: 0 auto 18px;
    padding: 12px 16px;
    border: 1px solid var(--border);
    border-radius: 12px;
    background: var(--bg-card);
}
.faq-search svg { width: 16px; height: 16px; color: var(--text-muted); flex-shrink: 0; }
.faq-search input {
    flex: 1;
    border: 0;
    background: transparent;
    color: var(--text);
    font: inherit;
    font-size: 14.5px;
    outline: none;
    min-width: 0;
}
.faq-search-count {
    font-family: var(--font-mono);
    font-size: 11px;
    letter-spacing: .1em;
    text-transform: uppercase;
    color: var(--text-muted);
    white-space: nowrap;
}
.faq-empty {
    max-width: 900px;
    margin: 0 auto 18px;
    padding: 14px 16px;
    border: 1px solid var(--border);
    border-radius: 12px;
    color: var(--text-light);
    font-size: 13.5px;
}
.faq-item[hidden], .faq-category[hidden] { display: none !important; }

/* ============================================================
   BLOCCHI INFORMATIVI (comuni a tutte le pagine informative)
   ============================================================ */
.lux-block {
    margin-top: 44px;
    padding-top: 28px;
    border-top: 1px solid var(--border);
}
.lux-block h2 {
    font-family: var(--font-title);
    font-size: 1.35rem;
    font-weight: 700;
    color: var(--text);
    margin-bottom: 12px;
}
.lux-block > p { color: var(--text-light); font-size: 14.5px; line-height: 1.85; margin-bottom: 12px; }
.lux-steps { display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 14px; margin-top: 16px; }
.lux-step { border: 1px solid var(--border); border-radius: 14px; padding: 16px 18px; background: var(--bg-card); }
.lux-step b {
    display: block;
    font-family: var(--font-mono);
    font-size: 10.5px;
    letter-spacing: .14em;
    text-transform: uppercase;
    color: var(--gold);
}
.lux-step h3 { font-size: 15px; margin: 8px 0 6px; color: var(--text); }
.lux-step p { font-size: 13px; color: var(--text-light); line-height: 1.7; margin: 0; }
.lux-table { width: 100%; border-collapse: collapse; margin-top: 14px; font-size: 13.5px; }
.lux-table th, .lux-table td { text-align: left; padding: 10px 12px; border-bottom: 1px solid var(--border); vertical-align: top; }
.lux-table th {
    font-family: var(--font-mono);
    font-size: 10.5px;
    letter-spacing: .12em;
    text-transform: uppercase;
    color: var(--text-muted);
}
.lux-table td { color: var(--text-light); }
.lux-table tr:last-child td { border-bottom: 0; }
.lux-facts { display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 12px; margin-top: 16px; }
.lux-fact { border: 1px solid var(--border); border-radius: 12px; padding: 14px 16px; background: var(--bg-card); }
.lux-fact b { font-family: var(--font-title); font-size: 24px; display: block; color: var(--text); }
.lux-fact span {
    font-family: var(--font-mono);
    font-size: 10.5px;
    letter-spacing: .1em;
    text-transform: uppercase;
    color: var(--text-muted);
}
.lux-list { list-style: none; margin: 14px 0 0; padding: 0; display: flex; flex-direction: column; gap: 8px; }
.lux-list li { position: relative; padding-left: 20px; font-size: 13.5px; color: var(--text-light); line-height: 1.75; }
.lux-list li::before {
    content: '';
    position: absolute;
    left: 4px;
    top: 9px;
    width: 6px;
    height: 6px;
    border-radius: 50%;
    background: var(--gold);
}
@media (max-width: 640px) {
    .lux-block { margin-top: 34px; }
    .lux-table th:nth-child(3), .lux-table td:nth-child(3) { display: none; }
}

LUXINFOCSS;

// ---------- BARRA DELLE SEZIONI ----------
function lux_info_nav(string $current): void
{
    global $__info_sections;
    ?>
        <div class="container">
            <div class="info-hub-inner">
                <span class="info-hub-title"><?= t('Pagine informative') ?></span>
                <?php foreach ($__info_sections as $slug => $meta):
                    $isNow = ($slug === $current);
                ?>
                    <a class="info-chip<?= $isNow ? ' is-current' : '' ?>"
                       href="/<?= htmlspecialchars($slug) ?>.php"
                       <?= $isNow ? 'aria-current="page"' : '' ?>>
                        <?= icon($meta[1], 14) ?>
                        <?= htmlspecialchars(t($meta[0])) ?>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>
    <?php
}


// ============================================================
// SEZIONE: chi-siamo   (prima era il file chi-siamo.php)
// ============================================================
if ($__info === 'chi-siamo') {

lux_session_start();
$isAdmin = isAdminLoggedIn();
$pageTitle       = t('L.U.X. Verse - About Us');
$pageDescription = t('L.U.X. Verse is not just a website: it is the result of a simple but ambitious idea — that of making artificial intelligence a tool within everyone\'s reach.');
$pageKeywords    = 'about, history, story, mission, team, L.U.X. Verse, AI ecosystem';

$extraCSS = '
.about-section { padding: 60px 0; }
.about-content { max-width: 900px; margin: 0 auto; }

.section-title { text-align: center; margin-bottom: 50px; }
.section-title h2 {
    font-family: var(--font-title);
    font-size: 2.4em;
    font-weight: 700;
    background: var(--gradient-full);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    letter-spacing: -1px;
    margin-bottom: 10px;
}
.section-title p { color: var(--text-light); font-size: 15px; }

.story-text { color: var(--text-light); font-size: 1.05em; line-height: 1.9; }
.story-text p { margin-bottom: 22px; }
.story-text strong { color: var(--text); font-weight: 600; }
.story-text em { color: var(--cyan); font-style: normal; font-weight: 500; }

.mission-box {
    background: var(--bg-card);
    border: 1px solid var(--border);
    border-radius: 22px;
    padding: 40px 32px;
    text-align: center;
    margin: 40px 0;
    position: relative;
    overflow: hidden;
}
.mission-box::before {
    content: "";
    position: absolute;
    top: 0; left: 0; right: 0;
    height: 3px;
    background: var(--gradient-full);
}
.mission-box .mission-icon { width: 48px; height: 48px; margin-bottom: 16px; display: block; }
.mission-box .mission-icon svg { width: 100%; height: 100%; }
.mission-box h2 {
    font-family: var(--font-title);
    font-size: 1.8em;
    font-weight: 700;
    margin-bottom: 16px;
    background: var(--gradient-full);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
}
.mission-box p {
    color: var(--text-light);
    font-size: 1.1em;
    line-height: 1.8;
    max-width: 700px;
    margin: 0 auto;
}

.past-section {
    background: rgba(232,97,75,0.04);
    border: 1px solid rgba(232,97,75,0.15);
    border-left: 4px solid var(--pink);
    border-radius: 16px;
    padding: 32px 36px;
    margin: 50px auto;
    position: relative;
}
.past-section h3 {
    font-family: var(--font-title);
    font-size: 1.4em;
    font-weight: 700;
    color: var(--pink);
    margin-bottom: 18px;
    display: flex;
    align-items: center;
    gap: 12px;
}
.past-section h3 .icon { stroke: var(--pink); }
.past-section p { color: var(--text-light); font-size: 15.5px; line-height: 1.9; margin-bottom: 16px; }
.past-section p:last-child { margin-bottom: 0; }
.past-section p em { color: var(--pink); font-style: normal; font-weight: 600; }
.past-section p strong { color: #fff; font-weight: 600; }

.goals-intro { text-align: center; max-width: 800px; margin: 60px auto 50px; }
.goals-intro h2 {
    font-family: var(--font-title);
    font-size: 2.4em;
    font-weight: 700;
    background: var(--gradient-full);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    letter-spacing: -1px;
    margin-bottom: 16px;
}
.goals-intro > p { color: var(--text-light); font-size: 16px; margin-bottom: 28px; }
.goals-quote {
    color: var(--text-light);
    font-size: 1.05em;
    line-height: 1.9;
    font-style: italic;
    padding: 24px 32px;
    background: rgba(212, 170, 87,0.04);
    border-left: 3px solid var(--cyan);
    border-radius: 0 14px 14px 0;
    text-align: left;
}
.goals-quote strong { color: var(--cyan); font-style: normal; font-weight: 600; }

.goals-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
    gap: 20px;
    margin: 0 0 60px;
}
.goal-card {
    background: var(--bg-card);
    border: 1px solid var(--border);
    border-radius: 20px;
    padding: 32px 26px;
    text-align: center;
    position: relative;
    overflow: hidden;
    transition: transform 0.3s, border-color 0.3s, box-shadow 0.3s;
}
.goal-card::before {
    content: "";
    position: absolute;
    top: 0; left: 0; right: 0;
    height: 3px;
    background: var(--gradient-full);
    transform: scaleX(0);
    transform-origin: left;
    transition: transform 0.4s;
}
.goal-card:hover {
    border-color: var(--border-hover);
    transform: translateY(-6px);
    box-shadow: 0 20px 40px rgba(0,0,0,0.3);
}
.goal-card:hover::before { transform: scaleX(1); }
.goal-icon { width: 44px; height: 44px; margin-bottom: 16px; display: block; filter: drop-shadow(0 0 12px rgba(212, 170, 87,0.3)); }
.goal-icon svg, .value-card .value-icon svg { width: 100%; height: 100%; }
.goal-title { font-family: var(--font-title); font-size: 1.25em; font-weight: 700; color: #fff; margin-bottom: 10px; line-height: 1.3; }
.goal-desc { color: var(--text-light); font-size: 14px; line-height: 1.7; }
.goal-desc strong { color: var(--yellow); font-weight: 600; }

.goal-card.special {
    background: linear-gradient(135deg, rgba(212,175,55,0.06), rgba(232,97,75,0.06), rgba(200, 127, 42,0.06));
    border: 1px solid rgba(212,175,55,0.25);
}
.goal-card.special::before {
    background: linear-gradient(135deg, #F0CE86, #D96C5B, #D8A93C);
    transform: scaleX(1);
}
.goal-card.special .goal-title {
    background: linear-gradient(135deg, #F0CE86, #D96C5B);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
}

.timeline { position: relative; margin: 60px 0; padding-left: 40px; }
.timeline::before {
    content: "";
    position: absolute;
    left: 12px; top: 8px; bottom: 8px;
    width: 2px;
    background: linear-gradient(180deg, var(--cyan), var(--purple), var(--pink));
    border-radius: 2px;
}
.timeline-item { position: relative; margin-bottom: 40px; }
.timeline-item::before {
    content: "";
    position: absolute;
    left: -34px; top: 6px;
    width: 14px; height: 14px;
    border-radius: 50%;
    background: var(--gradient-primary);
    box-shadow: 0 0 15px rgba(212, 170, 87,0.5);
    border: 3px solid var(--bg);
}
.timeline-date { font-family: var(--font-title); font-size: 13px; font-weight: 700; color: var(--cyan); text-transform: uppercase; letter-spacing: 1px; margin-bottom: 6px; }
.timeline-title { font-family: var(--font-title); font-size: 1.25em; font-weight: 600; color: #fff; margin-bottom: 8px; }
.timeline-text { color: var(--text-light); font-size: 15px; line-height: 1.8; }

.img-full {
    width: 100%;
    height: 380px;
    object-fit: cover;
    border-radius: 22px;
    margin: 40px 0;
    border: 1px solid var(--border);
}

.feature-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 40px;
    align-items: center;
    margin: 60px 0;
}
.feature-row.reverse { direction: rtl; }
.feature-row.reverse > * { direction: ltr; }
.feature-img {
    width: 100%;
    height: 320px;
    object-fit: cover;
    border-radius: 20px;
    border: 1px solid var(--border);
}
.feature-text h3 { font-family: var(--font-title); font-size: 1.6em; font-weight: 700; margin-bottom: 14px; color: var(--cyan); }
.feature-text p { color: var(--text-light); font-size: 1em; line-height: 1.8; margin-bottom: 14px; }

.values-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 20px; margin: 40px 0; }
.value-card {
    background: var(--bg-card);
    border: 1px solid var(--border);
    border-radius: 18px;
    padding: 28px 20px;
    text-align: center;
    transition: transform 0.2s, border-color 0.2s;
}
.value-card:hover { border-color: var(--border-hover); transform: translateY(-4px); }
.value-card .value-icon { width: 36px; height: 36px; margin-bottom: 12px; display: block; }
.value-card h4 { font-family: var(--font-title); font-size: 1.05em; font-weight: 600; margin-bottom: 8px; color: #fff; }
.value-card p { color: var(--text-light); font-size: 13px; line-height: 1.6; }

.team-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 24px; margin: 40px 0; }
.team-card {
    background: var(--bg-card);
    border: 1px solid var(--border);
    border-radius: 20px;
    padding: 28px 24px;
    text-align: center;
    transition: transform 0.2s, border-color 0.2s;
}
.team-card:hover { border-color: var(--border-hover); transform: translateY(-4px); }
.team-avatar {
    width: 90px; height: 90px;
    border-radius: 50%;
    margin: 0 auto 16px;
    background: var(--gradient-primary);
    display: flex;
    align-items: center;
    justify-content: center;
    font-family: var(--font-title);
    font-size: 2em;
    font-weight: 700;
    color: #fff;
    box-shadow: 0 0 25px rgba(212, 170, 87,0.3);
}
.team-card h4 { font-family: var(--font-title); font-size: 1.1em; font-weight: 600; margin-bottom: 4px; }
.team-card .team-role { color: var(--cyan); font-size: 12px; font-weight: 600; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 10px; }
.team-card p { color: var(--text-light); font-size: 13px; line-height: 1.6; }

.stats-row { display: grid; grid-template-columns: repeat(4, 1fr); gap: 20px; margin: 50px 0; }
.stat-block { background: var(--bg-card); border: 1px solid var(--border); border-radius: 18px; padding: 26px 20px; text-align: center; }
.stat-block .stat-num {
    font-family: var(--font-title);
    font-size: 2.4em;
    font-weight: 700;
    background: var(--gradient-primary);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    line-height: 1.1;
}
.stat-block .stat-lbl { color: var(--text-light); font-size: 13px; font-weight: 500; margin-top: 6px; }

.quote-box {
    border-left: 4px solid var(--cyan);
    padding: 24px 32px;
    margin: 50px 0;
    background: rgba(212, 170, 87,0.03);
    border-radius: 0 16px 16px 0;
}
.quote-box p { font-family: var(--font-title); font-size: 1.3em; font-style: italic; color: #fff; line-height: 1.6; margin-bottom: 12px; }
.quote-box .quote-author { color: var(--cyan); font-size: 14px; font-weight: 600; }

@media (max-width: 1024px) {
    .values-grid { grid-template-columns: repeat(2, 1fr); }
    .stats-row { grid-template-columns: repeat(2, 1fr); }
    .feature-row { grid-template-columns: 1fr; gap: 24px; }
    .feature-row.reverse { direction: ltr; }
}
@media (max-width: 768px) {
    .section-title h2 { font-size: 1.8em; }
    .img-full { height: 240px; }
    .feature-img { height: 220px; }
    .values-grid { grid-template-columns: 1fr 1fr; }
    .stats-row { grid-template-columns: 1fr 1fr; }
    .mission-box { padding: 28px 20px; }
    .quote-box { padding: 20px 22px; }
    .goals-intro h2 { font-size: 1.8em; }
    .goals-quote { padding: 16px 20px; font-size: 0.95em; }
    .goals-grid { grid-template-columns: 1fr; gap: 16px; margin: 0 0 40px; }
    .goal-card { padding: 24px 20px; }
    .goal-icon { width: 36px; height: 36px; }
    .goal-title { font-size: 1.1em; }
    .past-section { padding: 24px 22px; margin: 30px 0; }
    .past-section h3 { font-size: 1.2em; }
    .past-section p { font-size: 14.5px; }
}
@media (max-width: 480px) {
    .values-grid { grid-template-columns: 1fr; }
    .stats-row { grid-template-columns: 1fr; }
}
';

    // CSS condiviso delle pagine informative + quello di questa sezione.

    $extraCSS = (string)($extraCSS ?? '') . $__info_css;


    lux_header();
    ?>


<main>
    <section class="info-hub">
        <?php lux_info_nav('chi-siamo'); ?>
    </section>


    <section class="hero">
        <div class="container">
            <div class="hero-badge">
                <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2L2 7l10 5 10-5-10-5z"></path><path d="M2 17l10 5 10-5"></path><path d="M2 12l10 5 10-5"></path></svg>
                <?= t('Our Story') ?>
            </div>
            <h1><?= t('About Us') ?></h1>
            <p><?= t("L.U.X. Verse is not just a website: it is the result of a simple but ambitious idea — that of making artificial intelligence a tool within everyone's reach.") ?></p>
        </div>
    </section>

    <section class="about-section">
        <div class="container">
            <div class="about-content">

                <img class="img-full" src="https://images.unsplash.com/photo-1522071820081-009f0129c71c?w=1600&q=80&auto=format&fit=crop" alt="<?= t('L.U.X. Verse team at work') ?>" loading="lazy">

                <div class="mission-box">
                    <span class="mission-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2l2.4 7.6L22 12l-7.6 2.4L12 22l-2.4-7.6L2 12l7.6-2.4z"></path></svg></span>
                    <h2><?= t('Our Mission') ?></h2>
                    <p><?= t('We believe technology should be democratic, accessible and at the service of people. That is why our mission is to create smart digital tools that simplify everyday life, without economic barriers or registration requirements. We want anyone, anywhere, to have access to professional tools able to improve their work, their study and their creativity.') ?></p>
                </div>

                <div class="past-section">
                    <h3>
                        <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>
                        <?= t('Where we come from') ?>
                    </h3>
                    <p><?= t('Before becoming') ?> <strong>L.U.X. Verse</strong><?= t(', there were only') ?> <em><?= t('sleepless nights') ?></em><?= t(', blank pages that refused to be filled, mistakes that seemed impossible to solve. There were days when the urge to give up was stronger than the urge to continue. Days when it seemed like') ?> <em><?= t('no one believed') ?></em><?= t('in this project.') ?></p>
                    <p><?= t('But then came the') ?> <strong><?= t('community') ?></strong><?= t('. You arrived. And with you came the strength to move forward, to improve, to build something truly worth it.') ?> <em><?= t('Every single user') ?></em><?= t('turned silence into energy, doubts into goals, loneliness into a team.') ?></p>
                    <p><?= t('Today we are no longer an isolated project.') ?> <strong><?= t('We are a living ecosystem') ?></strong><?= t(', and this is only the beginning.') ?></p>
                </div>

                <div class="goals-intro">
                    <h2><?= t('Our Goals') ?></h2>
                    <p><?= t('What we want to achieve, together with you') ?></p>

                    <div class="goals-quote">
                        <p><?= t("We don't care about being") ?> <strong><?= t('the biggest') ?></strong><?= t('. We care about being') ?> <strong><?= t('the closest') ?></strong><?= t('— to those who use us, to those who believe in us, to those who dream with us. These are not just numbers: they are') ?> <strong><?= t('promises') ?></strong><?= t('we make to you, every day.') ?></p>
                    </div>
                </div>

                <div class="goals-grid">

                    <div class="goal-card">
                        <span class="goal-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4.5 16.5c-1.5 1.26-2 5-2 5s3.74-.5 5-2c.71-.84.7-2.13-.09-2.91a2.18 2.18 0 0 0-2.91-.09z"></path><path d="M12 15l-3-3a22 22 0 0 1 2-3.95A12.88 12.88 0 0 1 22 2c0 2.72-.78 7.5-6 11a22.35 22.35 0 0 1-4 2z"></path><path d="M9 12H4s.55-3.03 2-4c1.62-1.08 5 0 5 0"></path><path d="M12 15v5s3.03-.55 4-2c1.08-1.62 0-5 0-5"></path></svg></span>
                        <div class="goal-title"><?= t('Reaching 100,000 tools') ?></div>
                        <div class="goal-desc"><?= t('From 25 to 100,000. Every new tool is a step towards an ever more complete ecosystem, able to respond to every need.') ?></div>
                    </div>

                    <div class="goal-card">
                        <span class="goal-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><line x1="2" y1="12" x2="22" y2="12"></line><path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"></path></svg></span>
                        <div class="goal-title"><?= t('Users 24/7, Every Day') ?></div>
                        <div class="goal-desc"><?= t('We want to be the digital home of people from all over the world, in every time zone. A community that never sleeps.') ?></div>
                    </div>

                    <div class="goal-card">
                        <span class="goal-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="23 6 13.5 15.5 8.5 10.5 1 18"></polyline><polyline points="17 6 23 6 23 12"></polyline></svg></span>
                        <div class="goal-title"><?= t('10,000 Visits') ?></div>
                        <div class="goal-desc"><?= t("It's not a number. It's the sum of 10,000 people who chose to trust us, even for a moment.") ?></div>
                    </div>

                    <div class="goal-card">
                        <span class="goal-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M23 21v-2a4 4 0 0 0-3-3.87"></path><path d="M16 3.13a4 4 0 0 1 0 7.75"></path></svg></span>
                        <div class="goal-title"><?= t('Growing Together with the Community') ?></div>
                        <div class="goal-desc"><?= t('No project grows alone. Every feedback, every criticism, every idea makes us better. You are part of L.U.X. Verse.') ?></div>
                    </div>

                    <div class="goal-card">
                        <span class="goal-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path></svg></span>
                        <div class="goal-title"><?= t('Flawless Security for Everyone') ?></div>
                        <div class="goal-desc"><?= t("We don't want to be just useful. We want to be a safe place, where your data is protected as if it were ours.") ?></div>
                    </div>

                    <div class="goal-card special">
                        <span class="goal-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m2 4 3 12h14l3-12-6 7-4-7-4 7-6-7zm3 16h14"></path></svg></span>
                        <div class="goal-title"><?= t('Buying a Real Domain') ?></div>
                        <div class="goal-desc"><?= t('The biggest dream. Saying goodbye to subdomains and having a name that is all our own:') ?> <strong><?= t('a symbol of maturity and future') ?></strong><?= t('. We will get there, together.') ?></div>
                    </div>

                </div>

                <div class="section-title">
                    <h2><?= t('Our Story') ?></h2>
                    <p><?= t('A journey that started with a simple idea and became a complete ecosystem') ?></p>
                </div>

                <div class="story-text">
                    <p><?= t('L.U.X. Verse was born in 2026 from the intuition of a young developer passionate about technology and artificial intelligence. The underlying idea was as simple as it was revolutionary:') ?> <strong><?= t('to create an ecosystem of free, accessible and professional digital tools') ?></strong><?= t(", able to respond to people's real needs. No registration, no subscription, no barriers. Only useful tools, ready to use.") ?></p>

                    <p><?= t('What started as a small personal project quickly turned into something bigger. In a few months,') ?> <em>L.U.X. Verse</em><?= t('expanded its offer to include over twenty-five different tools, designed to meet the most varied needs: from writing to translation, from creativity to productivity, from education to entertainment.') ?></p>

                    <p><?= t('Every project in the ecosystem was developed with painstaking attention to detail, always putting') ?> <strong><?= t('the user experience') ?></strong><?= t('at the center. We believed from the start that a polished, elegant and intuitive interface is itself an integral part of the service, and not a simple addition. For this reason every page, every feature and every interaction was designed to offer a smooth, pleasant and modern experience.') ?></p>

                    <p><?= t('Along the way we learned that') ?> <em><?= t('innovation is not just a matter of technology') ?></em><?= t(', but also of sensitivity: it means understanding people\'s needs, listening to their feedback and constantly improving. L.U.X. Verse is not a static project, but a living organism that grows every day thanks to the ideas, suggestions and reports of those who use it.') ?></p>

                    <p><?= t('Today L.U.X. Verse represents much more than a simple website: it is a') ?> <strong><?= t('complete digital ecosystem') ?></strong><?= t(', a point of reference for those who look for free, reliable and well-designed AI tools. But above all, it is the concrete proof that with passion, commitment and vision you can create extraordinary things, regardless of age or available resources.') ?></p>
                </div>

                <div class="cta-box">
                    <h3><?= t('La linea del tempo completa') ?></h3>
                    <p><?= t('Every milestone of the project, from the first idea to today, now has a page of its own: release after release, date after date.') ?></p>
                    <div class="cta-buttons">
                        <a href="/linea-del-tempo.php" class="btn-neon"><?= icon('time', 17) ?> <?= t('Apri la Linea del Tempo') ?></a>
                    </div>
                </div>

                <div class="section-title">
                    <h2><?= t('What We Do') ?></h2>
                    <p><?= t('Concrete tools for real needs') ?></p>
                </div>

                <div class="story-text">
                    <p><?= t('Inside L.U.X. Verse we offer a wide range of digital tools, divided into different categories for easy browsing. Every tool is designed to be immediately usable, without complex configurations or advanced technical knowledge.') ?></p>
                </div>

                <div class="feature-row">
                    <img class="feature-img" src="https://images.unsplash.com/photo-1552664730-d307ca884978?w=1200&q=80&auto=format&fit=crop" alt="<?= t('Productivity tools') ?>" loading="lazy">
                    <div class="feature-text">
                        <h3><?= t('Everyday productivity') ?></h3>
                        <p><?= t('Tools designed to make work and study more efficient: professional email generators, text summarizers, custom plan creators, day organizers and much more.') ?></p>
                        <p><?= t('Every feature is designed to save you time and get professional quality results in a few moments.') ?></p>
                    </div>
                </div>

                <div class="feature-row reverse">
                    <img class="feature-img" src="https://images.unsplash.com/photo-1513475382585-d06e58bcb0e0?w=1200&q=80&auto=format&fit=crop" alt="<?= t('Creative tools') ?>" loading="lazy">
                    <div class="feature-text">
                        <h3><?= t('Creativity without limits') ?></h3>
                        <p><?= t('From color palette generators to name creators, from mind maps to song lyrics: our creative tools fuel the imagination and help you turn your ideas into reality.') ?></p>
                        <p><?= t('Perfect for artists, designers, writers, musicians and anyone who wants to explore new frontiers of creativity.') ?></p>
                    </div>
                </div>

                <div class="feature-row">
                    <img class="feature-img" src="https://images.unsplash.com/photo-1503676260728-1c00da094a0b?w=1200&q=80&auto=format&fit=crop" alt="<?= t('Educational tools') ?>" loading="lazy">
                    <div class="feature-text">
                        <h3><?= t('Accessible education') ?></h3>
                        <p><?= t('We deeply believe in the value of education and in the development of quality educational tools. Our tools help students of every age study better, understand complex concepts and deepen their knowledge.') ?></p>
                        <p><?= t('Interactive quizzes, clear explanations and study support tools: learning becomes a pleasant and engaging experience.') ?></p>
                    </div>
                </div>

                <div class="section-title">
                    <h2><?= t('Our Values') ?></h2>
                    <p><?= t('The principles that guide every choice we make') ?></p>
                </div>

                <div class="values-grid">
                    <div class="value-card">
                        <span class="value-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4.5 16.5c-1.5 1.26-2 5-2 5s3.74-.5 5-2c.71-.84.7-2.13-.09-2.91a2.18 2.18 0 0 0-2.91-.09z"></path><path d="M12 15l-3-3a22 22 0 0 1 2-3.95A12.88 12.88 0 0 1 22 2c0 2.72-.78 7.5-6 11a22.35 22.35 0 0 1-4 2z"></path><path d="M9 12H4s.55-3.03 2-4c1.62-1.08 5 0 5 0"></path><path d="M12 15v5s3.03-.55 4-2c1.08-1.62 0-5 0-5"></path></svg></span>
                        <h4><?= t('Innovation') ?></h4>
                        <p><?= t('We constantly look for new ideas and new technologies to improve our tools.') ?></p>
                    </div>
                    <div class="value-card">
                        <span class="value-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><line x1="2" y1="12" x2="22" y2="12"></line><path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"></path></svg></span>
                        <h4><?= t('Accessibility') ?></h4>
                        <p><?= t('All our tools are free and require no registration. Technology must belong to everyone.') ?></p>
                    </div>
                    <div class="value-card">
                        <span class="value-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 3h12l4 6-10 13L2 9z"></path><path d="M11 3 8 9l4 13 4-13-3-6"></path><path d="M2 9h20"></path></svg></span>
                        <h4><?= t('Quality') ?></h4>
                        <p><?= t('We take care of every detail, from the interface to the performance, to offer a flawless experience.') ?></p>
                    </div>
                    <div class="value-card">
                        <span class="value-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect><path d="M7 11V7a5 5 0 0 1 10 0v4"></path></svg></span>
                        <h4><?= t('Privacy') ?></h4>
                        <p><?= t('We respect your data and your privacy. No invasive tracking, no selling of information.') ?></p>
                    </div>
                </div>

                <div class="quote-box">
                    <p><?= t('"There is no tool more powerful than the will to create something that can help others."') ?></p>
                    <div class="quote-author"><?= t('— L.U.X. Verse Development Team') ?></div>
                </div>

                <div class="stats-row">
                    <div class="stat-block">
                        <div class="stat-num">100.000</div>
                        <div class="stat-lbl"><?= t('Tools available') ?></div>
                    </div>
                    <div class="stat-block">
                        <div class="stat-num">100%</div>
                        <div class="stat-lbl"><?= t('Free forever') ?></div>
                    </div>
                    <div class="stat-block">
                        <div class="stat-num">0€</div>
                        <div class="stat-lbl"><?= t('No hidden costs') ?></div>
                    </div>
                    <div class="stat-block">
                        <div class="stat-num">24/7</div>
                        <div class="stat-lbl"><?= t('Always available') ?></div>
                    </div>
                </div>

                <div class="section-title">
                    <h2><?= t('Our Team') ?></h2>
                    <p><?= t('The people behind L.U.X. Verse') ?></p>
                </div>

                <div class="team-grid">
                    <div class="team-card">
                        <div class="team-avatar">Y</div>
                        <h4>Yeamin</h4>
                        <div class="team-role"><?= t('Founder & Lead Developer') ?></div>
                        <p><?= t('Creator of the project, main developer and responsible for the entire architecture of the ecosystem.') ?></p>
                    </div>
                    <div class="team-card">
                        <div class="team-avatar">L</div>
                        <h4>L.U.X. Verse Dev Team</h4>
                        <div class="team-role"><?= t('Design & Innovation') ?></div>
                        <p><?= t('The development team in charge of design, content, testing and continuous improvement of every project.') ?></p>
                    </div>
                    <div class="team-card">
                        <div class="team-avatar">C</div>
                        <h4><?= t('Community') ?></h4>
                        <div class="team-role"><?= t('Heart of the Ecosystem') ?></div>
                        <p><?= t('Every user who uses our tools, sends feedback and helps make L.U.X. Verse better every day.') ?></p>
                    </div>
                </div>

                <img class="img-full" src="https://images.unsplash.com/photo-1519389950473-47ba0277781c?w=1600&q=80&auto=format&fit=crop" alt="<?= t('L.U.X. Verse digital ecosystem') ?>" loading="lazy">

                <div class="cta-box">
                    <h3><?= t('Join L.U.X. Verse') ?></h3>
                    <p><?= t('Explore all our tools, discover new features and let technology work for you. No registration, no cost, no complications.') ?></p>
                    <div class="cta-buttons">
                        <a href="webapp.php" class="btn-primary">
                            <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="7" height="7"></rect><rect x="14" y="3" width="7" height="7"></rect><rect x="14" y="14" width="7" height="7"></rect><rect x="3" y="14" width="7" height="7"></rect></svg>
                            <?= t('Explore Projects') ?>
                        </a>
                        <a href="contattaci.php" class="btn-secondary">
                            <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path></svg>
                            <?= t('Contact Us') ?>
                        </a>
                    </div>
                </div>

            </div>
        </div>
    </section>


                <div class="lux-block">
                    <h2><?= t('Come nasce ogni strumento: il metodo di lavoro') ?></h2>
                    <p><?= t('Nessuno degli strumenti di L.U.X. Verse nasce per caso. Seguono tutti lo stesso percorso, pensato per un progetto fatto da una sola persona: capire il problema, costruire la cosa più semplice che lo risolve, verificarla e poi pubblicarla.') ?></p>
                    <div class="lux-steps">
                        <div class="lux-step">
                            <b>01</b>
                            <h3><?= t('Il problema') ?></h3>
                            <p><?= t('Si parte da una necessità concreta, spesso segnalata da chi usa il sito: un calcolo che serve, un testo da sistemare, una verifica da fare. Se non risolve un problema reale, lo strumento non si costruisce.') ?></p>
                        </div>
                        <div class="lux-step">
                            <b>02</b>
                            <h3><?= t('La forma più semplice') ?></h3>
                            <p><?= t('Si scrivono solo i campi indispensabili e si sceglie se calcolare sul posto (istantaneo) o generare con l\'AI. Meno passaggi ci sono, meno cose si possono rompere.') ?></p>
                        </div>
                        <div class="lux-step">
                            <b>03</b>
                            <h3><?= t('La verifica') ?></h3>
                            <p><?= t('Ogni rilascio passa dai controlli automatici e da prove vere: i moduli vengono compilati e inviati, le pagine aperte in italiano e in inglese, su schermo grande e su telefono.') ?></p>
                        </div>
                        <div class="lux-step">
                            <b>04</b>
                            <h3><?= t('La pubblicazione') ?></h3>
                            <p><?= t('Si pubblica solo quando tutto regge. Poi lo strumento si può migliorare ancora, partendo da quello che non funziona: il lavoro non finisce con il rilascio.') ?></p>
                        </div>
                    </div>
                </div>

                <div class="lux-block">
                    <h2><?= t('Il progetto in numeri reali') ?></h2>
                    <p><?= t('Questi numeri non sono stime: vengono letti dal sito ogni volta che apri la pagina. Strumenti dal catalogo, avvisi dalla bacheca, visite dal contatore e giorno di apertura dagli archivi.') ?></p>
                    <?php
                    // Numeri presi dai dati veri del sito, non scritti a mano.
                    $__catalog = getProjectsFromLocal();
                    // Il catalogo intero (scritti a mano + generati al volo):
                    // non i soli 1.000 storici, altrimenti la pagina si
                    // contraddice con la home e con la bacheca.
                    $__catalogTotal = function_exists('lux_catalog_total') ? lux_catalog_total() : count($__catalog);
                    $__notices = getNoticesFromLocal(0);
                    $__visits  = json_decode((string)@file_get_contents(__DIR__ . '/data/visits.json'), true);
                    $__visitsTotal = is_array($__visits) ? (int)($__visits['total'] ?? 0) : 0;
                    $__days = is_array($__visits) && !empty($__visits['daily']) ? count((array)$__visits['daily']) : 0;
                    $__first = '';
                    if (is_array($__visits) && !empty($__visits['daily'])) {
                        $__daysList = array_keys((array)$__visits['daily']);
                        sort($__daysList);
                        $__first = (string)reset($__daysList);
                    }
                    ?>
                    <div class="lux-facts">
                        <div class="lux-fact"><b><?= number_format($__catalogTotal, 0, ',', '.') ?></b><span><?= t('strumenti nel catalogo') ?></span></div>
                        <div class="lux-fact"><b><?= count($__notices) ?></b><span><?= t('avvisi in bacheca') ?></span></div>
                        <div class="lux-fact"><b><?= number_format($__visitsTotal, 0, ',', '.') ?></b><span><?= t('visite registrate') ?></span></div>
                        <div class="lux-fact"><b>2</b><span><?= t('lingue complete') ?></span></div>
                        <div class="lux-fact"><b><?= $__days > 0 ? $__days : '—' ?></b><span><?= t('giorni con visite') ?></span></div>
                    </div>
                    <?php if ($__first !== ''): ?>
                        <p style="margin-top:14px; font-size:13px; color:var(--text-muted);">
                            <?= t('La prima visita registrata è del') ?> <?= htmlspecialchars($__first) ?>.
                        </p>
                    <?php endif; ?>
                </div>

                <div class="lux-block">
                    <h2><?= t('La qualità non è una promessa: è un controllo') ?></h2>
                    <p><?= t('Su un progetto piccolo il rischio non è il cattivo gusto, è la distrazione: un errore di sintassi, una traduzione mancante, un modulo che si rompe solo su telefono. Per questo ogni modifica passa da controlli automatici prima di arrivare online.') ?></p>
                    <ul class="lux-list">
                        <li><?= t('<strong>Sintassi</strong>: tutti i file PHP vengono controllati uno per uno, senza eccezioni né avvisi soppressi.') ?></li>
                        <li><?= t('<strong>Pagine</strong>: ogni pagina pubblica viene aperta in italiano e in inglese, su schermo grande e su telefono, per intercettare errori, richieste fallite e testi fuori posto.') ?></li>
                        <li><?= t('<strong>Traduzioni</strong>: i controlli verificano che ogni frase usata nel codice abbia la sua versione italiana, così nessuna pagina resta a metà lingua.') ?></li>
                        <li><?= t('<strong>Prove reali</strong>: gli strumenti istantanei vengono compilati e inviati come farebbe una persona, e il risultato deve comparire davvero.') ?></li>
                        <li><?= t('<strong>Coerenza</strong>: zero emoji nel testo, icone vettoriali, un solo stile visivo in tutto l\'ecosistema.') ?></li>
                    </ul>
                    <p style="margin-top:14px;"><?= t('Quando qualcosa non passa i controlli, non si pubblica: si corregge. È più lento, ma è l\'unico modo per fidarsi di quello che si mette online.') ?></p>
                </div>
</main>


    <?php
    lux_footer();
}


// ============================================================
// SEZIONE: privacy   (prima era il file privacy.php)
// ============================================================
if ($__info === 'privacy') {

lux_session_start();
$isAdmin = isAdminLoggedIn();
$pageTitle       = t('Privacy Policy - L.U.X. Verse');
$pageDescription = t('L.U.X. Verse Privacy Policy: how we collect, use and protect your personal data in accordance with the GDPR. Non-profit project, no tracking cookies.');
$pageKeywords    = 'privacy, L.U.X., personal data, GDPR, cookie, security, policy, non profit';

$extraCSS = '
.privacy-section { padding: 40px 0 80px; }
.privacy-content { max-width: 900px; margin: 0 auto; }

.hero-updated {
    display: inline-block;
    margin-top: 20px;
    padding: 6px 14px;
    background: rgba(255,255,255,0.03);
    border: 1px solid var(--border);
    border-radius: 20px;
    font-size: 12px;
    color: var(--text-muted);
    font-weight: 500;
}

.intro-box {
    background: rgba(212, 170, 87,0.05);
    border: 1px solid rgba(212, 170, 87,0.15);
    border-left: 4px solid var(--cyan);
    border-radius: 16px;
    padding: 24px 28px;
    margin-bottom: 40px;
}
.intro-box strong {
    color: var(--cyan);
    display: block;
    font-size: 1.05em;
    margin-bottom: 8px;
}
.intro-box p {
    color: var(--text-light);
    font-size: 15px;
    line-height: 1.8;
    margin: 0;
}

.toc {
    background: var(--bg-card);
    border: 1px solid var(--border);
    border-radius: 20px;
    padding: 28px 32px;
    margin-bottom: 50px;
}
.toc h3 {
    font-family: var(--font-title);
    font-size: 1.3em;
    font-weight: 700;
    color: var(--cyan);
    margin-bottom: 18px;
    display: flex;
    align-items: center;
    gap: 10px;
}
.toc-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 6px 24px;
}
.toc a {
    color: var(--text-light);
    text-decoration: none;
    font-size: 14px;
    padding: 6px 0;
    transition: color 0.2s, padding-left 0.2s;
    display: block;
}
.toc a:hover {
    color: var(--cyan);
    padding-left: 6px;
}

.privacy-content h2 {
    font-family: var(--font-title);
    font-size: 1.8em;
    font-weight: 700;
    color: var(--cyan);
    margin: 50px 0 20px;
    padding-bottom: 12px;
    border-bottom: 1px solid var(--border);
    scroll-margin-top: 90px;
}
.privacy-content h3 {
    font-family: var(--font-title);
    font-size: 1.25em;
    font-weight: 600;
    color: var(--purple);
    margin: 30px 0 12px;
}
.privacy-content h4 {
    font-family: var(--font-title);
    font-size: 1.05em;
    font-weight: 600;
    color: var(--pink);
    margin: 22px 0 10px;
}
.privacy-content p {
    color: var(--text-light);
    font-size: 15.5px;
    line-height: 1.9;
    margin-bottom: 16px;
}
.privacy-content strong { color: #fff; font-weight: 600; }
.privacy-content a {
    color: var(--cyan);
    text-decoration: none;
    border-bottom: 1px dotted rgba(212, 170, 87,0.4);
    transition: border-color 0.2s;
}
.privacy-content a:hover { border-bottom-color: var(--cyan); }
.privacy-content ul, .privacy-content ol {
    color: var(--text-light);
    margin: 16px 0 16px 24px;
    font-size: 15.5px;
}
.privacy-content li {
    margin-bottom: 10px;
    line-height: 1.8;
}

.box {
    background: var(--bg-card);
    border: 1px solid var(--border);
    border-radius: 14px;
    padding: 20px 24px;
    margin: 20px 0;
}
.box.info { border-left: 4px solid var(--cyan); }
.box.warning { border-left: 4px solid var(--orange); }
.box.success { border-left: 4px solid var(--green); }
.box.danger { border-left: 4px solid var(--pink); }
.box p:last-child { margin-bottom: 0; }
.box strong {
    display: block;
    margin-bottom: 6px;
    font-size: 15px;
}
.box.info strong { color: var(--cyan); }
.box.warning strong { color: var(--orange); }
.box.success strong { color: var(--green); }
.box.danger strong { color: var(--pink); }

.privacy-content table {
    width: 100%;
    border-collapse: collapse;
    margin: 20px 0;
    font-size: 14px;
    background: var(--bg-card);
    border-radius: 12px;
    overflow: hidden;
}
.privacy-content table th {
    background: rgba(212, 170, 87,0.08);
    padding: 14px 16px;
    text-align: left;
    color: var(--cyan);
    border-bottom: 1px solid var(--border);
    font-weight: 600;
    font-size: 12px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}
.privacy-content table td {
    padding: 14px 16px;
    border-bottom: 1px solid var(--border);
    color: var(--text-light);
    vertical-align: top;
}
.privacy-content table tr:last-child td { border-bottom: none; }

.back-to-top {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    color: var(--text-muted);
    text-decoration: none;
    font-size: 13px;
    padding: 8px 14px;
    background: rgba(255,255,255,0.03);
    border: 1px solid var(--border);
    border-radius: 10px;
    transition: color 0.2s, border-color 0.2s;
    margin-top: 40px;
}
.back-to-top:hover {
    color: var(--cyan);
    border-color: var(--cyan);
}

.footer-trust {
    display: flex;
    justify-content: center;
    align-items: center;
    flex-wrap: wrap;
    gap: 10px;
    padding: 20px 0;
    margin-top: 20px;
    border-top: 1px solid var(--border);
    color: var(--text-light);
    font-size: 13px;
    font-weight: 500;
}
.footer-trust .sep { color: var(--text-muted); opacity: 0.5; }

@media (max-width: 1024px) {
    .privacy-content h2 { font-size: 1.6em; }
}
@media (max-width: 768px) {
    .privacy-content h2 { font-size: 1.5em; }
    .privacy-content h3 { font-size: 1.15em; }
    .toc-grid { grid-template-columns: 1fr; }
    .privacy-content table { font-size: 13px; display: block; overflow-x: auto; }
    .privacy-content table th, .privacy-content table td { padding: 10px 12px; }
    .cta-box { padding: 32px 20px; }
    .cta-box h3 { font-size: 1.4em; }
}
@media (max-width: 480px) {
    .privacy-content p { font-size: 15px; }
    .toc { padding: 20px; }
    .footer-trust { font-size: 12px; gap: 8px; }
    .footer-trust .sep { display: none; }
}
';

    // CSS condiviso delle pagine informative + quello di questa sezione.

    $extraCSS = (string)($extraCSS ?? '') . $__info_css;


    lux_header();
    ?>


<main>
    <section class="info-hub">
        <?php lux_info_nav('privacy'); ?>
    </section>


    <!-- Hero -->
    <section class="hero">
        <div class="container">
            <div class="hero-badge">
                <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect><path d="M7 11V7a5 5 0 0 1 10 0v4"></path></svg>
                <?= t('Privacy') ?>
            </div>
            <h1><?= t('Privacy Policy') ?></h1>
            <p><?= t('How we collect, use, store and protect your personal data, in accordance with the General Data Protection Regulation (GDPR).') ?></p>
            <span class="hero-updated"><?= t('Last updated: 13 September 2026') ?></span>
        </div>
    </section>

    <!-- Privacy content -->
    <section class="privacy-section">
        <div class="container">
            <div class="privacy-content">

                <div class="intro-box">
                    <strong><?= t('In short:') ?></strong>
                    <p><?= t('Your privacy is important to us.') ?> <strong><?= t('We do not sell your data, we do not use invasive tracking cookies and we do not share information with third parties.') ?></strong> <?= t('L.U.X. Verse is a non-profit project created by a 14-year-old boy, without VAT number and without commercial purposes. This policy explains in detail, transparently, how we manage your personal data.') ?></p>
                </div>

                <div class="toc">
                    <h3>
                        <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 6h16M4 12h16M4 18h16"></path></svg>
                        <?= t('Table of contents') ?>
                    </h3>
                    <div class="toc-grid">
                        <a href="#s1"><?= t('1. Introduction') ?></a>
                        <a href="#s2"><?= t('2. Data Controller') ?></a>
                        <a href="#s3"><?= t('3. Definitions') ?></a>
                        <a href="#s4"><?= t('4. Personal Data Collected') ?></a>
                        <a href="#s5"><?= t('5. Purposes of Processing') ?></a>
                        <a href="#s6"><?= t('6. Legal Basis of Processing') ?></a>
                        <a href="#s7"><?= t('7. Processing Methods') ?></a>
                        <a href="#s8"><?= t('8. Data Retention') ?></a>
                        <a href="#s9"><?= t('9. Data Sharing') ?></a>
                        <a href="#s10"><?= t('10. International Transfers') ?></a>
                        <a href="#s11"><?= t('11. Rights of the Data Subject') ?></a>
                        <a href="#s12"><?= t('12. Right to Lodge a Complaint') ?></a>
                        <a href="#s13"><?= t('13. Data Security') ?></a>
                        <a href="#s14"><?= t('14. Cookie Policy') ?></a>
                        <a href="#s15"><?= t('15. Minors and Transparency') ?></a>
                        <a href="#s16"><?= t('16. Nature of the Project') ?></a>
                        <a href="#s17"><?= t('17. Changes to this Privacy Policy') ?></a>
                        <a href="#s18">18. 100.000 strumenti AI / 100,000 AI tools</a>
                        <a href="#s19">19. IA e fornitori esterni / AI and external providers</a>
                        <a href="#s20">20. L.U.X. GUIDE e assistenza / Guide and support</a>
                        <a href="#s21">21. Feedback e voti / Feedback and votes</a>
                        <a href="#s22">22. Preferiti e profilo locale / Local profile</a>
                        <a href="#s23">23. Statistiche e abusi / Statistics and abuse</a>
                        <a href="#s24">24. Tempi di conservazione / Retention</a>
                        <a href="#s25">25. Sicurezza / Security</a>
                        <a href="#s26">26. Versione 39.0.0 e aggiornamenti / Version 39.0.0</a>
                        <a href="#s27"><?= t('27. Contact') ?></a>
                    </div>
                </div>

                <h2 id="s1"><?= t('1. Introduction') ?></h2>
                <p><?= t('Welcome to') ?> <strong>L.U.X. Verse</strong> <?= t('(hereinafter also "the Site", "we", "our" or "the Controller"). This Privacy Policy describes in a detailed, transparent and complete way how we collect, use, store, protect and, in some cases, share the personal information of users who access our website and use our services.') ?></p>
                <p><?= t('We are committed to protecting your privacy and processing your personal data in accordance with the') ?> <strong><?= t('General Data Protection Regulation (GDPR)') ?></strong> <?= t('- EU Regulation 2016/679 - and with the') ?> <strong><?= t('Italian Privacy Code') ?></strong> <?= t('(Legislative Decree 196/2003 and subsequent amendments and additions).') ?></p>
                <p><?= t('We invite you to read this Privacy Policy carefully before using our services. By using the Site, you accept the practices described in this document.') ?></p>

                <h2 id="s2"><?= t('2. Data Controller') ?></h2>
                <p><?= t('The Data Controller of personal data is:') ?></p>
                <div class="box info">
                    <strong>MIAH MOHAMMAD YEAMIN</strong>
                    <p><strong><?= t('Data Controller:') ?></strong> Miah Mohammad Yeamin</p>
                    <p><strong><?= t('Age:') ?></strong> <?= t('14 years old') ?></p>
                    <p><strong><?= t('VAT number:') ?></strong> <?= t('not owned (as a minor)') ?></p>
                    <p><strong><?= t('Project nature:') ?></strong> <?= t('Personal non-profit project, without commercial purposes') ?></p>
                    <p><strong><?= t('Website:') ?></strong> <a href="https://luxverse.c22.eu" target="_blank" rel="noopener">https://luxverse.c22.eu</a></p>
                    <p><strong><?= t('Email:') ?></strong> <a href="mailto:administration.luxverse@gmail.com">administration.luxverse@gmail.com</a></p>
                    <p><strong><?= t('Support:') ?></strong> <a href="assistenza.php">luxverse.c22.eu/assistenza.php</a></p>
                    <p><strong><?= t('Contacts:') ?></strong> <a href="contattaci.php">luxverse.c22.eu/contattaci.php</a></p>
                </div>
                <p><?= t('For any communication regarding the processing of personal data, we invite you to use one of the following channels: the') ?> <a href="assistenza.php"><?= t('Support') ?></a> <?= t('page, the') ?> <a href="contattaci.php"><?= t('Contacts') ?></a> <?= t('page, or directly the email address') ?> <a href="mailto:administration.luxverse@gmail.com">administration.luxverse@gmail.com</a>.</p>

                <h2 id="s3"><?= t('3. Definitions') ?></h2>
                <p><?= t('For the purposes of this Privacy Policy, the following definitions apply:') ?></p>
                <ul>
                    <li><strong><?= t('Personal data:') ?></strong> <?= t('any information relating to an identified or identifiable natural person ("data subject").') ?></li>
                    <li><strong><?= t('Processing:') ?></strong> <?= t('any operation or set of operations performed with or without the aid of automated processes and applied to personal data.') ?></li>
                    <li><strong><?= t('Data subject:') ?></strong> <?= t('the natural person to whom the personal data refer.') ?></li>
                    <li><strong><?= t('Consent:') ?></strong> <?= t('any freely given, specific, informed and unambiguous manifestation of will by the data subject.') ?></li>
                    <li><strong><?= t('Processor:') ?></strong> <?= t('the natural or legal person who processes personal data on behalf of the controller.') ?></li>
                    <li><strong><?= t('Disclosure:') ?></strong> <?= t('the act of making personal data known to one or more specific subjects other than the data subject.') ?></li>
                    <li><strong><?= t('Dissemination:') ?></strong> <?= t('the act of making personal data known to indefinite subjects.') ?></li>
                </ul>

                <h2 id="s4"><?= t('4. Personal Data Collected') ?></h2>
                <h3><?= t('4.1 Data provided directly by the user') ?></h3>
                <p><?= t('When you use our services, we may collect the following personal data that you provide voluntarily:') ?></p>
                <table>
                    <thead>
                        <tr>
                            <th><?= t('Data') ?></th>
                            <th><?= t('When it is collected') ?></th>
                            <th><?= t('Purpose') ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><?= t('Name') ?></td>
                            <td><?= t('When you send a message or an assistance request') ?></td>
                            <td><?= t('Identify you and reply to you in a personalized way') ?></td>
                        </tr>
                        <tr>
                            <td><?= t('Email') ?></td>
                            <td><?= t('When you send a message or an assistance request') ?></td>
                            <td><?= t('Reply to your requests') ?></td>
                        </tr>
                        <tr>
                            <td><?= t('Message content') ?></td>
                            <td><?= t('When you send a request') ?></td>
                            <td><?= t('Manage and resolve your request') ?></td>
                        </tr>
                        <tr>
                            <td><?= t('Selected project') ?></td>
                            <td><?= t('When you send a contact request') ?></td>
                            <td><?= t('Route the request to the competent team') ?></td>
                        </tr>
                        <tr>
                            <td><?= t('Digital signature') ?></td>
                            <td><?= t('When you choose to ignore the Terms of Service') ?></td>
                            <td><?= t('Legally protect both parties') ?></td>
                        </tr>
                    </tbody>
                </table>

                <h3><?= t('4.2 Data collected automatically') ?></h3>
                <p><?= t('While browsing the Site, we may automatically collect the following technical data:') ?></p>
                <table>
                    <thead>
                        <tr>
                            <th><?= t('Data') ?></th>
                            <th><?= t('How it is collected') ?></th>
                            <th><?= t('Purpose') ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><?= t('IP address') ?></td>
                            <td><?= t('Automatically, upon access') ?></td>
                            <td><?= t('Security, prevention of abuse and fraud') ?></td>
                        </tr>
                        <tr>
                            <td>User Agent</td>
                            <td><?= t('Automatically, upon access') ?></td>
                            <td><?= t('User experience optimization') ?></td>
                        </tr>
                        <tr>
                            <td><?= t('Date and time of access') ?></td>
                            <td><?= t('Automatically, upon access') ?></td>
                            <td><?= t('Statistical analysis and security') ?></td>
                        </tr>
                        <tr>
                            <td><?= t('Referrer URL') ?></td>
                            <td><?= t('Automatically, upon access') ?></td>
                            <td><?= t('Internal statistical analysis') ?></td>
                        </tr>
                    </tbody>
                </table>

                <h3><?= t('4.3 Data NOT collected') ?></h3>
                <p><?= t('We want to be clear:') ?> <strong><?= t('we do not collect') ?></strong> <?= t('the following types of data:') ?></p>
                <ul>
                    <li><?= t('Sensitive data (health, sexual orientation, religious beliefs, etc.)') ?></li>
                    <li><?= t('Biometric data') ?></li>
                    <li><?= t('Genetic data') ?></li>
                    <li><?= t('Data relating to criminal convictions or offences') ?></li>
                    <li><?= t('Credit card data or bank details (as there is no payment system)') ?></li>
                    <li><?= t('Content of the texts you enter in the AI tools (they are processed in real time and not stored)') ?></li>
                </ul>

                <h2 id="s5"><?= t('5. Purposes of Processing') ?></h2>
                <p><?= t('Your personal data are processed exclusively for the following purposes:') ?></p>
                <ol>
                    <li><strong><?= t('Service delivery:') ?></strong> <?= t('provide the tools and features you request.') ?></li>
                    <li><strong><?= t('Request management:') ?></strong> <?= t('reply to your messages, assistance requests and tickets.') ?></li>
                    <li><strong><?= t('Site security:') ?></strong> <?= t('prevent and counter unlawful activity, abuse, spam and cyber attacks.') ?></li>
                    <li><strong><?= t('Service improvement:') ?></strong> <?= t('analyze the use of the Site to improve its performance.') ?></li>
                    <li><strong><?= t('Legal obligations:') ?></strong> <?= t('comply with any legal obligations.') ?></li>
                    <li><strong><?= t('Legal protection:') ?></strong> <?= t('defend our rights in case of disputes.') ?></li>
                </ol>

                <h2 id="s6"><?= t('6. Legal Basis of Processing') ?></h2>
                <p><?= t('The processing of your personal data is based on the following legal bases provided for in Article 6 of the GDPR:') ?></p>
                <table>
                    <thead>
                        <tr>
                            <th><?= t('Purpose') ?></th>
                            <th><?= t('Legal Basis') ?></th>
                            <th><?= t('GDPR Article') ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><?= t('Service delivery') ?></td>
                            <td><?= t('Performance of a contract') ?></td>
                            <td>Art. 6(1)(b)</td>
                        </tr>
                        <tr>
                            <td><?= t('Request management') ?></td>
                            <td><?= t('Performance of a contract') ?></td>
                            <td>Art. 6(1)(b)</td>
                        </tr>
                        <tr>
                            <td><?= t('Site security') ?></td>
                            <td><?= t('Legitimate interest of the Controller') ?></td>
                            <td>Art. 6(1)(f)</td>
                        </tr>
                        <tr>
                            <td><?= t('Service improvement') ?></td>
                            <td><?= t('Legitimate interest of the Controller') ?></td>
                            <td>Art. 6(1)(f)</td>
                        </tr>
                        <tr>
                            <td><?= t('Legal obligations') ?></td>
                            <td><?= t('Legal obligation') ?></td>
                            <td>Art. 6(1)(c)</td>
                        </tr>
                    </tbody>
                </table>

                <h2 id="s7"><?= t('7. Processing Methods') ?></h2>
                <p><?= t('The processing of personal data takes place in compliance with the principles of lawfulness, fairness and transparency, and is based on the following criteria:') ?></p>
                <ul>
                    <li><strong><?= t('Purpose limitation:') ?></strong> <?= t('we process data only for the stated purposes.') ?></li>
                    <li><strong><?= t('Minimization:') ?></strong> <?= t('we collect only strictly necessary data.') ?></li>
                    <li><strong><?= t('Accuracy:') ?></strong> <?= t('we are committed to keeping data accurate and up to date.') ?></li>
                    <li><strong><?= t('Storage limitation:') ?></strong> <?= t('we keep data only for the time necessary.') ?></li>
                    <li><strong><?= t('Integrity and confidentiality:') ?></strong> <?= t('we adopt adequate security measures.') ?></li>
                </ul>
                <p><?= t('Data are processed both in paper and electronic format, with the aid of computer and telematic tools.') ?></p>

                <h2 id="s8"><?= t('8. Data Retention') ?></h2>
                <p><?= t('Your personal data are kept for the time strictly necessary to achieve the purposes for which they were collected:') ?></p>
                <table>
                    <thead>
                        <tr>
                            <th><?= t('Type of data') ?></th>
                            <th><?= t('Retention period') ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><?= t('Contact data') ?></td>
                            <td><?= t('12 months from the date of the request') ?></td>
                        </tr>
                        <tr>
                            <td><?= t('Assistance messages') ?></td>
                            <td><?= t('24 months from the date of the request') ?></td>
                        </tr>
                        <tr>
                            <td><?= t('Access logs') ?></td>
                            <td><?= t('6 months from access') ?></td>
                        </tr>
                        <tr>
                            <td><?= t('Digital signatures') ?></td>
                            <td><?= t('24 months from the signature') ?></td>
                        </tr>
                        <tr>
                            <td><?= t('Browsing data') ?></td>
                            <td><?= t('12 months from access') ?></td>
                        </tr>
                    </tbody>
                </table>
                <p><?= t('At the end of these periods, the data are permanently deleted, unless legal obligations require longer retention.') ?></p>

                <h2 id="s9"><?= t('9. Data Sharing') ?></h2>
                <p><?= t('Your personal data') ?> <strong><?= t('are not sold, rented, exchanged or communicated to third parties') ?></strong> <?= t('for marketing or commercial purposes.') ?></p>
                <p><?= t('The data may be shared exclusively with:') ?></p>
                <ul>
                    <li><strong><?= t('Public authorities:') ?></strong> <?= t('when required by law or in response to formal legal requests.') ?></li>
                    <li><strong><?= t('Technical service providers:') ?></strong> <?= t('for the proper functioning of the Site (for example the hosting provider), bound by confidentiality agreements.') ?></li>
                </ul>
                <div class="box success">
                    <strong><?= t('Guarantee') ?></strong>
                    <p><?= t('No personal information is transferred, sold or shared with third parties for commercial purposes. Your trust is our most precious asset.') ?></p>
                </div>

                <h2 id="s10"><?= t('10. International Transfers') ?></h2>
                <p><?= t('Your personal data are processed and stored within the') ?> <strong><?= t('European Economic Area (EEA)') ?></strong> <?= t('. We do not transfer personal data to third countries outside the EEA.') ?></p>
                <p><?= t('Should international transfers become necessary in the future, we will commit to ensuring an adequate level of data protection, using the standard contractual clauses approved by the European Commission.') ?></p>

                <h2 id="s11"><?= t('11. Rights of the Data Subject') ?></h2>
                <p><?= t('As a data subject, you have the right to exercise at any time the following rights provided for by the GDPR:') ?></p>
                <ul>
                    <li><strong><?= t('Right of access (Art. 15):') ?></strong> <?= t('obtain confirmation of the processing of your data and receive a copy of it.') ?></li>
                    <li><strong><?= t('Right to rectification (Art. 16):') ?></strong> <?= t('obtain the correction of inaccurate data or the completion of incomplete ones.') ?></li>
                    <li><strong><?= t('Right to erasure (Art. 17):') ?></strong> <?= t('obtain the deletion of your data ("right to be forgotten").') ?></li>
                    <li><strong><?= t('Right to restriction (Art. 18):') ?></strong> <?= t('restrict the processing of your data in certain cases.') ?></li>
                    <li><strong><?= t('Right to data portability (Art. 20):') ?></strong> <?= t('receive your data in a structured and machine-readable format.') ?></li>
                    <li><strong><?= t('Right to object (Art. 21):') ?></strong> <?= t('object at any time to the processing of your data.') ?></li>
                    <li><strong><?= t('Right to withdraw consent:') ?></strong> <?= t('withdraw consent at any time, without affecting the lawfulness of processing based on consent before its withdrawal.') ?></li>
                </ul>
                <p><?= t('To exercise one or more of these rights, you can contact us via email') ?> <a href="mailto:administration.luxverse@gmail.com">administration.luxverse@gmail.com</a><?= t(', the') ?> <a href="contattaci.php"><?= t('Contacts') ?></a> <?= t('page or the') ?> <a href="assistenza.php"><?= t('Support') ?></a> <?= t('page. We will respond to your request within 30 days.') ?></p>

                <h2 id="s12"><?= t('12. Right to Lodge a Complaint') ?></h2>
                <p><?= t('If you believe that the processing of your personal data violates the provisions of the GDPR, you have the right to lodge a complaint with the competent supervisory authority:') ?></p>
                <div class="box info">
                    <strong><?= t('Italian Data Protection Authority') ?></strong>
                    <p><strong><?= t('Website:') ?></strong> <a href="https://www.garanteprivacy.it" target="_blank" rel="noopener">www.garanteprivacy.it</a></p>
                    <p><strong><?= t('Address:') ?></strong> <?= t('Piazza Venezia 11, 00187 Rome (Italy)') ?></p>
                </div>

                <h2 id="s13"><?= t('13. Data Security') ?></h2>
                <p><?= t('We adopt adequate technical and organizational security measures to protect your personal data from unauthorized access, loss, destruction or alteration. In particular:') ?></p>
                <ul>
                    <li><strong><?= t('Encrypted communications:') ?></strong> <?= t('all communications with the Site take place via HTTPS protocol.') ?></li>
                    <li><strong><?= t('Access control:') ?></strong> <?= t('access to data is restricted to the Controller only.') ?></li>
                    <li><strong><?= t('Continuous monitoring:') ?></strong> <?= t('logging and monitoring system for suspicious activity.') ?></li>
                    <li><strong><?= t('Regular backups:') ?></strong> <?= t('periodic backup copies of the data.') ?></li>
                    <li><strong><?= t('Firewalls and protections:') ?></strong> <?= t('protection systems against cyber attacks.') ?></li>
                </ul>
                <div class="box warning">
                    <strong><?= t('Important note') ?></strong>
                    <p><?= t('Despite the security measures adopted, no system is completely immune to risks. We invite you to never share sensitive personal data through our contact forms.') ?></p>
                </div>

                <h2 id="s14"><?= t('14. Cookie Policy') ?></h2>
                <p><?= t('Cookies are small text files stored on your device when you visit a website. Our Site uses exclusively technical cookies, essential for proper functioning:') ?></p>
                <table>
                    <thead>
                        <tr>
                            <th><?= t('Type') ?></th>
                            <th><?= t('Name') ?></th>
                            <th><?= t('Duration') ?></th>
                            <th><?= t('Purpose') ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><?= t('Technical cookie') ?></td>
                            <td>PHPSESSID</td>
                            <td><?= t('Session') ?></td>
                            <td><?= t('User session management') ?></td>
                        </tr>
                        <tr>
                            <td><?= t('Preferences') ?></td>
                            <td>workme_consent</td>
                            <td><?= t('1 year') ?></td>
                            <td><?= t('Remembers the privacy consent') ?></td>
                        </tr>
                    </tbody>
                </table>
                <p><strong><?= t('We do not use') ?></strong> <?= t('profiling cookies, tracking cookies, third-party analytical cookies or advertising cookies.') ?> <strong><?= t('We do not even use Google Analytics, Facebook Pixel or other tracking systems.') ?></strong></p>

                <h2 id="s15"><?= t('15. Minors and Transparency') ?></h2>
                <p><?= t('L.U.X. Verse is a project developed by') ?> <strong>Miah Mohammad Yeamin</strong><?= t(', a') ?> <strong><?= t('14-year-old') ?></strong> <?= t('technology and artificial intelligence enthusiast. The project started as a personal initiative, without profit-making purpose, with the goal of offering free and accessible digital tools to everyone.') ?></p>
                <p><?= t('Precisely because of the young age of the founder,') ?> <strong><?= t('the Controller does not hold a VAT number') ?></strong> <?= t('and does not carry out any commercial activity. The project does not sell services, does not require payments and does not generate any income.') ?></p>
                <p><?= t('Furthermore, the domain used by the site is not registered in the name of the Controller:') ?> <?= t('the site is reachable at') ?> <strong><em>luxverse.c22.eu</em></strong><?= t(', a domain provided to the project as part of the hosting service, chosen after the move from the previous address (the reasons are explained on the notice board).') ?></p>
                <p><?= t('This project has no commercial purposes, does not sell any service, does not require registration and has no payment system. It is a non-profit project created out of passion.') ?></p>
                <p><?= t("If you are a parent or guardian and believe there are issues regarding the processing of a minor's data, we invite you to contact us via email") ?> <a href="mailto:administration.luxverse@gmail.com">administration.luxverse@gmail.com</a><?= t(', the') ?> <a href="contattaci.php"><?= t('Contacts') ?></a> <?= t('page or the') ?> <a href="assistenza.php"><?= t('Support') ?></a> <?= t('page.') ?></p>

                <h2 id="s16"><?= t('16. Nature of the Project') ?></h2>
                <p><?= t('L.U.X. Verse is a') ?> <strong><?= t('non-profit') ?></strong> <?= t('project. Specifically:') ?></p>
                <ul>
                    <li><strong><?= t('Non-Profit:') ?></strong> <?= t('it generates no earnings, sells no services or data.') ?></li>
                    <li><strong><?= t('No Registration:') ?></strong> <?= t('most of the tools do not require an account.') ?></li>
                    <li><strong><?= t('No Tracking Cookies:') ?></strong> <?= t('we do not use cookies for profiling or marketing.') ?></li>
                    <li><strong><?= t('No Advertising:') ?></strong> <?= t('we do not show advertising banners or sponsored links.') ?></li>
                    <li><strong><?= t('No VAT Number:') ?></strong> <?= t('the Controller is a minor and does not hold a VAT number.') ?></li>
                    <li><strong><?= t('100% Free:') ?></strong> <?= t('all tools are and will remain free.') ?></li>
                    <li><strong><?= t('Hosting:') ?></strong> <?= t('the project is hosted on a free hosting service, under the address luxverse.c22.eu, for the reasons indicated in section 15.') ?></li>
                </ul>

                <h2 id="s17"><?= t('17. Changes to this Privacy Policy') ?></h2>
                <p><?= t('We reserve the right to modify this Privacy Policy at any time, to reflect changes in our practices or to comply with new regulations.') ?></p>
                <p><?= t('In case of substantial changes, we will inform you through a clearly visible notice on the Site. We invite you to check this page periodically to stay up to date.') ?></p>
                <p><?= t('The date of the last update is indicated at the top of this document.') ?></p>

                <h2 id="s18">18. 100.000 strumenti AI e catalogo generato / 100,000 AI tools and the generated catalog</h2>
                <p>L.U.X. Verse mette a disposizione un catalogo di <strong>100.000 strumenti</strong>. 99.000 di questi sono <em>generati</em>: non esistono come file separati, ma vengono calcolati al momento combinando un'azione, un argomento e un taglio del risultato. Per questo motivo non conserviamo una scheda dedicata per ogni strumento e non registriamo <strong>quale singolo strumento</strong> hai usato: viene contato solo in forma <strong>aggregata e anonima</strong>, per capire quali progetti sono più utili all'intera community.</p>
                <p><em>L.U.X. Verse offers a catalog of 100,000 tools. 99,000 of them are generated: they are not separate files but are computed at request time by combining an action, a topic and an output style. For this reason we do not keep a dedicated record for each tool and we do not log which single tool you used: it is counted only in an aggregated, anonymous form, to understand which projects help the whole community.</em></p>

                <h2 id="s19">19. Trattamento tramite intelligenza artificiale e fornitori esterni / AI processing and external providers</h2>
                <p>Quando usi uno strumento, il testo che scrivi viene inviato al fornitore del modello di intelligenza artificiale (oggi <strong>Groq</strong>) al solo scopo di generare la risposta che ti serve. Non autorizziamo il fornitore a usare i tuoi contenuti per scopi diversi. Ti invitiamo a <strong>non inserire dati personali, sanitari, finanziari o di terzi</strong> nei campi degli strumenti. Tutte le chiamate avvengono su connessione cifrata. Per distribuire il carico usiamo un gruppo di <strong>quattro chiavi dedicate</strong>: se una raggiunge il limite, la richiesta passa automaticamente a un'altra, senza che tu debba fare nulla.</p>
                <p><em>When you use a tool, the text you write is sent to the artificial-intelligence provider (today Groq) only to generate the answer you need. We do not authorise the provider to use your content for other purposes. Please do not enter personal, health, financial or third-party data in the tool fields. All calls use an encrypted connection. To balance the load we use a group of four dedicated keys: if one reaches its limit, the request automatically moves to another.</em></p>

                <h2 id="s20">20. La guida L.U.X. GUIDE e il trasferimento all'assistenza / L.U.X. GUIDE and transfer to support</h2>
                <p>La guida <strong>L.U.X. GUIDE</strong> funziona a opzioni: alla prima apertura chiede in quale lingua procedere, poi mostra dei pulsanti. Se la guida non capisce una richiesta, la conversazione viene trasferita a una persona dello staff e diventa visibile nel portale di amministrazione. Le conversazioni trasferite sono conservate solo per il tempo necessario a rispondere. Ti chiediamo di <strong>non inserire dati sensibili</strong> in chat.</p>
                <p><em>L.U.X. GUIDE works with options: on first use it asks in which language to continue, then shows buttons. If the guide does not understand a request, the conversation is transferred to a member of the staff and becomes visible in the administration panel. Transferred conversations are kept only for as long as needed to reply. Please do not enter sensitive data in the chat.</em></p>

                <h2 id="s21">21. Feedback, valutazioni e voti / Feedback, ratings and votes</h2>
                <p>Puoi lasciare un <strong>feedback</strong> (con un voto da 1 a 5 e un commento facoltativo) e <strong>votare</strong> i progetti con un upvote. Il feedback è visibile all'amministrazione per migliorare il servizio. I voti sono <strong>anonimi</strong> e vengono mostrati solo come numero complessivo: non associamo il voto alla tua identità e non lo usiamo per profilarti.</p>
                <p><em>You can leave feedback (with a rating from 1 to 5 and an optional comment) and upvote projects. Feedback is visible to the administration to improve the service. Votes are anonymous and shown only as an overall number: we do not link a vote to your identity and we do not use it to profile you.</em></p>

                <h2 id="s22">22. Preferiti e profilo locale (solo sul tuo dispositivo) / Favorites and local profile (on your device only)</h2>
                <p>I <strong>Preferiti</strong> e il <strong>profilo</strong> facoltativo (nome utente e immagine) sono salvati <strong>solo nel tuo browser</strong> (memoria locale del dispositivo). Non vengono inviati ai nostri server e non richiedono alcuna registrazione. Puoi cancellarli in qualsiasi momento rimuovendo i dati del sito dal browser. Se non scegli un'immagine, viene mostrata quella predefinita.</p>
                <p><em>Favorites and the optional profile (username and picture) are stored only in your browser (device local storage). They are never sent to our servers and need no registration. You can delete them at any time by clearing the site data in your browser. If you do not choose a picture, the default one is shown.</em></p>

                <h2 id="s23">23. Statistiche anonime e misure contro gli abusi / Anonymous statistics and anti-abuse measures</h2>
                <p>Per evitare che il servizio cada quando entrano molte persone contemporaneamente, applichiamo un freno alle richieste (<em>rate limit</em>) basato su un identificativo tecnico temporaneo (l'indirizzo di rete), usato <strong>solo per contare le richieste</strong> in una finestra di tempo molto breve. Questo dato non viene usato per profilarti né conservato a lungo. Anche i conteggi di visite sono aggregati e anonimi.</p>
                <p><em>To avoid the service going down when many people connect at once, we apply a request limiter (rate limit) based on a temporary technical identifier (the network address), used only to count requests in a very short time window. This data is not used to profile you and is not kept for long. Visit counts are also aggregated and anonymous.</em></p>

                <h2 id="s24">24. Tempi di conservazione / Retention periods</h2>
                <ul>
                    <li><strong>Feedback:</strong> 24 mesi / 24 months.</li>
                    <li><strong>Chat della guida trasferite:</strong> 12 mesi / 12 months.</li>
                    <li><strong>Conteggi aggregati (voti e aperture):</strong> senza scadenza, perché anonimi / no expiry, as they are anonymous.</li>
                    <li><strong>Registro tecnico delle attività:</strong> 12 mesi / 12 months.</li>
                    <li><strong>Richieste via email o assistenza:</strong> fino alla chiusura della richiesta e comunque non oltre 24 mesi / until the request is closed, and no longer than 24 months.</li>
                </ul>

                <h2 id="s25">25. Misure di sicurezza / Security measures</h2>
                <p>Adottiamo misure tecniche e organizzative adeguate: connessione cifrata (HTTPS), password dell'amministratore conservata come impronta non reversibile (bcrypt), cassaforte dei segreti separata dal codice e non raggiungibile dal web, blocco temporaneo dopo troppi tentativi di accesso, registro delle attività e area amministrativa accessibile a una sola persona autorizzata.</p>
                <p><em>We adopt suitable technical and organisational measures: encrypted connection (HTTPS), administrator password stored as a non-reversible hash (bcrypt), a secrets vault separated from the code and not reachable from the web, temporary lock-out after too many login attempts, an activity log and an administration area accessible to a single authorised person.</em></p>

                <h2 id="s26">26. Versione 39.0.0 e aggiornamenti del documento / Version 39.0.0 and updates to this document</h2>
                <p>Con la versione <strong>39.0.0</strong> ("Grande aggiornamento tecnico") sono stati introdotti la ricerca per esigenza, la guida L.U.X. GUIDE, i Preferiti, il profilo locale, i voti e il sistema di feedback. Questa informativa è stata ampliata e dettagliata di conseguenza. Continuiamo a non vendere dati, a non usare cookie di tracciamento e a non mostrare pubblicità.</p>
                <p><em>Version 39.0.0 (the "Grande aggiornamento tecnico") introduced the search by need, the L.U.X. GUIDE, Favorites, the local profile, upvotes and the feedback system. This policy has been extended and detailed accordingly. We still do not sell data, do not use tracking cookies and do not show advertising.</em></p>

                <h2 id="s27"><?= t('27. Contact') ?></h2>
                <p><?= t('For any question, doubt, clarification or request regarding this Privacy Policy or the processing of your personal data, you can contact us at the following details:') ?></p>
                <div class="box info">
                    <strong>MIAH MOHAMMAD YEAMIN</strong>
                    <p><strong><?= t('Email:') ?></strong> <a href="mailto:administration.luxverse@gmail.com">administration.luxverse@gmail.com</a></p>
                    <p><strong><?= t('Support:') ?></strong> <a href="assistenza.php">luxverse.c22.eu/assistenza.php</a></p>
                    <p><strong><?= t('Contacts:') ?></strong> <a href="contattaci.php">luxverse.c22.eu/contattaci.php</a></p>
                </div>
                <p><?= t('We will do everything possible to respond to your request as quickly as possible and in any case within') ?> <strong><?= t('30 days') ?></strong> <?= t('from receipt.') ?></p>

                <div class="box success">
                    <strong><?= t('Our commitment') ?></strong>
                    <p><?= t('We process your data in a transparent, secure and GDPR-compliant manner. We do not sell your data, we do not use tracking cookies and you can exercise your rights at any time. Your privacy is our priority.') ?></p>
                </div>

                <div style="text-align: center;">
                    <a href="#" class="back-to-top" onclick="window.scrollTo({top:0,behavior:'smooth'});return false;">
                        <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="18 15 12 9 6 15"></polyline></svg>
                        <?= t('Back to top') ?>
                    </a>
                </div>

                <div class="cta-box">
                    <h3><?= t('Questions about Privacy?') ?></h3>
                    <p><?= t('If you have doubts about the processing of your personal data, we are available to provide you with all the information you need.') ?></p>
                    <div class="cta-buttons">
                        <a href="contattaci.php" class="btn-primary">
                            <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path><polyline points="22,6 12,13 2,6"></polyline></svg>
                            <?= t('Contact Us') ?>
                        </a>
                        <a href="terms.php" class="btn-secondary">
                            <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline></svg>
                            <?= t('Terms of Service') ?>
                        </a>
                    </div>
                </div>

            </div>
        </div>
    </section>


                <div class="lux-block">
                    <h2><?= t('Sintesi in 60 secondi') ?></h2>
                    <p><?= t('Se hai un minuto e non vuoi leggere diciotto sezioni, questa è l\'essenza dell\'informativa. Il testo completo resta qui sotto e prevale su questa sintesi.') ?></p>
                    <ul class="lux-list">
                        <li><?= t('Non serve un account e non chiediamo nome, cognome o email per usare gli strumenti.') ?></li>
                        <li><?= t('I dati li fornisci solo se scrivi ad Assistenza o Contatti: quelli servono a risponderti e basta.') ?></li>
                        <li><?= t('Nessuna pubblicità, nessun profilo pubblicitario, nessuna vendita di dati a terzi.') ?></li>
                        <li><?= t('I testi che incolli negli strumenti istantanei non vengono inviati a nessun servizio esterno; quelli con AI vengono inviati al motore che genera la risposta, e non li salviamo nei nostri archivi.') ?></li>
                        <li><?= t('Puoi chiedere copia, correzione o cancellazione di quello che hai inviato scrivendo dalla pagina Contatti.') ?></li>
                    </ul>
                </div>

                <div class="lux-block">
                    <h2><?= t('Dati, finalità e tempi di conservazione') ?></h2>
                    <p><?= t('La tabella mette in fila quello che raccogliamo davvero, perché lo facciamo e per quanto tempo resta nei nostri archivi. Non ci sono voci nascoste: se un dato non è qui, non lo raccogliamo.') ?></p>
                    <div style="overflow-x:auto;">
                    <table class="lux-table">
                        <thead>
                            <tr>
                                <th><?= t('Dato') ?></th>
                                <th><?= t('Perché') ?></th>
                                <th><?= t('Per quanto') ?></th>
                                <th><?= t('Base giuridica') ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><?= t('Nome ed email (moduli)') ?></td>
                                <td><?= t('Risponderti e ricontattarti sullo stesso ticket') ?></td>
                                <td><?= t('24 mesi dall\'ultimo scambio') ?></td>
                                <td><?= t('Consenso, prestato compilando il modulo') ?></td>
                            </tr>
                            <tr>
                                <td><?= t('Testo del messaggio e cronologia') ?></td>
                                <td><?= t('Ricostruire la richiesta e mostrarti lo stato') ?></td>
                                <td><?= t('24 mesi dalla chiusura') ?></td>
                                <td><?= t('Consenso ed esecuzione del servizio richiesto') ?></td>
                            </tr>
                            <tr>
                                <td><?= t('Dati tecnici del browser (diagnostica)') ?></td>
                                <td><?= t('Riprodurre il problema che ci segnali') ?></td>
                                <td><?= t('Insieme al ticket, non oltre') ?></td>
                                <td><?= t('Consenso, con casella di invio volontaria') ?></td>
                            </tr>
                            <tr>
                                <td><?= t('Indirizzo IP (solo forma ridotta)') ?></td>
                                <td><?= t('Distinguere le visite, senza identificarti') ?></td>
                                <td><?= t('90 giorni nelle statistiche di visita') ?></td>
                                <td><?= t('Legittimo interesse alla sicurezza e al conteggio') ?></td>
                            </tr>
                            <tr>
                                <td><?= t('Lingua e preferenze') ?></td>
                                <td><?= t('Ricordare la lingua scelta') ?></td>
                                <td><?= t('12 mesi, nel tuo dispositivo') ?></td>
                                <td><?= t('Nessun trattamento: resta nel tuo browser') ?></td>
                            </tr>
                        </tbody>
                    </table>
                    </div>
                </div>

                <div class="lux-block">
                    <h2><?= t('Cosa non facciamo, per chiarezza') ?></h2>
                    <ul class="lux-list">
                        <li><?= t('Non vendiamo, affittiamo o scambiamo dati con nessuno: non c\'è nulla da vendere e non c\'è un reparto commerciale.') ?></li>
                        <li><?= t('Non creiamo profili pubblicitari e non usiamo cookie di tracciamento o pixel di terze parti.') ?></li>
                        <li><?= t('Non chiediamo mai la password di altri servizi e non pubblichiamo statistiche che permettano di identificare una persona.') ?></li>
                        <li><?= t('Non usiamo i tuoi testi per addestrare modelli: il contenuto passa al motore che genera la risposta e resta lì per il tempo tecnico della richiesta.') ?></li>
                    </ul>
                </div>

                <div class="lux-block">
                    <h2><?= t('Registro delle modifiche a questa informativa') ?></h2>
                    <table class="lux-table">
                        <thead><tr><th><?= t('Data') ?></th><th><?= t('Che cosa è cambiato') ?></th></tr></thead>
                        <tbody>
                            <tr><td>13/09/2026</td><td><?= t('Prima versione pubblica dell\'informativa') ?></td></tr>
                            <tr><td>11/10/2026</td><td><?= t('Aggiunta la sintesi in 60 secondi, la tabella dei tempi di conservazione e la voce sui dati tecnici allegati alle segnalazioni') ?></td></tr>
                        </tbody>
                    </table>
                    <p style="margin-top:14px;"><?= t('Le modifiche future si annotano qui. Se un cambiamento riguarda i tuoi diritti, lo segnaliamo anche in bacheca.') ?></p>
                </div>
</main>

<div class="footer-trust">
    <span><i data-i=heart class=ic></i> <?= t('100% No Profit') ?></span>
    <span class="sep">•</span>
    <span><i data-i=unlock class=ic></i> <?= t('No Registration') ?></span>
    <span class="sep">•</span>
    <span><i data-i=cookie class=ic></i> <?= t('No Cookies') ?></span>
    <span class="sep">•</span>
    <span><i data-i=tag class=ic></i> <?= t('100% Free') ?></span>
</div>


    <?php
    lux_footer();
}


// ============================================================
// SEZIONE: terms   (prima era il file terms.php)
// ============================================================
if ($__info === 'terms') {

lux_session_start();
$pageTitle       = t('Terms of Service - L.U.X. Verse');
$pageDescription = t('Terms of Service of L.U.X. Verse: rules, conditions and obligations for the use of our digital services. No-profit project without commercial purposes.');
$pageKeywords    = 'terms, L.U.X., conditions, service, rules, use, contract, no profit';

$extraCSS = <<<'LUXCSS'
.bg-glow .orb:nth-child(1) { background: var(--yellow); }
.bg-glow .orb:nth-child(2) { background: var(--orange); }
.hero { padding: 180px 0 80px; }
.hero::before {
    content: '';
    position: absolute;
    inset: 0;
    background-image: url('https://images.unsplash.com/photo-1450101499163-c8848c66ca85?w=1920&q=80&auto=format&fit=crop');
    background-size: cover;
    background-position: center;
    opacity: 0.12;
    z-index: -1;
    filter: saturate(1.2);
}
.hero-badge {
    background: rgba(212,175,55,0.1);
    border: 1px solid rgba(212,175,55,0.25);
    color: var(--yellow);
}
.hero-updated {
    display: inline-block;
    margin-top: 20px;
    padding: 6px 14px;
    background: rgba(255,255,255,0.03);
    border: 1px solid var(--border);
    border-radius: 20px;
    font-size: 12px;
    color: var(--text-muted);
    font-weight: 500;
}
.terms-section { padding: 40px 0 80px; }
.terms-content { max-width: 900px; margin: 0 auto; }
.intro-box {
    background: rgba(212,175,55,0.05);
    border: 1px solid rgba(212,175,55,0.15);
    border-left: 4px solid var(--yellow);
    border-radius: 16px;
    padding: 24px 28px;
    margin-bottom: 40px;
}
.intro-box strong {
    color: var(--yellow);
    display: block;
    font-size: 1.05em;
    margin-bottom: 8px;
}
.intro-box p {
    color: var(--text-light);
    font-size: 15px;
    line-height: 1.8;
    margin: 0;
}
.toc {
    background: var(--bg-card);
    border: 1px solid var(--border);
    border-radius: 20px;
    padding: 28px 32px;
    margin-bottom: 50px;
}
.toc h3 {
    font-family: var(--font-title);
    font-size: 1.3em;
    font-weight: 700;
    color: var(--yellow);
    margin-bottom: 18px;
    display: flex;
    align-items: center;
    gap: 10px;
}
.toc-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 6px 24px;
}
.toc a {
    color: var(--text-light);
    text-decoration: none;
    font-size: 14px;
    padding: 6px 0;
    transition: color 0.2s, padding-left 0.2s;
    display: block;
}
.toc a:hover {
    color: var(--yellow);
    padding-left: 6px;
}
.terms-content h2 {
    font-family: var(--font-title);
    font-size: 1.8em;
    font-weight: 700;
    color: var(--yellow);
    margin: 50px 0 20px;
    padding-bottom: 12px;
    border-bottom: 1px solid var(--border);
    scroll-margin-top: 90px;
}
.terms-content h3 {
    font-family: var(--font-title);
    font-size: 1.25em;
    font-weight: 600;
    color: var(--purple);
    margin: 30px 0 12px;
}
.terms-content h4 {
    font-family: var(--font-title);
    font-size: 1.05em;
    font-weight: 600;
    color: var(--pink);
    margin: 22px 0 10px;
}
.terms-content p {
    color: var(--text-light);
    font-size: 15.5px;
    line-height: 1.9;
    margin-bottom: 16px;
}
.terms-content strong { color: #fff; font-weight: 600; }
.terms-content a {
    color: var(--cyan);
    text-decoration: none;
    border-bottom: 1px dotted rgba(212, 170, 87,0.4);
    transition: border-color 0.2s;
}
.terms-content a:hover { border-bottom-color: var(--cyan); }
.terms-content ul, .terms-content ol {
    color: var(--text-light);
    margin: 16px 0 16px 24px;
    font-size: 15.5px;
}
.terms-content li {
    margin-bottom: 10px;
    line-height: 1.8;
}
.box {
    background: var(--bg-card);
    border: 1px solid var(--border);
    border-radius: 14px;
    padding: 20px 24px;
    margin: 20px 0;
}
.box.info { border-left: 4px solid var(--cyan); }
.box.warning { border-left: 4px solid var(--orange); }
.box.success { border-left: 4px solid var(--green); }
.box.danger { border-left: 4px solid var(--pink); }
.box p:last-child { margin-bottom: 0; }
.box strong {
    display: block;
    margin-bottom: 6px;
    font-size: 15px;
}
.box.info strong { color: var(--cyan); }
.box.warning strong { color: var(--orange); }
.box.success strong { color: var(--green); }
.box.danger strong { color: var(--pink); }
.back-to-top {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    color: var(--text-muted);
    text-decoration: none;
    font-size: 13px;
    padding: 8px 14px;
    background: rgba(255,255,255,0.03);
    border: 1px solid var(--border);
    border-radius: 10px;
    transition: color 0.2s, border-color 0.2s;
    margin-top: 40px;
}
.back-to-top:hover {
    color: var(--yellow);
    border-color: var(--yellow);
}
.terms-content .cta-box { padding: 50px 32px; margin-top: 60px; }
.terms-content .cta-box h3 { font-size: 1.6em; }
.footer-trust {
    display: flex;
    justify-content: center;
    align-items: center;
    flex-wrap: wrap;
    gap: 10px;
    padding: 20px 0;
    margin-top: 20px;
    border-top: 1px solid var(--border);
    color: var(--text-light);
    font-size: 13px;
    font-weight: 500;
}
.footer-trust .sep {
    color: var(--text-muted);
    opacity: 0.5;
}
@media (max-width: 768px) {
    .hero { padding: 140px 0 60px; }
    .terms-content h2 { font-size: 1.5em; }
    .terms-content h3 { font-size: 1.15em; }
    .toc-grid { grid-template-columns: 1fr; }
    .terms-content .cta-box { padding: 32px 20px; }
    .terms-content .cta-box h3 { font-size: 1.4em; }
}
@media (max-width: 480px) {
    .terms-content p { font-size: 15px; }
    .toc { padding: 20px; }
    .footer-trust { font-size: 12px; gap: 8px; }
    .footer-trust .sep { display: none; }
}
LUXCSS;

    // CSS condiviso delle pagine informative + quello di questa sezione.

    $extraCSS = (string)($extraCSS ?? '') . $__info_css;


    lux_header();
    ?>


<main>
    <section class="info-hub">
        <?php lux_info_nav('terms'); ?>
    </section>


    <section class="hero">
        <div class="container">
            <div class="hero-badge">
                <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line></svg>
                <?= t('Terms') ?>
            </div>
            <h1><?= t('Terms of Service') ?></h1>
            <p><?= t('Rules, conditions and obligations governing the use of the services offered by L.U.X. Verse. Please read them carefully before using the Site.') ?></p>
            <span class="hero-updated"><?= t('Last updated: 13 September 2026') ?></span>
        </div>
    </section>

    <section class="terms-section">
        <div class="container">
            <div class="terms-content">

                <div class="intro-box">
                    <strong><?= t('In summary:') ?></strong>
                    <p><?= t('By using L.U.X. Verse you accept these Terms of Service. The service is <strong>free, no profit, requires no registration</strong> and we are not responsible for errors in AI-generated content. The project is managed by a 14-year-old boy, without a VAT number. Please read the Terms in their entirety to fully understand their content.') ?></p>
                </div>

                <div class="toc">
                    <h3>
                        <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 6h16M4 12h16M4 18h16"></path></svg>
                        <?= t('Table of Contents') ?>
                    </h3>
                    <div class="toc-grid">
                        <a href="#s1"><?= t('1. Acceptance of Terms') ?></a>
                        <a href="#s2"><?= t('2. Description of the Service') ?></a>
                        <a href="#s3"><?= t('3. Access Requirements') ?></a>
                        <a href="#s4"><?= t('4. Permitted Use') ?></a>
                        <a href="#s5"><?= t('5. Prohibited Use') ?></a>
                        <a href="#s6"><?= t('6. Intellectual Property') ?></a>
                        <a href="#s7"><?= t('7. AI-Generated Content') ?></a>
                        <a href="#s8"><?= t('8. Limitation of Liability') ?></a>
                        <a href="#s9"><?= t('9. Warranties and Services Online') ?></a>
                        <a href="#s10"><?= t('10. Modifications to the Services') ?></a>
                        <a href="#s11"><?= t('11. Suspension and Termination') ?></a>
                        <a href="#s12"><?= t('12. Privacy and Data Protection') ?></a>
                        <a href="#s13"><?= t('13. Links to Third-Party Sites') ?></a>
                        <a href="#s14"><?= t('14. Communications') ?></a>
                        <a href="#s15"><?= t('15. Applicable Law and Competent Court') ?></a>
                        <a href="#s16"><?= t('16. Dispute Resolution') ?></a>
                        <a href="#s17"><?= t('17. Final Provisions') ?></a>
                        <a href="#s18">18. 100.000 strumenti / 100,000 tools</a>
                        <a href="#s19">19. Risposte AI e limiti / AI answers and limits</a>
                        <a href="#s20">20. L.U.X. GUIDE e trasferimento / Guide and transfer</a>
                        <a href="#s21">21. Feedback e voti pubblici / Feedback and votes</a>
                        <a href="#s22">22. Profilo locale e Preferiti / Local profile</a>
                        <a href="#s23">23. Uso corretto e freno alle richieste / Fair use</a>
                        <a href="#s24">24. App Android e tempo reale / Android app</a>
                        <a href="#s25">25. Disponibilità e manutenzione / Availability</a>
                        <a href="#s26">26. Versione 39.0.0 e modifiche / Version 39.0.0</a>
                        <a href="#s27"><?= t('27. Contacts') ?></a>
                    </div>
                </div>

                <h2 id="s1"><?= t('1. Acceptance of Terms') ?></h2>
                <p><?= t('Welcome to <strong>L.U.X. Verse</strong> (hereinafter also "the Site", "we", "our" or "Owner"). These Terms of Service ("Terms") constitute a legally binding agreement between you ("User" or "you") and L.U.X. Verse, and govern access to and use of all services, tools, features and content offered through the Site.') ?></p>
                <p><?= t('By accessing the Site and using our services, you declare that you have read, understood and fully accepted these Terms, as well as our <a href="privacy.php">Privacy Policy</a>, which is an integral and substantial part of this agreement.') ?></p>
                <p><?= t('If you <strong>do not accept</strong> even part of these Terms, we invite you to <strong>not use</strong> the Site and its services.') ?></p>
                <div class="box warning">
                    <strong><?= t('Important note') ?></strong>
                    <p><?= t('Use of the Site implies automatic and unconditional acceptance of these Terms. We therefore invite you to read them carefully and keep a copy for your reference.') ?></p>
                </div>

                <h2 id="s2"><?= t('2. Description of the Service') ?></h2>
                <p><?= t('L.U.X. Verse is a digital platform that offers a series of free AI-based tools and services designed to support the user in creative, productive, educational and entertainment activities.') ?></p>
                <h3><?= t('2.1 Categories of services offered') ?></h3>
                <p><?= t('The services currently offered can be grouped into the following categories:') ?></p>
                <ul>
                    <li><?= t('<strong>AI content generation:</strong> texts, emails, names, rewrites, summaries, explanations, translations.') ?></li>
                    <li><?= t('<strong>Productivity tools:</strong> daily planning, generation of custom plans, creation of mind maps.') ?></li>
                    <li><?= t('<strong>Educational tools:</strong> interactive quizzes, teaching explanations, search engine for students.') ?></li>
                    <li><?= t('<strong>Creative tools:</strong> color palette generation, song lyrics, recipes.') ?></li>
                    <li><?= t('<strong>Communication tools:</strong> AI chat, multilingual translation, voice transcription.') ?></li>
                    <li><?= t('<strong>Entertainment tools:</strong> minigames, quizzes, interactive pastimes.') ?></li>
                </ul>
                <h3><?= t('2.2 Access methods') ?></h3>
                <p><?= t('All the services offered by L.U.X. Verse are:') ?></p>
                <ul>
                    <li><?= t('<strong>Free:</strong> there is no usage cost, no subscriptions or payments.') ?></li>
                    <li><?= t('<strong>No registration:</strong> creating an account is not required to use most services.') ?></li>
                    <li><?= t('<strong>Available 24/7:</strong> the Site is continuously accessible, except for scheduled maintenance or technical interruptions.') ?></li>
                </ul>
                <h3><?= t('2.3 Nature of the Project') ?></h3>
                <p><?= t('L.U.X. Verse is a <strong>no-profit</strong> project, developed by <strong>Miah Mohammad Yeamin</strong>, a <strong>14-year-old</strong> boy. The site is hosted on a free hosting service and is reachable at <em>luxverse.c22.eu</em>; the domain is <strong>not registered in the name of the Controller</strong>, for the reasons explained in section 15.') ?></p>
                <p><?= t('The Owner <strong>does not hold a VAT number</strong> and does not carry out any commercial activity. The project:') ?></p>
                <ul>
                    <li><?= t('Has no commercial purpose') ?></li>
                    <li><?= t('Does not sell any service') ?></li>
                    <li><?= t('Does not require registration') ?></li>
                    <li><?= t('Does not use tracking cookies') ?></li>
                    <li><?= t('Does not show advertising') ?></li>
                    <li><?= t('Does not generate any income') ?></li>
                </ul>

                <h2 id="s3"><?= t('3. Access Requirements') ?></h2>
                <p><?= t('To use the services of L.U.X. Verse, the User declares and guarantees that they:') ?></p>
                <ul>
                    <li><?= t('Are at least <strong>14 years</strong> old, or have the consent of a parent or legal guardian if younger.') ?></li>
                    <li><?= t('Have full legal capacity to act, or are authorized by their legal representative.') ?></li>
                    <li><?= t('Use the Site in accordance with all applicable laws in their country of residence.') ?></li>
                    <li><?= t('Have not been previously suspended or banned from the Site.') ?></li>
                </ul>
                <div class="box info">
                    <strong><?= t('Note on minors') ?></strong>
                    <p><?= t('L.U.X. Verse is a friendly and safe project, but it is managed by a minor. If you are a parent or guardian and have doubts about the use of the site by a minor under 14, please contact us at the address') ?> <a href="mailto:administration.luxverse@gmail.com">administration.luxverse@gmail.com</a>.</p>
                </div>

                <h2 id="s4"><?= t('4. Permitted Use') ?></h2>
                <p><?= t('The User undertakes to use the Site and its services in a lawful, correct manner and in accordance with these Terms. In particular, the User undertakes to:') ?></p>
                <ul>
                    <li><?= t('Use the services exclusively for <strong>personal and legitimate purposes</strong>.') ?></li>
                    <li><?= t('Respect <strong>all applicable laws</strong>, national and international.') ?></li>
                    <li><?= t('Not violate the <strong>rights of third parties</strong>, including intellectual property rights.') ?></li>
                    <li><?= t('<strong>Not interfere</strong> with the proper functioning of the Site or with the experience of other users.') ?></li>
                    <li><?= t('<strong>Not overload</strong> the servers with automated or massive requests.') ?></li>
                    <li><?= t('Provide <strong>truthful and accurate</strong> information when required.') ?></li>
                </ul>

                <h2 id="s5"><?= t('5. Prohibited Use') ?></h2>
                <p><?= t('It is expressly <strong>forbidden</strong> to use the Site for:') ?></p>
                <ul>
                    <li><?= t('Activities that are <strong>illegal</strong> or contrary to public order and decency.') ?></li>
                    <li><?= t('Violating, attempting to violate or circumventing the <strong>security measures</strong> of the Site.') ?></li>
                    <li><?= t('Accessing <strong>restricted areas</strong> without authorization.') ?></li>
                    <li><?= t('Uploading, sending or spreading <strong>harmful content</strong>, viruses, malware or malicious code.') ?></li>
                    <li><?= t('Defaming, harassing, threatening or offending other users or third parties.') ?></li>
                    <li><?= t('Sending <strong>spam</strong>, unsolicited advertising or unauthorized commercial communications.') ?></li>
                    <li><?= t('Reselling, sublicensing, renting or commercially exploiting the services without authorization.') ?></li>
                    <li><?= t('Performing <strong>reverse engineering</strong>, decompiling or attempting to extract the source code.') ?></li>
                    <li><?= t('Using automated systems (bots, scrapers, crawlers) to access the services massively.') ?></li>
                    <li><?= t('Collecting personal data of other users without consent.') ?></li>
                    <li><?= t('Impersonating other people, companies or entities.') ?></li>
                    <li><?= t('Creating content that promotes <strong>violence, hatred, discrimination, pedophilia, terrorism, fascism, nazism</strong> or other ideologies contrary to the law.') ?></li>
                </ul>
                <div class="box danger">
                    <strong><?= t('Attention') ?></strong>
                    <p><?= t('Prohibited use of the Site may result in <strong>immediate suspension of access</strong>, deletion of content and, in the most serious cases, <strong>legal action</strong> against the responsible user.') ?></p>
                </div>

                <h2 id="s6"><?= t('6. Intellectual Property') ?></h2>
                <h3><?= t('6.1 Site content') ?></h3>
                <p><?= t('All content on the Site (including, by way of example, texts, graphics, logos, icons, images, design, structure, source code, databases) is the exclusive property of <strong>L.U.X. Verse</strong> or its respective rights holders, and is protected by Italian and international copyright and intellectual property laws.') ?></p>
                <h3><?= t('6.2 What is not permitted') ?></h3>
                <p><?= t('The User <strong>cannot</strong>:') ?></p>
                <ul>
                    <li><?= t('Reproduce, distribute, modify, publish or sell the contents of the Site without written authorization.') ?></li>
                    <li><?= t('Use logos, trademarks or trade names of L.U.X. Verse without explicit authorization.') ?></li>
                    <li><?= t('Claim authorship of the contents or design of the Site.') ?></li>
                    <li><?= t('Remove or alter copyright notices or other ownership notices.') ?></li>
                </ul>
                <h3><?= t('6.3 User-generated content') ?></h3>
                <p><?= t('The content that the User enters or generates through the AI services (texts, prompts, etc.) remains the property of the User. By using the services, the User grants L.U.X. Verse a limited, non-exclusive and free license for the technical processing of the content, solely for the purpose of providing the requested service.') ?></p>

                <h2 id="s7"><?= t('7. AI-Generated Content') ?></h2>
                <p><?= t('L.U.X. Verse uses advanced artificial intelligence models to generate content. The User acknowledges and accepts that:') ?></p>
                <ul>
                    <li><?= t('AI-generated content is <strong>automatic and probabilistic</strong> in nature.') ?></li>
                    <li><?= t('Content may contain <strong>errors, inaccuracies, omissions or outdated information</strong>.') ?></li>
                    <li><?= t('The User is <strong>solely responsible</strong> for the use they make of the generated content.') ?></li>
                    <li><?= t('Generated content <strong>does not constitute professional advice</strong> (legal, medical, financial, etc.).') ?></li>
                    <li><?= t('AI results <strong>must not be used</strong> to make critical decisions without human verification.') ?></li>
                </ul>
                <div class="box warning">
                    <strong><?= t('Warning') ?></strong>
                    <p><?= t('Content generated by artificial intelligence may be inaccurate or out of date. We invite you to always verify critical information with reliable sources before using it for important purposes.') ?></p>
                </div>

                <h2 id="s8"><?= t('8. Limitation of Liability') ?></h2>
                <p><?= t('To the maximum extent permitted by applicable law, L.U.X. Verse <strong>will not be liable</strong> for:') ?></p>
                <ul>
                    <li><?= t('Direct or indirect damages arising from the use or inability to use the Site.') ?></li>
                    <li><?= t('Loss of data, profits, business opportunities or other economic damages.') ?></li>
                    <li><?= t('Service interruptions, technical errors, malfunctions or delays.') ?></li>
                    <li><?= t('Errors or inaccuracies in AI-generated content.') ?></li>
                    <li><?= t('Content, products or services of third parties accessible through external links.') ?></li>
                    <li><?= t('Unlawful conduct of other users or third parties.') ?></li>
                    <li><?= t('Force majeure events, natural disasters, cyber-attacks or other causes beyond our control.') ?></li>
                </ul>
                <p><?= t('The User uses the Site and its services <strong>at their own risk</strong>.') ?></p>

                <h2 id="s9"><?= t('9. Warranties and Service Availability') ?></h2>
                <p><?= t('L.U.X. Verse undertakes to guarantee the <strong>maximum availability of the service</strong>, ensuring the operation of the ecosystem <strong>24 hours a day, 7 days a week</strong>, except for interruptions due to:') ?></p>
                <ul>
                    <li><?= t('<strong>Scheduled maintenance</strong>: planned interruptions for updates, improvements or fixes, communicated in advance through the notice board of the Site.') ?></li>
                    <li><?= t('<strong>Extraordinary maintenance</strong>: urgent and non-schedulable interventions to ensure the security, stability or proper functioning of the service.') ?></li>
                    <li><?= t('<strong>Force majeure events</strong>: causes beyond our control, such as hosting provider interruptions, cyber-attacks, technical failures or natural events.') ?></li>
                </ul>
                <p><?= t('Outside of these exceptions, we undertake to keep the service <strong>always accessible and functional</strong>, guaranteeing users a continuous and reliable experience.') ?></p>
                <div class="box success">
                    <strong><?= t('Our commitment') ?></strong>
                    <p><?= t('L.U.X. Verse is an independent <strong>no-profit</strong> project, managed with dedication and passion. We constantly strive to guarantee a stable, secure service available to all users. Any interruptions are always aimed at improving the quality of the service.') ?></p>
                </div>

                <h2 id="s10"><?= t('10. Modifications to the Services') ?></h2>
                <p><?= t('We reserve the right to:') ?></p>
                <ul>
                    <li><?= t('<strong>Modify, suspend or discontinue</strong> the services, in whole or in part, without notice.') ?></li>
                    <li><?= t('<strong>Add, modify or remove</strong> features and tools.') ?></li>
                    <li><?= t('<strong>Update, correct or delete</strong> content from the Site.') ?></li>
                    <li><?= t('<strong>Modify these Terms</strong> at any time.') ?></li>
                </ul>
                <p><?= t('Changes to the Terms will take effect from the moment they are published on the Site. Continued use of the Site after the publication of the changes will constitute tacit acceptance of them.') ?></p>

                <h2 id="s11"><?= t('11. Suspension and Termination') ?></h2>
                <p><?= t('We reserve the right to <strong>suspend, limit or terminate</strong> access to the Site, at any time and without notice, for those users who:') ?></p>
                <ul>
                    <li><?= t('Violate these Terms of Service.') ?></li>
                    <li><?= t('Use the Site in an unlawful or fraudulent manner.') ?></li>
                    <li><?= t('Cause harm to the Site, other users or third parties.') ?></li>
                    <li><?= t('Have harassing, offensive or threatening behavior.') ?></li>
                    <li><?= t('Violate intellectual property rights.') ?></li>
                </ul>
                <p><?= t('The User may at any time stop using the Site, without any obligation of notice.') ?></p>

                <h2 id="s12"><?= t('12. Privacy and Data Protection') ?></h2>
                <p><?= t('The processing of users\' personal data is governed by our') ?> <a href="privacy.php"><?= t('Privacy Policy') ?></a><?= t(', which constitutes an integral and substantial part of these Terms of Service.') ?></p>
                <p><?= t('The Data Controller is <strong>Miah Mohammad Yeamin</strong>, 14 years old, without a VAT number. For any detail on the processing of your data, we invite you to consult the Privacy Policy in its entirety.') ?></p>

                <h2 id="s13"><?= t('13. Links to Third-Party Sites') ?></h2>
                <p><?= t('The Site may contain links to third-party websites, resources or services. Such links are provided exclusively for the User\'s convenience.') ?></p>
                <p><?= t('L.U.X. Verse <strong>does not control, approve and is not responsible</strong> for:') ?></p>
                <ul>
                    <li><?= t('The content, privacy policy or practices of third-party sites.') ?></li>
                    <li><?= t('Any products, services or information offered by third parties.') ?></li>
                    <li><?= t('Any damages arising from access to third-party sites.') ?></li>
                </ul>
                <p><?= t('Access to third-party sites occurs at the User\'s sole risk.') ?></p>

                <h2 id="s14"><?= t('14. Communications') ?></h2>
                <p><?= t('All communications relating to these Terms or the services offered may be made through:') ?></p>
                <ul>
                    <li><?= t('The Site itself (through notices on the board).') ?></li>
                    <li><?= t('The contact forms available on the Site (') ?><a href="assistenza.php"><?= t('Support') ?></a> <?= t('and') ?> <a href="contattaci.php"><?= t('Contacts') ?></a>).</li>
                    <li><?= t('The official email address:') ?> <a href="mailto:administration.luxverse@gmail.com">administration.luxverse@gmail.com</a>.</li>
                </ul>

                <h2 id="s15"><?= t('15. Applicable Law and Competent Court') ?></h2>
                <p><?= t('These Terms of Service are governed and interpreted in accordance with <strong>Italian law</strong>, without regard to conflict of law principles.') ?></p>
                <p><?= t('For any dispute arising from the interpretation, validity, execution or termination of these Terms, the <strong>Consumer Court</strong> shall have exclusive jurisdiction, pursuant to Legislative Decree 206/2005 (Consumer Code), where applicable.') ?></p>
                <p><?= t('If the User acts as a professional, the court competent for the Data Controller shall have exclusive jurisdiction.') ?></p>
                <div class="box info">
                    <strong><?= t('Note') ?></strong>
                    <p><?= t('Since this is an amateur and no-profit project managed by a minor, any disputes will be handled amicably, always favoring dialogue and good faith between the parties.') ?></p>
                </div>

                <h2 id="s16"><?= t('16. Dispute Resolution') ?></h2>
                <p><?= t('In case of disputes relating to the interpretation or execution of these Terms, the parties undertake to:') ?></p>
                <ul>
                    <li><?= t('First seek an <strong>amicable solution</strong> through direct negotiation via email.') ?></li>
                    <li><?= t('Use, where possible, <strong>mediation</strong> or <strong>conciliation</strong> tools provided by law.') ?></li>
                    <li><?= t('Resort, as a last resort, to the competent <strong>Judicial Authority</strong>.') ?></li>
                </ul>
                <p><?= t('All communications relating to disputes must be sent to') ?> <a href="mailto:administration.luxverse@gmail.com">administration.luxverse@gmail.com</a> <?= t('with a clear and detailed subject.') ?></p>

                <h2 id="s17"><?= t('17. Final Provisions') ?></h2>
                <h3><?= t('17.1 Partial nullity') ?></h3>
                <p><?= t('If one or more clauses of these Terms are declared null, invalid or ineffective, the remaining clauses will remain in full force and effect.') ?></p>
                <h3><?= t('17.2 Waiver') ?></h3>
                <p><?= t('Any tolerance by L.U.X. Verse towards conduct that does not comply with these Terms does not constitute a waiver of the rights arising from them.') ?></p>
                <h3><?= t('17.3 Entire agreement') ?></h3>
                <p><?= t('These Terms, together with the Privacy Policy, constitute the entire agreement between the User and L.U.X. Verse regarding the use of the Site.') ?></p>
                <h3><?= t('17.4 Assignment') ?></h3>
                <p><?= t('The User cannot assign their rights or obligations arising from these Terms to third parties without the written consent of L.U.X. Verse.') ?></p>
                <h3><?= t('17.5 Nature of the Project') ?></h3>
                <p><?= t('L.U.X. Verse is a personal no-profit project created by <strong>Miah Mohammad Yeamin</strong>, 14 years old, without a VAT number and without commercial purposes. The site is hosted on a free hosting service and is reachable at <em>luxverse.c22.eu</em>. More details are available in the') ?> <a href="privacy.php"><?= t('Privacy Policy') ?></a><?= t(', sections 15 and 16.') ?></p>

                <h2 id="s18">18. 100.000 strumenti e contenuti generati / 100,000 tools and generated content</h2>
                <p>Il catalogo di L.U.X. Verse comprende <strong>100.000 strumenti</strong>, di cui 99.000 generati automaticamente. Gli strumenti generati sono forniti "così come sono" e possono essere affinati o modificati nel tempo. L'uso di un qualsiasi strumento non ti trasferisce alcun diritto sul catalogo, sui nomi o sul design del progetto.</p>
                <p><em>The L.U.X. Verse catalog includes 100,000 tools, 99,000 of which are generated automatically. Generated tools are provided "as is" and may be refined or changed over time. Using any tool does not transfer any right over the catalog, the names or the design of the project.</em></p>

                <h2 id="s19">19. Risposte dell'intelligenza artificiale e limiti dei fornitori / AI answers and provider limits</h2>
                <p>Le risposte degli strumenti sono generate da modelli di terze parti (oggi <strong>Groq</strong>) e possono contenere <strong>errori o imprecisioni</strong>: verifica sempre le informazioni importanti e non usarle come consulenza legale, medica o finanziaria. I fornitori applicano limiti di velocità alla generazione; il nostro sistema distribuisce le richieste su un gruppo di quattro chiavi per ridurre le attese, ma in caso di carico elevato le risposte possono richiedere qualche secondo in più.</p>
                <p><em>Tool answers are generated by third-party models (today Groq) and may contain errors or inaccuracies: always verify important information and never use it as legal, medical or financial advice. Providers apply rate limits; our system spreads requests across a group of four keys to reduce waiting, but under heavy load answers may take a few seconds longer.</em></p>

                <h2 id="s20">20. La guida L.U.X. GUIDE e il trasferimento allo staff / L.U.X. GUIDE and transfer to the staff</h2>
                <p>L.U.X. GUIDE è la guida ufficiale, a opzioni. Se non comprende una richiesta, la conversazione viene trasferita allo staff e diventa visibile nel portale di amministrazione, dove una persona risponde. Le conversazioni sono conservate per il tempo necessario; ti chiediamo di non condividere dati sensibili.</p>
                <p><em>L.U.X. GUIDE is the official guide, shown as options. If it does not understand a request, the conversation is transferred to the staff and becomes visible in the administration panel, where a person replies. Conversations are kept only as long as needed; please do not share sensitive data.</em></p>

                <h2 id="s21">21. Feedback e voti pubblici / Feedback and public votes</h2>
                <p>Puoi inviare un feedback e votare i progetti. I voti sono anonimi e mostrati come numero complessivo; il feedback è visibile all'amministrazione. Ti chiediamo di usare queste funzioni in modo leale, senza tentare di gonfiare i voti in modo artificiale.</p>
                <p><em>You can send feedback and upvote projects. Votes are anonymous and shown as an overall number; feedback is visible to the administration. Please use these features fairly, without trying to inflate votes artificially.</em></p>

                <h2 id="s22">22. Profilo locale e Preferiti (nessun account) / Local profile and Favorites (no account)</h2>
                <p>Non esiste un sistema di account. Il profilo facoltativo (nome utente e immagine) e i Preferiti restano solo nel tuo browser. Sui dispositivi condivisi, chiunque usi quel browser potrebbe vederli: usali con attenzione.</p>
                <p><em>There is no account system. The optional profile (username and picture) and Favorites stay only in your browser. On shared devices, anyone using that browser could see them: use them carefully.</em></p>

                <h2 id="s23">23. Uso corretto e freno alle richieste / Fair use and request limiter</h2>
                <p>Per mantenere il servizio stabile e gratuito per tutti, applichiamo un freno alle richieste troppo ravvicinate e ci riserviamo di limitare l'accesso a chi tenta di abusarne (per esempio con richieste automatizzate massive). Questo non influisce sull'uso normale del sito.</p>
                <p><em>To keep the service stable and free for everyone, we apply a limiter to requests that come too fast and we reserve the right to restrict access for those who try to abuse it (for example with massive automated requests). This does not affect normal use of the site.</em></p>

                <h2 id="s24">24. App Android e collegamento in tempo reale / Android app and real-time connection</h2>
                <p>L'app Android ufficiale è collegata al sito in tempo reale: quando il sito viene aggiornato, l'app lo vede subito. L'app mostra anche la propria versione durante il caricamento. Usa gli stessi indirizzi del sito e le stesse regole di questi Termini.</p>
                <p><em>The official Android app is connected to the site in real time: when the site is updated, the app sees it immediately. The app also shows its version while loading. It uses the same addresses and the same rules as these Terms.</em></p>

                <h2 id="s25">25. Disponibilità del servizio e finestre di manutenzione / Availability and maintenance windows</h2>
                <p>Il servizio è gratuito e fornito "così come è", senza garanzia di disponibilità continua. Possiamo sospendere temporaneamente il sito per manutenzione (in quel caso i visitatori vedono una pagina dedicata, con l'ora prevista di riapertura e un contatto per le urgenze). Non garantiamo che ogni strumento sia sempre disponibile o privo di errori.</p>
                <p><em>The service is free and provided "as is", with no guarantee of continuous availability. We may temporarily suspend the site for maintenance (visitors then see a dedicated page with the expected reopening time and a contact for urgent matters). We do not guarantee that every tool is always available or error-free.</em></p>

                <h2 id="s26">26. Versione 39.0.0 e modifiche ai Termini / Version 39.0.0 and changes to the Terms</h2>
                <p>La versione <strong>39.0.0</strong> introduce la ricerca per esigenza, la guida L.U.X. GUIDE, i Preferiti, il profilo locale, i voti e il feedback. Questi Termini sono stati aggiornati per descrivere le nuove funzioni. Possiamo modificare i Termini: le modifiche importanti saranno annunciate con un avviso visibile sul sito.</p>
                <p><em>Version 39.0.0 introduces the search by need, the L.U.X. GUIDE, Favorites, the local profile, upvotes and feedback. These Terms have been updated to describe the new features. We may change the Terms: important changes will be announced with a visible notice on the site.</em></p>

                <h2 id="s27"><?= t('27. Contacts') ?></h2>
                <p><?= t('For any question, doubt, clarification or request relating to these Terms of Service, you can contact us at the following addresses:') ?></p>
                <div class="box info">
                    <strong><?= t('MIAH MOHAMMAD YEAMIN') ?></strong>
                    <p><strong><?= t('Owner:') ?></strong> <?= t('Miah Mohammad Yeamin (14 years old)') ?></p>
                    <p><strong><?= t('Email:') ?></strong> <a href="mailto:administration.luxverse@gmail.com">administration.luxverse@gmail.com</a></p>
                    <p><strong><?= t('Support:') ?></strong> <a href="assistenza.php">luxverse.c22.eu/assistenza.php</a></p>
                    <p><strong><?= t('Contacts:') ?></strong> <a href="contattaci.php">luxverse.c22.eu/contattaci.php</a></p>
                </div>
                <p><?= t('We will do everything possible to respond to your request as soon as possible and in any case within <strong>30 days</strong> of receiving it.') ?></p>

                <div class="box success">
                    <strong><?= t('Thanks for reading the Terms') ?></strong>
                    <p><?= t('By using L.U.X. Verse, you help create a safe, respectful and innovative digital ecosystem. Thank you for being part of our community. This project was born from the passion of a 14-year-old boy: every visit, every piece of feedback and every use makes all this possible.') ?></p>
                </div>

                <div style="text-align: center;">
                    <a href="#" class="back-to-top" onclick="window.scrollTo({top:0,behavior:'smooth'});return false;">
                        <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="18 15 12 9 6 15"></polyline></svg>
                        <?= t('Back to top') ?>
                    </a>
                </div>

                <div class="cta-box">
                    <h3><?= t('Questions about the Terms?') ?></h3>
                    <p><?= t('If you need clarification on these Terms of Service or their content, our team is available to help you.') ?></p>
                    <div class="cta-buttons">
                        <a href="contattaci.php" class="btn-primary">
                            <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path><polyline points="22,6 12,13 2,6"></polyline></svg>
                            <?= t('Contact Us') ?>
                        </a>
                        <a href="privacy.php" class="btn-secondary">
                            <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect><path d="M7 11V7a5 5 0 0 1 10 0v4"></path></svg>
                            <?= t('Privacy Policy') ?>
                        </a>
                    </div>
                </div>

            </div>
        </div>
    </section>


                <div class="lux-block">
                    <h2><?= t('Sintesi in 60 secondi') ?></h2>
                    <p><?= t('Il testo completo è qui sotto e prevale su questa sintesi, che serve solo a farti capire in fretta con che cosa hai a che fare.') ?></p>
                    <ul class="lux-list">
                        <li><?= t('Il servizio è gratuito, senza registrazione, e non ha scopi commerciali.') ?></li>
                        <li><?= t('Puoi usare i risultati anche per lavoro, senza citarci: sono tuoi e ne rispondi tu.') ?></li>
                        <li><?= t('L\'AI può sbagliare: rileggi sempre quello che pubblichi o consegni.') ?></li>
                        <li><?= t('Non puoi usare gli strumenti per fare danni, per violare la legge o per raccogliere dati altrui.') ?></li>
                        <li><?= t('Il servizio si offre così com\'è: ci impegniamo a farlo funzionare, non a garantire che sia perfetto in ogni momento.') ?></li>
                    </ul>
                </div>

                <div class="lux-block">
                    <h2><?= t('Livello di servizio e uso corretto') ?></h2>
                    <p><?= t('Il progetto non ha un contratto di livello di servizio: è gestito da una persona, con tempi dichiarati e senza promesse che non potremmo mantenere. Quello che possiamo garantire è la trasparenza: se qualcosa si rompe, lo diciamo in bacheca invece di aspettare che tu lo scopra.') ?></p>
                    <table class="lux-table">
                        <thead><tr><th><?= t('Aspetto') ?></th><th><?= t('Impegno reale') ?></th></tr></thead>
                        <tbody>
                            <tr><td><?= t('Disponibilità del sito') ?></td><td><?= t('Continua, con manutenzioni programmate annunciate in bacheca quando possibile') ?></td></tr>
                            <tr><td><?= t('Tempi di risposta alle richieste') ?></td><td><?= t('Da 12 a 72 ore secondo la priorità assegnata alla segnalazione') ?></td></tr>
                            <tr><td><?= t('Uso degli strumenti con AI') ?></td><td><?= t('Uso personale e corretto: sono vietati gli invii automatici ripetuti, che danneggiano tutti gli altri') ?></td></tr>
                            <tr><td><?= t('Contenuti dei risultati') ?></td><td><?= t('Generati da un modello: possono contenere imprecisioni e vanno sempre verificati') ?></td></tr>
                            <tr><td><?= t('Modifiche al servizio') ?></td><td><?= t('Possibili in qualsiasi momento, con nota in bacheca per i cambiamenti che ti riguardano') ?></td></tr>
                        </tbody>
                    </table>
                </div>

                <div class="lux-block">
                    <h2><?= t('Contenuti generati: chi risponde di cosa') ?></h2>
                    <p><?= t('Il testo prodotto da uno strumento è materiale di partenza, come la bozza scritta a mano: nessuno può garantirti che sia corretto al cento per cento. La responsabilità di quello che pubblichi, consegni o firmi resta tua.') ?></p>
                    <ul class="lux-list">
                        <li><?= t('Non usare i risultati come consulenza legale, medica, fiscale o finanziaria senza farli verificare da una persona competente.') ?></li>
                        <li><?= t('Non presentare un risultato generato come se fosse una verifica ufficiale o un documento rilasciato da un ente.') ?></li>
                        <li><?= t('Non inserire dati di altre persone senza averne il diritto: se il caso lo richiede, prima ottieni il consenso.') ?></li>
                        <li><?= t('Se un risultato ti sembra pericoloso o gravemente sbagliato, segnalacelo: è l\'unico modo per correggere le istruzioni dello strumento.') ?></li>
                    </ul>
                </div>

                <div class="lux-block">
                    <h2><?= t('Registro delle modifiche a questi termini') ?></h2>
                    <table class="lux-table">
                        <thead><tr><th><?= t('Data') ?></th><th><?= t('Che cosa è cambiato') ?></th></tr></thead>
                        <tbody>
                            <tr><td>13/09/2026</td><td><?= t('Prima versione pubblica dei termini') ?></td></tr>
                            <tr><td>11/10/2026</td><td><?= t('Aggiunte la sintesi, la tabella del livello di servizio e la sezione sui contenuti generati') ?></td></tr>
                        </tbody>
                    </table>
                    <p style="margin-top:14px;"><?= t('Come per l\'informativa privacy, ogni modifica viene annotata con la sua data.') ?></p>
                </div>
</main>

<div class="footer-trust">
    <span><svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"></path></svg> <?= t('100% No Profit') ?></span>
    <span class="sep">•</span>
    <span><svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect><path d="M7 11V7a5 5 0 0 1 9.9-1"></path></svg> <?= t('No Registration') ?></span>
    <span class="sep">•</span>
    <span><svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><path d="M7 8.5a4 4 0 0 1 7.5 2 3.2 3.2 0 0 1 2 3A3.5 3.5 0 0 1 13 17h-2v-2h2a1.5 1.5 0 0 0 0-3h-2a4 4 0 0 1-1-2.5zM9 13h2v2H9z"></path></svg> <?= t('No Cookies') ?></span>
    <span class="sep">•</span>
    <span><svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20.59 13.41l-7.17 7.17a2 2 0 0 1-2.83 0L2 12V2h10l8.59 8.59a2 2 0 0 1 0 2.82z"></path><line x1="7" y1="7" x2="7.01" y2="7"></line></svg> <?= t('100% Free') ?></span>
</div>

<script>
    document.querySelectorAll('.toc a').forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                window.scrollTo({
                    top: target.offsetTop - 90,
                    behavior: 'smooth'
                });
            }
        });
    });
</script>


    <?php
    lux_footer();
}


// ============================================================
// SEZIONE: faq   (prima era il file faq.php)
// ============================================================
if ($__info === 'faq') {

lux_session_start();
$isAdmin = isAdminLoggedIn();
$pageTitle       = t('Frequently Asked Questions');
$pageDescription = t('Find answers to the most common questions about L.U.X. Verse. If you cannot find what you are looking for, contact us: we are always available.');
$pageKeywords    = 'FAQ, L.U.X. Verse, L.U.X. Verse, questions, answers, help, support';

$extraCSS = '
.faq-section { padding: 40px 0 80px; }
.faq-grid {
    max-width: 900px;
    margin: 0 auto;
    display: flex;
    flex-direction: column;
    gap: 30px;
}

.faq-category { margin-bottom: 10px; }
.faq-category-title {
    font-family: var(--font-title);
    font-size: 1.4em;
    font-weight: 700;
    color: var(--cyan);
    margin-bottom: 16px;
    display: flex;
    align-items: center;
    gap: 12px;
    padding-bottom: 10px;
    border-bottom: 1px solid var(--border);
}
.faq-category-title .cat-icon {
    width: 34px; height: 34px;
    background: rgba(212, 170, 87,0.1);
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}
.faq-category-title .cat-icon svg {
    width: 18px; height: 18px;
    stroke: var(--cyan);
}

.faq-item {
    background: var(--bg-card);
    border: 1px solid var(--border);
    border-radius: 14px;
    overflow: hidden;
    transition: border-color 0.2s, background 0.2s;
    cursor: pointer;
    margin-bottom: 10px;
}
.faq-item:hover {
    border-color: var(--border-hover);
    background: rgba(255,255,255,0.04);
}
.faq-item.active {
    border-color: var(--cyan);
    background: rgba(212, 170, 87,0.03);
}
.faq-question {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 18px 22px;
    font-family: var(--font-title);
    font-size: 1.02em;
    font-weight: 600;
    color: var(--text);
    gap: 16px;
    user-select: none;
}
.faq-question .faq-icon {
    flex-shrink: 0;
    width: 22px; height: 22px;
    transition: transform 0.3s;
    color: var(--text-muted);
    stroke-width: 2;
}
.faq-item.active .faq-question .faq-icon {
    transform: rotate(45deg);
    color: var(--cyan);
}
.faq-answer {
    max-height: 0;
    overflow: hidden;
    transition: max-height 0.4s ease, padding 0.3s ease;
    padding: 0 22px;
    color: var(--text-light);
    font-size: 0.97em;
    line-height: 1.8;
}
.faq-item.active .faq-answer {
    max-height: 500px;
    padding: 0 22px 20px;
}
.faq-answer p { margin-bottom: 12px; }
.faq-answer p:last-child { margin-bottom: 0; }
.faq-answer a {
    color: var(--cyan);
    text-decoration: none;
    font-weight: 500;
}
.faq-answer a:hover { text-decoration: underline; }
.faq-answer strong { color: var(--text); }
.faq-answer ul { margin: 10px 0 10px 20px; }
.faq-answer li { margin-bottom: 6px; }

@media (max-width: 768px) {
    .faq-question { font-size: 0.95em; padding: 16px 18px; }
    .faq-answer { font-size: 0.93em; }
    .faq-item.active .faq-answer { padding: 0 18px 16px; }
    .faq-category-title { font-size: 1.2em; }
    .cta-box { padding: 32px 20px; }
}
';

    // CSS condiviso delle pagine informative + quello di questa sezione.

    $extraCSS = (string)($extraCSS ?? '') . $__info_css;


    lux_header();
    ?>


<main>
    <section class="info-hub">
        <?php lux_info_nav('faq'); ?>
    </section>


    <section class="hero">
        <div class="container">
            <div class="hero-badge">
                <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"></path><line x1="12" y1="17" x2="12.01" y2="17"></line></svg>
                <?= t('Help') ?>
            </div>
            <h1><?= t('Frequently Asked Questions') ?></h1>
            <p><?= t('Find answers to the most common questions about L.U.X. Verse. If you cannot find what you are looking for, contact us: we are always available.') ?></p>
        </div>
    </section>

    <section class="faq-section">
        <div class="container">
                <div class="faq-search">
                    <?= icon('search', 16) ?>
                    <input type="search" id="faqSearch" autocomplete="off"
                           placeholder="<?= t('Cerca nella FAQ: due parole, ad esempio «password» o «app»') ?>"
                           aria-label="<?= t('Cerca nelle domande frequenti') ?>">
                    <span class="faq-search-count" id="faqSearchCount"></span>
                </div>
                <p class="faq-empty" id="faqEmpty" hidden><?= t('Nessuna domanda corrisponde a questa ricerca. Prova con una parola più semplice oppure scrivici: rispondiamo anche alle domande che non sono qui.') ?></p>

            <div class="faq-grid">

                <div class="faq-category">
                    <div class="faq-category-title">
                        <span class="cat-icon">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path></svg>
                        </span>
                        <?= t('General') ?>
                    </div>

                    <div class="faq-item" onclick="toggleFaq(this)">
                        <div class="faq-question">
                            <?= t('What is L.U.X. Verse?') ?>
                            <svg class="faq-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                        </div>
                        <div class="faq-answer">
                            <p><?= t('L.U.X. Verse is an ecosystem of digital tools based on artificial intelligence, completely free and accessible to everyone. We offer over twenty-five different tools, divided into categories such as creativity, productivity, education, games and utilities.') ?></p>
                            <p><?= t("The main goal of the project is to make AI technology a tool within everyone's reach, without economic barriers or registration requirements.") ?></p>
                        </div>
                    </div>

                    <div class="faq-item" onclick="toggleFaq(this)">
                        <div class="faq-question">
                            <?= t('Is everything really free?') ?>
                            <svg class="faq-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                        </div>
                        <div class="faq-answer">
                            <p><?= t('Yes, all our tools are completely free. We do not require subscriptions, payments or registrations of any kind. There are no paid features, premium versions or hidden costs.') ?></p>
                            <p><?= t('We firmly believe that artificial intelligence should be a tool accessible to everyone, and we are committed to keeping this promise over time.') ?></p>
                        </div>
                    </div>

                    <div class="faq-item" onclick="toggleFaq(this)">
                        <div class="faq-question">
                            <?= t('Do I need to register to use the services?') ?>
                            <svg class="faq-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                        </div>
                        <div class="faq-answer">
                            <p><?= t('No, no registration is required. You can use all our tools immediately, without creating an account, without providing personal data and without confirming your email.') ?></p>
                            <p><?= t('This choice is in line with our accessibility philosophy: we believe everyone should be able to use technology without having to overcome barriers of any kind.') ?></p>
                        </div>
                    </div>

                    <div class="faq-item" onclick="toggleFaq(this)">
                        <div class="faq-question">
                            <?= t('Who created L.U.X. Verse?') ?>
                            <svg class="faq-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                        </div>
                        <div class="faq-answer">
                            <p><?= t("L.U.X. Verse was born in 2026 from the idea of a young developer passionate about technology and artificial intelligence. The project grew quickly, becoming a complete ecosystem of digital tools.") ?></p>
                            <p><?= t('For more details about our history and our mission, we invite you to visit the') ?> <a href="chi-siamo.php"><?= t('About Us') ?></a> <?= t('page.') ?></p>
                        </div>
                    </div>
                </div>

                <div class="faq-category">
                    <div class="faq-category-title">
                        <span class="cat-icon">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="16 18 22 12 16 6"></polyline><polyline points="8 6 2 12 8 18"></polyline></svg>
                        </span>
                        <?= t('Technical') ?>
                    </div>

                    <div class="faq-item" onclick="toggleFaq(this)">
                        <div class="faq-question">
                            <?= t('How do your AI tools work?') ?>
                            <svg class="faq-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                        </div>
                        <div class="faq-answer">
                            <p><?= t('Our tools use advanced artificial intelligence models to generate content, answer questions, translate texts, create descriptive images and much more. Every tool is designed for a specific task.') ?></p>
                            <p><?= t('You simply have to write what you need: our system will process the request and return the result in a few moments, automatically and without any manual configuration.') ?></p>
                        </div>
                    </div>

                    <div class="faq-item" onclick="toggleFaq(this)">
                        <div class="faq-question">
                            <?= t('How long does it take to generate a response?') ?>
                            <svg class="faq-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                        </div>
                        <div class="faq-answer">
                            <p><?= t('Most of our tools generate responses within a few seconds. Waiting time depends on the complexity of the request and the type of tool used.') ?></p>
                            <p><?= t('In case of particularly complex requests or high traffic, processing may take a few more seconds. We thank you for your patience.') ?></p>
                        </div>
                    </div>

                    <div class="faq-item" onclick="toggleFaq(this)">
                        <div class="faq-question">
                            <?= t('Do the tools also work on smartphones?') ?>
                            <svg class="faq-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                        </div>
                        <div class="faq-answer">
                            <p><?= t('Absolutely yes. All our tools are designed with a') ?> <strong><?= t('mobile-first') ?></strong> <?= t('approach: they work perfectly on smartphones, tablets and computers, with interfaces optimized for every type of screen.') ?></p>
                            <p><?= t('No app is needed: just open the site from your favorite browser.') ?></p>
                        </div>
                    </div>

                    <div class="faq-item" onclick="toggleFaq(this)">
                        <div class="faq-question">
                            <?= t('Are there usage limits?') ?>
                            <svg class="faq-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                        </div>
                        <div class="faq-answer">
                            <p><?= t('Currently we do not apply strict usage limits, but in the future we may introduce some safeguard measures to prevent abuse and guarantee a stable service to all users.') ?></p>
                            <p><?= t('We recommend using the tools responsibly and not automating requests on a massive scale.') ?></p>
                        </div>
                    </div>
                </div>

                <div class="faq-category">
                    <div class="faq-category-title">
                        <span class="cat-icon">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect><path d="M7 11V7a5 5 0 0 1 10 0v4"></path></svg>
                        </span>
                        <?= t('Privacy and Security') ?>
                    </div>

                    <div class="faq-item" onclick="toggleFaq(this)">
                        <div class="faq-question">
                            <?= t('Is my data safe?') ?>
                            <svg class="faq-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                        </div>
                        <div class="faq-answer">
                            <p><?= t('Absolutely yes. We do not collect personal data, we do not use tracking cookies and we do not share information with third parties. Your privacy is one of our absolute priorities.') ?></p>
                            <p><?= t('All communications with the site take place through an encrypted HTTPS connection. For more details, see our') ?> <a href="privacy.php"><?= t('Privacy Policy') ?></a>.</p>
                        </div>
                    </div>

                    <div class="faq-item" onclick="toggleFaq(this)">
                        <div class="faq-question">
                            <?= t('Are the texts I enter saved?') ?>
                            <svg class="faq-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                        </div>
                        <div class="faq-answer">
                            <p><?= t('No, we do not save the texts you enter in our AI tools. The content you send is processed in real time and is not stored on our servers.') ?></p>
                            <p><?= t('This means you can use our tools with total freedom, knowing that your data will never be stored or used for other purposes.') ?></p>
                        </div>
                    </div>

                    <div class="faq-item" onclick="toggleFaq(this)">
                        <div class="faq-question">
                            <?= t('Do you use cookies?') ?>
                            <svg class="faq-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                        </div>
                        <div class="faq-answer">
                            <p><?= t('We only use technical cookies that are essential for the proper functioning of the site, such as those for managing sessions and user preferences.') ?></p>
                            <p><?= t('We do not use profiling, tracking or marketing cookies. For further information, see our') ?> <a href="privacy.php"><?= t('Privacy Policy') ?></a>.</p>
                        </div>
                    </div>

                    <div class="faq-item" onclick="toggleFaq(this)">
                        <div class="faq-question">
                            <?= t('What happens if I do not accept the Terms?') ?>
                            <svg class="faq-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                        </div>
                        <div class="faq-answer">
                            <p><?= t('On your first visit to the site you will be asked to accept our Privacy Policy and the Terms of Service. If you choose not to accept, you will not be able to use our services.') ?></p>
                            <p><?= t('If you choose to ignore the acceptance, you will still be held responsible for your actions on the site, as specified in the') ?> <a href="terms.php"><?= t('Terms of Service') ?></a>.</p>
                        </div>
                    </div>
                </div>

                <div class="faq-category">
                    <div class="faq-category-title">
                        <span class="cat-icon">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>
                        </span>
                        <?= t('Support and Assistance') ?>
                    </div>

                    <div class="faq-item" onclick="toggleFaq(this)">
                        <div class="faq-question">
                            <?= t('How can I contact support?') ?>
                            <svg class="faq-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                        </div>
                        <div class="faq-answer">
                            <p><?= t('You can contact us in several ways:') ?></p>
                            <ul>
                                <li><?= t('Through the') ?> <a href="assistenza.php"><?= t('Support') ?></a> <?= t('page, for technical requests and problems') ?></li>
                                <li><?= t('Through the') ?> <a href="contattaci.php"><?= t('Contacts') ?></a> <?= t('page, for general information') ?></li>
                                <li><?= t('Through our social channels (coming soon)') ?></li>
                            </ul>
                            <p><?= t('We try to answer all requests as quickly as possible, usually within 24-48 hours.') ?></p>
                        </div>
                    </div>

                    <div class="faq-item" onclick="toggleFaq(this)">
                        <div class="faq-question">
                            <?= t('Can I suggest new tools or features?') ?>
                            <svg class="faq-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                        </div>
                        <div class="faq-answer">
                            <p><?= t('Of course! We are always looking for new ideas and suggestions from our users. Every feedback is valuable to improve the L.U.X. Verse ecosystem.') ?></p>
                            <p><?= t('You can send your suggestions through the') ?> <a href="contattaci.php"><?= t('Contacts') ?></a> <?= t('page, selecting the "Suggestion" type.') ?></p>
                        </div>
                    </div>

                    <div class="faq-item" onclick="toggleFaq(this)">
                        <div class="faq-question">
                            <?= t('I found a bug. How can I report it?') ?>
                            <svg class="faq-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                        </div>
                        <div class="faq-answer">
                            <p><?= t('If you have found a bug or a malfunction, we invite you to report it through the') ?> <a href="assistenza.php"><?= t('Support') ?></a> <?= t('page, selecting the "Bug Report" category.') ?></p>
                            <p><?= t('To help us solve the problem faster, we ask you to describe in detail:') ?></p>
                            <ul>
                                <li><?= t('Which tool you were using') ?></li>
                                <li><?= t('What you did before encountering the problem') ?></li>
                                <li><?= t('What you expected to happen') ?></li>
                                <li><?= t('What actually happened') ?></li>
                            </ul>
                        </div>
                    </div>

                    <div class="faq-item" onclick="toggleFaq(this)">
                        <div class="faq-question">
                            <?= t('Can I use your tools for commercial purposes?') ?>
                            <svg class="faq-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                        </div>
                        <div class="faq-answer">
                            <p><?= t('Yes, our tools can also be used for commercial and professional purposes. There are no particular restrictions on the use of the generated results.') ?></p>
                            <p><?= t('However, we invite you to consult our') ?> <a href="terms.php"><?= t('Terms of Service') ?></a> <?= t('to learn the terms of use and limitations of liability in detail.') ?></p>
                        </div>
                    </div>

                    <div class="faq-item" onclick="toggleFaq(this)">
                        <div class="faq-question">
                            <?= t('Is the site available in other languages?') ?>
                            <svg class="faq-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                        </div>
                        <div class="faq-answer">
                            <p><?= t('Currently L.U.X. Verse is mainly available in Italian, but many of our tools natively support multiple languages, including English, French, Spanish, German and others.') ?></p>
                            <p><?= t('We are working to make the site interface available in more languages in the future.') ?></p>
                        </div>
                    </div>
                </div>

                <div class="faq-category">
                    <div class="faq-category-title">
                        <span class="cat-icon">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="7" height="7"></rect><rect x="14" y="3" width="7" height="7"></rect><rect x="14" y="14" width="7" height="7"></rect><rect x="3" y="14" width="7" height="7"></rect></svg>
                        </span>
                        <?= t('Projects') ?>
                    </div>

                    <div class="faq-item" onclick="toggleFaq(this)">
                        <div class="faq-question">
                            <?= t('How many projects are there on L.U.X. Verse?') ?>
                            <svg class="faq-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                        </div>
                        <div class="faq-answer">
                            <p><?= t('Currently the L.U.X. Verse ecosystem has over twenty-five active projects, and the number keeps growing. Each project is an independent tool but integrated into the ecosystem.') ?></p>
                            <p><?= t('You can explore all the projects from the') ?> <a href="webapp.php"><?= t('home page') ?></a><?= t(', filtering them by category or searching them by name.') ?></p>
                        </div>
                    </div>

                    <div class="faq-item" onclick="toggleFaq(this)">
                        <div class="faq-question">
                            <?= t('What does it mean when a project is "under maintenance"?') ?>
                            <svg class="faq-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                        </div>
                        <div class="faq-answer">
                            <p><?= t('When a project is marked as "under maintenance", it means we are carrying out updates, improvements or fixes on that specific tool.') ?></p>
                            <p><?= t('During this period, the project may be temporarily unavailable. We invite you to try again later: it will be back online as soon as possible, improved and more performant.') ?></p>
                        </div>
                    </div>

                    <div class="faq-item" onclick="toggleFaq(this)">
                        <div class="faq-question">
                            <?= t('Can I propose a new project?') ?>
                            <svg class="faq-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                        </div>
                        <div class="faq-answer">
                            <p><?= t('Absolutely yes! We are always open to new ideas and proposals from the community. If you have in mind a tool that could enrich the L.U.X. Verse ecosystem, let us know.') ?></p>
                            <p><?= t('You can send your proposal through the') ?> <a href="contattaci.php"><?= t('Contacts') ?></a> <?= t('page, selecting the "Suggestion" type.') ?></p>
                        </div>
                    </div>
                </div>

            </div>

                <!-- ===== NUOVI GRUPPI ===== -->
                <div class="faq-category" data-faq-category>
                    <div class="faq-category-title">
                        <span class="cat-icon"><?= icon('cpu', 18) ?></span>
                        <?= t('Strumenti e funzionamento') ?>
                    </div>

                    <div class="faq-item" onclick="toggleFaq(this)">
                        <div class="faq-question">
                            <?= t('Quanti strumenti ci sono e come sono organizzati?') ?>
                            <svg class="faq-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                        </div>
                        <div class="faq-answer">
                            <p><?= t('Oggi sono cento, divisi per categoria: creatività, produttività, studio, giochi e utilità. Puoi sfogliarli dalla home oppure cercarli per nome.') ?></p>
                            <p><?= t('Ogni strumento è pensato per risolvere una cosa sola e farla bene: niente pannelli complicati, solo i campi che servono e un risultato pronto da copiare o scaricare.') ?></p>
                        </div>
                    </div>

                    <div class="faq-item" onclick="toggleFaq(this)">
                        <div class="faq-question">
                            <?= t('Che differenza c\'è tra uno strumento istantaneo e uno con l\'AI?') ?>
                            <svg class="faq-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                        </div>
                        <div class="faq-answer">
                            <p><?= t('Gli strumenti <strong>istantanei</strong> calcolano sul posto, nel tuo browser o sul server, e danno il risultato in un istante: conversioni, calcoli, formattazioni, controlli. Non serve nessuna connessione a servizi esterni e il risultato è sempre lo stesso a parità di dati.') ?></p>
                            <p><?= t('Gli strumenti <strong>con AI</strong> generano testo: lì la risposta può variare leggermente tra un tentativo e l\'altro, perché il modello non ripete mai la stessa frase allo stesso modo. Per questo, sui contenuti importanti, ti conviene rileggere sempre il risultato.') ?></p>
                        </div>
                    </div>

                    <div class="faq-item" onclick="toggleFaq(this)">
                        <div class="faq-question">
                            <?= t('Gli strumenti funzionano anche da telefono?') ?>
                            <svg class="faq-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                        </div>
                        <div class="faq-answer">
                            <p><?= t('Sì, tutto il sito è progettato prima per il telefono e poi per il computer: nessuno strumento richiede il desktop. I moduli si adattano allo schermo e le aree di testo restano comode anche con la tastiera su schermo.') ?></p>
                            <p><?= t('Se usi Android c\'è anche l\'app ufficiale: stessa cosa del sito, ma con un\'icona sulla schermata iniziale e senza barra del browser.') ?></p>
                        </div>
                    </div>

                    <div class="faq-item" onclick="toggleFaq(this)">
                        <div class="faq-question">
                            <?= t('Che fine fanno i testi che incollo negli strumenti?') ?>
                            <svg class="faq-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                        </div>
                        <div class="faq-answer">
                            <p><?= t('Gli strumenti istantanei non inviano nulla a nessuno: il calcolo avviene e il testo non esce dalla tua sessione. Gli strumenti con AI, per forza di cose, mandano il testo al motore che genera la risposta.') ?></p>
                            <p><?= t('Non salviamo il contenuto dei tuoi testi nei nostri archivi. Nel tuo browser, se vuoi, restano gli ultimi risultati per poterli riaprire: puoi cancellarli quando vuoi.') ?></p>
                        </div>
                    </div>

                    <div class="faq-item" onclick="toggleFaq(this)">
                        <div class="faq-question">
                            <?= t('Perché a volte un risultato dell\'AI è impreciso?') ?>
                            <svg class="faq-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                        </div>
                        <div class="faq-answer">
                            <p><?= t('Perché un modello linguistico lavora sulle probabilità, non su una verità verificata: può sbagliare un numero, un nome o un riferimento. È un assistente, non un oracolo.') ?></p>
                            <p><?= t('La cosa giusta da fare è quella che faresti con qualsiasi bozza: rileggere, controllare i dati che contano e correggere. Se trovi un errore che si ripete, segnalacelo: ci aiuta a migliorare le istruzioni degli strumenti.') ?></p>
                        </div>
                    </div>
                </div>

                <div class="faq-category" data-faq-category>
                    <div class="faq-category-title">
                        <span class="cat-icon"><?= icon('smartphone', 18) ?></span>
                        <?= t('App Android') ?>
                    </div>

                    <div class="faq-item" onclick="toggleFaq(this)">
                        <div class="faq-question">
                            <?= t('Come installo l\'app?') ?>
                            <svg class="faq-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                        </div>
                        <div class="faq-answer">
                            <p><?= t('Scarica il file APK dalla home o dal footer, aprilo e conferma l\'installazione. Android potrebbe chiedere di consentire le installazioni da fonti sconosciute: è la procedura normale per un\'app distribuita fuori dal Play Store.') ?></p>
                            <p><?= t('L\'avviso di Chrome del tipo «questo file potrebbe essere dannoso» compare per qualsiasi APK scaricato dal web: puoi procedere tranquillamente, il file è quello che pubblichiamo noi.') ?></p>
                        </div>
                    </div>

                    <div class="faq-item" onclick="toggleFaq(this)">
                        <div class="faq-question">
                            <?= t('Devo reinstallare l\'app per avere gli aggiornamenti?') ?>
                            <svg class="faq-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                        </div>
                        <div class="faq-answer">
                            <p><?= t('No: l\'app carica il sito sempre aggiornato, quindi ogni nuovo strumento che pubblichiamo ti arriva subito. Installa di nuovo il file solo quando annunciamo una versione con novità dell\'app stessa.') ?></p>
                        </div>
                    </div>

                    <div class="faq-item" onclick="toggleFaq(this)">
                        <div class="faq-question">
                            <?= t('L\'app richiede permessi o account?') ?>
                            <svg class="faq-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                        </div>
                        <div class="faq-answer">
                            <p><?= t('No. Non serve un account e non chiediamo accesso a contatti, foto, posizione o microfono. L\'unica cosa che l\'app fa è mostrarti gli strumenti e, se scegli di generare un risultato, inviare il testo che scrivi.') ?></p>
                        </div>
                    </div>
                </div>

                <div class="faq-category" data-faq-category>
                    <div class="faq-category-title">
                        <span class="cat-icon"><?= icon('shield', 18) ?></span>
                        <?= t('Progetto, trasparenza e responsabilità') ?>
                    </div>

                    <div class="faq-item" onclick="toggleFaq(this)">
                        <div class="faq-question">
                            <?= t('Chi c\'è dietro L.U.X. Verse?') ?>
                            <svg class="faq-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                        </div>
                        <div class="faq-answer">
                            <p><?= t('Una persona sola: M. M. Yeamin, quattordici anni, che ha iniziato il progetto da studente del primo anno di superiori. Non c\'è una società, non c\'è un team pagato, non c\'è un piano di marketing.') ?></p>
                            <p><?= t('Questo spiega anche i tempi: il progetto vive nei pomeriggi e nei fine settimana, tra lo studio e la scuola. È il motivo per cui chiediamo pazienza quando una risposta non arriva in un\'ora.') ?></p>
                        </div>
                    </div>

                    <div class="faq-item" onclick="toggleFaq(this)">
                        <div class="faq-question">
                            <?= t('Come segnalo un problema di sicurezza o un abuso?') ?>
                            <svg class="faq-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                        </div>
                        <div class="faq-answer">
                            <p><?= t('Scrivi dalla pagina Assistenza scegliendo la categoria <strong>Privacy</strong> o indicando nel testo che si tratta di sicurezza: quei ticket entrano automaticamente in priorità alta e li leggiamo per primi.') ?></p>
                            <p><?= t('Non pubblicare i dettagli in un messaggio aperto al pubblico e non allegare dati di altre persone. Se ti serve, ti risponderemo per concordare un canale più riservato.') ?></p>
                        </div>
                    </div>

                    <div class="faq-item" onclick="toggleFaq(this)">
                        <div class="faq-question">
                            <?= t('Posso usare i risultati per un esame, un lavoro o un\'attività commerciale?') ?>
                            <svg class="faq-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                        </div>
                        <div class="faq-answer">
                            <p><?= t('Sì, anche per scopi commerciali, senza chiedere permessi e senza citare L.U.X. Verse. Il testo che generi è tuo e ne rispondi tu.') ?></p>
                            <p><?= t('L\'unica cosa che non puoi fare è presentare il sito o gli strumenti come tuoi, spacciarli per un prodotto diverso o rivendere l\'accesso. Per il resto, usali seriamente e rileggi sempre prima di consegnare.') ?></p>
                        </div>
                    </div>

                    <div class="faq-item" onclick="toggleFaq(this)">
                        <div class="faq-question">
                            <?= t('Il progetto è sostenibile? Chi paga i costi?') ?>
                            <svg class="faq-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                        </div>
                        <div class="faq-answer">
                            <p><?= t('Costa molto poco: un hosting condiviso e le chiamate al motore AI, che consumano quota solo quando usi gli strumenti con AI. Non c\'è pubblicità, non ci sono abbonamenti e nessuno paga per usare il sito.') ?></p>
                            <p><?= t('Per restare sostenibile nel tempo gli strumenti con AI hanno un uso corretto e non illimitato: non c\'è un contatore nascosto, ma se un giorno i consumi cresceranno troppo lo diremo apertamente, invece di introdurre costi a sorpresa.') ?></p>
                        </div>
                    </div>

                    <div class="faq-item" onclick="toggleFaq(this)">
                        <div class="faq-question">
                            <?= t('Come posso contribuire?') ?>
                            <svg class="faq-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                        </div>
                        <div class="faq-answer">
                            <p><?= t('Nel modo più utile: usandolo e dicendoci cosa non funziona. Una segnalazione precisa vale più di dieci complimenti, perché indica esattamente dove intervenire.') ?></p>
                            <p><?= t('Poi ci sono i suggerimenti: se pensi a uno strumento che manca, scrivilo dalla pagina Contatti scegliendo il tipo <strong>Suggerimento</strong>. Le idee che arrivano dagli utenti entrano nella lista di lavoro.') ?></p>
                        </div>
                    </div>
                </div>

            <div class="cta-box">
                <h3><?= t('Did not find the answer?') ?></h3>
                <p><?= t('If your question is not on this page, do not hesitate to contact us. Our team is always available to help you.') ?></p>
                <div class="cta-buttons">
                    <a href="assistenza.php" class="btn-primary">
                        <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path></svg>
                        <?= t('Go to Support') ?>
                    </a>
                    <a href="contattaci.php" class="btn-secondary">
                        <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path><polyline points="22,6 12,13 2,6"></polyline></svg>
                        <?= t('Contact Us') ?>
                    </a>
                </div>
            </div>

        </div>
    </section>

</main>

<script>
    function toggleFaq(element) {
        const item = element.closest('.faq-item');
        if (item) {
            item.classList.toggle('active');
        }
    }

    // --- ricerca immediata nella FAQ ---
    (function () {
        const box = document.getElementById('faqSearch');
        if (!box) return;
        const count = document.getElementById('faqSearchCount');
        const empty = document.getElementById('faqEmpty');
        const items = Array.prototype.slice.call(document.querySelectorAll('.faq-item'));
        const groups = Array.prototype.slice.call(document.querySelectorAll('.faq-category'));
        const total = items.length;

        items.forEach(function (item) {
            item.dataset.text = (item.textContent || '').toLowerCase().replace(/\s+/g, ' ');
        });

        function apply() {
            const needle = box.value.trim().toLowerCase();
            const words = needle.split(/\s+/).filter(function (w) { return w.length > 2; });
            let shown = 0;

            items.forEach(function (item) {
                const hit = needle === '' || words.some(function (w) { return item.dataset.text.indexOf(w) !== -1; });
                item.hidden = !hit;
                if (hit) shown++;
            });

            groups.forEach(function (group) {
                const visible = group.querySelectorAll('.faq-item:not([hidden])').length;
                group.hidden = visible === 0;
            });

            if (count) {
                count.textContent = (needle === '')
                    ? total + ' <?= tjs('domande disponibili') ?>'
                    : shown + ' / ' + total;
            }
            if (empty) empty.hidden = shown !== 0;
        }

        box.addEventListener('input', apply);

        // Se la pagina viene aperta con ?cerca=... la ricerca e' gia' pronta.
        const fromUrl = new URLSearchParams(window.location.search).get('cerca');
        if (fromUrl) { box.value = fromUrl; apply(); }
    })();
</script>


    <?php
    lux_footer();
}


// ============================================================
// SEZIONE: assistenza   (prima era il file assistenza.php)
// ============================================================
if ($__info === 'assistenza') {

lux_session_start();

// Modalita' dati: ?queue=1 risponde in JSON con lo stato reale della coda
// (il pannello dal vivo la usa per mostrare posizione e tempi medi).
if (isset($_GET['queue'])) {
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(array_merge(['success' => true], lux_support_queue()), JSON_UNESCAPED_UNICODE);
    exit;
}

// === GESTIONE DELLA RICHIESTA DI ASSISTENZA ===
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_assistance'])) {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $category = trim($_POST['category'] ?? '');
    $subject = trim($_POST['subject'] ?? '');
    $message = trim($_POST['message'] ?? '');
    $urgency = trim($_POST['urgency'] ?? '');

    if (!empty($name) && !empty($email) && !empty($category) && !empty($subject) && !empty($message)) {
        $trackingCode = strtoupper(substr(md5(uniqid(mt_rand(), true)), 0, 8));

        // Triage automatico: priorita' motivata, tempo di risposta, punteggio.
        $triage = lux_support_triage([
            'category' => $category, 'urgency' => $urgency,
            'subject'  => $subject,  'message' => $message,
            'email'    => $email,    'page'    => trim($_POST['page'] ?? ''),
        ]);

        // Diagnostica: dati tecnici del browser raccolti dal pannello dal vivo.
        $diagnostics = lux_support_diagnostics([
            'viewport'   => $_POST['diag_viewport'] ?? '',
            'screen'     => $_POST['diag_screen'] ?? '',
            'timezone'   => $_POST['diag_timezone'] ?? '',
            'cores'      => $_POST['diag_cores'] ?? '',
            'memory'     => $_POST['diag_memory'] ?? '',
            'connection' => $_POST['diag_connection'] ?? '',
            'js'         => $_POST['diag_js'] ?? '',
            'theme'      => $_POST['diag_theme'] ?? '',
            'session'    => $_POST['diag_session'] ?? '',
        ]);

        $queue = lux_support_queue();

        $assistanceFile = __DIR__ . '/data/assistance.json';
        $requests = [];

        if (file_exists($assistanceFile)) {
            $requests = json_decode(file_get_contents($assistanceFile), true) ?? [];
        }

        $requests[] = [
            'code' => $trackingCode,
            'name' => $name,
            'email' => $email,
            'category' => $category,
            'subject' => $subject,
            'message' => $message,
            'urgency' => $urgency,
            'priority' => $triage['priority'],
            'priority_label' => $triage['priority_label'],
            'sla_hours' => $triage['sla_hours'],
            'score' => $triage['score'],
            'reasons' => $triage['reasons'],
            'queue_position' => $queue['position'],
            'diagnostics' => $diagnostics,
            'lang' => currentLang(),
            'date' => date('Y-m-d H:i:s'),
            'status' => 'In Attesa',
            'history' => [[
                'when' => date('Y-m-d H:i:s'),
                'label' => 'Presa in carico automatica',
                'note' => '',
            ]],
        ];

        if (!is_dir(dirname($assistanceFile))) {
            mkdir(dirname($assistanceFile), 0755, true);
        }

        file_put_contents($assistanceFile, json_encode($requests, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

        header('Location: assistenza.php?success=1&code=' . $trackingCode);
        exit;
    }
}

// === HANDLING OF TRACKING ===
$trackingResult = null;
$trackingError = '';
if (isset($_GET['track'])) {
    $trackCode = trim($_GET['track']);

    $assistanceFile = __DIR__ . '/data/assistance.json';
    $requests = [];

    if (file_exists($assistanceFile)) {
        $requests = json_decode(file_get_contents($assistanceFile), true) ?? [];
    }

    foreach ($requests as $req) {
        if (($req['code'] ?? '') === $trackCode) {
            $trackingResult = $req;
            break;
        }
    }

    if (!$trackingResult) {
        $trackingError = t('No request found with this code.');
    }
}

$statusLabels = [
    'In Attesa'      => t('Pending'),
    'In Lavorazione' => t('In Progress'),
    'Risolto'        => t('Resolved'),
    'Archiviato'     => t('Archived')
];
$categoryLabels = [
    'Assistenza Tecnica'     => t('Technical Support'),
    'Segnalazione Bug'       => t('Bug Report'),
    'Richiesta Informazioni' => t('Information Request'),
    'Reclamo'                => t('Complaint'),
    'Suggerimento'           => t('Suggestion'),
    'Problemi Account'       => t('Account Issues'),
    'Privacy'                => t('Privacy'),
    'Altro'                  => t('Other')
];

$isAdmin = isAdminLoggedIn();
$pageTitle       = t('Support - L.U.X. Verse');
$pageDescription = t('Request assistance from L.U.X. Verse: report problems, bugs, requests and more. Fast and professional support.');
$pageKeywords    = 'assistance, support, L.U.X., help, request, ticket, bug';

$extraCSS = '
.assist-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 30px;
    max-width: 1100px;
    margin: 0 auto;
}

.card {
    background: var(--bg-card);
    border: 1px solid var(--border);
    border-radius: 22px;
    padding: 32px;
    transition: border-color 0.2s;
}
.card:hover { border-color: var(--border-hover); }
.card h2 {
    font-family: var(--font-title);
    font-size: 1.5em;
    font-weight: 700;
    margin-bottom: 22px;
    display: flex;
    align-items: center;
    gap: 12px;
    color: var(--cyan);
}
.card h2 .card-icon {
    width: 40px; height: 40px;
    background: rgba(212, 170, 87,0.1);
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}
.card h2 .card-icon svg {
    width: 20px; height: 20px;
    stroke: var(--cyan);
}

.form-group { margin-bottom: 16px; }
.form-group label {
    display: block;
    margin-bottom: 6px;
    color: var(--text-light);
    font-weight: 500;
    font-size: 13px;
}
.form-group input,
.form-group select,
.form-group textarea {
    width: 100%;
    padding: 12px 16px;
    background: rgba(255,255,255,0.04);
    border: 1px solid var(--border);
    border-radius: 12px;
    color: var(--text);
    font-size: 14px;
    font-family: var(--font-body);
    outline: none;
    transition: border-color 0.15s, background 0.15s, box-shadow 0.15s;
}
.form-group input:focus,
.form-group select:focus,
.form-group textarea:focus {
    border-color: var(--cyan);
    background: rgba(212, 170, 87,0.02);
    box-shadow: 0 0 0 3px rgba(212, 170, 87,0.1);
}
.form-group textarea { resize: vertical; min-height: 120px; }

select {
    appearance: none;
    -webkit-appearance: none;
    -moz-appearance: none;
    background-image: url("data:image/svg+xml,%3Csvg xmlns=\'http://www.w3.org/2000/svg\' width=\'12\' height=\'12\' viewBox=\'0 0 12 12\'%3E%3Cpath d=\'M6 8L1 3h10z\' fill=\'%2300E5FF\'/%3E%3C/svg%3E");
    background-repeat: no-repeat;
    background-position: right 14px center;
    padding-right: 36px !important;
    cursor: pointer;
}
select, select option {
    background-color: #0F1113 !important;
    color: #FFFFFF !important;
}
select option { padding: 10px; }

.tracking-form { display: flex; gap: 10px; margin-bottom: 20px; }
.tracking-form input {
    flex: 1;
    padding: 12px 16px;
    background: rgba(255,255,255,0.04);
    border: 1px solid var(--border);
    border-radius: 12px;
    color: var(--text);
    font-size: 14px;
    font-family: var(--font-body);
    outline: none;
    transition: border-color 0.15s, box-shadow 0.15s;
}
.tracking-form input:focus {
    border-color: var(--purple);
    box-shadow: 0 0 0 3px rgba(200, 127, 42,0.1);
}
.tracking-form button {
    padding: 12px 24px;
    background: linear-gradient(135deg, var(--purple), var(--pink));
    color: #fff;
    border: none;
    border-radius: 12px;
    font-weight: 600;
    cursor: pointer;
    transition: transform 0.2s, box-shadow 0.2s;
    font-family: var(--font-body);
    font-size: 14px;
    white-space: nowrap;
}
.tracking-form button:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 20px rgba(200, 127, 42,0.3);
}

.success-message {
    grid-column: 1 / -1;
    background: rgba(60,207,145,0.1);
    border: 1px solid rgba(60,207,145,0.3);
    border-radius: 16px;
    padding: 24px;
    margin-bottom: 20px;
    color: var(--green);
    text-align: center;
}
.success-message strong { display: block; font-size: 1.15em; margin-bottom: 8px; }
.success-message .tracking-code {
    font-family: var(--font-title);
    font-size: 1.8em;
    font-weight: 700;
    color: var(--yellow);
    letter-spacing: 2px;
    margin: 12px 0;
    padding: 12px 20px;
    background: rgba(212,175,55,0.08);
    border: 1px dashed rgba(212,175,55,0.4);
    border-radius: 12px;
    display: inline-block;
}
.success-message p { color: var(--text-light); font-size: 14px; margin-top: 8px; }

.tracking-result {
    background: rgba(255,255,255,0.03);
    border-radius: 14px;
    padding: 20px;
    margin-top: 16px;
    border-left: 4px solid var(--green);
}
.tracking-result .status-badge {
    font-size: 12px;
    font-weight: 700;
    padding: 5px 14px;
    border-radius: 20px;
    display: inline-block;
    margin-bottom: 14px;
}
.status-in-attesa { background: rgba(212,175,55,0.15); color: var(--yellow); }
.status-in-lavorazione { background: rgba(212, 170, 87,0.15); color: var(--cyan); }
.status-risolto { background: rgba(60,207,145,0.15); color: var(--green); }
.status-archiviato { background: rgba(255,255,255,0.05); color: var(--text-muted); }
.tracking-result p {
    color: var(--text-light);
    font-size: 13px;
    margin-bottom: 8px;
    line-height: 1.7;
}
.tracking-result p strong { color: #fff; font-weight: 600; }
.tracking-message {
    background: rgba(255,255,255,0.03);
    border-radius: 10px;
    padding: 12px 14px;
    margin-top: 12px;
    color: var(--text-light);
    font-size: 13px;
    line-height: 1.7;
    border-left: 2px solid var(--cyan);
}
.tracking-error {
    background: rgba(232,97,75,0.1);
    border: 1px solid rgba(232,97,75,0.3);
    border-radius: 12px;
    padding: 14px 18px;
    color: var(--pink);
    font-size: 13px;
    margin-top: 16px;
    display: flex;
    align-items: center;
    gap: 10px;
}

.card .info-box { max-width: none; margin-left: 0; margin-right: 0; }

@media (max-width: 1024px) {
    .assist-grid { grid-template-columns: 1fr; max-width: 700px; }
}
@media (max-width: 768px) {
    .card { padding: 24px; }
    .card h2 { font-size: 1.3em; }
    .tracking-form { flex-direction: column; }
    .tracking-form button { width: 100%; justify-content: center; }
}
@media (max-width: 480px) {
    .card { padding: 20px; }
    .success-message .tracking-code { font-size: 1.3em; }
}
';

    // CSS condiviso delle pagine informative + quello di questa sezione.

    $extraCSS = (string)($extraCSS ?? '') . $__info_css;


    lux_header();
    ?>


<main>
    <section class="info-hub">
        <?php lux_info_nav('assistenza'); ?>
    </section>


    <!-- Hero -->
    <section class="hero">
        <div class="container">
            <div class="hero-badge">
                <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path></svg>
                <?= t('Support') ?>
            </div>
            <h1><?= t('Help Center') ?></h1>
            <p><?= t('Request assistance, report problems or bugs. Our team will answer you as quickly as possible to help you resolve any difficulty.') ?></p>
        </div>
    </section>

    <!-- Main -->
    <section class="main-section">
        <div class="container">
            <div class="assist-grid">

                <?php if (isset($_GET['success']) && isset($_GET['code'])): ?>
                <div class="success-message">
                    <strong><?= t('Request sent successfully!') ?></strong>
                    <?= t('Your tracking code is:') ?>
                    <div class="tracking-code"><?php echo htmlspecialchars($_GET['code']); ?></div>
                    <p><?= t('Save this code: you will need it to track the status of your request at any time.') ?></p>
                </div>
                <?php endif; ?>

                <!-- Request form -->
                <div class="card">
                    <h2>
                        <span class="card-icon">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path></svg>
                        </span>
                        <?= t('Request Assistance') ?>
                    </h2>
                    <!-- Il modulo porta la classe support-form: la console dal vivo
                         riconosce i campi da qui, senza nomi duplicati. -->
                    <form method="POST" class="support-form">
                        <?php lux_support_console(['form' => 'assistenza']); ?>
                        <div class="form-group">
                            <label><?= t('Full name *') ?></label>
                            <input type="text" name="name" placeholder="<?= t('Ex. Mario Rossi') ?>" required>
                        </div>
                        <div class="form-group">
                            <label><?= t('Email *') ?></label>
                            <input type="email" name="email" placeholder="<?= t('Ex. mario.rossi@email.com') ?>" required>
                        </div>
                        <div class="form-group">
                            <label><?= t('Category *') ?></label>
                            <select name="category" required>
                                <option value=""><?= t('Select a category...') ?></option>
                                <option value="Assistenza Tecnica"><?= t('Technical Support') ?></option>
                                <option value="Segnalazione Bug"><?= t('Bug Report') ?></option>
                                <option value="Richiesta Informazioni"><?= t('Information Request') ?></option>
                                <option value="Reclamo"><?= t('Complaint') ?></option>
                                <option value="Suggerimento"><?= t('Suggestion') ?></option>
                                <option value="Problemi Account"><?= t('Account Issues') ?></option>
                                <option value="Privacy"><?= t('Privacy') ?></option>
                                <option value="Altro"><?= t('Other') ?></option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label><?= t('Quanto è urgente?') ?></label>
                            <select name="urgency">
                                <option value="bassa"><?= t('Bassa · posso aspettare') ?></option>
                                <option value="media" selected><?= t('Media · mi crea problemi') ?></option>
                                <option value="alta"><?= t('Alta · mi blocca il lavoro') ?></option>
                                <option value="bloccante"><?= t('Bloccante · non posso usare nulla') ?></option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label><?= t('Subject *') ?></label>
                            <input type="text" name="subject" placeholder="<?= t('Ex. Problem with the text generator') ?>" required>
                        </div>
                        <div class="form-group">
                            <label><?= t('Message *') ?></label>
                            <textarea name="message" rows="5" placeholder="<?= t('Describe your problem in detail. The more information you provide, the faster we can help you...') ?>" required></textarea>
                        </div>
                        <button type="submit" name="submit_assistance" class="btn-primary">
                            <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="22" y1="2" x2="11" y2="13"></line><polygon points="22 2 15 22 11 13 2 9 22 2"></polygon></svg>
                            <?= t('Send Request') ?>
                        </button>
                    </form>
                    <div class="info-box">
                        <p><strong><?= t('Note:') ?></strong> <?= t('you will receive a unique alphanumeric tracking code. Keep it: you will need it to check the status of your request and receive updates.') ?></p>
                    </div>

                    <?php lux_support_console_js(['form' => 'assistenza']); ?>
                </div>

                <!-- Tracking -->
                <div class="card">
                    <h2>
                        <span class="card-icon">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
                        </span>
                        <?= t('Track your Request') ?>
                    </h2>
                    <p style="color: var(--text-light); font-size: 14px; margin-bottom: 18px;"><?= t('Enter the tracking code received when submitting to check the status of your request.') ?></p>

                    <form method="GET" class="tracking-form">
                        <input type="text" name="track" placeholder="<?= t('Ex. A1B2C3D4') ?>" value="<?php echo htmlspecialchars($_GET['track'] ?? ''); ?>" required>
                        <button type="submit"><?= t('Track') ?></button>
                    </form>

                    <?php if ($trackingResult): ?>
                        <?php
                        $statusRaw = $trackingResult['status'] ?? 'In Attesa';
                        $statusClass = 'status-in-attesa';
                        if ($statusRaw === 'In Lavorazione') $statusClass = 'status-in-lavorazione';
                        elseif ($statusRaw === 'Risolto') $statusClass = 'status-risolto';
                        elseif ($statusRaw === 'Archiviato') $statusClass = 'status-archiviato';
                        $statusText = $statusLabels[$statusRaw] ?? $statusRaw;
                        $categoryText = $categoryLabels[$trackingResult['category'] ?? ''] ?? ($trackingResult['category'] ?? '');
                        ?>
                        <div class="tracking-result">
                            <span class="status-badge <?php echo $statusClass; ?>"><?php echo htmlspecialchars($statusText); ?></span>
                            <p><strong><?= t('Code:') ?></strong> <?php echo htmlspecialchars($trackingResult['code'] ?? ''); ?></p>
                            <p><strong><?= t('Name:') ?></strong> <?php echo htmlspecialchars($trackingResult['name'] ?? ''); ?></p>
                            <p><strong><?= t('Category:') ?></strong> <?php echo htmlspecialchars($categoryText); ?></p>
                            <p><strong><?= t('Subject:') ?></strong> <?php echo htmlspecialchars($trackingResult['subject'] ?? ''); ?></p>
                            <p><strong><?= t('Date:') ?></strong> <?php echo date('d/m/Y H:i', strtotime($trackingResult['date'] ?? 'now')); ?></p>
                            <div class="tracking-message">
                                <?php echo nl2br(htmlspecialchars($trackingResult['message'] ?? '')); ?>
                            </div>

                            <?php
                            // --- scheda tecnica: triage, diagnostica e cronologia ---
                            $__ticketTimeline = lux_support_timeline($trackingResult);
                            $__diag = (array)($trackingResult['diagnostics'] ?? []);
                            ?>
                            <div class="sup-ticket" id="supTicketText">
                                <p><strong><?= t('Triage:') ?></strong>
                                    <?= htmlspecialchars((string)($trackingResult['priority_label'] ?? ($trackingResult['priority'] ?? t('non disponibile per questa richiesta')))) ?>
                                    <?php if (!empty($trackingResult['sla_hours'])): ?>
                                        · <?= t('risposta prevista entro {h} ore', ['h' => (int)$trackingResult['sla_hours']]) ?>
                                    <?php endif; ?>
                                    <?php if (!empty($trackingResult['score'])): ?>
                                        · <?= t('completezza dichiarata: {n}%', ['n' => (int)$trackingResult['score']]) ?>
                                    <?php endif; ?>
                                </p>
                                <?php if (!empty($trackingResult['reasons'])): ?>
                                    <p><strong><?= t('Da completare:') ?></strong> <?= htmlspecialchars(implode(' · ', array_map('strval', (array)$trackingResult['reasons']))) ?></p>
                                <?php endif; ?>
                                <?php if ($__diag): ?>
                                    <p><strong><?= t('Diagnostica acquisita automaticamente:') ?></strong>
                                        <?php
                                        $__diagRows = [];
                                        foreach ($__diag as $__k => $__v) {
                                            $__diagRows[] = $__k . '=' . $__v;
                                        }
                                        echo htmlspecialchars(implode(' · ', array_slice($__diagRows, 0, 10)));
                                        ?>
                                    </p>
                                <?php endif; ?>
                                <?php if (!empty($trackingResult['queue_position'])): ?>
                                    <p><strong><?= t('Posizione in coda:') ?></strong> <?= (int)$trackingResult['queue_position'] ?></p>
                                <?php endif; ?>
                                <p><strong><?= t('Cronologia:') ?></strong></p>
                                <ul class="sup-timeline">
                                    <?php foreach ($__ticketTimeline as $__step): ?>
                                        <li>
                                            <span class="when"><?= htmlspecialchars((string)$__step['when']) ?></span>
                                            <span class="what"><b><?= htmlspecialchars((string)$__step['label']) ?></b>
                                                <?php if ((string)$__step['note'] !== ''): ?><span><?= htmlspecialchars((string)$__step['note']) ?></span><?php endif; ?>
                                            </span>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                            <button type="button" class="sup-btn small" id="supDownload" data-code="<?= htmlspecialchars((string)($trackingResult['code'] ?? '')) ?>">
                                <?= icon('download', 14) ?>
                                <span><?= t('Scarica il ticket') ?></span>
                            </button>
                        </div>
                    <?php elseif ($trackingError): ?>
                        <div class="tracking-error">
                            <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><line x1="15" y1="9" x2="9" y2="15"></line><line x1="9" y1="9" x2="15" y2="15"></line></svg>
                            <?php echo htmlspecialchars($trackingError); ?>
                        </div>
                    <?php endif; ?>

                    <div class="info-box" style="margin-top: 24px;">
                        <p><strong><?= t('Response times:') ?></strong> <?= t('our team tries to answer all requests within 24-48 hours. During peak periods, times may lengthen slightly.') ?></p>
                    </div>
                </div>

            </div>

            <!-- CTA -->
            <div class="cta-box">
                <h3><?= t('Have you already checked the FAQ?') ?></h3>
                <p><?= t('Many of the most common questions are already answered in our dedicated section. You could solve your problem in a few seconds.') ?></p>
                <div class="cta-buttons">
                    <a href="faq.php" class="btn-primary">
                        <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"></path><line x1="12" y1="17" x2="12.01" y2="17"></line></svg>
                        <?= t('Go to FAQ') ?>
                    </a>
                    <a href="contattaci.php" class="btn-secondary">
                        <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path><polyline points="22,6 12,13 2,6"></polyline></svg>
                        <?= t('Contact Us') ?>
                    </a>
                </div>
            </div>

        </div>
    </section>


                <div class="lux-block">
                    <h2><?= t('Tempi di risposta, priorità per priorità') ?></h2>
                    <p><?= t('La priorità non la scegli tu a mano: la calcola il triage in base a categoria, urgenza e contenuto del messaggio. Serve a dare un ordine alla coda e a dirti con onestà quando puoi aspettarti una risposta.') ?></p>
                    <table class="lux-table">
                        <thead><tr><th><?= t('Priorità') ?></th><th><?= t('Quando viene assegnata') ?></th><th><?= t('Risposta prevista') ?></th></tr></thead>
                        <tbody>
                            <tr><td><strong>P1 · <?= t('Critica') ?></strong></td><td><?= t('Sicurezza, perdita di dati, servizio bloccato del tutto') ?></td><td><?= t('entro 12 ore') ?></td></tr>
                            <tr><td><strong>P2 · <?= t('Alta') ?></strong></td><td><?= t('Un errore impedisce di completare un lavoro, oppure il problema riguarda account, reclami, privacy') ?></td><td><?= t('entro 24 ore') ?></td></tr>
                            <tr><td><strong>P3 · <?= t('Normale') ?></strong></td><td><?= t('Un problema su una funzione specifica, con una soluzione alternativa disponibile') ?></td><td><?= t('entro 48 ore') ?></td></tr>
                            <tr><td><strong>P4 · <?= t('Bassa') ?></strong></td><td><?= t('Domande, suggerimenti, richieste di informazioni') ?></td><td><?= t('entro 72 ore') ?></td></tr>
                        </tbody>
                    </table>
                    <p style="margin-top:14px;"><?= t('I tempi partono dalla ricezione e possono allungarsi nelle settimane di scuola piena o se il tuo messaggio è troppo breve per capire il problema: in quel caso ti scriviamo per un chiarimento e il conteggio riparte.') ?></p>
                </div>

                <div class="lux-block">
                    <h2><?= t('Come scrivere una segnalazione che si risolve al primo colpo') ?></h2>
                    <p><?= t('La differenza tra una risposta in poche ore e una settimana di domande e risposte sta quasi sempre in cinque informazioni. Nessuna richiede competenze tecniche.') ?></p>
                    <div class="lux-steps">
                        <div class="lux-step">
                            <b>01</b>
                            <h3><?= t('Dove') ?></h3>
                            <p><?= t('Il nome dello strumento e l\'indirizzo della pagina: per esempio «LUX.Short, dalla pagina riassumi.php».') ?></p>
                        </div>
                        <div class="lux-step">
                            <b>02</b>
                            <h3><?= t('Con cosa') ?></h3>
                            <p><?= t('Dispositivo e browser: «telefono Android, Chrome», oppure «computer Windows, Firefox». Basta questo.') ?></p>
                        </div>
                        <div class="lux-step">
                            <b>03</b>
                            <h3><?= t('Cosa hai fatto') ?></h3>
                            <p><?= t('I passi, in ordine: «ho incollato il testo, ho scelto medio, ho premuto Genera».') ?></p>
                        </div>
                        <div class="lux-step">
                            <b>04</b>
                            <h3><?= t('Cosa ti aspettavi') ?></h3>
                            <p><?= t('Il risultato atteso, in una riga: «mi aspettavo il riassunto in tre punti».') ?></p>
                        </div>
                        <div class="lux-step">
                            <b>05</b>
                            <h3><?= t('Cosa è successo invece') ?></h3>
                            <p><?= t('Il messaggio di errore, la schermata vuota, il numero sbagliato. Anche una foto dello schermo aiuta.') ?></p>
                        </div>
                    </div>
                    <p style="margin-top:16px;"><?= t('Il pannello qui sopra ti aiuta mentre scrivi: il punteggio di completezza sale quando il messaggio contiene queste informazioni, e la diagnostica tecnica viene allegata da sola, senza che tu debba copiarla a mano.') ?></p>
                </div>

                <div class="lux-block">
                    <h2><?= t('Che cosa significano gli stati del ticket') ?></h2>
                    <table class="lux-table">
                        <thead><tr><th><?= t('Stato') ?></th><th><?= t('Cosa succede davvero') ?></th></tr></thead>
                        <tbody>
                            <tr><td><strong><?= t('In attesa') ?></strong></td><td><?= t('Il ticket è in coda e nessuno lo ha ancora aperto. La posizione in coda è quella che vedi nel riquadro di tracciamento.') ?></td></tr>
                            <tr><td><strong><?= t('In lavorazione') ?></strong></td><td><?= t('Sto riproducendo il problema e cercando la causa. Se serve un chiarimento, ti scrivo all\'email che hai indicato.') ?></td></tr>
                            <tr><td><strong><?= t('Risolto') ?></strong></td><td><?= t('La causa è stata corretta e pubblicata. Il ticket resta consultabile per due anni, così puoi rileggere cosa era successo.') ?></td></tr>
                            <tr><td><strong><?= t('Archiviato') ?></strong></td><td><?= t('Il ticket non è più attivo: era un duplicato, non riproducibile, oppure è rimasto senza risposta ai chiarimenti richiesti.') ?></td></tr>
                        </tbody>
                    </table>
                </div>
</main>


    <?php
    lux_footer();
}


// ============================================================
// SEZIONE: contattaci   (prima era il file contattaci.php)
// ============================================================
if ($__info === 'contattaci') {

lux_session_start();

// Modalita' dati: ?queue=1 risponde in JSON con lo stato reale della coda.
if (isset($_GET['queue'])) {
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(array_merge(['success' => true], lux_support_queue()), JSON_UNESCAPED_UNICODE);
    exit;
}

// === GESTIONE DELLA SEGNALAZIONE ===
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_message'])) {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $project = trim($_POST['project'] ?? '');
    $type = trim($_POST['type'] ?? '');
    $message = trim($_POST['message'] ?? '');
    $urgency = trim($_POST['urgency'] ?? '');

    if (!empty($name) && !empty($email) && !empty($project) && !empty($type) && !empty($message)) {
        $trackingCode = strtoupper(substr(md5(uniqid(mt_rand(), true)), 0, 8));

        // Triage automatico sulla segnalazione (categoria + tipo + urgenza).
        $triage = lux_support_triage([
            'category' => $type, 'urgency' => $urgency,
            'subject'  => $project, 'message' => $message,
            'email'    => $email,    'page'    => trim($_POST['page'] ?? ''),
        ]);

        $diagnostics = lux_support_diagnostics([
            'viewport'   => $_POST['diag_viewport'] ?? '',
            'screen'     => $_POST['diag_screen'] ?? '',
            'timezone'   => $_POST['diag_timezone'] ?? '',
            'cores'      => $_POST['diag_cores'] ?? '',
            'memory'     => $_POST['diag_memory'] ?? '',
            'connection' => $_POST['diag_connection'] ?? '',
            'js'         => $_POST['diag_js'] ?? '',
            'theme'      => $_POST['diag_theme'] ?? '',
            'session'    => $_POST['diag_session'] ?? '',
        ]);

        $queue = lux_support_queue();

        $messagesFile = __DIR__ . '/data/messages.json';
        $messages = [];

        if (file_exists($messagesFile)) {
            $messages = json_decode(file_get_contents($messagesFile), true) ?? [];
        }

        $messages[] = [
            'code' => $trackingCode,
            'name' => $name,
            'email' => $email,
            'project' => $project,
            'type' => $type,
            'message' => $message,
            'urgency' => $urgency,
            'priority' => $triage['priority'],
            'priority_label' => $triage['priority_label'],
            'sla_hours' => $triage['sla_hours'],
            'score' => $triage['score'],
            'reasons' => $triage['reasons'],
            'queue_position' => $queue['position'],
            'diagnostics' => $diagnostics,
            'lang' => currentLang(),
            'date' => date('Y-m-d H:i:s'),
            'status' => 'In Attesa',
            'history' => [[
                'when' => date('Y-m-d H:i:s'),
                'label' => 'Presa in carico automatica',
                'note' => '',
            ]],
        ];

        if (!is_dir(dirname($messagesFile))) {
            mkdir(dirname($messagesFile), 0755, true);
        }

        file_put_contents($messagesFile, json_encode($messages, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

        header('Location: contattaci.php?success=1&code=' . $trackingCode);
        exit;
    }
}

// === HANDLING OF TRACKING ===
$trackingResult = null;
$trackingError = '';
if (isset($_GET['track'])) {
    $trackCode = trim($_GET['track']);

    $messagesFile = __DIR__ . '/data/messages.json';
    $messages = [];

    if (file_exists($messagesFile)) {
        $messages = json_decode(file_get_contents($messagesFile), true) ?? [];
    }

    foreach ($messages as $msg) {
        if (($msg['code'] ?? '') === $trackCode) {
            $trackingResult = $msg;
            break;
        }
    }

    if (!$trackingResult) {
        $trackingError = t('No message found with this code.');
    }
}

$statusLabels = [
    'In Attesa'      => t('Pending'),
    'In Lavorazione' => t('In Progress'),
    'Risolto'        => t('Resolved'),
    'Archiviato'     => t('Archived')
];
$projectLabels = [
    'Generale' => t('General'),
    'Altro'    => t('Other')
];
$typeLabels = [
    'Assistenza'   => t('Assistance'),
    'Consigli'     => t('Advice'),
    'Bug'          => t('Bug'),
    'Suggerimento' => t('Suggestion'),
    'Reclamo'      => t('Complaint'),
    'Altro'        => t('Other')
];

$isAdmin = isAdminLoggedIn();
$pageTitle       = t('Contacts - L.U.X. Verse');
$pageDescription = t('Contact L.U.X. Verse: send a message, request information or report a problem. We reply within 24-48 hours.');
$pageKeywords    = 'contacts, L.U.X., assistance, support, message, email';

$extraCSS = '
.contact-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 30px;
    max-width: 1100px;
    margin: 0 auto;
}

.card {
    background: var(--bg-card);
    border: 1px solid var(--border);
    border-radius: 22px;
    padding: 32px;
    transition: border-color 0.2s;
}
.card:hover { border-color: var(--border-hover); }
.card h2 {
    font-family: var(--font-title);
    font-size: 1.5em;
    font-weight: 700;
    margin-bottom: 22px;
    display: flex;
    align-items: center;
    gap: 12px;
    color: var(--cyan);
}
.card h2 .card-icon {
    width: 40px; height: 40px;
    background: rgba(212, 170, 87,0.1);
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}
.card h2 .card-icon svg {
    width: 20px; height: 20px;
    stroke: var(--cyan);
}

.form-group { margin-bottom: 16px; }
.form-group label {
    display: block;
    margin-bottom: 6px;
    color: var(--text-light);
    font-weight: 500;
    font-size: 13px;
}
.form-group input,
.form-group select,
.form-group textarea {
    width: 100%;
    padding: 12px 16px;
    background: rgba(255,255,255,0.04);
    border: 1px solid var(--border);
    border-radius: 12px;
    color: var(--text);
    font-size: 14px;
    font-family: var(--font-body);
    outline: none;
    transition: border-color 0.15s, background 0.15s, box-shadow 0.15s;
}
.form-group input:focus,
.form-group select:focus,
.form-group textarea:focus {
    border-color: var(--cyan);
    background: rgba(212, 170, 87,0.02);
    box-shadow: 0 0 0 3px rgba(212, 170, 87,0.1);
}
.form-group textarea { resize: vertical; min-height: 120px; }

select {
    appearance: none;
    -webkit-appearance: none;
    -moz-appearance: none;
    background-image: url("data:image/svg+xml,%3Csvg xmlns=\'http://www.w3.org/2000/svg\' width=\'12\' height=\'12\' viewBox=\'0 0 12 12\'%3E%3Cpath d=\'M6 8L1 3h10z\' fill=\'%2300E5FF\'/%3E%3C/svg%3E");
    background-repeat: no-repeat;
    background-position: right 14px center;
    padding-right: 36px !important;
    cursor: pointer;
}
select, select option {
    background-color: #0F1113 !important;
    color: #FFFFFF !important;
}
select option { padding: 10px; }

.tracking-form { display: flex; gap: 10px; margin-bottom: 20px; }
.tracking-form input {
    flex: 1;
    padding: 12px 16px;
    background: rgba(255,255,255,0.04);
    border: 1px solid var(--border);
    border-radius: 12px;
    color: var(--text);
    font-size: 14px;
    font-family: var(--font-body);
    outline: none;
    transition: border-color 0.15s, box-shadow 0.15s;
}
.tracking-form input:focus {
    border-color: var(--purple);
    box-shadow: 0 0 0 3px rgba(200, 127, 42,0.1);
}
.tracking-form button {
    padding: 12px 24px;
    background: linear-gradient(135deg, var(--purple), var(--pink));
    color: #fff;
    border: none;
    border-radius: 12px;
    font-weight: 600;
    cursor: pointer;
    transition: transform 0.2s, box-shadow 0.2s;
    font-family: var(--font-body);
    font-size: 14px;
    white-space: nowrap;
}
.tracking-form button:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 20px rgba(200, 127, 42,0.3);
}

.success-message {
    grid-column: 1 / -1;
    background: rgba(60,207,145,0.1);
    border: 1px solid rgba(60,207,145,0.3);
    border-radius: 16px;
    padding: 24px;
    margin-bottom: 20px;
    color: var(--green);
    text-align: center;
}
.success-message strong { display: block; font-size: 1.15em; margin-bottom: 8px; }
.success-message .tracking-code {
    font-family: var(--font-title);
    font-size: 1.8em;
    font-weight: 700;
    color: var(--yellow);
    letter-spacing: 2px;
    margin: 12px 0;
    padding: 12px 20px;
    background: rgba(212,175,55,0.08);
    border: 1px dashed rgba(212,175,55,0.4);
    border-radius: 12px;
    display: inline-block;
}
.success-message p { color: var(--text-light); font-size: 14px; margin-top: 8px; }

.tracking-result {
    background: rgba(255,255,255,0.03);
    border-radius: 14px;
    padding: 20px;
    margin-top: 16px;
    border-left: 4px solid var(--green);
}
.tracking-result .status-badge {
    font-size: 12px;
    font-weight: 700;
    padding: 5px 14px;
    border-radius: 20px;
    display: inline-block;
    margin-bottom: 14px;
}
.status-in-attesa { background: rgba(212,175,55,0.15); color: var(--yellow); }
.status-in-lavorazione { background: rgba(212, 170, 87,0.15); color: var(--cyan); }
.status-risolto { background: rgba(60,207,145,0.15); color: var(--green); }
.status-archiviato { background: rgba(255,255,255,0.05); color: var(--text-muted); }
.tracking-result p {
    color: var(--text-light);
    font-size: 13px;
    margin-bottom: 8px;
    line-height: 1.7;
}
.tracking-result p strong { color: #fff; font-weight: 600; }
.tracking-message {
    background: rgba(255,255,255,0.03);
    border-radius: 10px;
    padding: 12px 14px;
    margin-top: 12px;
    color: var(--text-light);
    font-size: 13px;
    line-height: 1.7;
    border-left: 2px solid var(--cyan);
}
.tracking-error {
    background: rgba(232,97,75,0.1);
    border: 1px solid rgba(232,97,75,0.3);
    border-radius: 12px;
    padding: 14px 18px;
    color: var(--pink);
    font-size: 13px;
    margin-top: 16px;
    display: flex;
    align-items: center;
    gap: 10px;
}

.card .info-box { max-width: none; margin-left: 0; margin-right: 0; }

/* LEGEND */
.legend-section {
    max-width: 1100px;
    margin: 40px auto 0;
}
.legend-card {
    background: var(--bg-card);
    border: 1px solid var(--border);
    border-radius: 22px;
    padding: 32px;
    position: relative;
    overflow: hidden;
}
.legend-card::before {
    content: "";
    position: absolute;
    top: 0; left: 0; right: 0;
    height: 3px;
    background: var(--gradient-full);
}
.legend-header { text-align: center; margin-bottom: 28px; }
.legend-header h2 {
    font-family: var(--font-title);
    font-size: 1.6em;
    font-weight: 700;
    margin-bottom: 8px;
    background: var(--gradient-full);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
}
.legend-header p { color: var(--text-light); font-size: 14px; }

.legend-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 16px;
}
.legend-item {
    background: rgba(255,255,255,0.02);
    border: 1px solid var(--border);
    border-radius: 16px;
    padding: 20px 16px;
    text-align: center;
    transition: transform 0.2s, border-color 0.2s;
}
.legend-item:hover {
    border-color: var(--border-hover);
    transform: translateY(-3px);
}
.legend-icon {
    width: 32px;
    height: 32px;
    margin-bottom: 10px;
    display: block;
    line-height: 1;
}
.legend-icon svg { width: 100%; height: 100%; }
.legend-status {
    font-family: var(--font-title);
    font-size: 0.95em;
    font-weight: 700;
    margin-bottom: 6px;
    padding: 4px 12px;
    border-radius: 12px;
    display: inline-block;
}
.legend-status.in-attesa { background: rgba(212,175,55,0.15); color: var(--yellow); }
.legend-status.in-lavorazione { background: rgba(212, 170, 87,0.15); color: var(--cyan); }
.legend-status.risolto { background: rgba(60,207,145,0.15); color: var(--green); }
.legend-status.archiviato { background: rgba(255,255,255,0.05); color: var(--text-muted); }
.legend-desc {
    color: var(--text-light);
    font-size: 12px;
    line-height: 1.5;
    margin-top: 8px;
}

.legend-divider {
    text-align: center;
    margin: 28px 0 20px;
    color: var(--text-muted);
    font-size: 12px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 2px;
}

.legend-types {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 12px;
}
.legend-type {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 14px 16px;
    background: rgba(255,255,255,0.02);
    border: 1px solid var(--border);
    border-radius: 12px;
    font-size: 13px;
    color: var(--text-light);
}
.legend-type-icon {
    width: 36px;
    height: 36px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 18px;
    flex-shrink: 0;
}
.legend-type-icon svg { width: 18px; height: 18px; }
.legend-type-icon.t-assistenza { background: rgba(212, 170, 87,0.15); }
.legend-type-icon.t-consigli { background: rgba(212,175,55,0.15); }
.legend-type-icon.t-bug { background: rgba(232,97,75,0.15); }
.legend-type-icon.t-suggerimento { background: rgba(60,207,145,0.15); }
.legend-type-icon.t-reclamo { background: rgba(232,169,60,0.15); }
.legend-type-icon.t-altro { background: rgba(200, 127, 42,0.15); }
.legend-type strong {
    display: block;
    color: #fff;
    font-size: 13px;
    font-weight: 600;
    margin-bottom: 2px;
}
.legend-type span { font-size: 12px; color: var(--text-muted); }

@media (max-width: 1024px) {
    .contact-grid { grid-template-columns: 1fr; max-width: 700px; }
    .legend-grid { grid-template-columns: repeat(2, 1fr); }
    .legend-types { grid-template-columns: repeat(2, 1fr); }
}
@media (max-width: 768px) {
    .card { padding: 24px; }
    .card h2 { font-size: 1.3em; }
    .tracking-form { flex-direction: column; }
    .tracking-form button { width: 100%; justify-content: center; }
    .legend-card { padding: 24px; }
    .legend-header h2 { font-size: 1.3em; }
    .legend-grid { grid-template-columns: 1fr 1fr; }
    .legend-types { grid-template-columns: 1fr; }
}
@media (max-width: 480px) {
    .card { padding: 20px; }
    .success-message .tracking-code { font-size: 1.3em; }
    .legend-grid { grid-template-columns: 1fr; }
}
';

    // CSS condiviso delle pagine informative + quello di questa sezione.

    $extraCSS = (string)($extraCSS ?? '') . $__info_css;


    lux_header();
    ?>


<main>
    <section class="info-hub">
        <?php lux_info_nav('contattaci'); ?>
    </section>


    <!-- Hero -->
    <section class="hero">
        <div class="container">
            <div class="hero-badge">
                <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path><polyline points="22,6 12,13 2,6"></polyline></svg>
                <?= t('Contacts') ?>
            </div>
            <h1><?= t('Contact Us') ?></h1>
            <p><?= t('Send a message, request information or report a problem. Our team is always available to help you.') ?></p>
        </div>
    </section>

    <!-- Main Section -->
    <section class="main-section">
        <div class="container">
            <div class="contact-grid">

                <?php if (isset($_GET['success']) && isset($_GET['code'])): ?>
                <div class="success-message">
                    <strong><?= t('Message sent successfully!') ?></strong>
                    <?= t('Your tracking code is:') ?>
                    <div class="tracking-code"><?php echo htmlspecialchars($_GET['code']); ?></div>
                    <p><?= t('Save this code: you will need it to track the status of your message at any time.') ?></p>
                </div>
                <?php endif; ?>

                <!-- Message form -->
                <div class="card">
                    <h2>
                        <span class="card-icon">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path></svg>
                        </span>
                        <?= t('Send a Message') ?>
                    </h2>
                    <form method="POST" class="support-form">
                        <?php lux_support_console(['form' => 'contatti']); ?>
                        <div class="form-group">
                            <label><?= t('Full name *') ?></label>
                            <input type="text" name="name" placeholder="<?= t('Ex. Mario Rossi') ?>" required>
                        </div>
                        <div class="form-group">
                            <label><?= t('Email *') ?></label>
                            <input type="email" name="email" placeholder="<?= t('Ex. mario.rossi@email.com') ?>" required>
                        </div>
                        <div class="form-group">
                            <label><?= t('Project *') ?></label>
                            <select name="project" required>
                                <option value=""><?= t('Select a project...') ?></option>
                                <option value="Generale"><?= t('General') ?></option>
                                <option value="LUX.Text">LUX.Text</option>
                                <option value="LUX.Chroma">LUX.Chroma</option>
                                <option value="LUX.Info">LUX.Info</option>
                                <option value="LUX.Plan">LUX.Plan</option>
                                <option value="LUX.Care">LUX.Care</option>
                                <option value="LUX.Mail">LUX.Mail</option>
                                <option value="LUX.Mind">LUX.Mind</option>
                                <option value="LUX.Quiz">LUX.Quiz</option>
                                <option value="LUX.Versus">LUX.Versus</option>
                                <option value="LUX.Code">LUX.Code</option>
                                <option value="LUX.Name">LUX.Name</option>
                                <option value="LUX.Rewrite">LUX.Rewrite</option>
                                <option value="LUX.Explain">LUX.Explain</option>
                                <option value="LUX.Short">LUX.Short</option>
                                <option value="LUX.Translate">LUX.Translate</option>
                                <option value="LUX.Recipe">LUX.Recipe</option>
                                <option value="LUX.Parlare">LUX.Parlare</option>
                                <option value="LUX.Games">LUX.Games</option>
                                <option value="LUX.Schedule">LUX.Schedule</option>
                                <option value="LUX.Music">LUX.Music</option>
                                <option value="LUX.Journal">LUX.Journal</option>
                                <option value="LUX.Bot">LUX.Bot</option>
                                <option value="LUX.Calc">LUX.Calc</option>
                                <option value="LUX.Voice">LUX.Voice</option>
                                <option value="LUX.Tap">LUX.Tap</option>
                                <option value="Altro"><?= t('Other') ?></option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label><?= t('Type *') ?></label>
                            <select name="type" required>
                                <option value=""><?= t('Select a type...') ?></option>
                                <option value="Assistenza"><?= t('Assistance') ?></option>
                                <option value="Consigli"><?= t('Advice') ?></option>
                                <option value="Bug"><?= t('Bug') ?></option>
                                <option value="Suggerimento"><?= t('Suggestion') ?></option>
                                <option value="Reclamo"><?= t('Complaint') ?></option>
                                <option value="Altro"><?= t('Other') ?></option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label><?= t('Quanto è urgente?') ?></label>
                            <select name="urgency">
                                <option value="bassa"><?= t('Bassa · posso aspettare') ?></option>
                                <option value="media" selected><?= t('Media · mi crea problemi') ?></option>
                                <option value="alta"><?= t('Alta · mi blocca il lavoro') ?></option>
                                <option value="bloccante"><?= t('Bloccante · non posso usare nulla') ?></option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label><?= t('Message *') ?></label>
                            <textarea name="message" rows="5" placeholder="<?= t('Write your message in detail...') ?>" required></textarea>
                        </div>
                        <button type="submit" name="submit_message" class="btn-primary">
                            <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="22" y1="2" x2="11" y2="13"></line><polygon points="22 2 15 22 11 13 2 9 22 2"></polygon></svg>
                            <?= t('Send Message') ?>
                        </button>
                    </form>
                    <div class="info-box">
                        <p><strong><?= t('Note:') ?></strong> <?= t('you will receive a unique alphanumeric tracking code. Keep it: you will need it to check the status of your message and receive updates.') ?></p>
                    </div>

                    <?php lux_support_console_js(['form' => 'contatti']); ?>
                </div>

                <!-- Tracking -->
                <div class="card">
                    <h2>
                        <span class="card-icon">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
                        </span>
                        <?= t('Track your Message') ?>
                    </h2>
                    <p style="color: var(--text-light); font-size: 14px; margin-bottom: 18px;"><?= t('Enter the tracking code received when submitting to check the status of your message.') ?></p>

                    <form method="GET" class="tracking-form">
                        <input type="text" name="track" placeholder="<?= t('Ex. A1B2C3D4') ?>" value="<?php echo htmlspecialchars($_GET['track'] ?? ''); ?>" required>
                        <button type="submit"><?= t('Track') ?></button>
                    </form>

                    <?php if ($trackingResult): ?>
                        <?php
                        $statusRaw = $trackingResult['status'] ?? 'In Attesa';
                        $statusClass = 'status-in-attesa';
                        if ($statusRaw === 'In Lavorazione') $statusClass = 'status-in-lavorazione';
                        elseif ($statusRaw === 'Risolto') $statusClass = 'status-risolto';
                        elseif ($statusRaw === 'Archiviato') $statusClass = 'status-archiviato';
                        $statusText = $statusLabels[$statusRaw] ?? $statusRaw;
                        $projectText = $projectLabels[$trackingResult['project'] ?? ''] ?? ($trackingResult['project'] ?? '');
                        $typeText = $typeLabels[$trackingResult['type'] ?? ''] ?? ($trackingResult['type'] ?? '');
                        ?>
                        <div class="tracking-result">
                            <span class="status-badge <?php echo $statusClass; ?>"><?php echo htmlspecialchars($statusText); ?></span>
                            <p><strong><?= t('Code:') ?></strong> <?php echo htmlspecialchars($trackingResult['code'] ?? ''); ?></p>
                            <p><strong><?= t('Name:') ?></strong> <?php echo htmlspecialchars($trackingResult['name'] ?? ''); ?></p>
                            <p><strong><?= t('Project:') ?></strong> <?php echo htmlspecialchars($projectText); ?></p>
                            <p><strong><?= t('Type:') ?></strong> <?php echo htmlspecialchars($typeText); ?></p>
                            <p><strong><?= t('Date:') ?></strong> <?php echo date('d/m/Y H:i', strtotime($trackingResult['date'] ?? 'now')); ?></p>
                            <div class="tracking-message">
                                <?php echo nl2br(htmlspecialchars($trackingResult['message'] ?? '')); ?>
                            </div>

                            <?php
                            // --- scheda tecnica: triage, diagnostica e cronologia ---
                            $__ticketTimeline = lux_support_timeline($trackingResult);
                            $__diag = (array)($trackingResult['diagnostics'] ?? []);
                            ?>
                            <div class="sup-ticket" id="supTicketText">
                                <p><strong><?= t('Triage:') ?></strong>
                                    <?= htmlspecialchars((string)($trackingResult['priority_label'] ?? ($trackingResult['priority'] ?? t('non disponibile per questa richiesta')))) ?>
                                    <?php if (!empty($trackingResult['sla_hours'])): ?>
                                        · <?= t('risposta prevista entro {h} ore', ['h' => (int)$trackingResult['sla_hours']]) ?>
                                    <?php endif; ?>
                                    <?php if (!empty($trackingResult['score'])): ?>
                                        · <?= t('completezza dichiarata: {n}%', ['n' => (int)$trackingResult['score']]) ?>
                                    <?php endif; ?>
                                </p>
                                <?php if (!empty($trackingResult['reasons'])): ?>
                                    <p><strong><?= t('Da completare:') ?></strong> <?= htmlspecialchars(implode(' · ', array_map('strval', (array)$trackingResult['reasons']))) ?></p>
                                <?php endif; ?>
                                <?php if ($__diag): ?>
                                    <p><strong><?= t('Diagnostica acquisita automaticamente:') ?></strong>
                                        <?php
                                        $__diagRows = [];
                                        foreach ($__diag as $__k => $__v) {
                                            $__diagRows[] = $__k . '=' . $__v;
                                        }
                                        echo htmlspecialchars(implode(' · ', array_slice($__diagRows, 0, 10)));
                                        ?>
                                    </p>
                                <?php endif; ?>
                                <?php if (!empty($trackingResult['queue_position'])): ?>
                                    <p><strong><?= t('Posizione in coda:') ?></strong> <?= (int)$trackingResult['queue_position'] ?></p>
                                <?php endif; ?>
                                <p><strong><?= t('Cronologia:') ?></strong></p>
                                <ul class="sup-timeline">
                                    <?php foreach ($__ticketTimeline as $__step): ?>
                                        <li>
                                            <span class="when"><?= htmlspecialchars((string)$__step['when']) ?></span>
                                            <span class="what"><b><?= htmlspecialchars((string)$__step['label']) ?></b>
                                                <?php if ((string)$__step['note'] !== ''): ?><span><?= htmlspecialchars((string)$__step['note']) ?></span><?php endif; ?>
                                            </span>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                            <button type="button" class="sup-btn small" id="supDownload" data-code="<?= htmlspecialchars((string)($trackingResult['code'] ?? '')) ?>">
                                <?= icon('download', 14) ?>
                                <span><?= t('Scarica il ticket') ?></span>
                            </button>
                        </div>
                    <?php elseif ($trackingError): ?>
                        <div class="tracking-error">
                            <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><line x1="15" y1="9" x2="9" y2="15"></line><line x1="9" y1="9" x2="15" y2="15"></line></svg>
                            <?php echo htmlspecialchars($trackingError); ?>
                        </div>
                    <?php endif; ?>

                    <div class="info-box" style="margin-top: 24px;">
                        <p><strong><?= t('Response times:') ?></strong> <?= t('our team tries to answer all messages within 24-48 hours. During peak periods, times may lengthen slightly.') ?></p>
                    </div>
                </div>

            </div>

            <!-- LEGEND -->
            <div class="legend-section">
                <div class="legend-card">
                    <div class="legend-header">
                        <h2><?= t('Legend') ?></h2>
                        <p><?= t('Understand at a glance the status and type of your requests') ?></p>
                    </div>

                    <!-- Statuses -->
                    <div class="legend-grid">
                        <div class="legend-item">
                            <span class="legend-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="22 12 16 12 14 15 10 15 8 12 2 12"></polyline><path d="M5.45 5.11L2 12v6a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2v-6l-3.45-6.89A2 2 0 0 0 16.76 4H7.24a2 2 0 0 0-1.79 1.11z"></path></svg></span>
                            <div class="legend-status in-attesa"><?= t('Pending') ?></div>
                            <div class="legend-desc"><?= t('Your request has been received and is in the queue to be taken care of.') ?></div>
                        </div>
                        <div class="legend-item">
                            <span class="legend-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="23 4 23 10 17 10"></polyline><polyline points="1 20 1 14 7 14"></polyline><path d="M3.51 9a9 9 0 0 1 14.85-3.36L23 10M1 14l4.64 4.36A9 9 0 0 0 20.49 15"></path></svg></span>
                            <div class="legend-status in-lavorazione"><?= t('In Progress') ?></div>
                            <div class="legend-desc"><?= t('An operator is currently examining your request.') ?></div>
                        </div>
                        <div class="legend-item">
                            <span class="legend-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg></span>
                            <div class="legend-status risolto"><?= t('Resolved') ?></div>
                            <div class="legend-desc"><?= t('Your request has been resolved. The case is closed successfully.') ?></div>
                        </div>
                        <div class="legend-item">
                            <span class="legend-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="21 8 21 21 3 21 3 8"></polyline><rect x="1" y="3" width="22" height="5"></rect><line x1="10" y1="12" x2="14" y2="12"></line></svg></span>
                            <div class="legend-status archiviato"><?= t('Archived') ?></div>
                            <div class="legend-desc"><?= t('The request has been archived. It is no longer active.') ?></div>
                        </div>
                    </div>

                    <!-- Types -->
                    <div class="legend-divider"><?= t('Request types') ?></div>

                    <div class="legend-types">
                        <div class="legend-type">
                            <div class="legend-type-icon t-assistenza"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.77-3.77a6 6 0 0 1-7.94 7.94l-6.91 6.91a2.12 2.12 0 0 1-3-3l6.91-6.91a6 6 0 0 1 7.94-7.94l-3.76 3.76z"></path></svg></div>
                            <div>
                                <strong><?= t('Assistance') ?></strong>
                                <span><?= t('Technical help request') ?></span>
                            </div>
                        </div>
                        <div class="legend-type">
                            <div class="legend-type-icon t-consigli"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 18h6"></path><path d="M10 22h4"></path><path d="M12 2a7 7 0 0 0-4 12.7c.6.5 1 1.3 1 2.1V17h6v-.2c0-.8.4-1.6 1-2.1A7 7 0 0 0 12 2z"></path></svg></div>
                            <div>
                                <strong><?= t('Advice') ?></strong>
                                <span><?= t('Suggestions and opinions') ?></span>
                            </div>
                        </div>
                        <div class="legend-type">
                            <div class="legend-type-icon t-bug"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="8" y="2" width="8" height="4" rx="1"></rect><path d="M8 5H4a2 2 0 0 0 0 4h4"></path><path d="M16 5h4a2 2 0 0 1 0 4h-4"></path><path d="M8 9H3v2a3 3 0 0 0 3 3h2v1c0 3.9 2.2 7 5 7s5-3.1 5-7v-1h2a3 3 0 0 0 3-3V9h-5z"></path><line x1="5" y1="14" x2="4" y2="16"></line><line x1="19" y1="14" x2="20" y2="16"></line></svg></div>
                            <div>
                                <strong><?= t('Bug') ?></strong>
                                <span><?= t('Malfunction reporting') ?></span>
                            </div>
                        </div>
                        <div class="legend-type">
                            <div class="legend-type-icon t-suggerimento"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2l2.4 7.6L22 12l-7.6 2.4L12 22l-2.4-7.6L2 12l7.6-2.4z"></path></svg></div>
                            <div>
                                <strong><?= t('Suggestion') ?></strong>
                                <span><?= t('Ideas for new features') ?></span>
                            </div>
                        </div>
                        <div class="legend-type">
                            <div class="legend-type-icon t-reclamo"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"></path><line x1="12" y1="9" x2="12" y2="13"></line><line x1="12" y1="17" x2="12.01" y2="17"></line></svg></div>
                            <div>
                                <strong><?= t('Complaint') ?></strong>
                                <span><?= t('Dissatisfaction reporting') ?></span>
                            </div>
                        </div>
                        <div class="legend-type">
                            <div class="legend-type-icon t-altro"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="21 8 21 21 3 21 3 8"></polyline><rect x="1" y="3" width="22" height="5"></rect><line x1="10" y1="12" x2="14" y2="12"></line></svg></div>
                            <div>
                                <strong><?= t('Other') ?></strong>
                                <span><?= t('Other types of requests') ?></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- CTA -->
            <div class="cta-box">
                <h3><?= t('Need urgent help?') ?></h3>
                <p><?= t('If you have an urgent technical problem, visit our Support page for faster and dedicated assistance.') ?></p>
                <div class="cta-buttons">
                    <a href="assistenza.php" class="btn-primary">
                        <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path></svg>
                        <?= t('Go to Support') ?>
                    </a>
                    <a href="faq.php" class="btn-secondary">
                        <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"></path><line x1="12" y1="17" x2="12.01" y2="17"></line></svg>
                        <?= t('Consult the FAQ') ?>
                    </a>
                </div>
            </div>

        </div>
    </section>


                <div class="lux-block">
                    <h2><?= t('Quale canale scegliere, per non perdere tempo') ?></h2>
                    <p><?= t('Non tutti i messaggi passano dallo stesso posto. Scegliere il canale giusto significa ricevere una risposta utile al primo tentativo.') ?></p>
                    <div class="lux-steps">
                        <div class="lux-step">
                            <b><?= t('Serve aiuto su un problema') ?></b>
                            <h3><?= t('Assistenza') ?></h3>
                            <p><?= t('La pagina con triage, cronologia e priorità: qui il problema viene riprodotto e tracciato fino alla soluzione.') ?></p>
                        </div>
                        <div class="lux-step">
                            <b><?= t('Vuoi dire qualcosa') ?></b>
                            <h3><?= t('Questo modulo') ?></h3>
                            <p><?= t('Informazioni, opinioni, proposte, collaborazioni: qualsiasi messaggio che non sia un guasto tecnico.') ?></p>
                        </div>
                        <div class="lux-step">
                            <b><?= t('Vuoi sapere se è tutto a posto') ?></b>
                            <h3><?= t('Stato dei sistemi') ?></h3>
                            <p><?= t('Prima di scrivere, dai un\'occhiata al cruscotto: se c\'è un guasto noto lo trovi lì, con orario e dettagli.') ?></p>
                        </div>
                        <div class="lux-step">
                            <b><?= t('Vuoi le novità') ?></b>
                            <h3><?= t('Bacheca') ?></h3>
                            <p><?= t('Aggiornamenti, manutenzioni e annunci: tutto quello che riguarda il progetto viene pubblicato qui.') ?></p>
                        </div>
                    </div>
                </div>

                <div class="lux-block">
                    <h2><?= t('Che cosa mettere in un messaggio') ?></h2>
                    <ul class="lux-list">
                        <li><?= t('Un oggetto concreto: «il pulsante di copia non funziona su Android» è meglio di «problema».') ?></li>
                        <li><?= t('Il contesto: da dove hai aperto il sito, con quale dispositivo e in quale lingua.') ?></li>
                        <li><?= t('Un recapito reale: se scrivi un indirizzo sbagliato non possiamo risponderti, e il ticket resta fermo.') ?></li>
                        <li><?= t('Un solo argomento per messaggio: due problemi diversi in un unico testo si risolvono più lentamente.') ?></li>
                        <li><?= t('Nessun dato sensibile: non inviare password, documenti, numeri di carte o informazioni di altre persone.') ?></li>
                    </ul>
                </div>

                <div class="lux-block">
                    <h2><?= t('Cosa succede dopo che premi Invia') ?></h2>
                    <p><?= t('Il messaggio diventa un ticket con un codice di otto caratteri: quello è la tua chiave per rileggere tutto in qualsiasi momento dalla pagina di tracciamento, senza account.') ?></p>
                    <table class="lux-table">
                        <thead><tr><th><?= t('Passo') ?></th><th><?= t('Cosa vedi') ?></th></tr></thead>
                        <tbody>
                            <tr><td><?= t('Subito') ?></td><td><?= t('Il codice di tracciamento e la pagina di conferma con priorità e tempo di risposta previsto.') ?></td></tr>
                            <tr><td><?= t('In coda') ?></td><td><?= t('Nella scheda del ticket trovi la posizione, la diagnostica tecnica allegata e la cronologia degli aggiornamenti.') ?></td></tr>
                            <tr><td><?= t('Alla risposta') ?></td><td><?= t('Ricevi una email all\'indirizzo indicato; se serve un chiarimento te lo chiediamo lì, nello stesso ticket.') ?></td></tr>
                            <tr><td><?= t('Alla chiusura') ?></td><td><?= t('Lo stato passa a Risolto e resti libero di rileggere la conversazione per due anni.') ?></td></tr>
                        </tbody>
                    </table>
                </div>
</main>


    <?php
    lux_footer();
}


// ============================================================
// SEZIONE: linea-del-tempo   (nuova pagina /linea-del-tempo.php)
// ============================================================
// La cronologia del progetto prima viveva dentro Chi Siamo. Ora ha una
// pagina sua, con tutte le tappe dall'idea iniziale a oggi e un blocco
// finale che si aggiorna da solo leggendo gli avvisi pubblicati in bacheca:
// quando rilasciamo qualcosa di importante, questa pagina lo mostra senza
// toccare il codice.
// ============================================================
if ($__info === 'linea-del-tempo') {

    $isIt = currentLang() === 'it';

    $pageTitle       = t('Linea del Tempo — L.U.X. Verse');
    $pageDescription = t('The story of L.U.X. Verse step by step: from the first idea to MEGA UPDATE 4.0, with 100,000 tools and every release that changed the ecosystem.');
    $pageKeywords    = 'linea del tempo, timeline, storia, aggiornamenti, MEGA UPDATE 4.0, 100.000 strumenti, L.U.X. Verse';
    $pageOgType      = 'website';

    // ---------- LE TAPPE ----------
    // Ogni voce: data, titolo, racconto e (quando serve) l'elenco delle cose
    // fatte. Le tappe storiche riusano le stesse traduzioni di Chi Siamo.
    $__ltl = [
        [
            'date'  => t('12 October 2026'),
            'title' => t('MEGA UPDATE 4.0 — 100,000 tools and a new control panel'),
            'tag'   => 'v4.0',
            'now'   => true,
            'text'  => t('The catalog jumps from 1,000 to 100,000 tools without adding a single file, and the administration panel is rewritten from scratch as a real control centre. Notifications are born here and reach every open page in seconds.'),
            'items' => [
                t('<strong>From 1,000 to 100,000 tools</strong>: the catalog is no longer a list, it is a multiplication. 44 actions, 45 fields and 50 output styles give 99,000 tools that are calculated on the spot, plus the 1,000 written by hand: 100,000 in total, with no extra file in the project.'),
                t('<strong>Every tool has its own page</strong>: a unique address (for example /riassumi-email-flash.php), its own name, its own description in Italian and English and its own AI instruction. Nothing is a copy of something else.'),
                t('<strong>Search over 100,000 tools</strong>: the server searches and sends 24 results at a time. The home page shows the first ones immediately and loads the rest as you scroll, without slowing down.'),
                t('<strong>The control panel, rebuilt from scratch</strong>: one page with sections that load only when you open them, KPI cards, a 14-day chart, tables with search and filters, everything usable from a phone. No more page reloads for every click.'),
                t('<strong>Global notification, one click away</strong>: write it in Italian and English, choose category, recipients (everyone, only admin, or both), redirect and even the day and time it should appear. The preview shows exactly how it will look on the site.'),
                t('<strong>The 5-second notice</strong>: when you send it, a card appears on every open page, stays five seconds with the time bar running and then goes back into the bell. Same behaviour in the panel, so you know it went out.'),
                t('<strong>Ctrl+K</strong>: a command palette to jump to a section or find a tool among the 100,000 without touching the mouse.'),
                t('<strong>One API for the whole panel</strong>: every action goes through a single endpoint, protected by the session and by the origin check.'),
            ],
        ],
        [
            'date'  => t('12 October 2026'),
            'title' => t('MEGA UPDATE 3.0 — the whole ecosystem, renewed'),
            'tag'   => 'v3.0',
            'text'  => t('The biggest release in the history of L.U.X. Verse lands today: new identity, new design, global notifications, a Timeline page and a cleaner Android app. This is everything that changed, in order.'),
            'items' => [
                t('<strong>Global notifications</strong>: the bell at the top right of the home page lights up with a red dot the moment there is something new, without reloading the page.'),
                t('<strong>A notification for everything</strong>: name, content, category, redirect and recipients. The admin can send it to everyone or keep it internal; automatic notifications are written by the system (new notice on the board, new project, status change, new message, problem reported by monitoring).'),
                t('<strong>The bell, refined</strong>: only the red dot, no counters and no confusing zero. The sign appears when there is something to read and disappears once you have read it.'),
                t('<strong>This Timeline page</strong>: the whole story of the project leaves About Us and becomes a page of its own, updated with every important release.'),
                t('<strong>Status in the navigation</strong>: the systems dashboard is now one click away, both in the top bar and in the mobile menu.'),
                t('<strong>Android app 3.8</strong>: fixed the bug that kept the top of the pages stuck, preventing them from loading from the top. The app is connected to the live site, so every update is immediate.'),
                t('<strong>One file for every thing</strong>: information pages, APIs, translations, icons and tools are gathered in a handful of files, with every public address unchanged.'),
                t('<strong>The old file manager has been removed for good</strong>: fewer ways in for those who should not have them.'),
            ],
        ],
        [
            'date'  => t('11 — 12 October 2026'),
            'title' => t('LUX Neon: the new face of the ecosystem'),
            'tag'   => 'v3.0',
            'text'  => t('The whole interface is rewritten from scratch: graphite, bone and gold, vector icons instead of emoji, a new header, a new mobile menu, a new footer and a new loading screen. And the old brand disappears: all 100,000 projects take the L.U.X. name, one by one, without breaking a single link.'),
            'items' => [
                t('<strong>New design</strong>: dark, essential and professional, with gold as the only accent and no emoji anywhere in the text.'),
                t('<strong>New icon engine</strong>: vector icons drawn in the page, no icon font to download, the same look on every device.'),
                t('<strong>Hundreds of pages rebuilt</strong>: index, board, information pages, tools, admin panel and email templates, all in the new language.'),
            ],
        ],
        [
            'date'  => t('11 ottobre 2026'),
            'title' => t('Quota 100.000 strumenti'),
            'text'  => t("Nelle stesse ore vengono rilasciati altri 900 strumenti: il catalogo arriva a 100.000 progetti, in meno di 24 ore. Un traguardo costruito unendo gli strumenti storici a quelli creati automaticamente dal catalogo."),
        ],
        [
            'date'  => t('11 ottobre 2026'),
            'title' => t('70 progetti in un giorno'),
            'text'  => t("In un solo giorno vengono aggiunti 70 nuovi progetti: il catalogo passa a 100 strumenti e l'ecosistema supera la soglia delle cento voci, tutte documentate e raggiungibili dall'indice."),
        ],
        [
            'date'  => t('11 ottobre 2026'),
            'title' => t('Su Google e negli AI dei motori di ricerca'),
            'text'  => t("L.U.X. Verse compare sugli indici e negli assistenti AI dei motori di ricerca: cercando il nome dell'ecosistema si viene portati alle sue pagine. Da progetto privato a qualcosa che chiunque può trovare."),
        ],
        [
            'date'  => t('10 ottobre 2026'),
            'title' => t('Il progetto riprende'),
            'text'  => t("Dopo una lunga pausa il progetto torna in vita. In meno di 24 ore il sito viene sistemato da cima a fondo e l'app Android viene completata, con un collegamento diretto al sito: chi la installa arriva subito all'ecosistema."),
        ],
        [
            'date'  => t('September 2026'),
            'title' => t('MEGA UPDATE 2.0'),
            'text'  => t('A landmark update completely renews the user experience. New design, new features, new tools, smarter and faster systems. L.U.X. Verse officially becomes a mature and complete digital ecosystem.'),
        ],
        [
            'date'  => t('Summer 2026'),
            'title' => t('The expansion of the ecosystem'),
            'text'  => t("The ecosystem expands rapidly. New projects are added in the educational, productive, creative and entertainment fields. The community begins to grow and provide valuable feedback."),
        ],
        [
            'date'  => t('Spring 2026'),
            'title' => t('The first AI tools'),
            'text'  => t('The first tools based on artificial intelligence are developed: text generators, translators, creative assistants. Every tool is tested, refined and made available for free to anyone.'),
        ],
        [
            'date'  => t('Early 2026'),
            'title' => t('The birth of the idea'),
            'text'  => t('It all starts with a simple idea: to create a free digital tool able to help people in their daily activities. The first prototype takes shape, laying the foundations of what would become an entire ecosystem.'),
        ],
        [
            'date'  => t('The future'),
            'title' => t('Toward new frontiers'),
            'text'  => t("The journey of L.U.X. Verse has just begun. New projects, new features and new ideas are already underway. The goal remains the same: to make technology accessible, useful and enjoyable for everyone."),
        ],
    ];

    // Blocco "in diretta": gli ultimi avvisi pubblicati in bacheca.
    $__ltlNotices = getNoticesFromLocal(4);

    $extraCSS = <<<'LUXLTLCSS'
.ltl-section { padding: 26px 0 70px; }

.ltl-rail { position: relative; padding-left: 34px; }
.ltl-rail::before {
    content: '';
    position: absolute;
    left: 9px; top: 8px; bottom: 12px; width: 2px;
    background: linear-gradient(180deg, var(--gold) 0%, rgba(216,169,60,.20) 46%, rgba(216,169,60,.04) 100%);
}
.ltl-item { position: relative; margin-bottom: 34px; }
.ltl-item::before {
    content: '';
    position: absolute;
    left: -32px; top: 6px;
    width: 11px; height: 11px;
    border-radius: 50%;
    background: var(--ink-2);
    border: 2px solid var(--gold);
    box-sizing: border-box;
}
.ltl-item.is-now::before { background: var(--gold); box-shadow: 0 0 0 5px rgba(216,169,60,.14); }

.ltl-date {
    display: flex; align-items: center; gap: 10px; flex-wrap: wrap;
    font-family: var(--font-mono);
    font-size: 10.5px; letter-spacing: .16em; text-transform: uppercase;
    color: var(--text-muted);
}
.ltl-now {
    font-family: var(--font-mono);
    font-size: 10px; letter-spacing: .12em; text-transform: uppercase;
    color: var(--gold);
    background: rgba(216,169,60,.08);
    border: 1px solid rgba(216,169,60,.38);
    border-radius: 999px;
    padding: 3px 9px;
}
.ltl-title {
    font-family: var(--font-title);
    font-size: 1.16em; font-weight: 600; line-height: 1.35;
    color: var(--bone);
    margin: 8px 0 6px;
}
.ltl-tag {
    display: inline-flex; align-items: center;
    font-family: var(--font-mono);
    font-size: 10.5px; letter-spacing: .1em; text-transform: uppercase;
    color: var(--gold-light);
    background: var(--gold-soft);
    border: 1px solid rgba(216,169,60,.28);
    border-radius: 6px;
    padding: 3px 8px;
    margin-bottom: 10px;
}
.ltl-text { color: var(--text-light); font-size: 15px; line-height: 1.75; }
.ltl-list { list-style: none; margin: 12px 0 0; padding: 0; display: flex; flex-direction: column; gap: 9px; }
.ltl-list li { position: relative; padding-left: 18px; color: var(--text-light); font-size: 14.5px; line-height: 1.7; }
.ltl-list li::before {
    content: ''; position: absolute; left: 2px; top: 9px;
    width: 6px; height: 6px; border-radius: 50%; background: var(--gold);
}
.ltl-list strong { color: var(--bone); font-weight: 600; }

.ltl-live {
    margin-top: 18px;
    border: 1px solid var(--border);
    border-radius: var(--radius-lg);
    background: rgba(255,255,255,.02);
    padding: 22px 24px 18px;
}
.ltl-live h2 { font-family: var(--font-title); font-size: 1.05em; color: var(--bone); margin-bottom: 6px; }
.ltl-live > p { color: var(--text-light); font-size: 14.5px; line-height: 1.7; }
.ltl-live-list { list-style: none; margin: 14px 0 6px; padding: 0; }
.ltl-live-list li {
    display: flex; gap: 14px; align-items: baseline;
    padding: 11px 0;
    border-top: 1px solid var(--line);
}
.ltl-live-list .when {
    font-family: var(--font-mono); font-size: 10.5px; letter-spacing: .08em; text-transform: uppercase;
    color: var(--text-muted); min-width: 92px; flex: 0 0 auto;
}
.ltl-live-list .what { color: var(--text); font-size: 14.5px; font-weight: 500; }
.ltl-live-list .what a { color: inherit; text-decoration: none; }
.ltl-live-list .what a:hover { color: var(--gold); }

@media (max-width: 640px) {
    .ltl-rail { padding-left: 26px; }
    .ltl-item::before { left: -25px; }
    .ltl-live-list li { flex-direction: column; gap: 4px; }
    .ltl-live-list .when { min-width: 0; }
}
LUXLTLCSS;

    lux_header();
    ?>

<main>
    <section class="info-hub">
        <?php lux_info_nav('linea-del-tempo'); ?>
    </section>

    <section class="hero">
        <div class="container">
            <div class="hero-badge">
                <?= icon('time', 13) ?>
                <?= t('Cronologia') ?>
            </div>
            <h1><?= t('Linea del Tempo') ?></h1>
            <p class="hero-lead"><?= t('Every important release of the ecosystem, in order: what changed, when, and why. This page grows with the project.') ?></p>
        </div>
    </section>

    <section class="ltl-section">
        <div class="container">

            <div class="ltl-rail">
                <?php foreach ($__ltl as $__step): ?>
                <article class="ltl-item<?= !empty($__step['now']) ? ' is-now' : '' ?>">
                    <div class="ltl-date">
                        <span><?= htmlspecialchars($__step['date']) ?></span>
                        <?php if (!empty($__step['now'])): ?><span class="ltl-now"><?= t('Oggi') ?></span><?php endif; ?>
                    </div>
                    <h2 class="ltl-title"><?= htmlspecialchars($__step['title']) ?></h2>
                    <?php if (!empty($__step['tag'])): ?>
                    <span class="ltl-tag"><?= htmlspecialchars($__step['tag']) ?></span>
                    <?php endif; ?>
                    <p class="ltl-text"><?= $__step['text'] ?></p>
                    <?php if (!empty($__step['items'])): ?>
                    <ul class="ltl-list">
                        <?php foreach ($__step['items'] as $__li): ?>
                        <li><?= $__li ?></li>
                        <?php endforeach; ?>
                    </ul>
                    <?php endif; ?>
                </article>
                <?php endforeach; ?>
            </div>

            <div class="ltl-live">
                <h2><?= icon('board', 15) ?> <?= t('Ultimi aggiornamenti pubblicati') ?></h2>
                <p><?= t('Every important release becomes a notice on the board as well: here are the most recent ones, read live from the archive.') ?></p>

                <?php if (empty($__ltlNotices)): ?>
                <p style="margin-top:12px;"><?= t('No updates published yet.') ?></p>
                <?php else: ?>
                <ul class="ltl-live-list">
                    <?php foreach ($__ltlNotices as $__n):
                        $__nTitle = $isIt ? ($__n['title_it'] ?? ($__n['title'] ?? '')) : ($__n['title_en'] ?? ($__n['title'] ?? ''));
                        $__nDate  = !empty($__n['date']) ? date('d.m.Y', strtotime($__n['date'])) : '';
                    ?>
                    <li>
                        <span class="when"><?= htmlspecialchars($__nDate) ?></span>
                        <span class="what"><a href="/bacheca.php"><?= htmlspecialchars($__nTitle) ?></a></span>
                    </li>
                    <?php endforeach; ?>
                </ul>
                <?php endif; ?>

                <div class="cta-buttons" style="margin-top:16px;">
                    <a href="/bacheca.php" class="btn-secondary"><?= icon('board', 17) ?> <?= t('Apri la Bacheca') ?></a>
                    <a href="/status.php" class="btn-secondary"><?= icon('pulse', 17) ?> <?= t('Stato dei sistemi') ?></a>
                </div>
            </div>

        </div>
    </section>
</main>

    <?php
    lux_footer();
}


// ============================================================
// SEZIONE: status   (nuova pagina /status.php)
// ============================================================
// Telemetria vera: le sonde di includes/lux.php leggono file, dati,
// configurazione ed estensioni del server e ne misurano la latenza. Qui i
// risultati diventano un cruscotto che si aggiorna da solo ogni 30 secondi
// (chiamando ?refresh=1, che risponde in JSON).
// ============================================================
if ($__info === 'status') {

    $__rep = lux_status_report(isset($_GET['fresh']));

    // Aggiornamento in diretta: stessi dati, in JSON, per il cruscotto.
    if (isset($_GET['refresh'])) {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(array_merge(['success' => true], $__rep), JSON_UNESCAPED_UNICODE);
        exit;
    }

    $__stateLabels = [
        'ok'   => t('Tutti i sistemi operativi'),
        'warn' => t('Operativo con avvisi'),
        'down' => t('Problemi in corso'),
    ];
    $__state = (string)$__rep['state'];

    $pageTitle       = t('Stato dei Sistemi - L.U.X. Verse');
    $pageDescription = t('Stato in tempo reale dei sistemi L.U.X. Verse: esito delle sonde, latenza, archivi dati, sicurezza e prestazioni, aggiornato automaticamente.');
    $pageKeywords    = 'status, sistema, monitoraggio, uptime, L.U.X. Verse, telemetria';
    $pageOgType      = 'website';

    $extraCSS = '
.status-section { padding: 30px 0 70px; }

/* --- cruscotto --- */
.core-hud {
    display: grid;
    grid-template-columns: 260px 1fr;
    gap: 22px;
    align-items: stretch;
    margin-bottom: 26px;
}
.core-gauge-box {
    background: var(--bg-card);
    border: 1px solid var(--border);
    border-radius: 16px;
    padding: 24px 20px;
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 14px;
    text-align: center;
}
.core-gauge {
    --pct: 0;
    width: 168px;
    height: 168px;
    border-radius: 50%;
    background:
        radial-gradient(closest-side, var(--bg-page) 78%, transparent 79% 100%),
        conic-gradient(var(--gold) calc(var(--pct) * 1%), rgba(237,233,224,.10) 0);
    display: flex;
    align-items: center;
    justify-content: center;
    position: relative;
    transition: background .6s ease;
}
.core-gauge::after {
    content: "";
    position: absolute;
    inset: -8px;
    border-radius: 50%;
    border: 1px dashed rgba(237,233,224,.14);
    animation: coreSpin 26s linear infinite;
}
@keyframes coreSpin { to { transform: rotate(360deg); } }
.core-gauge-value {
    font-family: var(--font-title);
    font-size: 40px;
    font-weight: 700;
    line-height: 1;
    color: var(--text);
}
.core-gauge-value small { font-size: 16px; color: var(--text-muted); }
.core-gauge-label {
    font-family: var(--font-mono);
    font-size: 10.5px;
    letter-spacing: .18em;
    text-transform: uppercase;
    color: var(--text-muted);
}
.core-state {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 7px 13px;
    border-radius: 999px;
    font-size: 12.5px;
    font-weight: 600;
    border: 1px solid var(--border);
}
.core-state .dot { width: 8px; height: 8px; border-radius: 50%; background: var(--green); animation: corePulse 2.1s ease-in-out infinite; }
.core-state.warn .dot { background: var(--gold); }
.core-state.down .dot { background: var(--red); }
@keyframes corePulse { 0%,100% { opacity: 1; transform: scale(1); } 50% { opacity: .45; transform: scale(.82); } }

.core-facts {
    background: var(--bg-card);
    border: 1px solid var(--border);
    border-radius: 16px;
    padding: 22px 24px;
    display: flex;
    flex-direction: column;
    gap: 16px;
}
.core-facts-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(140px, 1fr));
    gap: 16px;
}
.core-fact { display: flex; flex-direction: column; gap: 4px; }
.core-fact b {
    font-family: var(--font-title);
    font-size: 22px;
    font-weight: 700;
    color: var(--text);
}
.core-fact span {
    font-family: var(--font-mono);
    font-size: 10.5px;
    letter-spacing: .12em;
    text-transform: uppercase;
    color: var(--text-muted);
}
.core-refresh {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    gap: 12px;
    padding-top: 14px;
    border-top: 1px solid var(--border);
    font-size: 13px;
    color: var(--text-muted);
}
.core-refresh code { font-family: var(--font-mono); color: var(--text-light); }
.core-btn {
    margin-left: auto;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 9px 15px;
    border-radius: 10px;
    border: 1px solid var(--border);
    background: rgba(255,255,255,.02);
    color: var(--text);
    font-size: 12.5px;
    font-weight: 600;
    cursor: pointer;
    font-family: inherit;
}
.core-btn:hover { border-color: var(--gold); color: var(--gold); }
.core-btn svg { width: 14px; height: 14px; }
.core-btn.is-busy svg { animation: coreSpin 1s linear infinite; }

/* --- sonde --- */
.core-group { margin-bottom: 26px; }
.core-group-head {
    display: flex;
    align-items: center;
    gap: 12px;
    margin-bottom: 12px;
}
.core-group-head h2 {
    font-family: var(--font-title);
    font-size: 1.05rem;
    font-weight: 700;
    letter-spacing: .04em;
    text-transform: uppercase;
    color: var(--text);
    margin: 0;
}
.core-group-head .rule { flex: 1; height: 1px; background: var(--border); }
.core-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
    gap: 12px;
}
.core-card {
    background: var(--bg-card);
    border: 1px solid var(--border);
    border-radius: 13px;
    padding: 15px 17px;
    display: flex;
    flex-direction: column;
    gap: 8px;
    transition: border-color .2s ease;
}
.core-card:hover { border-color: var(--border-hover); }
.core-card-top {
    display: flex;
    align-items: center;
    gap: 10px;
}
.core-card-top svg { width: 16px; height: 16px; color: var(--text-muted); }
.core-name { font-weight: 600; font-size: 13.5px; color: var(--text); }
.core-pill {
    margin-left: auto;
    font-family: var(--font-mono);
    font-size: 10px;
    letter-spacing: .1em;
    text-transform: uppercase;
    padding: 3px 8px;
    border-radius: 6px;
    border: 1px solid var(--border);
    color: var(--text-muted);
}
.core-pill.ok   { color: var(--green); border-color: rgba(66,184,131,.4); }
.core-pill.warn { color: var(--gold);  border-color: rgba(216,169,60,.4); }
.core-pill.down { color: var(--red);   border-color: rgba(217,108,91,.4); }
.core-pill.na   { opacity: .75; }
.core-detail { font-size: 12.5px; color: var(--text-light); line-height: 1.6; margin: 0; }
.core-ms {
    font-family: var(--font-mono);
    font-size: 11px;
    color: var(--text-muted);
}

/* --- telemetria --- */
.core-strip {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
    gap: 10px;
    background: var(--bg-card);
    border: 1px solid var(--border);
    border-radius: 13px;
    padding: 16px 18px;
    margin-bottom: 26px;
}
.core-strip div { display: flex; flex-direction: column; gap: 3px; }
.core-strip b { font-family: var(--font-mono); font-size: 13px; color: var(--text); font-weight: 500; }
.core-strip span {
    font-family: var(--font-mono);
    font-size: 10px;
    letter-spacing: .12em;
    text-transform: uppercase;
    color: var(--text-muted);
}

/* --- presenza e diario --- */
.core-two {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
    gap: 16px;
    margin-bottom: 26px;
}
.core-panel {
    background: var(--bg-card);
    border: 1px solid var(--border);
    border-radius: 14px;
    padding: 20px 22px;
}
.core-panel h2 {
    font-family: var(--font-title);
    font-size: 1.02rem;
    font-weight: 700;
    margin: 0 0 14px;
    color: var(--text);
}
.core-bar {
    height: 8px;
    border-radius: 999px;
    background: rgba(237,233,224,.08);
    overflow: hidden;
    margin: 10px 0 8px;
}
.core-bar i { display: block; height: 100%; background: var(--gold); }
.core-note { font-size: 12.5px; color: var(--text-muted); line-height: 1.7; margin: 0; }

.core-stream { list-style: none; margin: 0; padding: 0; display: flex; flex-direction: column; gap: 12px; }
.core-stream li { display: flex; gap: 12px; align-items: flex-start; font-size: 12.5px; }
.core-stream .when {
    font-family: var(--font-mono);
    font-size: 10.5px;
    color: var(--text-muted);
    min-width: 118px;
    padding-top: 2px;
}
.core-stream .what b { color: var(--text); font-weight: 600; }
.core-stream .what span { color: var(--text-light); display: block; }

@media (max-width: 860px) {
    .core-hud { grid-template-columns: 1fr; }
    .core-gauge-box { padding: 20px 16px; }
    .core-gauge { width: 140px; height: 140px; }
    .core-gauge-value { font-size: 34px; }
}
';

    // CSS condiviso delle pagine informative + quello di questa sezione.
    $extraCSS = (string)($extraCSS ?? '') . $__info_css;

    lux_header();
    ?>

<main>
    <section class="info-hub">
        <?php lux_info_nav('status'); ?>
    </section>

    <section class="hero">
        <div class="container">
            <div class="hero-badge">
                <?= icon('pulse', 13) ?>
                L.U.X. Core · <?= t('Telemetria in tempo reale') ?>
            </div>
            <h1><?= t('Stato dei sistemi') ?></h1>
            <p class="hero-lead">
                <?= t('Every check on this page measures something that actually exists on the server: files, archives, configuration and PHP runtime. No invented numbers and no external monitoring services.') ?>
            </p>
        </div>
    </section>

    <section class="status-section">
        <div class="container">

            <!-- CRUSCOTTO -->
            <div class="core-hud">
                <div class="core-gauge-box">
                    <div class="core-gauge" id="coreGauge" style="--pct: <?= (int)$__rep['health'] ?>">
                        <div>
                            <div class="core-gauge-value"><span id="corePct"><?= (int)$__rep['health'] ?></span><small>%</small></div>
                            <div class="core-gauge-label"><?= t('Indice di salute') ?></div>
                        </div>
                    </div>
                    <span class="core-state <?= htmlspecialchars($__state) ?>" id="coreState">
                        <span class="dot" aria-hidden="true"></span>
                        <span id="coreStateLabel"><?= htmlspecialchars($__stateLabels[$__state] ?? $__stateLabels['ok']) ?></span>
                    </span>
                </div>

                <div class="core-facts">
                    <div class="core-facts-grid">
                        <div class="core-fact"><b id="coreOk"><?= (int)$__rep['counters']['ok'] ?></b><span><?= t('Controlli superati') ?></span></div>
                        <div class="core-fact"><b id="coreWarn"><?= (int)$__rep['counters']['warn'] ?></b><span><?= t('Avvisi') ?></span></div>
                        <div class="core-fact"><b id="coreDown"><?= (int)$__rep['counters']['down'] ?></b><span><?= t('Problemi') ?></span></div>
                        <div class="core-fact"><b id="coreMs"><?= number_format((float)$__rep['duration_ms'], 0, ',', '.') ?></b><span><?= t('ms per il controllo completo') ?></span></div>
                        <div class="core-fact"><b><?= count($__rep['checks']) ?></b><span><?= t('Sonde attive') ?></span></div>
                        <?php
                        // Quante sonde riguardano i percorsi reali dell'utente.
                        $__flowCount = 0;
                        foreach ($__rep['checks'] as $__c) {
                            if (($__c['group'] ?? '') === t('Flussi pubblici')) $__flowCount++;
                        }
                        ?>
                        <div class="core-fact"><b><?= (int)$__flowCount ?></b><span><?= t('Flussi pubblici verificati') ?></span></div>
                    </div>
                    <div class="core-refresh">
                        <?= icon('refresh', 14) ?>
                        <span>
                            <?= t('Ultimo controllo:') ?>
                            <code id="coreGenerated"><?= htmlspecialchars($__rep['generated']) ?></code>
                            <?= $__rep['from_cache']
                                ? ' · ' . t('in cache da {n}s', ['n' => (int)$__rep['cache_age']])
                                : ' · ' . t('appena eseguito') ?>
                        </span>
                        <button type="button" class="core-btn" id="coreRefresh">
                            <?= icon('retry', 14) ?>
                            <span><?= t('Controlla ora') ?></span>
                        </button>
                    </div>
                    <p class="core-note" id="coreCountdown">
                        <?= t('This dashboard updates itself every 30 seconds while the page is open. Each check measures the real latency on the server and every value is kept for a minute, so visits do not repeat the same work.') ?>
                    </p>
                </div>
            </div>

            <!-- TELEMETRIA -->
            <div class="core-strip">
                <div><b id="telPhp"><?= htmlspecialchars($__rep['telemetry']['php']) ?></b><span><?= t('Runtime PHP') ?></span></div>
                <div><b><?= htmlspecialchars($__rep['telemetry']['sapi']) ?></b><span><?= t('Interfaccia server') ?></span></div>
                <div><b><?= htmlspecialchars($__rep['telemetry']['memory']) ?></b><span><?= t('Limite memoria') ?></span></div>
                <div><b id="telPeak"><?= htmlspecialchars($__rep['telemetry']['peak']) ?></b><span><?= t('Picco usato') ?></span></div>
                <div><b><?= htmlspecialchars($__rep['telemetry']['opcache']) ?></b><span><?= t('Cache opcode') ?></span></div>
                <div><b><?= htmlspecialchars($__rep['telemetry']['timezone']) ?></b><span><?= t('Fuso orario') ?></span></div>
                <div><b id="telNow"><?= htmlspecialchars($__rep['telemetry']['now']) ?></b><span><?= t('Ora del server') ?></span></div>
            </div>

            <!-- SONDE, RAGGRUPPATE -->
            <?php
            $__groups = [];
            foreach ($__rep['checks'] as $c) $__groups[$c['group']][] = $c;
            foreach ($__groups as $groupName => $items): ?>
                <div class="core-group">
                    <div class="core-group-head">
                        <h2><?= htmlspecialchars((string)$groupName) ?></h2>
                        <span class="rule" aria-hidden="true"></span>
                    </div>
                    <div class="core-grid">
                        <?php foreach ($items as $c): ?>
                            <div class="core-card" data-check="<?= htmlspecialchars($c['key']) ?>">
                                <div class="core-card-top">
                                    <?= icon($c['icon'], 16) ?>
                                    <span class="core-name"><?= htmlspecialchars((string)$c['label']) ?></span>
                                    <span class="core-pill <?= htmlspecialchars($c['status']) ?>" data-field="status"><?= htmlspecialchars($c['status']) ?></span>
                                </div>
                                <p class="core-detail" data-field="detail"><?= htmlspecialchars((string)$c['detail']) ?></p>
                                <span class="core-ms" data-field="ms"><?= number_format((float)$c['ms'], 2, ',', '.') ?> ms</span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endforeach; ?>

            <!-- PRESENZA + DIARIO -->
            <div class="core-two">
                <div class="core-panel">
                    <h2><?= t('Presenza del servizio') ?></h2>
                    <?php if ((int)$__rep['presence']['days_active'] > 0): ?>
                        <div class="core-bar"><i id="presenceBar" style="width: <?= (int)$__rep['presence']['percent'] ?>%"></i></div>
                        <p class="core-note">
                            <b id="presencePct"><?= (int)$__rep['presence']['percent'] ?>%</b> ·
                            <?= t('giorni con visite reali su giorni di attività:') ?>
                            <span id="presenceDays"><?= (int)$__rep['presence']['days_active'] ?>/<?= (int)$__rep['presence']['days_span'] ?></span><br>
                            <?= t('Prima visita registrata:') ?> <code><?= htmlspecialchars($__rep['presence']['first']) ?></code> ·
                            <?= t('ultima:') ?> <code><?= htmlspecialchars($__rep['presence']['last']) ?></code>
                        </p>
                    <?php else: ?>
                        <p class="core-note"><?= t('No visits recorded yet: the counter starts with the first real visit.') ?></p>
                    <?php endif; ?>
                    <p class="core-note" style="margin-top:12px;">
                        <?= t('This is not an external pinger: it is calculated from the visits actually recorded by the site, so it says how often the ecosystem is used, not how often a robot asked if it was alive.') ?>
                    </p>
                </div>

                <div class="core-panel">
                    <h2><?= t('Ultime attività del sistema') ?></h2>
                    <ul class="core-stream" id="coreStream">
                        <?php
                        // $t e' la funzione di traduzione: il ciclo usa $__row.
                        foreach (array_slice($__rep['timeline'], 0, 8) as $__row): ?>
                            <li>
                                <span class="when"><?= htmlspecialchars((string)$__row['when']) ?></span>
                                <span class="what">
                                    <b><?= htmlspecialchars((string)$__row['label']) ?></b>
                                    <span><?= htmlspecialchars((string)$__row['detail']) ?></span>
                                </span>
                            </li>
                        <?php endforeach; ?>
                        <?php if (empty($__rep['timeline'])): ?>
                            <li><span class="what"><span><?= t('No activity recorded yet.') ?></span></span></li>
                        <?php endif; ?>
                    </ul>
                </div>
            </div>

            <!-- TRASPARENZA -->
            <div class="core-panel">
                <h2><?= t('What this page checks - and what it does not') ?></h2>
                <p class="core-note">
                    <?= t('Le {n} sonde coprono tutti i flussi del sito, nessuno escluso: pagine informative, indicizzazione e ricerca, app installabile, instradamento degli indirizzi, archivi dati e loro integrità, catalogo e categorie, bacheca, FAQ, richieste di assistenza, tracciamento di un ticket, messaggi di contatto, cambio lingua, consenso cookie, uso senza connessione, vecchi indirizzi, motore degli strumenti, API, interfaccia e asset, configurazione dell\'AI, app Android, traduzioni, segreti separati, protezioni del server, connessione cifrata, runtime PHP, cache e peso del sito.', ['n' => count($__rep['checks'])]) ?>
                </p>
                <p class="core-note" style="margin-top:10px;">
                    <?= t('What it does not do: it does not send messages, it does not call the AI engine on every visit (so it consumes no quota), it does not simulate users and it does not measure the speed of your connection. A check marked "na" means that this specific value cannot be verified on this server - we prefer to write that instead of showing a green light that means nothing.') ?>
                </p>
                <p class="core-note" style="margin-top:10px;">
                    <?= t('If something looks wrong, tell us: a report with the time of the check and the name of the probe is enough to investigate.') ?>
                    <a href="/assistenza.php"><?= t('Assistenza') ?></a> ·
                    <a href="/contattaci.php"><?= t('Contatti') ?></a>
                </p>
            </div>

        </div>
    </section>
</main>

<script>
(function () {
    var url = '/status.php?refresh=1';
    var gauge = document.getElementById('coreGauge');
    var btn = document.getElementById('coreRefresh');
    if (!gauge || !btn) return;

    function set(id, text) {
        var el = document.getElementById(id);
        if (el) el.textContent = text;
    }
    function ms(value) {
        return (Math.round(value * 100) / 100).toFixed(2).replace('.', ',') + ' ms';
    }

    function paint(data) {
        gauge.style.setProperty('--pct', data.health);
        set('corePct', data.health);
        set('coreOk', data.counters.ok);
        set('coreWarn', data.counters.warn);
        set('coreDown', data.counters.down);
        set('coreMs', Math.round(data.duration_ms));
        set('coreGenerated', data.generated);
        set('telPeak', data.telemetry.peak || '');
        set('telNow', data.telemetry.now || '');

        var stateBox = document.getElementById('coreState');
        var labels = { ok: '<?= tjs('Tutti i sistemi operativi') ?>',
                       warn: '<?= tjs('Operativo con avvisi') ?>',
                       down: '<?= tjs('Problemi in corso') ?>' };
        if (stateBox) {
            stateBox.className = 'core-state ' + data.state;
            set('coreStateLabel', labels[data.state] || data.state);
        }

        (data.checks || []).forEach(function (check) {
            var card = document.querySelector('[data-check="' + check.key + '"]');
            if (!card) return;
            var pill = card.querySelector('[data-field=status]');
            if (pill) { pill.textContent = check.status; pill.className = 'core-pill ' + check.status; }
            var det = card.querySelector('[data-field=detail]');
            if (det) det.textContent = check.detail;
            var lat = card.querySelector('[data-field=ms]');
            if (lat) lat.textContent = ms(check.ms);
        });

        if (data.presence && data.presence.days_active > 0) {
            set('presencePct', data.presence.percent + '%');
            set('presenceDays', data.presence.days_active + '/' + data.presence.days_span);
            var bar = document.getElementById('presenceBar');
            if (bar) bar.style.width = data.presence.percent + '%';
        }

        var stream = document.getElementById('coreStream');
        if (stream && data.timeline) {
            stream.innerHTML = data.timeline.slice(0, 8).map(function (row) {
                var when = String(row.when || '').replace(/[<>&]/g, '');
                var label = String(row.label || '').replace(/[<>&]/g, '');
                var detail = String(row.detail || '').replace(/[<>&]/g, '');
                return '<li><span class="when">' + when + '</span><span class="what"><b>' +
                       label + '</b><span>' + detail + '</span></span></li>';
            }).join('');
        }
    }

    var left = 30;
    var note = document.getElementById('coreCountdown');
    var baseNote = note ? note.textContent : '';

    function tick() {
        left--;
        if (note && left > 0) note.textContent = baseNote + ' (' + left + 's)';
        if (left <= 0) { left = 30; load(); }
    }

    function load() {
        btn.classList.add('is-busy');
        fetch(url, { headers: { 'Accept': 'application/json' } })
            .then(function (r) { return r.json(); })
            .then(function (data) { if (data && data.success) paint(data); left = 30; })
            .catch(function () { if (note) note.textContent = '<?= tjs('Riconnessione in corso...') ?>'; })
            .then(function () { btn.classList.remove('is-busy'); });
    }

    btn.addEventListener('click', function () { left = 30; load(); });
    setInterval(tick, 1000);
})();
</script>

    <?php
    lux_footer();
}

// ============================================================
// SEZIONE NON VALIDA -> pagina di avviso (404)
// ============================================================
if ($__info === '') {
    http_response_code(404);
    $pageTitle       = t('Page not found');
    $pageDescription = '';
    $pageKeywords    = '';
    $extraCSS        = $__info_css;
    lux_header();
    ?>
    <main>
        <section class="hero">
            <div class="container">
                <div class="hero-badge"><?= icon('alert', 13) ?> <?= t('Page not found') ?></div>
                <h1><?= t('This page does not exist') ?></h1>
                <p class="hero-lead"><?= t('The address you opened is not one of the L.U.X. Verse information pages.') ?></p>
            </div>
        </section>
        <section class="main-section">
            <div class="container">
                <div class="content-box">
                    <p><?= t('These are the available pages:') ?></p>
                    <ul>
                        <li><a href="/chi-siamo.php"><?= t('Chi Siamo') ?></a></li>
                        <li><a href="/faq.php"><?= t('FAQ') ?></a></li>
                        <li><a href="/assistenza.php"><?= t('Assistenza') ?></a></li>
                        <li><a href="/contattaci.php"><?= t('Contatti') ?></a></li>
                        <li><a href="/status.php"><?= t('Stato dei sistemi') ?></a></li>
                        <li><a href="/privacy.php"><?= t('Privacy Policy') ?></a></li>
                        <li><a href="/terms.php"><?= t('Termini di Servizio') ?></a></li>
                    </ul>
                </div>
            </div>
        </section>
    </main>
    <?php
    lux_footer();
}
