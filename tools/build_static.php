<?php
// ============================================================
// LA PARTE STATICA DEL SITO — L.U.X. VERSE (17/10/2026)
// ============================================================
// Vedi la nota in config.php (LUX_STATIC_BASE, LUX_STATIC_SELF):
//
//   · qui si generano gli HTML delle pagine di contenuto fisso (privacy,
//     termini, chi siamo, FAQ, percorso, linea del tempo, codice aperto,
//     condividi) e si copiano i file di lavoro (CSS, JS, immagini). Il
//     risultato va nella cartella `frontend/`;
//   · i file .html vengono caricati sul sito (tools/deploy.php), allo stesso
//     indirizzo di sempre: sono file, non programmi, e si aprono senza far
//     calcolare niente al server;
//   · la stessa cartella viene pubblicata anche su una rete di distribuzione:
//     da li' arrivano gli asset (il peso vero delle pagine) e resta una
//     copia di riserva delle pagine.
//
// Perche' cosi': una pagina di contenuto non cambia mentre la leggi, quindi
// non ha senso farla calcolare ogni volta; gli asset serviti da una rete
// arrivano prima e non consumano il lavoro del server.
//
// Le pagine sono renderizzate dal PHP vero (tools/_static_one.php), non
// riscritte a mano: quello che si vede nella copia statica e' esattamente
// quello che il sito mostra oggi. Se una pagina si rompe, l'esportazione si
// ferma invece di pubblicare un file rotto.
//
// Uso:
//   php tools/build_static.php                 → genera in frontend/
//   php tools/build_static.php --out=/tmp/sito → genera altrove
//   php tools/build_static.php --solo=faq.php  → una pagina sola (prova)
// ============================================================

if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit("Solo da riga di comando.\n");
}

$here = __DIR__;
$base = dirname($here);

// Due disposizioni possibili, per lo stesso file:
//   · nel repository pubblico:  <repo>/site/...  e si scrive <repo>/frontend/
//   · nella cartella di lavoro: <sito>/...       e si scrive <sito>/frontend/
$siteRoot = is_file($base . '/site/config.php') ? $base . '/site' : $base;
$outDir   = basename($siteRoot) === 'site' ? dirname($siteRoot) . '/frontend' : $siteRoot . '/frontend';

$solo = '';
foreach ($argv as $a) {
    if (strpos($a, '--out=') === 0)  $outDir = rtrim(substr($a, 6), '/\\');
    if (strpos($a, '--solo=') === 0) $solo = substr($a, 7);
}

// ------------------------------------------------------------
// 1. QUALI PAGINE
// ------------------------------------------------------------
// L'elenco vive in config.php (LUX_STATIC_PAGES): qui non si ricopia, cosi'
// non possono esistere due elenchi che dicono cose diverse. Si chiede a un
// processo separato di stamparlo, per non caricare tutto il sito in questo.
function lux_static_run(string $script, array $args, ?string &$err = null): string
{
    $cmd = array_merge([PHP_BINARY, $script], $args);
    $p = proc_open($cmd, [1 => ['pipe', 'w'], 2 => ['pipe', 'w']], $pipes);
    if (!is_resource($p)) { $err = 'avvio non riuscito'; return ''; }
    $out = (string)stream_get_contents($pipes[1]); fclose($pipes[1]);
    $err = (string)stream_get_contents($pipes[2]); fclose($pipes[2]);
    proc_close($p);
    return $out;
}

$listCode = 'require ' . var_export($siteRoot . '/config.php', true) . '; echo implode(" ", LUX_STATIC_PAGES);';
$list = trim(lux_static_run('-r', [$listCode], $listErr));
$pages = $list === '' ? [] : preg_split('/\s+/', $list);
if (!$pages) {
    fwrite(STDERR, "Non riesco a leggere LUX_STATIC_PAGES da config.php.\n" . $listErr . "\n");
    exit(1);
}
if ($solo !== '') $pages = array_values(array_filter($pages, function ($p) use ($solo) { return $p === $solo; }));
if (!$pages) {
    fwrite(STDERR, "--solo: nessuna pagina corrisponde.\n");
    exit(1);
}

// ------------------------------------------------------------
// 2. LA CARTELLA DI USCITA
// ------------------------------------------------------------
if (!is_dir($outDir) && !@mkdir($outDir, 0777, true)) {
    fwrite(STDERR, "Non posso creare la cartella " . $outDir . "\n");
    exit(1);
}

// ------------------------------------------------------------
// 3. LE PAGINE, IN ITALIANO E IN INGLESE
// ------------------------------------------------------------
// L'inglese sta in /en/ (privacy.php e en/privacy.php): su una rete statica
// non c'e' un programma che legge ?lang=, quindi ogni lingua ha il suo file.
$worker = $here . '/_static_one.php';
$langs  = ['it' => '', 'en' => 'en'];
$wrote  = 0;
$fail   = 0;

