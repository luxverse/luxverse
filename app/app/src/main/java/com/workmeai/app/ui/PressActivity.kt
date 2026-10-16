package com.workmeai.app.ui

import android.os.Bundle
import android.view.animation.ScaleAnimation
import com.workmeai.app.R
import com.workmeai.app.data.Tools
import com.workmeai.app.databinding.ActivityPressBinding

class PressActivity : BaseActivity() {

    private lateinit var b: ActivityPressBinding
    private var count = 0

    private val jokes = listOf(
        "Non serve a niente. Ma è tuo.",
        "Press. Release. Soddisfazione.",
        "Congratulazioni, l'hai premuto!",
        "Tech support: hai provato a ripremerlo?",
        "Il tasto che L.U.X. Verse non voleva che premessi.",
        "Questo è il massimo della produttività oggi.",
        " 100! Sei una leggenda.",
        "Continuando, il tasto dovrà pagare l'affitto."
    )

    override fun onCreate(savedInstanceState: Bundle?) {
        super.onCreate(savedInstanceState)
        b = ActivityPressBinding.inflate(layoutInflater)
        setContentView(b.root)

        val tool = Tools.byKey["pressme"] ?: Tools.all.first()
        b.toolbar.title = tool.name
        b.toolbar.setNavigationOnClickListener { finish() }

        b.btnPress.setOnClickListener {
            count++
            b.tvCount.text = count.toString()
            b.tvMsg.text = jokes[(count - 1) % jokes.size]
            val anim = ScaleAnimation(1f, 0.86f, 1f, 0.86f,
                ScaleAnimation.RELATIVE_TO_SELF, 0.5f, ScaleAnimation.RELATIVE_TO_SELF, 0.5f)
            anim.duration = 90
            anim.fillAfter = true
            anim.setAnimationListener(object : android.view.animation.Animation.AnimationListener {
                override fun onAnimationStart(a: android.view.animation.Animation?) {}
                override fun onAnimationRepeat(a: android.view.animation.Animation?) {}
                override fun onAnimationEnd(a: android.view.animation.Animation?) {
                    val back = ScaleAnimation(0.86f, 1f, 0.86f, 1f,
                        ScaleAnimation.RELATIVE_TO_SELF, 0.5f, ScaleAnimation.RELATIVE_TO_SELF, 0.5f)
                    back.duration = 120
                    b.btnPress.startAnimation(back)
                }
            })
            b.btnPress.startAnimation(anim)
        }
    }
}