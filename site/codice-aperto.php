<?php
// ============================================================
// PAGINA DEL CODICE APERTO — L.U.X. VERSE
// ============================================================
// La pagina che risponde a una domanda sola: "come faccio a sapere che
// questo posto fa quello che dice?". La risposta e' il codice pubblico:
// dove sta, cosa contiene, cosa NON contiene e come controllarlo da soli.
//
// Non ripete l'impronta dell'APK: quella sta nella pagina dell'app, dove
// viene calcolata sul file vero. Due copie della stessa impronta in due
// pagine diverse e' il modo piu' rapido per avere due impronte diverse.
// ============================================================

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/page.php';
lux_session_start();

$lang = currentLang();
$isIt = ($lang !== 'en');

$siteVer = defined('LUX_SITE_VERSION') ? (string)LUX_SITE_VERSION : '';
$appVer  = defined('LUX_APP_VERSION') ? (string)LUX_APP_VERSION : '';

// Il repository pubblico: un indirizzo solo, usato in tutta la pagina.
$repo    = 'https://github.com/luxverse/luxverse';
$repoApp = $repo . '/tree/main/app';
$repoSit = $repo . '/tree/main/site';
$relUrl  = $repo . '/releases';

$pageTitle       = $isIt ? 'Codice aperto — L.U.X. Verse' : 'Open source — L.U.X. Verse';
$pageDescription = $isIt
    ? 'Il codice di L.U.X. Verse è pubblico e leggibile: dove sta, cosa contiene, cosa non contiene mai, come controllare da solo il file dell\'app e cosa puoi farne con la licenza MIT.'
    : 'The L.U.X. Verse source code is public and readable: where it is, what it contains, what it never contains, how to check the app file yourself and what you can do with the MIT licence.';
$pageKeywords    = 'codice aperto, open source, GitHub, licenza MIT, verifica, impronta SHA-256, compilare app, L.U.X. Verse';
$pageOgType      = 'article';
$pageCSS         = '';

$sezioni = [
    ['id' => 'cosa-significa', 'n' => '01', 't' => $isIt ? 'Cos\'è il codice aperto, qui' : 'What open source means, here'],
    ['id' => 'dove-sta',       'n' => '02', 't' => $isIt ? 'Dove sta il codice e com\'è fatto' : 'Where the code is and how it is made'],
    ['id' => 'cosa-manca',     'n' => '03', 't' => $isIt ? 'Cosa non c\'è, e perché' : 'What is not there, and why'],
    ['id' => 'controllare',    'n' => '04', 't' => $isIt ? 'Come controllare da solo' : 'How to check it yourself'],
    ['id' => 'cosa-puoi',      'n' => '05', 't' => $isIt ? 'Cosa puoi farne' : 'What you can do with it'],
    ['id' => 'contribuire',    'n' => '06', 't' => $isIt ? 'Segnalare un problema, contribuire' : 'Report a problem, contribute'],
    ['id' => 'aggiornamenti',  'n' => '07', 't' => $isIt ? 'Come restare aggiornato' : 'How to stay updated'],
];

lux_header();
lux_page_open([
    'crumbs'   => [['t' => 'Home', 'u' => '/index.php'], ['t' => $isIt ? 'Codice aperto' : 'Open source']],
    'kicker'   => $isIt ? 'Trasparenza, con i fatti' : 'Transparency, with facts',
    'title'    => $isIt ? 'Il codice di L.U.X. Verse è pubblico' : 'The L.U.X. Verse source code is public',
    'lead'     => $isIt
        ? 'Non devi fidarti di noi: puoi leggere il codice che fa funzionare questo posto, controllare cosa fa e compilarti l\'app da solo. Qui trovi dove sta, cosa contiene, cosa non c\'è e come verificarlo in pochi minuti.'
        : 'You do not have to trust us: you can read the code that runs this place, check what it does and build the app yourself. Here you find where it is, what it contains, what is missing and how to verify it in a few minutes.',
    'meta'     => array_values(array_filter([
        $isIt ? 'Aggiornata il 16 ottobre 2026' : 'Updated on 16 October 2026',
        $siteVer !== '' ? ($isIt ? 'Versione piattaforma ' . $siteVer : 'Platform version ' . $siteVer) : '',
        $isIt ? 'Licenza MIT' : 'MIT licence',
    ])),
    'actions'  => [
        ['t' => $isIt ? 'Apri il repository' : 'Open the repository', 'u' => $repo, 'p' => true],
        ['t' => $isIt ? 'Vedi le versioni pubblicate' : 'See the published releases', 'u' => $relUrl],
    ],
    'sections' => $sezioni,
]);

