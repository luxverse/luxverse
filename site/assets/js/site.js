// ============================================================
// SITE.JS v4 — L.U.X. VERSE
// Loader rapido · menu mobile · dropdown · header · toast · markdown
// Nessuna emoji: le icone sono SVG inline (includes/icons.php).
// Nessun canvas / particellare: fluidità massima su mobile.
// ============================================================

(function () {
    'use strict';

    // ---------------------------------------------------------
    // LA LINGUA RESTA SCELTA ANCHE SULLE PAGINE GIÀ PRONTE
    // ---------------------------------------------------------
    // Molte pagine vengono consegnate come file già pronti (nessun PHP): lì
    // il server non può ricordare la lingua scelta. ?lang=en nell'indirizzo
    // dice la lingua della pagina che stai leggendo, ma senza questa riga il
    // cookie resterebbe quello di prima e la pagina dopo tornerebbe in
    // italiano. Qui si allinea il cookie all'indirizzo, una volta sola.
    (function () {
        var m = /[?&]lang=(it|en)(&|$)/.exec(location.search);
        if (!m) return;
        var c = /(?:^|; )workme_lang=([^;]*)/.exec(document.cookie || '');
        if (c && c[1] === m[1]) return;
        document.cookie = 'workme_lang=' + m[1] + ';path=/;max-age=31536000;samesite=lax';
    })();

    // ---------------------------------------------------------
    // OGNI PAGINA SI APRE DALL'ALTO
    // ---------------------------------------------------------
    // Il browser a volte conserva la posizione di scorrimento e la pagina
    // nuova si presenta già "in fondo": sembra che le opzioni rapide si
    // blocchino. Qui si disattiva il ripristino automatico e si torna in cima.
    if ('scrollRestoration' in history) { history.scrollRestoration = 'manual'; }
    window.scrollTo(0, 0);
    window.addEventListener('pageshow', function () {
        if (!location.hash) window.scrollTo(0, 0);
    });

    // ---------------------------------------------------------
    // LOADER — rapido: si chiude appena la pagina è pronta
    // ---------------------------------------------------------
    var loader   = document.getElementById('workme-loader-overlay');
    var barFill  = document.getElementById('loaderBarFill');
    var percentEl = document.getElementById('loaderPercent');
    var progress = 0;
    var loaderDone = false;

    function setProgress(p) {
        progress = Math.min(100, p);
        if (barFill) barFill.style.width = progress + '%';
        if (percentEl) percentEl.textContent = Math.floor(progress);
    }

    function hideLoader() {
        if (!loader || loaderDone) return;
        loaderDone = true;
        setProgress(100);
        loader.classList.add('hidden');
        window.setTimeout(function () { loader.style.display = 'none'; }, 380);
        document.dispatchEvent(new CustomEvent('lux:loader-done'));
    }

    var startedAt = (window.performance && performance.now) ? performance.now() : Date.now();
    function finish() {
        var elapsed = ((window.performance && performance.now) ? performance.now() : Date.now()) - startedAt;
        var delay = Math.max(0, 420 - elapsed);
        window.setTimeout(function () { setProgress(96); window.setTimeout(hideLoader, 90); }, delay);
    }

    setProgress(55);
    if (document.readyState === 'complete') finish();
    else window.addEventListener('load', finish);
    // Rete lenta: non bloccare mai l'utente più di 1,2 s
    window.setTimeout(hideLoader, 1200);

    // ---------------------------------------------------------
    // MENU MOBILE
    // ---------------------------------------------------------
    var hamburger = document.getElementById('hamburger');
    var mobileMenu = document.getElementById('mobileMenu');
    var overlay = document.getElementById('mobileMenuOverlay');

    if (hamburger && mobileMenu) {
        var closeMenu = function () {
            hamburger.classList.remove('active');
            mobileMenu.classList.remove('active');
            if (overlay) overlay.classList.remove('active');
            hamburger.setAttribute('aria-expanded', 'false');
            document.body.classList.remove('is-locked');
        };
        hamburger.addEventListener('click', function () {
            var open = !mobileMenu.classList.contains('active');
            hamburger.classList.toggle('active', open);
            mobileMenu.classList.toggle('active', open);
            if (overlay) overlay.classList.toggle('active', open);
            hamburger.setAttribute('aria-expanded', open ? 'true' : 'false');
            document.body.classList.toggle('is-locked', open);
        });
        if (overlay) overlay.addEventListener('click', closeMenu);
        mobileMenu.querySelectorAll('a').forEach(function (link) { link.addEventListener('click', closeMenu); });
        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape') closeMenu();
        });
    }

    // ---------------------------------------------------------
    // HEADER — stato "scrolled" senza jank (rAF + passive)
    // ---------------------------------------------------------
    var header = document.getElementById('header');
    if (header) {
        var ticking = false;
        var lastState = false;
        var onScroll = function () {
            if (ticking) return;
            ticking = true;
            window.requestAnimationFrame(function () {
                var state = window.pageYOffset > 12;
                if (state !== lastState) {
                    lastState = state;
                    header.classList.toggle('scrolled', state);
                }
                ticking = false;
            });
        };
        window.addEventListener('scroll', onScroll, { passive: true });
        onScroll();
    }

    // ---------------------------------------------------------
    // DROPDOWN STRUMENTI
    // ---------------------------------------------------------
    var toolDropdown = document.getElementById('toolsDropdown');
    if (toolDropdown) {
        var trigger = toolDropdown.querySelector('.dropdown-trigger');
        if (trigger) {
            trigger.addEventListener('click', function (e) {
                if (window.innerWidth <= 920) {
                    e.preventDefault();
                    var target = trigger.getAttribute('href');
                    if (toolDropdown.classList.contains('open')) {
                        toolDropdown.classList.remove('open');
                        if (target) window.location.href = target;
                    } else {
                        toolDropdown.classList.add('open');
                    }
                }
            });
        }
    }
})();

