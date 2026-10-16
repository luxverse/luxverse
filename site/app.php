<?php
// ============================================================
// PAGINA DELL'APP — L.U.X. VERSE 39.1.1
// ============================================================
// Prende il posto dei due avvisi che comparivano da soli (la finestra
// "RIVOLUZIONE SISTEMI" e il banner in alto da minimizzare): adesso c'e'
// una pagina nella barra, e chi vuole l'app ci va di sua volonta'.
//
// Qui stanno i dettagli veri (versione, peso, impronta del file,
// requisiti, permessi) e le avvertenze sull'installazione, compresa
// quella sulla verifica di Google: non essendo sul Play Store, Android
// puo' chiedere una scansione, e la risposta giusta e' accettarla e
// farla. Chi non si sente sicuro non installa niente e continua dal
// sito: l'app non e' obbligatoria.
// ============================================================

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/page.php';
lux_session_start();

$lang = currentLang();
$isIt = ($lang !== 'en');

$apk      = '/LUXVerse.apk';
$appVer   = defined('LUX_APP_VERSION') ? (string)LUX_APP_VERSION : '';
$appCode  = defined('LUX_APP_VERSION_CODE') ? (int)LUX_APP_VERSION_CODE : 0;
$siteVer  = defined('LUX_SITE_VERSION') ? (string)LUX_SITE_VERSION : '';

// Peso e impronta del file che stiamo offrendo: se il file locale c'e',
// si mostrano quelli veri, altrimenti niente numeri inventati.
$apkPath = __DIR__ . '/LUXVerse.apk';
$apkMb   = is_file($apkPath) ? round(filesize($apkPath) / 1048576, 2) : null;
$apkHash = is_file($apkPath) ? hash_file('sha256', $apkPath) : '';

$pageTitle       = $isIt ? 'L\'app — L.U.X. Verse' : 'The app — L.U.X. Verse';
$pageDescription = $isIt
    ? 'L\'app ufficiale L.U.X. Verse per Android: cosa fa, quanto pesa, come si installa, cosa fare se Google chiede una verifica. Gratuita, senza registrazione e senza pubblicità.'
    : 'The official L.U.X. Verse app for Android: what it does, how big it is, how to install it, what to do if Google asks for a verification. Free, no registration, no advertising.';
$pageKeywords    = 'app L.U.X. Verse, APK, Android, installazione, verifica Google, Play Protect, no profit, senza registrazione';
$pageCSS         = '';

$sezioni = [
    ['id' => 'cosa-fa',   'n' => '01', 't' => $isIt ? 'Cos\'è e cosa fa' : 'What it is and what it does'],
    ['id' => 'requisiti', 'n' => '02', 't' => $isIt ? 'Requisiti e peso' : 'Requirements and size'],
    ['id' => 'installare','n' => '03', 't' => $isIt ? 'Come si installa, passo per passo' : 'How to install, step by step'],
    ['id' => 'google',    'n' => '04', 't' => $isIt ? 'Se Google chiede una verifica' : 'If Google asks for a verification'],
    ['id' => 'dettagli',  'n' => '05', 't' => $isIt ? 'Dettagli tecnici e controllo del file' : 'Technical details and file check'],
    ['id' => 'codice',    'n' => '06', 't' => $isIt ? 'Codice aperto e verifica indipendente' : 'Open source and independent verification'],
    ['id' => 'aggiornamenti', 'n' => '07', 't' => $isIt ? 'Aggiornamenti, disinstallazione, dubbi' : 'Updates, uninstall, doubts'],
];

// Il rapporto VirusTotal consegnato dal titolare: e' l'analisi dell'APK
// publicato, cioe' il file che si scarica da questa pagina. Se un giorno si
// pubblica un APK nuovo, su VirusTotal se ne carica una copia e si aggiorna
// questo indirizzo: un rapporto che non corrisponde al file offerto sarebbe
// peggio di nessun rapporto.
$vtUrl   = 'https://www.virustotal.com/gui/file/dfdfbda322e368210505b4e142c434aa1b5ad43b7f1fcd74f95cdcb8ecaacbab/detection';
// Il repository pubblico del progetto: da qui si legge il codice e si prende
// l'APK dalle release. Se un giorno il progetto si sposta, si aggiorna questo
// indirizzo e basta: tutte le sezioni qui sotto lo usano.
$repoUrl = 'https://github.com/luxverse/luxverse';

