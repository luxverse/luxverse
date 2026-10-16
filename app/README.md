# L'app Android

Kotlin, minSdk 24 (Android 8), compileSdk 36. Non è una copia del sito: è una **WebView a
schermo intero** che apre il sito live, più la schermata di avvio, il controllo degli
aggiornamenti e una pagina di riserva quando manca la rete.

| File | Cosa fa |
|---|---|
| `ui/MainActivity.kt` | la WebView: apertura del sito, verifica anti-bot dell'hosting, aggiornamenti, pagina di riserva |
| `ui/SplashActivity.kt` | la schermata di avvio, subito seguita dall'app |
| `net/Api.kt` | le chiamate al sito per gli schermi che non passano dalla WebView |
| `assets/www/offline.html` | l'unica pagina locale: si apre solo se il sito non risponde |

## Compilare

Servono JDK 17 e l'Android SDK.

```bash
./gradlew assembleDebug     # APK di prova
./gradlew assembleRelease   # APK da pubblicare
```

Per firmarlo, crea `keystore.properties` in questa cartella (è in `.gitignore`):

```properties
storeFile=../la-tua-chiave.jks
storePassword=...
keyAlias=...
keyPassword=...
```

Senza quel file la compilazione riesce lo stesso e l'APK resta non firmato. La chiave con
cui firmiamo noi non è pubblica: un APK compilato da te non può sostituire quello
installato dall'app ufficiale (Android accetta solo la stessa firma), quindi per provarlo
disinstalla prima l'app ufficiale.

## Due cose da sapere prima di toccare la WebView

1. **La verifica anti-bot dell'hosting.** Il sito consegna prima una pagina dal corpo
   vuoto che esegue uno script e ricarica. Per questo l'app non considera "caricata" la
   fine di una pagina: aspetta che nel corpo ci sia davvero del testo, e nel frattempo
   tiene la schermata di avvio. Togliendo quel controllo torna la schermata nera.
2. **Le chiamate dirette al sito non funzionano.** La verifica risponde in HTML anche alle
   API, quindi la versione dell'app si chiede con una `fetch` dentro la WebView (ponte
   `LuxBridge`), non con una chiamata HTTP separata. Se aggiungi una funzione che deve
   parlare con il sito, falla passare di lì.

L'indirizzo del sito è in `app/build.gradle.kts` (`SITE_URL` e `APP_URL`): cambialo se
punti a un sito tuo. Nessuna chiave sta dentro il pacchetto: l'APK è pubblico e chiunque
può aprirlo, quindi le chiavi restano sul server.
