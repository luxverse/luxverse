package com.workmeai.app.ui

import android.annotation.SuppressLint
import android.app.AlertDialog
import android.content.Intent
import android.graphics.Color
import android.net.Uri
import android.os.Build
import android.os.Bundle
import android.view.View
import android.webkit.CookieManager
import android.webkit.WebResourceError
import android.webkit.WebResourceRequest
import android.webkit.WebResourceResponse
import android.webkit.WebSettings
import android.webkit.WebView
import android.webkit.WebViewClient
import androidx.activity.OnBackPressedCallback
import androidx.appcompat.app.AppCompatActivity
import androidx.core.content.FileProvider
import androidx.core.view.ViewCompat
import androidx.core.view.WindowInsetsCompat
import androidx.core.view.isVisible
import androidx.webkit.WebViewAssetLoader
import com.workmeai.app.BuildConfig
import com.workmeai.app.R
import com.workmeai.app.databinding.ActivityWebBinding
import okhttp3.OkHttpClient
import okhttp3.Request
import org.json.JSONObject
import java.io.File
import java.util.Locale
import java.util.concurrent.TimeUnit

/**
 * Webapp wrapper: carica il SITO LIVE L.U.X. Verse a schermo intero, installabile come app.
 *
 * L'indirizzo di partenza è BuildConfig.APP_URL (webapp.php?app=1): quello che si vede
 * nell'app è SEMPRE la versione appena pubblicata del sito, senza copie locali da tenere
 * allineate. In assets/www resta una sola pagina, offline.html, che serve unicamente
 * quando manca la rete.
 *
 * Nel pacchetto non è nascosta nessuna chiave: l'uovo d'oro (chiavi AI, token) vive sul
 * server, non nell'APK, che è pubblico e chiunque può aprire.
 */
class MainActivity : AppCompatActivity() {

    private lateinit var b: ActivityWebBinding
    private lateinit var siteHost: String
    private lateinit var assetLoader: WebViewAssetLoader
    private val assetHost = "appassets.androidplatform.net"
    private val localStartUrl = BuildConfig.APP_URL
    // Unico file locale dell'app: si apre solo se il sito non risponde.
    private val offlinePageName = "offline.html"
    private val offlineFallbackUrl = "https://$assetHost/assets/www/$offlinePageName"
    private val http = OkHttpClient.Builder()
        .connectTimeout(15, TimeUnit.SECONDS)
        .readTimeout(20, TimeUnit.SECONDS)
        .build()
    private var updateChecked = false
    private var offlineFallbackUsed = false
    // Collegamento in tempo reale: quando l'app torna in primo piano, se e'
    // passato abbastanza tempo la pagina viene ricaricata dal sito live.
    private var lastLoadAt = 0L

    // =====================================================
    // PERCHE' LA SCHERMATA NERA (causa trovata e qui curata)
    // =====================================================
    // L'hosting del sito mette davanti a TUTTE le pagine una verifica
    // anti-bot: la prima risposta non e' il sito, ma una paginetta HTML con
    // il corpo VUOTO che esegue uno script, scrive un cookie e poi ricarica
    // (e' il controllo "aes.js" di InfinityFree).
    //
    // Per un browser e' invisibile; per l'app era la schermata nera: il corpo
    // vuoto e' trasparente, quindi lasciava vedere il fondo scuro della
    // WebView, e onPageFinished la considerava "pagina caricata" chiudendo
    // subito la schermata di avvio. Se poi la ricarica non riusciva (rete
    // lenta, WebView vecchia), restava nero per sempre.
    //
    // Qui l'overlay NON si chiude piu' quando finisce una pagina: si chiude
    // quando la pagina vera si e' disegnata (controllo sul testo del corpo).
    // E se non succede entro il tempo massimo, si mostra la schermata di
    // errore con il pulsante Riprova: mai piu' un nero senza spiegazione.
    private var realPageShown = false
    private var watchdog: Runnable? = null
    // Tornare all'indirizzo richiesto dopo la verifica: una volta sola.
    private var returnedToStart = false
    private var versionHandled = false
    // La WebView non è più utilizzabile (onDestroy): i controlli differiti
    // che arrivano dopo non devono più toccarla.
    private var viewGone = false

