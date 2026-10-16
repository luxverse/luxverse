<?php
// ============================================================
// TERMINI DI SERVIZIO — L.U.X. VERSE 39.1.1
// ============================================================
// Estesi come la Privacy: entrano il motore IA, lo spazio personale
// senza account, i risultati condivisi, le API con chiave anonima,
// l'app Android (APK, verifica Google, aggiornamenti) e i limiti di
// velocita'. La regola di fondo: il servizio e' gratuito e no profit,
// quindi si usa con buon senso e senza garanzie di continuita'.
//
// Ultima modifica: 15 ottobre 2026 (versione 39.1.1).
// ============================================================

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/page.php';
lux_session_start();

$lang = currentLang();
$isIt = ($lang === 'it');

$email   = 'administration.luxverse@gmail.com';
$siteVer = defined('LUX_SITE_VERSION') ? (string)LUX_SITE_VERSION : '';
$appVer  = defined('LUX_APP_VERSION') ? (string)LUX_APP_VERSION : '';

$pageTitle       = $isIt ? 'Termini di Servizio — L.U.X. Verse' : 'Terms of Service — L.U.X. Verse';
$pageDescription = $isIt
    ? 'Termini di Servizio di L.U.X. Verse: uso consentito, contenuti generati dall\'IA, spazio personale, risultati condivisi, API, app Android e limiti di responsabilità. Aggiornati al 15 ottobre 2026.'
    : 'L.U.X. Verse Terms of Service: permitted use, AI-generated content, personal space, shared results, APIs, the Android app and limits of liability. Updated on 15 October 2026.';
$pageKeywords    = 'termini di servizio, condizioni d\'uso, L.U.X. Verse, licenza, responsabilità';
$pageCSS         = '';

$sezioni = [
    ['id' => 'accettazione', 'n' => '01', 't' => $isIt ? 'Che cosa accetti usando il sito' : 'What you accept by using the site'],
    ['id' => 'servizio',     'n' => '02', 't' => $isIt ? 'Cos\'è il servizio' : 'What the service is'],
    ['id' => 'gratis',       'n' => '03', 't' => $isIt ? 'Gratuito, senza account, no profit' : 'Free, no account, non-profit'],
    ['id' => 'uso',          'n' => '04', 't' => $isIt ? 'Uso consentito e uso vietato' : 'Permitted and prohibited use'],
    ['id' => 'ia',           'n' => '05', 't' => $isIt ? 'Contenuti generati dall\'IA' : 'AI-generated content'],
    ['id' => 'tuoi',         'n' => '06', 't' => $isIt ? 'Quello che scrivi resta tuo' : 'What you write stays yours'],
    ['id' => 'spazio',       'n' => '07', 't' => $isIt ? 'Il mio spazio e il codice personale' : 'My space and the personal code'],
    ['id' => 'condivisi',    'n' => '08', 't' => $isIt ? 'Risultati salvati e condivisi' : 'Saved and shared results'],
    ['id' => 'api',          'n' => '09', 't' => $isIt ? 'API e chiavi anonime' : 'APIs and anonymous keys'],
    ['id' => 'limiti-tecnici','n' => '10', 't' => $isIt ? 'Limiti d\'uso e velocità' : 'Usage and rate limits'],
    ['id' => 'app',          'n' => '11', 't' => $isIt ? 'App Android: APK, verifica, aggiornamenti' : 'Android app: APK, verification, updates'],
    ['id' => 'disponibile',  'n' => '12', 't' => $isIt ? 'Disponibilità e manutenzione' : 'Availability and maintenance'],
    ['id' => 'responsabilita','n' => '13', 't' => $isIt ? 'Limiti di responsabilità' : 'Limits of liability'],
    ['id' => 'sospensione',  'n' => '14', 't' => $isIt ? 'Sospensione e chiusura' : 'Suspension and closure'],
    ['id' => 'modifiche',    'n' => '15', 't' => $isIt ? 'Modifiche a questi termini' : 'Changes to these terms'],
    ['id' => 'legge',        'n' => '16', 't' => $isIt ? 'Legge applicabile e contatti' : 'Applicable law and contacts'],
];