lux_page_section([
    'n' => '01', 'id' => 'cosa-significa',
    't' => $isIt ? 'Cos\'è il codice aperto, qui' : 'What open source means, here',
    'body' => $isIt
        ? '<p>L.U.X. Verse è un progetto a <strong>codice aperto</strong>: il codice che lo fa funzionare — il sito e l\'app Android — è pubblico e leggibile da chiunque, senza chiedere permesso a noi. Non è una dichiarazione di principio: è un indirizzo internet dove il codice sta davvero, con la data di ogni modifica.</p>
           <p><strong>Perché lo facciamo.</strong> Un sito che ti chiede di scaricare un file, o che custodisce i risultati che salvi, ha un problema di fiducia: come fai a sapere che fa quello che dice? Ha due risposte possibili. La prima è «fidati». La seconda è <em>leggere</em>. Abbiamo scelto la seconda, perché la prima non si può verificare.</p>
           <p><strong>Cosa cambia per te.</strong> Puoi vedere con i tuoi occhi cosa raccogliamo e cosa no, come si comporta l\'app, a chi vengono mandati i testi quando usi l\'IA. E se una risposta non ti basta, nessuno ti vieta di compilarti l\'app da solo: la chiave per farlo è lì, nella pagina del codice.</p>
           <p><strong>A che prezzo.</strong> Nessuno. Il codice è pubblicato con licenza <strong>MIT</strong>, la più permissiva che esista: puoi usarlo, modificarlo, ridistribuirlo, anche dentro un progetto tuo o commerciale, tenendo soltanto la nota di copyright. Il progetto resta gratuito e senza registrazione come prima.</p>'
        : '<p>L.U.X. Verse is an <strong>open source</strong> project: the code that runs it — the website and the Android app — is public and readable by anyone, without asking us. It is not a statement of principle: it is an internet address where the code actually sits, with the date of every change.</p>
           <p><strong>Why we do it.</strong> A site that asks you to download a file, or that keeps the results you save, has a trust problem: how do you know it does what it says? There are two possible answers. The first is "trust us". The second is <em>to read</em>. We chose the second, because the first cannot be verified.</p>
           <p><strong>What changes for you.</strong> You can see with your own eyes what we collect and what we do not, how the app behaves, who receives the text when you use the AI. And if an answer is not enough for you, nothing stops you from building the app yourself: the key to doing it is right there, on the code page.</p>
           <p><strong>At what price.</strong> None. The code is published under the <strong>MIT</strong> licence, the most permissive there is: you can use it, change it, redistribute it, even inside your own or a commercial project, keeping only the copyright notice. The project stays free and registration-free as before.</p>',
    'brief' => [
        't' => $isIt ? 'In una riga' : 'In one line',
        'items' => $isIt
            ? ['Il codice è di tutti, i dati e i segreti non lo sono', 'Licenza MIT: usare, modificare, ridistribuire', 'Nessuna condizione per guardare: non serve un account']
            : ['The code belongs to everyone, the data and secrets do not', 'MIT licence: use, change, redistribute', 'No conditions to look: no account needed'],
    ],
]);

