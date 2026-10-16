<?php
// ============================================================
// ASSISTENZA — L.U.X. VERSE 39.1.1
// ============================================================
// Prima di scrivere: due strade piu' rapide (la guida e le pagine di
// stato/percorso/bacheca). Poi il modulo vero, che manda il messaggio a
// /api/submit_message.php e restituisce un codice per seguire la
// richiesta. Nessun dato in piu' del necessario: nome facoltativo.
// ============================================================

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/page.php';
lux_session_start();

$lang = currentLang();
$isIt = ($lang === 'it');

$pageTitle       = $isIt ? 'Assistenza — L.U.X. Verse' : 'Support — L.U.X. Verse';
$pageDescription = $isIt
    ? 'Assistenza L.U.X. Verse: la guida automatica, le pagine di stato e poi il modulo per scriverci. Ogni richiesta riceve un codice per seguirla.'
    : 'L.U.X. Verse support: the automatic guide, the status pages and then the form to write to us. Every request gets a code to follow it.';
$pageKeywords    = 'assistenza, supporto, aiuto, problema, ticket, L.U.X. Verse, support';
$pageCSS         = '';

$sezioni = [
    ['id' => 'prima',   'n' => '01', 't' => $isIt ? 'Prima di scriverci: tre controlli veloci' : 'Before writing: three quick checks'],
    ['id' => 'guida',   'n' => '02', 't' => $isIt ? 'Parla subito con la guida' : 'Talk to the guide right away'],
    ['id' => 'modulo',  'n' => '03', 't' => $isIt ? 'Scrivici: il modulo' : 'Write to us: the form'],
    ['id' => 'dopo',    'n' => '04', 't' => $isIt ? 'Cosa succede dopo' : 'What happens next'],
    ['id' => 'urgenti', 'n' => '05', 't' => $isIt ? 'Problemi seri' : 'Serious problems'],
];

lux_header();
lux_page_open([
    'crumbs'   => [['t' => 'Home', 'u' => '/index.php'], ['t' => t('Assistenza')]],
    'kicker'   => $isIt ? 'Siamo dalla stessa parte' : 'We are on the same side',
    'title'    => $isIt ? 'Assistenza' : 'Support',
    'lead'     => $isIt
        ? 'Qualcosa non funziona, un risultato non ti convince, una richiesta tecnica? Qui si scrive. Prima però vale la pena guardare tre pagine: spesso la risposta è già lì, e arrivi alla soluzione in meno tempo.'
        : 'Something is broken, a result does not convince you, a technical request? This is where you write. Before that, three pages are worth a look: the answer is often already there, and you get to the solution faster.',
    'meta'     => [
        $isIt ? 'Risponde il fondatore' : 'The founder answers',
        $isIt ? 'Nessun call center, nessun robot che ti fa ripetere tutto' : 'No call centre, no robot making you repeat everything',
        'administration.luxverse@gmail.com',
    ],
    'actions'  => [
        ['t' => $isIt ? 'Vai al modulo' : 'Go to the form', 'u' => '#modulo', 'p' => true],
        ['t' => t('FAQ'), 'u' => '/faq.php'],
    ],
    'sections' => $sezioni,
]);

