# L'app Android di L.U.X. Verse

Kotlin, `minSdk 24` (Android 7.0), `compileSdk`/`targetSdk` 36. Una sola
schermata: la pagina del sito **luxverse.c22.eu**, a schermo intero.

L'app non è una copia del sito e non tiene pagine sue, a parte la schermata di
attesa e quella di cortesia quando manca la rete. Quello che si vede dentro è
la pagina vera, sempre aggiornata: se il sito cambia, l'app cambia con lui,
senza pubblicare una versione nuova.

| File | Cosa fa |
|---|---|
| `app/src/main/AndroidManifest.xml` | tutto quello che l'app chiede al telefono: due permessi, elencati qui sotto |
| `app/src/main/java/org/luxverse/app/ui/MainActivity.kt` | la schermata unica: WebView, attesa, pagina di cortesia, collegamenti esterni, salvataggio dei file, ponte `window.LuxVerse` |
| `app/src/main/res/layout/activity_main.xml` | i tre strati: la pagina, il velo di attesa, la pagina di cortesia |
| `app/src/main/res/values*/strings.xml` | le poche frasi dell'app, in italiano e in inglese |
| `app/src/main/res/mipmap-*/` | le icone, rigenerate con `tools/make-icons.ps1` dal disegno ufficiale |
| `tools/make-icons.ps1` | rifà tutte le icone dai file del marchio in `site/assets/images/` |

## Cosa chiede al telefono, e cosa no

| Permesso | Perché |
|---|---|
| `INTERNET` | aprire le pagine del sito |
| `ACCESS_NETWORK_STATE` | distinguere "manca la rete" da "il sito non risponde" |
| `WRITE_EXTERNAL_STORAGE` (solo fino ad Android 9) | mettere nella cartella Download un file scaricato dal sito. Da Android 10 lo fa il sistema e il permesso non viene chiesto (`maxSdkVersion="28"`) |

Non ci sono: posizione, fotocamera, microfono, contatti, notifiche, telefono,
lettura dei file. La pagina non può nemmeno aprire file del telefono
(`allowFileAccess = false`), non può uscire da HTTPS (`usesCleartextTraffic =
false`, `MIXED_CONTENT_NEVER_ALLOW`) e non riceve cookie di terze parti.

Dentro l'APK non c'è nessuna chiave, nessun token e nessuna password: il file
è pubblico e chiunque può aprirlo. Le chiavi delle API stanno solo sul server.

## Come si compila

Serve **JDK 17** e l'**Android SDK** (platform 36, build-tools 36). Poi, da
questa cartella:

```bash
./gradlew assembleDebug      # APK di prova, si installa accanto all'app vera
./gradlew assembleRelease    # APK da pubblicare (firmato, se c'è la chiave)
```

L'APK esce in `app/build/outputs/apk/release/`.

### Firmare

In questa cartella, `keystore.properties` (che **non** va nel repository):

```properties
storeFile=luxverse-release.jks
storePassword=…
keyAlias=luxverse
keyPassword=…
```

Senza quel file la compilazione riesce lo stesso e l'APK resta **non
firmato**: è così che lo compila il servizio di F-Droid, che poi lo firma con
la propria chiave (vedi [COMPILARE-E-FIRMARE.md](COMPILARE-E-FIRMARE.md)).

La chiave con cui firmiamo noi **non è pubblica**, e non può esserlo: Android
riconosce un'app dalla firma, quindi un APK compilato da te non può sostituire
quello ufficiale (Android accetta solo la stessa firma). Per provare una tua
compilazione, disinstalla prima l'app ufficiale — oppure usa l'APK di prova
(`.debug`), che ha un nome diverso e convive con l'altra.

## Due cose da sapere prima di toccare la WebView

1. **L'attesa non è pigrizia.** L'hosting consegna prima una pagina dal corpo
   vuoto che esegue uno script e ricarica da sola. Per questo l'app non
   considera "caricata" la fine di una pagina: aspetta che nel corpo ci sia
   davvero del testo (`lookForText()`), e nel frattempo tiene la schermata di
   attesa. Togliendo quel controllo torna la schermata nera.
2. **I collegamenti esterni escono dall'app.** Restano dentro solo le pagine
   di `luxverse.c22.eu`: GitHub, VirusTotal e simili si aprono nel browser del
   telefono. Così l'app non diventa un browser, e una pagina di terzi non può
   parlare con il ponte `window.LuxVerse`. Se un giorno servisse tenere dentro
   un altro indirizzo, si aggiunge in `handleUrl()`, con una riga di commento
   che spiega perché.

L'indirizzo del sito è in `app/build.gradle.kts` (`SITE_URL` e `START_URL`):
si cambia solo lì.

## La versione dell'app e quella del sito

`versionCode` e `versionName` stanno in `app/build.gradle.kts`; gli stessi
numeri, per la pagina dell'app sul sito, stanno in `site/config.php`
(`LUX_APP_VERSION`, `LUX_APP_VERSION_CODE`). Quando si pubblica una versione
nuova si aggiornano tutti e due, altrimenti la pagina del sito annuncia un
file che non è quello scaricabile.