    /** Ponte minimo verso la pagina: serve solo a leggere la versione 
     *  dell'app dal sito, passando per la WebView (che supera la verifica). */
    inner class SiteBridge {
        @android.webkit.JavascriptInterface
        fun onVersion(json: String?) {
            if (json.isNullOrBlank() || versionHandled) return
            runOnUiThread {
                if (versionHandled) return@runOnUiThread
                runCatching { JSONObject(json) }.getOrNull()?.let {
                    versionHandled = true
                    handleVersion(it)
                }
            }
        }
    }

    @SuppressLint("SetJavaScriptEnabled")
    override fun onCreate(savedInstanceState: Bundle?) {
        super.onCreate(savedInstanceState)

        window.statusBarColor = Color.parseColor("#0A0B0D")
        window.navigationBarColor = Color.parseColor("#0A0B0D")
        applyLightSystemBars()

        b = ActivityWebBinding.inflate(layoutInflater)
        setContentView(b.root)
        // Nella schermata di caricamento si legge anche la versione dell'app.
        b.tvVersion.text = "L.U.X. Verse · v" + BuildConfig.VERSION_NAME
        // Edge-to-edge (Android 15+): teniamo la webapp sotto le barre di sistema,
        // così l'header non finisce sotto il notch/status bar.
        //
        // IMPORTANTE: gli inset vanno CONSUMATI qui. Se li lasciamo passare, la
        // WebView li riapplica da sola come padding tutto suo (padding sopra + sotto),
        // la pagina viene spinta in giù e il tratto in alto resta fuori asse: la parte
        // alta di ogni pagina appare bloccata e non si riesce a scorrerla.
        b.root.setBackgroundColor(Color.parseColor("#0A0B0D"))
        b.webview.setPadding(0, 0, 0, 0)
        ViewCompat.setOnApplyWindowInsetsListener(b.root) { v, insets ->
            val bars = insets.getInsets(WindowInsetsCompat.Type.systemBars())
            // LA TASTIERA E' UN INSET COME GLI ALTRI.
            // Da Android 15 la finestra è per forza "edge to edge" e
            // adjustResize da solo non accorcia più niente: senza queste due
            // righe la WebView resta alta quanto tutto lo schermo e, appena si
            // tocca un campo, la tastiera copre il campo di scrittura — si
            // scrive senza vedere quello che si scrive. Rimpicciolendo il
            // contenitore dell'altezza dei tasti, la pagina si accorcia e il
            // campo resta in vista (il sito lo riporta su con il visual
            // viewport, vedi assets/js/site.js).
            val ime = insets.getInsets(WindowInsetsCompat.Type.ime())
            v.setPadding(0, bars.top, 0, maxOf(bars.bottom, ime.bottom))
            // La WebView non deve ricevere gli inset: se li ricevesse ci metterebbe
            // un secondo padding proprio e sfalserebbe il rendering della pagina.
            b.webview.setPadding(0, 0, 0, 0)
            WindowInsetsCompat.CONSUMED
        }

        // Back = pagina precedente (mai uscire dall'app con il tasto sistema).
        onBackPressedDispatcher.addCallback(this, object : OnBackPressedCallback(true) {
            override fun handleOnBackPressed() {
                if (b.webview.canGoBack()) b.webview.goBack()
                else loadSite(withProgress = false)
            }
        })

        siteHost = Uri.parse(BuildConfig.SITE_URL).host ?: ""

        assetLoader = WebViewAssetLoader.Builder()
            .addPathHandler("/assets/", WebViewAssetLoader.AssetsPathHandler(this))
            .build()

        configureWebView()
        b.btnRetry.setOnClickListener { loadSite(withProgress = true) }

        loadSite(withProgress = true)
    }

