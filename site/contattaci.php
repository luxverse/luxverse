<?php
// ============================================================
// CONTATTI — L.U.X. VERSE 39.1.1
// ============================================================
// Un modulo solo, con un percorso diverso da quello dell'assistenza
// (project = "contatti"): cosi' una richiesta tecnica in attesa non
// blocca un messaggio di altro tipo.
// ============================================================

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/page.php';
lux_session_start();

$lang = currentLang();
$isIt = ($lang === 'it');

$pageTitle       = $isIt ? 'Contatti — L.U.X. Verse' : 'Contact — L.U.X. Verse';
$pageDescription = $isIt
    ? 'Come contattare L.U.X. Verse: modulo diretto, email, canali social. Risponde il fondatore del progetto.'
    : 'How to contact L.U.X. Verse: direct form, email, social channels. The founder of the project answers.';
$pageKeywords    = 'contatti, contattare, email, collaborazioni, scuole, stampa, L.U.X. Verse';
$pageCSS         = '';

$sezioni = [
    ['id' => 'quando',  'n' => '01', 't' => $isIt ? 'Quando scriverci qui' : 'When to write here'],
    ['id' => 'modulo',  'n' => '02', 't' => $isIt ? 'Il modulo' : 'The form'],
    ['id' => 'canali',  'n' => '03', 't' => $isIt ? 'Altri modi per raggiungerci' : 'Other ways to reach us'],
    ['id' => 'risponde','n' => '04', 't' => $isIt ? 'Chi risponde, e come' : 'Who answers, and how'],
];

lux_header();
lux_page_open([
    'crumbs'   => [['t' => 'Home', 'u' => '/index.php'], ['t' => t('Contatti')]],
    'kicker'   => $isIt ? 'Parliamo' : 'Let\'s talk',
    'title'    => $isIt ? 'Contatti' : 'Contact',
    'lead'     => $isIt
        ? 'Per collaborazioni, scuole, stampa, segnalazioni di idee o una domanda qualsiasi. Non c\'è un centralino e non c\'è un modulo che finisce nel vuoto: quello che scrivi arriva a una persona.'
        : 'For collaborations, schools, press, idea reports or any question at all. There is no call centre and no form that disappears into nothing: what you write reaches a person.',
    'meta'     => [
        'administration.luxverse@gmail.com',
        $isIt ? 'Progetto no profit' : 'Non-profit project',
        $isIt ? 'Aggiornata il 15 ottobre 2026' : 'Updated on 15 October 2026',
    ],
    'actions'  => [
        ['t' => $isIt ? 'Vai al modulo' : 'Go to the form', 'u' => '#modulo', 'p' => true],
        ['t' => t('Assistenza'), 'u' => '/assistenza.php'],
    ],
    'sections' => $sezioni,
]);

lux_page_section([
    'n' => '01', 'id' => 'quando',
    't' => $isIt ? 'Quando scriverci qui' : 'When to write here',
    'body' => $isIt
        ? '<h3>Scuole e insegnanti</h3>
           <p>Se vuoi usare L.U.X. Verse in classe, scrivici: possiamo dirti quali strumenti si prestano meglio a una lezione, come funziona la modalità IA senza registrazione e cosa dicono i Termini sui contenuti generati. Non chiediamo convenzioni né pagamenti: è tutto gratuito, anche per le scuole.</p>
           <h3>Collaborazioni e proposte</h3>
           <p>Hai un\'idea, uno strumento da proporre o vuoi dare una mano? Per gli strumenti c\'è la pagina dedicata (<a href="/proponi.php">Proponi uno strumento</a>), dove la tua proposta viene votata dal pubblico. Per tutto il resto, questo modulo va benissimo.</p>
           <h3>Stampa e contenuti</h3>
           <p>Sul progetto si può scrivere liberamente: i numeri veri li trovi in <a href="/chi-siamo.php">Chi siamo</a> e nello <a href="/status.php">stato dei servizi</a>. Se ti serve una dichiarazione o un\'intervista, chiedila qui.</p>
           <h3>Quando invece serve l\'assistenza</h3>
           <p>Se qualcosa non funziona, un risultato è sbagliato o l\'app dà problemi, la strada giusta è <a href="/assistenza.php">Assistenza</a>: lì la richiesta entra in coda con una priorità e ricevi un codice per seguirla.</p>'
        : '<h3>Schools and teachers</h3>
           <p>If you want to use L.U.X. Verse in class, write to us: we can tell you which tools suit a lesson best, how the AI mode works without registration and what the Terms say about generated content. We ask for no agreements and no payments: it is all free, for schools too.</p>
           <h3>Collaborations and proposals</h3>
           <p>You have an idea, a tool to propose, or want to help? For tools there is a dedicated page (<a href="/proponi.php">Suggest a tool</a>), where your proposal is voted on by the public. For everything else, this form is fine.</p>
           <h3>Press and content</h3>
           <p>You are free to write about the project: the real numbers are in <a href="/chi-siamo.php">About us</a> and in the <a href="/status.php">service status</a>. If you need a statement or an interview, ask here.</p>
           <h3>When support is the right route</h3>
           <p>If something is broken, a result is wrong or the app misbehaves, the right route is <a href="/assistenza.php">Support</a>: there the request joins a queue with a priority and you get a code to follow it.</p>',
    'brief' => [
        't' => $isIt ? 'In breve' : 'In short',
        'items' => $isIt
            ? ['Collaborazioni, scuole, stampa, idee: qui', 'Problemi tecnici e errori: Assistenza', 'Strumenti nuovi da proporre: bacheca delle proposte']
            : ['Collaborations, schools, press, ideas: here', 'Technical problems and bugs: Support', 'New tools to propose: the proposals board'],
    ],
]);

