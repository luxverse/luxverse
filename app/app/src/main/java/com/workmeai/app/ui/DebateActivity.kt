package com.workmeai.app.ui

import android.os.Bundle
import androidx.core.view.isVisible
import com.workmeai.app.R
import com.workmeai.app.data.Store
import com.workmeai.app.data.Tools
import com.workmeai.app.databinding.ActivityDebateBinding
import com.workmeai.app.net.Api
import kotlinx.coroutines.CoroutineScope
import kotlinx.coroutines.Dispatchers
import kotlinx.coroutines.launch
import kotlinx.coroutines.withContext

class DebateActivity : BaseActivity() {

    private lateinit var b: ActivityDebateBinding
    private var topic = ""
    private var side = ""
    private var diff = "facile"

    override fun onCreate(savedInstanceState: Bundle?) {
        super.onCreate(savedInstanceState)
        b = ActivityDebateBinding.inflate(layoutInflater)
        setContentView(b.root)

        val tool = Tools.byKey["workmedebate"] ?: Tools.all.first()
        b.toolbar.title = tool.name
        b.toolbar.setNavigationOnClickListener { finish() }

        b.btnStart.setOnClickListener { start() }
        b.btnReply.setOnClickListener { reply() }
    }

    private fun start() {
        topic = b.etTopic.text?.toString()?.trim().orEmpty()
        side = b.etSide.text?.toString()?.trim().orEmpty()
        if (topic.isEmpty()) {
            toastRes(R.string.fill_input)
            return
        }
        diff = when {
            b.chipAlta.isChecked -> "difficile"
            b.chipMedia.isChecked -> "medio"
            else -> "facile"
        }
        val lang = Store.lang(this)
        val sys = Tools.systemPrompt("workmedebate", lang)
        val opening = "Dibattito su: $topic\nLa mia posizione: ${side.ifEmpty { "a favore" }}\nDifficoltà: $diff\nApri il dibattito con la tua argomentazione contro la mia posizione."
        call(sys, opening)
    }

    private fun reply() {
        val text = b.etReply.text?.toString()?.trim().orEmpty()
        if (text.isEmpty()) return
        b.etReply.text?.clear()
        val lang = Store.lang(this)
        val sys = Tools.systemPrompt("workmedebate", lang)
        call(sys, "La mia nuova obiezione: $text\nRispondi con la tua contro-argomentazione.")
    }

    private fun call(sys: String, user: String) {
        b.progress.isVisible = true
        b.btnStart.isEnabled = false
        CoroutineScope(Dispatchers.IO).launch {
            val result = runCatching { Api.groq(sys, user) }
            withContext(Dispatchers.Main) {
                b.progress.isVisible = false
                b.btnStart.isEnabled = true
                val text = result.getOrElse { getString(R.string.ai_error) + ": " + (it.message ?: "") }
                setChatVisible()
                b.tvDebate.text = com.workmeai.app.util.Markdown.render(text)
                b.tvDebate.visibility = android.view.View.VISIBLE
            }
        }
    }

    private fun setChatVisible() {
        if (!b.chatBox.isVisible) b.chatBox.isVisible = true
    }
}