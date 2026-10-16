package com.workmeai.app.ui

import android.os.Bundle
import android.text.SpannableStringBuilder
import android.text.Spanned
import android.text.style.ImageSpan
import androidx.annotation.DrawableRes
import androidx.core.content.ContextCompat
import androidx.core.view.isVisible
import com.workmeai.app.R
import com.workmeai.app.data.Store
import com.workmeai.app.data.Tools
import com.workmeai.app.databinding.ActivityGamesBinding
import kotlin.random.Random

/**
 * Giochini nativi (indovina il numero + sasso/carta/forbici).
 *
 * Nessuna emoji: le icone sono VectorDrawable generate dallo stesso set SVG
 * del sito (tools/build_android_icons.py) e inserite nel testo come ImageSpan,
 * così restano nitide a ogni densità e coerenti con l'identità della app.
 */
class GamesActivity : BaseActivity() {

    private lateinit var b: ActivityGamesBinding
    private var secret = Random.nextInt(1, 101)
    private var attempts = 0
    private var scoreP = 0
    private var scoreC = 0
    private var isIt = true

    private val log = SpannableStringBuilder()

    override fun onCreate(savedInstanceState: Bundle?) {
        super.onCreate(savedInstanceState)
        b = ActivityGamesBinding.inflate(layoutInflater)
        setContentView(b.root)
        isIt = Store.lang(this) != "en"

        val tool = Tools.byKey["workmegames"] ?: Tools.all.first()
        b.toolbar.title = tool.name
        b.toolbar.setNavigationOnClickListener { finish() }

        b.chipGuess.setOnClickListener {
            b.panelGuess.isVisible = true
            b.panelRps.isVisible = false
        }
        b.chipRps.setOnClickListener {
            b.panelGuess.isVisible = false
            b.panelRps.isVisible = true
            b.tvRpsScore.text = scoreP.toString() + " - " + scoreC.toString()
        }

        b.btnGuess.setOnClickListener { guess() }
        b.btnGuessNew.setOnClickListener {
            secret = Random.nextInt(1, 101)
            attempts = 0
            log.clear()
            b.tvGuessLog.text = log
            b.etGuess.text?.clear()
            b.btnGuessNew.isVisible = false
        }

        b.btnRock.setOnClickListener { rps(0) }
        b.btnPaper.setOnClickListener { rps(1) }
        b.btnScissors.setOnClickListener { rps(2) }
        b.tvRpsScore.text = scoreP.toString() + " - " + scoreC.toString()
    }

    /**
     * Inserisce un'icona dentro il testo (sostituisce lo spazio che le fa da
     * ancora). Se la risorsa non esiste il testo resta pulito: nessun crash.
     */
    private fun appendIcon(target: SpannableStringBuilder, @DrawableRes resId: Int, sizeDp: Int = 16) {
        val drawable = ContextCompat.getDrawable(this, resId) ?: return
        val size = (sizeDp * resources.displayMetrics.density).toInt()
        drawable.setBounds(0, 0, size, size)
        val start = target.length
        target.append("\u00A0")
        target.setSpan(
            ImageSpan(drawable, ImageSpan.ALIGN_BASELINE),
            start,
            start + 1,
            Spanned.SPAN_EXCLUSIVE_EXCLUSIVE
        )
        target.append(" ")
    }

    private fun guess() {
        val n = b.etGuess.text?.toString()?.toIntOrNull()
        if (n == null) {
            toastRes(R.string.fill_input)
            return
        }
        attempts++
        log.append(getString(R.string.guess_try, attempts) + " — ")
        when {
            n == secret -> {
                appendIcon(log, R.drawable.ic_game_target)
                log.append(if (isIt) "Giusto!" else "Correct!").append("\n")
                b.btnGuessNew.isVisible = true
            }
            n < secret -> {
                appendIcon(log, R.drawable.ic_game_up)
                log.append(if (isIt) "Troppo basso" else "Too low").append("\n")
            }
            else -> {
                appendIcon(log, R.drawable.ic_game_down)
                log.append(if (isIt) "Troppo alto" else "Too high").append("\n")
            }
        }
        b.tvGuessLog.text = log
        b.etGuess.text?.clear()
    }

    private fun rps(player: Int) {
        val cpu = Random.nextInt(0, 3)
        val icons = listOf(R.drawable.ic_game_rock, R.drawable.ic_game_paper, R.drawable.ic_game_scissors)

        val outcome = when {
            player == (cpu + 1) % 3 -> {
                scoreP++
                1
            }
            player == cpu -> 0
            else -> {
                scoreC++
                -1
            }
        }

        val result = when (outcome) {
            1 -> if (isIt) "Vinci tu!" else "You win!"
            0 -> if (isIt) "Pareggio" else "Draw"
            else -> if (isIt) "Vinco io!" else "I win!"
        }

        val text = SpannableStringBuilder()
        text.append(if (isIt) "Tu: " else "You: ")
        appendIcon(text, icons[player], 20)
        text.append(if (isIt) "  vs  IA: " else "  vs  AI: ")
        appendIcon(text, icons[cpu], 20)
        text.append("\n")
        when (outcome) {
            1 -> appendIcon(text, R.drawable.ic_game_trophy, 18)
            0 -> appendIcon(text, R.drawable.ic_game_draw, 18)
        }
        text.append(result)

        b.tvRpsResult.text = text
        b.tvRpsScore.text = scoreP.toString() + " - " + scoreC.toString()
    }
}
