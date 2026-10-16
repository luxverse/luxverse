<?php
// ============================================================
// L.U.X. VERSE — PANNELLO DI CONTROLLO (riscritto da zero)
// ============================================================
// Come e' fatto:
//   1. questa pagina NON modifica piu' niente in PHP: e' solo l'interfaccia.
//      Ogni azione passa da /api/admin.php, che risponde in JSON. Cosi' non
//      c'e' piu' una ricarica di pagina per ogni click e il pannello resta
//      fluido anche con 100.000 strumenti in catalogo.
//   2. le sezioni sono in un'unica pagina (una "vista" per volta) e i dati
//      si caricano solo quando apri quella sezione: l'apertura e' immediata.
//   3. la sezione Notifica globale e' in cima: si scrive, si vede subito
//      l'anteprima di come apparira' sul sito, si invia. L'avviso compare
//      per 5 secondi sulle pagine aperte e poi rientra nel campanello.
//   4. Ctrl+K apre la ricerca globale (sezioni + strumenti del catalogo).
//
// Tutto in un solo file (CSS e JS compresi): nel progetto i file si contano.
// Design: grafite, osso e oro, icone SVG, nessuna emoji.
// ============================================================

// config.php prima della sessione: da li' arriva lux_session_start(), che
// imposta HttpOnly + SameSite + Secure sul cookie dell'admin.
require_once __DIR__ . '/config.php';
lux_session_start();

$isLoggedIn = isAdminLoggedIn();

// Gettone per le azioni del pannello (interruttore del blocco sito):
// un sito esterno non puo' far premere un pulsante dell'admin al posto suo.
if ($isLoggedIn && empty($_SESSION['lux_csrf'])) {
    $_SESSION['lux_csrf'] = bin2hex(random_bytes(16));
}

// Sessione scaduta per tempo: lo si dice, invece di far sembrare che il
// pannello sia sparito.
if (!$isLoggedIn && !empty($GLOBALS['lux_admin_expired']) && !isset($_GET['scaduta'])) {
    header('Location: admin.php?scaduta=1');
    exit;
}

if (isset($_GET['logout'])) {
    $_SESSION = [];
    session_destroy();
    header('Location: index.php');
    exit;
}

if (!$isLoggedIn && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['password'])) {
    $guardIp = lux_guard_ip();
    $guard   = lux_login_state($guardIp);

    if ($guard['blocked']) {
        // Troppi errori: si aspetta. Nessun tentativo viene nemmeno valutato.
        $loginBlocked = max(1, (int)$guard['minutes']);
    } elseif (lux_admin_password_ok((string)$_POST['password'])) {
        // La verifica, il conteggio dei tentativi e il registro attività
        // stanno tutti in lux_admin_password_ok(): la stessa porta che usano
        // l'API e lo script di setup, cosi' il blocco vale ovunque.
        // Nuovo ID di sessione dopo l'accesso: senza questo, un ID fatto
        // conoscere prima del login resterebbe valido anche dopo (fissazione).
        session_regenerate_id(true);
        $_SESSION['admin_logged_in'] = true;
        $_SESSION['admin_login_at']  = time();
        header('Location: admin.php');
        exit;
    } else {
        $after = lux_login_state($guardIp);
        if ($after['blocked']) $loginBlocked = max(1, (int)$after['minutes']);
        else                   $loginLeft    = max(0, 5 - (int)$after['fails']);
        $loginError = true;   // l'attesa di mezzo secondo la fa la porta unica
    }
}

// ============================================================
// INTERRUTTORE DEL BLOCCO SITO (manutenzione)
// ------------------------------------------------------------
// Il pannello resta raggiungibile anche a sito bloccato: e' da qui che si
// riapre. Il blocco e' voluto e lo dice ai visitatori (503 + avviso).
// ============================================================
if ($isLoggedIn && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['lux_maintenance'])) {
    $csrf = (string)($_POST['csrf'] ?? '');
    $now  = lux_maintenance_state();

    if (empty($_SESSION['lux_csrf']) || !hash_equals((string)$_SESSION['lux_csrf'], $csrf)) {
        $maintenanceError = true;
    } else {
        $wantOn = ($_POST['lux_maintenance'] === 'on');
        if ($wantOn && !$now['on']) {
            lux_maintenance_write([
                'on'       => true,
                'since'    => date('Y-m-d H:i:s'),   // ora del server (per il registro)
                'since_ts' => time(),                // momento assoluto: le ore si mostrano in italiano
                'hours'    => 24,
                'key'   => bin2hex(random_bytes(16)),   // anteprima per il titolare
                'api'   => true,                        // anche l'app entra in manutenzione
            ]);
            lux_log_activity('SITE_CLOSED', 'Sito messo in manutenzione: blocco voluto, riapertura entro 24 ore');
        } elseif (!$wantOn && $now['on']) {
            lux_maintenance_write(['on' => false]);
            lux_log_activity('SITE_REOPENED', 'Sito riaperto a tutti');
        }
        header('Location: admin.php');
        exit;
    }
}

// ============================================================
// LOGIN — una sola schermata, nessun dato, nessun pannello
// ============================================================
if (!$isLoggedIn) {
    $pageTitle       = t('Admin Login - L.U.X. Verse');
    $pageDescription = t('Restricted access area of the L.U.X. Verse administration panel.');
    $pageKeywords    = 'admin, L.U.X., login, restricted, dashboard';

    $extraCSS = <<<'LUXCSS'
.login-wrap {
    min-height: calc(100vh - 168px);
    display: flex; align-items: center; justify-content: center;
    padding: 60px 24px 40px;
}
.login-box {
    position: relative;
    width: 100%; max-width: 420px;
    padding: 46px 38px 34px;
    /* Superficie piena: prima era una velatura marrone con la sfocatura
       dietro, del tema precedente. Qui la sfocatura si paga a ogni
       scorrimento e non aggiunge niente. */
    background: var(--bg-card);
    border: 1px solid var(--line-2);
    border-radius: var(--radius-lg);
    box-shadow: var(--shadow-lg);
}
.login-box::before {
    content: ''; position: absolute; top: 0; left: 0; right: 0; height: 2px;
    border-radius: var(--radius-lg) var(--radius-lg) 0 0;
    background: linear-gradient(90deg, transparent, var(--accent), var(--accent-2), transparent);
}
.login-icon {
    width: 56px; height: 56px; margin: 0 auto 20px;
    display: flex; align-items: center; justify-content: center;
    border-radius: 18px;
    background: rgba(216,169,60,.10);
    border: 1px solid rgba(216,169,60,.32);
}
.login-icon svg { width: 24px; height: 24px; stroke: var(--gold); }
.login-title {
    margin: 0 0 6px; text-align: center;
    font-family: var(--font-title); font-size: 1.7em; font-weight: 600;
    letter-spacing: -.02em; color: var(--bone);
}
.login-subtitle { text-align: center; color: var(--text-muted); font-size: 13px; margin-bottom: 28px; }
.login-input {
    width: 100%; padding: 15px 16px; margin-bottom: 14px;
    background: var(--ink-3); border: 1px solid var(--line-2);
    border-radius: var(--radius-sm); color: var(--bone);
    font: inherit; font-size: 14px; outline: none;
    transition: border-color .18s;
}
.login-input:focus { border-color: var(--gold); }
.login-error {
    margin-bottom: 16px; padding: 12px;
    color: var(--danger); font-size: 13px; text-align: center;
    background: rgba(217,108,91,.08);
    border: 1px solid rgba(217,108,91,.28);
    border-radius: var(--radius-sm);
}
.login-back { text-align: center; margin-top: 18px; }
.login-back a { color: var(--text-light); font-size: 13px; text-decoration: none; }
.login-back a:hover { color: var(--gold); }
.btn-admin {
    display: block; width: 100%; padding: 15px;
    background: var(--gradient-primary); color: #120C04;
    border: 0; border-radius: var(--radius-sm);
    font: inherit; font-weight: 600; font-size: 14.5px; cursor: pointer;
    transition: background .18s, border-color .18s;
}
/* Fermo: il design e' piatto, il pulsante cambia solo tono. */
.btn-admin:hover { filter: none; transform: none; background: var(--gold); }
.login-input:focus { border-color: var(--gold); }
LUXCSS;

    lux_header();
    ?>
    <main>
        <div class="login-wrap">
            <div class="login-box">
                <div class="login-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2"></rect><path d="M7 11V7a5 5 0 0 1 10 0v4"></path></svg>
                </div>
                <h1 class="login-title"><?= t('Restricted Area') ?></h1>
                <p class="login-subtitle"><?= t('Access the admin dashboard') ?></p>
                <?php if (isset($loginBlocked)): ?>
                    <div class="login-error"><?= t('Too many attempts: accesso bloccato per altri {n} minuti.', ['n' => (int)$loginBlocked]) ?></div>
                <?php elseif (isset($loginError)): ?>
                    <div class="login-error"><?= t('Wrong password!') ?><?php if (isset($loginLeft) && $loginLeft > 0): ?> <span class="login-left"><?= t('Tentativi rimasti: {n}', ['n' => (int)$loginLeft]) ?></span><?php endif; ?></div>
                <?php elseif (!empty($_GET['scaduta'])): ?>
                    <div class="login-error"><?= t('Sessione scaduta: accedi di nuovo.') ?></div>
                <?php endif; ?>
                <form method="POST">
                    <input type="password" name="password" class="login-input" placeholder="<?= t('Access password') ?>" required autofocus>
                    <button type="submit" class="btn-admin"><?= t('Login to Dashboard') ?></button>
                </form>
                <div class="login-back">
                    <a href="index.php">&larr; <?= t('Back to Home') ?></a>
                </div>
            </div>
        </div>
    </main>
    <?php
    lux_footer();
    return;
}

// ============================================================
// PANNELLO
// ============================================================
$pageTitle       = t('Control Panel') . ' - L.U.X. Verse';
$pageDescription = t('L.U.X. Verse control panel: global notifications, tools, catalog, board, traffic and system.');
$pageKeywords    = 'admin, L.U.X., control panel, dashboard, notifications, tools, catalog';
$loginUser       = 'admin';

// Numeri per l'intestazione: letti una volta e passati al browser. Tutto il
// resto arriva dall'API quando serve.
$totTools  = lux_catalog_total();
$totStates = lux_catalog_status_counts();
lux_admin_csrf();   // token anti-CSRF del pannello, vive nella sessione

$extraCSS = <<<'LUXCSS'
/* ============================================================
   PANNELLO — struttura, superfici, movimenti
   ============================================================ */
