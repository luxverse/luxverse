package com.workmeai.app.ui

import android.os.Bundle
import androidx.core.view.isVisible
import com.workmeai.app.R
import com.workmeai.app.data.Store
import com.workmeai.app.data.Tools
import com.workmeai.app.databinding.ActivityQuizBinding
import com.workmeai.app.net.Api
import org.json.JSONArray
import org.json.JSONObject
import kotlinx.coroutines.CoroutineScope
import kotlinx.coroutines.Dispatchers
import kotlinx.coroutines.launch
import kotlinx.coroutines.withContext

class QuizActivity : BaseActivity() {

    private lateinit var b: ActivityQuizBinding
    private val answers = mutableListOf<Pair<String, Int>>()
    private var index = 0
    private var score = 0
    private var solved = false
    private var questions: List<Q> = emptyList()

    override fun onCreate(savedInstanceState: Bundle?) {
        super.onCreate(savedInstanceState)
        b = ActivityQuizBinding.inflate(layoutInflater)
        setContentView(b.root)

        val tool = Tools.byKey["squizzati"] ?: Tools.all.first()
        b.toolbar.title = tool.name
        b.toolbar.setNavigationOnClickListener { finish() }

        b.btnStart.setOnClickListener { startQuiz() }
        b.btnNewQuiz.setOnClickListener {
            b.quizPanel.isVisible = false
            b.setupPanel.isVisible = true
            b.etTopic.text?.clear()
            index = 0; score = 0
        }
    }

    private fun startQuiz() {
        val topic = b.etTopic.text?.toString()?.trim().orEmpty()
        if (topic.isEmpty()) {
            toastRes(R.string.fill_input)
            return
        }
        b.progress.isVisible = true
        b.btnStart.isEnabled = false
        val lang = Store.lang(this)
        val sys = Tools.systemPrompt("squizzati", lang)
        val prompt = "Crea un quiz di 5 domande su: $topic. Rispondi SOLO in formato JSON valido: [{\"q\":\"domanda\",\"options\":[\"a\",\"b\",\"c\",\"d\"],\"a\":0}, ...] con 'a' = indice (0-3) della risposta corretta."

        CoroutineScope(Dispatchers.IO).launch {
            val raw = runCatching { Api.groq(sys, prompt) }.getOrNull()
            withContext(Dispatchers.Main) {
                b.progress.isVisible = false
                b.btnStart.isEnabled = true
                if (raw == null) {
                    toastRes(R.string.ai_error)
                    return@withContext
                }
                val parsed = parseQuiz(raw)
                if (parsed == null || parsed.isEmpty()) {
                    b.tvFeedback.text = getString(R.string.ai_empty)
                    val empty = QuizData(listOf())
                    showQuiz(empty)
                    return@withContext
                }
                b.setupPanel.isVisible = false
                b.quizPanel.isVisible = true
                showQuiz(QuizData(parsed))
            }
        }
    }

    private data class QuizData(val questions: List<Q>)
    private data class Q(val q: String, val options: List<String>, val a: Int)

    private fun parseQuiz(raw: String): List<Q>? {
        val cleaned = raw.substringAfter("[").substringBeforeLast("]")
        val wrapped = try { JSONArray("[$cleaned]") } catch (e: Exception) { return null }
        val out = mutableListOf<Q>()
        for (i in 0 until wrapped.length()) {
            val o = wrapped.getJSONObject(i)
            val opts = o.optJSONArray("options")
            val options = (0 until opts.length()).map { opts.getString(it) }
            out.add(Q(o.optString("q"), options, o.optInt("a", 0)))
        }
        return out
    }

    private fun showQuiz(q: QuizData) {
        if (q.questions.isEmpty()) return
        answers.clear()
        answers.addAll(q.questions.map { it.q to it.a })
        questions = q.questions
        index = 0; score = 0; solved = false
        renderQuestion(questions[index])
    }

    private fun renderQuestion(q: Q) {
        b.tvProgress.text = getString(R.string.quiz_q, index + 1, answers.size)
        b.tvQuestion.text = q.q
        b.tvFeedback.text = ""
        val buttons = listOf(b.btnAns0, b.btnAns1, b.btnAns2, b.btnAns3)
        buttons.forEach { btn ->
            btn.isVisible = true
            btn.text = ""
            btn.isEnabled = true
        }
        for (i in 0 until 4) {
            if (i < q.options.size) {
                buttons[i].isVisible = true
                buttons[i].text = q.options[i]
                buttons[i].setOnClickListener { choose(i, q) }
            } else {
                buttons[i].isVisible = false
            }
        }
    }

    private fun choose(i: Int, q: Q) {
        if (solved) return
        solved = true
        if (i == q.a) {
            score++
            b.tvFeedback.text = getString(R.string.quiz_right)
        } else {
            b.tvFeedback.text = getString(R.string.quiz_wrong, q.options[q.a])
        }
        listOf(b.btnAns0, b.btnAns1, b.btnAns2, b.btnAns3).forEach { it.isEnabled = false }
        b.root.postDelayed({
            if (index + 1 < questions.size) {
                index++
                solved = false
                renderQuestion(questions[index])
            } else {
                b.tvFeedback.text = getString(R.string.quiz_end, score, answers.size)
                b.btnNewQuiz.isVisible = true
            }
        }, 1200)
    }
}