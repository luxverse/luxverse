<?php
// ============================================================
// L.U.X. VERSE — MOTORE DI ORCHESTRAZIONE (WS1, versione 39.1.1)
// ------------------------------------------------------------
// Fino a ieri il sito trovava lo strumento giusto e poi ti lasciava la
// pagina da aprire. Qui comincia la parte nuova: dalla ricerca che ti trova
// il tool, all'agente che ti esegue il risultato.
//
// Come e' fatto (tre pezzi, ognuno con un compito solo):
//
//   PIANIFICATORE  lux_agent_plan()
//     legge il bisogno scritto a parole tue e sceglie, dal registro dei
//     tool eseguibili, quali servono e con quali valori. Un bisogno con un
//     solo passo si risolve con un passo; un bisogno piu' lungo diventa una
//     catena (massimo tre passi), ognuno con il suo ingresso.
//
//   ESECUTORE      gli indirizzi dei tool
//     ogni passo porta con se' l'indirizzo da chiamare (l'endpoint del
//     registro). L'esecuzione la fa il motore che c'e' gia': la pagina dello
//     strumento, che risponde in JSON alle richieste POST. Nessun doppione,
//     nessuna seconda logica da tenere allineata.
//
//   AGGREGATORE    lux_agent_aggregate()
//     quando i passi sono piu' di uno, i risultati si uniscono in una
//     risposta sola, cosi' l'utente legge un risultato e non tre pezzi.
//
// Se l'IA non e' disponibile non si rompe niente: si ripiega sul catalogo
// (ricerca per intento e poi per parole chiave) e lo si dice all'utente.
//
// Solo gli strumenti con ingresso e uscita dichiarati entrano qui: il
// registro (data/agent_tools.json) li elenca con il loro contratto. Gli
// altri 99.000 restano navigabili come sempre, pagina per pagina.
// ============================================================

