# Segnalare un problema di sicurezza

Questo progetto è pubblico e gratuito, senza account e senza database: chi lo usa non ha
quasi niente da perdere. Resta comunque il dovere di trattare con cura quello che c'è.

## Come segnalare

Scrivi in privato a **administration.luxverse@gmail.com** con oggetto
`[SICUREZZA]` e, se puoi, spiega:

- cosa hai trovato e su quale indirizzo (per esempio `luxverse.c22.eu/api/api.php`);
- come si riproduce, un passo per passo, con la richiesta o lo script usato;
- cosa si riesce a ottenere (leggere dati di altri, entrare nell'amministrazione,
  bloccare il sito, eseguire codice);
- quanto è grave secondo te, e se pensi che sia già sfruttato da qualcuno.

Non aprire una issue pubblica prima che il problema sia risolto: una issue pubblica è un
manuale per chi vuole approfittarne.

## Cosa ci aspettiamo, e cosa chiediamo

Prova il meno possibile: **sul sito vero non leggere, non modificare e non cancellare
dati di altre persone**, non caricare niente di malevolo e non fare test di carico. Se un
test richiede di toccare dati reali, fermati e scrivici: mettiamo a disposizione una copia
locale del progetto (in questo stesso repository) su cui provare tutto.

Ti risponderemo appena possibile, ti diremo cosa abbiamo capito e cosa abbiamo cambiato, e
se vuoi ti citiamo nel messaggio del rilascio che chiude il problema.

## Cosa non è un problema di sicurezza

- **La verifica anti-bot dell'hosting.** Il sito sta su un hosting gratuito che mette
  davanti a ogni pagina una verifica in JavaScript (i file `aes.js` e il cookie `__test`
  non sono nostri: sono dell'hosting). Non è codice del progetto, non è disattivabile dal
  piano gratuito e non si "buca": semplicemente il server risponde solo ai client che
  eseguono quello script.
- **L'APK senza firma di Google Play.** L'app non passa dal Play Store, quindi Android
  può chiedere una scansione: l'APK è firmato e la firma è verificabile (`CN=WorkMeAI`).
- **L'assenza di autenticazione sulle funzioni pubbliche.** Il sito è nato senza account:
  non c'è niente da proteggere se non il pannello di amministrazione e la chiave delle API
  AI, che stanno fuori dal codice pubblico (vedi il README).
