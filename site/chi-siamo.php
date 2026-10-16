<?php
// ============================================================
// CHI SIAMO — L.U.X. VERSE 39.1.1
// ============================================================
// Ripresa dalla versione precedente e riscritta sullo scheletro nuovo.
// I numeri non sono raccontati: arrivano dal catalogo vero, dal registro
// degli strumenti eseguibili e dalle versioni dichiarate in config.php.
// ============================================================

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/page.php';
lux_session_start();

$lang = currentLang();
$isIt = ($lang === 'it');

$total   = function_exists('lux_catalog_total') ? (int)lux_catalog_total() : 0;
$cats    = function_exists('lux_catalog_counts_all') ? (array)lux_catalog_counts_all() : [];
$runnable = function_exists('lux_agent_tools') ? count(lux_agent_tools()) : 0;
$siteVer = defined('LUX_SITE_VERSION') ? (string)LUX_SITE_VERSION : '';
$appVer  = defined('LUX_APP_VERSION') ? (string)LUX_APP_VERSION : '';
$nNotices = count(getNoticesFromLocal(0));
$fmt     = number_format($total, 0, $isIt ? ',' : '.', $isIt ? '.' : ',');

$pageTitle       = $isIt ? 'Chi siamo — L.U.X. Verse' : 'About us — L.U.X. Verse';
$pageDescription = $isIt
    ? 'Chi c\'è dietro L.U.X. Verse, perché esiste, con quali principi e con quali numeri veri: un progetto no profit, gratuito, senza registrazione e senza tracciamento.'
    : 'Who is behind L.U.X. Verse, why it exists, with which principles and which real numbers: a non-profit project, free, without registration and without tracking.';
$pageKeywords    = 'chi siamo, storia, missione, progetto no profit, L.U.X. Verse, about us, non profit';
$pageCSS         = '';

$sezioni = [
    ['id' => 'dietro',   'n' => '01', 't' => $isIt ? 'Chi c\'è dietro' : 'Who is behind it'],
    ['id' => 'perche',   'n' => '02', 't' => $isIt ? 'Perché esiste' : 'Why it exists'],
    ['id' => 'numeri',   'n' => '03', 't' => $isIt ? 'Cosa è diventato, in numeri' : 'What it became, in numbers'],
    ['id' => 'principi', 'n' => '04', 't' => $isIt ? 'I principi che non cambiano' : 'The principles that do not change'],
    ['id' => 'metodo',   'n' => '05', 't' => $isIt ? 'Come lavoriamo' : 'How we work'],
    ['id' => 'contatti', 'n' => '06', 't' => $isIt ? 'Come parlare con noi' : 'How to talk to us'],
];

lux_header();
lux_page_open([
    'crumbs'   => [['t' => 'Home', 'u' => '/index.php'], ['t' => t('Chi Siamo')]],
    'kicker'   => $isIt ? 'Il progetto, senza scorciatoie' : 'The project, without shortcuts',
    'title'    => $isIt ? 'Chi siamo' : 'About us',
    'lead'     => $isIt
        ? 'L.U.X. Verse è un ecosistema di strumenti gratuiti nato da un\'idea semplice: la tecnologia deve essere alla portata di tutti, non di chi può pagarla. Qui non si compra niente, non ci si registra e non si viene tracciati.'
        : 'L.U.X. Verse is a free-tools ecosystem born from a simple idea: technology must be within everyone\'s reach, not only within reach of those who can pay for it. Here nothing is bought, nobody signs up and nobody is tracked.',
    'meta'     => [
        $isIt ? 'Progetto no profit' : 'Non-profit project',
        $isIt ? 'Gratuito e senza registrazione' : 'Free and without registration',
        ($isIt ? 'Versione piattaforma ' : 'Platform version ') . $siteVer,
    ],
    'actions'  => [
        ['t' => $isIt ? 'Esplora gli strumenti' : 'Explore the tools', 'u' => '/index.php#progetti', 'p' => true],
        ['t' => $isIt ? 'Linea del tempo' : 'Timeline', 'u' => '/linea-del-tempo.php'],
    ],
    'sections' => $sezioni,
]);

