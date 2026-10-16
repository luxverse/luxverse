<?php
// ============================================================
// API PER SVILUPPATORI — L.U.X. VERSE 39.1.1 (WS6)
// ============================================================
// Da sito a piattaforma su cui altri costruiscono sopra. Qui c'e' tutto e
// solo quello che esiste davvero:
//   POST /api/apikey.php   → una chiave anonima (nessuna email, nessun account)
//   POST /api/run.php      → esegue uno strumento del registro
//   GET  /api/run.php?op=docs → il registro completo (tool, ingressi, uscita)
//
// La chiave non identifica una persona: serve a contare le richieste e a
// fermare gli abusi. Il limite e' 60 richieste all'ora per chiave.
// La sandbox qui sotto usa la stessa API: quello che provi e' quello che
// otterrai nel tuo codice.
// ============================================================

require_once __DIR__ . '/config.php';
lux_session_start();

$lang   = currentLang();
$isIt   = ($lang !== 'en');
$tools  = function_exists('lux_agent_tools') ? lux_agent_tools() : [];
$nTools = count($tools);
$nLocal = 0;
foreach ($tools as $t) { if ((string)($t['engine'] ?? '') === 'local') $nLocal++; }

$pageTitle       = $isIt ? 'API per sviluppatori — L.U.X. Verse' : 'Developer API — L.U.X. Verse';
$pageDescription = $isIt
    ? 'Usa gli strumenti di L.U.X. Verse dentro i tuoi progetti: chiave anonima, limite di 60 richieste all\'ora, documentazione ed esempi in JavaScript.'
    : 'Use the L.U.X. Verse tools inside your own projects: anonymous key, 60 requests per hour, documentation and JavaScript examples.';
$pageKeywords = 'API, sviluppatori, chiave API, SDK JavaScript, L.U.X. Verse';
$pageCSS      = 'assets/css/index.css';

lux_header();
?>
<main>

    <section class="main-section" style="padding-top:56px;">
        <div class="container">
            <div class="sec-head">
                <div class="sec-head-main">
                    <span class="sec-index"><?= icon('code', 13) ?> <?= t('Developer API') ?> · v<?= htmlspecialchars((string)LUX_SITE_VERSION) ?></span>
                    <h1 class="sec-title"><?= t('Build on top of L.U.X. Verse') ?></h1>
                    <p class="sec-sub"><?= t('Not a shop and not a product: a public API to use our tools inside your own projects. No account, no credit card, no personal data.') ?></p>
                </div>
                <span class="sec-rule" aria-hidden="true"></span>
                <a href="/api/run.php?op=docs" class="btn-small" target="_blank" rel="noopener"><?= icon('external', 14) ?> <?= t('The registry in JSON') ?></a>
            </div>

            <div class="space-grid">
                <div class="space-card">
                    <h3><?= icon('key', 14) ?> <?= t('1. Ask for a key') ?></h3>
                    <p class="space-empty"><?= t('A key is just a name for your requests: it does not identify you and it can be replaced whenever you want.') ?></p>
                    <div class="space-code"><code id="dv-key">—</code></div>
                    <div class="space-code" style="margin-top:10px">
                        <button type="button" class="btn-neon" id="dv-newkey"><?= icon('key', 14) ?> <span><?= t('Get a key') ?></span></button>
                        <button type="button" class="btn-small" id="dv-copykey" hidden><?= icon('copy', 13) ?> <?= t('Copy') ?></button>
                    </div>
                    <p class="space-status" id="dv-status" aria-live="polite"></p>
                    <p class="space-note"><?= t('Limit: 60 requests per hour per key. Keep it private: if it leaks, ask for a new one.') ?></p>
                </div>

                <div class="space-card">
                    <h3><?= icon('cpu', 14) ?> <?= t('2. What you can run') ?></h3>
                    <p class="space-empty"><?= t('{n} tools are runnable right now, {a} of them instant (they answer without any AI call). The others are pages to open: they are not in the registry yet.', ['n' => (string)$nTools, 'a' => (string)$nLocal]) ?></p>
                    <div class="space-code" style="margin-top:10px">
                        <input type="text" id="dv-q" placeholder="<?= t('Filter the tools...') ?>" aria-label="<?= t('Filter the tools') ?>">
                    </div>
                    <div class="space-list" id="dv-tools" style="margin-top:10px;max-height:320px;overflow:auto"></div>
                </div>

                <div class="space-card" style="grid-column:1/-1">
                    <h3><?= icon('pulse', 14) ?> <?= t('3. The call, and a sandbox to try it here') ?></h3>
                    <p class="space-empty"><?= t('One endpoint, one shape: you send the tool and its inputs, you get the same result the site would give.') ?></p>
<pre style="margin:12px 0 0;font-family:var(--font-mono);font-size:12px;color:var(--text-light);background:var(--ink-2);border:1px solid var(--line);border-radius:8px;padding:12px;white-space:pre-wrap">POST <?= htmlspecialchars(LUX_SITE_URL) ?>/api/run.php
Content-Type: application/json

{
  "key":   "lux_...",
  "tool":  "workmebase64",
  "inputs": { "text": "ciao", "mode": "encode" }
}

