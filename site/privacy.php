<?php
// ============================================================
// PRIVACY POLICY — L.U.X. VERSE 39.1.1
// ============================================================
// Estesa rispetto alla versione precedente: entrano il motore IA, lo
// spazio personale con codice, i risultati salvati e condivisi, le
// chiavi API anonime, le notifiche e l'app Android. Ogni riga dice
// quello che il sito fa davvero: cookie tecnici elencati per nome,
// localStorage spiegato, tempi di conservazione indicati.
//
// Ultima modifica: 15 ottobre 2026 (versione 39.1.1).
// ============================================================

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/page.php';
lux_session_start();

$lang = currentLang();
$isIt = ($lang === 'it');

$email = 'administration.luxverse@gmail.com';
$siteVer = defined('LUX_SITE_VERSION') ? (string)LUX_SITE_VERSION : '';
$appVer  = defined('LUX_APP_VERSION') ? (string)LUX_APP_VERSION : '';

$pageTitle       = $isIt ? 'Privacy Policy — L.U.X. Verse' : 'Privacy Policy — L.U.X. Verse';
$pageDescription = $isIt
    ? 'Privacy Policy di L.U.X. Verse: quali dati trattiamo, quali non raccogliamo, cookie tecnici, spazio personale con codice, risultati condivisi, API e app Android. Aggiornata al 15 ottobre 2026.'
    : 'L.U.X. Verse Privacy Policy: which data we process, which we do not collect, technical cookies, personal space with a code, shared results, APIs and the Android app. Updated on 15 October 2026.';
$pageKeywords    = 'privacy policy, dati personali, GDPR, cookie tecnici, no tracciamento, L.U.X. Verse';
$pageCSS         = '';

$sezioni = [
    ['id' => 'titolare',   'n' => '01', 't' => $isIt ? 'Chi tratta i tuoi dati' : 'Who processes your data'],
    ['id' => 'cosa',       'n' => '02', 't' => $isIt ? 'Cosa non raccogliamo e cosa sì' : 'What we do not collect and what we do'],
    ['id' => 'cookie',     'n' => '03', 't' => $isIt ? 'Cookie e memoria del browser' : 'Cookies and browser storage'],
    ['id' => 'spazio',     'n' => '04', 't' => $isIt ? 'Spazio personale e codice' : 'Personal space and code'],
    ['id' => 'condivisi',  'n' => '05', 't' => $isIt ? 'Risultati salvati e link condivisi' : 'Saved results and shared links'],
    ['id' => 'messaggi',   'n' => '06', 't' => $isIt ? 'Messaggi, feedback e segnalazioni' : 'Messages, feedback and reports'],
    ['id' => 'api',        'n' => '07', 't' => $isIt ? 'API pubbliche e chiavi anonime' : 'Public APIs and anonymous keys'],
    ['id' => 'ia',         'n' => '08', 't' => $isIt ? 'Motore IA: dove finisce quello che scrivi' : 'AI engine: where what you write goes'],
    ['id' => 'app',        'n' => '09', 't' => $isIt ? 'App Android' : 'Android app'],
    ['id' => 'basi',       'n' => '10', 't' => $isIt ? 'Perché possiamo trattarli' : 'Why we may process them'],
    ['id' => 'tempi',      'n' => '11', 't' => $isIt ? 'Per quanto tempo li teniamo' : 'How long we keep them'],
    ['id' => 'terzi',      'n' => '12', 't' => $isIt ? 'Con chi li condividiamo' : 'Who we share them with'],
    ['id' => 'diritti',    'n' => '13', 't' => $isIt ? 'I tuoi diritti' : 'Your rights'],
    ['id' => 'sicurezza',  'n' => '14', 't' => $isIt ? 'Sicurezza' : 'Security'],
    ['id' => 'minori',     'n' => '15', 't' => $isIt ? 'Minori' : 'Minors'],
    ['id' => 'modifiche',  'n' => '16', 't' => $isIt ? 'Modifiche a questa informativa' : 'Changes to this policy'],
];

