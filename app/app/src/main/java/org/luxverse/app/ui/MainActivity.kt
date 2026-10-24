package org.luxverse.app.ui

import android.Manifest
import android.annotation.SuppressLint
import android.app.AlertDialog
import android.app.DownloadManager
import android.content.ActivityNotFoundException
import android.content.Context
import android.content.Intent
import android.content.pm.PackageManager
import android.graphics.Bitmap
import android.net.ConnectivityManager
import android.net.NetworkCapabilities
import android.net.Uri
import android.os.Build
import android.os.Bundle
import android.os.Environment
import android.os.Handler
import android.os.Looper
import android.view.View
import android.view.ViewGroup
import android.webkit.CookieManager
import android.webkit.JavascriptInterface
import android.webkit.RenderProcessGoneDetail
import android.webkit.SslErrorHandler
import android.webkit.URLUtil
import android.webkit.WebChromeClient
import android.webkit.WebResourceError
import android.webkit.WebResourceRequest
import android.webkit.WebResourceResponse
import android.webkit.WebSettings
import android.webkit.WebView
import android.webkit.WebViewClient
import android.widget.Button
import android.widget.ImageButton
import java.net.HttpURLConnection
import java.net.URL
import javax.crypto.Cipher
import javax.crypto.spec.IvParameterSpec
import javax.crypto.spec.SecretKeySpec
import android.net.http.SslError
import android.widget.ProgressBar
import android.widget.TextView
import android.widget.Toast
import androidx.activity.OnBackPressedCallback
import androidx.appcompat.app.AppCompatActivity
import androidx.core.content.ContextCompat
import androidx.core.view.ViewCompat
import androidx.core.view.WindowCompat
import androidx.core.view.WindowInsetsCompat
import androidx.core.view.WindowInsetsControllerCompat
import androidx.webkit.WebSettingsCompat
import androidx.webkit.WebViewFeature
import org.luxverse.app.BuildConfig
import org.luxverse.app.R
import org.json.JSONObject
import org.luxverse.app.net.SiteTls
import java.util.ArrayDeque
import javax.net.ssl.HttpsURLConnection

/**
 * L'unica schermata dell'app: una finestra sul sito.
 *
 * Non e' una copia del sito e non ha pagine proprie, a parte la schermata di
 * attesa e quella di cortesia quando la rete manca. Tutto quello che si vede
 * dentro e' la pagina vera, aggiornata: se il sito cambia, l'app cambia con
 * lui, senza dover pubblicare una versione nuova.
 *
 * Le quattro cose che questo file fa, e perche':
 *
 * 1. ASPETTA CHE LA PAGINA CI SIA DAVVERO. L'hosting consegna prima una
 *    pagina dal corpo vuoto che esegue uno script e ricarica da sola. Per
 *    questo "la pagina e' finita di caricare" non vuol dire "c'e' qualcosa da
 *    leggere": si controlla che nel corpo ci sia del testo, e finche' non ce
 *    n'e' si tiene la schermata di attesa. Senza questo controllo si vede una
 *    schermata nera.
 *
 * 2. I COLLEGAMENTI VERSO ALTRI SITI ESCONO DALL'APP. Restano dentro solo le
 *    pagine di luxverse.c22.eu; GitHub, VirusTotal e simili si aprono nel
 *    browser del telefono. Cosi' l'app non diventa un browser, e una pagina
 *    di terzi non puo' parlare con le funzioni dell'app.
 *
 * 3. I FILE SCARICATI VANNO NELLA CARTELLA DOWNLOAD, con il salvataggio di
 *    sistema (si vede nella tendina delle notifiche, si puo' annullare).
 *
 * 4. NIENTE PERMESSI INUTILI: solo internet e lo stato della rete. Non si
 *    legge niente del telefono: nessuna posizione, nessun contatto, nessuna
 *    fotocamera, e la pagina non puo' aprire file locali (allowFileAccess).
 *
 * 6. LA VERIFICA DELL'HOSTING SI SUPERA IN LOCALE (18/10/2026, 39.3.2).
 *    Davanti a ogni pagina del sito l'hosting mette una paginetta di 858 byte
 *    che calcola un biscotto (__test) con AES e poi ricarica. Senza quel
 *    biscotto il server NON serve il sito: al suo posto consegna la stessa
 *    paginetta al posto di ogni pagina, di ogni foglio di stile e di ogni
 *    script (verificato: /assets/js/lux-icons.js senza biscotto arrivava come
 *    paginetta di verifica, 859 byte, tipo text/html). Per questo l'app
 *    restava vuota fino al "sito non risponde": non era il sito a non
 *    rispondere, era la verifica che rispondeva al posto suo.
 *
 *    Adesso l'app fa quella verifica da sola, senza dipendere dal JavaScript
 *    della pagina: chiede una volta il sito, legge i tre valori esadecimali
 *    della verifica, li decifra con AES-128 in locale e scrive il biscotto
 *    __test nella riserva dei biscotti della WebView. Da quel momento la
 *    WebView chiede le pagine e i file come un browser gia' verificato.
 *    Tutto avviene su un thread separato: l'interfaccia non aspetta mai.
 *    Se qualcosa non riesce si carica comunque la pagina (comportamento di
 *    prima), quindi il peggio che puo' succedere e' tornare al passato.
 *
 * 5. NIENTE DOMANDE ALLA PAGINA (18/10/2026, 39.3.1). Fino alla 39.3.0, a
 *    ogni pagina caricata l'app chiedeva al contenuto, in JavaScript, se ci
 *    fosse del testo da leggere, e lo richiedeva ogni 0,35 s fino a 16
 *    volte. Se la pagina e' occupata — e la pagina di verifica dell'hosting lo
 *    e' — ogni domanda aspetta la risposta, la finestra dell'app non risponde
 *    piu' e Android mostra "L.U.X. non risponde" e la chiude. Ora la pagina
 *    non viene interrogata mai: si guarda soltanto il titolo (ogni pagina del
 *    sito ne ha uno, la verifica dell'hosting no) e il momento in cui la
 *    pagina disegna. In piu' l'app si presenta al sito con il nome di un
 *    browser normale, senza i segni di "finestra dentro un'app": era l'altro
 *    motivo per cui il telefono restava fermo sulla verifica.
 * 7. IL CERTIFICATO SI COMPLETA E SI VERIFICA (19/10/2026, 39.3.3).
 *    L'hosting consegna il certificato del sito senza la catena che porta a
 *    una radice riconosciuta dai telefoni, e Android non va a prendersi i
 *    pezzi mancanti da solo. Chrome si'. Adesso l'app fa lo stesso: segue gli
 *    indirizzi scritti dentro il certificato, scarica gli anelli mancanti e
 *    li fa verificare alle autorita' del TELEFONO; solo se il telefono dice
 *    si' la connessione prosegue. Nessun certificato e' scritto dentro l'app
 *    e nessuna scorciatoia e' stata presa: se la verifica non riesce, l'app
 *    si ferma dicendo che il collegamento non e' sicuro (vedi SiteTls.kt).
 */
