package com.workmeai.app.ui

import android.os.Bundle
import androidx.core.view.isVisible
import androidx.recyclerview.widget.LinearLayoutManager
import com.workmeai.app.R
import com.workmeai.app.data.Store
import com.workmeai.app.data.Tools
import com.workmeai.app.databinding.ActivityJournalBinding
import com.workmeai.app.net.Api
import kotlinx.coroutines.CoroutineScope
import kotlinx.coroutines.Dispatchers
import kotlinx.coroutines.launch
import kotlinx.coroutines.withContext

class JournalActivity : BaseActivity() {

    private lateinit var b: ActivityJournalBinding
    private val entries = mutableListOf<com.workmeai.app.data.JournalEntry>()

    override fun onCreate(savedInstanceState: Bundle?) {
        super.onCreate(savedInstanceState)
        b = ActivityJournalBinding.inflate(layoutInflater)
        setContentView(b.root)

        val tool = Tools.byKey["workmejournal"] ?: Tools.all.first()
        b.toolbar.title = tool.name
        b.toolbar.setNavigationOnClickListener { finish() }

        b.journalList.layoutManager = LinearLayoutManager(this)
        reload()

        b.btnSave.setOnClickListener {
            val text = b.etEntry.text?.toString()?.trim().orEmpty()
            if (text.isEmpty()) { toastRes(R.string.fill_input); return@setOnClickListener }
            Store.addJournal(this, text)
            b.etEntry.text?.clear()
            reload()
        }
        b.btnSummary.setOnClickListener { summary() }
    }

    private fun reload() {
        entries.clear()
        entries.addAll(Store.journal(this))
        b.journalList.adapter = JournalAdapter(entries.take(100))
    }

    private fun summary() {
        if (entries.isEmpty()) {
            toastRes(R.string.fill_input)
            return
        }
        b.progress.isVisible = true
        val lang = Store.lang(this)
        val joined = entries.take(20).joinToString("\n") { "• " + it.text }
        CoroutineScope(Dispatchers.IO).launch {
            val result = runCatching {
                Api.groq(
                    Tools.systemPrompt("workmejournal", lang),
                    if (lang == "en") "Summarize my journal in a short encouraging report:\n$joined" else "Riassumi il mio diario in un breve report incoraggiante:\n$joined"
                )
            }
            withContext(Dispatchers.Main) {
                b.progress.isVisible = false
                val text = result.getOrElse { getString(R.string.ai_error) }
                b.journalList.scrollToPosition(0)
                b.journalList.adapter = JournalAdapter(listOf(com.workmeai.app.data.JournalEntry("AI", text)) + entries.take(99))
            }
        }
    }
}