lux_page_section([
    'n' => '02', 'id' => 'dove-sta',
    't' => $isIt ? 'Dove sta il codice e com\'è fatto' : 'Where the code is and how it is made',
    'body' => $isIt
        ? '<p>Il codice sta su GitHub, in un repository pubblico: <a href="' . $repo . '" rel="noopener" target="_blank">github.com/luxverse/luxverse</a>. Puoi leggerlo dal browser, senza installare niente e senza avere un account.</p>
           <p>Dentro ci sono tre cose, e sono tutto quello che serve a questo posto:</p>
           <ul>
             <li><strong><a href="' . $repoApp . '" rel="noopener" target="_blank">app/</a></strong> — l\'app Android, scritta in Kotlin: una finestra sul sito dal vivo, più la schermata di avvio, il controllo degli aggiornamenti e la pagina che compare quando manca la rete.</li>
             <li><strong><a href="' . $repoSit . '" rel="noopener" target="_blank">site/</a></strong> — il sito, scritto in PHP 8: gli strumenti, gli avvisi, lo spazio personale, le pagine che stai leggendo. Nessun database e nessun framework: i dati stanno in file JSON leggibili.</li>
             <li><strong>release/</strong> — l\'APK pronto per essere allegato a una versione pubblicata, con la sua impronta. Il file compilato non sta nel codice: appesantirebbe ogni modifica futura.</li>
           </ul>
           <p>Nella pagina principale del repository trovi il <a href="' . $repo . '/blob/main/README.md" rel="noopener" target="_blank">README</a> con le istruzioni per compilare l\'app, la <a href="' . $repo . '/blob/main/LICENSE" rel="noopener" target="_blank">licenza</a> per esteso e il <a href="' . $relUrl . '" rel="noopener" target="_blank">pubblico delle versioni</a>.</p>'
        : '<p>The code lives on GitHub, in a public repository: <a href="' . $repo . '" rel="noopener" target="_blank">github.com/luxverse/luxverse</a>. You can read it from the browser, without installing anything and without having an account.</p>
           <p>Inside there are three things, and they are everything this place needs:</p>
           <ul>
             <li><strong><a href="' . $repoApp . '" rel="noopener" target="_blank">app/</a></strong> — the Android app, written in Kotlin: a window on the live site, plus the splash screen, the update check and the page shown when the network is missing.</li>
             <li><strong><a href="' . $repoSit . '" rel="noopener" target="_blank">site/</a></strong> — the website, written in PHP 8: the tools, the notices, the personal space, the pages you are reading. No database and no framework: the data sits in readable JSON files.</li>
             <li><strong>release/</strong> — the APK ready to be attached to a published version, with its fingerprint. The compiled file is not in the code: it would weigh down every future change.</li>
           </ul>
           <p>On the repository main page you find the <a href="' . $repo . '/blob/main/README.md" rel="noopener" target="_blank">README</a> with the instructions to build the app, the <a href="' . $repo . '/blob/main/LICENSE" rel="noopener" target="_blank">licence</a> in full and the <a href="' . $relUrl . '" rel="noopener" target="_blank">list of published versions</a>.</p>',
    'brief' => [
        't' => $isIt ? 'Struttura del repository' : 'Repository layout',
        'items' => [
            ['t' => 'app/', 'd' => $isIt ? 'l\'app Android in Kotlin (Android Studio)' : 'the Android app in Kotlin (Android Studio)'],
            ['t' => 'site/', 'd' => $isIt ? 'il sito in PHP 8, dati in file JSON, nessun database' : 'the site in PHP 8, data in JSON files, no database'],
            ['t' => 'release/', 'd' => $isIt ? 'l\'APK da allegare a una versione pubblicata' : 'the APK to attach to a published version'],
        ],
    ],
]);