lux_header();
lux_page_open([
    'crumbs'   => [['t' => 'Home', 'u' => '/index.php'], ['t' => 'Termini di Servizio']],
    'kicker'   => $isIt ? 'Legale' : 'Legal',
    'title'    => $isIt ? 'Termini di Servizio' : 'Terms of Service',
    'lead'     => $isIt
        ? 'Le regole con cui L.U.X. Verse viene offerto: che cosa puoi fare, che cosa non è consentito, cosa ci si può aspettare e che cosa no. Sono poche regole, scritte per essere lette davvero.'
        : 'The rules under which L.U.X. Verse is offered: what you can do, what is not allowed, what you can expect and what you cannot. They are few rules, written to be actually read.',
    'meta'     => [
        $isIt ? 'Ultima modifica: 15 ottobre 2026' : 'Last updated: 15 October 2026',
        ($isIt ? 'Versione ' : 'Version ') . $siteVer,
        $isIt ? 'Servizio gratuito, progetto no profit' : 'Free service, non-profit project',
    ],
    'actions'  => [
        ['t' => $isIt ? 'Privacy Policy' : 'Privacy Policy', 'u' => '/privacy.php', 'p' => true],
        ['t' => $isIt ? 'Domande frequenti' : 'FAQ', 'u' => '/faq.php'],
    ],
    'sections' => $sezioni,
]);

lux_page_section([
    'n' => '01', 'id' => 'accettazione',
    't' => $isIt ? 'Che cosa accetti usando il sito' : 'What you accept by using the site',
    'body' => $isIt
        ? '<p>Usando L.U.X. Verse — il sito, gli strumenti, le API, le guide, la bacheca e l\'app — accetti questi Termini di Servizio e l\'<a href="/privacy.php">Informativa Privacy</a>. Se qualcosa non ti convince, la soluzione è semplice: non usare il servizio.</p>
           <p>Il sito è aperto a tutti: non serve registrarsi, non serve un\'email, non serve accettare nulla di nascosto. L\'accettazione nasce dal semplice uso.</p>'
        : '<p>By using L.U.X. Verse — the site, the tools, the APIs, the guides, the board and the app — you accept these Terms of Service and the <a href="/privacy.php">Privacy Policy</a>. If something does not convince you, the solution is simple: do not use the service.</p>
           <p>The site is open to everyone: no registration, no email, nothing hidden to accept. Acceptance arises from use alone.</p>',
]);

lux_page_section([
    'n' => '02', 'id' => 'servizio',
    't' => $isIt ? 'Cos\'è il servizio' : 'What the service is',
    'body' => $isIt
        ? '<p>L.U.X. Verse è una piattaforma gratuita di strumenti: un catalogo di strumenti istantanei che funzionano nel browser, un motore che capisce una richiesta scritta in linguaggio normale e la collega agli strumenti giusti, guide, una bacheca con gli avvisi e il racconto di com\'è nato il progetto.</p>
           <p>Il servizio è offerto <strong>così com\'è</strong> (<em>as is</em>): nessuno garantisce che ogni singolo strumento sia adatto al tuo caso specifico, né che il risultato sia esente da errori. È uno strumento di lavoro, non un oracolo: il controllo finale resta a te.</p>'
        : '<p>L.U.X. Verse is a free tools platform: a catalogue of instant tools that run in the browser, an engine that understands a request written in plain language and links it to the right tools, guides, a board with notices and the story of how the project started.</p>
           <p>The service is offered <strong>as is</strong>: nobody guarantees that every single tool suits your specific case, nor that the result is free of errors. It is a work tool, not an oracle: the final check remains yours.</p>',
    'brief' => [
        't' => $isIt ? 'In breve' : 'In short',
        'items' => $isIt
            ? ['Strumenti istantanei: girano nel tuo browser, nessun invio.', 'Motore: capisce la richiesta in italiano o inglese e sceglie gli strumenti.', 'Guide, bacheca, notifiche: per capire cosa è cambiato e quando.', 'App Android: la stessa piattaforma, dentro un\'app.']
            : ['Instant tools: they run in your browser, nothing is sent.', 'Engine: it understands the request in Italian or English and picks the tools.', 'Guides, board, notifications: to see what changed and when.', 'Android app: the same platform, inside an app.'],
    ],
]);

