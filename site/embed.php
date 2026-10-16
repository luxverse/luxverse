<?php
// ============================================================
// WIDGET DA INCORPORARE — L.U.X. VERSE 39.1.1 (WS4)
// ============================================================
// Uno strumento dentro un sito esterno, con una riga di codice:
//
//   <iframe src="https://luxverse.c22.eu/embed.php?tool=workmebase64"
//           style="width:100%;height:420px;border:1px solid #ddd;border-radius:12px"
//           title="L.U.X. Verse tool"></iframe>
//
// La pagina e' volutamente leggera: nessuna intestazione del sito, nessuna
// guida, nessun popup. Solo il modulo dello strumento, il risultato e una
// riga che riporta a L.U.X. Verse (il ritorno e' parte dell'accordo, non un
// obbligo legale: gli strumenti sono nostri e ci piace che si sappia).
//
// L'esecuzione usa lo stesso motore del sito: si chiama la pagina dello
// strumento in POST e si legge la risposta JSON.
//
// 39.2.0 — due modi, due pagine:
//   /embed.php?tool=chiave   -> il widget da incorporare (come sopra);
//   /embed.php               -> la pagina che SPIEGA come incorporarlo,
//                               con l'esempio dal vivo e il codice da
//                               copiare. Prima rispondeva 404 a chi
//                               arrivava dalla nota del percorso.
// ============================================================

require_once __DIR__ . '/config.php';

$lang = currentLang();
$isIt = ($lang !== 'en');
$key  = strtolower(preg_replace('/[^a-z0-9_\-]/', '', (string)($_GET['tool'] ?? '')));
$spec = ($key !== '' && function_exists('lux_agent_tool')) ? lux_agent_tool($key) : null;