lux_header();
lux_page_open([
    'crumbs'   => [['t' => 'Home', 'u' => '/index.php'], ['t' => 'Privacy Policy']],
    'kicker'   => $isIt ? 'Legale' : 'Legal',
    'title'    => $isIt ? 'Privacy Policy' : 'Privacy Policy',
    'lead'     => $isIt
        ? 'Questa informativa dice, in modo leggibile, quali dati passano da L.U.X. Verse, quali non raccogliamo affatto e quanto restano. Il principio è semplice: il sito funziona senza sapere chi sei.'
        : 'This policy states, readably, which data passes through L.U.X. Verse, which we do not collect at all and how long it stays. The principle is simple: the site works without knowing who you are.',
    'meta'     => [
        $isIt ? 'Ultima modifica: 15 ottobre 2026' : 'Last updated: 15 October 2026',
        ($isIt ? 'Versione ' : 'Version ') . $siteVer,
        $isIt ? 'Progetto no profit, nessun fine commerciale' : 'Non-profit project, no commercial purpose',
    ],
    'actions'  => [
        ['t' => $isIt ? 'Come esercitare i tuoi diritti' : 'How to exercise your rights', 'u' => '#diritti', 'p' => true],
        ['t' => $isIt ? 'Termini di Servizio' : 'Terms of Service', 'u' => '/terms.php'],
    ],
    'sections' => $sezioni,
]);

lux_page_section([
    'n' => '01', 'id' => 'titolare',
    't' => $isIt ? 'Chi tratta i tuoi dati' : 'Who processes your data',
    'body' => $isIt
        ? '<p>Il titolare del trattamento è il fondatore e sviluppatore di L.U.X. Verse, che cura il progetto personalmente e non ha alcun fine commerciale. Per qualsiasi cosa scritta in questa pagina — accesso, correzione, cancellazione, domande — si scrive a <strong>' . htmlspecialchars($email) . '</strong>.</p>
           <p>Il progetto <strong>non ha partita IVA, non ha una società e non svolge attività d\'impresa</strong>: non vende servizi, non riceve pagamenti e non genera ricavi. Non esiste quindi nessun reparto commerciale, nessun reparto marketing e nessuna figura che tratti i tuoi dati per finalità diverse da quelle descritte qui.</p>'
        : '<p>The data controller is the founder and developer of L.U.X. Verse, who runs the project personally and has no commercial purpose. For anything written on this page — access, correction, deletion, questions — write to <strong>' . htmlspecialchars($email) . '</strong>.</p>
           <p>The project <strong>has no VAT number, no company and carries out no business activity</strong>: it sells no services, receives no payments and generates no income. There is therefore no commercial department, no marketing department and no one processing your data for purposes other than those described here.</p>',
]);

lux_page_section([
    'n' => '02', 'id' => 'cosa',
    't' => $isIt ? 'Cosa non raccogliamo e cosa sì' : 'What we do not collect and what we do',
    'body' => $isIt
        ? '<h3>Non raccogliamo</h3>
           <ul>
             <li><strong>Nessun account</strong>: nome, email e password non servono per usare il sito, e non li chiediamo.</li>
             <li><strong>Nessuna profilazione</strong>: non costruiamo profili pubblicitari e non incrociamo i tuoi usi con altri siti.</li>
             <li><strong>Nessun identificatore pubblicitario</strong>: nessun pixel di terze parti, nessun tag di analytics esterno, nessuno script di social network incorporato.</li>
             <li><strong>Nessun dato che non serva</strong>: non chiediamo data di nascita, indirizzo, telefono, posizione o contatti.</li>
           </ul>
           <h3>Trattiamo</h3>
           <ul>
             <li><strong>Cookie tecnici</strong>: sessione, lingua e la tua scelta sui cookie (dettaglio al punto 03).</li>
             <li><strong>Quello che ci scrivi</strong>: se usi il modulo di <a href="/assistenza.php">Assistenza</a> o di <a href="/contattaci.php">Contatti</a>, nome (facoltativo), email (facoltativa), categoria e testo del messaggio.</li>
             <li><strong>Feedback</strong>: se lasci un voto o un commento dal pannello del profilo, il voto e il testo.</li>
             <li><strong>Lo spazio personale</strong>: solo se scegli di usarlo, con le regole del punto 04.</li>
             <li><strong>Misure tecniche</strong>: contatore delle aperture di ogni strumento e registro dei tentativi di accesso al pannello di amministrazione, in forma aggregata o limitata all\'indirizzo IP, per capire cosa si usa e per difendere il sito.</li>
           </ul>'
        : '<h3>We do not collect</h3>
           <ul>
             <li><strong>No account</strong>: name, email and password are not needed to use the site, and we do not ask for them.</li>
             <li><strong>No profiling</strong>: we build no advertising profiles and do not cross your usage with other sites.</li>
             <li><strong>No advertising identifiers</strong>: no third-party pixels, no external analytics tag, no embedded social network script.</li>
             <li><strong>No unnecessary data</strong>: we ask for no date of birth, address, phone, location or contacts.</li>
           </ul>
           <h3>We do process</h3>
           <ul>
             <li><strong>Technical cookies</strong>: session, language and your cookie choice (details in section 03).</li>
             <li><strong>What you write to us</strong>: if you use the <a href="/assistenza.php">Support</a> or <a href="/contatti.php">Contact</a> form, name (optional), email (optional), category and message text.</li>
             <li><strong>Feedback</strong>: if you leave a rating or a comment from the profile panel, the rating and the text.</li>
             <li><strong>The personal space</strong>: only if you choose to use it, under the rules in section 04.</li>
             <li><strong>Technical measurements</strong>: a counter of tool openings and a log of attempts to access the admin panel, in aggregate form or limited to the IP address, to understand what is used and to defend the site.</li>
           </ul>',
    'brief' => [
        't' => $isIt ? 'La riga più importante' : 'The most important line',
        'items' => $isIt
            ? ['Puoi usare tutto il catalogo, la ricerca, gli strumenti e l\'IA senza dire chi sei e senza accettare nessun cookie non tecnico.']
            : ['You can use the whole catalogue, the search, the tools and the AI without saying who you are and without accepting any non-technical cookie.'],
    ],
]);

