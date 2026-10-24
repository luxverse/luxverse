package org.luxverse.app.net

import android.util.Log
import java.io.ByteArrayInputStream
import java.io.ByteArrayOutputStream
import java.net.HttpURLConnection
import java.net.InetSocketAddress
import java.net.URL
import java.security.KeyStore
import java.security.cert.CertificateException
import java.security.cert.CertificateFactory
import java.security.cert.X509Certificate
import javax.net.ssl.SSLContext
import javax.net.ssl.SSLSocket
import javax.net.ssl.SSLSocketFactory
import javax.net.ssl.TrustManager
import javax.net.ssl.TrustManagerFactory
import javax.net.ssl.X509TrustManager

/**
 * LA CATENA DEL CERTIFICATO, COMPLETATA E VERIFICATA QUI.
 *
 * IL PROBLEMA (19/10/2026). L'hosting consegna il certificato del sito da
 * solo, senza gli anelli che portano a una radice riconosciuta. Il
 * certificato e' autentico e lo ha emesso Let's Encrypt, ma chi lo riceve
 * non ha, accanto, l'anello intermedio con cui arrivare a una radice che il
 * telefono conosce. Chrome e Windows se la cavano lo stesso: leggono dentro
 * il certificato l'indirizzo da cui scaricare l'anello mancante (e' un campo
 * del certificato, si chiama "Authority Information Access") e se lo
 * prendono. Le app Android no: non lo fanno da sole, e per loro il sito non
 * esiste. Verificato il 18/10/2026: il server manda UN solo certificato,
 * `openssl verify` risponde "unable to get local issuer certificate", e la
 * catena completata a mano (sito -> YR2 -> Root YR) verifica.
 *
 * COSA FA QUESTO FILE. Esattamente quello che fa Chrome, e niente di meno:
 *  1. guarda la catena che il server manda;
 *  2. se non arriva a una radice, segue gli indirizzi scritti DENTRO i
 *     certificati e scarica gli anelli mancanti (al massimo tre);
 *  3. passa la catena cosi' completata alle autorita' di fiducia DEL
 *     TELEFONO (TrustManager di sistema) e al controllo del nome;
 *  4. solo se il telefono dice "va bene" la connessione si considera
 *     sicura.
 *
 * Quello che questo file NON fa, e non fara' mai: accettare un certificato
 * perche' "tanto e' il nostro sito". Non c'e' nessun certificato scritto
 * dentro l'app, nessuna scorciatoia, nessun "fidati comunque". Se la catena
 * non si ricostruisce, o se il telefono non la riconosce, la risposta e'
 * NO e l'app si ferma dicendo che il collegamento non e' sicuro. La
 * sicurezza della connessione resta una decisione del telefono: qui si
 * fornisce soltanto il pezzo che il server si e' dimenticato di mandare.
 *
 * Gli anelli si scaricano anche in chiaro (http): e' cosi' che sono
 * pubblicati e non e' un rischio, perche' il contenuto scaricato non viene
 * creduto sulla parola — deve comunque superare la verifica crittografica
 * del telefono, che un certificato sbagliato non passa.
 */
object SiteTls {

    private const val TAG = "SiteTls"

    /** Quanti anelli si possono scaricare, al massimo. */
    private const val MAX_ANELLI = 3

    /** Un anello e' piccolo: se arriva qualcosa di enorme, non e' un certificato. */
    private const val MAX_BYTE = 16 * 1024

    /** Quanto vale un esito positivo, prima di riverificare. */
    private const val VALIDITA_ESITO_MS = 12L * 60 * 60 * 1000

    /**
     * Quanto vale un esito NEGATIVO.
     *
     * Un "no" non e' una verita' permanente: puo' dipendere da un minuto di
     * rete assente mentre si scaricava un anello, o da un certificato appena
     * rinnovato. Tenendolo per dodici ore, un solo controllo andato male
     * lasciava l'app ferma su "Collegamento non sicuro" per mezza giornata:
     * era questo, il difetto che la faceva sembrare rotta. Un minuto e' il
     * compromesso giusto: chi ha davvero un problema non martella il server, e
     * chi ha avuto un intoppo si riprende da solo.
     */
    private const val VALIDITA_NEGATIVA_MS = 60L * 1000

    /** Anelli gia' scaricati, per non riscaricarli a ogni connessione. */
    private val anelli = HashMap<String, X509Certificate>()

    /** Esiti gia' ottenuti: host -> (quando, esito). */
    private val esiti = HashMap<String, Pair<Long, Boolean>>()

    /** La verifica e' una sola per volta: due controlli insieme non servono. */
    private val lucchetto = Any()

    /** Il certificato di sistema, gia' pronto (le autorita' del telefono). */
    private val autoritaDiSistema: X509TrustManager by lazy {
        val fabbrica = TrustManagerFactory.getInstance(TrustManagerFactory.getDefaultAlgorithm())
        fabbrica.init(null as KeyStore?)
        fabbrica.trustManagers.filterIsInstance<X509TrustManager>().firstOrNull()
            ?: throw IllegalStateException("il telefono non espone le sue autorita' di fiducia")
    }

