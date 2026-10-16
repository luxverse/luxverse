<?php
// ============================================================
// PROPONI UNO STRUMENTO — L.U.X. VERSE 39.1.1 (WS5)
// ============================================================
// Il catalogo cresce anche dal basso: qui si propone uno strumento che
// manca e si vota quello che serve di piu'. Si partecipa senza registrarsi:
// un soprannome (facoltativo) e nient'altro. Il controllo automatico
// scarta pubblicita' e doppioni (vedi lux_suggest_vet in includes/space.php).
// ============================================================

require_once __DIR__ . '/config.php';
lux_session_start();

$lang = currentLang();
$isIt = ($lang !== 'en');

$pageTitle       = $isIt ? 'Proponi uno strumento — L.U.X. Verse' : 'Suggest a tool — L.U.X. Verse';
$pageDescription = $isIt
    ? 'Proponi lo strumento che manca e vota quelli degli altri. Nessuna registrazione: le idee migliori entrano nel percorso del rilascio.'
    : 'Suggest the tool that is missing and vote on the ideas of others. No registration: the best ideas enter the release roadmap.';
$pageKeywords = 'proponi strumento, suggerisci, community, vota, L.U.X. Verse';
$pageCSS      = 'assets/css/index.css';

$count = count(array_filter(lux_data_read('suggestions'), function ($r) {
    return (string)($r['status'] ?? 'nuovo') !== 'spam';
}));

lux_header();
?>
<main>

    <section class="main-section" style="padding-top:56px;">
        <div class="container">
            <div class="sec-head">
                <div class="sec-head-main">
                    <span class="sec-index"><?= icon('bulb', 13) ?> <?= t('Suggest a tool') ?></span>
                    <h1 class="sec-title"><?= t('What is missing? Tell us.') ?></h1>
                    <p class="sec-sub"><?= t('Every tool of L.U.X. Verse was born from a need somebody had. Write yours: the most voted proposals enter the release roadmap.') ?></p>
                </div>
                <span class="sec-rule" aria-hidden="true"></span>
                <a href="/roadmap.php" class="btn-small"><?= icon('time', 14) ?> <?= t('Release roadmap') ?></a>
            </div>

            <div class="space-grid">
                <div class="space-card" style="grid-column:1/-1">
                    <h3><?= icon('pen', 14) ?> <?= t('Propose a tool') ?></h3>
                    <div class="space-code">
                        <input type="text" id="sg-name" maxlength="80" placeholder="<?= t('Name of the tool you would like') ?>" aria-label="<?= t('Name of the tool you would like') ?>">
                        <input type="text" id="sg-nick" maxlength="24" placeholder="<?= t('Nickname (optional)') ?>" aria-label="<?= t('Nickname (optional)') ?>">
                    </div>
                    <div class="space-code" style="margin-top:10px">
                        <input type="text" id="sg-why" maxlength="600" placeholder="<?= t('What should it do? Two lines are enough.') ?>" aria-label="<?= t('What should it do?') ?>">
                        <button type="button" class="btn-neon" id="sg-send"><?= icon('send', 15) ?> <span><?= t('Send the proposal') ?></span></button>
                    </div>
                    <p class="space-note">
                        <?= t('No registration and no personal data: your vote is counted from a fingerprint of your connection, and the nickname is only a name to show. Proposals that look like advertising, or that duplicate one already present, are discarded automatically.') ?>
                    </p>
                    <p class="space-status" id="sg-status" aria-live="polite"></p>
                </div>

                <div class="space-card" style="grid-column:1/-1">
                    <h3><?= icon('board', 14) ?> <?= t('The board of proposals') ?> · <span id="sg-total"><?= (int)$count ?></span></h3>
                    <div class="space-list" id="sg-list"><p class="space-empty"><?= t('Loading the proposals...') ?></p></div>
                </div>

                <div class="space-card" style="grid-column:1/-1">
                    <h3><?= icon('info', 14) ?> <?= t('How a proposal becomes a tool') ?></h3>
                    <p class="space-empty"><?= t('1. Someone proposes it and the community votes. 2. If it collects enough votes, it enters the roadmap under "later". 3. When it is built, it appears in the catalogue with its own page, free for everyone like the others. Nothing promised here is quietly dropped: if a proposal cannot be built, it stays on the board with the reason.') ?></p>
                </div>
            </div>
        </div>
    </section>

