package org.luxverse.app.ui

import android.animation.ValueAnimator
import android.content.Context
import android.graphics.Canvas
import android.graphics.LinearGradient
import android.graphics.Paint
import android.graphics.RectF
import android.graphics.Shader
import android.util.AttributeSet
import android.view.View
import android.view.animation.LinearInterpolator

/**
 * SCHELETRO DI CARICAMENTO (19/11/2026, 40.1.0)
 *
 * Tre barre tonde con una luce che le attraversa: si vede che l'app sta
 * lavorando, senza rotelle che girano a vuoto. E' lo stesso linguaggio dello
 * scheletro del sito (assets/css/fluid.css), cosi' app e sito sembrano la
 * stessa cosa.
 *
 * Colorie' due soli: il fondo delle barre e la luce. Si impostano da fuori
 * (setPalette) quando e' Natale, cosi' resta coerente con il velo.
 */
class LuxSkeletonView @JvmOverloads constructor(
    context: Context,
    attrs: AttributeSet? = null
) : View(context, attrs) {

    private val barPaint = Paint(Paint.ANTI_ALIAS_FLAG)
    private val shinePaint = Paint(Paint.ANTI_ALIAS_FLAG)
    private val rect = RectF()
    private var phase = 0f
    private var animator: ValueAnimator? = null

    /** Fondo delle barre. */
    var barColor: Int = 0xFF1B1F24.toInt()
        set(value) { field = value; invalidate() }

    /** Luce che scorre. */
    var shineColor: Int = 0x33FFFFFF
        set(value) { field = value; invalidate() }

    private val widths = floatArrayOf(0.58f, 0.92f, 0.74f)

    init {
        // Misura fissa: sta dentro una colonna centrata, non si allarga.
    }

    override fun onDraw(canvas: Canvas) {
        super.onDraw(canvas)
        val w = width.toFloat()
        val h = height.toFloat()
        if (w <= 0f || h <= 0f) return
        val rows = widths.size
        val gap = h / (rows * 2f)
        val barH = gap * 0.62f
        val left = w * 0.06f
        val radius = barH / 2f

        for (i in 0 until rows) {
            val top = gap * 0.55f + i * (barH + gap)
            rect.set(left, top, left + w * widths[i], top + barH)
            barPaint.color = barColor
            canvas.drawRoundRect(rect, radius, radius, barPaint)
        }

        // La luce: un gradiente che attraversa tutte le barre insieme.
        val span = w * 0.5f
        val x = -span + (w + span * 2f) * phase
        shinePaint.shader = LinearGradient(
            x - span, 0f, x + span, 0f,
            intArrayOf(shineColor and 0x00FFFFFF, shineColor, shineColor and 0x00FFFFFF),
            floatArrayOf(0f, 0.5f, 1f),
            Shader.TileMode.CLAMP
        )
        canvas.drawRoundRect(
            RectF(left, 0f, w - left, h),
            radius, radius, shinePaint
        )
    }

    fun start() {
        if (animator != null) return
        animator = ValueAnimator.ofFloat(0f, 1f).apply {
            duration = 1500
            repeatCount = ValueAnimator.INFINITE
            interpolator = LinearInterpolator()
            addUpdateListener {
                phase = it.animatedValue as Float
                invalidate()
            }
            start()
        }
    }

    fun stop() {
        animator?.cancel()
        animator = null
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
}
