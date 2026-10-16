package com.workmeai.app.ui

import android.os.Bundle
import androidx.core.view.isVisible
import com.workmeai.app.R
import com.workmeai.app.data.Store
import com.workmeai.app.data.Tools
import com.workmeai.app.databinding.ActivityResumeBinding
import com.workmeai.app.net.Api
import com.workmeai.app.util.shareText
import kotlinx.coroutines.CoroutineScope
import kotlinx.coroutines.Dispatchers
import kotlinx.coroutines.launch
import kotlinx.coroutines.withContext

class ResumeActivity : BaseActivity() {

    private lateinit var b: ActivityResumeBinding

    override fun onCreate(savedInstanceState: Bundle?) {
        super.onCreate(savedInstanceState)
        b = ActivityResumeBinding.inflate(layoutInflater)
        setContentView(b.root)

        val tool = Tools.byKey["workmeresume"] ?: Tools.all.first()
        b.toolbar.title = tool.name
        b.toolbar.setNavigationOnClickListener { finish() }

        b.btnGenerate.setOnClickListener { generate() }
        b.btnShare.setOnClickListener {
            if (b.tvResult.isVisible) shareText(b.tvResult.text.toString())
        }
    }

    private fun generate() {
        val name = b.etName.text?.toString()?.trim().orEmpty()
        val role = b.etRole.text?.toString()?.trim().orEmpty()
        val exp = b.etExp.text?.toString()?.trim().orEmpty()
        val skills = b.etSkills.text?.toString()?.trim().orEmpty()
        if (name.isEmpty() || role.isEmpty()) {
            toastRes(R.string.fill_input)
            return
        }
        val lang = Store.lang(this)
        b.progress.isVisible = true
        b.btnGenerate.isEnabled = false
        CoroutineScope(Dispatchers.IO).launch {
            val result = runCatching {
                Api.groq(
                    Tools.systemPrompt("workmeresume", lang),
                    if (lang == "en")
                        "CV for: $name\nRole: $role\nExperience: ${exp.ifEmpty { "-" }}\nSkills: ${skills.ifEmpty { "-" }}\nWrite a clean professional CV with sections (English)."
                    else
                        "CV per: $name\nRuolo: $role\nEsperienze: ${exp.ifEmpty { "-" }}\nCompetenze: ${skills.ifEmpty { "-" }}\nScrivi un CV professionale pulito con sezioni (italiano)."
                )
            }
            withContext(Dispatchers.Main) {
                b.progress.isVisible = false
                b.btnGenerate.isEnabled = true
                result.onSuccess {
                    b.tvResult.text = com.workmeai.app.util.Markdown.render(it)
                    b.tvResult.isVisible = true
                    b.btnShare.isVisible = true
                }.onFailure {
                    b.tvResult.text = getString(R.string.ai_error)
                    b.tvResult.isVisible = true
                }
            }
        }
    }
}