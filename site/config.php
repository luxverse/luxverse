<?php
// ============================================
// CONFIGURAZIONE GLOBALE WORKMEAI (v2 — ottimizzata)
// ============================================

// ============================================
// SEGRETI (password admin, token API, chiave Groq)
// ------------------------------------------------------------
// Si cercano in quest'ordine, e il PRIMO che esiste vince:
//   1. la variabile d'ambiente LUX_SECRETS_FILE (se un giorno servisse);
//   2. la CASSAFORTE: data/vault.local.php. Sta dentro data/, l'unica
//      cartella che questo hosting non lascia leggere al web (verificato:
//      ogni indirizzo /data/... risponde 403) e il file per giunta si
//      rifiuta da solo se qualcuno lo apre direttamente;
//   3. il vecchio config.local.php nella radice (serve solo allo sviluppo
//      in locale: sul server resta la cassaforte);
//   4. niente: in quel caso l'accesso all'admin e' CHIUSO (fail-closed) e
//      gli strumenti AI dicono che manca la chiave.
// Nel codice non c'e' piu' nessun valore di ripiego: chi legge questo file
// non trova ne' la password ne' la chiave.
// Per rigenerare password/token/chiave apri una volta admin_setup.php.
// ============================================
$__secret_files = [];
$__env = getenv('LUX_SECRETS_FILE');
if (is_string($__env) && $__env !== '') $__secret_files[] = $__env;
$__secret_files[] = __DIR__ . '/data/vault.local.php';
$__secret_files[] = __DIR__ . '/config.local.php';
$GLOBALS['lux_secrets_source'] = '';
foreach ($__secret_files as $__f) {
    if (!is_file($__f)) continue;
    require_once $__f;
    $GLOBALS['lux_secrets_source'] = $__f;
    break;
}
unset($__secret_files, $__f, $__env);

// ============================================
// BLOCCO DEL SITO (manutenzione)
// Prima di ogni altra cosa: se il blocco e' acceso si risponde 503 con la
// pagina di manutenzione e non si carica nient'altro (nemmeno il catalogo da
// 100.000 strumenti, che sarebbe lavoro inutile). Il pannello resta aperto,
// cosi' il blocco si spegne da li' senza toccare il codice.
// L'interruttore e' in data/maintenance.json.
// ============================================
require_once __DIR__ . '/includes/maintenance.php';
lux_maintenance_guard();

// Interfaccia condivisa in un unico file: icone SVG, header, footer e
// promo app (prima erano includes/icons.php + header + footer + app-promo).
// Definita QUI perche' config.php ha bisogno di icon() gia' per le sue pagine
// di avviso, e tutte le pagine caricano config.php.
require_once __DIR__ . '/includes/lux.php';

// Catalogo generato (99.000 strumenti calcolati, zero file per strumento).
// Definita QUI perche' serve a tool.php (le pagine degli strumenti), alla
// home, all'API e al pannello admin: un solo posto da caricare.
require_once __DIR__ . '/includes/catalog.php';

// Piattaforma condivisa: voti, uso, feedback, chat della guida, salute delle
// chiavi AI e freno alle richieste. La usano sito, API e pannello.
require_once __DIR__ . '/includes/lux_platform.php';

// La guida ufficiale L.U.X. GUIDE (a opzioni).
require_once __DIR__ . '/includes/lux_guide.php';

// Motore di orchestrazione (39.1.1): registro dei tool eseguibili,
// pianificatore, aggregatore e ripiego sul catalogo. Lo usano la home
// (barra a due modalita') e l'API pubblica.
require_once __DIR__ . '/includes/agent.php';

// Spazio personale (39.1.1): cronologia, preferiti, risultati salvati e
// codice personale che li ritrova su un altro dispositivo. Nessun account.
require_once __DIR__ . '/includes/space.php';

// Indirizzo pubblico del sito: unico posto in cui e' scritto.
if (!defined('LUX_SITE_URL')) define('LUX_SITE_URL', 'https://luxverse.c22.eu');

// --------------------------------------------------------------- -----------------
// PARTE STATICA DEL SITO (17/10/2026)
// ---------------------------------------------------------------------------
// Il sito e' diviso in due, ma l'indirizzo resta UNO SOLO: luxverse.c22.eu.
// Chi naviga non vede mai un dominio diverso.
//
//   · le pagine di contenuto fisso (privacy, termini, chi siamo, FAQ,
//     percorso del rilascio, linea del tempo, codice aperto, condividi)
//     sono file .html gia' pronti — nessun PHP, nessun database, nessun
//     calcolo: si aprono come si apre un'immagine. Vengono costruiti da
//     tools/build_static.php e caricati nella stessa cartella del sito, con
//     lo stesso indirizzo di sempre;
//   · il peso che serve a disegnarle (fogli di stile, script, imagini) sta
//     su una rete di distribuzione con copie in piu' parti del mondo: quello
//     e' il vero guadagno di velocita', ed e' il pezzo che il telefono
//     scarica ogni volta;
//   · i calcoli e il motore (IA, catalogo, spazio personale, API) restano qui
//     sul server, sotto lo stesso indirizzo.
//
// Perche' non far servire le pagine direttamente dalla rete esterna: il
// dominio e' ospitato qui, e un dominio puo' puntare a un solo posto. Se le
// pagine uscissero da un altro indirizzo, la barra del browser cambierebbe
// dominio — e non e' quello che vogliamo. Cosi' invece l'indirizzo non
// cambia mai e il lavoro del server si riduce comunque a consegnare un file.
//
// Gli indirizzi si costruiscono con lux_url() (includes/lux.php): toccare
// gli href a mano, pagina per pagina, e' il modo piu' sicuro per far finire
// un link nel posto sbagliato.
//
// Copia esterna: la stessa cartella `frontend/` generata dal costruttore
// viene pubblicata anche su una rete di distribuzione (pagine + file di
// lavoro). Serve come specchio veloce degli asset e come riserva: le pagine
// vere restano quelle del dominio.
if (!defined('LUX_STATIC_BASE')) define('LUX_STATIC_BASE', 'https://luxverse.github.io/luxverse');

// Le pagine statiche vivono sullo stesso dominio, come file .html.
// Mettendo a false questa riga tornerebbero a puntare alla copia esterna
// (utile solo per una prova).
if (!defined('LUX_STATIC_SELF')) define('LUX_STATIC_SELF', true);

// Le pagine che vengono costruite come file .html (e che quindi non hanno
// bisogno del PHP per essere lette). Il nome del file basta: la riscrittura
// dei link (lux_fix_links) riconosce anche i percorsi assoluti.
// Se una di queste pagine comincia a usare un modulo o un API, si toglie da
// qui e torna a essere una pagina normale del server.
if (!defined('LUX_STATIC_PAGES')) {
    define('LUX_STATIC_PAGES', [
        'privacy.php', 'terms.php', 'chi-siamo.php', 'faq.php',
        'roadmap.php', 'linea-del-tempo.php', 'codice-aperto.php', 'condividi.php',
    ]);
}

// PAGINE MESSE DA PARTE (17/10/2026)
// ---------------------------------------------------------------------------
// Centomila strumenti non possono essere costruiti tutti in anticipo: le
// pagine sarebbero circa 26 GB di file. Si fa un'altra strada, che dal punto
// di vista di chi visita il sito e' la stessa cosa: la PRIMA volta che una
// pagina viene aperta, il PHP la costruisce e ne lascia una copia esatta
// nella cartella qui sotto; dalla seconda volta in poi e' il server a
// consegnare quel file, senza eseguire niente.
//
// Cosi' ogni pagina diventa statica appena qualcuno la apre, e le pagine piu'
// visitate lo sono subito per tutti. Lo spazio cresce solo per le pagine che
// esistono davvero: nessun caricamento di 200.000 file.
//
// Per svuotare tutto (dopo un aggiornamento importante): basta cambiare il
// valore qui sotto, anche di un carattere. La cartella vecchia resta li' ma
// non viene piu' letta, e si puo' cancellare con calma.
if (!defined('LUX_CACHE_TAG')) define('LUX_CACHE_TAG', '2026-10-17a');
if (!defined('LUX_CACHE_DIR')) define('LUX_CACHE_DIR', 'cache/' . LUX_CACHE_TAG);

