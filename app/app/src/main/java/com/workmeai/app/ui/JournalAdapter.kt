package com.workmeai.app.ui

import android.view.LayoutInflater
import android.view.ViewGroup
import androidx.recyclerview.widget.RecyclerView
import com.workmeai.app.data.JournalEntry
import com.workmeai.app.databinding.ItemJournalBinding

class JournalAdapter(private val items: List<JournalEntry>) : RecyclerView.Adapter<JournalAdapter.VH>() {

    inner class VH(val b: ItemJournalBinding) : RecyclerView.ViewHolder(b.root)

    override fun onCreateViewHolder(parent: ViewGroup, viewType: Int): VH {
        val b = ItemJournalBinding.inflate(LayoutInflater.from(parent.context), parent, false)
        return VH(b)
    }

    override fun onBindViewHolder(h: VH, position: Int) {
        val e = items[position]
        h.b.tvDate.text = e.date
        h.b.tvText.text = if (e.date == "AI") com.workmeai.app.util.Markdown.render(e.text) else e.text
    }

    override fun getItemCount() = items.size
}