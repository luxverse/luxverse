<?php
// ============================================================
// FAQ — L.U.X. VERSE 39.1.1
// ============================================================
// Domande e risposte divise per argomento: progetto, motore IA, spazio
// personale, privacy, app, problemi. Ogni risposta dice una cosa vera:
// se una funzione non c'e' ancora, la risposta lo dice.
// ============================================================

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/page.php';
lux_session_start();

$lang = currentLang();
$isIt = ($lang === 'it');

$total = function_exists('lux_catalog_total') ? (int)lux_catalog_total() : 0;
$fmt   = number_format($total, 0, $isIt ? ',' : '.', $isIt ? '.' : ',');

$pageTitle       = $isIt ? 'Domande frequenti — L.U.X. Verse' : 'Frequently asked questions — L.U.X. Verse';
$pageDescription = $isIt
    ? 'Le risposte alle domande più frequenti su L.U.X. Verse: catalogo, motore IA, spazio personale senza account, privacy, app Android e problemi tecnici.'
    : 'Answers to the most frequent questions about L.U.X. Verse: catalogue, AI engine, personal space without an account, privacy, Android app and technical issues.';
$pageKeywords    = 'FAQ, domande frequenti, aiuto, L.U.X. Verse, supporto, senza registrazione, motore IA';
$pageCSS         = '';

/** Una domanda con la sua risposta. */
function faq_qa(string $q, string $a, string $id = ''): void
{
    echo '<h3' . ($id !== '' ? ' id="' . htmlspecialchars($id) . '"' : '') . '>' . htmlspecialchars($q) . '</h3>';
    echo '<p>' . $a . '</p>';
}

$sezioni = [
    ['id' => 'progetto', 'n' => '01', 't' => $isIt ? 'Il progetto e il catalogo' : 'The project and the catalogue'],
    ['id' => 'motore',   'n' => '02', 't' => $isIt ? 'Il motore: IA (Veloce) e Catalogo (Manuale)' : 'The engine: AI (Fast) and Catalogue (Manual)'],
    ['id' => 'spazio',   'n' => '03', 't' => $isIt ? 'Il mio spazio, senza account' : 'My space, without an account'],
    ['id' => 'privacy',  'n' => '04', 't' => $isIt ? 'Privacy, cookie e dati' : 'Privacy, cookies and data'],
    ['id' => 'app',      'n' => '05', 't' => $isIt ? 'L\'app Android' : 'The Android app'],
    ['id' => 'problemi', 'n' => '06', 't' => $isIt ? 'Problemi, errori, correzioni' : 'Problems, errors, fixes'],
];

lux_header();
lux_page_open([
    'crumbs'   => [['t' => 'Home', 'u' => '/index.php'], ['t' => t('FAQ')]],
    'kicker'   => $isIt ? 'Risposte brevi, cose vere' : 'Short answers, true things',
    'title'    => $isIt ? 'Domande frequenti' : 'Frequently asked questions',
    'lead'     => $isIt
        ? 'Le domande che ci fanno più spesso, in ordine di argomento. Se la risposta che cerchi non c\'è, la pagina Assistenza è a un clic: ci arriva tutto e rispondiamo.'
        : 'The questions we are asked most often, grouped by topic. If the answer you are looking for is not here, the Support page is one click away: everything reaches us and we answer.',
    'meta'     => [
        count($sezioni) . ' ' . ($isIt ? 'argomenti' : 'topics'),
        $isIt ? 'Aggiornata il 15 ottobre 2026' : 'Updated on 15 October 2026',
    ],
    'actions'  => [
        ['t' => t('Assistenza'), 'u' => '/assistenza.php', 'p' => true],
        ['t' => t('Contatti'), 'u' => '/contattaci.php'],
    ],
    'sections' => $sezioni,
]);