// Versione della PIATTAFORMA (il sito), cioe' il racconto del rilascio:
// oggi 39.1.1. NON e' la versione dell'app: quella (LUX_APP_VERSION) si
// cambia solo quando esce davvero un APK nuovo, altrimenti l'app installata
// propone un aggiornamento che non esiste.
if (!defined('LUX_SITE_VERSION')) define('LUX_SITE_VERSION', '39.1.1');

// Versione dell'ecosistema (mostrata nella schermata di caricamento e
// nell'app Android). Unico posto in cui e' scritta: si cambia qui.
if (!defined('LUX_APP_VERSION')) define('LUX_APP_VERSION', '39.2.0');
// Numero di versione che l'app confronta con il proprio: le due righe qui
// sopra e qui sotto si cambiano INSIEME quando si pubblica un APK nuovo.
// 39.0.2 = tastiera che non copre più il campo di scrittura, catalogo intero
// (100.000) dentro l'app, notifiche globali programmate nel fuso giusto.
// 39.1.2 = trasloco sul dominio nuovo (luxverse.c22.eu): l'app parla con il
// nuovo indirizzo. Chi ha la versione vecchia riceve l'avviso di aggiornare,
// perche' la vecchia punta a un indirizzo che adesso porta solo alla
// schermata del trasloco.
// 39.1.3 = niente piu' schermata nera: l'hosting mette davanti a ogni pagina
// una verifica che consegna una pagina dal corpo vuoto, e l'app la scambiava
// per "pagina caricata" chiudendo subito la schermata di avvio. Adesso la
// schermata di avvio resta finche' il sito vero non si e' disegnato, gli
// errori del server si spiegano invece di restare neri, e la versione nuova
// si chiede alla WebView (la chiamata diretta alle API riceveva la pagina di
// verifica, non il JSON).
// 39.2.0 = l'app e' stata rifatta da zero: un solo pacchetto nuovo
// (org.luxverse.app) che e' una finestra sulla pagina vera del sito, senzia
// copie di pagine dentro. Chi ha installato la versione vecchia (pacchetto
// com.workmeai.app) la tiene dov'e': sono due pacchetti diversi, con due
// firme diverse, e Android non sostituira' l'una con l'altra.
if (!defined('LUX_APP_VERSION_CODE')) define('LUX_APP_VERSION_CODE', 45);

// Hash BCRYPT della password admin. Se i segreti non sono stati caricati
// resta VUOTO: password_verify() con hash vuoto dice sempre "no", quindi
// senza file dei segreti l'accesso e' chiuso invece che aperto.
if (!defined('ADMIN_PASSWORD_HASH')) define('ADMIN_PASSWORD_HASH', '');

// Token API per comunicazioni interne (vuoto = porte chiuse, vedi checkApiToken)
if (!defined('API_TOKEN')) define('API_TOKEN', '');

// ============================================
// CONFIGURAZIONE GROQ AI
// ============================================
// Il motore AI non usa piu' UNA chiave ma un PICCOLO GRUPPO di chiavi
// (oggi quattro). Cosi' il sito non va fuori limite quando entra molta
// gente: se una chiave risponde "troppe richieste", la chiamata ripiega
// da sola sulla successiva, senza che l'utente veda niente. Il nome del
// modello resta lo stesso per tutte (GROQ_MODEL).
//
// Le chiavi si mettono nella cassaforte (data/vault.local.php) come
// GROQ_API_KEY_1 ... GROQ_API_KEY_4. GROQ_API_KEY resta come primo nome e
// come ripiego, per non rompere niente che la legga ancora.
if (!defined('GROQ_API_KEY')) define('GROQ_API_KEY', '');
if (!defined('GROQ_MODEL'))   define('GROQ_MODEL', 'openai/gpt-oss-120b');
if (!defined('GROQ_URL'))     define('GROQ_URL', 'https://api.groq.com/openai/v1/chat/completions');
if (!defined('GROQ_API_KEYS')) {
    $__gk = [];
    for ($__i = 1; $__i <= 8; $__i++) {
        $__name = 'GROQ_API_KEY_' . $__i;
        if (defined($__name)) {
            $__v = trim((string)constant($__name));
            if ($__v !== '') $__gk[] = $__v;
        }
    }
    if (!$__gk && defined('GROQ_API_KEY') && (string)GROQ_API_KEY !== '') {
        $__gk[] = (string)GROQ_API_KEY;
    }
    define('GROQ_API_KEYS', $__gk);
    unset($__gk, $__i, $__name, $__v);
}

// ============================================
// SISTEMA CONTATORE VISITE
// ============================================
// IMPORTANTE: Aggiungi qui il TUO IP per non contare le tue visite.
// Trova il tuo IP su: https://whatismyipaddress.com
// Puoi aggiungere più IP separati da virgola.
define('VISITS_IGNORED_IPS', [
    '195.181.179.86',  //  IP di Yeamin (PC principale)
]);

// ============================================
// FUNZIONI GLOBALI
// ============================================

// Verifica password admin.
// NON va chiamata direttamente dagli endpoint: quella che conta i tentativi
// e blocca chi insiste e' lux_admin_password_ok() qui sotto.
function verifyAdminPassword($password) {
    if ((string)ADMIN_PASSWORD_HASH === '') return false;   // segreti assenti: porta chiusa
    return password_verify($password, ADMIN_PASSWORD_HASH);
}

/**
 * Il file della cassaforte. Sta in data/, la cartella che il web non serve:
 * il .htaccess della radice rimanda 403 su tutto /data/, data/.htaccess
 * nega ogni file, e in piu' il file stesso si rifiuta di fare qualcosa se
 * viene aperto direttamente (vedi la prima riga di data/vault.local.php).
 */
function lux_secrets_file(): string
{
    return __DIR__ . '/data/vault.local.php';
}

/**
 * Dove scrivere i segreti: nella cassaforte se ci si scrive davvero (non
 * basta che la cartella esista: si prova), altrimenti nel config.local.php
 * della radice, che il .htaccess blocca comunque.
 */
function lux_secrets_write_target(): string
{
    $vault = lux_secrets_file();
    $dir   = dirname($vault);
    if (is_dir($dir) || @mkdir($dir, 0755, true)) {
        $probe = $dir . '/.scrivi-prova';
        if (@file_put_contents($probe, 'x') !== false) {
            @unlink($probe);
            return $vault;
        }
    }
    return __DIR__ . '/config.local.php';
}

/**
 * Com'e' messa la cassaforte dei segreti: da dove sono stati letti, se stanno
 * nella cartella protetta e se ci sono tutti. Non ritorna MAI i valori: solo
 * se ci sono o no. Serve al pannello per dire la verita' all'admin invece di
 * lasciarlo indovinare.
 */
function lux_secrets_status(): array
{
    $src   = (string)($GLOBALS['lux_secrets_source'] ?? '');
    $vault = lux_secrets_file();

    return [
        'loaded'   => $src !== '',
        'file'     => $src === '' ? '' : basename($src),
        'place'    => $src === '' ? '' : ($src === $vault ? 'vault' : 'root'),
        'password' => (string)ADMIN_PASSWORD_HASH !== '',
        'token'    => (string)API_TOKEN !== '',
        'groq'     => (string)GROQ_API_KEY !== '',
        'attempt_log_unwritable' => !empty($GLOBALS['lux_security_unwritable']),
    ];
}

// Verifica token API.
// Confronto a tempo costante (hash_equals): un confronto normale si ferma al
// primo carattere diverso, e dal tempo di risposta si puo' ricostruire il
// token un carattere per volta. Qui il tempo non cambia mai.
function verifyApiToken($token) {
    $token = (string)$token;
    if ($token === '') return false;
    return hash_equals((string)API_TOKEN, $token);
}

// ============================================
// UTILITY CONDIVISE (API + PAGINE)
// ============================================

// Header CORS/JSON per le API
function jsonCorsHeaders() {
    header('Content-Type: application/json');
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type');
}