lux_page_section([
    'n' => '03', 'id' => 'gratis',
    't' => $isIt ? 'Gratuito, senza account, no profit' : 'Free, no account, non-profit',
    'body' => $isIt
        ? '<p>Il servizio è <strong>completamente gratuito</strong> e non ha pubblicità. Non esiste una versione a pagamento, non esistono abbonamenti, non ci sono acquisti dentro l\'app e non chiediamo soldi in nessun modo.</p>
           <p>Il progetto è <strong>no profit</strong>: non ha partita IVA, non è una società e non genera ricavi. Questo ha una conseguenza concreta e visibile: non ci sono budget, quindi niente account sviluppatore a pagamento sugli store, niente servizi costosi, e il servizio può fermarsi per manutenzione senza preavviso (punto 12).</p>'
        : '<p>The service is <strong>completely free</strong> and has no advertising. There is no paid version, no subscriptions, no in-app purchases and we ask for money in no way.</p>
           <p>The project is <strong>non-profit</strong>: it has no VAT number, it is not a company and it generates no income. This has a concrete, visible consequence: there are no budgets, so no paid developer account in the stores, no expensive services, and the service may pause for maintenance without notice (section 12).</p>',
]);

lux_page_section([
    'n' => '04', 'id' => 'uso',
    't' => $isIt ? 'Uso consentito e uso vietato' : 'Permitted and prohibited use',
    'body' => $isIt
        ? '<h3>Puoi</h3>
           <ul>
             <li>usare gli strumenti per lavoro, studio, casa e tempo libero;</li>
             <li>usare il risultato come vuoi, anche per fini personali o professionali;</li>
             <li>condividere il link di un risultato o di una guida con chi vuoi;</li>
             <li>usare le API con la chiave anonima dal tuo progetto.</li>
           </ul>
           <h3>Non puoi</h3>
           <ul>
             <li>usare il servizio per attività illegali, per molestare, minacciare o discriminare qualcuno, o per creare contenuti che danneggino altre persone;</li>
             <li>caricare o far generare contenuti che violano diritti di altri (opere protette, dati personali di terzi, segreti industriali);</li>
             <li>scaricare in massa il catalogo, «grattare» il sito con robot, o rivendere il servizio o le sue API come se fossero tuoi;</li>
             <li>aggirare i limiti di velocità e le protezioni, tentare di entrare nelle aree riservate o interrompere il funzionamento del sito (compresi attacchi di qualunque tipo);</li>
             <li>usare il nome L.U.X. Verse e il suo logo in modo da far credere che il tuo progetto sia quello ufficiale.</li>
           </ul>
           <p>Chi non rispetta queste regole può perdere l\'accesso alle API o al servizio, senza obbligo di preavviso quando il danno è in corso.</p>'
        : '<h3>You may</h3>
           <ul>
             <li>use the tools for work, study, home and leisure;</li>
             <li>use the result as you like, including for personal or professional purposes;</li>
             <li>share the link of a result or a guide with anyone;</li>
             <li>use the APIs with the anonymous key from your project.</li>
           </ul>
           <h3>You may not</h3>
           <ul>
             <li>use the service for illegal activities, to harass, threaten or discriminate against anyone, or to create content that harms other people;</li>
             <li>upload or generate content that violates other people\'s rights (protected works, third-party personal data, trade secrets);</li>
             <li>mass-download the catalogue, scrape the site with bots, or resell the service or its APIs as if they were yours;</li>
             <li>circumvent rate limits and protections, attempt to enter restricted areas or disrupt the site (including attacks of any kind);</li>
             <li>use the L.U.X. Verse name and logo in a way that suggests your project is the official one.</li>
           </ul>
           <p>Those who do not respect these rules may lose access to the APIs or the service, without notice when the damage is ongoing.</p>',
]);

