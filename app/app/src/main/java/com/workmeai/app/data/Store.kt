package com.workmeai.app.data

import android.content.Context
import org.json.JSONArray
import org.json.JSONObject
import java.text.SimpleDateFormat
import java.util.Date
import java.util.Locale

object Store {

    private const val PREFS = "workme_prefs"
    private const val KEY_LANG = "lang"
    private const val KEY_ADMIN = "admin_on"
    private const val KEY_NOTICES = "local_notices"
    private const val KEY_LOGS = "local_logs"
    private const val KEY_MESSAGES = "local_messages"
    private const val KEY_ASSIST = "local_assist"
    private const val KEY_JOURNAL = "journal"
    private const val KEY_MANAGE = "manage_projects"
    private const val KEY_SIGNS = "signatures"
    private const val KEY_SKIP_UPDATE = "skip_update_code"
    private const val KEY_VISIT_DAILY = "visit_daily"
    private const val KEY_VISIT_TOTAL = "visit_total"
    private const val KEY_ONBOARDED = "onboarded"
    private const val KEY_PRIVACY_ACCEPTED = "privacy_accepted_v1"

    fun prefs(ctx: Context) = ctx.getSharedPreferences(PREFS, Context.MODE_PRIVATE)

    // Lingua
    fun lang(ctx: Context): String = prefs(ctx).getString(KEY_LANG, "it") ?: "it"
    fun setLang(ctx: Context, lang: String) = prefs(ctx).edit().putString(KEY_LANG, lang).apply()

    // Admin
    fun isAdmin(ctx: Context): Boolean = prefs(ctx).getBoolean(KEY_ADMIN, false)
    fun setAdmin(ctx: Context, on: Boolean) = prefs(ctx).edit().putBoolean(KEY_ADMIN, on).apply()

    // Auto-update
    fun skipUpdateCode(ctx: Context): Int = prefs(ctx).getInt(KEY_SKIP_UPDATE, 0)
    fun setSkipUpdateCode(ctx: Context, code: Int) = prefs(ctx).edit().putInt(KEY_SKIP_UPDATE, code).apply()

    // Onboarding privacy (primo avvio)
    fun isOnboarded(ctx: Context): Boolean = prefs(ctx).getBoolean(KEY_ONBOARDED, false)
    fun setOnboarded(ctx: Context) = prefs(ctx).edit().putBoolean(KEY_ONBOARDED, true).apply()
    fun isPrivacyAccepted(ctx: Context): Boolean = prefs(ctx).getBoolean(KEY_PRIVACY_ACCEPTED, false)
    fun setPrivacyAccepted(ctx: Context) = prefs(ctx).edit().putBoolean(KEY_PRIVACY_ACCEPTED, true).apply()

    // Svuota cache locale: elimina i contenuti utente ma conserva lingua, admin e preferenze.
    fun clearLocalData(ctx: Context) {
        val p = prefs(ctx)
        p.edit()
            .remove(KEY_NOTICES)
            .remove(KEY_LOGS)
            .remove(KEY_MESSAGES)
            .remove(KEY_ASSIST)
            .remove(KEY_JOURNAL)
            .remove(KEY_MANAGE)
            .remove(KEY_SIGNS)
            .apply()
    }

    private fun stamp(): String = SimpleDateFormat("yyyy-MM-dd HH:mm:ss", Locale.US).format(Date())
    private fun stampDay(): String = SimpleDateFormat("yyyy-MM-dd", Locale.US).format(Date())

    private fun jsonArr(ctx: Context, key: String): JSONArray {
        val raw = prefs(ctx).getString(key, "[]") ?: "[]"
        return try { JSONArray(raw) } catch (e: Exception) { JSONArray() }
    }
    private fun saveJsonArr(ctx: Context, key: String, arr: JSONArray) =
        prefs(ctx).edit().putString(key, arr.toString()).apply()

    // ---- Visite (app usage) ----
    fun trackOpen(ctx: Context) {
        val p = prefs(ctx)
        val total = p.getInt(KEY_VISIT_TOTAL, 0) + 1
        val day = stampDay()
        val daily = try { JSONObject(p.getString(KEY_VISIT_DAILY, "{}")) } catch (e: Exception) { JSONObject() }
        daily.put(day, daily.optInt(day, 0) + 1)
        p.edit().putInt(KEY_VISIT_TOTAL, total).putString(KEY_VISIT_DAILY, daily.toString()).apply()
    }

