package com.workmeai.app.ui

import android.graphics.Color
import android.os.Bundle
import android.view.Gravity
import android.widget.Button
import androidx.core.view.isVisible
import com.workmeai.app.R
import com.workmeai.app.data.Store
import com.workmeai.app.data.Tools
import com.workmeai.app.databinding.ActivityCalcBinding
import com.workmeai.app.net.Api
import kotlinx.coroutines.CoroutineScope
import kotlinx.coroutines.Dispatchers
import kotlinx.coroutines.launch
import kotlinx.coroutines.withContext
import android.widget.GridLayout
import android.widget.LinearLayout
import java.util.Stack

class CalcActivity : BaseActivity() {

    private lateinit var b: ActivityCalcBinding
    private var expr = ""
    private var lastResult: Double? = null

    override fun onCreate(savedInstanceState: Bundle?) {
        super.onCreate(savedInstanceState)
        b = ActivityCalcBinding.inflate(layoutInflater)
        setContentView(b.root)

        val tool = Tools.byKey["workmecalc"] ?: Tools.all.first()
        b.toolbar.title = tool.name
        b.toolbar.setNavigationOnClickListener { finish() }

        buildGrid()
        b.btnExplain.setOnClickListener { explain() }
    }

    private fun buildGrid() {
        val keys = listOf("C", "(", ")", "÷", "7", "8", "9", "×", "4", "5", "6", "−", "1", "2", "3", "+", "0", ".", "⌫", "=")
        b.btnGrid.removeAllViews()
        val d = resources.displayMetrics.density
        for (i in keys.indices) {
            val btn = Button(this)
            btn.text = keys[i]
            btn.setTextSize(18f)
            btn.setTextColor(Color.WHITE)
            btn.background = roundedBg()
            val lp = GridLayout.LayoutParams()
            lp.width = 0
            lp.height = (58 * d).toInt()
            lp.rowSpec = GridLayout.spec(i / 4, 1f)
            lp.columnSpec = GridLayout.spec(i % 4, 1f)
            lp.setMargins((6 * d).toInt(), (6 * d).toInt(), (6 * d).toInt(), (6 * d).toInt())
            btn.layoutParams = lp
            btn.setOnClickListener { key(keys[i]) }
            b.btnGrid.addView(btn)
        }
    }

    private fun roundedBg(): android.graphics.drawable.GradientDrawable =
        android.graphics.drawable.GradientDrawable().apply {
            cornerRadius = 18f
            setColor(Color.parseColor("#1D2126"))
        }

    private fun key(k: String) {
        when (k) {
            "C" -> { expr = ""; lastResult = null }
            "⌫" -> if (expr.isNotEmpty()) expr = expr.dropLast(1)
            "=" -> {
                val r = runCatching { evaluate(expr) }.getOrNull()
                if (r != null) {
                    lastResult = r
                    b.tvExpr.text = expr
                    b.tvDisplay.text = fmt(r)
                    expr = ""; return
                } else {
                    b.tvDisplay.text = "Errore"
                    expr = ""; return
                }
            }
            else -> expr += symbol(k)
        }
        b.tvDisplay.text = if (expr.isEmpty()) "0" else expr
    }

    private fun symbol(k: String) = when (k) {
        "÷" -> "/"
        "×" -> "*"
        "−" -> "-"
        else -> k
    }

    private fun fmt(v: Double): String =
        if (v == v.toLong().toDouble()) v.toLong().toString() else v.toString()

    private fun evaluate(expression: String): Double {
        val tokens = tokenize(expression)
        return toPostfix(tokens).let { evalPostfix(it) }
    }

    private fun tokenize(e: String): List<String> {
        val out = mutableListOf<String>()
        var i = 0
        while (i < e.length) {
            val c = e[i]
            if (c.isDigit() || c == '.') {
                val sb = StringBuilder()
                while (i < e.length && (e[i].isDigit() || e[i] == '.')) { sb.append(e[i]); i++ }
                out.add(sb.toString())
            } else {
                out.add(c.toString()); i++
            }
        }
        return out
    }

    private fun toPostfix(tokens: List<String>): List<String> {
        val out = mutableListOf<String>()
        val ops = Stack<String>()
        val prec = mapOf("+" to 1, "-" to 1, "*" to 2, "/" to 2, "(" to 0, ")" to 0)
        for (t in tokens) {
            when {
                t.all { it.isDigit() || it == '.' } -> out.add(t)
                t == "(" -> ops.push(t)
                t == ")" -> {
                    while (ops.isNotEmpty() && ops.peek() != "(") out.add(ops.pop())
                    if (ops.isNotEmpty()) ops.pop()
                }
                else -> {
                    while (ops.isNotEmpty() && (prec[ops.peek()] ?: 0) >= (prec[t] ?: 0)) out.add(ops.pop())
                    ops.push(t)
                }
            }
        }
        while (ops.isNotEmpty()) out.add(ops.pop())
        return out
    }

    private fun evalPostfix(post: List<String>): Double {
        val st = Stack<Double>()
        for (t in post) {
            if (t.all { it.isDigit() || it == '.' }) {
                st.push(t.toDouble())
            } else {
                val b = st.pop(); val a = st.pop()
                st.push(
                    when (t) {
                        "+" -> a + b
                        "-" -> a - b
                        "*" -> a * b
                        "/" -> a / b
                        else -> throw IllegalArgumentException()
                    }
                )
            }
        }
        return st.pop()
    }

    private fun explain() {
        val calcStr = if (lastResult != null) "${b.tvExpr.text} = ${fmt(lastResult!!)}" else expr
        if (calcStr.isBlank()) {
            toastRes(R.string.fill_input)
            return
        }
        b.progress.isVisible = true
        b.btnExplain.isEnabled = false
        val lang = Store.lang(this)
        CoroutineScope(Dispatchers.IO).launch {
            val result = runCatching {
                Api.groq(
                    Tools.systemPrompt("workmecalc", lang),
                    if (lang == "en") "Explain step by step this calculation: $calcStr" else "Spiega passo passo questo calcolo: $calcStr"
                )
            }
            withContext(Dispatchers.Main) {
                b.progress.isVisible = false
                b.btnExplain.isEnabled = true
                b.tvExpr.text = com.workmeai.app.util.Markdown.render(result.getOrElse { getString(R.string.ai_error) })
            }
        }
    }
}