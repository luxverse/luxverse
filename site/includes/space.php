<?php
// ============================================================
// L.U.X. VERSE — SPAZIO PERSONALE (WS2, versione 39.1.1)
// ------------------------------------------------------------
// La continuita' fra dispositivi senza registrazione.
//
// Come funziona, in una riga: il tuo spazio vive nel browser (cronologia,
// preferiti, risultati salvati) e il "codice personale" lo fa ritrovare su
// un altro dispositivo. Il codice e' una chiave, non un documento: non c'e'
// email, non c'e' password, non c'e' nome. Il server conserva il contenuto
// sotto l'impronta del codice (sha256), quindi il codice in chiaro non
// esiste da nessuna parte tranne che sul dispositivo di chi lo usa.
//
// Niente database: un file solo, data/spazio.json, con dentro le mappe.
// Le regole di casa: si estende quello che c'e' (lux_map_read/lux_map_write),
// non si riscrive niente.
// ============================================================

if (!defined('LUX_SPACE_LOADED')) {
    define('LUX_SPACE_LOADED', true);

    // Limiti: su un hosting gratuito lo spazio di ognuno deve restare piccolo.
    define('LUX_SPACE_MAX_BYTES', 60000);   // contenuto massimo per codice
    define('LUX_SPACE_MAX_CODES', 2000);    // quanti spazi si conservano

    // ------------------------------------------------------------
    // 1. IL CODICE PERSONALE
    // ------------------------------------------------------------
    // Alfabeto senza caratteri che si confondono leggendoli (niente 0/O,
    // 1/I/L): si scrive a mano su un altro dispositivo, quindi deve essere
    // impossibile sbagliare.
    function lux_space_alphabet(): string
    {
        return '23456789ABCDEFGHJKMNPQRSTUVWXYZ';
    }

    /** Toglie spazi e trattini e mette tutto in maiuscolo. */
    function lux_space_code_normalize(string $code): string
    {
        $code = strtoupper(preg_replace('/[^A-Za-z0-9]/', '', $code));
        return substr($code, 0, 12);
    }

    function lux_space_code_valid(string $code): bool
    {
        $c = lux_space_code_normalize($code);
        return (bool)preg_match('/^[' . lux_space_alphabet() . ']{12}$/', $c);
    }

    /** Come si scrive il codice per leggerlo: LUX-7K4M-2P9Q (4+4+4). */
    function lux_space_code_pretty(string $code): string
    {
        $c = lux_space_code_normalize($code);
        return implode('-', str_split($c, 4));
    }

    /** L'impronta: e' l'unica cosa che sta sul server. */
    function lux_space_hash(string $code): string
    {
        return substr(hash('sha256', 'lux-space|' . lux_space_code_normalize($code)), 0, 32);
    }

    /** Nuovo codice personale, garantito non gia' in uso. */
    function lux_space_new_code(): string
    {
        $alpha = lux_space_alphabet();
        $len   = strlen($alpha);
        $all   = lux_map_read('spazio');

        for ($try = 0; $try < 12; $try++) {
            $code = '';
            for ($i = 0; $i < 12; $i++) {
                $code .= $alpha[random_int(0, $len - 1)];
            }
            if (!isset($all[lux_space_hash($code)])) return $code;
        }
        // Ripiego (praticamente impossibile): si allunga con l'orologio.
        return substr($code . dechex(time()), 0, 12);
    }

    // ------------------------------------------------------------
    // 2. LEGGERE E SCRIVERE
    // ------------------------------------------------------------
    /** Lo spazio salvato, oppure null se quel codice non ha ancora niente. */
    function lux_space_get(string $code): ?array
    {
        if (!lux_space_code_valid($code)) return null;
        $all = lux_map_read('spazio');
        $row = $all[lux_space_hash($code)] ?? null;
        return is_array($row) ? $row : null;
    }

    /** Scrive lo spazio. Ritorna true se e' andata. */
    function lux_space_save(string $code, array $payload): bool
    {
        if (!lux_space_code_valid($code)) return false;
        $json = json_encode($payload, JSON_UNESCAPED_UNICODE);
        if ($json === false || strlen($json) > LUX_SPACE_MAX_BYTES) return false;

        $all = lux_map_read('spazio');
        $hash = lux_space_hash($code);
        $now  = date('Y-m-d H:i:s');
        $all[$hash] = [
            'created' => (string)($all[$hash]['created'] ?? $now),
            'updated' => $now,
            'lang'    => substr((string)($payload['lang'] ?? 'it'), 0, 2),
            'data'    => $payload,
        ];

        // Pulizia: si tengono gli spazi usati piu' di recente.
        if (count($all) > LUX_SPACE_MAX_CODES) {
            uasort($all, function ($a, $b) {
                return strcmp((string)($b['updated'] ?? ''), (string)($a['updated'] ?? ''));
            });
            $all = array_slice($all, 0, LUX_SPACE_MAX_CODES, true);
        }
        return lux_map_write('spazio', $all);
    }

    /** Solo il contenuto (quello che serve al browser). */
    function lux_space_payload(string $code): array
    {
        $row = lux_space_get($code);
        $data = is_array($row['data'] ?? null) ? $row['data'] : [];
        return lux_space_clean($data);
    }

    // ------------------------------------------------------------
    // 3. PULIZIA E UNIONE
    // ------------------------------------------------------------
    /** Tiene solo quello che ci si aspetta, con i limiti di casa. */
    function lux_space_clean(array $data): array
    {
        $keys = function ($list, int $max, array $fields) {
            $out = [];
            foreach (array_slice((array)$list, 0, $max) as $row) {
                if (!is_array($row)) continue;
                $one = [];
                foreach ($fields as $f => $len) {
                    $v = (string)($row[$f] ?? '');
                    $one[$f] = ($len > 0) ? substr($v, 0, $len) : $v;
                }
                if (implode('', $one) === '') continue;
                $out[] = $one;
            }
            return $out;
        };

        return [
            'lang'    => substr((string)($data['lang'] ?? 'it'), 0, 2),
            'history' => $keys($data['history'] ?? [], 40, ['key' => 60, 'name' => 120, 'url' => 160, 'at' => 20]),
            'favs'    => $keys($data['favs'] ?? [], 100, ['key' => 60, 'name' => 120, 'url' => 160]),
            'saved'   => $keys($data['saved'] ?? [], 30, ['id' => 24, 'tool' => 60, 'title' => 160, 'content' => 3000, 'at' => 20]),
            'memory'  => [
                'tone' => substr((string)($data['memory']['tone'] ?? ''), 0, 40),
                'note' => substr((string)($data['memory']['note'] ?? ''), 0, 300),
            ],
        ];
    }

    /**
     * Unisce lo spazio del server con quello del dispositivo che lo chiede.
     * Vince la versione piu' recente per ogni voce, senza duplicare i
     * doppioni e senza perdere quello che c'era solo da una parte.
     */
    function lux_space_merge(array $server, array $client): array
    {
        $server = lux_space_clean($server);
        $client = lux_space_clean($client);
        if (strtolower((string)($client['lang'] ?? '')) !== '') $server['lang'] = $client['lang'];

        foreach (['history', 'favs', 'saved'] as $where) {
            $merged = [];
            $index  = [];
            $rows   = array_merge((array)$client[$where], (array)$server[$where]);
            foreach ($rows as $row) {
                if (!is_array($row)) continue;
                $k = (string)($row['key'] ?? ($row['id'] ?? ''));
                if ($k === '') continue;
                if (isset($index[$k])) {
                    // Stessa voce: si tiene la piu' recente (se la data c'e').
                    $prev = $merged[$index[$k]];
                    if (strcmp((string)($row['at'] ?? ''), (string)($prev['at'] ?? '')) > 0) {
                        $merged[$index[$k]] = $row;
                    }
                    continue;
                }
                $index[$k] = count($merged);
                $merged[]  = $row;
            }
            $cap = $where === 'saved' ? 30 : ($where === 'favs' ? 100 : 40);
            if (count($merged) > $cap) $merged = array_slice($merged, -$cap);
            $merged = array_reverse($merged);   // prima i piu' recenti
            $server[$where] = array_values($merged);
        }

        foreach (['tone', 'note'] as $f) {
            if ((string)($client['memory'][$f] ?? '') !== '') {
                $server['memory'][$f] = (string)$client['memory'][$f];
            }
        }
        return lux_space_clean($server);
    }

    // ------------------------------------------------------------
    // 4. RISULTATI CONDIVISIBILI (permalink, WS4)
    // ------------------------------------------------------------
    // Un risultato salvato puo' diventare un indirizzo pubblico e stabile:
    // si conserva il testo e si restituisce l'id. Il link non cambia mai e
    // non contiene niente del codice personale di chi lo ha creato.
    function lux_space_share(string $tool, string $title, string $content, string $lang = 'it'): string
    {
        $content = trim($content);
        if ($content === '') return '';
        $id = substr(bin2hex(random_bytes(6)), 0, 12);

        $all = lux_map_read('shares');
        $all[$id] = [
            'tool'    => substr(preg_replace('/[^a-z0-9_\-]/i', '', $tool), 0, 60),
            'title'   => substr(trim($title), 0, 160),
            'content' => substr($content, 0, 20000),
            'lang'    => $lang === 'en' ? 'en' : 'it',
            'date'    => date('Y-m-d H:i:s'),
        ];
        if (count($all) > 3000) {
            uasort($all, function ($a, $b) {
                return strcmp((string)($b['date'] ?? ''), (string)($a['date'] ?? ''));
            });
            $all = array_slice($all, 0, 3000, true);
        }
        if (!lux_map_write('shares', $all)) return '';
        return $id;
    }

    function lux_space_share_get(string $id): ?array
    {
        $id = preg_replace('/[^a-f0-9]/', '', strtolower($id));
        if (strlen($id) !== 12) return null;
        $all = lux_map_read('shares');
        $row = $all[$id] ?? null;
        return is_array($row) ? $row : null;
    }

    // ------------------------------------------------------------
    // 5. CONTROLLO AUTOMATICO DELLE PROPOSTE (WS5)
    // ------------------------------------------------------------
    // La bacheca "Proponi uno strumento" e' aperta a tutti e anonima: senza
    // un controllo automatico diventerebbe in due giorni un muro di
    // pubblicita'. Questo non giudica le idee, scarta solo quello che non e'
    // un'idea: messaggi vuoti, muri di link, parole da spot, doppioni.
    function lux_suggest_vet(string $name, string $why, array $existing = []): array
    {
        $fail = function (string $why) { return ['ok' => false, 'why' => $why]; };

        if (mb_strlen($name) < 4)  return $fail(t('Write the name of the tool you would like (at least 4 characters).'));
        if (mb_strlen($why) < 20)  return $fail(t('Tell us in a few words what it should do (at least 20 characters).'));

        $joined = mb_strtolower($name . ' ' . $why);

        // 1. Troppi link (o link nel nome): e' pubblicita', non una proposta.
        if (preg_match_all('#(https?://|www\.)#i', $joined) > 1 || preg_match('#(https?://|www\.)#i', mb_strtolower($name))) {
            return $fail(t('Links are not allowed in a proposal: describe the tool with your own words.'));
        }

        // 2. Parole da spot e da truffa: stesso trattamento.
        $spam = ['viagra', 'casino', 'scommess', 'crypto pump', 'guadagno facile', 'guadagnare subito', 'click here', 'clikka qui', 'telegram t.me', 'whatsapp +', 'seo service', 'backlink', 'cialis', 'porn', 'xxx'];
        foreach ($spam as $w) {
            if (mb_strpos($joined, $w) !== false) {
                return $fail(t('That proposal looks like advertising. Write what the tool should do, with your own words.'));
            }
        }

        // 3. Niente muri di lettere ripetute ("aaaaaaa"): non e' un nome.
        if (preg_match('/(.)\1{6,}/u', $joined)) {
            return $fail(t('Please write a real name for the tool.'));
        }

        // 4. Doppioni: se uno strumento con lo stesso nome e' gia' stato
        //    proposto (e non e' stato scartato), si vota quello.
        $norm = function ($s) {
            return preg_replace('/[^a-z0-9]/u', '', mb_strtolower((string)$s));
        };
        $key = $norm($name);
        if ($key !== '') {
            foreach ($existing as $row) {
                if (!is_array($row)) continue;
                if (in_array((string)($row['status'] ?? ''), ['spam', 'rifiutato'], true)) continue;
                if ($norm($row['name'] ?? '') === $key) {
                    return $fail(t('This tool has already been proposed: vote the existing proposal instead.'));
                }
            }
        }

        return ['ok' => true, 'why' => ''];
    }
}
