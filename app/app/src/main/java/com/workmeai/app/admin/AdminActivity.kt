package com.workmeai.app.admin

import android.graphics.Color
import android.os.Bundle
import android.view.View
import android.widget.ArrayAdapter
import android.widget.AutoCompleteTextView
import android.widget.Button
import android.widget.CheckBox
import android.widget.EditText
import android.widget.LinearLayout
import android.widget.RelativeLayout
import android.widget.Spinner
import android.widget.TextView
import androidx.core.view.isVisible
import com.google.android.material.card.MaterialCardView
import com.google.android.material.chip.Chip
import com.google.android.material.dialog.MaterialAlertDialogBuilder
import com.google.android.material.textfield.TextInputLayout
import com.workmeai.app.R
import com.workmeai.app.data.LogItem
import com.workmeai.app.data.MsgItem
import com.workmeai.app.data.Notice
import com.workmeai.app.data.ProjectInfo
import com.workmeai.app.data.Store
import com.workmeai.app.data.Tools
import com.workmeai.app.databinding.ActivityAdminBinding
import com.workmeai.app.net.Api
import com.workmeai.app.ui.BaseActivity
import kotlinx.coroutines.CoroutineScope
import kotlinx.coroutines.Dispatchers
import kotlinx.coroutines.launch
import kotlinx.coroutines.withContext

class AdminActivity : BaseActivity() {

    private lateinit var b: ActivityAdminBinding
    private var currentSection = 0

    private var projects: List<ProjectInfo> = emptyList()
    private var noticesCache: List<Pair<Notice, Int>> = emptyList()
    private var messagesCache: List<Pair<MsgItem, Int>> = emptyList()
    private var logsCache: List<LogItem> = emptyList()

    private suspend fun loadAll() {
        projects = runCatching { Api.syncProjects() }.getOrDefault(
            Tools.all.map { ProjectInfo(it.key, it.name, it.category, "attivo", descriptionIt = it.descIt, descriptionEn = it.descEn, icon = "rocket", color = it.color, url = "https://luxverse.c22.eu/${it.key}.php") }
        )
        val remoteN = runCatching { Api.syncNotices() }.getOrDefault(emptyList())
        val localN = Store.localNotices(this)
        noticesCache = buildList {
            remoteN.forEach { add(Pair(it, -1)) }
            localN.forEachIndexed { idx, n -> add(Pair(n, idx)) }
        }
        val remoteM = runCatching { Api.syncMessages() }.getOrDefault(emptyList())
        val localM = Store.localMessages(this)
        messagesCache = buildList {
            remoteM.forEach { add(Pair(it, -1)) }
            localM.forEachIndexed { idx, m -> add(Pair(m, idx)) }
        }
        val remoteLogs = runCatching { Api.syncLogs() }.getOrDefault(emptyList())
        logsCache = remoteLogs + Store.localLogs(this).map {
            LogItem(it["action"].orEmpty(), it["details"].orEmpty(), it["timestamp"].orEmpty(), it["ip"].orEmpty())
        }
    }

    override fun onCreate(savedInstanceState: Bundle?) {
        super.onCreate(savedInstanceState)
        b = ActivityAdminBinding.inflate(layoutInflater)
        setContentView(b.root)

        b.toolbar.setNavigationOnClickListener { finish() }
        b.toolbar.inflateMenu(R.menu.menu_admin)
        b.toolbar.setOnMenuItemClickListener { item ->
            if (item.itemId == R.id.menu_logout) {
                Store.setAdmin(this, false)
                finish()
                true
            } else false
        }

        buildSections()
        renderSection(0)
    }