class MainActivity : AppCompatActivity() {

    private lateinit var web: WebView
    private lateinit var backBtn: ImageButton
    private lateinit var progress: ProgressBar
    private lateinit var veil: View
    private lateinit var veilProgress: ProgressBar
    private lateinit var veilStatus: TextView
    private lateinit var offline: View
    private lateinit var offlineTitle: TextView
    private lateinit var offlineText: TextView

    /** Il sito: tutte le pagine dello stesso indirizzo restano dentro l'app. */
    private val siteHost: String by lazy { Uri.parse(BuildConfig.SITE_URL).host ?: "" }

    /** Vero quando una pagina vera e' stata mostrata: da li' in poi niente attese. */
    private var settled = false

    /**
     * Le attese: tre sveglie, nessuna domanda alla pagina.
     *
     * hideVeilWatch alza il velo poco dopo che la pagina ha disegnato e ha un
     * titolo; slowWatch cambia la scritta se la rete e' lenta; giveUpWatch,
     * dopo venticinque secondi, mostra la pagina di cortesia. Si possono
     * sempre annullare, quindi non restano appese da una pagina all'altra.
     */
    private val ui = Handler(Looper.getMainLooper())
    private var hideVeilWatch: Runnable? = null
    private var slowWatch: Runnable? = null
    private var accessWatch: Runnable? = null
    private var giveUpWatch: Runnable? = null

    /** La pagina in corso ha un titolo vero? La pagina di verifica non ce l'ha. */
    private var titleSeen = false

    /** Il motore della pagina si e' gia' spento una volta: si riparte, ma solo una. */
    private var rendererGone = false

    /** La richiesta d'emergenza al sito e' gia' stata riprovata da sola una volta. */
    private var autoRetried = false

    /** La verifica dell'hosting e' gia' stata rifatta una volta in questa attesa. */
    private var accessRetried = false

    /** Il controllo degli aggiornamenti si fa una volta sola per apertura. */
    private var updateChecked = false

    /** Il lavoro che prepara il biscotto della verifica (fuori dall'interfaccia). */
    private var accessThread: Thread? = null

    /**
     * IL CERTIFICATO (19/10/2026, 39.3.3).
     *
     * L'hosting consegna il certificato del sito senza la catena che porta a
     * una radice riconosciuta, e Android non va a prendersi i pezzi mancanti
     * da solo (Chrome si'). Quando la WebView si ferma per questo, qui non si
     * aggira niente: si completa la catena con i pezzi che il certificato
     * stesso dichiara e la si fa verificare al telefono (SiteTls). Se il
     * telefono dice si', la connessione prosegue; se dice no, si mostra la
     * pagina di cortesia. tlsVerdict tiene l'esito perche' ogni immagine, CSS
     * e JS passa di qui: la prima richiesta paga il controllo, le altre no.
     */
    private val tlsWaiters = ArrayDeque<SslErrorHandler>()
    private var tlsChecking = false
    private var tlsVerdict: Boolean? = null
    private var tlsTimer: Runnable? = null

    /** Serve a distinguere "premi indietro due volte per uscire". */
    private var lastBackPress = 0L

    /** Il salvataggio in attesa del permesso (solo Android 9 e precedenti). */
    private var pendingUrl: String? = null
    private var pendingAgent: String? = null
    private var pendingDisposition: String? = null
    private var pendingMime: String? = null

    @SuppressLint("SetJavaScriptEnabled")
    override fun onCreate(savedInstanceState: Bundle?) {
        super.onCreate(savedInstanceState)
        setContentView(R.layout.activity_main)

        setUpWindow()

        web = findViewById(R.id.web)
        backBtn = findViewById(R.id.backBtn)
        progress = findViewById(R.id.progress)
        veil = findViewById(R.id.veil)
        veilProgress = findViewById(R.id.veilProgress)
        veilStatus = findViewById(R.id.veilStatus)
        offline = findViewById(R.id.offline)
        offlineTitle = findViewById(R.id.offlineTitle)
        offlineText = findViewById(R.id.offlineText)

        prepareWebView()

        findViewById<Button>(R.id.retry).setOnClickListener { retry() }
        findViewById<Button>(R.id.openInBrowser).setOnClickListener { openOutside(BuildConfig.SITE_URL) }

        // Il bottone indietro (24/10/2026). Fa la stessa cosa del gesto di
        // sistema, ma si vede: dentro l'app non c'e' la barra del browser, e
        // senza un comando a schermo l'unico modo per tornare era il gesto.
        // Compare solo quando c'e' davvero una pagina precedente.
        backBtn.setOnClickListener {
            if (web.canGoBack()) web.goBack()
        }

        // Il tasto indietro: prima le pagine visitate, poi l'uscita.
        onBackPressedDispatcher.addCallback(this, object : OnBackPressedCallback(true) {
            override fun handleOnBackPressed() {
                if (web.canGoBack()) {
                    web.goBack()
                } else if (System.currentTimeMillis() - lastBackPress < 2000) {
                    finish()
                } else {
                    lastBackPress = System.currentTimeMillis()
                    Toast.makeText(this@MainActivity, R.string.exit_hint, Toast.LENGTH_SHORT).show()
                }
            }
        })

        if (savedInstanceState != null) {
            // Ritorno dopo che Android ha ricreato la schermata: si riprende
            // da dove si era, senza ricaricare tutto.
            settled = true
            hideVeil()
            web.restoreState(savedInstanceState)
        } else {
            startWatches()
            openSite()
        }
    }