// Risposta JSON standardizzata (termina l'esecuzione)
function jsonResponse($data, $httpCode = 200) {
    header('Content-Type: application/json');
    http_response_code($httpCode);
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

// Verifica token API da GET/POST.
// $required = true   il token è obbligatorio e deve combaciare
// $required = false  il token è opzionale (vuoto = ammesso)
function checkApiToken($required = true) {
    // Senza token configurato non si apre niente, nemmeno le porte che di
    // solito accettano una richiesta senza token: se manca il file dei
    // segreti, la scelta sicura e' "chiuso".
    if ((string)API_TOKEN === '') return false;
    $token = (string)($_GET['token'] ?? $_POST['token'] ?? '');
    if (!$required) return $token === '' || verifyApiToken($token);
    return verifyApiToken($token);
}

// Chiamata all'API Groq.
// $messages  = array ['role' => 'system'|'user', 'content' => '...']
// $overrides = parametri opzionali (temperature, max_tokens, model, ...)
// Ritorna ['success' => true, 'content' => '...'] oppure ['success' => false, 'error' => '...']
function askGroq($messages, $overrides = [])
{
    // Senza l'estensione cURL la chiamata non e' possibile: meglio un errore
    // chiaro che un errore fatale a meta' pagina.
    if (!function_exists('curl_init')) {
        return ['success' => false, 'error' => t('The cURL extension is not available on this server.')];
    }

    // Il gruppo di chiavi AI (oggi quattro). Se la cassaforte non e' stata
    // caricata, non parte nessuna richiesta e non si espone niente.
    $keys = function_exists('lux_ai_keys') ? lux_ai_keys() : [];
    if (!$keys && (string)GROQ_API_KEY !== '') $keys = [(string)GROQ_API_KEY];
    if (!$keys) {
        return ['success' => false, 'error' => t('The AI key is not configured on this server.')];
    }

    // Chiave preferita (1-based): il chatbot e la ricerca per esigenza
    // usano la quarta (GROQ_API_KEY_4), cosi' non consumano le chiavi degli
    // strumenti. Non e' obbligatoria: sotto si usano comunque tutte.
    $prefer = (int)($overrides['key'] ?? 0);
    unset($overrides['key']);

    // Ordine dei tentativi: prima la preferita, poi le altre a rotazione
    // (round robin). Cosi' il carico si distribuisce da solo e una chiave
    // scarica non blocca nessuno: si ripiega sulla successiva.
    $n     = count($keys);
    $order = [];
    if ($prefer >= 1 && $prefer <= $n) $order[] = $prefer - 1;
    $start = function_exists('lux_ai_next_index') ? lux_ai_next_index($n) : 0;
    for ($i = 0; $i < $n; $i++) {
        $idx = ($start + $i) % $n;
        if (!in_array($idx, $order, true)) $order[] = $idx;
    }

    $payload = array_merge([
        'model'       => GROQ_MODEL,
        'temperature' => 0.7,
        'max_tokens'  => 2048,
    ], $overrides, ['messages' => $messages]);

    $lastError = '';

    foreach ($order as $idx) {
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL            => GROQ_URL,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_HTTPHEADER     => [
                'Authorization: Bearer ' . $keys[$idx],
                'Content-Type: application/json',
            ],
            CURLOPT_POSTFIELDS => json_encode($payload),
            CURLOPT_TIMEOUT    => 60,
        ]);

        $t0        = microtime(true);
        $response  = curl_exec($ch);
        $httpCode  = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);
        $ms = (int)round((microtime(true) - $t0) * 1000);

        if ($curlError) {
            $lastError = 'cURL Error: ' . $curlError;
            if (function_exists('lux_ai_record')) lux_ai_record($idx, false, $ms, $lastError);
            continue;
        }
        if ($httpCode === 429) {
            // Limite di velocita' di questa chiave: si passa alla successiva
            // invece di far aspettare l'utente. Nel pannello si vede quale
            // chiave ha preso il limite.
            $lastError = t('Rate limit reached. Try again in 30 seconds.');
            if (function_exists('lux_ai_record')) lux_ai_record($idx, false, $ms, 'HTTP 429', true);
            continue;
        }
        if ($httpCode !== 200) {
            $lastError = 'Groq API Error: HTTP ' . $httpCode;
            if (function_exists('lux_ai_record')) lux_ai_record($idx, false, $ms, $lastError);
            continue;
        }

        $data    = json_decode($response, true);
        $content = $data['choices'][0]['message']['content'] ?? '';
        if ($content === '') {
            $lastError = t('Empty response');
            if (function_exists('lux_ai_record')) lux_ai_record($idx, false, $ms, $lastError);
            continue;
        }

        // Design coerente: nessun emoji dal modello. Nel testo libero gli emoji
        // diventano icone SVG del set L.U.X.; nei payload JSON vengono rimossi
        // (li' il markup romperebbe la struttura dei dati).
        $head = ltrim($content);
        $content = ($head !== '' && ($head[0] === '{' || $head[0] === '['))
            ? lux_strip_emoji($content)
            : lux_deemoji($content);

        if (function_exists('lux_ai_record')) lux_ai_record($idx, true, $ms, '');
        return ['success' => true, 'content' => $content, 'key' => $idx + 1];
    }

    return ['success' => false, 'error' => $lastError !== '' ? $lastError : t('The AI is temporarily unavailable.')];
}

// Estrae JSON (array o oggetto) da una risposta AI in formato testo
function extractJsonFromAI($content) {
    $direct = json_decode($content, true);
    if (is_array($direct)) return $direct;

    if (preg_match('/\[.*\]/s', $content, $matches)) {
        $decoded = json_decode($matches[0], true);
        if (is_array($decoded)) return $decoded;
    }
    if (preg_match('/\{.*\}/s', $content, $matches)) {
        $decoded = json_decode($matches[0], true);
        if (is_array($decoded)) return $decoded;
    }
    return null;
}

// ============================================
// SISTEMA LINGUA (i18n) — ITALIANO primario, INGLESE secondario
// Le chiavi sono stringhe in INGLESE: t('Home')  'Home'; se la lingua
// selezionata è l'italiano (default) viene usata la traduzione in lang/it.php.
// ============================================
define('DEFAULT_LANG', 'it');
define('SUPPORTED_LANGS', ['it', 'en']);

// Determina la lingua corrente: URL ?lang= > cookie > browser > default
function currentLang() {
    // Il cookie viene salvato una sola volta, presto, da luxApplyLangFromQuery():
    // qui non si toccano gli header (verrebbe dopo l'output -> warning + cookie perso).
    if (isset($_GET['lang']) && in_array($_GET['lang'], SUPPORTED_LANGS, true)) {
        return $_GET['lang'];
    }
    if (isset($_COOKIE['workme_lang']) && in_array($_COOKIE['workme_lang'], SUPPORTED_LANGS, true)) {
        return $_COOKIE['workme_lang'];
    }
    $browser = substr($_SERVER['HTTP_ACCEPT_LANGUAGE'] ?? '', 0, 2);
    return in_array($browser, SUPPORTED_LANGS, true) ? $browser : DEFAULT_LANG;
}

// Traduzione: t('Home')  traduzione italiana (default) o inglese
function t($key, $vars = []) {
    static $translations = null;
    if ($translations === null) {
        $lang = currentLang();
        // Tutte le lingue vivono in UN unico file (lang/lang.php), che contiene
        // 'it' e 'en': una sola lettura per richiesta invece di nove.
        $langFile = __DIR__ . '/lang/lang.php';
        $all = file_exists($langFile) ? require $langFile : [];
        $translations = $all[$lang] ?? $all[DEFAULT_LANG] ?? [];
    }
    $str = $translations[$key] ?? $key;
    if (!empty($vars)) {
        foreach ($vars as $k => $v) {
            $str = str_replace('{' . $k . '}', $v, $str);
        }
    }
    return $str;
}

// Traduzione sicura per JavaScript: come t(), ma con la stringa gia' pronta
// per essere stampata dentro un letterale JS tra apici singoli.
// Senza questo, un apostrofo nella traduzione (es. "L'AI sta preparando...")
// chiude la stringa e spegne tutto lo script della pagina.
function tjs($key, $vars = []) {
    return str_replace(
        ["\\", "'", "\r\n", "\n", "\r", '</'],
        ["\\\\", "\\'", '\\n', '\\n', '', '<\\/'],
        t($key, $vars)
    );
}

// Salva la lingua scelta in un cookie (1 anno)
function setLangCookie($lang) {
    if (!in_array($lang, SUPPORTED_LANGS)) return;
    if (headers_sent()) return;   // nessun warning se l'output e' gia' partito
    setcookie('workme_lang', $lang, [
        'expires'  => time() + 86400 * 365,
        'path'     => '/',
        'samesite' => 'Lax',
    ]);
}

// Applica subito la lingua richiesta con ?lang=it|en.
// Chiamata all'include di config.php: nessun output e' ancora stato inviato.
function luxApplyLangFromQuery(): void {
    if (!isset($_GET['lang'])) return;
    if (!in_array($_GET['lang'], SUPPORTED_LANGS, true)) return;
    $_COOKIE['workme_lang'] = $_GET['lang'];
    setLangCookie($_GET['lang']);
}
luxApplyLangFromQuery();

