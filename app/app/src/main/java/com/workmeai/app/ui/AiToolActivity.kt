package com.workmeai.app.ui

import android.os.Bundle
import androidx.core.view.isVisible
import com.workmeai.app.R
import com.workmeai.app.data.Store
import com.workmeai.app.data.Tools
import com.workmeai.app.databinding.ActivityAiToolBinding
import com.workmeai.app.net.Api
import com.workmeai.app.util.copyText
import com.workmeai.app.util.shareText
import kotlinx.coroutines.CoroutineScope
import kotlinx.coroutines.Dispatchers
import kotlinx.coroutines.launch
import kotlinx.coroutines.withContext

class AiToolActivity : BaseActivity() {

    private lateinit var b: ActivityAiToolBinding
    private var key = ""
    private val scope = CoroutineScope(Dispatchers.IO)

    override fun onCreate(savedInstanceState: Bundle?) {
        super.onCreate(savedInstanceState)
        b = ActivityAiToolBinding.inflate(layoutInflater)
        setContentView(b.root)

        key = intent.getStringExtra("extra_tool_key") ?: "infonazionale"
        val tool = Tools.byKey[key] ?: Tools.all.first()
        val isIt = Store.lang(this) != "en"

        b.toolbar.title = tool.name
        b.toolbar.setNavigationOnClickListener { finish() }

        b.tvLabel.text = toolLabel(tool.key, isIt)
        b.tvResult.text = ""

        if (tool.key == "comparami" || tool.key == "riscivili") {
            b.tvLabel2.isVisible = true
            b.etInput2.isVisible = true
            b.tvLabel2.text = if (isIt) getString(R.string.input2_label) else getString(R.string.input2_label_en)
            b.etInput2.hint = if (isIt) getString(R.string.input2_hint) else getString(R.string.input2_hint_en)
            b.etInput.hint = if (isIt) getString(R.string.input_hint) else getString(R.string.input_hint_en)
        }

        b.btnGenerate.setOnClickListener { run() }
        b.btnCopy.setOnClickListener { copyText(b.tvResult.text.toString()) }
        b.btnShare.setOnClickListener { shareText(b.tvResult.text.toString()) }
        b.btnClear.setOnClickListener {
            b.etInput.text?.clear()
            b.etInput2.text?.clear()
            if (b.tvResult.isVisible) {
                b.tvResult.isVisible = false
                b.actionRow.isVisible = false
            }
        }
    }

    private fun toolLabel(key: String, isIt: Boolean): String = when (key) {
        "infonazionale" -> if (isIt) getString(R.string.label_info) else getString(R.string.label_info_en)
        "generatorepiani" -> if (isIt) getString(R.string.label_plan) else getString(R.string.label_plan_en)
        "promailer" -> if (isIt) getString(R.string.label_mail) else getString(R.string.label_mail_en)
        "mappaidee" -> if (isIt) getString(R.string.label_mind) else getString(R.string.label_mind_en)
        "comparami" -> if (isIt) getString(R.string.label_versus) else getString(R.string.label_versus_en)
        "facciolo" -> if (isIt) getString(R.string.label_code) else getString(R.string.label_code_en)
        "rinomina" -> if (isIt) getString(R.string.label_name) else getString(R.string.label_name_en)
        "riscivili" -> if (isIt) getString(R.string.label_rewrite) else getString(R.string.label_rewrite_en)
        "spiegalo" -> if (isIt) getString(R.string.label_explain) else getString(R.string.label_explain_en)
        "riassumi" -> if (isIt) getString(R.string.label_short) else getString(R.string.label_short_en)
        "workmerecipe" -> if (isIt) getString(R.string.label_recipe) else getString(R.string.label_recipe_en)
        "workmeschedule" -> if (isIt) getString(R.string.label_schedule) else getString(R.string.label_schedule_en)
        "workmemusic" -> if (isIt) getString(R.string.label_music) else getString(R.string.label_music_en)
        else -> getString(R.string.label_info)
    }

    private fun run() {
        val text = b.etInput.text?.toString()?.trim().orEmpty()
        if (text.isEmpty()) {
            toastRes(R.string.fill_input)
            return
        }
        val second = b.etInput2.text?.toString()?.trim()
        val lang = Store.lang(this)
        val sys = Tools.systemPrompt(key, lang)
        val userPrompt = when (key) {
            "comparami" -> "Confronta: [1] $text" + (if (!second.isNullOrEmpty()) "  [2] $second" else "") + "\nFornisci confronto: prezzo, qualità, pro, contro, verdetto."
            "riscivili" -> "Riscrivi questo testo nello stile: ${second.ifNullOrEmptyS("chiaro e semplice")}\n\n$text"
            else -> text
        }
        b.progress.isVisible = true
        b.btnGenerate.isEnabled = false
        scope.launch {
            val result = runCatching { Api.groq(sys, userPrompt) }
            withContext(Dispatchers.Main) {
                b.progress.isVisible = false
                b.btnGenerate.isEnabled = true
                result.onSuccess {
                    b.tvResult.text = com.workmeai.app.util.Markdown.render(it)
                    b.tvResult.textSize = 15f
                    b.tvResult.isVisible = true
                    b.actionRow.isVisible = true
                }.onFailure {
                    if (it.message?.contains("Risposta vuota") == true) {
                        b.tvResult.text = getString(R.string.ai_empty)
                    } else {
                        b.tvResult.text = getString(R.string.ai_error) + ": " + (it.message ?: "")
                    }
                    b.tvResult.isVisible = true
                    b.actionRow.isVisible = false
                }
            }
        }
    }

    private fun String?.ifNullOrEmptyS(def: String): String = if (isNullOrEmpty()) def else this
}