lux_page_section([
    'n' => '03', 'id' => 'cosa-manca',
    't' => $isIt ? 'Cosa non c\'è, e perché' : 'What is not there, and why',
    'body' => $isIt
        ? '<p>Un repository a codice aperto è credibile anche per quello che <strong>non</strong> contiene. Qui non ci sono, per scelta:</p>
           <ul>
             <li><strong>La cassaforte dei segreti</strong> — le chiavi delle API di intelligenza artificiale, il token interno e l\'impronta della password di amministrazione. Se fossero pubbliche, chiunque potrebbe usare le nostre chiavi o entrare nel pannello.</li>
             <li><strong>Le credenziali di pubblicazione sul server</strong> — servono a caricare i file sul sito: sono nostre e restano nostre.</li>
             <li><strong>La chiave privata con cui firmiamo l\'app</strong> — se uscisse, chiunque potrebbe firmare un\'app che si spaccia per la nostra, e gli aggiornamenti diventerebbero un rischio invece di una comodità.</li>
             <li><strong>Quello che scrivono le persone</strong> — visite, registro attività, messaggi, chat, richieste di assistenza, codici personali: sono dati di chi usa il sito, non codice del progetto.</li>
             <li><strong>L\'indirizzo IP di casa di chi gestisce il sito</strong> — nella lista usata per non contare le visite dello staff. È un dato personale e a chi legge il codice non serve: nel file c\'è il commento che spiega dove rimettere il proprio.</li>
           </ul>
           <p>Come funziona la protezione: c\'è un file, nel repository, che elenca tutto quello che non deve mai essere pubblicato. Se un giorno uno di quei file finisse nella cartella per sbaglio, verrebbe ignorato invece che caricato. È una rete di sicurezza, non una promessa.</p>'
        : '<p>An open source repository is credible also for what it does <strong>not</strong> contain. Here, by choice, there is none of the following:</p>
           <ul>
             <li><strong>The secrets vault</strong> — the artificial intelligence API keys, the internal token and the fingerprint of the administration password. If they were public, anyone could use our keys or get into the panel.</li>
             <li><strong>The publishing credentials for the server</strong> — they are needed to upload files to the site: they are ours and stay ours.</li>
             <li><strong>The private key we sign the app with</strong> — if it leaked, anyone could sign an app pretending to be ours, and updates would become a risk instead of a convenience.</li>
             <li><strong>What people write</strong> — visits, activity log, messages, chats, support requests, personal codes: they are the data of whoever uses the site, not project code.</li>
             <li><strong>The home IP address of whoever runs the site</strong> — in the list used not to count the staff\'s own visits. It is personal data and useless to anyone reading the code: the file carries the comment explaining where to put your own back.</li>
           </ul>
           <p>How the protection works: there is a file in the repository listing everything that must never be published. If one of those files ever ended up in the folder by mistake, it would be ignored instead of uploaded. It is a safety net, not a promise.</p>',
    'box' => [
        'tone' => 'warn',
        't' => $isIt ? 'Il confine, in una riga' : 'The boundary, in one line',
        'd' => $isIt
            ? 'Il codice è di tutti. I segreti e i dati delle persone restano di chi li ha scritti.'
            : 'The code belongs to everyone. Secrets and personal data stay with whoever they belong to.',
    ],
]);

lux_page_section([
    'n' => '04', 'id' => 'controllare',
    't' => $isIt ? 'Come controllare da solo' : 'How to check it yourself',
    'body' => $isIt
        ? '<p>Leggere il codice è la verifica più forte, ma richiede tempo. Se ne hai pochi, queste tre bastano a capire se il file che hai scaricato è davvero il nostro.</p>
           <ol>
             <li><strong>Leggi la pagina dell\'app</strong> (<a href="/app.php#dettagli">/app.php</a>): lì trovi versione, peso e <strong>impronta SHA-256</strong> del file offerto, calcolata sul file vero, non copiata a mano.</li>
             <li><strong>Confronta l\'impronta</strong> con quella del file che hai nel telefono: su Windows con <code>certutil -hashfile LUXVerse.apk SHA256</code>, su Linux con <code>sha256sum LUXVerse.apk</code>. Se combaciano carattere per carattere, è lo stesso file.</li>
             <li><strong>Prendi il file da una seconda fonte</strong>: le <a href="' . $relUrl . '" rel="noopener" target="_blank">versioni pubblicate</a> contengono lo stesso APK, scaricabile anche fuori dal nostro sito. Due strade indipendenti che portano allo stesso file sono una prova, una sola strada è una dichiarazione.</li>
           </ol>
           <p>Nella pagina dell\'app trovi anche il collegamento al rapporto pubblico di analisi antivirus: decine di motori diversi, risultato visibile a tutti. Nessun file scaricato da internet è «sicuro per definizione», ma un rapporto scritto da altri si può controllare, la nostra parola no.</p>'
        : '<p>Reading the code is the strongest verification, but it takes time. If you have little, these three are enough to tell whether the file you downloaded is really ours.</p>
           <ol>
             <li><strong>Read the app page</strong> (<a href="/app.php#dettagli">/app.php</a>): there you find the version, the size and the <strong>SHA-256 fingerprint</strong> of the offered file, computed on the real file, not copied by hand.</li>
             <li><strong>Compare the fingerprint</strong> with the one of the file on your phone: on Windows with <code>certutil -hashfile LUXVerse.apk SHA256</code>, on Linux with <code>sha256sum LUXVerse.apk</code>. If they match character by character, it is the same file.</li>
             <li><strong>Take the file from a second source</strong>: the <a href="' . $relUrl . '" rel="noopener" target="_blank">published versions</a> hold the same APK, downloadable outside our site too. Two independent roads leading to the same file are proof; a single road is only a statement.</li>
           </ol>
           <p>On the app page you also find the link to the public antivirus analysis report: dozens of different engines, result visible to everyone. No file downloaded from the internet is "safe by definition", but a report written by others can be checked — our word cannot.</p>',
]);