lux_page_section([
    'n' => '03', 'id' => 'cookie',
    't' => $isIt ? 'Cookie e memoria del browser' : 'Cookies and browser storage',
    'body' => $isIt
        ? '<p>Il sito usa <strong>solo cookie tecnici</strong>. Non ci sono cookie di tracciamento, cookie pubblicitari o cookie di terze parti. Questi sono, per nome:</p>
           <ul>
             <li><code>lux_session</code> (o nome equivalente della sessione): serve a far funzionare la sessione — per esempio a ricordare che hai aperto l\'area personale o che sei entrato nel pannello. È temporaneo.</li>
             <li><code>lux_lang</code>: ricorda la lingua che hai scelto (italiano o inglese).</li>
             <li><code>lux_cookie_choice</code>: ricorda la risposta che hai dato all\'avviso sui cookie, per non ripetertelo a ogni pagina.</li>
           </ul>
           <p>Oltre ai cookie, il sito salva alcune cose <strong>solo nel tuo browser</strong> (memoria locale): i preferiti, la cronologia recente degli strumenti, il nome e l\'immagine del profilo se li hai impostati, il codice personale, i risultati salvati e quali notifiche hai già letto. Questi dati <strong>non vengono inviati al server</strong>, tranne quando scegli di sincronizzare lo spazio personale (punto 04) o di salvare un risultato. Se svuoti i dati del browser, spariscono.</p>
           <p>Puoi rifiutare i cookie dall\'avviso iniziale: il sito continua a funzionare. Puoi anche cancellarli quando vuoi dalle impostazioni del browser.</p>'
        : '<p>The site uses <strong>technical cookies only</strong>. There are no tracking cookies, advertising cookies or third-party cookies. These are they, by name:</p>
           <ul>
             <li><code>lux_session</code> (or the equivalent session name): it makes the session work — for example remembering that you opened the personal area or signed into the panel. It is temporary.</li>
             <li><code>lux_lang</code>: remembers the language you chose (Italian or English).</li>
             <li><code>lux_cookie_choice</code>: remembers the answer you gave to the cookie notice, so it is not repeated on every page.</li>
           </ul>
           <p>Besides cookies, the site stores a few things <strong>only in your browser</strong> (local storage): favourites, recent tool history, profile name and image if you set them, the personal code, saved results and which notifications you have already read. These data <strong>are not sent to the server</strong>, except when you choose to sync the personal space (section 04) or to save a result. If you clear your browser data, they are gone.</p>
           <p>You can refuse cookies from the initial notice: the site keeps working. You can also delete them whenever you like from your browser settings.</p>',
    'box' => ['tone' => 'ok', 't' => $isIt ? 'Perché non trovi un banner a ogni pagina' : 'Why there is no banner on every page', 'd' => $isIt
        ? 'L\'avviso compare una volta e la tua scelta viene ricordata: non chiediamo consenso per cookie che non esistono e non usiamo il banner per altro.'
        : 'The notice appears once and your choice is remembered: we do not ask consent for cookies that do not exist and we do not use the banner for anything else.'],
]);

