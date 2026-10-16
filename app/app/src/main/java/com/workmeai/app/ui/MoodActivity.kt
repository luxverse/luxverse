package com.workmeai.app.ui

import android.os.Bundle
import androidx.core.view.isVisible
import com.workmeai.app.R
import com.workmeai.app.data.Store
import com.workmeai.app.data.Tools
import com.workmeai.app.databinding.ActivityMoodBinding
import com.workmeai.app.net.Api
import kotlinx.coroutines.CoroutineScope
import kotlinx.coroutines.Dispatchers
import kotlinx.coroutines.launch
import kotlinx.coroutines.withContext

class MoodActivity : BaseActivity() {

    private lateinit var b: ActivityMoodBinding

    override fun onCreate(savedInstanceState: Bundle?) {
        super.onCreate(savedInstanceState)
        b = ActivityMoodBinding.inflate(layoutInflater)
        setContentView(b.root)

        val tool = Tools.byKey["workmemood"] ?: Tools.all.first()
        b.toolbar.title = tool.name
        b.toolbar.setNavigationOnClickListener { finish() }

        b.btnAnalyse.setOnClickListener { analyse() }
    }

    private fun analyse() {
        val umore = b.slUmore.value.toInt()
        val energia = b.slEnergia.value.toInt()
        val stress = b.slStress.value.toInt()
        val sonno = b.slSonno.value.toInt()
        val notes = b.etNotes.text?.toString()?.trim().orEmpty()
        val lang = Store.lang(this)

        b.progress.isVisible = true
        b.btnAnalyse.isEnabled = false
        CoroutineScope(Dispatchers.IO).launch {
            val result = runCatching {
                Api.groq(
                    Tools.systemPrompt("workmemood", lang),
                    if (lang == "en")
                        "Mood: $umore/10, Energy: $energia/10, Stress: $stress/10, Sleep: $sonno/10. Notes: $notes. Give a score, analysis and kind practical tips."
                    else
                        "Umore: $umore/10, Energia: $energia/10, Stress: $stress/10, Sonno: $sonno/10. Note: $notes. Dai un punteggio, l'analisi e consigli pratici e gentili."
                )
            }
            withContext(Dispatchers.Main) {
                b.progress.isVisible = false
                b.btnAnalyse.isEnabled = true
                b.tvResult.text = com.workmeai.app.util.Markdown.render(result.getOrElse { getString(R.string.ai_error) })
            }
        }
    }
}