lux_page_section([
    'n' => '02', 'id' => 'modulo',
    't' => $isIt ? 'Il modulo' : 'The form',
    'body' => '
        <form class="st-form" id="lux-contact-form" novalidate>
            <div class="st-form-row">
                <label>' . ($isIt ? 'Nome' : 'Name') . '
                    <input type="text" name="name" maxlength="80" autocomplete="name" placeholder="' . ($isIt ? 'Come ti chiami' : 'What is your name') . '">
                </label>
                <label>' . ($isIt ? 'Email' : 'Email') . '
                    <input type="email" name="email" maxlength="120" autocomplete="email" placeholder="' . ($isIt ? 'nome@esempio.it' : 'name@example.com') . '">
                </label>
            </div>
            <label>' . ($isIt ? 'Motivo del contatto' : 'Reason for contacting') . '
                <select name="type">
                    <option value="Scuola">' . ($isIt ? 'Scuola o classe' : 'School or class') . '</option>
                    <option value="Collaborazione">' . ($isIt ? 'Collaborazione o proposta' : 'Collaboration or proposal') . '</option>
                    <option value="Stampa">' . ($isIt ? 'Stampa o contenuti' : 'Press or content') . '</option>
                    <option value="Dati e privacy">' . ($isIt ? 'Dati personali e privacy' : 'Personal data and privacy') . '</option>
                    <option value="Altro">' . ($isIt ? 'Altro' : 'Something else') . '</option>
                </select>
            </label>
            <label>' . ($isIt ? 'Messaggio' : 'Message') . '
                <textarea name="message" rows="6" maxlength="1500" required placeholder="' . ($isIt ? 'Scrivi qui. Se ci sono date o scadenze, mettile: così possiamo organizzarci.' : 'Write here. If there are dates or deadlines, include them so we can organise.') . '"></textarea>
            </label>
            <div class="st-form-foot">
                <button type="submit" class="btn-small primary" id="lux-contact-send">' . ($isIt ? 'Invia il messaggio' : 'Send the message') . '</button>
                <span class="b-empty-inline" id="lux-contact-state" role="status"></span>
            </div>
            <p class="doc-note">' . ($isIt
                ? 'Vengono salvati nome, email, motivo, testo e data. Non vengono usati per altro e non finiscono in nessuna lista di marketing.'
                : 'Name, email, reason, text and date are stored. They are not used for anything else and do not end up in any marketing list.') . '</p>
        </form>',
]);

