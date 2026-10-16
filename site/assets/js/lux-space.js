/* ============================================================
   L.U.X. VERSE 39.1.1 — LO SPAZIO PERSONALE (WS2)
   ------------------------------------------------------------
   Quello che il browser ricorda di te, e il codice personale che lo fa
   ritrovare su un altro dispositivo. Nessun account: il codice e' una
   chiave, non un documento. Tutto quello che sta qui dentro funziona anche
   senza mai collegarsi: la sincronizzazione e' un di piu', non un requisito.
   ============================================================ */
(function () {
    "use strict";

    var LS = {
        history: 'lux.history',
        favs: 'lux.favs',      // gia' usato dai Preferiti (includes/platform-ui.php)
        saved: 'lux.saved',
        space: 'lux.space',    // { code: '...', pretty: '...', synced: '...' }
        memory: 'lux.memory'   // { tone: '', note: '' }
    };
    var MAX_HISTORY = 40;
    var MAX_SAVED = 30;
    var T = (window.LUX_SPACE_T || {});

    function t(k, d) { return (T[k] !== undefined && T[k] !== '') ? T[k] : d; }

    function get(key, def) {
        try { var v = localStorage.getItem(key); return v ? JSON.parse(v) : def; } catch (e) { return def; }
    }
    function set(key, val) {
        try { localStorage.setItem(key, JSON.stringify(val)); return true; } catch (e) { return false; }
    }
    function now() {
        var d = new Date(), p = function (n) { return (n < 10 ? '0' : '') + n; };
        return d.getFullYear() + '-' + p(d.getMonth() + 1) + '-' + p(d.getDate()) + ' ' + p(d.getHours()) + ':' + p(d.getMinutes()) + ':' + p(d.getSeconds());
    }
    function esc(s) {
        return String(s == null ? '' : s).replace(/[&<>"']/g, function (c) {
            return ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]);
        });
    }
    function post(url, data) {
        return fetch(url, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(data || {})
        }).then(function (r) { return r.json(); });
    }

    // ------------------------------------------------------------
    // CHI E' UNA PAGINA STRUMENTO
    // ------------------------------------------------------------
    // Il sito ha poche pagine fisse; tutto il resto e' uno strumento (una
    // pagina per strumento, servita da tool.php). L'elenco sta qui perche'
    // il browser non puo' chiederlo al server a ogni pagina.
    var SITE_PAGES = ['index', 'bacheca', 'admin', 'webapp', 'info', 'tool', 'config', 'spazio',
        'roadmap', 'proponi', 'sviluppatori', 'condividi', 'embed', 'chi-siamo', 'linea-del-tempo',
        'timeline', 'privacy', 'terms', 'faq', 'assistenza', 'contattaci', 'status', 'offline'];

    function pageName() {
        var part = (location.pathname || '/').split('/').pop() || 'index.php';
        return part.replace(/\.php$/i, '') || 'index';
    }
    function isToolPage() {
        var p = pageName().toLowerCase();
        if (!p || SITE_PAGES.indexOf(p) >= 0) return false;
        if (p.indexOf('api') === 0) return false;
        return true;
    }
    function toolLabel() {
        var h = document.querySelector('.hero-title, h1');
        var txt = h ? (h.textContent || '').trim() : '';
        if (!txt) txt = (document.title || '').split(' - ')[0].trim();
        return txt.substring(0, 110);
    }

    // ------------------------------------------------------------
    // CRONOLOGIA (le ultime cose usate)
    // ------------------------------------------------------------
    function history() { var l = get(LS.history, []); return Array.isArray(l) ? l : []; }

    /** Segna uno strumento fra gli ultimi usati (lo chiama anche l'agente). */
    function recordTool(key, name, url) {
        key = String(key || '').substring(0, 60);
        if (!key) return;
        var list = history().filter(function (r) { return r && r.key !== key; });
        list.unshift({ key: key, name: String(name || key).substring(0, 120), url: String(url || ('/' + key + '.php')).substring(0, 160), at: now() });
        if (list.length > MAX_HISTORY) list = list.slice(0, MAX_HISTORY);
        set(LS.history, list);
    }

    function recordVisit() {
        if (!isToolPage()) return;
        recordTool(pageName(), toolLabel(), '/' + pageName() + '.php');
    }

    // ------------------------------------------------------------
    // RISULTATI SALVATI
    // ------------------------------------------------------------
    function saved() { var l = get(LS.saved, []); return Array.isArray(l) ? l : []; }

    function saveResult(entry) {
        if (!entry || !entry.content) return null;
        var row = {
            id: (entry.id || ('R' + Date.now().toString(36))).substring(0, 24),
            tool: String(entry.tool || '').substring(0, 60),
            title: String(entry.title || t('Saved result', 'Risultato salvato')).substring(0, 160),
            content: String(entry.content).substring(0, 3000),
            at: now()
        };
        var list = saved().filter(function (r) { return r.id !== row.id; });
        list.unshift(row);
        if (list.length > MAX_SAVED) list = list.slice(0, MAX_SAVED);
        set(LS.saved, list);
        return row;
    }

    function removeSaved(id) {
        set(LS.saved, saved().filter(function (r) { return r.id !== id; }));
    }

    // ------------------------------------------------------------
    // IL CODICE PERSONALE
    // ------------------------------------------------------------
    function space() { var s = get(LS.space, {}); return (s && typeof s === 'object') ? s : {}; }
    function code() { return String(space().code || ''); }
    function pretty() { return String(space().pretty || '') || code(); }

    function setSpace(next) {
        var s = space();
        for (var k in next) { if (Object.prototype.hasOwnProperty.call(next, k)) s[k] = next[k]; }
        set(LS.space, s);
        refreshBadges();
        return s;
    }

    /** Tutto quello che questo dispositivo sa, nella forma che il server si aspetta. */
    function payload() {
        var favs = get(LS.favs, []);
        return {
            lang: (document.documentElement.lang || 'it').substring(0, 2),
            history: history().slice(0, MAX_HISTORY),
            favs: (Array.isArray(favs) ? favs : []).map(function (f) {
                return { key: String(f.key || '').substring(0, 60), name: String(f.name || '').substring(0, 120), url: String(f.url || '').substring(0, 160) };
            }),
            saved: saved().slice(0, MAX_SAVED),
            memory: get(LS.memory, { tone: '', note: '' })
        };
    }

    /** Unione locale: le stesse regole del server (piu' recente vince). */
    function mergeInto(p) {
        if (!p || typeof p !== 'object') return;
        ['history', 'saved'].forEach(function (where) {
            var mine = get(LS[where], []);
            var rows = (Array.isArray(p[where]) ? p[where] : []).concat(Array.isArray(mine) ? mine : []);
            var out = [], seen = {};
            rows.forEach(function (r) {
                if (!r) return;
                var k = String(r.key || r.id || '');
                if (!k || seen[k]) return;
                seen[k] = 1;
                out.push(r);
            });
            out.sort(function (a, b) { return String(b.at || '').localeCompare(String(a.at || '')); });
            set(LS[where], out.slice(0, where === 'saved' ? MAX_SAVED : MAX_HISTORY));
        });
        if (p.favs && p.favs.length) {
            var favs = get(LS.favs, []), seenF = {};
            (Array.isArray(favs) ? favs : []).forEach(function (f) { seenF[String(f.key || '')] = 1; });
            p.favs.forEach(function (f) {
                var k = String(f.key || '');
                if (k && !seenF[k]) { favs.push(f); seenF[k] = 1; }
            });
            set(LS.favs, favs);
        }
        if (p.memory) {
            var mem = get(LS.memory, { tone: '', note: '' });
            if (p.memory.tone) mem.tone = p.memory.tone;
            if (p.memory.note) mem.note = p.memory.note;
            set(LS.memory, mem);
        }
    }

    /** Il codice nuovo lo fa il server (non e' mai indovinabile). */
    function createCode() {
        return post('/api/space.php', { op: 'new' }).then(function (d) {
            if (!d || !d.success) throw new Error((d && d.error) || 'error');
            setSpace({ code: d.code, pretty: d.pretty || d.code, synced: now() });
            return post('/api/space.php', { op: 'save', code: d.code, payload: payload() });
        }).then(function (d) {
            if (d && d.success) { mergeInto(d.payload); setSpace({ synced: now() }); }
            return pretty();
        });
    }

    /** Inserisci un codice che hai gia' (da un altro dispositivo). */
    function adoptCode(raw) {
        var c = String(raw || '').toUpperCase().replace(/[^A-Z0-9]/g, '').substring(0, 12);
        if (c.length !== 12) throw new Error(t('badCode', 'Codice non valido'));
        return post('/api/space.php', { op: 'get', code: c }).then(function (d) {
            if (!d || !d.success) throw new Error((d && d.error) || 'error');
            mergeInto(d.payload);
            setSpace({ code: d.code, pretty: d.pretty || d.code, synced: now() });
            return post('/api/space.php', { op: 'save', code: d.code, payload: payload() });
        }).then(function (d) {
            if (d && d.success) { mergeInto(d.payload); setSpace({ synced: now() }); }
            return pretty();
        });
    }

    /** Manda al server quello che c'e' qui e riporta indietro il resto. */
    function sync() {
        var c = code();
        if (!c) return Promise.resolve(false);
        return post('/api/space.php', { op: 'save', code: c, payload: payload() }).then(function (d) {
            if (!d || !d.success) throw new Error((d && d.error) || 'error');
            mergeInto(d.payload);
            setSpace({ synced: now() });
            return true;
        });
    }

    function forget() {
        set(LS.space, {});
        refreshBadges();
    }

    /** Un risultato diventa un indirizzo pubblico e stabile (WS4). */
    function share(entry) {
        return post('/api/space.php', {
            op: 'share',
            tool: String((entry && entry.tool) || ''),
            title: String((entry && entry.title) || ''),
            content: String((entry && entry.content) || '')
        }).then(function (d) {
            if (!d || !d.success) throw new Error((d && d.error) || 'error');
            return d.url;
        });
    }

    /** La memoria dell'assistente: quello che ha imparato su di te. */
    function memory() { return get(LS.memory, { tone: '', note: '' }); }
    function setMemory(patch) {
        var m = memory();
        for (var k in patch) { if (Object.prototype.hasOwnProperty.call(patch, k)) m[k] = patch[k]; }
        set(LS.memory, m);
    }
    function memoryHint() {
        var m = memory();
        return {
            lang: (document.documentElement.lang || 'it').substring(0, 2),
            tone: m.tone || '',
            note: m.note || '',
            recent: history().slice(0, 6).map(function (r) { return r.key; })
        };
    }

    // ------------------------------------------------------------
    // I CARTELLINI IN PAGINA (se ci sono)
    // ------------------------------------------------------------
    function refreshBadges() {
        var c = pretty();
        document.querySelectorAll('[data-lux-space-code]').forEach(function (el) {
            el.textContent = c || t('noCode', 'Nessun codice');
        });
        document.querySelectorAll('[data-lux-space-state]').forEach(function (el) {
            el.textContent = c ? t('linked', 'Collegato') : t('notLinked', 'Non collegato');
        });
    }

    // La sincronizzazione automatica: quando cambi pagina, quello che hai
    // fatto qui va anche sul server (se hai un codice). Non blocca niente:
    // se la rete non c'e' si prova la volta dopo.
    function autoSync() {
        if (!code()) return;
        var last = Number(space().auto || 0);
        if (Date.now() - last < 60000) return;
        setSpace({ auto: Date.now() });
        sync().catch(function () {});
    }

    document.addEventListener('DOMContentLoaded', function () {
        recordVisit();
        refreshBadges();
        autoSync();
    });
    window.addEventListener('pagehide', function () { autoSync(); });

    window.LuxSpace = {
        key: LS,
        get: get,
        set: set,
        esc: esc,
        history: history,
        recordTool: recordTool,
        saved: saved,
        saveResult: saveResult,
        removeSaved: removeSaved,
        code: code,
        pretty: pretty,
        createCode: createCode,
        adoptCode: adoptCode,
        sync: sync,
        forget: forget,
        share: share,
        memory: memory,
        setMemory: setMemory,
        memoryHint: memoryHint,
        payload: payload,
        refreshBadges: refreshBadges,
        isToolPage: isToolPage,
        toolLabel: toolLabel,
        now: now
    };
})();
