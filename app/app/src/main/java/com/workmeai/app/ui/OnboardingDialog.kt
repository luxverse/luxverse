package com.workmeai.app.ui

import android.app.Activity
import android.content.Intent
import android.os.Build
import android.os.VibrationEffect
import android.os.Vibrator
import android.view.LayoutInflater
import android.view.Window
import android.view.WindowManager
import androidx.appcompat.app.AlertDialog
import com.workmeai.app.R
import com.workmeai.app.data.Store
import com.workmeai.app.databinding.DialogOnboardingBinding

/** Popup iniziale sulla privacy, mostrato al primo avvio. */
class OnboardingDialog(
    private val activity: Activity,
    private val onDone: () -> Unit
) {

    fun show() {
        val b = DialogOnboardingBinding.inflate(LayoutInflater.from(activity))
        val dialog = AlertDialog.Builder(activity)
            .setView(b.root)
            .setCancelable(false)
            .create()

        b.tvPolicy.text = com.workmeai.app.util.Markdown.render(
            activity.getString(R.string.privacy_agree_link)
        )
        b.tvPolicy.setOnClickListener {
            dialog.dismiss()
            activity.startActivity(
                Intent(activity, InfoPageActivity::class.java)
                    .putExtra("extra_title", activity.getString(R.string.settings_privacy))
                    .putExtra("extra_page", "privacy.php")
            )
            onDone()
        }

        b.btnStart.setOnClickListener {
            haptic()
            Store.setPrivacyAccepted(activity)
            Store.setOnboarded(activity)
            dialog.dismiss()
            onDone()
        }

        if (Build.VERSION.SDK_INT >= Build.VERSION_CODES.S) {
            dialog.window?.attributes?.let { attr ->
                attr.blurBehindRadius = dp(24)
            }
        }

        dialog.show()
        // Effetto vetro: finestra semi-trasparente con schermo sfocato dove supportato.
        dialog.window?.let { w ->
            w.setBackgroundDrawable(android.graphics.drawable.ColorDrawable(android.graphics.Color.TRANSPARENT))
            w.addFlags(WindowManager.LayoutParams.FLAG_DIM_BEHIND)
            w.setDimAmount(0.55f)
            applyDecor(w)
        }
    }

    private fun dp(value: Int): Int =
        (value * activity.resources.displayMetrics.density).toInt()

    private fun applyDecor(w: Window) {
        w.setLayout(
            WindowManager.LayoutParams.MATCH_PARENT,
            WindowManager.LayoutParams.WRAP_CONTENT
        )
        w.setGravity(android.view.Gravity.CENTER)
    }

    private fun haptic() {
        runCatching {
            val v = activity.getSystemService(Activity.VIBRATOR_SERVICE) as Vibrator?
            if (v != null && v.hasVibrator()) {
                if (Build.VERSION.SDK_INT >= Build.VERSION_CODES.O) {
                    v.vibrate(VibrationEffect.createOneShot(30, VibrationEffect.DEFAULT_AMPLITUDE))
                } else {
                    @Suppress("DEPRECATION")
                    v.vibrate(30)
                }
            }
        }
    }
}