    // ========================================================
    // AGGIORNAMENTI
    // ========================================================
    /**
     * Chiede al sito qual e' l'ultima versione dell'app.
     *
     * La pagina dell'app promette che "l'app stessa avvisa" quando esce una
     * versione nuova: fino alla 39.3.5 la promessa non era mantenuta, perche'
     * nessuna riga dell'app chiamava questo indirizzo. Si chiama una volta per
     * apertura, su un thread separato e con i tempi stretti (5 secondi): se la
     * rete non risponde, o il JSON non e' quello atteso, non succede niente e
     * l'app resta quella che e'. Non e' un controllo che blocca l'apertura.
     *
     * Si avvisa una volta sola per versione (SharedPreferences): chi sceglie
     * "dopo" non se lo ritrova davanti a ogni avvio.
     */
    private fun checkForUpdate() {
        val lavoro = Thread {
            var connessione: HttpsURLConnection? = null
            try {
                val indirizzo = BuildConfig.SITE_URL + "/api/latest_version.php"
                connessione = (URL(indirizzo).openConnection() as HttpsURLConnection).apply {
                    connectTimeout = 5_000
                    readTimeout = 5_000
                    setRequestProperty("Accept", "application/json")
                    // Il biscotto della verifica dell'hosting va mandato a mano:
                    // HttpURLConnection non usa la riserva di biscotti della
                    // WebView, e senza __test il server risponde con la
                    // paginetta di verifica invece del JSON.
                    val biscotto = CookieManager.getInstance()
                        .getCookie(BuildConfig.SITE_URL).orEmpty()
                    if (biscotto.isNotEmpty()) setRequestProperty("Cookie", biscotto)
                    SiteTls.fabbrica(siteHost)?.let { sslSocketFactory = it }
                }
                val testo = connessione.inputStream.bufferedReader(Charsets.UTF_8).use { it.readText() }
                val json = JSONObject(testo)
                val codice = json.optInt("version_code", 0)
                val nome = json.optString("version_name", "")
                val dove = json.optString("url", "")
                if (codice <= BuildConfig.VERSION_CODE) return@Thread
                if (!dove.startsWith("https://")) return@Thread

                val gia = getSharedPreferences("luxverse", MODE_PRIVATE).getInt("update_prompted", 0)
                if (gia >= codice) return@Thread

                ui.post {
                    if (isFinishing || isDestroyed) return@post
                    getSharedPreferences("luxverse", MODE_PRIVATE).edit()
                        .putInt("update_prompted", codice).apply()
                    AlertDialog.Builder(this)
                        .setTitle(R.string.update_title)
                        .setMessage(getString(R.string.update_text, if (nome.isNotEmpty()) nome else codice.toString()))
                        .setPositiveButton(R.string.update_now) { _, _ -> openOutside(dove) }
                        .setNegativeButton(R.string.update_later, null)
                        .show()
                }
            } catch (t: Throwable) {
                // Nessuna rete, nessun JSON, hosting che risponde altro: si
                // tace. Un avviso di aggiornamento non deve mai diventare un
                // errore da mostrare.
            } finally {
                connessione?.disconnect()
            }
        }
        lavoro.isDaemon = true
        lavoro.start()
    }

    // ========================================================
    // SCHERMO INTERO E TASTIERA
    // ========================================================
    // 1. LA BARRA DI NAVIGAZIONE DEL TELEFONO SPARISCE. La striscia con
    //    indietro / Home / recenti (o la barretta dei gesti) sta sopra il
    //    sito e si mangia una fascia di schermo: dentro l'app la pagina
    //    prende tutto lo spazio, e la barra si ripresenta solo se la si
    //    tira su con un dito (BEHAVIOR_SHOW_TRANSIENT_BARS_BY_SWIPE). La
    //    barra di stato in alto resta: l'ora e la batteria servono.
    //
    // 2. LA TASTIERA NON COPRE IL CAMPO. Da Android 15 il contenuto disegna
    //    anche sotto le barre di sistema, e il vecchio "adjustResize" non
    //    accorcia piu' la finestra: la tastiera si apriva SOPRA la pagina,
    //    e il campo in scrittura finiva sotto i tasti. Qui l'altezza della
    //    tastiera si legge come "inset" e diventa spazio in fondo: la
    //    WebView si accorcia davvero, quindi la pagina si accorge di avere
    //    meno spazio e riporta in vista il campo che si sta scrivendo
    //    (il movimento lo fa il sito, in assets/js/site.js).
    //
    // 3. La fascia in alto vale l'altezza della barra di stato: il sito non
    //    finisce sotto l'orologio, esattamente come prima.
    private fun setUpWindow() {
        hideNavigationBar()

        // Su Android 11 in giu' la finestra si accorcia da sola quando la
        // tastiera compare (adjustResize nel manifesto), e le barre di
        // sistema non coprono il contenuto: li' non si tocca niente. Da
        // Android 11 in poi il contenuto disegna sotto le barre e l'altezza
        // della tastiera va letta e applicata qui.
        if (Build.VERSION.SDK_INT < Build.VERSION_CODES.R) return
        WindowCompat.setDecorFitsSystemWindows(window, false)

        val root = findViewById<View>(R.id.root)
        ViewCompat.setOnApplyWindowInsetsListener(root) { view, insets ->
            val bars = insets.getInsets(WindowInsetsCompat.Type.systemBars())
            val ime = insets.getInsets(WindowInsetsCompat.Type.ime())
            view.setPadding(bars.left, bars.top, bars.right, maxOf(bars.bottom, ime.bottom))
            insets
        }
        ViewCompat.requestApplyInsets(root)
    }

    /** Nasconde la barra di navigazione, lasciando il gesto per farla tornare. */
    private fun hideNavigationBar() {
        val controller = WindowInsetsControllerCompat(window, window.decorView)
        controller.systemBarsBehavior =
            WindowInsetsControllerCompat.BEHAVIOR_SHOW_TRANSIENT_BARS_BY_SWIPE
        controller.hide(WindowInsetsCompat.Type.navigationBars())
    }

    /**
     * Tornando sull'app (per esempio dopo aver guardato un'altra schermata)
     * Android rimette la barra di navigazione: si nasconde di nuovo, senza
     * che chi usa l'app debba fare niente.
     */
    override fun onWindowFocusChanged(hasFocus: Boolean) {
        super.onWindowFocusChanged(hasFocus)
        if (hasFocus) hideNavigationBar()
    }

