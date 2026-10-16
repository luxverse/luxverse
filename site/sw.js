/* ============================================
   L.U.X. VERSE — Service Worker
   Network-first per le pagine (PHP sempre vivo),
   stale-while-revalidate per gli asset statici.
   ============================================ */

// v9: puntino delle notifiche senza numero, nuova pagina Linea del Tempo e
// Stato in navigazione. Il cambio di versione svuota la cache vecchia, cosi'
// nessuno resta con il CSS o le pagine di prima.
// v13: nuovo tema "oro fuso" (nero del logo + oro metallico). Il nome della
// cache cambia insieme al tema: chi aveva gia' aperto il sito non resta con
// il CSS vecchio in cache per giorni.
// v18: design grafite + oro PIATTO, quello sobrio senza bagliori ne'
// animazioni (la v17 aveva rimesso la variante calda con gli aloni), piu'
// la chat della guida rifatta: niente messaggi doppi e la casella non si
// blocca piu'. Il nome cambia di nuovo, cosi' nessuno resta con il CSS o
// il JS vecchio in cache.
// v19: il marchio e' disegnato dentro la pagina (non si scarica piu' un
// file per il logo, che cosi' non compare mai in ritardo), il velo di
// caricamento si toglie da solo anche senza JavaScript e il controllo in
// tempo reale e' diventato uno solo, piu' rado: una scheda aperta non
// esaurisce piu' la quota giornaliera del servizio.
// v20: tolti gli ultimi pezzi del tema precedente rimasti nella home (la
// velatura dorata della barra di ricerca e un alone con scorrimento
// infinito che non ha mai disegnato niente).
// v21: rimesse le regole dell'area personale (erano finite nel tema
// archiviato): senza il limite di misura, l'immagine scelta dall'utente si
// disegnava a grandezza naturale e faceva sparire il sito dietro la testata.
// Ora l'immagine viene anche rimpicciolita prima di essere salvata.
// v22: rimesse anche le regole dell'avviso a comparsa (luxpop), che erano
// finite nello stesso tema archiviato: senza di esse l'avviso compariva in
// fondo alla pagina come testo grezzo.
// v23: l'app legge il catalogo dal vivo (100.000 strumenti, non 1.000), il
// campo di scrittura resta in vista con la tastiera del telefono e le
// notifiche globali arrivano fino in fondo. Il nome della cache cambia di
// nuovo, così chi ha già aperto il sito non resta con il CSS e il JS di
// prima: era proprio quello a far sembrare che le notifiche non arrivassero.
// v24: arriva il logo ufficiale nuovo (il marchio non e' piu' disegnato
// dentro la pagina) e le icone dell'app si rifanno dallo stesso disegno. Il
// marchio entra anche fra i file tenuti da parte: cosi' anche senza rete la
// testata resta con il suo logo, invece di un riquadro vuoto.
const CACHE = 'luxverse-v24';
const STATIC_ASSETS = [
  './',
  './manifest.json',
  './assets/css/site.css',
  './assets/css/index.css',
  './assets/js/site.js',
  './assets/js/lux-icons.js',
  './assets/images/icon-192.png',
  './assets/images/icon-512.png',
  './assets/images/luxverse-official-mark.png',
  './assets/images/logux.png'
];

self.addEventListener('install', (event) => {
  event.waitUntil(
    caches.open(CACHE).then((cache) => cache.addAll(STATIC_ASSETS)).then(() => self.skipWaiting())
  );
});

self.addEventListener('activate', (event) => {
  event.waitUntil(
    caches.keys().then((keys) => Promise.all(
      keys.filter((k) => k !== CACHE).map((k) => caches.delete(k))
    )).then(() => self.clients.claim())
  );
});

self.addEventListener('fetch', (event) => {
  const req = event.request;

  if (req.method !== 'GET' || !req.url.startsWith(self.location.origin)) {
    return;
  }

  const url = new URL(req.url);

  // API e dati restano sempre in rete (niente cache)
  if (url.pathname.startsWith('/api/') || url.pathname.startsWith('/data/') ||
      url.pathname.startsWith('/admin.php') ||
      url.pathname.startsWith('/workmemanage.php') || req.url.includes('lang=')) {
    return;
  }

  // Navigazione: network-first con fallback offline
  if (req.mode === 'navigate') {
    event.respondWith(
      fetch(req)
        .then((res) => {
          const copy = res.clone();
          caches.open(CACHE).then((cache) => cache.put(req, copy));
          return res;
        })
        .catch(() =>
          caches.match(req).then((hit) =>
            hit || caches.match('./offline.html')
          )
        )
    );
    return;
  }

  // Asset statici: stale-while-revalidate
  if (/\.(css|js|png|svg|webp|woff2?)(\?.*)?$/i.test(url.pathname)) {
    event.respondWith(
      caches.match(req).then((cached) => {
        const network = fetch(req).then((res) => {
          if (res && res.ok) {
            const copy = res.clone();
            caches.open(CACHE).then((cache) => cache.put(req, copy));
          }
          return res;
        }).catch(() => cached);
        return cached || network;
      })
    );
    return;
  }
});