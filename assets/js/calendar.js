document.addEventListener('DOMContentLoaded', () => {
    const calendarEl = document.getElementById('calendar');
    if (!calendarEl) return;

    const eventContainer = document.createElement('div');
    eventContainer.className = 'event-list';
    calendarEl.after(eventContainer);

    const calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: 'dayGridMonth',
        locale: 'fr',
        events: '/api/events',
        headerToolbar: { left: 'title', right: 'prev,next today' },
        buttonText: { today: "Aujourd'hui", prev: "Précédent", next: "Suivant" },
        datesSet: loadMonthEvents
    });

    calendar.render();

    function loadMonthEvents() {
        const date = calendar.getDate();
        const [month, year] = [date.getMonth(), date.getFullYear()];

        fetch('/api/events')
            .then(r => r.json())
            .then(events => {
                const filtered = events
                    .map(e => ({ ...e, date: new Date(e.start) }))
                    .filter(e => e.date.getMonth() === month && e.date.getFullYear() === year)
                    .sort((a, b) => a.date - b.date);

                eventContainer.innerHTML = filtered.length
                    ? ''
                    : `<p class="no-events">Aucun événement ce mois-ci.</p>`;

                filtered.forEach(e => {
                    const img = e.extendedProps?.imageUrl
                        ? `<img src="${e.extendedProps.imageUrl}" class="event-thumb" alt="${e.title}" onerror="this.style.display='none';">`
                        : '';

                    const card = document.createElement('div');
                    card.className = 'event-card';
                    card.innerHTML = `
                        <div class="event-header">
                            ${img}
                            <div class="event-meta">
                                <h3 class="event-title">${e.title}</h3>
                                <p class="event-date">${e.date.toLocaleDateString('fr-FR', { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' })}</p>
                            </div>
                        </div>
                        <div class="event-details" style="display:none;">
                            <p>${e.extendedProps?.content || "Pas de description."}</p>
                            <div class="event-reserve">
                                <button class="reserve-button">S'inscrire à l'événement</button>
                            </div>
                        </div>
                    `;

                    card.onclick = () => {
                        const details = card.querySelector('.event-details');
                        details.style.display = details.style.display === 'block' ? 'none' : 'block';
                        card.classList.toggle('open');
                    };

                    card.querySelector('.reserve-button').onclick = (ev) => {
                        ev.stopPropagation();
                        if (!IS_LOGGED_IN) {
                            window.location.href = LOGIN_URL;
                            return;
                        }
                        openReservationModal(e.id, e.title, e.start);
                    };

                    eventContainer.append(card);
                });
            })
            .catch(() => {
                eventContainer.innerHTML = `<p class="no-events">Erreur de chargement des événements.</p>`;
            });
    }

    loadMonthEvents();
});

function openReservationModal(eventId, title, startDate) {
    const modal = document.getElementById('modal');
    const list = document.getElementById('reservationList');
    const msg = document.getElementById('reservationMessage');
    const reserveBtn = document.getElementById('reserveBtn');
    const unreserveBtn = document.getElementById('unreserveBtn');
    const closeModal = document.getElementById('closeModal');

    // Affiche infos de l'événement
    list.innerHTML = `
        <li>
            <h3>${title}</h3>
            <p><strong>Date :</strong> ${new Date(startDate).toLocaleDateString('fr-FR')}</p>
        </li>
    `;

    // Charge la liste des inscrits depuis ton API Symfony
    fetch(`/api/event/${eventId}/reservations`)
        .then(r => r.json())
        .then(users => {
            msg.innerHTML = users.length
                ? `<p><strong>Déjà inscrit(s) :</strong></p><ul>${users.map(u => `<li>${u}</li>`).join('')}</ul>`
                : `<p>Aucun inscrit pour le moment.</p>`;
        });

    modal.style.display = 'flex';

    // Réserver
    reserveBtn.onclick = async () => {
        const res = await fetch(`/api/reserve/${eventId}`, { method: 'POST' });
        const data = await res.json();

        if (res.ok && data.success) {
            msg.innerHTML = `<p style='color:green;'>Réservation confirmée</p>`;
            const users = await (await fetch(`/api/event/${eventId}/reservations`)).json();
            msg.innerHTML += `<p><strong>Liste des inscrits :</strong></p><ul>${users.map(u => `<li>${u}</li>`).join('')}</ul>`;
        } else {
            msg.innerHTML = `<p style='color:red;'>${data.error || "Erreur lors de la réservation"}</p>`;
        }
    };

    // Annuler la réservation
    unreserveBtn.onclick = async () => {
        const res = await fetch(`/api/unreserve/${eventId}`, { method: 'DELETE' });
        const data = await res.json();

        if (res.ok && data.success) {
            msg.innerHTML = `<p style='color:red;'>Réservation annulée</p>`;
            const users = await (await fetch(`/api/event/${eventId}/reservations`)).json();
            msg.innerHTML += users.length
                ? `<p><strong>Liste restante :</strong></p><ul>${users.map(u => `<li>${u}</li>`).join('')}</ul>`
                : `<p>Aucun inscrit pour le moment.</p>`;
        } else {
            msg.innerHTML = `<p style='color:red;'>${data.error || "Vous n'avez fait aucune réservation"}</p>`;
        }
    };

    closeModal.onclick = () => modal.style.display = 'none';
}