lux_page_section([
    'n' => '01', 'id' => 'prima',
    't' => $isIt ? 'Prima di scriverci: tre controlli veloci' : 'Before writing: three quick checks',
    'body' => $isIt
        ? '<div class="space-list">
             <a class="space-row" href="/status.php"><span class="space-row-main"><b>Lo stato dei servizi</b><span>Quarantasette controlli misurati sul server: se una parte è ferma, lo vedi prima di scrivere.</span></span></a>
             <a class="space-row" href="/roadmap.php"><span class="space-row-main"><b>Il percorso del rilascio</b><span>Quello che stiamo costruendo adesso e quello che arriva dopo, con lo stato vero di ogni voce.</span></span></a>
             <a class="space-row" href="/bacheca.php"><span class="space-row-main"><b>La bacheca</b><span>Quello che abbiamo appena corretto, raccontato per esteso: quasi sempre il tuo problema è già lì.</span></span></a>
           </div>'
        : '<div class="space-list">
             <a class="space-row" href="/status.php"><span class="space-row-main"><b>Service status</b><span>Forty-seven checks measured on the server: if a part is stuck, you see it before writing.</span></span></a>
             <a class="space-row" href="/roadmap.php"><span class="space-row-main"><b>The release path</b><span>What we are building right now and what comes next, with the true state of each item.</span></span></a>
             <a class="space-row" href="/bacheca.php"><span class="space-row-main"><b>The board</b><span>What we just fixed, described in full: your problem is very often already there.</span></span></a>
           </div>',
    'brief' => [
        't' => $isIt ? 'E anche la FAQ' : 'And the FAQ too',
        'items' => $isIt
            ? ['Domande su catalogo, motore, spazio personale, privacy e app: <a href="/faq.php">/faq.php</a>', 'Il sito sembra senza stile dopo un aggiornamento? Un Ctrl+F5 lo risolve quasi sempre.']
            : ['Questions about catalogue, engine, personal space, privacy and app: <a href="/faq.php">/faq.php</a>', 'The site looks unstyled after an update? A Ctrl+F5 almost always fixes it.'],
    ],
]);

lux_page_section([
    'n' => '02', 'id' => 'guida',
    't' => $isIt ? 'Parla subito con la guida' : 'Talk to the guide right away',
    'body' => $isIt
        ? '<p>In basso a destra c\'è il pulsante <strong>L.U.X. GUIDE</strong>: è una guida automatica che risponde sulle cose del sito (come funziona un tool, dove trovi le cose, cosa significa un avviso) e sa dirti se il problema è già noto.</p>
           <p>Se la guida non basta, dalla stessa finestra c\'è la voce per <strong>passare a una persona</strong>: la conversazione ti segue, non devi riscrivere niente.</p>'
        : '<p>At the bottom right there is the <strong>L.U.X. GUIDE</strong> button: an automatic guide that answers about the site (how a tool works, where to find things, what a notice means) and can tell you whether the problem is already known.</p>
           <p>If the guide is not enough, the same window has an option to <strong>hand over to a person</strong>: the conversation follows you, you do not have to write everything again.</p>',
    'box' => ['tone' => 'ok', 't' => $isIt ? 'Quando la guida serve davvero' : 'When the guide really helps', 'd' => $isIt
        ? 'Per capire come si usa uno strumento, trovare una pagina, sapere se un errore è noto. Per quello che invece richiede una decisione (rimborsi non ne facciamo, ma correzioni, ripristini e richieste tecniche sì) serve il modulo.'
        : 'To understand how a tool works, find a page, know whether an error is known. What needs a decision instead (we issue no refunds, but fixes, restores and technical requests yes) needs the form.'],
]);