    // ========================================================
    // Impostazioni della WebView
    // ========================================================
    private fun prepareWebView() {
        web.setBackgroundColor(ContextCompat.getColor(this, R.color.lux_background))

        val s = web.settings
        s.javaScriptEnabled = true            // il sito e' fatto di JavaScript
        s.domStorageEnabled = true            // lingua e scelta sui cookie restano salvate
        s.allowFileAccess = false             // una pagina non legge i file del telefono
        s.allowContentAccess = false          // ne' le foto, ne' i documenti
        s.setGeolocationEnabled(false)        // la posizione non si chiede mai
        s.mixedContentMode = WebSettings.MIXED_CONTENT_NEVER_ALLOW   // solo HTTPS
        s.cacheMode = WebSettings.LOAD_DEFAULT
        s.textZoom = 100                      // la dimensione del testo la decide il sistema
        s.setSupportZoom(false)               // il sito e' gia' adatto al telefono
        s.setSupportMultipleWindows(false)
        s.userAgentString = browserUserAgent(s.userAgentString)

        // Cookies: solo quelli del sito. Niente cookie di terze parti, che
        // sarebbero il modo piu' comune di seguire le persone fra i siti.
        CookieManager.getInstance().setAcceptCookie(true)
        CookieManager.getInstance().setAcceptThirdPartyCookies(web, false)

        // Due protezioni che il sistema offre e che qui si accendono se ci sono:
        // la segnalazione delle pagine pericolose e l'adattamento del sito al
        // tema scuro del telefono.
        if (WebViewFeature.isFeatureSupported(WebViewFeature.SAFE_BROWSING_ENABLE)) {
            WebSettingsCompat.setSafeBrowsingEnabled(s, true)
        }
        if (WebViewFeature.isFeatureSupported(WebViewFeature.ALGORITHMIC_DARKENING)) {
            WebSettingsCompat.setAlgorithmicDarkeningAllowed(s, true)
        }

        web.webChromeClient = object : WebChromeClient() {
            /**
             * Il titolo e' l'unico segnale che serve per capire se la pagina e'
             * vera, e non costa niente: non si chiede nulla al contenuto, lo
             * passa il sistema. Ogni pagina del sito ha il suo titolo; la
             * pagina di verifica dell'hosting non ne ha (verificato: 854 byte,
             * nessun <title>), quindi il velo resta finche' non arriva una
             * pagina vera.
             */
            override fun onReceivedTitle(view: WebView?, title: String?) {
                val t = title?.trim().orEmpty()
                // Senza <title> Android passa l'indirizzo della pagina: non e'
                // una pagina del sito.
                titleSeen = t.isNotEmpty() && !t.startsWith("http")
                if (titleSeen) scheduleHideVeil()
            }

            override fun onProgressChanged(view: WebView?, newProgress: Int) {
                // Due indicatori, una sola verita': la riga in cima (visibile
                // anche a pagina aperta, poi sparisce) e la linea dentro il
                // velo, che avanza con il caricamento vero. Niente rotelle che
                // girano a vuoto: se la linea e' ferma, la rete e' ferma.
                if (newProgress >= 100) {
                    progress.visibility = View.GONE
                } else {
                    progress.visibility = View.VISIBLE
                    progress.progress = newProgress
                }
                // La riga di stato non si tocca da qui: ha la sua logica
                // ("connessione", poi "rete lenta" dopo 8 s). Prima veniva
                // riscritta a ogni avanzamento e cancellava l'avviso.
                if (veil.visibility == View.VISIBLE) {
                    veilProgress.progress = newProgress
                }
            }
        }

        web.webViewClient = object : WebViewClient() {

            override fun shouldOverrideUrlLoading(view: WebView, request: WebResourceRequest): Boolean {
                return handleUrl(request.url.toString())
            }

            override fun onPageStarted(view: WebView?, url: String?, favicon: Bitmap?) {
                super.onPageStarted(view, url, favicon)
                offline.visibility = View.GONE
                progress.visibility = View.VISIBLE
                // Una pagina che ricomincia e' un tentativo nuovo: se il velo
                // era in procinto di alzarsi, l'attesa si ferma. La pagina di
                // verifica dell'hosting disegna e poi si ricarica: alzare il
                // velo in quel momento mostrerebbe un lampo vuoto.
                cancelHideVeil()
                titleSeen = false
                backBtn.visibility = View.GONE
                if (veil.visibility == View.VISIBLE) {
                    veilStatus.setText(R.string.loading_connecting)
                    veilProgress.progress = 0
                }
            }

            /** La pagina ha disegnato il primo pezzo: se ha anche un titolo, si puo' alzare il velo. */
            override fun onPageCommitVisible(view: WebView?, url: String?) {
                scheduleHideVeil()
            }

            override fun onPageFinished(view: WebView?, url: String?) {
                super.onPageFinished(view, url)
                progress.visibility = View.GONE
                syncBackButton()
                if (!settled) scheduleHideVeil()
            }

            /** La cronologia e' cambiata (avanti/indietro o nuovo indirizzo). */
            override fun doUpdateVisitedHistory(view: WebView?, url: String?, isReload: Boolean) {
                super.doUpdateVisitedHistory(view, url, isReload)
                syncBackButton()
            }

            override fun onReceivedError(view: WebView, request: WebResourceRequest, error: WebResourceError) {
                // Solo l'errore della pagina principale conta: se non carica una
                // singola immagine non si copre tutto con la pagina di cortesia.
                if (!request.isForMainFrame) return
                showOffline()
            }

            override fun onReceivedHttpError(view: WebView, request: WebResourceRequest, response: WebResourceResponse) {
                // Una pagina d'errore del server (403, 404, 500) NON e' una
                // pagina da leggere: senza questo controllo si restava davanti
                // a un rettangolo vuoto. La protezione dell'hosting risponde
                // 403 e un istante dopo serve la pagina vera, quindi la prima
                // volta si riprova da soli, come fa "aggiorna" nel browser; se
                // non basta si mostra la pagina di cortesia.
                if (!request.isForMainFrame) return
                if (response.statusCode < 400) return
                if (!autoRetried) {
                    autoRetried = true
                    ui.postDelayed({ if (!settled && !isFinishing) web.reload() }, SOLO_RETRY)
                    return
                }
                showOffline()
            }

            /**
             * PROBLEMI DI CERTIFICATO: non si passa oltre, mai.
             *
             * Qui non si aggira la sicurezza per far funzionare l'app: se il
             * certificato del sito non si puo' verificare, il telefono non sa
             * con chi sta parlando, e l'app si ferma dicendolo con le parole
             * giuste ("Collegamento non sicuro"), invece di far credere che
             * sia il sito a non rispondere. E' successo davvero il
             * 18/10/2026: l'hosting consegnava il certificato senza la catena
             * che porta a una radice riconosciuta dai telefoni, e le app
             * Android non vanno a prendersi i pezzi mancanti da sole.
             */
            override fun onReceivedSslError(view: WebView?, handler: SslErrorHandler?, error: SslError?) {
                if (handler == null) {
                    showTls()
                    return
                }
                val host = error?.url?.let { Uri.parse(it).host }.orEmpty()
                val pagina = view?.url?.let { Uri.parse(it).host }.orEmpty()
                when {
                    host.isEmpty() || host != siteHost -> {
                        // Un altro sito non si tocca proprio: si rinuncia. E se
                        // era solo una risorsa di terzi dentro la nostra
                        // pagina (un'immagine, un video), non si copre tutto
                        // con la pagina di cortesia: quella risorsa resta
                        // vuota, la pagina si legge lo stesso.
                        handler.cancel()
                        if (host.isNotEmpty() && host == pagina) showTls()
                    }
                    tlsVerdict == true -> handler.proceed()
                    tlsVerdict == false -> {
                        handler.cancel()
                        showTls()
                    }
                    else -> askCertificate(handler)
                }
            }

            /**
             * Il motore che disegna le pagine puo' spegnersi quando la memoria
             * libera finisce (capita sulle pagine pesanti nei telefoni vecchi).
             * Senza una risposta qui il sistema chiude l'app; cosi' invece la
             * schermata si ricostruisce con una finestra nuova, e se succede
             * ancora si dice com'e' andata invece di lasciare il nero.
             */
            override fun onRenderProcessGone(view: WebView?, detail: RenderProcessGoneDetail?): Boolean {
                if (!rendererGone) {
                    rendererGone = true
                    recreate()
                    return true
                }
                showOffline()
                return true
            }
        }

        // I file: si affidano al salvataggio del sistema, che mostra
        // l'avanzamento e li mette nella cartella Download.
        web.setDownloadListener { url, userAgent, contentDisposition, mimeType, _ ->
            if (!url.startsWith("http")) return@setDownloadListener
            val name = URLUtil.guessFileName(url, contentDisposition, mimeType)
            if (Build.VERSION.SDK_INT < Build.VERSION_CODES.Q &&
                ContextCompat.checkSelfPermission(this, Manifest.permission.WRITE_EXTERNAL_STORAGE)
                != PackageManager.PERMISSION_GRANTED
            ) {
                pendingUrl = url
                pendingAgent = userAgent
                pendingDisposition = contentDisposition
                pendingMime = mimeType
                requestPermissions(arrayOf(Manifest.permission.WRITE_EXTERNAL_STORAGE), REQUEST_WRITE)
            } else {
                enqueueDownload(url, userAgent, contentDisposition, mimeType, name)
            }
        }

        // Il ponte fra la pagina e l'app. La pagina puo' chiedere soltanto
        // queste quattro cose: nessuna lettura del telefono, nessun file.
        web.addJavascriptInterface(WebBridge(), "LuxVerse")
    }