// ---------- 01 progetto ----------
ob_start();
faq_qa(
    $isIt ? 'Che cos\'è L.U.X. Verse?' : 'What is L.U.X. Verse?',
    $isIt
        ? 'Un ecosistema di strumenti gratuiti per creatività, produttività, studio e giochi. Il catalogo contiene ' . $fmt . ' strumenti; dal rilascio 39.1.1 la barra della home ha due modalità e in modalità IA ti dà il <strong>risultato</strong> invece di un\'altra pagina da aprire.'
        : 'An ecosystem of free tools for creativity, productivity, study and games. The catalogue holds ' . $fmt . ' tools; since release 39.1.1 the home bar has two modes and in AI mode it gives you the <strong>result</strong> instead of another page to open.'
);
faq_qa(
    $isIt ? 'Costa qualcosa? Ci sono limiti nascosti?' : 'Does it cost anything? Are there hidden limits?',
    $isIt
        ? 'No. Non ci sono costi, abbonamenti, versioni premium né limiti nascosti. Non c\'è pubblicità e non c\'è nulla da sbloccare: il progetto è no profit e non genera ricavi.'
        : 'No. There are no costs, subscriptions, premium versions or hidden limits. There is no advertising and nothing to unlock: the project is non-profit and generates no income.'
);
faq_qa(
    $isIt ? 'Serve un account o un\'email?' : 'Do I need an account or an email?',
    $isIt
        ? 'No. Nessuna registrazione, nessuna email, nessuna password. Tutto funziona subito. Le uniche cose che restano legate a te — preferiti, cronologia, risultati salvati — si ritrovano con un codice personale, e nel prossimo argomento c\'è scritto come.'
        : 'No. No registration, no email, no password. Everything works immediately. The only things tied to you — favourites, history, saved results — are found again with a personal code, explained in the next topic.'
);
faq_qa(
    $isIt ? 'Chi c\'è dietro?' : 'Who is behind it?',
    $isIt
        ? 'Una persona sola, il fondatore e sviluppatore del progetto (quattordici anni), senza società e senza partita IVA. La storia completa è nella pagina <a href="/chi-siamo.php">Chi siamo</a>.'
        : 'One person, the founder and developer of the project (fourteen years old), with no company and no VAT number. The full story is on the <a href="/chi-siamo.php">About us</a> page.'
);
$s1 = ob_get_clean();

lux_page_section([
    'n' => '01', 'id' => 'progetto',
    't' => $isIt ? 'Il progetto e il catalogo' : 'The project and the catalogue',
    'body' => $s1,
    'brief' => [
        't' => $isIt ? 'Da ricordare' : 'To remember',
        'items' => $isIt
            ? ['Gratis per sempre, senza pubblicità', 'Nessuna registrazione, in nessun caso', 'Il catalogo resta navigabile pagina per pagina come sempre']
            : ['Free forever, without advertising', 'No registration, ever', 'The catalogue stays browsable page by page as always'],
    ],
]);

