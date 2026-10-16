package com.workmeai.app.ui

import android.os.Bundle
import android.widget.ArrayAdapter
import androidx.core.view.isVisible
import com.workmeai.app.R
import com.workmeai.app.data.Store
import com.workmeai.app.data.Tools
import com.workmeai.app.databinding.ActivityHoroscopeBinding
import com.workmeai.app.net.Api
import kotlinx.coroutines.CoroutineScope
import kotlinx.coroutines.Dispatchers
import kotlinx.coroutines.launch
import kotlinx.coroutines.withContext

class HoroscopeActivity : BaseActivity() {

    private lateinit var b: ActivityHoroscopeBinding

    private val signs = listOf(
        "Ariete", "Toro", "Gemelli", "Cancro", "Leone", "Vergine",
        "Bilancia", "Scorpione", "Sagittario", "Capricorno", "Acquario", "Pesci"
    )

    override fun onCreate(savedInstanceState: Bundle?) {
        super.onCreate(savedInstanceState)
        b = ActivityHoroscopeBinding.inflate(layoutInflater)
        setContentView(b.root)

        val tool = Tools.byKey["workmehoroscope"] ?: Tools.all.first()
        b.toolbar.title = tool.name
        b.toolbar.setNavigationOnClickListener { finish() }

        b.spSign.adapter = ArrayAdapter(this, android.R.layout.simple_spinner_dropdown_item, signs)
        b.btnGenerate.setOnClickListener { generate() }
    }

    private fun generate() {
        val sign = signs[b.spSign.selectedItemPosition]
        val lang = Store.lang(this)
        b.progress.isVisible = true
        b.btnGenerate.isEnabled = false
        CoroutineScope(Dispatchers.IO).launch {
            val result = runCatching {
                Api.groq(
                    Tools.systemPrompt("workmehoroscope", lang),
                    if (lang == "en") "Write today's fun and motivating horoscope for $sign (English)." else "Scrivi l'oroscopo di oggi, divertente e motivante, per il segno $sign (italiano)."
                )
            }
            withContext(Dispatchers.Main) {
                b.progress.isVisible = false
                b.btnGenerate.isEnabled = true
                b.tvResult.text = com.workmeai.app.util.Markdown.render(result.getOrElse { getString(R.string.ai_error) })
            }
        }
    }
}