lux_page_section([
    'n' => '04', 'id' => 'spazio',
    't' => $isIt ? 'Spazio personale e codice' : 'Personal space and code',
    'body' => $isIt
        ? '<p>Nella pagina <a href="/spazio.php">Il mio spazio</a> puoi far sincronizzare fra i tuoi dispositivi la cronologia, i preferiti, i risultati salvati e la memoria dell\'assistente. Non serve un account: serve un <strong>codice personale di dodici caratteri</strong>, che viene generato dal sito e che scegli di usare.</p>
           <ul>
             <li>Sul server viene conservata l\'<strong>impronta</strong> del codice (un valore calcolato da cui non si risale al codice) insieme al contenuto dello spazio.</li>
             <li>Il codice in chiaro <strong>non viene mai scritto sul server</strong> e non è visibile nemmeno a chi amministra il sito.</li>
             <li>Chiunque conoscesse il tuo codice potrebbe vedere quello spazio: per questo va tenuto come una chiave, e non va condiviso.</li>
             <li>Puoi <strong>cancellare lo spazio</strong> in qualsiasi momento dalla pagina: la cancellazione è immediata e definitiva, e non lascia copie.</li>
             <li>Se non usi lo spazio personale, cronologia e preferiti restano solo nel tuo browser e non arrivano al server.</li>
           </ul>'
        : '<p>On the <a href="/spazio.php">My space</a> page you can have history, favourites, saved results and the assistant\'s memory synced across your devices. No account is needed: what is needed is a <strong>twelve-character personal code</strong>, generated by the site, which you choose to use.</p>
           <ul>
             <li>The server keeps the <strong>fingerprint</strong> of the code (a value from which the code cannot be recovered) together with the content of the space.</li>
             <li>The code in clear text is <strong>never written to the server</strong> and is not visible even to whoever administers the site.</li>
             <li>Anyone who knew your code could see that space: for this reason it must be treated like a key, and must not be shared.</li>
             <li>You can <strong>delete the space</strong> at any time from the page: deletion is immediate and permanent, and leaves no copies.</li>
             <li>If you do not use the personal space, history and favourites stay only in your browser and never reach the server.</li>
           </ul>',
]);

lux_page_section([
    'n' => '05', 'id' => 'condivisi',
    't' => $isIt ? 'Risultati salvati e link condivisi' : 'Saved results and shared links',
    'body' => $isIt
        ? '<p>Un risultato che salvi resta nel tuo spazio finché non lo cancelli. Se scegli di <strong>condividerlo</strong>, il sito crea un indirizzo pubblico e stabile (vedi <a href="/condividi.php">/condividi.php</a>) che contiene soltanto: il testo del risultato, gli strumenti che lo hanno prodotto e la data. <strong>Non contiene il tuo codice, il tuo nome, la tua email né il tuo indirizzo IP</strong>.</p>
           <p>Un link condiviso è pubblico: chiunque lo abbia può leggerlo, e può comparire in una ricerca. Per questo, prima di condividerlo, controlla che nel testo non ci siano cose tue — e ricorda che puoi revocarlo cancellando il risultato dal tuo spazio.</p>'
        : '<p>A result you save stays in your space until you delete it. If you choose to <strong>share it</strong>, the site creates a public, stable address (see <a href="/condividi.php">/condividi.php</a>) that contains only: the text of the result, the tools that produced it and the date. <strong>It contains neither your code, nor your name, nor your email, nor your IP address</strong>.</p>
           <p>A shared link is public: anyone holding it can read it, and it may appear in a search. So before sharing, check that the text contains nothing of yours — and remember you can revoke it by deleting the result from your space.</p>',
]);

