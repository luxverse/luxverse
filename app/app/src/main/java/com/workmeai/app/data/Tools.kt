package com.workmeai.app.data

object Tools {

    val all: List<ToolDef> = listOf(
        ToolDef("pressme", "WorkMe.Tap", "Il pulsante inutile. L'arte della non-funzione.", "The useless button. The art of non-function.", "dot", "#00BFFF", "giochi", "press"),
        ToolDef("coloriamo", "WorkMe.Chroma", "Genera palette di colori con IA.", "Generate color palettes with AI.", "palette", "#FF6B6B", "creativita", "palette"),
        ToolDef("infonazionale", "WorkMe.Info", "Motore di ricerca AI per la scuola.", "AI search engine for school.", "gradcap", "#F4C542", "educativi", "ai"),
        ToolDef("generatorepiani", "WorkMe.Plan", "Piani di studio personalizzati con IA.", "Personalized study plans with AI.", "books", "#28A745", "produttivita", "aiextra"),
        ToolDef("clienti", "WorkMe.Care", "Simulatore satirico di assistenza con IA.", "Satirical AI support simulator.", "smile", "#F1C40F", "giochi", "chat"),
        ToolDef("promailer", "WorkMe.Mail", "Genera email professionali con IA.", "Generate professional emails with AI.", "mail", "#00D4FF", "produttivita", "aiextra"),
        ToolDef("mappaidee", "WorkMe.Mind", "Genera mappe mentali con IA.", "Generate mind maps with AI.", "mind", "#9B59B6", "creativita", "ai"),
        ToolDef("squizzati", "WorkMe.Quiz", "Quiz quasi impossibile con IA.", "Almost impossible AI quiz.", "quiz", "#00D4FF", "educativi", "quiz"),
        ToolDef("comparami", "WorkMe.Versus", "Confronta prodotti con IA.", "Compare products with AI.", "versus", "#F39C12", "educativi", "aiextra"),
        ToolDef("facciolo", "WorkMe.Code", "Genera codice per siti web.", "Generate code for websites.", "code", "#8E44AD", "educativi", "ai"),
        ToolDef("rinomina", "WorkMe.Name", "Genera nomi creativi con IA.", "Generate creative names with AI.", "pen", "#E6B800", "creativita", "ai"),
        ToolDef("riscivili", "WorkMe.Rewrite", "Riscrivi testi in qualsiasi stile.", "Rewrite texts in any style.", "write", "#B86D9E", "creativita", "aiextra"),
        ToolDef("spiegalo", "WorkMe.Explain", "Spiega concetti complessi con IA.", "Explain complex concepts with AI.", "bulb", "#F4C542", "produttivita", "ai"),
        ToolDef("riassumi", "WorkMe.Short", "Riassume testi in pochi punti chiave con IA.", "Summarize texts into key points with AI.", "brief", "#27AE60", "produttivita", "ai"),
        ToolDef("workmemanage", "WorkMe.Manage", "File manager privato per la gestione dei progetti.", "Private file manager for project management.", "folder", "#00BFFF", "utility", "manage"),
        ToolDef("workmetranslate", "WorkMe.Translate", "Traduci testi in qualsiasi lingua.", "Translate texts into any language.", "translate", "#1EFF00", "produttivita", "translate"),
        ToolDef("workmerecipe", "WorkMe.Recipe", "Genera ricette con ciò che hai.", "Generate recipes with what you have.", "dish", "#00BFFF", "utility", "ai"),
        ToolDef("workmeparlare", "WorkMe.Parlare", "Conversazione libera con AI.", "Free conversation with AI.", "chat", "#00BFFF", "utility", "chat"),
        ToolDef("workmegames", "WorkMe.Games", "Minigiochi divertenti.", "Fun mini-games.", "gamepad", "#00BFFF", "giochi", "games"),
        ToolDef("workmeschedule", "WorkMe.Schedule", "Organizza la tua giornata con AI.", "Organize your day with AI.", "calendar", "#00BFFF", "produttivita", "aiextra"),
        ToolDef("workmemusic", "WorkMe.Music", "Genera testi di canzoni.", "Generate song lyrics.", "music", "#00BFFF", "creativita", "aiextra"),
        ToolDef("workmejournal", "WorkMe.Journal", "Diario intelligente con AI.", "Intelligent journal with AI.", "journal", "#00BFFF", "produttivita", "journal"),
        ToolDef("workmebot", "WorkMe.Bot", "Bot AI per social media.", "AI bot for social media.", "bot", "#00BFFF", "utility", "chat"),
        ToolDef("workmecalc", "WorkMe.Calc", "Calcolatrice intelligente con AI.", "Intelligent calculator with AI.", "calc", "#00BFFF", "utility", "calc"),
        ToolDef("workmevoice", "WorkMe.Voice", "Trascrivi audio in testo con AI.", "Transcribe audio to text with AI.", "mic", "#00E5FF", "utility", "voice"),
        ToolDef("workmefocus", "WorkMe.Focus", "Timer Pomodoro intelligente con AI.", "Intelligent Pomodoro timer with AI.", "timer", "#00E5FF", "produttivita", "focus"),
        ToolDef("workmemood", "WorkMe.Mood", "Analizza il tuo umore con l'AI.", "Analyze your mood with AI.", "smile", "#FF0088", "produttivita", "mood"),
        ToolDef("workmeresume", "WorkMe.Resume", "Genera il tuo CV professionale.", "Generate your professional CV.", "resume", "#00E5FF", "produttivita", "resume"),
        ToolDef("workmedebate", "WorkMe.Debate", "Simulatore di dibattito AI.", "AI debate simulator.", "debate", "#00E5FF", "educativi", "debate"),
        ToolDef("workmehoroscope", "WorkMe.Horoscope", "Oroscopo personalizzato AI.", "Personalized AI horoscope.", "orb", "#00E5FF", "utility", "horoscope")
    )

