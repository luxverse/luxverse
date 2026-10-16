package com.workmeai.app.ui

import android.Manifest
import android.content.Intent
import android.content.pm.PackageManager
import android.os.Bundle
import android.speech.RecognitionListener
import android.speech.RecognizerIntent
import android.speech.SpeechRecognizer
import androidx.core.content.ContextCompat
import androidx.core.view.isVisible
import androidx.activity.result.contract.ActivityResultContracts
import com.workmeai.app.R
import com.workmeai.app.data.Store
import com.workmeai.app.data.Tools
import com.workmeai.app.databinding.ActivityVoiceBinding
import com.workmeai.app.net.Api
import kotlinx.coroutines.CoroutineScope
import kotlinx.coroutines.Dispatchers
import kotlinx.coroutines.launch
import kotlinx.coroutines.withContext

class VoiceActivity : BaseActivity() {

    private lateinit var b: ActivityVoiceBinding
    private var recognizer: SpeechRecognizer? = null
    private var transcript = ""

    private val permLauncher =
        registerForActivityResult(ActivityResultContracts.RequestPermission()) { granted ->
            if (granted) startListening() else toastRes(R.string.voice_permission)
        }

    override fun onCreate(savedInstanceState: Bundle?) {
        super.onCreate(savedInstanceState)
        b = ActivityVoiceBinding.inflate(layoutInflater)
        setContentView(b.root)

        val tool = Tools.byKey["workmevoice"] ?: Tools.all.first()
        b.toolbar.title = tool.name
        b.toolbar.setNavigationOnClickListener { finish() }

        b.btnMic.setOnClickListener {
            if (ContextCompat.checkSelfPermission(this, Manifest.permission.RECORD_AUDIO)
                != PackageManager.PERMISSION_GRANTED
            ) {
                permLauncher.launch(Manifest.permission.RECORD_AUDIO)
            } else {
                startListening()
            }
        }
        b.btnSummarize.setOnClickListener {
            if (transcript.isBlank()) { toastRes(R.string.fill_input); return@setOnClickListener }
            runAI(getString(R.string.summarize), if (Store.lang(this) == "it") "Riassumi: $transcript" else "Summarize: $transcript")
        }
        b.btnTranslate.setOnClickListener {
            if (transcript.isBlank()) { toastRes(R.string.fill_input); return@setOnClickListener }
            runAI(getString(R.string.translate_en), if (Store.lang(this) == "it") "Traduci in inglese: $transcript" else "Translate to English: $transcript")
        }
    }

    private fun startListening() {
        if (!SpeechRecognizer.isRecognitionAvailable(this)) {
            toastRes(R.string.voice_error)
            return
        }
        recognizer?.destroy()
        recognizer = SpeechRecognizer.createSpeechRecognizer(this)
        val intent = Intent(RecognizerIntent.ACTION_RECOGNIZE_SPEECH)
        intent.putExtra(RecognizerIntent.EXTRA_LANGUAGE_MODEL, RecognizerIntent.LANGUAGE_MODEL_FREE_FORM)
        intent.putExtra(RecognizerIntent.EXTRA_LANGUAGE, "it-IT")
        intent.putExtra(RecognizerIntent.EXTRA_PARTIAL_RESULTS, true)
        recognizer?.setRecognitionListener(object : RecognitionListener {
            override fun onReadyForSpeech(params: android.os.Bundle?) {
                runOnUiThread { b.tvStatus.setText(R.string.voice_listening) }
            }
            override fun onBeginningOfSpeech() {}
            override fun onRmsChanged(rmsdB: Float) {}
            override fun onBufferReceived(buffer: ByteArray?) {}
            override fun onEndOfSpeech() {}
            override fun onError(error: Int) {
                runOnUiThread { b.tvStatus.setText(R.string.voice_status) }
            }
            override fun onResults(results: android.os.Bundle?) {
                val list = results?.getStringArrayList(SpeechRecognizer.RESULTS_RECOGNITION)
                val heard = list?.firstOrNull().orEmpty()
                if (heard.isNotEmpty()) {
                    transcript = heard
                    b.tvTranscript.text = heard
                }
                runOnUiThread { b.tvStatus.setText(R.string.voice_status) }
            }
            override fun onPartialResults(partialResults: android.os.Bundle?) {
                val list = partialResults?.getStringArrayList(SpeechRecognizer.RESULTS_RECOGNITION)
                val heard = list?.firstOrNull().orEmpty()
                if (heard.isNotEmpty()) {
                    transcript = heard
                    b.tvTranscript.text = heard
                }
            }
            override fun onEvent(eventType: Int, params: android.os.Bundle?) {}
        })
        recognizer?.startListening(intent)
    }

    private fun runAI(label: String, prompt: String) {
        b.progress.isVisible = true
        val lang = Store.lang(this)
        CoroutineScope(Dispatchers.IO).launch {
            val result = runCatching {
                Api.groq(
                    if (lang == "en") "You are a helpful assistant. Answer briefly in English." else "Sei un assistente utile. Rispondi in italiano.",
                    prompt
                )
            }
            withContext(Dispatchers.Main) {
                b.progress.isVisible = false
                val text = result.getOrElse { getString(R.string.ai_error) }
                b.tvTranscript.text = com.workmeai.app.util.Markdown.render("$label\n\n$text")
            }
        }
    }

    override fun onDestroy() {
        recognizer?.destroy()
        super.onDestroy()
    }
}