    /**
     * Vero se il collegamento con [host] e' verificabile dal telefono, anche
     * quando il server manda la catena incompleta.
     *
     * Va chiamata FUORI dal thread dell'interfaccia: fa rete e puo' metterci
     * qualche secondo. Blocca finche' non ha un esito, e non lancia mai:
     * qualunque intoppo vale "no".
     */
    fun verifica(host: String, porta: Int = 443): Boolean {
        if (host.isEmpty()) return false
        synchronized(lucchetto) {
            esiti[host]?.let { (quando, esito) ->
                val durata = if (esito) VALIDITA_ESITO_MS else VALIDITA_NEGATIVA_MS
                if (System.currentTimeMillis() - quando < durata) return esito
            }
            val esito = try {
                mano(host, porta)
                true
            } catch (t: Throwable) {
                Log.i(TAG, "catena non verificabile per $host: ${t.javaClass.simpleName}")
                false
            }
            esiti[host] = System.currentTimeMillis() to esito
            return esito
        }
    }

    /**
     * Una fabbrica di connessioni sicure per [host]: si comporta come quella
     * del telefono, ma prima di dare un verdetto completa la catena.
     *
     * Serve alle richieste che l'app fa da sola (per esempio la verifica
     * dell'hosting): senza questa, quelle richieste fallivano per lo stesso
     * motivo della WebView, e il biscotto non si poteva nemmeno preparare.
     */
    fun fabbrica(host: String): SSLSocketFactory? = try {
        val contesto = SSLContext.getInstance("TLS")
        contesto.init(null, arrayOf<TrustManager>(Catena(host)), null)
        contesto.socketFactory
    } catch (t: Throwable) {
        null
    }

    /** Un giro di mano completo: se arriva in fondo, il telefono ha detto si'. */
    private fun mano(host: String, porta: Int) {
        val contesto = SSLContext.getInstance("TLS")
        contesto.init(null, arrayOf<TrustManager>(Catena(host)), null)
        val socket = contesto.socketFactory.createSocket() as SSLSocket
        socket.use {
            it.soTimeout = 12_000
            it.connect(InetSocketAddress(host, porta), 8_000)
            // Il nome: deve essere davvero quello del certificato. Il
            // controllo lo fa la libreria del telefono, non noi.
            it.sslParameters = it.sslParameters.apply { endpointIdentificationAlgorithm = "HTTPS" }
            it.startHandshake()
        }
    }

    /**
     * Il controllore di fiducia: chiede al telefono, completando la catena
     * quando serve. Non e' piu' indulgente del telefono: delega a lui.
     */
    private class Catena(private val host: String) : X509TrustManager {

        override fun checkServerTrusted(catena: Array<X509Certificate>, tipo: String) {
            // 1. Cosi' com'e': se il server manda tutto, e' gia' finita.
            try {
                autoritaDiSistema.checkServerTrusted(catena, tipo)
                return
            } catch (_: CertificateException) {
                // manca un pezzo: si va a prenderlo
            }

            // 2. Si seguono gli indirizzi scritti dentro i certificati, come
            //    fa Chrome, e si richiede il verdetto al telefono appena la
            //    catena arriva in fondo.
            val completa = catena.toMutableList()
            var corrente = catena.last()
            var giro = 0
            while (giro < MAX_ANELLI) {
                val emittente = emittenteDi(corrente, completa) ?: break
                completa.add(emittente)
                corrente = emittente
                try {
                    autoritaDiSistema.checkServerTrusted(completa.toTypedArray(), tipo)
                    Log.i(TAG, "catena completata per $host con ${completa.size} certificati")
                    return
                } catch (_: CertificateException) {
                    // ancora non basta: si prova un anello piu' su
                }
                giro++
            }
            throw CertificateException("catena incompleta o non riconosciuta per $host")
        }

        override fun checkClientTrusted(catena: Array<X509Certificate>, tipo: String) {
            throw CertificateException("questa app non accetta certificati dei client")
        }

        override fun getAcceptedIssuers(): Array<X509Certificate> = autoritaDiSistema.acceptedIssuers
    }

    /**
     * L'anello che ha emesso [cert], scaricato dall'indirizzo che il
     * certificato stesso dichiara. Null se non c'e' o non si riesce.
     */
    private fun emittenteDi(cert: X509Certificate, gia: List<X509Certificate>): X509Certificate? {
        val atteso = cert.issuerX500Principal
        if (cert.subjectX500Principal == atteso) return null     // gia' in cima

        synchronized(anelli) { anelli[atteso.name]?.let { return it } }

        for (indirizzo in indirizziAnello(cert)) {
            val grezzo = scarica(indirizzo) ?: continue
            for (candidato in leggi(grezzo)) {
                if (candidato.subjectX500Principal != atteso) continue
                if (gia.any { it.encoded.contentEquals(candidato.encoded) }) continue
                synchronized(anelli) { anelli[atteso.name] = candidato }
                Log.i(TAG, "anello scaricato da $indirizzo")
                return candidato
            }
        }
        return null
    }