lux_page_section([
    'n' => '05', 'id' => 'ia',
    't' => $isIt ? 'Contenuti generati dall\'IA' : 'AI-generated content',
    'body' => $isIt
        ? '<p>Una parte degli strumenti e la modalità <strong>IA (Veloce)</strong> producono il risultato con un modello di intelligenza artificiale. Questi risultati possono contenere <strong>errori, imprecisioni, cose inventate o superate</strong>: è il modo di funzionare di questi sistemi, e va tenuto presente.</p>
           <p>Di conseguenza <strong>non</strong> sono consulenza medica, legale, fiscale, finanziaria, psicologica o di sicurezza, e non vanno usati come se lo fossero. Non prendere decisioni importanti basandoti solo su una risposta generata: verifica con una fonte affidabile o con un professionista.</p>
           <p>Usi il risultato sotto la tua responsabilità. Il sito non garantisce l\'accuratezza, la completezza, l\'aggiornamento né l\'idoneità a uno scopo particolare di ciò che l\'IA produce, e non risponde delle conseguenze dell\'uso che ne fai.</p>'
        : '<p>Some tools and the <strong>AI (Fast)</strong> mode produce the result with an artificial intelligence model. Such results may contain <strong>errors, inaccuracies, invented or outdated things</strong>: that is how these systems work, and it must be kept in mind.</p>
           <p>Consequently they are <strong>not</strong> medical, legal, tax, financial, psychological or security advice, and must not be used as if they were. Do not make important decisions based only on a generated answer: verify with a reliable source or a professional.</p>
           <p>You use the result at your own responsibility. The site guarantees no accuracy, completeness, timeliness or fitness for a particular purpose of what the AI produces, and is not liable for the consequences of the use you make of it.</p>',
    'box' => ['tone' => 'warn', 't' => $isIt ? 'Attenzione' : 'Careful', 'd' => $isIt
        ? 'Non inviare all\'IA dati personali, sanitari, documenti o informazioni riservate: la richiesta viene trasmessa a un fornitore esterno per produrre la risposta.'
        : 'Do not send personal, health, document or confidential information to the AI: the request is transmitted to an external provider to produce the answer.'],
]);

lux_page_section([
    'n' => '06', 'id' => 'tuoi',
    't' => $isIt ? 'Quello che scrivi resta tuo' : 'What you write stays yours',
    'body' => $isIt
        ? '<p>Il testo che scrivi è tuo e resta tuo. Non lo rivendichiamo, non lo pubblichiamo e non lo usiamo per altro oltre a farti ottenere il risultato. Lo conserviamo solo se scegli di salvare il risultato o di usare lo spazio personale, e solo per quel fine.</p>
           <p>Al progetto appartengono invece il catalogo, il codice, il design, il nome e il logo: si possono usare come previsto dal servizio, ma non copiare e ripresentare come un prodotto tuo.</p>'
        : '<p>The text you write is yours and stays yours. We claim no rights on it, we do not publish it and we do not use it for anything other than getting you the result. We store it only if you choose to save the result or to use the personal space, and only for that purpose.</p>
           <p>The catalogue, the code, the design, the name and the logo belong to the project: they may be used as the service provides, but not copied and presented as a product of your own.</p>',
]);

