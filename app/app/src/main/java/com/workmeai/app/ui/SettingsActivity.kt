package com.workmeai.app.ui

import android.content.Intent
import android.os.Bundle
import android.view.View
import com.workmeai.app.BuildConfig
import com.workmeai.app.R
import com.workmeai.app.data.Store
import com.workmeai.app.databinding.ActivitySettingsBinding

class SettingsActivity : BaseActivity() {

    private lateinit var b: ActivitySettingsBinding

    override fun onCreate(savedInstanceState: Bundle?) {
        super.onCreate(savedInstanceState)
        b = ActivitySettingsBinding.inflate(layoutInflater)
        setContentView(b.root)

        b.toolbar.title = getString(R.string.settings_title)
        b.toolbar.setNavigationOnClickListener { finish() }

        refreshLangButtons()

        b.btnIt.setOnClickListener {
            if (Store.lang(this) != "it") {
                Store.setLang(this, "it")
                recreate()
            }
        }
        b.btnEn.setOnClickListener {
            if (Store.lang(this) != "en") {
                Store.setLang(this, "en")
                recreate()
            }
        }

        b.btnClearCache.setOnClickListener {
            Store.clearLocalData(this)
            Store.addLog(this, "cache_cleared", "utente ha svuotato la cache locale")
            toastRes(R.string.settings_cleared)
        }

        b.rowPrivacy.setOnClickListener {
            startActivity(
                Intent(this, InfoPageActivity::class.java)
                    .putExtra("extra_title", getString(R.string.settings_privacy))
                    .putExtra("extra_page", "privacy.php")
            )
        }

        b.rowAbout.setOnClickListener {
            androidx.appcompat.app.AlertDialog.Builder(this)
                .setTitle(R.string.settings_about)
                .setMessage(
                    "L.U.X. Verse ${BuildConfig.VERSION_NAME} (build ${BuildConfig.VERSION_CODE})\n\n" +
                        getString(R.string.settings_footer)
                )
                .setPositiveButton("OK") { _, _ -> }
                .show()
        }
    }

    private fun refreshLangButtons() {
        val isIt = Store.lang(this) != "en"
        b.btnIt.setBackgroundResource(if (isIt) R.drawable.bg_btn_primary else R.drawable.bg_input)
        b.btnIt.setTextColor(resources.getColor(if (isIt) R.color.text_primary else R.color.text_secondary))
        b.btnEn.setBackgroundResource(if (!isIt) R.drawable.bg_btn_primary else R.drawable.bg_input)
        b.btnEn.setTextColor(resources.getColor(if (!isIt) R.color.text_primary else R.color.text_secondary))
    }
}