<?php
// ============================================================
// L.U.X. VERSE — CATALOGO GENERATO (99.000 strumenti, ZERO file)
// ============================================================
// Il catalogo non e' una lista salvata da nessuna parte: e' una
// moltiplicazione. 44 azioni x 45 argomenti x 50 preset = 99.000
// strumenti, e ognuno ha:
//   - un nome (LUX.RiassumiEmailFlash)
//   - uno slug unico (riassumi-email-flash.php)
//   - descrizioni IT/EN
//   - una scheda completa per il motore AI (campi, prompt, system)
//
// Perche' cosi': 99.000 schede su disco sarebbero centinaia di MB e un
// json_decode enorme a ogni pagina. Qui il costo di una richiesta e' di
// qualche lookup in array: lo slug di 3 parole viene diviso e cercato
// negli indici, quindi si risale all'indice in tempo costante.
//
// Le tre liste qui sotto sono l'unico dato da toccare: aggiungendo una
// voce alle azioni il catalogo cresce di 2.250 strumenti, senza sfiorare
// il resto del sito.
//
// Aggiungere uno strumento "vero" (con la sua pagina scritta a mano)
// resta possibile come prima: si mette la voce in data/projects.json e
// il suo spec in tool.php.
// ============================================================

if (!defined('LUX_CATALOG_LOADED')) {
    define('LUX_CATALOG_LOADED', true);

    /** Indirizzo pubblico del sito (unico posto in cui e' scritto). */
    function lux_site_url(): string
    {
        if (defined('LUX_SITE_URL')) return rtrim((string)LUX_SITE_URL, '/');
        $host = (string)($_SERVER['HTTP_HOST'] ?? '');
        if ($host === '' || !preg_match('/^[a-z0-9.:\-]+$/i', $host)) $host = 'luxverse.c22.eu';
        $https = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');
        return ($https ? 'https://' : 'http://') . $host;
    }

    // ------------------------------------------------------------
    // 1. AZIONI (44): il verbo dello strumento.
    //    token = parola dello slug, name = parola del nome pubblico,
    //    it = infinito per le descrizioni, en = verbo inglese.
    // ------------------------------------------------------------
    function lux_catalog_actions(): array
    {
        return [
            ['token' => 'riassumi',    'name' => 'Summarize',   'it' => 'riassumere',              'en' => 'summarize'],
            ['token' => 'riscrivi',    'name' => 'Rewrite',     'it' => 'riscrivere',              'en' => 'rewrite'],
            ['token' => 'traduci',     'name' => 'Translate',   'it' => 'tradurre',                'en' => 'translate'],
            ['token' => 'spiega',      'name' => 'Explain',     'it' => 'spiegare',                'en' => 'explain'],
            ['token' => 'analizza',    'name' => 'Analyze',     'it' => 'analizzare',              'en' => 'analyze'],
            ['token' => 'genera',      'name' => 'Generate',    'it' => 'generare',                'en' => 'generate'],
            ['token' => 'pianifica',   'name' => 'Plan',        'it' => 'pianificare',             'en' => 'plan'],
            ['token' => 'ottimizza',   'name' => 'Optimize',    'it' => 'ottimizzare',             'en' => 'optimize'],
            ['token' => 'confronta',   'name' => 'Compare',     'it' => 'confrontare',             'en' => 'compare'],
            ['token' => 'estrai',      'name' => 'Extract',     'it' => 'estrarre dati da',        'en' => 'extract data from'],
            ['token' => 'converti',    'name' => 'Convert',     'it' => 'convertire',              'en' => 'convert'],
            ['token' => 'verifica',    'name' => 'Validate',    'it' => 'verificare',              'en' => 'validate'],
            ['token' => 'stima',       'name' => 'Estimate',    'it' => 'stimare',                 'en' => 'estimate'],
            ['token' => 'idee',        'name' => 'Brainstorm',  'it' => 'trovare idee su',         'en' => 'brainstorm'],
            ['token' => 'recensisci',  'name' => 'Review',      'it' => 'recensire',               'en' => 'review'],
            ['token' => 'valuta',      'name' => 'Score',       'it' => 'valutare con un punteggio', 'en' => 'score'],
            ['token' => 'semplifica',  'name' => 'Simplify',    'it' => 'semplificare',            'en' => 'simplify'],
            ['token' => 'amplia',      'name' => 'Expand',      'it' => 'ampliare',                'en' => 'expand'],
            ['token' => 'accorcia',    'name' => 'Shorten',     'it' => 'accorciare',              'en' => 'shorten'],
            ['token' => 'struttura',   'name' => 'Structure',   'it' => 'strutturare',             'en' => 'structure'],
            ['token' => 'organizza',   'name' => 'Organize',    'it' => 'organizzare',             'en' => 'organize'],
            ['token' => 'priorita',    'name' => 'Prioritize',  'it' => 'mettere in ordine di priorità', 'en' => 'prioritize'],
            ['token' => 'debug',       'name' => 'Debug',       'it' => 'trovare errori in',       'en' => 'debug'],
            ['token' => 'bozza',       'name' => 'Draft',       'it' => 'scrivere una bozza di',   'en' => 'draft'],
            ['token' => 'consiglia',   'name' => 'Recommend',   'it' => 'consigliare',             'en' => 'recommend'],
            ['token' => 'rifinisci',   'name' => 'Refine',      'it' => 'rifinire',                'en' => 'refine'],
            ['token' => 'simula',      'name' => 'Simulate',    'it' => 'simulare',                'en' => 'simulate'],
            ['token' => 'correggi',    'name' => 'Correct',     'it' => 'correggere',              'en' => 'correct'],
            ['token' => 'scaletta',    'name' => 'Outline',     'it' => 'fare la scaletta di',     'en' => 'outline'],
            ['token' => 'unisci',      'name' => 'Merge',       'it' => 'unire',                   'en' => 'merge'],
            ['token' => 'dividi',      'name' => 'Split',       'it' => 'dividere',                'en' => 'split'],
            ['token' => 'programma',   'name' => 'Schedule',    'it' => 'programmare',             'en' => 'schedule'],
            ['token' => 'prevedi',     'name' => 'Forecast',    'it' => 'prevedere l\'andamento di', 'en' => 'forecast'],
            ['token' => 'revisiona',   'name' => 'Audit',       'it' => 'fare un controllo su',    'en' => 'audit'],
            ['token' => 'allena',      'name' => 'Coach',       'it' => 'allenare su',             'en' => 'coach'],
            ['token' => 'quiz',        'name' => 'Quiz',        'it' => 'creare un quiz su',       'en' => 'quiz'],
            ['token' => 'report',      'name' => 'Report',      'it' => 'redigere un report su',   'en' => 'report on'],
            ['token' => 'presenta',    'name' => 'Pitch',       'it' => 'presentare',              'en' => 'pitch'],
            ['token' => 'automatizza', 'name' => 'Automate',    'it' => 'automatizzare',           'en' => 'automate'],
            ['token' => 'monitora',    'name' => 'Monitor',     'it' => 'monitorare',              'en' => 'monitor'],
            ['token' => 'localizza',   'name' => 'Localize',    'it' => 'adattare al mercato locale', 'en' => 'localize'],
            ['token' => 'migliora',    'name' => 'Improve',     'it' => 'migliorare',              'en' => 'improve'],
            ['token' => 'classifica',  'name' => 'Classify',    'it' => 'classificare',            'en' => 'classify'],
            ['token' => 'formatta',    'name' => 'Format',      'it' => 'formattare',              'en' => 'format'],
        ];
    }

    // ------------------------------------------------------------
    // 2. ARGOMENTI (45): il campo di lavoro. 9 per categoria, cosi'
    //    i filtri della home restano bilanciati.
    //    token = parola dello slug, name = parola del nome pubblico,
    //    it/en = etichetta, itPh/itPhEn = frase per le descrizioni.
    // ------------------------------------------------------------
    function lux_catalog_topics(): array
    {
        return [
            // --- utility ---
            ['token' => 'email',    'name' => 'Email',    'it' => 'Email',           'en' => 'Email',         'itPh' => 'email e messaggi',            'enPh' => 'emails and messages',      'cat' => 'utility',      'icon' => 'mail',       'color' => '#D8A93C'],
            ['token' => 'contratti','name' => 'Contract', 'it' => 'Contratti',       'en' => 'Contracts',     'itPh' => 'contratti e accordi',         'enPh' => 'contracts and agreements', 'cat' => 'utility',      'icon' => 'scroll',     'color' => '#6E9BC4'],
            ['token' => 'fatture',  'name' => 'Invoice',  'it' => 'Fatture',         'en' => 'Invoices',      'itPh' => 'fatture e preventivi',        'enPh' => 'invoices and quotes',      'cat' => 'utility',      'icon' => 'calc',       'color' => '#57B98A'],
            ['token' => 'dati',     'name' => 'Data',     'it' => 'Dati',            'en' => 'Data',          'itPh' => 'dati e dataset',              'enPh' => 'data and datasets',        'cat' => 'utility',      'icon' => 'cpu',        'color' => '#6E9BC4'],
            ['token' => 'sql',      'name' => 'Sql',      'it' => 'Query SQL',       'en' => 'SQL queries',   'itPh' => 'query SQL e database',        'enPh' => 'SQL queries and databases','cat' => 'utility',      'icon' => 'code',       'color' => '#6E9BC4'],
            ['token' => 'python',   'name' => 'Python',   'it' => 'Codice Python',   'en' => 'Python code',   'itPh' => 'script Python',               'enPh' => 'Python scripts',           'cat' => 'utility',      'icon' => 'code',       'color' => '#57B98A'],
            ['token' => 'regex',    'name' => 'Regex',    'it' => 'Espressioni regolari', 'en' => 'Regex patterns', 'itPh' => 'espressioni regolari e pattern', 'enPh' => 'regular expressions and patterns', 'cat' => 'utility', 'icon' => 'search', 'color' => '#F0CE86'],
            ['token' => 'ricette',  'name' => 'Recipe',   'it' => 'Ricette',         'en' => 'Recipes',       'itPh' => 'ricette e menu',              'enPh' => 'recipes and menus',        'cat' => 'utility',      'icon' => 'dish',       'color' => '#D96C5B'],
            ['token' => 'viaggi',   'name' => 'Travel',   'it' => 'Viaggi',          'en' => 'Travel',        'itPh' => 'viaggi e itinerari',          'enPh' => 'trips and itineraries',    'cat' => 'utility',      'icon' => 'globe',      'color' => '#6E9BC4'],

            // --- produttivita ---
            ['token' => 'curriculum','name' => 'Resume',    'it' => 'Curriculum',      'en' => 'Resumes',        'itPh' => 'curriculum e CV',            'enPh' => 'resumes and CVs',          'cat' => 'produttivita', 'icon' => 'resume',    'color' => '#57B98A'],
            ['token' => 'riunioni', 'name' => 'Meeting',    'it' => 'Riunioni',        'en' => 'Meetings',       'itPh' => 'riunioni e call',            'enPh' => 'meetings and calls',       'cat' => 'produttivita', 'icon' => 'users',     'color' => '#6E9BC4'],
            ['token' => 'report',   'name' => 'Report',     'it' => 'Report',          'en' => 'Reports',        'itPh' => 'report e analisi',           'enPh' => 'reports and analyses',     'cat' => 'produttivita', 'icon' => 'chart',     'color' => '#D8A93C'],
            ['token' => 'slide',    'name' => 'Slides',     'it' => 'Presentazioni',   'en' => 'Presentations',  'itPh' => 'presentazioni e slide',      'enPh' => 'presentations and slides', 'cat' => 'produttivita', 'icon' => 'board',     'color' => '#D8A93C'],
            ['token' => 'fogli',    'name' => 'Sheet',      'it' => 'Fogli di calcolo','en' => 'Spreadsheets',   'itPh' => 'fogli di calcolo e tabelle', 'enPh' => 'spreadsheets and tables',  'cat' => 'produttivita', 'icon' => 'grid',      'color' => '#57B98A'],
            ['token' => 'lavoro',   'name' => 'Job',        'it' => 'Lavoro',          'en' => 'Hiring',         'itPh' => 'offerte di lavoro e selezione', 'enPh' => 'job offers and hiring', 'cat' => 'produttivita', 'icon' => 'brief',     'color' => '#D8A93C'],
            ['token' => 'budget',   'name' => 'Budget',     'it' => 'Budget',          'en' => 'Budgets',        'itPh' => 'budget e risparmi',          'enPh' => 'budgets and savings',      'cat' => 'produttivita', 'icon' => 'calc',      'color' => '#57B98A'],
            ['token' => 'impresa',  'name' => 'Business',   'it' => 'Impresa',         'en' => 'Business',       'itPh' => 'piani d\'impresa',           'enPh' => 'business plans',           'cat' => 'produttivita', 'icon' => 'brief',     'color' => '#D8A93C'],
            ['token' => 'prezzi',   'name' => 'Pricing',    'it' => 'Prezzi',          'en' => 'Pricing',        'itPh' => 'prezzi e offerte',           'enPh' => 'prices and offers',        'cat' => 'produttivita', 'icon' => 'tag',       'color' => '#57B98A'],

            // --- creativita ---
            ['token' => 'blog',     'name' => 'Blog',       'it' => 'Articoli blog',   'en' => 'Blog posts',     'itPh' => 'articoli di blog',           'enPh' => 'blog posts',               'cat' => 'creativita',   'icon' => 'write',     'color' => '#F0CE86'],
            ['token' => 'social',   'name' => 'Social',     'it' => 'Post social',     'en' => 'Social posts',   'itPh' => 'post per i social',          'enPh' => 'social media posts',       'cat' => 'creativita',   'icon' => 'hash',      'color' => '#D96C5B'],
            ['token' => 'annunci',  'name' => 'Ads',        'it' => 'Annunci',         'en' => 'Ads',            'itPh' => 'annunci e campagne',         'enPh' => 'ads and campaigns',        'cat' => 'creativita',   'icon' => 'megaphone', 'color' => '#D96C5B'],
            ['token' => 'newsletter','name' => 'News',      'it' => 'Newsletter',      'en' => 'Newsletters',    'itPh' => 'newsletter e aggiornamenti', 'enPh' => 'newsletters and updates',  'cat' => 'creativita',   'icon' => 'send',      'color' => '#6E9BC4'],
            ['token' => 'video',    'name' => 'Video',      'it' => 'Video',           'en' => 'Video',          'itPh' => 'video e copioni per reel',   'enPh' => 'videos and reel scripts',  'cat' => 'creativita',   'icon' => 'film',      'color' => '#D96C5B'],
            ['token' => 'canzoni',  'name' => 'Lyrics',     'it' => 'Canzoni',         'en' => 'Songs',          'itPh' => 'testi di canzoni',           'enPh' => 'song lyrics',              'cat' => 'creativita',   'icon' => 'music',     'color' => '#D8A93C'],
            ['token' => 'logo',     'name' => 'Logo',       'it' => 'Loghi',           'en' => 'Logos',          'itPh' => 'loghi e identità visiva',  'enPh' => 'logos and visual identity','cat' => 'creativita',   'icon' => 'palette',   'color' => '#D8A93C'],
            ['token' => 'marca',    'name' => 'Brand',      'it' => 'Marchio',         'en' => 'Branding',       'itPh' => 'nomi di marca e domini',     'enPh' => 'brand names and domains',  'cat' => 'creativita',   'icon' => 'pin',       'color' => '#F0CE86'],
            ['token' => 'storie',   'name' => 'Story',      'it' => 'Storie',          'en' => 'Stories',        'itPh' => 'storie e racconti',          'enPh' => 'stories and tales',        'cat' => 'creativita',   'icon' => 'book-open', 'color' => '#F0CE86'],

            // --- educativi ---
            ['token' => 'temi',     'name' => 'Essay',      'it' => 'Temi',            'en' => 'Essays',         'itPh' => 'temi e testi scolastici',    'enPh' => 'essays and school texts',  'cat' => 'educativi',    'icon' => 'pen',       'color' => '#D8A93C'],
            ['token' => 'tesi',     'name' => 'Thesis',     'it' => 'Tesi',            'en' => 'Theses',         'itPh' => 'tesi e ricerca',             'enPh' => 'theses and research',      'cat' => 'educativi',    'icon' => 'book',      'color' => '#D8A93C'],
            ['token' => 'studio',   'name' => 'Study',      'it' => 'Piano di studio', 'en' => 'Study plans',    'itPh' => 'piani di studio e appunti',  'enPh' => 'study plans and notes',    'cat' => 'educativi',    'icon' => 'gradcap',   'color' => '#57B98A'],
            ['token' => 'esami',    'name' => 'Exam',       'it' => 'Esami',           'en' => 'Exams',          'itPh' => 'esami e verifiche',          'enPh' => 'exams and tests',          'cat' => 'educativi',    'icon' => 'quiz',      'color' => '#F0CE86'],
            ['token' => 'lezioni',  'name' => 'Lesson',     'it' => 'Lezioni',         'en' => 'Lessons',        'itPh' => 'lezioni e didattica',        'enPh' => 'lessons and teaching',     'cat' => 'educativi',    'icon' => 'book-open', 'color' => '#57B98A'],
            ['token' => 'compiti',  'name' => 'Homework',   'it' => 'Compiti',         'en' => 'Homework',       'itPh' => 'compiti a casa',             'enPh' => 'homework',                 'cat' => 'educativi',    'icon' => 'clipboard','color' => '#F0CE86'],
            ['token' => 'lingue',   'name' => 'Languages',  'it' => 'Lingue',          'en' => 'Languages',      'itPh' => 'studio delle lingue',        'enPh' => 'language learning',        'cat' => 'educativi',    'icon' => 'translate', 'color' => '#6E9BC4'],
            ['token' => 'scienza',  'name' => 'Science',    'it' => 'Scienza',         'en' => 'Science',        'itPh' => 'scienza e laboratorio',      'enPh' => 'science and lab work',     'cat' => 'educativi',    'icon' => 'flask',     'color' => '#57B98A'],
            ['token' => 'libri',    'name' => 'Book',       'it' => 'Libri',           'en' => 'Books',          'itPh' => 'libri e riassunti',          'enPh' => 'books and summaries',      'cat' => 'educativi',    'icon' => 'books',     'color' => '#D8A93C'],

            // --- giochi ---
            ['token' => 'ideagame', 'name' => 'Game',       'it' => 'Idee di gioco',   'en' => 'Game ideas',     'itPh' => 'idee di giochi',             'enPh' => 'game ideas',               'cat' => 'giochi',       'icon' => 'gamepad',   'color' => '#D96C5B'],
            ['token' => 'arcade',   'name' => 'Arcade',     'it' => 'Arcade',          'en' => 'Arcade',         'itPh' => 'giochi arcade e punteggi',   'enPh' => 'arcade games and scores',  'cat' => 'giochi',       'icon' => 'invader',   'color' => '#6E9BC4'],
            ['token' => 'tavolo',   'name' => 'Board',      'it' => 'Giochi da tavolo','en' => 'Board games',    'itPh' => 'giochi da tavolo',           'enPh' => 'board games',              'cat' => 'giochi',       'icon' => 'dice',      'color' => '#D8A93C'],
            ['token' => 'enigma',   'name' => 'Puzzle',     'it' => 'Enigmi',          'en' => 'Puzzles',        'itPh' => 'enigmi e rompicapi',         'enPh' => 'puzzles and riddles',      'cat' => 'giochi',       'icon' => 'puzzle',    'color' => '#F0CE86'],
            ['token' => 'campagna', 'name' => 'Campaign',   'it' => 'Campagne',        'en' => 'Campaigns',      'itPh' => 'campagne di gioco di ruolo', 'enPh' => 'role-playing campaigns',   'cat' => 'giochi',       'icon' => 'chess',     'color' => '#D96C5B'],
            ['token' => 'quizgioco','name' => 'Trivia',     'it' => 'Quiz',            'en' => 'Trivia',         'itPh' => 'quiz e indovinelli',         'enPh' => 'quizzes and trivia',       'cat' => 'giochi',       'icon' => 'help',      'color' => '#D8A93C'],
            ['token' => 'carte',    'name' => 'Cards',      'it' => 'Giochi di carte', 'en' => 'Card games',     'itPh' => 'giochi di carte',            'enPh' => 'card games',               'cat' => 'giochi',       'icon' => 'cards',     'color' => '#D96C5B'],
            ['token' => 'festa',    'name' => 'Party',      'it' => 'Feste',           'en' => 'Parties',        'itPh' => 'giochi per feste e serate',  'enPh' => 'party games and nights',   'cat' => 'giochi',       'icon' => 'party',     'color' => '#F0CE86'],
            ['token' => 'lore',     'name' => 'Lore',       'it' => 'Storie di gioco', 'en' => 'Game lore',      'itPh' => 'storie e lore di gioco',     'enPh' => 'game stories and lore',    'cat' => 'giochi',       'icon' => 'spiral',    'color' => '#6E9BC4'],
        ];
    }

    // ------------------------------------------------------------
    // 3. PRESET (50): il taglio del risultato. Non cambiano il verbo,
    //    cambiano come deve uscire la risposta.
    // ------------------------------------------------------------
    function lux_catalog_presets(): array
    {
        return [
            ['token' => 'flash',   'name' => 'Flash',   'it' => 'rapido, poche righe',            'en' => 'fast, just a few lines',        'hint_it' => 'Rispondi in modo rapido e conciso: poche righe, nessun preambolo.',           'hint_en' => 'Answer quickly and concisely: a few lines, no preamble.'],
            ['token' => 'deep',    'name' => 'Deep',    'it' => 'approfondito, con esempi',       'en' => 'in depth, with examples',       'hint_it' => 'Approfondisci: aggiungi dettagli, esempi concreti e le motivazioni.',          'hint_en' => 'Go deep: add details, concrete examples and the reasoning.'],
            ['token' => 'pro',     'name' => 'Pro',     'it' => 'professionale, pronto da usare', 'en' => 'professional, ready to use',    'hint_it' => 'Usa un tono professionale e consegna il risultato pronto da usare cosi\' com\'e\'.', 'hint_en' => 'Use a professional tone and deliver the result ready to use as it is.'],
            ['token' => 'zen',     'name' => 'Zen',     'it' => 'essenziale, senza fronzoli',     'en' => 'essential, no fluff',           'hint_it' => 'Togli tutto il superfluo: solo l\'essenziale, frasi brevi.',                   'hint_en' => 'Remove everything unnecessary: only the essentials, short sentences.'],
            ['token' => 'bold',    'name' => 'Bold',    'it' => 'diretto e deciso',               'en' => 'direct and bold',               'hint_it' => 'Sii diretto e deciso, senza giri di parole.',                                  'hint_en' => 'Be direct and decisive, no beating around the bush.'],
            ['token' => 'sharp',   'name' => 'Sharp',   'it' => 'tecnico e preciso',              'en' => 'technical and precise',         'hint_it' => 'Sii tecnico e preciso: termini corretti, nessuna approssimazione.',            'hint_en' => 'Be technical and precise: correct terms, no approximation.'],
            ['token' => 'warm',    'name' => 'Warm',    'it' => 'cordiale e umano',               'en' => 'warm and human',                'hint_it' => 'Usa un tono cordiale e umano, come una persona che aiuta un\'altra.',          'hint_en' => 'Use a warm, human tone, like one person helping another.'],
            ['token' => 'calm',    'name' => 'Calm',    'it' => 'chiaro e rassicurante',          'en' => 'clear and reassuring',          'hint_it' => 'Spiega in modo chiaro e rassicurante, passo dopo passo.',                      'hint_en' => 'Explain clearly and reassuringly, step by step.'],
            ['token' => 'nova',    'name' => 'Nova',    'it' => 'creativo e originale',           'en' => 'creative and original',         'hint_it' => 'Proponi soluzioni creative e originali, mai sentite.',                         'hint_en' => 'Dare to be creative: propose original solutions you rarely see.'],
            ['token' => 'atlas',   'name' => 'Atlas',   'it' => 'strutturato in sezioni',         'en' => 'structured in sections',        'hint_it' => 'Organizza la risposta in sezioni con titoli brevi.',                           'hint_en' => 'Organize the answer into sections with short headings.'],
            ['token' => 'lista',   'name' => 'List',    'it' => 'in elenco puntato',              'en' => 'as a bullet list',              'hint_it' => 'Consegna il risultato come elenco puntato.',                                   'hint_en' => 'Deliver the result as a bullet list.'],
            ['token' => 'tabella', 'name' => 'Table',   'it' => 'in tabella',                     'en' => 'as a table',                    'hint_it' => 'Consegna il risultato come tabella con colonne chiare.',                       'hint_en' => 'Deliver the result as a table with clear columns.'],
            ['token' => 'passi',   'name' => 'Steps',   'it' => 'passo per passo',                'en' => 'step by step',                  'hint_it' => 'Procedi passo per passo, numerando ogni passaggio.',                           'hint_en' => 'Go step by step, numbering each passage.'],
            ['token' => 'brief',   'name' => 'Brief',   'it' => 'sintesi esecutiva',              'en' => 'as an executive brief',         'hint_it' => 'Scrivi una sintesi esecutiva: le conclusioni prima dei dettagli.',            'hint_en' => 'Write an executive brief: conclusions before details.'],
            ['token' => 'draft',   'name' => 'Draft',   'it' => 'bozza completa',                 'en' => 'as a full draft',               'hint_it' => 'Consegna una bozza completa e modificabile, non un semplice schema.',          'hint_en' => 'Deliver a complete, editable draft, not just an outline.'],
            ['token' => 'tiny',    'name' => 'Tiny',    'it' => 'massimo 100 parole',             'en' => '100 words maximum',             'hint_it' => 'Rispetta un limite di 100 parole.',                                            'hint_en' => 'Keep it under 100 words.'],
            ['token' => 'wide',    'name' => 'Wide',    'it' => 'versione ampia',                 'en' => 'wide version',                  'hint_it' => 'Scrivi una versione ampia e dettagliata, senza limiti di lunghezza.',          'hint_en' => 'Write a wide, detailed version, with no length limit.'],
            ['token' => 'kids',    'name' => 'Kids',    'it' => 'semplice, per ragazzi',          'en' => 'simple, for young readers',     'hint_it' => 'Usa parole semplici, adatte a ragazzi delle medie.',                           'hint_en' => 'Use simple words suitable for middle-school readers.'],
            ['token' => 'edu',     'name' => 'Edu',     'it' => 'taglio didattico',               'en' => 'teaching oriented',             'hint_it' => 'Dai un taglio didattico: spiega il perche\' di ogni cosa.',                    'hint_en' => 'Give it a teaching angle: explain the why behind everything.'],
            ['token' => 'biz',     'name' => 'Biz',     'it' => 'in chiave business',             'en' => 'business oriented',             'hint_it' => 'Inquadra tutto in chiave business: obiettivi, costi, risultati.',              'hint_en' => 'Frame everything in business terms: goals, costs, results.'],
            ['token' => 'formale', 'name' => 'Formal',  'it' => 'tono formale',                   'en' => 'formal tone',                   'hint_it' => 'Usa un registro formale e istituzionale.',                                     'hint_en' => 'Use a formal, institutional register.'],
            ['token' => 'email',   'name' => 'Email',   'it' => 'pronto come email',              'en' => 'email ready',                   'hint_it' => 'Scrivi il risultato come una email gia\' pronta, con oggetto e corpo.',        'hint_en' => 'Write the result as a ready-to-send email, with subject and body.'],
            ['token' => 'post',    'name' => 'Post',    'it' => 'pronto per i social',            'en' => 'social ready',                  'hint_it' => 'Scrivi il risultato pronto per i social: gancio iniziale, testo breve, hashtag.', 'hint_en' => 'Write the result social-ready: hook, short text, hashtags.'],
            ['token' => 'slide',   'name' => 'Slide',   'it' => 'pronto per le slide',            'en' => 'slide ready',                   'hint_it' => 'Struttura il risultato come slide: una idea per slide, titolo e punti.',        'hint_en' => 'Structure the result as slides: one idea per slide, title and bullets.'],
            ['token' => 'doc',     'name' => 'Doc',     'it' => 'pronto come documento',          'en' => 'document ready',                'hint_it' => 'Formatta il risultato come un documento ordinato, con titolo e sezioni.',      'hint_en' => 'Format the result as an orderly document, with title and sections.'],
            ['token' => 'chat',    'name' => 'Chat',    'it' => 'conversazionale',                'en' => 'conversational',                'hint_it' => 'Usa un tono conversazionale, come in una chat.',                               'hint_en' => 'Use a conversational tone, as in a chat.'],
            ['token' => 'qa',      'name' => 'Qa',      'it' => 'domande e risposte',             'en' => 'question and answer',           'hint_it' => 'Rispondi in formato domanda e risposta.',                                      'hint_en' => 'Answer in question-and-answer format.'],
            ['token' => 'check',   'name' => 'Check',   'it' => 'sotto forma di checklist',       'en' => 'as a checklist',                'hint_it' => 'Trasforma il risultato in una checklist con caselle da spuntare.',             'hint_en' => 'Turn the result into a checklist with tick boxes.'],
            ['token' => 'mappa',   'name' => 'Map',     'it' => 'come mappa concettuale',         'en' => 'as a mind map',                 'hint_it' => 'Organizza il risultato come mappa concettuale, dal centro verso i rami.',      'hint_en' => 'Organize the result as a mind map, from the center outwards.'],
            ['token' => 'piano',   'name' => 'Plan',    'it' => 'con piano d\'azione',            'en' => 'with an action plan',           'hint_it' => 'Chiudi con un piano d\'azione: cosa fare, in che ordine, entro quando.',       'hint_en' => 'End with an action plan: what to do, in what order, by when.'],
            ['token' => 'numeri',  'name' => 'Data',    'it' => 'con numeri e dati',              'en' => 'with numbers and data',         'hint_it' => 'Sostieni ogni affermazione con numeri, stime o dati.',                          'hint_en' => 'Support every claim with numbers, estimates or data.'],
            ['token' => 'fonti',   'name' => 'Cite',    'it' => 'con fonti e riferimenti',        'en' => 'with sources and references',   'hint_it' => 'Indica fonti affidabili o il tipo di fonte da consultare per ogni punto.',     'hint_en' => 'Point to reliable sources, or the type of source to check, for each point.'],
            ['token' => 'semplice','name' => 'Plain',   'it' => 'senza gergo tecnico',            'en' => 'without jargon',                'hint_it' => 'Evita il gergo tecnico: spiega come se parlassi a una persona nuova.',         'hint_en' => 'Avoid jargon: explain as if talking to a newcomer.'],
            ['token' => 'italiano','name' => 'Ita',     'it' => 'in italiano impeccabile',        'en' => 'in flawless Italian',           'hint_it' => 'Scrivi la risposta in italiano impeccabile.',                                  'hint_en' => 'Write the answer in flawless Italian.'],
            ['token' => 'inglese', 'name' => 'Eng',     'it' => 'in inglese naturale',            'en' => 'in natural English',            'hint_it' => 'Scrivi la risposta in inglese naturale.',                                      'hint_en' => 'Write the answer in natural English.'],
            ['token' => 'bilingue','name' => 'Dual',    'it' => 'in italiano e inglese',          'en' => 'in Italian and English',        'hint_it' => 'Scrivi la risposta prima in italiano e poi in inglese.',                       'hint_en' => 'Write the answer first in Italian and then in English.'],
            ['token' => 'seo',     'name' => 'Seo',     'it' => 'ottimizzato per la ricerca',     'en' => 'search optimized',              'hint_it' => 'Ottimizza per la ricerca: parole chiave naturali, titoli chiari.',             'hint_en' => 'Optimize for search: natural keywords, clear titles.'],
            ['token' => 'adv',     'name' => 'Ads',     'it' => 'pensato per la pubblicità',    'en' => 'ad oriented',                   'hint_it' => 'Pensa come chi vende: beneficio chiaro, invito all\'azione.',                   'hint_en' => 'Think like a seller: clear benefit, call to action.'],
            ['token' => 'pitch',   'name' => 'Pitch',   'it' => 'per convincere',                 'en' => 'persuasive pitch',              'hint_it' => 'Scrivi per convincere: argomenti forti e obiezioni anticipate.',               'hint_en' => 'Write to persuade: strong arguments and anticipated objections.'],
            ['token' => 'storia',  'name' => 'Story',   'it' => 'raccontato come una storia',     'en' => 'told as a story',               'hint_it' => 'Racconta il risultato come una storia: inizio, svolta, conclusione.',          'hint_en' => 'Tell the result as a story: setup, turn, conclusion.'],
            ['token' => 'test',    'name' => 'Test',    'it' => 'in stile verifica',              'en' => 'test style',                    'hint_it' => 'Presenta il risultato come una verifica: domande prima, soluzioni dopo.',      'hint_en' => 'Present the result as a test: questions first, solutions after.'],
            ['token' => 'teach',   'name' => 'Teach',   'it' => 'per spiegare in aula',           'en' => 'to teach in class',             'hint_it' => 'Scrivi materiale pronto per una spiegazione in aula.',                         'hint_en' => 'Write material ready for a classroom explanation.'],
            ['token' => 'sprint',  'name' => 'Sprint',  'it' => 'piano da 30 minuti',             'en' => '30-minute sprint',              'hint_it' => 'Progetta il lavoro in una sessione da 30 minuti.',                             'hint_en' => 'Design the work as a single 30-minute session.'],
            ['token' => 'weekly',  'name' => 'Weekly',  'it' => 'programma settimanale',          'en' => 'weekly plan',                   'hint_it' => 'Distribuisci il risultato su una settimana, giorno per giorno.',               'hint_en' => 'Spread the result across a week, day by day.'],
            ['token' => 'costi',   'name' => 'Cost',    'it' => 'attento ai costi',               'en' => 'cost aware',                    'hint_it' => 'Tieni i costi bassi o a zero: preferisci soluzioni economiche.',               'hint_en' => 'Keep costs low or at zero: prefer cheap solutions.'],
            ['token' => 'rischi',  'name' => 'Risk',    'it' => 'con rischi e contromisure',      'en' => 'with risks and mitigations',    'hint_it' => 'Elenca i rischi e come ridurli.',                                              'hint_en' => 'List the risks and how to reduce them.'],
            ['token' => 'versus',  'name' => 'Versus',  'it' => 'con pro e contro',               'en' => 'with pros and cons',            'hint_it' => 'Metti in evidenza pro e contro, in modo equilibrato.',                         'hint_en' => 'Highlight pros and cons in a balanced way.'],
            ['token' => 'punteggio','name' => 'Score',  'it' => 'con punteggi e valutazioni',     'en' => 'with scores',                   'hint_it' => 'Assegna punteggi (da 1 a 10) con una breve motivazione.',                      'hint_en' => 'Assign scores (1 to 10) with a short reason.'],
            ['token' => 'locale',  'name' => 'Local',   'it' => 'adattato al contesto locale',    'en' => 'locally adapted',               'hint_it' => 'Adatta il risultato al contesto italiano: riferimenti, norme, abitudini.',     'hint_en' => 'Adapt the result to the Italian context: references, rules, habits.'],
            ['token' => 'scala',   'name' => 'Scale',   'it' => 'pronto a crescere',              'en' => 'built to scale',                'hint_it' => 'Progetta pensando a quando il volume crescera\': cosa cambia e cosa regge.',   'hint_en' => 'Design with growth in mind: what changes and what holds up.'],
        ];
    }

    // ------------------------------------------------------------
    // 4. INDICI (token => posizione). Costruiti una volta sola.
    // ------------------------------------------------------------
    function lux_catalog_axis(string $axis): array
    {
        static $cache = [];
        if (isset($cache[$axis])) return $cache[$axis];

        switch ($axis) {
            case 'action': $rows = lux_catalog_actions(); break;
            case 'topic':  $rows = lux_catalog_topics();  break;
            default:       $rows = lux_catalog_presets(); break;
        }

        $byToken = [];
        foreach ($rows as $i => $row) {
            $byToken[(string)$row['token']] = $i;
        }
        $cache[$axis] = ['rows' => $rows, 'map' => $byToken];
        return $cache[$axis];
    }

    /** Numero di strumenti generati: 44 x 45 x 50. */
    function lux_catalog_generated_count(): int
    {
        return count(lux_catalog_actions()) * count(lux_catalog_topics()) * count(lux_catalog_presets());
    }

/**
     * Gli strumenti che hanno una pagina propria in data/projects.json.
     *
     * Nel catalogo NON esiste piu' la distinzione "scritto a mano" /
     * "generato": sono tutti strumenti L.U.X. allo stesso livello. Questa
     * funzione serve solo a sapere da dove arriva la voce (per la pagina e
     * per l'indice), non a metterla in una categoria a parte.
     */
    function lux_catalog_roots_count(): int
    {
        if (!function_exists('getProjectsFromLocal')) return 0;
        static $n = null;
        if ($n === null) $n = count(getProjectsFromLocal());
        return $n;
    }

    function lux_catalog_total(): int
    {
        return lux_catalog_generated_count() + lux_catalog_roots_count();
    }

    /**
     * URL vecchia -> slug nuovo.
     *
     * Il catalogo e' stato ricostruito (970 voci non erano piu' una copia
     * dell'altra), ma le vecchie URL erano gia' indicizzate: qui si trova la
     * voce nuova per rispondere con un reindirizzamento, non con un 404.
     */
    function lux_catalog_alias(string $slug): string
    {
        static $map = null;
        if ($map === null) {
            $map = [];
            $file = __DIR__ . '/../data/aliases.json';
            if (is_file($file)) {
                $data = json_decode((string)@file_get_contents($file), true);
                if (is_array($data)) $map = $data;
            }
        }
        $slug = strtolower(trim($slug));
        return isset($map[$slug]) ? (string)$map[$slug] : '';
    }

    // ------------------------------------------------------------
    // 5. DA INDICE A SCHEDA (e ritorno)
    // ------------------------------------------------------------

    /** Indice piatto -> coordinate (azione, argomento, preset). */
    function lux_catalog_coords(int $index): ?array
    {
        $A = count(lux_catalog_actions());
        $T = count(lux_catalog_topics());
        $P = count(lux_catalog_presets());
        if ($index < 0 || $index >= $A * $T * $P) return null;
        $a = intdiv($index, $T * $P);
        $r = $index % ($T * $P);
        return ['a' => $a, 't' => intdiv($r, $P), 'p' => $r % $P];
    }

    /** Slug pubblico: "riassumi-email-flash" (unico per costruzione). */
    function lux_catalog_slug(?int $index): string
    {
        if ($index === null) return '';
        $c = lux_catalog_coords($index);
        if (!$c) return '';
        $A = lux_catalog_axis('action')['rows'];
        $T = lux_catalog_axis('topic')['rows'];
        $P = lux_catalog_axis('preset')['rows'];
        return $A[$c['a']]['token'] . '-' . $T[$c['t']]['token'] . '-' . $P[$c['p']]['token'];
    }

    /** Slug -> indice. null se lo slug non esiste (niente 404 casuali). */
    function lux_catalog_index_from_slug(string $slug): ?int
    {
        $slug = strtolower(trim($slug));
        if ($slug === '' || substr_count($slug, '-') !== 2) return null;

        $parts = explode('-', $slug);
        $A = lux_catalog_axis('action');
        $T = lux_catalog_axis('topic');
        $P = lux_catalog_axis('preset');
        if (!isset($A['map'][$parts[0]], $T['map'][$parts[1]], $P['map'][$parts[2]])) return null;

        $a = (int)$A['map'][$parts[0]];
        $t = (int)$T['map'][$parts[1]];
        $p = (int)$P['map'][$parts[2]];
        return ($a * count($T['rows']) + $t) * count($P['rows']) + $p;
    }

    /**
     * Scheda completa di uno strumento generato, nella forma usata da
     * tool.php (stessa struttura degli altri strumenti automatici).
     */
    function lux_catalog_item(int $index): ?array
    {
        $c = lux_catalog_coords($index);
        if (!$c) return null;

        $A = lux_catalog_axis('action')['rows'];
        $T = lux_catalog_axis('topic')['rows'];
        $P = lux_catalog_axis('preset')['rows'];
        $act = $A[$c['a']];
        $top = $T[$c['t']];
        $pre = $P[$c['p']];

        $name     = 'LUX.' . $act['name'] . $top['name'] . $pre['name'];
        $descIt   = 'Strumento L.U.X. per ' . $act['it'] . ' ' . $top['itPh'] . '. Modalità ' . $pre['name'] . ': ' . $pre['it'] . '.';
        $descEn   = 'L.U.X. tool to ' . $act['en'] . ' ' . $top['enPh'] . '. Mode ' . $pre['name'] . ': ' . $pre['en'] . '.';

        return [
            'index'   => $index,
            'slug'    => $act['token'] . '-' . $top['token'] . '-' . $pre['token'],
            'name'    => $name,
            'icon'    => $top['icon'],
            'color'   => $top['color'],
            'category'=> $top['cat'],
            'desc_it' => $descIt,
            'desc_en' => $descEn,
            'action'  => $act,
            'topic'   => $top,
            'preset'  => $pre,
        ];
    }

    // ------------------------------------------------------------
    // 6. RICERCA E PAGINAZIONE
    // ------------------------------------------------------------
    // Senza ricerca si va a salti: l'indice di pagina e' matematica pura.
    // Con ricerca si scorre il catalogo con confronti su stringhe piccole
    // (nessun oggetto creato finche' la riga non corrisponde), quindi il
    // costo resta basso anche su 99.000 voci.
    // ------------------------------------------------------------

    /** Quanti strumenti generati esistono per categoria (istantaneo). */
    function lux_catalog_counts(): array
    {
        $T = lux_catalog_axis('topic')['rows'];
        $perTopic = count(lux_catalog_actions()) * count(lux_catalog_presets());
        $out = [];
        foreach ($T as $t) {
            $cat = (string)$t['cat'];
            $out[$cat] = ($out[$cat] ?? 0) + $perTopic;
        }
        return $out;
    }

    /**
     * Gli strumenti con pagina propria (data/projects.json).
     * Le schede si leggono una volta sola; lo stato si risolve ogni volta,
     * perche' il pannello puo' cambiare 100.000 stati in una sola richiesta
     * e la pagina deve mostrarli subito (senza cache vecchie).
     */
    function lux_catalog_roots(): array
    {
        static $raw = null;
        if ($raw === null) {
            $raw = [];
            if (function_exists('getProjectsFromLocal')) {
                foreach (getProjectsFromLocal() as $p) {
                    if (!is_array($p)) continue;
                    $key = (string)($p['key'] ?? '');
                    if ($key === '') continue;
                    $p['short'] = $key;
                    $p['url']   = (string)($p['url'] ?? lux_tool_url($key));
                    $raw[$key]  = $p;
                }
            }
        }
        $rows = [];
        foreach ($raw as $key => $p) {
            $p['status'] = function_exists('lux_project_state') ? lux_project_state($key) : 'attivo';
            $rows[] = $p;
        }
        return $rows;
    }

    /** Nome precedente, tenuto per compatibilita' con i file che lo usano. */
    function lux_catalog_curated(): array
    {
        return lux_catalog_roots();
    }

    /** Gli stati che il pannello puo' assegnare a uno strumento. */
    function lux_catalog_states(): array
    {
        return ['attivo', 'manutenzione', 'pausa', 'costruzione', 'verifica', 'bloccato', 'archiviato', 'disattivato'];
    }

    /** Le categorie reali del catalogo, nell'ordine in cui si mostrano. */
    function lux_catalog_categories(): array
    {
        return ['creativita', 'produttivita', 'educativi', 'giochi', 'utility'];
    }

    /**
     * Quanti strumenti, in TUTTO il catalogo, hanno un certo stato.
     *
     * Non scorre i 99.000 generati: quelli condividono lo stato della loro
     * categoria (o quello generale), quindi bastano 45 letture piu' le
     * eccezioni scritte a mano. Resta istantaneo anche a catalogo pieno.
     */
    function lux_catalog_status_counts(): array
    {
        $out = array_fill_keys(lux_catalog_states(), 0);
        $bump = function (string $s) use (&$out) {
            if (!isset($out[$s])) $out[$s] = 0;
            $out[$s]++;
        };

        // 1. le voci con pagina propria
        foreach (lux_catalog_roots() as $p) {
            $bump((string)($p['status'] ?? 'attivo'));
        }

        // 2. i generati: una lettura per categoria, poi il peso della categoria
        $perTopic = count(lux_catalog_actions()) * count(lux_catalog_presets());
        $cats = [];
        foreach (lux_catalog_topics() as $t) {
            $cats[(string)$t['cat']] = ($cats[(string)$t['cat']] ?? 0) + $perTopic;
        }
        foreach ($cats as $cat => $n) {
            $s = lux_generated_state($cat);
            if (!isset($out[$s])) $out[$s] = 0;
            $out[$s] += $n;
        }

        // 3. eccezioni: singoli strumenti generati cambiati a mano
        foreach (lux_states() as $key => $row) {
            if ($key === '' || $key[0] === '_') continue;
            if (lux_catalog_index_from_slug((string)$key) === null) continue;
            $item = lux_catalog_item((int)lux_catalog_index_from_slug((string)$key));
            if (!$item) continue;
            $fallback = lux_generated_state((string)$item['category']);
            $now      = (string)($row['status'] ?? 'attivo');
            if ($fallback === $now) continue;
            if (isset($out[$fallback])) $out[$fallback]--;
            if (!isset($out[$now])) $out[$now] = 0;
            $out[$now]++;
        }

        return $out;
    }

    /**
     * Applica uno stato a un intero gruppo del catalogo.
     * $scope: 'all' | 'roots' | 'generated' | 'category:<slug>'
     * Ritorna quanti strumenti ha toccato.
     */
    function lux_catalog_apply_status(string $scope, string $status): int
    {
        if (!in_array($status, lux_catalog_states(), true)) return 0;

        $states = lux_states();
        $now    = date('Y-m-d H:i:s');
        $n      = 0;

        $setRoots = function (?string $onlyCat) use (&$states, &$n, $status, $now) {
            foreach (lux_catalog_roots() as $p) {
                $key = (string)($p['key'] ?? '');
                if ($key === '') continue;
                if ($onlyCat !== null && (string)($p['category'] ?? '') !== $onlyCat) continue;
                $states[$key] = ['status' => $status, 'timestamp' => $now];
                $n++;
            }
        };

        if ($scope === 'all') {
            $states['__generated__'] = ['status' => $status, 'timestamp' => $now];
            // le eccezioni per categoria non servono piu': vale per tutti
            foreach (array_keys($states) as $k) {
                if (strpos((string)$k, '__gencat__:') === 0) unset($states[$k]);
            }
            $n += lux_catalog_generated_count();
            $setRoots(null);
        } elseif ($scope === 'generated') {
            $states['__generated__'] = ['status' => $status, 'timestamp' => $now];
            foreach (array_keys($states) as $k) {
                if (strpos((string)$k, '__gencat__:') === 0) unset($states[$k]);
            }
            $n += lux_catalog_generated_count();
        } elseif ($scope === 'roots') {
            $setRoots(null);
        } elseif (strpos($scope, 'category:') === 0) {
            $cat = preg_replace('/[^a-z0-9_\-]/i', '', substr($scope, 9));
            if ($cat === '') return 0;
            $states['__gencat__:' . $cat] = ['status' => $status, 'timestamp' => $now];
            $perTopic = count(lux_catalog_actions()) * count(lux_catalog_presets());
            $perCat   = 0;
            foreach (lux_catalog_topics() as $t) {
                if ((string)$t['cat'] === $cat) $perCat++;
            }
            $n += $perCat * $perTopic;
            $setRoots($cat);
        }

        $GLOBALS['__lux_states'] = $states;
        lux_states_write($states);
        return $n;
    }

    /** Conteggio per categoria: tutte le voci del catalogo, insieme. */
    function lux_catalog_counts_all(): array
    {
        $out = lux_catalog_counts();
        foreach (lux_catalog_roots() as $p) {
            $cat = (string)($p['category'] ?? 'utility');
            $out[$cat] = ($out[$cat] ?? 0) + 1;
        }
        return $out;
    }

    /**
     * Pagina di catalogo: prima i 1000 strumenti scritti a mano, poi i
     * generati. $opts: page, per, cat, q.
     * Ritorna le voci in formato "progetto" (lo stesso che usa la home).
     */
    function lux_catalog_page(array $opts = []): array
    {
        $per  = (int)($opts['per'] ?? 24);
        if ($per < 1)  $per = 24;
        if ($per > 60) $per = 60;
        $page = (int)($opts['page'] ?? 1);
        if ($page < 1) $page = 1;
        $cat  = (string)($opts['cat'] ?? '');
        if ($cat === 'all') $cat = '';
        $q     = strtolower(trim((string)($opts['q'] ?? '')));
        $words = $q === '' ? [] : array_values(array_filter(preg_split('/\s+/', $q)));
        $full  = $words ? (1 << count($words)) - 1 : 0;

        $A = lux_catalog_axis('action')['rows'];
        $T = lux_catalog_axis('topic')['rows'];
        $P = lux_catalog_axis('preset')['rows'];
        $nA = count($A); $nT = count($T); $nP = count($P);

        // ---- 1. strumenti scritti a mano che passano il filtro ----
        $curated = [];
        foreach (lux_catalog_roots() as $row) {
            if ($cat !== '' && (string)($row['category'] ?? '') !== $cat) continue;
            if ($words) {
                $hay = strtolower(($row['key'] ?? '') . ' ' . ($row['name'] ?? '') . ' '
                     . ($row['description_it'] ?? '') . ' ' . ($row['description_en'] ?? ''));
                $ok = true;
                foreach ($words as $w) {
                    if (strpos($hay, $w) === false) { $ok = false; break; }
                }
                if (!$ok) continue;
            }
            $curated[] = $row;
        }

        // ---- 2. quali argomenti entrano (filtro categoria, zero cicli) ----
        $topicOk = [];
        $topicsIn= 0;
        foreach ($T as $ti => $t) {
            $ok = ($cat === '' || (string)$t['cat'] === $cat);
            $topicOk[$ti] = $ok;
            if ($ok) $topicsIn++;
        }

        // ---- 3. totale dei generati ----
        $genTotal = $topicsIn * $nA * $nP;

        // ---- 4. la finestra richiesta nella lista unita ----
        $offset  = ($page - 1) * $per;
        $items   = array_slice($curated, $offset, $per);
        $missing = $per - count($items);

        if ($missing > 0) {
            $genStart = max(0, $offset - count($curated));
            $picked   = 0;
            $seen     = 0;   // posizione progressiva nel solo catalogo generato

            if (!$words) {
                // Nessuna ricerca: si salta a blocchi (argomento per argomento),
                // quindi la posizione di partenza e' matematica, non una scansione.
                $block = $nA * $nP;
                $skip  = $genStart;
                foreach ($T as $ti => $t) {
                    if (!$topicOk[$ti]) continue;
                    if ($skip >= $block) { $skip -= $block; continue; }
                    $base = $ti * $block;
                    while ($picked < $missing && $skip < $block) {
                        $items[] = lux_catalog_project(lux_catalog_item($base + $skip));
                        $picked++; $skip++;
                    }
                    if ($picked >= $missing) break;
                }
            } else {
                // Con la ricerca ogni voce delle tre liste diventa una maschera di
                // bit (una parola cercata = un bit): la combinazione entra se
                // azione OR argomento OR preset coprono tutti i bit. Sono due
                // operazioni intere per voce, nessuna stringa costruita a runtime.
                $mask = function (array $row) use ($words): int {
                    $hay = strtolower(implode(' ', $row));
                    $m = 0;
                    foreach ($words as $bit => $w) {
                        if (strpos($hay, $w) !== false) $m |= (1 << $bit);
                    }
                    return $m;
                };
                $mA = [];
                foreach ($A as $i => $row) $mA[$i] = $mask([$row['token'], $row['name'], $row['it'], $row['en']]);
                $mT = [];
                foreach ($T as $i => $row) $mT[$i] = $mask([$row['token'], $row['name'], $row['it'], $row['en'], $row['itPh'], $row['enPh']]);
                $mP = [];
                foreach ($P as $i => $row) $mP[$i] = $mask([$row['token'], $row['name'], $row['it'], $row['en']]);

                // L'indice piatto e' (azione * argomenti + argomento) * preset + preset:
                // lo stesso ordine di lux_catalog_coords().
                $genTotal = 0;
                for ($i = 0; $i < $nA; $i++) {
                    $ma   = $mA[$i];
                    $offA = $i * $nT * $nP;
                    for ($t = 0; $t < $nT; $t++) {
                        if (!$topicOk[$t]) continue;
                        $m = $ma | $mT[$t];
                        if ($m === 0) continue;
                        $off = $offA + $t * $nP;
                        for ($p = 0; $p < $nP; $p++) {
                            if (($m | $mP[$p]) !== $full) continue;
                            if ($seen >= $genStart && $picked < $missing) {
                                $items[] = lux_catalog_project(lux_catalog_item($off + $p));
                                $picked++;
                            }
                            $seen++;
                            $genTotal++;
                        }
                    }
                }
            }
        }

        $total = count($curated) + $genTotal;

        return [
            'items' => $items,
            'total' => $total,
            'page'  => $page,
            'pages' => $total > 0 ? (int)ceil($total / $per) : 0,
            'per'   => $per,
        ];
    }

    /** Voce generata -> formato "progetto" (quello che usa la home). */
    function lux_catalog_project(array $item): array
    {
        $base = function_exists('lux_site_url') ? lux_site_url() : 'https://luxverse.c22.eu';
        // Lo stato arriva dal sistema unico del pannello: prima lo stato della
        // categoria, poi quello generale, poi la singola voce (se l'admin ne
        // ha cambiato una sola).
        $genState = function_exists('lux_project_state')
            ? lux_project_state((string)$item['slug'])
            : 'attivo';
        return [
            'key'         => (string)$item['slug'],
            'name'        => (string)$item['name'],
            'url'         => $base . '/' . $item['slug'] . '.php',
            'short'       => (string)$item['slug'],
            'description_it' => (string)$item['desc_it'],
            'description_en' => (string)$item['desc_en'],
            'description' => (string)$item['desc_en'],
            'icon'        => (string)$item['icon'],
            'color'       => (string)$item['color'],
            'category'    => (string)$item['category'],
            'status'      => $genState,
            'preset'      => (string)$item['preset']['name'],
            'action'      => (string)$item['action']['name'],
        ];
    }

    // ------------------------------------------------------------
    // 7. SCHEDA PER IL MOTORE (tool.php)
    // ------------------------------------------------------------
    // Stessa struttura degli altri strumenti automatici: due campi, prompt
    // in italiano e in inglese, motore AI condiviso. Niente di nuovo da
    // imparare: e' lo stesso motore di tutti gli altri strumenti.
    // ------------------------------------------------------------
    function lux_catalog_spec(string $slug): ?array
    {
        $index = lux_catalog_index_from_slug($slug);
        if ($index === null) return null;

        $item = lux_catalog_item($index);
        if (!$item) return null;

        $act = $item['action'];
        $top = $item['topic'];
        $pre = $item['preset'];

        return [
            'key'         => (string)$item['slug'],
            'name'        => (string)$item['name'],
            'icon'        => (string)$item['icon'],
            'category'    => (string)$item['category'],
            'engine'      => 'ai',
            'output'      => 'rich',
            'temperature' => 0.75,
            'max_tokens'  => 1800,
            'desc_it'     => (string)$item['desc_it'],
            'desc_en'     => (string)$item['desc_en'],
            'action_it'   => 'Genera ora',
            'action_en'   => 'Generate now',
            'result_it'   => 'Risultato',
            'result_en'   => 'Result',
            'cta_icon'    => 'sparkles',
            'fields'      => [
                [
                    'name'     => 'topic',
                    'type'     => 'textarea',
                    'label_it' => 'Su cosa deve lavorare?',
                    'label_en' => 'What should it work on?',
                    'rows'     => 7,
                    'required' => true,
                    'max'      => 3500,
                    'ph_it'    => 'Incolla il testo, descrivi la situazione o scrivi il tuo obiettivo...',
                    'ph_en'    => 'Paste the text, describe the situation or write your goal...',
                ],
                [
                    'name'     => 'context',
                    'type'     => 'text',
                    'label_it' => 'Dettagli extra (facoltativo)',
                    'label_en' => 'Extra details (optional)',
                    'max'      => 500,
                    'ph_it'    => 'Pubblico, tono, vincoli, scadenze...',
                    'ph_en'    => 'Audience, tone, constraints, deadlines...',
                ],
            ],
            'system_it'   => 'Sei "' . $item['name'] . '", uno strumento di L.U.X. Verse. La tua specialità: ' . $act['it'] . ' ' . $top['itPh'] . '. '
                             . 'Consegni sempre risultati concreti e utilizzabili, senza spiegare come sei fatto. '
                             . 'Se l\'utente non indica la lingua, rispondi in italiano.',
            'system_en'   => 'You are "' . $item['name'] . '", a L.U.X. Verse tool. Your speciality: ' . $act['en'] . ' ' . $top['enPh'] . '. '
                             . 'You always deliver concrete, usable results and never explain how you work. '
                             . 'If the user does not specify a language, answer in English.',
            'prompt_it'   => "Compito: " . $act['it'] . " " . $top['itPh'] . ".\n"
                             . "Taglio richiesto (" . $pre['name'] . "): " . $pre['hint_it'] . "\n\n"
                             . "Materiale fornito dall'utente:\n{topic}\n\n"
                             . "Dettagli extra:\n{context}",
            'prompt_en'   => "Task: " . $act['en'] . " " . $top['enPh'] . ".\n"
                             . "Required angle (" . $pre['name'] . "): " . $pre['hint_en'] . "\n\n"
                             . "Material provided by the user:\n{topic}\n\n"
                             . "Extra details:\n{context}",
        ];
    }

    /** Voci di catalogo collegate (stesso argomento, azioni diverse). */
    function lux_catalog_related(string $slug, int $howMany = 6): array
    {
        $index = lux_catalog_index_from_slug($slug);
        if ($index === null) return [];

        $c    = lux_catalog_coords($index);
        $A    = count(lux_catalog_actions());
        $T    = count(lux_catalog_topics());
        $P    = count(lux_catalog_presets());
        $out  = [];
        $seen = 0;
        for ($step = 1; $seen < $howMany && $step <= $A; $step++) {
            $a = ($c['a'] + $step) % $A;
            $idx = ($a * $T + $c['t']) * $P + $c['p'];
            $item = lux_catalog_item($idx);
            if ($item) { $out[] = lux_catalog_project($item); $seen++; }
        }
        return $out;
    }
}
