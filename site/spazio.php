<?php
// ============================================================
// IL MIO SPAZIO — L.U.X. VERSE 39.1.1 (WS2)
// ============================================================
// La continuita' fra dispositivi senza registrazione. Qui si vedono gli
// strumenti usati di recente, i preferiti e i risultati salvati; con il
// codice personale tutto questo si ritrova su un altro telefono o computer.
//
// Il codice lo genera il server (/api/space.php, op=new): 12 caratteri da
// un alfabeto senza lettere che si confondono. Sul server non si conserva
// il codice, ma solo la sua impronta: non e' una password, e' una chiave.
// ============================================================

require_once __DIR__ . '/config.php';
lux_session_start();

$lang = currentLang();
$isIt = ($lang !== 'en');

$agentN   = function_exists('lux_agent_count') ? lux_agent_count() : 0;
$engines  = function_exists('lux_agent_engine_counts') ? lux_agent_engine_counts() : ['local' => 0, 'ai' => 0];

$pageTitle       = $isIt ? 'Il mio spazio — L.U.X. Verse' : 'My space — L.U.X. Verse';
$pageDescription = $isIt
    ? 'Cronologia, preferiti, risultati salvati e memoria dell\'assistente: il tuo spazio personale su L.U.X. Verse, senza registrazione. Un codice personale lo ritrova su ogni dispositivo.'
    : 'History, favourites, saved results and assistant memory: your personal space on L.U.X. Verse, with no registration. A personal code finds it on any device.';
$pageKeywords = 'spazio personale, cronologia strumenti, preferiti, risultati salvati, codice personale, senza registrazione';
$pageCSS      = 'assets/css/index.css';

lux_header();
?>
<main>

    <section class="main-section" style="padding-top:56px;">
        <div class="container">
            <div class="sec-head">
                <div class="sec-head-main">
                    <span class="sec-index"><?= icon('user', 13) ?> <?= t('My space') ?></span>
                    <h1 class="sec-title"><?= t('Everything you have done, in one place') ?></h1>
                    <p class="sec-sub"><?= t('The tools you used recently, the ones you saved and the results you kept. All of it stays yours: no account, no email, no password.') ?></p>
                </div>
                <span class="sec-rule" aria-hidden="true"></span>
                <a href="/roadmap.php" class="btn-small"><?= icon('time', 14) ?> <?= t('Release roadmap') ?></a>
            </div>

            <div class="space-grid">
                <!-- ---------- IL CODICE PERSONALE ---------- -->
                <div class="space-card" style="grid-column:1/-1">
                    <h3><?= icon('key', 14) ?> <?= t('Personal code') ?></h3>
                    <div class="space-code">
                        <code data-lux-space-code><?= t('No code yet') ?></code>
                        <button type="button" class="btn-small" id="space-new"><?= icon('plus', 13) ?> <?= t('Create my code') ?></button>
                        <button type="button" class="btn-small" id="space-copy" hidden><?= icon('copy', 13) ?> <?= t('Copy') ?></button>
                        <button type="button" class="btn-small" id="space-sync" hidden><?= icon('refresh', 13) ?> <?= t('Sync now') ?></button>
                        <button type="button" class="btn-small" id="space-forget" hidden><?= icon('close', 12) ?> <?= t('Unlink this device') ?></button>
                    </div>
                    <div class="space-code" style="margin-top:12px">
                        <input type="text" id="space-code-input" maxlength="16" inputmode="latin" autocomplete="off"
                               placeholder="<?= t('Already have a code? Write it here') ?>" aria-label="<?= t('Personal code') ?>">
                        <button type="button" class="btn-small" id="space-adopt"><?= icon('check', 13) ?> <?= t('Use this code') ?></button>
                    </div>
                    <p class="space-note">
                        <?= t('Write the code down or keep it somewhere safe: it is the only key to your space. It is not an account, nobody can identify you with it, and on our side we only keep its fingerprint, never the code itself.') ?>
                    </p>
                    <p class="space-status" id="space-status" aria-live="polite"></p>
                </div>

                <!-- ---------- CRONOLOGIA ---------- -->
                <div class="space-card">
                    <h3><?= icon('time', 14) ?> <?= t('Recently used') ?></h3>
                    <div class="space-list" id="space-history"><p class="space-empty"><?= t('Nothing here yet. Use a tool and it will show up.') ?></p></div>
                </div>

                <!-- ---------- PREFERITI ---------- -->
                <div class="space-card">
                    <h3><?= icon('star', 14) ?> <?= t('Favourites') ?></h3>
                    <div class="space-list" id="space-favs"><p class="space-empty"><?= t('No favourites yet. Use the star on a project.') ?></p></div>
                </div>

                <!-- ---------- RISULTATI SALVATI ---------- -->
                <div class="space-card" style="grid-column:1/-1">
                    <h3><?= icon('bookmark', 14) ?> <?= t('Saved results') ?></h3>
                    <div class="space-list" id="space-saved"><p class="space-empty"><?= t('No saved results yet. When the agent gives you a result, press Save.') ?></p></div>
                </div>

                <!-- ---------- MEMORIA DELL'ASSISTENTE ---------- -->
                <div class="space-card" style="grid-column:1/-1">
                    <h3><?= icon('brain', 14) ?> <?= t('What the assistant remembers') ?></h3>
                    <p class="space-empty"><?= t('The assistant keeps in mind how you like to be answered and the tools you use most: it changes how it writes, not who you are.') ?></p>
                    <div class="space-code" style="margin-top:10px">
                        <input type="text" id="space-tone" maxlength="60" placeholder="<?= t('Preferred tone (for example: short and practical)') ?>">
                        <input type="text" id="space-note" maxlength="200" placeholder="<?= t('A note you want it to remember') ?>">
                        <button type="button" class="btn-small" id="space-memory-save"><?= icon('check', 13) ?> <?= t('Save') ?></button>
                    </div>
                    <p class="space-note"><?= t('This memory lives in your space, together with your code: it is not used for advertising and it is not shared with anyone.') ?></p>
                </div>
            </div>

            <div class="cta-box" style="margin-top:22px">
                <h3><?= t('What you can do from here') ?></h3>
                <p><?= t('The agent can run {n} tools for you right now ({a} of them instant, without any AI call). Write what you need on the home page: it picks the tools, runs them and gives you the finished result.', ['n' => (string)$agentN, 'a' => (string)($engines['local'] ?? 0)]) ?></p>
                <div class="cta-buttons">
                    <a href="/index.php" class="btn-neon"><?= icon('sparkles', 16) ?> <?= t('Try the agent') ?></a>
                    <a href="/proponi.php" class="btn-secondary"><?= icon('bulb', 16) ?> <?= t('Suggest a tool') ?></a>
                </div>
            </div>
        </div>
    </section>

