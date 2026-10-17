# Compilare, firmare e far controllare l'app — gratis, e senza raccontare favole

Questa pagina risponde a tre domande: **chi può compilare l'app al posto nostro**,
**chi può firmarla** e **chi può "certificarla"** — gratis e legalmente. In mezzo
ci sono cose che si sentono dire e che non esistono: sono scritte in fondo, per
non farsele raccontare da nessuno.

## 1. La firma: chi la fa, e perché non può farla un altro

Un APK che non è firmato **non si installa**: Android lo rifiuta. Ma la firma
di un'app Android **non è un certificato di un'autorità** (non c'è nessuna
"Camera di commercio dei certificati" per le app): è un certificato
**autofirmato** che identifica chi pubblica. Google lo dice senza giri di
parole: *"il certificato non deve essere firmato da un'autorità di
certificazione"* ([Android: definire i permessi](https://developer.android.com/guide/topics/permissions/defining)).

Conseguenza pratica, la più importante di tutte: **la firma È l'app**. Android
riconosce un'app dal certificato. Se un'altra azienda o associazione firmasse
l'app al posto nostro, terrebbe in mano l'aggiornamento dell'app e noi non
potremmo più pubblicarne uno: chi ha installato la nostra versione rifiuterebbe
la successiva firmata da altri. Quindi "farsi firmare l'app da un'azienda
gratis" non è una cosa che conviene, e infatti nessuno la fa.

La firma giusta è la nostra, fatta sulla nostra macchina, con la chiave nuova
generata il **16/10/2026**:

| Cosa | Valore |
|---|---|
| Alias | `luxverse` |
| Algoritmo | RSA 4096, SHA256withRSA |
| Impronta SHA-256 del certificato | `27:67:A1:41:D4:0B:BD:D0:E0:37:64:64:9E:86:6B:E6:0A:7E:25:7E:1F:B4:59:C7:CE:CF:7F:1F:52:FB:3B:14` |
| Scadenza | 10.950 giorni (circa 30 anni) |
| Dove sta | `app/luxverse-release.jks` + `app/keystore.properties` — **solo sulla macchina**, mai nel repository |
| Firme dentro l'APK | v1 (Android 6 e precedenti), v2, v3 (Android 7 e successivi, con possibilità di cambiare chiave in futuro) |

**Da fare adesso, una volta sola:** copia `luxverse-release.jks` e
`keystore.properties` su una chiavetta (o in un gestore di password). Se si
perdono insieme, l'app non può più essere aggiornata: si può solo pubblicare
un'app nuova, con un nome nuovo, e chiedere a tutti di reinstallarla.

## 2. Chi può compilare al posto nostro, gratis

