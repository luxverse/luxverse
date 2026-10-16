# L.U.X. Verse — codice aperto

L.U.X. Verse è un progetto **no profit** che mette a disposizione di tutti, gratis e senza
registrazione, un catalogo di strumenti digitali e un piccolo ecosistema di intelligenza
artificiale. Il sito è **luxverse.c22.eu**; l'app Android è una finestra sul sito.

Questo repository contiene **tutto il codice**: il sito (PHP) e l'app (Kotlin/Android).
Il codice è pubblicato perché chiunque possa leggerlo, controllare cosa fa e compilarlo da
sé, senza doversi fidare sulla parola.

- **Sito**: PHP 8, niente database (i dati sono file JSON in `site/data/`), niente
  framework, niente dipendenze da installare.
- **App**: Kotlin, una `WebView` a schermo intero sul sito live, più la schermata di
  avvio, il controllo degli aggiornamenti e la pagina di riserva quando manca la rete.
- **Licenza**: MIT (vedi [LICENSE](LICENSE)).
- **File dell'app**: pubblicato qui fra le [release](../../releases) come `LUXVerse-39.1.3.apk`.

## Cosa NON c'è in questo repository (e perché)

Niente di quello che serve a far funzionare il sito in produzione è qui dentro, ed è una
scelta: la cassaforte dei segreti, le credenziali di pubblicazione, la chiave di firma
dell'APK e i dati scritti dagli utenti restano fuori dal codice pubblico.

| Non c'è | Cos'è |
|---|---|
| `site/data/vault.local.php` | la cassaforte: chiavi delle API AI, token interno, hash della password di amministrazione |
| `site/deploy.local.php` | credenziali di pubblicazione sul server (FTP) |
| `site/config.local.php` | eventuali sovrascritture locali della configurazione |
| `site/admin_setup.php` | lo script che genera password e token (si usa una volta, sul proprio computer) |
| `site/tools/` | gli script di manutenzione che usiamo noi (build del catalogo, mappe del sito, pubblicazione): contengono percorsi e indirizzi della nostra macchina |
| `app/keystore.properties`, `app/*.jks` | la chiave privata con cui firmiamo l'APK: se la pubblichiamo, chiunque può firmare un'app che si spaccia per la nostra |
| `site/data/*.json` di stato | visite, registro attività, messaggi, chat, ticket, codici personali: sono dati di chi usa il sito |
| i file giganti `sitemap-strumenti-*.xml` | si rigenerano dal catalogo (`site/data/projects.json`) |

Nel `site/config.php` la lista `VISITS_IGNORED_IPS` è **vuota**: nel sito vero contiene
l'indirizzo IP di casa di chi lo gestisce, che è un dato personale e non serve a chi legge
il codice. Nel file trovi il commento che spiega dove rimettere il proprio.

## Verifica del file, senza fidarsi

L'APK dell'app si può controllare in due modi indipendenti:

1. **Il rapporto pubblico su VirusTotal** — decine di motori antivirus diversi analizzano
   il file e il risultato è visibile a tutti:
   <https://www.virustotal.com/gui/file/dfdfbda322e368210505b4e142c434aa1b5ad43b7f1fcd74f95cdcb8ecaacbab/detection>
2. **L'impronta del file** — confronta l'SHA-256 che vedi nella pagina dell'app sul sito
   con quella del file che hai scaricato. Devono combaciare, carattere per carattere.

Impronta SHA-256 dell'APK 39.1.3 (build 44) pubblicato in queste release:

```
9bcecb4a8811ed5275aa5f36c01121e7a34dc2f2c24d7971726c7924637b79fd  LUXVerse-39.1.3.apk
```

L'APK è firmato con il certificato `CN=WorkMeAI` (SHA-256
`f969c6b36e2bc3c2b10e27cff012367e38bce5e99b604ae7b597d9dc31027b14`): se un file dichiara
lo stesso nome ma una firma diversa, non è il nostro.

## Struttura

