<?php
// ============================================================
// L.U.X. VERSE - API IN UN UNICO FILE
// ============================================================
// Prima erano 21 file separati in api/. I corpi sono gli stessi: qui sono
// dentro un solo switch, scelto dal nome dell'endpoint.
//
// Le URL pubbliche non cambiano: /api/get_projects.php, /api/notices.php e
// cosi' via. Il rewrite di api/.htaccess manda ogni /api/<nome>.php qui con
// ?__ep=<nome>; senza rewrite si puo' usare /api/api.php?__ep=<nome>.
// L'app Android usa gli stessi indirizzi, quindi continua a funzionare.
//
// Aggiungere un endpoint = aggiungere un case + la voce in api/.htaccess
// (il rewrite e' generico: nessuna modifica necessaria).
// ============================================================

require_once __DIR__ . '/../config.php';
jsonCorsHeaders();
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(200); exit; }

// ---------- QUALE ENDPOINT? ----------
$__ep = (string)($_GET['__ep'] ?? '');
if ($__ep === '') {
    // /api/notices.php -> 'notices' (in produzione il rewrite passa __ep,
    // questo e' il ripiego quando la riscrittura non e' attiva)
    $__ep = basename((string)parse_url((string)($_SERVER['REQUEST_URI'] ?? ''), PHP_URL_PATH), '.php');
    if ($__ep === 'api') $__ep = (string)($_GET['endpoint'] ?? '');
}
$__ep = preg_replace('/[^a-z0-9_]/', '', strtolower($__ep));

// ============================================================
// AIUTANTI CONDIVISI (admin + catalogo)
// ============================================================

/** Corpo della richiesta, da JSON o da form. */
function lux_api_input(): array
{
    static $in = null;
    if ($in !== null) return $in;
    $in = $_POST;
    if (empty($in)) {
        $raw = json_decode((string)file_get_contents('php://input'), true);
        if (is_array($raw)) $in = $raw;
    }
    return $in;
}

/**
 * Solo l'admin entra qui. Per le modifiche serve anche la prova che la
 * richiesta arriva dalle nostre pagine (Origin/Referer): senza quella, un
 * sito esterno potrebbe far cancellare dati al browser dell'admin.
 */
function lux_api_admin_guard(bool $mutate = false): void
{
    lux_session_start();   // cookie HttpOnly + SameSite (vedi config.php)

    $in    = lux_api_input();
    $token = (string)($in['token'] ?? ($_GET['token'] ?? ''));
    $hasToken = ($token !== '' && verifyApiToken($token));
    if (!isAdminLoggedIn() && !$hasToken) {
        jsonResponse(['success' => false, 'error' => t('Invalid credentials')], 401);
    }

    // Il pannello manda il token anti-CSRF della sessione: e' la prova che la
    // richiesta parte dalle nostre pagine. Il controllo di provenienza sotto
    // resta come seconda barriera, ma non e' piu' l'unico modo di autorizzare
    // una modifica (su alcuni hosting l'intestazione Origin non arriva e ogni
    // invio dal pannello veniva respinto con un errore generico).
    $csrf   = (string)($in['csrf'] ?? ($_GET['csrf'] ?? ''));
    $csrfOk = lux_admin_csrf_ok($csrf);

    if ($mutate && !$hasToken && !$csrfOk) {
        // Si confrontano indirizzo E porta: HTTP_HOST porta la porta
        // ("127.0.0.1:8140"), e senza normalizzare anche quella ogni invio
        // dal pannello verrebbe rifiutato.
        $normalize = function (string $url): string {
            if ($url === '') return '';
            if (strpos($url, '//') === false) $url = 'http://' . $url;
            $p = parse_url($url);
            if (!is_array($p) || empty($p['host'])) return '';
            $scheme = strtolower((string)($p['scheme'] ?? 'http'));
            $host   = strtolower((string)$p['host']);
            $port   = isset($p['port']) ? (int)$p['port'] : ($scheme === 'https' ? 443 : 80);
            if (($scheme === 'http' && $port === 80) || ($scheme === 'https' && $port === 443)) {
                return $host;
            }
            return $host . ':' . $port;
        };

        $host = $normalize((string)($_SERVER['HTTP_HOST'] ?? ''));
        $src  = (string)($_SERVER['HTTP_ORIGIN'] ?? ($_SERVER['HTTP_REFERER'] ?? ''));
        if ($host === '' || $normalize($src) !== $host) {
            jsonResponse([
                'success' => false,
                'error'   => t('Invalid credentials'),
                'hint'    => 'csrf',
            ], 403);
        }
    }
    header('Cache-Control: private, no-store, max-age=0');
}

/** Le ultime righe di log che interessano al pannello. */
function lux_api_logs(int $limit = 200, string $q = ''): array
{
    $logs = lux_data_read('logs');
    if ($q !== '') {
        $needle = strtolower($q);
        $logs = array_values(array_filter($logs, function ($l) use ($needle) {
            return strpos(strtolower(($l['action'] ?? '') . ' ' . ($l['details'] ?? '') . ' ' . ($l['ip'] ?? '')), $needle) !== false;
        }));
    }
    usort($logs, function ($a, $b) {
        return strtotime((string)($b['timestamp'] ?? '')) <=> strtotime((string)($a['timestamp'] ?? ''));
    });
    return array_slice($logs, 0, $limit);
}

/** Una riga di log scritta dall'API (stessa forma di quella del pannello). */
function lux_api_log(string $action, string $details): void
{
    $logs = lux_data_read('logs');
    $logs[] = [
        'id'         => uniqid('LOG_'),
        'action'     => $action,
        'details'    => $details,
        'ip'         => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
        'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown',
        'timestamp'  => date('Y-m-d H:i:s'),
    ];
    if (count($logs) > 1000) $logs = array_slice($logs, -1000);
    lux_data_write('logs', $logs);
}

