# Il sito

PHP 8 puro: nessun framework, nessun database, nessuna dipendenza da installare. I dati
stanno in file JSON dentro `data/`, che è anche la cartella scrivibile dal sito.

## Provarlo sul proprio computer

```bash
cd site
php -S 127.0.0.1:8000
```

Poi apri <http://127.0.0.1:8000>. Il catalogo è già dentro (`data/projects.json`), quindi
la prima pagina si vede subito. Le funzioni AI restano spente finché non metti una chiave
nella cassaforte: è voluto, il sito non deve spendere chiavi di nessuno.

## Cosa manca qui dentro (ed è normale)

- `data/vault.local.php` — la cassaforte dei segreti: chiavi delle API AI, token interno
  (`API_TOKEN`) e hash della password di amministrazione (`ADMIN_PASSWORD_HASH`). Crea il
  file tu, anche vuoto: senza di esso l'amministrazione resta chiusa e le funzioni AI
  spente, che è il ripiego giusto.
- `deploy.local.php` — le credenziali di pubblicazione: servono solo a chi pubblica.
- `admin_setup.php` — lo script che genera password e token: si usa una volta, sul proprio
  computer, non sul server.
- `tools/` — gli script di manutenzione (build del catalogo, mappe del sito, icone,
  pubblicazione). Restano fuori dal repository perché contengono i percorsi e gli
  indirizzi della macchina di chi mantiene il progetto.

## Come è fatto, in breve

- `index.php` — la prima pagina: ricerca, catalogo, avvisi, spazio personale.
- `tool.php` — il motore: genera la pagina di ogni strumento a partire dal catalogo.
- `api/api.php` — tutti gli endpoint in un file solo (JSON), usati dal sito e dall'app.
- `includes/` — il motore condiviso: scheletro delle pagine (`page.php`), testi comuni e
  testata (`lux.php`), catalogo (`catalog.php`), avvisi (`maintenance.php`), guida
  (`lux_guide.php`), piattaforma (`lux_platform.php`), spazio personale (`space.php`).
- `lang/lang.php` — le traduzioni dell'interfaccia: ogni voce esiste in inglese e in
  italiano. Se aggiungi un testo visibile, aggiungilo qui in tutte e due le lingue.
- `data/` — i dati. Vedi `data/README.md` per sapere quali file sono contenuto e quali
  vengono creati dal sito mentre lavora.

## La verifica anti-bot dell'hosting

Sul sito pubblico (hosting gratuito) ogni richiesta passa da una verifica in JavaScript
prima di arrivare a PHP: la prima risposta non è la pagina, è un piccolo documento che
esegue uno script e ricarica (`aes.js` e il cookie `__test`, non sono file del progetto).
Per un browser è invisibile; per un client che non esegue JavaScript (per esempio una
chiamata API diretta da un'app) significa ricevere quella pagina invece del JSON. È il
motivo per cui l'app chiede la versione del sito **dentro la WebView** e non con una
chiamata diretta: vedi `app/app/src/main/java/org/luxverse/app/ui/MainActivity.kt`.
