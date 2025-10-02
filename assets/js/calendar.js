document.addEventListener('DOMContentLoaded', function() {
    let calendarEl = document.getElementById('calendar');

    if (calendarEl) {
        let calendar = new FullCalendar.Calendar(calendarEl, {
            initialView: 'dayGridMonth',
            locale: 'fr',
            events: '/api/events',

            eventClick: function(info) {
                let eventId = info.event.id;

                // Première question : réserver ?
                if (confirm("Voulez-vous réserver cet événement ?")) {
                    fetch(`/api/reserve/${eventId}`, {
                        method: "POST"
                    })
                        .then(res => res.json())
                        .then(data => {
                            if (data.success) {
                                alert("Réservation réussie !");
                            } else {
                                alert("Erreur : " + data.error);
                            }
                        })
                        .catch(() => alert("Erreur serveur"));
                }

                // Ensuite on affiche la modale avec les inscrits
                fetch(`/api/event/${eventId}/reservations`)
                    .then(res => res.json())
                    .then(data => {
                        let list = document.getElementById('reservationList');
                        list.innerHTML = "";

                        if (data.length === 0) {
                            list.innerHTML = "<li>Aucune réservation</li>";
                        } else {
                            data.forEach(email => {
                                list.innerHTML += `<li>${email}</li>`;
                            });
                        }

                        document.getElementById('modal').style.display = "block";
                    });
            }
        });

        calendar.render();
    }
});
