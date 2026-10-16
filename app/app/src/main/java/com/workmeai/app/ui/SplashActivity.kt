package com.workmeai.app.ui

import android.content.Intent
import android.graphics.Color
import android.os.Bundle
import android.os.Handler
import android.os.Looper
import android.view.View
import android.view.animation.DecelerateInterpolator
import android.app.AlertDialog
import androidx.appcompat.app.AppCompatActivity
import com.workmeai.app.R
import com.workmeai.app.util.CrashRecorder
import com.workmeai.app.util.gradientText

class SplashActivity : AppCompatActivity() {

    override fun onCreate(savedInstanceState: Bundle?) {
        super.onCreate(savedInstanceState)
        CrashRecorder.install(this)
        setContentView(R.layout.activity_splash)

        val crash = CrashRecorder.last(this)
        if (crash.isNotBlank()) {
            showCrash(crash)
            return
        }

        startAnimations()
        startLoader()

        if (savedInstanceState == null) {
            Handler(Looper.getMainLooper()).postDelayed({ goHome() }, 1700)
        } else {
            goHome()
        }
    }

    private fun startAnimations() {
        val logo = findViewById<View>(R.id.splashLogo)
        logo.alpha = 0f
        logo.scaleX = 0.55f
        logo.scaleY = 0.55f
        logo.animate()
            .alpha(1f)
            .scaleX(1f)
            .scaleY(1f)
            .setDuration(700)
            .setInterpolator(DecelerateInterpolator())
            .start()

        val title = findViewById<android.widget.TextView>(R.id.splashTitle)
        title.alpha = 0f
        title.gradientText(
            Color.parseColor("#EDE9E0"),
            Color.parseColor("#D8A93C"),
            Color.parseColor("#F0CE86")
        )
        title.animate()
            .alpha(1f)
            .setStartDelay(260)
            .setDuration(600)
            .start()
    }

    private fun startLoader() {
        val bar = findViewById<View>(R.id.loaderBar)
        bar.scaleX = 0f
        bar.pivotX = 0f
        bar.animate()
            .scaleX(1f)
            .setDuration(1500)
            .setInterpolator(DecelerateInterpolator())
            .start()
    }

    private fun showCrash(crash: String) {
        CrashRecorder.clear(this)
        AlertDialog.Builder(this)
            .setTitle("Errore alla partenza")
            .setMessage("L'app è uscita la volta scorsa per questo motivo:\n\n$crash\n\nTelefono:\n${CrashRecorder.deviceInfo()}")
            .setPositiveButton("Copia e continua") { _, _ ->
                copyToClipboard(crash)
                goHome()
            }
            .setCancelable(false)
            .show()
    }

    private fun copyToClipboard(text: String) {
        val cm = getSystemService(android.content.ClipboardManager::class.java)
        cm.setPrimaryClip(android.content.ClipData.newPlainText("crash", text))
    }

    private fun goHome() {
        startActivity(Intent(this, MainActivity::class.java))
        overridePendingTransition(android.R.anim.fade_in, android.R.anim.fade_out)
        finish()
    }
}