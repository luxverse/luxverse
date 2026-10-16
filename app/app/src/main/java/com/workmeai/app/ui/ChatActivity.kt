package com.workmeai.app.ui

import android.os.Bundle
import androidx.core.view.isVisible
import androidx.recyclerview.widget.LinearLayoutManager
import com.workmeai.app.databinding.ActivityChatBinding
import com.workmeai.app.data.Store
import com.workmeai.app.data.Tools
import com.workmeai.app.net.Api
import com.workmeai.app.R
import kotlinx.coroutines.CoroutineScope
import kotlinx.coroutines.Dispatchers
import kotlinx.coroutines.launch
import kotlinx.coroutines.withContext

class ChatActivity : BaseActivity() {

    private lateinit var b: ActivityChatBinding
    private var key = ""
    private val messages = mutableListOf<ChatMsg>()
    private val history = mutableListOf<Pair<String, String>>()

    override fun onCreate(savedInstanceState: Bundle?) {
        super.onCreate(savedInstanceState)
        b = ActivityChatBinding.inflate(layoutInflater)
        setContentView(b.root)

        key = intent.getStringExtra("extra_tool_key") ?: "workmeparlare"
        val tool = Tools.byKey[key] ?: Tools.all.first()
        val isIt = Store.lang(this) != "en"

        b.toolbar.title = tool.name
        b.toolbar.setNavigationOnClickListener { finish() }
        b.tvPersona.text = if (isIt) getString(R.string.persona_hint) else getString(R.string.persona_hint_en)
        b.tvPersona.isVisible = key != "clienti" && key != "workmebot"

        b.messageList.layoutManager = LinearLayoutManager(this)
        b.messageList.adapter = ChatAdapter(messages)

        val intro = if (isIt) {
            when (key) {
                "clienti" -> "Ciao! Sono WorkMe.Care, il bot di assistenza più (in)utile della rete. Come posso rovinarti la giornata oggi?"
                "workmebot" -> "Ciao! Sono WorkMe.Bot. Pronto a interagire. Scrivimi qualcosa per i social!"
                else -> "Ciao! Sono " + toolsName(tool.name, isIt) + ". Di cosa vuoi parlare?"
            }
        } else {
            when (key) {
                "clienti" -> "Hi! I'm WorkMe.Care, the most (un)helpful support bot on the web. How can I ruin your day today?"
                "workmebot" -> "Hi! I'm WorkMe.Bot. Ready to interact. Message me something for social media!"
                else -> "Hi! I'm " + toolsName(tool.name, isIt) + ". What do you want to talk about?"
            }
        }
        messages.add(ChatMsg(intro, false))
        b.messageList.adapter?.notifyDataSetChanged()

        b.etMessage.setHint(if (isIt) getString(R.string.type_message) else getString(R.string.type_message_en))
        b.btnSend.setOnClickListener { send() }
    }

    private fun toolsName(name: String, it: Boolean): String =
        if (it) name else name

    private fun send() {
        val text = b.etMessage.text?.toString()?.trim().orEmpty()
        if (text.isEmpty()) return
        b.etMessage.text?.clear()
        messages.add(ChatMsg(text, true))
        b.messageList.adapter?.notifyItemInserted(messages.size - 1)
        b.messageList.scrollToPosition(messages.size - 1)

        history.add(text to "")
        val sys = Tools.systemPrompt(key, Store.lang(this))
        val userPrompt = buildString {
            for (h in history.takeLast(8)) {
                append(h.first).append("\n")
            }
            append(text)
        }

        b.progress.isVisible = true
        b.btnSend.isEnabled = false
        CoroutineScope(Dispatchers.IO).launch {
            val result = runCatching { Api.groq(sys, userPrompt) }
            withContext(Dispatchers.Main) {
                b.progress.isVisible = false
                b.btnSend.isEnabled = true
                val reply = result.getOrElse { getString(R.string.ai_error) + ": " + (it.message ?: "") }
                messages.add(ChatMsg(reply, false))
                history[history.lastIndex] = history.last().first to reply
                b.messageList.adapter?.notifyItemInserted(messages.size - 1)
                b.messageList.scrollToPosition(messages.size - 1)
            }
        }
    }
}