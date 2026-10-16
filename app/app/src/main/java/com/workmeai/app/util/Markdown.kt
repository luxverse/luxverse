package com.workmeai.app.util

import android.graphics.Typeface
import android.text.SpannableStringBuilder
import android.text.Spanned
import android.text.style.ForegroundColorSpan
import android.text.style.RelativeSizeSpan
import android.text.style.StyleSpan
import android.text.style.TypefaceSpan
import android.text.style.URLSpan

object Markdown {

    private const val CODE_COLOR = 0xFF6ECBFF.toInt()
    private const val LINK_COLOR = 0xFF5B8CFF.toInt()
    private const val HEADING_COLOR = 0xFFF0CD5A.toInt()
    private const val QUOTE_COLOR = 0xFF9FABD0.toInt()

    fun render(raw: String): Spanned {
        val sb = SpannableStringBuilder()
        val normalized = raw.replace("\r\n", "\n")
        val lines = normalized.split("\n")
        var inCodeBlock = false

        for ((i, line0) in lines.withIndex()) {
            val line = line0

            if (line.trimStart().startsWith("```")) {
                inCodeBlock = !inCodeBlock
                if (inCodeBlock && i < lines.lastIndex) sb.append("\n")
                continue
            }

            if (inCodeBlock) {
                val start = sb.length
                sb.append("    ").append(line).append("\n")
                sb.setSpan(ForegroundColorSpan(CODE_COLOR), start, sb.length - 1, Spanned.SPAN_EXCLUSIVE_EXCLUSIVE)
                sb.setSpan(TypefaceSpan("monospace"), start, sb.length - 1, Spanned.SPAN_EXCLUSIVE_EXCLUSIVE)
                continue
            }

            val trimmed = line.trimStart()

            val hashes = trimmed.takeWhile { it == '#' }
            if (hashes.length in 1..4 && (trimmed.length == hashes.length || trimmed[hashes.length] == ' ')) {
                val block = inline(trimmed.drop(hashes.length).trimStart())
                val start = sb.length
                sb.append(block)
                sb.setSpan(StyleSpan(Typeface.BOLD), start, sb.length, Spanned.SPAN_EXCLUSIVE_EXCLUSIVE)
                sb.setSpan(ForegroundColorSpan(HEADING_COLOR), start, sb.length, Spanned.SPAN_EXCLUSIVE_EXCLUSIVE)
                when (hashes.length) {
                    1 -> sb.setSpan(RelativeSizeSpan(1.45f), start, sb.length, Spanned.SPAN_EXCLUSIVE_EXCLUSIVE)
                    2 -> sb.setSpan(RelativeSizeSpan(1.25f), start, sb.length, Spanned.SPAN_EXCLUSIVE_EXCLUSIVE)
                    3 -> sb.setSpan(RelativeSizeSpan(1.12f), start, sb.length, Spanned.SPAN_EXCLUSIVE_EXCLUSIVE)
                }
                sb.append("\n")
                continue
            }

            when {
                trimmed.startsWith("- ") || trimmed.startsWith("* ") || trimmed.startsWith("• ") -> {
                    sb.append("  •  ").append(inline(trimmed.drop(2).trimStart()))
                }
                Regex("^\\d+[.)] ").containsMatchIn(trimmed) -> {
                    val m = Regex("^\\d+[.)]\\s*").find(trimmed)
                    val content = if (m != null) trimmed.substring(m.value.length) else trimmed
                    sb.append("   ").append(inline(content.trimStart()))
                }
                trimmed.startsWith("> ") -> {
                    val block = inline(trimmed.drop(2))
                    val start = sb.length
                    sb.append("│ ").append(block)
                    sb.setSpan(QUOTE_COLOR_SPAN(), start, sb.length, Spanned.SPAN_EXCLUSIVE_EXCLUSIVE)
                    sb.setSpan(TypefaceSpan("serif"), start, sb.length, Spanned.SPAN_EXCLUSIVE_EXCLUSIVE)
                }
                else -> sb.append(inline(line))
            }

            if (i < lines.lastIndex) sb.append("\n")
        }

        return sb
    }

    private fun QUOTE_COLOR_SPAN() = ForegroundColorSpan(QUOTE_COLOR)

    private fun inline(text: String): SpannableStringBuilder {
        val sb = SpannableStringBuilder()
        var i = 0
        while (i < text.length) {
            when {
                text[i] == '`' -> {
                    val close = text.indexOf('`', i + 1)
                    if (close > 0) {
                        val start = sb.length
                        sb.append(text.substring(i + 1, close))
                        sb.setSpan(ForegroundColorSpan(CODE_COLOR), start, sb.length, Spanned.SPAN_EXCLUSIVE_EXCLUSIVE)
                        sb.setSpan(TypefaceSpan("monospace"), start, sb.length, Spanned.SPAN_EXCLUSIVE_EXCLUSIVE)
                        i = close + 1
                    } else {
                        sb.append(text[i])
                        i++
                    }
                }
                text.startsWith("**", i) || text.startsWith("__", i) -> {
                    val marker = text.substring(i, i + 2)
                    val close = text.indexOf(marker, i + 2)
                    if (close > 0) {
                        val inner = text.substring(i + 2, close)
                        val flat = inner.replace("**", "").replace("__", "")
                        val start = sb.length
                        sb.append(flat)
                        sb.setSpan(StyleSpan(Typeface.BOLD), start, sb.length, Spanned.SPAN_EXCLUSIVE_EXCLUSIVE)
                        i = close + 2
                    } else {
                        sb.append(text[i])
                        i++
                    }
                }
                text[i] == '*' || text[i] == '_' -> {
                    val marker = text[i].toString()
                    val close = text.indexOf(marker, i + 1)
                    val nextNonMarker = if (close > 0 && close + 1 < text.length) text[close + 1] else ' '
                    if (close > 0 && nextNonMarker != marker[0]) {
                        val inner = text.substring(i + 1, close)
                        val flat = inner.replace("*", "").replace("_", "")
                        val start = sb.length
                        sb.append(flat)
                        sb.setSpan(StyleSpan(Typeface.ITALIC), start, sb.length, Spanned.SPAN_EXCLUSIVE_EXCLUSIVE)
                        i = close + 1
                    } else {
                        sb.append(text[i])
                        i++
                    }
                }
                text[i] == '[' -> {
                    val closeB = text.indexOf(']', i + 1)
                    if (closeB > 0 && closeB + 1 < text.length && text[closeB + 1] == '(') {
                        val closeP = text.indexOf(')', closeB + 2)
                        if (closeP > 0) {
                            val label = text.substring(i + 1, closeB)
                            val url = text.substring(closeB + 2, closeP)
                            val start = sb.length
                            sb.append(label)
                            sb.setSpan(ForegroundColorSpan(LINK_COLOR), start, sb.length, Spanned.SPAN_EXCLUSIVE_EXCLUSIVE)
                            sb.setSpan(URLSpan(url), start, sb.length, Spanned.SPAN_EXCLUSIVE_EXCLUSIVE)
                            i = closeP + 1
                        } else {
                            sb.append(text[i])
                            i++
                        }
                    } else {
                        sb.append(text[i])
                        i++
                    }
                }
                else -> {
                    sb.append(text[i])
                    i++
                }
            }
        }
        return sb
    }
}