    // ========================================================
    // La verifica dell'hosting, fatta in casa
    // ========================================================
    /**
     * Prepara l'accesso e poi apre il sito nella WebView.
     *
     * Tutto il lavoro di rete sta su un thread a parte: l'interfaccia mostra
     * la schermata di attesa e resta sempre viva (nessun blocco possibile).
     */
    private fun openSite(keepUrl: Boolean = false) {
        if (accessThread?.isAlive == true) return

        // Il nome con cui la WebView si presenta si legge QUI, sul thread
        // dell'interfaccia. Le impostazioni della WebView non sono fatte per
        // essere lette da un altro thread: prima la lettura avveniva dentro
        // ensureAccess() (che gira in secondo piano) e poteva fallire, cosi'
        // l'app restava senza il biscotto della verifica e sembrava rotta.
        val ua = web.settings.userAgentString ?: ""
        val lavoro = Thread {
            // Prima il certificato: se la catena si puo' completare, la
            // WebView non avra' nemmeno bisogno di fermarsi.
            SiteTls.verifica(siteHost)
            ensureAccess(ua)
            ui.post {
                if (isFinishing || isDestroyed) return@post
                if (keepUrl && !web.url.isNullOrEmpty()) web.reload() else web.loadUrl(BuildConfig.START_URL)
            }
        }
        lavoro.isDaemon = true
        accessThread = lavoro
        lavoro.start()
    }

    /**
     * IL BISCOTTO DELLA VERIFICA, CALCOLATO QUI.
     *
     * La paginetta che l'hosting mette davanti a ogni pagina contiene tre
     * valori esadecimali: una chiave, un vettore e un blocco cifrato. Il suo
     * script li decifra con AES e scrive il risultato in un biscotto __test,
     * poi ricarica. Qui si fa la stessa cosa con le funzioni di cifratura del
     * telefono: niente JavaScript, niente dipendenza dalla pagina.
     *
     * - se il biscotto c'e' gia' (dura circa due ore) si usa quello;
     * - se il sito risponde gia' con la pagina vera, non serve niente;
     * - altrimenti si legge la verifica, si calcola il biscotto e lo si salva
     *   nella riserva della WebView, cosi' vale anche per immagini, CSS e JS.
     */
    private fun ensureAccess(ua: String): Boolean {
        return try {
            val cm = CookieManager.getInstance()
            cm.setAcceptCookie(true)
            val gia = cm.getCookie(BuildConfig.SITE_URL).orEmpty()
            if (gia.contains("__test=")) return true

            // 1. Domanda leggerissima: la testata dice se la pagina e' quella
            //    vera (text/html; charset=UTF-8) o la verifica (text/html).
            val testa = fetch(BuildConfig.START_URL, true, ua)
            if (testa != null && testa.second.contains("charset", true)) return true

            // 2. La pagina, per esteso: se e' la verifica, si risolve.
            val corpo = fetch(BuildConfig.START_URL, false, ua)?.first ?: return false
            if (!corpo.contains("slowAES")) return true     // nessuna verifica da superare
            val valori = Regex("""toNumbers\("([0-9a-fA-F]{16,})"\)""")
                .findAll(corpo).map { it.groupValues[1] }.take(3).toList()
            if (valori.size < 3) return false               // verifica di forma sconosciuta

            val biscotto = aesCookie(valori[0], valori[1], valori[2]) ?: return false
            cm.setCookie(BuildConfig.SITE_URL, "__test=" + biscotto + "; path=/; max-age=21600")
            cm.flush()
            true
        } catch (t: Throwable) {
            false        // si carica comunque: al massimo si torna al comportamento di prima
        }
    }