foreach ($langs as $lang => $sub) {
    $target = $sub === '' ? $outDir : $outDir . '/' . $sub;
    if (!is_dir($target) && !@mkdir($target, 0777, true)) {
        fwrite(STDERR, "Non posso creare " . $target . "\n");
        exit(1);
    }

    foreach ($pages as $page) {
        // La cartella del sito passa al worker: nel repository le pagine
        // stanno in site/, nella cartella di lavoro nella radice.
        $html = lux_static_run($worker, [$siteRoot, $page, $lang], $err);

        // Controlli: se qualcosa e' andato storto, non si pubblica niente.
        $broken = '';
        if ($err !== '' && preg_match('/Fatal error|Parse error|Uncaught/i', $err)) $broken = 'errore PHP';
        elseif (stripos($html, '</html>') === false) $broken = 'HTML incompleto';
        elseif (preg_match('/<(b|strong)>\s*(Fatal error|Warning|Notice)/i', $html)) $broken = 'avvisi dentro la pagina';

        if ($broken !== '') {
            fwrite(STDERR, "ERRORE  " . $page . ' (' . $lang . '): ' . $broken . "\n" . trim($err) . "\n");
            $fail++;
            continue;
        }

        // Nessun noindex: questi file sono le pagine vere del sito, allo
        // stesso indirizzo. Il canonical scritto dentro punta a
        // luxverse.c22.eu, quindi la copia esterna (quella su una rete di
        // distribuzione) non fa concorrenza a niente: se un motore la trova
        // sa qual e' l'indirizzo ufficiale.

        // File .html e non .php: un .html lo si consegna cosi' com'e', senza
        // eseguire niente.
        $file = $target . '/' . str_replace('.php', '.html', $page);
        if (@file_put_contents($file, $html) === false) {
            fwrite(STDERR, "ERRORE  scrittura " . $file . "\n");
            $fail++;
            continue;
        }
        printf("SCRITTO  %-42s %6.1f KB\n", ($sub === '' ? '' : $sub . '/') . str_replace('.php', '.html', $page), strlen($html) / 1024);
        $wrote++;
    }
}

// ------------------------------------------------------------
// 4. I FILE DI LAVORO (CSS, JS, IMMAGINI)
// ------------------------------------------------------------
// Sono il peso vero delle pagine: serviti dalla rete arrivano prima e non
// consumano il lavoro del server. Si copia la cartella assets/ intera, cosi'
// non c'e' un elenco da tenere aggiornato a mano.
function lux_copy_tree(string $from, string $to): int
{
    if (!is_dir($from)) return 0;
    if (!is_dir($to) && !@mkdir($to, 0777, true)) return 0;
    $n = 0;
    foreach ((array)scandir($from) as $f) {
        if ($f === '.' || $f === '..') continue;
        $s = $from . '/' . $f;
        $d = $to . '/' . $f;
        if (is_dir($s)) $n += lux_copy_tree($s, $d);
        elseif (is_file($s) && @copy($s, $d)) $n++;
    }
    return $n;
}

$copied = lux_copy_tree($siteRoot . '/assets', $outDir . '/assets');
printf("COPIATI  %-42s %6d file\n", 'assets/', $copied);

// I file che stanno nella radice del sito e che le pagine chiedono davvero:
// l'avviso sui cookie e il file che descrive il sito quando lo si aggiunge
// alla schermata iniziale del telefono. Se si dimentica uno di questi, la
// pagina si apre lo stesso ma con una richiesta persa (e un errore nella
// console di chi la guarda).
foreach (['cookie-consent.js', 'manifest.json'] as $extra) {
    if (is_file($siteRoot . '/' . $extra) && @copy($siteRoot . '/' . $extra, $outDir . '/' . $extra)) {
        printf("COPIATO  %-42s\n", $extra);
        $copied++;
    }
}

// La cartella statica non e' la home del sito: chi apre il suo indirizzo
// senza una pagina viene portato al sito vero, invece di trovare un elenco.
$landing = '<!doctype html><html lang="it"><head><meta charset="utf-8">'
    . '<meta name="robots" content="noindex,nofollow">'
    . '<meta name="viewport" content="width=device-width,initial-scale=1">'
    . '<title>L.U.X. Verse</title>'
    . '<meta http-equiv="refresh" content="0;url=https://luxverse.c22.eu/">'
    . '</head><body style="background:#0A0B0D;color:#EDE9E0;font-family:system-ui,sans-serif;padding:24px">'
    . '<p>L.U.X. Verse — <a href="https://luxverse.c22.eu/" style="color:#D8A93C">vai al sito</a>.</p>'
    . '<script>location.replace("https://luxverse.c22.eu/");</script>'
    . '</body></html>';
file_put_contents($outDir . '/index.html', $landing);

// robots.txt: qui non c'e' niente da indicizzare, il sito e' un altro.
file_put_contents($outDir . '/robots.txt', "User-agent: *\nDisallow: /\n");

echo "--------------------------------------------------------------\n";
printf("Pagine scritte: %d · errori: %d · file di lavoro: %d · cartella: %s\n", $wrote, $fail, $copied, $outDir);
exit($fail > 0 ? 1 : 0);