    fun getVisits(ctx: Context): VisitStat {
        val p = prefs(ctx)
        val total = p.getInt(KEY_VISIT_TOTAL, 0)
        val daily = try { JSONObject(p.getString(KEY_VISIT_DAILY, "{}")) } catch (e: Exception) { JSONObject() }
        val today = stampDay()
        var todayC = 0; var week = 0; var month = 0
        val list = mutableListOf<Pair<String, Int>>()
        val keys = mutableListOf<String>()
        val it = daily.keys()
        while (it.hasNext()) keys.add(it.next())
        keys.sortByDescending { it }
        for (d in keys) {
            val c = daily.optInt(d, 0)
            if (d == today) todayC = c
            val dm = try { SimpleDateFormat("yyyy-MM-dd", Locale.US).parse(d) } catch (e: Exception) { null }
            if (dm != null) {
                val diff = (System.currentTimeMillis() - dm.time) / 86400000.0
                if (diff < 7) week += c
                if (diff < 30) month += c
            }
            list.add(Pair(d, c))
        }
        list.sortByDescending { it.first }
        return VisitStat(total, todayC, week, month, daily.length(), list.take(30))
    }

    // ---- Log locale ----
    fun addLog(ctx: Context, action: String, details: String, ip: String = "") {
        val arr = jsonArr(ctx, KEY_LOGS)
        val o = JSONObject()
        o.put("action", action); o.put("details", details); o.put("timestamp", stamp()); o.put("ip", ip)
        val out = JSONArray()
        out.put(o)
        for (i in 0 until arr.length()) if (out.length() < 120) out.put(arr.get(i))
        saveJsonArr(ctx, KEY_LOGS, out)
    }
    fun localLogs(ctx: Context): List<Map<String, String>> {
        val arr = jsonArr(ctx, KEY_LOGS)
        return (0 until arr.length()).map { i ->
            val o = arr.getJSONObject(i)
            mapOf("action" to o.optString("action"), "details" to o.optString("details"),
                "timestamp" to o.optString("timestamp"), "ip" to o.optString("ip"))
        }
    }

    // ---- Bacheca locale ----
    fun localNotices(ctx: Context): List<Notice> {
        val arr = jsonArr(ctx, KEY_NOTICES)
        return (0 until arr.length()).map { i ->
            val o = arr.getJSONObject(i)
            Notice(o.optString("title"), o.optString("message"), o.optString("type", "info"),
                o.optString("signature", "L.U.X. Verse Ecosystem"), o.optString("date"), o.optBoolean("pinned"), true)
        }
    }
    fun addNotice(ctx: Context, n: Notice) {
        val cur = jsonArr(ctx, KEY_NOTICES)
        val o = JSONObject()
        o.put("title", n.title); o.put("message", n.message); o.put("type", n.type)
        o.put("signature", n.signature); o.put("date", if (n.date.isEmpty()) stamp() else n.date)
        o.put("pinned", n.pinned)
        cur.put(o)
        saveJsonArr(ctx, KEY_NOTICES, cur)
    }
    fun togglePinNotice(ctx: Context, index: Int) {
        val cur = jsonArr(ctx, KEY_NOTICES)
        if (index in 0 until cur.length()) {
            val o = cur.getJSONObject(index)
            o.put("pinned", !o.optBoolean("pinned"))
            saveJsonArr(ctx, KEY_NOTICES, cur)
        }
    }
    fun removeNotice(ctx: Context, index: Int) {
        val cur = jsonArr(ctx, KEY_NOTICES)
        val out = JSONArray()
        for (i in 0 until cur.length()) if (i != index) out.put(cur.get(i))
        saveJsonArr(ctx, KEY_NOTICES, out)
    }

    // ---- Messaggi locali ----
    fun localMessages(ctx: Context): List<MsgItem> {
        val arr = jsonArr(ctx, KEY_MESSAGES)
        return (0 until arr.length()).map { i ->
            val o = arr.getJSONObject(i)
            MsgItem(o.optString("code"), o.optString("name"), o.optString("email"), o.optString("project"),
                o.optString("type"), o.optString("message"), o.optString("date"), o.optString("status", "In Attesa"), true)
        }
    }
    fun addMessage(ctx: Context, m: MsgItem) {
        val cur = jsonArr(ctx, KEY_MESSAGES)
        val o = JSONObject()
        o.put("code", m.code); o.put("name", m.name); o.put("email", m.email); o.put("project", m.project)
        o.put("type", m.type); o.put("message", m.message); o.put("date", if (m.date.isEmpty()) stamp() else m.date)
        o.put("status", m.status)
        cur.put(o)
        saveJsonArr(ctx, KEY_MESSAGES, cur)
    }
    fun updateLocalMessageStatus(ctx: Context, index: Int, status: String) {
        val cur = jsonArr(ctx, KEY_MESSAGES)
        if (index in 0 until cur.length()) {
            cur.getJSONObject(index).put("status", status)
            saveJsonArr(ctx, KEY_MESSAGES, cur)
        }
    }
    fun removeLocalMessage(ctx: Context, index: Int) {
        val cur = jsonArr(ctx, KEY_MESSAGES)
        val out = JSONArray()
        for (i in 0 until cur.length()) if (i != index) out.put(cur.get(i))
        saveJsonArr(ctx, KEY_MESSAGES, out)
    }