switch (true) {

    // ========================================================
    // catalog.php — il catalogo degli strumenti (pubblico)
    // ========================================================
    // Serve la home: senza questo endpoint il browser dovrebbe scaricare
    // 100.000 schede per mostrarne 24. Qui si chiede una pagina alla volta,
    // con ricerca e categoria risolte dal server.
    case $__ep === 'catalog': {
        $page = (int)($_GET['page'] ?? 1);
        $per  = (int)($_GET['per'] ?? 24);
        $cat  = substr((string)($_GET['cat'] ?? ''), 0, 24);
        $q    = substr((string)($_GET['q'] ?? ''), 0, 60);

        $res = lux_catalog_page([
            'page' => $page, 'per' => $per, 'cat' => $cat, 'q' => $q,
        ]);

        header('Cache-Control: public, max-age=60');
        jsonResponse([
            'success'    => true,
            'items'      => $res['items'],
            'total'      => $res['total'],
            'page'       => $res['page'],
            'pages'      => $res['pages'],
            'per'        => $res['per'],
            'generated'  => lux_catalog_generated_count(),
            'curated'    => lux_catalog_roots_count(),
            'states'     => lux_catalog_status_counts(),
            'counts'     => lux_catalog_counts_all(),
        ]);
        break;
    }

    // ========================================================
    // catalog_stats.php — numeri del catalogo (pubblico)
    // ========================================================
    case $__ep === 'catalog_stats': {
        header('Cache-Control: public, max-age=300');
        jsonResponse([
            'success'   => true,
            'generated' => lux_catalog_generated_count(),
            'curated'   => lux_catalog_roots_count(),
            'states'    => lux_catalog_status_counts(),
            'total'     => lux_catalog_total(),
            'counts'    => lux_catalog_counts_all(),
        ]);
        break;
    }

    // ========================================================
    // admin.php — un solo endpoint per tutto il pannello
    // ========================================================
    // Il pannello non ricarica piu' la pagina per ogni azione: chiede JSON,
    // aggiorna solo il pezzo che serve e resta fluido. Le operazioni sono
    // scelte con "op".
    case $__ep === 'admin': {
        $in = lux_api_input();
        $op = (string)($in['op'] ?? ($_GET['op'] ?? 'overview'));
        $mutating = in_array($op, [
            'project_add', 'project_state', 'project_delete',
            'notice_add', 'notice_update', 'notice_delete', 'notice_pin',
            'notification_send', 'notification_delete', 'notification_purge',
            'message_status', 'message_delete', 'support_update',
            'chat_reply', 'chat_close', 'feedback_status', 'bulk_state',
            'catalog_bulk', 'catalog_reset',
        ], true);

        lux_api_admin_guard($mutating);

        // ---------- PANORAMICA ----------
        if ($op === 'overview') {
            $projects = getProjectsFromLocal();
            $states   = lux_states();
            $active = 0;
            foreach ($projects as $p) {
                $k = (string)($p['key'] ?? '');
                if ($k !== '' && (($states[$k]['status'] ?? 'attivo') === 'attivo')) $active++;
            }
            $visits   = getVisitsStats();
            $notices  = getNoticesFromLocal();
            $notifs   = lux_notifications_read();
            $queue    = function_exists('lux_support_queue') ? lux_support_queue() : [];
            $pending  = 0;
            $openMsgs = 0;
            foreach (lux_data_read('messages') as $m) {
                if (stripos((string)($m['status'] ?? ''), 'risolt') === false) $openMsgs++;
            }
            foreach (lux_data_read('assistance') as $a) {
                if (stripos((string)($a['status'] ?? ''), 'risolt') === false) $pending++;
            }

            // Serie degli ultimi 14 giorni per il grafico delle visite.
            $series = [];
            $daily  = (array)($visits['daily'] ?? []);
            for ($i = 13; $i >= 0; $i--) {
                $d = date('Y-m-d', strtotime('-' . $i . ' days'));
                $series[] = ['day' => $d, 'label' => date('d/m', strtotime($d)), 'value' => (int)($daily[$d] ?? 0)];
            }

            jsonResponse([
                'success' => true,
                'kpi' => [
                    'tools'        => lux_catalog_total(),
                    'tools_delta'  => lux_catalog_generated_count(),
                    'projects'     => count($projects),
                    'active'       => $active,
                    'notices'      => count($notices),
                    'notifications'=> count($notifs),
                    'messages'     => $openMsgs,
                    'assistance'   => $pending,
                    'logs'         => count(lux_data_read('logs')),
                    'visits_today' => (int)($visits['today'] ?? 0),
                    'visits_week'  => (int)($visits['week'] ?? 0),
                    'visits_month' => (int)($visits['month'] ?? 0),
                    'visits_total' => (int)($visits['total'] ?? 0),
                    'visitors'     => (int)($visits['unique_ips'] ?? 0),
                    'queue_open'   => (int)($queue['open'] ?? 0),
                ],
                'series'     => $series,
                'pages'      => array_slice((array)($visits['pages'] ?? []), 0, 10, true),
                'activity'   => lux_api_logs(12),
                'catalog'    => [
                    'generated' => lux_catalog_generated_count(),
                    'curated'   => lux_catalog_roots_count(),
                    'total'     => lux_catalog_total(),
                    'counts'    => lux_catalog_counts_all(),
                    'status'    => lux_catalog_status_counts(),
                ],
                'health'     => (function () {
                    if (!function_exists('lux_status_report')) return null;
                    $r = lux_status_report(false);
                    return [
                        'state'    => (string)($r['state'] ?? 'ok'),
                        'counters' => (array)($r['counters'] ?? []),
                        'label'    => (string)($r['label'] ?? ''),
                    ];
                })(),
                // Salute delle 4 chiavi AI: sta in cima alla dashboard.
                'ai'         => function_exists('lux_ai_snapshot') ? lux_ai_snapshot() : [],
                // Progetti piu' prestigiosi (voti + uso), senza index/admin.
                'top'        => (function () {
                    $rows  = function_exists('lux_top_projects') ? lux_top_projects(8) : [];
                    if (!$rows) return [];
                    $names = [];
                    foreach (getProjectsFromLocal() as $p) {
                        $names[(string)($p['key'] ?? '')] = (string)($p['name'] ?? '');
                    }
                    foreach ($rows as &$r) {
                        $k  = (string)$r['key'];
                        $nm = (string)($names[$k] ?? '');
                        if ($nm === '' && function_exists('lux_catalog_index_from_slug')) {
                            $idx = lux_catalog_index_from_slug($k);
                            if ($idx !== null) { $it = lux_catalog_item($idx); if ($it) $nm = (string)$it['name']; }
                        }
                        $r['name'] = $nm !== '' ? $nm : $k;
                    }
                    unset($r);
                    return $rows;
                })(),
                'feedback'   => function_exists('lux_feedback_summary') ? lux_feedback_summary() : null,
                'guide_queue'=> function_exists('lux_chat_queue_count') ? lux_chat_queue_count() : 0,
                'now' => date('Y-m-d H:i:s'),
            ]);
        }

        // ---------- SALUTE DELLE 4 API AI ----------
        if ($op === 'ai_health') {
            jsonResponse(['success' => true, 'keys' => lux_ai_snapshot()]);
        }

        // ---------- PROGETTI PIU' PRESTIGIOSI ----------
        if ($op === 'top_projects') {
            $rows  = lux_top_projects(20);
            $names = [];
            foreach (getProjectsFromLocal() as $p) {
                $names[(string)($p['key'] ?? '')] = (string)($p['name'] ?? '');
            }
            foreach ($rows as &$r) {
                $k  = (string)$r['key'];
                $nm = (string)($names[$k] ?? '');
                if ($nm === '' && function_exists('lux_catalog_index_from_slug')) {
                    $idx = lux_catalog_index_from_slug($k);
                    if ($idx !== null) {
                        $it = lux_catalog_item($idx);
                        if ($it) $nm = (string)$it['name'];
                    }
                }
                $r['name'] = $nm !== '' ? $nm : $k;
            }
            unset($r);
            jsonResponse(['success' => true, 'rows' => $rows]);
        }

        // ---------- I 100.000 STRUMENTI: UN SOLO SISTEMA DI STATO ----------
        // Nessuna distinzione tra strumenti "scritti a mano" e generati: il
        // perimetro si sceglie (tutto il catalogo, una categoria, le voci con
        // pagina propria) e lo stato si applica a tutti insieme.
        if ($op === 'catalog_status') {
            jsonResponse([
                'success'    => true,
                'total'      => lux_catalog_total(),
                'counts'     => lux_catalog_status_counts(),
                'states'     => lux_catalog_states(),
                'categories' => lux_catalog_categories(),
                'labels'     => array_map(function ($s) { return lux_project_state_label($s); }, lux_catalog_states()),
            ]);
        }

        if ($op === 'catalog_bulk' || $op === 'bulk_state') {
            $status = (string)($in['status'] ?? 'attivo');
            $scope  = (string)($in['scope'] ?? 'all');
            if (!in_array($status, lux_catalog_states(), true)) {
                jsonResponse(['success' => false, 'error' => t('Invalid status')], 422);
            }
            // 'curated' era il vecchio nome: ora il catalogo e' uno solo.
            if ($scope === 'curated' || $scope === '') $scope = 'all';

            $n = lux_catalog_apply_status($scope, $status);
            lux_api_log('CATALOG_STATE', $n . ' strumenti -> ' . $status . ' (' . $scope . ')');
            jsonResponse([
                'success' => true,
                'changed' => $n,
                'status'  => $status,
                'scope'   => $scope,
                'counts'  => lux_catalog_status_counts(),
            ]);
        }

        // ---------- CHAT DELLA GUIDA ----------
        if ($op === 'guide_chats') {
            $filter = (string)($in['filter'] ?? 'all');
            $rows = [];
            foreach (lux_chats_all() as $c) {
                $st = (string)($c['status'] ?? 'bot');
                if ($filter === 'open' && !in_array($st, ['queued', 'answered'], true)) continue;
                if ($filter === 'closed' && $st !== 'closed') continue;
                $log  = array_values((array)($c['log'] ?? []));
                $last = $log ? $log[count($log) - 1] : ['text' => '', 'date' => '', 'from' => ''];
                $rows[] = [
                    'id'        => (string)($c['id'] ?? ''),
                    'lang'      => (string)($c['lang'] ?? 'it'),
                    'status'    => $st,
                    'opened'    => (string)($c['opened'] ?? ''),
                    'updated'   => (string)($c['updated'] ?? ''),
                    'messages'  => count($log),
                    'last'      => substr((string)($last['text'] ?? ''), 0, 90),
                    'last_from' => (string)($last['from'] ?? ''),
                ];
            }
            usort($rows, function ($a, $b) { return strcmp((string)$b['updated'], (string)$a['updated']); });
            jsonResponse(['success' => true, 'rows' => $rows, 'queue' => lux_chat_queue_count()]);
        }

        if ($op === 'guide_chat') {
            $chat = lux_chat_find((string)($in['id'] ?? ''));
            if (!$chat) jsonResponse(['success' => false, 'error' => t('Conversation not found.')], 404);
            jsonResponse(['success' => true, 'chat' => $chat]);
        }

        if ($op === 'chat_reply') {
            $id   = (string)($in['id'] ?? '');
            $text = substr(trim((string)($in['text'] ?? '')), 0, 1500);
            if ($id === '' || $text === '') jsonResponse(['success' => false, 'error' => t('Missing parameters')], 422);
            if (!lux_chat_find($id)) jsonResponse(['success' => false, 'error' => t('Conversation not found.')], 404);
            lux_chat_append($id, 'admin', $text, ['kind' => 'admin']);
            $chat = lux_chat_find($id);
            $chat['status'] = 'answered';
            lux_chat_save($chat);
            lux_api_log('CHAT_REPLY', 'Risposta dell admin alla chat ' . $id);
            jsonResponse(['success' => true, 'chat' => $chat]);
        }

        if ($op === 'chat_close') {
            $chat = lux_chat_find((string)($in['id'] ?? ''));
            if (!$chat) jsonResponse(['success' => false, 'error' => t('Conversation not found.')], 404);
            $chat['status'] = 'closed';
            lux_chat_save($chat);
            jsonResponse(['success' => true]);
        }

        // ---------- FEEDBACK ----------
        if ($op === 'feedback_list') {
            $list = lux_feedback_all();
            usort($list, function ($a, $b) { return strcmp((string)($b['date'] ?? ''), (string)($a['date'] ?? '')); });
            jsonResponse(['success' => true, 'rows' => array_slice($list, 0, 200), 'summary' => lux_feedback_summary()]);
        }

        if ($op === 'feedback_status') {
            $id = (string)($in['id'] ?? '');
            $st = (string)($in['status'] ?? 'letto');
            if (!in_array($st, ['nuovo', 'letto', 'archiviato'], true)) $st = 'letto';
            lux_feedback_set_status($id, $st);
            jsonResponse(['success' => true]);
        }

        // ---------- PROGETTI ----------
        if ($op === 'projects') {
            $q     = strtolower(trim((string)($in['q'] ?? '')));
            $state = (string)($in['state'] ?? '');
            $page  = max(1, (int)($in['page'] ?? 1));
            $per   = min(60, max(5, (int)($in['per'] ?? 24)));
            $states = lux_states();

            $rows = [];
            foreach (getProjectsFromLocal() as $p) {
                $k   = (string)($p['key'] ?? '');
                $st  = (string)($states[$k]['status'] ?? 'attivo');
                $p['status'] = $st;
                if ($state !== '' && $state !== 'all' && $st !== $state) continue;
                if ($q !== '' && strpos(strtolower($k . ' ' . ($p['name'] ?? '') . ' ' . ($p['description_it'] ?? '')), $q) === false) continue;
                $rows[] = $p;
            }
            $total = count($rows);
            $rows  = array_slice($rows, ($page - 1) * $per, $per);

            jsonResponse([
                'success' => true, 'items' => $rows, 'total' => $total, 'page' => $page,
                'pages' => (int)ceil($total / $per), 'per' => $per,
                'states' => ['attivo','manutenzione','pausa','costruzione','verifica','bloccato','archiviato','disattivato'],
            ]);
        }

        if ($op === 'project_add') {
            $name = trim((string)($in['name'] ?? ''));
            $slug = strtolower(preg_replace('/[^a-zA-Z0-9_\-]/', '', (string)($in['key'] ?? $name)));
            $descIt = trim((string)($in['description_it'] ?? ''));
            $descEn = trim((string)($in['description_en'] ?? ''));
            if ($name === '' || $slug === '') {
                jsonResponse(['success' => false, 'error' => t('Name and URL are required!')], 422);
            }
            if (lux_catalog_index_from_slug($slug) !== null || isProjectActive($slug)) {
                jsonResponse(['success' => false, 'error' => t('Project already exists!')], 409);
            }
            $projects   = getProjectsFromLocal();
            $projects[] = [
                'key' => $slug, 'name' => $name, 'url' => lux_tool_url($slug),
                'description_it' => $descIt, 'description_en' => $descEn, 'description' => $descEn !== '' ? $descEn : $descIt,
                'icon' => (string)($in['icon'] ?? 'rocket'), 'color' => (string)($in['color'] ?? '#D8A93C'),
                'category' => (string)($in['category'] ?? 'utility'),
            ];
            lux_data_write('projects', $projects);
            lux_set_project_state($slug, 'attivo');
            lux_api_log('PROJECT_ADDED', 'Progetto: ' . $name);
            lux_notification_add([
                'title_it' => 'Nuovo strumento aggiunto', 'title_en' => 'New tool added',
                'message_it' => $name . ' ora e\' disponibile nell\'ecosistema.',
                'message_en' => $name . ' is now available in the ecosystem.',
                'title' => 'New tool added', 'message' => $name . ' is now available in the ecosystem.',
                'category' => 'news', 'audience' => 'public', 'redirect' => '/bacheca.php', 'source' => 'project', 'auto' => true,
            ]);
            jsonResponse(['success' => true, 'key' => $slug, 'message' => t('Project added!')]);
        }

        if ($op === 'project_state') {
            $key   = strtolower(preg_replace('/[^a-zA-Z0-9_\-]/', '', (string)($in['key'] ?? '')));
            $state = (string)($in['state'] ?? 'attivo');
            $valid = ['attivo','manutenzione','pausa','costruzione','verifica','bloccato','archiviato','disattivato'];
            if ($key === '' || !in_array($state, $valid, true)) {
                jsonResponse(['success' => false, 'error' => t('Missing parameters')], 422);
            }
            // Vale anche per uno strumento del catalogo generato: anche lui
            // puo' avere uno stato tutto suo.
            if (!isProjectActive($key) && lux_catalog_index_from_slug($key) === null) {
                jsonResponse(['success' => false, 'error' => 'Unknown project'], 404);
            }
            lux_set_project_state($key, $state);
            lux_api_log('STATUS_UPDATE', 'Progetto: ' . $key . ', stato: ' . $state);
            jsonResponse(['success' => true, 'key' => $key, 'state' => $state]);
        }

        if ($op === 'project_delete') {
            $key = strtolower(preg_replace('/[^a-zA-Z0-9_\-]/', '', (string)($in['key'] ?? '')));
            if ($key === '') jsonResponse(['success' => false, 'error' => t('Missing parameters')], 422);
            $projects = array_values(array_filter(getProjectsFromLocal(), function ($p) use ($key) {
                return (string)($p['key'] ?? '') !== $key;
            }));
            lux_data_write('projects', $projects);
            lux_delete_project_state($key);
            lux_api_log('PROJECT_DELETED', 'Progetto: ' . $key);
            jsonResponse(['success' => true, 'key' => $key]);
        }

        // ---------- AVVISI ----------
        if ($op === 'notices') {
            jsonResponse(['success' => true, 'items' => getNoticesFromLocal()]);
        }

        if ($op === 'notice_add' || $op === 'notice_update') {
            $index  = (int)($in['index'] ?? -1);
            $row = [
                'title_it' => trim((string)($in['title_it'] ?? '')),
                'title_en' => trim((string)($in['title_en'] ?? '')),
                'message_it' => trim((string)($in['message_it'] ?? '')),
                'message_en' => trim((string)($in['message_en'] ?? '')),
                'type' => (string)($in['type'] ?? 'info'),
                'signature' => trim((string)($in['signature'] ?? 'L.U.X. Verse')),
                'start_date' => (string)($in['start_date'] ?? date('Y-m-d')),
                'end_date' => (string)($in['end_date'] ?? 'Indefinito'),
                'pinned' => !empty($in['pinned']),
                'date' => date('Y-m-d H:i:s'),
            ];
            if ($row['title_it'] === '' && $row['title_en'] === '') {
                jsonResponse(['success' => false, 'error' => t('Title and message are required!')], 422);
            }
            $row['title']   = $row['title_en'] !== '' ? $row['title_en'] : $row['title_it'];
            $row['message'] = $row['message_en'] !== '' ? $row['message_en'] : $row['message_it'];

            $notices = getNoticesFromLocal();
            if ($op === 'notice_update' && isset($notices[$index])) {
                $notices[$index] = array_merge($notices[$index], $row);
                $verb = 'NOTICE_UPDATED';
            } else {
                $notices[] = $row;
                $verb = 'NOTICE_ADDED';
                if (!empty($in['notify'])) {
                    lux_notification_add([
                        'title_it' => 'Nuovo avviso in bacheca', 'title_en' => 'New notice on the board',
                        'message_it' => (string)$row['title'], 'message_en' => (string)$row['title'],
                        'title' => (string)$row['title'], 'message' => (string)$row['title'],
                        'category' => ($row['type'] === 'event') ? 'event' : 'update',
                        'audience' => 'public', 'redirect' => '/bacheca.php', 'source' => 'notice', 'auto' => true,
                    ]);
                }
            }
            lux_data_write('notices', $notices);
            lux_api_log($verb, 'Avviso: ' . $row['title']);
            jsonResponse(['success' => true, 'items' => getNoticesFromLocal()]);
        }

        if ($op === 'notice_delete') {
            $index = (int)($in['index'] ?? -1);
            $notices = getNoticesFromLocal();
            if (!isset($notices[$index])) jsonResponse(['success' => false, 'error' => 'Not found'], 404);
            $title = (string)($notices[$index]['title'] ?? '');
            unset($notices[$index]);
            lux_data_write('notices', array_values($notices));
            lux_api_log('NOTICE_DELETED', 'Avviso: ' . $title);
            jsonResponse(['success' => true, 'items' => getNoticesFromLocal()]);
        }

        if ($op === 'notice_pin') {
            $index = (int)($in['index'] ?? -1);
            $notices = getNoticesFromLocal();
            if (!isset($notices[$index])) jsonResponse(['success' => false, 'error' => 'Not found'], 404);
            $notices[$index]['pinned'] = empty($notices[$index]['pinned']);
            lux_data_write('notices', array_values($notices));
            jsonResponse(['success' => true, 'items' => getNoticesFromLocal()]);
        }

        // ---------- NOTIFICHE (compreso il broadcast globale) ----------
        if ($op === 'notifications') {
            jsonResponse([
                'success'    => true,
                'items'      => lux_notifications_read(),
                'categories' => lux_notification_categories(),
                // L'ora del SERVER: il pannello la usa per dire quali notifiche
                // sono ancora programmate, senza confrontare orologi diversi.
                'now'        => date('Y-m-d H:i:s'),
            ]);
        }

        if ($op === 'notification_send') {
            $titleIt = trim((string)($in['title_it'] ?? ''));
            $titleEn = trim((string)($in['title_en'] ?? ''));
            $msgIt   = trim((string)($in['message_it'] ?? ''));
            $msgEn   = trim((string)($in['message_en'] ?? ''));
            $cat     = (string)($in['category'] ?? 'update');
            $aud     = (string)($in['audience'] ?? 'public');
            if (!array_key_exists($cat, lux_notification_categories())) $cat = 'info';
            if (!in_array($aud, ['public', 'admin', 'all'], true)) $aud = 'public';
            $title = $titleIt !== '' ? $titleIt : $titleEn;
            if ($title === '') {
                jsonResponse(['success' => false, 'error' => t('Notification title is required!')], 422);
            }            $ok = lux_notification_add([
                'title_it' => $titleIt, 'title_en' => $titleEn,
                'message_it' => $msgIt, 'message_en' => $msgEn,
                'title' => $titleEn !== '' ? $titleEn : $titleIt,
                'message' => $msgEn !== '' ? $msgEn : $msgIt,
                'category' => $cat, 'audience' => $aud, 'redirect' => trim((string)($in['redirect'] ?? '')),
                'source' => 'admin', 'auto' => false,
                'publish_at' => trim((string)($in['publish_at'] ?? '')),
                // Scostamento da UTC dell'orologio di chi scrive: serve a
                // leggere l'ora programmata nel fuso giusto (vedi
                // lux_notification_publish_at in config.php).
                'publish_tz' => $in['publish_tz'] ?? null,
            ]);
            if (!$ok) {
                jsonResponse(['success' => false, 'error' => t('Error saving data!') . ' (data/notifications.json)'], 500);
            }

            // Non basta "la scrittura non ha dato errore": si rilegge il file
            // dal disco e si controlla che la notifica ci sia davvero. Se non
            // c'è, il pannello lo dice, invece di mostrare un invio riuscito
            // che poi non arriva a nessuno.
            $newId = (string)($GLOBALS['__lux_notif_last_id'] ?? '');
            unset($GLOBALS['__lux_notifs']);
            $items = lux_notifications_read();
            $sent  = null;
            foreach ($items as $n) {
                if (is_array($n) && $newId !== '' && (string)($n['id'] ?? '') === $newId) { $sent = $n; break; }
            }
            if ($sent === null) {
                jsonResponse(['success' => false, 'error' => t('Error saving data!') . ' (notifications.json non aggiornato)'], 500);
            }

            lux_api_log('NOTIFICATION_SENT', 'Notifica: ' . $title . ' (' . $aud . ')'
                . (!empty($sent['publish_at']) ? ' · programmata per ' . $sent['publish_at'] : ' · subito'));
            jsonResponse([
                'success'   => true,
                'items'     => $items,
                'sent'      => $sent,
                'count'     => count($items),
                'scheduled' => !empty($sent['publish_at']),
                'now'       => date('Y-m-d H:i:s'),
            ]);
        }

        if ($op === 'notification_delete' || $op === 'notification_purge') {
            $id  = (string)($in['id'] ?? '');
            $all = ($op === 'notification_purge');
            $list = lux_notifications_read();
            $list = array_values(array_filter($list, function ($n) use ($id, $all) {
                return $all ? false : ((string)($n['id'] ?? '') !== $id);
            }));
            lux_notifications_write($list);
            lux_api_log($all ? 'NOTIFICATION_PURGED' : 'NOTIFICATION_DELETED', $all ? 'tutte' : $id);
            jsonResponse(['success' => true, 'items' => $list]);
        }

        // ---------- MESSAGGI ----------
        if ($op === 'messages') {
            $rows = lux_data_read('messages');
            usort($rows, function ($a, $b) {
                return strtotime((string)($b['date'] ?? '')) <=> strtotime((string)($a['date'] ?? ''));
            });
            jsonResponse(['success' => true, 'items' => $rows, 'count' => count($rows)]);
        }

        if ($op === 'message_status') {
            $code = (string)($in['code'] ?? '');
            $new  = (string)($in['status'] ?? 'Risolto');
            $rows = lux_data_read('messages');
            $found = false;
            foreach ($rows as $i => $m) {
                if ((string)($m['code'] ?? '') === $code) {
                    $rows[$i]['status'] = $new;
                    $rows[$i]['resolved_at'] = date('Y-m-d H:i:s');
                    $found = true;
                }
            }
            if (!$found) jsonResponse(['success' => false, 'error' => 'Not found'], 404);
            lux_data_write('messages', $rows);
            lux_api_log('MESSAGE_STATUS', $code . ' -> ' . $new);
            jsonResponse(['success' => true, 'code' => $code, 'status' => $new]);
        }

        if ($op === 'message_delete') {
            $code = (string)($in['code'] ?? '');
            $rows = array_values(array_filter(lux_data_read('messages'), function ($m) use ($code) {
                return (string)($m['code'] ?? '') !== $code;
            }));
            lux_data_write('messages', $rows);
            lux_api_log('MESSAGE_DELETED', 'Messaggio: ' . $code);
            jsonResponse(['success' => true, 'items' => $rows]);
        }

        // ---------- ASSISTENZA ----------
        if ($op === 'support') {
            $rows = lux_data_read('assistance');
            usort($rows, function ($a, $b) {
                return strtotime((string)($b['date'] ?? '')) <=> strtotime((string)($a['date'] ?? ''));
            });
            jsonResponse([
                'success' => true,
                'items'   => $rows,
                'queue'   => function_exists('lux_support_queue') ? lux_support_queue() : [],
                'count'   => count($rows),
            ]);
        }

        if ($op === 'support_update') {
            $id    = (string)($in['id'] ?? ($in['code'] ?? ''));
            $new   = (string)($in['status'] ?? 'Risolto');
            $rows  = lux_data_read('assistance');
            $found = false;
            foreach ($rows as $i => $r) {
                $rid = (string)($r['id'] ?? ($r['code'] ?? ''));
                if ($rid === $id) {
                    $rows[$i]['status'] = $new;
                    $rows[$i]['resolved_at'] = date('Y-m-d H:i:s');
                    $found = true;
                }
            }
            if (!$found) jsonResponse(['success' => false, 'error' => 'Not found'], 404);
            lux_data_write('assistance', $rows);
            lux_api_log('SUPPORT_UPDATED', $id . ' -> ' . $new);
            jsonResponse(['success' => true, 'id' => $id, 'status' => $new]);
        }

        // ---------- VISITE ----------
        if ($op === 'visits') {
            jsonResponse(['success' => true, 'stats' => getVisitsStats()]);
        }

        // ---------- LOG ----------
        if ($op === 'logs') {
            $rows = lux_api_logs((int)($in['limit'] ?? 200), (string)($in['q'] ?? ''));
            jsonResponse(['success' => true, 'items' => $rows, 'count' => count($rows)]);
        }

        // ---------- SISTEMA ----------
        if ($op === 'system') {
            $root = dirname(__DIR__);
            $scan = function_exists('lux_core_scan') ? lux_core_scan($root) : [];
            jsonResponse([
                'success' => true,
                'php'      => PHP_VERSION,
                'server'   => (string)($_SERVER['SERVER_SOFTWARE'] ?? ''),
                'time'     => date('Y-m-d H:i:s'),
                'ai'       => (GROQ_API_KEY !== ''),
                'curland'  => function_exists('curl_init'),
                'disk_free'=> (function () use ($root) { $f = @disk_free_space($root); return $f ? round($f / 1048576, 1) : null; })(),
                'data'     => (function () {
                    $out = [];
                    $dir = dirname(__DIR__) . '/data';
                    foreach (glob($dir . '/*.json') ?: [] as $f) {
                        $out[basename($f)] = filesize($f);
                    }
                    arsort($out);
                    return $out;
                })(),
                'scan'     => $scan,
                'report'   => function_exists('lux_status_report') ? lux_status_report(true) : null,
            ]);
        }

        // ---------- CATALOGO (vista admin) ----------
        if ($op === 'catalog') {
            $res = lux_catalog_page([
                'page' => (int)($in['page'] ?? 1),
                'per'  => (int)($in['per'] ?? 24),
                'cat'  => (string)($in['cat'] ?? ''),
                'q'    => (string)($in['q'] ?? ''),
            ]);
            jsonResponse([
                'success' => true, 'items' => $res['items'], 'total' => $res['total'],
                'page' => $res['page'], 'pages' => $res['pages'],
                'generated' => lux_catalog_generated_count(), 'curated' => lux_catalog_roots_count(),
                'counts' => lux_catalog_counts_all(),
                'status' => lux_catalog_status_counts(),
            ]);
        }

        jsonResponse(['success' => false, 'error' => 'Unknown op: ' . $op], 400);
        break;
    }

    // ========================================================
    // add_log.php  (era api/add_log.php)
    // ========================================================
    case $__ep === 'add_log': {
$input = json_decode(file_get_contents('php://input'), true);
if (!$input) $input = $_POST;

$logData = [
    'id'         => uniqid('LOG_'),
    'action'     => $input['action'] ?? 'ACTION',
    'details'    => $input['details'] ?? '',
    'ip'         => $input['ip'] ?? $_SERVER['REMOTE_ADDR'],
    'user_agent' => $input['userAgent'] ?? $_SERVER['HTTP_USER_AGENT'],
    'timestamp'  => $input['timestamp'] ?? date('Y-m-d H:i:s'),
    'url'        => $input['url'] ?? $_SERVER['REQUEST_URI']
];

$logsFile = __DIR__ . '/../data/logs.json';
$logs = [];
if (file_exists($logsFile)) $logs = json_decode(file_get_contents($logsFile), true) ?? [];
$logs[] = $logData;
if (count($logs) > 1000) $logs = array_slice($logs, -1000);
file_put_contents($logsFile, json_encode($logs, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

jsonResponse(['success' => true, 'message' => 'Log added', 'log_id' => $logData['id'], 'timestamp' => $logData['timestamp']]);
        break;
    }

    // ========================================================
    // content.php  (era api/content.php)
    // ========================================================
    case $__ep === 'content': {
// ============================================================
// API UNIVERSALE: contenuti CMS (pagine info)
// GET  -> contenuti modificati dall'amministrazione (pubblico)
// POST -> salvataggio universale (richiede password admin o token)
// ============================================================




$contentFile = __DIR__ . '/../data/content.json';

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $content = [];
    if (file_exists($contentFile)) $content = json_decode(file_get_contents($contentFile), true) ?? [];
    jsonResponse(['success' => true, 'content' => $content]);
}

$input = $_POST;
if (empty($input)) {
    $jsonInput = json_decode(file_get_contents('php://input'), true);
    if (is_array($jsonInput)) $input = $jsonInput;
}

$password = $input['password'] ?? '';
$page     = trim($input['page'] ?? '');
$html     = $input['html'] ?? null;
$reset    = isset($input['reset']) ? (int)$input['reset'] : 0;

if (!lux_admin_password_ok($password) && !verifyApiToken($input['token'] ?? '')) {
    jsonResponse(['success' => false, 'error' => t('Invalid credentials')], 403);
}

$allowed = ['chi-siamo', 'faq', 'assistenza', 'contattaci', 'privacy', 'terms'];
if (!in_array($page, $allowed, true)) {
    jsonResponse(['success' => false, 'error' => t('Invalid page')]);
}

$content = [];
if (file_exists($contentFile)) $content = json_decode(file_get_contents($contentFile), true) ?? [];

if ($reset) {
    unset($content[$page]);
} else {
    if ($html === null) jsonResponse(['success' => false, 'error' => t('Missing html')]);
    $content[$page] = $html;
    $content['_updated'] = date('Y-m-d H:i:s');
}

if (!is_dir(dirname($contentFile))) mkdir(dirname($contentFile), 0755, true);
file_put_contents($contentFile, json_encode($content, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

jsonResponse(['success' => true, 'page' => $page, 'reset' => (bool)$reset]);
        break;
    }

    // ========================================================
    // get_logs.php  (era api/get_logs.php)
    // ========================================================
    case $__ep === 'get_logs': {
if (!checkApiToken()) jsonResponse(['success' => false, 'error' => t('Invalid token')], 403);

$logsFile = __DIR__ . '/../data/logs.json';
$logs = [];
if (file_exists($logsFile)) $logs = json_decode(file_get_contents($logsFile), true) ?? [];

$actionFilter = $_GET['action'] ?? $_POST['action'] ?? '';
if ($actionFilter !== '') {
    $logs = array_filter($logs, function($log) use ($actionFilter) {
        return ($log['action'] ?? '') === $actionFilter;
    });
}

usort($logs, function($a, $b) {
    return strtotime($b['timestamp'] ?? '') - strtotime($a['timestamp'] ?? '');
});

$limit = intval($_GET['limit'] ?? $_POST['limit'] ?? 100);
if ($limit > 0) $logs = array_slice($logs, 0, $limit);

jsonResponse(['success' => true, 'logs' => array_values($logs), 'count' => count($logs)]);
        break;
    }

    // ========================================================
    // get_messages.php  (era api/get_messages.php)
    // ========================================================
    case $__ep === 'get_messages': {
$authError = false;
if (checkApiToken()) {
    // ok, token valido
} elseif (isset($_POST['password']) && lux_admin_password_ok($_POST['password'])) {
    // ok, password admin valida
} else {
    jsonResponse(['success' => false, 'error' => t('Invalid token')], 403);
}

$messagesFile = __DIR__ . '/../data/messages.json';
$messages = [];
if (file_exists($messagesFile)) $messages = json_decode(file_get_contents($messagesFile), true) ?? [];

$projectFilter = $_GET['project'] ?? $_POST['project'] ?? '';
$statusFilter  = $_GET['status']  ?? $_POST['status']  ?? '';

if ($projectFilter !== '') {
    $messages = array_filter($messages, function($msg) use ($projectFilter) {
        return $msg['project'] === $projectFilter;
    });
}
if ($statusFilter !== '') {
    $messages = array_filter($messages, function($msg) use ($statusFilter) {
        return $msg['status'] === $statusFilter;
    });
}

usort($messages, function($a, $b) {
    return strtotime($b['date']) - strtotime($a['date']);
});

jsonResponse(['success' => true, 'messages' => array_values($messages), 'count' => count($messages)]);
        break;
    }

    // ========================================================
    // get_notices.php  (era api/get_notices.php)
    // ========================================================
    case $__ep === 'get_notices': {
$noticesFile = __DIR__ . '/../data/notices.json';
$notices = [];
if (file_exists($noticesFile)) $notices = json_decode(file_get_contents($noticesFile), true) ?? [];

jsonResponse(['success' => true, 'notices' => $notices, 'count' => count($notices)]);
        break;
    }

    // ========================================================
    // get_projects.php  (era api/get_projects.php)
    // ========================================================
    case $__ep === 'get_projects': {
$projectsFile = __DIR__ . '/../data/projects.json';
$projects = [];

if (!file_exists($projectsFile)) {
    jsonResponse(['success' => false, 'error' => 'File projects.json not found', 'projects' => [], 'count' => 0]);
}

$jsonProjects = json_decode(file_get_contents($projectsFile), true);
if (!is_array($jsonProjects)) {
    jsonResponse(['success' => false, 'error' => 'projects.json invalid', 'projects' => [], 'count' => 0]);
}

// Gli stati arrivano tutti da data/states.json (un solo file, vedi config.php).
$projectStates = [];
foreach (lux_states() as $__stKey => $__stData) {
    $projectStates[$__stKey] = $__stData['status'] ?? 'attivo';
}
unset($__stKey, $__stData);

// Tutti gli strumenti vivono in un unico file (tool.php): l'elenco valido e'
// il catalogo data/projects.json, non la presenza di un file per progetto.
foreach ($jsonProjects as $p) {
    if (!isset($p['key'])) continue;
    $key = $p['key'];
    if (!isProjectActive($key)) continue;
    $projects[] = [
        'key'          => $key,
        'name'         => $p['name'] ?? ucfirst($key),
        'url'          => $p['url'] ?? ('https://luxverse.c22.eu/' . $key . '.php'),
        'description'  => $p['description_en'] ?? $p['description'] ?? '',
        'description_en' => $p['description_en'] ?? $p['description'] ?? '',
        'description_it' => $p['description_it'] ?? $p['description'] ?? '',
        'icon'         => $p['icon'] ?? 'rocket',
        'color'        => $p['color'] ?? '#00BFFF',
        'category'     => $p['category'] ?? 'other',
        'status'       => $projectStates[$key] ?? 'attivo'
    ];
}

jsonResponse(['success' => true, 'projects' => $projects, 'count' => count($projects)]);
        break;
    }

    // ========================================================
    // realtime.php — una sola chiamata per tutto ciò che cambia
    // ========================================================
    // Il sito, l'app e il pannello ripetono questa chiamata ogni pochi
    // secondi. Risponde con una revisione (rev): se non è cambiata, il
    // client non rifà niente. Così le notifiche, la chat, il trasferimento a
    // un umano e gli stati degli strumenti arrivano tutti insieme, in tempo
    // reale, con una richiesta minima invece di cinque polling diversi.
    case $__ep === 'realtime': {
        lux_session_start();
        $isAdmin = isAdminLoggedIn();

        // I file che contano: la revisione è la loro impronta.
        $watch = ['chats', 'notifications', 'notices', 'states', 'status', 'feedback'];
        $parts = [];
        foreach ($watch as $f) {
            $path = lux_data_file($f);
            $parts[] = $f . '=' . (is_file($path) ? (filemtime($path) . '.' . filesize($path)) : '0');
        }
        $rev = substr(md5(implode('|', $parts)), 0, 12);

        $since   = substr((string)($_GET['since'] ?? ''), 0, 12);
        $changed = ($since === '' || $since !== $rev);

        $out = [
            'success' => true,
            'rev'     => $rev,
            'changed' => $changed,
            'now'     => date('Y-m-d H:i:s'),
        ];

        if ($changed) {
            // ---- notifiche visibili a chi chiede ----
            $limit = (int)($_GET['limit'] ?? 20);
            if ($limit < 1 || $limit > 50) $limit = 20;
            $list = [];
            foreach (lux_notifications_read() as $n) {
                $aud = (string)($n['audience'] ?? 'public');
                if ($aud === 'admin' && !$isAdmin) continue;
                // Programmata nel futuro: ancora nessuno la vede.
                if (lux_notification_pending($n)) continue;
                $list[] = [
                    'id'       => (string)($n['id'] ?? ''),
                    'title'    => lux_notification_text($n, 'title'),
                    'message'  => lux_notification_text($n, 'message'),
                    'category' => (string)($n['category'] ?? 'info'),
                    'redirect' => (string)($n['redirect'] ?? ''),
                    'date'     => (string)($n['date'] ?? ''),
                    'audience' => $aud,
                ];
                if (count($list) >= $limit) break;
            }
            $out['notifications'] = $list;
            $out['notifications_count'] = count($list);
        }

        // ---- chat della guida aperta da chi chiede ----
        $chatId = substr(preg_replace('/[^a-zA-Z0-9_\-]/', '', (string)($_GET['chat'] ?? '')), 0, 40);
        if ($chatId !== '' && function_exists('lux_chat_find')) {
            $chat = lux_chat_find($chatId);
            if ($chat) {
                $log  = array_values((array)($chat['log'] ?? []));
                $last = $log ? $log[count($log) - 1] : [];
                $out['chat'] = [
                    'id'      => $chatId,
                    'status'  => (string)($chat['status'] ?? 'bot'),
                    'count'   => count($log),
                    'updated' => (string)($chat['updated'] ?? ''),
                    'last'    => ['from' => (string)($last['from'] ?? ''), 'text' => (string)($last['text'] ?? '')],
                ];
            }
        }

        // ---- roba da pannello ----
        if ($isAdmin) {
            $out['queue']  = function_exists('lux_chat_queue_count') ? lux_chat_queue_count() : 0;
            $out['counts'] = lux_catalog_status_counts();
        }

        header('Cache-Control: private, no-store, max-age=0');
        jsonResponse($out);
        break;
    }

    // ========================================================
    // notifications.php  — notifiche globali (pubblico + admin)
    // ========================================================
    // Il pubblico riceve solo audience=public; l'admin loggato riceve anche
    // le notifiche interne. ?since=YYYY-MM-DD HH:MM:SS per l'aggiornamento
    // incrementale (il campanello in alto a destra).
    case $__ep === 'notifications': {
        // Serve la sessione: senza session_start() qui dentro isAdminLoggedIn()
        // risponde sempre "no" e le notifiche riservate all'admin non le
        // vedrebbe nessuno (nemmeno l'admin stesso).
        lux_session_start();
        $isAdmin = isAdminLoggedIn();
        $since = trim((string)($_GET['since'] ?? ''));
        $limit = (int)($_GET['limit'] ?? 20);
        if ($limit < 1 || $limit > 50) $limit = 20;

        $out = [];
        foreach (lux_notifications_read() as $n) {
            $audience = (string)($n['audience'] ?? 'public');
            if ($audience === 'admin' && !$isAdmin) continue;
            // Notifiche programmate: finche' l'ora non e' arrivata nessuno le vede.
            if (lux_notification_pending($n)) continue;
            // Le notifiche senza data (voci vecchie, scritte da altri
            // strumenti) non si buttano via per un confronto con una stringa
            // vuota: si mostrano e basta.
            $date = (string)($n['date'] ?? '');
            if ($since !== '' && $date !== '' && $date <= $since) continue;
            $out[] = [
                'id'       => (string)($n['id'] ?? ''),
                'title'    => lux_notification_text($n, 'title'),
                'message'  => lux_notification_text($n, 'message'),
                'category' => (string)($n['category'] ?? 'info'),
                'redirect' => (string)($n['redirect'] ?? ''),
                'date'     => (string)($n['date'] ?? ''),
                'audience' => $audience,
                'auto'     => !empty($n['auto']),
            ];
            if (count($out) >= $limit) break;
        }

        // La risposta cambia da persona a persona (admin o visitatore): non
        // deve finire in nessuna cache condivisa.
        header('Cache-Control: private, no-store, max-age=0');

        jsonResponse([
            'success'       => true,
            'now'           => date('Y-m-d H:i:s'),
            'count'         => count($out),
            'total'         => lux_notifications_count('public') + ($isAdmin ? lux_notifications_count('admin') : 0),
            'notifications' => $out,
        ]);
        break;
    }

    // ========================================================
    // get_signatures.php  (era api/get_signatures.php)
    // ========================================================
    case $__ep === 'get_signatures': {
if (!checkApiToken()) jsonResponse(['success' => false, 'error' => t('Invalid token')], 403);

$signaturesFile = __DIR__ . '/../data/signatures.json';
$signatures = [];
if (file_exists($signaturesFile)) $signatures = json_decode(file_get_contents($signaturesFile), true) ?? [];

usort($signatures, function($a, $b) {
    return strtotime($b['timestamp'] ?? '') - strtotime($a['timestamp'] ?? '');
});

$limit = intval($_GET['limit'] ?? $_POST['limit'] ?? 100);
if ($limit > 0) $signatures = array_slice($signatures, 0, $limit);

jsonResponse(['success' => true, 'signatures' => array_values($signatures), 'count' => count($signatures)]);
        break;
    }

    // ========================================================
    // get_status.php  (era api/get_status.php)
    // ========================================================
    case $__ep === 'get_status': {
$projectsFile = __DIR__ . '/../data/projects.json';
$jsonProjects = [];
if (file_exists($projectsFile)) $jsonProjects = json_decode(file_get_contents($projectsFile), true) ?? [];

// Gli stati arrivano tutti da data/states.json (un solo file, vedi config.php).
$projectStates = [];
foreach (lux_states() as $__stKey => $__stData) {
    $projectStates[$__stKey] = $__stData['status'] ?? 'attivo';
}
unset($__stKey, $__stData);

// Tutti gli strumenti vivono in un unico file (tool.php): l'elenco arriva dal
// catalogo data/projects.json, non piu' dalla lettura della cartella.
$projects = [];
foreach ($jsonProjects as $p) {
    $projectKey = (string)($p['key'] ?? '');
    if ($projectKey === '') continue;
    if (!isProjectActive($projectKey)) continue;

    $projects[] = [
        'key'         => $projectKey,
        'name'        => $p['name'] ?? ucfirst($projectKey),
        'url'         => $p['url'] ?? ('https://luxverse.c22.eu/' . $projectKey . '.php'),
        'description' => $p['description_en'] ?? $p['description'] ?? '',
        'icon'        => $p['icon'] ?? 'rocket',
        'color'       => $p['color'] ?? '#00BFFF',
        'status'      => $projectStates[$projectKey] ?? 'attivo'
    ];
}

jsonResponse([
    'success'   => true,
    'timestamp' => date('Y-m-d H:i:s'),
    'projects'  => $projects,
    'count'     => count($projects)
]);
        break;
    }

    // ========================================================
    // heartbeat.php  (era api/heartbeat.php)
    // ========================================================
    case $__ep === 'heartbeat': {
if (!checkApiToken()) jsonResponse(['success' => false, 'error' => t('Invalid token')], 403);

$hqResult = getProjectsFromHQ();
if (!$hqResult['success']) {
    jsonResponse(['success' => false, 'error' => 'HQ unreachable: ' . $hqResult['error']]);
}

$heartbeats = [];
foreach ($hqResult['projects'] as $project) {
    $projectKey = strtolower(preg_replace('/[^a-zA-Z0-9]/', '', $project['name']));
    $heartbeats[] = [
        'key'       => $projectKey,
        'name'      => $project['name'],
        'active'    => isProjectActive($projectKey),
        'status'    => getProjectStatus($projectKey),
        'timestamp' => date('Y-m-d H:i:s')
    ];
}

jsonResponse(['success' => true, 'heartbeats' => $heartbeats]);
        break;
    }

    // ========================================================
    // latest_version.php  (era api/latest_version.php)
    // ========================================================
    case $__ep === 'latest_version': {
// Endpoint usato dall'app per l'auto-aggiornamento in-app.
//
// I numeri NON sono piu' scritti a mano qui: si leggono da config.php
// (LUX_APP_VERSION / LUX_APP_VERSION_CODE). Prima erano fermi a 3.8 e l'app,
// gia' alla 39, non vedeva mai l'avviso di aggiornamento: la versione del sito
// e quella dichiarata all'app non possono piu' separarsi.
$__appCode = defined('LUX_APP_VERSION_CODE') ? (int)LUX_APP_VERSION_CODE : 0;
$__appName = defined('LUX_APP_VERSION') ? (string)LUX_APP_VERSION : '0.0.0';
$__siteUrl = defined('LUX_SITE_URL') ? LUX_SITE_URL : 'https://luxverse.c22.eu';
$version = [
    'success'      => true,
    'version_code' => $__appCode,
    'version_name' => $__appName,
    'url'          => $__siteUrl . '/LUXVerse.apk',
    'message'      => [
        // Il testo dice CHE COSA CAMBIA, non solo che c'e' una versione nuova:
        // chi aggiorna deve sapere perche' gli conviene. 39.1.3 = via la
        // schermata nera (la verifica dell'hosting consegnava una pagina dal
        // corpo vuoto e l'app la scambiava per il sito).
        'it' => 'L.U.X. Verse ' . $__appName . ' e\' disponibile: niente piu\' schermata nera all\'avvio, il sito compare appena e\' pronto invece di restare nero, e l\'avviso di aggiornamento arriva anche quando l\'hosting chiede la verifica anti-bot. Aggiorna ora!',
        'en' => 'L.U.X. Verse ' . $__appName . ' is available: no more black screen at start-up, the site appears as soon as it is ready instead of staying black, and the update check now works even when the hosting asks for the anti-bot verification. Update now!'
    ]
];

jsonResponse($version);
        break;
    }

    // ========================================================
    // login.php  (era api/login.php)
    // ========================================================
    case $__ep === 'login': {
// ============================================================
// API UNIVERSALE: login amministrazione
// POST { password } -> verifica la password amministratore
// Accesso riservato SOLO a chi conosce la password.
// ============================================================




$input = $_POST;
if (empty($input)) {
    $jsonInput = json_decode(file_get_contents('php://input'), true);
    if (is_array($jsonInput)) $input = $jsonInput;
}

$password = $input['password'] ?? '';
if ($password === '') {
    jsonResponse(['success' => false, 'error' => 'Missing password']);
}

if (lux_admin_password_ok($password)) {
    jsonResponse(['success' => true, 'admin' => true]);
}
jsonResponse(['success' => false, 'error' => 'Invalid credentials'], 403);
        break;
    }

    // ========================================================
    // messages.php  (era api/messages.php)
    // ========================================================
    case $__ep === 'messages': {
// ============================================================
// API UNIVERSALE: messaggi (Assistenza + Contatti)
// GET  -> elenco (token o password admin)
// POST -> elimina (token o password admin)
// ============================================================




$input = $_POST;
if (empty($input)) {
    $jsonInput = json_decode(file_get_contents('php://input'), true);
    if (is_array($jsonInput)) $input = $jsonInput;
}

// Confronto a tempo costante, e con il token vuoto la porta resta chiusa:
// un '===' diretto con API_TOKEN vuoto accetterebbe un token vuoto.
$tokOk = verifyApiToken((string)($input['token'] ?? ''));
if (!$tokOk && !lux_admin_password_ok($input['password'] ?? '')) {
    jsonResponse(['success' => false, 'error' => t('Invalid token')], 403);
}

$messagesFile = __DIR__ . '/../data/messages.json';
$messages = [];
if (file_exists($messagesFile)) $messages = json_decode(file_get_contents($messagesFile), true) ?? [];

$action = $input['action'] ?? 'list';

if ($action === 'delete') {
    $code = $input['code'] ?? '';
    if ($code === '') jsonResponse(['success' => false, 'error' => 'Missing code']);
    foreach ($messages as $i => $m) {
        if (($m['code'] ?? '') === $code) { array_splice($messages, $i, 1); break; }
    }
    if (!is_dir(dirname($messagesFile))) mkdir(dirname($messagesFile), 0755, true);
    file_put_contents($messagesFile, json_encode($messages, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    jsonResponse(['success' => true, 'action' => 'delete', 'count' => count($messages)]);
}

usort($messages, function ($a, $b) {
    return strtotime($b['date'] ?? '') - strtotime($a['date'] ?? '');
});

jsonResponse(['success' => true, 'messages' => array_values($messages), 'count' => count($messages)]);
        break;
    }

    // ========================================================
    // notices.php  (era api/notices.php)
    // ========================================================
    case $__ep === 'notices': {
// ============================================================
// API UNIVERSALE: bacheca / notizie
// GET  -> notizie (pubblico)
// POST -> pubblica o elimina una notizia (password admin o token)
// ============================================================




$noticesFile = __DIR__ . '/../data/notices.json';

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $notices = [];
    if (file_exists($noticesFile)) $notices = json_decode(file_get_contents($noticesFile), true) ?? [];
    jsonResponse(['success' => true, 'notices' => $notices, 'count' => count($notices)]);
}

$input = $_POST;
if (empty($input)) {
    $jsonInput = json_decode(file_get_contents('php://input'), true);
    if (is_array($jsonInput)) $input = $jsonInput;
}

$action   = $input['action'] ?? 'add';
$password = $input['password'] ?? '';
if (!lux_admin_password_ok($password) && !verifyApiToken($input['token'] ?? '')) {
    jsonResponse(['success' => false, 'error' => t('Invalid credentials')], 403);
}

$notices = [];
if (file_exists($noticesFile)) $notices = json_decode(file_get_contents($noticesFile), true) ?? [];

if ($action === 'delete') {
    $id = $input['id'] ?? '';
    foreach ($notices as $i => $n) {
        $nid = isset($n['id']) ? (string)$n['id'] : ('idx' . $i);
        if ($nid === (string)$id) { array_splice($notices, $i, 1); break; }
    }
} else {
    $titleIt    = trim($input['title_it'] ?? '');
    $titleEn    = trim($input['title_en'] ?? '');
    $messageIt  = trim($input['message_it'] ?? '');
    $messageEn  = trim($input['message_en'] ?? '');
    $type       = trim($input['type'] ?? 'info');
    if ($titleIt === '' || $messageIt === '') {
        jsonResponse(['success' => false, 'error' => 'Missing title or message']);
    }
    if (empty($titleEn)) $titleEn = $titleIt;
    if (empty($messageEn)) $messageEn = $messageIt;
    $now = date('Y-m-d H:i:s');
    $notices[] = [
        'id'        => 'n' . time() . mt_rand(100, 999),
        'title_it'  => $titleIt,
        'title_en'  => $titleEn,
        'message_it'=> $messageIt,
        'message_en'=> $messageEn,
        'title'     => $titleIt,
        'message'   => $messageIt,
        'type'      => $type,
        'signature' => 'L.U.X. Verse ADMIN',
        'date'      => $now,
        'pinned'    => false
    ];
}

if (!is_dir(dirname($noticesFile))) mkdir(dirname($noticesFile), 0755, true);
file_put_contents($noticesFile, json_encode($notices, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

jsonResponse(['success' => true, 'action' => $action, 'count' => count($notices)]);
        break;
    }

    // ========================================================
    // save_signature.php  (era api/save_signature.php)
    // ========================================================
    case $__ep === 'save_signature': {
$input = json_decode(file_get_contents('php://input'), true);
if (!$input) $input = $_POST;

$signatureData = [
    'id'        => uniqid('SIG_'),
    'timestamp' => $input['timestamp'] ?? date('Y-m-d H:i:s'),
    'ip'        => $input['ip'] ?? $_SERVER['REMOTE_ADDR'],
    'userAgent' => $input['userAgent'] ?? $_SERVER['HTTP_USER_AGENT'],
    'url'       => $input['url'] ?? $_SERVER['REQUEST_URI'],
    'action'    => $input['action'] ?? 'firma_digitale',
    'status'    => $input['status'] ?? 'ignored_signed'
];

$signaturesFile = __DIR__ . '/../data/signatures.json';
$signatures = [];
if (file_exists($signaturesFile)) $signatures = json_decode(file_get_contents($signaturesFile), true) ?? [];
$signatures[] = $signatureData;
if (count($signatures) > 1000) $signatures = array_slice($signatures, -1000);
file_put_contents($signaturesFile, json_encode($signatures, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

$logsFile = __DIR__ . '/../data/logs.json';
$logs = [];
if (file_exists($logsFile)) $logs = json_decode(file_get_contents($logsFile), true) ?? [];
$logs[] = [
    'id'         => uniqid('LOG_'),
    'action'     => 'FIRMA_DIGITALE',
    'details'    => 'User signed digitally',
    'ip'         => $signatureData['ip'],
    'user_agent' => $signatureData['userAgent'],
    'timestamp'  => date('Y-m-d H:i:s')
];
if (count($logs) > 1000) $logs = array_slice($logs, -1000);
file_put_contents($logsFile, json_encode($logs, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

jsonResponse([
    'success'      => true,
    'message'      => 'Digital signature captured',
    'signature_id' => $signatureData['id'],
    'ip'           => $signatureData['ip'],
    'timestamp'    => $signatureData['timestamp']
]);
        break;
    }

    // ========================================================
    // send_message.php  (era api/send_message.php)
    // ========================================================
    case $__ep === 'send_message': {
$input = json_decode(file_get_contents('php://input'), true);
if (!$input || empty($input)) $input = $_POST;

if (!isset($input['name']) || !isset($input['email']) || !isset($input['message']) || !isset($input['project']) || !isset($input['type'])) {
    jsonResponse(['success' => false, 'error' => t('Missing parameters')]);
}

$name    = $input['name'];
$email   = $input['email'];
$message = $input['message'];
$project = $input['project'];
$type    = $input['type'];

$code = strtoupper(substr(md5(uniqid(mt_rand(), true)), 0, 8));

$messagesFile = __DIR__ . '/../data/messages.json';
$messages = [];
if (file_exists($messagesFile)) $messages = json_decode(file_get_contents($messagesFile), true) ?? [];

foreach ($messages as $msg) {
    if (($msg['project'] ?? '') === $project && isset($msg['status']) && strpos($msg['status'], 'In Attesa') !== false) {
        jsonResponse(['success' => false, 'error' => 'You already have a pending message for this project. Wait for it to be resolved.']);
    }
}

$messages[] = [
    'code'    => $code,
    'project' => $project,
    'type'    => $type,
    'name'    => $name,
    'email'   => $email,
    'message' => $message,
    'date'    => date('Y-m-d H:i:s'),
    'status'  => 'In Attesa'
];

if (!is_dir(dirname($messagesFile))) mkdir(dirname($messagesFile), 0755, true);
file_put_contents($messagesFile, json_encode($messages, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

// Notifica interna: e' arrivato un messaggio dal sito.
lux_notification_add([
    'title_it'   => 'Nuovo messaggio: ' . $type,
    'title_en'   => 'New message: ' . $type,
    'message_it' => $name . ' ha scritto per "' . $project . '". Codice ' . $code . '.',
    'message_en' => $name . ' wrote about "' . $project . '". Code ' . $code . '.',
    'title'      => 'New message: ' . $type,
    'message'    => $name . ' wrote about "' . $project . '". Code ' . $code . '.',
    'category'   => 'news',
    'audience'   => 'admin',
    'redirect'   => '/admin.php#messages',
    'source'     => 'message',
    'auto'       => true,
]);

jsonResponse(['success' => true, 'code' => $code]);
        break;
    }

    // ========================================================
    // status.php  (era api/status.php)
    // ========================================================
    case $__ep === 'status': {
if (!checkApiToken(false)) jsonResponse(['success' => false, 'error' => t('Invalid token')], 403);

$hqResult = getProjectsFromHQ();
if (!$hqResult['success']) {
    jsonResponse(['success' => false, 'error' => 'HQ unreachable: ' . $hqResult['error']]);
}

$status = [];
foreach ($hqResult['projects'] as $project) {
    $projectKey = strtolower(preg_replace('/[^a-zA-Z0-9]/', '', $project['name']));
    $status[] = [
        'key'         => $projectKey,
        'name'        => $project['name'],
        'url'         => 'https://luxverse.c22.eu/' . $projectKey . '.php',
        'description' => $project['description'] ?? '',
        'icon'        => $project['icon'] ?? 'rocket',
        'color'       => $project['color'] ?? '#00BFFF',
        'active'      => isProjectActive($projectKey),
        'status'      => getProjectStatus($projectKey)
    ];
}

jsonResponse(['success' => true, 'timestamp' => date('Y-m-d H:i:s'), 'status' => $status]);
        break;
    }

    // ========================================================
    // submit_message.php  (era api/submit_message.php)
    // ========================================================
    case $__ep === 'submit_message': {
$input = $_POST;
if (empty($input)) {
    $jsonInput = json_decode(file_get_contents('php://input'), true);
    if (is_array($jsonInput)) $input = $jsonInput;
}

$name    = trim($input['name'] ?? '');
$email   = trim($input['email'] ?? '');
$message = trim($input['message'] ?? '');
$project = trim($input['project'] ?? '');
$type    = trim($input['type'] ?? '');

if ($name === '') $name = 'Anonimo';
if ($project === '') $project = 'workmeai';
if ($type === '') $type = trim($input['kind'] ?? 'Altro');

if ($message === '') {
    jsonResponse(['success' => false, 'error' => t('Missing parameters')]);
}

$code = strtoupper(substr(md5(uniqid(mt_rand(), true)), 0, 8));

$messagesFile = __DIR__ . '/../data/messages.json';
$messages = [];
if (file_exists($messagesFile)) $messages = json_decode(file_get_contents($messagesFile), true) ?? [];

foreach ($messages as $msg) {
    if (($msg['project'] ?? '') === $project && isset($msg['status']) && strpos($msg['status'], 'In Attesa') !== false) {
        jsonResponse(['success' => false, 'error' => 'You already have a pending message for this project. Wait for it to be resolved.']);
    }
}

$messages[] = [
    'code'    => $code,
    'project' => $project,
    'type'    => $type,
    'kind'    => $input['kind'] ?? ($input['form'] ?? 'contatti'),
    'name'    => $name,
    'email'   => $email,
    'message' => $message,
    'date'    => date('Y-m-d H:i:s'),
    'status'  => 'In Attesa'
];

if (!is_dir(dirname($messagesFile))) mkdir(dirname($messagesFile), 0755, true);
file_put_contents($messagesFile, json_encode($messages, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

// Notifica interna: e' arrivato un contatto dal modulo pubblico.
lux_notification_add([
    'title_it'   => 'Nuovo contatto: ' . $type,
    'title_en'   => 'New contact: ' . $type,
    'message_it' => $name . ' ha inviato un messaggio dal modulo contatti. Codice ' . $code . '.',
    'message_en' => $name . ' sent a message from the contact form. Code ' . $code . '.',
    'title'      => 'New contact: ' . $type,
    'message'    => $name . ' sent a message from the contact form. Code ' . $code . '.',
    'category'   => 'news',
    'audience'   => 'admin',
    'redirect'   => '/admin.php#messages',
    'source'     => 'contact',
    'auto'       => true,
]);

jsonResponse(['success' => true, 'code' => $code]);
        break;
    }

    // ========================================================
    // toggle_project.php  (era api/toggle_project.php)
    // ========================================================
    case $__ep === 'toggle_project': {
if (!checkApiToken()) jsonResponse(['success' => false, 'error' => t('Invalid token')], 403);

$projectKey = $_POST['project'] ?? $_GET['project'] ?? '';
if (empty($projectKey)) jsonResponse(['success' => false, 'error' => 'Missing project']);

$hqResult = getProjectsFromHQ();
if (!$hqResult['success']) {
    jsonResponse(['success' => false, 'error' => 'HQ unreachable: ' . $hqResult['error']]);
}

$project = null;
foreach ($hqResult['projects'] as $p) {
    $key = (string)($p['key'] ?? '');
    $fromName = strtolower(preg_replace('/[^a-zA-Z0-9]/', '', (string)($p['name'] ?? '')));
    if ($key === $projectKey || $fromName === $projectKey) {
        $project = $p;
        break;
    }
}
if (!$project) jsonResponse(['success' => false, 'error' => 'Project not found']);

// Gli strumenti vivono tutti in un unico file (tool.php): non c'e' piu' un
// file per progetto da rinominare. Attivare e disattivare significa scrivere
// lo stato in data/states/, che e' quello che checkProjectStatus() controlla
// prima di mostrare la pagina di uno strumento.
$current  = lux_project_state($projectKey);
$newState = ($current === 'attivo') ? 'disattivato' : 'attivo';

if (!lux_set_project_state($projectKey, $newState)) {
    jsonResponse(['success' => false, 'error' => 'Error during ' . ($newState === 'attivo' ? 'activation' : 'deactivation')]);
}

jsonResponse([
    'success' => true,
    'status'  => $newState,
    'message' => ($newState === 'attivo' ? 'Project activated: ' : 'Project deactivated: ') . $project['name'],
]);
        break;
    }

    // ========================================================
    // track_status.php  (era api/track_status.php)
    // ========================================================
    case $__ep === 'track_status': {
$code = $_GET['code'] ?? $_POST['code'] ?? '';
if (empty($code)) jsonResponse(['success' => false, 'error' => 'Missing code']);

$messagesFile = __DIR__ . '/../data/messages.json';
$messages = [];
if (file_exists($messagesFile)) $messages = json_decode(file_get_contents($messagesFile), true) ?? [];

foreach ($messages as $msg) {
    if ($msg['code'] === $code) {
        jsonResponse(['success' => true, 'message' => $msg]);
    }
}

jsonResponse(['success' => false, 'error' => 'Message not found']);
        break;
    }

    // ========================================================
    // update_status.php  (era api/update_status.php)
    // ========================================================
    case $__ep === 'update_status': {
if (!checkApiToken()) jsonResponse(['success' => false, 'error' => t('Invalid token')], 403);

// Sanifica la chiave progetto (evita path traversal) e verifica che esista
$projectKey = strtolower(preg_replace('/[^a-zA-Z0-9_-]/', '', $_POST['project'] ?? $_GET['project'] ?? ''));
$status     = $_POST['status']  ?? $_GET['status']  ?? '';

if ($projectKey === '' || empty($status)) jsonResponse(['success' => false, 'error' => t('Missing parameters')]);
if (!isProjectActive($projectKey)) jsonResponse(['success' => false, 'error' => 'Unknown project'], 404);

$validStatuses = ['attivo', 'manutenzione', 'pausa', 'costruzione', 'verifica', 'bloccato', 'archiviato'];
if (!in_array($status, $validStatuses)) jsonResponse(['success' => false, 'error' => 'Invalid status']);

lux_set_project_state($projectKey, $status);

jsonResponse(['success' => true, 'project' => $projectKey, 'status' => $status]);
        break;
    }

    // ========================================================
    // triage  (nuovo: pre-analisi AI di una segnalazione)
    // ========================================================
    // Riceve oggetto e messaggio di una richiesta di assistenza e propone
    // categoria, priorita' e primi passi. Non inventa nulla: se la chiave AI
    // non e' configurata (o cURL non c'e') risponde con un errore chiaro.
    case $__ep === 'triage': {
        $input = $_POST;
        if (empty($input)) {
            $jsonInput = json_decode(file_get_contents('php://input'), true);
            if (is_array($jsonInput)) $input = $jsonInput;
        }

        $subject = trim((string)($input['subject'] ?? ''));
        $message = trim((string)($input['message'] ?? ''));

        if ($subject === '' && $message === '') {
            jsonResponse(['success' => false, 'error' => t('Missing parameters')]);
        }
        if (GROQ_API_KEY === '') {
            jsonResponse([
                'success' => false,
                'error'   => t('AI not configured'),
                'hint'    => t('Add the AI key in the server vault to enable the automatic pre-analysis.'),
            ]);
        }

        $categories = 'Assistenza Tecnica, Segnalazione Bug, Richiesta Informazioni, Reclamo, Suggerimento, Problemi Account, Privacy, Altro';
        $res = askGroq([
            ['role' => 'system', 'content' =>
                'Sei il sistema di triage del supporto di L.U.X. Verse. Analizzi una richiesta e rispondi SOLO con un oggetto JSON, senza testo attorno, con i campi: '
                . '"category" (una fra: ' . $categories . '), "priority" (P1, P2, P3 o P4), "summary" (una riga, massimo 140 caratteri), '
                . '"questions" (array di 2 o 3 domande brevi da fare all utente per risolvere prima). '
                . 'Se l utente e vago, usa P4 e chiedi i dettagli mancanti. Non usare emoji.'],
            ['role' => 'user', 'content' => "Oggetto: " . $subject . "\n\nMessaggio: " . $message],
        ], ['temperature' => 0.2, 'max_tokens' => 600]);

        if (empty($res['success'])) {
            jsonResponse(['success' => false, 'error' => (string)($res['error'] ?? 'AI error')]);
        }

        $analysis = extractJsonFromAI((string)$res['content']);
        jsonResponse([
            'success'  => true,
            'raw'      => (string)$res['content'],
            'category' => (string)($analysis['category'] ?? ''),
            'priority' => (string)($analysis['priority'] ?? ''),
            'summary'  => (string)($analysis['summary'] ?? ''),
            'questions'=> array_slice(array_values((array)($analysis['questions'] ?? [])), 0, 3),
        ]);
    }

    // ========================================================
    // catalog_intent.php — ricerca PER ESIGENZA (pubblico)
    // ========================================================
    // Non e' una ricerca per nome: l'utente scrive cosa gli serve con
    // parole sue e il sistema sceglie gli strumenti piu' adatti. Usa la
    // quarta chiave AI (GROQ_API_KEY_4), riservata a chatbot e ricerca.
    case $__ep === 'catalog_intent': {
        $ip = (string)($_SERVER['REMOTE_ADDR'] ?? '');
        if (function_exists('lux_rate_limit') && !lux_rate_limit('intent', $ip, 25, 60)) {
            jsonResponse(['success' => false, 'throttled' => true, 'error' => t('Too many requests. Please wait a moment and try again.')], 429);
        }

        $in = lux_api_input();
        $q  = trim((string)($in['q'] ?? ($_GET['q'] ?? '')));
        $q  = substr($q, 0, 400);
        if ($q === '') {
            jsonResponse(['success' => false, 'error' => t('Write what you need.')], 422);
        }

        $items = [];
        $why   = '';

        if (function_exists('lux_catalog_axis')) {
            $A = lux_catalog_axis('action')['rows'];
            $T = lux_catalog_axis('topic')['rows'];
            $P = lux_catalog_axis('preset')['rows'];

            $listOf = function (array $rows, string $field): string {
                $o = [];
                foreach ($rows as $r) $o[] = (string)$r[$field];
                return implode(', ', $o);
            };

            $system = 'Sei il motore di ricerca per esigenza di L.U.X. Verse. '
                . 'L utente descrive a parole sue un bisogno. Devi scegliere le combinazioni piu adatte tra queste tre liste, usando SOLO i token esatti. '
                . 'Azioni: ' . $listOf($A, 'token') . '. '
                . 'Argomenti: ' . $listOf($T, 'token') . '. '
                . 'Tagli: ' . $listOf($P, 'token') . '. '
                . 'Rispondi SOLO con un oggetto JSON, senza testo attorno: '
                . '{"combos":[["azione","argomento","taglio"]],"why":"una riga che spiega la scelta"}. '
                . 'Massimo 6 combinazioni, dalla piu adatta alla meno adatta. Se il bisogno e chiarissimo usa anche piu tagli dello stesso argomento. Non usare emoji.';

            $res = askGroq([
                ['role' => 'system', 'content' => $system],
                ['role' => 'user', 'content' => 'Esigenza dell utente: ' . $q],
            ], ['key' => 4, 'temperature' => 0.2, 'max_tokens' => 700]);

            if (!empty($res['success'])) {
                $parsed = extractJsonFromAI((string)$res['content']);
                $why    = (string)($parsed['why'] ?? '');
                $seen   = [];
                foreach ((array)($parsed['combos'] ?? []) as $combo) {
                    if (!is_array($combo) || count($combo) < 3) continue;
                    $slug = strtolower(preg_replace('/[^a-z0-9\-]/i', '', (string)$combo[0] . '-' . (string)$combo[1] . '-' . (string)$combo[2]));
                    $idx  = lux_catalog_index_from_slug($slug);
                    if ($idx === null || isset($seen[$slug])) continue;
                    $seen[$slug] = true;
                    $it = lux_catalog_item($idx);
                    if ($it) $items[] = lux_catalog_project($it);
                    if (count($items) >= 8) break;
                }
            }
        }

        // Ripiego (o integrazione): ricerca per parole chiave sul catalogo,
        // cosi' la risposta non resta mai vuota se l'AI non e' disponibile.
        // La frase intera raramente combacia: si prova prima con le parole
        // piu' lunghe (quelle che di solito contano) e poi una per una.
        if (count($items) < 6 && function_exists('lux_catalog_page')) {
            $have = [];
            foreach ($items as $it) $have[(string)$it['key']] = true;

            $clean = strtolower(preg_replace('/[^\p{L}\p{N}\s]/u', ' ', $q));
            $words = array_values(array_filter(array_filter(preg_split('/\s+/', $clean)), function ($w) {
                return strlen($w) >= 4;
            }));
            usort($words, function ($a, $b) { return strlen($b) <=> strlen($a); });
            $words = array_slice($words, 0, 6);

            $tries = [];
            if ($words) {
                $tries[] = implode(' ', array_slice($words, 0, 4));
                $tries[] = implode(' ', array_slice($words, 0, 2));
                foreach ($words as $w) $tries[] = $w;
            }
            $tries[] = $q;

            foreach ($tries as $tq) {
                $tq = trim((string)$tq);
                if ($tq === '') continue;
                $kw = lux_catalog_page(['q' => $tq, 'per' => 8]);
                foreach ((array)($kw['items'] ?? []) as $it) {
                    if (isset($have[(string)$it['key']])) continue;
                    $items[] = $it;
                    $have[(string)$it['key']] = true;
                    if (count($items) >= 8) break 2;
                }
            }
        }

        jsonResponse([
            'success' => true,
            'query'   => $q,
            'why'     => $why,
            'items'   => array_slice($items, 0, 8),
        ]);
    }

    // ========================================================
    // guide.php — L.U.X. GUIDE (pubblico, a opzioni)
    // ========================================================
    // La guida ufficiale: prima la lingua, poi pulsanti. Se una domanda
    // non viene capita, la chat viene trasferita all'admin (status=queued)
    // e da li' diventa visibile nel portale. Usa la quarta chiave AI.
    case $__ep === 'guide': {
        $in     = lux_api_input();
        $action = (string)($in['action'] ?? 'start');
        $ip     = (string)($_SERVER['REMOTE_ADDR'] ?? '');
        $visitor= substr(hash('sha256', 'v|' . $ip), 0, 16);

        if (function_exists('lux_rate_limit') && !lux_rate_limit('guide', $ip, 60, 60)) {
            jsonResponse(['success' => false, 'throttled' => true, 'error' => t('Too many requests. Please wait a moment and try again.')], 429);
        }

        $id = preg_replace('/[^A-Za-z0-9_]/', '', (string)($in['chat'] ?? ''));

        // --- avvio: si chiede la lingua in due domande ---
        if ($action === 'start' || $id === '') {
            [$id, $chat] = lux_chat_new('it', $visitor);
            lux_chat_append($id, 'bot', 'In quale lingua vuoi procedere? · In which language would you like to continue?', ['kind' => 'lang']);
            // Si risponde con lo stato COMPLETO: il browser disegna e basta.
            jsonResponse(lux_chat_state_payload(lux_chat_find($id) ?: $chat));
        }

        if (!function_exists('lux_chat_find') || !lux_chat_find($id)) {
            jsonResponse(['success' => false, 'error' => t('Conversation not found.')], 404);
        }

        $lang = (string)($in['lang'] ?? '');
        $lang = $lang === 'en' ? 'en' : 'it';

        // --- stato completo: e' l'unica cosa che disegna il pannello ---
        // Il client non tiene copia dei messaggi: chiede qui il log intero e
        // lo ridisegna. Cosi' un messaggio non puo' comparire due volte e
        // ricaricando la pagina la conversazione resta quella di prima.
        if ($action === 'state') {
            jsonResponse(lux_chat_state_payload(lux_chat_find($id), $lang));
        }

        // --- scelta della lingua ---
        if ($action === 'lang') {
            $chat = lux_chat_find($id);
            $chat['lang'] = $lang;
            lux_chat_save($chat);
            // La scelta resta scritta nella conversazione: cosi' il dialogo
            // si rilegge per intero anche dopo una ricarica.
            lux_chat_append($id, 'user', ($lang === 'en' ? 'English' : 'Italiano'), ['kind' => 'choice']);
            $welcome = lux_guide_welcome($lang);
            lux_chat_append($id, 'bot', $welcome, ['kind' => 'menu']);
            jsonResponse(lux_chat_state_payload(lux_chat_find($id), $lang));
        }

        // --- una voce del menu ---
        if ($action === 'option') {
            // Dopo il passaggio a una persona il bot non risponde piu':
            // la conversazione la porta avanti lo staff.
            $stNow = (string)((lux_chat_find($id)['status'] ?? 'bot'));
            if (in_array($stNow, ['queued', 'answered', 'closed'], true)) {
                jsonResponse(lux_chat_state_payload(lux_chat_find($id), $lang));
            }
            $optId = preg_replace('/[^a-z_]/', '', strtolower((string)($in['option'] ?? '')));
            $ans = lux_guide_answer($optId, $lang);
            if (empty($ans['ok'])) {
                jsonResponse(['success' => false, 'error' => t('Unknown option.')], 422);
            }
            lux_chat_append($id, 'user', (string)($ans['title']), ['kind' => 'option', 'option' => $optId]);
            lux_chat_append($id, 'bot', (string)$ans['text'], ['kind' => 'answer']);
            if (!empty($ans['escalate'])) {
                $chat = lux_chat_find($id);
                $chat['status'] = 'queued';
                lux_chat_save($chat);
            }
            jsonResponse(lux_chat_state_payload(lux_chat_find($id), $lang));
        }

        // --- domanda scritta con parole proprie ---
        if ($action === 'ask') {
            $text = trim((string)($in['text'] ?? ''));
            $text = substr($text, 0, 600);
            if ($text === '') jsonResponse(['success' => false, 'error' => t('Write your question.')], 422);

            // Se la chat e' gia' in mano a una persona, il messaggio si
            // registra per lo staff ma il bot tace: e' il blocco vero,
            // non solo il pulsante disattivato nel browser.
            $chatNow = lux_chat_find($id);
            $stNow   = (string)($chatNow['status'] ?? 'bot');
            if (in_array($stNow, ['queued', 'answered', 'closed'], true)) {
                lux_chat_append($id, 'user', $text, ['kind' => 'text']);
                jsonResponse(lux_chat_state_payload(lux_chat_find($id), $lang));
            }

            // RICHIESTA RIPETUTA. Se l'ultimo messaggio della persona e'
            // identico a questo e il bot ha gia' risposto, si restituisce lo
            // stato senza riscrivere niente: un doppio clic, un "Invia"
            // premuto due volte o una ritentata non creano messaggi doppi
            // (ne' del testo, ne' della risposta).
            $logNow = array_values((array)($chatNow['log'] ?? []));
            $lastUser = -1;
            foreach ($logNow as $li => $lr) { if ((string)($lr['from'] ?? '') === 'user') $lastUser = $li; }
            if ($lastUser >= 0 && $lastUser < count($logNow) - 1
                && (string)($logNow[$lastUser]['text'] ?? '') === $text) {
                jsonResponse(lux_chat_state_payload($chatNow, $lang));
            }

            lux_chat_append($id, 'user', $text, ['kind' => 'text']);
            $match = lux_guide_match($text);

            if ($match === null) {
                // NON CAPITA: la chat NON si blocca. Prima una domanda non
                // riconosciuta congelava tutto (la casella spariva e non si
                // poteva piu' scrivere). Ora il bot lo dice, rimette davanti
                // il menu e la persona resta libera di scrivere o di
                // chiedere "Parlare con una persona".
                $msg = $lang === 'en'
                    ? 'I did not quite understand that. Pick one of the options below, or press "Talk to a person" and I will pass the chat to the staff. You can also write to ' . lux_contact_email() . '.'
                    : 'Non ho capito bene. Scegli una delle opzioni qui sotto, oppure premi "Parla con una persona" e passo la chat allo staff. Puoi anche scrivere a ' . lux_contact_email() . '.';
                lux_chat_append($id, 'bot', $msg, ['kind' => 'answer']);
                jsonResponse(lux_chat_state_payload(lux_chat_find($id), $lang));
            }

            $ans = lux_guide_answer($match, $lang);
            lux_chat_append($id, 'bot', (string)$ans['text'], ['kind' => 'answer']);
            // Se la voce riconosciuta e' "parlare con una persona" il
            // trasferimento deve avvenire davvero: prima il bot lo
            // prometteva e la chat restava al bot, quindi lo staff non
            // vedeva niente.
            if (!empty($ans['escalate'])) {
                $chat = lux_chat_find($id);
                $chat['status'] = 'queued';
                lux_chat_save($chat);
            }
            jsonResponse(lux_chat_state_payload(lux_chat_find($id), $lang));
        }

        // --- trasferimento diretto all'admin ---
        if ($action === 'escalate') {
            $chat = lux_chat_find($id);
            $chat['status'] = 'queued';
            lux_chat_save($chat);
            $msg = $lang === 'en'
                ? 'Done: a member of the staff will reply to you here. Thank you for your patience.'
                : 'Fatto: una persona dello staff ti risponderà qui. Grazie della pazienza.';
            lux_chat_append($id, 'bot', $msg, ['kind' => 'escalate']);
            jsonResponse(lux_chat_state_payload(lux_chat_find($id), $lang));
        }

        // --- chiusura della chat (anche dal cliente) ---
        if ($action === 'close') {
            $chat = lux_chat_find($id);
            $chat['status'] = 'closed';
            lux_chat_save($chat);
            jsonResponse(lux_chat_state_payload(lux_chat_find($id), $lang));
        }

        // --- novita' dalla chat (risposta dell'admin) ---
        if ($action === 'poll') {
            $chat = lux_chat_find($id);
            $log  = (array)($chat['log'] ?? []);
            $last = max(0, (int)($in['last'] ?? 0));
            jsonResponse([
                'success' => true,
                'chat'    => $id,
                'status'  => (string)($chat['status'] ?? 'bot'),
                'total'   => count($log),
                'new'     => array_slice($log, $last),
            ]);
        }

        jsonResponse(['success' => false, 'error' => 'Unknown action'], 422);
    }

    // ========================================================
    // feedback.php — giudizio su sito / guida / admin (pubblico)
    // ========================================================
    case $__ep === 'feedback': {
        $ip = (string)($_SERVER['REMOTE_ADDR'] ?? '');
        if (function_exists('lux_rate_limit') && !lux_rate_limit('feedback', $ip, 8, 300)) {
            jsonResponse(['success' => false, 'throttled' => true, 'error' => t('Too many requests. Please wait a moment and try again.')], 429);
        }
        $in = lux_api_input();
        $rating  = (int)($in['rating'] ?? 0);
        $message = trim((string)($in['message'] ?? ''));
        $channel = (string)($in['channel'] ?? 'site');
        if (!in_array($channel, ['site', 'guide', 'admin', 'chat'], true)) $channel = 'site';
        if ($rating < 1 && $message === '') {
            jsonResponse(['success' => false, 'error' => t('Add a rating or a message.')], 422);
        }
        lux_feedback_add([
            'rating'  => $rating,
            'message' => substr($message, 0, 1500),
            'channel' => $channel,
            'page'    => substr((string)($in['page'] ?? ''), 0, 120),
            'lang'    => currentLang(),
        ]);
        jsonResponse(['success' => true, 'thanks' => t('Thank you! Your feedback has been recorded.')]);
    }

    // ========================================================
    // upvote.php — voto pubblico su un progetto
    // ========================================================
    case $__ep === 'upvote': {
        $ip = (string)($_SERVER['REMOTE_ADDR'] ?? '');
        if (function_exists('lux_rate_limit') && !lux_rate_limit('upvote', $ip, 30, 300)) {
            jsonResponse(['success' => false, 'throttled' => true, 'error' => t('Too many requests. Please wait a moment and try again.')], 429);
        }
        $in   = lux_api_input();
        $slug = strtolower(trim((string)($in['slug'] ?? ($_GET['slug'] ?? ''))));
        if ($slug === '') jsonResponse(['success' => false, 'error' => t('Missing parameters')], 422);
        $count = lux_upvote_add($slug, $ip);
        jsonResponse(['success' => true, 'slug' => preg_replace('/[^a-z0-9_\-]/i', '', $slug), 'count' => max(0, $count)]);
    }

    // ========================================================
    // usage.php — registra l'apertura di un progetto (pubblico)
    // ========================================================
    case $__ep === 'usage': {
        $in   = lux_api_input();
        $slug = strtolower(trim((string)($in['slug'] ?? ($_GET['slug'] ?? ''))));
        if ($slug === '' || strlen($slug) > 64) jsonResponse(['success' => false], 204);
        $n = lux_usage_bump($slug);
        jsonResponse(['success' => true, 'opens' => $n]);
    }

    // ========================================================
    // agent.php — il motore di orchestrazione (39.1.1, WS1)
    // ========================================================
    // op=plan      → dal bisogno scritto a parole sue al piano di esecuzione:
    //               quali strumenti servono e con quali valori (o, se l'IA non
    //               e' disponibile, le voci del catalogo da esplorare).
    // op=aggregate → i risultati di piu' passi uniti in una risposta sola.
    // op=registry  → l'elenco pubblico degli strumenti eseguibili.
    case $__ep === 'agent': {
        $in = lux_api_input();
        $op = (string)($in['op'] ?? ($_GET['op'] ?? 'plan'));

        if ($op === 'registry') {
            $tools = [];
            foreach (lux_agent_tools() as $key => $t) {
                $tools[] = [
                    'key'      => (string)$key,
                    'name'     => (string)($t['name'] ?? $key),
                    'category' => (string)($t['category'] ?? 'utility'),
                    'engine'   => (string)($t['engine'] ?? 'ai'),
                    'output'   => (string)($t['output'] ?? 'rich'),
                    'endpoint' => (string)($t['endpoint'] ?? ('/' . $key . '.php')),
                ];
            }
            $counts = lux_agent_engine_counts();
            header('Cache-Control: public, max-age=300');
            jsonResponse([
                'success' => true,
                'version' => defined('LUX_SITE_VERSION') ? LUX_SITE_VERSION : '',
                'total'   => count($tools),
                'instant' => (int)($counts['local'] ?? 0),
                'ai'      => (int)($counts['ai'] ?? 0),
                'tools'   => $tools,
            ]);
        }

        $ip = (string)($_SERVER['REMOTE_ADDR'] ?? '');

        if ($op === 'aggregate') {
            if (!lux_rate_limit('agent_agg', $ip, 20, 300)) {
                jsonResponse(['success' => false, 'throttled' => true, 'error' => t('Too many requests. Please wait a moment and try again.')], 429);
            }
            $need  = trim((string)($in['need'] ?? ''));
            $steps = is_array($in['steps'] ?? null) ? array_slice((array)$in['steps'], 0, 4) : [];
            if ($need === '' || !$steps) {
                jsonResponse(['success' => false, 'error' => t('Missing parameters')], 422);
            }
            $out = lux_agent_aggregate($need, $steps, currentLang());
            if (empty($out['success'])) {
                // L'aggregazione e' un di piu': se non riesce, il client mostra
                // comunque i singoli passi e l'utente non perde niente.
                jsonResponse(['success' => false, 'error' => (string)($out['error'] ?? ''), 'degraded' => true]);
            }
            jsonResponse(['success' => true, 'content' => (string)$out['content']]);
        }

        // op=plan (predefinito)
        if (!lux_rate_limit('agent', $ip, 30, 60)) {
            jsonResponse(['success' => false, 'throttled' => true, 'error' => t('Too many requests. Please wait a moment and try again.')], 429);
        }
        $need = trim((string)($in['q'] ?? ($_GET['q'] ?? '')));
        if ($need === '') {
            jsonResponse(['success' => false, 'error' => t('Write what you need.')], 422);
        }
        $memory = is_array($in['memory'] ?? null) ? (array)$in['memory'] : [];

        $plan = lux_agent_plan($need, currentLang(), $memory);
        if (empty($plan['success'])) {
            jsonResponse(['success' => false, 'error' => t('Write what you need.')], 422);
        }
        header('Cache-Control: private, no-store, max-age=0');
        jsonResponse($plan);
    }

    // ========================================================
    // space.php — lo spazio personale con codice (39.1.1, WS2)
    // ========================================================
    // op=new     → un codice personale nuovo
    // op=get     → quello che c'e' sotto un codice
    // op=save    → unisce e salva (il dispositivo manda il suo, il server
    //              unisce senza perdere niente e risponde con l'unione)
    // op=share   → un risultato diventa un indirizzo pubblico e stabile
    case $__ep === 'space': {
        $in = lux_api_input();
        $op = (string)($in['op'] ?? ($_GET['op'] ?? 'get'));
        $ip = (string)($_SERVER['REMOTE_ADDR'] ?? '');

        if ($op === 'share') {
            if (!lux_rate_limit('space_share', $ip, 20, 600)) {
                jsonResponse(['success' => false, 'throttled' => true, 'error' => t('Too many requests. Please wait a moment and try again.')], 429);
            }
            $id = lux_space_share(
                (string)($in['tool'] ?? ''),
                (string)($in['title'] ?? ''),
                (string)($in['content'] ?? ''),
                currentLang()
            );
            if ($id === '') {
                jsonResponse(['success' => false, 'error' => t('Missing parameters')], 422);
            }
            jsonResponse(['success' => true, 'id' => $id, 'url' => LUX_SITE_URL . '/condividi.php?id=' . $id]);
        }

        // Il resto e' legato al codice: si limita per non lasciar provare
        // codici a raffica (un codice sono 12 caratteri, ma il freno resta).
        if (!lux_rate_limit('space', $ip, 60, 300)) {
            jsonResponse(['success' => false, 'throttled' => true, 'error' => t('Too many requests. Please wait a moment and try again.')], 429);
        }

        if ($op === 'new') {
            $code = lux_space_new_code();
            lux_space_save($code, ['lang' => currentLang()]);
            jsonResponse(['success' => true, 'code' => $code, 'pretty' => lux_space_code_pretty($code)]);
        }

        $code = (string)($in['code'] ?? ($_GET['code'] ?? ''));
        if (!lux_space_code_valid($code)) {
            jsonResponse(['success' => false, 'error' => t('That code is not valid. Check it and try again.'), 'bad_code' => true], 422);
        }

        if ($op === 'save') {
            $client = is_array($in['payload'] ?? null) ? (array)$in['payload'] : [];
            $server = lux_space_payload($code);
            $merged = lux_space_merge($server, $client);
            if (!lux_space_save($code, $merged)) {
                jsonResponse(['success' => false, 'error' => t('Something went wrong. Try again.')], 500);
            }
            header('Cache-Control: private, no-store, max-age=0');
            jsonResponse([
                'success' => true,
                'code'    => lux_space_code_normalize($code),
                'pretty'  => lux_space_code_pretty($code),
                'payload' => $merged,
                'known'   => (bool)lux_space_get($code),
            ]);
        }

        header('Cache-Control: private, no-store, max-age=0');
        jsonResponse([
            'success' => true,
            'code'    => lux_space_code_normalize($code),
            'pretty'  => lux_space_code_pretty($code),
            'payload' => lux_space_payload($code),
            'known'   => (bool)lux_space_get($code),
        ]);
    }

    // ========================================================
    // palette.php — il comando universale Ctrl+K (39.1.1, WS3)
    // ========================================================
    // Una sola ricerca che guarda in tre posti: gli strumenti del catalogo,
    // le pagine del sito e gli avvisi della bacheca. Cosi' il comando apre
    // qualunque cosa in due tasti, senza scaricare il catalogo nel browser.
    case $__ep === 'palette': {
        $ip = (string)($_SERVER['REMOTE_ADDR'] ?? '');
        if (function_exists('lux_rate_limit') && !lux_rate_limit('palette', $ip, 240, 60)) {
            jsonResponse(['success' => false, 'throttled' => true, 'error' => t('Too many requests. Please wait a moment and try again.')], 429);
        }
        $q    = trim((string)($_GET['q'] ?? ''));
        $q    = substr($q, 0, 80);
        $lang = currentLang();
        $it   = $lang !== 'en';
        $low  = function ($s) { return strtolower((string)$s); };
        $has  = function (string $hay, string $needle): bool {
            return $needle === '' || strpos(strtolower($hay), strtolower($needle)) !== false;
        };

        // 1. gli strumenti eseguibili (hanno gia' nome e descrizione pronti)
        $tools = [];
        foreach (lux_agent_tools() as $key => $t) {
            $name = (string)($t['name'] ?? $key);
            $desc = (string)($it ? ($t['desc_it'] ?? '') : ($t['desc_en'] ?? ($t['desc_it'] ?? '')));
            if (!$has($name . ' ' . $desc . ' ' . $key, $q)) continue;
            $tools[] = [
                'kind'  => 'tool',
                'label' => $name,
                'hint'  => $desc,
                'url'   => (string)($t['endpoint'] ?? ('/' . $key . '.php')),
                'icon'  => (string)($t['icon'] ?? 'sparkles'),
                'run'   => ((string)($t['engine'] ?? 'ai') === 'local'),
            ];
            if (count($tools) >= 6) break;
        }

        // 2. le pagine del sito (elenco fisso: sono poche e non cambiano)
        $pages = [
            ['url' => '/index.php',       'it' => 'Home',              'en' => 'Home',              'icon' => 'grid',      'hint_it' => 'Il catalogo e la ricerca', 'hint_en' => 'The catalogue and the search'],
            ['url' => '/spazio.php',      'it' => 'Il mio spazio',     'en' => 'My space',          'icon' => 'user',      'hint_it' => 'Cronologia, preferiti, risultati salvati', 'hint_en' => 'History, favourites, saved results'],
            ['url' => '/bacheca.php',     'it' => 'Bacheca',           'en' => 'Board',             'icon' => 'bell',      'hint_it' => 'Notizie e avvisi', 'hint_en' => 'News and notices'],
            ['url' => '/roadmap.php',     'it' => 'Percorso del rilascio', 'en' => 'Release roadmap', 'icon' => 'time',   'hint_it' => 'Cosa arriva, in che ordine', 'hint_en' => 'What is coming, in which order'],
            ['url' => '/proponi.php',     'it' => 'Proponi uno strumento', 'en' => 'Suggest a tool', 'icon' => 'sparkles','hint_it' => 'Idee e voti della community', 'hint_en' => 'Community ideas and votes'],
            ['url' => '/sviluppatori.php','it' => 'API per sviluppatori', 'en' => 'Developer API',  'icon' => 'code',      'hint_it' => 'Chiavi, documentazione, esempi', 'hint_en' => 'Keys, docs, examples'],
            ['url' => '/webapp.php',      'it' => 'Tutti gli strumenti', 'en' => 'All the tools',   'icon' => 'layers',    'hint_it' => 'Esplora il catalogo', 'hint_en' => 'Browse the catalogue'],
            ['url' => '/linea-del-tempo.php', 'it' => 'Linea del tempo', 'en' => 'Timeline',        'icon' => 'time',      'hint_it' => 'La storia del progetto', 'hint_en' => 'The history of the project'],
            ['url' => '/chi-siamo.php',   'it' => 'Chi siamo',         'en' => 'About us',          'icon' => 'user',      'hint_it' => 'Il progetto e le persone', 'hint_en' => 'The project and the people'],
            ['url' => '/faq.php',         'it' => 'Domande frequenti', 'en' => 'FAQ',               'icon' => 'guide',     'hint_it' => 'Le risposte piu chieste', 'hint_en' => 'The most asked answers'],
            ['url' => '/assistenza.php',  'it' => 'Assistenza',        'en' => 'Support',           'icon' => 'guide',     'hint_it' => 'Problemi e richieste', 'hint_en' => 'Problems and requests'],
            ['url' => '/status.php',      'it' => 'Stato del sistema', 'en' => 'System status',     'icon' => 'pulse',     'hint_it' => 'Tutto quello che e online', 'hint_en' => 'Everything that is online'],
            ['url' => '/privacy.php',     'it' => 'Privacy',           'en' => 'Privacy',           'icon' => 'shield',    'hint_it' => 'Dati, cookie e diritti', 'hint_en' => 'Data, cookies and rights'],
            ['url' => '/terms.php',       'it' => 'Termini',           'en' => 'Terms',             'icon' => 'shield',    'hint_it' => 'Le regole d uso', 'hint_en' => 'The rules of use'],
        ];
        $pageHits = [];
        foreach ($pages as $p) {
            $label = $it ? $p['it'] : $p['en'];
            $hint  = $it ? $p['hint_it'] : $p['hint_en'];
            if (!$has($label . ' ' . $hint . ' ' . $p['url'], $q)) continue;
            $pageHits[] = ['kind' => 'page', 'label' => $label, 'hint' => $hint, 'url' => $p['url'], 'icon' => $p['icon']];
            if (count($pageHits) >= 5) break;
        }

        // 3. gli avvisi della bacheca (titoli e testo, quelli ancora in corso)
        $noticeHits = [];
        if (function_exists('getNoticesFromLocal')) {
            foreach (getNoticesFromLocal(0) as $n) {
                $title = $it ? (string)($n['title_it'] ?? ($n['title'] ?? '')) : (string)($n['title_en'] ?? ($n['title'] ?? ''));
                $body  = $it ? (string)($n['message_it'] ?? '') : (string)($n['message_en'] ?? '');
                if (!$has($title . ' ' . $body, $q)) continue;
                $noticeHits[] = [
                    'kind'  => 'notice',
                    'label' => $title,
                    'hint'  => substr(trim(preg_replace('/\s+/', ' ', $body)), 0, 110),
                    'url'   => '/bacheca.php',
                    'icon'  => 'bell',
                ];
                if (count($noticeHits) >= 4) break;
            }
        }

        // 4. se non basta, il catalogo intero (100.000): ultime voci
        $catalog = [];
        if (mb_strlen($q) >= 2 && function_exists('lux_catalog_page')) {
            $res = lux_catalog_page(['page' => 1, 'per' => 6, 'q' => $q]);
            foreach ((array)($res['items'] ?? []) as $item) {
                $catalog[] = [
                    'kind'  => 'catalog',
                    'label' => (string)($item['name'] ?? ''),
                    'hint'  => (string)($item['description'] ?? ''),
                    'url'   => (string)($item['url'] ?? ''),
                    'icon'  => (string)($item['icon'] ?? 'sparkles'),
                ];
                if (count($catalog) >= 6) break;
            }
        }

        header('Cache-Control: private, max-age=30');
        jsonResponse([
            'success' => true,
            'q'       => $q,
            'groups'  => [
                ['label' => t('Tools'),         'items' => $tools],
                ['label' => t('Pages'),         'items' => $pageHits],
                ['label' => t('Bacheca'),       'items' => $noticeHits],
                ['label' => t('The catalogue'), 'items' => $catalog],
            ],
            'total'   => count($tools) + count($pageHits) + count($noticeHits) + count($catalog),
        ]);
    }

    // ========================================================
    // suggest.php — proposte della community (39.1.1, WS5)
    // ========================================================
    // op=list  → classifica (prima i voti, poi le piu' recenti)
    // op=add   → nuova proposta (anonima: soprannome + impronta dell'IP)
    // op=vote  → un voto per proposta, per impronta (mai l'IP in chiaro)
    // Il controllo automatico scarta lo spam: messaggi troppo corti, troppi
    // link, parole da pubblicita', doppioni dello stesso strumento.
    case $__ep === 'suggest': {
        $in = lux_api_input();
        $op = (string)($in['op'] ?? ($_GET['op'] ?? 'list'));
        $ip = (string)($_SERVER['REMOTE_ADDR'] ?? '');
        $fp = substr(hash('sha256', 'suggest|' . $ip), 0, 20);

        $all = function_exists('lux_data_read') ? lux_data_read('suggestions') : [];

        if ($op === 'list') {
            $vis = array_values(array_filter($all, function ($r) {
                return (string)($r['status'] ?? 'nuovo') !== 'spam';
            }));
            usort($vis, function ($a, $b) {
                $va = (int)($a['votes'] ?? 0); $vb = (int)($b['votes'] ?? 0);
                if ($va !== $vb) return $vb <=> $va;
                return strcmp((string)($b['date'] ?? ''), (string)($a['date'] ?? ''));
            });
            $mine = [];
            foreach ($all as $r) {
                if (in_array($fp, (array)($r['fps'] ?? []), true)) $mine[] = (string)($r['id'] ?? '');
            }
            $out = [];
            foreach (array_slice($vis, 0, 60) as $r) {
                $out[] = [
                    'id'     => (string)($r['id'] ?? ''),
                    'name'   => (string)($r['name'] ?? ''),
                    'why'    => (string)($r['why'] ?? ''),
                    'nick'   => (string)($r['nick'] ?? ''),
                    'votes'  => (int)($r['votes'] ?? 0),
                    'status' => (string)($r['status'] ?? 'nuovo'),
                    'date'   => (string)($r['date'] ?? ''),
                    'voted'  => in_array($fp, (array)($r['fps'] ?? []), true),
                ];
            }
            header('Cache-Control: private, max-age=20');
            jsonResponse(['success' => true, 'items' => $out, 'total' => count($vis), 'mine' => array_values(array_filter($mine))]);
        }

        if ($op === 'add') {
            if (!function_exists('lux_rate_limit') || !lux_rate_limit('suggest_new', $ip, 4, 1800)) {
                jsonResponse(['success' => false, 'throttled' => true, 'error' => t('Too many requests. Please wait a moment and try again.')], 429);
            }
            $name = trim(preg_replace('/\s+/', ' ', (string)($in['name'] ?? '')));
            $why  = trim(preg_replace('/\s+/', ' ', (string)($in['why'] ?? '')));
            $nick = trim(preg_replace('/[^\p{L}\p{N} ._-]/u', '', (string)($in['nick'] ?? '')));
            $name = mb_substr($name, 0, 80);
            $why  = mb_substr($why, 0, 600);
            $nick = mb_substr($nick, 0, 24);

            // --- controllo automatico ---
            $check = function_exists('lux_suggest_vet') ? lux_suggest_vet($name, $why, $all) : ['ok' => true, 'why' => ''];
            if (empty($check['ok'])) {
                jsonResponse(['success' => false, 'rejected' => true, 'error' => (string)($check['why'] ?? '')], 422);
            }

            $row = [
                'id'     => 'S' . substr(md5(uniqid('', true)), 0, 10),
                'name'   => $name,
                'why'    => $why,
                'nick'   => $nick !== '' ? $nick : t('Anonymous'),
                'votes'  => 1,
                'fps'    => [$fp],
                'status' => 'nuovo',
                'lang'   => currentLang(),
                'date'   => date('Y-m-d H:i:s'),
            ];
            $all[] = $row;
            if (count($all) > 2000) $all = array_slice($all, -2000);
            if (!lux_data_write('suggestions', $all)) {
                jsonResponse(['success' => false, 'error' => t('Something went wrong. Try again.')], 500);
            }
            jsonResponse(['success' => true, 'id' => $row['id'], 'votes' => 1]);
        }

        if ($op === 'vote') {
            if (!function_exists('lux_rate_limit') || !lux_rate_limit('suggest_vote', $ip, 60, 600)) {
                jsonResponse(['success' => false, 'throttled' => true, 'error' => t('Too many requests. Please wait a moment and try again.')], 429);
            }
            $id = preg_replace('/[^A-Za-z0-9]/', '', (string)($in['id'] ?? ''));
            $found = -1;
            foreach ($all as $i => $r) {
                if ((string)($r['id'] ?? '') === $id) { $found = $i; break; }
            }
            if ($found < 0) jsonResponse(['success' => false, 'error' => t('Missing parameters')], 422);
            $fps = (array)($all[$found]['fps'] ?? []);
            if (in_array($fp, $fps, true)) {
                jsonResponse(['success' => true, 'votes' => (int)$all[$found]['votes'], 'already' => true]);
            }
            $fps[] = $fp;
            $all[$found]['fps'] = array_slice($fps, -4000);
            $all[$found]['votes'] = (int)($all[$found]['votes'] ?? 0) + 1;
            lux_data_write('suggestions', $all);
            jsonResponse(['success' => true, 'votes' => (int)$all[$found]['votes']]);
        }

        jsonResponse(['success' => false, 'error' => 'Unknown action'], 422);
    }

    // ========================================================
    // apikey.php / run.php — API pubblica per sviluppatori (WS6)
    // ========================================================
    // op=key  → una chiave anonima (nessuna email, nessun account): serve
    //           solo a contare le richieste e a fermare gli abusi.
    // op=run  → esegue uno strumento del registro con quella chiave.
    // op=docs → il registro compatto, per chi costruisce sopra il sito.
    case $__ep === 'apikey' || $__ep === 'run': {
        $in = lux_api_input();
        $op = (string)($in['op'] ?? ($_GET['op'] ?? ($__ep === 'run' ? 'run' : 'key')));
        if ($__ep === 'run') $op = 'run';
        $ip = (string)($_SERVER['REMOTE_ADDR'] ?? '');

        if ($op === 'docs') {
            $list = [];
            foreach (lux_agent_tools() as $key => $t) {
                $fields = [];
                foreach ((array)($t['inputs'] ?? []) as $f) {
                    $fields[] = [
                        'name'     => (string)($f['name'] ?? ''),
                        'type'     => (string)($f['type'] ?? 'text'),
                        'required' => !empty($f['required']),
                        'options'  => array_values((array)($f['options'] ?? [])),
                    ];
                }
                $list[] = [
                    'tool'    => (string)$key,
                    'name'    => (string)($t['name'] ?? $key),
                    'engine'  => (string)($t['engine'] ?? 'ai'),
                    'output'  => (string)($t['output'] ?? 'rich'),
                    'inputs'  => $fields,
                ];
            }
            header('Cache-Control: public, max-age=600');
            jsonResponse(['success' => true, 'version' => (string)LUX_SITE_VERSION, 'tools' => $list, 'total' => count($list)]);
        }

        if ($op === 'key') {
            if (!function_exists('lux_rate_limit') || !lux_rate_limit('apikey', $ip, 6, 3600)) {
                jsonResponse(['success' => false, 'throttled' => true, 'error' => t('Too many requests. Please wait a moment and try again.')], 429);
            }
            $keys = lux_map_read('apikeys');
            $key  = 'lux_' . bin2hex(random_bytes(12));
            $keys[$key] = [
                'created'  => date('Y-m-d H:i:s'),
                'used'     => 0,
                'last'     => '',
                'fp'       => substr(hash('sha256', 'apikey|' . $ip), 0, 16),
            ];
            if (count($keys) > 3000) {
                uasort($keys, function ($a, $b) { return strcmp((string)($b['created'] ?? ''), (string)($a['created'] ?? '')); });
                $keys = array_slice($keys, 0, 3000, true);
            }
            lux_map_write('apikeys', $keys);
            jsonResponse([
                'success' => true,
                'key'     => $key,
                'limit'   => 60,
                'window'  => 3600,
                'note'    => t('Keep this key: it identifies your requests, not you. You can always ask for a new one.'),
            ]);
        }

        // op=run
        $key = trim((string)($in['key'] ?? ($_GET['key'] ?? '')));
        $tool = strtolower(trim((string)($in['tool'] ?? ($_GET['tool'] ?? ''))));
        $tool = preg_replace('/[^a-z0-9_\-]/', '', $tool);
        $spec = $tool !== '' ? lux_agent_tool($tool) : null;
        if (!$spec) {
            jsonResponse(['success' => false, 'error' => t('Missing parameters'), 'hint' => 'tool='], 422);
        }
        if ($key === '' || !preg_match('/^lux_[a-f0-9]{24}$/', $key)) {
            jsonResponse(['success' => false, 'error' => t('A valid API key is required.'), 'hint' => 'Ask for one at /sviluppatori.php'], 401);
        }
        $keys = lux_map_read('apikeys');
        if (!isset($keys[$key])) {
            jsonResponse(['success' => false, 'error' => t('That key does not exist. Ask for a new one.'), 'hint' => '/sviluppatori.php'], 401);
        }
        if (!function_exists('lux_rate_limit') || !lux_rate_limit('apirun_k', $key, 60, 3600) || !lux_rate_limit('apirun_ip', $ip, 200, 3600)) {
            jsonResponse(['success' => false, 'throttled' => true, 'error' => t('Too many requests. Please wait a moment and try again.'), 'limit' => '60/hour'], 429);
        }

        // L'esecuzione la fa il motore di sempre: si chiama la pagina dello
        // strumento in POST, come farebbe il browser. Un solo percorso di
        // esecuzione, una sola validazione: quello che passa da qui e' lo
        // stesso risultato che vedrebbe una persona dal sito.
        $fields = is_array($in['inputs'] ?? null) ? (array)$in['inputs'] : (array)($in['fields'] ?? []);
        $post = ['lux_tool' => $tool];
        foreach ((array)($spec['inputs'] ?? []) as $f) {
            $n = (string)($f['name'] ?? '');
            if ($n === '') continue;
            $v = $fields[$n] ?? ($f['default'] ?? '');
            if (is_array($v)) $v = implode(' ', array_map('strval', $v));
            $post[$n] = (string)$v;
        }
        // L'indirizzo da chiamare: il proprio. In produzione e' l'indirizzo
        // pubblico del sito; in prova locale e' 127.0.0.1, cosi' il giro non
        // esce su internet. Si accettano SOLO il nostro host e il loopback:
        // un Host inventato non puo' far chiamare un altro server (niente
        // richieste a sorpresa verso l'esterno).
        $ours   = (string)parse_url(LUX_SITE_URL, PHP_URL_HOST);
        $host   = strtolower((string)($_SERVER['HTTP_HOST'] ?? ''));
        $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
        $self   = LUX_SITE_URL;
        $hostOnly = preg_replace('/:\d+$/', '', $host);
        $sameHost = ($ours !== '' && ($hostOnly === $ours || substr($hostOnly, -strlen('.' . $ours)) === '.' . $ours));
        $loopback = (strpos($hostOnly, '127.0.0.1') === 0 || $hostOnly === 'localhost' || substr($hostOnly, -10) === '.localhost');
        if ($host !== '' && ($sameHost || $loopback)) {
            $self = $scheme . '://' . $host;
        }
        // In prova locale la richiesta a se' stessi non puo' funzionare: il
        // server di sviluppo di PHP serve una richiesta per volta, quindi la
        // chiamata resterebbe in attesa di se stessa. Con la variabile
        // d'ambiente LUX_SELF_URL si punta a un secondo server di prova. Sul
        // server vero questa variabile non esiste: si usa il proprio host.
        $envSelf = getenv('LUX_SELF_URL');
        if (is_string($envSelf) && $envSelf !== '' && preg_match('#^https?://[a-z0-9\.\-]+(:\d+)?$#i', $envSelf)) {
            $self = rtrim($envSelf, '/');
        }
        $url = $self . (string)($spec['endpoint'] ?? ('/' . $tool . '.php'));
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL            => $url,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => http_build_query($post),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 75,
            CURLOPT_FOLLOWLOCATION => false,
            CURLOPT_USERAGENT      => 'LUX-Verse-API/39.1.1',
        ]);
        $raw  = curl_exec($ch);
        $code = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $cerr = curl_error($ch);
        curl_close($ch);

        $keys[$key]['used'] = (int)($keys[$key]['used'] ?? 0) + 1;
        $keys[$key]['last'] = date('Y-m-d H:i:s');
        lux_map_write('apikeys', $keys);

        if ($raw === false || $code === 0) {
            jsonResponse(['success' => false, 'error' => t('The service is temporarily unavailable.'), 'detail' => substr((string)$cerr, 0, 120)], 503);
        }
        $data = json_decode((string)$raw, true);
        if (!is_array($data)) {
            jsonResponse(['success' => false, 'error' => t('The tool did not answer with a result.'), 'http' => $code], 502);
        }
        header('Cache-Control: private, no-store, max-age=0');
        $data['tool']  = $tool;
        $data['key_used'] = substr($key, 0, 8) . '…';
        jsonResponse($data, $data['success'] ? 200 : 422);
    }

    default:
        jsonResponse([
            'success' => false,
            'error'   => 'Unknown endpoint',
            'endpoint'=> $__ep,
            'hint'    => 'Use /api/api.php?__ep=<nome> oppure la URL classica /api/<nome>.php',
        ], 404);
}
