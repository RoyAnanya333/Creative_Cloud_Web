// dashboard.js
async function loadNotifications() {
    const res = await apiGet('/Creative_Cloud_Web/api/notification.php?action=list');
    if (res.success) {
        const container = document.querySelector('#notif-list');
        if (!container) return;
        container.innerHTML = res.notifications.map(n => `
            <div class="post-box">
                <strong>${n.title}</strong>
                <p>${n.body}</p>
                <small>${n.created_at}</small>
            </div>
        `).join('');
    }
}

async function markRead(id){
    const res = await fetch('/Creative_Cloud_Web/api/notification.php', {
        method: 'POST',
        body: new URLSearchParams({action:'mark_read', id})
    });
    const json = await res.json();
    if (json.success) loadNotifications();
}

document.addEventListener('DOMContentLoaded', ()=>{ loadNotifications(); });
