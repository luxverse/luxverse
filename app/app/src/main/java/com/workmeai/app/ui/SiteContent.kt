package com.workmeai.app.ui

object SiteContent {

    private const val IT_HERO = "L.U.X. Verse non è solo un sito: è il risultato di un'idea semplice ma ambiziosa — rendere l'intelligenza artificiale uno strumento alla portata di tutti."

    fun chiSiamo(lang: String): String {
        val it = lang == "it" || lang.isEmpty()
        return if (it) """
# Chi Siamo

$IT_HERO

**La nostra missione.** Crediamo che la tecnologia debba essere democratica, accessibile e al servizio delle persone. Per questo creiamo strumenti digitali intelligenti che semplificano la vita quotidiana, senza barriere economiche né registrazioni. Chiunque, ovunque, deve poter accedere a strumenti professionali per migliorare lavoro, studio e creatività.

**Da dove veniamo.** Prima di diventare L.U.X. Verse c'erano solo notti insonni, pagine bianche e errori impossibili da risolvere. Giorni in cui la voglia di mollare era più forte. Ma poi è arrivata la **community**: e con essa la forza di andare avanti, migliorare e costruire qualcosa che vale davvero. Oggi non siamo più un progetto isolato: **siamo un ecosistema vivo**, e questo è solo l'inizio.

**I nostri obiettivi**

- Raggiungere i **100 strumenti** (da 25 a 100)
- Utenti 24/7, ogni giorno, in ogni fuso orario
- 10.000 visite: 10.000 persone che scelgono di fidarsi
- Crescere insieme alla community
- Sicurezza impeccabile per tutti
- Comprare un dominio vero: un simbolo di maturità e futuro

**La nostra storia.** L.U.X. Verse nasce nel 2026 dall'intuizione di un giovane sviluppatore appassionato di tecnologia e IA. L'idea era semplice quanto rivoluzionaria: creare un ecosistema di strumenti digitali gratuiti, accessibili e professionali. Nessuna registrazione, nessun abbonamento, nessuna barriera. In pochi mesi l'offerta è cresciuta fino a superare i 25 strumenti diversi: scrittura, traduzione, creatività, produttività, educazione e intrattenimento.

## Cos'è L.U.X. Verse oggi

Un **ecosistema digitale completo**: oltre 25 strumenti gratui, efficaci e ben progettati. Ogni pagina, ogni funzione e ogni interazione è stata pensata per offrire un'esperienza fluida, moderna e professionale.

## I nostri valori

- **Innovazione** — cerchiamo sempre nuove idee e nuove tecnologie
- **Accessibilità** — tutti i tool sono gratuiti e senza registrazione
- **Qualità** — curiamo ogni dettaglio, dall'interfaccia alle performance
- **Privacy** — nessun tracking invasivo, nessuna vendita di dati

> "Non c'è strumento più potente della volontà di creare qualcosa che possa aiutare gli altri."
>
> — L.U.X. Verse Development Team

**Il nostro team.** Yeamin (Founder & Lead Developer), il L.U.X. Verse Dev Team (Design & Innovation) e la Community, il cuore dell'ecosistema.

**Unisciti a L.U.X. Verse.** Esplora tutti i nostri strumenti: nessuna registrazione, nessun costo, nessuna complicazione.
""".trimIndent() else """
# About Us

L.U.X. Verse is not just a website: it is the result of a simple but ambitious idea — making artificial intelligence a tool within everyone's reach.

**Our mission.** We believe technology should be democratic, accessible and at the service of people. That is why we create smart digital tools that simplify everyday life, without economic barriers or registration requirements. Anyone, anywhere, should have access to professional tools able to improve their work, study and creativity.

**Where we come from.** Before becoming L.U.X. Verse there were only sleepless nights, blank pages that refused to be filled, mistakes that seemed impossible to solve. Days when giving up seemed easier. But then the **community** arrived: and with you came the strength to move forward, improve and build something truly worthwhile. Today we are no longer an isolated project: **we are a living ecosystem**, and this is only the beginning.

**Our goals**

- Reaching the **100 Tools** (from 25 to 100)
- Users 24/7, every day, in every time zone
- 10,000 visits: 10,000 people choosing to trust us
- Growing together with the community
- Flawless security for everyone
- Buying a real domain: a symbol of maturity and future

**Our story.** L.U.X. Verse was born in 2026 from the intuition of a young developer passionate about technology and AI. The idea was as simple as it was revolutionary: create an ecosystem of free, accessible and professional digital tools. No registration, no subscription, no barriers. In a few months the offer grew to over 25 different tools: writing, translation, creativity, productivity, education and entertainment.

## What L.U.X. Verse is today

A **complete digital ecosystem**: over 25 free, effective and well-designed tools. Every page, every feature and every interaction was designed to offer a smooth, modern and professional experience.

## Our values

- **Innovation** — we constantly look for new ideas and technologies
- **Accessibility** — all tools are free and require no registration
- **Quality** — we take care of every detail, from interface to performance
- **Privacy** — no invasive tracking, no selling of information

> "There is no tool more powerful than the will to create something that can help others."
>
> — L.U.X. Verse Development Team

**Our team.** Yeamin (Founder & Lead Developer), the L.U.X. Verse Dev Team (Design & Innovation) and the Community, the heart of the ecosystem.

**Join L.U.X. Verse.** Explore all our tools: no registration, no cost, no complications.
""".trimIndent()
    }