lux_page_section([
    'n' => '03', 'id' => 'modulo',
    't' => $isIt ? 'Scrivici: il modulo' : 'Write to us: the form',
    'body' => '
        <form class="st-form" id="lux-support-form" novalidate>
            <div class="st-form-row">
                <label>' . ($isIt ? 'Nome (facoltativo)' : 'Name (optional)') . '
                    <input type="text" name="name" maxlength="80" autocomplete="name" placeholder="' . ($isIt ? 'Come ti chiami' : 'What is your name') . '">
                </label>
                <label>' . ($isIt ? 'Email (per la risposta)' : 'Email (for the reply)') . '
                    <input type="email" name="email" maxlength="120" autocomplete="email" placeholder="' . ($isIt ? 'nome@esempio.it' : 'name@example.com') . '">
                </label>
            </div>
            <label>' . ($isIt ? 'Di cosa si tratta' : 'What is it about') . '
                <select name="type">
                    <option value="Bug / errore">' . ($isIt ? 'Un errore o qualcosa che non funziona' : 'A bug or something broken') . '</option>
                    <option value="Risultato non corretto">' . ($isIt ? 'Un risultato non corretto o strano' : 'A wrong or odd result') . '</option>
                    <option value="App Android">' . ($isIt ? 'Problema con l\'app Android' : 'A problem with the Android app') . '</option>
                    <option value="Richiesta tecnica">' . ($isIt ? 'Richiesta tecnica' : 'Technical request') . '</option>
                    <option value="Altro">' . ($isIt ? 'Altro' : 'Something else') . '</option>
                </select>
            </label>
            <label>' . ($isIt ? 'Racconta cosa succede' : 'Tell us what happens') . '
                <textarea name="message" rows="6" maxlength="1500" required placeholder="' . ($isIt ? 'Cosa stavi facendo, cosa ti aspettavi, cosa è successo. Se puoi, la pagina e il dispositivo.' : 'What you were doing, what you expected, what happened. If you can, the page and the device.') . '"></textarea>
            </label>
            <div class="st-form-foot">
                <button type="submit" class="btn-small primary" id="lux-support-send">' . ($isIt ? 'Invia la richiesta' : 'Send the request') . '</button>
                <span class="b-empty-inline" id="lux-support-state" role="status"></span>
            </div>
            <p class="doc-note">' . ($isIt
                ? 'Cosa viene salvato: nome (o «Anonimo»), email, categoria, testo e la data. Nient\'altro. Tempi e diritti sono nella <a href="/privacy.php">Privacy</a>.'
                : 'What is stored: name (or "Anonymous"), email, category, text and the date. Nothing else. Retention and rights are in the <a href="/privacy.php">Privacy</a> page.') . '</p>
        </form>',
]);

lux_page_section([
    'n' => '04', 'id' => 'dopo',
    't' => $isIt ? 'Cosa succede dopo' : 'What happens next',
    'body' => $isIt
        ? '<ul>
             <li>Alla fine dell\'invio ricevi un <strong>codice</strong> (otto caratteri): è il riferimento della tua richiesta, annotalo o lascia la pagina aperta.</li>
             <li>La richiesta compare <strong>in coda</strong> nel pannello di chi risponde, con la sua categoria e priorità.</li>
             <li>La risposta arriva <strong>all\'indirizzo email</strong> che hai indicato, se l\'hai indicato. Senza email la richiesta resta visibile solo a noi: se serve una risposta, mettila.</li>
             <li>Quando la cosa è risolta lo stato passa a <strong>Risolto</strong> e la correzione viene raccontata in <a href="/bacheca.php">bacheca</a>.</li>
           </ul>
           <p>Non promettiamo tempi che non possiamo mantenere: risponde una persona sola, che nel frattempo scrive anche il codice. Le richieste semplici si chiudono in giornata, quelle che richiedono una modifica al sito entrano nel <a href="/roadmap.php">percorso del rilascio</a>.</p>'
        : '<ul>
             <li>When you send the form you get a <strong>code</strong> (eight characters): that is the reference of your request, note it down or leave the page open.</li>
             <li>The request appears <strong>in the queue</strong> of whoever answers, with its category and priority.</li>
             <li>The reply arrives <strong>at the email address</strong> you entered, if you entered one. Without an email the request is visible to us only: if you need an answer, add it.</li>
             <li>When the matter is solved the state becomes <strong>Resolved</strong> and the fix is described on the <a href="/bacheca.php">board</a>.</li>
           </ul>
           <p>We do not promise times we cannot keep: one person answers, and that person is also writing the code. Simple requests are closed the same day; those needing a change to the site join the <a href="/roadmap.php">release path</a>.</p>',
]);