lux_page_section([
    'n' => '01', 'id' => 'dietro',
    't' => $isIt ? 'Chi c\'è dietro' : 'Who is behind it',
    'body' => $isIt
        ? '<p>Dietro L.U.X. Verse c\'è <strong>una persona sola</strong>, il fondatore e sviluppatore del progetto, che ha quattordici anni: scrive il codice, disegna le pagine, pubblica le versioni e risponde ai messaggi. Nessuna società, nessun team alle spalle, nessun reparto che possa scaricare una decisione su qualcun altro.</p>
           <p>Il progetto <strong>non ha partita IVA e non svolge attività commerciale</strong>: non vende servizi, non chiede pagamenti, non incassa pubblicità e non genera alcun ricavo. È un progetto <strong>no profit</strong>, e per questo certe cose restano impossibili: per esempio un account sviluppatore a pagamento sugli store ufficiali, che è la ragione per cui l\'app si scarica come file APK dalla <a href="/app.php">pagina dell\'app</a>.</p>'
        : '<p>Behind L.U.X. Verse there is <strong>one person</strong>, the founder and developer of the project, who is fourteen: he writes the code, designs the pages, publishes releases and answers messages. No company, no team behind him, no department that can pass a decision on to someone else.</p>
           <p>The project <strong>has no VAT number and carries out no commercial activity</strong>: it sells no services, asks for no payments, takes no advertising and generates no income. It is a <strong>non-profit</strong> project, and that is why some things remain impossible: for example a paid developer account in the official stores, which is the reason the app is downloaded as an APK file from the <a href="/app.php">app page</a>.</p>',
    'brief' => [
        't' => $isIt ? 'In breve' : 'In short',
        'items' => $isIt
            ? ['Una persona, quattordici anni', 'Nessuna società, nessuna partita IVA', 'Nessun ricavo: né pubblicità, né abbonamenti, né vendite']
            : ['One person, fourteen years old', 'No company, no VAT number', 'No income: no advertising, no subscriptions, no sales'],
    ],
]);

lux_page_section([
    'n' => '02', 'id' => 'perche',
    't' => $isIt ? 'Perché esiste' : 'Why it exists',
    'body' => $isIt
        ? '<p>Nasce come un piccolo progetto personale: qualche strumento per risolvere cose di tutti i giorni — riassumere un testo, scrivere un\'email, spiegare un argomento, preparare uno schema. Poi è cresciuto, e la crescita ha chiarito la ragione per cui valeva la pena continuare: <strong>gli strumenti utili non devono diventare merce</strong>.</p>
           <p>Oggi il sito risponde a due bisogni concreti. Il primo: trovare lo strumento giusto in pochi secondi, senza perdersi in centomila voci uguali. Il secondo: ricevere il <em>risultato</em>, non un\'altra pagina da leggere. Da qui la frase di questo rilascio: L.U.X. Verse non è più solo un catalogo, è una piattaforma che <strong>capisce, esegue e ricorda</strong>.</p>'
        : '<p>It started as a small personal project: a few tools to solve everyday things — summarising a text, writing an email, explaining a topic, preparing an outline. Then it grew, and growing clarified the reason it was worth continuing: <strong>useful tools must not become merchandise</strong>.</p>
           <p>Today the site answers two concrete needs. The first: finding the right tool in seconds, without getting lost among one hundred thousand identical entries. The second: getting the <em>result</em>, not another page to read. Hence the sentence of this release: L.U.X. Verse is no longer only a catalogue, it is a platform that <strong>understands, runs and remembers</strong>.</p>',
]);