// ============================================================
// TOAST — icone SVG, nessuna emoji
// ============================================================
function showToast(message, type) {
    type = type || 'info';
    var container = document.querySelector('.toast-container');
    if (!container) {
        container = document.createElement('div');
        container.className = 'toast-container';
        document.body.appendChild(container);
    }

    var paths = {
        success: '<polyline points="20 6.5 9.5 17 4 11.5"/>',
        error:   '<line x1="6" y1="6" x2="18" y2="18"/><line x1="18" y1="6" x2="6" y2="18"/>',
        info:    '<circle cx="12" cy="12" r="9.5"/><line x1="12" y1="16" x2="12" y2="11.5"/><line x1="12" y1="8" x2="12.01" y2="8"/>'
    };
    var svg = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" '
            + 'stroke-linejoin="round" aria-hidden="true">' + (paths[type] || paths.info) + '</svg>';

    var toast = document.createElement('div');
    toast.className = 'toast ' + type;
    toast.setAttribute('role', 'status');
    toast.innerHTML = svg + '<span></span>';
    var msg = window.luxDeEmoji ? window.luxDeEmoji(String(message)) : String(message);
    toast.lastChild.innerHTML = msg;
    if (window.luxUpgrade) window.luxUpgrade(toast.lastChild);
    container.appendChild(toast);

    window.setTimeout(function () {
        toast.style.transition = 'opacity .25s, transform .25s';
        toast.style.opacity = '0';
        toast.style.transform = 'translateY(8px)';
        window.setTimeout(function () { toast.remove(); }, 260);
    }, 3200);
}