lux_page_section([
    'n' => '07', 'id' => 'spazio',
    't' => $isIt ? 'Il mio spazio e il codice personale' : 'My space and the personal code',
    'body' => $isIt
        ? '<p>Lo spazio personale (pagina <a href="/spazio.php">Il mio spazio</a>) è una comodità facoltativa: ti fa ritrovare cronologia, preferiti, risultati salvati e memoria dell\'assistente su più dispositivi. Non è un account, non chiede email e non ha password: si entra con un <strong>codice personale</strong> generato dal sito.</p>
           <ul>
             <li>Il codice è personale: <strong>chi lo conosce può vedere lo spazio</strong>. Trattalo come una chiave di casa e non condividerlo.</li>
             <li>Il codice in chiaro non è conservato sul server (solo la sua impronta), quindi non può essere recuperato: se lo perdi, lo spazio non è più raggiungibile e va creato un codice nuovo.</li>
             <li>Puoi cancellare lo spazio in ogni momento dalla pagina: la cancellazione è immediata e non lascia copie.</li>
             <li>Non usare lo spazio per conservare materiale illegale o di altri senza permesso: in quel caso lo spazio viene chiuso.</li>
           </ul>'
        : '<p>The personal space (<a href="/spazio.php">My space</a> page) is an optional convenience: it helps you find history, favourites, saved results and assistant memory across devices. It is not an account, it asks for no email and has no password: you enter with a <strong>personal code</strong> generated by the site.</p>
           <ul>
             <li>The code is personal: <strong>anyone who knows it can see the space</strong>. Treat it like a house key and do not share it.</li>
             <li>The clear-text code is not kept on the server (only its fingerprint), so it cannot be recovered: if you lose it, the space can no longer be reached and a new code must be created.</li>
             <li>You can delete the space at any time from the page: deletion is immediate and leaves no copies.</li>
             <li>Do not use the space to keep illegal material or other people\'s material without permission: in that case the space is closed.</li>
           </ul>',
]);

lux_page_section([
    'n' => '08', 'id' => 'condivisi',
    't' => $isIt ? 'Risultati salvati e condivisi' : 'Saved and shared results',
    'body' => $isIt
        ? '<p>Un risultato salvato vive nel tuo spazio e resta tuo. Se scegli di <strong>condividerlo</strong>, il sito ne crea un indirizzo pubblico e stabile (pagina <a href="/condividi.php">Condividi</a>): da quel momento il contenuto è <strong>pubblico</strong>, chiunque abbia il link può leggerlo, e può essere trovato dai motori di ricerca.</p>
           <p>Prima di condividere controlla che nel testo non ci siano dati tuoi o di altre persone. La scelta di condividerlo è tua e la responsabilità di quello che rendi pubblico è tua: puoi revocarla cancellando il risultato dal tuo spazio.</p>'
        : '<p>A saved result lives in your space and remains yours. If you choose to <strong>share it</strong>, the site creates a public and stable address (<a href="/condividi.php">Share</a> page): from that moment the content is <strong>public</strong>, anyone with the link can read it, and it may be found by search engines.</p>
           <p>Before sharing, check that the text contains no data of yours or other people\'s. The choice to share is yours and the responsibility for what you make public is yours: you can revoke it by deleting the result from your space.</p>',
]);

lux_page_section([
    'n' => '09', 'id' => 'api',
    't' => $isIt ? 'API e chiavi anonime' : 'APIs and anonymous keys',
    'body' => $isIt
        ? '<p>Le API del catalogo si usano con una <strong>chiave anonima</strong> richiesta dalla pagina <a href="/sviluppatori.php">per sviluppatori</a>. La chiave non è legata a una persona e non va trattata come una credenziale segreta: serve a contare le richieste e a fermare gli abusi.</p>
           <ul>
             <li>La chiave è gratuita e non comporta impegni; può essere revocata se usata per abusi, rivendita o carichi anomali.</li>
             <li>Il progetto è <strong>no profit</strong>: le API non hanno garanzia di livello di servizio, possono cambiare forma o fermarsi, e non va costruito sopra di esse un servizio critico.</li>
             <li>Le risposte delle API possono cambiare nel tempo (nuovi strumenti, campi aggiunti): scrivi il tuo codice in modo tollerante.</li>
           </ul>'
        : '<p>The catalogue APIs work with an <strong>anonymous key</strong> requested from the <a href="/sviluppatori.php">developers page</a>. The key is not tied to a person and is not a secret credential: it is there to count requests and stop abuse.</p>
           <ul>
             <li>The key is free and carries no commitment; it may be revoked if used for abuse, resale or abnormal loads.</li>
             <li>The project is <strong>non-profit</strong>: the APIs carry no service-level guarantee, they may change shape or stop, and no critical service should be built on them.</li>
             <li>API responses may change over time (new tools, added fields): write your code tolerantly.</li>
           </ul>',
]);