    fun faq(lang: String): String {
        val it = lang == "it" || lang.isEmpty()
        return if (it) """
# FAQ

Risposte alle domande più comuni su L.U.X. Verse.

## Generali

**Cos'è L.U.X. Verse?**
L.U.X. Verse è un ecosistema di strumenti digitali basati sull'intelligenza artificiale, completamente gratuiti e accessibili a tutti. Offriamo oltre 25 strumenti diversi, divisi in categorie come creatività, produttività, educazione, giochi e utility.

**È tutto davvero gratuito?**
Sì, tutti i nostri strumenti sono completamente gratuiti. Nessun abbonamento, nessun pagamento, nessuna registrazione. Non esistono funzioni a pagamento, versioni premium o costi nascosti.

**Devo registrarmi per usare i servizi?**
No. Puoi usare tutti gli strumenti subito, senza account, senza dati personali e senza conferme via email. È la nostra filosofia di accessibilità.

## Tecniche

**Come funzionano gli strumenti AI?**
I nostri strumenti usano modelli avanzati di intelligenza artificiale per generare contenuti, rispondere a domande, tradurre testi e molto altro. Scrivi cosa ti serve: il sistema elabora la richiesta e restituisce il risultato in pochi istanti.

**Quanto tempo serve per generare una risposta?**
La maggior parte degli strumenti risponde in pochi secondi. Per richieste complesse o alto traffico può servire qualche secondo in più.

**Gli strumenti funzionano anche su smartphone?**
Assolutamente sì. Tutti i tool sono mobile-first: funzionano perfettamente su smartphone, tablet e computer.

**Ci sono limiti di utilizzo?**
Attualmente non applichiamo limiti rigidi, ma in futuro potremmo introdurre misure di salvaguardia per prevenire abusi e garantire un servizio stabile.

## Privacy e Sicurezza

**I miei dati sono al sicuro?**
Assolutamente sì. Non raccogliamo dati personali, non usiamo cookie di tracciamento e non condividiamo informazioni con terzi. Tutte le comunicazioni avvengono su connessione HTTPS crittografata.

**I testi che inserisco vengono salvati?**
No. I contenuti che invii vengono elaborati in tempo reale e non vengono memorizzati sui nostri server.

**Usate i cookie?**
Usiamo solo cookie tecnici essenziali per il funzionamento del sito. Nessun cookie di profilazione, tracciamento o marketing.

## Supporto e Assistenza

**Come posso contattare il supporto?**
Attraverso la pagina **Assistenza** per richieste tecniche e problemi, e la pagina **Contatti** per informazioni generali. Rispondiamo di solito entro 24-48 ore.

**Posso suggerire nuovi strumenti o funzioni?**
Certo! Cerchiamo sempre nuove idee. Puoi inviare i tuoi suggerimenti dalla pagina **Contatti**, selezionando il tipo "Suggerimento".

**Ho trovato un bug. Come lo segnalo?**
Segnalacelo dalla pagina **Assistenza**, categoria "Segnalazione Bug". Descrivi in dettaglio: quale strumento stavi usando, cosa hai fatto, cosa ti aspettavi e cosa è successo.

## Progetti

**Quanti progetti ci sono su L.U.X. Verse?**
Oltre 25 progetti attivi, e il numero cresce ogni giorno. Ogni progetto è uno strumento indipendente ma integrato nell'ecosistema.

**Cosa significa progetto "in manutenzione"?**
Stiamo effettuando aggiornamenti, miglioramenti o fix su quello strumento. Potrebbe essere temporaneamente non disponibile: riprova più tardi, tornerà online migliorato.
""".trimIndent() else """
# FAQ

Answers to the most common questions about L.U.X. Verse.

## General

**What is L.U.X. Verse?**
L.U.X. Verse is an ecosystem of AI-based digital tools, completely free and accessible to everyone. We offer over 25 different tools, divided into categories such as creativity, productivity, education, games and utilities.

**Is everything really free?**
Yes, all our tools are completely free. No subscriptions, no payments, no registration. There are no paid features, premium versions or hidden costs.

**Do I need to register to use the services?**
No. You can use all tools immediately, without an account, without personal data and without email confirmations. That is our accessibility philosophy.

## Technical

**How do your AI tools work?**
Our tools use advanced AI models to generate content, answer questions, translate texts and much more. Just write what you need: the system processes the request and returns the result in moments.

**How long does it take to generate a response?**
Most tools respond within seconds. Complex requests or high traffic may take a few more seconds.

**Do the tools work on smartphones?**
Absolutely yes. All tools are mobile-first: they work perfectly on smartphones, tablets and computers.

**Are there usage limits?**
Currently no strict limits, but in the future we may introduce safeguards to prevent abuse and guarantee a stable service.

## Privacy and Security

**Is my data safe?**
Absolutely yes. We do not collect personal data, we do not use tracking cookies and we do not share information with third parties. All communications use encrypted HTTPS.

**Are the texts I enter saved?**
No. The content you send is processed in real time and is not stored on our servers.

**Do you use cookies?**
We only use essential technical cookies. No profiling, tracking or marketing cookies.

## Support

**How can I contact support?**
Through the **Assistance** page for technical requests and issues, and the **Contacts** page for general information. We reply within 24-48 hours.

**Can I suggest new tools or features?**
Of course! We are always looking for new ideas. Send your suggestions from the **Contacts** page, selecting "Suggestion" type.

**I found a bug. How do I report it?**
Report it from the **Assistance** page, "Bug Report" category. Describe in detail: which tool you were using, what you did, what you expected and what happened.

## Projects

**How many projects are on L.U.X. Verse?**
Over 25 active projects, growing every day. Each project is an independent tool integrated into the ecosystem.

**What does "under maintenance" mean?**
We are doing updates, improvements or fixes on that tool. It may be temporarily unavailable: try again later, it will be back online improved.
""".trimIndent()
    }

