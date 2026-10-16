package com.workmeai.app.net

import com.workmeai.app.BuildConfig
import com.workmeai.app.data.Notice
import com.workmeai.app.data.ProjectInfo
import kotlinx.coroutines.Dispatchers
import kotlinx.coroutines.withContext
import okhttp3.MediaType.Companion.toMediaType
import okhttp3.OkHttpClient
import okhttp3.Request
import okhttp3.RequestBody.Companion.toRequestBody
import okhttp3.FormBody
import org.json.JSONArray
import org.json.JSONObject
import java.util.concurrent.TimeUnit

object Api {

    val client: OkHttpClient = OkHttpClient.Builder()
        .connectTimeout(20, TimeUnit.SECONDS)
        .readTimeout(90, TimeUnit.SECONDS)
        .writeTimeout(60, TimeUnit.SECONDS)
        .build()

    private val json = "application/json; charset=utf-8".toMediaType()

    private fun get(url: String): JSONObject {
        val req = Request.Builder().url(url).get().build()
        val resp = client.newCall(req).execute()
        val body = resp.body?.string().orEmpty()
        resp.close()
        return JSONObject(body)
    }

    private fun postJson(url: String, payload: JSONObject): JSONObject {
        val req = Request.Builder().url(url)
            .addHeader("Authorization", "Bearer ${BuildConfig.GROQ_API_KEY}")
            .addHeader("Content-Type", "application/json")
            .post(payload.toString().toRequestBody(json))
            .build()
        val resp = client.newCall(req).execute()
        val body = resp.body?.string().orEmpty()
        resp.close()
        return JSONObject(body)
    }

    // --------- AI ---------
    suspend fun groq(system: String, user: String, temperature: Double = 0.7, maxTokens: Int = 2048): String {
        val messages = JSONArray().apply {
            put(JSONObject().put("role", "system").put("content", system))
            put(JSONObject().put("role", "user").put("content", user))
        }
        val payload = JSONObject()
            .put("model", BuildConfig.GROQ_MODEL)
            .put("temperature", temperature)
            .put("max_tokens", maxTokens)
            .put("messages", messages)

        val o = postJson(BuildConfig.GROQ_URL, payload)
        if (o.optString("error").isNotEmpty()) throw Exception(o.optJSONObject("error")?.optString("message") ?: o.optString("error"))
        val content = o.optJSONArray("choices")?.optJSONObject(0)?.optJSONObject("message")?.optString("content").orEmpty()
        if (content.isEmpty()) throw Exception("Risposta vuota")
        return content
    }

    // --------- Sito ---------
    suspend fun syncProjects(): List<ProjectInfo> = withContext(Dispatchers.IO) {
        val o = get("${BuildConfig.SITE_URL}/api/get_projects.php")
        val arr = o.optJSONArray("projects") ?: JSONArray()
        (0 until arr.length()).map { i ->
            val p = arr.getJSONObject(i)
            ProjectInfo(
                key = p.optString("key"),
                name = p.optString("name"),
                category = p.optString("category"),
                status = p.optString("status", "attivo"),
                url = p.optString("url"),
                descriptionEn = p.optString("description_en"),
                descriptionIt = p.optString("description_it"),
                icon = p.optString("icon", "rocket"),
                color = p.optString("color", "#00BFFF")
            )
        }
    }

    suspend fun sendMessage(
        name: String, email: String, project: String, type: String, message: String
    ): String = withContext(Dispatchers.IO) {
        val form = FormBody.Builder()
            .add("name", name)
            .add("email", email)
            .add("project", project)
            .add("type", type)
            .add("message", message)
            .build()
        val req = Request.Builder().url("${BuildConfig.SITE_URL}/api/submit_message.php")
            .post(form)
            .build()
        val resp = client.newCall(req).execute()
        val body = resp.body?.string().orEmpty()
        resp.close()
        val o = JSONObject(body)
        if (!o.optBoolean("success")) throw Exception(o.optString("error", "Errore"))
        o.optString("code")
    }

    suspend fun syncNotices(): List<Notice> = withContext(Dispatchers.IO) {
        val o = get("${BuildConfig.SITE_URL}/api/get_notices.php")
        val arr = o.optJSONArray("notices") ?: JSONArray()
        (0 until arr.length()).map { i ->
            val n = arr.getJSONObject(i)
            Notice(
                title = n.optString("title_en").ifEmpty { n.optString("title") },
                message = n.optString("message_en").ifEmpty { n.optString("message") },
                type = n.optString("type", "info"),
                signature = n.optString("signature", "L.U.X. Verse"),
                date = n.optString("date", ""),
                pinned = n.optBoolean("pinned"),
                local = false
            )
        }
    }

    suspend fun syncMessages(): List<com.workmeai.app.data.MsgItem> = withContext(Dispatchers.IO) {
        val o = get("${BuildConfig.SITE_URL}/api/get_messages.php?token=${BuildConfig.API_TOKEN}")
        val arr = o.optJSONArray("messages") ?: JSONArray()
        (0 until arr.length()).map { i ->
            val m = arr.getJSONObject(i)
            com.workmeai.app.data.MsgItem(
                m.optString("code"), m.optString("name"), m.optString("email"), m.optString("project"),
                m.optString("type"), m.optString("message"), m.optString("date"), m.optString("status"), false
            )
        }
    }

    suspend fun syncLogs(): List<com.workmeai.app.data.LogItem> = withContext(Dispatchers.IO) {
        val o = get("${BuildConfig.SITE_URL}/api/get_logs.php?limit=100&token=${BuildConfig.API_TOKEN}")
        val arr = o.optJSONArray("logs") ?: JSONArray()
        (0 until arr.length()).map { i ->
            val l = arr.getJSONObject(i)
            com.workmeai.app.data.LogItem(l.optString("action"), l.optString("details"), l.optString("timestamp"), l.optString("ip"))
        }
    }

    suspend fun updateProjectStatus(project: String, status: String): Boolean = withContext(Dispatchers.IO) {
        try {
            val o = get("${BuildConfig.SITE_URL}/api/update_status.php?project=$project&status=$status&token=${BuildConfig.API_TOKEN}")
            o.optBoolean("success", false)
        } catch (e: Exception) {
            false
        }
    }

    suspend fun latestVersion(): com.workmeai.app.data.LatestVersion? = withContext(Dispatchers.IO) {
        try {
            val o = get("${BuildConfig.SITE_URL}/api/latest_version.php")
            val msg = o.optJSONObject("message")
            com.workmeai.app.data.LatestVersion(
                code = o.optInt("version_code", 0),
                name = o.optString("version_name", ""),
                url = o.optString("url", ""),
                messageIt = msg?.optString("it").orEmpty(),
                messageEn = msg?.optString("en").orEmpty()
            )
        } catch (e: Exception) {
            null
        }
    }
}