$catHtml = '';
if ($cats) {
    $catHtml = '<h3>' . ($isIt ? 'Le categorie del catalogo' : 'The catalogue categories') . '</h3><div class="st-list">';
    foreach ($cats as $slug => $n) {
        if ($slug === '' || !is_numeric($n) || (int)$n <= 0) continue;
        $catHtml .= '<div class="st-row"><span class="st-dot ok"></span><b>' . htmlspecialchars(ucfirst((string)$slug)) . '</b>'
                  . '<span>' . number_format((int)$n, 0, $isIt ? ',' : '.', $isIt ? '.' : ',') . ' ' . ($isIt ? 'strumenti' : 'tools') . '</span></div>';
    }
    $catHtml .= '</div>';
}

lux_page_section([
    'n' => '03', 'id' => 'numeri',
    't' => $isIt ? 'Cosa è diventato, in numeri' : 'What it became, in numbers',
    'body' => ($isIt
        ? '<p>Questi numeri sono letti dal catalogo vero ogni volta che apri la pagina: non li aggiorniamo a mano e non li arrotondiamo per farli sembrare più belli.</p>'
        : '<p>These numbers are read from the real catalogue every time you open the page: we do not update them by hand and we do not round them to make them look better.</p>')
        . '<div class="st-sum">'
        . '<span class="st-pill ok"><span class="st-dot ok"></span>' . ($isIt ? 'Strumenti nel catalogo' : 'Tools in the catalogue') . ' <b>' . $fmt . '</b></span>'
        . '<span class="st-pill ok"><span class="st-dot ok"></span>' . ($isIt ? 'Eseguibili dal motore' : 'Runnable by the engine') . ' <b>' . $runnable . '</b></span>'
        . '<span class="st-pill"><span class="st-dot"></span>' . ($isIt ? 'Avvisi pubblicati' : 'Published notices') . ' <b>' . $nNotices . '</b></span>'
        . '<span class="st-pill"><span class="st-dot"></span>' . ($isIt ? 'Versione app Android' : 'Android app version') . ' <b>' . htmlspecialchars($appVer) . '</b></span>'
        . '</div>' . $catHtml,
    'note' => $isIt
        ? 'Quanti strumenti sa eseguire il motore adesso è scritto anche nel <a href="/roadmap.php">percorso del rilascio</a>, con ingresso e uscita di ognuno.'
        : 'How many tools the engine can run right now is also written in the <a href="/roadmap.php">release path</a>, with the input and output of each one.',
]);

lux_page_section([
    'n' => '04', 'id' => 'principi',
    't' => $isIt ? 'I principi che non cambiano' : 'The principles that do not change',
    'body' => $isIt
        ? '<ul>
             <li><strong>Gratis, davvero.</strong> Nessun costo, nessun abbonamento, nessuna versione "premium" che sblocca quello che serve.</li>
             <li><strong>Nessuna registrazione.</strong> Si usa tutto subito. Il tuo spazio personale si ritrova con un codice, non con un account.</li>
             <li><strong>Nessun tracciamento.</strong> Non ci sono cookie di tracciamento né profilazione pubblicitaria. I cookie tecnici che il sito usa sono elencati nella <a href="/privacy.php">Privacy</a>.</li>
             <li><strong>Nessuna pubblicità.</strong> Non guadagniamo dal tuo tempo, quindi non dobbiamo rubarlo.</li>
             <li><strong>Trasparenza.</strong> Ogni rilascio ha il suo avviso in bacheca, il percorso è pubblico e lo <a href="/status.php">stato dei servizi</a> è misurato, non dichiarato.</li>
             <li><strong>Gli errori si dicono.</strong> Quando sbagliamo lo scriviamo, con la stessa evidenza con cui scriviamo quello che funziona.</li>
           </ul>'
        : '<ul>
             <li><strong>Free, truly.</strong> No cost, no subscription, no "premium" version that unlocks what you need.</li>
             <li><strong>No registration.</strong> Everything works immediately. Your personal space is found again with a code, not an account.</li>
             <li><strong>No tracking.</strong> There are no tracking cookies and no advertising profiling. The technical cookies the site uses are listed in the <a href="/privacy.php">Privacy</a> page.</li>
             <li><strong>No advertising.</strong> We do not earn from your time, so we do not need to steal it.</li>
             <li><strong>Transparency.</strong> Every release has its notice on the board, the path is public and <a href="/status.php">service status</a> is measured, not declared.</li>
             <li><strong>Mistakes are said out loud.</strong> When we get it wrong we write it, with the same prominence as what works.</li>
           </ul>',
]);

