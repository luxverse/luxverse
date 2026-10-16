<?php
// ============================================================
// L.U.X. VERSE — PIATTAFORMA (stato condiviso)
// ------------------------------------------------------------
// Un solo posto da cui il sito e il pannello leggono e scrivono:
//   - i voti (upvote) di ogni progetto;
//   - quanto ogni progetto viene aperto (per sapere quali contano);
//   - i feedback dei visitatori (sito, chatbot, admin);
//   - le chat di L.U.X. GUIDE e quelle trasferite all'admin;
//   - la salute delle 4 chiavi AI (richieste, errori, limiti, tempo);
//   - il freno alle richieste (rate limit) per non andare in crash
//     quando entrano molte persone insieme.
//
// Niente database: sempre file JSON in data/, come il resto del sito.
// Le mappe (chiave => valore) NON usano lux_data_write(), che azzera le
// chiavi con array_values(): qui c'e' una lettura/scrittura che conserva
// gli indici associativi.
// ============================================================

if (!defined('LUX_PLATFORM_LOADED')) {
    define('LUX_PLATFORM_LOADED', true);

    // ------------------------------------------------------------
    // 0. MAPPE (chiave => valore) in data/<nome>.json
    // ------------------------------------------------------------
    function lux_map_file(string $name): string
    {
        $name = preg_replace('/[^a-z0-9_\-]/i', '', $name);
        return __DIR__ . '/../data/' . $name . '.json';
    }

    function lux_map_read(string $name): array
    {
        $file = lux_map_file($name);
        if (!is_file($file)) return [];
        $raw = (string)@file_get_contents($file);
        if (trim($raw) === '') return [];
        $data = json_decode($raw, true);
        return is_array($data) ? $data : [];
    }

    function lux_map_write(string $name, array $data): bool
    {
        $file = lux_map_file($name);
        if (!is_dir(dirname($file))) @mkdir(dirname($file), 0755, true);
        return @file_put_contents(
            $file,
            json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
            LOCK_EX
        ) !== false;
    }

    // ------------------------------------------------------------
    // 1. RATE LIMIT (freno alle richieste)
    // ------------------------------------------------------------
    // Conta quante volte un identificativo (IP) ha fatto una certa azione
    // nella finestra di tempo. Quando supera il massimo, la richiesta si
    // rifiuta con un invito ad attendere, invece di far lavorare il server
    // fino a farlo cadere. La finestra e' scorrevole: si guardano solo i
    // battiti degli ultimi $window secondi.
    function lux_rate_limit(string $bucket, string $id, int $max, int $window): bool
    {
        if ($max <= 0) return true;
        $bucket = preg_replace('/[^a-z0-9_\-]/i', '', $bucket);
        $key    = $bucket . ':' . substr(hash('sha256', $id), 0, 24);

        $store = lux_map_read('ratelimit');
        $now   = time();
        $hits  = array_values(array_filter(
            (array)($store[$key] ?? []),
            function ($t) use ($now, $window) { return (int)$t > $now - $window; }
        ));

        if (count($hits) >= $max) {
            $store[$key] = $hits;
            lux_map_write('ratelimit', $store);
            return false;
        }

        $hits[] = $now;
        $store[$key] = $hits;

        // Pulizia: non si tiene memoria infinita di identificativi vecchi.
        if (count($store) > 4000) {
            foreach ($store as $k => $v) {
                $v = array_filter((array)$v, function ($t) use ($now) { return (int)$t > $now - 3600; });
                if (!$v) unset($store[$k]);
                else $store[$k] = array_values($v);
            }
        }
        lux_map_write('ratelimit', $store);
        return true;
    }

    // ------------------------------------------------------------
    // 2. UPVOTE (un voto per progetto)
    // ------------------------------------------------------------
    // Si tiene il conteggio e una lista (limitata) di impronte degli
    // indirizzi che hanno votato: cosi' lo stesso visitatore non gonfia il
    // numero, ma non si conserva nessun indirizzo in chiaro.
    function lux_upvotes(): array
    {
        static $c = null;
        if ($c === null) $c = lux_map_read('upvotes');
        return $c;
    }

    function lux_upvote_count(string $slug): int
    {
        $slug = preg_replace('/[^a-z0-9_\-]/i', '', strtolower($slug));
        $all  = lux_upvotes();
        return (int)($all[$slug]['count'] ?? 0);
    }

    /** Aggiunge un voto. Ritorna il nuovo conteggio, oppure -1 se gia' votato. */
    function lux_upvote_add(string $slug, string $voter = ''): int
    {
        $slug = preg_replace('/[^a-z0-9_\-]/i', '', strtolower($slug));
        if ($slug === '') return -1;

        $all = lux_map_read('upvotes');
        $row = $all[$slug] ?? ['count' => 0, 'ips' => []];
        if (!is_array($row['ips'] ?? null)) $row['ips'] = [];

        $fp = substr(hash('sha256', 'upvote|' . $voter), 0, 20);
        if ($voter !== '' && in_array($fp, $row['ips'], true)) {
            return (int)$row['count'];
        }
        if ($voter !== '') {
            $row['ips'][] = $fp;
            if (count($row['ips']) > 4000) $row['ips'] = array_slice($row['ips'], -4000);
        }
        $row['count'] = (int)$row['count'] + 1;
        $all[$slug] = $row;
        lux_map_write('upvotes', $all);
        $GLOBALS['__lux_upvotes'] = null;
        return (int)$row['count'];
    }

    // ------------------------------------------------------------
    // 3. USO (quante volte un progetto viene aperto)
    // ------------------------------------------------------------
    function lux_usage(): array
    {
        static $c = null;
        if ($c === null) $c = lux_map_read('usage');
        return $c;
    }

    function lux_usage_bump(string $slug): int
    {
        $slug = preg_replace('/[^a-z0-9_\-]/i', '', strtolower($slug));
        if ($slug === '') return 0;
        $all = lux_map_read('usage');
        $all[$slug] = (int)($all[$slug] ?? 0) + 1;
        lux_map_write('usage', $all);
        return $all[$slug];
    }

    function lux_usage_count(string $slug): int
    {
        $slug = preg_replace('/[^a-z0-9_\-]/i', '', strtolower($slug));
        $all  = lux_usage();
        return (int)($all[$slug] ?? 0);
    }

    // ------------------------------------------------------------
    // 4. PROGETTI PIU' PRESTIGIOSI
    // ------------------------------------------------------------
    // La classifica unisce il gradimento (upvote) e l'uso reale (aperture).
    // Si escludono le pagine che non sono progetti interni: index, admin,
    // bacheca, info e via dicendo.
    function lux_non_project_keys(): array
    {
        return [
            'index', 'admin', 'bacheca', 'webapp', 'info', 'tool', 'config',
            'privacy', 'terms', 'faq', 'assistenza', 'contattaci', 'chi-siamo',
            'status', 'linea-del-tempo', 'timeline', 'offline', 'sw', 'manifest',
            'admin_setup', 'gestisci', 'login', 'logout', 'api', 'query',
        ];
    }

    function lux_top_projects(int $n = 10): array
    {
        $up    = lux_map_read('upvotes');
        $us    = lux_map_read('usage');
        $skip  = lux_non_project_keys();
        $rows  = [];

        $keys = array_unique(array_merge(array_keys($up), array_keys($us)));
        foreach ($keys as $k) {
            $k = (string)$k;
            if ($k === '' || in_array($k, $skip, true)) continue;
            $votes = (int)($up[$k]['count'] ?? 0);
            $opens = (int)($us[$k] ?? 0);
            if ($votes === 0 && $opens === 0) continue;
            $rows[] = ['key' => $k, 'upvotes' => $votes, 'opens' => $opens, 'score' => $votes * 3 + $opens];
        }
        usort($rows, function ($a, $b) { return $b['score'] <=> $a['score']; });
        return array_slice($rows, 0, max(1, min(100, $n)));
    }

    // ------------------------------------------------------------
    // 5. FEEDBACK (sito, chatbot, admin)
    // ------------------------------------------------------------
    function lux_feedback_file(): string { return lux_data_file('feedback'); }

    function lux_feedback_all(): array
    {
        return lux_data_read('feedback');
    }

    function lux_feedback_add(array $row): bool
    {
        $list = lux_data_read('feedback');
        $row += [
            'id'      => 'F_' . substr(md5(uniqid('', true)), 0, 10),
            'rating'  => 0,
            'message' => '',
            'channel' => 'site',
            'page'    => '',
            'lang'    => 'it',
            'status'  => 'nuovo',
            'date'    => date('Y-m-d H:i:s'),
        ];
        $row['rating']  = max(1, min(5, (int)$row['rating']));
        $list[] = $row;
        if (count($list) > 2000) $list = array_slice($list, -2000);
        return lux_data_write('feedback', $list);
    }

    function lux_feedback_summary(): array
    {
        $list = lux_feedback_all();
        $n = count($list); $sum = 0; $new = 0; $byChannel = [];
        foreach ($list as $r) {
            $sum += (int)($r['rating'] ?? 0);
            if ((string)($r['status'] ?? '') === 'nuovo') $new++;
            $ch = (string)($r['channel'] ?? 'site');
            $byChannel[$ch] = ($byChannel[$ch] ?? 0) + 1;
        }
        return [
            'count'   => $n,
            'average' => $n ? round($sum / $n, 1) : 0,
            'new'     => $new,
            'channels'=> $byChannel,
        ];
    }

    function lux_feedback_set_status(string $id, string $status): bool
    {
        $list = lux_feedback_all();
        $ok = false;
        foreach ($list as &$r) {
            if ((string)($r['id'] ?? '') === $id) { $r['status'] = $status; $ok = true; }
        }
        unset($r);
        if ($ok) lux_data_write('feedback', $list);
        return $ok;
    }

    // ------------------------------------------------------------
    // 6. CHAT (L.U.X. GUIDE + trasferite all'admin)
    // ------------------------------------------------------------
    function lux_chats_all(): array
    {
        return lux_data_read('chats');
    }

    function lux_chats_write(array $list): bool
    {
        return lux_data_write('chats', $list);
    }

    function lux_chat_find(string $id): ?array
    {
        foreach (lux_chats_all() as $c) {
            if ((string)($c['id'] ?? '') === $id) return $c;
        }
        return null;
    }

    function lux_chat_save(array $chat): bool
    {
        $list = lux_chats_all();
        $found = false;
        foreach ($list as $i => $c) {
            if ((string)($c['id'] ?? '') === (string)$chat['id']) { $list[$i] = $chat; $found = true; break; }
        }
        if (!$found) $list[] = $chat;
        if (count($list) > 1000) $list = array_slice($list, -1000);
        return lux_chats_write($list);
    }

    /** Nuova chat. Ritorna [id, chat]. */
    function lux_chat_new(string $lang = 'it', string $visitor = ''): array
    {
        $id = 'C_' . substr(md5(uniqid('', true)), 0, 12);
        $chat = [
            'id'      => $id,
            'lang'    => $lang === 'en' ? 'en' : 'it',
            'visitor' => $visitor,
            'status'  => 'bot',              // bot | queued | answered | closed
            'opened'  => date('Y-m-d H:i:s'),
            'updated' => date('Y-m-d H:i:s'),
            'log'     => [],
        ];
        lux_chat_save($chat);
        return [$id, $chat];
    }

    function lux_chat_append(string $id, string $from, string $text, array $meta = []): ?array
    {
        $chat = lux_chat_find($id);
        if (!$chat) return null;

        // NIENTE DOPPIONI: se l'ultimo messaggio e' identico a questo
        // (stesso autore, stesso testo) non si scrive una seconda volta.
        // Serve perche' una richiesta ripetuta dal browser — un doppio clic,
        // un "Invia" premuto due volte, una ritentata dopo un timeout —
        // non deve far comparire due volte lo stesso messaggio in chat.
        $last = end($chat['log']);
        if (is_array($last)
            && (string)($last['from'] ?? '') === $from
            && (string)($last['text'] ?? '') === $text) {
            return $chat;
        }

        $chat['log'][] = array_merge([
            'from' => $from,                 // bot | user | admin
            'text' => $text,
            'date' => date('Y-m-d H:i:s'),
        ], $meta);
        if (count($chat['log']) > 400) $chat['log'] = array_slice($chat['log'], -400);
        $chat['updated'] = date('Y-m-d H:i:s');
        lux_chat_save($chat);
        return $chat;
    }

    /**
     * Lo stato COMPLETO di una chat: e' l'unica cosa che disegna il pannello.
     * Ogni azione della guida risponde con questo stesso pacchetto, cosi' il
     * browser non deve fare un secondo giro per sapere cosa mostrare: una
     * richiesta, un disegno. Cosi' un messaggio non compare due volte e non
     * "sparisce e ritorna" mentre il server sta ancora scrivendo.
     */
    function lux_chat_state_payload(array $chat, string $pageLang = 'it'): array
    {
        $log    = array_values((array)($chat['log'] ?? []));
        $status = (string)($chat['status'] ?? 'bot');
        $chLang = (string)($chat['lang'] ?? 'it');
        if ($chLang !== 'en') $chLang = 'it';
        $pageLang = $pageLang === 'en' ? 'en' : 'it';

        // La lingua non e' ancora stata scelta finche' non compare la prima
        // risposta del menu (kind = menu).
        $chosen = false;
        foreach ($log as $row) {
            if ((string)($row['kind'] ?? '') === 'menu') { $chosen = true; break; }
        }

        $closed = ($status === 'closed');
        // "frozen" = la risposta la porta avanti una persona dello staff:
        // il bot tace, ma la casella di scrittura resta viva (i messaggi
        // vengono comunque registrati per lo staff).
        $frozen = in_array($status, ['queued', 'answered'], true);

        $options = [];
        if (!$frozen && !$closed) {
            $options = $chosen
                ? lux_guide_menu($chLang)
                : [['id' => 'it', 'label' => 'Italiano'], ['id' => 'en', 'label' => 'English']];
        }

        return [
            'success' => true,
            'chat'    => (string)($chat['id'] ?? ''),
            'status'  => $status,
            'lang'    => $chosen ? $chLang : $pageLang,
            'log'     => $log,
            'frozen'  => $frozen,
            'closed'  => $closed,
            'options' => $options,
            'free'    => !$frozen && !$closed,
        ];
    }

    function lux_chat_queue_count(): int
    {
        $n = 0;
        foreach (lux_chats_all() as $c) {
            $st = (string)($c['status'] ?? '');
            if ($st === 'queued' || $st === 'answered') $n++;
        }
        return $n;
    }

    // ------------------------------------------------------------
    // 7. SALUTE DELLE 4 CHIAVI AI
    // ------------------------------------------------------------
    function lux_ai_keys(): array
    {
        $keys = [];
        if (defined('GROQ_API_KEYS') && is_array(GROQ_API_KEYS)) {
            foreach (GROQ_API_KEYS as $k) {
                $k = trim((string)$k);
                if ($k !== '') $keys[] = $k;
            }
        }
        if (!$keys && defined('GROQ_API_KEY') && (string)GROQ_API_KEY !== '') {
            $keys[] = (string)GROQ_API_KEY;
        }
        return $keys;
    }

    function lux_ai_health_read(): array
    {
        $d = lux_map_read('ai_health');
        return is_array($d) ? $d : [];
    }

    function lux_ai_health_write(array $d): bool
    {
        return lux_map_write('ai_health', $d);
    }

    /**
     * Registra l'esito di una chiamata su una chiave (indice 0-based).
     * $ms = quanto ci ha messo, $error = vuoto se e' andata bene.
     */
    function lux_ai_record(int $index, bool $ok, int $ms = 0, string $error = '', bool $rateLimit = false): void
    {
        $d = lux_ai_health_read();
        $slot = 'k' . $index;
        $row = $d[$slot] ?? ['requests' => 0, 'ok' => 0, 'errors' => 0, 'limits' => 0, 'ms_sum' => 0, 'last_ms' => 0, 'last_error' => '', 'last_used' => ''];
        $row['requests'] = (int)($row['requests'] ?? 0) + 1;
        if ($ok) {
            $row['ok'] = (int)($row['ok'] ?? 0) + 1;
            $row['ms_sum'] = (int)($row['ms_sum'] ?? 0) + max(0, $ms);
            $row['last_ms'] = max(0, $ms);
            $row['last_error'] = '';
        } else {
            $row['errors'] = (int)($row['errors'] ?? 0) + 1;
            if ($rateLimit) $row['limits'] = (int)($row['limits'] ?? 0) + 1;
            $row['last_error'] = substr((string)$error, 0, 180);
        }
        $row['last_used'] = date('Y-m-d H:i:s');
        $d[$slot] = $row;
        $d['__rr'] = (int)($d['__rr'] ?? 0);
        lux_ai_health_write($d);
    }

    /** Gira al prossimo indice (round robin) e lo salva. */
    function lux_ai_next_index(int $count): int
    {
        if ($count <= 1) return 0;
        $d = lux_ai_health_read();
        $i = ((int)($d['__rr'] ?? 0) + 1) % $count;
        $d['__rr'] = $i;
        lux_ai_health_write($d);
        return $i;
    }

    /**
     * Fotografia delle chiavi per il pannello: una barra per chiave.
     * Non esce MAI il valore della chiave, solo la sua salute.
     */
    function lux_ai_snapshot(): array
    {
        $keys = lux_ai_keys();
        $d    = lux_ai_health_read();
        $out  = [];
        foreach ($keys as $i => $k) {
            $row = $d['k' . $i] ?? [];
            $req = (int)($row['requests'] ?? 0);
            $ok  = (int)($row['ok'] ?? 0);
            $lim = (int)($row['limits'] ?? 0);
            $err = (int)($row['errors'] ?? 0);
            $pct = $req > 0 ? (int)round($ok / $req * 100) : 100;
            $avg = $ok > 0 ? (int)round(((int)($row['ms_sum'] ?? 0)) / $ok) : 0;
            $state = 'ok';
            if ($req > 0 && $pct < 50) $state = 'down';
            elseif ($req > 0 && $pct < 90) $state = 'warn';
            $out[] = [
                'label'      => 'API ' . ($i + 1),
                'role'       => ($i === 3) ? 'chatbot+ricerca' : 'strumenti',
                'requests'   => $req,
                'ok'         => $ok,
                'errors'     => $err,
                'limits'     => $lim,
                'health'     => $pct,
                'avg_ms'     => $avg,
                'last_ms'    => (int)($row['last_ms'] ?? 0),
                'last_error' => (string)($row['last_error'] ?? ''),
                'last_used'  => (string)($row['last_used'] ?? ''),
                'state'      => $state,
                'configured' => true,
            ];
        }
        // Se non c'e' nessuna chiave, si dice chiaramente.
        if (!$out) {
            return [['label' => 'API', 'role' => '', 'requests' => 0, 'ok' => 0, 'errors' => 0,
                     'limits' => 0, 'health' => 0, 'avg_ms' => 0, 'last_ms' => 0,
                     'last_error' => '', 'last_used' => '', 'state' => 'down', 'configured' => false]];
        }
        return $out;
    }
}