// ---------- 02 motore ----------
ob_start();
faq_qa(
    $isIt ? 'Che differenza c\'è tra "IA (Veloce)" e "Catalogo (Manuale)"?' : 'What is the difference between "AI (Fast)" and "Catalogue (Manual)"?',
    $isIt
        ? '<strong>IA (Veloce)</strong>: scrivi cosa ti serve con parole tue, L.U.X. sceglie gli strumenti adatti, li esegue e ti mostra il risultato finito, senza aprire nessuna pagina. <strong>Catalogo (Manuale)</strong>: la ricerca di sempre, dove scegli tu lo strumento da aprire. Si passa da una all\'altra con un clic, nella stessa barra.'
        : '<strong>AI (Fast)</strong>: write what you need in your own words, L.U.X. picks the right tools, runs them and shows you the finished result, without opening any page. <strong>Catalogue (Manual)</strong>: the usual search, where you choose the tool to open. One click switches between them, in the same bar.'
);
faq_qa(
    $isIt ? 'Tutti gli strumenti si possono eseguire dall\'IA?' : 'Can the AI run every tool?',
    $isIt
        ? 'No, e lo diciamo: nel motore entrano solo gli strumenti con ingresso e uscita dichiarati e verificati. Gli altri restano raggiungibili come pagine da aprire. L\'elenco aggiornato di quelli eseguibili è nel <a href="/roadmap.php">percorso del rilascio</a>.'
        : 'No, and we say so: only tools with a declared and verified input and output enter the engine. The others stay reachable as pages to open. The up-to-date list of runnable ones is in the <a href="/roadmap.php">release path</a>.'
);
faq_qa(
    $isIt ? 'Se l\'IA non risponde, il sito si rompe?' : 'If the AI does not answer, does the site break?',
    $isIt
        ? 'No. Il sito ripiega sulla ricerca per intento e poi sulle parole chiave, e ti dice che sta ripiegando. Il catalogo e le pagine degli strumenti restano sempre funzionanti.'
        : 'No. The site falls back to intent search and then to keywords, and tells you it is falling back. The catalogue and tool pages always keep working.'
);
faq_qa(
    $isIt ? 'I risultati dell\'IA sono affidabili?' : 'Are AI results reliable?',
    $isIt
        ? 'Sono generati da modelli automatici: utili per partire in fretta, ma vanno sempre riletti con la tua testa. Per questo ogni risultato mostra gli strumenti usati e, quando serve, puoi salvarlo e condividerlo. Non sono consulenza professionale (né medica, né legale, né fiscale): i <a href="/terms.php">Termini di Servizio</a> lo dicono per esteso.'
        : 'They are produced by automatic models: useful to get started fast, but always worth re-reading with your own head. That is why every result shows the tools used and, when needed, you can save and share it. They are not professional advice (medical, legal or tax): the <a href="/terms.php">Terms of Service</a> spell this out.'
);
$s2 = ob_get_clean();

lux_page_section([
    'n' => '02', 'id' => 'motore',
    't' => $isIt ? 'Il motore: IA (Veloce) e Catalogo (Manuale)' : 'The engine: AI (Fast) and Catalogue (Manual)',
    'body' => $s2,
    'box' => [
        'tone' => 'ok',
        't' => $isIt ? 'Da provare in dieci secondi' : 'Try it in ten seconds',
        'd' => $isIt
            ? 'Vai in home, lascia la modalità <strong>IA (Veloce)</strong>, scrivi per esempio «riassumi questo testo in cinque punti» e premi Esegui adesso: il risultato compare sotto la barra.'
            : 'Go to the home, leave <strong>AI (Fast)</strong> mode, write for example "summarise this text in five points" and press Run it now: the result appears under the bar.',
    ],
]);

// ---------- 03 spazio ----------
ob_start();
faq_qa(
    $isIt ? 'Che cos\'è il "codice personale"?' : 'What is the "personal code"?',
    $isIt
        ? 'Un codice di dodici caratteri che ritrova il tuo spazio (cronologia, preferiti, risultati salvati, memoria dell\'assistente) anche su un altro telefono o computer. Lo trovi nella pagina <a href="/spazio.php">Il mio spazio</a>: lo generi una volta e lo conservi dove vuoi.'
        : 'A twelve-character code that finds your space again (history, favourites, saved results, assistant memory) on another phone or computer. You find it on the <a href="/spazio.php">My space</a> page: you generate it once and keep it wherever you like.'
);
faq_qa(
    $isIt ? 'Sul server cosa resta del mio codice?' : 'What stays on the server about my code?',
    $isIt
        ? 'Solo l\'<strong>impronta</strong> del codice (una stringa che non permette di risalire al codice stesso), insieme al contenuto dello spazio. Il codice in chiaro non viene scritto da nessuna parte e non è visibile a nessuno: se lo perdi, quello spazio non è più raggiungibile.'
        : 'Only the <strong>fingerprint</strong> of the code (a string that does not allow the code itself to be recovered), together with the content of the space. The code in clear text is written nowhere and is visible to no one: if you lose it, that space can no longer be reached.'
);
faq_qa(
    $isIt ? 'Devo per forza usare il codice?' : 'Do I have to use the code?',
    $isIt
        ? 'No. Il sito funziona benissimo senza: in quel caso cronologia e preferiti restano solo sul tuo dispositivo, e non viaggiano da nessuna parte.'
        : 'No. The site works perfectly well without it: in that case history and favourites stay only on your device and travel nowhere.'
);
$s3 = ob_get_clean();

