<?php
// ============================================================
// SCHELETRO DELLE PAGINE — L.U.X. VERSE 39.1.1
// ============================================================
// Una sola struttura per tutte le pagine informative (chi siamo, FAQ,
// assistenza, contatti, stato, linea del tempo, app, privacy, termini).
// Prima ogni pagina si costruiva il proprio impianto: intestazioni
// diverse, colonne diverse, link in fondo diversi. Qui c'e' un posto
// solo.
//
// Com'e' fatta una pagina:
//
//   ┌──────────────────────────────────────────────┐
//   │ briciole · occhiello · titolo · riga sintesi │
//   │ dati (data, versione) · pulsanti             │
//   ├───────────────┬──────────────────────────────┤
//   │ indice        │ sezioni 01, 02, 03…          │
//   │ (segue lo     │ ognuna con testo e, se       │
//   │  scorrimento) │ serve, un riquadro "in breve"│
//   │               ├──────────────────────────────┤
//   │               │ continua da qui →            │
//   └───────────────┴──────────────────────────────┘
//
// Su schermo stretto l'indice diventa una fila di pastiglie che resta
// appiccicata sotto l'intestazione: niente JavaScript.
//
// Uso:
//   lux_page_open([
//       'crumbs'   => [['t' => 'Home', 'u' => '/index.php'], ['t' => 'Chi siamo']],
//       'kicker'   => 'Chi siamo',
//       'title'    => '...',
//       'lead'     => '...',
//       'meta'     => ['Aggiornata il 15 ottobre 2026'],
//       'actions'  => [['t' => 'Apri la Bacheca', 'u' => '/bacheca.php', 'p' => true]],
//       'sections' => $sezioni,          // solo per l'indice: id, n, t
//   ]);
//   lux_page_section(['n' => '01', 'id' => 'storia', 't' => '...', 'body' => '...', 'brief' => [...]]);
//   lux_page_close(['next' => [['t' => 'FAQ', 'd' => '...', 'u' => '/faq.php']]]);
// ============================================================