// ============================================
// SESSIONE PROTETTA + DIFESA DELL'ACCESSO ADMIN
// ============================================
// La sessione dell'admin vale come una chiave: va trattata come tale.
// Qui il cookie diventa HttpOnly (il JavaScript della pagina non lo puo'
// leggere, quindi non si puo' rubare con uno script iniettato), SameSite
// (un altro sito non puo' usare la sessione dell'admin per conto suo) e
// Secure quando la connessione e' cifrata. Inoltre use_strict_mode fa
// rifiutare gli ID di sessione inventati a tavolino.
//
// Uso: require_once __DIR__ . '/config.php';  POI  lux_session_start();

function lux_session_start(): void
{
    if (session_status() !== PHP_SESSION_NONE) return;
    if (headers_sent()) { @session_start(); return; }

    $https = (!empty($_SERVER['HTTPS']) && strtolower((string)$_SERVER['HTTPS']) !== 'off')
          || (strtolower((string)($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '')) === 'https');

    @ini_set('session.use_strict_mode', '1');
    @ini_set('session.use_only_cookies', '1');
    @ini_set('session.cookie_httponly', '1');
    // Durata della sessione lato server: 8 ore. Su questo hosting il valore
    // predefinito e' 24 minuti, e la sessione dell'admin veniva cancellata
    // mentre si stava ancora lavorando nel pannello. La scadenza vera per
    // chi non fa niente resta comunque 2 ore (vedi isAdminLoggedIn).
    @ini_set('session.gc_maxlifetime', '28800');
    session_set_cookie_params([
        'lifetime' => 0,
        'path'     => '/',
        'httponly' => true,
        'secure'   => $https,
        'samesite' => 'Lax',
    ]);
    @session_start();
}

/**
 * Token anti-CSRF del pannello.
 *
 * Vive nella sessione e viaggia in ogni chiamata del pannello. Il controllo
 * di provenienza (Origin/Referer) resta come seconda barriera, ma non e' piu'
 * l'unico modo di autorizzare una modifica: su alcuni hosting l'intestazione
 * Origin non arriva e il pannello si bloccava senza dire perche'.
 */
function lux_admin_csrf(): string
{
    lux_session_start();
    $t = (string)($_SESSION['lux_csrf'] ?? '');
    if ($t === '') {
        $t = bin2hex(random_bytes(16));
        $_SESSION['lux_csrf'] = $t;
    }
    return $t;
}

/** La richiesta porta il token anti-CSRF giusto? */
function lux_admin_csrf_ok(string $token): bool
{
    lux_session_start();
    $have = (string)($_SESSION['lux_csrf'] ?? '');
    return ($have !== '' && $token !== '' && hash_equals($have, $token));
}

/** Indirizzo di chi sta chiamando, per il registro attività (solo per i log). */
function lux_client_ip(): string
{
    $ip = (string)($_SERVER['HTTP_CF_CONNECTING_IP']
        ?? ($_SERVER['HTTP_X_FORWARDED_FOR'] ?? ($_SERVER['REMOTE_ADDR'] ?? '')));
    if (strpos($ip, ',') !== false) $ip = trim(explode(',', $ip)[0]);
    return ($ip !== '' && preg_match('/^[0-9a-fA-F:.]{3,45}$/', $ip)) ? $ip : 'unknown';
}

/**
 * Indirizzo vero di chi chiama, NON falsificabile: per il blocco dei
 * tentativi si usa solo REMOTE_ADDR, perche' un X-Forwarded-For inventato
 * permetterebbe di aggirare il blocco cambiando intestazione a ogni prova.
 */
function lux_guard_ip(): string
{
    $ip = (string)($_SERVER['REMOTE_ADDR'] ?? '');
    return ($ip !== '' && preg_match('/^[0-9a-fA-F:.]{3,45}$/', $ip)) ? $ip : 'unknown';
}

/** Riga nel registro attività (stessa forma che legge il pannello). */
function lux_log_activity(string $action, string $details): void
{
    $logs = lux_data_read('logs');
    $logs[] = [
        'id'         => uniqid('LOG_'),
        'action'     => $action,
        'details'    => $details,
        'ip'         => lux_client_ip(),
        'user_agent' => substr((string)($_SERVER['HTTP_USER_AGENT'] ?? ''), 0, 220),
        'timestamp'  => date('Y-m-d H:i:s'),
    ];
    if (count($logs) > 1000) $logs = array_slice($logs, -1000);
    lux_data_write('logs', $logs);
}

// ---------- Blocco dei tentativi di accesso ----------
// Senza questo, una password corta si puo' provare migliaia di volte al
// minuto. Regola: 5 errori in 15 minuti bloccano l'indirizzo per 30 minuti,
// 10 errori per 2 ore. Il file sta in data/, che il web non puo' leggere.

function lux_security_read(): array
{
    $file = __DIR__ . '/data/security.json';
    if (!is_file($file)) return [];
    $d = json_decode((string)@file_get_contents($file), true);
    return is_array($d) ? $d : [];
}

function lux_security_write(array $rows): bool
{
    $file = __DIR__ . '/data/security.json';
    if (!is_dir(dirname($file))) @mkdir(dirname($file), 0755, true);
    $ok = @file_put_contents($file, json_encode(array_values($rows), JSON_UNESCAPED_UNICODE), LOCK_EX) !== false;
    // Se non si riesce a scrivere, il blocco dei tentativi non funziona: il
    // pannello lo dice a chiare lettere invece di far credere che sia attivo.
    $GLOBALS['lux_security_unwritable'] = !$ok;
    return $ok;
}

/** Stato dell'indirizzo: quanti errori recenti e se e' ancora bloccato. */
function lux_login_state(string $ip): array
{
    $now = time();
    foreach (lux_security_read() as $r) {
        if (!is_array($r) || (string)($r['ip'] ?? '') !== $ip) continue;
        $fails = array_values(array_filter((array)($r['fails'] ?? []), function ($t) use ($now) {
            return ($now - (int)$t) < 900;          // si contano solo gli errori degli ultimi 15 minuti
        }));
        $until = (int)($r['blocked_until'] ?? 0);
        return [
            'fails'   => count($fails),
            'blocked' => $until > $now,
            'minutes' => (int)ceil(max(0, $until - $now) / 60),
        ];
    }
    return ['fails' => 0, 'blocked' => false, 'minutes' => 0];
}

/** Registra l'esito di un tentativo di accesso. */
function lux_login_record(string $ip, bool $ok): void
{
    $now   = time();
    $keep  = [];
    $fails = [];
    $glob  = [];
    foreach (lux_security_read() as $r) {
        if (!is_array($r)) continue;
        $who = (string)($r['ip'] ?? '');
        if ($who === $ip)          { $fails = (array)($r['fails'] ?? []); continue; }
        if ($who === '*generale')  { $glob  = (array)($r['fails'] ?? []); continue; }
        $keep[] = $r;
    }

    // Accesso riuscito con la password giusta: si azzera il contatore di
    // quell'indirizzo e anche quello generale (chi conosce la password vera
    // non deve restare fuori per colpa di chi ha provato prima di lui).
    if ($ok) { lux_security_write($keep); return; }

    $fails = array_values(array_filter($fails, function ($t) use ($now) {
        return ($now - (int)$t) < 900;
    }));
    $fails[] = $now;

    $until = 0;
    if (count($fails) >= 10)     $until = $now + 7200;   // 2 ore
    elseif (count($fails) >= 5)  $until = $now + 1800;   // 30 minuti

    $keep[] = [
        'ip'            => $ip,
        'fails'         => $fails,
        'blocked_until' => $until,
        'last'          => date('Y-m-d H:i:s'),
    ];

    // Contatore GENERALE degli errori (tutti gli indirizzi insieme). Serve a
    // accorgersi di un attacco condotto da tanti computer diversi. NON blocca
    // nessuno: un blocco generale permetterebbe a un estraneo di chiudere
    // fuori l'admin provando password sbagliate. Fa solo aumentare il costo
    // di ogni tentativo (vedi lux_admin_password_ok) e lo si vede nel pannello.
    $glob = array_values(array_filter($glob, function ($t) use ($now) {
        return ($now - (int)$t) < 900;
    }));
    $glob[] = $now;
    $keep[] = [
        'ip'            => '*generale',
        'fails'         => $glob,
        'blocked_until' => 0,
        'last'          => date('Y-m-d H:i:s'),
    ];

    if (count($keep) > 200) $keep = array_slice($keep, -200); // il registro non cresce all'infinito
    lux_security_write($keep);
}