lux_page_section([
    'n' => '06', 'id' => 'messaggi',
    't' => $isIt ? 'Messaggi, feedback e segnalazioni' : 'Messages, feedback and reports',
    'body' => $isIt
        ? '<p>Quando scrivi dal modulo di Assistenza o di Contatti, vengono salvati: nome (o «Anonimo» se lo lasci vuoto), email (se la indichi), categoria, testo del messaggio, data e ora, e un codice di riferimento. L\'email serve solo a risponderti. Il messaggio viene letto da chi amministra il sito e non viene ceduto a nessuno.</p>
           <p>I feedback con voto (le stelle del pannello del profilo) vengono salvati in forma aggregata, con il canale da cui arrivano (sito, guida, assistenza) e il testo se lo scrivi. Servono a capire cosa migliorare.</p>
           <p>L\'indirizzo IP viene usato <strong>solo</strong> per limitare gli abusi (per esempio impedire cento segnalazioni in un minuto, o troppi voti dallo stesso indirizzo) e per il registro dei tentativi di accesso al pannello: non viene usato per riconoscerti, non viene incrociato con altri dati e non viene conservato oltre i tempi del punto 11.</p>'
        : '<p>When you write from the Support or Contact form, these are stored: name (or "Anonymous" if left empty), email (if given), category, message text, date and time, and a reference code. The email is used only to reply to you. The message is read by whoever administers the site and is not handed to anyone.</p>
           <p>Ratings (the stars in the profile panel) are stored in aggregate form, with the channel they come from (site, guide, support) and the text if you write one. They are used to understand what to improve.</p>
           <p>The IP address is used <strong>only</strong> to limit abuse (for example preventing a hundred reports in a minute, or too many votes from the same address) and for the log of attempts to access the panel: it is not used to recognise you, is not crossed with other data and is not kept beyond the times in section 11.</p>',
]);

lux_page_section([
    'n' => '07', 'id' => 'api',
    't' => $isIt ? 'API pubbliche e chiavi anonime' : 'Public APIs and anonymous keys',
    'body' => $isIt
        ? '<p>Gli sviluppatori possono usare gli strumenti del catalogo anche dai propri progetti, con una <strong>chiave anonima</strong> richiesta dalla pagina <a href="/sviluppatori.php">per sviluppatori</a>. La chiave non è legata a una persona: non chiediamo nome, email o partita IVA per rilasciarla.</p>
           <p>Di quella chiave conserviamo la chiave stessa, il momento in cui è stata creata, il numero di richieste fatte e l\'ultimo utilizzo: servono al limite di velocità e a capire se una chiave viene usata male. Le richieste fatte con la chiave <strong>non</strong> vengono registrate come attività di una persona. Puoi chiedere la revoca di una chiave in qualsiasi momento scrivendo a ' . htmlspecialchars($email) . '.</p>'
        : '<p>Developers can use the catalogue tools from their own projects, with an <strong>anonymous key</strong> requested from the <a href="/sviluppatori.php">developers page</a>. The key is not tied to a person: we ask for no name, email or VAT number to issue it.</p>
           <p>About that key we keep the key itself, when it was created, how many requests it made and when it was last used: these are needed for the rate limit and to notice whether a key is being misused. Requests made with the key are <strong>not</strong> recorded as a person\'s activity. You can ask for a key to be revoked at any time by writing to ' . htmlspecialchars($email) . '.</p>',
]);

lux_page_section([
    'n' => '08', 'id' => 'ia',
    't' => $isIt ? 'Motore IA: dove finisce quello che scrivi' : 'AI engine: where what you write goes',
    'body' => $isIt
        ? '<p>Quando usi uno strumento con l\'IA, o la modalità <strong>IA (Veloce)</strong> della barra, <strong>il testo che scrivi viene inviato a un fornitore di modelli di intelligenza artificiale</strong> per produrre il risultato. È inevitabile: il risultato lo genera quel servizio. Il testo viene trasmesso solo per quella richiesta.</p>
           <p>Il testo della tua richiesta viene conservato nel tuo spazio solo se scegli di salvare il risultato; negli altri casi non viene scritto nei nostri file. Non inviare all\'IA dati personali, sanitari, documenti di identità o informazioni riservate di altre persone: usa il buon senso come faresti con qualunque servizio online.</p>
           <p>Se non vuoi che il testo esca dal tuo dispositivo, usa gli strumenti <strong>istantanei</strong> (quelli senza IA): funzionano interamente nel tuo browser e non mandano niente a nessuno.</p>'
        : '<p>When you use an AI tool, or the <strong>AI (Fast)</strong> mode in the bar, <strong>the text you write is sent to an artificial intelligence model provider</strong> to produce the result. That is unavoidable: that service generates the result. The text is transmitted only for that request.</p>
           <p>The text of your request is stored in your space only if you choose to save the result; otherwise it is not written to our files. Do not send personal, health, identity document or other people\'s confidential information to the AI: use common sense as you would with any online service.</p>
           <p>If you do not want the text to leave your device, use the <strong>instant</strong> tools (the ones without AI): they work entirely in your browser and send nothing to anyone.</p>',
    'box' => ['tone' => 'warn', 't' => $isIt ? 'Da sapere' : 'Good to know', 'd' => $isIt
        ? 'Le risposte dell\'IA possono essere imprecise. Non sono consulenza medica, legale, fiscale o finanziaria: verifica sempre quello che ti serve prima di usarlo.'
        : 'AI answers can be inaccurate. They are not medical, legal, tax or financial advice: always verify what you need before using it.'],
]);