lux_page_section([
    'n' => '05', 'id' => 'urgenti',
    't' => $isIt ? 'Problemi seri' : 'Serious problems',
    'body' => $isIt
        ? '<p>Se hai trovato un problema di <strong>sicurezza</strong> (dati visibili a chi non dovrebbe vederli, accessi anomali, contenuti manipolati) scrivilo dal modulo scegliendo «Richiesta tecnica» e metti <strong>SICUREZZA</strong> all\'inizio del testo: passa davanti alle altre richieste.</p>
           <p>Se invece l\'intero sito non risponde, sappi che non pubblichiamo nulla senza prima controllarlo: le <a href="/status.php">pagine di stato</a> e la <a href="/bacheca.php">bacheca</a> sono i primi due posti da guardare.</p>'
        : '<p>If you found a <strong>security</strong> problem (data visible to those who should not see it, anomalous access, tampered content) report it through the form by choosing "Technical request" and put <strong>SECURITY</strong> at the start of the text: it goes ahead of other requests.</p>
           <p>If the whole site is not responding, know that we publish nothing without checking it first: the <a href="/status.php">status pages</a> and the <a href="/bacheca.php">board</a> are the first two places to look.</p>',
]);

lux_page_close([
    'next' => [
        ['t' => t('FAQ'), 'd' => $isIt ? 'Le domande più comuni, prima di scrivere.' : 'The most common questions, before writing.', 'u' => '/faq.php'],
        ['t' => t('Contatti'), 'd' => $isIt ? 'Per collaborazioni, scuole, stampa o una domanda qualsiasi.' : 'For collaborations, schools, press or any question.', 'u' => '/contattaci.php'],
        ['t' => t('Stato'), 'd' => $isIt ? 'Come stanno i servizi adesso, misurato.' : 'How the services are doing now, measured.', 'u' => '/status.php'],
    ],
]);
?>
<script>
(function () {
    var form = document.getElementById('lux-support-form');
    if (!form) return;
    var state = document.getElementById('lux-support-state');
    var btn   = document.getElementById('lux-support-send');
    var L = {
        sending: <?= json_encode($isIt ? 'Invio in corso…' : 'Sending…') ?>,
        empty:   <?= json_encode($isIt ? 'Scrivi il testo della richiesta.' : 'Please write the request.') ?>,
        ok:      <?= json_encode($isIt ? 'Richiesta inviata. Il tuo codice è ' : 'Request sent. Your code is ') ?>,
        dup:     <?= json_encode($isIt ? 'Hai già una richiesta in attesa: aspetta che venga chiusa, oppure scrivi per email.' : 'You already have a pending request: wait for it to be closed, or write by email.') ?>,
        err:     <?= json_encode($isIt ? 'Non sono riuscito a inviare. Riprova o scrivi a administration.luxverse@gmail.com.' : 'I could not send it. Try again or write to administration.luxverse@gmail.com.') ?>
    };
    form.addEventListener('submit', function (e) {
        e.preventDefault();
        var fd = new FormData(form);
        if (!String(fd.get('message') || '').trim()) { state.textContent = L.empty; state.style.color = 'var(--danger)'; return; }
        fd.set('project', 'assistenza');   // non si sovrappone ai messaggi dei Contatti
        fd.set('kind', 'assistenza');
        state.textContent = L.sending; state.style.color = 'var(--text-muted)';
        if (btn) btn.disabled = true;
        fetch('/api/submit_message.php', { method: 'POST', body: fd })
            .then(function (r) { return r.json(); })
            .then(function (d) {
                if (d && d.success) {
                    state.textContent = L.ok + (d.code || '') + '.';
                    state.style.color = 'var(--ok)';
                    form.reset();
                } else {
                    var msg = (d && d.error) ? d.error : '';
                    state.textContent = /pending/i.test(msg) ? L.dup : (msg || L.err);
                    state.style.color = 'var(--danger)';
                }
            })
            .catch(function () { state.textContent = L.err; state.style.color = 'var(--danger)'; })
            .then(function () { if (btn) btn.disabled = false; });
    });
})();
</script>
<?php lux_footer(); ?>
