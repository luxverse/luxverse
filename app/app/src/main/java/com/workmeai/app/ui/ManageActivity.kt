package com.workmeai.app.ui

import android.os.Bundle
import androidx.recyclerview.widget.LinearLayoutManager
import com.workmeai.app.R
import com.workmeai.app.data.Store
import com.workmeai.app.data.Tools
import com.workmeai.app.databinding.ActivityManageBinding
import com.google.android.material.dialog.MaterialAlertDialogBuilder

class ManageActivity : BaseActivity() {

    private lateinit var b: ActivityManageBinding
    private val projects = mutableListOf<Pair<String, String>>()

    override fun onCreate(savedInstanceState: Bundle?) {
        super.onCreate(savedInstanceState)
        b = ActivityManageBinding.inflate(layoutInflater)
        setContentView(b.root)

        val tool = Tools.byKey["workmemanage"] ?: Tools.all.first()
        b.toolbar.title = tool.name
        b.toolbar.setNavigationOnClickListener { finish() }

        b.projectList.layoutManager = LinearLayoutManager(this)
        reload()

        b.btnAdd.setOnClickListener {
            val name = b.etName.text?.toString()?.trim().orEmpty()
            if (name.isEmpty()) { toastRes(R.string.fill_input); return@setOnClickListener }
            Store.addManageProject(this, name)
            b.etName.text?.clear()
            reload()
        }
    }

    private fun reload() {
        projects.clear()
        projects.addAll(Store.manageProjects(this))
        if (projects.isEmpty()) {
            b.projectList.adapter = ManageAdapter(emptyList()) {}
            toastRes(R.string.manage_empty)
        } else {
            b.projectList.adapter = ManageAdapter(projects) { index ->
                MaterialAlertDialogBuilder(this)
                    .setTitle(projects[index].first)
                    .setMessage(getString(R.string.delete) + "?")
                    .setPositiveButton(getString(R.string.delete)) { _, _ ->
                        Store.removeManageProject(this, index)
                        reload()
                    }
                    .setNegativeButton(getString(R.string.cancel), null)
                    .show()
            }
        }
    }
}