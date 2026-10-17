<?php
// ============================================================
// L.U.X. VERSE — INTERFACCIA DI PIATTAFORMA (globale)
// ------------------------------------------------------------
// Quello che si vede su OGNI pagina del sito, in fondo:
//   - il pulsante a cerchio di L.U.X. GUIDE (segue lo scorrimento);
//   - il profilo locale (facoltativo, senza registrazione);
//   - i Preferiti (la stellina su ogni progetto);
//   - il voto (upvote) su ogni progetto;
//   - il modulo per il feedback.
// Tutto senza registrazione: profilo e preferiti restano nel browser
// (localStorage), i voti e i feedback passano dalle API.
//
// Si aggancia dal footer (lux_footer) e non usa nessuna libreria.
// ============================================================

if (!function_exists('lux_platform_ui')) {
    function lux_platform_ui(): void
    {
        $lang = function_exists('currentLang') ? currentLang() : 'it';
        $it   = $lang !== 'en';
        $mail = function_exists('lux_contact_email') ? lux_contact_email() : 'administration.luxverse@gmail.com';
        ?>
<style>
/* ===== L.U.X. PLATFORM UI ===== */
.luxp-stack{position:fixed;right:20px;bottom:calc(22px + env(safe-area-inset-bottom));z-index:9000;display:flex;flex-direction:column;align-items:flex-end;gap:12px}
/* L'avviso dei cookie (cookie-consent.js) sta in fondo ed era piu' in alto
   (9998): copriva questi due pulsanti, e su un telefono il tocco finiva
   sull'avviso invece che su di loro. Per questo l'avviso e' stato messo
   sotto (8900) e, quando c'e', la pila si alza della sua altezza (misurata
   dallo stesso file, variabile --lux-cookie-h): in questo modo i due
   pulsanti restano sempre raggiungibili e visibili. Ordine da sotto a sopra:
   avviso cookie 8900, pila 9000, riquadro di comando 9600. */
body.lux-cookie-open .luxp-stack{bottom:calc(var(--lux-cookie-h, 0px) + 34px + env(safe-area-inset-bottom))}
.luxp-btn{width:54px;height:54px;border-radius:50%;border:1px solid rgba(216,169,60,.5);
  background:linear-gradient(180deg,#0C0E11,#0C0E11);color:#EDE9E0;cursor:pointer;
  display:flex;align-items:center;justify-content:center;position:relative;
  box-shadow:0 16px 40px -18px rgba(0,0,0,.9);transition:transform .16s ease,border-color .16s ease}
.luxp-btn:hover{transform:translateY(-2px);border-color:#D8A93C}
.luxp-btn.primary{width:62px;height:62px;border-color:#D8A93C;color:#D8A93C}
.luxp-btn .luxp-badge{position:absolute;top:-4px;right:-4px;min-width:18px;height:18px;border-radius:9px;
  background:#D8A93C;color:#0A0B0D;font-size:11px;font-weight:700;display:flex;align-items:center;justify-content:center;padding:0 5px}
.luxp-btn .luxp-badge[hidden]{display:none}
.luxp-label{position:absolute;right:70px;top:50%;transform:translateY(-50%);white-space:nowrap;
  background:#0C0E11;border:1px solid rgba(255,255,255,.1);color:#EDE9E0;font-size:12px;
  padding:6px 10px;border-radius:8px;opacity:0;pointer-events:none;transition:opacity .15s ease}
.luxp-btn:hover .luxp-label{opacity:1}

.luxp-panel{position:fixed;right:20px;bottom:96px;z-index:9001;width:min(380px,calc(100vw - 32px));
  max-height:min(72vh,660px);display:none;flex-direction:column;overflow:hidden;
  background:linear-gradient(180deg,#0C0E11,#0A0B0D);border:1px solid rgba(255,255,255,.1);
  border-radius:16px;box-shadow:0 40px 90px -40px rgba(0,0,0,.95)}
.luxp-panel.open{display:flex}
.luxp-head{display:flex;align-items:center;gap:10px;padding:14px 16px;border-bottom:1px solid rgba(255,255,255,.08)}
.luxp-head h3{font-size:14px;font-weight:600;color:#EDE9E0;margin:0;flex:1;letter-spacing:.02em}
.luxp-head .luxp-sub{font-size:11px;color:#8C8980;display:block;font-weight:400;margin-top:2px}
.luxp-close{background:transparent;border:0;color:#8C8980;cursor:pointer;font-size:20px;line-height:1;padding:4px}
.luxp-close:hover{color:#EDE9E0}
.luxp-body{padding:14px 16px;overflow-y:auto;display:flex;flex-direction:column;gap:10px}
.luxp-msg{border-radius:12px;padding:11px 13px;font-size:13.5px;line-height:1.55;max-width:92%}
.luxp-msg.bot{background:#171A20;color:#EDE9E0;align-self:flex-start;border:1px solid rgba(255,255,255,.06)}
.luxp-msg.user{background:rgba(216,169,60,.14);color:#EDE9E0;align-self:flex-end;border:1px solid rgba(216,169,60,.35)}
.luxp-msg.admin{background:rgba(87,185,138,.14);color:#EDE9E0;align-self:flex-start;border:1px solid rgba(87,185,138,.35)}
.luxp-msg b{display:block;color:#D8A93C;font-size:11.5px;letter-spacing:.06em;text-transform:uppercase;margin-bottom:4px}
.luxp-msgs{display:flex;flex-direction:column;gap:10px}
.luxp-notice{padding:14px 15px;border-radius:12px;background:rgba(216,169,60,.10);border:1px solid rgba(216,169,60,.34);color:#EDE9E0;font-size:13.5px;line-height:1.6}
.luxp-notice[hidden]{display:none}
.luxp-opts{display:flex;flex-wrap:wrap;gap:8px}
/* Senza questa riga "hidden" non nascondeva niente: la regola display:flex
   dell'autore vince su quella del browser, e il blocco restava a vista. */
.luxp-opts[hidden]{display:none}
.luxp-opt{background:#171A20;border:1px solid rgba(255,255,255,.12);color:#EDE9E0;font-size:12.5px;
  padding:8px 12px;border-radius:999px;cursor:pointer;transition:border-color .15s ease,color .15s ease}
.luxp-opt:hover{border-color:#D8A93C;color:#D8A93C}
.luxp-input{display:flex;gap:8px;margin-top:2px}
.luxp-input[hidden]{display:none}
.luxp-input input{flex:1;background:#0C0E11;border:1px solid rgba(255,255,255,.14);border-radius:10px;
  color:#EDE9E0;font-size:13px;padding:10px 12px;outline:none}
.luxp-input input:focus{border-color:#D8A93C}
.luxp-input button{background:#D8A93C;color:#0A0B0D;border:0;border-radius:10px;padding:0 14px;font-weight:700;cursor:pointer}
.luxp-row{display:flex;align-items:center;gap:10px;justify-content:space-between;font-size:12.5px;color:#A5A197}
.luxp-note{font-size:11.5px;color:#8C8980;line-height:1.5}
.luxp-avatar{width:44px;height:44px;border-radius:50%;background:#171A20;border:1px solid rgba(255,255,255,.14);
  display:flex;align-items:center;justify-content:center;overflow:hidden;flex-shrink:0}
.luxp-avatar img{width:100%;height:100%;object-fit:cover}
.luxp-avatar .luxp-avatar-empty{display:flex;align-items:center;justify-content:center;width:100%;height:100%}
.luxp-avatar[data-editable="1"]{cursor:pointer;border-color:#D8A93C}
.luxp-avatar[data-editable="1"]:hover{box-shadow:0 0 0 3px rgba(216,169,60,.18)}
.luxp-name{display:block;color:#EDE9E0;font-size:14px;font-weight:600;margin-bottom:2px;word-break:break-word}
.luxp-name.empty{color:#8C8980;font-weight:400;font-style:italic}
.luxp-avatar-hint{font-size:11px;color:#8C8980}
.luxp-fields{display:flex;flex-direction:column;gap:8px}
.luxp-fav{display:flex;align-items:center;gap:10px;padding:9px 10px;border:1px solid rgba(255,255,255,.08);
  border-radius:10px;background:#0C0E11;font-size:12.5px;color:#EDE9E0;text-decoration:none}
.luxp-fav:hover{border-color:#D8A93C}
.luxp-fav span{flex:1;min-width:0;overflow:hidden;text-overflow:ellipsis;white-space:nowrap}
.luxp-stars{display:flex;gap:4px}
.luxp-stars button{background:transparent;border:0;color:#8C8980;font-size:20px;cursor:pointer;padding:0 2px;line-height:1}
.luxp-stars button.on{color:#D8A93C}
/* Dopo la chiusura della chat: "Valutaci" con 5 stelle, per 15 secondi. */
.luxp-rate{display:flex;flex-direction:column;gap:8px;padding:13px 14px;border-radius:12px;
  background:rgba(216,169,60,.10);border:1px solid rgba(216,169,60,.34);color:#EDE9E0}
.luxp-rate[hidden]{display:none}
.luxp-rate strong{font-size:13.5px;font-weight:600;display:block}
.luxp-rate .luxp-rate-sub{font-size:11.5px;color:#A5A197;line-height:1.5}
.luxp-rate .luxp-stars button{font-size:26px}
.luxp-rate-actions{display:flex;align-items:center;gap:10px}
.luxp-rate-done{font-size:12px;color:#8FCF6B}
.luxp-btnline{background:#D8A93C;color:#0A0B0D;border:0;border-radius:10px;padding:10px 14px;font-weight:700;cursor:pointer;font-size:13px}
.luxp-btnline.ghost{background:transparent;border:1px solid rgba(255,255,255,.16);color:#EDE9E0;font-weight:500}
/* schede progetto: stellina preferiti + voto */
.luxp-card-tools{position:absolute;top:10px;right:10px;display:flex;gap:6px;z-index:3}
.luxp-tool{width:28px;height:28px;border-radius:50%;border:1px solid rgba(255,255,255,.16);
  background:rgba(10,11,13,.78);color:#A5A197;display:flex;align-items:center;justify-content:center;cursor:pointer}
.luxp-tool:hover{border-color:#D8A93C;color:#D8A93C}
.luxp-tool.on{color:#D8A93C;border-color:#D8A93C}
.luxp-tool svg{width:15px;height:15px}
.luxp-tool .n{font-size:10px;margin-left:2px}
/* La sigla della scorciatoia si vede solo dove c'e' una tastiera: su un
   telefono "(Ctrl+K)" non vuol dire niente. */
@media (hover: none), (pointer: coarse) {
  .luxp-kbd{display:none}
}
</style>

<div class="luxp-stack" id="luxpStack">
    <!-- 39.1.1: il comando universale (Ctrl+K) si puo' aprire anche da qui:
         non tutti sanno che esiste una scorciatoia da tastiera. -->
    <button type="button" class="luxp-btn" data-lux-cmdk="1" aria-label="<?= $it ? 'Cerca o comanda' : 'Search or command' ?>">
        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="7.5"></circle><line x1="21" y1="21" x2="16.5" y2="16.5"></line></svg>
        <?php // Su un telefono Ctrl+K non esiste: la sigla si nasconde (vedi
              // site.css), il pulsante resta e dice solo cosa fa. ?>
        <span class="luxp-label"><?= $it ? 'Cerca o comanda' : 'Search or command' ?><span class="luxp-kbd"> (Ctrl+K)</span></span>
    </button>
    <button type="button" class="luxp-btn primary" id="luxpGuideBtn" aria-label="L.U.X. GUIDE">
        <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><path d="M21 12a9 9 0 0 1-9 9H4l2-3a9 9 0 1 1 15-6z"></path><path d="M8.5 11.5h.01M12 11.5h.01M15.5 11.5h.01" stroke-width="2.4"></path></svg>
        <span class="luxp-badge" id="luxpGuideBadge" hidden>!</span>
        <span class="luxp-label">L.U.X. GUIDE</span>
    </button>
</div>

<div class="luxp-panel" id="luxpGuidePanel" role="dialog" aria-label="L.U.X. GUIDE">
    <div class="luxp-head">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#D8A93C" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><path d="M21 12a9 9 0 0 1-9 9H4l2-3a9 9 0 1 1 15-6z"></path></svg>
        <h3>L.U.X. GUIDE<span class="luxp-sub"><?= $it ? 'La guida ufficiale dell\'ecosistema' : 'The official guide of the ecosystem' ?></span></h3>
        <button type="button" class="luxp-close" data-luxp-close="1" aria-label="Close">&times;</button>
    </div>
    <div class="luxp-body" id="luxpGuideBody">
        <div class="luxp-msgs" id="luxpGuideMsgs"></div>
        <div class="luxp-notice" id="luxpGuideNotice" hidden></div>
        <div class="luxp-rate" id="luxpRate" hidden>
            <strong><?= $it ? 'Valutaci' : 'Rate us' ?><span id="luxpRateCount" class="luxp-rate-sub" style="float:right;font-weight:400"></span></strong>
            <span class="luxp-rate-sub"><?= $it ? 'Quante stelle merita questa assistenza?' : 'How many stars does this support deserve?' ?></span>
            <div class="luxp-stars" id="luxpRateStars" data-rating="0">
                <button type="button" data-v="1">&#9734;</button><button type="button" data-v="2">&#9734;</button><button type="button" data-v="3">&#9734;</button><button type="button" data-v="4">&#9734;</button><button type="button" data-v="5">&#9734;</button>
            </div>
            <div class="luxp-rate-actions">
                <button type="button" class="luxp-btnline" id="luxpRateSend"><?= $it ? 'Invia voto' : 'Send rating' ?></button>
                <span class="luxp-rate-done" id="luxpRateDone" hidden></span>
            </div>
        </div>
        <div class="luxp-opts" id="luxpGuideOpts"></div>
        <div class="luxp-input" id="luxpGuideInput" hidden>
            <input type="text" id="luxpGuideText" maxlength="600" placeholder="<?= $it ? 'Scrivi la tua domanda...' : 'Write your question...' ?>">
            <button type="button" id="luxpGuideSend"><?= $it ? 'Invia' : 'Send' ?></button>
        </div>
        <div class="luxp-row">
            <button type="button" class="luxp-btnline ghost" id="luxpGuideHuman"><?= $it ? 'Parla con una persona' : 'Talk to a person' ?></button>
            <button type="button" class="luxp-btnline ghost" id="luxpGuideClose"><?= $it ? 'Chiudi chat' : 'Close chat' ?></button>
            <button type="button" class="luxp-btnline ghost" id="luxpGuideAgain" hidden><?= $it ? 'Nuova chat' : 'New chat' ?></button>
        </div>
    </div>
</div>

<div class="luxp-panel" id="luxpProfilePanel" role="dialog" aria-label="<?= $it ? 'Profilo' : 'Profile' ?>">
    <div class="luxp-head">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#D8A93C" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="8" r="4"></circle><path d="M4 20c0-4 3.6-6 8-6s8 2 8 6"></path></svg>
        <h3><?= $it ? 'La tua area personale' : 'Your personal area' ?><span class="luxp-sub"><?= $it ? 'Solo sul tuo dispositivo · nessuna registrazione' : 'Only on your device · no registration' ?></span></h3>
        <button type="button" class="luxp-close" data-luxp-close="1" aria-label="Close">&times;</button>
    </div>
    <div class="luxp-body" id="luxpProfileBody" data-mode="view">
        <div class="luxp-row">
            <div class="luxp-avatar" id="luxpAvatar" role="button" tabindex="0" title="<?= $it ? 'Clicca per scegliere l\'immagine' : 'Click to choose the picture' ?>"></div>
            <div class="luxp-fields" style="flex:1">
                <strong class="luxp-name" id="luxpNameDisplay"></strong>
                <input id="luxpName" maxlength="32" placeholder="<?= $it ? 'Nome utente (facoltativo)' : 'Username (optional)' ?>" hidden style="width:100%;background:#0C0E11;border:1px solid rgba(255,255,255,.14);border-radius:8px;color:#EDE9E0;font-size:13px;padding:10px 12px;outline:none">
                <span class="luxp-avatar-hint" id="luxpAvatarHint" hidden><?= $it ? 'Clicca l\'immagine per cambiarla' : 'Click the picture to change it' ?></span>
            </div>
        </div>
        <button type="button" class="luxp-btnline" id="luxpSaveProfile" hidden><?= $it ? 'Salva' : 'Save' ?></button>
        <button type="button" class="luxp-btnline ghost" id="luxpEditProfile"><?= $it ? 'Modifica' : 'Edit' ?></button>
        <input type="file" id="luxpAvatarFile" accept="image/*" hidden>
        <div class="luxp-note"><?= $it ? 'Nome e immagine sono facoltativi e restano nel tuo browser. Se non scegli un\'immagine viene mostrata quella predefinita.' : 'Name and picture are optional and stay in your browser. If you do not choose a picture, the default one is shown.' ?></div>

        <div class="luxp-head" style="padding:6px 0;border:0"><h3 style="font-size:13px"><?= $it ? 'I tuoi Preferiti' : 'Your Favorites' ?></h3></div>
        <div id="luxpFavList" class="luxp-fields"><div class="luxp-note"><?= $it ? 'Nessun preferito per ora. Usa la stellina su un progetto.' : 'No favorites yet. Use the star on a project.' ?></div></div>

        <div class="luxp-head" style="padding:10px 0 2px;border:0"><h3 style="font-size:13px"><?= $it ? 'Lascia un feedback' : 'Leave feedback' ?></h3></div>
        <div class="luxp-stars" id="luxpFbStars" data-rating="0">
            <button type="button" data-v="1">&#9734;</button><button type="button" data-v="2">&#9734;</button><button type="button" data-v="3">&#9734;</button><button type="button" data-v="4">&#9734;</button><button type="button" data-v="5">&#9734;</button>
        </div>
        <input id="luxpFbText" maxlength="1500" placeholder="<?= $it ? 'Cosa possiamo migliorare? (facoltativo)' : 'What can we improve? (optional)' ?>" style="width:100%;background:#0C0E11;border:1px solid rgba(255,255,255,.14);border-radius:10px;color:#EDE9E0;font-size:13px;padding:10px 12px;outline:none">
        <button type="button" class="luxp-btnline" id="luxpFbSend"><?= $it ? 'Invia feedback' : 'Send feedback' ?></button>
        <div class="luxp-note" id="luxpFbDone" hidden></div>
    </div>
</div>

<script>
(function () {
    "use strict";
    var LANG = <?= json_encode($lang) ?>;
    var IT   = LANG !== 'en';
    var T = {
        favNo   : IT ? 'Nessun preferito per ora.' : 'No favorites yet.',
        thanks  : IT ? 'Grazie! Feedback registrato.' : 'Thank you! Feedback recorded.',
        sent    : IT ? 'Invia' : 'Send',
        escalated: IT ? 'Conversazione trasferita allo staff. Ti risponderemo qui.' : 'Conversation transferred to the staff. We will reply here.',
        err     : IT ? 'Qualcosa non ha funzionato. Riprova.' : 'Something went wrong. Try again.',
        wait    : IT ? 'Un momento...' : 'One moment...',
        saved   : IT ? 'Profilo salvato.' : 'Profile saved.',
        thanksShort: IT ? 'Grazie!' : 'Thank you!',
        transfer: IT ? 'La chat sta venendo trasferita a un umano. Si prega di attendere: le risponderemo il prima possibile.' : 'Your chat is being transferred to a human. Please wait: we will reply as soon as possible.',
        closed  : IT ? 'La conversazione è stata chiusa. Grazie!' : 'The conversation has been closed. Thank you!',
        rate    : IT ? 'Valutaci' : 'Rate us',
        rateSent: IT ? 'Grazie del voto!' : 'Thanks for the rating!',
        rateNeed: IT ? 'Scegli da 1 a 5 stelle.' : 'Pick 1 to 5 stars.',
        again   : IT ? 'Nuova chat' : 'New chat',
        // Il trasferimento e la chiusura seguono la lingua scelta nella
        // chat, non quella della pagina: chi ha parlato in italiano deve
        // leggere l'avviso in italiano anche se il sito e' in inglese.
        transferIt: 'La chat sta venendo trasferita a un umano. Si prega di attendere: le risponderemo il prima possibile.',
        transferEn: 'Your chat is being transferred to a human. Please wait: we will reply as soon as possible.',
        closedIt  : 'La conversazione è stata chiusa. Grazie!',
        closedEn  : 'The conversation has been closed. Thank you!'
    };

    var LS = {
        profile: 'lux.profile',
        favs: 'lux.favs',
        welcomed: 'lux.welcomed'
    };
    function get(k, d) { try { var v = localStorage.getItem(k); return v ? JSON.parse(v) : d; } catch (e) { return d; } }
    function set(k, v) { try { localStorage.setItem(k, JSON.stringify(v)); } catch (e) {} }
    function esc(s) { return String(s == null ? '' : s).replace(/[&<>"']/g, function (c) { return ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]); }); }

    // ---------- PREFERITI ----------
    function favs() { return get(LS.favs, []); }
    function isFav(key) { return favs().some(function (f) { return f.key === key; }); }
    function toggleFav(project) {
        var list = favs();
        var i = list.findIndex(function (f) { return f.key === project.key; });
        if (i >= 0) list.splice(i, 1); else list.push(project);
        set(LS.favs, list);
        renderFavs();
        return i < 0;
    }
    function renderFavs() {
        var box = document.getElementById('luxpFavList');
        if (!box) return;
        var list = favs();
        if (!list.length) { box.innerHTML = '<div class="luxp-note">' + esc(T.favNo) + '</div>'; return; }
        box.innerHTML = list.map(function (f) {
            return '<a class="luxp-fav" href="' + esc(f.url) + '"><span>' + esc(f.name) + '</span><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#D8A93C" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><polygon points="12 2 15 9 22 9.3 16.5 13.8 18.4 21 12 16.9 5.6 21 7.5 13.8 2 9.3 9 9"></polygon></svg></a>';
        }).join('');
    }

    // ---------- STELLINA + VOTO SULLE SCHEDE ----------
    function svgStar(on) {
        return '<svg viewBox="0 0 24 24" fill="' + (on ? 'currentColor' : 'none') + '" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><polygon points="12 2 15 9 22 9.3 16.5 13.8 18.4 21 12 16.9 5.6 21 7.5 13.8 2 9.3 9 9"></polygon></svg>';
    }
    function svgUp() {
        return '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M12 19V5M5 12l7-7 7 7"></path></svg>';
    }
    function cardKey(card) {
        var href = card.getAttribute('href') || '';
        return (href.split('/').pop() || '').replace(/\.php.*$/, '');
    }
    function decorate(card) {
        if (card.dataset.luxpDone) return;
        card.dataset.luxpDone = '1';
        if (getComputedStyle(card).position === 'static') card.style.position = 'relative';
        var key  = cardKey(card);
        var name = (card.querySelector('h4') || {}).textContent || key;
        var desc = (card.querySelector('p') || {}).textContent || '';
        var url  = card.getAttribute('href') || '';

        var box = document.createElement('div');
        box.className = 'luxp-card-tools';
        var star = document.createElement('button');
        star.type = 'button'; star.className = 'luxp-tool' + (isFav(key) ? ' on' : '');
        star.innerHTML = svgStar(isFav(key));
        star.title = IT ? 'Aggiungi ai Preferiti' : 'Add to Favorites';
        star.addEventListener('click', function (e) {
            e.preventDefault(); e.stopPropagation();
            var on = toggleFav({ key: key, name: name, url: url, desc: desc });
            star.classList.toggle('on', on);
            star.innerHTML = svgStar(on);
        });
        var up = document.createElement('button');
        up.type = 'button'; up.className = 'luxp-tool';
        up.innerHTML = svgUp();
        up.title = IT ? 'Vota questo progetto' : 'Upvote this project';
        up.addEventListener('click', function (e) {
            e.preventDefault(); e.stopPropagation();
            if (up.dataset.sent) return;
            up.dataset.sent = '1';
            fetch((window.LUX_API_BASE || '') + '/api/upvote.php', {
                method: 'POST', headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ slug: key })
            }).then(function (r) { return r.json(); }).then(function (d) {
                if (d && d.success) { up.classList.add('on'); }
            }).catch(function () {});
        });
        box.appendChild(star);
        box.appendChild(up);
        card.appendChild(box);

        card.addEventListener('click', function () {
            try {
                fetch((window.LUX_API_BASE || '') + '/api/usage.php', {
                    method: 'POST', headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ slug: key }), keepalive: true
                }).catch(function () {});
            } catch (e) {}
        });
    }
    function decorateAll(root) {
        (root || document).querySelectorAll('.project-card:not(.skeleton)').forEach(decorate);
    }

    // ---------- PROFILO ----------
    function profile() { return get(LS.profile, { name: '', avatar: '' }); }
    function defaultAvatar() {
        return '<span class="luxp-avatar-empty"><svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#8C8980" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="8" r="4"></circle><path d="M4 20c0-4 3.6-6 8-6s8 2 8 6"></path></svg></span>';
    }
    function headerAvatarHtml(p) {
        if (p.avatar) return '<img src="' + esc(p.avatar) + '" alt="">';
        return '<svg class="ico" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="8" r="4"></circle><path d="M4 20c0-4 3.6-6 8-6s8 2 8 6"></path></svg>';
    }
    function renderHeaderProfile() {
        var h = document.getElementById('luxpHeaderAvatar');
        if (h) h.innerHTML = headerAvatarHtml(profile());
    }
    function renderProfile() {
        var p = profile();
        var av = document.getElementById('luxpAvatar');
        if (av) av.innerHTML = (p.avatar ? '<img src="' + esc(p.avatar) + '" alt="">' : defaultAvatar());
        var nm = document.getElementById('luxpName');
        if (nm && !nm.dataset.touched) nm.value = p.name || '';
        var nd = document.getElementById('luxpNameDisplay');
        if (nd) {
            nd.textContent = p.name || (IT ? 'Ospite' : 'Guest');
            nd.classList.toggle('empty', !p.name);
        }
        renderHeaderProfile();
    }
    function setProfileMode(mode) {
        var editing = (mode === 'edit');
        var body = document.getElementById('luxpProfileBody');
        if (body) body.setAttribute('data-mode', editing ? 'edit' : 'view');
        var av = document.getElementById('luxpAvatar');
        if (av) av.setAttribute('data-editable', editing ? '1' : '0');
        var nm = document.getElementById('luxpName');
        var nd = document.getElementById('luxpNameDisplay');
        var hint = document.getElementById('luxpAvatarHint');
        var save = document.getElementById('luxpSaveProfile');
        var edit = document.getElementById('luxpEditProfile');
        if (nm) nm.hidden = !editing;
        if (nd) nd.hidden = editing;
        if (hint) hint.hidden = !editing;
        if (save) save.hidden = !editing;
        if (edit) edit.hidden = editing;
    }

    // ============================================================
    // L.U.X. GUIDE — chat RIFATTA DA ZERO (14 ottobre 2026)
    // ------------------------------------------------------------
    // Una sola regola: la conversazione la scrive il SERVER, il browser
    // la disegna. Da qui vengono i tre comportamenti che prima si
    // rompevano:
    //
    //  1. NIENTE MESSAGGI DOPPI. Il testo non si aggiunge "a mano" in
    //     attesa della risposta: ogni azione risponde con lo stato
    //     COMPLETO della chat (log intero + opzioni + stato) e il
    //     pannello si ridisegna da quell'elenco. Lo stesso messaggio non
    //     puo' quindi comparire due volte, ne' sparire e tornare mentre
    //     il server sta scrivendo.
    //  2. NIENTE BLOCCO. La casella di scrittura non si nasconde mai
    //     (solo a chat chiusa) e ogni richiesta ha un tempo massimo: se
    //     il server non risponde compare un avviso e si puo' riprovare.
    //     Prima bastava una domanda non riconosciuta per far sparire la
    //     casella, e una richiesta senza risposta lasciava tutto fermo.
    //  3. NON SI RIPARTE DA ZERO. Ricaricando la pagina si riprende la
    //     conversazione salvata (solo l'identificativo sta nel browser).
    // ============================================================
    var GK = { chat: 'lux.guide.chat', seen: 'lux.guide.seen', rated: 'lux.guide.rated' };
    var gv = {
        id: '', status: 'bot', lang: LANG, log: [], options: [],
        frozen: false, closed: false, busy: false, waiting: false, error: '',
        poll: null, rate: null, watch: false
    };

    function gGet(k)     { try { return localStorage.getItem(k) || ''; } catch (e) { return ''; } }
    function gPut(k, v)  { try { if (v === '' || v === null || v === undefined) localStorage.removeItem(k); else localStorage.setItem(k, String(v)); } catch (e) {} }
    function gSeen()     { return parseInt(gGet(GK.seen) || '0', 10) || 0; }
    function gSetSeen(n) { gPut(GK.seen, Math.max(0, n | 0)); }
    function gRated()    { return gv.id !== '' && gGet(GK.rated) === gv.id; }
    function gSetRated() { gPut(GK.rated, gv.id); }

    // ---------- UNA SOLA PORTA VERSO IL SERVER ----------
    // POST JSON con tempo massimo. Rifiuta SEMPRE su errore di rete o su
    // timeout: nessuna richiesta puo' restare appesa e bloccare i pulsanti.
    // Serve anche al profilo e al voto in fondo alla pagina (prima
    // chiamavano una funzione "api" che su questa pagina non esisteva).
    function api(url, data, timeoutMs) {
        var ctrl  = (typeof AbortController === 'function') ? new AbortController() : null;
        var timer = window.setTimeout(function () { if (ctrl) ctrl.abort(); }, timeoutMs || 15000);
        function stop() { window.clearTimeout(timer); }
        var opt = {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(data || {}),
            credentials: 'same-origin',
            cache: 'no-store'
        };
        if (ctrl) opt.signal = ctrl.signal;
        return fetch((window.LUX_API_BASE || '') + url, opt).then(function (r) {
            stop();
            return r.json().catch(function () { throw new Error('risposta non valida ' + r.status); });
        }, function (e) {
            stop();
            throw e;
        });
    }

    function gPost(data, timeoutMs) {
        return api('/api/guide.php', data, timeoutMs || 12000);
    }

    function gMiss() { return /not found|non trovata/i.test(gv.error); }

    function gPanelOpen() {
        var p = document.getElementById('luxpGuidePanel');
        return !!(p && p.classList.contains('open'));
    }

    // ---------- DISEGNO DEL PANNELLO ----------
    function gRender() {
        var box = document.getElementById('luxpGuideMsgs');
        if (box) {
            var html = gv.log.map(function (m) {
                var from = (m.from === 'user') ? 'user' : (m.from === 'admin' ? 'admin' : 'bot');
                var who  = from === 'bot' ? 'L.U.X. GUIDE' : (from === 'admin' ? 'Staff' : '');
                return '<div class="luxp-msg ' + from + '">'
                     + (who ? '<b>' + esc(who) + '</b>' : '')
                     + esc(m.text || '') + '</div>';
            }).join('');
            // L'attesa si vede come una riga a parte: non e' un messaggio
            // della conversazione e sparisce da sola quando arriva la
            // risposta vera, quindi non puo' restare appesa ne' duplicarsi.
            if (gv.waiting) html += '<div class="luxp-msg bot">&hellip;</div>';
            box.innerHTML = html;
            var body = document.getElementById('luxpGuideBody');
            if (body) body.scrollTop = body.scrollHeight;
        }

        // Le voci del menu: solo quando risponde il bot e la chat e' viva.
        var opts = document.getElementById('luxpGuideOpts');
        if (opts) {
            var show = !gv.closed && gv.options.length > 0;
            opts.hidden = !show;
            opts.innerHTML = '';
            if (show) {
                gv.options.forEach(function (o) {
                    var b = document.createElement('button');
                    b.type = 'button';
                    b.className = 'luxp-opt';
                    b.textContent = o.label;
                    b.disabled = gv.busy;
                    b.addEventListener('click', function () { gPick(String(o.id)); });
                    opts.appendChild(b);
                });
            }
        }

        // La casella di scrittura resta SEMPRE a vista, tranne a chat
        // chiusa: se risponde una persona si avvisa, ma si puo' scrivere.
        var host = document.getElementById('luxpGuideInput');
        if (host) host.hidden = gv.closed;
        var inp = document.getElementById('luxpGuideText');
        if (inp) inp.disabled = gv.closed;
        var sendBtn = document.getElementById('luxpGuideSend');
        if (sendBtn) { sendBtn.disabled = gv.closed || gv.busy; sendBtn.textContent = T.sent; }

        var human = document.getElementById('luxpGuideHuman');
        if (human) human.hidden = (gv.closed || gv.frozen);
        var closeBtn = document.getElementById('luxpGuideClose');
        if (closeBtn) closeBtn.hidden = gv.closed;
        var again = document.getElementById('luxpGuideAgain');
        if (again) again.hidden = !(gv.closed || gv.error !== '');

        var notice = document.getElementById('luxpGuideNotice');
        if (notice) {
            var it  = gv.lang !== 'en';
            var txt = '';
            if (gv.error) txt = gv.error;
            else if (gv.closed) txt = it ? T.closedIt : T.closedEn;
            else if (gv.frozen) txt = it ? T.transferIt : T.transferEn;
            notice.hidden = (txt === '');
            if (txt) notice.textContent = txt;
        }

        // Pastiglia sul pulsante: quanti messaggi dello staff non sono
        // ancora stati visti (si contano davvero, nessun numero inventato).
        var open = gPanelOpen();
        var seen = gSeen();
        var unseen = 0;
        gv.log.forEach(function (m, i) { if (m.from === 'admin' && i >= seen) unseen++; });
        var badge = document.getElementById('luxpGuideBadge');
        if (badge) {
            badge.hidden = (unseen === 0 || open);
            badge.textContent = unseen > 9 ? '9+' : String(unseen);
        }

        if (gv.closed) gRating(); else gHideRating();
    }

    // ---------- APPLICAZIONE DELLO STATO CHE ARRIVA DAL SERVER ----------
    function gApply(d) {
        if (!d || !d.success) return false;
        if (d.chat) {
            gv.id = String(d.chat);
            gPut(GK.chat, gv.id);
            window.LUX_CHAT_ID = gv.id;
        }
        gv.status  = String(d.status || 'bot');
        gv.lang    = (String(d.lang || LANG) === 'en') ? 'en' : 'it';
        gv.log     = (d.log && d.log.length !== undefined) ? d.log : [];
        gv.options = (d.options && d.options.length !== undefined) ? d.options : [];
        gv.frozen  = !!d.frozen;
        gv.closed  = !!d.closed;
        gv.error   = '';
        // Conversazione chiusa: non c'e' piu' niente da aspettare, quindi il
        // controllo in tempo reale torna alla cadenza lenta invece di
        // continuare a chiedere inutilmente.
        if (gv.closed) window.LUX_CHAT_ID = '';
        if (gPanelOpen()) gSetSeen(gv.log.length);
        return true;
    }

    function gReset() {
        gPut(GK.chat, '');
        gv.id = ''; gv.log = []; gv.options = [];
        gv.frozen = false; gv.closed = false; gv.error = '';
        window.LUX_CHAT_ID = '';
        gSetSeen(0);
        if (gv.poll) { window.clearInterval(gv.poll); gv.poll = null; }
    }

    // ---------- AZIONI ----------
    // Ogni azione manda UNA richiesta e riceve lo stato completo.
    function gCall(payload) {
        if (!gv.id) { gStart(); return; }
        if (gv.busy) return;
        gv.busy = true; gv.waiting = true; gv.error = '';
        gRender();
        gPost(payload).then(function (d) {
            gv.busy = false; gv.waiting = false;
            if (!d || !d.success) {
                gv.error = (d && d.error) ? String(d.error) : T.err;
                if (gMiss()) { gReset(); gStart(); return; }
            } else {
                gApply(d);
            }
            gRender();
        }, function () {
            gv.busy = false; gv.waiting = false;
            gv.error = T.err;
            gRender();
        });
    }

    function gStart() {
        if (gv.busy) return;
        gv.busy = true; gv.waiting = true; gv.error = '';
        gRender();
        gPost({ action: 'start', lang: LANG }).then(function (d) {
            gv.busy = false; gv.waiting = false;
            if (!d || !d.success || !d.chat) {
                gv.error = (d && d.error) ? String(d.error) : T.err;
                gRender();
                return;
            }
            gApply(d);
            gRender();
            gPoll();
            gWatch();
        }, function () {
            gv.busy = false; gv.waiting = false;
            gv.error = T.err;
            gRender();
        });
    }

    // Giro di controllo: serve solo a ricevere le risposte dello staff.
    // Non mostra l'attesa (non e' un'azione della persona) e non fa mai
    // rumore: se la rete cade, si riprova al giro dopo.
    function gRefresh() {
        if (!gv.id || gv.busy) return;
        gv.busy = true;
        gPost({ action: 'state', chat: gv.id, lang: LANG }).then(function (d) {
            gv.busy = false;
            if (d && d.success) { gApply(d); gRender(); return; }
            if (d && /not found|non trovata/i.test(String(d.error || ''))) { gReset(); gStart(); }
        }, function () { gv.busy = false; });
    }

    function gPoll() {
        if (gv.poll) return;
        gv.poll = window.setInterval(function () {
            if (document.visibilityState !== 'visible') return;
            if (!gPanelOpen() || !gv.id || gv.closed || gv.busy) return;
            // Solo quando la conversazione e' in mano a una persona: e'
            // l'unico caso in cui puo' arrivare qualcosa da fuori.
            if (gv.status === 'bot') return;
            gRefresh();
        }, 30000);
    }

    function gWatch() {
        if (gv.watch) return;
        if (!window.LUX_REALTIME || !window.LUX_REALTIME.on) { window.setTimeout(gWatch, 300); return; }
        gv.watch = true;
        window.LUX_REALTIME.on(function (d) {
            if (!gv.id || gv.closed || !gPanelOpen()) return;
            // L'avviso porta gia' un riassunto della conversazione: si ricarica
            // per intero solo se e' cambiata davvero, non a ogni cosa che
            // succede altrove nel sito. Prima ogni avviso costava una
            // richiesta in piu'.
            var c = d && d.chat;
            if (c && c.id === gv.id
                && String(c.status || '') === gv.status
                && Number(c.count || 0) === (gv.log ? gv.log.length : 0)) return;
            gRefresh();
        });
    }

    function gPick(id) {
        if (gv.busy) return;
        if (id === 'it' || id === 'en') { gCall({ action: 'lang', chat: gv.id, lang: id }); return; }
        gCall({ action: 'option', chat: gv.id, option: id, lang: gv.lang });
    }

    function gAsk() {
        if (gv.busy) return;
        var inp  = document.getElementById('luxpGuideText');
        var text = ((inp && inp.value) || '').trim();
        if (text === '') { if (inp) inp.focus(); return; }
        if (!gv.id) { gStart(); return; }
        if (inp) inp.value = '';
        gCall({ action: 'ask', chat: gv.id, text: text, lang: gv.lang });
    }

    function gHuman() {
        if (gv.busy) return;
        if (gv.id) { gCall({ action: 'escalate', chat: gv.id, lang: gv.lang }); return; }
        gStart();
    }

    function gCloseChat() { if (gv.id && !gv.busy) gCall({ action: 'close', chat: gv.id }); }

    function gNewChat() {
        gHideRating();
        gReset();
        gRender();
        gStart();
    }

    // ---------- INGRESSO NEL PANNELLO ----------
    function gEnter() {
        gWatch();
        if (!gv.id) { gStart(); return; }
        gSetSeen(gv.log.length);
        gRender();
        gPoll();
        gRefresh();
    }

    // ---------- "VALUTACI": 15 secondi, solo stelle, una volta sola ----------
    function gHideRating() {
        var box = document.getElementById('luxpRate');
        if (box) box.hidden = true;
        if (gv.rate) { window.clearInterval(gv.rate); gv.rate = null; }
    }

    function gRating() {
        if (gv.rate || gRated()) return;
        var box = document.getElementById('luxpRate');
        if (!box) return;
        var cnt  = document.getElementById('luxpRateCount');
        var done = document.getElementById('luxpRateDone');
        if (done) done.hidden = true;
        gStars(0);
        box.hidden = false;
        var left = 15;
        if (cnt) cnt.textContent = left + ' s';
        gv.rate = window.setInterval(function () {
            left--;
            if (cnt) cnt.textContent = left + ' s';
            if (left <= 0) { gSetRated(); gHideRating(); }
        }, 1000);
    }

    function gStars(v) {
        var box = document.getElementById('luxpRateStars');
        if (!box) return;
        box.dataset.rating = String(v);
        box.querySelectorAll('button').forEach(function (x) {
            var on = (parseInt(x.dataset.v, 10) || 0) <= v;
            x.classList.toggle('on', on);
            x.innerHTML = on ? '&#9733;' : '&#9734;';
        });
    }

    function gRateSend() {
        var box  = document.getElementById('luxpRateStars');
        var done = document.getElementById('luxpRateDone');
        var v = parseInt((box && box.dataset.rating) || '0', 10) || 0;
        if (v < 1) { if (done) { done.hidden = false; done.textContent = T.rateNeed; } return; }
        api('/api/feedback.php', {
            rating: v,
            channel: 'chat',
            message: (IT ? 'Voto sulla chat ' : 'Rating for chat ') + gv.id,
            page: (location.pathname || '') + '#' + gv.id
        }).then(function (d) {
            if (done) { done.hidden = false; done.textContent = (d && d.success) ? T.rateSent : T.err; }
            gSetRated();
            window.setTimeout(gHideRating, 1800);
        }, function () { if (done) { done.hidden = false; done.textContent = T.err; } });
    }
    // ---------- APRI/CHIUDI ----------
    function closeAll() {
        document.querySelectorAll('.luxp-panel').forEach(function (p) { p.classList.remove('open'); });
    }
    function togglePanel(id) {
        var p = document.getElementById(id);
        var wasOpen = p && p.classList.contains('open');
        closeAll();
        if (p && !wasOpen) p.classList.add('open');
    }

    document.addEventListener('DOMContentLoaded', function () {
        renderProfile();
        renderFavs();
        decorateAll(document);

        var gb = document.getElementById('luxpGuideBtn');
        var pb = document.getElementById('luxpHeaderProfile');
        if (gb) gb.addEventListener('click', function () {
            var p = document.getElementById('luxpGuidePanel');
            var open = p && p.classList.contains('open');
            closeAll();
            if (!open) { p.classList.add('open'); gEnter(); }
            else gRender();
        });
        if (pb) pb.addEventListener('click', function () { togglePanel('luxpProfilePanel'); });
        document.querySelectorAll('[data-luxp-close]').forEach(function (b) {
            b.addEventListener('click', closeAll);
        });

        var send = document.getElementById('luxpGuideSend');
        var text = document.getElementById('luxpGuideText');
        if (send) send.addEventListener('click', gAsk);
        if (text) text.addEventListener('keydown', function (e) {
            if (e.key === 'Enter') { e.preventDefault(); gAsk(); }
        });
        var human = document.getElementById('luxpGuideHuman');
        if (human) human.addEventListener('click', gHuman);
        var closeChat = document.getElementById('luxpGuideClose');
        if (closeChat) closeChat.addEventListener('click', gCloseChat);
        var again = document.getElementById('luxpGuideAgain');
        if (again) again.addEventListener('click', gNewChat);

        var rStars = document.getElementById('luxpRateStars');
        if (rStars) rStars.querySelectorAll('button').forEach(function (b) {
            b.addEventListener('click', function () { gStars(parseInt(b.dataset.v, 10) || 0); });
        });
        var rSend = document.getElementById('luxpRateSend');
        if (rSend) rSend.addEventListener('click', gRateSend);

        // Si riprende la conversazione salvata: la chat non riparte mai da
        // zero da sola e non mostra mai due volte lo stesso messaggio.
        gv.id = gGet(GK.chat);
        gWatch();
        gRender();
        if (gv.id) { gPoll(); gRefresh(); }
        // profilo — vista/modifica, immagine sulla rotonda
        setProfileMode((profile().name || profile().avatar) ? 'view' : 'edit');

        // Immagini scelte prima di questa correzione: se in memoria c'e' una
        // foto intera (centinaia di KB) la si rimpicciolisce una volta sola e
        // la si riscrive, cosi' anche chi l'aveva gia' scelta non si porta
        // dietro mezzo megabyte per sempre.
        var savedAv = profile().avatar;
        if (savedAv && savedAv.length > 120000) {
            shrinkAvatar(savedAv, function (small) {
                if (small && small.length < savedAv.length) {
                    var p2 = profile(); p2.avatar = small; set(LS.profile, p2); renderProfile();
                }
            });
        }
        var nm = document.getElementById('luxpName');
        if (nm) nm.addEventListener('input', function () { nm.dataset.touched = '1'; });
        var save = document.getElementById('luxpSaveProfile');
        if (save) save.addEventListener('click', function () {
            var p = profile();
            p.name = (nm && nm.value || '').trim().slice(0, 32);
            set(LS.profile, p);
            renderProfile();
            setProfileMode('view');
            save.textContent = T.saved;
            window.setTimeout(function () { save.textContent = IT ? 'Salva' : 'Save'; }, 1200);
        });
        var editBtn = document.getElementById('luxpEditProfile');
        if (editBtn) editBtn.addEventListener('click', function () {
            setProfileMode('edit');
            if (nm) nm.focus();
        });
        var file = document.getElementById('luxpAvatarFile');
        var avBox = document.getElementById('luxpAvatar');
        function openAvatarPicker() {
            var body = document.getElementById('luxpProfileBody');
            if (!body || body.getAttribute('data-mode') !== 'edit') setProfileMode('edit');
            if (file) file.click();
        }
        if (avBox) {
            avBox.addEventListener('click', openAvatarPicker);
            avBox.addEventListener('keydown', function (e) {
                if (e.key === 'Enter' || e.key === ' ') { e.preventDefault(); openAvatarPicker(); }
            });
        }
        // L'immagine scelta viene rimpicciolita prima di essere salvata.
        // Prima si conservava il file intero: una foto da 1254 px diventava una
        // stringa da ~700 KB nelle memoria del browser che poi, non essendoci
        // piu' la regola che la conteneva, veniva disegnata a grandezza natural
        // e faceva sparire il sito. Qui scende a 256x256, ritagliata al centro:
        // pochi KB, e resta nitida nel cerchio della testata.
        function shrinkAvatar(src, done) {
            var img = new Image();
            img.onload = function () {
                var SIDE = 256;
                var cv = document.createElement('canvas');
                cv.width = SIDE; cv.height = SIDE;
                var ctx = cv.getContext ? cv.getContext('2d') : null;
                if (!ctx) { done(src); return; }
                var w = img.naturalWidth || img.width;
                var h = img.naturalHeight || img.height;
                var side = Math.min(w, h) || SIDE;
                ctx.drawImage(img, (w - side) / 2, (h - side) / 2, side, side, 0, 0, SIDE, SIDE);
                var out = src;
                try { out = cv.toDataURL('image/png'); } catch (e) { out = src; }
                done(out && out.length < src.length ? out : src);
            };
            img.onerror = function () { done(src); };
            img.src = src;
        }

        if (file) file.addEventListener('change', function () {
            var f = file.files && file.files[0];
            if (!f) return;
            if (f.size > 6 * 1024 * 1024) { alert(IT ? 'Immagine troppo grande (max 6 MB).' : 'Image too large (max 6 MB).'); return; }
            var fr = new FileReader();
            fr.onload = function () {
                shrinkAvatar(String(fr.result), function (small) {
                    var p = profile(); p.avatar = small; set(LS.profile, p); renderProfile();
                });
            };
            fr.readAsDataURL(f);
            file.value = '';
        });

        // feedback
        var stars = document.getElementById('luxpFbStars');
        if (stars) stars.querySelectorAll('button').forEach(function (b) {
            b.addEventListener('click', function () {
                var v = parseInt(b.dataset.v, 10) || 0;
                stars.dataset.rating = v;
                stars.querySelectorAll('button').forEach(function (x) {
                    x.classList.toggle('on', parseInt(x.dataset.v, 10) <= v);
                    x.innerHTML = (parseInt(x.dataset.v, 10) <= v) ? '&#9733;' : '&#9734;';
                });
            });
        });
        var fbSend = document.getElementById('luxpFbSend');
        if (fbSend) fbSend.addEventListener('click', function () {
            var rating = parseInt((stars && stars.dataset.rating) || '0', 10) || 0;
            var msg = (document.getElementById('luxpFbText') || {}).value || '';
            api('/api/feedback.php', { rating: rating, message: msg, channel: 'site', page: location.pathname }).then(function (d) {
                var done = document.getElementById('luxpFbDone');
                if (done) { done.hidden = false; done.textContent = (d && d.success) ? T.thanks : T.err; }
            });
        });

        // Prime visite: si segna che il visitatore è passato, e basta.
        // Prima, 1,4 s dopo il caricamento, il pannello del profilo si apriva
        // DA SOLO sopra il contenuto: chi arrivava trovava un riquadro aperto
        // sulla pagina, senza aver chiesto niente, e la sensazione era quella
        // di un sito che si muove da solo. Il pulsante "Area personale" in
        // alto basta: il pannello si apre quando lo si clicca.
        if (!get(LS.welcomed, false)) set(LS.welcomed, true);

        // schede aggiunte dopo (scorrimento infinito, filtri)
        if (window.MutationObserver) {
            new MutationObserver(function (muts) {
                muts.forEach(function (m) {
                    m.addedNodes && m.addedNodes.forEach(function (n) {
                        if (n.nodeType === 1) {
                            if (n.classList && n.classList.contains('project-card')) decorate(n);
                            else if (n.querySelectorAll) decorateAll(n);
                        }
                    });
                });
            }).observe(document.body, { childList: true, subtree: true });
        }
    });
})();
</script>
        <?php
    }
}