lux_header();
lux_page_open([
    'crumbs'   => [['t' => $isIt ? 'Home' : 'Home', 'u' => '/index.php'], ['t' => $isIt ? 'App' : 'App']],
    'kicker'   => $isIt ? 'App ufficiale per Android' : 'Official app for Android',
    'title'    => $isIt ? 'L\'app: L.U.X. Verse sul tuo telefono' : 'The app: L.U.X. Verse on your phone',
    'lead'     => $isIt
        ? 'La stessa piattaforma, dentro un\'app: gli stessi centomila strumenti, gli stessi avvisi, sempre allineati al sito perché l\'app legge il sito dal vivo. Nessuna registrazione, nessun costo, nessuna pubblicità.'
        : 'The same platform, inside an app: the same one hundred thousand tools, the same notices, always aligned with the site because the app reads the live site. No registration, no cost, no advertising.',
    'meta'     => [
        $isIt ? 'Versione app ' . $appVer . ' (build ' . $appCode . ')' : 'App version ' . $appVer . ' (build ' . $appCode . ')',
        $isIt ? 'Aggiornata il 16 ottobre 2026' : 'Updated on 16 October 2026',
        $isIt ? 'Gratuita, no profit' : 'Free, non-profit',
    ],
    'actions'  => [
        ['t' => $isIt ? 'Scarica l\'APK' : 'Download the APK', 'u' => $apk, 'p' => true],
        ['t' => $isIt ? 'Leggi le avvertenze' : 'Read the warnings', 'u' => '#google'],
    ],
    'sections' => $sezioni,
]);

lux_page_section([
    'n' => '01', 'id' => 'cosa-fa',
    't' => $isIt ? 'Cos\'è e cosa fa' : 'What it is and what it does',
    'body' => $isIt
        ? '<p>L\'app è una finestra su L.U.X. Verse costruita per il telefono: si apre in un secondo e ti porta dritto a quello che ti serve, senza passare dal browser. Non è una copia del sito scaricata una volta e dimenticata: <strong>l\'app legge il sito dal vivo</strong>, quindi quello che vedi è sempre la versione pubblicata in quel momento.</p>
           <p>Dentro hai: il catalogo completo e la ricerca, la modalità <strong>IA (Veloce)</strong> che esegue lo strumento e ti mostra il risultato, i preferiti, la cronologia, gli avvisi della bacheca e le notifiche globali sullo schermo del telefono.</p>'
        : '<p>The app is a window on L.U.X. Verse built for the phone: it opens in a second and takes you straight to what you need, without going through the browser. It is not a copy of the site downloaded once and forgotten: <strong>the app reads the live site</strong>, so what you see is always the version published at that moment.</p>
           <p>Inside you have: the full catalogue and search, the <strong>AI (Fast)</strong> mode that runs the tool and shows you the result, favourites, history, the board notices and global notifications on your phone screen.</p>',
    'brief' => [
        't' => $isIt ? 'In breve' : 'In short',
        'items' => $isIt
            ? ['Sempre allineata al sito: nessuna copia da aggiornare', 'Nessuna registrazione, nessun account, nessun tracciamento', 'Gratuita e senza pubblicità, come tutto il progetto']
            : ['Always aligned with the site: no copy to update', 'No registration, no account, no tracking', 'Free and without advertising, like the whole project'],
    ],
]);

lux_page_section([
    'n' => '02', 'id' => 'requisiti',
    't' => $isIt ? 'Requisiti e peso' : 'Requirements and size',
    'body' => $isIt
        ? '<ul>
             <li><strong>Android 8.0 o successivo</strong> (la quasi totalità dei telefoni in uso).</li>
             <li><strong>Peso del file: ' . ($apkMb !== null ? htmlspecialchars((string)$apkMb) . ' MB' : 'circa 6 MB') . '</strong>' . ($apkMb !== null ? ', misurato sul file che scarichi da qui' : '') . '. Occupa meno di una foto.</li>
             <li><strong>Connessione a internet</strong>: l\'app lavora online, come il sito, perché legge i contenuti dal vivo.</li>
             <li><strong>Nessun permesso invasivo</strong>: non chiede contatti, posizione, fotocamera, microfono né accesso ai tuoi file.</li>
           </ul>'
        : '<ul>
             <li><strong>Android 8.0 or later</strong> (almost every phone in use).</li>
             <li><strong>File size: ' . ($apkMb !== null ? htmlspecialchars((string)$apkMb) . ' MB' : 'about 6 MB') . '</strong>' . ($apkMb !== null ? ', measured on the file you download from here' : '') . '. Less than one photo.</li>
             <li><strong>An internet connection</strong>: the app works online, like the site, because it reads content live.</li>
             <li><strong>No invasive permissions</strong>: it does not ask for contacts, location, camera, microphone or access to your files.</li>
           </ul>',
]);