lux_page_section([
    'n' => '03', 'id' => 'spazio',
    't' => $isIt ? 'Il mio spazio, senza account' : 'My space, without an account',
    'body' => $s3,
]);

// ---------- 04 privacy ----------
ob_start();
faq_qa(
    $isIt ? 'Usate cookie?' : 'Do you use cookies?',
    $isIt
        ? 'Solo <strong>cookie tecnici</strong>: sessione, lingua e la tua scelta sui cookie. Nessun cookie di tracciamento, nessuna profilazione, nessuna pubblicità. L\'elenco completo è nella <a href="/privacy.php">Privacy</a>.'
        : 'Only <strong>technical cookies</strong>: session, language and your cookie choice. No tracking cookies, no profiling, no advertising. The full list is in the <a href="/privacy.php">Privacy</a> page.'
);
faq_qa(
    $isIt ? 'Cosa fate dei messaggi che vi scrivo?' : 'What do you do with the messages I send you?',
    $isIt
        ? 'Restano nel server per il tempo necessario a risponderti e a tenere la cronologia della conversazione. Non vengono ceduti né usati per altro. Nella Privacy sono indicati i tempi di conservazione e come chiedere la cancellazione.'
        : 'They stay on the server for as long as needed to answer you and keep the history of the conversation. They are neither sold nor used for anything else. The Privacy page lists retention times and how to ask for deletion.'
);
faq_qa(
    $isIt ? 'Serve il consenso ai cookie per usare il sito?' : 'Is cookie consent needed to use the site?',
    $isIt
        ? 'Il sito funziona anche dicendo «Non accetto»: i cookie tecnici servono solo a far funzionare le cose (sessione e lingua). Puoi cambiare la tua scelta quando vuoi dalle pagine legali.'
        : 'The site works even if you choose "I do not accept": technical cookies only make things work (session and language). You can change your choice whenever you like from the legal pages.'
);
$s4 = ob_get_clean();

lux_page_section([
    'n' => '04', 'id' => 'privacy',
    't' => $isIt ? 'Privacy, cookie e dati' : 'Privacy, cookies and data',
    'body' => $s4,
]);

// ---------- 05 app ----------
ob_start();
faq_qa(
    $isIt ? 'L\'app è obbligatoria?' : 'Is the app mandatory?',
    $isIt
        ? '<strong>No.</strong> Il sito ha tutto quello che ha l\'app. Installarla è una scelta, non un requisito, e non cambia quello che puoi fare qui.'
        : '<strong>No.</strong> The site has everything the app has. Installing it is a choice, not a requirement, and does not change what you can do here.'
);
faq_qa(
    $isIt ? 'Perché l\'app non è sul Play Store?' : 'Why is the app not on the Play Store?',
    $isIt
        ? 'Perché L.U.X. Verse è un progetto no profit e non può permettersi un account sviluppatore a pagamento. L\'app si scarica come file APK dalla <a href="/app.php">pagina dell\'app</a>.'
        : 'Because L.U.X. Verse is a non-profit project and cannot afford a paid developer account. The app is downloaded as an APK file from the <a href="/app.php">app page</a>.'
);
faq_qa(
    $isIt ? 'Google mi segnala l\'app come sconosciuta: devo preoccuparmi?' : 'Google flags the app as unknown: should I worry?',
    $isIt
        ? 'È normale per qualunque app scaricata fuori dal Play Store. Se durante l\'installazione compare una <strong>verifica</strong>, non ignorarla: <strong>accettala ed esegui la scansione</strong>. Siamo sicuri al 100% che l\'app è sicura e legittima. Se comunque non ti senti tranquillo, <strong>non installarla</strong> e continua dal sito.'
        : 'It is normal for any app downloaded outside the Play Store. If a <strong>verification</strong> appears during installation, do not ignore it: <strong>accept it and run the scan</strong>. We are 100% certain the app is safe and legitimate. If you still do not feel comfortable, <strong>do not install it</strong> and continue from the site.'
);
$s5 = ob_get_clean();