.ad-shell {
    display: grid;
    grid-template-columns: 244px minmax(0, 1fr);
    gap: 0;
    max-width: 1320px;
    margin: 0 auto;
    /* L'intestazione del sito e' fissa (position: fixed): senza questo
       margine in alto la barra del pannello (ricerca, invio, menu) finisce
       dietro l'intestazione e non si riesce piu' a cliccare. 92px = altezza
       dell'intestazione + il respiro della colonna laterale. */
    padding: 88px 18px 52px;
    align-items: start;
    min-height: calc(100vh - 168px);
}
.ad-side {
    position: sticky; top: 92px;
    display: flex; flex-direction: column; gap: 18px;
    padding: 18px 14px;
    background: var(--ink-2);
    border: 1px solid var(--line-2);
    border-radius: var(--radius-lg);
}
.ad-brand {
    display: flex; align-items: center; gap: 10px;
    padding: 0 6px 14px; border-bottom: 1px solid var(--line);
}
.ad-brand-mark {
    width: 30px; height: 30px; flex-shrink: 0;
    display: flex; align-items: center; justify-content: center;
    border-radius: 9px; background: rgba(216,169,60,.10);
    border: 1px solid rgba(216,169,60,.32);
}
.ad-brand-mark svg, .ad-brand-mark img { width: 15px; height: 15px; stroke: var(--gold); }
.ad-brand-txt { display: flex; flex-direction: column; line-height: 1.25; }
.ad-brand-txt strong { color: var(--bone); font-size: 13.5px; font-weight: 600; letter-spacing: .01em; }
.ad-brand-txt span {
    color: var(--text-light); font-family: var(--font-mono);
    font-size: 10.5px; letter-spacing: .06em; text-transform: uppercase;
}
.ad-nav { display: flex; flex-direction: column; gap: 3px; }
.ad-group {
    padding: 14px 8px 6px; color: var(--text-light);
    font-family: var(--font-mono); font-size: 10.5px;
    letter-spacing: .08em; text-transform: uppercase;
}
.ad-link {
    display: flex; align-items: center; gap: 10px;
    width: 100%; padding: 10px 10px;
    background: none; border: 1px solid transparent; border-radius: 9px;
    color: var(--text-light); font: inherit; font-size: 13.5px; text-align: left;
    cursor: pointer; transition: background .16s, color .16s, border-color .16s;
}
.ad-link svg { width: 15px; height: 15px; flex-shrink: 0; opacity: .85; }
.ad-link:hover { color: var(--bone); background: rgba(237,233,224,.04); }
.ad-link.active {
    color: var(--bone); background: rgba(216,169,60,.09);
    border-color: rgba(216,169,60,.28);
}
.ad-link.active svg { stroke: var(--gold); opacity: 1; }
.ad-link .ad-n {
    margin-left: auto; color: var(--text-muted);
    font-family: var(--font-mono); font-size: 10px;
}
.ad-link.active .ad-n { color: var(--gold); }
.ad-side-foot {
    margin-top: auto; padding-top: 14px;
    border-top: 1px solid var(--line);
    display: flex; flex-direction: column; gap: 2px;
}
.ad-side-foot a {
    padding: 8px 10px; border-radius: 9px;
    color: var(--text-muted); font-size: 12.5px; text-decoration: none;
    transition: color .16s, background .16s;
}
.ad-side-foot a:hover { color: var(--bone); background: rgba(237,233,224,.04); }
.ad-vault {
    display: flex; align-items: flex-start; gap: 7px;
    padding: 7px 10px 4px; margin-top: 6px;
    font-family: var(--font-mono); font-size: 10.5px; line-height: 1.5;
    color: var(--text-muted); letter-spacing: .02em;
}
.ad-vault svg { width: 13px; height: 13px; flex-shrink: 0; margin-top: 1px; }
.ad-vault.ok svg   { color: var(--ok); }
.ad-vault.warn     { color: var(--gold); }
.ad-vault.bad      { color: var(--bad, #D96C5B); }
.ad-maint { padding: 6px 10px 2px; }
.ad-maint-btn {
    width: 100%; padding: 9px 12px; border-radius: 9px; cursor: pointer;
    font-family: var(--font-mono); font-size: 11px; letter-spacing: .04em;
    background: rgba(255,255,255,.05); border: 1px solid var(--line-2); color: var(--bone);
    transition: background .16s, border-color .16s, color .16s;
}
.ad-maint-btn:hover { background: rgba(255,255,255,.09); }
.ad-maint-btn.on  { border-color: rgba(216,169,60,.5); color: var(--gold); }
.ad-maint-btn.off { border-color: rgba(87,185,138,.5); color: #A7DEC7; }

.ad-main { min-width: 0; padding-left: 18px; }
.ad-top {
    display: flex; align-items: center; gap: 10px;
    padding: 0 0 16px; margin-bottom: 20px;
    /* Colore pieno: la barra non e' sfocata e non cambia mentre si scorre. */
    background: var(--ink);
    border-bottom: 1px solid var(--line-2);
}
.ad-search {
    position: relative; flex: 1 1 auto; max-width: 460px;
}
.ad-search svg {
    position: absolute; left: 13px; top: 50%; transform: translateY(-50%);
    width: 15px; height: 15px; stroke: var(--text-muted); pointer-events: none;
}
.ad-search input {
    width: 100%; padding: 11px 14px 11px 38px;
    background: var(--ink-2); border: 1px solid var(--line-2);
    border-radius: var(--radius-sm); color: var(--bone);
    font: inherit; font-size: 13.5px; outline: none;
}
.ad-search input:focus { border-color: var(--gold); background: var(--ink-3); }
.ad-search kbd {
    position: absolute; right: 10px; top: 50%; transform: translateY(-50%);
    padding: 2px 6px; border: 1px solid var(--line-2); border-radius: 5px;
    color: var(--text-muted); font-family: var(--font-mono); font-size: 10px;
}
.ad-act {
    display: inline-flex; align-items: center; gap: 7px;
    padding: 10px 14px; border-radius: var(--radius-sm);
    border: 1px solid var(--line-2); background: var(--ink-2);
    color: var(--text-light); font: inherit; font-size: 12.5px; cursor: pointer;
    white-space: nowrap; transition: color .16s, border-color .16s, background .16s;
}
.ad-act svg { width: 14px; height: 14px; }
.ad-act:hover { color: var(--bone); border-color: var(--line-3); }
.ad-act.primary {
    background: var(--gold); border-color: var(--gold); color: #120C04; font-weight: 600;
}
.ad-act.primary:hover { background: var(--gold-light); color: #120C04; }
.ad-act.danger { color: #E8917F; border-color: rgba(217,108,91,.32); }
.ad-act.danger:hover { color: #F3A695; }
.ad-act:disabled { opacity: .5; cursor: default; }
.ad-burger { display: none; }

.ad-view { animation: adIn .22s ease both; }
@keyframes adIn { from { opacity: 0; transform: translateY(6px); } to { opacity: 1; transform: none; } }

.ad-head { margin-bottom: 20px; }
.ad-head h1 {
    margin: 0 0 6px; font-family: var(--font-title);
    font-size: clamp(21px, 2.4vw, 26px); font-weight: 600; letter-spacing: -.02em; color: var(--bone);
}
.ad-head p { margin: 0; color: var(--text-muted); font-size: 13.5px; max-width: 78ch; }

.ad-card {
    padding: 18px;
    background: var(--ink-2);
    border: 1px solid var(--line-2);
    border-radius: 12px;
    margin-bottom: 14px;
}
.ad-card > h2 {
    margin: 0 0 4px; font-size: 15px; font-weight: 600; color: var(--bone);
}
.ad-card > .ad-sub { margin: 0 0 18px; color: var(--text-muted); font-size: 12.5px; }
.ad-card.flat { background: transparent; }

.ad-kpis { display: grid; grid-template-columns: repeat(auto-fit, minmax(168px, 1fr)); gap: 12px; margin-bottom: 18px; }
.ad-kpi {
    padding: 15px 15px 13px;
    background: var(--ink-2); border: 1px solid var(--line-2);
    border-radius: var(--radius); position: relative; overflow: hidden;
}
.ad-kpi::before {
    content: ''; position: absolute; left: 0; top: 0; bottom: 0; width: 2px;
    background: var(--gold); opacity: .75;
}
.ad-kpi.ok::before { background: var(--ok); }
.ad-kpi.info::before { background: var(--info); }
.ad-kpi.warn::before { background: var(--warn); }
.ad-kpi.danger::before { background: var(--danger); }
.ad-kpi-label {
    display: block; margin-bottom: 6px; color: var(--text-light);
    font-family: var(--font-mono); font-size: 11px;
    letter-spacing: .04em; text-transform: uppercase;
}
.ad-kpi-num {
    display: block; font-family: var(--font-title);
    font-size: 25px; font-weight: 600; letter-spacing: -.02em; color: var(--bone);
}
.ad-kpi-note { display: block; margin-top: 4px; color: var(--text-muted); font-size: 11.5px; }

.ad-grid2 { display: grid; grid-template-columns: minmax(0, 1.35fr) minmax(0, 1fr); gap: 16px; align-items: start; }

.ad-field { margin-bottom: 14px; }
.ad-field label {
    display: block; margin-bottom: 6px; color: var(--text-light);
    font-family: var(--font-mono); font-size: 11px;
    letter-spacing: .04em; text-transform: uppercase;
}
.ad-field input, .ad-field textarea, .ad-field select {
    width: 100%; padding: 11px 13px;
    background: var(--ink-3); border: 1px solid var(--line-2);
    border-radius: var(--radius-sm); color: var(--bone);
    font: inherit; font-size: 13.5px; outline: none; resize: vertical;
}
.ad-field input:focus, .ad-field textarea:focus, .ad-field select:focus { border-color: var(--gold); }
.ad-field .ad-hint { display: block; margin-top: 5px; color: var(--text-muted); font-size: 11.5px; }
.ad-2col { display: grid; grid-template-columns: 1fr 1fr; gap: 0 14px; }
.ad-3col { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 0 14px; }

.ad-check { display: flex; align-items: center; gap: 9px; margin: 4px 0 16px; color: var(--text-light); font-size: 13px; }
.ad-check input { width: 16px; height: 16px; accent-color: var(--gold); }

.ad-row {
    display: flex; align-items: center; justify-content: space-between; gap: 12px;
    padding: 11px 0; border-bottom: 1px solid var(--line); font-size: 13px;
}
.ad-row:last-child { border-bottom: 0; }
.ad-row .ad-row-main { min-width: 0; }
.ad-row b { color: var(--bone); font-weight: 600; }
.ad-row small { display: block; margin-top: 3px; color: var(--text-muted); font-size: 11.5px; }
.ad-row .ad-row-side { flex-shrink: 0; display: flex; align-items: center; gap: 8px; }

.ad-table-wrap { overflow-x: auto; margin: 0 -4px; }
.ad-table { width: 100%; border-collapse: collapse; font-size: 12.5px; }
.ad-table th {
    padding: 10px 10px; text-align: left; white-space: nowrap;
    color: var(--text-light); font-family: var(--font-mono);
    font-size: 10.5px; letter-spacing: .05em; text-transform: uppercase;
    border-bottom: 1px solid var(--line-2);
}
.ad-table td {
    padding: 11px 10px; border-bottom: 1px solid var(--line);
    color: var(--text-light); vertical-align: middle;
}
.ad-table tr:hover td { background: rgba(237,233,224,.02); }
.ad-table td.strong { color: var(--bone); font-weight: 600; }
.ad-table td.mono { font-family: var(--font-mono); font-size: 11.5px; }
.ad-table select {
    padding: 6px 8px; background: var(--ink-3);
    border: 1px solid var(--line-2); border-radius: 7px;
    color: var(--bone); font: inherit; font-size: 11.5px; outline: none;
}
.ad-trunc { display: block; max-width: 460px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
.ad-empty { padding: 34px 10px; text-align: center; color: var(--text-muted); font-size: 13px; }

.ad-pill {
    display: inline-flex; align-items: center; gap: 5px;
    padding: 4px 9px; border-radius: 6px;
    font-family: var(--font-mono); font-size: 10.5px;
    letter-spacing: .04em; text-transform: uppercase;
    border: 1px solid var(--line-2); color: var(--text-light);
}
.ad-pill.ok { color: var(--ok); border-color: rgba(143,207,107,.5); background: rgba(143,207,107,.10); }
.ad-pill.warn { color: var(--warn); border-color: rgba(255,217,138,.5); background: rgba(255,217,138,.10); }
.ad-pill.info { color: var(--info); border-color: rgba(110,155,196,.45); background: rgba(110,155,196,.10); }
.ad-pill.danger { color: var(--danger); border-color: rgba(228,102,79,.55); background: rgba(228,102,79,.10); }
.ad-pill.gold { color: var(--gold); border-color: rgba(216,169,60,.5); background: rgba(216,169,60,.10); }

.ad-bars { display: flex; flex-direction: column; gap: 10px; }
.ad-bar-row { display: grid; grid-template-columns: 118px minmax(0, 1fr) 74px; gap: 10px; align-items: center; font-size: 12px; }
.ad-bar-row span { color: var(--text-light); }
.ad-bar { height: 7px; background: rgba(237,233,224,.08); border-radius: 4px; overflow: hidden; }
.ad-bar i { display: block; height: 100%; background: var(--gold); opacity: .8; border-radius: 4px; }
.ad-bar-row em { color: var(--bone); font-family: var(--font-mono); font-size: 10.5px; font-style: normal; text-align: right; }

/* Grafico a barre: l'altezza delle barre e' in pixel (calcolata sulle visite
   massime del periodo), cosi' il disegno non dipende dall'altezza del
   contenitore e resta uguale su ogni schermo. */
.ad-chart { display: flex; align-items: flex-end; gap: 5px; height: 168px; }
.ad-chart .ad-col { flex: 1; display: flex; flex-direction: column; justify-content: flex-end; align-items: center; gap: 6px; min-width: 0; height: 100%; }
.ad-chart .ad-col i {
    display: block; width: 100%; min-height: 3px;
    background: var(--gold);
    opacity: .85;
    border-radius: 3px 3px 0 0;
    transition: height .4s var(--ease);
}
.ad-chart .ad-col span {
    color: var(--text-muted); font-family: var(--font-mono); font-size: 9px;
    white-space: nowrap; height: 12px;
}

.ad-preview {
    position: relative;
    padding: 16px 40px 18px 16px;
    background: var(--ink-2);
    border: 1px solid var(--line-2); border-radius: 12px;
    display: flex; gap: 12px; align-items: flex-start; overflow: hidden;
}
.ad-preview::before { content: ''; position: absolute; left: 0; top: 0; bottom: 0; width: 2px; background: var(--gold); }
.ad-preview-icon {
    width: 34px; height: 34px; flex-shrink: 0; border-radius: 10px;
    display: flex; align-items: center; justify-content: center;
    background: rgba(216,169,60,.10); color: var(--gold);
}
.ad-preview-icon svg { width: 16px; height: 16px; }
.ad-preview b { display: block; color: var(--bone); font-size: 13.5px; margin-bottom: 3px; }
.ad-preview p { margin: 0; color: var(--text-light); font-size: 12.5px; line-height: 1.55; }

/* Avviso a comparsa del pannello: 5 secondi, poi si toglie */
.ad-pop {
    position: fixed; top: 88px; right: 20px; z-index: 3000;
    display: flex; flex-direction: column; gap: 10px;
    width: min(380px, calc(100vw - 28px));
}
.ad-pop-card {
    position: relative; padding: 15px 40px 17px 15px;
    background: var(--ink-3);
    border: 1px solid var(--line-2); border-radius: 12px;
    box-shadow: 0 10px 26px -14px rgba(0,0,0,.7);
    overflow: hidden; animation: adPopIn .3s var(--ease) both;
}
.ad-pop-card.out { animation: adPopOut .24s ease forwards; }
.ad-pop-card::before { content: ''; position: absolute; left: 0; top: 0; bottom: 0; width: 2px; background: var(--gold); }
.ad-pop-card.ok::before { background: var(--ok); }
.ad-pop-card.danger::before { background: var(--danger); }
.ad-pop-card b { display: block; color: var(--bone); font-size: 13.5px; margin-bottom: 3px; padding-right: 6px; }
.ad-pop-card p { margin: 0; color: var(--text-light); font-size: 12.5px; line-height: 1.55; }
.ad-pop-close {
    position: absolute; top: 9px; right: 9px; width: 26px; height: 26px;
    display: flex; align-items: center; justify-content: center;
    background: none; border: 1px solid transparent; border-radius: 7px;
    color: var(--text-muted); cursor: pointer;
}
.ad-pop-close:hover { color: var(--bone); border-color: var(--line-2); }
.ad-pop-close svg { width: 13px; height: 13px; }
.ad-pop-bar { position: absolute; left: 0; bottom: 0; height: 2px; width: 100%; background: var(--gold); opacity: .7; }
.ad-pop-card.ok .ad-pop-bar { background: var(--ok); }
@keyframes adPopIn { from { opacity: 0; transform: translateY(-12px) scale(.98); } to { opacity: 1; transform: none; } }
@keyframes adPopOut { to { opacity: 0; transform: translateY(-10px) scale(.98); } }

/* Tavolozza comandi (Ctrl+K) */
.ad-pal {
    position: fixed; inset: 0; z-index: 3100;
    display: flex; align-items: flex-start; justify-content: center;
    padding: 96px 20px 20px;
    background: rgba(5,6,7,.72);
}
.ad-pal[hidden] { display: none; }
.ad-pal-box {
    width: 100%; max-width: 560px;
    background: var(--ink-2); border: 1px solid var(--line-2);
    border-radius: 12px; overflow: hidden;
    box-shadow: 0 14px 40px -20px rgba(0,0,0,.8);
}
.ad-pal-box input {
    width: 100%; padding: 17px 18px; background: transparent;
    border: 0; border-bottom: 1px solid var(--line);
    color: var(--bone); font: inherit; font-size: 15px; outline: none;
}
.ad-pal-list { max-height: 52vh; overflow-y: auto; padding: 8px; }
.ad-pal-item {
    display: flex; align-items: center; gap: 10px; width: 100%;
    padding: 10px 12px; background: none; border: 0; border-radius: 9px;
    color: var(--text-light); font: inherit; font-size: 13.5px;
    text-align: left; cursor: pointer;
}
.ad-pal-item:hover, .ad-pal-item.sel { background: rgba(216,169,60,.10); color: var(--bone); }
.ad-pal-item svg { width: 15px; height: 15px; }
.ad-pal-item small { margin-left: auto; color: var(--text-muted); font-family: var(--font-mono); font-size: 10px; }

/* Skeleton */
.ad-sk { display: flex; flex-direction: column; gap: 10px; padding: 6px 0; }
/* Barre ferme: prima mandavano un riflesso di continuo (adSk all'infinito). */
.ad-sk i {
    display: block; height: 13px; border-radius: 6px;
    background: var(--ink-4);
}
.ad-sk i.w40 { width: 40%; } .ad-sk i.w70 { width: 70%; } .ad-sk i.w55 { width: 55%; }

.ad-note {
    padding: 12px 14px; border-radius: var(--radius-sm);
    background: rgba(216,169,60,.06); border: 1px solid rgba(216,169,60,.24);
    color: var(--text-light); font-size: 12.5px; line-height: 1.6;
}
.ad-clock { margin-left: auto; color: var(--text-muted); font-family: var(--font-mono); font-size: 11px; white-space: nowrap; }

@media (prefers-reduced-motion: reduce) {
    * { animation-duration: .001ms !important; transition-duration: .001ms !important; }
}

@media (max-width: 1080px) {
    .ad-grid2 { grid-template-columns: 1fr; }
}
@media (max-width: 900px) {
    .ad-shell { grid-template-columns: minmax(0, 1fr); padding: 88px 14px 50px; }
    .ad-side {
        position: fixed; top: 0; bottom: 0; left: 0; z-index: 2400;
        width: 264px; border-radius: 0; padding-top: 24px;
        overflow-y: auto; transform: translateX(-104%);
        transition: transform .26s var(--ease);
        box-shadow: 14px 0 30px -18px rgba(0,0,0,.75);
    }
    .ad-side.open { transform: none; }
    .ad-main { padding-left: 0; }
    .ad-burger { display: inline-flex; }
    .ad-top { flex-wrap: wrap; }
    .ad-search { max-width: none; order: 3; flex-basis: 100%; }
    .ad-clock { display: none; }
    .ad-2col, .ad-3col { grid-template-columns: 1fr; }
}
@media (max-width: 720px) {
    .ad-pop { top: auto; bottom: 14px; left: 14px; right: 14px; width: auto; }
    .ad-bar-row { grid-template-columns: 96px minmax(0, 1fr) 60px; }
}

LUXCSS;

lux_header();
?>
<main class="ad-shell" id="adShell">

    <aside class="ad-side" id="adSide">
        <div class="ad-brand">
            <span class="ad-brand-mark" aria-hidden="true">
                <?= lux_logo(26, 'ad-mark-svg') ?>
            </span>
            <span class="ad-brand-txt">
                <strong>L.U.X. Verse</strong>
                <span><?= t('Control Panel') ?></span>
            </span>
        </div>

        <nav class="ad-nav" aria-label="<?= t('Control Panel') ?>">
            <span class="ad-group"><?= t('Overview') ?></span>
            <button type="button" class="ad-link active" data-view="dashboard">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="7" height="9"></rect><rect x="14" y="3" width="7" height="5"></rect><rect x="14" y="12" width="7" height="9"></rect><rect x="3" y="16" width="7" height="5"></rect></svg>
                <?= t('Overview') ?>
            </button>
            <button type="button" class="ad-link" data-view="broadcast">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><path d="M18 8.6a6 6 0 0 0-12 0c0 5-2.2 6.4-2.2 6.4h16.4S18 13.6 18 8.6z"></path><path d="M13.7 18.6a2 2 0 0 1-3.4 0"></path></svg>
                <?= t('Broadcast') ?>
            </button>

            <span class="ad-group"><?= t('Content') ?></span>
            <button type="button" class="ad-link" data-view="catalog">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="7.5" height="7.5" rx="1.5"></rect><rect x="13.5" y="3" width="7.5" height="7.5" rx="1.5"></rect><rect x="3" y="13.5" width="7.5" height="7.5" rx="1.5"></rect><rect x="13.5" y="13.5" width="7.5" height="7.5" rx="1.5"></rect></svg>
                <?= t('Catalog') ?>
                <span class="ad-n" id="adNTools"><?= number_format($totTools, 0, '.', '.') ?></span>
            </button>
            <button type="button" class="ad-link" data-view="tools">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2.4 3.6 7v10L12 21.6 20.4 17V7z"></path><path d="M12 12v9.6"></path><path d="M3.6 7 12 11.6 20.4 7"></path></svg>
                <?= t('Status of the tools') ?>
                <span class="ad-n"><?= number_format($totTools, 0, '.', '.') ?></span>
            </button>
            <button type="button" class="ad-link" data-view="notices">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><path d="M4 4.5A2.5 2.5 0 0 1 6.5 2h12A1.5 1.5 0 0 1 20 3.5v17a1.5 1.5 0 0 1-1.5 1.5h-12A2.5 2.5 0 0 0 4 19.5z"></path><line x1="8" y1="2" x2="8" y2="22"></line><line x1="12" y1="7" x2="17" y2="7"></line><line x1="12" y1="11" x2="17" y2="11"></line></svg>
                <?= t('Notices') ?>
                <span class="ad-n" id="adNNotices">0</span>
            </button>

            <span class="ad-group"><?= t('Support') ?></span>
            <button type="button" class="ad-link" data-view="messages">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path></svg>
                <?= t('Messages') ?>
                <span class="ad-n" id="adNMessages">0</span>
            </button>
            <button type="button" class="ad-link" data-view="support">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="9.5"></circle><path d="M9.4 9.3a2.7 2.7 0 0 1 5.3.8c0 2-2.7 2.5-2.7 2.5"></path><line x1="12" y1="17" x2="12.01" y2="17"></line></svg>
                <?= t('Assistance') ?>
                <span class="ad-n" id="adNSupport">0</span>
            </button>
            <button type="button" class="ad-link" data-view="guide">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><path d="M21 12a9 9 0 0 1-9 9H4l2-3a9 9 0 1 1 15-6z"></path></svg>
                <?= t('L.U.X. Guide') ?>
                <span class="ad-n" id="adNGuide">0</span>
            </button>
            <button type="button" class="ad-link" data-view="feedback">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><polygon points="12 2 15 9 22 9.3 16.5 13.8 18.4 21 12 16.9 5.6 21 7.5 13.8 2 9.3 9 9"></polygon></svg>
                <?= t('Feedback') ?>
                <span class="ad-n" id="adNFeedback">0</span>
            </button>

            <span class="ad-group"><?= t('Traffic') ?></span>
            <button type="button" class="ad-link" data-view="traffic">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><path d="M2.4 12S6 6.4 12 6.4 21.6 12 21.6 12 18 17.6 12 17.6 2.4 12 2.4 12z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                <?= t('Visitors') ?>
                <span class="ad-n" id="adNVisits">0</span>
            </button>

            <span class="ad-group"><?= t('System') ?></span>
            <button type="button" class="ad-link" data-view="system">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><rect x="6.4" y="6.4" width="11.2" height="11.2" rx="2"></rect><rect x="9.6" y="9.6" width="4.8" height="4.8" rx="1"></rect><line x1="9.6" y1="2.6" x2="9.6" y2="6.4"></line><line x1="14.4" y1="2.6" x2="14.4" y2="6.4"></line><line x1="9.6" y1="17.6" x2="9.6" y2="21.4"></line><line x1="14.4" y1="17.6" x2="14.4" y2="21.4"></line><line x1="2.6" y1="9.6" x2="6.4" y2="9.6"></line><line x1="17.6" y1="9.6" x2="21.4" y2="9.6"></line><line x1="2.6" y1="14.4" x2="6.4" y2="14.4"></line><line x1="17.6" y1="14.4" x2="21.4" y2="14.4"></line></svg>
                <?= t('System Logs') ?>
                <span class="ad-n" id="adNLogs">0</span>
            </button>
        </nav>

        <div class="ad-side-foot">
            <a href="index.php"><?= t('Back to site') ?></a>
            <a href="linea-del-tempo.php"><?= t('Linea del Tempo') ?></a>
            <a href="admin.php?logout=1"><?= t('Logout') ?></a>

            <?php
            // Stato della cassaforte dei segreti, scritto dal server: si vede
            // a colpo d'occhio da dove sono letti password, token e chiave AI,
            // e quanti tentativi sbagliati ci sono stati nel mondo.
            $__sec   = lux_secrets_status();
            $__glob  = lux_login_state('*generale');
            $__vault = $__sec['loaded'] ? ($__sec['place'] === 'vault' ? 'ok' : 'warn') : 'bad';
            $__vaultText = $__sec['loaded']
                ? ($__sec['place'] === 'vault' ? 'Vault: in the protected data folder' : 'Vault: in the public root (fallback)')
                : 'Vault: missing — the login is closed';
            ?>
            <span class="ad-vault <?= $__vault ?>"
                  title="<?= htmlspecialchars($__sec['file'] !== '' ? $__sec['file'] : '—') ?>">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><rect x="4.5" y="10.5" width="15" height="10" rx="2.4"></rect><path d="M8.2 10.5V7.6a3.8 3.8 0 0 1 7.6 0v2.9"></path></svg>
                <?= t($__vaultText) ?>
            </span>
            <span class="ad-vault <?= ((int)$__glob['fails'] > 0) ? 'warn' : 'ok' ?>">
                <?= t('Failed attempts in the last 15 minutes: {n}', ['n' => (string)(int)$__glob['fails']]) ?>
            </span>
            <?php if (!empty($__sec['attempt_log_unwritable'])): ?>
                <span class="ad-vault bad"><?= t('The attempt log is not writable: the lock-out is not working.') ?></span>
            <?php endif; ?>
            <?php unset($__sec, $__glob, $__vault, $__vaultText); ?>

            <?php
            // Interruttore del blocco sito: si vede subito se il sito e'
            // chiuso ai visitatori, e si riapre da qui senza toccare il codice.
            $__maint = lux_maintenance_state();
            ?>
            <span class="ad-vault <?= $__maint['on'] ? 'bad' : 'ok' ?>">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="9"></circle><path d="M12 7.6V12l3.2 2.1"></path></svg>
                <?= t($__maint['on'] ? 'Site closed: visitors see the maintenance notice' : 'Site open to everyone') ?>
                <?php if ($__maint['on'] AND lux_maintenance_until() !== ''): ?>
                    · <?= t('Back online by {when}', ['when' => lux_maintenance_until()]) ?>
                <?php endif; ?>
            </span>
            <?php if (!empty($maintenanceError)): ?>
                <span class="ad-vault bad"><?= t('Action not valid: reload the panel and try again.') ?></span>
            <?php endif; ?>
            <form method="post" class="ad-maint">
                <input type="hidden" name="csrf" value="<?= htmlspecialchars((string)($_SESSION['lux_csrf'] ?? '')) ?>">
                <button type="submit" name="lux_maintenance" value="<?= $__maint['on'] ? 'off' : 'on' ?>"
                        class="ad-maint-btn <?= $__maint['on'] ? 'off' : 'on' ?>">
                    <?= t($__maint['on'] ? 'Reopen the site' : 'Close the site (maintenance)') ?>
                </button>
            </form>
            <?php if ($__maint['on'] && !empty($__maint['key'])): ?>
                <span class="ad-vault warn">
                    <?= t('Preview link (2 hours)') ?>:<br>
                    <code><?= htmlspecialchars(rtrim(LUX_SITE_URL, '/') . '/?lux_anteprima=' . $__maint['key']) ?></code>
                </span>
                <span class="ad-vault">
                    <?= t('To close the preview') ?>:<br>
                    <code><?= htmlspecialchars(rtrim(LUX_SITE_URL, '/') . '/?lux_anteprima=esci') ?></code>
                </span>
            <?php endif; ?>
            <?php unset($__maint); ?>
        </div>
    </aside>

    <section class="ad-main">
        <header class="ad-top">
            <button type="button" class="ad-act ad-burger" id="adBurger" aria-label="<?= t('Mobile menu') ?>">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"><line x1="4" y1="7" x2="20" y2="7"></line><line x1="4" y1="12" x2="20" y2="12"></line><line x1="4" y1="17" x2="20" y2="17"></line></svg>
            </button>

            <div class="ad-search">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="7.5"></circle><line x1="21" y1="21" x2="16.5" y2="16.5"></line></svg>
                <input type="text" id="adSearch" placeholder="<?= t('Search among {n} tools...', ['n' => number_format($totTools, 0, '.', '.')]) ?>" autocomplete="off">
                <kbd>Ctrl+K</kbd>
            </div>

            <button type="button" class="ad-act primary" id="adQuickBroadcast">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><path d="M18 8.6a6 6 0 0 0-12 0c0 5-2.2 6.4-2.2 6.4h16.4S18 13.6 18 8.6z"></path><path d="M13.7 18.6a2 2 0 0 1-3.4 0"></path></svg>
                <?= t('Send to everyone') ?>
            </button>
            <button type="button" class="ad-act" id="adReload">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"></polyline></svg>
                <?= t('Refresh') ?>
            </button>
            <span class="ad-clock" id="adClock"></span>
        </header>

        <div class="ad-view" id="adView"></div>
    </section>
</main>

<div class="ad-pop" id="adPop" aria-live="polite"></div>

<div class="ad-pal" id="adPal" hidden>
    <div class="ad-pal-box" role="dialog" aria-modal="true" aria-label="<?= t('Search') ?>">
        <input type="text" id="adPalInput" placeholder="<?= t('Search') ?> — <?= t('Overview') ?>, <?= t('Catalog') ?>, <?= t('System') ?>..." autocomplete="off">
        <div class="ad-pal-list" id="adPalList"></div>
    </div>
</div>

<script>
(function () {
    'use strict';

    // ============================================================
    // FRASI (tradotte dal server, nessuna emoji)
    // ============================================================
    var S = {
        view:            <?= json_encode(t('Overview')) ?>,
        overview:        <?= json_encode(t('Overview')) ?>,
        overviewSub:     <?= json_encode(t('General status of L.U.X. Verse')) ?>,
        broadcast:       <?= json_encode(t('Broadcast')) ?>,
        broadcastSub:    <?= json_encode(t('Send a notification to everyone')) ?>,
        broadcastInfo:   <?= json_encode(t('The popup appears immediately on every open page, then goes back into the bell.')) ?>,
        sendNow:         <?= json_encode(t('Send now')) ?>,
        sendPublic:      <?= json_encode(t('Everyone (public)')) ?>,
        sendAdmin:       <?= json_encode(t('Admin only')) ?>,
        sendAll:         <?= json_encode(t('Everyone and admin')) ?>,
        recipients:      <?= json_encode(t('Recipients')) ?>,
        category:        <?= json_encode(t('Category')) ?>,
        redirect:        <?= json_encode(t('Redirect (optional)')) ?>,
        publishAt:       <?= json_encode(t('Publish at (optional)')) ?>,
        titleIt:         <?= json_encode(t('Title (Italian)')) ?>,
        titleEn:         <?= json_encode(t('Title (English)')) ?>,
        msgIt:           <?= json_encode(t('Message (Italian)')) ?>,
        msgEn:           <?= json_encode(t('Message (English)')) ?>,
        sentNotifs:      <?= json_encode(t('Sent notifications')) ?>,
        purgeAll:        <?= json_encode(t('Purge all')) ?>,
        delete:          <?= json_encode(t('Delete')) ?>,
        saving:          <?= json_encode(t('Saving...')) ?>,
        loadingData:     <?= json_encode(t('Loading data...')) ?>,
        nothing:         <?= json_encode(t('Nothing to show')) ?>,
        errorGeneric:    <?= json_encode(t('Error loading projects. Try again later.')) ?>,
        catalog:         <?= json_encode(t('Catalog')) ?>,
        catalogSub:      <?= json_encode(t('Tools per category')) ?>,
        totalTools:      <?= json_encode(t('Total tools')) ?>,
        genTools:        <?= json_encode(t('Generated tools')) ?>,
        handTools:       <?= json_encode(t('Handcrafted tools')) ?>,
        activeTitle:     <?= json_encode(t('Active tools')) ?>,
        wholeCatalog:    <?= json_encode(t('The whole catalog')) ?>,
        search:          <?= json_encode(t('Search')) ?>,
        all:             <?= json_encode(t('All')) ?>,
        loadMore:        <?= json_encode(t('Load more')) ?>,
        page:            <?= json_encode(t('Page {n} of {t}')) ?>,
        tools:           <?= json_encode(t('Handcrafted tools')) ?>,
        toolsSub:        <?= json_encode(t('Projects') . ' · ' . t('Handcrafted tools')) ?>,
        addProject:      <?= json_encode(t('Add project')) ?>,
        name:            <?= json_encode(t('Name')) ?>,
        key:             <?= json_encode(t('Key')) ?>,
        status:          <?= json_encode(t('Status')) ?>,
        active:          <?= json_encode(t('Active')) ?>,
        maintenance:     <?= json_encode(t('Maintenance')) ?>,
        paused:          <?= json_encode(t('On Pause')) ?>,
        building:        <?= json_encode(t('Under Construction')) ?>,
        review:          <?= json_encode(t('Under Review')) ?>,
        blocked:         <?= json_encode(t('Blocked')) ?>,
        archived:        <?= json_encode(t('Archived')) ?>,
        off:             <?= json_encode(t('Off')) ?>,
        open:            <?= json_encode(t('Open')) ?>,
        copyLink:        <?= json_encode(t('Copy link')) ?>,
        address:         <?= json_encode(t('URL')) ?>,
        notices:         <?= json_encode(t('Notices')) ?>,
        noticesSub:      <?= json_encode(t('Board notices: what visitors read on the home page and on the board.')) ?>,
        addNotice:       <?= json_encode(t('Add notice')) ?>,
        updateNotice:    <?= json_encode(t('Update notice')) ?>,
        edit:            <?= json_encode(t('Edit')) ?>,
        pinned:          <?= json_encode(t('Pinned')) ?>,
        pin:             <?= json_encode(t('Pin')) ?>,
        unpin:           <?= json_encode(t('Unpin')) ?>,
        signature:       <?= json_encode(t('Signature')) ?>,
        startDate:       <?= json_encode(t('Start date')) ?>,
        endDate:         <?= json_encode(t('End date')) ?>,
        alsoNotify:      <?= json_encode(t('Also send a notification')) ?>,
        messages:        <?= json_encode(t('Messages')) ?>,
        messagesSub:     <?= json_encode(t('Requests and messages from the contact form.')) ?>,
        code:            <?= json_encode(t('Code')) ?>,
        project:         <?= json_encode(t('Project')) ?>,
        date:            <?= json_encode(t('Date')) ?>,
        details:         <?= json_encode(t('Details')) ?>,
        support:         <?= json_encode(t('Assistance')) ?>,
        supportSub:      <?= json_encode(t('Support requests, with the real queue.') ) ?>,
        queue:           <?= json_encode(t('Queue')) ?>,
        openRequests:    <?= json_encode(t('Open requests')) ?>,
        traffic:         <?= json_encode(t('Visitors')) ?>,
        trafficSub:      <?= json_encode(t('Real visits, without invented numbers.')) ?>,
        today:           <?= json_encode(t('Today')) ?>,
        week:            <?= json_encode(t('This week')) ?>,
        month:           <?= json_encode(t('This month')) ?>,
        allTime:         <?= json_encode(t('All time')) ?>,
        uniqueVis:       <?= json_encode(t('Unique visitors')) ?>,
        last14:          <?= json_encode(t('Last 14 days')) ?>,
        topPages:        <?= json_encode(t('Most visited pages')) ?>,
        system:          <?= json_encode(t('System Logs')) ?>,
        systemSub:       <?= json_encode(t('Diagnostics and complete activity log.')) ?>,
        health:          <?= json_encode(t('Health')) ?>,
        allOk:           <?= json_encode(t('All systems working')) ?>,
        phpVersion:      <?= json_encode(t('PHP version')) ?>,
        aiConfigured:    <?= json_encode(t('AI configured')) ?>,
        curl:            <?= json_encode(t('cURL available')) ?>,
        diskFree:        <?= json_encode(t('Disk free')) ?>,
        serverTime:      <?= json_encode(t('Server time')) ?>,
        storage:         <?= json_encode(t('Storage used')) ?>,
        activity:        <?= json_encode(t('Activity')) ?>,
        yes:             <?= json_encode(t('Yes')) ?>,
        no:              <?= json_encode(t('No')) ?>,
        confirm:         <?= json_encode(t('Confirm')) ?>,
        areYouSure:      <?= json_encode(t('Are you sure?')) ?>,
        cancel:          <?= json_encode(t('Cancel')) ?>,
        notificationSent:<?= json_encode(t('Notification sent to everyone')) ?>,
        publishAtHint:   <?= json_encode(t('Leave it empty to send right away. The time is read in your time zone, so what you see here is when it goes out.')) ?>,
        scheduledFor:    <?= json_encode(t('Scheduled for {when} (server time).')) ?>,
        sentNow:         <?= json_encode(t('Sent now. If nothing appears on the open pages within a few seconds, reload them.')) ?>,
        noticeSaved:     <?= json_encode(t('Notice saved!')) ?>,
        deleted:         <?= json_encode(t('Deleted')) ?>,
        saved:           <?= json_encode(t('Saved!')) ?>,
        filter:          <?= json_encode(t('Filter')) ?>,
        reset:           <?= json_encode(t('Reset filters')) ?>,
        generated:       <?= json_encode(t('Generated')) ?>,
        handcrafted:     <?= json_encode(t('Handcrafted')) ?>,
        logs:            <?= json_encode(t('Logs')) ?>,
        close:           <?= json_encode(t('Close')) ?>,
        preview:         <?= json_encode(t('Preview')) ?>,
        refresh:         <?= json_encode(t('Refresh')) ?>,
        scheduled:       <?= json_encode(t('Scheduled')) ?>,
        creativity:      <?= json_encode(t('Creativity')) ?>,
        productivity:    <?= json_encode(t('Productivity')) ?>,
        educational:     <?= json_encode(t('Educational')) ?>,
        games:           <?= json_encode(t('Games')) ?>,
        utilities:       <?= json_encode(t('Utilities')) ?>,
        // ---------- AGGIORNAMENTO 39.0.0: guida, feedback, salute API ----------
        guide:           <?= json_encode(t('L.U.X. Guide')) ?>,
        guideSub:        <?= json_encode(t('Chats with the guide and those transferred to the staff.')) ?>,
        feedback:        <?= json_encode(t('Feedback')) ?>,
        feedbackSub:     <?= json_encode(t('What visitors think: site, guide and support.')) ?>,
        apiHealth:       <?= json_encode(t('AI keys health')) ?>,
        apiHealthSub:    <?= json_encode(t('The four keys: requests, speed and limits. The fourth serves the chatbot and the search.')) ?>,
        topProjects:     <?= json_encode(t('Most prestigious projects')) ?>,
        topProjectsSub:  <?= json_encode(t('Upvotes and real opens. Home, admin and board are excluded.')) ?>,
        bulkState:       <?= json_encode(t('Status of the 100,000 tools')) ?>,
        bulkStateSub:    <?= json_encode(t('One system for the whole catalog. Pick a group, pick a status, apply: there is no hand-curated list and no separate generated list, every tool is on the same level.')) ?>,
        bulkScope:       <?= json_encode(t('Which group')) ?>,
        bulkStatus:      <?= json_encode(t('New status')) ?>,
        csScopeAll:      <?= json_encode(t('The whole catalog (100,000)')) ?>,
        csScopeRoots:    <?= json_encode(t('Only the tools with their own page')) ?>,
        csCategory:      <?= json_encode(t('One category')) ?>,
        applyBulk:       <?= json_encode(t('Apply to the whole group')) ?>,
        csNow:           <?= json_encode(t('Current state of the catalog')) ?>,
        csOne:           <?= json_encode(t('A single tool')) ?>,
        csOneHint:       <?= json_encode(t('Type its address (for example riassumi-email-flash) and change only that one.')) ?>,
        csLookup:        <?= json_encode(t('Find the tool')) ?>,
        csNotFound:      <?= json_encode(t('No tool with that address.')) ?>,
        csSet:           <?= json_encode(t('Set status')) ?>,
        csSize:          <?= json_encode(t('The site and the app pick up the new status in real time, as soon as you save.')) ?>,
        csTools:         <?= json_encode(t('Tools')) ?>,
        csPick:          <?= json_encode(t('Pick a tool from the catalog')) ?>,
        notConfigured:   <?= json_encode(t('Not configured')) ?>,
        handcrafted:     <?= json_encode(t('Tool')) ?>,
        generated:       <?= json_encode(t('Tool')) ?>,
        reply:           <?= json_encode(t('Reply')) ?>,
        closeChat:       <?= json_encode(t('Close chat')) ?>,
        conversation:    <?= json_encode(t('Conversation')) ?>,
        lastMessage:     <?= json_encode(t('Last message')) ?>,
        chatQueue:       <?= json_encode(t('Waiting for staff')) ?>,
        allChats:        <?= json_encode(t('All conversations')) ?>,
        rating:          <?= json_encode(t('Rating')) ?>,
        channel:         <?= json_encode(t('Channel')) ?>,
        feedbackCount:   <?= json_encode(t('Total feedback')) ?>,
        feedbackAvg:     <?= json_encode(t('Average rating')) ?>,
        feedbackNew:     <?= json_encode(t('Unread')) ?>,
        bulkDone:        <?= json_encode(t('Status updated')) ?>,
        categories:      <?= json_encode(lux_notification_categories()) ?>,
        catLabels: <?= json_encode(array_map(function ($k) { return t($k); }, lux_notification_categories())) ?>,
        catNames:  <?= json_encode(array_keys(lux_notification_categories())) ?>
    };

    // ============================================================
    // API — un solo posto per parlare col server
    // ============================================================
    // Gli stati e le categorie del catalogo, in un posto solo.
    var CATALOG_STATES = ['attivo', 'manutenzione', 'pausa', 'costruzione', 'verifica', 'bloccato', 'archiviato', 'disattivato'];
    var CATALOG_CATS   = ['creativita', 'produttivita', 'educativi', 'giochi', 'utility'];

    // Token anti-CSRF della sessione: viaggia con OGNI richiesta del pannello.
    // Prima l'autorizzazione delle modifiche dipendeva dall'intestazione
    // Origin, che su alcuni hosting non arriva: gli invii (compresa la
    // notifica globale) venivano respinti senza una spiegazione chiara.
    var LUX_CSRF = <?= json_encode((string)($_SESSION['lux_csrf'] ?? '')) ?>;

    function api(op, data) {
        var payload = Object.assign({ op: op, csrf: LUX_CSRF }, data || {});
        return fetch('/api/admin.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            credentials: 'same-origin',
            cache: 'no-store',
            body: JSON.stringify(payload)
        }).then(function (r) {
            if (r.status === 401 || r.status === 403) {
                // Se il rifiuto arriva sulla prova di provenienza si puo'
                // rinnovare la pagina e riprovare: non e' una sessione scaduta.
                return r.json().catch(function () { return {}; }).then(function (e) {
                    if (r.status === 403 && e && e.hint === 'csrf') {
                        pop('danger', S.errorGeneric, 'Prova di provenienza mancante: ricarica il pannello.');
                        throw new Error('csrf');
                    }
                    pop('danger', S.errorGeneric, 'Sessione scaduta: rientra per continuare.');
                    // Invece di lasciare il pannello a meta' (i pulsanti non
                    // farebbero piu' niente), si torna alla schermata di accesso.
                    window.setTimeout(function () { window.location.href = 'admin.php'; }, 1600);
                    throw new Error('auth');
                });
            }
            return r.json();
        });
    }

    function esc(s) {
        return String(s == null ? '' : s).replace(/[&<>"']/g, function (c) {
            return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c];
        });
    }
    function num(n) {
        return (Number(n) || 0).toLocaleString('it-IT');
    }
    function icon(name) {
        var P = {
            bolt:     '<path d="M13 2 4.5 13.5H11l-1 8.5L19.5 10H13z"/>',
            grid:     '<rect x="3" y="3" width="7.5" height="7.5" rx="1.5"/><rect x="13.5" y="3" width="7.5" height="7.5" rx="1.5"/><rect x="3" y="13.5" width="7.5" height="7.5" rx="1.5"/><rect x="13.5" y="13.5" width="7.5" height="7.5" rx="1.5"/>',
            bell:     '<path d="M18 8.6a6 6 0 0 0-12 0c0 5-2.2 6.4-2.2 6.4h16.4S18 13.6 18 8.6z"/><path d="M13.7 18.6a2 2 0 0 1-3.4 0"/>',
            copy:     '<rect x="9" y="9" width="12" height="12" rx="2"/><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"/>',
            trash:    '<polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/>',
            external: '<path d="M18 13.5V19a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h5.5"/><polyline points="14 3 21 3 21 10"/><line x1="10" y1="14" x2="21" y2="3"/>',
            plus:     '<line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/>',
            pin:      '<path d="M12 21s7-6.2 7-11a7 7 0 0 0-14 0c0 4.8 7 11 7 11z"/><circle cx="12" cy="10" r="2.6"/>',
            search:   '<circle cx="11" cy="11" r="7.5"/><line x1="21" y1="21" x2="16.5" y2="16.5"/>',
            chip:     '<rect x="6.4" y="6.4" width="11.2" height="11.2" rx="2"/><rect x="9.6" y="9.6" width="4.8" height="4.8" rx="1"/><line x1="9.6" y1="2.6" x2="9.6" y2="6.4"/><line x1="14.4" y1="2.6" x2="14.4" y2="6.4"/><line x1="9.6" y1="17.6" x2="9.6" y2="21.4"/><line x1="14.4" y1="17.6" x2="14.4" y2="21.4"/><line x1="2.6" y1="9.6" x2="6.4" y2="9.6"/><line x1="17.6" y1="9.6" x2="21.4" y2="9.6"/><line x1="2.6" y1="14.4" x2="6.4" y2="14.4"/><line x1="17.6" y1="14.4" x2="21.4" y2="14.4"/>',
            eye:      '<path d="M2.4 12S6 6.4 12 6.4 21.6 12 21.6 12 18 17.6 12 17.6 2.4 12 2.4 12z"/><circle cx="12" cy="12" r="3"/>',
            send:     '<line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/>',
            close:    '<line x1="6" y1="6" x2="18" y2="18"/><line x1="18" y1="6" x2="6" y2="18"/>',
            check:    '<polyline points="20 6.5 9.5 17 4 11.5"/>',
            clock:    '<circle cx="12" cy="12" r="9.5"/><polyline points="12 6.5 12 12 16 14"/>',
            book:     '<path d="M4 4.5A2.5 2.5 0 0 1 6.5 2h12A1.5 1.5 0 0 1 20 3.5v17a1.5 1.5 0 0 1-1.5 1.5h-12A2.5 2.5 0 0 0 4 19.5z"/><line x1="8" y1="2" x2="8" y2="22"/>',
            chat:     '<path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/>',
            help:     '<circle cx="12" cy="12" r="9.5"/><path d="M9.4 9.3a2.7 2.7 0 0 1 5.3.8c0 2-2.7 2.5-2.7 2.5"/><line x1="12" y1="17" x2="12.01" y2="17"/>'
        };
        return '<svg viewBox="0 0 24 24" width="15" height="15" fill="none" stroke="currentColor" stroke-width="1.7" '
             + 'stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">' + (P[name] || P.chip) + '</svg>';
    }

    // ============================================================
    // AVVISO A COMPARSA (5 secondi) — poi sparisce
    // ============================================================
    // Serve a confermare le azioni del pannello: invio di una notifica,
    // salvataggio di un avviso, cancellazione. Dura 5 secondi esatti e ha la
    // barra che scorre; la stessa notifica resta poi nella sua sezione.
    var POP_MS = 5000;
    function pop(kind, title, message) {
        var box = document.getElementById('adPop');
        var card = document.createElement('div');
        card.className = 'ad-pop-card ' + (kind || '');
        card.setAttribute('role', 'status');
        card.innerHTML = '<b>' + esc(title) + '</b><p>' + esc(message || '') + '</p>'
            + '<button type="button" class="ad-pop-close" aria-label="' + esc(S.close) + '">' + icon('close') + '</button>'
            + '<span class="ad-pop-bar"></span>';
        box.appendChild(card);
        var bar = card.querySelector('.ad-pop-bar'), timer = null;
        function start() {
            if (bar) {
                bar.style.transition = 'none'; bar.style.width = '100%';
                requestAnimationFrame(function () { bar.style.transition = 'width ' + POP_MS + 'ms linear'; bar.style.width = '0%'; });
            }
            timer = setTimeout(close, POP_MS);
        }
        function close() {
            clearTimeout(timer);
            card.classList.add('out');
            setTimeout(function () { card.remove(); }, 240);
        }
        card.querySelector('.ad-pop-close').addEventListener('click', close);
        card.addEventListener('mouseenter', function () { clearTimeout(timer); });
        card.addEventListener('mouseleave', start);
        start();
    }

    // ============================================================
    // VISTE
    // ============================================================
    var view    = 'dashboard';
    var viewEl  = document.getElementById('adView');
    var loaded  = {};
    var cache   = {};

    function skeleton() {
        return '<div class="ad-card"><div class="ad-sk"><i class="w40"></i><i class="w70"></i><i class="w55"></i><i class="w70"></i></div></div>';
    }
    function head(title, sub) {
        return '<div class="ad-head"><h1>' + esc(title) + '</h1><p>' + esc(sub || '') + '</p></div>';
    }
    function kpi(label, value, note, kind) {
        return '<div class="ad-kpi ' + (kind || '') + '"><span class="ad-kpi-label">' + esc(label) + '</span>'
             + '<span class="ad-kpi-num">' + esc(value) + '</span>'
             + (note ? '<span class="ad-kpi-note">' + esc(note) + '</span>' : '') + '</div>';
    }
    function empty(txt) { return '<div class="ad-empty">' + esc(txt || S.nothing) + '</div>'; }
    function prettyDate(s) {
        if (!s) return '';
        var d = new Date(String(s).replace(' ', 'T'));
        if (isNaN(d.getTime())) return String(s);
        return d.toLocaleString('it-IT', { day: '2-digit', month: '2-digit', year: 'numeric', hour: '2-digit', minute: '2-digit' });
    }

    // ---------- AIUTANTI PER LE BARRE ----------
    function statusLabel(slug) {
        var map = { attivo: S.active, manutenzione: S.maintenance, pausa: S.paused, costruzione: S.building,
                    verifica: S.review, bloccato: S.blocked, archiviato: S.archived, disattivato: S.off };
        return map[slug] || slug;
    }
    function aiRow(a) {
        var col = a.state === 'down' ? '#D96C5B' : (a.state === 'warn' ? '#D8A93C' : '#57B98A');
        var pct = a.configured === false ? 0 : Math.max(0, Math.min(100, Number(a.health) || 0));
        return '<div class="ad-bar-row"><span>' + esc(a.label) + (a.role ? ' <em style="color:var(--text-light);font-style:normal;font-size:11px;">' + esc(a.role) + '</em>' : '') + '</span>'
             + '<span class="ad-bar"><i style="width:' + pct + '%;background:' + col + '"></i></span>'
             + '<em>' + (a.configured === false ? esc(S.notConfigured) : (pct + '%')) + '</em></div>';
    }

    // ---------- PANORAMICA ----------
    function renderDashboard() {
        var d = cache.overview;
        if (!d) { viewEl.innerHTML = head(S.overview, S.overviewSub) + skeleton(); return; }
        var k = d.kpi || {};
        var max = 1;
        (d.series || []).forEach(function (p) { if (p.value > max) max = p.value; });
        var chart = '<div class="ad-chart">' + (d.series || []).map(function (p, i) {
            var h = Math.max(3, Math.round((p.value / max) * 112));
            // Un'etichetta ogni due: con 14 giorni restano leggibili tutte.
            return '<div class="ad-col" title="' + esc(p.label + ' · ' + p.value) + '"><i style="height:' + h + 'px"></i><span>' + esc(i % 2 === 0 ? p.label : '') + '</span></div>';
        }).join('') + '</div>';

        var health = d.health || {};
        var healthKind = (health.state === 'down') ? 'danger' : (health.state === 'warn' ? 'warn' : 'ok');
        var catCounts = (d.catalog && d.catalog.counts) || {};
        var catMax = 1;
        Object.keys(catCounts).forEach(function (c) { if (catCounts[c] > catMax) catMax = catCounts[c]; });
        var bars = Object.keys(catCounts).map(function (c) {
            return '<div class="ad-bar-row"><span>' + esc(labelOfCategory(c)) + '</span>'
                 + '<span class="ad-bar"><i style="width:' + Math.round((catCounts[c] / catMax) * 100) + '%"></i></span>'
                 + '<em>' + num(catCounts[c]) + '</em></div>';
        }).join('');

        // La salute delle 4 chiavi AI sta in cima alla dashboard.
        var aiCard = '<div class="ad-card"><h2>' + esc(S.apiHealth) + '</h2><p class="ad-sub">' + esc(S.apiHealthSub) + '</p>'
            + '<div class="ad-bars">' + (d.ai || []).map(aiRow).join('') + '</div></div>';

        var topMax = 1;
        (d.top || []).forEach(function (t) { if (t.score > topMax) topMax = t.score; });
        var topCard = '<div class="ad-card"><h2>' + esc(S.topProjects) + '</h2><p class="ad-sub">' + esc(S.topProjectsSub) + '</p>'
            + ((d.top || []).length ? '<div class="ad-bars">' + d.top.map(function (t) {
                return '<div class="ad-bar-row"><span>' + esc(t.name || t.key) + '</span><span class="ad-bar"><i style="width:' + Math.round((t.score / topMax) * 100) + '%"></i></span><em>' + num(t.upvotes) + ' / ' + num(t.opens) + '</em></div>';
            }).join('') + '</div>' : empty());

        // Lo stato di TUTTO il catalogo (100.000 strumenti). Qui si vede la
        // fotografia; per cambiarla si apre il sistema completo.
        var st = (d.catalog && d.catalog.status) || {};
        var stTotal = 0;
        Object.keys(st).forEach(function (k) { stTotal += st[k]; });
        if (!stTotal) stTotal = 1;
        var bulkCard = '<div class="ad-card"><h2>' + esc(S.bulkState) + '</h2><p class="ad-sub">' + esc(S.csSize) + '</p>'
            + '<div class="ad-bars">' + CATALOG_STATES.map(function (v) {
                var n = st[v] || 0;
                return '<div class="ad-bar-row"><span>' + esc(statusLabel(v)) + '</span><span class="ad-bar"><i style="width:' + Math.round((n / stTotal) * 100) + '%"></i></span><em>' + num(n) + '</em></div>';
              }).join('') + '</div>'
            + '<div style="margin-top:14px;"><button type="button" class="ad-act primary" id="bsOpen">' + icon('chip') + ' ' + esc(S.bulkState) + '</button></div></div>';

        viewEl.innerHTML = head(S.overview, S.overviewSub)
            + aiCard
            + '<div class="ad-kpis">'
            + kpi(S.totalTools, num(k.tools), S.wholeCatalog, 'gold')
            + kpi(S.activeTitle, num(k.active), S.maintenance + ': ' + num((d.catalog && d.catalog.status ? (d.catalog.status.manutenzione || 0) : 0)), 'ok')
            + kpi(S.today, num(k.visits_today), S.week + ': ' + num(k.visits_week), 'ok')
            + kpi(S.uniqueVis, num(k.visitors), S.allTime + ': ' + num(k.visits_total))
            + kpi(S.openRequests, num(k.queue_open), S.messages + ': ' + num(k.messages) + ' · ' + S.notices + ': ' + num(k.notices), 'warn')
            + kpi(S.health, (health.state === 'down' ? 'KO' : (health.state === 'warn' ? 'WARN' : 'OK')),
                  health.counters ? ('ok ' + num(health.counters.ok) + ' · warn ' + num(health.counters.warn) + ' · down ' + num(health.counters.down)) : '', healthKind)
            + '</div>'
            + '<div class="ad-grid2">'
            +   '<div class="ad-card"><h2>' + esc(S.last14) + '</h2><p class="ad-sub">' + esc(S.trafficSub) + '</p>' + chart + '</div>'
            +   '<div class="ad-card"><h2>' + esc(S.catalogSub) + '</h2><p class="ad-sub">' + num(d.catalog.total) + ' ' + esc(S.filter).toLowerCase() + '</p><div class="ad-bars">' + bars + '</div></div>'
            + '</div>'
            + '<div class="ad-grid2">'
            +   '<div class="ad-card"><h2>' + esc(S.activity) + '</h2><p class="ad-sub">' + esc(S.systemSub) + '</p>'
            +     ((d.activity || []).length ? '<div class="ad-table-wrap"><table class="ad-table"><thead><tr><th>' + esc(S.details) + '</th><th>' + esc(S.date) + '</th></tr></thead><tbody>'
            +       d.activity.map(function (l) {
                        return '<tr><td><b>' + esc(l.action || '') + '</b><br><span class="ad-trunc">' + esc(l.details || '') + '</span></td>'
                             + '<td class="mono">' + esc(prettyDate(l.timestamp)) + '</td></tr>';
                    }).join('') + '</tbody></table></div>' : empty())
            +   '</div>'
            +   '<div class="ad-card"><h2>' + esc(S.topPages) + '</h2><p class="ad-sub">' + esc(S.traffic) + '</p>'
            +     (Object.keys(d.pages || {}).length ? '<div class="ad-bars">' + Object.keys(d.pages).slice(0, 8).map(function (p) {
                        var maxP = Math.max.apply(null, Object.keys(d.pages).map(function (x) { return d.pages[x]; })) || 1;
                        return '<div class="ad-bar-row"><span class="mono">' + esc(p) + '</span><span class="ad-bar"><i style="width:' + Math.round((d.pages[p] / maxP) * 100) + '%"></i></span><em>' + num(d.pages[p]) + '</em></div>';
                    }).join('') + '</div>' : empty())
            +   '</div>'
            + '</div>'
            + '<div class="ad-grid2">'
            +   topCard
            +   bulkCard
            + '</div>';

        var bs = document.getElementById('bsOpen');
        if (bs) bs.addEventListener('click', function () {
            document.querySelectorAll('.ad-link').forEach(function (b) {
                b.classList.toggle('active', b.getAttribute('data-view') === 'tools');
            });
            go('tools', true);
        });
    }

    function labelOfCategory(slug) {
        var names = { creativita: S.creativity || 'Creativity', produttivita: S.productivity || 'Productivity', educativi: S.educational || 'Educational', giochi: S.games || 'Games', utility: S.utilities || 'Utilities' };
        return names[slug] || slug;
    }

    // ---------- L.U.X. GUIDE (chat con la guida + trasferite all'admin) ----------
    function renderGuide() {
        viewEl.innerHTML = head(S.guide, S.guideSub)
            + '<div class="ad-card"><div style="display:flex;gap:8px;margin-bottom:12px;">'
            +   '<button type="button" class="ad-act" data-gf="open">' + icon('chat') + ' ' + esc(S.chatQueue) + '</button>'
            +   '<button type="button" class="ad-act" data-gf="all">' + esc(S.allChats) + '</button>'
            + '</div><div id="gdList">' + skeleton() + '</div></div>'
            + '<div id="gdChat"></div>';

        var curFilter = 'open';
        var curChat   = null;

        function chatLogHtml(log) {
            return (log || []).map(function (m) {
                return '<div style="border:1px solid var(--line-2);border-radius:10px;padding:8px 10px;"><b style="font-size:11px;color:var(--gold);text-transform:uppercase;">' + esc(m.from) + '</b><p style="margin:4px 0 0;font-size:13px;color:var(--bone);">' + esc(m.text) + '</p><span style="font-size:11px;color:var(--text-light);">' + esc(prettyDate(m.date)) + '</span></div>';
            }).join('');
        }

        // quiet = true quando e' il giro automatico: niente scheletro di
        // caricamento e nessuna riscrittura se l'elenco non e' cambiato.
        // Prima la lista si ricostruiva ogni 3 secondi e sembrava che il
        // pannello si ricaricasse all'infinito.
        function load(filter, quiet) {
            curFilter = filter;
            var list = document.getElementById('gdList');
            if (!list) return;
            if (!quiet) list.innerHTML = skeleton();
            api('guide_chats', { filter: filter }).then(function (d) {
                if (!list.isConnected) return;
                if (!d.success) { if (!quiet) { list.dataset.html = ''; list.innerHTML = empty(); } return; }
                var rows = d.rows || [];
                var html = rows.length
                    ? '<div class="ad-table-wrap"><table class="ad-table"><thead><tr><th>ID</th><th>' + esc(S.status) + '</th><th>' + esc(S.recipients) + '</th><th>' + esc(S.lastMessage) + '</th><th>' + esc(S.date) + '</th></tr></thead><tbody>'
                      + rows.map(function (r) {
                            return '<tr data-chat="' + esc(r.id) + '" style="cursor:pointer"><td class="mono">' + esc(r.id) + '</td><td>' + esc(r.status) + '</td><td>' + esc(r.lang) + '</td><td>' + esc(r.last) + '</td><td class="mono">' + esc(prettyDate(r.updated)) + '</td></tr>';
                        }).join('') + '</tbody></table></div>'
                    : empty();
                if (list.dataset.html === html) return;   // niente da ridisegnare
                list.dataset.html = html;
                list.innerHTML = html;
                document.querySelectorAll('#gdList tr[data-chat]').forEach(function (tr) {
                    tr.addEventListener('click', function () { openChat(tr.getAttribute('data-chat')); });
                });
            });
        }

        function renderChat(id, c) {
            var box = document.getElementById('gdChat');
            box.innerHTML = '<div class="ad-card"><h2>' + esc(S.conversation) + ' · ' + esc(id) + '</h2><p class="ad-sub" id="gdChatStatus">' + esc(S.status) + ': ' + esc(c.status) + ' · ' + esc(c.lang) + '</p>'
                + '<div id="gdChatLog" style="display:flex;flex-direction:column;gap:8px;max-height:340px;overflow:auto;margin-bottom:12px;">' + chatLogHtml(c.log) + '</div>'
                + '<div class="ad-field"><textarea id="gdReply" rows="3" placeholder="' + esc(S.reply) + '..."></textarea></div>'
                + '<div style="display:flex;gap:8px;"><button type="button" class="ad-act primary" id="gdSend">' + icon('send') + ' ' + esc(S.reply) + '</button>'
                + '<button type="button" class="ad-act" id="gdClose">' + esc(S.closeChat) + '</button></div></div>';
            var logEl = document.getElementById('gdChatLog');
            if (logEl) { logEl.dataset.html = chatLogHtml(c.log); logEl.scrollTop = logEl.scrollHeight; }

            document.getElementById('gdSend').addEventListener('click', function () {
                var t = document.getElementById('gdReply').value.trim();
                if (!t) return;
                api('chat_reply', { id: id, text: t }).then(function (r) {
                    if (r.success) { pop('ok', S.saved, S.guide); openChat(id); load(curFilter); }
                });
            });
            document.getElementById('gdClose').addEventListener('click', function () {
                api('chat_close', { id: id }).then(function () { pop('ok', S.saved, S.guide); syncChat(); load(curFilter); });
            });
        }

        function openChat(id) {
            curChat = id;
            api('guide_chat', { id: id }).then(function (d) {
                if (!d.success) return;
                renderChat(id, d.chat);
            });
        }

        // Sincronizzazione in tempo reale: la conversazione aperta e l'elenco
        // si aggiornano da soli. Il cliente vede subito la risposta, l'admin
        // vede subito i nuovi messaggi e le chat chiuse dal cliente.
        function syncChat() {
            if (!curChat) return;
            api('guide_chat', { id: curChat }).then(function (d) {
                if (!d.success || !d.chat) return;
                var c = d.chat;
                var st = document.getElementById('gdChatStatus');
                if (st) st.textContent = S.status + ': ' + c.status + ' · ' + c.lang;
                var logEl = document.getElementById('gdChatLog');
                if (logEl) {
                    // Si riscrive solo se c'e' davvero qualcosa di nuovo:
                    // altrimenti la conversazione aperta "lampareggiava".
                    var html = chatLogHtml(c.log);
                    if (logEl.dataset.html === html) return;
                    var atBottom = (logEl.scrollHeight - logEl.scrollTop - logEl.clientHeight) < 48;
                    logEl.dataset.html = html;
                    logEl.innerHTML = html;
                    if (atBottom) logEl.scrollTop = logEl.scrollHeight;
                }
            });
        }

        document.querySelectorAll('[data-gf]').forEach(function (b) {
            b.addEventListener('click', function () { load(b.getAttribute('data-gf')); });
        });
        load('open');

        // Giro della guida: prima ogni 3 secondi, cioe' il piu' fitto di
        // tutto il pannello, solo per una conversazione. Ora ogni 12 s, e
        // niente lavoro quando la scheda non e' in primo piano.
        var live = window.setInterval(function () {
            if (typeof view !== 'undefined' && view !== 'guide') { window.clearInterval(live); return; }
            if (!document.getElementById('gdList')) { window.clearInterval(live); return; }
            if (document.visibilityState !== 'visible') return;
            // Aggiornamento silenzioso: solo se i dati sono cambiati.
            load(curFilter, true);
            syncChat();
        }, 12000);
    }

    // ---------- FEEDBACK ----------
    function renderFeedback() {
        viewEl.innerHTML = head(S.feedback, S.feedbackSub)
            + '<div id="fbSummary" class="ad-kpis"></div>'
            + '<div id="fbList" class="ad-card">' + skeleton() + '</div>';

        api('feedback_list', {}).then(function (d) {
            if (!d.success) return;
            var s = d.summary || {};
            document.getElementById('fbSummary').innerHTML =
                kpi(S.feedbackCount, num(s.count), '', 'info')
                + kpi(S.feedbackAvg, (s.average || 0) + ' / 5', '', 'gold')
                + kpi(S.feedbackNew, num(s.new), '', 'warn');

            var rows = d.rows || [];
            document.getElementById('fbList').innerHTML = '<h2>' + esc(S.feedback) + '</h2><p class="ad-sub">' + esc(S.feedbackSub) + '</p>'
                + (rows.length ? '<div class="ad-table-wrap"><table class="ad-table"><thead><tr><th>' + esc(S.rating) + '</th><th>' + esc(S.channel) + '</th><th>' + esc(S.message) + '</th><th>' + esc(S.status) + '</th><th>' + esc(S.date) + '</th></tr></thead><tbody>'
                    + rows.map(function (r) {
                        return '<tr><td class="mono">' + num(r.rating || 0) + '/5</td><td>' + esc(r.channel) + '</td><td>' + esc(r.message || '') + (r.page ? ' <span class="mono">' + esc(r.page) + '</span>' : '') + '</td>'
                             + '<td><button type="button" class="ad-act" data-fbs="' + esc(r.id) + '" data-v="' + (r.status === 'nuovo' ? 'letto' : 'archiviato') + '">' + esc(r.status) + '</button></td><td class="mono">' + esc(prettyDate(r.date)) + '</td></tr>';
                    }).join('') + '</tbody></table></div>' : empty());

            document.querySelectorAll('[data-fbs]').forEach(function (b) {
                b.addEventListener('click', function () {
                    api('feedback_status', { id: b.getAttribute('data-fbs'), status: b.getAttribute('data-v') }).then(function () { renderFeedback(); });
                });
            });
        });
    }

    // ---------- NOTIFICA GLOBALE ----------
    function renderBroadcast() {
        var cats = (cache.notifs && cache.notifs.categories) || S.categories;
        var opts = (S.catNames || []).map(function (slug) {
            var sel = (slug === 'update') ? ' selected' : '';
            return '<option value="' + esc(slug) + '"' + sel + '>' + esc((S.catLabels || {})[slug] || slug) + '</option>';
        }).join('');

        viewEl.innerHTML = head(S.broadcast, S.broadcastSub)
            + '<div class="ad-grid2">'
            + '<div class="ad-card"><h2>' + esc(S.sendNow) + '</h2><p class="ad-sub">' + esc(S.broadcastInfo) + '</p>'
            +   '<div class="ad-2col">'
            +     '<div class="ad-field"><label>' + esc(S.titleIt) + '</label><input type="text" id="bcTitleIt" placeholder="es. Nuovo traguardo raggiunto"></div>'
            +     '<div class="ad-field"><label>' + esc(S.titleEn) + '</label><input type="text" id="bcTitleEn" placeholder="e.g. A new milestone reached"></div>'
            +   '</div>'
            +   '<div class="ad-2col">'
            +     '<div class="ad-field"><label>' + esc(S.msgIt) + '</label><textarea id="bcMsgIt" rows="3" placeholder="Cosa vuoi comunicare?"></textarea></div>'
            +     '<div class="ad-field"><label>' + esc(S.msgEn) + '</label><textarea id="bcMsgEn" rows="3" placeholder="What do you want to say?"></textarea></div>'
            +   '</div>'
            +   '<div class="ad-3col">'
            +     '<div class="ad-field"><label>' + esc(S.category) + '</label><select id="bcCategory">' + opts + '</select></div>'
            +     '<div class="ad-field"><label>' + esc(S.recipients) + '</label><select id="bcAudience">'
            +       '<option value="public">' + esc(S.sendPublic) + '</option>'
            +       '<option value="admin">' + esc(S.sendAdmin) + '</option>'
            +       '<option value="all">' + esc(S.sendAll) + '</option>'
            +     '</select></div>'
            +     '<div class="ad-field"><label>' + esc(S.redirect) + '</label><input type="text" id="bcRedirect" placeholder="/bacheca.php"></div>'
            +   '</div>'
            +   '<div class="ad-2col">'
            +     '<div class="ad-field"><label>' + esc(S.publishAt) + '</label><input type="datetime-local" id="bcPublishAt"><span class="ad-hint">' + esc(S.publishAtHint) + '</span></div>'
            +   '</div>'
            +   '<div style="display:flex;gap:10px;flex-wrap:wrap;">'
            +     '<button type="button" class="ad-act primary" id="bcSend">' + icon('send') + ' ' + esc(S.sendNow) + '</button>'
            +     '<button type="button" class="ad-act" id="bcClear">' + esc(S.reset) + '</button>'
            +   '</div>'
            + '</div>'
            + '<div class="ad-card"><h2>' + esc(S.preview || 'Anteprima') + '</h2><p class="ad-sub">' + esc(S.broadcastInfo) + '</p>'
            +   '<div class="ad-preview"><span class="ad-preview-icon">' + icon('bell') + '</span>'
            +     '<div><span class="ad-pill gold" id="bcPrevCat">update</span>'
            +       '<b id="bcPrevTitle" style="margin-top:8px;">' + esc(S.notificationSent) + '</b>'
            +       '<p id="bcPrevMsg"></p></div>'
            +   '</div>'
            + '</div>'
            + '</div>'
            + '<div class="ad-card"><h2>' + esc(S.sentNotifs) + '</h2><p class="ad-sub">' + esc(S.broadcastInfo) + '</p>'
            +   '<div style="margin-bottom:12px;"><button type="button" class="ad-act danger" id="bcPurge">' + icon('trash') + ' ' + esc(S.purgeAll) + '</button></div>'
            +   '<div id="bcList">' + skeleton() + '</div>'
            + '</div>';

        function refreshPreview() {
            var t = document.getElementById('bcTitleIt').value || document.getElementById('bcTitleEn').value;
            var m = document.getElementById('bcMsgIt').value || document.getElementById('bcMsgEn').value;
            var c = document.getElementById('bcCategory').value;
            document.getElementById('bcPrevTitle').textContent = t || S.notificationSent;
            document.getElementById('bcPrevMsg').textContent = m || '';
            document.getElementById('bcPrevCat').textContent = c;
        }
        ['bcTitleIt', 'bcTitleEn', 'bcMsgIt', 'bcMsgEn'].forEach(function (id) {
            document.getElementById(id).addEventListener('input', refreshPreview);
        });
        document.getElementById('bcCategory').addEventListener('change', refreshPreview);

        document.getElementById('bcClear').addEventListener('click', function () {
            ['bcTitleIt', 'bcTitleEn', 'bcMsgIt', 'bcMsgEn', 'bcRedirect', 'bcPublishAt'].forEach(function (id) {
                document.getElementById(id).value = '';
            });
            refreshPreview();
        });

        document.getElementById('bcSend').addEventListener('click', function () {
            var btn = this;
            var title = document.getElementById('bcTitleIt').value.trim() || document.getElementById('bcTitleEn').value.trim();
            if (!title) {
                pop('danger', S.broadcast, 'Serve almeno un titolo: senza titolo la notifica non si puo\' leggere.');
                return;
            }
            btn.disabled = true;
            api('notification_send', {
                title_it: document.getElementById('bcTitleIt').value,
                title_en: document.getElementById('bcTitleEn').value,
                message_it: document.getElementById('bcMsgIt').value,
                message_en: document.getElementById('bcMsgEn').value,
                category: document.getElementById('bcCategory').value,
                audience: document.getElementById('bcAudience').value,
                redirect: document.getElementById('bcRedirect').value,
                publish_at: (document.getElementById('bcPublishAt').value || '').replace('T', ' '),
                // L'ora scritta qui sopra e' quella dell'orologio di chi
                // scrive: insieme allo scostamento da UTC permette al server
                // di capire l'istante giusto anche se vive in un altro fuso.
                publish_tz: new Date().getTimezoneOffset()
            }).then(function (d) {
                btn.disabled = false;
                if (!d.success) { pop('danger', S.broadcast, d.error || S.errorGeneric); return; }
                cache.notifs = { items: d.items, now: d.now };
                SERVER_NOW = d.now || SERVER_NOW;
                // L'avviso a comparsa dura 5 secondi e poi si toglie; la
                // notifica intanto e' gia' nella lista qui sotto (va nelle
                // notifiche, non sparisce).
                var msg = (d.sent && d.sent.publish_at)
                    ? S.scheduledFor.replace('{when}', d.sent.publish_at)
                    : S.sentNow;
                pop('ok', S.notificationSent, title + ' · ' + msg);
                renderNotifList(d.items);
            }).catch(function () { btn.disabled = false; });
        });

        document.getElementById('bcPurge').addEventListener('click', function () {
            ask(S.purgeAll + '?', S.areYouSure, function () {
                api('notification_purge', {}).then(function (d) {
                    if (!d.success) { pop('danger', S.broadcast, d.error || S.errorGeneric); return; }
                    pop('ok', S.deleted, S.sentNotifs);
                    renderNotifList(d.items);
                });
            });
        });

        renderNotifList((cache.notifs && cache.notifs.items) || null);
    }

    // Ora del server, come la vede il pannello: serve a dire quali notifiche
    // sono ancora programmate. Prima si confrontava l'ora programmata con
    // l'orologio del browser, che vive in un altro fuso: una notifica già
    // uscita poteva sembrare ancora in attesa, e viceversa.
    var SERVER_NOW = '';
    function isScheduled(n) {
        return !!(n && n.publish_at && SERVER_NOW && n.publish_at > SERVER_NOW);
    }

    function renderNotifList(items) {
        var box = document.getElementById('bcList');
        if (!box) return;
        if (items === null) {
            api('notifications', {}).then(function (d) {
                SERVER_NOW = d.now || SERVER_NOW;
                cache.notifs = { items: d.items || [], categories: d.categories || {}, now: d.now || '' };
                renderNotifList(d.items || []);
            });
            return;
        }
        if (!items.length) { box.innerHTML = empty(); return; }
        box.innerHTML = '<div class="ad-table-wrap"><table class="ad-table"><thead><tr><th>' + esc(S.category) + '</th><th>'
            + esc(S.name) + '</th><th>' + esc(S.recipients) + '</th><th>' + esc(S.date) + '</th><th></th></tr></thead><tbody>'
            + items.map(function (n) {
                var title = n.title_it || n.title_en || n.title || '';
                var audience = n.audience === 'admin' ? S.sendAdmin : (n.audience === 'all' ? S.sendAll : S.sendPublic);
                var cuando = prettyDate(isScheduled(n) ? n.publish_at : n.date);
                return '<tr><td><span class="ad-pill gold">' + esc(n.category || 'info') + '</span></td>'
                     + '<td class="strong">' + esc(title) + (n.auto ? ' <span class="ad-pill">auto</span>' : '')
                     + (isScheduled(n) ? ' <span class="ad-pill info">' + esc(S.scheduled) + '</span>' : '') + '</td>'
                     + '<td>' + esc(audience) + '</td><td class="mono">' + esc(cuando) + '</td>'
                     + '<td><button type="button" class="ad-act danger" data-del-notif="' + esc(n.id || '') + '">' + icon('trash') + '</button></td></tr>';
            }).join('') + '</tbody></table></div>';

        box.querySelectorAll('[data-del-notif]').forEach(function (b) {
            b.addEventListener('click', function () {
                var id = this.getAttribute('data-del-notif');
                ask(S.delete + '?', S.areYouSure, function () {
                    api('notification_delete', { id: id }).then(function (d) {
                        if (d.success) { pop('ok', S.deleted); renderNotifList(d.items || []); }
                    });
                });
            });
        });
    }


    // ---------- CATALOGO ----------
    var catState = { page: 1, q: '', cat: '' };

    function renderCatalog() {
        viewEl.innerHTML = head(S.catalog, S.catalogSub) + '<div id="catStats"></div><div class="ad-card">'
            + '<div style="display:flex;gap:10px;flex-wrap:wrap;margin-bottom:14px;">'
            +   '<div class="ad-search" style="flex:1 1 260px;"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="7.5"></circle><line x1="21" y1="21" x2="16.5" y2="16.5"></line></svg>'
            +   '<input type="text" id="catQ" value="' + esc(catState.q) + '" placeholder="' + esc(S.search) + '..."></div>'
            +   '<select id="catCat" class="ad-act" style="padding:10px 12px;">'
            +     '<option value="">' + esc(S.all) + '</option>'
            +     ['creativita', 'produttivita', 'educativi', 'giochi', 'utility'].map(function (c) {
                    return '<option value="' + c + '"' + (catState.cat === c ? ' selected' : '') + '>' + esc(labelOfCategory(c)) + '</option>';
                }).join('')
            +   '</select>'
            + '</div><div id="catList">' + skeleton() + '</div>'
            + '<div style="display:flex;align-items:center;gap:12px;margin-top:16px;">'
            +   '<button type="button" class="ad-act" id="catMore">' + esc(S.loadMore) + '</button>'
            +   '<span class="ad-clock" id="catPage"></span>'
            + '</div></div>';

        api('catalog', {}).then(function (d) {
            if (!d.success) return;
            var box = document.getElementById('catStats');
            var catMax = 1;
            Object.keys(d.counts || {}).forEach(function (c) { if (d.counts[c] > catMax) catMax = d.counts[c]; });
            box.innerHTML = '<div class="ad-kpis">'
                + kpi(S.totalTools, num(d.generated + d.curated), S.wholeCatalog, 'gold')
                + kpi(S.activeTitle, num((d.states && d.states.attivo) || 0), null, 'ok')
                + kpi(S.maintenance, num((d.states && d.states.manutenzione) || 0), null, 'warn')
                + '</div>'
                + '<div class="ad-card"><h2>' + esc(S.catalogSub) + '</h2><p class="ad-sub">' + esc(S.catalog) + '</p><div class="ad-bars">'
                + Object.keys(d.counts || {}).map(function (c) {
                    return '<div class="ad-bar-row"><span>' + esc(labelOfCategory(c)) + '</span><span class="ad-bar"><i style="width:' + Math.round((d.counts[c] / catMax) * 100) + '%"></i></span><em>' + num(d.counts[c]) + '</em></div>';
                }).join('') + '</div></div>';
        });

        function load(reset) {
            if (reset) { catState.page = 1; document.getElementById('catList').innerHTML = skeleton(); }
            api('catalog', { page: catState.page, per: 18, q: catState.q, cat: catState.cat }).then(function (d) {
                if (!d.success) return;
                var list = document.getElementById('catList');
                if (reset) list.innerHTML = '';
                if (!(d.items || []).length && reset) { list.innerHTML = empty(); }
                list.insertAdjacentHTML('beforeend',
                    '<div class="ad-table-wrap"><table class="ad-table"><thead><tr><th>' + esc(S.tools) + '</th><th>' + esc(S.category) + '</th><th>' + esc(S.status) + '</th><th></th></tr></thead><tbody>'
                    + d.items.map(function (t) {
                        return '<tr><td class="strong">' + esc(t.name) + '<br><span class="ad-trunc">' + esc((t.description_it || '').slice(0, 150)) + '</span></td>'
                             + '<td>' + esc(labelOfCategory(t.category)) + '</td>'
                             + '<td><span class="ad-pill ' + (t.status === 'attivo' ? 'ok' : 'warn') + '">' + esc(stateLabel(t.status || 'attivo')) + '</span></td>'
                             + '<td><a class="ad-act" href="' + esc(t.url) + '" target="_blank" rel="noopener">' + icon('external') + ' ' + esc(S.open) + '</a> '
                             + '<button type="button" class="ad-act" data-copy="' + esc(t.url) + '">' + icon('copy') + '</button></td></tr>';
                    }).join('') + '</tbody></table></div>');
                document.getElementById('catPage').textContent =
                    S.page.replace('{n}', d.page).replace('{t}', num(Math.max(1, d.pages)));
                bindCopy(list);
                document.getElementById('catMore').hidden = (d.page >= d.pages);
            });
        }
        document.getElementById('catQ').addEventListener('input', debounce(function (e) {
            catState.q = e.target.value.trim(); load(true);
        }, 300));
        document.getElementById('catCat').addEventListener('change', function () {
            catState.cat = this.value; load(true);
        });
        document.getElementById('catMore').addEventListener('click', function () { catState.page++; load(false); });
        load(true);
    }

    function bindCopy(scope) {
        (scope || document).querySelectorAll('[data-copy]').forEach(function (b) {
            b.addEventListener('click', function () {
                var text = this.getAttribute('data-copy');
                if (navigator.clipboard) navigator.clipboard.writeText(text);
                pop('ok', S.copyLink, text);
            });
        });
    }

    // ---------- STRUMENTI SCRITTI A MANO ----------
    var toolState = { page: 1, q: '', state: '' };

    function renderTools() {
        viewEl.innerHTML = head(S.bulkState, S.bulkStateSub)
            + '<div id="csBulk"></div>'
            + '<div class="ad-card"><h2>' + esc(S.addProject) + '</h2><p class="ad-sub">' + esc(S.toolsSub) + '</p>'
            +   '<div class="ad-2col">'
            +     '<div class="ad-field"><label>' + esc(S.name) + '</label><input type="text" id="tName" placeholder="LUX.Nome"></div>'
            +     '<div class="ad-field"><label>' + esc(S.key) + '</label><input type="text" id="tKey" placeholder="nomebreve"><span class="ad-hint">Lettere e numeri: diventa /nomebreve.php</span></div>'
            +   '</div>'
            +   '<div class="ad-2col">'
            +     '<div class="ad-field"><label>' + esc(S.msgIt) + '</label><input type="text" id="tDescIt"></div>'
            +     '<div class="ad-field"><label>' + esc(S.msgEn) + '</label><input type="text" id="tDescEn"></div>'
            +   '</div>'
            +   '<div class="ad-3col">'
            +     '<div class="ad-field"><label>' + esc(S.category) + '</label><select id="tCat">'
            +       ['creativita', 'produttivita', 'educativi', 'giochi', 'utility'].map(function (c) { return '<option value="' + c + '">' + esc(labelOfCategory(c)) + '</option>'; }).join('')
            +     '</select></div>'
            +     '<div class="ad-field"><label>Icona</label><input type="text" id="tIcon" value="rocket"></div>'
            +     '<div class="ad-field"><label>Colore</label><input type="text" id="tColor" value="#D8A93C"></div>'
            +   '</div>'
            +   '<button type="button" class="ad-act primary" id="tAdd">' + icon('plus') + ' ' + esc(S.addProject) + '</button>'
            + '</div>'
            + '<div class="ad-card">'
            +   '<div style="display:flex;gap:10px;flex-wrap:wrap;margin-bottom:14px;">'
            +     '<div class="ad-search" style="flex:1 1 240px;"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="7.5"></circle><line x1="21" y1="21" x2="16.5" y2="16.5"></line></svg>'
            +     '<input type="text" id="tQ" value="' + esc(toolState.q) + '" placeholder="' + esc(S.search) + '..."></div>'
            +     '<select id="tState" class="ad-act" style="padding:10px 12px;"><option value="">' + esc(S.all) + '</option>'
            +       ['attivo', 'manutenzione', 'pausa', 'costruzione', 'verifica', 'bloccato', 'archiviato', 'disattivato'].map(function (s) {
                    return '<option value="' + s + '"' + (toolState.state === s ? ' selected' : '') + '>' + esc(stateLabel(s)) + '</option>';
                }).join('')
            +     '</select>'
            +   '</div>'
            +   '<div id="tList">' + skeleton() + '</div>'
            +   '<div style="display:flex;align-items:center;gap:12px;margin-top:16px;">'
            +     '<button type="button" class="ad-act" id="tMore">' + esc(S.loadMore) + '</button>'
            +     '<span class="ad-clock" id="tPage"></span>'
            +   '</div>'
            + '</div>';

        document.getElementById('tAdd').addEventListener('click', function () {
            var payload = {
                name: document.getElementById('tName').value.trim(),
                key: document.getElementById('tKey').value.trim(),
                description_it: document.getElementById('tDescIt').value.trim(),
                description_en: document.getElementById('tDescEn').value.trim(),
                category: document.getElementById('tCat').value,
                icon: document.getElementById('tIcon').value.trim(),
                color: document.getElementById('tColor').value.trim()
            };
            if (!payload.name) { pop('danger', S.addProject, S.name + '?'); return; }
            api('project_add', payload).then(function (d) {
                if (!d.success) { pop('danger', S.addProject, d.error || S.errorGeneric); return; }
                pop('ok', S.saved, payload.name);
                ['tName', 'tKey', 'tDescIt', 'tDescEn'].forEach(function (id) { document.getElementById(id).value = ''; });
                loadTools(true);
            });
        });

        function loadTools(reset) {
            if (reset) { toolState.page = 1; document.getElementById('tList').innerHTML = skeleton(); }
            api('projects', { page: toolState.page, per: 20, q: toolState.q, state: toolState.state }).then(function (d) {
                if (!d.success) return;
                var list = document.getElementById('tList');
                if (reset) list.innerHTML = '';
                if (!(d.items || []).length && reset) { list.innerHTML = empty(); }
                list.insertAdjacentHTML('beforeend',
                    '<div class="ad-table-wrap"><table class="ad-table"><thead><tr><th>' + esc(S.name) + '</th><th>' + esc(S.key) + '</th><th>' + esc(S.category) + '</th><th>' + esc(S.status) + '</th><th></th></tr></thead><tbody>'
                    + d.items.map(function (t) {
                        return '<tr><td class="strong">' + esc(t.name || '') + '</td><td class="mono">' + esc(t.key || '') + '</td>'
                             + '<td>' + esc(labelOfCategory(t.category)) + '</td>'
                             + '<td><select data-state="' + esc(t.key) + '">'
                             +   ['attivo', 'manutenzione', 'pausa', 'costruzione', 'verifica', 'bloccato', 'archiviato', 'disattivato'].map(function (s) {
                                    return '<option value="' + s + '"' + (t.status === s ? ' selected' : '') + '>' + esc(stateLabel(s)) + '</option>';
                                 }).join('')
                             + '</select></td>'
                             + '<td><a class="ad-act" href="' + esc(t.url || ('/' + t.key + '.php')) + '" target="_blank" rel="noopener">' + icon('external') + '</a> '
                             + '<button type="button" class="ad-act danger" data-del-tool="' + esc(t.key) + '">' + icon('trash') + '</button></td></tr>';
                    }).join('') + '</tbody></table></div>');
                document.getElementById('tPage').textContent = S.page.replace('{n}', d.page).replace('{t}', num(Math.max(1, d.pages)));
                document.getElementById('tMore').hidden = (d.page >= d.pages);

                list.querySelectorAll('[data-state]').forEach(function (sel) {
                    sel.addEventListener('change', function () {
                        api('project_state', { key: this.getAttribute('data-state'), state: this.value })
                            .then(function (r) { if (r.success) pop('ok', S.saved, r.key + ' · ' + stateLabel(r.state)); });
                    });
                });
                list.querySelectorAll('[data-del-tool]').forEach(function (b) {
                    b.addEventListener('click', function () {
                        var key = this.getAttribute('data-del-tool');
                        ask(S.delete + ' ' + key + '?', S.areYouSure, function () {
                            api('project_delete', { key: key }).then(function (r) {
                                if (r.success) { pop('ok', S.deleted, key); loadTools(true); }
                            });
                        });
                    });
                });
            });
        }

        document.getElementById('tQ').addEventListener('input', debounce(function (e) {
            toolState.q = e.target.value.trim(); loadTools(true);
        }, 300));
        document.getElementById('tState').addEventListener('change', function () {
            toolState.state = this.value; loadTools(true);
        });
        document.getElementById('tMore').addEventListener('click', function () { toolState.page++; loadTools(false); });
        loadTools(true);
        renderCatalogState(document.getElementById('csBulk'));
    }

    // ---------- STATO DI TUTTO IL CATALOGO (100.000 STRUMENTI) ----------
    // L'unico sistema: nessuna lista "scritta a mano" e nessuna lista a
    // parte. Si sceglie il gruppo (tutto il catalogo, una categoria, oppure
    // solo le voci con pagina propria), si sceglie lo stato e si applica.
    function renderCatalogState(box) {
        if (!box) return;

        var scopeOpts = '<option value="all">' + esc(S.csScopeAll) + '</option>'
                      + '<option value="roots">' + esc(S.csScopeRoots) + '</option>'
                      + CATALOG_CATS.map(function (c) {
                            return '<option value="category:' + c + '">' + esc(S.csCategory) + ': ' + esc(labelOfCategory(c)) + '</option>';
                        }).join('');
        var stateOpts = CATALOG_STATES.map(function (v) {
            return '<option value="' + v + '"' + (v === 'attivo' ? ' selected' : '') + '>' + esc(statusLabel(v)) + '</option>';
        }).join('');

        box.innerHTML = '<div class="ad-card"><h2>' + esc(S.bulkState) + '</h2><p class="ad-sub">' + esc(S.bulkStateSub) + '</p>'
            + '<div id="csCounts">' + skeleton() + '</div>'
            + '<div class="ad-3col" style="margin-top:16px;">'
            +   '<div class="ad-field"><label>' + esc(S.bulkScope) + '</label><select id="csScope">' + scopeOpts + '</select></div>'
            +   '<div class="ad-field"><label>' + esc(S.bulkStatus) + '</label><select id="csStatus">' + stateOpts + '</select></div>'
            +   '<div class="ad-field"><label>&nbsp;</label><button type="button" class="ad-act primary" id="csApply">' + icon('check') + ' ' + esc(S.applyBulk) + '</button></div>'
            + '</div>'
            + '<p class="ad-sub" style="margin:12px 0 0;">' + esc(S.csSize) + '</p>'
            + '</div>'
            + '<div class="ad-card"><h2>' + esc(S.csOne) + '</h2><p class="ad-sub">' + esc(S.csOneHint) + '</p>'
            +   '<div style="display:flex;gap:10px;flex-wrap:wrap;align-items:flex-end;">'
            +     '<div class="ad-field" style="flex:1 1 240px;margin:0;"><label>' + esc(S.csLookup) + '</label><input type="text" id="csSlug" placeholder="riassumi-email-flash"></div>'
            +     '<div class="ad-field" style="margin:0;"><label>' + esc(S.bulkStatus) + '</label><select id="csSlugState">' + stateOpts + '</select></div>'
            +     '<button type="button" class="ad-act" id="csSlugGo">' + esc(S.csSet) + '</button>'
            +   '</div>'
            + '</div>';

        function paint(counts) {
            var total = 0;
            CATALOG_STATES.forEach(function (v) { total += (counts[v] || 0); });
            if (!total) total = 1;
            document.getElementById('csCounts').innerHTML =
                '<div class="ad-kpis">'
                + kpi(S.csTools, num(total), null, 'gold')
                + kpi(S.active, num(counts.attivo || 0), null, 'ok')
                + kpi(S.maintenance, num(counts.manutenzione || 0), null, 'warn')
                + kpi(S.off, num(counts.disattivato || 0), null, 'info')
                + '</div>'
                + '<p class="ad-sub" style="margin:14px 0 6px;">' + esc(S.csNow) + '</p>'
                + '<div class="ad-bars">' + CATALOG_STATES.map(function (v) {
                    var n = counts[v] || 0;
                    return '<div class="ad-bar-row"><span>' + esc(statusLabel(v)) + '</span><span class="ad-bar"><i style="width:' + Math.round((n / total) * 100) + '%"></i></span><em>' + num(n) + '</em></div>';
                  }).join('') + '</div>';
        }

        api('catalog_status', {}).then(function (d) {
            if (!d.success) { document.getElementById('csCounts').innerHTML = empty(); return; }
            paint(d.counts || {});
        });

        document.getElementById('csApply').addEventListener('click', function () {
            var btn   = this;
            var sel   = document.getElementById('csScope');
            var scope = sel.value;
            var label = sel.options[sel.selectedIndex].text;
            var state = document.getElementById('csStatus').value;
            ask(S.bulkState + '?', statusLabel(state) + ' · ' + label, function () {
                btn.disabled = true;
                api('catalog_bulk', { scope: scope, status: state }).then(function (r) {
                    btn.disabled = false;
                    if (!r.success) { pop('danger', S.bulkState, r.error || S.errorGeneric); return; }
                    pop('ok', S.bulkDone, num(r.changed) + ' · ' + statusLabel(state));
                    paint(r.counts || {});
                    cache.overview = null;
                    updateBadges();
                }).catch(function () { btn.disabled = false; });
            });
        });

        document.getElementById('csSlugGo').addEventListener('click', function () {
            var slug  = document.getElementById('csSlug').value.trim().toLowerCase().replace(/[^a-z0-9_-]/g, '');
            var state = document.getElementById('csSlugState').value;
            if (!slug) return;
            api('project_state', { key: slug, state: state }).then(function (r) {
                if (!r.success) { pop('danger', S.csOne, r.error || S.csNotFound); return; }
                pop('ok', S.bulkDone, slug + ' · ' + statusLabel(state));
                api('catalog_status', {}).then(function (d2) { if (d2.success) paint(d2.counts || {}); });
            });
        });
    }

    function stateLabel(slug) {
        var map = {
            attivo: S.active, manutenzione: S.maintenance, pausa: S.paused, costruzione: S.building,
            verifica: S.review, bloccato: S.blocked, archiviato: S.archived, disattivato: S.off
        };
        return map[slug] || slug;
    }

    // ---------- BACHECA ----------
    function renderNotices() {
        viewEl.innerHTML = head(S.notices, S.noticesSub)
            + '<div class="ad-grid2">'
            + '<div class="ad-card"><h2 id="ntFormTitle">' + esc(S.addNotice) + '</h2><p class="ad-sub">' + esc(S.noticesSub) + '</p>'
            +   '<input type="hidden" id="ntIndex" value="-1">'
            +   '<div class="ad-2col">'
            +     '<div class="ad-field"><label>' + esc(S.titleIt) + '</label><input type="text" id="ntTitleIt"></div>'
            +     '<div class="ad-field"><label>' + esc(S.titleEn) + '</label><input type="text" id="ntTitleEn"></div>'
            +   '</div>'
            +   '<div class="ad-2col">'
            +     '<div class="ad-field"><label>' + esc(S.msgIt) + '</label><textarea id="ntMsgIt" rows="4"></textarea></div>'
            +     '<div class="ad-field"><label>' + esc(S.msgEn) + '</label><textarea id="ntMsgEn" rows="4"></textarea></div>'
            +   '</div>'
            +   '<div class="ad-3col">'
            +     '<div class="ad-field"><label>' + esc(S.category) + '</label><select id="ntType">'
            +       ['info', 'update', 'event', 'warning', 'success'].map(function (v) { return '<option value="' + v + '">' + esc(v) + '</option>'; }).join('')
            +     '</select></div>'
            +     '<div class="ad-field"><label>' + esc(S.startDate) + '</label><input type="date" id="ntStart"></div>'
            +     '<div class="ad-field"><label>' + esc(S.endDate) + '</label><input type="text" id="ntEnd" placeholder="Indefinito"></div>'
            +   '</div>'
            +   '<div class="ad-field"><label>' + esc(S.signature) + '</label><input type="text" id="ntSign" value="L.U.X. Verse"></div>'
            +   '<label class="ad-check"><input type="checkbox" id="ntPinned"> ' + esc(S.pinned) + '</label>'
            +   '<label class="ad-check"><input type="checkbox" id="ntNotify" checked> ' + esc(S.alsoNotify) + '</label>'
            +   '<div style="display:flex;gap:10px;flex-wrap:wrap;">'
            +     '<button type="button" class="ad-act primary" id="ntSave">' + icon('check') + ' ' + esc(S.addNotice) + '</button>'
            +     '<button type="button" class="ad-act" id="ntReset">' + esc(S.reset) + '</button>'
            +   '</div>'
            + '</div>'
            + '<div class="ad-card"><h2>' + esc(S.notices) + '</h2><p class="ad-sub">' + esc(S.noticesSub) + '</p><div id="ntList">' + skeleton() + '</div></div>'
            + '</div>';

        function fillState() {
            var idx = parseInt(document.getElementById('ntIndex').value, 10);
            var items = (cache.notices && cache.notices.items) || [];
            if (idx < 0 || !items[idx]) return;
            var n = items[idx];
            document.getElementById('ntTitleIt').value = n.title_it || '';
            document.getElementById('ntTitleEn').value = n.title_en || '';
            document.getElementById('ntMsgIt').value = n.message_it || '';
            document.getElementById('ntMsgEn').value = n.message_en || '';
            document.getElementById('ntType').value = n.type || 'info';
            document.getElementById('ntStart').value = n.start_date || '';
            document.getElementById('ntEnd').value = n.end_date || '';
            document.getElementById('ntSign').value = n.signature || '';
            document.getElementById('ntPinned').checked = !!n.pinned;
        }

        document.getElementById('ntSave').addEventListener('click', function () {
            var idx = parseInt(document.getElementById('ntIndex').value, 10);
            var payload = {
                index: idx,
                title_it: document.getElementById('ntTitleIt').value.trim(),
                title_en: document.getElementById('ntTitleEn').value.trim(),
                message_it: document.getElementById('ntMsgIt').value.trim(),
                message_en: document.getElementById('ntMsgEn').value.trim(),
                type: document.getElementById('ntType').value,
                start_date: document.getElementById('ntStart').value,
                end_date: document.getElementById('ntEnd').value,
                signature: document.getElementById('ntSign').value.trim(),
                pinned: document.getElementById('ntPinned').checked ? 1 : 0,
                notify: document.getElementById('ntNotify').checked ? 1 : 0
            };
            api(idx >= 0 ? 'notice_update' : 'notice_add', payload).then(function (d) {
                if (!d.success) { pop('danger', S.notices, d.error || S.errorGeneric); return; }
                cache.notices = { items: d.items };
                pop('ok', S.noticeSaved, payload.title_it || payload.title_en);
                resetForm();
                renderNoticeList(d.items);
            });
        });

        function resetForm() {
            document.getElementById('ntIndex').value = -1;
            ['ntTitleIt', 'ntTitleEn', 'ntMsgIt', 'ntMsgEn', 'ntEnd'].forEach(function (id) { document.getElementById(id).value = ''; });
            document.getElementById('ntFormTitle').textContent = S.addNotice;
            document.getElementById('ntPinned').checked = false;
        }
        document.getElementById('ntReset').addEventListener('click', resetForm);

        function renderNoticeList(items) {
            var box = document.getElementById('ntList');
            if (!items.length) { box.innerHTML = empty(); return; }
            box.innerHTML = '<div class="ad-table-wrap"><table class="ad-table"><thead><tr><th>' + esc(S.titleIt) + '</th><th>' + esc(S.category) + '</th><th>' + esc(S.date) + '</th><th></th></tr></thead><tbody>'
                + items.map(function (n, i) {
                    return '<tr><td class="strong">' + esc(n.title_it || n.title || '') + (n.pinned ? ' <span class="ad-pill gold">' + esc(S.pinned) + '</span>' : '') + '</td>'
                         + '<td>' + esc(n.type || 'info') + '</td><td class="mono">' + esc(prettyDate(n.date)) + '</td>'
                         + '<td><button type="button" class="ad-act" data-edit-notice="' + i + '">' + esc(S.edit) + '</button> '
                         + '<button type="button" class="ad-act" data-pin-notice="' + i + '">' + icon('pin') + '</button> '
                         + '<button type="button" class="ad-act danger" data-del-notice="' + i + '">' + icon('trash') + '</button></td></tr>';
                }).join('') + '</tbody></table></div>';

            box.querySelectorAll('[data-edit-notice]').forEach(function (b) {
                b.addEventListener('click', function () {
                    document.getElementById('ntIndex').value = this.getAttribute('data-edit-notice');
                    document.getElementById('ntFormTitle').textContent = S.updateNotice;
                    fillState();
                    window.scrollTo({ top: 0, behavior: 'smooth' });
                });
            });
            box.querySelectorAll('[data-pin-notice]').forEach(function (b) {
                b.addEventListener('click', function () {
                    api('notice_pin', { index: parseInt(this.getAttribute('data-pin-notice'), 10) }).then(function (d) {
                        if (d.success) { cache.notices = { items: d.items }; renderNoticeList(d.items); }
                    });
                });
            });
            box.querySelectorAll('[data-del-notice]').forEach(function (b) {
                b.addEventListener('click', function () {
                    var idx = parseInt(this.getAttribute('data-del-notice'), 10);
                    ask(S.delete + '?', S.areYouSure, function () {
                        api('notice_delete', { index: idx }).then(function (d) {
                            if (d.success) { pop('ok', S.deleted); cache.notices = { items: d.items }; renderNoticeList(d.items); updateBadges(); }
                        });
                    });
                });
            });
        }

        api('notices', {}).then(function (d) {
            cache.notices = { items: d.items || [] };
            renderNoticeList(d.items || []);
            var badge = document.getElementById('adNNotices');
            if (badge) badge.textContent = num((d.items || []).length);
        });
    }

    // ---------- MESSAGGI ----------
    function renderMessages() {
        viewEl.innerHTML = head(S.messages, S.messagesSub) + '<div class="ad-card"><div id="msgList">' + skeleton() + '</div></div>';
        api('messages', {}).then(function (d) {
            var box = document.getElementById('msgList');
            var items = d.items || [];
            var badge = document.getElementById('adNMessages');
            if (badge) badge.textContent = num(items.length);
            if (!items.length) { box.innerHTML = empty(); return; }
            box.innerHTML = '<div class="ad-table-wrap"><table class="ad-table"><thead><tr><th>' + esc(S.code) + '</th><th>' + esc(S.project) + '</th>'
                + '<th>' + esc(S.msgIt) + '</th><th>' + esc(S.status) + '</th><th>' + esc(S.date) + '</th><th></th></tr></thead><tbody>'
                + items.map(function (m) {
                    return '<tr><td class="mono">' + esc(m.code || '') + '</td><td>' + esc(m.project || '') + '</td>'
                         + '<td><span class="ad-trunc">' + esc(m.message || m.subject || '') + '</span></td>'
                         + '<td><select data-msg="' + esc(m.code || '') + '">'
                         +   ['In Attesa', 'In Lavorazione', 'Risolto', 'Archiviato'].map(function (s) {
                                return '<option value="' + s + '"' + ((m.status || 'In Attesa') === s ? ' selected' : '') + '>' + esc(s) + '</option>';
                             }).join('')
                         + '</select></td>'
                         + '<td class="mono">' + esc(prettyDate(m.date)) + '</td>'
                         + '<td><button type="button" class="ad-act danger" data-del-msg="' + esc(m.code || '') + '">' + icon('trash') + '</button></td></tr>';
                }).join('') + '</tbody></table></div>';

            box.querySelectorAll('[data-msg]').forEach(function (sel) {
                sel.addEventListener('change', function () {
                    api('message_status', { code: this.getAttribute('data-msg'), status: this.value })
                        .then(function (r) { if (r.success) pop('ok', S.saved, r.code + ' · ' + r.status); });
                });
            });
            box.querySelectorAll('[data-del-msg]').forEach(function (b) {
                b.addEventListener('click', function () {
                    var code = this.getAttribute('data-del-msg');
                    ask(S.delete + ' ' + code + '?', S.areYouSure, function () {
                        api('message_delete', { code: code }).then(function (r) { if (r.success) { pop('ok', S.deleted, code); renderMessages(); } });
                    });
                });
            });
        });
    }

    // ---------- ASSISTENZA ----------
    function renderSupport() {
        viewEl.innerHTML = head(S.support, S.supportSub) + '<div id="supKpi"></div><div class="ad-card"><div id="supList">' + skeleton() + '</div></div>';
        api('support', {}).then(function (d) {
            var q = d.queue || {};
            document.getElementById('supKpi').innerHTML = '<div class="ad-kpis">'
                + kpi(S.openRequests, num(q.open), S.queue, 'warn')
                + kpi(S.allTime, num(q.total), null, 'info')
                + kpi(S.date, q.oldest_hours ? (q.oldest_hours + ' h') : '—', 'Richiesta più vecchia')
                + kpi(S.category, q.avg_hours ? (q.avg_hours + ' h') : '—', 'Tempo medio di chiusura', 'ok')
                + '</div>';

            var box = document.getElementById('supList');
            var items = d.items || [];
            var badge = document.getElementById('adNSupport');
            if (badge) badge.textContent = num(q.open || 0);
            if (!items.length) { box.innerHTML = empty(); return; }
            box.innerHTML = '<div class="ad-table-wrap"><table class="ad-table"><thead><tr><th>' + esc(S.date) + '</th><th>' + esc(S.msgIt) + '</th>'
                + '<th>' + esc(S.status) + '</th><th></th></tr></thead><tbody>'
                + items.map(function (s) {
                    var id = s.id || s.code || '';
                    return '<tr><td class="mono">' + esc(prettyDate(s.date)) + '</td>'
                         + '<td class="strong">' + esc(s.subject || s.email || '') + '<br><span class="ad-trunc">' + esc(s.message || '') + '</span></td>'
                         + '<td><select data-sup="' + esc(id) + '">'
                         +   ['In Attesa', 'In Lavorazione', 'Risolto', 'Archiviato'].map(function (st) {
                                return '<option value="' + st + '"' + ((s.status || 'In Attesa') === st ? ' selected' : '') + '>' + esc(st) + '</option>';
                             }).join('')
                         + '</select></td><td></td></tr>';
                }).join('') + '</tbody></table></div>';

            box.querySelectorAll('[data-sup]').forEach(function (sel) {
                sel.addEventListener('change', function () {
                    api('support_update', { id: this.getAttribute('data-sup'), status: this.value })
                        .then(function (r) { if (r.success) pop('ok', S.saved, r.status); });
                });
            });
        });
    }

    // ---------- TRAFFICO ----------
    function renderTraffic() {
        viewEl.innerHTML = head(S.traffic, S.trafficSub) + skeleton();
        api('visits', {}).then(function (d) {
            var s = d.stats || {};
            var series = [];
            for (var i = 13; i >= 0; i--) {
                var day = new Date(); day.setDate(day.getDate() - i);
                var key = day.toISOString().slice(0, 10);
                series.push({ label: String(day.getDate()).padStart(2, '0') + '/' + String(day.getMonth() + 1).padStart(2, '0'), value: (s.daily && s.daily[key]) || 0 });
            }
            var max = series.reduce(function (a, b) { return Math.max(a, b.value); }, 1);
            var pages = Object.keys(s.pages || {}).slice(0, 12);
            var maxP = pages.reduce(function (a, k) { return Math.max(a, s.pages[k]); }, 1);

            viewEl.innerHTML = head(S.traffic, S.trafficSub)
                + '<div class="ad-kpis">'
                + kpi(S.today, num(s.today), null, 'ok')
                + kpi(S.week, num(s.week), null, 'info')
                + kpi(S.month, num(s.month), null, 'gold')
                + kpi(S.allTime, num(s.total), null)
                + kpi(S.uniqueVis, num(s.unique_ips), null, 'info')
                + '</div>'
                + '<div class="ad-grid2">'
                +   '<div class="ad-card"><h2>' + esc(S.last14) + '</h2><p class="ad-sub">' + esc(S.trafficSub) + '</p><div class="ad-chart">'
                +     series.map(function (p, i) {
                        return '<div class="ad-col" title="' + esc(p.label + ' · ' + p.value) + '"><i style="height:' + Math.max(3, Math.round((p.value / max) * 112)) + 'px"></i><span>' + esc(i % 2 === 0 ? p.label : '') + '</span></div>';
                      }).join('')
                +   '</div></div>'
                +   '<div class="ad-card"><h2>' + esc(S.topPages) + '</h2><p class="ad-sub">' + esc(S.traffic) + '</p><div class="ad-bars">'
                +     (pages.length ? pages.map(function (p) {
                        return '<div class="ad-bar-row"><span class="mono">' + esc(p) + '</span><span class="ad-bar"><i style="width:' + Math.round((s.pages[p] / maxP) * 100) + '%"></i></span><em>' + num(s.pages[p]) + '</em></div>';
                      }).join('') : empty())
                +   '</div></div>'
                + '</div>';
        });
    }

    // ---------- SISTEMA + LOG ----------
    function renderSystem() {
        viewEl.innerHTML = head(S.system, S.systemSub) + skeleton();
        Promise.all([api('system', {}), api('logs', { limit: 120 })]).then(function (res) {
            var d = res[0], logs = res[1];
            if (!d.success) return;
            var report = d.report || {};
            var counters = (report.counters || {});
            var files = Object.keys(d.data || {});
            var logsItems = logs.items || [];
            var badge = document.getElementById('adNLogs');
            if (badge) badge.textContent = num(logsItems.length);

            viewEl.innerHTML = head(S.system, S.systemSub)
                + '<div class="ad-kpis">'
                + kpi(S.health, (report.state === 'down' ? 'KO' : (report.state === 'warn' ? 'WARN' : 'OK')),
                      'ok ' + num(counters.ok) + ' · warn ' + num(counters.warn) + ' · down ' + num(counters.down),
                      report.state === 'down' ? 'danger' : (report.state === 'warn' ? 'warn' : 'ok'))
                + kpi(S.phpVersion, d.php || '?', d.server || '', 'info')
                + kpi(S.aiConfigured, d.ai ? S.yes : S.no, null, d.ai ? 'ok' : 'danger')
                + kpi(S.curl, d.curland ? S.yes : S.no, null, d.curland ? 'ok' : 'warn')
                + kpi(S.diskFree, d.disk_free !== null ? (d.disk_free + ' MB') : '—', null)
                + kpi(S.serverTime, (d.time || '').slice(11), (d.time || '').slice(0, 10), 'info')
                + '</div>'
                + '<div class="ad-grid2">'
                +   '<div class="ad-card"><h2>' + esc(S.activity) + '</h2><p class="ad-sub">' + esc(S.systemSub) + '</p>'
                +     '<div class="ad-table-wrap"><table class="ad-table"><thead><tr><th>' + esc(S.details) + '</th><th>IP</th><th>' + esc(S.date) + '</th></tr></thead><tbody>'
                +       logsItems.map(function (l) {
                          return '<tr><td><b>' + esc(l.action || '') + '</b><br><span class="ad-trunc">' + esc(l.details || '') + '</span></td>'
                               + '<td class="mono">' + esc(l.ip || '') + '</td><td class="mono">' + esc(prettyDate(l.timestamp)) + '</td></tr>';
                        }).join('')
                +     '</tbody></table></div>'
                +   '</div>'
                +   '<div class="ad-card"><h2>' + esc(S.storage) + '</h2><p class="ad-sub">data/*.json</p><div class="ad-bars">'
                +     (files.length ? files.map(function (f) {
                        var maxF = Math.max.apply(null, files.map(function (x) { return d.data[x]; })) || 1;
                        return '<div class="ad-bar-row"><span class="mono">' + esc(f) + '</span><span class="ad-bar"><i style="width:' + Math.round((d.data[f] / maxF) * 100) + '%"></i></span><em>' + Math.round(d.data[f] / 1024) + ' KB</em></div>';
                      }).join('') : empty())
                +   '</div></div>'
                + '</div>';
        });
    }

    // ============================================================
    // NAVIGAZIONE
    // ============================================================
    var renderers = {
        dashboard: renderDashboard,
        broadcast: renderBroadcast,
        catalog:   renderCatalog,
        tools:     renderTools,
        notices:   renderNotices,
        messages:  renderMessages,
        support:   renderSupport,
        guide:     renderGuide,
        feedback:  renderFeedback,
        traffic:   renderTraffic,
        system:    renderSystem
    };
    var titles = {
        dashboard: [S.overview, S.overviewSub],
        broadcast: [S.broadcast, S.broadcastSub],
        catalog:   [S.catalog, S.catalogSub],
        tools:     [S.bulkState, S.bulkStateSub],
        notices:   [S.notices, S.noticesSub],
        messages:  [S.messages, S.messagesSub],
        support:   [S.support, S.supportSub],
        guide:     [S.guide, S.guideSub],
        feedback:  [S.feedback, S.feedbackSub],
        traffic:   [S.traffic, S.trafficSub],
        system:    [S.system, S.systemSub]
    };

    function go(name, force) {
        if (!renderers[name]) name = 'dashboard';
        view = name;
        document.querySelectorAll('.ad-link').forEach(function (b) {
            b.classList.toggle('active', b.getAttribute('data-view') === name);
        });
        document.getElementById('adSide').classList.remove('open');
        history.replaceState(null, '', '#' + name);
        // La panoramica si ricarica sempre: e' il cruscotto, se i numeri sono
        // vecchi non serve a niente.
        if (name === 'dashboard' || force) cache.overview = null;
        renderers[name]();
    }

    document.querySelectorAll('.ad-link').forEach(function (b) {
        b.addEventListener('click', function () { go(this.getAttribute('data-view')); });
    });
    document.getElementById('adBurger').addEventListener('click', function () {
        document.getElementById('adSide').classList.toggle('open');
    });
    document.getElementById('adQuickBroadcast').addEventListener('click', function () { go('broadcast', true); });
    document.getElementById('adReload').addEventListener('click', function () {
        cache = {};
        pop('ok', S.loadingData, S.refresh);
        go(view, true);
    });

    // ============================================================
    // TAVOLOZZA (Ctrl+K) — sezioni e strumenti del catalogo
    // ============================================================
    var pal = document.getElementById('adPal');
    var palInput = document.getElementById('adPalInput');
    var palList = document.getElementById('adPalList');
    var palSel = 0;
    var palResults = [];

    function palSections(q) {
        var out = [];
        Object.keys(titles).forEach(function (k) {
            var label = titles[k][0];
            if (!q || label.toLowerCase().indexOf(q) !== -1) {
                out.push({ kind: 'view', view: k, label: label, hint: 'Ctrl+K' });
            }
        });
        return out;
    }
    function palRender() {
        palList.innerHTML = palResults.length
            ? palResults.map(function (r, i) {
                return '<button type="button" class="ad-pal-item' + (i === palSel ? ' sel' : '') + '" data-i="' + i + '">'
                     + (r.kind === 'view' ? icon('grid') : icon('chip')) + ' ' + esc(r.label)
                     + '<small>' + esc(r.hint || '') + '</small></button>';
              }).join('')
            : '<div class="ad-empty">' + esc(S.nothing) + '</div>';
        palList.querySelectorAll('[data-i]').forEach(function (b) {
            b.addEventListener('click', function () { palPick(parseInt(this.getAttribute('data-i'), 10)); });
        });
    }
    function palPick(i) {
        var r = palResults[i];
        if (!r) return;
        pal.hidden = true;
        if (r.kind === 'view') { go(r.view, true); return; }
        // Strumento del catalogo: si apre la sezione e si imposta la ricerca.
        catState.q = r.label; catState.page = 1; catState.cat = '';
        go('catalog');
    }
    function palOpen() {
        pal.hidden = false;
        palInput.value = '';
        palResults = palSections('');
        palSel = 0;
        palRender();
        palInput.focus();
    }
    document.addEventListener('keydown', function (e) {
        if ((e.ctrlKey || e.metaKey) && e.key.toLowerCase() === 'k') { e.preventDefault(); palOpen(); return; }
        if (e.key === 'Escape') { pal.hidden = true; return; }
        if (pal.hidden) return;
        if (e.key === 'ArrowDown') { palSel = Math.min(palSel + 1, palResults.length - 1); palRender(); e.preventDefault(); }
        if (e.key === 'ArrowUp') { palSel = Math.max(palSel - 1, 0); palRender(); e.preventDefault(); }
        if (e.key === 'Enter') { palPick(palSel); }
    });
    pal.addEventListener('click', function (e) { if (e.target === pal) pal.hidden = true; });
    var palTimer = null;
    palInput.addEventListener('input', function () {
        var q = this.value.trim();
        clearTimeout(palTimer);
        palTimer = setTimeout(function () {
            var base = palSections(q.toLowerCase());
            if (q.length >= 2) {
                api('catalog', { page: 1, per: 8, q: q }).then(function (d) {
                    var tools = (d.items || []).map(function (t) {
                        return { kind: 'tool', label: t.name, hint: labelOfCategory(t.category) };
                    });
                    palResults = base.concat(tools);
                    palSel = 0;
                    palRender();
                });
            } else {
                palResults = base;
                palSel = 0;
                palRender();
            }
        }, 220);
    });

    // La barra di ricerca in alto: preme la stessa cosa della tavolozza.
    document.getElementById('adSearch').addEventListener('focus', function () { this.blur(); palOpen(); });

    // ============================================================
    // CONFERMA (una modale piccola, niente popup del browser)
    // ============================================================
    function ask(title, text, onYes) {
        var wrap = document.createElement('div');
        wrap.className = 'ad-pal';
        wrap.innerHTML = '<div class="ad-pal-box" style="max-width:420px;padding:22px;">'
            + '<b style="display:block;color:var(--bone);font-size:15px;margin-bottom:6px;">' + esc(title) + '</b>'
            + '<p style="margin:0 0 18px;color:var(--text-light);font-size:13px;">' + esc(text || '') + '</p>'
            + '<div style="display:flex;gap:10px;justify-content:flex-end;">'
            + '<button type="button" class="ad-act" data-no="1">' + esc(S.cancel) + '</button>'
            + '<button type="button" class="ad-act danger" data-yes="1">' + esc(S.confirm) + '</button>'
            + '</div></div>';
        document.body.appendChild(wrap);
        function close() { wrap.remove(); }
        wrap.querySelector('[data-no]').addEventListener('click', close);
        wrap.querySelector('[data-yes]').addEventListener('click', function () { close(); onYes(); });
        wrap.addEventListener('click', function (e) { if (e.target === wrap) close(); });
    }

    function debounce(fn, ms) {
        var t = null;
        return function () {
            var args = arguments, self = this;
            clearTimeout(t);
            t = setTimeout(function () { fn.apply(self, args); }, ms);
        };
    }

    // ============================================================
    // AVVIO: i numeri del pannello arrivano dall'API, la pagina e' gia' usabile
    // ============================================================
    function updateBadges() {
        api('overview', {}).then(function (d) {
            if (!d.success) return;
            cache.overview = d;
            var k = d.kpi || {};
            var map = { adNNotices: k.notices, adNMessages: k.messages, adNSupport: k.queue_open, adNVisits: k.visits_today, adNLogs: k.logs, adNTools: k.tools,
                        adNGuide: k.guide_queue, adNFeedback: (d.feedback ? d.feedback.new : 0) };
            Object.keys(map).forEach(function (id) {
                var el = document.getElementById(id);
                if (el) el.textContent = num(map[id]);
            });
            if (view === 'dashboard') renderDashboard();
        });
    }

    // ============================================================
    // TEMPO REALE — una sola chiamata leggera dice se è cambiato qualcosa
    // ============================================================
    // Prima ogni pezzo aveva il suo polling (campanello 15s, chat 4s,
    // pannello 6s): le cose arrivavano in ritardo o non arrivavano. Ora c'è
    // una revisione condivisa: se cambia, si ricaricano i numeri e la vista
    // aperta, subito.
    // OGNI QUANTO SI CONTROLLA
    // Prima era fisso ogni 4 secondi, sempre: anche a scheda in secondo piano
    // e anche quando non cambiava niente. Erano circa 21 chiamate al minuto
    // per una sola scheda aperta (oltre 30.000 al giorno): su un servizio
    // condiviso ogni chiamata costa, e il conto lo paga tutto il sito.
    // Ora si parte da 15 s, si raddoppia fino a un minuto quando non c'e'
    // nulla di nuovo, si torna subito veloci appena qualcosa cambia e si
    // smette del tutto quando la scheda non e' in primo piano.
    var LUX_REV = '';
    var RT_BASE = 15000, RT_MAX = 60000;
    var rtQuiet = 0, rtTimer = null, rtBusy = false;

    function rtNextMs() { return Math.min(RT_MAX, RT_BASE * Math.pow(2, Math.min(2, rtQuiet))); }
    function rtPlan(ms) {
        if (rtTimer) window.clearTimeout(rtTimer);
        rtTimer = window.setTimeout(rtRun, ms || rtNextMs());
    }

    function rtRun() {
        if (document.visibilityState !== 'visible') { rtPlan(); return; }
        if (rtBusy) { rtPlan(3000); return; }      // una chiamata per volta
        rtBusy = true;
        fetch('/api/realtime.php?since=' + encodeURIComponent(LUX_REV), { cache: 'no-store', credentials: 'same-origin' })
            .then(function (r) { return r.json(); })
            .then(function (d) {
                rtBusy = false;
                if (!d || !d.success || !d.changed) { rtQuiet++; rtPlan(); return; }
                rtQuiet = 0;
                LUX_REV = d.rev;
                updateBadges();
                // Le viste che mostrano dati vivi si ricaricano da sole.
                if (view === 'notices' || view === 'catalog' || view === 'tools' || view === 'feedback') go(view, true);
                rtPlan();
            })
            .catch(function () { rtBusy = false; rtQuiet++; rtPlan(); });
    }

    // Chiamata a mano (ritorno sulla scheda, dopo aver salvato qualcosa):
    // controllo subito e si riparte veloci.
    function realtimeTick() {
        rtQuiet = 0;
        if (rtTimer) window.clearTimeout(rtTimer);
        rtTimer = null;
        rtRun();
    }
    rtRun();

    var clock = document.getElementById('adClock');
    function tick() {
        var d = new Date();
        clock.textContent = d.toLocaleDateString('it-IT') + ' · ' + d.toLocaleTimeString('it-IT');
    }
    tick();
    setInterval(tick, 30000);

    // I pulsanti indietro/avanti del browser e i link con #sezione devono
    // funzionare: senza questo listener il cancelletto cambierebbe e la vista
    // resterebbe quella di prima.
    window.addEventListener('hashchange', function () {
        var h = (location.hash || '#dashboard').replace('#', '');
        if (h !== view) go(h, true);
    });

    var start = (location.hash || '#dashboard').replace('#', '');
    go(start);
    updateBadges();

    // Ricarica quando la scheda torna in primo piano: quello che vedi e' vero.
    document.addEventListener('visibilitychange', function () {
        if (document.visibilityState === 'visible') { updateBadges(); realtimeTick(); }
    });
})();
</script>

<?php lux_footer(); ?>
