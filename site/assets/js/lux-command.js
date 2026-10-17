/* ============================================================
   L.U.X. VERSE 39.1.1 — IL COMANDO UNIVERSALE Ctrl+K (WS3)
   ------------------------------------------------------------
   Due tasti e arrivi dove vuoi: strumenti, pagine del sito, avvisi della
   bacheca e tutto il catalogo. La ricerca la fa il server (l'elenco delle
   pagine, i titoli degli avvisi e la ricerca nel catalogo), cosi' il browser
   non deve scaricare 100.000 schede per aprire un riquadro.

   Nello stesso riquadro stanno anche le impostazioni che rendono il sito
   piu' leggibile (WS7): lettura facilitata, testo piu' grande, lettura ad
   alta voce e dettatura per chi preferisce parlare invece di scrivere.
   ============================================================ */
(function () {
    "use strict";

    var T = (window.LUX_CMDK_T || {});
    function t(k, d) { return (T[k] !== undefined && T[k] !== '') ? T[k] : d; }

    var A11Y_KEY = 'lux.a11y';
    var state = { open: false, items: [], sel: -1, q: '', loading: false, timer: null, speaking: false };

    function esc(s) {
        return String(s == null ? '' : s).replace(/[&<>"']/g, function (c) {
            return ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]);
        });
    }
    function icon(name) {
        var p = (window.LUX_ICON_PATHS || {})[name] || (window.LUX_ICON_PATHS || {}).sparkles || '';
        return '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">' + p + '</svg>';
    }
    function a11y() {
        try { return JSON.parse(localStorage.getItem(A11Y_KEY) || '{}') || {}; } catch (e) { return {}; }
    }
    function setA11y(patch) {
        var s = a11y();
        for (var k in patch) { if (Object.prototype.hasOwnProperty.call(patch, k)) s[k] = patch[k]; }
        try { localStorage.setItem(A11Y_KEY, JSON.stringify(s)); } catch (e) {}
        applyA11y();
        return s;
    }
    function applyA11y() {
        var s = a11y();
        var root = document.documentElement;
        root.classList.toggle('lux-a11y-dyslexia', !!s.dyslexia);
        root.classList.toggle('lux-a11y-big', !!s.big);
    }

    /* ---------- la struttura del riquadro (una volta sola) ---------- */
    var box = null, input = null, listEl = null, hintEl = null;

    function build() {
        if (box) return;
        box = document.createElement('div');
        box.className = 'cmdk';
        box.hidden = true;
        box.setAttribute('role', 'dialog');
        box.setAttribute('aria-modal', 'true');
        box.setAttribute('aria-label', t('Search or command', 'Cerca o comanda'));
        box.innerHTML =
            '<div class="cmdk-box">' +
              '<div class="cmdk-input">' + icon('search') +
                // inputmode/enterkeyhint servono a un telefono: aprono la
                // tastiera con il tasto "cerca" e non con "invio" qualsiasi.
                // autocapitalize/autocorrect evitano che la ricerca venga
                // corretta in automatico ("crono" diventerebbe "Crono").
                '<input type="text" id="luxCmdkInput" autocomplete="off" spellcheck="false" ' +
                  'inputmode="search" enterkeyhint="search" autocapitalize="off" autocorrect="off" ' +
                  'placeholder="' + esc(t('Search tools, pages, notices or run a command...', 'Cerca fra strumenti, pagine, avvisi o dai un comando...')) + '">' +
                '<button type="button" class="luxp-tool" id="luxCmdkMic" hidden aria-label="' + esc(t('Dictate', 'Detta a voce')) + '">' + icon('mic') + '</button>' +
                // Su un telefono non c'e' il tasto Esc: al suo posto questo
                // pulsante. Sulla tastiera non si vede (vedi site.css).
                '<button type="button" class="cmdk-close" id="luxCmdkClose" aria-label="' + esc(t('Close', 'Chiudi')) + '">' + icon('close') + '</button>' +
                '<span class="cmdk-esc">Esc</span>' +
              '</div>' +
              '<div class="cmdk-list" id="luxCmdkList"></div>' +
              '<div class="cmdk-foot" id="luxCmdkFoot"></div>' +
            '</div>';
        box.addEventListener('mousedown', function (e) { if (e.target === box) close(); });
        document.body.appendChild(box);

        input  = box.querySelector('#luxCmdkInput');
        listEl = box.querySelector('#luxCmdkList');
        hintEl = box.querySelector('#luxCmdkFoot');

        input.addEventListener('input', function () {
            clearTimeout(state.timer);
            state.timer = setTimeout(function () { search(input.value); }, 140);
        });
        input.addEventListener('keydown', onKey);
        box.querySelector('#luxCmdkMic').addEventListener('click', dictate);
        box.querySelector('#luxCmdkClose').addEventListener('click', close);

        hintEl.innerHTML =
            '<span><kbd>&uarr;</kbd> <kbd>&darr;</kbd> ' + esc(t('to move', 'per muoverti')) + '</span>' +
            '<span><kbd>&crarr;</kbd> ' + esc(t('to open', 'per aprire')) + '</span>' +
            '<span><kbd>Esc</kbd> ' + esc(t('to close', 'per chiudere')) + '</span>';

        // La dettatura si mostra solo dove il browser la sa fare.
        var SR = window.SpeechRecognition || window.webkitSpeechRecognition;
        if (SR) {
            var mic = box.querySelector('#luxCmdkMic');
            mic.hidden = false;
        }
    }

    /* ---------- le impostazioni di lettura (WS7) ---------- */
    function localCommands(q) {
        var s = a11y();
        var defs = [
            { id: 'a11y-dyslexia', label: t('Easier reading', 'Lettura facilitata'), hint: t('Rounder letters, wider lines', 'Lettere piu tonde, righe piu larghe'), on: !!s.dyslexia, icon: 'guide' },
            { id: 'a11y-big', label: t('Bigger text', 'Testo piu grande'), hint: t('Everything a little larger', 'Tutto un po piu grande'), on: !!s.big, icon: 'sparkles' },
            { id: 'a11y-read', label: t('Read this page aloud', 'Leggi questa pagina ad alta voce'), hint: t('The browser reads the text', 'Il browser legge il testo'), on: false, icon: 'pulse' },
            { id: 'a11y-stop', label: t('Stop reading', 'Smetti di leggere'), hint: '', on: false, icon: 'close' }
        ];
        var ql = (q || '').toLowerCase();
        return defs.filter(function (d) {
            if (!ql) return false;
            return (d.label + ' ' + d.hint).toLowerCase().indexOf(ql) >= 0;
        }).map(function (d) {
            return { kind: 'a11y', id: d.id, label: d.label, hint: d.hint, on: d.on, icon: d.icon };
        });
    }

    function runLocal(id) {
        if (id === 'a11y-dyslexia') { setA11y({ dyslexia: !a11y().dyslexia }); render(); return; }
        if (id === 'a11y-big')      { setA11y({ big: !a11y().big }); render(); return; }
        if (id === 'a11y-read')     { readAloud(); return; }
        if (id === 'a11y-stop')     { stopReading(); close(); return; }
    }

    function readAloud() {
        if (!('speechSynthesis' in window)) return;
        stopReading();
        var main = document.querySelector('main') || document.body;
        var text = (main.innerText || '').replace(/\s+/g, ' ').trim().substring(0, 6000);
        if (!text) return;
        var u = new SpeechSynthesisUtterance(text);
        u.lang = (document.documentElement.lang === 'en') ? 'en-GB' : 'it-IT';
        u.rate = 1;
        u.onend = function () { state.speaking = false; };
        state.speaking = true;
        window.speechSynthesis.speak(u);
        close();
    }
    function stopReading() {
        if ('speechSynthesis' in window) { try { window.speechSynthesis.cancel(); } catch (e) {} }
        state.speaking = false;
    }

    function dictate() {
        var SR = window.SpeechRecognition || window.webkitSpeechRecognition;
        if (!SR) return;
        var rec = new SR();
        rec.lang = (document.documentElement.lang === 'en') ? 'en-GB' : 'it-IT';
        rec.interimResults = false;
        rec.maxAlternatives = 1;
        var mic = box.querySelector('#luxCmdkMic');
        if (mic) mic.classList.add('on');
        rec.onresult = function (e) {
            var txt = (e.results && e.results[0] && e.results[0][0] && e.results[0][0].transcript) || '';
            input.value = txt;
            search(txt);
        };
        rec.onend = function () { if (mic) mic.classList.remove('on'); };
        try { rec.start(); } catch (e) { if (mic) mic.classList.remove('on'); }
    }

    /* ---------- la ricerca ---------- */
    function search(q) {
        state.q = q || '';
        build();

        var local = localCommands(state.q);
        if (state.q.trim().length < 2) {
            state.items = local;
            render();
            if (state.q.trim().length === 0) suggestStart();
            return;
        }

        state.loading = true;
        listEl.innerHTML = '<div class="cmdk-empty">' + esc(t('Searching...', 'Sto cercando...')) + '</div>';

        fetch((window.LUX_API_BASE || '') + '/api/palette.php?q=' + encodeURIComponent(state.q), { cache: 'no-store' })
            .then(function (r) { return r.json(); })
            .then(function (d) {
                var items = [];
                (d.groups || []).forEach(function (g) {
                    (g.items || []).forEach(function (it) {
                        it.group = g.label;
                        items.push(it);
                    });
                });
                state.items = local.concat(items);
                state.loading = false;
                render();
            })
            .catch(function () {
                state.items = local;
                state.loading = false;
                render();
            });
    }

    /** All'apertura: le ultime cose usate e i comandi piu' utili. */
    function suggestStart() {
        var recent = (window.LuxSpace && LuxSpace.history) ? LuxSpace.history().slice(0, 5) : [];
        var items = recent.map(function (r) {
            return { kind: 'recent', label: r.name || r.key, hint: t('Recently used', 'Usato di recente'), url: r.url, icon: 'time' };
        });
        var pages = [
            { kind: 'page', label: t('My space', 'Il mio spazio'), hint: t('History, favourites, saved results', 'Cronologia, preferiti, risultati salvati'), url: '/spazio.php', icon: 'user' },
            { kind: 'page', label: t('Release roadmap', 'Percorso del rilascio'), hint: t('What is coming, in which order', 'Cosa arriva, in che ordine'), url: '/roadmap.php', icon: 'time' },
            { kind: 'page', label: t('Suggest a tool', 'Proponi uno strumento'), hint: t('Ideas and votes of the community', 'Idee e voti della community'), url: '/proponi.php', icon: 'sparkles' },
            { kind: 'page', label: t('Developer API', 'API per sviluppatori'), hint: t('Keys, docs, examples', 'Chiavi, documentazione, esempi'), url: '/sviluppatori.php', icon: 'code' }
        ];
        state.items = items.concat(pages);
        render();
    }

    function render() {
        if (!listEl) return;
        if (!state.items.length) {
            listEl.innerHTML = '<div class="cmdk-empty">' + esc(t('Nothing found. Try other words.', 'Nessun risultato. Prova altre parole.')) + '</div>';
            return;
        }
        var html = '', group = '';
        state.items.forEach(function (it, i) {
            var g = it.group || (it.kind === 'a11y' ? t('Settings', 'Impostazioni') : t('Tools', 'Strumenti'));
            if (g !== group) { group = g; html += '<div class="cmdk-group">' + esc(g) + '</div>'; }
            var kind = it.kind === 'page' ? t('Page', 'Pagina') : (it.kind === 'notice' ? t('Notice', 'Avviso') : (it.kind === 'catalog' ? t('Tool', 'Strumento') : (it.kind === 'a11y' ? t('Setting', 'Impostazione') : t('Tool', 'Strumento'))));
            // L'indice della riga sta in data-lux-i (prima era data-i, che
            // invece e' il segnaposto delle icone di lux-icons.js: quel file
            // svuota ogni elemento con data-i per metterci l'SVG, e le righe
            // restavano senza titolo ne' descrizione).
            html += '<a class="cmdk-item' + (i === state.sel ? ' on' : '') + '" data-lux-i="' + i + '" href="' + esc(it.url || '#') + '">' +
                        '<span class="cmdk-item-ico">' + icon(it.icon || 'sparkles') + '</span>' +
                        '<span class="cmdk-item-main"><b>' + esc(it.label || '') + '</b>' +
                        (it.hint ? '<span>' + esc(it.hint) + '</span>' : '') + '</span>' +
                        '<span class="cmdk-item-kind">' + esc(it.kind === 'a11y' ? t('Setting', 'Impostazione') : kind) + '</span>' +
                    '</a>';
        });
        listEl.innerHTML = html;
        listEl.querySelectorAll('.cmdk-item').forEach(function (el) {
            el.addEventListener('click', function (e) {
                var i = Number(el.dataset.luxI);
                var it = state.items[i];
                if (it && it.kind === 'a11y') { e.preventDefault(); runLocal(it.id); }
            });
        });
        var on = listEl.querySelector('.cmdk-item.on');
        if (on && on.scrollIntoView) on.scrollIntoView({ block: 'nearest' });
    }

    function onKey(e) {
        if (e.key === 'ArrowDown' || e.key === 'ArrowUp') {
            e.preventDefault();
            if (!state.items.length) return;
            state.sel = (state.sel + (e.key === 'ArrowDown' ? 1 : -1) + state.items.length) % state.items.length;
            render();
            return;
        }
        if (e.key === 'Enter') {
            e.preventDefault();
            var it = state.items[state.sel >= 0 ? state.sel : 0];
            if (!it) return;
            if (it.kind === 'a11y') { runLocal(it.id); return; }
            if (it.url) { stopReading(); location.href = it.url; }
        }
    }

    // Mentre il riquadro e' aperto la pagina di sotto non si deve muovere:
    // su un telefono, trascinando la lista si scorriva la pagina intera.
    var prevBodyOverflow = null;
    function lockScroll(on) {
        if (!document.body) return;
        if (on) {
            if (prevBodyOverflow === null) prevBodyOverflow = document.body.style.overflow;
            document.body.style.overflow = 'hidden';
        } else if (prevBodyOverflow !== null) {
            document.body.style.overflow = prevBodyOverflow;
            prevBodyOverflow = null;
        }
    }

    // Su un telefono la tastiera NON riduce l'area di layout: resta sopra la
    // pagina e copre gli ultimi risultati. Si segue invece l'area visibile
    // reale (visualViewport) e il riquadro si accorcia insieme a quella.
    var vvHandler = null;
    function fitViewport() {
        if (!box || !window.visualViewport || vvHandler) return;
        vvHandler = function () { box.style.height = window.visualViewport.height + 'px'; };
        vvHandler();
        window.visualViewport.addEventListener('resize', vvHandler);
    }
    function unfitViewport() {
        if (!box || !vvHandler) return;
        window.visualViewport.removeEventListener('resize', vvHandler);
        vvHandler = null;
        box.style.height = '';
    }

    function open(q) {
        build();
        box.hidden = false;
        state.open = true;
        state.sel = -1;
        input.value = q || '';
        applyA11y();
        lockScroll(true);
        fitViewport();
        // preventScroll: mettere a fuoco senza far saltare la pagina.
        try { input.focus({ preventScroll: true }); } catch (e) { input.focus(); }
        search(input.value);
    }
    function close() {
        if (!box) return;
        box.hidden = true;
        state.open = false;
        state.items = [];
        state.sel = -1;
        lockScroll(false);
        unfitViewport();
    }
    function toggle() { state.open ? close() : open(''); }

    /* ---------- chi lo apre ---------- */
    document.addEventListener('keydown', function (e) {
        var k = e.key || '';
        if ((e.ctrlKey || e.metaKey) && (k === 'k' || k === 'K')) { e.preventDefault(); toggle(); return; }
        if (k === 'Escape' && state.open) { e.preventDefault(); close(); }
    });
    // Il pulsante di ricerca dell'intestazione (c'e' su tutte le pagine).
    document.addEventListener('click', function (e) {
        var trigger = e.target.closest ? e.target.closest('[data-lux-cmdk]') : null;
        if (trigger) { e.preventDefault(); open(''); }
    });

    document.addEventListener('DOMContentLoaded', function () {
        build();
        applyA11y();
        build();   // idempotente: se la prima chiamata non ha trovato body, qui c'e'
    });

    window.LuxCmdk = { open: open, close: close, toggle: toggle, search: search, a11y: a11y, setA11y: setA11y };
})();