    // ---- Assistenza locale ----
    fun localAssist(ctx: Context): List<AssistItem> {
        val arr = jsonArr(ctx, KEY_ASSIST)
        return (0 until arr.length()).map { i ->
            val o = arr.getJSONObject(i)
            AssistItem(o.optString("code"), o.optString("name"), o.optString("email"), o.optString("category"),
                o.optString("subject"), o.optString("message"), o.optString("date"), o.optString("status", "In Attesa"))
        }
    }
    fun addAssist(ctx: Context, a: AssistItem) {
        val cur = jsonArr(ctx, KEY_ASSIST)
        val o = JSONObject()
        o.put("code", a.code); o.put("name", a.name); o.put("email", a.email); o.put("category", a.category)
        o.put("subject", a.subject); o.put("message", a.message); o.put("date", stamp()); o.put("status", a.status)
        cur.put(o)
        saveJsonArr(ctx, KEY_ASSIST, cur)
    }
    fun removeAssist(ctx: Context, index: Int) {
        val cur = jsonArr(ctx, KEY_ASSIST)
        val out = JSONArray()
        for (i in 0 until cur.length()) if (i != index) out.put(cur.get(i))
        saveJsonArr(ctx, KEY_ASSIST, out)
    }

    // ---- Diario ----
    fun journal(ctx: Context): List<JournalEntry> {
        val arr = jsonArr(ctx, KEY_JOURNAL)
        return (0 until arr.length()).map { i ->
            val o = arr.getJSONObject(i)
            JournalEntry(o.optString("date"), o.optString("text"))
        }
    }
    fun addJournal(ctx: Context, text: String) {
        val cur = jsonArr(ctx, KEY_JOURNAL)
        val o = JSONObject()
        o.put("date", stamp()); o.put("text", text)
        val out = JSONArray()
        out.put(o)
        for (i in 0 until cur.length()) if (out.length() < 200) out.put(cur.get(i))
        saveJsonArr(ctx, KEY_JOURNAL, out)
    }

    // ---- Manage projects ----
    fun manageProjects(ctx: Context): List<Pair<String, String>> {
        val arr = jsonArr(ctx, KEY_MANAGE)
        return (0 until arr.length()).map { i ->
            val o = arr.getJSONObject(i)
            Pair(o.optString("name"), o.optString("date"))
        }
    }
    fun addManageProject(ctx: Context, name: String) {
        val cur = jsonArr(ctx, KEY_MANAGE)
        val o = JSONObject(); o.put("name", name); o.put("date", stampDay())
        cur.put(o)
        saveJsonArr(ctx, KEY_MANAGE, cur)
    }
    fun removeManageProject(ctx: Context, index: Int) {
        val cur = jsonArr(ctx, KEY_MANAGE)
        val out = JSONArray()
        for (i in 0 until cur.length()) if (i != index) out.put(cur.get(i))
        saveJsonArr(ctx, KEY_MANAGE, out)
    }

    // ---- Firme ----
    fun signatures(ctx: Context): List<Map<String, String>> {
        val arr = jsonArr(ctx, KEY_SIGNS)
        return (0 until arr.length()).map { i ->
            val o = arr.getJSONObject(i)
            mapOf("ip" to o.optString("ip"), "action" to o.optString("action"),
                "timestamp" to o.optString("timestamp"), "url" to o.optString("url"))
        }
    }
    fun ensureSignature(ctx: Context) {
        if (jsonArr(ctx, KEY_SIGNS).length() == 0) {
            val o = JSONObject()
            o.put("ip", "per-utente"); o.put("action", "consenso_ctrl_check"); o.put("timestamp", stamp())
            o.put("url", "app:workmeai")
            val arr = JSONArray(); arr.put(o)
            saveJsonArr(ctx, KEY_SIGNS, arr)
        }
    }
}