// Nessuno strumento chiesto: e' la pagina che spiega il widget.
if (!$spec && $key === '') {
    require_once __DIR__ . '/includes/page.php';

    // L'esempio dal vivo usa uno strumento ISTANTANEO (calcola nel browser:
    // non consuma chiamate IA) e soprattutto DISPONIBILE ADESSO. Lo stato si
    // legge qui, sul server, quindi la pagina non racconta una verita' vecchia:
    // se domani gli strumenti tornano attivi, l'esempio ricompare da solo,
    // senza ripubblicare niente.
    $allTools = function_exists('lux_agent_tools') ? lux_agent_tools() : [];
    $statoDi  = function_exists('lux_project_state') ? 'lux_project_state' : null;
    $demoKey  = '';
    $primoLocal = '';
    foreach ($allTools as $k => $t) {
        if ((string)($t['engine'] ?? 'ai') !== 'local' || empty($t['inputs'])) continue;
        if ($primoLocal === '') $primoLocal = (string)$k;
        if ($statoDi !== null && (string)$statoDi((string)$k) !== 'attivo') continue;
        $demoKey = (string)$k;
        break;
    }
    $demoName  = $demoKey !== '' ? (string)($allTools[$demoKey]['name'] ?? $demoKey) : '';
    $chiaveEsempio = $demoKey !== '' ? $demoKey : ($primoLocal !== '' ? $primoLocal : 'chiave-strumento');
    $snippet  = '<iframe src="' . LUX_SITE_URL . '/embed.php?tool=' . $chiaveEsempio
              . '" style="width:100%;height:460px;border:1px solid #2a2d31;border-radius:12px" title="L.U.X. Verse"></iframe>';
    $nEmbeddable = count($allTools);

    // Se adesso nessuno strumento e' disponibile si dice come sta la cosa:
    // meglio una riga onesta di un modulo che non esegue.
    $statoEsempio = '';
    if ($demoKey === '' && $primoLocal !== '' && $statoDi !== null) {
        $statoEsempio = function_exists('lux_project_state_label')
            ? (string)lux_project_state_label((string)$statoDi($primoLocal))
            : (string)$statoDi($primoLocal);
    }

    $pageTitle       = $isIt ? 'Widget da incorporare — L.U.X. Verse' : 'Embeddable widget — L.U.X. Verse';
    $pageDescription = $isIt
        ? 'Porta uno strumento di L.U.X. Verse dentro il tuo sito con una riga di codice: come si fa, cosa chiediamo in cambio e l\'esempio dal vivo.'
        : 'Bring a L.U.X. Verse tool inside your own site with one line of code: how it works, what we ask in return and the live example.';
    $pageKeywords = 'widget, embed, incorporare, iframe, strumento, API, L.U.X. Verse';
    $pageCSS      = '';
    // Solo due ritocchi, e solo per questa pagina: il riquadro del codice da
    // copiare e l'aria attorno all'esempio dal vivo. Cosi' non c'e' bisogno
    // di toccare il foglio di stile che usano tutte le altre pagine.
    $extraCSS = '.doc-body pre.emb-code{background:var(--ink-3);border:1px solid var(--line-2);border-radius:12px;padding:14px 16px;overflow-x:auto;margin:0 0 16px}'
              . '.doc-body pre.emb-code code{background:none;padding:0;color:var(--bone);font-size:12.5px;line-height:1.6}'
              . '.doc-body .emb-demo{margin:4px 0 10px}'
              . '.doc-body .emb-demo iframe{display:block;background:var(--ink-2)}';

    $sezioni = [
        ['id' => 'cosa',    'n' => '01', 't' => $isIt ? 'Cosa diventa il widget' : 'What the widget becomes'],
        ['id' => 'codice',  'n' => '02', 't' => $isIt ? 'Come si incorpora' : 'How to embed it'],
        ['id' => 'esempio', 'n' => '03', 't' => $isIt ? 'Esempio dal vivo' : 'Live example'],
        ['id' => 'regole',  'n' => '04', 't' => $isIt ? 'Regole, limiti e rispetto' : 'Rules, limits and respect'],
    ];
    // Nota: l'indice elenca solo le sezioni che esistono davvero. Il blocco
    // «continua da qui» in fondo e' una navigazione, non un capitolo: se lo
    // mettessimo qui, il clic porterebbe a un'ancora che non c'e'.


    lux_header();
    lux_page_open([
        'crumbs'  => [['t' => 'Home', 'u' => '/index.php'], ['t' => $isIt ? 'Widget da incorporare' : 'Embeddable widget']],
        'kicker'  => $isIt ? 'Per chi ha un sito' : 'For whoever has a site',
        'title'   => $isIt ? 'Uno strumento del catalogo, dentro il tuo sito' : 'A tool from the catalogue, inside your site',
        'lead'    => $isIt
            ? 'Una riga di codice e lo strumento funziona dentro la tua pagina, con il risultato lì dov\'è. In cambio chiediamo una cosa sola: un collegamento che riporta a L.U.X. Verse.'
            : 'One line of code and the tool works inside your page, with the result right there. In return we ask one thing only: a link that goes back to L.U.X. Verse.',
        'meta'    => [
            $isIt ? 'Gratuito, senza chiave' : 'Free, no key',
            $nEmbeddable . ' ' . ($isIt ? 'strumenti incorporabili' : 'embeddable tools'),
            $isIt ? 'Nessun tracciamento dentro l\'iframe' : 'No tracking inside the iframe',
        ],
        'actions' => [
            ['t' => $isIt ? 'Scegli uno strumento' : 'Pick a tool', 'u' => '/index.php', 'p' => true],
            ['t' => $isIt ? 'API per sviluppatori' : 'Developer API', 'u' => '/sviluppatori.php'],
        ],
        'sections' => $sezioni,
    ]);

    lux_page_section([
        'n' => '01', 'id' => 'cosa',
        't' => $isIt ? 'Cosa diventa il widget' : 'What the widget becomes',
        'body' => $isIt
            ? '<p>Il widget è <strong>lo strumento, e nient\'altro</strong>: nessuna intestazione del sito, nessun menu, nessuna guida, nessun avviso. Dentro la tua pagina compare solo il modulo con i suoi campi, il pulsante per eseguire e il risultato.</p>
               <p>Esegue lo stesso motore del sito: gli strumenti marcati <strong>istantanei</strong> calcolano nel browser, quelli con <strong>IA</strong> passano dal fornitore del modello come sul sito. Non chiede chiavi, non chiede registrazione e non mette cookie di tracciamento dentro l\'iframe.</p>'
            : '<p>The widget is <strong>the tool, and nothing else</strong>: no site header, no menu, no guide, no notices. Inside your page only the form with its fields, the run button and the result appear.</p>
               <p>It runs the same engine as the site: <strong>instant</strong> tools compute in the browser, <strong>AI</strong> ones go through the model provider as on the site. It asks for no keys, no registration and sets no tracking cookies inside the iframe.</p>',
        'brief' => [
            't' => $isIt ? 'In breve' : 'In short',
            'items' => $isIt
                ? ['Una riga di codice, nessuna libreria da installare', 'Funziona anche senza JavaScript nel tuo sito: è un iframe', 'Il risultato resta nella tua pagina', 'La riga di ritorno a L.U.X. Verse resta in fondo al widget']
                : ['One line of code, no library to install', 'Works even without JavaScript on your site: it is an iframe', 'The result stays in your page', 'The return line to L.U.X. Verse stays at the bottom of the widget'],
        ],
    ]);

    lux_page_section([
        'n' => '02', 'id' => 'codice',
        't' => $isIt ? 'Come si incorpora' : 'How to embed it',
        'body' => ($isIt
            ? '<p>Prendi la chiave dello strumento: è quella che vedi nell\'indirizzo della sua pagina (per esempio <code>workmebase64</code>). Poi incolla questo codice dove vuoi che compaia, e cambia altezza e larghezza come ti serve.</p>'
            : '<p>Take the tool key: it is the one you see in the address of its page (for example <code>workmebase64</code>). Then paste this code where you want it to appear, and change height and width as you need.</p>')
            . '<pre class="emb-code"><code>' . htmlspecialchars($snippet) . '</code></pre>'
            . ($isIt
                ? '<p>Va bene in una pagina scritta a mano, in WordPress (blocco «HTML personalizzato»), in Squarespace o in qualsiasi altro sistema che permetta di incollare codice. L\'altezza consigliata è <strong>460px</strong> per gli strumenti con un campo, <strong>560px</strong> per quelli con più campi o con un risultato lungo.</p>'
                : '<p>It works in a hand-written page, in WordPress (the «Custom HTML» block), in Squarespace or in any other system that lets you paste code. The recommended height is <strong>460px</strong> for tools with one field, <strong>560px</strong> for those with more fields or a long result.</p>'),
        'box' => ['tone' => 'ok', 't' => $isIt ? 'Una cosa da sapere' : 'One thing to know', 'd' => $isIt
            ? 'Il tema del widget è scuro, come il sito: su una pagina chiara resta leggibile, ma se preferisci un riquadro bianco basta mettere uno sfondo bianco attorno all\'iframe.'
            : 'The widget theme is dark, like the site: on a light page it stays readable, but if you prefer a white box just put a white background around the iframe.'],
    ]);

    if ($demoKey !== '') {
        // C'e' uno strumento disponibile: si mostra il widget vero e lo si dice.
        $demoHtml = '<p>' . ($isIt
            ? 'Questo è il widget vero, caricato qui sotto con lo stesso codice che copierai. Provane uno dei campi: funziona.'
            : 'This is the real widget, loaded below with the same code you will copy. Try one of the fields: it works.') . '</p>'
            . '<div class="emb-demo"><iframe src="/embed.php?tool=' . htmlspecialchars($demoKey)
            . '" style="width:100%;height:460px;border:1px solid var(--line-2);border-radius:12px" title="L.U.X. Verse widget — ' . htmlspecialchars($demoName) . '"></iframe></div>'
            . '<p class="doc-note">' . ($isIt ? 'Esempio: ' : 'Example: ') . '<b>' . htmlspecialchars($demoName)
            . '</b> · <code>' . htmlspecialchars($demoKey) . '</code></p>';
        $notaEsempio = null;
    } else {
        // Nessuno strumento disponibile in questo momento (per esempio sono
        // In Verifica). Non si finge niente: si spiega, e si lascia la porta
        // aperta per vederli quando tornano.
        $demoHtml = '<p>' . ($isIt
            ? 'Il widget è pronto: qui si vedrebbe lo strumento con i suoi campi, esattamente come apparirà nella tua pagina. Adesso gli strumenti del motore sono <b>' . htmlspecialchars($statoEsempio !== '' ? $statoEsempio : (($isIt ? 'non disponibili' : 'not available'))) . '</b>, quindi il modulo non eseguirebbe: meglio dirlo che mostrare un pulsante che non risponde.'
            : 'The widget is ready: here you would see the tool with its fields, exactly as it will appear in your page. Right now the engine tools are <b>' . htmlspecialchars($statoEsempio !== '' ? $statoEsempio : 'not available') . '</b>, so the form would not run: better to say it than to show a button that does not answer.') . '</p>'
            . '<p>' . ($isIt
                ? 'Il codice qui sopra però non cambia: quello resta il codice definitivo, e quando gli strumenti tornano attivi l\'esempio ricompare da solo in questa pagina.'
                : 'The code above does not change though: that is the final code, and when the tools come back the example reappears by itself on this page.') . '</p>';
        $notaEsempio = ['tone' => 'warn',
            't' => $isIt ? 'Perché lo dico' : 'Why I am saying this',
            'd' => $isIt
                ? 'Lo stato si legge dal sito, non da una nota scritta a mano: questa pagina non può restare indietro rispetto alla realtà.'
                : 'The state is read from the site, not from a hand-written note: this page cannot fall behind reality.'];
    }

    lux_page_section([
        'n' => '03', 'id' => 'esempio',
        't' => $isIt ? 'Esempio dal vivo' : 'Live example',
        'body' => $demoHtml,
        'box'  => $notaEsempio,
    ]);

    lux_page_section([
        'n' => '04', 'id' => 'regole',
        't' => $isIt ? 'Regole, limiti e rispetto' : 'Rules, limits and respect',
        'body' => $isIt
            ? '<ul>
                 <li><strong>Gratuito</strong>: nessuna chiave, nessun costo, nessuna registrazione, anche per un sito commerciale.</li>
                 <li><strong>La riga di ritorno resta.</strong> Il widget chiude con il collegamento a L.U.X. Verse: non va tolto o coperto. È l\'unica cosa che chiediamo in cambio.</li>
                 <li><strong>Limiti di velocità</strong>: valgono le stesse regole del sito (un numero massimo di richieste al minuto per indirizzo). Un uso intenso va concordato: scrivici.</li>
                 <li><strong>Niente rivendita</strong>: il widget non si può rivendere come servizio tuo, né usare per generare contenuti illegali o che danneggino qualcuno.</li>
                 <li><strong>Cambiamenti</strong>: l\'indirizzo e il comportamento del widget possono cambiare; se cambiamo qualcosa di importante lo scriviamo in bacheca.</li>
               </ul>'
            : '<ul>
                 <li><strong>Free</strong>: no key, no cost, no registration, even for a commercial site.</li>
                 <li><strong>The return line stays.</strong> The widget closes with the link to L.U.X. Verse: it must not be removed or covered. It is the only thing we ask in return.</li>
                 <li><strong>Rate limits</strong>: the same rules as the site apply (a maximum number of requests per minute per address). Heavy use should be agreed: write to us.</li>
                 <li><strong>No resale</strong>: the widget cannot be resold as your own service, nor used to generate illegal content or content that harms someone.</li>
                 <li><strong>Changes</strong>: the widget address and behaviour may change; if we change something important we write it on the board.</li>
               </ul>',
    ]);

    lux_page_close([
        'next' => [
            ['t' => $isIt ? 'Scegli lo strumento' : 'Pick the tool', 'd' => $isIt ? 'Centomila strumenti, e la chiave da usare è nell\'indirizzo.' : 'One hundred thousand tools, and the key to use is in the address.', 'u' => '/index.php'],
            ['t' => $isIt ? 'API e sviluppatori' : 'API and developers', 'd' => $isIt ? 'Chiavi anonime, esempi e documentazione.' : 'Anonymous keys, examples and documentation.', 'u' => '/sviluppatori.php'],
            ['t' => $isIt ? 'Percorso del rilascio' : 'Release roadmap', 'd' => $isIt ? 'Il widget è una delle voci in corso: qui lo stato vero.' : 'The widget is one of the items in progress: here is its real state.', 'u' => '/roadmap.php'],
        ],
    ]);
    lux_footer();
    exit;
}