    /** Una richiesta al sito: corpo (null se si chiedono solo le testate) e tipo. */
    private fun fetch(url: String, onlyHeaders: Boolean, ua: String): Pair<String?, String>? {
        var c: HttpURLConnection? = null
        return try {
            val connessione = URL(url).openConnection() as HttpURLConnection
            c = connessione
            // Le richieste che fa l'app da sola devono superare lo stesso
            // ostacolo della WebView: la catena incompleta. Con questa
            // fabbrica il telefono verifica lo stesso, ma dopo aver
            // recuperato i pezzi mancanti (vedi SiteTls).
            if (connessione is HttpsURLConnection) {
                SiteTls.fabbrica(Uri.parse(url).host.orEmpty())?.let { connessione.sslSocketFactory = it }
            }
            connessione.instanceFollowRedirects = true
            connessione.connectTimeout = 10_000
            connessione.readTimeout = 15_000
            connessione.requestMethod = if (onlyHeaders) "HEAD" else "GET"
            if (ua.isNotEmpty()) connessione.setRequestProperty("User-Agent", ua)
            connessione.setRequestProperty("Accept", "text/html,application/xhtml+xml")
            val tipo = connessione.contentType.orEmpty()
            val corpo = if (onlyHeaders) null else
                connessione.inputStream.bufferedReader(Charsets.UTF_8).use { it.readText() }
            Pair(corpo, tipo)
        } catch (t: Throwable) {
            null
        } finally {
            c?.disconnect()
        }
    }

    /** Decifra il blocco della verifica e restituisce il biscotto in esadecimale. */
    private fun aesCookie(chiave: String, vettore: String, cifrato: String): String? = try {
        val cifrario = Cipher.getInstance("AES/CBC/NoPadding")
        cifrario.init(
            Cipher.DECRYPT_MODE,
            SecretKeySpec(hexToBytes(chiave), "AES"),
            IvParameterSpec(hexToBytes(vettore))
        )
        cifrario.doFinal(hexToBytes(cifrato))
            .joinToString("") { (it.toInt() and 0xff).toString(16).padStart(2, '0') }
    } catch (t: Throwable) {
        null
    }

    private fun hexToBytes(hex: String): ByteArray =
        ByteArray(hex.length / 2) { i ->
            ((Character.digit(hex[i * 2], 16) shl 4) or Character.digit(hex[i * 2 + 1], 16)).toByte()
        }

    /**
     * Il nome con cui l'app si presenta al sito.
     *
     * La WebView si annuncia da sola con due segni che dicono "sono una
     * finestra dentro un'app": "; wv" e "Version/4.0". La protezione
     * dell'hosting li legge come "non e' un browser" e risponde con la pagina
     * di verifica, che si ricarica e non arriva mai: il telefono restava
     * fermo li'. Si tolgono quei due segni e resta l'identita' vera del
     * telefono (Android, Chrome, Mobile). Il sito sa comunque di essere
     * dentro l'app: glielo dice il ponte LuxVerse, non il nome.
     */
    private fun browserUserAgent(original: String?): String {
        var ua = (original ?: "").trim()
        if (ua.isEmpty()) return ua
        ua = ua.replace("; wv;", ";").replace("; wv)", ")").replace(" wv ", " ")
        ua = ua.replace("Version/4.0 ", "")
        return ua.replace(Regex("\\s{2,}"), " ").trim()
    }

    // ========================================================
    // Indirizzi: cosa resta dentro, cosa esce
    // ========================================================
    private fun handleUrl(raw: String): Boolean {
        val uri = Uri.parse(raw)
        val scheme = uri.scheme?.lowercase() ?: return true

        // Indirizzi speciali della pagina di cortesia (bottone "Riprova").
        if (scheme == "luxverse") {
            retry()
            return true
        }
        // Posta, telefono, mappe, negozi: si aprono con l'app giusta del telefono.
        if (scheme != "http" && scheme != "https") {
            openOutside(raw)
            return true
        }
        val host = uri.host ?: return true
        // Le pagine del sito restano dentro; il resto esce.
        val isOurs = host == siteHost || host.endsWith(".$siteHost")
        if (!isOurs) openOutside(raw)
        return !isOurs
    }

    /** Apre un indirizzo fuori dall'app: browser, posta, telefono, negozio. */
    private fun openOutside(url: String) {
        try {
            startActivity(Intent(Intent.ACTION_VIEW, Uri.parse(url)).addFlags(Intent.FLAG_ACTIVITY_NEW_TASK))
        } catch (e: ActivityNotFoundException) {
            Toast.makeText(this, R.string.no_app_for_link, Toast.LENGTH_SHORT).show()
        }
    }

    // ========================================================
    // Attesa, pagina di cortesia, ritorno
    // ========================================================
    /**
     * IL VELO SI ALZA PER CONTO SUO, SENZA CHIEDERE NIENTE ALLA PAGINA.
     *
     * Qui c'era il difetto che faceva comparire "L.U.X. non risponde": a ogni
     * pagina caricata si domandava al contenuto, in JavaScript, se ci fosse
     * del testo da leggere, e glielo si richiedeva ogni 0,35 s fino a 16
     * volte; ogni nuova pagina faceva ripartire il conto, quindi le domande
     * potevano anche sovrapporsi. Se la pagina e' occupata — e la pagina di
     * verifica dell'hosting lo e' — ogni domanda resta in attesa della
     * risposta, la finestra dell'app non risponde piu' e Android la chiude.
     *
     * Adesso non si chiede piu' niente: si aspetta che la pagina abbia un
     * titolo (le nostre pagine ce l'hanno sempre, la verifica no) e che abbia
     * disegnato il primo pezzo; poi si aspetta un respiro e il velo sale.
     */
    private fun scheduleHideVeil() {
        if (settled || isFinishing || !titleSeen) return
        cancelHideVeil()
        val task = Runnable { if (!settled && !isFinishing) settle() }
        hideVeilWatch = task
        ui.postDelayed(task, VEIL_BREATH)
    }

