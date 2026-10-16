# I dati del sito

Questa cartella è il "database" del progetto: file JSON leggibili con un editor di testo.
Il web non può leggerli direttamente perché c'è un `.htaccess` che li protegge: restano
accessibili solo dal codice PHP del sito.

## Contenuto, e sta nel repository

| File | Cosa contiene |
|---|---|
| `projects.json` | il catalogo: nome, categoria, descrizione (italiano e inglese), colore e icona di ogni strumento |
| `aliases.json` | i vecchi indirizzi ricostruiti: servono a non perdere i collegamenti già in giro |
| `agent_tools.json` | il registro degli strumenti eseguibili: cosa accettano in ingresso e cosa restituiscono |
| `notices.json` | gli avvisi della bacheca (notizie pubbliche) |
| `notifications.json` | le notifiche del campanello (pubbliche anche quelle) |

## File creati dal sito mentre lavora, e quindi NON nel repository

Sono dati di chi usa il sito, o stato del server: rinascere vuoti al primo avvio è la
cosa giusta.

`visits.json`, `logs.json`, `chats.json`, `chat.json`, `posts.json`, `messages.json`,
`tickets.json`, `feedback.json`, `suggestions.json`, `assistance.json`, `users.json`,
`spazio.json`, `leaderboard.json`, `signatures.json`, `states.json`, `status.json`,
`ratelimit.json`, `upvotes.json`, `usage.json`, `ai_health.json`, `maintenance.json`,
`security.json`, `moved.json`, `apikeys.json`.

Il `.gitignore` nella radice del repository li tiene fuori comunque, anche se qualcuno li
copiasse per sbaglio nella cartella.

## La cassaforte

`vault.local.php` è l'unico file di questa cartella che contiene segreti: chiavi delle API
AI, token interno e hash della password di amministrazione. Non è nel repository e non
deve finirci mai. Il sito lo legge se c'è, e senza di esso si comporta in modo prudente:
amministrazione chiusa, funzioni AI spente.
