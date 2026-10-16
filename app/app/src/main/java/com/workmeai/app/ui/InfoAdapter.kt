package com.workmeai.app.ui

import android.view.LayoutInflater
import android.view.ViewGroup
import android.widget.ImageView
import androidx.core.widget.ImageViewCompat
import androidx.recyclerview.widget.RecyclerView
import com.workmeai.app.R
import com.workmeai.app.databinding.ItemInfoBinding
import android.content.res.ColorStateList

data class InfoEntry(
    val title: String,
    val subtitle: String,
    val iconRes: Int,
    val pageKey: String
)

class InfoAdapter(
    private val items: List<InfoEntry>,
    private val onClick: (InfoEntry) -> Unit
) : RecyclerView.Adapter<InfoAdapter.VH>() {

    inner class VH(val b: ItemInfoBinding) : RecyclerView.ViewHolder(b.root)

    override fun onCreateViewHolder(parent: ViewGroup, viewType: Int): VH {
        val b = ItemInfoBinding.inflate(LayoutInflater.from(parent.context), parent, false)
        return VH(b)
    }

    override fun onBindViewHolder(h: VH, position: Int) {
        val e = items[position]
        h.b.ivIcon.setImageResource(e.iconRes)
        ImageViewCompat.setImageTintList(h.b.ivIcon, ColorStateList.valueOf(h.b.root.context.getColor(R.color.gold)))
        h.b.tvTitle.text = e.title
        h.b.tvSub.text = e.subtitle
        h.b.root.setOnClickListener { onClick(e) }
    }

    override fun getItemCount() = items.size
}