lux_page_section([
    'n' => '09', 'id' => 'app',
    't' => $isIt ? 'App Android' : 'Android app',
    'body' => $isIt
        ? '<p>L\'app ufficiale (versione ' . htmlspecialchars($appVer) . ') si scarica come file APK dalla <a href="/app.php">pagina dell\'app</a>, perché il progetto è no profit e non ha un account sviluppatore a pagamento sugli store. L\'app <strong>non chiede permessi invasivi</strong>: non legge contatti, posizione, fotocamera, microfono o file.</p>
           <p>Quando usi l\'app, la raccolta dati è la stessa del sito: nessun account, nessun tracciamento, nessun identificatore pubblicitario. Il download del file viene tracciato dal fornitore di servizi del sito (come per qualunque file scaricato dal web) e non da noi in forma personale.</p>'
        : '<p>The official app (version ' . htmlspecialchars($appVer) . ') is downloaded as an APK file from the <a href="/app.php">app page</a>, because the project is non-profit and has no paid developer account in the stores. The app <strong>asks for no invasive permissions</strong>: it does not read contacts, location, camera, microphone or files.</p>
           <p>When you use the app, data collection is the same as the site: no account, no tracking, no advertising identifier. The download of the file is tracked by the site\'s hosting provider (as with any file downloaded from the web), not by us in personal form.</p>',
]);

lux_page_section([
    'n' => '10', 'id' => 'basi',
    't' => $isIt ? 'Perché possiamo trattarli' : 'Why we may process them',
    'body' => $isIt
        ? '<ul>
             <li><strong>Cookie tecnici e memoria del browser</strong>: non richiedono consenso perché servono a erogare il servizio che hai chiesto (e la tua scelta viene comunque chiesta e rispettata).</li>
             <li><strong>Messaggi, feedback e segnalazioni</strong>: esecuzione di quanto hai richiesto scrivendoci (risponderti) e interesse legittimo a migliorare il progetto e a difenderlo dagli abusi.</li>
             <li><strong>Spazio personale</strong>: consenso, che dai generando il codice: se non lo usi, non trattiamo nulla.</li>
             <li><strong>Misure tecniche e registri di sicurezza</strong>: interesse legittimo a mantenere il servizio funzionante e sicuro.</li>
           </ul>
           <p>Non usiamo nessuno di questi dati per decisioni automatiche che ti riguardino, né per pubblicità o profilazione.</p>'
        : '<ul>
             <li><strong>Technical cookies and browser storage</strong>: they need no consent because they deliver the service you asked for (and your choice is still requested and respected).</li>
             <li><strong>Messages, feedback and reports</strong>: performance of what you asked for by writing to us (replying) and legitimate interest in improving the project and defending it from abuse.</li>
             <li><strong>Personal space</strong>: consent, given by generating the code: if you do not use it, we process nothing.</li>
             <li><strong>Technical measurements and security logs</strong>: legitimate interest in keeping the service working and safe.</li>
           </ul>
           <p>We use none of these data for automated decisions concerning you, nor for advertising or profiling.</p>',
]);

lux_page_section([
    'n' => '11', 'id' => 'tempi',
    't' => $isIt ? 'Per quanto tempo li teniamo' : 'How long we keep them',
    'body' => $isIt
        ? '<ul>
             <li><strong>Cookie</strong>: la sessione dura finché il browser è aperto; lingua e scelta sui cookie restano finché non le cancelli.</li>
             <li><strong>Memoria del browser</strong>: resta sul tuo dispositivo finché non la cancelli tu.</li>
             <li><strong>Messaggi e feedback</strong>: fino a <strong>24 mesi</strong> dall\'ultimo scambio, per poter rileggere la conversazione; prima se chiedi la cancellazione.</li>
             <li><strong>Spazio personale</strong>: finché lo usi. Senza attività per <strong>12 mesi consecutivi</strong> può essere cancellato; puoi cancellarlo tu in qualsiasi momento.</li>
             <li><strong>Chiavi API</strong>: finché la chiave esiste; i contatori delle richieste restano aggregati per 12 mesi.</li>
             <li><strong>Registro dei tentativi di accesso e contatori aggregati</strong>: massimo <strong>12 mesi</strong>.</li>
           </ul>
           <p>I dati vengono conservati su server dell\'Unione Europea. Alla scadenza sono cancellati.</p>'
        : '<ul>
             <li><strong>Cookies</strong>: the session lasts while the browser is open; language and cookie choice stay until you delete them.</li>
             <li><strong>Browser storage</strong>: it stays on your device until you clear it.</li>
             <li><strong>Messages and feedback</strong>: up to <strong>24 months</strong> from the last exchange, so the conversation can be re-read; sooner if you ask for deletion.</li>
             <li><strong>Personal space</strong>: while you use it. With no activity for <strong>12 consecutive months</strong> it may be deleted; you can delete it yourself at any time.</li>
             <li><strong>API keys</strong>: as long as the key exists; request counters stay aggregate for 12 months.</li>
             <li><strong>Log of access attempts and aggregate counters</strong>: at most <strong>12 months</strong>.</li>
           </ul>
           <p>Data are stored on servers in the European Union. When the term expires, they are deleted.</p>',
]);