// ============================================================
// MARKDOWN + pulizia residui LaTeX
// ============================================================
function markdownToHTML(text) {
    text = String(text == null ? '' : text);
    text = text.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;');

    text = text.replace(/\\\(/g, '').replace(/\\\)/g, '');
    text = text.replace(/\\\[/g, '').replace(/\\\]/g, '');
    text = text.replace(/\$\$/g, '').replace(/\$/g, '');

    text = text.replace(/\\frac\{([^}]+)\}\{([^}]+)\}/g, '($1)/($2)');
    text = text.replace(/\\sqrt\{([^}]+)\}/g, '\u221A($1)');
    text = text.replace(/\\text\{([^}]+)\}/g, '$1');
    text = text.replace(/\\times/g, '\u00D7');
    text = text.replace(/\\div/g, '\u00F7');
    text = text.replace(/\\cdot/g, '\u00B7');
    text = text.replace(/\\pi/g, '\u03C0');
    text = text.replace(/\\infty/g, '\u221E');
    text = text.replace(/\\displaystyle/g, '');
    text = text.replace(/\\left/g, '').replace(/\\right/g, '');

    text = text.replace(/^### (.*$)/gim, '<h3>$1</h3>');
    text = text.replace(/^## (.*$)/gim, '<h2>$1</h2>');
    text = text.replace(/^# (.*$)/gim, '<h1>$1</h1>');

    text = text.replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>');
    text = text.replace(/\*(.*?)\*/g, '<em>$1</em>');
    text = text.replace(/`(.*?)`/g, '<code>$1</code>');

    text = text.replace(/^\s*-\s(.*$)/gim, '<ul><li>$1</li></ul>');
    text = text.replace(/<\/ul>\s*<ul>/g, '');

    text = text.replace(/^\s*(\d+)\.\s(.*$)/gim, '<ol start="$1"><li>$2</li></ol>');
    text = text.replace(/<\/ol>\s*<ol[^>]*>/g, '');

    text = text.replace(/^(?!<h[1-6]|<ul|<ol|<hr>|<strong>|<em>|<code>)(.*$)/gim, '<p>$1</p>');
    text = text.replace(/<p>\s*<\/p>/g, '');
    return text;
}

// ============================================================
// TASTIERA DEL TELEFONO — IL CAMPO IN SCRITTURA RESTA IN VISTA
// ============================================================
// Sul telefono, appena si tocca un campo, la tastiera copre quasi metà
// schermo: se il campo finisce sotto i tasti si scrive alla cieca, senza
// vedere quello che si scrive. Qui, appena la tastiera si apre, il campo
// viene riportato in vista e la pagina scorre di quanto basta.
//
// Vale sia dentro l'app L.U.X. Verse sia in un browser normale, e non serve
// sapere QUANTO è alta la tastiera: si guarda il "visual viewport", l'unica
// misura che si accorcia davvero quando i tasti compaiono.
(function () {
    'use strict';

    var vv = window.visualViewport;
    var campo = null;
    var MARGINE = 24;      // spazio libero fra il campo e la tastiera
    var TESTATA = 76;      // altezza dell'intestazione fissa, in alto

    function inVista(el, subito) {
        if (!el || !el.getBoundingClientRect) return;
        var r    = el.getBoundingClientRect();
        var alto = (vv && vv.height) ? vv.height : window.innerHeight;
        if (r.height === 0 && r.width === 0) return;
        var sotto  = r.bottom > alto - MARGINE;
        var sopra  = r.top < TESTATA && r.height < alto - TESTATA;
        if (!sotto && !sopra) return;
        var delta = sotto
            ? (r.bottom - alto + MARGINE + 8)
            : (r.top - TESTATA - 8);
        window.scrollBy({ top: Math.round(delta), behavior: subito ? 'auto' : 'smooth' });
    }

    function eUnCampo(t) {
        if (!t || !t.matches || t.disabled || t.readOnly) return false;
        return t.matches('input:not([type="hidden"]):not([type="checkbox"]):not([type="radio"]), textarea, select, [contenteditable="true"]');
    }

    document.addEventListener('focusin', function (e) {
        if (!eUnCampo(e.target)) return;
        campo = e.target;
        // La tastiera non compare all'istante e la sua altezza cambia mentre
        // si apre: si ricontrolla più volte, con calma.
        [60, 200, 420, 700].forEach(function (ms) {
            window.setTimeout(function () { if (campo === e.target) inVista(campo); }, ms);
        });
    });
    document.addEventListener('focusout', function () { campo = null; });

    if (vv) {
        vv.addEventListener('resize', function () { inVista(campo, true); });
        vv.addEventListener('scroll', function () { inVista(campo, true); });
    }
})();

// ============================================================
// ESCAPE HTML
// ============================================================
function escapeHtml(str) {
    var div = document.createElement('div');
    div.textContent = String(str == null ? '' : str);
    return div.innerHTML;
}
// ============================================================
// L.U.X. 40.0.0 — COMPARSA FLUIDA + STATO CONDIVISO
// ============================================================

(function () {
    'use strict';

    // ---------------------------------------------------------
    // 1. COMPARSA FLUIDA — le sezioni entrano dall'alto
    // ---------------------------------------------------------
    // Solo su transform/opacity, e solo se il browser sa farlo: dove non
    // c'e' IntersectionObserver il contenuto resta semplicemente visibile.
    var targets = document.querySelectorAll(
        '.glass-card, .b-card, .project-card, .info-box, .intent-box, .trust-badges, .section-head, .sec-head, .cta-box'
    );
    if ('IntersectionObserver' in window && targets.length) {
        var io = new IntersectionObserver(function (entries) {
            entries.forEach(function (e) {
                if (!e.isIntersecting) return;
                e.target.classList.add('on');
                io.unobserve(e.target);
            });
        }, { rootMargin: '0px 0px -8% 0px', threshold: 0.05 });
        targets.forEach(function (el, i) {
            el.classList.add('lux-reveal');
            el.style.transitionDelay = Math.min(6, i % 8) * 45 + 'ms';
            io.observe(el);
        });
        // Rete lenta o stampa: dopo 1,6 s si mostra tutto comunque.
        window.setTimeout(function () {
            document.querySelectorAll('.lux-reveal:not(.on)').forEach(function (el) { el.classList.add('on'); });
        }, 1600);
    }

    // ---------------------------------------------------------
    // 2. REVISIONE CONDIVISA (tempo reale)
    // ---------------------------------------------------------
    // Una sola chiamata piccola dice se e' cambiato qualcosa: notifiche,
    // chat, stati degli strumenti. Prima ogni pezzo aveva il suo polling e
    // le cose si vedevano in ritardo o non si vedevano.
    var rev = '';
    var listeners = [];
    var started = false;
    var timer = null;
    var busy = false;
    var quiet = 0;                       // giri di fila senza novita'

    // OGNI QUANTO SI CONTROLLA
    // Con una chat aperta serve reattivita' (5 s); per tutto il resto 15 s
    // bastano e avanzano. E ogni giro a vuoto raddoppia l'attesa, fino a un
    // minuto: sul server condiviso ogni chiamata ha un costo, e una sola
    // scheda lasciata aperta tutto il giorno esauriva la quota giornaliera
    // del servizio — da li' il sito che smetteva di rispondere. Appena c'e'
    // qualcosa di nuovo si torna subito veloci.
    var BASE_MS = 5000;
    var IDLE_MS = 15000;
    var MAX_MS  = 60000;

    function baseMs() { return window.LUX_CHAT_ID ? BASE_MS : IDLE_MS; }

    function nextMs() {
        // Con una chat aperta l'attesa non supera mai i 15 s, cosi' la
        // risposta di una persona non si fa aspettare; senza chat si puo'
        // arrivare fino a un minuto.
        var cap = window.LUX_CHAT_ID ? 15000 : MAX_MS;
        return Math.min(cap, baseMs() * Math.pow(2, Math.min(3, quiet)));
    }

    function schedule(ms) {
        if (timer) window.clearTimeout(timer);
        timer = window.setTimeout(tick, ms || nextMs());
    }

    function tick() {
        // Pagina statica: il controllo in tempo reale usa la sessione del
        // server, che da un altro indirizzo non arriva. Non si riprogramma
        // nemmeno un altro giro: qui non c'e' niente da controllare.
        if (window.LUX_STATIC_PAGE) return;
        if (document.visibilityState !== 'visible') { schedule(); return; }
        if (busy) { schedule(3000); return; }   // una chiamata per volta
        busy = true;
        var url = (window.LUX_API_BASE || '') + '/api/realtime.php?since=' + encodeURIComponent(rev)
                + (window.LUX_CHAT_ID ? ('&chat=' + encodeURIComponent(window.LUX_CHAT_ID)) : '');
        fetch(url, { cache: 'no-store', credentials: 'same-origin' })
            .then(function (r) { return r.json(); })
            .then(function (d) {
                busy = false;
                if (!d || !d.success) { quiet++; schedule(); return; }
                if (d.rev && d.rev !== rev) rev = d.rev;
                if (!d.changed) { quiet++; schedule(); return; }
                quiet = 0;
                listeners.forEach(function (fn) {
                    try { fn(d); } catch (e) {}
                });
                schedule();
            })
            .catch(function () { busy = false; quiet++; schedule(); });
    }

    // Controllo a richiesta, subito dopo un'azione ("ho scritto", "ho votato"):
    // cosi' il risultato compare all'istante senza tenere il controllo veloce
    // acceso per sempre.
    function now() {
        quiet = 0;
        if (timer) window.clearTimeout(timer);
        timer = null;
        tick();
    }

    window.LUX_REALTIME = {
        on: function (fn) {
            if (typeof fn !== 'function') return;
            listeners.push(fn);
            if (!started) {
                started = true;
                tick();
                schedule();
                document.addEventListener('visibilitychange', function () {
                    if (document.visibilityState === 'visible') now();
                });
            }
        },
        now: now,
        rev: function () { return rev; }
    };
})();