    @SuppressLint("SetJavaScriptEnabled")
    private fun configureWebView() {
        val wv = b.webview
        // Unico ponte verso la pagina: legge la versione dell'app dal sito.
        wv.addJavascriptInterface(SiteBridge(), "LuxBridge")

        // --- RESA FLUIDA (feel da app nativa) ---
        wv.setBackgroundColor(Color.parseColor("#0A0B0D"))
        // NOTA: qui c'era setLayerType(LAYER_TYPE_HARDWARE) per il compositing
        // su GPU. Su WebView e' una causa nota di schermo nero (la pagina non
        // viene ridisegnata finche' non si tocca lo schermo) e non serve a
        // niente: l'accelerazione hardware e' gia' attiva da sola. Tolto.
        wv.overScrollMode = View.OVER_SCROLL_NEVER        // niente rimbalzo/filamento blu
        wv.isVerticalScrollBarEnabled = false
        wv.isHorizontalScrollBarEnabled = false
        wv.isScrollbarFadingEnabled = true
        wv.isFocusableInTouchMode = true

        wv.settings.javaScriptEnabled = true
        wv.settings.domStorageEnabled = true
        wv.settings.databaseEnabled = true
        wv.settings.mediaPlaybackRequiresUserGesture = false
        wv.settings.mixedContentMode = WebSettings.MIXED_CONTENT_NEVER_ALLOW
        wv.settings.loadWithOverviewMode = true
        wv.settings.useWideViewPort = true
        wv.settings.builtInZoomControls = false
        wv.settings.setSupportZoom(false)
        wv.settings.displayZoomControls = false
        wv.settings.cacheMode = WebSettings.LOAD_DEFAULT
        // Il testo resta leggibile anche se l'utente ha ingrandito i caratteri di sistema:
        // evita che il layout venga "gonfiato" e vada fuori asse sui telefoni.
        wv.settings.textZoom = 100
        wv.settings.setGeolocationEnabled(false)
        wv.settings.allowFileAccess = false
        wv.settings.allowContentAccess = false
        if (Build.VERSION.SDK_INT >= Build.VERSION_CODES.TIRAMISU) {
            wv.settings.isAlgorithmicDarkeningAllowed = false
        }

        CookieManager.getInstance().setAcceptCookie(true)
        if (Build.VERSION.SDK_INT >= Build.VERSION_CODES.LOLLIPOP) {
            CookieManager.getInstance().setAcceptThirdPartyCookies(wv, true)
        }

        wv.webViewClient = object : WebViewClient() {
            override fun shouldInterceptRequest(view: WebView?, request: WebResourceRequest?): WebResourceResponse? {
                val url = request?.url ?: return null
                return assetLoader.shouldInterceptRequest(url)
            }

            override fun onPageFinished(view: WebView?, url: String?) {
                b.progress.isVisible = false
                // Rete di sicurezza: nessun padding sulla WebView, così la pagina parte
                // sempre dalla prima riga (niente striscia bloccata in alto).
                b.webview.setPadding(0, 0, 0, 0)
                // Se l'indirizzo non punta a una sezione precisa, la pagina riparte dall'inizio.
                if (url == null || !url.contains('#')) b.webview.scrollTo(0, 0)

                val loaded = url.orEmpty()

                // La pagina di riserva è nostra e non conosce l'indirizzo del
                // sito: glielo passiamo da qui, così resta scritto in un solo
                // posto (BuildConfig.APP_URL). Si mostra subito.
                if (loaded.contains(offlinePageName)) {
                    showRealPage()
                    val js = "window.__luxRetryUrl && window.__luxRetryUrl(" +
                        JSONObject.quote(localStartUrl) + ");"
                    b.webview.evaluateJavascript(js, null)
                    return
                }

                // Fine di una pagina NON vuol dire che sia la pagina giusta:
                // la verifica anti-bot consegna una pagina dal corpo vuoto.
                // Si aspetta che il corpo abbia davvero del testo, e solo
                // allora si scopre l'app (vedi probeRealPage).
                probeRealPage(0)
            }

            override fun onReceivedHttpError(
                view: WebView?,
                request: WebResourceRequest?,
                errorResponse: WebResourceResponse?
            ) {
                if (request == null || request.isForMainFrame) {
                    val status = errorResponse?.statusCode ?: 0
                    b.progress.isVisible = false
                    b.overlay.isVisible = false
                    // Un errore del server non deve restare un nero muto:
                    // si dice cosa è successo e si offre Riprova.
                    b.tvErrorBody.text = "Il sito ha risposto con un errore" +
                        (if (status > 0) " ($status)" else "") +
                        ". Riprova fra qualche secondo."
                    b.overlayError.isVisible = true
                }
            }

            override fun onReceivedError(
                view: WebView?,
                request: WebResourceRequest?,
                error: WebResourceError?
            ) {
                if (request == null || request.isForMainFrame) {
                    b.progress.isVisible = false
                    b.overlay.isVisible = false
                    val url = request?.url?.toString() ?: ""
                    val isOfflineShell = url.contains(assetHost)
                    if (isOfflineShell) {
                        // La shell offline non si apre nemmeno: errore di rete reale.
                        b.overlayError.isVisible = true
                        return
                    }
                    if (!offlineFallbackUsed) {
                        // Sito live irraggiungibile (offline): fallback sulla copia locale.
                        offlineFallbackUsed = true
                        b.webview.postDelayed({ b.webview.loadUrl(offlineFallbackUrl) }, 400)
                        return
                    }
                    b.overlayError.isVisible = true
                }
            }

            override fun shouldOverrideUrlLoading(view: WebView?, request: WebResourceRequest?): Boolean {
                val url = request?.url?.toString() ?: return false
                return openIfExternal(url)
            }

            @Suppress("DEPRECATION")
            override fun shouldOverrideUrlLoading(view: WebView?, url: String?): Boolean {
                if (url == null) return false
                return openIfExternal(url)
            }
        }
    }