// Nome sbagliato (o strumento inesistente): qui un 404 e' la risposta giusta.
if (!$spec) {
    http_response_code(404);
    header('Content-Type: text/plain; charset=utf-8');
    echo $isIt ? 'Strumento non trovato.' : 'Tool not found.';
    exit;
}

// Da qui in poi si serve il widget: deve poter stare dentro un altro sito.
header('X-Frame-Options: ALLOWALL');
header('Content-Security-Policy: frame-ancestors *');
header('Referrer-Policy: no-referrer-when-downgrade');

$name   = (string)($spec['name'] ?? $key);
$desc   = (string)($isIt ? ($spec['desc_it'] ?? '') : ($spec['desc_en'] ?? ($spec['desc_it'] ?? '')));
$fields = function_exists('lux_agent_fields') ? lux_agent_fields($spec, $isIt ? 'it' : 'en') : [];
$endpoint = (string)($spec['endpoint'] ?? ('/' . $key . '.php'));

// Lo strumento risponde solo se e' attivo: se e' In Verifica (o in pausa,
// bloccato, in manutenzione) il widget lo dice con le stesse parole della
// pagina dello strumento, invece di mostrare un modulo che poi non esegue.
$stato    = function_exists('lux_project_state') ? (string)lux_project_state($key) : 'attivo';
$disponibile = ($stato === 'attivo');
$statoTxt = function_exists('lux_project_state_label') ? (string)lux_project_state_label($stato) : $stato;
?><!DOCTYPE html>
<html lang="<?= $isIt ? 'it' : 'en' ?>">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="robots" content="noindex, follow">
<title><?= htmlspecialchars($name) ?></title>
<style>
    :root{--ink:#0C0E11;--ink2:#0F1113;--bone:#EDE9E0;--light:#A5A197;--muted:#8C8980;--gold:#D8A93C;--line:rgba(237,233,224,.09);--line2:rgba(237,233,224,.16)}
    *{box-sizing:border-box}
    body{margin:0;background:var(--ink);color:var(--bone);font:14px/1.55 system-ui,-apple-system,Segoe UI,Roboto,sans-serif;padding:14px}
    .w-head{display:flex;align-items:center;gap:9px;margin-bottom:11px}
    .w-head b{font-size:14px}
    .w-head span{display:block;font-size:11.5px;color:var(--muted)}
    .w-dot{width:9px;height:9px;border-radius:50%;background:var(--gold);flex-shrink:0}
    label{display:block;font-size:12px;color:var(--light);margin:9px 0 5px}
    input,textarea,select{width:100%;background:var(--ink2);border:1px solid var(--line2);border-radius:7px;color:var(--bone);padding:9px 11px;font:inherit;outline:none}
    input:focus,textarea:focus,select:focus{border-color:var(--gold)}
    textarea{min-height:76px;resize:vertical}
    button{margin-top:11px;width:100%;background:linear-gradient(135deg,#E0B24E,#B98D2A);border:0;border-radius:7px;color:#15181B;font-weight:700;font-size:13px;padding:11px;cursor:pointer}
    button[disabled]{opacity:.6;pointer-events:none}
    .w-out{margin-top:12px;background:var(--ink2);border:1px solid var(--line);border-radius:8px;padding:11px 12px;white-space:pre-wrap;word-break:break-word;font-size:13px;display:none;max-height:280px;overflow:auto}
    .w-out.on{display:block}
    .w-out.mono{font-family:ui-monospace,Menlo,Consolas,monospace;font-size:12.5px}
    .w-off{background:var(--ink2);border:1px solid var(--line2);border-radius:8px;padding:12px;font-size:13px;line-height:1.6}
    .w-off b{display:block;color:var(--gold);font-size:12px;letter-spacing:.06em;text-transform:uppercase;margin-bottom:5px}
    .w-off span{color:var(--light)}
    .w-foot{margin-top:12px;padding-top:9px;border-top:1px solid var(--line);font-size:11.5px;color:var(--muted);display:flex;justify-content:space-between;gap:10px;flex-wrap:wrap}
    .w-foot a{color:var(--gold);text-decoration:none}
</style>
</head>
<body>
    <div class="w-head">
        <span class="w-dot" aria-hidden="true"></span>
        <div><b><?= htmlspecialchars($name) ?></b><span><?= htmlspecialchars(mb_substr($desc, 0, 110)) ?></span></div>
    </div>

    <?php if (!$disponibile): ?>
    <div class="w-off">
        <b><?= htmlspecialchars($statoTxt) ?></b>
        <span><?= $isIt
            ? 'Questo strumento è temporaneamente non disponibile: lo riattiviamo appena possibile. Gli altri strumenti del sito restano aperti.'
            : 'This tool is temporarily not available: we will turn it back on as soon as possible. The other tools on the site stay open.' ?></span>
    </div>
    <?php else: ?>
    <form id="w-form">
        <?php foreach ($fields as $f):
            $fn = htmlspecialchars((string)$f['name'], ENT_QUOTES);
            $fl = htmlspecialchars((string)($f['label'] !== '' ? $f['label'] : $f['name']), ENT_QUOTES);
        ?>
        <label for="w_<?= $fn ?>"><?= $fl ?><?= !empty($f['required']) ? ' *' : '' ?></label>
        <?php if (($f['type'] ?? 'text') === 'textarea'): ?>
            <textarea id="w_<?= $fn ?>" name="<?= $fn ?>" maxlength="<?= (int)($f['max'] ?: 6000) ?>"></textarea>
        <?php elseif (($f['type'] ?? '') === 'select' && !empty($f['options'])): ?>
            <select id="w_<?= $fn ?>" name="<?= $fn ?>">
                <?php foreach ((array)$f['options'] as $o): ?>
                <option value="<?= htmlspecialchars((string)$o, ENT_QUOTES) ?>"<?= (string)$f['default'] === (string)$o ? ' selected' : '' ?>><?= htmlspecialchars((string)$o) ?></option>
                <?php endforeach; ?>
            </select>
        <?php else: ?>
            <input id="w_<?= $fn ?>" name="<?= $fn ?>" type="text" maxlength="<?= (int)($f['max'] ?: 300) ?>" value="<?= htmlspecialchars((string)$f['default'], ENT_QUOTES) ?>">
        <?php endif; ?>
        <?php endforeach; ?>
        <button type="submit" id="w-go"><?= $isIt ? 'Esegui' : 'Run' ?></button>
    </form>

    <div class="w-out<?= (string)($spec['output'] ?? '') === 'mono' ? ' mono' : '' ?>" id="w-out" aria-live="polite"></div>
    <?php endif; ?>

    <div class="w-foot">
        <span><?= $isIt ? 'Widget di L.U.X. Verse' : 'L.U.X. Verse widget' ?></span>
        <a href="<?= htmlspecialchars(LUX_SITE_URL . $endpoint) ?>" target="_blank" rel="noopener"><?= $isIt ? 'Apri lo strumento completo' : 'Open the full tool' ?> &rarr;</a>
    </div>

<script>
(function () {
    "use strict";
    var form = document.getElementById('w-form');
    var out  = document.getElementById('w-out');
    var go   = document.getElementById('w-go');
    if (!form) return;   // strumento non disponibile: qui non c'e' niente da eseguire
    var T = {
        run  : <?= json_encode($isIt ? 'Esegui' : 'Run') ?>,
        wait : <?= json_encode($isIt ? 'Sto eseguendo...' : 'Running...') ?>,
        err  : <?= json_encode($isIt ? 'Qualcosa non ha funzionato. Riprova.' : 'Something went wrong. Try again.') ?>,
        off  : <?= json_encode($isIt ? 'Questo strumento non è disponibile in questo momento.' : 'This tool is not available right now.') ?>
    };
    form.addEventListener('submit', function (e) {
        e.preventDefault();
        var body = new URLSearchParams(new FormData(form));
        body.set('lux_tool', <?= json_encode($key) ?>);
        go.disabled = true;
        var old = go.textContent;
        go.textContent = T.wait;
        out.classList.add('on');
        out.textContent = T.wait;
        fetch(<?= json_encode($endpoint) ?>, { method: 'POST', body: body })
            .then(function (r) { return r.text(); })
            .then(function (txt) {
                // Non sempre arriva JSON: se lo strumento e' bloccato, il sito
                // risponde con la sua pagina. In quel caso si dice che lo
                // strumento non e' disponibile, non "riprova".
                var d = null;
                try { d = JSON.parse(txt); } catch (err) { d = null; }
                if (d && d.success)      out.textContent = String(d.content || '');
                else if (d && d.error)   out.textContent = d.error;
                else                     out.textContent = T.off;
            })
            .catch(function () { out.textContent = T.err; })
            .then(function () { go.disabled = false; go.textContent = old || T.run; });
    });
})();
</script>
</body>
</html>