lux_page_section([
    'n' => '05', 'id' => 'app',
    't' => $isIt ? 'L\'app Android' : 'The Android app',
    'body' => $s5,
    'box' => [
        'tone' => 'warn',
        't' => $isIt ? 'La regola più semplice' : 'The simplest rule',
        'd' => $isIt
            ? 'Installa solo se ti senti tranquillo. Nessuno ti obbliga, nessuna funzione del sito si sblocca con l\'app e nessuna funzione si perde senza.'
            : 'Install only if you feel comfortable. Nobody forces you, no site feature unlocks with the app and no feature is lost without it.',
    ],
]);

// ---------- 06 problemi ----------
ob_start();
faq_qa(
    $isIt ? 'Ho trovato un errore: cosa faccio?' : 'I found a bug: what should I do?',
    $isIt
        ? 'Scrivilo in <a href="/assistenza.php">Assistenza</a>. Allega, se puoi, la pagina e il dispositivo: il messaggio arriva con un codice che ti permette di seguire la richiesta. Le correzioni vengono poi raccontate in <a href="/bacheca.php">bacheca</a> e in <a href="/linea-del-tempo.php">linea del tempo</a>.'
        : 'Report it in <a href="/assistenza.php">Support</a>. If you can, include the page and the device: the message arrives with a code that lets you follow the request. Fixes are then described on the <a href="/bacheca.php">board</a> and in the <a href="/linea-del-tempo.php">timeline</a>.'
);
faq_qa(
    $isIt ? 'Il sito sembra vuoto o ha perso lo stile: che succede?' : 'The site looks empty or lost its styling: what is happening?',
    $isIt
        ? 'Quasi sempre è il browser che tiene in memoria una copia vecchia degli stili: un aggiornamento della pagina (Ctrl+F5) lo risolve. Se resta così, segnalacelo in Assistenza: è un dato che ci serve.'
        : 'Almost always the browser is holding an old copy of the styles: refreshing the page (Ctrl+F5) solves it. If it stays like that, report it in Support: it is information we need.'
);
faq_qa(
    $isIt ? 'Prima di scrivere, dove guardo?' : 'Before writing, where do I look?',
    $isIt
        ? 'Nella <a href="/status.php">pagina Stato</a> (misura reale dei servizi), nel <a href="/roadmap.php">percorso del rilascio</a> (cosa è in corso) e in <a href="/bacheca.php">bacheca</a> (cosa abbiamo appena corretto). Spesso il problema è già lì, in lavorazione.'
        : 'The <a href="/status.php">Status page</a> (real measurement of the services), the <a href="/roadmap.php">release path</a> (what is in progress) and the <a href="/bacheca.php">board</a> (what we just fixed). The problem is often already there, being worked on.'
);
$s6 = ob_get_clean();

lux_page_section([
    'n' => '06', 'id' => 'problemi',
    't' => $isIt ? 'Problemi, errori, correzioni' : 'Problems, errors, fixes',
    'body' => $s6,
]);

lux_page_close([
    'next' => [
        ['t' => t('Assistenza'), 'd' => $isIt ? 'Non hai trovato la risposta? Scrivicelo e ti rispondiamo.' : 'Did not find the answer? Write to us and we answer.', 'u' => '/assistenza.php'],
        ['t' => t('Stato'), 'd' => $isIt ? 'Come stanno i servizi, misurato adesso.' : 'How the services are doing, measured now.', 'u' => '/status.php'],
        ['t' => $isIt ? 'Percorso del rilascio' : 'Release roadmap', 'd' => $isIt ? 'Cosa è fatto, cosa è in corso, cosa arriva.' : 'What is done, in progress, coming.', 'u' => '/roadmap.php'],
    ],
]);
lux_footer();