    /** La pagina vera è a schermo: si chiudono le schermate di attesa. */
    private fun showRealPage() {
        realPageShown = true
        watchdog?.let { b.root.removeCallbacks(it) }
        b.progress.isVisible = false
        b.overlay.isVisible = false
        b.overlayError.isVisible = false
    }

    /**
     * Guarda dentro la pagina per capire se è il sito o la pagina di verifica.
     * Il controllo è sul testo del corpo: la pagina di verifica non ne ha.
     * Riprova da sola per qualche secondo (la verifica si supera in meno di
     * uno), e intanto l'utente vede la schermata di avvio dell'app, non il nero.
     */
    private fun probeRealPage(attempt: Int) {
        if (viewGone) return
        val js = "(function(){try{var b=document.body;return b?b.innerText.length:0;}catch(e){return 0;}})()"
        b.webview.evaluateJavascript(js) { raw ->
            val chars = raw?.trim()?.removeSurrounding("\"")?.toIntOrNull() ?: 0
            if (chars > 200) {
                if (!realPageShown) {
                    showRealPage()
                    afterRealPage(b.webview.url.orEmpty())
                }
                return@evaluateJavascript
            }
            if (attempt < 12) {
                b.webview.postDelayed({ probeRealPage(attempt + 1) }, 400)
            }
            // Oltre questo non si insiste: ci pensa il controllo a tempo
            // (vedi scheduleWatchdog) a mostrare la schermata di errore.
        }
    }

    private fun afterRealPage(url: String) {
        // La verifica ci lascia sulla radice del sito invece che sulla pagina
        // richiesta: si torna dove si voleva entrare, una volta sola.
        val path = url.substringAfter("://", "").substringAfter('/', "")
        if (!returnedToStart && (path.isEmpty() || path.startsWith("?"))) {
            returnedToStart = true
            loadSite(withProgress = false)
            return
        }
        askVersionThroughWebView()
    }

