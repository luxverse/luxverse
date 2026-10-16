package com.workmeai.app.ui

import android.content.Context
import android.view.LayoutInflater
import android.view.ViewGroup
import androidx.recyclerview.widget.RecyclerView
import com.workmeai.app.R
import com.workmeai.app.data.Notice
import com.workmeai.app.databinding.ItemNoticeBinding

class NoticeAdapter(private val items: List<Notice>) : RecyclerView.Adapter<NoticeAdapter.VH>() {

    inner class VH(val b: ItemNoticeBinding) : RecyclerView.ViewHolder(b.root)

    override fun onCreateViewHolder(parent: ViewGroup, viewType: Int): VH {
        val b = ItemNoticeBinding.inflate(LayoutInflater.from(parent.context), parent, false)
        return VH(b)
    }

    override fun onBindViewHolder(h: VH, position: Int) {
        val n = items[position]
        val ctx = h.b.root.context
        val color = noticeTypeColor(n.type)
        h.b.tvType.text = noticeTypeLabel(ctx, n.type)
        h.b.tvType.setTextColor(android.graphics.Color.WHITE)
        h.b.tvType.background = noticePillBg(color)
        h.b.accentBar.background = noticeBarBg(color)
        h.b.tvTitle.text = (if (n.pinned) "PIN • " else "") + n.title
        h.b.tvMsg.text = n.message
        h.b.tvDate.text = n.date

        val d = h.b.root.resources.displayMetrics.density
        h.b.root.alpha = 0f
        h.b.root.translationX = 60 * d
        h.b.root.animate()
            .alpha(1f)
            .translationX(0f)
            .setDuration(420)
            .setStartDelay(position * 80L)
            .start()
    }

    override fun getItemCount() = items.size
}

fun noticeTypeLabel(ctx: Context, type: String): String {
    val res = ctx.resources
    return when (type) {
        "info" -> res.getString(R.string.notice_info).uppercase()
        "warning" -> res.getString(R.string.notice_warning).uppercase()
        "success" -> res.getString(R.string.notice_success).uppercase()
        "danger" -> res.getString(R.string.notice_danger).uppercase()
        "event" -> res.getString(R.string.notice_event).uppercase()
        "update" -> res.getString(R.string.notice_update).uppercase()
        "new" -> res.getString(R.string.notice_new).uppercase()
        "maintenance" -> res.getString(R.string.notice_maintenance).uppercase()
        "tip" -> res.getString(R.string.notice_tip).uppercase()
        "spotlight" -> res.getString(R.string.notice_spotlight).uppercase()
        "celebrate" -> res.getString(R.string.notice_celebrate).uppercase()
        else -> type.uppercase()
    }
}

fun noticeTypeColor(type: String): Int = when (type) {
    "danger" -> android.graphics.Color.parseColor("#D96C5B")
    "warning" -> android.graphics.Color.parseColor("#D8A93C")
    "success" -> android.graphics.Color.parseColor("#57B98A")
    "event" -> android.graphics.Color.parseColor("#D8A93C")
    "update" -> android.graphics.Color.parseColor("#6E9BC4")
    "new" -> android.graphics.Color.parseColor("#D8A93C")
    "maintenance" -> android.graphics.Color.parseColor("#D08A4A")
    "tip" -> android.graphics.Color.parseColor("#D8A93C")
    "spotlight" -> android.graphics.Color.parseColor("#F0CE86")
    "celebrate" -> android.graphics.Color.parseColor("#57B98A")
    else -> android.graphics.Color.parseColor("#D8A93C")
}

fun noticePillBg(color: Int): android.graphics.drawable.GradientDrawable {
    val solid = (color and 0xFFFFFF) or 0x26000000
    val stroke = (color and 0xFFFFFF) or 0x66000000
    return android.graphics.drawable.GradientDrawable().apply {
        shape = android.graphics.drawable.GradientDrawable.RECTANGLE
        cornerRadius = 24.dpToPx().toFloat()
        setColor(solid)
        setStroke(1.dpToPx(), stroke)
    }
}

fun noticeBarBg(color: Int): android.graphics.drawable.GradientDrawable {
    return android.graphics.drawable.GradientDrawable().apply {
        shape = android.graphics.drawable.GradientDrawable.RECTANGLE
        cornerRadius = 3.dpToPx().toFloat()
        setColor(color and 0xFFFFFF or (0xE6000000.toInt()))
    }
}