    private fun cancelHideVeil() {
        hideVeilWatch?.let { ui.removeCallbacks(it) }
        hideVeilWatch = null
    }

    /** Le due sveglie dell'attesa: "la rete e' lenta" e "basta cosi'". */
    private fun startWatches() {
        cancelWatches()
        val slow = Runnable {
            if (!settled && !isFinishing && veil.visibility == View.VISIBLE) {
                veilStatus.setText(R.string.loading_slow)
            }
        }
        // A meta' strada, se ancora non si vede niente, si rifa' la verifica
        // e si riapre il sito una volta: il biscotto puo' essere scaduto o
        // essere stato rifiutato, e questa e' la cura piu' comune.
        val riprova = Runnable {
            if (!settled && !isFinishing && !accessRetried) {
                accessRetried = true
                veilStatus.setText(R.string.loading_verifying)
                openSite()
            }
        }
        val giveUp = Runnable { if (!settled && !isFinishing) showOffline() }
        slowWatch = slow
        accessWatch = riprova
        giveUpWatch = giveUp
        ui.postDelayed(slow, SLOW_MS)
        ui.postDelayed(riprova, ACCESS_RETRY_MS)
        ui.postDelayed(giveUp, GIVE_UP_MS)
    }

    private fun cancelWatches() {
        slowWatch?.let { ui.removeCallbacks(it) }
        accessWatch?.let { ui.removeCallbacks(it) }
        giveUpWatch?.let { ui.removeCallbacks(it) }
        slowWatch = null
        accessWatch = null
        giveUpWatch = null
    }

    private fun settle() {
        cancelHideVeil()
        cancelWatches()
        settled = true
        // Solo adesso il biscotto della verifica e' sicuramente nella riserva:
        // il controllo degli aggiornamenti parte da qui, non dall'avvio.
        if (!updateChecked) {
            updateChecked = true
            checkForUpdate()
        }
        // Da qui in poi una nuova richiesta d'emergenza al sito puo' essere
        // riprovata da sola un'altra volta.
        autoRetried = false
        offline.visibility = View.GONE
        progress.visibility = View.GONE
        syncBackButton()
        hideVeil()
    }

    /**
     * Accende il bottone indietro solo quando c'e' una pagina precedente
     * dentro il sito. Sulla pagina di cortesia resta spento: li' l'unica
     * cosa utile e' "Riprova", e un indietro che non porta a niente
     * confonderebbe soltanto.
     */
    private fun syncBackButton() {
        // 24/10/2026: il bottone indietro nativo resta spento. La freccia
        // adesso la disegna il sito, in alto a destra, e SOLO dentro uno
        // strumento: tenerne due (uno in basso a sinistra su ogni pagina)
        // confondeva e non era quello che si voleva. Il tasto/gesto di
        // sistema continua a funzionare come prima (vedi onBackPressed).
        backBtn.visibility = View.GONE
    }

    /** Il velo si alza come una tendina: una sola trasformazione, 220 ms. */
    private fun hideVeil() {
        if (veil.visibility != View.VISIBLE) return
        veil.animate()
            .translationY(-veil.height.toFloat())
            .setDuration(220)
            .withEndAction { veil.visibility = View.GONE }
            .start()
    }

    private fun showOffline() {
        cancelHideVeil()
        cancelWatches()
        settled = true
        veil.visibility = View.GONE
        offline.visibility = View.VISIBLE
        backBtn.visibility = View.GONE
        if (hasNetwork()) {
            offlineTitle.setText(R.string.offline_title)
            offlineText.setText(R.string.offline_text)
        } else {
            offlineTitle.setText(R.string.no_network_title)
            offlineText.setText(R.string.no_network_text)
        }
    }

    /** La pagina di cortesia quando il problema e' il certificato del sito. */
    private fun showTls() {
        cancelHideVeil()
        cancelWatches()
        settled = true
        veil.visibility = View.GONE
        offline.visibility = View.VISIBLE
        backBtn.visibility = View.GONE
        offlineTitle.setText(R.string.tls_title)
        offlineText.setText(R.string.tls_text)
    }

    /**
     * IL CERTIFICATO SI CONTROLLA, POI SI DECIDE.
     *
     * La WebView si ferma perche' l'hosting manda il certificato senza la
     * catena. Qui non si dice "vai avanti lo stesso": si ricostruisce la
     * catena con i pezzi che il certificato stesso dichiara e la si fa
     * verificare al telefono, fuori dall'interfaccia (SiteTls). Se il
     * telefono riconosce chi risponde, si prosegue; altrimenti si rinuncia e
     * si mostra la pagina di cortesia.
     *
     * Le richieste della stessa pagina arrivano una per una (documento, CSS,
     * immagini): la prima paga il controllo, le altre lo trovano gia' fatto.
     */
    private fun askCertificate(handler: SslErrorHandler) {
        tlsWaiters.addLast(handler)
        if (veil.visibility == View.VISIBLE) veilStatus.setText(R.string.tls_checking)
        if (tlsChecking) return
        tlsChecking = true
        val scadenza = Runnable {
            if (!tlsChecking) return@Runnable
            tlsChecking = false
            tlsVerdict = false
            tlsWaiters.forEach { it.cancel() }
            tlsWaiters.clear()
            showTls()
        }
        tlsTimer = scadenza
        ui.postDelayed(scadenza, TLS_WAIT_MS)
        val lavoro = Thread {
            val esito = SiteTls.verifica(siteHost)
            ui.post {
                if (isFinishing || isDestroyed) return@post
                tlsTimer?.let { ui.removeCallbacks(it) }
                tlsTimer = null
                tlsChecking = false
                tlsVerdict = esito
                val attese = tlsWaiters.toList()
                tlsWaiters.clear()
                if (esito) {
                    attese.forEach { it.proceed() }
                    if (!settled) scheduleHideVeil()
                } else {
                    attese.forEach { it.cancel() }
                    showTls()
                }
            }
        }
        lavoro.isDaemon = true
        lavoro.start()
    }