```
app/            il progetto Android (Kotlin) — si apre in Android Studio
  app/src/main/ codice, risorse, AndroidManifest.xml
  gradle/       wrapper di Gradle (Gradle 8.13, JDK 17)
site/           il sito PHP — si copia su un server con PHP 8
  includes/     il motore: pagine, catalogo, guida, piattaforma, spazio personale
  api/          api.php: tutti gli endpoint (JSON) usati da sito e app
  data/         i dati in chiaro: catalogo, avvisi, traduzioni dei contenuti
  assets/       CSS, JavaScript, immagini
  lang/         le traduzioni dell'interfaccia (italiano e inglese)
release/        l'APK pronto da allegare a una release di GitHub
```

## Compilare l'app

Servono **JDK 17** e l'**Android SDK** (compileSdk 36, build-tools recenti).

```bash
cd app
./gradlew assembleDebug          # APK di prova, senza firma: app/build/outputs/apk/debug/
./gradlew assembleRelease        # APK da pubblicare (va firmato, vedi sotto)
```

Per firmare la release, crea `app/keystore.properties` (è in `.gitignore`, non finisce mai
nel repository):

```properties
storeFile=../la-tua-chiave.jks
storePassword=...
keyAlias=...
keyPassword=...
```

Senza quel file il progetto compila lo stesso: l'APK resta semplicemente non firmato. La
chiave privata di questo progetto non è pubblica, quindi l'APK che compili tu non potrà
sostituire quello installato da noi (Android accetta solo aggiornamenti con la stessa
firma). Per provarla, disinstalla prima l'app ufficiale.

L'indirizzo del sito è in `app/build.gradle.kts` (`SITE_URL`, `APP_URL`): cambialo se
punti a un sito tuo.

## Mettere in piedi il sito

Serve PHP 8 con le estensioni standard, un server web e una cartella scrivibile.

1. copia il contenuto di `site/` nella cartella pubblica del server;
2. crea `site/data/vault.local.php` con i tuoi segreti (anche vuoto: senza di esso
   l'accesso all'amministrazione resta chiuso e le funzioni AI restano spente);
3. apri il sito: la prima pagina funziona già. Il catalogo è in
   `site/data/projects.json`.

La cartella `site/data/` deve contenere anche il suo `.htaccess`: è quello che impedisce
al web di leggere direttamente i file di dati. Su server che non leggono `.htaccess`
(Apache) quella protezione va riprodotta nella configurazione del server.

## Segnalare un problema di sicurezza

Vedi [SECURITY.md](SECURITY.md). In due parole: scrivi in privato, non aprire una issue
pubblica con i dettagli, e non provare nulla su dati o utenti reali.

## Contribuire

Vedi [CONTRIBUTING.md](CONTRIBUTING.md). Il progetto è piccolo e a conduzione familiare:
prima di scrivere molto codice, apri una issue e parliamone.

## Come pubblicare una versione (promemoria per chi mantiene il progetto)

1. alza `versionCode` e `versionName` **sia** in `app/app/build.gradle.kts` **sia** in
   `site/config.php` (`LUX_APP_VERSION_CODE`, `LUX_APP_VERSION`): le due versioni devono
   restare allineate, altrimenti l'app installata propone un aggiornamento che non esiste;
2. compila l'APK firmato e caricalo sul sito come `LUXVerse.apk`;
3. crea qui una release con il tag della versione (per esempio `v39.1.3`) e allega l'APK
   rinominato `LUXVerse-39.1.3.apk`;
4. carica lo stesso APK su VirusTotal e aggiorna il link nella pagina dell'app
   (`site/app.php`, variabile `$vtUrl`) e in questo README.

---

## English (short version)

L.U.X. Verse is a non-profit project: a free catalogue of digital tools plus a small AI
ecosystem, with no registration. This repository holds the whole source code — the PHP
site (`site/`) and the Android app (`app/`, a Kotlin WebView wrapper around the live
site). It is published so that anyone can read it, check what it does and build it.

Everything needed to run the service is deliberately **not** here: the secrets vault, the
deployment credentials, the APK signing key and user-generated data are all excluded (see
the table above). The `VISITS_IGNORED_IPS` list in `site/config.php` is empty on purpose:
in the live site it holds the maintainer's home IP address, which is personal data and is
of no use to anyone reading the code.

The APK is published in the [releases](../../releases) and can be verified on VirusTotal
(link above) or by comparing its SHA-256 fingerprint with the one printed on the app page
of the site. Licence: MIT. Security reports: see [SECURITY.md](SECURITY.md).
