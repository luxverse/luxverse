package com.workmeai.app.ui

import android.content.Context
import android.content.res.ColorStateList
import android.graphics.Color
import android.view.LayoutInflater
import android.view.ViewGroup
import android.widget.ImageView
import androidx.core.widget.ImageViewCompat
import androidx.recyclerview.widget.RecyclerView
import com.workmeai.app.R
import com.workmeai.app.data.ToolDef
import com.workmeai.app.databinding.ItemToolBinding

class ToolAdapter(
    private val items: List<ToolDef>,
    private val onClick: (ToolDef) -> Unit,
    private val isIt: Boolean
) : RecyclerView.Adapter<ToolAdapter.VH>() {

    inner class VH(val b: ItemToolBinding) : RecyclerView.ViewHolder(b.root)

    override fun onCreateViewHolder(parent: ViewGroup, viewType: Int): VH {
        val b = ItemToolBinding.inflate(LayoutInflater.from(parent.context), parent, false)
        b.root.elevation = 0f
        return VH(b)
    }

    override fun onBindViewHolder(h: VH, position: Int) {
        val t = items[position]
        h.b.ivIcon.setImageResource(toolIconRes(t.key))
        ImageViewCompat.setImageTintList(h.b.ivIcon, ColorStateList.valueOf(Color.WHITE))
        h.b.ivIcon.background = iconBg(t.color)
        h.b.tvName.text = t.name
        h.b.tvDesc.text = if (isIt) t.descIt else t.descEn
        h.b.tvCat.text = categoryLabel(h.b.root.context, t.category)
        h.b.tvBadge.text = if (t.runtime == "local") {
            if (isIt) "Locale" else "Local"
        } else {
            if (isIt) "Cloud anonima" else "Anonymous Cloud"
        }

        val st = statusColor(h.b.root.context, t.status)
        h.b.tvStatus.text = statusLabel(h.b.root.context, t.status)
        h.b.tvStatus.setTextColor(st)
        h.b.tvStatus.background = pillBg(st)
        h.b.root.setOnClickListener { onClick(t) }

        val pos = h.bindingAdapterPosition
        val d = h.b.root.resources.displayMetrics.density
        h.b.root.alpha = 0f
        h.b.root.translationY = (36 * d)
        h.b.root.scaleX = 0.96f
        h.b.root.scaleY = 0.96f
        h.b.root.animate()
            .alpha(1f)
            .translationY(0f)
            .scaleX(1f)
            .scaleY(1f)
            .setDuration(420)
            .setStartDelay((pos % 8) * 50L)
            .start()
    }

    override fun getItemCount() = items.size

    private fun categoryLabel(ctx: Context, cat: String): String {
        val res = ctx.resources
        return when (cat) {
            "giochi" -> res.getString(R.string.cat_giochi).uppercase()
            "creativita" -> res.getString(R.string.cat_creativita).uppercase()
            "educativi" -> res.getString(R.string.cat_educativi).uppercase()
            "produttivita" -> res.getString(R.string.cat_produttivita).uppercase()
            "utility" -> res.getString(R.string.cat_utility).uppercase()
            else -> res.getString(R.string.cat_altro).uppercase()
        }
    }

    private fun statusColor(ctx: Context, status: String): Int {
        val res = ctx.resources
        return when (normalize(status)) {
            "maintenance" -> res.getColor(R.color.st_manutenzione, ctx.theme)
            "pausa" -> res.getColor(R.color.st_pausa, ctx.theme)
            "costruzione" -> res.getColor(R.color.st_costruzione, ctx.theme)
            "verifica" -> res.getColor(R.color.st_verifica, ctx.theme)
            "bloccato" -> res.getColor(R.color.st_bloccato, ctx.theme)
            "archiviato" -> res.getColor(R.color.st_archiviato, ctx.theme)
            else -> res.getColor(R.color.st_active, ctx.theme)
        }
    }

    private fun statusLabel(ctx: Context, status: String): String {
        val res = ctx.resources
        return when (normalize(status)) {
            "maintenance" -> res.getString(R.string.status_manutenzione)
            "pausa" -> res.getString(R.string.status_pausa)
            "costruzione" -> res.getString(R.string.status_costruzione)
            "verifica" -> res.getString(R.string.status_verifica)
            "bloccato" -> res.getString(R.string.status_bloccato)
            "archiviato" -> res.getString(R.string.status_archiviato)
            else -> res.getString(R.string.status_attivo)
        }
    }

    private fun normalize(status: String): String = when (status) {
        "active", "attivo", "online" -> "attivo"
        "maintenance", "manutenzione" -> "maintenance"
        "busy", "pause", "pausa" -> "pausa"
        "construction", "costruzione" -> "costruzione"
        "review", "verifica" -> "verifica"
        "blocked", "bloccato" -> "bloccato"
        "archived", "archiviato" -> "archiviato"
        else -> status.lowercase()
    }

    private fun toolIconRes(key: String): Int {
        return when (key) {
            "pressme" -> R.drawable.ic_tool_dot
            "coloriamo" -> R.drawable.ic_tool_palette
            "infonazionale" -> R.drawable.ic_tool_gradcap
            "generatorepiani" -> R.drawable.ic_tool_books
            "clienti" -> R.drawable.ic_tool_smile
            "promailer" -> R.drawable.ic_tool_mail
            "mappaidee" -> R.drawable.ic_tool_mind
            "squizzati" -> R.drawable.ic_tool_quiz
            "comparami" -> R.drawable.ic_tool_versus
            "facciolo" -> R.drawable.ic_tool_code
            "rinomina" -> R.drawable.ic_tool_pen
            "riscivili" -> R.drawable.ic_tool_write
            "spiegalo" -> R.drawable.ic_tool_bulb
            "riassumi" -> R.drawable.ic_tool_brief
            "workmemanage" -> R.drawable.ic_tool_folder
            "workmetranslate" -> R.drawable.ic_tool_globe
            "workmerecipe" -> R.drawable.ic_tool_dish
            "workmeparlare" -> R.drawable.ic_tool_chat
            "workmegames" -> R.drawable.ic_tool_gamepad
            "workmeschedule" -> R.drawable.ic_tool_calendar
            "workmemusic" -> R.drawable.ic_tool_music
            "workmejournal" -> R.drawable.ic_tool_journal
            "workmebot" -> R.drawable.ic_tool_bot
            "workmecalc" -> R.drawable.ic_tool_calc
            "workmevoice" -> R.drawable.ic_tool_mic
            "workmefocus" -> R.drawable.ic_tool_timer
            "workmemood" -> R.drawable.ic_tool_smile
            "workmeresume" -> R.drawable.ic_tool_resume
            "workmedebate" -> R.drawable.ic_tool_debate
            "workmehoroscope" -> R.drawable.ic_tool_orb
            else -> R.drawable.ic_tool_rocket
        }
    }

    private fun pillBg(color: Int): android.graphics.drawable.GradientDrawable {
        val solid = (color and 0xFFFFFF) or 0x22000000
        val stroke = (color and 0xFFFFFF) or 0x66000000
        return android.graphics.drawable.GradientDrawable().apply {
            shape = android.graphics.drawable.GradientDrawable.RECTANGLE
            cornerRadius = 24.dpToPx().toFloat()
            setColor(solid)
            setStroke(1.dpToPx(), stroke)
        }
    }

    private fun iconBg(color: String): android.graphics.drawable.GradientDrawable {
        return android.graphics.drawable.GradientDrawable().apply {
            shape = android.graphics.drawable.GradientDrawable.RECTANGLE
            cornerRadius = 18.dpToPx().toFloat()
            setColor(Color.parseColor(hexWithAlpha(color)))
        }
    }

    private fun hexWithAlpha(color: String): String {
        val c = color.removePrefix("#")
        return if (c.length == 6) "#26$c" else color
    }
}

fun Int.dpToPx(): Int = (this * android.content.res.Resources.getSystem().displayMetrics.density).toInt()