    private fun retry() {
        cancelHideVeil()
        cancelWatches()
        settled = false
        autoRetried = false
        accessRetried = false
        titleSeen = false
        offline.visibility = View.GONE
        progress.visibility = View.VISIBLE
        veil.visibility = View.VISIBLE
        veil.translationY = 0f
        veilProgress.progress = 0
        veilStatus.setText(R.string.loading_connecting)
        startWatches()
        // Non basta ricaricare: se il motivo era la verifica dell'hosting, va
        // rifatta prima. openSite() la rifà e poi ricarica la pagina aperta.
        openSite(keepUrl = web.url != null)
    }

    private fun hasNetwork(): Boolean {
        val cm = getSystemService(Context.CONNECTIVITY_SERVICE) as? ConnectivityManager ?: return true
        val network = cm.activeNetwork ?: return false
        val caps = cm.getNetworkCapabilities(network) ?: return false
        return caps.hasCapability(NetworkCapabilities.NET_CAPABILITY_INTERNET)
    }

    // ========================================================
    // Salvataggio dei file
    // ========================================================
    private fun enqueueDownload(url: String, agent: String, disposition: String, mime: String, name: String) {
        try {
            val request = DownloadManager.Request(Uri.parse(url))
                .setTitle(name)
                .setDescription(getString(R.string.download_description, siteHost))
                .setMimeType(mime)
                .setNotificationVisibility(DownloadManager.Request.VISIBILITY_VISIBLE_NOTIFY_COMPLETED)
                .setDestinationInExternalPublicDir(Environment.DIRECTORY_DOWNLOADS, name)
            if (agent.isNotEmpty()) request.addRequestHeader("User-Agent", agent)
            (getSystemService(Context.DOWNLOAD_SERVICE) as DownloadManager).enqueue(request)
            Toast.makeText(this, R.string.download_started, Toast.LENGTH_SHORT).show()
        } catch (e: Exception) {
            Toast.makeText(this, R.string.download_failed, Toast.LENGTH_SHORT).show()
        }
    }

    override fun onRequestPermissionsResult(requestCode: Int, permissions: Array<out String>, grantResults: IntArray) {
        super.onRequestPermissionsResult(requestCode, permissions, grantResults)
        if (requestCode != REQUEST_WRITE) return
        val url = pendingUrl
        val granted = grantResults.isNotEmpty() && grantResults[0] == PackageManager.PERMISSION_GRANTED
        pendingUrl = null
        if (url != null && granted) {
            enqueueDownload(url, pendingAgent.orEmpty(), pendingDisposition.orEmpty(), pendingMime.orEmpty(),
                URLUtil.guessFileName(url, pendingDisposition, pendingMime))
        } else {
            Toast.makeText(this, R.string.download_needs_permission, Toast.LENGTH_LONG).show()
        }
    }

    // ========================================================
    // Ciclo di vita
    // ========================================================
    override fun onSaveInstanceState(outState: Bundle) {
        super.onSaveInstanceState(outState)
        web.saveState(outState)
    }

    override fun onPause() {
        super.onPause()
        web.onPause()          // ferma i lavori della pagina: non consuma batteria in sottofondo
    }

    override fun onResume() {
        super.onResume()
        web.onResume()
    }

    override fun onDestroy() {
        // Le sveglie si spengono sempre: se restassero accese dopo la chiusura
        // della schermata toccherebbero viste che non esistono piu'.
        cancelHideVeil()
        cancelWatches()
        // La finestra della pagina va smontata a mano: se resta attaccata, il
        // sistema tiene in vita il motore che la disegna (memoria occupata per
        // niente). Si fa sempre, non solo all'uscita: quando la schermata si
        // rifa' da zero il sistema ne costruisce comunque una nuova.
        (web.parent as? ViewGroup)?.removeView(web)
        web.destroy()
        super.onDestroy()
    }

    /**
     * Il ponte che la pagina puo' chiamare: window.LuxVerse.*
     *
     * Sono quattro funzioni, tutte innocue e tutte senza dati personali.
     * Nessuna legge il telefono, nessuna restituisce qualcosa di chi usa
     * l'app: servono solo a far sapere alla pagina che sta girando dentro
     * l'app e in che versione.
     */
    inner class WebBridge {
        @JavascriptInterface
        fun platform(): String = "android"

        @JavascriptInterface
        fun appVersion(): String = BuildConfig.VERSION_NAME

        @JavascriptInterface
        fun appBuild(): Int = BuildConfig.VERSION_CODE

        @JavascriptInterface
        fun appId(): String = BuildConfig.APPLICATION_ID

        /** Apre un indirizzo nel browser del telefono (la pagina lo puo' chiedere). */
        @JavascriptInterface
        fun openExternal(url: String) {
            if (!url.startsWith("http://") && !url.startsWith("https://")) return
            runOnUiThread { openOutside(url) }
        }

        /** Apre il pannello "condividi" di Android con testo e collegamento. */
        @JavascriptInterface
        fun share(text: String) {
            runOnUiThread {
                val send = Intent(Intent.ACTION_SEND).setType("text/plain").putExtra(Intent.EXTRA_TEXT, text)
                startActivity(Intent.createChooser(send, getString(R.string.share_via)))
            }
        }
    }

    private companion object {
        // Il respiro fra "la pagina ha disegnato" e "il velo si alza": serve a
        // non mostrare un lampo vuoto quando la pagina di verifica sta per
        // ricaricarsi. Non e' un'attesa che blocca niente: e' un timer.
        const val VEIL_BREATH = 140L
        const val SLOW_MS = 8_000L       // dopo 8 s: "la rete e' lenta"
        const val ACCESS_RETRY_MS = 15_000L  // dopo 15 s: si rifa' la verifica
        const val GIVE_UP_MS = 45_000L   // dopo 45 s: pagina di cortesia
        const val SOLO_RETRY = 1_500L    // un solo ritentativo automatico
        // Quanto si aspetta il controllo del certificato prima di dire di no:
        // se il telefono non risponde in questo tempo, il collegamento resta
        // non verificato e l'app si ferma.
        const val TLS_WAIT_MS = 20_000L
        const val REQUEST_WRITE = 4711
    }
}
