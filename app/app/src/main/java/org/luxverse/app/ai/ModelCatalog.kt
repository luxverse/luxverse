package org.luxverse.app.ai

/**
 * CATALOGO DEI MODELLI LOCALI — la stessa verità del sito.
 *
 * L'elenco, i nomi dei file, le dimensioni e la soglia di RAM sono identici a
 * quelli dell'endpoint /api/api.php?__ep=models: se cambia una riga là, va
 * cambiata anche qui, altrimenti app e sito consigliano modelli diversi allo
 * stesso telefono.
 *
 * I pesi non stanno sul nostro hosting (sarebbero centinaia di megabyte e
 * l'hosting non li serve): si scaricano dai repository ufficiali dei modelli,
 * in formato GGUF 4-bit, una volta sola, e restano nella cartella privata
 * dell'app.
 */
data class LuxModel(
    val id: String,
    val name: String,
    val file: String,
    val sizeMb: Int,
    val ramMin: Int,
    val tier: String,
    val note: String,
    val url: String,
) {
    /** "Qwen 2.5 0.5B Instruct · 400 MB" */
    val label: String get() = "$name · $sizeMb MB"
}

object ModelCatalog {

    val all: List<LuxModel> = listOf(
        LuxModel(
            id = "qwen2.5-0.5b-q4",
            name = "Qwen 2.5 0.5B Instruct",
            file = "qwen2.5-0.5b-instruct-q4_k_m.gguf",
            sizeMb = 400, ramMin = 2, tier = "base",
            note = "Gira su quasi tutto, risposte brevi.",
            url = "https://huggingface.co/Qwen/Qwen2.5-0.5B-Instruct-GGUF/resolve/main/qwen2.5-0.5b-instruct-q4_k_m.gguf",
        ),
        LuxModel(
            id = "llama3.2-1b-q4",
            name = "Llama 3.2 1B Instruct",
            file = "Llama-3.2-1B-Instruct-Q4_K_M.gguf",
            sizeMb = 800, ramMin = 3, tier = "base",
            note = "Buon compromesso qualità/velocità.",
            url = "https://huggingface.co/bartowski/Llama-3.2-1B-Instruct-GGUF/resolve/main/Llama-3.2-1B-Instruct-Q4_K_M.gguf",
        ),
        LuxModel(
            id = "qwen2.5-1.5b-q4",
            name = "Qwen 2.5 1.5B Instruct",
            file = "qwen2.5-1.5b-instruct-q4_k_m.gguf",
            sizeMb = 1100, ramMin = 4, tier = "medio",
            note = "Italiano migliore, testi più lunghi.",
            url = "https://huggingface.co/Qwen/Qwen2.5-1.5B-Instruct-GGUF/resolve/main/qwen2.5-1.5b-instruct-q4_k_m.gguf",
        ),
        LuxModel(
            id = "llama3.2-3b-q4",
            name = "Llama 3.2 3B Instruct",
            file = "Llama-3.2-3B-Instruct-Q4_K_M.gguf",
            sizeMb = 2000, ramMin = 6, tier = "alto",
            note = "Ragionamento solido, serve RAM.",
            url = "https://huggingface.co/bartowski/Llama-3.2-3B-Instruct-GGUF/resolve/main/Llama-3.2-3B-Instruct-Q4_K_M.gguf",
        ),
        LuxModel(
            id = "qwen2.5-7b-q4",
            name = "Qwen 2.5 7B Instruct",
            file = "qwen2.5-7b-instruct-q4_k_m.gguf",
            sizeMb = 4700, ramMin = 12, tier = "avanzato",
            note = "Solo dispositivi con 12 GB+ liberi.",
            // Il repository ufficiale Qwen spezza il 7B in due pezzi: qui serve
            // un file unico, quindi si prende la conversione singola (stessa
            // quantizzazione 4-bit, stesso modello).
            url = "https://huggingface.co/bartowski/Qwen2.5-7B-Instruct-GGUF/resolve/main/Qwen2.5-7B-Instruct-Q4_K_M.gguf",
        ),
        LuxModel(
            id = "llama3.1-8b-q4",
            name = "Llama 3.1 8B Instruct",
            file = "Meta-Llama-3.1-8B-Instruct-Q4_K_M.gguf",
            sizeMb = 4900, ramMin = 12, tier = "avanzato",
            note = "Qualità alta, richiede 12 GB+ liberi.",
            url = "https://huggingface.co/bartowski/Meta-Llama-3.1-8B-Instruct-GGUF/resolve/main/Meta-Llama-3.1-8B-Instruct-Q4_K_M.gguf",
        ),
    )

    /**
     * La stessa regola del sito: sotto i 4 GB si lascia un giga al sistema,
     * poi si prende il modello più grande che ci sta, e due alternative più
     * leggere. Il primo della lista è quello consigliato.
     */
    fun forRam(ramGb: Float): List<LuxModel> {
        if (ramGb <= 0f) return listOf(all.first())
        val limit = if (ramGb < 4f) ramGb - 1f else ramGb
        val fit = all.filter { it.ramMin <= limit }.sortedByDescending { it.ramMin }
        return if (fit.isEmpty()) listOf(all.first()) else fit
    }

    fun byId(id: String?): LuxModel? = all.firstOrNull { it.id == id }

    fun byFile(file: String?): LuxModel? = all.firstOrNull { it.file == file }
}
