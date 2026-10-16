package com.workmeai.app.ui

import android.os.Bundle
import android.widget.ArrayAdapter
import com.workmeai.app.R
import com.workmeai.app.data.Store
import com.workmeai.app.databinding.ActivityContactFormBinding
import com.google.android.material.dialog.MaterialAlertDialogBuilder
import kotlinx.coroutines.CoroutineScope
import kotlinx.coroutines.Dispatchers
import kotlinx.coroutines.launch
import kotlinx.coroutines.withContext

class ContactFormActivity : BaseActivity() {

    private lateinit var b: ActivityContactFormBinding
    private var mode = "contatti" // "contatti" | "assistenza"

    override fun onCreate(savedInstanceState: Bundle?) {
        super.onCreate(savedInstanceState)
        b = ActivityContactFormBinding.inflate(layoutInflater)
        setContentView(b.root)

        mode = intent.getStringExtra("extra_mode") ?: "contatti"
        b.toolbar.title = getString(if (mode == "assistenza") R.string.page_assistenza else R.string.page_contatti)
        b.toolbar.setNavigationOnClickListener { finish() }

        val types = when (mode) {
            "assistenza" -> listOf(
                getString(R.string.type_support),
                getString(R.string.type_bug),
                getString(R.string.type_info),
                getString(R.string.type_complaint),
                getString(R.string.type_suggestion),
                getString(R.string.type_account),
                getString(R.string.type_privacy),
                getString(R.string.type_other)
            )
            else -> listOf(
                getString(R.string.type_assistance),
                getString(R.string.type_advice),
                getString(R.string.type_bug),
                getString(R.string.type_suggestion),
                getString(R.string.type_complaint),
                getString(R.string.type_other)
            )
        }
        b.spinnerType.adapter = ArrayAdapter(this, android.R.layout.simple_spinner_dropdown_item, types)

        b.btnSend.setOnClickListener {
            val name = b.etName.text?.toString()?.trim().orEmpty()
            val email = b.etEmail.text?.toString()?.trim().orEmpty()
            val project = b.etProject.text?.toString()?.trim().orEmpty()
            val type = b.spinnerType.selectedItem?.toString().orEmpty()
            val message = b.etMessage.text?.toString()?.trim().orEmpty()
            if (name.isEmpty() || email.isEmpty() || project.isEmpty() || message.isEmpty()) {
                toastRes(R.string.fill_input)
                return@setOnClickListener
            }
            send(name, email, project, type, message)
        }
    }

    private fun send(name: String, email: String, project: String, type: String, message: String) {
        b.btnSend.isEnabled = false
        CoroutineScope(Dispatchers.IO).launch {
            val code = withContext(Dispatchers.IO) {
                runCatching { com.workmeai.app.net.Api.sendMessage(name, email, project, type, message) }
                    .getOrElse { "" }
            }
            runOnUiThread {
                b.btnSend.isEnabled = true
                if (code.isEmpty()) {
                    toastRes(R.string.ai_error)
                } else {
                    MaterialAlertDialogBuilder(this@ContactFormActivity)
                        .setTitle(R.string.contact_sent)
                        .setMessage(getString(R.string.contact_code, code))
                        .setPositiveButton(R.string.ok, null)
                        .show()
                }
            }
        }
    }
}