    fun assistenza(lang: String): String {
        val it = lang == "it" || lang.isEmpty()
        return if (it) """
# Assistenza

Richiedi assistenza, segnala problemi o bug. Il nostro team risponderà il prima possibile per aiutarti a risolvere qualsiasi difficoltà.

**Categorie supportate**

- Assistenza Tecnica
- Segnalazione Bug
- Richiesta Informazioni
- Reclamo
- Suggerimento
- Problemi Account
- Privacy
- Altro

**Tempi di risposta:** il nostro team cerca di rispondere a tutte le richieste entro 24-48 ore. Nei periodi di picco i tempi possono allungarsi leggermente.

**Hai già controllato le FAQ?** Molte delle domande più comuni hanno già una risposta nella sezione dedicata. Potresti risolvere il tuo problema in pochi secondi.

Per inviare una richiesta di assistenza usa il modulo nativo dell'app (pulsante sotto): riceverai un codice di tracciamento univoco per seguire lo stato della richiesta in qualsiasi momento.
""".trimIndent() else """
# Support

Request assistance, report problems or bugs. Our team will answer as quickly as possible to help you resolve any difficulty.

**Supported categories**

- Technical Support
- Bug Report
- Information Request
- Complaint
- Suggestion
- Account Issues
- Privacy
- Other

**Response times:** our team tries to answer all requests within 24-48 hours. During peak periods, times may lengthen slightly.

**Have you checked the FAQ?** Many common questions already have an answer in the dedicated section. You could solve your problem in seconds.

To send an assistance request use the native app form (button below): you will receive a unique tracking code to follow the status of your request anytime.
""".trimIndent()
    }