    /** Nessuna pagina vera entro il tempo massimo? Si spiega, invece di restare neri. */
    private fun scheduleWatchdog() {
        watchdog?.let { b.root.removeCallbacks(it) }
        val run = Runnable {
            if (realPageShown) return@Runnable
            b.progress.isVisible = false
            b.overlay.isVisible = false
            b.overlayError.isVisible = true
            b.tvErrorBody.text = "Il sito non ha risposto in tempo. " +
                "Controlla la connessione e premi Riprova."
        }
        watchdog = run
        b.root.postDelayed(run, WATCHDOG_MS)
    }

    private fun openIfExternal(url: String): Boolean {
        val urlHost = Uri.parse(url).host ?: return false
        if (urlHost == assetHost || urlHost == siteHost || urlHost.endsWith("." + siteHost)) {
            return false
        }
        // Link esterni (telefono, mail, altri domini) → browser di sistema
        return runCatching {
            startActivity(Intent(Intent.ACTION_VIEW, Uri.parse(url)))
        }.isSuccess
    }

    private fun loadSite(withProgress: Boolean) {
        b.overlayError.isVisible = false
        // La schermata di avvio dell'app resta finché la pagina vera non è
        // disegnata: è quello che trasforma il nero in attesa.
        b.overlay.isVisible = true
        if (withProgress) b.progress.isVisible = true
        offlineFallbackUsed = false
        realPageShown = false
        lastLoadAt = System.currentTimeMillis()
        scheduleWatchdog()
        runCatching { b.webview.loadUrl(localStartUrl) }
    }

    // Ogni volta che l'app torna in primo piano, se sono passati piu' di 5
    // minuti la pagina viene ricaricata dal sito live: cosi' quello che si
    // vede e' sempre il sito aggiornato, non una copia vecchia in memoria.
    override fun onResume() {
        super.onResume()
        if (lastLoadAt > 0 && System.currentTimeMillis() - lastLoadAt > 5 * 60 * 1000L) {
            loadSite(withProgress = false)
        }
    }

    /** Barre di sistema chiare (icone bianche) sullo sfondo grafite: niente flash bianco. */
    private fun applyLightSystemBars() {
        if (Build.VERSION.SDK_INT >= Build.VERSION_CODES.M) {
            window.decorView.systemUiVisibility =
                window.decorView.systemUiVisibility and View.SYSTEM_UI_FLAG_LIGHT_STATUS_BAR.inv()
        }
        if (Build.VERSION.SDK_INT >= Build.VERSION_CODES.O) {
            window.decorView.systemUiVisibility =
                window.decorView.systemUiVisibility and View.SYSTEM_UI_FLAG_LIGHT_NAVIGATION_BAR.inv()
        }
    }

    // =====================================================
    // CONTROLLO AGGIORNAMENTI
    // =====================================================
    // La versione si chiede ALLA WEBVIEW, con una fetch dentro la pagina, non
    // piu' con una chiamata diretta: la verifica anti-bot dell'hosting
    // risponde in HTML anche alle API, quindi la chiamata diretta non poteva
    // mai riuscire e l'app non vedeva mai gli aggiornamenti. La WebView invece
    // la verifica l'ha gia' superata, e la sua fetch porta con se' il cookie.
    private fun askVersionThroughWebView() {
        if (updateChecked || viewGone) return
        updateChecked = true
        val js = "(function(){try{" +
            "fetch('/api/latest_version.php',{credentials:'include',cache:'no-store'})" +
            ".then(function(r){return r.text();})" +
            ".then(function(t){LuxBridge.onVersion(t);})" +
            ".catch(function(){});" +
            "}catch(e){}})()"
        b.webview.evaluateJavascript(js, null)
    }

