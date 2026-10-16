<?php
// ============================================================
// PERCORSO DEL RILASCIO — dati condivisi
// ============================================================
// Le fasi del percorso stavano scritte dentro roadmap.php. Ora stanno qui,
// perche' le usano due pagine: il percorso del rilascio e la linea del
// tempo. Se una voce cambia stato, cambia in un posto solo e le due pagine
// non possono raccontare cose diverse.
//
// Stato di ogni voce:
//   ok   = fatto, ed e' online in questo rilascio
//   work = in corso, la prima parte c'e' gia'
//   next = annunciato, non promesso per oggi
//
// Ogni voce ha: t (titolo), d (cosa cambia, in parole chiare),
// n (dove vive gia', se vive: conterrà un indirizzo /pagina.php), s (stato).
// ============================================================

if (!function_exists('lux_roadmap_phases')) {

    /**
     * Le fasi del percorso, nella lingua richiesta.
     *
     * @param bool  $isIt   true = italiano
     * @param array $stats  numeri veri: tools, local, ai, total
     */
    function lux_roadmap_phases(bool $isIt, array $stats = []): array
    {
        $nTools = (int)($stats['tools'] ?? 0);
        $nLocal = (int)($stats['local'] ?? 0);
        $nAi    = (int)($stats['ai'] ?? 0);
        $total  = (int)($stats['total'] ?? 0);

        return [
            [
                'state' => 'ok',
                'label' => $isIt ? 'Fatto — in questo rilascio' : 'Done — in this release',
                'items' => [
                    [
                        't' => $isIt ? 'Struttura nuova su tutte le pagine' : 'New structure on every page',
                        'd' => $isIt
                            ? 'Privacy, Termini, App, Bacheca, Chi siamo, FAQ, Assistenza, Contatti, Stato e Linea del tempo: ognuna con il suo impianto, indice, capitoli numerati e i dati veri in testa. Non è un modello ripetuto: è una forma per pagina.'
                            : 'Privacy, Terms, App, Board, About us, FAQ, Support, Contact, Status and Timeline: each with its own layout, index, numbered chapters and the real data at the top. Not one repeated template: a shape per page.',
                        'n' => '',
                    ],
                    [
                        't' => $isIt ? 'La home, rimessa in ordine' : 'The home, put back in order',
                        'd' => $isIt
                            ? 'La pagina iniziale parla la stessa lingua del resto del sito: capitoli numerati, dati veri e un percorso chiaro da dove si arriva a dove si va.'
                            : 'The home page speaks the same language as the rest of the site: numbered chapters, real data and a clear path from where you arrive to where you go.',
                        'n' => '',
                    ],
                    [
                        't' => $isIt ? 'Pagina dell\'app con l\'APK' : 'App page with the APK',
                        'd' => $isIt
                            ? 'Dettagli completi (versione, peso reale, impronta del file, requisiti, permessi) e il pulsante per scaricare. Con le avvertenze per esteso sulla verifica di Google.'
                            : 'Full details (version, real size, file fingerprint, requirements, permissions) and the download button. With the warnings in full about the Google verification.',
                        'n' => $isIt ? 'Online: /app.php' : 'Online: /app.php',
                    ],
                    [
                        't' => $isIt ? 'Privacy e Termini estesi' : 'Privacy and Terms extended',
                        'd' => $isIt
                            ? 'Sedici capitoli ciascuno, aggiornati al 15 ottobre 2026: cookie per nome, spazio personale e codice, risultati condivisi, chiavi API, dove finisce il testo che scrivi all\'IA, tempi di conservazione, diritti.'
                            : 'Sixteen chapters each, updated on 15 October 2026: cookies by name, personal space and code, shared results, API keys, where the text you write to the AI goes, retention times, rights.',
                        'n' => $isIt ? 'Online: /privacy.php' : 'Online: /privacy.php',
                    ],
                    [
                        't' => $isIt ? 'Motore di orchestrazione (fase 1)' : 'Orchestration engine (phase 1)',
                        'd' => $isIt
                            ? 'Il pianificatore legge il bisogno, sceglie gli strumenti dal registro e li esegue: un bisogno con un solo strumento dà il risultato finito senza aprire nessuna pagina.'
                            : 'The planner reads the need, picks the tools from the registry and runs them: a one-tool need gives the finished result without opening any page.',
                        'n' => $isIt ? 'Online: /api/agent.php' : 'Online: /api/agent.php',
                    ],
                    [
                        't' => $isIt ? 'Barra a due modalità' : 'Two-mode bar',
                        'd' => $isIt
                            ? 'Sulla home la stessa barra lavora in due modi: IA (Veloce) esegue, Catalogo (Manuale) fa scegliere a mano. Si cambia con un clic.'
                            : 'On the home the same bar works in two ways: AI (Fast) runs the task, Catalogue (Manual) lets you choose by hand. One click switches between them.',
                        'n' => '',
                    ],
                    [
                        't' => $isIt ? 'Registro dei tool eseguibili' : 'Registry of runnable tools',
                        'd' => $isIt
                            ? 'Solo gli strumenti con ingresso e uscita dichiarati entrano nel motore: gli altri restano navigabili, pagina per pagina, come sempre.'
                            : 'Only tools with a declared input and output enter the engine: all the others stay browsable, page by page, as always.',
                        'n' => $nTools > 0
                            ? ($isIt
                                ? sprintf('%d strumenti eseguibili (%d istantanei, %d con IA) su %s del catalogo', $nTools, $nLocal, $nAi, number_format($total, 0, ',', '.'))
                                : sprintf('%d runnable tools (%d instant, %d AI) out of %s in the catalogue', $nTools, $nLocal, $nAi, number_format($total, 0, ',', '.')))
                            : ($isIt ? 'Registro: data/agent_tools.json' : 'Registry: data/agent_tools.json'),
                    ],
                    [
                        't' => $isIt ? 'Ripiego pulito' : 'Clean fallback',
                        'd' => $isIt
                            ? 'Se l\'IA non è disponibile non si rompe niente: si passa alla ricerca per intento e poi alle parole chiave, e lo si dice all\'utente.'
                            : 'If the AI is not available nothing breaks: the site falls back to intent search and then to keywords, and tells the user about it.',
                        'n' => '',
                    ],
                    [
                        't' => $isIt ? 'Spazio personale con codice' : 'Personal space with a code',
                        'd' => $isIt
                            ? 'Cronologia, preferiti, risultati salvati e memoria dell\'assistente. Un codice personale di 12 caratteri li ritrova su un altro dispositivo: nessuna email, nessun account. Sul server si conserva l\'impronta del codice, non il codice.'
                            : 'History, favourites, saved results and assistant memory. A 12-character personal code finds them on another device: no email, no account. The server keeps the fingerprint of the code, never the code itself.',
                        'n' => $isIt ? 'Online: /spazio.php' : 'Online: /spazio.php',
                    ],
                    [
                        't' => $isIt ? 'Comando universale Ctrl+K' : 'Universal Ctrl+K command',
                        'd' => $isIt
                            ? 'Due tasti e cerchi in un colpo solo fra strumenti, pagine del sito, avvisi della bacheca e catalogo. Nello stesso riquadro: lettura facilitata, testo più grande, lettura ad alta voce e dettatura.'
                            : 'Two keys and you search tools, site pages, board notices and the catalogue at once. In the same box: easier reading, bigger text, read aloud and dictation.',
                        'n' => '',
                    ],
                    [
                        't' => $isIt ? 'Risultati condivisibili' : 'Shareable results',
                        'd' => $isIt
                            ? 'Un risultato salvato può diventare un indirizzo pubblico e stabile, da mandare a chi vuoi senza che contenga niente del tuo codice.'
                            : 'A saved result can become a public, stable address you can send to anyone, with nothing of your code inside.',
                        'n' => $isIt ? 'Online: /condividi.php' : 'Online: /condividi.php',
                    ],
                    [
                        't' => $isIt ? 'Bottoni e etichette, rimodellati' : 'Buttons and labels, redesigned',
                        'd' => $isIt
                            ? 'Una misura sola per i pulsanti, angoli morbidi, niente scatti al passaggio del mouse. Le etichette di stato (Aggiornamento, Segui ci, Urgente) sono pastiglie con il pallino colorato, e la riga dei canali compare una volta sola per avviso.'
                            : 'A single measure for buttons, soft corners, no jumping on hover. Status labels (Update, Follow us, Urgent) are pills with a coloured dot, and the channel row appears once per notice.',
                        'n' => $isIt ? 'Online: /bacheca.php' : 'Online: /bacheca.php',
                    ],
                    [
                        't' => $isIt ? 'Il racconto sulla privacy, corretto' : 'The privacy claim, corrected',
                        'd' => $isIt
                            ? 'Il sito usa cookie tecnici (sessione, lingua, scelta sui cookie) e non usa cookie di tracciamento. La scritta in home non dice più "nessun cookie" ma "nessun cookie di tracciamento": è la verità, ed è verificabile.'
                            : 'The site uses technical cookies (session, language, cookie choice) and no tracking cookies. The badge on the home no longer says "no cookies" but "no tracking cookies": that is the truth, and it can be checked.',
                        'n' => '',
                    ],
                ],
            ],
            [
                'state' => 'work',
                'label' => $isIt ? 'Subito dopo — quello che stiamo costruendo' : 'Right after — what we are building',
                'items' => [
                    [
                        't' => $isIt ? 'Catene multi-strumento e aggregazione' : 'Multi-tool chains and aggregation',
                        'd' => $isIt
                            ? 'Un bisogno che si risolve in più passaggi (calcola, poi scrivi, poi confronta) diventa una catena eseguita in ordine, con un risultato unico invece di tre pezzi.'
                            : 'A need that takes several steps (calculate, then write, then compare) becomes a chain run in order, with one single result instead of three pieces.',
                        'n' => $isIt ? 'Prima parte online: l\'aggregatore è attivo' : 'First part online: the aggregator is live',
                    ],
                    [
                        't' => $isIt ? 'Ricerca per significato' : 'Search by meaning',
                        'd' => $isIt
                            ? 'Oggi la ricerca per intento capisce il senso della frase; il passo successivo è la ricerca per significato su tutto il catalogo, che trova strumenti che la ricerca per nome non troverebbe. Richiede un servizio di vettori (embeddings): lo scegliamo con calma, perché deve restare gratuito e senza tracciamento.'
                            : 'Today intent search understands the meaning of a sentence; the next step is search by meaning over the whole catalogue, finding tools that a name search would never find. It needs an embeddings service: we will pick one carefully, because it must stay free and without tracking.',
                        'n' => $isIt ? 'Da decidere' : 'To be decided',
                    ],
                    [
                        't' => $isIt ? 'Widget da incorporare' : 'Embeddable widget',
                        'd' => $isIt
                            ? 'Un modo per portare uno strumento dentro un sito esterno con una riga di codice, con il link che torna a L.U.X. Verse.'
                            : 'A way to bring a tool inside an external site with one line of code, with the link going back to L.U.X. Verse.',
                        'n' => $isIt ? 'Prima versione online: /embed.php' : 'First version online: /embed.php',
                    ],
                    [
                        't' => $isIt ? 'Notifiche push (facoltative)' : 'Push notifications (optional)',
                        'd' => $isIt
                            ? 'Per chi lo vuole: un avviso sul telefono quando esce qualcosa di nuovo. Spento per tutti finché non lo accendi tu.'
                            : 'For whoever wants it: a notice on the phone when something new comes out. Off for everyone until you turn it on.',
                        'n' => '',
                    ],
                ],
            ],
            [
                'state' => 'next',
                'label' => $isIt ? 'Più avanti — annunciato, non promesso per oggi' : 'Later — announced, not promised for today',
                'items' => [
                    [
                        't' => $isIt ? 'Community e marketplace' : 'Community and marketplace',
                        'd' => $isIt
                            ? 'La bacheca "Proponi uno strumento" con i voti è già online. Dopo arriva il marketplace degli strumenti e dei prompt creati dal pubblico, con la classifica.'
                            : 'The "Suggest a tool" board with votes is online. Next comes the marketplace of tools and prompts created by the public, with a ranking.',
                        'n' => $isIt ? 'Prima parte online: /proponi.php' : 'First part online: /proponi.php',
                    ],
                    [
                        't' => $isIt ? 'API pubblica e SDK' : 'Public API and SDK',
                        'd' => $isIt
                            ? 'Chiavi anonime con limite di velocità, sandbox di prova, SDK JavaScript e documentazione, per usare i nostri strumenti dentro i propri progetti.'
                            : 'Anonymous keys with rate limits, a test sandbox, a JavaScript SDK and documentation, to use our tools inside your own projects.',
                        'n' => $isIt ? 'Prima versione online: /sviluppatori.php' : 'First version online: /sviluppatori.php',
                    ],
                    [
                        't' => $isIt ? 'Altre lingue' : 'More languages',
                        'd' => $isIt
                            ? 'Oggi italiano e inglese, con tutte le stringhe nuove tradotte in entrambe. Le lingue successive si aggiungono solo quando la traduzione è completa, non a metà.'
                            : 'Today Italian and English, with every new string translated in both. Further languages will be added only when the translation is complete, never half done.',
                        'n' => '',
                    ],
                    [
                        't' => $isIt ? 'Accessibilità avanzata' : 'Advanced accessibility',
                        'd' => $isIt
                            ? 'Lettura facilitata, testo più grande, lettura ad alta voce e dettatura sono già attive nel comando Ctrl+K. Dopo: controllo vocale completo e modalità offline.'
                            : 'Easier reading, bigger text, read aloud and dictation are already live in the Ctrl+K command. Next: full voice control and an offline mode.',
                        'n' => '',
                    ],
                    [
                        't' => $isIt ? 'Trasparenza e dati aperti' : 'Transparency and open data',
                        'd' => $isIt
                            ? 'Una pagina pubblica con i numeri veri del progetto (strumenti, richieste, stato dei servizi), senza numeri inventati e senza servizi di monitoraggio esterni.'
                            : 'A public page with the real numbers of the project (tools, requests, service status), with no invented figures and no external monitoring services.',
                        'n' => $isIt ? 'Oggi: /status.php' : 'Today: /status.php',
                    ],
                ],
            ],
        ];
    }

    /** Etichetta dello stato, tradotta. */
    function lux_roadmap_state_label(string $state, bool $isIt): string
    {
        $m = [
            'ok'   => $isIt ? 'Fatto'      : 'Done',
            'work' => $isIt ? 'In corso'   : 'In progress',
            'next' => $isIt ? 'Annunciato' : 'Announced',
        ];
        return $m[$state] ?? '';
    }

    /**
     * Estrae da una nota l'eventuale indirizzo interno (/pagina.php) e lo
     * rende cliccabile: nella linea del tempo una voce deve portare dove
     * quella cosa vive davvero.
     */
    function lux_roadmap_note_html(string $note): string
    {
        if ($note === '') return '';
        if (preg_match('#(/[a-zA-Z0-9_\-/]+\.php)#', $note, $m)) {
            $url  = $m[1];
            $safe = htmlspecialchars($url);
            $note = str_replace($url, '<a href="' . $safe . '">' . $safe . '</a>', htmlspecialchars($note));
            return '<span class="rl-note">' . $note . '</span>';
        }
        return '<span class="rl-note">' . htmlspecialchars($note) . '</span>';
    }
}