    private fun buildSections() {
        val sections = listOf(
            R.string.overview, R.string.projects, R.string.notices, R.string.messages,
            R.string.assistance, R.string.visits, R.string.signatures, R.string.logs
        )
        for ((i, name) in sections.withIndex()) {
            val chip = Chip(this)
            chip.text = getString(name)
            chip.isCheckable = true
            chip.isChecked = i == 0
            chip.tag = i
            chip.setOnClickListener {
                currentSection = i
                for (j in 0 until b.secChips.childCount) {
                    val c = b.secChips.getChildAt(j) as Chip
                    c.isChecked = c.tag == i
                }
                renderSection(i)
            }
            b.secChips.addView(chip)
        }
    }

    private fun renderSection(section: Int) {
        currentSection = section
        b.contentBox.removeAllViews()
        when (section) {
            0 -> renderOverview()
            1 -> renderProjects()
            2 -> renderNotices()
            3 -> renderMessages()
            4 -> renderAssistance()
            5 -> renderVisits()
            6 -> renderSignatures()
            7 -> renderLogs()
        }
    }

    private fun card(): Pair<MaterialCardView, LinearLayout> {
        val card = MaterialCardView(this)
        card.layoutParams = LinearLayout.LayoutParams(
            LinearLayout.LayoutParams.MATCH_PARENT,
            LinearLayout.LayoutParams.WRAP_CONTENT
        ).apply { topMargin = (12 * resources.displayMetrics.density).toInt() }
        card.radius = 16f
        card.setCardBackgroundColor(android.content.res.ColorStateList.valueOf(Color.parseColor("#121417")))
        card.cardElevation = 0f
        card.setStrokeColor(android.content.res.ColorStateList.valueOf(Color.parseColor("#232730")))
        card.setStrokeWidth(1)
        val box = LinearLayout(this)
        box.orientation = LinearLayout.VERTICAL
        box.setPadding(16, 16, 16, 16)
        card.addView(box)
        return Pair(card, box)
    }

    private fun title(box: LinearLayout, text: String) {
        val tv = TextView(this)
        tv.text = text
        tv.setTextColor(Color.parseColor("#FFFFFF"))
        tv.textSize = 16f
        tv.setTypeface(null, android.graphics.Typeface.BOLD)
        box.addView(tv)
    }

    private fun sub(box: LinearLayout, text: String) {
        val tv = TextView(this)
        tv.text = text
        tv.setTextColor(Color.parseColor("#A5A197"))
        tv.textSize = 13f
        box.addView(tv)
    }

    private fun statRow(): Pair<LinearLayout, MutableList<Pair<TextView, TextView>>> {
        val row = LinearLayout(this)
        row.orientation = LinearLayout.HORIZONTAL
        val cells = mutableListOf<Pair<TextView, TextView>>()
        for (i in 0 until 2) {
            val col = LinearLayout(this)
            col.orientation = LinearLayout.VERTICAL
            col.layoutParams = LinearLayout.LayoutParams(0, LinearLayout.LayoutParams.WRAP_CONTENT, 1f)
            val label = TextView(this)
            label.setTextColor(Color.parseColor("#A5A197"))
            label.textSize = 12f
            val value = TextView(this)
            value.setTextColor(Color.parseColor("#FFFFFF"))
            value.textSize = 20f
            value.setTypeface(null, android.graphics.Typeface.BOLD)
            col.addView(label)
            col.addView(value)
            row.addView(col)
            cells.add(Pair(label, value))
        }
        return Pair(row, cells)
    }

    private fun fmtDate(d: String): String = if (d.isEmpty()) "—" else d

