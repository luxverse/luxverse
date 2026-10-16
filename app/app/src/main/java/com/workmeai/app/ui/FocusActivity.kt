package com.workmeai.app.ui

import android.os.Bundle
import android.os.CountDownTimer
import androidx.core.view.isVisible
import com.workmeai.app.R
import com.workmeai.app.data.Store
import com.workmeai.app.data.Tools
import com.workmeai.app.databinding.ActivityFocusBinding
import com.workmeai.app.net.Api
import kotlinx.coroutines.CoroutineScope
import kotlinx.coroutines.Dispatchers
import kotlinx.coroutines.launch
import kotlinx.coroutines.withContext
import java.util.Locale

class FocusActivity : BaseActivity() {

    private lateinit var b: ActivityFocusBinding
    private var timer: CountDownTimer? = null
    private var remainingMs = 25 * 60 * 1000L
    private var workPhase = true
    private var pomodoros = 0
    private var running = false

    override fun onCreate(savedInstanceState: Bundle?) {
        super.onCreate(savedInstanceState)
        b = ActivityFocusBinding.inflate(layoutInflater)
        setContentView(b.root)

        val tool = Tools.byKey["workmefocus"] ?: Tools.all.first()
        b.toolbar.title = tool.name
        b.toolbar.setNavigationOnClickListener { finish() }

        val chips = listOf(b.chip25 to 25, b.chip50 to 50, b.chip10 to 10)
        chips.forEach { (chip, minutes) ->
            chip.setOnClickListener {
                if (!running) {
                    remainingMs = minutes * 60 * 1000L
                    updateLabel()
                }
            }
        }
        b.btnStart.setOnClickListener { toggle() }
        b.btnReset.setOnClickListener { reset() }
        b.btnReport.setOnClickListener { report() }

        updateLabel()
    }

    private fun updateLabel() {
        val s = (remainingMs / 1000).toInt()
        b.tvTimer.text = String.format(Locale.US, "%02d:%02d", s / 60, s % 60)
        b.tvPhase.text = if (workPhase) getString(R.string.focus_work) else getString(R.string.focus_break)
    }

    private fun toggle() {
        if (running) {
            timer?.cancel()
            running = false
            b.btnStart.setText(R.string.focus_resume)
        } else {
            running = true
            b.btnStart.setText(R.string.focus_pause)
            timer = object : CountDownTimer(remainingMs, 1000) {
                override fun onTick(ms: Long) {
                    remainingMs = ms
                    updateLabel()
                }
                override fun onFinish() {
                    running = false
                    b.btnStart.setText(R.string.focus_start)
                    if (workPhase) {
                        pomodoros++
                        b.tvResult.text = getString(R.string.focus_done, pomodoros)
                        workPhase = false
                        remainingMs = 5 * 60 * 1000L
                    } else {
                        workPhase = true
                        remainingMs = 25 * 60 * 1000L
                    }
                    updateLabel()
                }
            }.start()
        }
    }

    private fun reset() {
        timer?.cancel()
        running = false
        workPhase = true
        remainingMs = 25 * 60 * 1000L
        b.btnStart.setText(R.string.focus_start)
        updateLabel()
    }

    private fun report() {
        b.progress.isVisible = true
        val lang = Store.lang(this)
        CoroutineScope(Dispatchers.IO).launch {
            val result = runCatching {
                Api.groq(
                    Tools.systemPrompt("workmefocus", lang),
                    if (lang == "en") "I completed $pomodoros pomodoro sessions today. Give me a short report and tips." else "Ho completato $pomodoros sessioni pomodoro oggi. Dammi un breve report e consigli."
                )
            }
            withContext(Dispatchers.Main) {
                b.progress.isVisible = false
                b.tvResult.text = com.workmeai.app.util.Markdown.render(result.getOrElse { getString(R.string.ai_error) })
            }
        }
    }
}