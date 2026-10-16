package com.workmeai.app.ui

import android.view.LayoutInflater
import android.view.ViewGroup
import androidx.recyclerview.widget.RecyclerView
import com.workmeai.app.databinding.ItemProjectBinding

class ManageAdapter(
    private val items: List<Pair<String, String>>,
    private val onLongClick: (Int) -> Unit
) : RecyclerView.Adapter<ManageAdapter.VH>() {

    inner class VH(val b: ItemProjectBinding) : RecyclerView.ViewHolder(b.root)

    override fun onCreateViewHolder(parent: ViewGroup, viewType: Int): VH {
        val b = ItemProjectBinding.inflate(LayoutInflater.from(parent.context), parent, false)
        return VH(b)
    }

    override fun onBindViewHolder(h: VH, position: Int) {
        h.b.tvName.text = items[position].first
        h.b.tvDate.text = items[position].second
        h.b.root.setOnLongClickListener {
            onLongClick(position)
            true
        }
    }

    override fun getItemCount() = items.size
}