lux_page_section([
    'n' => '05', 'id' => 'cosa-puoi',
    't' => $isIt ? 'Cosa puoi farne' : 'What you can do with it',
    'body' => $isIt
        ? '<p>La licenza <strong>MIT</strong> ti permette, senza chiedere niente a nessuno:</p>
           <ul>
             <li><strong>leggere</strong> ogni riga, per capire cosa fa il sito o l\'app prima di usarla;</li>
             <li><strong>compilare l\'app da solo</strong>, con Android Studio o da riga di comando (JDK 17, istruzioni nel README): l\'APK che ottieni è tuo;</li>
             <li><strong>modificare il codice</strong> — cambiare un testo, togliere uno strumento, aggiungerne uno — e usare la tua versione;</li>
             <li><strong>ridistribuire</strong> il tutto, anche dentro un progetto commerciale, tenendo la nota di copyright e la licenza.</li>
           </ul>
           <p><strong>Due avvertenze oneste.</strong> La prima: se compili l\'app da solo, il tuo file non potrà sostituire quello installato dal nostro, perché Android accetta un aggiornamento solo se è firmato con la stessa chiave. Per provarla devi prima disinstallare l\'app ufficiale. La seconda: la chiave di firma e il marchio L.U.X. Verse restano nostri — puoi fare la tua versione, non puoi presentarla come se fosse la nostra.</p>'
        : '<p>The <strong>MIT</strong> licence lets you, without asking anyone:</p>
           <ul>
             <li><strong>read</strong> every line, to understand what the site or the app does before using it;</li>
             <li><strong>build the app yourself</strong>, with Android Studio or from the command line (JDK 17, instructions in the README): the APK you get is yours;</li>
             <li><strong>change the code</strong> — alter a text, remove a tool, add one — and use your own version;</li>
             <li><strong>redistribute</strong> the whole thing, even inside a commercial project, keeping the copyright notice and the licence.</li>
           </ul>
           <p><strong>Two honest warnings.</strong> The first: if you build the app yourself, your file cannot replace the one we installed, because Android accepts an update only when it is signed with the same key. To try it you must uninstall the official app first. The second: the signing key and the L.U.X. Verse name stay ours — you can make your own version, you cannot present it as if it were ours.</p>',
]);

lux_page_section([
    'n' => '06', 'id' => 'contribuire',
    't' => $isIt ? 'Segnalare un problema, contribuire' : 'Report a problem, contribute',
    'body' => $isIt
        ? '<p>Ci sono tre strade, e servono a cose diverse.</p>
           <ul>
             <li><strong>Un difetto di sicurezza</strong> → <a href="' . $repo . '/blob/main/SECURITY.md" rel="noopener" target="_blank">SECURITY.md</a>: si scrive in privato, non in una segnalazione pubblica. Ti chiediamo solo di non provare niente su dati o utenti veri.</li>
             <li><strong>Un errore, una pagina rotta, un\'idea</strong> → le <a href="' . $repo . '/issues" rel="noopener" target="_blank">segnalazioni del repository</a>, oppure la pagina <a href="/assistenza.php">Assistenza</a> di questo sito, se preferisci scrivere in italiano e non serve un account GitHub.</li>
             <li><strong>Del codice da proporre</strong> → <a href="' . $repo . '/blob/main/CONTRIBUTING.md" rel="noopener" target="_blank">CONTRIBUTING.md</a>: il progetto è piccolo e a conduzione familiare, quindi prima di scrivere molto codice conviene parlarne. Meno lavoro sprecato per tutti.</li>
           </ul>
           <p>Una nota che vale la pena sapere: a ogni modifica del codice parte da sola una compilazione di prova dell\'app. Non firma e non pubblica niente, ma dice subito se quello che è stato scritto compila: è il modo più semplice per evitare che un errore arrivi fino a te.</p>'
        : '<p>There are three roads, and they serve different things.</p>
           <ul>
             <li><strong>A security flaw</strong> → <a href="' . $repo . '/blob/main/SECURITY.md" rel="noopener" target="_blank">SECURITY.md</a>: write privately, not in a public report. We only ask you not to try anything on real data or users.</li>
             <li><strong>A bug, a broken page, an idea</strong> → the <a href="' . $repo . '/issues" rel="noopener" target="_blank">repository issues</a>, or the <a href="/assistenza.php">Support</a> page of this site, if you prefer writing in Italian without a GitHub account.</li>
             <li><strong>Code to propose</strong> → <a href="' . $repo . '/blob/main/CONTRIBUTING.md" rel="noopener" target="_blank">CONTRIBUTING.md</a>: this is a small, family-run project, so before writing a lot of code it is worth talking it through. Less wasted work for everyone.</li>
           </ul>
           <p>One thing worth knowing: on every change to the code a test build of the app starts by itself. It signs nothing and publishes nothing, but it says at once whether what was written compiles: the simplest way to keep a mistake from reaching you.</p>',
]);