    fun contatti(lang: String): String {
        val it = lang == "it" || lang.isEmpty()
        return if (it) """
# Contatti

Hai domande, proposte o suggerimenti? Scrivici! Il nostro team è sempre disponibile.

**Tempi di risposta:** cerchiamo di rispondere a tutti i messaggi entro 24-48 ore.

**Tipi di messaggio**

- Collaborazione
- Suggerimento
- Bug Report
- Domande
- Altro

Per inviarci un messaggio usa il modulo nativo dell'app (pulsante sotto). Riceverai un codice di tracciamento univoco per seguire lo stato della tua richiesta.
""".trimIndent() else """
# Contacts

Questions, proposals or suggestions? Write to us! Our team is always available.

**Response times:** we try to answer all messages within 24-48 hours.

**Message types**

- Collaboration
- Suggestion
- Bug Report
- Questions
- Other

To send us a message use the native app form (button below). You will receive a unique tracking code to follow the status of your request.
""".trimIndent()
    }

    fun privacy(lang: String): String {
        val it = lang == "it" || lang.isEmpty()
        return if (it) """
# Privacy Policy

**Data di ultima modifica:** 20 settembre 2026 · **Versione:** 2.2

La privacy è la pietra angolare di L.U.X. Verse. Questo documento descrive in modo esteso, rigoroso e trasparente come l'applicazione tratta i dati, quali informazioni non vengono mai raccolte e quali sono i tuoi diritti.

## 1. Assenza totale di identificativi personali

L.U.X. Verse è progettato secondo il principio **Privacy by Design**:

- **Nessun account.** Non viene richiesta registrazione, email, nome o telefono per usare alcuno strumento.
- **Nessun identificatore pubblicitario.** L'app non genera né memorizza ID pubblicitari (Advertising ID) e non integra SDK di tracciamento di terze parti.
- **Nessun dato biometrico.** L'app non acquisisce impronte digitali, riconoscimento facciale o altri dati biometrici.
- **Nessun profilo di utilizzo commerciale.** Non viene costruito alcun profilo personale: nessuna profilazione comportamentale, nessuna segmentazione pubblicitaria, nessuna vendita di dati a intermediari.

Poiché non raccogliamo identificativi personali, non esiste alcuna base dati da "de-anonimizzare" in caso di richieste esterne: non abbiamo nulla che ci consenta di riconoscere un singolo utente.

## 2. Crittografia locale e storage sul dispositivo

I dati generati durante l'utilizzo (diario, cronologie, log di attività, bozze) sono salvati **in locale sul telefono**, nel suo spazio di archiviazione privato:

- **Cifratura a riposo.** I dati applicativi sono protetti tramite crittografia supportata nativamente dalla piattaforma; i contenuti non vengono inviati a server di terze parti per l'archiviazione.
- **Isolamento per app.** L'archiviazione è confinata alla sandbox dell'applicazione: nessun'altra app può legittimamente accedere a quei file.
- **Chiavi sul chip.** Quando disponibile, la piattaforma usa l'hardware di sicurezza del dispositivo (KeyStore hardware-backed) per custodire il materiale crittografico, rendendo l'accesso fisico ai dati estremamente difficile.
- **Cancellazione totale con un clic.** Dalla sezione **Impostazioni Avanzate → Svuota cache locale** puoi cancellare in un solo gesto l'intero contenuto salvato sul dispositivo. L'operazione è permanente e non reversibile.

**Il tuo diritto fondamentale:** puoi, in ogni momento, azzerare completamente la presenza locale della tua attività con un clic. Non esiste backup remoto da cui un operatore possa ripristinarla.

## 3. Transito dei dati in modo cifrato

Le richieste destinate all'elaborazione da modelli di intelligenza artificiale possono transitare su infrastrutture di calcolo distribuite:

- **Cifratura in transito.** Tutte le comunicazioni avvengono su canali cifrati (HTTPS/TLS). Il contenuto è protetto durante il trasferimento dal dispositivo alla rete di elaborazione.
- **Elaborazione anonima.** Le richieste giungono senza identificativi personali associati: un nodo della rete non sa *chi* sta inviando il prompt, ma solo che esiste una richiesta da elaborare.
- **Nessuna persistenza dei prompt.** I nodi della rete di calcolo elaborano la richiesta in tempo reale e **non memorizzano i prompt altrui**: una volta restituita la risposta, il contenuto non viene conservato per fini di addestramento o successiva consultazione.
- **Nessun nodo può decifrare o archiviare i contenuti altrui.** Le infrastrutture di elaborazione operano su richieste anonime e aggregate; alcun nodo singolo è in grado di decifrare o memorizzare in modo persistente il contenuto di un altro utente.

Non vendiamo, non affittiamo e non concediamo in licenza alcun contenuto utente.

## 4. Cookie, tracciamento e strumenti analitici

- **Cookie:** il sito web usa esclusivamente cookie tecnici essenziali (sessione e preferenze di lingua). Nessun cookie di profilazione, tracciamento o marketing.
- **Analitiche:** non utilizziamo sistemi di analytics che monitorino il singolo visitatore nel tempo. Eventuali statistiche aggregate (numero di richieste elaborate, tempi di risposta) sono anonime e prive di riferimenti personali.

## 5. Limitazioni di calcolo e fair use

L.U.X. Verse è un ecosistema gratuito. Per garantire stabilità e parità di accesso possono essere applicate **soglie di fair use** (ad esempio un numero massimo di richieste per intervallo di tempo). Tali limiti sono basati su identificativi anonimi legati al dispositivo e non implicano la raccolta di dati personali.

## 6. Minorenni

Il servizio è offerto a tutti, inclusi studenti. Non raccogliamo dati di età né richiediamo consensi parentali digitali perché non trattiamo dati personali di alcun tipo.

## 7. Trasparenza open-source

- L.U.X. Verse aderisce ai principi di **trasparenza open-source internazionali**: le logiche di flusso dei dati e le policy sono documentate in modo pubblico e verificabile.
- Nessuna parte di questo documento è ambigua: se un dato non è elencato come raccolto, semplicemente non viene raccolto.

## 8. Contatti e diritti

Per qualsiasi domanda sulla privacy, contattaci tramite la pagina Contatti o il modulo nativo dell'app. Puoi esercitare in ogni momento il diritto alla cancellazione totale della cache locale direttamente dalle Impostazioni Avanzate dell'app.
""".trimIndent() else """
# Privacy Policy

**Last updated:** September 20, 2026 · **Version:** 2.2

Privacy is the cornerstone of L.U.X. Verse. This document describes, in a thorough, rigorous and transparent way, how the app handles data, which information is never collected and what your rights are.

## 1. Complete absence of personal identifiers

L.U.X. Verse is built on the **Privacy by Design** principle:

- **No account.** No registration, email, name or phone number is required to use any tool.
- **No advertising identifier.** The app does not generate or store Advertising IDs and does not integrate third-party tracking SDKs.
- **No biometric data.** The app does not collect fingerprints, facial recognition data or any other biometric information.
- **No commercial usage profile.** No personal profile is built: no behavioral profiling, no advertising segmentation, no selling of data to intermediaries.

Because we do not collect personal identifiers, there is no dataset to "de-anonymize" under external requests: we have no way to recognize a single user.

## 2. On-device encryption and local storage

Data generated during use (journal, histories, activity logs, drafts) is saved **locally on your phone**, in its private storage area:

- **Encryption at rest.** App data is protected with platform-native encryption; content is not sent to third-party servers for storage.
- **Per-app isolation.** Storage is confined to the app sandbox: no other app can legitimately access those files.
- **Keys on the chip.** When available, the platform uses the device security hardware (hardware-backed KeyStore) to protect cryptographic material, making physical access extremely difficult.
- **One-tap full deletion.** From **Advanced Settings → Clear local cache** you can delete the entire on-device content in a single action. The operation is permanent and irreversible.

**Your fundamental right:** at any moment you can reset the entire local footprint of your activity with one tap. There is no remote backup an operator could restore.

## 3. Encrypted data transit

Requests destined for AI model processing may travel through distributed computing infrastructures:

- **Encryption in transit.** All communications use encrypted channels (HTTPS/TLS). Content is protected while traveling from the device to the processing network.
- **Anonymous processing.** Requests arrive without personal identifiers: a network node does not know *who* is sending a prompt, only that a request needs processing.
- **No prompt persistence.** Processing nodes handle the request in real time and **do not store other users' prompts**: once the answer is returned, the content is not kept for training or later inspection.
- **No node can decrypt or archive others' content.** Processing infrastructures operate on anonymous, aggregated requests; no single node is able to decrypt or persistently store another user's content.

We do not sell, rent or license any user content.

## 4. Cookies, tracking and analytics

- **Cookies:** the website uses only essential technical cookies (session and language preferences). No profiling, tracking or marketing cookies.
- **Analytics:** we do not use analytics systems that monitor a single visitor over time. Any aggregate statistics (number of processed requests, response times) are anonymous and free of personal references.

## 5. Fair use and computation limits

L.U.X. Verse is a free ecosystem. To guarantee stability and fair access, **fair-use thresholds** (for example a maximum number of requests per time window) may be applied. Such limits are based on anonymous device identifiers and do not involve collecting personal data.

## 6. Minors

The service is open to everyone, including students. We do not collect age data nor require digital parental consent because we do not process any personal data.

## 7. Open-source transparency

- L.U.X. Verse follows **international open-source transparency principles**: data-flow logic and policies are documented publicly and verifiably.
- No part of this document is ambiguous: if a datum is not listed as collected, it simply is not collected.

## 8. Contact and rights

For any privacy question, contact us via the Contacts page or the native app form. You can exercise your right to full deletion of the local cache at any time directly from the app's Advanced Settings.
""".trimIndent()
    }