    // ---------- 0 OVERVIEW ----------
    private fun renderOverview() {
        val v = Store.getVisits(this)
        val active = projects.count { it.status == "attivo" }

        val row = statRow()
        b.contentBox.addView(row.first)
        row.second[0].first.text = getString(R.string.total_projects)
        row.second[0].second.text = projects.size.toString()
        row.second[1].first.text = getString(R.string.active_projects)
        row.second[1].second.text = active.toString()

        val row2 = statRow()
        b.contentBox.addView(row2.first)
        row2.second[0].first.text = getString(R.string.total_notices)
        row2.second[0].second.text = noticesCache.size.toString()
        row2.second[1].first.text = getString(R.string.total_visits)
        row2.second[1].second.text = v.total.toString()

        val row3 = statRow()
        b.contentBox.addView(row3.first)
        row3.second[0].first.text = getString(R.string.total_messages)
        row3.second[0].second.text = messagesCache.size.toString()
        row3.second[1].first.text = getString(R.string.total_assistance)
        row3.second[1].second.text = Store.localAssist(this).size.toString()

        val (c, box) = card()
        title(box, getString(R.string.latest_activity))
        Store.localLogs(this).take(6).forEach { l ->
            sub(box, "• " + l["timestamp"].orEmpty() + "  " + l["action"].orEmpty())
        }
        b.contentBox.addView(c)

        val (c2, box2) = card()
        title(box2, getString(R.string.latest_messages))
        messagesCache.take(5).forEach { (m, _) ->
            sub(box2, "• " + fmtDate(m.date) + "  " + m.name + " — " + m.project)
        }
        b.contentBox.addView(c2)
    }

    // ---------- 1 PROGETTI ----------
    private fun renderProjects() {
        b.contentBox.addView(loadingCard(getString(R.string.loading)))
        CoroutineScope(Dispatchers.IO).launch {
            loadAll()
            withContext(Dispatchers.Main) {
                b.contentBox.removeAllViews()
                renderProjectsInternal()
                b.tvSync.text = getString(R.string.last_sync,
                    java.text.SimpleDateFormat("HH:mm:ss", java.util.Locale.US).format(java.util.Date()))
            }
        }
    }

    private fun loadingCard(text: String): MaterialCardView {
        val (c, box) = card()
        sub(box, text)
        return c
    }

    private fun renderProjectsInternal() {
        val (c, box) = card()
        title(box, "L.U.X. Verse " + getString(R.string.projects))
        for ((idx, pr) in projects.withIndex()) {
            val row = LinearLayout(this)
            row.orientation = LinearLayout.HORIZONTAL
            row.gravity = android.view.Gravity.CENTER_VERTICAL
            row.setPadding(0, 10, 0, 10)

            val info = LinearLayout(this)
            info.orientation = LinearLayout.VERTICAL
            info.layoutParams = LinearLayout.LayoutParams(0, LinearLayout.LayoutParams.WRAP_CONTENT, 1f)
            val name = TextView(this)
            name.text = pr.name
            name.setTextColor(Color.WHITE)
            name.textSize = 14f
            name.setTypeface(null, android.graphics.Typeface.BOLD)
            val cat = TextView(this)
            cat.text = pr.category
            cat.setTextColor(Color.parseColor("#A5A197"))
            cat.textSize = 12f
            info.addView(name)
            info.addView(cat)
            row.addView(info)

            val spinner = android.widget.EditText(this)
            spinner.isFocusable = false
            spinner.setText(statusLabel(pr.status))
            spinner.setTextColor(Color.WHITE)
            spinner.setOnClickListener {
                val positions = Tools.statuses
                MaterialAlertDialogBuilder(this)
                    .setTitle(getString(R.string.status))
                    .setItems(positions.map { statusLabel(it) }.toTypedArray()) { _, w ->
                        val newStatus = positions[w]
                        CoroutineScope(Dispatchers.IO).launch {
                            val ok = Api.updateProjectStatus(pr.key, newStatus)
                            withContext(Dispatchers.Main) {
                                if (ok) {
                                    spinner.setText(statusLabel(newStatus))
                                    Store.addLog(this@AdminActivity, "STATO_PROGETTO", "${pr.name} -> $newStatus")
                                    toastRes(R.string.project_status_updated)
                                } else {
                                    toastRes(R.string.no_internet)
                                }
                            }
                        }
                    }
                    .show()
            }
            row.addView(spinner)
            box.addView(row)
        }
        b.contentBox.addView(c)
    }

