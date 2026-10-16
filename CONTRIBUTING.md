# Contribuire

Grazie dell'interesse. Il progetto è piccolo e condotto da una persona sola, quindi la
cosa più utile non è scrivere molto codice di colpo: è **parlarsi prima**.

## Prima di scrivere codice

Apri una issue e racconta cosa vuoi cambiare e perché. Vale per tutto: una correzione,
una funzione nuova, una riscrittura. Si evita così di lavorare su qualcosa che poi non
entra, e si tiene il progetto leggibile.

Per un errore, invece, puoi anche andare diretto: riproduci, spiega, allega lo schermo o
il testo dell'errore, e indica versione dell'app (si legge nella schermata di avvio) e
pagina del sito.

## Regole del progetto

- **Niente dipendenze in più** se si può evitare. Il sito è PHP puro senza framework e
  senza database: è una scelta, non una dimenticanza. Una funzione nuova si scrive con
  quello che c'è.
- **Italiano nei commenti, italiano e inglese nei testi.** I commenti spiegano il *perché*
  di una scelta, non ripetono il codice. Le stringhe che legge l'utente passano da
  `site/lang/lang.php`, sempre in tutte e due le lingue.
- **Non si rompe quello che funziona.** Le modifiche al sito si provano prima in locale
  con `php -S 127.0.0.1:8000` dentro `site/`, su una copia dei dati.
- **Niente segreti nel codice.** Chiavi, password e token stanno nella cassaforte
  (`site/data/vault.local.php`), che non è nel repository. Una pull request che contiene
  una chiave viene chiusa e la chiave va considerata bruciata.

## Come si manda una modifica

1. fai un *fork* del repository;
2. lavora su un ramo con un nome chiaro (`correzione-tastiera`, `nuova-pagina-faq`);
3. prova la modifica in locale (il sito con `php -S`, l'app con `./gradlew assembleDebug`);
4. apri la *pull request* spiegando cosa cambia e come lo hai provato. Se tocca testi
   visibili, indica anche la pagina del sito dove si vede.

Sulle modifiche più grandi (nuove sezioni del sito, cambi al catalogo, cambi all'app)
aspetta un parere prima di scrivere: si risparmia tempo tutti e due.

## Cosa non entra

- Dati di persone reali (messaggi, chat, indirizzi IP, codici personali): mai nel
  repository, nemmeno come esempio. Usa dati inventati.
- Chiavi di qualsiasi tipo, anche se sembrano "solo di prova".
- File compilati, APK e librerie scaricate: si ricompilano.
- Pubblicità, tracciamento, analytics e raccolta dati di qualsiasi genere: il progetto è
  no profit e senza tracciamento, e questa parte non si negozia.
