package com.workmeai.app.ui

import android.os.Bundle
import android.speech.tts.TextToSpeech
import android.widget.ArrayAdapter
import androidx.core.view.isVisible
import com.workmeai.app.R
import com.workmeai.app.data.Store
import com.workmeai.app.data.Tools
import com.workmeai.app.databinding.ActivityTranslateBinding
import com.workmeai.app.net.Api
import kotlinx.coroutines.CoroutineScope
import kotlinx.coroutines.Dispatchers
import kotlinx.coroutines.launch
import kotlinx.coroutines.withContext
import java.util.Locale

class TranslateActivity : BaseActivity() {

    private lateinit var b: ActivityTranslateBinding
    private var tts: TextToSpeech? = null
    private var lastText = ""

    private val languages = listOf(
        "Italiano", "English", "Français", "Español", "Deutsch", "Português",
        "Nederlands", "Русский", "العربية", "中文", "日本語", "हिन्दी"
    )
    private val codes = listOf(
        Locale.ITALIAN, Locale.ENGLISH, Locale.FRENCH, Locale("es", "ES"), Locale.GERMAN,
        Locale("pt", "PT"), Locale("nl", "NL"), Locale("ru", "RU"), Locale("ar", "AR"),
        Locale.CHINESE, Locale.JAPANESE, Locale("hi", "IN")
    )

    override fun onCreate(savedInstanceState: Bundle?) {
        super.onCreate(savedInstanceState)
        b = ActivityTranslateBinding.inflate(layoutInflater)
        setContentView(b.root)

        val tool = Tools.byKey["workmetranslate"] ?: Tools.all.first()
        b.toolbar.title = tool.name
        b.toolbar.setNavigationOnClickListener { finish() }

        b.spTarget.adapter = ArrayAdapter(this, android.R.layout.simple_spinner_dropdown_item, languages)
        b.spTarget.setSelection(1)

        b.btnTranslate.setOnClickListener { translate() }
        b.btnSpeak.setOnClickListener { speak(lastText) }

        tts = TextToSpeech(this) {}
    }

    private fun translate() {
        val text = b.etSource.text?.toString()?.trim().orEmpty()
        if (text.isEmpty()) { toastRes(R.string.fill_input); return }
        val langName = languages[b.spTarget.selectedItemPosition]
        val lang = Store.lang(this)
        b.progress.isVisible = true
        b.btnTranslate.isEnabled = false
        CoroutineScope(Dispatchers.IO).launch {
            val result = runCatching {
                Api.groq(
                    Tools.systemPrompt("workmetranslate", lang),
                    if (lang == "en") "Translate to $langName, natural and fluent:\n$text" else "Traduci in $langName, naturale e fluente:\n$text"
                )
            }
            withContext(Dispatchers.Main) {
                b.progress.isVisible = false
                b.btnTranslate.isEnabled = true
                result.onSuccess {
                    b.tvResult.text = com.workmeai.app.util.Markdown.render(it)
                    b.tvResult.isVisible = true
                    lastText = it
                    b.btnSpeak.isVisible = true
                }.onFailure {
                    b.tvResult.text = getString(R.string.ai_error)
                    b.tvResult.isVisible = true
                }
            }
        }
    }

    private fun speak(text: String) {
        if (text.isEmpty()) return
        val code = codes[b.spTarget.selectedItemPosition]
        tts?.language = code
        tts?.speak(text, TextToSpeech.QUEUE_FLUSH, null, "tx")
    }

    override fun onDestroy() {
        tts?.stop()
        tts?.shutdown()
        super.onDestroy()
    }
}