    private fun statusLabel(s: String): String = when (s) {
        "attivo" -> getString(R.string.status_attivo)
        "manutenzione" -> getString(R.string.status_manutenzione)
        "pausa" -> getString(R.string.status_pausa)
        "costruzione" -> getString(R.string.status_costruzione)
        "verifica" -> getString(R.string.status_verifica)
        "bloccato" -> getString(R.string.status_bloccato)
        "archiviato" -> getString(R.string.status_archiviato)
        else -> s
    }

    // ---------- 2 BACHECA ----------
    private fun renderNotices() {
        val (fc, fbox) = card()
        title(fbox, getString(R.string.create_notice))

        val etTitle = EditText(this); etTitle.hint = getString(R.string.title_label)
        val etMsg = EditText(this); etMsg.hint = getString(R.string.message_label); etMsg.minLines = 3
        val etSign = EditText(this); etSign.hint = getString(R.string.signature_label)
        val cbPin = CheckBox(this); cbPin.text = getString(R.string.pin)
        val btnPub = Button(this); btnPub.text = getString(R.string.publish_notice)
        listOf(etTitle, etMsg, etSign, cbPin).forEach { fbox.addView(inputWrap(it)) }
        fbox.addView(btnPub)
        b.contentBox.addView(fc)

        btnPub.setOnClickListener {
            val t = etTitle.text.toString().trim()
            val m = etMsg.text.toString().trim()
            if (t.isEmpty() || m.isEmpty()) { toastRes(R.string.fill_input); return@setOnClickListener }
            Store.addNotice(
                this,
                Notice(t, m, "info", etSign.text.toString().trim().ifEmpty { "L.U.X. Verse Ecosystem" }, "", cbPin.isChecked)
            )
            Store.addLog(this, "BACHECA", "Nuovo avviso: " + t)
            renderNoticesList()
        }
        renderNoticesList()
    }

    private fun inputWrap(v: View): LinearLayout {
        val w = LinearLayout(this)
        w.orientation = LinearLayout.VERTICAL
        w.layoutParams = LinearLayout.LayoutParams(LinearLayout.LayoutParams.MATCH_PARENT, LinearLayout.LayoutParams.WRAP_CONTENT)
        w.setPadding(0, 6, 0, 6)
        if (v is TextView) {
            v.setTextColor(Color.WHITE)
            v.setHintTextColor(Color.parseColor("#A5A197"))
        }
        v.setBackgroundColor(Color.parseColor("#121417"))
        w.addView(v)
        return w
    }

    private fun renderNoticesList() {
        val (c, box) = card()
        title(box, getString(R.string.total_notices) + ": " + noticesCache.size)
        noticesCache.forEachIndexed { idx, (n, localIdx) ->
            val row = LinearLayout(this)
            row.orientation = LinearLayout.VERTICAL
            val name = TextView(this)
            name.text = (if (n.pinned) "PIN • " else "") + n.title
            name.setTextColor(Color.WHITE)
            name.setTypeface(null, android.graphics.Typeface.BOLD)
            val d = TextView(this)
            d.text = (if (n.type.isNotEmpty()) "[${n.type}] " else "") + fmtDate(n.date) + "  —  " + n.signature
            d.setTextColor(Color.parseColor("#A5A197"))
            d.textSize = 12f
            val msg = TextView(this)
            msg.text = n.message
            msg.setTextColor(Color.parseColor("#C9C4BA"))
            msg.textSize = 13f
            row.addView(name); row.addView(d); row.addView(msg)
            if (localIdx >= 0) {
                val actions = LinearLayout(this)
                actions.orientation = LinearLayout.HORIZONTAL
                val pin = Button(this)
                pin.text = if (n.pinned) getString(R.string.unpin) else getString(R.string.pin)
                val del = Button(this)
                del.text = getString(R.string.delete)
                pin.setOnClickListener {
                    Store.togglePinNotice(this, localIdx)
                    renderNotices()
                }
                del.setOnClickListener {
                    Store.removeNotice(this, localIdx)
                    renderNotices()
                }
                actions.addView(pin); actions.addView(del)
                row.addView(actions)
            }
            box.addView(row)
        }
        b.contentBox.addView(c)
    }