lux_page_section([
    'n' => '10', 'id' => 'limiti-tecnici',
    't' => $isIt ? 'Limiti d\'uso e velocità' : 'Usage and rate limits',
    'body' => $isIt
        ? '<p>Per restare in piedi con risorse minime, il sito applica <strong>limiti di velocità</strong>: un numero massimo di richieste al minuto per lo stesso indirizzo, e una pausa breve dopo troppe richieste in fila. Vale per il motore, per il modulo contatti, per i voti e per le API.</p>
           <p>Se ricevi un messaggio di «troppe richieste», aspetta qualche secondo e riprova. I limiti esistono per tutti e non sono personali: servono a non far cadere il servizio per colpa di un singolo uso intenso.</p>'
        : '<p>To stay up on minimal resources, the site applies <strong>rate limits</strong>: a maximum number of requests per minute from the same address, and a short pause after too many requests in a row. It applies to the engine, the contact form, ratings and the APIs.</p>
           <p>If you get a "too many requests" message, wait a few seconds and try again. The limits apply to everyone and are not personal: they keep the service from falling over because of a single heavy use.</p>',
]);

lux_page_section([
    'n' => '11', 'id' => 'app',
    't' => $isIt ? 'App Android: APK, verifica, aggiornamenti' : 'Android app: APK, verification, updates',
    'body' => $isIt
        ? '<p>L\'app ufficiale (versione ' . htmlspecialchars($appVer) . ') si scarica come <strong>file APK</strong> dalla <a href="/app.php">pagina dell\'app</a>. Non essendo su Google Play — perché il progetto è no profit e non può permettersi un account sviluppatore — l\'installazione avviene «da fonte sconosciuta» per il telefono, e <strong>Android o Google Play Protect possono chiedere una verifica o una scansione del file</strong>.</p>
           <p>In quel caso il consiglio è: <strong>accetta la verifica e lascia completare la scansione</strong>. Il file è firmato digitalmente dal progetto, è distribuito cifrato (HTTPS) e il suo peso e la sua impronta SHA-256 sono pubblicati nella pagina dell\'app per controllarlo. Non ignorare l\'avviso per abitudine: è lì per la tua sicurezza.</p>
           <p>L\'installazione resta <strong>facoltativa e sotto la tua responsabilità</strong>. Se non ti senti sicuro, <strong>non installare nulla</strong>: il sito fa tutto quello che fa l\'app, e puoi continuare a navigare da lì. Non scaricare l\'APK da altri siti o da link ricevuti in chat: solo dalla pagina ufficiale.</p>
           <p>L\'app si aggiorna scaricando la nuova versione e installandola sopra la precedente: i dati del profilo locale e dello spazio personale non si perdono. Puoi disinstallarla in qualsiasi momento, come qualunque app.</p>'
        : '<p>The official app (version ' . htmlspecialchars($appVer) . ') is downloaded as an <strong>APK file</strong> from the <a href="/app.php">app page</a>. As it is not on Google Play — because the project is non-profit and cannot afford a developer account — installation happens "from an unknown source" for the phone, and <strong>Android or Google Play Protect may ask for a verification or a scan of the file</strong>.</p>
           <p>In that case the advice is: <strong>accept the verification and let the scan complete</strong>. The file is digitally signed by the project, distributed encrypted (HTTPS) and its size and SHA-256 fingerprint are published on the app page so you can check it. Do not dismiss the warning out of habit: it is there for your safety.</p>
           <p>Installation remains <strong>optional and at your own responsibility</strong>. If you do not feel safe, <strong>do not install anything</strong>: the site does everything the app does, and you can keep browsing there. Do not download the APK from other sites or links received in chat: only from the official page.</p>
           <p>The app updates by downloading the new version and installing it over the previous one: local profile data and the personal space are not lost. You can uninstall it at any time, like any app.</p>',
    'brief' => [
        't' => $isIt ? 'Riga da ricordare' : 'Line to remember',
        'items' => $isIt
            ? ['L\'app non è obbligatoria: se non ti fidi, non la installi e usi il sito.', 'Se Google chiede la verifica, si accetta: il file è nostro e firmato.']
            : ['The app is not mandatory: if you do not trust it, do not install it and use the site.', 'If Google asks for the verification, accept it: the file is ours and signed.'],
    ],
]);