lux_page_section([
    'n' => '03', 'id' => 'installare',
    't' => $isIt ? 'Come si installa, passo per passo' : 'How to install, step by step',
    'body' => $isIt
        ? '<ol>
             <li>Premi <strong>Scarica l\'APK</strong> qui sopra. Il file si salva nella cartella dei download.</li>
             <li>Chrome può chiederti se <em>mantenere</em> il file: scegli <strong>Mantieni</strong> (o <em>Scarica comunque</em>).</li>
             <li>Apri il file scaricato. Android chiederà di consentire l\'installazione da questa fonte: scegli <strong>Consenti</strong> o <strong>Installa comunque</strong>, e solo per Chrome.</li>
             <li>Attendi pochi secondi e apri l\'app. Fine: non serve nessun account, la trovi già pronta.</li>
           </ol>
           <p>Se vuoi tornare indietro, si disinstalla come qualunque altra app: tieni premuta l\'icona e scegli <strong>Disinstalla</strong>. Non lascia niente sul telefono.</p>'
        : '<ol>
             <li>Press <strong>Download the APK</strong> above. The file goes to your downloads folder.</li>
             <li>Chrome may ask whether to <em>keep</em> the file: choose <strong>Keep</strong> (or <em>Download anyway</em>).</li>
             <li>Open the downloaded file. Android will ask to allow installation from this source: choose <strong>Allow</strong> or <strong>Install anyway</strong>, for Chrome only.</li>
             <li>Wait a few seconds and open the app. That is it: no account needed, it is ready.</li>
           </ol>
           <p>If you want to go back, it uninstalls like any other app: hold the icon and choose <strong>Uninstall</strong>. It leaves nothing on your phone.</p>',
]);

lux_page_section([
    'n' => '04', 'id' => 'google',
    't' => $isIt ? 'Se Google chiede una verifica' : 'If Google asks for a verification',
    'body' => $isIt
        ? '<p>L.U.X. Verse è un progetto <strong>no profit</strong> e non può permettersi un account sviluppatore su Google Play. Per questo l\'app si scarica da qui come file <code>.apk</code>: è lo stesso modo in cui si scaricano tante applicazioni fuori dal negozio ufficiale.</p>
           <p>Poiché il file arriva da internet e non dal Play Store, Android e Google possono chiedere una <strong>verifica di sicurezza</strong> durante l\'installazione. <strong>Non ignorarla e non aggirarla: accettala ed esegui la scansione.</strong> La verifica controlla il file, e il file è nostro: siamo sicuri al 100% che l\'app è sicura e legittima. Se la scansione chiede qualche secondo in più, è normale: sta lavorando.</p>
           <p>Detto questo, la scelta resta tua. <strong>Se non ti senti sicuro, non scaricare l\'app e continua la tua navigazione sul sito:</strong> il sito ha tutto quello che ha l\'app, solo che si apre nel browser. Installare l\'app <strong>non è obbligatorio</strong> e non cambia niente di quello che puoi fare qui.</p>'
        : '<p>L.U.X. Verse is a <strong>non-profit</strong> project and cannot afford a Google Play developer account. That is why the app is downloaded from here as an <code>.apk</code> file: the same way many applications are downloaded outside the official store.</p>
           <p>Because the file comes from the internet and not from the Play Store, Android and Google may ask for a <strong>security verification</strong> during installation. <strong>Do not ignore it and do not work around it: accept it and run the scan.</strong> The verification checks the file, and the file is ours: we are 100% certain the app is safe and legitimate. If the scan takes a few extra seconds, that is normal: it is working.</p>
           <p>That said, the choice remains yours. <strong>If you do not feel safe, do not download the app and keep browsing the site:</strong> the site has everything the app has, it just opens in the browser. Installing the app is <strong>not mandatory</strong> and changes nothing about what you can do here.</p>',
    'box' => [
        'tone' => 'warn',
        't' => $isIt ? 'Le tre cose da ricordare' : 'The three things to remember',
        'd' => $isIt
            ? '1. Google può segnalare il file come «sconosciuto»: è normale per un\'app che non passa dal Play Store.<br>
               2. Se compare una verifica, <strong>accettala e falla</strong>: serve a proteggerti, non a farti perdere tempo.<br>
               3. Se preferisci non installare, <strong>non installare</strong>: il sito resta completo e gratuito come sempre.'
            : '1. Google may flag the file as "unknown": that is normal for an app that does not come from the Play Store.<br>
               2. If a verification appears, <strong>accept it and let it run</strong>: it protects you, it does not waste your time.<br>
               3. If you prefer not to install, <strong>do not install</strong>: the site stays complete and free as always.',
    ],
]);