    // ---------- 3 MESSAGGI ----------
    private fun renderMessages() {
        val (c, box) = card()
        title(box, getString(R.string.total_messages) + ": " + messagesCache.size)
        if (messagesCache.isEmpty()) {
            sub(box, getString(R.string.no_messages))
        }
        messagesCache.forEach { (m, localIdx) ->
            val row = LinearLayout(this)
            row.orientation = LinearLayout.VERTICAL
            val head = TextView(this)
            head.text = "#" + m.code + "  " + m.name + "  <" + m.email + ">"
            head.setTextColor(Color.WHITE)
            head.setTypeface(null, android.graphics.Typeface.BOLD)
            val meta = TextView(this)
            meta.text = "${getString(R.string.project)}: ${m.project}  •  ${getString(R.string.status)}: ${m.status}  •  ${fmtDate(m.date)}"
            meta.setTextColor(Color.parseColor("#A5A197"))
            meta.textSize = 12f
            val body = TextView(this)
            body.text = m.message
            body.setTextColor(Color.parseColor("#C9C4BA"))
            body.textSize = 13f
            row.addView(head); row.addView(meta); row.addView(body)
            if (localIdx >= 0) {
                val next = when (m.status) {
                    getString(R.string.status_pending) -> getString(R.string.status_progress)
                    getString(R.string.status_progress) -> getString(R.string.status_resolved)
                    getString(R.string.status_resolved) -> getString(R.string.status_archived_msg)
                    else -> getString(R.string.status_pending)
                }
                val btn = Button(this)
                btn.text = getString(R.string.update) + " → " + next
                btn.setOnClickListener {
                    Store.updateLocalMessageStatus(this, localIdx, next)
                    renderMessages()
                }
                row.addView(btn)
            }
            box.addView(row)
        }
        b.contentBox.addView(c)
    }

    // ---------- 4 ASSISTENZA ----------
    private fun renderAssistance() {
        val items = Store.localAssist(this)
        val (c, box) = card()
        title(box, getString(R.string.total_assistance) + ": " + items.size)
        if (items.isEmpty()) sub(box, getString(R.string.no_assistance))
        items.forEachIndexed { idx, a ->
            val row = LinearLayout(this)
            row.orientation = LinearLayout.VERTICAL
            val head = TextView(this)
            head.text = "#" + a.code + "  " + a.name + "  <" + a.email + ">"
            head.setTextColor(Color.WHITE)
            head.setTypeface(null, android.graphics.Typeface.BOLD)
            val meta = TextView(this)
            meta.text = a.category + "  •  " + a.subject + "  •  " + fmtDate(a.date)
            meta.setTextColor(Color.parseColor("#A5A197"))
            meta.textSize = 12f
            val body = TextView(this)
            body.text = a.message
            body.setTextColor(Color.parseColor("#C9C4BA"))
            body.textSize = 13f
            row.addView(head); row.addView(meta); row.addView(body)
            val del = Button(this)
            del.text = getString(R.string.delete)
            del.setOnClickListener {
                Store.removeAssist(this, idx)
                renderAssistance()
            }
            row.addView(del)
            box.addView(row)
        }
        b.contentBox.addView(c)
    }

