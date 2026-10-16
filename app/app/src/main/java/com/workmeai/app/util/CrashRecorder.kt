package com.workmeai.app.util

import android.content.Context
import android.content.Intent
import android.os.Build
import java.io.PrintWriter
import java.io.StringWriter

object CrashRecorder {

    private const val PREFS = "crash_prefs"
    private const val KEY_LAST = "last_crash"
    private const val KEY_FAULTING = "faulting_class"
    private const val KEY_REP = "report_sent"

    private var installed = false

    fun install(ctx: Context) {
        if (installed) return
        installed = true
        val prev = Thread.getDefaultUncaughtExceptionHandler()
        Thread.setDefaultUncaughtExceptionHandler { thread, throwable ->
            try {
                val prefs = ctx.getSharedPreferences(PREFS, Context.MODE_PRIVATE)
                prefs.edit()
                    .putString(KEY_LAST, stackTrace(throwable))
                    .putString(KEY_FAULTING, throwable.javaClass.name)
                    .putBoolean(KEY_REP, false)
                    .commit()
            } catch (_: Throwable) {
            }
            prev?.uncaughtException(thread, throwable)
        }
    }

    fun last(ctx: Context): String =
        ctx.getSharedPreferences(PREFS, Context.MODE_PRIVATE).getString(KEY_LAST, "").orEmpty()

    fun clear(ctx: Context) {
        ctx.getSharedPreferences(PREFS, Context.MODE_PRIVATE).edit().remove(KEY_LAST).remove(KEY_FAULTING).apply()
    }

    fun deviceInfo(): String {
        return "device=${Build.MANUFACTURER} ${Build.MODEL}\n" +
            "android=${Build.VERSION.RELEASE} (API ${Build.VERSION.SDK_INT})\n" +
            "app=${com.workmeai.app.BuildConfig.VERSION_NAME} (code ${com.workmeai.app.BuildConfig.VERSION_CODE})"
    }

    private fun stackTrace(t: Throwable): String {
        val sw = StringWriter()
        val pw = PrintWriter(sw)
        t.printStackTrace(pw)
        pw.flush()
        val cause = t.cause
        return if (cause != null && cause !== t) {
            sw.toString() + "\n\nCausa:\n" + cause.toString()
        } else {
            sw.toString()
        }
    }

    fun reportIntent(ctx: Context, subject: String, body: String): Intent {
        val i = Intent(Intent.ACTION_SEND)
        i.type = "text/plain"
        i.putExtra(Intent.EXTRA_EMAIL, arrayOf("workmeai.report@gmail.com"))
        i.putExtra(Intent.EXTRA_SUBJECT, subject)
        i.putExtra(Intent.EXTRA_TEXT, body)
        return Intent.createChooser(i, null)
    }
}