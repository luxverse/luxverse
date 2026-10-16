package com.workmeai.app.util

import android.content.Context
import android.content.Intent
import android.content.res.Configuration
import android.widget.Toast
import androidx.core.content.ContextCompat
import java.util.Locale

object LocaleHelper {
    fun apply(ctx: Context): Context {
        val lang = com.workmeai.app.data.Store.lang(ctx)
        val config = Configuration(ctx.resources.configuration)
        config.setLocale(Locale(lang))
        config.setLayoutDirection(Locale(lang))
        return ctx.createConfigurationContext(config)
    }
}

fun Context.toast(msg: String) = Toast.makeText(this, msg, Toast.LENGTH_SHORT).show()

fun Context.copyText(text: String) {
    val cm = ContextCompat.getSystemService(this, android.content.ClipboardManager::class.java)
    if (cm != null) {
        cm.setPrimaryClip(android.content.ClipData.newPlainText("workmeai", text))
        toast(getString(com.workmeai.app.R.string.copied))
    }
}

fun Context.shareText(text: String) {
    val i = Intent(Intent.ACTION_SEND).apply {
        type = "text/plain"
        putExtra(Intent.EXTRA_TEXT, text)
    }
    startActivity(Intent.createChooser(i, null))
}

fun nowStamp(): String = java.text.SimpleDateFormat("yyyy-MM-dd HH:mm:ss", Locale.US).format(java.util.Date())

/** Applica un gradiente orizzontale al testo di una TextView (tipo i titoli del sito). */
fun android.widget.TextView.gradientText(vararg colors: Int) {
    post {
        if (!isAttachedToWindow || width <= 0 || height <= 0) return@post
        paint?.shader = android.graphics.LinearGradient(
            0f, 0f, width.toFloat(), 0f,
            colors, null, android.graphics.Shader.TileMode.CLAMP
        )
        invalidate()
    }
}