lux_page_section([
    'n' => '12', 'id' => 'terzi',
    't' => $isIt ? 'Con chi li condividiamo' : 'Who we share them with',
    'body' => $isIt
        ? '<p>Non vendiamo e non cediamo dati personali. Le uniche comunicazioni possibili sono queste:</p>
           <ul>
             <li><strong>Fornitore di hosting</strong>: ospita il sito e i file, quindi tecnicamente vede il traffico (compreso l\'indirizzo IP) come qualunque servizio di hosting.</li>
             <li><strong>Fornitori di modelli IA</strong>: ricevono il testo che invii quando usi uno strumento con IA o la modalità IA, al solo fine di generare il risultato.</li>
             <li><strong>Autorità</strong>: solo se obbligati da una richiesta di legge.</li>
           </ul>
           <p>Non ci sono altri destinatari: nessuna rete pubblicitaria, nessun servizio di analytics esterno, nessun servizio di monitoraggio esterno.</p>'
        : '<p>We do not sell or hand over personal data. The only possible disclosures are these:</p>
           <ul>
             <li><strong>Hosting provider</strong>: it hosts the site and the files, so technically it sees traffic (including the IP address) like any hosting service.</li>
             <li><strong>AI model providers</strong>: they receive the text you submit when using an AI tool or AI mode, solely to generate the result.</li>
             <li><strong>Authorities</strong>: only if obliged by a lawful request.</li>
           </ul>
           <p>There are no other recipients: no advertising network, no external analytics service, no external monitoring service.</p>',
]);

lux_page_section([
    'n' => '13', 'id' => 'diritti',
    't' => $isIt ? 'I tuoi diritti' : 'Your rights',
    'body' => $isIt
        ? '<p>Puoi chiedere: <strong>accesso</strong> ai dati che ti riguardano, <strong>correzione</strong> se sono sbagliati, <strong>cancellazione</strong>, <strong>limitazione</strong> del trattamento, <strong>opposizione</strong> e, dove applicabile, <strong>portabilità</strong>. Puoi anche revocare il consenso allo spazio personale cancellandolo.</p>
           <p>Per esercitarli basta scrivere a <strong>' . htmlspecialchars($email) . '</strong>, oppure usare il modulo <a href="/contattaci.php">Contatti</a> scegliendo «Dati personali e privacy». Rispondiamo <strong>senza costi</strong> e il più presto possibile; se la richiesta è complessa te lo diciamo e ti indichiamo una data.</p>
           <p>Se ritieni che il trattamento non sia corretto puoi rivolgerti al <strong>Garante per la protezione dei dati personali</strong> (Italia): è un diritto, e non è necessario avvisarci prima.</p>'
        : '<p>You may request: <strong>access</strong> to the data concerning you, <strong>rectification</strong> if they are wrong, <strong>erasure</strong>, <strong>restriction</strong> of processing, <strong>objection</strong> and, where applicable, <strong>portability</strong>. You may also withdraw consent to the personal space by deleting it.</p>
           <p>To exercise them simply write to <strong>' . htmlspecialchars($email) . '</strong>, or use the <a href="/contattaci.php">Contact</a> form choosing "Personal data and privacy". We reply <strong>at no cost</strong> and as early as possible; if the request is complex we tell you and give you a date.</p>
           <p>If you believe the processing is not correct you may turn to the <strong>Italian Data Protection Authority</strong>: that is a right, and you do not need to tell us first.</p>',
]);

lux_page_section([
    'n' => '14', 'id' => 'sicurezza',
    't' => $isIt ? 'Sicurezza' : 'Security',
    'body' => $isIt
        ? '<p>Il sito viaggia su connessione cifrata (HTTPS). I dati che contano sono pochi per scelta: non ci sono password di utenti da rubare, perché non ci sono utenti registrati. I file con i dati non sono leggibili dal web (le cartelle interne sono protette) e gli accessi al pannello di amministrazione sono registrati.</p>
           <p>Se trovi un problema di sicurezza, segnalalo come scritto nella pagina <a href="/assistenza.php#urgenti">Assistenza</a>: passa davanti alle altre richieste.</p>'
        : '<p>The site runs over an encrypted connection (HTTPS). The data that matter are few by choice: there are no user passwords to steal, because there are no registered users. Data files are not readable from the web (internal folders are protected) and access to the admin panel is logged.</p>
           <p>If you find a security problem, report it as described on the <a href="/assistenza.php#urgenti">Support</a> page: it goes ahead of other requests.</p>',
]);

lux_page_section([
    'n' => '15', 'id' => 'minori',
    't' => $isIt ? 'Minori' : 'Minors',
    'body' => $isIt
        ? '<p>Il sito è pensato per essere usato da chiunque, anche da ragazzi, e non richiede la creazione di un account né la comunicazione di dati personali per funzionare: questo lo rende utilizzabile in sicurezza anche da minori. L\'unico momento in cui si forniscono dati è quando si scrive un messaggio, e lì l\'email è facoltativa.</p>
           <p>Il progetto stesso è gestito da una persona minorenne, con la supervisione della propria famiglia: se ti serve, per una segnalazione o per un chiarimento, si può usare l\'indirizzo ' . htmlspecialchars($email) . '.</p>'
        : '<p>The site is meant to be used by anyone, including young people, and requires no account and no personal data to work: this makes it safe for minors too. The only moment data is given is when writing a message, and there the email is optional.</p>
           <p>The project itself is run by a minor, with the supervision of his family: if needed, for a report or a clarification, the address ' . htmlspecialchars($email) . ' can be used.</p>',
]);

lux_page_section([
    'n' => '16', 'id' => 'modifiche',
    't' => $isIt ? 'Modifiche a questa informativa' : 'Changes to this policy',
    'body' => $isIt
        ? '<p>Quando cambia qualcosa di importante in questa informativa, cambia anche la <strong>data di ultima modifica</strong> in cima alla pagina, e la modifica viene annunciata in <a href="/bacheca.php">bacheca</a> come qualunque altro cambiamento del sito.</p>
           <p><strong>Ultima modifica: 15 ottobre 2026.</strong> In questa versione sono state aggiunte le parti sul motore IA (dove finisce il testo che scrivi), sullo spazio personale con codice, sui risultati salvati e condivisi, sulle chiavi API anonime, sulle notifiche e sull\'app Android, oltre all\'elenco dei cookie tecnici per nome.</p>'
        : '<p>When something important changes in this policy, the <strong>last-updated date</strong> at the top of the page changes too, and the change is announced on the <a href="/bacheca.php">board</a> like any other change to the site.</p>
           <p><strong>Last updated: 15 October 2026.</strong> In this version we added the parts on the AI engine (where the text you write goes), the personal space with a code, saved and shared results, anonymous API keys, notifications and the Android app, plus the list of technical cookies by name.</p>',
    'note' => $isIt
        ? 'Questa informativa è scritta in modo semplice di proposito: se qualcosa non è chiaro, scrivici e la riscriviamo meglio.'
        : 'This policy is written simply on purpose: if something is not clear, write to us and we will rewrite it better.',
]);

lux_page_close([
    'next' => [
        ['t' => $isIt ? 'Termini di Servizio' : 'Terms of Service', 'd' => $isIt ? 'Le regole d\'uso, gli obblighi e i limiti.' : 'The rules of use, obligations and limits.', 'u' => '/terms.php'],
        ['t' => $isIt ? 'Il mio spazio' : 'My space', 'd' => $isIt ? 'Cronologia, preferiti e codice personale.' : 'History, favourites and personal code.', 'u' => '/spazio.php'],
        ['t' => t('Contatti'), 'd' => $isIt ? 'Una domanda sui tuoi dati? Scrivici qui.' : 'A question about your data? Write here.', 'u' => '/contattaci.php'],
    ],
]);
lux_footer();
