package org.luxverse.app.ui

import android.Manifest
import android.annotation.SuppressLint
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
import android.view.View
import android.webkit.CookieManager
import android.webkit.JavascriptInterface
import android.webkit.URLUtil
import android.webkit.WebChromeClient
import android.webkit.WebResourceError
import android.webkit.WebResourceRequest
import android.webkit.WebSettings
import android.webkit.WebView
import android.webkit.WebViewClient
import android.widget.Button
import android.widget.ProgressBar
import android.widget.TextView
import android.widget.Toast
import androidx.activity.OnBackPressedCallback
import androidx.appcompat.app.AppCompatActivity
import androidx.core.content.ContextCompat
import androidx.webkit.WebSettingsCompat
import androidx.webkit.WebViewFeature
import org.luxverse.app.BuildConfig
import org.luxverse.app.R

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
 */
class MainActivity : AppCompatActivity() {

    private lateinit var web: WebView
    private lateinit var progress: ProgressBar
    private lateinit var veil: View
    private lateinit var offline: View
    private lateinit var offlineTitle: TextView
    private lateinit var offlineText: TextView

    /** Il sito: tutte le pagine dello stesso indirizzo restano dentro l'app. */
    private val siteHost: String by lazy { Uri.parse(BuildConfig.SITE_URL).host ?: "" }

    /** Quante volte si e' ricaricato in attesa che la pagina avesse del testo. */
    private var tries = 0

    /** Vero quando una pagina vera e' stata mostrata: da li' in poi niente attese. */
    private var settled = false

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

        web = findViewById(R.id.web)
        progress = findViewById(R.id.progress)
        veil = findViewById(R.id.veil)
        offline = findViewById(R.id.offline)
        offlineTitle = findViewById(R.id.offlineTitle)
        offlineText = findViewById(R.id.offlineText)

        prepareWebView()

        findViewById<Button>(R.id.retry).setOnClickListener { retry() }
        findViewById<Button>(R.id.openInBrowser).setOnClickListener { openOutside(BuildConfig.SITE_URL) }

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
            web.loadUrl(BuildConfig.START_URL)
        }
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
        s.userAgentString = s.userAgentString + " LuxVerseApp/" + BuildConfig.VERSION_NAME

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
            override fun onProgressChanged(view: WebView?, newProgress: Int) {
                if (newProgress >= 100) {
                    progress.visibility = View.GONE
                } else {
                    progress.visibility = View.VISIBLE
                    progress.progress = newProgress
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
            }

            override fun onPageFinished(view: WebView?, url: String?) {
                super.onPageFinished(view, url)
                progress.visibility = View.GONE
                if (settled) return
                lookForText()
            }

            override fun onReceivedError(view: WebView, request: WebResourceRequest, error: WebResourceError) {
                // Solo l'errore della pagina principale conta: se non carica una
                // singola immagine non si copre tutto con la pagina di cortesia.
                if (!request.isForMainFrame) return
                showOffline()
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
     * La pagina ha del testo da leggere? Finche' non ne ha, e' la pagina
     * dell'hosting che si sta preparando. Si riprova qualche volta, poi si
     * mostra la pagina di cortesia: meglio dire "non risponde" che lasciare
     * un rettangolo scuro.
     */
    private fun lookForText() {
        web.evaluateJavascript(JS_TEXT_LENGTH) { raw ->
            val length = raw?.trim()?.trim('"')?.toIntOrNull() ?: -1
            if (length >= MIN_TEXT) {
                settle()
            } else if (tries < MAX_TRIES) {
                tries++
                web.postDelayed({ if (!settled && !isFinishing) web.reload() }, RETRY_DELAY)
            } else {
                showOffline()
            }
        }
    }

    private fun settle() {
        tries = 0
        settled = true
        offline.visibility = View.GONE
        hideVeil()
    }

    private fun hideVeil() {
        veil.animate().alpha(0f).setDuration(220).withEndAction { veil.visibility = View.GONE }.start()
    }

    private fun showOffline() {
        settled = true
        veil.visibility = View.GONE
        offline.visibility = View.VISIBLE
        if (hasNetwork()) {
            offlineTitle.setText(R.string.offline_title)
            offlineText.setText(R.string.offline_text)
        } else {
            offlineTitle.setText(R.string.no_network_title)
            offlineText.setText(R.string.no_network_text)
        }
    }

    private fun retry() {
        tries = 0
        settled = false
        offline.visibility = View.GONE
        progress.visibility = View.VISIBLE
        veil.visibility = View.VISIBLE
        veil.alpha = 1f
        if (web.url != null) web.reload() else web.loadUrl(BuildConfig.START_URL)
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
        if (isFinishing) {
            (web.parent as? android.view.ViewGroup)?.removeView(web)
            web.destroy()
        }
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
        const val JS_TEXT_LENGTH =
            """(function(){try{var b=document.body;if(!b)return 0;var t=(b.innerText||'').replace(/\s+/g,' ').trim();return t.length}catch(e){return -1}})()"""
        const val MIN_TEXT = 12          // sotto questa soglia la pagina e' vuota
        const val MAX_TRIES = 6          // tentativi prima della pagina di cortesia
        const val RETRY_DELAY = 700L     // millisecondi fra un tentativo e l'altro
        const val REQUEST_WRITE = 4711
    }
}
