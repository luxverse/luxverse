package org.luxverse.app.ai

import android.app.Activity
import android.app.AlertDialog
import android.content.Context
import android.graphics.Color
import android.graphics.Typeface
import android.net.ConnectivityManager
import android.net.NetworkCapabilities
import android.os.Bundle
import android.text.InputType
import android.view.Gravity
import android.view.View
import android.view.ViewGroup
import android.widget.ArrayAdapter
import android.widget.Button
import android.widget.EditText
import android.widget.LinearLayout
import android.widget.ProgressBar
import android.widget.ScrollView
import android.widget.Spinner
import android.widget.TextView
import android.widget.Toast
import java.io.File
import java.net.HttpURLConnection
import java.net.URL
import java.util.concurrent.Executors

/**
 * IA LOCALE — la schermata nativa dell'app.
 *
 * Il modello GGUF gira dentro il telefono con llama.cpp: nessun testo esce
 * da qui, e funziona anche senza rete una volta scaricato il modello.
 *
 * Cosa fa:
 *  - legge la RAM del dispositivo e propone il modello adatto (la stessa
 *    regola del sito, vedi ModelCatalog);
 *  - scarica il modello una volta sola, con la barra di avanzamento, e solo
 *    su Wi-Fi se non c'è un permesso esplicito per i dati mobili;
 *  - carica il modello e apre la chat, con la cronologia degli ultimi turni.
 *
 * Il primo caricamento è lento (il file viene letto dal disco): la schermata
 * lo dice, non resta muta.
 */
class LocalAiActivity : Activity() {

    private val worker = Executors.newSingleThreadExecutor()

    private lateinit var spinner: Spinner
    private lateinit var status: TextView
    private lateinit var action: Button
    private lateinit var bar: ProgressBar
    private lateinit var log: LinearLayout
    private lateinit var scroll: ScrollView
    private lateinit var input: EditText
    private lateinit var send: Button

    private lateinit var models: List<LuxModel>
    private var ramGb: Float = 0f
    private var downloading = false
    private var generating = false
    private val history = ArrayList<Pair<String, String>>()

    private val prefs by lazy { getSharedPreferences("lux_ai", Context.MODE_PRIVATE) }

    override fun onCreate(savedInstanceState: Bundle?) {
        super.onCreate(savedInstanceState)

        ramGb = LocalLlm.deviceRamGb(this)
        models = ModelCatalog.forRam(ramGb)

        setContentView(buildUi())
        fillModels()
        refreshState()
    }