    // ---------- 5 VISITE ----------
    private fun renderVisits() {
        val v = Store.getVisits(this)
        val row = statRow()
        b.contentBox.addView(row.first)
        row.second[0].first.text = getString(R.string.total_visits)
        row.second[0].second.text = v.total.toString()
        row.second[1].first.text = getString(R.string.visits_unique)
        row.second[1].second.text = v.unique.toString()

        val row2 = statRow()
        b.contentBox.addView(row2.first)
        row2.second[0].first.text = getString(R.string.visits_today)
        row2.second[0].second.text = v.today.toString()
        row2.second[1].first.text = getString(R.string.visits_week)
        row2.second[1].second.text = v.week.toString()

        val row3 = statRow()
        b.contentBox.addView(row3.first)
        row3.second[0].first.text = getString(R.string.visits_month)
        row3.second[0].second.text = v.month.toString()
        row3.second[1].first.text = "Daily"

        val (c, box) = card()
        title(box, "Daily")
        v.daily.take(14).forEach { (day, count) ->
            val bar = LinearLayout(this)
            bar.orientation = LinearLayout.HORIZONTAL
            bar.gravity = android.view.Gravity.CENTER_VERTICAL
            val label = TextView(this)
            label.text = day
            label.setTextColor(Color.parseColor("#A5A197"))
            label.textSize = 11f
            label.layoutParams = LinearLayout.LayoutParams((100 * resources.displayMetrics.density).toInt(), LinearLayout.LayoutParams.WRAP_CONTENT)
            val fill = View(this)
            val w = (count.coerceAtMost(200) * 1.6f * resources.displayMetrics.density).toInt()
            fill.layoutParams = LinearLayout.LayoutParams(w.coerceAtLeast(4), (18 * resources.displayMetrics.density).toInt())
            fill.background = android.graphics.drawable.GradientDrawable().apply {
                cornerRadius = 9f
                setColor(Color.parseColor("#D8A93C"))
            }
            val cnt = TextView(this)
            cnt.text = count.toString()
            cnt.setTextColor(Color.WHITE)
            cnt.textSize = 11f
            cnt.layoutParams = LinearLayout.LayoutParams(LinearLayout.LayoutParams.WRAP_CONTENT, LinearLayout.LayoutParams.WRAP_CONTENT)
            cnt.setPadding(8, 0, 0, 0)
            bar.addView(label); bar.addView(fill); bar.addView(cnt)
            box.addView(bar)
        }
        b.contentBox.addView(c)
    }

    // ---------- 6 FIRME ----------
    private fun renderSignatures() {
        val items = Store.signatures(this)
        val (c, box) = card()
        title(box, getString(R.string.total_signatures) + ": " + items.size)
        if (items.isEmpty()) sub(box, getString(R.string.no_signatures))
        items.forEach { s ->
            sub(box, "• " + s["timestamp"].orEmpty() + "  —  " + s["action"].orEmpty() + "  (" + s["url"].orEmpty() + ")")
        }
        b.contentBox.addView(c)
    }

    // ---------- 7 LOG ----------
    private fun renderLogs() {
        val (fc, fbox) = card()
        val btn = Button(this)
        btn.text = getString(R.string.sync_now)
        btn.setOnClickListener {
            CoroutineScope(Dispatchers.IO).launch {
                val remote = runCatching { Api.syncLogs() }.getOrDefault(emptyList())
                withContext(Dispatchers.Main) {
                    logsCache = remote + Store.localLogs(this@AdminActivity).map {
                        LogItem(it["action"].orEmpty(), it["details"].orEmpty(), it["timestamp"].orEmpty(), it["ip"].orEmpty())
                    }
                    renderLogsList()
                }
            }
        }
        fbox.addView(btn)
        b.contentBox.addView(fc)
        renderLogsList()
    }

    private fun renderLogsList() {
        val (c, box) = card()
        title(box, "Log")
        logsCache.take(80).forEach { l ->
            val row = TextView(this)
            row.text = "• " + fmtDate(l.date) + "  [" + l.action + "]  " + l.details + (if (l.ip.isNotEmpty()) "  (" + l.ip + ")" else "")
            row.setTextColor(Color.parseColor("#C9C4BA"))
            row.textSize = 12f
            box.addView(row)
        }
        b.contentBox.addView(c)
    }
}