if (!defined('LUX_AGENT_LOADED')) {
    define('LUX_AGENT_LOADED', true);

    // ------------------------------------------------------------
    // 0. IL REGISTRO DEI TOOL ESEGUIBILI
    // ------------------------------------------------------------
    function lux_agent_registry(): array
    {
        static $reg = null;
        if ($reg !== null) return $reg;

        $file = __DIR__ . '/../data/agent_tools.json';
        $raw  = is_file($file) ? (string)@file_get_contents($file) : '';
        $data = $raw === '' ? null : json_decode($raw, true);
        $reg  = (is_array($data) && is_array($data['tools'] ?? null)) ? $data : ['tools' => []];
        return $reg;
    }

    function lux_agent_tools(): array
    {
        $r = lux_agent_registry();
        return (array)($r['tools'] ?? []);
    }

    function lux_agent_tool(string $key): ?array
    {
        $tools = lux_agent_tools();
        return (isset($tools[$key]) && is_array($tools[$key])) ? $tools[$key] : null;
    }

    function lux_agent_count(): int
    {
        return count(lux_agent_tools());
    }

    /** Quanti strumenti eseguibili per motore (istantanei / IA). */
    function lux_agent_engine_counts(): array
    {
        $out = ['local' => 0, 'ai' => 0];
        foreach (lux_agent_tools() as $t) {
            $e = (string)($t['engine'] ?? 'ai');
            $out[$e] = ($out[$e] ?? 0) + 1;
        }
        return $out;
    }

    // ------------------------------------------------------------
    // 1. L'ELENCO PER IL PIANIFICATORE
    // ------------------------------------------------------------
    // Al modello non si manda il registro intero (troppo): una riga per
    // strumento con chiave, nome, a cosa serve e quali ingressi accetta.
    // Cosi' la scelta e' guidata dai dati veri, non dalla memoria del modello.
    function lux_agent_catalog_for_prompt(int $limitPerTool = 150): string
    {
        $lines = [];
        foreach (lux_agent_tools() as $key => $t) {
            $desc = (string)($t['desc_it'] ?? '');
            if ($desc === '') $desc = (string)($t['desc_en'] ?? '');
            $desc = trim(preg_replace('/\s+/', ' ', $desc));
            if (strlen($desc) > $limitPerTool) $desc = substr($desc, 0, $limitPerTool);

            $ins = [];
            foreach ((array)($t['inputs'] ?? []) as $f) {
                $one = (string)($f['name'] ?? '');
                if ($one === '') continue;
                if (!empty($f['options'])) {
                    $one .= '[' . implode('|', array_slice((array)$f['options'], 0, 12)) . ']';
                } elseif (!empty($f['required'])) {
                    $one .= '*';
                }
                $ins[] = $one;
            }
            $lines[] = $key . ' (' . (string)($t['name'] ?? $key) . '): ' . $desc . ' | ingressi: ' . implode(', ', $ins);
        }
        return implode("\n", $lines);
    }

    // ------------------------------------------------------------
    // 2. IL CONTRATTO DEGLI INGRESSI
    // ------------------------------------------------------------
    // Le stesse regole della pagina dello strumento (lunghezze, valori
    // ammessi, obbligatorieta'), applicate pero' ai valori scelti dal
    // pianificatore: quello che arriva dal modello non e' mai dato per buono.
    function lux_agent_validate_inputs(array $tool, $raw): array
    {
        $raw = is_array($raw) ? $raw : [];
        $in = [];
        $missing = [];

        foreach ((array)($tool['inputs'] ?? []) as $f) {
            $name = (string)($f['name'] ?? '');
            if ($name === '') continue;
            $type = (string)($f['type'] ?? 'text');

            $val = $raw[$name] ?? ($f['default'] ?? '');
            if (is_array($val)) $val = implode(' ', array_map('strval', $val));
            $val = str_replace(["\r\n", "\r"], "\n", trim((string)$val));

            $max = (int)($f['max'] ?? 0);
            if ($max > 0 && strlen($val) > $max) $val = substr($val, 0, $max);

            if (!empty($f['options']) && is_array($f['options'])) {
                $allowed = array_values(array_filter(array_map('strval', $f['options'])));
                if ($val !== '' && !in_array($val, $allowed, true)) $val = '';
                if ($val === '' && !empty($f['default']) && in_array((string)$f['default'], $allowed, true)) {
                    $val = (string)$f['default'];
                }
            }

            if ($type === 'number' && $val !== '') {
                $num = str_replace(',', '.', $val);
                if (!is_numeric($num)) $val = (string)($f['default'] ?? '0');
                else {
                    $val = $num;
                    if (isset($f['min_value']) && is_numeric($f['min_value']) && (float)$val < (float)$f['min_value']) $val = (string)$f['min_value'];
                    if (isset($f['max_value']) && is_numeric($f['max_value']) && (float)$val > (float)$f['max_value']) $val = (string)$f['max_value'];
                }
            }

            if ($val === '' && !empty($f['required'])) $missing[] = $name;
            $in[$name] = $val;
        }

        return ['inputs' => $in, 'missing' => $missing];
    }

    /** I campi di un tool nella forma che serve al browser (per il modulo). */
    function lux_agent_fields(array $tool, string $lang = 'it'): array
    {
        $it = $lang !== 'en';
        $out = [];
        foreach ((array)($tool['inputs'] ?? []) as $f) {
            $out[] = [
                'name'     => (string)($f['name'] ?? ''),
                'type'     => (string)($f['type'] ?? 'text'),
                'label'    => (string)($it ? ($f['label_it'] ?? '') : ($f['label_en'] ?? ($f['label_it'] ?? ''))),
                'required' => !empty($f['required']),
                'max'      => (int)($f['max'] ?? 0),
                'options'  => array_values((array)($f['options'] ?? [])),
                'default'  => (string)($f['default'] ?? ''),
            ];
        }
        return $out;
    }

    // ------------------------------------------------------------
    // 3. IL PIANIFICATORE
    // ------------------------------------------------------------
    // Torna sempre un piano utilizzabile:
    //   mode "ai"      → passi scelti dal pianificatore, con i valori;
    //   mode "catalog" → IA non disponibile: voci del catalogo da esplorare.
    // In nessun caso si risponde con un errore secco: al massimo si dice
    // che si e' passati al catalogo.
    function lux_agent_plan(string $need, string $lang = 'it', array $memory = []): array
    {
        $need = trim($need);
        if ($need === '') {
            return ['success' => false, 'error' => 'empty'];
        }
        $need = substr($need, 0, 700);
        $it   = $lang !== 'en';

        $tools = lux_agent_tools();
        if (!$tools) {
            return lux_agent_catalog_fallback($need, $lang, '');
        }

        $system = 'Sei il pianificatore di L.U.X. Verse. Ricevi un bisogno scritto a parole tue e scegli, fra gli strumenti elencati, quelli che lo risolvono, con i valori di ingresso gia pronti. '
            . 'Gli strumenti disponibili sono elencati cosi: chiave (nome): descrizione | ingressi: campo, campo*, campo[valori]. L asterisco indica un campo obbligatorio; le parentesi quadre i valori ammessi. '
            . 'Regole: usa SOLO le chiavi elencate; riempi ogni campo obbligatorio; per i campi a scelta usa uno dei valori ammessi; scrivi i valori nella lingua dell utente; se il bisogno si risolve con un solo strumento usa un solo passo; '
            . 'usa piu passi (massimo 3) solo quando il secondo passo ha bisogno del risultato del primo, e in quel caso scrivi nel campo dell ultimo passo il segnaposto {passo1}, {passo2} o {passo3} al posto del testo che non conosci ancora. '
            . 'Non inventare campi che non esistono. Non usare emoji. Rispondi SOLO con un oggetto JSON, senza testo attorno: '
            . '{"steps":[{"tool":"chiave","inputs":{"campo":"valore"}}],"why":"una riga che spiega la scelta"}.';

        $user = 'Strumenti disponibili:' . "\n" . lux_agent_catalog_for_prompt() . "\n\n"
              . lux_agent_memory_hint($memory)
              . 'Bisogno dell utente: ' . $need;

        $res = askGroq([
            ['role' => 'system', 'content' => $system],
            ['role' => 'user', 'content' => $user],
        ], ['key' => 4, 'temperature' => 0.2, 'max_tokens' => 900]);

        if (empty($res['success'])) {
            return lux_agent_catalog_fallback($need, $lang, (string)($res['error'] ?? ''));
        }

        $parsed = extractJsonFromAI((string)$res['content']);
        $steps  = [];
        $why    = '';
        if (is_array($parsed)) {
            $why = trim((string)($parsed['why'] ?? ''));
            foreach ((array)($parsed['steps'] ?? []) as $step) {
                if (count($steps) >= 3) break;
                if (!is_array($step)) continue;
                $key  = (string)($step['tool'] ?? ($step['key'] ?? ''));
                $tool = lux_agent_tool($key);
                if (!$tool) continue;
                // Lo stesso strumento due volte nella stessa catena non porta
                // a niente: si tiene il primo.
                foreach ($steps as $s) { if ($s['key'] === $key) continue 2; }

                $v = lux_agent_validate_inputs($tool, $step['inputs'] ?? []);
                $steps[] = [
                    'key'      => $key,
                    'name'     => (string)($tool['name'] ?? $key),
                    'endpoint' => (string)($tool['endpoint'] ?? ('/' . $key . '.php')),
                    'engine'   => (string)($tool['engine'] ?? 'ai'),
                    'output'   => (string)($tool['output'] ?? 'rich'),
                    'icon'     => (string)($tool['icon'] ?? 'sparkles'),
                    'desc'     => (string)($it ? ($tool['desc_it'] ?? '') : ($tool['desc_en'] ?? ($tool['desc_it'] ?? ''))),
                    'inputs'   => $v['inputs'],
                    'missing'  => $v['missing'],
                    'fields'   => lux_agent_fields($tool, $lang),
                ];
            }
        }

        if (!$steps) {
            return lux_agent_catalog_fallback($need, $lang, '');
        }

        return [
            'success'  => true,
            'mode'     => 'ai',
            'need'     => $need,
            'why'      => $why,
            'steps'    => $steps,
            'fallback' => false,
        ];
    }

    /** La riga di memoria: quello che l'assistente ricorda della persona. */
    function lux_agent_memory_hint(array $memory): string
    {
        $bits = [];
        $lang = (string)($memory['lang'] ?? '');
        if ($lang !== '') $bits[] = 'lingua: ' . substr($lang, 0, 5);

        $tone = (string)($memory['tone'] ?? '');
        if ($tone !== '') $bits[] = 'tono preferito: ' . substr($tone, 0, 40);

        $recent = (array)($memory['recent'] ?? []);
        $recent = array_values(array_filter(array_map(function ($r) {
            return preg_replace('/[^a-z0-9_\-]/i', '', (string)$r);
        }, array_slice($recent, 0, 6))));
        if ($recent) $bits[] = 'strumenti usati di recente: ' . implode(', ', $recent);

        $note = (string)($memory['note'] ?? '');
        if ($note !== '') $bits[] = 'nota lasciata dalla persona: ' . substr($note, 0, 160);

        if (!$bits) return '';
        return 'Memoria dell utente (dal suo spazio personale, nessun dato personale): ' . implode('; ', $bits) . ".\n";
    }

    // ------------------------------------------------------------
    // 4. IL RIPIEGO: IL CATALOGO
    // ------------------------------------------------------------
    // Se l'IA non risponde, il bisogno viene cercato nel catalogo (parole
    // chiave sull'intero patrimonio, 100.000 voci). L'utente vede comunque
    // delle proposte, con la spiegazione del perche' non e' stato eseguito.
    function lux_agent_catalog_fallback(string $need, string $lang = 'it', string $reason = '', int $limit = 8): array
    {
        $limit = max(1, min(24, $limit));
        $items = [];
        $seen  = [];

        // 1. Il bisogno intero, cosi' com'e' stato scritto: nel catalogo la
        //    ricerca richiede tutte le parole, quindi va bene per i bisogni
        //    corti e precisi.
        $collect = function (array $rows) use (&$items, &$seen, $limit) {
            foreach ($rows as $r) {
                $url = (string)($r['url'] ?? '');
                if ($url === '' || isset($seen[$url])) continue;
                $seen[$url] = true;
                $items[] = $r;
                if (count($items) >= $limit) break;
            }
        };

        if (function_exists('lux_catalog_page') && $need !== '') {
            $res = lux_catalog_page(['page' => 1, 'per' => $limit, 'q' => $need]);
            $collect((array)($res['items'] ?? []));
        }

        // 2. Parola per parola (le piu' significative), unendo i risultati:
        //    cosi' anche un bisogno lungo trova qualcosa, invece di niente.
        if (count($items) < $limit && function_exists('lux_catalog_page') && $need !== '') {
            $stop = ['che', 'per', 'con', 'una', 'uno', 'del', 'della', 'dei', 'gli', 'sono', 'come', 'poi', 'the', 'for', 'with', 'and', 'from', 'that', 'this', 'please'];
            $words = preg_split('/[^\p{L}\p{N}]+/u', mb_strtolower($need));
            $words = array_values(array_filter($words, function ($w) use ($stop) {
                return mb_strlen((string)$w) > 3 && !in_array((string)$w, $stop, true);
            }));
            usort($words, function ($a, $b) { return mb_strlen((string)$b) <=> mb_strlen((string)$a); });
            foreach (array_slice($words, 0, 4) as $w) {
                if (count($items) >= $limit) break;
                $res = lux_catalog_page(['page' => 1, 'per' => 4, 'q' => $w]);
                $collect((array)($res['items'] ?? []));
            }
        }

        // 3. Non si resta mai a mani vuote: se non c'e' nessuna corrispondenza
        //    si mostrano le prime voci del catalogo, cosi' la pagina resta
        //    utilizzabile anche quando l'IA e' spenta.
        if (!$items && function_exists('lux_catalog_page')) {
            $res = lux_catalog_page(['page' => 1, 'per' => $limit]);
            $collect((array)($res['items'] ?? []));
        }

        return [
            'success'  => true,
            'mode'     => 'catalog',
            'need'     => $need,
            'why'      => '',
            'items'    => $items,
            'fallback' => true,
            'reason'   => substr($reason, 0, 200),
        ];
    }

    // ------------------------------------------------------------
    // 5. L'AGGREGATORE
    // ------------------------------------------------------------
    // Piu' passi, un risultato solo: i testi raccolti vengono uniti in una
    // risposta unica, senza ripetizioni e senza intestazioni inutili. Se
    // l'IA non risponde si torna la catena dei passi: l'utente legge
    // comunque tutto, nell'ordine giusto.
    function lux_agent_aggregate(string $need, array $steps, string $lang = 'it'): array
    {
        $blocks = [];
        foreach ($steps as $i => $s) {
            if (!is_array($s)) continue;
            $text = trim((string)($s['content'] ?? ''));
            if ($text === '') continue;
            $blocks[] = 'PASSO ' . ($i + 1) . ' — ' . (string)($s['name'] ?? '') . ":\n" . $text;
        }
        if (!$blocks) return ['success' => false, 'error' => 'empty'];

        $it = $lang !== 'en';
        $system = $it
            ? 'Sei l aggregatore di L.U.X. Verse. Ricevi i risultati di piu strumenti eseguiti in catena sullo stesso bisogno. Uniscili in UNA risposta sola, ben organizzata, senza ripetere le stesse cose e senza scrivere "passo 1" o "strumento x". Tieni tutti i dati concreti. Usa titoli brevi, elenchi dove aiutano, nessuna emoji. Rispondi solo con il testo finale.'
            : 'You are the aggregator of L.U.X. Verse. You receive the results of several tools run in a chain on the same need. Merge them into ONE well organised answer, without repeating the same things and without writing "step 1" or "tool x". Keep every concrete detail. Use short headings, lists where they help, no emoji. Reply with the final text only.';

        $user = 'Bisogno: ' . substr($need, 0, 400) . "\n\n" . implode("\n\n", $blocks);

        $res = askGroq([
            ['role' => 'system', 'content' => $system],
            ['role' => 'user', 'content' => $user],
        ], ['key' => 4, 'temperature' => 0.4, 'max_tokens' => 1800]);

        if (empty($res['success'])) {
            return ['success' => false, 'error' => (string)($res['error'] ?? '')];
        }
        return ['success' => true, 'content' => (string)$res['content']];
    }
}