// ---------- UNICA porta per la password dell'admin ----------
// Ogni endpoint che verifica la password (pannello, API, setup) passa di qui.
// Cosi' il blocco dei tentativi vale OVUNQUE: prima era attivo solo sulla
// pagina di accesso, e chi provava password sulla API non veniva contato.
//
// Cosa fa, in ordine:
//   1. se l'indirizzo e' bloccato, non valuta nemmeno il tentativo;
//   2. senza password non e' un tentativo: non si conta niente;
//   3. fa aspettare tanto piu' quanto piu' errori ci sono stati nel mondo
//      (mezzo secondo di base, fino a 2 secondi): provare password a raffica
//      costa tempo a chi prova, non a noi;
//   4. verifica (bcrypt, confronto a tempo costante) e registra l'esito.
function lux_admin_password_ok($password): bool
{
    $password = (string)$password;
    $ip       = lux_guard_ip();

    if (lux_login_state($ip)['blocked']) return false;   // bloccato: si aspetta
    if ($password === '')                return false;   // nessun tentativo da contare

    $glob  = lux_login_state('*generale');
    $delay = 0.5 + (min(20, (int)$glob['fails']) * 0.075);   // max ~2 secondi
    usleep((int)($delay * 1000000));

    $ok = verifyAdminPassword($password);
    lux_login_record($ip, $ok);

    if ($ok) {
        lux_log_activity('LOGIN_OK', 'Accesso riuscito dall\'indirizzo ' . $ip);
    } else {
        $st = lux_login_state($ip);
        lux_log_activity('LOGIN_FAILED', 'Password errata dall\'indirizzo ' . $ip
            . ($st['blocked'] ? ' — indirizzo bloccato per ' . $st['minutes'] . ' minuti' : ''));
    }
    return $ok;
}

// Verifica se admin è loggato (su tutto il sito).
// La sessione dell'admin non dura per sempre: 8 ore in totale e 2 ore di
// inattività. Oltre quel tempo si torna alla schermata di accesso, cosi' un
// computer lasciato aperto non resta una porta aperta per giorni.
function isAdminLoggedIn() {
    if (empty($_SESSION['admin_logged_in'])) return false;

    $now     = time();
    $started = (int)($_SESSION['admin_login_at'] ?? 0);
    $seen    = (int)($_SESSION['admin_seen_at'] ?? $started);

    if (($started > 0 && ($now - $started) > 28800) || ($seen > 0 && ($now - $seen) > 7200)) {
        unset($_SESSION['admin_logged_in'], $_SESSION['admin_login_at'], $_SESSION['admin_seen_at']);
        $GLOBALS['lux_admin_expired'] = true;
        return false;
    }

    $_SESSION['admin_seen_at'] = $now;
    return true;
}

// Carica progetti dal file locale
function getProjectsFromLocal() {
    $projectsFile = __DIR__ . '/data/projects.json';
    if (file_exists($projectsFile)) {
        $projects = json_decode(file_get_contents($projectsFile), true);
        return is_array($projects) ? $projects : [];
    }
    return [];
}

// ============================================
// LETTURA/SCRITTURA DEI DATI (un solo modo per tutti)
// ============================================
// Prima il pannello admin aveva le sue copie di readJson/writeJson, l'API le
// sue. Qui ci sono quelle condivise: il nome del file viene ripulito, quindi
// non si puo' uscire da data/ per sbaglio.
function lux_data_file(string $name): string
{
    $name = preg_replace('/[^a-z0-9_\-]/i', '', $name);
    return __DIR__ . '/data/' . $name . '.json';
}

function lux_data_read(string $name, array $fallback = []): array
{
    $file = lux_data_file($name);
    if (!is_file($file)) return $fallback;
    $raw  = (string)@file_get_contents($file);
    if (trim($raw) === '') return $fallback;
    $data = json_decode($raw, true);
    return is_array($data) ? $data : $fallback;
}

