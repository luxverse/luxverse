package com.workmeai.app.ui

import android.content.Intent
import android.os.Bundle
import com.workmeai.app.R
import com.workmeai.app.data.Store
import com.workmeai.app.databinding.ActivityInfoPageBinding
import com.workmeai.app.util.Markdown

class InfoPageActivity : BaseActivity() {

    private lateinit var b: ActivityInfoPageBinding

    override fun onCreate(savedInstanceState: Bundle?) {
        super.onCreate(savedInstanceState)
        b = ActivityInfoPageBinding.inflate(layoutInflater)
        setContentView(b.root)

        val title = intent.getStringExtra("extra_title") ?: ""
        val page = intent.getStringExtra("extra_page") ?: "chi-siamo.php"

        b.toolbar.title = title
        b.toolbar.setNavigationOnClickListener { finish() }

        val lang = if (Store.lang(this) == "en") "en" else "it"
        val content = when (page) {
            "chi-siamo.php" -> SiteContent.chiSiamo(lang)
            "faq.php" -> SiteContent.faq(lang)
            "assistenza.php" -> SiteContent.assistenza(lang)
            "contattaci.php" -> SiteContent.contatti(lang)
            "privacy.php" -> SiteContent.privacy(lang)
            "terms.php" -> SiteContent.termini(lang)
            else -> SiteContent.chiSiamo(lang)
        }
        b.tvBody.text = Markdown.render(content)
        b.tvBody.movementMethod = android.text.method.LinkMovementMethod.getInstance()

        val isFormPage = page == "assistenza.php" || page == "contattaci.php"
        if (isFormPage) {
            b.tvOpen.visibility = android.view.View.VISIBLE
            b.tvOpen.text = getString(if (page == "assistenza.php") R.string.contact_request else R.string.contact_us)
            b.tvOpen.setOnClickListener {
                startActivity(
                    Intent(this, ContactFormActivity::class.java)
                        .putExtra("extra_mode", if (page == "assistenza.php") "assistenza" else "contatti")
                )
            }
        }
    }
}