</main>

<script>
(function () {
    "use strict";
    if (!window.LuxSpace) return;
    var S = window.LuxSpace;
    var T = {
        copy      : <?= json_encode(t('Copy')) ?>,
        copied    : <?= json_encode(t('Copied')) ?>,
        synced    : <?= json_encode(t('Everything is aligned with your other devices.')) ?>,
        created   : <?= json_encode(t('Code created. Write it down: it is the only key to your space.')) ?>,
        adopted   : <?= json_encode(t('Space found. Your history and your results are here.')) ?>,
        forgotten : <?= json_encode(t('This device is no longer linked. Your code still works elsewhere.')) ?>,
        saved     : <?= json_encode(t('Saved.')) ?>,
        err       : <?= json_encode(t('Something went wrong. Try again.')) ?>,
        badCode   : <?= json_encode(t('That code is not valid. Check it and try again.')) ?>,
        del       : <?= json_encode(t('Remove')) ?>,
        open      : <?= json_encode(t('Open')) ?>,
        results   : <?= json_encode(t('results saved')) ?>
    };

    function esc(s) { return S.esc(s); }
    function $(id) { return document.getElementById(id); }
    function status(msg, bad) {
        var el = $('space-status');
        if (!el) return;
        el.textContent = msg || '';
        el.className = 'space-status' + (bad ? ' err' : '');
    }
    function rowFor(o) {
        return '<a class="space-row" href="' + esc(o.url || '#') + '">' +
                 '<span class="space-row-main"><b>' + esc(o.name || o.title || '') + '</b>' +
                 (o.sub ? '<span>' + esc(o.sub) + '</span>' : '') + '</span>' +
               '</a>';
    }

    function render() {
        var h = S.history() || [];
        var box = $('space-history');
        if (box) box.innerHTML = h.length
            ? h.map(function (r) { return rowFor({ url: r.url, name: r.name || r.key, sub: r.at || '' }); }).join('')
            : '<p class="space-empty">' + esc(<?= json_encode(t('Nothing here yet. Use a tool and it will show up.')) ?>) + '</p>';

        var f = (S.get(S.key.favs, []) || []);
        var fbox = $('space-favs');
        if (fbox) fbox.innerHTML = f.length
            ? f.map(function (x) { return rowFor({ url: x.url, name: x.name || x.key }); }).join('')
            : '<p class="space-empty">' + esc(<?= json_encode(t('No favourites yet. Use the star on a project.')) ?>) + '</p>';

        var sv = S.saved() || [];
        var sbox = $('space-saved');
        if (sbox) sbox.innerHTML = sv.length
            ? sv.map(function (r) {
                return '<div class="space-row"><span class="space-row-main"><b>' + esc(r.title || r.tool || '') + '</b>' +
                       '<span>' + esc((r.content || '').substring(0, 160)) + (r.content && r.content.length > 160 ? '…' : '') + '</span></span>' +
                       '<button type="button" class="btn-small" data-open="' + esc(r.id) + '">' + esc(T.open) + '</button>' +
                       '<button type="button" class="btn-small" data-del="' + esc(r.id) + '">' + esc(T.del) + '</button></div>';
              }).join('')
            : '<p class="space-empty">' + esc(<?= json_encode(t('No saved results yet. When the agent gives you a result, press Save.')) ?>) + '</p>';

        if (sbox) {
            sbox.querySelectorAll('[data-del]').forEach(function (b) {
                b.addEventListener('click', function () { S.removeSaved(b.dataset.del); render(); });
            });
            sbox.querySelectorAll('[data-open]').forEach(function (b) {
                b.addEventListener('click', function () {
                    var r = (S.saved() || []).filter(function (x) { return x.id === b.dataset.open; })[0];
                    if (!r) return;
                    var w = window.open('', '_blank');
                    if (w) w.document.write('<pre style="white-space:pre-wrap;font:14px/1.6 system-ui;padding:20px;max-width:900px;margin:auto">' + esc(r.content) + '</pre>');
                });
            });
        }

        var mem = S.memory();
        if ($('space-tone')) $('space-tone').value = mem.tone || '';
        if ($('space-note')) $('space-note').value = mem.note || '';

        S.refreshBadges();
        var linked = !!S.code();
        ['space-copy', 'space-sync', 'space-forget'].forEach(function (id) { if ($(id)) $(id).hidden = !linked; });
    }

    if ($('space-new')) $('space-new').addEventListener('click', function () {
        status(<?= json_encode(t('Creating your code...')) ?>);
        S.createCode().then(function () { render(); status(T.created); })
                      .catch(function (e) { status((e && e.message) || T.err, true); });
    });

    if ($('space-copy')) $('space-copy').addEventListener('click', function () {
        var c = S.pretty();
        if (navigator.clipboard) navigator.clipboard.writeText(c).catch(function () {});
        status(T.copied);
    });

    if ($('space-sync')) $('space-sync').addEventListener('click', function () {
        status(<?= json_encode(t('Aligning...')) ?>);
        S.sync().then(function () { render(); status(T.synced); })
                .catch(function () { status(T.err, true); });
    });

    if ($('space-forget')) $('space-forget').addEventListener('click', function () {
        S.forget(); render(); status(T.forgotten);
    });

    if ($('space-adopt')) $('space-adopt').addEventListener('click', function () {
        var v = ($('space-code-input') || {}).value || '';
        if (v.replace(/[^A-Za-z0-9]/g, '').length !== 12) { status(T.badCode, true); return; }
        status(<?= json_encode(t('Looking for your space...')) ?>);
        S.adoptCode(v).then(function () { render(); status(T.adopted); })
                      .catch(function (e) { status((e && e.message) || T.badCode, true); });
    });

    if ($('space-memory-save')) $('space-memory-save').addEventListener('click', function () {
        S.setMemory({ tone: ($('space-tone') || {}).value || '', note: ($('space-note') || {}).value || '' });
        var go = S.code() ? S.sync() : Promise.resolve(true);
        go.then(function () { status(T.saved); }).catch(function () { status(T.saved); });
    });

    render();
    // All'apertura, se c'e' un codice, si allinea una volta: cosi' quello che
    // hai fatto su un altro dispositivo si vede subito anche qui.
    if (S.code()) S.sync().then(render).catch(function () {});
})();
</script>

<?php lux_footer(); ?>