function lux_data_write(string $name, array $data): bool
{
    $file = lux_data_file($name);
    if (!is_dir(dirname($file))) @mkdir(dirname($file), 0755, true);
    return @file_put_contents(
        $file,
        json_encode(array_values($data), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
        LOCK_EX
    ) !== false;
}

/** Indirizzo pubblico di uno strumento del catalogo. */
function lux_tool_url(string $slug): string
{
    return (function_exists('lux_site_url') ? lux_site_url() : 'https://luxverse.c22.eu')
         . '/' . preg_replace('/[^a-z0-9_\-]/i', '', strtolower($slug)) . '.php';
}

// Verifica se uno strumento esiste.
// Tutti gli strumenti vivono in un unico file (tool.php): l'elenco ufficiale e'
// il catalogo data/projects.json, non piu' la presenza di un file per progetto.
function isProjectActive($projectKey) {
    $projectKey = (string)$projectKey;
    if ($projectKey === '') return false;

    // Pagine classiche rimaste come file vero (bacheca, admin, ...).
    if (file_exists(__DIR__ . '/' . $projectKey . '.php')) return true;

    static $keys = null;
    if ($keys === null) {
        $keys = [];
        foreach (getProjectsFromLocal() as $p) {
            $k = (string)($p['key'] ?? '');
            if ($k !== '') $keys[$k] = true;
        }
    }
    return isset($keys[$projectKey]);
}

// Ottieni lo stato di un progetto
function getProjectStatus($projectKey) {
    return isProjectActive($projectKey) ? 'attivo' : 'disattivato';
}

// Interroga l'HQ (solo se necessario, altrimenti usa file locale)
function getProjectsFromHQ() {
    return [
        'success' => true,
        'projects' => getProjectsFromLocal()
    ];
}

// ============================================
// BACHECA / AVVISI
// ============================================

/**
 * Legge gli avvisi della bacheca dal file locale, ordinati:
 * prima i fissati (pinned), poi per data decrescente.
 * $limit = 0  tutti.
 */
function getNoticesFromLocal($limit = 0) {
    $file = __DIR__ . '/data/notices.json';
    if (!file_exists($file)) return [];
    $notices = json_decode(file_get_contents($file), true);
    if (!is_array($notices)) return [];

    usort($notices, function ($a, $b) {
        $pa = !empty($a['pinned']) ? 1 : 0;
        $pb = !empty($b['pinned']) ? 1 : 0;
        if ($pa !== $pb) return $pb - $pa;
        return strcmp($b['date'] ?? '', $a['date'] ?? '');
    });

    if ($limit > 0) $notices = array_slice($notices, 0, $limit);
    return $notices;
}

/**
 * Meta di un avviso per la UI: classe CSS (.b-tag) + chiave di traduzione.
 */
function noticeTypeMeta($type) {
    $map = [
        'update'       => ['class' => 'update', 'label' => 'Update'],
        'news'         => ['class' => 'news',   'label' => 'New'],
        'success'      => ['class' => 'news',   'label' => 'New'],
        'tip'          => ['class' => 'news',   'label' => 'Tip'],
        'info'         => ['class' => '',       'label' => 'Information'],
        'information'  => ['class' => '',       'label' => 'Information'],
        'notice'       => ['class' => '',       'label' => 'Notice'],
        'notification' => ['class' => '',       'label' => 'Notification'],
        'alert'        => ['class' => 'alert',  'label' => 'Urgent'],
        'danger'       => ['class' => 'alert',  'label' => 'Urgent'],
        'urgent'       => ['class' => 'alert',  'label' => 'Urgent'],
        'warning'      => ['class' => 'alert',  'label' => 'Urgent'],
        'event'        => ['class' => 'event',  'label' => 'Event'],
        'maintenance'  => ['class' => 'event',  'label' => 'Maintenance'],
        'spotlight'    => ['class' => 'event',  'label' => 'Featured'],
        'featured'     => ['class' => 'event',  'label' => 'Featured'],
        'social'       => ['class' => 'social', 'label' => 'Follow us'],
    ];
    $t = strtolower(trim((string)$type));
    return $map[$t] ?? ['class' => '', 'label' => 'Information'];
}

// ============================================
// NOTIFICHE (in tempo reale: pubblico + admin)
// ============================================
// Un solo archivio (data/notifications.json) alimentato in tre modi:
//   1. dall'admin, a mano (nome, contenuto, categoria, reindirizzamento);
//   2. in automatico quando esce un avviso, entra un progetto o arriva una
//      richiesta/messaggio;
//   3. dal monitoraggio, quando una sonda peggiora.
// Il pubblico vede solo "audience=public"; l'admin vede anche le sue.

/** Categorie disponibili per una notifica (slug => etichetta). */
function lux_notification_categories(): array
{
    return [
        'info'        => 'Information',
        'update'      => 'Update',
        'news'        => 'New',
        'success'     => 'Success',
        'warning'     => 'Urgent',
        'danger'      => 'Urgent',
        'maintenance' => 'Maintenance',
        'event'       => 'Event',
        'spotlight'   => 'Featured',
        'system'      => 'System',
    ];
}

function lux_notifications_file(): string
{
    return __DIR__ . '/data/notifications.json';
}

/** Tutte le notifiche (in cache per la richiesta). */
function lux_notifications_read(): array
{
    if (isset($GLOBALS['__lux_notifs']) && is_array($GLOBALS['__lux_notifs'])) {
        return $GLOBALS['__lux_notifs'];
    }
    $list = [];
    $file = lux_notifications_file();
    if (is_file($file)) {
        $data = json_decode((string)file_get_contents($file), true);
        if (is_array($data)) $list = $data;
    }
    $GLOBALS['__lux_notifs'] = $list;
    return $list;
}

function lux_notifications_write(array $list): bool
{
    $file = lux_notifications_file();
    if (!is_dir(dirname($file))) @mkdir(dirname($file), 0755, true);
    return @file_put_contents(
        $file,
        json_encode(array_values($list), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
        LOCK_EX
    ) !== false;
}

/**
 * L'ora di uscita di una notifica, riportata al fuso del SERVER.
 *
 * Il campo "Programma per" del pannello è un datetime-local: contiene
 * l'ora dell'orologio di chi scrive (per esempio 13:27 italiane) senza
 * nessun fuso. Il server, però, vive in UTC: confrontare quella stringa con
 * date() significava nascondere la notifica fino alle 13:27 UTC, cioè sei
 * ore dopo — e per chi guardava il sito sembrava che il pannello non
 * mandasse niente, anche se la notifica era stata scritta.
 *
 * Qui l'ora viene convertita usando lo scostamento da UTC che manda il
 * browser (getTimezoneOffset): se il momento è già passato la notifica
 * diventa immediata, così non resta mai bloccata per errore.
 */
function lux_notification_publish_at(string $when, $clientOffsetMinutes = null): string
{
    $when = trim(str_replace('T', ' ', $when));
    if ($when === '') return '';
    if (!preg_match('/^\d{4}-\d{2}-\d{2}[ T]\d{2}:\d{2}(:\d{2})?$/', $when)) return '';
    if (strlen($when) === 16) $when .= ':00';
    $ts = strtotime($when);
    if ($ts === false) return '';
    if (is_numeric($clientOffsetMinutes)) {
        // getTimezoneOffset() del browser vale UTC meno ora locale, in minuti:
        // l'Italia d'estate è -120. Quindi istante UTC = ora locale + offset.
        // Prima si torna all'istante UTC, poi lo si scrive nel fuso del
        // server, che può essere un altro (qui la macchina vive in UTC).
        $ts += (int)$clientOffsetMinutes * 60;
        $ts += (int)date('Z');
    }
    // Già passata (o entro mezzo minuto): esce subito.
    if ($ts <= time() + 30) return '';
    return date('Y-m-d H:i:s', $ts);
}

/** Vero se una notifica programmata non deve ancora uscire. */
function lux_notification_pending(array $n): bool
{
    $p = trim((string)($n['publish_at'] ?? ''));
    if ($p === '') return false;
    $ts = strtotime($p);
    if ($ts === false) return false;
    // Un minuto di tolleranza: due orologi non segnano mai lo stesso secondo,
    // e un minuto di ritardo è meglio di una notifica che non esce.
    return $ts > time() + 60;
}

/** Aggiunge una notifica: la più recente resta in cima. */
function lux_notification_add(array $n): bool
{
    $list = lux_notifications_read();
    $n['id']       = (string)($n['id'] ?? ('N_' . substr(md5(uniqid('', true)), 0, 10)));
    $n['date']     = (string)($n['date'] ?? date('Y-m-d H:i:s'));
    $n['category'] = (string)($n['category'] ?? 'info');
    $n['audience'] = (string)($n['audience'] ?? 'public');
    $n['redirect'] = (string)($n['redirect'] ?? '');
    $n['auto']     = !empty($n['auto']);
    // L'ora programmata passa SEMPRE da qui: è l'unico punto in cui si
    // scrivono le notifiche, quindi nessun ingresso può dimenticarsene.
    $n['publish_at'] = lux_notification_publish_at(
        (string)($n['publish_at'] ?? ''),
        $n['publish_tz'] ?? null
    );
    unset($n['publish_tz']);
    $GLOBALS['__lux_notif_last_id'] = $n['id'];
    array_unshift($list, $n);
    if (count($list) > 200) $list = array_slice($list, 0, 200);
    $GLOBALS['__lux_notifs'] = $list;
    return lux_notifications_write($list);
}

/** Titolo/messaggio nella lingua della pagina, con ripiego sul campo generico. */
function lux_notification_text(array $n, string $field): string
{
    $it = trim((string)($n[$field . '_it'] ?? ''));
    $en = trim((string)($n[$field . '_en'] ?? ''));
    $gen = trim((string)($n[$field] ?? ''));
    if (currentLang() === 'en') return $en !== '' ? $en : ($gen !== '' ? $gen : $it);
    return $it !== '' ? $it : ($gen !== '' ? $gen : $en);
}

/** Quante notifiche sono visibili a un certo pubblico. */
function lux_notifications_count(string $audience = 'public'): int
{
    $n = 0;
    foreach (lux_notifications_read() as $row) {
        if ((string)($row['audience'] ?? 'public') === $audience) $n++;
    }
    return $n;
}

/**
 * Notifica automatica dal monitoraggio: se le sonde segnalano problemi, l'admin
 * riceve una notifica. Deduplicata (una per stato ogni 6 ore).
 */
function lux_notifications_sync_alerts(): int
{
    if (!function_exists('lux_status_report')) return 0;
    $rep   = lux_status_report(false);
    $state = (string)($rep['state'] ?? 'ok');
    if ($state === 'ok') return 0;

    foreach (lux_notifications_read() as $row) {
        if ((string)($row['source'] ?? '') !== 'monitor') continue;
        if ((string)($row['state'] ?? '') !== $state) continue;
        if (strtotime((string)($row['date'] ?? '')) > time() - 21600) return 0;
    }

    $down = (int)($rep['counters']['down'] ?? 0);
    $warn = (int)($rep['counters']['warn'] ?? 0);
    $isDown = ($state === 'down');
    return (int)lux_notification_add([
        'title_it'   => $isDown ? 'Problema rilevato dal monitoraggio' : 'Avvisi dal monitoraggio',
        'title_en'   => $isDown ? 'Problem detected by monitoring' : 'Monitoring warnings',
        'message_it' => $isDown
            ? 'Il controllo automatico segnala ' . $down . ' problemi aperti. Apri la pagina Stato per il dettaglio delle sonde.'
            : 'Il controllo automatico segnala ' . $warn . ' avvisi. Apri la pagina Stato per il dettaglio.',
        'message_en' => $isDown
            ? 'The automatic check reports ' . $down . ' open problems. Open the Status page for the probe details.'
            : 'The automatic check reports ' . $warn . ' warnings. Open the Status page for the details.',
        'title'      => $isDown ? 'Problem detected by monitoring' : 'Monitoring warnings',
        'message'    => $isDown
            ? 'The automatic check reports ' . $down . ' open problems.'
            : 'The automatic check reports ' . $warn . ' warnings.',
        'category'   => $isDown ? 'danger' : 'warning',
        'audience'   => 'admin',
        'redirect'   => '/status.php',
        'source'     => 'monitor',
        'state'      => $state,
        'auto'       => true,
    ]);
}

// ============================================
// SISTEMA CONTATORE VISITE
// ============================================

/**
 * Registra una visita. Da chiamare in cima a ogni pagina pubblica.
 * Ignora automaticamente gli IP nella lista VISITS_IGNORED_IPS.
 */
function trackVisit() {
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';

    // Se l'IP è nella lista ignorati, non contare
    if (in_array($ip, VISITS_IGNORED_IPS)) {
        return;
    }

    // Non contare bot/crawler/preview: alleggerisce il contatore e riduce le scritture su disco
    $ua = $_SERVER['HTTP_USER_AGENT'] ?? '';
    if ($ua === '' || preg_match('/bot|crawl|spider|slurp|bingpreview|facebookexternalhit|preview|monitor|pingdom|uptime|curl|wget|python-requests|headless|scan/i', $ua)) {
        return;
    }

    $visitsFile = __DIR__ . '/data/visits.json';
    $visits = [];

    if (file_exists($visitsFile)) {
        $visits = json_decode(file_get_contents($visitsFile), true) ?? [];
    }

    $today = date('Y-m-d');
    $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';
    $page = basename($_SERVER['SCRIPT_NAME']);

    // Inizializza struttura se mancante
    if (!isset($visits['total'])) $visits['total'] = 0;
    if (!isset($visits['daily'])) $visits['daily'] = [];
    if (!isset($visits['ips'])) $visits['ips'] = [];
    if (!isset($visits['pages'])) $visits['pages'] = [];
    if (!isset($visits['hourly'])) $visits['hourly'] = [];

    // Incrementa totale
    $visits['total']++;

    // Incrementa giornaliero
    if (!isset($visits['daily'][$today])) {
        $visits['daily'][$today] = 0;
    }
    $visits['daily'][$today]++;

    // Registra IP unico (con data primo accesso)
    if (!isset($visits['ips'][$ip])) {
        $visits['ips'][$ip] = [
            'first' => date('Y-m-d H:i:s'),
            'visits' => 0,
            'user_agent' => $userAgent
        ];
    }
    $visits['ips'][$ip]['visits']++;
    $visits['ips'][$ip]['last'] = date('Y-m-d H:i:s');

    // Pagine visitate
    if (!isset($visits['pages'][$page])) {
        $visits['pages'][$page] = 0;
    }
    $visits['pages'][$page]++;

    // Visite per ora (per grafico ultimi 7 giorni)
    $hourKey = date('Y-m-d H');
    if (!isset($visits['hourly'][$hourKey])) {
        $visits['hourly'][$hourKey] = 0;
    }
    $visits['hourly'][$hourKey]++;

    // Pulizia: mantieni solo ultimi 90 giorni di daily
    $cutoff = date('Y-m-d', strtotime('-90 days'));
    foreach (array_keys($visits['daily']) as $d) {
        if ($d < $cutoff) unset($visits['daily'][$d]);
    }

    // Pulizia hourly: ultimi 7 giorni
    $cutoffHour = date('Y-m-d H', strtotime('-7 days'));
    foreach (array_keys($visits['hourly']) as $h) {
        if ($h < $cutoffHour) unset($visits['hourly'][$h]);
    }

    // Se ci sono troppi IP, tieni solo i più attivi
    if (count($visits['ips']) > 5000) {
        uasort($visits['ips'], function($a, $b) {
            return $b['visits'] - $a['visits'];
        });
        $visits['ips'] = array_slice($visits['ips'], 0, 5000, true);
    }

    // Salva
    if (!is_dir(dirname($visitsFile))) {
        mkdir(dirname($visitsFile), 0755, true);
    }
    file_put_contents($visitsFile, json_encode($visits, JSON_UNESCAPED_UNICODE), LOCK_EX);
}

/**
 * Legge i dati delle visite per la dashboard admin.
 */
function getVisitsStats() {
    $visitsFile = __DIR__ . '/data/visits.json';
    if (!file_exists($visitsFile)) {
        return [
            'total' => 0,
            'today' => 0,
            'week' => 0,
            'month' => 0,
            'unique_ips' => 0,
            'daily' => [],
            'pages' => [],
            'ips' => []
        ];
    }

    $visits = json_decode(file_get_contents($visitsFile), true) ?? [];
    $today = date('Y-m-d');

    // Calcola visite ultimi 7 giorni
    $week = 0;
    for ($i = 0; $i < 7; $i++) {
        $d = date('Y-m-d', strtotime("-$i days"));
        $week += $visits['daily'][$d] ?? 0;
    }

    // Calcola visite ultimi 30 giorni
    $month = 0;
    for ($i = 0; $i < 30; $i++) {
        $d = date('Y-m-d', strtotime("-$i days"));
        $month += $visits['daily'][$d] ?? 0;
    }

    // Ordina pagine per visite
    $pages = $visits['pages'] ?? [];
    arsort($pages);

    return [
        'total' => $visits['total'] ?? 0,
        'today' => $visits['daily'][$today] ?? 0,
        'week' => $week,
        'month' => $month,
        'unique_ips' => count($visits['ips'] ?? []),
        'daily' => $visits['daily'] ?? [],
        'pages' => $pages,
        'ips' => $visits['ips'] ?? []
    ];
}

// ============================================
// STATO DEGLI STRUMENTI — UN UNICO FILE
// ============================================
// Prima ogni strumento aveva il suo file in data/states/<chiave>.json (30 file
// da leggere e scrivere). Ora c'e' un solo data/states.json:
//   { "riassumi": { "status": "attivo", "timestamp": "..." }, ... }
// Se sul server ci sono ancora i vecchi file, vengono importati qui una volta
// sola e poi rimossi: nessun dato va perso.

function lux_states_file(): string
{
    return __DIR__ . '/data/states.json';
}

function lux_states_read(): array
{
    $states = [];

    $file = lux_states_file();
    if (is_file($file)) {
        $data = json_decode((string)file_get_contents($file), true);
        if (is_array($data)) $states = $data;
    }

    // Importazione una tantum dai vecchi file singoli.
    $oldDir = __DIR__ . '/data/states';
    if (is_dir($oldDir)) {
        $imported = 0;
        foreach ((array)scandir($oldDir) as $entry) {
            if ($entry === '.' || $entry === '..' || substr($entry, -5) !== '.json') continue;
            $key = basename($entry, '.json');
            $data = json_decode((string)@file_get_contents($oldDir . '/' . $entry), true);
            if (!is_array($data)) continue;
            if (!isset($states[$key])) {
                $states[$key] = [
                    'status'    => (string)($data['status'] ?? 'attivo'),
                    'timestamp' => (string)($data['timestamp'] ?? date('Y-m-d H:i:s')),
                ];
            }
            @unlink($oldDir . '/' . $entry);
            $imported++;
        }
        @rmdir($oldDir);
        if ($imported > 0) {
            lux_states_write($states);
            error_log('L.U.X. Verse: importati ' . $imported . ' stati da data/states/ a data/states.json');
        }
    }

    return $states;
}

function lux_states_write(array $states): bool
{
    $file = lux_states_file();
    if (!is_dir(dirname($file)) && !@mkdir(dirname($file), 0755, true)) return false;
    return file_put_contents($file, json_encode($states, JSON_UNESCAPED_UNICODE), LOCK_EX) !== false;
}

/** Tutti gli stati (in cache per la richiesta). */
function lux_states(): array
{
    if (!isset($GLOBALS['__lux_states']) || !is_array($GLOBALS['__lux_states'])) {
        $GLOBALS['__lux_states'] = lux_states_read();
    }
    return $GLOBALS['__lux_states'];
}

/**
 * Stato di uno strumento: 'attivo' se non e' mai stato toccato.
 * Gli strumenti GENERATI dal catalogo (99.000) non hanno un file singolo:
 * se non hanno uno stato proprio, usano quello comune salvato sotto la
 * chiave "__generated__" (si imposta dal pannello con lo stato di massa).
 * Cosi' si governano 100.000 progetti insieme, senza scrivere 100.000 righe.
 */
function lux_project_state(string $key): string
{
    $states = lux_states();
    if (isset($states[$key]['status'])) return (string)$states[$key]['status'];
    $idx = function_exists('lux_catalog_index_from_slug') ? lux_catalog_index_from_slug($key) : null;
    if ($idx !== null) {
        $cat = '';
        if (function_exists('lux_catalog_item')) {
            $it = lux_catalog_item($idx);
            if ($it) $cat = (string)($it['category'] ?? '');
        }
        return lux_generated_state($cat);
    }
    return 'attivo';
}

/**
 * Stato comune degli strumenti generati.
 *
 * Ordine: stato della singola categoria, poi quello generale, poi 'attivo'.
 * Cosi' dal pannello si puo' mettere in manutenzione UNA categoria (es. i
 * giochi) senza toccare gli altri 99.000, e resta un solo interruttore
 * generale per agire su tutto in un colpo.
 */
function lux_generated_state(string $cat = ''): string
{
    $states = lux_states();
    $cat = preg_replace('/[^a-z0-9_\-]/i', '', strtolower($cat));
    if ($cat !== '' && isset($states['__gencat__:' . $cat]['status'])) {
        return (string)$states['__gencat__:' . $cat]['status'];
    }
    return (string)($states['__generated__']['status'] ?? 'attivo');
}

/** Etichetta leggibile di uno stato (tradotta). */
function lux_project_state_label(string $status): string
{
    $map = [
        'manutenzione' => 'Under Maintenance',
        'pausa'        => 'On Pause',
        'costruzione'  => 'Under Construction',
        'verifica'     => 'Under Review',
        'bloccato'     => 'Blocked',
        'archiviato'   => 'Archived',
        'disattivato'  => 'Off',
    ];
    return isset($map[$status]) ? t($map[$status]) : $status;
}

/** Scrive lo stato di uno strumento nel file unico. */
function lux_set_project_state(string $key, string $status): bool
{
    $states = lux_states();
    $states[$key] = ['status' => $status, 'timestamp' => date('Y-m-d H:i:s')];
    $GLOBALS['__lux_states'] = $states;
    return lux_states_write($states);
}

/** Cancella lo stato (strumento eliminato dal catalogo). */
function lux_delete_project_state(string $key): bool
{
    $states = lux_states();
    if (!isset($states[$key])) return true;
    unset($states[$key]);
    $GLOBALS['__lux_states'] = $states;
    return lux_states_write($states);
}

// ============================================
// BLOCCO AUTOMATICO PROGETTO (SE NON ATTIVO)
// ============================================
function checkProjectStatus() {
    $projectKey = basename($_SERVER['SCRIPT_NAME'], '.php');

    // File di sistema da non bloccare
    $allowedFiles = [
        'index', 'admin', 'config', 'config.local', 'admin_setup', 'bacheca',
        'contattaci', 'privacy', 'terms', 'chi-siamo', 'progetti-workme',
        'workmemanage', 'faq', 'assistenza'
    ];

    if (in_array($projectKey, $allowedFiles)) {
        return;
    }

    $projectStatus = lux_project_state($projectKey);
    {
        if ($projectStatus !== 'attivo') {
            $blockedStatusText = '';
            $statusColor = '#D96C5B';
            $blockIcon = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><line x1="15" y1="9" x2="9" y2="15"></line><line x1="9" y1="9" x2="15" y2="15"></line></svg>';

            switch ($projectStatus) {
                case 'manutenzione':
                    $blockedStatusText = t('Under Maintenance');
                    $statusColor = '#D8A93C';
                    $blockIcon = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.77-3.77a6 6 0 0 1-7.94 7.94l-6.91 6.91a2.12 2.12 0 0 1-3-3l6.91-6.91a6 6 0 0 1 7.94-7.94l-3.76 3.76z"></path></svg>';
                    break;
                case 'bloccato':
                    $blockedStatusText = t('Blocked');
                    $statusColor = '#D96C5B';
                    $blockIcon = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect><path d="M7 11V7a5 5 0 0 1 10 0v4"></path></svg>';
                    break;
                case 'disattivato':
                case 'pausa':
                    $blockedStatusText = t('On Pause');
                    $statusColor = '#D8A93C';
                    $blockIcon = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="6" y="4" width="4" height="16"></rect><rect x="14" y="4" width="4" height="16"></rect></svg>';
                    break;
                case 'costruzione':
                    $blockedStatusText = t('Under Construction');
                    $statusColor = '#D8A93C';
                    $blockIcon = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2l4 8H8z"></path><polyline points="8 10 8 21 16 21 16 10"></polyline></svg>';
                    break;
                case 'verifica':
                    $blockedStatusText = t('Under Review');
                    $statusColor = '#F0CE86';
                    $blockIcon = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>';
                    break;
                case 'archiviato':
                    $blockedStatusText = t('Archived');
                    $statusColor = '#706D66';
                    $blockIcon = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="21 8 21 21 3 21 3 8"></polyline><rect x="1" y="3" width="22" height="5"></rect><line x1="10" y1="12" x2="14" y2="12"></line></svg>';
                    break;
            }

            echo '<!DOCTYPE html>
            <html lang="' . (currentLang() === 'it' ? 'it' : 'en') . '">
            <head>
                <meta charset="UTF-8">
                <meta name="viewport" content="width=device-width, initial-scale=1.0">
                <meta name="robots" content="noindex, nofollow">
                <meta name="description" content="' . htmlspecialchars(strip_tags(t('This project is currently {status}. Please wait or come back later.', ['status' => $blockedStatusText]))) . '">
                <link rel="canonical" href="https://luxverse.c22.eu/' . urlencode($projectKey) . '.php">
                <meta property="og:title" content="' . t('Project Not Available') . '">
                <meta property="og:description" content="' . htmlspecialchars(strip_tags(t('This project is currently {status}. Please wait or come back later.', ['status' => $blockedStatusText]))) . '">
                <meta property="og:type" content="website">
                <title>' . t('Project Not Available') . '</title>
                <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@300;400;500;600;700&family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
                <style>
                    * { margin: 0; padding: 0; box-sizing: border-box; }
                    body { font-family: "Inter", sans-serif; background: #0A0B0D; color: #EDE9E0; display: flex; justify-content: center; align-items: center; height: 100vh; overflow: hidden; }
                    .block-popup {
                        background: linear-gradient(160deg, #0A0B0D, #0A0B0D);
                        border: 2px solid ' . $statusColor . ';
                        border-radius: 24px;
                        padding: 40px;
                        max-width: 500px;
                        width: 90%;
                        text-align: center;
                        box-shadow: 0 20px 60px rgba(0, 0, 0, 0.5);
                    }
                    .block-icon { width: 76px; height: 76px; margin: 0 auto 20px; animation: pulse 1.5s infinite; }
                    .block-icon svg { width: 100%; height: 100%; }
                    .block-title { font-family: "Space Grotesk", sans-serif; font-size: 2em; margin-bottom: 10px; }
                    .block-status {
                        display: inline-block;
                        padding: 8px 20px;
                        border-radius: 20px;
                        font-weight: 700;
                        margin-bottom: 20px;
                        font-size: 14px;
                        text-transform: uppercase;
                        letter-spacing: 1px;
                    }
                    .block-message { color: #A5A197; font-size: 16px; line-height: 1.8; margin-bottom: 30px; }
                    .block-button {
                        display: inline-block;
                        padding: 12px 30px;
                        background: linear-gradient(135deg, #D8A93C, #D8A93C);
                        color: #FFFFFF;
                        border-radius: 12px;
                        text-decoration: none;
                        font-weight: 600;
                        font-size: 14px;
                        transition: all 0.3s;
                        margin-bottom: 10px;
                    }
                    .block-button:hover { transform: translateY(-2px); box-shadow: 0 0 20px rgba(212, 170, 87, 0.5); }
                    .block-footer { color: #706D66; font-size: 12px; margin-top: 15px; }
                    @keyframes pulse { 0%, 100% { transform: scale(1); opacity: 1; } 50% { transform: scale(1.1); opacity: 0.8; } }
                </style>
            </head>
            <body>
                <div class="block-popup">
                    <div class="block-icon" style="color: ' . $statusColor . ';">' . $blockIcon . '</div>
                    <h1 class="block-title">' . t('Project Not Available') . '</h1>
                    <div class="block-status" style="background: ' . $statusColor . '20; color: ' . $statusColor . ';">' . $blockedStatusText . '</div>
                    <p class="block-message">' . t('This project is currently {status}. Please wait or come back later.', ['status' => '<strong style="color: ' . $statusColor . ';">' . strtolower($blockedStatusText) . '</strong>']) . '</p>
                    <a href="index.php" class="block-button"><i data-i=arrow-left class=ic></i> ' . t('Back to Home') . '</a>
                    <p class="block-footer">© 2026 L.U.X. Verse</p>
                </div>
            </body>
            </html>';
            exit;
        }
    }
}

// ============================================
// ESEGUI IL CONTROLLO AUTOMATICO
// ============================================
checkProjectStatus();
?>