lux_page_section([
    'n' => '05', 'id' => 'dettagli',
    't' => $isIt ? 'Dettagli tecnici e controllo del file' : 'Technical details and file check',
    'body' => $isIt
        ? '<p>Questi sono i dati del file che scarichi da questa pagina. Puoi confrontarli con quelli del file che ti ritrovi nel telefono: se combaciano, è esattamente il nostro.</p>'
        : '<p>These are the details of the file you download from this page. You can compare them with the file on your phone: if they match, it is exactly ours.</p>',
    'brief' => [
        't' => $isIt ? 'Scheda tecnica' : 'Technical sheet',
        'items' => [
            ['t' => $isIt ? 'Versione app:' : 'App version:', 'd' => $appVer . ' (build ' . $appCode . ')'],
            ['t' => $isIt ? 'Versione piattaforma:' : 'Platform version:', 'd' => $siteVer],
            ['t' => $isIt ? 'File:' : 'File:', 'd' => 'LUXVerse.apk' . ($apkMb !== null ? ' · ' . $apkMb . ' MB' : '')],
            ['t' => $isIt ? 'Requisito:' : 'Requirement:', 'd' => 'Android 8.0+'],
            ['t' => $isIt ? 'Costo:' : 'Cost:', 'd' => $isIt ? 'zero, per sempre' : 'zero, forever'],
            ['t' => $isIt ? 'Tracciamento:' : 'Tracking:', 'd' => $isIt ? 'nessuno, nessun cookie di tracciamento' : 'none, no tracking cookies'],
        ],
    ],
    'note' => $apkHash !== ''
        ? ($isIt ? 'Impronta SHA-256 del file: <code>' . htmlspecialchars($apkHash) . '</code>' : 'SHA-256 fingerprint of the file: <code>' . htmlspecialchars($apkHash) . '</code>')
        : '',
]);

lux_page_section([
    'n' => '06', 'id' => 'codice',
    't' => $isIt ? 'Codice aperto e verifica indipendente' : 'Open source and independent verification',
    'body' => $isIt
        ? '<p>L\'app di L.U.X. Verse è <strong>open source</strong>: il codice che la compone è pubblico, su <a href="' . $repoUrl . '" rel="noopener" target="_blank">GitHub</a>. Chiunque può leggerlo, controllare cosa fa e compilarlo da solo, senza chiedere niente a noi. Nella stessa pagina trovi anche l\'APK pubblicato come <em>release</em>: è lo stesso file che scarichi da qui, messo a disposizione anche fuori dal nostro sito.</p>
           <p>E non devi crederci sulla parola: <strong>puoi controllare il file da solo</strong>. Il rapporto dell\'APK pubblicato è su <a href="' . $vtUrl . '" rel="noopener" target="_blank">VirusTotal</a>, il servizio che analizza ogni file con decine di antivirus diversi e mostra il risultato a tutti, gratis. Lì vedi se qualcuno ha trovato qualcosa da ridire su questo file: noi lo teniamo d\'occhio proprio per questo.</p>
           <p>Come leggerlo in due passi: apri il rapporto, poi confronta l\'<strong>impronta SHA-256</strong> scritta qui sopra con quella del file che hai scaricato. Se combaciano, il file che hai in mano è esattamente il nostro, e quello che dice l\'analisi vale anche per la tua copia. Se hai scaricato una versione più nuova di quella analizzata, carica pure il file su VirusTotal: ti dà il rapporto della tua copia in pochi secondi.</p>'
        : '<p>The L.U.X. Verse app is <strong>open source</strong>: the code it is made of is public, on <a href="' . $repoUrl . '" rel="noopener" target="_blank">GitHub</a>. Anyone can read it, check what it does and build it themselves, without asking us anything. On the same page you also find the APK published as a <em>release</em>: it is the same file you download from here, made available outside our site as well.</p>
           <p>And you do not have to take our word for it: <strong>you can check the file yourself</strong>. The report of the published APK is on <a href="' . $vtUrl . '" rel="noopener" target="_blank">VirusTotal</a>, the service that analyses every file with dozens of different antivirus engines and shows the result to everyone, for free. There you can see whether anyone found something to object to in this file: we keep an eye on it precisely for that reason.</p>
           <p>How to read it in two steps: open the report, then compare the <strong>SHA-256 fingerprint</strong> written above with the one of the file you downloaded. If they match, the file in your hands is exactly ours, and what the analysis says applies to your copy as well. If you downloaded a version newer than the analysed one, upload the file to VirusTotal yourself: it gives you the report of your copy in a few seconds.</p>',
]);