→ { "success": true, "engine": "local", "content": "Y2lhbyBp", "output": "mono" }</pre>

                    <div class="space-code" style="margin-top:14px">
                        <input type="text" id="sb-tool" value="workmebase64" placeholder="<?= t('Tool') ?>" aria-label="<?= t('Tool') ?>">
                        <input type="text" id="sb-input" placeholder="<?= t('Text for the first field of the tool') ?>" aria-label="<?= t('Text') ?>" style="flex:2">
                        <button type="button" class="btn-neon" id="sb-run"><?= icon('play', 14) ?> <span><?= t('Try it') ?></span></button>
                    </div>
                    <pre id="sb-out" style="margin:12px 0 0;font-family:var(--font-mono);font-size:12px;color:var(--text-light);background:var(--ink-2);border:1px solid var(--line);border-radius:8px;padding:12px;white-space:pre-wrap;min-height:44px">—</pre>
                </div>

                <div class="space-card" style="grid-column:1/-1">
                    <h3><?= icon('tools', 14) ?> <?= t('4. The small JavaScript SDK') ?></h3>
                    <p class="space-empty"><?= t('No package to install: four lines and it works in any modern browser. Copy it, change the key and you are done.') ?></p>
<pre style="margin:12px 0 0;font-family:var(--font-mono);font-size:12px;color:var(--text-light);background:var(--ink-2);border:1px solid var(--line);border-radius:8px;padding:12px;white-space:pre-wrap">const LUX = (key) =&gt; async (tool, inputs) =&gt; {
  const r = await fetch('<?= htmlspecialchars(LUX_SITE_URL) ?>/api/run.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ key, tool, inputs })
  });
  return r.json();
};

const lux = LUX('lux_la_tua_chiave');
const out = await lux('workmebase64', { text: 'ciao', mode: 'encode' });
console.log(out.content);   // Y2lhbyBp</pre>
                    <p class="space-note"><?= t('Rules of the house: no resale, no hidden tracking of the people using your project, and a link back to L.U.X. Verse when a tool did the work. The service is free and stays free.') ?></p>
                </div>
            </div>
        </div>
    </section>

</main>

<script>
(function () {
    "use strict";
    var TOOLS = <?= json_encode(array_values(array_map(function ($k, $t) {
        return ['tool' => (string)$k, 'name' => (string)($t['name'] ?? $k), 'engine' => (string)($t['engine'] ?? 'ai')];
    }, array_keys($tools), $tools)), JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP) ?>;
    var T = {
        ready   : <?= json_encode(t('Key ready. Copy it and keep it somewhere safe.')) ?>,
        copied  : <?= json_encode(t('Copied')) ?>,
        err     : <?= json_encode(t('Something went wrong. Try again.')) ?>,
        none    : <?= json_encode(t('No tool matches that word.')) ?>,
        waiting : <?= json_encode(t('Calling...')) ?>
    };
    function $(id) { return document.getElementById(id); }
    function esc(s) { return String(s == null ? '' : s).replace(/[&<>"']/g, function (c) { return ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]); }); }
    var key = '';
    function status(msg, bad) {
        var el = $('dv-status');
        if (!el) return;
        el.textContent = msg || '';
        el.className = 'space-status' + (bad ? ' err' : '');
    }
    function paint(filter) {
        var box = $('dv-tools');
        if (!box) return;
        var f = String(filter || '').toLowerCase();
        var rows = TOOLS.filter(function (t) { return !f || (t.tool + ' ' + t.name).toLowerCase().indexOf(f) >= 0; });
        if (!rows.length) { box.innerHTML = '<p class="space-empty">' + esc(T.none) + '</p>'; return; }
        box.innerHTML = rows.map(function (t) {
            return '<div class="space-row"><span class="space-row-main"><b>' + esc(t.name) + '</b><span>' + esc(t.tool) + ' · ' + esc(t.engine === 'local' ? <?= json_encode(t('instant')) ?> : 'AI') + '</span></span></div>';
        }).join('');
    }
    paint('');
    if ($('dv-q')) $('dv-q').addEventListener('input', function (e) { paint(e.target.value); });

    if ($('dv-newkey')) $('dv-newkey').addEventListener('click', function () {
        fetch('/api/apikey.php?op=key').then(function (r) { return r.json(); }).then(function (d) {
            if (!d || !d.success) { status((d && d.error) || T.err, true); return; }
            key = d.key;
            if ($('dv-key')) $('dv-key').textContent = key;
            if ($('dv-copykey')) $('dv-copykey').hidden = false;
            status(T.ready);
        }).catch(function () { status(T.err, true); });
    });
    if ($('dv-copykey')) $('dv-copykey').addEventListener('click', function () {
        if (navigator.clipboard) navigator.clipboard.writeText(key).catch(function () {});
        status(T.copied);
    });

    if ($('sb-run')) $('sb-run').addEventListener('click', function () {
        var tool = (($('sb-tool') || {}).value || '').trim();
        var text = (($('sb-input') || {}).value || '');
        if (!key) { status(<?= json_encode(t('Ask for a key first.')) ?>, true); return; }
        var out = $('sb-out');
        if (out) out.textContent = T.waiting;
        fetch('/api/run.php', {
            method: 'POST', headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ key: key, tool: tool, inputs: { text: text, topic: text, input: text } })
        }).then(function (r) { return r.json(); }).then(function (d) {
            if (out) out.textContent = JSON.stringify(d, null, 2).substring(0, 3000);
        }).catch(function () { if (out) out.textContent = T.err; });
    });
})();
</script>
<?php lux_footer(); ?>
