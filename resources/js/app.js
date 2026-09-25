import './bootstrap';
import Echo from 'laravel-echo';
import Pusher from 'pusher-js';


window.Pusher = Pusher;
window.Echo = new Echo({
    broadcaster: 'reverb',
    key: import.meta.env.VITE_REVERB_APP_KEY,
    wsHost: window.location.hostname,
    wsPort: 8080,
    wssPort: 8443,
    forceTLS: false,
    disableStats: true,
    enabledTransports: ['ws', 'wss'],
});

// On écoute le canal 'alerts' pour TOUTES les alertes
window.Echo.channel('alerts')
    .listen('AlertCreated', (e) => {
        // 1. On met à jour le compteur de la cloche (+1)
        const notifBadge = document.getElementById('notif-count');
        if (notifBadge) {
            let count = parseInt(notifBadge.textContent) || 0;
            count++;
            notifBadge.textContent = count;
            notifBadge.style.display = 'flex';
        }

        // 2. On ajoute l'alerte dans le menu déroulant de la cloche
        const notifList = document.getElementById('notif-list');
        if (notifList) {
            const color = e.alert.priority === 'critical' ? 'var(--red)' : 'var(--orange)';
            // On insère la nouvelle alerte en haut de la liste
            notifList.innerHTML = `
                <a href="/alerts/${e.alert.id}" style="display: flex; gap: 12px; padding: 12px; border-bottom: 1px solid var(--border-color); text-decoration: none; color: inherit;">
                    <div style="color: ${color};">
                        <i class="fa-solid fa-triangle-exclamation"></i>
                    </div>
                    <div style="flex: 1;">
                        <strong style="display: block; font-size: 14px;">${e.alert.title}</strong>
                        <small style="color: var(--text-muted);">${e.alert.source || 'Système'} - ${e.alert.code || 'ALR-' + e.alert.id}</small>
                    </div>
                </a>
            ` + notifList.innerHTML; // On ajoute au début de la liste
        }

        // 3. Si c'est critique, on déclenche le pop-up rouge et le son !
        if (e.alert.priority === 'critical') {
            // Appel la fonction qui gère le pop-up et le son (déjà dans login.js)
            triggerUrgentAlert(e.alert);
        }
    });