if (!function_exists('lux_page_open')) {

    /** Intestazione + inizio della griglia (indice a sinistra, testo a destra). */
    function lux_page_open(array $o): void
    {
        $crumbs   = (array)($o['crumbs'] ?? []);
        $kicker   = (string)($o['kicker'] ?? '');
        $title    = (string)($o['title'] ?? '');
        $lead     = (string)($o['lead'] ?? '');
        $meta     = (array)($o['meta'] ?? []);
        $actions  = (array)($o['actions'] ?? []);
        $sections = (array)($o['sections'] ?? []);
        ?>
    <main>
        <section class="main-section doc-top">
            <div class="container">

                <?php if ($crumbs): ?>
                <nav class="doc-crumbs" aria-label="<?= t('You are here') ?>">
                    <?php foreach ($crumbs as $i => $c): ?>
                        <?php if (!empty($c['u'])): ?>
                            <a href="<?= htmlspecialchars((string)$c['u']) ?>"><?= htmlspecialchars((string)$c['t']) ?></a><span aria-hidden="true">›</span>
                        <?php else: ?>
                            <span class="doc-crumb-now" aria-current="page"><?= htmlspecialchars((string)$c['t']) ?></span>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </nav>
                <?php endif; ?>

                <div class="doc-head">
                    <?php if ($kicker !== ''): ?>
                    <span class="sec-index"><?= icon('info', 13) ?> <?= htmlspecialchars($kicker) ?></span>
                    <?php endif; ?>
                    <h1 class="sec-title doc-title"><?= htmlspecialchars($title) ?></h1>
                    <?php if ($lead !== ''): ?>
                    <p class="sec-sub doc-lead"><?= htmlspecialchars($lead) ?></p>
                    <?php endif; ?>

                    <?php if ($meta || $actions): ?>
                    <div class="doc-bar">
                        <?php if ($meta): ?>
                        <ul class="doc-meta">
                            <?php foreach ($meta as $m): ?><li><?= htmlspecialchars((string)$m) ?></li><?php endforeach; ?>
                        </ul>
                        <?php endif; ?>
                        <?php if ($actions): ?>
                        <div class="doc-actions">
                            <?php foreach ($actions as $a): ?>
                            <a class="btn-small<?= !empty($a['p']) ? ' primary' : '' ?>" href="<?= htmlspecialchars((string)$a['u']) ?>"><?= htmlspecialchars((string)$a['t']) ?></a>
                            <?php endforeach; ?>
                        </div>
                        <?php endif; ?>
                    </div>
                    <?php endif; ?>
                </div>

                <div class="doc-grid<?= $sections ? '' : ' no-toc' ?>">
                    <?php if ($sections): ?>
                    <nav class="doc-toc" aria-label="<?= t('On this page') ?>">
                        <span class="doc-toc-title"><?= icon('book-open', 13) ?> <?= t('On this page') ?></span>
                        <ol>
                            <?php foreach ($sections as $s): ?>
                            <li><a href="#<?= htmlspecialchars((string)($s['id'] ?? '')) ?>"><b><?= htmlspecialchars((string)($s['n'] ?? '')) ?></b> <?= htmlspecialchars((string)($s['t'] ?? '')) ?></a></li>
                            <?php endforeach; ?>
                        </ol>
                    </nav>
                    <?php endif; ?>
                    <div class="doc-main">
        <?php
    }

    /** Una sezione: numero, titolo, testo e (facoltativo) riquadro "in breve". */
    function lux_page_section(array $s): void
    {
        $n     = (string)($s['n'] ?? '');
        $id    = (string)($s['id'] ?? '');
        $title = (string)($s['t'] ?? '');
        $body  = (string)($s['body'] ?? '');
        $brief = $s['brief'] ?? null;
        $note  = (string)($s['note'] ?? '');
        $box   = $s['box'] ?? null;
        ?>
        <section class="doc-sec"<?= $id !== '' ? ' id="' . htmlspecialchars($id) . '"' : '' ?>>
            <h2 class="doc-sec-t">
                <?php if ($n !== ''): ?><span class="doc-num"><?= htmlspecialchars($n) ?></span><?php endif; ?>
                <span><?= htmlspecialchars($title) ?></span>
            </h2>
            <?php if ($body !== ''): ?><div class="doc-body"><?= $body ?></div><?php endif; ?>
            <?php if (is_array($brief) && (!empty($brief['items']) || !empty($brief['t']))): ?>
            <aside class="doc-brief">
                <?php if (!empty($brief['t'])): ?><b><?= htmlspecialchars((string)$brief['t']) ?></b><?php endif; ?>
                <?php if (!empty($brief['items'])): ?>
                <ul>
                    <?php foreach ((array)$brief['items'] as $it): ?>
                        <li><?= is_array($it) ? '<b>' . htmlspecialchars((string)($it['t'] ?? '')) . '</b> ' . htmlspecialchars((string)($it['d'] ?? '')) : htmlspecialchars((string)$it) ?></li>
                    <?php endforeach; ?>
                </ul>
                <?php endif; ?>
            </aside>
            <?php endif; ?>
            <?php if (is_array($box)): ?>
            <div class="doc-box<?= !empty($box['tone']) ? ' tone-' . htmlspecialchars((string)$box['tone']) : '' ?>">
                <?php if (!empty($box['t'])): ?><b><?= htmlspecialchars((string)$box['t']) ?></b><?php endif; ?>
                <p><?= (string)($box['d'] ?? '') ?></p>
            </div>
            <?php endif; ?>
            <?php if ($note !== ''): ?><p class="doc-note"><?= $note ?></p><?php endif; ?>
        </section>
        <?php
    }

    /** Chiude la pagina con i collegamenti "continua da qui". */
    function lux_page_close(array $o = []): void
    {
        $next = (array)($o['next'] ?? []);
        ?>
                    </div>
                </div>

                <?php if ($next): ?>
                <nav class="doc-next" aria-label="<?= t('Continue from here') ?>">
                    <span class="doc-next-title"><?= icon('arrow-right', 13) ?> <?= t('Continue from here') ?></span>
                    <div class="doc-next-grid">
                        <?php foreach ($next as $n): ?>
                        <a class="doc-next-card" href="<?= htmlspecialchars((string)($n['u'] ?? '#')) ?>">
                            <b><?= htmlspecialchars((string)($n['t'] ?? '')) ?></b>
                            <span><?= htmlspecialchars((string)($n['d'] ?? '')) ?></span>
                            <i aria-hidden="true"><?= icon('arrow-right', 14) ?></i>
                        </a>
                        <?php endforeach; ?>
                    </div>
                </nav>
                <?php endif; ?>

            </div>
        </section>
    </main>
        <?php
    }
}
