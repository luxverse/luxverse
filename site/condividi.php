<?php
// ============================================================
// RISULTATO CONDIVISO — L.U.X. VERSE 39.1.1 (WS4)
// ============================================================
// Un risultato salvato puo' diventare un indirizzo pubblico e stabile
// (/condividi.php?id=...). Nell'indirizzo non c'e' niente del codice
// personale di chi l'ha creato: solo l'identificativo del testo.
//
// La pagina non viene indicizzata (e' un contenuto creato da una persona,
// non una pagina del sito), ma resta raggiungibile per chi ha il link.
// ============================================================

require_once __DIR__ . '/config.php';
lux_session_start();

$lang = currentLang();
$isIt = ($lang !== 'en');
$id   = preg_replace('/[^a-f0-9]/', '', strtolower((string)($_GET['id'] ?? '')));
$row  = (strlen($id) === 12 && function_exists('lux_space_share_get')) ? lux_space_share_get($id) : null;

$tool  = $row ? (string)($row['tool'] ?? '') : '';
$spec  = ($tool !== '' && function_exists('lux_agent_tool')) ? lux_agent_tool($tool) : null;
$title = $row ? (string)($row['title'] ?? '') : '';

$pageTitle       = $row
    ? (($title !== '' ? $title : t('Shared result')) . ' — L.U.X. Verse')
    : t('Result not found') . ' — L.U.X. Verse';
$pageDescription = $row ? mb_substr(trim(preg_replace('/\s+/', ' ', (string)$row['content'])), 0, 180) : '';
$pageCSS         = 'assets/css/index.css';
// Contenuto creato da una persona, non una pagina del sito: si raggiunge per
// link ma non finisce nei motori di ricerca (vedi il meta robots in lux_header).
$GLOBALS['luxNoIndex'] = true;

lux_header();
?>
<main>
    <section class="main-section" style="padding-top:56px;">
        <div class="container">

            <?php if (!$row): ?>
            <div class="sec-head">
                <div class="sec-head-main">
                    <span class="sec-index"><?= icon('info', 13) ?> <?= t('Shared result') ?></span>
                    <h1 class="sec-title"><?= t('This result is no longer available') ?></h1>
                    <p class="sec-sub"><?= t('The link may be old or mistyped. You can ask the agent for a new result on the home page.') ?></p>
                </div>
                <span class="sec-rule" aria-hidden="true"></span>
                <a href="/index.php" class="btn-neon"><?= icon('sparkles', 15) ?> <?= t('Try the agent') ?></a>
            </div>
            <?php else: ?>

            <div class="sec-head">
                <div class="sec-head-main">
                    <span class="sec-index"><?= icon('link', 13) ?> <?= t('Shared result') ?> · <?= htmlspecialchars((string)($row['date'] ?? '')) ?></span>
                    <h1 class="sec-title"><?= htmlspecialchars($title !== '' ? $title : t('Shared result')) ?></h1>
                    <p class="sec-sub">
                        <?= $spec
                            ? t('Produced with {name} on L.U.X. Verse.', ['name' => (string)($spec['name'] ?? $tool)])
                            : t('Produced on L.U.X. Verse.') ?>
                    </p>
                </div>
                <span class="sec-rule" aria-hidden="true"></span>
                <?php if ($spec): ?>
                <a href="<?= htmlspecialchars((string)($spec['endpoint'] ?? ('/' . $tool . '.php'))) ?>" class="btn-small"><?= icon('external', 14) ?> <?= t('Open the tool') ?></a>
                <?php endif; ?>
            </div>

            <div class="agent-result">
                <div class="agent-card">
                    <div class="agent-head">
                        <span class="agent-head-ico"><?= icon('sparkles', 18) ?></span>
                        <div class="agent-head-main">
                            <span class="agent-kicker"><?= t('Result') ?></span>
                            <h2 class="agent-title"><?= htmlspecialchars($title !== '' ? $title : t('Result')) ?></h2>
                        </div>
                    </div>
                    <div class="agent-body<?= (string)($spec['output'] ?? '') === 'mono' ? ' mono' : '' ?>"><?= htmlspecialchars((string)$row['content']) ?></div>
                    <div class="agent-actions">
                        <button type="button" class="agent-act primary" id="sh-copy"><?= icon('copy', 14) ?> <span><?= t('Copy') ?></span></button>
                        <a class="agent-act" href="/spazio.php"><?= icon('user', 14) ?> <span><?= t('My space') ?></span></a>
                        <a class="agent-act" href="/index.php"><?= icon('sparkles', 14) ?> <span><?= t('Ask the agent') ?></span></a>
                    </div>
                </div>
            </div>

            <p class="agent-note">
                <?= t('This result was shared from a L.U.X. Verse personal space. The code of that space is not inside this link and cannot be recovered from it.') ?>
            </p>

            <script>
            (function () {
                var b = document.getElementById('sh-copy');
                if (!b) return;
                b.addEventListener('click', function () {
                    var txt = document.querySelector('.agent-body');
                    txt = txt ? txt.textContent : '';
                    if (navigator.clipboard) navigator.clipboard.writeText(txt).catch(function () {});
                    var s = b.querySelector('span');
                    if (s) { var o = s.textContent; s.textContent = <?= json_encode(t('Copied')) ?>; setTimeout(function () { s.textContent = o; }, 1500); }
                });
            })();
            </script>
            <?php endif; ?>

        </div>
    </section>
</main>
<?php lux_footer(); ?>