lux_page_section([
    'n' => '07', 'id' => 'aggiornamenti',
    't' => $isIt ? 'Come restare aggiornato' : 'How to stay updated',
    'body' => $isIt
        ? '<p>Il codice non si muove di nascosto: ogni modifica è datata e leggibile.</p>
           <ul>
             <li><strong>Le versioni pubblicate</strong> (<a href="' . $relUrl . '" rel="noopener" target="_blank">' . $relUrl . '</a>): ogni versione dell\'app con il file allegato e la sua impronta. Quando ne esce una nuova, la trovi qui e nella <a href="/app.php">pagina dell\'app</a>.</li>
             <li><strong>La cronologia delle modifiche</strong> (<a href="' . $repo . '/commits/main" rel="noopener" target="_blank">commit</a>): cosa è cambiato e quando, con le parole di chi l\'ha fatto. È il registro più onesto che esista, perché non si può riscrivere senza lasciare traccia.</li>
             <li><strong>La <a href="/bacheca.php">bacheca</a> qui sul sito</strong>: le stesse cose raccontate in italiano, con il motivo delle scelte. È il posto giusto se non vuoi leggere il codice.</li>
             <li><strong>Il <a href="/roadmap.php">percorso del rilascio</a></strong>: cosa stiamo costruendo adesso e cosa arriverà dopo, in ordine.</li>
           </ul>'
        : '<p>The code does not move in secret: every change is dated and readable.</p>
           <ul>
             <li><strong>The published versions</strong> (<a href="' . $relUrl . '" rel="noopener" target="_blank">' . $relUrl . '</a>): every app version with the attached file and its fingerprint. When a new one comes out, you find it here and on the <a href="/app.php">app page</a>.</li>
             <li><strong>The history of changes</strong> (<a href="' . $repo . '/commits/main" rel="noopener" target="_blank">commits</a>): what changed and when, in the words of whoever did it. It is the most honest log there is, because it cannot be rewritten without leaving a trace.</li>
             <li><strong>The <a href="/bacheca.php">board</a> on this site</strong>: the same things told in plain words, with the reason behind each choice. The right place if you do not want to read code.</li>
             <li><strong>The <a href="/roadmap.php">release path</a></strong>: what we are building right now and what comes next, in order.</li>
           </ul>',
]);

lux_page_close([
    'next' => [
        ['t' => $isIt ? 'L\'app per Android' : 'The Android app', 'd' => $isIt ? 'Come si scarica l\'APK, quanto pesa e come si controlla il file.' : 'How to download the APK, how big it is and how to check the file.', 'u' => '/app.php'],
        ['t' => $isIt ? 'Percorso del rilascio' : 'Release roadmap', 'd' => $isIt ? 'Cosa stiamo costruendo adesso, in chiaro, senza promesse a metà.' : 'What we are building right now, plainly, without half promises.', 'u' => '/roadmap.php'],
        ['t' => $isIt ? 'Assistenza' : 'Support', 'd' => $isIt ? 'Una cosa non funziona come deve? Scrivi e ti rispondiamo.' : 'Something not working as it should? Write and we answer.', 'u' => '/assistenza.php'],
    ],
]);
lux_footer();
