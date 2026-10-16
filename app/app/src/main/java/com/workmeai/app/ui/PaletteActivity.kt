package com.workmeai.app.ui

import android.graphics.Color
import android.os.Bundle
import android.view.View
import android.widget.LinearLayout
import android.widget.TextView
import androidx.core.view.isVisible
import com.workmeai.app.R
import com.workmeai.app.data.Store
import com.workmeai.app.data.Tools
import com.workmeai.app.databinding.ActivityPaletteBinding
import com.workmeai.app.net.Api
import kotlinx.coroutines.CoroutineScope
import kotlinx.coroutines.Dispatchers
import kotlinx.coroutines.launch
import kotlinx.coroutines.withContext
import org.json.JSONArray

class PaletteActivity : BaseActivity() {

    private lateinit var b: ActivityPaletteBinding

    override fun onCreate(savedInstanceState: Bundle?) {
        super.onCreate(savedInstanceState)
        b = ActivityPaletteBinding.inflate(layoutInflater)
        setContentView(b.root)

        val tool = Tools.byKey["coloriamo"] ?: Tools.all.first()
        b.toolbar.title = tool.name
        b.toolbar.setNavigationOnClickListener { finish() }

        b.btnGenerate.setOnClickListener { generate() }
    }

    private fun generate() {
        val kw = b.etKeyword.text?.toString()?.trim().orEmpty()
        if (kw.isEmpty()) { toastRes(R.string.fill_input); return }
        val lang = Store.lang(this)
        b.progress.isVisible = true
        b.btnGenerate.isEnabled = false
        CoroutineScope(Dispatchers.IO).launch {
            val result = runCatching {
                Api.groq(
                    Tools.systemPrompt("coloriamo", lang),
                    "Genera una palette di 5 colori per il tema: $kw. Rispondi SOLO con un array JSON di 5 hex validi, es: [\"#FF5733\",\"#33FF57\",\"#3357FF\",\"#F1C40F\",\"#9B59B6\"]"
                )
            }
            withContext(Dispatchers.Main) {
                b.progress.isVisible = false
                b.btnGenerate.isEnabled = true
                val hexes = result.mapCatching { parseHex(it) }.getOrDefault(emptyList())
                if (hexes.size >= 3) {
                    b.swatchRow.removeAllViews()
                    for (h in hexes) {
                        b.swatchRow.addView(makeSwatch(h))
                    }
                    b.swatchRow.isVisible = true
                    b.tvResult.text = hexes.joinToString("\n")
                } else {
                    b.tvResult.text = result.getOrElse { getString(R.string.ai_error) }
                }
            }
        }
    }

    private fun parseHex(text: String): List<String> {
        val cleaned = text.substringAfter("[").substringBefore("]")
        if (cleaned.isBlank()) return emptyList()
        val arr = try { JSONArray("[$cleaned]") } catch (e: Exception) { return emptyList() }
        val out = mutableListOf<String>()
        for (i in 0 until arr.length()) {
            val s = arr.getString(i).trim()
            if (s.matches(Regex("#[0-9A-Fa-f]{6}"))) out.add(s)
        }
        return out
    }

    private fun makeSwatch(hex: String): View {
        val d = resources.displayMetrics.density
        val root = LinearLayout(this)
        root.orientation = LinearLayout.VERTICAL
        root.gravity = android.view.Gravity.CENTER
        val box = View(this)
        box.background = android.graphics.drawable.GradientDrawable().apply {
            shape = android.graphics.drawable.GradientDrawable.RECTANGLE
            cornerRadius = (10 * d)
            setColor(Color.parseColor(hex))
        }
        box.layoutParams = LinearLayout.LayoutParams((120 * d).toInt(), (90 * d).toInt())
        val label = TextView(this)
        label.text = hex.uppercase()
        label.setTextColor(Color.WHITE)
        label.setTextSize(10f)
        label.layoutParams = LinearLayout.LayoutParams(android.view.ViewGroup.LayoutParams.WRAP_CONTENT, android.view.ViewGroup.LayoutParams.WRAP_CONTENT)
        root.addView(box)
        root.addView(label)
        val lp = LinearLayout.LayoutParams(android.view.ViewGroup.LayoutParams.WRAP_CONTENT, android.view.ViewGroup.LayoutParams.WRAP_CONTENT)
        lp.marginEnd = (10 * d).toInt()
        root.layoutParams = lp
        return root
    }
}