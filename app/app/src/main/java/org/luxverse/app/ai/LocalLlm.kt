package org.luxverse.app.ai

import android.content.Context
import java.io.File

/**
 * Motore locale llama.cpp. Il modello GGUF sta in filesDir/models.
 * La scelta del modello arriva dal questionario del sito
 * (/api/api.php?__ep=models&ram=...), così app e sito usano la stessa regola.
 */
object LocalLlm {
    init {
        System.loadLibrary("luxllm")
    }

    private external fun nativeInit(path: String, threads: Int, nctx: Int): Long
    private external fun nativeGenerate(handle: Long, prompt: String, maxTokens: Int): String
    private external fun nativeFree(handle: Long)

    private var handle: Long = 0L
    private var loadedPath: String? = null

    /** Turni di cronologia tenuti nel prompt (6 = circa 1500 token a 2048). */
    private const val MAX_TURNS = 6

    val isLoaded: Boolean get() = handle != 0L

    /** RAM totale in GB, per il questionario del sito. */
    fun deviceRamGb(context: Context): Float {
        val activityManager = context.getSystemService(Context.ACTIVITY_SERVICE) as android.app.ActivityManager
        val info = android.app.ActivityManager.MemoryInfo()
        activityManager.getMemoryInfo(info)
        return (info.totalMem / (1024.0 * 1024.0 * 1024.0)).toFloat()
    }

    fun threads(): Int = (Runtime.getRuntime().availableProcessors() - 1).coerceIn(2, 6)

    @Synchronized
    fun load(model: File, nctx: Int = 2048): Boolean {
        if (isLoaded && loadedPath == model.absolutePath) return true
        unload()
        if (!model.isFile) return false
        handle = nativeInit(model.absolutePath, threads(), nctx)
        loadedPath = if (handle != 0L) model.absolutePath else null
        return handle != 0L
    }

    @Synchronized
    fun generate(prompt: String, maxTokens: Int = 256): String {
        if (handle == 0L) return ""
        return nativeGenerate(handle, prompt, maxTokens)
    }

    @Synchronized
    fun chat(userText: String, system: String = "Sei L.U.X., assistente italiano conciso.", maxTokens: Int = 256): String {
        return chat(listOf("user" to userText), system, maxTokens)
    }

    /**
     * Conversazione a più turni nello stesso formato ChatML del modello.
     * `turns` è la cronologia in ordine: "user"/"assistant" a coppie. Si
     * tengono solo gli ultimi turni (vedi MAX_TURNS) perché la finestra è di
     * 2048 token: una cronologia lunga mangerebbe lo spazio della risposta.
     */
    @Synchronized
    fun chat(turns: List<Pair<String, String>>, system: String = "Sei L.U.X., assistente italiano conciso.", maxTokens: Int = 256): String {
        val prompt = buildString {
            append("<|im_start|>system\n").append(system).append("<|im_end|>\n")
            for ((role, text) in turns.takeLast(MAX_TURNS)) {
                append("<|im_start|>").append(role).append("\n").append(text).append("<|im_end|>\n")
            }
            append("<|im_start|>assistant\n")
        }
        return generate(prompt, maxTokens)
    }

    /** La cartella privata dei modelli: /data/data/<app>/files/models. */
    fun modelsDir(context: Context): File = File(context.filesDir, "models").apply { mkdirs() }

    fun modelFile(context: Context, file: String): File = File(modelsDir(context), file)

    @Synchronized
    fun unload() {
        if (handle != 0L) {
            nativeFree(handle)
            handle = 0L
            loadedPath = null
        }
    }
}
