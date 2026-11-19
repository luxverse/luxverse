package org.luxverse.app.ui

import android.content.Context
import android.graphics.Canvas
import android.graphics.Paint
import android.util.AttributeSet
import android.view.Choreographer
import android.view.View
import kotlin.math.sin
import kotlin.random.Random

/**
 * NEVE DELL'APP (19/11/2026, 40.1.0)
 *
 * Fiocchi veri disegnati a mano, non un'animazione GIF: nessuna immagine da
 * caricare, nessun costo per la memoria, e si fermano quando la schermata
 * sparisce (onVisibilityChanged / stop), cosi' non consumano batteria
 * dietro le pagine del sito.
 *
 * Tre colori soli, quelli del Natale di L.U.X. Verse: bianco, rosso, oro.
 * Con "Rimuovi animazioni" del telefono attivo non parte affatto.
 */
class LuxSnowView @JvmOverloads constructor(
    context: Context,
    attrs: AttributeSet? = null
) : View(context, attrs) {

    private class Flake(
        var x: Float,
        var y: Float,
        var r: Float,
        var vy: Float,
        var drift: Float,
        var phase: Float,
        val color: Int,
        var alpha: Int
    )

    private val flakes = ArrayList<Flake>(FLAKES)
    private val paint = Paint(Paint.ANTI_ALIAS_FLAG)
    private val colors = intArrayOf(0xFFFFFFFF.toInt(), 0xFFC8102E.toInt(), 0xFFC9A227.toInt())
    private var running = false
    private var lastFrame = 0L
    private var clock = 0f

    private val callback = object : Choreographer.FrameCallback {
        override fun doFrame(frameTimeNanos: Long) {
            if (!running) return
            val now = frameTimeNanos / 1_000_000L
            val delta = if (lastFrame == 0L) 16f else (now - lastFrame).coerceAtMost(48L).toFloat()
            lastFrame = now
            clock += delta / 1000f
            step(delta / 1000f)
            invalidate()
            Choreographer.getInstance().postFrameCallback(this)
        }
    }

    init {
        // Rispetta "Rimuovi animazioni": chi lo tiene acceso non vede movimento.
        val reduce = try {
            android.provider.Settings.Global.getFloat(
                context.contentResolver,
                android.provider.Settings.Global.ANIMATOR_DURATION_SCALE, 1f
            ) == 0f
        } catch (t: Throwable) {
            false
        }
        if (!reduce) {
            val rnd = Random(System.currentTimeMillis())
            repeat(FLAKES) {
                flakes.add(
                    Flake(
                        x = rnd.nextFloat(),
                        y = rnd.nextFloat(),
                        r = 1.1f + rnd.nextFloat() * 3.2f,
                        vy = 0.012f + rnd.nextFloat() * 0.05f,
                        drift = 0.004f + rnd.nextFloat() * 0.012f,
                        phase = rnd.nextFloat() * 6.28f,
                        color = colors[it % colors.size],
                        alpha = 70 + rnd.nextInt(150)
                    )
                )
            }
        }
    }

    private fun step(dt: Float) {
        for (f in flakes) {
            f.y += f.vy * dt * 4f
            f.x += sin(clock * 0.7f + f.phase) * f.drift * dt * 6f
            if (f.y > 1.04f) {
                f.y = -0.04f
                f.x = Random.nextFloat()
            }
            if (f.x < -0.05f) f.x = 1.04f
            if (f.x > 1.05f) f.x = -0.04f
        }
    }

    override fun onDraw(canvas: Canvas) {
        super.onDraw(canvas)
        if (flakes.isEmpty()) return
        val w = width.toFloat()
        val h = height.toFloat()
        for (f in flakes) {
            paint.color = f.color
            paint.alpha = f.alpha
            canvas.drawCircle(f.x * w, f.y * h, f.r * resources.displayMetrics.density, paint)
        }
    }

    fun start() {
        if (flakes.isEmpty() || running) return
        running = true
        lastFrame = 0L
        Choreographer.getInstance().postFrameCallback(callback)
    }

    fun stop() {
        running = false
        Choreographer.getInstance().removeFrameCallback(callback)
    }

    override fun onAttachedToWindow() {
        super.onAttachedToWindow()
        if (visibility == VISIBLE) start()
    }

    override fun onDetachedFromWindow() {
        stop()
        super.onDetachedFromWindow()
    }

    override fun onVisibilityChanged(changedView: View, visibility: Int) {
        super.onVisibilityChanged(changedView, visibility)
        if (visibility == VISIBLE) start() else stop()
    }

    companion object {
        private const val FLAKES = 38
    }
}