| Chi compila | Chi firma | Costo | Cosa serve |
|---|---|---|---|
| **La nostra macchina** | noi, con la nostra chiave | zero | JDK 17 + Android SDK (vedi [README.md](README.md)) |
| **GitHub Actions** (le macchine di GitHub, che è di Microsoft) | — (compila e basta) | zero sui repository pubblici | il flusso è già in `.github/workflows/build-apk.yml`: a ogni modifica compila l'app e allega l'APK di prova |
| **F-Droid** (l'ente no profit che tiene il negozio di software libero) | **F-Droid**, con la *sua* chiave | zero | il codice è già pubblico e libero (MIT): manca solo la richiesta di inserimento |

Il caso interessante è il terzo, perché è **l'unico in cui un'organizzazione
compila e firma davvero l'app al posto nostro, gratis**: F-Droid prende il
codice dal repository, lo compila sui propri server e firma l'APK con la
propria chiave ([procedura ufficiale](https://f-droid.org/en/docs/Submitting_to_F-Droid_Quick_Start_Guide/)).
Chi installa dal loro negozio riceve un file compilato da loro, non da noi: è
una garanzia in più per chi non si fida.

Cosa pretende, e cosa abbiamo già:

| Regola di F-Droid | Noi |
|---|---|
| Licenza libera, codice pubblico | MIT, repository pubblico ✓ |
| Niente Firebase, niente Play Services, niente librerie di tracciamento o pubblicità | nessuna: l'app usa solo `androidx` (Apache 2.0) ✓ |
| Il pacchetto deve compilarsi dai sorgenti con gli attrezzi dichiarati | `./gradlew assembleRelease`, JDK 17, SDK 36 ✓ |
| Ascoltatori di rete, pubblicità, tracciamento: da dichiarare | non ce ne sono ✓ |
| *"The application should provide native features and enhancements when it wraps a website"* ([regole di ammissione](https://f-droid.org/en/docs/Inclusion_Policy/)) | l'app aggiunge schermata di attesa, pagina di cortesia quando manca la rete, apertura dei collegamenti esterni nel browser, salvataggio dei file, protezione dalle pagine pericolose, adattamento al tema scuro, niente cookie di terze parti, nomi dei permessi spiegati |
| Release con tag riconoscibili | da fare alla prima release (§4) |

**Avviso onesto:** un'app che è "una finestra su un sito" è il caso più
discutibile per F-Droid. Le regole non la vietano, ma chiedono che aggiunga
qualcosa di nativo, e chi decide è una persona che legge la richiesta. Se la
richiesta venisse respinta, il motivo sarà quello, e la strada resta comunque
aperta: la release su GitHub (che è nostra e non dipende da nessuno) e i
negozi alternativi che accettano l'APK firmato da noi.

## 3. "Farla certificare": cosa esiste davvero

Non esiste un ente che certifichi le app Android. Esistono quattro controlli
veri, tutti gratuiti, e sono quelli che si possono mostrare a chi legge:

1. **La compilazione di F-Droid** (§2): il file lo fanno loro, dai sorgenti
   pubblici. È il controllo più forte che un utente può avere, perché non si
   basa sulla nostra parola.
2. **Il controllo di VirusTotal**: il file viene analizzato da decine di
   antivirus diversi e il rapporto è pubblico. Il link sta nella pagina dell'app
   sul sito (`site/app.php`) e qui in fondo.
3. **L'impronta SHA-256**: chiunque può confrontare il file scaricato con
   l'impronta pubblicata. Se combaciano, è lo stesso file.
4. **La verifica dello sviluppatore di Google** (Android Developer
   Verification): qui sotto, perché è l'unico caso in cui serve pagare.

## 4. Google: quanto costa farsi "riconoscere", e quando serve

Dal **30 settembre 2026** Google ha cominciato a pretendere che chi pubblica
app sia verificato, ma solo in **Brasile, Indonesia, Singapore e Thailandia**,
e solo per gli app store che hanno aderito (Google Play, Galaxy Store, App
Market di OPPO, GetApps di Xiaomi e pochi altri). L'Italia **non è ancora
toccata**; l'estensione al mondo intero è annunciata per il **2027**.

- Le installazioni dirette (un APK scaricato da un sito o da GitHub) e i
  negozi che non hanno aderito **non sono toccati** in questa fase.
- Il **conto a distribuzione limitata** è **gratuito**, non chiede documenti
  d'identità e serve a far installare l'app a un massimo di **20 dispositivi
  indicati per nome**: è la via giusta per provare l'app su telefoni nostri.
- Il **conto completo** (per distribuirlo a tutti, se un giorno servisse) costa
  **25 $ una volta sola** e chiede un documento d'identità; per un'impresa o
  un'associazione serve anche il numero **D-U-N-S**, gratuito presso Dun &
  Bradstreet.

Fonti: [annuncio della verifica](https://developer.android.com/blog/posts/android-developer-verification-building-a-safer-ecosystem-together)
· [domande frequenti](https://developer.android.com/developer-verification/guides/faq)
· [conto limitato](https://developer.android.com/developer-verification/guides/limited-distribution).

**Google Play**: resta la via più costosa e più lenta — 25 $ una volta sola, ma
per i conti personali servono **12 provatori per 14 giorni consecutivi** prima
di poter chiedere la pubblicazione ([regole](https://support.google.com/googleplay/android-developer/answer/14151465?hl=en)).
Non è la strada da prendere per cominciare.

## 5. Le cose false, scritte una volta per tutte

- ❌ *"Esiste un'azienda che certifica le app Android."* Non esiste nessuna
  autorità che firmi o certifichi gli APK. Le firme sono autofirmate.
- ❌ *"Un'azienda può firmare la nostra app per garantirla."* Può, ma da quel
  momento quella azienda possiede l'app: gli aggiornamenti passano da lei.
- ❌ *"Serve un certificato di firma del codice (quello con la chiavetta USB)."*
  Quelli servono per i programmi di Windows e per alcuni file Java, **non** per
  Android: sarebbe solo un costo inutile.
- ❌ *"Su Google Play si pubblica gratis."* 25 $ una volta sola, e con i conti
  personali 12 provatori per 14 giorni.
- ❌ *"Un'app firmata è un'app sicura."* La firma dice **chi** l'ha pubblicata,
  non che sia sicura. Per la sicurezza contano il codice leggibile, la
  compilazione indipendente di F-Droid e i controlli di VirusTotal.
