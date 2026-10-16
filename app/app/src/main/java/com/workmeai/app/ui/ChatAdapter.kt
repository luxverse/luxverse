package com.workmeai.app.ui

import android.graphics.Color
import android.graphics.drawable.GradientDrawable
import android.text.method.LinkMovementMethod
import android.view.LayoutInflater
import android.view.ViewGroup
import android.widget.LinearLayout
import androidx.recyclerview.widget.RecyclerView
import com.workmeai.app.databinding.ItemMessageBinding
import com.workmeai.app.util.Markdown

data class ChatMsg(val text: String, val fromUser: Boolean)

class ChatAdapter(
    private val items: MutableList<ChatMsg>,
    private val colored: Boolean = true
) : RecyclerView.Adapter<ChatAdapter.VH>() {

    inner class VH(val b: ItemMessageBinding) : RecyclerView.ViewHolder(b.root)

    override fun onCreateViewHolder(parent: ViewGroup, viewType: Int): VH {
        val b = ItemMessageBinding.inflate(LayoutInflater.from(parent.context), parent, false)
        return VH(b)
    }

    override fun onBindViewHolder(h: VH, position: Int) {
        val m = items[position]
        h.b.tvMessage.text = if (m.fromUser) m.text else Markdown.render(m.text)
        h.b.tvMessage.movementMethod = LinkMovementMethod()
        val d = h.b.root.resources.displayMetrics.density
        val lp = h.b.msgRow.layoutParams as LinearLayout.LayoutParams
        if (m.fromUser) {
            h.b.msgRow.gravity = android.view.Gravity.END
            h.b.tvMessage.setBackgroundResource(com.workmeai.app.R.drawable.bg_msg_out)
            lp.setMargins((100 * d).toInt(), 0, 0, 0)
        } else {
            h.b.msgRow.gravity = android.view.Gravity.START
            h.b.tvMessage.setBackgroundResource(com.workmeai.app.R.drawable.bg_msg_in)
            lp.setMargins(0, 0, (100 * d).toInt(), 0)
        }
        h.b.msgRow.layoutParams = lp

        if (position == items.lastIndex) {
            h.b.root.alpha = 0f
            h.b.root.translationY = (26 * d)
            h.b.root.scaleX = 0.97f
            h.b.root.scaleY = 0.97f
            h.b.root.animate()
                .alpha(1f)
                .translationY(0f)
                .scaleX(1f)
                .scaleY(1f)
                .setDuration(300)
                .setStartDelay(40)
                .start()
        }
    }

    override fun getItemCount() = items.size

    companion object {
        fun circleBg(color: String): GradientDrawable {
            return GradientDrawable().apply {
                shape = GradientDrawable.OVAL
                setColor(Color.parseColor(color))
            }
        }
    }
}