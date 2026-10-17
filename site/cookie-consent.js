// ============================================
// LUX.CONSENT — avviso su cookie e privacy
// ============================================
// COS'E' CAMBIATO (16/10/2026) E PERCHE'
// Prima questo file apriva un riquadro a tutto schermo che BLOCCAVA l'accesso
// al sito finche' non si sceglieva fra "Accetto", "Non accetto" e "Ignora", e
// la via "Ignora" apriva una seconda schermata con l'indirizzo IP del
// visitatore, chiesto a un servizio esterno (api.ipify.org), e lo registrava
// in un archivio di firme.
// (Quella "firma digitale", con il suo indirizzo api/save_signature.php e
//  l'archivio data/signatures.json, e' stata poi rimossa del tutto: non ne
//  resta traccia da nessuna parte.)
// Tre cose non andavano:
//   1. il sito usa SOLO cookie tecnici (lo dice la pagina Privacy) e non ha
//      tracciamento: un blocco all'accesso per dei cookie tecnici non serve a
//      nessuno e contraddice le promesse del sito;
//   2. l'indirizzo del visitatore partiva verso un sito di terzi e finiva in un
//      archivio, mentre la Privacy dice che l'indirizzo IP si usa solo per
//      limitare gli abusi e per il registro degli accessi al pannello;
//   3. le frasi erano scritte in italiano dentro il file: chi leggeva il sito
//      in inglese trovava un muro in italiano.
// Adesso c'e' un avviso piccolo in fondo alla pagina, chiudibile, con i
// collegamenti a Privacy e Termini. Non blocca niente, non manda niente a
// nessuno e non registra l'indirizzo di chi legge.
// ============================================
(function () {
    const CONFIG = {
        storageKey: 'workme_consent_status',
        version: '1.0.0',      // la stessa di prima: chi ha gia' scelto non lo rivede
        delay: 800
    };

    // Le frasi arrivano dalla pagina (window.LUX_CONSENT_T, vedi
    // includes/lux.php), cosi' il file resta uno solo per tutte le lingue.
    // Qui sotto c'e' il ripiego italiano.
    const T = Object.assign({
        title: 'Cookie e privacy',
        text: 'Questo sito usa solo cookie tecnici: nessun tracciamento, nessuna pubblicità, nessun cookie di terze parti.',
        accept: 'Accetto',
        close: 'Chiudi'
    }, window.LUX_CONSENT_T || {});

    // === HA GIA' SCELTO? ===
    function hasChoice() {
        try {
            const saved = localStorage.getItem(CONFIG.storageKey);
            if (saved) {
                const parsed = JSON.parse(saved);
                if (parsed && parsed.status) return true;
            }
        } catch (e) {
            // localStorage non disponibile: si prova col cookie
        }
        return document.cookie.indexOf('workme_consent=') !== -1;
    }

    // === SALVA LA SCELTA (solo sul dispositivo di chi legge) ===
    // Come prima: nessuna chiamata al server, niente da conservare altrove.
    function saveChoice(status) {
        const data = JSON.stringify({
            status: status,
            version: CONFIG.version,
            timestamp: new Date().toISOString()
        });
        try {
            localStorage.setItem(CONFIG.storageKey, data);
        } catch (e) {
            document.cookie = 'workme_consent=' + encodeURIComponent(status)
                + ';max-age=31536000;path=/;samesite=lax';
        }
    }

    // === L'AVVISO ===
    function build() {
        if (document.getElementById('lux-cookie-notice')) return;

        const box = document.createElement('div');
        box.id = 'lux-cookie-notice';
        box.setAttribute('role', 'region');
        box.setAttribute('aria-label', T.title);
        box.style.cssText = 'position:fixed;left:50%;bottom:16px;transform:translateX(-50%);'
            + 'z-index:8900;width:min(720px,calc(100% - 24px));background:#121417;'
            + 'border:1px solid rgba(216,169,60,.35);border-radius:14px;'
            + 'box-shadow:0 12px 40px rgba(0,0,0,.45);padding:14px 16px;'
            + 'display:flex;gap:14px;align-items:center;flex-wrap:wrap;'
            + 'font-size:13px;line-height:1.6;color:#A5A197;'
            + 'font-family:"Inter Tight",system-ui,sans-serif;';

        const testo = document.createElement('p');
        testo.style.cssText = 'margin:0;flex:1 1 320px;';
        const forte = document.createElement('strong');
        forte.style.cssText = 'color:#EDE9E0;display:block;margin-bottom:4px;';
        forte.textContent = T.title;
        testo.appendChild(forte);
        // Il testo puo' contenere i due collegamenti a Privacy e Termini: e'
        // una stringa nostra, scritta in lang/lang.php, non roba di chi legge.
        const corpo = document.createElement('span');
        corpo.innerHTML = T.text;
        testo.appendChild(corpo);

        const azioni = document.createElement('div');
        azioni.style.cssText = 'display:flex;gap:8px;align-items:center;';

        const accetta = document.createElement('button');
        accetta.type = 'button';
        accetta.id = 'lux-cookie-accept';
        accetta.textContent = T.accept;
        accetta.style.cssText = 'padding:10px 18px;background:linear-gradient(135deg,#D8A93C,#F0CE86);'
            + 'color:#12140f;border:0;border-radius:10px;font:600 13px/1 "Inter Tight",system-ui,sans-serif;'
            + 'cursor:pointer;';

        const chiudi = document.createElement('button');
        chiudi.type = 'button';
        chiudi.id = 'lux-cookie-close';
        chiudi.textContent = T.close;
        chiudi.style.cssText = 'padding:10px 14px;background:transparent;color:#A5A197;'
            + 'border:1px solid rgba(255,255,255,.18);border-radius:10px;'
            + 'font:600 13px/1 "Inter Tight",system-ui,sans-serif;cursor:pointer;';

        azioni.appendChild(accetta);
        azioni.appendChild(chiudi);
        box.appendChild(testo);
        box.appendChild(azioni);
        document.body.appendChild(box);

        // Il pulsante di ricerca e quello della guida stanno in basso a
        // destra, cioe' esattamente dove finisce questo avviso: prima lo
        // copriva e il tocco finiva qui. Si segnala alla pagina quanto e'
        // alto l'avviso (variabile --lux-cookie-h) e si alza la pila dei due
        // pulsanti sopra di esso (vedi includes/platform-ui.php).
        document.body.classList.add('lux-cookie-open');
        try {
            document.documentElement.style.setProperty('--lux-cookie-h', box.offsetHeight + 'px');
        } catch (e) {}

        accetta.addEventListener('click', function () { saveChoice('accepted'); togli(); });
        chiudi.addEventListener('click', function () { saveChoice('dismissed'); togli(); });
    }

    function togli() {
        const box = document.getElementById('lux-cookie-notice');
        document.body.classList.remove('lux-cookie-open');
        try { document.documentElement.style.removeProperty('--lux-cookie-h'); } catch (e) {}
        if (!box) return;
        box.style.transition = 'opacity .25s';
        box.style.opacity = '0';
        window.setTimeout(function () { if (box.parentNode) box.parentNode.removeChild(box); }, 260);
    }

    function init() {
        if (hasChoice()) return;
        window.setTimeout(build, CONFIG.delay);
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