    // ========================================================
    // Interfaccia
    // ========================================================
    private fun buildUi(): View {
        val root = LinearLayout(this).apply {
            orientation = LinearLayout.VERTICAL
            setBackgroundColor(Color.parseColor("#0A0B0D"))
            setPadding(dp(20), dp(28), dp(20), dp(16))
        }

        val head = LinearLayout(this).apply {
            orientation = LinearLayout.HORIZONTAL
            gravity = Gravity.CENTER_VERTICAL
        }
        head.addView(TextView(this).apply {
            text = "IA locale"
            setTextColor(Color.parseColor("#C9A86A"))
            textSize = 22f
            typeface = Typeface.create("serif", Typeface.NORMAL)
        }, LinearLayout.LayoutParams(0, ViewGroup.LayoutParams.WRAP_CONTENT, 1f))
        head.addView(Button(this).apply {
            text = "Chiudi"
            setBackgroundColor(Color.TRANSPARENT)
            setTextColor(Color.parseColor("#A5A197"))
            textSize = 13f
            setOnClickListener { finish() }
        })
        root.addView(head)

        root.addView(TextView(this).apply {
            text = "Il modello scaricato resta su questo telefono. I messaggi non escono da qui: nessun server, nessun account."
            setTextColor(Color.parseColor("#A5A197"))
            textSize = 13f
            setLineSpacing(dp(3).toFloat(), 1f)
            setPadding(0, dp(6), 0, dp(14))
        })

        // --- scheda del modello ---
        val card = LinearLayout(this).apply {
            orientation = LinearLayout.VERTICAL
            setBackgroundColor(Color.parseColor("#121316"))
            setPadding(dp(14), dp(14), dp(14), dp(14))
        }

        card.addView(TextView(this).apply {
            text = "TELEFONO · ${String.format("%.1f", ramGb)} GB DI RAM"
            setTextColor(Color.parseColor("#767D85"))
            textSize = 10.5f
            letterSpacing = 0.12f
        })

        spinner = Spinner(this)
        card.addView(spinner, LinearLayout.LayoutParams(
            ViewGroup.LayoutParams.MATCH_PARENT, ViewGroup.LayoutParams.WRAP_CONTENT
        ).apply { topMargin = dp(8) })
        spinner.onItemSelectedListener = object : android.widget.AdapterView.OnItemSelectedListener {
            override fun onItemSelected(parent: android.widget.AdapterView<*>?, view: View?, position: Int, id: Long) {
                if (position in models.indices) prefs.edit().putString("model", models[position].id).apply()
                refreshState()
            }
            override fun onNothingSelected(parent: android.widget.AdapterView<*>?) {}
        }

        status = TextView(this).apply {
            setTextColor(Color.parseColor("#A5A197"))
            textSize = 12.5f
            setPadding(0, dp(10), 0, 0)
        }
        card.addView(status)

        bar = ProgressBar(this, null, android.R.attr.progressBarStyleHorizontal).apply {
            max = 100
            visibility = View.GONE
            progressTintList = android.content.res.ColorStateList.valueOf(Color.parseColor("#C9A86A"))
        }
        card.addView(bar, LinearLayout.LayoutParams(
            ViewGroup.LayoutParams.MATCH_PARENT, dp(4)
        ).apply { topMargin = dp(10) })

        action = Button(this).apply {
            setBackgroundColor(Color.parseColor("#C9A86A"))
            setTextColor(Color.parseColor("#12100C"))
            textSize = 14f
            isAllCaps = false
            setOnClickListener { onAction() }
        }
        card.addView(action, LinearLayout.LayoutParams(
            ViewGroup.LayoutParams.MATCH_PARENT, ViewGroup.LayoutParams.WRAP_CONTENT
        ).apply { topMargin = dp(12) })

        root.addView(card)

        // --- chat ---
        scroll = ScrollView(this).apply {
            setBackgroundColor(Color.parseColor("#0E1013"))
            isFillViewport = true
        }
        log = LinearLayout(this).apply {
            orientation = LinearLayout.VERTICAL
            setPadding(dp(12), dp(12), dp(12), dp(12))
        }
        scroll.addView(log)
        root.addView(scroll, LinearLayout.LayoutParams(
            ViewGroup.LayoutParams.MATCH_PARENT, 0, 1f
        ).apply { topMargin = dp(12) })

        bubble(
            "assistant",
            "Scrivi qualcosa quando il modello è avviato. La prima risposta arriva dopo qualche secondo: il telefono sta leggendo il modello dal disco."
        )

        val row = LinearLayout(this).apply {
            orientation = LinearLayout.HORIZONTAL
            gravity = Gravity.CENTER_VERTICAL
        }
        input = EditText(this).apply {
            hint = "Scrivi un messaggio…"
            setHintTextColor(Color.parseColor("#6B7178"))
            setTextColor(Color.parseColor("#EDE9E0"))
            textSize = 15f
            inputType = InputType.TYPE_CLASS_TEXT or InputType.TYPE_TEXT_FLAG_CAP_SENTENCES
            setBackgroundColor(Color.parseColor("#121316"))
            setPadding(dp(12), dp(10), dp(12), dp(10))
            isEnabled = false
            setOnEditorActionListener { _, _, _ -> sendMessage(); true }
        }
        row.addView(input, LinearLayout.LayoutParams(0, ViewGroup.LayoutParams.WRAP_CONTENT, 1f))
        send = Button(this).apply {
            text = "Invia"
            setBackgroundColor(Color.parseColor("#C9A86A"))
            setTextColor(Color.parseColor("#12100C"))
            textSize = 14f
            isAllCaps = false
            isEnabled = false
            setOnClickListener { sendMessage() }
        }
        row.addView(send, LinearLayout.LayoutParams(
            ViewGroup.LayoutParams.WRAP_CONTENT, ViewGroup.LayoutParams.WRAP_CONTENT
        ).apply { leftMargin = dp(8) })
        root.addView(row, LinearLayout.LayoutParams(
            ViewGroup.LayoutParams.MATCH_PARENT, ViewGroup.LayoutParams.WRAP_CONTENT
        ).apply { topMargin = dp(10) })

        return root
    }

