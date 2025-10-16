document.addEventListener('DOMContentLoaded', function () {
    const calendarEl = document.getElementById('calendar');
    if (!calendarEl) return;

    // Conteneur des cards
    const eventContainer = document.createElement('div');
    eventContainer.classList.add('event-list');
    calendarEl.insertAdjacentElement('afterend', eventContainer);

    // FullCalendar (header seul, mais on garde la logique standard)
    const calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: 'dayGridMonth',
        locale: 'fr',
        events: '/api/events',
        headerToolbar: { left: 'title', center: '', right: 'prev,next today' },
        buttonText: { today: "Aujourd'hui", prev: "Précédent", next: "Suivant" },

        // Chaque changement de mois -> recharge les cards
        datesSet() {
            loadMonthEvents();
        },
    });

    calendar.render();

    function loadMonthEvents() {
        // Mois/année réellement affichés (pas la plage visible étendue)
        const center = calendar.getDate();
        const curMonth = center.getMonth();
        const curYear = center.getFullYear();

        fetch('/api/events')
            .then(res => res.json())
            .then(data => {
                // Filtre strict: même mois + même année
                const monthEvents = data
                    .map(e => ({ ...e, _date: new Date(e.start) }))
                    .filter(e => e._date.getMonth() === curMonth && e._date.getFullYear() === curYear)
                    .sort((a, b) => a._date - b._date); // tri chronologique

                eventContainer.innerHTML = '';

                if (monthEvents.length === 0) {
                    eventContainer.innerHTML = `<p class="no-events">Aucun événement ce mois-ci.</p>`;
                    return;
                }

                monthEvents.forEach(event => {
                    const formattedDate = event._date.toLocaleDateString('fr-FR', {
                        weekday: 'long', day: 'numeric', month: 'long', year: 'numeric'
                    });

                    const card = document.createElement('div');
                    card.classList.add('event-card');

                    const hasImg = !!event.extendedProps?.imageUrl;
                    const imgHtml = hasImg
                        ? `<img src="${event.extendedProps.imageUrl}"
                    alt="${event.title}"
                    class="event-thumb"
                    loading="lazy"
                    onerror="this.style.display='none';">`
                        : '';

                    card.innerHTML = `
            <div class="event-header">
              ${imgHtml}
              <div class="event-meta">
                <h3 class="event-title">${event.title}</h3>
                <p class="event-date">${formattedDate}</p>
              </div>
            </div>
            <div class="event-details" style="display:none;">
              <p>${event.extendedProps?.content || "Pas de description."}</p>
            </div>
          `;

                    // Toggle description au clic
                    card.addEventListener('click', () => {
                        const details = card.querySelector('.event-details');
                        const open = details.style.display === 'block';
                        details.style.display = open ? 'none' : 'block';
                        card.classList.toggle('open', !open);
                    });

                    eventContainer.appendChild(card);
                });
            })
            .catch(err => {
                console.error('Erreur de chargement des événements :', err);
                eventContainer.innerHTML = `<p class="no-events">Erreur de chargement des événements.</p>`;
            });
    }

    // Première charge (mois courant)
    loadMonthEvents();
});
