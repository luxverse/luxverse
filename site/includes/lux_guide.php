<?php
// ============================================================
// L.U.X. GUIDE — la guida ufficiale dell'ecosistema
// ------------------------------------------------------------
// Non e' una chat libera: e' a OPZIONI. All'inizio chiede in due
// domande (una in italiano e una in inglese) in quale lingua si vuole
// procedere; poi mostra dei pulsanti. Se una domanda non viene capita,
// la conversazione viene trasferita all'admin (vedi lux_chat_* in
// lux_platform.php e l'endpoint "guide" in api/api.php).
//
// La "conoscenza" NON e' scritta a mano una volta per tutte: si costruisce
// a ogni richiesta leggendo il sito vero (numero di strumenti, categorie,
// pagine, stato dei sistemi). Cosi' L.U.X. GUIDE sa sempre al 100% cosa
// c'e' dentro L.U.X. Verse e non racconta mai numeri vecchi.
// ============================================================

if (!defined('LUX_GUIDE_LOADED')) {
    define('LUX_GUIDE_LOADED', true);

    /** Quanti strumenti ci sono, in cifre leggibili. */
    function lux_guide_tool_count(string $lang): string
    {
        $n = function_exists('lux_catalog_total') ? lux_catalog_total() : 100000;
        return $lang === 'en'
            ? number_format($n, 0, '.', ',')
            : number_format($n, 0, ',', '.');
    }

    /** Il numero ufficiale di strumenti del catalogo. */
    function lux_guide_official_count(): int
    {
        return function_exists('lux_catalog_total') ? lux_catalog_total() : 100000;
    }

    /** Contatto ufficiale (unico posto nel codice). */
    function lux_contact_email(): string
    {
        $e = 'administration.luxverse@gmail.com';
        return $e;
    }

    /**
     * Voci della guida. Ogni voce: parole chiave per riconoscerla e testo
     * della risposta nelle due lingue. Questo e' il "sapere" fisso; i numeri
     * vengono composti al momento da lux_guide_answer().
     */
    function lux_guide_entries(): array
    {
        return [
            'about' => [
                'keywords' => ['cos e', 'cos\'e', 'che cos', 'what is', 'l u x verse', 'luxverse', 'ecosistema', 'ecosystem', 'chi siete', 'who are you', 'azienda', 'company', 'verse', 'progetto', 'project'],
                'title_it' => "Cos'è L.U.X. Verse",
                'title_en' => 'What is L.U.X. Verse',
                'answer_it' => "L.U.X. Verse è un ecosistema gratuito di strumenti basati sull'intelligenza artificiale: creatività, produttività, studio, giochi e utilità. Al momento mette a disposizione {n} strumenti, tutti accessibili senza registrazione e senza costi. È un progetto senza scopo di lucro: nessun ricavo, nessuna pubblicità, nessun tracciamento.",
                'answer_en' => 'L.U.X. Verse is a free ecosystem of AI-powered tools for creativity, productivity, study, games and utilities. It currently offers {n} tools, all available with no registration and no cost. It is a non-profit project: no revenue, no advertising, no tracking.',
            ],
            'tools' => [
                'keywords' => ['strumenti', 'tool', 'tools', 'catalogo', 'catalog', '100000', '100.000', 'quanti', 'how many', 'quali', 'which', 'categoria', 'category', 'usare', 'use', 'funzioni', 'features'],
                'title_it' => 'Strumenti e catalogo',
                'title_en' => 'Tools and catalog',
                'answer_it' => "Il catalogo contiene {n} strumenti divisi in cinque famiglie: creatività, produttività, studio (educativi), giochi e utilità. Ogni strumento ha la sua pagina, il suo nome (per esempio LUX.SummarizeEmailFlash) e una scheda pensata per l'intelligenza artificiale. Puoi cercarli dalla home scrivendo quello che ti serve, oppure esplorarli per categoria.",
                'answer_en' => 'The catalog contains {n} tools in five families: creativity, productivity, study (educational), games and utilities. Each tool has its own page, its own name (for example LUX.SummarizeEmailFlash) and a card designed for the AI. You can look for them from the home page by writing what you need, or browse by category.',
            ],
            'choose' => [
                'keywords' => ['scegliere', 'choose', 'consigli', 'recommend', 'adatto', 'right', 'migliore', 'best', 'quale strumento', 'which tool', 'trovare', 'find', 'esigenza', 'need'],
                'title_it' => 'Trovare lo strumento giusto',
                'title_en' => 'Finding the right tool',
                'answer_it' => "Non devi conoscere il nome: nella home scrivi con parole tue cosa ti serve (per esempio \"devo riassumere una email lunga in poche righe\") e il sistema ti propone gli strumenti più adatti. È la ricerca per esigenza.",
                'answer_en' => 'You do not need to know the name: on the home page write in your own words what you need (for example "I have to summarize a long email into a few lines") and the system suggests the most suitable tools. That is the search by need.',
            ],
            'free' => [
                'keywords' => ['gratis', 'free', 'costo', 'cost', 'prezzo', 'price', 'pagare', 'pay', 'abbonamento', 'subscription', 'pubblicità', 'ads', 'advertising'],
                'title_it' => 'È davvero gratis?',
                'title_en' => 'Is it really free?',
                'answer_it' => "Sì: tutti gli strumenti sono gratuiti e senza abbonamento. Non c'è pubblicità e il progetto non ha scopo di lucro. Esistono però dei limiti tecnici dovuti ai fornitori dell'intelligenza artificiale: se molte persone usano il sito nello stesso momento, qualche risposta può richiedere qualche secondo in più. Il sistema distribuisce automaticamente il carico.",
                'answer_en' => 'Yes: every tool is free, with no subscription. There is no advertising and the project is non-profit. There are technical limits due to the AI providers: if many people use the site at the same time, a response may take a few seconds longer. The system balances the load automatically.',
            ],
            'privacy' => [
                'keywords' => ['privacy', 'dati', 'data', 'personali', 'personal', 'cookie', 'tracciamento', 'tracking', 'gdpr', 'goletta', 'garante', 'raccolta', 'collect', 'conservare', 'store', 'sicurezza', 'security'],
                'title_it' => 'Privacy e dati',
                'title_en' => 'Privacy and data',
                'answer_it' => "Non serve registrarsi e non chiediamo dati personali. Il profilo (nome utente e immagine) è facoltativo e resta solo sul tuo dispositivo: non viene inviato ai nostri server. Non usiamo cookie di tracciamento né profilazione pubblicitaria. Puoi leggere tutti i dettagli nella pagina Privacy. Il titolare del trattamento è L.U.X. Verse e il contatto è {mail}.",
                'answer_en' => 'No registration is required and we do not ask for personal data. The profile (username and picture) is optional and stays only on your device: it is never sent to our servers. We use no tracking cookies and no advertising profiling. You can read every detail on the Privacy page. The data controller is L.U.X. Verse and the contact is {mail}.',
            ],
            'terms' => [
                'keywords' => ['termini', 'terms', 'condizioni', 'conditions', 'regole', 'rules', 'legale', 'legal', 'responsabilità', 'liability', 'uso consentito', 'acceptable', 'legge', 'law'],
                'title_it' => 'Termini e regole d\'uso',
                'title_en' => 'Terms and rules of use',
                'answer_it' => "L.U.X. Verse è un servizio gratuito offerto \"così com'è\". Puoi usarlo per scopi personali e professionali leciti; non puoi usarlo per attività illegali, per generare contenuti dannosi o per danneggiare il servizio. Le risposte dell'intelligenza artificiale possono contenere imprecisioni: verifica sempre le informazioni importanti. I dettagli completi sono nella pagina Termini.",
                'answer_en' => 'L.U.X. Verse is a free service offered "as is". You may use it for lawful personal and professional purposes; you may not use it for illegal activities, to create harmful content or to damage the service. AI answers can contain inaccuracies: always verify important information. Full details are on the Terms page.',
            ],
            'app' => [
                'keywords' => ['app', 'android', 'applicazione', 'application', 'telefono', 'phone', 'cellulare', 'apk', 'download', 'installa', 'install', 'mobile'],
                'title_it' => 'App Android',
                'title_en' => 'Android app',
                'answer_it' => "Esiste l'app Android ufficiale di L.U.X. Verse, collegata al sito in tempo reale: quando il sito cambia, l'app lo vede subito. La trovi dalla pagina dell'app; il file si chiama LUXVerse.apk. La versione attuale è {appver}.",
                'answer_en' => 'There is an official L.U.X. Verse Android app, connected to the site in real time: when the site changes, the app sees it immediately. You can find it from the app page; the file is called LUXVerse.apk. The current version is {appver}.',
            ],
            'status' => [
                'keywords' => ['stato', 'status', 'sistemi', 'systems', 'funziona', 'working', 'guasto', 'down', 'manutenzione', 'maintenance', 'monitoraggio', 'monitoring', 'lento', 'slow'],
                'title_it' => 'Stato dei sistemi',
                'title_en' => 'System status',
                'answer_it' => "C'è una pagina dedicata, \"Stato\", che controlla in tempo reale decine di cose del sito: pagine, cataloghi, ricerca, API, strumenti, traduzioni, sicurezza e prestazioni. La pagina è raggiungibile dal menu in alto e riporta lo stato attuale: {status}.",
                'answer_en' => 'There is a dedicated "Status" page that checks dozens of things about the site in real time: pages, catalogs, search, APIs, tools, translations, security and performance. It is reachable from the top menu and shows the current state: {status}.',
            ],
            'contacts' => [
                'keywords' => ['contatti', 'contact', 'email', 'mail', 'scrivere', 'write', 'assistenza', 'support', 'aiuto', 'help', 'segnalare', 'report', 'problema', 'problem', 'bug', 'operatore', 'human'],
                'title_it' => 'Assistenza e contatti',
                'title_en' => 'Support and contacts',
                'answer_it' => "Per qualunque domanda puoi usare la pagina Assistenza (con apertura e tracciamento di una richiesta) oppure scrivere direttamente a {mail}. Se quello che ti serve non è tra le opzioni, dì \"operatore\" e trasferisco la conversazione a una persona dello staff.",
                'answer_en' => 'For any question you can use the Support page (opening and tracking a ticket) or write directly to {mail}. If what you need is not among the options, say "agent" and I will transfer the conversation to a member of the staff.',
            ],
            'account' => [
                'keywords' => ['registrazione', 'registration', 'account', 'profilo', 'profile', 'utente', 'username', 'nome', 'name', 'login', 'accedere', 'sign in', 'immagine', 'avatar', 'foto'],
                'title_it' => 'Profilo e registrazione',
                'title_en' => 'Profile and registration',
                'answer_it' => "Non esiste una registrazione: non c'è nessun account da creare e nessuna password. Al primo accesso puoi scegliere, se vuoi, un nome utente e un'immagine del profilo: sono facoltativi e restano solo sul tuo dispositivo, in locale. Se non scegli un'immagine, viene mostrata quella predefinita.",
                'answer_en' => 'There is no registration: no account to create and no password. On your first visit you may optionally choose a username and a profile picture: they are optional and stay only on your device, locally. If you do not choose a picture, the default one is shown.',
            ],
            'favorites' => [
                'keywords' => ['preferiti', 'favorites', 'favourites', 'stella', 'star', 'salvare', 'save', 'segna', 'bookmark', 'raccolta', 'collection'],
                'title_it' => 'Preferiti',
                'title_en' => 'Favorites',
                'answer_it' => "Su ogni progetto trovi una stellina: un clic e finisce nei tuoi Preferiti. Anche i preferiti restano solo sul tuo dispositivo: nessuno può vederli al posto tuo e non servono registrazioni.",
                'answer_en' => 'Every project has a little star: one click and it goes into your Favorites. Favorites also stay only on your device: nobody can see them for you and no registration is needed.',
            ],
            'upvote' => [
                'keywords' => ['upvote', 'voto', 'vote', 'votare', 'like', 'mi piace', 'gradimento', 'classifica', 'ranking', 'popolare', 'popular'],
                'title_it' => 'Voti e classifica',
                'title_en' => 'Votes and ranking',
                'answer_it' => "Ogni progetto ha anche una freccia per il voto: il voto è pubblico e anonimo, serve a capire quali strumenti piacciono di più e alimenta la classifica dei progetti più prestigiosi, che l'amministrazione vede nel pannello.",
                'answer_en' => 'Every project also has an upvote arrow: the vote is public and anonymous, it shows which tools people like most and feeds the ranking of the most prestigious projects, visible to the administration in the panel.',
            ],
            'feedback' => [
                'keywords' => ['feedback', 'suggerimento', 'suggestion', 'opinione', 'opinion', 'migliorare', 'improve', 'recensione', 'review', 'voto al sito', 'valutazione', 'rating'],
                'title_it' => 'Feedback',
                'title_en' => 'Feedback',
                'answer_it' => "Puoi lasciare un feedback in qualunque momento: sul sito, oppure alla fine di una conversazione con me o con l'assistenza. Serve a migliorare L.U.X. Verse ed è visibile all'amministrazione.",
                'answer_en' => 'You can leave feedback at any time: on the site, or at the end of a conversation with me or with support. It helps improve L.U.X. Verse and is visible to the administration.',
            ],
            'human' => [
                'keywords' => ['operatore', 'operator', 'persona', 'human', 'umano', 'admin', 'amministratore', 'parlare con', 'talk to', 'assistenza umana', 'reale', 'real'],
                'title_it' => 'Parlare con una persona',
                'title_en' => 'Talk to a person',
                'answer_it' => "Va bene: trasferisco questa conversazione allo staff. Da adesso la chat diventa visibile nel portale dell'amministrazione e una persona ti risponderà qui. Puoi anche scrivere a {mail}.",
                'answer_en' => 'Alright: I am transferring this conversation to the staff. From now on the chat becomes visible in the administration panel and a person will reply to you here. You can also write to {mail}.',
                'escalate' => true,
            ],
        ];
    }

    /** Le voci del menu principale (pulsanti). */
    function lux_guide_menu(string $lang): array
    {
        $it = $lang !== 'en';
        return [
            ['id' => 'about',    'label' => $it ? "Cos'è L.U.X. Verse" : 'What is L.U.X. Verse'],
            ['id' => 'tools',    'label' => $it ? 'Strumenti e catalogo' : 'Tools and catalog'],
            ['id' => 'choose',   'label' => $it ? 'Trovare lo strumento giusto' : 'Finding the right tool'],
            ['id' => 'free',     'label' => $it ? 'È davvero gratis?' : 'Is it really free?'],
            ['id' => 'account',  'label' => $it ? 'Profilo e registrazione' : 'Profile and registration'],
            ['id' => 'privacy',  'label' => $it ? 'Privacy e dati' : 'Privacy and data'],
            ['id' => 'app',      'label' => $it ? 'App Android' : 'Android app'],
            ['id' => 'status',   'label' => $it ? 'Stato dei sistemi' : 'System status'],
            ['id' => 'contacts', 'label' => $it ? 'Assistenza e contatti' : 'Support and contacts'],
            ['id' => 'human',    'label' => $it ? 'Parlare con una persona' : 'Talk to a person'],
        ];
    }

    /** La domanda iniziale: in quale lingua procedere (in italiano e in inglese). */
    function lux_guide_lang_prompt(): array
    {
        return [
            'question_it' => 'In quale lingua vuoi procedere?',
            'question_en' => 'In which language would you like to continue?',
            'options' => [
                ['id' => 'it', 'label' => 'Italiano'],
                ['id' => 'en', 'label' => 'English'],
            ],
        ];
    }

    /** Compone il testo di una voce con i numeri veri del sito. */
    function lux_guide_answer(string $id, string $lang): array
    {
        $entries = lux_guide_entries();
        if (!isset($entries[$id])) return ['ok' => false];

        $e  = $entries[$id];
        $it = $lang !== 'en';

        $status = 'ok';
        if (function_exists('lux_status_report')) {
            $rep = lux_status_report(false);
            $status = (string)($rep['state'] ?? 'ok');
        }
        $statusWord = $it
            ? ['ok' => 'tutto regolare', 'warn' => 'alcuni avvisi minori', 'down' => 'alcuni sistemi segnalano problemi'][$status] ?? 'tutto regolare'
            : ['ok' => 'all good', 'warn' => 'a few minor warnings', 'down' => 'some systems report problems'][$status] ?? 'all good';

        $vars = [
            '{n}'      => lux_guide_tool_count($lang),
            '{mail}'   => lux_contact_email(),
            '{appver}' => '39.0.0',
            '{status}' => $statusWord,
        ];

        $body = strtr($it ? (string)$e['answer_it'] : (string)$e['answer_en'], $vars);

        return [
            'ok'       => true,
            'title'    => $it ? (string)$e['title_it'] : (string)$e['title_en'],
            'text'     => $body,
            'escalate' => !empty($e['escalate']),
        ];
    }

    /**
     * Prova a riconoscere una domanda scritta con parole proprie.
     * Ritorna l'id della voce migliore, oppure null se nessuna somiglia
     * abbastanza (in quel caso il bot la passa all'admin).
     */
    function lux_guide_match(string $text): ?string
    {
        $t = strtolower(trim($text));
        if ($t === '') return null;
        $t = preg_replace('/[^\p{L}\p{N}\s]/u', ' ', $t);
        $t = preg_replace('/\s+/', ' ', $t);

        $best = null; $bestScore = 0;
        foreach (lux_guide_entries() as $id => $e) {
            $score = 0;
            foreach ((array)$e['keywords'] as $kw) {
                $kw = strtolower($kw);
                // Si usa strpos/strlen di proposito: sul server l'estensione
                // mbstring non e' garantita, e con le parole chiave in ASCII
                // il confronto a byte basta.
                if ($kw !== '' && strpos($t, $kw) !== false) $score += max(1, strlen($kw));
            }
            if ($score > $bestScore) { $bestScore = $score; $best = $id; }
        }
        // Sotto la soglia non si inventa niente: si trasferisce all'admin.
        return $bestScore >= 3 ? $best : null;
    }

    /** Il benvenuto di L.U.X. GUIDE, dopo la scelta della lingua. */
    function lux_guide_welcome(string $lang): string
    {
        return $lang === 'en'
            ? 'Hi, I am L.U.X. GUIDE, the official guide of L.U.X. Verse. Pick an option and I will answer right away. If I do not understand something, I will pass the chat to a member of the staff.'
            : 'Ciao, sono L.U.X. GUIDE, la guida ufficiale di L.U.X. Verse. Scegli un\'opzione e ti rispondo subito. Se non capisco qualcosa, passo la chat a una persona dello staff.';
    }
}
