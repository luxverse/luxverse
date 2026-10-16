// ============================================
// LUX.CONSENT - Accettazione Privacy, Termini e Cookie
// ============================================
(function() {
    // === CONFIGURAZIONE ===
    const CONFIG = {
        storageKey: 'workme_consent_status',
        version: '1.0.0',
        popupDelay: 1000
    };
    
    // === VERIFICA SE L'UTENTE HA GIÀ ACCETTATO ===
    function hasConsent() {
        try {
            const saved = localStorage.getItem(CONFIG.storageKey);
            if (saved) {
                const parsed = JSON.parse(saved);
                return parsed.status === 'accepted' && parsed.version === CONFIG.version;
            }
        } catch (e) {
            // Se localStorage non è disponibile, prova con cookie
            const cookie = document.cookie.split(';').find(c => c.trim().startsWith('workme_consent='));
            return !!cookie && cookie.includes('accepted');
        }
        return false;
    }
    
    // === SALVA CONSENSO ===
    function saveConsent(status) {
        const consentData = {
            status: status,
            version: CONFIG.version,
            timestamp: new Date().toISOString(),
            url: window.location.href,
            userAgent: navigator.userAgent,
            ip: null // sarà acquisito lato server
        };
        
        try {
            localStorage.setItem(CONFIG.storageKey, JSON.stringify(consentData));
        } catch (e) {
            document.cookie = 'workme_consent=' + encodeURIComponent(JSON.stringify(consentData)) + ';max-age=31536000;path=/';
        }
    }
    
    // === ACQUISISCI IP (tramite API esterna) ===
    async function getIP() {
        try {
            const response = await fetch('https://api.ipify.org?format=json');
            const data = await response.json();
            return data.ip;
        } catch (e) {
            return 'IP non rilevato';
        }
    }
    
    // === POPUP INIZIALE ===
    function createInitialPopup() {
        // Rimuovi eventuali popup esistenti
        const existing = document.getElementById('workme-consent-popup');
        if (existing) existing.remove();
        
        // Crea overlay
        const overlay = document.createElement('div');
        overlay.id = 'workme-consent-popup';
        overlay.style.cssText = `
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(10, 11, 13, 0.96);
            backdrop-filter: blur(10px);
            z-index: 999999;
            display: flex;
            justify-content: center;
            align-items: center;
        `;
        
        overlay.innerHTML = `
            <div style="background: #121417; border: 2px solid rgba(255,255,255,0.1); border-radius: 24px; padding: 40px; max-width: 600px; width: 90%; box-shadow: 0 20px 60px rgba(0,0,0,0.5); text-align: center;">
                <div style="margin: 0 auto 20px; width: 62px; height: 62px; display: grid; place-items: center; border: 1px solid rgba(216,169,60,0.35); background: rgba(216,169,60,0.07); color: #D8A93C;"><svg viewBox="0 0 24 24" width="30" height="30" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="square" stroke-linejoin="miter"><path d="M12 2.8l7.5 3.1v5.4c0 4.6-3.1 8.6-7.5 9.9-4.4-1.3-7.5-5.3-7.5-9.9V5.9L12 2.8z"></path><path d="M8.8 12l2.3 2.3 4.3-4.6"></path></svg></div>
                <h2 style="font-family: 'Archivo', sans-serif; font-size: 2em; color: #EDE9E0; margin-bottom: 15px;">Benvenuto su L.U.X. Verse</h2>
                <p style="color: #A5A197; font-size: 14px; line-height: 1.8; margin-bottom: 25px;">
                    Prima di accedere al sito, devi accettare la nostra <a href="privacy.php" style="color: #D8A93C; text-decoration: underline;">Privacy Policy</a> e i <a href="terms.php" style="color: #D8A93C; text-decoration: underline;">Termini di Servizio</a>.
                    <br><br>
                    Utilizziamo cookie tecnici per il funzionamento del sito. Non usiamo cookie di tracciamento.
                </p>
                <div style="display: flex; gap: 10px; justify-content: center; flex-wrap: wrap;">
                    <button id="workme-accept" style="padding: 14px 30px; background: linear-gradient(135deg, #D8A93C, #F0CE86); color: #fff; border: none; border-radius: 12px; font-size: 14px; font-weight: 600; cursor: pointer;">
                        <i data-i=check-circle class=ic></i> Accetto
                    </button>
                    <button id="workme-reject" style="padding: 14px 30px; background: rgba(200, 127, 42,0.1); color: #D96C5B; border: 1px solid rgba(200, 127, 42,0.3); border-radius: 12px; font-size: 14px; font-weight: 600; cursor: pointer;">
                        <i data-i=close-circle class=ic></i> Non accetto
                    </button>
                    <button id="workme-ignore" style="padding: 14px 30px; background: transparent; color: #A5A197; border: 1px solid rgba(255,255,255,0.2); border-radius: 12px; font-size: 14px; font-weight: 600; cursor: pointer;">
                        <i data-i=alert class=ic></i> Ignora
                    </button>
                </div>
            </div>
        `;
        
        document.body.appendChild(overlay);
        
        // EVENTI
        document.getElementById('workme-accept').addEventListener('click', function() {
            saveConsent('accepted');
            removePopup();
            showToast('<i data-i=check-circle class=ic></i> Benvenuto su L.U.X. Verse!');
        });
        
        document.getElementById('workme-reject').addEventListener('click', function() {
            saveConsent('rejected');
            removePopup();
            createRejectedPopup();
        });
        
        document.getElementById('workme-ignore').addEventListener('click', function() {
            saveConsent('ignored');
            removePopup();
            createIgnorePopup();
        });
    }
    
    // === POPUP NON ACCETTO (ROSSO) ===
    function createRejectedPopup() {
        const overlay = document.createElement('div');
        overlay.id = 'workme-consent-popup';
        overlay.style.cssText = `
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(217, 108, 91, 0.2);
            backdrop-filter: blur(10px);
            z-index: 999999;
            display: flex;
            justify-content: center;
            align-items: center;
        `;
        
        overlay.innerHTML = `
            <div style="background: #14100F; border: 3px solid #D96C5B; border-radius: 24px; padding: 40px; max-width: 600px; width: 90%; box-shadow: 0 0 60px rgba(200, 127, 42,0.3); text-align: center;">
                <div style="margin: 0 auto 20px; width: 62px; height: 62px; display: grid; place-items: center; border: 1px solid rgba(217,108,91,0.45); background: rgba(217,108,91,0.08); color: #D96C5B; animation: shake 0.5s;"><svg viewBox="0 0 24 24" width="30" height="30" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="square" stroke-linejoin="miter"><circle cx="12" cy="12" r="8.6"></circle><path d="M6 6l12 12"></path></svg></div>
                <h2 style="font-family: 'Archivo', sans-serif; font-size: 2em; color: #D96C5B; margin-bottom: 15px;">Accesso Negato</h2>
                <p style="color: #EDE9E0; font-size: 14px; line-height: 1.8; margin-bottom: 25px;">
                    <strong>Non puoi accedere al sito senza accettare i Termini di Servizio e la Privacy Policy.</strong>
                    <br><br>
                    Per utilizzare L.U.X. Verse, devi accettare le condizioni di utilizzo.
                    <br><br>
                    Se non accetti, non potrai utilizzare i nostri servizi.
                </p>
                <div style="display: flex; gap: 10px; justify-content: center;">
                    <button id="workme-back" style="padding: 14px 30px; background: linear-gradient(135deg, #D8A93C, #F0CE86); color: #fff; border: none; border-radius: 12px; font-size: 14px; font-weight: 600; cursor: pointer;">
                        <i data-i=retry class=ic></i> Riprova
                    </button>
                    <button id="workme-exit" style="padding: 14px 30px; background: #D96C5B; color: #fff; border: none; border-radius: 12px; font-size: 14px; font-weight: 600; cursor: pointer;">
                        Esci
                    </button>
                </div>
            </div>
        `;
        
        document.body.appendChild(overlay);
        
        document.getElementById('workme-back').addEventListener('click', function() {
            removePopup();
            createInitialPopup();
        });
        
        document.getElementById('workme-exit').addEventListener('click', function() {
            window.location.href = 'https://www.google.com';
        });
    }
    
    // === POPUP IGNOA (INFO URGENTE + FIRMA) ===
    async function createIgnorePopup() {
        const ip = await getIP();
        
        const overlay = document.createElement('div');
        overlay.id = 'workme-consent-popup';
        overlay.style.cssText = `
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(216, 169, 60, 0.1);
            backdrop-filter: blur(10px);
            z-index: 999999;
            display: flex;
            justify-content: center;
            align-items: center;
        `;
        
        overlay.innerHTML = `
            <div style="background: #131210; border: 3px solid #D8A93C; border-radius: 24px; padding: 40px; max-width: 650px; width: 90%; box-shadow: 0 0 60px rgba(216,169,60,0.3); text-align: center;">
                <div style="font-size: 60px; margin-bottom: 20px;"><i data-i=alert class=ic></i></div>
                <h2 style="font-family: 'Archivo', sans-serif; font-size: 2em; color: #D8A93C; margin-bottom: 15px;">Sei Responsabile</h2>
                <p style="color: #EDE9E0; font-size: 14px; line-height: 1.8; margin-bottom: 20px;">
                    Hai scelto di <strong style="color: #D8A93C;">ignorare</strong> l'accettazione dei Termini e della Privacy.
                    <br><br>
                    <strong>Ti rendi responsabile</strong> di qualsiasi danno o attività illegale effettuata tramite il sito.
                    <br><br>
                    Il tuo <strong style="color: #D8A93C;">indirizzo IP</strong> verrà acquisito e registrato:
                    <br>
                    <code style="background: rgba(255,255,255,0.1); padding: 5px 10px; border-radius: 8px; font-size: 13px; color: #D8A93C;">${ip}</code>
                    <br><br>
                    Questo IP <strong>non sarà cancellato</strong> e potrà essere utilizzato in caso di attività illegali.
                </p>
                <div style="background: rgba(255,255,255,0.05); border: 1px solid rgba(216,169,60,0.3); border-radius: 12px; padding: 20px; margin-bottom: 20px;">
                    <label style="display: flex; align-items: center; gap: 10px; color: #EDE9E0; font-weight: 600; font-size: 13px; text-align: left;">
                        <input type="checkbox" id="workme-signature-check" style="width: 20px; height: 20px;">
                        Ho letto e accetto di essere responsabile per ogni danno o attività illegale.
                    </label>
                </div>
                <div style="display: flex; gap: 10px; justify-content: center;">
                    <button id="workme-sign" disabled style="padding: 14px 30px; background: #D8A93C; color: #000; border: none; border-radius: 12px; font-size: 14px; font-weight: 600; cursor: pointer; opacity: 0.5;">
                        <i data-i=pen class=ic></i> Firma Digitale
                    </button>
                    <button id="workme-back" style="padding: 14px 30px; background: transparent; color: #EDE9E0; border: 1px solid rgba(255,255,255,0.2); border-radius: 12px; font-size: 14px; font-weight: 600; cursor: pointer;">
                        <i data-i=retry class=ic></i> Riprova
                    </button>
                </div>
                <p style="color: #A5A197; font-size: 12px; margin-top: 15px;">
                    La firma digitale acquisisce: IP, Data, Ora, Browser e URL.
                </p>
            </div>
        `;
        
        document.body.appendChild(overlay);
        
        // Abilita firma solo se checkbox è selezionato
        document.getElementById('workme-signature-check').addEventListener('change', function() {
            const signBtn = document.getElementById('workme-sign');
            if (this.checked) {
                signBtn.disabled = false;
                signBtn.style.opacity = '1';
            } else {
                signBtn.disabled = true;
                signBtn.style.opacity = '0.5';
            }
        });
        
        document.getElementById('workme-sign').addEventListener('click', async function() {
            // Acquisisci tutti i dati
            const signatureData = {
                status: 'ignored_signed',
                timestamp: new Date().toISOString(),
                ip: ip,
                userAgent: navigator.userAgent,
                url: window.location.href,
                action: 'firma_digitale'
            };
            
            // Invia al server (se c'è endpoint)
            try {
                await fetch('api/save_signature.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(signatureData)
                });
            } catch (e) {
                console.log('Errore salvataggio firma:', e);
            }
            
            saveConsent('ignored_signed');
            removePopup();
            showToast('<i data-i=check-circle class=ic></i> Firma digitale acquisita. Accesso concesso.');
        });
        
        document.getElementById('workme-back').addEventListener('click', function() {
            removePopup();
            createInitialPopup();
        });
    }
    
    // === RIMUOVI POPUP ===
    function removePopup() {
        const popup = document.getElementById('workme-consent-popup');
        if (popup) {
            popup.style.transition = 'opacity 0.3s';
            popup.style.opacity = '0';
            setTimeout(function() {
                popup.remove();
            }, 300);
        }
    }
    
    // === TOAST NOTIFICA ===
    function showToast(message) {
        const toast = document.createElement('div');
        toast.style.cssText = `
            position: fixed;
            top: 20px;
            right: 20px;
            background: #121417;
            border: 1px solid rgba(255, 233, 168,0.3);
            border-radius: 12px;
            padding: 15px 20px;
            color: #EDE9E0;
            font-size: 14px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.3);
            z-index: 999999;
            animation: slideIn 0.3s ease;
        `;
        toast.innerHTML = window.luxDeEmoji ? window.luxDeEmoji(message) : message;
        if (window.luxUpgrade) window.luxUpgrade(toast);
        document.body.appendChild(toast);
        
        setTimeout(function() {
            toast.style.transition = 'opacity 0.3s';
            toast.style.opacity = '0';
            setTimeout(function() {
                toast.remove();
            }, 300);
        }, 3000);
    }
    
    // === INIZIALIZZAZIONE ===
    function init() {
        if (!hasConsent()) {
            const style = document.createElement('style');
            style.textContent = `
                @keyframes slideIn {
                    from { transform: translateX(100px); opacity: 0; }
                    to { transform: translateX(0); opacity: 1; }
                }
                @keyframes shake {
                    0%, 100% { transform: translateX(0); }
                    25% { transform: translateX(-10px); }
                    75% { transform: translateX(10px); }
                }
            `;
            document.head.appendChild(style);
            
            setTimeout(createInitialPopup, CONFIG.popupDelay);
        }
    }
    
    // === AVVIA ===
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();