    private fun fillModels() {
        val labels = models.mapIndexed { i, m ->
            if (i == 0) "${m.label} — consigliato" else m.label
        }
        spinner.adapter = ArrayAdapter(this, android.R.layout.simple_spinner_dropdown_item, labels)
        val saved = prefs.getString("model", null)
        val idx = models.indexOfFirst { it.id == saved }
        if (idx > 0) spinner.setSelection(idx)
    }

    private fun current(): LuxModel = models.getOrElse(spinner.selectedItemPosition) { models.first() }

    private fun refreshState() {
        val model = current()
        val file = LocalLlm.modelFile(this, model.file)

        when {
            LocalLlm.isLoaded && file.isFile -> {
                status.text = "Attivo: ${model.name}. ${model.note}"
                action.text = "Modello attivo"
                action.isEnabled = !downloading
                input.isEnabled = true
                send.isEnabled = !generating
                bar.visibility = View.GONE
            }
            file.isFile -> {
                status.text = "Pronto sul telefono (${file.length() / (1024 * 1024)} MB). ${model.note}"
                action.text = "Avvia il modello"
                action.isEnabled = !downloading
                input.isEnabled = false
                send.isEnabled = false
                bar.visibility = View.GONE
            }
            else -> {
                status.text = "Da scaricare · ${model.sizeMb} MB · ${model.note}"
                action.text = if (downloading) "Scarico…" else "Scarica il modello"
                action.isEnabled = !downloading
                input.isEnabled = false
                send.isEnabled = false
            }
        }
    }

    // ========================================================
    // Azione principale: scarica, poi avvia
    // ========================================================
    private fun onAction() {
        val model = current()
        val file = LocalLlm.modelFile(this, model.file)
        if (file.isFile) loadModel(model, file) else askDownload(model)
    }

    private fun askDownload(model: LuxModel) {
        if (isMetered()) {
            AlertDialog.Builder(this)
                .setTitle("Sei sui dati mobili")
                .setMessage("Il modello ${model.name} pesa circa ${model.sizeMb} MB. Conviene scaricarlo in Wi-Fi. Vuoi usare i dati mobili lo stesso?")
                .setPositiveButton("Scarica comunque") { _, _ -> download(model) }
                .setNegativeButton("Aspetta il Wi-Fi", null)
                .show()
        } else {
            download(model)
        }
    }

    private fun isMetered(): Boolean {
        val cm = getSystemService(Context.CONNECTIVITY_SERVICE) as ConnectivityManager
        val net = cm.activeNetwork ?: return false
        val caps = cm.getNetworkCapabilities(net) ?: return false
        return !caps.hasTransport(NetworkCapabilities.TRANSPORT_WIFI) &&
            caps.hasCapability(NetworkCapabilities.NET_CAPABILITY_NOT_METERED).not()
    }

