package com.workmeai.app.admin

import android.content.Intent
import android.os.Bundle
import androidx.core.widget.doAfterTextChanged
import com.workmeai.app.R
import com.workmeai.app.data.Store
import com.workmeai.app.databinding.ActivityAdminLoginBinding
import com.workmeai.app.ui.BaseActivity

class AdminLoginActivity : BaseActivity() {

    private lateinit var b: ActivityAdminLoginBinding
    private val masterPassword = "5643"

    override fun onCreate(savedInstanceState: Bundle?) {
        super.onCreate(savedInstanceState)
        b = ActivityAdminLoginBinding.inflate(layoutInflater)
        setContentView(b.root)

        if (Store.isAdmin(this)) {
            openAdmin()
            return
        }

        b.btnLogin.setOnClickListener { login() }
        b.etPass.doAfterTextChanged { }
    }

    private fun login() {
        val pass = b.etPass.text?.toString().orEmpty()
        if (pass == masterPassword) {
            Store.setAdmin(this, true)
            Store.addLog(this, "ACCESSO_ADMIN", "Accesso riuscito dalla app Android")
            openAdmin()
        } else {
            b.etPass.error = getString(R.string.wrong_password)
            Store.addLog(this, "ACCESSO_ADMIN_NEGATO", "Tentativo di accesso fallito")
        }
    }

    private fun openAdmin() {
        startActivity(Intent(this, AdminActivity::class.java))
        finish()
    }
}