lux_page_section([
    'n' => '03', 'id' => 'canali',
    't' => $isIt ? 'Altri modi per raggiungerci' : 'Other ways to reach us',
    'body' => $isIt
        ? '<div class="space-list">
             <a class="space-row" href="mailto:administration.luxverse@gmail.com"><span class="space-row-main"><b>Email</b><span>administration.luxverse@gmail.com — la stessa casella legge tutto.</span></span></a>
             <a class="space-row" href="https://www.tiktok.com/@luxverse.c22.eu" target="_blank" rel="noopener"><span class="space-row-main"><b>TikTok</b><span>Le novità mostrate in pochi secondi e il dietro le quinte.</span></span></a>
             <a class="space-row" href="https://www.youtube.com/@luxverse.22web" target="_blank" rel="noopener"><span class="space-row-main"><b>YouTube</b><span>Le stesse cose, con più calma e in video.</span></span></a>
             <a class="space-row" href="/proponi.php"><span class="space-row-main"><b>Proponi uno strumento</b><span>Un\'idea per il catalogo: si propone e si vota in pubblico.</span></span></a>
           </div>'
        : '<div class="space-list">
             <a class="space-row" href="mailto:administration.luxverse@gmail.com"><span class="space-row-main"><b>Email</b><span>administration.luxverse@gmail.com — the same mailbox reads everything.</span></span></a>
             <a class="space-row" href="https://www.tiktok.com/@luxverse.c22.eu" target="_blank" rel="noopener"><span class="space-row-main"><b>TikTok</b><span>New things shown in seconds, plus behind the scenes.</span></span></a>
             <a class="space-row" href="https://www.youtube.com/@luxverse.22web" target="_blank" rel="noopener"><span class="space-row-main"><b>YouTube</b><span>The same things, more calmly and on video.</span></span></a>
             <a class="space-row" href="/proponi.php"><span class="space-row-main"><b>Suggest a tool</b><span>An idea for the catalogue: proposed and voted on in public.</span></span></a>
           </div>',
]);

lux_page_section([
    'n' => '04', 'id' => 'risponde',
    't' => $isIt ? 'Chi risponde, e come' : 'Who answers, and how',
    'body' => $isIt
        ? '<p>Risponde il fondatore del progetto: la stessa persona che scrive il codice, pubblica i rilasci e firma gli avvisi in bacheca. Non ci sono risposte automatiche che rimandano a un\'altra pagina, se non quando la pagina giusta è davvero più veloce.</p>
           <p>Se il tuo messaggio contiene una richiesta sui <strong>tuoi dati</strong> (accesso, correzione, cancellazione) puoi scriverlo da qui scegliendo «Dati personali e privacy»: è la stessa strada prevista dalla <a href="/privacy.php">Privacy Policy</a>, e viene trattata come tale.</p>'
        : '<p>The founder of the project answers: the same person who writes the code, publishes releases and signs the notices on the board. There are no automatic replies sending you to another page, unless that page really is faster.</p>
           <p>If your message contains a request about <strong>your data</strong> (access, correction, deletion) you can write it here by choosing "Personal data and privacy": it is the same route provided by the <a href="/privacy.php">Privacy Policy</a>, and it is treated as such.</p>',
    'box' => ['tone' => 'ok', 't' => $isIt ? 'La firma' : 'The signature', 'd' => 'M. M. YEAMIN — ' . ($isIt ? 'Fondatore e sviluppatore di L.U.X. Verse' : 'Founder and developer of L.U.X. Verse')],
]);

lux_page_close([
    'next' => [
        ['t' => t('Assistenza'), 'd' => $isIt ? 'Qualcosa non funziona? Il posto giusto è qui.' : 'Something broken? That is the right place.', 'u' => '/assistenza.php'],
        ['t' => t('Chi Siamo'), 'd' => $isIt ? 'Chi c\'è dietro il progetto, senza giri di parole.' : 'Who is behind the project, without beating around the bush.', 'u' => '/chi-siamo.php'],
        ['t' => t('Privacy Policy'), 'd' => $isIt ? 'Cosa raccogliamo, perché e per quanto tempo.' : 'What we collect, why and for how long.', 'u' => '/privacy.php'],
    ],
]);
?>
<script>
(function () {
    var form = document.getElementById('lux-contact-form');
    if (!form) return;
    var state = document.getElementById('lux-contact-state');
    var btn   = document.getElementById('lux-contact-send');
    var L = {
        sending: <?= json_encode($isIt ? 'Invio in corso…' : 'Sending…') ?>,
        empty:   <?= json_encode($isIt ? 'Scrivi il messaggio.' : 'Please write the message.') ?>,
        ok:      <?= json_encode($isIt ? 'Messaggio inviato. Il tuo codice è ' : 'Message sent. Your code is ') ?>,
        dup:     <?= json_encode($isIt ? 'Hai già un messaggio in attesa: aspetta la risposta, oppure scrivi per email.' : 'You already have a pending message: wait for the reply, or write by email.') ?>,
        err:     <?= json_encode($isIt ? 'Invio non riuscito. Riprova o scrivi a administration.luxverse@gmail.com.' : 'Sending failed. Try again or write to administration.luxverse@gmail.com.') ?>
    };
    form.addEventListener('submit', function (e) {
        e.preventDefault();
        var fd = new FormData(form);
        if (!String(fd.get('message') || '').trim()) { state.textContent = L.empty; state.style.color = 'var(--danger)'; return; }
        fd.set('project', 'contatti');
        fd.set('kind', 'contatti');
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