lux_page_section([
    'n' => '07', 'id' => 'aggiornamenti',
    't' => $isIt ? 'Aggiornamenti, disinstallazione, dubbi' : 'Updates, uninstall, doubts',
    'body' => $isIt
        ? '<p><strong>Come si aggiorna.</strong> Quando esce una versione nuova la trovi su questa pagina: scarichi il file nuovo e lo installi sopra il vecchio, senza perdere niente (l\'app non tiene dati tuoi sul telefono, quindi non c\'è niente da salvare). L\'app stessa ti avvisa quando è disponibile una versione più recente.</p>
           <p>Ogni versione dell\'APK viene pubblicata anche fra le <a href="' . $repoUrl . '/releases" rel="noopener" target="_blank">release su GitHub</a>: se un giorno questo sito non fosse raggiungibile, o se preferisci prendere il file da un posto in cui il codice si può leggere, il file resta lì, con la sua versione e la sua impronta.</p>
           <p><strong>Serve un account?</strong> No. Mai. L\'app non chiede un indirizzo email, non crea un profilo e non ti identifica.</p>
           <p><strong>Se qualcosa non funziona</strong>, la strada più rapida è la pagina <a href="/assistenza.php">Assistenza</a>: scrivici e ti rispondiamo. Per i problemi dell\'app allega la versione che vedi nella scheda tecnica qui sopra.</p>'
        : '<p><strong>How it updates.</strong> When a new version comes out you find it on this page: download the new file and install it over the old one, losing nothing (the app keeps no data of yours on the phone, so there is nothing to save). The app itself tells you when a newer version is available.</p>
           <p>Every version of the APK is published among the <a href="' . $repoUrl . '/releases" rel="noopener" target="_blank">GitHub releases</a> as well: if this site were ever unreachable, or if you prefer to take the file from a place where the code can be read, the file stays there, with its version and its fingerprint.</p>
           <p><strong>Does it need an account?</strong> No. Never. The app asks for no email address, creates no profile and does not identify you.</p>
           <p><strong>If something does not work</strong>, the fastest route is the <a href="/assistenza.php">Support</a> page: write to us and we answer. For app problems, attach the version you see in the technical sheet above.</p>',
]);

lux_page_close([
    'next' => [
        ['t' => $isIt ? 'Assistenza' : 'Support', 'd' => $isIt ? 'Problemi con l\'app o con uno strumento: scrivici e ti rispondiamo.' : 'Problems with the app or a tool: write to us and we answer.', 'u' => '/assistenza.php'],
        ['t' => $isIt ? 'FAQ' : 'FAQ', 'd' => $isIt ? 'Le domande che ci fanno più spesso, con risposte brevi.' : 'The questions we get most often, with short answers.', 'u' => '/faq.php'],
        ['t' => $isIt ? 'Privacy' : 'Privacy', 'd' => $isIt ? 'Cosa raccogliamo (poco) e cosa non facciamo mai.' : 'What we collect (little) and what we never do.', 'u' => '/privacy.php'],
    ],
]);
lux_footer();