    fun termini(lang: String): String {
        val it = lang == "it" || lang.isEmpty()
        return if (it) """
# Termini di utilizzo

**Data di ultima modifica:** 20 settembre 2026 · **Versione:** 2.2

## 1. Accettazione

Usando L.U.X. Verse accetti i presenti termini. Se non li accetti, non potrai utilizzare i nostri servizi. I termini si applicano a tutti gli strumenti dell'ecosistema, senza eccezioni.

## 2. Uso dei servizi

- I nostri strumenti possono essere usati anche per scopi commerciali e professionali.
- Non è richiesta registrazione per utilizzare i servizi.
- Utilizza gli strumenti in modo responsabile: non automatizzare le richieste su larga scala e non tentare di aggirare le misure di fair use.
- È vietato usare i servizi per attività illecite, diffamatorie, discriminatorie o per generare contenuti che violino i diritti di terzi.

## 3. Disponibilità e continuità

- I servizi sono offerti gratuitamente e "così come sono", senza garanzia esplicita o implicita di disponibilità continua.
- Ci riserviamo la possibilità di introdurre misure di salvaguardia contro gli abusi.
- I progetti possono essere temporaneamente non disponibili durante le manutenzioni.
- L'ecosistema può essere aggiornato in qualsiasi momento: le nuove versioni dell'app sono distribuite come aggiornamenti opzionali e la mancata installazione non impedisce l'accesso alle funzioni già presenti.

## 4. Elaborazione locale e anonimato

- I contenuti che generi restano sul tuo dispositivo e possono essere cancellati in un clic.
- Le richieste AI vengono elaborate in modo anonimo: l'utente non è identificabile e i prompt non vengono memorizzati dai nodi di elaborazione.
- Accetti che per garantire il fair use possano essere applicate soglie di utilizzo basate su identificativi anonimi.

## 5. Proprietà intellettuale

- I contenuti generati dagli strumenti possono essere usati liberamente per scopi personali e professionali.
- Il marchio e il design di L.U.X. Verse appartengono al team di sviluppo.
- Non è consentito rivendere i servizi o impersonare l'ecosistema L.U.X. Verse.

## 6. Limiti di responsabilità

- L.U.X. Verse non è responsabile per l'uso dei contenuti generati.
- I risultati generati dall'IA possono contenere errori: verifica sempre l'accuratezza delle informazioni importanti, in particolare quelle di natura medica, legale o finanziaria.
- In nessun caso L.U.X. Verse sarà responsabile per danni indiretti, accidentali o consequenziali derivanti dall'uso dei servizi.

## 7. Modifiche ai termini

Possiamo aggiornare questi termini in qualsiasi momento. L'uso continuato del servizio dopo la pubblicazione di modifiche costituisce accettazione dei nuovi termini. La versione vigente è sempre disponibile nella sezione Info dell'app.

## 8. Legge applicabile e contatti

I presenti termini sono regolati dalla legge applicabile nella giurisdizione del gestore del servizio. Per domande sui termini, contattaci tramite la pagina Contatti o il modulo nativo dell'app.
""".trimIndent() else """
# Terms of Service

**Last updated:** September 20, 2026 · **Version:** 2.2

## 1. Acceptance

By using L.U.X. Verse you accept these terms. If you do not accept them, you will not be able to use our services. These terms apply to all tools of the ecosystem without exception.

## 2. Use of services

- Our tools can be used for commercial and professional purposes too.
- No registration is required to use the services.
- Use the tools responsibly: do not automate requests on a massive scale and do not attempt to circumvent fair-use measures.
- It is forbidden to use the services for illegal, defamatory, discriminatory activities or to generate content that violates third-party rights.

## 3. Availability and continuity

- Services are offered for free and "as is", without explicit or implicit guarantee of continuous availability.
- We reserve the right to introduce safeguards against abuse.
- Projects may be temporarily unavailable during maintenance.
- The ecosystem may be updated at any time: new versions of the app are distributed as optional updates and not installing them does not prevent access to existing features.

## 4. Local processing and anonymity

- Content you generate stays on your device and can be deleted with one tap.
- AI requests are processed anonymously: the user is not identifiable and prompts are not stored by processing nodes.
- You accept that fair-use thresholds based on anonymous identifiers may be applied.

## 5. Intellectual property

- Content generated by the tools can be freely used for personal and professional purposes.
- The L.U.X. Verse brand and design belong to the development team.
- Reselling the services or impersonating the L.U.X. Verse ecosystem is not allowed.

## 6. Limitation of liability

- L.U.X. Verse is not responsible for the use of generated content.
- AI-generated results may contain errors: always verify the accuracy of important information, especially medical, legal or financial.
- In no event shall L.U.X. Verse be liable for indirect, incidental or consequential damages arising from the use of the services.

## 7. Changes to these terms

We may update these terms at any time. Continued use of the service after changes are published constitutes acceptance of the new terms. The current version is always available in the Info section of the app.

## 8. Governing law and contact

These terms are governed by the law applicable in the jurisdiction of the service operator. For questions about these terms, contact us via the Contacts page or the native app form.
""".trimIndent()
    }
}