    private fun handleVersion(json: JSONObject) {
        val remoteCode = json.optInt("version_code", 0)
        if (remoteCode <= BuildConfig.VERSION_CODE) return

        val isIt = Locale.getDefault().language == "it"
        val title = if (isIt) "Aggiornamento disponibile" else "Update available"
        val msg = json.optJSONObject("message")?.let {
            if (isIt) it.optString("it") else it.optString("en")
        }.takeUnless { it.isNullOrBlank() }
            ?: if (isIt) "È disponibile una nuova versione dell'app. Aggiorna ora per avere le ultime novità."
            else "A new version of the app is available. Update now to get the latest features."
        val remoteUrl = json.optString("url", "").takeUnless { it.isBlank() }
            ?: (BuildConfig.SITE_URL + "/LUXVerse.apk")
        val versionName = json.optString("version_name", "")

        val positive = if (isIt) "Aggiorna ora" else "Update now"
        val negative = if (isIt) "Più tardi" else "Later"

        AlertDialog.Builder(this)
            .setTitle(if (versionName.isNotBlank()) "$title ($versionName)" else title)
            .setMessage(msg)
            .setCancelable(true)
            .setPositiveButton(positive) { _, _ -> downloadAndInstall(remoteUrl) }
            .setNegativeButton(negative, null)
            .show()
    }

    private fun downloadAndInstall(url: String) {
        val isIt = Locale.getDefault().language == "it"
        val preparing = if (isIt) "Download in corso…" else "Downloading…"
        val dialog = AlertDialog.Builder(this)
            .setMessage(preparing)
            .setCancelable(false)
            .create()
        dialog.show()

        Thread {
            val apkFile = runCatching { downloadApk(url) }.getOrNull()
            runOnUiThread {
                dialog.dismiss()
                if (apkFile == null) {
                    val err = if (isIt) "Download non riuscito. Riprova più tardi." else "Download failed. Try again later."
                    AlertDialog.Builder(this).setMessage(err).setPositiveButton("OK", null).show()
                } else {
                    installApk(apkFile)
                }
            }
        }.start()
    }

    private fun downloadApk(url: String): File? {
        val dir = File(cacheDir, "updates").apply { mkdirs() }
        val out = File(dir, "L.U.X. Verse-update.apk")
        val req = Request.Builder().url(url).build()
        http.newCall(req).execute().use { resp ->
            if (!resp.isSuccessful) return null
            val body = resp.body ?: return null
            body.byteStream().use { input ->
                out.outputStream().use { output -> input.copyTo(output) }
            }
        }
        return if (out.exists() && out.length() > 0) out else null
    }

    private fun installApk(file: File) {
        runCatching {
            val uri = FileProvider.getUriForFile(this, "$packageName.fileprovider", file)
            val intent = Intent(Intent.ACTION_VIEW).apply {
                setDataAndType(uri, "application/vnd.android.package-archive")
                addFlags(Intent.FLAG_GRANT_READ_URI_PERMISSION)
                addFlags(Intent.FLAG_ACTIVITY_NEW_TASK)
            }
            startActivity(intent)
        }.onFailure {
            val isIt = Locale.getDefault().language == "it"
            val err = if (isIt)
                "Impossibile avviare l'installazione. Consenti l'installazione da fonti sconosciute nelle impostazioni."
            else
                "Unable to start installation. Allow install from unknown sources in settings."
            AlertDialog.Builder(this).setMessage(err).setPositiveButton("OK", null).show()
        }
    }

    @Deprecated("Deprecated in Java")
    override fun onBackPressed() {
        if (b.webview.canGoBack()) {
            b.webview.goBack()
        } else {
            loadSite(withProgress = false)
        }
    }

    /** Niente più controlli differiti su una WebView che non esiste più. */
    override fun onDestroy() {
        viewGone = true
        watchdog?.let { b.root.removeCallbacks(it) }
        watchdog = null
        super.onDestroy()
    }

    companion object {
        /** Tempo massimo concesso al sito prima di spiegare che non ha risposto. */
        private const val WATCHDOG_MS = 25_000L
    }
}