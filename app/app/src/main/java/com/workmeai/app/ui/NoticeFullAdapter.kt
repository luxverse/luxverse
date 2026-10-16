package com.workmeai.app.ui

import android.graphics.Color
import android.view.LayoutInflater
import androidx.core.view.isVisible
import android.view.ViewGroup
import androidx.recyclerview.widget.RecyclerView
import com.workmeai.app.data.Notice
import com.workmeai.app.databinding.ItemNoticeFullBinding

class NoticeFullAdapter(
    private val items: List<Notice>
) : RecyclerView.Adapter<NoticeFullAdapter.VH>() {

    inner class VH(val b: ItemNoticeFullBinding) : RecyclerView.ViewHolder(b.root)

    override fun onCreateViewHolder(parent: ViewGroup, viewType: Int): VH {
        val b = ItemNoticeFullBinding.inflate(LayoutInflater.from(parent.context), parent, false)
        return VH(b)
    }

    override fun onBindViewHolder(h: VH, position: Int) {
        val n = items[position]
        val ctx = h.b.root.context
        val color = noticeTypeColor(n.type)
        h.b.tvType.text = noticeTypeLabel(ctx, n.type)
        h.b.tvType.setTextColor(Color.WHITE)
        h.b.tvType.background = noticePillBg(color)
        h.b.accentBar.background = noticeBarBg(color)
        h.b.tvTitle.text = n.title
        h.b.tvMsg.text = n.message
        h.b.tvDate.text = n.date
        h.b.tvSig.text = n.signature
        h.b.tvPin.isVisible = n.pinned

        val pos = h.bindingAdapterPosition
        h.b.root.alpha = 0f
        h.b.root.translationX = 60f
        h.b.root.animate()
            .alpha(1f)
            .translationX(0f)
            .setDuration(360)
            .setStartDelay((pos % 6) * 45L)
            .start()
    }

    override fun getItemCount() = items.size
}