lux_page_section([
    'n' => '12', 'id' => 'disponibile',
    't' => $isIt ? 'Disponibilità e manutenzione' : 'Availability and maintenance',
    'body' => $isIt
        ? '<p>Il sito è ospitato su un servizio di hosting unico e non ha ridondanza: può diventare irraggiungibile per un guasto, un aggiornamento tecnico o un intervento di manutenzione. Quando la manutenzione è programmata, l\'avviso compare in <a href="/bacheca.php">bacheca</a> e nella pagina <a href="/status.php">Stato dei servizi</a>.</p>
           <p>Non è previsto alcun livello di servizio garantito (SLA) e nessun risarcimento per interruzioni: il servizio è gratuito, quindi si usa sapendo che può fermarsi.</p>'
        : '<p>The site is hosted on a single hosting service and has no redundancy: it may become unreachable due to a fault, a technical update or maintenance work. When maintenance is scheduled, the notice appears on the <a href="/bacheca.php">board</a> and on the <a href="/status.php">Service status</a> page.</p>
           <p>No guaranteed service level (SLA) is provided and no compensation for interruptions: the service is free, so it is used knowing it may pause.</p>',
]);

lux_page_section([
    'n' => '13', 'id' => 'responsabilita',
    't' => $isIt ? 'Limiti di responsabilità' : 'Limits of liability',
    'body' => $isIt
        ? '<p>Nei limiti consentiti dalla legge, il progetto e chi lo cura <strong>non rispondono</strong> di: danni diretti o indiretti derivanti dall\'uso o dall\'impossibilità di usare il servizio; errori o imprecisioni nei risultati (compresi quelli dell\'IA); perdita di dati salvati nel browser o nello spazio personale; uso che fai di un risultato o di un contenuto condiviso.</p>
           <p>Resta ferma la responsabilità per dolo o colpa grave e ogni altro limite che la legge non permette di escludere. Se sei un consumatore, i tuoi diritti inderogabili previsti dal Codice del Consumo restano intatti: nessuna riga di questi termini li tocca.</p>'
        : '<p>To the extent permitted by law, the project and whoever runs it <strong>are not liable</strong> for: direct or indirect damage arising from the use or inability to use the service; errors or inaccuracies in results (including AI ones); loss of data saved in the browser or in the personal space; the use you make of a result or shared content.</p>
           <p>Liability for wilful misconduct or gross negligence remains, as does any other limit the law does not allow to be excluded. If you are a consumer, your mandatory rights under consumer law remain intact: no line of these terms touches them.</p>',
]);

lux_page_section([
    'n' => '14', 'id' => 'sospensione',
    't' => $isIt ? 'Sospensione e chiusura' : 'Suspension and closure',
    'body' => $isIt
        ? '<p>Possiamo sospendere l\'accesso al servizio, alle API o a un singolo spazio personale se vengono violati questi termini, se il servizio viene usato per danneggiare altri o se vi è un obbligo di legge. Il servizio può anche essere modificato o chiuso in qualsiasi momento, con un avviso in bacheca quando è possibile.</p>
           <p>Se il progetto venisse chiuso, i dati di contatto che ci hai mandato verrebbero cancellati secondo i tempi indicati nell\'<a href="/privacy.php">Informativa Privacy</a>.</p>'
        : '<p>We may suspend access to the service, the APIs or a single personal space if these terms are violated, if the service is used to harm others or if there is a legal obligation. The service may also be changed or closed at any time, with a notice on the board when possible.</p>
           <p>If the project were closed, contact data you sent us would be deleted within the times stated in the <a href="/privacy.php">Privacy Policy</a>.</p>',
]);