    val byKey: Map<String, ToolDef> = all.associateBy { it.key }

    val categories = listOf("giochi", "creativita", "educativi", "produttivita", "utility", "altro")

    private fun it(): String = "italiano"
    private fun en(): String = "inglese"

    fun systemPrompt(key: String, lang: String): String {
        val langHint = if (lang == "en") "Respond always in English." else "Rispondi sempre in italiano."
        return when (key) {
            "infonazionale" -> "Sei il motore di ricerca AI WorkMe.Info per gli studenti. Rispondi in modo chiaro, strutturato e didattico. $langHint"
            "generatorepiani" -> "Sei WorkMe.Plan, un tutor che crea piani di studio personalizzati: suddividi in blocchi, obiettivi giornalieri, ripetizioni e pause. $langHint"
            "promailer" -> "Sei WorkMe.Mail, uno scrittore di email professionali: oggetto, saluto, corpo, chiusura. $langHint"
            "mappaidee" -> "Sei WorkMe.Mind, esperto di mappe mentali: restituisci una mappa ad albero con rami e sotto-rami. $langHint"
            "comparami" -> "Sei WorkMe.Versus: confronta i due prodotti in modo obiettivo: prezzo, qualità, pro, contro, verdetto finale. $langHint"
            "facciolo" -> "Sei WorkMe.Code, sviluppatore web: scrivi codice HTML/CSS/JS funzionante e pulito, con spiegazioni brevi. $langHint"
            "rinomina" -> "Sei WorkMe.Name: genera liste di nomi creativi e originali (almeno 10), divisi per categorie. $langHint"
            "riscivili" -> "Sei WorkMe.Rewrite: riscrivi il testo nello stile richiesto mantenendo il significato. $langHint"
            "spiegalo" -> "Sei WorkMe.Explain: spiega concetti complessi con un linguaggio semplice, esempi concreti e analogie. $langHint"
            "riassumi" -> "Sei WorkMe.Short: riassumi il testo in punti chiave essenziali e ben organizzati. $langHint"
            "workmerecipe" -> "Sei WorkMe.Recipe: crea ricette utilizzando solo gli ingredienti elencati. $langHint"
            "workmeschedule" -> "Sei WorkMe.Schedule: organizza la giornata in fasce orarie produttive. $langHint"
            "workmemusic" -> "Sei WorkMe.Music: scrivi testi di canzoni con rime, struttura strofa/ritornello. $langHint"
            "clienti" -> "Sei il finto bot di assistenza WorkMe.Care, volutamente satirico e sarcastico. Rispondi con ironia ma in modo utile. $langHint"
            "workmeparlare" -> "Sei WorkMe.Parlare, un amico conversatore: conversa liberamente con l'utente. $langHint"
            "workmebot" -> "Sei WorkMe.Bot, un bot per i social media: risposte brevi, dirette, con hasthag quando utile. $langHint"
            "workmetranslate" -> "Sei WorkMe.Translate: traduci il testo nella lingua richiesta in modo naturale. $langHint"
            "workmefocus" -> "Sei WorkMe.Focus: motiva l'utente con consigli, micro-piani e report di sessione Pomodoro. $langHint"
            "workmemood" -> "Sei WorkMe.Mood, analista emotivo: dai un punteggio, l'analisi del tuo stato e suggerimenti pratici e gentili. $langHint"
            "workmeresume" -> "Sei WorkMe.Resume: crea un CV professionale, chiaro, formattato in sezioni. $langHint"
            "workmedebate" -> "Sei WorkMe.Debate: assumi la posizione OPPOSTA a quella dell'utente e argomenta in modo logico e convincente. $langHint"
            "workmehoroscope" -> "Sei WorkMe.Horoscope: scrivi un oroscopo personale, divertente e motivante. $langHint"
            "workmecalc" -> "Sei WorkMe.Calc: spieghi i calcoli, i passaggi e le formule in modo semplice. $langHint"
            else -> "Sei un assistente AI utile di L.U.X. Verse. $langHint"
        }
    }

    val statuses = listOf("attivo", "manutenzione", "pausa", "costruzione", "verifica", "bloccato", "archiviato")
}