package com.workmeai.app.ui

import android.content.Context
import android.os.Bundle
import android.view.View
import android.widget.Toast
import androidx.annotation.StringRes
import androidx.appcompat.app.AppCompatActivity
import androidx.core.view.ViewCompat
import androidx.core.view.WindowCompat
import androidx.core.view.WindowInsetsCompat
import com.workmeai.app.util.LocaleHelper

abstract class BaseActivity : AppCompatActivity() {

    override fun attachBaseContext(newBase: Context) {
        super.attachBaseContext(LocaleHelper.apply(newBase))
    }

    override fun onCreate(savedInstanceState: Bundle?) {
        super.onCreate(savedInstanceState)
        WindowCompat.setDecorFitsSystemWindows(window, true)
        applySystemBarsPadding()
    }

    /** Su Android 15+ il target 36 forza edge-to-edge: applica padding su status bar e navbar. */
    private fun applySystemBarsPadding() {
        window.decorView.post {
            val content = window.decorView.findViewById<View>(android.R.id.content)
            ViewCompat.setOnApplyWindowInsetsListener(content) { v, insets ->
                val bars = insets.getInsets(WindowInsetsCompat.Type.systemBars())
                v.setPadding(bars.left, bars.top, bars.right, bars.bottom)
                WindowInsetsCompat.CONSUMED
            }
            content.requestApplyInsets()
        }
    }

    fun toastRes(@StringRes id: Int) {
        Toast.makeText(this, getString(id), Toast.LENGTH_SHORT).show()
    }
}