lux_page_section([
    'n' => '15', 'id' => 'modifiche',
    't' => $isIt ? 'Modifiche a questi termini' : 'Changes to these terms',
    'body' => $isIt
        ? '<p>Questi termini possono cambiare: se cambia qualcosa di importante, cambia la <strong>data di ultima modifica</strong> in cima alla pagina e la modifica viene annunciata in <a href="/bacheca.php">bacheca</a>. Continuando a usare il servizio dopo una modifica, accetti la versione aggiornata.</p>
           <p><strong>Ultima modifica: 15 ottobre 2026.</strong> In questa versione sono state aggiunte le parti sui contenuti generati dall\'IA, sullo spazio personale e sul codice, sui risultati condivisi, sulle API anonime, sui limiti di velocità e sull\'installazione dell\'APK con la verifica di Google.</p>'
        : '<p>These terms may change: if something important changes, the <strong>last-updated date</strong> at the top of the page changes and the change is announced on the <a href="/bacheca.php">board</a>. By continuing to use the service after a change, you accept the updated version.</p>
           <p><strong>Last updated: 15 October 2026.</strong> In this version we added the parts on AI-generated content, the personal space and its code, shared results, anonymous APIs, rate limits and APK installation with the Google verification.</p>',
]);

lux_page_section([
    'n' => '16', 'id' => 'legge',
    't' => $isIt ? 'Legge applicabile e contatti' : 'Applicable law and contacts',
    'body' => $isIt
        ? '<p>A questi termini si applica la <strong>legge italiana</strong>. Per i consumatori il foro competente è quello del luogo di residenza o domicilio del consumatore, come previsto dalla legge.</p>
           <p>Per qualunque domanda su questi termini si scrive a <strong>' . htmlspecialchars($email) . '</strong> oppure si usa il modulo <a href="/contattaci.php">Contatti</a>. Per i problemi tecnici c\'è la pagina <a href="/assistenza.php">Assistenza</a>.</p>'
        : '<p>These terms are governed by <strong>Italian law</strong>. For consumers, the competent court is that of the consumer\'s place of residence or domicile, as provided by law.</p>
           <p>For any question about these terms write to <strong>' . htmlspecialchars($email) . '</strong> or use the <a href="/contattaci.php">Contact</a> form. For technical problems there is the <a href="/assistenza.php">Support</a> page.</p>',
    'note' => $isIt
        ? 'Questi termini non sono stati scritti da un avvocato: sono scritti in modo onesto e semplice, e dicono quello che il progetto fa davvero. Se certi passaggi dovessero servirti in forma più formale, scrivici.'
        : 'These terms were not written by a lawyer: they are written honestly and simply, and they say what the project really does. If you ever need some passages in a more formal version, write to us.',
]);

lux_page_close([
    'next' => [
        ['t' => $isIt ? 'Privacy Policy' : 'Privacy Policy', 'd' => $isIt ? 'Quali dati trattiamo e quali no.' : 'Which data we process and which we do not.', 'u' => '/privacy.php'],
        ['t' => $isIt ? 'Assistenza' : 'Support', 'd' => $isIt ? 'Un problema tecnico, una segnalazione, un dubbio.' : 'A technical problem, a report, a doubt.', 'u' => '/assistenza.php'],
        ['t' => $isIt ? 'L\'app per Android' : 'The Android app', 'd' => $isIt ? 'Come si installa e cosa fare con la verifica di Google.' : 'How to install it and what to do with the Google verification.', 'u' => '/app.php'],
    ],
]);
lux_footer();