    private fun download(model: LuxModel) {
        downloading = true
        bar.visibility = View.VISIBLE
        bar.progress = 0
        refreshState()
        status.text = "Scarico ${model.name}…"

        worker.execute {
            val dir = LocalLlm.modelsDir(this)
            val target = File(dir, model.file)
            val part = File(dir, model.file + ".part")
            var ok = false
            var message = ""
            try {
                var url = URL(model.url)
                var conn: HttpURLConnection
                var redirects = 0
                while (true) {
                    conn = (url.openConnection() as HttpURLConnection).apply {
                        connectTimeout = 20_000
                        readTimeout = 30_000
                        instanceFollowRedirects = true
                        setRequestProperty("User-Agent", "LUXVerse/${org.luxverse.app.BuildConfig.VERSION_NAME}")
                    }
                    val code = conn.responseCode
                    if (code in 300..399 && redirects < 5) {
                        val to = conn.getHeaderField("Location") ?: break
                        url = URL(url, to)
                        redirects++
                        conn.disconnect()
                        continue
                    }
                    if (code != 200) throw IllegalStateException("Il server ha risposto $code")
                    break
                }
                val total = conn.contentLength.toLong()
                conn.inputStream.use { ins ->
                    part.outputStream().use { outs ->
                        val buf = ByteArray(256 * 1024)
                        var done = 0L
                        var lastPost = 0L
                        while (true) {
                            val n = ins.read(buf)
                            if (n <= 0) break
                            outs.write(buf, 0, n)
                            done += n
                            val now = System.currentTimeMillis()
                            if (total > 0 && now - lastPost > 250) {
                                lastPost = now
                                val pct = (done * 100 / total).toInt()
                                runOnUiThread {
                                    bar.progress = pct
                                    status.text = "Scarico ${model.name}… $pct%  (${done / (1024 * 1024)} di ${total / (1024 * 1024)} MB)"
                                }
                            }
                        }
                    }
                }
                if (total > 0 && part.length() != total) throw IllegalStateException("Download incompleto")
                if (target.exists()) target.delete()
                ok = part.renameTo(target)
                if (!ok) message = "Non riesco a salvare il modello."
            } catch (e: Exception) {
                message = e.message ?: "Download non riuscito."
                part.delete()
            }
            runOnUiThread {
                downloading = false
                bar.visibility = View.GONE
                if (ok) {
                    Toast.makeText(this, "Modello pronto", Toast.LENGTH_SHORT).show()
                    refreshState()
                    loadModel(model, LocalLlm.modelFile(this, model.file))
                } else {
                    status.text = "Download non riuscito: $message"
                    refreshState()
                }
            }
        }
    }

    private fun loadModel(model: LuxModel, file: File) {
        status.text = "Carico ${model.name}… può volerci mezzo minuto."
        action.isEnabled = false
        worker.execute {
            val t0 = System.currentTimeMillis()
            val ok = LocalLlm.load(file)
            val ms = System.currentTimeMillis() - t0
            runOnUiThread {
                if (ok) {
                    status.text = "Attivo: ${model.name} · caricato in ${ms / 1000} s"
                    bubble("assistant", "Modello attivo. Nessun testo lascia il telefono.")
                    refreshState()
                } else {
                    status.text = "Il modello non si carica: il file potrebbe essere incompleto. Eliminalo e riscaricalo."
                    refreshState()
                }
            }
        }
    }

    // ========================================================
    // Chat
    // ========================================================
    private fun sendMessage() {
        if (generating) return
        val text = input.text.toString().trim()
        if (text.isEmpty()) return
        if (!LocalLlm.isLoaded) {
            Toast.makeText(this, "Prima avvia il modello", Toast.LENGTH_SHORT).show()
            return
        }
        input.setText("")
        bubble("user", text)
        history.add("user" to text)

        generating = true
        send.isEnabled = false
        val waiting = bubble("assistant", "…")

        worker.execute {
            val out = LocalLlm.chat(history, maxTokens = 256)
            runOnUiThread {
                val answer = out.trim().ifEmpty { "Il modello non ha prodotto testo. Riprova con una domanda più breve." }
                waiting.text = answer
                history.add("assistant" to answer)
                generating = false
                send.isEnabled = true
                scroll.post { scroll.fullScroll(View.FOCUS_DOWN) }
            }
        }
    }

    private fun bubble(who: String, text: String): TextView {
        val tv = TextView(this).apply {
            this.text = text
            setTextColor(Color.parseColor("#EDE9E0"))
            textSize = 14.5f
            setLineSpacing(dp(3).toFloat(), 1f)
            setPadding(dp(12), dp(10), dp(12), dp(10))
            setBackgroundColor(
                if (who == "user") Color.parseColor("#1B1A16") else Color.parseColor("#141619")
            )
            setTextIsSelectable(true)
        }
        val lp = LinearLayout.LayoutParams(
            ViewGroup.LayoutParams.WRAP_CONTENT, ViewGroup.LayoutParams.WRAP_CONTENT
        ).apply {
            topMargin = dp(8)
            if (who == "user") {
                gravity = Gravity.END
                leftMargin = dp(48)
            } else {
                gravity = Gravity.START
                rightMargin = dp(48)
            }
        }
        log.addView(tv, lp)
        scroll.post { scroll.fullScroll(View.FOCUS_DOWN) }
        return tv
    }

    private fun dp(v: Int): Int = (v * resources.displayMetrics.density).toInt()

    override fun onDestroy() {
        worker.shutdownNow()
        LocalLlm.unload()
        super.onDestroy()
    }
}