</main>

<script>
(function () {
    "use strict";
    var T = {
        vote     : <?= json_encode(t('Vote')) ?>,
        voted    : <?= json_encode(t('You voted')) ?>,
        votes    : <?= json_encode(t('votes')) ?>,
        mine     : <?= json_encode(t('Your proposal')) ?>,
        loading  : <?= json_encode(t('Loading the proposals...')) ?>,
        empty    : <?= json_encode(t('No proposals yet: yours would be the first.')) ?>,
        err      : <?= json_encode(t('Something went wrong. Try again.')) ?>,
        sent     : <?= json_encode(t('Thank you! Your proposal is on the board.')) ?>,
        sending  : <?= json_encode(t('Sending...')) ?>,
        anon     : <?= json_encode(t('Anonymous')) ?>,
        statuses : <?= json_encode([
            'nuovo'         => t('New'),
            'approvato'     => t('Accepted'),
            'in_lavorazione'=> t('Being built'),
            'rifiutato'     => t('Not possible'),
        ]) ?>
    };
    function $(id) { return document.getElementById(id); }
    function esc(s) { return String(s == null ? '' : s).replace(/[&<>"']/g, function (c) { return ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]); }); }
    function status(msg, bad) {
        var el = $('sg-status');
        if (!el) return;
        el.textContent = msg || '';
        el.className = 'space-status' + (bad ? ' err' : '');
    }
    function post(data) {
        return fetch('/api/suggest.php', {
            method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify(data)
        }).then(function (r) { return r.json(); });
    }

    function render(d) {
        var list = $('sg-list');
        if (!list) return;
        var items = (d && d.items) || [];
        var mine = (d && d.mine) || [];
        if ($('sg-total')) $('sg-total').textContent = (d && d.total) || 0;
        if (!items.length) { list.innerHTML = '<p class="space-empty">' + esc(T.empty) + '</p>'; return; }
        list.innerHTML = items.map(function (it) {
            var st = T.statuses[it.status] || it.status;
            var isMine = mine.indexOf(it.id) >= 0;
            return '<div class="space-row"><span class="space-row-main">' +
                     '<b>' + esc(it.name) + ' <span class="agent-badge">' + esc(st) + '</span>' +
                       (isMine ? ' <span class="agent-badge">' + esc(T.mine) + '</span>' : '') + '</b>' +
                     '<span style="white-space:normal;line-height:1.6">' + esc(it.why) + '</span>' +
                     '<span>' + esc(it.nick || T.anon) + ' · ' + esc((it.date || '').substring(0, 10)) + '</span>' +
                   '</span>' +
                   '<button type="button" class="btn-small" data-vote="' + esc(it.id) + '"' + (it.voted ? ' disabled' : '') + '>' +
                     (it.voted ? esc(T.voted) : esc(T.vote)) + ' · ' + (it.votes || 0) +
                   '</button></div>';
        }).join('');
        list.querySelectorAll('[data-vote]').forEach(function (b) {
            b.addEventListener('click', function () {
                b.disabled = true;
                post({ op: 'vote', id: b.dataset.vote }).then(load).catch(function () { b.disabled = false; });
            });
        });
    }

    function load() {
        return fetch('/api/suggest.php?op=list', { cache: 'no-store' })
            .then(function (r) { return r.json(); })
            .then(render)
            .catch(function () { var l = $('sg-list'); if (l) l.innerHTML = '<p class="space-empty">' + esc(T.err) + '</p>'; });
    }

    if ($('sg-send')) $('sg-send').addEventListener('click', function () {
        var name = ($('sg-name') || {}).value || '';
        var why  = ($('sg-why') || {}).value || '';
        var nick = ($('sg-nick') || {}).value || '';
        if (name.trim().length < 4 || why.trim().length < 20) {
            status(<?= json_encode(t('Write the name (at least 4 characters) and what it should do (at least 20).')) ?>, true);
            return;
        }
        status(T.sending);
        post({ op: 'add', name: name, why: why, nick: nick }).then(function (d) {
            if (!d || !d.success) { status((d && d.error) || T.err, true); return; }
            status(T.sent);
            if ($('sg-name')) $('sg-name').value = '';
            if ($('sg-why')) $('sg-why').value = '';
            load();
        }).catch(function () { status(T.err, true); });
    });

    load();
})();
</script>
<?php lux_footer(); ?>