lux_page_section([
    'n' => '05', 'id' => 'metodo',
    't' => $isIt ? 'Come lavoriamo' : 'How we work',
    'body' => $isIt
        ? '<p>Una regola vale per ogni modifica: <strong>deve rendere l\'ecosistema più utile, non solo più ricco</strong>. Se una funzione non fa risparmiare tempo a chi usa il sito, non entra nel rilascio: resta nel <a href="/roadmap.php">percorso</a>, nella colonna di quello che arriva dopo, finché non ha un senso vero.</p>
           <p>Il lavoro si fa in pubblico: le correzioni si annunciano, i rilievi dei visitatori diventano lavoro vero e le cose che non vanno dette si dicono nel modo in cui le direbbe una persona che ci mette la faccia.</p>'
        : '<p>One rule applies to every change: <strong>it must make the ecosystem more useful, not just bigger</strong>. If a feature does not save time for the people using the site, it does not enter the release: it stays in the <a href="/roadmap.php">path</a>, in the column of what comes later, until it has a real purpose.</p>
           <p>The work happens in public: fixes are announced, visitors\' reports become real work, and the things that need saying are said the way a person who puts their face to it would say them.</p>',
]);

lux_page_section([
    'n' => '06', 'id' => 'contatti',
    't' => $isIt ? 'Come parlare con noi' : 'How to talk to us',
    'body' => $isIt
        ? '<p>Tre strade, a seconda di cosa ti serve:</p>
           <ul>
             <li><strong>Assistenza</strong> — qualcosa non funziona, un errore, una richiesta tecnica: <a href="/assistenza.php">/assistenza.php</a>.</li>
             <li><strong>Contatti</strong> — collaborazioni, scuole, stampa o una domanda qualsiasi: <a href="/contattaci.php">/contattaci.php</a>.</li>
             <li><strong>Proponi uno strumento</strong> — manca qualcosa nel catalogo? Dillo e vota le idee degli altri: <a href="/proponi.php">/proponi.php</a>.</li>
           </ul>
           <p>Si può scrivere anche per email, all\'indirizzo <strong>administration.luxverse@gmail.com</strong>. Risponde la stessa persona che scrive il codice.</p>'
        : '<p>Three routes, depending on what you need:</p>
           <ul>
             <li><strong>Support</strong> — something is broken, an error, a technical request: <a href="/assistenza.php">/assistenza.php</a>.</li>
             <li><strong>Contact</strong> — collaborations, schools, press or any question: <a href="/contattaci.php">/contattaci.php</a>.</li>
             <li><strong>Suggest a tool</strong> — something missing from the catalogue? Say it and vote on other ideas: <a href="/proponi.php">/proponi.php</a>.</li>
           </ul>
           <p>You can also write by email, to <strong>administration.luxverse@gmail.com</strong>. The same person who writes the code answers.</p>',
    'box' => ['tone' => 'ok', 't' => $isIt ? 'La firma' : 'The signature', 'd' => 'M. M. YEAMIN — ' . ($isIt ? 'Fondatore e sviluppatore di L.U.X. Verse' : 'Founder and developer of L.U.X. Verse')],
]);

lux_page_close([
    'next' => [
        ['t' => t('Linea del Tempo'), 'd' => $isIt ? 'La storia del progetto, tappa per tappa.' : 'The history of the project, milestone by milestone.', 'u' => '/linea-del-tempo.php'],
        ['t' => t('FAQ'), 'd' => $isIt ? 'Le domande più frequenti, con risposte brevi.' : 'The most frequent questions, with short answers.', 'u' => '/faq.php'],
        ['t' => t('Privacy Policy'), 'd' => $isIt ? 'Cosa raccogliamo, perché e per quanto tempo.' : 'What we collect, why and for how long.', 'u' => '/privacy.php'],
    ],
]);
lux_footer();