    /** Scarica un certificato: prima come dichiarato, poi con l'altro schema. */
    private fun scarica(indirizzo: String): ByteArray? {
        val tentativi = if (indirizzo.startsWith("http://")) listOf(indirizzo, "https://" + indirizzo.removePrefix("http://"))
        else listOf(indirizzo, "http://" + indirizzo.removePrefix("https://"))
        for (url in tentativi) {
            var c: HttpURLConnection? = null
            try {
                c = URL(url).openConnection() as HttpURLConnection
                c.instanceFollowRedirects = true
                c.connectTimeout = 6_000
                c.readTimeout = 8_000
                c.setRequestProperty("Accept", "application/pkix-cert, application/x-x509-ca-cert, */*")
                c.inputStream.use { scorrimento ->
                    val fuori = ByteArrayOutputStream()
                    val pezzo = ByteArray(4096)
                    while (true) {
                        val n = scorrimento.read(pezzo)
                        if (n <= 0) break
                        fuori.write(pezzo, 0, n)
                        if (fuori.size() > MAX_BYTE) break     // non e' un certificato
                    }
                    val dati = fuori.toByteArray()
                    if (dati.isNotEmpty() && dati.size <= MAX_BYTE) return dati
                }
            } catch (t: Throwable) {
                // si prova il tentativo successivo
            } finally {
                c?.disconnect()
            }
        }
        return null
    }

    private fun leggi(dati: ByteArray): List<X509Certificate> = try {
        CertificateFactory.getInstance("X.509")
            .generateCertificates(ByteArrayInputStream(dati))
            .filterIsInstance<X509Certificate>()
    } catch (t: Throwable) {
        emptyList()
    }

    // ------------------------------------------------------------
    // Lettura del campo "Authority Information Access"
    // ------------------------------------------------------------
    // E' una struttura ASN.1 piccola e stabile: si legge a mano, senza
    // aggiungere librerie all'APK. Se il campo non c'e' o e' scritto in un
    // modo che non si capisce, si restituisce l'elenco vuoto e la catena
    // resta incompleta: il verdetto sara' "no", mai "si' per gentilezza".
    private const val OID_CA_ISSUERS = "1.3.6.1.5.5.7.48.2"

    private fun indirizziAnello(cert: X509Certificate): List<String> {
        val estensione = cert.getExtensionValue("1.3.6.1.5.5.7.1.1") ?: return emptyList()
        val testa = IntArray(1)
        return try {
            var p = salta(estensione, 0)           // OCTET STRING esterno
            p = intestazione(estensione, p, testa) // SEQUENCE
            val fine = p + testa[0]
            val trovati = ArrayList<String>(2)
            while (p < fine) {
                p = intestazione(estensione, p, testa)   // AccessDescription
                val fineVoce = p + testa[0]
                if (p < fineVoce && estensione[p] == 0x06.toByte()) {
                    p = intestazione(estensione, p, testa)
                    val oid = oid(estensione, p, testa[0])
                    p += testa[0]
                    if (oid == OID_CA_ISSUERS && p < fineVoce && estensione[p] == 0x86.toByte()) {
                        p = intestazione(estensione, p, testa)
                        trovati.add(String(estensione, p, testa[0], Charsets.US_ASCII))
                    }
                }
                p = fineVoce
            }
            trovati
        } catch (t: Throwable) {
            emptyList()
        }
    }

    private fun salta(dati: ByteArray, p: Int): Int = intestazione(dati, p, IntArray(1))

    /** Legge la testata ASN.1 e restituisce dove comincia il contenuto. */
    private fun intestazione(dati: ByteArray, da: Int, lunghezza: IntArray): Int {
        var p = da + 1                                  // il tipo
        val primo = dati[p++].toInt() and 0xff
        if (primo < 0x80) {
            lunghezza[0] = primo
            return p
        }
        val quanti = primo and 0x7f
        var valore = 0
        for (i in 0 until quanti) valore = (valore shl 8) or (dati[p++].toInt() and 0xff)
        lunghezza[0] = valore
        return p
    }

    /** Un identificatore di oggetto ASN.1 in forma leggibile (1.3.6.1...). */
    private fun oid(dati: ByteArray, da: Int, quanti: Int): String {
        val testo = StringBuilder()
        val primo = dati[da].toInt() and 0xff
        testo.append(primo / 40).append('.').append(primo % 40)
        var valore = 0L
        for (i in 1 until quanti) {
            val x = dati[da + i].toInt() and 0xff
            valore = (valore shl 7) or (x and 0x7f).toLong()
            if (x and 0x80 == 0) {
                testo.append('.').append(valore)
                valore = 0
            }
        }
        return testo.toString()
    }
}
