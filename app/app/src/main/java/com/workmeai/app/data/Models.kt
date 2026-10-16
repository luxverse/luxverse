package com.workmeai.app.data

data class ToolDef(
    val key: String,
    val name: String,
    val descIt: String,
    val descEn: String,
    val icon: String,
    val color: String,
    val category: String,
    val type: String,
    val runtime: String = "cloud",
    val status: String = "online"
)

data class Notice(
    val title: String,
    val message: String,
    val type: String,
    val signature: String,
    val date: String,
    val pinned: Boolean = false,
    val local: Boolean = false
)

data class LogItem(
    val action: String,
    val details: String,
    val date: String,
    val ip: String = ""
)

data class MsgItem(
    val code: String,
    val name: String,
    val email: String,
    val project: String,
    val type: String,
    val message: String,
    val date: String,
    val status: String,
    val local: Boolean = false
)

data class AssistItem(
    val code: String,
    val name: String,
    val email: String,
    val category: String,
    val subject: String,
    val message: String,
    val date: String,
    val status: String
)

data class JournalEntry(
    val date: String,
    val text: String
)

data class VisitStat(
    val total: Int,
    val today: Int,
    val week: Int,
    val month: Int,
    val unique: Int,
    val daily: List<Pair<String, Int>>
)

data class ProjectInfo(
    val key: String,
    val name: String,
    val category: String,
    val status: String,
    val url: String = "",
    val descriptionEn: String = "",
    val descriptionIt: String = "",
    val icon: String = "rocket",
    val color: String = "#00BFFF"
)

data class LatestVersion(
    val code: Int,
    val name: String,
    val url: String,
    val messageIt: String,
    val messageEn: String
)